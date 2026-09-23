<?php
/**
 * ============================================================
 *  POSUNG HRIS – Model Module (Phân hệ / Menu)
 * ============================================================
 */

class Module
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Lấy danh sách tất cả các module dưới dạng cây phân cấp.
     */
    public function getAllModulesTree(): array
    {
        $this->db->query(
            "SELECT id, parent_id, title as name, url, icon, sort_order, is_active as status, permission_required
             FROM system_menus 
             ORDER BY parent_id ASC, sort_order ASC"
        );
        $modules = $this->db->fetchAll();

        return $this->buildTree($modules);
    }

    /**
     * Lấy các module mà một user cụ thể có quyền truy cập.
     */
    public function getAccessibleModules(int $userId, bool $isSuperAdmin): array
    {
        $this->db->query(
            "SELECT id, parent_id, title as name, url, icon, sort_order, is_active as status, permission_required
             FROM system_menus 
             WHERE is_active = 1
             ORDER BY sort_order ASC"
        );
        $allMenus = $this->db->fetchAll();

        if ($isSuperAdmin) {
            return $allMenus;
        }

        $accessible = [];
        foreach ($allMenus as $menu) {
            if (empty($menu['permission_required']) || Session::hasPermission($menu['permission_required'])) {
                $accessible[] = $menu;
            }
        }
        return $accessible;
    }

    /**
     * Helper: Xây dựng cấu trúc cây từ danh sách phẳng
     */
    private function buildTree(array $elements, $parentId = null): array
    {
        $branch = [];

        foreach ($elements as $element) {
            if ($element['parent_id'] == $parentId) {
                $children = $this->buildTree($elements, $element['id']);
                if ($children) {
                    $element['children'] = $children;
                } else {
                    $element['children'] = [];
                }
                $branch[] = $element;
            }
        }

        return $branch;
    }
}
