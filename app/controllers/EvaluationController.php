<?php
/**
 * ============================================================
 *  POSUNG HRIS – EvaluationController
 * ============================================================
 *  Phân hệ Quản lý Đánh giá Năng lực & KPI (Performance Management)
 *  Chu kỳ đánh giá, Mẫu & Tiêu chí, Chấm điểm trực quan, Radar Chart & BI
 * ============================================================
 */

require_once APP_ROOT . '/core/NotificationService.php';

class EvaluationController extends Controller
{
    /**
     * Dashboard danh sách các Chu kỳ đánh giá
     */
    public function index(): void
    {
        $this->checkPermission('employee.view');

        $evalModel = $this->model('Evaluation');

        $filters = [
            'status' => $this->getData('status', ''),
            'year'   => $this->getData('year', date('Y')),
            'search' => $this->getData('search', ''),
        ];

        $periods = $evalModel->getPeriods($filters);
        $stats   = $evalModel->getPeriodStats();

        $this->view('layouts/header', ['pageTitle' => 'Đánh giá Năng lực & KPI']);
        $this->view('evaluation/index', [
            'periods' => $periods,
            'stats'   => $stats,
            'filters' => $filters,
        ]);
        $this->view('layouts/footer');
    }

    /**
     * Quản lý Mẫu đánh giá (Templates) & Tiêu chí (Criteria)
     */
    public function templates(): void
    {
        $this->checkPermission('employee.edit');

        $evalModel = $this->model('Evaluation');
        $templates = $evalModel->getTemplates();

        // Lấy chi tiết các tiêu chí cho từng template
        foreach ($templates as &$tmpl) {
            $tmpl['criteria_list'] = $evalModel->getCriteriaByTemplate((int)$tmpl['id']);
        }

        $this->view('layouts/header', ['pageTitle' => 'Quản lý Mẫu & Tiêu chí Đánh giá']);
        $this->view('evaluation/templates', [
            'templates' => $templates,
        ]);
        $this->view('layouts/footer');
    }

    /**
     * Lưu (Thêm mới / Sửa) Mẫu đánh giá
     */
    public function saveTemplate(): void
    {
        $this->checkPermission('employee.edit');

        if (!$this->isPost()) {
            $this->redirect('evaluation/templates');
            return;
        }

        $evalModel = $this->model('Evaluation');
        $id = (int)$this->postData('id', 0);
        $name = $this->postData('name');

        if (empty($name)) {
            Session::setFlash('error', 'Vui lòng nhập tên mẫu đánh giá.');
            $this->redirect('evaluation/templates');
            return;
        }

        $data = [
            'name'        => $name,
            'description' => $this->postData('description'),
            'applies_to'  => $this->postData('applies_to', 'All'),
            'status'      => $this->postData('status', 'Active'),
        ];

        if ($id > 0) {
            $evalModel->updateTemplate($id, $data);
            Session::setFlash('success', 'Cập nhật mẫu đánh giá thành công!');
        } else {
            $id = $evalModel->createTemplate($data);
            Session::setFlash('success', 'Tạo mới mẫu đánh giá thành công!');
        }

        $this->redirect('evaluation/templates');
    }

    /**
     * Xóa Mẫu đánh giá
     */
    public function deleteTemplate(int $id = 0): void
    {
        $this->checkPermission('employee.delete');

        if (!$this->isPost()) {
            $this->redirect('evaluation/templates');
            return;
        }

        $evalModel = $this->model('Evaluation');
        if ($evalModel->deleteTemplate($id)) {
            Session::setFlash('success', 'Đã xóa mẫu đánh giá và các tiêu chí liên quan.');
        } else {
            Session::setFlash('error', 'Không thể xóa mẫu đánh giá này.');
        }

        $this->redirect('evaluation/templates');
    }

    /**
     * Lưu (Thêm/Sửa) Tiêu chí đánh giá qua AJAX
     */
    public function saveCriteria(): void
    {
        $this->checkPermission('employee.edit');

        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => 'Phương thức không hợp lệ.'], 405);
            return;
        }

        $templateId = (int)$this->postData('template_id');
        $name = $this->postData('name');

        if ($templateId <= 0 || empty($name)) {
            $this->json(['success' => false, 'message' => 'Vui lòng nhập tên tiêu chí.'], 400);
            return;
        }

        $evalModel = $this->model('Evaluation');
        $id = $evalModel->saveCriteria([
            'id'          => $this->postData('id'),
            'template_id' => $templateId,
            'name'        => $name,
            'category'    => $this->postData('category', 'KPI'),
            'weight'      => (int)$this->postData('weight', 20),
            'description' => $this->postData('description'),
            'sort_order'  => (int)$this->postData('sort_order', 1),
        ]);

        $this->json(['success' => true, 'message' => 'Lưu tiêu chí thành công!', 'id' => $id]);
    }

    /**
     * Xóa Tiêu chí đánh giá qua AJAX
     */
    public function deleteCriteria(int $id = 0): void
    {
        $this->checkPermission('employee.delete');

        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => 'Phương thức không hợp lệ.'], 405);
            return;
        }

        $evalModel = $this->model('Evaluation');
        if ($evalModel->deleteCriteria($id)) {
            $this->json(['success' => true, 'message' => 'Đã xóa tiêu chí.']);
        } else {
            $this->json(['success' => false, 'message' => 'Không thể xóa tiêu chí này.'], 400);
        }
    }

    /**
     * Form tạo Chu kỳ đánh giá mới
     */
    public function createPeriod(): void
    {
        $this->checkPermission('employee.edit');

        $evalModel = $this->model('Evaluation');
        $deptModel = $this->model('Department');

        $templates = $evalModel->getTemplates();
        $departments = $deptModel->all('dept_code ASC');

        $this->view('layouts/header', ['pageTitle' => 'Tạo Chu kỳ Đánh giá Mới']);
        $this->view('evaluation/create_period', [
            'templates'   => $templates,
            'departments' => $departments,
        ]);
        $this->view('layouts/footer');
    }

    /**
     * Lưu Chu kỳ đánh giá mới
     */
    public function storePeriod(): void
    {
        $this->checkPermission('employee.edit');

        if (!$this->isPost()) {
            $this->redirect('evaluation');
            return;
        }

        $name = $this->postData('name');
        $templateId = (int)$this->postData('template_id');

        if (empty($name) || $templateId <= 0) {
            Session::setFlash('error', 'Vui lòng nhập tên chu kỳ và chọn mẫu đánh giá.');
            $this->redirect('evaluation/createPeriod');
            return;
        }

        $evalModel = $this->model('Evaluation');
        $periodId = $evalModel->createPeriod([
            'name'              => $name,
            'template_id'       => $templateId,
            'start_date'        => $this->postData('start_date'),
            'end_date'          => $this->postData('end_date'),
            'department_id'     => $this->postData('department_id'),
            'allow_self_review' => (int)$this->postData('allow_self_review', 1),
            'allow_peer_review' => (int)$this->postData('allow_peer_review', 1),
            'peer_review_count' => (int)$this->postData('peer_review_count', 2),
            'status'            => $this->postData('status', 'Active'),
            'notes'             => $this->postData('notes'),
        ]);

        Session::setFlash('success', 'Đã khởi tạo chu kỳ đánh giá mới thành công!');
        $this->redirect("evaluation/show/{$periodId}");
    }

    /**
     * Xem danh sách nhân viên trong Chu kỳ để thực hiện đánh giá
     */
    public function show(int $periodId = 0): void
    {
        $this->checkPermission('employee.view');

        $evalModel = $this->model('Evaluation');
        $deptModel = $this->model('Department');

        $period = $evalModel->getPeriodById($periodId);
        if (!$period) {
            Session::setFlash('error', 'Không tìm thấy chu kỳ đánh giá.');
            $this->redirect('evaluation');
            return;
        }

        $filters = [
            'dept_id' => $this->getData('dept', ''),
            'grade'   => $this->getData('grade', ''),
            'status'  => $this->getData('status', ''),
            'search'  => $this->getData('search', ''),
        ];

        $employees = $evalModel->getEmployeesForPeriod($periodId, $filters);
        $departments = $deptModel->all('dept_code ASC');

        $this->view('layouts/header', ['pageTitle' => 'Chi tiết Chu kỳ: ' . $period->name]);
        $this->view('evaluation/show', [
            'period'      => $period,
            'employees'   => $employees,
            'departments' => $departments,
            'filters'     => $filters,
        ]);
        $this->view('layouts/footer');
    }

    /**
     * Form chấm điểm đánh giá trực quan (thang điểm 1-5 / sao / thanh trượt)
     */
    public function evaluate(int $periodId = 0, int $employeeId = 0): void
    {
        $this->checkPermission('employee.edit');

        $evalModel = $this->model('Evaluation');
        $evalData = $evalModel->getEvaluationData($periodId, $employeeId);

        if (!$evalData) {
            Session::setFlash('error', 'Không tìm thấy thông tin đánh giá cho nhân viên này.');
            $this->redirect('evaluation');
            return;
        }

        $this->view('layouts/header', ['pageTitle' => 'Chấm điểm Đánh giá: ' . $evalData['employee']->full_name]);
        $this->view('evaluation/evaluate', $evalData);
        $this->view('layouts/footer');
    }

    /**
     * Lưu kết quả chấm điểm đánh giá
     */
    public function saveEvaluation(): void
    {
        $this->checkPermission('employee.edit');

        if (!$this->isPost()) {
            $this->redirect('evaluation');
            return;
        }

        $periodId = (int)$this->postData('period_id');
        $employeeId = (int)$this->postData('employee_id');

        if ($periodId <= 0 || $employeeId <= 0) {
            Session::setFlash('error', 'Dữ liệu không hợp lệ.');
            $this->redirect('evaluation');
            return;
        }

        $evalModel = $this->model('Evaluation');
        $evaluatorId = Session::userId();

        $scores = $_POST['scores'] ?? [];
        $comments = $_POST['comments'] ?? [];

        $evalModel->saveEvaluationScores($periodId, $employeeId, [
            'scores'       => $scores,
            'comments'     => $comments,
            'eval_role'    => $this->postData('eval_role', 'manager'),
            'strengths'    => $this->postData('strengths'),
            'improvements' => $this->postData('improvements'),
            'notes'        => $this->postData('notes'),
        ], $evaluatorId);

        Session::setFlash('success', 'Đã lưu và cập nhật kết quả đánh giá thành công!');
        $this->redirect("evaluation/show/{$periodId}");
    }

    /**
     * Báo cáo Tổng hợp Chu kỳ Đánh giá (Department breakdown, Radar chart, Grade distribution)
     */
    public function summary(int $periodId = 0): void
    {
        $this->checkPermission('employee.view');

        $evalModel = $this->model('Evaluation');
        $summaryData = $evalModel->getPeriodSummary($periodId);

        if (empty($summaryData)) {
            Session::setFlash('error', 'Không tìm thấy báo cáo cho chu kỳ này.');
            $this->redirect('evaluation');
            return;
        }

        $this->view('layouts/header', ['pageTitle' => 'Báo cáo Tổng kết: ' . $summaryData['period']->name]);
        $this->view('evaluation/summary', $summaryData);
        $this->view('layouts/footer');
    }

    /**
     * Lịch sử đánh giá của nhân viên (phục vụ AJAX hoặc Profile 360°)
     */
    public function employeeHistory(int $employeeId = 0): void
    {
        $this->checkPermission('employee.view');

        if ($employeeId <= 0) {
            $this->json(['success' => false, 'message' => 'ID nhân viên không hợp lệ.'], 400);
            return;
        }

        $evalModel = $this->model('Evaluation');
        $evaluations = $evalModel->getByEmployee($employeeId);

        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) || $this->getData('format') === 'json') {
            $this->json(['success' => true, 'data' => $evaluations]);
            return;
        }

        $this->view('evaluation/employee_history', [
            'evaluations' => $evaluations,
            'employeeId'  => $employeeId,
        ]);
    }

    // ══════════════════════════════════════════════════════════
    //  PHÂN HỆ NÂNG CẤP: PERFORMANCE 360° VỚI GOALS / KRA
    // ══════════════════════════════════════════════════════════

    /**
     * Quản lý mục tiêu KRA cho chu kỳ
     * GET /evaluation/goals/{period_id}
     */
    public function goals(int $periodId = 0): void
    {
        $this->checkPermission('employee.view');

        $evalModel = $this->model('Evaluation');
        $deptModel = $this->model('Department');

        if ($periodId <= 0) {
            $db = Database::getInstance();
            $db->query("SELECT id FROM evaluation_periods WHERE status = 'Active' ORDER BY start_date DESC LIMIT 1");
            $p = $db->fetch();
            if ($p) {
                $periodId = (int)$p['id'];
            } else {
                $db->query("SELECT id FROM evaluation_periods ORDER BY id DESC LIMIT 1");
                $p = $db->fetch();
                $periodId = $p ? (int)$p['id'] : 0;
            }
        }

        $period = $evalModel->getPeriodById($periodId);
        if (!$period) {
            Session::setFlash('error', 'Không tìm thấy chu kỳ đánh giá.');
            $this->redirect('evaluation');
            return;
        }

        $filters = [
            'dept_id' => $this->getData('dept', ''),
            'status'  => $this->getData('status', ''),
            'search'  => $this->getData('search', ''),
        ];

        $employees = $evalModel->getGoalsSummaryByPeriod($periodId, $filters);
        $departments = $deptModel->all('dept_code ASC');
        $allPeriods = $evalModel->getPeriods(['status' => '']);

        $this->view('layouts/header', ['pageTitle' => 'Quản lý Mục tiêu KRA: ' . $period->name]);
        $this->view('evaluation/goals', [
            'period'      => $period,
            'employees'   => $employees,
            'departments' => $departments,
            'filters'     => $filters,
            'allPeriods'  => $allPeriods,
        ]);
        $this->view('layouts/footer');
    }

    /**
     * NV / Quản lý thiết lập mục tiêu KRA + trọng số (Bắt buộc tổng = 100%)
     * GET/POST /evaluation/setGoals/{period_id}/{employee_id}
     */
    public function setGoals(int $periodId = 0, int $employeeId = 0): void
    {
        $evalModel = $this->model('Evaluation');
        $empModel = $this->model('Employee');

        $loggedInEmpId = Session::employeeId();
        $isSelf = ($loggedInEmpId && $loggedInEmpId === $employeeId);

        // Kiểm tra quyền: Tự đặt goals của mình HOẶC có quyền 'employee.edit' / Admin
        if (!$isSelf && !Session::hasPermission('employee.edit') && !Session::isAdmin()) {
            Session::setFlash('error', 'Bạn không có quyền thiết lập mục tiêu cho nhân viên này.');
            $this->redirect('evaluation');
            return;
        }

        $period = $evalModel->getPeriodById($periodId);
        if (!$period) {
            Session::setFlash('error', 'Không tìm thấy chu kỳ đánh giá.');
            $this->redirect('evaluation');
            return;
        }

        $employee = $empModel->getById($employeeId);
        if (!$employee) {
            Session::setFlash('error', 'Không tìm thấy nhân viên.');
            $this->redirect("evaluation/goals/{$periodId}");
            return;
        }

        if ($this->isPost()) {
            $goalsData = $_POST['goals'] ?? [];
            $action = $this->postData('action', 'submit'); // 'draft' or 'submit'
            $status = ($action === 'draft') ? 'Draft' : 'Submitted';

            // Server-side check tổng trọng số = 100%
            $result = $evalModel->saveGoals($periodId, $employeeId, $goalsData, $status);

            if (!$result['success']) {
                Session::setFlash('error', $result['message']);
                $this->redirect("evaluation/setGoals/{$periodId}/{$employeeId}");
                return;
            }

            // Gửi thông báo cho Quản lý trực tiếp nếu nhân viên nộp mục tiêu
            if ($status === 'Submitted' && !empty($employee->direct_manager_id)) {
                NotificationService::sendToEmployee(
                    (int)$employee->direct_manager_id,
                    'reminder',
                    'Mục tiêu KRA cần duyệt',
                    "Nhân viên {$employee->full_name} đã gửi thiết lập mục tiêu KRA cho chu kỳ {$period->name}.",
                    BASE_URL . "/evaluation/setGoals/{$periodId}/{$employeeId}",
                    true
                );
            }

            Session::setFlash('success', ($status === 'Submitted') 
                ? 'Đã gửi thiết lập mục tiêu KRA thành công!' 
                : 'Đã lưu bản nháp mục tiêu KRA thành công.');

            if ($isSelf && str_contains($_SERVER['HTTP_REFERER'] ?? '', '/ess/')) {
                $this->redirect('ess/performance');
            } else {
                $this->redirect("evaluation/goals/{$periodId}");
            }
            return;
        }

        $currentGoals = $evalModel->getGoals($periodId, $employeeId);

        $this->view('layouts/header', ['pageTitle' => 'Thiết lập Mục tiêu KRA - ' . $employee->full_name]);
        $this->view('evaluation/set_goals', [
            'period'       => $period,
            'employee'     => $employee,
            'currentGoals' => $currentGoals,
            'isSelf'       => $isSelf,
        ]);
        $this->view('layouts/footer');
    }

    /**
     * Nhân viên tự đánh giá Goals và kết quả thực tế (Self Review)
     * GET/POST /evaluation/submitSelfReview/{period_id}
     */
    public function submitSelfReview(int $periodId = 0, int $employeeId = 0): void
    {
        $evalModel = $this->model('Evaluation');
        $empModel = $this->model('Employee');

        $loggedInEmpId = Session::employeeId();
        if ($employeeId <= 0) {
            $employeeId = (int)$loggedInEmpId;
        }

        $isSelf = ($loggedInEmpId && $loggedInEmpId === $employeeId);
        if (!$isSelf && !Session::hasPermission('employee.edit') && !Session::isAdmin()) {
            Session::setFlash('error', 'Bạn không có quyền thực hiện tự đánh giá cho nhân viên này.');
            $this->redirect('evaluation');
            return;
        }

        $period = $evalModel->getPeriodById($periodId);
        if (!$period) {
            Session::setFlash('error', 'Không tìm thấy chu kỳ đánh giá.');
            $this->redirect('evaluation');
            return;
        }

        if (empty($period->allow_self_review)) {
            Session::setFlash('error', 'Chu kỳ đánh giá này không áp dụng tự đánh giá (Self Review).');
            $this->redirect("evaluation/goals/{$periodId}");
            return;
        }

        $employee = $empModel->getById($employeeId);
        if (!$employee) {
            Session::setFlash('error', 'Không tìm thấy nhân viên.');
            $this->redirect('evaluation');
            return;
        }

        $goals = $evalModel->getGoals($periodId, $employeeId);
        if (empty($goals)) {
            Session::setFlash('warning', 'Bạn chưa thiết lập mục tiêu KRA cho chu kỳ này. Vui lòng đặt mục tiêu trước khi tự đánh giá.');
            $this->redirect("evaluation/setGoals/{$periodId}/{$employeeId}");
            return;
        }

        if ($this->isPost()) {
            $achievements = $_POST['actual_achievement'] ?? [];
            $selfScores = $_POST['self_score'] ?? [];
            $overallScore = $this->postData('overall_score');
            $strengths = $this->postData('strengths');
            $improvements = $this->postData('improvements');
            $comments = $this->postData('comments');

            $evalModel->submitSelfReviewGoals($periodId, $employeeId, [
                'achievements'  => $achievements,
                'self_scores'   => $selfScores,
                'overall_score' => $overallScore,
                'strengths'     => $strengths,
                'improvements'  => $improvements,
                'comments'      => $comments,
            ]);

            // Thông báo Quản lý trực tiếp
            if (!empty($employee->direct_manager_id)) {
                NotificationService::sendToEmployee(
                    (int)$employee->direct_manager_id,
                    'reminder',
                    'Tự đánh giá hoàn tất',
                    "Nhân viên {$employee->full_name} đã hoàn tất tự đánh giá 360° cho chu kỳ {$period->name}.",
                    BASE_URL . "/evaluation/review360/{$periodId}/{$employeeId}",
                    true
                );
            }

            Session::setFlash('success', 'Bạn đã hoàn tất nộp phiếu tự đánh giá 360° thành công!');

            if ($isSelf && str_contains($_SERVER['HTTP_REFERER'] ?? '', '/ess/')) {
                $this->redirect('ess/performance');
            } else {
                $this->redirect("evaluation/goals/{$periodId}");
            }
            return;
        }

        $existingSelfReview = $evalModel->getReview360($periodId, $employeeId, $employeeId);

        $this->view('layouts/header', ['pageTitle' => 'Tự Đánh Giá 360° - ' . $employee->full_name]);
        $this->view('evaluation/self_review', [
            'period'             => $period,
            'employee'           => $employee,
            'goals'              => $goals,
            'existingSelfReview' => $existingSelfReview,
            'isSelf'             => $isSelf,
        ]);
        $this->view('layouts/footer');
    }

    /**
     * Form review 360° (Quản lý / Đồng nghiệp Peer / Cấp dưới đánh giá)
     * GET/POST /evaluation/review360/{period_id}/{employee_id}
     */
    public function review360(int $periodId = 0, int $employeeId = 0): void
    {
        $evalModel = $this->model('Evaluation');
        $empModel = $this->model('Employee');

        $period = $evalModel->getPeriodById($periodId);
        if (!$period) {
            Session::setFlash('error', 'Không tìm thấy chu kỳ đánh giá.');
            $this->redirect('evaluation');
            return;
        }

        $employee = $empModel->getById($employeeId);
        if (!$employee) {
            Session::setFlash('error', 'Không tìm thấy nhân viên.');
            $this->redirect("evaluation/goals/{$periodId}");
            return;
        }

        $reviewerEmpId = Session::employeeId();
        $isManager = ($reviewerEmpId && $reviewerEmpId == $employee->direct_manager_id) || Session::isAdmin() || Session::hasPermission('employee.edit');

        // Xác định relationship
        $assignedReview = null;
        if ($reviewerEmpId) {
            $assignedReview = $evalModel->getReview360($periodId, $employeeId, $reviewerEmpId);
        }

        $relationship = 'Peer';
        if ($reviewerEmpId == $employee->direct_manager_id || ($isManager && !$assignedReview)) {
            $relationship = 'Manager';
        } elseif ($assignedReview) {
            $relationship = $assignedReview->relationship;
        }

        // Kiểm tra quyền đánh giá
        if (!$isManager && !$assignedReview) {
            Session::setFlash('error', 'Bạn không được phân công đánh giá cho nhân viên này.');
            $this->redirect('evaluation');
            return;
        }

        if ($this->isPost()) {
            $kraScores = $_POST['kra_scores'] ?? [];
            $overallScore = $this->postData('overall_score');
            $strengths = $this->postData('strengths');
            $improvements = $this->postData('improvements');
            $comments = $this->postData('comments');
            $subRelationship = $this->postData('relationship', $relationship);

            $reviewerIdToSave = $reviewerEmpId ?: Session::userId();

            $evalModel->saveReview360($periodId, $employeeId, $reviewerIdToSave, $subRelationship, [
                'overall_score' => $overallScore,
                'strengths'     => $strengths,
                'improvements'  => $improvements,
                'comments'      => $comments,
                'kra_scores'    => $kraScores,
                'status'        => 'Submitted',
            ]);

            // Thông báo
            NotificationService::sendToEmployee(
                $employeeId,
                'system',
                'Nhận được đánh giá 360°',
                "Bạn vừa nhận được một đánh giá từ vai trò [{$subRelationship}] trong chu kỳ {$period->name}.",
                BASE_URL . "/evaluation/finalScore/{$periodId}/{$employeeId}",
                true
            );

            Session::setFlash('success', 'Đã lưu và gửi phiếu đánh giá 360° thành công!');
            if (str_contains($_SERVER['HTTP_REFERER'] ?? '', '/ess/')) {
                $this->redirect('ess/performance');
            } else {
                $this->redirect("evaluation/goals/{$periodId}");
            }
            return;
        }

        $goals = $evalModel->getGoals($periodId, $employeeId);
        $selfReview = $evalModel->getReview360($periodId, $employeeId, $employeeId);
        $existingReview = $reviewerEmpId ? $evalModel->getReview360($periodId, $employeeId, $reviewerEmpId) : null;

        $this->view('layouts/header', ['pageTitle' => 'Đánh giá 360°: ' . $employee->full_name]);
        $this->view('evaluation/review_360', [
            'period'         => $period,
            'employee'       => $employee,
            'goals'          => $goals,
            'selfReview'     => $selfReview,
            'existingReview' => $existingReview,
            'relationship'   => $relationship,
            'isManager'      => $isManager,
        ]);
        $this->view('layouts/footer');
    }

    /**
     * HR chỉ định Peer Reviewers
     * GET/POST /evaluation/assignPeerReviewers/{period_id}
     */
    public function assignPeerReviewers(int $periodId = 0): void
    {
        $this->checkPermission('employee.edit');

        $evalModel = $this->model('Evaluation');
        $deptModel = $this->model('Department');
        $empModel = $this->model('Employee');

        $period = $evalModel->getPeriodById($periodId);
        if (!$period) {
            Session::setFlash('error', 'Không tìm thấy chu kỳ đánh giá.');
            $this->redirect('evaluation');
            return;
        }

        if (empty($period->allow_peer_review)) {
            Session::setFlash('warning', 'Chu kỳ này đang tắt tính năng Peer Review. Hãy bật tính năng trong cấu hình chu kỳ.');
        }

        if ($this->isPost()) {
            $employeeId = (int)$this->postData('employee_id');
            $peerIds = $this->postData('peer_ids', []);

            if ($employeeId <= 0) {
                if ($this->isAjax()) {
                    $this->json(['success' => false, 'message' => 'Nhân viên không hợp lệ.'], 400);
                    return;
                }
                Session::setFlash('error', 'Dữ liệu không hợp lệ.');
                $this->redirect("evaluation/assignPeerReviewers/{$periodId}");
                return;
            }

            $evalModel->assignPeerReviewers($periodId, $employeeId, $peerIds);

            // Gửi thông báo cho từng peer reviewer được phân công
            $targetEmp = $empModel->getById($employeeId);
            $targetName = $targetEmp ? $targetEmp->full_name : 'đồng nghiệp';

            foreach ($peerIds as $pId) {
                NotificationService::sendToEmployee(
                    (int)$pId,
                    'reminder',
                    'Phân công đánh giá đồng nghiệp 360°',
                    "Bạn được phân công đánh giá chéo cho nhân viên {$targetName} trong chu kỳ {$period->name}.",
                    BASE_URL . "/evaluation/review360/{$periodId}/{$employeeId}",
                    true
                );
            }

            if ($this->isAjax()) {
                $this->json(['success' => true, 'message' => 'Đã lưu phân công Peer Reviewers thành công!']);
                return;
            }

            Session::setFlash('success', 'Đã lưu và gửi thông báo phân công Peer Reviewers thành công!');
            $this->redirect("evaluation/assignPeerReviewers/{$periodId}");
            return;
        }

        $filters = [
            'dept_id' => $this->getData('dept', ''),
            'search'  => $this->getData('search', ''),
        ];

        $employees = $evalModel->getGoalsSummaryByPeriod($periodId, $filters);
        
        // Lấy danh sách tất cả đồng nghiệp đang làm việc để chọn peer reviewer
        $allColleagues = $empModel->all("status != 'Resigned'", 'full_name ASC');
        $departments = $deptModel->all('dept_code ASC');

        // Lấy danh sách peer reviews chi tiết cho từng nhân viên
        $db = Database::getInstance();
        foreach ($employees as &$e) {
            $db->query(
                "SELECT r.id, r.reviewer_id, r.status, r.overall_score, emp.full_name AS reviewer_name, emp.employee_code AS reviewer_code
                 FROM performance_reviews_360 r
                 JOIN employees emp ON r.reviewer_id = emp.id
                 WHERE r.period_id = :pid AND r.employee_id = :eid AND r.relationship = 'Peer'",
                ['pid' => $periodId, 'eid' => $e['id']]
            );
            $e['assigned_peers'] = $db->fetchAll();
        }

        $this->view('layouts/header', ['pageTitle' => 'Phân công Đồng nghiệp Đánh giá (Peer Review) - ' . $period->name]);
        $this->view('evaluation/assign_peer', [
            'period'        => $period,
            'employees'     => $employees,
            'departments'   => $departments,
            'allColleagues' => $allColleagues,
            'filters'       => $filters,
        ]);
        $this->view('layouts/footer');
    }

    /**
     * Manager chốt điểm cuối (Weighted Average) & Radar Chart
     * GET/POST /evaluation/finalScore/{period_id}/{employee_id}
     */
    public function finalScore(int $periodId = 0, int $employeeId = 0): void
    {
        $this->checkPermission('employee.edit');

        $evalModel = $this->model('Evaluation');

        $comparisonData = $evalModel->get360ComparisonData($periodId, $employeeId);
        if (!$comparisonData) {
            Session::setFlash('error', 'Không tìm thấy dữ liệu đánh giá 360° cho nhân viên này.');
            $this->redirect('evaluation');
            return;
        }

        if ($this->isPost()) {
            $goalFinalScores = $_POST['goal_final_score'] ?? [];
            $finalScore = (float)$this->postData('final_score', 0);
            $overallGrade = $this->postData('overall_grade');
            $strengths = $this->postData('strengths');
            $improvements = $this->postData('improvements');
            $notes = $this->postData('notes');

            $evalModel->saveFinalScore($periodId, $employeeId, [
                'goal_final_scores' => $goalFinalScores,
                'final_score'       => $finalScore,
                'overall_grade'     => $overallGrade,
                'strengths'         => $strengths,
                'improvements'      => $improvements,
                'notes'             => $notes,
            ], Session::userId());

            // Gửi thông báo kết quả cho nhân viên
            NotificationService::sendToEmployee(
                $employeeId,
                'system',
                'Kết quả đánh giá 360° đã chốt',
                "Đánh giá hiệu suất của bạn trong chu kỳ {$comparisonData['period']->name} đã được chốt với điểm số: {$finalScore} (Hạng {$overallGrade}).",
                BASE_URL . "/evaluation/finalScore/{$periodId}/{$employeeId}",
                false
            );

            Session::setFlash('success', "Đã chốt và phê duyệt kết quả đánh giá 360° thành công cho nhân viên {$comparisonData['employee']->full_name}!");
            $this->redirect("evaluation/goals/{$periodId}");
            return;
        }

        $this->view('layouts/header', ['pageTitle' => 'Chốt điểm Đánh giá 360°: ' . $comparisonData['employee']->full_name]);
        $this->view('evaluation/final_score', $comparisonData);
        $this->view('layouts/footer');
    }

    /**
     * Dashboard Phân tích 360° (Phân bố điểm Bell Curve, Top performers, Radar chart)
     * GET /evaluation/analytics/{period_id}
     */
    public function analytics(int $periodId = 0): void
    {
        $this->checkPermission('employee.view');

        $evalModel = $this->model('Evaluation');

        if ($periodId <= 0) {
            $db = Database::getInstance();
            $db->query("SELECT id FROM evaluation_periods ORDER BY id DESC LIMIT 1");
            $p = $db->fetch();
            $periodId = $p ? (int)$p['id'] : 0;
        }

        $analyticsData = $evalModel->get360Analytics($periodId);
        if (empty($analyticsData)) {
            Session::setFlash('error', 'Không tìm thấy dữ liệu phân tích cho chu kỳ này.');
            $this->redirect('evaluation');
            return;
        }

        $allPeriods = $evalModel->getPeriods(['status' => '']);

        $this->view('layouts/header', ['pageTitle' => 'Phân tích & Thống kê 360° - ' . $analyticsData['period']->name]);
        $this->view('evaluation/analytics', [
            'analytics'  => $analyticsData,
            'allPeriods' => $allPeriods,
        ]);
        $this->view('layouts/footer');
    }
}
