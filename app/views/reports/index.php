<?php /** View: reports/index.php – Business Intelligence (BI) Dashboard */ ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-0"><i class="fas fa-chart-line text-primary"></i> Business Intelligence (BI) Dashboard</h2>
        <p class="text-muted mb-0">Hệ thống phân tích dữ liệu quản trị tự động cập nhật theo thời gian thực.</p>
    </div>
    <div>
        <a href="<?= BASE_URL ?>/report/headcount" class="btn btn-outline-primary btn-sm"><i class="fas fa-users"></i> Báo cáo Quân số</a>
        <a href="<?= BASE_URL ?>/report/reward" class="btn btn-outline-success btn-sm"><i class="fas fa-medal"></i> Báo cáo Khen thưởng/Kỷ luật</a>
    </div>
</div>

<div class="row">
    <!-- Chart 1: Donut Chart - Cost Allocation -->
    <div class="col-md-4 mb-4">
        <div class="panel h-100">
            <div class="panel-header">
                <h4 class="mb-0"><i class="fas fa-coins text-warning"></i> Phân bổ Quỹ Lương</h4>
            </div>
            <div class="panel-body">
                <div style="position: relative; height:250px; width:100%">
                    <canvas id="costChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart 2: Line Chart - Payroll vs Revenue -->
    <div class="col-md-8 mb-4">
        <div class="panel h-100">
            <div class="panel-header">
                <h4 class="mb-0"><i class="fas fa-chart-area text-success"></i> Biến động Quỹ Lương vs Doanh Thu (12 Tháng)</h4>
            </div>
            <div class="panel-body">
                <div style="position: relative; height:250px; width:100%">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Chart 3: Gauges - Compliance -->
    <div class="col-md-6 mb-4">
        <div class="panel h-100">
            <div class="panel-header">
                <h4 class="mb-0"><i class="fas fa-shield-alt text-info"></i> Tỷ lệ Tuân thủ Pháp lý & Chứng chỉ</h4>
            </div>
            <div class="panel-body d-flex justify-content-around align-items-center text-center">
                <div style="width: 30%;">
                    <canvas id="gaugeHse"></canvas>
                    <p class="mt-2 font-weight-bold">Thẻ An Toàn HSE</p>
                </div>
                <div style="width: 30%;">
                    <canvas id="gaugeWelding"></canvas>
                    <p class="mt-2 font-weight-bold">Thợ Hàn 6G</p>
                </div>
                <div style="width: 30%;">
                    <canvas id="gaugeExpat"></canvas>
                    <p class="mt-2 font-weight-bold">Visa Expat</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart 4: Turnover Rate -->
    <div class="col-md-6 mb-4">
        <div class="panel h-100">
            <div class="panel-header">
                <h4 class="mb-0"><i class="fas fa-running text-danger"></i> Tỷ lệ Nghỉ việc (Turnover Rate)</h4>
            </div>
            <div class="panel-body">
                <div style="position: relative; height:200px; width:100%">
                    <canvas id="turnoverChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // 1. Donut Chart - Cost Allocation
    const ctxCost = document.getElementById('costChart').getContext('2d');
    new Chart(ctxCost, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode($biData['cost_allocation']['labels']) ?>,
            datasets: [{
                data: <?= json_encode($biData['cost_allocation']['data']) ?>,
                backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4'],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'right' }
            }
        }
    });

    // 2. Line Chart - Trend
    const ctxTrend = document.getElementById('trendChart').getContext('2d');
    new Chart(ctxTrend, {
        type: 'line',
        data: {
            labels: <?= json_encode($biData['salary_vs_revenue']['labels']) ?>,
            datasets: [
                {
                    label: 'Quỹ lương (VNĐ)',
                    data: <?= json_encode($biData['salary_vs_revenue']['payroll']) ?>,
                    borderColor: '#ef4444',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Doanh thu thi công (Dự kiến)',
                    data: <?= json_encode($biData['salary_vs_revenue']['revenue']) ?>,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    tension: 0.4,
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            }
        }
    });

    // 3. Gauges (Doughnut nửa)
    const gaugeOptions = {
        rotation: -90,
        circumference: 180,
        responsive: true,
        maintainAspectRatio: false,
        plugins: { tooltip: { enabled: false } },
        cutout: '75%'
    };

    const createGauge = (ctx, value, color) => {
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Đạt', 'Chưa đạt'],
                datasets: [{
                    data: [value, 100 - value],
                    backgroundColor: [color, '#e2e8f0'],
                    borderWidth: 0
                }]
            },
            options: gaugeOptions,
            plugins: [{
                id: 'textCenter',
                beforeDraw: function(chart) {
                    var width = chart.width, height = chart.height, ctx = chart.ctx;
                    ctx.restore();
                    var fontSize = (height / 60).toFixed(2);
                    ctx.font = "bold " + fontSize + "em sans-serif";
                    ctx.textBaseline = "middle";
                    var text = value + "%",
                        textX = Math.round((width - ctx.measureText(text).width) / 2),
                        textY = height - 15;
                    ctx.fillText(text, textX, textY);
                    ctx.save();
                }
            }]
        });
    };

    createGauge(document.getElementById('gaugeHse').getContext('2d'), <?= $biData['compliance']['hse'] ?>, '#10b981');
    createGauge(document.getElementById('gaugeWelding').getContext('2d'), <?= $biData['compliance']['welding'] ?>, '#3b82f6');
    createGauge(document.getElementById('gaugeExpat').getContext('2d'), <?= $biData['compliance']['expat'] ?>, '#f59e0b');

    // 4. Bar Chart - Turnover
    const ctxTurnover = document.getElementById('turnoverChart').getContext('2d');
    new Chart(ctxTurnover, {
        type: 'bar',
        data: {
            labels: <?= json_encode($biData['turnover']['labels']) ?>,
            datasets: [{
                label: 'Tỷ lệ Nghỉ việc (%)',
                data: <?= json_encode($biData['turnover']['data']) ?>,
                backgroundColor: 'rgba(239, 68, 68, 0.7)'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true, max: 100 }
            }
        }
    });
});
</script>
