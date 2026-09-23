<?php
/**
 * ============================================================
 *  POSUNG HRIS – Controller Xác thực (Authentication) V2
 * ============================================================
 */

class AuthController extends Controller
{
    // ══════════════════════════════════════════════════════════
    //  ĐĂNG NHẬP
    // ══════════════════════════════════════════════════════════
    public function login(): void
    {
        if (Session::isLoggedIn()) {
            $this->redirect('');
            return;
        }

        $error    = '';
        $username = '';

        if ($this->isPost()) {
            $csrfToken = $this->postData('_csrf_token', '');
            if (!Session::validateCsrfToken($csrfToken)) {
                $error = 'Lỗi bảo mật (CSRF). Vui lòng thử lại.';
            } else {
                $username = $this->postData('username', '');
                $password = $this->postData('password', '');

            if (empty($username) || empty($password)) {
                $error = 'Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu.';
            } else {
                $userModel = $this->model('User');
                $userData  = $userModel->authenticate($username, $password);

                if ($userData !== false) {
                    // XÁC THỰC THÀNH CÔNG -> Tải quyền RBAC
                    $db = Database::getInstance();
                    
                    $userId = (int) $userData['id'];
                    $roleId = (int) ($userData['role_id'] ?? 0);
                    
                    try {
                        // Lấy danh sách Permission Codes
                        $db->query(
                            "SELECT DISTINCT p.action_code
                             FROM permissions p
                             LEFT JOIN role_permissions rp ON p.id = rp.permission_id AND rp.role_id = :role_id
                             LEFT JOIN user_permissions up ON p.id = up.permission_id AND up.user_id = :user_id
                             WHERE (rp.role_id IS NOT NULL AND (up.is_granted IS NULL OR up.is_granted = 1))
                                OR (up.is_granted = 1)",
                            ['role_id' => $roleId, 'user_id' => $userId]
                        );
                        $permissions = array_column($db->fetchAll(), 'action_code');
                        
                        // Lấy danh sách Module Codes
                        $db->query(
                            "SELECT DISTINCT p.module_code
                             FROM permissions p
                             LEFT JOIN role_permissions rp ON p.id = rp.permission_id AND rp.role_id = :role_id
                             LEFT JOIN user_permissions up ON p.id = up.permission_id AND up.user_id = :user_id
                             WHERE (rp.role_id IS NOT NULL AND (up.is_granted IS NULL OR up.is_granted = 1))
                                OR (up.is_granted = 1)",
                            ['role_id' => $roleId, 'user_id' => $userId]
                        );
                        $modules = array_column($db->fetchAll(), 'module_code');
                    } catch (Exception $e) {
                        error_log("Database error in AuthController::login (RBAC): " . $e->getMessage());
                        $permissions = [];
                        $modules = [];
                    }

                    // Nạp vào mảng userData
                    $userData['permissions'] = $permissions;
                    $userData['modules'] = $modules;

                    // Lưu session
                    Session::loginUser($userData);
                    $userModel->updateLastLogin($userId);

                    Session::setFlash('success', 'Xin chào, ' . htmlspecialchars($userData['full_name'] ?? $userData['username']) . '! Đăng nhập thành công.');
                    $this->redirect('');
                    return;

                } else {
                    $error = 'Sai tên đăng nhập hoặc mật khẩu. Vui lòng thử lại.';
                }
            }
            }
        }

        $this->view('auth/login', [
            'error'    => $error,
            'username' => $username,
        ]);
    }

    // ══════════════════════════════════════════════════════════
    //  ĐĂNG XUẤT
    // ══════════════════════════════════════════════════════════
    public function logout(): void
    {
        Session::destroy();
        header('Location: ' . BASE_URL . '/auth/login');
        exit;
    }
}
