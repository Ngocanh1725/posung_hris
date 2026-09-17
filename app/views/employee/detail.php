<?php
/**
 * ============================================================
 *  View: employee/detail.php
 *  Hồ sơ nhân sự 360 độ – Chuẩn Doanh nghiệp Cơ điện FDI
 *  3 Tab chính + Toolbar 7 Quá trình Công tác (AJAX Modal)
 * ============================================================
 */

// Helper hiển thị ngày
function fmtDate(?string $d, string $fmt = 'd/m/Y'): string {
    return $d ? date($fmt, strtotime($d)) : '---';
}
// Helper tính số ngày còn lại
function daysLeft(?string $d): ?int {
    return $d ? (int)((strtotime($d) - time()) / 86400) : null;
}
?>

<!-- Profile Header (Giữ nguyên thiết kế premium) -->
<div class="profile-header panel mb-4 overflow-hidden position-relative" style="border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.05); border-radius: var(--radius-lg);">
    <div class="profile-cover" style="height: 160px; background: linear-gradient(135deg, rgba(37,99,235,0.85), rgba(14,165,233,0.85)), url('data:image/svg+xml,%3Csvg width=%2760%27 height=%2760%27 viewBox=%270 0 60 60%27 xmlns=%27http://www.w3.org/2000/svg%27%3E%3Cg fill=%27none%27 fill-rule=%27evenodd%27%3E%3Cg fill=%27%23ffffff%27 fill-opacity=%270.15%27%3E%3Cpath d=%27M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z%27/%3E%3C/g%3E%3C/g%3E%3C/svg%3E'); background-size: cover; border-radius: var(--radius-lg) var(--radius-lg) 0 0;"></div>
    
    <div class="panel-body d-flex flex-column flex-md-row gap-4 position-relative" style="padding: 0 30px 30px;">
        <div class="profile-avatar text-center" style="margin-top: -60px; position: relative; z-index: 2; width: max-content;">
            <div class="avatar-wrapper" style="border: 4px solid #fff; border-radius: 20px; box-shadow: 0 8px 24px rgba(0,0,0,0.12); display: inline-block; background: #fff; transition: transform 0.3s ease;">
                <?php if (!empty($employee->avatar_path)): ?>
                    <img src="<?= BASE_URL ?>/<?= htmlspecialchars($employee->avatar_path) ?>" alt="Avatar" style="width: 140px; height: 140px; border-radius: 16px; object-fit: cover;">
                <?php else: ?>
                    <div style="width: 140px; height: 140px; border-radius: 16px; background: linear-gradient(135deg, var(--primary), var(--accent)); color: #fff; font-size: 56px; display: flex; align-items: center; justify-content: center; font-weight: 800;"><?= mb_substr($employee->full_name, 0, 1) ?></div>
                <?php endif; ?>
            </div>
            <div class="mt-3">
                <span class="badge badge-<?= strtolower($employee->status) ?>" style="font-size: 13px; padding: 6px 14px;">
                    <i class="fas fa-circle" style="font-size: 8px; margin-right: 6px;"></i><?= htmlspecialchars($employee->status) ?>
                </span>
            </div>
        </div>

        <div class="profile-info flex-1" style="padding-top: 24px;">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h2 style="font-size: 28px; font-weight: 800; letter-spacing: -0.5px; color: var(--text-heading); margin: 0 0 4px;">
                        <?= htmlspecialchars($employee->full_name) ?>
                    </h2>
                    <p class="text-muted mb-0" style="font-size: 14px; font-weight: 500;">
                        Mã NV: <span class="badge bg-secondary text-dark px-2 py-1"><?= htmlspecialchars($employee->emp_code) ?></span>
                    </p>
                </div>
                <div class="d-flex gap-2 mt-2 mt-md-0">
                    <a href="<?= BASE_URL ?>/employee/edit/<?= $employee->id ?>" class="btn btn-warning btn-sm" style="border-radius: 8px; padding: 8px 16px; font-weight: 600;">
                        <i class="fas fa-edit"></i> Chỉnh sửa
                    </a>
                    <a href="<?= BASE_URL ?>/employee/printProfile/<?= $employee->id ?>" target="_blank" class="btn btn-primary btn-sm" style="border-radius: 8px; padding: 8px 16px; font-weight: 600; box-shadow: 0 6px 15px rgba(37,99,235,0.25);">
                        <i class="fas fa-print"></i> In Hồ sơ DN
                    </a>
                    <a href="<?= BASE_URL ?>/employee/print2c/<?= $employee->id ?>" target="_blank" class="btn btn-ghost btn-sm" style="border-radius: 8px; padding: 8px 16px; font-weight: 600;">
                        <i class="fas fa-file-alt"></i> Mẫu 2C
                    </a>
                </div>
            </div>
            
            <div class="profile-meta mt-4" style="background: rgba(248,250,252,0.9); padding: 16px 20px; border-radius: 12px; border: 1px solid var(--border); display: flex; flex-wrap: wrap; gap: 24px;">
                <div class="meta-item"><i class="fas fa-briefcase text-primary" style="font-size: 16px;"></i> <span class="fw-bold text-dark" style="font-size: 14px;"><?= htmlspecialchars($employee->pos_title ?? 'Chưa cập nhật') ?></span></div>
                <div class="meta-item"><i class="fas fa-sitemap text-info" style="font-size: 16px;"></i> <span style="font-size: 14px;"><?= htmlspecialchars($employee->dept_name ?? 'Chưa phân bổ') ?></span></div>
                <div class="meta-item"><i class="fas fa-hard-hat text-warning" style="font-size: 16px;"></i> <span style="font-size: 14px;">Dự án: <strong class="text-dark"><?= htmlspecialchars($employee->project_name ?? 'N/A') ?></strong></span></div>
            </div>

            <div class="profile-contact mt-3 d-flex flex-wrap gap-4">
                <div class="contact-item p-2 rounded hover-bg" style="display: flex; align-items: center; gap: 8px;"><i class="fas fa-phone-alt text-muted"></i> <a href="tel:<?= htmlspecialchars($employee->phone) ?>" class="text-secondary" style="font-weight: 500; text-decoration: none; font-size: 14px;"><?= htmlspecialchars($employee->phone ?: '---') ?></a></div>
                <div class="contact-item p-2 rounded hover-bg" style="display: flex; align-items: center; gap: 8px;"><i class="fas fa-envelope text-muted"></i> <a href="mailto:<?= htmlspecialchars($employee->email) ?>" class="text-secondary" style="font-weight: 500; text-decoration: none; font-size: 14px;"><?= htmlspecialchars($employee->email ?: '---') ?></a></div>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════ -->
<!--  3 TAB CHÍNH                                               -->
<!-- ══════════════════════════════════════════════════════════ -->
<div class="panel" style="box-shadow: 0 10px 30px rgba(0,0,0,0.03); border: none;">
    <div class="panel-body p-0">
        <div class="tabs-wrapper px-4 pt-3" style="border-bottom: 1px solid var(--border); background: var(--bg-card); overflow-x: auto;">
            <ul class="nav-tabs-modern" id="profileTabs">
                <li class="tab-link active" data-target="tab-basic">
                    <i class="fas fa-id-card"></i> <span>Thông tin cơ bản & Định danh</span>
                </li>
                <li class="tab-link" data-target="tab-salary-skill">
                    <i class="fas fa-cogs"></i> <span>Lương, PC & Năng lực Kỹ thuật</span>
                </li>
                <li class="tab-link" data-target="tab-health-expat">
                    <i class="fas fa-heartbeat"></i> <span>Sức khỏe & Hồ sơ Expat</span>
                </li>
            </ul>
        </div>
        
        <div class="p-4">
            <!-- ══════════════ TAB 1: THÔNG TIN CƠ BẢN & ĐỊNH DANH ══════════════ -->
            <div class="tab-content active" id="tab-basic">
                <div class="row">
                    <div class="col-md-6">
                        <h4 class="section-title">Lý lịch trích ngang</h4>
                        <table class="table-info">
                            <tr><td>Ngày sinh:</td><td><?= fmtDate($employee->dob) ?></td></tr>
                            <tr><td>Giới tính:</td><td><?= $employee->gender === 'Male' ? 'Nam' : ($employee->gender === 'Female' ? 'Nữ' : 'Khác') ?></td></tr>
                            <tr><td>Hôn nhân:</td><td>
                                <?php
                                $marital = ['Single'=>'Độc thân','Married'=>'Đã kết hôn','Divorced'=>'Ly hôn','Widowed'=>'Góa'];
                                echo $marital[$employee->marital_status ?? 'Single'] ?? 'Độc thân';
                                ?>
                            </td></tr>
                            <tr><td>Dân tộc:</td><td><?= htmlspecialchars($employee->ethnic ?? '---') ?></td></tr>
                            <tr><td>Tôn giáo:</td><td><?= htmlspecialchars($employee->religion ?? '---') ?></td></tr>
                            <tr><td>Quốc tịch:</td><td><?= htmlspecialchars($employee->nationality) ?></td></tr>
                            <tr><td>Số CCCD/HC:</td><td><?= htmlspecialchars($employee->id_card) ?> (Cấp: <?= fmtDate($employee->id_card_date) ?> tại <?= htmlspecialchars($employee->id_card_place) ?>)</td></tr>
                            <tr><td>Quê quán:</td><td><?= htmlspecialchars($employee->hometown ?: '---') ?></td></tr>
                            <tr><td>Thường trú:</td><td><?= htmlspecialchars($employee->address ?: '---') ?></td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h4 class="section-title">Tổ chức & Trình độ</h4>
                        <table class="table-info">
                            <tr><td>Loại nhân sự:</td><td><span class="badge bg-secondary"><?= htmlspecialchars($employee->employee_type) ?></span></td></tr>
                            <tr><td>Phòng ban:</td><td><?= htmlspecialchars($employee->dept_name ?? '---') ?></td></tr>
                            <tr><td>Chức vụ:</td><td><?= htmlspecialchars($employee->pos_title ?? '---') ?></td></tr>
                            <tr><td>Dự án hiện tại:</td><td><?= htmlspecialchars($employee->project_name ?? '---') ?></td></tr>
                            <tr><td>Ngày vào làm:</td><td><?= fmtDate($employee->join_date) ?></td></tr>
                            <tr><td>Ký HĐ chính thức:</td><td><?= fmtDate($employee->official_date) ?></td></tr>
                            <tr><td>Trình độ chuyên môn:</td><td><?= htmlspecialchars($employee->highest_degree ?: '---') ?></td></tr>
                        </table>

                        <h4 class="section-title mt-4">Liên hệ Khẩn cấp</h4>
                        <table class="table-info">
                            <tr><td>Người liên hệ:</td><td><?= htmlspecialchars($employee->emergency_contact_name ?? '---') ?></td></tr>
                            <tr><td>Mối quan hệ:</td><td><?= htmlspecialchars($employee->emergency_contact_relation ?? '---') ?></td></tr>
                            <tr><td>Số điện thoại:</td><td><?= htmlspecialchars($employee->emergency_contact_phone ?? '---') ?></td></tr>
                        </table>

                        <h4 class="section-title mt-4">Thuế & Bảo hiểm & Ngân hàng</h4>
                        <table class="table-info">
                            <tr><td>Mã số thuế:</td><td class="fw-bold"><?= htmlspecialchars($employee->tax_code ?? '---') ?></td></tr>
                            <tr><td>Số sổ BHXH:</td><td class="fw-bold text-primary"><?= htmlspecialchars($employee->social_insurance_no ?? '---') ?></td></tr>
                            <tr><td>Số thẻ BHYT:</td><td><?= htmlspecialchars($employee->health_insurance_no ?? '---') ?></td></tr>
                            <tr><td>Tài khoản NH:</td><td class="fw-bold"><?= htmlspecialchars($employee->bank_account ?? '---') ?></td></tr>
                            <tr><td>Ngân hàng:</td><td><?= htmlspecialchars($employee->bank_name ?? '---') ?> – <?= htmlspecialchars($employee->bank_branch ?? '') ?></td></tr>
                        </table>

                        <?php if (!empty($employee->cv_file_path)): ?>
                        <div class="mt-4">
                            <a href="<?= BASE_URL ?>/<?= htmlspecialchars($employee->cv_file_path) ?>" target="_blank" class="btn btn-ghost btn-sm">
                                <i class="fas fa-file-pdf"></i> Xem bản Scan Sơ yếu lý lịch
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- ══════════════ TAB 2: LƯƠNG, PHỤ CẤP & NĂNG LỰC KỸ THUẬT ══════════════ -->
            <div class="tab-content" id="tab-salary-skill">
                <?php if (!Session::isManager() && !Session::isAdmin()): ?>
                    <div class="alert alert-danger"><i class="fas fa-lock"></i> Bạn không có quyền xem thông tin chế độ lương.</div>
                <?php else: ?>
                <div class="row">
                    <!-- Lương & Phụ cấp -->
                    <div class="col-md-6">
                        <h4 class="section-title">Lương & Phụ cấp hiện hưởng</h4>
                        <table class="table-info">
                            <tr><td>Ngày hưởng lương:</td><td><?= fmtDate($employee->ngay_huong_luong ?? null) ?></td></tr>
                            <tr><td>Hệ số lương:</td><td class="fw-bold text-primary"><?= htmlspecialchars($employee->pctn_vuot_khung ?? '---') ?></td></tr>
                            <tr><td>PC Công trường/Xa nhà:</td><td><?= number_format($employee->phu_cap_khu_vuc ?? 0, 2) ?></td></tr>
                            <tr><td>PC Độc hại Cleanroom:</td><td><?= number_format($employee->phu_cap_khac ?? 0, 2) ?></td></tr>
                            <tr><td>PC Trách nhiệm:</td><td><?= number_format($employee->phu_cap_trach_nhiem ?? 0, 2) ?></td></tr>
                            <tr><td>PC Kiêm nhiệm:</td><td><?= number_format($employee->phu_cap_kiem_nhiem ?? 0, 2) ?></td></tr>
                        </table>

                        <?php if (!empty($employee->allowances)): ?>
                        <h4 class="section-title mt-4">Phụ cấp đặc biệt</h4>
                        <div class="d-flex flex-wrap gap-2">
                            <?php foreach ($employee->allowances as $allw): ?>
                                <div class="badge badge-primary py-2 px-3" style="font-size:13px; background: rgba(59, 130, 246, 0.1); color: var(--primary); border: 1px solid var(--primary);">
                                    <?= htmlspecialchars($allw->allowance_name) ?>: <strong><?= number_format($allw->amount) ?> VNĐ</strong>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Kỹ năng kỹ thuật -->
                    <div class="col-md-6">
                        <h4 class="section-title">Kỹ năng Kỹ thuật M&E</h4>
                        <table class="table-info">
                            <tr><td>AutoCAD:</td><td><?= htmlspecialchars($employee->skill_autocad ?? '---') ?></td></tr>
                            <tr><td>Revit BIM:</td><td><?= htmlspecialchars($employee->skill_revit_bim ?? '---') ?></td></tr>
                            <tr><td>Navisworks:</td><td><?= htmlspecialchars($employee->skill_navisworks ?? '---') ?></td></tr>
                            <tr><td>Dự toán:</td><td><?= htmlspecialchars($employee->skill_estimation ?? '---') ?></td></tr>
                        </table>

                        <h4 class="section-title mt-4">Chứng chỉ Thợ hàn</h4>
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <span class="badge <?= ($employee->welding_cert_3g ?? 0) ? 'bg-success' : 'bg-light text-muted' ?> py-2 px-3" style="font-size:13px;">3G <?= ($employee->welding_cert_3g ?? 0) ? '✓' : '✗' ?></span>
                            <span class="badge <?= ($employee->welding_cert_6g ?? 0) ? 'bg-success' : 'bg-light text-muted' ?> py-2 px-3" style="font-size:13px;">6G <?= ($employee->welding_cert_6g ?? 0) ? '✓' : '✗' ?></span>
                            <span class="badge <?= ($employee->welding_cert_tig ?? 0) ? 'bg-success' : 'bg-light text-muted' ?> py-2 px-3" style="font-size:13px;">TIG <?= ($employee->welding_cert_tig ?? 0) ? '✓' : '✗' ?></span>
                            <span class="badge <?= ($employee->welding_cert_mig ?? 0) ? 'bg-success' : 'bg-light text-muted' ?> py-2 px-3" style="font-size:13px;">MIG <?= ($employee->welding_cert_mig ?? 0) ? '✓' : '✗' ?></span>
                        </div>

                        <h4 class="section-title mt-4">Ngoại ngữ & Tin học</h4>
                        <table class="table-info">
                            <tr><td>Tiếng Hàn:</td><td><?= htmlspecialchars($employee->korean_level ?? '---') ?></td></tr>
                            <tr><td>Tiếng Anh:</td><td><?= htmlspecialchars($employee->english_level ?? '---') ?></td></tr>
                            <tr><td>Tin học:</td><td><?= htmlspecialchars($employee->it_level ?? '---') ?></td></tr>
                        </table>

                        <h4 class="section-title mt-4">An toàn Lao động HSE</h4>
                        <table class="table-info">
                            <tr><td>Số thẻ ATLĐ:</td><td class="fw-bold"><?= htmlspecialchars($employee->hse_card_number ?? '---') ?></td></tr>
                            <tr><td>Ngày cấp:</td><td><?= fmtDate($employee->hse_card_issue_date ?? null) ?></td></tr>
                            <tr>
                                <td>Hạn thẻ Samsung:</td>
                                <td>
                                    <?php $dl = daysLeft($employee->hse_card_expiry_samsung ?? null); ?>
                                    <?= fmtDate($employee->hse_card_expiry_samsung ?? null) ?>
                                    <?php if ($dl !== null && $dl <= 30): ?>
                                        <span class="badge-expiry-<?= $dl <= 0 ? 'danger' : 'warning' ?>">
                                            <?= $dl <= 0 ? 'HẾT HẠN' : "Còn {$dl} ngày" ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td>Hạn thẻ Amkor:</td>
                                <td>
                                    <?php $dl2 = daysLeft($employee->hse_card_expiry_amkor ?? null); ?>
                                    <?= fmtDate($employee->hse_card_expiry_amkor ?? null) ?>
                                    <?php if ($dl2 !== null && $dl2 <= 30): ?>
                                        <span class="badge-expiry-<?= $dl2 <= 0 ? 'danger' : 'warning' ?>">
                                            <?= $dl2 <= 0 ? 'HẾT HẠN' : "Còn {$dl2} ngày" ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- ══════════════ TAB 3: SỨC KHỎE & HỒ SƠ EXPAT ══════════════ -->
            <div class="tab-content" id="tab-health-expat">
                <div class="row">
                    <div class="col-md-6">
                        <h4 class="section-title">Sức khỏe & PPE</h4>
                        <table class="table-info">
                            <tr><td>Chiều cao:</td><td><?= htmlspecialchars($employee->chieu_cao ?? '---') ?> cm</td></tr>
                            <tr><td>Cân nặng:</td><td><?= htmlspecialchars($employee->can_nang ?? '---') ?> kg</td></tr>
                            <tr><td>Nhóm máu:</td><td><span class="badge bg-danger text-white"><?= htmlspecialchars($employee->nhom_mau ?? '---') ?></span></td></tr>
                            <tr><td>Tình trạng sức khỏe:</td><td><?= htmlspecialchars($employee->tinh_trang_suc_khoe ?? '---') ?></td></tr>
                            <tr>
                                <td>Đủ ĐK làm việc trên cao:</td>
                                <td>
                                    <?php if ($employee->can_work_at_height ?? 0): ?>
                                        <span class="badge bg-success"><i class="fas fa-check"></i> Đạt</span>
                                    <?php else: ?>
                                        <span class="badge bg-light text-muted"><i class="fas fa-times"></i> Chưa</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td>Đủ ĐK làm việc hầm kín:</td>
                                <td>
                                    <?php if ($employee->can_work_confined_space ?? 0): ?>
                                        <span class="badge bg-success"><i class="fas fa-check"></i> Đạt</span>
                                    <?php else: ?>
                                        <span class="badge bg-light text-muted"><i class="fas fa-times"></i> Chưa</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr><td>Size giày bảo hộ:</td><td><?= htmlspecialchars($employee->safety_shoe_size ?? '---') ?></td></tr>
                            <tr><td>Size áo bảo hộ:</td><td><?= htmlspecialchars($employee->safety_uniform_size ?? '---') ?></td></tr>
                        </table>
                    </div>

                    <div class="col-md-6">
                        <?php if ($employee->employee_type === 'Expat' && isset($employee->expat)): ?>
                        <h4 class="section-title">Hồ sơ Chuyên gia Hàn Quốc (Expat)</h4>
                        <table class="table-info">
                            <tr><td>Số Hộ chiếu:</td><td class="fw-bold"><?= htmlspecialchars($employee->expat->passport_number ?: '---') ?></td></tr>
                            <tr>
                                <td>Hạn Hộ chiếu:</td>
                                <td>
                                    <?php $dlp = daysLeft($employee->expat->passport_expiry ?? null); ?>
                                    <?= fmtDate($employee->expat->passport_expiry ?? null) ?>
                                    <?php if ($dlp !== null && $dlp <= 90): ?>
                                        <span class="badge-expiry-<?= $dlp <= 30 ? 'danger' : 'warning' ?>">
                                            <?= $dlp <= 0 ? 'HẾT HẠN' : "Còn {$dlp} ngày" ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr><td>Số Work Permit:</td><td class="fw-bold"><?= htmlspecialchars($employee->expat->work_permit_number ?: '---') ?></td></tr>
                            <tr>
                                <td>Hạn Work Permit:</td>
                                <td>
                                    <?php $dlw = daysLeft($employee->expat->work_permit_expiry ?? null); ?>
                                    <?= fmtDate($employee->expat->work_permit_expiry ?? null) ?>
                                    <?php if ($dlw !== null && $dlw <= 90): ?>
                                        <span class="badge-expiry-<?= $dlw <= 30 ? 'danger' : 'warning' ?>">
                                            <?= $dlw <= 0 ? 'HẾT HẠN' : "Còn {$dlw} ngày" ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr><td>Số thẻ TRC:</td><td class="fw-bold"><?= htmlspecialchars($employee->expat->trc_number ?: '---') ?></td></tr>
                            <tr>
                                <td>Hạn thẻ TRC:</td>
                                <td>
                                    <?php $dlt = daysLeft($employee->expat->trc_expiry ?? null); ?>
                                    <?= fmtDate($employee->expat->trc_expiry ?? null) ?>
                                    <?php if ($dlt !== null && $dlt <= 90): ?>
                                        <span class="badge-expiry-<?= $dlt <= 30 ? 'danger' : 'warning' ?>">
                                            <?= $dlt <= 0 ? 'HẾT HẠN' : "Còn {$dlt} ngày" ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </table>
                        <?php else: ?>
                        <h4 class="section-title">Chứng chỉ & Đào tạo An toàn</h4>
                        <?php if (empty($employee->certificates)): ?>
                            <div class="alert alert-info py-2">Chưa có chứng chỉ nào được ghi nhận.</div>
                        <?php else: ?>
                            <div class="table-wrapper">
                                <table>
                                    <thead><tr><th>Loại</th><th>Tên chứng chỉ</th><th>Ngày hết hạn</th></tr></thead>
                                    <tbody>
                                        <?php foreach ($employee->certificates as $cert): ?>
                                        <tr>
                                            <td><span class="badge bg-secondary"><?= htmlspecialchars($cert->cert_type) ?></span></td>
                                            <td class="fw-bold"><?= htmlspecialchars($cert->cert_name) ?></td>
                                            <td>
                                                <?php if ($cert->expiry_date):
                                                    $cdl = daysLeft($cert->expiry_date);
                                                ?>
                                                    <span class="<?= $cdl !== null && $cdl < 30 ? 'text-danger fw-bold' : '' ?>"><?= fmtDate($cert->expiry_date) ?></span>
                                                    <?php if ($cdl !== null && $cdl <= 30): ?>
                                                        <span class="badge-expiry-<?= $cdl <= 0 ? 'danger' : 'warning' ?>"><?= $cdl <= 0 ? 'HẾT HẠN' : "Còn {$cdl} ngày" ?></span>
                                                    <?php endif; ?>
                                                <?php else: echo '---'; endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div><!-- /p-4 -->
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════ -->
<!--  TOOLBAR 7 QUÁ TRÌNH CÔNG TÁC                            -->
<!-- ══════════════════════════════════════════════════════════ -->
<div class="process-toolbar panel mt-4" style="box-shadow: 0 10px 30px rgba(0,0,0,0.03); border: none;">
    <div class="panel-body">
        <h4 class="section-title" style="margin-bottom: 16px;"><i class="fas fa-layer-group text-primary"></i> 7 Quá trình Công tác</h4>
        <div class="process-buttons">
            <button class="process-btn" data-process="work_histories" onclick="openProcessModal('work_histories')">
                <i class="fas fa-briefcase"></i><span>1. Quá trình công tác</span>
            </button>
            <button class="process-btn" data-process="trainings" onclick="openProcessModal('trainings')">
                <i class="fas fa-graduation-cap"></i><span>2. Quá trình đào tạo</span>
            </button>
            <button class="process-btn" data-process="salary_progressions" onclick="openProcessModal('salary_progressions')">
                <i class="fas fa-chart-line"></i><span>3. Diễn biến lương</span>
            </button>
            <button class="process-btn" data-process="family_members" onclick="openProcessModal('family_members')">
                <i class="fas fa-users"></i><span>4. Quan hệ gia đình</span>
            </button>
            <button class="process-btn" data-process="reward_disciplines" onclick="openProcessModal('reward_disciplines')">
                <i class="fas fa-award"></i><span>5. Khen thưởng – Kỷ luật</span>
            </button>
            <button class="process-btn" data-process="evaluations" onclick="openProcessModal('evaluations')">
                <i class="fas fa-chart-bar"></i><span>6. Đánh giá KPI</span>
            </button>
            <button class="process-btn" data-process="appointments" onclick="openProcessModal('appointments')">
                <i class="fas fa-user-tie"></i><span>7. Quá trình bổ nhiệm</span>
            </button>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════ -->
<!--  MODAL 7 QUÁ TRÌNH (Chung – nội dung thay đổi qua JS)    -->
<!-- ══════════════════════════════════════════════════════════ -->
<div class="modal-overlay" id="processModalOverlay" style="display:none;">
    <div class="modal-container process-modal">
        <div class="modal-header-custom">
            <h5 class="modal-title-custom" id="processModalTitle">Quá trình</h5>
            <button type="button" class="modal-close-btn" onclick="closeProcessModal()">&times;</button>
        </div>
        <div class="modal-body-custom">
            <!-- Bảng danh sách -->
            <div class="table-wrapper mb-3" style="max-height: 280px; overflow-y: auto;">
                <table id="processTable">
                    <thead id="processTableHead"></thead>
                    <tbody id="processTableBody"></tbody>
                </table>
            </div>
            <hr style="border-color: var(--border);">
            <!-- Form nhập liệu -->
            <form id="processForm" onsubmit="saveProcess(event)">
                <input type="hidden" name="id" id="pf_id" value="0">
                <input type="hidden" name="employee_id" value="<?= $employee->id ?>">
                <input type="hidden" name="process_type" id="pf_process_type" value="">
                <div id="processFormFields"></div>
            </form>
        </div>
        <div class="modal-footer-custom">
            <button type="button" class="btn btn-sm" style="background: var(--success); color:#fff; border-radius: 8px; padding: 8px 20px; font-weight: 600;" onclick="saveProcess(event)">
                <i class="fas fa-save"></i> Ghi / Lưu
            </button>
            <button type="button" class="btn btn-ghost btn-sm" style="border-radius: 8px; padding: 8px 20px; font-weight: 600;" onclick="resetProcessForm()">
                <i class="fas fa-plus"></i> Thêm mới
            </button>
            <button type="button" class="btn btn-sm" id="btnDeleteProcess" style="background: var(--danger); color:#fff; border-radius: 8px; padding: 8px 20px; font-weight: 600; display:none;" onclick="deleteProcess()">
                <i class="fas fa-trash"></i> Xóa
            </button>
            <button type="button" class="btn btn-ghost btn-sm" style="border-radius: 8px; padding: 8px 20px; font-weight: 600;" onclick="closeProcessModal()">
                <i class="fas fa-times"></i> Đóng
            </button>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════ -->
<!--  STYLES                                                    -->
<!-- ══════════════════════════════════════════════════════════ -->
<style>
/* Layout Utilities */
.gap-4 { gap: 1.5rem; } .gap-2 { gap: 0.5rem; } .flex-1 { flex: 1; }
.flex-column { flex-direction: column; }
.justify-content-between { justify-content: space-between; }
.align-items-center { align-items: center; } .align-items-start { align-items: flex-start; }
.mx-1 { margin-left:0.25rem; margin-right:0.25rem; } .m-0 { margin:0; }
.mb-1 { margin-bottom:0.25rem; } .mb-3 { margin-bottom:1rem; } .mb-4 { margin-bottom:1.5rem; }
.mt-2 { margin-top:0.5rem; } .mt-3 { margin-top:1rem; } .mt-4 { margin-top:1.5rem; }
.py-2 { padding-top:0.5rem; padding-bottom:0.5rem; } .px-3 { padding-left:1rem; padding-right:1rem; }
.p-0 { padding:0!important; } .p-4 { padding:1.5rem; } .pt-3 { padding-top:1rem; } .px-4 { padding-left:1.5rem; padding-right:1.5rem; }
.text-dark { color:#0f172a; } .hover-bg:hover { background:rgba(37,99,235,0.05); }
@media (min-width:768px) { .flex-md-row { flex-direction:row; } .mt-md-0 { margin-top:0; } }

/* Modern Tabs */
.nav-tabs-modern { display:flex; list-style:none; margin:0; padding:0; }
.nav-tabs-modern .tab-link {
    position:relative; padding:14px 20px; font-weight:600; color:var(--text-secondary);
    cursor:pointer; transition:all 0.3s cubic-bezier(0.4,0,0.2,1); font-size:14px;
    display:flex; align-items:center; gap:8px; white-space:nowrap;
}
.nav-tabs-modern .tab-link i { font-size:16px; color:var(--text-muted); transition:all 0.3s; }
.nav-tabs-modern .tab-link:hover, .nav-tabs-modern .tab-link:hover i { color:var(--primary); }
.nav-tabs-modern .tab-link.active, .nav-tabs-modern .tab-link.active i { color:var(--primary); }
.nav-tabs-modern .tab-link::after {
    content:''; position:absolute; bottom:-1px; left:0; right:0; height:3px;
    background:var(--primary); border-radius:3px 3px 0 0; opacity:0; transform:scaleX(0.5);
    transition:all 0.3s cubic-bezier(0.4,0,0.2,1);
}
.nav-tabs-modern .tab-link.active::after { opacity:1; transform:scaleX(1); }

/* Tab Content */
.tab-content { display:none; animation:fadeIn 0.4s cubic-bezier(0.4,0,0.2,1); }
.tab-content.active { display:block; }
@keyframes fadeIn { from { opacity:0; transform:translateY(10px); } to { opacity:1; transform:translateY(0); } }

/* Grid */
.row { display:flex; flex-wrap:wrap; margin:-12px; }
.col-md-6 { width:50%; padding:12px; }
@media (max-width:768px) { .col-md-6 { width:100%; } }

/* Section Title */
.section-title {
    font-size:16px; font-weight:700; color:var(--text-heading); margin-bottom:20px;
    padding-bottom:12px; border-bottom:2px solid #f1f5f9; display:flex; align-items:center; gap:10px;
}
.section-title::before { content:''; display:block; width:4px; height:16px; background:var(--primary); border-radius:4px; }

/* Table Info */
.table-info { width:100%; border-collapse:separate; border-spacing:0; }
.table-info tr { transition:background 0.2s; }
.table-info tr:hover td { background:#f8fafc; }
.table-info td { padding:12px 16px; border-bottom:1px dashed var(--border); font-size:14px; }
.table-info tr:last-child td { border-bottom:none; }
.table-info td:first-child { color:var(--text-secondary); width:35%; font-weight:600; border-radius:8px 0 0 8px; }
.table-info td:last-child { color:var(--text-heading); font-weight:500; border-radius:0 8px 8px 0; }
.bg-secondary { background:rgba(148,163,184,0.15); color:#475569; }
.bg-success { background: #10b981; color: #fff; }
.bg-danger { background: #ef4444; }
.bg-light { background: #f1f5f9; }
.text-danger { color:var(--danger); }
.text-muted { color: var(--text-muted); }
.fw-bold { font-weight:600; }

/* Expiry Badges */
.badge-expiry-warning {
    display:inline-block; font-size:11px; font-weight:700; padding:3px 8px; border-radius:6px;
    background:rgba(245,158,11,0.15); color:#d97706; margin-left:6px;
    animation: pulse 2s ease-in-out infinite;
}
.badge-expiry-danger {
    display:inline-block; font-size:11px; font-weight:700; padding:3px 8px; border-radius:6px;
    background:rgba(239,68,68,0.15); color:#dc2626; margin-left:6px;
    animation: pulse 1.5s ease-in-out infinite;
}
@keyframes pulse { 0%,100%{opacity:1;} 50%{opacity:0.6;} }

/* ═══════ Process Toolbar ═══════ */
.process-buttons { display:flex; flex-wrap:wrap; gap:10px; }
.process-btn {
    display:flex; align-items:center; gap:8px; padding:12px 18px; border:1px solid var(--border);
    border-radius:12px; background:#fff; color:var(--text-heading); font-size:13px; font-weight:600;
    cursor:pointer; transition:all 0.3s cubic-bezier(0.4,0,0.2,1); box-shadow:0 2px 8px rgba(0,0,0,0.04);
}
.process-btn i { font-size:16px; color:var(--primary); transition:all 0.3s; }
.process-btn:hover {
    background:var(--primary); color:#fff; border-color:var(--primary);
    transform:translateY(-2px); box-shadow:0 8px 20px rgba(37,99,235,0.25);
}
.process-btn:hover i { color:#fff; }

/* ═══════ Custom Modal (No Bootstrap dependency) ═══════ */
.modal-overlay {
    position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5);
    z-index:9999; display:flex; align-items:center; justify-content:center;
    animation:fadeIn 0.25s ease;
}
.modal-container {
    background:#fff; border-radius:16px; width:90%; max-width:900px; max-height:90vh;
    display:flex; flex-direction:column; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);
    animation:slideUp 0.3s ease;
}
@keyframes slideUp { from { opacity:0; transform:translateY(30px); } to { opacity:1; transform:translateY(0); } }
.modal-header-custom {
    display:flex; justify-content:space-between; align-items:center; padding:20px 24px;
    border-bottom:1px solid var(--border); background:linear-gradient(135deg,rgba(37,99,235,0.05),rgba(14,165,233,0.05));
    border-radius:16px 16px 0 0;
}
.modal-title-custom { font-size:18px; font-weight:700; color:var(--text-heading); margin:0; }
.modal-close-btn {
    width:36px; height:36px; border:none; background:rgba(0,0,0,0.05); border-radius:10px;
    font-size:20px; cursor:pointer; display:flex; align-items:center; justify-content:center;
    color:var(--text-secondary); transition:all 0.2s;
}
.modal-close-btn:hover { background:var(--danger); color:#fff; }
.modal-body-custom { padding:24px; overflow-y:auto; flex:1; }
.modal-footer-custom {
    display:flex; gap:10px; padding:16px 24px; border-top:1px solid var(--border);
    border-radius:0 0 16px 16px; background:#fafbfc;
}

/* Process table in modal */
#processTable { width:100%; border-collapse:collapse; font-size:13px; }
#processTable th { background:#f8fafc; font-weight:700; padding:10px 12px; text-align:left; border-bottom:2px solid var(--border); color:var(--text-secondary); font-size:12px; text-transform:uppercase; letter-spacing:0.5px; }
#processTable td { padding:10px 12px; border-bottom:1px solid #f1f5f9; }
#processTable tbody tr { cursor:pointer; transition:all 0.2s; }
#processTable tbody tr:hover { background:rgba(37,99,235,0.05); }
#processTable tbody tr.selected { background:rgba(37,99,235,0.1); border-left:3px solid var(--primary); }

/* Process form fields */
#processFormFields { display:flex; flex-wrap:wrap; margin:0 -8px; }
#processFormFields .form-group { margin-bottom:14px; padding:0 8px; box-sizing: border-box; }
#processFormFields .form-group label {
    display:block; font-size:12px; font-weight:700; color:var(--text-secondary);
    margin-bottom:5px; text-transform:uppercase; letter-spacing:0.3px;
}
#processFormFields .form-group input,
#processFormFields .form-group select,
#processFormFields .form-group textarea {
    width:100%; padding:9px 12px; border:1px solid var(--border); border-radius:8px;
    font-size:14px; transition:all 0.2s; outline:none; font-family:inherit;
    box-sizing:border-box;
}
#processFormFields .form-group input:focus,
#processFormFields .form-group select:focus,
#processFormFields .form-group textarea:focus {
    border-color:var(--primary); box-shadow:0 0 0 3px rgba(37,99,235,0.1);
}

/* Avatar hover */
.avatar-wrapper:hover { transform:translateY(-5px); }
</style>

<!-- ══════════════════════════════════════════════════════════ -->
<!--  JAVASCRIPT                                                -->
<!-- ══════════════════════════════════════════════════════════ -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Tab switching
    const tabs = document.querySelectorAll('.tab-link');
    const contents = document.querySelectorAll('.tab-content');
    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            tabs.forEach(t => t.classList.remove('active'));
            contents.forEach(c => c.classList.remove('active'));
            tab.classList.add('active');
            document.getElementById(tab.getAttribute('data-target')).classList.add('active');
        });
    });
});

// ═══════════════════════════════════════════════════════════
//  7 PROCESSES – AJAX MODULE
// ═══════════════════════════════════════════════════════════
const BASE = '<?= BASE_URL ?>';
const EMP_ID = <?= $employee->id ?>;
let currentProcess = '';
let processData = {};

// Cấu hình cho từng quá trình
const PROCESS_CONFIG = {
    work_histories: {
        title: '1. Quá trình Công tác',
        saveUrl: `${BASE}/employee/saveWorkHistory`,
        deleteUrl: `${BASE}/employee/deleteWorkHistory`,
        columns: ['Từ ngày', 'Đến ngày', 'Tổ chức / Công ty', 'Chức vụ', 'Dự án'],
        fields: ['from_date', 'to_date', 'organization', 'position', 'project_name', 'description'],
        colWidths: ['col-3','col-3','col-6','col-6','col-6','col-12'],
        labels: ['Từ ngày', 'Đến ngày', 'Tổ chức / Công ty', 'Chức vụ', 'Tên dự án', 'Mô tả công việc'],
        types: ['date','date','text','text','text','textarea'],
        tableFields: ['from_date','to_date','organization','position','project_name']
    },
    trainings: {
        title: '2. Quá trình Đào tạo',
        saveUrl: `${BASE}/employee/saveTraining`,
        deleteUrl: `${BASE}/employee/deleteTraining`,
        columns: ['Từ ngày', 'Đến ngày', 'Trường / Cơ sở', 'Chuyên ngành', 'Văn bằng'],
        fields: ['from_date','to_date','institution','major','certificate','degree_type','notes'],
        colWidths: ['col-3','col-3','col-6','col-6','col-6','col-6','col-12'],
        labels: ['Từ ngày','Đến ngày','Trường / Cơ sở đào tạo','Chuyên ngành','Chứng chỉ / Văn bằng','Loại hình (ĐH, CĐ, TC...)','Ghi chú'],
        types: ['date','date','text','text','text','text','textarea'],
        tableFields: ['from_date','to_date','institution','major','certificate']
    },
    salary_progressions: {
        title: '3. Diễn biến Lương',
        saveUrl: `${BASE}/employee/saveSalaryProgression`,
        deleteUrl: `${BASE}/employee/deleteSalaryProgression`,
        columns: ['Ngày áp dụng', 'Ngạch / Bậc', 'Hệ số', 'Mức lương CB', 'Số QĐ'],
        fields: ['effective_date','salary_grade','salary_coefficient','base_salary','decision_number','notes'],
        colWidths: ['col-4','col-4','col-4','col-4','col-4','col-12'],
        labels: ['Ngày áp dụng','Ngạch / Bậc lương','Hệ số lương','Mức lương cơ bản (VNĐ)','Số quyết định','Ghi chú'],
        types: ['date','text','number','number','text','textarea'],
        tableFields: ['effective_date','salary_grade','salary_coefficient','base_salary','decision_number']
    },
    family_members: {
        title: '4. Quan hệ Gia đình',
        saveUrl: `${BASE}/employee/saveFamilyMember`,
        deleteUrl: `${BASE}/employee/deleteFamilyMember`,
        columns: ['Họ tên', 'Quan hệ', 'Năm sinh', 'Nghề nghiệp', 'Nơi ở'],
        fields: ['full_name','relationship','dob','occupation','workplace','address','id_card','phone','notes'],
        colWidths: ['col-6','col-3','col-3','col-6','col-6','col-12','col-4','col-4','col-12'],
        labels: ['Họ tên','Quan hệ','Ngày sinh','Nghề nghiệp','Nơi làm việc','Nơi ở hiện nay','Số CCCD','Điện thoại','Ghi chú'],
        types: ['text','select:Bố|Mẹ|Vợ|Chồng|Con trai|Con gái|Anh|Chị|Em','date','text','text','text','text','text','textarea'],
        tableFields: ['full_name','relationship','dob','occupation','address']
    },
    reward_disciplines: {
        title: '5. Khen thưởng – Kỷ luật',
        saveUrl: `${BASE}/employee/saveRewardDiscipline`,
        deleteUrl: `${BASE}/employee/deleteRewardDiscipline`,
        columns: ['Loại', 'Số QĐ', 'Ngày QĐ', 'Hình thức', 'Cơ quan QĐ'],
        fields: ['type','decision_number','decision_date','title','reason','authority','notes'],
        colWidths: ['col-4','col-4','col-4','col-12','col-12','col-6','col-12'],
        labels: ['Loại','Số quyết định','Ngày quyết định','Hình thức KT/KL','Lý do','Cơ quan quyết định','Ghi chú'],
        types: ['select:Reward|Discipline','text','date','text','textarea','text','textarea'],
        tableFields: ['type','decision_number','decision_date','title','authority']
    },
    evaluations: {
        title: '6. Đánh giá KPI',
        saveUrl: `${BASE}/employee/saveEvaluation`,
        deleteUrl: `${BASE}/employee/deleteEvaluation`,
        columns: ['Năm', 'Kỳ đánh giá', 'Xếp loại', 'Điểm', 'Người đánh giá'],
        fields: ['eval_year','eval_period','rating','score','evaluator','notes'],
        colWidths: ['col-3','col-3','col-3','col-3','col-6','col-12'],
        labels: ['Năm đánh giá','Kỳ đánh giá','Xếp loại','Điểm số','Người đánh giá','Nhận xét'],
        types: ['number','select:6 tháng đầu|6 tháng cuối|Cả năm','select:Xuất sắc|Tốt|Khá|Trung bình|Yếu','number','text','textarea'],
        tableFields: ['eval_year','eval_period','rating','score','evaluator']
    },
    appointments: {
        title: '7. Quá trình Bổ nhiệm',
        saveUrl: `${BASE}/employee/saveAppointment`,
        deleteUrl: `${BASE}/employee/deleteAppointment`,
        columns: ['Ngày hiệu lực', 'Chức vụ', 'Phòng ban', 'Số QĐ'],
        fields: ['effective_date','position_title','department','decision_number','notes'],
        colWidths: ['col-4','col-4','col-4','col-6','col-12'],
        labels: ['Ngày hiệu lực','Chức vụ được bổ nhiệm','Phòng ban / Bộ phận','Số quyết định','Ghi chú'],
        types: ['date','text','text','text','textarea'],
        tableFields: ['effective_date','position_title','department','decision_number']
    }
};

function openProcessModal(processKey) {
    currentProcess = processKey;
    const cfg = PROCESS_CONFIG[processKey];
    document.getElementById('processModalTitle').textContent = cfg.title;
    document.getElementById('pf_process_type').value = processKey;
    
    // Build table header
    let headHtml = '<tr>';
    headHtml += '<th>#</th>';
    cfg.columns.forEach(c => headHtml += `<th>${c}</th>`);
    headHtml += '</tr>';
    document.getElementById('processTableHead').innerHTML = headHtml;
    
    // Build form fields
    let formHtml = '';
    cfg.fields.forEach((f, i) => {
        const colW = cfg.colWidths[i] || 'col-6';
        const label = cfg.labels[i] || f;
        const type = cfg.types[i] || 'text';
        const widthStyle = colW === 'col-12' ? 'width:100%' : (colW === 'col-6' ? 'width:50%' : (colW === 'col-4' ? 'width:33.33%' : 'width:25%'));
        
        formHtml += `<div class="form-group" style="${widthStyle}; display:inline-block; vertical-align:top;">`;
        formHtml += `<label for="pf_${f}">${label}</label>`;
        
        if (type === 'textarea') {
            formHtml += `<textarea name="${f}" id="pf_${f}" rows="2"></textarea>`;
        } else if (type.startsWith('select:')) {
            const opts = type.substring(7).split('|');
            formHtml += `<select name="${f}" id="pf_${f}"><option value="">-- Chọn --</option>`;
            opts.forEach(o => formHtml += `<option value="${o}">${o}</option>`);
            formHtml += '</select>';
        } else {
            formHtml += `<input type="${type}" name="${f}" id="pf_${f}" step="any">`;
        }
        formHtml += '</div>';
    });
    document.getElementById('processFormFields').innerHTML = formHtml;
    
    // Show modal
    document.getElementById('processModalOverlay').style.display = 'flex';
    document.body.style.overflow = 'hidden';
    
    // Load data
    loadProcessData(processKey);
    resetProcessForm();
}

function closeProcessModal() {
    document.getElementById('processModalOverlay').style.display = 'none';
    document.body.style.overflow = '';
}

// Click outside modal to close
document.addEventListener('click', (e) => {
    if (e.target.id === 'processModalOverlay') closeProcessModal();
});

// ESC to close
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeProcessModal();
});

function loadProcessData(processKey) {
    fetch(`${BASE}/employee/getProcesses/${EMP_ID}`)
        .then(r => r.json())
        .then(resp => {
            if (resp.success) {
                processData = resp.data;
                renderProcessTable(processKey);
            }
        })
        .catch(err => console.error('Load error:', err));
}

function renderProcessTable(processKey) {
    const cfg = PROCESS_CONFIG[processKey];
    const rows = processData[processKey] || [];
    const tbody = document.getElementById('processTableBody');
    
    if (rows.length === 0) {
        tbody.innerHTML = `<tr><td colspan="${cfg.columns.length + 1}" style="text-align:center; color:var(--text-muted); padding:20px;">Chưa có dữ liệu</td></tr>`;
        return;
    }
    
    let html = '';
    rows.forEach((row, idx) => {
        html += `<tr onclick="selectProcessRow(${JSON.stringify(row).replace(/"/g, '&quot;')})">`;
        html += `<td>${idx + 1}</td>`;
        cfg.tableFields.forEach(f => {
            let val = row[f] ?? '---';
            // Format dates
            if (f.includes('date') || f === 'from_date' || f === 'to_date' || f === 'effective_date' || f === 'dob') {
                val = val && val !== '---' ? formatDate(val) : '---';
            }
            // Format money
            if (f === 'base_salary' && val !== '---') {
                val = Number(val).toLocaleString('vi-VN');
            }
            html += `<td>${val}</td>`;
        });
        html += '</tr>';
    });
    tbody.innerHTML = html;
}

function formatDate(d) {
    if (!d) return '---';
    const parts = d.split('-');
    if (parts.length === 3) return `${parts[2]}/${parts[1]}/${parts[0]}`;
    return d;
}

function selectProcessRow(rowData) {
    const cfg = PROCESS_CONFIG[currentProcess];
    document.getElementById('pf_id').value = rowData.id;
    
    cfg.fields.forEach(f => {
        const el = document.getElementById(`pf_${f}`);
        if (el) el.value = rowData[f] ?? '';
    });
    
    document.getElementById('btnDeleteProcess').style.display = 'inline-flex';
    
    // Highlight selected row
    document.querySelectorAll('#processTableBody tr').forEach(tr => tr.classList.remove('selected'));
    event.currentTarget.classList.add('selected');
}

function resetProcessForm() {
    document.getElementById('pf_id').value = '0';
    const cfg = PROCESS_CONFIG[currentProcess];
    if (cfg) {
        cfg.fields.forEach(f => {
            const el = document.getElementById(`pf_${f}`);
            if (el) el.value = '';
        });
    }
    document.getElementById('btnDeleteProcess').style.display = 'none';
    document.querySelectorAll('#processTableBody tr').forEach(tr => tr.classList.remove('selected'));
}

function saveProcess(e) {
    if (e) e.preventDefault();
    const cfg = PROCESS_CONFIG[currentProcess];
    const form = document.getElementById('processForm');
    const formData = new FormData(form);
    
    fetch(cfg.saveUrl, { method: 'POST', body: formData })
        .then(r => r.json())
        .then(resp => {
            if (resp.success) {
                showToast(resp.message, 'success');
                loadProcessData(currentProcess);
                resetProcessForm();
            } else {
                showToast(resp.message || 'Có lỗi xảy ra', 'error');
            }
        })
        .catch(err => {
            console.error('Save error:', err);
            showToast('Lỗi kết nối server', 'error');
        });
}

function deleteProcess() {
    const id = document.getElementById('pf_id').value;
    if (!id || id === '0') return;
    
    if (!confirm('Bạn có chắc chắn muốn xóa bản ghi này?')) return;
    
    const cfg = PROCESS_CONFIG[currentProcess];
    fetch(`${cfg.deleteUrl}/${id}`, { method: 'POST' })
        .then(r => r.json())
        .then(resp => {
            if (resp.success) {
                showToast(resp.message, 'success');
                loadProcessData(currentProcess);
                resetProcessForm();
            } else {
                showToast(resp.message || 'Có lỗi xảy ra', 'error');
            }
        })
        .catch(err => {
            console.error('Delete error:', err);
            showToast('Lỗi kết nối server', 'error');
        });
}

// Toast notification
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = 'toast-notification';
    toast.style.cssText = `
        position:fixed; top:20px; right:20px; z-index:99999; padding:14px 24px;
        border-radius:12px; font-size:14px; font-weight:600; color:#fff;
        box-shadow:0 10px 30px rgba(0,0,0,0.2); animation:slideIn 0.3s ease;
        background:${type === 'success' ? '#10b981' : '#ef4444'};
    `;
    toast.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}" style="margin-right:8px;"></i>${message}`;
    document.body.appendChild(toast);
    setTimeout(() => { toast.style.animation = 'fadeOut 0.3s ease'; setTimeout(() => toast.remove(), 300); }, 3000);
}
</script>
<style>
@keyframes slideIn { from { transform:translateX(100px); opacity:0; } to { transform:translateX(0); opacity:1; } }
@keyframes fadeOut { to { opacity:0; transform:translateY(-10px); } }
</style>
