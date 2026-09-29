<?php
/**
 * ============================================================
 *  POSUNG HRIS – Employee Self-Service (ESS) Controller
 * ============================================================
 *  Cổng tự phục vụ dành cho Nhân viên (Employee Portal):
 *  - Nhân viên chỉ được xem và thao tác dữ liệu của chính mình
 *  - Sử dụng Session::employeeId() làm định danh duy nhất
 *  - Bảo mật tuyệt đối chống tấn công IDOR
 * ============================================================
 */

class EssController extends Controller
{
    /** @var int|null ID của nhân viên đang đăng nhập */
    protected ?int $employeeId = null;

    /** @var int|null ID tài khoản user */
    protected ?int $userId = null;

    /** @var Database Đối tượng kết nối CSDL */
    protected Database $db;

    public function __construct()
    {
        // 1. Bắt buộc đăng nhập
        if (!Session::isLoggedIn()) {
            Session::setFlash('error', 'Vui lòng đăng nhập để truy cập Cổng nhân viên.');
            $this->redirect('auth/login');
            exit;
        }

        $this->db = Database::getInstance();
        $this->userId = Session::userId();
        $this->employeeId = Session::employeeId();

        // 2. Nếu session chưa có employee_id, truy vấn lại từ bảng users
        if (!$this->employeeId && $this->userId) {
            $this->db->query("SELECT employee_id FROM users WHERE id = :id LIMIT 1", ['id' => $this->userId]);
            $u = $this->db->fetch();
            if (!empty($u['employee_id'])) {
                $this->employeeId = (int)$u['employee_id'];
                Session::set('user_employee_id', $this->employeeId);
            }
        }

        // 3. Nếu tài khoản không liên kết với hồ sơ nhân sự
        if (!$this->employeeId) {
            // Nếu là admin không gắn employee_id, cho phép fallback sang employee id 1 để xem demo giao diện
            if (Session::isAdmin()) {
                $this->db->query("SELECT id FROM employees ORDER BY id ASC LIMIT 1");
                $emp = $this->db->fetch();
                if ($emp) {
                    $this->employeeId = (int)$emp['id'];
                }
            } else {
                echo '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 80px auto; padding: 30px; border: 1px solid #e2e8f0; border-radius: 12px; background: #fff; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); text-align: center;">';
                echo '<h2 style="color: #e53e3e; margin-bottom: 12px;">⚠️ Tài khoản chưa được liên kết hồ sơ</h2>';
                echo '<p style="color: #4a5568; line-height: 1.6; margin-bottom: 24px;">Tài khoản đăng nhập của bạn hiện chưa được gắn với mã nhân viên trong hệ thống HRIS. Vui lòng liên hệ Phòng Tổ chức Nhân sự để được kích hoạt.</p>';
                echo '<a href="' . BASE_URL . '/auth/logout" style="display: inline-block; padding: 10px 20px; background: #3182ce; color: #fff; text-decoration: none; border-radius: 6px; font-weight: bold;">Đăng xuất</a>';
                echo '</div>';
                exit;
            }
        }
    }

    /**
     * Chuyển hướng mặc định về Dashboard
     */
    public function index(): void
    {
        $this->dashboard();
    }

    /**
     * Dashboard chính của cổng ESS
     */
    public function dashboard(): void
    {
        $empId = $this->employeeId;

        // 1. Thông tin cơ bản của nhân viên
        $this->db->query(
            "SELECT e.*, d.dept_name, p.pos_title, pr.project_name,
                    m.full_name as manager_name
             FROM employees e
             LEFT JOIN departments d ON e.department_id = d.id
             LEFT JOIN positions p ON e.position_id = p.id
             LEFT JOIN projects pr ON e.current_project_id = pr.id
             LEFT JOIN employees m ON e.direct_manager_id = m.id
             WHERE e.id = :id
             LIMIT 1",
            ['id' => $empId]
        );
        $employee = $this->db->fetch();

        // 2. Chấm công hôm nay
        $today = date('Y-m-d');
        $this->db->query(
            "SELECT * FROM timesheets 
             WHERE employee_id = :id AND work_date = :today 
             ORDER BY id DESC LIMIT 1",
            ['id' => $empId, 'today' => $today]
        );
        $todayAttendance = $this->db->fetch();

        // 3. Phép năm còn lại
        $leaveStats = $this->calculateAnnualLeaveStats($empId);

        // 4. Lương tháng gần nhất
        $this->db->query(
            "SELECT * FROM payrolls 
             WHERE employee_id = :id 
             ORDER BY year DESC, month DESC 
             LIMIT 1",
            ['id' => $empId]
        );
        $latestPayroll = $this->db->fetch();

        // 5. Thống kê công tháng hiện tại
        $currentMonth = (int)date('m');
        $currentYear = (int)date('Y');
        $this->db->query(
            "SELECT COUNT(DISTINCT work_date) as worked_days,
                    SUM(standard_hours) as total_standard_hours,
                    SUM(ot_hours + ot_normal_hours + ot_sunday_hours + ot_holiday_hours) as total_ot_hours
             FROM timesheets
             WHERE employee_id = :id 
               AND MONTH(work_date) = :m 
               AND YEAR(work_date) = :y",
            ['id' => $empId, 'm' => $currentMonth, 'y' => $currentYear]
        );
        $monthAttendanceSummary = $this->db->fetch();

        // 6. Thông báo nội bộ mới nhất (Tin tức công ty)
        $this->db->query(
            "SELECT id, title, slug, category, published_at, created_at 
             FROM internal_articles 
             WHERE status = 'published' 
             ORDER BY published_at DESC, id DESC 
             LIMIT 4"
        );
        $companyAnnouncements = $this->db->fetchAll();

        // 7. Thông báo cá nhân chưa đọc
        $this->db->query(
            "SELECT * FROM employee_notifications 
             WHERE employee_id = :id 
             ORDER BY is_read ASC, created_at DESC 
             LIMIT 5",
            ['id' => $empId]
        );
        $myNotifications = $this->db->fetchAll();

        // 8. Đơn nghỉ phép gần đây
        $this->db->query(
            "SELECT lr.*, lt.name as leave_type_name
             FROM leave_requests lr
             JOIN leave_types lt ON lr.leave_type_id = lt.id
             WHERE lr.employee_id = :id
             ORDER BY lr.id DESC
             LIMIT 3",
            ['id' => $empId]
        );
        $recentLeaves = $this->db->fetchAll();

        $this->view('ess/dashboard', [
            'employee'               => $employee,
            'todayAttendance'        => $todayAttendance,
            'leaveStats'             => $leaveStats,
            'latestPayroll'          => $latestPayroll,
            'monthAttendanceSummary' => $monthAttendanceSummary,
            'companyAnnouncements'   => $companyAnnouncements,
            'myNotifications'        => $myNotifications,
            'recentLeaves'           => $recentLeaves,
            'pageTitle'              => 'Cổng tự phục vụ Nhân viên (ESS)'
        ]);
    }

    /**
     * Xem hồ sơ cá nhân (Chỉ đọc các trường nhạy cảm, cho phép sửa thông tin liên hệ)
     */
    public function profile(): void
    {
        $empId = $this->employeeId;

        // Lấy toàn bộ thông tin nhân viên
        $this->db->query(
            "SELECT e.*, d.dept_name, p.pos_title, pr.project_name,
                    m.full_name as manager_name
             FROM employees e
             LEFT JOIN departments d ON e.department_id = d.id
             LEFT JOIN positions p ON e.position_id = p.id
             LEFT JOIN projects pr ON e.current_project_id = pr.id
             LEFT JOIN employees m ON e.direct_manager_id = m.id
             WHERE e.id = :id
             LIMIT 1",
            ['id' => $empId]
        );
        $employee = $this->db->fetch();

        // Lấy danh sách Người phụ thuộc (Giảm trừ gia cảnh)
        $this->db->query(
            "SELECT * FROM dependents WHERE employee_id = :id ORDER BY id DESC",
            ['id' => $empId]
        );
        $dependents = $this->db->fetchAll();

        // Lấy thông tin Bảo hiểm xã hội
        $this->db->query(
            "SELECT * FROM employee_insurance WHERE employee_id = :id LIMIT 1",
            ['id' => $empId]
        );
        $insurance = $this->db->fetch();

        // Lấy danh sách Yêu cầu thay đổi hồ sơ đang chờ duyệt
        $this->db->query(
            "SELECT * FROM profile_change_requests WHERE employee_id = :id AND status = 'Pending' ORDER BY id DESC",
            ['id' => $empId]
        );
        $pendingChanges = $this->db->fetchAll();

        $this->view('ess/profile', [
            'employee'       => $employee,
            'dependents'     => $dependents,
            'insurance'      => $insurance,
            'pendingChanges' => $pendingChanges,
            'pageTitle'      => 'Hồ sơ cá nhân - ESS'
        ]);
    }

    /**
     * Xử lý Cập nhật thông tin được phép (SĐT, địa chỉ, ảnh đại diện, người phụ thuộc)
     */
    public function updateProfile(): void
    {
        if (!$this->isPost()) {
            $this->redirect('ess/profile');
            return;
        }

        $csrfToken = $this->postData('_csrf_token', '');
        if (!Session::validateCsrfToken($csrfToken)) {
            Session::setFlash('error', 'Lỗi bảo mật (CSRF). Vui lòng thử lại.');
            $this->redirect('ess/profile');
            return;
        }

        $empId = $this->employeeId;
        $actionType = $this->postData('action_type', 'update_contact');

        if ($actionType === 'update_contact') {
            // Lấy dữ liệu nhân viên hiện tại để so sánh
            $this->db->query("SELECT * FROM employees WHERE id = :id LIMIT 1", ['id' => $empId]);
            $currentEmp = $this->db->fetch();

            $phone = trim($this->postData('phone', ''));
            $email = trim($this->postData('email', ''));
            $homeAddress = trim($this->postData('home_address', ''));
            $currentAddress = trim($this->postData('current_address', ''));
            $emergencyName = trim($this->postData('emergency_contact_name', ''));
            $emergencyPhone = trim($this->postData('emergency_contact_phone', ''));
            $emergencyRelation = trim($this->postData('emergency_contact_relation', ''));
            $bankAccount = trim($this->postData('bank_account_no', ''));
            $bankName = trim($this->postData('bank_name', ''));
            $bankBranch = trim($this->postData('bank_branch', ''));
            $shoeSize = trim($this->postData('safety_shoe_size', ''));
            $uniformSize = trim($this->postData('safety_uniform_size', ''));

            // Các trường CCCD/CMND nếu gửi từ form
            $idCardNo = trim($this->postData('id_card_no', $currentEmp['id_card_no'] ?? ''));
            $idCardDate = trim($this->postData('id_card_date', $currentEmp['id_card_date'] ?? ''));
            $idCardPlace = trim($this->postData('id_card_place', $currentEmp['id_card_place'] ?? ''));

            $sensitiveChanges = [];

            // 1. Kiểm tra trường nhạy cảm: Số CCCD/CMND
            if (!empty($idCardNo) && $idCardNo !== ($currentEmp['id_card_no'] ?? '')) {
                WorkflowService::createProfileChangeRequest(
                    $empId, (int)$this->userId, 'personal_info', 'id_card_no', 'Số CMND / CCCD',
                    $currentEmp['id_card_no'] ?? '', $idCardNo, 'Cập nhật từ Cổng ESS'
                );
                $sensitiveChanges[] = 'Số CCCD/CMND';
            }

            // 2. Kiểm tra trường nhạy cảm: Hộ khẩu thường trú
            if ($homeAddress !== ($currentEmp['home_address'] ?? '')) {
                WorkflowService::createProfileChangeRequest(
                    $empId, (int)$this->userId, 'contact', 'home_address', 'Hộ khẩu thường trú',
                    $currentEmp['home_address'] ?? '', $homeAddress, 'Cập nhật từ Cổng ESS'
                );
                $sensitiveChanges[] = 'Hộ khẩu thường trú';
                // Không cập nhật trực tiếp vào DB, giữ giá trị cũ
                $homeAddress = $currentEmp['home_address'] ?? '';
            }

            // 3. Kiểm tra trường nhạy cảm: Số tài khoản ngân hàng
            if ($bankAccount !== ($currentEmp['bank_account_no'] ?? '')) {
                WorkflowService::createProfileChangeRequest(
                    $empId, (int)$this->userId, 'bank', 'bank_account_no', 'Số tài khoản ngân hàng',
                    $currentEmp['bank_account_no'] ?? '', $bankAccount, 'Cập nhật từ Cổng ESS'
                );
                $sensitiveChanges[] = 'Số tài khoản ngân hàng';
                $bankAccount = $currentEmp['bank_account_no'] ?? '';
            }

            // 4. Kiểm tra trường nhạy cảm: Tên ngân hàng
            if ($bankName !== ($currentEmp['bank_name'] ?? '')) {
                WorkflowService::createProfileChangeRequest(
                    $empId, (int)$this->userId, 'bank', 'bank_name', 'Tên ngân hàng',
                    $currentEmp['bank_name'] ?? '', $bankName, 'Cập nhật từ Cổng ESS'
                );
                $sensitiveChanges[] = 'Tên ngân hàng';
                $bankName = $currentEmp['bank_name'] ?? '';
            }

            // 5. Kiểm tra trường nhạy cảm: Chi nhánh ngân hàng
            if ($bankBranch !== ($currentEmp['bank_branch'] ?? '')) {
                WorkflowService::createProfileChangeRequest(
                    $empId, (int)$this->userId, 'bank', 'bank_branch', 'Chi nhánh ngân hàng',
                    $currentEmp['bank_branch'] ?? '', $bankBranch, 'Cập nhật từ Cổng ESS'
                );
                $sensitiveChanges[] = 'Chi nhánh ngân hàng';
                $bankBranch = $currentEmp['bank_branch'] ?? '';
            }

            // Xử lý upload avatar (nếu có - không nhạy cảm, cho sửa trực tiếp)
            $avatarPath = null;
            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                $fileTmp = $_FILES['avatar']['tmp_name'];
                $fileName = $_FILES['avatar']['name'];
                $fileSize = $_FILES['avatar']['size'];
                $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
                if (in_array($ext, $allowedExts) && $fileSize <= 2 * 1024 * 1024) {
                    $uploadDir = APP_ROOT . '/../public/uploads/avatars/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    $newFileName = 'avatar_' . $empId . '_' . time() . '.' . $ext;
                    $destination = $uploadDir . $newFileName;
                    if (move_uploaded_file($fileTmp, $destination)) {
                        $avatarPath = 'uploads/avatars/' . $newFileName;
                    }
                }
            }

            // Cập nhật các trường không nhạy cảm trực tiếp vào DB
            $sql = "UPDATE employees SET 
                        phone = :phone,
                        email = :email,
                        current_address = :current_address,
                        emergency_contact_name = :em_name,
                        emergency_contact_phone = :em_phone,
                        emergency_contact_relation = :em_rel,
                        safety_shoe_size = :shoe_size,
                        safety_uniform_size = :uniform_size";

            $params = [
                'phone'           => $phone,
                'email'           => $email,
                'current_address' => $currentAddress,
                'em_name'         => $emergencyName,
                'em_phone'        => $emergencyPhone,
                'em_rel'          => $emergencyRelation,
                'shoe_size'       => $shoeSize,
                'uniform_size'    => $uniformSize,
                'id'              => $empId
            ];

            if ($avatarPath) {
                $sql .= ", avatar_path = :avatar_path";
                $params['avatar_path'] = $avatarPath;
            }

            $sql .= " WHERE id = :id";
            $this->db->query($sql, $params);

            // Ghi audit log
            AuditLogger::log('update', 'ess', $this->userId, null, null, 'Nhân viên cập nhật thông tin cá nhân');

            if (!empty($sensitiveChanges)) {
                $changeList = implode(', ', $sensitiveChanges);
                Session::setFlash('success', "Thông tin liên hệ cơ bản đã cập nhật! Các mục quan trọng: [{$changeList}] đã được tạo Yêu cầu phê duyệt (Profile Change Request) gửi tới Phòng Nhân sự kiểm tra.");
            } else {
                Session::setFlash('success', 'Cập nhật thông tin cá nhân thành công!');
            }
        } 
        elseif ($actionType === 'add_dependent') {
            $depName = trim($this->postData('dep_full_name', ''));
            $depRelation = trim($this->postData('dep_relationship', ''));
            $depBirthDate = $this->postData('dep_birth_date', null);
            $depIdNumber = trim($this->postData('dep_id_number', ''));
            $isTax = $this->postData('is_tax_dependent') ? 1 : 0;

            if (empty($depName) || empty($depRelation)) {
                Session::setFlash('error', 'Vui lòng nhập họ tên và mối quan hệ người phụ thuộc.');
                $this->redirect('ess/profile');
                return;
            }

            $this->db->query(
                "INSERT INTO dependents (employee_id, full_name, relationship, birth_date, id_number, is_tax_dependent, created_at)
                 VALUES (:emp_id, :name, :relation, :birth, :id_num, :is_tax, NOW())",
                [
                    'emp_id'   => $empId,
                    'name'     => $depName,
                    'relation' => $depRelation,
                    'birth'    => !empty($depBirthDate) ? $depBirthDate : null,
                    'id_num'   => $depIdNumber,
                    'is_tax'   => $isTax
                ]
            );

            // Thông báo cá nhân
            $this->createNotification(
                $empId,
                'Đã ghi nhận người phụ thuộc mới',
                "Bạn đã đăng ký người phụ thuộc {$depName}. Vui lòng nộp hồ sơ chứng minh về Phòng Nhân sự.",
                'info',
                'ess/profile'
            );

            Session::setFlash('success', 'Thêm người phụ thuộc thành công! Vui lòng nộp hồ sơ chứng minh cho HR.');
        }

        $this->redirect('ess/profile');
    }

    /**
     * Xem phiếu lương hàng tháng (hỗ trợ in phiếu lương chuẩn)
     */
    public function payslip(mixed $month = null, mixed $year = null): void
    {
        $empId = $this->employeeId;

        // Lấy danh sách các tháng đã có phiếu lương
        $this->db->query(
            "SELECT DISTINCT month, year 
             FROM payrolls 
             WHERE employee_id = :id 
             ORDER BY year DESC, month DESC",
            ['id' => $empId]
        );
        $availablePeriods = $this->db->fetchAll();

        // Xác định tháng/năm cần xem
        if (!$month || !$year) {
            if (!empty($availablePeriods)) {
                $month = (int)$availablePeriods[0]['month'];
                $year = (int)$availablePeriods[0]['year'];
            } else {
                $month = (int)date('m');
                $year = (int)date('Y');
            }
        } else {
            $month = (int)$month;
            $year = (int)$year;
        }

        // Lấy thông tin nhân viên
        $this->db->query(
            "SELECT e.*, d.dept_name, p.pos_title, pr.project_name
             FROM employees e
             LEFT JOIN departments d ON e.department_id = d.id
             LEFT JOIN positions p ON e.position_id = p.id
             LEFT JOIN projects pr ON e.current_project_id = pr.id
             WHERE e.id = :id LIMIT 1",
            ['id' => $empId]
        );
        $employee = $this->db->fetch();

        // Lấy bản ghi phiếu lương
        $this->db->query(
            "SELECT * FROM payrolls 
             WHERE employee_id = :id AND month = :m AND year = :y 
             ORDER BY id DESC LIMIT 1",
            ['id' => $empId, 'm' => $month, 'y' => $year]
        );
        $payslip = $this->db->fetch();

        // Kiểm tra xem có yêu cầu chế độ in (print mode) không
        $isPrint = isset($_GET['print']) && $_GET['print'] == 1;

        $this->view('ess/payslip', [
            'employee'         => $employee,
            'payslip'          => $payslip,
            'selectedMonth'    => $month,
            'selectedYear'     => $year,
            'availablePeriods' => $availablePeriods,
            'isPrint'          => $isPrint,
            'pageTitle'        => "Phiếu lương tháng {$month}/{$year} - ESS"
        ]);
    }

    /**
     * Xem lịch sử chấm công cá nhân
     */
    public function attendance(mixed $month = null, mixed $year = null): void
    {
        $empId = $this->employeeId;

        $month = !empty($month) ? (int)$month : (int)date('m');
        $year = !empty($year) ? (int)$year : (int)date('Y');

        // Lấy danh sách chấm công trong tháng
        $this->db->query(
            "SELECT t.*, s.shift_name, s.start_time as shift_start, s.end_time as shift_end
             FROM timesheets t
             LEFT JOIN shifts s ON t.shift_id = s.id
             WHERE t.employee_id = :id 
               AND MONTH(t.work_date) = :m 
               AND YEAR(t.work_date) = :y
             ORDER BY t.work_date ASC",
            ['id' => $empId, 'm' => $month, 'y' => $year]
        );
        $attendanceRecords = $this->db->fetchAll();

        // Tính tổng hợp
        $summary = [
            'total_worked_days' => 0,
            'total_standard_hours' => 0.0,
            'total_ot_hours' => 0.0,
            'total_night_hours' => 0.0,
            'late_count' => 0,
            'cleanroom_days' => 0
        ];

        foreach ($attendanceRecords as $att) {
            if (!empty($att['check_in'])) {
                $summary['total_worked_days']++;
            }
            $summary['total_standard_hours'] += (float)($att['standard_hours'] ?? 0);
            $ot = (float)($att['ot_hours'] ?? 0) + (float)($att['ot_normal_hours'] ?? 0) + (float)($att['ot_sunday_hours'] ?? 0) + (float)($att['ot_holiday_hours'] ?? 0);
            $summary['total_ot_hours'] += $ot;
            $summary['total_night_hours'] += (float)($att['night_shift_hours'] ?? 0);
            if (!empty($att['is_cleanroom']) || ($att['work_environment'] ?? '') === 'cleanroom') {
                $summary['cleanroom_days']++;
            }
        }

        $this->view('ess/attendance', [
            'attendanceRecords' => $attendanceRecords,
            'summary'           => $summary,
            'selectedMonth'     => $month,
            'selectedYear'      => $year,
            'pageTitle'         => "Bảng chấm công tháng {$month}/{$year} - ESS"
        ]);
    }

    /**
     * Nộp đơn xin nghỉ phép
     */
    public function leaveRequest(): void
    {
        $empId = $this->employeeId;

        // Danh sách loại nghỉ phép
        $this->db->query("SELECT * FROM leave_types ORDER BY id ASC");
        $leaveTypes = $this->db->fetchAll();

        // Thống kê phép năm
        $leaveStats = $this->calculateAnnualLeaveStats($empId);

        // Các đơn gần nhất
        $this->db->query(
            "SELECT lr.*, lt.name as leave_type_name
             FROM leave_requests lr
             JOIN leave_types lt ON lr.leave_type_id = lt.id
             WHERE lr.employee_id = :id
             ORDER BY lr.id DESC
             LIMIT 5",
            ['id' => $empId]
        );
        $recentLeaves = $this->db->fetchAll();

        $this->view('ess/leave_request', [
            'leaveTypes'   => $leaveTypes,
            'leaveStats'   => $leaveStats,
            'recentLeaves' => $recentLeaves,
            'pageTitle'    => 'Nộp đơn xin nghỉ phép - ESS'
        ]);
    }

    /**
     * Xử lý POST nộp đơn nghỉ phép
     */
    public function submitLeaveRequest(): void
    {
        if (!$this->isPost()) {
            $this->redirect('ess/leaveRequest');
            return;
        }

        $csrfToken = $this->postData('_csrf_token', '');
        if (!Session::validateCsrfToken($csrfToken)) {
            Session::setFlash('error', 'Lỗi bảo mật (CSRF). Vui lòng thử lại.');
            $this->redirect('ess/leaveRequest');
            return;
        }

        $empId = $this->employeeId;
        $leaveTypeId = (int)$this->postData('leave_type_id', 1);
        $startDate = $this->postData('start_date', '');
        $endDate = $this->postData('end_date', '');
        $totalDays = (float)$this->postData('total_days', 1.0);
        $reason = trim($this->postData('reason', ''));

        // Kiểm tra hợp lệ
        if (empty($startDate) || empty($endDate) || $totalDays <= 0) {
            Session::setFlash('error', 'Vui lòng nhập ngày bắt đầu, ngày kết thúc và số ngày nghỉ hợp lệ.');
            $this->redirect('ess/leaveRequest');
            return;
        }

        if (strtotime($startDate) > strtotime($endDate)) {
            Session::setFlash('error', 'Ngày kết thúc không được nhỏ hơn ngày bắt đầu.');
            $this->redirect('ess/leaveRequest');
            return;
        }

        // 1. Tự động tính số ngày làm việc thực tế (trừ Lễ + CN)
        require_once APP_ROOT . '/models/LeaveHoliday.php';
        $holidayModel = new LeaveHoliday();
        $calc = $holidayModel->calculateWorkingDays($startDate, $endDate);
        if ($this->postData('total_days')) {
            $totalDays = (float)$this->postData('total_days');
        } else {
            $totalDays = $calc['working_days'];
        }

        if ($totalDays <= 0) {
            Session::setFlash('error', 'Khoảng thời gian bạn chọn rơi hoàn toàn vào ngày nghỉ Lễ hoặc cuối tuần (0 ngày làm việc).');
            $this->redirect('ess/leaveRequest');
            return;
        }

        // 2. Kiểm tra chồng đơn (Overlap Check)
        require_once APP_ROOT . '/models/LeaveRequest.php';
        $leaveModel = new LeaveRequest();
        $overlap = $leaveModel->checkOverlap($empId, $startDate, $endDate);
        if ($overlap) {
            $sFormatted = date('d/m/Y', strtotime($overlap['start_date']));
            $eFormatted = date('d/m/Y', strtotime($overlap['end_date']));
            Session::setFlash('error', "Phát hiện chồng đơn! Bạn đã có đơn xin nghỉ '{$overlap['leave_type_name']}' từ ngày {$sFormatted} đến {$eFormatted} (Trạng thái: {$overlap['status']}).");
            $this->redirect('ess/leaveRequest');
            return;
        }

        // 3. Nếu là nghỉ phép năm, kiểm tra số ngày phép còn lại
        if ($leaveTypeId === 1) {
            $stats = $this->calculateAnnualLeaveStats($empId);
            if ($totalDays > $stats['remaining']) {
                Session::setFlash('error', "Số ngày nghỉ ({$totalDays} ngày) vượt quá số ngày phép năm còn lại của bạn ({$stats['remaining']} ngày).");
                $this->redirect('ess/leaveRequest');
                return;
            }
        }

        // Lưu đơn nghỉ phép
        $this->db->query(
            "INSERT INTO leave_requests (employee_id, leave_type_id, start_date, end_date, total_days, reason, status, created_at)
             VALUES (:emp_id, :type_id, :start, :end, :days, :reason, 'Pending', NOW())",
            [
                'emp_id'  => $empId,
                'type_id' => $leaveTypeId,
                'start'   => $startDate,
                'end'     => $endDate,
                'days'    => $totalDays,
                'reason'  => $reason
            ]
        );

        $newLeaveId = (int)$this->db->lastInsertId();

        // Ghi Audit Log
        AuditLogger::log('create', 'leave_request', $newLeaveId, null, null, "Nhân viên nộp đơn xin nghỉ phép từ {$startDate} đến {$endDate}");

        // Tạo thông báo xác nhận
        $this->createNotification(
            $empId,
            'Đã gửi đơn xin nghỉ phép',
            "Đơn xin nghỉ phép từ {$startDate} đến {$endDate} ({$totalDays} ngày) đã được gửi đến người quản lý.",
            'success',
            'ess/myLeaves'
        );

        Session::setFlash('success', 'Nộp đơn xin nghỉ phép thành công! Đơn của bạn đang chờ phê duyệt.');
        $this->redirect('ess/myLeaves');
    }

    /**
     * Lịch sử nghỉ phép cá nhân & Số ngày phép còn lại
     */
    public function myLeaves(): void
    {
        $empId = $this->employeeId;

        // Thống kê phép năm
        $leaveStats = $this->calculateAnnualLeaveStats($empId);

        // Lấy danh sách toàn bộ đơn nghỉ phép
        $this->db->query(
            "SELECT lr.*, lt.name as leave_type_name, lt.code as leave_type_code,
                    u.full_name as approver_name
             FROM leave_requests lr
             JOIN leave_types lt ON lr.leave_type_id = lt.id
             LEFT JOIN users u ON lr.approved_by = u.id
             WHERE lr.employee_id = :id
             ORDER BY lr.created_at DESC",
            ['id' => $empId]
        );
        $leaveRequests = $this->db->fetchAll();

        $this->view('ess/my_leaves', [
            'leaveStats'    => $leaveStats,
            'leaveRequests' => $leaveRequests,
            'pageTitle'     => 'Lịch sử nghỉ phép - ESS'
        ]);
    }

    /**
     * Hủy đơn xin nghỉ phép (khi đang ở trạng thái 'Pending')
     */
    public function cancelLeave(mixed $id): void
    {
        $empId = $this->employeeId;
        $id = (int)$id;

        // Kiểm tra đơn thuộc về nhân viên và đang Pending
        $this->db->query(
            "SELECT * FROM leave_requests WHERE id = :id AND employee_id = :emp_id LIMIT 1",
            ['id' => $id, 'emp_id' => $empId]
        );
        $leave = $this->db->fetch();

        if (!$leave) {
            Session::setFlash('error', 'Đơn xin nghỉ phép không tồn tại.');
            $this->redirect('ess/myLeaves');
            return;
        }

        if ($leave['status'] !== 'Pending') {
            Session::setFlash('error', 'Không thể hủy đơn đã được xét duyệt hoặc đã xử lý.');
            $this->redirect('ess/myLeaves');
            return;
        }

        $this->db->query(
            "UPDATE leave_requests SET status = 'Cancelled' WHERE id = :id AND employee_id = :emp_id",
            ['id' => $id, 'emp_id' => $empId]
        );

        Session::setFlash('success', 'Đã hủy đơn xin nghỉ phép thành công.');
        $this->redirect('ess/myLeaves');
    }

    /**
     * Xem danh sách Hợp đồng lao động
     */
    public function myContracts(): void
    {
        $empId = $this->employeeId;

        $this->db->query(
            "SELECT c.*, ct.name as contract_type_name, ct.code as contract_type_code
             FROM contracts c
             LEFT JOIN contract_types ct ON c.contract_type_id = ct.id
             WHERE c.employee_id = :id
             ORDER BY c.start_date DESC",
            ['id' => $empId]
        );
        $contracts = $this->db->fetchAll();

        $this->view('ess/my_contracts', [
            'contracts' => $contracts,
            'pageTitle' => 'Hợp đồng lao động - ESS'
        ]);
    }

    /**
     * Xem lịch sử Đào tạo & Phát triển (L&D) đã tham gia
     */
    public function myTrainings(): void
    {
        $empId = $this->employeeId;

        $this->db->query(
            "SELECT tp.*, t.title as course_name, t.code as course_code,
                    t.start_date, t.end_date, t.trainer_name, t.location,
                    t.status as course_status, t.description
             FROM training_participants tp
             JOIN trainings t ON tp.training_id = t.id
             WHERE tp.employee_id = :id
             ORDER BY t.start_date DESC",
            ['id' => $empId]
        );
        $trainings = $this->db->fetchAll();

        $this->view('ess/my_trainings', [
            'trainings' => $trainings,
            'pageTitle' => 'Khóa đào tạo của tôi - ESS'
        ]);
    }

    /**
     * Xem thông báo nội bộ công ty & thông báo cá nhân
     */
    public function notifications(): void
    {
        $empId = $this->employeeId;

        // Thông báo chung từ ban giám đốc / công ty
        $this->db->query(
            "SELECT id, title, slug, content, category, attachment_path, published_at, created_at
             FROM internal_articles
             WHERE status = 'published'
             ORDER BY published_at DESC, id DESC"
        );
        $companyArticles = $this->db->fetchAll();

        // Thông báo riêng cá nhân
        $this->db->query(
            "SELECT * FROM employee_notifications 
             WHERE employee_id = :id 
             ORDER BY created_at DESC",
            ['id' => $empId]
        );
        $personalNotifications = $this->db->fetchAll();

        $this->view('ess/notifications', [
            'companyArticles'       => $companyArticles,
            'personalNotifications' => $personalNotifications,
            'pageTitle'             => 'Thông báo nội bộ - ESS'
        ]);
    }

    /**
     * Xem chi tiết 1 bài viết / thông báo công ty
     */
    public function viewArticle(mixed $id): void
    {
        $id = (int)$id;

        $this->db->query(
            "SELECT a.*, u.full_name as author_name 
             FROM internal_articles a
             LEFT JOIN users u ON a.author_id = u.id
             WHERE a.id = :id AND a.status = 'published'
             LIMIT 1",
            ['id' => $id]
        );
        $article = $this->db->fetch();

        if (!$article) {
            Session::setFlash('error', 'Thông báo không tồn tại hoặc đã bị gỡ bỏ.');
            $this->redirect('ess/notifications');
            return;
        }

        $this->view('ess/article_detail', [
            'article'   => $article,
            'pageTitle' => $article['title'] . ' - ESS'
        ]);
    }

    /**
     * Đánh dấu đã đọc thông báo cá nhân
     */
    public function markNotificationRead(mixed $id): void
    {
        $empId = $this->employeeId;
        $id = (int)$id;

        $this->db->query(
            "UPDATE employee_notifications SET is_read = 1 WHERE id = :id AND employee_id = :emp_id",
            ['id' => $id, 'emp_id' => $empId]
        );

        $this->redirect('ess/notifications');
    }

    /**
     * Đánh dấu tất cả thông báo cá nhân là đã đọc
     */
    public function markAllNotificationsRead(): void
    {
        $empId = $this->employeeId;

        $this->db->query(
            "UPDATE employee_notifications SET is_read = 1 WHERE employee_id = :emp_id",
            ['emp_id' => $empId]
        );

        Session::setFlash('success', 'Đã đánh dấu tất cả thông báo là đã đọc.');
        $this->redirect('ess/notifications');
    }

    // ══════════════════════════════════════════════════════════
    //  HELPER FUNCTIONS (PRIVATE)
    // ══════════════════════════════════════════════════════════

    /**
     * Tính toán số ngày nghỉ phép năm của nhân viên (Được cấp, Đã nghỉ, Đang chờ, Còn lại)
     */
    private function calculateAnnualLeaveStats(int $empId): array
    {
        $currentYear = (int)date('Y');

        // Kiểm tra bảng leave_allocations (Frappe HRMS Allocation engine)
        $this->db->query(
            "SELECT * FROM leave_allocations WHERE employee_id = :id AND leave_type_id = 1 AND year = :y LIMIT 1",
            ['id' => $empId, 'y' => $currentYear]
        );
        $alloc = $this->db->fetch();

        // Số ngày phép đang chờ duyệt (Pending) trong năm hiện tại
        $this->db->query(
            "SELECT COALESCE(SUM(total_days), 0) as pending_days
             FROM leave_requests 
             WHERE employee_id = :id 
               AND leave_type_id = 1 
               AND status = 'Pending' 
               AND YEAR(start_date) = :year",
            ['id' => $empId, 'year' => $currentYear]
        );
        $pending = (float)$this->db->fetch()['pending_days'];

        if ($alloc) {
            $totalEntitled = (float)$alloc['entitled_days'] + (float)$alloc['carried_forward_days'];
            $used = (float)$alloc['used_days'];
            $remaining = max(0.0, (float)$alloc['remaining_days'] - $pending);

            return [
                'entitled'  => $totalEntitled,
                'used'      => $used,
                'pending'   => $pending,
                'remaining' => $remaining
            ];
        }

        // Lấy ngày vào làm của nhân viên để tính thâm niên
        $this->db->query("SELECT join_date FROM employees WHERE id = :id LIMIT 1", ['id' => $empId]);
        $emp = $this->db->fetch();
        $joinDate = $emp['join_date'] ?? null;

        $baseDays = 12.0; // Phép năm cơ bản theo luật
        $seniorityDays = 0.0;

        if (!empty($joinDate)) {
            $yearsWorked = floor((time() - strtotime($joinDate)) / (365.25 * 86400));
            if ($yearsWorked >= 5) {
                $seniorityDays = floor($yearsWorked / 5);
            }
        }
        $entitled = $baseDays + $seniorityDays;

        // Số ngày phép đã được duyệt (Approved) trong năm hiện tại
        $this->db->query(
            "SELECT COALESCE(SUM(total_days), 0) as used_days
             FROM leave_requests 
             WHERE employee_id = :id 
               AND leave_type_id = 1 
               AND status = 'Approved'
               AND YEAR(start_date) = :year",
            ['id' => $empId, 'year' => $currentYear]
        );
        $used = (float)$this->db->fetch()['used_days'];

        // Số ngày phép đang chờ duyệt (Pending) trong năm hiện tại
        $this->db->query(
            "SELECT COALESCE(SUM(total_days), 0) as pending_days
             FROM leave_requests 
             WHERE employee_id = :id 
               AND leave_type_id = 1 
               AND status = 'Pending'
               AND YEAR(start_date) = :year",
            ['id' => $empId, 'year' => $currentYear]
        );
        $pending = (float)$this->db->fetch()['pending_days'];

        $remaining = max(0.0, $entitled - $used - $pending);

        return [
            'entitled'  => $entitled,
            'used'      => $used,
            'pending'   => $pending,
            'remaining' => $remaining
        ];
    }

    /**
     * Tạo thông báo cá nhân
     */
    private function createNotification(int $empId, string $title, string $message, string $type = 'info', ?string $link = null): void
    {
        $this->db->query(
            "INSERT INTO employee_notifications (employee_id, title, message, type, link, is_read, created_at)
             VALUES (:emp_id, :title, :msg, :type, :link, 0, NOW())",
            [
                'emp_id' => $empId,
                'title'  => $title,
                'msg'    => $message,
                'type'   => $type,
                'link'   => $link
            ]
        );
    }

    /**
     * Cổng Tự Phục Vụ: Quản lý Mục tiêu KRA & Đánh giá 360° Cá nhân
     */
    public function performance(): void
    {
        $empId = $this->employeeId;

        // 1. Thông tin nhân viên
        $this->db->query(
            "SELECT e.*, d.dept_name, p.pos_title, m.full_name as manager_name
             FROM employees e
             LEFT JOIN departments d ON e.department_id = d.id
             LEFT JOIN positions p ON e.position_id = p.id
             LEFT JOIN employees m ON e.direct_manager_id = m.id
             WHERE e.id = :id LIMIT 1",
            ['id' => $empId]
        );
        $employee = $this->db->fetch();

        // 2. Lấy các chu kỳ đánh giá áp dụng cho nhân viên
        $deptId = (int)($employee['department_id'] ?? 0);
        $sql = "SELECT p.*, t.name as template_name
                FROM evaluation_periods p
                LEFT JOIN evaluation_templates t ON p.template_id = t.id
                WHERE (p.department_id IS NULL OR p.department_id = :dept_id)
                ORDER BY FIELD(p.status, 'Active', 'Draft', 'Closed'), p.start_date DESC";
        $this->db->query($sql, ['dept_id' => $deptId]);
        $periods = $this->db->fetchAll();

        // 3. Với mỗi chu kỳ, lấy thông tin KRA goals và tiến độ đánh giá của nhân viên
        foreach ($periods as &$p) {
            $pid = (int)$p['id'];

            // Goals
            $this->db->query(
                "SELECT COUNT(*) as cnt, COALESCE(SUM(weightage), 0) as total_weight, MAX(status) as kra_status,
                        ROUND(AVG(self_score), 1) as avg_self, ROUND(AVG(manager_score), 1) as avg_mgr, ROUND(AVG(final_score), 1) as avg_final
                 FROM performance_goals 
                 WHERE period_id = :pid AND employee_id = :eid",
                ['pid' => $pid, 'eid' => $empId]
            );
            $p['goals_summary'] = $this->db->fetch();

            // Self review
            $this->db->query(
                "SELECT * FROM performance_reviews_360 
                 WHERE period_id = :pid AND employee_id = :eid AND relationship = 'Self' AND status = 'Submitted' 
                 LIMIT 1",
                ['pid' => $pid, 'eid' => $empId]
            );
            $p['self_review'] = $this->db->fetch();

            // Final score in emp_evaluations
            $this->db->query(
                "SELECT * FROM emp_evaluations 
                 WHERE period_id = :pid AND employee_id = :eid 
                 LIMIT 1",
                ['pid' => $pid, 'eid' => $empId]
            );
            $p['evaluation_result'] = $this->db->fetch();
        }

        // 4. Các bài đánh giá đồng nghiệp 360° được phân công cho nhân viên này (Peer Reviews Assigned)
        $this->db->query(
            "SELECT r.*, e.full_name as target_name, e.employee_code as target_code, d.dept_name as target_dept,
                    p.name as period_name, p.end_date as period_end_date, p.status as period_status
             FROM performance_reviews_360 r
             JOIN employees e ON r.employee_id = e.id
             JOIN evaluation_periods p ON r.period_id = p.id
             LEFT JOIN departments d ON e.department_id = d.id
             WHERE r.reviewer_id = :rid AND r.relationship = 'Peer'
             ORDER BY FIELD(r.status, 'Pending', 'Submitted'), p.start_date DESC",
            ['rid' => $empId]
        );
        $assignedPeerReviews = $this->db->fetchAll();

        $this->view('ess/performance', [
            'employee'            => $employee,
            'periods'             => $periods,
            'assignedPeerReviews' => $assignedPeerReviews,
            'pageTitle'           => 'Đánh Giá 360° & Mục Tiêu KRA - Cổng Nhân Viên'
        ]);
    }
}
