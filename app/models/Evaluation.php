<?php
/**
 * ============================================================
 *  POSUNG HRIS – Evaluation Model
 * ============================================================
 *  Quản lý Đánh giá KPI / Năng lực
 *  Tương tác với bảng: emp_evaluations
 * ============================================================
 */

class Evaluation extends BaseModel
{
    protected string $table = 'emp_evaluations';

    /**
     * Lấy lịch sử đánh giá theo employee_id.
     */
    public function getByEmployee(int $employeeId): array
    {
        $this->db->query(
            "SELECT * FROM `{$this->table}` WHERE employee_id = :eid ORDER BY eval_year DESC, id DESC",
            ['eid' => $employeeId]
        );
        return $this->db->fetchAll();
    }
}
