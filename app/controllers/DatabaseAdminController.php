<?php
/**
 * ============================================================
 *  POSUNG HRIS – DatabaseAdminController
 * ============================================================
 */

class DatabaseAdminController extends Controller
{
    public function __construct()
    {
        if (!Session::isLoggedIn()) {
            $this->redirect('auth/login');
        }
        $this->requirePermission('database_mgr', 'view');
    }

    public function index(): void
    {
        $db = Database::getInstance();
        $stmt = $db->query("SHOW TABLE STATUS");
        $tables = $db->fetchAll();

        $this->view('layouts/header', ['pageTitle' => 'Quản trị Cơ sở dữ liệu']);
        $this->view('database_admin/index', ['tables' => $tables]);
        $this->view('layouts/footer');
    }

    public function backup(): void
    {
        $this->requirePermission('database_mgr', 'backup');
        
        // Cần thực hiện backup database
        // ... Code backup database 
        Session::setFlash('success', 'Tính năng sao lưu đang được hoàn thiện. (Mô phỏng thành công)');
        $this->redirect('databaseAdmin');
    }

    public function optimize(): void
    {
        $this->requirePermission('database_mgr', 'optimize');
        
        $db = Database::getInstance();
        $stmt = $db->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($tables as $t) {
            $db->query("OPTIMIZE TABLE `$t`");
        }

        Session::setFlash('success', 'Đã tối ưu hóa ' . count($tables) . ' bảng trong Cơ sở dữ liệu.');
        $this->redirect('databaseAdmin');
    }
}
