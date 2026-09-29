<?php
/**
 * ============================================================
 *  POSUNG HRIS – Loan Model
 * ============================================================
 *  Quản lý Hồ sơ Khoản vay & Tạm ứng Cán bộ Nhân viên
 * ============================================================
 */

require_once APP_ROOT . '/models/BaseModel.php';

class Loan extends BaseModel
{
    protected string $table = 'employee_loans';

    /**
     * Lấy danh sách khoản vay kèm liên kết thông tin nhân viên, phòng ban, loại vay
     */
    public function getAllWithRelations(array $filters = []): array
    {
        $sql = "SELECT l.*,
                       e.full_name AS employee_name,
                       e.emp_code AS employee_code,
                       e.avatar_path,
                       d.dept_name,
                       p.pos_title,
                       t.name AS type_name,
                       t.type_code,
                       u.username AS approver_username,
                       ue.full_name AS approver_fullname
                FROM `{$this->table}` l
                INNER JOIN `employees` e ON l.employee_id = e.id
                LEFT JOIN `departments` d ON e.department_id = d.id
                LEFT JOIN `positions` p ON e.position_id = p.id
                LEFT JOIN `loan_types` t ON l.loan_type_id = t.id
                LEFT JOIN `users` u ON l.approved_by = u.id
                LEFT JOIN `employees` ue ON u.employee_id = ue.id
                WHERE 1=1";

        $params = [];

        if (!empty($filters['search'])) {
            $sql .= " AND (l.loan_code LIKE :search OR e.full_name LIKE :search2 OR e.emp_code LIKE :search3)";
            $params['search']  = "%{$filters['search']}%";
            $params['search2'] = "%{$filters['search']}%";
            $params['search3'] = "%{$filters['search']}%";
        }

        if (!empty($filters['status'])) {
            $sql .= " AND l.status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['loan_type_id'])) {
            $sql .= " AND l.loan_type_id = :loan_type_id";
            $params['loan_type_id'] = (int)$filters['loan_type_id'];
        }

        if (!empty($filters['employee_id'])) {
            $sql .= " AND l.employee_id = :employee_id";
            $params['employee_id'] = (int)$filters['employee_id'];
        }

        if (!empty($filters['department_id'])) {
            $sql .= " AND e.department_id = :department_id";
            $params['department_id'] = (int)$filters['department_id'];
        }

        if (!empty($filters['from_date'])) {
            $sql .= " AND l.applied_date >= :from_date";
            $params['from_date'] = $filters['from_date'];
        }

        if (!empty($filters['to_date'])) {
            $sql .= " AND l.applied_date <= :to_date";
            $params['to_date'] = $filters['to_date'];
        }

        $sql .= " ORDER BY l.id DESC";

        $this->db->query($sql, $params);
        return json_decode(json_encode($this->db->fetchAll()));
    }

    /**
     * Lấy chi tiết 1 khoản vay đầy đủ thông tin và lịch sử trả
     */
    public function getByIdWithDetails(int $id): ?object
    {
        $sql = "SELECT l.*,
                       e.full_name AS employee_name,
                       e.emp_code AS employee_code,
                       e.avatar_path,
                       e.phone AS employee_phone,
                       e.email AS employee_email,
                       s.base_salary AS employee_salary,
                       d.dept_name,
                       p.pos_title,
                       t.name AS type_name,
                       t.type_code,
                       u.username AS approver_username,
                       ue.full_name AS approver_fullname
                FROM `{$this->table}` l
                INNER JOIN `employees` e ON l.employee_id = e.id
                LEFT JOIN `salaries` s ON e.id = s.employee_id
                LEFT JOIN `departments` d ON e.department_id = d.id
                LEFT JOIN `positions` p ON e.position_id = p.id
                LEFT JOIN `loan_types` t ON l.loan_type_id = t.id
                LEFT JOIN `users` u ON l.approved_by = u.id
                LEFT JOIN `employees` ue ON u.employee_id = ue.id
                WHERE l.id = :id
                LIMIT 1";

        $this->db->query($sql, ['id' => $id]);
        $row = $this->db->fetch();
        if (!$row) {
            return null;
        }

        $loan = (object)$row;

        // Lấy lịch sử trả nợ
        require_once APP_ROOT . '/models/LoanRepayment.php';
        $repaymentModel = new LoanRepayment();
        $loan->repayments = $repaymentModel->getByLoanId($id);

        return $loan;
    }

    /**
     * Lấy tất cả khoản vay của 1 nhân viên
     */
    public function getByEmployee(int $employeeId): array
    {
        $sql = "SELECT l.*, t.name AS type_name, t.type_code
                FROM `{$this->table}` l
                LEFT JOIN `loan_types` t ON l.loan_type_id = t.id
                WHERE l.employee_id = :employee_id
                ORDER BY l.id DESC";
        $this->db->query($sql, ['employee_id' => $employeeId]);
        return json_decode(json_encode($this->db->fetchAll()));
    }

    /**
     * Lấy các khoản vay đang còn dư nợ (Active) của nhân viên
     */
    public function getActiveByEmployee(int $employeeId): array
    {
        $sql = "SELECT l.*, t.name AS type_name, t.type_code
                FROM `{$this->table}` l
                LEFT JOIN `loan_types` t ON l.loan_type_id = t.id
                WHERE l.employee_id = :employee_id
                  AND l.status = 'Active'
                  AND l.remaining_balance > 0
                ORDER BY l.id ASC";
        $this->db->query($sql, ['employee_id' => $employeeId]);
        return json_decode(json_encode($this->db->fetchAll()));
    }

    /**
     * Tính tổng số tiền khấu trừ khoản vay/tạm ứng hàng tháng của 1 nhân viên
     */
    public function getMonthlyDeductionByEmployee(int $employeeId): float
    {
        $activeLoans = $this->getActiveByEmployee($employeeId);
        $totalDeduction = 0.0;
        foreach ($activeLoans as $loan) {
            // Khấu trừ không vượt quá dư nợ còn lại
            $deduction = min((float)$loan->monthly_emi, (float)$loan->remaining_balance);
            $totalDeduction += $deduction;
        }
        return $totalDeduction;
    }

    /**
     * Tự động sinh mã khoản vay duy nhất (VD: LN-2026-0005)
     */
    public function generateLoanCode(): string
    {
        $year = date('Y');
        $prefix = "LN-{$year}-";

        $this->db->query(
            "SELECT loan_code FROM `{$this->table}` 
             WHERE loan_code LIKE :prefix 
             ORDER BY id DESC LIMIT 1",
            ['prefix' => "{$prefix}%"]
        );
        $latest = $this->db->fetch();

        if ($latest && preg_match('/LN-\d{4}-(\d+)/', $latest['loan_code'], $m)) {
            $nextNum = (int)$m[1] + 1;
        } else {
            $nextNum = 1;
        }

        return sprintf('%s%04d', $prefix, $nextNum);
    }

    /**
     * Thuật toán tính toán trả góp định kỳ (EMI) và tổng nợ
     */
    public static function calculateEmi(float $amount, float $annualRate, int $termMonths): array
    {
        $termMonths = max(1, $termMonths);
        $amount = max(0, $amount);
        $annualRate = max(0, $annualRate);

        if ($annualRate <= 0.0) {
            // Lãi suất 0% (Tạm ứng lương, quỹ phúc lợi)
            $totalInterest = 0.0;
            $totalRepayment = $amount;
            $monthlyEmi = round($amount / $termMonths, 0);
        } else {
            // Lãi suất đơn giản theo dư nợ ban đầu (áp dụng cho khoản vay công đoàn/nội bộ công ty)
            $totalInterest = round($amount * ($annualRate / 100.0) * ($termMonths / 12.0), 0);
            $totalRepayment = $amount + $totalInterest;
            $monthlyEmi = round($totalRepayment / $termMonths, 0);
        }

        return [
            'amount'          => $amount,
            'interest_rate'   => $annualRate,
            'term_months'     => $termMonths,
            'monthly_emi'     => $monthlyEmi,
            'total_interest'  => $totalInterest,
            'total_repayment' => $totalRepayment,
        ];
    }

    /**
     * Phê duyệt khoản vay
     */
    public function approve(int $id, int $approverId, ?string $disbursementDate = null, ?string $disbursementMethod = null): bool
    {
        $loan = $this->find($id);
        if (!$loan || $loan->status !== 'Pending') {
            return false;
        }

        $disbDate = $disbursementDate ?: date('Y-m-d');
        $method = $disbursementMethod ?: ($loan->disbursement_method ?? 'Bank Transfer');

        $this->db->query(
            "UPDATE `{$this->table}` 
             SET `status` = 'Active',
                 `approved_by` = :approver_id,
                 `approved_date` = NOW(),
                 `disbursement_date` = :disb_date,
                 `disbursement_method` = :method
             WHERE `id` = :id",
            [
                'id'          => $id,
                'approver_id' => $approverId,
                'disb_date'   => $disbDate,
                'method'      => $method
            ]
        );

        return true;
    }

    /**
     * Từ chối khoản vay
     */
    public function reject(int $id, int $approverId, string $reason): bool
    {
        $loan = $this->find($id);
        if (!$loan || $loan->status !== 'Pending') {
            return false;
        }

        $this->db->query(
            "UPDATE `{$this->table}` 
             SET `status` = 'Rejected',
                 `approved_by` = :approver_id,
                 `approved_date` = NOW(),
                 `rejected_reason` = :reason
             WHERE `id` = :id",
            [
                'id'          => $id,
                'approver_id' => $approverId,
                'reason'      => $reason
            ]
        );

        return true;
    }

    /**
     * Ghi nhận trả nợ / hoàn ứng (Thủ công hoặc qua Bảng lương)
     */
    public function recordRepayment(
        int $loanId,
        float $amount,
        string $paymentDate,
        string $paymentMethod = 'Manual',
        string $paymentType = 'Manual',
        ?int $payrollId = null,
        ?int $recordedBy = null,
        ?string $notes = null
    ): array {
        $loan = $this->find($loanId);
        if (!$loan) {
            return ['success' => false, 'message' => 'Khoản vay không tồn tại.'];
        }

        if ($loan->status !== 'Active') {
            return ['success' => false, 'message' => 'Khoản vay không ở trạng thái hoạt động (Active).'];
        }

        if ($amount <= 0) {
            return ['success' => false, 'message' => 'Số tiền thanh toán phải lớn hơn 0.'];
        }

        // Kiểm tra số tiền trả không vượt quá dư nợ còn lại
        $curBalance = (float)$loan->remaining_balance;
        $actualPayment = min($amount, $curBalance);

        // Tính phân bổ gốc / lãi ước tính
        $principalPortion = $actualPayment;
        $interestPortion = 0.0;
        if ((float)$loan->interest_rate > 0 && (float)$loan->total_repayment > 0) {
            $interestRatio = ((float)$loan->total_repayment - (float)$loan->amount) / (float)$loan->total_repayment;
            $interestPortion = round($actualPayment * $interestRatio, 0);
            $principalPortion = $actualPayment - $interestPortion;
        }

        $newBalance = max(0.0, $curBalance - $actualPayment);
        $newTotalPaid = (float)$loan->total_paid + $actualPayment;
        $newStatus = ($newBalance <= 0.0) ? 'Closed' : 'Active';

        // Bắt đầu giao dịch lưu
        $this->db->query(
            "INSERT INTO `loan_repayments` 
             (`loan_id`, `payroll_id`, `amount`, `principal_amount`, `interest_amount`, 
              `balance_after`, `payment_date`, `payment_method`, `payment_type`, `recorded_by`, `notes`)
             VALUES 
             (:loan_id, :payroll_id, :amount, :principal, :interest,
              :balance_after, :payment_date, :payment_method, :payment_type, :recorded_by, :notes)",
            [
                'loan_id'        => $loanId,
                'payroll_id'     => $payrollId,
                'amount'         => $actualPayment,
                'principal'      => $principalPortion,
                'interest'       => $interestPortion,
                'balance_after'  => $newBalance,
                'payment_date'   => $paymentDate,
                'payment_method' => $paymentMethod,
                'payment_type'   => $paymentType,
                'recorded_by'    => $recordedBy,
                'notes'          => $notes
            ]
        );

        // Cập nhật lại số liệu khoản vay
        $this->db->query(
            "UPDATE `{$this->table}`
             SET `total_paid` = :total_paid,
                 `remaining_balance` = :remaining_balance,
                 `status` = :status
             WHERE `id` = :id",
            [
                'id'                => $loanId,
                'total_paid'        => $newTotalPaid,
                'remaining_balance' => $newBalance,
                'status'            => $newStatus
            ]
        );

        return [
            'success'           => true,
            'payment_amount'    => $actualPayment,
            'remaining_balance' => $newBalance,
            'is_closed'         => ($newStatus === 'Closed'),
            'message'           => 'Ghi nhận hoàn trả nợ vay thành công.'
        ];
    }

    /**
     * Thống kê tổng hợp số liệu khoản vay
     */
    public function getStats(): array
    {
        $this->db->query(
            "SELECT 
                COUNT(*) AS total_loans,
                COALESCE(SUM(amount), 0) AS total_lent,
                COALESCE(SUM(remaining_balance), 0) AS total_outstanding,
                COALESCE(SUM(total_paid), 0) AS total_repaid,
                SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) AS pending_count,
                SUM(CASE WHEN status = 'Active' THEN 1 ELSE 0 END) AS active_count,
                SUM(CASE WHEN status = 'Closed' THEN 1 ELSE 0 END) AS closed_count,
                SUM(CASE WHEN status = 'Rejected' THEN 1 ELSE 0 END) AS rejected_count
             FROM `{$this->table}`"
        );
        $row = $this->db->fetch();
        return $row ? $row : [
            'total_loans'       => 0,
            'total_lent'        => 0,
            'total_outstanding' => 0,
            'total_repaid'      => 0,
            'pending_count'     => 0,
            'active_count'      => 0,
            'closed_count'      => 0,
            'rejected_count'    => 0,
        ];
    }

    /**
     * Thống kê dư nợ theo phòng ban
     */
    public function getSummaryByDepartment(): array
    {
        $sql = "SELECT d.id AS dept_id,
                       COALESCE(d.dept_name, 'Chưa phân bổ') AS dept_name,
                       COUNT(l.id) AS loan_count,
                       COALESCE(SUM(l.amount), 0) AS total_lent,
                       COALESCE(SUM(l.remaining_balance), 0) AS total_outstanding
                FROM `{$this->table}` l
                INNER JOIN `employees` e ON l.employee_id = e.id
                LEFT JOIN `departments` d ON e.department_id = d.id
                WHERE l.status IN ('Active', 'Pending', 'Closed')
                GROUP BY d.id, d.dept_name
                ORDER BY total_outstanding DESC";
        $this->db->query($sql);
        return json_decode(json_encode($this->db->fetchAll()));
    }

    /**
     * Thống kê dư nợ theo loại khoản vay
     */
    public function getSummaryByType(): array
    {
        $sql = "SELECT t.id AS type_id,
                       t.name AS type_name,
                       t.type_code,
                       COUNT(l.id) AS loan_count,
                       COALESCE(SUM(l.amount), 0) AS total_lent,
                       COALESCE(SUM(l.remaining_balance), 0) AS total_outstanding
                FROM `loan_types` t
                LEFT JOIN `{$this->table}` l ON t.id = l.loan_type_id
                GROUP BY t.id, t.name, t.type_code
                ORDER BY total_outstanding DESC";
        $this->db->query($sql);
        return json_decode(json_encode($this->db->fetchAll()));
    }
}
