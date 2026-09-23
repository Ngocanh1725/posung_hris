<?php /** View: reports/headcount.php – BC Quân số */ ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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
                <a href="<?= BASE_URL ?>/report/printReport/headcount" target="_blank" class="btn btn-ghost"><i class="fas fa-print"></i> In BC (PDF/Print)</a>
            </div>
        </form>
    </div>
</div>

<!-- THỐNG KÊ VÀ BIỂU ĐỒ -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="panel h-100">
            <div class="panel-header"><h4>Phân bổ theo Giới tính</h4></div>
            <div class="panel-body" style="position: relative; height:250px;">
                <canvas id="genderChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="panel h-100">
            <div class="panel-header"><h4>Phân bổ theo Khối / Bộ phận</h4></div>
            <div class="panel-body" style="position: relative; height:250px;">
                <canvas id="deptChart"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="panel">
    <div class="panel-header"><h3><i class="fas fa-users"></i> Danh sách Nhân sự (<?= count($data) ?>)</h3></div>
    <div class="panel-body p-0">
        <div class="table-wrapper" style="max-height: 500px; overflow-y: auto;">
            <table>
                <thead><tr><th>Mã NV</th><th>Họ tên</th><th>Giới tính</th><th>Ngày sinh</th><th>Chức vụ</th><th>Phòng ban</th><th>Dự án</th><th>Ngày vào</th></tr></thead>
                <tbody>
                    <?php 
                    $genderStats = ['Nam' => 0, 'Nữ' => 0];
                    $deptStats = [];
                    foreach($data as $r): 
                        // Thu thập data vẽ biểu đồ
                        $g = $r['gender']=='Male'?'Nam':'Nữ';
                        $genderStats[$g]++;
                        $d = $r['dept_name'] ?? 'Không xác định';
                        if(!isset($deptStats[$d])) $deptStats[$d] = 0;
                        $deptStats[$d]++;
                    ?>
                    <tr>
                        <td><?= $r['emp_code'] ?></td>
                        <td><strong><?= $r['full_name'] ?></strong><br><small><?= $r['employee_type'] ?></small></td>
                        <td><?= $g ?></td>
                        <td><?= $r['dob'] ? date('d/m/Y', strtotime($r['dob'])) : '-' ?></td>
                        <td>
                            <?= $r['pos_title'] ?? '-' ?>
                            <?php if(!empty($r['highest_degree'])): ?>
                                <br><small class="text-muted"><?= $r['highest_degree'] ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?= $d ?></td>
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

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Biểu đồ Giới tính (Donut)
    var ctxGender = document.getElementById('genderChart').getContext('2d');
    new Chart(ctxGender, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode(array_keys($genderStats)) ?>,
            datasets: [{
                data: <?= json_encode(array_values($genderStats)) ?>,
                backgroundColor: ['#3b82f6', '#f43f5e'],
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

    // Biểu đồ Phòng ban (Bar)
    var ctxDept = document.getElementById('deptChart').getContext('2d');
    new Chart(ctxDept, {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_keys($deptStats)) ?>,
            datasets: [{
                label: 'Số lượng nhân sự',
                data: <?= json_encode(array_values($deptStats)) ?>,
                backgroundColor: '#10b981',
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            }
        }
    });
});
</script>
