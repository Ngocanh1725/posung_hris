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
        $this->checkPermission('employee.view');

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
        $this->checkPermission('employee.view');

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
        $orgChart = $projectModel->buildProjectOrgChart($id);

        // Lấy lịch sử thuyên chuyển của dự án
        $db = Database::getInstance();
        $db->query("
            SELECT jm.*, e.emp_code, e.full_name, p.pos_title, d.dept_name
            FROM job_movements jm
            JOIN employees e ON jm.employee_id = e.id
            LEFT JOIN positions p ON e.position_id = p.id
            LEFT JOIN departments d ON jm.from_dept_id = d.id OR jm.to_dept_id = d.id
            WHERE jm.to_project_id = :id1 OR jm.from_project_id = :id2
            ORDER BY jm.effective_date DESC
        ", ['id1' => $id, 'id2' => $id]);
        $movements = $db->fetchAll();

        // Lấy thông tin HSE (Sự cố & Vi phạm)
        $db->query("
            SELECT i.*, e.full_name, e.emp_code, d.dept_name 
            FROM hse_incidents i 
            LEFT JOIN employees e ON i.emp_id = e.id 
            LEFT JOIN departments d ON e.department_id = d.id 
            WHERE i.project_id = :id 
            ORDER BY i.incident_date DESC
        ", ['id' => $id]);
        $hseIncidents = $db->fetchAll();

        $db->query("
            SELECT v.*, e.full_name, e.emp_code, d.dept_name 
            FROM hse_violations v 
            LEFT JOIN employees e ON v.emp_id = e.id 
            LEFT JOIN departments d ON e.department_id = d.id 
            WHERE v.project_id = :id 
            ORDER BY v.violation_date DESC
        ", ['id' => $id]);
        $hseViolations = $db->fetchAll();

        $this->view('layouts/header', ['pageTitle' => 'Chi tiết Dự án: ' . $project['project_name']]);
        $this->view('project/detail', [
            'project'       => $project,
            'stats'         => $stats,
            'personnel'     => $personnel,
            'orgChart'      => $orgChart,
            'movements'     => $movements,
            'hseIncidents'  => $hseIncidents,
            'hseViolations' => $hseViolations
        ]);
        $this->view('layouts/footer');
    }

    public function store(): void
    {
        $this->checkPermission('category.manage');

        if ($this->isPost()) {
            $projectModel = $this->model('Project');
            $id = (int) $this->postData('id', 0);
            
            $data = [
                'project_code'     => $this->postData('project_code'),
                'project_name'     => $this->postData('project_name'), // Fixed field name
                'client_name'      => $this->postData('client_name'),
                'location'         => $this->postData('location'),
                'start_date'       => $this->postData('start_date') ?: null,
                'end_date'         => $this->postData('end_date') ?: null,
                'status'           => $this->postData('status', 'in_progress'),
                'cost_center_code' => $this->postData('cost_center_code'),
                'headcount_budget' => (int)$this->postData('headcount_budget', 0),
                'site_manager_id'  => $this->postData('site_manager_id') ? (int)$this->postData('site_manager_id') : null,
                'hse_lead_id'      => $this->postData('hse_lead_id') ? (int)$this->postData('hse_lead_id') : null,
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

    /**
     * Lấy danh sách nhân viên chưa có dự án (dùng cho AJAX)
     */
    public function getAvailableEmployees(): void
    {
        $db = Database::getInstance();
        $db->query("SELECT id, emp_code, full_name, department_id FROM employees WHERE (current_project_id IS NULL OR current_project_id = 0) AND status = 'Active'");
        $emps = $db->fetchAll();
        echo json_encode(['success' => true, 'data' => $emps]);
    }

    /**
     * AJAX: Phân bổ nhân sự vào dự án
     */
    public function ajaxAssignPersonnel(): void
    {
        if (!$this->isPost(false)) return;

        $projectId = (int)$this->postData('project_id');
        $employeeIds = $_POST['employee_ids'] ?? [];

        if ($projectId > 0 && !empty($employeeIds)) {
            $db = Database::getInstance();
            try {
                $db->beginTransaction();
                foreach ($employeeIds as $empId) {
                    $empId = (int)$empId;
                    // Update employee table
                    $db->query("UPDATE employees SET current_project_id = :pid WHERE id = :eid", [
                        'pid' => $projectId,
                        'eid' => $empId
                    ]);
                    
                    // Log to job_movements
                    $db->query("INSERT INTO job_movements (employee_id, movement_type, to_project_id, effective_date, status) VALUES (:eid, 'transfer', :pid, CURDATE(), 'approved')", [
                        'eid' => $empId,
                        'pid' => $projectId
                    ]);
                }
                $db->commit();
                echo json_encode(['success' => true]);
            } catch (Exception $e) {
                $db->rollBack();
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
        }
    }

    /**
     * AJAX: Rút nhân sự khỏi dự án
     */
    public function ajaxRemovePersonnel(): void
    {
        if (!$this->isPost(false)) return;

        $projectId = (int)$this->postData('project_id');
        $employeeId = (int)$this->postData('employee_id');

        if ($projectId > 0 && $employeeId > 0) {
            $db = Database::getInstance();
            try {
                $db->beginTransaction();
                
                // Set current_project_id to NULL for the employee
                $db->query("UPDATE employees SET current_project_id = NULL WHERE id = :eid", [
                    'eid' => $employeeId
                ]);
                
                // Log to job_movements
                $db->query("INSERT INTO job_movements (employee_id, movement_type, from_project_id, effective_date, status) VALUES (:eid, 'transfer', :pid, CURDATE(), 'approved')", [
                    'eid' => $employeeId,
                    'pid' => $projectId
                ]);
                
                $db->commit();
                echo json_encode(['success' => true]);
            } catch (Exception $e) {
                $db->rollBack();
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
        }
    }

    /**
     * AJAX: AI Smart Matcher (Gợi ý nhân sự theo Keyword)
     */
    public function ajaxSmartMatch(): void
    {
        if (!$this->isPost(false)) return;

        $keyword = trim($this->postData('keyword', ''));
        if (empty($keyword)) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng nhập từ khóa']);
            return;
        }

        $db = Database::getInstance();
        $likeKeyword = "%" . $keyword . "%";

        // Query tìm kiếm những người chưa có dự án, khớp keyword ở chức danh hoặc chứng chỉ
        $sql = "
            SELECT e.id, e.emp_code, e.full_name, e.pos_title, 
                   GROUP_CONCAT(c.certificate_name SEPARATOR ', ') as matched_certs
            FROM employees e
            LEFT JOIN employee_certificates c ON e.id = c.employee_id
            WHERE (e.current_project_id IS NULL OR e.current_project_id = 0)
              AND e.status = 'Active'
              AND (
                  e.pos_title LIKE :kw 
                  OR e.full_name LIKE :kw 
                  OR c.certificate_name LIKE :kw
              )
            GROUP BY e.id
        ";
        $db->query($sql, ['kw' => $likeKeyword]);
        $results = $db->fetchAll();

        // Tính điểm đơn giản (Score)
        $scoredResults = [];
        foreach ($results as $r) {
            $score = 0;
            if (stripos($r['pos_title'] ?? '', $keyword) !== false) $score += 50;
            if (stripos($r['matched_certs'] ?? '', $keyword) !== false) $score += 30;
            if (stripos($r['full_name'] ?? '', $keyword) !== false) $score += 10;
            
            $r['match_score'] = $score;
            $scoredResults[] = $r;
        }

        // Sắp xếp theo score giảm dần
        usort($scoredResults, function($a, $b) {
            return $b['match_score'] <=> $a['match_score'];
        });

        echo json_encode(['success' => true, 'data' => $scoredResults]);
    }
}
