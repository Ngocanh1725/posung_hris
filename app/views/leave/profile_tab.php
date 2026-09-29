<?php
/**
 * ============================================================
 *  POSUNG HRIS – Tab 16: Nghỉ phép & Quỹ phép (Employee Profile)
 * ============================================================
 */
$leaveBalances = $employee->leave_balances ?? [];
$leaveRequests = $employee->leave_requests ?? [];
$annualBalance = null;
foreach ($leaveBalances as $b) {
    if ($b['leave_type_id'] === 1 || $b['code'] === 'NP') {
        $annualBalance = $b;
        break;
    }
}
?>

<div class="leave-profile-tab">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="section-title mb-1"><i class="fas fa-calendar-check text-primary me-2"></i> Quỹ Phép & Lịch Sử Nghỉ Phép</h4>
            <p class="text-muted small mb-0">Theo dõi hạn mức ngày phép năm, số ngày đã sử dụng và các đơn nghỉ phép theo chuẩn Frappe HRMS.</p>
        </div>
        <div>
            <a href="<?= BASE_URL ?>/leave/allocations?search=<?= urlencode($employee->emp_code) ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                <i class="fas fa-layer-group me-1"></i> Quản lý phân bổ
            </a>
        </div>
    </div>

    <!-- Mini KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="p-3 bg-light rounded text-center border">
                <span class="text-muted small d-block">Phép tiêu chuẩn</span>
                <h4 class="fw-bold text-primary mb-0 mt-1"><?= $annualBalance ? floatval($annualBalance['entitled_days']) : 12 ?> <small style="font-size:12px;">ngày</small></h4>
                <small class="text-muted">12d + thâm niên</small>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="p-3 bg-light rounded text-center border">
                <span class="text-muted small d-block">Phép chuyển tiếp</span>
                <h4 class="fw-bold text-warning mb-0 mt-1"><?= $annualBalance ? floatval($annualBalance['carried_forward_days']) : 0 ?> <small style="font-size:12px;">ngày</small></h4>
                <small class="text-muted">Từ năm trước</small>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="p-3 bg-light rounded text-center border">
                <span class="text-muted small d-block">Đã sử dụng</span>
                <h4 class="fw-bold text-danger mb-0 mt-1"><?= $annualBalance ? floatval($annualBalance['used_days']) : 0 ?> <small style="font-size:12px;">ngày</small></h4>
                <small class="text-danger">Đã phê duyệt</small>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="p-3 bg-light rounded text-center border border-success">
                <span class="text-muted small d-block">Khả dụng còn lại</span>
                <h4 class="fw-bold text-success mb-0 mt-1"><?= $annualBalance ? floatval($annualBalance['remaining_days']) : 12 ?> <small style="font-size:12px;">ngày</small></h4>
                <small class="text-success fw-semibold">Có thể đăng ký</small>
            </div>
        </div>
    </div>

    <!-- Bảng phân bổ các loại phép -->
    <div class="card border mb-4" style="border-radius: 10px; overflow: hidden;">
        <div class="card-header bg-white py-2 px-3 border-bottom">
            <h6 class="fw-bold mb-0 text-dark small text-uppercase">Hạn mức theo từng loại nghỉ phép</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th>Loại nghỉ phép</th>
                        <th class="text-center">Tính lương</th>
                        <th class="text-center">Được cấp</th>
                        <th class="text-center">Chuyển tiếp</th>
                        <th class="text-center">Đã nghỉ</th>
                        <th class="text-center">Còn lại</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($leaveBalances)): ?>
                        <tr><td colspan="6" class="text-center py-3 text-muted">Chưa có dữ liệu phân bổ.</td></tr>
                    <?php else: ?>
                        <?php foreach ($leaveBalances as $b): ?>
                        <tr>
                            <td class="fw-semibold text-dark"><?= h($b['name']) ?></td>
                            <td class="text-center">
                                <?= $b['is_paid'] ? '<span class="badge bg-success-subtle text-success">Có lương</span>' : '<span class="badge bg-secondary-subtle text-secondary">Không lương</span>' ?>
                            </td>
                            <td class="text-center"><?= floatval($b['entitled_days']) ?></td>
                            <td class="text-center"><?= floatval($b['carried_forward_days']) ?></td>
                            <td class="text-center fw-bold text-danger"><?= floatval($b['used_days']) ?></td>
                            <td class="text-center fw-bold text-success">
                                <?= ($b['is_paid'] === 0 || $b['allow_negative']) ? '∞' : floatval($b['remaining_days']) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Lịch sử đơn nghỉ phép -->
    <div class="card border" style="border-radius: 10px; overflow: hidden;">
        <div class="card-header bg-white py-2 px-3 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-dark small text-uppercase">Lịch sử đơn nghỉ phép</h6>
            <span class="badge bg-light text-muted border"><?= count($leaveRequests) ?> đơn</span>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th>Mã</th>
                        <th>Loại phép</th>
                        <th>Thời gian</th>
                        <th class="text-center">Số ngày</th>
                        <th>Lý do</th>
                        <th class="text-center">Trạng thái</th>
                        <th>Người duyệt</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($leaveRequests)): ?>
                        <tr><td colspan="7" class="text-center py-4 text-muted">Chưa có đơn xin nghỉ phép nào.</td></tr>
                    <?php else: ?>
                        <?php foreach ($leaveRequests as $lr): ?>
                        <tr>
                            <td class="text-muted font-monospace">#<?= $lr->id ?></td>
                            <td class="fw-semibold"><?= h($lr->leave_type_name) ?></td>
                            <td>
                                <?= date('d/m/Y', strtotime($lr->start_date)) ?> 
                                <i class="fas fa-arrow-right text-muted mx-1" style="font-size:9px;"></i> 
                                <?= date('d/m/Y', strtotime($lr->end_date)) ?>
                            </td>
                            <td class="text-center fw-bold text-primary"><?= floatval($lr->total_days ?? $lr->days) ?></td>
                            <td class="text-truncate" style="max-width: 200px;"><?= h($lr->reason) ?></td>
                            <td class="text-center">
                                <?php if ($lr->status === 'Approved'): ?>
                                    <span class="badge bg-success">Đã duyệt</span>
                                <?php elseif ($lr->status === 'Rejected'): ?>
                                    <span class="badge bg-danger">Từ chối</span>
                                <?php elseif ($lr->status === 'Cancelled'): ?>
                                    <span class="badge bg-secondary">Đã hủy</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark">Chờ duyệt</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-muted"><?= h($lr->approver_name ?? '---') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
