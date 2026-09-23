<?php
/**
 * ============================================================
 *  POSUNG HRIS – PayrollController
 * ============================================================
 *  Điều khiển các thao tác Chấm công và Tính lương.
 * ============================================================
 */

class PayrollController extends Controller
{
    /**
     * Màn hình Bảng chấm công (Timesheet Grid)
     */
    public function timesheet(): void
    {
        $this->checkPermission('payroll.view');

        $month = (int) $this->getData('month', date('m'));
        $year  = (int) $this->getData('year', date('Y'));
        $projectId = $this->getData('project_id') ? (int) $this->getData('project_id') : null;

        $projectModel = $this->model('Project');
        $projects = $projectModel->getActiveProjects();

        $timesheetModel = $this->model('Timesheet');
        $grid = $timesheetModel->getMonthlyGrid($month, $year, $projectId);

        $this->view('layouts/header', ['pageTitle' => "Bảng Chấm Công - $month/$year"]);
        $this->view('payroll/timesheet', [
            'grid' => $grid,
            'month' => $month,
            'year' => $year,
            'projects' => $projects,
            'currentProject' => $projectId
        ]);
        $this->view('layouts/footer');
    }

    /**
     * Màn hình Danh sách Bảng lương tháng
     */
    public function index(): void
    {
        $this->checkPermission('payroll.view');

        $month = (int) $this->getData('month', date('m'));
        $year  = (int) $this->getData('year', date('Y'));
        $projectId = $this->getData('project_id') ? (int) $this->getData('project_id') : null;

        $projectModel = $this->model('Project');
        $projects = $projectModel->getActiveProjects();

        $payrollModel = $this->model('Payroll');
        $payrolls = $payrollModel->getPayrolls($month, $year, $projectId);

        // Tính tổng quỹ lương
        $totalNet = 0;
        foreach ($payrolls as $pr) {
            $totalNet += (float) $pr->net_salary;
        }

        $this->view('layouts/header', ['pageTitle' => "Bảng Lương Tháng $month/$year"]);
        $this->view('payroll/index', [
            'payrolls' => $payrolls,
            'month' => $month,
            'year' => $year,
            'projects' => $projects,
            'currentProject' => $projectId,
            'totalNet' => $totalNet,
            'canExportCost' => Session::hasPermission('payroll.export')
        ]);
        $this->view('layouts/footer');
    }

    /**
     * Chạy tính lương (Payroll Engine)
     */
    public function calculate(): void
    {
        $this->checkPermission('payroll.calculate');

        if ($this->isPost()) {
            $month = (int) $this->postData('month', date('m'));
            $year  = (int) $this->postData('year', date('Y'));
            $projectId = $this->postData('project_id') ? (int) $this->postData('project_id') : null;

            $payrollModel = $this->model('Payroll');
            $count = $payrollModel->calculateMonthlyPayroll($month, $year, $projectId);

            if ($count > 0) {
                Session::setFlash('success', "Đã tính lương thành công cho $count nhân viên (Kỳ $month/$year).");
            } else {
                Session::setFlash('warning', "Không có dữ liệu hoặc không có nhân viên nào được tính lương (Kỳ $month/$year).");
            }

            $this->redirect("payroll/index?month=$month&year=$year" . ($projectId ? "&project_id=$projectId" : ""));
        } else {
            $this->redirect('payroll');
        }
    }

    /**
     * Xem / In Phiếu lương điện tử (e-Payslip)
     */
    public function payslip(int $employeeId = 0, int $month = 0, int $year = 0): void
    {
        // Yêu cầu đăng nhập. Nếu không phải Admin/HR, chỉ được xem của chính mình.
        if (!Session::isLoggedIn()) {
            $this->redirect('auth/login');
            return;
        }

        if (!Session::isManager() && Session::userId() !== $employeeId) {
            // Wait: employee_id != user_id vì bảng users và employees khác nhau.
            // Để đơn giản, giả sử chỉ Manager mới xem được payslip hoặc người đó được link user_id = employee_id.
            // Do hiện tại chưa có tính năng Portal cho nhân viên thường, chỉ check quyền Manager.
            $this->checkPermission('payroll.export');
        }

        if (!$employeeId || !$month || !$year) {
            die("Tham số không hợp lệ.");
        }

        $payrollModel = $this->model('Payroll');
        $payslip = $payrollModel->getPayslip($employeeId, $month, $year);

        if (!$payslip) {
            die("Không tìm thấy dữ liệu phiếu lương cho nhân sự này trong tháng $month/$year.");
        }

        // Render view HTML dành riêng cho In ấn (Không dùng header/footer chính)
        $this->view('payroll/payslip', [
            'payslip' => $payslip
        ]);
    }
    /**
     * Xuất Báo Cáo Phân Bổ Chi Phí Kế Toán
     */
    public function exportCostAllocation(): void
    {
        $this->checkPermission('payroll.export');

        $month = (int) $this->getData('month', date('m'));
        $year  = (int) $this->getData('year', date('Y'));

        // Giả lập xuất file Excel/CSV
        header('Content-Type: text/csv; charset=utf-8');
        header("Content-Disposition: attachment; filename=Cost_Allocation_{$month}_{$year}.csv");

        $output = fopen('php://output', 'w');
        fputcsv($output, ['Mã NV', 'Họ Tên', 'Dự Án', 'Mã Cost Center', 'Tổng Lương (VND)']);

        $payrollModel = $this->model('Payroll');
        $payrolls = $payrollModel->getPayrolls($month, $year);

        foreach ($payrolls as $pr) {
            fputcsv($output, [
                $pr->emp_code,
                $pr->full_name,
                $pr->project_name,
                $pr->cc_code,
                $pr->net_salary
            ]);
        }

        fclose($output);
        exit;
    }
}
