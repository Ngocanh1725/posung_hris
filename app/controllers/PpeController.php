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

        // Thêm cảnh báo sắp đến hạn thay thế
        $thirtyDaysFromNow = date('Y-m-d', strtotime('+30 days'));
        $db->query("
            SELECT p.*, e.emp_code, e.full_name 
            FROM emp_ppe_issuances p
            JOIN employees e ON p.employee_id = e.id
            WHERE p.status = 'Issued' AND p.next_replacement_date IS NOT NULL 
              AND p.next_replacement_date <= :thirtyDays
            ORDER BY p.next_replacement_date ASC
        ", ['thirtyDays' => $thirtyDaysFromNow]);
        $expiringPpe = $db->fetchAll();

        $this->view('layouts/header', ['pageTitle' => 'Quản lý Cấp phát PPE']);
        $this->view('ppe/index', [
            'issuances' => $issuances,
            'stats' => $stats,
            'expiringPpe' => $expiringPpe
        ]);
        $this->view('layouts/footer');
    }

    public function store(): void
    {
        if ($this->isPost()) {
            $employee_id = $this->postData('employee_id');
            $ppe_item = $this->postData('ppe_item_name');
            $serial_no = $this->postData('serial_no');
            $issue_date = $this->postData('issue_date');
            
            // Tính chu kỳ cấp mới tự động (ví dụ: Giày 6 tháng, Mũ 12 tháng)
            $months = 6;
            if (stripos($ppe_item, 'Mũ') !== false || stripos($ppe_item, 'Dây đai') !== false) {
                $months = 12;
            }
            $next_replacement = date('Y-m-d', strtotime("+$months months", strtotime($issue_date)));

            $db = Database::getInstance();
            $db->query(
                "INSERT INTO emp_ppe_issuances (employee_id, ppe_item_name, serial_no, quantity, issue_date, next_replacement_date, status) 
                 VALUES (:emp, :item, :serial, 1, :iss, :next, 'Issued')",
                [
                    'emp' => $employee_id, 'item' => $ppe_item, 'serial' => $serial_no,
                    'iss' => $issue_date, 'next' => $next_replacement
                ]
            );
            Session::setFlash('success', "Đã cấp $ppe_item. Hạn cấp mới tiếp theo: " . date('d/m/Y', strtotime($next_replacement)));
            $this->redirect('ppe/index');
        }
    }
}
