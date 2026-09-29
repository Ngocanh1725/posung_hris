<?php
/**
 * View: evaluation/index.php – Dashboard Chu kỳ Đánh giá Năng lực & KPI
 */

$statusBadges = [
    'Active' => ['Đang diễn ra',  'badge bg-primary text-white', 'fas fa-spinner fa-spin'],
    'Draft'  => ['Bản nháp',      'badge bg-warning text-dark', 'fas fa-pencil-alt'],
    'Closed' => ['Đã đóng / Khóa','badge bg-secondary text-white', 'fas fa-lock'],
];
?>

<!-- KPI CARDS -->
<div class="kpi-row" style="margin-bottom: 24px;">
    <div class="kpi-card" onclick="window.location.href='<?= BASE_URL ?>/evaluation'" style="cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='none'">
        <div class="kpi-icon" style="background: linear-gradient(135deg, #6366f1, #4f46e5);"><i class="fas fa-calendar-check"></i></div>
        <div class="kpi-body">
            <div class="kpi-value"><?= (int)($stats['total_periods'] ?? 0) ?></div>
            <div class="kpi-label">Tổng chu kỳ đánh giá</div>
        </div>
    </div>

    <div class="kpi-card" onclick="window.location.href='<?= BASE_URL ?>/evaluation?status=Active'" style="cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='none'">
        <div class="kpi-icon" style="background: linear-gradient(135deg, #0284c7, #38bdf8);"><i class="fas fa-play"></i></div>
        <div class="kpi-body">
            <div class="kpi-value"><?= (int)($stats['active_periods'] ?? 0) ?></div>
            <div class="kpi-label">Chu kỳ đang mở</div>
        </div>
    </div>

    <div class="kpi-card" style="transition: transform 0.2s;">
        <div class="kpi-icon" style="background: linear-gradient(135deg, #10b981, #059669);"><i class="fas fa-user-check"></i></div>
        <div class="kpi-body">
            <div class="kpi-value"><?= (int)($stats['total_evaluations'] ?? 0) ?></div>
            <div class="kpi-label">Lượt nhân sự đã đánh giá</div>
        </div>
    </div>

    <div class="kpi-card" style="transition: transform 0.2s;">
        <div class="kpi-icon" style="background: linear-gradient(135deg, #f59e0b, #d97706);"><i class="fas fa-chart-line"></i></div>
        <div class="kpi-body">
            <div class="kpi-value"><?= $stats['overall_avg_score'] ?? '---' ?> <span style="font-size: 0.8rem; font-weight: normal;">/ 100</span></div>
            <div class="kpi-label">Điểm trung bình toàn công ty</div>
        </div>
    </div>

    <div class="kpi-card" style="transition: transform 0.2s;">
        <div class="kpi-icon" style="background: linear-gradient(135deg, #ec4899, #be185d);"><i class="fas fa-medal"></i></div>
        <div class="kpi-body">
            <div class="kpi-value text-success"><?= (int)($stats['grade_a_count'] ?? 0) ?> <span style="font-size: 0.8rem; color: var(--text-muted); font-weight: normal;">Xuất sắc (A)</span></div>
            <div class="kpi-label">Phân loại: B: <?= (int)($stats['grade_b_count'] ?? 0) ?> • C: <?= (int)($stats['grade_c_count'] ?? 0) ?> • D: <?= (int)($stats['grade_d_count'] ?? 0) ?></div>
        </div>
    </div>
</div>

<!-- ACTIONS TOOLBAR -->
<div style="display: flex; justify-content: space-between; align-items: center; gap: 12px; margin-bottom: 20px; flex-wrap: wrap;">
    <div class="d-flex gap-2">
        <a href="<?= BASE_URL ?>/evaluation/createPeriod" class="btn btn-primary" style="box-shadow: 0 4px 14px rgba(79, 70, 229, 0.3);">
            <i class="fas fa-plus"></i> Tạo Chu kỳ Đánh giá Mới
        </a>
        <a href="<?= BASE_URL ?>/evaluation/templates" class="btn btn-ghost" style="border: 1px solid var(--border);">
            <i class="fas fa-sliders-h text-primary"></i> Quản lý Mẫu & Tiêu chí KPI
        </a>
    </div>
    <div>
        <span class="text-muted" style="font-size: 13px;">
            <i class="fas fa-info-circle"></i> Đang hiển thị <strong><?= count($periods) ?></strong> chu kỳ đánh giá
        </span>
    </div>
</div>

<!-- BỘ LỌC CHU KỲ -->
<div class="panel mb-4" style="border: none; box-shadow: 0 4px 15px rgba(0,0,0,0.03);">
    <div class="panel-header" style="background: var(--bg-card); border-bottom: 1px solid var(--border);">
        <h3 style="font-size: 15px; font-weight: 700; margin: 0; color: var(--text-heading);">
            <i class="fas fa-filter text-primary"></i> Bộ lọc & Tìm kiếm Chu kỳ Đánh giá
        </h3>
    </div>
    <div class="panel-body">
        <form method="GET" action="<?= BASE_URL ?>/evaluation">
            <div style="display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap;">
                <div class="form-group" style="flex: 2; min-width: 220px; margin-bottom: 0;">
                    <label class="form-label-sm" style="font-weight: 600; font-size: 12px;">Từ khóa</label>
                    <input type="text" name="search" class="form-control" value="<?= h($filters['search']) ?>" placeholder="Tên chu kỳ, mẫu đánh giá...">
                </div>

                <div class="form-group" style="flex: 1; min-width: 140px; margin-bottom: 0;">
                    <label class="form-label-sm" style="font-weight: 600; font-size: 12px;">Năm</label>
                    <select name="year" class="form-control">
                        <option value="">-- Tất cả năm --</option>
                        <?php for($y = (int)date('Y') + 1; $y >= (int)date('Y') - 3; $y--): ?>
                            <option value="<?= $y ?>" <?= ($filters['year'] == $y) ? 'selected' : '' ?>>Năm <?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div class="form-group" style="flex: 1; min-width: 150px; margin-bottom: 0;">
                    <label class="form-label-sm" style="font-weight: 600; font-size: 12px;">Trạng thái</label>
                    <select name="status" class="form-control">
                        <option value="">-- Tất cả trạng thái --</option>
                        <?php foreach($statusBadges as $k => $v): ?>
                            <option value="<?= $k ?>" <?= ($filters['status'] === $k) ? 'selected' : '' ?>><?= $v[0] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="d-flex gap-2" style="margin-bottom: 0;">
                    <button type="submit" class="btn btn-primary" style="min-width: 90px;">
                        <i class="fas fa-search"></i> Lọc
                    </button>
                    <a href="<?= BASE_URL ?>/evaluation" class="btn btn-ghost" title="Xóa bộ lọc">
                        <i class="fas fa-redo"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- DANH SÁCH CHU KỲ -->
<div class="panel" style="border: none; box-shadow: 0 4px 20px rgba(0,0,0,0.04);">
    <div class="panel-header d-flex justify-content-between align-items-center" style="background: var(--bg-card); border-bottom: 1px solid var(--border);">
        <h3 style="font-size: 15px; font-weight: 700; margin: 0; color: var(--text-heading);">
            <i class="fas fa-tasks text-primary"></i> Danh sách Chu kỳ Đánh giá Hiệu suất (<?= count($periods) ?>)
        </h3>
    </div>
    <div class="panel-body p-0">
        <?php if (empty($periods)): ?>
            <div class="text-center p-5">
                <div style="font-size: 48px; color: var(--text-muted); margin-bottom: 12px;">
                    <i class="fas fa-clipboard-check"></i>
                </div>
                <h4 style="font-size: 16px; font-weight: 700; color: var(--text-heading);">Chưa có chu kỳ đánh giá nào</h4>
                <p class="text-muted" style="font-size: 13px; max-width: 420px; margin: 0 auto 16px;">
                    Khởi tạo chu kỳ đánh giá để định kỳ đo lường KPI, năng lực chuyên môn và đề xuất khen thưởng / tăng lương cho CBNV.
                </p>
                <a href="<?= BASE_URL ?>/evaluation/createPeriod" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Khởi tạo chu kỳ ngay
                </a>
            </div>
        <?php else: ?>
            <div class="table-wrapper">
                <table class="table-hover">
                    <thead>
                        <tr>
                            <th style="width: 40px; text-align: center;">STT</th>
                            <th style="min-width: 220px;">Tên Chu kỳ Đánh giá</th>
                            <th style="min-width: 180px;">Mẫu Đánh giá Áp dụng</th>
                            <th style="min-width: 140px;">Thời gian</th>
                            <th style="min-width: 130px;">Phạm vi áp dụng</th>
                            <th style="min-width: 150px;">Tiến độ hoàn thành</th>
                            <th style="min-width: 90px; text-align: center;">Điểm TB</th>
                            <th style="min-width: 110px; text-align: center;">Trạng thái</th>
                            <th style="width: 130px; text-align: center;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($periods as $idx => $p): 
                            $badge = $statusBadges[$p['status']] ?? ['Không rõ', 'badge bg-secondary', 'fas fa-question'];
                            $rate = (int)$p['completion_rate'];
                        ?>
                            <tr>
                                <td style="text-align: center; color: var(--text-muted); font-size: 13px;"><?= $idx + 1 ?></td>
                                <td>
                                    <a href="<?= BASE_URL ?>/evaluation/show/<?= $p['id'] ?>" style="font-weight: 700; color: var(--primary); text-decoration: none; font-size: 14px;">
                                        <?= h($p['name']) ?>
                                    </a>
                                    <?php if (!empty($p['notes'])): ?>
                                        <div class="text-muted" style="font-size: 11px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 260px;">
                                            <?= h($p['notes']) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="font-weight: 600; font-size: 13px; color: var(--text-heading);">
                                        <i class="fas fa-file-alt text-muted"></i> <?= h($p['template_name'] ?: 'Mặc định') ?>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-size: 12px; font-weight: 500;">
                                        <i class="far fa-calendar-alt text-primary"></i> <?= fmtDate($p['start_date']) ?>
                                    </div>
                                    <div class="text-muted" style="font-size: 11px;">
                                        đến <?= fmtDate($p['end_date']) ?>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-size: 12px; font-weight: 600;">
                                        <i class="fas fa-sitemap text-muted"></i> <?= h($p['dept_name'] ?: 'Toàn công ty') ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-between align-items-center mb-1" style="font-size: 11px;">
                                        <span><strong><?= (int)$p['evaluated_count'] ?></strong> / <?= (int)$p['total_eligible'] ?> người</span>
                                        <span class="fw-bold <?= $rate >= 80 ? 'text-success' : 'text-primary' ?>"><?= $rate ?>%</span>
                                    </div>
                                    <div style="height: 6px; background: rgba(0,0,0,0.06); border-radius: 6px; overflow: hidden;">
                                        <div style="width: <?= $rate ?>%; height: 100%; background: linear-gradient(90deg, var(--primary), var(--accent)); border-radius: 6px;"></div>
                                    </div>
                                </td>
                                <td style="text-align: center; font-weight: 800; font-size: 14px; color: var(--primary);">
                                    <?= $p['avg_score'] ? $p['avg_score'] : '<span class="text-muted font-weight-normal">--</span>' ?>
                                </td>
                                <td style="text-align: center;">
                                    <span class="<?= $badge[1] ?>" style="font-size: 11px; padding: 5px 10px; border-radius: 6px;">
                                        <i class="<?= $badge[2] ?>"></i> <?= $badge[0] ?>
                                    </span>
                                </td>
                                <td style="text-align: center;">
                                    <div class="d-flex justify-content-center gap-1">
                                        <a href="<?= BASE_URL ?>/evaluation/goals/<?= $p['id'] ?>" class="btn btn-outline-info btn-sm" title="Mục tiêu KRA & 360°" style="padding: 4px 8px; font-size: 12px;">
                                            <i class="fas fa-bullseye"></i>
                                        </a>
                                        <a href="<?= BASE_URL ?>/evaluation/show/<?= $p['id'] ?>" class="btn btn-primary btn-sm" title="Danh sách đánh giá nhân viên" style="padding: 4px 8px; font-size: 12px;">
                                            <i class="fas fa-user-edit"></i> Chấm điểm
                                        </a>
                                        <a href="<?= BASE_URL ?>/evaluation/analytics/<?= $p['id'] ?>" class="btn btn-outline-primary btn-sm" title="Dashboard Phân tích 360°" style="padding: 4px 8px; font-size: 12px;">
                                            <i class="fas fa-chart-line"></i>
                                        </a>
                                        <a href="<?= BASE_URL ?>/evaluation/summary/<?= $p['id'] ?>" class="btn btn-ghost btn-sm" title="Báo cáo Radar" style="padding: 4px 8px; color: var(--primary);">
                                            <i class="fas fa-chart-pie"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
