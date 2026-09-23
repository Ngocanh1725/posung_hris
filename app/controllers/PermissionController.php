<?php
/**
 * ============================================================
 *  POSUNG HRIS – PermissionController
 *  Xử lý Giao diện Ma trận phân quyền
 * ============================================================
 */

class PermissionController extends Controller
{
    private Permission $permissionModel;
    private User $userModel;

    public function __construct()
    {
        // Chặn người dùng chưa đăng nhập
        if (!Session::isLoggedIn()) {
            Session::setFlash('error', 'Vui lòng đăng nhập.');
            $this->redirect('auth/login');
        }

        $this->permissionModel = $this->model('Permission');
        $this->userModel = $this->model('User');
    }

    /**
     * Hiển thị ma trận phân quyền cho một user.
     */
    public function matrix(int $targetUserId): void
    {
        $currentUserId = Session::userId();
        $isSuperAdmin = Session::isSuperAdmin();
        $canDelegate = Session::get('can_delegate', false);

        // Phải là Super Admin hoặc có cờ can_delegate mới được phép vào
        if (!$isSuperAdmin && !$canDelegate) {
            Session::setFlash('error', 'Bạn không có quyền ủy quyền (delegate).');
            $this->redirect('user');
        }

        $targetUser = $this->userModel->getUserById($targetUserId);
        if (!$targetUser) {
            Session::setFlash('error', 'Không tìm thấy tài khoản đích.');
            $this->redirect('user');
        }
        
        $targetUserRoleInfo = $this->userModel->getUserRoleInfo($targetUserId);

        // Lấy ma trận quyền
        $allPermissionsGrouped = $this->permissionModel->getPermissionsGroupedByModule();

        // Lấy danh sách quyền của target user
        $targetPerms = $this->permissionModel->getUserEffectivePermissions($targetUserId);
        $targetPermIds = array_column($targetPerms, 'id');

        // Lấy danh sách quyền của current user (nếu là Sub Admin)
        $currentUserPermIds = [];
        if (!$isSuperAdmin) {
            $currentUserPerms = $this->permissionModel->getUserEffectivePermissions($currentUserId);
            $currentUserPermIds = array_column($currentUserPerms, 'id');
        }

        $this->view('permission/matrix', [
            'targetUser' => $targetUser,
            'targetUserRoleInfo' => $targetUserRoleInfo,
            'allPermissionsGrouped' => $allPermissionsGrouped,
            'targetPermIds' => $targetPermIds,
            'currentUserPermIds' => $currentUserPermIds,
            'isSuperAdmin' => $isSuperAdmin,
            'pageTitle' => 'Ma trận Phân quyền'
        ]);
    }

    /**
     * Xử lý lưu các checkbox được tích (Cấp quyền).
     */
    public function saveMatrix(): void
    {
        if (!$this->isPost()) {
            $this->redirect('user');
        }

        $currentUserId = Session::userId();
        $isSuperAdmin = Session::isSuperAdmin();
        $canDelegate = Session::get('can_delegate', false);

        if (!$isSuperAdmin && !$canDelegate) {
            Session::setFlash('error', 'Bạn không có quyền ủy quyền.');
            $this->redirect('user');
        }

        $targetUserId = (int) $this->postData('user_id');
        $permissions = $_POST['permissions'] ?? [];
        $permissions = array_map('intval', $permissions); // Lọc chỉ lấy ID nguyên

        // Cập nhật cờ can_delegate nếu là Super Admin
        if ($isSuperAdmin) {
            $canDelegateInput = $this->postData('can_delegate') ? 1 : 0;
            $db = Database::getInstance();
            $db->query("UPDATE users SET can_delegate = :cd WHERE id = :id", [
                'cd' => $canDelegateInput, 
                'id' => $targetUserId
            ]);
        }

        // Gọi model xử lý lưu (Đã có sẵn Security Boundary trong PermissionModel)
        $success = $this->permissionModel->saveUserPermissions($targetUserId, $permissions, $currentUserId);

        if ($success) {
            Session::setFlash('success', 'Đã lưu phân quyền thành công.');
        } else {
            Session::setFlash('error', 'Có lỗi xảy ra khi lưu phân quyền.');
        }

        $this->redirect('permission/matrix/' . $targetUserId);
    }
}
