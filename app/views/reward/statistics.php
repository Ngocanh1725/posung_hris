<?php
/**
 * View: reward/statistics.php
 * Thống kê Khen thưởng / Kỷ luật theo năm
 */
?>

<!-- Chọn năm -->
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
    <div style="display:flex; gap:8px; align-items:center;">
        <label style="font-weight:600;">Năm:</label>
        <select onchange="location.href='<?= BASE_URL ?>/reward/statistics?year='+this.value" class="form-control" style="width:120px;">
            <?php for($y = date('Y'); $y >= date('Y')-5; $y--): ?>
                <option value="<?= $y ?>" <?= $y == $year ? 'selected' : '' ?>><?= $y ?></option>
            <?php endfor; ?>
        </select>
    </div>
    <a href="<?= BASE_URL ?>/reward" class="btn btn-ghost"><i class="fas fa-arrow-left"></i> Quay lại</a>
</div>

<!-- KPI Cards -->
<div class="kpi-row" style="margin-bottom:24px;">
    <?php
    $totalReward = 0; $amountReward = 0;
    $totalDiscipline = 0; $amountDiscipline = 0;
    foreach ($stats['by_type'] as $t) {
        if ($t['type'] === 'Reward') { $totalReward = (int)$t['total']; $amountReward = (float)$t['total_amount']; }
        if ($t['type'] === 'Discipline') { $totalDiscipline = (int)$t['total']; $amountDiscipline = (float)$t['total_amount']; }
    }
    ?>
    <div class="kpi-card">
        <div class="kpi-icon" style="background:linear-gradient(135deg,#10b981,#059669);"><i class="fas fa-trophy"></i></div>
        <div class="kpi-body">
            <div class="kpi-value"><?= $totalReward ?></div>
            <div class="kpi-label">Khen thưởng</div>
        </div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon" style="background:linear-gradient(135deg,#f59e0b,#d97706);"><i class="fas fa-coins"></i></div>
        <div class="kpi-body">
            <div class="kpi-value"><?= number_format($amountReward, 0, ',', '.') ?></div>
            <div class="kpi-label">Tổng tiền thưởng (VNĐ)</div>
        </div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon" style="background:linear-gradient(135deg,#ef4444,#dc2626);"><i class="fas fa-gavel"></i></div>
        <div class="kpi-body">
            <div class="kpi-value"><?= $totalDiscipline ?></div>
            <div class="kpi-label">Kỷ luật</div>
        </div>
    </div>
    <div class="kpi-card" style="<?= $stats['hse_violations'] > 0 ? 'border-color:rgba(239,68,68,0.4);' : '' ?>">
        <div class="kpi-icon" style="background:linear-gradient(135deg,#7c3aed,#6d28d9);"><i class="fas fa-skull-crossbones"></i></div>
        <div class="kpi-body">
            <div class="kpi-value" style="<?= $stats['hse_violations'] > 0 ? 'color:var(--danger);' : '' ?>"><?= $stats['hse_violations'] ?></div>
            <div class="kpi-label">Vi phạm HSE</div>
        </div>
    </div>
</div>

<!-- Biểu đồ -->
<div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:24px;">
    <!-- Biểu đồ theo tháng -->
    <div class="panel">
        <div class="panel-header"><h3><i class="fas fa-chart-bar"></i> KT/KL theo Tháng</h3></div>
        <div class="panel-body" style="min-height:300px;"><canvas id="monthlyChart"></canvas></div>
    </div>
    <!-- Biểu đồ theo phòng ban -->
    <div class="panel">
        <div class="panel-header"><h3><i class="fas fa-chart-pie"></i> KT/KL theo Phòng ban</h3></div>
        <div class="panel-body" style="min-height:300px;"><canvas id="deptChart"></canvas></div>
    </div>
</div>

<!-- Bảng chi tiết hình thức -->
<div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
    <!-- Hình thức Khen thưởng -->
    <div class="panel">
        <div class="panel-header"><h3><i class="fas fa-award"></i> Theo Hình thức Khen thưởng</h3></div>
        <div class="panel-body">
            <?php if (empty($stats['reward_forms'])): ?>
                <p style="color:var(--text-muted);">Chưa có dữ liệu.</p>
            <?php else: ?>
                <div class="table-wrapper">
                    <table>
                        <thead><tr><th>Hình thức</th><th style="text-align:center;">Số lượng</th><th style="text-align:right;">Tổng tiền</th></tr></thead>
                        <tbody>
                        <?php foreach ($stats['reward_forms'] as $rf): ?>
                            <tr>
                                <td><?= h($rf['reward_form']) ?></td>
                                <td style="text-align:center;"><span class="badge badge-active"><?= $rf['total'] ?></span></td>
                                <td style="text-align:right;"><?= number_format($rf['total_amount'], 0, ',', '.') ?> đ</td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <!-- Hình thức Kỷ luật -->
    <div class="panel">
        <div class="panel-header"><h3><i class="fas fa-ban"></i> Theo Hình thức Kỷ luật</h3></div>
        <div class="panel-body">
            <?php if (empty($stats['discipline_forms'])): ?>
                <p style="color:var(--text-muted);">Chưa có dữ liệu.</p>
            <?php else: ?>
                <div class="table-wrapper">
                    <table>
                        <thead><tr><th>Hình thức</th><th style="text-align:center;">Số lượng</th><th style="text-align:center;">Vi phạm HSE</th></tr></thead>
                        <tbody>
                        <?php foreach ($stats['discipline_forms'] as $df): ?>
                            <tr>
                                <td><?= h($df['discipline_form']) ?></td>
                                <td style="text-align:center;"><span class="badge badge-resigned"><?= $df['total'] ?></span></td>
                                <td style="text-align:center;">
                                    <?php if ((int)$df['safety_count'] > 0): ?>
                                        <span class="badge" style="background:var(--danger);color:#fff;"><?= $df['safety_count'] ?></span>
                                    <?php else: ?>
                                        <span style="color:var(--text-muted);">0</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    // Monthly Chart
    const monthlyData = <?= json_encode($stats['by_month'] ?? []) ?>;
    const months = Array.from({length:12}, (_,i) => 'T' + (i+1));
    const rewardByMonth = new Array(12).fill(0);
    const disciplineByMonth = new Array(12).fill(0);
    monthlyData.forEach(d => {
        const idx = parseInt(d.month) - 1;
        if (d.type === 'Reward') rewardByMonth[idx] = parseInt(d.total);
        else disciplineByMonth[idx] = parseInt(d.total);
    });

    new Chart(document.getElementById('monthlyChart'), {
        type: 'bar',
        data: {
            labels: months,
            datasets: [
                { label: 'Khen thưởng', data: rewardByMonth, backgroundColor: '#10b981' },
                { label: 'Kỷ luật', data: disciplineByMonth, backgroundColor: '#ef4444' }
            ]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'bottom' } },
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });

    // Department Chart
    const deptData = <?= json_encode($stats['by_department'] ?? []) ?>;
    const deptLabels = [...new Set(deptData.map(d => d.dept_name || 'Chưa phân bổ'))];
    const deptReward = deptLabels.map(name => {
        const row = deptData.find(d => (d.dept_name || 'Chưa phân bổ') === name && d.type === 'Reward');
        return row ? parseInt(row.total) : 0;
    });
    const deptDiscipline = deptLabels.map(name => {
        const row = deptData.find(d => (d.dept_name || 'Chưa phân bổ') === name && d.type === 'Discipline');
        return row ? parseInt(row.total) : 0;
    });

    new Chart(document.getElementById('deptChart'), {
        type: 'bar',
        data: {
            labels: deptLabels,
            datasets: [
                { label: 'Khen thưởng', data: deptReward, backgroundColor: '#10b981' },
                { label: 'Kỷ luật', data: deptDiscipline, backgroundColor: '#ef4444' }
            ]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            plugins: { legend: { position: 'bottom' } },
            scales: { x: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });
});
</script>
