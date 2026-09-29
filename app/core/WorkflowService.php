<?php
/**
 * ============================================================
 *  POSUNG HRIS – Workflow & Approval Service
 * ============================================================
 *  Dịch vụ lõi xử lý Động cơ Phê duyệt đa cấp (Approval Engine)
 *  - Hỗ trợ các phân hệ: Leave, Loan, Expense, Transfer, Profile Change
 *  - Quản lý quy trình (Flows), Yêu cầu duyệt (Requests), Nhật ký (Actions)
 *  - Tích hợp kiểm tra E-Sign, phân quyền cấp duyệt, thông báo tự động
 * ============================================================
 */

if (!class_exists('NotificationService') && defined('APP_ROOT') && file_exists(APP_ROOT . '/core/NotificationService.php')) {
    require_once APP_ROOT . '/core/NotificationService.php';
}

class WorkflowService
{
    /**
     * Khởi tạo và đệ trình một yêu cầu phê duyệt mới vào Approval Engine
     *
     * @param string   $module    leave | loan | expense | transfer | profile_change
     * @param int      $recordId  ID bản ghi trong bảng nghiệp vụ tương ứng
     * @param int|null $createdBy User ID người tạo yêu cầu (mặc định lấy từ Session)
     * @return int|null ID của approval_requests được tạo hoặc đã tồn tại
     */
    public static function submit(string $module, int $recordId, ?int $createdBy = null): ?int
    {
        try {
            $db = Database::getInstance();
            $createdBy = $createdBy ?? Session::userId() ?? 1;

            // 1. Kiểm tra xem đã có yêu cầu duyệt đang Pending cho bản ghi này chưa
            $db->query(
                "SELECT id FROM approval_requests 
                 WHERE module = :m AND record_id = :r AND status = 'Pending' 
                 LIMIT 1",
                ['m' => $module, 'r' => $recordId]
            );
            $existing = $db->fetch();
            if ($existing) {
                return (int)$existing['id'];
            }

            // 2. Tìm flow đang active phù hợp với module
            $db->query(
                "SELECT id, steps FROM approval_flows 
                 WHERE module = :m AND is_active = 1 
                 ORDER BY id ASC LIMIT 1",
                ['m' => $module]
            );
            $flow = $db->fetch();
            $flowId = $flow ? (int)$flow['id'] : null;

            // 3. Tạo bản ghi approval_requests
            $db->query(
                "INSERT INTO approval_requests (flow_id, module, record_id, current_level, status, created_by, created_at)
                 VALUES (:flow_id, :module, :record_id, 1, 'Pending', :created_by, NOW())",
                [
                    'flow_id'    => $flowId,
                    'module'     => $module,
                    'record_id'  => $recordId,
                    'created_by' => $createdBy
                ]
            );
            $requestId = (int)$db->lastInsertId();

            // 4. Gửi thông báo đến cấp duyệt đầu tiên (nếu có thông tin)
            self::notifyApproversForLevel($requestId, 1, 'new_request');

            return $requestId;
        } catch (Throwable $e) {
            error_log("[WorkflowService::submit] Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Xử lý phê duyệt (Approve) yêu cầu
     *
     * @param int    $requestId ID của approval_requests
     * @param int    $userId    ID của user thực hiện duyệt
     * @param string $comments  Ý kiến nhận xét khi duyệt
     * @return array ['success' => bool, 'message' => string, 'is_final' => bool]
     */
    public static function approve(int $requestId, int $userId, string $comments = ''): array
    {
        try {
            $db = Database::getInstance();

            // 1. Lấy thông tin request
            $db->query("SELECT * FROM approval_requests WHERE id = :id LIMIT 1", ['id' => $requestId]);
            $req = $db->fetch();

            if (!$req) {
                return ['success' => false, 'message' => 'Không tìm thấy yêu cầu phê duyệt.', 'is_final' => false];
            }

            if ($req['status'] !== 'Pending') {
                return ['success' => false, 'message' => 'Yêu cầu này đã được xử lý trước đó (' . $req['status'] . ').', 'is_final' => false];
            }

            $currentLevel = (int)$req['current_level'];
            $flowId = $req['flow_id'];
            $module = $req['module'];
            $recordId = (int)$req['record_id'];

            // 2. Xác định tổng số cấp duyệt trong flow
            $totalLevels = 1;
            if ($flowId) {
                $db->query("SELECT steps FROM approval_flows WHERE id = :id LIMIT 1", ['id' => $flowId]);
                $flow = $db->fetch();
                if ($flow && !empty($flow['steps'])) {
                    $steps = json_decode($flow['steps'], true);
                    if (is_array($steps)) {
                        $totalLevels = count($steps);
                    }
                }
            }

            // 3. Ghi log hành động duyệt vào approval_actions
            $db->query(
                "INSERT INTO approval_actions (request_id, level, action, user_id, comments, acted_at)
                 VALUES (:rid, :lvl, 'approve', :uid, :cmt, NOW())",
                [
                    'rid' => $requestId,
                    'lvl' => $currentLevel,
                    'uid' => $userId,
                    'cmt' => $comments
                ]
            );

            // 4. Kiểm tra xem đã qua tất cả các cấp duyệt chưa
            $isFinal = ($currentLevel >= $totalLevels);

            if ($isFinal) {
                // Đã hoàn thành mọi cấp duyệt -> Đổi trạng thái request sang Approved
                $db->query(
                    "UPDATE approval_requests SET status = 'Approved', updated_at = NOW() WHERE id = :id",
                    ['id' => $requestId]
                );

                // Cập nhật trạng thái tương ứng trên bảng dữ liệu nghiệp vụ
                self::applyBusinessApproval($module, $recordId, $userId, $comments);

                // Gửi thông báo thành công cho người tạo yêu cầu
                if (!empty($req['created_by'])) {
                    $modLabels = self::getModuleLabels();
                    $label = $modLabels[$module] ?? $module;
                    NotificationService::send(
                        (int)$req['created_by'],
                        'approval',
                        "Yêu cầu {$label} đã được phê duyệt",
                        "Yêu cầu mã #{$recordId} của bạn đã hoàn tất phê duyệt bởi ban thẩm quyền. Ý kiến: " . ($comments ?: 'Đồng ý duyệt.'),
                        self::getModuleDetailLink($module, $recordId)
                    );
                }

                return ['success' => true, 'message' => 'Phê duyệt thành công! Yêu cầu đã hoàn tất quy trình.', 'is_final' => true];
            } else {
                // Chuyển sang cấp duyệt tiếp theo
                $nextLevel = $currentLevel + 1;
                $db->query(
                    "UPDATE approval_requests SET current_level = :next, updated_at = NOW() WHERE id = :id",
                    ['next' => $nextLevel, 'id' => $requestId]
                );

                // Gửi thông báo cho người duyệt ở cấp tiếp theo
                self::notifyApproversForLevel($requestId, $nextLevel, 'next_level');

                return ['success' => true, 'message' => "Đã duyệt Cấp {$currentLevel}! Đơn đã chuyển sang Cấp {$nextLevel} chờ phê duyệt tiếp.", 'is_final' => false];
            }
        } catch (Throwable $e) {
            error_log("[WorkflowService::approve] Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Lỗi xử lý: ' . $e->getMessage(), 'is_final' => false];
        }
    }

    /**
     * Xử lý từ chối (Reject) yêu cầu
     *
     * @param int    $requestId ID của approval_requests
     * @param int    $userId    ID của user thực hiện từ chối
     * @param string $comments  Lý do từ chối
     * @return array ['success' => bool, 'message' => string]
     */
    public static function reject(int $requestId, int $userId, string $comments = ''): array
    {
        try {
            $db = Database::getInstance();

            $db->query("SELECT * FROM approval_requests WHERE id = :id LIMIT 1", ['id' => $requestId]);
            $req = $db->fetch();

            if (!$req || $req['status'] !== 'Pending') {
                return ['success' => false, 'message' => 'Yêu cầu không hợp lệ hoặc đã xử lý.'];
            }

            $currentLevel = (int)$req['current_level'];
            $module = $req['module'];
            $recordId = (int)$req['record_id'];

            // 1. Ghi log từ chối
            $db->query(
                "INSERT INTO approval_actions (request_id, level, action, user_id, comments, acted_at)
                 VALUES (:rid, :lvl, 'reject', :uid, :cmt, NOW())",
                [
                    'rid' => $requestId,
                    'lvl' => $currentLevel,
                    'uid' => $userId,
                    'cmt' => $comments
                ]
            );

            // 2. Đổi trạng thái request sang Rejected
            $db->query(
                "UPDATE approval_requests SET status = 'Rejected', updated_at = NOW() WHERE id = :id",
                ['id' => $requestId]
            );

            // 3. Cập nhật bảng dữ liệu nghiệp vụ
            self::applyBusinessRejection($module, $recordId, $userId, $comments);

            // 4. Thông báo cho người tạo yêu cầu
            if (!empty($req['created_by'])) {
                $modLabels = self::getModuleLabels();
                $label = $modLabels[$module] ?? $module;
                NotificationService::send(
                    (int)$req['created_by'],
                    'alert',
                    "Yêu cầu {$label} bị từ chối",
                    "Yêu cầu mã #{$recordId} của bạn đã bị từ chối tại Cấp {$currentLevel}. Lý do: " . ($comments ?: 'Không đáp ứng điều kiện phê duyệt.'),
                    self::getModuleDetailLink($module, $recordId)
                );
            }

            return ['success' => true, 'message' => 'Đã từ chối yêu cầu thành công.'];
        } catch (Throwable $e) {
            error_log("[WorkflowService::reject] Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Lỗi xử lý: ' . $e->getMessage()];
        }
    }

    /**
     * Áp dụng kết quả duyệt thành công lên các bảng dữ liệu thực tế
     */
    protected static function applyBusinessApproval(string $module, int $recordId, int $userId, string $comments = ''): void
    {
        $db = Database::getInstance();

        switch ($module) {
            case 'profile_change':
                // Lấy thông tin yêu cầu đổi hồ sơ
                $db->query("SELECT * FROM profile_change_requests WHERE id = :id LIMIT 1", ['id' => $recordId]);
                $pcr = $db->fetch();
                if ($pcr && $pcr['status'] === 'Pending') {
                    $empId = (int)$pcr['employee_id'];
                    $field = $pcr['field_name'];
                    $newVal = $pcr['new_value'];

                    // Danh sách cột hợp lệ trên bảng employees chống SQL Injection
                    $allowedFields = [
                        'id_card_no', 'id_card_date', 'id_card_place', 'tax_code',
                        'social_insurance_no', 'health_insurance_no', 'home_address',
                        'current_address', 'bank_account_no', 'bank_name', 'bank_branch',
                        'academic_level', 'degree_level', 'degree_title', 'training_school'
                    ];

                    if (in_array($field, $allowedFields, true)) {
                        $db->query(
                            "UPDATE employees SET `{$field}` = :val, updated_at = NOW() WHERE id = :emp_id",
                            ['val' => $newVal, 'emp_id' => $empId]
                        );
                    }

                    // Cập nhật trạng thái profile_change_requests
                    $db->query(
                        "UPDATE profile_change_requests 
                         SET status = 'Approved', reviewed_by = :uid, reviewed_at = NOW(), notes = :notes 
                         WHERE id = :id",
                        ['uid' => $userId, 'notes' => $comments, 'id' => $recordId]
                    );

                    AuditLogger::log('approve', 'profile_change', $userId, 'profile_change_requests', $recordId, "Phê duyệt đổi trường {$field} cho nhân viên #{$empId}");
                }
                break;

            case 'leave':
                $db->query(
                    "UPDATE leave_requests 
                     SET status = 'Approved', approved_by = :uid, approved_at = NOW(), approver_note = :cmt 
                     WHERE id = :id",
                    ['uid' => $userId, 'cmt' => $comments, 'id' => $recordId]
                );
                AuditLogger::log('approve', 'leave', $userId, 'leave_requests', $recordId, "Phê duyệt đơn nghỉ phép #{$recordId}");
                break;

            case 'loan':
                $db->query(
                    "UPDATE employee_loans 
                     SET status = 'Approved', approved_by = :uid, approved_date = NOW(), notes = CONCAT(IFNULL(notes,''), ' | Duyệt: ', :cmt) 
                     WHERE id = :id",
                    ['uid' => $userId, 'cmt' => $comments, 'id' => $recordId]
                );
                AuditLogger::log('approve', 'loan', $userId, 'employee_loans', $recordId, "Phê duyệt đơn vay #{$recordId}");
                break;

            case 'expense':
                $db->query(
                    "UPDATE expense_claims 
                     SET status = 'Approved', approved_by = :uid, approved_date = NOW(), notes = CONCAT(IFNULL(notes,''), ' | Duyệt: ', :cmt) 
                     WHERE id = :id",
                    ['uid' => $userId, 'cmt' => $comments, 'id' => $recordId]
                );
                AuditLogger::log('approve', 'expense', $userId, 'expense_claims', $recordId, "Phê duyệt thanh toán chi phí #{$recordId}");
                break;

            case 'transfer':
                $db->query(
                    "UPDATE transfer_orders 
                     SET status = 'Approved', approved_by = :uid, approved_at = NOW(), notes = CONCAT(IFNULL(notes,''), ' | Duyệt: ', :cmt) 
                     WHERE id = :id",
                    ['uid' => $userId, 'cmt' => $comments, 'id' => $recordId]
                );
                AuditLogger::log('approve', 'transfer', $userId, 'transfer_orders', $recordId, "Phê duyệt lệnh điều chuyển #{$recordId}");
                break;
        }
    }

    /**
     * Áp dụng kết quả từ chối lên các bảng dữ liệu thực tế
     */
    protected static function applyBusinessRejection(string $module, int $recordId, int $userId, string $comments = ''): void
    {
        $db = Database::getInstance();

        switch ($module) {
            case 'profile_change':
                $db->query(
                    "UPDATE profile_change_requests 
                     SET status = 'Rejected', reviewed_by = :uid, reviewed_at = NOW(), notes = :notes 
                     WHERE id = :id",
                    ['uid' => $userId, 'notes' => $comments, 'id' => $recordId]
                );
                AuditLogger::log('reject', 'profile_change', $userId, 'profile_change_requests', $recordId, "Từ chối yêu cầu đổi hồ sơ #{$recordId}");
                break;

            case 'leave':
                $db->query(
                    "UPDATE leave_requests 
                     SET status = 'Rejected', approved_by = :uid, approved_at = NOW(), approver_note = :cmt 
                     WHERE id = :id",
                    ['uid' => $userId, 'cmt' => $comments, 'id' => $recordId]
                );
                AuditLogger::log('reject', 'leave', $userId, 'leave_requests', $recordId, "Từ chối đơn nghỉ phép #{$recordId}");
                break;

            case 'loan':
                $db->query(
                    "UPDATE employee_loans 
                     SET status = 'Rejected', approved_by = :uid, approved_date = NOW(), rejected_reason = :cmt 
                     WHERE id = :id",
                    ['uid' => $userId, 'cmt' => $comments, 'id' => $recordId]
                );
                AuditLogger::log('reject', 'loan', $userId, 'employee_loans', $recordId, "Từ chối đơn vay #{$recordId}");
                break;

            case 'expense':
                $db->query(
                    "UPDATE expense_claims 
                     SET status = 'Rejected', approved_by = :uid, approved_date = NOW(), rejected_reason = :cmt 
                     WHERE id = :id",
                    ['uid' => $userId, 'cmt' => $comments, 'id' => $recordId]
                );
                AuditLogger::log('reject', 'expense', $userId, 'expense_claims', $recordId, "Từ chối thanh toán chi phí #{$recordId}");
                break;

            case 'transfer':
                $db->query(
                    "UPDATE transfer_orders 
                     SET status = 'Rejected', approved_by = :uid, approved_at = NOW(), notes = CONCAT(IFNULL(notes,''), ' | Từ chối: ', :cmt) 
                     WHERE id = :id",
                    ['uid' => $userId, 'cmt' => $comments, 'id' => $recordId]
                );
                AuditLogger::log('reject', 'transfer', $userId, 'transfer_orders', $recordId, "Từ chối lệnh điều chuyển #{$recordId}");
                break;
        }
    }

    /**
     * Lấy danh sách tất cả các yêu cầu đang chờ duyệt (Hộp duyệt tập trung)
     */
    public static function getPendingApprovals(?string $filterModule = null, ?int $userId = null): array
    {
        $db = Database::getInstance();

        // Đồng bộ các bản ghi Pending từ các bảng nghiệp vụ chưa có trong approval_requests
        self::syncLegacyPendingRequests();

        $sql = "
            SELECT ar.*, 
                   af.name as flow_name,
                   af.steps as flow_steps,
                   u.username as creator_username,
                   IFNULL(e.full_name, u.username) as creator_name,
                   e.emp_code as creator_emp_code
            FROM approval_requests ar
            LEFT JOIN approval_flows af ON ar.flow_id = af.id
            LEFT JOIN users u ON ar.created_by = u.id
            LEFT JOIN employees e ON u.employee_id = e.id
            WHERE ar.status = 'Pending'
        ";
        $params = [];

        if (!empty($filterModule) && $filterModule !== 'all') {
            $sql .= " AND ar.module = :mod";
            $params['mod'] = $filterModule;
        }

        $sql .= " ORDER BY ar.created_at DESC, ar.id DESC";

        $db->query($sql, $params);
        $requests = $db->fetchAll();

        // Gắn chi tiết dữ liệu thực tế cho từng loại đơn
        foreach ($requests as &$req) {
            $req['details'] = self::getRequestDetails($req['module'], (int)$req['record_id']);
            $steps = !empty($req['flow_steps']) ? json_decode($req['flow_steps'], true) : [];
            $req['total_levels'] = is_array($steps) ? count($steps) : 1;
            
            // Tìm tên vai trò của cấp duyệt hiện tại
            $currentRole = 'Quản lý phê duyệt';
            if (is_array($steps)) {
                foreach ($steps as $st) {
                    if (($st['level'] ?? 1) == $req['current_level']) {
                        $currentRole = $st['role_name'] ?? $st['role'] ?? 'Quản lý phê duyệt';
                        break;
                    }
                }
            }
            $req['current_role_name'] = $currentRole;
        }

        return $requests;
    }

    /**
     * Thống kê số lượng đơn chờ duyệt theo từng phân hệ
     */
    public static function getPendingCounts(): array
    {
        $db = Database::getInstance();
        self::syncLegacyPendingRequests();

        $db->query("
            SELECT module, COUNT(*) as cnt 
            FROM approval_requests 
            WHERE status = 'Pending' 
            GROUP BY module
        ");
        $rows = $db->fetchAll();

        $counts = [
            'total'          => 0,
            'leave'          => 0,
            'loan'           => 0,
            'expense'        => 0,
            'transfer'       => 0,
            'profile_change' => 0
        ];

        foreach ($rows as $row) {
            $mod = $row['module'];
            $c = (int)$row['cnt'];
            if (isset($counts[$mod])) {
                $counts[$mod] = $c;
            }
            $counts['total'] += $c;
        }

        return $counts;
    }

    /**
     * Đọc thông tin chi tiết của một bản ghi nghiệp vụ theo module
     */
    public static function getRequestDetails(string $module, int $recordId): array
    {
        $db = Database::getInstance();
        $details = [];

        switch ($module) {
            case 'profile_change':
                $db->query(
                    "SELECT pcr.*, e.emp_code, e.full_name as employee_name, d.dept_name, p.pos_title
                     FROM profile_change_requests pcr
                     JOIN employees e ON pcr.employee_id = e.id
                     LEFT JOIN departments d ON e.department_id = d.id
                     LEFT JOIN positions p ON e.position_id = p.id
                     WHERE pcr.id = :id LIMIT 1",
                    ['id' => $recordId]
                );
                $row = $db->fetch();
                if ($row) {
                    $details = [
                        'title'          => "Đổi " . ($row['field_label'] ?: $row['field_name']),
                        'employee_name'  => $row['employee_name'],
                        'emp_code'       => $row['emp_code'],
                        'dept_name'      => $row['dept_name'] ?? '—',
                        'field_name'     => $row['field_name'],
                        'field_label'    => $row['field_label'] ?: $row['field_name'],
                        'old_value'      => $row['old_value'],
                        'new_value'      => $row['new_value'],
                        'request_type'   => $row['request_type'],
                        'summary'        => "Cập nhật " . ($row['field_label'] ?: $row['field_name']) . " từ '{$row['old_value']}' thành '{$row['new_value']}'"
                    ];
                }
                break;

            case 'leave':
                $db->query(
                    "SELECT lr.*, lt.name as leave_type_name, e.emp_code, e.full_name as employee_name, d.dept_name
                     FROM leave_requests lr
                     JOIN employees e ON lr.employee_id = e.id
                     LEFT JOIN leave_types lt ON lr.leave_type_id = lt.id
                     LEFT JOIN departments d ON e.department_id = d.id
                     WHERE lr.id = :id LIMIT 1",
                    ['id' => $recordId]
                );
                $row = $db->fetch();
                if ($row) {
                    $details = [
                        'title'         => "Nghỉ " . ($row['leave_type_name'] ?? 'phép') . " ({$row['total_days']} ngày)",
                        'employee_name' => $row['employee_name'],
                        'emp_code'      => $row['emp_code'],
                        'dept_name'     => $row['dept_name'] ?? '—',
                        'start_date'    => $row['start_date'],
                        'end_date'      => $row['end_date'],
                        'total_days'    => $row['total_days'],
                        'summary'       => "Nghỉ từ " . date('d/m/Y', strtotime($row['start_date'])) . " đến " . date('d/m/Y', strtotime($row['end_date'])) . ": " . ($row['reason'] ?? '—')
                    ];
                }
                break;

            case 'loan':
                $db->query(
                    "SELECT el.*, e.emp_code, e.full_name as employee_name, d.dept_name
                     FROM employee_loans el
                     JOIN employees e ON el.employee_id = e.id
                     LEFT JOIN departments d ON e.department_id = d.id
                     WHERE el.id = :id LIMIT 1",
                    ['id' => $recordId]
                );
                $row = $db->fetch();
                if ($row) {
                    $details = [
                        'title'         => "Vay/Tạm ứng " . number_format($row['amount'] ?? 0) . " đ",
                        'employee_name' => $row['employee_name'],
                        'emp_code'      => $row['emp_code'],
                        'dept_name'     => $row['dept_name'] ?? '—',
                        'amount'        => $row['amount'],
                        'term_months'   => $row['term_months'] ?? 1,
                        'summary'       => "Mã vay {$row['loan_code']} - Số tiền: " . number_format($row['amount']) . " đ (" . ($row['term_months'] ?? 1) . " tháng). Lý do: " . ($row['reason'] ?? '—')
                    ];
                }
                break;

            case 'expense':
                $db->query(
                    "SELECT ec.*, e.emp_code, e.full_name as employee_name, p.project_name
                     FROM expense_claims ec
                     JOIN employees e ON ec.employee_id = e.id
                     LEFT JOIN projects p ON ec.project_id = p.id
                     WHERE ec.id = :id LIMIT 1",
                    ['id' => $recordId]
                );
                $row = $db->fetch();
                if ($row) {
                    $details = [
                        'title'         => "Thanh toán " . ($row['title'] ?: 'Chi phí') . " (" . number_format($row['total_amount'] ?? 0) . " đ)",
                        'employee_name' => $row['employee_name'],
                        'emp_code'      => $row['emp_code'],
                        'dept_name'     => $row['project_name'] ?? 'Dự án chung',
                        'total_amount'  => $row['total_amount'],
                        'category'      => $row['category'] ?? 'Khác',
                        'summary'       => "Mã đơn {$row['claim_code']} - " . number_format($row['total_amount']) . " đ. Danh mục: " . ($row['category'] ?? '—')
                    ];
                }
                break;

            case 'transfer':
                $db->query(
                    "SELECT t.*, p1.project_name as from_proj, p2.project_name as to_proj
                     FROM transfer_orders t
                     LEFT JOIN projects p1 ON t.from_project_id = p1.id
                     LEFT JOIN projects p2 ON t.to_project_id = p2.id
                     WHERE t.id = :id LIMIT 1",
                    ['id' => $recordId]
                );
                $row = $db->fetch();
                if ($row) {
                    $details = [
                        'title'         => "Điều chuyển {$row['order_code']}",
                        'employee_name' => 'Nhân sự điều chuyển',
                        'emp_code'      => 'ORDER',
                        'dept_name'     => ($row['from_proj'] ?? 'Dự án A') . " → " . ($row['to_proj'] ?? 'Dự án B'),
                        'effective_date'=> $row['effective_date'] ?? null,
                        'summary'       => "Điều chuyển từ " . ($row['from_proj'] ?? 'Site cũ') . " sang " . ($row['to_proj'] ?? 'Site mới') . ". Lý do: " . ($row['reason'] ?? '—')
                    ];
                }
                break;
        }

        return $details;
    }

    /**
     * Tự động đồng bộ các bản ghi Pending từ các bảng nghiệp vụ cũ vào approval_requests
     */
    public static function syncLegacyPendingRequests(): void
    {
        try {
            $db = Database::getInstance();

            // 1. Sync Leave Requests
            $db->query("
                SELECT lr.id, lr.employee_id, u.id as user_id 
                FROM leave_requests lr
                LEFT JOIN users u ON u.employee_id = lr.employee_id
                WHERE lr.status = 'Pending'
                  AND NOT EXISTS (
                      SELECT 1 FROM approval_requests ar 
                      WHERE ar.module = 'leave' AND ar.record_id = lr.id
                  )
            ");
            $leaves = $db->fetchAll();
            foreach ($leaves as $l) {
                self::submit('leave', (int)$l['id'], $l['user_id'] ? (int)$l['user_id'] : 1);
            }

            // 2. Sync Profile Change Requests
            $db->query("
                SELECT pcr.id, pcr.requested_by
                FROM profile_change_requests pcr
                WHERE pcr.status = 'Pending'
                  AND NOT EXISTS (
                      SELECT 1 FROM approval_requests ar 
                      WHERE ar.module = 'profile_change' AND ar.record_id = pcr.id
                  )
            ");
            $pcrs = $db->fetchAll();
            foreach ($pcrs as $p) {
                self::submit('profile_change', (int)$p['id'], (int)$p['requested_by']);
            }

            // 3. Sync Loan Requests
            $db->query("
                SELECT el.id, el.employee_id, u.id as user_id
                FROM employee_loans el
                LEFT JOIN users u ON u.employee_id = el.employee_id
                WHERE el.status = 'Pending'
                  AND NOT EXISTS (
                      SELECT 1 FROM approval_requests ar 
                      WHERE ar.module = 'loan' AND ar.record_id = el.id
                  )
            ");
            $loans = $db->fetchAll();
            foreach ($loans as $ln) {
                self::submit('loan', (int)$ln['id'], $ln['user_id'] ? (int)$ln['user_id'] : 1);
            }

            // 4. Sync Expense Claims
            $db->query("
                SELECT ec.id, ec.employee_id, u.id as user_id
                FROM expense_claims ec
                LEFT JOIN users u ON u.employee_id = ec.employee_id
                WHERE ec.status = 'Submitted'
                  AND NOT EXISTS (
                      SELECT 1 FROM approval_requests ar 
                      WHERE ar.module = 'expense' AND ar.record_id = ec.id
                  )
            ");
            $expenses = $db->fetchAll();
            foreach ($expenses as $ex) {
                self::submit('expense', (int)$ex['id'], $ex['user_id'] ? (int)$ex['user_id'] : 1);
            }

            // 5. Sync Transfer Orders
            $db->query("
                SELECT t.id, t.created_by
                FROM transfer_orders t
                WHERE t.status = 'Pending'
                  AND NOT EXISTS (
                      SELECT 1 FROM approval_requests ar 
                      WHERE ar.module = 'transfer' AND ar.record_id = t.id
                  )
            ");
            $transfers = $db->fetchAll();
            foreach ($transfers as $tr) {
                self::submit('transfer', (int)$tr['id'], $tr['created_by'] ? (int)$tr['created_by'] : 1);
            }
        } catch (Throwable $e) {
            error_log("[WorkflowService::syncLegacyPendingRequests] Error: " . $e->getMessage());
        }
    }

    /**
     * Tạo yêu cầu đổi thông tin hồ sơ cho nhân viên (Profile Change Request)
     */
    public static function createProfileChangeRequest(
        int $empId,
        int $userId,
        string $type,
        string $fieldName,
        string $fieldLabel,
        ?string $oldVal,
        ?string $newVal,
        ?string $notes = ''
    ): ?int {
        try {
            $db = Database::getInstance();

            // Nếu giá trị không đổi thì bỏ qua
            if (trim((string)$oldVal) === trim((string)$newVal)) {
                return null;
            }

            // Kiểm tra xem đã có request đổi trường này đang Pending chưa
            $db->query(
                "SELECT id FROM profile_change_requests 
                 WHERE employee_id = :e AND field_name = :f AND status = 'Pending' 
                 LIMIT 1",
                ['e' => $empId, 'f' => $fieldName]
            );
            $exist = $db->fetch();
            if ($exist) {
                // Cập nhật lại giá trị mới cho request đang Pending
                $db->query(
                    "UPDATE profile_change_requests 
                     SET new_value = :nv, notes = :notes, updated_at = NOW() 
                     WHERE id = :id",
                    ['nv' => $newVal, 'notes' => $notes, 'id' => $exist['id']]
                );
                return (int)$exist['id'];
            }

            $db->query(
                "INSERT INTO profile_change_requests 
                    (employee_id, requested_by, request_type, field_name, field_label, old_value, new_value, status, notes, created_at)
                 VALUES 
                    (:emp_id, :req_by, :type, :field, :label, :old_val, :new_val, 'Pending', :notes, NOW())",
                [
                    'emp_id'  => $empId,
                    'req_by'  => $userId,
                    'type'    => $type,
                    'field'   => $fieldName,
                    'label'   => $fieldLabel,
                    'old_val' => $oldVal,
                    'new_val' => $newVal,
                    'notes'   => $notes
                ]
            );
            $pcrId = (int)$db->lastInsertId();

            // Tạo approval request vào hệ thống Workflow
            self::submit('profile_change', $pcrId, $userId);

            return $pcrId;
        } catch (Throwable $e) {
            error_log("[WorkflowService::createProfileChangeRequest] Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Lấy danh sách Profile Change Requests (Dành cho HR / Diff view)
     */
    public static function getProfileChanges(?string $status = null, ?int $empId = null): array
    {
        $db = Database::getInstance();

        $sql = "
            SELECT pcr.*, 
                   e.emp_code, e.full_name as employee_name, e.avatar_path,
                   d.dept_name, pos.pos_title,
                   u_req.username as requester_name,
                   IFNULL(e_rev.full_name, u_rev.username) as reviewer_name,
                   ar.id as approval_request_id,
                   ar.current_level
            FROM profile_change_requests pcr
            JOIN employees e ON pcr.employee_id = e.id
            LEFT JOIN departments d ON e.department_id = d.id
            LEFT JOIN positions pos ON e.position_id = pos.id
            LEFT JOIN users u_req ON pcr.requested_by = u_req.id
            LEFT JOIN users u_rev ON pcr.reviewed_by = u_rev.id
            LEFT JOIN employees e_rev ON u_rev.employee_id = e_rev.id
            LEFT JOIN approval_requests ar ON (ar.module = 'profile_change' AND ar.record_id = pcr.id)
            WHERE 1=1
        ";
        $params = [];

        if ($status && $status !== 'all') {
            $sql .= " AND pcr.status = :st";
            $params['st'] = $status;
        }

        if ($empId) {
            $sql .= " AND pcr.employee_id = :emp";
            $params['emp'] = $empId;
        }

        $sql .= " ORDER BY pcr.created_at DESC, pcr.id DESC";

        $db->query($sql, $params);
        return $db->fetchAll();
    }

    /**
     * Gửi thông báo đến người có trách nhiệm duyệt cho Cấp cụ thể
     */
    protected static function notifyApproversForLevel(int $requestId, int $level, string $triggerType): void
    {
        try {
            $db = Database::getInstance();
            $db->query(
                "SELECT ar.*, af.steps, IFNULL(e.full_name, u.username) as requester_name 
                 FROM approval_requests ar
                 LEFT JOIN approval_flows af ON ar.flow_id = af.id
                 LEFT JOIN users u ON ar.created_by = u.id
                 LEFT JOIN employees e ON u.employee_id = e.id
                 WHERE ar.id = :id LIMIT 1",
                ['id' => $requestId]
            );
            $req = $db->fetch();
            if (!$req) return;

            $modLabels = self::getModuleLabels();
            $modName = $modLabels[$req['module']] ?? $req['module'];
            $reqName = $req['requester_name'] ?? 'Nhân viên';

            // Tìm user IDs nhận thông báo
            // Mặc định: Gửi cho các user có role Quản lý hoặc HR (role level 0, 1, 2)
            $db->query("SELECT id FROM users WHERE role_id IN (1, 2, 3, 4, 5, 6) LIMIT 10");
            $admins = $db->fetchAll();

            foreach ($admins as $adm) {
                NotificationService::send(
                    (int)$adm['id'],
                    'approval',
                    "Yêu cầu {$modName} chờ duyệt (Cấp {$level})",
                    "Đơn từ {$reqName} đang chờ bạn phê duyệt tại Hộp duyệt tập trung.",
                    "workflow/pendingApprovals"
                );
            }
        } catch (Throwable $e) {
            error_log("[WorkflowService::notifyApproversForLevel] Error: " . $e->getMessage());
        }
    }

    /**
     * Map tên hiển thị các module
     */
    public static function getModuleLabels(): array
    {
        return [
            'leave'          => 'Nghỉ phép',
            'loan'           => 'Vay vốn / Tạm ứng',
            'expense'        => 'Chi phí & Công tác',
            'transfer'       => 'Điều chuyển nhân sự',
            'profile_change' => 'Sửa đổi hồ sơ'
        ];
    }

    /**
     * Lấy link chi tiết của nghiệp vụ
     */
    public static function getModuleDetailLink(string $module, int $recordId): string
    {
        switch ($module) {
            case 'profile_change':
                return "workflow/profileChanges";
            case 'leave':
                return "leave/view/{$recordId}";
            case 'loan':
                return "loan/show/{$recordId}";
            case 'expense':
                return "expense/show/{$recordId}";
            case 'transfer':
                return "transfer/show/{$recordId}";
            default:
                return "workflow/pendingApprovals";
        }
    }
}
