<?php
/**
 * View: training/edit.php – Form cập nhật thông tin khóa đào tạo
 */
?>

<div style="max-width: 900px; margin: 0 auto;">
    <!-- Breadcrumb & Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <div style="font-size: 13px; color: var(--text-muted); margin-bottom: 4px;">
                <a href="<?= BASE_URL ?>/training" style="color: var(--text-muted); text-decoration: none;">Đào tạo & L&D</a>
                <span style="margin: 0 6px;">/</span>
                <a href="<?= BASE_URL ?>/training/show/<?= $course->id ?>" style="color: var(--text-muted); text-decoration: none;"><?= h($course->course_name) ?></a>
                <span style="margin: 0 6px;">/</span>
                <span style="color: var(--primary); font-weight: 600;">Chỉnh sửa</span>
            </div>
            <h2 style="font-size: 22px; font-weight: 800; color: var(--text-heading); margin: 0;">
                <i class="fas fa-edit text-warning"></i> Chỉnh sửa Khóa Đào tạo
            </h2>
        </div>
        <div>
            <a href="<?= BASE_URL ?>/training/show/<?= $course->id ?>" class="btn btn-ghost">
                <i class="fas fa-arrow-left"></i> Quay lại chi tiết
            </a>
        </div>
    </div>

    <!-- Main Form Panel -->
    <div class="panel" style="border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.05); border-radius: var(--radius-lg);">
        <div class="panel-header" style="background: var(--bg-card); border-bottom: 1px solid var(--border); padding: 18px 24px;">
            <h3 style="font-size: 16px; font-weight: 700; margin: 0; color: var(--text-heading);">
                <i class="fas fa-chalkboard-teacher text-primary"></i> Cập nhật Thông tin Khóa học: <?= h($course->course_name) ?>
            </h3>
        </div>
        <div class="panel-body" style="padding: 24px;">
            <form method="POST" action="<?= BASE_URL ?>/training/update/<?= $course->id ?>">
                <input type="hidden" name="_csrf_token" value="<?= Session::generateCsrfToken() ?>">

                <!-- PHẦN 1: THÔNG TIN CƠ BẢN -->
                <div class="row mb-4">
                    <div class="col-md-12 mb-3">
                        <label class="form-label" style="font-weight: 700;">
                            Tên khóa đào tạo <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="course_name" class="form-control" value="<?= h($course->course_name) ?>" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label" style="font-weight: 600;">
                            Giảng viên / Đơn vị tổ chức
                        </label>
                        <input type="text" name="provider" class="form-control" value="<?= h($course->provider) ?>" placeholder="VD: Trung tâm Kiểm định Vinacontrol / Nội bộ POSUNG">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label" style="font-weight: 600;">
                            Phòng ban phụ trách / Áp dụng
                        </label>
                        <select name="department_id" class="form-control">
                            <option value="">-- Toàn công ty / Không cố định --</option>
                            <?php foreach($departments as $d): ?>
                                <option value="<?= $d['id'] ?>" <?= ($course->department_id == $d['id']) ? 'selected' : '' ?>>
                                    <?= h($d['dept_name']) ?> (<?= h($d['dept_code']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label class="form-label" style="font-weight: 600;">
                            Địa điểm / Hình thức tổ chức
                        </label>
                        <input type="text" name="location" class="form-control" value="<?= h($course->location) ?>" placeholder="VD: Phòng họp Tầng 3 Trụ sở hoặc Online qua MS Teams">
                    </div>
                </div>

                <!-- PHẦN 2: THỜI GIAN & CHI PHÍ -->
                <div style="border-top: 1px dashed var(--border); padding-top: 20px;" class="mb-4">
                    <h4 style="font-size: 14px; font-weight: 700; color: var(--text-heading); margin-bottom: 16px;">
                        <i class="far fa-clock text-primary"></i> Thời gian, Chi phí & Quy mô
                    </h4>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" style="font-weight: 600;">
                                Ngày bắt đầu <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="start_date" class="form-control" value="<?= $course->start_date ?>" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label" style="font-weight: 600;">
                                Ngày kết thúc dự kiến
                            </label>
                            <input type="date" name="end_date" class="form-control" value="<?= $course->end_date ?>">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label" style="font-weight: 600;">
                                Chi phí dự toán (VNĐ)
                            </label>
                            <input type="number" name="cost" class="form-control" value="<?= (float)$course->cost ?>" min="0" step="10000">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label" style="font-weight: 600;">
                                Số học viên tối đa (0 = Không giới hạn)
                            </label>
                            <input type="number" name="max_participants" class="form-control" value="<?= (int)$course->max_participants ?>" min="0">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label" style="font-weight: 600;">
                                Trạng thái khóa học
                            </label>
                            <select name="status" class="form-control">
                                <option value="Planning" <?= $course->status === 'Planning' ? 'selected' : '' ?>>Lập kế hoạch (Planning)</option>
                                <option value="In_Progress" <?= $course->status === 'In_Progress' ? 'selected' : '' ?>>Đang diễn ra (In Progress)</option>
                                <option value="Completed" <?= $course->status === 'Completed' ? 'selected' : '' ?>>Đã hoàn thành (Completed)</option>
                                <option value="Cancelled" <?= $course->status === 'Cancelled' ? 'selected' : '' ?>>Đã hủy (Cancelled)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- PHẦN 3: MÔ TẢ & ĐỀ CƯƠNG -->
                <div style="border-top: 1px dashed var(--border); padding-top: 20px;" class="mb-4">
                    <h4 style="font-size: 14px; font-weight: 700; color: var(--text-heading); margin-bottom: 16px;">
                        <i class="fas fa-file-alt text-primary"></i> Mô tả chi tiết & Đề cương đào tạo
                    </h4>

                    <div class="form-group mb-0">
                        <textarea name="description" class="form-control" rows="5" placeholder="Ghi chú mục tiêu đào tạo, đề cương khóa học, điều kiện được cấp chứng chỉ hoặc tài liệu cần chuẩn bị..."><?= h($course->description) ?></textarea>
                    </div>
                </div>

                <!-- BUTTONS -->
                <div class="d-flex justify-content-end gap-2" style="border-top: 1px solid var(--border); padding-top: 20px;">
                    <a href="<?= BASE_URL ?>/training/show/<?= $course->id ?>" class="btn btn-ghost">
                        Hủy bỏ
                    </a>
                    <button type="submit" class="btn btn-primary" style="padding: 10px 24px; font-weight: 700;">
                        <i class="fas fa-save"></i> Cập nhật Khóa học
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
