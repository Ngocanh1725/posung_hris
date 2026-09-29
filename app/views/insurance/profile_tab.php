<?php
/**
 * View: insurance/profile_tab.php – Tab Bảo hiểm Xã hội nhúng vào Employee Profile 360°
 */

$ins = $employee->insurance ?? null;
$claims = $employee->insurance_claims ?? [];
$adjustments = $employee->insurance_adjustments ?? [];

$statusBadges = [
    'Active'    => ['Đang tham gia', 'badge bg-success text-white', 'fas fa-check-circle'],
    'Suspended' => ['Tạm dừng đóng', 'badge bg-warning text-dark', 'fas fa-pause-circle'],
    'Stopped'   => ['Đã dừng đóng',  'badge bg-danger text-white', 'fas fa-times-circle'],
];
?>

<div class="insurance-profile-tab">
    <!-- THÔNG TIN TỔNG QUAN HỒ SƠ BHXH -->
    <div class="card mb-4" style="border: 1px solid var(--border); box-shadow: 0 2px 10px rgba(0,0,0,0.02);">
        <div class="card-header d-flex justify-content-between align-items-center" style="background: var(--bg-hover); padding: 14px 20px; border-bottom: 1px solid var(--border);">
            <h5 style="margin: 0; font-size: 15px; font-weight: 700; color: var(--text);">
                <i class="fas fa-shield-alt text-primary"></i> Thông Tin Bảo Hiểm Xã Hội & Y Tế Hiện Tại
            </h5>
            <a href="<?= BASE_URL ?>/insurance/register/<?= $employee->id ?>" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-edit"></i> Chỉnh sửa hồ sơ BH
            </a>
        </div>
        <div class="card-body p-3">
            <?php if (!$ins): ?>
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-id-card-alt fa-2x mb-2 text-secondary" style="opacity: 0.5;"></i>
                    <div>Nhân sự này chưa được thiết lập thông tin bảo hiểm xã hội.</div>
                    <a href="<?= BASE_URL ?>/insurance/register/<?= $employee->id ?>" class="btn btn-sm btn-primary mt-2">
                        <i class="fas fa-plus"></i> Đăng ký thông tin BHXH
                    </a>
                </div>
            <?php else: ?>
                <div class="row g-3">
                    <div class="col-md-3 col-sm-6">
                        <div class="text-muted small">Số sổ BHXH (Mã định danh)</div>
                        <div class="fw-bold" style="font-size: 15px; color: #4f46e5;">
                            <?= !empty($ins->social_insurance_no) ? htmlspecialchars($ins->social_insurance_no) : 'Chưa cấp sổ' ?>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="text-muted small">Mã thẻ BHYT</div>
                        <div class="fw-bold" style="font-size: 15px;">
                            <?= !empty($ins->health_insurance_no) ? htmlspecialchars($ins->health_insurance_no) : 'Chưa cấp thẻ' ?>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="text-muted small">Mức tiền lương đóng BH</div>
                        <div class="fw-bold text-success" style="font-size: 16px;">
                            <?= number_format((float)$ins->insurance_salary, 0, ',', '.') ?>đ
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="text-muted small">Trạng thái tham gia</div>
                        <div>
                            <?php $b = $statusBadges[$ins->status] ?? [$ins->status, 'badge bg-secondary', '']; ?>
                            <span class="<?= $b[1] ?>" style="font-size: 12px; padding: 4px 10px; border-radius: 12px;">
                                <i class="<?= $b[2] ?>"></i> <?= $b[0] ?>
                            </span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">Nơi đăng ký KCB ban đầu</div>
                        <div class="fw-bold text-dark">
                            <i class="fas fa-hospital-alt text-primary"></i> <?= htmlspecialchars($ins->hospital_name ?? '---') ?> 
                            <?php if (!empty($ins->hospital_code)): ?>(Mã: <?= htmlspecialchars($ins->hospital_code) ?>)<?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="text-muted small">Ngày bắt đầu đóng</div>
                        <div class="fw-bold"><?= !empty($ins->start_date) ? date('d/m/Y', strtotime($ins->start_date)) : '---' ?></div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="text-muted small">Ngày dừng đóng</div>
                        <div class="fw-bold text-danger"><?= !empty($ins->end_date) ? date('d/m/Y', strtotime($ins->end_date)) : 'Đang tham gia' ?></div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- LỊCH SỬ HƯỞNG CHẾ ĐỘ BẢO HIỂM (Ốm đau, Thai sản) -->
    <div class="card mb-4" style="border: 1px solid var(--border); box-shadow: 0 2px 10px rgba(0,0,0,0.02);">
        <div class="card-header d-flex justify-content-between align-items-center" style="background: var(--bg-hover); padding: 14px 20px; border-bottom: 1px solid var(--border);">
            <h5 style="margin: 0; font-size: 15px; font-weight: 700; color: var(--text);">
                <i class="fas fa-medkit text-danger"></i> Lịch Sử Hưởng Chế Độ BHXH (<?= count($claims) ?> lượt)
            </h5>
            <a href="<?= BASE_URL ?>/insurance/claims" class="btn btn-sm btn-ghost" style="border: 1px solid var(--border);">
                <i class="fas fa-external-link-alt"></i> Quản lý Chế độ
            </a>
        </div>
        <div class="card-body p-0">
            <?php if (empty($claims)): ?>
                <div class="text-center py-4 text-muted small">Chưa có lượt giải quyết chế độ ốm đau, thai sản nào.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 12.5px;">
                        <thead style="background: var(--bg-hover); color: var(--text-muted); font-size: 11px; text-transform: uppercase;">
                            <tr>
                                <th>Chế độ</th>
                                <th>Thời gian nghỉ</th>
                                <th style="text-align: center;">Số ngày</th>
                                <th style="text-align: right;">Tiền trợ cấp</th>
                                <th>Chứng từ</th>
                                <th style="text-align: center;">Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($claims as $cl): 
                                $cObj = (object)$cl;
                            ?>
                                <tr>
                                    <td><strong style="color: var(--text);"><?= htmlspecialchars($cObj->claim_type ?? '') ?></strong></td>
                                    <td><?= !empty($cObj->from_date) ? date('d/m/Y', strtotime($cObj->from_date)) : '' ?> – <?= !empty($cObj->to_date) ? date('d/m/Y', strtotime($cObj->to_date)) : '' ?></td>
                                    <td style="text-align: center; font-weight: 600;"><?= $cObj->leave_days ?? 0 ?></td>
                                    <td style="text-align: right; font-weight: 700; color: #10b981;">
                                        <?= number_format((float)($cObj->claim_amount ?? 0), 0, ',', '.') ?>đ
                                    </td>
                                    <td><?= htmlspecialchars($cObj->document_ref ?? '---') ?></td>
                                    <td style="text-align: center;">
                                        <span class="badge bg-light text-dark" style="border: 1px solid var(--border);">
                                            <?= htmlspecialchars($cObj->status ?? '') ?>
                                        </span>
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
