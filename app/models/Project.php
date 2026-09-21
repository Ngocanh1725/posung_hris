<?php
/**
 * ============================================================
 *  POSUNG HRIS – Project Model (V2 Schema)
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
        $this->db->query("SELECT * FROM {$this->table} WHERE status = 'in_progress' ORDER BY project_code ASC");
        $results = $this->db->fetchAll();
        return $results;
    }

    /**
     * Lấy tất cả dự án kèm thông tin tổng quát.
     */
    public function getAllProjects(): array
    {
        $this->db->query("SELECT * FROM {$this->table} ORDER BY status DESC, created_at DESC");
        return $this->db->fetchAll();
    }

    /**
     * Lấy danh sách nhân sự hiện hành tại dự án.
     */
    public function getActivePersonnel(int $projectId): array
    {
        $this->db->query(
            "SELECT e.*, p.title AS pos_title, d.name AS dept_name
             FROM employees e
             LEFT JOIN positions p ON e.position_id = p.id
             LEFT JOIN departments d ON e.department_id = d.id
             WHERE e.current_project_id = :id AND e.status IN ('active', 'probation')
             ORDER BY e.full_name ASC",
            ['id' => $projectId]
        );
        return $this->db->fetchAll();
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

    /**
     * Tính toán định biên nhân sự cho một dự án.
     * Trả về mảng: quota, actual, incoming, missing
     */
    public function getHeadcountStats(int $projectId): array
    {
        // 1. Định biên phê duyệt (Quota)
        $this->db->query("SELECT headcount_quota FROM {$this->table} WHERE id = :id", ['id' => $projectId]);
        $row = $this->db->fetch();
        $quota = (int)($row['headcount_quota'] ?? 0);

        // 2. Số nhân sự thực tế hiện hữu
        $this->db->query(
            "SELECT COUNT(*) as cnt FROM employees WHERE current_project_id = :id AND status IN ('active', 'probation')",
            ['id' => $projectId]
        );
        $actual = (int)($this->db->fetch()['cnt'] ?? 0);

        // 3. Số nhân sự đang điều động đến (chờ hiệu lực)
        // Lấy những record transfer đến dự án này mà status = 'approved' và effective_date >= CURDATE()
        $this->db->query(
            "SELECT COUNT(*) as cnt FROM job_movements 
             WHERE to_project_id = :id 
               AND status = 'approved' 
               AND effective_date > CURDATE()",
            ['id' => $projectId]
        );
        $incoming = (int)($this->db->fetch()['cnt'] ?? 0);

        // 4. Số còn thiếu
        $missing = $quota - ($actual + $incoming);
        if ($missing < 0) $missing = 0;

        return [
            'quota'    => $quota,
            'actual'   => $actual,
            'incoming' => $incoming,
            'missing'  => $missing,
        ];
    }
}
