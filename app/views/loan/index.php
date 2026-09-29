<?php
/**
 * ============================================================
 *  POSUNG HRIS – View: loan/index.php
 * ============================================================
 *  Danh sách Quản lý Tạm ứng & Khoản vay Nhân viên
 * ============================================================
 */
?>
<div class="content-wrapper">
    <!-- TIÊU ĐỀ & THANH ĐIỀU HƯỚNG -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h2 class="mb-1 fw-bold text-dark"><i class="fas fa-hand-holding-dollar text-primary me-2"></i>Quản lý Tạm ứng & Khoản vay</h2>
            <p class="text-muted mb-0">Quản lý các khoản tạm ứng lương, vay vốn phúc lợi công đoàn và chương trình trả góp cho CBNV</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>/loan/types" class="btn btn-outline-secondary">
                <i class="fas fa-tags me-1"></i> Danh mục loại vay
            </a>
            <a href="<?= BASE_URL ?>/loan/report" class="btn btn-outline-info">
                <i class="fas fa-chart-pie me-1"></i> Báo cáo dư nợ
            </a>
            <a href="<?= BASE_URL ?>/loan/create" class="btn btn-primary">
                <i class="fas fa-plus-circle me-1"></i> Lập đề xuất vay mới
            </a>
        </div>
    </div>

    <!-- THỐNG KÊ NHANH (KPI CARDS) -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 bg-white p-3 h-100 border-start border-4 border-primary">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Tổng vốn giải ngân</span>
                        <h3 class="fw-bold mb-0 text-dark mt-1"><?= number_format($stats['total_lent'] ?? 0, 0, ',', '.') ?> <small class="fs-6 text-muted">₫</small></h3>
                        <small class="text-primary"><i class="fas fa-file-invoice-dollar me-1"></i><?= number_format($stats['total_loans'] ?? 0) ?> hồ sơ giải ngân</small>
                    </div>
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 text-primary fs-3">
                        <i class="fas fa-coins"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 bg-white p-3 h-100 border-start border-4 border-danger">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Tổng dư nợ còn lại</span>
                        <h3 class="fw-bold mb-0 text-danger mt-1"><?= number_format($stats['total_outstanding'] ?? 0, 0, ',', '.') ?> <small class="fs-6 text-muted">₫</small></h3>
                        <small class="text-danger"><i class="fas fa-hourglass-half me-1"></i><?= number_format($stats['active_count'] ?? 0) ?> khoản đang trả</small>
                    </div>
                    <div class="rounded-circle bg-danger bg-opacity-10 p-3 text-danger fs-3">
                        <i class="fas fa-scale-unbalanced"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 bg-white p-3 h-100 border-start border-4 border-success">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Đã thu hồi hoàn trả</span>
                        <h3 class="fw-bold mb-0 text-success mt-1"><?= number_format($stats['total_repaid'] ?? 0, 0, ',', '.') ?> <small class="fs-6 text-muted">₫</small></h3>
                        <small class="text-success"><i class="fas fa-circle-check me-1"></i><?= number_format($stats['closed_count'] ?? 0) ?> khoản tất toán</small>
                    </div>
                    <div class="rounded-circle bg-success bg-opacity-10 p-3 text-success fs-3">
                        <i class="fas fa-piggy-bank"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 bg-white p-3 h-100 border-start border-4 border-warning">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Đang chờ phê duyệt</span>
                        <h3 class="fw-bold mb-0 text-warning mt-1"><?= number_format($stats['pending_count'] ?? 0) ?></h3>
                        <small class="text-muted">Cần HR & Ban GĐ duyệt</small>
                    </div>
                    <div class="rounded-circle bg-warning bg-opacity-10 p-3 text-warning fs-3">
                        <i class="fas fa-clock-rotate-left"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- BỘ LỌC TÌM KIẾM -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-3">
            <form action="<?= BASE_URL ?>/loan" method="GET" class="row g-2 align-items-center">
                <div class="col-md-3">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Mã vay, Tên NV, Mã NV..." value="<?= h($filters['search']) ?>">
                    </div>
                </div>

                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">-- Tất cả trạng thái --</option>
                        <option value="Pending" <?= $filters['status'] === 'Pending' ? 'selected' : '' ?>>Chờ duyệt</option>
                        <option value="Active" <?= $filters['status'] === 'Active' ? 'selected' : '' ?>>Đang trả (Active)</option>
                        <option value="Closed" <?= $filters['status'] === 'Closed' ? 'selected' : '' ?>>Đã tất toán</option>
                        <option value="Rejected" <?= $filters['status'] === 'Rejected' ? 'selected' : '' ?>>Bị từ chối</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <select name="loan_type_id" class="form-select">
                        <option value="">-- Loại khoản vay --</option>
                        <?php foreach ($types as $t): ?>
                            <option value="<?= $t->id ?>" <?= $filters['loan_type_id'] == $t->id ? 'selected' : '' ?>>
                                <?= h($t->name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <select name="department_id" class="form-select">
                        <option value="">-- Phòng ban --</option>
                        <?php foreach ($departments as $d): ?>
                            <option value="<?= $d->id ?>" <?= $filters['department_id'] == $d->id ? 'selected' : '' ?>>
                                <?= h($d->dept_name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill">
                        <i class="fas fa-filter me-1"></i> Lọc
                    </button>
                    <a href="<?= BASE_URL ?>/loan" class="btn btn-light border" title="Đặt lại bộ lọc">
                        <i class="fas fa-undo"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- BẢNG DANH SÁCH KHOẢN VAY -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0 text-dark">
                <i class="fas fa-list-check text-primary me-2"></i>Danh sách Khoản vay & Tạm ứng
                <span class="badge bg-light text-dark ms-2"><?= count($loans) ?> hồ sơ</span>
            </h5>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted small text-uppercase">
                    <tr>
                        <th class="ps-3" style="width: 140px;">Mã khoản vay</th>
                        <th>Nhân viên</th>
                        <th>Loại khoản vay</th>
                        <th class="text-end">Số tiền vay</th>
                        <th class="text-end">EMI / Tháng</th>
                        <th style="min-width: 170px;">Tiến độ thu hồi</th>
                        <th>Ngày lập / Kỳ hạn</th>
                        <th class="text-center">Trạng thái</th>
                        <th class="text-end pe-3">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($loans)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="fas fa-folder-open fs-1 d-block mb-3 opacity-50"></i>
                                Không tìm thấy dữ liệu khoản vay / tạm ứng nào phù hợp với bộ lọc.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($loans as $loan): ?>
                            <?php
                            $paidPercent = ((float)$loan->total_repayment > 0)
                                ? min(100, round(((float)$loan->total_paid / (float)$loan->total_repayment) * 100, 1))
                                : 0;
                            
                            $statusBadge = match($loan->status) {
                                'Pending'   => '<span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i>Chờ duyệt</span>',
                                'Active'    => '<span class="badge bg-info text-white"><i class="fas fa-rotate me-1"></i>Đang trả</span>',
                                'Closed'    => '<span class="badge bg-success text-white"><i class="fas fa-check-circle me-1"></i>Đã tất toán</span>',
                                'Rejected'  => '<span class="badge bg-danger text-white"><i class="fas fa-ban me-1"></i>Từ chối</span>',
                                default     => '<span class="badge bg-secondary">' . h($loan->status) . '</span>'
                            };
                            ?>
                            <tr>
                                <td class="ps-3">
                                    <a href="<?= BASE_URL ?>/loan/show/<?= $loan->id ?>" class="fw-bold text-primary text-decoration-none">
                                        <?= h($loan->loan_code) ?>
                                    </a>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <?php if (!empty($loan->avatar_path)): ?>
                                            <img src="<?= BASE_URL ?>/<?= h($loan->avatar_path) ?>" alt="Avatar" class="rounded-circle me-2" style="width: 34px; height: 34px; object-fit: cover;">
                                        <?php else: ?>
                                            <div class="rounded-circle bg-secondary bg-opacity-25 text-dark fw-bold d-flex align-items-center justify-content-center me-2" style="width: 34px; height: 34px; font-size: 13px;">
                                                <?= mb_substr($loan->employee_name, 0, 1) ?>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <a href="<?= BASE_URL ?>/employee/detail/<?= $loan->employee_id ?>" class="fw-semibold text-dark text-decoration-none d-block">
                                                <?= h($loan->employee_name) ?>
                                            </a>
                                            <small class="text-muted"><?= h($loan->employee_code) ?> &bull; <?= h($loan->dept_name ?? 'N/A') ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        <?= h($loan->type_name ?? 'Khác') ?>
                                    </span>
                                    <?php if ((float)$loan->interest_rate > 0): ?>
                                        <small class="d-block text-muted mt-1"><i class="fas fa-percent me-1 text-primary"></i>Lãi <?= $loan->interest_rate ?>%/năm</small>
                                    <?php else: ?>
                                        <small class="d-block text-success mt-1"><i class="fas fa-shield-halved me-1"></i>Lãi 0%</small>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end fw-bold text-dark">
                                    <?= number_format((float)$loan->amount, 0, ',', '.') ?> ₫
                                </td>
                                <td class="text-end fw-semibold text-primary">
                                    <?= number_format((float)$loan->monthly_emi, 0, ',', '.') ?> ₫
                                </td>
                                <td>
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <small class="text-muted">Đã trả: <strong><?= number_format((float)$loan->total_paid, 0, ',', '.') ?></strong></small>
                                        <small class="fw-bold <?= $paidPercent >= 100 ? 'text-success' : 'text-primary' ?>"><?= $paidPercent ?>%</small>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar <?= $paidPercent >= 100 ? 'bg-success' : 'bg-primary' ?>" 
                                             role="progressbar" 
                                             style="width: <?= $paidPercent ?>%" 
                                             aria-valuenow="<?= $paidPercent ?>" 
                                             aria-valuemin="0" 
                                             aria-valuemax="100"></div>
                                    </div>
                                    <small class="text-muted d-block mt-1">Dư nợ: <strong class="text-danger"><?= number_format((float)$loan->remaining_balance, 0, ',', '.') ?> ₫</strong></small>
                                </td>
                                <td>
                                    <div><?= date('d/m/Y', strtotime($loan->applied_date)) ?></div>
                                    <small class="text-muted"><?= $loan->term_months ?> tháng</small>
                                </td>
                                <td class="text-center">
                                    <?= $statusBadge ?>
                                </td>
                                <td class="text-end pe-3">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= BASE_URL ?>/loan/show/<?= $loan->id ?>" class="btn btn-outline-primary" title="Xem chi tiết & Lịch sử trả">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if ($loan->status === 'Pending'): ?>
                                            <button type="button" class="btn btn-outline-success" title="Phê duyệt nhanh" onclick="openApproveModal(<?= $loan->id ?>, '<?= h($loan->loan_code) ?>', '<?= h($loan->employee_name) ?>', '<?= number_format((float)$loan->amount, 0, ',', '.') ?> ₫')">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        <?php elseif ($loan->status === 'Active'): ?>
                                            <button type="button" class="btn btn-outline-success" title="Ghi nhận hoàn trả nợ" onclick="openRepayModal(<?= $loan->id ?>, '<?= h($loan->loan_code) ?>', '<?= number_format((float)$loan->remaining_balance, 0, ',', '.') ?> ₫', <?= (float)$loan->monthly_emi ?>)">
                                                <i class="fas fa-hand-holding-dollar"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL PHÊ DUYỆT NHANH -->
<div class="modal fade" id="approveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="" method="POST" id="approveForm" class="modal-content border-0 shadow">
            <input type="hidden" name="_csrf_token" value="<?= Session::generateCsrfToken() ?>">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-circle-check me-2"></i>Phê duyệt Khoản vay</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p>Bạn sắp phê duyệt giải ngân cho hồ sơ <strong id="modalApproveCode" class="text-primary"></strong> của nhân viên <strong id="modalApproveEmp"></strong>.</p>
                <div class="alert alert-light border d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted">Số tiền giải ngân:</span>
                    <strong id="modalApproveAmount" class="fs-5 text-success"></strong>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Ngày giải ngân <span class="text-danger">*</span></label>
                    <input type="date" name="disbursement_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Phương thức giải ngân</label>
                    <select name="disbursement_method" class="form-select">
                        <option value="Bank Transfer">Chuyển khoản Ngân hàng (Khuyên dùng)</option>
                        <option value="Cash">Chi tiền mặt tại Thủ quỹ</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy bỏ</button>
                <button type="submit" class="btn btn-success"><i class="fas fa-check me-1"></i> Xác nhận Phê duyệt</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL GHI NHẬN HOÀN TRẢ NHANH -->
<div class="modal fade" id="repayModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="" method="POST" id="repayForm" class="modal-content border-0 shadow">
            <input type="hidden" name="_csrf_token" value="<?= Session::generateCsrfToken() ?>">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-hand-holding-dollar me-2"></i>Ghi nhận Thu hồi / Trả nợ</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted">Khoản vay: <strong id="modalRepayCode" class="text-primary"></strong></span>
                    <span>Dư nợ còn: <strong id="modalRepayBalance" class="text-danger"></strong></span>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Số tiền thanh toán (VNĐ) <span class="text-danger">*</span></label>
                    <input type="number" name="amount" id="modalRepayAmountInput" class="form-control form-control-lg fw-bold text-primary" required min="1000" step="1000">
                    <small class="text-muted">Gợi ý trả theo định kỳ EMI hoặc tất toán phần còn lại.</small>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label fw-semibold">Ngày thanh toán <span class="text-danger">*</span></label>
                        <input type="date" name="payment_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold">Hình thức</label>
                        <select name="payment_method" class="form-select">
                            <option value="Cash">Tiền mặt</option>
                            <option value="Bank Transfer">Chuyển khoản</option>
                            <option value="Payroll Deduction">Khấu trừ lương</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Ghi chú thanh toán</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="Ví dụ: Trả kỳ 2, chuyển khoản qua Vietcombank..."></textarea>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Lưu thanh toán</button>
            </div>
        </form>
    </div>
</div>

<script>
function openApproveModal(id, code, emp, amount) {
    document.getElementById('approveForm').action = '<?= BASE_URL ?>/loan/approve/' + id;
    document.getElementById('modalApproveCode').innerText = code;
    document.getElementById('modalApproveEmp').innerText = emp;
    document.getElementById('modalApproveAmount').innerText = amount;
    new bootstrap.Modal(document.getElementById('approveModal')).show();
}

function openRepayModal(id, code, balance, defaultEmi) {
    document.getElementById('repayForm').action = '<?= BASE_URL ?>/loan/repay/' + id;
    document.getElementById('modalRepayCode').innerText = code;
    document.getElementById('modalRepayBalance').innerText = balance;
    document.getElementById('modalRepayAmountInput').value = defaultEmi || '';
    new bootstrap.Modal(document.getElementById('repayModal')).show();
}
</script>
