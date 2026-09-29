<?php
/**
 * View: training/index.php – Quản lý Đào tạo & Phát triển (L&D)
 */

$statusBadges = [
    'Planning'    => ['Lập kế hoạch', 'badge bg-warning text-dark', 'fas fa-calendar-alt'],
    'In_Progress' => ['Đang diễn ra',  'badge bg-primary text-white', 'fas fa-spinner fa-spin'],
    'Completed'   => ['Đã hoàn tất',   'badge bg-success text-white', 'fas fa-check-circle'],
    'Cancelled'   => ['Đã hủy',        'badge bg-secondary text-white', 'fas fa-ban'],
];
?>

<!-- KPI CARDS -->
<div class="kpi-row" style="margin-bottom: 24px;">
    <div class="kpi-card" onclick="window.location.href='<?= BASE_URL ?>/training'" style="cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='none'">
        <div class="kpi-icon" style="background: linear-gradient(135deg, #4f46e5, #6366f1);"><i class="fas fa-graduation-cap"></i></div>
        <div class="kpi-body">
            <div class="kpi-value"><?= (int)($stats['total_courses'] ?? 0) ?></div>
            <div class="kpi-label">Tổng khóa đào tạo</div>
        </div>
    </div>
    <div class="kpi-card" onclick="window.location.href='<?= BASE_URL ?>/training?status=In_Progress'" style="cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='none'">
        <div class="kpi-icon" style="background: linear-gradient(135deg, #0284c7, #38bdf8);"><i class="fas fa-chalkboard-teacher"></i></div>
        <div class="kpi-body">
            <div class="kpi-value"><?= (int)($stats['in_progress_courses'] ?? 0) ?></div>
            <div class="kpi-label">Đang diễn ra</div>
        </div>
    </div>
    <div class="kpi-card" onclick="window.location.href='<?= BASE_URL ?>/training?status=Completed'" style="cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='none'">
        <div class="kpi-icon" style="background: linear-gradient(135deg, #10b981, #059669);"><i class="fas fa-user-graduate"></i></div>
        <div class="kpi-body">
            <div class="kpi-value"><?= (int)($stats['completed_courses'] ?? 0) ?></div>
            <div class="kpi-label">Đã hoàn tất</div>
        </div>
    </div>
    <div class="kpi-card" style="transition: transform 0.2s;">
        <div class="kpi-icon" style="background: linear-gradient(135deg, #f59e0b, #d97706);"><i class="fas fa-users"></i></div>
        <div class="kpi-body">
            <div class="kpi-value"><?= (int)($stats['total_participants'] ?? 0) ?></div>
            <div class="kpi-label">Tổng lượt học viên (<?= (int)($stats['total_passed'] ?? 0) ?> Đạt)</div>
        </div>
    </div>
    <div class="kpi-card" style="transition: transform 0.2s;">
        <div class="kpi-icon" style="background: linear-gradient(135deg, #ec4899, #be185d);"><i class="fas fa-coins"></i></div>
        <div class="kpi-body">
            <div class="kpi-value" style="font-size: 1.15rem;"><?= number_format((float)($stats['total_budget'] ?? 0)) ?> <span style="font-size: 0.8rem; font-weight: normal;">VNĐ</span></div>
            <div class="kpi-label">Tổng ngân sách L&D</div>
        </div>
    </div>
</div>

<!-- ACTIONS TOOLBAR -->
<div style="display: flex; justify-content: space-between; align-items: center; gap: 12px; margin-bottom: 20px; flex-wrap: wrap;">
    <div class="d-flex gap-2">
        <a href="<?= BASE_URL ?>/training/create" class="btn btn-primary" style="box-shadow: 0 4px 14px rgba(79, 70, 229, 0.3);">
            <i class="fas fa-plus"></i> Tạo Khóa Đào tạo Mới
        </a>
    </div>
    <div>
        <span class="text-muted" style="font-size: 13px;">
            <i class="fas fa-info-circle"></i> Đang hiển thị <strong><?= count($trainings) ?></strong> khóa học
        </span>
    </div>
</div>

<!-- BỘ LỌC ĐA TIÊU CHÍ -->
<div class="panel mb-4" style="border: none; box-shadow: 0 4px 15px rgba(0,0,0,0.03);">
    <div class="panel-header" style="background: var(--bg-card); border-bottom: 1px solid var(--border);">
        <h3 style="font-size: 15px; font-weight: 700; margin: 0; color: var(--text-heading);">
            <i class="fas fa-filter text-primary"></i> Bộ lọc & Tìm kiếm Khóa đào tạo
        </h3>
    </div>
    <div class="panel-body">
        <form method="GET" action="<?= BASE_URL ?>/training">
            <div style="display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap;">
                <div class="form-group" style="flex: 2; min-width: 200px; margin-bottom: 0;">
                    <label class="form-label-sm" style="font-weight: 600; font-size: 12px;">Từ khóa</label>
                    <input type="text" name="search" class="form-control" value="<?= h($filters['search']) ?>" placeholder="Tên khóa đào tạo, giảng viên, địa điểm...">
                </div>
                
                <div class="form-group" style="flex: 1; min-width: 140px; margin-bottom: 0;">
                    <label class="form-label-sm" style="font-weight: 600; font-size: 12px;">Năm</label>
                    <select name="year" class="form-control">
                        <option value="">-- Tất cả năm --</option>
                        <?php foreach($years as $y): ?>
                            <option value="<?= $y ?>" <?= ($filters['year'] == $y) ? 'selected' : '' ?>>Năm <?= $y ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group" style="flex: 1.5; min-width: 180px; margin-bottom: 0;">
                    <label class="form-label-sm" style="font-weight: 600; font-size: 12px;">Phòng ban phụ trách</label>
                    <select name="dept" class="form-control">
                        <option value="">-- Tất cả phòng ban --</option>
                        <?php foreach($departments as $d): ?>
                            <option value="<?= $d['id'] ?>" <?= ($filters['department_id'] == $d['id']) ? 'selected' : '' ?>>
                                <?= h($d['dept_name']) ?> (<?= h($d['dept_code']) ?>)
                            </option>
                        <?php endforeach; ?>
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
                    <a href="<?= BASE_URL ?>/training" class="btn btn-ghost" title="Xóa bộ lọc">
                        <i class="fas fa-redo"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- DANH SÁCH KHÓA ĐÀO TẠO -->
<div class="panel" style="border: none; box-shadow: 0 4px 20px rgba(0,0,0,0.04);">
    <div class="panel-header d-flex justify-content-between align-items-center" style="background: var(--bg-card); border-bottom: 1px solid var(--border);">
        <h3 style="font-size: 15px; font-weight: 700; margin: 0; color: var(--text-heading);">
            <i class="fas fa-list-check text-primary"></i> Danh sách Khóa Đào tạo & Bồi dưỡng (<?= count($trainings) ?>)
        </h3>
    </div>
    <div class="panel-body p-0">
        <?php if (empty($trainings)): ?>
            <div class="text-center p-5">
                <div style="font-size: 48px; color: var(--text-muted); margin-bottom: 12px;">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <h4 style="font-size: 16px; font-weight: 700; color: var(--text-heading);">Chưa có khóa đào tạo nào</h4>
                <p class="text-muted" style="font-size: 13px; max-width: 400px; margin: 0 auto 16px;">
                    Không tìm thấy khóa đào tạo phù hợp với bộ lọc hiện tại hoặc chưa có kế hoạch đào tạo nào được tạo.
                </p>
                <a href="<?= BASE_URL ?>/training/create" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Tạo khóa đào tạo ngay
                </a>
            </div>
        <?php else: ?>
            <div class="table-wrapper">
                <table class="table-hover">
                    <thead>
                        <tr>
                            <th style="width: 40px; text-align: center;">STT</th>
                            <th style="min-width: 220px;">Khóa đào tạo</th>
                            <th style="min-width: 140px;">Giảng viên / Đơn vị</th>
                            <th style="min-width: 140px;">Thời gian</th>
                            <th style="min-width: 130px;">Địa điểm / Phòng ban</th>
                            <th style="min-width: 120px; text-align: center;">Học viên</th>
                            <th style="min-width: 110px; text-align: right;">Chi phí (VNĐ)</th>
                            <th style="min-width: 110px; text-align: center;">Trạng thái</th>
                            <th style="width: 130px; text-align: center;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($trainings as $idx => $t): 
                            $badge = $statusBadges[$t['status']] ?? ['Không rõ', 'badge bg-secondary', 'fas fa-question'];
                            $pCount = (int)($t['participant_count'] ?? 0);
                            $maxP = (int)($t['max_participants'] ?? 0);
                            $passedCount = (int)($t['passed_count'] ?? 0);
                        ?>
                            <tr>
                                <td style="text-align: center; color: var(--text-muted); font-size: 13px;"><?= $idx + 1 ?></td>
                                <td>
                                    <a href="<?= BASE_URL ?>/training/show/<?= $t['id'] ?>" style="font-weight: 700; color: var(--primary); text-decoration: none; font-size: 14px;">
                                        <?= h($t['course_name']) ?>
                                    </a>
                                    <?php if (!empty($t['description'])): ?>
                                        <div class="text-muted" style="font-size: 12px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 260px;">
                                            <?= h(mb_strimwidth($t['description'], 0, 70, '...')) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="font-weight: 600; font-size: 13px;"><?= h($t['provider'] ?: 'Nội bộ công ty') ?></div>
                                </td>
                                <td>
                                    <div style="font-size: 13px; font-weight: 500;">
                                        <i class="far fa-calendar-alt text-primary" style="font-size: 12px;"></i>
                                        <?= fmtDate($t['start_date']) ?>
                                    </div>
                                    <?php if (!empty($t['end_date'])): ?>
                                        <div class="text-muted" style="font-size: 11px;">
                                            đến <?= fmtDate($t['end_date']) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($t['dept_name'])): ?>
                                        <div style="font-size: 12px; font-weight: 600; color: var(--text-heading);">
                                            <i class="fas fa-sitemap text-muted"></i> <?= h($t['dept_name']) ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($t['location'])): ?>
                                        <div class="text-muted" style="font-size: 11px;">
                                            <i class="fas fa-map-marker-alt text-danger"></i> <?= h($t['location']) ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted" style="font-size: 11px;">Trụ sở chính</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: center;">
                                    <div style="font-weight: 700; font-size: 14px;">
                                        <?= $pCount ?><?= $maxP > 0 ? " / <span class='text-muted' style='font-size:12px;'>{$maxP}</span>" : "" ?>
                                    </div>
                                    <?php if ($pCount > 0): ?>
                                        <div style="font-size: 11px;" class="text-success font-weight-bold">
                                            <i class="fas fa-check"></i> <?= $passedCount ?> Đạt
                                        </div>
                                    <?php else: ?>
                                        <div style="font-size: 11px;" class="text-muted">Chưa có HV</div>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: right; font-weight: 700; color: var(--text-heading); font-size: 13px;">
                                    <?= number_format((float)($t['cost'] ?? 0)) ?>
                                </td>
                                <td style="text-align: center;">
                                    <span class="<?= $badge[1] ?>" style="font-size: 11px; padding: 5px 10px; border-radius: 6px;">
                                        <i class="<?= $badge[2] ?>"></i> <?= $badge[0] ?>
                                    </span>
                                </td>
                                <td style="text-align: center;">
                                    <div class="d-flex justify-content-center gap-1">
                                        <a href="<?= BASE_URL ?>/training/show/<?= $t['id'] ?>" class="btn btn-ghost btn-sm" title="Chi tiết & Học viên" style="padding: 4px 8px;">
                                            <i class="fas fa-eye text-primary"></i>
                                        </a>
                                        <a href="<?= BASE_URL ?>/training/edit/<?= $t['id'] ?>" class="btn btn-ghost btn-sm" title="Chỉnh sửa" style="padding: 4px 8px;">
                                            <i class="fas fa-edit text-warning"></i>
                                        </a>
                                        <form method="POST" action="<?= BASE_URL ?>/training/delete/<?= $t['id'] ?>" style="display: inline;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa khóa đào tạo này vào thùng rác?');">
                                            <input type="hidden" name="_csrf_token" value="<?= Session::generateCsrfToken() ?>">
                                            <button type="submit" class="btn btn-ghost btn-sm" title="Xóa khóa học" style="padding: 4px 8px;">
                                                <i class="fas fa-trash-alt text-danger"></i>
                                            </button>
                                        </form>
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
