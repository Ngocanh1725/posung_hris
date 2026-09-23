<?php
/**
 * ============================================================
 *  POSUNG HRIS – MenuAdminController
 * ============================================================
 */

class MenuAdminController extends Controller
{
    public function __construct()
    {
        if (!Session::isLoggedIn()) {
            $this->redirect('auth/login');
        }
        $this->requirePermission('frame_menu', 'manage');
    }

    public function index(): void
    {
        $db = Database::getInstance();
        $db->query("SELECT * FROM system_menus ORDER BY sort_order ASC, id ASC");
        $menus = $db->fetchAll();

        $this->view('layouts/header', ['pageTitle' => 'Quản trị Cây Menu']);
        $this->view('menu_admin/index', ['menus' => $menus]);
        $this->view('layouts/footer');
    }

    public function create(): void
    {
        // View create form (Giả lập để tiết kiệm thời gian)
        echo "Tính năng thêm Menu đang được hoàn thiện. Vui lòng thao tác trực tiếp trên CSDL.";
    }

    public function edit(int $id): void
    {
        // View edit form
        echo "Tính năng sửa Menu đang được hoàn thiện. Vui lòng thao tác trực tiếp trên CSDL.";
    }

    public function delete(int $id): void
    {
        $db = Database::getInstance();
        $db->query("DELETE FROM system_menus WHERE id = :id", ['id' => $id]);
        Session::setFlash('success', 'Đã xóa Menu thành công.');
        $this->redirect('menuAdmin');
    }
}
