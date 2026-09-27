<?php
/**
 * View: employee/retire_alerts.php
 */
?>
<div class="panel mb-4">
    <div class="panel-header"><h3><i class="fas fa-filter"></i> Lọc Cảnh báo Nghỉ hưu</h3></div>
    <div class="panel-body">
        <form method="GET" action="<?= BASE_URL ?>/employee/retireAlerts">
            <div style="display:flex; gap:12px; align-items:end; flex-wrap:wrap;">
                <div class="form-group"><label class="form-label-sm">Khoảng thời gian</label>
                    <select name="months" class="form-control">
                        <option value="6" <?= $months == 6 ? 'selected' : '' ?>>Trong vòng 6 tháng tới</option>
                        <option value="12" <?= $months == 12 ? 'selected' : '' ?>>Trong vòng 12 tháng tới</option>
                        <option value="24" <?= $months == 24 ? 'selected' : '' ?>>Trong vòng 24 tháng tới</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Lọc</button>
            </div>
        </form>
    </div>
</div>

<div class="panel">
    <div class="panel-header"><h3><i class="fas fa-user-clock"></i> Danh sách Sắp nghỉ hưu (<?= count($data) ?>)</h3></div>
    <div class="panel-body p-0">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Mã NV</th>
                        <th>Họ tên</th>
                        <th>Giới tính</th>
                        <th>Ngày sinh</th>
                        <th>Tuổi HT</th>
                        <th>Ngày nghỉ hưu</th>
                        <th>Thâm niên</th>
                        <th>Phòng ban</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data as $r): 
                        $badge = 'badge-active'; // Green
                        if ($r['months_left'] < 6) $badge = 'badge-danger'; // Red
                        elseif ($r['months_left'] <= 12) $badge = 'badge-warning'; // Yellow
                    ?>
                    <tr>
                        <td><?= $r['emp_code'] ?></td>
                        <td><strong><?= $r['full_name'] ?></strong></td>
                        <td><?= $r['gender'] == 'Male' ? 'Nam' : 'Nữ' ?></td>
                        <td><?= date('d/m/Y', strtotime($r['dob'])) ?></td>
                        <td><?= $r['current_age'] ?></td>
                        <td>
                            <span class="badge <?= $badge ?>">
                                <?= date('d/m/Y', strtotime($r['retirement_date'])) ?>
                                (Còn <?= $r['months_left'] ?> tháng)
                            </span>
                        </td>
                        <td><?= $r['seniority'] ?> năm</td>
                        <td><?= $r['dept_name'] ?></td>
                        <td>
                            <button type="button" class="btn btn-sm btn-danger" onclick="openRetireModal(<?= $r['id'] ?>, '<?= $r['full_name'] ?>')">
                                Xử lý Nghỉ hưu
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Xử lý Nghỉ hưu -->
<div id="retireModal" class="modal" style="display:none; position:fixed; z-index:1000; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.5);">
    <div class="modal-content" style="background:#fff; margin:10% auto; padding:20px; width:400px; border-radius:8px;">
        <h4>Xử lý Nghỉ hưu: <span id="retireEmpName"></span></h4>
        <form method="POST" action="<?= BASE_URL ?>/employee/processRetirement">
            <input type="hidden" name="employee_id" id="retireEmpId">
            <div class="form-group">
                <label>Số QĐ Nghỉ hưu</label>
                <input type="text" name="decision_number" class="form-control" required placeholder="VD: 123/QĐ-NH">
            </div>
            <div class="form-group">
                <label>Ngày hiệu lực</label>
                <input type="date" name="effective_date" class="form-control" required>
            </div>
            <div style="text-align:right; margin-top:20px;">
                <button type="button" class="btn btn-ghost" onclick="document.getElementById('retireModal').style.display='none'">Hủy</button>
                <button type="submit" class="btn btn-primary">Xác nhận & Cập nhật</button>
            </div>
        </form>
    </div>
</div>

<script>
function openRetireModal(id, name) {
    document.getElementById('retireEmpId').value = id;
    document.getElementById('retireEmpName').innerText = name;
    document.getElementById('retireModal').style.display = 'block';
}
</script>
<style>
.form-label-sm{font-size:0.8rem;color:var(--text-secondary);display:block;margin-bottom:4px;}
.badge-danger{background-color: var(--danger); color: white; padding: 4px 8px; border-radius: 4px;}
.badge-warning{background-color: #f59e0b; color: white; padding: 4px 8px; border-radius: 4px;}
.badge-active{background-color: var(--success); color: white; padding: 4px 8px; border-radius: 4px;}
</style>
