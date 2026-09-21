<?php
/**
 * ============================================================
 *  POSUNG HRIS – Department Model (V2 Schema)
 * ============================================================
 */

class Department extends BaseModel
{
    protected string $table = 'departments';

    /**
     * Lấy toàn bộ phòng ban dạng cây phân cấp (cơ bản).
     */
    public function getTree(): array
    {
        $this->db->query("SELECT * FROM {$this->table} ORDER BY parent_id, code ASC");
        $allDepts = $this->db->fetchAll();

        return $this->buildTree($allDepts);
    }

    /**
     * Lấy cây phân cấp kèm thông tin trưởng phòng (JOIN employees).
     */
    public function getTreeWithDetails(): array
    {
        $this->db->query(
            "SELECT d.*, 
                    e.full_name AS manager_name, 
                    e.employee_code AS manager_code
             FROM {$this->table} d
             LEFT JOIN employees e ON d.manager_id = e.id
             ORDER BY d.parent_id, d.code ASC"
        );
        $allDepts = $this->db->fetchAll();

        return $this->buildTree($allDepts);
    }

    /**
     * Lấy tất cả bộ phận kèm thông tin trưởng phòng (flat list).
     */
    public function allWithManager(string $orderBy = 'code ASC'): array
    {
        $this->db->query(
            "SELECT d.*, 
                    e.full_name AS manager_name, 
                    e.employee_code AS manager_code
             FROM {$this->table} d
             LEFT JOIN employees e ON d.manager_id = e.id
             ORDER BY {$orderBy}"
        );
        return $this->db->fetchAll();
    }

    /**
     * Lấy chi tiết 1 bộ phận kèm tên trưởng phòng.
     */
    public function getDetailById(int $id): ?array
    {
        $this->db->query(
            "SELECT d.*, 
                    e.full_name AS manager_name, 
                    e.employee_code AS manager_code,
                    p.title AS manager_position
             FROM {$this->table} d
             LEFT JOIN employees e ON d.manager_id = e.id
             LEFT JOIN positions p ON e.position_id = p.id
             WHERE d.id = :id
             LIMIT 1",
            ['id' => $id]
        );
        return $this->db->fetch() ?: null;
    }

    /**
     * Lấy danh sách bộ phận theo loại (office, site_pmb, factory).
     */
    public function getDeptByType(string $type): array
    {
        $this->db->query(
            "SELECT d.*, 
                    e.full_name AS manager_name, 
                    e.employee_code AS manager_code
             FROM {$this->table} d
             LEFT JOIN employees e ON d.manager_id = e.id
             WHERE d.type = :type
             ORDER BY d.code ASC",
            ['type' => $type]
        );
        return $this->db->fetchAll();
    }

    /**
     * Lấy danh sách nhân viên thuộc bộ phận.
     */
    public function getEmployees(int $deptId): array
    {
        $this->db->query(
            "SELECT emp.*, p.title AS pos_title
             FROM employees emp
             LEFT JOIN positions p ON emp.position_id = p.id
             WHERE emp.department_id = :id AND emp.status IN ('active','probation')
             ORDER BY emp.full_name ASC",
            ['id' => $deptId]
        );
        return $this->db->fetchAll();
    }

    /**
     * Thống kê quân số thực tế đang Active của một phòng ban.
     */
    public function getHeadcountStats(int $deptId): int
    {
        $this->db->query(
            "SELECT COUNT(*) AS cnt 
             FROM employees 
             WHERE department_id = :id AND status IN ('active','probation')",
            ['id' => $deptId]
        );
        $result = $this->db->fetch();
        return (int)($result['cnt'] ?? 0);
    }

    /**
     * Thống kê tổng hợp cho trang overview.
     */
    public function getOrgSummary(): array
    {
        // Tổng bộ phận
        $this->db->query("SELECT COUNT(*) AS cnt FROM {$this->table}");
        $totalDepts = (int)($this->db->fetch()['cnt'] ?? 0);

        // Đếm theo loại
        $this->db->query(
            "SELECT type, COUNT(*) AS cnt 
             FROM {$this->table} 
             GROUP BY type"
        );
        $typeCounts = $this->db->fetchAll();

        // Tổng NV Active
        $this->db->query("SELECT COUNT(*) AS cnt FROM employees WHERE status IN ('active','probation')");
        $totalEmployees = (int)($this->db->fetch()['cnt'] ?? 0);

        // Tổng dự án đang triển khai
        $this->db->query("SELECT COUNT(*) AS cnt FROM projects WHERE status = 'in_progress'");
        $totalProjects = (int)($this->db->fetch()['cnt'] ?? 0);

        // Quân số theo từng bộ phận
        $this->db->query(
            "SELECT d.id, d.code, d.name, d.type,
                    COUNT(e.id) AS headcount
             FROM {$this->table} d
             LEFT JOIN employees e ON d.id = e.department_id AND e.status IN ('active','probation')
             GROUP BY d.id
             ORDER BY d.type ASC, d.code ASC"
        );
        $deptHeadcounts = $this->db->fetchAll();

        return [
            'totalDepts'      => $totalDepts,
            'typeCounts'      => $typeCounts,
            'totalEmployees'  => $totalEmployees,
            'totalProjects'   => $totalProjects,
            'deptHeadcounts'  => $deptHeadcounts,
        ];
    }

    /**
     * Helper: Đệ quy tạo cây danh mục.
     */
    private function buildTree(array $elements, $parentId = null): array
    {
        $branch = [];
        foreach ($elements as $element) {
            if ($element['parent_id'] == $parentId) {
                $children = $this->buildTree($elements, $element['id']);
                if ($children) {
                    $element['children'] = $children;
                }
                $branch[] = (object)$element;
            }
        }
        return $branch;
    }
}
