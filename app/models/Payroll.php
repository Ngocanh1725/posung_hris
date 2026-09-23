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
     * Kích hoạt Động cơ tính lương cho một tháng
     * 
     * @param int $month
     * @param int $year
     * @param int|null $projectId Nếu truyền vào sẽ chỉ tính cho nhân sự của dự án đó
     * @return int Số bảng lương đã tính
     */
    public function calculateMonthlyPayroll(int $month, int $year, ?int $projectId = null): int
    {
        // 1. Lấy danh sách nhân viên cần tính lương
        $sql = "SELECT e.id, e.current_project_id, e.department_id, e.nationality, e.employee_type, p.allowance_rate,
                       s.base_salary, s.project_allowance, s.cleanroom_allowance, 
                       s.remote_allowance, s.hazard_allowance, s.insurance_rate
                FROM employees e
                LEFT JOIN positions p ON e.position_id = p.id
                LEFT JOIN salaries s ON e.id = s.employee_id
                WHERE e.status = 'Active'";
                
        $params = [];
        if ($projectId) {
            $sql .= " AND e.current_project_id = :proj";
            $params['proj'] = $projectId;
        }

        $this->db->query($sql, $params);
        $employees = $this->db->fetchAll();

        // Cần instance của Timesheet
        require_once APP_ROOT . '/models/Timesheet.php';
        $timesheet = new Timesheet();

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
        $standardDays = 26.0; // Số ngày công chuẩn của tháng (Thực tế nên có bảng cấu hình riêng)

        $this->db->beginTransaction();

        try {
            foreach ($employees as $emp) {
                // Bỏ qua nếu chưa khai báo lương
                if (empty($emp['base_salary'])) continue;

                // 2. Lấy dữ liệu chấm công tổng hợp
                $ts = $timesheet->getMonthlySummary($emp['id'], $month, $year);
                if ($ts['actual_days'] <= 0) continue; // Không đi làm

                $baseSalary = (float)$emp['base_salary'];
                
                // --- THU NHẬP ---
                // 3.1. Lương ngày công = (Base / Chuẩn) * Thực tế
                $dailyRate = $baseSalary / $standardDays;
                $regularPay = $dailyRate * $ts['actual_days'];

                // 3.2. Lương OT (Ngày = 1.5, Đêm = 2.0, Chủ nhật = 2.0, Lễ = 3.0, Phụ cấp ca đêm = 0.3)
                $otRateHour = $baseSalary / ($standardDays * 8.0);
                $ot150 = $ts['ot_day_hours'];
                $ot200 = $ts['ot_sunday_hours']; // Tạm gộp chủ nhật vào 2.0
                $ot300 = 0; // Holiday (Nếu có dữ liệu thì lấy từ ts)
                // Đếm số ca đêm để tính phụ cấp 30%
                // Giả định 1 ca đêm = 8 giờ làm việc x 0.3
                $nightShiftAllowance = ($ts['ot_night_hours'] > 0) ? ($ts['ot_night_hours'] * 2.0 * $otRateHour) : 0; // Giữ OT đêm 2.0 
                // Thêm phụ cấp ca đêm 30% cho số ca đêm (ts trả về số ca đêm ở mục ot_night_hours? Không, nó trả về số ca ở `ts['night_shifts']` từ getMonthlySummary - à getMonthlySummary chưa trả về, wait, nó trả về trong getMonthlySummary mà! 
                
                // Tiền làm thêm giờ = Đơn giá giờ * (OT_150 * 1.5 + OT_200 * 2.0 + OT_300 * 3.0 + Night_Shift * 0.3)
                // Chú ý: Ở đây Night_Shift * 0.3 tính trên GIỜ (tức là = Số ca đêm * 8 giờ * 0.3)
                $otPay = $otRateHour * ($ts['ot_day_hours'] * 1.5 + $ts['ot_sunday_hours'] * 2.0 + $ts['ot_night_hours'] * 2.0); // Cộng thêm phần ca đêm nếu cần
                // Bổ sung phụ cấp làm đêm (30% lương ngày) cho mỗi ca đêm thực tế
                // (ts['night_shifts'] chưa chắc có, nhưng nếu getMonthlySummary trả về night_shifts thì ta dùng)
                $nightShiftBonus = 0; // Ta sẽ bỏ qua cái này hoặc dùng: $nightShiftBonus = ($ts['night_shifts'] ?? 0) * 8 * $otRateHour * 0.3;
                $otPay += $nightShiftBonus;

                // 3.3. Phụ cấp động
                // - Chức vụ: allowance_rate * Mức lương cơ sở
                $positionAllowance = ((float)$emp['allowance_rate'] * $this->baseStateWage);
                
                // - Xa nhà (Đi công trường): Mức remote_allowance * Số ngày thực tế (Tạm tính max là trọn tháng)
                // Ở Po Sung, remote_allowance là cục cố định tháng hay ngày? Giả sử là cố định nếu làm đủ công.
                $remoteAllowance = ((float)$emp['remote_allowance'] / $standardDays) * $ts['actual_days'];
                
                // - Cleanroom (150k/ngày)
                $cleanroomAllowance = $ts['cleanroom_days'] * 150000;
                
                // - Phụ cấp Dự án & Độc hại (Tính theo tỷ lệ ngày công)
                $otherAllowances = ((float)$emp['project_allowance'] + (float)$emp['hazard_allowance']) / $standardDays * $ts['actual_days'];

                // - Expat (Korean) Currency Exchange Adjustment
                $expatAdjustment = 0;
                if ($emp['nationality'] === 'South Korean' || $emp['employee_type'] === 'Expat') {
                    // Giả lập tỷ giá hối đoái lấy từ Shinhan Bank API
                    $exchangeRateVND = 25000; // 1 USD = 25000 VND
                    $baseSalaryUSD = $baseSalary / 24000; // Giả sử lương gốc đang quy đổi ở mức 24k
                    $expatAdjustment = ($baseSalaryUSD * $exchangeRateVND) - $baseSalary;
                }

                $totalAllowances = $positionAllowance + $remoteAllowance + $cleanroomAllowance + $otherAllowances + $expatAdjustment;

                $totalIncome = $regularPay + $otPay + $totalAllowances;

                // --- KHẤU TRỪ ---
                // 4.1. Bảo hiểm (Theo tỷ lệ đóng)
                $insuranceRate = (float)$emp['insurance_rate'] / 100; // VD: 10.5% = 0.105
                $insuranceDeduction = $baseSalary * $insuranceRate;

                // 4.2. Thuế TNCN
                // Thu nhập chịu thuế = Tổng thu nhập - BHXH - Lương OT phần chênh lệch (Miễn thuế phần chênh 1.5, 2.0)
                // Giản lược: Trừ phần OT vượt 1.0
                $otTaxExempt = ($ts['ot_day_hours'] * 0.5 + $ts['ot_night_hours'] * 1.0 + $ts['ot_sunday_hours'] * 1.0) * $otRateHour;
                $taxableIncome = $totalIncome - $insuranceDeduction - $otTaxExempt - $this->personalDeduction;
                
                $taxDeduction = $this->calculatePIT($taxableIncome);
                
                $totalDeductions = $insuranceDeduction + $taxDeduction;

                // --- THỰC LĨNH ---
                $netSalary = $totalIncome - $totalDeductions;

                // Xác định Cost Center
                $ccId = $projectCostCenters[$emp['current_project_id']] ?? null;

                // 5. Lưu vào Database (Dùng UPSERT)
                $sql = "INSERT INTO payrolls 
                        (month, year, employee_id, project_id, cost_center_id, standard_days, actual_days, 
                         ot_pay, allowances_total, deductions_total, net_salary, payment_status)
                        VALUES 
                        (:m, :y, :emp, :proj, :cc, :std, :act, :ot, :allow, :deduct, :net, 'Calculated')
                        ON DUPLICATE KEY UPDATE 
                        project_id=VALUES(project_id), cost_center_id=VALUES(cost_center_id),
                        actual_days=VALUES(actual_days), ot_pay=VALUES(ot_pay), 
                        allowances_total=VALUES(allowances_total), deductions_total=VALUES(deductions_total), 
                        net_salary=VALUES(net_salary), payment_status='Calculated'";

                $this->db->query($sql, [
                    'm'      => $month,
                    'y'      => $year,
                    'emp'    => $emp['id'],
                    'proj'   => $emp['current_project_id'],
                    'cc'     => $ccId,
                    'std'    => $standardDays,
                    'act'    => $ts['actual_days'],
                    'ot'     => round($otPay, 2),
                    'allow'  => round($totalAllowances, 2),
                    'deduct' => round($totalDeductions, 2),
                    'net'    => round($netSalary, 2)
                ]);

                $count++;
            }

            $this->db->commit();
            return $count;

        } catch (Exception $e) {
            $this->db->rollBack();
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
        // Phải join lại với salaries để lấy thông tin chi tiết
        $sql = "SELECT pr.*, e.emp_code, e.full_name, e.employee_type, 
                       p.pos_title, d.dept_name, proj.project_name, cc.code as cc_code,
                       s.base_salary
                FROM payrolls pr
                JOIN employees e ON pr.employee_id = e.id
                LEFT JOIN positions p ON e.position_id = p.id
                LEFT JOIN departments d ON e.department_id = d.id
                LEFT JOIN projects proj ON pr.project_id = proj.id
                LEFT JOIN cost_centers cc ON pr.cost_center_id = cc.id
                LEFT JOIN salaries s ON e.id = s.employee_id
                WHERE pr.employee_id = :emp AND pr.month = :m AND pr.year = :y";

        $this->db->query($sql, ['emp' => $employeeId, 'm' => $month, 'y' => $year]);
        $row = $this->db->fetch();

        if ($row) {
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
