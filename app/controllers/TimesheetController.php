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
}
