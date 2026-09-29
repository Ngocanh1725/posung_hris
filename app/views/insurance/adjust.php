<?php
/**
 * View: insurance/adjust.php – Quản lý Biến động Lao động Bảo hiểm (Báo Tăng/Giảm D02-TS)
 */

$adjTypeBadges = [
    'Tang_Moi'        => ['Báo tăng mới', 'badge bg-success text-white', 'fas fa-user-plus'],
    'Tang_Luong'      => ['Tăng mức lương', 'badge bg-primary text-white', 'fas fa-level-up-alt'],
    'Giam_Han'        => ['Báo giảm hẳn (Nghỉ việc)', 'badge bg-danger text-white', 'fas fa-user-minus'],
    'Giam_ThaiSan'    => ['Giảm thai sản', 'badge bg-info text-dark', 'fas fa-baby'],
    'Giam_OmDau'      => ['Giảm ốm đau', 'badge bg-warning text-dark', 'fas fa-stethoscope'],
    'Giam_KhongLuong' => ['Giảm nghỉ không lương', 'badge bg-secondary text-white', 'fas fa-calendar-times'],
];
?>

<!-- BREADCRUMB -->
<div class="breadcrumb-nav mb-3" style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: var(--text-muted);">
    <a href="<?= BASE_URL ?>/insurance" style="color: var(--primary); text-decoration: none;">
        <i class="fas fa-shield-alt"></i> Quản lý Bảo hiểm Xã hội
    </a>
    <i class="fas fa-chevron-right" style="font-size: 10px;"></i>
    <span>Biến động Báo Tăng / Báo Giảm Lao động (Mẫu D02-TS)</span>
</div>

<!-- TOP ACTIONS & TITLE -->
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div>
        <h3 style="font-size: 18px; font-weight: 700; margin: 0; color: var(--text);">
            <i class="fas fa-exchange-alt text-primary"></i> Biến Động Lao Động Bảo Hiểm (Mẫu D02-TS)
        </h3>
        <p style="margin: 3px 0 0; font-size: 13px; color: var(--text-muted);">
            Lập danh sách báo tăng mới, điều chỉnh lương, báo giảm hẳn hoặc giảm nghỉ chế độ gửi Cơ quan BHXH
        </p>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newAdjustmentModal" style="box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);">
            <i class="fas fa-plus-circle"></i> Thêm Biến Động Mới
        </button>
        <a href="<?= BASE_URL ?>/insurance/report?type=D02TS&month=<?= htmlspecialchars($filters['month'] ?? date('Y-m')) ?>" class="btn btn-outline-secondary">
            <i class="fas fa-file-invoice"></i> Mẫu D02-TS Chuẩn
        </a>
    </div>
</div>

<!-- FILTER CARD -->
<div class="card mb-3" style="border: 1px solid var(--border);">
    <div class="card-body p-3">
        <form method="GET" action="<?= BASE_URL ?>/insurance/adjust" class="row g-2 align-items-center">
            <!-- Tháng -->
            <div class="col-md-3">
                <input type="month" name="month" class="form-control" value="<?= htmlspecialchars($filters['month'] ?? date('Y-m')) ?>" onchange="this.form.submit()">
            </div>

            <!-- Loại biến động -->
            <div class="col-md-3">
                <select name="type" class="form-select" onchange="this.form.submit()">
                    <option value="">-- Tất cả loại biến động --</option>
                    <option value="Tang_Moi" <?= ($filters['type'] === 'Tang_Moi') ? 'selected' : '' ?>>Báo tăng mới</option>
                    <option value="Tang_Luong" <?= ($filters['type'] === 'Tang_Luong') ? 'selected' : '' ?>>Tăng mức lương</option>
                    <option value="Giam_Han" <?= ($filters['type'] === 'Giam_Han') ? 'selected' : '' ?>>Báo giảm hẳn (Nghỉ việc)</option>
                    <option value="Giam_ThaiSan" <?= ($filters['type'] === 'Giam_ThaiSan') ? 'selected' : '' ?>>Giảm thai sản</option>
                    <option value="Giam_OmDau" <?= ($filters['type'] === 'Giam_OmDau') ? 'selected' : '' ?>>Giảm ốm đau dài ngày</option>
                    <option value="Giam_KhongLuong" <?= ($filters['type'] === 'Giam_KhongLuong') ? 'selected' : '' ?>>Giảm nghỉ không lương</option>
                </select>
            </div>

            <!-- Trạng thái -->
            <div class="col-md-2">
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="">-- Trạng thái --</option>
                    <option value="Draft" <?= ($filters['status'] === 'Draft') ? 'selected' : '' ?>>Bản nháp</option>
                    <option value="Submitted" <?= ($filters['status'] === 'Submitted') ? 'selected' : '' ?>>Đã nộp BHXH</option>
                    <option value="Approved" <?= ($filters['status'] === 'Approved') ? 'selected' : '' ?>>BHXH đã duyệt</option>
                    <option value="Rejected" <?= ($filters['status'] === 'Rejected') ? 'selected' : '' ?>>Từ chối</option>
                </select>
            </div>

            <!-- Search -->
            <div class="col-md-3">
                <input type="text" name="search" class="form-control" placeholder="Tìm theo tên, mã NV, công văn..." value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
            </div>

            <!-- Button -->
            <div class="col-md-1">
                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i></button>
            </div>
        </form>
    </div>
</div>

<!-- TABLE ADJUSTMENTS -->
<div class="card" style="border: 1px solid var(--border); box-shadow: 0 2px 10px rgba(0,0,0,0.02);">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
            <thead style="background: var(--bg-hover); color: var(--text-muted); font-size: 11.5px; text-transform: uppercase;">
                <tr>
                    <th style="width: 50px; text-align: center;">STT</th>
                    <th style="width: 230px;">Nhân sự</th>
                    <th style="width: 170px;">Loại biến động</th>
                    <th style="text-align: right; width: 140px;">Lương đóng cũ</th>
                    <th style="text-align: right; width: 140px;">Lương đóng mới</th>
                    <th style="text-align: center; width: 110px;">Ngày hiệu lực</th>
                    <th>Lý do & Số công văn</th>
                    <th style="text-align: center; width: 130px;">Trạng thái</th>
                    <th style="text-align: right; width: 120px;">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($adjustments)): ?>
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <i class="fas fa-calendar-check fa-3x mb-3 text-secondary" style="opacity: 0.5;"></i>
                            <div>Không có bản ghi biến động bảo hiểm nào phù hợp.</div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php $idx = 1; foreach ($adjustments as $adj): ?>
                        <tr>
                            <td style="text-align: center; color: var(--text-muted);"><?= $idx++ ?></td>
                            <td>
                                <strong style="color: var(--text);"><?= htmlspecialchars($adj['full_name']) ?></strong>
                                <div style="font-size: 11.5px; color: var(--text-muted);">
                                    Mã: <span class="badge bg-light text-dark" style="border: 1px solid var(--border);"><?= htmlspecialchars($adj['emp_code']) ?></span>
                                    • Sổ: <?= htmlspecialchars($adj['social_insurance_no'] ?? 'Chưa cấp') ?>
                                </div>
                            </td>
                            <td>
                                <?php $b = $adjTypeBadges[$adj['adjustment_type']] ?? [$adj['adjustment_type'], 'badge bg-secondary', '']; ?>
                                <span class="<?= $b[1] ?>" style="font-size: 11px; padding: 4px 8px; border-radius: 12px;">
                                    <i class="<?= $b[2] ?>"></i> <?= $b[0] ?>
                                </span>
                            </td>
                            <td style="text-align: right; color: var(--text-muted);">
                                <?= $adj['old_salary'] > 0 ? number_format((float)$adj['old_salary'], 0, ',', '.') . 'đ' : '---' ?>
                            </td>
                            <td style="text-align: right; font-weight: 700; color: #10b981;">
                                <?= $adj['new_salary'] > 0 ? number_format((float)$adj['new_salary'], 0, ',', '.') . 'đ' : '---' ?>
                            </td>
                            <td style="text-align: center;">
                                <?= date('d/m/Y', strtotime($adj['effective_date'])) ?>
                            </td>
                            <td>
                                <div style="font-weight: 500; color: var(--text);"><?= htmlspecialchars($adj['reason']) ?></div>
                                <?php if (!empty($adj['doc_no'])): ?>
                                    <div style="font-size: 11.5px; color: var(--primary);">
                                        <i class="fas fa-file-alt"></i> Số hồ sơ: <?= htmlspecialchars($adj['doc_no']) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center;">
                                <?php if ($adj['status'] === 'Approved'): ?>
                                    <span class="badge bg-success text-white" style="font-size: 11px;"><i class="fas fa-check"></i> Đã duyệt</span>
                                <?php elseif ($adj['status'] === 'Submitted'): ?>
                                    <span class="badge bg-primary text-white" style="font-size: 11px;"><i class="fas fa-paper-plane"></i> Đã gửi BHXH</span>
                                <?php elseif ($adj['status'] === 'Rejected'): ?>
                                    <span class="badge bg-danger text-white" style="font-size: 11px;"><i class="fas fa-times"></i> Từ chối</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark" style="font-size: 11px;"><i class="fas fa-clock"></i> Bản nháp</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: right;">
                                <?php if ($adj['status'] !== 'Approved'): ?>
                                    <button type="button" class="btn btn-sm btn-outline-success" 
                                            onclick="updateAdjStatus(<?= $adj['id'] ?>, 'Approved')" 
                                            title="Duyệt đợt biến động này">
                                        <i class="fas fa-check"></i> Duyệt
                                    </button>
                                <?php else: ?>
                                    <span class="text-muted" style="font-size: 12px;"><i class="fas fa-lock"></i> Đã xong</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL THÊM BIẾN ĐỘNG LAO ĐỘNG (D02-TS) -->
<div class="modal fade" id="newAdjustmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius: 14px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            <div class="modal-header" style="background: linear-gradient(135deg, rgba(79,70,229,0.08), rgba(99,102,241,0.02)); border-bottom: 1px solid var(--border);">
                <h5 class="modal-title" style="font-weight: 700; color: var(--text);">
                    <i class="fas fa-plus-circle text-primary"></i> Lập Bản Ghi Biến Động Bảo Hiểm (Báo Tăng/Giảm)
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= BASE_URL ?>/insurance/saveAdjustment" method="POST">
                <input type="hidden" name="_csrf_token" value="<?= Session::generateCsrfToken() ?>">

                <div class="modal-body" style="padding: 24px;">
                    <div class="row g-3">
                        <!-- Chọn nhân viên -->
                        <div class="col-md-6">
                            <label class="form-label" style="font-weight: 600; font-size: 13px;">
                                Chọn Nhân viên <span class="text-danger">*</span>
                            </label>
                            <select name="employee_id" class="form-select" required>
                                <option value="">-- Chọn nhân sự --</option>
                                <?php foreach ($employees as $emp): ?>
                                    <option value="<?= $emp->id ?>">
                                        [<?= htmlspecialchars($emp->emp_code) ?>] <?= htmlspecialchars($emp->full_name) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Loại biến động -->
                        <div class="col-md-6">
                            <label class="form-label" style="font-weight: 600; font-size: 13px;">
                                Phân loại biến động <span class="text-danger">*</span>
                            </label>
                            <select name="adjustment_type" class="form-select" required>
                                <option value="Tang_Moi">🟢 Báo tăng mới (Ký HĐLĐ mới)</option>
                                <option value="Tang_Luong">🔵 Tăng mức tiền lương đóng BH</option>
                                <option value="Giam_Han">🔴 Báo giảm hẳn (Chấm dứt HĐLĐ, nghỉ việc)</option>
                                <option value="Giam_ThaiSan">🟣 Giảm nghỉ thai sản (Hưởng chế độ BHXH)</option>
                                <option value="Giam_OmDau">🟡 Giảm nghỉ ốm đau dài ngày</option>
                                <option value="Giam_KhongLuong">⚪ Giảm nghỉ không lương từ 14 ngày/tháng</option>
                            </select>
                        </div>

                        <!-- Lương cũ & mới -->
                        <div class="col-md-6">
                            <label class="form-label" style="font-weight: 600; font-size: 13px;">
                                Mức lương đóng cũ (VNĐ)
                            </label>
                            <input type="text" name="old_salary" class="form-control" placeholder="0" oninput="formatNumberInput(this)">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" style="font-weight: 600; font-size: 13px;">
                                Mức lương đóng mới (VNĐ)
                            </label>
                            <input type="text" name="new_salary" class="form-control" placeholder="6,000,000" oninput="formatNumberInput(this)">
                        </div>

                        <!-- Tháng áp dụng & ngày hiệu lực -->
                        <div class="col-md-6">
                            <label class="form-label" style="font-weight: 600; font-size: 13px;">
                                Tháng áp dụng (YYYY-MM) <span class="text-danger">*</span>
                            </label>
                            <input type="month" name="effective_month" class="form-control" required value="<?= date('Y-m') ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" style="font-weight: 600; font-size: 13px;">
                                Ngày hiệu lực <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="effective_date" class="form-control" required value="<?= date('Y-m-01') ?>">
                        </div>

                        <!-- Số công văn -->
                        <div class="col-md-6">
                            <label class="form-label" style="font-weight: 600; font-size: 13px;">
                                Số công văn / Đợt nộp hồ sơ D02-TS
                            </label>
                            <input type="text" name="doc_no" class="form-control" placeholder="VD: D02-202610-01" value="D02-<?= date('Ym') ?>-01">
                        </div>

                        <!-- Trạng thái -->
                        <div class="col-md-6">
                            <label class="form-label" style="font-weight: 600; font-size: 13px;">
                                Trạng thái phê duyệt <span class="text-danger">*</span>
                            </label>
                            <select name="status" class="form-select" required>
                                <option value="Approved" selected>✅ Đã duyệt (Cập nhật ngay vào hồ sơ bảo hiểm)</option>
                                <option value="Submitted">📤 Đã nộp lên cổng BHXH</option>
                                <option value="Draft">📝 Bản nháp nội bộ</option>
                            </select>
                        </div>

                        <!-- Lý do chi tiết -->
                        <div class="col-12">
                            <label class="form-label" style="font-weight: 600; font-size: 13px;">
                                Lý do biến động chi tiết <span class="text-danger">*</span>
                            </label>
                            <textarea name="reason" class="form-control" rows="2" required
                                      placeholder="VD: Ký hợp đồng lao động không xác định thời hạn từ ngày 01/10/2026..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer" style="background: var(--bg-hover); border-top: 1px solid var(--border);">
                    <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary" style="padding: 7px 20px;">
                        <i class="fas fa-save"></i> Ghi Nhận Biến Động
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function formatNumberInput(input) {
    let val = input.value.replace(/[^0-9]/g, '');
    input.value = val ? parseInt(val, 10).toLocaleString('vi-VN') : '';
}

function updateAdjStatus(id, status) {
    if (!confirm('Xác nhận phê duyệt đợt biến động này và tự động cập nhật vào hồ sơ bảo hiểm nhân viên?')) return;

    fetch('<?= BASE_URL ?>/insurance/updateAdjustmentStatus/' + id, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'status=' + encodeURIComponent(status) + '&_csrf_token=<?= Session::generateCsrfToken() ?>'
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            window.location.reload();
        } else {
            alert(data.message || 'Lỗi xử lý.');
        }
    })
    .catch(() => alert('Không thể kết nối đến máy chủ.'));
}
</script>
