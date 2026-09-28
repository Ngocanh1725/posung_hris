<!-- app/views/dashboard/index.php -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
:root {
    --navy: #1e293b;
    --slate: #475569;
    --orange: #f97316;
    --bg-card: #ffffff;
    --text-primary: #334155;
    --text-secondary: #64748b;
}

body {
    background-color: #f1f5f9;
    color: var(--text-primary);
}

.kpi-card {
    background: var(--bg-card);
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    border-left: 4px solid var(--navy);
    height: 100%;
    transition: transform 0.2s;
}

.kpi-card:hover {
    transform: translateY(-2px);
}

.kpi-card.border-orange { border-left-color: var(--orange); }
.kpi-card.border-red { border-left-color: #ef4444; }
.kpi-card.border-green { border-left-color: #10b981; }

.kpi-title {
    font-size: 0.875rem;
    font-weight: 600;
    text-transform: uppercase;
    color: var(--slate);
    margin-bottom: 10px;
}

.kpi-value {
    font-size: 2rem;
    font-weight: 700;
    color: var(--navy);
}

.kpi-icon {
    font-size: 2rem;
    color: #cbd5e1;
    position: absolute;
    right: 20px;
    top: 20px;
}

.kpi-subtext {
    font-size: 0.8rem;
    color: var(--text-secondary);
    margin-top: 10px;
}

.chart-container {
    background: var(--bg-card);
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    margin-top: 20px;
}
</style>

<div class="content-header">
    <div class="header-left">
        <h2><i class="fas fa-chart-line text-primary"></i> Executive Dashboard</h2>
        <p>Trung tâm điều hành POSUNG HRIS - Tỷ lệ Turnover: <strong class="<?= $turnoverRate > 5 ? 'text-danger' : 'text-success' ?>"><?= $turnoverRate ?>%</strong> (Tháng này)</p>
    </div>
</div>

<!-- Row 1: KPI Cards -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="kpi-card position-relative">
            <i class="fas fa-users kpi-icon"></i>
            <div class="kpi-title">Tổng Quân Số</div>
            <div class="kpi-value"><?= $totalEmployees ?></div>
            <div class="kpi-subtext">
                <span class="text-primary"><i class="fas fa-building"></i> VP: <?= $headcountOffice ?></span> | 
                <span class="text-success"><i class="fas fa-hard-hat"></i> CT: <?= $headcountSite ?></span>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="kpi-card border-green position-relative">
            <i class="fas fa-project-diagram kpi-icon"></i>
            <div class="kpi-title">Dự án Đang Thi Công</div>
            <div class="kpi-value"><?= $totalProjects ?></div>
            <div class="kpi-subtext">
                <a href="<?= BASE_URL ?>/project" class="text-decoration-none">Xem danh sách dự án <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="kpi-card border-orange position-relative">
            <i class="fas fa-shield-alt kpi-icon"></i>
            <div class="kpi-title">Cảnh Báo HSE (60 Ngày)</div>
            <div class="kpi-value <?= $totalHseWarnings > 0 ? 'text-danger' : 'text-success' ?>"><?= $totalHseWarnings ?></div>
            <div class="kpi-subtext">
                <i class="fas fa-exclamation-triangle"></i> Thẻ & Hành nghề sắp hết hạn
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="kpi-card border-red position-relative">
            <i class="fas fa-clock kpi-icon"></i>
            <div class="kpi-title">Tổng Giờ OT (Tháng)</div>
            <div class="kpi-value"><?= number_format($totalOtHours) ?> <small style="font-size: 1rem;">h</small></div>
            <div class="kpi-subtext <?= $otWarningsCount > 0 ? 'text-danger fw-bold' : 'text-success' ?>">
                <i class="fas fa-exclamation-circle"></i> <?= $otWarningsCount ?> nhân sự vượt trần (>40h)
            </div>
        </div>
    </div>
</div>

<!-- Row 2: Charts -->
<div class="row">
    <!-- Cột trái: Stacked Bar & Donut -->
    <div class="col-md-7">
        <div class="chart-container">
            <h5 class="mb-3 text-uppercase text-secondary fw-bold" style="font-size:14px;"><i class="fas fa-city"></i> Phân bổ nhân sự theo Dự án</h5>
            <canvas id="projectEmpChart" height="120"></canvas>
        </div>
        
        <div class="chart-container">
            <h5 class="mb-3 text-uppercase text-secondary fw-bold" style="font-size:14px;"><i class="fas fa-file-invoice-dollar"></i> Biến động Quỹ lương & OT (6 tháng)</h5>
            <canvas id="salaryTrendChart" height="100"></canvas>
        </div>
    </div>

    <!-- Cột phải: Line Chart -->
    <div class="col-md-5">
        <div class="chart-container" style="height: 100%;">
            <h5 class="mb-3 text-uppercase text-secondary fw-bold" style="font-size:14px;"><i class="fas fa-hard-hat"></i> Tỷ lệ Đào tạo HSE Nhóm 1-6</h5>
            <canvas id="hseDonutChart" height="250"></canvas>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Biểu đồ Cột nhân sự dự án
    const projData = <?= json_encode($projectEmpData) ?>;
    const projLabels = projData.map(d => d.project_code || 'VP');
    const projCounts = projData.map(d => d.emp_count);
    
    new Chart(document.getElementById('projectEmpChart'), {
        type: 'bar',
        data: {
            labels: projLabels,
            datasets: [{
                label: 'Số lượng (Người)',
                data: projCounts,
                backgroundColor: '#1e293b',
                borderRadius: 4
            }]
        },
        options: {
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true } }
        }
    });

    // 2. Biểu đồ Đường quỹ lương
    const chartMonths = <?= json_encode($chartMonths) ?>;
    const salaryData = <?= json_encode($salaryData) ?>;
    const otPayData = <?= json_encode($otPayData) ?>;
    
    new Chart(document.getElementById('salaryTrendChart'), {
        type: 'line',
        data: {
            labels: chartMonths,
            datasets: [
                {
                    label: 'Quỹ Lương (Triệu VNĐ)',
                    data: salaryData,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    tension: 0.3,
                    fill: true
                },
                {
                    label: 'Tiền OT (Triệu VNĐ)',
                    data: otPayData,
                    borderColor: '#f97316',
                    backgroundColor: 'rgba(249, 115, 22, 0.1)',
                    tension: 0.3,
                    fill: true
                }
            ]
        },
        options: {
            plugins: { legend: { position: 'bottom' } },
            scales: { y: { beginAtZero: true } }
        }
    });

    // 3. Biểu đồ Tròn HSE
    const hseDataRaw = <?= json_encode($hseGroupData) ?>;
    const hseLabels = hseDataRaw.map(d => 'Nhóm ' + d.group_type.replace('GROUP_', ''));
    const hseCounts = hseDataRaw.map(d => d.count);
    
    new Chart(document.getElementById('hseDonutChart'), {
        type: 'doughnut',
        data: {
            labels: hseLabels,
            datasets: [{
                data: hseCounts,
                backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899'],
                borderWidth: 1
            }]
        },
        options: {
            plugins: { legend: { position: 'bottom' } },
            cutout: '65%'
        }
    });
});
</script>
