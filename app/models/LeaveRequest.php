<?php
class LeaveRequest extends BaseModel
{
    protected string $table = 'leave_requests';

    /**
     * Lấy danh sách đơn xin nghỉ theo nhân viên
     *
     * @param int $employeeId
     * @return array
     */
    public function getByEmployee(int $employeeId): array
    {
        $sql = "SELECT lr.*, lt.name as leave_type_name, lt.is_paid
                FROM {$this->table} lr
                JOIN leave_types lt ON lr.leave_type_id = lt.id
                WHERE lr.employee_id = :emp_id
                ORDER BY lr.start_date DESC";
        $this->db->query($sql, ['emp_id' => $employeeId]);
        return $this->db->fetchAll();
    }

    /**
     * Lấy danh sách đơn xin nghỉ chờ duyệt
     *
     * @return array
     */
    public function getPending(): array
    {
        $sql = "SELECT lr.*, e.emp_code, e.full_name, lt.name as leave_type_name
                FROM {$this->table} lr
                JOIN employees e ON lr.employee_id = e.id
                JOIN leave_types lt ON lr.leave_type_id = lt.id
                WHERE lr.status = 'Pending'
                ORDER BY lr.created_at ASC";
        $this->db->query($sql);
        return $this->db->fetchAll();
    }
}
