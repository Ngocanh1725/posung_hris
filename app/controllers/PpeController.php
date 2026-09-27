<?php
/**
 * ============================================================
 *  POSUNG HRIS – PpeController (Quản lý Cấp phát BHLĐ)
 * ============================================================
 */

class PpeController extends Controller
{
    public function __construct()
    {
        if (!Session::isLoggedIn()) {
            $this->redirect('auth/login');
        }
        $this->requirePermission('employee', 'view'); // Tạm dùng quyền xem nhân sự
    }

    public function index(): void
    {
        $db = Database::getInstance();
        
        // Lấy danh sách cấp phát kèm thông tin nhân viên
        $db->query(
            "SELECT p.*, e.emp_code, e.full_name 
             FROM emp_ppe_issuances p
             JOIN employees e ON p.employee_id = e.id
             ORDER BY p.issue_date DESC, p.id DESC"
        );
        $issuances = $db->fetchAll();

        // Thống kê cho biểu đồ Chart.js
        $stats = [
            'Issued' => 0,
            'Returned' => 0,
            'Lost' => 0,
            'Damaged' => 0
        ];
        foreach ($issuances as $i) {
            $stats[$i['status']]++;
        }

        $this->view('layouts/header', ['pageTitle' => 'Quản lý Cấp phát PPE']);
        $this->view('ppe/index', [
            'issuances' => $issuances,
            'stats' => $stats
        ]);
        $this->view('layouts/footer');
    }
}
