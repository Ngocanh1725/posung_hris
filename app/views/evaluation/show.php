<?php
/**
 * View: evaluation/show.php – Chi tiết Chu kỳ Đánh giá & Danh sách Chấm điểm Nhân viên
 */

$statusBadges = [
    'Active' => ['Đang diễn ra',  'badge bg-primary text-white', 'fas fa-spinner fa-spin'],
    'Draft'  => ['Bản nháp',      'badge bg-warning text-dark', 'fas fa-pencil-alt'],
    'Closed' => ['Đã khóa',       'badge bg-secondary text-white', 'fas fa-lock'],
];

$gradeColors = [
    'A' => ['badge bg-success text-white', 'Xuất sắc (A)', '#10b981'],
    'B' => ['badge bg-primary text-white', 'Tốt (B)', '#3b82f6'],
    'C' => ['badge bg-warning text-dark',  'Đạt (C)', '#f59e0b'],
    'D' => ['badge bg-danger text-white',  'Cần cải thiện (D)', '#ef4444'],
];

// Tính toán thống kê nhanh
$totalEmps = count($employees);
$evaluatedCount = 0;
$totalScore = 0;

foreach ($employees as $e) {
    if (!empty($e['eval_id']) && $e['final_score'] !== null) {
        $evaluatedCount++;
        $totalScore += (float)$e['final_score'];
    }
}

$pendingCount = $totalEmps - $evaluatedCount;
$avgScore = $evaluatedCount > 0 ? round($totalScore / $evaluatedCount, 1) : 0;
$completionPct = $totalEmps > 0 ? round(($evaluatedCount / $totalEmps) * 100) : 0;
?>

<!-- BREADCRUMB -->
<div class="breadcrumb-nav mb-3" style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: var(--text-muted);">
    <a href="<?= BASE_URL ?>/evaluation" style="color: var(--primary); text-decoration: none;">
        <i class="fas fa-award"></i> Quản lý Đánh giá KPI
    </a>
    <i class="fas fa-chevron-right" style="font-size: 10px;"></i>
    <span>Chi tiết Chu kỳ: <?= htmlspecialchars($period->name) ?></span>
</div>

<!-- PERIOD HEADER CARD -->
<div class="card mb-4" style="border: 1px solid var(--border); box-shadow: 0 4px 18px rgba(0,0,0,0.04);">
    <div class="card-body" style="padding: 24px;">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div class="d-flex gap-3 align-items-start">
                <div style="width: 56px; height: 56px; border-radius: 14px; background: linear-gradient(135deg, #4f46e5, #6366f1); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 24px; box-shadow: 0 6px 16px rgba(79,70,229,0.3); flex-shrink: 0;">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div>
                    <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                        <h2 style="font-size: 20px; font-weight: 700; margin: 0; color: var(--text);">
                            <?= htmlspecialchars($period->name) ?>
                        </h2>
                        <?php 
                            $badge = $statusBadges[$period->status] ?? ['Không xác định', 'badge bg-secondary', ''];
                        ?>
                        <span class="<?= $badge[1] ?>" style="font-size: 12px; font-weight: 600; padding: 4px 10px; border-radius: 20px;">
                            <i class="<?= $badge[2] ?>"></i> <?= $badge[0] ?>
                        </span>
                    </div>

                    <div style="font-size: 13px; color: var(--text-muted); display: flex; gap: 16px; flex-wrap: wrap; margin-top: 6px;">
                        <span><i class="fas fa-clipboard-list text-primary"></i> Mẫu: <strong><?= htmlspecialchars($period->template_name ?? 'Mặc định') ?></strong></span>
                        <span><i class="fas fa-calendar-alt text-primary"></i> Thời gian: <strong><?= date('d/m/Y', strtotime($period->start_date)) ?></strong> – <strong><?= date('d/m/Y', strtotime($period->end_date)) ?></strong></span>
                        <span><i class="fas fa-building text-primary"></i> Phạm vi: <strong><?= !empty($period->dept_name) ? htmlspecialchars($period->dept_name) : 'Toàn bộ Công ty' ?></strong></span>
                    </div>

                    <?php if (!empty($period->notes)): ?>
                        <div style="margin-top: 10px; font-size: 13px; background: var(--bg-hover); padding: 8px 12px; border-radius: 6px; border-left: 3px solid var(--primary); color: var(--text);">
                            <i class="fas fa-info-circle text-primary"></i> <?= nl2br(htmlspecialchars($period->notes)) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ACTION BUTTONS -->
            <div class="d-flex gap-2 flex-wrap">
                <a href="<?= BASE_URL ?>/evaluation/goals/<?= $period->id ?>" class="btn btn-primary" style="font-weight: 600; box-shadow: 0 4px 14px rgba(79,70,229,0.3);">
                    <i class="fas fa-bullseye me-1"></i> Quản lý Goals KRA
                </a>
                <a href="<?= BASE_URL ?>/evaluation/assignPeerReviewers/<?= $period->id ?>" class="btn btn-outline-primary" style="font-weight: 600;">
                    <i class="fas fa-users-cog me-1"></i> Phân công Peer 360°
                </a>
                <a href="<?= BASE_URL ?>/evaluation/analytics/<?= $period->id ?>" class="btn btn-outline-info" style="font-weight: 600;">
                    <i class="fas fa-chart-line me-1"></i> Analytics 360°
                </a>
                <a href="<?= BASE_URL ?>/evaluation/summary/<?= $period->id ?>" class="btn btn-ghost" style="border: 1px solid var(--border);">
                    <i class="fas fa-chart-pie me-1"></i> Báo cáo Radar
                </a>
                <a href="<?= BASE_URL ?>/evaluation" class="btn btn-ghost" style="border: 1px solid var(--border);">
                    <i class="fas fa-arrow-left"></i> Danh sách
                </a>
            </div>
        </div>

        <!-- PROGRESS & QUICK STATS ROW -->
        <div class="row g-3 mt-3 pt-3" style="border-top: 1px solid var(--border);">
            <div class="col-md-3 col-sm-6">
                <div style="background: var(--bg-hover); padding: 12px 16px; border-radius: 10px;">
                    <div class="text-muted" style="font-size: 12px;">Tổng nhân sự trong chu kỳ</div>
                    <div style="font-size: 20px; font-weight: 700; color: var(--text);"><?= $totalEmps ?> <span style="font-size: 12px; font-weight: normal;">người</span></div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div style="background: rgba(16, 185, 129, 0.08); padding: 12px 16px; border-radius: 10px; border: 1px solid rgba(16, 185, 129, 0.2);">
                    <div class="text-muted" style="font-size: 12px;">Đã hoàn thành đánh giá</div>
                    <div style="font-size: 20px; font-weight: 700; color: #10b981;"><?= $evaluatedCount ?> <span style="font-size: 12px; font-weight: normal; color: var(--text-muted);">/ <?= $totalEmps ?> (<?= $completionPct ?>%)</span></div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div style="background: rgba(245, 158, 11, 0.08); padding: 12px 16px; border-radius: 10px; border: 1px solid rgba(245, 158, 11, 0.2);">
                    <div class="text-muted" style="font-size: 12px;">Đang chờ chấm điểm</div>
                    <div style="font-size: 20px; font-weight: 700; color: #d97706;"><?= $pendingCount ?> <span style="font-size: 12px; font-weight: normal; color: var(--text-muted);">người</span></div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div style="background: rgba(79, 70, 229, 0.08); padding: 12px 16px; border-radius: 10px; border: 1px solid rgba(79, 70, 229, 0.2);">
                    <div class="text-muted" style="font-size: 12px;">Điểm trung bình chu kỳ</div>
                    <div style="font-size: 20px; font-weight: 700; color: var(--primary);"><?= $avgScore > 0 ? $avgScore : '---' ?> <span style="font-size: 12px; font-weight: normal; color: var(--text-muted);">/ 100</span></div>
                </div>
            </div>
        </div>

        <!-- Progress Bar -->
        <div style="margin-top: 14px;">
            <div class="d-flex justify-content-between align-items-center mb-1" style="font-size: 12px;">
                <span class="text-muted">Tiến độ hoàn thành chu kỳ:</span>
                <span style="font-weight: 700; color: var(--primary);"><?= $completionPct ?>%</span>
            </div>
            <div class="progress" style="height: 8px; border-radius: 10px; background: var(--border);">
                <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" 
                     style="width: <?= $completionPct ?>%; background: linear-gradient(90deg, #4f46e5, #10b981);" 
                     aria-valuenow="<?= $completionPct ?>" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
        </div>
    </div>
</div>

<!-- FILTER BAR -->
<div class="card mb-3" style="border: 1px solid var(--border);">
    <div class="card-body" style="padding: 14px 18px;">
        <form method="GET" action="<?= BASE_URL ?>/evaluation/show/<?= $period->id ?>" class="row g-2 align-items-center">
            <!-- Search -->
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text" style="background: var(--bg-hover); border-color: var(--border);">
                        <i class="fas fa-search text-muted"></i>
                    </span>
                    <input type="text" name="search" class="form-control" 
                           placeholder="Tìm kiếm mã NV, họ tên nhân sự..." 
                           value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
                </div>
            </div>

            <!-- Dept -->
            <div class="col-md-3">
                <select name="dept" class="form-select">
                    <option value="">-- Tất cả Phòng ban --</option>
                    <?php foreach ($departments as $dept): ?>
                        <option value="<?= $dept->id ?>" <?= ($filters['dept_id'] == $dept->id) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($dept->dept_name) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Status -->
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">-- Trạng thái --</option>
                    <option value="Not_Started" <?= ($filters['status'] === 'Not_Started') ? 'selected' : '' ?>>Chưa chấm</option>
                    <option value="Self_Evaluated" <?= ($filters['status'] === 'Self_Evaluated') ? 'selected' : '' ?>>Đã tự chấm</option>
                    <option value="Approved" <?= ($filters['status'] === 'Approved') ? 'selected' : '' ?>>Đã chấm hoàn tất</option>
                </select>
            </div>

            <!-- Grade -->
            <div class="col-md-2">
                <select name="grade" class="form-select">
                    <option value="">-- Xếp loại --</option>
                    <option value="A" <?= ($filters['grade'] === 'A') ? 'selected' : '' ?>>Hạng A (Xuất sắc)</option>
                    <option value="B" <?= ($filters['grade'] === 'B') ? 'selected' : '' ?>>Hạng B (Tốt)</option>
                    <option value="C" <?= ($filters['grade'] === 'C') ? 'selected' : '' ?>>Hạng C (Đạt)</option>
                    <option value="D" <?= ($filters['grade'] === 'D') ? 'selected' : '' ?>>Hạng D (Cần cải thiện)</option>
                </select>
            </div>

            <!-- Filter Buttons -->
            <div class="col-md-1 d-flex gap-1">
                <button type="submit" class="btn btn-primary w-100" title="Lọc">
                    <i class="fas fa-filter"></i>
                </button>
                <?php if (!empty($filters['search']) || !empty($filters['dept_id']) || !empty($filters['status']) || !empty($filters['grade'])): ?>
                    <a href="<?= BASE_URL ?>/evaluation/show/<?= $period->id ?>" class="btn btn-ghost" title="Xóa bộ lọc" style="border: 1px solid var(--border);">
                        <i class="fas fa-redo"></i>
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- EMPLOYEE EVALUATION TABLE -->
<div class="card" style="border: 1px solid var(--border); box-shadow: 0 2px 12px rgba(0,0,0,0.03);">
    <div class="card-header d-flex justify-content-between align-items-center" style="padding: 14px 20px; background: transparent; border-bottom: 1px solid var(--border);">
        <h5 style="margin: 0; font-size: 15px; font-weight: 700; color: var(--text);">
            <i class="fas fa-users text-primary"></i> Danh Sách Nhân Sự Cần Đánh Giá (<?= count($employees) ?> người)
        </h5>
        <div style="font-size: 13px; color: var(--text-muted);">
            Thang điểm chuẩn: <strong>1 - 5</strong> • Quy đổi: <strong>100 điểm</strong>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size: 13.5px;">
            <thead style="background: var(--bg-hover); color: var(--text-muted); font-size: 12px; text-transform: uppercase;">
                <tr>
                    <th style="width: 50px; text-align: center;">STT</th>
                    <th style="width: 260px;">Nhân sự</th>
                    <th>Phòng ban & Vị trí</th>
                    <th style="text-align: center; width: 130px;">Trạng thái</th>
                    <th style="text-align: center; width: 90px;">Điểm KPI</th>
                    <th style="text-align: center; width: 90px;">Điểm NL</th>
                    <th style="text-align: center; width: 110px;">Tổng điểm</th>
                    <th style="text-align: center; width: 100px;">Xếp loại</th>
                    <th style="text-align: right; width: 160px;">Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($employees)): ?>
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <i class="fas fa-user-slash fa-3x mb-3 text-secondary" style="opacity: 0.5;"></i>
                            <div>Không có nhân sự nào phù hợp với bộ lọc tìm kiếm.</div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php $idx = 1; foreach ($employees as $emp): ?>
                        <tr>
                            <td style="text-align: center; color: var(--text-muted);"><?= $idx++ ?></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div style="width: 38px; height: 38px; border-radius: 50%; background: #e0e7ff; color: #4338ca; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 14px; flex-shrink: 0; overflow: hidden;">
                                        <?php if (!empty($emp['avatar_path'])): ?>
                                            <img src="<?= BASE_URL . '/' . htmlspecialchars($emp['avatar_path']) ?>" alt="" style="width: 100%; height: 100%; object-fit: cover;">
                                        <?php else: ?>
                                            <?= mb_strtoupper(mb_substr($emp['full_name'], 0, 1, 'UTF-8'), 'UTF-8') ?>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <div style="font-weight: 600; color: var(--text);">
                                            <a href="<?= BASE_URL ?>/employee/view/<?= $emp['id'] ?>" target="_blank" style="color: inherit; text-decoration: none;">
                                                <?= htmlspecialchars($emp['full_name']) ?>
                                            </a>
                                        </div>
                                        <div style="font-size: 12px; color: var(--text-muted);">
                                            Mã: <span class="badge bg-light text-dark" style="border: 1px solid var(--border);"><?= htmlspecialchars($emp['emp_code']) ?></span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div style="font-weight: 500; color: var(--text);"><?= htmlspecialchars($emp['dept_name'] ?? 'Chưa gán') ?></div>
                                <div style="font-size: 12px; color: var(--text-muted);"><?= htmlspecialchars($emp['pos_title'] ?? '---') ?></div>
                            </td>
                            <td style="text-align: center;">
                                <?php if (empty($emp['eval_id'])): ?>
                                    <span class="badge bg-secondary" style="font-size: 11px; padding: 4px 8px; border-radius: 12px;">Chưa chấm</span>
                                <?php elseif ($emp['eval_status'] === 'Self_Evaluated'): ?>
                                    <span class="badge bg-warning text-dark" style="font-size: 11px; padding: 4px 8px; border-radius: 12px;">Đã tự chấm</span>
                                <?php else: ?>
                                    <span class="badge bg-success text-white" style="font-size: 11px; padding: 4px 8px; border-radius: 12px;">Đã hoàn thành</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center; font-weight: 600; color: var(--text);">
                                <?= ($emp['kpi_score'] !== null) ? number_format((float)$emp['kpi_score'], 1) : '---' ?>
                            </td>
                            <td style="text-align: center; font-weight: 600; color: var(--text);">
                                <?= ($emp['competency_score'] !== null) ? number_format((float)$emp['competency_score'], 1) : '---' ?>
                            </td>
                            <td style="text-align: center;">
                                <?php if ($emp['final_score'] !== null): ?>
                                    <?php
                                        $fScore = (float)$emp['final_score'];
                                        $scoreColor = $fScore >= 85 ? '#10b981' : ($fScore >= 70 ? '#3b82f6' : ($fScore >= 50 ? '#f59e0b' : '#ef4444'));
                                    ?>
                                    <span style="font-size: 15px; font-weight: 700; color: <?= $scoreColor ?>;">
                                        <?= number_format($fScore, 1) ?>
                                    </span>
                                    <span style="font-size: 11px; color: var(--text-muted);">/100</span>
                                <?php else: ?>
                                    <span class="text-muted">---</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center;">
                                <?php if (!empty($emp['overall_grade']) && isset($gradeColors[$emp['overall_grade']])): ?>
                                    <span class="<?= $gradeColors[$emp['overall_grade']][0] ?>" style="font-size: 12px; font-weight: 700; padding: 4px 10px; border-radius: 20px;">
                                        <?= $emp['overall_grade'] ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">---</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: right;">
                                <div class="d-flex justify-content-end gap-1">
                                    <a href="<?= BASE_URL ?>/evaluation/setGoals/<?= $period->id ?>/<?= $emp['id'] ?>" 
                                       class="btn btn-sm btn-outline-info" 
                                       title="Thiết lập Goals KRA">
                                        <i class="fas fa-bullseye"></i>
                                    </a>
                                    <a href="<?= BASE_URL ?>/evaluation/review360/<?= $period->id ?>/<?= $emp['id'] ?>" 
                                       class="btn btn-sm btn-outline-warning" 
                                       title="Đánh giá 360°">
                                        <i class="fas fa-star-half-alt"></i>
                                    </a>
                                    <a href="<?= BASE_URL ?>/evaluation/finalScore/<?= $period->id ?>/<?= $emp['id'] ?>" 
                                       class="btn btn-sm btn-outline-primary" 
                                       title="Chốt điểm 360° & Radar Chart">
                                        <i class="fas fa-stamp"></i>
                                    </a>
                                    <a href="<?= BASE_URL ?>/evaluation/evaluate/<?= $period->id ?>/<?= $emp['id'] ?>" 
                                       class="btn btn-sm <?= empty($emp['eval_id']) ? 'btn-primary' : 'btn-outline-secondary' ?>"
                                       title="Chấm điểm tiêu chuẩn">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
