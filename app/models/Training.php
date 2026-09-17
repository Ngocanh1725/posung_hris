<?php
/**
 * ============================================================
 *  POSUNG HRIS – Training Model
 * ============================================================
 *  Quản lý Quá trình Đào tạo
 *  Tương tác với bảng: emp_trainings
 * ============================================================
 */

class Training extends BaseModel
{
    protected string $table = 'emp_trainings';

    /**
     * Lấy danh sách quá trình đào tạo theo employee_id.
     */
    public function getByEmployee(int $employeeId): array
    {
        $this->db->query(
            "SELECT * FROM `{$this->table}` WHERE employee_id = :eid ORDER BY from_date DESC",
            ['eid' => $employeeId]
        );
        return $this->db->fetchAll();
    }
}
