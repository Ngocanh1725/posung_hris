<?php
/**
 * ============================================================
 *  POSUNG HRIS – Workflow & Approval Controller
 * ============================================================
 *  Trung tâm Phê duyệt & Động cơ Quy trình (Approval Engine):
 *  - Hộp duyệt tập trung (Approval Inbox): Leave + Loan + Expense + Transfer + Profile Change
 *  - Quản lý cấu hình luồng duyệt (Approval Flows)
 *  - Quản lý yêu cầu thay đổi hồ sơ & Diff view (Profile Change Requests)
 *  - Xử lý Phê duyệt / Từ chối đa cấp
 * ============================================================
 */

class WorkflowController extends Controller
{
    /**
     * Mặc định chuyển về Dashboard Hộp duyệt tập trung
     */
    public function index(): void
    {
        $this->pendingApprovals();
    }

    /**
     * Dashboard "Hộp duyệt" tập trung (Approval Inbox)
     * Gộp tất cả các loại đơn: Leave, Loan, Expense, Transfer, Profile Change
     */
    public function pendingApprovals(): void
    {
        $this->checkPermission('workflow.view');

        $workflowModel = $this->model('Workflow');
        $module = $_GET['module'] ?? 'all';
        $search = trim($_GET['search'] ?? '');

        // Lấy danh sách đơn chờ duyệt
        $requests = $workflowModel->getPendingApprovals($module);

        // Lọc tìm kiếm nếu có
        if (!empty($search)) {
            $requests = array_filter($requests, function ($req) use ($search) {
                $searchLower = mb_strtolower($search, 'UTF-8');
                $title = mb_strtolower($req['details']['title'] ?? '', 'UTF-8');
                $empName = mb_strtolower($req['details']['employee_name'] ?? $req['creator_name'] ?? '', 'UTF-8');
                $empCode = mb_strtolower($req['details']['emp_code'] ?? $req['creator_emp_code'] ?? '', 'UTF-8');
                return str_contains($title, $searchLower) || str_contains($empName, $searchLower) || str_contains($empCode, $searchLower);
            });
        }

        // Đếm số lượng theo từng module
        $counts = $workflowModel->getPendingCounts();

        $this->view('layouts/header', ['pageTitle' => 'Hộp duyệt tập trung (Approval Inbox)']);
        $this->view('workflow/pending', [
            'requests'      => $requests,
            'counts'        => $counts,
            'currentModule' => $module,
            'search'        => $search
        ]);
        $this->view('layouts/footer');
    }

    /**
     * Quản lý luồng duyệt đa cấp (Approval Flows CRUD)
     */
    public function flows(): void
    {
        $this->checkPermission('workflow.view');

        $workflowModel = $this->model('Workflow');
        $flows = $workflowModel->getFlows();

        // Danh sách vai trò có thể chỉ định duyệt
        $roles = [
            'direct_manager'   => 'Quản lý trực tiếp (Direct Manager)',
            'dept_head'        => 'Trưởng phòng / Trưởng bộ phận',
            'site_manager'     => 'Chỉ huy trưởng / Giám đốc Dự án',
            'hr_admin'         => 'Chuyên viên Nhân sự (C&B)',
            'hr_manager'       => 'Trưởng phòng Nhân sự',
            'accountant'       => 'Kế toán trưởng / Kế toán thanh toán',
            'director'         => 'Ban Giám đốc điều hành (Director)',
            'super_admin'      => 'Quản trị tối cao (Super Admin)'
        ];

        $this->view('layouts/header', ['pageTitle' => 'Cấu hình Luồng phê duyệt (Approval Flows)']);
        $this->view('workflow/flows', [
            'flows' => $flows,
            'roles' => $roles
        ]);
        $this->view('layouts/footer');
    }

    /**
     * Lưu hoặc cập nhật quy trình duyệt (POST)
     */
    public function saveFlow(): void
    {
        $this->checkPermission('workflow.view');

        if (!$this->isPost()) {
            $this->redirect('workflow/flows');
            return;
        }

        $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
        $name = trim($_POST['name'] ?? '');
        $module = trim($_POST['module'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        // Parse danh sách các bước duyệt (steps) từ form
        $stepsInput = $_POST['steps'] ?? [];
        $steps = [];

        if (is_array($stepsInput)) {
            $level = 1;
            foreach ($stepsInput as $st) {
                if (!empty($st['role'])) {
                    $steps[] = [
                        'level'          => $level++,
                        'role'           => $st['role'],
                        'role_name'      => $st['role_name'] ?? $st['role'],
                        'required_count' => !empty($st['required_count']) ? (int)$st['required_count'] : 1
                    ];
                }
            }
        }

        if (empty($name) || empty($module) || empty($steps)) {
            Session::setFlash('error', 'Vui lòng nhập tên quy trình, phân hệ và ít nhất một bước duyệt.');
            $this->redirect('workflow/flows');
            return;
        }

        $workflowModel = $this->model('Workflow');
        $workflowModel->saveFlow([
            'id'          => $id,
            'name'        => $name,
            'module'      => $module,
            'description' => $description,
            'steps'       => $steps,
            'is_active'   => $isActive
        ]);

        Session::setFlash('success', 'Đã lưu cấu hình luồng phê duyệt thành công!');
        $this->redirect('workflow/flows');
    }

    /**
     * Xóa quy trình duyệt
     */
    public function deleteFlow(mixed $id): void
    {
        $this->checkPermission('workflow.view');

        $flowId = (int)$id;
        if ($flowId > 0) {
            $workflowModel = $this->model('Workflow');
            $workflowModel->deleteFlow($flowId);
            Session::setFlash('success', 'Đã xóa quy trình phê duyệt.');
        }

        $this->redirect('workflow/flows');
    }

    /**
     * Xử lý phê duyệt (Approve) đơn từ Hộp duyệt
     */
    public function approve(mixed $id = null): void
    {
        $this->checkPermission('workflow.view');

        $reqId = $id ? (int)$id : (int)($_POST['request_id'] ?? 0);
        $comments = trim($_POST['comments'] ?? $_GET['comments'] ?? '');
        $userId = Session::userId() ?? 1;

        if ($reqId <= 0) {
            Session::setFlash('error', 'Yêu cầu không hợp lệ.');
            $this->redirect('workflow/pendingApprovals');
            return;
        }

        // Tùy chọn kiểm tra mã PIN E-Sign nếu có gửi
        if (isset($_POST['esign_pin']) && !empty($_POST['esign_pin'])) {
            $workflowModel = $this->model('Workflow');
            if (!$workflowModel->verifyEsignPin($userId, $_POST['esign_pin'])) {
                Session::setFlash('error', 'Mã PIN E-Sign không chính xác! Không thể thực hiện ký duyệt.');
                $this->redirect('workflow/pendingApprovals');
                return;
            }
        }

        $result = WorkflowService::approve($reqId, $userId, $comments);

        if ($result['success']) {
            Session::setFlash('success', $result['message']);
        } else {
            Session::setFlash('error', $result['message']);
        }

        $redirectUrl = !empty($_POST['return_url']) ? $_POST['return_url'] : 'workflow/pendingApprovals';
        $this->redirect($redirectUrl);
    }

    /**
     * Xử lý từ chối (Reject) đơn từ Hộp duyệt
     */
    public function reject(mixed $id = null): void
    {
        $this->checkPermission('workflow.view');

        $reqId = $id ? (int)$id : (int)($_POST['request_id'] ?? 0);
        $comments = trim($_POST['comments'] ?? $_GET['comments'] ?? '');
        $userId = Session::userId() ?? 1;

        if ($reqId <= 0) {
            Session::setFlash('error', 'Yêu cầu không hợp lệ.');
            $this->redirect('workflow/pendingApprovals');
            return;
        }

        if (empty($comments)) {
            $comments = 'Không đủ điều kiện phê duyệt.';
        }

        $result = WorkflowService::reject($reqId, $userId, $comments);

        if ($result['success']) {
            Session::setFlash('success', $result['message']);
        } else {
            Session::setFlash('error', $result['message']);
        }

        $redirectUrl = !empty($_POST['return_url']) ? $_POST['return_url'] : 'workflow/pendingApprovals';
        $this->redirect($redirectUrl);
    }

    /**
     * Quản lý các yêu cầu thay đổi hồ sơ & Diff view (HR Dashboard)
     */
    public function profileChanges(): void
    {
        $this->checkPermission('workflow.view');

        $status = $_GET['status'] ?? 'Pending';
        $workflowModel = $this->model('Workflow');
        $requests = $workflowModel->getProfileChanges($status);

        $this->view('layouts/header', ['pageTitle' => 'Duyệt thay đổi hồ sơ cá nhân (Diff View)']);
        $this->view('workflow/profile_changes', [
            'requests'      => $requests,
            'currentStatus' => $status
        ]);
        $this->view('layouts/footer');
    }

    /**
     * Phê duyệt trực tiếp yêu cầu đổi hồ sơ từ màn hình Diff View
     */
    public function approveProfileChange(mixed $id): void
    {
        $this->checkPermission('workflow.view');

        $pcrId = (int)$id;
        $userId = Session::userId() ?? 1;
        $comments = trim($_POST['comments'] ?? 'Thông tin hồ sơ hợp lệ, đồng ý phê duyệt.');

        // Tìm approval_request tương ứng
        $db = Database::getInstance();
        $db->query("SELECT id FROM approval_requests WHERE module = 'profile_change' AND record_id = :id LIMIT 1", ['id' => $pcrId]);
        $ar = $db->fetch();

        if ($ar) {
            $result = WorkflowService::approve((int)$ar['id'], $userId, $comments);
            if ($result['success']) {
                Session::setFlash('success', 'Đã duyệt cập nhật hồ sơ thành công!');
            } else {
                Session::setFlash('error', $result['message']);
            }
        } else {
            // Trường hợp chưa có approval_request thì cập nhật thẳng
            $workflowModel = $this->model('Workflow');
            $pcr = $workflowModel->getProfileChangeById($pcrId);
            if ($pcr) {
                $db->query("UPDATE employees SET `{$pcr['field_name']}` = :v, updated_at = NOW() WHERE id = :e", [
                    'v' => $pcr['new_value'],
                    'e' => $pcr['employee_id']
                ]);
                $db->query("UPDATE profile_change_requests SET status = 'Approved', reviewed_by = :uid, reviewed_at = NOW(), notes = :n WHERE id = :id", [
                    'uid' => $userId,
                    'n'   => $comments,
                    'id'  => $pcrId
                ]);
                Session::setFlash('success', 'Đã duyệt cập nhật hồ sơ thành công!');
            } else {
                Session::setFlash('error', 'Không tìm thấy yêu cầu.');
            }
        }

        $this->redirect('workflow/profileChanges');
    }

    /**
     * Từ chối yêu cầu đổi hồ sơ từ màn hình Diff View
     */
    public function rejectProfileChange(mixed $id): void
    {
        $this->checkPermission('workflow.view');

        $pcrId = (int)$id;
        $userId = Session::userId() ?? 1;
        $comments = trim($_POST['comments'] ?? 'Thông tin không chính xác hoặc không cung cấp đủ minh chứng.');

        $db = Database::getInstance();
        $db->query("SELECT id FROM approval_requests WHERE module = 'profile_change' AND record_id = :id LIMIT 1", ['id' => $pcrId]);
        $ar = $db->fetch();

        if ($ar) {
            $result = WorkflowService::reject((int)$ar['id'], $userId, $comments);
            Session::setFlash('success', 'Đã từ chối yêu cầu thay đổi hồ sơ.');
        } else {
            $db->query("UPDATE profile_change_requests SET status = 'Rejected', reviewed_by = :uid, reviewed_at = NOW(), notes = :n WHERE id = :id", [
                'uid' => $userId,
                'n'   => $comments,
                'id'  => $pcrId
            ]);
            Session::setFlash('success', 'Đã từ chối yêu cầu thay đổi hồ sơ.');
        }

        $this->redirect('workflow/profileChanges');
    }
}
