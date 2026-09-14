<?php
/**
 * ============================================================
 *  POSUNG HRIS – Controller Xác thực (Authentication)
 * ============================================================
 *  Xử lý đăng nhập / đăng xuất hệ thống.
 *
 *  Routes:
 *    GET  /auth/login   → Hiển thị form đăng nhập
 *    POST /auth/login   → Xử lý xác thực tài khoản
 *    GET  /auth/logout  → Đăng xuất và huỷ session
 * ============================================================
 */

class AuthController extends Controller
{
    // ══════════════════════════════════════════════════════════
    //  ĐĂNG NHẬP
    // ══════════════════════════════════════════════════════════

    /**
     * Hiển thị form đăng nhập (GET) hoặc xử lý đăng nhập (POST).
     *
     * Luồng xử lý:
     *   1. Nếu đã đăng nhập → chuyển về Dashboard
     *   2. Nếu là GET request → hiển thị form đăng nhập
     *   3. Nếu là POST request:
     *      a. Lấy username & password từ form
     *      b. Gọi User::authenticate() để kiểm tra
     *      c. Đúng → lưu session + chuyển hướng Dashboard
     *      d. Sai  → hiển thị lại form với thông báo lỗi
     *
     * @return void
     */
    public function login(): void
    {
        // Nếu đã đăng nhập rồi → về trang chủ
        if (Session::isLoggedIn()) {
            $this->redirect('');
            return;
        }

        // Biến lưu lỗi để truyền vào View
        $error    = '';
        $username = '';

        // Xử lý khi submit form (POST)
        if ($this->isPost()) {

            // Lấy dữ liệu từ form, đã được trim tự động
            $username = $this->postData('username', '');
            $password = $this->postData('password', '');

            // ── Validate đầu vào ────────────────────────────
            if (empty($username) || empty($password)) {
                $error = 'Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu.';
            } else {
                // ── Gọi Model để xác thực ───────────────────
                $userModel = $this->model('User');
                $userData  = $userModel->authenticate($username, $password);

                if ($userData !== false) {
                    // ✅ XÁC THỰC THÀNH CÔNG

                    // Lưu thông tin user vào Session
                    Session::loginUser($userData);

                    // Cập nhật thời gian đăng nhập gần nhất
                    $userModel->updateLastLogin((int) $userData['id']);

                    // Ghi nhận thông báo chào mừng
                    Session::setFlash('success',
                        'Xin chào, ' . htmlspecialchars($userData['full_name']) . '! '
                        . 'Đăng nhập thành công.'
                    );

                    // Chuyển hướng về Dashboard
                    $this->redirect('');
                    return;

                } else {
                    // ❌ XÁC THỰC THẤT BẠI
                    $error = 'Sai tên đăng nhập hoặc mật khẩu. Vui lòng thử lại.';
                }
            }
        }

        // Hiển thị trang đăng nhập (không dùng layout header/footer)
        $this->view('auth/login', [
            'error'    => $error,
            'username' => $username,  // Giữ lại tên đăng nhập đã nhập
        ]);
    }

    // ══════════════════════════════════════════════════════════
    //  ĐĂNG XUẤT
    // ══════════════════════════════════════════════════════════

    /**
     * Đăng xuất: Huỷ toàn bộ session và chuyển về trang login.
     *
     * @return void
     */
    public function logout(): void
    {
        // Huỷ session (xoá dữ liệu + cookie)
        Session::destroy();

        // Chuyển hướng về trang đăng nhập
        header('Location: ' . BASE_URL . '/auth/login');
        exit;
    }
}
