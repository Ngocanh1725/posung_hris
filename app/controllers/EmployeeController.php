<?php
/**
 * ============================================================
 *  POSUNG HRIS – EmployeeController
 * ============================================================
 *  Quản lý danh sách, thêm mới, xem chi tiết 360 độ,
 *  7 Quá trình Công tác (AJAX) và In Hồ sơ Doanh nghiệp.
 * ============================================================
 */

class EmployeeController extends Controller
{
    // ══════════════════════════════════════════════════════════
    //  DANH SÁCH & FORM THÊM MỚI (Giữ nguyên)
    // ══════════════════════════════════════════════════════════

    /**
     * Danh sách nhân viên (Tích hợp DataTables)
     */
    public function index(): void
    {
        $this->checkPermission('employee.view'); // Bất kỳ ai đăng nhập đều được vào

        $employeeModel = $this->model('Employee');
        $deptModel     = $this->model('Department');
        $projectModel  = $this->model('Project');

        // Lấy danh sách để lọc
        $departments = $deptModel->all('dept_code ASC');
        $projects    = $projectModel->getActiveProjects();

        // Xử lý bộ lọc
        $filters = [
            'search'    => $this->getData('search', ''),
            'status'    => $this->getData('status', ''),
            'type'        => $this->getData('type', ''),
            'deptId'      => (int)$this->getData('dept', 0),
            'projectId'   => (int)$this->getData('project', 0),
            'cert_status' => $this->getData('cert_status', ''),
        ];

        // Lấy danh sách nhân viên theo bộ lọc
        $employees = $employeeModel->getAll($filters);

        $this->view('layouts/header', ['pageTitle' => 'Danh sách Nhân sự']);
        $this->view('employee/index', [
            'employees'   => $employees,
            'departments' => $departments,
            'projects'    => $projects,
            'filters'     => $filters
        ]);
        $this->view('layouts/footer');
    }

    /**
     * Xuất danh sách nhân sự (CSV)
     */
    public function export(): void
    {
        $this->checkPermission('employee.view');

        $employeeModel = $this->model('Employee');
        $filters = [
            'search'    => $this->getData('search', ''),
            'status'    => $this->getData('status', ''),
            'type'        => $this->getData('type', ''),
            'deptId'      => (int)$this->getData('dept', 0),
            'projectId'   => (int)$this->getData('project', 0),
            'cert_status' => $this->getData('cert_status', ''),
        ];

        $employees = $employeeModel->getAll($filters);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=danh_sach_nhan_su_' . date('Ymd_His') . '.csv');
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM cho Excel

        fputcsv($output, ['Mã NV', 'Họ tên', 'Loại hình', 'Trạng thái', 'Phòng ban/Dự án', 'SĐT', 'Email']);

        foreach ($employees as $emp) {
            $deptProj = $emp->dept_name ?: ($emp->project_name ?: '');
            $statusVi = match($emp->status) {
                'active' => 'Đang làm việc',
                'probation' => 'Thử việc',
                'suspended' => 'Đình chỉ',
                'terminated' => 'Đã nghỉ việc',
                'retired' => 'Nghỉ hưu',
                'blocked_hse' => 'Khóa HSE',
                default => $emp->status
            };
            
            fputcsv($output, [
                $emp->emp_code,
                $emp->full_name,
                $emp->employee_type,
                $statusVi,
                $deptProj,
                $emp->phone,
                $emp->email
            ]);
        }
        fclose($output);
        exit;
    }

    /**
     * Form thêm mới nhân sự (5 Tabs)
     */
    public function create(): void
    {
        $this->checkPermission('employee.create');

        $db = Database::getInstance();
        $departments = $this->model('Department')->all('dept_code ASC');
        $projects    = $this->model('Project')->getActiveProjects();
        $positions   = $this->model('Position')->all('pos_code ASC');

        $this->view('layouts/header', ['pageTitle' => 'Thêm Nhân viên mới']);
        $this->view('employee/create', [
            'departments' => $departments,
            'projects'    => $projects,
            'positions'   => $positions
        ]);
        $this->view('layouts/footer');
    }

    /**
     * Xử lý POST thêm mới nhân sự
     */
    public function store(): void
    {
        $this->checkPermission('employee.create');

        if ($this->isPost()) {
            $employeeModel = $this->model('Employee');

            // Kiểm tra Blacklist CCCD
            $idCard = $this->postData('id_card');
            if ($idCard && $employeeModel->checkBlacklistIdCard($idCard)) {
                Session::setFlash('error', 'BÁO ĐỘNG: Số CCCD này đang nằm trong danh sách đen (Blacklisted) do vi phạm an toàn! Không thể tiếp nhận.');
                $this->redirect('employee/create');
                return;
            }

            // Validation & Unique Check
            $errors = $this->validate([
                'full_name' => 'required',
                'id_card' => 'id_card_vn',
                'phone' => 'phone_vn',
                'email' => 'email'
            ]);
            if (!empty($errors)) {
                Session::setFlash('error', 'Dữ liệu không hợp lệ: ' . implode(', ', $errors));
                $this->redirect('employee/create');
                return;
            }
            if ($idCard && !$employeeModel->checkUniqueIdCard($idCard)) {
                Session::setFlash('error', 'Lỗi: Số CCCD này đã tồn tại trong hệ thống!');
                $this->redirect('employee/create');
                return;
            }

            $data = [
                'full_name'          => $this->postData('full_name'),
                'dob'                => $this->postData('dob') ?: null,
                'gender'             => $this->postData('gender', 'Male'),
                'marital_status'     => $this->postData('marital_status', 'Single'),
                'blood_group'        => $this->postData('blood_group'),
                'ethnic'             => $this->postData('ethnic', 'Kinh'),
                'religion'           => $this->postData('religion', 'Không'),
                'id_card_no'         => $this->postData('id_card'),
                'id_card_date'       => $this->postData('id_card_date') ?: null,
                'id_card_place'      => $this->postData('id_card_place'),
                'tax_code'           => $this->postData('tax_code'),
                'social_insurance_no'=> $this->postData('social_insurance_no'),
                'health_insurance_no'=> $this->postData('health_insurance_no'),
                'bank_account'       => $this->postData('bank_account'),
                'bank_name'          => $this->postData('bank_name'),
                'bank_branch'        => $this->postData('bank_branch'),
                'emergency_contact_name'     => $this->postData('emergency_contact_name'),
                'emergency_contact_phone'    => $this->postData('emergency_contact_phone'),
                'emergency_contact_relation' => $this->postData('emergency_contact_relation'),
                'home_address'       => $this->postData('hometown'),
                'current_address'    => $this->postData('address'),
                'phone'              => $this->postData('phone'),
                'email'              => $this->postData('email'),
                'nationality'        => $this->postData('nationality', 'Vietnam'),
                'employee_type'      => $this->postData('employee_type', 'Direct_Worker'),
                'department_id'      => $this->postData('department_id') ?: null,
                'current_project_id' => $this->postData('current_project_id') ?: null,
                'position_id'        => $this->postData('position_id') ?: null,
                'join_date'          => $this->postData('join_date') ?: null,
                'official_date'      => $this->postData('official_date') ?: null,
                'highest_degree'     => $this->postData('highest_degree'),
                'status'             => $this->postData('status', 'Probation'),
                'notes'              => $this->postData('notes'),
            ];

            // Xử lý Expat Details (Nếu là Expat)
            $expatData = [];
            if ($data['employee_type'] === 'Expat') {
                $expatData = [
                    'passport_number'    => $this->postData('passport_number'),
                    'visa_number'        => $this->postData('visa_number'),
                    'visa_expiry'        => $this->postData('visa_expiry') ?: null,
                    'work_permit_number' => $this->postData('work_permit_number'),
                    'work_permit_expiry' => $this->postData('work_permit_expiry') ?: null,
                    'trc_number'         => $this->postData('trc_number'),
                    'trc_expiry'         => $this->postData('trc_expiry') ?: null,
                ];
            }

            try {
                // Upload files and create employee
                $empId = $employeeModel->createWithFiles($data, $_FILES);

                // Insert expat details if needed
                if (!empty($expatData) && $empId) {
                    $expatData['employee_id'] = $empId;
                    $db = Database::getInstance();
                    $cols = implode('`, `', array_keys($expatData));
                    $vals = implode(', ', array_map(fn($k) => ":{$k}", array_keys($expatData)));
                    $db->query("INSERT INTO expat_details (`{$cols}`) VALUES ({$vals})", $expatData);
                }

                Session::setFlash('success', 'Thêm mới nhân sự thành công! Hệ thống đã tự động cấp mã nhân viên.');
                $this->redirect("employee/detail/{$empId}");
                return;
            } catch (Exception $e) {
                Session::setFlash('error', 'Lỗi lưu dữ liệu: ' . $e->getMessage());
                $this->redirect('employee/create');
            }
        }
    }

    // ══════════════════════════════════════════════════════════
    //  XEM CHI TIẾT HỒ SƠ 360 ĐỘ
    // ══════════════════════════════════════════════════════════

    public function edit(int $id = 0): void
    {
        $this->checkPermission('employee.edit');

        $employeeModel = $this->model('Employee');
        $employee = $employeeModel->getById($id);

        if (!$employee) {
            Session::setFlash('error', 'Không tìm thấy nhân viên.');
            $this->redirect('employee');
            return;
        }

        $deptModel = $this->model('Department');
        $projectModel = $this->model('Project');
        $positionModel = $this->model('Position');

        $this->view('layouts/header', ['pageTitle' => 'Cập nhật Hồ sơ Nhân sự']);
        $this->view('employee/edit', [
            'employee'    => $employee,
            'departments' => $deptModel->all(),
            'projects'    => $projectModel->getActiveProjects(),
            'positions'   => $positionModel->all()
        ]);
        $this->view('layouts/footer');
    }

    public function update(int $id = 0): void
    {
        $this->checkPermission('employee.edit');

        if ($this->isPost()) {
            $employeeModel = $this->model('Employee');
            $employee = $employeeModel->getById($id);

            if (!$employee) {
                Session::setFlash('error', 'Không tìm thấy nhân viên.');
                $this->redirect('employee');
                return;
            }

            $idCard = $this->postData('id_card');
            $errors = $this->validate([
                'full_name' => 'required',
                'id_card' => 'id_card_vn',
                'phone' => 'phone_vn',
                'email' => 'email'
            ]);
            if (!empty($errors)) {
                Session::setFlash('error', 'Dữ liệu không hợp lệ: ' . implode(', ', $errors));
                $this->redirect('employee/edit/' . $id);
                return;
            }
            if ($idCard && !$employeeModel->checkUniqueIdCard($idCard, $id)) {
                Session::setFlash('error', 'Lỗi: Số CCCD này đã tồn tại trong hệ thống!');
                $this->redirect('employee/edit/' . $id);
                return;
            }

            $data = [
                'full_name'          => $this->postData('full_name'),
                'dob'                => $this->postData('dob') ?: null,
                'gender'             => $this->postData('gender', 'Male'),
                'marital_status'     => $this->postData('marital_status', 'Single'),
                'blood_group'        => $this->postData('blood_group'),
                'ethnic'             => $this->postData('ethnic', 'Kinh'),
                'religion'           => $this->postData('religion', 'Không'),
                'id_card_no'         => $this->postData('id_card'),
                'id_card_date'       => $this->postData('id_card_date') ?: null,
                'id_card_place'      => $this->postData('id_card_place'),
                'tax_code'           => $this->postData('tax_code'),
                'social_insurance_no'=> $this->postData('social_insurance_no'),
                'health_insurance_no'=> $this->postData('health_insurance_no'),
                'bank_account'       => $this->postData('bank_account'),
                'bank_name'          => $this->postData('bank_name'),
                'bank_branch'        => $this->postData('bank_branch'),
                'emergency_contact_name'     => $this->postData('emergency_contact_name'),
                'emergency_contact_phone'    => $this->postData('emergency_contact_phone'),
                'emergency_contact_relation' => $this->postData('emergency_contact_relation'),
                'home_address'       => $this->postData('hometown'),
                'current_address'    => $this->postData('address'),
                'phone'              => $this->postData('phone'),
                'email'              => $this->postData('email'),
                'nationality'        => $this->postData('nationality', 'Vietnam'),
                'employee_type'      => $this->postData('employee_type', 'Direct_Worker'),
                'department_id'      => $this->postData('department_id') ?: null,
                'current_project_id' => $this->postData('current_project_id') ?: null,
                'position_id'        => $this->postData('position_id') ?: null,
                'join_date'          => $this->postData('join_date') ?: null,
                'official_date'      => $this->postData('official_date') ?: null,
                'highest_degree'     => $this->postData('highest_degree'),
                'status'             => $this->postData('status', 'Probation'),
                'notes'              => $this->postData('notes'),
                
                'skill_autocad'      => $this->postData('skill_autocad'),
                'skill_revit_bim'    => $this->postData('skill_revit_bim'),
                'skill_navisworks'   => $this->postData('skill_navisworks'),
                'skill_estimation'   => $this->postData('skill_estimation'),
                'welding_cert_3g'    => $this->postData('welding_cert_3g') ? 1 : 0,
                'welding_cert_6g'    => $this->postData('welding_cert_6g') ? 1 : 0,
                'welding_cert_tig'   => $this->postData('welding_cert_tig') ? 1 : 0,
                'welding_cert_mig'   => $this->postData('welding_cert_mig') ? 1 : 0,
                'korean_level'       => $this->postData('korean_level'),
                'english_level'      => $this->postData('english_level'),
                'it_level'           => $this->postData('it_level'),
                'hse_card_number'    => $this->postData('hse_card_number'),
                'hse_card_issue_date'=> $this->postData('hse_card_issue_date') ?: null,
                'hse_card_expiry_samsung' => $this->postData('hse_card_expiry_samsung') ?: null,
                'hse_card_expiry_amkor'   => $this->postData('hse_card_expiry_amkor') ?: null,
                'can_work_at_height' => $this->postData('can_work_at_height') ? 1 : 0,
                'can_work_confined_space' => $this->postData('can_work_confined_space') ? 1 : 0,
                'chieu_cao'          => $this->postData('chieu_cao') ?: null,
                'can_nang'           => $this->postData('can_nang') ?: null,
                'safety_shoe_size'   => $this->postData('safety_shoe_size'),
                'safety_uniform_size'=> $this->postData('safety_uniform_size'),
            ];

            $expatData = [];
            if ($data['employee_type'] === 'Expat') {
                $expatData = [
                    'passport_number'    => $this->postData('passport_number'),
                    'visa_number'        => $this->postData('visa_number'),
                    'visa_expiry'        => $this->postData('visa_expiry') ?: null,
                    'work_permit_number' => $this->postData('work_permit_number'),
                    'work_permit_expiry' => $this->postData('work_permit_expiry') ?: null,
                    'trc_number'         => $this->postData('trc_number'),
                    'trc_expiry'         => $this->postData('trc_expiry') ?: null,
                ];
            }

            try {
                $employeeModel->updateWithFiles($id, $data, $_FILES);
                $db = Database::getInstance();
                
                // Handling Expat Details Update
                if (!empty($expatData)) {
                    $expatData['employee_id'] = $id;
                    $db->query("DELETE FROM expat_details WHERE employee_id = :id", ['id' => $id]);
                    $cols = implode('`, `', array_keys($expatData));
                    $vals = implode(', ', array_map(fn($k) => ":{$k}", array_keys($expatData)));
                    $db->query("INSERT INTO expat_details (`{$cols}`) VALUES ({$vals})", $expatData);
                } else {
                    $db->query("DELETE FROM expat_details WHERE employee_id = :id", ['id' => $id]);
                }

                Session::setFlash('success', 'Cập nhật hồ sơ nhân sự thành công!');
                $this->redirect("employee/detail/{$id}");
            } catch (Exception $e) {
                Session::setFlash('error', 'Lỗi lưu dữ liệu: ' . $e->getMessage());
                $this->redirect("employee/edit/{$id}");
            }
        }
    }
    /**
     * Xem chi tiết hồ sơ 360 độ
     */
    public function detail(int $id = 0): void
    {
        $this->checkPermission('employee.view');

        $employeeModel = $this->model('Employee');
        $employee = $employeeModel->getById($id);

        if (!$employee) {
            Session::setFlash('error', 'Không tìm thấy hồ sơ nhân viên này!');
            $this->redirect('employee');
            return;
        }

        $allowanceModel = $this->model('Allowance');
        $allowances = $allowanceModel->all();

        $this->view('layouts/header', ['pageTitle' => 'Hồ sơ: ' . $employee->full_name]);
        $this->view('employee/detail', [
            'employee' => $employee,
            'allAllowances' => $allowances
        ]);
        $this->view('layouts/footer');
    }

    // ══════════════════════════════════════════════════════════
    //  7 QUÁ TRÌNH CÔNG TÁC – AJAX ENDPOINTS
    // ══════════════════════════════════════════════════════════

    /**
     * Lấy dữ liệu tất cả 7 quá trình của một nhân viên (JSON).
     * GET: /employee/getProcesses/{employee_id}
     */
    public function getProcesses(int $employeeId = 0): void
    {
        $this->checkPermission('employee.view');

        if ($employeeId <= 0) {
            $this->json(['success' => false, 'message' => 'ID nhân viên không hợp lệ.'], 400);
        }

        $data = [
            'work_histories'       => $this->model('WorkHistory')->getByEmployee($employeeId),
            'trainings'            => $this->model('Training')->getByEmployee($employeeId),
            'salary_progressions'  => $this->model('SalaryProgression')->getByEmployee($employeeId),
            'family_members'       => $this->model('FamilyMember')->getByEmployee($employeeId),
            'reward_disciplines'   => $this->model('RewardDisciplineHistory')->getByEmployee($employeeId),
            'evaluations'          => $this->model('Evaluation')->getByEmployee($employeeId),
            'appointments'         => $this->model('Appointment')->getByEmployee($employeeId),
        ];

        $this->json(['success' => true, 'data' => $data]);
    }

    // ── 1. Quá trình Công tác ──────────────────────────────

    /**
     * POST: Thêm/Sửa quá trình công tác
     */
    public function saveWorkHistory(): void
    {
        $this->checkPermission('employee.edit');
        if (!$this->isPost()) $this->json(['success' => false, 'message' => 'Invalid request'], 405);

        $model = $this->model('WorkHistory');
        $id = (int)$this->postData('id', 0);
        $data = [
            'employee_id'  => (int)$this->postData('employee_id'),
            'from_date'    => $this->postData('from_date') ?: null,
            'to_date'      => $this->postData('to_date') ?: null,
            'organization' => $this->postData('organization', ''),
            'position'     => $this->postData('position'),
            'project_name' => $this->postData('project_name'),
            'description'  => $this->postData('description'),
        ];

        try {
            if ($id > 0) {
                unset($data['employee_id']);
                $model->update($id, $data);
                $this->json(['success' => true, 'message' => 'Cập nhật thành công.']);
            } else {
                $newId = $model->create($data);
                $this->json(['success' => true, 'message' => 'Thêm mới thành công.', 'id' => $newId]);
            }
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }

    /**
     * POST: Xóa quá trình công tác
     */
    public function deleteWorkHistory(int $id = 0): void
    {
        $this->checkPermission('employee.delete');
        if ($id <= 0) $this->json(['success' => false, 'message' => 'ID không hợp lệ'], 400);

        try {
            $this->model('WorkHistory')->delete($id);
            $this->json(['success' => true, 'message' => 'Đã xóa thành công.']);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }

    // ── 2. Quá trình Đào tạo ───────────────────────────────

    public function saveTraining(): void
    {
        $this->checkPermission('employee.edit');
        if (!$this->isPost()) $this->json(['success' => false, 'message' => 'Invalid request'], 405);

        $model = $this->model('Training');
        $id = (int)$this->postData('id', 0);
        $data = [
            'employee_id' => (int)$this->postData('employee_id'),
            'from_date'   => $this->postData('from_date') ?: null,
            'to_date'     => $this->postData('to_date') ?: null,
            'institution' => $this->postData('institution', ''),
            'major'       => $this->postData('major'),
            'certificate' => $this->postData('certificate'),
            'degree_type' => $this->postData('degree_type'),
            'notes'       => $this->postData('notes'),
        ];

        try {
            if ($id > 0) {
                unset($data['employee_id']);
                $model->update($id, $data);
                $this->json(['success' => true, 'message' => 'Cập nhật thành công.']);
            } else {
                $newId = $model->create($data);
                $this->json(['success' => true, 'message' => 'Thêm mới thành công.', 'id' => $newId]);
            }
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }

    public function deleteTraining(int $id = 0): void
    {
        $this->checkPermission('employee.delete');
        if ($id <= 0) $this->json(['success' => false, 'message' => 'ID không hợp lệ'], 400);

        try {
            $this->model('Training')->delete($id);
            $this->json(['success' => true, 'message' => 'Đã xóa thành công.']);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }

    // ── 3. Diễn biến Lương ─────────────────────────────────

    public function saveSalaryProgression(): void
    {
        $this->checkPermission('employee.edit');
        if (!$this->isPost()) $this->json(['success' => false, 'message' => 'Invalid request'], 405);

        $model = $this->model('SalaryProgression');
        $id = (int)$this->postData('id', 0);
        $data = [
            'employee_id'        => (int)$this->postData('employee_id'),
            'effective_date'     => $this->postData('effective_date'),
            'salary_grade'       => $this->postData('salary_grade'),
            'salary_coefficient' => $this->postData('salary_coefficient') ?: null,
            'base_salary'        => $this->postData('base_salary', 0),
            'decision_number'    => $this->postData('decision_number'),
            'notes'              => $this->postData('notes'),
        ];

        try {
            if ($id > 0) {
                unset($data['employee_id']);
                $model->update($id, $data);
                $this->json(['success' => true, 'message' => 'Cập nhật thành công.']);
            } else {
                $newId = $model->create($data);
                $this->json(['success' => true, 'message' => 'Thêm mới thành công.', 'id' => $newId]);
            }
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }

    public function deleteSalaryProgression(int $id = 0): void
    {
        $this->checkPermission('employee.delete');
        if ($id <= 0) $this->json(['success' => false, 'message' => 'ID không hợp lệ'], 400);

        try {
            $this->model('SalaryProgression')->delete($id);
            $this->json(['success' => true, 'message' => 'Đã xóa thành công.']);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }

    // ── 4. Quan hệ Gia đình ────────────────────────────────

    public function saveFamilyMember(): void
    {
        $this->checkPermission('employee.edit');
        if (!$this->isPost()) $this->json(['success' => false, 'message' => 'Invalid request'], 405);

        $model = $this->model('FamilyMember');
        $id = (int)$this->postData('id', 0);
        $data = [
            'employee_id'  => (int)$this->postData('employee_id'),
            'full_name'    => $this->postData('full_name', ''),
            'relationship' => $this->postData('relationship', ''),
            'dob'          => $this->postData('dob') ?: null,
            'occupation'   => $this->postData('occupation'),
            'workplace'    => $this->postData('workplace'),
            'address'      => $this->postData('address'),
            'id_card'      => $this->postData('id_card'),
            'phone'        => $this->postData('phone'),
            'notes'        => $this->postData('notes'),
        ];

        try {
            if ($id > 0) {
                unset($data['employee_id']);
                $model->update($id, $data);
                $this->json(['success' => true, 'message' => 'Cập nhật thành công.']);
            } else {
                $newId = $model->create($data);
                $this->json(['success' => true, 'message' => 'Thêm mới thành công.', 'id' => $newId]);
            }
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }

    public function deleteFamilyMember(int $id = 0): void
    {
        $this->checkPermission('employee.delete');
        if ($id <= 0) $this->json(['success' => false, 'message' => 'ID không hợp lệ'], 400);

        try {
            $this->model('FamilyMember')->delete($id);
            $this->json(['success' => true, 'message' => 'Đã xóa thành công.']);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }

    // ── 5. Khen thưởng – Kỷ luật ───────────────────────────

    public function saveRewardDiscipline(): void
    {
        $this->checkPermission('employee.edit');
        if (!$this->isPost()) $this->json(['success' => false, 'message' => 'Invalid request'], 405);

        $model = $this->model('RewardDisciplineHistory');
        $id = (int)$this->postData('id', 0);
        $data = [
            'employee_id'     => (int)$this->postData('employee_id'),
            'type'            => $this->postData('type', 'Reward'),
            'decision_number' => $this->postData('decision_number'),
            'decision_date'   => $this->postData('decision_date') ?: null,
            'title'           => $this->postData('title', ''),
            'reason'          => $this->postData('reason'),
            'authority'       => $this->postData('authority'),
            'notes'           => $this->postData('notes'),
        ];

        try {
            if ($id > 0) {
                unset($data['employee_id']);
                $model->update($id, $data);
                $this->json(['success' => true, 'message' => 'Cập nhật thành công.']);
            } else {
                $newId = $model->create($data);
                $this->json(['success' => true, 'message' => 'Thêm mới thành công.', 'id' => $newId]);
            }
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }

    public function deleteRewardDiscipline(int $id = 0): void
    {
        $this->checkPermission('employee.delete');
        if ($id <= 0) $this->json(['success' => false, 'message' => 'ID không hợp lệ'], 400);

        try {
            $this->model('RewardDisciplineHistory')->delete($id);
            $this->json(['success' => true, 'message' => 'Đã xóa thành công.']);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }

    // ── 6. Đánh giá KPI ────────────────────────────────────

    public function saveEvaluation(): void
    {
        $this->checkPermission('employee.edit');
        if (!$this->isPost()) $this->json(['success' => false, 'message' => 'Invalid request'], 405);

        $model = $this->model('Evaluation');
        $id = (int)$this->postData('id', 0);
        $data = [
            'employee_id' => (int)$this->postData('employee_id'),
            'eval_year'   => (int)$this->postData('eval_year', date('Y')),
            'eval_period' => $this->postData('eval_period'),
            'rating'      => $this->postData('rating'),
            'evaluator'   => $this->postData('evaluator'),
            'score'       => $this->postData('score') ?: null,
            'notes'       => $this->postData('notes'),
        ];

        try {
            if ($id > 0) {
                unset($data['employee_id']);
                $model->update($id, $data);
                $this->json(['success' => true, 'message' => 'Cập nhật thành công.']);
            } else {
                $newId = $model->create($data);
                $this->json(['success' => true, 'message' => 'Thêm mới thành công.', 'id' => $newId]);
            }
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }

    public function deleteEvaluation(int $id = 0): void
    {
        $this->checkPermission('employee.delete');
        if ($id <= 0) $this->json(['success' => false, 'message' => 'ID không hợp lệ'], 400);

        try {
            $this->model('Evaluation')->delete($id);
            $this->json(['success' => true, 'message' => 'Đã xóa thành công.']);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }

    // ── 7. Quá trình Bổ nhiệm ──────────────────────────────

    public function saveAppointment(): void
    {
        $this->checkPermission('employee.edit');
        if (!$this->isPost()) $this->json(['success' => false, 'message' => 'Invalid request'], 405);

        $model = $this->model('Appointment');
        $id = (int)$this->postData('id', 0);
        $data = [
            'employee_id'     => (int)$this->postData('employee_id'),
            'effective_date'  => $this->postData('effective_date'),
            'position_title'  => $this->postData('position_title', ''),
            'department'      => $this->postData('department'),
            'decision_number' => $this->postData('decision_number'),
            'notes'           => $this->postData('notes'),
        ];

        try {
            if ($id > 0) {
                unset($data['employee_id']);
                $model->update($id, $data);
                $this->json(['success' => true, 'message' => 'Cập nhật thành công.']);
            } else {
                $newId = $model->create($data);
                $this->json(['success' => true, 'message' => 'Thêm mới thành công.', 'id' => $newId]);
            }
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }

    public function deleteAppointment(int $id = 0): void
    {
        $this->checkPermission('employee.delete');
        if ($id <= 0) $this->json(['success' => false, 'message' => 'ID không hợp lệ'], 400);

        try {
            $this->model('Appointment')->delete($id);
            $this->json(['success' => true, 'message' => 'Đã xóa thành công.']);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }

    // ══════════════════════════════════════════════════════════
    //  IN HỒ SƠ
    // ══════════════════════════════════════════════════════════



    /**
     * Màn hình In Bản khai Hồ sơ Nhân sự Doanh nghiệp (Mới)
     */
    public function printProfile(int $id = 0): void
    {
        $this->checkPermission('employee.view');

        $employeeModel = $this->model('Employee');
        $employee = $employeeModel->getById($id);

        if (!$employee) {
            die('Không tìm thấy dữ liệu nhân viên!');
        }

        // Load dữ liệu 7 quá trình
        $processes = [
            'work_histories'      => $this->model('WorkHistory')->getByEmployee($id),
            'trainings'           => $this->model('Training')->getByEmployee($id),
            'salary_progressions' => $this->model('SalaryProgression')->getByEmployee($id),
            'family_members'      => $this->model('FamilyMember')->getByEmployee($id),
            'reward_disciplines'  => $this->model('RewardDisciplineHistory')->getByEmployee($id),
        ];

        $this->view('employee/print_profile', [
            'employee'  => $employee,
            'processes' => $processes
        ]);
    }

    // ══════════════════════════════════════════════════════════
    //  CÁC CHỨC NĂNG KHÁC (Giữ nguyên)
    // ══════════════════════════════════════════════════════════

    /**
     * Màn hình cảnh báo Hưu trí
     */
    public function retireAlerts(): void
    {
        $this->checkPermission('employee.view');
        
        $employeeModel = $this->model('Employee');
        $alerts = $employeeModel->getRetiringAlerts();

        $this->view('layouts/header', ['pageTitle' => 'Cảnh báo Hưu trí']);
        $this->view('employee/retire_alerts', ['alerts' => $alerts]);
        $this->view('layouts/footer');
    }

    /**
     * Màn hình Offboarding (Bàn giao tài sản)
     */
    public function offboard(int $id): void
    {
        $this->checkPermission('employee.edit');
        
        $employeeModel = $this->model('Employee');
        $employee = $employeeModel->getById($id);
        
        if (!$employee) {
            $this->redirect('employee');
            return;
        }

        if ($this->isPost()) {
            $offboardingModel = $this->model('Offboarding');
            $data = [
                'employee_id'      => $id,
                'ppe_returned'     => $this->postData('ppe_returned') ? 1 : 0,
                'tools_returned'   => $this->postData('tools_returned') ? 1 : 0,
                'id_card_returned' => $this->postData('id_card_returned') ? 1 : 0,
                'laptop_returned'  => $this->postData('laptop_returned') ? 1 : 0,
                'notes'            => $this->postData('notes'),
                'created_by'       => Session::userId()
            ];
            
            $status = $this->postData('new_status', 'Resigned');

            if ($offboardingModel->processOffboarding($data, $status)) {
                Session::setFlash('success', 'Đã lưu Biên bản bàn giao và cập nhật trạng thái nhân viên thành công.');
                $this->redirect('employee/view/' . $id);
                return;
            } else {
                Session::setFlash('error', 'Lỗi khi lưu Biên bản bàn giao.');
            }
        }

        $this->view('layouts/header', ['pageTitle' => 'Biên bản Bàn giao Thôi việc']);
        $this->view('employee/offboard', ['employee' => $employee]);
        $this->view('layouts/footer');
    }

    /**
     * Danh sách Chuyên gia nước ngoài (Expat)
     */
    public function expats(): void
    {
        $this->checkPermission('employee.view'); // Thay vì edit, xem là view

        $db = Database::getInstance();
        $db->query(
            "SELECT e.id, e.emp_code, e.full_name, e.nationality, e.status, e.email,
                    ex.passport_number, ex.visa_expiry, ex.work_permit_expiry, ex.trc_expiry
             FROM employees e
             JOIN expat_details ex ON e.id = ex.employee_id
             WHERE e.employee_type = 'Expat' AND e.status IN ('Active','Probation')
             ORDER BY e.emp_code ASC"
        );
        $expats = $db->fetchAll();

        $processedExpats = [];
        foreach ($expats as $ex) {
            $ex['trc_status'] = $this->getExpiryStatusColor($ex['trc_expiry']);
            $ex['visa_status'] = $this->getExpiryStatusColor($ex['visa_expiry']);
            $processedExpats[] = (object)$ex;
        }

        $this->view('layouts/header', ['pageTitle' => 'Chuyên gia nước ngoài (Expat)']);
        $this->view('employee/expats', ['expats' => $processedExpats]);
        $this->view('layouts/footer');
    }

    private function getExpiryStatusColor($dateStr) {
        if (!$dateStr) return 'gray';
        $days = (strtotime($dateStr) - time()) / (60 * 60 * 24);
        if ($days < 30) return 'red';
        if ($days <= 60) return 'yellow';
        return 'green';
    }

    /**
     * Danh sách Chứng chỉ & HSE
     */
    public function certificates(): void
    {
        $this->checkPermission('employee.edit');

        $db = Database::getInstance();

        // Chứng chỉ sắp hết hạn (90 ngày)
        $db->query(
            "SELECT c.*, e.emp_code, e.full_name
             FROM certificates c
             JOIN employees e ON c.employee_id = e.id
             WHERE c.expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 90 DAY)
             ORDER BY c.expiry_date ASC"
        );
        $expiring = json_decode(json_encode($db->fetchAll()));

        // Tất cả chứng chỉ
        $db->query(
            "SELECT c.*, e.emp_code, e.full_name
             FROM certificates c
             JOIN employees e ON c.employee_id = e.id
             ORDER BY c.expiry_date DESC"
        );
        $allCerts = json_decode(json_encode($db->fetchAll()));

        $this->view('layouts/header', ['pageTitle' => 'Chứng chỉ & HSE']);
        $this->view('employee/certificates', [
            'expiring' => $expiring,
            'allCerts' => $allCerts
        ]);
        $this->view('layouts/footer');
    }

    // ── IN ẤN & XUẤT DỮ LIỆU HUHA ──────────────────────────

    /**
     * In Sơ yếu lý lịch (Mẫu 2C-BNV/2008)
     */
    public function print2c(int $id)
    {
        $this->checkPermission('employee.view');
        $employee = $this->model('Employee')->getById($id);
        if (!$employee) {
            $this->redirect('/employee/index', 'Không tìm thấy nhân sự.', 'error');
        }
        $this->view('employee/print_2c', ['employee' => $employee]);
    }

    /**
     * Xuất dữ liệu nhân sự tương thích HUHA HRM (QTQHGD.DBF, QTCTAC.DBF...)
     */
    public function exportHuha(int $id)
    {
        $this->checkPermission('employee.edit');
        $employee = $this->model('Employee')->getById($id);
        if (!$employee) {
            $this->redirect('/employee/index', 'Không tìm thấy nhân sự.', 'error');
        }
        $this->view('employee/huha_export', ['employee' => $employee]);
    }

}
