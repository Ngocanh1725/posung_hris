<?php
/**
 * ============================================================
 *  POSUNG HRIS – Appointment Model
 * ============================================================
 *  Quản lý Quá trình Bổ nhiệm
 *  Tương tác với bảng: emp_appointments
 * ============================================================
 */

class Appointment extends BaseModel
{
    protected string $table = 'emp_appointments';

    /**
     * Lấy lịch sử bổ nhiệm theo employee_id.
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
