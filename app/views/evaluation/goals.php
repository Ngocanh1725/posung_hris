<?php
/**
 * View: evaluation/goals.php – Quản lý Mục tiêu KRA & Tiến độ Đánh giá 360°
 */

$statusBadges = [
    'Active' => ['Đang diễn ra',  'badge bg-primary text-white', 'fas fa-spinner fa-spin'],
    'Draft'  => ['Bản nháp',      'badge bg-warning text-dark', 'fas fa-pencil-alt'],
    'Closed' => ['Đã đóng / Khóa','badge bg-secondary text-white', 'fas fa-lock'],
];

// Thống kê nhanh
$totalEmps = count($employees);
$goalsSetCount = 0;
$selfCount = 0;
$peerCount = 0;
$managerCount = 0;
$finalCount = 0;

foreach ($employees as $e) {
    if (!empty($e['kra_count']) && $e['kra_count'] > 0) $goalsSetCount++;
    if (!empty($e['self_reviewed']) && $e['self_reviewed'] > 0) $selfCount++;
    if (!empty($e['peer_submitted_count']) && $e['peer_submitted_count'] > 0) $peerCount++;
    if (!empty($e['manager_reviewed']) && $e['manager_reviewed'] > 0) $managerCount++;
    if (!empty($e['overall_final_score'])) $finalCount++;
}

$currentEmpId = Session::employeeId();
?>

<!-- BREADCRUMB -->
<div class="breadcrumb-nav mb-3 d-flex align-items-center gap-2" style="font-size: 13px; color: var(--text-muted);">
    <a href="<?= BASE_URL ?>/evaluation" class="text-decoration-none" style="color: var(--primary);">
        <i class="fas fa-award"></i> Quản lý Đánh giá KPI
    </a>
    <i class="fas fa-chevron-right" style="font-size: 10px;"></i>
    <a href="<?= BASE_URL ?>/evaluation/show/<?= $period->id ?>" class="text-decoration-none" style="color: var(--primary);">
        <?= htmlspecialchars($period->name) ?>
    </a>
    <i class="fas fa-chevron-right" style="font-size: 10px;"></i>
    <span>Quản lý Mục tiêu KRA & Tiến độ 360°</span>
</div>

<!-- HEADER CARD & NAV TABS -->
<div class="card mb-4" style="border: 1px solid var(--border); box-shadow: 0 4px 18px rgba(0,0,0,0.04); border-radius: 12px;">
    <div class="card-body" style="padding: 24px;">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div class="d-flex gap-3 align-items-start">
                <div style="width: 56px; height: 56px; border-radius: 14px; background: linear-gradient(135deg, #0284c7, #0369a1); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 24px; box-shadow: 0 6px 16px rgba(2,132,199,0.3); flex-shrink: 0;">
                    <i class="fas fa-bullseye"></i>
                </div>
                <div>
                    <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                        <h2 style="font-size: 20px; font-weight: 700; margin: 0; color: var(--text);">
                            Mục Tiêu KRA & Tiến Độ Đánh Giá 360°: <?= htmlspecialchars($period->name) ?>
                        </h2>
                        <?php $badge = $statusBadges[$period->status] ?? ['Không xác định', 'badge bg-secondary', '']; ?>
                        <span class="<?= $badge[1] ?>" style="font-size: 12px; font-weight: 600; padding: 4px 10px; border-radius: 20px;">
                            <i class="<?= $badge[2] ?>"></i> <?= $badge[0] ?>
                        </span>
                    </div>

                    <div style="font-size: 13px; color: var(--text-muted); display: flex; gap: 16px; flex-wrap: wrap; margin-top: 6px;">
                        <span><i class="fas fa-calendar-alt text-primary"></i> Thời gian: <strong><?= date('d/m/Y', strtotime($period->start_date)) ?></strong> – <strong><?= date('d/m/Y', strtotime($period->end_date)) ?></strong></span>
                        <span><i class="fas fa-sync-alt text-primary"></i> Đánh giá chéo: <strong><?= !empty($period->allow_peer_review) ? 'Bật (Tối đa ' . ($period->peer_review_count ?? 2) . ' Peers)' : 'Tắt' ?></strong></span>
                        <span><i class="fas fa-user-check text-primary"></i> Tự đánh giá: <strong><?= !empty($period->allow_self_review) ? 'Bật' : 'Tắt' ?></strong></span>
                    </div>
                </div>
            </div>

            <!-- ACTION BUTTONS -->
            <div class="d-flex gap-2 flex-wrap">
                <a href="<?= BASE_URL ?>/evaluation/assignPeerReviewers/<?= $period->id ?>" class="btn btn-outline-primary" style="font-weight: 600;">
                    <i class="fas fa-users-cog"></i> Phân công Peer 360°
                </a>
                <a href="<?= BASE_URL ?>/evaluation/analytics/<?= $period->id ?>" class="btn btn-primary" style="box-shadow: 0 4px 14px rgba(79,70,229,0.3); font-weight: 600;">
                    <i class="fas fa-chart-line"></i> Dashboard Phân tích 360°
                </a>
                <a href="<?= BASE_URL ?>/evaluation/show/<?= $period->id ?>" class="btn btn-ghost" style="border: 1px solid var(--border);">
                    <i class="fas fa-th-list"></i> Chấm điểm Tiêu chuẩn
                </a>
            </div>
        </div>

        <!-- 4-STEP TIMELINE REVIEW PROGRESS -->
        <div class="mt-4 pt-3" style="border-top: 1px solid var(--border);">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span style="font-size: 13px; font-weight: 700; color: var(--text); text-transform: uppercase; letter-spacing: 0.5px;">
                    <i class="fas fa-route text-primary me-1"></i> Quy trình 4 bước Đánh giá 360° (Timeline Review)
                </span>
                <span class="text-muted" style="font-size: 12px;">Đã chốt: <strong><?= $finalCount ?>/<?= $totalEmps ?></strong> (<?= $totalEmps > 0 ? round(($finalCount/$totalEmps)*100) : 0 ?>%)</span>
            </div>

            <div class="row g-2">
                <!-- Bước 1: KRA Goals -->
                <div class="col-md-3 col-sm-6">
                    <div style="background: rgba(2, 132, 199, 0.06); border: 1px solid rgba(2, 132, 199, 0.2); border-radius: 10px; padding: 12px;">
                        <div class="d-flex align-items-center justify-content-between">
                            <span style="font-size: 12px; font-weight: 600; color: #0284c7;">1. Mục tiêu KRA</span>
                            <span class="badge bg-info text-white"><?= $goalsSetCount ?>/<?= $totalEmps ?></span>
                        </div>
                        <div class="progress mt-2" style="height: 6px;">
                            <div class="progress-bar bg-info" style="width: <?= $totalEmps > 0 ? ($goalsSetCount/$totalEmps)*100 : 0 ?>%;"></div>
                        </div>
                    </div>
                </div>

                <!-- Bước 2: Self Review -->
                <div class="col-md-3 col-sm-6">
                    <div style="background: rgba(16, 185, 129, 0.06); border: 1px solid rgba(16, 185, 129, 0.2); border-radius: 10px; padding: 12px;">
                        <div class="d-flex align-items-center justify-content-between">
                            <span style="font-size: 12px; font-weight: 600; color: #059669;">2. Tự Đánh Giá (Self)</span>
                            <span class="badge bg-success text-white"><?= $selfCount ?>/<?= $totalEmps ?></span>
                        </div>
                        <div class="progress mt-2" style="height: 6px;">
                            <div class="progress-bar bg-success" style="width: <?= $totalEmps > 0 ? ($selfCount/$totalEmps)*100 : 0 ?>%;"></div>
                        </div>
                    </div>
                </div>

                <!-- Bước 3: Peer & Manager -->
                <div class="col-md-3 col-sm-6">
                    <div style="background: rgba(245, 158, 11, 0.06); border: 1px solid rgba(245, 158, 11, 0.2); border-radius: 10px; padding: 12px;">
                        <div class="d-flex align-items-center justify-content-between">
                            <span style="font-size: 12px; font-weight: 600; color: #d97706;">3. Peer & Manager Review</span>
                            <span class="badge bg-warning text-dark"><?= $managerCount ?> Quản lý</span>
                        </div>
                        <div class="progress mt-2" style="height: 6px;">
                            <div class="progress-bar bg-warning" style="width: <?= $totalEmps > 0 ? ($managerCount/$totalEmps)*100 : 0 ?>%;"></div>
                        </div>
                    </div>
                </div>

                <!-- Bước 4: Final Score -->
                <div class="col-md-3 col-sm-6">
                    <div style="background: rgba(79, 70, 229, 0.06); border: 1px solid rgba(79, 70, 229, 0.2); border-radius: 10px; padding: 12px;">
                        <div class="d-flex align-items-center justify-content-between">
                            <span style="font-size: 12px; font-weight: 600; color: #4f46e5;">4. Chốt Điểm (Final)</span>
                            <span class="badge bg-primary text-white"><?= $finalCount ?>/<?= $totalEmps ?></span>
                        </div>
                        <div class="progress mt-2" style="height: 6px;">
                            <div class="progress-bar bg-primary" style="width: <?= $totalEmps > 0 ? ($finalCount/$totalEmps)*100 : 0 ?>%;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- FILTER & SEARCH BAR -->
<div class="card mb-4" style="border: 1px solid var(--border); box-shadow: 0 2px 10px rgba(0,0,0,0.02); border-radius: 10px;">
    <div class="card-body" style="padding: 16px 20px;">
        <form method="GET" action="<?= BASE_URL ?>/evaluation/goals/<?= $period->id ?>" class="row g-2 align-items-center">
            <!-- Chọn chu kỳ khác -->
            <div class="col-md-3 col-sm-6">
                <select class="form-select form-select-sm" onchange="location.href='<?= BASE_URL ?>/evaluation/goals/' + this.value;">
                    <?php foreach ($allPeriods as $p): ?>
                        <option value="<?= $p['id'] ?>" <?= $p['id'] == $period->id ? 'selected' : '' ?>>
                            Chu kỳ: <?= htmlspecialchars($p['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Lọc Phòng ban -->
            <div class="col-md-3 col-sm-6">
                <select name="dept" class="form-select form-select-sm">
                    <option value="">-- Tất cả Phòng ban --</option>
                    <?php foreach ($departments as $dept): ?>
                        <option value="<?= $dept->id ?>" <?= $filters['dept_id'] == $dept->id ? 'selected' : '' ?>>
                            <?= htmlspecialchars($dept->dept_name) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Lọc Trạng thái KRA -->
            <div class="col-md-2 col-sm-6">
                <select name="status" class="form-select form-select-sm">
                    <option value="">-- Trạng thái Goals --</option>
                    <option value="No_Goals" <?= $filters['status'] === 'No_Goals' ? 'selected' : '' ?>>Chưa đặt Goals</option>
                    <option value="Draft" <?= $filters['status'] === 'Draft' ? 'selected' : '' ?>>Bản nháp (Draft)</option>
                    <option value="Submitted" <?= $filters['status'] === 'Submitted' ? 'selected' : '' ?>>Đã nộp (Submitted)</option>
                    <option value="Reviewed" <?= $filters['status'] === 'Reviewed' ? 'selected' : '' ?>>Đã duyệt (Reviewed)</option>
                </select>
            </div>

            <!-- Tìm kiếm tên/mã -->
            <div class="col-md-3 col-sm-6">
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Tìm tên hoặc mã NV..." value="<?= htmlspecialchars($filters['search']) ?>">
                </div>
            </div>

            <!-- Nút Lọc & Reset -->
            <div class="col-md-1 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm w-100" title="Tìm kiếm">
                    <i class="fas fa-filter"></i>
                </button>
                <a href="<?= BASE_URL ?>/evaluation/goals/<?= $period->id ?>" class="btn btn-ghost btn-sm" title="Xóa bộ lọc" style="border: 1px solid var(--border);">
                    <i class="fas fa-redo"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- DANH SÁCH NHÂN SỰ & TIẾN ĐỘ 360 -->
<div class="card" style="border: 1px solid var(--border); box-shadow: 0 4px 18px rgba(0,0,0,0.04); border-radius: 12px; overflow: hidden;">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
            <thead style="background: var(--bg-hover); border-bottom: 2px solid var(--border);">
                <tr>
                    <th style="padding: 14px 16px; width: 50px;" class="text-center">#</th>
                    <th style="padding: 14px 16px;">Nhân sự & Vị trí</th>
                    <th style="padding: 14px 16px;">Phòng ban & Quản lý</th>
                    <th style="padding: 14px 16px; width: 170px;">Mục tiêu KRA & Trọng số</th>
                    <th style="padding: 14px 16px; width: 220px;">Tiến trình Đánh giá 360°</th>
                    <th style="padding: 14px 16px; width: 130px;" class="text-center">Kết quả Final</th>
                    <th style="padding: 14px 16px; width: 190px;" class="text-end">Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($employees)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="fas fa-inbox fa-3x mb-3 text-secondary opacity-50"></i>
                            <div>Không tìm thấy nhân viên nào phù hợp với bộ lọc trong chu kỳ này.</div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($employees as $idx => $e): ?>
                        <tr>
                            <td class="text-center text-muted fw-semibold"><?= $idx + 1 ?></td>

                            <!-- Nhân viên -->
                            <td style="padding: 12px 16px;">
                                <div class="d-flex align-items-center gap-2">
                                    <div style="width: 38px; height: 38px; border-radius: 10px; background: linear-gradient(135deg, #4f46e5, #6366f1); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 14px; flex-shrink: 0;">
                                        <?= mb_strtoupper(mb_substr($e['full_name'], 0, 1, 'UTF-8')) ?>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark" style="font-size: 14px;"><?= htmlspecialchars($e['full_name']) ?></div>
                                        <div class="text-muted" style="font-size: 11px;">
                                            <span class="badge bg-secondary-subtle text-secondary" style="font-family: monospace;"><?= htmlspecialchars($e['employee_code']) ?></span>
                                            <?= htmlspecialchars($e['pos_title'] ?? 'Nhân viên') ?>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Phòng ban & Quản lý -->
                            <td style="padding: 12px 16px;">
                                <div class="fw-semibold text-dark"><?= htmlspecialchars($e['dept_name'] ?? 'Chưa phân bổ') ?></div>
                                <small class="text-muted" style="font-size: 11px;">
                                    <i class="fas fa-user-tie text-secondary me-1"></i>QL: <?= htmlspecialchars($e['manager_name'] ?? 'Chưa gán') ?>
                                </small>
                            </td>

                            <!-- Mục tiêu KRA & Trọng số -->
                            <td style="padding: 12px 16px;">
                                <?php if ($e['kra_count'] > 0): ?>
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="badge bg-primary-subtle text-primary fw-semibold">
                                            <?= $e['kra_count'] ?> KRA
                                        </span>
                                        <span class="fw-bold <?= $e['total_weight'] == 100 ? 'text-success' : 'text-danger' ?>" style="font-size: 11px;">
                                            <?= $e['total_weight'] ?>%
                                        </span>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar <?= $e['total_weight'] == 100 ? 'bg-success' : 'bg-warning' ?>" style="width: <?= min(100, $e['total_weight']) ?>%;"></div>
                                    </div>
                                    <div class="text-muted mt-1" style="font-size: 10px;">
                                        Trạng thái: 
                                        <?php if ($e['kra_status'] === 'Reviewed'): ?>
                                            <span class="text-success fw-bold"><i class="fas fa-check-circle"></i> Đã duyệt</span>
                                        <?php elseif ($e['kra_status'] === 'Submitted'): ?>
                                            <span class="text-primary fw-bold"><i class="fas fa-paper-plane"></i> Đã nộp</span>
                                        <?php else: ?>
                                            <span class="text-warning fw-bold"><i class="fas fa-pencil-alt"></i> Bản nháp</span>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <span class="badge bg-light text-muted border">
                                        <i class="fas fa-exclamation-circle text-warning me-1"></i> Chưa thiết lập
                                    </span>
                                <?php endif; ?>
                            </td>

                            <!-- Tiến trình 360° -->
                            <td style="padding: 12px 16px;">
                                <div class="d-flex flex-column gap-1" style="font-size: 11px;">
                                    <!-- Self Review -->
                                    <div class="d-flex align-items-center justify-content-between">
                                        <span class="text-muted"><i class="fas fa-user text-secondary me-1"></i> Tự chấm:</span>
                                        <?php if (!empty($e['self_reviewed'])): ?>
                                            <span class="badge bg-success-subtle text-success"><i class="fas fa-check"></i> Đã nộp <?= !empty($e['avg_self_score']) ? "({$e['avg_self_score']})" : '' ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-light text-muted border">Chờ nộp</span>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Peer Review -->
                                    <div class="d-flex align-items-center justify-content-between">
                                        <span class="text-muted"><i class="fas fa-user-friends text-secondary me-1"></i> Đồng nghiệp:</span>
                                        <?php if ($e['peer_assigned_count'] > 0): ?>
                                            <span class="badge <?= $e['peer_submitted_count'] >= $e['peer_assigned_count'] ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' ?>">
                                                <?= $e['peer_submitted_count'] ?>/<?= $e['peer_assigned_count'] ?> đã nộp
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-light text-muted border">Chưa gán peer</span>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Manager Review -->
                                    <div class="d-flex align-items-center justify-content-between">
                                        <span class="text-muted"><i class="fas fa-user-shield text-secondary me-1"></i> Quản lý:</span>
                                        <?php if (!empty($e['manager_reviewed'])): ?>
                                            <span class="badge bg-success-subtle text-success"><i class="fas fa-check"></i> Đã chấm <?= !empty($e['avg_mgr_score']) ? "({$e['avg_mgr_score']})" : '' ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-light text-muted border">Chờ đánh giá</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>

                            <!-- Kết quả Final -->
                            <td class="text-center" style="padding: 12px 16px;">
                                <?php if ($e['overall_final_score'] !== null): ?>
                                    <div style="font-size: 18px; font-weight: 800; color: #4f46e5;">
                                        <?= number_format((float)$e['overall_final_score'], 1) ?>
                                    </div>
                                    <?php 
                                        $gr = $e['overall_grade'] ?? 'B';
                                        $gColor = ($gr === 'A') ? 'bg-success' : (($gr === 'B') ? 'bg-primary' : (($gr === 'C') ? 'bg-warning text-dark' : 'bg-danger'));
                                    ?>
                                    <span class="badge <?= $gColor ?>" style="font-size: 10px; padding: 2px 8px;">
                                        Hạng <?= $gr ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted" style="font-size: 12px;">-- Chưa chốt --</span>
                                <?php endif; ?>
                            </td>

                            <!-- Hành động -->
                            <td class="text-end" style="padding: 12px 16px;">
                                <div class="dropdown d-inline-block">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-ellipsis-v me-1"></i> Thao tác
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" style="border-radius: 10px; font-size: 13px;">
                                        <!-- Thiết lập Goals -->
                                        <li>
                                            <a class="dropdown-item py-2" href="<?= BASE_URL ?>/evaluation/setGoals/<?= $period->id ?>/<?= $e['id'] ?>">
                                                <i class="fas fa-bullseye text-primary me-2"></i> Thiết lập Mục tiêu KRA
                                            </a>
                                        </li>

                                        <!-- Tự đánh giá (Nếu là chính mình hoặc HR) -->
                                        <?php if ($e['id'] == $currentEmpId || Session::isAdmin()): ?>
                                            <li>
                                                <a class="dropdown-item py-2" href="<?= BASE_URL ?>/evaluation/submitSelfReview/<?= $period->id ?>/<?= $e['id'] ?>">
                                                    <i class="fas fa-user-edit text-success me-2"></i> Tự Đánh Giá (Self Review)
                                                </a>
                                            </li>
                                        <?php endif; ?>

                                        <!-- Review 360 -->
                                        <li>
                                            <a class="dropdown-item py-2" href="<?= BASE_URL ?>/evaluation/review360/<?= $period->id ?>/<?= $e['id'] ?>">
                                                <i class="fas fa-star-half-alt text-warning me-2"></i> Đánh giá 360° (Manager / Peer)
                                            </a>
                                        </li>

                                        <li><hr class="dropdown-divider my-1"></li>

                                        <!-- Chốt điểm Final -->
                                        <li>
                                            <a class="dropdown-item py-2 fw-bold text-indigo" href="<?= BASE_URL ?>/evaluation/finalScore/<?= $period->id ?>/<?= $e['id'] ?>" style="color: #4f46e5;">
                                                <i class="fas fa-clipboard-check text-indigo me-2"></i> Chốt Điểm & Radar Chart
                                            </a>
                                        </li>

                                        <!-- Đề xuất tăng lương (Salary Progression) -->
                                        <?php if ($e['overall_final_score'] !== null): ?>
                                            <li>
                                                <a class="dropdown-item py-2 text-success fw-semibold" href="<?= BASE_URL ?>/salaryProgression/create/<?= $e['id'] ?>?reason=<?= urlencode('Đánh giá 360 - ' . $period->name . ' - ' . $e['overall_final_score'] . ' điểm') ?>">
                                                    <i class="fas fa-chart-line text-success me-2"></i> Đề xuất Tăng lương
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
