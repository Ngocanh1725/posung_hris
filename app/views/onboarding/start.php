<!-- ══════════════════════════════════════════════════════════
     POSUNG HRIS – KHỞI TẠO QUY TRÌNH HỘI NHẬP CHO NHÂN VIÊN
     ══════════════════════════════════════════════════════════ -->

<div class="row justify-content-center">
    <div class="col-lg-7 col-md-9">
        <div class="breadcrumb-bar mb-3">
            <a href="<?= BASE_URL ?>/onboarding"><i class="fas fa-user-check"></i> Hội nhập</a>
            <i class="fas fa-chevron-right"></i>
            <span>Khởi tạo Quy trình</span>
        </div>

        <div class="card shadow-sm border-0" style="border-radius: 14px; overflow: hidden; background: var(--bg-card);">
            <div class="card-header p-4 text-white" style="background: linear-gradient(135deg, #4f46e5, #3730a3);">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-white text-primary d-flex align-items-center justify-content-center shadow" style="width: 50px; height: 50px; font-size: 22px;">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-1">Khởi Tạo Quy Trình Hội Nhập (Onboarding)</h4>
                        <p class="mb-0 text-white-50" style="font-size: 13.5px;">Thiết lập danh mục nhiệm vụ hội nhập tiếp nhận nhân sự mới</p>
                    </div>
                </div>
            </div>

            <div class="card-body p-4">
                <!-- Employee Summary Card -->
                <div class="p-3 mb-4 rounded-3 border d-flex align-items-center gap-3" style="background: var(--bg-hover);">
                    <?php 
                    $hasAvatar = !empty($employee->avatar_path) && file_exists(ROOT_PATH . '/public/' . $employee->avatar_path);
                    ?>
                    <?php if ($hasAvatar): ?>
                        <img src="<?= BASE_URL . '/' . $employee->avatar_path ?>" class="rounded-circle shadow-sm" style="width: 54px; height: 54px; object-fit: cover;" alt="">
                    <?php else: ?>
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold fs-4" style="width: 54px; height: 54px;">
                            <?= mb_substr(trim($employee->full_name), 0, 1, 'UTF-8') ?>
                        </div>
                    <?php endif; ?>
                    <div>
                        <h5 class="fw-bold mb-0 text-primary"><?= htmlspecialchars($employee->full_name) ?></h5>
                        <div class="text-muted small">
                            <span class="badge bg-light text-primary border"><?= htmlspecialchars($employee->emp_code) ?></span> • 
                            <?= htmlspecialchars($employee->pos_title ?? 'Nhân viên') ?> • 
                            <strong><?= htmlspecialchars($employee->dept_name ?? 'POSUNG') ?></strong>
                        </div>
                    </div>
                </div>

                <form action="<?= BASE_URL ?>/onboarding/start/<?= $employee->id ?>" method="POST">
                    <input type="hidden" name="_csrf_token" value="<?= Session::getCsrfToken() ?>">

                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            <i class="fas fa-layer-group text-primary me-1"></i> Chọn Mẫu Quy Trình (Template) <span class="text-danger">*</span>
                        </label>
                        <select name="template_id" class="form-select form-select-lg" required style="border-radius: 10px;">
                            <option value="">-- Chọn mẫu phù hợp cho nhân viên --</option>
                            <?php foreach ($templates as $tpl): ?>
                                <option value="<?= $tpl['id'] ?>">
                                    <?= htmlspecialchars($tpl['name']) ?> (<?= $tpl['total_tasks'] ?> nhiệm vụ • <?= $tpl['dept_name'] ?? 'Toàn công ty' ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Các nhiệm vụ tương ứng sẽ được tự động nhân bản và gán hạn hoàn thành cho nhân viên này.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold"><i class="fas fa-calendar-alt text-primary me-1"></i> Ngày Bắt Đầu Đi Làm (Start Date) <span class="text-danger">*</span></label>
                        <input type="date" name="start_date" class="form-control" value="<?= !empty($employee->join_date) ? $employee->join_date : date('Y-m-d') ?>" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold"><i class="fas fa-sticky-note text-primary me-1"></i> Ghi chú phân công / Lưu ý</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Nhập ghi chú hoặc yêu cầu đặc thù (VD: Bổ sung laptop cấu hình cao cho kỹ sư BIM/Thiết kế...)..."></textarea>
                    </div>

                    <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                        <a href="<?= BASE_URL ?>/employee/detail/<?= $employee->id ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Quay lại
                        </a>
                        <button type="submit" class="btn btn-primary btn-lg px-4 shadow-sm" style="border-radius: 10px; font-weight: 700;">
                            <i class="fas fa-check-circle me-1"></i> Kích Hoạt Quy Trình Hội Nhập
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
