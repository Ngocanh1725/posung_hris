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
    //  HÀM TIỆN ÍCH BỔ SUNG
    // ══════════════════════════════════════════════════════════

    /**
     * Kiểm tra phương thức request có phải POST không.
     * Dùng để phân biệt lúc hiển thị form (GET) và lúc xử lý form (POST).
     *
     * @return bool true nếu là POST request
     *
     * Ví dụ:
     *   if ($this->isPost()) {
     *       // Xử lý dữ liệu form
     *   } else {
     *       // Hiển thị form
     *   }
     */
    protected function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
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
}
