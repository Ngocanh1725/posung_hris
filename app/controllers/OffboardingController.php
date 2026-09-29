<?php
/**
 * ============================================================
 *  POSUNG HRIS – OffboardingController
 * ============================================================
 *  Điều phối Quy trình Thôi việc Đa bộ phận (IT, HR, Finance, HSE, Admin).
 *  Tích hợp liên kết Thu hồi Tài sản, Quyết toán Lương/Phép, Chốt BHXH.
 * ============================================================
 */

class OffboardingController extends Controller
{
    private Offboarding $offboardingModel;

    public function __construct()
    {
        $this->offboardingModel = $this->model('Offboarding');
    }

    /**
     * Dashboard tổng quan tiến trình thôi việc
     * URL: /offboarding hoặc /offboarding/index
     */
    public function index(): void
    {
        $this->checkPermission('employee.view');

        $status = $this->getData('status', '');
        $deptId = (int)$this->getData('department_id', 0);
        $reason = $this->getData('reason', '');
        $search = $this->getData('search', '');

        $filters = [
            'status'        => $status,
            'department_id' => $deptId,
            'reason'        => $reason,
            'search'        => $search
        ];

        $stats        = $this->offboardingModel->getDashboardStats();
        $offboardings = $this->offboardingModel->getAllOffboardings($filters);
        $departments  = $this->model('Department')->all('dept_name ASC');
        $templates    = $this->offboardingModel->getTemplates();

        // Danh sách nhân viên đang Active để phục vụ modal khởi tạo nhanh
        $activeEmployees = $this->model('Employee')->getAll([
            'status' => 'Active',
            'limit'  => 500
        ]);

        $this->view('layouts/header', ['pageTitle' => 'Quy trình Thôi việc Đa phòng ban (Offboarding)']);
        $this->view('offboarding/index', [
            'stats'           => $stats,
            'offboardings'    => $offboardings,
            'departments'     => $departments,
            'templates'       => $templates,
            'activeEmployees' => $activeEmployees,
            'filters'         => $filters
        ]);
        $this->view('layouts/footer');
    }

    /**
     * Bắt đầu quy trình thôi việc cho một nhân viên
     * URL: /offboarding/start hoặc /offboarding/start/{employee_id}
     */
    public function start(int $employeeId = 0): void
    {
        $this->checkPermission('employee.edit');

        if ($this->isPost()) {
            $empId          = (int)$this->postData('employee_id', $employeeId);
            $templateId     = (int)$this->postData('template_id', 1);
            $lastWorkingDay = $this->postData('last_working_day', date('Y-m-d'));
            $reason         = $this->postData('reason', 'Resign');
            $notes          = trim($this->postData('notes', ''));

            if (!$empId || !$templateId || !$lastWorkingDay) {
                Session::setFlash('error', 'Vui lòng điền đầy đủ các thông tin bắt buộc.');
                $this->redirect('offboarding');
                return;
            }

            try {
                $offboardingId = $this->offboardingModel->startOffboarding([
                    'employee_id'      => $empId,
                    'template_id'      => $templateId,
                    'last_working_day' => $lastWorkingDay,
                    'reason'           => $reason,
                    'notes'            => $notes,
                    'created_by'       => Session::userId()
                ]);

                Session::setFlash('success', 'Đã khởi tạo Quy trình Thôi việc thành công! Vui lòng hoàn tất checklist.');
                $this->redirect("offboarding/detail/{$offboardingId}");
                return;
            } catch (Exception $e) {
                Session::setFlash('error', 'Lỗi: ' . $e->getMessage());
                $this->redirect('offboarding');
                return;
            }
        }

        // Màn hình Form nếu gọi GET /offboarding/start/{employee_id}
        $employee = null;
        if ($employeeId > 0) {
            $employee = $this->model('Employee')->getById($employeeId);
        }

        $templates = $this->offboardingModel->getTemplates();
        $employees = $this->model('Employee')->getAll(['status' => 'Active']);

        $this->view('layouts/header', ['pageTitle' => 'Khởi tạo Quy trình Thôi việc']);
        $this->view('offboarding/start', [
            'employee'  => $employee,
            'templates' => $templates,
            'employees' => $employees
        ]);
        $this->view('layouts/footer');
    }

    /**
     * Chi tiết hồ sơ thôi việc và checklist đa phòng ban
     * URL: /offboarding/detail/{id}
     */
    public function detail(int $id): void
    {
        $this->checkPermission('employee.view');

        $offboarding = $this->offboardingModel->getOffboardingDetail($id);
        if (!$offboarding) {
            Session::setFlash('error', 'Không tìm thấy hồ sơ thôi việc.');
            $this->redirect('offboarding');
            return;
        }

        $this->view('layouts/header', ['pageTitle' => 'Chi tiết Offboarding – ' . $offboarding['employee_name']]);
        $this->view('offboarding/detail', [
            'offboarding' => $offboarding,
            'canEdit'     => Session::hasPermission('employee.edit') || Session::isHR() || Session::isAdmin()
        ]);
        $this->view('layouts/footer');
    }

    /**
     * API AJAX Cập nhật trạng thái từng checklist item
     * POST /offboarding/updateItem
     */
    public function updateItem(): void
    {
        $this->checkPermission('employee.edit');

        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => 'Phương thức không được hỗ trợ.'], 405);
            return;
        }

        $input = $this->getJsonInput();
        $itemId = (int)($input['item_id'] ?? $this->postData('item_id'));
        $status = $input['status'] ?? $this->postData('status');
        $notes  = $input['notes'] ?? $this->postData('notes');
        $offboardingId = (int)($input['offboarding_id'] ?? $this->postData('offboarding_id'));

        if (!$itemId || !$status) {
            $this->json(['success' => false, 'message' => 'Dữ liệu không đầy đủ.'], 400);
            return;
        }

        try {
            $this->offboardingModel->updateItemStatus($itemId, $status, $notes, Session::userId());

            // Kiểm tra trạng thái có thể hoàn tất chưa
            $check = ['can_complete' => false, 'pending_count' => 0];
            if ($offboardingId) {
                $check = $this->offboardingModel->canComplete($offboardingId);
            }

            $this->json([
                'success'              => true,
                'message'              => 'Cập nhật mục bàn giao thành công.',
                'can_complete'         => $check['can_complete'],
                'pending_blocking_cnt' => $check['pending_count']
            ]);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Hoàn tất toàn bộ quy trình thôi việc & Chốt trạng thái nhân sự
     * POST /offboarding/complete/{id}
     */
    public function complete(int $id): void
    {
        $this->checkPermission('employee.edit');

        if (!$this->isPost()) {
            $this->redirect("offboarding/detail/{$id}");
            return;
        }

        try {
            $this->offboardingModel->completeOffboarding($id, Session::userId());
            Session::setFlash('success', 'Chúc mừng! Đã hoàn tất toàn bộ quy trình thôi việc. Trạng thái nhân sự đã được cập nhật chính thức.');
            $this->redirect("offboarding/detail/{$id}");
        } catch (Exception $e) {
            Session::setFlash('error', 'Không thể hoàn tất: ' . $e->getMessage());
            $this->redirect("offboarding/detail/{$id}");
        }
    }

    /**
     * Mẫu quy trình (Templates)
     * URL: /offboarding/templates
     */
    public function templates(): void
    {
        $this->checkPermission('employee.view');

        $templates = $this->offboardingModel->getTemplates(false);

        $this->view('layouts/header', ['pageTitle' => 'Mẫu Quy trình Thôi việc']);
        $this->view('offboarding/templates', [
            'templates' => $templates
        ]);
        $this->view('layouts/footer');
    }
}
