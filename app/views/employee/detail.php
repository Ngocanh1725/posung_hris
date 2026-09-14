<?php
/**
 * ============================================================
 *  View: employee/detail.php
 *  Hồ sơ nhân sự 360 độ (Cập nhật chuẩn HRM)
 * ============================================================
 */
?>

<div class="profile-header panel mb-4 overflow-hidden position-relative" style="border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.05); border-radius: var(--radius-lg);">
    <!-- Premium Cover Image -->
    <div class="profile-cover" style="height: 160px; background: linear-gradient(135deg, rgba(37,99,235,0.85), rgba(14,165,233,0.85)), url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'0.15\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E'); background-size: cover; border-top-left-radius: var(--radius-lg); border-top-right-radius: var(--radius-lg);">
    </div>
    
    <div class="panel-body d-flex flex-column flex-md-row gap-4 position-relative" style="padding: 0 30px 30px;">
        <!-- Avatar Column -->
        <div class="profile-avatar text-center" style="margin-top: -60px; position: relative; z-index: 2; width: max-content;">
            <div class="avatar-wrapper" style="border: 4px solid #fff; border-radius: 20px; box-shadow: 0 8px 24px rgba(0,0,0,0.12); display: inline-block; background: #fff; transition: transform 0.3s ease;">
                <?php if (!empty($employee->avatar_path)): ?>
                    <img src="<?= BASE_URL ?>/<?= htmlspecialchars($employee->avatar_path) ?>" alt="Avatar" class="avatar-xl" style="width: 140px; height: 140px; border-radius: 16px; object-fit: cover;">
                <?php else: ?>
                    <div class="avatar-xl-initial" style="width: 140px; height: 140px; border-radius: 16px; background: linear-gradient(135deg, var(--primary), var(--accent)); color: #fff; font-size: 56px; display: flex; align-items: center; justify-content: center; font-weight: 800;"><?= mb_substr($employee->full_name, 0, 1) ?></div>
                <?php endif; ?>
            </div>
            <div class="mt-3">
                <span class="badge badge-<?= strtolower($employee->status) ?>" style="font-size: 13px; padding: 6px 14px; box-shadow: 0 4px 10px rgba(0,0,0,0.06);">
                    <i class="fas fa-circle" style="font-size: 8px; margin-right: 6px; vertical-align: middle;"></i><?= htmlspecialchars($employee->status) ?>
                </span>
            </div>
        </div>

        <!-- Info Column -->
        <div class="profile-info flex-1" style="padding-top: 24px;">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h2 class="profile-name mb-1" style="font-size: 28px; font-weight: 800; letter-spacing: -0.5px; color: var(--text-heading);">
                        <?= htmlspecialchars($employee->full_name) ?>
                    </h2>
                    <p class="text-muted mb-0" style="font-size: 14px; font-weight: 500;">
                        Mã NV: <span class="badge bg-secondary text-dark px-2 py-1"><?= htmlspecialchars($employee->emp_code) ?></span>
                    </p>
                </div>
                <div class="d-flex gap-2 mt-2 mt-md-0">
                    <a href="<?= BASE_URL ?>/employee/print2c/<?= $employee->id ?>" target="_blank" class="btn btn-primary btn-sm" style="border-radius: 8px; padding: 8px 16px; font-weight: 600; box-shadow: 0 6px 15px rgba(37,99,235,0.25);">
                        <i class="fas fa-print"></i> Sơ yếu lý lịch (2C)
                    </a>
                </div>
            </div>
            
            <div class="profile-meta mt-4" style="background: rgba(248,250,252,0.9); padding: 16px 20px; border-radius: 12px; border: 1px solid var(--border); display: flex; flex-wrap: wrap; gap: 24px;">
                <div class="meta-item"><i class="fas fa-briefcase text-primary" style="font-size: 16px;"></i> <span class="fw-bold text-dark" style="font-size: 14px;"><?= htmlspecialchars($employee->pos_title ?? 'Chưa cập nhật') ?></span></div>
                <div class="meta-item"><i class="fas fa-sitemap text-info" style="font-size: 16px;"></i> <span style="font-size: 14px;"><?= htmlspecialchars($employee->dept_name ?? 'Chưa phân bổ') ?></span></div>
                <div class="meta-item"><i class="fas fa-hard-hat text-warning" style="font-size: 16px;"></i> <span style="font-size: 14px;">Dự án: <strong class="text-dark"><?= htmlspecialchars($employee->project_name ?? 'N/A') ?></strong></span></div>
            </div>

            <div class="profile-contact mt-3 d-flex flex-wrap gap-4">
                <div class="contact-item p-2 rounded hover-bg" style="transition: all 0.2s; display: flex; align-items: center; gap: 8px;"><i class="fas fa-phone-alt text-muted"></i> <a href="tel:<?= htmlspecialchars($employee->phone) ?>" class="text-secondary" style="font-weight: 500; text-decoration: none; font-size: 14px;"><?= htmlspecialchars($employee->phone ?: '---') ?></a></div>
                <div class="contact-item p-2 rounded hover-bg" style="transition: all 0.2s; display: flex; align-items: center; gap: 8px;"><i class="fas fa-envelope text-muted"></i> <a href="mailto:<?= htmlspecialchars($employee->email) ?>" class="text-secondary" style="font-weight: 500; text-decoration: none; font-size: 14px;"><?= htmlspecialchars($employee->email ?: '---') ?></a></div>
            </div>
        </div>
    </div>
</div>

<div class="panel" style="box-shadow: 0 10px 30px rgba(0,0,0,0.03); border: none;">
    <div class="panel-body p-0">
        <!-- Modern Tabs -->
        <div class="tabs-wrapper px-4 pt-3" style="border-bottom: 1px solid var(--border); background: var(--bg-card); overflow-x: auto;">
            <ul class="nav-tabs-modern" id="profileTabs">
                <li class="tab-link active" data-target="tab-personal">
                    <i class="fas fa-user-circle"></i> <span>Thông tin chung</span>
                </li>
                <li class="tab-link" data-target="tab-contract">
                    <i class="fas fa-file-signature"></i> <span>Hợp đồng & Pháp lý</span>
                </li>
                <?php if ($employee->employee_type === 'Expat'): ?>
                <li class="tab-link" data-target="tab-expat">
                    <i class="fas fa-passport"></i> <span>Thông tin Expat</span>
                </li>
                <?php endif; ?>
                <li class="tab-link" data-target="tab-history">
                    <i class="fas fa-history"></i> <span>Quá trình công tác</span>
                </li>
                <li class="tab-link" data-target="tab-certs">
                    <i class="fas fa-certificate"></i> <span>Bằng cấp & HSE</span>
                </li>
                <li class="tab-link" data-target="tab-salary">
                    <i class="fas fa-money-check-dollar"></i> <span>Lương, PC & Nghỉ phép</span>
                </li>
            </ul>
        </div>
        
        <div class="p-4">
            <!-- TAB 1: THÔNG TIN CHUNG -->
            <div class="tab-content active" id="tab-personal">
                <div class="row">
                    <div class="col-md-6">
                        <h4 class="section-title">Lý lịch trích ngang</h4>
                        <table class="table-info">
                            <tr><td>Ngày sinh:</td><td><?= $employee->dob ? date('d/m/Y', strtotime($employee->dob)) : '---' ?></td></tr>
                            <tr><td>Giới tính:</td><td><?= $employee->gender === 'Male' ? 'Nam' : ($employee->gender === 'Female' ? 'Nữ' : 'Khác') ?></td></tr>
                            <tr><td>Hôn nhân:</td><td>
                                <?php
                                $marital = [
                                    'Single' => 'Độc thân',
                                    'Married' => 'Đã kết hôn',
                                    'Divorced' => 'Ly hôn',
                                    'Widowed' => 'Góa'
                                ];
                                echo $marital[$employee->marital_status ?? 'Single'] ?? 'Độc thân';
                                ?>
                            </td></tr>
                            <tr><td>Dân tộc:</td><td><?= htmlspecialchars($employee->ethnic ?: '---') ?></td></tr>
                            <tr><td>Tôn giáo:</td><td><?= htmlspecialchars($employee->religion ?: '---') ?></td></tr>
                            <tr><td>Nhóm máu:</td><td><?= htmlspecialchars($employee->blood_group ?: '---') ?></td></tr>
                            <tr><td>Quốc tịch:</td><td><?= htmlspecialchars($employee->nationality) ?></td></tr>
                            <tr><td>Số CCCD/HC:</td><td><?= htmlspecialchars($employee->id_card) ?> (Cấp: <?= $employee->id_card_date ? date('d/m/Y', strtotime($employee->id_card_date)) : '---' ?> tại <?= htmlspecialchars($employee->id_card_place) ?>)</td></tr>
                            <tr><td>Quê quán:</td><td><?= htmlspecialchars($employee->hometown ?: '---') ?></td></tr>
                            <tr><td>Thường trú:</td><td><?= htmlspecialchars($employee->address ?: '---') ?></td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h4 class="section-title">Tổ chức & Trình độ</h4>
                        <table class="table-info">
                            <tr><td>Loại nhân sự:</td><td>
                                <span class="badge bg-secondary"><?= htmlspecialchars($employee->employee_type) ?></span>
                            </td></tr>
                            <tr><td>Ngày vào làm:</td><td><?= $employee->join_date ? date('d/m/Y', strtotime($employee->join_date)) : '---' ?></td></tr>
                            <tr><td>Ký HĐ chính thức:</td><td><?= $employee->official_date ? date('d/m/Y', strtotime($employee->official_date)) : '---' ?></td></tr>
                            <tr><td>Trình độ chuyên môn:</td><td><?= htmlspecialchars($employee->highest_degree ?: '---') ?></td></tr>
                            <tr><td>Ngày vào Đảng:</td><td><?= $employee->party_join_date ? date('d/m/Y', strtotime($employee->party_join_date)) : '---' ?></td></tr>
                        </table>

                        <h4 class="section-title mt-4">Liên hệ Khẩn cấp</h4>
                        <table class="table-info">
                            <tr><td>Người liên hệ:</td><td><?= htmlspecialchars($employee->emergency_contact_name ?: '---') ?></td></tr>
                            <tr><td>Mối quan hệ:</td><td><?= htmlspecialchars($employee->emergency_contact_relation ?: '---') ?></td></tr>
                            <tr><td>Số điện thoại:</td><td><?= htmlspecialchars($employee->emergency_contact_phone ?: '---') ?></td></tr>
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

            <!-- TAB 2: HỢP ĐỒNG & PHÁP LÝ -->
            <div class="tab-content" id="tab-contract">
                <div class="row">
                    <div class="col-md-6">
                        <h4 class="section-title">Thuế & Bảo hiểm & Ngân hàng</h4>
                        <table class="table-info">
                            <tr><td>Mã số thuế:</td><td class="fw-bold"><?= htmlspecialchars($employee->tax_code ?: '---') ?></td></tr>
                            <tr><td>Số sổ BHXH:</td><td class="fw-bold text-primary"><?= htmlspecialchars($employee->social_insurance_no ?: '---') ?></td></tr>
                            <tr><td>Số thẻ BHYT:</td><td><?= htmlspecialchars($employee->health_insurance_no ?: '---') ?></td></tr>
                            <tr><td>Số Tài khoản:</td><td class="fw-bold"><?= htmlspecialchars($employee->bank_account ?: '---') ?></td></tr>
                            <tr><td>Ngân hàng:</td><td><?= htmlspecialchars($employee->bank_name ?: '---') ?></td></tr>
                            <tr><td>Chi nhánh:</td><td><?= htmlspecialchars($employee->bank_branch ?: '---') ?></td></tr>
                        </table>

                        <h4 class="section-title mt-4 d-flex justify-content-between align-items-center">
                            <span>Người phụ thuộc (Giảm trừ gia cảnh)</span>
                        </h4>
                        <?php if (empty($employee->dependents)): ?>
                            <div class="alert alert-info py-2">Chưa có người phụ thuộc.</div>
                        <?php else: ?>
                            <div class="table-wrapper">
                                <table>
                                    <thead><tr><th>Họ tên</th><th>Quan hệ</th><th>Mã số thuế</th></tr></thead>
                                    <tbody>
                                        <?php foreach ($employee->dependents as $dep): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($dep->full_name) ?></td>
                                            <td><?= htmlspecialchars($dep->relationship) ?></td>
                                            <td><?= htmlspecialchars($dep->tax_code ?: '---') ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <h4 class="section-title d-flex justify-content-between align-items-center">
                            <span>Lịch sử Hợp đồng</span>
                        </h4>
                        <?php if (empty($employee->contracts)): ?>
                            <div class="alert alert-info py-2">Chưa có dữ liệu hợp đồng.</div>
                        <?php else: ?>
                            <div class="timeline">
                                <?php foreach ($employee->contracts as $contract): ?>
                                <div class="timeline-item">
                                    <div class="timeline-dot"></div>
                                    <div class="timeline-content">
                                        <div class="timeline-date"><?= date('d/m/Y', strtotime($contract->start_date)) ?> - <?= $contract->end_date ? date('d/m/Y', strtotime($contract->end_date)) : 'Không xác định' ?></div>
                                        <div class="timeline-title">
                                            <span class="badge bg-secondary"><?= htmlspecialchars($contract->status) ?></span> 
                                            Số HĐ: <?= htmlspecialchars($contract->contract_number) ?>
                                        </div>
                                        <div class="timeline-desc mt-2">
                                            <strong>Loại HĐ:</strong> <?= htmlspecialchars($contract->contract_type_name ?: '---') ?><br>
                                            <strong>Lương cơ bản:</strong> <span class="text-primary fw-bold"><?= number_format($contract->basic_salary) ?> VNĐ</span>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- TAB EXPAT -->
            <?php if ($employee->employee_type === 'Expat' && isset($employee->expat)): ?>
            <div class="tab-content" id="tab-expat">
                <div class="row">
                    <div class="col-md-6">
                        <h4 class="section-title">Thị thực & Giấy phép</h4>
                        <table class="table-info">
                            <tr><td>Số Hộ chiếu:</td><td><?= htmlspecialchars($employee->expat->passport_number ?: '---') ?></td></tr>
                            <tr>
                                <td>Số Visa:</td>
                                <td><?= htmlspecialchars($employee->expat->visa_number ?: '---') ?> 
                                    (Hạn: <?= $employee->expat->visa_expiry ? date('d/m/Y', strtotime($employee->expat->visa_expiry)) : '---' ?>)
                                </td>
                            </tr>
                            <tr>
                                <td>Work Permit:</td>
                                <td><?= htmlspecialchars($employee->expat->work_permit_number ?: '---') ?> 
                                    (Hạn: <?= $employee->expat->work_permit_expiry ? date('d/m/Y', strtotime($employee->expat->work_permit_expiry)) : '---' ?>)
                                </td>
                            </tr>
                            <tr>
                                <td>Thẻ tạm trú (TRC):</td>
                                <td><?= htmlspecialchars($employee->expat->trc_number ?: '---') ?> 
                                    (Hạn: <?= $employee->expat->trc_expiry ? date('d/m/Y', strtotime($employee->expat->trc_expiry)) : '---' ?>)
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- TAB 3: QUÁ TRÌNH CÔNG TÁC -->
            <div class="tab-content" id="tab-history">
                <div class="row">
                    <div class="col-md-6">
                        <h4 class="section-title">Nội bộ (Điều động & Bổ nhiệm)</h4>
                        <div class="timeline">
                            <?php if (empty($employee->movements)): ?>
                                <div class="text-muted">Chưa có ghi nhận điều động nào.</div>
                            <?php else: ?>
                                <?php foreach ($employee->movements as $mov): ?>
                                <div class="timeline-item">
                                    <div class="timeline-dot"></div>
                                    <div class="timeline-content">
                                        <div class="timeline-date"><?= date('d/m/Y', strtotime($mov->effective_date)) ?></div>
                                        <div class="timeline-title">
                                            <span class="badge bg-secondary"><?= htmlspecialchars($mov->movement_type) ?></span> 
                                            QĐ: <?= htmlspecialchars($mov->decision_number ?: 'N/A') ?>
                                        </div>
                                        <div class="timeline-desc mt-2">
                                            <?php if ($mov->from_project || $mov->to_project): ?>
                                                <strong>Dự án:</strong> <?= htmlspecialchars($mov->from_project ?? 'N/A') ?> <i class="fas fa-arrow-right mx-1"></i> <span class="text-primary"><?= htmlspecialchars($mov->to_project ?? 'N/A') ?></span><br>
                                            <?php endif; ?>
                                            <?php if ($mov->from_dept || $mov->to_dept): ?>
                                                <strong>Phòng ban:</strong> <?= htmlspecialchars($mov->from_dept ?? 'N/A') ?> <i class="fas fa-arrow-right mx-1"></i> <span class="text-primary"><?= htmlspecialchars($mov->to_dept ?? 'N/A') ?></span><br>
                                            <?php endif; ?>
                                            <div class="text-muted mt-1"><i class="fas fa-quote-left"></i> <em><?= htmlspecialchars($mov->reason ?: '---') ?></em></div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h4 class="section-title">Kinh nghiệm trước khi vào Cty</h4>
                        <div class="timeline">
                            <?php if (empty($employee->work_experiences)): ?>
                                <div class="text-muted">Chưa có ghi nhận kinh nghiệm làm việc trước đây.</div>
                            <?php else: ?>
                                <?php foreach ($employee->work_experiences as $we): ?>
                                <div class="timeline-item">
                                    <div class="timeline-dot" style="background:var(--accent);"></div>
                                    <div class="timeline-content">
                                        <div class="timeline-date"><?= date('m/Y', strtotime($we->start_date)) ?> - <?= $we->end_date ? date('m/Y', strtotime($we->end_date)) : 'Nay' ?></div>
                                        <div class="timeline-title">
                                            <?= htmlspecialchars($we->company_name) ?>
                                        </div>
                                        <div class="timeline-desc mt-2">
                                            <strong>Chức vụ:</strong> <?= htmlspecialchars($we->position) ?><br>
                                            <div class="text-muted mt-1"><?= nl2br(htmlspecialchars($we->description)) ?></div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 4: BẰNG CẤP & HSE -->
            <div class="tab-content" id="tab-certs">
                <div class="d-flex justify-content-between mb-3">
                    <h4 class="section-title m-0">Chứng chỉ & Đào tạo an toàn</h4>
                    <button class="btn btn-ghost btn-sm"><i class="fas fa-plus"></i> Thêm chứng chỉ</button>
                </div>
                
                <?php if (empty($employee->certificates)): ?>
                    <div class="alert alert-info py-2">Chưa có chứng chỉ nào được ghi nhận.</div>
                <?php else: ?>
                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>Loại chứng chỉ</th>
                                    <th>Tên chứng chỉ</th>
                                    <th>Cơ quan cấp</th>
                                    <th>Ngày cấp</th>
                                    <th>Ngày hết hạn</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($employee->certificates as $cert): ?>
                                <tr>
                                    <td><span class="badge bg-secondary"><?= htmlspecialchars($cert->cert_type) ?></span></td>
                                    <td class="fw-bold"><?= htmlspecialchars($cert->cert_name) ?></td>
                                    <td><?= htmlspecialchars($cert->issuing_authority ?: '---') ?></td>
                                    <td><?= $cert->issue_date ? date('d/m/Y', strtotime($cert->issue_date)) : '---' ?></td>
                                    <td>
                                        <?php if ($cert->expiry_date): 
                                            $daysLeft = (strtotime($cert->expiry_date) - time()) / (60 * 60 * 24);
                                        ?>
                                            <span class="<?= $daysLeft < 30 ? 'text-danger fw-bold' : '' ?>">
                                                <?= date('d/m/Y', strtotime($cert->expiry_date)) ?>
                                            </span>
                                        <?php else: echo '---'; endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <!-- TAB 5: LƯƠNG, PHỤ CẤP & NGHỈ PHÉP -->
            <div class="tab-content" id="tab-salary">
                <?php if (!Session::isManager() && !Session::isAdmin()): ?>
                    <div class="alert alert-danger"><i class="fas fa-lock"></i> Bạn không có quyền xem thông tin chế độ lương.</div>
                <?php else: ?>
                    <div class="row">
                        <div class="col-md-12 mb-4">
                            <h4 class="section-title">Phụ cấp đang hưởng</h4>
                            <?php if (empty($employee->allowances)): ?>
                                <div class="text-muted">Không có phụ cấp đặc biệt.</div>
                            <?php else: ?>
                                <div class="d-flex flex-wrap gap-2">
                                    <?php foreach ($employee->allowances as $allw): ?>
                                        <div class="badge badge-primary py-2 px-3" style="font-size:13px; background: rgba(59, 130, 246, 0.1); color: var(--primary); border: 1px solid var(--primary);">
                                            <?= htmlspecialchars($allw->allowance_name) ?>: 
                                            <strong><?= number_format($allw->amount) ?> VNĐ</strong>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="col-md-6">
                            <h4 class="section-title">Lịch sử Lương</h4>
                            <?php if (empty($employee->salaries)): ?>
                                <div class="alert alert-info py-2">Chưa có dữ liệu lương.</div>
                            <?php else: ?>
                                <div class="table-wrapper">
                                    <table>
                                        <thead>
                                            <tr>
                                                <th>Ngày áp dụng</th>
                                                <th>Ngạch</th>
                                                <th>Lương CB</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($employee->salaries as $sal): ?>
                                            <tr>
                                                <td><?= date('d/m/Y', strtotime($sal->effective_date)) ?></td>
                                                <td><span class="badge bg-secondary"><?= htmlspecialchars($sal->grade_code ?: 'N/A') ?></span></td>
                                                <td class="fw-bold text-primary"><?= number_format($sal->base_salary) ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-6">
                            <h4 class="section-title">Lịch sử Nghỉ phép (Gần đây)</h4>
                            <?php if (empty($employee->leave_requests)): ?>
                                <div class="alert alert-info py-2">Chưa có lịch sử xin nghỉ.</div>
                            <?php else: ?>
                                <div class="timeline">
                                    <?php foreach ($employee->leave_requests as $leave): ?>
                                    <div class="timeline-item">
                                        <div class="timeline-dot" style="background:var(--warning);"></div>
                                        <div class="timeline-content">
                                            <div class="timeline-date"><?= date('d/m/Y', strtotime($leave->start_date)) ?> - <?= date('d/m/Y', strtotime($leave->end_date)) ?> (<?= $leave->total_days ?> ngày)</div>
                                            <div class="timeline-title">
                                                <span class="badge badge-<?= strtolower($leave->status) ?>"><?= htmlspecialchars($leave->status) ?></span> 
                                                <?= htmlspecialchars($leave->leave_type_name) ?>
                                            </div>
                                            <div class="timeline-desc mt-2">
                                                <div class="text-muted"><i class="fas fa-comment"></i> <?= htmlspecialchars($leave->reason) ?></div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

        </div> <!-- /p-4 -->
    </div>
</div>

<style>
/* Layout Utilities */
.gap-4 { gap: 1.5rem; }
.gap-2 { gap: 0.5rem; }
.flex-1 { flex: 1; }
.flex-column { flex-direction: column; }
.justify-content-between { justify-content: space-between; }
.align-items-center { align-items: center; }
.align-items-start { align-items: flex-start; }
.mx-1 { margin-left: 0.25rem; margin-right: 0.25rem; }
.m-0 { margin: 0; }
.mb-1 { margin-bottom: 0.25rem; }
.mb-3 { margin-bottom: 1rem; }
.mb-4 { margin-bottom: 1.5rem; }
.mt-2 { margin-top: 0.5rem; }
.mt-3 { margin-top: 1rem; }
.mt-4 { margin-top: 1.5rem; }
.py-2 { padding-top: 0.5rem; padding-bottom: 0.5rem; }
.px-3 { padding-left: 1rem; padding-right: 1rem; }
.p-0 { padding: 0 !important; }
.p-4 { padding: 1.5rem; }
.pt-3 { padding-top: 1rem; }
.px-4 { padding-left: 1.5rem; padding-right: 1.5rem; }
.text-dark { color: #0f172a; }
.hover-bg:hover { background: rgba(37, 99, 235, 0.05); }
@media (min-width: 768px) { .flex-md-row { flex-direction: row; } .mt-md-0 { margin-top: 0; } }

/* Modern Tabs */
.nav-tabs-modern { display: flex; list-style: none; margin: 0; padding: 0; }
.nav-tabs-modern .tab-link {
    position: relative;
    padding: 14px 20px;
    font-weight: 600;
    color: var(--text-secondary);
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 8px;
    white-space: nowrap;
}
.nav-tabs-modern .tab-link i {
    font-size: 16px;
    color: var(--text-muted);
    transition: all 0.3s;
}
.nav-tabs-modern .tab-link:hover { color: var(--primary); }
.nav-tabs-modern .tab-link:hover i { color: var(--primary); }
.nav-tabs-modern .tab-link.active { color: var(--primary); }
.nav-tabs-modern .tab-link.active i { color: var(--primary); }
.nav-tabs-modern .tab-link::after {
    content: '';
    position: absolute;
    bottom: -1px; left: 0; right: 0;
    height: 3px;
    background: var(--primary);
    border-radius: 3px 3px 0 0;
    opacity: 0;
    transform: scaleX(0.5);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.nav-tabs-modern .tab-link.active::after { opacity: 1; transform: scaleX(1); }

/* Tab Content */
.tab-content { display: none; animation: fadeIn 0.4s cubic-bezier(0.4, 0, 0.2, 1); }
.tab-content.active { display: block; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

/* Grid & Tables Refinements */
.row { display: flex; flex-wrap: wrap; margin: -12px; }
.col-md-6 { width: 50%; padding: 12px; }
@media (max-width: 768px) { .col-md-6 { width: 100%; } }
.col-md-12 { width: 100%; padding: 12px; }

/* Enhanced Section Title */
.section-title { 
    font-size: 16px; 
    font-weight: 700; 
    color: var(--text-heading); 
    margin-bottom: 20px; 
    padding-bottom: 12px; 
    border-bottom: 2px solid #f1f5f9; 
    display: flex;
    align-items: center;
    gap: 10px;
}
.section-title::before {
    content: '';
    display: block;
    width: 4px;
    height: 16px;
    background: var(--primary);
    border-radius: 4px;
}

/* Beautiful Table Grid Info */
.table-info { width: 100%; border-collapse: separate; border-spacing: 0; }
.table-info tr { transition: background 0.2s; }
.table-info tr:hover td { background: #f8fafc; }
.table-info td { 
    padding: 12px 16px; 
    border-bottom: 1px dashed var(--border); 
    font-size: 14px; 
}
.table-info tr:last-child td { border-bottom: none; }
.table-info td:first-child { 
    color: var(--text-secondary); 
    width: 35%; 
    font-weight: 600; 
    border-top-left-radius: 8px;
    border-bottom-left-radius: 8px;
}
.table-info td:last-child { 
    color: var(--text-heading); 
    font-weight: 500; 
    border-top-right-radius: 8px;
    border-bottom-right-radius: 8px;
}
.bg-secondary { background: rgba(148,163,184,0.15); color: #475569; }
.text-danger { color: var(--danger); }
.fw-bold { font-weight: 600; }

/* Timeline Glow Effect */
.timeline { position: relative; padding-left: 28px; margin-top: 16px; }
.timeline::before { content: ''; position: absolute; left: 8px; top: 0; bottom: 0; width: 2px; background: #e2e8f0; }
.timeline-item { position: relative; margin-bottom: 28px; }
.timeline-dot { 
    position: absolute; left: -25px; top: 5px; width: 12px; height: 12px; 
    border-radius: 50%; background: var(--primary); 
    border: 2px solid #fff; 
    box-shadow: 0 0 0 3px rgba(37,99,235,0.2); 
    transition: all 0.3s;
}
.timeline-item:hover .timeline-dot { transform: scale(1.2); box-shadow: 0 0 0 4px rgba(37,99,235,0.3); }
.timeline-date { font-size: 12px; color: var(--primary); margin-bottom: 6px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
.timeline-title { font-size: 15px; font-weight: 700; color: var(--text-heading); }
.timeline-desc { 
    font-size: 13.5px; color: var(--text-secondary); line-height: 1.6; 
    padding: 16px; background: #fff; 
    border-radius: 10px; border: 1px solid #e2e8f0; 
    margin-top: 10px;
    box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);
    transition: all 0.3s;
}
.timeline-item:hover .timeline-desc {
    box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05);
    border-color: #cbd5e1;
}

/* Avatar Hover Effect */
.avatar-wrapper:hover {
    transform: translateY(-5px);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
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
</script>
