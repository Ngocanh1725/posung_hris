<?php
/**
 * ============================================================
 *  POSUNG HRIS – ExpenseController
 * ============================================================
 *  Quản lý Đề xuất Công tác & Quyết toán Chi phí (Travel & Expenses)
 * ============================================================
 */

class ExpenseController extends Controller
{
    /**
     * Màn hình chính tích hợp: Tab Đề xuất Công tác & Tab Bảng Quyết toán Chi phí
     */
    public function index(): void
    {
        $this->checkPermission('expense.view');

        $activeTab = $this->getData('tab', 'travel'); // 'travel' hoặc 'claims'

        $travelModel = $this->model('TravelRequest');
        $claimModel = $this->model('ExpenseClaim');
        $projectModel = $this->model('Project');
        $deptModel = $this->model('Department');

        $projects = $projectModel->getActiveProjects();
        $departments = $deptModel->all();

        // Bộ lọc chung
        $filters = [
            'search'        => trim($this->getData('search', '')),
            'status'        => $this->getData('status', ''),
            'project_id'    => $this->getData('project_id', ''),
            'department_id' => $this->getData('department_id', ''),
            'from_date'     => $this->getData('from_date', ''),
            'to_date'       => $this->getData('to_date', ''),
            'category'      => $this->getData('category', ''),
        ];

        $travels = $travelModel->getAllWithRelations($filters);
        $travelStats = $travelModel->getStats();

        $claims = $claimModel->getAllWithRelations($filters);
        $claimStats = $claimModel->getStats();

        $this->view('layouts/header', ['pageTitle' => 'Quản lý Công tác & Chi phí Dự án']);
        $this->view('expense/index', [
            'activeTab'    => $activeTab,
            'travels'      => $travels,
            'travelStats'  => $travelStats,
            'claims'       => $claims,
            'claimStats'   => $claimStats,
            'projects'     => $projects,
            'departments'  => $departments,
            'filters'      => $filters,
        ]);
        $this->view('layouts/footer');
    }

    /**
     * Alias chuyển đến danh sách đề xuất công tác
     */
    public function travelRequests(): void
    {
        $this->redirect('expense?tab=travel');
    }

    /**
     * Alias chuyển đến danh sách bảng quyết toán
     */
    public function claims(): void
    {
        $this->redirect('expense?tab=claims');
    }

    /**
     * Form lập Đề xuất Công tác mới
     */
    public function createTravel(): void
    {
        $this->checkPermission('expense.create');

        $employeeModel = $this->model('Employee');
        $projectModel = $this->model('Project');
        $travelModel = $this->model('TravelRequest');

        $employees = $employeeModel->getAll();
        $projects = $projectModel->getActiveProjects();
        $suggestedCode = $travelModel->generateRequestCode();
        $selectedEmpId = (int)$this->getData('employee_id', 0);
        $selectedProjId = (int)$this->getData('project_id', 0);

        $this->view('layouts/header', ['pageTitle' => 'Lập Đề xuất Đi công tác']);
        $this->view('expense/create_travel', [
            'employees'      => $employees,
            'projects'       => $projects,
            'suggestedCode'  => $suggestedCode,
            'selectedEmpId'  => $selectedEmpId,
            'selectedProjId' => $selectedProjId,
        ]);
        $this->view('layouts/footer');
    }

    /**
     * Lưu Đề xuất Công tác vào CSDL
     */
    public function storeTravel(): void
    {
        $this->checkPermission('expense.create');

        if (!$this->isPost()) {
            $this->redirect('expense/createTravel');
        }

        $employeeId = (int)$this->postData('employee_id');
        $purpose = trim($this->postData('purpose', ''));
        $fromLocation = trim($this->postData('from_location', ''));
        $toLocation = trim($this->postData('to_location', ''));
        $departureDate = $this->postData('departure_date');
        $returnDate = $this->postData('return_date');
        $projectId = $this->postData('project_id') ? (int)$this->postData('project_id') : null;
        $transportType = $this->postData('transport_type', 'Xe công ty');
        $accommodation = trim($this->postData('accommodation', 'Khách sạn'));
        $notes = trim($this->postData('notes', ''));

        // Làm sạch số tiền ngân sách & tạm ứng
        $rawBudget = $this->postData('estimated_budget', '0');
        $budget = (float)str_replace(['.', ',', ' '], '', $rawBudget);

        $rawAdvance = $this->postData('advance_amount', '0');
        $advance = (float)str_replace(['.', ',', ' '], '', $rawAdvance);

        if ($employeeId <= 0 || empty($purpose) || empty($fromLocation) || empty($toLocation) || empty($departureDate) || empty($returnDate)) {
            Session::setFlash('error', 'Vui lòng điền đầy đủ các thông tin bắt buộc của chuyến công tác.');
            $this->redirect('expense/createTravel');
        }

        if (strtotime($returnDate) < strtotime($departureDate)) {
            Session::setFlash('error', 'Ngày về không thể trước ngày đi.');
            $this->redirect('expense/createTravel');
        }

        $travelModel = $this->model('TravelRequest');
        $requestCode = $travelModel->generateRequestCode();

        $data = [
            'request_code'     => $requestCode,
            'employee_id'      => $employeeId,
            'purpose'          => $purpose,
            'from_location'    => $fromLocation,
            'to_location'      => $toLocation,
            'departure_date'   => $departureDate,
            'return_date'      => $returnDate,
            'project_id'       => $projectId,
            'estimated_budget' => $budget,
            'advance_amount'   => $advance,
            'transport_type'   => $transportType,
            'accommodation'    => $accommodation,
            'status'           => 'Pending',
            'notes'            => $notes ?: null,
        ];

        $db = Database::getInstance();
        $sql = "INSERT INTO travel_requests 
                (request_code, employee_id, purpose, from_location, to_location, departure_date, return_date,
                 project_id, estimated_budget, advance_amount, transport_type, accommodation, status, notes)
                VALUES 
                (:request_code, :employee_id, :purpose, :from_location, :to_location, :departure_date, :return_date,
                 :project_id, :estimated_budget, :advance_amount, :transport_type, :accommodation, :status, :notes)";

        if ($db->query($sql, $data)) {
            $newId = $db->lastInsertId();

            $this->auditLog('CREATE', 'travel_request', $newId, null, $data, "Tạo đề xuất công tác {$requestCode} đi {$toLocation}");

            // Gửi thông báo đến HR/Ban Giám đốc
            NotificationService::broadcastToRole(
                'site_manager',
                'approval',
                'Đề xuất Công tác mới cần duyệt',
                "Đề xuất công tác {$requestCode} đi {$toLocation} đang chờ phê duyệt kế hoạch & ngân sách.",
                "expense/showTravel/{$newId}"
            );

            Session::setFlash('success', "Tạo đề xuất công tác {$requestCode} thành công. Đang chờ phê duyệt.");
            $this->redirect('expense/showTravel/' . $newId);
        } else {
            Session::setFlash('error', 'Đã xảy ra lỗi khi lưu đề xuất công tác.');
            $this->redirect('expense/createTravel');
        }
    }

    /**
     * Xem chi tiết đợt công tác
     */
    public function showTravel(int $id): void
    {
        $this->checkPermission('expense.view');

        $travelModel = $this->model('TravelRequest');
        $travel = $travelModel->getByIdWithDetails($id);

        if (!$travel) {
            Session::setFlash('error', 'Hồ sơ công tác không tồn tại.');
            $this->redirect('expense?tab=travel');
        }

        $this->view('layouts/header', ['pageTitle' => "Chi tiết Đề xuất Công tác {$travel->request_code}"]);
        $this->view('expense/show_travel', ['travel' => $travel]);
        $this->view('layouts/footer');
    }

    /**
     * Phê duyệt đề xuất công tác
     */
    public function approveTravel(int $id): void
    {
        $this->checkPermission('expense.approve');

        if (!$this->isPost()) {
            $this->redirect('expense/showTravel/' . $id);
        }

        $travelModel = $this->model('TravelRequest');
        $travel = $travelModel->find($id);

        if (!$travel || $travel->status !== 'Pending') {
            Session::setFlash('error', 'Đề xuất công tác không hợp lệ hoặc không ở trạng thái Chờ duyệt.');
            $this->redirect('expense/showTravel/' . $id);
        }

        $approverId = Session::userId() ?: 1;

        if ($travelModel->approve($id, $approverId)) {
            $this->auditLog('APPROVE', 'travel_request', $id, ['status' => 'Pending'], ['status' => 'Approved'], "Phê duyệt kế hoạch công tác {$travel->request_code}");

            // Thông báo cho nhân viên
            $db = Database::getInstance();
            $db->query("SELECT id FROM users WHERE employee_id = :emp_id LIMIT 1", ['emp_id' => $travel->employee_id]);
            $uRow = $db->fetch();
            if ($uRow) {
                NotificationService::send(
                    (int)$uRow['id'],
                    'alert',
                    'Đề xuất Công tác được Phê duyệt',
                    "Kế hoạch công tác {$travel->request_code} đi {$travel->to_location} đã được phê duyệt.",
                    "expense/showTravel/{$id}"
                );
            }

            Session::setFlash('success', "Đã phê duyệt đề xuất công tác {$travel->request_code}.");
        } else {
            Session::setFlash('error', 'Phê duyệt thất bại.');
        }

        $this->redirect('expense/showTravel/' . $id);
    }

    /**
     * Từ chối đề xuất công tác
     */
    public function rejectTravel(int $id): void
    {
        $this->checkPermission('expense.approve');

        if (!$this->isPost()) {
            $this->redirect('expense/showTravel/' . $id);
        }

        $travelModel = $this->model('TravelRequest');
        $travel = $travelModel->find($id);

        if (!$travel || $travel->status !== 'Pending') {
            Session::setFlash('error', 'Đề xuất công tác không hợp lệ.');
            $this->redirect('expense/showTravel/' . $id);
        }

        $reason = trim($this->postData('rejected_reason', ''));
        if (empty($reason)) {
            Session::setFlash('error', 'Vui lòng cung cấp lý do từ chối.');
            $this->redirect('expense/showTravel/' . $id);
        }

        $approverId = Session::userId() ?: 1;

        if ($travelModel->reject($id, $approverId, $reason)) {
            $this->auditLog('REJECT', 'travel_request', $id, ['status' => 'Pending'], ['status' => 'Rejected', 'reason' => $reason], "Từ chối đề xuất công tác {$travel->request_code}");

            $db = Database::getInstance();
            $db->query("SELECT id FROM users WHERE employee_id = :emp_id LIMIT 1", ['emp_id' => $travel->employee_id]);
            $uRow = $db->fetch();
            if ($uRow) {
                NotificationService::send(
                    (int)$uRow['id'],
                    'alert',
                    'Đề xuất Công tác bị Từ chối',
                    "Đề xuất công tác {$travel->request_code} đã bị từ chối với lý do: {$reason}",
                    "expense/showTravel/{$id}"
                );
            }

            Session::setFlash('success', "Đã từ chối đề xuất công tác {$travel->request_code}.");
        } else {
            Session::setFlash('error', 'Thao tác từ chối thất bại.');
        }

        $this->redirect('expense/showTravel/' . $id);
    }

    /**
     * Form lập Bảng quyết toán chi phí công tác / dự án
     */
    public function createClaim(): void
    {
        $this->checkPermission('expense.create');

        $employeeModel = $this->model('Employee');
        $projectModel = $this->model('Project');
        $travelModel = $this->model('TravelRequest');
        $claimModel = $this->model('ExpenseClaim');

        $employees = $employeeModel->getAll();
        $projects = $projectModel->getActiveProjects();
        $suggestedCode = $claimModel->generateClaimCode();

        $travelId = (int)$this->getData('travel_id', 0);
        $travel = $travelId > 0 ? $travelModel->getByIdWithDetails($travelId) : null;

        // Lấy danh sách các chuyến công tác đã được duyệt để chọn liên kết
        $db = Database::getInstance();
        $db->query("SELECT id, request_code, employee_id, to_location, departure_date, return_date, estimated_budget, advance_amount, project_id 
                    FROM travel_requests 
                    WHERE status IN ('Approved', 'Completed') 
                    ORDER BY id DESC");
        $approvedTravels = json_decode(json_encode($db->fetchAll()));

        $this->view('layouts/header', ['pageTitle' => 'Lập Bảng Quyết toán Chi phí']);
        $this->view('expense/create_claim', [
            'employees'       => $employees,
            'projects'        => $projects,
            'approvedTravels' => $approvedTravels,
            'travel'          => $travel,
            'suggestedCode'   => $suggestedCode,
        ]);
        $this->view('layouts/footer');
    }

    /**
     * Lưu Bảng quyết toán chi phí vào CSDL kèm các items và upload hóa đơn
     */
    public function storeClaim(): void
    {
        $this->checkPermission('expense.create');

        if (!$this->isPost()) {
            $this->redirect('expense/createClaim');
        }

        $employeeId = (int)$this->postData('employee_id');
        $travelRequestId = $this->postData('travel_request_id') ? (int)$this->postData('travel_request_id') : null;
        $projectId = $this->postData('project_id') ? (int)$this->postData('project_id') : null;
        $title = trim($this->postData('title', ''));
        $category = $this->postData('category', 'Travel');
        $submittedDate = $this->postData('submitted_date', date('Y-m-d'));
        $notes = trim($this->postData('notes', ''));

        // Xử lý tạm ứng đã khấu trừ
        $rawAdvance = $this->postData('advance_deducted', '0');
        $advanceDeducted = (float)str_replace(['.', ',', ' '], '', $rawAdvance);

        // Lấy danh sách items động từ form
        $itemDescriptions = $_POST['item_description'] ?? [];
        $itemCategories   = $_POST['item_category'] ?? [];
        $itemAmounts      = $_POST['item_amount'] ?? [];
        $itemDates        = $_POST['item_date'] ?? [];
        $itemNotes        = $_POST['item_notes'] ?? [];

        if ($employeeId <= 0 || empty($title) || empty($itemDescriptions)) {
            Session::setFlash('error', 'Vui lòng nhập tiêu đề quyết toán và ít nhất một khoản chi tiêu chi tiết.');
            $this->redirect('expense/createClaim');
        }

        // Tính tổng chi phí từ danh sách items
        $parsedItems = [];
        $totalAmount = 0.0;

        $uploadDir = APP_ROOT . '/../public/uploads/expenses/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        foreach ($itemDescriptions as $i => $desc) {
            $desc = trim($desc);
            if (empty($desc)) continue;

            $amtStr = str_replace(['.', ',', ' '], '', $itemAmounts[$i] ?? '0');
            $amt = (float)$amtStr;
            if ($amt <= 0) continue;

            $receiptPath = null;
            // Xử lý upload file hóa đơn/biên lai cho từng dòng nếu có
            if (isset($_FILES['item_receipt']['name'][$i]) && $_FILES['item_receipt']['error'][$i] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($_FILES['item_receipt']['name'][$i], PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'pdf', 'webp'];
                if (in_array($ext, $allowed, true)) {
                    $newFileName = 'receipt_' . time() . '_' . rand(100, 999) . '.' . $ext;
                    $destPath = $uploadDir . $newFileName;
                    if (move_uploaded_file($_FILES['item_receipt']['tmp_name'][$i], $destPath)) {
                        $receiptPath = 'public/uploads/expenses/' . $newFileName;
                    }
                }
            }

            $parsedItems[] = [
                'description'  => $desc,
                'category'     => $itemCategories[$i] ?? 'Other',
                'amount'       => $amt,
                'expense_date' => $itemDates[$i] ?? date('Y-m-d'),
                'receipt_path' => $receiptPath,
                'notes'        => trim($itemNotes[$i] ?? '') ?: null,
            ];

            $totalAmount += $amt;
        }

        if (empty($parsedItems) || $totalAmount <= 0) {
            Session::setFlash('error', 'Không có khoản chi hợp lệ nào được nhập.');
            $this->redirect('expense/createClaim');
        }

        $netPayable = $totalAmount - $advanceDeducted;

        $claimModel = $this->model('ExpenseClaim');
        $claimCode = $claimModel->generateClaimCode();

        $data = [
            'claim_code'        => $claimCode,
            'employee_id'       => $employeeId,
            'travel_request_id' => $travelRequestId,
            'project_id'        => $projectId,
            'title'             => $title,
            'category'          => $category,
            'total_amount'      => $totalAmount,
            'advance_deducted'  => $advanceDeducted,
            'net_payable'       => $netPayable,
            'status'            => 'Submitted',
            'submitted_date'    => $submittedDate,
            'notes'             => $notes ?: null,
        ];

        $db = Database::getInstance();
        $sql = "INSERT INTO expense_claims 
                (claim_code, employee_id, travel_request_id, project_id, title, category, 
                 total_amount, advance_deducted, net_payable, status, submitted_date, notes)
                VALUES 
                (:claim_code, :employee_id, :travel_request_id, :project_id, :title, :category, 
                 :total_amount, :advance_deducted, :net_payable, :status, :submitted_date, :notes)";

        if ($db->query($sql, $data)) {
            $newClaimId = $db->lastInsertId();

            // Lưu các items chi tiết
            $itemModel = $this->model('ExpenseItem');
            $itemModel->insertBatch($newClaimId, $parsedItems);

            // Nếu liên kết chuyến công tác, cập nhật trạng thái chuyến công tác sang 'Completed'
            if ($travelRequestId) {
                $travelModel = $this->model('TravelRequest');
                $travelModel->complete($travelRequestId);
            }

            $this->auditLog('CREATE', 'expense_claim', $newClaimId, null, $data, "Lập bảng quyết toán {$claimCode} tổng tiền " . number_format($totalAmount, 0, ',', '.') . " ₫");

            NotificationService::broadcastToRole(
                'cb_staff',
                'approval',
                'Bảng Quyết toán Chi phí mới',
                "Bảng quyết toán {$claimCode} ({$title}) số tiền " . number_format($totalAmount, 0, ',', '.') . " ₫ đang chờ kế toán kiểm tra và phê duyệt.",
                "expense/showClaim/{$newClaimId}"
            );

            Session::setFlash('success', "Tạo bảng quyết toán chi phí {$claimCode} thành công. Đang chờ phê duyệt.");
            $this->redirect('expense/showClaim/' . $newClaimId);
        } else {
            Session::setFlash('error', 'Đã xảy ra lỗi khi tạo bảng quyết toán.');
            $this->redirect('expense/createClaim');
        }
    }

    /**
     * Xem chi tiết Bảng quyết toán chi phí
     */
    public function showClaim(int $id): void
    {
        $this->checkPermission('expense.view');

        $claimModel = $this->model('ExpenseClaim');
        $claim = $claimModel->getByIdWithDetails($id);

        if (!$claim) {
            Session::setFlash('error', 'Bảng quyết toán chi phí không tồn tại.');
            $this->redirect('expense?tab=claims');
        }

        $this->view('layouts/header', ['pageTitle' => "Quyết toán Chi phí {$claim->claim_code}"]);
        $this->view('expense/show_claim', ['claim' => $claim]);
        $this->view('layouts/footer');
    }

    /**
     * In Biên bản Thanh quyết toán Tài chính (Phiếu in A4 chuẩn)
     */
    public function printClaim(int $id): void
    {
        $this->checkPermission('expense.view');

        $claimModel = $this->model('ExpenseClaim');
        $claim = $claimModel->getByIdWithDetails($id);

        if (!$claim) {
            die("Không tìm thấy hồ sơ quyết toán #{$id}");
        }

        $this->view('expense/print_claim', ['claim' => $claim]);
    }

    /**
     * Phê duyệt Bảng quyết toán chi phí
     */
    public function approveClaim(int $id): void
    {
        $this->checkPermission('expense.approve');

        if (!$this->isPost()) {
            $this->redirect('expense/showClaim/' . $id);
        }

        $claimModel = $this->model('ExpenseClaim');
        $claim = $claimModel->find($id);

        if (!$claim || $claim->status !== 'Submitted') {
            Session::setFlash('error', 'Hồ sơ quyết toán không ở trạng thái Chờ duyệt.');
            $this->redirect('expense/showClaim/' . $id);
        }

        $approverId = Session::userId() ?: 1;

        if ($claimModel->approve($id, $approverId)) {
            $this->auditLog('APPROVE', 'expense_claim', $id, ['status' => 'Submitted'], ['status' => 'Approved'], "Phê duyệt quyết toán chi phí {$claim->claim_code}");

            $db = Database::getInstance();
            $db->query("SELECT id FROM users WHERE employee_id = :emp_id LIMIT 1", ['emp_id' => $claim->employee_id]);
            $uRow = $db->fetch();
            if ($uRow) {
                NotificationService::send(
                    (int)$uRow['id'],
                    'alert',
                    'Bảng Quyết toán được Phê duyệt',
                    "Bảng quyết toán {$claim->claim_code} ({$claim->title}) đã được phê duyệt và chuyển sang chờ chi trả.",
                    "expense/showClaim/{$id}"
                );
            }

            Session::setFlash('success', "Đã phê duyệt quyết toán {$claim->claim_code}. Hồ sơ đã chuyển sang bước chi trả.");
        } else {
            Session::setFlash('error', 'Phê duyệt thất bại.');
        }

        $this->redirect('expense/showClaim/' . $id);
    }

    /**
     * Từ chối Bảng quyết toán chi phí
     */
    public function rejectClaim(int $id): void
    {
        $this->checkPermission('expense.approve');

        if (!$this->isPost()) {
            $this->redirect('expense/showClaim/' . $id);
        }

        $claimModel = $this->model('ExpenseClaim');
        $claim = $claimModel->find($id);

        if (!$claim || $claim->status !== 'Submitted') {
            Session::setFlash('error', 'Hồ sơ quyết toán không hợp lệ.');
            $this->redirect('expense/showClaim/' . $id);
        }

        $reason = trim($this->postData('rejected_reason', ''));
        if (empty($reason)) {
            Session::setFlash('error', 'Vui lòng cung cấp lý do từ chối.');
            $this->redirect('expense/showClaim/' . $id);
        }

        $approverId = Session::userId() ?: 1;

        if ($claimModel->reject($id, $approverId, $reason)) {
            $this->auditLog('REJECT', 'expense_claim', $id, ['status' => 'Submitted'], ['status' => 'Rejected', 'reason' => $reason], "Từ chối quyết toán chi phí {$claim->claim_code}");

            $db = Database::getInstance();
            $db->query("SELECT id FROM users WHERE employee_id = :emp_id LIMIT 1", ['emp_id' => $claim->employee_id]);
            $uRow = $db->fetch();
            if ($uRow) {
                NotificationService::send(
                    (int)$uRow['id'],
                    'alert',
                    'Bảng Quyết toán bị Từ chối',
                    "Bảng quyết toán {$claim->claim_code} đã bị từ chối với lý do: {$reason}",
                    "expense/showClaim/{$id}"
                );
            }

            Session::setFlash('success', "Đã từ chối bảng quyết toán {$claim->claim_code}.");
        } else {
            Session::setFlash('error', 'Từ chối thất bại.');
        }

        $this->redirect('expense/showClaim/' . $id);
    }

    /**
     * Đánh dấu đã chi trả tiền quyết toán (Thủ quỹ / Kế toán thanh toán)
     */
    public function markPaid(int $id): void
    {
        $this->checkPermission('expense.pay');

        if (!$this->isPost()) {
            $this->redirect('expense/showClaim/' . $id);
        }

        $claimModel = $this->model('ExpenseClaim');
        $claim = $claimModel->find($id);

        if (!$claim || $claim->status !== 'Approved') {
            Session::setFlash('error', 'Hồ sơ phải được Phê duyệt trước khi thực hiện chi trả.');
            $this->redirect('expense/showClaim/' . $id);
        }

        $paidDate = $this->postData('paid_date', date('Y-m-d'));
        $method = $this->postData('payment_method', 'Bank Transfer');
        $payerId = Session::userId() ?: 1;

        if ($claimModel->markPaid($id, $payerId, $paidDate, $method)) {
            $this->auditLog('PAY', 'expense_claim', $id, ['status' => 'Approved'], ['status' => 'Paid', 'paid_date' => $paidDate], "Xác nhận chi trả tiền quyết toán {$claim->claim_code}");

            $db = Database::getInstance();
            $db->query("SELECT id FROM users WHERE employee_id = :emp_id LIMIT 1", ['emp_id' => $claim->employee_id]);
            $uRow = $db->fetch();
            if ($uRow) {
                NotificationService::send(
                    (int)$uRow['id'],
                    'alert',
                    'Đã Chi trả Tiền Quyết toán Chi phí',
                    "Hồ sơ {$claim->claim_code} số tiền " . number_format((float)$claim->net_payable, 0, ',', '.') . " ₫ đã được chi trả thành công.",
                    "expense/showClaim/{$id}"
                );
            }

            Session::setFlash('success', "Đã xác nhận chi trả hoàn tất cho bảng quyết toán {$claim->claim_code}.");
        } else {
            Session::setFlash('error', 'Thao tác chi trả thất bại.');
        }

        $this->redirect('expense/showClaim/' . $id);
    }

    /**
     * Báo cáo phân tích chi phí công tác & dự án
     */
    public function report(): void
    {
        $this->checkPermission('expense.report');

        $claimModel = $this->model('ExpenseClaim');
        $travelModel = $this->model('TravelRequest');

        $stats = $claimModel->getStats();
        $travelStats = $travelModel->getStats();
        $projectSummary = $claimModel->getSummaryByProject();
        $categorySummary = $claimModel->getSummaryByCategory();
        $deptSummary = $claimModel->getSummaryByDepartment();

        $this->view('layouts/header', ['pageTitle' => 'Báo cáo Chi phí Công tác & Dự án']);
        $this->view('expense/report', [
            'stats'           => $stats,
            'travelStats'     => $travelStats,
            'projectSummary'  => $projectSummary,
            'categorySummary' => $categorySummary,
            'deptSummary'     => $deptSummary,
        ]);
        $this->view('layouts/footer');
    }
}
