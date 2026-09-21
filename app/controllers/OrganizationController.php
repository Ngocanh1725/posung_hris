<?php
/**
 * ============================================================
 *  POSUNG HRIS – OrganizationController (V2)
 * ============================================================
 */

class OrganizationController extends Controller
{
    /**
     * Sơ đồ tổ chức & Ma trận Dự án (Tabs)
     * URL: /organization  hoặc /organization/index
     */
    public function index(): void
    {
        Session::checkPermission(['admin', 'hr_manager', 'project_manager']);

        $deptModel = $this->model('Department');
        $projectModel = $this->model('Project');

        // Sơ đồ phòng ban hành chính
        $tree = $deptModel->getTreeWithDetails();
        $allDepts = $deptModel->allWithManager();
        
        $deptStats = [];
        foreach ($allDepts as $dept) {
            $deptStats[$dept['id']] = [
                'name'         => $dept['name'],
                'code'         => $dept['code'],
                'type'         => $dept['type'],
                'manager_name' => $dept['manager_name'] ?? 'Chưa bổ nhiệm',
                'headcount'    => $deptModel->getHeadcountStats((int)$dept['id']),
            ];
        }

        // Ma trận Ban Quản lý Dự án
        $projects = $projectModel->getAllProjects();
        $projectStats = [];
        foreach ($projects as $prj) {
            $stats = $projectModel->getHeadcountStats((int)$prj['id']);
            $projectStats[$prj['id']] = $stats;
        }

        $this->view('layouts/header', ['pageTitle' => 'Sơ đồ Tổ chức & PMB']);
        $this->view('organization/index', [
            'tree'         => $tree,
            'deptStats'    => $deptStats,
            'allDepts'     => $allDepts,
            'projects'     => $projects,
            'projectStats' => $projectStats
        ]);
        $this->view('layouts/footer');
    }

    /**
     * Tổng quan Cơ cấu Tổ chức
     * URL: /organization/overview
     */
    public function overview(): void
    {
        Session::checkPermission(['admin', 'hr_manager', 'project_manager']);

        $deptModel = $this->model('Department');
        $summary   = $deptModel->getOrgSummary();

        // 3 Cụm chính
        $officeDepts  = $deptModel->getDeptByType('office');
        $siteDepts    = $deptModel->getDeptByType('site_pmb');
        $factoryDepts = $deptModel->getDeptByType('factory');

        $this->view('layouts/header', ['pageTitle' => 'Tổng quan Nhân lực']);
        $this->view('organization/overview', [
            'summary'      => $summary,
            'officeDepts'  => $officeDepts,
            'siteDepts'    => $siteDepts,
            'factoryDepts' => $factoryDepts,
        ]);
        $this->view('layouts/footer');
    }

    /**
     * API trả về dữ liệu Tree cho JS (Google Charts hoặc D3)
     * URL: /organization/apiGetTree
     */
    public function apiGetTree(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            Session::checkPermission(['admin', 'hr_manager', 'project_manager']);
            $deptModel = $this->model('Department');
            $tree = $deptModel->getTreeWithDetails();
            echo json_encode(['success' => true, 'data' => $tree]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized or Error']);
        }
        exit;
    }
}
