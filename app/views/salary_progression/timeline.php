<?php
/**
 * ============================================================
 *  POSUNG HRIS – View: salary_progression/timeline.php
 * ============================================================
 *  Vertical Timeline Lịch sử Lương & Biểu đồ Chart.js Tăng trưởng Lương
 * ============================================================
 */

$firstSalary = !empty($history) ? end($history)->new_salary : $currentSalary;
$cumulativeIncrease = $currentSalary - $firstSalary;
$cumulativePercent = ($firstSalary > 0) ? round(($cumulativeIncrease / $firstSalary) * 100, 1) : 0;
?>

<div class="content-wrapper">
    <!-- HEADER & BREADCRUMB -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <a href="<?= BASE_URL ?>/salaryProgression" class="text-decoration-none text-muted small">
                <i class="fas fa-arrow-left me-1"></i> Quay lại danh sách nâng bậc
            </a>
            <h2 class="mt-2 fw-bold text-dark"><i class="fas fa-chart-line-up text-primary me-2"></i>Lịch sử Lương: <?= h($employee->full_name) ?></h2>
            <p class="text-muted mb-0">Mã NV: <span class="font-monospace fw-semibold text-primary"><?= h($employee->emp_code) ?></span> | Phòng ban: <?= h($employee->dept_name ?? 'N/A') ?> | Chức vụ: <?= h($employee->pos_title ?? 'N/A') ?></p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>/employee/view/<?= $employee->id ?>#tab-4" class="btn btn-outline-secondary">
                <i class="fas fa-id-card me-1"></i> Hồ sơ 360°
            </a>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#quickIncreaseModal">
                <i class="fas fa-plus-circle me-1"></i> Đề xuất nâng lương
            </button>
        </div>
    </div>

    <!-- KHỐI KPI TÓM TẮT TIẾN TRÌNH LƯƠNG -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 bg-white p-3 h-100 border-start border-4 border-success">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Mức lương hiện hưởng</span>
                        <h3 class="fw-bold mb-0 text-success mt-1"><?= number_format($currentSalary, 0, ',', '.') ?> ₫</h3>
                        <small class="text-muted">Áp dụng từ: <?= !empty($history) ? fmtDate($history[0]->effective_date) : 'N/A' ?></small>
                    </div>
                    <div class="rounded-circle bg-success bg-opacity-10 p-3 text-success fs-3">
                        <i class="fas fa-coins"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 bg-white p-3 h-100 border-start border-4 border-primary">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Tăng trưởng lũy kế</span>
                        <h3 class="fw-bold mb-0 text-primary mt-1">+<?= number_format($cumulativeIncrease, 0, ',', '.') ?> ₫</h3>
                        <small class="text-muted">Mức ban đầu: <?= number_format($firstSalary, 0, ',', '.') ?> ₫ (+<?= $cumulativePercent ?>%)</small>
                    </div>
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 text-primary fs-3">
                        <i class="fas fa-arrow-trend-up"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 bg-white p-3 h-100 border-start border-4 border-info">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Số lần điều chỉnh lương</span>
                        <h3 class="fw-bold mb-0 text-info mt-1"><?= count($history) ?> đợt</h3>
                        <small class="text-muted">Theo dõi từ khi gia nhập công ty</small>
                    </div>
                    <div class="rounded-circle bg-info bg-opacity-10 p-3 text-info fs-3">
                        <i class="fas fa-timeline"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- BIỂU ĐỒ CHART.JS LINE CHART TĂNG TRƯỞNG LƯƠNG -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-chart-line text-primary me-2"></i>Biểu đồ Tăng trưởng Lương theo Thời gian</h5>
            <small class="text-muted">Đơn vị: VNĐ</small>
        </div>
        <div class="card-body p-4">
            <div style="height: 280px; position: relative;">
                <canvas id="salaryProgressionChart"></canvas>
            </div>
        </div>
    </div>

    <!-- VERTICAL TIMELINE UI -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-clock-rotate-left text-secondary me-2"></i>Dòng thời gian Nâng bậc Lương (Timeline)</h5>
            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#quickIncreaseModal">
                <i class="fas fa-plus me-1"></i> Thêm quyết định
            </button>
        </div>
        <div class="card-body p-4">
            <?php if (empty($history)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-calendar-xmark fa-3x mb-3 text-secondary"></i>
                    <p class="mb-0">Nhân viên này chưa có bản ghi diễn biến lương nào.</p>
                </div>
            <?php else: ?>
                <div class="vertical-timeline">
                    <?php foreach ($history as $idx => $item): ?>
                        <div class="timeline-item">
                            <!-- TIMELINE BADGE ICON -->
                            <div class="timeline-marker <?= $idx === 0 ? 'marker-current' : 'marker-past' ?>">
                                <i class="fas <?= $idx === 0 ? 'fa-star' : 'fa-check' ?>"></i>
                            </div>

                            <!-- TIMELINE CONTENT CARD -->
                            <div class="timeline-content card border shadow-none rounded-3 mb-4">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-primary fs-6 px-3 py-1 font-monospace">
                                                <i class="far fa-calendar-check me-1"></i><?= fmtDate($item->effective_date) ?>
                                            </span>
                                            <?php if ($idx === 0): ?>
                                                <span class="badge bg-success-subtle text-success border border-success-subtle">Mức lương hiện hành</span>
                                            <?php endif; ?>
                                            <span class="badge bg-light text-dark border"><?= h($item->reason ?: 'Điều chỉnh lương') ?></span>
                                        </div>
                                        <div>
                                            <?php if ($item->increase_amount > 0): ?>
                                                <span class="badge bg-success px-2 py-1 fs-6">
                                                    <i class="fas fa-arrow-up me-1"></i>+<?= number_format($item->increase_amount, 0, ',', '.') ?> ₫ (+<?= $item->increase_percent ?>%)
                                                </span>
                                            <?php elseif ($item->increase_amount < 0): ?>
                                                <span class="badge bg-danger px-2 py-1 fs-6">
                                                    <i class="fas fa-arrow-down me-1"></i><?= number_format($item->increase_amount, 0, ',', '.') ?> ₫ (<?= $item->increase_percent ?>%)
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary px-2 py-1">Mức khởi điểm</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="row align-items-center g-3 my-1">
                                        <div class="col-md-5">
                                            <div class="d-flex align-items-center gap-3">
                                                <div>
                                                    <span class="text-muted small d-block">Mức lương cũ:</span>
                                                    <span class="text-secondary font-monospace fw-semibold fs-6">
                                                        <?= ($item->old_salary > 0) ? number_format($item->old_salary, 0, ',', '.') . ' ₫' : '---' ?>
                                                    </span>
                                                </div>
                                                <i class="fas fa-arrow-right text-muted"></i>
                                                <div>
                                                    <span class="text-muted small d-block">Mức lương mới:</span>
                                                    <span class="text-primary font-monospace fw-bold fs-5">
                                                        <?= number_format($item->new_salary ?? $item->base_salary, 0, ',', '.') ?> ₫
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-7 border-start ps-md-4">
                                            <div class="small space-y-1">
                                                <div>
                                                    <i class="fas fa-file-contract text-secondary me-1"></i>
                                                    <strong>Số QĐ:</strong> <span class="font-monospace fw-semibold text-dark"><?= h($item->decision_num ?: 'Chưa cập nhật') ?></span>
                                                    <?php if (!empty($item->decision_date)): ?>
                                                        <span class="text-muted">(Ký ngày <?= fmtDate($item->decision_date) ?>)</span>
                                                    <?php endif; ?>
                                                </div>
                                                <div>
                                                    <i class="fas fa-user-check text-secondary me-1"></i>
                                                    <strong>Người phê duyệt:</strong> <span class="text-muted"><?= h($item->approver_name ?: 'Ban Giám Đốc') ?></span>
                                                </div>
                                                <?php if (!empty($item->notes)): ?>
                                                    <div class="text-muted fst-italic mt-1 bg-light p-2 rounded">
                                                        <i class="fas fa-quote-left me-1 text-secondary"></i><?= h($item->notes) ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ==================== MODAL NÂNG LƯƠNG NHANH ==================== -->
<div class="modal fade" id="quickIncreaseModal" tabindex="-1" aria-labelledby="quickIncreaseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="<?= BASE_URL ?>/salaryProgression/store" method="POST" class="modal-content border-0 shadow">
            <input type="hidden" name="_csrf_token" value="<?= Session::generateCsrfToken() ?>">
            <input type="hidden" name="employee_id" value="<?= $employee->id ?>">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="quickIncreaseModalLabel"><i class="fas fa-chart-line-up me-2"></i>Quyết định Nâng bậc Lương</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="p-3 bg-light rounded mb-3 border">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small d-block">Nhân sự áp dụng:</span>
                            <strong class="text-dark fs-6"><?= h($employee->full_name) ?> (<?= h($employee->emp_code) ?>)</strong>
                        </div>
                        <div class="text-end">
                            <span class="text-muted small d-block">Lương hiện tại:</span>
                            <span class="text-success font-monospace fw-bold fs-6" id="currentSalaryDisplay"><?= number_format($currentSalary, 0, ',', '.') ?> ₫</span>
                        </div>
                    </div>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Mức lương mới (VNĐ) <span class="text-danger">*</span></label>
                        <input type="number" step="1000" name="new_salary" id="modalNewSalary" class="form-control font-monospace fw-bold" placeholder="Nhập mức lương..." value="<?= $currentSalary * 1.1 ?>" required oninput="calcPercent()">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Tỷ lệ tăng (%)</label>
                        <div class="input-group">
                            <input type="number" step="0.1" id="modalPercent" class="form-control font-monospace" placeholder="10" oninput="calcFromPercent()">
                            <span class="input-group-text">%</span>
                        </div>
                    </div>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Ngày hiệu lực <span class="text-danger">*</span></label>
                        <input type="date" name="effective_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Lý do điều chỉnh <span class="text-danger">*</span></label>
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

                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Số quyết định</label>
                        <input type="text" name="decision_number" class="form-control font-monospace text-uppercase" placeholder="QĐ-NL-<?= date('Y') ?>/...">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Ngày ký quyết định</label>
                        <input type="date" name="decision_date" class="form-control" value="<?= date('Y-m-d') ?>">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Ghi chú / Căn cứ nâng lương</label>
                    <textarea name="notes" class="form-control" rows="3" placeholder="Ví dụ: Căn cứ đánh giá KPI năm và đề xuất của Trưởng bộ phận..."></textarea>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy bỏ</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-check me-1"></i> Xác nhận ban hành quyết định</button>
            </div>
        </form>
    </div>
</div>

<!-- CSS CHO VERTICAL TIMELINE -->
<style>
.vertical-timeline {
    position: relative;
    padding-left: 30px;
}
.vertical-timeline::before {
    content: '';
    position: absolute;
    top: 15px;
    bottom: 15px;
    left: 14px;
    width: 2px;
    background: #e2e8f0;
}
.timeline-item {
    position: relative;
}
.timeline-marker {
    position: absolute;
    left: -30px;
    top: 15px;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    z-index: 2;
}
.marker-current {
    background: #2563eb;
    color: #fff;
    box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.2);
}
.marker-past {
    background: #94a3b8;
    color: #fff;
}
</style>

<!-- CHART.JS SCRIPT -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const currentSalaryVal = <?= (float)$currentSalary ?>;

function calcPercent() {
    const newSal = parseFloat(document.getElementById('modalNewSalary').value) || 0;
    if (currentSalaryVal > 0) {
        const pct = ((newSal - currentSalaryVal) / currentSalaryVal) * 100;
        document.getElementById('modalPercent').value = pct.toFixed(1);
    }
}

function calcFromPercent() {
    const pct = parseFloat(document.getElementById('modalPercent').value) || 0;
    if (currentSalaryVal > 0) {
        const newSal = Math.round(currentSalaryVal * (1 + pct / 100));
        document.getElementById('modalNewSalary').value = newSal;
    }
}

// Khởi tạo biểu đồ Chart.js
document.addEventListener("DOMContentLoaded", function () {
    calcPercent();

    const chartData = <?= json_encode($chartData) ?>;
    const ctx = document.getElementById('salaryProgressionChart').getContext('2d');
    
    new Chart(ctx, {
        type: 'line',
        data: chartData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: { font: { family: 'inherit', weight: 'bold' } }
                },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            let val = context.raw || 0;
                            return ' Mức lương: ' + new Intl.NumberFormat('vi-VN').format(val) + ' ₫';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: false,
                    ticks: {
                        callback: function (val) {
                            return (val / 1000000) + ' Tr';
                        }
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });
});
</script>
