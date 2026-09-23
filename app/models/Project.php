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
        $this->db->query("SELECT * FROM {$this->table} WHERE status = 'In_Progress' ORDER BY project_code ASC");
        $results = $this->db->fetchAll();
        return $results;
    }

    /**
     * Lấy tất cả dự án kèm thông tin tổng quát.
     */
    public function getAllProjects(): array
    {
        $this->db->query("SELECT p.*, e1.full_name AS site_manager_name, e2.full_name AS hse_lead_name
                          FROM {$this->table} p
                          LEFT JOIN employees e1 ON p.site_manager_id = e1.id
                          LEFT JOIN employees e2 ON p.hse_lead_id = e2.id
                          ORDER BY p.status DESC, p.created_at DESC");
        return $this->db->fetchAll();
    }

    /**
     * Lấy danh sách nhân sự hiện hành tại dự án.
     */
    public function getActivePersonnel(int $projectId): array
    {
        $this->db->query(
            "SELECT e.*, p.pos_title, d.dept_name
             FROM employees e
             LEFT JOIN positions p ON e.position_id = p.id
             LEFT JOIN departments d ON e.department_id = d.id
             WHERE e.current_project_id = :id AND e.status IN ('active', 'probation')
             ORDER BY e.emp_code ASC",
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
        $this->db->query("SELECT headcount_budget FROM {$this->table} WHERE id = :id", ['id' => $projectId]);
        $row = $this->db->fetch();
        $quota = (int)($row['headcount_budget'] ?? 0);

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
               AND approved_by IS NOT NULL 
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



    /**
     * Xây dựng sơ đồ tổ chức dạng cây cho dự án
     */
    public function buildProjectOrgChart(int $projectId): array
    {
        $project = $this->find($projectId);
        $siteManagerId = $project ? $project->site_manager_id : null;

        $personnel = $this->getActivePersonnel($projectId);
        
        $indexed = [];
        foreach ($personnel as $p) {
            $p['children'] = [];
            $indexed[$p['id']] = $p;
        }

        $tree = [];
        
        foreach ($indexed as $id => &$p) {
            $parentId = $p['direct_manager_id'];
            // Chỉ gắn vào parent nếu parent cũng nằm trong dự án này
            if ($parentId && isset($indexed[$parentId])) {
                $indexed[$parentId]['children'][] = &$p;
            } else {
                $tree[] = &$p;
            }
        }
        
        // Nếu có site_manager_id và site_manager nằm trong tree, ta có thể đẩy site_manager lên đầu
        if ($siteManagerId && isset($indexed[$siteManagerId])) {
            $root = [];
            foreach ($tree as $k => $t) {
                if ($t['id'] == $siteManagerId) {
                    $root[] = $t;
                    unset($tree[$k]);
                }
            }
            // Các nhân sự không có quản lý sẽ được đưa làm con của site_manager (tuỳ chọn)
            // Tạm thời giữ nguyên phân cấp thực tế
            $tree = array_merge($root, array_values($tree));
        }

        return $tree;
    }
}
