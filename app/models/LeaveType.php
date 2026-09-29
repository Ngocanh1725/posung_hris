<?php
/**
 * ============================================================
 *  POSUNG HRIS – LeaveType Model
 * ============================================================
 */

class LeaveType extends BaseModel
{
    protected string $table = 'leave_types';

    /**
     * Lấy tất cả loại phép kèm cấu hình chính sách
     */
    public function getActiveTypes(): array
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY id ASC";
        $this->db->query($sql);
        return $this->db->fetchAll();
    }

    /**
     * Lấy cấu hình của một loại phép
     */
    public function findById(int $id): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
        $this->db->query($sql, ['id' => $id]);
        $row = $this->db->fetch();
        return $row ?: null;
    }
}
