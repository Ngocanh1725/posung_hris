<?php
/**
 * ============================================================
 *  POSUNG HRIS – View: expense/show_travel.php
 * ============================================================
 *  Chi tiết Đề xuất & Kế hoạch Công tác
 * ============================================================
 */

$statusBadge = match($travel->status) {
    'Pending'   => '<span class="badge bg-warning text-dark px-3 py-2 fs-6"><i class="fas fa-clock me-1"></i>Chờ phê duyệt</span>',
    'Approved'  => '<span class="badge bg-info text-white px-3 py-2 fs-6"><i class="fas fa-check me-1"></i>Đã phê duyệt</span>',
    'Completed' => '<span class="badge bg-success text-white px-3 py-2 fs-6"><i class="fas fa-circle-check me-1"></i>Đã hoàn tất</span>',
    'Rejected'  => '<span class="badge bg-danger text-white px-3 py-2 fs-6"><i class="fas fa-ban me-1"></i>Đã bị từ chối</span>',
    default     => '<span class="badge bg-secondary px-3 py-2 fs-6">' . h($travel->status) . '</span>'
};

$days = max(1, round((strtotime($travel->return_date) - strtotime($travel->departure_date)) / 86400) + 1);
?>
<div class="content-wrapper">
    <!-- TIÊU ĐỀ & THANH CÔNG CỤ -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div class="d-flex align-items-center gap-3">
            <a href="<?= BASE_URL ?>/expense?tab=travel" class="btn btn-light border rounded-circle p-2" style="width: 40px; height: 40px; display: inline-flex; align-items: center; justify-content: center;">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h3 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                    <?= h($travel->request_code) ?>
                    <?= $statusBadge ?>
                </h3>
                <small class="text-muted">Kế hoạch công tác: <strong class="text-dark"><?= h($travel->to_location) ?></strong> (<?= $days ?> ngày)</small>
            </div>
        </div>

        <div class="d-flex gap-2">
            <?php if ($travel->status === 'Pending'): ?>
                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                    <i class="fas fa-times me-1"></i> Từ chối
                </button>
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#approveModal">
                    <i class="fas fa-check me-1"></i> Phê duyệt Kế hoạch
                </button>
            <?php elseif (in_array($travel->status, ['Approved', 'Completed'])): ?>
                <a href="<?= BASE_URL ?>/expense/createClaim?travel_id=<?= $travel->id ?>" class="btn btn-primary">
                    <i class="fas fa-receipt me-1"></i> Lập Quyết toán Chi phí cho Chuyến này
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- TỔNG QUAN TÀI CHÍNH CÔNG TÁC -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
                <span class="text-muted small fw-semibold text-uppercase">Ngân sách dự kiến</span>
                <h3 class="fw-bold text-dark mb-0 mt-1"><?= number_format((float)$travel->estimated_budget, 0, ',', '.') ?> ₫</h3>
                <small class="text-muted">Định mức cho phép</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
                <span class="text-muted small fw-semibold text-uppercase">Số tiền Tạm ứng</span>
                <h3 class="fw-bold text-primary mb-0 mt-1"><?= number_format((float)$travel->advance_amount, 0, ',', '.') ?> ₫</h3>
                <small class="text-primary">Đã/sẽ chi nhận trước</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
                <span class="text-muted small fw-semibold text-uppercase">Số bảng quyết toán liên quan</span>
                <h3 class="fw-bold text-success mb-0 mt-1"><?= count($travel->claims ?? []) ?></h3>
                <small class="text-muted">Hồ sơ thanh toán thực tế</small>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <!-- CỘT TRÁI: THÔNG TIN HỒ SƠ & LỘ TRÌNH -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
                <h5 class="fw-bold text-dark mb-3 border-bottom pb-2">
                    <i class="fas fa-route text-primary me-2"></i>Chi tiết Kế hoạch & Lịch trình
                </h5>

                <table class="table table-borderless table-sm mb-3">
                    <tbody>
                        <tr>
                            <td class="text-muted" style="width: 170px;">Mục đích công tác:</td>
                            <td class="fw-bold text-dark"><?= nl2br(h($travel->purpose)) ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Dự án liên quan:</td>
                            <td class="fw-semibold text-primary">
                                <?= !empty($travel->project_name) ? h($travel->project_name) . ' (' . h($travel->project_code) . ')' : 'Công tác chung' ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Lộ trình:</td>
                            <td class="text-dark">
                                <strong><?= h($travel->from_location) ?></strong> &rarr; <strong class="text-danger"><?= h($travel->to_location) ?></strong>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Thời gian công tác:</td>
                            <td class="text-dark">
                                Từ <strong><?= date('d/m/Y', strtotime($travel->departure_date)) ?></strong> đến <strong><?= date('d/m/Y', strtotime($travel->return_date)) ?></strong>
                                <span class="badge bg-light text-dark border ms-2"><?= $days ?> ngày</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Phương tiện:</td>
                            <td class="text-dark"><?= h($travel->transport_type) ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Lưu trú:</td>
                            <td class="text-dark"><?= h($travel->accommodation) ?></td>
                        </tr>
                        <?php if (!empty($travel->notes)): ?>
                            <tr>
                                <td class="text-muted">Ghi chú:</td>
                                <td class="text-muted"><?= nl2br(h($travel->notes)) ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <?php if ($travel->status === 'Rejected' && !empty($travel->rejected_reason)): ?>
                    <div class="alert alert-danger border-0 rounded-3 mb-0">
                        <strong class="d-block mb-1"><i class="fas fa-circle-exclamation me-1"></i>Lý do từ chối:</strong>
                        <?= nl2br(h($travel->rejected_reason)) ?>
                    </div>
                <?php endif; ?>

                <div class="pt-3 border-top d-flex justify-content-between text-muted small">
                    <span>Người phê duyệt: <strong><?= h($travel->approver_fullname ?: ($travel->approver_username ?: 'Chưa phê duyệt')) ?></strong></span>
                    <span>Ngày duyệt: <strong><?= $travel->approved_date ? date('d/m/Y H:i', strtotime($travel->approved_date)) : '---' ?></strong></span>
                </div>
            </div>
        </div>

        <!-- CỘT PHẢI: CÁN BỘ CÔNG TÁC -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-3 p-4 bg-white mb-4">
                <h5 class="fw-bold text-dark mb-3 border-bottom pb-2">
                    <i class="fas fa-id-badge text-primary me-2"></i>Cán bộ Đi công tác
                </h5>

                <div class="d-flex align-items-center mb-3">
                    <?php if (!empty($travel->avatar_path)): ?>
                        <img src="<?= BASE_URL ?>/<?= h($travel->avatar_path) ?>" alt="Avatar" class="rounded-circle me-3 border" style="width: 50px; height: 50px; object-fit: cover;">
                    <?php else: ?>
                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary fw-bold d-flex align-items-center justify-content-center me-3 fs-4" style="width: 50px; height: 50px;">
                            <?= mb_substr($travel->employee_name, 0, 1) ?>
                        </div>
                    <?php endif; ?>
                    <div>
                        <h6 class="fw-bold text-dark mb-0">
                            <a href="<?= BASE_URL ?>/employee/detail/<?= $travel->employee_id ?>" class="text-dark text-decoration-none">
                                <?= h($travel->employee_name) ?>
                            </a>
                        </h6>
                        <small class="text-muted"><?= h($travel->employee_code) ?> &bull; <?= h($travel->pos_title ?? 'N/A') ?></small>
                    </div>
                </div>

                <div class="small text-muted border-top pt-2">
                    <div class="mb-1"><i class="fas fa-sitemap me-2"></i>Phòng ban: <strong><?= h($travel->dept_name ?? 'N/A') ?></strong></div>
                    <div class="mb-1"><i class="fas fa-phone me-2"></i>Số điện thoại: <?= h($travel->employee_phone ?: '---') ?></div>
                    <div><i class="fas fa-envelope me-2"></i>Email: <?= h($travel->employee_email ?: '---') ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- DANH SÁCH BẢNG QUYẾT TOÁN CÔNG TÁC PHÍ PHÁT SINH TỪ CHUYẾN NÀY -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0 text-dark">
                <i class="fas fa-file-invoice-dollar text-primary me-2"></i>Bảng Quyết toán Chi phí Thực tế
                <span class="badge bg-light text-dark ms-2"><?= count($travel->claims ?? []) ?> bảng chi</span>
            </h5>

            <?php if (in_array($travel->status, ['Approved', 'Completed'])): ?>
                <a href="<?= BASE_URL ?>/expense/createClaim?travel_id=<?= $travel->id ?>" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-1"></i> Lập quyết toán mới
                </a>
            <?php endif; ?>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted small text-uppercase">
                    <tr>
                        <th class="ps-3">Mã quyết toán</th>
                        <th>Tiêu đề bảng chi</th>
                        <th class="text-end">Tổng tiền chi</th>
                        <th class="text-end">Đã trừ tạm ứng</th>
                        <th class="text-end">Thực nhận / Chi trả</th>
                        <th>Ngày nộp</th>
                        <th class="text-center">Trạng thái</th>
                        <th class="text-end pe-3">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($travel->claims)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="fas fa-receipt fs-3 d-block mb-2 opacity-50"></i>
                                Chưa có bảng quyết toán chi phí nào được lập cho chuyến công tác này.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($travel->claims as $c): ?>
                            <?php
                            $cBadge = match($c->status) {
                                'Submitted' => '<span class="badge bg-warning text-dark">Chờ duyệt</span>',
                                'Approved'  => '<span class="badge bg-info text-white">Đã duyệt</span>',
                                'Paid'      => '<span class="badge bg-success text-white">Đã thanh toán</span>',
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
                                <td class="fw-semibold text-dark">
                                    <?= h($c->title) ?>
                                    <small class="text-muted d-block"><?= $c->items_count ?> khoản chi</small>
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
                                <td class="text-muted small">
                                    <?= date('d/m/Y', strtotime($c->submitted_date)) ?>
                                </td>
                                <td class="text-center">
                                    <?= $cBadge ?>
                                </td>
                                <td class="text-end pe-3">
                                    <a href="<?= BASE_URL ?>/expense/showClaim/<?= $c->id ?>" class="btn btn-sm btn-outline-primary" title="Xem chi tiết">
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

<!-- MODAL PHÊ DUYỆT -->
<div class="modal fade" id="approveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="<?= BASE_URL ?>/expense/approveTravel/<?= $travel->id ?>" method="POST" class="modal-content border-0 shadow">
            <input type="hidden" name="_csrf_token" value="<?= Session::generateCsrfToken() ?>">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-circle-check me-2"></i>Phê duyệt Kế hoạch Công tác</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <p>Xác nhận phê duyệt đợt công tác <strong><?= h($travel->request_code) ?></strong> đi <strong><?= h($travel->to_location) ?></strong>.</p>
                <div class="d-flex justify-content-between p-3 bg-light rounded-3">
                    <span class="text-muted">Ngân sách dự kiến:</span>
                    <strong class="text-dark"><?= number_format((float)$travel->estimated_budget, 0, ',', '.') ?> ₫</strong>
                </div>
                <div class="d-flex justify-content-between p-3 bg-light rounded-3 mt-2">
                    <span class="text-muted">Tạm ứng đề xuất:</span>
                    <strong class="text-primary"><?= number_format((float)$travel->advance_amount, 0, ',', '.') ?> ₫</strong>
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
        <form action="<?= BASE_URL ?>/expense/rejectTravel/<?= $travel->id ?>" method="POST" class="modal-content border-0 shadow">
            <input type="hidden" name="_csrf_token" value="<?= Session::generateCsrfToken() ?>">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-ban me-2"></i>Từ chối Đề xuất Công tác</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <label class="form-label fw-semibold">Lý do từ chối <span class="text-danger">*</span></label>
                <textarea name="rejected_reason" class="form-control" rows="3" placeholder="Nhập lý do không phê duyệt..." required></textarea>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="submit" class="btn btn-danger"><i class="fas fa-ban me-1"></i> Xác nhận Từ chối</button>
            </div>
        </form>
    </div>
</div>
