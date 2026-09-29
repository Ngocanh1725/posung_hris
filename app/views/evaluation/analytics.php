<?php
/**
 * View: evaluation/analytics.php – Dashboard Phân Tích Đánh Giá 360°, Bell Curve & Radar Benchmark
 */

$period = $analytics['period'];
$bellBins = $analytics['bell_bins'];
$topPerformers = $analytics['top_performers'];
$bottomPerformers = $analytics['bottom_performers'];
$deptStats = $analytics['dept_stats'];
$kraBenchmarks = $analytics['kra_benchmarks'];

// Chuẩn bị dữ liệu cho Bell Curve Chart
$bellLabels = [];
$bellCounts = [];
$bellColors = [];
foreach ($bellBins as $binKey => $binData) {
    $bellLabels[] = $binData['label'];
    $bellCounts[] = $binData['count'];
    $bellColors[] = $binData['color'];
}

// Chuẩn bị dữ liệu Department Bar Chart
$deptLabels = [];
$deptAverages = [];
foreach ($deptStats as $ds) {
    $deptLabels[] = $ds['dept_name'];
    $deptAverages[] = (float)$ds['avg_score'];
}

// Chuẩn bị dữ liệu Radar Benchmark
$kraLabels = [];
$kraSelfAvg = [];
$kraMgrAvg = [];
$kraFinalAvg = [];
foreach ($kraBenchmarks as $kb) {
    $kraLabels[] = $kb['kra_title'];
    $kraSelfAvg[] = (float)($kb['avg_self'] ?? 0);
    $kraMgrAvg[] = (float)($kb['avg_manager'] ?? 0);
    $kraFinalAvg[] = (float)($kb['avg_final'] ?? 0);
}
?>

<!-- BREADCRUMB -->
<div class="breadcrumb-nav mb-3 d-flex align-items-center gap-2" style="font-size: 13px; color: var(--text-muted);">
    <a href="<?= BASE_URL ?>/evaluation" class="text-decoration-none" style="color: var(--primary);">
        <i class="fas fa-award"></i> Quản lý Đánh giá KPI
    </a>
    <i class="fas fa-chevron-right" style="font-size: 10px;"></i>
    <a href="<?= BASE_URL ?>/evaluation/goals/<?= $period->id ?>" class="text-decoration-none" style="color: var(--primary);">
        Mục tiêu KRA: <?= htmlspecialchars($period->name) ?>
    </a>
    <i class="fas fa-chevron-right" style="font-size: 10px;"></i>
    <span>Dashboard Phân Tích & Thống Kê 360°</span>
</div>

<!-- HEADER CARD -->
<div class="card mb-4" style="border: 1px solid var(--border); box-shadow: 0 4px 18px rgba(0,0,0,0.04); border-radius: 12px;">
    <div class="card-body" style="padding: 24px;">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="d-flex gap-3 align-items-center">
                <div style="width: 58px; height: 58px; border-radius: 14px; background: linear-gradient(135deg, #4f46e5, #06b6d4); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 24px; font-weight: 700; box-shadow: 0 6px 16px rgba(79,70,229,0.3); flex-shrink: 0;">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div>
                    <h2 style="font-size: 20px; font-weight: 700; margin: 0; color: var(--text);">
                        Dashboard Phân Tích Hiệu Suất 360°: <?= htmlspecialchars($period->name) ?>
                    </h2>
                    <div style="font-size: 13px; color: var(--text-muted); display: flex; gap: 16px; flex-wrap: wrap; margin-top: 4px;">
                        <span><i class="fas fa-calendar-alt text-primary"></i> Thời gian: <strong><?= date('d/m/Y', strtotime($period->start_date)) ?></strong> – <strong><?= date('d/m/Y', strtotime($period->end_date)) ?></strong></span>
                        <span><i class="fas fa-users text-primary"></i> Tổng nhân sự: <strong><?= $analytics['total_eligible'] ?></strong></span>
                        <span><i class="fas fa-star text-warning"></i> Điểm TB chu kỳ: <strong><?= $analytics['avg_final_score'] ?></strong> / 100</span>
                    </div>
                </div>
            </div>

            <!-- CHỌN CHU KỲ & NÚT THAO TÁC -->
            <div class="d-flex gap-2 align-items-center flex-wrap">
                <select class="form-select form-select-sm" style="width: 220px;" onchange="location.href='<?= BASE_URL ?>/evaluation/analytics/' + this.value;">
                    <?php foreach ($allPeriods as $p): ?>
                        <option value="<?= $p['id'] ?>" <?= $p['id'] == $period->id ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button onclick="window.print()" class="btn btn-ghost btn-sm" style="border: 1px solid var(--border);">
                    <i class="fas fa-print me-1"></i> In Báo Cáo
                </button>
                <a href="<?= BASE_URL ?>/evaluation/goals/<?= $period->id ?>" class="btn btn-primary btn-sm fw-bold">
                    <i class="fas fa-bullseye me-1"></i> Quản Lý KRA
                </a>
            </div>
        </div>

        <!-- TIMELINE REVIEW 4 GIAI ĐOẠN -->
        <div class="mt-4 pt-3" style="border-top: 1px solid var(--border);">
            <div style="font-size: 13px; font-weight: 700; color: var(--text); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 12px;">
                <i class="fas fa-route text-primary me-1"></i> Tiến độ dòng đời đánh giá 360° (Timeline Review Stages)
            </div>

            <div class="row g-3">
                <!-- Giai đoạn 1: Đặt Goals -->
                <div class="col-md-3 col-sm-6">
                    <div class="p-3 rounded h-100" style="background: rgba(2, 132, 199, 0.05); border: 1px solid rgba(2, 132, 199, 0.2);">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-bold" style="color: #0284c7; font-size: 13px;">1. Thiết lập KRA</span>
                            <i class="fas fa-bullseye text-info"></i>
                        </div>
                        <div style="font-size: 22px; font-weight: 800; color: #0369a1;">
                            <?= $analytics['goals_count'] ?> <small style="font-size: 12px; font-weight: normal; color: #64748b;">/ <?= $analytics['total_eligible'] ?> NV</small>
                        </div>
                        <div class="progress mt-2" style="height: 6px;">
                            <div class="progress-bar bg-info" style="width: <?= $analytics['total_eligible'] > 0 ? ($analytics['goals_count'] / $analytics['total_eligible']) * 100 : 0 ?>%;"></div>
                        </div>
                    </div>
                </div>

                <!-- Giai đoạn 2: Self Review -->
                <div class="col-md-3 col-sm-6">
                    <div class="p-3 rounded h-100" style="background: rgba(16, 185, 129, 0.05); border: 1px solid rgba(16, 185, 129, 0.2);">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-bold text-success" style="font-size: 13px;">2. Tự Đánh Giá (Self)</span>
                            <i class="fas fa-user-check text-success"></i>
                        </div>
                        <div style="font-size: 22px; font-weight: 800; color: #059669;">
                            <?= $analytics['self_count'] ?> <small style="font-size: 12px; font-weight: normal; color: #64748b;">/ <?= $analytics['total_eligible'] ?> NV</small>
                        </div>
                        <div class="progress mt-2" style="height: 6px;">
                            <div class="progress-bar bg-success" style="width: <?= $analytics['total_eligible'] > 0 ? ($analytics['self_count'] / $analytics['total_eligible']) * 100 : 0 ?>%;"></div>
                        </div>
                    </div>
                </div>

                <!-- Giai đoạn 3: Peer & Manager -->
                <div class="col-md-3 col-sm-6">
                    <div class="p-3 rounded h-100" style="background: rgba(245, 158, 11, 0.05); border: 1px solid rgba(245, 158, 11, 0.2);">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-bold" style="color: #d97706; font-size: 13px;">3. Đánh Giá Chéo & QL</span>
                            <i class="fas fa-users text-warning"></i>
                        </div>
                        <div style="font-size: 22px; font-weight: 800; color: #b45309;">
                            <?= $analytics['manager_count'] ?> <small style="font-size: 12px; font-weight: normal; color: #64748b;">QL & <?= $analytics['peer_count'] ?> Peer</small>
                        </div>
                        <div class="progress mt-2" style="height: 6px;">
                            <div class="progress-bar bg-warning" style="width: <?= $analytics['total_eligible'] > 0 ? ($analytics['manager_count'] / $analytics['total_eligible']) * 100 : 0 ?>%;"></div>
                        </div>
                    </div>
                </div>

                <!-- Giai đoạn 4: Final Score -->
                <div class="col-md-3 col-sm-6">
                    <div class="p-3 rounded h-100" style="background: rgba(79, 70, 229, 0.05); border: 1px solid rgba(79, 70, 229, 0.2);">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-bold" style="color: #4f46e5; font-size: 13px;">4. Chốt Điểm (Final)</span>
                            <i class="fas fa-stamp text-primary"></i>
                        </div>
                        <div style="font-size: 22px; font-weight: 800; color: #4338ca;">
                            <?= $analytics['final_count'] ?> <small style="font-size: 12px; font-weight: normal; color: #64748b;">/ <?= $analytics['total_eligible'] ?> NV</small>
                        </div>
                        <div class="progress mt-2" style="height: 6px;">
                            <div class="progress-bar bg-primary" style="width: <?= $analytics['total_eligible'] > 0 ? ($analytics['final_count'] / $analytics['total_eligible']) * 100 : 0 ?>%;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- BIỂU ĐỒ HÀNG 1: BELL CURVE & RADAR BENCHMARK -->
<div class="row g-4 mb-4">
    <!-- BIỂU ĐỒ PHÂN BỐ ĐIỂM SỐ (BELL CURVE) -->
    <div class="col-lg-6">
        <div class="card h-100" style="border: 1px solid var(--border); box-shadow: 0 4px 18px rgba(0,0,0,0.04); border-radius: 12px;">
            <div class="card-header d-flex justify-content-between align-items-center" style="background: var(--bg-hover); padding: 14px 20px;">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-chart-bar text-primary"></i>
                    <strong style="font-size: 14px; color: var(--text);">Phân Bố Điểm Số Chuẩn (Bell Curve / Normal Distribution)</strong>
                </div>
                <span class="badge bg-primary"><?= $analytics['final_count'] ?> Nhân sự đã chốt</span>
            </div>
            <div class="card-body p-3">
                <div style="position: relative; width: 100%; height: 320px;">
                    <canvas id="bellCurveChart"></canvas>
                </div>
                <div class="text-muted text-center mt-2 small" style="font-size: 11px;">
                    <i class="fas fa-info-circle me-1"></i> Biểu đồ chuẩn hóa hình chuông Gauss: Phân bổ nhân sự theo các dải điểm hiệu suất.
                </div>
            </div>
        </div>
    </div>

    <!-- RADAR BENCHMARK CÔNG TY -->
    <div class="col-lg-6">
        <div class="card h-100" style="border: 1px solid var(--border); box-shadow: 0 4px 18px rgba(0,0,0,0.04); border-radius: 12px;">
            <div class="card-header d-flex justify-content-between align-items-center" style="background: var(--bg-hover); padding: 14px 20px;">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-spider text-info"></i>
                    <strong style="font-size: 14px; color: var(--text);">Radar Benchmark Năng Lực & KRA Toàn Doanh Nghiệp</strong>
                </div>
                <span class="badge bg-light text-muted border">Toàn công ty</span>
            </div>
            <div class="card-body p-3">
                <div style="position: relative; width: 100%; height: 320px;">
                    <canvas id="kraRadarBenchmarkChart"></canvas>
                </div>
                <div class="text-muted text-center mt-2 small" style="font-size: 11px;">
                    <i class="fas fa-info-circle me-1"></i> So sánh điểm bình quân KRA: Tự chấm (Self), Quản lý chấm (Manager), và Điểm chốt (Final).
                </div>
            </div>
        </div>
    </div>
</div>

<!-- BIỂU ĐỒ HÀNG 2: SO SÁNH PHÒNG BAN -->
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="card" style="border: 1px solid var(--border); box-shadow: 0 4px 18px rgba(0,0,0,0.04); border-radius: 12px;">
            <div class="card-header d-flex justify-content-between align-items-center" style="background: var(--bg-hover); padding: 14px 20px;">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-building text-primary"></i>
                    <strong style="font-size: 14px; color: var(--text);">So Sánh Điểm Hiệu Suất Trung Bình Giữa Các Phòng Ban</strong>
                </div>
                <span class="badge bg-info text-white"><?= count($deptStats) ?> Phòng Ban</span>
            </div>
            <div class="card-body p-3">
                <div style="position: relative; width: 100%; height: 260px;">
                    <canvas id="deptComparisonChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- TOP PERFORMERS & BOTTOM PERFORMERS (KẾ HOẠCH BỒI DƯỠNG) -->
<div class="row g-4 mb-4">
    <!-- TOP PERFORMERS -->
    <div class="col-lg-6">
        <div class="card h-100" style="border: 1px solid var(--border); box-shadow: 0 4px 18px rgba(0,0,0,0.04); border-radius: 12px; overflow: hidden;">
            <div class="card-header d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(5, 150, 105, 0.05)); padding: 14px 20px; border-bottom: 1px solid rgba(16, 185, 129, 0.2);">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-crown text-warning" style="font-size: 18px;"></i>
                    <strong style="font-size: 14px; color: #065f46;">Top Performers – Nhân Sự Xuất Sắc Nhất Chu Kỳ</strong>
                </div>
                <span class="badge bg-success">Hạng A & B+</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                        <tbody>
                            <?php if (empty($topPerformers)): ?>
                                <tr><td class="text-center py-4 text-muted">Chưa có dữ liệu đánh giá đã chốt.</td></tr>
                            <?php else: ?>
                                <?php foreach ($topPerformers as $i => $tp): ?>
                                    <tr>
                                        <td class="text-center fw-bold" style="width: 45px;">
                                            <?php if ($i === 0): ?><i class="fas fa-medal text-warning fa-lg"></i>
                                            <?php elseif ($i === 1): ?><i class="fas fa-medal text-secondary fa-lg"></i>
                                            <?php elseif ($i === 2): ?><i class="fas fa-medal text-danger fa-lg" style="color: #cd7f32 !important;"></i>
                                            <?php else: ?><?= $i + 1 ?><?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark"><?= htmlspecialchars($tp['full_name']) ?></div>
                                            <div class="text-muted small"><?= htmlspecialchars($tp['employee_code']) ?> – <?= htmlspecialchars($tp['dept_name']) ?></div>
                                        </td>
                                        <td class="text-center">
                                            <div class="fw-bolder text-success" style="font-size: 17px;"><?= number_format((float)$tp['final_score'], 1) ?></div>
                                            <span class="badge bg-success" style="font-size: 10px;">Hạng <?= $tp['overall_grade'] ?></span>
                                        </td>
                                        <td class="text-end" style="padding-right: 16px;">
                                            <a href="<?= BASE_URL ?>/salaryProgression/create/<?= $tp['employee_id'] ?>?reason=<?= urlencode('Top Performer - Chu kỳ ' . $period->name . ' - ' . $tp['final_score'] . ' điểm') ?>" 
                                               class="btn btn-sm btn-outline-success" title="Đề xuất tăng lương ngay">
                                                <i class="fas fa-money-bill-wave me-1"></i> Tăng lương
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- BOTTOM PERFORMERS / PHÁT TRIỂN NĂNG LỰC -->
    <div class="col-lg-6">
        <div class="card h-100" style="border: 1px solid var(--border); box-shadow: 0 4px 18px rgba(0,0,0,0.04); border-radius: 12px; overflow: hidden;">
            <div class="card-header d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, rgba(239, 68, 68, 0.08), rgba(249, 115, 22, 0.04)); padding: 14px 20px; border-bottom: 1px solid rgba(239, 68, 68, 0.2);">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-seedling text-warning" style="font-size: 18px;"></i>
                    <strong style="font-size: 14px; color: #991b1b;">Nhân Sự Cần Bồi Dưỡng & Đào Tạo Phát Triển</strong>
                </div>
                <span class="badge bg-warning text-dark">< 75 Điểm</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                        <tbody>
                            <?php if (empty($bottomPerformers)): ?>
                                <tr><td class="text-center py-4 text-muted"><i class="fas fa-smile text-success me-1"></i> Tuyệt vời! Không có nhân sự nào dưới 75 điểm trong chu kỳ này.</td></tr>
                            <?php else: ?>
                                <?php foreach ($bottomPerformers as $i => $bp): ?>
                                    <tr>
                                        <td class="text-center text-muted fw-bold" style="width: 45px;"><?= $i + 1 ?></td>
                                        <td>
                                            <div class="fw-bold text-dark"><?= htmlspecialchars($bp['full_name']) ?></div>
                                            <div class="text-muted small"><?= htmlspecialchars($bp['employee_code']) ?> – <?= htmlspecialchars($bp['dept_name']) ?></div>
                                        </td>
                                        <td class="text-center">
                                            <div class="fw-bolder text-danger" style="font-size: 17px;"><?= number_format((float)$bp['final_score'], 1) ?></div>
                                            <span class="badge bg-danger" style="font-size: 10px;">Hạng <?= $bp['overall_grade'] ?></span>
                                        </td>
                                        <td class="text-end" style="padding-right: 16px;">
                                            <a href="<?= BASE_URL ?>/evaluation/finalScore/<?= $period->id ?>/<?= $bp['employee_id'] ?>" 
                                               class="btn btn-sm btn-outline-primary" title="Xem chi tiết & Kế hoạch đào tạo">
                                                <i class="fas fa-book-reader me-1"></i> Xem chi tiết
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CHART.JS INTEGRATION -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Bell Curve Chart
    const ctxBell = document.getElementById('bellCurveChart');
    if (ctxBell) {
        new Chart(ctxBell, {
            type: 'bar',
            data: {
                labels: <?= json_encode($bellLabels) ?>,
                datasets: [
                    {
                        type: 'line',
                        label: 'Đường cong Chuẩn (Gauss Normal)',
                        data: <?= json_encode($bellCounts) ?>,
                        borderColor: '#4f46e5',
                        backgroundColor: 'transparent',
                        borderWidth: 3,
                        tension: 0.4,
                        pointBackgroundColor: '#4f46e5',
                        pointRadius: 4
                    },
                    {
                        type: 'bar',
                        label: 'Số lượng Nhân sự',
                        data: <?= json_encode($bellCounts) ?>,
                        backgroundColor: <?= json_encode($bellColors) ?>,
                        borderRadius: 6
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 }
                    }
                },
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    }

    // 2. Radar Benchmark Toàn Doanh Nghiệp
    const ctxRadar = document.getElementById('kraRadarBenchmarkChart');
    if (ctxRadar) {
        new Chart(ctxRadar, {
            type: 'radar',
            data: {
                labels: <?= json_encode($kraLabels) ?>,
                datasets: [
                    {
                        label: 'Điểm Quản Lý TB',
                        data: <?= json_encode($kraMgrAvg) ?>,
                        backgroundColor: 'rgba(79, 70, 229, 0.2)',
                        borderColor: '#4f46e5',
                        borderWidth: 2
                    },
                    {
                        label: 'Điểm Chốt Cuối TB',
                        data: <?= json_encode($kraFinalAvg) ?>,
                        backgroundColor: 'rgba(16, 185, 129, 0.2)',
                        borderColor: '#10b981',
                        borderWidth: 2
                    },
                    {
                        label: 'Điểm Tự Chấm TB',
                        data: <?= json_encode($kraSelfAvg) ?>,
                        backgroundColor: 'rgba(245, 158, 11, 0.15)',
                        borderColor: '#f59e0b',
                        borderWidth: 1.5,
                        borderDash: [4, 4]
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    r: {
                        suggestedMin: 50,
                        suggestedMax: 100,
                        ticks: { stepSize: 10 }
                    }
                },
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    }

    // 3. Department Comparison Bar Chart
    const ctxDept = document.getElementById('deptComparisonChart');
    if (ctxDept) {
        new Chart(ctxDept, {
            type: 'bar',
            data: {
                labels: <?= json_encode($deptLabels) ?>,
                datasets: [{
                    label: 'Điểm Trung Bình (Thang 100)',
                    data: <?= json_encode($deptAverages) ?>,
                    backgroundColor: 'rgba(2, 132, 199, 0.75)',
                    borderColor: '#0284c7',
                    borderWidth: 1,
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        suggestedMin: 50,
                        suggestedMax: 100,
                        ticks: { stepSize: 10 }
                    }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });
    }
});
</script>
