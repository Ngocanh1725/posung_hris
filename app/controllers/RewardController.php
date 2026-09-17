<?php
/**
 * ============================================================
 *  POSUNG HRIS – RewardController (Upgraded)
 * ============================================================
 *  Quản lý Khen thưởng & Kỷ luật đầy đủ quy trình:
 *  - Danh sách có lọc đa tiêu chí
 *  - Thêm mới với trường thông tin mở rộng
 *  - Phê duyệt / Từ chối
 *  - In quyết định
 *  - Thống kê báo cáo
 * ============================================================
 */

class RewardController extends Controller
{
    /**
     * Danh sách Khen thưởng / Kỷ luật (Có bộ lọc)
     */
    public function index(): void
    {
        Session::checkPermission(['Admin', 'HR_Manager', 'Project_Manager']);

        $rewardModel = $this->model('RewardDiscipline');

        // Thu thập bộ lọc
        $filters = [
            'type'          => $this->getData('type', ''),
            'status'        => $this->getData('status', ''),
            'department_id' => (int)$this->getData('department_id', 0),
            'project_id'    => (int)$this->getData('project_id', 0),
            'from_date'     => $this->getData('from_date', ''),
            'to_date'       => $this->getData('to_date', ''),
            'search'        => $this->getData('search', ''),
            'safety_only'   => (int)$this->getData('safety_only', 0),
        ];

        $records = $rewardModel->getAllRecords($filters);

        // Dữ liệu cho dropdown
        $employeeModel = $this->model('Employee');
        $employees = $employeeModel->getAll();

        $db = Database::getInstance();
        $db->query("SELECT id, dept_name, dept_code FROM departments WHERE status = 'Active' ORDER BY dept_code");
        $departments = $db->fetchAll();

        $projectModel = $this->model('Project');
        $projects = $projectModel->getActiveProjects();

        $this->view('layouts/header', ['pageTitle' => 'Khen thưởng & Kỷ luật']);
        $this->view('reward/index', [
            'records'     => $records,
            'employees'   => $employees,
            'departments' => $departments,
            'projects'    => $projects,
            'filters'     => $filters
        ]);
        $this->view('layouts/footer');
    }

    /**
     * Lưu quyết định KT/KL mới
     */
    public function store(): void
    {
        Session::checkPermission(['Admin', 'HR_Manager']);

        if ($this->isPost()) {
            $type = $this->postData('type');
            $data = [
                'employee_id'         => (int)$this->postData('employee_id'),
                'department_id'       => $this->postData('department_id') ?: null,
                'project_id'          => $this->postData('project_id') ?: null,
                'type'                => $type,
                'reward_form'         => ($type === 'Reward') ? $this->postData('reward_form') : null,
                'discipline_form'     => ($type === 'Discipline') ? $this->postData('discipline_form') : null,
                'decision_number'     => $this->postData('decision_number'),
                'decision_date'       => $this->postData('decision_date'),
                'title'               => $this->postData('title'),
                'amount'              => (float)$this->postData('amount', 0),
                'reason'              => $this->postData('reason'),
                'is_safety_violation' => $this->postData('is_safety_violation') ? 1 : 0,
                'authority_level'     => $this->postData('authority_level'),
                'proposed_by'         => $this->postData('proposed_by'),
                'status'              => $this->postData('decision_status', 'Approved'),
            ];

            $rewardModel = $this->model('RewardDiscipline');
            if ($rewardModel->createRecord($data)) {
                if ($data['type'] === 'Discipline' && $data['is_safety_violation']) {
                    Session::setFlash('success', 'Đã lưu Kỷ luật & Khóa Blacklist nhân sự thành công!');
                } else {
                    Session::setFlash('success', 'Đã lưu Quyết định thành công!');
                }
            } else {
                Session::setFlash('error', 'Có lỗi xảy ra khi lưu Quyết định.');
            }

            $this->redirect('reward/index');
        }
    }

    /**
     * Phê duyệt quyết định
     */
    public function approve(int $id = 0): void
    {
        Session::checkPermission(['Admin', 'HR_Manager']);

        if ($id <= 0) {
            $this->redirect('reward');
            return;
        }

        $rewardModel = $this->model('RewardDiscipline');
        if ($rewardModel->approveRecord($id, Session::userId())) {
            Session::setFlash('success', 'Đã PHÊ DUYỆT Quyết định thành công!');
        } else {
            Session::setFlash('error', 'Không thể phê duyệt (QĐ không tồn tại hoặc đã được xử lý).');
        }

        $this->redirect('reward');
    }

    /**
     * Từ chối quyết định
     */
    public function reject(int $id = 0): void
    {
        Session::checkPermission(['Admin', 'HR_Manager']);

        if ($id <= 0) {
            $this->redirect('reward');
            return;
        }

        $rewardModel = $this->model('RewardDiscipline');
        if ($rewardModel->rejectRecord($id, Session::userId())) {
            Session::setFlash('success', 'Đã TỪ CHỐI Quyết định.');
        } else {
            Session::setFlash('error', 'Không thể từ chối (QĐ không tồn tại hoặc đã được xử lý).');
        }

        $this->redirect('reward');
    }

    /**
     * In Quyết định Khen thưởng / Kỷ luật
     */
    public function printDecision(int $id = 0): void
    {
        Session::checkPermission(['Admin', 'HR_Manager', 'Project_Manager']);

        $rewardModel = $this->model('RewardDiscipline');
        $record = $rewardModel->getRecordById($id);

        if (!$record) {
            die('Không tìm thấy quyết định.');
        }

        // Render view HTML dành riêng cho In ấn (Không dùng header/footer chính)
        $this->view('reward/print_decision', [
            'record' => $record
        ]);
    }

    /**
     * Thống kê Khen thưởng / Kỷ luật
     */
    public function statistics(): void
    {
        Session::checkPermission(['Admin', 'HR_Manager']);

        $year = (int)$this->getData('year', date('Y'));

        $rewardModel = $this->model('RewardDiscipline');
        $stats = $rewardModel->getStatistics(['year' => $year]);

        $this->view('layouts/header', ['pageTitle' => "Thống kê KT-KL Năm $year"]);
        $this->view('reward/statistics', [
            'stats' => $stats,
            'year'  => $year
        ]);
        $this->view('layouts/footer');
    }
}
