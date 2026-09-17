<?php
/**
 * ============================================================
 *  POSUNG HRIS – WorkHistory Model
 * ============================================================
 *  Quản lý Quá trình Công tác (Dự án / Kinh nghiệm làm việc)
 *  Tương tác với bảng: emp_work_histories
 * ============================================================
 */

class WorkHistory extends BaseModel
{
    protected string $table = 'emp_work_histories';

    /**
     * Lấy danh sách quá trình công tác theo employee_id.
     *
     * @param  int   $employeeId
     * @return array Mảng các bản ghi, sắp xếp theo from_date giảm dần
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
