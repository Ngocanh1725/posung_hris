<?php
/**
 * ============================================================
 *  POSUNG HRIS – View: loan/show.php
 * ============================================================
 *  Chi tiết Hồ sơ Khoản vay & Lịch sử Thu hồi Hoàn trả
 * ============================================================
 */

$paidPercent = ((float)$loan->total_repayment > 0)
    ? min(100, round(((float)$loan->total_paid / (float)$loan->total_repayment) * 100, 1))
    : 0;

$statusBadge = match($loan->status) {
    'Pending'   => '<span class="badge bg-warning text-dark px-3 py-2 fs-6"><i class="fas fa-clock me-1"></i>Chờ phê duyệt</span>',
    'Active'    => '<span class="badge bg-info text-white px-3 py-2 fs-6"><i class="fas fa-rotate me-1"></i>Đang trả nợ</span>',
    'Closed'    => '<span class="badge bg-success text-white px-3 py-2 fs-6"><i class="fas fa-check-circle me-1"></i>Đã tất toán xong</span>',
    'Rejected'  => '<span class="badge bg-danger text-white px-3 py-2 fs-6"><i class="fas fa-ban me-1"></i>Đã bị từ chối</span>',
    default     => '<span class="badge bg-secondary px-3 py-2 fs-6">' . h($loan->status) . '</span>'
};
?>
<div class="content-wrapper">
    <!-- TIÊU ĐỀ & THANH CÔNG CỤ -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div class="d-flex align-items-center gap-3">
            <a href="<?= BASE_URL ?>/loan" class="btn btn-light border rounded-circle p-2" style="width: 40px; height: 40px; display: inline-flex; align-items: center; justify-content: center;">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h3 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                    <?= h($loan->loan_code) ?>
                    <?= $statusBadge ?>
                </h3>
                <small class="text-muted">Lập ngày <?= date('d/m/Y', strtotime($loan->applied_date)) ?> &bull; Loại: <strong class="text-dark"><?= h($loan->type_name) ?></strong></small>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
                <i class="fas fa-print me-1"></i> In Phiếu đề xuất
            </button>

            <?php if ($loan->status === 'Pending'): ?>
                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                    <i class="fas fa-times me-1"></i> Từ chối
                </button>
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#approveModal">
                    <i class="fas fa-check me-1"></i> Phê duyệt & Giải ngân
                </button>
            <?php elseif ($loan->status === 'Active'): ?>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#repayModal">
                    <i class="fas fa-hand-holding-dollar me-1"></i> Ghi nhận Thu hồi / Trả nợ
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- KHỐI TIẾN ĐỘ THU HỒI NỢ VAY -->
    <div class="card border-0 shadow-sm rounded-3 p-4 mb-4 bg-white">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="fw-semibold text-muted text-uppercase small">Tiến độ thanh toán hoàn trả</span>
                    <span class="fs-5 fw-bold <?= $paidPercent >= 100 ? 'text-success' : 'text-primary' ?>">
                        <?= $paidPercent ?>% Hoàn tất
                    </span>
                </div>
                <div class="progress" style="height: 12px; border-radius: 8px;">
                    <div class="progress-bar <?= $paidPercent >= 100 ? 'bg-success' : 'bg-primary' ?> progress-bar-striped progress-bar-animated" 
                         role="progressbar" 
                         style="width: <?= $paidPercent ?>%" 
                         aria-valuenow="<?= $paidPercent ?>" 
                         aria-valuemin="0" 
                         aria-valuemax="100"></div>
                </div>
                <div class="d-flex justify-content-between text-muted small mt-2">
                    <span>Đã thanh toán: <strong class="text-success"><?= number_format((float)$loan->total_paid, 0, ',', '.') ?> ₫</strong></span>
                    <span>Dư nợ còn lại: <strong class="text-danger"><?= number_format((float)$loan->remaining_balance, 0, ',', '.') ?> ₫</strong></span>
                    <span>Tổng số tiền: <strong><?= number_format((float)$loan->total_repayment, 0, ',', '.') ?> ₫</strong></span>
                </div>
            </div>

            <div class="col-lg-4 mt-3 mt-lg-0 border-start ps-lg-4 text-center text-lg-start">
                <span class="text-muted small text-uppercase fw-semibold d-block">Trừ lương hàng tháng (EMI)</span>
                <h3 class="fw-bold text-primary mb-1 mt-1"><?= number_format((float)$loan->monthly_emi, 0, ',', '.') ?> ₫</h3>
                <small class="text-muted"><i class="fas fa-calendar-check me-1"></i>Thời hạn khấu trừ: <?= $loan->term_months ?> tháng</small>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <!-- CỘT TRÁI: THÔNG TIN HỒ SƠ & NGƯỜI ĐỀ XUẤT -->
        <div class="col-lg-7">
            <!-- THÔNG TIN NHÂN VIÊN VAY -->
            <div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
                <h5 class="fw-bold text-dark mb-3 border-bottom pb-2">
                    <i class="fas fa-id-card text-primary me-2"></i>Người Đề xuất Vay / Tạm ứng
                </h5>

                <div class="d-flex align-items-center mb-3">
                    <?php if (!empty($loan->avatar_path)): ?>
                        <img src="<?= BASE_URL ?>/<?= h($loan->avatar_path) ?>" alt="Avatar" class="rounded-circle me-3 border" style="width: 54px; height: 54px; object-fit: cover;">
                    <?php else: ?>
                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary fw-bold d-flex align-items-center justify-content-center me-3 fs-4" style="width: 54px; height: 54px;">
                            <?= mb_substr($loan->employee_name, 0, 1) ?>
                        </div>
                    <?php endif; ?>
                    <div>
                        <h5 class="fw-bold text-dark mb-0">
                            <a href="<?= BASE_URL ?>/employee/detail/<?= $loan->employee_id ?>" class="text-dark text-decoration-none">
                                <?= h($loan->employee_name) ?>
                            </a>
                        </h5>
                        <div class="text-muted small">
                            Mã NV: <span class="badge bg-light text-dark border"><?= h($loan->employee_code) ?></span> &bull; 
                            Phòng ban: <strong><?= h($loan->dept_name ?? 'N/A') ?></strong> &bull; 
                            Vị trí: <?= h($loan->pos_title ?? 'N/A') ?>
                        </div>
                    </div>
                </div>

                <div class="row g-2 small text-muted pt-2 border-top">
                    <div class="col-6">
                        <i class="fas fa-phone me-1"></i> <?= h($loan->employee_phone ?: 'Chưa có SĐT') ?>
                    </div>
                    <div class="col-6">
                        <i class="fas fa-envelope me-1"></i> <?= h($loan->employee_email ?: 'Chưa có Email') ?>
                    </div>
                </div>
            </div>

            <!-- NỘI DUNG LÝ DO & PHÊ DUYỆT -->
            <div class="card border-0 shadow-sm rounded-3 p-4">
                <h5 class="fw-bold text-dark mb-3 border-bottom pb-2">
                    <i class="fas fa-file-lines text-primary me-2"></i>Mục đích & Lịch sử Phê duyệt
                </h5>

                <div class="mb-3">
                    <label class="text-muted small text-uppercase fw-semibold d-block">Lý do xin vay / tạm ứng:</label>
                    <div class="p-3 bg-light rounded-3 text-dark mt-1">
                        <?= nl2br(h($loan->reason)) ?>
                    </div>
                </div>

                <?php if (!empty($loan->notes)): ?>
                    <div class="mb-3">
                        <label class="text-muted small text-uppercase fw-semibold d-block">Ghi chú bổ sung:</label>
                        <p class="text-muted mb-0 mt-1"><?= nl2br(h($loan->notes)) ?></p>
                    </div>
                <?php endif; ?>

                <?php if ($loan->status === 'Rejected' && !empty($loan->rejected_reason)): ?>
                    <div class="alert alert-danger border-0 rounded-3 mb-3">
                        <h6 class="fw-bold mb-1"><i class="fas fa-circle-exclamation me-1"></i>Lý do từ chối giải ngân:</h6>
                        <p class="mb-0"><?= nl2br(h($loan->rejected_reason)) ?></p>
                    </div>
                <?php endif; ?>

                <div class="row g-3 pt-3 border-top small">
                    <div class="col-md-6">
                        <span class="text-muted d-block">Người phê duyệt:</span>
                        <strong class="text-dark"><?= h($loan->approver_fullname ?: ($loan->approver_username ?: 'Chưa phê duyệt')) ?></strong>
                    </div>
                    <div class="col-md-6">
                        <span class="text-muted d-block">Thời điểm phê duyệt:</span>
                        <strong class="text-dark"><?= $loan->approved_date ? date('d/m/Y H:i', strtotime($loan->approved_date)) : '---' ?></strong>
                    </div>
                    <div class="col-md-6">
                        <span class="text-muted d-block">Ngày giải ngân:</span>
                        <strong class="text-dark"><?= $loan->disbursement_date ? date('d/m/Y', strtotime($loan->disbursement_date)) : '---' ?></strong>
                    </div>
                    <div class="col-md-6">
                        <span class="text-muted d-block">Phương thức giải ngân:</span>
                        <strong class="text-dark"><?= h($loan->disbursement_method ?? 'Bank Transfer') ?></strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- CỘT PHẢI: BẢNG SỐ LIỆU TÀI CHÍNH -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-3 p-4 bg-white h-100">
                <h5 class="fw-bold text-dark mb-3 border-bottom pb-2">
                    <i class="fas fa-scale-balanced text-primary me-2"></i>Thông số Hợp đồng Vay
                </h5>

                <table class="table table-sm table-borderless align-middle mb-0">
                    <tbody>
                        <tr>
                            <td class="text-muted py-2">Mã hợp đồng:</td>
                            <td class="text-end fw-bold text-dark py-2"><?= h($loan->loan_code) ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted py-2">Loại khoản vay:</td>
                            <td class="text-end fw-semibold text-dark py-2"><?= h($loan->type_name) ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted py-2">Số tiền gốc vay:</td>
                            <td class="text-end fw-bold text-dark fs-5 py-2"><?= number_format((float)$loan->amount, 0, ',', '.') ?> ₫</td>
                        </tr>
                        <tr>
                            <td class="text-muted py-2">Lãi suất áp dụng:</td>
                            <td class="text-end fw-semibold text-primary py-2"><?= $loan->interest_rate ?>% / năm</td>
                        </tr>
                        <tr>
                            <td class="text-muted py-2">Kỳ hạn khấu trừ:</td>
                            <td class="text-end fw-semibold text-dark py-2"><?= $loan->term_months ?> tháng</td>
                        </tr>
                        <tr>
                            <td class="text-muted py-2">Tiền lãi ước tính:</td>
                            <td class="text-end fw-semibold text-danger py-2">
                                <?= number_format(max(0, (float)$loan->total_repayment - (float)$loan->amount), 0, ',', '.') ?> ₫
                            </td>
                        </tr>
                        <tr class="border-top">
                            <td class="text-dark fw-bold py-2">Tổng nghĩa vụ hoàn trả:</td>
                            <td class="text-end fw-bold text-primary fs-5 py-2"><?= number_format((float)$loan->total_repayment, 0, ',', '.') ?> ₫</td>
                        </tr>
                        <tr class="bg-light rounded">
                            <td class="text-success fw-bold py-2 ps-2">Đã thu hồi:</td>
                            <td class="text-end fw-bold text-success py-2 pe-2"><?= number_format((float)$loan->total_paid, 0, ',', '.') ?> ₫</td>
                        </tr>
                        <tr class="bg-light rounded">
                            <td class="text-danger fw-bold py-2 ps-2">Dư nợ còn lại:</td>
                            <td class="text-end fw-bold text-danger fs-5 py-2 pe-2"><?= number_format((float)$loan->remaining_balance, 0, ',', '.') ?> ₫</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- BẢNG LỊCH SỬ THANH TOÁN / THU HỒI NỢ -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0 text-dark">
                <i class="fas fa-history text-primary me-2"></i>Lịch sử Giao dịch Trả nợ & Khấu trừ Lương
                <span class="badge bg-light text-dark ms-2"><?= count($loan->repayments ?? []) ?> đợt</span>
            </h5>

            <?php if ($loan->status === 'Active'): ?>
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#repayModal">
                    <i class="fas fa-plus me-1"></i> Ghi nhận đợt trả mới
                </button>
            <?php endif; ?>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted small text-uppercase">
                    <tr>
                        <th class="ps-3" style="width: 60px;">#</th>
                        <th>Ngày thanh toán</th>
                        <th class="text-end">Số tiền trả</th>
                        <th class="text-end">Tiền gốc</th>
                        <th class="text-end">Tiền lãi</th>
                        <th class="text-end">Dư nợ sau trả</th>
                        <th>Hình thức</th>
                        <th>Loại trừ</th>
                        <th>Người ghi nhận</th>
                        <th class="pe-3">Ghi chú</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($loan->repayments)): ?>
                        <tr>
                            <td colspan="10" class="text-center py-4 text-muted">
                                <i class="fas fa-receipt fs-3 d-block mb-2 opacity-50"></i>
                                Chưa có phát sinh đợt hoàn trả / khấu trừ nào cho khoản vay này.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($loan->repayments as $idx => $rep): ?>
                            <tr>
                                <td class="ps-3 text-muted"><?= $idx + 1 ?></td>
                                <td class="fw-semibold text-dark">
                                    <?= date('d/m/Y', strtotime($rep->payment_date)) ?>
                                </td>
                                <td class="text-end fw-bold text-success">
                                    +<?= number_format((float)$rep->amount, 0, ',', '.') ?> ₫
                                </td>
                                <td class="text-end text-muted">
                                    <?= number_format((float)$rep->principal_amount, 0, ',', '.') ?> ₫
                                </td>
                                <td class="text-end text-muted">
                                    <?= number_format((float)$rep->interest_amount, 0, ',', '.') ?> ₫
                                </td>
                                <td class="text-end fw-bold text-danger">
                                    <?= number_format((float)$rep->balance_after, 0, ',', '.') ?> ₫
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        <?= h($rep->payment_method) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($rep->payment_type === 'Auto'): ?>
                                        <span class="badge bg-info text-white"><i class="fas fa-robot me-1"></i>Trừ lương tự động</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary"><i class="fas fa-hand-holding me-1"></i>Thủ công</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-muted small">
                                    <?= h($rep->recorder_full_name ?: ($rep->recorder_name ?: 'Hệ thống')) ?>
                                </td>
                                <td class="pe-3 text-muted small">
                                    <?= h($rep->notes ?: '---') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL PHÊ DUYỆT -->
<div class="modal fade" id="approveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="<?= BASE_URL ?>/loan/approve/<?= $loan->id ?>" method="POST" class="modal-content border-0 shadow">
            <input type="hidden" name="_csrf_token" value="<?= Session::generateCsrfToken() ?>">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-circle-check me-2"></i>Phê duyệt & Giải ngân</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p>Bạn sắp xác nhận phê duyệt giải ngân hồ sơ <strong class="text-primary"><?= h($loan->loan_code) ?></strong> của nhân viên <strong><?= h($loan->employee_name) ?></strong>.</p>
                <div class="alert alert-light border d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted">Số tiền giải ngân:</span>
                    <strong class="fs-5 text-success"><?= number_format((float)$loan->amount, 0, ',', '.') ?> ₫</strong>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Ngày giải ngân <span class="text-danger">*</span></label>
                    <input type="date" name="disbursement_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Phương thức giải ngân</label>
                    <select name="disbursement_method" class="form-select">
                        <option value="Bank Transfer" <?= ($loan->disbursement_method === 'Bank Transfer') ? 'selected' : '' ?>>Chuyển khoản Ngân hàng (Khuyên dùng)</option>
                        <option value="Cash" <?= ($loan->disbursement_method === 'Cash') ? 'selected' : '' ?>>Chi tiền mặt tại Thủ quỹ</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy bỏ</button>
                <button type="submit" class="btn btn-success"><i class="fas fa-check me-1"></i> Xác nhận Giải ngân</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL TỪ CHỐI -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="<?= BASE_URL ?>/loan/reject/<?= $loan->id ?>" method="POST" class="modal-content border-0 shadow">
            <input type="hidden" name="_csrf_token" value="<?= Session::generateCsrfToken() ?>">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-circle-xmark me-2"></i>Từ chối Đề xuất Vay</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p>Vui lòng nêu rõ lý do từ chối để nhân viên <strong><?= h($loan->employee_name) ?></strong> nắm thông tin.</p>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Lý do từ chối <span class="text-danger">*</span></label>
                    <textarea name="rejected_reason" class="form-control" rows="3" placeholder="Ví dụ: Vượt quá định mức quy định, kỳ hạn đề xuất không phù hợp..." required></textarea>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy bỏ</button>
                <button type="submit" class="btn btn-danger"><i class="fas fa-ban me-1"></i> Xác nhận Từ chối</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL GHI NHẬN HOÀN TRẢ / TRẢ NỢ -->
<div class="modal fade" id="repayModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="<?= BASE_URL ?>/loan/repay/<?= $loan->id ?>" method="POST" class="modal-content border-0 shadow">
            <input type="hidden" name="_csrf_token" value="<?= Session::generateCsrfToken() ?>">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-hand-holding-dollar me-2"></i>Ghi nhận Thu hồi / Hoàn trả nợ</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted">Khoản vay: <strong class="text-primary"><?= h($loan->loan_code) ?></strong></span>
                    <span>Dư nợ còn lại: <strong class="text-danger"><?= number_format((float)$loan->remaining_balance, 0, ',', '.') ?> ₫</strong></span>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Số tiền thanh toán (VNĐ) <span class="text-danger">*</span></label>
                    <input type="number" name="amount" class="form-control form-control-lg fw-bold text-primary" required min="1000" max="<?= (float)$loan->remaining_balance ?>" step="1000" value="<?= min((float)$loan->monthly_emi, (float)$loan->remaining_balance) ?>">
                    <small class="text-muted">Mặc định theo định kỳ EMI hàng tháng (<?= number_format((float)$loan->monthly_emi, 0, ',', '.') ?> ₫) hoặc trả hết dư nợ.</small>
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
                    <textarea name="notes" class="form-control" rows="2" placeholder="Ví dụ: Hoàn trả đợt 2 qua tài khoản Vietcombank..."></textarea>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Lưu thanh toán</button>
            </div>
        </form>
    </div>
</div>
