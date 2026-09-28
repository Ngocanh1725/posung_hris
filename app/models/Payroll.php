<?php
/**
 * ============================================================
 *  POSUNG HRIS – Payroll Model (Dynamic Payroll Engine)
 * ============================================================
 *  Tính toán Lương động dựa trên công thức cấu hình và 
 *  dữ liệu từ Timesheet, bao gồm Thuế TNCN lũy tiến.
 * ============================================================
 */

class Payroll extends BaseModel
{
    protected string $table = 'payrolls';

    /**
     * Mức lương cơ sở nhà nước để tính phụ cấp chức vụ (VD: 2.340.000 VNĐ)
     */
    private float $baseStateWage = 2340000.0;

    /**
     * Mức giảm trừ gia cảnh (Bản thân) - Tạm tính 11.000.000 VNĐ
     */
    private float $personalDeduction = 11000000.0;

    /**
     * Tính thuế TNCN Lũy tiến từng phần (Việt Nam)
     * @param float $taxableIncome Thu nhập tính thuế (đã trừ BHXH và Giảm trừ gia cảnh)
     * @return float Tiền thuế phải nộp
     */
    private function calculatePIT(float $taxableIncome): float
    {
        if ($taxableIncome <= 0) return 0.0;

        $tax = 0.0;

        // Biểu thuế lũy tiến từng phần (Theo tháng)
        $brackets = [
            ['limit' => 5000000,  'rate' => 0.05], // Bậc 1: Đến 5tr
            ['limit' => 10000000, 'rate' => 0.10], // Bậc 2: Trên 5tr đến 10tr
            ['limit' => 18000000, 'rate' => 0.15], // Bậc 3: Trên 10tr đến 18tr
            ['limit' => 32000000, 'rate' => 0.20], // Bậc 4: Trên 18tr đến 32tr
            ['limit' => 52000000, 'rate' => 0.25], // Bậc 5: Trên 32tr đến 52tr
            ['limit' => 80000000, 'rate' => 0.30], // Bậc 6: Trên 52tr đến 80tr
            ['limit' => PHP_FLOAT_MAX, 'rate' => 0.35], // Bậc 7: Trên 80tr
        ];

        $previousLimit = 0;

        foreach ($brackets as $bracket) {
            if ($taxableIncome > $previousLimit) {
                // Thu nhập chịu thuế trong bậc hiện tại
                $incomeInBracket = min($taxableIncome, $bracket['limit']) - $previousLimit;
                $tax += $incomeInBracket * $bracket['rate'];
                $previousLimit = $bracket['limit'];
            } else {
                break;
            }
        }

        return round($tax);
    }

    /**
     * Kích hoạt Động cơ tính lương cho một tháng, bóc tách theo dự án
     * 
     * @param int $month
     * @param int $year
     * @param int|null $projectId Nếu truyền vào sẽ chỉ tính cho nhân sự của dự án đó
     * @return int Số bảng lương đã tính
     */
    public function calculateProjectAllocatedPayroll(int $month, int $year, ?int $projectId = null): int
    {
        // Xóa bảng lương cũ của tháng/năm này (nếu đang tính lại và chưa chốt)
        $delSql = "DELETE FROM payrolls WHERE month = :m AND year = :y AND payment_status != 'Approved'";
        $delParams = ['m' => $month, 'y' => $year];
        if ($projectId) {
            $delSql .= " AND project_id = :proj";
            $delParams['proj'] = $projectId;
        }
        $this->db->query($delSql, $delParams);

        // 1. Lấy danh sách nhân viên cần tính lương
        $sql = "SELECT e.id, e.current_project_id, e.department_id, e.nationality, e.employee_type, p.allowance_rate,
                       s.base_salary, s.project_allowance, s.cleanroom_allowance, 
                       s.remote_allowance, s.hazard_allowance, s.insurance_rate
                FROM employees e
                LEFT JOIN positions p ON e.position_id = p.id
                LEFT JOIN salaries s ON e.id = s.employee_id
                WHERE e.`status` = 'Active'";
                
        $params = [];
        if ($projectId) {
            $sql .= " AND e.current_project_id = :proj";
            $params['proj'] = $projectId;
        }

        $this->db->query($sql, $params);
        $employees = $this->db->fetchAll();

        require_once APP_ROOT . '/models/Timesheet.php';
        $timesheet = new Timesheet();
        
        require_once APP_ROOT . '/models/PayrollFormula.php';
        $formulaEngine = new PayrollFormula();

        // Nạp công thức từ Database (bảng payroll_formulas)
        $formulas = $formulaEngine->getAllFormulas();

        // Lấy danh sách Cost Center của các Project để ánh xạ
        $this->db->query("SELECT id, project_id FROM cost_centers");
        $costCenters = $this->db->fetchAll();
        $projectCostCenters = [];
        foreach ($costCenters as $cc) {
            if ($cc['project_id']) {
                $projectCostCenters[$cc['project_id']] = $cc['id'];
            }
        }

        $count = 0;
        $standardDays = 26.0;

        // Tối ưu N+1: Lấy trước toàn bộ hse_violations chưa khấu trừ trong tháng này
        $this->db->query("SELECT emp_id, SUM(penalty_amount) as hse_penalty 
                          FROM hse_violations 
                          WHERE is_deducted = 0 AND MONTH(violation_date) = :m AND YEAR(violation_date) = :y 
                          GROUP BY emp_id", [
            'm' => $month, 'y' => $year
        ]);
        $hsePenalties = [];
        $penalizedEmpIds = [];
        foreach ($this->db->fetchAll() as $row) {
            $hsePenalties[$row['emp_id']] = $row['hse_penalty'];
            $penalizedEmpIds[] = $row['emp_id'];
        }

        $this->db->beginTransaction();

        try {
            foreach ($employees as $emp) {
                if (empty($emp['base_salary'])) continue;

                // 2. Lấy dữ liệu chấm công phân bổ theo Project
                $summaries = $timesheet->getMonthlySummaryByProject($emp['id'], $month, $year);
                if (empty($summaries)) continue;

                $baseSalary = (float)$emp['base_salary'];
                $otRateHour = $baseSalary / ($standardDays * 8.0);
                
                // Bước 1: Tính tổng thu nhập cho toàn bộ tháng và phân bổ theo Cost Center
                $totalActualDays = 0;
                $totalIncome = 0;
                $projectIncomes = [];
                
                foreach ($summaries as $ts) {
                    $pId = $ts['project_id'] ?: $emp['current_project_id'];
                    $ccId = $projectCostCenters[$pId] ?? null;
                    
                    $dailyRate = $baseSalary / $standardDays;
                    $regularPay = $dailyRate * $ts['actual_days'];
                    
                    $otPay = $otRateHour * ($ts['ot_day_hours'] * 1.5 + $ts['ot_sunday_hours'] * 2.0 + $ts['ot_night_hours'] * 2.0);
                    $nightShiftBonus = ($ts['night_shifts'] ?? 0) * 8 * $otRateHour * 0.3;
                    $otPay += $nightShiftBonus;
                    
                    $positionAllowance = ((float)$emp['allowance_rate'] * $this->baseStateWage) * ($ts['actual_days'] / $standardDays);
                    
                    // Sử dụng Formula Builder Engine
                    $vars = [
                        'remote_allowance' => (float)$emp['remote_allowance'],
                        'standard_days' => $standardDays,
                        'actual_days' => $ts['actual_days'],
                        'cleanroom_days' => $ts['cleanroom_days'],
                        'hazard_allowance' => (float)$emp['hazard_allowance']
                    ];
                    $remoteAllowance = isset($formulas['remote_allowance']) ? $formulaEngine->evaluate($formulas['remote_allowance'], $vars) : 0;
                    $cleanroomAllowance = isset($formulas['cleanroom_allowance']) ? $formulaEngine->evaluate($formulas['cleanroom_allowance'], $vars) : 0;
                    $hazardAllowance = isset($formulas['hazard_allowance']) ? $formulaEngine->evaluate($formulas['hazard_allowance'], $vars) : 0;
                    
                    $otherAllowances = ((float)$emp['project_allowance']) / $standardDays * $ts['actual_days'];
                    
                    $expatAdjustment = 0;
                    if ($emp['nationality'] === 'South Korean' || $emp['employee_type'] === 'Expat') {
                        $exchangeRateVND = 25000;
                        $baseSalaryUSD = $baseSalary / 24000;
                        $expatAdjustment = (($baseSalaryUSD * $exchangeRateVND) - $baseSalary) * ($ts['actual_days'] / $standardDays);
                    }
                    
                    $totalAllowances = $positionAllowance + $remoteAllowance + $cleanroomAllowance + $hazardAllowance + $otherAllowances + $expatAdjustment;
                    
                    $projectIncome = $regularPay + $otPay + $totalAllowances;
                    $otTaxExempt = ($ts['ot_day_hours'] * 0.5 + $ts['ot_night_hours'] * 1.0 + $ts['ot_sunday_hours'] * 1.0) * $otRateHour;
                    
                    $projectIncomes[] = [
                        'project_id' => $pId,
                        'cc_id' => $ccId,
                        'actual_days' => $ts['actual_days'],
                        'regular_pay' => $regularPay,
                        'ot_pay' => $otPay,
                        'allowances' => $totalAllowances,
                        'gross_income' => $projectIncome,
                        'tax_exempt' => $otTaxExempt
                    ];
                    
                    $totalIncome += $projectIncome;
                    $totalActualDays += $ts['actual_days'];
                }
                
                // Bước 2: Tính tổng Khấu trừ (Bảo hiểm & Thuế TNCN lũy tiến) cho CẢ THÁNG
                // Bảo hiểm theo luật 2026: BHXH 8%, BHYT 1.5%, BHTN 1%
                $bhxh = $baseSalary * 0.08;
                $bhyt = $baseSalary * 0.015;
                $bhtn = $baseSalary * 0.01;
                $insuranceDeductionTotal = $bhxh + $bhyt + $bhtn;
                
                $totalTaxExempt = array_sum(array_column($projectIncomes, 'tax_exempt'));
                $taxableIncome = $totalIncome - $insuranceDeductionTotal - $totalTaxExempt - $this->personalDeduction;
                $taxDeductionTotal = $this->calculatePIT($taxableIncome);
                
                // Lấy tổng tiền phạt vi phạm HSE chưa khấu trừ trong tháng từ cache
                $hsePenaltyTotal = $hsePenalties[$emp['id']] ?? 0;
                
                $totalDeductions = $insuranceDeductionTotal + $taxDeductionTotal + $hsePenaltyTotal;
                
                // Bước 3: Phân bổ Khấu trừ về từng Cost Center và lưu Database
                foreach ($projectIncomes as $pi) {
                    $ratio = $totalIncome > 0 ? ($pi['gross_income'] / $totalIncome) : 0;
                    $projDeductions = $totalDeductions * $ratio;
                    $projNet = $pi['gross_income'] - $projDeductions;
                    
                    $sql = "INSERT INTO payrolls 
                            (month, year, employee_id, project_id, cost_center_id, standard_days, actual_days, 
                             base_salary, regular_pay, ot_pay, allowance_hazard, allowances_total, 
                             insurance_social, insurance_health, insurance_unemployment, tax_pit, 
                             deductions_total, net_salary, payment_status, status)
                            VALUES 
                            (:m, :y, :emp, :proj, :cc, :std, :act, 
                             :base, :reg, :ot, :haz, :allow, 
                             :bhxh, :bhyt, :bhtn, :tax, 
                             :deduct, :net, 'Calculated', 'Approved')";
    
                    $this->db->query($sql, [
                        'm'      => $month,
                        'y'      => $year,
                        'emp'    => $emp['id'],
                        'proj'   => $pi['project_id'],
                        'cc'     => $pi['cc_id'],
                        'std'    => $standardDays,
                        'act'    => $pi['actual_days'],
                        'base'   => $baseSalary,
                        'reg'    => $pi['regular_pay'],
                        'ot'     => round($pi['ot_pay'], 2),
                        'haz'    => round($hazardAllowance * $ratio, 2),
                        'allow'  => round($pi['allowances'], 2),
                        'bhxh'   => round($bhxh * $ratio, 2),
                        'bhyt'   => round($bhyt * $ratio, 2),
                        'bhtn'   => round($bhtn * $ratio, 2),
                        'tax'    => round($taxDeductionTotal * $ratio, 2),
                        'deduct' => round($projDeductions, 2),
                        'net'    => round($projNet, 2)
                    ]);
                }
                
                $count++;
            }

            // Batch update hse_violations
            if (!empty($penalizedEmpIds)) {
                $placeholders = str_repeat('?,', count($penalizedEmpIds) - 1) . '?';
                $params = $penalizedEmpIds;
                $params[] = $month;
                $params[] = $year;
                $this->db->query("UPDATE hse_violations SET is_deducted = 1 
                                  WHERE is_deducted = 0 AND emp_id IN ($placeholders) 
                                  AND MONTH(violation_date) = ? AND YEAR(violation_date) = ?", 
                                  $params);
            }

            $this->db->commit();
            return $count;

        } catch (Exception $e) {
            $this->db->rollBack();
            echo "Lỗi tính lương: " . $e->getMessage() . "\n";
            error_log("Lỗi tính lương: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Chốt bảng lương (Không cho sửa chấm công nữa)
     */
    public function lockPayroll(int $month, int $year): bool
    {
        $this->db->query(
            "UPDATE payrolls SET payment_status = 'Approved' WHERE month = :m AND year = :y AND payment_status = 'Calculated'",
            ['m' => $month, 'y' => $year]
        );
        return $this->db->rowCount() > 0;
    }

    /**
     * Lấy danh sách bảng lương tháng (Dashboard)
     */
    public function getPayrolls(int $month, int $year, ?int $projectId = null): array
    {
        $sql = "SELECT pr.*, e.emp_code, e.full_name, p.pos_title, proj.project_name, cc.code as cc_code
                FROM payrolls pr
                JOIN employees e ON pr.employee_id = e.id
                LEFT JOIN positions p ON e.position_id = p.id
                LEFT JOIN projects proj ON pr.project_id = proj.id
                LEFT JOIN cost_centers cc ON pr.cost_center_id = cc.id
                WHERE pr.month = :m AND pr.year = :y";
        
        $params = ['m' => $month, 'y' => $year];
        if ($projectId) {
            $sql .= " AND pr.project_id = :proj";
            $params['proj'] = $projectId;
        }

        $this->db->query($sql, $params);
        return json_decode(json_encode($this->db->fetchAll()));
    }

    /**
     * Lấy chi tiết 1 phiếu lương
     */
    public function getPayslip(int $employeeId, int $month, int $year): ?object
    {
        // Phải group by employee_id và SUM các khoản tiền do lương có thể được chia theo nhiều project
        $sql = "SELECT e.id as employee_id, e.emp_code, e.full_name, e.employee_type, e.bank_account_no, e.bank_name, e.tax_code,
                       p.pos_title, d.dept_name,
                       MAX(pr.base_salary) as base_salary,
                       SUM(pr.actual_days) as actual_days,
                       MAX(pr.standard_days) as standard_days,
                       SUM(pr.ot_pay) as ot_pay,
                       SUM(pr.allowances_total) as allowances_total,
                       SUM(pr.deductions_total) as deductions_total,
                       SUM(pr.net_salary) as net_salary,
                       SUM(pr.leave_days) as leave_days,
                       SUM(pr.unpaid_leave_days) as unpaid_leave_days,
                       SUM(pr.performance_salary) as performance_salary,
                       SUM(pr.allowance_hazard) as allowance_hazard,
                       SUM(pr.allowance_meal) as allowance_meal,
                       SUM(pr.allowance_travel) as allowance_travel,
                       SUM(pr.allowance_phone) as allowance_phone,
                       SUM(pr.bonus) as bonus,
                       SUM(pr.other_support) as other_support,
                       SUM(pr.insurance_social) as insurance_social,
                       SUM(pr.insurance_health) as insurance_health,
                       SUM(pr.insurance_unemployment) as insurance_unemployment,
                       SUM(pr.tax_pit) as tax_pit,
                       SUM(pr.union_fee) as union_fee,
                       SUM(pr.advance_payment) as advance_payment,
                       GROUP_CONCAT(DISTINCT proj.project_name SEPARATOR ', ') as project_name,
                       GROUP_CONCAT(DISTINCT cc.code SEPARATOR ', ') as cc_code
                FROM payrolls pr
                JOIN employees e ON pr.employee_id = e.id
                LEFT JOIN positions p ON e.position_id = p.id
                LEFT JOIN departments d ON e.department_id = d.id
                LEFT JOIN projects proj ON pr.project_id = proj.id
                LEFT JOIN cost_centers cc ON pr.cost_center_id = cc.id
                WHERE pr.employee_id = :emp AND pr.month = :m AND pr.year = :y
                GROUP BY pr.employee_id";

        $this->db->query($sql, ['emp' => $employeeId, 'm' => $month, 'y' => $year]);
        $row = $this->db->fetch();

        if ($row) {
            $row['month'] = $month;
            $row['year'] = $year;
            // Lấy thêm summary chấm công để in vào phiếu lương
            require_once APP_ROOT . '/models/Timesheet.php';
            $tsModel = new Timesheet();
            $ts = $tsModel->getMonthlySummary($employeeId, $month, $year);
            $row['timesheet'] = $ts;

            return (object)$row;
        }
        return null;
    }
}
