<?php
/**
 * ============================================================
 *  POSUNG HRIS – RewardDisciplineHistory Model
 * ============================================================
 *  Quản lý Khen thưởng – Kỷ luật (Lịch sử trong 7 quá trình)
 *  Tương tác với bảng: emp_reward_discipline_histories
 * ============================================================
 */

class RewardDisciplineHistory extends BaseModel
{
    protected string $table = 'emp_reward_discipline_histories';

    /**
     * Lấy lịch sử khen thưởng / kỷ luật theo employee_id.
     */
    public function getByEmployee(int $employeeId): array
    {
        $this->db->query(
            "SELECT * FROM `{$this->table}` WHERE employee_id = :eid ORDER BY decision_date DESC",
            ['eid' => $employeeId]
        );
        return $this->db->fetchAll();
    }
}
