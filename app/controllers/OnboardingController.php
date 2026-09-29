<?php
/**
 * ============================================================
 *  POSUNG HRIS – OnboardingController
 * ============================================================
 *  Quản lý Quy trình Hội nhập & Checklist Nhân sự mới
 * ============================================================
 */

class OnboardingController extends Controller
{
    /**
     * Dashboard tổng quan tiến trình hội nhập nhân sự
     * URL: /onboarding hoặc /onboarding/dashboard
     */
    public function index(): void
    {
        $this->dashboard();
    }

    public function dashboard(): void
    {
        $this->checkPermission('employee.view');

        $onbModel = $this->model('Onboarding');
        $deptModel = $this->model('Department');

        $status = $this->getData('status', '');
        $deptId = (int)$this->getData('department_id', 0);
        $search = $this->getData('search', '');

        $stats       = $onbModel->getDashboardStats();
        $onboardings = $onbModel->getAllOnboardings([
            'status'        => $status,
            'department_id' => $deptId,
            'search'        => $search,
        ]);
        $departments = $deptModel->allWithManager();

        $this->view('layouts/header', ['pageTitle' => 'Dashboard Quy trình Hội nhập']);
        $this->view('onboarding/dashboard', [
            'stats'         => $stats,
            'onboardings'   => $onboardings,
            'departments'   => $departments,
            'currentStatus' => $status,
            'currentDept'   => $deptId,
            'search'        => $search,
        ]);
        $this->view('layouts/footer');
    }

    /**
     * Quản lý danh sách các mẫu quy trình (Templates)
     * URL: /onboarding/templates
     */
    public function templates(): void
    {
        $this->checkPermission('employee.view');

        $onbModel = $this->model('Onboarding');
        $templates = $onbModel->getAllTemplates();

        $this->view('layouts/header', ['pageTitle' => 'Mẫu Quy trình Hội nhập']);
        $this->view('onboarding/templates', [
            'templates' => $templates,
        ]);
        $this->view('layouts/footer');
    }

    /**
     * Form tạo mới mẫu Onboarding
     * URL: /onboarding/createTemplate
     */
    public function createTemplate(): void
    {
        $this->checkPermission('recruitment.manage');

        $deptModel = $this->model('Department');
        $departments = $deptModel->allWithManager();

        $this->view('layouts/header', ['pageTitle' => 'Tạo Mẫu Quy trình Hội nhập']);
        $this->view('onboarding/create_template', [
            'departments' => $departments,
        ]);
        $this->view('layouts/footer');
    }

    /**
     * Lưu mẫu Onboarding mới
     * URL: POST /onboarding/storeTemplate
     */
    public function storeTemplate(): void
    {
        $this->checkPermission('recruitment.manage');

        if (!$this->isPost()) {
            $this->redirect('onboarding/templates');
            return;
        }

        $name = trim($this->postData('name', ''));
        if (empty($name)) {
            Session::setFlash('error', 'Vui lòng nhập tên mẫu quy trình.');
            $this->redirect('onboarding/createTemplate');
            return;
        }

        $data = [
            'name'          => $name,
            'description'   => $this->postData('description'),
            'department_id' => $this->postData('department_id') ?: null,
            'is_active'     => $this->postData('is_active', 1),
        ];

        // Lấy danh sách tasks
        $tasks = [];
        $titles = $_POST['task_title'] ?? [];
        $descs  = $_POST['task_desc'] ?? [];
        $depts  = $_POST['task_dept'] ?? [];
        $days   = $_POST['task_days'] ?? [];
        $reqs   = $_POST['task_required'] ?? [];

        foreach ($titles as $idx => $title) {
            if (empty(trim($title))) continue;
            $tasks[] = [
                'title'                  => trim($title),
                'description'            => trim($descs[$idx] ?? ''),
                'responsible_department' => $depts[$idx] ?? 'HR',
                'due_days_after_join'    => (int)($days[$idx] ?? 1),
                'is_required'            => isset($reqs[$idx]) ? 1 : 0,
            ];
        }

        $onbModel = $this->model('Onboarding');
        $templateId = $onbModel->createTemplate($data, $tasks);

        if ($templateId > 0) {
            Session::setFlash('success', 'Đã tạo mẫu quy trình hội nhập thành công!');
            $this->redirect('onboarding/templates');
        } else {
            Session::setFlash('error', 'Có lỗi xảy ra khi lưu mẫu quy trình.');
            $this->redirect('onboarding/createTemplate');
        }
    }

    /**
     * Chỉnh sửa mẫu Onboarding
     * URL: /onboarding/editTemplate/{id}
     */
    public function editTemplate(int $id): void
    {
        $this->checkPermission('recruitment.manage');

        $onbModel = $this->model('Onboarding');
        $template = $onbModel->getTemplateById($id);

        if (!$template) {
            Session::setFlash('error', 'Không tìm thấy mẫu quy trình.');
            $this->redirect('onboarding/templates');
            return;
        }

        $deptModel = $this->model('Department');
        $departments = $deptModel->allWithManager();

        $this->view('layouts/header', ['pageTitle' => 'Chỉnh sửa Mẫu: ' . $template['name']]);
        $this->view('onboarding/edit_template', [
            'template'    => $template,
            'departments' => $departments,
        ]);
        $this->view('layouts/footer');
    }

    /**
     * Cập nhật mẫu Onboarding
     * URL: POST /onboarding/updateTemplate/{id}
     */
    public function updateTemplate(int $id): void
    {
        $this->checkPermission('recruitment.manage');

        if (!$this->isPost()) {
            $this->redirect('onboarding/templates');
            return;
        }

        $name = trim($this->postData('name', ''));
        if (empty($name)) {
            Session::setFlash('error', 'Vui lòng nhập tên mẫu quy trình.');
            $this->redirect('onboarding/editTemplate/' . $id);
            return;
        }

        $data = [
            'name'          => $name,
            'description'   => $this->postData('description'),
            'department_id' => $this->postData('department_id') ?: null,
            'is_active'     => $this->postData('is_active', 1),
        ];

        $tasks = [];
        $titles = $_POST['task_title'] ?? [];
        $descs  = $_POST['task_desc'] ?? [];
        $depts  = $_POST['task_dept'] ?? [];
        $days   = $_POST['task_days'] ?? [];
        $reqs   = $_POST['task_required'] ?? [];

        foreach ($titles as $idx => $title) {
            if (empty(trim($title))) continue;
            $tasks[] = [
                'title'                  => trim($title),
                'description'            => trim($descs[$idx] ?? ''),
                'responsible_department' => $depts[$idx] ?? 'HR',
                'due_days_after_join'    => (int)($days[$idx] ?? 1),
                'is_required'            => isset($reqs[$idx]) ? 1 : 0,
            ];
        }

        $onbModel = $this->model('Onboarding');
        if ($onbModel->updateTemplate($id, $data, $tasks)) {
            Session::setFlash('success', 'Đã cập nhật mẫu quy trình thành công!');
            $this->redirect('onboarding/templates');
        } else {
            Session::setFlash('error', 'Có lỗi xảy ra khi cập nhật mẫu.');
            $this->redirect('onboarding/editTemplate/' . $id);
        }
    }

    /**
     * Xóa mẫu Onboarding
     */
    public function deleteTemplate(int $id): void
    {
        $this->checkPermission('recruitment.manage');
        $onbModel = $this->model('Onboarding');
        $onbModel->deleteTemplate($id);
        Session::setFlash('success', 'Đã xử lý xóa/vô hiệu hóa mẫu quy trình.');
        $this->redirect('onboarding/templates');
    }

    /**
     * Bắt đầu Onboarding cho nhân viên mới
     * URL: /onboarding/start/{employee_id}
     */
    public function start(int $employeeId = 0): void
    {
        $this->checkPermission('employee.view');

        $empModel = $this->model('Employee');
        $onbModel = $this->model('Onboarding');

        // Kiểm tra xem đã có onboarding chưa
        $existing = $onbModel->getOnboardingByEmployee($employeeId);
        if ($existing) {
            $this->redirect('onboarding/show/' . $existing['id']);
            return;
        }

        $employee = $empModel->getDetailById($employeeId);
        if (!$employee) {
            Session::setFlash('error', 'Không tìm thấy thông tin nhân viên.');
            $this->redirect('employee');
            return;
        }

        if ($this->isPost()) {
            $templateId = (int)$this->postData('template_id');
            $startDate  = $this->postData('start_date') ?: date('Y-m-d');
            $notes      = $this->postData('notes');
            $userId     = Session::get('user_id');

            if ($templateId <= 0) {
                Session::setFlash('error', 'Vui lòng chọn mẫu quy trình hội nhập.');
            } else {
                $onbId = $onbModel->startOnboarding($employeeId, $templateId, $startDate, $notes, $userId);
                if ($onbId > 0) {
                    Session::setFlash('success', 'Đã khởi tạo quy trình hội nhập cho nhân viên thành công!');
                    $this->redirect('onboarding/show/' . $onbId);
                    return;
                } else {
                    Session::setFlash('error', 'Không thể khởi tạo quy trình.');
                }
            }
        }

        $templates = $onbModel->getAllTemplates(true);

        $this->view('layouts/header', ['pageTitle' => 'Khởi tạo Quy trình Hội nhập: ' . $employee->full_name]);
        $this->view('onboarding/start', [
            'employee'  => $employee,
            'templates' => $templates,
        ]);
        $this->view('layouts/footer');
    }

    /**
     * Xem tiến trình & checklist hội nhập của nhân viên
     * URL: /onboarding/show/{id hoặc employee_id}
     */
    public function show(int $param = 0): void
    {
        $this->checkPermission('employee.view');

        $onbModel = $this->model('Onboarding');
        
        // Kiểm tra xem $param là onboarding_id hay employee_id
        $onboarding = $onbModel->getOnboardingById($param);
        if (!$onboarding) {
            // Thử tìm theo employee_id
            $onboarding = $onbModel->getOnboardingByEmployee($param);
        }

        if (!$onboarding) {
            Session::setFlash('error', 'Không tìm thấy hồ sơ hội nhập.');
            $this->redirect('onboarding/dashboard');
            return;
        }

        $this->view('layouts/header', ['pageTitle' => 'Checklist Hội nhập: ' . $onboarding['full_name']]);
        $this->view('onboarding/show', [
            'onboarding' => $onboarding,
        ]);
        $this->view('layouts/footer');
    }

    /**
     * In Biên bản Bàn giao & Tiếp nhận Nhân sự mới (A4)
     * URL: /onboarding/printHandover/{id}
     */
    public function printHandover(int $id): void
    {
        $this->checkPermission('employee.view');

        $onbModel = $this->model('Onboarding');
        $onboarding = $onbModel->getOnboardingById($id);

        if (!$onboarding) {
            die('Không tìm thấy thông tin biên bản.');
        }

        $this->view('onboarding/print_handover', [
            'onboarding' => $onboarding,
        ]);
    }

    /**
     * Cập nhật trạng thái task hội nhập (Hỗ trợ AJAX & Form POST)
     * URL: POST /onboarding/updateTask/{item_id}
     */
    public function updateTask(int $itemId = 0): void
    {
        $this->checkPermission('employee.view');

        $itemId = $itemId > 0 ? $itemId : (int)$this->postData('item_id', 0);
        $status = $this->postData('status', 'Done');
        $notes  = $this->postData('notes');
        $userId = Session::get('user_id');

        $onbModel = $this->model('Onboarding');
        $success  = $onbModel->updateTaskItem($itemId, $status, $notes, $userId);

        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') 
               || strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false;

        if ($isAjax) {
            if ($success) {
                $this->json([
                    'success' => true,
                    'message' => 'Đã cập nhật trạng thái nhiệm vụ thành công!',
                    'status'  => $status,
                ]);
            } else {
                $this->json([
                    'success' => false,
                    'message' => 'Lỗi khi cập nhật nhiệm vụ.',
                ], 400);
            }
        }

        if ($success) {
            Session::setFlash('success', 'Đã cập nhật tiến độ công việc!');
        } else {
            Session::setFlash('error', 'Lỗi cập nhật tiến độ.');
        }

        $redirectUrl = $_SERVER['HTTP_REFERER'] ?? (BASE_URL . '/onboarding/dashboard');
        header('Location: ' . $redirectUrl);
        exit;
    }
}
