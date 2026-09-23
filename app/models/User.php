<?php
/**
 * ============================================================
 *  POSUNG HRIS – Model Người dùng (User)
 * ============================================================
 *  Quản lý xác thực (authentication) và phân quyền cho tài khoản
 *  đăng nhập hệ thống HRIS.
 *
 *  Bảng CSDL: users
 *  ┌─────────┬──────────────┬──────────────────────────┐
 *  │ Cột     │ Kiểu         │ Mô tả                    │
 *  ├─────────┼──────────────┼──────────────────────────┤
 *  │ id      │ INT UNSIGNED │ Khoá chính               │
 *  │ username│ VARCHAR(50)  │ Tên đăng nhập (UNIQUE)   │
 *  │ password│ VARCHAR(255) │ Mật khẩu mã hoá bcrypt   │
 *  │ full_name│VARCHAR(100) │ Họ tên đầy đủ            │
 *  │ email   │ VARCHAR(100) │ Email                    │
 *  │ role    │ ENUM(...)    │ Vai trò RBAC             │
 *  │ status  │ ENUM(...)    │ Active, Inactive, Locked │
 *  └─────────┴──────────────┴──────────────────────────┘
 * ============================================================
 */

class User
{
    /** @var Database Đối tượng kết nối CSDL */
    private Database $db;

    /**
     * Hàm khởi tạo – lấy instance Database.
     */
    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ══════════════════════════════════════════════════════════
    //  XÁC THỰC ĐĂNG NHẬP (Authentication)
    // ══════════════════════════════════════════════════════════

    /**
     * Xác thực tài khoản đăng nhập.
     *
     * Quy trình:
     *   1. Tìm user theo username (chỉ user có status = 'Active')
     *   2. So sánh mật khẩu nhập vào với hash bcrypt trong CSDL
     *   3. Trả về dữ liệu user nếu đúng, false nếu sai
     *
     * @param  string $username Tên đăng nhập
     * @param  string $password Mật khẩu dạng plaintext
     * @return array|false      Mảng thông tin user nếu xác thực thành công,
     *                          false nếu sai tài khoản/mật khẩu
     *
     * Ví dụ:
     *   $userModel = new User();
     *   $result = $userModel->authenticate('admin', '123456');
     *   if ($result) {
     *       Session::loginUser($result);
     *       // Chuyển hướng vào Dashboard
     *   } else {
     *       // Hiển thị lỗi "Sai tài khoản hoặc mật khẩu"
     *   }
     */
    public function authenticate(string $username, string $password): array|false
    {
        try {
            // Truy vấn user theo username, chỉ lấy user đang active
            $this->db->query(
                "SELECT u.id, u.username, u.password_hash, u.employee_id, u.role_id,
                        e.full_name, e.current_project_id AS project_id,
                        r.code AS role_code, r.is_system AS is_system, r.level AS role_level
                 FROM users u
                 LEFT JOIN employees e ON u.employee_id = e.id
                 LEFT JOIN roles r ON u.role_id = r.id
                 WHERE u.username = :username
                   AND u.status = 'active'
                 LIMIT 1",
                ['username' => $username]
            );

            $user = $this->db->fetch();

            if (!$user) {
                return false;
            }

            if (!password_verify($password, $user['password_hash'])) {
                return false;
            }

            unset($user['password_hash']);
            return $user;
        } catch (Exception $e) {
            // Ghi log lỗi nếu cần thiết
            error_log("Database error in User::authenticate: " . $e->getMessage());
            return false;
        }
    }

    // ══════════════════════════════════════════════════════════
    //  TÌM KIẾM USER
    // ══════════════════════════════════════════════════════════

    /**
     * Lấy thông tin user theo ID.
     *
     * @param  int $id ID của user
     * @return array|false Mảng thông tin user hoặc false nếu không tìm thấy
     *
     * Ví dụ:
     *   $user = $userModel->getUserById(1);
     *   echo $user['full_name']; // "Quản trị viên"
     */
    public function getUserById(int $id): array|false
    {
        $this->db->query(
            "SELECT id, username, full_name, email, role, status, created_at, updated_at
             FROM users
             WHERE id = :id
             LIMIT 1",
            ['id' => $id]
        );

        return $this->db->fetch();
    }

    /**
     * Lấy thông tin user theo username.
     *
     * @param  string $username Tên đăng nhập
     * @return array|false
     */
    public function getUserByUsername(string $username): array|false
    {
        $this->db->query(
            "SELECT id, username, full_name, email, role, status
             FROM users
             WHERE username = :username
             LIMIT 1",
            ['username' => $username]
        );

        return $this->db->fetch();
    }

    /**
     * Lấy danh sách tất cả user (dùng cho trang quản lý tài khoản).
     *
     * @return array Mảng 2 chiều chứa thông tin tất cả user
     */
    public function getAllUsers(): array
    {
        $this->db->query(
            "SELECT id, username, full_name, email, role, status, created_at
             FROM users
             ORDER BY id ASC"
        );

        return $this->db->fetchAll();
    }

    // ══════════════════════════════════════════════════════════
    //  KIỂM TRA VAI TRÒ (RBAC)
    // ══════════════════════════════════════════════════════════

    /**
     * Kiểm tra user hiện tại (trong session) có thuộc vai trò chỉ định không.
     *
     * @param  string $role Vai trò cần kiểm tra
     *                      VD: 'Admin', 'HR_Manager', 'Project_Manager'
     * @return bool   true nếu user có vai trò đó
     *
     * Ví dụ:
     *   $userModel = new User();
     *   if ($userModel->hasRole('Admin')) {
     *       // Hiển thị menu quản trị
     *   }
     */
    public function hasRole(string $role): bool
    {
        return Session::userRole() === $role;
    }

    /**
     * Kiểm tra user hiện tại có thuộc MỘT TRONG các vai trò cho trước không.
     *
     * @param  array $roles Mảng các vai trò
     * @return bool
     *
     * Ví dụ:
     *   if ($userModel->hasAnyRole(['Admin', 'HR_Manager'])) {
     *       // Cho phép thao tác
     *   }
     */
    public function hasAnyRole(array $roles): bool
    {
        return in_array(Session::userRole(), $roles, true);
    }

    // ══════════════════════════════════════════════════════════
    //  THAO TÁC DỮ LIỆU (CRUD)
    // ══════════════════════════════════════════════════════════

    /**
     * Tạo tài khoản user mới.
     * Mật khẩu tự động được mã hoá bằng bcrypt.
     *
     * @param  array $data Dữ liệu user: username, password, full_name, email, role
     * @return int   ID của user vừa tạo
     *
     * Ví dụ:
     *   $newId = $userModel->createUser([
     *       'username'  => 'ks_minh',
     *       'password'  => 'matkhau123',
     *       'full_name' => 'Nguyễn Minh',
     *       'email'     => 'minh@posung.vn',
     *       'role'      => 'Employee',
     *   ]);
     */
    public function createUser(array $data): int
    {
        // Mã hoá mật khẩu bằng bcrypt (cost mặc định = 10)
        $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);

        $this->db->query(
            "INSERT INTO users (username, password, full_name, email, role, status)
             VALUES (:username, :password, :full_name, :email, :role, 'Active')",
            [
                'username'  => $data['username'],
                'password'  => $hashedPassword,
                'full_name' => $data['full_name'],
                'email'     => $data['email'] ?? null,
                'role'      => $data['role'] ?? 'Employee',
            ]
        );

        return (int) $this->db->lastInsertId();
    }

    /**
     * Cập nhật thông tin user.
     *
     * @param  int   $id   ID user
     * @param  array $data Dữ liệu cần cập nhật
     * @return bool  true nếu thành công
     */
    public function updateUser(int $id, array $data): bool
    {
        $this->db->query(
            "UPDATE users
             SET full_name = :full_name,
                 email     = :email,
                 role      = :role,
                 status    = :status
             WHERE id = :id",
            [
                'id'        => $id,
                'full_name' => $data['full_name'],
                'email'     => $data['email'],
                'role'      => $data['role'],
                'status'    => $data['status'],
            ]
        );

        return $this->db->rowCount() > 0;
    }

    /**
     * Đổi mật khẩu user.
     *
     * @param  int    $id          ID user
     * @param  string $newPassword Mật khẩu mới (plaintext, sẽ tự mã hoá)
     * @return bool
     */
    public function changePassword(int $id, string $newPassword): bool
    {
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

        $this->db->query(
            "UPDATE users SET password = :password WHERE id = :id",
            ['id' => $id, 'password' => $hashedPassword]
        );

        return $this->db->rowCount() > 0;
    }

    /**
     * Cập nhật thời gian đăng nhập gần nhất (ghi vào updated_at).
     *
     * @param  int $id ID user
     * @return void
     */
    public function updateLastLogin(int $id): void
    {
        $this->db->query(
            "UPDATE users SET updated_at = NOW() WHERE id = :id",
            ['id' => $id]
        );
    }

    // ══════════════════════════════════════════════════════════
    //  QUẢN LÝ TÀI KHOẢN VÀ RBAC V2 (Mở rộng)
    // ══════════════════════════════════════════════════════════

    /**
     * Lấy danh sách các user do một admin (Sub Admin) quản lý.
     * Dựa vào trường parent_admin_id trong bảng users.
     */
    public function getSubUsersByAdmin(int $adminId): array
    {
        $this->db->query(
            "SELECT u.id, u.username, u.email, u.status, u.role_id, r.name as role_name
             FROM users u
             LEFT JOIN roles r ON u.role_id = r.id
             WHERE u.employee_id IS NOT NULL /* Or another logic since parent_admin_id doesn't exist */
             ORDER BY u.id ASC",
            ['aid' => $adminId]
        );
        return $this->db->fetchAll();
    }

    /**
     * Lấy thông tin Vai trò (Role) chi tiết của một user.
     */
    public function getUserRoleInfo(int $userId): array|false
    {
        $this->db->query(
            "SELECT u.role_id, r.code as role_code, r.name as role_name, r.is_system
             FROM users u
             LEFT JOIN roles r ON u.role_id = r.id
             WHERE u.id = :id
             LIMIT 1",
            ['id' => $userId]
        );
        return $this->db->fetch();
    }
}
