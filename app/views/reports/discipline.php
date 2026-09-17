<?php /** View: reports/discipline.php – BC Kỷ luật */ ?>
<div class="panel mb-4">
    <div class="panel-header"><h3><i class="fas fa-filter"></i> Lọc Báo cáo</h3></div>
    <div class="panel-body">
        <form method="GET">
            <div style="display:flex; gap:12px; align-items:end; flex-wrap:wrap;">
                <div class="form-group"><label class="form-label-sm">Năm</label>
                    <select name="year" class="form-control"><?php for($y=date('Y');$y>=2020;$y--): ?><option value="<?=$y?>" <?=$filters['year']==$y?'selected':''?>><?=$y?></option><?php endfor; ?></select></div>
                <div class="form-group">
                    <label style="margin-top:20px; font-weight:bold; color:var(--danger);"><input type="checkbox" name="safety_only" value="1" <?=$filters['safety_only']?'checked':''?>> Chỉ hiện vi phạm HSE</label>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Lọc</button>
                <a href="<?= BASE_URL ?>/report/printReport/discipline" target="_blank" class="btn btn-ghost"><i class="fas fa-print"></i> In BC</a>
            </div>
        </form>
    </div>
</div>

<div class="panel">
    <div class="panel-header"><h3><i class="fas fa-gavel"></i> Danh sách Kỷ luật (<?= count($data) ?>)</h3></div>
    <div class="panel-body p-0">
        <div class="table-wrapper">
            <table>
                <thead><tr><th>Ngày QĐ</th><th>Số QĐ</th><th>Mã NV</th><th>Họ tên</th><th>Phòng ban</th><th>Hình thức</th><th>Nội dung</th><th>Vi phạm HSE</th><th style="text-align:right;">Số tiền (VNĐ)</th></tr></thead>
                <tbody>
                    <?php foreach($data as $r): ?>
                    <tr class="<?= $r['is_safety_violation'] ? 'row-danger' : '' ?>">
                        <td><?= date('d/m/Y', strtotime($r['decision_date'])) ?></td>
                        <td><small><?= $r['decision_number'] ?></small></td>
                        <td><?= $r['emp_code'] ?></td>
                        <td><strong><?= $r['full_name'] ?></strong></td>
                        <td><?= $r['dept_name'] ?? '-' ?></td>
                        <td><span class="badge badge-resigned"><?= $r['discipline_form'] ?? 'Kỷ luật' ?></span></td>
                        <td><?= htmlspecialchars($r['title']) ?></td>
                        <td style="text-align:center;">
                            <?php if($r['is_safety_violation']): ?><span class="badge" style="background:var(--danger);color:#fff;">HSE Blacklist</span><?php else: ?>-<?php endif; ?>
                        </td>
                        <td style="text-align:right;font-weight:bold;"><?= number_format($r['amount'],0,',','.') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<style>.form-label-sm{font-size:0.8rem;color:var(--text-secondary);display:block;margin-bottom:4px;} .row-danger{background:rgba(239,68,68,0.05)!important;}</style>
