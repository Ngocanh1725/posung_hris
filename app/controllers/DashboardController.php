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
        // Yêu cầu đăng nhập
        if (!Session::isLoggedIn()) {
            $this->redirect('auth/login');
            return;
        }

        // Nếu là tài khoản Nhân viên (Employee), chuyển sang Cổng tự phục vụ ESS
        if (Session::isEmployee()) {
            $this->redirect('ess');
            return;
        }

        // Lấy dữ liệu thống kê từ CSDL
        $db = Database::getInstance();

        // Card 1: Tổng quân số Văn phòng vs Công trường
        $db->query("SELECT current_project_id FROM employees WHERE status IN ('Active','Probation')");
        $allActive = $db->fetchAll();
        $headcountOffice = 0;
        $headcountSite = 0;
        foreach ($allActive as $e) {
            if (empty($e['current_project_id'])) $headcountOffice++;
            else $headcountSite++;
        }
        $totalEmployees = $headcountOffice + $headcountSite;

        // Card 2: Số dự án đang thi công
        $db->query("SELECT COUNT(*) AS total FROM projects WHERE status = 'In_Progress'");
        $totalProjects = $db->fetch()['total'] ?? 0;

        // Card 3: Cảnh báo HSE (Thẻ 1-6 và Chứng chỉ hành nghề)
        $today = date('Y-m-d');
        $sixtyDays = date('Y-m-d', strtotime('+60 days'));
        
        $db->query("SELECT COUNT(*) AS total FROM hse_safety_cards WHERE expiry_date <= :t", ['t' => $sixtyDays]);
        $hseWarnings = $db->fetch()['total'] ?? 0;
        
        $db->query("SELECT COUNT(*) AS total FROM employee_certificates WHERE expiry_date <= :t", ['t' => $sixtyDays]);
        $certWarnings = $db->fetch()['total'] ?? 0;
        
        $totalHseWarnings = $hseWarnings + $certWarnings;

        // Card 4: Tổng giờ OT và Cảnh báo vượt trần (Trên 40h/tháng)
        $currentMonth = date('n');
        $currentYear = date('Y');
        
        $db->query("
            SELECT employee_id, SUM(COALESCE(ot_normal_hours, 0) + COALESCE(ot_sunday_hours, 0) + COALESCE(ot_holiday_hours, 0)) as total_ot
            FROM timesheets
            WHERE MONTH(work_date) = :m AND YEAR(work_date) = :y
            GROUP BY employee_id
        ", ['m' => $currentMonth, 'y' => $currentYear]);
        $otData = $db->fetchAll();
        
        $totalOtHours = 0;
        $otWarningsCount = 0;
        foreach ($otData as $ot) {
            $totalOtHours += $ot['total_ot'];
            if ($ot['total_ot'] > 40) {
                $otWarningsCount++;
            }
        }

        // Tính Turnover Rate (Tỷ lệ nghỉ việc)
        $db->query("SELECT COUNT(*) as total FROM employees WHERE status = 'Resigned' AND MONTH(updated_at) = :m AND YEAR(updated_at) = :y", ['m' => $currentMonth, 'y' => $currentYear]);
        $resignedThisMonth = $db->fetch()['total'] ?? 0;
        
        // Số người đầu kỳ = Tổng số hiện tại - Số mới vào trong tháng + Số nghỉ trong tháng
        $db->query("SELECT COUNT(*) as total FROM employees WHERE status IN ('Active','Probation') AND MONTH(join_date) = :m AND YEAR(join_date) = :y", ['m' => $currentMonth, 'y' => $currentYear]);
        $newThisMonth = $db->fetch()['total'] ?? 0;
        
        $headcountEnd = $totalEmployees;
        $headcountStart = $headcountEnd - $newThisMonth + $resignedThisMonth;
        
        $avgHeadcount = ($headcountStart + $headcountEnd) / 2;
        $turnoverRate = $avgHeadcount > 0 ? round(($resignedThisMonth / $avgHeadcount) * 100, 2) : 0;

        // Biểu đồ 1: Nhân sự theo dự án
        $db->query("
            SELECT p.project_code, COUNT(e.id) as emp_count
            FROM employees e
            JOIN projects p ON e.current_project_id = p.id
            WHERE e.status IN ('Active','Probation')
            GROUP BY p.id
        ");
        $projectEmpData = $db->fetchAll();

        // Biểu đồ 2: Lương & OT 6 tháng qua
        $months = [];
        $salaryData = [];
        $otPayData = [];
        for ($i = 5; $i >= 0; $i--) {
            $m = date('n', strtotime("-$i months"));
            $y = date('Y', strtotime("-$i months"));
            $months[] = "T$m/$y";
            
            $db->query("SELECT SUM(net_salary) as total_net, SUM(ot_pay) as total_ot FROM payrolls WHERE month = :m AND year = :y", ['m' => $m, 'y' => $y]);
            $res = $db->fetch();
            $salaryData[] = round(($res['total_net'] ?? 0) / 1000000, 2); // Triệu VNĐ
            $otPayData[] = round(($res['total_ot'] ?? 0) / 1000000, 2);
        }

        // Biểu đồ 3: HSE Training (Tỷ lệ nhóm)
        $db->query("SELECT group_type, COUNT(*) as count FROM hse_safety_cards GROUP BY group_type");
        $hseGroupData = $db->fetchAll();

        $this->view('layouts/header', [
            'pageTitle' => 'Executive Dashboard',
        ]);

        $this->view('dashboard/index', [
            'totalEmployees'    => $totalEmployees,
            'headcountOffice'   => $headcountOffice,
            'headcountSite'     => $headcountSite,
            'totalProjects'     => $totalProjects,
            'totalHseWarnings'  => $totalHseWarnings,
            'totalOtHours'      => $totalOtHours,
            'otWarningsCount'   => $otWarningsCount,
            'turnoverRate'      => $turnoverRate,
            'projectEmpData'    => $projectEmpData,
            'chartMonths'       => $months,
            'salaryData'        => $salaryData,
            'otPayData'         => $otPayData,
            'hseGroupData'      => $hseGroupData
        ]);

        $this->view('layouts/footer');
    }
}
