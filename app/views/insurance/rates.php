<?php
/**
 * View: insurance/rates.php – Quản lý Tỷ lệ Đóng Bảo hiểm Xã hội theo Năm
 */

$empTotal = 0;
$comTotal = 0;
foreach ($rates as $r) {
    if ($r['status'] === 'Active') {
        $empTotal += (float)$r['employee_rate'];
        $comTotal += (float)$r['company_rate'];
    }
}
$grandTotal = $empTotal + $comTotal;
?>

<!-- BREADCRUMB -->
<div class="breadcrumb-nav mb-3" style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: var(--text-muted);">
    <a href="<?= BASE_URL ?>/insurance" style="color: var(--primary); text-decoration: none;">
        <i class="fas fa-shield-alt"></i> Quản lý Bảo hiểm Xã hội
    </a>
    <i class="fas fa-chevron-right" style="font-size: 10px;"></i>
    <span>Cấu hình Tỷ lệ Đóng BHXH, BHYT, BHTN</span>
</div>

<!-- HEADER CARDS ROW -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card p-3" style="border: 1px solid rgba(79, 70, 229, 0.2); background: rgba(79, 70, 229, 0.03); border-radius: 12px;">
            <div class="text-muted" style="font-size: 12px; font-weight: 600; text-transform: uppercase;">Người Lao Động Đóng</div>
            <div style="font-size: 26px; font-weight: 800; color: #4f46e5; margin-top: 4px;"><?= number_format($empTotal, 1) ?>%</div>
            <div style="font-size: 12px; color: var(--text-muted);">Khấu trừ trực tiếp vào lương hàng tháng</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card p-3" style="border: 1px solid rgba(217, 119, 6, 0.2); background: rgba(217, 119, 6, 0.03); border-radius: 12px;">
            <div class="text-muted" style="font-size: 12px; font-weight: 600; text-transform: uppercase;">Doanh Nghiệp Đóng</div>
            <div style="font-size: 26px; font-weight: 800; color: #d97706; margin-top: 4px;"><?= number_format($comTotal, 1) ?>%</div>
            <div style="font-size: 12px; color: var(--text-muted);">Tính vào chi phí quản lý / sản xuất của DN</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card p-3" style="border: 1px solid rgba(16, 185, 129, 0.2); background: rgba(16, 185, 129, 0.03); border-radius: 12px;">
            <div class="text-muted" style="font-size: 12px; font-weight: 600; text-transform: uppercase;">Tổng Trích Nộp Cơ Quan BH</div>
            <div style="font-size: 26px; font-weight: 800; color: #10b981; margin-top: 4px;"><?= number_format($grandTotal, 1) ?>%</div>
            <div style="font-size: 12px; color: var(--text-muted);">Tổng tỷ lệ nộp cho Cơ quan BHXH</div>
        </div>
    </div>
</div>

<!-- ACTIONS TOOLBAR -->
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div>
        <h3 style="font-size: 18px; font-weight: 700; margin: 0; color: var(--text);">
            <i class="fas fa-percentage text-primary"></i> Bảng Tỷ Lệ Đóng Bảo Hiểm (Năm <?= $year ?>)
        </h3>
    </div>
    <div class="d-flex align-items-center gap-2">
        <form method="GET" action="<?= BASE_URL ?>/insurance/rates" class="d-flex align-items-center gap-1">
            <select name="year" class="form-select form-select-sm" onchange="this.form.submit()">
                <?php for ($y = date('Y') - 2; $y <= date('Y') + 2; $y++): ?>
                    <option value="<?= $y ?>" <?= ($y == $year) ? 'selected' : '' ?>>Năm <?= $y ?></option>
                <?php endfor; ?>
            </select>
        </form>
        <button type="button" class="btn btn-primary btn-sm" onclick="openRateModal()">
            <i class="fas fa-plus"></i> Thêm Tỷ Lệ Mới
        </button>
    </div>
</div>

<!-- RATES TABLE -->
<div class="card" style="border: 1px solid var(--border); box-shadow: 0 2px 10px rgba(0,0,0,0.02);">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size: 13.5px;">
            <thead style="background: var(--bg-hover); color: var(--text-muted); font-size: 12px; text-transform: uppercase;">
                <tr>
                    <th style="width: 50px; text-align: center;">STT</th>
                    <th style="width: 120px;">Mã loại</th>
                    <th>Tên Loại Bảo Hiểm</th>
                    <th style="text-align: center; width: 120px;">NLĐ Đóng (%)</th>
                    <th style="text-align: center; width: 120px;">DN Đóng (%)</th>
                    <th style="text-align: center; width: 120px;">Tổng (%)</th>
                    <th style="text-align: center; width: 120px;">Năm áp dụng</th>
                    <th style="text-align: center; width: 110px;">Trạng thái</th>
                    <th style="text-align: right; width: 90px;">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rates)): ?>
                    <tr>
                        <td colspan="9" class="text-center py-4 text-muted">Chưa có cấu hình tỷ lệ đóng cho năm <?= $year ?>.</td>
                    </tr>
                <?php else: ?>
                    <?php $idx = 1; foreach ($rates as $r): ?>
                        <tr>
                            <td style="text-align: center; color: var(--text-muted);"><?= $idx++ ?></td>
                            <td>
                                <span class="badge bg-light text-dark" style="border: 1px solid var(--border); font-size: 12px; font-weight: 700;">
                                    <?= htmlspecialchars($r['insurance_type']) ?>
                                </span>
                            </td>
                            <td>
                                <strong style="color: var(--text);"><?= htmlspecialchars($r['name']) ?></strong>
                                <?php if (!empty($r['notes'])): ?>
                                    <div style="font-size: 11.5px; color: var(--text-muted);"><?= htmlspecialchars($r['notes']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center; font-weight: 700; color: #4f46e5; font-size: 14px;">
                                <?= number_format((float)$r['employee_rate'], 1) ?>%
                            </td>
                            <td style="text-align: center; font-weight: 700; color: #d97706; font-size: 14px;">
                                <?= number_format((float)$r['company_rate'], 1) ?>%
                            </td>
                            <td style="text-align: center; font-weight: 800; color: #10b981; font-size: 14px;">
                                <?= number_format((float)$r['employee_rate'] + (float)$r['company_rate'], 1) ?>%
                            </td>
                            <td style="text-align: center;"><?= $r['effective_year'] ?></td>
                            <td style="text-align: center;">
                                <?php if ($r['status'] === 'Active'): ?>
                                    <span class="badge bg-success" style="font-size: 11px;">Đang áp dụng</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary" style="font-size: 11px;">Hết hiệu lực</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: right;">
                                <button type="button" class="btn btn-sm btn-ghost" style="border: 1px solid var(--border);"
                                        onclick='editRate(<?= json_encode($r) ?>)'>
                                    <i class="fas fa-edit text-primary"></i> Sửa
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL THÊM / SỬA TỶ LỆ BẢO HIỂM -->
<div class="modal fade" id="rateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius: 14px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            <div class="modal-header" style="background: linear-gradient(135deg, rgba(79,70,229,0.08), rgba(99,102,241,0.02)); border-bottom: 1px solid var(--border);">
                <h5 class="modal-title" id="rateModalTitle" style="font-weight: 700; color: var(--text);">
                    <i class="fas fa-percentage text-primary"></i> Cấu Hình Tỷ Lệ Đóng Bảo Hiểm
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= BASE_URL ?>/insurance/saveRate" method="POST">
                <input type="hidden" name="_csrf_token" value="<?= Session::generateCsrfToken() ?>">
                <input type="hidden" name="id" id="rateId" value="">

                <div class="modal-body" style="padding: 20px;">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" style="font-weight: 600; font-size: 13px;">Mã loại BH <span class="text-danger">*</span></label>
                            <select name="insurance_type" id="rateType" class="form-select" required>
                                <option value="BHXH">BHXH (Bảo hiểm Xã hội)</option>
                                <option value="BHYT">BHYT (Bảo hiểm Y tế)</option>
                                <option value="BHTN">BHTN (Bảo hiểm Thất nghiệp)</option>
                                <option value="BHTNLD">BHTNLĐ (Tai nạn LĐ - BNN)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="font-weight: 600; font-size: 13px;">Năm áp dụng <span class="text-danger">*</span></label>
                            <input type="number" name="effective_year" id="rateYear" class="form-control" required value="<?= $year ?>">
                        </div>

                        <div class="col-12">
                            <label class="form-label" style="font-weight: 600; font-size: 13px;">Tên diễn giải <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="rateName" class="form-control" required placeholder="VD: Bảo hiểm Xã hội...">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" style="font-weight: 600; font-size: 13px;">Tỷ lệ NLĐ đóng (%) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="employee_rate" id="rateEmp" class="form-control" required placeholder="8.00">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" style="font-weight: 600; font-size: 13px;">Tỷ lệ DN đóng (%) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="company_rate" id="rateCom" class="form-control" required placeholder="17.50">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" style="font-weight: 600; font-size: 13px;">Ngày bắt đầu hiệu lực</label>
                            <input type="date" name="start_date" id="rateStartDate" class="form-control" value="<?= date('Y-01-01') ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" style="font-weight: 600; font-size: 13px;">Trạng thái</label>
                            <select name="status" id="rateStatus" class="form-select">
                                <option value="Active">Đang áp dụng</option>
                                <option value="Inactive">Hết hiệu lực</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label" style="font-weight: 600; font-size: 13px;">Ghi chú</label>
                            <textarea name="notes" id="rateNotes" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer" style="background: var(--bg-hover); border-top: 1px solid var(--border);">
                    <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary" style="padding: 7px 20px;">
                        <i class="fas fa-save"></i> Lưu Tỷ Lệ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openRateModal() {
    document.getElementById('rateId').value = '';
    document.getElementById('rateModalTitle').innerText = 'Thêm Cấu Hình Tỷ Lệ Mới';
    document.getElementById('rateName').value = '';
    document.getElementById('rateEmp').value = '0.00';
    document.getElementById('rateCom').value = '0.00';
    document.getElementById('rateNotes').value = '';
    new bootstrap.Modal(document.getElementById('rateModal')).show();
}

function editRate(r) {
    document.getElementById('rateId').value = r.id;
    document.getElementById('rateModalTitle').innerText = 'Chỉnh Sửa Tỷ Lệ: ' + r.name;
    document.getElementById('rateType').value = r.insurance_type;
    document.getElementById('rateYear').value = r.effective_year;
    document.getElementById('rateName').value = r.name;
    document.getElementById('rateEmp').value = r.employee_rate;
    document.getElementById('rateCom').value = r.company_rate;
    document.getElementById('rateStartDate').value = r.start_date;
    document.getElementById('rateStatus').value = r.status;
    document.getElementById('rateNotes').value = r.notes || '';
    new bootstrap.Modal(document.getElementById('rateModal')).show();
}
</script>
