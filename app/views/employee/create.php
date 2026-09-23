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
            <input type="hidden" name="_csrf_token" value="<?= Session::generateCsrfToken() ?>">
            
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
                                <option value="<?= $d['id'] ?>"><?= h($d['dept_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group col-md-6">
                        <label>Dự án hiện tại</label>
                        <select name="current_project_id" class="form-control">
                            <option value="">-- Chưa phân bổ --</option>
                            <?php foreach ($projects as $p): ?>
                                <option value="<?= $p['id'] ?>"><?= h($p['project_name']) ?></option>
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
                                <option value="<?= $pos['id'] ?>"><?= h($pos['pos_title']) ?></option>
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



<script>
(function() {
    // 1. Tab Switching Logic
    const tabs = document.querySelectorAll('.tab-link');
    const contents = document.querySelectorAll('.tab-content');

    if (tabs.length > 0) {
        tabs.forEach(tab => {
            tab.addEventListener('click', function() {
                // Xóa active
                tabs.forEach(t => t.classList.remove('active'));
                contents.forEach(c => c.classList.remove('active'));
                
                // Thêm active
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

    // 2. Ẩn/Hiện Tab Expat dựa trên Employee Type
    const empTypeSelect = document.getElementById('employee_type');
    const navExpat = document.getElementById('nav-expat');
    
    if (empTypeSelect && navExpat) {
        empTypeSelect.addEventListener('change', (e) => {
            if (e.target.value === 'Expat') {
                navExpat.style.display = 'flex';
            } else {
                navExpat.style.display = 'none';
                // Nếu đang đứng ở tab Expat mà đổi type, chuyển về tab Cá nhân
                if (navExpat.classList.contains('active') && tabs.length > 0) {
                    tabs[0].click();
                }
            }
        });
    }

    // 3. Image Preview Logic
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
