<?php
/**
 * ============================================================
 *  POSUNG HRIS – View: loan/types.php
 * ============================================================
 *  Quản lý Danh mục Loại Khoản vay & Tạm ứng Nhân viên
 * ============================================================
 */
?>
<div class="content-wrapper">
    <!-- TIÊU ĐỀ & THANH ĐIỀU HƯỚNG -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h2 class="mb-1 fw-bold text-dark"><i class="fas fa-tags text-primary me-2"></i>Danh mục Loại Khoản vay & Tạm ứng</h2>
            <p class="text-muted mb-0">Cấu hình các chương trình vay vốn, hạn mức tối đa, kỳ hạn và lãi suất áp dụng</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>/loan" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Quay lại danh sách vay
            </a>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTypeModal">
                <i class="fas fa-plus-circle me-1"></i> Thêm loại khoản vay
            </button>
        </div>
    </div>

    <!-- BẢNG DANH MỤC LOẠI KHOẢN VAY -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0 text-dark">
                <i class="fas fa-list text-primary me-2"></i>Các gói chương trình vay & tạm ứng
            </h5>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted small text-uppercase">
                    <tr>
                        <th class="ps-3" style="width: 140px;">Mã loại</th>
                        <th>Tên loại khoản vay</th>
                        <th class="text-end">Hạn mức tối đa</th>
                        <th class="text-center">Kỳ hạn tối đa</th>
                        <th class="text-center">Lãi suất (%/năm)</th>
                        <th>Mô tả chương trình</th>
                        <th class="text-center">Trạng thái</th>
                        <th class="text-end pe-3">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($types)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                Chưa có loại khoản vay nào được thiết lập.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($types as $type): ?>
                            <tr>
                                <td class="ps-3">
                                    <span class="badge bg-light text-primary border fw-bold">
                                        <?= h($type->type_code) ?>
                                    </span>
                                </td>
                                <td class="fw-bold text-dark">
                                    <?= h($type->name) ?>
                                </td>
                                <td class="text-end fw-semibold text-dark">
                                    <?= !empty($type->max_amount) ? number_format((float)$type->max_amount, 0, ',', '.') . ' ₫' : '<span class="text-muted">Không giới hạn</span>' ?>
                                </td>
                                <td class="text-center">
                                    <?= $type->max_term_months ?> tháng
                                </td>
                                <td class="text-center">
                                    <?php if ((float)$type->interest_rate > 0): ?>
                                        <span class="badge bg-warning text-dark fw-bold"><?= $type->interest_rate ?>%</span>
                                    <?php else: ?>
                                        <span class="badge bg-success text-white">0% (Miễn lãi)</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-muted small" style="max-width: 280px;">
                                    <?= h($type->description ?? '---') ?>
                                </td>
                                <td class="text-center">
                                    <?= $type->is_active ? '<span class="badge bg-success">Đang kích hoạt</span>' : '<span class="badge bg-secondary">Tạm dừng</span>' ?>
                                </td>
                                <td class="text-end pe-3">
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-warning" title="Chỉnh sửa"
                                                onclick="openEditModal(<?= htmlspecialchars(json_encode($type), ENT_QUOTES, 'UTF-8') ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <a href="<?= BASE_URL ?>/loan/deleteType/<?= $type->id ?>" 
                                           class="btn btn-outline-danger" 
                                           title="Xóa loại vay"
                                           onclick="return confirm('Bạn có chắc chắn muốn xóa loại khoản vay này? Thao tác không thể hoàn tác nếu không có ràng buộc.');">
                                            <i class="fas fa-trash"></i>
                                        </a>
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

<!-- MODAL THÊM MỚI LOẠI KHOẢN VAY -->
<div class="modal fade" id="createTypeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="<?= BASE_URL ?>/loan/storeType" method="POST" class="modal-content border-0 shadow">
            <input type="hidden" name="_csrf_token" value="<?= Session::generateCsrfToken() ?>">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Thêm Loại Khoản vay mới</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3 mb-3">
                    <div class="col-md-5">
                        <label class="form-label fw-semibold">Mã loại <span class="text-danger">*</span></label>
                        <input type="text" name="type_code" class="form-control text-uppercase" placeholder="VD: ADV_TET" required>
                    </div>
                    <div class="col-md-7">
                        <label class="form-label fw-semibold">Tên loại khoản vay <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="VD: Tạm ứng thưởng Tết" required>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Hạn mức tối đa (VNĐ)</label>
                        <input type="text" name="max_amount" class="form-control" placeholder="Để trống nếu không giới hạn" oninput="formatCurrency(this)">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Kỳ hạn tối đa (Tháng) <span class="text-danger">*</span></label>
                        <input type="number" name="max_term_months" class="form-control" value="12" min="1" max="60" required>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Lãi suất (% / năm)</label>
                        <div class="input-group">
                            <input type="number" name="interest_rate" class="form-control" value="0.00" min="0" max="100" step="0.1">
                            <span class="input-group-text">%</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Trạng thái áp dụng</label>
                        <select name="is_active" class="form-select">
                            <option value="1">Kích hoạt (Cho phép tạo vay)</option>
                            <option value="0">Tạm khóa</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Mô tả chương trình & Quy định</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Điều kiện vay, đối tượng áp dụng..."></textarea>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Lưu loại khoản vay</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL CHỈNH SỬA LOẠI KHOẢN VAY -->
<div class="modal fade" id="editTypeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="" method="POST" id="editTypeForm" class="modal-content border-0 shadow">
            <input type="hidden" name="_csrf_token" value="<?= Session::generateCsrfToken() ?>">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Chỉnh sửa Loại Khoản vay</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3 mb-3">
                    <div class="col-md-5">
                        <label class="form-label fw-semibold">Mã loại <span class="text-danger">*</span></label>
                        <input type="text" name="type_code" id="editTypeCode" class="form-control text-uppercase" required>
                    </div>
                    <div class="col-md-7">
                        <label class="form-label fw-semibold">Tên loại khoản vay <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="editTypeName" class="form-control" required>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Hạn mức tối đa (VNĐ)</label>
                        <input type="text" name="max_amount" id="editTypeMaxAmount" class="form-control" oninput="formatCurrency(this)">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Kỳ hạn tối đa (Tháng) <span class="text-danger">*</span></label>
                        <input type="number" name="max_term_months" id="editTypeMaxTerm" class="form-control" min="1" max="60" required>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Lãi suất (% / năm)</label>
                        <div class="input-group">
                            <input type="number" name="interest_rate" id="editTypeRate" class="form-control" min="0" max="100" step="0.1">
                            <span class="input-group-text">%</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Trạng thái áp dụng</label>
                        <select name="is_active" id="editTypeActive" class="form-select">
                            <option value="1">Kích hoạt (Cho phép tạo vay)</option>
                            <option value="0">Tạm khóa</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Mô tả chương trình & Quy định</label>
                    <textarea name="description" id="editTypeDesc" class="form-control" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="submit" class="btn btn-warning"><i class="fas fa-save me-1"></i> Cập nhật</button>
            </div>
        </form>
    </div>
</div>

<script>
function formatCurrency(input) {
    let value = input.value.replace(/\D/g, '');
    if (value === '') {
        input.value = '';
        return;
    }
    input.value = new Intl.NumberFormat('vi-VN').format(value);
}

function openEditModal(type) {
    document.getElementById('editTypeForm').action = '<?= BASE_URL ?>/loan/updateType/' + type.id;
    document.getElementById('editTypeCode').value = type.type_code;
    document.getElementById('editTypeName').value = type.name;
    document.getElementById('editTypeMaxAmount').value = type.max_amount ? new Intl.NumberFormat('vi-VN').format(type.max_amount) : '';
    document.getElementById('editTypeMaxTerm').value = type.max_term_months;
    document.getElementById('editTypeRate').value = type.interest_rate;
    document.getElementById('editTypeActive').value = type.is_active;
    document.getElementById('editTypeDesc').value = type.description || '';
    new bootstrap.Modal(document.getElementById('editTypeModal')).show();
}
</script>
