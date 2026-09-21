<?php
/**
 * ============================================================
 *  POSUNG HRIS – EmployeePpe Model
 * ============================================================
 *  Quản lý cấp phát và thu hồi tài sản, bảo hộ lao động (PPE)
 * ============================================================
 */

class EmployeePpe extends BaseModel
{
    protected string $table = 'emp_ppe_issuances';

    /**
     * Lấy danh sách PPE của một nhân viên
     */
    public function getByEmployee(int $employeeId): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE employee_id = :emp_id ORDER BY issue_date DESC, id DESC";
        $this->db->query($sql, ['emp_id' => $employeeId]);
        return $this->db->fetchAll();
    }
}
