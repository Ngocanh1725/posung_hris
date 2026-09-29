<!-- ══════════════════════════════════════════════════════════
     POSUNG HRIS – DASHBOARD QUY TRÌNH HỘI NHẬP (ONBOARDING)
     ══════════════════════════════════════════════════════════ -->

<div class="onboarding-dashboard-page">
    <!-- Breadcrumb & Top Bar -->
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div class="breadcrumb-bar m-0">
            <a href="<?= BASE_URL ?>/employee"><i class="fas fa-users"></i> Nhân sự</a>
            <i class="fas fa-chevron-right"></i>
            <span class="text-primary fw-bold"><i class="fas fa-user-check"></i> Quy trình Hội nhập (Onboarding)</span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="<?= BASE_URL ?>/onboarding/templates" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-layer-group me-1"></i> Quản lý Mẫu Quy trình
            </a>
            <a href="<?= BASE_URL ?>/employee" class="btn btn-sm btn-primary">
                <i class="fas fa-user-plus me-1"></i> Khởi tạo cho Nhân sự mới
            </a>
        </div>
    </div>

    <!-- 1. KPI Cards Thống Kê -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-sm-6">
            <div class="card h-100 border-0 shadow-sm" style="border-radius: 12px; border-left: 4px solid #3b82f6 !important; background: var(--bg-card);">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small text-uppercase fw-bold">Đang Hội Nhập</div>
                            <h3 class="fw-bold my-1 text-primary"><?= $stats['counts']['InProgress'] ?? 0 ?></h3>
                            <small class="text-muted">Nhân sự đang thực hiện checklist</small>
                        </div>
                        <div class="stat-icon-circle bg-primary bg-opacity-10 text-primary">
                            <i class="fas fa-spinner fa-spin-pulse fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="card h-100 border-0 shadow-sm" style="border-radius: 12px; border-left: 4px solid #10b981 !important; background: var(--bg-card);">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small text-uppercase fw-bold">Đã Hoàn Tất</div>
                            <h3 class="fw-bold my-1 text-success"><?= $stats['counts']['Completed'] ?? 0 ?></h3>
                            <small class="text-muted">Đạt 100% nhiệm vụ hội nhập</small>
                        </div>
                        <div class="stat-icon-circle bg-success bg-opacity-10 text-success">
                            <i class="fas fa-check-double fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="card h-100 border-0 shadow-sm" style="border-radius: 12px; border-left: 4px solid #ef4444 !important; background: var(--bg-card);">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small text-uppercase fw-bold">Cảnh Báo Quá Hạn</div>
                            <h3 class="fw-bold my-1 text-danger"><?= $stats['counts']['Overdue'] ?? 0 ?></h3>
                            <small class="text-danger fw-bold"><i class="fas fa-clock"></i> Có task trễ hạn cần đôn đốc</small>
                        </div>
                        <div class="stat-icon-circle bg-danger bg-opacity-10 text-danger">
                            <i class="fas fa-exclamation-triangle fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="card h-100 border-0 shadow-sm" style="border-radius: 12px; border-left: 4px solid #8b5cf6 !important; background: var(--bg-card);">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small text-uppercase fw-bold">Tổng Nhân Sự Mới</div>
                            <h3 class="fw-bold my-1 text-purple" style="color: #8b5cf6;"><?= count($onboardings) ?></h3>
                            <small class="text-muted">Áp dụng trong hệ thống</small>
                        </div>
                        <div class="stat-icon-circle bg-purple bg-opacity-10" style="background: rgba(139,92,246,0.1); color: #8b5cf6;">
                            <i class="fas fa-id-badge fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Cảnh báo nhiệm vụ quá hạn (nếu có) -->
    <?php if (!empty($stats['overdue_tasks'])): ?>
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; background: var(--bg-card); border-top: 3px solid #ef4444 !important;">
        <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-danger">
                <i class="fas fa-bell me-2"></i>Nhiệm Vụ Hội Nhập Quá Hạn Cần Bộ Phận Xử Lý Gấp (<?= count($stats['overdue_tasks']) ?>)
            </h6>
            <span class="badge bg-danger">Chậm tiến độ</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                    <thead style="background: rgba(239,68,68,0.05); color: #b91c1c;">
                        <tr>
                            <th>Nhân sự nhận việc</th>
                            <th>Nhiệm vụ chậm</th>
                            <th style="text-align: center;">Bộ phận phụ trách</th>
                            <th style="text-align: center;">Hạn chót</th>
                            <th style="text-align: center;">Trễ hạn</th>
                            <th style="text-align: center;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stats['overdue_tasks'] as $ot): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($ot['full_name']) ?></strong>
                                <span class="badge bg-light text-dark border ms-1"><?= htmlspecialchars($ot['emp_code']) ?></span>
                                <div class="text-muted small"><?= htmlspecialchars($ot['dept_name'] ?? '') ?></div>
                            </td>
                            <td>
                                <strong class="text-danger"><?= htmlspecialchars($ot['task_title']) ?></strong>
                            </td>
                            <td style="text-align: center;">
                                <?php
                                $dColor = [
                                    'IT'      => '#3b82f6',
                                    'HR'      => '#ec4899',
                                    'HSE'     => '#10b981',
                                    'Admin'   => '#f59e0b',
                                    'Finance' => '#6366f1',
                                ][$ot['responsible_department']] ?? '#64748b';
                                ?>
                                <span class="badge" style="background: <?= $dColor ?>;"><?= htmlspecialchars($ot['responsible_department']) ?></span>
                            </td>
                            <td style="text-align: center; font-weight: 600;">
                                <?= date('d/m/Y', strtotime($ot['due_date'])) ?>
                            </td>
                            <td style="text-align: center;">
                                <span class="badge bg-danger text-white">Quá <?= $ot['days_overdue'] ?> ngày</span>
                            </td>
                            <td style="text-align: center;">
                                <a href="<?= BASE_URL ?>/onboarding/show/<?= $ot['onboarding_id'] ?>" class="btn btn-sm btn-outline-danger py-1 px-2">
                                    <i class="fas fa-check"></i> Xử lý
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

    <!-- 3. Bộ lọc & Danh sách Nhân viên đang Hội nhập -->
    <div class="card border-0 shadow-sm" style="border-radius: 12px; background: var(--bg-card);">
        <div class="card-header bg-transparent py-3 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h6 class="fw-bold mb-0 text-primary">
                <i class="fas fa-list-check me-2"></i>Tiến Trình Hội Nhập Nhân Sự Mới
            </h6>

            <!-- Filter form -->
            <form method="GET" action="<?= BASE_URL ?>/onboarding" class="d-flex align-items-center gap-2 flex-wrap">
                <select name="status" class="form-select form-select-sm" style="width: 150px;" onchange="this.form.submit()">
                    <option value="">Tất cả trạng thái</option>
                    <option value="InProgress" <?= $currentStatus === 'InProgress' ? 'selected' : '' ?>>Đang hội nhập</option>
                    <option value="Completed" <?= $currentStatus === 'Completed' ? 'selected' : '' ?>>Đã hoàn tất</option>
                    <option value="Overdue" <?= $currentStatus === 'Overdue' ? 'selected' : '' ?>>Quá hạn</option>
                </select>

                <select name="department_id" class="form-select form-select-sm" style="width: 180px;" onchange="this.form.submit()">
                    <option value="0">Tất cả phòng ban</option>
                    <?php foreach ($departments as $d): ?>
                        <option value="<?= $d['id'] ?>" <?= $currentDept == $d['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($d['dept_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <div class="input-group input-group-sm" style="width: 220px;">
                    <input type="text" name="search" class="form-control" placeholder="Tìm tên, mã NV..." value="<?= htmlspecialchars($search) ?>">
                    <button class="btn btn-outline-secondary" type="submit"><i class="fas fa-search"></i></button>
                </div>
            </form>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 13.5px;">
                    <thead style="background: var(--bg-hover, #f8fafc); font-size: 11px; text-transform: uppercase; color: var(--text-muted);">
                        <tr>
                            <th style="width: 50px; text-align: center;">#</th>
                            <th>Nhân sự mới</th>
                            <th>Phòng ban / Chức vụ</th>
                            <th>Mẫu quy trình áp dụng</th>
                            <th style="text-align: center;">Ngày bắt đầu</th>
                            <th style="width: 220px;">Tiến độ hoàn thành</th>
                            <th style="text-align: center; width: 130px;">Trạng thái</th>
                            <th style="text-align: center; width: 110px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($onboardings)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="fas fa-user-check fa-3x mb-3 text-secondary" style="opacity: 0.3;"></i>
                                <h5>Không tìm thấy dữ liệu hội nhập nhân sự nào.</h5>
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php $idx = 1; foreach ($onboardings as $onb): 
                                $pct = (int)($onb['progress_pct'] ?? 0);
                                $done = (int)($onb['done_items'] ?? 0);
                                $total = (int)($onb['total_items'] ?? 0);
                                $hasAvatar = !empty($onb['avatar_path']) && file_exists(ROOT_PATH . '/public/' . $onb['avatar_path']);
                            ?>
                            <tr>
                                <td style="text-align: center; font-weight: 600; color: var(--text-muted);"><?= $idx++ ?></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <?php if ($hasAvatar): ?>
                                            <img src="<?= BASE_URL . '/' . $onb['avatar_path'] ?>" class="rounded-circle shadow-sm" style="width: 36px; height: 36px; object-fit: cover;" alt="">
                                        <?php else: ?>
                                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width: 36px; height: 36px; font-size: 13px;">
                                                <?= mb_substr(trim($onb['full_name']), 0, 1, 'UTF-8') ?>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <a href="<?= BASE_URL ?>/onboarding/show/<?= $onb['id'] ?>" class="fw-bold text-decoration-none" style="color: var(--text);">
                                                <?= htmlspecialchars($onb['full_name']) ?>
                                            </a>
                                            <div><span class="badge bg-light text-primary border" style="font-size: 11px;"><?= htmlspecialchars($onb['emp_code']) ?></span></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div><strong><?= htmlspecialchars($onb['pos_title'] ?? 'Nhân viên') ?></strong></div>
                                    <small class="text-muted"><?= htmlspecialchars($onb['dept_name'] ?? 'POSUNG') ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        <i class="fas fa-layer-group text-primary me-1"></i><?= htmlspecialchars($onb['template_name']) ?>
                                    </span>
                                </td>
                                <td style="text-align: center; font-weight: 600;">
                                    <?= date('d/m/Y', strtotime($onb['start_date'])) ?>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-between align-items-center mb-1 small">
                                        <span class="fw-semibold"><?= $done ?> / <?= $total ?> nhiệm vụ</span>
                                        <strong class="<?= $pct >= 100 ? 'text-success' : 'text-primary' ?>"><?= $pct ?>%</strong>
                                    </div>
                                    <div class="progress" style="height: 7px; border-radius: 4px; background: #e2e8f0;">
                                        <div class="progress-bar <?= $pct >= 100 ? 'bg-success' : 'bg-primary' ?>" 
                                             role="progressbar" style="width: <?= $pct ?>%;"></div>
                                    </div>
                                    <?php if (!empty($onb['overdue_items'])): ?>
                                        <small class="text-danger fw-semibold"><i class="fas fa-exclamation-circle"></i> Trễ <?= $onb['overdue_items'] ?> task</small>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: center;">
                                    <?php if ($onb['status'] === 'Completed'): ?>
                                        <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i> Hoàn tất</span>
                                    <?php elseif ($onb['status'] === 'Overdue'): ?>
                                        <span class="badge bg-danger"><i class="fas fa-clock me-1"></i> Quá hạn</span>
                                    <?php else: ?>
                                        <span class="badge bg-primary"><i class="fas fa-spinner fa-spin me-1"></i> Đang thực hiện</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: center;">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= BASE_URL ?>/onboarding/show/<?= $onb['id'] ?>" class="btn btn-outline-primary" title="Xem checklist & cập nhật">
                                            <i class="fas fa-clipboard-check"></i>
                                        </a>
                                        <a href="<?= BASE_URL ?>/onboarding/printHandover/<?= $onb['id'] ?>" class="btn btn-outline-secondary" target="_blank" title="In Biên bản bàn giao">
                                            <i class="fas fa-print"></i>
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
    </div>
</div>

<style>
.stat-icon-circle {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>
