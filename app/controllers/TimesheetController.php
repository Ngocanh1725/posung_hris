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
        Session::checkPermission(['Admin', 'HR_Manager', 'Project_Manager', 'Site_Supervisor']);

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
        $count = $timesheetModel->syncData($payload);

        echo json_encode(['status' => 'success', 'message' => "Synced $count records"]);
    }

    /**
     * Khóa bảng công (Lock Timesheet)
     */
    public function lock(): void
    {
        Session::checkPermission(['Admin', 'Project_Manager']);
        
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
}
