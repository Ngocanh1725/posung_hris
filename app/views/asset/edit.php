<?php
/**
 * ============================================================
 *  POSUNG HRIS – View: asset/edit.php
 * ============================================================
 *  Form Cập nhật Thông tin Tài sản Doanh nghiệp
 * ============================================================
 */
?>
<div class="content-wrapper">
    <div class="mb-4">
        <a href="<?= BASE_URL ?>/asset/show/<?= $asset->id ?>" class="text-decoration-none text-muted small">
            <i class="fas fa-arrow-left me-1"></i> Quay lại chi tiết tài sản
        </a>
        <h2 class="mt-2 fw-bold text-dark"><i class="fas fa-edit text-primary me-2"></i>Cập nhật Tài sản: <?= h($asset->asset_code) ?></h2>
        <p class="text-muted mb-0"><?= h($asset->name) ?></p>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-pen-to-square text-primary me-2"></i>Chỉnh sửa thông số tài sản</h5>
                </div>
                <div class="card-body p-4">
                    <form action="<?= BASE_URL ?>/asset/update/<?= $asset->id ?>" method="POST" id="editAssetForm">
                        <input type="hidden" name="_csrf_token" value="<?= Session::generateCsrfToken() ?>">

                        <!-- HÀNG 1: Loại tài sản & Mã tài sản -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Loại tài sản <span class="text-danger">*</span></label>
                                <select name="category_id" id="categoryIdSelect" class="form-select" required>
                                    <option value="">-- Chọn danh mục --</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= $cat->id ?>" <?= $asset->category_id == $cat->id ? 'selected' : '' ?>>
                                            <?= h($cat->name) ?> (<?= h($cat->category_code) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Mã quản lý tài sản</label>
                                <input type="text" class="form-control font-monospace text-uppercase bg-light" value="<?= h($asset->asset_code) ?>" readonly>
                                <small class="text-muted">Mã tài sản cố định sau khi khởi tạo.</small>
                            </div>
                        </div>

                        <!-- HÀNG 2: Tên tài sản & Số Serial -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">Tên tài sản / Model <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" value="<?= h($asset->name) ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Số Serial / Service Tag / IMEI</label>
                                <input type="text" name="serial_number" class="form-control font-monospace" value="<?= h($asset->serial_number ?? '') ?>">
                            </div>
                        </div>

                        <!-- HÀNG 3: Ngày mua & Nguyên giá & Hạn bảo hành -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Ngày mua sắm</label>
                                <input type="date" name="purchase_date" class="form-control" value="<?= $asset->purchase_date ?? '' ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Nguyên giá mua sắm (VNĐ)</label>
                                <div class="input-group">
                                    <input type="number" step="1000" name="purchase_cost" class="form-control" value="<?= (int)($asset->purchase_cost ?? 0) ?>">
                                    <span class="input-group-text">₫</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Hạn bảo hành</label>
                                <input type="date" name="warranty_expiry" class="form-control" value="<?= $asset->warranty_expiry ?? '' ?>">
                            </div>
                        </div>

                        <!-- HÀNG 4: Tình trạng & Trạng thái -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tình trạng vật lý</label>
                                <select name="condition" class="form-select">
                                    <option value="New" <?= $asset->condition === 'New' ? 'selected' : '' ?>>Mới 100% (New)</option>
                                    <option value="Good" <?= $asset->condition === 'Good' ? 'selected' : '' ?>>Tốt (Good)</option>
                                    <option value="Fair" <?= $asset->condition === 'Fair' ? 'selected' : '' ?>>Bình thường (Fair)</option>
                                    <option value="Damaged" <?= $asset->condition === 'Damaged' ? 'selected' : '' ?>>Hư hỏng / Lỗi (Damaged)</option>
                                    <option value="Disposed" <?= $asset->condition === 'Disposed' ? 'selected' : '' ?>>Đã thanh lý (Disposed)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Trạng thái quản lý</label>
                                <select name="status" class="form-select">
                                    <option value="Available" <?= $asset->status === 'Available' ? 'selected' : '' ?>>Sẵn sàng cấp phát (Lưu kho)</option>
                                    <option value="Assigned" <?= $asset->status === 'Assigned' ? 'selected' : '' ?>>Đang cấp phát (Assigned)</option>
                                    <option value="Maintenance" <?= $asset->status === 'Maintenance' ? 'selected' : '' ?>>Đang kiểm định / Bảo dưỡng</option>
                                    <option value="Disposed" <?= $asset->status === 'Disposed' ? 'selected' : '' ?>>Đã thanh lý</option>
                                </select>
                            </div>
                        </div>

                        <!-- HÀNG 5: Vị trí lưu kho & Dự án -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Vị trí đặt tài sản / Phòng lưu kho</label>
                                <input type="text" name="location" class="form-control" value="<?= h($asset->location ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Thuộc Dự án</label>
                                <select name="project_id" class="form-select">
                                    <option value="">-- Dùng chung toàn công ty --</option>
                                    <?php foreach ($projects as $prj): ?>
                                        <option value="<?= $prj['id'] ?>" <?= $asset->project_id == $prj['id'] ? 'selected' : '' ?>>
                                            <?= h($prj['project_code']) ?> - <?= h($prj['project_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- HÀNG 6: Ghi chú & Cấu hình -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Ghi chú kỹ thuật / Cấu hình</label>
                            <textarea name="notes" class="form-control" rows="4"><?= h($asset->notes ?? '') ?></textarea>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <a href="<?= BASE_URL ?>/asset/delete/<?= $asset->id ?>" class="btn btn-outline-danger" onclick="return confirm('Bạn có chắc chắn muốn xóa tài sản này?');">
                                    <i class="fas fa-trash me-1"></i> Xóa tài sản
                                </a>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="<?= BASE_URL ?>/asset/show/<?= $asset->id ?>" class="btn btn-light px-4">Hủy</a>
                                <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save me-1"></i> Lưu thay đổi</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-history text-secondary me-2"></i>Thông tin bổ sung</h6>
                </div>
                <div class="card-body p-3 small">
                    <div class="mb-2">
                        <span class="text-muted d-block">Ngày tạo bản ghi:</span>
                        <strong><?= fmtDate($asset->created_at ?? null) ?></strong>
                    </div>
                    <div class="mb-2">
                        <span class="text-muted d-block">Trạng thái hiện tại:</span>
                        <span class="badge bg-secondary"><?= h($asset->status) ?></span> (<?= h($asset->condition) ?>)
                    </div>
                    <?php if ($asset->status === 'Assigned' && !empty($asset->holder_name)): ?>
                        <div class="p-2 bg-light rounded mt-3 border">
                            <span class="text-muted d-block small">Người đang quản lý:</span>
                            <strong class="text-primary"><?= h($asset->holder_name) ?></strong>
                            <div class="text-muted small"><?= h($asset->holder_dept ?? '') ?></div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
