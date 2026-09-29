<?php
/**
 * ============================================================
 *  POSUNG HRIS – View: offboarding/index.php
 *  Dashboard Quy trình Thôi việc Đa phòng ban (Offboarding)
 * ============================================================
 */
?>

<div class="offboarding-dashboard">
    <!-- Header Page -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h3 class="fw-bold mb-1 text-dark">
                <i class="fa-solid fa-user-minus text-danger me-2"></i>Quy trình Thôi việc (Offboarding)
            </h3>
            <p class="text-muted small mb-0">Quản lý và theo dõi quy trình bàn giao đa phòng ban: IT, HR, Finance, HSE, Admin</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>/offboarding/templates" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm">
                <i class="fa-solid fa-sliders me-1"></i> Mẫu quy trình
            </a>
            <button type="button" class="btn btn-danger btn-sm rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#startOffboardingModal">
                <i class="fa-solid fa-plus me-1"></i> Khởi tạo Thôi việc
            </button>
        </div>
    </div>

    <!-- Alert Flash Messages -->
    <?php if ($flash = Session::getFlash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i><?= h($flash) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if ($flash = Session::getFlash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
            <i class="fa-solid fa-triangle-exclamation me-2"></i><?= h($flash) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Stat Cards -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white border-start border-4 border-primary">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold">Tổng số ca thôi việc</div>
                        <h3 class="fw-bold text-dark mt-1 mb-0"><?= number_format($stats['total_cases']) ?></h3>
                    </div>
                    <div class="bg-primary-subtle text-primary rounded-4 p-3 fs-4">
                        <i class="fa-solid fa-folder-open"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white border-start border-4 border-warning">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold">Đang xử lý checklist</div>
                        <h3 class="fw-bold text-warning mt-1 mb-0"><?= number_format($stats['in_progress_cases']) ?></h3>
                    </div>
                    <div class="bg-warning-subtle text-warning rounded-4 p-3 fs-4">
                        <i class="fa-solid fa-spinner fa-spin-pulse"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white border-start border-4 border-success">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold">Đã hoàn tất bàn giao</div>
                        <h3 class="fw-bold text-success mt-1 mb-0"><?= number_format($stats['completed_cases']) ?></h3>
                    </div>
                    <div class="bg-success-subtle text-success rounded-4 p-3 fs-4">
                        <i class="fa-solid fa-clipboard-check"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white border-start border-4 border-danger">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold">Hoàn tất trong tháng</div>
                        <h3 class="fw-bold text-danger mt-1 mb-0"><?= number_format($stats['completed_this_month']) ?></h3>
                    </div>
                    <div class="bg-danger-subtle text-danger rounded-4 p-3 fs-4">
                        <i class="fa-solid fa-calendar-check"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
        <div class="card-body p-3">
            <form action="<?= BASE_URL ?>/offboarding" method="GET" class="row g-2 align-items-center">
                <div class="col-md-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                        <input type="text" name="search" class="form-control bg-light border-start-0" 
                               placeholder="Tìm theo tên, mã NV, SĐT..." value="<?= h($filters['search']) ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select form-select-sm bg-light">
                        <option value="">-- Tất cả trạng thái --</option>
                        <option value="InProgress" <?= $filters['status'] === 'InProgress' ? 'selected' : '' ?>>⏳ Đang xử lý (InProgress)</option>
                        <option value="Completed" <?= $filters['status'] === 'Completed' ? 'selected' : '' ?>>✅ Đã hoàn tất (Completed)</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="department_id" class="form-select form-select-sm bg-light">
                        <option value="">-- Tất cả phòng ban --</option>
                        <?php foreach ($departments as $d): ?>
                            <option value="<?= $d['id'] ?>" <?= (int)$filters['department_id'] === (int)$d['id'] ? 'selected' : '' ?>>
                                <?= h($d['dept_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="reason" class="form-select form-select-sm bg-light">
                        <option value="">-- Lý do nghỉ --</option>
                        <option value="Resign" <?= $filters['reason'] === 'Resign' ? 'selected' : '' ?>>Thôi việc tự nguyện</option>
                        <option value="Terminate" <?= $filters['reason'] === 'Terminate' ? 'selected' : '' ?>>Chấm dứt HĐ / Sa thải</option>
                        <option value="Contract_End" <?= $filters['reason'] === 'Contract_End' ? 'selected' : '' ?>>Hết hạn HĐLĐ</option>
                        <option value="Retirement" <?= $filters['reason'] === 'Retirement' ? 'selected' : '' ?>>Nghỉ hưu theo chế độ</option>
                    </select>
                </div>
                <div class="col-md-1 d-flex gap-1">
                    <button type="submit" class="btn btn-primary btn-sm rounded-pill w-100 shadow-sm" title="Lọc dữ liệu">
                        <i class="fa-solid fa-filter"></i> Lọc
                    </button>
                    <?php if (!empty($filters['search']) || !empty($filters['status']) || !empty($filters['department_id']) || !empty($filters['reason'])): ?>
                        <a href="<?= BASE_URL ?>/offboarding" class="btn btn-outline-secondary btn-sm rounded-pill" title="Xóa lọc">
                            <i class="fa-solid fa-rotate-left"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Offboarding List Table -->
    <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
        <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-dark">
                <i class="fa-solid fa-list-check text-primary me-2"></i>Danh sách Hồ sơ Thôi việc (<?= count($offboardings) ?> hồ sơ)
            </h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted small text-uppercase">
                    <tr>
                        <th class="ps-3" style="width: 60px;">#ID</th>
                        <th>Nhân sự</th>
                        <th>Phòng ban / Chức vụ</th>
                        <th>Mẫu quy trình</th>
                        <th>Lý do</th>
                        <th>Ngày làm việc cuối</th>
                        <th style="min-width: 170px;">Tiến độ bàn giao</th>
                        <th class="text-center">Trạng thái</th>
                        <th class="text-end pe-3">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($offboardings)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-folder-open fa-3x text-secondary opacity-50 mb-3 d-block"></i>
                                <div class="fw-semibold">Không tìm thấy hồ sơ thôi việc nào</div>
                                <small>Bấm "Khởi tạo Thôi việc" để bắt đầu quy trình cho nhân viên.</small>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($offboardings as $item): ?>
                            <tr>
                                <td class="ps-3 font-monospace small text-muted">#<?= $item['id'] ?></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle bg-light border text-primary fw-bold d-flex align-items-center justify-content-center" 
                                             style="width: 38px; height: 38px; font-size: 0.9rem;">
                                            <?= strtoupper(mb_substr($item['employee_name'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <a href="<?= BASE_URL ?>/offboarding/detail/<?= $item['id'] ?>" class="fw-bold text-dark text-decoration-none">
                                                <?= h($item['employee_name']) ?>
                                            </a>
                                            <div class="font-monospace text-muted small"><?= h($item['emp_code']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-semibold small"><?= h($item['dept_name'] ?? 'Chưa phân bổ') ?></div>
                                    <div class="text-muted small"><?= h($item['pos_name'] ?? 'N/A') ?></div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border small">
                                        <i class="fa-solid fa-diagram-project text-secondary me-1"></i><?= h($item['template_name']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php 
                                    $reasonBadge = match($item['reason']) {
                                        'Resign'       => '<span class="badge bg-secondary-subtle text-secondary border">Thôi việc</span>',
                                        'Terminate'    => '<span class="badge bg-danger-subtle text-danger border">Chấm dứt / Sa thải</span>',
                                        'Contract_End' => '<span class="badge bg-warning-subtle text-warning border">Hết hạn HĐ</span>',
                                        'Retirement'   => '<span class="badge bg-info-subtle text-info border">Nghỉ hưu</span>',
                                        default        => '<span class="badge bg-light text-dark">' . h($item['reason']) . '</span>'
                                    };
                                    echo $reasonBadge;
                                    ?>
                                </td>
                                <td>
                                    <div class="small fw-semibold"><i class="fa-regular fa-calendar text-muted me-1"></i><?= date('d/m/Y', strtotime($item['last_working_day'])) ?></div>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-between align-items-center mb-1 small">
                                        <span class="fw-bold"><?= $item['progress_percent'] ?>%</span>
                                        <span class="text-muted"><?= ((int)$item['done_items'] + (int)$item['na_items']) ?>/<?= $item['total_items'] ?> task</span>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar <?= $item['progress_percent'] === 100 ? 'bg-success' : 'bg-primary' ?>" 
                                             role="progressbar" style="width: <?= $item['progress_percent'] ?>%"></div>
                                    </div>
                                    <?php if ($item['pending_blocking_items'] > 0): ?>
                                        <small class="text-danger d-block mt-1" style="font-size: 0.72rem;">
                                            <i class="fa-solid fa-lock me-1"></i>Còn <?= $item['pending_blocking_items'] ?> mục bắt buộc
                                        </small>
                                    <?php else: ?>
                                        <small class="text-success d-block mt-1" style="font-size: 0.72rem;">
                                            <i class="fa-solid fa-circle-check me-1"></i>Đã đủ điều kiện chốt
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($item['status'] === 'Completed'): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill small">
                                            <i class="fa-solid fa-check me-1"></i>Hoàn tất
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1 rounded-pill small">
                                            <i class="fa-solid fa-spinner fa-spin me-1"></i>Đang xử lý
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-3">
                                    <a href="<?= BASE_URL ?>/offboarding/detail/<?= $item['id'] ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3 shadow-sm">
                                        <i class="fa-solid fa-arrow-right me-1"></i>Chi tiết
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Khởi tạo Thôi việc -->
<div class="modal fade" id="startOffboardingModal" tabindex="-1" aria-labelledby="startModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form action="<?= BASE_URL ?>/offboarding/start" method="POST">
                <div class="modal-header border-bottom bg-light rounded-top-4">
                    <h5 class="modal-title fw-bold text-dark" id="startModalLabel">
                        <i class="fa-solid fa-user-minus text-danger me-2"></i>Khởi tạo Quy trình Thôi việc
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted">Chọn nhân sự <span class="text-danger">*</span></label>
                        <select name="employee_id" class="form-select rounded-3" required id="modalEmployeeSelect">
                            <option value="">-- Chọn nhân viên thôi việc --</option>
                            <?php foreach ($activeEmployees as $emp): ?>
                                <option value="<?= $emp->id ?>">
                                    <?= h($emp->emp_code) ?> - <?= h($emp->full_name) ?> (<?= h($emp->dept_name ?? 'N/A') ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted">Mẫu quy trình bàn giao <span class="text-danger">*</span></label>
                        <select name="template_id" class="form-select rounded-3" required>
                            <?php foreach ($templates as $tmpl): ?>
                                <option value="<?= $tmpl['id'] ?>">
                                    <?= h($tmpl['name']) ?> (<?= $tmpl['total_tasks'] ?> nhiệm vụ)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold small text-muted">Lý do nghỉ việc <span class="text-danger">*</span></label>
                            <select name="reason" class="form-select rounded-3" required>
                                <option value="Resign">Thôi việc tự nguyện</option>
                                <option value="Terminate">Chấm dứt HĐ / Sa thải</option>
                                <option value="Contract_End">Hết hạn hợp đồng</option>
                                <option value="Retirement">Nghỉ hưu theo chế độ</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold small text-muted">Ngày làm việc cuối <span class="text-danger">*</span></label>
                            <input type="date" name="last_working_day" class="form-control rounded-3" value="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted">Ghi chú thêm</label>
                        <textarea name="notes" class="form-control rounded-3" rows="3" placeholder="Nhập lý do chi tiết hoặc các chỉ đạo bàn giao đặc biệt..."></textarea>
                    </div>

                    <div class="alert alert-info py-2 px-3 small rounded-3 border-0">
                        <i class="fa-solid fa-circle-info me-1"></i> Sau khi tạo, hệ thống sẽ tự động gửi checklist tới các phòng ban <strong>IT, HR, Finance, HSE, Admin</strong> để xử lý.
                    </div>
                </div>
                <div class="modal-footer border-top bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Hủy bỏ</button>
                    <button type="submit" class="btn btn-danger btn-sm rounded-pill px-4 shadow-sm">
                        <i class="fa-solid fa-check me-1"></i> Bắt đầu Offboarding
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
