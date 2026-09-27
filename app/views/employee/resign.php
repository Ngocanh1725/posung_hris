<?php
/**
 * View: employee/resign.php
 */
?>
<div class="panel">
    <div class="panel-header"><h3><i class="fas fa-sign-out-alt"></i> Xử lý Nghỉ việc / Thôi việc</h3></div>
    <div class="panel-body">
        <div class="mb-4">
            <h5>Thông tin Nhân sự</h5>
            <p><strong>Mã NV:</strong> <?= $employee['emp_code'] ?> | <strong>Họ tên:</strong> <?= $employee['full_name'] ?> | <strong>Phòng ban:</strong> <?= $employee['dept_name'] ?? 'Chưa cập nhật' ?></p>
        </div>
        
        <form method="POST" action="<?= BASE_URL ?>/employee/resign/<?= $employee['id'] ?>">
            <input type="hidden" name="employee_id" value="<?= $employee['id'] ?>">
            
            <div class="form-group mb-3">
                <label>Loại nghỉ việc</label>
                <select name="type" class="form-control" required>
                    <option value="Voluntary">Tự nguyện (Xin nghỉ)</option>
                    <option value="Termination">Buộc thôi việc (Sa thải)</option>
                    <option value="Contract_Expired">Hết Hạn Hợp Đồng</option>
                </select>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6 form-group">
                    <label>Ngày nộp đơn / quyết định</label>
                    <input type="date" name="resignation_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="col-md-6 form-group">
                    <label>Ngày làm việc cuối cùng</label>
                    <input type="date" name="last_working_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                </div>
            </div>
            
            <div class="form-group mb-3">
                <label>Lý do nghỉ việc</label>
                <textarea name="reason" class="form-control" rows="3" placeholder="Nhập lý do chi tiết..." required></textarea>
            </div>
            
            <div class="form-group mb-3" style="background:#f8f9fa; padding:15px; border-radius:4px;">
                <label style="display:flex; align-items:center; gap:10px; font-weight:bold; cursor:pointer;">
                    <input type="checkbox" name="asset_returned" value="1">
                    Xác nhận đã bàn giao tài sản và trả hồ sơ gốc
                </label>
            </div>

            <div class="form-group mb-3" style="background:#fee2e2; padding:15px; border-radius:4px; border: 1px solid #fca5a5;">
                <label style="display:flex; align-items:center; gap:10px; font-weight:bold; color:#b91c1c; cursor:pointer;">
                    <input type="checkbox" name="is_hse_violation" value="1">
                    Nghỉ việc do vi phạm An toàn lao động (HSE) nghiêm trọng (Tự động đưa vào Blacklist)
                </label>
            </div>
            
            <div style="text-align:right;">
                <a href="<?= BASE_URL ?>/employee/index" class="btn btn-ghost">Quay lại</a>
                <button type="submit" class="btn btn-danger">Xác nhận Nghỉ việc</button>
            </div>
        </form>
    </div>
</div>
