<?php
/**
 * ============================================================
 *  POSUNG HRIS – View: salary_progression/report.php
 * ============================================================
 *  Báo cáo Biến động Lương & Chi phí Nhân sự theo Phòng ban/Dự án
 * ============================================================
 */

$ov = $overview;
$deptNames = [];
$deptIncreases = [];
foreach ($byDept as $bd) {
    $deptNames[] = $bd->dept_name;
    $deptIncreases[] = (float)$bd->fund_diff;
}
?>
<div class="content-wrapper">
    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <a href="<?= BASE_URL ?>/salaryProgression" class="text-decoration-none text-muted small">
                <i class="fas fa-arrow-left me-1"></i> Quay lại danh sách nâng bậc
            </a>
            <h2 class="mt-2 fw-bold text-dark"><i class="fas fa-chart-pie text-primary me-2"></i>Báo cáo Biến động Lương & Chi phí Năm <?= $year ?></h2>
            <p class="text-muted mb-0">Thống kê chi phí quỹ lương điều chỉnh, phân bổ ngân sách theo phòng ban và dự án</p>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <form action="<?= BASE_URL ?>/salaryProgression/report" method="GET" class="d-flex gap-2">
                <select name="year" class="form-select form-select-sm" onchange="this.form.submit()">
                    <?php for ($y = date('Y'); $y >= 2022; $y--): ?>
                        <option value="<?= $y ?>" <?= $year == $y ? 'selected' : '' ?>>Năm <?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </form>
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                <i class="fas fa-print me-1"></i> In báo cáo
            </button>
        </div>
    </div>

    <!-- 4 CARDS KPI TỔNG QUAN -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 bg-white p-3 h-100 border-start border-4 border-primary">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Nhân sự được nâng lương</span>
                        <h3 class="fw-bold mb-0 text-primary mt-1"><?= number_format($ov->total_employees_increased) ?></h3>
                        <small class="text-muted">Số quyết định: <?= number_format($ov->total_decisions) ?></small>
                    </div>
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 text-primary fs-3">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 bg-white p-3 h-100 border-start border-4 border-success">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Quỹ lương tăng thêm / tháng</span>
                        <h3 class="fw-bold mb-0 text-success mt-1">+<?= number_format($ov->total_monthly_increase, 0, ',', '.') ?> ₫</h3>
                        <small class="text-muted">Chi phí quỹ lương định kỳ</small>
                    </div>
                    <div class="rounded-circle bg-success bg-opacity-10 p-3 text-success fs-3">
                        <i class="fas fa-vault"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 bg-white p-3 h-100 border-start border-4 border-warning">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Tỷ lệ tăng bình quân</span>
                        <h3 class="fw-bold mb-0 text-warning mt-1">+<?= round($ov->avg_percent_increase, 1) ?>%</h3>
                        <small class="text-muted">Trung bình các đợt nâng bậc</small>
                    </div>
                    <div class="rounded-circle bg-warning bg-opacity-10 p-3 text-warning fs-3">
                        <i class="fas fa-percent"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 bg-white p-3 h-100 border-start border-4 border-info">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Ước tính ngân sách năm</span>
                        <?php 
                        $annualCost = $ov->total_monthly_increase * 12;
                        ?>
                        <h3 class="fw-bold mb-0 text-info mt-1">+<?= number_format($annualCost, 0, ',', '.') ?> ₫</h3>
                        <small class="text-muted">Tổng phát sinh 12 tháng</small>
                    </div>
                    <div class="rounded-circle bg-info bg-opacity-10 p-3 text-info fs-3">
                        <i class="fas fa-money-bill-trend-up"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- BIỂU ĐỒ BIẾN ĐỘNG THEO PHÒNG BAN -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-chart-column text-primary me-2"></i>Biểu đồ Quỹ lương Tăng thêm theo Phòng ban (VNĐ)</h5>
        </div>
        <div class="card-body p-4">
            <div style="height: 280px; position: relative;">
                <canvas id="deptIncreaseChart"></canvas>
            </div>
        </div>
    </div>

    <!-- BẢNG BIẾN ĐỘNG THEO PHÒNG BAN -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-building text-primary me-2"></i>Chi tiết Biến động Quỹ lương theo Phòng ban</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Phòng ban / Bộ phận</th>
                        <th class="text-center">Số lượt tăng</th>
                        <th class="text-end">Quỹ lương cũ / tháng</th>
                        <th class="text-end">Quỹ lương mới / tháng</th>
                        <th class="text-end">Mức tăng thêm (+VNĐ)</th>
                        <th class="text-end pe-3">Tỷ lệ tăng (%)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($byDept)): ?>
                        <tr><td colspan="6" class="text-center py-4 text-muted">Không có dữ liệu biến động lương trong năm <?= $year ?>.</td></tr>
                    <?php else: ?>
                        <?php foreach ($byDept as $d): ?>
                            <tr>
                                <td class="ps-3 fw-semibold text-dark"><?= h($d->dept_name) ?></td>
                                <td class="text-center"><span class="badge bg-primary px-2"><?= number_format($d->increase_count) ?></span></td>
                                <td class="text-end font-monospace text-muted"><?= number_format($d->old_fund, 0, ',', '.') ?> ₫</td>
                                <td class="text-end font-monospace fw-bold text-dark"><?= number_format($d->new_fund, 0, ',', '.') ?> ₫</td>
                                <td class="text-end font-monospace text-success fw-bold">+<?= number_format($d->fund_diff, 0, ',', '.') ?> ₫</td>
                                <td class="text-end pe-3 font-monospace fw-semibold text-primary">+<?= round($d->avg_percent, 1) ?>%</td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- DANH SÁCH CÁC QUYẾT ĐỊNH GẦN ĐÂY -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-list-check text-secondary me-2"></i>Các Quyết định Nâng lương trong Năm <?= $year ?></h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Ngày H.Lực</th>
                        <th>Họ và tên</th>
                        <th>Mã NV</th>
                        <th>Phòng ban</th>
                        <th class="text-end">Lương cũ</th>
                        <th class="text-end">Lương mới</th>
                        <th class="text-end">Tăng thêm</th>
                        <th>Lý do</th>
                        <th class="text-end pe-3">Số QĐ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentIncreases)): ?>
                        <tr><td colspan="9" class="text-center py-4 text-muted">Chưa có quyết định nâng lương nào trong năm <?= $year ?>.</td></tr>
                    <?php else: ?>
                        <?php foreach (array_slice($recentIncreases, 0, 15) as $r): ?>
                            <tr>
                                <td class="ps-3 font-monospace"><?= fmtDate($r->effective_date) ?></td>
                                <td class="fw-semibold text-dark"><?= h($r->employee_name) ?></td>
                                <td class="font-monospace text-primary"><?= h($r->emp_code) ?></td>
                                <td><small class="text-muted"><?= h($r->dept_name ?? '---') ?></small></td>
                                <td class="text-end font-monospace text-muted"><?= number_format($r->old_salary, 0, ',', '.') ?> ₫</td>
                                <td class="text-end font-monospace fw-bold"><?= number_format($r->new_salary, 0, ',', '.') ?> ₫</td>
                                <td class="text-end font-monospace text-success fw-bold">+<?= number_format($r->increase_amount, 0, ',', '.') ?> ₫</td>
                                <td><span class="badge bg-light text-dark border"><?= h($r->reason ?: 'Nâng bậc') ?></span></td>
                                <td class="text-end pe-3 font-monospace text-primary fw-medium"><?= h($r->decision_num ?: '---') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- SCRIPT BIỂU ĐỒ CHART.JS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const deptNames = <?= json_encode($deptNames) ?>;
    const deptIncreases = <?= json_encode($deptIncreases) ?>;

    if (deptNames.length > 0) {
        const ctx = document.getElementById('deptIncreaseChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: deptNames,
                datasets: [{
                    label: 'Quỹ lương tăng thêm hàng tháng (VNĐ)',
                    data: deptIncreases,
                    backgroundColor: 'rgba(37, 99, 235, 0.8)',
                    borderColor: '#2563eb',
                    borderWidth: 1,
                    borderRadius: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function (ctx) {
                                return ' Tăng: +' + new Intl.NumberFormat('vi-VN').format(ctx.raw || 0) + ' ₫/tháng';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function (val) {
                                return (val / 1000000) + ' Tr';
                            }
                        },
                        grid: { color: 'rgba(0, 0, 0, 0.05)' }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    }
});
</script>
