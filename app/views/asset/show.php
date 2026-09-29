<?php
/**
 * ============================================================
 *  POSUNG HRIS – View: asset/show.php
 * ============================================================
 *  Chi tiết Hồ sơ Tài sản & Lịch sử Bàn giao / Thu hồi
 * ============================================================
 */

$statusBadge = match($asset->status) {
    'Available'   => '<span class="badge bg-success fs-6"><i class="fas fa-check-circle me-1"></i>Sẵn sàng (Kho)</span>',
    'Assigned'    => '<span class="badge bg-warning text-dark fs-6"><i class="fas fa-user-check me-1"></i>Đang cấp phát</span>',
    'Maintenance' => '<span class="badge bg-danger fs-6"><i class="fas fa-wrench me-1"></i>Đang bảo dưỡng</span>',
    'Disposed'    => '<span class="badge bg-secondary fs-6"><i class="fas fa-archive me-1"></i>Đã thanh lý</span>',
    default       => '<span class="badge bg-light text-dark fs-6">' . h($asset->status) . '</span>'
};

$condBadge = match($asset->condition) {
    'New'     => '<span class="badge bg-info text-dark">Mới 100%</span>',
    'Good'    => '<span class="badge bg-primary">Hoạt động tốt</span>',
    'Fair'    => '<span class="badge bg-secondary">Bình thường (Đã dùng)</span>',
    'Damaged' => '<span class="badge bg-danger">Hư hỏng / Lỗi</span>',
    default   => '<span class="badge bg-light text-dark">' . h($asset->condition) . '</span>'
};
?>
<div class="content-wrapper">
    <!-- BREADCRUMB & HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <a href="<?= BASE_URL ?>/asset" class="text-decoration-none text-muted small">
                <i class="fas fa-arrow-left me-1"></i> Quay lại danh sách tài sản
            </a>
            <div class="d-flex align-items-center gap-3 mt-2 flex-wrap">
                <h2 class="mb-0 fw-bold text-dark font-monospace"><?= h($asset->asset_code) ?></h2>
                <?= $statusBadge ?>
                <?= $condBadge ?>
            </div>
            <h5 class="text-muted mt-1 fw-normal"><?= h($asset->name) ?></h5>
        </div>

        <div class="d-flex gap-2">
            <?php if ($asset->status === 'Available'): ?>
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#assignModal">
                    <i class="fas fa-hand-holding-hand me-1"></i> Bàn giao tài sản
                </button>
            <?php elseif ($asset->status === 'Assigned' && !empty($asset->current_assignment_id)): ?>
                <a href="<?= BASE_URL ?>/asset/handoverReceipt/<?= $asset->current_assignment_id ?>" target="_blank" class="btn btn-outline-secondary">
                    <i class="fas fa-print me-1"></i> In biên bản bàn giao
                </a>
                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#returnModal">
                    <i class="fas fa-rotate-left me-1"></i> Thu hồi tài sản
                </button>
            <?php endif; ?>

            <a href="<?= BASE_URL ?>/asset/edit/<?= $asset->id ?>" class="btn btn-outline-primary">
                <i class="fas fa-edit me-1"></i> Chỉnh sửa
            </a>
        </div>
    </div>

    <!-- KHỐI NGƯỜI ĐANG NẮM GIỮ (CURRENT CUSTODIAN) -->
    <?php if ($asset->status === 'Assigned' && !empty($asset->holder_name)): ?>
        <div class="card border-0 shadow-sm rounded-3 mb-4 bg-primary bg-opacity-10 border-start border-4 border-primary">
            <div class="card-body p-4">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <span class="badge bg-primary mb-2"><i class="fas fa-user-check me-1"></i>Người đang nắm giữ hiện tại</span>
                        <h4 class="fw-bold text-dark mb-1">
                            <a href="<?= BASE_URL ?>/employee/view/<?= $asset->current_holder_id ?>" class="text-dark text-decoration-none hover-primary">
                                <?= h($asset->holder_name) ?> <span class="text-muted fw-normal fs-6">(<?= h($asset->holder_code) ?>)</span>
                            </a>
                        </h4>
                        <div class="text-secondary small d-flex flex-wrap gap-3 mt-2">
                            <span><i class="fas fa-briefcase me-1"></i>Chức vụ: <strong><?= h($asset->holder_position ?? 'N/A') ?></strong></span>
                            <span><i class="fas fa-building me-1"></i>Phòng ban: <strong><?= h($asset->holder_dept ?? 'N/A') ?></strong></span>
                            <span><i class="far fa-calendar-alt me-1"></i>Ngày nhận: <strong><?= fmtDate($asset->assigned_date) ?></strong></span>
                            <?php if ($asset->holder_phone): ?>
                                <span><i class="fas fa-phone me-1"></i><?= h($asset->holder_phone) ?></span>
                            <?php endif; ?>
                        </div>
                        <?php if ($asset->assignment_notes): ?>
                            <div class="mt-2 text-muted small bg-white p-2 rounded border">
                                <strong>Ghi chú lúc giao:</strong> <?= h($asset->assignment_notes) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        <button type="button" class="btn btn-danger px-3" data-bs-toggle="modal" data-bs-target="#returnModal">
                            <i class="fas fa-rotate-left me-1"></i> Làm thủ tục thu hồi
                        </button>
                    </div>
                </div>
            </div>
        </div>
    <?php elseif ($asset->status === 'Available'): ?>
        <div class="card border-0 shadow-sm rounded-3 mb-4 bg-success bg-opacity-10 border-start border-4 border-success">
            <div class="card-body p-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-success text-white p-3 fs-4">
                        <i class="fas fa-box-archive"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold text-success mb-1">Tài sản đang sẵn sàng trong kho</h5>
                        <p class="text-muted mb-0 small">Vị trí hiện tại: <strong><?= h($asset->location ?: 'Kho thiết bị công ty') ?></strong>. Có thể bàn giao cho nhân viên ngay.</p>
                    </div>
                </div>
                <button type="button" class="btn btn-success px-4" data-bs-toggle="modal" data-bs-target="#assignModal">
                    <i class="fas fa-hand-holding-hand me-1"></i> Bàn giao cho nhân viên
                </button>
            </div>
        </div>
    <?php endif; ?>

    <!-- THÔNG SỐ KỸ THUẬT & TÀI CHÍNH -->
    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-sliders text-primary me-2"></i>Thông số & Nhận diện</h5>
                </div>
                <div class="card-body p-4">
                    <table class="table table-borderless align-middle mb-0">
                        <tbody>
                            <tr>
                                <td class="text-muted ps-0" style="width: 170px;">Mã tài sản:</td>
                                <td class="font-monospace fw-bold text-primary"><?= h($asset->asset_code) ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted ps-0">Loại tài sản:</td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        <i class="<?= h($asset->category_icon ?? 'fas fa-cube') ?> text-secondary me-1"></i><?= h($asset->category_name) ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted ps-0">Số Serial / IMEI:</td>
                                <td class="font-monospace fw-semibold"><?= h($asset->serial_number ?: '---') ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted ps-0">Vị trí hiện tại:</td>
                                <td><?= h($asset->location ?: 'Kho trung tâm') ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted ps-0">Tình trạng vật lý:</td>
                                <td><?= $condBadge ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted ps-0">Ghi chú kỹ thuật:</td>
                                <td><?= nl2br(h($asset->notes ?: 'Không có ghi chú thêm.')) ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-coins text-success me-2"></i>Tài chính & Bảo hành</h5>
                </div>
                <div class="card-body p-4">
                    <table class="table table-borderless align-middle mb-0">
                        <tbody>
                            <tr>
                                <td class="text-muted ps-0" style="width: 170px;">Nguyên giá mua sắm:</td>
                                <td class="fs-5 fw-bold text-dark font-monospace"><?= number_format($asset->purchase_cost ?? 0, 0, ',', '.') ?> ₫</td>
                            </tr>
                            <tr>
                                <td class="text-muted ps-0">Ngày mua sắm:</td>
                                <td class="fw-semibold"><?= fmtDate($asset->purchase_date) ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted ps-0">Hạn bảo hành:</td>
                                <td>
                                    <?php if ($asset->warranty_expiry): ?>
                                        <span class="fw-semibold"><?= fmtDate($asset->warranty_expiry) ?></span>
                                        <?php 
                                        $dDiff = (strtotime($asset->warranty_expiry) - time()) / 86400;
                                        if ($dDiff < 0) {
                                            echo '<span class="badge bg-danger ms-2">Đã hết hạn BH</span>';
                                        } elseif ($dDiff <= 60) {
                                            echo '<span class="badge bg-warning text-dark ms-2">Sắp hết hạn BH (' . round($dDiff) . ' ngày)</span>';
                                        } else {
                                            echo '<span class="badge bg-success ms-2">Còn hạn BH</span>';
                                        }
                                        ?>
                                    <?php else: ?>
                                        <span class="text-muted">Không áp dụng</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted ps-0">Khấu hao tài sản:</td>
                                <td><span class="text-muted">Theo quy chế tài chính Posung Vina</span></td>
                            </tr>
                            <tr>
                                <td class="text-muted ps-0">Ngày tạo hồ sơ:</td>
                                <td class="text-muted small"><?= fmtDate($asset->created_at ?? null) ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- LỊCH SỬ BÀN GIAO & THU HỒI TÀI SẢN -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-history text-secondary me-2"></i>Lịch sử Cấp phát & Thu hồi</h5>
            <span class="badge bg-secondary"><?= count($history) ?> lần bàn giao</span>
        </div>
        <div class="card-body p-0">
            <?php if (empty($history)): ?>
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-calendar-times fa-2x mb-2 text-secondary"></i>
                    <p class="mb-0">Tài sản này chưa từng được bàn giao lần nào.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">STT</th>
                                <th>Nhân viên tiếp nhận</th>
                                <th>Phòng ban</th>
                                <th>Ngày bàn giao</th>
                                <th>Tình trạng lúc giao</th>
                                <th>Ngày thu hồi</th>
                                <th>Tình trạng lúc trả</th>
                                <th>Người lập biên bản</th>
                                <th>Ghi chú</th>
                                <th class="text-end pe-3">Biên bản</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $stt = 1; foreach ($history as $h): ?>
                                <tr>
                                    <td class="ps-3 text-muted"><?= $stt++ ?></td>
                                    <td>
                                        <a href="<?= BASE_URL ?>/employee/view/<?= $h->employee_id ?>" class="text-decoration-none fw-semibold text-dark">
                                            <?= h($h->employee_name) ?>
                                        </a>
                                        <small class="text-muted font-monospace d-block">(<?= h($h->emp_code) ?>)</small>
                                    </td>
                                    <td><small class="text-muted"><?= h($h->dept_name ?? '---') ?></small></td>
                                    <td class="fw-medium"><?= fmtDate($h->assigned_date) ?></td>
                                    <td>
                                        <span class="badge bg-light text-dark border"><?= h($h->condition_on_assign) ?></span>
                                    </td>
                                    <td>
                                        <?php if ($h->return_date): ?>
                                            <span class="text-success fw-medium"><?= fmtDate($h->return_date) ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i>Đang giữ</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($h->return_date): ?>
                                            <span class="badge bg-light text-dark border"><?= h($h->condition_on_return) ?></span>
                                        <?php else: ?>
                                            <span class="text-muted small">---</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><small class="text-muted"><?= h($h->assigned_by_name ?? 'Admin') ?></small></td>
                                    <td><small class="text-muted"><?= h($h->notes ?? '') ?></small></td>
                                    <td class="text-end pe-3">
                                        <a href="<?= BASE_URL ?>/asset/handoverReceipt/<?= $h->id ?>" target="_blank" class="btn btn-sm btn-outline-secondary" title="In biên bản bàn giao">
                                            <i class="fas fa-print me-1"></i> In
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ==================== MODAL BÀN GIAO TÀI SẢN ==================== -->
<div class="modal fade" id="assignModal" tabindex="-1" aria-labelledby="assignModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="<?= BASE_URL ?>/asset/assign/<?= $asset->id ?>" method="POST" class="modal-content border-0 shadow">
            <input type="hidden" name="_csrf_token" value="<?= Session::generateCsrfToken() ?>">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="assignModalLabel"><i class="fas fa-hand-holding-hand me-2"></i>Bàn giao Tài sản cho Nhân viên</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="p-3 bg-light rounded mb-3 border">
                    <span class="text-muted small d-block">Tài sản:</span>
                    <strong class="text-primary fs-6"><?= h($asset->asset_code) ?> - <?= h($asset->name) ?></strong>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Nhân viên tiếp nhận <span class="text-danger">*</span></label>
                    <select name="employee_id" class="form-select" required>
                        <option value="">-- Chọn nhân viên --</option>
                        <?php foreach ($employees as $emp): ?>
                            <option value="<?= $emp->id ?>">
                                <?= h($emp->emp_code) ?> - <?= h($emp->full_name) ?> (<?= h($emp->dept_name ?? 'N/A') ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Ngày bàn giao <span class="text-danger">*</span></label>
                        <input type="date" name="assigned_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Tình trạng lúc giao</label>
                        <select name="condition_on_assign" class="form-select">
                            <option value="New" <?= $asset->condition === 'New' ? 'selected' : '' ?>>Mới 100%</option>
                            <option value="Good" <?= $asset->condition === 'Good' ? 'selected' : '' ?>>Hoạt động tốt</option>
                            <option value="Fair" <?= $asset->condition === 'Fair' ? 'selected' : '' ?>>Bình thường</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Ghi chú bàn giao / Phụ kiện kèm theo</label>
                    <textarea name="notes" class="form-control" rows="3" placeholder="Sạc zin, cáp nguồn, túi chống sốc, hướng dẫn sử dụng..."></textarea>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy bỏ</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-check me-1"></i> Xác nhận bàn giao</button>
            </div>
        </form>
    </div>
</div>

<!-- ==================== MODAL THU HỒI TÀI SẢN ==================== -->
<?php if ($asset->status === 'Assigned' && !empty($asset->current_assignment_id)): ?>
<div class="modal fade" id="returnModal" tabindex="-1" aria-labelledby="returnModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="<?= BASE_URL ?>/asset/return/<?= $asset->id ?>" method="POST" class="modal-content border-0 shadow">
            <input type="hidden" name="_csrf_token" value="<?= Session::generateCsrfToken() ?>">
            <input type="hidden" name="assignment_id" value="<?= $asset->current_assignment_id ?>">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="returnModalLabel"><i class="fas fa-rotate-left me-2"></i>Thu hồi Tài sản về Kho</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="p-3 bg-light rounded mb-3 border">
                    <div class="mb-1"><span class="text-muted small">Tài sản:</span> <strong><?= h($asset->name) ?></strong></div>
                    <div><span class="text-muted small">Nhân viên hoàn trả:</span> <strong class="text-primary"><?= h($asset->holder_name) ?></strong></div>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Ngày thu hồi <span class="text-danger">*</span></label>
                        <input type="date" name="return_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Tình trạng lúc thu hồi</label>
                        <select name="condition_on_return" class="form-select">
                            <option value="Good" selected>Tốt / Hoạt động bình thường</option>
                            <option value="Fair">Bình thường (Có hao mòn tự nhiên)</option>
                            <option value="Damaged">Hư hỏng / Lỗi kỹ thuật (Cần sửa chữa)</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Ghi chú kiểm tra & lý do hoàn trả</label>
                    <textarea name="notes" class="form-control" rows="3" placeholder="Đầy đủ phụ kiện hoặc ghi nhận hao hụt, lỗi nếu có..."></textarea>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy bỏ</button>
                <button type="submit" class="btn btn-danger"><i class="fas fa-check me-1"></i> Xác nhận hoàn tất thu hồi</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>
