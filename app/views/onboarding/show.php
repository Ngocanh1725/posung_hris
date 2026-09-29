<!-- ══════════════════════════════════════════════════════════
     POSUNG HRIS – CHECKLIST TIẾN TRÌNH HỘI NHẬP NHÂN SỰ
     ══════════════════════════════════════════════════════════ -->

<div class="onboarding-show-page">
    <!-- Breadcrumb & Top Bar -->
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div class="breadcrumb-bar m-0">
            <a href="<?= BASE_URL ?>/onboarding"><i class="fas fa-user-check"></i> Hội nhập</a>
            <i class="fas fa-chevron-right"></i>
            <span class="text-primary fw-bold">Checklist: <?= htmlspecialchars($onboarding['full_name']) ?></span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="<?= BASE_URL ?>/onboarding" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Dashboard Hội nhập
            </a>
            <a href="<?= BASE_URL ?>/employee/detail/<?= $onboarding['employee_id'] ?>" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-id-card me-1"></i> Hồ Sơ 360°
            </a>
            <a href="<?= BASE_URL ?>/onboarding/printHandover/<?= $onboarding['id'] ?>" target="_blank" class="btn btn-sm btn-success">
                <i class="fas fa-print me-1"></i> In Biên Bản Bàn Giao
            </a>
        </div>
    </div>

    <!-- 1. Header Profile & Overall Progress Card -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px; background: var(--bg-card); overflow: hidden;">
        <div class="card-body p-4">
            <div class="row g-4 align-items-center">
                <!-- Employee Info -->
                <div class="col-lg-6 d-flex align-items-center gap-3">
                    <?php 
                    $hasAvatar = !empty($onboarding['avatar_path']) && file_exists(ROOT_PATH . '/public/' . $onboarding['avatar_path']);
                    ?>
                    <?php if ($hasAvatar): ?>
                        <img src="<?= BASE_URL . '/' . $onboarding['avatar_path'] ?>" class="rounded-circle shadow" style="width: 72px; height: 72px; object-fit: cover; border: 3px solid var(--border);" alt="">
                    <?php else: ?>
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold fs-3 shadow" style="width: 72px; height: 72px;">
                            <?= mb_substr(trim($onboarding['full_name']), 0, 1, 'UTF-8') ?>
                        </div>
                    <?php endif; ?>

                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <h4 class="fw-bold mb-0 text-primary"><?= htmlspecialchars($onboarding['full_name']) ?></h4>
                            <span class="badge bg-light text-primary border"><?= htmlspecialchars($onboarding['emp_code']) ?></span>
                            <?php if ($onboarding['status'] === 'Completed'): ?>
                                <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i> Hoàn tất 100%</span>
                            <?php elseif ($onboarding['status'] === 'Overdue'): ?>
                                <span class="badge bg-danger"><i class="fas fa-exclamation-triangle me-1"></i> Quá hạn</span>
                            <?php else: ?>
                                <span class="badge bg-primary"><i class="fas fa-spinner fa-spin me-1"></i> Đang hội nhập</span>
                            <?php endif; ?>
                        </div>

                        <div class="text-muted small mb-1">
                            <i class="fas fa-briefcase text-secondary me-1"></i><strong><?= htmlspecialchars($onboarding['pos_title'] ?? 'Nhân viên') ?></strong> • 
                            <i class="fas fa-building text-secondary me-1"></i><?= htmlspecialchars($onboarding['dept_name'] ?? 'POSUNG') ?>
                        </div>

                        <div class="text-muted small">
                            <span class="badge bg-light text-dark border">
                                <i class="fas fa-layer-group text-primary me-1"></i>Mẫu: <?= htmlspecialchars($onboarding['template_name']) ?>
                            </span>
                            <span class="ms-2"><i class="fas fa-calendar-check text-success me-1"></i>Bắt đầu: <?= date('d/m/Y', strtotime($onboarding['start_date'])) ?></span>
                        </div>
                    </div>
                </div>

                <!-- Progress Bar & Metrics -->
                <div class="col-lg-6">
                    <div class="p-3 rounded-3 border" style="background: var(--bg-hover);">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-bold" style="font-size: 14px;">
                                <i class="fas fa-chart-line text-primary me-1"></i> Tiến độ hoàn thành checklist
                            </span>
                            <span class="fs-5 fw-bold <?= $onboarding['progress_pct'] >= 100 ? 'text-success' : 'text-primary' ?>" id="progressPctText">
                                <?= $onboarding['progress_pct'] ?>%
                            </span>
                        </div>
                        <div class="progress mb-2" style="height: 10px; border-radius: 6px; background: #e2e8f0;">
                            <div class="progress-bar <?= $onboarding['progress_pct'] >= 100 ? 'bg-success' : 'bg-primary' ?> progress-bar-striped progress-bar-animated" 
                                 id="progressBarEl" role="progressbar" style="width: <?= $onboarding['progress_pct'] ?>%;"></div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center small text-muted">
                            <span id="progressCountsText">
                                Đã xong <strong><?= $onboarding['done_items'] ?></strong> / <?= $onboarding['total_items'] ?> nhiệm vụ
                            </span>
                            <?php if (!empty($onboarding['overdue_items'])): ?>
                                <span class="badge bg-danger"><i class="fas fa-clock"></i> <?= $onboarding['overdue_items'] ?> task quá hạn</span>
                            <?php else: ?>
                                <span class="badge bg-success bg-opacity-10 text-success"><i class="fas fa-check"></i> Đúng tiến độ</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Checklist Groups By Department -->
    <?php
    $deptConfigs = [
        'IT' => [
            'name'  => 'Công Nghệ Thông Tin (IT Support)',
            'icon'  => 'fa-laptop-code',
            'color' => '#3b82f6',
            'bg'    => 'rgba(59, 130, 246, 0.08)',
            'badge' => 'Cấp phát email, máy tính, tài khoản HRIS, VPN'
        ],
        'HR' => [
            'name'  => 'Hành Chính Nhân Sự (HR)',
            'icon'  => 'fa-user-tie',
            'color' => '#ec4899',
            'bg'    => 'rgba(236, 72, 153, 0.08)',
            'badge' => 'Ký hợp đồng, nộp BHXH, ảnh thẻ, nội quy'
        ],
        'HSE' => [
            'name'  => 'An Toàn Lao Động & Môi Trường (HSE)',
            'icon'  => 'fa-shield-halved',
            'color' => '#10b981',
            'bg'    => 'rgba(16, 185, 129, 0.08)',
            'badge' => 'Huấn luyện an toàn, khám sức khỏe, cấp đồ PPE, Thẻ an toàn'
        ],
        'Admin' => [
            'name'  => 'Hành Chính Tổng Hợp (Admin)',
            'icon'  => 'fa-building',
            'color' => '#f59e0b',
            'bg'    => 'rgba(245, 158, 11, 0.08)',
            'badge' => 'Cấp thẻ ra vào, bố trí chỗ ngồi, vé xe, đồ dùng'
        ],
        'Finance' => [
            'name'  => 'Tài Chính Kế Toán (Finance)',
            'icon'  => 'fa-money-bill-wave',
            'color' => '#6366f1',
            'bg'    => 'rgba(99, 102, 241, 0.08)',
            'badge' => 'Mở STK nhận lương, đăng ký MST cá nhân'
        ],
    ];
    ?>

    <div class="row g-4 mb-4">
        <?php foreach ($onboarding['items_by_dept'] as $deptKey => $tasks): 
            $cfg = $deptConfigs[$deptKey] ?? [
                'name'  => 'Bộ phận ' . $deptKey,
                'icon'  => 'fa-tasks',
                'color' => '#64748b',
                'bg'    => 'rgba(100, 116, 139, 0.08)',
                'badge' => 'Nhiệm vụ chuyên trách'
            ];
            $deptDone = 0;
            foreach ($tasks as $t) {
                if ($t['status'] === 'Done' || $t['status'] === 'Skipped') $deptDone++;
            }
            $deptTotal = count($tasks);
            $deptPct = $deptTotal > 0 ? round(($deptDone / $deptTotal) * 100) : 0;
        ?>
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="border-radius: 14px; background: var(--bg-card); overflow: hidden; border-left: 5px solid <?= $cfg['color'] ?> !important;">
                <div class="card-header bg-transparent py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2" style="background: <?= $cfg['bg'] ?> !important;">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center text-white shadow-sm" style="width: 40px; height: 40px; background: <?= $cfg['color'] ?>; font-size: 16px;">
                            <i class="fas <?= $cfg['icon'] ?>"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0" style="color: var(--text);">
                                <?= $cfg['name'] ?>
                            </h5>
                            <small class="text-muted"><?= $cfg['badge'] ?></small>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <div class="text-end">
                            <span class="small fw-semibold"><?= $deptDone ?> / <?= $deptTotal ?> hoàn tất</span>
                            <div class="progress" style="width: 120px; height: 6px; background: #e2e8f0; border-radius: 4px;">
                                <div class="progress-bar" style="width: <?= $deptPct ?>%; background: <?= $cfg['color'] ?>;"></div>
                            </div>
                        </div>
                        <span class="badge" style="background: <?= $cfg['color'] ?>; font-size: 12px;"><?= $deptPct ?>%</span>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size: 13.5px;">
                            <thead style="background: var(--bg-hover); font-size: 11px; text-transform: uppercase; color: var(--text-muted);">
                                <tr>
                                    <th style="width: 45px; text-align: center;"></th>
                                    <th>Nhiệm vụ & Hướng dẫn chi tiết</th>
                                    <th style="width: 140px; text-align: center;">Hạn chót</th>
                                    <th style="width: 160px; text-align: center;">Trạng thái</th>
                                    <th>Ghi chú thực hiện</th>
                                    <th style="width: 80px; text-align: center;">Lưu</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tasks as $item): 
                                    $isDone = ($item['status'] === 'Done' || $item['status'] === 'Skipped');
                                    $isOverdue = !empty($item['is_overdue']);
                                ?>
                                <tr id="item-row-<?= $item['id'] ?>" class="<?= $isDone ? 'table-light text-muted' : '' ?>">
                                    <td style="text-align: center;">
                                        <input type="checkbox" class="form-check-input task-checkbox" 
                                               id="check-<?= $item['id'] ?>"
                                               <?= $isDone ? 'checked' : '' ?>
                                               onchange="toggleTaskDone(<?= $item['id'] ?>, this.checked)"
                                               style="width: 20px; height: 20px; cursor: pointer;">
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <strong class="<?= $isDone ? 'text-decoration-line-through text-muted' : '' ?>" style="color: var(--text);" id="task-title-<?= $item['id'] ?>">
                                                <?= htmlspecialchars($item['title']) ?>
                                            </strong>
                                            <?php if ($item['is_required']): ?>
                                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger small" style="font-size: 10px;">Bắt buộc</span>
                                            <?php endif; ?>
                                        </div>
                                        <?php if (!empty($item['description'])): ?>
                                            <div class="text-muted small mt-1"><?= htmlspecialchars($item['description']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align: center;">
                                        <?php if (!empty($item['due_date'])): ?>
                                            <div class="fw-semibold <?= $isOverdue ? 'text-danger' : '' ?>">
                                                <?= date('d/m/Y', strtotime($item['due_date'])) ?>
                                            </div>
                                            <?php if ($isOverdue): ?>
                                                <span class="badge bg-danger small" style="font-size: 10px;">Quá hạn</span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-muted">---</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align: center;">
                                        <select class="form-select form-select-sm" 
                                                id="status-select-<?= $item['id'] ?>"
                                                onchange="updateItemStatus(<?= $item['id'] ?>, this.value)"
                                                style="font-size: 12px; font-weight: 600;">
                                            <option value="Pending" <?= $item['status'] === 'Pending' ? 'selected' : '' ?>>⏳ Chờ xử lý</option>
                                            <option value="InProgress" <?= $item['status'] === 'InProgress' ? 'selected' : '' ?>>⚙ Đang làm</option>
                                            <option value="Done" <?= $item['status'] === 'Done' ? 'selected' : '' ?>>✅ Hoàn tất</option>
                                            <option value="Skipped" <?= $item['status'] === 'Skipped' ? 'selected' : '' ?>>⏭ Bỏ qua</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm" 
                                               id="notes-input-<?= $item['id'] ?>"
                                               placeholder="Ghi chú, mã thiết bị, serial..." 
                                               value="<?= htmlspecialchars($item['notes'] ?? '') ?>">
                                    </td>
                                    <td style="text-align: center;">
                                        <button type="button" class="btn btn-sm btn-outline-primary py-1 px-2" 
                                                onclick="saveItemNotes(<?= $item['id'] ?>)" title="Lưu ghi chú">
                                            <i class="fas fa-save"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
// Toggle Done / Pending via Checkbox (AJAX)
function toggleTaskDone(itemId, isChecked) {
    const status = isChecked ? 'Done' : 'Pending';
    const select = document.getElementById('status-select-' + itemId);
    if (select) select.value = status;

    updateItemStatus(itemId, status);
}

// Update task status via AJAX
function updateItemStatus(itemId, status) {
    const notes = document.getElementById('notes-input-' + itemId)?.value || '';

    fetch('<?= BASE_URL ?>/onboarding/updateTask/' + itemId, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: new URLSearchParams({
            status: status,
            notes: notes,
            _csrf_token: '<?= Session::getCsrfToken() ?>'
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            // Update row appearance
            const row = document.getElementById('item-row-' + itemId);
            const title = document.getElementById('task-title-' + itemId);
            const check = document.getElementById('check-' + itemId);

            if (status === 'Done' || status === 'Skipped') {
                row.classList.add('table-light', 'text-muted');
                title.classList.add('text-decoration-line-through', 'text-muted');
                if (check) check.checked = true;
            } else {
                row.classList.remove('table-light', 'text-muted');
                title.classList.remove('text-decoration-line-through', 'text-muted');
                if (check) check.checked = false;
            }

            // Reload to recompute progress or alert user
            setTimeout(() => {
                location.reload();
            }, 400);
        } else {
            alert('Lỗi: ' + (data.message || 'Không thể cập nhật task.'));
        }
    })
    .catch(err => {
        console.error(err);
        alert('Có lỗi xảy ra khi lưu trạng thái.');
    });
}

// Save notes only
function saveItemNotes(itemId) {
    const status = document.getElementById('status-select-' + itemId)?.value || 'Pending';
    const notes = document.getElementById('notes-input-' + itemId)?.value || '';

    fetch('<?= BASE_URL ?>/onboarding/updateTask/' + itemId, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: new URLSearchParams({
            status: status,
            notes: notes,
            _csrf_token: '<?= Session::getCsrfToken() ?>'
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert('Đã lưu ghi chú thành công!');
        } else {
            alert('Lỗi lưu ghi chú.');
        }
    })
    .catch(err => {
        alert('Lỗi kết nối máy chủ.');
    });
}
</script>
