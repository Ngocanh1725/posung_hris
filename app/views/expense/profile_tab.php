<?php
/**
 * ============================================================
 *  POSUNG HRIS – View: expense/profile_tab.php
 * ============================================================
 *  Tab 14: Lịch sử Công tác & Quyết toán Chi phí Nhân viên
 *  (Tích hợp trong Hồ sơ Nhân sự 360 độ)
 * ============================================================
 */

$travels = $employee->travel_requests ?? [];
$claims  = $employee->expense_claims ?? [];

$totalClaimed = 0;
$totalNetPaid = 0;
foreach ($claims as $c) {
    $totalClaimed += (float)$c->total_amount;
    if ($c->status === 'Paid') {
        $totalNetPaid += (float)$c->net_payable;
    }
}
?>

<div class="expense-profile-tab">
    <!-- KHỐI THỐNG KÊ NHANH -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 bg-light rounded-3 p-3">
                <span class="text-muted small fw-semibold text-uppercase">Chuyến công tác đã thực hiện</span>
                <div class="d-flex align-items-center justify-content-between mt-1">
                    <h3 class="fw-bold mb-0 text-primary"><?= count($travels) ?></h3>
                    <div class="fs-4 text-primary bg-white rounded-circle p-2 shadow-sm">
                        <i class="fas fa-plane-departure"></i>
                    </div>
                </div>
                <small class="text-muted">Các đợt công tác dự án & đối tác</small>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 bg-light rounded-3 p-3">
                <span class="text-muted small fw-semibold text-uppercase">Tổng chi phí quyết toán</span>
                <div class="d-flex align-items-center justify-content-between mt-1">
                    <h3 class="fw-bold mb-0 text-dark"><?= number_format($totalClaimed, 0, ',', '.') ?> ₫</h3>
                    <div class="fs-4 text-secondary bg-white rounded-circle p-2 shadow-sm">
                        <i class="fas fa-receipt"></i>
                    </div>
                </div>
                <small class="text-muted"><?= count($claims) ?> bảng quyết toán đã nộp</small>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 bg-light rounded-3 p-3">
                <span class="text-muted small fw-semibold text-uppercase">Đã nhận thanh toán hoàn ứng</span>
                <div class="d-flex align-items-center justify-content-between mt-1">
                    <h3 class="fw-bold mb-0 text-success"><?= number_format($totalNetPaid, 0, ',', '.') ?> ₫</h3>
                    <div class="fs-4 text-success bg-white rounded-circle p-2 shadow-sm">
                        <i class="fas fa-money-check-dollar"></i>
                    </div>
                </div>
                <small class="text-muted">Công ty đã chi trả thành công</small>
            </div>
        </div>
    </div>

    <!-- DANH SÁCH CÁC CHUYẾN CÔNG TÁC -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-dark">
                <i class="fas fa-suitcase text-primary me-2"></i>Lịch sử Chuyến Đi Công tác
            </h6>
            <a href="<?= BASE_URL ?>/expense/createTravel?employee_id=<?= $employee->id ?>" class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i> Lập đề xuất công tác mới
            </a>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-muted text-uppercase">
                    <tr>
                        <th class="ps-3">Mã chuyến</th>
                        <th>Mục đích</th>
                        <th>Dự án</th>
                        <th>Điểm đến</th>
                        <th>Thời gian</th>
                        <th class="text-end">Tạm ứng</th>
                        <th class="text-center">Trạng thái</th>
                        <th class="text-end pe-3">Chi tiết</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($travels)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                Chưa có lịch sử chuyến công tác nào.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($travels as $tr): ?>
                            <?php
                            $trBadge = match($tr->status) {
                                'Pending'   => '<span class="badge bg-warning text-dark">Chờ duyệt</span>',
                                'Approved'  => '<span class="badge bg-info text-white">Đã duyệt</span>',
                                'Completed' => '<span class="badge bg-success text-white">Hoàn tất</span>',
                                'Rejected'  => '<span class="badge bg-danger text-white">Từ chối</span>',
                                default     => '<span class="badge bg-secondary">' . h($tr->status) . '</span>'
                            };
                            ?>
                            <tr>
                                <td class="ps-3 fw-bold text-primary">
                                    <a href="<?= BASE_URL ?>/expense/showTravel/<?= $tr->id ?>" class="text-decoration-none">
                                        <?= h($tr->request_code) ?>
                                    </a>
                                </td>
                                <td class="fw-semibold text-dark"><?= h($tr->purpose) ?></td>
                                <td>
                                    <?= !empty($tr->project_name) ? '<span class="badge bg-light text-dark border">' . h($tr->project_name) . '</span>' : '<span class="text-muted small">Chung</span>' ?>
                                </td>
                                <td><?= h($tr->to_location) ?></td>
                                <td class="small">
                                    <?= date('d/m/Y', strtotime($tr->departure_date)) ?> &rarr; <?= date('d/m/Y', strtotime($tr->return_date)) ?>
                                </td>
                                <td class="text-end text-primary fw-semibold">
                                    <?= number_format((float)$tr->advance_amount, 0, ',', '.') ?> ₫
                                </td>
                                <td class="text-center"><?= $trBadge ?></td>
                                <td class="text-end pe-3">
                                    <a href="<?= BASE_URL ?>/expense/showTravel/<?= $tr->id ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- DANH SÁCH BẢNG QUYẾT TOÁN CHI PHÍ -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-dark">
                <i class="fas fa-receipt text-primary me-2"></i>Bảng Thanh Quyết toán Chi phí Thực tế
            </h6>
            <a href="<?= BASE_URL ?>/expense/createClaim?employee_id=<?= $employee->id ?>" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-plus me-1"></i> Tạo bảng quyết toán
            </a>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-muted text-uppercase">
                    <tr>
                        <th class="ps-3">Mã quyết toán</th>
                        <th>Tiêu đề</th>
                        <th>Dự án</th>
                        <th class="text-end">Tổng chi</th>
                        <th class="text-end">Trừ tạm ứng</th>
                        <th class="text-end">Thực nhận</th>
                        <th>Ngày nộp</th>
                        <th class="text-center">Trạng thái</th>
                        <th class="text-end pe-3">Chi tiết</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($claims)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                Chưa có hồ sơ thanh quyết toán chi phí nào.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($claims as $c): ?>
                            <?php
                            $cBadge = match($c->status) {
                                'Submitted' => '<span class="badge bg-warning text-dark">Chờ duyệt</span>',
                                'Approved'  => '<span class="badge bg-info text-white">Đã duyệt</span>',
                                'Paid'      => '<span class="badge bg-success text-white">Đã chi trả</span>',
                                'Rejected'  => '<span class="badge bg-danger text-white">Từ chối</span>',
                                default     => '<span class="badge bg-secondary">' . h($c->status) . '</span>'
                            };
                            ?>
                            <tr>
                                <td class="ps-3 fw-bold text-primary">
                                    <a href="<?= BASE_URL ?>/expense/showClaim/<?= $c->id ?>" class="text-decoration-none">
                                        <?= h($c->claim_code) ?>
                                    </a>
                                </td>
                                <td class="fw-semibold text-dark"><?= h($c->title) ?></td>
                                <td>
                                    <?= !empty($c->project_name) ? '<span class="badge bg-light text-dark border">' . h($c->project_name) . '</span>' : '<span class="text-muted small">Văn phòng</span>' ?>
                                </td>
                                <td class="text-end fw-bold text-dark">
                                    <?= number_format((float)$c->total_amount, 0, ',', '.') ?> ₫
                                </td>
                                <td class="text-end text-muted">
                                    <?= number_format((float)$c->advance_deducted, 0, ',', '.') ?> ₫
                                </td>
                                <td class="text-end fw-bold text-success">
                                    <?= number_format((float)$c->net_payable, 0, ',', '.') ?> ₫
                                </td>
                                <td class="small text-muted">
                                    <?= date('d/m/Y', strtotime($c->submitted_date)) ?>
                                </td>
                                <td class="text-center"><?= $cBadge ?></td>
                                <td class="text-end pe-3">
                                    <a href="<?= BASE_URL ?>/expense/showClaim/<?= $c->id ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
