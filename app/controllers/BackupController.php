<?php
/**
 * ============================================================
 *  POSUNG HRIS – Controller Backup (Sao lưu & Phục hồi CSDL)
 * ============================================================
 */

class BackupController extends Controller
{
    public function __construct()
    {
        if (!Session::isLoggedIn()) {
            $this->redirect('auth/login');
            return;
        }

        if (!Session::isSuperAdmin() && !Session::hasPermission('database_mgr.view')) {
            $this->abort403('Bạn không có quyền truy cập chức năng Sao lưu & Phục hồi CSDL.');
            return;
        }
    }

    /**
     * Danh sách các bản sao lưu đã tạo
     */
    public function index(): void
    {
        $backupModel = $this->model('Backup');

        $backups = $backupModel->getBackups();
        $stats = $backupModel->getStats();
        $settings = $backupModel->getSettings();

        $this->view('layouts/header', ['pageTitle' => 'Sao lưu & Phục hồi Cơ sở dữ liệu']);
        $this->view('backup/index', [
            'backups'  => $backups,
            'stats'    => $stats,
            'settings' => $settings,
        ]);
        $this->view('layouts/footer');
    }

    /**
     * Tạo bản sao lưu CSDL mới
     */
    public function create(): void
    {
        $backupModel = $this->model('Backup');
        $notes = $this->postData('notes', 'Sao lưu thủ công từ giao diện');

        $result = $backupModel->createBackup('Manual', $notes);

        if ($this->getData('format') === 'json' || isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            $this->json($result);
            return;
        }

        if ($result['success']) {
            Session::setFlash('success', $result['message']);
        } else {
            Session::setFlash('error', $result['message']);
        }

        $this->redirect('backup');
    }

    /**
     * Tải xuống file SQL backup
     */
    public function download(string $filename = ''): void
    {
        if (empty($filename)) {
            $filename = $this->getData('file', '');
        }

        $safeName = basename($filename);
        $backupModel = $this->model('Backup');
        $filepath = $backupModel->getBackupDir() . '/' . $safeName;

        if (empty($safeName) || !file_exists($filepath)) {
            Session::setFlash('error', 'File sao lưu không tồn tại hoặc đã bị xóa.');
            $this->redirect('backup');
            return;
        }

        AuditLogger::log('export', 'system', $safeName, null, null, "Tải xuống file sao lưu CSDL: {$safeName}");

        header('Content-Description: File Transfer');
        header('Content-Type: application/sql');
        header('Content-Disposition: attachment; filename="' . $safeName . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filepath));
        readfile($filepath);
        exit;
    }

    /**
     * Phục hồi CSDL từ bản backup
     */
    public function restore(string $filename = ''): void
    {
        if (empty($filename)) {
            $filename = $this->postData('filename', $this->getData('file', ''));
        }

        $safeName = basename($filename);
        if (empty($safeName)) {
            Session::setFlash('error', 'Vui lòng chọn file sao lưu cần phục hồi.');
            $this->redirect('backup');
            return;
        }

        // Kiểm tra mã xác thực an toàn nếu submit form
        if ($this->isPost()) {
            $confirmWord = trim($this->postData('confirm_code', ''));
            if ($confirmWord !== 'RESTORE') {
                Session::setFlash('error', 'Mã xác nhận không chính xác! Vui lòng nhập đúng chữ "RESTORE" để tiếp tục.');
                $this->redirect('backup');
                return;
            }
        }

        $backupModel = $this->model('Backup');
        $result = $backupModel->restoreBackup($safeName);

        if ($result['success']) {
            Session::setFlash('success', $result['message']);
        } else {
            Session::setFlash('error', $result['message']);
        }

        $this->redirect('backup');
    }

    /**
     * Xóa bản sao lưu cũ
     */
    public function delete(string $filename = ''): void
    {
        if (empty($filename)) {
            $filename = $this->postData('filename', $this->getData('file', ''));
        }

        $safeName = basename($filename);
        if (!empty($safeName)) {
            $backupModel = $this->model('Backup');
            $backupModel->deleteBackup($safeName);
            Session::setFlash('success', "Đã xóa bản sao lưu {$safeName} thành công.");
        } else {
            Session::setFlash('error', 'Không tìm thấy file cần xóa.');
        }

        $this->redirect('backup');
    }

    /**
     * Cài đặt sao lưu tự động
     */
    public function autoBackupSettings(): void
    {
        if (!$this->isPost()) {
            $this->redirect('backup');
            return;
        }

        $backupModel = $this->model('Backup');
        $data = [
            'auto_backup_enabled'   => $this->postData('auto_backup_enabled', '0'),
            'auto_backup_frequency' => $this->postData('auto_backup_frequency', 'daily'),
            'auto_backup_time'      => $this->postData('auto_backup_time', '02:00'),
            'auto_backup_keep_days' => $this->postData('auto_backup_keep_days', '30'),
        ];

        $backupModel->saveSettings($data);
        Session::setFlash('success', 'Đã lưu cấu hình sao lưu tự động thành công!');
        $this->redirect('backup');
    }
}
