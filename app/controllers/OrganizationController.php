<?php
/**
 * ============================================================
 *  POSUNG HRIS – OrganizationController
 * ============================================================
 *  Quản lý cơ cấu tổ chức: Sơ đồ Tổ chức, Tổng quan,
 *  và Chi tiết từng bộ phận.
 * ============================================================
 */

class OrganizationController extends Controller
{
    /**
     * Tổng quan Cơ cấu Tổ chức
     * URL: /organization/overview
     */
    public function overview(): void
    {
        Session::checkPermission(['Admin', 'HR_Manager', 'Project_Manager', 'Site_Supervisor']);

        $deptModel = $this->model('Department');
        $summary   = $deptModel->getOrgSummary();

        // Lấy bộ phận theo loại
        $hqDepts      = $deptModel->getDeptByType('Division');
        $officeDepts   = $deptModel->getDeptByType('Department');
        $projectDepts  = $deptModel->getDeptByType('Project');

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
     * Sơ đồ tổ chức dạng cây phân cấp
     * URL: /organization  hoặc /organization/index
     */
    public function index(): void
    {
        Session::checkPermission(['Admin', 'HR_Manager', 'Project_Manager', 'Site_Supervisor']);

        $deptModel = $this->model('Department');
        $tree = $deptModel->getTreeWithDetails();

        // Thống kê quân số từng phòng ban
        $allDepts = $deptModel->allWithManager();
        $deptStats = [];
        foreach ($allDepts as $dept) {
            $deptStats[$dept['id']] = [
                'dept_name'    => $dept['dept_name'],
                'dept_code'    => $dept['dept_code'],
                'branch'       => $dept['branch'] ?? '',
                'dept_type'    => $dept['dept_type'] ?? 'Department',
                'manager_name' => $dept['manager_name'] ?? '',
                'description'  => $dept['description'] ?? '',
                'headcount'    => $deptModel->getHeadcountStats((int)$dept['id']),
            ];
        }

        $this->view('layouts/header', ['pageTitle' => 'Sơ đồ Tổ chức']);
        $this->view('organization/index', [
            'tree'      => $tree,
            'deptStats' => $deptStats,
            'allDepts'  => $allDepts
        ]);
        $this->view('layouts/footer');
    }

    /**
     * Chi tiết bộ phận
     * URL: /organization/detail/{id}
     */
    public function detail(int $id): void
    {
        Session::checkPermission(['Admin', 'HR_Manager', 'Project_Manager', 'Site_Supervisor']);

        $deptModel = $this->model('Department');
        $dept = $deptModel->getDetailById($id);

        if (!$dept) {
            Session::setFlash('error', 'Không tìm thấy bộ phận.');
            $this->redirect('organization');
            return;
        }

        // Lấy danh sách NV thuộc bộ phận
        $employees = $deptModel->getEmployees($id);

        // Lấy bộ phận con
        $children = $deptModel->getChildren($id);

        // Lấy bộ phận cha (nếu có)
        $parent = null;
        if (!empty($dept['parent_id'])) {
            $parent = $deptModel->getDetailById((int)$dept['parent_id']);
        }

        // Parse functions JSON
        $functions = [];
        if (!empty($dept['functions'])) {
            $functions = json_decode($dept['functions'], true) ?: [];
        }

        $this->view('layouts/header', ['pageTitle' => $dept['dept_name']]);
        $this->view('organization/detail', [
            'dept'      => $dept,
            'employees' => $employees,
            'children'  => $children,
            'parent'    => $parent,
            'functions' => $functions,
        ]);
        $this->view('layouts/footer');
    }
}
