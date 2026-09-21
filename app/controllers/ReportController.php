<?php
/**
 * ============================================================
 *  POSUNG HRIS – ReportController (Upgraded)
 * ============================================================
 *  Quản lý hệ thống báo cáo toàn diện 8 phân hệ.
 * ============================================================
 */

class ReportController extends Controller
{
    private function getCommonFilters(): array
    {
        $db = Database::getInstance();
        $db->query("SELECT id, dept_name, dept_code FROM departments WHERE status = 'Active' ORDER BY dept_code");
        $departments = $db->fetchAll();

        $projectModel = $this->model('Project');
        $projects = $projectModel->getActiveProjects();

        return [
            'departments' => $departments,
            'projects'    => $projects
        ];
    }

    /**
     * Dashboard Báo cáo
     */
    public function index(): void
    {
        Session::checkPermission(['Admin', 'HR_Manager', 'Director']);
        
        $reportModel = $this->model('Report');
        $biData = $reportModel->getBiDashboardData();

        $this->view('layouts/header', ['pageTitle' => 'Báo cáo BI (Business Intelligence)']);
        $this->view('reports/index', ['biData' => $biData]);
        $this->view('layouts/footer');
    }

    /**
     * Báo cáo Quân số
     */
    public function headcount(): void
    {
        Session::checkPermission(['Admin', 'HR_Manager', 'Project_Manager']);

        $filters = [
            'status'        => $this->getData('status', 'Active'),
            'department_id' => (int)$this->getData('department_id', 0),
            'project_id'    => (int)$this->getData('project_id', 0),
            'employee_type' => $this->getData('employee_type', ''),
        ];

        $model = $this->model('Report');
        $data = $model->getHeadcountReport($filters);
        
        $common = $this->getCommonFilters();

        $this->view('layouts/header', ['pageTitle' => 'Báo cáo Quân số']);
        $this->view('reports/headcount', array_merge($common, [
            'data'    => $data,
            'filters' => $filters
        ]));
        $this->view('layouts/footer');
    }

    /**
     * Báo cáo Khen thưởng
     */
    public function reward(): void
    {
        Session::checkPermission(['Admin', 'HR_Manager']);

        $filters = [
            'year'          => (int)$this->getData('year', date('Y')),
            'department_id' => (int)$this->getData('department_id', 0),
        ];

        $model = $this->model('Report');
        $data = $model->getRewardReport($filters);
        
        $common = $this->getCommonFilters();

        $this->view('layouts/header', ['pageTitle' => 'Báo cáo Khen thưởng']);
        $this->view('reports/reward', array_merge($common, [
            'data'    => $data,
            'filters' => $filters
        ]));
        $this->view('layouts/footer');
    }

    /**
     * Báo cáo Kỷ luật
     */
    public function discipline(): void
    {
        Session::checkPermission(['Admin', 'HR_Manager']);

        $filters = [
            'year'        => (int)$this->getData('year', date('Y')),
            'safety_only' => (int)$this->getData('safety_only', 0),
        ];

        $model = $this->model('Report');
        $data = $model->getDisciplineReport($filters);

        $this->view('layouts/header', ['pageTitle' => 'Báo cáo Kỷ luật & An toàn']);
        $this->view('reports/discipline', [
            'data'    => $data,
            'filters' => $filters
        ]);
        $this->view('layouts/footer');
    }

    /**
     * Báo cáo Nghỉ hưu
     */
    public function retirement(): void
    {
        Session::checkPermission(['Admin', 'HR_Manager']);

        $filters = [
            'within_months' => (int)$this->getData('months', 12)
        ];

        $model = $this->model('Report');
        $data = $model->getRetirementReport($filters);

        $this->view('layouts/header', ['pageTitle' => 'Báo cáo Hưu trí']);
        $this->view('reports/retirement', [
            'data'    => $data,
            'filters' => $filters
        ]);
        $this->view('layouts/footer');
    }

    /**
     * Báo cáo Thuyên chuyển
     */
    public function transfer(): void
    {
        Session::checkPermission(['Admin', 'HR_Manager']);

        $filters = [
            'year'   => (int)$this->getData('year', date('Y')),
            'status' => $this->getData('status', '')
        ];

        $model = $this->model('Report');
        $data = $model->getTransferReport($filters);

        $this->view('layouts/header', ['pageTitle' => 'Báo cáo Thuyên chuyển']);
        $this->view('reports/transfer', [
            'data'    => $data,
            'filters' => $filters
        ]);
        $this->view('layouts/footer');
    }

    /**
     * Báo cáo Chấm công
     */
    public function attendance(): void
    {
        Session::checkPermission(['Admin', 'HR_Manager', 'Project_Manager']);

        $filters = [
            'month'         => (int)$this->getData('month', date('m')),
            'year'          => (int)$this->getData('year', date('Y')),
            'department_id' => (int)$this->getData('department_id', 0),
        ];

        $model = $this->model('Report');
        $data = $model->getAttendanceReport($filters);
        
        $common = $this->getCommonFilters();

        $this->view('layouts/header', ['pageTitle' => 'Báo cáo Chấm công']);
        $this->view('reports/attendance', array_merge($common, [
            'data'    => $data,
            'filters' => $filters
        ]));
        $this->view('layouts/footer');
    }

    /**
     * Báo cáo Lương
     */
    public function payroll(): void
    {
        Session::checkPermission(['Admin', 'HR_Manager']);

        $filters = [
            'month'         => (int)$this->getData('month', date('m')),
            'year'          => (int)$this->getData('year', date('Y')),
            'department_id' => (int)$this->getData('department_id', 0),
        ];

        $model = $this->model('Report');
        $data = $model->getPayrollReport($filters);
        
        $common = $this->getCommonFilters();

        $this->view('layouts/header', ['pageTitle' => 'Báo cáo Tiền lương']);
        $this->view('reports/payroll_report', array_merge($common, [
            'data'    => $data,
            'filters' => $filters
        ]));
        $this->view('layouts/footer');
    }

    /**
     * Báo cáo Tuyển dụng
     */
    public function recruitment(): void
    {
        Session::checkPermission(['Admin', 'HR_Manager']);

        $filters = [
            'year' => (int)$this->getData('year', date('Y')),
        ];

        $model = $this->model('Report');
        $data = $model->getRecruitmentReport($filters);

        $this->view('layouts/header', ['pageTitle' => 'Báo cáo Tuyển dụng']);
        $this->view('reports/recruitment', [
            'data'    => $data,
            'filters' => $filters
        ]);
        $this->view('layouts/footer');
    }

    /**
     * In báo cáo
     */
    public function printReport(string $type = ''): void
    {
        Session::checkPermission(['Admin', 'HR_Manager']);
        
        $model = $this->model('Report');
        $data = [];
        $title = "Báo cáo";

        switch ($type) {
            case 'headcount':
                $data = $model->getHeadcountReport(['status'=>'Active']);
                $title = "BÁO CÁO QUÂN SỐ HIỆN TẠI";
                break;
            case 'reward':
                $data = $model->getRewardReport(['year' => date('Y')]);
                $title = "BÁO CÁO KHEN THƯỞNG NĂM " . date('Y');
                break;
            case 'discipline':
                $data = $model->getDisciplineReport(['year' => date('Y')]);
                $title = "BÁO CÁO KỶ LUẬT NĂM " . date('Y');
                break;
            default:
                die('Loại báo cáo không hợp lệ');
        }

        $this->view('reports/print_report', [
            'type'  => $type,
            'title' => $title,
            'data'  => $data
        ]);
    }
}
