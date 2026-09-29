<?php
/**
 * ============================================================
 *  POSUNG HRIS – View: offboarding/start.php
 *  Màn hình Khởi tạo Quy trình Thôi việc cho Nhân viên
 * ============================================================
 */
?>

<div class="offboarding-start-page" style="max-width: 800px; margin: 0 auto;">
    <!-- Breadcrumb & Back -->
    <div class="mb-3">
        <a href="<?= BASE_URL ?>/offboarding" class="text-decoration-none text-muted small">
            <i class="fa-solid fa-arrow-left me-1"></i> Quay lại Danh sách Thôi việc
        </a>
    </div>

    <!-- Header Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
        <div class="card-body p-4">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-4 bg-danger-subtle text-danger p-3 fs-3">
                    <i class="fa-solid fa-user-minus"></i>
                </div>
                <div>
                    <h4 class="fw-bold mb-1 text-dark">Khởi tạo Quy trình Thôi việc (Offboarding)</h4>
                    <p class="text-muted small mb-0">Thiết lập tiến trình bàn giao đa bộ phận và kích hoạt checklist kiểm soát</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Start Form Card -->
    <div class="card border-0 shadow-sm rounded-4 bg-white">
        <form action="<?= BASE_URL ?>/offboarding/start" method="POST">
            <div class="card-body p-4">
                <?php if ($employee): ?>
                    <!-- Thông tin nhân viên đã chọn -->
                    <input type="hidden" name="employee_id" value="<?= $employee->id ?>">
                    <div class="alert alert-light border rounded-4 p-3 mb-4">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle bg-primary text-white fw-bold d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; font-size: 1.2rem;">
                                <?= strtoupper(mb_substr($employee->full_name, 0, 1)) ?>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-0 text-dark"><?= h($employee->full_name) ?> <span class="badge bg-secondary font-monospace"><?= h($employee->emp_code) ?></span></h5>
                                <div class="text-muted small mt-1">
                                    <span class="me-3"><i class="fa-solid fa-briefcase text-muted me-1"></i><?= h($employee->pos_title ?? 'Chức vụ: N/A') ?></span>
                                    <span class="me-3"><i class="fa-solid fa-building text-muted me-1"></i><?= h($employee->dept_name ?? 'Phòng ban: N/A') ?></span>
                                    <span><i class="fa-regular fa-calendar-check text-muted me-1"></i>Vào làm: <?= date('d/m/Y', strtotime($employee->join_date)) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Chọn nhân viên -->
                    <div class="mb-4">
                        <label class="form-label fw-bold text-dark">1. Chọn Nhân viên thôi việc <span class="text-danger">*</span></label>
                        <select name="employee_id" class="form-select form-select-lg rounded-3" required>
                            <option value="">-- Chọn nhân viên từ danh sách --</option>
                            <?php foreach ($employees as $emp): ?>
                                <option value="<?= $emp->id ?>">
                                    <?= h($emp->emp_code) ?> - <?= h($emp->full_name) ?> (<?= h($emp->dept_name ?? 'N/A') ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>

                <!-- Chọn Mẫu Quy Trình -->
                <div class="mb-4">
                    <label class="form-label fw-bold text-dark">2. Mẫu quy trình bàn giao (Template) <span class="text-danger">*</span></label>
                    <div class="row g-3">
                        <?php foreach ($templates as $idx => $tmpl): ?>
                            <div class="col-md-12">
                                <label class="card border rounded-3 p-3 h-100 d-flex flex-row align-items-start gap-3 cursor-pointer hover-shadow" style="cursor: pointer;">
                                    <input type="radio" name="template_id" value="<?= $tmpl['id'] ?>" class="form-check-input mt-1" <?= $idx === 0 ? 'checked' : '' ?>>
                                    <div class="flex-grow-1">
                                        <div class="fw-bold text-dark"><?= h($tmpl['name']) ?></div>
                                        <div class="text-muted small mt-1"><?= h($tmpl['description']) ?></div>
                                        <div class="mt-2">
                                            <span class="badge bg-light text-dark border small me-2">
                                                <i class="fa-solid fa-list-check text-primary me-1"></i><?= $tmpl['total_tasks'] ?> nhiệm vụ
                                            </span>
                                            <span class="badge bg-danger-subtle text-danger small">
                                                <i class="fa-solid fa-lock me-1"></i><?= $tmpl['blocking_tasks'] ?> mục bắt buộc (Blocking)
                                            </span>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Lý do & Ngày làm việc cuối -->
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-dark">3. Lý do thôi việc <span class="text-danger">*</span></label>
                        <select name="reason" class="form-select rounded-3" required>
                            <option value="Resign">Thôi việc tự nguyện (Resign)</option>
                            <option value="Terminate">Chấm dứt HĐLĐ / Sa thải (Terminate)</option>
                            <option value="Contract_End">Hết hạn hợp đồng không gia hạn (Contract End)</option>
                            <option value="Retirement">Nghỉ hưu theo chế độ (Retirement)</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-dark">4. Ngày làm việc cuối cùng <span class="text-danger">*</span></label>
                        <input type="date" name="last_working_day" class="form-control rounded-3" value="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>

                <!-- Ghi chú / Chỉ đạo thêm -->
                <div class="mb-4">
                    <label class="form-label fw-bold text-dark">5. Ghi chú / Ý kiến lãnh đạo</label>
                    <textarea name="notes" class="form-control rounded-3" rows="3" placeholder="Nhập lý do chi tiết, các khoản cần chú ý thanh toán hoặc bàn giao..."></textarea>
                </div>

                <div class="alert alert-warning rounded-3 border-0 small">
                    <i class="fa-solid fa-triangle-exclamation me-1"></i> <strong>Lưu ý quan trọng:</strong> Hệ thống sẽ <strong>KHÔNG CHO PHÉP</strong> chuyển trạng thái nhân viên sang <em>"Resigned"</em> hoặc <em>"Terminated"</em> nếu các mục bắt buộc (Blocking) chưa được các phòng ban xác nhận <em>"Done"</em>.
                </div>
            </div>

            <div class="card-footer bg-light border-top p-3 d-flex justify-content-between align-items-center rounded-bottom-4">
                <a href="<?= BASE_URL ?>/offboarding" class="btn btn-outline-secondary rounded-pill px-4">Hủy bỏ</a>
                <button type="submit" class="btn btn-danger rounded-pill px-4 shadow-sm">
                    <i class="fa-solid fa-check me-1"></i> Bắt đầu Quy trình Thôi việc
                </button>
            </div>
        </form>
    </div>
</div>
