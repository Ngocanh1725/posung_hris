<?php
/**
 * ============================================================
 *  POSUNG HRIS – Model Insurance (Bảo hiểm Xã hội, Y tế & Thất nghiệp)
 * ============================================================
 */

class Insurance extends BaseModel
{
    protected string $table = 'employee_insurance';

    public function __construct()
    {
        parent::__construct();
    }

    // ══════════════════════════════════════════════════════════
    //  1. DASHBOARD & THỐNG KÊ TỔNG QUAN
    // ══════════════════════════════════════════════════════════

    /**
     * Lấy các chỉ số KPI thống kê tổng quan bảo hiểm
     */
    public function getDashboardStats(?int $month = null, ?int $year = null): array
    {
        $month = $month ?? (int)date('m');
        $year  = $year ?? (int)date('Y');
        $currentMonthStr = sprintf('%04d-%02d', $year, $month);

        // 1. Số nhân viên đang tham gia BH
        $this->db->query("SELECT COUNT(*) as active_count, COALESCE(SUM(insurance_salary), 0) as total_insurance_fund 
                          FROM employee_insurance WHERE status = 'Active'");
        $activeRow = $this->db->fetch() ?: ['active_count' => 0, 'total_insurance_fund' => 0];

        // 2. Biến động tăng/giảm trong tháng
        $this->db->query("SELECT 
                            SUM(CASE WHEN adjustment_type IN ('Tang_Moi', 'Tang_Luong') THEN 1 ELSE 0 END) as increase_count,
                            SUM(CASE WHEN adjustment_type LIKE 'Giam_%' THEN 1 ELSE 0 END) as decrease_count
                          FROM insurance_adjustments 
                          WHERE effective_month = :mStr", ['mStr' => $currentMonthStr]);
        $adjRow = $this->db->fetch() ?: ['increase_count' => 0, 'decrease_count' => 0];

        // 3. Chế độ bảo hiểm (claims) trong tháng / năm
        $this->db->query("SELECT 
                            COUNT(*) as total_claims,
                            SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending_claims,
                            COALESCE(SUM(CASE WHEN status IN ('Approved', 'Paid') THEN claim_amount ELSE 0 END), 0) as total_claim_amount
                          FROM insurance_claims 
                          WHERE YEAR(from_date) = :yr", ['yr' => $year]);
        $claimRow = $this->db->fetch() ?: ['total_claims' => 0, 'pending_claims' => 0, 'total_claim_amount' => 0];

        // 4. Tỷ lệ đóng hiện tại
        $rates = $this->getActiveRates($year);

        // 5. Ước tính tổng tiền bảo hiểm phải đóng tháng này
        $totalFund = (float)$activeRow['total_insurance_fund'];
        $empDeductionEst = $totalFund * ($rates['employee_total_rate'] / 100);
        $companyCostEst  = $totalFund * ($rates['company_total_rate'] / 100);
        $totalInsuranceEst = $empDeductionEst + $companyCostEst;

        return [
            'month'               => $month,
            'year'                => $year,
            'active_count'        => (int)$activeRow['active_count'],
            'total_insurance_fund'=> $totalFund,
            'increase_count'      => (int)($adjRow['increase_count'] ?? 0),
            'decrease_count'      => (int)($adjRow['decrease_count'] ?? 0),
            'total_claims'        => (int)$claimRow['total_claims'],
            'pending_claims'      => (int)$claimRow['pending_claims'],
            'total_claim_amount'  => (float)$claimRow['total_claim_amount'],
            'rates'               => $rates,
            'emp_deduction_est'   => $empDeductionEst,
            'company_cost_est'    => $companyCostEst,
            'total_insurance_est' => $totalInsuranceEst,
        ];
    }

    // ══════════════════════════════════════════════════════════
    //  2. TỶ LỆ ĐÓNG BẢO HIỂM (Insurance Rates)
    // ══════════════════════════════════════════════════════════

    /**
     * Lấy toàn bộ tỷ lệ đóng bảo hiểm theo năm
     */
    public function getRates(?int $year = null): array
    {
        $sql = "SELECT * FROM insurance_rates";
        $params = [];
        if ($year) {
            $sql .= " WHERE effective_year = :yr";
            $params['yr'] = $year;
        }
        $sql .= " ORDER BY effective_year DESC, id ASC";

        $this->db->query($sql, $params);
        return $this->db->fetchAll();
    }

    /**
     * Lấy tỷ lệ đóng đang có hiệu lực để tính toán
     */
    public function getActiveRates(int $year = 2026): array
    {
        $this->db->query(
            "SELECT * FROM insurance_rates WHERE effective_year = :yr AND status = 'Active'",
            ['yr' => $year]
        );
        $rows = $this->db->fetchAll();

        $ratesByType = [
            'BHXH'   => ['name' => 'Bảo hiểm Xã hội', 'emp' => 8.0,  'com' => 17.5],
            'BHYT'   => ['name' => 'Bảo hiểm Y tế',   'emp' => 1.5,  'com' => 3.0],
            'BHTN'   => ['name' => 'Bảo hiểm Thất nghiệp', 'emp' => 1.0, 'com' => 1.0],
            'BHTNLD' => ['name' => 'Bảo hiểm Tai nạn LĐ - BNN', 'emp' => 0.0, 'com' => 0.5],
        ];

        if (!empty($rows)) {
            foreach ($rows as $r) {
                $type = $r['insurance_type'];
                $ratesByType[$type] = [
                    'name' => $r['name'],
                    'emp'  => (float)$r['employee_rate'],
                    'com'  => (float)$r['company_rate'],
                ];
            }
        }

        $empTotal = 0;
        $comTotal = 0;
        foreach ($ratesByType as $t => $info) {
            $empTotal += $info['emp'];
            $comTotal += $info['com'];
        }

        return [
            'rates_by_type'       => $ratesByType,
            'employee_total_rate' => $empTotal, // 10.5%
            'company_total_rate'  => $comTotal,  // 22.0%
            'total_rate'          => $empTotal + $comTotal, // 32.5%
        ];
    }

    /**
     * Lưu hoặc cập nhật tỷ lệ bảo hiểm
     */
    public function saveRate(array $data): bool
    {
        if (!empty($data['id'])) {
            $this->db->query(
                "UPDATE insurance_rates 
                 SET name = :name,
                     employee_rate = :emp_rate,
                     company_rate = :com_rate,
                     effective_year = :year,
                     start_date = :s_date,
                     status = :status,
                     notes = :notes
                 WHERE id = :id",
                [
                    'name'     => $data['name'],
                    'emp_rate' => (float)$data['employee_rate'],
                    'com_rate' => (float)$data['company_rate'],
                    'year'     => (int)$data['effective_year'],
                    's_date'   => $data['start_date'],
                    'status'   => $data['status'] ?? 'Active',
                    'notes'    => $data['notes'] ?? null,
                    'id'       => (int)$data['id']
                ]
            );
        } else {
            $this->db->query(
                "INSERT INTO insurance_rates 
                 (insurance_type, name, employee_rate, company_rate, effective_year, start_date, status, notes, created_at)
                 VALUES (:type, :name, :emp_rate, :com_rate, :year, :s_date, :status, :notes, NOW())",
                [
                    'type'     => $data['insurance_type'],
                    'name'     => $data['name'],
                    'emp_rate' => (float)$data['employee_rate'],
                    'com_rate' => (float)$data['company_rate'],
                    'year'     => (int)($data['effective_year'] ?? 2026),
                    's_date'   => $data['start_date'] ?? date('Y-01-01'),
                    'status'   => $data['status'] ?? 'Active',
                    'notes'    => $data['notes'] ?? null,
                ]
            );
        }
        return true;
    }

    // ══════════════════════════════════════════════════════════
    //  3. QUẢN LÝ NHÂN VIÊN THAM GIA BẢO HIỂM
    // ══════════════════════════════════════════════════════════

    /**
     * Danh sách nhân viên và thông tin bảo hiểm
     */
    public function getEmployeesInsurance(array $filters = []): array
    {
        $sql = "SELECT e.id as employee_id,
                       e.emp_code,
                       e.full_name,
                       e.gender,
                       e.birth_date,
                       e.avatar_path,
                       d.dept_name,
                       p.pos_title,
                       ei.id as insurance_id,
                       ei.social_insurance_no,
                       ei.health_insurance_no,
                       ei.hospital_code,
                       ei.hospital_name,
                       ei.insurance_salary,
                       ei.start_date as ins_start_date,
                       ei.end_date as ins_end_date,
                       ei.status as ins_status,
                       ei.notes as ins_notes
                FROM employees e
                LEFT JOIN departments d ON e.department_id = d.id
                LEFT JOIN positions p ON e.position_id = p.id
                LEFT JOIN employee_insurance ei ON e.id = ei.employee_id
                WHERE e.status != 'Resigned'";

        $params = [];

        if (!empty($filters['dept_id'])) {
            $sql .= " AND e.department_id = :dept";
            $params['dept'] = (int)$filters['dept_id'];
        }

        if (!empty($filters['status'])) {
            if ($filters['status'] === 'Not_Registered') {
                $sql .= " AND (ei.id IS NULL OR ei.status IS NULL)";
            } else {
                $sql .= " AND ei.status = :ins_status";
                $params['ins_status'] = $filters['status'];
            }
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (e.full_name LIKE :kw OR e.emp_code LIKE :kw OR ei.social_insurance_no LIKE :kw OR ei.health_insurance_no LIKE :kw)";
            $params['kw'] = '%' . $filters['search'] . '%';
        }

        $sql .= " ORDER BY (ei.status = 'Active') DESC, ei.insurance_salary DESC, e.full_name ASC";

        $this->db->query($sql, $params);
        return $this->db->fetchAll();
    }

    /**
     * Lấy chi tiết thông tin bảo hiểm của 1 nhân viên
     */
    public function getInsuranceByEmployeeId(int $employeeId): ?object
    {
        $sql = "SELECT ei.*,
                       e.emp_code,
                       e.full_name,
                       e.birth_date,
                       e.gender,
                       e.id_card_no,
                       d.dept_name,
                       p.pos_title
                FROM employees e
                LEFT JOIN employee_insurance ei ON e.id = ei.employee_id
                LEFT JOIN departments d ON e.department_id = d.id
                LEFT JOIN positions p ON e.position_id = p.id
                WHERE e.id = :eid LIMIT 1";

        $this->db->query($sql, ['eid' => $employeeId]);
        $row = $this->db->fetch();
        return $row ? (object)$row : null;
    }

    /**
     * Đăng ký hoặc cập nhật hồ sơ bảo hiểm nhân viên
     */
    public function saveEmployeeInsurance(array $data): bool
    {
        $employeeId = (int)$data['employee_id'];
        if ($employeeId <= 0) return false;

        $socialNo = trim($data['social_insurance_no'] ?? '');
        $healthNo = trim($data['health_insurance_no'] ?? '');
        $salary = (float)str_replace([',', ' '], '', $data['insurance_salary'] ?? '0');
        $hospitalCode = trim($data['hospital_code'] ?? '');
        $hospitalName = trim($data['hospital_name'] ?? '');
        $startDate = $data['start_date'] ?: date('Y-m-01');
        $endDate = !empty($data['end_date']) ? $data['end_date'] : null;
        $status = $data['status'] ?? 'Active';
        $notes = $data['notes'] ?? null;

        $this->db->query("SELECT id FROM employee_insurance WHERE employee_id = :eid LIMIT 1", ['eid' => $employeeId]);
        $existing = $this->db->fetch();

        if ($existing) {
            $this->db->query(
                "UPDATE employee_insurance
                 SET social_insurance_no = :s_no,
                     health_insurance_no = :h_no,
                     hospital_code = :h_code,
                     hospital_name = :h_name,
                     insurance_salary = :salary,
                     start_date = :s_date,
                     end_date = :e_date,
                     status = :status,
                     notes = :notes
                 WHERE employee_id = :eid",
                [
                    's_no'   => $socialNo,
                    'h_no'   => $healthNo,
                    'h_code' => $hospitalCode,
                    'h_name' => $hospitalName,
                    'salary' => $salary,
                    's_date' => $startDate,
                    'e_date' => $endDate,
                    'status' => $status,
                    'notes'  => $notes,
                    'eid'    => $employeeId,
                ]
            );
        } else {
            $this->db->query(
                "INSERT INTO employee_insurance
                 (employee_id, social_insurance_no, health_insurance_no, hospital_code, hospital_name, insurance_salary, start_date, end_date, status, notes, created_at)
                 VALUES (:eid, :s_no, :h_no, :h_code, :h_name, :salary, :s_date, :e_date, :status, :notes, NOW())",
                [
                    'eid'    => $employeeId,
                    's_no'   => $socialNo,
                    'h_no'   => $healthNo,
                    'h_code' => $hospitalCode,
                    'h_name' => $hospitalName,
                    'salary' => $salary,
                    's_date' => $startDate,
                    'e_date' => $endDate,
                    'status' => $status,
                    'notes'  => $notes,
                ]
            );
        }

        // Đồng bộ số sổ BHXH & thẻ BHYT sang bảng employees nếu có
        $this->db->query(
            "UPDATE employees 
             SET social_insurance_no = :s_no, 
                 health_insurance_no = :h_no 
             WHERE id = :eid",
            ['s_no' => $socialNo, 'h_no' => $healthNo, 'eid' => $employeeId]
        );

        return true;
    }

    // ══════════════════════════════════════════════════════════
    //  4. BIẾN ĐỘNG LAO ĐỘNG BẢO HIỂM (Báo Tăng/Giảm D02-TS)
    // ══════════════════════════════════════════════════════════

    /**
     * Lấy danh sách biến động tăng giảm bảo hiểm
     */
    public function getAdjustments(array $filters = []): array
    {
        $sql = "SELECT a.*,
                       e.emp_code,
                       e.full_name,
                       e.birth_date,
                       e.gender,
                       d.dept_name,
                       p.pos_title,
                       ei.social_insurance_no
                FROM insurance_adjustments a
                JOIN employees e ON a.employee_id = e.id
                LEFT JOIN departments d ON e.department_id = d.id
                LEFT JOIN positions p ON e.position_id = p.id
                LEFT JOIN employee_insurance ei ON e.id = ei.employee_id
                WHERE 1=1";

        $params = [];

        if (!empty($filters['month'])) {
            $sql .= " AND a.effective_month = :month";
            $params['month'] = $filters['month'];
        }

        if (!empty($filters['type'])) {
            $sql .= " AND a.adjustment_type = :type";
            $params['type'] = $filters['type'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND a.status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (e.full_name LIKE :kw OR e.emp_code LIKE :kw OR a.doc_no LIKE :kw)";
            $params['kw'] = '%' . $filters['search'] . '%';
        }

        $sql .= " ORDER BY a.effective_date DESC, a.id DESC";

        $this->db->query($sql, $params);
        return $this->db->fetchAll();
    }

    /**
     * Thêm bản ghi báo tăng/giảm lao động
     */
    public function createAdjustment(array $data): int
    {
        $employeeId = (int)$data['employee_id'];
        $adjType = $data['adjustment_type'];
        $oldSalary = (float)str_replace([',', ' '], '', $data['old_salary'] ?? '0');
        $newSalary = (float)str_replace([',', ' '], '', $data['new_salary'] ?? '0');
        $effectiveMonth = $data['effective_month'] ?: date('Y-m');
        $effectiveDate = $data['effective_date'] ?: date('Y-m-01');
        $status = $data['status'] ?? 'Draft';

        $this->db->query(
            "INSERT INTO insurance_adjustments 
             (employee_id, adjustment_type, old_salary, new_salary, reason, effective_month, effective_date, doc_no, status, created_by, created_at)
             VALUES (:eid, :type, :old_sal, :new_sal, :reason, :eff_month, :eff_date, :doc_no, :status, :creator, NOW())",
            [
                'eid'       => $employeeId,
                'type'      => $adjType,
                'old_sal'   => $oldSalary,
                'new_sal'   => $newSalary,
                'reason'    => $data['reason'] ?? '',
                'eff_month' => $effectiveMonth,
                'eff_date'  => $effectiveDate,
                'doc_no'    => $data['doc_no'] ?? null,
                'status'    => $status,
                'creator'   => Session::userId(),
            ]
        );
        $adjId = (int)$this->db->lastInsertId();

        // Nếu trạng thái là Approved -> Tự động đồng bộ sang employee_insurance
        if ($status === 'Approved') {
            $this->applyAdjustmentToInsurance($employeeId, $adjType, $newSalary, $effectiveDate);
        }

        return $adjId;
    }

    /**
     * Cập nhật trạng thái đợt biến động và áp dụng vào hồ sơ
     */
    public function updateAdjustmentStatus(int $id, string $status): bool
    {
        $this->db->query("SELECT * FROM insurance_adjustments WHERE id = :id LIMIT 1", ['id' => $id]);
        $adj = $this->db->fetch();
        if (!$adj) return false;

        $this->db->query(
            "UPDATE insurance_adjustments SET status = :status WHERE id = :id",
            ['status' => $status, 'id' => $id]
        );

        if ($status === 'Approved') {
            $this->applyAdjustmentToInsurance((int)$adj['employee_id'], $adj['adjustment_type'], (float)$adj['new_salary'], $adj['effective_date']);
        }

        return true;
    }

    /**
     * Áp dụng biến động vào employee_insurance
     */
    private function applyAdjustmentToInsurance(int $employeeId, string $type, float $newSalary, string $effectiveDate): void
    {
        if ($type === 'Tang_Moi' || $type === 'Tang_Luong') {
            $this->db->query(
                "UPDATE employee_insurance 
                 SET status = 'Active', 
                     insurance_salary = CASE WHEN :new_sal > 0 THEN :new_sal ELSE insurance_salary END,
                     start_date = COALESCE(start_date, :eff_date),
                     end_date = NULL
                 WHERE employee_id = :eid",
                ['new_sal' => $newSalary, 'eff_date' => $effectiveDate, 'eid' => $employeeId]
            );
        } elseif ($type === 'Giam_Han') {
            $this->db->query(
                "UPDATE employee_insurance 
                 SET status = 'Stopped', 
                     end_date = :eff_date
                 WHERE employee_id = :eid",
                ['eff_date' => $effectiveDate, 'eid' => $employeeId]
            );
        } elseif (in_array($type, ['Giam_ThaiSan', 'Giam_OmDau', 'Giam_KhongLuong'])) {
            $this->db->query(
                "UPDATE employee_insurance 
                 SET status = 'Suspended', 
                     end_date = :eff_date
                 WHERE employee_id = :eid",
                ['eff_date' => $effectiveDate, 'eid' => $employeeId]
            );
        }
    }

    // ══════════════════════════════════════════════════════════
    //  5. QUẢN LÝ CHẾ ĐỘ BẢO HIỂM ĐÃ HƯỞNG (Claims - C70a-HD)
    // ══════════════════════════════════════════════════════════

    /**
     * Lấy danh sách hồ sơ hưởng chế độ
     */
    public function getClaims(array $filters = []): array
    {
        $sql = "SELECT c.*,
                       e.emp_code,
                       e.full_name,
                       e.gender,
                       d.dept_name,
                       p.pos_title,
                       ei.social_insurance_no,
                       ei.insurance_salary
                FROM insurance_claims c
                JOIN employees e ON c.employee_id = e.id
                LEFT JOIN departments d ON e.department_id = d.id
                LEFT JOIN positions p ON e.position_id = p.id
                LEFT JOIN employee_insurance ei ON e.id = ei.employee_id
                WHERE 1=1";

        $params = [];

        if (!empty($filters['type'])) {
            $sql .= " AND c.claim_type = :type";
            $params['type'] = $filters['type'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND c.status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['year'])) {
            $sql .= " AND YEAR(c.from_date) = :yr";
            $params['yr'] = (int)$filters['year'];
        }

        if (!empty($filters['employee_id'])) {
            $sql .= " AND c.employee_id = :eid";
            $params['eid'] = (int)$filters['employee_id'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (e.full_name LIKE :kw OR e.emp_code LIKE :kw OR c.document_ref LIKE :kw)";
            $params['kw'] = '%' . $filters['search'] . '%';
        }

        $sql .= " ORDER BY c.from_date DESC, c.id DESC";

        $this->db->query($sql, $params);
        return $this->db->fetchAll();
    }

    /**
     * Lưu hồ sơ hưởng chế độ bảo hiểm (Thêm mới / Sửa)
     */
    public function saveClaim(array $data): int
    {
        $amount = (float)str_replace([',', ' '], '', $data['claim_amount'] ?? '0');
        $leaveDays = (int)($data['leave_days'] ?? 1);

        if (!empty($data['id'])) {
            $this->db->query(
                "UPDATE insurance_claims
                 SET claim_type = :type,
                     from_date = :f_date,
                     to_date = :t_date,
                     leave_days = :days,
                     claim_amount = :amount,
                     bank_account = :bank_acc,
                     bank_name = :bank_name,
                     document_ref = :doc_ref,
                     status = :status,
                     notes = :notes
                 WHERE id = :id",
                [
                    'type'      => $data['claim_type'],
                    'f_date'    => $data['from_date'],
                    't_date'    => $data['to_date'],
                    'days'      => $leaveDays,
                    'amount'    => $amount,
                    'bank_acc'  => $data['bank_account'] ?? null,
                    'bank_name' => $data['bank_name'] ?? null,
                    'doc_ref'   => $data['document_ref'] ?? null,
                    'status'    => $data['status'] ?? 'Pending',
                    'notes'     => $data['notes'] ?? null,
                    'id'        => (int)$data['id']
                ]
            );
            return (int)$data['id'];
        } else {
            $this->db->query(
                "INSERT INTO insurance_claims 
                 (employee_id, claim_type, from_date, to_date, leave_days, claim_amount, bank_account, bank_name, document_ref, status, notes, created_at)
                 VALUES (:eid, :type, :f_date, :t_date, :days, :amount, :bank_acc, :bank_name, :doc_ref, :status, :notes, NOW())",
                [
                    'eid'       => (int)$data['employee_id'],
                    'type'      => $data['claim_type'],
                    'f_date'    => $data['from_date'],
                    't_date'    => $data['to_date'],
                    'days'      => $leaveDays,
                    'amount'    => $amount,
                    'bank_acc'  => $data['bank_account'] ?? null,
                    'bank_name' => $data['bank_name'] ?? null,
                    'doc_ref'   => $data['document_ref'] ?? null,
                    'status'    => $data['status'] ?? 'Pending',
                    'notes'     => $data['notes'] ?? null,
                ]
            );
            return (int)$this->db->lastInsertId();
        }
    }

    /**
     * Cập nhật trạng thái duyệt/chi trả chế độ
     */
    public function updateClaimStatus(int $id, string $status, ?float $amount = null): bool
    {
        $sql = "UPDATE insurance_claims SET status = :status";
        $params = ['status' => $status, 'id' => $id];

        if ($amount !== null && $amount >= 0) {
            $sql .= ", claim_amount = :amt";
            $params['amt'] = $amount;
        }

        if ($status === 'Approved') {
            $sql .= ", approved_date = NOW()";
        } elseif ($status === 'Paid') {
            $sql .= ", paid_date = NOW()";
        }

        $sql .= " WHERE id = :id";
        $this->db->query($sql, $params);
        return true;
    }

    // ══════════════════════════════════════════════════════════
    //  6. TÍNH TOÁN BẢNG ĐÓNG BẢO HIỂM HÀNG THÁNG (Monthly Calc)
    // ══════════════════════════════════════════════════════════

    /**
     * Tính toán chi tiết số tiền đóng BH tháng của toàn bộ nhân viên
     */
    public function monthlyCalculation(int $month, int $year): array
    {
        $rates = $this->getActiveRates($year);

        // Lấy danh sách nhân viên Active tham gia bảo hiểm
        $sql = "SELECT e.id as employee_id,
                       e.emp_code,
                       e.full_name,
                       d.dept_name,
                       p.pos_title,
                       ei.social_insurance_no,
                       ei.health_insurance_no,
                       ei.insurance_salary
                FROM employee_insurance ei
                JOIN employees e ON ei.employee_id = e.id
                LEFT JOIN departments d ON e.department_id = d.id
                LEFT JOIN positions p ON e.position_id = p.id
                WHERE ei.status = 'Active' AND e.status != 'Resigned'
                ORDER BY d.dept_name ASC, e.full_name ASC";

        $this->db->query($sql);
        $employees = $this->db->fetchAll();

        $calcList = [];
        $totals = [
            'total_salary'     => 0,
            'emp_bhxh'         => 0,
            'emp_bhyt'         => 0,
            'emp_bhtn'         => 0,
            'emp_total'        => 0,
            'com_bhxh'         => 0,
            'com_bhyt'         => 0,
            'com_bhtn'         => 0,
            'com_bhtnld'       => 0,
            'com_total'        => 0,
            'grand_total'      => 0,
        ];

        foreach ($employees as $emp) {
            $salary = (float)$emp['insurance_salary'];
            
            // NLĐ
            $empBhxh = round($salary * 0.08);
            $empBhyt = round($salary * 0.015);
            $empBhtn = round($salary * 0.01);
            $empTotal = $empBhxh + $empBhyt + $empBhtn;

            // Doanh nghiệp
            $comBhxh = round($salary * 0.175);
            $comBhyt = round($salary * 0.03);
            $comBhtn = round($salary * 0.01);
            $comBhtnld = round($salary * 0.005);
            $comTotal = $comBhxh + $comBhyt + $comBhtn + $comBhtnld;

            $itemGrandTotal = $empTotal + $comTotal;

            $calcList[] = array_merge($emp, [
                'salary'          => $salary,
                'emp_bhxh'        => $empBhxh,
                'emp_bhyt'        => $empBhyt,
                'emp_bhtn'        => $empBhtn,
                'emp_total'       => $empTotal,
                'com_bhxh'        => $comBhxh,
                'com_bhyt'        => $comBhyt,
                'com_bhtn'        => $comBhtn,
                'com_bhtnld'      => $comBhtnld,
                'com_total'       => $comTotal,
                'grand_total'     => $itemGrandTotal,
            ]);

            // Cộng dồn tổng
            $totals['total_salary'] += $salary;
            $totals['emp_bhxh']     += $empBhxh;
            $totals['emp_bhyt']     += $empBhyt;
            $totals['emp_bhtn']     += $empBhtn;
            $totals['emp_total']    += $empTotal;
            $totals['com_bhxh']     += $comBhxh;
            $totals['com_bhyt']     += $comBhyt;
            $totals['com_bhtn']     += $comBhtn;
            $totals['com_bhtnld']   += $comBhtnld;
            $totals['com_total']    += $comTotal;
            $totals['grand_total']  += $itemGrandTotal;
        }

        return [
            'month'     => $month,
            'year'      => $year,
            'rates'     => $rates,
            'list'      => $calcList,
            'totals'    => $totals,
            'count'     => count($calcList),
        ];
    }

    // ══════════════════════════════════════════════════════════
    //  7. BÁO CÁO MẪU D02-TS & C70a-HD
    // ══════════════════════════════════════════════════════════

    /**
     * Dữ liệu Báo cáo D02-TS (Danh sách lao động tham gia BHXH, BHYT, BHTN)
     */
    public function getReportD02TS(string $month): array
    {
        $adjustments = $this->getAdjustments(['month' => $month]);

        $increases = [];
        $decreases = [];

        foreach ($adjustments as $adj) {
            if (in_array($adj['adjustment_type'], ['Tang_Moi', 'Tang_Luong'])) {
                $increases[] = $adj;
            } else {
                $decreases[] = $adj;
            }
        }

        return [
            'month'     => $month,
            'increases' => $increases,
            'decreases' => $decreases,
            'total'     => count($adjustments),
        ];
    }

    /**
     * Dữ liệu Báo cáo C70a-HD (Danh sách đề nghị giải quyết hưởng chế độ ốm đau, thai sản...)
     */
    public function getReportC70aHD(string $month): array
    {
        $parts = explode('-', $month);
        $year = (int)($parts[0] ?? date('Y'));
        $m = (int)($parts[1] ?? date('m'));

        $sql = "SELECT c.*,
                       e.emp_code,
                       e.full_name,
                       e.gender,
                       e.birth_date,
                       d.dept_name,
                       p.pos_title,
                       ei.social_insurance_no,
                       ei.insurance_salary
                FROM insurance_claims c
                JOIN employees e ON c.employee_id = e.id
                LEFT JOIN departments d ON e.department_id = d.id
                LEFT JOIN positions p ON e.position_id = p.id
                LEFT JOIN employee_insurance ei ON e.id = ei.employee_id
                WHERE MONTH(c.from_date) = :m AND YEAR(c.from_date) = :yr
                ORDER BY c.claim_type ASC, c.from_date ASC";

        $this->db->query($sql, ['m' => $m, 'yr' => $year]);
        $claims = $this->db->fetchAll();

        $byType = [
            'OmDau'         => [],
            'ThaiSan'       => [],
            'TaiNanLD_BNN'  => [],
            'DuongSuc'      => [],
        ];

        $totalAmount = 0;
        $totalDays = 0;

        foreach ($claims as $cl) {
            $type = $cl['claim_type'];
            if (isset($byType[$type])) {
                $byType[$type][] = $cl;
            }
            $totalAmount += (float)$cl['claim_amount'];
            $totalDays += (int)$cl['leave_days'];
        }

        return [
            'month'        => $month,
            'by_type'      => $byType,
            'total_claims' => count($claims),
            'total_amount' => $totalAmount,
            'total_days'   => $totalDays,
        ];
    }
}
