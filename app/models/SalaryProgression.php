<?php
/**
 * ============================================================
 *  POSUNG HRIS – SalaryProgression Model
 * ============================================================
 *  Quản lý Diễn biến Lương & Nâng bậc nhân sự (Salary Progression)
 *  Timeline lịch sử lương, Đề xuất nâng lương, Tăng lương hàng loạt & Báo cáo BI
 * ============================================================
 */

class SalaryProgression extends BaseModel
{
    protected string $table = 'salary_progressions';

    /**
     * Lấy toàn bộ lịch sử nâng bậc của 1 nhân viên (kèm tính toán % tăng)
     */
    public function getByEmployee(int $employeeId, string $order = 'DESC'): array
    {
        $direction = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';
        $sql = "SELECT sp.*,
                       COALESCE(sp.decision_number, sp.decision_no) AS decision_num,
                       e.emp_code,
                       e.full_name AS employee_name,
                       d.dept_name,
                       p.pos_title,
                       u.username AS approver_username,
                       COALESCE(u_emp.full_name, u.username) AS approver_name
                FROM `{$this->table}` sp
                JOIN `employees` e ON sp.employee_id = e.id
                LEFT JOIN `departments` d ON e.department_id = d.id
                LEFT JOIN `positions` p ON e.position_id = p.id
                LEFT JOIN `users` u ON sp.approved_by = u.id
                LEFT JOIN `employees` u_emp ON u.employee_id = u_emp.id
                WHERE sp.employee_id = :eid
                ORDER BY sp.effective_date {$direction}, sp.id {$direction}";

        $this->db->query($sql, ['eid' => $employeeId]);
        $rows = $this->db->fetchAll();

        $results = [];
        foreach ($rows as $row) {
            $obj = (object)$row;
            $old = (float)($obj->old_salary ?? 0);
            $new = (float)($obj->new_salary ?? $obj->base_salary ?? 0);
            
            $diff = $new - $old;
            $percent = ($old > 0) ? round(($diff / $old) * 100, 2) : 0;

            $obj->increase_amount = $diff;
            $obj->increase_percent = $percent;
            $results[] = $obj;
        }

        return $results;
    }

    /**
     * Lấy danh sách biến động lương trên toàn hệ thống kèm bộ lọc
     */
    public function getAllWithFilters(array $filters = []): array
    {
        $sql = "SELECT sp.*,
                       COALESCE(sp.decision_number, sp.decision_no) AS decision_num,
                       e.emp_code,
                       e.full_name AS employee_name,
                       e.current_project_id,
                       d.dept_name,
                       p.pos_title,
                       u.username AS approver_username,
                       COALESCE(u_emp.full_name, u.username) AS approver_name
                FROM `{$this->table}` sp
                JOIN `employees` e ON sp.employee_id = e.id
                LEFT JOIN `departments` d ON e.department_id = d.id
                LEFT JOIN `positions` p ON e.position_id = p.id
                LEFT JOIN `users` u ON sp.approved_by = u.id
                LEFT JOIN `employees` u_emp ON u.employee_id = u_emp.id
                WHERE 1=1";

        $params = [];

        if (!empty($filters['search'])) {
            $sql .= " AND (e.full_name LIKE :search OR e.emp_code LIKE :search2 OR sp.decision_number LIKE :search3)";
            $params['search']  = "%{$filters['search']}%";
            $params['search2'] = "%{$filters['search']}%";
            $params['search3'] = "%{$filters['search']}%";
        }

        if (!empty($filters['department_id'])) {
            $sql .= " AND e.department_id = :dept_id";
            $params['dept_id'] = (int)$filters['department_id'];
        }

        if (!empty($filters['project_id'])) {
            $sql .= " AND e.current_project_id = :project_id";
            $params['project_id'] = (int)$filters['project_id'];
        }

        if (!empty($filters['year'])) {
            $sql .= " AND YEAR(sp.effective_date) = :year";
            $params['year'] = (int)$filters['year'];
        }

        if (!empty($filters['reason'])) {
            $sql .= " AND sp.reason LIKE :reason";
            $params['reason'] = "%{$filters['reason']}%";
        }

        $sql .= " ORDER BY sp.effective_date DESC, sp.id DESC";

        $this->db->query($sql, $params);
        $rows = $this->db->fetchAll();

        $results = [];
        foreach ($rows as $row) {
            $obj = (object)$row;
            $old = (float)($obj->old_salary ?? 0);
            $new = (float)($obj->new_salary ?? $obj->base_salary ?? 0);
            $diff = $new - $old;
            $percent = ($old > 0) ? round(($diff / $old) * 100, 2) : 0;
            $obj->increase_amount = $diff;
            $obj->increase_percent = $percent;
            $results[] = $obj;
        }

        return $results;
    }

    /**
     * Lấy mức lương cơ bản hiện tại của 1 nhân viên
     */
    public function getCurrentSalary(int $employeeId): float
    {
        // 1. Tìm bản ghi gần nhất trong salary_progressions
        $this->db->query(
            "SELECT new_salary, base_salary FROM `{$this->table}` 
             WHERE employee_id = :eid 
             ORDER BY effective_date DESC, id DESC LIMIT 1",
            ['eid' => $employeeId]
        );
        $prog = $this->db->fetch();
        if ($prog && !empty($prog['new_salary'])) {
            return (float)$prog['new_salary'];
        }
        if ($prog && !empty($prog['base_salary'])) {
            return (float)$prog['base_salary'];
        }

        // 2. Nếu chưa có trong progressions, lấy từ bảng salaries
        $this->db->query(
            "SELECT base_salary FROM `salaries` 
             WHERE employee_id = :eid 
             ORDER BY effective_date DESC, id DESC LIMIT 1",
            ['eid' => $employeeId]
        );
        $sal = $this->db->fetch();
        if ($sal && isset($sal['base_salary'])) {
            return (float)$sal['base_salary'];
        }

        return 0.00;
    }

    /**
     * Tạo quyết định nâng lương đơn lẻ cho 1 nhân viên
     */
    public function createProgression(array $data): bool
    {
        try {
            $this->db->beginTransaction();

            $employeeId = (int)$data['employee_id'];
            $newSalary = (float)$data['new_salary'];
            $oldSalary = isset($data['old_salary']) ? (float)$data['old_salary'] : $this->getCurrentSalary($employeeId);
            $effectiveDate = $data['effective_date'] ?? date('Y-m-d');
            $reason = $data['reason'] ?? 'Nâng bậc lương định kỳ';
            $decisionNumber = $data['decision_number'] ?? null;
            $decisionDate = $data['decision_date'] ?? null;
            $approvedBy = $data['approved_by'] ?? null;
            $notes = $data['notes'] ?? null;

            // 1. Thêm vào salary_progressions
            $sql = "INSERT INTO `{$this->table}` 
                    (`employee_id`, `old_salary`, `new_salary`, `effective_date`, `reason`, 
                     `decision_number`, `decision_date`, `approved_by`, `notes`, `base_salary`) 
                    VALUES 
                    (:employee_id, :old_salary, :new_salary, :effective_date, :reason, 
                     :decision_number, :decision_date, :approved_by, :notes, :base_salary)";

            $this->db->query($sql, [
                'employee_id'     => $employeeId,
                'old_salary'      => $oldSalary,
                'new_salary'      => $newSalary,
                'effective_date'  => $effectiveDate,
                'reason'          => $reason,
                'decision_number' => $decisionNumber,
                'decision_date'   => $decisionDate,
                'approved_by'     => $approvedBy,
                'notes'           => $notes,
                'base_salary'     => $newSalary,
            ]);

            // 2. Thêm hoặc cập nhật bảng salaries để đồng bộ Payroll
            $salarySql = "INSERT INTO `salaries` (`employee_id`, `base_salary`, `effective_date`, `notes`)
                          VALUES (:employee_id, :base_salary, :effective_date, :notes)";
            $this->db->query($salarySql, [
                'employee_id'    => $employeeId,
                'base_salary'    => $newSalary,
                'effective_date' => $effectiveDate,
                'notes'          => "Nâng bậc lương: {$reason} (QĐ: {$decisionNumber})",
            ]);

            $this->db->commit();

            // 3. Gửi thông báo tự động đến nhân viên
            if (class_exists('NotificationService')) {
                $formattedNewSalary = number_format($newSalary, 0, ',', '.') . ' ₫';
                NotificationService::sendToEmployee(
                    $employeeId,
                    'system',
                    "Quyết định nâng bậc lương: {$formattedNewSalary}",
                    "Chúc mừng bạn! Mức lương mới của bạn là {$formattedNewSalary}, có hiệu lực từ ngày " . date('d/m/Y', strtotime($effectiveDate)) . " theo QĐ: {$decisionNumber}.",
                    "salaryProgression/index/{$employeeId}"
                );
            }

            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Lỗi createProgression: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Nâng lương hàng loạt (Batch Increase)
     */
    public function processBatchIncrease(array $data): array
    {
        $employeeIds = $data['employee_ids'] ?? [];
        if (empty($employeeIds)) {
            return ['success' => false, 'count' => 0, 'message' => 'Chưa chọn nhân viên nào'];
        }

        $increaseType = $data['increase_type'] ?? 'percent'; // 'percent' hoặc 'fixed'
        $increaseValue = (float)($data['increase_value'] ?? 0);
        $effectiveDate = $data['effective_date'] ?? date('Y-m-d');
        $reason = $data['reason'] ?? 'Điều chỉnh lương hàng loạt';
        $decisionNumber = $data['decision_number'] ?? null;
        $decisionDate = $data['decision_date'] ?? null;
        $approvedBy = $data['approved_by'] ?? null;
        $notes = $data['notes'] ?? null;

        if ($increaseValue <= 0) {
            return ['success' => false, 'count' => 0, 'message' => 'Giá trị tăng lương phải lớn hơn 0'];
        }

        $successCount = 0;
        $totalCostIncrease = 0;

        try {
            $this->db->beginTransaction();

            foreach ($employeeIds as $empId) {
                $empId = (int)$empId;
                $currentSalary = $this->getCurrentSalary($empId);

                // Nếu chưa có mức lương nào, lấy mặc định tối thiểu vùng (ví dụ 5,000,000)
                if ($currentSalary <= 0) {
                    $currentSalary = 5000000;
                }

                // Tính lương mới
                if ($increaseType === 'percent') {
                    $newSalary = round($currentSalary * (1 + $increaseValue / 100), -3); // Làm tròn đến nghìn
                } else {
                    $newSalary = $currentSalary + $increaseValue;
                }

                $diff = $newSalary - $currentSalary;
                $totalCostIncrease += $diff;

                // Lưu vào salary_progressions
                $sql = "INSERT INTO `{$this->table}` 
                        (`employee_id`, `old_salary`, `new_salary`, `effective_date`, `reason`, 
                         `decision_number`, `decision_date`, `approved_by`, `notes`, `base_salary`) 
                        VALUES 
                        (:employee_id, :old_salary, :new_salary, :effective_date, :reason, 
                         :decision_number, :decision_date, :approved_by, :notes, :base_salary)";

                $this->db->query($sql, [
                    'employee_id'     => $empId,
                    'old_salary'      => $currentSalary,
                    'new_salary'      => $newSalary,
                    'effective_date'  => $effectiveDate,
                    'reason'          => $reason,
                    'decision_number' => $decisionNumber,
                    'decision_date'   => $decisionDate,
                    'approved_by'     => $approvedBy,
                    'notes'           => $notes,
                    'base_salary'     => $newSalary,
                ]);

                // Cập nhật salaries
                $salarySql = "INSERT INTO `salaries` (`employee_id`, `base_salary`, `effective_date`, `notes`)
                              VALUES (:employee_id, :base_salary, :effective_date, :notes)";
                $this->db->query($salarySql, [
                    'employee_id'    => $empId,
                    'base_salary'    => $newSalary,
                    'effective_date' => $effectiveDate,
                    'notes'          => "Nâng lương hàng loạt: {$reason} (QĐ: {$decisionNumber})",
                ]);

                $successCount++;

                // Gửi thông báo đến nhân viên
                if (class_exists('NotificationService')) {
                    $formattedNewSalary = number_format($newSalary, 0, ',', '.') . ' ₫';
                    NotificationService::sendToEmployee(
                        $empId,
                        'system',
                        "Quyết định điều chỉnh lương: {$formattedNewSalary}",
                        "Mức lương của bạn đã được điều chỉnh thành {$formattedNewSalary}, có hiệu lực từ ngày " . date('d/m/Y', strtotime($effectiveDate)) . " theo QĐ: {$decisionNumber}.",
                        "salaryProgression/index/{$empId}"
                    );
                }
            }

            $this->db->commit();

            return [
                'success'             => true,
                'count'               => $successCount,
                'total_cost_increase' => $totalCostIncrease,
                'message'             => "Đã nâng lương thành công cho {$successCount} nhân viên!",
            ];
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Lỗi processBatchIncrease: " . $e->getMessage());
            return [
                'success' => false,
                'count'   => 0,
                'message' => 'Lỗi xử lý cơ sở dữ liệu: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Dữ liệu cho biểu đồ tăng trưởng lương Chart.js
     */
    public function getChartData(int $employeeId): array
    {
        $history = $this->getByEmployee($employeeId, 'ASC');
        $labels = [];
        $salaries = [];

        foreach ($history as $h) {
            $labels[] = date('d/m/Y', strtotime($h->effective_date));
            $salaries[] = (float)($h->new_salary ?? $h->base_salary ?? 0);
        }

        return [
            'labels'   => $labels,
            'datasets' => [
                [
                    'label'                => 'Mức lương cơ bản (VNĐ)',
                    'data'                 => $salaries,
                    'borderColor'          => '#2563eb',
                    'backgroundColor'      => 'rgba(37, 99, 235, 0.1)',
                    'fill'                 => true,
                    'tension'              => 0.3,
                    'borderWidth'          => 3,
                    'pointBackgroundColor' => '#2563eb',
                    'pointRadius'          => 5,
                    'pointHoverRadius'     => 7,
                ]
            ]
        ];
    }

    /**
     * Báo cáo Biến động lương theo Phòng ban & Dự án (Report BI)
     */
    public function getProgressionReport(array $filters = []): array
    {
        $year = !empty($filters['year']) ? (int)$filters['year'] : (int)date('Y');

        // 1. Thống kê tổng quan trong năm
        $sqlOverview = "SELECT 
                            COUNT(DISTINCT sp.employee_id) AS total_employees_increased,
                            COUNT(sp.id) AS total_decisions,
                            COALESCE(SUM(sp.new_salary - sp.old_salary), 0) AS total_monthly_increase,
                            COALESCE(AVG((sp.new_salary - sp.old_salary) / NULLIF(sp.old_salary, 0) * 100), 0) AS avg_percent_increase
                        FROM `{$this->table}` sp
                        WHERE YEAR(sp.effective_date) = :year";

        $this->db->query($sqlOverview, ['year' => $year]);
        $overview = (object)$this->db->fetch();

        // 2. Biến động theo Phòng ban
        $sqlByDept = "SELECT 
                        d.id AS department_id,
                        d.dept_name,
                        COUNT(sp.id) AS increase_count,
                        COALESCE(SUM(sp.old_salary), 0) AS old_fund,
                        COALESCE(SUM(sp.new_salary), 0) AS new_fund,
                        COALESCE(SUM(sp.new_salary - sp.old_salary), 0) AS fund_diff,
                        COALESCE(AVG((sp.new_salary - sp.old_salary) / NULLIF(sp.old_salary, 0) * 100), 0) AS avg_percent
                      FROM `{$this->table}` sp
                      JOIN `employees` e ON sp.employee_id = e.id
                      JOIN `departments` d ON e.department_id = d.id
                      WHERE YEAR(sp.effective_date) = :year
                      GROUP BY d.id, d.dept_name
                      ORDER BY fund_diff DESC";

        $this->db->query($sqlByDept, ['year' => $year]);
        $byDept = json_decode(json_encode($this->db->fetchAll()));

        // 3. Biến động theo Dự án
        $sqlByProject = "SELECT 
                            pr.id AS project_id,
                            pr.project_name,
                            pr.project_code,
                            COUNT(sp.id) AS increase_count,
                            COALESCE(SUM(sp.old_salary), 0) AS old_fund,
                            COALESCE(SUM(sp.new_salary), 0) AS new_fund,
                            COALESCE(SUM(sp.new_salary - sp.old_salary), 0) AS fund_diff
                         FROM `{$this->table}` sp
                         JOIN `employees` e ON sp.employee_id = e.id
                         JOIN `projects` pr ON e.current_project_id = pr.id
                         WHERE YEAR(sp.effective_date) = :year
                         GROUP BY pr.id, pr.project_name, pr.project_code
                         ORDER BY fund_diff DESC";

        $this->db->query($sqlByProject, ['year' => $year]);
        $byProject = json_decode(json_encode($this->db->fetchAll()));

        // 4. Danh sách các đợt nâng bậc gần nhất
        $recentIncreases = $this->getAllWithFilters(['year' => $year]);

        return [
            'overview'        => $overview,
            'byDept'          => $byDept,
            'byProject'       => $byProject,
            'recentIncreases' => $recentIncreases,
            'year'            => $year,
        ];
    }
}
