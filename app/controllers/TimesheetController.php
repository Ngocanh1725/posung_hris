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

    /**
     * API: Nhận tọa độ GPS chấm công từ Mobile App / Trình duyệt
     */
    public function checkinGps(): void
    {
        header('Content-Type: application/json');
        
        // Kiểm tra CSRF Token cho API
        $csrfToken = $this->postData('csrf_token');
        if (!Session::verifyCsrfToken($csrfToken)) {
            echo json_encode(['status' => 'error', 'message' => 'Lỗi bảo mật CSRF Token. Vui lòng tải lại trang.']);
            return;
        }

        $lat = (float) $this->postData('latitude');
        $lng = (float) $this->postData('longitude');
        $empId = (int) $this->postData('employee_id');
        $projectId = (int) $this->postData('project_id');
        $inOut = $this->postData('in_out') === 'OUT' ? 'OUT' : 'IN';

        if (!$lat || !$lng || !$empId || !$projectId) {
            echo json_encode(['status' => 'error', 'message' => 'Thiếu thông tin tọa độ hoặc dự án!']);
            return;
        }

        $locModel = $this->model('TimekeepingLocation');
        $locations = $locModel->getLocationsByProject($projectId);

        if (empty($locations)) {
            echo json_encode(['status' => 'error', 'message' => 'Dự án này chưa được thiết lập tọa độ chấm công.']);
            return;
        }

        $isValid = false;
        $minDistance = 999999;
        
        // Công thức Haversine tính khoảng cách (meters)
        $earthRadius = 6371000;
        foreach ($locations as $loc) {
            $siteLat = (float) $loc['latitude'];
            $siteLng = (float) $loc['longitude'];
            $allowedRadius = (int) $loc['allowed_radius_meters'];
            
            $latFrom = deg2rad($lat);
            $lonFrom = deg2rad($lng);
            $latTo = deg2rad($siteLat);
            $lonTo = deg2rad($siteLng);

            $latDelta = $latTo - $latFrom;
            $lonDelta = $lonTo - $lonFrom;

            $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) + cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
            $distance = $angle * $earthRadius;
            
            if ($distance < $minDistance) {
                $minDistance = $distance;
            }

            if ($distance <= $allowedRadius) {
                $isValid = true;
                break;
            }
        }

        if (!$isValid) {
            echo json_encode([
                'status' => 'error', 
                'message' => 'Bạn đang ở ngoài bán kính công trường. Khoảng cách hiện tại: ' . round($minDistance) . 'm. Vui lòng di chuyển vào đúng khu vực quy định.'
            ]);
            return;
        }

        // Lưu chấm công
        $timesheetModel = $this->model('Timesheet');
        $workDate = date('Y-m-d');
        $time = date('H:i:s');
        
        $success = $timesheetModel->saveGpsCheckin($empId, $projectId, $workDate, $time, $lat, $lng, round($minDistance), $inOut);

        if ($success) {
            echo json_encode(['status' => 'success', 'message' => "Chấm công $inOut thành công lúc $time! Khoảng cách: " . round($minDistance) . "m."]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Lỗi lưu chấm công. Bảng công có thể đã bị khóa.']);
        }
    }

    /**
     * Workflow: Duyệt bảng công công trường 3 cấp
     */
    public function approvalSiteWorkflow(): void
    {
        $this->checkPermission('timesheet.manage');

        if ($this->isPost()) {
            $ids = isset($_POST['timesheet_ids']) ? $_POST['timesheet_ids'] : [];
            $status = $this->postData('approval_status'); // PENDING_SITE_MANAGER, PENDING_HR, APPROVED, REJECTED
            
            if (empty($ids) || !$status) {
                Session::setFlash('error', 'Chưa chọn bản ghi hoặc thiếu trạng thái duyệt!');
                $this->redirect('timesheet/workflow_site');
                return;
            }

            $timesheetModel = $this->model('Timesheet');
            $success = $timesheetModel->updateApprovalStatus($ids, $status);

            if ($success) {
                Session::setFlash('success', "Đã duyệt/cập nhật " . count($ids) . " bản ghi sang trạng thái: $status.");
            } else {
                Session::setFlash('error', 'Lỗi khi cập nhật trạng thái duyệt.');
            }
            $this->redirect('timesheet/workflow_site');
        } else {
            // View danh sách chờ duyệt
            $this->view('layouts/header', ['pageTitle' => 'Duyệt Công (Site Workflow)']);
            
            // Tạm thời fetch tất cả records (nên filter theo role trong thực tế)
            $timesheetModel = $this->model('Timesheet');
            $month = (int) $this->getData('month', date('m'));
            $year  = (int) $this->getData('year', date('Y'));
            $projectId = $this->getData('project_id') ? (int) $this->getData('project_id') : null;
            
            $grid = $timesheetModel->getMonthlyGrid($month, $year, $projectId);
            
            $projectModel = $this->model('Project');
            $projects = $projectModel->getActiveProjects();
            
            $this->view('timesheet/workflow_site', [
                'grid' => $grid,
                'projects' => $projects,
                'currentProject' => $projectId,
                'month' => $month,
                'year' => $year
            ]);
            $this->view('layouts/footer');
        }
    }
    
    /**
     * Màn hình Chấm công Mobile (Giao diện Mobile-first)
     */
    public function mobileCheckin(): void
    {
        // Require login
        if (!Session::isLoggedIn()) {
            $this->redirect('auth/login');
            return;
        }

        $employeeId = Session::userId(); // Thực tế nên là employee_id liên kết với user
        $empModel = $this->model('Employee');
        $emp = $empModel->find($employeeId); // Giả sử user id = employee id hoặc mapping
        
        $projectModel = $this->model('Project');
        $projects = $projectModel->getActiveProjects();
        
        $this->view('layouts/header', ['pageTitle' => 'Chấm Công GPS (Mobile)']);
        $this->view('timesheet/mobile_checkin', [
            'employeeId' => $employeeId,
            'projects' => $projects
        ]);
        $this->view('layouts/footer');
    }
}
