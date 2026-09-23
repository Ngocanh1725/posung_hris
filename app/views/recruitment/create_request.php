<?php /** View: recruitment/create_request.php – Form tạo Yêu cầu Tuyển dụng */ ?>

<div class="panel" style="max-width:900px;">
    <div class="panel-header"><h3><i class="fas fa-clipboard-list"></i> Tạo Yêu cầu Tuyển dụng mới</h3></div>
    <div class="panel-body">
        <form action="<?= BASE_URL ?>/recruitment/storeRequest" method="POST">
            <input type="hidden" name="_csrf_token" value="<?= Session::generateCsrfToken() ?>">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                <!-- Phòng ban -->
                <div class="form-group">
                    <label>Phòng ban yêu cầu <span class="text-danger">*</span></label>
                    <select name="department_id" class="form-control" required>
                        <option value="">-- Chọn phòng ban --</option>
                        <?php foreach($departments as $d): ?>
                            <option value="<?= $d['id'] ?>"><?= h($d['dept_code'].' - '.$d['dept_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <!-- Vị trí -->
                <div class="form-group">
                    <label>Vị trí cần tuyển <span class="text-danger">*</span></label>
                    <select name="position_id" class="form-control" required>
                        <option value="">-- Chọn vị trí --</option>
                        <?php foreach($positions as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= h($p['pos_title']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <!-- Dự án -->
                <div class="form-group">
                    <label>Dự án (nếu có)</label>
                    <select name="project_id" class="form-control" id="project_select">
                        <option value="">-- Không chọn --</option>
                        <?php foreach($projects as $proj): ?>
                            <option value="<?= $proj->id ?>" data-budget="<?= $proj->headcount_budget ?? 'Chưa xác định' ?>"><?= h($proj->project_name) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <small id="project_budget_info" class="text-info mt-1" style="display:none;"><i class="fas fa-info-circle"></i> Định biên dự án: <strong id="pb_val">0</strong> người.</small>
                </div>
                <!-- Số lượng -->
                <div class="form-group">
                    <label>Số lượng cần tuyển <span class="text-danger">*</span></label>
                    <input type="number" name="quantity" class="form-control" value="1" min="1" required>
                </div>
                <!-- Lý do -->
                <div class="form-group">
                    <label>Lý do tuyển</label>
                    <select name="reason" class="form-control">
                        <option value="Expansion">Mở rộng đội ngũ</option>
                        <option value="Replacement">Thay thế nhân sự</option>
                        <option value="New_Position">Vị trí mới</option>
                        <option value="Seasonal">Thời vụ / Ngắn hạn</option>
                    </select>
                </div>
                <!-- Độ gấp -->
                <div class="form-group">
                    <label>Mức độ ưu tiên</label>
                    <select name="urgency" class="form-control">
                        <option value="Normal">Bình thường</option>
                        <option value="Urgent">Gấp</option>
                        <option value="Critical">Rất gấp (Critical)</option>
                    </select>
                </div>
                <!-- Mức lương dự kiến -->
                <div class="form-group">
                    <label>Lương từ (VNĐ)</label>
                    <input type="number" name="salary_range_from" class="form-control" placeholder="VD: 8000000">
                </div>
                <div class="form-group">
                    <label>Lương đến (VNĐ)</label>
                    <input type="number" name="salary_range_to" class="form-control" placeholder="VD: 15000000">
                </div>
                <!-- Địa điểm -->
                <div class="form-group">
                    <label>Địa điểm làm việc</label>
                    <input type="text" name="work_location" class="form-control" placeholder="VD: KCN Yên Phong, Bắc Ninh">
                </div>
                <!-- Hạn tuyển -->
                <div class="form-group">
                    <label>Hạn tuyển</label>
                    <input type="date" name="deadline" class="form-control">
                </div>
            </div>

            <!-- Mô tả công việc -->
            <div class="form-group" style="margin-top:16px;">
                <label>Mô tả công việc</label>
                <textarea name="description" class="form-control" rows="4" placeholder="Chi tiết công việc cần tuyển..."></textarea>
            </div>
            <!-- Yêu cầu ứng viên -->
            <div class="form-group" style="margin-top:12px;">
                <label>Yêu cầu ứng viên</label>
                <textarea name="requirements" class="form-control" rows="4" placeholder="Bằng cấp, kinh nghiệm, kỹ năng yêu cầu..."></textarea>
            </div>
            <!-- Quyền lợi -->
            <div class="form-group" style="margin-top:12px;">
                <label>Quyền lợi</label>
                <textarea name="benefits" class="form-control" rows="3" placeholder="BHXH, BHYT, thưởng, phụ cấp..."></textarea>
            </div>
            <!-- Trạng thái -->
            <div class="form-group" style="margin-top:12px;">
                <label>Trạng thái</label>
                <select name="request_status" class="form-control" style="max-width:250px;">
                    <option value="Pending">Chờ phê duyệt</option>
                    <option value="Draft">Lưu nháp</option>
                    <option value="Approved">Đã duyệt (bỏ qua phê duyệt)</option>
                </select>
            </div>
            <!-- Ghi chú -->
            <div class="form-group" style="margin-top:12px;">
                <label>Ghi chú</label>
                <textarea name="notes" class="form-control" rows="2"></textarea>
            </div>

            <div style="margin-top:24px; display:flex; gap:12px;">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Tạo Yêu cầu</button>
                <a href="<?= BASE_URL ?>/recruitment" class="btn btn-ghost">Hủy</a>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('project_select').addEventListener('change', function() {
    let opt = this.options[this.selectedIndex];
    let budget = opt.getAttribute('data-budget');
    let infoDiv = document.getElementById('project_budget_info');
    
    if (this.value && budget) {
        document.getElementById('pb_val').innerText = budget;
        infoDiv.style.display = 'block';
    } else {
        infoDiv.style.display = 'none';
    }
});
</script>
