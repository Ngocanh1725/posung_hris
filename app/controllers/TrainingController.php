<?php
/**
 * ============================================================
 *  POSUNG HRIS – TrainingController
 * ============================================================
 *  Phân hệ Quản lý Đào tạo & Phát triển Nguồn nhân lực (L&D)
 *  Quản lý khóa học, học viên, đánh giá kết quả, tích hợp hồ sơ 360°
 * ============================================================
 */

class TrainingController extends Controller
{
    /**
     * Danh sách khóa đào tạo (kèm bộ lọc & KPI)
     */
    public function index(): void
    {
        $this->checkPermission('employee.view');

        $trainingModel = $this->model('Training');
        $deptModel     = $this->model('Department');

        // Nhận bộ lọc từ query parameters
        $filters = [
            'year'          => $this->getData('year', date('Y')),
            'department_id' => $this->getData('dept', ''),
            'status'        => $this->getData('status', ''),
            'search'        => $this->getData('search', ''),
        ];

        $trainings   = $trainingModel->getAll($filters);
        $stats       = $trainingModel->getStats();
        $years       = $trainingModel->getYears();
        $departments = $deptModel->all('dept_code ASC');

        $this->view('layouts/header', ['pageTitle' => 'Quản lý Đào tạo & Phát triển (L&D)']);
        $this->view('training/index', [
            'trainings'   => $trainings,
            'stats'       => $stats,
            'years'       => $years,
            'departments' => $departments,
            'filters'     => $filters,
        ]);
        $this->view('layouts/footer');
    }

    /**
     * Form tạo khóa đào tạo mới
     */
    public function create(): void
    {
        $this->checkPermission('employee.edit');

        $deptModel = $this->model('Department');
        $departments = $deptModel->all('dept_code ASC');

        $this->view('layouts/header', ['pageTitle' => 'Tạo Khóa Đào tạo Mới']);
        $this->view('training/create', [
            'departments' => $departments,
        ]);
        $this->view('layouts/footer');
    }

    /**
     * Lưu khóa đào tạo mới vào CSDL
     */
    public function store(): void
    {
        $this->checkPermission('employee.edit');

        if (!$this->isPost()) {
            $this->redirect('training');
            return;
        }

        $courseName = $this->postData('course_name');
        if (empty($courseName)) {
            Session::setFlash('error', 'Vui lòng nhập tên khóa đào tạo.');
            $this->redirect('training/create');
            return;
        }

        $data = [
            'course_name'      => $courseName,
            'description'      => $this->postData('description'),
            'start_date'       => $this->postData('start_date'),
            'end_date'         => $this->postData('end_date'),
            'provider'         => $this->postData('provider'),
            'department_id'    => $this->postData('department_id'),
            'cost'             => $this->postData('cost', 0),
            'max_participants' => $this->postData('max_participants', 0),
            'location'         => $this->postData('location'),
            'status'           => $this->postData('status', 'Planning'),
        ];

        try {
            $trainingModel = $this->model('Training');
            $id = $trainingModel->createCourse($data);

            Session::setFlash('success', 'Tạo khóa đào tạo mới thành công!');
            $this->redirect("training/show/{$id}");
        } catch (Exception $e) {
            Session::setFlash('error', 'Lỗi lưu dữ liệu: ' . $e->getMessage());
            $this->redirect('training/create');
        }
    }

    /**
     * Chi tiết khóa đào tạo + Danh sách học viên tham gia
     */
    public function show(int $id = 0): void
    {
        $this->checkPermission('employee.view');

        $trainingModel = $this->model('Training');
        $course = $trainingModel->getById($id);

        if (!$course) {
            Session::setFlash('error', 'Không tìm thấy khóa đào tạo yêu cầu.');
            $this->redirect('training');
            return;
        }

        $participants = $trainingModel->getParticipants($id);
        $deptModel = $this->model('Department');
        $departments = $deptModel->all('dept_code ASC');

        $this->view('layouts/header', ['pageTitle' => 'Chi tiết Khóa Đào tạo: ' . $course->course_name]);
        $this->view('training/show', [
            'course'       => $course,
            'participants' => $participants,
            'departments'  => $departments,
        ]);
        $this->view('layouts/footer');
    }

    /**
     * Form chỉnh sửa thông tin khóa đào tạo
     */
    public function edit(int $id = 0): void
    {
        $this->checkPermission('employee.edit');

        $trainingModel = $this->model('Training');
        $course = $trainingModel->getById($id);

        if (!$course) {
            Session::setFlash('error', 'Không tìm thấy khóa đào tạo.');
            $this->redirect('training');
            return;
        }

        $deptModel = $this->model('Department');
        $departments = $deptModel->all('dept_code ASC');

        $this->view('layouts/header', ['pageTitle' => 'Chỉnh sửa Khóa Đào tạo: ' . $course->course_name]);
        $this->view('training/edit', [
            'course'      => $course,
            'departments' => $departments,
        ]);
        $this->view('layouts/footer');
    }

    /**
     * Cập nhật thông tin khóa đào tạo
     */
    public function update(int $id = 0): void
    {
        $this->checkPermission('employee.edit');

        if (!$this->isPost()) {
            $this->redirect('training');
            return;
        }

        $courseName = $this->postData('course_name');
        if (empty($courseName)) {
            Session::setFlash('error', 'Vui lòng nhập tên khóa đào tạo.');
            $this->redirect("training/edit/{$id}");
            return;
        }

        $data = [
            'course_name'      => $courseName,
            'description'      => $this->postData('description'),
            'start_date'       => $this->postData('start_date'),
            'end_date'         => $this->postData('end_date'),
            'provider'         => $this->postData('provider'),
            'department_id'    => $this->postData('department_id'),
            'cost'             => $this->postData('cost', 0),
            'max_participants' => $this->postData('max_participants', 0),
            'location'         => $this->postData('location'),
            'status'           => $this->postData('status', 'Planning'),
        ];

        try {
            $trainingModel = $this->model('Training');
            $trainingModel->updateCourse($id, $data);

            Session::setFlash('success', 'Cập nhật thông tin khóa đào tạo thành công!');
            $this->redirect("training/show/{$id}");
        } catch (Exception $e) {
            Session::setFlash('error', 'Lỗi cập nhật: ' . $e->getMessage());
            $this->redirect("training/edit/{$id}");
        }
    }

    /**
     * Xóa khóa đào tạo (Soft delete)
     */
    public function delete(int $id = 0): void
    {
        $this->checkPermission('employee.delete');

        if (!$this->isPost()) {
            $this->redirect('training');
            return;
        }

        $trainingModel = $this->model('Training');
        if ($trainingModel->softDelete($id)) {
            Session::setFlash('success', 'Đã chuyển khóa đào tạo vào lưu trữ (Xóa thành công)!');
        } else {
            Session::setFlash('error', 'Không thể xóa khóa đào tạo này.');
        }

        $this->redirect('training');
    }

    /**
     * Thêm danh sách nhân viên vào khóa đào tạo
     */
    public function addParticipants(int $id = 0): void
    {
        $this->checkPermission('employee.edit');

        if (!$this->isPost()) {
            $this->redirect("training/show/{$id}");
            return;
        }

        $employeeIds = $_POST['employee_ids'] ?? [];
        if (empty($employeeIds)) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                $this->json(['success' => false, 'message' => 'Chưa chọn nhân viên nào.'], 400);
                return;
            }
            Session::setFlash('error', 'Vui lòng chọn ít nhất một nhân viên để thêm.');
            $this->redirect("training/show/{$id}");
            return;
        }

        $trainingModel = $this->model('Training');
        $addedCount = $trainingModel->addParticipants($id, $employeeIds);

        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            $this->json([
                'success' => true,
                'message' => "Đã thêm thành công {$addedCount} học viên vào khóa học.",
                'count'   => $addedCount,
            ]);
            return;
        }

        Session::setFlash('success', "Đã thêm thành công {$addedCount} học viên vào khóa học.");
        $this->redirect("training/show/{$id}");
    }

    /**
     * Xóa 1 học viên khỏi khóa đào tạo
     */
    public function removeParticipant(int $trainingId = 0, int $employeeId = 0): void
    {
        $this->checkPermission('employee.edit');

        if (!$this->isPost()) {
            $this->redirect("training/show/{$trainingId}");
            return;
        }

        $trainingModel = $this->model('Training');
        if ($trainingModel->removeParticipant($trainingId, $employeeId)) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                $this->json(['success' => true, 'message' => 'Đã xóa học viên khỏi khóa đào tạo.']);
                return;
            }
            Session::setFlash('success', 'Đã xóa học viên khỏi khóa đào tạo.');
        } else {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                $this->json(['success' => false, 'message' => 'Không thể xóa học viên này.'], 400);
                return;
            }
            Session::setFlash('error', 'Không thể xóa học viên này.');
        }

        $this->redirect("training/show/{$trainingId}");
    }

    /**
     * Cập nhật kết quả đào tạo của một học viên (Điểm số, Đạt/Không đạt, Số chứng chỉ, v.v.)
     */
    public function updateResult(int $trainingId = 0, int $employeeId = 0): void
    {
        $this->checkPermission('employee.edit');

        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => 'Phương thức không hợp lệ.'], 405);
            return;
        }

        // Nhận ID từ POST nếu không có trong URL params
        if ($trainingId <= 0) $trainingId = (int)$this->postData('training_id');
        if ($employeeId <= 0) $employeeId = (int)$this->postData('employee_id');

        if ($trainingId <= 0 || $employeeId <= 0) {
            $this->json(['success' => false, 'message' => 'Thiếu thông tin khóa đào tạo hoặc nhân viên.'], 400);
            return;
        }

        $data = [
            'score'          => $this->postData('score'),
            'result'         => $this->postData('result', 'Pending'),
            'certificate_no' => $this->postData('certificate_no'),
            'status'         => $this->postData('status', 'Completed'),
            'completed_date' => $this->postData('completed_date'),
            'notes'          => $this->postData('notes'),
        ];

        $trainingModel = $this->model('Training');
        $success = $trainingModel->updateParticipantResult($trainingId, $employeeId, $data);

        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) || $this->postData('is_ajax')) {
            if ($success) {
                $this->json(['success' => true, 'message' => 'Cập nhật kết quả đào tạo thành công!']);
            } else {
                $this->json(['success' => false, 'message' => 'Cập nhật kết quả thất bại.'], 500);
            }
            return;
        }

        if ($success) {
            Session::setFlash('success', 'Cập nhật kết quả đào tạo thành công!');
        } else {
            Session::setFlash('error', 'Không thể cập nhật kết quả đào tạo.');
        }

        $this->redirect("training/show/{$trainingId}");
    }

    /**
     * Lấy danh sách nhân viên có thể thêm vào khóa đào tạo (AJAX)
     */
    public function getAvailableEmployees(int $trainingId = 0): void
    {
        $this->checkPermission('employee.view');

        $deptId = $this->getData('dept_id');
        $search = $this->getData('search');

        $trainingModel = $this->model('Training');
        $employees = $trainingModel->getAvailableEmployees($trainingId, [
            'dept_id' => $deptId,
            'search'  => $search,
        ]);

        $this->json(['success' => true, 'data' => $employees]);
    }

    /**
     * Lịch sử đào tạo theo nhân viên (Phục vụ Tab Đào tạo & AJAX 360°)
     */
    public function employeeTrainingHistory(int $employeeId = 0): void
    {
        $this->checkPermission('employee.view');

        if ($employeeId <= 0) {
            $this->json(['success' => false, 'message' => 'ID nhân viên không hợp lệ.'], 400);
            return;
        }

        $trainingModel = $this->model('Training');
        $trainings = $trainingModel->getByEmployee($employeeId);

        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) || $this->getData('format') === 'json') {
            $this->json(['success' => true, 'data' => $trainings]);
            return;
        }

        // Render partial view cho tab embed
        $this->view('training/employee_history', [
            'trainings'  => $trainings,
            'employeeId' => $employeeId,
        ]);
    }

    /**
     * Thêm bản ghi đào tạo cá nhân thủ công (Từ Profile 360°)
     */
    public function saveEmployeeTraining(): void
    {
        $this->checkPermission('employee.edit');

        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => 'Phương thức không hợp lệ.'], 405);
            return;
        }

        $employeeId = (int)$this->postData('employee_id');
        $trainingName = $this->postData('training_name');

        if ($employeeId <= 0 || empty($trainingName)) {
            $this->json(['success' => false, 'message' => 'Vui lòng nhập tên khóa học / bằng cấp.'], 400);
            return;
        }

        $data = [
            'employee_id'    => $employeeId,
            'training_name'  => $trainingName,
            'training_type'  => $this->postData('training_type', 'External'),
            'institution'    => $this->postData('institution'),
            'from_date'      => $this->postData('from_date'),
            'to_date'        => $this->postData('to_date'),
            'result'         => $this->postData('result', 'Completed'),
            'certificate_no' => $this->postData('certificate_no'),
            'notes'          => $this->postData('notes'),
        ];

        try {
            $trainingModel = $this->model('Training');
            $id = $trainingModel->addEmployeeRecord($data);

            $this->json([
                'success' => true,
                'message' => 'Thêm quá trình đào tạo thành công!',
                'id'      => $id
            ]);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Xóa bản ghi đào tạo cá nhân (Từ Profile 360°)
     */
    public function deleteEmployeeTraining(int $id = 0): void
    {
        $this->checkPermission('employee.delete');

        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => 'Phương thức không hợp lệ.'], 405);
            return;
        }

        $employeeId = (int)$this->postData('employee_id');

        $trainingModel = $this->model('Training');
        if ($trainingModel->deleteEmployeeRecord($id, $employeeId)) {
            $this->json(['success' => true, 'message' => 'Đã xóa bản ghi đào tạo.']);
        } else {
            $this->json(['success' => false, 'message' => 'Không thể xóa bản ghi này.'], 400);
        }
    }
}
