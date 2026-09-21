<?php
class EmployeeAllowance extends BaseModel
{
    protected string $table = 'employee_allowances';

    /**
     * Lấy danh sách phụ cấp được gán cho nhân viên
     *
     * @param int $employeeId
     * @return array
     */
    public function getByEmployee(int $employeeId): array
    {
        $sql = "SELECT ea.*, a.code, a.name, a.type, a.is_taxable
                FROM {$this->table} ea
                JOIN allowances a ON ea.allowance_id = a.id
                WHERE ea.employee_id = :emp_id
                ORDER BY a.type ASC";
        $this->db->query($sql, ['emp_id' => $employeeId]);
        return $this->db->fetchAll();
    }
}
