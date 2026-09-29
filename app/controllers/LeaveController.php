<?php
/**
 * ============================================================
 *  POSUNG HRIS – LeaveController (Nâng cấp chuẩn Frappe HRMS)
 * ============================================================
 *  Quản lý toàn diện quy trình Nghỉ phép:
 *  - Phân bổ quỹ phép năm (Leave Allocation, Auto-Allocate, Proration, Thâm niên)
 *  - Chuyển tiếp phép dư (Carry Forward) kèm thời hạn hiệu lực
 *  - Quản lý lịch nghỉ Lễ / Tết (Leave Holidays CRUD)
 *  - Động cơ tính ngày nghỉ thực tế (trừ Weekend + Holiday)
 *  - Kiểm soát chồng đơn (Overlap), hạn mức liên tục (Max Continuous Days)
 *  - API số dư (Leave Balance API) & Tích hợp Lịch nghỉ trực quan
 * ============================================================
 */

class LeaveController extends Controller
{
    protected Database $db;
    protected LeaveAllocation $allocationModel;
    protected LeaveRequest $leaveModel;
    protected LeaveHoliday $holidayModel;
    protected LeaveType $leaveTypeModel;

    public function __construct()
    {
        $this->checkPermission('leave.view');
        
        $this->db = Database::getInstance();
        $this->leaveModel = $this->model('LeaveRequest');
        $this->allocationModel = $this->model('LeaveAllocation');
        $this->holidayModel = $this->model('LeaveHoliday');
        $this->leaveTypeModel = $this->model('LeaveType');
    }

    /**
     * Dashboard Nghỉ phép chính
     */
    public function index(): void
    {
        $employeeId = Session::employeeId();
        $isManager = Session::isManager() || Session::isAdmin() || Session::isHR();
        $currentYear = (int)date('Y');

        // 1. Quỹ phép & Số dư chi tiết của bản thân
        $balances = [];
        $annualStats = [
            'entitled'  => 12.0,
            'carried'   => 0.0,
            'total'     => 12.0,
            'used'      => 0.0,
            'pending'   => 0.0,
            'remaining' => 12.0
        ];
        $sickUsed = 0.0;
        $unpaidUsed = 0.0;

        if ($employeeId) {
            $balances = $this->allocationModel->getEmployeeBalances($employeeId, $currentYear);
            foreach ($balances as $b) {
                if ($b['leave_type_id'] === 1 || $b['code'] === 'NP') {
                    $annualStats = [
                        'entitled'  => $b['entitled_days'],
                        'carried'   => $b['carried_forward_days'],
                        'total'     => $b['total_quota'],
                        'used'      => $b['used_days'],
                        'pending'   => $b['pending_days'],
                        'remaining' => $b['remaining_days']
                    ];
                } elseif ($b['code'] === 'NO') {
                    $sickUsed = $b['used_days'];
                } elseif ($b['code'] === 'NKL' || $b['is_paid'] === 0) {
                    $unpaidUsed = $b['used_days'];
                }
            }
        }

        // 2. Lịch sử đơn của bản thân
        $myRequests = $employeeId ? $this->leaveModel->getByEmployee($employeeId, $currentYear) : [];

        // 3. Đơn cần phê duyệt (Nếu là Quản lý / HR / Admin)
        $pendingRequests = [];
        if ($isManager) {
            $pendingRequests = $this->leaveModel->getPending();
        }

        // 4. Danh mục loại phép đang kích hoạt
        $leaveTypes = $this->leaveTypeModel->getActiveTypes();

        // 5. Các ngày nghỉ lễ sắp tới
        $upcomingHolidays = $this->holidayModel->getUpcomingHolidays(4);

        // 6. Dữ liệu sự kiện lịch (Approved leaves + Holidays)
        $calendarEvents = $this->leaveModel->getApprovedCalendarEvents($currentYear);
        $holidays = $this->holidayModel->getHolidays($currentYear);

        $this->view('layouts/header', ['pageTitle' => 'Quản lý Nghỉ phép – POSUNG HRIS']);
        $this->view('leave/index', [
            'employeeId'       => $employeeId,
            'isManager'        => $isManager,
            'currentYear'      => $currentYear,
            'balances'         => $balances,
            'annualStats'      => $annualStats,
            'sickUsed'         => $sickUsed,
            'unpaidUsed'       => $unpaidUsed,
            'myRequests'       => $myRequests,
            'pendingRequests'  => $pendingRequests,
            'leaveTypes'       => $leaveTypes,
            'upcomingHolidays' => $upcomingHolidays,
            'calendarEvents'   => $calendarEvents,
            'holidays'         => $holidays
        ]);
        $this->view('layouts/footer');
    }

    /**
     * Nộp đơn xin nghỉ phép (Đã nâng cấp: Động cơ tính ngày làm việc, Check Chồng đơn, Check Hạn mức)
     */
    public function store(): void
    {
        if (!$this->isPost()) {
            $this->redirect('leave');
            return;
        }

        $csrfToken = $this->postData('_csrf_token');
        if (!Session::validateCsrfToken($csrfToken)) {
            Session::setFlash('error', 'Phiên làm việc đã hết hạn hoặc mã bảo mật không khớp.');
            $this->redirect('leave');
            return;
        }

        $employeeId = (int)$this->postData('employee_id');
        if (!$employeeId) {
            $employeeId = Session::employeeId();
        }

        if (!$employeeId) {
            Session::setFlash('error', 'Tài khoản của bạn chưa được liên kết với hồ sơ nhân sự.');
            $this->redirect('leave');
            return;
        }

        $leaveTypeId = (int)$this->postData('leave_type_id');
        $startDate = trim($this->postData('start_date'));
        $endDate = trim($this->postData('end_date'));
        $reason = trim($this->postData('reason'));
        $customDays = $this->postData('total_days');

        // Validation cơ bản
        if (!$leaveTypeId || empty($startDate) || empty($endDate) || empty($reason)) {
            Session::setFlash('error', 'Vui lòng điền đầy đủ loại phép, khoảng thời gian và lý do nghỉ.');
            $this->redirect('leave');
            return;
        }

        if (strtotime($startDate) > strtotime($endDate)) {
            Session::setFlash('error', 'Ngày bắt đầu không được lớn hơn ngày kết thúc.');
            $this->redirect('leave');
            return;
        }

        // 1. Tự động tính số ngày nghỉ thực tế (Trừ Chủ Nhật và Ngày Lễ)
        $calc = $this->holidayModel->calculateWorkingDays($startDate, $endDate);
        $totalDays = $calc['working_days'];

        // Nếu người dùng chỉ định cụ thể (ví dụ nghỉ nửa ngày 0.5)
        if (!empty($customDays) && is_numeric($customDays) && (float)$customDays > 0) {
            $totalDays = (float)$customDays;
        }

        if ($totalDays <= 0) {
            Session::setFlash('error', 'Khoảng thời gian bạn chọn hoàn toàn rơi vào ngày Lễ hoặc ngày nghỉ cuối tuần (0 ngày làm việc).');
            $this->redirect('leave');
            return;
        }

        // 2. KIỂM TRA CHỒNG ĐƠN (Overlap Validation)
        $overlap = $this->leaveModel->checkOverlap($employeeId, $startDate, $endDate);
        if ($overlap) {
            $startFormatted = date('d/m/Y', strtotime($overlap['start_date']));
            $endFormatted = date('d/m/Y', strtotime($overlap['end_date']));
            Session::setFlash('error', "Phát hiện chồng đơn! Bạn đã có đơn xin nghỉ '{$overlap['leave_type_name']}' từ ngày {$startFormatted} đến {$endFormatted} (Trạng thái: {$overlap['status']}).");
            $this->redirect('leave');
            return;
        }

        // 3. KIỂM TRA CHÍNH SÁCH LOẠI PHÉP & SỐ DƯ
        $leaveType = $this->leaveTypeModel->findById($leaveTypeId);
        if (!$leaveType) {
            Session::setFlash('error', 'Loại phép không tồn tại trên hệ thống.');
            $this->redirect('leave');
            return;
        }

        // Kiểm tra số ngày nghỉ liên tục tối đa (Max continuous days)
        $maxContinuous = (int)($leaveType['max_continuous_days'] ?? 0);
        if ($maxContinuous > 0 && $totalDays > $maxContinuous) {
            Session::setFlash('error', "Loại phép '{$leaveType['name']}' quy định nghỉ liên tục tối đa {$maxContinuous} ngày làm việc. Đơn của bạn là {$totalDays} ngày.");
            $this->redirect('leave');
            return;
        }

        // Kiểm tra số dư khả dụng (nếu không cho phép âm và có lương)
        $allowNegative = (bool)($leaveType['allow_negative'] ?? 0);
        $reqYear = (int)date('Y', strtotime($startDate));

        if (!$allowNegative && (int)$leaveType['is_paid'] === 1) {
            $balances = $this->allocationModel->getEmployeeBalances($employeeId, $reqYear);
            $typeBalance = null;
            foreach ($balances as $b) {
                if ($b['leave_type_id'] === $leaveTypeId) {
                    $typeBalance = $b;
                    break;
                }
            }

            $availableDays = $typeBalance ? (float)$typeBalance['available_days'] : 0.0;
            if ($totalDays > $availableDays) {
                Session::setFlash('error', "Số ngày xin nghỉ ({$totalDays} ngày) vượt quá số dư khả dụng hiện tại của bạn ({$availableDays} ngày). Vui lòng điều chỉnh lại.");
                $this->redirect('leave');
                return;
            }
        }

        // 4. Lưu đơn nghỉ phép
        $insertData = [
            'employee_id'   => $employeeId,
            'leave_type_id' => $leaveTypeId,
            'start_date'    => $startDate,
            'end_date'      => $endDate,
            'total_days'    => $totalDays,
            'reason'        => $reason,
            'status'        => 'Pending'
        ];

        $requestId = $this->leaveModel->create($insertData);

        // Audit Log & Notification
        $this->auditLog('create', 'leave_request', $requestId, null, $insertData, "Nhân viên nộp đơn xin nghỉ phép từ {$startDate} đến {$endDate} ({$totalDays} ngày)");

        if (class_exists('NotificationService')) {
            NotificationService::notifyLeavePending((int)$requestId);
        }

        Session::setFlash('success', "Đã nộp đơn xin nghỉ phép thành công ({$totalDays} ngày làm việc tính phép). Đơn đang chờ quản lý xem xét!");
        $this->redirect('leave');
    }

    /**
     * Phê duyệt đơn xin phép (Tự động cập nhật used_days trong LeaveAllocation)
     */
    public function approve(int $id): void
    {
        $this->checkManagerOrHRAccess();

        if ($this->isPost()) {
            $approverNote = $this->postData('approver_note', 'Đồng ý phê duyệt');
            $success = $this->leaveModel->approveRequest($id, (int)Session::userId(), $approverNote);

            if ($success) {
                $this->auditLog('approve', 'leave_request', $id, ['status' => 'Pending'], ['status' => 'Approved'], "Phê duyệt đơn nghỉ phép: {$approverNote}");

                // Gửi thông báo đến nhân viên
                $this->db->query("SELECT lr.*, e.user_id FROM leave_requests lr JOIN employees e ON lr.employee_id = e.id WHERE lr.id = :id", ['id' => $id]);
                $req = $this->db->fetch();
                if ($req && !empty($req['user_id']) && class_exists('NotificationService')) {
                    NotificationService::send(
                        (int)$req['user_id'],
                        'approval',
                        'Đơn xin nghỉ phép đã được duyệt',
                        "Đơn xin nghỉ từ " . date('d/m/Y', strtotime($req['start_date'])) . " đến " . date('d/m/Y', strtotime($req['end_date'])) . " ({$req['total_days']} ngày) đã được duyệt.",
                        'leave'
                    );
                }

                Session::setFlash('success', 'Đã phê duyệt đơn xin nghỉ phép thành công. Quỹ phép của nhân viên đã được cập nhật.');
            } else {
                Session::setFlash('error', 'Có lỗi xảy ra khi phê duyệt đơn.');
            }
            $this->redirect('leave');
        }
    }

    /**
     * Từ chối đơn xin phép
     */
    public function reject(int $id): void
    {
        $this->checkManagerOrHRAccess();

        if ($this->isPost()) {
            $approverNote = $this->postData('approver_note', 'Từ chối');
            $success = $this->leaveModel->rejectRequest($id, (int)Session::userId(), $approverNote);

            if ($success) {
                $this->auditLog('reject', 'leave_request', $id, ['status' => 'Pending'], ['status' => 'Rejected'], "Từ chối đơn nghỉ phép: {$approverNote}");

                // Gửi thông báo đến nhân viên
                $this->db->query("SELECT lr.*, e.user_id FROM leave_requests lr JOIN employees e ON lr.employee_id = e.id WHERE lr.id = :id", ['id' => $id]);
                $req = $this->db->fetch();
                if ($req && !empty($req['user_id']) && class_exists('NotificationService')) {
                    NotificationService::send(
                        (int)$req['user_id'],
                        'announcement',
                        'Đơn xin nghỉ phép bị từ chối',
                        "Đơn xin nghỉ từ " . date('d/m/Y', strtotime($req['start_date'])) . " đến " . date('d/m/Y', strtotime($req['end_date'])) . " đã bị từ chối. Lý do: {$approverNote}",
                        'leave'
                    );
                }

                Session::setFlash('error', 'Đã từ chối đơn xin nghỉ phép.');
            } else {
                Session::setFlash('error', 'Có lỗi xảy ra khi từ chối đơn.');
            }
            $this->redirect('leave');
        }
    }

    // ══════════════════════════════════════════════════════════
    //  QUẢN LÝ QUỸ PHÉP NĂM (LEAVE ALLOCATIONS - FRAPPE PATTERN)
    // ══════════════════════════════════════════════════════════

    /**
     * Màn hình Quản lý Quỹ phép năm (HR Portal)
     */
    public function allocations(): void
    {
        $this->checkManagerOrHRAccess();

        $year = (int)$this->getData('year', date('Y'));
        $deptId = $this->getData('department_id') ? (int)$this->getData('department_id') : null;
        $search = $this->getData('search');
        $page = max(1, (int)$this->getData('page', 1));
        $limit = 50;
        $offset = ($page - 1) * $limit;

        $allocations = $this->allocationModel->getAllocations($year, $deptId, $search, $limit, $offset);
        $totalItems = $this->allocationModel->countAllocations($year, $deptId, $search);
        $totalPages = ceil($totalItems / $limit);
        $yearStats = $this->allocationModel->getYearStats($year);

        // Danh sách phòng ban phục vụ bộ lọc
        $deptModel = $this->model('Department');
        $departments = $deptModel->all();

        $this->view('layouts/header', ['pageTitle' => "Quản lý Phân bổ Quỹ phép năm {$year}"]);
        $this->view('leave/allocations', [
            'year'         => $year,
            'deptId'       => $deptId,
            'search'       => $search,
            'allocations'  => $allocations,
            'totalItems'   => $totalItems,
            'totalPages'   => $totalPages,
            'currentPage'  => $page,
            'yearStats'    => $yearStats,
            'departments'  => $departments
        ]);
        $this->view('layouts/footer');
    }

    /**
     * Tự động phân bổ quỹ phép theo năm (Proration + Thâm niên)
     */
    public function autoAllocate(): void
    {
        $this->checkManagerOrHRAccess();

        if ($this->isPost()) {
            $year = (int)$this->postData('year', date('Y'));
            $res = $this->allocationModel->autoAllocate($year, (int)Session::userId());

            $this->auditLog('auto_allocate', 'leave_allocations', null, null, $res, "Chạy tự động phân bổ quỹ phép năm {$year}");

            Session::setFlash('success', "Đã hoàn tất phân bổ phép năm {$year}! Xử lý: {$res['total_processed']} nhân viên (Tạo mới: {$res['created']}, Cập nhật: {$res['updated']}).");
            $this->redirect("leave/allocations?year={$year}");
        }
    }

    /**
     * Chuyển phép tồn năm cũ sang năm mới (Carry Forward)
     */
    public function carryForward(): void
    {
        $this->checkManagerOrHRAccess();

        if ($this->isPost()) {
            $fromYear = (int)$this->postData('from_year');
            $toYear = (int)$this->postData('to_year');

            if ($fromYear >= $toYear) {
                Session::setFlash('error', 'Năm chuyển đi phải nhỏ hơn năm chuyển đến.');
                $this->redirect("leave/allocations?year={$toYear}");
                return;
            }

            $res = $this->allocationModel->carryForward($fromYear, $toYear, (int)Session::userId());

            $this->auditLog('carry_forward', 'leave_allocations', null, null, $res, "Kết chuyển phép từ năm {$fromYear} sang năm {$toYear}");

            Session::setFlash('success', "Đã kết chuyển thành công {$res['total_days_carried']} ngày phép cho {$res['carried_count']} nhân viên sang năm {$toYear} (Hạn sử dụng phép tồn: " . date('d/m/Y', strtotime($res['expiry_date'])) . ").");
            $this->redirect("leave/allocations?year={$toYear}");
        }
    }

    /**
     * Cập nhật / Điều chỉnh thủ công phân bổ của một nhân viên
     */
    public function saveAllocation(): void
    {
        $this->checkManagerOrHRAccess();

        if ($this->isPost()) {
            $employeeId = (int)$this->postData('employee_id');
            $leaveTypeId = (int)$this->postData('leave_type_id', 1);
            $year = (int)$this->postData('year', date('Y'));
            $entitledDays = (float)$this->postData('entitled_days', 12.0);
            $carriedDays = (float)$this->postData('carried_forward_days', 0.0);
            $notes = trim($this->postData('notes', ''));

            $existing = $this->allocationModel->getByEmployeeAndYear($employeeId, $year, $leaveTypeId);
            $usedDays = $existing ? (float)$existing['used_days'] : 0.0;
            $remaining = max(0.0, ($entitledDays + $carriedDays) - $usedDays);

            if ($existing) {
                $this->db->query(
                    "UPDATE leave_allocations 
                     SET entitled_days = :entitled, carried_forward_days = :carried, 
                         remaining_days = :remaining, notes = :notes, updated_at = NOW() 
                     WHERE id = :id",
                    [
                        'entitled'  => $entitledDays,
                        'carried'   => $carriedDays,
                        'remaining' => $remaining,
                        'notes'     => $notes,
                        'id'        => $existing['id']
                    ]
                );
            } else {
                $this->db->query(
                    "INSERT INTO leave_allocations 
                     (employee_id, leave_type_id, year, entitled_days, carried_forward_days, used_days, remaining_days, effective_from, effective_to, created_by, notes, created_at)
                     VALUES 
                     (:emp, :type, :y, :entitled, :carried, 0.0, :remaining, :eff_from, :eff_to, :creator, :notes, NOW())",
                    [
                        'emp'       => $employeeId,
                        'type'      => $leaveTypeId,
                        'y'         => $year,
                        'entitled'  => $entitledDays,
                        'carried'   => $carriedDays,
                        'remaining' => $remaining,
                        'eff_from'  => "{$year}-01-01",
                        'eff_to'    => "{$year}-12-31",
                        'creator'   => Session::userId(),
                        'notes'     => $notes
                    ]
                );
            }

            Session::setFlash('success', 'Đã lưu điều chỉnh phân bổ quỹ phép thành công.');
            $this->redirect("leave/allocations?year={$year}");
        }
    }

    // ══════════════════════════════════════════════════════════
    //  QUẢN LÝ LỊCH NGHỈ LỄ (LEAVE HOLIDAYS CRUD)
    // ══════════════════════════════════════════════════════════

    /**
     * Quản lý lịch nghỉ lễ
     */
    public function holidays(): void
    {
        $year = (int)$this->getData('year', date('Y'));
        $holidays = $this->holidayModel->getHolidays($year);

        $deptModel = $this->model('Department');
        $departments = $deptModel->all();

        $this->view('layouts/header', ['pageTitle' => "Lịch nghỉ Lễ / Tết năm {$year}"]);
        $this->view('leave/holidays', [
            'year'        => $year,
            'holidays'    => $holidays,
            'departments' => $departments,
            'isManager'   => Session::isAdmin() || Session::isHR()
        ]);
        $this->view('layouts/footer');
    }

    /**
     * Thêm ngày nghỉ lễ mới
     */
    public function storeHoliday(): void
    {
        $this->checkManagerOrHRAccess();

        if ($this->isPost()) {
            $name = trim($this->postData('name'));
            $date = trim($this->postData('date'));
            $type = $this->postData('type', 'National');
            $isRecurring = (int)$this->postData('is_recurring', 0);
            $deptId = $this->postData('applies_to_department_id') ?: null;
            $description = trim($this->postData('description', ''));

            if (empty($name) || empty($date)) {
                Session::setFlash('error', 'Vui lòng nhập tên ngày lễ và ngày nghỉ.');
                $this->redirect('leave/holidays');
                return;
            }

            $this->holidayModel->addHoliday([
                'name'                     => $name,
                'date'                     => $date,
                'type'                     => $type,
                'is_recurring'             => $isRecurring,
                'applies_to_department_id' => $deptId,
                'description'              => $description
            ]);

            $year = date('Y', strtotime($date));
            Session::setFlash('success', "Đã thêm ngày nghỉ lễ '{$name}' thành công.");
            $this->redirect("leave/holidays?year={$year}");
        }
    }

    /**
     * Xóa ngày nghỉ lễ
     */
    public function deleteHoliday(int $id): void
    {
        $this->checkManagerOrHRAccess();

        if ($this->isPost()) {
            $this->holidayModel->deleteHoliday($id);
            Session::setFlash('success', 'Đã xóa ngày nghỉ lễ thành công.');
            $this->redirect('leave/holidays');
        }
    }

    // ══════════════════════════════════════════════════════════
    //  API AJAX ENDPOINTS
    // ══════════════════════════════════════════════════════════

    /**
     * API Số dư phép của nhân viên: GET /leave/balance/{employee_id}
     */
    public function balance(mixed $employeeId = null): void
    {
        $empId = $employeeId ? (int)$employeeId : Session::employeeId();
        if (!$empId) {
            $this->json(['success' => false, 'message' => 'Không tìm thấy mã nhân viên.'], 400);
        }

        $year = (int)$this->getData('year', date('Y'));
        $balances = $this->allocationModel->getEmployeeBalances($empId, $year);

        $this->json([
            'success'     => true,
            'employee_id' => $empId,
            'year'        => $year,
            'balances'    => $balances
        ]);
    }

    /**
     * API Tự động tính số ngày nghỉ làm việc: POST /leave/calcDays
     */
    public function calcDays(): void
    {
        $input = $this->getJsonInput();
        $startDate = $input['start_date'] ?? $this->postData('start_date');
        $endDate = $input['end_date'] ?? $this->postData('end_date');
        $empId = $input['employee_id'] ?? $this->postData('employee_id', Session::employeeId());

        if (empty($startDate) || empty($endDate)) {
            $this->json(['success' => false, 'message' => 'Vui lòng cung cấp khoảng thời gian.'], 400);
        }

        // Lấy department_id nếu có
        $deptId = null;
        if ($empId) {
            $this->db->query("SELECT department_id FROM employees WHERE id = :id LIMIT 1", ['id' => $empId]);
            $row = $this->db->fetch();
            $deptId = $row ? $row['department_id'] : null;
        }

        $result = $this->holidayModel->calculateWorkingDays($startDate, $endDate, $deptId);

        // Kiểm tra overlap luôn để cảnh báo ngay trên UI
        $overlap = null;
        if ($empId) {
            $overlap = $this->leaveModel->checkOverlap((int)$empId, $startDate, $endDate);
        }

        $this->json([
            'success'   => true,
            'data'      => $result,
            'has_overlap' => $overlap !== null,
            'overlap_info' => $overlap ? [
                'type'   => $overlap['leave_type_name'],
                'start'  => $overlap['start_date'],
                'end'    => $overlap['end_date'],
                'status' => $overlap['status']
            ] : null
        ]);
    }

    /**
     * API Dữ liệu sự kiện lịch (Approved Leaves + Holidays)
     */
    public function calendarEvents(): void
    {
        $year = (int)$this->getData('year', date('Y'));
        $month = $this->getData('month') ? (int)$this->getData('month') : null;

        $approvedLeaves = $this->leaveModel->getApprovedCalendarEvents($year, $month);
        $holidays = $this->holidayModel->getHolidays($year);

        $events = [];

        // 1. Thêm Holidays (Màu đỏ / Vàng cam)
        foreach ($holidays as $h) {
            $events[] = [
                'id'          => 'holiday_' . $h['id'],
                'title'       => '🎉 ' . $h['name'],
                'start'       => $h['date'],
                'end'         => $h['date'],
                'allDay'      => true,
                'color'       => $h['type'] === 'Company' ? '#f59e0b' : '#ef4444',
                'textColor'   => '#ffffff',
                'type'        => 'holiday',
                'description' => $h['description'] ?? ''
            ];
        }

        // 2. Thêm Leaves đã duyệt (Màu xanh dương / Xanh lá)
        foreach ($approvedLeaves as $l) {
            // FullCalendar end date is exclusive for allDay, so add 1 day if needed or handle
            $endInclusive = date('Y-m-d', strtotime($l['end_date'] . ' +1 day'));
            $events[] = [
                'id'          => 'leave_' . $l['id'],
                'title'       => $l['full_name'] . ' (' . $l['leave_type_name'] . ' - ' . $l['total_days'] . 'd)',
                'start'       => $l['start_date'],
                'end'         => $endInclusive,
                'allDay'      => true,
                'color'       => $l['is_paid'] ? '#3b82f6' : '#64748b',
                'textColor'   => '#ffffff',
                'type'        => 'leave',
                'description' => "Lý do: {$l['reason']} | Phòng ban: {$l['dept_name']}"
            ];
        }

        $this->json($events);
    }

    // ── Helper kiểm tra quyền Quản lý / HR ──
    private function checkManagerOrHRAccess(): void
    {
        if (!Session::isAdmin() && !Session::isHR() && !Session::isManager() && !Session::hasPermission('leave.allocations')) {
            Session::setFlash('error', 'Bạn không có quyền thực hiện thao tác này.');
            $this->redirect('leave');
            exit;
        }
    }
}
