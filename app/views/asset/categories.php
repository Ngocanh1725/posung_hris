<?php
/**
 * ============================================================
 *  POSUNG HRIS – View: asset/categories.php
 * ============================================================
 *  Quản lý Danh mục Loại Tài sản Công ty
 * ============================================================
 */
?>
<div class="content-wrapper">
    <!-- TIÊU ĐỀ & THANH ĐIỀU HƯỚNG -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <a href="<?= BASE_URL ?>/asset" class="text-decoration-none text-muted small">
                <i class="fas fa-arrow-left me-1"></i> Quay lại danh sách tài sản
            </a>
            <h2 class="mt-2 fw-bold text-dark"><i class="fas fa-tags text-primary me-2"></i>Danh mục Loại Tài sản</h2>
            <p class="text-muted mb-0">Phân nhóm tài sản để theo dõi định mức, cấu hình và báo cáo kiểm kê</p>
        </div>
        <div>
            <button type="button" class="btn btn-primary" onclick="openCategoryModal()">
                <i class="fas fa-plus me-1"></i> Thêm loại tài sản mới
            </button>
        </div>
    </div>

    <!-- DANH SÁCH DANH MỤC -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" style="width: 60px;">Icon</th>
                        <th>Tên loại tài sản</th>
                        <th>Mã phân loại</th>
                        <th>Mô tả nhóm tài sản</th>
                        <th class="text-center">Tổng TS</th>
                        <th class="text-center">Sẵn sàng</th>
                        <th class="text-center">Đang dùng</th>
                        <th class="text-end">Tổng giá trị</th>
                        <th class="text-center">Trạng thái</th>
                        <th class="text-end pe-3" style="width: 120px;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $cat): ?>
                        <tr>
                            <td class="ps-3 text-center">
                                <div class="rounded p-2 bg-light text-primary d-inline-block">
                                    <i class="<?= h($cat->icon ?: 'fas fa-box') ?> fa-lg"></i>
                                </div>
                            </td>
                            <td>
                                <strong class="text-dark"><?= h($cat->name) ?></strong>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border font-monospace"><?= h($cat->category_code) ?></span>
                            </td>
                            <td>
                                <small class="text-muted"><?= h($cat->description ?: '---') ?></small>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-primary rounded-pill"><?= number_format($cat->total_assets) ?></span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill"><?= number_format($cat->available_count) ?></span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill"><?= number_format($cat->assigned_count) ?></span>
                            </td>
                            <td class="text-end font-monospace fw-medium">
                                <?= number_format($cat->total_cost ?? 0, 0, ',', '.') ?> ₫
                            </td>
                            <td class="text-center">
                                <?php if ($cat->status === 'Active'): ?>
                                    <span class="badge bg-success">Đang dùng</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Tạm ngưng</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end pe-3">
                                <button type="button" class="btn btn-sm btn-light text-primary me-1" onclick="editCategory(<?= htmlspecialchars(json_encode($cat), ENT_QUOTES, 'UTF-8') ?>)" title="Sửa danh mục">
                                    <i class="fas fa-pen"></i>
                                </button>
                                <?php if ($cat->total_assets == 0): ?>
                                    <a href="<?= BASE_URL ?>/asset/deleteCategory/<?= $cat->id ?>" class="btn btn-sm btn-light text-danger" onclick="return confirm('Bạn có chắc chắn muốn xóa danh mục này?');" title="Xóa danh mục">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ==================== MODAL THÊM / SỬA DANH MỤC ==================== -->
<div class="modal fade" id="categoryModal" tabindex="-1" aria-labelledby="categoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="<?= BASE_URL ?>/asset/saveCategory" method="POST" id="categoryForm" class="modal-content border-0 shadow">
            <input type="hidden" name="_csrf_token" value="<?= Session::generateCsrfToken() ?>">
            <input type="hidden" name="id" id="catId" value="">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="categoryModalLabel"><i class="fas fa-tags me-2"></i>Loại Tài sản</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Tên loại tài sản <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="catName" class="form-control" placeholder="Ví dụ: Thiết bị CNTT & Viễn thông" required>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Mã phân loại <span class="text-danger">*</span></label>
                        <input type="text" name="category_code" id="catCode" class="form-control font-monospace text-uppercase" placeholder="IT_EQUIPMENT" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Biểu tượng (Icon)</label>
                        <select name="icon" id="catIcon" class="form-select">
                            <option value="fas fa-laptop">fas fa-laptop (Máy tính/CNTT)</option>
                            <option value="fas fa-car-side">fas fa-car-side (Phương tiện)</option>
                            <option value="fas fa-tools">fas fa-tools (Máy móc/Dụng cụ)</option>
                            <option value="fas fa-chair">fas fa-chair (Nội thất)</option>
                            <option value="fas fa-id-badge">fas fa-id-badge (Thẻ/Bảo mật)</option>
                            <option value="fas fa-phone">fas fa-phone (Điện thoại)</option>
                            <option value="fas fa-box">fas fa-box (Hộp/Khác)</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Trạng thái</label>
                    <select name="status" id="catStatus" class="form-select">
                        <option value="Active">Đang áp dụng (Active)</option>
                        <option value="Inactive">Tạm ngưng (Inactive)</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Mô tả nhóm</label>
                    <textarea name="description" id="catDesc" class="form-control" rows="3" placeholder="Mô tả phạm vi các thiết bị, tài sản thuộc nhóm này..."></textarea>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy bỏ</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Lưu danh mục</button>
            </div>
        </form>
    </div>
</div>

<script>
function openCategoryModal() {
    document.getElementById('catId').value = '';
    document.getElementById('catName').value = '';
    document.getElementById('catCode').value = '';
    document.getElementById('catIcon').value = 'fas fa-box';
    document.getElementById('catStatus').value = 'Active';
    document.getElementById('catDesc').value = '';
    document.getElementById('categoryModalLabel').innerHTML = '<i class="fas fa-plus-circle me-2"></i>Thêm Loại Tài sản Mới';
    new bootstrap.Modal(document.getElementById('categoryModal')).show();
}

function editCategory(cat) {
    document.getElementById('catId').value = cat.id;
    document.getElementById('catName').value = cat.name;
    document.getElementById('catCode').value = cat.category_code;
    document.getElementById('catIcon').value = cat.icon || 'fas fa-box';
    document.getElementById('catStatus').value = cat.status || 'Active';
    document.getElementById('catDesc').value = cat.description || '';
    document.getElementById('categoryModalLabel').innerHTML = '<i class="fas fa-pen-to-square me-2"></i>Sửa Loại Tài sản';
    new bootstrap.Modal(document.getElementById('categoryModal')).show();
}
</script>
