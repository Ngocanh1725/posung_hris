<?php
/**
 * ============================================================
 *  POSUNG HRIS – AboutController
 * ============================================================
 *  Giới thiệu Công ty, Cơ cấu Tổ chức, Chức năng nhiệm vụ.
 * ============================================================
 */

class AboutController extends Controller
{
    /**
     * Trang Giới thiệu Công ty & Cơ cấu Tổ chức
     */
    public function index(): void
    {
        $this->checkPermission('employee.view');

        // Lấy thống kê nhân sự nhanh từ DB
        $db = Database::getInstance();

        $db->query("SELECT COUNT(*) as cnt FROM employees WHERE `status` IN ('Active','Probation')");
        $totalActive = (int)($db->fetch()['cnt'] ?? 0);

        $db->query("SELECT COUNT(*) as cnt FROM departments WHERE `status` = 'Active'");
        $totalDepts = (int)($db->fetch()['cnt'] ?? 0);

        $db->query("SELECT COUNT(*) as cnt FROM projects WHERE `status` = 'Active'");
        $totalProjects = (int)($db->fetch()['cnt'] ?? 0);

        $db->query("SELECT employee_type, COUNT(*) as cnt FROM employees WHERE `status` IN ('Active','Probation') GROUP BY employee_type");
        $typeBreakdown = [];
        foreach ($db->fetchAll() as $row) {
            $typeBreakdown[$row['employee_type']] = (int)$row['cnt'];
        }

        // Lấy danh sách phòng ban thật từ DB
        $db->query("SELECT d.id, d.dept_code, d.dept_name, d.description, d.functions,
                           (SELECT COUNT(*) FROM employees e WHERE e.department_id = d.id AND e.`status` IN ('Active','Probation')) as staff_count
                    FROM departments d WHERE d.`status` = 'Active' ORDER BY d.dept_code");
        $departments = $db->fetchAll();

        // Lấy danh sách nhân sự theo phòng ban
        $db->query("SELECT e.full_name, e.emp_code, p.pos_title, d.dept_code 
                    FROM employees e 
                    LEFT JOIN departments d ON e.department_id = d.id 
                    LEFT JOIN positions p ON e.position_id = p.id
                    WHERE e.`status` IN ('Active','Probation')");
        $employeesList = $db->fetchAll();
        $deptEmployees = [];
        foreach ($employeesList as $emp) {
            $code = $emp['dept_code'];
            if ($code) {
                if (!isset($deptEmployees[$code])) {
                    $deptEmployees[$code] = [];
                }
                $deptEmployees[$code][] = $emp;
            }
        }

        $this->view('layouts/header', ['pageTitle' => 'Giới thiệu Công ty']);
        $this->view('about/index', [
            'totalActive'   => $totalActive,
            'totalDepts'    => $totalDepts,
            'totalProjects' => $totalProjects,
            'typeBreakdown' => $typeBreakdown,
            'departments'   => $departments,
            'deptEmployees' => $deptEmployees
        ]);
        $this->view('layouts/footer');
    }

    /**
     * Trang Phân tích Thiết kế (DFD & ERD)
     */
    public function analysis(): void
    {
        $this->checkPermission('employee.view'); // Hoặc quyền phù hợp
        $this->view('layouts/header', ['pageTitle' => 'Phân tích Thiết kế Hệ thống']);
        $this->view('about/analysis');
        $this->view('layouts/footer');
    }
}
