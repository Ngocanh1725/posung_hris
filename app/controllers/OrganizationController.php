<?php
/**
 * ============================================================
 *  POSUNG HRIS – OrganizationController
 * ============================================================
 *  Hiển thị Sơ đồ Tổ chức (Org Chart) từ bảng departments.
 * ============================================================
 */

class OrganizationController extends Controller
{
    /**
     * Sơ đồ tổ chức dạng cây phân cấp
     */
    public function index(): void
    {
        Session::checkPermission(['Admin', 'HR_Manager', 'Project_Manager', 'Site_Supervisor']);

        $deptModel = $this->model('Department');
        $tree = $deptModel->getTree();

        // Thống kê quân số từng phòng ban
        $allDepts = $deptModel->all('dept_code ASC');
        $deptStats = [];
        foreach ($allDepts as $dept) {
            $deptStats[$dept['id']] = [
                'dept_name'  => $dept['dept_name'],
                'dept_code'  => $dept['dept_code'],
                'branch'     => $dept['branch'] ?? '',
                'headcount'  => $deptModel->getHeadcountStats((int)$dept['id']),
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
}
