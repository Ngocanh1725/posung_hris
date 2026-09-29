<?php
/**
 * ============================================================
 *  POSUNG HRIS – LoanController
 * ============================================================
 *  Quản lý Tạm ứng Lương & Khoản vay Nhân viên (Employee Loans)
 * ============================================================
 */

class LoanController extends Controller
{
    /**
     * Danh sách khoản vay & tạm ứng (Bộ lọc, Thống kê, Bảng dữ liệu)
     */
    public function index(): void
    {
        $this->checkPermission('loan.view');

        $loanModel = $this->model('Loan');
        $typeModel = $this->model('LoanType');
        $deptModel = $this->model('Department');

        $filters = [
            'search'        => trim($this->getData('search', '')),
            'status'        => $this->getData('status', ''),
            'loan_type_id'  => $this->getData('loan_type_id', ''),
            'department_id' => $this->getData('department_id', ''),
            'from_date'     => $this->getData('from_date', ''),
            'to_date'       => $this->getData('to_date', ''),
        ];

        $loans = $loanModel->getAllWithRelations($filters);
        $types = $typeModel->all();
        $departments = $deptModel->all();
        $stats = $loanModel->getStats();

        $this->view('layouts/header', ['pageTitle' => 'Quản lý Tạm ứng & Khoản vay']);
        $this->view('loan/index', [
            'loans'       => $loans,
            'types'       => $types,
            'departments' => $departments,
            'filters'     => $filters,
            'stats'       => $stats,
        ]);
        $this->view('layouts/footer');
    }

    /**
     * Form tạo đề xuất tạm ứng / vay vốn mới
     */
    public function create(): void
    {
        $this->checkPermission('loan.create');

        $employeeModel = $this->model('Employee');
        $typeModel = $this->model('LoanType');
        $loanModel = $this->model('Loan');

        $employees = $employeeModel->getAll();
        $types = $typeModel->getActiveTypes();
        $suggestedCode = $loanModel->generateLoanCode();
        $selectedEmpId = (int)$this->getData('employee_id', 0);

        $this->view('layouts/header', ['pageTitle' => 'Tạo Đề xuất Tạm ứng / Vay vốn']);
        $this->view('loan/create', [
            'employees'     => $employees,
            'types'         => $types,
            'suggestedCode' => $suggestedCode,
            'selectedEmpId' => $selectedEmpId,
        ]);
        $this->view('layouts/footer');
    }

    /**
     * Lưu đề xuất tạm ứng / khoản vay vào CSDL
     */
    public function store(): void
    {
        $this->checkPermission('loan.create');

        if (!$this->isPost()) {
            $this->redirect('loan/create');
        }

        $employeeId = (int)$this->postData('employee_id');
        $loanTypeId = (int)$this->postData('loan_type_id');
        $termMonths = max(1, (int)$this->postData('term_months', 1));
        $appliedDate = $this->postData('applied_date', date('Y-m-d'));
        $reason = trim($this->postData('reason', ''));
        $disbursementMethod = $this->postData('disbursement_method', 'Bank Transfer');
        $notes = trim($this->postData('notes', ''));

        // Làm sạch số tiền nhập vào
        $rawAmount = $this->postData('amount', '0');
        $cleanAmount = str_replace(['.', ',', ' '], '', $rawAmount);
        $amount = (float)$cleanAmount;

        if ($employeeId <= 0 || $loanTypeId <= 0 || $amount <= 0 || empty($reason)) {
            Session::setFlash('error', 'Vui lòng điền đầy đủ các thông tin bắt buộc (Nhân viên, Loại vay, Số tiền, Lý do).');
            $this->redirect('loan/create');
        }

        $typeModel = $this->model('LoanType');
        $loanType = $typeModel->find($loanTypeId);
        if (!$loanType) {
            Session::setFlash('error', 'Loại khoản vay không hợp lệ.');
            $this->redirect('loan/create');
        }

        // Kiểm tra hạn mức tối đa
        if (!empty($loanType->max_amount) && $amount > (float)$loanType->max_amount) {
            Session::setFlash('error', 'Số tiền đề xuất (' . number_format($amount, 0, ',', '.') . ' ₫) vượt quá hạn mức cho phép của loại vay này (' . number_format((float)$loanType->max_amount, 0, ',', '.') . ' ₫).');
            $this->redirect('loan/create');
        }

        // Kiểm tra kỳ hạn tối đa
        if (!empty($loanType->max_term_months) && $termMonths > (int)$loanType->max_term_months) {
            Session::setFlash('error', 'Kỳ hạn đề xuất (' . $termMonths . ' tháng) vượt quá kỳ hạn tối đa (' . $loanType->max_term_months . ' tháng).');
            $this->redirect('loan/create');
        }

        $interestRate = (float)($loanType->interest_rate ?? 0.0);
        $calc = Loan::calculateEmi($amount, $interestRate, $termMonths);

        $loanModel = $this->model('Loan');
        $loanCode = $loanModel->generateLoanCode();

        $data = [
            'loan_code'           => $loanCode,
            'employee_id'         => $employeeId,
            'loan_type_id'        => $loanTypeId,
            'amount'              => $amount,
            'interest_rate'       => $interestRate,
            'term_months'         => $termMonths,
            'monthly_emi'         => $calc['monthly_emi'],
            'total_repayment'     => $calc['total_repayment'],
            'total_paid'          => 0.00,
            'remaining_balance'   => $calc['total_repayment'],
            'status'              => 'Pending',
            'applied_date'        => $appliedDate,
            'disbursement_method' => $disbursementMethod,
            'reason'              => $reason,
            'notes'               => $notes ?: null,
        ];

        $db = Database::getInstance();
        $sql = "INSERT INTO employee_loans 
                (loan_code, employee_id, loan_type_id, amount, interest_rate, term_months, monthly_emi,
                 total_repayment, total_paid, remaining_balance, status, applied_date, disbursement_method, reason, notes)
                VALUES 
                (:loan_code, :employee_id, :loan_type_id, :amount, :interest_rate, :term_months, :monthly_emi,
                 :total_repayment, :total_paid, :remaining_balance, :status, :applied_date, :disbursement_method, :reason, :notes)";

        if ($db->query($sql, $data)) {
            $newId = $db->lastInsertId();

            // Ghi Audit Log
            $this->auditLog('CREATE', 'loan', $newId, null, $data, "Tạo đề xuất khoản vay {$loanCode} cho nhân viên ID {$employeeId}");

            // Gửi thông báo đến HR/C&B
            $employeeModel = $this->model('Employee');
            $emp = $employeeModel->find($employeeId);
            $empName = $emp ? $emp->full_name : "ID {$employeeId}";

            NotificationService::broadcastToRole(
                'cb_staff',
                'approval',
                'Đề xuất Tạm ứng / Khoản vay mới',
                "Nhân viên {$empName} vừa gửi đề xuất vay {$loanCode} số tiền " . number_format($amount, 0, ',', '.') . " ₫ cần phê duyệt.",
                "loan/show/{$newId}"
            );

            Session::setFlash('success', "Tạo đề xuất tạm ứng / khoản vay {$loanCode} thành công. Đang chờ phê duyệt.");
            $this->redirect('loan/show/' . $newId);
        } else {
            Session::setFlash('error', 'Đã xảy ra lỗi khi tạo đề xuất khoản vay. Vui lòng thử lại.');
            $this->redirect('loan/create');
        }
    }

    /**
     * Chi tiết khoản vay, timeline trả nợ & các nút thao tác
     */
    public function show(int $id): void
    {
        $this->checkPermission('loan.view');

        $loanModel = $this->model('Loan');
        $loan = $loanModel->getByIdWithDetails($id);

        if (!$loan) {
            Session::setFlash('error', 'Không tìm thấy thông tin khoản vay.');
            $this->redirect('loan');
        }

        $this->view('layouts/header', ['pageTitle' => "Chi tiết Khoản vay {$loan->loan_code}"]);
        $this->view('loan/show', ['loan' => $loan]);
        $this->view('layouts/footer');
    }

    /**
     * Phê duyệt đề xuất vay vốn
     */
    public function approve(int $id): void
    {
        $this->checkPermission('loan.approve');

        if (!$this->isPost()) {
            $this->redirect('loan/show/' . $id);
        }

        $loanModel = $this->model('Loan');
        $loan = $loanModel->find($id);

        if (!$loan) {
            Session::setFlash('error', 'Khoản vay không tồn tại.');
            $this->redirect('loan');
        }

        if ($loan->status !== 'Pending') {
            Session::setFlash('error', 'Khoản vay này không ở trạng thái Chờ duyệt.');
            $this->redirect('loan/show/' . $id);
        }

        $disbDate = $this->postData('disbursement_date', date('Y-m-d'));
        $disbMethod = $this->postData('disbursement_method', $loan->disbursement_method ?? 'Bank Transfer');
        $approverId = Session::userId() ?: 1;

        if ($loanModel->approve($id, $approverId, $disbDate, $disbMethod)) {
            // Ghi Audit Log
            $this->auditLog('APPROVE', 'loan', $id, ['status' => 'Pending'], ['status' => 'Active', 'disbursement_date' => $disbDate], "Phê duyệt khoản vay {$loan->loan_code}");

            // Thông báo cho nhân viên qua user nếu có
            $db = Database::getInstance();
            $db->query("SELECT id FROM users WHERE employee_id = :emp_id LIMIT 1", ['emp_id' => $loan->employee_id]);
            $uRow = $db->fetch();
            if ($uRow) {
                NotificationService::send(
                    (int)$uRow['id'],
                    'alert',
                    'Đề xuất Vay / Tạm ứng được Phê duyệt',
                    "Khoản vay {$loan->loan_code} với số tiền " . number_format((float)$loan->amount, 0, ',', '.') . " ₫ đã được phê duyệt và giải ngân.",
                    "loan/show/{$id}"
                );
            }

            Session::setFlash('success', "Đã phê duyệt và kích hoạt giải ngân khoản vay {$loan->loan_code}.");
        } else {
            Session::setFlash('error', 'Phê duyệt thất bại. Vui lòng kiểm tra lại.');
        }

        $this->redirect('loan/show/' . $id);
    }

    /**
     * Từ chối đề xuất vay
     */
    public function reject(int $id): void
    {
        $this->checkPermission('loan.approve');

        if (!$this->isPost()) {
            $this->redirect('loan/show/' . $id);
        }

        $loanModel = $this->model('Loan');
        $loan = $loanModel->find($id);

        if (!$loan || $loan->status !== 'Pending') {
            Session::setFlash('error', 'Khoản vay không tồn tại hoặc không ở trạng thái Chờ duyệt.');
            $this->redirect('loan');
        }

        $reason = trim($this->postData('rejected_reason', ''));
        if (empty($reason)) {
            Session::setFlash('error', 'Vui lòng cung cấp lý do từ chối.');
            $this->redirect('loan/show/' . $id);
        }

        $approverId = Session::userId() ?: 1;

        if ($loanModel->reject($id, $approverId, $reason)) {
            $this->auditLog('REJECT', 'loan', $id, ['status' => 'Pending'], ['status' => 'Rejected', 'reason' => $reason], "Từ chối đề xuất khoản vay {$loan->loan_code}");

            // Thông báo cho nhân viên
            $db = Database::getInstance();
            $db->query("SELECT id FROM users WHERE employee_id = :emp_id LIMIT 1", ['emp_id' => $loan->employee_id]);
            $uRow = $db->fetch();
            if ($uRow) {
                NotificationService::send(
                    (int)$uRow['id'],
                    'alert',
                    'Đề xuất Vay / Tạm ứng bị Từ chối',
                    "Khoản vay {$loan->loan_code} đã bị từ chối với lý do: {$reason}",
                    "loan/show/{$id}"
                );
            }

            Session::setFlash('success', "Đã từ chối đề xuất khoản vay {$loan->loan_code}.");
        } else {
            Session::setFlash('error', 'Thao tác từ chối thất bại.');
        }

        $this->redirect('loan/show/' . $id);
    }

    /**
     * Ghi nhận thanh toán / hoàn trả nợ vay (Thủ công)
     */
    public function repay(int $id): void
    {
        $this->checkPermission('loan.repay');

        if (!$this->isPost()) {
            $this->redirect('loan/show/' . $id);
        }

        $loanModel = $this->model('Loan');
        $loan = $loanModel->find($id);

        if (!$loan || $loan->status !== 'Active') {
            Session::setFlash('error', 'Khoản vay không tồn tại hoặc không đang hoạt động.');
            $this->redirect('loan/show/' . $id);
        }

        $rawAmount = $this->postData('amount', '0');
        $cleanAmount = str_replace(['.', ',', ' '], '', $rawAmount);
        $amount = (float)$cleanAmount;

        $paymentDate = $this->postData('payment_date', date('Y-m-d'));
        $paymentMethod = $this->postData('payment_method', 'Cash');
        $notes = trim($this->postData('notes', ''));
        $userId = Session::userId() ?: 1;

        if ($amount <= 0) {
            Session::setFlash('error', 'Số tiền thanh toán phải lớn hơn 0.');
            $this->redirect('loan/show/' . $id);
        }

        $result = $loanModel->recordRepayment(
            $id,
            $amount,
            $paymentDate,
            $paymentMethod,
            'Manual',
            null,
            $userId,
            $notes
        );

        if ($result['success']) {
            $this->auditLog('REPAY', 'loan', $id, null, ['amount' => $amount, 'balance_after' => $result['remaining_balance']], "Ghi nhận trả nợ " . number_format($amount, 0, ',', '.') . " ₫ cho khoản vay {$loan->loan_code}");

            $msg = "Ghi nhận thanh toán thành công " . number_format($result['payment_amount'], 0, ',', '.') . " ₫. Dư nợ còn lại: " . number_format($result['remaining_balance'], 0, ',', '.') . " ₫.";
            if ($result['is_closed']) {
                $msg .= " Khoản vay đã được tất toán (Closed).";
            }
            Session::setFlash('success', $msg);
        } else {
            Session::setFlash('error', $result['message'] ?? 'Thao tác thất bại.');
        }

        $this->redirect('loan/show/' . $id);
    }

    /**
     * Quản lý Danh mục loại khoản vay & tạm ứng
     */
    public function types(): void
    {
        $this->checkPermission('loan.view');

        $typeModel = $this->model('LoanType');
        $types = $typeModel->all();

        $this->view('layouts/header', ['pageTitle' => 'Danh mục Loại Khoản vay & Tạm ứng']);
        $this->view('loan/types', ['types' => $types]);
        $this->view('layouts/footer');
    }

    /**
     * Thêm mới loại khoản vay
     */
    public function storeType(): void
    {
        $this->checkPermission('loan.create');

        if (!$this->isPost()) {
            $this->redirect('loan/types');
        }

        $typeCode = strtoupper(trim($this->postData('type_code', '')));
        $name = trim($this->postData('name', ''));
        $maxAmount = $this->postData('max_amount') ? (float)str_replace(['.', ',', ' '], '', $this->postData('max_amount')) : null;
        $maxTerm = max(1, (int)$this->postData('max_term_months', 12));
        $interestRate = (float)str_replace(',', '.', $this->postData('interest_rate', '0.00'));
        $description = trim($this->postData('description', ''));
        $isActive = (int)$this->postData('is_active', 1);

        if (empty($typeCode) || empty($name)) {
            Session::setFlash('error', 'Vui lòng nhập Mã loại và Tên loại khoản vay.');
            $this->redirect('loan/types');
        }

        $typeModel = $this->model('LoanType');
        if ($typeModel->checkCodeExists($typeCode)) {
            Session::setFlash('error', "Mã loại '{$typeCode}' đã tồn tại trong hệ thống.");
            $this->redirect('loan/types');
        }

        $db = Database::getInstance();
        $sql = "INSERT INTO loan_types (type_code, name, max_amount, max_term_months, interest_rate, description, is_active)
                VALUES (:type_code, :name, :max_amount, :max_term_months, :interest_rate, :description, :is_active)";

        if ($db->query($sql, [
            'type_code'       => $typeCode,
            'name'            => $name,
            'max_amount'      => $maxAmount,
            'max_term_months' => $maxTerm,
            'interest_rate'   => $interestRate,
            'description'     => $description ?: null,
            'is_active'       => $isActive,
        ])) {
            $this->auditLog('CREATE', 'loan_type', $db->lastInsertId(), null, ['code' => $typeCode, 'name' => $name], "Thêm mới loại khoản vay {$name}");
            Session::setFlash('success', "Thêm loại khoản vay '{$name}' thành công.");
        } else {
            Session::setFlash('error', 'Đã xảy ra lỗi khi thêm loại khoản vay.');
        }

        $this->redirect('loan/types');
    }

    /**
     * Cập nhật loại khoản vay
     */
    public function updateType(int $id): void
    {
        $this->checkPermission('loan.create');

        if (!$this->isPost()) {
            $this->redirect('loan/types');
        }

        $typeModel = $this->model('LoanType');
        $type = $typeModel->find($id);
        if (!$type) {
            Session::setFlash('error', 'Loại khoản vay không tồn tại.');
            $this->redirect('loan/types');
        }

        $typeCode = strtoupper(trim($this->postData('type_code', '')));
        $name = trim($this->postData('name', ''));
        $maxAmount = $this->postData('max_amount') ? (float)str_replace(['.', ',', ' '], '', $this->postData('max_amount')) : null;
        $maxTerm = max(1, (int)$this->postData('max_term_months', 12));
        $interestRate = (float)str_replace(',', '.', $this->postData('interest_rate', '0.00'));
        $description = trim($this->postData('description', ''));
        $isActive = (int)$this->postData('is_active', 1);

        if (empty($typeCode) || empty($name)) {
            Session::setFlash('error', 'Vui lòng nhập đầy đủ thông tin.');
            $this->redirect('loan/types');
        }

        if ($typeModel->checkCodeExists($typeCode, $id)) {
            Session::setFlash('error', "Mã loại '{$typeCode}' đã bị trùng lặp.");
            $this->redirect('loan/types');
        }

        $db = Database::getInstance();
        $sql = "UPDATE loan_types 
                SET type_code = :type_code,
                    name = :name,
                    max_amount = :max_amount,
                    max_term_months = :max_term_months,
                    interest_rate = :interest_rate,
                    description = :description,
                    is_active = :is_active
                WHERE id = :id";

        if ($db->query($sql, [
            'id'              => $id,
            'type_code'       => $typeCode,
            'name'            => $name,
            'max_amount'      => $maxAmount,
            'max_term_months' => $maxTerm,
            'interest_rate'   => $interestRate,
            'description'     => $description ?: null,
            'is_active'       => $isActive,
        ])) {
            $this->auditLog('UPDATE', 'loan_type', $id, (array)$type, ['name' => $name], "Cập nhật loại khoản vay {$name}");
            Session::setFlash('success', "Cập nhật loại khoản vay '{$name}' thành công.");
        } else {
            Session::setFlash('error', 'Cập nhật thất bại.');
        }

        $this->redirect('loan/types');
    }

    /**
     * Xóa loại khoản vay (chỉ khi chưa có khoản vay nào liên kết)
     */
    public function deleteType(int $id): void
    {
        $this->checkPermission('loan.delete');

        $typeModel = $this->model('LoanType');
        $type = $typeModel->find($id);
        if (!$type) {
            Session::setFlash('error', 'Loại khoản vay không tồn tại.');
            $this->redirect('loan/types');
        }

        $db = Database::getInstance();
        $db->query("SELECT COUNT(*) AS cnt FROM employee_loans WHERE loan_type_id = :id", ['id' => $id]);
        $row = $db->fetch();

        if ($row && (int)$row['cnt'] > 0) {
            Session::setFlash('error', "Không thể xóa loại khoản vay này vì đang có {$row['cnt']} khoản vay của nhân viên đang áp dụng.");
            $this->redirect('loan/types');
        }

        $db->query("DELETE FROM loan_types WHERE id = :id", ['id' => $id]);
        $this->auditLog('DELETE', 'loan_type', $id, (array)$type, null, "Xóa loại khoản vay {$type->name}");
        Session::setFlash('success', "Đã xóa loại khoản vay '{$type->name}'.");
        $this->redirect('loan/types');
    }

    /**
     * Báo cáo phân tích dư nợ & tạm ứng nhân viên
     */
    public function report(): void
    {
        $this->checkPermission('loan.report');

        $loanModel = $this->model('Loan');
        $stats = $loanModel->getStats();
        $deptSummary = $loanModel->getSummaryByDepartment();
        $typeSummary = $loanModel->getSummaryByType();

        $this->view('layouts/header', ['pageTitle' => 'Báo cáo Dư nợ Tạm ứng & Khoản vay']);
        $this->view('loan/report', [
            'stats'       => $stats,
            'deptSummary' => $deptSummary,
            'typeSummary' => $typeSummary,
        ]);
        $this->view('layouts/footer');
    }

    /**
     * API AJAX: Tính toán trước EMI trả góp
     */
    public function calculateEmi(): void
    {
        $amount = (float)str_replace(['.', ',', ' '], '', $this->postData('amount', $this->getData('amount', '0')));
        $rate = (float)str_replace(',', '.', $this->postData('interest_rate', $this->getData('interest_rate', '0')));
        $termMonths = max(1, (int)$this->postData('term_months', $this->getData('term_months', '1')));

        $result = Loan::calculateEmi($amount, $rate, $termMonths);
        $this->json(['status' => 'success', 'data' => $result]);
    }

    /**
     * API AJAX: Lấy danh sách khoản vay của 1 nhân viên
     */
    public function employeeLoans(int $employeeId): void
    {
        $this->checkPermission('loan.view');

        $loanModel = $this->model('Loan');
        $loans = $loanModel->getByEmployee($employeeId);
        $this->json(['status' => 'success', 'data' => $loans]);
    }
}
