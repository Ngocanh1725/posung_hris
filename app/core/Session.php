<?php
/**
 * ============================================================
 *  POSUNG HRIS – Quản lý Session & Phân quyền RBAC (V2)
 * ============================================================
 */

class Session
{
    // ══════════════════════════════════════════════════════════
    //  PHẦN 1: KHỞI TẠO SESSION AN TOÀN
    // ══════════════════════════════════════════════════════════
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_name(SESSION_NAME);
            session_set_cookie_params([
                'lifetime' => SESSION_LIFETIME,
                'path'     => '/',
                'domain'   => '',
                'secure'   => false,
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            session_start();

            if (!isset($_SESSION['_session_created'])) {
                $_SESSION['_session_created'] = time();
            } elseif (time() - $_SESSION['_session_created'] > 1800) {
                session_regenerate_id(true);
                $_SESSION['_session_created'] = time();
            }
        }
    }

    // ══════════════════════════════════════════════════════════
    //  PHẦN 2: THAO TÁC DỮ LIỆU SESSION (CRUD)
    // ══════════════════════════════════════════════════════════
    public static function set(string $key, mixed $val): void
    {
        $_SESSION[$key] = $val;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function destroy(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
    }

    // ══════════════════════════════════════════════════════════
    //  PHẦN 3: TIN NHẮN FLASH
    // ══════════════════════════════════════════════════════════
    public static function setFlash(string $key, string $msg): void
    {
        $_SESSION['_flash'][$key] = $msg;
    }

    public static function getFlash(string $key): ?string
    {
        $msg = $_SESSION['_flash'][$key] ?? null;
        unset($_SESSION['_flash'][$key]);
        return $msg;
    }

    public static function hasFlash(string $key): bool
    {
        return isset($_SESSION['_flash'][$key]);
    }

    // ══════════════════════════════════════════════════════════
    //  PHẦN 4: KIỂM TRA XÁC THỰC
    // ══════════════════════════════════════════════════════════
    public static function isLoggedIn(): bool
    {
        return isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0;
    }

    public static function userId(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }

    public static function userRole(): ?string
    {
        return $_SESSION['user_role'] ?? null;
    }

    public static function userFullName(): ?string
    {
        return $_SESSION['user_fullname'] ?? null;
    }

    public static function userProjectId(): ?int
    {
        return $_SESSION['user_project_id'] ?? null;
    }

    // ══════════════════════════════════════════════════════════
    //  PHẦN 5: PHÂN QUYỀN RBAC & SESSION LOGIN
    // ══════════════════════════════════════════════════════════

    /**
     * Lưu thông tin đăng nhập và danh sách quyền hạn.
     */
    public static function loginUser(array $userData): void
    {
        session_regenerate_id(true);

        self::set('user_id',         (int) $userData['id']);
        self::set('username',        $userData['username']);
        self::set('user_fullname',   $userData['full_name'] ?? $userData['username']);
        self::set('user_role',       $userData['role_code'] ?? 'employee');
        
        // RBAC V2 Attributes
        self::set('role_id',         (int) ($userData['role_id'] ?? 0));
        self::set('role_level',      (int) ($userData['role_level'] ?? 3));
        self::set('can_delegate',    (bool) ($userData['can_delegate'] ?? false));
        self::set('permissions',     $userData['permissions'] ?? []);
        self::set('modules',         $userData['modules'] ?? []);

        self::set('user_email',      $userData['email'] ?? '');
        self::set('user_project_id', $userData['project_id'] ?? null);
        self::set('login_time',      time());
    }

    /**
     * Kiểm tra user có phải là Super Admin không (level 1)
     */
    public static function isSuperAdmin(): bool
    {
        return self::get('role_level', 3) === 0
            || self::get('user_role') === 'super_admin';
    }

    /**
     * Kiểm tra quyền truy cập (RBAC Code)
     */
    public static function hasPermission(string $permissionCode): bool
    {
        if (self::isSuperAdmin()) {
            return true;
        }
        $perms = self::get('permissions', []);
        return in_array($permissionCode, $perms, true);
    }

    /**
     * Kiểm tra người dùng có quyền vào module không
     */
    public static function hasModule(string $moduleCode): bool
    {
        if (self::isSuperAdmin()) {
            return true;
        }
        $modules = self::get('modules', []);
        return in_array($moduleCode, $modules, true);
    }

    // Tạm giữ hàm cũ để tương thích với các view chưa sửa
    public static function checkPermission(array $allowedRoles = []): bool
    {
        if (!self::isLoggedIn()) {
            self::setFlash('error', 'Vui lòng đăng nhập để tiếp tục.');
            header('Location: ' . BASE_URL . '/auth/login');
            exit;
        }

        if (empty($allowedRoles)) {
            return true;
        }

        if (self::isSuperAdmin() || self::userRole() === 'admin' || self::userRole() === 'super_admin') {
            return true;
        }

        if (!in_array(self::userRole(), $allowedRoles, true)) {
            self::setFlash('error', 'Bạn không có quyền truy cập chức năng này.');
            header('Location: ' . BASE_URL);
            exit;
        }

        return true;
    }

    public static function checkProjectAccess(int $projectId): bool
    {
        if (!self::isLoggedIn()) {
            self::setFlash('error', 'Vui lòng đăng nhập để tiếp tục.');
            header('Location: ' . BASE_URL . '/auth/login');
            exit;
        }

        if (self::isSuperAdmin() || in_array(self::userRole(), ['admin', 'super_admin', 'hr_manager', 'Admin', 'HR_Manager'])) {
            return true;
        }

        $userProjectId = self::userProjectId();

        if ($userProjectId === null) {
            self::setFlash('error', 'Bạn chưa được phân công vào dự án nào.');
            header('Location: ' . BASE_URL);
            exit;
        }

        if ((int) $userProjectId !== (int) $projectId) {
            self::setFlash('error', 'Bạn không có quyền truy cập dữ liệu dự án này.');
            header('Location: ' . BASE_URL);
            exit;
        }

        return true;
    }

    public static function isAdmin(): bool
    {
        return self::isSuperAdmin() || strtolower(self::userRole() ?? '') === 'admin' || self::userRole() === 'super_admin';
    }

    public static function isManager(): bool
    {
        return self::isAdmin() || in_array(strtolower(self::userRole() ?? ''), ['hr_manager', 'sub_admin']);
    }

    // ══════════════════════════════════════════════════════════
    //  PHẦN 7: BẢO MẬT CSRF
    // ══════════════════════════════════════════════════════════
    public static function generateCsrfToken(): string
    {
        if (!self::has('_csrf_token')) {
            self::set('_csrf_token', bin2hex(random_bytes(32)));
        }
        return self::get('_csrf_token');
    }

    public static function validateCsrfToken(string $token): bool
    {
        $sessionToken = self::get('_csrf_token');
        if (!$sessionToken || !$token) {
            return false;
        }
        return hash_equals($sessionToken, $token);
    }

    /**
     * Tạo hidden input chứa CSRF token để nhúng vào form.
     */
    public static function csrfField(): string
    {
        $token = self::generateCsrfToken();
        return '<input type="hidden" name="_csrf_token" value="' . htmlspecialchars($token) . '">';
    }
}
