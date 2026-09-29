<?php
/**
 * ============================================================
 *  POSUNG HRIS – View: loan/create.php
 * ============================================================
 *  Lập Đề xuất Tạm ứng Lương & Khoản vay Nhân viên
 * ============================================================
 */
?>
<div class="content-wrapper">
    <!-- TIÊU ĐỀ & ĐIỀU HƯỚNG -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1 fw-bold text-dark"><i class="fas fa-file-invoice-dollar text-primary me-2"></i>Lập Đề xuất Tạm ứng / Vay vốn</h2>
            <p class="text-muted mb-0">Hồ sơ sẽ được chuyển đến C&B và Ban Giám đốc để xem xét giải ngân</p>
        </div>
        <div>
            <a href="<?= BASE_URL ?>/loan" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Quay lại danh sách
            </a>
        </div>
    </div>

    <form action="<?= BASE_URL ?>/loan/store" method="POST" id="loanForm">
        <input type="hidden" name="_csrf_token" value="<?= Session::generateCsrfToken() ?>">

        <div class="row g-4">
            <!-- CỘT TRÁI: FORM NHẬP LIỆU -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
                    <h5 class="fw-bold mb-3 text-dark border-bottom pb-2">
                        <i class="fas fa-user-tag text-primary me-2"></i>1. Thông tin Người đề xuất & Loại vay
                    </h5>

                    <div class="row g-3 mb-3">
                        <div class="col-md-7">
                            <label class="form-label fw-semibold">Nhân viên đề xuất <span class="text-danger">*</span></label>
                            <select name="employee_id" id="employeeSelect" class="form-select" required onchange="updateEmployeeInfo()">
                                <option value="">-- Chọn nhân viên --</option>
                                <?php foreach ($employees as $emp): ?>
                                    <option value="<?= $emp->id ?>" 
                                            data-code="<?= h($emp->emp_code) ?>"
                                            data-dept="<?= h($emp->dept_name ?? 'N/A') ?>"
                                            data-salary="<?= (float)($emp->base_salary ?? 0) ?>"
                                            <?= $selectedEmpId == $emp->id ? 'selected' : '' ?>>
                                        <?= h($emp->full_name) ?> (<?= h($emp->emp_code) ?> - <?= h($emp->dept_name ?? 'N/A') ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-5">
                            <label class="form-label fw-semibold">Loại khoản vay / Tạm ứng <span class="text-danger">*</span></label>
                            <select name="loan_type_id" id="loanTypeSelect" class="form-select" required onchange="handleTypeChange()">
                                <option value="">-- Chọn loại khoản vay --</option>
                                <?php foreach ($types as $type): ?>
                                    <option value="<?= $type->id ?>"
                                            data-rate="<?= (float)$type->interest_rate ?>"
                                            data-max-amount="<?= (float)($type->max_amount ?? 0) ?>"
                                            data-max-term="<?= (int)($type->max_term_months ?? 12) ?>"
                                            data-desc="<?= h($type->description ?? '') ?>">
                                        <?= h($type->name) ?> (Lãi: <?= $type->interest_rate ?>%)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- THÔNG BÁO VỀ HẠN MỨC CỦA LOẠI VAY -->
                    <div id="typeInfoAlert" class="alert alert-info border-0 rounded-3 d-none mb-3 py-2 px-3 small">
                        <i class="fas fa-circle-info me-1"></i> <span id="typeInfoText"></span>
                    </div>

                    <h5 class="fw-bold mb-3 text-dark border-bottom pb-2 mt-4">
                        <i class="fas fa-calculator text-primary me-2"></i>2. Thông số Khoản vay & Phương thức
                    </h5>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Số tiền đề xuất (VNĐ) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" name="amount" id="amountInput" class="form-control form-control-lg fw-bold text-primary" placeholder="Ví dụ: 10,000,000" required oninput="formatCurrency(this); recalculateEmi();">
                                <span class="input-group-text bg-light fw-bold">₫</span>
                            </div>
                            <small class="text-muted" id="amountHelp">Nhập số tiền bằng số, hệ thống sẽ tự định dạng.</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Thời hạn trả góp (Tháng) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" name="term_months" id="termInput" class="form-control form-control-lg" value="3" min="1" max="60" required oninput="recalculateEmi();">
                                <span class="input-group-text bg-light">tháng</span>
                            </div>
                            <small class="text-muted" id="termHelp">Kỳ hạn khấu trừ phân bổ hàng tháng.</small>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Ngày làm đơn đề xuất <span class="text-danger">*</span></label>
                            <input type="date" name="applied_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Hình thức nhận tiền</label>
                            <select name="disbursement_method" class="form-select">
                                <option value="Bank Transfer">Chuyển khoản Ngân hàng (Khuyên dùng)</option>
                                <option value="Cash">Chi tiền mặt tại Thủ quỹ</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Lý do đề xuất vay / tạm ứng <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control" rows="3" placeholder="Ghi rõ lý do và mục đích sử dụng (vd: Tạm ứng việc gia đình khẩn cấp, mua thiết bị phục vụ công trình...)" required></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Ghi chú bổ sung (nếu có)</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Cam kết hoàn trả, điều khoản đặc thù..."></textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="<?= BASE_URL ?>/loan" class="btn btn-light border px-4">Hủy bỏ</a>
                    <button type="submit" class="btn btn-primary px-4 fw-semibold">
                        <i class="fas fa-paper-plane me-1"></i> Gửi Đề xuất Phê duyệt
                    </button>
                </div>
            </div>

            <!-- CỘT PHẢI: BẢNG TÍNH EMI TRỰC TIẾP (INSTANT EMI CALCULATOR) -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-3 bg-white p-4 sticky-top" style="top: 20px;">
                    <h5 class="fw-bold mb-3 text-dark border-bottom pb-2">
                        <i class="fas fa-chart-pie text-primary me-2"></i>Dự toán Hoàn trả (EMI)
                    </h5>

                    <!-- CARD TỔNG TIỀN TRẢ HÀNG THÁNG -->
                    <div class="rounded-3 p-3 bg-primary bg-opacity-10 text-center mb-4">
                        <span class="text-muted small text-uppercase fw-semibold">Trừ lương hàng tháng (Ước tính)</span>
                        <h2 class="fw-bold text-primary mb-0 mt-1" id="boxEmiDisplay">0 ₫</h2>
                        <small class="text-muted" id="boxTermDisplay">trong 0 tháng</small>
                    </div>

                    <!-- DANH SÁCH CHI TIẾT TÍNH TOÁN -->
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Tiền gốc ban đầu:</span>
                        <strong class="text-dark" id="boxPrincipal">0 ₫</strong>
                    </div>

                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Lãi suất áp dụng:</span>
                        <strong class="text-dark" id="boxRate">0% / năm</strong>
                    </div>

                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Kỳ hạn trả góp:</span>
                        <strong class="text-dark" id="boxMonths">0 tháng</strong>
                    </div>

                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Tổng tiền lãi:</span>
                        <strong class="text-danger" id="boxInterest">0 ₫</strong>
                    </div>

                    <div class="d-flex justify-content-between py-2 border-bottom bg-light px-2 rounded mt-2">
                        <span class="fw-bold text-dark">Tổng tiền phải trả:</span>
                        <strong class="fw-bold text-primary" id="boxTotal">0 ₫</strong>
                    </div>

                    <!-- THÔNG TIN LƯƠNG NHÂN VIÊN ĐƯỢC CHỌN -->
                    <div class="mt-4 pt-3 border-top" id="empSalaryBox">
                        <h6 class="fw-bold text-dark mb-2"><i class="fas fa-wallet text-secondary me-1"></i>Đối chiếu Thu nhập</h6>
                        <div class="d-flex justify-content-between small text-muted mb-1">
                            <span>Lương cơ bản:</span>
                            <strong class="text-dark" id="boxEmpSalary">Chưa chọn NV</strong>
                        </div>
                        <div class="d-flex justify-content-between small text-muted mb-1">
                            <span>Tỷ lệ trừ lương/tháng:</span>
                            <strong id="boxDeductionRatio" class="text-dark">---</strong>
                        </div>
                        <div class="progress mt-2" style="height: 6px;">
                            <div class="progress-bar bg-success" id="boxRatioBar" style="width: 0%"></div>
                        </div>
                        <small class="text-muted d-block mt-1" style="font-size: 11px;">* Khuyến nghị: Tổng trừ nợ không vượt quá 40% lương tháng để đảm bảo đời sống người LĐ.</small>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
let currentEmpSalary = 0;

function formatCurrency(input) {
    let value = input.value.replace(/\D/g, '');
    if (value === '') {
        input.value = '';
        return;
    }
    input.value = new Intl.NumberFormat('vi-VN').format(value);
}

function updateEmployeeInfo() {
    const sel = document.getElementById('employeeSelect');
    const opt = sel.options[sel.selectedIndex];
    if (!opt || !opt.value) {
        currentEmpSalary = 0;
        document.getElementById('boxEmpSalary').innerText = 'Chưa chọn NV';
        document.getElementById('boxDeductionRatio').innerText = '---';
        document.getElementById('boxRatioBar').style.width = '0%';
        return;
    }

    currentEmpSalary = parseFloat(opt.getAttribute('data-salary')) || 0;
    document.getElementById('boxEmpSalary').innerText = new Intl.NumberFormat('vi-VN').format(currentEmpSalary) + ' ₫';
    recalculateEmi();
}

function handleTypeChange() {
    const sel = document.getElementById('loanTypeSelect');
    const opt = sel.options[sel.selectedIndex];
    const alertBox = document.getElementById('typeInfoAlert');
    const alertText = document.getElementById('typeInfoText');

    if (!opt || !opt.value) {
        alertBox.classList.add('d-none');
        recalculateEmi();
        return;
    }

    const rate = parseFloat(opt.getAttribute('data-rate')) || 0;
    const maxAmount = parseFloat(opt.getAttribute('data-max-amount')) || 0;
    const maxTerm = parseInt(opt.getAttribute('data-max-term')) || 12;
    const desc = opt.getAttribute('data-desc') || '';

    let infoParts = [];
    if (maxAmount > 0) {
        infoParts.push(`Hạn mức tối đa: <strong>${new Intl.NumberFormat('vi-VN').format(maxAmount)} ₫</strong>`);
    } else {
        infoParts.push(`Hạn mức: <em>Không giới hạn</em>`);
    }
    infoParts.push(`Kỳ hạn tối đa: <strong>${maxTerm} tháng</strong>`);
    infoParts.push(`Lãi suất: <strong>${rate}%/năm</strong>`);

    alertText.innerHTML = infoParts.join(' &bull; ') + (desc ? `<br><small class="text-muted">${desc}</small>` : '');
    alertBox.classList.remove('d-none');

    // Thiết lập giới hạn max cho input kỳ hạn
    const termInput = document.getElementById('termInput');
    termInput.max = maxTerm;
    if (parseInt(termInput.value) > maxTerm) {
        termInput.value = maxTerm;
    }

    recalculateEmi();
}

function recalculateEmi() {
    const amountStr = document.getElementById('amountInput').value.replace(/\D/g, '');
    const amount = parseFloat(amountStr) || 0;
    const term = parseInt(document.getElementById('termInput').value) || 1;

    const typeSel = document.getElementById('loanTypeSelect');
    const typeOpt = typeSel ? typeSel.options[typeSel.selectedIndex] : null;
    const rate = (typeOpt && typeOpt.value) ? (parseFloat(typeOpt.getAttribute('data-rate')) || 0) : 0;

    let totalInterest = 0;
    if (rate > 0) {
        totalInterest = Math.round(amount * (rate / 100) * (term / 12));
    }
    const totalRepay = amount + totalInterest;
    const emi = term > 0 ? Math.round(totalRepay / term) : totalRepay;

    document.getElementById('boxPrincipal').innerText = new Intl.NumberFormat('vi-VN').format(amount) + ' ₫';
    document.getElementById('boxRate').innerText = rate + '% / năm';
    document.getElementById('boxMonths').innerText = term + ' tháng';
    document.getElementById('boxInterest').innerText = new Intl.NumberFormat('vi-VN').format(totalInterest) + ' ₫';
    document.getElementById('boxTotal').innerText = new Intl.NumberFormat('vi-VN').format(totalRepay) + ' ₫';
    document.getElementById('boxEmiDisplay').innerText = new Intl.NumberFormat('vi-VN').format(emi) + ' ₫';
    document.getElementById('boxTermDisplay').innerText = 'trong ' + term + ' tháng';

    // Tính tỷ lệ trừ lương
    if (currentEmpSalary > 0 && emi > 0) {
        const ratio = Math.round((emi / currentEmpSalary) * 100);
        const ratioBadge = document.getElementById('boxDeductionRatio');
        const ratioBar = document.getElementById('boxRatioBar');

        ratioBadge.innerText = ratio + '% thu nhập';
        ratioBar.style.width = Math.min(100, ratio) + '%';

        if (ratio > 40) {
            ratioBadge.className = 'text-danger fw-bold';
            ratioBar.className = 'progress-bar bg-danger';
        } else if (ratio > 25) {
            ratioBadge.className = 'text-warning fw-bold';
            ratioBar.className = 'progress-bar bg-warning';
        } else {
            ratioBadge.className = 'text-success fw-bold';
            ratioBar.className = 'progress-bar bg-success';
        }
    } else {
        document.getElementById('boxDeductionRatio').innerText = '---';
        document.getElementById('boxRatioBar').style.width = '0%';
    }
}

// Khởi chạy khi tải trang nếu đã có nhân viên hoặc loại vay chọn sẵn
document.addEventListener('DOMContentLoaded', function() {
    updateEmployeeInfo();
    handleTypeChange();
});
</script>
