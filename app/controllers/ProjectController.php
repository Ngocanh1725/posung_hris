<?php
/**
 * ============================================================
 *  POSUNG HRIS – ProjectController
 * ============================================================
 *  Quản lý Dự án (Projects) – CRUD + thống kê nhân lực.
 * ============================================================
 */

class ProjectController extends Controller
{
    /**
     * Danh sách Dự án
     */
    public function index(): void
    {
        Session::checkPermission(['Admin', 'HR_Manager', 'Project_Manager', 'Site_Supervisor']);

        $db = Database::getInstance();

        // Lấy tất cả dự án kèm thống kê nhân lực
        $db->query(
            "SELECT p.*,
                    (SELECT COUNT(*) FROM employees e WHERE e.current_project_id = p.id AND e.status IN ('Active','Probation')) AS headcount,
                    (SELECT COUNT(*) FROM employees e WHERE e.current_project_id = p.id AND e.employee_type = 'Expat' AND e.status = 'Active') AS expat_count
             FROM projects p
             ORDER BY p.status ASC, p.project_code ASC"
        );
        $projects = json_decode(json_encode($db->fetchAll()));

        $this->view('layouts/header', ['pageTitle' => 'Quản lý Dự án']);
        $this->view('project/index', ['projects' => $projects]);
        $this->view('layouts/footer');
    }

    /**
     * Xử lý thêm / cập nhật dự án
     */
    public function store(): void
    {
        Session::checkPermission(['Admin', 'HR_Manager', 'Project_Manager']);

        if ($this->isPost()) {
            $projectModel = $this->model('Project');
            $id = (int) $this->postData('id', 0);
            
            $data = [
                'project_code'  => $this->postData('project_code'),
                'project_name'  => $this->postData('project_name'),
                'client_name'   => $this->postData('client_name'),
                'location'      => $this->postData('location'),
                'start_date'    => $this->postData('start_date') ?: null,
                'end_date'      => $this->postData('end_date') ?: null,
                'status'        => $this->postData('status', 'In_Progress'),
            ];

            try {
                if ($id > 0) {
                    $projectModel->update($id, $data);
                    Session::setFlash('success', 'Cập nhật dự án thành công!');
                } else {
                    $projectModel->create($data);
                    Session::setFlash('success', 'Thêm mới dự án thành công!');
                }
            } catch (Exception $e) {
                Session::setFlash('error', 'Lỗi: ' . $e->getMessage());
            }

            $this->redirect('project');
        }
    }
}
