<?php
/**
 * ============================================================
 *  POSUNG HRIS – LeaveController
 * ============================================================
 *  Quản lý quy trình Xin nghỉ phép, Phê duyệt nghỉ phép và
 *  Tính toán quỹ phép năm (Leave Balance).
 * ============================================================
 */

class LeaveController extends Controller
{
    public function __construct()
    {
        // Ai cũng có thể vào mục này (Nhân viên xin phép, Quản lý duyệt phép)
        Session::checkPermission([]);
    }

    /**
     * Dashboard Nghỉ phép (Danh sách đơn của bản thân, Quỹ phép, và Đơn cần duyệt nếu là Manager)
     */
    public function index(): void
    {
        $userId = Session::userId();
        $employeeId = Session::employeeId();
        $isManager = Session::isManager();

        $leaveModel = $this->model('LeaveRequest');
        
        // 1. Lấy danh sách đơn của bản thân
        $myRequests = $employeeId ? $leaveModel->getByEmployee($employeeId) : [];

        // 2. Nếu là quản lý, lấy danh sách chờ duyệt
        $pendingRequests = [];
        if ($isManager) {
            $pendingRequests = $leaveModel->getPending();
        }

        // 3. Lấy danh mục Loại phép
        $leaveTypeModel = $this->model('LeaveType');
        $leaveTypes = $leaveTypeModel->all();

        // 4. Tính toán quỹ phép năm của bản thân
        $annualLeaveTotal = 12; // Mặc định 12 ngày/năm
        $usedLeave = 0;
        foreach ($myRequests as $req) {
            if ($req->status === 'Approved' && stripos($req->leave_type_name, 'Phép năm') !== false) {
                // Tính số ngày (bỏ qua cuối tuần nếu cần, ở đây tính đơn giản)
                $usedLeave += (int) $req->days;
            }
        }
        $balance = $annualLeaveTotal - $usedLeave;

        $this->view('layouts/header', ['pageTitle' => 'Nghỉ phép']);
        $this->view('leave/index', [
            'myRequests' => $myRequests,
            'pendingRequests' => $pendingRequests,
            'leaveTypes' => $leaveTypes,
            'isManager' => $isManager,
            'annualLeaveTotal' => $annualLeaveTotal,
            'usedLeave' => $usedLeave,
            'balance' => $balance
        ]);
        $this->view('layouts/footer');
    }

    /**
     * Nộp đơn xin nghỉ phép
     */
    public function store(): void
    {
        if ($this->isPost()) {
            $employeeId = Session::employeeId();
            if (!$employeeId) {
                Session::setFlash('error', 'Tài khoản của bạn chưa được liên kết với hồ sơ nhân sự.');
                $this->redirect('leave');
                return;
            }

            $startDate = $this->postData('start_date');
            $endDate = $this->postData('end_date');
            
            // Tính số ngày (Đơn giản: (end - start) + 1)
            $diff = strtotime($endDate) - strtotime($startDate);
            $days = round($diff / 86400) + 1;
            
            // Nếu người dùng nhập ngày thực tế (nửa ngày v.v.) thì lấy từ form
            if ($this->postData('days')) {
                $days = (float)$this->postData('days');
            }

            $data = [
                'employee_id' => $employeeId,
                'leave_type_id' => $this->postData('leave_type_id'),
                'start_date' => $startDate,
                'end_date' => $endDate,
                'days' => $days,
                'reason' => $this->postData('reason'),
                'status' => 'Pending'
            ];

            $leaveModel = $this->model('LeaveRequest');
            $leaveModel->create($data);
            
            Session::setFlash('success', 'Đã nộp đơn xin nghỉ phép. Vui lòng chờ phê duyệt!');
            $this->redirect('leave');
        }
    }

    /**
     * Phê duyệt đơn
     */
    public function approve(int $id): void
    {
        Session::checkPermission(['Admin', 'HR_Manager', 'Project_Manager', 'Site_Supervisor']);
        
        if ($this->isPost()) {
            $leaveModel = $this->model('LeaveRequest');
            $data = [
                'status' => 'Approved',
                'approver_id' => Session::userId(),
                'approver_note' => $this->postData('approver_note', 'Đồng ý')
            ];
            $leaveModel->update($id, $data);
            Session::setFlash('success', 'Đã phê duyệt đơn xin nghỉ phép.');
            $this->redirect('leave');
        }
    }

    /**
     * Từ chối đơn
     */
    public function reject(int $id): void
    {
        Session::checkPermission(['Admin', 'HR_Manager', 'Project_Manager', 'Site_Supervisor']);
        
        if ($this->isPost()) {
            $leaveModel = $this->model('LeaveRequest');
            $data = [
                'status' => 'Rejected',
                'approver_id' => Session::userId(),
                'approver_note' => $this->postData('approver_note', 'Từ chối')
            ];
            $leaveModel->update($id, $data);
            Session::setFlash('error', 'Đã từ chối đơn xin nghỉ phép.');
            $this->redirect('leave');
        }
    }
}
