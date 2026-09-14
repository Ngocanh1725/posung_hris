<?php
/**
 * ============================================================
 *  View: employee/offboard.php
 *  Biên bản Bàn giao Thôi việc / Hưu trí
 * ============================================================
 */
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="panel mb-4">
            <div class="panel-header bg-danger text-white">
                <h3><i class="fas fa-sign-out-alt"></i> Biên bản Bàn giao & Thôi việc (Clearance Checklist)</h3>
            </div>
            <div class="panel-body">
                <div class="alert alert-warning">
                    <strong>Đang thao tác:</strong> <?= htmlspecialchars($employee->emp_code) ?> - <?= htmlspecialchars($employee->full_name) ?> 
                    <br>Trạng thái hiện tại: <span class="badge badge-active"><?= $employee->status ?></span>
                </div>

                <form action="<?= BASE_URL ?>/employee/offboard/<?= $employee->id ?>" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn chốt biên bản và khóa tài khoản nhân sự này?');">
                    
                    <h5 class="mt-4 mb-3"><i class="fas fa-tasks text-primary"></i> 1. Đánh dấu các hạng mục đã thu hồi</h5>
                    <div class="list-group mb-4">
                        <label class="list-group-item d-flex gap-3">
                            <input class="form-check-input flex-shrink-0" type="checkbox" name="id_card_returned" value="1" style="font-size: 1.375em;">
                            <span class="pt-1 form-checked-content ml-2">
                                <strong>Thẻ nhân viên & Thẻ từ ra vào công trường</strong>
                                <small class="d-block text-muted">Bao gồm thẻ chấm công, thẻ ra vào cổng Samsung/Amkor.</small>
                            </span>
                        </label>
                        <label class="list-group-item d-flex gap-3">
                            <input class="form-check-input flex-shrink-0" type="checkbox" name="ppe_returned" value="1" style="font-size: 1.375em;">
                            <span class="pt-1 form-checked-content ml-2">
                                <strong>Đồ bảo hộ lao động (PPE)</strong>
                                <small class="d-block text-muted">Mũ bảo hộ, áo phản quang, dây đai an toàn toàn thân, giày bảo hộ.</small>
                            </span>
                        </label>
                        <label class="list-group-item d-flex gap-3">
                            <input class="form-check-input flex-shrink-0" type="checkbox" name="tools_returned" value="1" style="font-size: 1.375em;">
                            <span class="pt-1 form-checked-content ml-2">
                                <strong>Máy móc & Công cụ thi công</strong>
                                <small class="d-block text-muted">Máy hàn, máy mài, đồ nghề cá nhân mượn từ thủ kho.</small>
                            </span>
                        </label>
                        <label class="list-group-item d-flex gap-3">
                            <input class="form-check-input flex-shrink-0" type="checkbox" name="laptop_returned" value="1" style="font-size: 1.375em;">
                            <span class="pt-1 form-checked-content ml-2">
                                <strong>Laptop / PC / Thiết bị văn phòng</strong>
                                <small class="d-block text-muted">Chỉ áp dụng cho Kỹ sư và khối Văn phòng.</small>
                            </span>
                        </label>
                    </div>

                    <h5 class="mt-4 mb-3"><i class="fas fa-cog text-primary"></i> 2. Xác nhận Trạng thái mới</h5>
                    <div class="form-group mb-3">
                        <select name="new_status" class="form-control" required>
                            <option value="Resigned">Thôi việc (Resigned)</option>
                            <option value="Retired">Nghỉ hưu (Retired)</option>
                        </select>
                        <small class="text-danger mt-1 d-block"><i class="fas fa-exclamation-triangle"></i> Lưu ý: Khi chuyển trạng thái, nhân sự này sẽ không thể chấm công hay được tính lương vào kỳ sau, nhưng dữ liệu lịch sử vẫn được giữ nguyên.</small>
                    </div>

                    <div class="form-group mb-4">
                        <label>Ghi chú thêm (Lý do nghỉ, nợ đọng nếu có)</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Nhập ghi chú..."></textarea>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="<?= BASE_URL ?>/employee/view/<?= $employee->id ?>" class="btn btn-secondary">Hủy bỏ</a>
                        <button type="submit" class="btn btn-danger"><i class="fas fa-lock"></i> Chốt Bàn Giao & Khóa NS</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>
