<?php
/**
 * ============================================================
 *  POSUNG HRIS – MovementController
 * ============================================================
 *  Proxy Controller cho chức năng Điều động (Job Move).
 *  Sidebar trỏ tới /movement nhưng logic nằm ở TransferController.
 *  Controller này tái sử dụng logic TransferController.
 * ============================================================
 */

class MovementController extends Controller
{
    /**
     * Danh sách Lệnh điều động
     */
    public function index(): void
    {
        Session::checkPermission(['Admin', 'HR_Manager', 'Project_Manager']);

        $jobMovementModel = $this->model('JobMovement');
        $status = $this->getData('status');
        
        $orders = $jobMovementModel->getOrders($status);

        $this->view('layouts/header', ['pageTitle' => 'Lệnh Điều động (Transfer Orders)']);
        $this->view('transfer/index', [
            'orders' => $orders,
            'currentStatus' => $status
        ]);
        $this->view('layouts/footer');
    }

    /**
     * Tạo Lệnh điều động → chuyển sang TransferController
     */
    public function create(): void
    {
        $this->redirect('transfer/create');
    }

    /**
     * Phê duyệt → chuyển sang TransferController
     */
    public function approve(int $id = 0): void
    {
        $this->redirect('transfer/approve/' . $id);
    }

    /**
     * In Quyết định → chuyển sang TransferController
     */
    public function exportDecision(int $id = 0): void
    {
        $this->redirect('transfer/exportDecision/' . $id);
    }
}
