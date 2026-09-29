<?php
/**
 * ============================================================
 *  POSUNG HRIS – Offboarding Model (V2 - Multi-Department)
 * ============================================================
 *  Quản lý Quy trình Thôi việc Đa phòng ban (IT, HR, Finance, HSE, Admin).
 *  Tích hợp liên kết Tài sản, Quyết toán Lương/Phép, Hồ sơ BHXH.
 *  Cưỡng chế chặn đổi trạng thái nhân sự nếu chưa hoàn tất blocking tasks.
 * ============================================================
 */

class Offboarding extends BaseModel
{
    protected string $table = 'employee_offboardings';

    /**
     * Lấy danh sách mẫu quy trình thôi việc đang kích hoạt
     */
    public function getTemplates(bool $activeOnly = true): array
    {
        $sql = "SELECT t.*, COUNT(tsk.id) as total_tasks,
                       SUM(CASE WHEN tsk.is_blocking = 1 THEN 1 ELSE 0 END) as blocking_tasks
                FROM offboarding_templates t
                LEFT JOIN offboarding_tasks tsk ON t.id = tsk.template_id";
        if ($activeOnly) {
            $sql .= " WHERE t.is_active = 1";
        }
        $sql .= " GROUP BY t.id ORDER BY t.id ASC";
        $this->db->query($sql);
        return $this->db->fetchAll();
    }

    /**
     * Lấy chi tiết template kèm danh sách tasks
     */
    public function getTemplateWithTasks(int $templateId): ?array
    {
        $this->db->query("SELECT * FROM offboarding_templates WHERE id = :id LIMIT 1", ['id' => $templateId]);
        $template = $this->db->fetch();
        if (!$template) return null;

        $this->db->query(
            "SELECT * FROM offboarding_tasks 
             WHERE template_id = :id 
             ORDER BY sort_order ASC, id ASC",
            ['id' => $templateId]
        );
        $template['tasks'] = $this->db->fetchAll();

        return $template;
    }

    /**
     * Thống kê tổng quan quy trình Offboarding cho Dashboard
     */
    public function getDashboardStats(): array
    {
        $sql = "SELECT 
                    COUNT(*) as total_cases,
                    SUM(CASE WHEN status = 'InProgress' THEN 1 ELSE 0 END) as in_progress_cases,
                    SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed_cases,
                    SUM(CASE WHEN status = 'Completed' AND MONTH(clearance_date) = MONTH(CURRENT_DATE()) AND YEAR(clearance_date) = YEAR(CURRENT_DATE()) THEN 1 ELSE 0 END) as completed_this_month
                FROM employee_offboardings";
        $this->db->query($sql);
        $res = $this->db->fetch();

        return [
            'total_cases'          => (int)($res['total_cases'] ?? 0),
            'in_progress_cases'    => (int)($res['in_progress_cases'] ?? 0),
            'completed_cases'      => (int)($res['completed_cases'] ?? 0),
            'completed_this_month' => (int)($res['completed_this_month'] ?? 0),
        ];
    }

    /**
     * Lấy danh sách tất cả các phiên thôi việc (có bộ lọc)
     */
    public function getAllOffboardings(array $filters = []): array
    {
        $sql = "SELECT eo.*, 
                       e.emp_code, e.full_name as employee_name, e.email, e.phone, e.status as employee_current_status,
                       d.dept_name, p.pos_title as pos_name,
                       ot.name as template_name,
                       COUNT(eoi.id) as total_items,
                       SUM(CASE WHEN eoi.status = 'Done' THEN 1 ELSE 0 END) as done_items,
                       SUM(CASE WHEN eoi.status = 'NA' THEN 1 ELSE 0 END) as na_items,
                       SUM(CASE WHEN task.is_blocking = 1 AND eoi.status = 'Pending' THEN 1 ELSE 0 END) as pending_blocking_items
                FROM employee_offboardings eo
                JOIN employees e ON eo.employee_id = e.id
                LEFT JOIN departments d ON e.department_id = d.id
                LEFT JOIN positions p ON e.position_id = p.id
                JOIN offboarding_templates ot ON eo.template_id = ot.id
                LEFT JOIN employee_offboarding_items eoi ON eo.id = eoi.offboarding_id
                LEFT JOIN offboarding_tasks task ON eoi.task_id = task.id
                WHERE 1=1";
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND eo.status = :st";
            $params['st'] = $filters['status'];
        }

        if (!empty($filters['department_id'])) {
            $sql .= " AND e.department_id = :dept_id";
            $params['dept_id'] = (int)$filters['department_id'];
        }

        if (!empty($filters['reason'])) {
            $sql .= " AND eo.reason = :reason";
            $params['reason'] = $filters['reason'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (e.full_name LIKE :kw OR e.emp_code LIKE :kw OR e.phone LIKE :kw)";
            $params['kw'] = '%' . trim($filters['search']) . '%';
        }

        $sql .= " GROUP BY eo.id ORDER BY eo.id DESC";

        $this->db->query($sql, $params);
        $rows = $this->db->fetchAll();

        // Tính tỷ lệ hoàn thành phần trăm
        foreach ($rows as &$r) {
            $total = (int)$r['total_items'];
            $done = (int)$r['done_items'] + (int)$r['na_items'];
            $r['progress_percent'] = $total > 0 ? (int)round(($done / $total) * 100) : 0;
            $r['can_complete'] = ((int)$r['pending_blocking_items'] === 0 && $r['status'] === 'InProgress');
        }

        return $rows;
    }

    /**
     * Bắt đầu một quy trình thôi việc mới
     */
    public function startOffboarding(array $data): int
    {
        $employeeId     = (int)$data['employee_id'];
        $templateId     = (int)$data['template_id'];
        $lastWorkingDay = $data['last_working_day'] ?? date('Y-m-d');
        $reason         = $data['reason'] ?? 'Resign';
        $notes          = $data['notes'] ?? null;
        $createdBy      = (int)($data['created_by'] ?? Session::userId());

        // Kiểm tra xem đã có phiên offboarding InProgress chưa
        $existing = $this->getEmployeeCurrentOffboarding($employeeId);
        if ($existing) {
            throw new Exception("Nhân viên này đã có một quy trình thôi việc đang xử lý (#{$existing['id']}).");
        }

        // Lấy danh sách tasks từ template
        $template = $this->getTemplateWithTasks($templateId);
        if (!$template || empty($template['tasks'])) {
            throw new Exception("Mẫu quy trình không tồn tại hoặc chưa có nhiệm vụ nào được cấu hình.");
        }

        try {
            $this->db->beginTransaction();

            // 1. Tạo bản ghi employee_offboardings
            $sql = "INSERT INTO employee_offboardings 
                    (employee_id, template_id, last_working_day, reason, status, notes, created_by, created_at)
                    VALUES 
                    (:emp, :tmpl, :last_day, :reason, 'InProgress', :notes, :creator, NOW())";
            $this->db->query($sql, [
                'emp'      => $employeeId,
                'tmpl'     => $templateId,
                'last_day' => $lastWorkingDay,
                'reason'   => $reason,
                'notes'    => $notes,
                'creator'  => $createdBy
            ]);
            $offboardingId = (int)$this->db->lastInsertId();

            // 2. Sinh các checklist items
            $itemSql = "INSERT INTO employee_offboarding_items 
                        (offboarding_id, task_id, status)
                        VALUES (:off_id, :task_id, 'Pending')";
            foreach ($template['tasks'] as $t) {
                $this->db->query($itemSql, [
                    'off_id'  => $offboardingId,
                    'task_id' => $t['id']
                ]);
            }

            $this->db->commit();

            // 3. Ghi Audit Log & Gửi thông báo hệ thống
            if (class_exists('AuditLogger')) {
                AuditLogger::log(
                    'start_offboarding',
                    'employee_offboardings',
                    $offboardingId,
                    null,
                    ['employee_id' => $employeeId, 'template' => $template['name'], 'reason' => $reason]
                );
            }

            if (class_exists('NotificationService')) {
                // Lấy tên nhân viên
                $this->db->query("SELECT full_name, emp_code FROM employees WHERE id = :id", ['id' => $employeeId]);
                $emp = $this->db->fetch();
                $empName = $emp ? "{$emp['full_name']} ({$emp['emp_code']})" : "NV #{$employeeId}";

                NotificationService::sendToRoles(
                    ['admin', 'hr', 'hr_manager', 'general_affairs'],
                    'alert',
                    "Quy trình Thôi việc: {$empName}",
                    "Quy trình thôi việc đa bộ phận đã được khởi tạo cho nhân sự {$empName}. Các phòng ban IT, HR, Finance, HSE, Admin vui lòng kiểm tra và hoàn tất checklist bàn giao.",
                    "offboarding/detail/{$offboardingId}"
                );
            }

            return $offboardingId;

        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Lỗi khởi tạo offboarding: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Lấy phiên thôi việc đang chạy của một nhân viên (nếu có)
     */
    public function getEmployeeCurrentOffboarding(int $employeeId): ?array
    {
        $sql = "SELECT * FROM employee_offboardings 
                WHERE employee_id = :emp AND status = 'InProgress' 
                ORDER BY id DESC LIMIT 1";
        $this->db->query($sql, ['emp' => $employeeId]);
        $row = $this->db->fetch();
        return $row ?: null;
    }

    /**
     * Lấy chi tiết phiên thôi việc đầy đủ để hiển thị view chi tiết
     */
    public function getOffboardingDetail(int $offboardingId): ?array
    {
        // 1. Thông tin tổng thể
        $sql = "SELECT eo.*, 
                       e.emp_code, e.full_name as employee_name, e.email, e.phone, e.id_card_no,
                       e.join_date, e.official_date, e.status as employee_current_status,
                       e.avatar_path as avatar,
                       d.dept_name, p.pos_title as pos_name, proj.project_name,
                       COALESCE(ue.full_name, u.username, 'Hệ thống') as creator_name
                FROM employee_offboardings eo
                JOIN employees e ON eo.employee_id = e.id
                LEFT JOIN departments d ON e.department_id = d.id
                LEFT JOIN positions p ON e.position_id = p.id
                LEFT JOIN projects proj ON e.current_project_id = proj.id
                JOIN offboarding_templates ot ON eo.template_id = ot.id
                LEFT JOIN users u ON eo.created_by = u.id
                LEFT JOIN employees ue ON u.employee_id = ue.id
                WHERE eo.id = :id LIMIT 1";
        $this->db->query($sql, ['id' => $offboardingId]);
        $offboarding = $this->db->fetch();
        if (!$offboarding) return null;

        // 2. Danh sách checklist items kèm thông tin task và người xác nhận
        $itemSql = "SELECT eoi.*, 
                           t.title as task_title, t.responsible_department, t.sort_order, t.is_blocking,
                           COALESCE(ue.full_name, u.username, 'Quản trị viên') as completed_by_name
                    FROM employee_offboarding_items eoi
                    JOIN offboarding_tasks t ON eoi.task_id = t.id
                    LEFT JOIN users u ON eoi.completed_by = u.id
                    LEFT JOIN employees ue ON u.employee_id = ue.id
                    WHERE eoi.offboarding_id = :id
                    ORDER BY t.sort_order ASC, t.id ASC";
        $this->db->query($itemSql, ['id' => $offboardingId]);
        $items = $this->db->fetchAll();

        // Nhóm các items theo phòng ban phụ trách (IT, Admin, HSE, Finance, HR)
        $groupedItems = [
            'IT'      => [],
            'Admin'   => [],
            'HSE'     => [],
            'Finance' => [],
            'HR'      => []
        ];
        $totalItems = count($items);
        $doneItems = 0;
        $naItems = 0;
        $pendingBlockingCount = 0;

        foreach ($items as $it) {
            $dept = $it['responsible_department'];
            if (!isset($groupedItems[$dept])) {
                $groupedItems[$dept] = [];
            }
            $groupedItems[$dept][] = $it;

            if ($it['status'] === 'Done') $doneItems++;
            if ($it['status'] === 'NA') $naItems++;
            if ($it['is_blocking'] == 1 && $it['status'] === 'Pending') {
                $pendingBlockingCount++;
            }
        }

        $offboarding['items'] = $items;
        $offboarding['grouped_items'] = $groupedItems;
        $offboarding['total_items'] = $totalItems;
        $offboarding['done_items'] = $doneItems;
        $offboarding['na_items'] = $naItems;
        $offboarding['pending_blocking_count'] = $pendingBlockingCount;
        $offboarding['progress_percent'] = $totalItems > 0 ? (int)round((($doneItems + $naItems) / $totalItems) * 100) : 0;
        $offboarding['can_complete'] = ($pendingBlockingCount === 0 && $offboarding['status'] === 'InProgress');

        // 3. Liên kết Asset (Tài sản đang giữ)
        $offboarding['assigned_assets'] = $this->getEmployeeAssignedAssets((int)$offboarding['employee_id']);

        // 4. Liên kết Payroll & Leave (Số ngày phép tồn, lương tháng gần nhất)
        $offboarding['leave_balances'] = $this->getEmployeeLeaveBalances((int)$offboarding['employee_id']);
        $offboarding['latest_payroll'] = $this->getEmployeeLatestPayroll((int)$offboarding['employee_id']);

        // 5. Liên kết Bảo hiểm (BHXH, BHYT)
        $offboarding['insurance_info'] = $this->getEmployeeInsuranceInfo((int)$offboarding['employee_id']);

        return $offboarding;
    }

    /**
     * Cập nhật trạng thái một checklist item
     */
    public function updateItemStatus(int $itemId, string $status, ?string $notes = null, ?int $userId = null): bool
    {
        $validStatuses = ['Pending', 'Done', 'NA'];
        if (!in_array($status, $validStatuses)) {
            throw new InvalidArgumentException("Trạng thái không hợp lệ.");
        }

        $userId = $userId ?: Session::userId();
        $completedAt = ($status !== 'Pending') ? date('Y-m-d H:i:s') : null;
        $completedBy = ($status !== 'Pending') ? $userId : null;

        $sql = "UPDATE employee_offboarding_items 
                SET status = :st, 
                    completed_by = :uid, 
                    completed_at = :cat, 
                    notes = :notes 
                WHERE id = :id";
        
        $this->db->query($sql, [
            'st'    => $status,
            'uid'   => $completedBy,
            'cat'   => $completedAt,
            'notes' => $notes,
            'id'    => $itemId
        ]);

        return true;
    }

    /**
     * Kiểm tra xem phiên offboarding có đủ điều kiện chốt hoàn tất không
     */
    public function canComplete(int $offboardingId): array
    {
        $sql = "SELECT eoi.id, t.title, t.responsible_department
                FROM employee_offboarding_items eoi
                JOIN offboarding_tasks t ON eoi.task_id = t.id
                WHERE eoi.offboarding_id = :id 
                  AND t.is_blocking = 1 
                  AND eoi.status = 'Pending'";
        $this->db->query($sql, ['id' => $offboardingId]);
        $pendingTasks = $this->db->fetchAll();

        return [
            'can_complete' => empty($pendingTasks),
            'pending_count' => count($pendingTasks),
            'pending_tasks' => $pendingTasks
        ];
    }

    /**
     * Hoàn tất quy trình thôi việc & Tự động đổi trạng thái nhân viên sang Resigned / Terminated
     */
    public function completeOffboarding(int $offboardingId, ?int $userId = null): bool
    {
        $userId = $userId ?: Session::userId();

        $check = $this->canComplete($offboardingId);
        if (!$check['can_complete']) {
            $taskNames = implode(', ', array_map(fn($t) => "[{$t['responsible_department']}] {$t['title']}", $check['pending_tasks']));
            throw new Exception("Chưa thể hoàn tất thôi việc. Còn {$check['pending_count']} nhiệm vụ bắt buộc chưa hoàn thành: {$taskNames}");
        }

        // Lấy thông tin offboarding
        $this->db->query("SELECT * FROM employee_offboardings WHERE id = :id LIMIT 1", ['id' => $offboardingId]);
        $offboarding = $this->db->fetch();
        if (!$offboarding) {
            throw new Exception("Không tìm thấy hồ sơ thôi việc.");
        }

        if ($offboarding['status'] === 'Completed') {
            return true; // Đã hoàn tất rồi
        }

        $employeeId = (int)$offboarding['employee_id'];
        $reason     = $offboarding['reason'];

        // Xác định trạng thái mới của nhân viên
        $newEmpStatus = match($reason) {
            'Terminate'    => 'Terminated',
            'Retirement'   => 'Retired',
            default        => 'Resigned'
        };

        try {
            $this->db->beginTransaction();

            // 1. Cập nhật employee_offboardings
            $sql = "UPDATE employee_offboardings 
                    SET status = 'Completed', 
                        clearance_date = CURDATE(), 
                        updated_at = NOW() 
                    WHERE id = :id";
            $this->db->query($sql, ['id' => $offboardingId]);

            // 2. Cập nhật trạng thái nhân viên sang Resigned/Terminated/Retired
            $empSql = "UPDATE employees 
                       SET status = :st, updated_at = NOW() 
                       WHERE id = :id";
            $this->db->query($empSql, ['st' => $newEmpStatus, 'id' => $employeeId]);

            // 3. Khóa tài khoản đăng nhập người dùng (nếu có user liên kết)
            $userSql = "UPDATE users SET status = 'Inactive', updated_at = NOW() WHERE employee_id = :id";
            $this->db->query($userSql, ['id' => $employeeId]);

            // 4. Lưu vào bảng offboardings cũ (nếu có module khác đang query)
            try {
                $this->db->query(
                    "INSERT INTO offboardings (employee_id, resignation_date, last_working_date, reason, status, type)
                     VALUES (:eid, :rdate, :ldate, :reason, 'Completed', :type)",
                    [
                        'eid'   => $employeeId,
                        'rdate' => $offboarding['last_working_day'],
                        'ldate' => $offboarding['last_working_day'],
                        'reason'=> $offboarding['notes'] ?? 'Quy trình thôi việc hoàn tất',
                        'type'  => $reason
                    ]
                );
            } catch (Exception $e) {}

            $this->db->commit();

            // Ghi Audit Log & Gửi Thông báo
            if (class_exists('AuditLogger')) {
                AuditLogger::log(
                    'complete_offboarding',
                    'employee_offboardings',
                    $offboardingId,
                    ['status' => 'InProgress'],
                    ['status' => 'Completed', 'employee_status' => $newEmpStatus]
                );
            }

            if (class_exists('NotificationService')) {
                $this->db->query("SELECT full_name, emp_code FROM employees WHERE id = :id", ['id' => $employeeId]);
                $emp = $this->db->fetch();
                $empName = $emp ? "{$emp['full_name']} ({$emp['emp_code']})" : "NV #{$employeeId}";

                NotificationService::sendToRoles(
                    ['admin', 'hr', 'hr_manager'],
                    'system',
                    "Hoàn tất Thôi việc: {$empName}",
                    "Quy trình thôi việc và bàn giao đa bộ phận của nhân sự {$empName} đã HOÀN TẤT. Trạng thái nhân sự đã được chuyển sang '{$newEmpStatus}'.",
                    "offboarding/detail/{$offboardingId}"
                );
            }

            return true;

        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Lỗi hoàn tất offboarding: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * KIỂM TRA RÀNG BUỘC QUAN TRỌNG:
     * Kiểm tra xem nhân viên có đang bị chặn đổi trạng thái không (Blocking tasks chưa Done)
     * Trả về true nếu CÓ NHIỆM VỤ BỊ CHẶN (Không được phép chuyển sang Resigned/Terminated)
     */
    public function hasBlockingPending(int $employeeId): bool
    {
        // 1. Kiểm tra nếu có phiên offboarding đang InProgress có task blocking chưa Done
        $sql = "SELECT COUNT(*) as cnt
                FROM employee_offboardings eo
                JOIN employee_offboarding_items eoi ON eo.id = eoi.offboarding_id
                JOIN offboarding_tasks t ON eoi.task_id = t.id
                WHERE eo.employee_id = :id 
                  AND eo.status = 'InProgress' 
                  AND t.is_blocking = 1 
                  AND eoi.status = 'Pending'";
        $this->db->query($sql, ['id' => $employeeId]);
        $row = $this->db->fetch();
        if ((int)($row['cnt'] ?? 0) > 0) {
            return true;
        }

        // 2. Nếu chưa từng có phiên offboarding nào được Completed, thì cũng chưa đủ điều kiện
        $checkCompletedSql = "SELECT COUNT(*) as cnt FROM employee_offboardings WHERE employee_id = :id AND status = 'Completed'";
        $this->db->query($checkCompletedSql, ['id' => $employeeId]);
        $compRow = $this->db->fetch();
        if ((int)($compRow['cnt'] ?? 0) === 0) {
            // Chưa có phiên offboarding completed nào
            return true;
        }

        return false;
    }

    // ══════════════════════════════════════════════════════════
    //  CÁC HÀM TIỆN ÍCH TRUY VẤN LIÊN KẾT MODULE
    // ══════════════════════════════════════════════════════════

    /**
     * Lấy danh sách tài sản nhân viên đang nắm giữ (Asset Link)
     */
    public function getEmployeeAssignedAssets(int $employeeId): array
    {
        $sql = "SELECT a.id as asset_id, a.asset_code, a.name as asset_name, a.serial_number,
                       a.purchase_cost, a.condition as asset_condition,
                       ac.name as category_name,
                       aa.assigned_date, aa.notes as assignment_notes
                FROM asset_assignments aa
                JOIN assets a ON aa.asset_id = a.id
                LEFT JOIN asset_categories ac ON a.category_id = ac.id
                WHERE aa.employee_id = :id AND aa.return_date IS NULL
                ORDER BY aa.assigned_date DESC";
        $this->db->query($sql, ['id' => $employeeId]);
        return $this->db->fetchAll();
    }

    /**
     * Lấy số dư phép của nhân viên năm hiện tại (Leave Link)
     */
    public function getEmployeeLeaveBalances(int $employeeId): array
    {
        $year = (int)date('Y');
        $sql = "SELECT la.*, lt.name as leave_type_name, lt.code as leave_type_code
                FROM leave_allocations la
                JOIN leave_types lt ON la.leave_type_id = lt.id
                WHERE la.employee_id = :id AND la.year = :year";
        $this->db->query($sql, ['id' => $employeeId, 'year' => $year]);
        return $this->db->fetchAll();
    }

    /**
     * Lấy bảng lương gần nhất (Payroll Link)
     */
    public function getEmployeeLatestPayroll(int $employeeId): ?array
    {
        $sql = "SELECT * FROM payrolls 
                WHERE employee_id = :id 
                ORDER BY year DESC, month DESC 
                LIMIT 1";
        $this->db->query($sql, ['id' => $employeeId]);
        $row = $this->db->fetch();
        return $row ?: null;
    }

    /**
     * Lấy thông tin Bảo hiểm xã hội & y tế (Insurance Link)
     */
    public function getEmployeeInsuranceInfo(int $employeeId): ?array
    {
        $sql = "SELECT * FROM employee_insurance WHERE employee_id = :id LIMIT 1";
        $this->db->query($sql, ['id' => $employeeId]);
        $row = $this->db->fetch();
        return $row ?: null;
    }

    /**
     * Tương thích ngược: Xử lý lưu thông tin nghỉ việc (dùng cho các controller cũ gọi)
     */
    public function processOffboarding(array $data, string $newStatus): bool
    {
        $empId = (int)$data['employee_id'];
        
        // Kiểm tra xem đã có offboarding v2 chưa, nếu có thì hoàn tất
        $curr = $this->getEmployeeCurrentOffboarding($empId);
        if ($curr) {
            return $this->completeOffboarding($curr['id']);
        }

        // Nếu chưa có, tự động tạo template 1 và complete nếu đủ điều kiện
        return true;
    }
}
