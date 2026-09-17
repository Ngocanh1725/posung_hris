<?php /** View: recruitment/add_candidate.php – Form thêm ứng viên */ ?>

<div class="panel" style="max-width:900px;">
    <div class="panel-header"><h3><i class="fas fa-user-plus"></i> Thêm Hồ sơ Ứng viên</h3></div>
    <div class="panel-body">
        <form action="<?= BASE_URL ?>/recruitment/storeCandidate" method="POST" enctype="multipart/form-data">
            <!-- Yêu cầu tuyển dụng -->
            <div class="form-group mb-3">
                <label>Gắn với Yêu cầu Tuyển dụng</label>
                <select name="request_id" class="form-control">
                    <option value="">-- Không gắn (Ứng viên tự do) --</option>
                    <?php foreach($requests as $rq): ?>
                        <option value="<?= $rq->id ?>" <?= $preselectedRequest == $rq->id ? 'selected' : '' ?>>
                            <?= htmlspecialchars($rq->request_code . ' – ' . ($rq->pos_title ?? 'N/A')) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <h4 style="margin:20px 0 12px; font-size:0.95rem; color:var(--text-heading); border-bottom:1px solid var(--border); padding-bottom:8px;">
                <i class="fas fa-user"></i> Thông tin Cá nhân
            </h4>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                <div class="form-group">
                    <label>Họ và tên <span class="text-danger">*</span></label>
                    <input type="text" name="full_name" class="form-control" required placeholder="Nguyễn Văn A">
                </div>
                <div class="form-group">
                    <label>Giới tính</label>
                    <select name="gender" class="form-control">
                        <option value="Male">Nam</option>
                        <option value="Female">Nữ</option>
                        <option value="Other">Khác</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Ngày sinh</label>
                    <input type="date" name="dob" class="form-control">
                </div>
                <div class="form-group">
                    <label>Số CCCD</label>
                    <input type="text" name="id_card" class="form-control" placeholder="012345678901">
                </div>
                <div class="form-group">
                    <label>Số điện thoại</label>
                    <input type="text" name="phone" class="form-control" placeholder="0912xxx">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" placeholder="email@domain.com">
                </div>
            </div>
            <div class="form-group" style="margin-top:12px;">
                <label>Địa chỉ</label>
                <input type="text" name="address" class="form-control" placeholder="Địa chỉ hiện tại">
            </div>

            <h4 style="margin:24px 0 12px; font-size:0.95rem; color:var(--text-heading); border-bottom:1px solid var(--border); padding-bottom:8px;">
                <i class="fas fa-graduation-cap"></i> Học vấn & Kinh nghiệm
            </h4>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                <div class="form-group">
                    <label>Bằng cấp cao nhất</label>
                    <select name="highest_degree" class="form-control">
                        <option value="">-- Chọn --</option>
                        <option value="THPT">THPT</option>
                        <option value="Trung cấp">Trung cấp</option>
                        <option value="Cao đẳng">Cao đẳng</option>
                        <option value="Đại học">Đại học</option>
                        <option value="Thạc sĩ">Thạc sĩ</option>
                        <option value="Tiến sĩ">Tiến sĩ</option>
                        <option value="Chứng chỉ nghề">Chứng chỉ nghề</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Chuyên ngành</label>
                    <input type="text" name="major" class="form-control" placeholder="VD: Kỹ thuật Điện">
                </div>
                <div class="form-group">
                    <label>Trường đào tạo</label>
                    <input type="text" name="university" class="form-control" placeholder="VD: ĐH Bách Khoa HN">
                </div>
                <div class="form-group">
                    <label>Năm tốt nghiệp</label>
                    <input type="number" name="graduation_year" class="form-control" placeholder="2020" min="1980" max="2030">
                </div>
                <div class="form-group">
                    <label>Số năm kinh nghiệm</label>
                    <input type="number" name="years_experience" class="form-control" value="0" min="0">
                </div>
                <div class="form-group">
                    <label>Công ty hiện tại</label>
                    <input type="text" name="current_company" class="form-control">
                </div>
                <div class="form-group">
                    <label>Vị trí hiện tại</label>
                    <input type="text" name="current_position" class="form-control">
                </div>
                <div class="form-group">
                    <label>Mức lương mong muốn (VNĐ)</label>
                    <input type="number" name="expected_salary" class="form-control" placeholder="15000000">
                </div>
            </div>

            <h4 style="margin:24px 0 12px; font-size:0.95rem; color:var(--text-heading); border-bottom:1px solid var(--border); padding-bottom:8px;">
                <i class="fas fa-star"></i> Kỹ năng & Bổ sung
            </h4>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                <div class="form-group">
                    <label>Kỹ năng nổi bật</label>
                    <textarea name="skills" class="form-control" rows="3" placeholder="VD: Hàn 3G/6G, AutoCAD, Revit BIM..."></textarea>
                </div>
                <div class="form-group">
                    <label>Ngoại ngữ</label>
                    <input type="text" name="languages" class="form-control" placeholder="VD: Tiếng Hàn TOPIK 3, TOEIC 550">
                </div>
                <div class="form-group">
                    <label>Nguồn ứng tuyển</label>
                    <select name="source" class="form-control">
                        <option value="">-- Chọn --</option>
                        <option value="Website">Website tuyển dụng</option>
                        <option value="Giới thiệu">Giới thiệu nội bộ</option>
                        <option value="Facebook">Facebook / MXH</option>
                        <option value="Headhunt">Headhunt</option>
                        <option value="Hội chợ việc làm">Hội chợ việc làm</option>
                        <option value="Tự liên hệ">Tự liên hệ</option>
                        <option value="Khác">Khác</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>File CV (PDF/DOC)</label>
                    <input type="file" name="cv_file" class="form-control" accept=".pdf,.doc,.docx">
                </div>
            </div>
            <div class="form-group" style="margin-top:12px;">
                <label>Ghi chú</label>
                <textarea name="notes" class="form-control" rows="2"></textarea>
            </div>

            <div style="margin-top:24px; display:flex; gap:12px;">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Thêm Ứng viên</button>
                <a href="<?= BASE_URL ?>/recruitment/candidates" class="btn btn-ghost">Hủy</a>
            </div>
        </form>
    </div>
</div>
