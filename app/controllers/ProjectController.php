<?php
/**
 * ============================================================
 *  POSUNG HRIS – ProjectController (V2)
 * ============================================================
 */

class ProjectController extends Controller
{
    /**
     * Danh sách Dự án
     * URL: /project
     */
    public function index(): void
    {
        Session::checkPermission(['admin', 'hr_manager', 'project_manager']);

        $projectModel = $this->model('Project');
        $allProjects = $projectModel->getAllProjects();

        // Gắn thêm số liệu thống kê định biên cho từng dự án
        $projects = [];
        foreach ($allProjects as $prj) {
            $stats = $projectModel->getHeadcountStats((int)$prj['id']);
            $prj['stats'] = $stats;
            
            // Tính số lượng expat riêng lẻ từ DB
            $db = Database::getInstance();
            $db->query("SELECT COUNT(*) as cnt FROM employees WHERE current_project_id = :id AND is_expat = 1 AND status IN ('active', 'probation')", ['id' => $prj['id']]);
            $prj['expat_count'] = (int)($db->fetch()['cnt'] ?? 0);
            
            $projects[] = $prj;
        }

        $this->view('layouts/header', ['pageTitle' => 'Ma Trận Quản Lý Dự Án']);
        $this->view('project/index', ['projects' => $projects]);
        $this->view('layouts/footer');
    }

    /**
     * Xem chi tiết Dự án & Nhân sự hiện hành
     * URL: /project/detail/{id}
     */
    public function detail(int $id): void
    {
        Session::checkPermission(['admin', 'hr_manager', 'project_manager']);

        $projectModel = $this->model('Project');
        $project = $projectModel->find($id);

        if (!$project) {
            Session::setFlash('error', 'Không tìm thấy dự án.');
            $this->redirect('project');
            return;
        }

        // Ép kiểu object -> array để view dễ xài nếu model trả về object
        $project = (array)$project;

        $stats = $projectModel->getHeadcountStats($id);
        $personnel = $projectModel->getActivePersonnel($id);

        $this->view('layouts/header', ['pageTitle' => 'Chi tiết Dự án: ' . $project['name']]);
        $this->view('project/detail', [
            'project'   => $project,
            'stats'     => $stats,
            'personnel' => $personnel
        ]);
        $this->view('layouts/footer');
    }

    /**
     * Xử lý thêm / cập nhật dự án
     */
    public function store(): void
    {
        Session::checkPermission(['admin', 'hr_manager', 'project_manager']);

        if ($this->isPost()) {
            $projectModel = $this->model('Project');
            $id = (int) $this->postData('id', 0);
            
            $data = [
                'project_code'     => $this->postData('project_code'),
                'name'             => $this->postData('name'),
                'client_name'      => $this->postData('client_name'),
                'location'         => $this->postData('location'),
                'start_date'       => $this->postData('start_date') ?: null,
                'end_date'         => $this->postData('end_date') ?: null,
                'status'           => $this->postData('status', 'in_progress'),
                'cost_center_code' => $this->postData('cost_center_code'),
                'headcount_quota'  => (int)$this->postData('headcount_quota', 0),
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
