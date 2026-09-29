<?php
/**
 * ============================================================
 *  POSUNG HRIS – Controller Audit (Nhật ký Hoạt động Hệ thống)
 * ============================================================
 */

class AuditController extends Controller
{
    public function __construct()
    {
        if (!Session::isLoggedIn()) {
            $this->redirect('auth/login');
            return;
        }

        if (!Session::isSuperAdmin() && !Session::hasPermission('database_mgr.view')) {
            $this->abort403('Bạn không có quyền truy cập Nhật ký Kiểm toán hệ thống.');
            return;
        }
    }

    /**
     * Danh sách nhật ký hoạt động
     */
    public function index(): void
    {
        $auditModel = $this->model('AuditLog');

        $filters = [
            'user_id'   => $this->getData('user_id', ''),
            'module'    => $this->getData('module', ''),
            'action'    => $this->getData('action', ''),
            'date_from' => $this->getData('date_from', ''),
            'date_to'   => $this->getData('date_to', ''),
            'search'    => $this->getData('search', ''),
        ];

        $page = (int)$this->getData('page', 1);
        if ($page < 1) $page = 1;

        $logData = $auditModel->getLogs($filters, $page, 30);
        $stats = $auditModel->getStats();
        $filterOptions = $auditModel->getFilterOptions();

        $this->view('layouts/header', ['pageTitle' => 'Nhật ký Hoạt động Hệ thống (Audit Log)']);
        $this->view('audit/index', [
            'logs'          => $logData['data'],
            'total'         => $logData['total'],
            'page'          => $logData['page'],
            'totalPages'    => $logData['total_pages'],
            'stats'         => $stats,
            'filters'       => $filters,
            'filterOptions' => $filterOptions,
        ]);
        $this->view('layouts/footer');
    }

    /**
     * Xem chi tiết 1 bản ghi nhật ký (hỗ trợ AJAX diff)
     */
    public function detail(int $id = 0): void
    {
        if ($id <= 0) {
            $this->redirect('audit');
            return;
        }

        $auditModel = $this->model('AuditLog');
        $log = $auditModel->getLogById($id);

        if (!$log) {
            Session::setFlash('error', 'Không tìm thấy bản ghi nhật ký này.');
            $this->redirect('audit');
            return;
        }

        // Nếu request AJAX -> trả JSON
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) || $this->getData('format') === 'json') {
            $this->json(['success' => true, 'log' => $log]);
            return;
        }

        $this->view('layouts/header', ['pageTitle' => 'Chi tiết Nhật ký #' . $log->id]);
        $this->view('audit/detail', ['log' => $log]);
        $this->view('layouts/footer');
    }

    /**
     * Xuất danh sách nhật ký ra CSV
     */
    public function export(): void
    {
        $auditModel = $this->model('AuditLog');

        $filters = [
            'user_id'   => $this->getData('user_id', ''),
            'module'    => $this->getData('module', ''),
            'action'    => $this->getData('action', ''),
            'date_from' => $this->getData('date_from', ''),
            'date_to'   => $this->getData('date_to', ''),
            'search'    => $this->getData('search', ''),
        ];

        $logs = $auditModel->exportLogs($filters);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="Audit_Log_' . date('Ymd_His') . '.csv"');
        $output = fopen('php://output', 'w');
        fputs($output, "\xEF\xBB\xBF"); // UTF-8 BOM

        fputcsv($output, ['NHẬT KÝ HOẠT ĐỘNG HỆ THỐNG POSUNG HRIS - XUẤT NGÀY ' . date('d/m/Y H:i:s')]);
        fputcsv($output, ['ID', 'Thời gian', 'Người thực hiện', 'Hành động', 'Phân hệ', 'Mã bản ghi', 'Mô tả chi tiết', 'Địa chỉ IP', 'User Agent']);

        foreach ($logs as $l) {
            fputcsv($output, [
                $l['id'],
                $l['created_at'],
                $l['user_name'] ?? ('User #' . $l['user_id']),
                strtoupper($l['action']),
                strtoupper($l['module']),
                $l['record_id'] ?? '',
                $l['description'],
                $l['ip_address'],
                $l['user_agent'],
            ]);
        }

        fclose($output);
        exit;
    }
}
