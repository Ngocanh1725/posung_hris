<?php
class WorkflowController extends Controller
{
    public function index(): void
    {
        $this->checkPermission('workflow.view');
        
        $model = $this->model('Workflow');
        
        // My Requests
        $empId = Session::get('employee_id') ?? 1; // Demo fallback
        $myRequests = $model->getMyRequests($empId);

        // Pending Approvals (For HR/Managers)
        $approvals = $model->getPendingApprovals();

        $this->view('layouts/header', ['pageTitle' => 'Phê duyệt & E-Sign (Kanban)']);
        $this->view('workflow/index', [
            'myRequests' => $myRequests, 
            'approvals' => $approvals
        ]);
        $this->view('layouts/footer');
    }

    public function process(): void
    {
        $this->checkPermission('workflow.view');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $reqId = $_POST['request_id'] ?? 0;
            $action = $_POST['action'] ?? '';
            $password = $_POST['esign_password'] ?? '';
            $comment = $_POST['comment'] ?? '';
            
            $userId = Session::userId();

            $model = $this->model('Workflow');
            $success = $model->processRequest($reqId, $userId, $action, $password, $comment);

            if ($success) {
                $_SESSION['flash_success'] = "Đã xử lý yêu cầu thành công!";
            } else {
                $_SESSION['flash_error'] = "Xử lý thất bại! Sai mật khẩu cấp 2 hoặc yêu cầu không hợp lệ.";
            }

            header('Location: ' . BASE_URL . '/workflow');
            exit;
        }
    }
}
