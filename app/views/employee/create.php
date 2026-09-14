<?php
/**
 * ============================================================
 *  View: employee/create.php
 *  Form thêm mới nhân sự (5 Tabs)
 * ============================================================
 */
?>

<div class="panel">
    <div class="panel-header">
        <h3><i class="fas fa-user-plus"></i> Thêm mới Nhân sự</h3>
    </div>
    
    <div class="panel-body">
        <!-- Tabs Navigation -->
        <ul class="nav-tabs" id="createEmployeeTabs">
            <li class="tab-link active" data-target="tab-personal"><i class="fas fa-user"></i> 1. Cá nhân</li>
            <li class="tab-link" data-target="tab-extra"><i class="fas fa-id-card"></i> 2. TT Khác</li>
            <li class="tab-link" data-target="tab-org"><i class="fas fa-sitemap"></i> 3. Tổ chức</li>
            <li class="tab-link" data-target="tab-expat" id="nav-expat" style="display:none;"><i class="fas fa-passport"></i> 4. Expat</li>
            <li class="tab-link" data-target="tab-hse"><i class="fas fa-hard-hat"></i> 5. Trình độ & HSE</li>
            <li class="tab-link" data-target="tab-files"><i class="fas fa-paperclip"></i> 6. Đính kèm</li>
        </ul>

        <form action="<?= BASE_URL ?>/employee/store" method="POST" enctype="multipart/form-data" id="formCreate">
            
            <!-- TAB 1: CÁ NHÂN -->
            <div class="tab-content active" id="tab-personal">
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Họ và tên *</label>
                        <input type="text" name="full_name" class="form-control" required placeholder="Nguyễn Văn A">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Giới tính</label>
                        <select name="gender" class="form-control">
                            <option value="Male">Nam</option>
                            <option value="Female">Nữ</option>
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label>Tình trạng hôn nhân</label>
                        <select name="marital_status" class="form-control">
                            <option value="Single">Độc thân</option>
                            <option value="Married">Đã kết hôn</option>
                            <option value="Divorced">Ly hôn</option>
                            <option value="Widowed">Góa</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label>Dân tộc</label>
                        <input type="text" name="ethnic" class="form-control" value="Kinh">
                    </div>
                    <div class="form-group col-md-3">
                        <label>Tôn giáo</label>
                        <input type="text" name="religion" class="form-control" value="Không">
                    </div>
                    <div class="form-group col-md-3">
                        <label>Nhóm máu</label>
                        <select name="blood_group" class="form-control">
                            <option value="">-- Chọn --</option>
                            <option value="A">A</option>
                            <option value="B">B</option>
                            <option value="AB">AB</option>
                            <option value="O">O</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Ngày sinh</label>
                        <input type="date" name="dob" class="form-control">
                    </div>
                    <div class="form-group col-md-6">
                        <label>Quốc tịch</label>
                        <input type="text" name="nationality" class="form-control" value="Vietnam">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>CCCD / Hộ chiếu *</label>
                        <input type="text" name="id_card" class="form-control" required>
                    </div>
                    <div class="form-group col-md-4">
                        <label>Ngày cấp</label>
                        <input type="date" name="id_card_date" class="form-control">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Nơi cấp</label>
                        <input type="text" name="id_card_place" class="form-control">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Số điện thoại</label>
                        <input type="text" name="phone" class="form-control">
                    </div>
                    <div class="form-group col-md-6">
                        <label>Email liên hệ</label>
                        <input type="email" name="email" class="form-control">
                    </div>
                </div>
                <div class="form-group">
                    <label>Quê quán</label>
                    <input type="text" name="hometown" class="form-control">
                </div>
                <div class="form-group">
                    <label>Địa chỉ thường trú</label>
                    <input type="text" name="address" class="form-control">
                </div>
            </div>

            <!-- TAB 2: THÔNG TIN KHÁC (Thuế, BH, Ngân hàng) -->
            <div class="tab-content" id="tab-extra">
                <h4 class="mb-3">Thuế & Bảo hiểm</h4>
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>Mã số thuế</label>
                        <input type="text" name="tax_code" class="form-control">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Số sổ BHXH</label>
                        <input type="text" name="social_insurance_no" class="form-control">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Số thẻ BHYT</label>
                        <input type="text" name="health_insurance_no" class="form-control">
                    </div>
                </div>
                
                <h4 class="mb-3 mt-4">Tài khoản Ngân hàng</h4>
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>Số tài khoản</label>
                        <input type="text" name="bank_account" class="form-control">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Tên Ngân hàng</label>
                        <input type="text" name="bank_name" class="form-control">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Chi nhánh</label>
                        <input type="text" name="bank_branch" class="form-control">
                    </div>
                </div>

                <h4 class="mb-3 mt-4">Liên hệ Khẩn cấp</h4>
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>Người liên hệ</label>
                        <input type="text" name="emergency_contact_name" class="form-control">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Mối quan hệ</label>
                        <input type="text" name="emergency_contact_relation" class="form-control">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Số điện thoại</label>
                        <input type="text" name="emergency_contact_phone" class="form-control">
                    </div>
                </div>
            </div>

            <!-- TAB 3: TỔ CHỨC & CÔNG VIỆC -->
            <div class="tab-content" id="tab-org">
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Loại hình nhân sự *</label>
                        <select name="employee_type" id="employee_type" class="form-control" required>
                            <option value="Direct_Worker">Công nhân trực tiếp (Hàn, Lắp máy)</option>
                            <option value="Site_Engineer">Kỹ sư hiện trường</option>
                            <option value="Office_BIM">Văn phòng / BIM</option>
                            <option value="Expat">Chuyên gia nước ngoài (Expat)</option>
                        </select>
                    </div>
                    <div class="form-group col-md-6">
                        <label>Trạng thái</label>
                        <select name="status" class="form-control">
                            <option value="Probation">Thử việc</option>
                            <option value="Active">Đang làm việc chính thức</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Phòng ban</label>
                        <select name="department_id" class="form-control">
                            <option value="">-- Chưa phân bổ --</option>
                            <?php foreach ($departments as $d): ?>
                                <option value="<?= $d->id ?>"><?= htmlspecialchars($d->dept_name) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group col-md-6">
                        <label>Dự án hiện tại</label>
                        <select name="current_project_id" class="form-control">
                            <option value="">-- Chưa phân bổ --</option>
                            <?php foreach ($projects as $p): ?>
                                <option value="<?= $p->id ?>"><?= htmlspecialchars($p->project_name) ?></option>
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
                                <option value="<?= $pos->id ?>"><?= htmlspecialchars($pos->pos_title) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label>Ngày gia nhập</label>
                        <input type="date" name="join_date" class="form-control">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Ngày ký HĐ chính thức</label>
                        <input type="date" name="official_date" class="form-control">
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
                        <input type="text" name="passport_number" class="form-control">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Số Visa</label>
                        <input type="text" name="visa_number" class="form-control">
                    </div>
                    <div class="form-group col-md-6">
                        <label>Ngày hết hạn Visa</label>
                        <input type="date" name="visa_expiry" class="form-control">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Số Giấy phép lao động (Work Permit)</label>
                        <input type="text" name="work_permit_number" class="form-control">
                    </div>
                    <div class="form-group col-md-6">
                        <label>Hạn Giấy phép LĐ</label>
                        <input type="date" name="work_permit_expiry" class="form-control">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Số Thẻ tạm trú (TRC)</label>
                        <input type="text" name="trc_number" class="form-control">
                    </div>
                    <div class="form-group col-md-6">
                        <label>Hạn Thẻ tạm trú</label>
                        <input type="date" name="trc_expiry" class="form-control">
                    </div>
                </div>
            </div>

            <!-- TAB 5: TRÌNH ĐỘ & HSE -->
            <div class="tab-content" id="tab-hse">
                <div class="form-group">
                    <label>Trình độ chuyên môn cao nhất</label>
                    <input type="text" name="highest_degree" class="form-control" placeholder="Đại học Bách Khoa - Cơ điện tử">
                </div>
                <div class="alert alert-info">
                    <i class="fas fa-certificate"></i> Lưu ý: Bạn có thể cập nhật chi tiết từng Chứng chỉ an toàn nhóm 3, Chứng chỉ hàn 6G... sau khi tạo xong hồ sơ trong trang "Chi tiết nhân sự".
                </div>
                <div class="form-group">
                    <label>Ghi chú thêm về năng lực (BIM, Tiếng Hàn...)</label>
                    <textarea name="notes" class="form-control" rows="4"></textarea>
                </div>
            </div>

            <!-- TAB 6: FILE ĐÍNH KÈM -->
            <div class="tab-content" id="tab-files">
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Ảnh đại diện / Thẻ nhân viên (Tỷ lệ 3x4 hoặc 4x6)</label>
                        <div class="file-upload-wrapper">
                            <input type="file" name="avatar" id="avatarInput" class="file-input" accept="image/*">
                            <div class="preview-box" id="avatarPreview">
                                <i class="fas fa-camera text-muted fa-3x"></i>
                                <span class="d-block mt-2 text-muted">Click hoặc kéo thả ảnh</span>
                            </div>
                        </div>
                    </div>
                    <div class="form-group col-md-6">
                        <label>Bản scan CCCD / Bằng cấp (PDF/Image)</label>
                        <div class="file-upload-wrapper">
                            <input type="file" name="cv_file" class="form-control" style="padding-top: 10px;">
                            <div class="mt-2 text-muted small">
                                Hệ thống sẽ tự động lưu trữ an toàn trong kho dữ liệu nhân sự số hóa.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Nút Lưu -->
            <div class="form-actions mt-4 border-top pt-4 text-right">
                <a href="<?= BASE_URL ?>/employee" class="btn btn-ghost">Hủy</a>
                <button type="submit" class="btn btn-primary" id="btnSubmit">
                    <i class="fas fa-save"></i> Hoàn tất tạo Sơ yếu lý lịch
                </button>
            </div>

        </form>
    </div>
</div>

<style>
/* Layout Form */
.form-row { display: flex; flex-wrap: wrap; margin-left: -10px; margin-right: -10px; }
.col-md-6 { width: 50%; padding: 0 10px; }
.col-md-4 { width: 33.333%; padding: 0 10px; }
.mb-4 { margin-bottom: 1.5rem; }
.mt-4 { margin-top: 1.5rem; }
.mt-2 { margin-top: 0.5rem; }
.pt-4 { padding-top: 1.5rem; }
.text-right { text-align: right; }
.border-top { border-top: 1px solid var(--border); }
.d-block { display: block; }

/* Custom Tabs */
.nav-tabs { 
    display: flex; list-style: none; padding: 0; margin: 0 0 24px 0; 
    border-bottom: 2px solid var(--border);
}
.tab-link { 
    padding: 12px 20px; font-weight: 600; color: var(--text-secondary); 
    cursor: pointer; transition: all 0.2s var(--ease);
    border-bottom: 2px solid transparent; margin-bottom: -2px;
}
.tab-link:hover { color: var(--primary-light); }
.tab-link.active { 
    color: var(--primary); border-bottom-color: var(--primary); 
    background: linear-gradient(to top, rgba(59,130,246,0.1), transparent);
}
.tab-link i { margin-right: 8px; }
.tab-content { display: none; animation: fadeIn 0.3s var(--ease); }
.tab-content.active { display: block; }

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(5px); }
    to { opacity: 1; transform: translateY(0); }
}

/* File Upload Preview Box */
.file-upload-wrapper { position: relative; }
.file-input { 
    position: absolute; top: 0; left: 0; width: 100%; height: 100%; 
    opacity: 0; cursor: pointer; z-index: 2;
}
.preview-box {
    width: 100%; height: 200px; border: 2px dashed var(--border);
    border-radius: var(--radius-sm); background: var(--bg-input);
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    position: relative; overflow: hidden; z-index: 1;
}
.preview-box img {
    width: 100%; height: 100%; object-fit: contain; position: absolute; top:0; left:0;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // 1. Tab Switching Logic
    const tabs = document.querySelectorAll('.tab-link');
    const contents = document.querySelectorAll('.tab-content');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            // Xóa active
            tabs.forEach(t => t.classList.remove('active'));
            contents.forEach(c => c.classList.remove('active'));
            // Thêm active
            tab.classList.add('active');
            document.getElementById(tab.getAttribute('data-target')).classList.add('active');
        });
    });

    // 2. Ẩn/Hiện Tab Expat dựa trên Employee Type
    const empTypeSelect = document.getElementById('employee_type');
    const navExpat = document.getElementById('nav-expat');
    
    empTypeSelect.addEventListener('change', (e) => {
        if (e.target.value === 'Expat') {
            navExpat.style.display = 'block';
        } else {
            navExpat.style.display = 'none';
            // Nếu đang đứng ở tab Expat mà đổi type, chuyển về tab Cá nhân
            if (navExpat.classList.contains('active')) {
                tabs[0].click();
            }
        }
    });

    // 3. Image Preview Logic
    const avatarInput = document.getElementById('avatarInput');
    const avatarPreview = document.getElementById('avatarPreview');

    avatarInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                avatarPreview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
            }
            reader.readAsDataURL(this.files[0]);
        }
    });
});
</script>
