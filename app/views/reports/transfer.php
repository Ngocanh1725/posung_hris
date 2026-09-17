<?php /** View: reports/transfer.php – BC Thuyên chuyển */ ?>
<div class="panel mb-4">
    <div class="panel-header"><h3><i class="fas fa-filter"></i> Lọc Báo cáo</h3></div>
    <div class="panel-body">
        <form method="GET">
            <div style="display:flex; gap:12px; align-items:end; flex-wrap:wrap;">
                <div class="form-group"><label class="form-label-sm">Năm</label>
                    <select name="year" class="form-control"><?php for($y=date('Y');$y>=2020;$y--): ?><option value="<?=$y?>" <?=$filters['year']==$y?'selected':''?>><?=$y?></option><?php endfor; ?></select></div>
                <div class="form-group"><label class="form-label-sm">Trạng thái</label>
                    <select name="status" class="form-control"><option value="">- Tất cả -</option><option value="Approved" <?=$filters['status']=='Approved'?'selected':''?>>Đã điều động</option><option value="Draft" <?=$filters['status']=='Draft'?'selected':''?>>Nháp</option></select></div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Lọc</button>
            </div>
        </form>
    </div>
</div>

<div class="panel">
    <div class="panel-header"><h3><i class="fas fa-exchange-alt"></i> Lịch sử Thuyên chuyển / Điều động (<?= count($data) ?>)</h3></div>
    <div class="panel-body p-0">
        <div class="table-wrapper">
            <table>
                <thead><tr><th>Mã NV</th><th>Họ tên</th><th>Ngày QĐ</th><th>Ngày hiệu lực</th><th>Từ Đơn vị/Dự án</th><th>Đến Đơn vị/Dự án</th><th>Lý do</th><th>Trạng thái</th></tr></thead>
                <tbody>
                    <?php foreach($data as $r): ?>
                    <tr>
                        <td><?= $r['emp_code'] ?></td>
                        <td><strong><?= $r['full_name'] ?></strong></td>
                        <td><?= $r['decision_date'] ? date('d/m/Y', strtotime($r['decision_date'])) : '-' ?><br><small><?= $r['decision_number'] ?></small></td>
                        <td style="font-weight:bold; color:var(--primary);"><?= date('d/m/Y', strtotime($r['effective_date'])) ?></td>
                        <td><?= $r['from_dept'] ?? '-' ?><br><small><?= $r['from_project'] ?? '' ?></small></td>
                        <td><strong><?= $r['to_dept'] ?? '-' ?></strong><br><small><?= $r['to_project'] ?? '' ?></small></td>
                        <td><small><?= htmlspecialchars($r['reason']) ?></small></td>
                        <td><span class="badge <?= $r['status']=='Approved'?'badge-active':'badge-muted' ?>"><?= $r['status'] ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<style>.form-label-sm{font-size:0.8rem;color:var(--text-secondary);display:block;margin-bottom:4px;}</style>
