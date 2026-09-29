<?php
/**
 * ============================================================
 *  POSUNG HRIS – View: expense/show_claim.php
 * ============================================================
 *  Chi tiết Bảng Thanh Quyết toán Chi phí Công tác & Dự án
 * ============================================================
 */

$statusBadge = match($claim->status) {
    'Submitted' => '<span class="badge bg-warning text-dark px-3 py-2 fs-6"><i class="fas fa-clock me-1"></i>Chờ phê duyệt</span>',
    'Approved'  => '<span class="badge bg-info text-white px-3 py-2 fs-6"><i class="fas fa-check me-1"></i>Đã duyệt (Chờ chi trả)</span>',
    'Paid'      => '<span class="badge bg-success text-white px-3 py-2 fs-6"><i class="fas fa-money-bill-check me-1"></i>Đã thanh toán</span>',
    'Rejected'  => '<span class="badge bg-danger text-white px-3 py-2 fs-6"><i class="fas fa-ban me-1"></i>Bị từ chối</span>',
    default     => '<span class="badge bg-secondary px-3 py-2 fs-6">' . h($claim->status) . '</span>'
};
?>
<div class="content-wrapper">
    <!-- TIÊU ĐỀ & THANH CÔNG CỤ -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div class="d-flex align-items-center gap-3">
            <a href="<?= BASE_URL ?>/expense?tab=claims" class="btn btn-light border rounded-circle p-2" style="width: 40px; height: 40px; display: inline-flex; align-items: center; justify-content: center;">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h3 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                    <?= h($claim->claim_code) ?>
                    <?= $statusBadge ?>
                </h3>
                <small class="text-muted">Hồ sơ quyết toán: <strong class="text-dark"><?= h($claim->title) ?></strong> &bull; Lập ngày <?= date('d/m/Y', strtotime($claim->submitted_date)) ?></small>
            </div>
        </div>

        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>/expense/printClaim/<?= $claim->id ?>" target="_blank" class="btn btn-outline-secondary">
                <i class="fas fa-print me-1"></i> In Biên bản Quyết toán
            </a>

            <?php if ($claim->status === 'Submitted'): ?>
                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                    <i class="fas fa-times me-1"></i> Từ chối
                </button>
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#approveModal">
                    <i class="fas fa-check me-1"></i> Phê duyệt Quyết toán
                </button>
            <?php elseif ($claim->status === 'Approved'): ?>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#payModal">
                    <i class="fas fa-money-check-dollar me-1"></i> Xác nhận Chi trả Tiền
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- TỔNG QUAN TÀI CHÍNH QUYẾT TOÁN -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
                <span class="text-muted small fw-semibold text-uppercase">Tổng chi phí thực tế</span>
                <h3 class="fw-bold text-dark mb-0 mt-1"><?= number_format((float)$claim->total_amount, 0, ',', '.') ?> ₫</h3>
                <small class="text-muted"><?= count($claim->items ?? []) ?> hóa đơn / khoản chi</small>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
                <span class="text-muted small fw-semibold text-uppercase">Khấu trừ Tạm ứng</span>
                <h3 class="fw-bold text-primary mb-0 mt-1"><?= number_format((float)$claim->advance_deducted, 0, ',', '.') ?> ₫</h3>
                <small class="text-muted">
                    <?= !empty($claim->travel_request_code) ? 'Thuộc ' . h($claim->travel_request_code) : 'Không có tạm ứng' ?>
                </small>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white border-start border-4 border-success">
                <span class="text-muted small fw-semibold text-uppercase">
                    <?= ((float)$claim->net_payable >= 0) ? 'Số tiền Thực nhận (Chi bù)' : 'Số tiền Hoàn trả lại quỹ' ?>
                </span>
                <h3 class="fw-bold <?= ((float)$claim->net_payable >= 0) ? 'text-success' : 'text-danger' ?> mb-0 mt-1">
                    <?= number_format(abs((float)$claim->net_payable), 0, ',', '.') ?> ₫
                </h3>
                <small class="text-muted">Số tiền thực tế thanh toán qua ngân hàng/thủ quỹ</small>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <!-- CỘT TRÁI: THÔNG TIN HỒ SƠ & DỰ ÁN -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
                <h5 class="fw-bold text-dark mb-3 border-bottom pb-2">
                    <i class="fas fa-file-invoice text-primary me-2"></i>Thông tin Hồ sơ Quyết toán
                </h5>

                <table class="table table-borderless table-sm mb-0">
                    <tbody>
                        <tr>
                            <td class="text-muted" style="width: 170px;">Mã quyết toán:</td>
                            <td class="fw-bold text-primary"><?= h($claim->claim_code) ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Tiêu đề:</td>
                            <td class="fw-semibold text-dark"><?= h($claim->title) ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Phân loại chi phí:</td>
                            <td><span class="badge bg-light text-dark border"><?= h($claim->category) ?></span></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Dự án công trình:</td>
                            <td class="fw-semibold text-primary">
                                <?= !empty($claim->project_name) ? h($claim->project_name) . ' (' . h($claim->project_code) . ')' : 'Chi phí quản lý văn phòng' ?>
                            </td>
                        </tr>
                        <?php if (!empty($claim->travel_request_code)): ?>
                            <tr>
                                <td class="text-muted">Chuyến công tác:</td>
                                <td>
                                    <a href="<?= BASE_URL ?>/expense/showTravel/<?= $claim->travel_request_id ?>" class="text-decoration-none fw-semibold">
                                        <i class="fas fa-link me-1"></i><?= h($claim->travel_request_code) ?> (<?= h($claim->to_location) ?>)
                                    </a>
                                </td>
                            </tr>
                        <?php endif; ?>
                        <?php if (!empty($claim->notes)): ?>
                            <tr>
                                <td class="text-muted">Ghi chú:</td>
                                <td class="text-muted"><?= nl2br(h($claim->notes)) ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <?php if ($claim->status === 'Rejected' && !empty($claim->rejected_reason)): ?>
                    <div class="alert alert-danger border-0 rounded-3 mt-3 mb-0">
                        <strong class="d-block mb-1"><i class="fas fa-circle-exclamation me-1"></i>Lý do từ chối:</strong>
                        <?= nl2br(h($claim->rejected_reason)) ?>
                    </div>
                <?php endif; ?>

                <div class="row g-3 pt-3 border-top mt-2 small text-muted">
                    <div class="col-md-6">
                        <span>Người phê duyệt:</span>
                        <strong class="text-dark d-block"><?= h($claim->approver_fullname ?: ($claim->approver_username ?: 'Chưa phê duyệt')) ?></strong>
                        <small><?= $claim->approved_date ? date('d/m/Y H:i', strtotime($claim->approved_date)) : '' ?></small>
                    </div>
                    <div class="col-md-6">
                        <span>Chi trả hoàn tất:</span>
                        <strong class="text-dark d-block"><?= h($claim->payer_fullname ?: ($claim->payer_username ?: ($claim->status === 'Paid' ? 'Đã chi' : 'Chưa chi trả'))) ?></strong>
                        <small><?= $claim->paid_date ? 'Ngày ' . date('d/m/Y', strtotime($claim->paid_date)) . ' (' . h($claim->payment_method) . ')' : '' ?></small>
                    </div>
                </div>
            </div>
        </div>

        <!-- CỘT PHẢI: NGƯỜI ĐỀ NGHỊ & TÀI KHOẢN NGÂN HÀNG -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-3 p-4 bg-white mb-4">
                <h5 class="fw-bold text-dark mb-3 border-bottom pb-2">
                    <i class="fas fa-user-check text-primary me-2"></i>Người Đề nghị & Tài khoản Nhận tiền
                </h5>

                <div class="d-flex align-items-center mb-3">
                    <?php if (!empty($claim->avatar_path)): ?>
                        <img src="<?= BASE_URL ?>/<?= h($claim->avatar_path) ?>" alt="Avatar" class="rounded-circle me-3 border" style="width: 48px; height: 48px; object-fit: cover;">
                    <?php else: ?>
                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary fw-bold d-flex align-items-center justify-content-center me-3 fs-4" style="width: 48px; height: 48px;">
                            <?= mb_substr($claim->employee_name, 0, 1) ?>
                        </div>
                    <?php endif; ?>
                    <div>
                        <h6 class="fw-bold text-dark mb-0">
                            <a href="<?= BASE_URL ?>/employee/detail/<?= $claim->employee_id ?>" class="text-dark text-decoration-none">
                                <?= h($claim->employee_name) ?>
                            </a>
                        </h6>
                        <small class="text-muted"><?= h($claim->employee_code) ?> &bull; <?= h($claim->pos_title ?? 'N/A') ?></small>
                    </div>
                </div>

                <div class="p-3 bg-light rounded-3 small">
                    <div class="text-muted mb-1 text-uppercase fw-semibold" style="font-size: 11px;">Thông tin Chuyển khoản thanh toán:</div>
                    <div class="d-flex justify-content-between mb-1">
                        <span>Số tài khoản:</span>
                        <strong class="text-dark"><?= h($claim->bank_account_no ?: 'Chưa cập nhật') ?></strong>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span>Ngân hàng:</span>
                        <strong class="text-dark"><?= h($claim->bank_name ?: '---') ?></strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Chi nhánh:</span>
                        <span class="text-muted"><?= h($claim->bank_branch ?: '---') ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- BẢNG CHI TIẾT CÁC HẠNG MỤC CHI & BIÊN LAI -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0 text-dark">
                <i class="fas fa-receipt text-primary me-2"></i>Bảng kê Chi tiết các Khoản chi & Hóa đơn
                <span class="badge bg-light text-dark ms-2"><?= count($claim->items ?? []) ?> khoản</span>
            </h5>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted small text-uppercase">
                    <tr>
                        <th class="ps-3" style="width: 50px;">#</th>
                        <th style="width: 150px;">Loại chi</th>
                        <th>Nội dung chi phí / Diễn giải</th>
                        <th>Ngày chi</th>
                        <th class="text-end">Số tiền</th>
                        <th class="text-center" style="width: 140px;">Chứng từ / Bill</th>
                        <th class="pe-3">Ghi chú</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($claim->items)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                Không có chi tiết khoản chi nào.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($claim->items as $idx => $item): ?>
                            <?php
                            $catIcon = match($item->category) {
                                'Transport' => 'fas fa-plane-departure text-primary',
                                'Hotel'     => 'fas fa-hotel text-info',
                                'Meal'      => 'fas fa-utensils text-success',
                                'Fuel'      => 'fas fa-gas-pump text-warning',
                                'Material'  => 'fas fa-screwdriver-wrench text-danger',
                                default     => 'fas fa-tag text-secondary'
                            };
                            ?>
                            <tr>
                                <td class="ps-3 text-muted"><?= $idx + 1 ?></td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        <i class="<?= $catIcon ?> me-1"></i><?= h($item->category) ?>
                                    </span>
                                </td>
                                <td class="fw-semibold text-dark">
                                    <?= h($item->description) ?>
                                </td>
                                <td class="text-muted small">
                                    <?= date('d/m/Y', strtotime($item->expense_date)) ?>
                                </td>
                                <td class="text-end fw-bold text-dark fs-6">
                                    <?= number_format((float)$item->amount, 0, ',', '.') ?> ₫
                                </td>
                                <td class="text-center">
                                    <?php if (!empty($item->receipt_path)): ?>
                                        <a href="<?= BASE_URL ?>/<?= h($item->receipt_path) ?>" target="_blank" class="btn btn-sm btn-outline-primary" title="Xem hóa đơn/biên lai">
                                            <i class="fas fa-file-image me-1"></i> Xem bill
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted small">Không đính kèm</span>
                                    <?php endif; ?>
                                </td>
                                <td class="pe-3 text-muted small">
                                    <?= h($item->notes ?: '---') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <!-- DÒNG TỔNG CỘNG -->
                        <tr class="table-light fw-bold">
                            <td colspan="4" class="text-end ps-3 text-uppercase">Tổng chi phí thực tế:</td>
                            <td class="text-end text-primary fs-5"><?= number_format((float)$claim->total_amount, 0, ',', '.') ?> ₫</td>
                            <td colspan="2"></td>
                        </tr>
                        <tr class="table-light">
                            <td colspan="4" class="text-end ps-3 text-muted">Trừ tiền tạm ứng:</td>
                            <td class="text-end text-muted">-<?= number_format((float)$claim->advance_deducted, 0, ',', '.') ?> ₫</td>
                            <td colspan="2"></td>
                        </tr>
                        <tr class="table-light fw-bold">
                            <td colspan="4" class="text-end ps-3 text-success text-uppercase">Số tiền thực thanh toán:</td>
                            <td class="text-end text-success fs-5"><?= number_format((float)$claim->net_payable, 0, ',', '.') ?> ₫</td>
                            <td colspan="2"></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL PHÊ DUYỆT -->
<div class="modal fade" id="approveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="<?= BASE_URL ?>/expense/approveClaim/<?= $claim->id ?>" method="POST" class="modal-content border-0 shadow">
            <input type="hidden" name="_csrf_token" value="<?= Session::generateCsrfToken() ?>">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-circle-check me-2"></i>Phê duyệt Bảng Quyết toán</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <p>Xác nhận duyệt bảng quyết toán <strong><?= h($claim->claim_code) ?></strong> của nhân viên <strong><?= h($claim->employee_name) ?></strong>.</p>
                <div class="d-flex justify-content-between p-3 bg-light rounded-3 mb-2">
                    <span class="text-muted">Tổng chi phí:</span>
                    <strong class="text-dark"><?= number_format((float)$claim->total_amount, 0, ',', '.') ?> ₫</strong>
                </div>
                <div class="d-flex justify-content-between p-3 bg-light rounded-3">
                    <span class="text-muted">Số tiền thực thanh toán:</span>
                    <strong class="text-success fs-5"><?= number_format((float)$claim->net_payable, 0, ',', '.') ?> ₫</strong>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="submit" class="btn btn-success"><i class="fas fa-check me-1"></i> Xác nhận Phê duyệt</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL TỪ CHỐI -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="<?= BASE_URL ?>/expense/rejectClaim/<?= $claim->id ?>" method="POST" class="modal-content border-0 shadow">
            <input type="hidden" name="_csrf_token" value="<?= Session::generateCsrfToken() ?>">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-ban me-2"></i>Từ chối Bảng Quyết toán</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <label class="form-label fw-semibold">Lý do từ chối quyết toán <span class="text-danger">*</span></label>
                <textarea name="rejected_reason" class="form-control" rows="3" placeholder="Nhập lý do hóa đơn không hợp lệ, vượt định mức..." required></textarea>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="submit" class="btn btn-danger"><i class="fas fa-ban me-1"></i> Xác nhận Từ chối</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL CHI TRẢ -->
<div class="modal fade" id="payModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="<?= BASE_URL ?>/expense/markPaid/<?= $claim->id ?>" method="POST" class="modal-content border-0 shadow">
            <input type="hidden" name="_csrf_token" value="<?= Session::generateCsrfToken() ?>">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-money-check-dollar me-2"></i>Xác nhận Chi trả Tiền Quyết toán</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="d-flex justify-content-between p-3 bg-light rounded-3 mb-3">
                    <span class="text-muted">Số tiền chi trả:</span>
                    <strong class="text-success fs-5"><?= number_format((float)$claim->net_payable, 0, ',', '.') ?> ₫</strong>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Ngày chi tiền <span class="text-danger">*</span></label>
                    <input type="date" name="paid_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Hình thức chi</label>
                    <select name="payment_method" class="form-select">
                        <option value="Bank Transfer">Chuyển khoản Ngân hàng (Khuyên dùng)</option>
                        <option value="Cash">Chi tiền mặt qua Thủ quỹ</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-check me-1"></i> Xác nhận Đã thanh toán</button>
            </div>
        </form>
    </div>
</div>
