<?php
/**
 * ============================================================
 *  POSUNG HRIS – Model Navigation (Thanh điều hướng)
 * ============================================================
 *  Model chuyên trách xử lý cấu trúc Menu đa cấp dựa trên
 *  quyền hạn (RBAC) của người dùng hiện tại.
 */

class Navigation
{
    private Module $moduleModel;

    public function __construct()
    {
        $this->moduleModel = new Module();
    }

    /**
     * Truy vấn và trả về mảng dữ liệu menu đa cấp
     * Chỉ bao gồm các module mà user có quyền xem.
     */
    public function renderMenu(int $userId): array
    {
        // Kiểm tra quyền Super Admin từ Session
        $isSuperAdmin = Session::isSuperAdmin();

        // 1. Lấy danh sách các Module được phép truy cập
        $accessibleModules = $this->moduleModel->getAccessibleModules($userId, $isSuperAdmin);

        // 2. Thuật toán chuyển đổi danh sách phẳng thành Cây (Tree)
        $tree = [];
        $mapped = [];

        // Khởi tạo thuộc tính children và map by ID
        foreach ($accessibleModules as $m) {
            $m['children'] = [];
            $mapped[$m['id']] = $m;
        }

        // Đẩy vào cây
        foreach ($accessibleModules as $m) {
            if (!empty($m['parent_id']) && isset($mapped[$m['parent_id']])) {
                // Nếu có cha, đẩy tham chiếu vào mảng con của cha
                $mapped[$m['parent_id']]['children'][] = &$mapped[$m['id']];
            } else {
                // Nếu không có cha (Node gốc), đẩy vào cây chính
                $tree[] = &$mapped[$m['id']];
            }
        }

        return $tree;
    }
}
