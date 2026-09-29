<?php
/**
 * ============================================================
 *  POSUNG HRIS – View: expense/create_claim.php
 * ============================================================
 *  Lập Bảng Thanh Quyết toán Chi phí (Expense Claim Form)
 * ============================================================
 */
?>
<div class="content-wrapper">
    <!-- TIÊU ĐỀ & ĐIỀU HƯỚNG -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1 fw-bold text-dark"><i class="fas fa-file-invoice-dollar text-primary me-2"></i>Lập Bảng Quyết toán Chi phí</h2>
            <p class="text-muted mb-0">Khai báo các khoản chi tiêu thực tế, đính kèm hóa đơn chứng từ để thanh quyết toán</p>
        </div>
        <div>
            <a href="<?= BASE_URL ?>/expense?tab=claims" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Quay lại
            </a>
        </div>
    </div>

    <form action="<?= BASE_URL ?>/expense/storeClaim" method="POST" enctype="multipart/form-data" id="claimForm">
        <input type="hidden" name="_csrf_token" value="<?= Session::generateCsrfToken() ?>">

        <div class="row g-4">
            <!-- CỘT TRÁI: THÔNG TIN CHUNG & BẢNG KHOẢN CHI ĐỘNG -->
            <div class="col-lg-8">
                <!-- THÔNG TIN CHUNG -->
                <div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
                    <h5 class="fw-bold mb-3 text-dark border-bottom pb-2">
                        <i class="fas fa-file-lines text-primary me-2"></i>1. Thông tin Chung Hồ sơ
                    </h5>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Người đề nghị quyết toán <span class="text-danger">*</span></label>
                            <select name="employee_id" id="empSelect" class="form-select" required>
                                <option value="">-- Chọn nhân viên --</option>
                                <?php foreach ($employees as $emp): ?>
                                    <option value="<?= $emp->id ?>" <?= ($travel && $travel->employee_id == $emp->id) ? 'selected' : '' ?>>
                                        <?= h($emp->full_name) ?> (<?= h($emp->emp_code) ?> - <?= h($emp->dept_name ?? 'N/A') ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Liên kết Chuyến công tác</label>
                            <select name="travel_request_id" id="travelSelect" class="form-select" onchange="handleTravelSelectChange()">
                                <option value="">-- Không liên kết công tác (Chi phí độc lập) --</option>
                                <?php foreach ($approvedTravels as $tr): ?>
                                    <option value="<?= $tr->id ?>" 
                                            data-empid="<?= $tr->employee_id ?>"
                                            data-prjid="<?= $tr->project_id ?>"
                                            data-advance="<?= (float)$tr->advance_amount ?>"
                                            data-budget="<?= (float)$tr->estimated_budget ?>"
                                            data-location="<?= h($tr->to_location) ?>"
                                            <?= ($travel && $travel->id == $tr->id) ? 'selected' : '' ?>>
                                        <?= h($tr->request_code) ?> - Đi <?= h($tr->to_location) ?> (Tạm ứng: <?= number_format((float)$tr->advance_amount, 0, ',', '.') ?> ₫)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-7">
                            <label class="form-label fw-semibold">Tiêu đề bảng quyết toán <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="titleInput" class="form-control" placeholder="Ví dụ: Quyết toán công tác dự án Samsung SEVM..." value="<?= $travel ? 'Quyết toán công tác ' . h($travel->to_location) : '' ?>" required>
                        </div>

                        <div class="col-md-5">
                            <label class="form-label fw-semibold">Danh mục chi phí</label>
                            <select name="category" class="form-select">
                                <option value="Travel" <?= ($travel ? 'selected' : '') ?>>Chi phí Công tác (Travel)</option>
                                <option value="Project">Chi phí Công trường Dự án (Project)</option>
                                <option value="Office">Chi phí Văn phòng (Office)</option>
                                <option value="Other">Chi phí Khác (Other)</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Dự án hạch toán chi phí</label>
                            <select name="project_id" id="prjSelect" class="form-select">
                                <option value="">-- Chi phí quản lý chung công ty --</option>
                                <?php foreach ($projects as $prj): ?>
                                    <option value="<?= $prj->id ?>" <?= ($travel && $travel->project_id == $prj->id) ? 'selected' : '' ?>>
                                        <?= h($prj->project_name) ?> (<?= h($prj->location ?? 'N/A') ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Ngày lập bảng kê</label>
                            <input type="date" name="submitted_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Ghi chú & Diễn giải thêm</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Ghi chú chi tiết cho kế toán..."></textarea>
                    </div>
                </div>

                <!-- BẢNG CÁC KHOẢN CHI TIẾT (DYNAMIC ITEMS) -->
                <div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                        <h5 class="fw-bold mb-0 text-dark">
                            <i class="fas fa-list-check text-primary me-2"></i>2. Chi tiết các Khoản chi & Hóa đơn
                        </h5>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="addExpenseRow()">
                            <i class="fas fa-plus me-1"></i> Thêm khoản chi
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered align-middle" id="itemsTable">
                            <thead class="table-light small text-muted text-uppercase text-center">
                                <tr>
                                    <th style="width: 140px;">Loại chi</th>
                                    <th>Nội dung chi / Hóa đơn số <span class="text-danger">*</span></th>
                                    <th style="width: 130px;">Ngày chi</th>
                                    <th style="width: 150px;">Số tiền (VNĐ) <span class="text-danger">*</span></th>
                                    <th style="width: 140px;">Biên lai / Bill</th>
                                    <th style="width: 40px;"></th>
                                </tr>
                            </thead>
                            <tbody id="itemsBody">
                                <!-- DÒNG MẪU 1 -->
                                <tr class="expense-row">
                                    <td>
                                        <select name="item_category[]" class="form-select form-select-sm">
                                            <option value="Transport">Vé xe / Tàu / Bay</option>
                                            <option value="Hotel">Tiền phòng KS</option>
                                            <option value="Meal">Ăn uống / Tiếp khách</option>
                                            <option value="Fuel">Xăng dầu xe</option>
                                            <option value="Material">Vật tư công trường</option>
                                            <option value="Other">Chi phí khác</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" name="item_description[]" class="form-control form-control-sm" placeholder="VD: Vé máy bay khứ hồi Hà Nội - Sài Gòn..." required>
                                    </td>
                                    <td>
                                        <input type="date" name="item_date[]" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>" required>
                                    </td>
                                    <td>
                                        <input type="text" name="item_amount[]" class="form-control form-control-sm text-end fw-bold text-dark amount-input" placeholder="0" oninput="formatCurrency(this); recalculateTotal();" required>
                                    </td>
                                    <td>
                                        <input type="file" name="item_receipt[]" class="form-control form-control-sm" accept="image/*,.pdf" title="Đính kèm ảnh biên lai">
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="removeRow(this)" title="Xóa dòng">
                                            <i class="fas fa-trash-can"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="<?= BASE_URL ?>/expense?tab=claims" class="btn btn-light border px-4">Hủy bỏ</a>
                    <button type="submit" class="btn btn-primary px-4 fw-semibold">
                        <i class="fas fa-paper-plane me-1"></i> Gửi Quyết toán Phê duyệt
                    </button>
                </div>
            </div>

            <!-- CỘT PHẢI: TỔNG HỢP THANH TOÁN (PAYMENT SUMMARY) -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-3 p-4 bg-white sticky-top" style="top: 20px;">
                    <h5 class="fw-bold mb-3 text-dark border-bottom pb-2">
                        <i class="fas fa-calculator text-primary me-2"></i>Tổng hợp Quyết toán
                    </h5>

                    <!-- TỔNG TIỀN CHI PHÍ -->
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Tổng chi phí thực tế:</span>
                        <strong class="text-dark fs-5" id="boxTotalAmount">0 ₫</strong>
                    </div>

                    <!-- KHẤU TRỪ TẠM ỨNG -->
                    <div class="py-2 border-bottom">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-muted">Đã nhận Tạm ứng:</span>
                            <strong class="text-primary" id="boxAdvanceDisplay">0 ₫</strong>
                        </div>
                        <div class="input-group input-group-sm mt-1">
                            <span class="input-group-text bg-light">Khấu trừ:</span>
                            <input type="text" name="advance_deducted" id="advanceInput" class="form-control text-end fw-bold text-primary" value="<?= $travel ? number_format((float)$travel->advance_amount, 0, ',', '.') : '0' ?>" oninput="formatCurrency(this); recalculateTotal();">
                            <span class="input-group-text bg-light">₫</span>
                        </div>
                        <small class="text-muted" style="font-size: 11px;">Số tiền tạm ứng đã nhận của đợt công tác này.</small>
                    </div>

                    <!-- THỰC NHẬN / NỘP LẠI -->
                    <div class="rounded-3 p-3 bg-success bg-opacity-10 text-center my-3" id="netBox">
                        <span class="text-muted small text-uppercase fw-semibold" id="netLabel">Số tiền Thực nhận (Công ty chi bù)</span>
                        <h2 class="fw-bold text-success mb-0 mt-1" id="boxNetPayable">0 ₫</h2>
                    </div>

                    <!-- ĐỐI CHIẾU VỚI NGÂN SÁCH DỰ KIẾN -->
                    <div id="budgetComparisonBox" class="p-3 bg-light rounded-3 d-none">
                        <div class="d-flex justify-content-between small text-muted mb-1">
                            <span>Ngân sách dự kiến:</span>
                            <strong class="text-dark" id="boxBudgetAmount">0 ₫</strong>
                        </div>
                        <div class="d-flex justify-content-between small text-muted mb-1">
                            <span>Chênh lệch:</span>
                            <strong id="boxVariance">0 ₫</strong>
                        </div>
                        <div class="progress mt-2" style="height: 6px;">
                            <div class="progress-bar bg-primary" id="budgetProgressBar" style="width: 0%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
let currentBudget = <?= $travel ? (float)$travel->estimated_budget : 0 ?>;

function formatCurrency(input) {
    let value = input.value.replace(/\D/g, '');
    if (value === '') {
        input.value = '';
        return;
    }
    input.value = new Intl.NumberFormat('vi-VN').format(value);
}

function addExpenseRow() {
    const tbody = document.getElementById('itemsBody');
    const tr = document.createElement('tr');
    tr.className = 'expense-row';
    tr.innerHTML = `
        <td>
            <select name="item_category[]" class="form-select form-select-sm">
                <option value="Transport">Vé xe / Tàu / Bay</option>
                <option value="Hotel">Tiền phòng KS</option>
                <option value="Meal">Ăn uống / Tiếp khách</option>
                <option value="Fuel">Xăng dầu xe</option>
                <option value="Material">Vật tư công trường</option>
                <option value="Other">Chi phí khác</option>
            </select>
        </td>
        <td>
            <input type="text" name="item_description[]" class="form-control form-control-sm" placeholder="Mô tả khoản chi / Số hóa đơn..." required>
        </td>
        <td>
            <input type="date" name="item_date[]" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>" required>
        </td>
        <td>
            <input type="text" name="item_amount[]" class="form-control form-control-sm text-end fw-bold text-dark amount-input" placeholder="0" oninput="formatCurrency(this); recalculateTotal();" required>
        </td>
        <td>
            <input type="file" name="item_receipt[]" class="form-control form-control-sm" accept="image/*,.pdf" title="Đính kèm ảnh biên lai">
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="removeRow(this)" title="Xóa dòng">
                <i class="fas fa-trash-can"></i>
            </button>
        </td>
    `;
    tbody.appendChild(tr);
}

function removeRow(btn) {
    const tbody = document.getElementById('itemsBody');
    if (tbody.children.length > 1) {
        btn.closest('tr').remove();
        recalculateTotal();
    } else {
        alert('Phải có ít nhất 1 khoản chi tiêu trong bảng quyết toán.');
    }
}

function handleTravelSelectChange() {
    const sel = document.getElementById('travelSelect');
    const opt = sel.options[sel.selectedIndex];

    if (!opt || !opt.value) {
        currentBudget = 0;
        document.getElementById('budgetComparisonBox').classList.add('d-none');
        document.getElementById('advanceInput').value = '0';
        recalculateTotal();
        return;
    }

    const empId = opt.getAttribute('data-empid');
    const prjId = opt.getAttribute('data-prjid');
    const advance = parseFloat(opt.getAttribute('data-advance')) || 0;
    const budget = parseFloat(opt.getAttribute('data-budget')) || 0;
    const location = opt.getAttribute('data-location') || '';

    if (empId) document.getElementById('empSelect').value = empId;
    if (prjId) document.getElementById('prjSelect').value = prjId;
    if (location) document.getElementById('titleInput').value = 'Quyết toán công tác ' + location;

    currentBudget = budget;
    document.getElementById('advanceInput').value = new Intl.NumberFormat('vi-VN').format(advance);
    document.getElementById('boxAdvanceDisplay').innerText = new Intl.NumberFormat('vi-VN').format(advance) + ' ₫';

    if (budget > 0) {
        document.getElementById('budgetComparisonBox').classList.remove('d-none');
        document.getElementById('boxBudgetAmount').innerText = new Intl.NumberFormat('vi-VN').format(budget) + ' ₫';
    } else {
        document.getElementById('budgetComparisonBox').classList.add('d-none');
    }

    recalculateTotal();
}

function recalculateTotal() {
    let total = 0;
    const inputs = document.querySelectorAll('.amount-input');
    inputs.forEach(input => {
        const val = parseFloat(input.value.replace(/\D/g, '')) || 0;
        total += val;
    });

    const advance = parseFloat(document.getElementById('advanceInput').value.replace(/\D/g, '')) || 0;
    const net = total - advance;

    document.getElementById('boxTotalAmount').innerText = new Intl.NumberFormat('vi-VN').format(total) + ' ₫';
    document.getElementById('boxAdvanceDisplay').innerText = new Intl.NumberFormat('vi-VN').format(advance) + ' ₫';

    const netBox = document.getElementById('netBox');
    const netLabel = document.getElementById('netLabel');
    const boxNet = document.getElementById('boxNetPayable');

    if (net >= 0) {
        netLabel.innerText = 'Số tiền Thực nhận (Công ty chi bù)';
        netBox.className = 'rounded-3 p-3 bg-success bg-opacity-10 text-center my-3';
        boxNet.className = 'fw-bold text-success mb-0 mt-1';
        boxNet.innerText = new Intl.NumberFormat('vi-VN').format(net) + ' ₫';
    } else {
        netLabel.innerText = 'Số tiền Hoàn trả lại quỹ (Thừa tạm ứng)';
        netBox.className = 'rounded-3 p-3 bg-danger bg-opacity-10 text-center my-3';
        boxNet.className = 'fw-bold text-danger mb-0 mt-1';
        boxNet.innerText = new Intl.NumberFormat('vi-VN').format(Math.abs(net)) + ' ₫';
    }

    // So sánh với ngân sách dự kiến
    if (currentBudget > 0) {
        const variance = total - currentBudget;
        const vEl = document.getElementById('boxVariance');
        const pBar = document.getElementById('budgetProgressBar');
        const ratio = Math.round((total / currentBudget) * 100);

        pBar.style.width = Math.min(100, ratio) + '%';
        if (variance > 0) {
            vEl.className = 'text-danger fw-bold';
            vEl.innerText = '+' + new Intl.NumberFormat('vi-VN').format(variance) + ' ₫ (Vượt dự toán)';
            pBar.className = 'progress-bar bg-danger';
        } else {
            vEl.className = 'text-success fw-bold';
            vEl.innerText = new Intl.NumberFormat('vi-VN').format(variance) + ' ₫ (Tiết kiệm)';
            pBar.className = 'progress-bar bg-success';
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    recalculateTotal();
});
</script>
