<?php
/**
 * ============================================================
 *  POSUNG HRIS – View: salary_progression/batch_increase.php
 * ============================================================
 *  Quy trình Nâng lương Hàng loạt (Batch Salary Increase)
 * ============================================================
 */
?>
<div class="content-wrapper">
    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <a href="<?= BASE_URL ?>/salaryProgression" class="text-decoration-none text-muted small">
                <i class="fas fa-arrow-left me-1"></i> Quay lại danh sách nâng bậc
            </a>
            <h2 class="mt-2 fw-bold text-dark"><i class="fas fa-users-gear text-success me-2"></i>Nâng lương Hàng loạt (Batch Increase)</h2>
            <p class="text-muted mb-0">Áp dụng tăng lương theo tỷ lệ % hoặc số tiền cố định cho nhiều nhân sự cùng lúc</p>
        </div>
        <div>
            <a href="<?= BASE_URL ?>/salaryProgression" class="btn btn-outline-secondary">Hủy bỏ</a>
        </div>
    </div>

    <!-- FORM NÂNG LƯƠNG HÀNG LOẠT -->
    <form action="<?= BASE_URL ?>/salaryProgression/batchIncrease" method="POST" id="batchIncreaseForm">
        <input type="hidden" name="_csrf_token" value="<?= Session::generateCsrfToken() ?>">

        <!-- BẢNG ĐIỀU KHIỂN CẤU HÌNH MỨC TĂNG -->
        <div class="card border-0 shadow-sm rounded-3 mb-4 border-top border-4 border-success">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-sliders text-success me-2"></i>1. Thiết lập Chính sách Nâng lương</h5>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Hình thức tăng lương <span class="text-danger">*</span></label>
                        <select name="increase_type" id="increaseType" class="form-select fw-semibold" onchange="updateCalculations()">
                            <option value="percent" selected>Tăng theo tỷ lệ Phần trăm (%)</option>
                            <option value="fixed">Tăng mức cố định (+VNĐ)</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold" id="valueLabel">Mức tăng (%) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" step="0.1" name="increase_value" id="increaseValue" class="form-control font-monospace fw-bold fs-5" value="10" required oninput="updateCalculations()">
                            <span class="input-group-text" id="unitText">%</span>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Ngày hiệu lực <span class="text-danger">*</span></label>
                        <input type="date" name="effective_date" class="form-control" value="<?= date('Y-m-01') ?>" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Lý do nâng lương <span class="text-danger">*</span></label>
                        <select name="reason" class="form-select" required>
                            <option value="Nâng bậc lương định kỳ hàng năm" selected>Nâng bậc lương định kỳ hàng năm</option>
                            <option value="Điều chỉnh trượt giá thị trường">Điều chỉnh trượt giá thị trường</option>
                            <option value="Đánh giá hiệu suất toàn công ty">Đánh giá hiệu suất toàn công ty</option>
                            <option value="Điều chỉnh theo thang bảng lương mới">Điều chỉnh theo thang bảng lương mới</option>
                        </select>
                    </div>
                </div>

                <div class="row g-3 mt-1">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Số Quyết định chung</label>
                        <input type="text" name="decision_number" class="form-control font-monospace text-uppercase" placeholder="Ví dụ: QĐ-NL-<?= date('Y') ?>/BATCH">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Ngày ký quyết định</label>
                        <input type="date" name="decision_date" class="form-control" value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label fw-semibold">Ghi chú quyết định</label>
                        <input type="text" name="notes" class="form-control" placeholder="Căn cứ nghị quyết HĐQT hoặc quy chế nâng bậc...">
                    </div>
                </div>
            </div>
        </div>

        <!-- BỘ LỌC CHỌN NHÂN SỰ -->
        <div class="card border-0 shadow-sm rounded-3 mb-3">
            <div class="card-body p-3 bg-light rounded-3">
                <div class="row g-2 align-items-center">
                    <div class="col-md-4">
                        <span class="fw-bold text-dark"><i class="fas fa-users-viewfinder text-primary me-2"></i>2. Chọn Nhân sự Áp dụng (<?= count($employees) ?> người)</span>
                    </div>
                    <div class="col-md-3">
                        <select id="filterDept" class="form-select form-select-sm" onchange="filterTable()">
                            <option value="">-- Tất cả phòng ban --</option>
                            <?php foreach ($departments as $d): ?>
                                <option value="<?= h($d['dept_name']) ?>"><?= h($d['dept_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="text" id="filterKeyword" class="form-control form-select-sm" placeholder="Tìm tên hoặc mã nhân viên..." oninput="filterTable()">
                    </div>
                    <div class="col-md-2 text-end">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleSelectAll(true)">Chọn hết</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleSelectAll(false)">Bỏ chọn</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- BẢNG DANH SÁCH NHÂN SỰ VÀ REALTIME PREVIEW -->
        <div class="card border-0 shadow-sm rounded-3 mb-5">
            <div class="table-responsive" style="max-height: 480px; overflow-y: auto;">
                <table class="table table-hover align-middle mb-0" id="employeeBatchTable">
                    <thead class="table-light sticky-top">
                        <tr>
                            <th class="ps-3" style="width: 45px;">
                                <input type="checkbox" class="form-check-input" id="checkAllMaster" onchange="masterCheckChanged(this)">
                            </th>
                            <th style="width: 120px;">Mã NV</th>
                            <th>Họ và tên</th>
                            <th>Phòng ban</th>
                            <th>Chức vụ</th>
                            <th class="text-end" style="width: 160px;">Lương hiện tại</th>
                            <th class="text-end" style="width: 170px;">Lương mới dự kiến</th>
                            <th class="text-end pe-3" style="width: 150px;">Mức tăng</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($employees as $emp): ?>
                            <?php 
                            $currSal = (float)($emp['current_salary'] ?? 0);
                            if ($currSal <= 0) $currSal = 5000000;
                            ?>
                            <tr class="emp-row" data-name="<?= strtolower(h($emp['full_name'])) ?>" data-code="<?= strtolower(h($emp['emp_code'])) ?>" data-dept="<?= h($emp['dept_name'] ?? '') ?>" data-salary="<?= $currSal ?>">
                                <td class="ps-3">
                                    <input type="checkbox" name="employee_ids[]" value="<?= $emp['id'] ?>" class="form-check-input emp-check" onchange="updateSummary()">
                                </td>
                                <td class="font-monospace fw-bold text-primary"><?= h($emp['emp_code']) ?></td>
                                <td>
                                    <div class="fw-semibold text-dark"><?= h($emp['full_name']) ?></div>
                                </td>
                                <td><span class="text-muted small"><?= h($emp['dept_name'] ?? 'N/A') ?></span></td>
                                <td><span class="text-muted small"><?= h($emp['pos_title'] ?? 'N/A') ?></span></td>
                                <td class="text-end font-monospace">
                                    <?= number_format($currSal, 0, ',', '.') ?> ₫
                                </td>
                                <td class="text-end font-monospace fw-bold text-success new-salary-cell">
                                    0 ₫
                                </td>
                                <td class="text-end pe-3 font-monospace text-primary diff-salary-cell">
                                    +0 ₫
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- THANH TỔNG KẾT & NÚT THỰC THI (STICKY BOTTOM) -->
        <div class="fixed-bottom bg-white border-top shadow-lg py-3 px-4" style="z-index: 1030;">
            <div class="container-fluid d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="d-flex align-items-center gap-4">
                    <div>
                        <span class="text-muted small d-block">Số nhân sự được chọn:</span>
                        <h4 class="fw-bold mb-0 text-primary"><span id="selectedCount">0</span> / <?= count($employees) ?> nhân viên</h4>
                    </div>
                    <div class="border-start ps-4">
                        <span class="text-muted small d-block">Tổng quỹ lương tăng thêm dự tính:</span>
                        <h4 class="fw-bold mb-0 text-success font-monospace" id="totalIncreaseCost">+0 ₫/tháng</h4>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <a href="<?= BASE_URL ?>/salaryProgression" class="btn btn-light px-4">Hủy bỏ</a>
                    <button type="submit" class="btn btn-success px-4 fw-bold" id="btnSubmitBatch" disabled onclick="return confirm('Bạn có chắc chắn muốn thực hiện tăng lương hàng loạt cho danh sách nhân viên đã chọn? Hành động này sẽ cập nhật mức lương mới vào hồ sơ nhân sự.')">
                        <i class="fas fa-check-double me-1"></i> Xác nhận Nâng lương Hàng loạt
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function updateCalculations() {
    const type = document.getElementById('increaseType').value;
    const val = parseFloat(document.getElementById('increaseValue').value) || 0;
    
    if (type === 'percent') {
        document.getElementById('valueLabel').innerHTML = 'Mức tăng (%) <span class="text-danger">*</span>';
        document.getElementById('unitText').textContent = '%';
    } else {
        document.getElementById('valueLabel').innerHTML = 'Mức tăng (+VNĐ) <span class="text-danger">*</span>';
        document.getElementById('unitText').textContent = '₫';
    }

    const rows = document.querySelectorAll('.emp-row');
    rows.forEach(row => {
        const cur = parseFloat(row.getAttribute('data-salary')) || 0;
        let newSal = 0;
        if (type === 'percent') {
            newSal = Math.round(cur * (1 + val / 100));
        } else {
            newSal = cur + val;
        }
        const diff = newSal - cur;

        row.querySelector('.new-salary-cell').textContent = new Intl.NumberFormat('vi-VN').format(newSal) + ' ₫';
        row.querySelector('.diff-salary-cell').textContent = '+' + new Intl.NumberFormat('vi-VN').format(diff) + ' ₫';
        row.setAttribute('data-diff', diff);
    });

    updateSummary();
}

function updateSummary() {
    const checks = document.querySelectorAll('.emp-check:checked');
    let totalDiff = 0;
    checks.forEach(chk => {
        const row = chk.closest('tr');
        const diff = parseFloat(row.getAttribute('data-diff')) || 0;
        totalDiff += diff;
    });

    document.getElementById('selectedCount').textContent = checks.length;
    document.getElementById('totalIncreaseCost').textContent = '+' + new Intl.NumberFormat('vi-VN').format(totalDiff) + ' ₫/tháng';

    const btn = document.getElementById('btnSubmitBatch');
    btn.disabled = (checks.length === 0);
}

function masterCheckChanged(master) {
    const rows = document.querySelectorAll('.emp-row');
    rows.forEach(r => {
        if (r.style.display !== 'none') {
            const chk = r.querySelector('.emp-check');
            chk.checked = master.checked;
        }
    });
    updateSummary();
}

function toggleSelectAll(select) {
    const rows = document.querySelectorAll('.emp-row');
    rows.forEach(r => {
        if (r.style.display !== 'none') {
            r.querySelector('.emp-check').checked = select;
        }
    });
    document.getElementById('checkAllMaster').checked = select;
    updateSummary();
}

function filterTable() {
    const dept = document.getElementById('filterDept').value.toLowerCase();
    const kw = document.getElementById('filterKeyword').value.trim().toLowerCase();

    const rows = document.querySelectorAll('.emp-row');
    rows.forEach(r => {
        const rName = r.getAttribute('data-name');
        const rCode = r.getAttribute('data-code');
        const rDept = r.getAttribute('data-dept').toLowerCase();

        const matchDept = !dept || rDept === dept;
        const matchKw = !kw || rName.includes(kw) || rCode.includes(kw);

        r.style.display = (matchDept && matchKw) ? '' : 'none';
    });
}

document.addEventListener('DOMContentLoaded', function() {
    updateCalculations();
});
</script>
