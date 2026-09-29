<?php
/**
 * View: insurance/register.php – Đăng ký & Cập nhật Hồ sơ Bảo hiểm Nhân viên
 */

$ins = $insurance ?? null;
$salaryVal = $ins ? (float)$ins->insurance_salary : 6000000;
?>

<!-- BREADCRUMB -->
<div class="breadcrumb-nav mb-3" style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: var(--text-muted);">
    <a href="<?= BASE_URL ?>/insurance" style="color: var(--primary); text-decoration: none;">
        <i class="fas fa-shield-alt"></i> Quản lý Bảo hiểm Xã hội
    </a>
    <i class="fas fa-chevron-right" style="font-size: 10px;"></i>
    <a href="<?= BASE_URL ?>/insurance/employees" style="color: var(--primary); text-decoration: none;">
        Danh sách tham gia BH
    </a>
    <i class="fas fa-chevron-right" style="font-size: 10px;"></i>
    <span>Hồ sơ BH: <?= htmlspecialchars($employee->full_name) ?></span>
</div>

<!-- EMPLOYEE INFO HEADER -->
<div class="card mb-4" style="border: 1px solid var(--border); box-shadow: 0 4px 18px rgba(0,0,0,0.04);">
    <div class="card-body" style="padding: 20px 24px;">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <div style="width: 58px; height: 58px; border-radius: 50%; background: #e0e7ff; color: #4338ca; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 22px; flex-shrink: 0; overflow: hidden; border: 2px solid var(--border);">
                    <?php if (!empty($employee->avatar_path)): ?>
                        <img src="<?= BASE_URL . '/' . htmlspecialchars($employee->avatar_path) ?>" alt="" style="width: 100%; height: 100%; object-fit: cover;">
                    <?php else: ?>
                        <?= mb_strtoupper(mb_substr($employee->full_name, 0, 1, 'UTF-8'), 'UTF-8') ?>
                    <?php endif; ?>
                </div>
                <div>
                    <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                        <h3 style="font-size: 19px; font-weight: 700; margin: 0; color: var(--text);">
                            <?= htmlspecialchars($employee->full_name) ?>
                        </h3>
                        <span class="badge bg-light text-dark" style="border: 1px solid var(--border); font-size: 12px;">
                            Mã: <?= htmlspecialchars($employee->emp_code) ?>
                        </span>
                        <?php if ($ins && $ins->status === 'Active'): ?>
                            <span class="badge bg-success text-white"><i class="fas fa-check-circle"></i> Đang tham gia</span>
                        <?php elseif ($ins && $ins->status === 'Suspended'): ?>
                            <span class="badge bg-warning text-dark"><i class="fas fa-pause-circle"></i> Tạm dừng đóng</span>
                        <?php elseif ($ins && $ins->status === 'Stopped'): ?>
                            <span class="badge bg-danger text-white"><i class="fas fa-times-circle"></i> Đã dừng đóng</span>
                        <?php else: ?>
                            <span class="badge bg-secondary text-white">Chưa đăng ký</span>
                        <?php endif; ?>
                    </div>
                    <div style="font-size: 13px; color: var(--text-muted); display: flex; gap: 16px; flex-wrap: wrap;">
                        <span><i class="fas fa-building text-primary"></i> <?= htmlspecialchars($employee->dept_name ?? 'Chưa gán') ?></span>
                        <span><i class="fas fa-id-badge text-primary"></i> <?= htmlspecialchars($employee->pos_title ?? '---') ?></span>
                        <span><i class="fas fa-id-card text-primary"></i> CCCD/CMND: <strong><?= htmlspecialchars($employee->id_card_no ?? '---') ?></strong></span>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2">
                <a href="<?= BASE_URL ?>/employee/detail/<?= $employee->id ?>" target="_blank" class="btn btn-ghost" style="border: 1px solid var(--border);">
                    <i class="fas fa-user"></i> Xem Hồ sơ 360°
                </a>
                <a href="<?= BASE_URL ?>/insurance/employees" class="btn btn-ghost" style="border: 1px solid var(--border);">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- FORM CẬP NHẬT / ĐĂNG KÝ BẢO HIỂM -->
    <div class="col-lg-7">
        <div class="card" style="border: 1px solid var(--border); box-shadow: 0 2px 12px rgba(0,0,0,0.03);">
            <div class="card-header" style="background: var(--bg-hover); padding: 14px 20px; border-bottom: 1px solid var(--border);">
                <h4 style="margin: 0; font-size: 15px; font-weight: 700; color: var(--text);">
                    <i class="fas fa-file-signature text-primary"></i> Thiết Lập Thông Tin Bảo Hiểm Bắt Buộc
                </h4>
            </div>
            <div class="card-body" style="padding: 24px;">
                <form action="<?= BASE_URL ?>/insurance/saveRegistration" method="POST" id="insuranceForm">
                    <input type="hidden" name="_csrf_token" value="<?= Session::generateCsrfToken() ?>">
                    <input type="hidden" name="employee_id" value="<?= $employee->id ?>">

                    <div class="row g-3">
                        <!-- Số sổ BHXH -->
                        <div class="col-md-6">
                            <label class="form-label" style="font-weight: 600; font-size: 13px;">
                                Số Sổ BHXH (Mã số BHXH)
                            </label>
                            <div class="input-group">
                                <span class="input-group-text" style="background: var(--bg-hover); border-color: var(--border);">
                                    <i class="fas fa-book text-muted"></i>
                                </span>
                                <input type="text" name="social_insurance_no" class="form-control" 
                                       placeholder="Gồm 10 chữ số..." 
                                       value="<?= htmlspecialchars($ins->social_insurance_no ?? $employee->social_insurance_no ?? '') ?>">
                            </div>
                            <div class="form-text" style="font-size: 11.5px;">Mã định danh duy nhất của người tham gia BHXH.</div>
                        </div>

                        <!-- Mã thẻ BHYT -->
                        <div class="col-md-6">
                            <label class="form-label" style="font-weight: 600; font-size: 13px;">
                                Mã Thẻ BHYT
                            </label>
                            <div class="input-group">
                                <span class="input-group-text" style="background: var(--bg-hover); border-color: var(--border);">
                                    <i class="fas fa-heart text-muted"></i>
                                </span>
                                <input type="text" name="health_insurance_no" class="form-control" 
                                       placeholder="Gồm 15 ký tự..." 
                                       value="<?= htmlspecialchars($ins->health_insurance_no ?? $employee->health_insurance_no ?? '') ?>">
                            </div>
                            <div class="form-text" style="font-size: 11.5px;">Ví dụ: DN401...</div>
                        </div>

                        <!-- Mã Bệnh viện KCB -->
                        <div class="col-md-4">
                            <label class="form-label" style="font-weight: 600; font-size: 13px;">
                                Mã Cơ sở KCB
                            </label>
                            <input type="text" name="hospital_code" class="form-control" 
                                   placeholder="VD: 01-015" 
                                   value="<?= htmlspecialchars($ins->hospital_code ?? '01-015') ?>">
                        </div>

                        <!-- Tên Bệnh viện KCB ban đầu -->
                        <div class="col-md-8">
                            <label class="form-label" style="font-weight: 600; font-size: 13px;">
                                Nơi Đăng ký Khám Chữa Bệnh Ban Đầu
                            </label>
                            <div class="input-group">
                                <span class="input-group-text" style="background: var(--bg-hover); border-color: var(--border);">
                                    <i class="fas fa-hospital text-muted"></i>
                                </span>
                                <input type="text" name="hospital_name" class="form-control" 
                                       placeholder="Tên bệnh viện / trung tâm y tế..." 
                                       value="<?= htmlspecialchars($ins->hospital_name ?? 'Bệnh viện Đa khoa Quốc tế Hải Phòng') ?>">
                            </div>
                        </div>

                        <!-- Mức lương đóng BHXH -->
                        <div class="col-md-6">
                            <label class="form-label" style="font-weight: 600; font-size: 13px;">
                                Mức Tiền Lương Đóng BH (VNĐ) <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text" style="background: var(--bg-hover); border-color: var(--border); font-weight: 600;">
                                    ₫
                                </span>
                                <input type="text" name="insurance_salary" id="insuranceSalaryInput" class="form-control" required
                                       placeholder="VD: 6,000,000" 
                                       value="<?= number_format($salaryVal, 0, ',', '.') ?>" 
                                       oninput="formatCurrency(this); recalculateLiveShare();">
                            </div>
                            <div class="form-text" style="font-size: 11.5px;">Mức lương làm căn cứ trích nộp BHXH, BHYT, BHTN hàng tháng.</div>
                        </div>

                        <!-- Trạng thái tham gia -->
                        <div class="col-md-6">
                            <label class="form-label" style="font-weight: 600; font-size: 13px;">
                                Trạng thái Tham gia <span class="text-danger">*</span>
                            </label>
                            <select name="status" class="form-select" required>
                                <option value="Active" <?= (!$ins || $ins->status === 'Active') ? 'selected' : '' ?>>🟢 Đang tham gia (Active)</option>
                                <option value="Suspended" <?= ($ins && $ins->status === 'Suspended') ? 'selected' : '' ?>>🟡 Tạm dừng đóng (Thai sản, ốm dài ngày...)</option>
                                <option value="Stopped" <?= ($ins && $ins->status === 'Stopped') ? 'selected' : '' ?>>🔴 Đã dừng đóng (Nghỉ việc, thôi việc)</option>
                            </select>
                        </div>

                        <!-- Ngày bắt đầu & kết thúc -->
                        <div class="col-md-6">
                            <label class="form-label" style="font-weight: 600; font-size: 13px;">
                                Ngày bắt đầu đóng <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="start_date" class="form-control" required 
                                   value="<?= htmlspecialchars($ins->start_date ?? date('Y-m-01')) ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" style="font-weight: 600; font-size: 13px;">
                                Ngày dừng đóng (nếu có)
                            </label>
                            <input type="date" name="end_date" class="form-control" 
                                   value="<?= htmlspecialchars($ins->end_date ?? '') ?>">
                        </div>

                        <!-- Ghi chú -->
                        <div class="col-12">
                            <label class="form-label" style="font-weight: 600; font-size: 13px;">
                                Ghi chú hồ sơ
                            </label>
                            <textarea name="notes" class="form-control" rows="2" 
                                      placeholder="Ghi chú thêm về hồ sơ sổ BHXH, đợt báo tăng hoặc thông tin chuyển bảo hiểm..."><?= htmlspecialchars($ins->notes ?? '') ?></textarea>
                        </div>
                    </div>

                    <!-- SUBMIT BUTTON -->
                    <div class="d-flex justify-content-end gap-2 mt-4 pt-3" style="border-top: 1px solid var(--border);">
                        <a href="<?= BASE_URL ?>/insurance/employees" class="btn btn-ghost" style="border: 1px solid var(--border);">
                            <i class="fas fa-times"></i> Hủy
                        </a>
                        <button type="submit" class="btn btn-primary" style="padding: 8px 24px; font-weight: 600; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);">
                            <i class="fas fa-save"></i> Lưu Hồ Sơ Bảo Hiểm
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- BOX TÍNH TOÁN TRÍCH NỘP HÀNG THÁNG CỦA NHÂN VIÊN (LIVE PREVIEW) -->
    <div class="col-lg-5">
        <div class="card mb-4" style="border: 1px solid rgba(79, 70, 229, 0.2); box-shadow: 0 4px 18px rgba(79,70,229,0.06); border-radius: 12px;">
            <div class="card-header" style="background: linear-gradient(135deg, rgba(79, 70, 229, 0.08), rgba(99, 102, 241, 0.02)); border-bottom: 1px solid var(--border); padding: 14px 20px;">
                <h5 style="margin: 0; font-size: 14.5px; font-weight: 700; color: var(--primary);">
                    <i class="fas fa-calculator"></i> Chi Phí Trích Nộp Hàng Tháng (Dự Tính)
                </h5>
            </div>
            <div class="card-body p-3">
                <div class="mb-3 text-center" style="background: var(--bg-hover); padding: 12px; border-radius: 8px;">
                    <div style="font-size: 12px; color: var(--text-muted);">Mức tiền lương làm căn cứ đóng:</div>
                    <div id="liveBaseSalaryDisplay" style="font-size: 22px; font-weight: 800; color: var(--text); margin-top: 2px;">
                        <?= number_format($salaryVal, 0, ',', '.') ?>đ
                    </div>
                </div>

                <!-- NLĐ KHẤU TRỪ (10.5%) -->
                <div class="mb-3" style="border-left: 3px solid #4f46e5; padding-left: 10px;">
                    <div class="d-flex justify-content-between align-items-center">
                        <strong style="color: #4f46e5; font-size: 13.5px;">1. Người lao động đóng (10.5%):</strong>
                        <strong id="liveEmpTotal" style="color: #4f46e5; font-size: 14.5px;">0đ</strong>
                    </div>
                    <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">
                        • BHXH (8%): <span id="liveEmpBhxh" class="text-dark fw-bold">0đ</span><br>
                        • BHYT (1.5%): <span id="liveEmpBhyt" class="text-dark fw-bold">0đ</span><br>
                        • BHTN (1%): <span id="liveEmpBhtn" class="text-dark fw-bold">0đ</span>
                    </div>
                </div>

                <!-- DOANH NGHIỆP ĐÓNG (22%) -->
                <div class="mb-3" style="border-left: 3px solid #d97706; padding-left: 10px;">
                    <div class="d-flex justify-content-between align-items-center">
                        <strong style="color: #d97706; font-size: 13.5px;">2. Doanh nghiệp nộp (22.0%):</strong>
                        <strong id="liveComTotal" style="color: #d97706; font-size: 14.5px;">0đ</strong>
                    </div>
                    <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">
                        • BHXH (17.5%): <span id="liveComBhxh" class="text-dark fw-bold">0đ</span><br>
                        • BHYT (3%): <span id="liveComBhyt" class="text-dark fw-bold">0đ</span><br>
                        • BHTN (1%): <span id="liveComBhtn" class="text-dark fw-bold">0đ</span><br>
                        • BHTNLĐ-BNN (0.5%): <span id="liveComBhtnld" class="text-dark fw-bold">0đ</span>
                    </div>
                </div>

                <!-- TỔNG CỘNG (32.5%) -->
                <div class="p-3" style="background: rgba(16, 185, 129, 0.08); border-radius: 8px; border: 1px solid rgba(16, 185, 129, 0.2);">
                    <div class="d-flex justify-content-between align-items-center">
                        <strong style="color: #059669; font-size: 14px;">TỔNG TIỀN NỘP BHXH (32.5%):</strong>
                        <strong id="liveGrandTotal" style="color: #059669; font-size: 18px; font-weight: 800;">0đ</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function formatCurrency(input) {
    let val = input.value.replace(/[^0-9]/g, '');
    if (val) {
        input.value = parseInt(val, 10).toLocaleString('vi-VN');
    } else {
        input.value = '';
    }
}

function recalculateLiveShare() {
    let raw = document.getElementById('insuranceSalaryInput').value.replace(/[^0-9]/g, '');
    let salary = parseFloat(raw) || 0;

    document.getElementById('liveBaseSalaryDisplay').innerText = salary.toLocaleString('vi-VN') + 'đ';

    // NLĐ (10.5%)
    let empBhxh = Math.round(salary * 0.08);
    let empBhyt = Math.round(salary * 0.015);
    let empBhtn = Math.round(salary * 0.01);
    let empTotal = empBhxh + empBhyt + empBhtn;

    document.getElementById('liveEmpBhxh').innerText = empBhxh.toLocaleString('vi-VN') + 'đ';
    document.getElementById('liveEmpBhyt').innerText = empBhyt.toLocaleString('vi-VN') + 'đ';
    document.getElementById('liveEmpBhtn').innerText = empBhtn.toLocaleString('vi-VN') + 'đ';
    document.getElementById('liveEmpTotal').innerText = empTotal.toLocaleString('vi-VN') + 'đ';

    // DN (22%)
    let comBhxh = Math.round(salary * 0.175);
    let comBhyt = Math.round(salary * 0.03);
    let comBhtn = Math.round(salary * 0.01);
    let comBhtnld = Math.round(salary * 0.005);
    let comTotal = comBhxh + comBhyt + comBhtn + comBhtnld;

    document.getElementById('liveComBhxh').innerText = comBhxh.toLocaleString('vi-VN') + 'đ';
    document.getElementById('liveComBhyt').innerText = comBhyt.toLocaleString('vi-VN') + 'đ';
    document.getElementById('liveComBhtn').innerText = comBhtn.toLocaleString('vi-VN') + 'đ';
    document.getElementById('liveComBhtnld').innerText = comBhtnld.toLocaleString('vi-VN') + 'đ';
    document.getElementById('liveComTotal').innerText = comTotal.toLocaleString('vi-VN') + 'đ';

    // Tổng cộng (32.5%)
    let grandTotal = empTotal + comTotal;
    document.getElementById('liveGrandTotal').innerText = grandTotal.toLocaleString('vi-VN') + 'đ';
}

document.addEventListener('DOMContentLoaded', function() {
    recalculateLiveShare();
});
</script>
