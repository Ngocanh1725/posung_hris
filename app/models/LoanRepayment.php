<?php
/**
 * ============================================================
 *  POSUNG HRIS – LoanRepayment Model
 * ============================================================
 *  Quản lý Lịch sử Hoàn trả & Khấu trừ Khoản vay
 * ============================================================
 */

require_once APP_ROOT . '/models/BaseModel.php';

class LoanRepayment extends BaseModel
{
    protected string $table = 'loan_repayments';

    /**
     * Lấy toàn bộ lịch sử thanh toán của 1 khoản vay
     */
    public function getByLoanId(int $loanId): array
    {
        $this->db->query(
            "SELECT r.*, u.username as recorder_name, ue.full_name as recorder_full_name
             FROM `{$this->table}` r
             LEFT JOIN `users` u ON r.recorded_by = u.id
             LEFT JOIN `employees` ue ON u.employee_id = ue.id
             WHERE r.loan_id = :loan_id
             ORDER BY r.payment_date ASC, r.id ASC",
            ['loan_id' => $loanId]
        );
        return json_decode(json_encode($this->db->fetchAll()));
    }

    /**
     * Lấy các khoản khấu trừ theo kỳ lương (payroll_id)
     */
    public function getByPayrollId(int $payrollId): array
    {
        $this->db->query(
            "SELECT r.*, l.loan_code, e.full_name, e.emp_code
             FROM `{$this->table}` r
             INNER JOIN `employee_loans` l ON r.loan_id = l.id
             INNER JOIN `employees` e ON l.employee_id = e.id
             WHERE r.payroll_id = :payroll_id
             ORDER BY r.payment_date ASC",
            ['payroll_id' => $payrollId]
        );
        return json_decode(json_encode($this->db->fetchAll()));
    }
}
