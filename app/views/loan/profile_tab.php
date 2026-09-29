<?php
/**
 * ============================================================
 *  POSUNG HRIS – View: loan/profile_tab.php
 * ============================================================
 *  Tab 13: Quản lý Tạm ứng & Khoản vay Nhân viên
 *  (Tích hợp trong Hồ sơ Nhân sự 360 độ)
 * ============================================================
 */

$loans = $employee->loans ?? [];
$activeLoans = $employee->active_loans ?? [];

$totalActiveBalance = 0;
$totalMonthlyDeduction = 0;
foreach ($activeLoans as $al) {
    $totalActiveBalance += (float)$al->remaining_balance;
    $totalMonthlyDeduction += min((float)$al->monthly_emi, (float)$al->remaining_balance);
}
?>

<div class="loan-profile-tab">
    <!-- KHỐI THỐNG KÊ TỔNG QUAN NỢ VAY CỦA NHÂN VIÊN -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 bg-light rounded-3 p-3">
                <span class="text-muted small fw-semibold text-uppercase">Khoản vay đang thực hiện</span>
                <div class="d-flex align-items-center justify-content-between mt-1">
                    <h3 class="fw-bold mb-0 text-primary"><?= count($activeLoans) ?></h3>
                    <div class="fs-4 text-primary bg-white rounded-circle p-2 shadow-sm">
                        <i class="fas fa-hand-holding-dollar"></i>
                    </div>
                </div>
                <small class="text-muted">Tổng số khoản vay còn dư nợ</small>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 bg-light rounded-3 p-3">
                <span class="text-muted small fw-semibold text-uppercase">Dư nợ còn phải hoàn trả</span>
                <div class="d-flex align-items-center justify-content-between mt-1">
                    <h3 class="fw-bold mb-0 text-danger"><?= number_format($totalActiveBalance, 0, ',', '.') ?> ₫</h3>
                    <div class="fs-4 text-danger bg-white rounded-circle p-2 shadow-sm">
                        <i class="fas fa-scale-unbalanced"></i>
                    </div>
                </div>
                <small class="text-muted">Trừ dần vào kỳ lương hàng tháng</small>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 bg-light rounded-3 p-3">
                <span class="text-muted small fw-semibold text-uppercase">Khấu trừ lương dự kiến kỳ này</span>
                <div class="d-flex align-items-center justify-content-between mt-1">
                    <h3 class="fw-bold mb-0 text-dark"><?= number_format($totalMonthlyDeduction, 0, ',', '.') ?> ₫</h3>
                    <div class="fs-4 text-secondary bg-white rounded-circle p-2 shadow-sm">
                        <i class="fas fa-money-check-dollar"></i>
                    </div>
                </div>
                <small class="text-muted">Tự động trừ khi tính bảng lương</small>
            </div>
        </div>
    </div>

    <!-- DANH SÁCH CÁC KHOẢN VAY / TẠM ỨNG -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-dark">
                <i class="fas fa-list-check text-primary me-2"></i>Lịch sử các Hợp đồng Vay & Tạm ứng lương
            </h6>
            <a href="<?= BASE_URL ?>/loan/create?employee_id=<?= $employee->id ?>" class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i> Lập đề xuất vay mới
            </a>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-muted text-uppercase">
                    <tr>
                        <th class="ps-3">Mã vay</th>
                        <th>Loại khoản vay</th>
                        <th class="text-end">Số tiền vay</th>
                        <th class="text-end">EMI / Tháng</th>
                        <th class="text-end">Đã hoàn trả</th>
                        <th class="text-end">Dư nợ còn lại</th>
                        <th>Ngày lập</th>
                        <th class="text-center">Trạng thái</th>
                        <th class="text-end pe-3">Chi tiết</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($loans)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                <i class="fas fa-receipt fs-3 d-block mb-2 opacity-50"></i>
                                Nhân viên này chưa có khoản tạm ứng hoặc hợp đồng vay vốn nào.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($loans as $loan): ?>
                            <?php
                            $statusBadge = match($loan->status) {
                                'Pending'   => '<span class="badge bg-warning text-dark">Chờ duyệt</span>',
                                'Active'    => '<span class="badge bg-info text-white">Đang trả</span>',
                                'Closed'    => '<span class="badge bg-success text-white">Đã tất toán</span>',
                                'Rejected'  => '<span class="badge bg-danger text-white">Từ chối</span>',
                                default     => '<span class="badge bg-secondary">' . h($loan->status) . '</span>'
                            };
                            ?>
                            <tr>
                                <td class="ps-3 fw-bold text-primary">
                                    <a href="<?= BASE_URL ?>/loan/show/<?= $loan->id ?>" class="text-decoration-none">
                                        <?= h($loan->loan_code) ?>
                                    </a>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        <?= h($loan->type_name) ?>
                                    </span>
                                </td>
                                <td class="text-end fw-semibold text-dark">
                                    <?= number_format((float)$loan->amount, 0, ',', '.') ?> ₫
                                </td>
                                <td class="text-end text-primary fw-semibold">
                                    <?= number_format((float)$loan->monthly_emi, 0, ',', '.') ?> ₫
                                </td>
                                <td class="text-end text-success fw-semibold">
                                    <?= number_format((float)$loan->total_paid, 0, ',', '.') ?> ₫
                                </td>
                                <td class="text-end text-danger fw-bold">
                                    <?= number_format((float)$loan->remaining_balance, 0, ',', '.') ?> ₫
                                </td>
                                <td class="text-muted small">
                                    <?= date('d/m/Y', strtotime($loan->applied_date)) ?>
                                </td>
                                <td class="text-center">
                                    <?= $statusBadge ?>
                                </td>
                                <td class="text-end pe-3">
                                    <a href="<?= BASE_URL ?>/loan/show/<?= $loan->id ?>" class="btn btn-sm btn-outline-primary" title="Xem chi tiết">
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
