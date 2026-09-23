<?php /** View: reports/attendance.php – BC Chấm công */ ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="panel mb-4">
    <div class="panel-header"><h3><i class="fas fa-filter"></i> Lọc Báo cáo</h3></div>
    <div class="panel-body">
        <form method="GET">
            <div style="display:flex; gap:12px; align-items:end; flex-wrap:wrap;">
                <div class="form-group"><label class="form-label-sm">Tháng</label>
                    <select name="month" class="form-control"><?php for($m=1;$m<=12;$m++): ?><option value="<?=$m?>" <?=$filters['month']==$m?'selected':''?>>Tháng <?=$m?></option><?php endfor; ?></select></div>
                <div class="form-group"><label class="form-label-sm">Năm</label>
                    <select name="year" class="form-control"><?php for($y=date('Y');$y>=2020;$y--): ?><option value="<?=$y?>" <?=$filters['year']==$y?'selected':''?>><?=$y?></option><?php endfor; ?></select></div>
                <div class="form-group"><label class="form-label-sm">Phòng ban</label>
                    <select name="department_id" class="form-control"><option value="">- Tất cả -</option>
                    <?php foreach($departments as $d): ?><option value="<?= $d['id'] ?>" <?= ($filters['department_id']==$d['id'])?'selected':'' ?>><?= $d['dept_code'] ?></option><?php endforeach; ?></select></div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Lọc</button>
            </div>
        </form>
    </div>
</div>

<?php
$totalDays = 0; $totalOt = 0; $totalNight = 0;
$otByDept = [];
foreach($data as $r) {
    $totalDays += $r['total_days'];
    $totalOt += $r['ot_hours'];
    $totalNight += $r['night_shifts'];

    $dName = $r['dept_name'] ?? 'Không xác định';
    if(!isset($otByDept[$dName])) $otByDept[$dName] = 0;
    $otByDept[$dName] += $r['ot_hours'];
}
?>

<div class="row mb-4">
    <div class="col-md-6">
        <div class="panel h-100">
            <div class="panel-header"><h4>Tổng quan Chấm công (Tháng <?= $filters['month'] ?>)</h4></div>
            <div class="panel-body" style="position: relative; height:250px;">
                <canvas id="attendanceSummaryChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="panel h-100">
            <div class="panel-header"><h4>Tổng giờ OT theo Phòng ban</h4></div>
            <div class="panel-body" style="position: relative; height:250px;">
                <canvas id="otDeptChart"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="panel">
    <div class="panel-header"><h3><i class="fas fa-clock"></i> Báo cáo Chấm công Tháng <?= $filters['month'] ?>/<?= $filters['year'] ?> (<?= count($data) ?> NV)</h3></div>
    <div class="panel-body p-0">
        <div class="table-wrapper" style="max-height: 500px; overflow-y: auto;">
            <table>
                <thead><tr><th>Mã NV</th><th>Họ tên</th><th>Phòng ban</th><th>Dự án</th><th style="text-align:center;">Tổng ngày công</th><th style="text-align:center;">Ca ngày</th><th style="text-align:center;">Ca đêm</th><th style="text-align:center;">Làm CN</th><th style="text-align:center;">Làm Lễ</th><th style="text-align:center;">Giờ OT</th><th style="text-align:center;">Phòng sạch</th></tr></thead>
                <tbody>
                    <?php foreach($data as $r): ?>
                    <tr>
                        <td><?= $r['emp_code'] ?></td>
                        <td><strong><?= $r['full_name'] ?></strong></td>
                        <td><?= $r['dept_name'] ?? '-' ?></td>
                        <td><small><?= $r['project_name'] ?? '-' ?></small></td>
                        <td style="text-align:center; font-weight:bold; color:var(--primary);"><?= $r['total_days'] ?></td>
                        <td style="text-align:center;"><?= $r['day_shifts'] ?></td>
                        <td style="text-align:center;"><?= $r['night_shifts'] ?></td>
                        <td style="text-align:center; color:var(--warning);"><?= $r['sunday_shifts'] ?></td>
                        <td style="text-align:center; color:var(--danger);"><?= $r['holiday_shifts'] ?></td>
                        <td style="text-align:center; font-weight:bold;"><?= $r['ot_hours'] ?></td>
                        <td style="text-align:center;"><?= $r['cleanroom_days'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<style>.form-label-sm{font-size:0.8rem;color:var(--text-secondary);display:block;margin-bottom:4px;}</style>

<script>
document.addEventListener("DOMContentLoaded", function() {
    var ctxSummary = document.getElementById('attendanceSummaryChart').getContext('2d');
    new Chart(ctxSummary, {
        type: 'doughnut',
        data: {
            labels: ['Tổng ngày công', 'Ca đêm', 'Giờ OT'],
            datasets: [{
                data: [<?= $totalDays ?>, <?= $totalNight ?>, <?= $totalOt ?>],
                backgroundColor: ['#3b82f6', '#f59e0b', '#ef4444'],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'right' } }
        }
    });

    var ctxOt = document.getElementById('otDeptChart').getContext('2d');
    new Chart(ctxOt, {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_keys($otByDept)) ?>,
            datasets: [{
                label: 'Tổng giờ OT',
                data: <?= json_encode(array_values($otByDept)) ?>,
                backgroundColor: '#f97316',
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } }
        }
    });
});
</script>
