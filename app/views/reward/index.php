<?php
/**
 * ============================================================
 *  View: reward/index.php
 *  Quản lý Khen thưởng, Kỷ luật & HSE Blacklist
 * ============================================================
 */
?>

<div class="row">
    <div class="col-md-4">
        <div class="panel mb-4">
            <div class="panel-header">
                <h3><i class="fas fa-plus-circle"></i> Thêm Quyết Định</h3>
            </div>
            <div class="panel-body">
                <?php if ($msg = Session::getFlash('success')): ?>
                    <div class="alert alert-success"><?= $msg ?></div>
                <?php endif; ?>
                <?php if ($msg = Session::getFlash('error')): ?>
                    <div class="alert alert-danger"><?= $msg ?></div>
                <?php endif; ?>

                <form action="<?= BASE_URL ?>/reward/store" method="POST">
                    <div class="form-group mb-3">
                        <label>Nhân sự <span class="text-danger">*</span></label>
                        <select name="employee_id" class="form-control" required>
                            <option value="">-- Chọn nhân sự --</option>
                            <?php foreach($employees as $emp): ?>
                                <option value="<?= $emp->id ?>"><?= htmlspecialchars($emp->emp_code) ?> - <?= htmlspecialchars($emp->full_name) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label>Loại quyết định <span class="text-danger">*</span></label>
                        <select name="type" class="form-control" required onchange="toggleSafety(this.value)">
                            <option value="Reward">Khen thưởng (Reward)</option>
                            <option value="Discipline">Kỷ luật (Discipline)</option>
                        </select>
                    </div>

                    <div class="form-group mb-3" id="safetyCheck" style="display: none;">
                        <div class="custom-control custom-checkbox border p-2 rounded bg-light border-danger">
                            <input type="checkbox" class="custom-control-input" id="is_safety" name="is_safety_violation" value="1">
                            <label class="custom-control-label text-danger font-weight-bold" for="is_safety">
                                <i class="fas fa-exclamation-triangle"></i> Vi phạm An toàn (HSE Blacklist)
                            </label>
                            <small class="form-text text-muted">Đánh dấu nếu vi phạm quy định an toàn (vd: Không đeo dây an toàn trên cao). Nhân viên sẽ bị khóa (Blacklisted).</small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label>Số Quyết định</label>
                            <input type="text" name="decision_number" class="form-control">
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label>Ngày QĐ</label>
                            <input type="date" name="decision_date" class="form-control" value="<?= date('Y-m-d') ?>">
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label>Tiêu đề / Lý do vắn tắt <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" required placeholder="VD: Không mặc áo phản quang">
                    </div>
                    
                    <div class="form-group mb-3">
                        <label>Số tiền (VNĐ)</label>
                        <input type="number" name="amount" class="form-control" value="0">
                        <small class="text-muted">Nhập 0 nếu chỉ là cảnh cáo/đình chỉ.</small>
                    </div>

                    <div class="form-group mb-4">
                        <label>Chi tiết</label>
                        <textarea name="reason" class="form-control" rows="3"></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-save"></i> Lưu Quyết Định</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="panel mb-4">
            <div class="panel-header">
                <h3><i class="fas fa-history"></i> Lịch sử Khen thưởng / Kỷ luật</h3>
            </div>
            <div class="panel-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Ngày QĐ</th>
                                <th>Nhân sự</th>
                                <th>Phân loại</th>
                                <th>Nội dung</th>
                                <th>Số tiền</th>
                                <th>Trạng thái NV</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($records)): ?>
                                <tr><td colspan="6" class="text-center p-4">Chưa có dữ liệu</td></tr>
                            <?php else: ?>
                                <?php foreach ($records as $r): ?>
                                    <tr class="<?= $r['is_safety_violation'] ? 'table-danger' : '' ?>">
                                        <td><?= date('d/m/Y', strtotime($r['decision_date'])) ?><br><small class="text-muted"><?= htmlspecialchars($r['decision_number']) ?></small></td>
                                        <td>
                                            <strong><?= htmlspecialchars($r['full_name']) ?></strong><br>
                                            <small><?= htmlspecialchars($r['emp_code']) ?></small>
                                        </td>
                                        <td>
                                            <?php if($r['type'] === 'Reward'): ?>
                                                <span class="badge bg-success">Khen thưởng</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Kỷ luật</span>
                                                <?php if($r['is_safety_violation']): ?>
                                                    <br><span class="badge bg-dark mt-1"><i class="fas fa-skull"></i> HSE</span>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($r['title']) ?></td>
                                        <td class="text-right"><?= number_format($r['amount'], 0, ',', '.') ?> đ</td>
                                        <td>
                                            <?php if ($r['emp_status'] === 'Blacklisted'): ?>
                                                <span class="badge badge-resigned">Blacklisted</span>
                                            <?php else: ?>
                                                <span class="badge badge-active"><?= $r['emp_status'] ?></span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.table-danger { background-color: #fef2f2 !important; }
</style>

<script>
function toggleSafety(type) {
    if (type === 'Discipline') {
        document.getElementById('safetyCheck').style.display = 'block';
    } else {
        document.getElementById('safetyCheck').style.display = 'none';
        document.getElementById('is_safety').checked = false;
    }
}
</script>
