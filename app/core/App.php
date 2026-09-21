<?php
/**
 * ============================================================
 *  POSUNG HRIS – Front Controller / Router (Bộ điều phối URL)
 * ============================================================
 *  Mọi request HTTP đều được .htaccess chuyển tới public/index.php,
 *  sau đó lớp App sẽ:
 *
 *  1. Phân tích chuỗi URL từ $_GET['url']
 *  2. Xác định Controller, Action (method) và các tham số (params)
 *  3. Kiểm tra trạng thái đăng nhập:
 *     - Chưa đăng nhập → AuthController@login
 *     - Đã đăng nhập   → Controller được yêu cầu (mặc định: DashboardController@index)
 *  4. Gọi Controller@Action(params)
 *
 *  Mẫu URL:
 *    /employee/show/5     → EmployeeController@show(5)
 *    /auth/login          → AuthController@login()
 *    /payroll/calculate   → PayrollController@calculate()
 *    / (trống)            → DashboardController@index()
 * ============================================================
 */

class App
{
    // ── Giá trị mặc định ────────────────────────────────────

    /** @var string Tên Controller mặc định khi đã đăng nhập */
    protected string $defaultController = 'DashboardController';

    /** @var string Tên Controller xử lý đăng nhập */
    protected string $authController = 'AuthController';

    /** @var string Tên Action (phương thức) mặc định */
    protected string $defaultAction = 'index';

    // ── Các giá trị sẽ được xác định từ URL ─────────────────

    /** @var string Tên Controller được chọn */
    protected string $currentController;

    /** @var string Tên Action được chọn */
    protected string $currentAction;

    /** @var array Các tham số truyền vào Action */
    protected array $params = [];

    /**
     * Danh sách các route KHÔNG YÊU CẦU đăng nhập.
     * Các URL này được phép truy cập khi chưa login.
     */
    protected array $publicRoutes = [
        'auth/login',
        'auth/logout',
        'timesheet/sync' // API Endpoint không cần session cookie (có thể xác thực qua API Key ở Controller nếu cần)
    ];

    // ══════════════════════════════════════════════════════════
    //  CONSTRUCTOR – Khởi chạy khi ứng dụng boot
    // ══════════════════════════════════════════════════════════

    public function __construct()
    {
        // Bước 1: Phân tích URL thành các phần (segments)
        $url = $this->parseUrl();

        // Xử lý tiền tố "api/"
        if (isset($url[0]) && strtolower($url[0]) === 'api') {
            unset($url[0]);
            $url = array_values($url); // Re-index array
        }

        $originalUrl = $url; // Giữ lại bản copy trước khi bị resolveController sửa đổi

        // Bước 2: Xác định Controller từ segment đầu tiên
        $this->currentController = $this->resolveController($url);

        // Bước 3: Kiểm tra quyền truy cập (đăng nhập)
        $this->checkAuthentication($originalUrl);

        // Bước 4: Nạp file Controller
        $controllerFile = APP_ROOT . '/controllers/' . $this->currentController . '.php';

        if (!file_exists($controllerFile)) {
            // Controller không tồn tại → quay về mặc định
            $this->currentController = $this->defaultController;
            $controllerFile = APP_ROOT . '/controllers/' . $this->currentController . '.php';
        }

        require_once $controllerFile;

        // Bước 5: Tạo đối tượng Controller
        $controllerInstance = new $this->currentController();

        // Bước 6: Xác định Action (method) từ segment thứ 2
        $this->currentAction = $this->resolveAction($controllerInstance, $url);

        // Bước 7: Các segment còn lại là tham số (params)
        $this->params = $url ? array_values($url) : [];

        // Bước 8: GỌI Controller → Action(params)
        // VD: EmployeeController->show(5)
        call_user_func_array(
            [$controllerInstance, $this->currentAction],
            $this->params
        );
    }

    // ══════════════════════════════════════════════════════════
    //  PHÂN TÍCH URL
    // ══════════════════════════════════════════════════════════

    /**
     * Phân tích chuỗi URL từ query string ?url=...
     * được tạo bởi .htaccess URL Rewrite.
     *
     * Ví dụ:
     *   URL gốc: /posung_hris/public/employee/show/5
     *   .htaccess chuyển thành: index.php?url=employee/show/5
     *   Hàm này trả về: ['employee', 'show', '5']
     *
     * @return array Mảng các segment của URL
     */
    protected function parseUrl(): array
    {
        if (isset($_GET['url']) && $_GET['url'] !== '') {
            // Xoá dấu / ở cuối, lọc ký tự đặc biệt, tách thành mảng
            $url = filter_var(
                rtrim($_GET['url'], '/'),
                FILTER_SANITIZE_URL
            );
            return explode('/', $url);
        }
        return [];
    }

    // ══════════════════════════════════════════════════════════
    //  XÁC ĐỊNH CONTROLLER
    // ══════════════════════════════════════════════════════════

    /**
     * Xác định Controller cần nạp dựa trên segment đầu tiên của URL.
     *
     * Quy tắc:
     *   - Segment 'employee' → EmployeeController
     *   - Segment 'auth'     → AuthController
     *   - Segment trống      → DashboardController (mặc định)
     *
     * @param  array &$url Mảng URL segments (sẽ bị thay đổi: xoá segment đã dùng)
     * @return string Tên Controller
     */
    protected function resolveController(array &$url): string
    {
        if (isset($url[0]) && $url[0] !== '') {
            // Chuyển chữ cái đầu thành HOA, thêm hậu tố Controller
            // VD: 'employee' → 'EmployeeController'
            $candidate = ucfirst(strtolower($url[0])) . 'Controller';
            $filePath  = APP_ROOT . '/controllers/' . $candidate . '.php';

            if (file_exists($filePath)) {
                // Tìm thấy Controller → dùng nó và xoá segment khỏi mảng
                unset($url[0]);
                return $candidate;
            }
        }

        // Không tìm thấy → trả về Controller mặc định
        return $this->defaultController;
    }

    // ══════════════════════════════════════════════════════════
    //  XÁC ĐỊNH ACTION (METHOD)
    // ══════════════════════════════════════════════════════════

    /**
     * Xác định Action (phương thức) cần gọi từ segment thứ 2.
     *
     * @param  object $controller Instance của Controller
     * @param  array  &$url       Mảng URL segments
     * @return string Tên Action
     */
    protected function resolveAction(object $controller, array &$url): string
    {
        if (isset($url[1]) && $url[1] !== '') {
            // Kiểm tra phương thức có tồn tại trong Controller không
            if (method_exists($controller, $url[1])) {
                $action = $url[1];
                unset($url[1]);
                return $action;
            }
        }

        // Không tìm thấy → dùng action mặc định ('index')
        return $this->defaultAction;
    }

    // ══════════════════════════════════════════════════════════
    //  KIỂM TRA XÁC THỰC
    // ══════════════════════════════════════════════════════════

    /**
     * Kiểm tra người dùng đã đăng nhập chưa.
     *
     * Logic:
     *   1. Nếu URL thuộc danh sách public (auth/login, auth/logout) → bỏ qua
     *   2. Nếu chưa đăng nhập → chuyển hướng về AuthController@login
     *   3. Nếu đã đăng nhập mà truy cập trang login → chuyển về Dashboard
     *
     * @param array $url Mảng URL segments
     * @return void
     */
    protected function checkAuthentication(array $url): void
    {
        // Xây dựng route hiện tại từ 2 segment đầu (VD: 'auth/login')
        $segment1 = $url[0] ?? '';
        $segment2 = $url[1] ?? '';
        $currentRoute = trim($segment1 . '/' . $segment2, '/');

        // Kiểm tra route có thuộc danh sách public không
        $isPublicRoute = false;
        foreach ($this->publicRoutes as $route) {
            if (stripos($currentRoute, $route) === 0) {
                $isPublicRoute = true;
                break;
            }
        }

        // Trường hợp 1: Chưa đăng nhập + Route không public → bắt login
        if (!Session::isLoggedIn() && !$isPublicRoute) {
            Session::setFlash('error', 'Vui lòng đăng nhập để truy cập hệ thống.');
            header('Location: ' . BASE_URL . '/auth/login');
            exit;
        }

        // Trường hợp 2: Đã đăng nhập + Đang vào trang login → về Dashboard
        if (Session::isLoggedIn() && $currentRoute === 'auth/login') {
            header('Location: ' . BASE_URL);
            exit;
        }
    }
}
