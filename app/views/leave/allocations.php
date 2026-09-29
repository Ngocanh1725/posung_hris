<?php
/**
 * ============================================================
 *  POSUNG HRIS – Quản lý Phân bổ Quỹ phép (Leave Allocation)
 * ============================================================
 *  Chuẩn Frappe HRMS Leave Allocation:
 *  - Phân bổ tự động đầu năm (Auto Allocate + Proration + Thâm niên)
 *  - Kết chuyển phép tồn năm cũ (Carry Forward)
 *  - Điều chỉnh định ngạch cá nhân
 * ============================================================
 */
?>

<div class="leave-allocations-page">
    <!-- Header & Action Buttons -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="<?= BASE_URL ?>/leave" class="btn btn-sm btn-outline-secondary rounded-circle" style="width: 32px; height: 32px; padding:0; display:flex; align-items:center; justify-content:center;">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h3 class="fw-bold text-dark mb-0">
                    <i class="fas fa-layer-group text-primary me-2"></i> Quản lý Quỹ phép năm <?= $year ?>
                </h3>
            </div>
            <p class="text-muted small mb-0">Thiết lập định ngạch ngày nghỉ phép, tính thâm niên tự động và kết chuyển phép tồn theo chuẩn Frappe HRMS.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <button class="btn btn-primary shadow-sm" onclick="openAutoAllocateModal()">
                <i class="fas fa-magic me-1"></i> Tự động phân bổ phép năm
            </button>
            <button class="btn btn-warning text-dark fw-bold shadow-sm" onclick="openCarryForwardModal()">
                <i class="fas fa-share-square me-1"></i> Kết chuyển phép tồn
            </button>
            <a href="<?= BASE_URL ?>/leave/holidays?year=<?= $year ?>" class="btn btn-outline-danger shadow-sm">
                <i class="fas fa-gifts me-1"></i> Lịch Lễ Tết <?= $year ?>
            </a>
        </div>
    </div>

    <!-- 4 KPI SUMMARY METRICS -->
    <div class="row g-3 mb-4">
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 12px; background: #ffffff;">
                <span class="text-muted small text-uppercase fw-semibold">Tổng nhân sự</span>
                <h3 class="fw-bold text-dark mb-0 mt-1"><?= number_format($yearStats['total_employees']) ?></h3>
                <small class="text-success"><i class="fas fa-users me-1"></i> Đã cấp quỹ phép</small>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 12px; background: #ffffff; border-left: 4px solid #2563eb !important;">
                <span class="text-muted small text-uppercase fw-semibold">Tổng phép tiêu chuẩn</span>
                <h3 class="fw-bold text-primary mb-0 mt-1"><?= number_format($yearStats['total_entitled'], 1) ?></h3>
                <small class="text-muted">12 ngày + thâm niên</small>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 12px; background: #ffffff; border-left: 4px solid #f59e0b !important;">
                <span class="text-muted small text-uppercase fw-semibold">Tổng phép chuyển tiếp</span>
                <h3 class="fw-bold text-warning mb-0 mt-1"><?= number_format($yearStats['total_carried'], 1) ?></h3>
                <small class="text-muted">Từ năm <?= $year - 1 ?> chuyển sang</small>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 12px; background: #ffffff; border-left: 4px solid #ef4444 !important;">
                <span class="text-muted small text-uppercase fw-semibold">Tổng ngày đã dùng</span>
                <h3 class="fw-bold text-danger mb-0 mt-1"><?= number_format($yearStats['total_used'], 1) ?></h3>
                <small class="text-danger">Đơn đã phê duyệt</small>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 12px; background: #ffffff; border-left: 4px solid #10b981 !important;">
                <span class="text-muted small text-uppercase fw-semibold">Tổng quỹ còn lại</span>
                <h3 class="fw-bold text-success mb-0 mt-1"><?= number_format($yearStats['total_remaining'], 1) ?></h3>
                <small class="text-success">Khả dụng toàn công ty</small>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 12px; background: #ffffff;">
                <span class="text-muted small text-uppercase fw-semibold">Tỷ lệ sử dụng</span>
                <?php 
                    $totalAlloc = $yearStats['total_entitled'] + $yearStats['total_carried'];
                    $usageRate = $totalAlloc > 0 ? round(($yearStats['total_used'] / $totalAlloc) * 100, 1) : 0.0;
                ?>
                <h3 class="fw-bold text-dark mb-0 mt-1"><?= $usageRate ?>%</h3>
                <div class="progress mt-2" style="height: 5px;">
                    <div class="progress-bar bg-primary" role="progressbar" style="width: <?= min(100, $usageRate) ?>%;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- FILTER BAR -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
        <div class="card-body p-3">
            <form action="<?= BASE_URL ?>/leave/allocations" method="GET" class="row g-2 align-items-center">
                <div class="col-md-2 col-6">
                    <label class="form-label small text-muted mb-1">Năm làm việc</label>
                    <select name="year" class="form-select form-select-sm" onchange="this.form.submit()">
                        <?php for ($y = date('Y') + 1; $y >= 2024; $y--): ?>
                            <option value="<?= $y ?>" <?= $year === $y ? 'selected' : '' ?>>Năm <?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-3 col-6">
                    <label class="form-label small text-muted mb-1">Phòng ban</label>
                    <select name="department_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">-- Tất cả phòng ban --</option>
                        <?php foreach ($departments as $d): ?>
                            <option value="<?= $d->id ?>" <?= $deptId == $d->id ? 'selected' : '' ?>><?= h($d->dept_name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-5 col-8">
                    <label class="form-label small text-muted mb-1">Tìm kiếm nhân sự</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Tìm theo tên hoặc mã nhân viên..." value="<?= h($search ?? '') ?>">
                    </div>
                </div>
                <div class="col-md-2 col-4 d-flex align-items-end gap-1" style="margin-top: auto;">
                    <button type="submit" class="btn btn-primary btn-sm flex-grow-1">Lọc</button>
                    <?php if (!empty($search) || !empty($deptId)): ?>
                        <a href="<?= BASE_URL ?>/leave/allocations?year=<?= $year ?>" class="btn btn-light btn-sm text-danger" title="Xóa bộ lọc"><i class="fas fa-undo"></i></a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- ALLOCATIONS TABLE -->
    <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
        <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0 text-dark">
                Danh sách định ngạch phép nhân viên
                <span class="badge bg-light text-muted border ms-2"><?= $totalItems ?> bản ghi</span>
            </h5>
            <small class="text-muted">Trang <?= $currentPage ?> / <?= max(1, $totalPages) ?></small>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light" style="font-size: 0.8rem; text-transform: uppercase; color: #64748b;">
                        <tr>
                            <th>Nhân viên</th>
                            <th>Phòng ban</th>
                            <th>Ngày vào làm</th>
                            <th class="text-center">Tiêu chuẩn</th>
                            <th class="text-center">Chuyển tiếp</th>
                            <th class="text-center">Tổng quỹ</th>
                            <th class="text-center">Đã nghỉ</th>
                            <th class="text-center">Còn lại</th>
                            <th>Hiệu lực</th>
                            <th class="text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($allocations)): ?>
                            <tr>
                                <td colspan="10" class="text-center py-5 text-muted">
                                    <i class="fas fa-folder-open fa-3x mb-3 text-secondary opacity-50"></i>
                                    <p class="mb-2">Chưa có dữ liệu phân bổ quỹ phép nào cho năm <?= $year ?>.</p>
                                    <button class="btn btn-sm btn-primary" onclick="openAutoAllocateModal()">Chạy tự động phân bổ ngay</button>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($allocations as $a): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div style="width: 34px; height: 34px; border-radius: 50%; background: #e0e7ff; color: #3730a3; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 0.85rem;">
                                            <?= strtoupper(substr($a['full_name'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <strong class="d-block text-dark"><?= h($a['full_name']) ?></strong>
                                            <span class="text-muted small"><?= h($a['emp_code']) ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="d-block text-dark small fw-semibold"><?= h($a['dept_name'] ?? '---') ?></span>
                                    <span class="text-muted" style="font-size: 11px;"><?= h($a['pos_title'] ?? '') ?></span>
                                </td>
                                <td>
                                    <?php if (!empty($a['join_date'])): ?>
                                        <span class="small"><?= date('d/m/Y', strtotime($a['join_date'])) ?></span>
                                        <?php 
                                            $yDiff = floor((strtotime($year . '-01-01') - strtotime($a['join_date'])) / (365.25 * 86400));
                                            if ($yDiff >= 5):
                                        ?>
                                            <span class="badge bg-info-subtle text-info ms-1" style="font-size: 10px;" title="Thâm niên trên 5 năm: +<?= floor($yDiff / 5) ?> ngày">+<?= floor($yDiff / 5) ?> TN</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted small">---</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center fw-bold text-primary">
                                    <?= floatval($a['entitled_days']) ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($a['carried_forward_days'] > 0): ?>
                                        <span class="badge bg-warning-subtle text-warning fw-bold">+<?= floatval($a['carried_forward_days']) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted small">0</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center fw-bold text-dark">
                                    <?= floatval($a['entitled_days'] + $a['carried_forward_days']) ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-danger border">
                                        <?= floatval($a['used_days']) ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-success px-2 py-1 fw-bold" style="font-size: 0.9rem;">
                                        <?= floatval($a['remaining_days']) ?>
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted d-block">Từ: <?= !empty($a['effective_from']) ? date('d/m/Y', strtotime($a['effective_from'])) : '---' ?></small>
                                    <small class="text-muted d-block">Đến: <?= !empty($a['effective_to']) ? date('d/m/Y', strtotime($a['effective_to'])) : '---' ?></small>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-outline-primary btn-sm rounded-pill px-2 py-1" 
                                            onclick='openEditModal(<?= json_encode($a) ?>)'
                                            title="Điều chỉnh quỹ phép">
                                        <i class="fas fa-edit me-1"></i> Điều chỉnh
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- PAGINATION -->
        <?php if ($totalPages > 1): ?>
        <div class="card-footer bg-white border-top p-3 d-flex justify-content-between align-items-center">
            <span class="text-muted small">Hiển thị <?= count($allocations) ?> / <?= $totalItems ?> nhân viên</span>
            <ul class="pagination pagination-sm m-0">
                <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= BASE_URL ?>/leave/allocations?year=<?= $year ?>&department_id=<?= $deptId ?>&search=<?= urlencode($search ?? '') ?>&page=<?= $currentPage - 1 ?>">Trước</a>
                </li>
                <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                    <?php if ($p == 1 || $p == $totalPages || abs($p - $currentPage) <= 2): ?>
                        <li class="page-item <?= $currentPage == $p ? 'active' : '' ?>">
                            <a class="page-link" href="<?= BASE_URL ?>/leave/allocations?year=<?= $year ?>&department_id=<?= $deptId ?>&search=<?= urlencode($search ?? '') ?>&page=<?= $p ?>"><?= $p ?></a>
                        </li>
                    <?php elseif (abs($p - $currentPage) == 3): ?>
                        <li class="page-item disabled"><span class="page-link">...</span></li>
                    <?php endif; ?>
                <?php endfor; ?>
                <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= BASE_URL ?>/leave/allocations?year=<?= $year ?>&department_id=<?= $deptId ?>&search=<?= urlencode($search ?? '') ?>&page=<?= $currentPage + 1 ?>">Sau</a>
                </li>
            </ul>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- ============================================================ -->
<!-- MODAL: TỰ ĐỘNG PHÂN BỔ PHÉP NĂM (AUTO ALLOCATE) -->
<!-- ============================================================ -->
<div class="modal fade" id="autoAllocateModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 14px; border:none; box-shadow: 0 20px 40px rgba(0,0,0,0.15);">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold"><i class="fas fa-magic me-2"></i> Tự động phân bổ quỹ phép năm</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= BASE_URL ?>/leave/autoAllocate" method="POST">
                <?= Session::csrfField() ?>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Chọn năm áp dụng <span class="text-danger">*</span></label>
                        <select name="year" class="form-select" required>
                            <option value="<?= $year ?>" selected>Năm <?= $year ?></option>
                            <option value="<?= $year + 1 ?>">Năm <?= $year + 1 ?></option>
                        </select>
                    </div>

                    <div class="alert alert-info py-2 px-3 small" style="border-radius: 8px;">
                        <h6 class="fw-bold mb-1"><i class="fas fa-cogs"></i> Cơ chế tính toán chuẩn Frappe HRMS:</h6>
                        <ul class="mb-0 ps-3">
                            <li><strong>Phép cơ bản:</strong> 12 ngày làm việc/năm (theo Luật Lao động 2019).</li>
                            <li><strong>Tính theo tỷ lệ (Proration):</strong> Nhân sự vào làm giữa năm sẽ được cấp theo số tháng làm việc thực tế: <code>((12 - Tháng vào làm + 1) / 12) * 12</code>.</li>
                            <li><strong>Thâm niên (Seniority Bonus):</strong> Cứ mỗi 5 năm làm việc tính đến đầu năm được cộng thêm <strong>+1 ngày phép</strong> (Điều 114 BLLĐ 2019).</li>
                            <li>Tự động giữ nguyên số ngày phép chuyển tiếp và tính lại số dư còn lại.</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary btn-sm fw-bold">
                        <i class="fas fa-play me-1"></i> Bắt đầu chạy phân bổ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- MODAL: KẾT CHUYỂN PHÉP TỒN NĂM CŨ (CARRY FORWARD) -->
<!-- ============================================================ -->
<div class="modal fade" id="carryForwardModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 14px; border:none; box-shadow: 0 20px 40px rgba(0,0,0,0.15);">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title fw-bold"><i class="fas fa-share-square me-2"></i> Kết chuyển phép tồn sang năm mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= BASE_URL ?>/leave/carryForward" method="POST">
                <?= Session::csrfField() ?>
                <div class="modal-body p-4">
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-bold small">Chuyển từ năm:</label>
                            <input type="number" name="from_year" class="form-control" value="<?= $year - 1 ?>" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold small">Chuyển sang năm:</label>
                            <input type="number" name="to_year" class="form-control" value="<?= $year ?>" required>
                        </div>
                    </div>

                    <div class="alert alert-warning py-2 px-3 small" style="border-radius: 8px;">
                        <h6 class="fw-bold mb-1"><i class="fas fa-shield-alt"></i> Quy tắc chính sách POSUNG:</h6>
                        <ul class="mb-0 ps-3">
                            <li>Chỉ áp dụng cho nhân viên có số dư phép còn lại lớn hơn 0 ở năm cũ.</li>
                            <li>Giới hạn tối đa: <strong>5.0 ngày phép</strong> (theo cấu hình max_carry_forward).</li>
                            <li>Hạn sử dụng phép chuyển tiếp: <strong>Hết ngày 31/03</strong> của năm mới (3 tháng).</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Hủy bỏ</button>
                    <button type="submit" class="btn btn-warning text-dark btn-sm fw-bold">
                        <i class="fas fa-check me-1"></i> Thực hiện kết chuyển
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- MODAL: ĐIỀU CHỈNH ĐỊNH NGẠCH CÁ NHÂN -->
<!-- ============================================================ -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 14px;">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold"><i class="fas fa-user-edit me-2"></i> Điều chỉnh định ngạch phép</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= BASE_URL ?>/leave/saveAllocation" method="POST">
                <?= Session::csrfField() ?>
                <input type="hidden" name="employee_id" id="editEmpId">
                <input type="hidden" name="year" value="<?= $year ?>">
                <input type="hidden" name="leave_type_id" value="1">

                <div class="modal-body p-4">
                    <div class="p-3 bg-light rounded mb-3">
                        <div class="fw-bold text-dark" id="editEmpName">---</div>
                        <div class="text-muted small" id="editEmpDept">---</div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-bold small">Phép tiêu chuẩn (ngày) <span class="text-danger">*</span></label>
                            <input type="number" step="0.5" min="0" name="entitled_days" id="editEntitled" class="form-control fw-bold text-primary" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold small">Phép chuyển tiếp (ngày)</label>
                            <input type="number" step="0.5" min="0" name="carried_forward_days" id="editCarried" class="form-control fw-bold text-warning" required>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label fw-bold small">Ghi chú điều chỉnh / Quyết định bổ sung</label>
                        <textarea name="notes" id="editNotes" class="form-control" rows="2" placeholder="Ví dụ: Bổ sung phép theo thỏa thuận ban giám đốc..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary btn-sm fw-bold">Lưu thay đổi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let autoAllocateModal;
let carryForwardModal;
let editModal;

document.addEventListener('DOMContentLoaded', () => {
    autoAllocateModal = new bootstrap.Modal(document.getElementById('autoAllocateModal'));
    carryForwardModal = new bootstrap.Modal(document.getElementById('carryForwardModal'));
    editModal = new bootstrap.Modal(document.getElementById('editModal'));
});

function openAutoAllocateModal() {
    autoAllocateModal.show();
}

function openCarryForwardModal() {
    carryForwardModal.show();
}

function openEditModal(alloc) {
    document.getElementById('editEmpId').value = alloc.employee_id;
    document.getElementById('editEmpName').textContent = alloc.full_name + ' (' + alloc.emp_code + ')';
    document.getElementById('editEmpDept').textContent = (alloc.dept_name || 'Phòng ban') + ' - ' + (alloc.pos_title || '');
    document.getElementById('editEntitled').value = alloc.entitled_days;
    document.getElementById('editCarried').value = alloc.carried_forward_days;
    document.getElementById('editNotes').value = alloc.notes || '';
    editModal.show();
}
</script>
