<?php
/**
 * ============================================================
 *  POSUNG HRIS – SalaryProgression Model
 * ============================================================
 *  Quản lý Diễn biến Lương (Ngạch, Bậc, Hệ số)
 *  Tương tác với bảng: emp_salary_progressions
 * ============================================================
 */

class SalaryProgression extends BaseModel
{
    protected string $table = 'emp_salary_progressions';

    /**
     * Lấy diễn biến lương theo employee_id.
     */
    public function getByEmployee(int $employeeId): array
    {
        $this->db->query(
            "SELECT * FROM `{$this->table}` WHERE employee_id = :eid ORDER BY effective_date DESC",
            ['eid' => $employeeId]
        );
        return $this->db->fetchAll();
    }
}
