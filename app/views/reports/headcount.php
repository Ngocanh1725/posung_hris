<?php /** View: reports/headcount.php – BC Quân số */ ?>
<div class="panel mb-4">
    <div class="panel-header"><h3><i class="fas fa-filter"></i> Lọc Báo cáo</h3></div>
    <div class="panel-body">
        <form method="GET">
            <div style="display:flex; gap:12px; align-items:end; flex-wrap:wrap;">
                <div class="form-group"><label class="form-label-sm">Phòng ban</label>
                    <select name="department_id" class="form-control"><option value="">- Tất cả -</option>
                    <?php foreach($departments as $d): ?><option value="<?= $d['id'] ?>" <?= ($filters['department_id']==$d['id'])?'selected':'' ?>><?= $d['dept_code'] ?></option><?php endforeach; ?></select></div>
                <div class="form-group"><label class="form-label-sm">Dự án</label>
                    <select name="project_id" class="form-control"><option value="">- Tất cả -</option>
                    <?php foreach($projects as $p): ?><option value="<?= $p->id ?>" <?= ($filters['project_id']==$p->id)?'selected':'' ?>><?= $p->project_code ?? $p->project_name ?></option><?php endforeach; ?></select></div>
                <div class="form-group"><label class="form-label-sm">Loại NV</label>
                    <select name="employee_type" class="form-control"><option value="">- Tất cả -</option><option value="Office" <?= $filters['employee_type']=='Office'?'selected':'' ?>>Văn phòng</option><option value="Site_Engineer" <?= $filters['employee_type']=='Site_Engineer'?'selected':'' ?>>Kỹ sư</option><option value="Direct_Worker" <?= $filters['employee_type']=='Direct_Worker'?'selected':'' ?>>Công nhân</option><option value="Expat" <?= $filters['employee_type']=='Expat'?'selected':'' ?>>Expat</option></select></div>
                <div class="form-group"><label class="form-label-sm">Trạng thái</label>
                    <select name="status" class="form-control"><option value="Active" <?= $filters['status']=='Active'?'selected':'' ?>>Đang làm việc</option><option value="Resigned" <?= $filters['status']=='Resigned'?'selected':'' ?>>Đã nghỉ</option></select></div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Lọc</button>
                <a href="<?= BASE_URL ?>/report/printReport/headcount" target="_blank" class="btn btn-ghost"><i class="fas fa-print"></i> In BC</a>
            </div>
        </form>
    </div>
</div>

<div class="panel">
    <div class="panel-header"><h3><i class="fas fa-users"></i> Danh sách Nhân sự (<?= count($data) ?>)</h3></div>
    <div class="panel-body p-0">
        <div class="table-wrapper">
            <table>
                <thead><tr><th>Mã NV</th><th>Họ tên</th><th>Giới tính</th><th>Ngày sinh</th><th>Chức vụ</th><th>Phòng ban</th><th>Dự án</th><th>Ngày vào</th></tr></thead>
                <tbody>
                    <?php foreach($data as $r): ?>
                    <tr>
                        <td><?= $r['emp_code'] ?></td>
                        <td><strong><?= $r['full_name'] ?></strong><br><small><?= $r['employee_type'] ?></small></td>
                        <td><?= $r['gender']=='Male'?'Nam':'Nữ' ?></td>
                        <td><?= $r['dob'] ? date('d/m/Y', strtotime($r['dob'])) : '-' ?></td>
                        <td><?= $r['pos_title'] ?? '-' ?></td>
                        <td><?= $r['dept_name'] ?? '-' ?></td>
                        <td><?= $r['project_name'] ?? '-' ?></td>
                        <td><?= $r['join_date'] ? date('d/m/Y', strtotime($r['join_date'])) : '-' ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<style>.form-label-sm{font-size:0.8rem;color:var(--text-secondary);display:block;margin-bottom:4px;}</style>
