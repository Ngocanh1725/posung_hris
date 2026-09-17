<?php /** View: reports/retirement.php – BC Nghỉ hưu */ ?>
<div class="panel mb-4">
    <div class="panel-header"><h3><i class="fas fa-filter"></i> Lọc Báo cáo</h3></div>
    <div class="panel-body">
        <form method="GET">
            <div style="display:flex; gap:12px; align-items:end; flex-wrap:wrap;">
                <div class="form-group"><label class="form-label-sm">Thời gian hưu trí (Trong vòng)</label>
                    <select name="months" class="form-control">
                        <option value="6" <?=$filters['within_months']==6?'selected':''?>>6 tháng tới</option>
                        <option value="12" <?=$filters['within_months']==12?'selected':''?>>12 tháng tới (1 năm)</option>
                        <option value="24" <?=$filters['within_months']==24?'selected':''?>>24 tháng tới (2 năm)</option>
                        <option value="60" <?=$filters['within_months']==60?'selected':''?>>60 tháng tới (5 năm)</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Lọc</button>
            </div>
        </form>
    </div>
</div>

<div class="panel">
    <div class="panel-header"><h3><i class="fas fa-user-clock"></i> Danh sách Sắp Hưu trí (<?= count($data) ?>)</h3></div>
    <div class="panel-body p-0">
        <div class="table-wrapper">
            <table>
                <thead><tr><th>Mã NV</th><th>Họ tên</th><th>Giới tính</th><th>Ngày sinh</th><th>Tuổi</th><th>Ngày dự kiến hưu</th><th>Phòng ban</th><th>Ngày vào</th></tr></thead>
                <tbody>
                    <?php foreach($data as $r): ?>
                    <tr>
                        <td><?= $r['emp_code'] ?></td>
                        <td><strong><?= $r['full_name'] ?></strong></td>
                        <td><?= $r['gender']=='Male'?'Nam':'Nữ' ?></td>
                        <td><?= date('d/m/Y', strtotime($r['dob'])) ?></td>
                        <td style="font-weight:bold; color:var(--warning);"><?= $r['current_age'] ?></td>
                        <td style="font-weight:bold; color:var(--danger);"><?= date('d/m/Y', strtotime($r['retirement_date'])) ?></td>
                        <td><?= $r['dept_name'] ?? '-' ?></td>
                        <td><?= $r['join_date'] ? date('d/m/Y', strtotime($r['join_date'])) : '-' ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<style>.form-label-sm{font-size:0.8rem;color:var(--text-secondary);display:block;margin-bottom:4px;}</style>
