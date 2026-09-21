<?php
/**
 * ============================================================
 *  POSUNG HRIS – RecruitmentController
 * ============================================================
 *  Quản lý Tuyển dụng: Yêu cầu, Ứng viên, Phỏng vấn,
 *  Chuyển đổi thành Nhân viên, Thống kê.
 * ============================================================
 */

class RecruitmentController extends Controller
{
    /**
     * Dashboard Tuyển dụng
     */
    public function index(): void
    {
        Session::checkPermission(['Admin', 'HR_Manager', 'Project_Manager']);

        $model = $this->model('Recruitment');

        $statusFilter = $this->getData('status', '');
        $searchFilter = $this->getData('search', '');
        $requests = $model->getRequests([
            'status' => $statusFilter,
            'search' => $searchFilter,
        ]);

        // Đếm nhanh
        $db = Database::getInstance();
        $db->query("SELECT status, COUNT(*) as cnt FROM recruitment_requests GROUP BY status");
        $statusCounts = [];
        foreach ($db->fetchAll() as $r) $statusCounts[$r['status']] = (int)$r['cnt'];

        $db->query("SELECT COUNT(*) as cnt FROM candidates WHERE status IN ('New','Screening')");
        $newCandidates = $db->fetch()['cnt'] ?? 0;

        $this->view('layouts/header', ['pageTitle' => 'Quản lý Tuyển dụng']);
        $this->view('recruitment/index', [
            'requests'       => $requests,
            'statusCounts'   => $statusCounts,
            'newCandidates'  => $newCandidates,
            'currentStatus'  => $statusFilter,
            'searchFilter'   => $searchFilter,
        ]);
        $this->view('layouts/footer');
    }

    /**
     * Form tạo yêu cầu tuyển dụng
     */
    public function createRequest(): void
    {
        Session::checkPermission(['Admin', 'HR_Manager', 'Project_Manager']);

        $db = Database::getInstance();
        $db->query("SELECT id, dept_name, dept_code FROM departments WHERE status = 'Active' ORDER BY dept_code");
        $departments = $db->fetchAll();

        $db->query("SELECT id, pos_title, pos_code FROM positions ORDER BY pos_code");
        $positions = $db->fetchAll();

        $projectModel = $this->model('Project');
        $projects = $projectModel->getActiveProjects();

        $this->view('layouts/header', ['pageTitle' => 'Tạo Yêu cầu Tuyển dụng']);
        $this->view('recruitment/create_request', [
            'departments' => $departments,
            'positions'   => $positions,
            'projects'    => $projects,
        ]);
        $this->view('layouts/footer');
    }

    /**
     * Lưu yêu cầu tuyển dụng
     */
    public function storeRequest(): void
    {
        Session::checkPermission(['Admin', 'HR_Manager', 'Project_Manager']);

        if ($this->isPost()) {
            $data = [
                'department_id'    => $this->postData('department_id') ?: null,
                'position_id'      => $this->postData('position_id') ?: null,
                'project_id'       => $this->postData('project_id') ?: null,
                'quantity'         => (int)$this->postData('quantity', 1),
                'reason'           => $this->postData('reason', 'Expansion'),
                'urgency'          => $this->postData('urgency', 'Normal'),
                'description'      => $this->postData('description'),
                'requirements'     => $this->postData('requirements'),
                'salary_range_from'=> $this->postData('salary_range_from') ?: null,
                'salary_range_to'  => $this->postData('salary_range_to') ?: null,
                'benefits'         => $this->postData('benefits'),
                'work_location'    => $this->postData('work_location'),
                'deadline'         => $this->postData('deadline') ?: null,
                'requested_by'     => Session::userId(),
                'status'           => $this->postData('request_status', 'Pending'),
                'notes'            => $this->postData('notes'),
            ];

            $model = $this->model('Recruitment');
            $id = $model->createRequest($data);

            if ($id) {
                Session::setFlash('success', 'Đã tạo Yêu cầu Tuyển dụng thành công!');
            } else {
                Session::setFlash('error', 'Lỗi khi tạo yêu cầu tuyển dụng.');
            }
            $this->redirect('recruitment');
        }
    }

    /**
     * Phê duyệt yêu cầu tuyển dụng
     */
    public function approveRequest(int $id = 0): void
    {
        Session::checkPermission(['Admin', 'HR_Manager']);
        $model = $this->model('Recruitment');
        if ($model->approveRequest($id, Session::userId())) {
            Session::setFlash('success', 'Đã phê duyệt yêu cầu tuyển dụng!');
        } else {
            Session::setFlash('error', 'Không thể phê duyệt.');
        }
        $this->redirect('recruitment');
    }

    /**
     * Danh sách & Form thêm ứng viên
     */
    public function candidates(): void
    {
        Session::checkPermission(['Admin', 'HR_Manager']);

        $model = $this->model('Recruitment');
        $filters = [
            'request_id' => (int)$this->getData('request_id', 0),
            'status'     => $this->getData('status', ''),
            'search'     => $this->getData('search', ''),
        ];
        $candidates = $model->getCandidates($filters);

        // Lấy danh sách yêu cầu tuyển dụng (cho dropdown)
        $requests = $model->getRequests(['status' => 'In_Progress']);
        $approvedRequests = $model->getRequests(['status' => 'Approved']);
        $requests = array_merge($requests, $approvedRequests);

        $this->view('layouts/header', ['pageTitle' => 'Quản lý Ứng viên']);
        $this->view('recruitment/candidates', [
            'candidates' => $candidates,
            'requests'   => $requests,
            'filters'    => $filters,
        ]);
        $this->view('layouts/footer');
    }

    /**
     * Form thêm ứng viên
     */
    public function addCandidate(): void
    {
        Session::checkPermission(['Admin', 'HR_Manager']);

        $model = $this->model('Recruitment');
        $requests = $model->getRequests();

        $this->view('layouts/header', ['pageTitle' => 'Thêm Ứng viên']);
        $this->view('recruitment/add_candidate', [
            'requests' => $requests,
            'preselectedRequest' => (int)$this->getData('request_id', 0),
        ]);
        $this->view('layouts/footer');
    }

    /**
     * Lưu ứng viên mới
     */
    public function storeCandidate(): void
    {
        Session::checkPermission(['Admin', 'HR_Manager']);

        if ($this->isPost()) {
            $data = [
                'request_id'       => $this->postData('request_id') ?: null,
                'full_name'        => $this->postData('full_name'),
                'dob'              => $this->postData('dob') ?: null,
                'gender'           => $this->postData('gender', 'Male'),
                'phone'            => $this->postData('phone'),
                'email'            => $this->postData('email'),
                'address'          => $this->postData('address'),
                'id_card'          => $this->postData('id_card'),
                'highest_degree'   => $this->postData('highest_degree'),
                'major'            => $this->postData('major'),
                'university'       => $this->postData('university'),
                'graduation_year'  => $this->postData('graduation_year') ?: null,
                'years_experience' => (int)$this->postData('years_experience', 0),
                'current_company'  => $this->postData('current_company'),
                'current_position' => $this->postData('current_position'),
                'expected_salary'  => $this->postData('expected_salary') ?: null,
                'source'           => $this->postData('source'),
                'skills'           => $this->postData('skills'),
                'languages'        => $this->postData('languages'),
                'notes'            => $this->postData('notes'),
            ];

            // Upload CV
            if (!empty($_FILES['cv_file']['name']) && $_FILES['cv_file']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../../public/uploads/cvs/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                $ext = strtolower(pathinfo($_FILES['cv_file']['name'], PATHINFO_EXTENSION));
                $newName = 'CV_' . preg_replace('/[^a-zA-Z0-9]/', '_', $data['full_name']) . '_' . time() . '.' . $ext;
                if (move_uploaded_file($_FILES['cv_file']['tmp_name'], $uploadDir . $newName)) {
                    $data['cv_file_path'] = 'uploads/cvs/' . $newName;
                }
            }

            $model = $this->model('Recruitment');
            $id = $model->addCandidate($data);

            if ($id) {
                Session::setFlash('success', 'Đã thêm Ứng viên thành công!');
            } else {
                Session::setFlash('error', 'Lỗi khi thêm ứng viên.');
            }
            $this->redirect('recruitment/candidates' . ($data['request_id'] ? '?request_id=' . $data['request_id'] : ''));
        }
    }

    /**
     * Cập nhật phỏng vấn / trạng thái ứng viên
     */
    public function interview(int $id = 0): void
    {
        Session::checkPermission(['Admin', 'HR_Manager']);

        $model = $this->model('Recruitment');

        if ($this->isPost()) {
            $newStatus = $this->postData('new_status');
            $extra = [
                'interview_date'     => $this->postData('interview_date') ?: null,
                'interview_location' => $this->postData('interview_location'),
                'interview_result'   => $this->postData('interview_result'),
                'interview_score'    => $this->postData('interview_score') ?: null,
                'interviewer_name'   => $this->postData('interviewer_name'),
                'interviewer_notes'  => $this->postData('interviewer_notes'),
                'final_decision'     => $this->postData('final_decision'),
                'offer_salary'       => $this->postData('offer_salary') ?: null,
                'offer_date'         => $this->postData('offer_date') ?: null,
                'start_date'         => $this->postData('start_date') ?: null,
                'rejection_reason'   => $this->postData('rejection_reason'),
            ];

            if ($model->updateCandidateStatus($id, $newStatus, $extra)) {
                Session::setFlash('success', 'Đã cập nhật trạng thái ứng viên!');
            } else {
                Session::setFlash('error', 'Lỗi cập nhật.');
            }
            $this->redirect('recruitment/candidates');
            return;
        }

        // GET: Hiển thị form phỏng vấn
        $candidate = $model->getCandidateById($id);
        if (!$candidate) {
            Session::setFlash('error', 'Không tìm thấy ứng viên.');
            $this->redirect('recruitment/candidates');
            return;
        }

        $this->view('layouts/header', ['pageTitle' => 'Phỏng vấn: ' . $candidate->full_name]);
        $this->view('recruitment/interview', ['candidate' => $candidate]);
        $this->view('layouts/footer');
    }

    /**
     * Chuyển ứng viên thành nhân viên (Hire)
     */
    public function hire(int $id = 0): void
    {
        Session::checkPermission(['Admin', 'HR_Manager']);

        $model = $this->model('Recruitment');
        $empId = $model->convertToEmployee($id);

        if ($empId) {
            Session::setFlash('success', "Đã chuyển Ứng viên thành Nhân viên thành công! Mã NV mới đã được cấp.");
            $this->redirect("employee/detail/{$empId}");
        } else {
            Session::setFlash('error', 'Không thể chuyển đổi (Ứng viên chưa có Offer hoặc lỗi hệ thống).');
            $this->redirect('recruitment/candidates');
        }
    }

    /**
     * In thư mời nhận việc
     */
    public function printOffer(int $id = 0): void
    {
        Session::checkPermission(['Admin', 'HR_Manager']);

        $model = $this->model('Recruitment');
        $candidate = $model->getCandidateById($id);

        if (!$candidate) {
            die('Không tìm thấy ứng viên.');
        }

        $this->view('recruitment/print_offer', ['candidate' => $candidate]);
    }

    /**
     * Cập nhật trạng thái ứng viên qua AJAX (Kanban Drag & Drop)
     */
    public function updateStatus(): void
    {
        Session::checkPermission(['Admin', 'HR_Manager']);
        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => 'Invalid method'], 405);
        }

        $id = (int)$this->postData('id', 0);
        $status = $this->postData('status');

        if ($id <= 0 || !$status) {
            $this->json(['success' => false, 'message' => 'Thiếu dữ liệu.'], 400);
        }

        $model = $this->model('Recruitment');
        $candidate = $model->getCandidateById($id);
        
        if (!$candidate) {
            $this->json(['success' => false, 'message' => 'Không tìm thấy ứng viên.'], 404);
        }

        if ($candidate->is_blacklisted && $status === 'offered') {
            $this->json(['success' => false, 'message' => 'Ứng viên nằm trong danh sách đen (Blacklist). Không thể chuyển sang Đề xuất lương!'], 403);
        }

        if ($model->updateCandidateStatus($id, $status)) {
            $this->json(['success' => true, 'message' => 'Cập nhật trạng thái thành công.']);
        } else {
            $this->json(['success' => false, 'message' => 'Lỗi cập nhật trạng thái.'], 500);
        }
    }

    /**
     * Kiosk: Giao diện ứng tuyển nhanh qua QR Code
     */
    public function apply(): void
    {
        // Public action, no login required
        $this->view('recruitment/apply', []);
    }

    /**
     * Kiosk: Tiếp nhận dữ liệu ứng tuyển
     */
    public function submitApply(): void
    {
        if (!$this->isPost()) {
            $this->redirect('recruitment/apply', 'Invalid request method.', 'error');
            return;
        }

        $data = [
            'full_name'        => $this->postData('full_name'),
            'id_card'          => $this->postData('id_card'),
            'dob'              => $this->postData('dob') ?: null,
            'phone'            => $this->postData('phone'),
            'address'          => $this->postData('hometown'),
            'current_position' => $this->postData('position'),
            'source'           => 'QR Kiosk',
            'status'           => 'received',
            'notes'            => 'Ứng tuyển qua QR Kiosk cổng dự án',
        ];

        // Upload Helper
        $uploadFile = function($inputName) use ($data) {
            if (!empty($_FILES[$inputName]['name']) && $_FILES[$inputName]['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../../public/uploads/candidates/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                $ext = strtolower(pathinfo($_FILES[$inputName]['name'], PATHINFO_EXTENSION));
                $newName = $inputName . '_' . preg_replace('/[^a-zA-Z0-9]/', '_', $data['full_name']) . '_' . time() . '.' . $ext;
                if (move_uploaded_file($_FILES[$inputName]['tmp_name'], $uploadDir . $newName)) {
                    return 'uploads/candidates/' . $newName;
                }
            }
            return null;
        };

        $data['front_id_card_path'] = $uploadFile('front_id_card');
        $data['back_id_card_path']  = $uploadFile('back_id_card');
        $data['cert_file_path']     = $uploadFile('cert_file');

        $model = $this->model('Recruitment');
        $id = $model->addCandidate($data);

        if ($id) {
            Session::setFlash('success', 'Nộp hồ sơ thành công! Hệ thống sẽ tự động quét thông tin và nhân sự sẽ liên hệ với bạn trong thời gian sớm nhất.');
            $this->redirect('recruitment/apply');
        } else {
            Session::setFlash('error', 'Có lỗi xảy ra trong quá trình nộp hồ sơ. Xin vui lòng thử lại.');
            $this->redirect('recruitment/apply');
        }
    }
}
