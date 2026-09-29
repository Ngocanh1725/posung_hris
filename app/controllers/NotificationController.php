<?php
/**
 * ============================================================
 *  POSUNG HRIS – Notification Controller
 * ============================================================
 *  Quản lý trung tâm thông báo (Notification Center):
 *  - API trả dữ liệu chuông thông báo (bell dropdown) & AJAX polling
 *  - Đánh dấu đã đọc / Đánh dấu tất cả đã đọc
 *  - Trang xem toàn bộ thông báo (lọc, phân trang)
 *  - Quản lý cấu hình quy tắc nhắc nhở tự động (Settings)
 * ============================================================
 */

require_once APP_ROOT . '/core/NotificationService.php';

class NotificationController extends Controller
{
    /** @var Database Đối tượng Database */
    protected Database $db;

    public function __construct()
    {
        // Yêu cầu bắt buộc đăng nhập cho tất cả các action
        if (!Session::isLoggedIn()) {
            // Nếu là request AJAX, trả JSON 401
            if ($this->isAjax()) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(401);
                echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
                exit;
            }
            Session::setFlash('error', 'Vui lòng đăng nhập để tiếp tục.');
            $this->redirect('auth/login');
            exit;
        }

        $this->db = Database::getInstance();
    }

    /**
     * Trang xem toàn bộ thông báo của người dùng
     */
    public function index(): void
    {
        $userId = (int)Session::userId();
        $typeFilter = $_GET['type'] ?? 'all';
        $statusFilter = $_GET['status'] ?? 'all';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 15;
        $offset = ($page - 1) * $limit;

        // Xây dựng điều kiện lọc
        $whereSql = "WHERE user_id = :uid";
        $params = ['uid' => $userId];

        if ($typeFilter !== 'all' && in_array($typeFilter, ['system', 'reminder', 'approval', 'alert'])) {
            $whereSql .= " AND type = :type";
            $params['type'] = $typeFilter;
        }

        if ($statusFilter === 'unread') {
            $whereSql .= " AND is_read = 0";
        } elseif ($statusFilter === 'read') {
            $whereSql .= " AND is_read = 1";
        }

        // Đếm tổng số
        $countSql = "SELECT COUNT(*) as total FROM notifications {$whereSql}";
        $this->db->query($countSql, $params);
        $totalItems = (int)($this->db->fetch()['total'] ?? 0);
        $totalPages = ceil($totalItems / $limit);

        // Lấy danh sách thông báo
        $listSql = "SELECT * FROM notifications 
                    {$whereSql} 
                    ORDER BY is_read ASC, created_at DESC 
                    LIMIT {$limit} OFFSET {$offset}";
        $this->db->query($listSql, $params);
        $notifications = $this->db->fetchAll();

        // Thống kê số lượng chưa đọc
        $unreadCount = NotificationService::getUnreadCount($userId);

        $this->view('notification/index', [
            'notifications' => $notifications,
            'unreadCount'   => $unreadCount,
            'typeFilter'    => $typeFilter,
            'statusFilter'  => $statusFilter,
            'currentPage'   => $page,
            'totalPages'    => $totalPages,
            'totalItems'    => $totalItems,
            'pageTitle'     => 'Trung tâm thông báo – POSUNG HRIS'
        ]);
    }

    /**
     * API JSON: Lấy số lượng chưa đọc và danh sách thông báo cho dropdown chuông
     */
    public function getUnread(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $userId = (int)Session::userId();

        $unreadCount = NotificationService::getUnreadCount($userId);
        $rawNotifs = NotificationService::getRecentNotifications($userId, 8);

        $formatted = [];
        foreach ($rawNotifs as $n) {
            $formatted[] = [
                'id'         => (int)$n['id'],
                'type'       => $n['type'],
                'title'      => $n['title'],
                'message'    => $n['message'],
                'link'       => !empty($n['link']) ? (BASE_URL . '/' . ltrim($n['link'], '/')) : null,
                'is_read'    => (int)$n['is_read'],
                'time_ago'   => $this->timeAgo($n['created_at']),
                'icon_class' => match($n['type']) {
                    'reminder' => 'fa-solid fa-clock text-warning',
                    'approval' => 'fa-solid fa-clipboard-check text-primary',
                    'alert'    => 'fa-solid fa-triangle-exclamation text-danger',
                    default    => 'fa-solid fa-circle-info text-info'
                },
                'bg_class'   => match($n['type']) {
                    'reminder' => 'bg-warning-subtle text-warning',
                    'approval' => 'bg-primary-subtle text-primary',
                    'alert'    => 'bg-danger-subtle text-danger',
                    default    => 'bg-info-subtle text-info'
                }
            ];
        }

        echo json_encode([
            'status'        => 'success',
            'unread_count'  => $unreadCount,
            'notifications' => $formatted
        ]);
        exit;
    }

    /**
     * Đánh dấu 1 thông báo là đã đọc
     */
    public function markAsRead(mixed $id): void
    {
        $userId = (int)Session::userId();
        $id = (int)$id;

        // Lấy link thông báo trước khi cập nhật
        $this->db->query("SELECT link FROM notifications WHERE id = :id AND user_id = :uid LIMIT 1", ['id' => $id, 'uid' => $userId]);
        $notif = $this->db->fetch();

        NotificationService::markAsRead($id, $userId);

        if ($this->isAjax() || (isset($_GET['format']) && $_GET['format'] === 'json')) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['status' => 'success']);
            exit;
        }

        // Nếu có link, chuyển hướng đến đích
        if (!empty($notif['link'])) {
            $this->redirect(ltrim($notif['link'], '/'));
            return;
        }

        $this->redirect('notification');
    }

    /**
     * Đánh dấu toàn bộ thông báo của người dùng là đã đọc
     */
    public function markAllRead(): void
    {
        $userId = (int)Session::userId();
        NotificationService::markAllAsRead($userId);

        if ($this->isAjax() || (isset($_GET['format']) && $_GET['format'] === 'json')) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['status' => 'success']);
            exit;
        }

        Session::setFlash('success', 'Đã đánh dấu tất cả thông báo là đã đọc.');
        $this->redirect('notification');
    }

    /**
     * Quản lý cấu hình quy tắc thông báo tự động (Notification Rules)
     * Chỉ dành cho Admin và HR Manager
     */
    public function settings(): void
    {
        // Phân quyền: Chỉ admin, super_admin, hr_manager được phép
        if (!Session::isAdmin() && !Session::isManager()) {
            Session::setFlash('error', 'Bạn không có quyền truy cập trang cấu hình thông báo.');
            $this->redirect('');
            return;
        }

        // Xử lý POST lưu cấu hình
        if ($this->isPost()) {
            $csrfToken = $this->postData('_csrf_token', '');
            if (!Session::validateCsrfToken($csrfToken)) {
                Session::setFlash('error', 'Lỗi bảo mật (CSRF). Vui lòng thử lại.');
                $this->redirect('notification/settings');
                return;
            }

            $action = $this->postData('action', 'update_rules');

            if ($action === 'trigger_now') {
                // Kích hoạt quét thông báo tức thì
                $results = NotificationService::runScheduledReminders();
                AuditLogger::log('execute', 'notification', Session::userId(), null, null, "Quản trị viên kích hoạt quét thông báo thủ công");
                Session::setFlash('success', "Đã quét và tạo thành công {$results['total_created']} thông báo tự động mới (HĐ: {$results['contract_reminders']}, HSE: {$results['hse_reminders']}, Visa: {$results['visa_reminders']}, Sinh nhật: {$results['birthdays']}, Thử việc: {$results['probation_reminders']}).");
                $this->redirect('notification/settings');
                return;
            }

            if ($action === 'update_rule') {
                $ruleId = (int)$this->postData('rule_id');
                $daysBefore = (int)$this->postData('days_before', 0);
                $isActive = $this->postData('is_active') ? 1 : 0;
                $template = trim($this->postData('template', ''));
                $targetRole = trim($this->postData('target_role', 'admin,hr_manager'));

                $this->db->query(
                    "UPDATE notification_rules 
                     SET days_before = :days, is_active = :active, template = :tmpl, target_role = :role
                     WHERE id = :id",
                    [
                        'days'   => $daysBefore,
                        'active' => $isActive,
                        'tmpl'   => $template,
                        'role'   => $targetRole,
                        'id'     => $ruleId
                    ]
                );

                AuditLogger::log('update', 'notification_rules', $ruleId, null, null, "Cập nhật quy tắc thông báo ID: {$ruleId}");
                Session::setFlash('success', 'Cập nhật quy tắc thông báo thành công.');
                $this->redirect('notification/settings');
                return;
            }

            if ($action === 'toggle_active') {
                $ruleId = (int)$this->postData('rule_id');
                $this->db->query("UPDATE notification_rules SET is_active = NOT is_active WHERE id = :id", ['id' => $ruleId]);
                Session::setFlash('success', 'Đã chuyển đổi trạng thái quy tắc thông báo.');
                $this->redirect('notification/settings');
                return;
            }
        }

        // Lấy danh sách quy tắc
        $this->db->query("SELECT * FROM notification_rules ORDER BY id ASC");
        $rules = $this->db->fetchAll();

        $this->view('notification/settings', [
            'rules'     => $rules,
            'pageTitle' => 'Cài đặt Quy tắc thông báo tự động'
        ]);
    }

    // ══════════════════════════════════════════════════════════
    //  HELPER FUNCTIONS
    // ══════════════════════════════════════════════════════════

    /**
     * Định dạng thời gian tương đối thân thiện (VD: "5 phút trước", "Hôm nay 08:30")
     */
    private function timeAgo(string $datetime): string
    {
        $timestamp = strtotime($datetime);
        $diff = time() - $timestamp;

        if ($diff < 60) {
            return 'Vừa xong';
        } elseif ($diff < 3600) {
            return floor($diff / 60) . ' phút trước';
        } elseif ($diff < 86400) {
            return floor($diff / 3600) . ' giờ trước';
        } elseif ($diff < 172800) {
            return 'Hôm qua ' . date('H:i', $timestamp);
        } else {
            return date('d/m/Y H:i', $timestamp);
        }
    }

    /**
     * Kiểm tra request có phải là AJAX không
     */
    private function isAjax(): bool
    {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
            || (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'));
    }
}
