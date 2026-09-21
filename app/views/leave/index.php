<?php
/**
 * ============================================================
 *  View: leave/index.php
 * ============================================================
 */
?>

<div class="row">
    <!-- CỘT TRÁI: Dành cho Bản thân -->
    <div class="col-md-<?= $isManager ? '8' : '12' ?>">
        
        <!-- Widget Quỹ Phép -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-primary text-white h-100" style="border-radius: 12px; border:none; box-shadow: 0 4px 15px rgba(37,99,235,0.2);">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white-50 mb-1">Tổng Phép Năm</h6>
                                <h3 class="mb-0 fw-bold"><?= $annualLeaveTotal ?> ngày</h3>
                            </div>
                            <div style="background: rgba(255,255,255,0.2); padding: 10px; border-radius: 50%;">
                                <i class="fas fa-calendar-alt fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-info text-white h-100" style="border-radius: 12px; border:none; box-shadow: 0 4px 15px rgba(14,165,233,0.2);">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white-50 mb-1">Đã Sử Dụng</h6>
                                <h3 class="mb-0 fw-bold"><?= $usedLeave ?> ngày</h3>
                            </div>
                            <div style="background: rgba(255,255,255,0.2); padding: 10px; border-radius: 50%;">
                                <i class="fas fa-plane-departure fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-success text-white h-100" style="border-radius: 12px; border:none; box-shadow: 0 4px 15px rgba(16,185,129,0.2);">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white-50 mb-1">Còn Lại</h6>
                                <h3 class="mb-0 fw-bold"><?= $balance ?> ngày</h3>
                            </div>
                            <div style="background: rgba(255,255,255,0.2); padding: 10px; border-radius: 50%;">
                                <i class="fas fa-check-circle fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="panel" style="border-radius: 12px; border: none; box-shadow: 0 5px 20px rgba(0,0,0,0.04);">
            <div class="panel-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0 fw-bold text-dark"><i class="fas fa-history text-muted"></i> Lịch sử Đơn từ của tôi</h4>
                <button class="btn btn-primary" onclick="openLeaveModal()"><i class="fas fa-plus"></i> Nộp đơn xin phép</button>
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Ngày nộp</th>
                                <th>Loại phép</th>
                                <th>Thời gian</th>
                                <th>Số ngày</th>
                                <th>Trạng thái</th>
                                <th>Ghi chú duyệt</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($myRequests)): ?>
                                <tr><td colspan="6" class="text-center py-4 text-muted">Bạn chưa có đơn xin phép nào.</td></tr>
                            <?php else: ?>
                                <?php foreach($myRequests as $req): ?>
                                <tr>
                                    <td><?= date('d/m/Y', strtotime($req->created_at)) ?></td>
                                    <td class="fw-bold text-primary">
                                        <?= h($req->leave_type_name) ?>
                                        <?php if ($req->is_paid): ?>
                                            <span class="badge bg-success ms-1" style="font-size:10px;">Có lương</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary ms-1" style="font-size:10px;">Không lương</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        Từ: <?= date('d/m/Y', strtotime($req->start_date)) ?><br>
                                        Đến: <?= date('d/m/Y', strtotime($req->end_date)) ?>
                                    </td>
                                    <td><strong><?= floatval($req->days) ?></strong> ngày</td>
                                    <td>
                                        <?php if ($req->status === 'Approved'): ?>
                                            <span class="badge bg-success"><i class="fas fa-check"></i> Đã duyệt</span>
                                        <?php elseif ($req->status === 'Rejected'): ?>
                                            <span class="badge bg-danger"><i class="fas fa-times"></i> Từ chối</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark"><i class="fas fa-clock"></i> Chờ duyệt</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><small class="text-muted"><?= h($req->approver_note ?? '---') ?></small></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- CỘT PHẢI: Dành cho Quản lý (Phê duyệt) -->
    <?php if ($isManager): ?>
    <div class="col-md-4">
        <div class="panel" style="border-radius: 12px; border: none; box-shadow: 0 5px 20px rgba(0,0,0,0.04);">
            <div class="panel-header border-bottom">
                <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-clipboard-check text-warning"></i> Cần phê duyệt (<?= count($pendingRequests) ?>)</h5>
            </div>
            <div class="panel-body p-0">
                <?php if (empty($pendingRequests)): ?>
                    <div class="p-4 text-center text-muted">
                        <i class="fas fa-check-circle mb-2" style="font-size: 30px; color: var(--success); opacity:0.5;"></i>
                        <p class="mb-0">Tuyệt vời! Không có đơn nào đang chờ duyệt.</p>
                    </div>
                <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach($pendingRequests as $req): ?>
                        <li class="list-group-item p-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <strong class="d-block text-dark"><?= h($req->full_name) ?></strong>
                                    <span class="badge bg-secondary text-dark"><?= h($req->emp_code) ?></span>
                                </div>
                                <span class="badge bg-primary text-white"><?= h($req->leave_type_name) ?></span>
                            </div>
                            <div class="small text-muted mb-2">
                                <i class="fas fa-calendar-day"></i> Từ <strong><?= date('d/m/Y', strtotime($req->start_date)) ?></strong> đến <strong><?= date('d/m/Y', strtotime($req->end_date)) ?></strong> (<?= floatval($req->days) ?> ngày)
                            </div>
                            <div class="small mb-3 p-2 bg-light rounded" style="border-left: 3px solid var(--primary);">
                                "<?= h($req->reason) ?>"
                            </div>
                            <div class="d-flex gap-2">
                                <form action="<?= BASE_URL ?>/leave/approve/<?= $req->id ?>" method="POST" style="flex:1;">
                                    <input type="hidden" name="_csrf_token" value="<?= Session::generateCsrfToken() ?>">
                                    <button type="submit" class="btn btn-success btn-sm w-100" onclick="return confirm('Bạn đồng ý duyệt đơn này?');"><i class="fas fa-check"></i> Duyệt</button>
                                </form>
                                <form action="<?= BASE_URL ?>/leave/reject/<?= $req->id ?>" method="POST" style="flex:1;" onsubmit="let reason = prompt('Lý do từ chối:'); if(reason === null) return false; this.approver_note.value = reason;">
                                    <input type="hidden" name="_csrf_token" value="<?= Session::generateCsrfToken() ?>">
                                    <input type="hidden" name="approver_note" value="">
                                    <button type="submit" class="btn btn-danger btn-sm w-100"><i class="fas fa-times"></i> Từ chối</button>
                                </form>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Modal Nộp đơn -->
<div class="modal fade" id="leaveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            <div class="modal-header" style="background: linear-gradient(135deg, var(--primary), var(--accent)); color: #fff; border-radius: 12px 12px 0 0;">
                <h5 class="modal-title"><i class="fas fa-paper-plane"></i> Nộp đơn xin phép</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= BASE_URL ?>/leave/store" method="POST">
                <input type="hidden" name="_csrf_token" value="<?= Session::generateCsrfToken() ?>">
                <div class="modal-body p-4">
                    <div class="form-group mb-3">
                        <label>Loại phép <span class="text-danger">*</span></label>
                        <select name="leave_type_id" class="form-control" required>
                            <option value="">-- Chọn loại phép --</option>
                            <?php foreach($leaveTypes as $lt): ?>
                                <option value="<?= $lt->id ?>"><?= h($lt->name) ?> <?= $lt->is_paid ? '(Hưởng lương)' : '(Không lương)' ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <label>Từ ngày <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" id="start_date" class="form-control" required>
                        </div>
                        <div class="col-6">
                            <label>Đến ngày <span class="text-danger">*</span></label>
                            <input type="date" name="end_date" id="end_date" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-group mb-3">
                        <label>Tổng số ngày (Tùy chỉnh nếu nghỉ nửa ngày)</label>
                        <input type="number" step="0.5" name="days" id="days" class="form-control" placeholder="Để trống hệ thống sẽ tự tính theo khoảng thời gian">
                    </div>
                    <div class="form-group">
                        <label>Lý do nghỉ <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control" rows="3" required placeholder="Nhập lý do chi tiết..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light" style="border-radius: 0 0 12px 12px;">
                    <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Nộp đơn ngay</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let leaveModal;
document.addEventListener('DOMContentLoaded', () => {
    leaveModal = new bootstrap.Modal(document.getElementById('leaveModal'));
    
    // Tự động tính ngày
    const sd = document.getElementById('start_date');
    const ed = document.getElementById('end_date');
    const dInp = document.getElementById('days');
    
    function calcDays() {
        if(sd.value && ed.value) {
            let start = new Date(sd.value);
            let end = new Date(ed.value);
            if(end >= start) {
                let diffTime = Math.abs(end - start);
                let diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                dInp.value = diffDays;
            }
        }
    }
    sd.addEventListener('change', calcDays);
    ed.addEventListener('change', calcDays);
});

function openLeaveModal() {
    leaveModal.show();
}
</script>
