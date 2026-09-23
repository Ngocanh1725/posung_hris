<?php
/**
 * ============================================================
 *  POSUNG HRIS – TransferController
 * ============================================================
 *  Điều khiển các thao tác Điều động & Luân chuyển Công tác.
 * ============================================================
 */

class TransferController extends Controller
{
    /**
     * Danh sách Lệnh điều động (Transfer Orders)
     */
    public function index(): void
    {
        Session::checkPermission(['Admin', 'HR_Manager', 'Project_Manager']);

        $jobMovementModel = $this->model('JobMovement');
        $status = $this->getData('status'); // Lọc theo trạng thái nếu có
        
        $orders = $jobMovementModel->getOrders($status);

        $this->view('layouts/header', ['pageTitle' => 'Lệnh Điều động (Transfer Orders)']);
        $this->view('transfer/index', [
            'orders' => $orders,
            'currentStatus' => $status
        ]);
        $this->view('layouts/footer');
    }

    /**
     * Giao diện Tạo Lệnh điều động hàng loạt
     */
    public function create(): void
    {
        Session::checkPermission(['Admin', 'HR_Manager', 'Project_Manager']);

        $projectModel = $this->model('Project');
        $projects = $projectModel->getActiveProjects();

        // Lấy tất cả Cost Centers (Trong thực tế có thể gọi AJAX theo Dự án đích)
        $db = Database::getInstance();
        $db->query("SELECT * FROM cost_centers ORDER BY code ASC");
        $costCenters = json_decode(json_encode($db->fetchAll()));

        // Lấy danh sách nhân viên để chọn (Tạm load tất cả những người đang Active, kèm theo cờ kiểm tra Thợ hàn 6G)
        $db->query("SELECT e.id, e.emp_code, e.full_name, e.employee_type, e.current_project_id,
                           EXISTS(SELECT 1 FROM employee_certificates ec WHERE ec.employee_id = e.id AND ec.cert_name LIKE '%6G%' AND (ec.expiry_date IS NULL OR ec.expiry_date >= CURDATE())) AS is_6g_welder
                    FROM employees e 
                    WHERE e.status = 'Active' 
                    ORDER BY e.emp_code ASC");
        $employees = json_decode(json_encode($db->fetchAll()));

        $this->view('layouts/header', ['pageTitle' => 'Tạo Lệnh Điều Động']);
        $this->view('transfer/create', [
            'projects' => $projects,
            'costCenters' => $costCenters,
            'employees' => $employees
        ]);
        $this->view('layouts/footer');
    }

    /**
     * Xử lý Lưu Lệnh điều động
     */
    public function store(): void
    {
        Session::checkPermission(['Admin', 'HR_Manager', 'Project_Manager']);

        if ($this->isPost()) {
            $employeeIds = $_POST['employee_ids'] ?? [];
            if (empty($employeeIds)) {
                Session::setFlash('error', 'Vui lòng chọn ít nhất 1 nhân sự để điều động.');
                $this->redirect('transfer/create');
                return;
            }

            $orderData = [
                'decision_number' => $this->postData('decision_number'),
                'from_project_id' => $this->postData('from_project_id') ?: null,
                'to_project_id'   => $this->postData('to_project_id') ?: null,
                'effective_date'  => $this->postData('effective_date'),
                'reason'          => $this->postData('reason'),
                'created_by'      => Session::userId()
            ];

            $costCenterId = (int) $this->postData('cost_center_id');
            
            // Xử lý nút bấm (Draft hoặc Pending)
            $action = $this->postData('action_type', 'Draft');
            $status = ($action === 'Pending') ? 'Pending' : 'Draft';

            $jobMovementModel = $this->model('JobMovement');
            $orderId = $jobMovementModel->createTransferOrder($orderData, $employeeIds, $costCenterId, $status);

            if ($orderId) {
                if ($status === 'Pending') {
                    Session::setFlash('success', 'Đã tạo Lệnh điều động (Transfer Order) thành công. Đang chờ phê duyệt.');
                } else {
                    Session::setFlash('success', 'Đã lưu nháp Lệnh điều động.');
                }
                $this->redirect('transfer');
            } else {
                Session::setFlash('error', 'Lỗi hệ thống khi tạo Lệnh điều động (Có thể trùng số Quyết định).');
                $this->redirect('transfer/create');
            }
        }
    }

    /**
     * Phê duyệt Lệnh điều động
     */
    public function approve(int $id = 0): void
    {
        // Chỉ cấp Giám đốc / Trưởng phòng mới được duyệt
        Session::checkPermission(['Admin', 'HR_Manager']);

        if ($id <= 0) {
            $this->redirect('transfer');
            return;
        }

        $jobMovementModel = $this->model('JobMovement');
        $success = $jobMovementModel->approveTransferOrder($id, Session::userId());

        if ($success) {
            Session::setFlash('success', 'Đã PHÊ DUYỆT Lệnh điều động. Hồ sơ nhân sự và Cost Center đã được cập nhật!');
        } else {
            Session::setFlash('error', 'Không thể phê duyệt (Lệnh không tồn tại hoặc đã được duyệt trước đó).');
        }

        $this->redirect('transfer');
    }

    /**
     * Xem / In Quyết định (Bản HTML để in)
     */
    public function decisionPrint(int $id = 0): void
    {
        Session::checkPermission(['Admin', 'HR_Manager', 'Project_Manager']);

        $jobMovementModel = $this->model('JobMovement');
        $order = $jobMovementModel->getOrderDetails($id);

        if (!$order) {
            die('Lệnh điều động không tồn tại.');
        }

        // Render view HTML dành riêng cho In ấn
        $this->view('transfer/decision_print', [
            'order' => $order
        ]);
    }
}
