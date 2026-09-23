<?php
/**
 * ============================================================
 *  View: employee/edit.php
 *  Form cập nhật nhân sự (5 Tabs) + Các trường Kỹ thuật/HSE mới
 * ============================================================
 */
?>

<div class="panel">
    <div class="panel-header">
        <h3><i class="fas fa-user-edit"></i> Cập nhật Hồ sơ Nhân sự</h3>
    </div>
    
    <div class="panel-body">
        <!-- Tabs Navigation -->
        <ul class="nav-tabs" id="createEmployeeTabs">
            <li class="tab-link active" data-target="tab-personal"><i class="fas fa-user"></i> 1. Cá nhân</li>
            <li class="tab-link" data-target="tab-extra"><i class="fas fa-id-card"></i> 2. TT Khác</li>
            <li class="tab-link" data-target="tab-org"><i class="fas fa-sitemap"></i> 3. Tổ chức</li>
            <li class="tab-link" data-target="tab-expat" id="nav-expat" style="<?= $employee->employee_type === 'Expat' ? 'display:block;' : 'display:none;' ?>"><i class="fas fa-passport"></i> 4. Expat</li>
            <li class="tab-link" data-target="tab-hse"><i class="fas fa-hard-hat"></i> 5. Kỹ thuật & HSE</li>
            <li class="tab-link" data-target="tab-files"><i class="fas fa-paperclip"></i> 6. Đính kèm</li>
        </ul>

        <form action="<?= BASE_URL ?>/employee/update/<?= $employee->id ?>" method="POST" enctype="multipart/form-data" id="formEdit">
            <input type="hidden" name="_csrf_token" value="<?= Session::generateCsrfToken() ?>">
            
            <!-- TAB 1: CÁ NHÂN -->
            <div class="tab-content active" id="tab-personal">
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Họ và tên *</label>
                        <input type="text" name="full_name" class="form-control" required value="<?= h($employee->full_name ?? '') ?>">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Giới tính</label>
                        <select name="gender" class="form-control">
                            <option value="Male" <?= ($employee->gender ?? '') === 'Male' ? 'selected' : '' ?>>Nam</option>
                            <option value="Female" <?= ($employee->gender ?? '') === 'Female' ? 'selected' : '' ?>>Nữ</option>
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label>Tình trạng hôn nhân</label>
                        <select name="marital_status" class="form-control">
                            <option value="Single" <?= ($employee->marital_status ?? '') === 'Single' ? 'selected' : '' ?>>Độc thân</option>
                            <option value="Married" <?= ($employee->marital_status ?? '') === 'Married' ? 'selected' : '' ?>>Đã kết hôn</option>
                            <option value="Divorced" <?= ($employee->marital_status ?? '') === 'Divorced' ? 'selected' : '' ?>>Ly hôn</option>
                            <option value="Widowed" <?= ($employee->marital_status ?? '') === 'Widowed' ? 'selected' : '' ?>>Góa</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label>Dân tộc</label>
                        <input type="text" name="ethnic" class="form-control" value="<?= h($employee->ethnic ?? 'Kinh') ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label>Tôn giáo</label>
                        <input type="text" name="religion" class="form-control" value="<?= h($employee->religion ?? 'Không') ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label>Nhóm máu</label>
                        <select name="blood_group" class="form-control">
                            <option value="">-- Chọn --</option>
                            <option value="A" <?= ($employee->blood_group ?? '') === 'A' ? 'selected' : '' ?>>A</option>
                            <option value="B" <?= ($employee->blood_group ?? '') === 'B' ? 'selected' : '' ?>>B</option>
                            <option value="AB" <?= ($employee->blood_group ?? '') === 'AB' ? 'selected' : '' ?>>AB</option>
                            <option value="O" <?= ($employee->blood_group ?? '') === 'O' ? 'selected' : '' ?>>O</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Ngày sinh</label>
                        <input type="date" name="dob" class="form-control" value="<?= $employee->dob ?? '' ?>">
                    </div>
                    <div class="form-group col-md-6">
                        <label>Quốc tịch</label>
                        <input type="text" name="nationality" class="form-control" value="<?= h($employee->nationality ?? 'Vietnam') ?>">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>CCCD / Hộ chiếu *</label>
                        <input type="text" name="id_card" class="form-control" required value="<?= h($employee->id_card ?? '') ?>">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Ngày cấp</label>
                        <input type="date" name="id_card_date" class="form-control" value="<?= $employee->id_card_date ?? '' ?>">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Nơi cấp</label>
                        <input type="text" name="id_card_place" class="form-control" value="<?= h($employee->id_card_place ?? '') ?>">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Số điện thoại</label>
                        <input type="text" name="phone" class="form-control" value="<?= h($employee->phone ?? '') ?>">
                    </div>
                    <div class="form-group col-md-6">
                        <label>Email liên hệ</label>
                        <input type="email" name="email" class="form-control" value="<?= h($employee->email ?? '') ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label>Quê quán</label>
                    <input type="text" name="hometown" class="form-control" value="<?= h($employee->hometown ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Địa chỉ thường trú</label>
                    <input type="text" name="address" class="form-control" value="<?= h($employee->address ?? '') ?>">
                </div>
            </div>

            <!-- TAB 2: THÔNG TIN KHÁC (Thuế, BH, Ngân hàng) -->
            <div class="tab-content" id="tab-extra">
                <h4 class="mb-3">Thuế & Bảo hiểm</h4>
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>Mã số thuế</label>
                        <input type="text" name="tax_code" class="form-control" value="<?= h($employee->tax_code ?? '') ?>">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Số sổ BHXH</label>
                        <input type="text" name="social_insurance_no" class="form-control" value="<?= h($employee->social_insurance_no ?? '') ?>">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Số thẻ BHYT</label>
                        <input type="text" name="health_insurance_no" class="form-control" value="<?= h($employee->health_insurance_no ?? '') ?>">
                    </div>
                </div>
                
                <h4 class="mb-3 mt-4">Tài khoản Ngân hàng</h4>
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>Số tài khoản</label>
                        <input type="text" name="bank_account" class="form-control" value="<?= h($employee->bank_account ?? '') ?>">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Tên Ngân hàng</label>
                        <input type="text" name="bank_name" class="form-control" value="<?= h($employee->bank_name ?? '') ?>">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Chi nhánh</label>
                        <input type="text" name="bank_branch" class="form-control" value="<?= h($employee->bank_branch ?? '') ?>">
                    </div>
                </div>

                <h4 class="mb-3 mt-4">Liên hệ Khẩn cấp</h4>
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>Người liên hệ</label>
                        <input type="text" name="emergency_contact_name" class="form-control" value="<?= h($employee->emergency_contact_name ?? '') ?>">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Mối quan hệ</label>
                        <input type="text" name="emergency_contact_relation" class="form-control" value="<?= h($employee->emergency_contact_relation ?? '') ?>">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Số điện thoại</label>
                        <input type="text" name="emergency_contact_phone" class="form-control" value="<?= h($employee->emergency_contact_phone ?? '') ?>">
                    </div>
                </div>
            </div>

            <!-- TAB 3: TỔ CHỨC & CÔNG VIỆC -->
            <div class="tab-content" id="tab-org">
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Loại hình nhân sự *</label>
                        <select name="employee_type" id="employee_type" class="form-control" required>
                            <option value="Direct_Worker" <?= ($employee->employee_type ?? '') === 'Direct_Worker' ? 'selected' : '' ?>>Công nhân trực tiếp (Hàn, Lắp máy)</option>
                            <option value="Site_Engineer" <?= ($employee->employee_type ?? '') === 'Site_Engineer' ? 'selected' : '' ?>>Kỹ sư hiện trường</option>
                            <option value="Office_BIM" <?= ($employee->employee_type ?? '') === 'Office_BIM' ? 'selected' : '' ?>>Văn phòng / BIM</option>
                            <option value="Expat" <?= ($employee->employee_type ?? '') === 'Expat' ? 'selected' : '' ?>>Chuyên gia nước ngoài (Expat)</option>
                        </select>
                    </div>
                    <div class="form-group col-md-6">
                        <label>Trạng thái</label>
                        <select name="status" class="form-control">
                            <option value="Probation" <?= ($employee->status ?? '') === 'Probation' ? 'selected' : '' ?>>Thử việc</option>
                            <option value="Active" <?= ($employee->status ?? '') === 'Active' ? 'selected' : '' ?>>Đang làm việc chính thức</option>
                            <option value="Suspended" <?= ($employee->status ?? '') === 'Suspended' ? 'selected' : '' ?>>Đình chỉ</option>
                            <option value="Resigned" <?= ($employee->status ?? '') === 'Resigned' ? 'selected' : '' ?>>Đã nghỉ việc</option>
                            <option value="Retired" <?= ($employee->status ?? '') === 'Retired' ? 'selected' : '' ?>>Đã nghỉ hưu</option>
                            <option value="Blacklisted" <?= ($employee->status ?? '') === 'Blacklisted' ? 'selected' : '' ?>>Blacklist</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Phòng ban</label>
                        <select name="department_id" class="form-control">
                            <option value="">-- Chưa phân bổ --</option>
                            <?php foreach ($departments as $d): ?>
                                <option value="<?= $d['id'] ?>" <?= ($employee->department_id ?? '') == $d['id'] ? 'selected' : '' ?>><?= h($d['dept_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group col-md-6">
                        <label>Dự án hiện tại</label>
                        <select name="current_project_id" class="form-control">
                            <option value="">-- Chưa phân bổ --</option>
                            <?php foreach ($projects as $p): ?>
                                <option value="<?= $p['id'] ?>" <?= ($employee->current_project_id ?? '') == $p['id'] ? 'selected' : '' ?>><?= h($p['project_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>Chức danh / Vị trí</label>
                        <select name="position_id" class="form-control">
                            <option value="">-- Chọn --</option>
                            <?php foreach ($positions as $pos): ?>
                                <option value="<?= $pos['id'] ?>" <?= ($employee->position_id ?? '') == $pos['id'] ? 'selected' : '' ?>><?= h($pos['pos_title']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label>Ngày gia nhập</label>
                        <input type="date" name="join_date" class="form-control" value="<?= $employee->join_date ?? '' ?>">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Ngày ký HĐ chính thức</label>
                        <input type="date" name="official_date" class="form-control" value="<?= $employee->official_date ?? '' ?>">
                    </div>
                </div>
            </div>

            <!-- TAB 4: EXPAT -->
            <div class="tab-content" id="tab-expat">
                <div class="alert alert-warning mb-4">
                    <i class="fas fa-info-circle"></i> Vui lòng nhập đầy đủ thời hạn để hệ thống tự động cảnh báo (60/30/15 ngày) trước khi hết hạn.
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Số Hộ chiếu / Passport</label>
                        <input type="text" name="passport_number" class="form-control" value="<?= h($employee->expat->passport_number ?? '') ?>">
                    </div>
                    <div class="form-group col-md-6">
                        <label>Hạn Hộ chiếu</label>
                        <input type="date" name="passport_expiry" class="form-control" value="<?= $employee->expat->passport_expiry ?? '' ?>">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Số Visa</label>
                        <input type="text" name="visa_number" class="form-control" value="<?= h($employee->expat->visa_number ?? '') ?>">
                    </div>
                    <div class="form-group col-md-6">
                        <label>Ngày hết hạn Visa</label>
                        <input type="date" name="visa_expiry" class="form-control" value="<?= $employee->expat->visa_expiry ?? '' ?>">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Số Giấy phép lao động (Work Permit)</label>
                        <input type="text" name="work_permit_number" class="form-control" value="<?= h($employee->expat->work_permit_number ?? '') ?>">
                    </div>
                    <div class="form-group col-md-6">
                        <label>Hạn Giấy phép LĐ</label>
                        <input type="date" name="work_permit_expiry" class="form-control" value="<?= $employee->expat->work_permit_expiry ?? '' ?>">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Số Thẻ tạm trú (TRC)</label>
                        <input type="text" name="trc_number" class="form-control" value="<?= h($employee->expat->trc_number ?? '') ?>">
                    </div>
                    <div class="form-group col-md-6">
                        <label>Hạn Thẻ tạm trú</label>
                        <input type="date" name="trc_expiry" class="form-control" value="<?= $employee->expat->trc_expiry ?? '' ?>">
                    </div>
                </div>
            </div>

            <!-- TAB 5: KỸ THUẬT & HSE -->
            <div class="tab-content" id="tab-hse">
                <h4 class="mb-3">Kỹ năng Phần mềm M&E</h4>
                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label>AutoCAD</label>
                        <select name="skill_autocad" class="form-control">
                            <option value="">-- Không --</option>
                            <option value="Cơ bản" <?= ($employee->skill_autocad ?? '') === 'Cơ bản' ? 'selected' : '' ?>>Cơ bản</option>
                            <option value="Khá" <?= ($employee->skill_autocad ?? '') === 'Khá' ? 'selected' : '' ?>>Khá</option>
                            <option value="Giỏi" <?= ($employee->skill_autocad ?? '') === 'Giỏi' ? 'selected' : '' ?>>Giỏi</option>
                            <option value="Chuyên gia" <?= ($employee->skill_autocad ?? '') === 'Chuyên gia' ? 'selected' : '' ?>>Chuyên gia</option>
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label>Revit BIM</label>
                        <select name="skill_revit_bim" class="form-control">
                            <option value="">-- Không --</option>
                            <option value="Cơ bản" <?= ($employee->skill_revit_bim ?? '') === 'Cơ bản' ? 'selected' : '' ?>>Cơ bản</option>
                            <option value="Khá" <?= ($employee->skill_revit_bim ?? '') === 'Khá' ? 'selected' : '' ?>>Khá</option>
                            <option value="Giỏi" <?= ($employee->skill_revit_bim ?? '') === 'Giỏi' ? 'selected' : '' ?>>Giỏi</option>
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label>Navisworks</label>
                        <select name="skill_navisworks" class="form-control">
                            <option value="">-- Không --</option>
                            <option value="Cơ bản" <?= ($employee->skill_navisworks ?? '') === 'Cơ bản' ? 'selected' : '' ?>>Cơ bản</option>
                            <option value="Khá" <?= ($employee->skill_navisworks ?? '') === 'Khá' ? 'selected' : '' ?>>Khá</option>
                            <option value="Giỏi" <?= ($employee->skill_navisworks ?? '') === 'Giỏi' ? 'selected' : '' ?>>Giỏi</option>
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label>Dự toán</label>
                        <select name="skill_estimation" class="form-control">
                            <option value="">-- Không --</option>
                            <option value="Cơ bản" <?= ($employee->skill_estimation ?? '') === 'Cơ bản' ? 'selected' : '' ?>>Cơ bản</option>
                            <option value="Khá" <?= ($employee->skill_estimation ?? '') === 'Khá' ? 'selected' : '' ?>>Khá</option>
                            <option value="Giỏi" <?= ($employee->skill_estimation ?? '') === 'Giỏi' ? 'selected' : '' ?>>Giỏi</option>
                        </select>
                    </div>
                </div>

                <h4 class="mb-3 mt-4">Chứng chỉ Hàn</h4>
                <div class="form-row align-items-center">
                    <div class="form-group col-md-3">
                        <label><input type="checkbox" name="welding_cert_3g" value="1" <?= ($employee->welding_cert_3g ?? 0) ? 'checked' : '' ?>> Hàn 3G</label>
                    </div>
                    <div class="form-group col-md-3">
                        <label><input type="checkbox" name="welding_cert_6g" value="1" <?= ($employee->welding_cert_6g ?? 0) ? 'checked' : '' ?>> Hàn 6G</label>
                    </div>
                    <div class="form-group col-md-3">
                        <label><input type="checkbox" name="welding_cert_tig" value="1" <?= ($employee->welding_cert_tig ?? 0) ? 'checked' : '' ?>> Hàn TIG</label>
                    </div>
                    <div class="form-group col-md-3">
                        <label><input type="checkbox" name="welding_cert_mig" value="1" <?= ($employee->welding_cert_mig ?? 0) ? 'checked' : '' ?>> Hàn MIG</label>
                    </div>
                </div>

                <h4 class="mb-3 mt-4">Ngoại ngữ & Tin học & Học vấn</h4>
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>Tiếng Hàn</label>
                        <input type="text" name="korean_level" class="form-control" value="<?= h($employee->korean_level ?? '') ?>" placeholder="Topik / Giao tiếp">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Tiếng Anh</label>
                        <input type="text" name="english_level" class="form-control" value="<?= h($employee->english_level ?? '') ?>" placeholder="Toeic / Giao tiếp">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Tin học</label>
                        <input type="text" name="it_level" class="form-control" value="<?= h($employee->it_level ?? '') ?>" placeholder="VD: Mos, Cơ bản...">
                    </div>
                </div>
                <div class="form-group">
                    <label>Trình độ chuyên môn cao nhất</label>
                    <input type="text" name="highest_degree" class="form-control" value="<?= h($employee->highest_degree ?? '') ?>" placeholder="Đại học Bách Khoa - Cơ điện tử">
                </div>

                <h4 class="mb-3 mt-4">HSE & Sức khỏe</h4>
                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label>Số thẻ ATLĐ</label>
                        <input type="text" name="hse_card_number" class="form-control" value="<?= h($employee->hse_card_number ?? '') ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label>Ngày cấp</label>
                        <input type="date" name="hse_card_issue_date" class="form-control" value="<?= $employee->hse_card_issue_date ?? '' ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label>Hạn thẻ Samsung</label>
                        <input type="date" name="hse_card_expiry_samsung" class="form-control" value="<?= $employee->hse_card_expiry_samsung ?? '' ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label>Hạn thẻ Amkor</label>
                        <input type="date" name="hse_card_expiry_amkor" class="form-control" value="<?= $employee->hse_card_expiry_amkor ?? '' ?>">
                    </div>
                </div>
                
                <div class="form-row align-items-center mt-2 mb-3">
                    <div class="form-group col-md-4">
                        <label><input type="checkbox" name="can_work_at_height" value="1" <?= ($employee->can_work_at_height ?? 0) ? 'checked' : '' ?>> Đủ ĐK làm việc trên cao</label>
                    </div>
                    <div class="form-group col-md-4">
                        <label><input type="checkbox" name="can_work_confined_space" value="1" <?= ($employee->can_work_confined_space ?? 0) ? 'checked' : '' ?>> Đủ ĐK làm việc hầm kín</label>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label>Chiều cao (cm)</label>
                        <input type="number" step="0.1" name="chieu_cao" class="form-control" value="<?= $employee->chieu_cao ?? '' ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label>Cân nặng (kg)</label>
                        <input type="number" step="0.1" name="can_nang" class="form-control" value="<?= $employee->can_nang ?? '' ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label>Size giày bảo hộ</label>
                        <input type="text" name="safety_shoe_size" class="form-control" value="<?= h($employee->safety_shoe_size ?? '') ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label>Size áo bảo hộ</label>
                        <input type="text" name="safety_uniform_size" class="form-control" value="<?= h($employee->safety_uniform_size ?? '') ?>">
                    </div>
                </div>

                <div class="form-group mt-3">
                    <label>Ghi chú thêm về nhân sự</label>
                    <textarea name="notes" class="form-control" rows="4"><?= h($employee->notes ?? '') ?></textarea>
                </div>
            </div>

            <!-- TAB 6: FILE ĐÍNH KÈM -->
            <div class="tab-content" id="tab-files">
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Ảnh đại diện (Mới)</label>
                        <div class="file-upload-wrapper">
                            <input type="file" name="avatar" id="avatarInput" class="file-input" accept="image/*">
                            <div class="preview-box" id="avatarPreview">
                                <?php if (!empty($employee->avatar_path)): ?>
                                    <img src="<?= BASE_URL ?>/<?= h($employee->avatar_path) ?>" alt="Avatar">
                                <?php else: ?>
                                    <i class="fas fa-camera text-muted fa-3x"></i>
                                    <span class="d-block mt-2 text-muted">Click hoặc kéo thả ảnh mới</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="form-group col-md-6">
                        <label>Bản scan CCCD / Bằng cấp (Mới)</label>
                        <div class="file-upload-wrapper">
                            <input type="file" name="cv_file" class="form-control" style="padding-top: 10px;">
                            <?php if (!empty($employee->cv_file_path)): ?>
                                <div class="mt-3">
                                    <a href="<?= BASE_URL ?>/<?= h($employee->cv_file_path) ?>" target="_blank" class="btn btn-sm btn-info"><i class="fas fa-file-pdf"></i> Xem File Hiện Tại</a>
                                </div>
                            <?php endif; ?>
                            <div class="mt-2 text-muted small">
                                Hệ thống sẽ tự động thay thế file cũ nếu bạn upload file mới.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Nút Lưu -->
            <div class="form-actions mt-4 border-top pt-4 text-right">
                <a href="<?= BASE_URL ?>/employee/detail/<?= $employee->id ?>" class="btn btn-ghost">Hủy</a>
                <button type="submit" class="btn btn-primary" id="btnSubmit">
                    <i class="fas fa-save"></i> Lưu Thay Đổi Hồ Sơ
                </button>
            </div>

        </form>
    </div>
</div>



<script>
(function() {
    const tabs = document.querySelectorAll('.tab-link');
    const contents = document.querySelectorAll('.tab-content');

    if (tabs.length > 0) {
        tabs.forEach(tab => {
            tab.addEventListener('click', function() {
                tabs.forEach(t => t.classList.remove('active'));
                contents.forEach(c => c.classList.remove('active'));
                
                this.classList.add('active');
                const targetId = this.getAttribute('data-target');
                if (targetId) {
                    const targetContent = document.getElementById(targetId);
                    if (targetContent) {
                        targetContent.classList.add('active');
                    }
                }
            });
        });
    }

    const empTypeSelect = document.getElementById('employee_type');
    const navExpat = document.getElementById('nav-expat');
    
    if (empTypeSelect && navExpat) {
        empTypeSelect.addEventListener('change', (e) => {
            if (e.target.value === 'Expat') {
                navExpat.style.display = 'flex';
            } else {
                navExpat.style.display = 'none';
                if (navExpat.classList.contains('active') && tabs.length > 0) {
                    tabs[0].click();
                }
            }
        });
    }

    const avatarInput = document.getElementById('avatarInput');
    const avatarPreview = document.getElementById('avatarPreview');

    if (avatarInput && avatarPreview) {
        avatarInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    avatarPreview.innerHTML = `<img src="${e.target.result}" alt="Preview" style="width:100%; height:100%; object-fit:contain; border-radius: var(--radius-sm);">`;
                }
                reader.readAsDataURL(this.files[0]);
            }
        });
    }
})();
</script>
