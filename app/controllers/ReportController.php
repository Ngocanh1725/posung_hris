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
        $this->checkPermission('reports.view');
        
        $reportModel = $this->model('Report');
        $biData = $reportModel->getBiDashboardData();
        $biData['turnover_rate'] = $reportModel->getTurnoverRate(['year' => date('Y')]);
        $biData['labor_cost'] = $reportModel->getLaborCostByProject(['month' => date('m'), 'year' => date('Y')]);

        $this->view('layouts/header', ['pageTitle' => 'Báo cáo BI (Business Intelligence)']);
        $this->view('reports/index', ['biData' => $biData]);
        $this->view('layouts/footer');
    }

    /**
     * Báo cáo Quân số
     */
    public function headcount(): void
    {
        $this->checkPermission('reports.view');

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
        $this->checkPermission('reports.view');

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
        $this->checkPermission('reports.view');

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
        $this->checkPermission('reports.view');

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
        $this->checkPermission('reports.view');

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
        $this->checkPermission('reports.view');

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
        $this->checkPermission('reports.view');

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
     * Báo cáo Phân bổ Chi phí Lương Dự án (Project Labor Cost)
     */
    public function projectLaborCost(): void
    {
        $this->checkPermission('reports.view');

        $filters = [
            'month'         => (int)$this->getData('month', date('m')),
            'year'          => (int)$this->getData('year', date('Y')),
            'project_id'    => (int)$this->getData('project_id', 0),
            'position'      => $this->getData('position', ''),
        ];

        $model = $this->model('Report');
        
        // Custom query for detailed labor cost
        $sql = "SELECT pr.*, e.emp_code, e.full_name, p.pos_title, proj.project_name, proj.project_code
                FROM payrolls pr
                JOIN employees e ON pr.employee_id = e.id
                LEFT JOIN positions p ON e.position_id = p.id
                LEFT JOIN projects proj ON pr.project_id = proj.id
                WHERE pr.month = :m AND pr.year = :y";
                
        $params = ['m' => $filters['month'], 'y' => $filters['year']];
        
        if (!empty($filters['project_id'])) {
            $sql .= " AND pr.project_id = :proj";
            $params['proj'] = $filters['project_id'];
        }
        
        if (!empty($filters['position'])) {
            $sql .= " AND p.pos_title LIKE :pos";
            $params['pos'] = "%" . $filters['position'] . "%";
        }
        
        $sql .= " ORDER BY proj.project_code, e.emp_code";
        
        $db = Database::getInstance();
        $db->query($sql, $params);
        $data = $db->fetchAll();
        
        $common = $this->getCommonFilters();

        $this->view('layouts/header', ['pageTitle' => 'Báo cáo Chi phí Nhân công Dự án']);
        $this->view('reports/project_labor_cost', array_merge($common, [
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
        $this->checkPermission('reports.view');

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
        $this->checkPermission('reports.view');
        
        $model = $this->model('Report');
        $data = [];
        $title = "Báo cáo";

        switch ($type) {
            case 'headcount':
                $filters = [
                    'status'        => $this->getData('status', 'Active'),
                    'department_id' => (int)$this->getData('department_id', 0),
                    'project_id'    => (int)$this->getData('project_id', 0),
                    'employee_type' => $this->getData('employee_type', ''),
                ];
                $data = $model->getHeadcountReport($filters);
                $title = "BÁO CÁO QUÂN SỐ HIỆN TẠI";
                break;
            case 'reward':
                $filters = [
                    'year'          => (int)$this->getData('year', date('Y')),
                    'department_id' => (int)$this->getData('department_id', 0),
                ];
                $data = $model->getRewardReport($filters);
                $title = "BÁO CÁO KHEN THƯỞNG NĂM " . $filters['year'];
                break;
            case 'discipline':
                $filters = [
                    'year'        => (int)$this->getData('year', date('Y')),
                    'safety_only' => (int)$this->getData('safety_only', 0),
                ];
                $data = $model->getDisciplineReport($filters);
                $title = "BÁO CÁO KỶ LUẬT NĂM " . $filters['year'];
                break;
            case 'retirement':
                $filters = [
                    'within_months' => (int)$this->getData('months', 12)
                ];
                $data = $model->getRetirementReport($filters);
                $title = "BÁO CÁO HƯU TRÍ (TRONG " . $filters['within_months'] . " THÁNG TỚI)";
                break;
            case 'retirementDecision':
                $empId = (int)$this->getData('id', 0);
                $data = $model->getRetirementDecision($empId);
                $title = "QUYẾT ĐỊNH NGHỈ HƯU";
                break;
            case 'transfer':
                $filters = [
                    'year'   => (int)$this->getData('year', date('Y')),
                    'status' => $this->getData('status', '')
                ];
                $data = $model->getTransferReport($filters);
                $title = "BÁO CÁO THUYÊN CHUYỂN NĂM " . $filters['year'];
                break;
            case 'attendance':
                $filters = [
                    'month'         => (int)$this->getData('month', date('m')),
                    'year'          => (int)$this->getData('year', date('Y')),
                    'department_id' => (int)$this->getData('department_id', 0),
                ];
                $data = $model->getAttendanceReport($filters);
                $title = "BÁO CÁO CHẤM CÔNG THÁNG " . $filters['month'] . "/" . $filters['year'];
                break;
            case 'payroll':
                $filters = [
                    'month'         => (int)$this->getData('month', date('m')),
                    'year'          => (int)$this->getData('year', date('Y')),
                    'department_id' => (int)$this->getData('department_id', 0),
                ];
                $data = $model->getPayrollReport($filters);
                $title = "BÁO CÁO TIỀN LƯƠNG THÁNG " . $filters['month'] . "/" . $filters['year'];
                break;
            case 'recruitment':
                $filters = [
                    'year' => (int)$this->getData('year', date('Y')),
                ];
                $data = $model->getRecruitmentReport($filters);
                $title = "BÁO CÁO TUYỂN DỤNG NĂM " . $filters['year'];
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
