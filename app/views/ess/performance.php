<?php
require_once APP_ROOT . '/views/ess/layout/header.php';
?>

<div class="container-xl py-4">
    <!-- Header Page -->
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3">
        <div>
            <h4 class="fw-bold mb-1 text-dark">
                <i class="fa-solid fa-bullseye text-primary me-2"></i> Mục Tiêu KRA & Đánh Giá Hiệu Suất 360°
            </h4>
            <p class="text-muted small mb-0">
                Cổng nhân viên: Thiết lập mục tiêu KRA cá nhân, tự đánh giá định kỳ và thực hiện đánh giá chéo đồng nghiệp.
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>/ess/dashboard" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="fa-solid fa-house-chimney me-1"></i> Trang chủ ESS
            </a>
        </div>
    </div>

    <!-- FLASH MESSAGES -->
    <?php if (Session::hasFlash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i> <?= Session::getFlash('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (Session::hasFlash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="fa-solid fa-circle-exclamation me-2"></i> <?= Session::getFlash('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- DANH SÁCH YÊU CẦU ĐÁNH GIÁ ĐỒNG NGHIỆP CẦN HOÀN THÀNH (NẾU CÓ) -->
    <?php if (!empty($assignedPeerReviews)): ?>
        <div class="card mb-4 border-warning border-opacity-50 shadow-sm" style="border-radius: 12px;">
            <div class="card-header bg-warning bg-opacity-10 py-3 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <i class="fa-solid fa-users text-warning fs-5"></i>
                    <strong class="text-dark">Yêu Cầu Đánh Giá Đồng Nghiệp (Peer Reviews Được Phân Công)</strong>
                </div>
                <span class="badge bg-warning text-dark"><?= count($assignedPeerReviews) ?> Yêu cầu</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-3">Đồng nghiệp được đánh giá</th>
                                <th>Bộ phận</th>
                                <th>Chu kỳ đánh giá</th>
                                <th class="text-center">Trạng thái</th>
                                <th class="text-end pe-3">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($assignedPeerReviews as $apr): ?>
                                <tr>
                                    <td class="ps-3">
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($apr['target_name']) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($apr['target_code']) ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($apr['target_dept'] ?? 'Chưa rõ') ?></td>
                                    <td>
                                        <div class="fw-semibold text-primary"><?= htmlspecialchars($apr['period_name']) ?></div>
                                        <small class="text-muted">Hạn chót: <?= date('d/m/Y', strtotime($apr['period_end_date'])) ?></small>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($apr['status'] === 'Submitted'): ?>
                                            <span class="badge bg-success-subtle text-success"><i class="fa-solid fa-check me-1"></i> Đã hoàn thành</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning-subtle text-warning"><i class="fa-solid fa-clock me-1"></i> Chờ đánh giá</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end pe-3">
                                        <a href="<?= BASE_URL ?>/evaluation/review360/<?= $apr['period_id'] ?>/<?= $apr['employee_id'] ?>" 
                                           class="btn btn-sm <?= $apr['status'] === 'Submitted' ? 'btn-outline-secondary' : 'btn-warning text-dark fw-bold' ?>">
                                            <i class="fa-solid fa-pen-to-square me-1"></i> <?= $apr['status'] === 'Submitted' ? 'Xem lại' : 'Đánh giá ngay' ?>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- DANH SÁCH CÁC CHU KỲ ĐÁNH GIÁ CỦA BẢN THÂN -->
    <div class="card shadow-sm border-0" style="border-radius: 12px;">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <i class="fa-solid fa-calendar-check text-primary fs-5"></i>
                <h5 class="mb-0 fw-bold text-dark">Chu Kỳ Đánh Giá Hiệu Suất Của Tôi</h5>
            </div>
        </div>

        <div class="card-body p-4">
            <?php if (empty($periods)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="fa-solid fa-folder-open fa-3x mb-3 text-secondary opacity-50"></i>
                    <div>Hiện tại chưa có chu kỳ đánh giá nào áp dụng cho bạn.</div>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($periods as $p): ?>
                        <?php 
                            $gs = $p['goals_summary'];
                            $sr = $p['self_review'];
                            $ev = $p['evaluation_result'];
                        ?>
                        <div class="col-12">
                            <div class="p-3 border rounded-3 bg-white shadow-xs position-relative overflow-hidden">
                                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                                    <div>
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <h5 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($p['name']) ?></h5>
                                            <?php if ($p['status'] === 'Active'): ?>
                                                <span class="badge bg-primary text-white">Đang diễn ra</span>
                                            <?php elseif ($p['status'] === 'Draft'): ?>
                                                <span class="badge bg-warning text-dark">Bản nháp</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Đã đóng</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-muted small">
                                            <i class="fa-regular fa-calendar text-primary me-1"></i>
                                            <?= date('d/m/Y', strtotime($p['start_date'])) ?> – <?= date('d/m/Y', strtotime($p['end_date'])) ?>
                                        </div>
                                    </div>

                                    <!-- Final Score & Grade Badge nếu đã chốt -->
                                    <?php if (!empty($ev) && $ev['final_score'] !== null): ?>
                                        <div class="text-end">
                                            <div class="small text-muted">Kết quả chính thức</div>
                                            <span class="badge bg-success fs-6 fw-bold">
                                                <?= number_format((float)$ev['final_score'], 1) ?> điểm (Hạng <?= htmlspecialchars($ev['overall_grade'] ?? 'B') ?>)
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Cards 3 bước: Goals, Self Review, Final -->
                                <div class="row g-3 mb-3">
                                    <!-- Bước 1: KRA Goals -->
                                    <div class="col-md-4">
                                        <div class="p-3 rounded bg-light border h-100">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <strong class="text-dark" style="font-size: 13px;">1. Mục tiêu KRA</strong>
                                                <?php if ($gs['cnt'] > 0): ?>
                                                    <span class="badge <?= $gs['total_weight'] == 100 ? 'bg-success' : 'bg-warning' ?>">
                                                        <?= $gs['cnt'] ?> KRA (<?= $gs['total_weight'] ?>%)
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-light text-muted border">Chưa đặt</span>
                                                <?php endif; ?>
                                            </div>
                                            <p class="text-muted small mb-3">Thiết lập các mục tiêu công việc và trọng số (100%).</p>
                                            <a href="<?= BASE_URL ?>/evaluation/setGoals/<?= $p['id'] ?>/<?= $employee['id'] ?>" 
                                               class="btn btn-sm btn-outline-primary w-100 fw-semibold">
                                                <i class="fa-solid fa-bullseye me-1"></i> <?= $gs['cnt'] > 0 ? 'Xem & Chỉnh sửa KRA' : 'Đặt mục tiêu KRA' ?>
                                            </a>
                                        </div>
                                    </div>

                                    <!-- Bước 2: Tự Đánh Giá (Self Review) -->
                                    <div class="col-md-4">
                                        <div class="p-3 rounded bg-light border h-100">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <strong class="text-dark" style="font-size: 13px;">2. Tự Đánh Giá (Self)</strong>
                                                <?php if (!empty($sr)): ?>
                                                    <span class="badge bg-success-subtle text-success">Đã nộp (<?= (float)$sr['overall_score'] ?>đ)</span>
                                                <?php else: ?>
                                                    <span class="badge bg-light text-muted border">Chưa nộp</span>
                                                <?php endif; ?>
                                            </div>
                                            <p class="text-muted small mb-3">Báo cáo kết quả thực tế và tự chấm điểm cá nhân.</p>
                                            <?php if (!empty($p['allow_self_review'])): ?>
                                                <a href="<?= BASE_URL ?>/evaluation/submitSelfReview/<?= $p['id'] ?>/<?= $employee['id'] ?>" 
                                                   class="btn btn-sm <?= !empty($sr) ? 'btn-outline-success' : 'btn-success' ?> w-100 fw-semibold">
                                                    <i class="fa-solid fa-user-check me-1"></i> <?= !empty($sr) ? 'Xem lại phiếu tự chấm' : 'Nộp Tự Đánh Giá' ?>
                                                </a>
                                            <?php else: ?>
                                                <button class="btn btn-sm btn-light w-100 text-muted" disabled>Không áp dụng</button>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <!-- Bước 3: Đánh giá & Chốt Điểm -->
                                    <div class="col-md-4">
                                        <div class="p-3 rounded bg-light border h-100">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <strong class="text-dark" style="font-size: 13px;">3. Kết quả Tổng thể</strong>
                                                <?php if (!empty($ev) && $ev['final_score'] !== null): ?>
                                                    <span class="badge bg-success">Đã duyệt</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning text-dark">Đang xử lý</span>
                                                <?php endif; ?>
                                            </div>
                                            <p class="text-muted small mb-3">Đối chiếu 360° từ Quản lý & đồng nghiệp.</p>
                                            <a href="<?= BASE_URL ?>/evaluation/finalScore/<?= $p['id'] ?>/<?= $employee['id'] ?>" 
                                               class="btn btn-sm btn-outline-secondary w-100 fw-semibold">
                                                <i class="fa-solid fa-chart-pie me-1"></i> Xem Biểu Đồ Radar 360°
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once APP_ROOT . '/views/ess/layout/footer.php'; ?>
