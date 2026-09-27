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
        $isSuperAdmin = Session::isSuperAdmin();

        // 1. Lấy toàn bộ cây menu (bao gồm cả cha và con)
        $fullTree = $this->moduleModel->getAllModulesTree();

        if ($isSuperAdmin) {
            return $fullTree;
        }

        // 2. Lọc cây menu theo quyền thực tế
        return $this->filterTreeByPermission($fullTree);
    }

    /**
     * Đệ quy lọc cây menu dựa trên quyền.
     * Quy tắc:
     * - Node lá (không có con): Giữ lại nếu có quyền.
     * - Node cha (có con): Sau khi lọc các con, nếu không còn con nào -> Xóa luôn node cha (vì là thư mục rỗng).
     */
    private function filterTreeByPermission(array $tree): array
    {
        $result = [];
        foreach ($tree as $node) {
            // Kiểm tra xem có quyền truy cập bản thân node này không
            $hasPerm = empty($node['permission_required']) || Session::hasPermission($node['permission_required']);
            
            $originallyHadChildren = !empty($node['children']);
            
            if ($originallyHadChildren) {
                // Lọc đệ quy các con
                $node['children'] = $this->filterTreeByPermission($node['children']);
                
                // Nếu sau khi lọc mà còn con thì giữ lại thư mục này
                if (!empty($node['children'])) {
                    $result[] = $node;
                }
                // Nếu bị rỗng, bỏ qua luôn (không thêm vào $result)
            } else {
                // Node lá: Phải có quyền thì mới hiển thị
                if ($hasPerm) {
                    // Đảm bảo không phải là danh mục rỗng (ví dụ URL rỗng hoặc #)
                    if (!empty($node['url']) && $node['url'] !== '#') {
                        $result[] = $node;
                    }
                }
            }
        }
        return $result;
    }
}
