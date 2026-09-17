<?php /** View: reports/payroll_report.php – BC Tiền lương */ ?>
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

<div class="panel">
    <div class="panel-header"><h3><i class="fas fa-money-bill-wave"></i> Báo cáo Lương Tháng <?= $filters['month'] ?>/<?= $filters['year'] ?> (<?= count($data) ?> NV)</h3></div>
    <div class="panel-body p-0">
        <div class="table-wrapper">
            <table>
                <thead><tr><th>Mã NV</th><th>Họ tên</th><th>Phòng ban</th><th>Dự án</th><th style="text-align:right;">Lương cơ bản</th><th style="text-align:right;">Thưởng/Phụ cấp</th><th style="text-align:right;">Trừ (BH/Thuế/KL)</th><th style="text-align:right;">Thực lĩnh</th></tr></thead>
                <tbody>
                    <?php 
                    $totalBase = 0; $totalAdd = 0; $totalDed = 0; $totalNet = 0;
                    foreach($data as $r): 
                        $totalBase += $r['base_salary'];
                        $totalAdd += $r['total_allowance'] + $r['total_reward'] + $r['ot_salary'];
                        $totalDed += $r['total_insurance'] + $r['pit_tax'] + $r['total_discipline'];
                        $totalNet += $r['net_salary'];
                    ?>
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
