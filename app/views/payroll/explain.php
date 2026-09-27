<?php
/**
 * ============================================================
 *  View: payroll/explain.php
 *  Giải trình công lỗi (Quên quẹt thẻ, đi muộn, về sớm)
 * ============================================================
 */
?>

<div class="panel mb-4">
    <div class="panel-header">
        <h3><i class="fas fa-edit"></i> Giải trình công lỗi (Timesheet Explanation)</h3>
    </div>
    <div class="panel-body">
        <form action="<?= BASE_URL ?>/payroll/submit_explanation" method="POST">
            <div class="form-group">
                <label for="work_date">Ngày công lỗi (Work Date):</label>
                <input type="date" name="work_date" id="work_date" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="error_type">Loại lỗi (Error Type):</label>
                <select name="error_type" id="error_type" class="form-control" required>
                    <option value="">-- Chọn loại lỗi --</option>
                    <option value="forget_checkin">Quên Check-in</option>
                    <option value="forget_checkout">Quên Check-out</option>
                    <option value="late">Đi muộn / Về sớm (Do công việc)</option>
                    <option value="system_error">Lỗi thiết bị / Mất mạng</option>
                    <option value="other">Khác (Other)</option>
                </select>
            </div>
            <div class="form-group">
                <label for="actual_time">Thời gian thực tế (Actual Time - nếu có):</label>
                <input type="time" name="actual_time" id="actual_time" class="form-control">
            </div>
            <div class="form-group">
                <label for="reason">Lý do giải trình (Reason / Explanation):</label>
                <textarea name="reason" id="reason" rows="4" class="form-control" required placeholder="Vui lòng nhập lý do cụ thể..."></textarea>
            </div>
            <div class="form-group">
                <label for="attachment">Tài liệu đính kèm (Attachment - nếu có):</label>
                <input type="file" name="attachment" id="attachment" class="form-control-file">
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Gửi giải trình</button>
            <a href="<?= BASE_URL ?>/payroll/timesheet" class="btn btn-secondary">Hủy bỏ</a>
        </form>
    </div>
</div>
