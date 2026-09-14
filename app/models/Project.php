<?php
/**
 * ============================================================
 *  POSUNG HRIS – Project Model
 * ============================================================
 */

class Project extends BaseModel
{
    protected string $table = 'projects';

    /**
     * Lấy danh sách các dự án đang hoạt động.
     */
    public function getActiveProjects(): array
    {
        $this->db->query("SELECT * FROM {$this->table} WHERE status = 'In_Progress' ORDER BY project_code ASC");
        $results = $this->db->fetchAll();
        return json_decode(json_encode($results)); // Cast to objects
    }

    /**
     * Cập nhật tiến độ / trạng thái dự án.
     */
    public function updateStatus(int $id, string $status): bool
    {
        $this->db->query(
            "UPDATE {$this->table} SET status = :status WHERE id = :id",
            ['status' => $status, 'id' => $id]
        );
        return true;
    }
}
