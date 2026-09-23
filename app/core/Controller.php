<?php
/**
 * ============================================================
 *  POSUNG HRIS – Base Controller (Lớp điều khiển cơ sở)
 * ============================================================
 *  Tất cả Controller cụ thể (AuthController, EmployeeController, ...)
 *  đều kế thừa (extends) lớp này để sử dụng các hàm tiện ích:
 *
 *  - model($modelName)    : Nạp và khởi tạo Model
 *  - view($viewPath, $data): Nạp View với dữ liệu
 *  - redirect($url)       : Chuyển hướng an toàn
 * ============================================================
 */

class Controller
{
    // ══════════════════════════════════════════════════════════
    //  NẠP MODEL
    // ══════════════════════════════════════════════════════════

    /**
     * Nạp file Model và trả về instance (đối tượng) của Model đó.
     *
     * Cơ chế:
     *   1. Nhận tên Model (VD: 'User', 'Employee')
     *   2. Tìm file tương ứng trong app/models/
     *   3. require_once file đó
     *   4. Tạo và trả về đối tượng mới
     *
     * @param  string $modelName Tên Model (không cần đuôi .php)
     * @return object Instance của Model
     *
     * Ví dụ:
     *   $userModel = $this->model('User');
     *   $result = $userModel->authenticate('admin', '123456');
     */
    protected function model(string $modelName): object
    {
        // Đường dẫn tới file Model
        $modelFile = APP_ROOT . '/models/' . $modelName . '.php';

        // Kiểm tra file tồn tại
        if (!file_exists($modelFile)) {
            die("<h3>Lỗi hệ thống</h3><p>Không tìm thấy Model: <strong>{$modelName}</strong></p>"
              . "<p>Đường dẫn: {$modelFile}</p>");
        }

        // Nạp file Model (chỉ nạp 1 lần dù gọi nhiều lần)
        require_once $modelFile;

        // Tạo và trả về đối tượng Model
        return new $modelName();
    }

    // ══════════════════════════════════════════════════════════
    //  NẠP VIEW
    // ══════════════════════════════════════════════════════════

    /**
     * Nạp file View và truyền dữ liệu vào cho View sử dụng.
     *
     * Cơ chế:
     *   1. Nhận đường dẫn View (VD: 'employee/index' hoặc 'auth/login')
     *   2. Chuyển đổi dấu chấm/gạch chéo thành đường dẫn file
     *   3. Dùng extract($data) để chuyển mảng thành các biến riêng lẻ
     *   4. require file View
     *
     * @param  string $viewPath Đường dẫn view (VD: 'auth/login', 'employee/index')
     * @param  array  $data     Mảng dữ liệu cần truyền vào view
     *                          Mỗi key sẽ thành tên biến trong view
     * @return void
     *
     * Ví dụ:
     *   $this->view('employee/index', [
     *       'employees' => $listNV,     // Trong view dùng: $employees
     *       'pageTitle'  => 'Danh sách',  // Trong view dùng: $pageTitle
     *   ]);
     */
    protected function view(string $viewPath, array $data = []): void
    {
        // Chuyển đổi dấu chấm thành dấu / (hỗ trợ cả 2 cách viết)
        $viewPath = str_replace('.', '/', $viewPath);

        // Xây dựng đường dẫn đầy đủ tới file View
        $viewFile = APP_ROOT . '/views/' . $viewPath . '.php';

        // Kiểm tra file View tồn tại
        if (!file_exists($viewFile)) {
            die("<h3>Lỗi hệ thống</h3><p>Không tìm thấy View: <strong>{$viewPath}</strong></p>"
              . "<p>Đường dẫn: {$viewFile}</p>");
        }

        // Trích xuất mảng $data thành các biến riêng lẻ
        // VD: ['pageTitle' => 'Xin chào'] → biến $pageTitle = 'Xin chào'
        extract($data);

        // Nạp file View (các biến vừa extract sẽ có sẵn trong file)
        require $viewFile;
    }

    // ══════════════════════════════════════════════════════════
    //  CHUYỂN HƯỚNG
    // ══════════════════════════════════════════════════════════

    /**
     * Chuyển hướng trình duyệt đến một URL khác.
     *
     * @param string $url Đường dẫn tương đối (VD: 'auth/login', 'employee/show/5')
     *                    Tự động ghép với BASE_URL
     * @return void
     *
     * Ví dụ:
     *   $this->redirect('auth/login');         // → http://localhost/posung_hris/public/auth/login
     *   $this->redirect('employee/show/5');    // → http://localhost/posung_hris/public/employee/show/5
     *   $this->redirect('');                   // → Về trang chủ
     */
    protected function redirect(string $url): void
    {
        header('Location: ' . BASE_URL . '/' . ltrim($url, '/'));
        exit;  // QUAN TRỌNG: Phải exit sau header Location
    }

    // ══════════════════════════════════════════════════════════
    //  PHÂN QUYỀN (RBAC)
    // ══════════════════════════════════════════════════════════

    /**
     * Kiểm tra quyền hạt nhân
     */
    public function hasPermission(string $module, string $action): bool
    {
        if (!Session::isLoggedIn()) return false;
        return Session::hasPermission("{$module}.{$action}");
    }

    /**
     * Chặn truy cập nếu không có quyền
     */
    protected function requirePermission(string $module, string $action): void
    {
        if (!$this->hasPermission($module, $action)) {
            Session::setFlash('error', 'Bạn không có quyền thực hiện thao tác này!');
            $this->redirect('dashboard');
        }
    }

    /**
     * Cầu nối tương thích: kiểm tra quyền theo mã permission code ('module.action').
     * Super Admin luôn được phép.
     */
    protected function checkPermission(string $permissionCode): bool
    {
        if (!Session::isLoggedIn()) {
            Session::setFlash('error', 'Vui lòng đăng nhập để tiếp tục.');
            $this->redirect('auth/login');
        }

        if (Session::isSuperAdmin()) {
            return true;
        }

        if (!Session::hasPermission($permissionCode)) {
            Session::setFlash('error', 'Bạn không có quyền truy cập chức năng này.');
            $this->redirect('dashboard');
        }

        return true;
    }

    /**
     * Tiện ích lấy tất cả dữ liệu dạng JSON (Cho API)
     */
    protected function getJsonInput(): array
    {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }

    /**
     * Lớp Xác thực Dữ liệu (Validation Layer)
     * @param array $rules Mảng luật, vd: ['email' => 'required|email', 'phone' => 'required|phone_vn']
     * @param array $data Dữ liệu cần validate (mặc định lấy từ $_POST)
     * @return array Trả về mảng lỗi (nếu rỗng là hợp lệ)
     */
    protected function validate(array $rules, array $data = null): array
    {
        $data = $data ?? $_POST;
        $errors = [];

        foreach ($rules as $field => $ruleString) {
            $ruleArray = explode('|', $ruleString);
            $value = trim($data[$field] ?? '');

            foreach ($ruleArray as $rule) {
                // Tách tham số nếu có (vd: min:8)
                $ruleParts = explode(':', $rule);
                $ruleName = $ruleParts[0];
                $ruleParam = $ruleParts[1] ?? null;

                if ($ruleName === 'required' && $value === '') {
                    $errors[$field] = "Trường này là bắt buộc.";
                } elseif ($value !== '') { // Chỉ validate định dạng nếu có giá trị
                    switch ($ruleName) {
                        case 'email':
                            if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                                $errors[$field] = "Email không đúng định dạng.";
                            }
                            break;
                        case 'numeric':
                            if (!is_numeric($value)) {
                                $errors[$field] = "Phải là kiểu số.";
                            }
                            break;
                        case 'min':
                            if (strlen($value) < (int)$ruleParam) {
                                $errors[$field] = "Tối thiểu {$ruleParam} ký tự.";
                            }
                            break;
                        case 'max':
                            if (strlen($value) > (int)$ruleParam) {
                                $errors[$field] = "Tối đa {$ruleParam} ký tự.";
                            }
                            break;
                        case 'phone_vn':
                            if (!preg_match('/^(0|84)(3|5|7|8|9)[0-9]{8}$/', $value)) {
                                $errors[$field] = "Số điện thoại Việt Nam không hợp lệ.";
                            }
                            break;
                        case 'id_card_vn':
                            if (!preg_match('/^([0-9]{9}|[0-9]{12})$/', $value)) {
                                $errors[$field] = "Số CMND/CCCD phải là 9 hoặc 12 số.";
                            }
                            break;
                    }
                }

                // Nếu field này đã có lỗi thì không xét rule tiếp theo cho field đó nữa
                if (isset($errors[$field])) {
                    break;
                }
            }
        }

        return $errors;
    }

    // ══════════════════════════════════════════════════════════
    //  HÀM TIỆN ÍCH BỔ SUNG
    // ══════════════════════════════════════════════════════════

    /**
     * Kiểm tra phương thức request có phải POST không.
     * Dùng để phân biệt lúc hiển thị form (GET) và lúc xử lý form (POST).
     * Mặc định tự động kiểm tra CSRF Token để chống tấn công giả mạo.
     *
     * @param bool $checkCsrf Bật/tắt kiểm tra CSRF (mặc định: true)
     * @return bool true nếu là POST request hợp lệ
     *
     * Ví dụ:
     *   if ($this->isPost()) {
     *       // Xử lý dữ liệu form (CSRF đã được kiểm tra an toàn)
     *   } else {
     *       // Hiển thị form
     *   }
     */
    protected function isPost(bool $checkCsrf = true): bool
    {
        $isPost = $_SERVER['REQUEST_METHOD'] === 'POST';
        
        if ($isPost && $checkCsrf) {
            $token = $_POST['_csrf_token'] ?? '';
            if (!Session::validateCsrfToken($token)) {
                // Có thể ghi log ở đây trong thực tế
                die("<h3>Lỗi Bảo Mật (403 Forbidden)</h3><p>Yêu cầu không hợp lệ hoặc phiên làm việc đã hết hạn (CSRF Token mismatch). Vui lòng <a href='javascript:history.back()'>quay lại</a> và tải lại trang để thử lại.</p>");
            }
        }
        
        return $isPost;
    }

    /**
     * Lấy dữ liệu POST đã được làm sạch (trim khoảng trắng).
     *
     * @param  string $key     Tên field trong form
     * @param  mixed  $default Giá trị mặc định nếu field không tồn tại
     * @return mixed
     *
     * Ví dụ:
     *   $username = $this->postData('username');
     *   $status   = $this->postData('status', 'Probation');
     */
    protected function postData(string $key, mixed $default = null): mixed
    {
        if (isset($_POST[$key])) {
            if (is_string($_POST[$key])) {
                return trim($_POST[$key]);
            }
            return $_POST[$key];
        }
        return $default;
    }

    /**
     * Lấy dữ liệu GET đã được làm sạch.
     *
     * @param  string $key     Tên tham số URL
     * @param  mixed  $default Giá trị mặc định
     * @return mixed
     */
    protected function getData(string $key, mixed $default = null): mixed
    {
        if (isset($_GET[$key])) {
            if (is_string($_GET[$key])) {
                return trim($_GET[$key]);
            }
            return $_GET[$key];
        }
        return $default;
    }

    /**
     * Trả về dữ liệu dạng JSON (dùng cho API/AJAX).
     *
     * @param mixed $data       Dữ liệu cần trả về
     * @param int   $statusCode Mã HTTP (200 = OK, 400 = Bad Request, ...)
     * @return void
     *
     * Ví dụ:
     *   $this->json(['status' => 'success', 'data' => $employees]);
     *   $this->json(['error' => 'Không tìm thấy'], 404);
     */
    protected function json(mixed $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    // (checkPermission đã được khai báo ở trên, xem dòng 159)

    /**
     * Kiểm tra phân hệ (Module)
     */
    protected function requireModule(string $moduleCode): bool
    {
        if (Session::isSuperAdmin()) {
            return true;
        }

        if (Session::hasModule($moduleCode)) {
            return true;
        }

        $this->abort403();
        return false;
    }

    /**
     * Render trang lỗi 403
     */
    protected function abort403(): void
    {
        http_response_code(403);
        $this->view('errors/403');
        exit;
    }
}

