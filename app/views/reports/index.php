<!-- Báo cáo & Biểu mẫu -->

<!-- Thống kê Quân số theo Loại nhân sự -->
<div class="stats-grid" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(300px, 1fr)); gap:20px; margin-bottom:24px;">
    
    <!-- Panel 1: Theo loại nhân sự -->
    <div class="panel">
        <div class="panel-header">
            <h3><i class="fas fa-chart-pie"></i> Quân số theo Loại NV</h3>
        </div>
        <div class="panel-body">
            <?php if (empty($headcountByType)): ?>
                <p style="color:var(--text-muted);">Chưa có dữ liệu.</p>
            <?php else: ?>
                <div class="table-wrapper">
                    <table>
                        <thead><tr><th>Loại</th><th>Trạng thái</th><th style="text-align:center;">Số lượng</th></tr></thead>
                        <tbody>
                        <?php 
                        $typeLabels = [
                            'Direct_Worker' => 'CN Trực tiếp',
                            'Indirect_Worker' => 'CN Gián tiếp',
                            'Office_Staff' => 'Văn phòng',
                            'Expat' => 'Chuyên gia',
                            'Intern' => 'Thực tập'
                        ];
                        $statusLabels = [
                            'Active' => 'Đang làm',
                            'Probation' => 'Thử việc',
                            'Resigned' => 'Nghỉ việc',
                            'Retired' => 'Hưu trí',
                            'Blacklisted' => 'Blacklist',
                            'Suspended' => 'Tạm nghỉ'
                        ];
                        $total = 0;
                        foreach ($headcountByType as $row): 
                            $total += (int)$row['cnt'];
                        ?>
                        <tr>
                            <td><?= $typeLabels[$row['employee_type']] ?? $row['employee_type'] ?></td>
                            <td>
                                <?php
                                $badgeMap = ['Active'=>'badge-active','Probation'=>'badge-probation','Resigned'=>'badge-resigned','Blacklisted'=>'badge-resigned'];
                                ?>
                                <span class="badge <?= $badgeMap[$row['status']] ?? '' ?>"><?= $statusLabels[$row['status']] ?? $row['status'] ?></span>
                            </td>
                            <td style="text-align:center;"><strong><?= $row['cnt'] ?></strong></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr style="background:rgba(99,102,241,0.1); font-weight:700;">
                                <td colspan="2">TỔNG</td>
                                <td style="text-align:center;"><?= $total ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Panel 2: Theo phòng ban -->
    <div class="panel">
        <div class="panel-header">
            <h3><i class="fas fa-building"></i> Quân số theo Phòng ban</h3>
        </div>
        <div class="panel-body">
            <?php if (empty($headcountByDept)): ?>
                <p style="color:var(--text-muted);">Chưa có dữ liệu.</p>
            <?php else: ?>
                <div class="table-wrapper">
                    <table>
                        <thead><tr><th>Mã PB</th><th>Phòng ban</th><th style="text-align:center;">Quân số</th></tr></thead>
                        <tbody>
                        <?php 
                        $totalDept = 0;
                        foreach ($headcountByDept as $row): 
                            $totalDept += (int)$row['cnt'];
                        ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($row['dept_code']) ?></strong></td>
                            <td><?= htmlspecialchars($row['dept_name']) ?></td>
                            <td style="text-align:center;">
                                <?php if ((int)$row['cnt'] > 0): ?>
                                    <span class="badge badge-active"><?= $row['cnt'] ?></span>
                                <?php else: ?>
                                    <span style="color:var(--text-muted);">0</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr style="background:rgba(99,102,241,0.1); font-weight:700;">
                                <td colspan="2">TỔNG</td>
                                <td style="text-align:center;"><?= $totalDept ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Panel 3: Theo dự án -->
<div class="panel" style="margin-bottom:24px;">
    <div class="panel-header">
        <h3><i class="fas fa-hard-hat"></i> Quân số theo Dự án (Đang triển khai)</h3>
    </div>
    <div class="panel-body">
        <?php if (empty($headcountByProject)): ?>
            <p style="color:var(--text-muted);">Chưa có dữ liệu.</p>
        <?php else: ?>
            <div class="table-wrapper">
                <table>
                    <thead><tr><th>Mã DA</th><th>Tên Dự án</th><th style="text-align:center;">Quân số</th></tr></thead>
                    <tbody>
                    <?php 
                    $totalProj = 0;
                    foreach ($headcountByProject as $row): 
                        $totalProj += (int)$row['cnt'];
                    ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($row['project_code']) ?></strong></td>
                        <td><?= htmlspecialchars($row['project_name']) ?></td>
                        <td style="text-align:center;">
                            <span class="badge badge-active"><?= $row['cnt'] ?></span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr style="background:rgba(99,102,241,0.1); font-weight:700;">
                            <td colspan="2">TỔNG</td>
                            <td style="text-align:center;"><?= $totalProj ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Panel 4: In Mẫu 2C -->
<div class="panel">
    <div class="panel-header">
        <h3><i class="fas fa-print"></i> In Sơ yếu Lý lịch (Mẫu 2C/TCTW-98)</h3>
    </div>
    <div class="panel-body">
        <p style="color:var(--text-secondary); margin-bottom:16px;">
            Chọn nhân viên để in Sơ yếu Lý lịch theo Mẫu 2C/TCTW-98 của Tổ chức Trung ương.
        </p>
        <div style="display:flex; gap:12px; align-items:flex-end; flex-wrap:wrap;">
            <div class="form-group" style="flex:1; min-width:250px;">
                <label style="display:block; margin-bottom:6px; font-weight:500; font-size:0.85rem; color:var(--text-secondary);">Chọn nhân viên</label>
                <select id="print2cSelect" style="width:100%; padding:10px 14px; border-radius:8px; border:1px solid rgba(255,255,255,0.1); background:rgba(255,255,255,0.04); color:var(--text-primary); font-size:0.9rem;">
                    <option value="">-- Chọn nhân viên --</option>
                    <?php foreach ($employees as $emp): ?>
                        <option value="<?= $emp['id'] ?>"><?= htmlspecialchars($emp['emp_code'] . ' – ' . $emp['full_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button onclick="print2C()" class="btn btn-primary" style="padding:10px 20px; border-radius:8px; border:none; cursor:pointer; font-weight:600; background:var(--primary); color:#fff;">
                <i class="fas fa-print"></i> In Mẫu 2C
            </button>
        </div>
    </div>
</div>

<script>
function print2C() {
    var sel = document.getElementById('print2cSelect');
    var empId = sel.value;
    if (!empId) { alert('Vui lòng chọn nhân viên!'); return; }
    window.open('<?= BASE_URL ?>/employee/print2c/' + empId, '_blank');
}
</script>
