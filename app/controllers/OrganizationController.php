<?php
/**
 * ============================================================
 *  POSUNG HRIS – OrganizationController (V3 Interactive Org Chart)
 * ============================================================
 */

class OrganizationController extends Controller
{
    /**
     * Danh sách phòng ban & Ma trận Dự án (List + Grid View)
     * URL: /organization hoặc /organization/index
     */
    public function index(): void
    {
        $this->checkPermission('employee.view');

        $deptModel = $this->model('Department');
        $projectModel = $this->model('Project');

        // Thống kê tổng hợp phòng ban
        $statistics = $deptModel->getDepartmentStatistics();
        $allDepts   = $deptModel->allWithManager();
        $tree       = $deptModel->getTreeWithDetails();

        // Ma trận Ban Quản lý Dự án
        $projects = $projectModel->getAllProjects();
        $projectStats = [];
        foreach ($projects as $prj) {
            $stats = $projectModel->getHeadcountStats((int)$prj['id']);
            $projectStats[$prj['id']] = $stats;
        }

        $this->view('layouts/header', ['pageTitle' => 'Cơ Cấu Phòng Ban & Dự Án']);
        $this->view('organization/index', [
            'statistics'   => $statistics,
            'allDepts'     => $allDepts,
            'tree'         => $tree,
            'projects'     => $projects,
            'projectStats' => $projectStats
        ]);
        $this->view('layouts/footer');
    }

    /**
     * Sơ đồ Tổ chức dạng Cây tương tác (Interactive Org Chart)
     * URL: /organization/chart
     */
    public function chart(): void
    {
        $this->checkPermission('employee.view');

        $deptModel = $this->model('Department');
        $tree       = $deptModel->getTreeWithDetails();
        $statistics = $deptModel->getDepartmentStatistics();
        $hierarchy  = $deptModel->getChartHierarchy();

        $this->view('layouts/header', ['pageTitle' => 'Sơ đồ Tổ chức Trực quan (Org Chart)']);
        $this->view('organization/chart', [
            'tree'       => $tree,
            'statistics' => $statistics,
            'hierarchy'  => $hierarchy,
        ]);
        $this->view('layouts/footer');
    }

    /**
     * API trả về dữ liệu Tree cho JS / AJAX OrgChart
     * URL: /organization/getChartData hoặc /organization/apiGetTree
     */
    public function getChartData(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $this->checkPermission('employee.view');
            $deptModel = $this->model('Department');
            $hierarchy = $deptModel->getChartHierarchy();
            $stats     = $deptModel->getDepartmentStatistics();

            echo json_encode([
                'success' => true,
                'data'    => $hierarchy,
                'summary' => [
                    'total_depts'     => $stats['total_depts'],
                    'total_employees' => $stats['total_employees'],
                    'largest_dept'    => $stats['largest_dept']['name'] ?? '',
                ]
            ], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Alias giữ tương thích URL cũ
     */
    public function apiGetTree(): void
    {
        $this->getChartData();
    }

    /**
     * Thống kê nhân sự theo phòng ban (Biểu đồ phân tích chuyên sâu)
     * URL: /organization/statistics
     */
    public function statistics(): void
    {
        $this->checkPermission('employee.view');

        $deptModel  = $this->model('Department');
        $statistics = $deptModel->getDepartmentStatistics();
        $allDepts   = $deptModel->allWithManager('employee_count DESC');

        $this->view('layouts/header', ['pageTitle' => 'Thống kê Cơ cấu Nhân sự Phòng ban']);
        $this->view('organization/statistics', [
            'statistics' => $statistics,
            'allDepts'   => $allDepts,
        ]);
        $this->view('layouts/footer');
    }

    /**
     * Tổng quan Cơ cấu Tổ chức (Cụm phân cấp)
     * URL: /organization/overview
     */
    public function overview(): void
    {
        $this->checkPermission('employee.view');

        $deptModel = $this->model('Department');
        $summary   = $deptModel->getOrgSummary();

        // 3 Cụm chính
        $hqDepts      = $deptModel->getDeptByType('division') ?: $deptModel->getDeptByType('bod');
        $officeDepts  = $deptModel->getDeptByType('office') ?: $deptModel->getDeptByType('department');
        $projectDepts = $deptModel->getDeptByType('site_pmb') ?: $deptModel->getDeptByType('factory');

        $this->view('layouts/header', ['pageTitle' => 'Tổng quan Cơ cấu Tổ chức']);
        $this->view('organization/overview', [
            'summary'      => $summary,
            'hqDepts'      => $hqDepts,
            'officeDepts'  => $officeDepts,
            'projectDepts' => $projectDepts,
        ]);
        $this->view('layouts/footer');
    }

    /**
     * Chi tiết Bộ phận
     * URL: /organization/detail/{id}
     */
    public function detail(int $id): void
    {
        $this->checkPermission('employee.view');
        
        $deptModel = $this->model('Department');
        $dept = $deptModel->getDetailById($id);
        
        if (!$dept) {
            $this->redirect('organization');
        }
        
        $parent = null;
        if (!empty($dept['parent_id'])) {
            $parent = $deptModel->getDetailById((int)$dept['parent_id']);
        }
        
        $employees = $deptModel->getEmployees($id);
        $children  = $deptModel->getChildren($id);
        
        // Parse functions if any
        $functions = [];
        if (!empty($dept['functions'])) {
            $functions = explode("\n", str_replace("\r", "", $dept['functions']));
            $functions = array_filter(array_map('trim', $functions));
        }

        $this->view('layouts/header', ['pageTitle' => 'Chi tiết: ' . ($dept['dept_name'] ?? '')]);
        $this->view('organization/detail', [
            'dept'      => $dept,
            'parent'    => $parent,
            'employees' => $employees,
            'children'  => $children,
            'functions' => $functions
        ]);
        $this->view('layouts/footer');
    }
}
