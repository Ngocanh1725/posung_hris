<!-- ══════════════════════════════════════════════════════════
     POSUNG HRIS – BÁO CÁO THỐNG KÊ CƠ CẤU NHÂN SỰ PHÒNG BAN
     ══════════════════════════════════════════════════════════ -->

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="dept-statistics-page">
    <!-- Breadcrumb & Top Bar -->
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div class="breadcrumb-bar m-0">
            <a href="<?= BASE_URL ?>/organization/overview"><i class="fas fa-building"></i> Tổng quan</a>
            <i class="fas fa-chevron-right"></i>
            <a href="<?= BASE_URL ?>/organization"><i class="fas fa-list"></i> Phòng ban</a>
            <i class="fas fa-chevron-right"></i>
            <span class="text-primary fw-bold"><i class="fas fa-chart-pie"></i> Thống kê Nhân sự Phòng ban</span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="<?= BASE_URL ?>/organization/chart" class="btn btn-sm btn-primary">
                <i class="fas fa-sitemap"></i> Xem Sơ đồ Cây
            </a>
            <a href="<?= BASE_URL ?>/organization" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-list"></i> Danh sách Đơn vị
            </a>
            <button class="btn btn-sm btn-outline-primary" onclick="window.print()">
                <i class="fas fa-print"></i> In Báo cáo
            </button>
        </div>
    </div>

    <!-- 4 Overview KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-sm-6">
            <div class="card h-100 border-0 shadow-sm" style="border-radius: 12px; border-left: 4px solid #4f46e5 !important; background: var(--bg-card);">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small text-uppercase fw-bold">Tổng Số Đơn Vị</div>
                            <h3 class="fw-bold my-1 text-primary"><?= $statistics['total_depts'] ?? 0 ?></h3>
                            <small class="text-muted">Phòng ban & Tổ đội</small>
                        </div>
                        <div class="stat-icon-circle bg-primary bg-opacity-10 text-primary">
                            <i class="fas fa-building fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="card h-100 border-0 shadow-sm" style="border-radius: 12px; border-left: 4px solid #10b981 !important; background: var(--bg-card);">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small text-uppercase fw-bold">Tổng Nhân Sự Active</div>
                            <h3 class="fw-bold my-1 text-success"><?= number_format($statistics['total_employees'] ?? 0) ?></h3>
                            <small class="text-muted">Trung bình: <?= $statistics['avg_headcount'] ?? 0 ?> NV/đơn vị</small>
                        </div>
                        <div class="stat-icon-circle bg-success bg-opacity-10 text-success">
                            <i class="fas fa-users fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="card h-100 border-0 shadow-sm" style="border-radius: 12px; border-left: 4px solid #f59e0b !important; background: var(--bg-card);">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small text-uppercase fw-bold">Đơn Vị Quy Mô Nhất</div>
                            <h4 class="fw-bold my-1 text-truncate" style="max-width: 170px;" title="<?= htmlspecialchars($statistics['largest_dept']['name'] ?? '---') ?>">
                                <?= htmlspecialchars($statistics['largest_dept']['name'] ?? '---') ?>
                            </h4>
                            <small class="text-warning fw-bold"><i class="fas fa-crown"></i> <?= $statistics['largest_dept']['headcount'] ?? 0 ?> nhân sự</small>
                        </div>
                        <div class="stat-icon-circle bg-warning bg-opacity-10 text-warning">
                            <i class="fas fa-trophy fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="card h-100 border-0 shadow-sm" style="border-radius: 12px; border-left: 4px solid #ec4899 !important; background: var(--bg-card);">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small text-uppercase fw-bold">Cơ Cấu Giới Tính</div>
                            <?php 
                            $male = $statistics['total_male'] ?? 0;
                            $female = $statistics['total_female'] ?? 0;
                            $totalGender = max(1, $male + $female);
                            $malePct = round(($male / $totalGender) * 100);
                            $femalePct = 100 - $malePct;
                            ?>
                            <h4 class="fw-bold my-1">
                                <span class="text-primary"><?= $malePct ?>%</span> <small class="text-muted">/</small> <span class="text-pink" style="color: #ec4899;"><?= $femalePct ?>%</span>
                            </h4>
                            <small class="text-muted">Nam: <?= number_format($male) ?> | Nữ: <?= number_format($female) ?></small>
                        </div>
                        <div class="stat-icon-circle bg-danger bg-opacity-10 text-danger">
                            <i class="fas fa-venus-mars fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-3 mb-4">
        <!-- Top Departments Horizontal Bar Chart -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm" style="border-radius: 12px; background: var(--bg-card);">
                <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-primary">
                        <i class="fas fa-chart-bar me-2"></i>Quy Mô Nhân Sự Các Phòng Ban & Đơn Vị (Top 12)
                    </h6>
                    <span class="badge bg-light text-muted border">Quân số chính thức & thử việc</span>
                </div>
                <div class="card-body p-3">
                    <div style="height: 340px; position: relative;">
                        <canvas id="deptHeadcountChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Distribution by Type Donut Chart -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm" style="border-radius: 12px; background: var(--bg-card);">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h6 class="fw-bold mb-0 text-primary">
                        <i class="fas fa-chart-pie me-2"></i>Phân Bổ Theo Khối Hình Hoạt Động
                    </h6>
                </div>
                <div class="card-body p-3">
                    <div style="height: 250px; position: relative;">
                        <canvas id="deptTypeDonutChart"></canvas>
                    </div>
                    <div class="mt-3 pt-2 border-top">
                        <?php foreach (($statistics['type_stats'] ?? []) as $typeKey => $tInfo): ?>
                            <?php if ($tInfo['count'] > 0): ?>
                            <div class="d-flex justify-content-between align-items-center py-1 small">
                                <span><i class="fas fa-circle me-1" style="font-size: 8px; color: var(--primary);"></i> <?= $tInfo['label'] ?>:</span>
                                <div>
                                    <strong><?= $tInfo['employees'] ?> NV</strong>
                                    <span class="text-muted">(<?= $tInfo['count'] ?> đơn vị)</span>
                                </div>
                            </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Statistics Table -->
    <div class="card border-0 shadow-sm" style="border-radius: 12px; background: var(--bg-card);">
        <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h6 class="fw-bold mb-0 text-primary">
                <i class="fas fa-table me-2"></i>Bảng Tổng Hợp Chi Tiết Cơ Cấu & Quân Số
            </h6>
            <div class="d-flex gap-2">
                <input type="text" id="statTableSearch" class="form-control form-control-sm" placeholder="Lọc nhanh bảng..." style="width: 220px;" oninput="filterStatTable(this.value)">
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="statTable" style="font-size: 13.5px;">
                    <thead style="background: var(--bg-hover, #f8fafc); font-size: 11px; text-transform: uppercase; color: var(--text-muted);">
                        <tr>
                            <th style="width: 50px; text-align: center;">STT</th>
                            <th style="width: 110px;">Mã Đơn Vị</th>
                            <th>Tên Phòng Ban / Bộ Phận</th>
                            <th>Cấp Quản Lý Trực Thuộc</th>
                            <th>Trưởng Bộ Phận</th>
                            <th style="text-align: center;">Khối</th>
                            <th style="text-align: center; width: 120px;">Nam / Nữ</th>
                            <th style="text-align: right; width: 120px;">Quân Số</th>
                            <th style="text-align: center; width: 130px;">Tỷ Trọng (%)</th>
                            <th style="text-align: center; width: 90px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $totalEmps = max(1, (int)($statistics['total_employees'] ?? 1));
                        $idx = 1;
                        foreach ($allDepts as $d): 
                            $cnt = (int)($d['employee_count'] ?? 0);
                            $pct = round(($cnt / $totalEmps) * 100, 1);
                            $maleC = (int)($d['male_count'] ?? 0);
                            $femC = (int)($d['female_count'] ?? 0);
                        ?>
                        <tr>
                            <td style="text-align: center; font-weight: 600; color: var(--text-muted);"><?= $idx++ ?></td>
                            <td><span class="badge bg-light text-primary border fw-bold"><?= htmlspecialchars($d['dept_code']) ?></span></td>
                            <td>
                                <strong style="color: var(--text);"><?= htmlspecialchars($d['dept_name']) ?></strong>
                            </td>
                            <td>
                                <?php if (!empty($d['parent_name'])): ?>
                                    <span class="text-muted"><i class="fas fa-level-up-alt fa-rotate-90 me-1"></i><?= htmlspecialchars($d['parent_name']) ?></span>
                                <?php else: ?>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary">Cấp Gốc (Root)</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($d['manager_name'])): ?>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width: 26px; height: 26px; font-size: 11px;">
                                            <?= mb_substr($d['manager_name'], 0, 1, 'UTF-8') ?>
                                        </div>
                                        <div>
                                            <div class="fw-semibold" style="line-height: 1.2;"><?= htmlspecialchars($d['manager_name']) ?></div>
                                            <small class="text-muted" style="font-size: 11px;"><?= htmlspecialchars($d['manager_position'] ?? 'Trưởng đơn vị') ?></small>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted fst-italic">Chưa bổ nhiệm</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center;">
                                <?php
                                $typeBadge = [
                                    'bod'      => ['Ban Giám Đốc', 'bg-indigo text-white', '#4338ca'],
                                    'office'   => ['Văn Phòng', 'bg-primary text-white', '#3b82f6'],
                                    'factory'  => ['Sản Xuất', 'bg-warning text-dark', '#f59e0b'],
                                    'site_pmb' => ['Công Trường', 'bg-success text-white', '#10b981'],
                                ][$d['type'] ?? 'office'] ?? ['Khác', 'bg-secondary text-white', '#64748b'];
                                ?>
                                <span class="badge" style="background: <?= $typeBadge[2] ?>; font-size: 11px; padding: 4px 8px;">
                                    <?= $typeBadge[0] ?>
                                </span>
                            </td>
                            <td style="text-align: center;">
                                <small class="text-primary fw-semibold"><?= $maleC ?> ♂</small>
                                <span class="text-muted">/</span>
                                <small class="text-danger fw-semibold"><?= $femC ?> ♀</small>
                            </td>
                            <td style="text-align: right;">
                                <span class="fw-bold fs-6 <?= $cnt > 0 ? 'text-primary' : 'text-muted' ?>">
                                    <?= number_format($cnt) ?>
                                </span>
                                <small class="text-muted">NV</small>
                            </td>
                            <td style="text-align: center;">
                                <div class="d-flex align-items-center gap-1 justify-content-center">
                                    <div class="progress flex-grow-1" style="height: 6px; background: #e2e8f0; border-radius: 4px;">
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: <?= min(100, $pct * 2) ?>%;"></div>
                                    </div>
                                    <small class="fw-bold text-muted" style="font-size: 11px; min-width: 38px; text-align: right;"><?= $pct ?>%</small>
                                </div>
                            </td>
                            <td style="text-align: center;">
                                <a href="<?= BASE_URL ?>/organization/detail/<?= $d['id'] ?>" class="btn btn-sm btn-ghost p-1 text-primary" title="Xem chi tiết bộ phận">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
.stat-icon-circle {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Dữ liệu biểu đồ thanh ngang
    <?php
    $topDepts = array_slice($statistics['dept_headcounts'] ?? [], 0, 12);
    $chartLabels = array_column($topDepts, 'name');
    $chartCounts = array_column($topDepts, 'headcount');
    ?>
    const barLabels = <?= json_encode($chartLabels, JSON_UNESCAPED_UNICODE) ?>;
    const barCounts = <?= json_encode($chartCounts) ?>;

    const ctxBar = document.getElementById('deptHeadcountChart').getContext('2d');
    new Chart(ctxBar, {
        type: 'bar',
        data: {
            labels: barLabels,
            datasets: [{
                label: 'Số lượng nhân sự',
                data: barCounts,
                backgroundColor: 'rgba(79, 70, 229, 0.85)',
                hoverBackgroundColor: '#4338ca',
                borderRadius: 6,
                borderSkipped: false
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(ctx) { return ' Quân số: ' + ctx.raw + ' người'; }
                    }
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,0.05)' },
                    ticks: { precision: 0 }
                },
                y: {
                    grid: { display: false },
                    ticks: { font: { size: 12 } }
                }
            }
        }
    });

    // 2. Dữ liệu biểu đồ Donut phân bổ theo khối
    <?php
    $donutLabels = [];
    $donutData = [];
    $donutColors = [
        'office'   => '#3b82f6',
        'factory'  => '#f59e0b',
        'site_pmb' => '#10b981',
        'bod'      => '#6366f1',
    ];
    $activeDonutColors = [];
    foreach (($statistics['type_stats'] ?? []) as $key => $tInfo) {
        if ($tInfo['employees'] > 0) {
            $donutLabels[] = $tInfo['label'];
            $donutData[] = $tInfo['employees'];
            $activeDonutColors[] = $donutColors[$key] ?? '#94a3b8';
        }
    }
    ?>
    const ctxDonut = document.getElementById('deptTypeDonutChart').getContext('2d');
    new Chart(ctxDonut, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode($donutLabels, JSON_UNESCAPED_UNICODE) ?>,
            datasets: [{
                data: <?= json_encode($donutData) ?>,
                backgroundColor: <?= json_encode($activeDonutColors) ?>,
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '65%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { boxWidth: 12, font: { size: 11 } }
                }
            }
        }
    });
});

// Search in table
function filterStatTable(term) {
    term = term.toLowerCase().trim();
    const rows = document.querySelectorAll('#statTable tbody tr');
    rows.forEach(r => {
        const text = r.innerText.toLowerCase();
        r.style.display = text.includes(term) ? '' : 'none';
    });
}
</script>
