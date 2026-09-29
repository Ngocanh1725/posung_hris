<?php
require_once APP_ROOT . '/views/ess/layout/header.php';

// Helper đọc số tiền thành chữ tiếng Việt
function readMoneyVietnamese($number) {
    $hyphen = ' ';
    $conjunction = ' ';
    $separator = ' ';
    $negative = 'âm ';
    $decimal = ' phẩy ';
    $dictionary = [
        0 => 'không', 1 => 'một', 2 => 'hai', 3 => 'ba', 4 => 'bốn', 5 => 'năm',
        6 => 'sáu', 7 => 'bảy', 8 => 'tám', 9 => 'chín', 10 => 'mười',
        100 => 'trăm', 1000 => 'nghìn', 1000000 => 'triệu', 1000000000 => 'tỷ'
    ];
    if (!is_numeric($number)) return '';
    $number = round($number);
    if ($number == 0) return 'Không đồng';
    
    $units = ['', 'nghìn', 'triệu', 'tỷ'];
    $i = 0;
    $res = '';
    while ($number > 0) {
        $chunk = $number % 1000;
        if ($chunk > 0) {
            $chunkStr = '';
            $h = floor($chunk / 100);
            $t = floor(($chunk % 100) / 10);
            $u = $chunk % 10;
            if ($h > 0 || $number >= 1000) {
                $chunkStr .= $dictionary[$h] . ' trăm ';
            }
            if ($t > 1) {
                $chunkStr .= $dictionary[$t] . ' mươi ';
                if ($u == 1) $chunkStr .= 'mốt ';
                elseif ($u == 5) $chunkStr .= 'lăm ';
                elseif ($u > 0) $chunkStr .= $dictionary[$u] . ' ';
            } elseif ($t == 1) {
                $chunkStr .= 'mười ';
                if ($u == 5) $chunkStr .= 'lăm ';
                elseif ($u > 0) $chunkStr .= $dictionary[$u] . ' ';
            } else {
                if ($u > 0) {
                    if ($h > 0 || $number >= 1000) $chunkStr .= 'lẻ ';
                    $chunkStr .= $dictionary[$u] . ' ';
                }
            }
            $chunkStr .= $units[$i] . ' ';
            $res = $chunkStr . $res;
        }
        $number = floor($number / 1000);
        $i++;
    }
    $res = trim($res) . ' đồng';
    return mb_strtoupper(mb_substr($res, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($res, 1, null, 'UTF-8');
}
?>

<div class="container-xl">
    
    <!-- Thanh điều khiển chọn kỳ lương & In ấn -->
    <div class="card-custom p-3 mb-4 no-print">
        <div class="row align-items-center g-3">
            <div class="col-md-6">
                <form action="<?= BASE_URL ?>/ess/payslip" method="GET" class="d-flex align-items-center gap-2">
                    <label class="fw-bold text-dark small text-nowrap mb-0">
                        <i class="fa-solid fa-calendar-days text-primary me-1"></i> Chọn kỳ lương:
                    </label>
                    <select name="period" class="form-select form-select-sm" style="max-width: 200px;" onchange="this.form.submit()">
                        <?php if (!empty($availablePeriods)): ?>
                            <?php foreach ($availablePeriods as $p): ?>
                                <?php $val = $p['month'] . '-' . $p['year']; ?>
                                <option value="<?= $val ?>" <?= ($selectedMonth == $p['month'] && $selectedYear == $p['year']) ? 'selected' : '' ?>>
                                    Tháng <?= $p['month'] ?> / <?= $p['year'] ?>
                                </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="">Tháng <?= $selectedMonth ?> / <?= $selectedYear ?></option>
                        <?php endif; ?>
                    </select>
                </form>
            </div>
            <div class="col-md-6 text-md-end">
                <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 me-2" onclick="window.print()">
                    <i class="fa-solid fa-print me-1"></i> In phiếu lương
                </button>
                <a href="<?= BASE_URL ?>/ess/dashboard" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                    <i class="fa-solid fa-arrow-left me-1"></i> Quay lại
                </a>
            </div>
        </div>
    </div>

    <?php if (!empty($payslip)): ?>
        <!-- ══════════════════════════════════════════════════════
             PHIẾU LƯƠNG CHUẨN IN & XEM BẢO MẬT
             ══════════════════════════════════════════════════════ -->
        <div class="card-custom p-4 p-md-5 bg-white shadow-sm" id="printablePayslip" style="max-width: 900px; margin: 0 auto; border: 1px solid #cbd5e1;">
            
            <!-- Header Phiếu Lương -->
            <div class="d-flex justify-content-between align-items-start border-bottom pb-4 mb-4">
                <div>
                    <h5 class="fw-bold text-dark mb-1">CÔNG TY CỔ PHẦN KỸ THUẬT & XÂY DỰNG POSUNG</h5>
                    <p class="text-muted small mb-0">POSUNG CONSTRUCTION E&C &bull; Hệ thống quản trị nhân sự HRIS</p>
                    <small class="text-muted" style="font-size: 0.75rem;">Địa chỉ: KCN Yên Phong, Bắc Ninh &bull; Hotline: 024.3795.8888</small>
                </div>
                <div class="text-end">
                    <span class="badge bg-primary px-3 py-2 fs-6 rounded-pill">
                        KỲ LƯƠNG: T<?= $selectedMonth ?>/<?= $selectedYear ?>
                    </span>
                    <div class="text-muted small mt-1">Trạng thái: <strong><?= htmlspecialchars($payslip['status'] ?? 'Approved') ?></strong></div>
                </div>
            </div>

            <div class="text-center my-3">
                <h3 class="fw-bold text-dark mb-1">PHIẾU LƯƠNG CHI TIẾT NHÂN VIÊN</h3>
                <p class="text-muted small">Kỳ chi trả: Tháng <?= $selectedMonth ?> năm <?= $selectedYear ?></p>
            </div>

            <!-- Thông tin nhân viên trong phiếu lương -->
            <div class="bg-light p-3 rounded-3 mb-4 border small">
                <div class="row g-2">
                    <div class="col-sm-6">
                        <span class="text-muted">Họ và tên:</span>
                        <strong class="text-dark fs-6 ms-1"><?= htmlspecialchars($employee['full_name']) ?></strong>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted">Mã nhân viên:</span>
                        <strong class="text-primary font-monospace ms-1"><?= htmlspecialchars($employee['emp_code']) ?></strong>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted">Phòng ban / Dự án:</span>
                        <span class="fw-semibold text-dark ms-1"><?= htmlspecialchars($employee['dept_name'] ?? '—') ?> / <?= htmlspecialchars($employee['project_name'] ?? 'Văn phòng') ?></span>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted">Chức danh công việc:</span>
                        <span class="fw-semibold text-dark ms-1"><?= htmlspecialchars($employee['pos_title'] ?? '—') ?></span>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted">Số ngày công chuẩn:</span>
                        <strong class="text-dark ms-1"><?= number_format($payslip['standard_days'], 1) ?> ngày</strong>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted">Số ngày công thực tế:</span>
                        <strong class="text-success ms-1"><?= number_format($payslip['actual_days'], 1) ?> ngày</strong>
                    </div>
                </div>
            </div>

            <!-- Bảng Chi tiết Lương: Thu nhập vs Khấu trừ -->
            <div class="row g-4 mb-4">
                <!-- Cột Trái: Các Khoản Thu Nhập -->
                <div class="col-md-6">
                    <div class="border rounded-3 h-100 overflow-hidden">
                        <div class="bg-primary text-white p-2 px-3 fw-bold small">
                            <i class="fa-solid fa-plus-circle me-1"></i> I. CÁC KHOẢN THU NHẬP
                        </div>
                        <table class="table table-sm table-striped mb-0 small">
                            <tbody>
                                <tr>
                                    <td class="ps-3 py-2 text-muted">Lương cơ bản hợp đồng:</td>
                                    <td class="pe-3 py-2 text-end fw-semibold"><?= number_format($payslip['base_salary'], 0, ',', '.') ?> đ</td>
                                </tr>
                                <tr>
                                    <td class="ps-3 py-2 text-muted">Lương công nhật thực tế:</td>
                                    <td class="pe-3 py-2 text-end fw-semibold"><?= number_format($payslip['regular_pay'] > 0 ? $payslip['regular_pay'] : $payslip['base_salary'], 0, ',', '.') ?> đ</td>
                                </tr>
                                <tr>
                                    <td class="ps-3 py-2 text-muted">Lương làm thêm giờ (OT):</td>
                                    <td class="pe-3 py-2 text-end fw-semibold"><?= number_format($payslip['ot_pay'], 0, ',', '.') ?> đ</td>
                                </tr>
                                <tr>
                                    <td class="ps-3 py-2 text-muted">Phụ cấp ăn trưa:</td>
                                    <td class="pe-3 py-2 text-end fw-semibold"><?= number_format($payslip['allowance_meal'], 0, ',', '.') ?> đ</td>
                                </tr>
                                <tr>
                                    <td class="ps-3 py-2 text-muted">Phụ cấp độc hại / Cleanroom:</td>
                                    <td class="pe-3 py-2 text-end fw-semibold"><?= number_format($payslip['allowance_hazard'], 0, ',', '.') ?> đ</td>
                                </tr>
                                <tr>
                                    <td class="ps-3 py-2 text-muted">Phụ cấp đi lại / Xăng xe:</td>
                                    <td class="pe-3 py-2 text-end fw-semibold"><?= number_format($payslip['allowance_travel'], 0, ',', '.') ?> đ</td>
                                </tr>
                                <tr>
                                    <td class="ps-3 py-2 text-muted">Phụ cấp điện thoại:</td>
                                    <td class="pe-3 py-2 text-end fw-semibold"><?= number_format($payslip['allowance_phone'], 0, ',', '.') ?> đ</td>
                                </tr>
                                <tr>
                                    <td class="ps-3 py-2 text-muted">Tiền thưởng / Hỗ trợ khác:</td>
                                    <td class="pe-3 py-2 text-end fw-semibold"><?= number_format((float)$payslip['bonus'] + (float)$payslip['other_support'], 0, ',', '.') ?> đ</td>
                                </tr>
                            </tbody>
                            <tfoot class="table-light border-top">
                                <tr>
                                    <th class="ps-3 py-2 text-primary">TỔNG THU NHẬP (A):</th>
                                    <th class="pe-3 py-2 text-end text-primary fs-6">
                                        <?php 
                                            $totalIncome = (float)($payslip['regular_pay'] > 0 ? $payslip['regular_pay'] : $payslip['base_salary']) + (float)$payslip['ot_pay'] + (float)$payslip['allowances_total'] + (float)$payslip['bonus'] + (float)$payslip['other_support'];
                                            echo number_format($totalIncome, 0, ',', '.');
                                        ?> đ
                                    </th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- Cột Phải: Các Khoản Giảm Trừ -->
                <div class="col-md-6">
                    <div class="border rounded-3 h-100 overflow-hidden">
                        <div class="bg-danger text-white p-2 px-3 fw-bold small">
                            <i class="fa-solid fa-minus-circle me-1"></i> II. CÁC KHOẢN KHẤU TRỪ
                        </div>
                        <table class="table table-sm table-striped mb-0 small">
                            <tbody>
                                <tr>
                                    <td class="ps-3 py-2 text-muted">Bảo hiểm xã hội (BHXH 8%):</td>
                                    <td class="pe-3 py-2 text-end fw-semibold text-danger">
                                        <?= number_format($payslip['insurance_social'] > 0 ? $payslip['insurance_social'] : ($payslip['base_salary'] * 0.08), 0, ',', '.') ?> đ
                                    </td>
                                </tr>
                                <tr>
                                    <td class="ps-3 py-2 text-muted">Bảo hiểm y tế (BHYT 1.5%):</td>
                                    <td class="pe-3 py-2 text-end fw-semibold text-danger">
                                        <?= number_format($payslip['insurance_health'] > 0 ? $payslip['insurance_health'] : ($payslip['base_salary'] * 0.015), 0, ',', '.') ?> đ
                                    </td>
                                </tr>
                                <tr>
                                    <td class="ps-3 py-2 text-muted">Bảo hiểm thất nghiệp (BHTN 1%):</td>
                                    <td class="pe-3 py-2 text-end fw-semibold text-danger">
                                        <?= number_format($payslip['insurance_unemployment'] > 0 ? $payslip['insurance_unemployment'] : ($payslip['base_salary'] * 0.01), 0, ',', '.') ?> đ
                                    </td>
                                </tr>
                                <tr>
                                    <td class="ps-3 py-2 text-muted">Thuế thu nhập cá nhân (TNCN):</td>
                                    <td class="pe-3 py-2 text-end fw-semibold text-danger">
                                        <?= number_format($payslip['tax_pit'], 0, ',', '.') ?> đ
                                    </td>
                                </tr>
                                <tr>
                                    <td class="ps-3 py-2 text-muted">Đoàn phí công đoàn:</td>
                                    <td class="pe-3 py-2 text-end fw-semibold text-danger">
                                        <?= number_format($payslip['union_fee'], 0, ',', '.') ?> đ
                                    </td>
                                </tr>
                                <tr>
                                    <td class="ps-3 py-2 text-muted">Tạm ứng trong kỳ:</td>
                                    <td class="pe-3 py-2 text-end fw-semibold text-danger">
                                        <?= number_format($payslip['advance_payment'], 0, ',', '.') ?> đ
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot class="table-light border-top">
                                <tr>
                                    <th class="ps-3 py-2 text-danger">TỔNG KHẤU TRỪ (B):</th>
                                    <th class="pe-3 py-2 text-end text-danger fs-6">
                                        <?= number_format($payslip['deductions_total'], 0, ',', '.') ?> đ
                                    </th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Tổng Thực Lĩnh (Net Salary) -->
            <div class="card p-3 mb-4 text-center border-2 border-success bg-success-subtle">
                <div class="small text-uppercase fw-bold text-success mb-1">
                    <i class="fa-solid fa-wallet me-1"></i> SỐ TIỀN THỰC LĨNH CHUYỂN KHOẢN (NET = A - B)
                </div>
                <div class="display-6 fw-bold text-success mb-1">
                    <?= number_format($payslip['net_salary'], 0, ',', '.') ?> VNĐ
                </div>
                <div class="text-dark small fst-italic">
                    (Bằng chữ: <strong><?= readMoneyVietnamese($payslip['net_salary']) ?></strong>)
                </div>
            </div>

            <!-- Chữ ký điện tử xác nhận & Ghi chú -->
            <div class="row pt-4 text-center small text-muted">
                <div class="col-4">
                    <div class="fw-bold text-dark mb-5">NGƯỜI LẬP BIỂU</div>
                    <div>Phòng C&B / Tiền lương</div>
                </div>
                <div class="col-4">
                    <div class="fw-bold text-dark mb-5">KẾ TOÁN TRƯỞNG</div>
                    <div>Phòng Tài chính Kế toán</div>
                </div>
                <div class="col-4">
                    <div class="fw-bold text-dark mb-5">GIÁM ĐỐC ĐIỀU HÀNH</div>
                    <div class="text-success fw-semibold"><i class="fa-solid fa-stamp me-1"></i> [Đã duyệt điện tử]</div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-top text-center text-muted small" style="font-size: 0.72rem;">
                Phiếu lương này là chứng từ bảo mật nội bộ của Công ty Cổ phần Posung E&C. Người lao động có trách nhiệm bảo mật thông tin thu nhập theo Quy chế Công ty.
            </div>

        </div>
    <?php else: ?>
        <div class="card-custom p-5 text-center bg-white">
            <i class="fa-solid fa-receipt fa-3x text-muted opacity-50 mb-3"></i>
            <h5 class="fw-bold text-dark">Chưa có phiếu lương cho tháng <?= $selectedMonth ?>/<?= $selectedYear ?></h5>
            <p class="text-muted small">Phiếu lương của kỳ này chưa được tính toán hoặc đang trong quá trình phê duyệt.</p>
        </div>
    <?php endif; ?>

</div>

<?php require_once APP_ROOT . '/views/ess/layout/footer.php'; ?>
