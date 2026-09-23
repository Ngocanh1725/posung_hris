<?php /** View: reports/payroll_report.php – BC Tiền lương */ ?>
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
$totalBase = 0; $totalAdd = 0; $totalDed = 0; $totalNet = 0;
$costByDept = [];
foreach($data as $r) {
    $totalBase += $r['base_salary'];
    $add = $r['total_allowance'] + $r['total_reward'] + $r['ot_salary'];
    $totalAdd += $add;
    $ded = $r['total_insurance'] + $r['pit_tax'] + $r['total_discipline'];
    $totalDed += $ded;
    $totalNet += $r['net_salary'];

    $dName = $r['dept_name'] ?? 'Không xác định';
    if(!isset($costByDept[$dName])) $costByDept[$dName] = 0;
    $costByDept[$dName] += $r['net_salary'];
}
?>

<div class="row mb-4">
    <div class="col-md-6">
        <div class="panel h-100">
            <div class="panel-header"><h4>Cơ cấu Chi phí Lương (Toàn Công ty)</h4></div>
            <div class="panel-body" style="position: relative; height:300px;">
                <canvas id="costStructureChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="panel h-100">
            <div class="panel-header"><h4>Quỹ lương theo Phòng ban (Thực lĩnh)</h4></div>
            <div class="panel-body" style="position: relative; height:300px;">
                <canvas id="deptCostChart"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="panel">
    <div class="panel-header"><h3><i class="fas fa-money-bill-wave"></i> Báo cáo Lương Tháng <?= $filters['month'] ?>/<?= $filters['year'] ?> (<?= count($data) ?> NV)</h3></div>
    <div class="panel-body p-0">
        <div class="table-wrapper" style="max-height: 500px; overflow-y: auto;">
            <table>
                <thead><tr><th>Mã NV</th><th>Họ tên</th><th>Phòng ban</th><th>Dự án</th><th style="text-align:right;">Lương cơ bản</th><th style="text-align:right;">Thưởng/Phụ cấp</th><th style="text-align:right;">Trừ (BH/Thuế/KL)</th><th style="text-align:right;">Thực lĩnh</th></tr></thead>
                <tbody>
                    <?php foreach($data as $r): ?>
                    <tr>
                        <td><?= $r['emp_code'] ?></td>
                        <td><strong><?= $r['full_name'] ?></strong></td>
                        <td><small><?= $r['dept_name'] ?? '-' ?></small></td>
                        <td><small><?= $r['project_name'] ?? '-' ?></small></td>
                        <td style="text-align:right;"><?= number_format($r['base_salary'],0,',','.') ?></td>
                        <td style="text-align:right; color:var(--success);"><?= number_format($r['total_allowance'] + $r['total_reward'] + $r['ot_salary'],0,',','.') ?></td>
                        <td style="text-align:right; color:var(--danger);"><?= number_format($r['total_insurance'] + $r['pit_tax'] + $r['total_discipline'],0,',','.') ?></td>
                        <td style="text-align:right; font-weight:bold; color:var(--primary);"><?= number_format($r['net_salary'],0,',','.') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <?php if(!empty($data)): ?>
                <tfoot>
                    <tr style="background:#f1f5f9; font-weight:bold;">
                        <td colspan="4" style="text-align:right;">TỔNG CỘNG:</td>
                        <td style="text-align:right;"><?= number_format($totalBase,0,',','.') ?></td>
                        <td style="text-align:right; color:var(--success);"><?= number_format($totalAdd,0,',','.') ?></td>
                        <td style="text-align:right; color:var(--danger);"><?= number_format($totalDed,0,',','.') ?></td>
                        <td style="text-align:right; color:var(--primary); font-size:1.1rem;"><?= number_format($totalNet,0,',','.') ?> đ</td>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>
</div>
<style>.form-label-sm{font-size:0.8rem;color:var(--text-secondary);display:block;margin-bottom:4px;}</style>

<script>
document.addEventListener("DOMContentLoaded", function() {
    var ctxStructure = document.getElementById('costStructureChart').getContext('2d');
    new Chart(ctxStructure, {
        type: 'pie',
        data: {
            labels: ['Lương cơ bản', 'Thưởng/Phụ cấp/OT', 'Các khoản trừ'],
            datasets: [{
                data: [<?= $totalBase ?>, <?= $totalAdd ?>, <?= $totalDed ?>],
                backgroundColor: ['#3b82f6', '#10b981', '#ef4444'],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });

    var ctxDept = document.getElementById('deptCostChart').getContext('2d');
    new Chart(ctxDept, {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_keys($costByDept)) ?>,
            datasets: [{
                label: 'Quỹ lương (VNĐ)',
                data: <?= json_encode(array_values($costByDept)) ?>,
                backgroundColor: '#8b5cf6',
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
