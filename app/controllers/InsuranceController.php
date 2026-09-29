<?php
/**
 * ============================================================
 *  POSUNG HRIS – Controller Insurance (Quản lý Bảo hiểm Xã hội)
 * ============================================================
 */

class InsuranceController extends Controller
{
    /**
     * Dashboard Bảo hiểm Xã hội
     */
    public function index(): void
    {
        $this->checkPermission('employee.view');

        $insModel = $this->model('Insurance');
        $month = (int)$this->getData('month', date('m'));
        $year  = (int)$this->getData('year', date('Y'));

        $stats = $insModel->getDashboardStats($month, $year);
        $recentAdjustments = $insModel->getAdjustments(['month' => sprintf('%04d-%02d', $year, $month)]);
        $recentClaims = $insModel->getClaims(['year' => $year]);

        $this->view('layouts/header', ['pageTitle' => 'Tổng quan Bảo hiểm Xã hội & Y tế']);
        $this->view('insurance/index', [
            'stats'             => $stats,
            'recentAdjustments' => array_slice($recentAdjustments, 0, 5),
            'recentClaims'      => array_slice($recentClaims, 0, 5),
            'month'             => $month,
            'year'              => $year,
        ]);
        $this->view('layouts/footer');
    }

    /**
     * Danh sách nhân viên tham gia bảo hiểm
     */
    public function employees(): void
    {
        $this->checkPermission('employee.view');

        $insModel  = $this->model('Insurance');
        $deptModel = $this->model('Department');

        $filters = [
            'dept_id' => $this->getData('dept_id', ''),
            'status'  => $this->getData('status', ''),
            'search'  => $this->getData('search', ''),
        ];

        $employees = $insModel->getEmployeesInsurance($filters);
        $departments = $deptModel->all('dept_code ASC');

        $this->view('layouts/header', ['pageTitle' => 'Danh sách Nhân sự Tham gia Bảo hiểm']);
        $this->view('insurance/employees', [
            'employees'   => $employees,
            'departments' => $departments,
            'filters'     => $filters,
        ]);
        $this->view('layouts/footer');
    }

    /**
     * Form Đăng ký / Chỉnh sửa thông tin BH của 1 nhân viên
     */
    public function register(int $employeeId = 0): void
    {
        $this->checkPermission('employee.edit');

        if ($employeeId <= 0) {
            Session::setFlash('error', 'Vui lòng chọn nhân viên cần đăng ký bảo hiểm.');
            $this->redirect('insurance/employees');
            return;
        }

        $insModel = $this->model('Insurance');
        $empModel = $this->model('Employee');

        $employee = $empModel->getById($employeeId);
        if (!$employee) {
            Session::setFlash('error', 'Không tìm thấy hồ sơ nhân viên này.');
            $this->redirect('insurance/employees');
            return;
        }

        $insurance = $insModel->getInsuranceByEmployeeId($employeeId);

        $this->view('layouts/header', ['pageTitle' => 'Hồ sơ Bảo hiểm: ' . $employee->full_name]);
        $this->view('insurance/register', [
            'employee'  => $employee,
            'insurance' => $insurance,
        ]);
        $this->view('layouts/footer');
    }

    /**
     * Lưu hồ sơ bảo hiểm nhân viên (POST)
     */
    public function saveRegistration(): void
    {
        $this->checkPermission('employee.edit');

        if (!$this->isPost()) {
            $this->redirect('insurance/employees');
            return;
        }

        $employeeId = (int)$this->postData('employee_id');
        if ($employeeId <= 0) {
            Session::setFlash('error', 'Dữ liệu không hợp lệ.');
            $this->redirect('insurance/employees');
            return;
        }

        $insModel = $this->model('Insurance');
        $insModel->saveEmployeeInsurance($_POST);

        Session::setFlash('success', 'Đã lưu và cập nhật hồ sơ bảo hiểm nhân sự thành công!');
        $this->redirect("insurance/register/{$employeeId}");
    }

    /**
     * Quản lý biến động lao động BH (Báo tăng / giảm D02-TS)
     */
    public function adjust(): void
    {
        $this->checkPermission('employee.view');

        $insModel = $this->model('Insurance');
        $empModel = $this->model('Employee');

        $filters = [
            'month'  => $this->getData('month', date('Y-m')),
            'type'   => $this->getData('type', ''),
            'status' => $this->getData('status', ''),
            'search' => $this->getData('search', ''),
        ];

        $adjustments = $insModel->getAdjustments($filters);
        $employees = $empModel->all('full_name ASC');

        $this->view('layouts/header', ['pageTitle' => 'Quản lý Biến động Bảo hiểm (Báo Tăng/Giảm)']);
        $this->view('insurance/adjust', [
            'adjustments' => $adjustments,
            'employees'   => $employees,
            'filters'     => $filters,
        ]);
        $this->view('layouts/footer');
    }

    /**
     * Lưu đợt báo tăng/giảm (POST)
     */
    public function saveAdjustment(): void
    {
        $this->checkPermission('employee.edit');

        if (!$this->isPost()) {
            $this->redirect('insurance/adjust');
            return;
        }

        $insModel = $this->model('Insurance');
        $insModel->createAdjustment($_POST);

        Session::setFlash('success', 'Đã ghi nhận bản ghi biến động bảo hiểm thành công!');
        $this->redirect('insurance/adjust');
    }

    /**
     * Cập nhật trạng thái đợt biến động D02-TS (POST/AJAX)
     */
    public function updateAdjustmentStatus(int $id = 0): void
    {
        $this->checkPermission('employee.edit');

        $status = $this->postData('status');
        if ($id <= 0 || empty($status)) {
            $this->json(['success' => false, 'message' => 'Dữ liệu không hợp lệ.'], 400);
            return;
        }

        $insModel = $this->model('Insurance');
        if ($insModel->updateAdjustmentStatus($id, $status)) {
            $this->json(['success' => true, 'message' => 'Cập nhật trạng thái thành công!']);
        } else {
            $this->json(['success' => false, 'message' => 'Không tìm thấy bản ghi biến động.'], 404);
        }
    }

    /**
     * Quản lý Chế độ Bảo hiểm Đã hưởng (C70a-HD)
     */
    public function claims(): void
    {
        $this->checkPermission('employee.view');

        $insModel = $this->model('Insurance');
        $empModel = $this->model('Employee');

        $filters = [
            'type'   => $this->getData('type', ''),
            'status' => $this->getData('status', ''),
            'year'   => $this->getData('year', date('Y')),
            'search' => $this->getData('search', ''),
        ];

        $claims = $insModel->getClaims($filters);
        $employees = $empModel->all('full_name ASC');

        $this->view('layouts/header', ['pageTitle' => 'Quản lý Chế độ Bảo hiểm (Ốm đau, Thai sản)']);
        $this->view('insurance/claims', [
            'claims'    => $claims,
            'employees' => $employees,
            'filters'   => $filters,
        ]);
        $this->view('layouts/footer');
    }

    /**
     * Lưu hồ sơ chế độ bảo hiểm (POST)
     */
    public function saveClaim(): void
    {
        $this->checkPermission('employee.edit');

        if (!$this->isPost()) {
            $this->redirect('insurance/claims');
            return;
        }

        $insModel = $this->model('Insurance');
        $insModel->saveClaim($_POST);

        Session::setFlash('success', 'Đã lưu hồ sơ chế độ bảo hiểm thành công!');
        $this->redirect('insurance/claims');
    }

    /**
     * Cập nhật trạng thái duyệt / chi trả chế độ (AJAX)
     */
    public function updateClaimStatus(int $id = 0): void
    {
        $this->checkPermission('employee.edit');

        $status = $this->postData('status');
        $amount = $this->postData('claim_amount') !== null ? (float)str_replace([',', ' '], '', $this->postData('claim_amount')) : null;

        if ($id <= 0 || empty($status)) {
            $this->json(['success' => false, 'message' => 'Dữ liệu không hợp lệ.'], 400);
            return;
        }

        $insModel = $this->model('Insurance');
        if ($insModel->updateClaimStatus($id, $status, $amount)) {
            $this->json(['success' => true, 'message' => 'Đã cập nhật trạng thái chế độ thành công!']);
        } else {
            $this->json(['success' => false, 'message' => 'Lỗi cập nhật chế độ.'], 400);
        }
    }

    /**
     * Quản lý tỷ lệ đóng bảo hiểm theo năm
     */
    public function rates(): void
    {
        $this->checkPermission('employee.view');

        $insModel = $this->model('Insurance');
        $year = (int)$this->getData('year', date('Y'));

        $rates = $insModel->getRates($year);

        $this->view('layouts/header', ['pageTitle' => 'Quản lý Tỷ lệ Đóng BHXH, BHYT, BHTN']);
        $this->view('insurance/rates', [
            'rates' => $rates,
            'year'  => $year,
        ]);
        $this->view('layouts/footer');
    }

    /**
     * Lưu tỷ lệ đóng bảo hiểm (POST)
     */
    public function saveRate(): void
    {
        $this->checkPermission('employee.edit');

        if (!$this->isPost()) {
            $this->redirect('insurance/rates');
            return;
        }

        $insModel = $this->model('Insurance');
        $insModel->saveRate($_POST);

        Session::setFlash('success', 'Đã lưu cấu hình tỷ lệ đóng bảo hiểm thành công!');
        $this->redirect('insurance/rates?year=' . ($this->postData('effective_year') ?? date('Y')));
    }

    /**
     * Báo cáo tổng hợp BHXH (Mẫu D02-TS & C70a-HD)
     */
    public function report(): void
    {
        $this->checkPermission('employee.view');

        $insModel = $this->model('Insurance');
        $reportType = $this->getData('type', 'D02TS'); // 'D02TS' or 'C70aHD'
        $month = $this->getData('month', date('Y-m'));

        if ($reportType === 'C70aHD') {
            $data = $insModel->getReportC70aHD($month);
        } else {
            $data = $insModel->getReportD02TS($month);
        }

        // Xuất file CSV / Excel nếu có yêu cầu
        if ($this->getData('export') === 'csv') {
            $this->exportReportCsv($reportType, $data, $month);
            return;
        }

        $this->view('layouts/header', ['pageTitle' => 'Báo cáo Tổng hợp BHXH (' . $reportType . ')']);
        $this->view('insurance/report', [
            'reportType' => $reportType,
            'month'      => $month,
            'data'       => $data,
        ]);
        $this->view('layouts/footer');
    }

    /**
     * Bảng tính tiền đóng bảo hiểm chi tiết tháng
     */
    public function monthlyCalculation(?int $month = null, ?int $year = null): void
    {
        $this->checkPermission('employee.view');

        $m = $month ?? (int)$this->getData('month', date('m'));
        $y = $year ?? (int)$this->getData('year', date('Y'));

        $insModel = $this->model('Insurance');
        $calcData = $insModel->monthlyCalculation($m, $y);

        // Xuất file CSV nếu có tham số
        if ($this->getData('export') === 'csv') {
            $this->exportMonthlyCalcCsv($calcData, $m, $y);
            return;
        }

        $this->view('layouts/header', ['pageTitle' => "Bảng Tính Tiền Đóng Bảo Hiểm Tháng {$m}/{$y}"]);
        $this->view('insurance/monthly_calc', $calcData);
        $this->view('layouts/footer');
    }

    // ── Helper Xuất CSV Báo cáo ────────────────────────────────
    private function exportReportCsv(string $type, array $data, string $month): void
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="BaoCao_BHXH_' . $type . '_' . $month . '.csv"');
        $output = fopen('php://output', 'w');
        fputs($output, "\xEF\xBB\xBF"); // BOM UTF-8

        if ($type === 'D02TS') {
            fputcsv($output, ['DANH SÁCH LAO ĐỘNG THAM GIA BHXH, BHYT, BHTN (MẪU D02-TS) - THÁNG ' . $month]);
            fputcsv($output, ['STT', 'Mã NV', 'Họ và tên', 'Số sổ BHXH', 'Phòng ban', 'Chức danh', 'Loại biến động', 'Mức lương cũ', 'Mức lương mới', 'Ngày hiệu lực', 'Lý do']);
            $stt = 1;
            foreach (array_merge($data['increases'], $data['decreases']) as $r) {
                fputcsv($output, [
                    $stt++,
                    $r['emp_code'],
                    $r['full_name'],
                    $r['social_insurance_no'] ?? '',
                    $r['dept_name'] ?? '',
                    $r['pos_title'] ?? '',
                    $r['adjustment_type'],
                    $r['old_salary'],
                    $r['new_salary'],
                    $r['effective_date'],
                    $r['reason']
                ]);
            }
        } else {
            fputcsv($output, ['DANH SÁCH HƯỞNG CHẾ ĐỘ ỐM ĐAU, THAI SẢN (MẪU C70a-HD) - THÁNG ' . $month]);
            fputcsv($output, ['STT', 'Mã NV', 'Họ và tên', 'Số sổ BHXH', 'Chế độ', 'Từ ngày', 'Đến ngày', 'Số ngày', 'Số tiền trợ cấp', 'Số tài khoản', 'Ngân hàng', 'Trạng thái']);
            $stt = 1;
            foreach ($data['by_type'] as $items) {
                foreach ($items as $c) {
                    fputcsv($output, [
                        $stt++,
                        $c['emp_code'],
                        $c['full_name'],
                        $c['social_insurance_no'] ?? '',
                        $c['claim_type'],
                        $c['from_date'],
                        $c['to_date'],
                        $c['leave_days'],
                        $c['claim_amount'],
                        $c['bank_account'] ?? '',
                        $c['bank_name'] ?? '',
                        $c['status']
                    ]);
                }
            }
        }
        fclose($output);
        exit;
    }

    private function exportMonthlyCalcCsv(array $data, int $m, int $y): void
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="Bang_Tinh_Dong_BHXH_' . $m . '_' . $y . '.csv"');
        $output = fopen('php://output', 'w');
        fputs($output, "\xEF\xBB\xBF");

        fputcsv($output, ["BẢNG TÍNH TIỀN ĐÓNG BẢO HIỂM XÃ HỘI, BHYT, BHTN - THÁNG {$m}/{$y}"]);
        fputcsv($output, [
            'STT', 'Mã NV', 'Họ và tên', 'Phòng ban', 'Số sổ BHXH', 'Lương đóng BH',
            'BHXH NLĐ (8%)', 'BHYT NLĐ (1.5%)', 'BHTN NLĐ (1%)', 'Tổng NLĐ (10.5%)',
            'BHXH DN (17.5%)', 'BHYT DN (3%)', 'BHTN DN (1%)', 'BHTNLĐ DN (0.5%)', 'Tổng DN (22%)',
            'Tổng cộng phải nộp'
        ]);

        $stt = 1;
        foreach ($data['list'] as $r) {
            fputcsv($output, [
                $stt++,
                $r['emp_code'],
                $r['full_name'],
                $r['dept_name'] ?? '',
                $r['social_insurance_no'] ?? '',
                $r['salary'],
                $r['emp_bhxh'],
                $r['emp_bhyt'],
                $r['emp_bhtn'],
                $r['emp_total'],
                $r['com_bhxh'],
                $r['com_bhyt'],
                $r['com_bhtn'],
                $r['com_bhtnld'],
                $r['com_total'],
                $r['grand_total'],
            ]);
        }

        // Dòng tổng cộng
        $t = $data['totals'];
        fputcsv($output, [
            'TỔNG CỘNG', '', '', '', '',
            $t['total_salary'],
            $t['emp_bhxh'], $t['emp_bhyt'], $t['emp_bhtn'], $t['emp_total'],
            $t['com_bhxh'], $t['com_bhyt'], $t['com_bhtn'], $t['com_bhtnld'], $t['com_total'],
            $t['grand_total']
        ]);

        fclose($output);
        exit;
    }
}
