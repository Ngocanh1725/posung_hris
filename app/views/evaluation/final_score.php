<?php
/**
 * View: evaluation/final_score.php – Chốt Điểm Đánh Giá 360°, Radar Chart & Đề Xuất Nâng Lương
 */

$isFinalized = ($existing_eval && $existing_eval->status === 'Approved' && $existing_eval->final_score !== null);
$currentGrade = $existing_eval->overall_grade ?? 'B';
$currentFinalScore = $existing_eval ? (float)$existing_eval->final_score : ($recommended_score ?? 82);

// Chuẩn bị dữ liệu Radar Chart
$radarLabelsJson = json_encode($radar_labels);
$radarSelfJson = json_encode($radar_self);
$radarManagerJson = json_encode($radar_manager);
$radarPeerJson = json_encode($radar_peer);
?>

<!-- BREADCRUMB -->
<div class="breadcrumb-nav mb-3 d-flex align-items-center gap-2" style="font-size: 13px; color: var(--text-muted);">
    <a href="<?= BASE_URL ?>/evaluation" class="text-decoration-none" style="color: var(--primary);">
        <i class="fas fa-award"></i> Quản lý Đánh giá KPI
    </a>
    <i class="fas fa-chevron-right" style="font-size: 10px;"></i>
    <a href="<?= BASE_URL ?>/evaluation/goals/<?= $period->id ?>" class="text-decoration-none" style="color: var(--primary);">
        Mục tiêu KRA: <?= htmlspecialchars($period->name) ?>
    </a>
    <i class="fas fa-chevron-right" style="font-size: 10px;"></i>
    <span>Chốt Điểm Đánh Giá 360°: <?= htmlspecialchars($employee->full_name) ?></span>
</div>

<!-- HEADER CARD -->
<div class="card mb-4" style="border: 1px solid var(--border); box-shadow: 0 4px 18px rgba(0,0,0,0.04); border-radius: 12px;">
    <div class="card-body" style="padding: 24px;">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div class="d-flex gap-3 align-items-center">
                <div style="width: 58px; height: 58px; border-radius: 14px; background: linear-gradient(135deg, #4f46e5, #4338ca); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 24px; font-weight: 700; box-shadow: 0 6px 16px rgba(79,70,229,0.3); flex-shrink: 0;">
                    <i class="fas fa-award"></i>
                </div>
                <div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <h2 style="font-size: 20px; font-weight: 700; margin: 0; color: var(--text);">
                            Hội Đồng & Quản Lý Chốt Điểm Đánh Giá 360°
                        </h2>
                        <?php if ($isFinalized): ?>
                            <span class="badge bg-success" style="font-size: 12px; font-weight: 600; padding: 4px 10px; border-radius: 20px;">
                                <i class="fas fa-check-circle me-1"></i> Đã Phê Duyệt Kết Quả
                            </span>
                        <?php else: ?>
                            <span class="badge bg-warning text-dark" style="font-size: 12px; font-weight: 600; padding: 4px 10px; border-radius: 20px;">
                                <i class="fas fa-clock me-1"></i> Đang Chờ Chốt Điểm Cuối
                            </span>
                        <?php endif; ?>
                    </div>
                    <div style="font-size: 13px; color: var(--text-muted); display: flex; gap: 16px; flex-wrap: wrap; margin-top: 6px;">
                        <span><i class="fas fa-user text-primary"></i> Nhân sự: <strong><?= htmlspecialchars($employee->full_name) ?></strong> (<?= htmlspecialchars($employee->employee_code) ?>)</span>
                        <span><i class="fas fa-id-badge text-primary"></i> Vị trí: <strong><?= htmlspecialchars($employee->pos_title ?? 'Nhân viên') ?></strong></span>
                        <span><i class="fas fa-building text-primary"></i> Phòng ban: <strong><?= htmlspecialchars($employee->dept_name ?? 'Chưa phân bổ') ?></strong></span>
                    </div>
                </div>
            </div>

            <!-- TÍCH HỢP ĐỀ XUẤT TĂNG LƯƠNG & NÚT ĐIỀU HƯỚNG -->
            <div class="d-flex gap-2 flex-wrap">
                <a href="<?= BASE_URL ?>/salaryProgression/create/<?= $employee->id ?>?reason=<?= urlencode('Đề xuất tăng lương theo kết quả đánh giá 360 - Chu kỳ ' . $period->name . ' - Xếp loại ' . $currentGrade . ' (' . $currentFinalScore . ' điểm)') ?>" 
                   class="btn btn-success fw-bold" style="box-shadow: 0 4px 14px rgba(16,185,129,0.3);">
                    <i class="fas fa-money-bill-wave me-1"></i> Đề Xuất Tăng Lương
                </a>
                <a href="<?= BASE_URL ?>/evaluation/goals/<?= $period->id ?>" class="btn btn-ghost" style="border: 1px solid var(--border);">
                    <i class="fas fa-arrow-left"></i> Danh sách Goals
                </a>
            </div>
        </div>

        <!-- 360 DIMENSIONAL SCORE COMPARISON CARDS -->
        <div class="row g-3 mt-3 pt-3" style="border-top: 1px solid var(--border);">
            <!-- Self Review -->
            <div class="col-md-3 col-sm-6">
                <div class="p-3 rounded" style="background: rgba(16, 185, 129, 0.08); border: 1px solid rgba(16, 185, 129, 0.25);">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="text-success fw-bold" style="font-size: 13px;"><i class="fas fa-user-check me-1"></i> Tự Đánh Giá (Self)</span>
                        <span class="badge bg-success" style="font-size: 10px;">Trọng số 15%</span>
                    </div>
                    <div style="font-size: 26px; font-weight: 800; color: #059669;">
                        <?= $self_review && $self_review->overall_score !== null ? number_format((float)$self_review->overall_score, 1) : '--' ?>
                        <small style="font-size: 13px; font-weight: normal; color: #059669;">/ 100</small>
                    </div>
                    <small class="text-muted" style="font-size: 11px;">
                        <?= $self_review ? 'Đã nộp lúc ' . date('d/m/Y', strtotime($self_review->submitted_at ?? 'now')) : 'Chưa nộp phiếu tự chấm' ?>
                    </small>
                </div>
            </div>

            <!-- Peer Review (Average) -->
            <div class="col-md-3 col-sm-6">
                <div class="p-3 rounded" style="background: rgba(245, 158, 11, 0.08); border: 1px solid rgba(245, 158, 11, 0.25);">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="text-warning fw-bold text-dark" style="font-size: 13px;"><i class="fas fa-user-friends me-1"></i> Đồng Nghiệp (Peers)</span>
                        <span class="badge bg-warning text-dark" style="font-size: 10px;">Trọng số 25%</span>
                    </div>
                    <div style="font-size: 26px; font-weight: 800; color: #d97706;">
                        <?= $peer_overall_avg !== null ? number_format((float)$peer_overall_avg, 1) : '--' ?>
                        <small style="font-size: 13px; font-weight: normal; color: #d97706;">/ 100</small>
                    </div>
                    <small class="text-muted" style="font-size: 11px;">
                        <?= $peer_count ?> đồng nghiệp đã nộp đánh giá chéo
                    </small>
                </div>
            </div>

            <!-- Manager Review -->
            <div class="col-md-3 col-sm-6">
                <div class="p-3 rounded" style="background: rgba(79, 70, 229, 0.08); border: 1px solid rgba(79, 70, 229, 0.25);">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="text-indigo fw-bold" style="color: #4f46e5; font-size: 13px;"><i class="fas fa-user-shield me-1"></i> Quản Lý (Manager)</span>
                        <span class="badge bg-primary" style="font-size: 10px;">Trọng số 60%</span>
                    </div>
                    <div style="font-size: 26px; font-weight: 800; color: #4338ca;">
                        <?= $manager_review && $manager_review->overall_score !== null ? number_format((float)$manager_review->overall_score, 1) : '--' ?>
                        <small style="font-size: 13px; font-weight: normal; color: #4338ca;">/ 100</small>
                    </div>
                    <small class="text-muted" style="font-size: 11px;">
                        <?= $manager_review ? 'Đã chấm lúc ' . date('d/m/Y', strtotime($manager_review->submitted_at ?? 'now')) : 'Quản lý chưa chấm điểm' ?>
                    </small>
                </div>
            </div>

            <!-- Recommended Weighted Score -->
            <div class="col-md-3 col-sm-6">
                <div class="p-3 rounded" style="background: linear-gradient(135deg, rgba(79,70,229,0.12), rgba(16,185,129,0.12)); border: 1px solid rgba(79, 70, 229, 0.3);">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="fw-bold" style="color: #1e293b; font-size: 13px;"><i class="fas fa-balance-scale text-primary me-1"></i> Khuyến Nghị 360°</span>
                        <span class="badge bg-dark" style="font-size: 10px;">Weighted Avg</span>
                    </div>
                    <div style="font-size: 26px; font-weight: 800; color: #0f172a;">
                        <?= $recommended_score !== null ? number_format((float)$recommended_score, 1) : '--' ?>
                        <small style="font-size: 13px; font-weight: normal; color: #475569;">/ 100</small>
                    </div>
                    <small class="text-muted" style="font-size: 11px;">
                        Điểm bình quân chuẩn hóa tự động
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- RADAR CHART 360 ĐA CHIỀU (Chart.js) -->
    <div class="col-lg-6">
        <div class="card h-100" style="border: 1px solid var(--border); box-shadow: 0 4px 18px rgba(0,0,0,0.04); border-radius: 12px;">
            <div class="card-header d-flex justify-content-between align-items-center" style="background: var(--bg-hover); padding: 14px 20px;">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-spider text-primary"></i>
                    <strong style="font-size: 14px; color: var(--text);">Biểu Đồ Radar So Sánh Đa Chiều 360°</strong>
                </div>
                <span class="badge bg-light text-muted border">Self vs Manager vs Peer</span>
            </div>
            <div class="card-body d-flex flex-column justify-content-center align-items-center p-3" style="min-height: 380px;">
                <div style="position: relative; width: 100%; height: 350px;">
                    <canvas id="radar360Chart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- CHI TIẾT NHẬN XÉT ĐA CHIỀU 360° -->
    <div class="col-lg-6">
        <div class="card h-100" style="border: 1px solid var(--border); box-shadow: 0 4px 18px rgba(0,0,0,0.04); border-radius: 12px;">
            <div class="card-header" style="background: var(--bg-hover); padding: 14px 20px;">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-comments text-primary"></i>
                    <strong style="font-size: 14px; color: var(--text);">Tổng Hợp Nhận Xét Từ Các Bên Đánh Giá</strong>
                </div>
            </div>
            <div class="card-body p-3" style="max-height: 400px; overflow-y: auto;">
                <!-- Nhận xét của Quản lý -->
                <div class="p-3 rounded mb-3" style="background: rgba(79, 70, 229, 0.04); border-left: 3px solid #4f46e5;">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <strong style="color: #4f46e5; font-size: 13px;"><i class="fas fa-user-shield me-1"></i> Quản lý trực tiếp nhận xét:</strong>
                        <span class="badge bg-primary"><?= $manager_review && $manager_review->overall_score !== null ? $manager_review->overall_score . ' điểm' : 'Chưa có' ?></span>
                    </div>
                    <?php if ($manager_review): ?>
                        <div class="small mt-2"><strong>Điểm mạnh:</strong> <?= nl2br(htmlspecialchars($manager_review->strengths ?: 'Chưa nhập')) ?></div>
                        <div class="small mt-1"><strong>Cần cải thiện:</strong> <?= nl2br(htmlspecialchars($manager_review->improvements ?: 'Chưa nhập')) ?></div>
                    <?php else: ?>
                        <em class="text-muted small">Quản lý chưa gửi nhận xét.</em>
                    <?php endif; ?>
                </div>

                <!-- Nhận xét của Đồng nghiệp Peer -->
                <div class="p-3 rounded mb-3" style="background: rgba(245, 158, 11, 0.04); border-left: 3px solid #f59e0b;">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <strong style="color: #d97706; font-size: 13px;"><i class="fas fa-user-friends me-1"></i> Đồng nghiệp Peer nhận xét (<?= count($peer_reviews) ?>):</strong>
                        <span class="badge bg-warning text-dark"><?= $peer_overall_avg !== null ? $peer_overall_avg . ' điểm TB' : 'Chưa có' ?></span>
                    </div>
                    <?php if (!empty($peer_reviews)): ?>
                        <?php foreach ($peer_reviews as $pr): ?>
                            <?php if ($pr->status === 'Submitted'): ?>
                                <div class="p-2 border rounded mb-2 bg-white small">
                                    <div class="fw-bold text-dark mb-1">Đồng nghiệp: <?= htmlspecialchars($pr->reviewer_name ?? 'Ẩn danh') ?> (<?= $pr->overall_score ?> điểm)</div>
                                    <?php if (!empty($pr->strengths)): ?><div><strong class="text-success">+ Thế mạnh:</strong> <?= htmlspecialchars($pr->strengths) ?></div><?php endif; ?>
                                    <?php if (!empty($pr->improvements)): ?><div><strong class="text-warning">- Gợi ý:</strong> <?= htmlspecialchars($pr->improvements) ?></div><?php endif; ?>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <em class="text-muted small">Chưa có đánh giá từ đồng nghiệp.</em>
                    <?php endif; ?>
                </div>

                <!-- Nhận xét Nhân viên Tự Đánh Giá -->
                <div class="p-3 rounded" style="background: rgba(16, 185, 129, 0.04); border-left: 3px solid #10b981;">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <strong style="color: #059669; font-size: 13px;"><i class="fas fa-user-check me-1"></i> Nhân sự tự đánh giá:</strong>
                        <span class="badge bg-success"><?= $self_review && $self_review->overall_score !== null ? $self_review->overall_score . ' điểm' : 'Chưa có' ?></span>
                    </div>
                    <?php if ($self_review): ?>
                        <div class="small mt-2"><strong>Điểm mạnh tự nhận:</strong> <?= nl2br(htmlspecialchars($self_review->strengths ?: 'Chưa nhập')) ?></div>
                        <div class="small mt-1"><strong>Mong muốn cải thiện:</strong> <?= nl2br(htmlspecialchars($self_review->improvements ?: 'Chưa nhập')) ?></div>
                        <div class="small mt-1"><strong>Ý kiến nguyện vọng:</strong> <?= nl2br(htmlspecialchars($self_review->comments ?: 'Không có')) ?></div>
                    <?php else: ?>
                        <em class="text-muted small">Nhân viên chưa nộp phiếu tự đánh giá.</em>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- FORM CHỐT ĐIỂM FINAL SCORE & XẾP LOẠI -->
<form action="<?= BASE_URL ?>/evaluation/finalScore/<?= $period->id ?>/<?= $employee->id ?>" method="POST" id="finalScoreForm">
    <input type="hidden" name="_csrf_token" value="<?= Session::generateCsrfToken() ?>">

    <!-- BẢNG SO SÁNH ĐIỂM THEO TỪNG KRA & CHỐT ĐIỂM KRA -->
    <div class="card mb-4" style="border: 1px solid var(--border); box-shadow: 0 4px 18px rgba(0,0,0,0.04); border-radius: 12px; overflow: hidden;">
        <div class="card-header d-flex justify-content-between align-items-center" style="background: var(--bg-hover); padding: 14px 20px;">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-table text-primary"></i>
                <strong style="font-size: 14px; color: var(--text);">Đối Chiếu Điểm Số Đa Chiều & Chốt Điểm Từng KRA</strong>
            </div>
            <span class="badge bg-secondary"><?= count($goals) ?> Mục tiêu KRA</span>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0" style="font-size: 13px;">
                <thead style="background: rgba(0,0,0,0.02);">
                    <tr>
                        <th style="width: 45px;" class="text-center">#</th>
                        <th style="min-width: 220px;">Mục tiêu KRA</th>
                        <th style="width: 90px;" class="text-center">Trọng số</th>
                        <th style="min-width: 220px;">Kết quả thực tế (Báo cáo)</th>
                        <th style="width: 110px;" class="text-center text-success">Tự Chấm (Self)</th>
                        <th style="width: 110px;" class="text-center text-warning">Đồng nghiệp (Peer TB)</th>
                        <th style="width: 110px;" class="text-center text-primary">Quản lý (Manager)</th>
                        <th style="width: 140px;" class="text-center bg-primary-subtle text-primary fw-bold">Điểm Chốt (Final) <span class="text-danger">*</span></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($goals)): ?>
                        <tr><td colspan="8" class="text-center py-4 text-muted">Chưa có mục tiêu KRA</td></tr>
                    <?php else: ?>
                        <?php foreach ($goals as $i => $g): ?>
                            <?php 
                                $recommendedKraFinal = $g['final_score'] !== null ? (float)$g['final_score'] : ($g['manager_score'] ?? ($g['self_score'] ?? 80));
                            ?>
                            <tr>
                                <td class="text-center text-muted fw-bold"><?= $i + 1 ?></td>
                                <td>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($g['kra_title']) ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($g['target_metric'] ?? '') ?></small>
                                </td>
                                <td class="text-center fw-bold"><?= (int)$g['weightage'] ?>%</td>
                                <td>
                                    <div class="small text-muted" style="max-height: 80px; overflow-y: auto;">
                                        <?= !empty($g['actual_achievement']) ? nl2br(htmlspecialchars($g['actual_achievement'])) : '<em class="text-secondary opacity-75">Chưa có báo cáo</em>' ?>
                                    </div>
                                </td>
                                <!-- Self -->
                                <td class="text-center fw-semibold text-success" style="font-size: 14px;">
                                    <?= $g['self_score'] !== null ? (float)$g['self_score'] : '--' ?>
                                </td>
                                <!-- Peer -->
                                <td class="text-center fw-semibold text-warning" style="font-size: 14px;">
                                    <?= $g['peer_score'] !== null ? (float)$g['peer_score'] : '--' ?>
                                </td>
                                <!-- Manager -->
                                <td class="text-center fw-semibold text-primary" style="font-size: 14px;">
                                    <?= $g['manager_score'] !== null ? (float)$g['manager_score'] : '--' ?>
                                </td>
                                <!-- Final KRA Input -->
                                <td class="text-center bg-primary-subtle">
                                    <input type="number" name="goal_final_score[<?= $g['id'] ?>]" 
                                           class="form-control form-control-sm text-center fw-bolder text-primary kra-final-input" 
                                           data-weight="<?= (int)$g['weightage'] ?>"
                                           min="0" max="100" step="0.5" 
                                           value="<?= (float)$recommendedKraFinal ?>"
                                           oninput="recalcOverallFinalScore()">
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- KHỐI CHỐT ĐIỂM TỔNG THỂ, XẾP LOẠI & NHẬN XÉT HỘI ĐỒNG -->
    <div class="card mb-4" style="border: 1px solid var(--border); box-shadow: 0 4px 18px rgba(0,0,0,0.04); border-radius: 12px;">
        <div class="card-header" style="background: var(--bg-hover); padding: 14px 20px;">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-stamp text-primary"></i>
                <strong style="font-size: 14px; color: var(--text);">Phê Duyệt Điểm Tổng Kết & Xếp Hạng Đánh Giá</strong>
            </div>
        </div>

        <div class="card-body p-4">
            <div class="row g-4">
                <!-- Điểm Final Tổng Kết & Xếp Loại -->
                <div class="col-md-6">
                    <label class="form-label" style="font-size: 13px; font-weight: 700; color: var(--text);">
                        Điểm Chốt Cuối Cùng (Overall Final Score - Thang 100) <span class="text-danger">*</span>
                    </label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-primary text-white"><i class="fas fa-trophy"></i></span>
                        <input type="number" name="final_score" id="mainFinalScoreInput" step="0.1" min="0" max="100" required
                               class="form-control fw-bolder text-primary" style="font-size: 24px;" 
                               value="<?= $currentFinalScore ?>" oninput="updateGradeByScore(this.value)">
                        <span class="input-group-text fw-bold">/ 100</span>
                    </div>
                    <div class="form-text mt-1 text-muted">Tự động tính theo điểm bình quân trọng số các KRA đã chốt.</div>
                </div>

                <div class="col-md-6">
                    <label class="form-label" style="font-size: 13px; font-weight: 700; color: var(--text);">
                        Xếp Loại Năng Lực (Performance Grade) <span class="text-danger">*</span>
                    </label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-dark text-white"><i class="fas fa-medal"></i></span>
                        <select name="overall_grade" id="overallGradeSelect" class="form-select fw-bolder" required style="font-size: 18px;">
                            <option value="A" <?= $currentGrade === 'A' ? 'selected' : '' ?>>🟢 Hạng A – Xuất sắc (>= 90 điểm)</option>
                            <option value="B" <?= $currentGrade === 'B' ? 'selected' : '' ?>>🔵 Hạng B – Tốt / Đạt chỉ tiêu (75 - 89.9 điểm)</option>
                            <option value="C" <?= $currentGrade === 'C' ? 'selected' : '' ?>>🟡 Hạng C – Đạt yêu cầu cơ bản (60 - 74.9 điểm)</option>
                            <option value="D" <?= $currentGrade === 'D' ? 'selected' : '' ?>>🔴 Hạng D – Cần cải thiện (< 60 điểm)</option>
                        </select>
                    </div>
                </div>

                <!-- Điểm mạnh ghi nhận chính thức -->
                <div class="col-md-6">
                    <label class="form-label" style="font-size: 13px; font-weight: 700; color: var(--text);">
                        <i class="fas fa-check-circle text-success me-1"></i> Thành tích & Điểm mạnh được công nhận chính thức
                    </label>
                    <textarea name="strengths" class="form-control" rows="4" 
                              placeholder="Ghi nhận thành tích thi công, năng lực chuyên môn, sáng kiến cải tiến..."><?= htmlspecialchars($existing_eval->strengths ?? ($manager_review->strengths ?? '')) ?></textarea>
                </div>

                <!-- Định hướng đào tạo phát triển -->
                <div class="col-md-6">
                    <label class="form-label" style="font-size: 13px; font-weight: 700; color: var(--text);">
                        <i class="fas fa-lightbulb text-warning me-1"></i> Điểm cần đào tạo & Kế hoạch phát triển
                    </label>
                    <textarea name="improvements" class="form-control" rows="4" 
                              placeholder="Yêu cầu tham gia các khóa an toàn HSE, quản lý dự án, nâng cao tay nghề..."><?= htmlspecialchars($existing_eval->improvements ?? ($manager_review->improvements ?? '')) ?></textarea>
                </div>

                <!-- Ghi chú Hội đồng / Ban Giám đốc -->
                <div class="col-12">
                    <label class="form-label" style="font-size: 13px; font-weight: 700; color: var(--text);">
                        <i class="fas fa-sticky-note text-secondary me-1"></i> Ghi chú của Ban Giám đốc / Quyết định liên quan
                    </label>
                    <textarea name="notes" class="form-control" rows="3" 
                              placeholder="Ghi chú về đề xuất khen thưởng, nâng ngạch lương hoặc luân chuyển công tác..."><?= htmlspecialchars($existing_eval->notes ?? '') ?></textarea>
                </div>
            </div>
        </div>
    </div>

    <!-- BUTTONS -->
    <div class="d-flex justify-content-between align-items-center mb-5">
        <a href="<?= BASE_URL ?>/evaluation/goals/<?= $period->id ?>" class="btn btn-ghost" style="border: 1px solid var(--border);">
            <i class="fas fa-times me-1"></i> Hủy bỏ
        </a>
        <button type="submit" class="btn btn-primary btn-lg px-4" style="box-shadow: 0 4px 14px rgba(79,70,229,0.3); font-weight: 700;">
            <i class="fas fa-stamp me-2"></i> Xác Nhận & Chốt Điểm Đánh Giá 360°
        </button>
    </div>
</form>

<!-- CHART.JS INTEGRATION -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Radar Chart Setup
    const ctx = document.getElementById('radar360Chart');
    if (ctx) {
        new Chart(ctx, {
            type: 'radar',
            data: {
                labels: <?= $radarLabelsJson ?>,
                datasets: [
                    {
                        label: 'Quản Lý (Manager)',
                        data: <?= $radarManagerJson ?>,
                        backgroundColor: 'rgba(79, 70, 229, 0.2)',
                        borderColor: '#4f46e5',
                        pointBackgroundColor: '#4f46e5',
                        pointBorderColor: '#fff',
                        borderWidth: 2
                    },
                    {
                        label: 'Đồng Nghiệp (Peers)',
                        data: <?= $radarPeerJson ?>,
                        backgroundColor: 'rgba(245, 158, 11, 0.2)',
                        borderColor: '#f59e0b',
                        pointBackgroundColor: '#f59e0b',
                        pointBorderColor: '#fff',
                        borderWidth: 2
                    },
                    {
                        label: 'Tự Chấm (Self)',
                        data: <?= $radarSelfJson ?>,
                        backgroundColor: 'rgba(16, 185, 129, 0.2)',
                        borderColor: '#10b981',
                        pointBackgroundColor: '#10b981',
                        pointBorderColor: '#fff',
                        borderWidth: 2
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    r: {
                        angleLines: { color: 'rgba(0, 0, 0, 0.08)' },
                        grid: { color: 'rgba(0, 0, 0, 0.05)' },
                        suggestedMin: 50,
                        suggestedMax: 100,
                        ticks: { stepSize: 10, font: { size: 10 } },
                        pointLabels: { font: { size: 11, weight: '600' } }
                    }
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 12, font: { size: 12 } }
                    }
                }
            }
        });
    }
});

function recalcOverallFinalScore() {
    const inputs = document.querySelectorAll('.kra-final-input');
    let weightedSum = 0;
    let totalWeight = 0;

    inputs.forEach(inp => {
        const score = parseFloat(inp.value) || 0;
        const weight = parseFloat(inp.dataset.weight) || 0;
        weightedSum += (score * (weight / 100));
        totalWeight += weight;
    });

    if (totalWeight > 0) {
        const finalCalculated = Math.round(weightedSum * 10) / 10;
        document.getElementById('mainFinalScoreInput').value = finalCalculated;
        updateGradeByScore(finalCalculated);
    }
}

function updateGradeByScore(score) {
    const s = parseFloat(score) || 0;
    const select = document.getElementById('overallGradeSelect');
    if (s >= 90) select.value = 'A';
    else if (s >= 75) select.value = 'B';
    else if (s >= 60) select.value = 'C';
    else select.value = 'D';
}
</script>
