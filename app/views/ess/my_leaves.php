<?php
require_once APP_ROOT . '/views/ess/layout/header.php';
?>

<div class="container-xl">
    
    <!-- Header Page -->
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-2">
        <div>
            <h4 class="fw-bold mb-1 text-dark">
                <i class="fa-solid fa-calendar-xmark text-warning me-2"></i> Lịch sử đơn xin nghỉ phép
            </h4>
            <p class="text-muted small mb-0">Theo dõi toàn bộ đơn nghỉ phép cá nhân và hạn mức ngày phép năm.</p>
        </div>
        <div>
            <a href="<?= BASE_URL ?>/ess/leaveRequest" class="btn btn-warning text-dark fw-bold btn-sm rounded-pill px-3 shadow-sm">
                <i class="fa-solid fa-plus me-1"></i> Nộp đơn xin nghỉ mới
            </a>
        </div>
    </div>

    <!-- 4 Card Thống kê Phép Năm -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="metric-card blue">
                <div class="metric-icon">
                    <i class="fa-solid fa-calendar-plus"></i>
                </div>
                <div class="metric-value text-primary"><?= number_format($leaveStats['entitled'], 1) ?></div>
                <div class="metric-label">Tổng phép năm <?= date('Y') ?></div>
                <div class="metric-sub">Tiêu chuẩn: 12 ngày + thâm niên</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="metric-card rose">
                <div class="metric-icon">
                    <i class="fa-solid fa-calendar-check"></i>
                </div>
                <div class="metric-value text-danger"><?= number_format($leaveStats['used'], 1) ?></div>
                <div class="metric-label">Đã nghỉ (Approved)</div>
                <div class="metric-sub">Đã được phê duyệt</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="metric-card amber">
                <div class="metric-icon">
                    <i class="fa-solid fa-hourglass-half"></i>
                </div>
                <div class="metric-value text-warning"><?= number_format($leaveStats['pending'], 1) ?></div>
                <div class="metric-label">Đang chờ xét duyệt</div>
                <div class="metric-sub">Đơn đang chờ quản lý ký</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="metric-card emerald">
                <div class="metric-icon">
                    <i class="fa-solid fa-umbrella-beach"></i>
                </div>
                <div class="metric-value text-success"><?= number_format($leaveStats['remaining'], 1) ?></div>
                <div class="metric-label">Số ngày còn lại</div>
                <div class="metric-sub">Có thể đăng ký nghỉ</div>
            </div>
        </div>
    </div>

    <!-- Danh sách Đơn Xin Nghỉ Phép -->
    <div class="card-custom">
        <div class="card-custom-header">
            <h5 class="card-custom-title">
                <i class="fa-solid fa-table-list text-primary"></i> Danh sách tất cả đơn nghỉ phép
            </h5>
            <span class="badge bg-light text-muted border">
                <?= count($leaveRequests) ?> đơn đã tạo
            </span>
        </div>
        <div class="card-custom-body p-0">
            <?php if (!empty($leaveRequests)): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th>Mã đơn</th>
                                <th>Loại nghỉ phép</th>
                                <th>Thời gian nghỉ</th>
                                <th class="text-center">Số ngày</th>
                                <th>Lý do xin nghỉ</th>
                                <th>Ngày nộp</th>
                                <th class="text-center">Trạng thái</th>
                                <th>Người duyệt</th>
                                <th class="text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($leaveRequests as $lr): ?>
                                <tr>
                                    <td class="font-monospace fw-bold text-muted">#<?= $lr['id'] ?></td>
                                    <td>
                                        <span class="fw-bold text-dark"><?= htmlspecialchars($lr['leave_type_name']) ?></span>
                                    </td>
                                    <td>
                                        <span class="fw-semibold text-dark">
                                            <?= date('d/m/Y', strtotime($lr['start_date'])) ?>
                                        </span>
                                        &rarr;
                                        <span class="fw-semibold text-dark">
                                            <?= date('d/m/Y', strtotime($lr['end_date'])) ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark border fs-6">
                                            <?= number_format($lr['total_days'], 1) ?>
                                        </span>
                                    </td>
                                    <td style="max-width: 250px;">
                                        <div class="text-truncate" title="<?= htmlspecialchars($lr['reason'] ?? '') ?>">
                                            <?= htmlspecialchars($lr['reason'] ?? '—') ?>
                                        </div>
                                    </td>
                                    <td class="text-muted">
                                        <?= date('d/m/Y H:i', strtotime($lr['created_at'])) ?>
                                    </td>
                                    <td class="text-center">
                                        <?php 
                                            $stClass = match($lr['status']) {
                                                'Approved' => 'bg-success-subtle text-success border border-success-subtle',
                                                'Pending'  => 'bg-warning-subtle text-warning border border-warning-subtle',
                                                'Rejected' => 'bg-danger-subtle text-danger border border-danger-subtle',
                                                default    => 'bg-secondary-subtle text-secondary'
                                            };
                                            $stLabel = match($lr['status']) {
                                                'Approved' => 'Đã duyệt',
                                                'Pending'  => 'Chờ duyệt',
                                                'Rejected' => 'Từ chối',
                                                default    => 'Đã hủy'
                                            };
                                        ?>
                                        <span class="badge <?= $stClass ?> px-2 py-1">
                                            <?= $stLabel ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (!empty($lr['approver_name'])): ?>
                                            <span class="small fw-semibold text-dark"><?= htmlspecialchars($lr['approver_name']) ?></span>
                                            <?php if (!empty($lr['approved_at'])): ?>
                                                <small class="d-block text-muted" style="font-size: 0.7rem;">
                                                    <?= date('d/m/Y', strtotime($lr['approved_at'])) ?>
                                                </small>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($lr['status'] === 'Pending'): ?>
                                            <a href="<?= BASE_URL ?>/ess/cancelLeave/<?= $lr['id'] ?>" 
                                               class="btn btn-outline-danger btn-sm rounded-pill py-0 px-2"
                                               onclick="return confirm('Bạn có chắc chắn muốn hủy đơn xin nghỉ phép này?')">
                                                <i class="fa-solid fa-xmark me-1"></i> Hủy đơn
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted small">—</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5 text-muted small">
                    <i class="fa-solid fa-plane-slash fa-3x text-secondary opacity-50 mb-3"></i>
                    <p>Bạn chưa có lịch sử đơn nghỉ phép nào.</p>
                    <a href="<?= BASE_URL ?>/ess/leaveRequest" class="btn btn-warning text-dark fw-bold btn-sm rounded-pill px-3">
                        + Nộp đơn xin nghỉ ngay
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<?php require_once APP_ROOT . '/views/ess/layout/footer.php'; ?>
