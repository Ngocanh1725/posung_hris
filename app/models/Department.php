<?php
/**
 * ============================================================
 *  POSUNG HRIS – Department Model
 * ============================================================
 */

class Department extends BaseModel
{
    protected string $table = 'departments';

    /**
     * Lấy toàn bộ phòng ban dạng cây phân cấp.
     */
    public function getTree(): array
    {
        $this->db->query("SELECT * FROM {$this->table} ORDER BY parent_id, dept_code ASC");
        $allDepts = $this->db->fetchAll();

        return $this->buildTree($allDepts);
    }

    /**
     * Thống kê quân số thực tế đang Active của một phòng ban.
     */
    public function getHeadcountStats(int $deptId): int
    {
        $this->db->query(
            "SELECT COUNT(*) AS cnt 
             FROM employees 
             WHERE department_id = :id AND status = 'Active'",
            ['id' => $deptId]
        );
        $result = $this->db->fetch();
        return (int)($result['cnt'] ?? 0);
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
                // Ép kiểu mảng thành object để view sử dụng ->id
                $branch[] = (object)$element;
            }
        }
        return $branch;
    }
}
