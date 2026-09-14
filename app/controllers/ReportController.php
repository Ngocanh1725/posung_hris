<?php
/**
 * ============================================================
 *  POSUNG HRIS – ReportController
 * ============================================================
 *  Trang Báo cáo & Biểu mẫu (Mẫu 2C).
 * ============================================================
 */

class ReportController extends Controller
{
    /**
     * Trang báo cáo tổng hợp
     */
    public function index(): void
    {
        Session::checkPermission(['Admin', 'HR_Manager', 'Project_Manager']);

        $db = Database::getInstance();

        // Thống kê tổng quân số theo loại
        $db->query(
            "SELECT employee_type, status, COUNT(*) AS cnt
             FROM employees
             GROUP BY employee_type, status
             ORDER BY employee_type, status"
        );
        $headcountByType = $db->fetchAll();

        // Thống kê theo phòng ban
        $db->query(
            "SELECT d.dept_name, d.dept_code, COUNT(e.id) AS cnt
             FROM departments d
             LEFT JOIN employees e ON d.id = e.department_id AND e.status IN ('Active','Probation')
             GROUP BY d.id
             ORDER BY d.dept_code"
        );
        $headcountByDept = $db->fetchAll();

        // Thống kê theo dự án
        $db->query(
            "SELECT p.project_name, p.project_code, COUNT(e.id) AS cnt
             FROM projects p
             LEFT JOIN employees e ON p.id = e.current_project_id AND e.status IN ('Active','Probation')
             WHERE p.status = 'In_Progress'
             GROUP BY p.id
             ORDER BY p.project_code"
        );
        $headcountByProject = $db->fetchAll();

        // Danh sách nhân viên để in mẫu 2C
        $db->query(
            "SELECT id, emp_code, full_name 
             FROM employees 
             WHERE status IN ('Active','Probation') 
             ORDER BY emp_code ASC"
        );
        $employees = $db->fetchAll();

        $this->view('layouts/header', ['pageTitle' => 'Báo cáo & Biểu mẫu']);
        $this->view('reports/index', [
            'headcountByType'    => $headcountByType,
            'headcountByDept'    => $headcountByDept,
            'headcountByProject' => $headcountByProject,
            'employees'          => $employees
        ]);
        $this->view('layouts/footer');
    }
}
