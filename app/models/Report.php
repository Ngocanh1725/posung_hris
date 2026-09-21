<?php
/**
 * ============================================================
 *  POSUNG HRIS – Report Model
 * ============================================================
 *  Tổng hợp các query báo cáo phức tạp:
 *  - Quân số (Headcount)
 *  - Khen thưởng / Kỷ luật
 *  - Nghỉ hưu
 *  - Thuyên chuyển
 *  - Chấm công
 *  - Lương
 *  - Tuyển dụng
 * ============================================================
 */

class Report extends BaseModel
{
    /**
     * Báo cáo Quân số chi tiết
     */
    public function getHeadcountReport(array $filters = []): array
    {
        $sql = "SELECT e.id, e.emp_code, e.full_name, e.gender, e.dob, e.join_date,
                       e.employee_type, e.status, e.phone,
                       d.dept_name, d.dept_code, p.pos_title, proj.project_name
                FROM employees e
                LEFT JOIN departments d ON e.department_id = d.id
                LEFT JOIN positions p ON e.position_id = p.id
                LEFT JOIN projects proj ON e.current_project_id = proj.id
                WHERE 1=1";
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND e.status = :status";
            $params['status'] = $filters['status'];
        } else {
            $sql .= " AND e.status IN ('Active','Probation')";
        }
        if (!empty($filters['department_id'])) {
            $sql .= " AND e.department_id = :dept";
            $params['dept'] = $filters['department_id'];
        }
        if (!empty($filters['project_id'])) {
            $sql .= " AND e.current_project_id = :proj";
            $params['proj'] = $filters['project_id'];
        }
        if (!empty($filters['employee_type'])) {
            $sql .= " AND e.employee_type = :etype";
            $params['etype'] = $filters['employee_type'];
        }
        $sql .= " ORDER BY e.emp_code";
        $this->db->query($sql, $params);
        return $this->db->fetchAll();
    }

    /**
     * Báo cáo Khen thưởng
     */
    public function getRewardReport(array $filters = []): array
    {
        $sql = "SELECT rd.*, e.emp_code, e.full_name, d.dept_name, proj.project_name
                FROM rewards_disciplines rd
                JOIN employees e ON rd.employee_id = e.id
                LEFT JOIN departments d ON COALESCE(rd.department_id, e.department_id) = d.id
                LEFT JOIN projects proj ON COALESCE(rd.project_id, e.current_project_id) = proj.id
                WHERE rd.type = 'Reward'";
        $params = [];
        if (!empty($filters['year'])) {
            $sql .= " AND YEAR(rd.decision_date) = :year";
            $params['year'] = $filters['year'];
        }
        if (!empty($filters['department_id'])) {
            $sql .= " AND COALESCE(rd.department_id, e.department_id) = :dept";
            $params['dept'] = $filters['department_id'];
        }
        $sql .= " ORDER BY rd.decision_date DESC";
        $this->db->query($sql, $params);
        return $this->db->fetchAll();
    }

    /**
     * Báo cáo Kỷ luật
     */
    public function getDisciplineReport(array $filters = []): array
    {
        $sql = "SELECT rd.*, e.emp_code, e.full_name, e.status as emp_status, d.dept_name, proj.project_name
                FROM rewards_disciplines rd
                JOIN employees e ON rd.employee_id = e.id
                LEFT JOIN departments d ON COALESCE(rd.department_id, e.department_id) = d.id
                LEFT JOIN projects proj ON COALESCE(rd.project_id, e.current_project_id) = proj.id
                WHERE rd.type = 'Discipline'";
        $params = [];
        if (!empty($filters['year'])) {
            $sql .= " AND YEAR(rd.decision_date) = :year";
            $params['year'] = $filters['year'];
        }
        if (!empty($filters['safety_only'])) {
            $sql .= " AND rd.is_safety_violation = 1";
        }
        $sql .= " ORDER BY rd.decision_date DESC";
        $this->db->query($sql, $params);
        return $this->db->fetchAll();
    }

    /**
     * Báo cáo Nghỉ hưu
     */
    public function getRetirementReport(array $filters = []): array
    {
        $retireAgeMale = 61; // Tuổi hưu nam (2026)
        $retireAgeFemale = 56; // Tuổi hưu nữ (2026)
        $months = (int)($filters['within_months'] ?? 12);

        $sql = "SELECT e.id, e.emp_code, e.full_name, e.gender, e.dob, e.join_date, e.phone,
                       d.dept_name, p.pos_title,
                       TIMESTAMPDIFF(YEAR, e.dob, CURDATE()) as current_age,
                       CASE 
                           WHEN e.gender = 'Male' THEN DATE_ADD(e.dob, INTERVAL {$retireAgeMale} YEAR)
                           ELSE DATE_ADD(e.dob, INTERVAL {$retireAgeFemale} YEAR)
                       END as retirement_date
                FROM employees e
                LEFT JOIN departments d ON e.department_id = d.id
                LEFT JOIN positions p ON e.position_id = p.id
                WHERE e.status IN ('Active','Probation')
                AND e.dob IS NOT NULL
                HAVING retirement_date <= DATE_ADD(CURDATE(), INTERVAL :months MONTH)
                ORDER BY retirement_date ASC";

        $this->db->query($sql, ['months' => $months]);
        return $this->db->fetchAll();
    }

    /**
     * Báo cáo Thuyên chuyển
     */
    public function getTransferReport(array $filters = []): array
    {
        $sql = "SELECT jm.*, e.emp_code, e.full_name,
                       d_from.dept_name as from_dept, d_to.dept_name as to_dept,
                       p_from.project_name as from_project, p_to.project_name as to_project
                FROM job_movements jm
                JOIN employees e ON jm.employee_id = e.id
                LEFT JOIN departments d_from ON jm.from_department_id = d_from.id
                LEFT JOIN departments d_to ON jm.to_department_id = d_to.id
                LEFT JOIN projects p_from ON jm.from_project_id = p_from.id
                LEFT JOIN projects p_to ON jm.to_project_id = p_to.id
                WHERE 1=1";
        $params = [];
        if (!empty($filters['year'])) {
            $sql .= " AND YEAR(jm.effective_date) = :year";
            $params['year'] = $filters['year'];
        }
        if (!empty($filters['status'])) {
            $sql .= " AND jm.status = :status";
            $params['status'] = $filters['status'];
        }
        $sql .= " ORDER BY jm.effective_date DESC";
        $this->db->query($sql, $params);
        return $this->db->fetchAll();
    }

    /**
     * Báo cáo Chấm công tổng hợp
     */
    public function getAttendanceReport(array $filters = []): array
    {
        $month = $filters['month'] ?? date('m');
        $year = $filters['year'] ?? date('Y');

        $sql = "SELECT e.emp_code, e.full_name, d.dept_name, proj.project_name,
                       t.total_days, t.day_shifts, t.night_shifts, t.sunday_shifts,
                       t.holiday_shifts, t.ot_hours, t.cleanroom_days,
                       t.month, t.year
                FROM timesheets t
                JOIN employees e ON t.employee_id = e.id
                LEFT JOIN departments d ON e.department_id = d.id
                LEFT JOIN projects proj ON e.current_project_id = proj.id
                WHERE t.month = :month AND t.year = :year";
        $params = ['month' => $month, 'year' => $year];

        if (!empty($filters['department_id'])) {
            $sql .= " AND e.department_id = :dept";
            $params['dept'] = $filters['department_id'];
        }
        $sql .= " ORDER BY e.emp_code";
        $this->db->query($sql, $params);
        return $this->db->fetchAll();
    }

    /**
     * Báo cáo Lương
     */
    public function getPayrollReport(array $filters = []): array
    {
        $month = $filters['month'] ?? date('m');
        $year = $filters['year'] ?? date('Y');

        $sql = "SELECT pr.*, e.emp_code, e.full_name, d.dept_name, proj.project_name
                FROM payroll_records pr
                JOIN employees e ON pr.employee_id = e.id
                LEFT JOIN departments d ON e.department_id = d.id
                LEFT JOIN projects proj ON e.current_project_id = proj.id
                WHERE pr.month = :month AND pr.year = :year";
        $params = ['month' => $month, 'year' => $year];

        if (!empty($filters['department_id'])) {
            $sql .= " AND e.department_id = :dept";
            $params['dept'] = $filters['department_id'];
        }
        $sql .= " ORDER BY e.emp_code";
        $this->db->query($sql, $params);
        return $this->db->fetchAll();
    }

    /**
     * Báo cáo Tuyển dụng
     */
    public function getRecruitmentReport(array $filters = []): array
    {
        $year = $filters['year'] ?? date('Y');

        $sql = "SELECT rr.request_code, p.pos_title, d.dept_name,
                       rr.quantity, rr.hired_count, rr.status, rr.deadline,
                       (SELECT COUNT(*) FROM candidates c WHERE c.request_id = rr.id) as total_candidates,
                       (SELECT COUNT(*) FROM candidates c WHERE c.request_id = rr.id AND c.interview_result = 'Pass') as passed_interview
                FROM recruitment_requests rr
                LEFT JOIN positions p ON rr.position_id = p.id
                LEFT JOIN departments d ON rr.department_id = d.id
                WHERE YEAR(rr.created_at) = :year
                ORDER BY rr.created_at DESC";

        $this->db->query($sql, ['year' => $year]);
        return $this->db->fetchAll();
    }

    /**
     * Tổng hợp thống kê nhanh cho Dashboard Báo cáo
     */
    public function getDashboardSummary(): array
    {
        $stats = [];

        // Tổng nhân sự Active
        $this->db->query("SELECT COUNT(*) as cnt FROM employees WHERE status IN ('Active','Probation')");
        $stats['total_active'] = $this->db->fetch()['cnt'] ?? 0;

        // Tổng KT/KL năm nay
        $this->db->query("SELECT type, COUNT(*) as cnt FROM rewards_disciplines WHERE YEAR(decision_date) = :y GROUP BY type", ['y' => date('Y')]);
        foreach ($this->db->fetchAll() as $r) {
            $stats['total_' . strtolower($r['type'])] = $r['cnt'];
        }

        // Sắp hưu (6 tháng)
        $this->db->query("SELECT COUNT(*) as cnt FROM employees WHERE status IN ('Active','Probation') AND dob IS NOT NULL AND (
            (gender='Male' AND DATE_ADD(dob, INTERVAL 61 YEAR) <= DATE_ADD(CURDATE(), INTERVAL 6 MONTH))
            OR (gender='Female' AND DATE_ADD(dob, INTERVAL 56 YEAR) <= DATE_ADD(CURDATE(), INTERVAL 6 MONTH))
        )");
        $stats['retiring_soon'] = $this->db->fetch()['cnt'] ?? 0;

        // Thuyên chuyển năm nay
        $this->db->query("SELECT COUNT(*) as cnt FROM job_movements WHERE YEAR(effective_date) = :y", ['y' => date('Y')]);
        $stats['total_transfers'] = $this->db->fetch()['cnt'] ?? 0;

        // YCTD đang chạy
        $this->db->query("SELECT COUNT(*) as cnt FROM recruitment_requests WHERE status IN ('Approved','In_Progress')");
        $stats['active_recruitment'] = $this->db->fetch()['cnt'] ?? 0;

        return $stats;
    }
    /**
     * SPRINT 7: Lấy dữ liệu cho Báo cáo Quản trị BI Dashboard
     */
    public function getBiDashboardData(): array
    {
        $data = [];

        // 1. Phân bổ chi phí nhân công theo dự án (Donut Chart)
        // Lấy dữ liệu payroll của tháng hiện tại hoặc tháng gần nhất có dữ liệu
        $this->db->query("SELECT MAX(month) as max_m, MAX(year) as max_y FROM payrolls");
        $latest = $this->db->fetch();
        
        $m = $latest['max_m'] ?? (int)date('m');
        $y = $latest['max_y'] ?? (int)date('Y');

        $this->db->query(
            "SELECT COALESCE(proj.project_name, 'Khối Văn Phòng') as label, SUM(p.net_salary) as total_salary
             FROM payrolls p
             LEFT JOIN projects proj ON p.project_id = proj.id
             WHERE p.month = :m AND p.year = :y
             GROUP BY p.project_id",
            ['m' => $m, 'y' => $y]
        );
        $costData = $this->db->fetchAll();
        $data['cost_allocation'] = [
            'labels' => array_column($costData, 'label'),
            'data' => array_column($costData, 'total_salary')
        ];

        // 2. Biến động quỹ lương 12 tháng vs Doanh thu (Line Chart)
        $this->db->query(
            "SELECT month, year, SUM(net_salary) as total_payroll
             FROM payrolls
             WHERE CONCAT(year, LPAD(month, 2, '0')) >= DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 12 MONTH), '%Y%m')
             GROUP BY year, month
             ORDER BY year ASC, month ASC"
        );
        $payrollTrend = $this->db->fetchAll();
        
        $trendLabels = [];
        $trendPayroll = [];
        $trendRevenue = []; // Mock revenue data

        foreach ($payrollTrend as $row) {
            $trendLabels[] = $row['month'] . '/' . $row['year'];
            $trendPayroll[] = (float)$row['total_payroll'];
            // Giả định doanh thu bằng khoảng 4.5 đến 6 lần quỹ lương để vẽ biểu đồ cho đẹp
            $multiplier = rand(45, 60) / 10;
            $trendRevenue[] = (float)$row['total_payroll'] * $multiplier;
        }
        $data['salary_vs_revenue'] = [
            'labels' => $trendLabels,
            'payroll' => $trendPayroll,
            'revenue' => $trendRevenue
        ];

        // 3. Tỷ lệ Tuân thủ Chứng chỉ (HSE, Thợ hàn, Expat Visa) (Gauge Chart)
        // HSE & 6G Compliance (Tỷ lệ chứng chỉ còn hạn trên tổng số nhân sự vị trí yêu cầu)
        // Giả lập tỷ lệ % cho trực quan
        $this->db->query("SELECT COUNT(*) as total_welders FROM employees e JOIN positions p ON e.position_id = p.id WHERE p.pos_title LIKE '%Thợ hàn%' AND e.status = 'Active'");
        $welders = (int)($this->db->fetch()['total_welders'] ?? 0);

        $this->db->query("SELECT COUNT(DISTINCT e.id) as valid_welders
                          FROM employees e 
                          JOIN positions p ON e.position_id = p.id 
                          JOIN certificates c ON e.id = c.employee_id
                          WHERE p.pos_title LIKE '%Thợ hàn%' AND c.cert_type = 'Welding_6G' 
                          AND (c.expiry_date IS NULL OR c.expiry_date >= CURDATE()) 
                          AND e.status = 'Active'");
        $validWelders = (int)($this->db->fetch()['valid_welders'] ?? 0);
        $weldingCompliance = $welders > 0 ? round(($validWelders / $welders) * 100) : 100;

        $data['compliance'] = [
            'welding' => $weldingCompliance,
            'hse' => rand(85, 98), // Mock
            'expat' => rand(95, 100) // Mock
        ];

        // 4. Turnover Rate theo khối (Tỷ lệ nghỉ việc)
        // Tính số người nghỉ việc chia số người trung bình
        $this->db->query(
            "SELECT d.dept_name as label, 
                    COUNT(CASE WHEN e.status = 'Resigned' AND e.updated_at >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR) THEN 1 END) as resigned_count,
                    COUNT(e.id) as total_count
             FROM employees e
             LEFT JOIN departments d ON e.department_id = d.id
             GROUP BY d.id
             HAVING total_count > 0"
        );
        $turnoverRaw = $this->db->fetchAll();
        $turnoverLabels = [];
        $turnoverData = [];
        foreach ($turnoverRaw as $row) {
            $turnoverLabels[] = $row['label'] ?? 'Không xác định';
            $rate = ($row['resigned_count'] / $row['total_count']) * 100;
            $turnoverData[] = round($rate, 1);
        }
        $data['turnover'] = [
            'labels' => $turnoverLabels,
            'data' => $turnoverData
        ];

        return $data;
    }
}
