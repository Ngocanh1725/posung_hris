<?php
/**
 * ============================================================
 *  POSUNG HRIS – RbacController (Trung tâm Phân quyền Đa tầng)
 * ============================================================
 */

class RbacController extends Controller
{
    public function __construct()
    {
        if (!Session::isLoggedIn()) {
            $this->redirect('auth/login');
        }
        
        // Chỉ Super Admin hoặc người có quyền rbac_matrix.view mới được truy cập
        $this->requirePermission('rbac_matrix', 'view');
    }

    public function index(): void
    {
        $db = Database::getInstance();
        
        // Đếm số lượng user theo từng role
        $db->query(
            "SELECT r.id, r.code, r.name, r.description, r.level, COUNT(u.id) as user_count 
             FROM roles r 
             LEFT JOIN users u ON r.id = u.role_id 
             GROUP BY r.id, r.code, r.name, r.description, r.level 
             ORDER BY r.level ASC, r.id ASC"
        );
        $roles = $db->fetchAll();

        $this->view('layouts/header', ['pageTitle' => 'Trung tâm Phân quyền']);
        $this->view('rbac/index', ['roles' => $roles]);
        $this->view('layouts/footer');
    }

    public function matrix(?int $roleId = null): void
    {
        $db = Database::getInstance();
        
        $db->query("SELECT * FROM roles ORDER BY level ASC, id ASC");
        $roles = $db->fetchAll();

        if (empty($roles)) {
            Session::setFlash('error', 'Không tìm thấy vai trò nào.');
            $this->redirect('rbac');
        }

        if (!$roleId) {
            $roleId = $roles[0]['id'];
        }

        // Lấy tất cả permissions gom nhóm theo module
        $db->query("SELECT * FROM permissions ORDER BY module_code ASC, id ASC");
        $allPerms = $db->fetchAll();
        
        $groupedPerms = [];
        foreach ($allPerms as $p) {
            $groupedPerms[$p['module_code']][] = $p;
        }

        // Lấy các permission_id đã gán cho role
        $db->query("SELECT permission_id FROM role_permissions WHERE role_id = :role_id", ['role_id' => $roleId]);
        $assignedPerms = array_column($db->fetchAll(), 'permission_id');

        $this->view('layouts/header', ['pageTitle' => 'Ma trận Phân quyền']);
        $this->view('rbac/matrix', [
            'roles' => $roles,
            'currentRoleId' => $roleId,
            'groupedPerms' => $groupedPerms,
            'assignedPerms' => $assignedPerms
        ]);
        $this->view('layouts/footer');
    }

    public function saveMatrix(): void
    {
        $this->requirePermission('rbac_matrix', 'assign');

        if ($this->isPost()) {
            $roleId = (int)$this->postData('role_id');
            $permissions = $_POST['permissions'] ?? [];

            $db = Database::getInstance();
            try {
                $db->beginTransaction();

                // Xóa quyền cũ của role
                $db->query("DELETE FROM role_permissions WHERE role_id = :role_id", ['role_id' => $roleId]);

                // Thêm quyền mới
                if (!empty($permissions)) {
                    foreach ($permissions as $permId) {
                        $db->query(
                            "INSERT INTO role_permissions (role_id, permission_id) VALUES (:role_id, :permission_id)",
                            ['role_id' => $roleId, 'permission_id' => (int)$permId]
                        );
                    }
                }

                $db->commit();
                Session::setFlash('success', 'Lưu phân quyền thành công!');
            } catch (Exception $e) {
                $db->rollBack();
                error_log("Lỗi lưu ma trận quyền: " . $e->getMessage());
                Session::setFlash('error', 'Có lỗi xảy ra khi lưu phân quyền.');
            }
            
            $this->redirect('rbac/matrix/' . $roleId);
        }
    }

    public function userPermissions(int $userId): void
    {
        // ... (Tuỳ chọn phát triển sau hoặc bổ sung dựa trên yêu cầu)
        echo "Tính năng đang hoàn thiện.";
    }
}
