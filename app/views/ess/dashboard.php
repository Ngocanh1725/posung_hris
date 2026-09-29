<?php
require_once APP_ROOT . '/views/ess/layout/header.php';

$avatarSrc = !empty($employee['avatar_path']) && file_exists(APP_ROOT . '/../public/' . $employee['avatar_path'])
    ? BASE_URL . '/' . $employee['avatar_path']
    : null;
$firstLetter = mb_strtoupper(mb_substr($employee['full_name'] ?? 'N', 0, 1, 'UTF-8'), 'UTF-8');
?>

<div class="container-xl">
    
    <!-- ══════════════════════════════════════════════════════
         SECTION 1: HERO PROFILE & CHECK-IN HÔM NAY
         ══════════════════════════════════════════════════════ -->
    <div class="row g-3 mb-4">
        <!-- Thông tin cá nhân cơ bản -->
        <div class="col-lg-8">
            <div class="card-custom h-100 p-4" style="background: linear-gradient(135deg, #ffffff 0%, #f0f7fc 100%);">
                <div class="d-flex flex-column flex-sm-row align-items-center align-items-sm-start gap-3">
                    <?php if ($avatarSrc): ?>
                        <img src="<?= $avatarSrc ?>" alt="<?= htmlspecialchars($employee['full_name']) ?>" 
                             class="rounded-circle shadow-sm" style="width: 80px; height: 80px; object-fit: cover; border: 3px solid #0088cc;">
                    <?php else: ?>
                        <div class="rounded-circle shadow-sm d-flex align-items-center justify-content-center text-white fw-bold fs-2"
                             style="width: 80px; height: 80px; background: linear-gradient(135deg, #0d3c61, #0088cc); border: 3px solid #e0f2fe;">
                            <?= $firstLetter ?>
                        </div>
                    <?php endif; ?>

                    <div class="flex-grow-1 text-center text-sm-start">
                        <div class="d-flex flex-column flex-sm-row align-items-center gap-2 mb-1">
                            <h4 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($employee['full_name'] ?? '') ?></h4>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1">
                                <?= htmlspecialchars($employee['emp_code'] ?? 'NV') ?>
                            </span>
                        </div>
                        <p class="text-muted mb-2 small">
                            <i class="fa-solid fa-briefcase text-secondary me-1"></i>
                            <strong><?= htmlspecialchars($employee['pos_title'] ?? 'Nhân viên') ?></strong>
                            &bull; <?= htmlspecialchars($employee['dept_name'] ?? 'Phòng ban') ?>
                            <?php if (!empty($employee['project_name'])): ?>
                                &bull; <span class="text-primary fw-semibold"><i class="fa-solid fa-building-flag me-1"></i><?= htmlspecialchars($employee['project_name']) ?></span>
                            <?php endif; ?>
                        </p>
                        <div class="d-flex flex-wrap justify-content-center justify-content-sm-start gap-3 small text-secondary">
                            <span><i class="fa-regular fa-calendar-check text-muted me-1"></i> Vào làm: <strong><?= !empty($employee['join_date']) ? date('d/m/Y', strtotime($employee['join_date'])) : '—' ?></strong></span>
                            <span><i class="fa-solid fa-phone text-muted me-1"></i> <strong><?= htmlspecialchars($employee['phone'] ?? 'Chưa cập nhật') ?></strong></span>
                            <span><i class="fa-regular fa-envelope text-muted me-1"></i> <?= htmlspecialchars($employee['email'] ?? '—') ?></span>
                        </div>
                    </div>

                    <div class="text-end">
                        <a href="<?= BASE_URL ?>/ess/profile" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                            <i class="fa-regular fa-pen-to-square me-1"></i> Chỉnh sửa
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chấm công hôm nay -->
        <div class="col-lg-4">
            <div class="card-custom h-100 p-4 border-start border-4 border-primary">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="fw-bold text-dark small text-uppercase letter-spacing-1">
                        <i class="fa-regular fa-clock text-primary me-1"></i> Chấm công hôm nay
                    </span>
                    <span class="badge bg-light text-secondary border">
                        <?= date('d/m/Y') ?>
                    </span>
                </div>

                <?php if ($todayAttendance): ?>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <div class="bg-light p-2 rounded text-center">
                                <small class="text-muted d-block">Giờ vào (Check-in)</small>
                                <span class="fs-5 fw-bold text-success">
                                    <?= !empty($todayAttendance['check_in']) ? date('H:i', strtotime($todayAttendance['check_in'])) : '—' ?>
                                </span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-light p-2 rounded text-center">
                                <small class="text-muted d-block">Giờ ra (Check-out)</small>
                                <span class="fs-5 fw-bold text-primary">
                                    <?= !empty($todayAttendance['check_out']) ? date('H:i', strtotime($todayAttendance['check_out'])) : '—' ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center justify-content-between small">
                        <span class="text-muted">Ca: <?= htmlspecialchars($todayAttendance['shift_type'] ?? 'Hành chính') ?></span>
                        <span class="badge bg-success-subtle text-success border border-success-subtle">
                            <i class="fa-solid fa-circle-check me-1"></i> Đã ghi nhận
                        </span>
                    </div>
                <?php else: ?>
                    <div class="text-center py-2 text-muted">
                        <i class="fa-solid fa-fingerprint fa-2x text-warning mb-2"></i>
                        <p class="small mb-2">Hôm nay bạn chưa có dữ liệu chấm công từ máy chấm công / FaceID.</p>
                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle">
                            Chưa có lượt Check-in
                        </span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════
         SECTION 2: 4 METRIC CARDS NHANH
         ══════════════════════════════════════════════════════ -->
    <div class="row g-3 mb-4">
        <!-- Phép năm còn lại -->
        <div class="col-6 col-md-3">
            <div class="metric-card emerald h-100">
                <div class="metric-icon">
                    <i class="fa-solid fa-calendar-minus"></i>
                </div>
                <div class="metric-value text-success"><?= number_format($leaveStats['remaining'], 1) ?></div>
                <div class="metric-label">Phép năm còn lại</div>
                <div class="metric-sub">
                    Đã dùng <strong><?= number_format($leaveStats['used'], 1) ?></strong> / Cấp <strong><?= number_format($leaveStats['entitled'], 1) ?></strong> ngày
                </div>
            </div>
        </div>

        <!-- Lương tháng gần nhất -->
        <div class="col-6 col-md-3">
            <div class="metric-card blue h-100">
                <div class="metric-icon">
                    <i class="fa-solid fa-coins"></i>
                </div>
                <div class="metric-value text-primary" style="font-size: 1.25rem;">
                    <?php if ($latestPayroll): ?>
                        <?= number_format($latestPayroll['net_salary'], 0, ',', '.') ?> <small style="font-size: 0.75rem;">đ</small>
                    <?php else: ?>
                        0 đ
                    <?php endif; ?>
                </div>
                <div class="metric-label">Lương tháng gần nhất</div>
                <div class="metric-sub">
                    <?php if ($latestPayroll): ?>
                        Kỳ lương: <strong>T<?= $latestPayroll['month'] ?>/<?= $latestPayroll['year'] ?></strong>
                    <?php else: ?>
                        Chưa có phiếu lương
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Chấm công tháng này -->
        <div class="col-6 col-md-3">
            <div class="metric-card amber h-100">
                <div class="metric-icon">
                    <i class="fa-solid fa-user-clock"></i>
                </div>
                <div class="metric-value text-warning">
                    <?= number_format($monthAttendanceSummary['worked_days'] ?? 0) ?> <small style="font-size: 0.85rem; font-weight: normal;">công</small>
                </div>
                <div class="metric-label">Công tháng <?= date('m/Y') ?></div>
                <div class="metric-sub">
                    OT: <strong><?= number_format((float)($monthAttendanceSummary['total_ot_hours'] ?? 0), 1) ?></strong> giờ làm thêm
                </div>
            </div>
        </div>

        <!-- Trạng thái nhân sự -->
        <div class="col-6 col-md-3">
            <div class="metric-card rose h-100">
                <div class="metric-icon">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <div class="metric-value text-danger" style="font-size: 1.2rem;">
                    <?= htmlspecialchars($employee['status'] ?? 'Active') ?>
                </div>
                <div class="metric-label">Trạng thái hồ sơ</div>
                <div class="metric-sub">
                    HĐ: <strong><?= htmlspecialchars($employee['contract_status'] ?? 'Chính thức') ?></strong>
                </div>
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════
         SECTION 3: PHÍM TẮT THAO TÁC NHANH (QUICK ACTIONS)
         ══════════════════════════════════════════════════════ -->
    <div class="card-custom mb-4 p-3 bg-white">
        <div class="row g-2 text-center">
            <div class="col-3 col-sm-3">
                <a href="<?= BASE_URL ?>/ess/leaveRequest" class="d-block p-2 rounded text-decoration-none hover-bg text-dark">
                    <div class="rounded-circle bg-warning-subtle text-warning mx-auto d-flex align-items-center justify-content-center mb-2" style="width: 48px; height: 48px;">
                        <i class="fa-solid fa-plane-departure fs-5"></i>
                    </div>
                    <span class="fw-semibold small d-block">Xin nghỉ phép</span>
                </a>
            </div>
            <div class="col-3 col-sm-3">
                <a href="<?= BASE_URL ?>/ess/payslip" class="d-block p-2 rounded text-decoration-none hover-bg text-dark">
                    <div class="rounded-circle bg-primary-subtle text-primary mx-auto d-flex align-items-center justify-content-center mb-2" style="width: 48px; height: 48px;">
                        <i class="fa-solid fa-file-invoice-dollar fs-5"></i>
                    </div>
                    <span class="fw-semibold small d-block">Xem phiếu lương</span>
                </a>
            </div>
            <div class="col-3 col-sm-3">
                <a href="<?= BASE_URL ?>/ess/attendance" class="d-block p-2 rounded text-decoration-none hover-bg text-dark">
                    <div class="rounded-circle bg-info-subtle text-info mx-auto d-flex align-items-center justify-content-center mb-2" style="width: 48px; height: 48px;">
                        <i class="fa-solid fa-calendar-days fs-5"></i>
                    </div>
                    <span class="fw-semibold small d-block">Bảng chấm công</span>
                </a>
            </div>
            <div class="col-3 col-sm-3">
                <a href="<?= BASE_URL ?>/ess/profile" class="d-block p-2 rounded text-decoration-none hover-bg text-dark">
                    <div class="rounded-circle bg-success-subtle text-success mx-auto d-flex align-items-center justify-content-center mb-2" style="width: 48px; height: 48px;">
                        <i class="fa-solid fa-user-gear fs-5"></i>
                    </div>
                    <span class="fw-semibold small d-block">Hồ sơ cá nhân</span>
                </a>
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════
         SECTION 4: HAI CỘT CHÍNH (THÔNG BÁO & HOẠT ĐỘNG CÁ NHÂN)
         ══════════════════════════════════════════════════════ -->
    <div class="row g-4">
        <!-- Cột trái: Thông báo nội bộ công ty -->
        <div class="col-lg-7">
            <div class="card-custom h-100">
                <div class="card-custom-header">
                    <h5 class="card-custom-title">
                        <i class="fa-solid fa-bullhorn text-primary"></i> Thông báo nội bộ công ty
                    </h5>
                    <a href="<?= BASE_URL ?>/ess/notifications" class="small text-primary text-decoration-none fw-semibold">
                        Xem tất cả <i class="fa-solid fa-arrow-right small"></i>
                    </a>
                </div>
                <div class="card-custom-body p-0">
                    <?php if (!empty($companyAnnouncements)): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($companyAnnouncements as $post): ?>
                                <a href="<?= BASE_URL ?>/ess/viewArticle/<?= $post['id'] ?>" class="list-group-item list-group-item-action p-3 border-bottom">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <?php if ($post['category'] === 'hse_rule'): ?>
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                                <i class="fa-solid fa-shield-halved me-1"></i> An toàn HSE
                                            </span>
                                        <?php elseif ($post['category'] === 'notice'): ?>
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                                <i class="fa-solid fa-bell me-1"></i> Thông báo
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-info-subtle text-info border border-info-subtle">
                                                <i class="fa-solid fa-newspaper me-1"></i> Tin tức
                                            </span>
                                        <?php endif; ?>
                                        <small class="text-muted"><?= date('d/m/Y', strtotime($post['published_at'] ?? $post['created_at'])) ?></small>
                                    </div>
                                    <h6 class="fw-bold mb-1 text-dark"><?= htmlspecialchars($post['title']) ?></h6>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4 text-muted small">
                            Chưa có thông báo mới nào từ công ty.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Cột phải: Đơn nghỉ phép gần đây & Thông báo cá nhân -->
        <div class="col-lg-5">
            <!-- Đơn nghỉ phép gần đây -->
            <div class="card-custom mb-4">
                <div class="card-custom-header">
                    <h5 class="card-custom-title">
                        <i class="fa-solid fa-calendar-xmark text-warning"></i> Đơn nghỉ phép gần đây
                    </h5>
                    <a href="<?= BASE_URL ?>/ess/myLeaves" class="small text-primary text-decoration-none fw-semibold">
                        Lịch sử <i class="fa-solid fa-arrow-right small"></i>
                    </a>
                </div>
                <div class="card-custom-body p-0">
                    <?php if (!empty($recentLeaves)): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($recentLeaves as $lr): ?>
                                <div class="list-group-item p-3 border-bottom">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <span class="fw-bold text-dark small"><?= htmlspecialchars($lr['leave_type_name']) ?></span>
                                        <?php 
                                            $stClass = match($lr['status']) {
                                                'Approved' => 'approved',
                                                'Pending'  => 'pending',
                                                'Rejected' => 'rejected',
                                                default    => 'cancelled'
                                            };
                                            $stLabel = match($lr['status']) {
                                                'Approved' => 'Đã duyệt',
                                                'Pending'  => 'Chờ duyệt',
                                                'Rejected' => 'Từ chối',
                                                default    => 'Đã hủy'
                                            };
                                        ?>
                                        <span class="badge-status <?= $stClass ?>"><?= $stLabel ?></span>
                                    </div>
                                    <div class="small text-muted mb-1">
                                        <?= date('d/m/Y', strtotime($lr['start_date'])) ?> &rarr; <?= date('d/m/Y', strtotime($lr['end_date'])) ?>
                                        (<strong><?= $lr['total_days'] ?></strong> ngày)
                                    </div>
                                    <p class="small text-secondary mb-0 text-truncate"><?= htmlspecialchars($lr['reason'] ?? 'Không có lý do') ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4 text-muted small">
                            Bạn chưa nộp đơn xin nghỉ phép nào.
                            <div class="mt-2">
                                <a href="<?= BASE_URL ?>/ess/leaveRequest" class="btn btn-sm btn-outline-warning">
                                    + Nộp đơn xin nghỉ ngay
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Thông báo cá nhân -->
            <div class="card-custom">
                <div class="card-custom-header">
                    <h5 class="card-custom-title">
                        <i class="fa-solid fa-bell text-danger"></i> Thông báo cá nhân
                    </h5>
                    <a href="<?= BASE_URL ?>/ess/notifications" class="small text-primary text-decoration-none fw-semibold">
                        Xem tất cả
                    </a>
                </div>
                <div class="card-custom-body p-0">
                    <?php if (!empty($myNotifications)): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($myNotifications as $notif): ?>
                                <div class="list-group-item p-3 border-bottom <?= empty($notif['is_read']) ? 'bg-light' : '' ?>">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <span class="fw-bold small text-dark"><?= htmlspecialchars($notif['title']) ?></span>
                                        <small class="text-muted" style="font-size: 0.7rem;">
                                            <?= date('d/m/Y H:i', strtotime($notif['created_at'])) ?>
                                        </small>
                                    </div>
                                    <p class="small text-secondary mb-0"><?= htmlspecialchars($notif['message']) ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4 text-muted small">
                            Không có thông báo cá nhân mới.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

</div>

<?php require_once APP_ROOT . '/views/ess/layout/footer.php'; ?>
