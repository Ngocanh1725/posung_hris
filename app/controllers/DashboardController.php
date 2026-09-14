<?php
/**
 * ============================================================
 *  POSUNG HRIS – Controller Trang tổng quan (Dashboard)
 * ============================================================
 *  Trang chủ sau khi đăng nhập, hiển thị các chỉ số KPI
 *  và cảnh báo quan trọng.
 * ============================================================
 */

class DashboardController extends Controller
{
    /**
     * Trang tổng quan – hiển thị sau khi đăng nhập thành công.
     *
     * @return void
     */
    public function index(): void
    {
        // Yêu cầu đăng nhập (tất cả role đều được xem Dashboard)
        Session::checkPermission([]);

        // Lấy dữ liệu thống kê từ CSDL
        $db = Database::getInstance();

        // Tổng số nhân viên đang làm việc
        $db->query("SELECT COUNT(*) AS total FROM employees WHERE status IN ('Active','Probation')");
        $totalEmployees = $db->fetch()['total'] ?? 0;

        // Số dự án đang triển khai
        $db->query("SELECT COUNT(*) AS total FROM projects WHERE status = 'In_Progress'");
        $totalProjects = $db->fetch()['total'] ?? 0;

        // Số chuyên gia nước ngoài (Expat)
        $db->query("SELECT COUNT(*) AS total FROM employees WHERE employee_type = 'Expat' AND status = 'Active'");
        $totalExpats = $db->fetch()['total'] ?? 0;

        // Số chứng chỉ sắp hết hạn trong 90 ngày
        $db->query(
            "SELECT COUNT(*) AS total FROM certificates
             WHERE expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 90 DAY)"
        );
        $expiringCerts = $db->fetch()['total'] ?? 0;

        // Số visa/GPLĐ/TRC sắp hết hạn trong 90 ngày
        $db->query(
            "SELECT COUNT(*) AS total FROM expat_details
             WHERE visa_expiry      BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 90 DAY)
                OR work_permit_expiry BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 90 DAY)
                OR trc_expiry         BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 90 DAY)"
        );
        $expiringExpat = $db->fetch()['total'] ?? 0;

        // Nhân viên mới trong tháng
        $db->query(
            "SELECT COUNT(*) AS total FROM employees
             WHERE MONTH(join_date) = MONTH(CURDATE())
               AND YEAR(join_date)  = YEAR(CURDATE())"
        );
        $newThisMonth = $db->fetch()['total'] ?? 0;

        // Dữ liệu cho Biểu đồ 1: Tháp nhân lực theo phân loại
        $db->query(
            "SELECT employee_type, COUNT(*) AS count
             FROM employees
             WHERE status IN ('Active','Probation')
             GROUP BY employee_type"
        );
        $employeeTypesData = $db->fetchAll();

        // Dữ liệu cho Biểu đồ 2: Tỷ trọng lương theo Cost Center (Dự án)
        $db->query(
            "SELECT p.project_name, SUM(pr.net_salary) AS total_cost
             FROM payrolls pr
             JOIN projects p ON pr.cost_center_id = p.id
             WHERE pr.year = YEAR(CURDATE()) AND pr.month = MONTH(CURDATE())
             GROUP BY pr.cost_center_id"
        );
        $payrollCostData = $db->fetchAll();

        // Nạp View với Layout (header + content + footer)
        $this->view('layouts/header', [
            'pageTitle' => 'Tổng quan',
        ]);

        $this->view('dashboard/index', [
            'totalEmployees'    => $totalEmployees,
            'totalProjects'     => $totalProjects,
            'totalExpats'       => $totalExpats,
            'expiringCerts'     => $expiringCerts,
            'expiringExpat'     => $expiringExpat,
            'newThisMonth'      => $newThisMonth,
            'employeeTypesData' => $employeeTypesData,
            'payrollCostData'   => $payrollCostData,
        ]);

        $this->view('layouts/footer');
    }
}
