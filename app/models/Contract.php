<?php
class Contract extends BaseModel
{
    protected string $table = 'contracts';

    /**
     * Lấy danh sách hợp đồng của nhân viên
     *
     * @param int $employeeId
     * @return array
     */
    public function getByEmployee(int $employeeId): array
    {
        $sql = "SELECT c.*, ct.name as contract_type_name
                FROM {$this->table} c
                LEFT JOIN contract_types ct ON c.contract_type_id = ct.id
                WHERE c.employee_id = :emp_id
                ORDER BY c.start_date DESC";
        $this->db->query($sql, ['emp_id' => $employeeId]);
        return $this->db->fetchAll();
    }

    /**
     * Lấy danh sách hợp đồng sắp hết hạn trong X ngày tới
     *
     * @param int $days
     * @return array
     */
    public function getExpiring(int $days = 30): array
    {
        $sql = "SELECT c.*, e.emp_code, e.full_name, ct.name as contract_type_name
                FROM {$this->table} c
                JOIN employees e ON c.employee_id = e.id
                LEFT JOIN contract_types ct ON c.contract_type_id = ct.id
                WHERE c.status = 'Active' 
                  AND c.end_date IS NOT NULL
                  AND c.end_date <= DATE_ADD(CURDATE(), INTERVAL :days DAY)
                  AND c.end_date >= CURDATE()
                ORDER BY c.end_date ASC";
        $this->db->query($sql, ['days' => $days]);
        return $this->db->fetchAll();
    }
}
