<?php
/**
 * ============================================================
 *  POSUNG HRIS – FamilyMember Model
 * ============================================================
 *  Quản lý Quan hệ Gia đình của nhân sự
 *  Tương tác với bảng: emp_family_members
 * ============================================================
 */

class FamilyMember extends BaseModel
{
    protected string $table = 'emp_family_members';

    /**
     * Lấy danh sách thành viên gia đình theo employee_id.
     */
    public function getByEmployee(int $employeeId): array
    {
        $this->db->query(
            "SELECT * FROM `{$this->table}` WHERE employee_id = :eid ORDER BY id ASC",
            ['eid' => $employeeId]
        );
        return $this->db->fetchAll();
    }
}
