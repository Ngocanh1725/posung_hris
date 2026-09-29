<?php
/**
 * View: evaluation/summary.php – Báo cáo Tổng kết Chu kỳ Đánh giá & Biểu đồ Radar Năng lực
 */

$totalEvaluated = array_sum($grade_counts);
$radarLabels = [];
$radarValues = [];
foreach ($radar_categories as $rc) {
    $catMap = [
        'KPI'        => 'Chỉ số KPI',
        'Competency' => 'Năng lực Chuyên môn',
        'Attitude'   => 'Thái độ & Kỷ luật',
        'HSE'        => 'An toàn & 5S',
    ];
    $radarLabels[] = $catMap[$rc['category']] ?? $rc['category'];
    $radarValues[] = (float)$rc['avg_score'];
}
?>

<!-- BREADCRUMB -->
<div class="breadcrumb-nav mb-3" style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: var(--text-muted);">
    <a href="<?= BASE_URL ?>/evaluation" style="color: var(--primary); text-decoration: none;">
        <i class="fas fa-award"></i> Quản lý Đánh giá KPI
    </a>
    <i class="fas fa-chevron-right" style="font-size: 10px;"></i>
    <a href="<?= BASE_URL ?>/evaluation/show/<?= $period->id ?>" style="color: var(--primary); text-decoration: none;">
        <?= htmlspecialchars($period->name) ?>
    </a>
    <i class="fas fa-chevron-right" style="font-size: 10px;"></i>
    <span>Báo cáo Tổng kết & Phân tích Radar</span>
</div>

<!-- SUMMARY HEADER -->
<div class="card mb-4" style="border: 1px solid var(--border); box-shadow: 0 4px 18px rgba(0,0,0,0.04);">
    <div class="card-body" style="padding: 24px;">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="d-flex gap-3 align-items-center">
                <div style="width: 56px; height: 56px; border-radius: 14px; background: linear-gradient(135deg, #10b981, #059669); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 24px; box-shadow: 0 6px 16px rgba(16,185,129,0.3); flex-shrink: 0;">
                    <i class="fas fa-chart-pie"></i>
                </div>
                <div>
                    <h2 style="font-size: 20px; font-weight: 700; margin: 0; color: var(--text);">
                        Báo Cáo Tổng Hợp Kết Quả: <?= htmlspecialchars($period->name) ?>
                    </h2>
                    <div style="font-size: 13px; color: var(--text-muted); display: flex; gap: 16px; flex-wrap: wrap; margin-top: 4px;">
                        <span><i class="fas fa-calendar-alt text-success"></i> Thời gian: <strong><?= date('d/m/Y', strtotime($period->start_date)) ?></strong> – <strong><?= date('d/m/Y', strtotime($period->end_date)) ?></strong></span>
                        <span><i class="fas fa-clipboard-check text-success"></i> Đã hoàn thành: <strong><?= $totalEvaluated ?></strong> nhân sự</span>
                        <span><i class="fas fa-layer-group text-success"></i> Mẫu: <strong><?= htmlspecialchars($period->template_name ?? 'Chuẩn') ?></strong></span>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button onclick="window.print()" class="btn btn-ghost" style="border: 1px solid var(--border);">
                    <i class="fas fa-print"></i> In Báo cáo
                </button>
                <a href="<?= BASE_URL ?>/evaluation/show/<?= $period->id ?>" class="btn btn-primary">
                    <i class="fas fa-list"></i> Danh sách Chấm điểm
                </a>
            </div>
        </div>
    </div>
</div>

<!-- GRADE DISTRIBUTION CARDS -->
<div class="row g-3 mb-4">
    <!-- Hạng A -->
    <div class="col-md-3 col-sm-6">
        <div class="card h-100" style="border: 1px solid rgba(16, 185, 129, 0.3); background: rgba(16, 185, 129, 0.04); border-radius: 12px;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="badge bg-success text-white" style="font-size: 13px; padding: 4px 10px; border-radius: 14px;">Hạng A</span>
                    <i class="fas fa-medal text-success" style="font-size: 20px;"></i>
                </div>
                <div class="mt-2">
                    <div style="font-size: 28px; font-weight: 800; color: #10b981; line-height: 1;">
                        <?= $grade_counts['A'] ?>
                    </div>
                    <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">
                        Xuất sắc (85 - 100đ) • <strong><?= $totalEvaluated > 0 ? round(($grade_counts['A'] / $totalEvaluated) * 100, 1) : 0 ?>%</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Hạng B -->
    <div class="col-md-3 col-sm-6">
        <div class="card h-100" style="border: 1px solid rgba(59, 130, 246, 0.3); background: rgba(59, 130, 246, 0.04); border-radius: 12px;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="badge bg-primary text-white" style="font-size: 13px; padding: 4px 10px; border-radius: 14px;">Hạng B</span>
                    <i class="fas fa-thumbs-up text-primary" style="font-size: 20px;"></i>
                </div>
                <div class="mt-2">
                    <div style="font-size: 28px; font-weight: 800; color: #3b82f6; line-height: 1;">
                        <?= $grade_counts['B'] ?>
                    </div>
                    <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">
                        Tốt (70 - 84.9đ) • <strong><?= $totalEvaluated > 0 ? round(($grade_counts['B'] / $totalEvaluated) * 100, 1) : 0 ?>%</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Hạng C -->
    <div class="col-md-3 col-sm-6">
        <div class="card h-100" style="border: 1px solid rgba(245, 158, 11, 0.3); background: rgba(245, 158, 11, 0.04); border-radius: 12px;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="badge bg-warning text-dark" style="font-size: 13px; padding: 4px 10px; border-radius: 14px;">Hạng C</span>
                    <i class="fas fa-check-circle text-warning" style="font-size: 20px;"></i>
                </div>
                <div class="mt-2">
                    <div style="font-size: 28px; font-weight: 800; color: #d97706; line-height: 1;">
                        <?= $grade_counts['C'] ?>
                    </div>
                    <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">
                        Đạt (50 - 69.9đ) • <strong><?= $totalEvaluated > 0 ? round(($grade_counts['C'] / $totalEvaluated) * 100, 1) : 0 ?>%</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Hạng D -->
    <div class="col-md-3 col-sm-6">
        <div class="card h-100" style="border: 1px solid rgba(239, 68, 68, 0.3); background: rgba(239, 68, 68, 0.04); border-radius: 12px;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="badge bg-danger text-white" style="font-size: 13px; padding: 4px 10px; border-radius: 14px;">Hạng D</span>
                    <i class="fas fa-exclamation-triangle text-danger" style="font-size: 20px;"></i>
                </div>
                <div class="mt-2">
                    <div style="font-size: 28px; font-weight: 800; color: #ef4444; line-height: 1;">
                        <?= $grade_counts['D'] ?>
                    </div>
                    <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">
                        Cần cải thiện (< 50đ) • <strong><?= $totalEvaluated > 0 ? round(($grade_counts['D'] / $totalEvaluated) * 100, 1) : 0 ?>%</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CHARTS ROW: RADAR CHART & GRADE DONUT -->
<div class="row g-4 mb-4">
    <!-- RADAR CHART -->
    <div class="col-lg-7">
        <div class="card h-100" style="border: 1px solid var(--border); box-shadow: 0 2px 12px rgba(0,0,0,0.03);">
            <div class="card-header d-flex justify-content-between align-items-center" style="background: var(--bg-hover); padding: 14px 20px; border-bottom: 1px solid var(--border);">
                <h5 style="margin: 0; font-size: 15px; font-weight: 700; color: var(--text);">
                    <i class="fas fa-spider text-primary"></i> Biểu Đồ Radar Hiệu Suất Năng Lực Toàn Chu Kỳ
                </h5>
                <span class="badge bg-light text-muted" style="border: 1px solid var(--border);">Thang điểm 1 - 5</span>
            </div>
            <div class="card-body d-flex flex-column align-items-center justify-content-center" style="min-height: 340px; padding: 20px;">
                <?php if (!empty($radar_categories)): ?>
                    <div style="width: 100%; max-width: 450px; height: 320px; position: relative;">
                        <canvas id="radarChart"></canvas>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-chart-area fa-3x mb-3 text-secondary" style="opacity: 0.5;"></i>
                        <div>Chưa có đủ dữ liệu chấm điểm chi tiết để vẽ biểu đồ radar.</div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- GRADE DISTRIBUTION DOUGHNUT & TOP PERFORMERS -->
    <div class="col-lg-5">
        <div class="card h-100" style="border: 1px solid var(--border); box-shadow: 0 2px 12px rgba(0,0,0,0.03);">
            <div class="card-header" style="background: var(--bg-hover); padding: 14px 20px; border-bottom: 1px solid var(--border);">
                <h5 style="margin: 0; font-size: 15px; font-weight: 700; color: var(--text);">
                    <i class="fas fa-trophy text-warning"></i> Top 5 Nhân Sự Xuất Sắc Nhất Chu Kỳ
                </h5>
            </div>
            <div class="card-body p-0">
                <?php if (empty($top_performers)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-award fa-3x mb-3 text-secondary" style="opacity: 0.5;"></i>
                        <div>Chưa có dữ liệu xếp loại.</div>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php $rank = 1; foreach ($top_performers as $top): ?>
                            <div class="list-group-item d-flex align-items-center justify-content-between p-3" style="border-color: var(--border);">
                                <div class="d-flex align-items-center gap-3">
                                    <div style="width: 28px; height: 28px; border-radius: 50%; background: <?= $rank === 1 ? '#fef08a' : ($rank === 2 ? '#e2e8f0' : ($rank === 3 ? '#fed7aa' : 'var(--bg-hover)')) ?>; color: <?= $rank === 1 ? '#ca8a04' : ($rank === 2 ? '#475569' : ($rank === 3 ? '#ea580c' : 'var(--text-muted)')) ?>; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px;">
                                        <?= $rank++ ?>
                                    </div>
                                    <div style="width: 38px; height: 38px; border-radius: 50%; background: #e0e7ff; color: #4338ca; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 14px; overflow: hidden; border: 1px solid var(--border);">
                                        <?php if (!empty($top['avatar_path'])): ?>
                                            <img src="<?= BASE_URL . '/' . htmlspecialchars($top['avatar_path']) ?>" alt="" style="width: 100%; height: 100%; object-fit: cover;">
                                        <?php else: ?>
                                            <?= mb_strtoupper(mb_substr($top['full_name'], 0, 1, 'UTF-8'), 'UTF-8') ?>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <div style="font-weight: 600; color: var(--text); font-size: 14px;">
                                            <a href="<?= BASE_URL ?>/employee/view/<?= $top['id'] ?>" target="_blank" style="color: inherit; text-decoration: none;">
                                                <?= htmlspecialchars($top['full_name']) ?>
                                            </a>
                                        </div>
                                        <div style="font-size: 12px; color: var(--text-muted);">
                                            <?= htmlspecialchars($top['dept_name'] ?? '') ?> • <?= htmlspecialchars($top['pos_title'] ?? '') ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <div style="font-weight: 800; font-size: 16px; color: #10b981;">
                                        <?= number_format((float)$top['final_score'], 1) ?> <span style="font-size: 11px; font-weight: normal; color: var(--text-muted);">đ</span>
                                    </div>
                                    <span class="badge bg-success text-white" style="font-size: 10px;">Hạng <?= $top['overall_grade'] ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- DEPARTMENT BREAKDOWN TABLE -->
<div class="card mb-4" style="border: 1px solid var(--border); box-shadow: 0 2px 12px rgba(0,0,0,0.03);">
    <div class="card-header d-flex justify-content-between align-items-center" style="background: var(--bg-hover); padding: 14px 20px; border-bottom: 1px solid var(--border);">
        <h5 style="margin: 0; font-size: 15px; font-weight: 700; color: var(--text);">
            <i class="fas fa-building text-primary"></i> Tổng Hợp Hiệu Suất Theo Phòng Ban / Bộ Phận
        </h5>
        <span class="badge bg-light text-muted" style="border: 1px solid var(--border);"><?= count($dept_summary) ?> phòng ban</span>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size: 13.5px;">
            <thead style="background: var(--bg-hover); color: var(--text-muted); font-size: 12px; text-transform: uppercase;">
                <tr>
                    <th style="width: 50px; text-align: center;">STT</th>
                    <th>Phòng ban</th>
                    <th style="text-align: center; width: 110px;">Tổng NV</th>
                    <th style="text-align: center; width: 120px;">Đã Đánh Giá</th>
                    <th style="width: 160px;">Tỷ Lệ Hoàn Thành</th>
                    <th style="text-align: center; width: 110px;">Điểm KPI TB</th>
                    <th style="text-align: center; width: 110px;">Điểm NL TB</th>
                    <th style="text-align: center; width: 130px;">Điểm Chung TB</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($dept_summary)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">Chưa có dữ liệu phòng ban.</td>
                    </tr>
                <?php else: ?>
                    <?php $idx = 1; foreach ($dept_summary as $ds): ?>
                        <?php
                            $rate = $ds['total_employees'] > 0 ? round(($ds['evaluated_count'] / $ds['total_employees']) * 100) : 0;
                            $deptScore = (float)($ds['avg_score'] ?? 0);
                            $scoreColor = $deptScore >= 85 ? '#10b981' : ($deptScore >= 70 ? '#3b82f6' : ($deptScore >= 50 ? '#f59e0b' : '#ef4444'));
                        ?>
                        <tr>
                            <td style="text-align: center; color: var(--text-muted);"><?= $idx++ ?></td>
                            <td>
                                <strong style="color: var(--text);"><?= htmlspecialchars($ds['dept_name']) ?></strong>
                                <span class="badge bg-light text-muted ms-1" style="border: 1px solid var(--border); font-size: 11px;">
                                    <?= htmlspecialchars($ds['dept_code']) ?>
                                </span>
                            </td>
                            <td style="text-align: center;"><?= (int)$ds['total_employees'] ?></td>
                            <td style="text-align: center; font-weight: 600; color: #10b981;"><?= (int)$ds['evaluated_count'] ?></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="height: 6px; border-radius: 4px;">
                                        <div class="progress-bar" style="width: <?= $rate ?>%; background: #4f46e5;"></div>
                                    </div>
                                    <span style="font-size: 11.5px; font-weight: 600; min-width: 32px;"><?= $rate ?>%</span>
                                </div>
                            </td>
                            <td style="text-align: center; font-weight: 600;">
                                <?= $ds['avg_kpi'] ? number_format((float)$ds['avg_kpi'], 1) : '---' ?>
                            </td>
                            <td style="text-align: center; font-weight: 600;">
                                <?= $ds['avg_comp'] ? number_format((float)$ds['avg_comp'], 1) : '---' ?>
                            </td>
                            <td style="text-align: center;">
                                <?php if ($ds['avg_score']): ?>
                                    <span style="font-size: 15px; font-weight: 800; color: <?= $scoreColor ?>;">
                                        <?= number_format($deptScore, 1) ?>
                                    </span>
                                    <span style="font-size: 11px; color: var(--text-muted);">/100</span>
                                <?php else: ?>
                                    <span class="text-muted">---</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- CHART.JS INTEGRATION -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const radarCtx = document.getElementById('radarChart');
    if (radarCtx) {
        new Chart(radarCtx, {
            type: 'radar',
            data: {
                labels: <?= json_encode($radarLabels) ?>,
                datasets: [{
                    label: 'Điểm Trung Bình (Thang 5)',
                    data: <?= json_encode($radarValues) ?>,
                    backgroundColor: 'rgba(79, 70, 229, 0.25)',
                    borderColor: '#4f46e5',
                    pointBackgroundColor: '#4f46e5',
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: '#4f46e5',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    r: {
                        angleLines: { color: 'rgba(0, 0, 0, 0.1)' },
                        grid: { color: 'rgba(0, 0, 0, 0.05)' },
                        suggestedMin: 1,
                        suggestedMax: 5,
                        ticks: {
                            stepSize: 1,
                            font: { size: 10 }
                        },
                        pointLabels: {
                            font: { size: 12, weight: '600' }
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 12, font: { size: 12 } }
                    }
                }
            }
        });
    }
});
</script>
