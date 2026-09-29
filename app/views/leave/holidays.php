<?php
/**
 * ============================================================
 *  POSUNG HRIS – Quản lý Lịch Nghỉ Lễ / Tết (Leave Holidays)
 * ============================================================
 *  View: leave/holidays.php
 * ============================================================
 */
?>

<div class="leave-holidays-page">
    <!-- Top Action Bar -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="<?= BASE_URL ?>/leave" class="btn btn-sm btn-outline-secondary rounded-circle" style="width: 32px; height: 32px; padding:0; display:flex; align-items:center; justify-content:center;">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h3 class="fw-bold text-dark mb-0">
                    <i class="fas fa-gifts text-danger me-2"></i> Lịch Nghỉ Lễ / Tết & Ngày truyền thống
                </h3>
            </div>
            <p class="text-muted small mb-0">Thiết lập các ngày nghỉ lễ hưởng nguyên lương theo Bộ luật Lao động và ngày nghỉ truyền thống của POSUNG E&C.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <button class="btn btn-danger shadow-sm" onclick="openAddHolidayModal()">
                <i class="fas fa-plus me-1"></i> Thêm ngày nghỉ lễ
            </button>
            <a href="<?= BASE_URL ?>/leave" class="btn btn-outline-secondary shadow-sm">
                <i class="fas fa-calendar-alt me-1"></i> Dashboard Nghỉ phép
            </a>
            <?php if ($isManager): ?>
                <a href="<?= BASE_URL ?>/leave/allocations?year=<?= $year ?>" class="btn btn-outline-primary shadow-sm">
                    <i class="fas fa-layer-group me-1"></i> Quản lý Quỹ phép (HR)
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 12px; background: #ffffff; border-left: 4px solid #ef4444 !important;">
                <span class="text-muted small text-uppercase fw-semibold">Tổng ngày nghỉ lễ <?= $year ?></span>
                <h2 class="fw-bold text-danger mb-0 mt-1"><?= count($holidays) ?> <small style="font-size:1rem; font-weight:normal;">ngày</small></h2>
                <small class="text-muted mt-1 d-block"><i class="fas fa-calendar-check text-danger me-1"></i> Tự động loại trừ khi nhân viên xin nghỉ</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 12px; background: #ffffff; border-left: 4px solid #2563eb !important;">
                <span class="text-muted small text-uppercase fw-semibold">Lễ Quốc Gia</span>
                <?php 
                    $nationalCount = count(array_filter($holidays, fn($h) => $h['type'] === 'National'));
                ?>
                <h2 class="fw-bold text-primary mb-0 mt-1"><?= $nationalCount ?> <small style="font-size:1rem; font-weight:normal;">ngày</small></h2>
                <small class="text-muted mt-1 d-block">Tết, 30/4, 1/5, Quốc khánh, Giỗ tổ</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 12px; background: #ffffff; border-left: 4px solid #f59e0b !important;">
                <span class="text-muted small text-uppercase fw-semibold">Lễ Nội Bộ Công Ty</span>
                <?php 
                    $companyCount = count(array_filter($holidays, fn($h) => $h['type'] === 'Company'));
                ?>
                <h2 class="fw-bold text-warning mb-0 mt-1"><?= $companyCount ?> <small style="font-size:1rem; font-weight:normal;">ngày</small></h2>
                <small class="text-muted mt-1 d-block">Ngày truyền thống POSUNG 18/08</small>
            </div>
        </div>
    </div>

    <!-- YEAR SELECTOR & TABLE -->
    <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
        <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="fw-bold mb-0 text-dark">
                Danh mục ngày nghỉ lễ năm <?= $year ?>
            </h5>
            <div class="d-flex align-items-center gap-2">
                <span class="small text-muted">Chọn năm:</span>
                <select class="form-select form-select-sm" style="width: 120px;" onchange="window.location.href = '<?= BASE_URL ?>/leave/holidays?year=' + this.value">
                    <?php for ($y = date('Y') + 1; $y >= 2024; $y--): ?>
                        <option value="<?= $y ?>" <?= $year === $y ? 'selected' : '' ?>>Năm <?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light" style="font-size: 0.8rem; text-transform: uppercase; color: #64748b;">
                        <tr>
                            <th>Ngày nghỉ</th>
                            <th>Thứ</th>
                            <th>Tên ngày lễ</th>
                            <th class="text-center">Phân loại</th>
                            <th class="text-center">Tính chất</th>
                            <th>Phạm vi áp dụng</th>
                            <th>Ghi chú mô tả</th>
                            <?php if ($isManager): ?>
                                <th class="text-center">Thao tác</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($holidays)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="fas fa-gifts fa-3x mb-3 text-secondary opacity-50"></i>
                                    <p class="mb-2">Chưa có lịch nghỉ lễ nào được thiết lập cho năm <?= $year ?>.</p>
                                    <button class="btn btn-sm btn-danger" onclick="openAddHolidayModal()">Thêm ngày nghỉ lễ đầu tiên</button>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($holidays as $h): ?>
                            <?php 
                                $timestamp = strtotime($h['date']);
                                $dayOfWeek = (int)date('w', $timestamp);
                                $dowNames = ['Chủ Nhật', 'Thứ Hai', 'Thứ Ba', 'Thứ Tư', 'Thứ Năm', 'Thứ Sáu', 'Thứ Bảy'];
                            ?>
                            <tr>
                                <td class="fw-bold text-dark">
                                    <span class="badge bg-light text-dark border px-2 py-1" style="font-size: 0.9rem;">
                                        <?= date('d/m/Y', $timestamp) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="<?= $dayOfWeek === 0 ? 'text-danger fw-bold' : ($dayOfWeek === 6 ? 'text-primary fw-semibold' : 'text-muted') ?>">
                                        <?= $dowNames[$dayOfWeek] ?>
                                    </span>
                                </td>
                                <td>
                                    <strong class="text-dark d-block"><?= h($h['name']) ?></strong>
                                </td>
                                <td class="text-center">
                                    <?php if ($h['type'] === 'Company'): ?>
                                        <span class="badge bg-warning-subtle text-warning border px-2 py-1">Lễ Công ty</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger-subtle text-danger border px-2 py-1">Lễ Quốc gia</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if (!empty($h['is_recurring'])): ?>
                                        <span class="badge bg-success-subtle text-success"><i class="fas fa-sync-alt me-1"></i> Lặp lại hàng năm</span>
                                    <?php else: ?>
                                        <span class="badge bg-light text-muted border">Sự kiện năm</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($h['dept_name'])): ?>
                                        <span class="badge bg-info-subtle text-info"><?= h($h['dept_name']) ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-light text-dark border">Toàn công ty</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small class="text-muted"><?= h($h['description'] ?? '---') ?></small>
                                </td>
                                <?php if ($isManager): ?>
                                <td class="text-center">
                                    <form action="<?= BASE_URL ?>/leave/deleteHoliday/<?= $h['id'] ?>" method="POST" class="d-inline" onsubmit="return confirm('Bạn chắc chắn muốn xóa ngày nghỉ lễ này?');">
                                        <?= Session::csrfField() ?>
                                        <button type="submit" class="btn btn-outline-danger btn-sm rounded-circle" style="width:30px; height:30px; padding:0;" title="Xóa">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </td>
                                <?php endif; ?>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- MODAL: THÊM NGÀY NGHỈ LỄ MỚI -->
<!-- ============================================================ -->
<div class="modal fade" id="addHolidayModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 14px; border:none; box-shadow: 0 20px 40px rgba(0,0,0,0.15);">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title fw-bold"><i class="fas fa-gifts me-2"></i> Thêm Ngày Nghỉ Lễ Mới</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= BASE_URL ?>/leave/storeHoliday" method="POST">
                <?= Session::csrfField() ?>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Tên ngày lễ <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required placeholder="Ví dụ: Giỗ tổ Hùng Vương, Tết Dương Lịch...">
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-bold small">Ngày nghỉ <span class="text-danger">*</span></label>
                            <input type="date" name="date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold small">Phân loại</label>
                            <select name="type" class="form-select">
                                <option value="National">Lễ Quốc gia (Luật quy định)</option>
                                <option value="Company">Lễ Nội bộ Công ty</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">Phòng ban áp dụng</label>
                        <select name="applies_to_department_id" class="form-select">
                            <option value="">-- Toàn công ty --</option>
                            <?php foreach ($departments as $d): ?>
                                <option value="<?= $d->id ?>"><?= h($d->dept_name) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted" style="font-size: 11px;">Để trống nếu áp dụng cho toàn bộ nhân sự công ty.</small>
                    </div>

                    <div class="mb-3 form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_recurring" value="1" id="isRecurringCheck">
                        <label class="form-check-label small fw-semibold" for="isRecurringCheck">Lặp lại hàng năm theo Dương lịch (ví dụ 01/01, 30/04, 01/05)</label>
                    </div>

                    <div class="mb-2">
                        <label class="form-label fw-bold small">Mô tả chi tiết</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Ghi chú thêm về quy định nghỉ bù, thông báo của ban giám đốc..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Hủy bỏ</button>
                    <button type="submit" class="btn btn-danger btn-sm fw-bold">Lưu ngày lễ</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let addHolidayModal;
document.addEventListener('DOMContentLoaded', () => {
    addHolidayModal = new bootstrap.Modal(document.getElementById('addHolidayModal'));
});

function openAddHolidayModal() {
    addHolidayModal.show();
}
</script>
