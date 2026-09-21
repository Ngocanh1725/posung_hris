<?php
/**
 * ============================================================
 *  POSUNG HRIS – Quản lý Session & Phân quyền RBAC
 * ============================================================
 *  Quản lý phiên làm việc (session) của người dùng, bao gồm:
 *  - Khởi tạo session an toàn
 *  - Lưu / đọc / xoá dữ liệu session
 *  - Tin nhắn flash (hiển thị 1 lần rồi tự xoá)
 *  - Kiểm tra đăng nhập, vai trò (RBAC)
 *  - Kiểm tra quyền truy cập dự án (Project-based ACL)
 *
 *  Ma trận phân quyền RBAC của Po Sung:
 *  ┌─────────────────────┬───────┬──────────┬─────────┬──────────┬──────────┐
 *  │ Chức năng           │ Admin │ HR_Mgr   │ PM      │ Site_Sup │ Employee │
 *  ├─────────────────────┼───────┼──────────┼─────────┼──────────┼──────────┤
 *  │ Quản lý tài khoản   │  ✓    │          │         │          │          │
 *  │ CRUD Nhân sự        │  ✓    │    ✓     │         │          │          │
 *  │ Xem DS Nhân viên    │  ✓    │    ✓     │   ✓     │    ✓     │          │
 *  │ Quản lý Dự án       │  ✓    │          │   ✓     │          │          │
 *  │ Chấm công           │  ✓    │    ✓     │   ✓     │    ✓     │          │
 *  │ Tính lương           │  ✓    │    ✓     │         │          │          │
 *  │ Xem hồ sơ cá nhân  │  ✓    │    ✓     │   ✓     │    ✓     │    ✓     │
 *  │ Báo cáo tổng hợp   │  ✓    │    ✓     │   ✓     │          │          │
 *  │ Quản lý Expat/HSE   │  ✓    │    ✓     │         │          │          │
 *  └─────────────────────┴───────┴──────────┴─────────┴──────────┴──────────┘
 * ============================================================
 */

class Session
{
    // ══════════════════════════════════════════════════════════
    //  PHẦN 1: KHỞI TẠO SESSION AN TOÀN
    // ══════════════════════════════════════════════════════════

    /**
     * Khởi tạo session với các thiết lập bảo mật.
     * Phải được gọi trước khi sử dụng bất kỳ hàm session nào.
     *
     * Các biện pháp bảo mật:
     * - httponly = true : JavaScript không truy cập được cookie session
     * - samesite = Lax  : Chống tấn công CSRF cơ bản
     * - Tên session tuỳ chỉnh thay vì PHPSESSID mặc định
     *
     * @return void
     */
    public static function start(): void
    {
        // Chỉ khởi tạo nếu chưa có session nào đang chạy
        if (session_status() === PHP_SESSION_NONE) {

            // Đặt tên session riêng cho ứng dụng (tránh xung đột với ứng dụng khác trên XAMPP)
            session_name(SESSION_NAME);

            // Cấu hình cookie session an toàn
            session_set_cookie_params([
                'lifetime' => SESSION_LIFETIME,  // Thời gian sống: 7200 giây (2 giờ)
                'path'     => '/',               // Áp dụng cho toàn bộ domain
                'domain'   => '',                // Tự động lấy domain hiện tại
                'secure'   => false,             // true khi dùng HTTPS (production)
                'httponly'  => true,              // Chặn JavaScript đọc cookie
                'samesite'  => 'Lax',            // Chống CSRF
            ]);

            session_start();

            // Tái tạo session ID định kỳ để chống tấn công Session Fixation
            // Mỗi 30 phút tạo ID mới
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

    /**
     * Lưu một giá trị vào session.
     *
     * @param string $key   Tên khoá
     * @param mixed  $val   Giá trị cần lưu
     * @return void
     *
     * Ví dụ: Session::set('user_id', 1);
     */
    public static function set(string $key, mixed $val): void
    {
        $_SESSION[$key] = $val;
    }

    /**
     * Đọc một giá trị từ session.
     *
     * @param  string $key     Tên khoá
     * @param  mixed  $default Giá trị mặc định nếu khoá không tồn tại
     * @return mixed
     *
     * Ví dụ: $userId = Session::get('user_id');
     *        $name   = Session::get('nickname', 'Khách');
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Kiểm tra một khoá có tồn tại trong session không.
     *
     * @param  string $key Tên khoá
     * @return bool        true nếu tồn tại, false nếu không
     *
     * Ví dụ: if (Session::has('user_id')) { ... }
     */
    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    /**
     * Xoá một khoá khỏi session.
     *
     * @param string $key Tên khoá cần xoá
     * @return void
     */
    public static function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    /**
     * Huỷ toàn bộ session (dùng khi đăng xuất).
     * - Xoá tất cả dữ liệu session
     * - Xoá cookie session trên trình duyệt
     * - Huỷ phiên trên server
     *
     * @return void
     */
    public static function destroy(): void
    {
        // Bước 1: Xoá tất cả biến session
        $_SESSION = [];

        // Bước 2: Xoá cookie session trên trình duyệt
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),       // Tên cookie
                '',                   // Giá trị rỗng
                time() - 42000,       // Hết hạn trong quá khứ → trình duyệt tự xoá
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        // Bước 3: Huỷ session trên server
        session_destroy();
    }

    // ══════════════════════════════════════════════════════════
    //  PHẦN 3: TIN NHẮN FLASH (Hiển thị 1 lần rồi tự xoá)
    // ══════════════════════════════════════════════════════════

    /**
     * Lưu một tin nhắn flash vào session.
     * Tin nhắn flash chỉ hiển thị 1 lần trên trang tiếp theo,
     * sau đó tự động bị xoá.
     *
     * @param string $key Loại tin nhắn: 'success', 'error', 'warning', 'info'
     * @param string $msg Nội dung tin nhắn
     * @return void
     *
     * Ví dụ: Session::setFlash('success', 'Thêm nhân viên thành công!');
     *        Session::setFlash('error', 'Sai tên đăng nhập hoặc mật khẩu.');
     */
    public static function setFlash(string $key, string $msg): void
    {
        $_SESSION['_flash'][$key] = $msg;
    }

    /**
     * Đọc và XOÁ tin nhắn flash (chỉ lấy được 1 lần).
     *
     * @param  string $key Loại tin nhắn
     * @return string|null Nội dung tin nhắn, hoặc null nếu không có
     *
     * Ví dụ: $msg = Session::getFlash('success');
     *        if ($msg) echo "<div class='alert-success'>{$msg}</div>";
     */
    public static function getFlash(string $key): ?string
    {
        $msg = $_SESSION['_flash'][$key] ?? null;
        unset($_SESSION['_flash'][$key]);
        return $msg;
    }

    /**
     * Kiểm tra có tin nhắn flash hay không (KHÔNG xoá).
     *
     * @param  string $key Loại tin nhắn
     * @return bool
     */
    public static function hasFlash(string $key): bool
    {
        return isset($_SESSION['_flash'][$key]);
    }

    // ══════════════════════════════════════════════════════════
    //  PHẦN 4: KIỂM TRA XÁC THỰC (Authentication)
    // ══════════════════════════════════════════════════════════

    /**
     * Kiểm tra người dùng đã đăng nhập chưa.
     *
     * @return bool true nếu đã đăng nhập
     */
    public static function isLoggedIn(): bool
    {
        return isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0;
    }

    /**
     * Lấy ID người dùng đang đăng nhập.
     *
     * @return int|null ID hoặc null nếu chưa đăng nhập
     */
    public static function userId(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }

    /**
     * Lấy vai trò (role) người dùng đang đăng nhập.
     *
     * @return string|null Vai trò: Admin, HR_Manager, Project_Manager, Site_Supervisor, Employee
     */
    public static function userRole(): ?string
    {
        return $_SESSION['user_role'] ?? null;
    }

    /**
     * Lấy tên đầy đủ người dùng đang đăng nhập.
     *
     * @return string|null
     */
    public static function userFullName(): ?string
    {
        return $_SESSION['user_fullname'] ?? null;
    }

    /**
     * Lấy ID dự án mà người dùng đang được phân công (nếu có).
     *
     * @return int|null
     */
    public static function userProjectId(): ?int
    {
        return $_SESSION['user_project_id'] ?? null;
    }

    // ══════════════════════════════════════════════════════════
    //  PHẦN 5: PHÂN QUYỀN RBAC (Role-Based Access Control)
    // ══════════════════════════════════════════════════════════

    /**
     * Kiểm tra người dùng hiện tại có thuộc danh sách vai trò
     * được phép truy cập hay không.
     *
     * Nếu KHÔNG có quyền → chuyển hướng về trang Dashboard với thông báo lỗi.
     * Nếu CHƯA đăng nhập → chuyển hướng về trang đăng nhập.
     *
     * @param array $allowedRoles Danh sách vai trò được phép
     *                            VD: ['Admin', 'HR_Manager']
     * @return bool true nếu có quyền
     *
     * Ví dụ sử dụng trong Controller:
     *   Session::checkPermission(['Admin', 'HR_Manager']);
     *   // Chỉ Admin và HR_Manager mới được tiếp tục
     *   // Các vai trò khác bị chặn tự động
     */
    public static function checkPermission(array $allowedRoles = []): bool
    {
        // Bước 1: Kiểm tra đã đăng nhập chưa
        if (!self::isLoggedIn()) {
            self::setFlash('error', 'Vui lòng đăng nhập để tiếp tục.');
            header('Location: ' . BASE_URL . '/auth/login');
            exit;
        }

        // Bước 2: Nếu không chỉ định vai trò cụ thể → cho phép tất cả user đã đăng nhập
        if (empty($allowedRoles)) {
            return true;
        }

        // Bước 3: Admin luôn có mọi quyền (bỏ qua kiểm tra)
        if (self::userRole() === 'Admin') {
            return true;
        }

        // Bước 4: Kiểm tra vai trò người dùng có nằm trong danh sách cho phép không
        if (!in_array(self::userRole(), $allowedRoles, true)) {
            self::setFlash('error', 'Bạn không có quyền truy cập chức năng này.');
            header('Location: ' . BASE_URL);
            exit;
        }

        return true;
    }

    /**
     * Kiểm tra quyền truy cập DỰ ÁN cụ thể.
     *
     * Quy tắc nghiệp vụ Po Sung:
     * ─────────────────────────────────────────────────────────
     * - Admin, HR_Manager: Truy cập TẤT CẢ dự án (toàn công ty).
     * - Project_Manager, Site_Supervisor: Chỉ truy cập dữ liệu
     *   của DỰ ÁN mình được phân công.
     *   → Nếu KS thuộc Dự án Amkor → KHÔNG được xem dữ liệu Samsung SEHC.
     * - Employee: Chỉ xem hồ sơ cá nhân của mình.
     * ─────────────────────────────────────────────────────────
     *
     * @param int $projectId ID dự án cần kiểm tra
     * @return bool true nếu có quyền truy cập dự án đó
     *
     * Ví dụ sử dụng:
     *   if (!Session::checkProjectAccess($projectId)) {
     *       // Redirect hoặc hiển thị lỗi 403
     *   }
     */
    public static function checkProjectAccess(int $projectId): bool
    {
        // Bước 1: Kiểm tra đăng nhập
        if (!self::isLoggedIn()) {
            self::setFlash('error', 'Vui lòng đăng nhập để tiếp tục.');
            header('Location: ' . BASE_URL . '/auth/login');
            exit;
        }

        // Bước 2: Admin và HR_Manager → truy cập toàn bộ dự án
        $globalRoles = ['Admin', 'HR_Manager'];
        if (in_array(self::userRole(), $globalRoles, true)) {
            return true;
        }

        // Bước 3: Project_Manager và Site_Supervisor → chỉ dự án được phân công
        $userProjectId = self::userProjectId();

        if ($userProjectId === null) {
            // Người dùng chưa được gán dự án → không cho xem bất kỳ dự án nào
            self::setFlash('error', 'Bạn chưa được phân công vào dự án nào.');
            header('Location: ' . BASE_URL);
            exit;
        }

        if ((int) $userProjectId !== (int) $projectId) {
            // Dự án yêu cầu KHÁC với dự án được phân công
            // VD: KS Amkor cố truy cập dữ liệu Samsung SEHC → bị chặn
            self::setFlash('error', 'Bạn không có quyền truy cập dữ liệu dự án này.');
            header('Location: ' . BASE_URL);
            exit;
        }

        // Bước 4: Dự án trùng khớp → cho phép
        return true;
    }

    // ══════════════════════════════════════════════════════════
    //  PHẦN 6: TIỆN ÍCH BỔ SUNG
    // ══════════════════════════════════════════════════════════

    /**
     * Lưu thông tin đăng nhập vào session sau khi xác thực thành công.
     *
     * @param array $userData Dữ liệu người dùng từ bảng users
     *                        Phải có: id, username, full_name, role
     *                        Tuỳ chọn: project_id (cho PM/Supervisor)
     * @return void
     */
    public static function loginUser(array $userData): void
    {
        // Tái tạo session ID để chống tấn công Session Fixation
        session_regenerate_id(true);

        self::set('user_id',         (int) $userData['id']);
        self::set('username',        $userData['username']);
        self::set('user_fullname',   $userData['full_name']);
        self::set('user_role',       $userData['role']);
        self::set('user_email',      $userData['email'] ?? '');
        self::set('user_project_id', $userData['project_id'] ?? null);
        self::set('login_time',      time());
    }

    /**
     * Kiểm tra người dùng có phải Admin không.
     *
     * @return bool
     */
    public static function isAdmin(): bool
    {
        return self::userRole() === 'Admin';
    }

    /**
     * Kiểm tra người dùng có thuộc một trong các vai trò quản lý
     * (Admin hoặc HR_Manager) không.
     *
     * @return bool
     */
    public static function isManager(): bool
    {
        return in_array(self::userRole(), ['Admin', 'HR_Manager'], true);
    }

    // ══════════════════════════════════════════════════════════
    //  PHẦN 7: BẢO MẬT CSRF (Cross-Site Request Forgery)
    // ══════════════════════════════════════════════════════════

    /**
     * Tạo CSRF Token ngẫu nhiên và lưu vào session.
     * Thường dùng để nhúng vào thẻ input hidden trong các form POST.
     *
     * @return string Token chuỗi ngẫu nhiên (32 byte hex)
     */
    public static function generateCsrfToken(): string
    {
        if (!self::has('_csrf_token')) {
            self::set('_csrf_token', bin2hex(random_bytes(32)));
        }
        return self::get('_csrf_token');
    }

    /**
     * Kiểm tra CSRF Token từ client gửi lên có khớp với session không.
     *
     * @param string $token Token từ POST request
     * @return bool true nếu hợp lệ
     */
    public static function validateCsrfToken(string $token): bool
    {
        $sessionToken = self::get('_csrf_token');
        if (!$sessionToken || !$token) {
            return false;
        }
        return hash_equals($sessionToken, $token);
    }
}
