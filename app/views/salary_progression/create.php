<?php
/**
 * ============================================================
 *  POSUNG HRIS – View: salary_progression/create.php
 * ============================================================
 *  Form Đề xuất & Ban hành Quyết định Nâng bậc Lương
 * ============================================================
 */
?>
<div class="content-wrapper">
    <div class="mb-4">
        <a href="<?= BASE_URL ?>/salaryProgression/index/<?= $employee->id ?>" class="text-decoration-none text-muted small">
            <i class="fas fa-arrow-left me-1"></i> Quay lại lịch sử lương
        </a>
        <h2 class="mt-2 fw-bold text-dark"><i class="fas fa-plus-circle text-primary me-2"></i>Đề xuất Nâng bậc Lương: <?= h($employee->full_name) ?></h2>
        <p class="text-muted mb-0">Ban hành quyết định nâng lương, thăng cấp bậc hoặc điều chỉnh mức thu nhập</p>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-pen-to-square text-primary me-2"></i>Nội dung Quyết định Nâng lương</h5>
                </div>
                <div class="card-body p-4">
                    <form action="<?= BASE_URL ?>/salaryProgression/store" method="POST" id="createProgressionForm">
                        <input type="hidden" name="_csrf_token" value="<?= Session::generateCsrfToken() ?>">
                        <input type="hidden" name="employee_id" value="<?= $employee->id ?>">

                        <!-- THÔNG TIN LƯƠNG HIỆN TẠI -->
                        <div class="p-3 bg-light rounded-3 mb-4 border d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-muted small d-block">Mức lương cơ bản hiện tại:</span>
                                <h4 class="fw-bold text-success font-monospace mb-0"><?= number_format($currentSalary, 0, ',', '.') ?> ₫</h4>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-primary fs-6 px-3 py-1 font-monospace"><?= h($employee->emp_code) ?></span>
                                <small class="text-muted d-block mt-1"><?= h($employee->dept_name ?? 'Phòng ban N/A') ?></small>
                            </div>
                        </div>

                        <!-- HÀNG 1: MỨC LƯƠNG MỚI VÀ PHẦN TRĂM TĂNG -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Mức lương mới (VNĐ) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" step="1000" name="new_salary" id="inputNewSalary" class="form-control font-monospace fw-bold fs-5" placeholder="0" value="<?= $currentSalary * 1.1 ?>" required oninput="calcPercent()">
                                    <span class="input-group-text">₫</span>
                                </div>
                                <small class="text-muted">Nhập số tiền hoặc nhập tỷ lệ % bên cạnh.</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tỷ lệ điều chỉnh tăng (%)</label>
                                <div class="input-group">
                                    <input type="number" step="0.1" id="inputPercent" class="form-control font-monospace fs-5" placeholder="10" oninput="calcFromPercent()">
                                    <span class="input-group-text">%</span>
                                </div>
                                <small class="text-muted" id="diffText">Tăng thêm: +0 ₫</small>
                            </div>
                        </div>

                        <!-- HÀNG 2: NGÀY HIỆU LỰC & LÝ DO -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Ngày bắt đầu có hiệu lực <span class="text-danger">*</span></label>
                                <input type="date" name="effective_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Lý do điều chỉnh / Nâng bậc <span class="text-danger">*</span></label>
                                <select name="reason" class="form-select" required>
                                    <option value="Nâng bậc lương định kỳ" selected>Nâng bậc lương định kỳ</option>
                                    <option value="Thăng chức & Bổ nhiệm">Thăng chức & Bổ nhiệm</option>
                                    <option value="Đánh giá thành tích xuất sắc (KPI)">Đánh giá thành tích xuất sắc (KPI)</option>
                                    <option value="Điều chỉnh trượt giá thị trường">Điều chỉnh trượt giá thị trường</option>
                                    <option value="Hết thời gian thử việc / Đào tạo">Hết thời gian thử việc / Đào tạo</option>
                                    <option value="Khác">Khác</option>
                                </select>
                            </div>
                        </div>

                        <!-- HÀNG 3: SỐ QUYẾT ĐỊNH & NGÀY KÝ -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Số Quyết định (QĐ số)</label>
                                <input type="text" name="decision_number" class="form-control font-monospace text-uppercase" placeholder="Ví dụ: QĐ-NL-<?= date('Y') ?>/012">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Ngày ký quyết định</label>
                                <input type="date" name="decision_date" class="form-control" value="<?= date('Y-m-d') ?>">
                            </div>
                        </div>

                        <!-- HÀNG 4: GHI CHÚ -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Ghi chú / Căn cứ quyết định</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="Nhập căn cứ nâng lương (kết quả đánh giá hiệu suất, thâm niên, thành tích đặc biệt...)"></textarea>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="<?= BASE_URL ?>/salaryProgression/index/<?= $employee->id ?>" class="btn btn-light px-4">Hủy</a>
                            <button type="submit" class="btn btn-primary px-4"><i class="fas fa-check me-1"></i> Ban hành Quyết định</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-3 bg-light">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-dark mb-3"><i class="fas fa-circle-info text-primary me-2"></i>Quy định Nâng bậc Lương</h6>
                    <ul class="small text-muted ps-3 mb-0 space-y-2">
                        <li><strong>Định kỳ hàng năm:</strong> Xét nâng bậc vào tháng 12 hoặc tháng 1 cho CBNV đạt KPI từ loại Khá trở lên.</li>
                        <li><strong>Nâng bậc trước hạn:</strong> Áp dụng cho cá nhân có sáng kiến cải tiến, thành tích đặc biệt xuất sắc hoặc thăng cấp vị trí.</li>
                        <li><strong>Đồng bộ tự động:</strong> Khi quyết định nâng bậc được ban hành, hệ thống sẽ tự động cập nhật mức lương mới vào bảng lương tháng và thông báo tới nhân viên.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const baseSalary = <?= (float)$currentSalary ?>;

function calcPercent() {
    const newSal = parseFloat(document.getElementById('inputNewSalary').value) || 0;
    if (baseSalary > 0) {
        const diff = newSal - baseSalary;
        const pct = (diff / baseSalary) * 100;
        document.getElementById('inputPercent').value = pct.toFixed(1);
        document.getElementById('diffText').textContent = 'Tăng thêm: ' + (diff >= 0 ? '+' : '') + new Intl.NumberFormat('vi-VN').format(diff) + ' ₫';
    }
}

function calcFromPercent() {
    const pct = parseFloat(document.getElementById('inputPercent').value) || 0;
    if (baseSalary > 0) {
        const newSal = Math.round(baseSalary * (1 + pct / 100));
        document.getElementById('inputNewSalary').value = newSal;
        const diff = newSal - baseSalary;
        document.getElementById('diffText').textContent = 'Tăng thêm: ' + (diff >= 0 ? '+' : '') + new Intl.NumberFormat('vi-VN').format(diff) + ' ₫';
    }
}

document.addEventListener('DOMContentLoaded', calcPercent);
</script>
