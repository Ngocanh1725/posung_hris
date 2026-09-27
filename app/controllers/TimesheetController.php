<?php
/**
 * ============================================================
 *  POSUNG HRIS – TimesheetController
 * ============================================================
 *  Sidebar trỏ tới /timesheet. Controller này hiển thị
 *  bảng chấm công (tái sử dụng logic từ PayrollController).
 * ============================================================
 */

class TimesheetController extends Controller
{
    /**
     * Màn hình Bảng chấm công (Timesheet Grid)
     */
    public function index(): void
    {
        $this->checkPermission('timesheet.view');

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
     * API đồng bộ dữ liệu từ thiết bị chấm công
     */
    public function sync(): void
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
            return;
        }

        $input = file_get_contents('php://input');
        $payload = json_decode($input, true);

        if (!$payload || !is_array($payload)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid JSON payload']);
            return;
        }

        $timesheetModel = $this->model('Timesheet');
        $count = $timesheetModel->importEdgeData($payload);

        echo json_encode(['status' => 'success', 'message' => "Synced $count records"]);
    }

    /**
     * Khóa bảng công (Lock Timesheet)
     */
    public function lock(): void
    {
        $this->checkPermission('timesheet.view');
        
        if ($this->isPost()) {
            $month = (int) $this->postData('month');
            $year = (int) $this->postData('year');
            $projectId = (int) $this->postData('project_id');

            if (!$projectId) {
                Session::setFlash('error', 'Vui lòng chọn dự án để khóa.');
                $this->redirect("timesheet?month=$month&year=$year");
                return;
            }

            $timesheetModel = $this->model('Timesheet');
            $success = $timesheetModel->lockTimesheet($month, $year, $projectId);

            if ($success) {
                Session::setFlash('success', 'Đã khóa bảng công thành công.');
            } else {
                Session::setFlash('error', 'Lỗi khi khóa bảng công.');
            }
            $this->redirect("timesheet?month=$month&year=$year&project_id=$projectId");
        }
    }

    /**
     * Nhập/Sửa chấm công thủ công từ giao diện Web
     */
    public function manualInput(): void
    {
        $this->checkPermission('timesheet.view');

        if ($this->isPost()) {
            $employeeId = (int) $this->postData('employee_id');
            $workDate = $this->postData('work_date');
            $checkIn = $this->postData('check_in') ?: null;
            $checkOut = $this->postData('check_out') ?: null;
            $isCleanroom = (int) $this->postData('is_cleanroom');
            
            $month = (int)date('m', strtotime($workDate));
            $year = (int)date('Y', strtotime($workDate));

            $timesheetModel = $this->model('Timesheet');
            $success = $timesheetModel->saveManualTimesheet($employeeId, $workDate, $checkIn, $checkOut, $isCleanroom);

            if ($success) {
                Session::setFlash('success', 'Đã lưu dữ liệu chấm công thủ công.');
            } else {
                Session::setFlash('error', 'Lỗi khi lưu (Bảng công đã khóa hoặc sai thông tin).');
            }
            
            $this->redirect("payroll/timesheet?month=$month&year=$year");
        }
    }
}
