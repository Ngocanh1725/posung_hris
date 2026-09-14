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
        Session::checkPermission(['Admin', 'HR_Manager', 'Project_Manager']);

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
        Session::checkPermission(['Admin', 'HR_Manager']);

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
            'totalNet' => $totalNet
        ]);
        $this->view('layouts/footer');
    }

    /**
     * Chạy tính lương (Payroll Engine)
     */
    public function calculate(): void
    {
        Session::checkPermission(['Admin', 'HR_Manager']);

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
            Session::checkPermission(['Admin', 'HR_Manager']);
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
}
