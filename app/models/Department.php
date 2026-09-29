<?php
/**
 * ============================================================
 *  POSUNG HRIS – Department Model (V2 Schema & Org Chart)
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
        $this->db->query("SELECT * FROM {$this->table} ORDER BY sort_order ASC, parent_id ASC, dept_code ASC");
        $allDepts = $this->db->fetchAll();

        return $this->buildTree($allDepts);
    }

    /**
     * Lấy cây phân cấp kèm thông tin chi tiết trưởng phòng & quân số.
     */
    public function getTreeWithDetails(): array
    {
        $this->db->query(
            "SELECT d.*, 
                    e.full_name AS manager_name, 
                    e.emp_code AS manager_code,
                    e.avatar_path AS manager_avatar,
                    e.gender AS manager_gender,
                    p.pos_title AS manager_position,
                    (SELECT COUNT(*) FROM employees emp WHERE emp.department_id = d.id AND emp.status IN ('active','probation')) AS employee_count,
                    (SELECT COUNT(*) FROM employees emp WHERE emp.department_id = d.id AND emp.status IN ('active','probation') AND emp.gender = 'Male') AS male_count,
                    (SELECT COUNT(*) FROM employees emp WHERE emp.department_id = d.id AND emp.status IN ('active','probation') AND emp.gender = 'Female') AS female_count
             FROM {$this->table} d
             LEFT JOIN employees e ON d.manager_id = e.id
             LEFT JOIN positions p ON e.position_id = p.id
             ORDER BY d.sort_order ASC, d.id ASC"
        );
        $allDepts = $this->db->fetchAll();

        return $this->buildTree($allDepts, null);
    }

    /**
     * Lấy tất cả bộ phận kèm thông tin trưởng phòng (flat list).
     */
    public function allWithManager(string $orderBy = 'd.sort_order ASC, d.dept_code ASC'): array
    {
        $this->db->query(
            "SELECT d.*, 
                    e.full_name AS manager_name, 
                    e.emp_code AS manager_code,
                    e.avatar_path AS manager_avatar,
                    e.gender AS manager_gender,
                    p.pos_title AS manager_position,
                    parent.dept_name AS parent_name,
                    (SELECT COUNT(*) FROM employees emp WHERE emp.department_id = d.id AND emp.status IN ('active','probation')) AS employee_count,
                    (SELECT COUNT(*) FROM employees emp WHERE emp.department_id = d.id AND emp.status IN ('active','probation') AND emp.gender = 'Male') AS male_count,
                    (SELECT COUNT(*) FROM employees emp WHERE emp.department_id = d.id AND emp.status IN ('active','probation') AND emp.gender = 'Female') AS female_count
             FROM {$this->table} d
             LEFT JOIN employees e ON d.manager_id = e.id
             LEFT JOIN positions p ON e.position_id = p.id
             LEFT JOIN {$this->table} parent ON d.parent_id = parent.id
             ORDER BY {$orderBy}"
        );
        return $this->db->fetchAll();
    }

    /**
     * Dữ liệu phân cấp chuẩn cho API OrgChart
     */
    public function getChartHierarchy(): array
    {
        $tree = $this->getTreeWithDetails();
        return $this->formatNodeForChart($tree);
    }

    private function formatNodeForChart(array $nodes): array
    {
        $result = [];
        foreach ($nodes as $node) {
            $formatted = [
                'id'             => (int)$node->id,
                'name'           => $node->dept_name,
                'code'           => $node->dept_code,
                'type'           => $node->type ?? 'office',
                'parent_id'      => !empty($node->parent_id) ? (int)$node->parent_id : null,
                'employee_count' => (int)($node->employee_count ?? 0),
                'total_headcount'=> (int)($node->total_headcount ?? $node->employee_count ?? 0),
                'male_count'     => (int)($node->male_count ?? 0),
                'female_count'   => (int)($node->female_count ?? 0),
                'phone'          => $node->phone ?? '',
                'email'          => $node->email ?? '',
                'office_location'=> $node->office_location ?? '',
                'manager'        => [
                    'id'       => !empty($node->manager_id) ? (int)$node->manager_id : null,
                    'name'     => $node->manager_name ?? 'Chưa bổ nhiệm',
                    'code'     => $node->manager_code ?? '',
                    'position' => $node->manager_position ?? 'Trưởng đơn vị',
                    'avatar'   => $node->manager_avatar ?? '',
                    'gender'   => $node->manager_gender ?? 'Male',
                ],
                'children'       => !empty($node->children) ? $this->formatNodeForChart($node->children) : []
            ];
            $result[] = $formatted;
        }
        return $result;
    }

    /**
     * Lấy chi tiết 1 bộ phận kèm tên trưởng phòng.
     */
    public function getDetailById(int $id): ?array
    {
        $this->db->query(
            "SELECT d.*, 
                    e.full_name AS manager_name, 
                    e.emp_code AS manager_code,
                    e.avatar_path AS manager_avatar,
                    p.pos_title AS manager_position,
                    parent.dept_name AS parent_name,
                    (SELECT COUNT(*) FROM employees emp WHERE emp.department_id = d.id AND emp.status IN ('active','probation')) AS employee_count
             FROM {$this->table} d
             LEFT JOIN employees e ON d.manager_id = e.id
             LEFT JOIN positions p ON e.position_id = p.id
             LEFT JOIN {$this->table} parent ON d.parent_id = parent.id
             WHERE d.id = :id
             LIMIT 1",
            ['id' => $id]
        );
        return $this->db->fetch() ?: null;
    }

    /**
     * Lấy danh sách bộ phận con.
     */
    public function getChildren(int $parentId): array
    {
        $this->db->query(
            "SELECT d.*, 
                    e.full_name AS manager_name, 
                    e.emp_code AS manager_code,
                    e.avatar_path AS manager_avatar,
                    (SELECT COUNT(*) FROM employees emp WHERE emp.department_id = d.id AND emp.status IN ('active','probation')) AS employee_count
             FROM {$this->table} d
             LEFT JOIN employees e ON d.manager_id = e.id
             WHERE d.parent_id = :parent_id
             ORDER BY d.sort_order ASC, d.dept_code ASC",
            ['parent_id' => $parentId]
        );
        return $this->db->fetchAll();
    }

    /**
     * Lấy danh sách bộ phận theo loại (office, site_pmb, factory).
     */
    public function getDeptByType(string $type): array
    {
        $this->db->query(
            "SELECT d.*, 
                    e.full_name AS manager_name, 
                    e.emp_code AS manager_code,
                    e.avatar_path AS manager_avatar
             FROM {$this->table} d
             LEFT JOIN employees e ON d.manager_id = e.id
             WHERE d.type = :type
             ORDER BY d.sort_order ASC, d.dept_code ASC",
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
            "SELECT emp.*, p.pos_title AS pos_title
             FROM employees emp
             LEFT JOIN positions p ON emp.position_id = p.id
             WHERE emp.department_id = :id AND emp.`status` IN ('active','probation')
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
             WHERE department_id = :id AND `status` IN ('active','probation')",
            ['id' => $deptId]
        );
        $result = $this->db->fetch();
        return (int)($result['cnt'] ?? 0);
    }

    /**
     * Thống kê toàn diện nhân sự theo phòng ban
     */
    public function getDepartmentStatistics(): array
    {
        // 1. Tổng bộ phận
        $this->db->query("SELECT COUNT(*) AS cnt FROM {$this->table}");
        $totalDepts = (int)($this->db->fetch()['cnt'] ?? 0);

        // 2. Tổng NV Active
        $this->db->query("SELECT COUNT(*) AS cnt FROM employees WHERE `status` IN ('active','probation')");
        $totalEmployees = (int)($this->db->fetch()['cnt'] ?? 0);

        // 3. Quân số theo từng bộ phận
        $this->db->query(
            "SELECT d.id, d.dept_code AS code, d.dept_name AS name, d.type,
                    e.full_name AS manager_name, e.emp_code AS manager_code,
                    COUNT(emp.id) AS headcount,
                    SUM(CASE WHEN emp.gender = 'Male' THEN 1 ELSE 0 END) AS male_count,
                    SUM(CASE WHEN emp.gender = 'Female' THEN 1 ELSE 0 END) AS female_count
             FROM {$this->table} d
             LEFT JOIN employees e ON d.manager_id = e.id
             LEFT JOIN employees emp ON d.id = emp.department_id AND emp.`status` IN ('active','probation')
             GROUP BY d.id
             ORDER BY headcount DESC, d.dept_code ASC"
        );
        $deptHeadcounts = $this->db->fetchAll();

        // 4. Phòng ban lớn nhất
        $largestDept = !empty($deptHeadcounts) ? $deptHeadcounts[0] : null;

        // 5. Trung bình nhân sự / phòng ban
        $avgHeadcount = $totalDepts > 0 ? round($totalEmployees / $totalDepts, 1) : 0;

        // 6. Tỷ lệ giới tính
        $totalMale = array_sum(array_column($deptHeadcounts, 'male_count'));
        $totalFemale = array_sum(array_column($deptHeadcounts, 'female_count'));

        // 7. Phân bổ theo loại hình
        $typeStats = [
            'office'   => ['label' => 'Khối Văn phòng', 'count' => 0, 'employees' => 0],
            'factory'  => ['label' => 'Khối Sản xuất / Xưởng', 'count' => 0, 'employees' => 0],
            'site_pmb' => ['label' => 'Ban QLDA Công trường', 'count' => 0, 'employees' => 0],
            'bod'      => ['label' => 'Ban Giám đốc', 'count' => 0, 'employees' => 0],
        ];

        foreach ($deptHeadcounts as $dept) {
            $t = $dept['type'] ?? 'office';
            if (!isset($typeStats[$t])) {
                $typeStats[$t] = ['label' => ucfirst($t), 'count' => 0, 'employees' => 0];
            }
            $typeStats[$t]['count']++;
            $typeStats[$t]['employees'] += (int)$dept['headcount'];
        }

        return [
            'total_depts'     => $totalDepts,
            'total_employees' => $totalEmployees,
            'largest_dept'    => $largestDept,
            'avg_headcount'   => $avgHeadcount,
            'total_male'      => $totalMale,
            'total_female'    => $totalFemale,
            'dept_headcounts' => $deptHeadcounts,
            'type_stats'      => $typeStats,
        ];
    }

    /**
     * Thống kê tổng hợp cho trang overview cũ.
     */
    public function getOrgSummary(): array
    {
        $stats = $this->getDepartmentStatistics();

        // Tổng dự án đang triển khai
        $this->db->query("SELECT COUNT(*) AS cnt FROM projects WHERE `status` = 'In_Progress'");
        $totalProjects = (int)($this->db->fetch()['cnt'] ?? 0);

        // Đếm theo loại
        $this->db->query("SELECT type, COUNT(*) AS cnt FROM {$this->table} GROUP BY type");
        $typeCounts = $this->db->fetchAll();

        return [
            'totalDepts'      => $stats['total_depts'],
            'typeCounts'      => $typeCounts,
            'totalEmployees'  => $stats['total_employees'],
            'totalProjects'   => $totalProjects,
            'deptHeadcounts'  => $stats['dept_headcounts'],
            'largestDept'     => $stats['largest_dept'],
        ];
    }

    /**
     * Helper: Đệ quy tạo cây danh mục.
     */
    private function buildTree(array $elements, $parentId = null): array
    {
        $branch = [];
        foreach ($elements as $element) {
            $pId = !empty($element['parent_id']) ? (int)$element['parent_id'] : null;
            if ($pId === $parentId) {
                $children = $this->buildTree($elements, (int)$element['id']);
                $totalCount = (int)($element['employee_count'] ?? 0);
                if (!empty($children)) {
                    $element['children'] = $children;
                    foreach ($children as $c) {
                        $totalCount += (int)($c->total_headcount ?? $c->employee_count ?? 0);
                    }
                } else {
                    $element['children'] = [];
                }
                $element['total_headcount'] = $totalCount;
                $branch[] = (object)$element;
            }
        }
        return $branch;
    }
}
