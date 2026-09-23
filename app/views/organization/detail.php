<!-- ══════════════════════════════════════════════════════════
     CHI TIẾT BỘ PHẬN – POSUNG HRIS
     ══════════════════════════════════════════════════════════ -->

<!-- Breadcrumb -->
<div class="breadcrumb-bar">
    <a href="<?= BASE_URL ?>/organization/overview"><i class="fas fa-building"></i> Cơ cấu Tổ chức</a>
    <i class="fas fa-chevron-right"></i>
    <a href="<?= BASE_URL ?>/organization">Sơ đồ Tổ chức</a>
    <i class="fas fa-chevron-right"></i>
    <span><?= h($dept['name']) ?></span>
</div>

<?php
$typeClass = strtolower($dept['type'] ?? 'department');
if ($typeClass === 'site_pmb') $typeClass = 'project';
if ($typeClass === 'bod') $typeClass = 'division';
$gradientMap = [
    'division'   => 'linear-gradient(135deg, rgba(245,158,11,0.15) 0%, rgba(217,119,6,0.08) 100%)',
    'department'  => 'linear-gradient(135deg, rgba(99,102,241,0.15) 0%, rgba(139,92,246,0.08) 100%)',
    'team'        => 'linear-gradient(135deg, rgba(59,130,246,0.15) 0%, rgba(37,99,235,0.08) 100%)',
    'project'     => 'linear-gradient(135deg, rgba(16,185,129,0.15) 0%, rgba(5,150,105,0.08) 100%)',
];
$gradient = $gradientMap[$typeClass] ?? $gradientMap['department'];
$iconMap = [
    'division' => 'fas fa-crown', 'department' => 'fas fa-building',
    'team' => 'fas fa-users', 'project' => 'fas fa-hard-hat',
];
$icon = $iconMap[$typeClass] ?? 'fas fa-building';
?>

<!-- ── HEADER BỘ PHẬN ── -->
<div class="detail-header" style="background: <?= $gradient ?>;">
    <div class="detail-header-content">
        <div class="detail-header-icon">
            <i class="<?= $icon ?>"></i>
        </div>
        <div class="detail-header-info">
            <div class="detail-header-meta">
                <span class="dept-type-badge badge-<?= $typeClass ?>"><?= h($dept['type'] ?? 'Department') ?></span>
                <span class="detail-code"><?= h($dept['code']) ?></span>
                <?php if (!empty($dept['branch'])): ?>
                    <span class="badge bg-secondary" style="font-size:0.65rem;"><?= h($dept['branch']) ?></span>
                <?php endif; ?>
                <span class="badge <?= ($dept['status'] ?? 'Active') === 'Active' ? 'badge-active' : 'badge-inactive' ?>" style="font-size:0.65rem;">
                    <?= ($dept['status'] ?? 'Active') === 'Active' ? '● Đang hoạt động' : '○ Ngưng hoạt động' ?>
                </span>
            </div>
            <h2 class="detail-title"><?= h($dept['name']) ?></h2>
            <?php if ($parent): ?>
                <div class="detail-parent">
                    <i class="fas fa-level-up-alt fa-rotate-90" style="margin-right: 4px;"></i>
                    Thuộc: <a href="<?= BASE_URL ?>/organization/detail/<?= $parent['id'] ?>" class="text-link"><?= h($parent['name']) ?></a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="detail-grid">
    <!-- CỘT TRÁI: Thông tin chi tiết -->
    <div class="detail-main">
        <!-- Mô tả chi tiết -->
        <?php if (!empty($dept['description'])): ?>
        <div class="panel detail-section">
            <div class="panel-header">
                <h3><i class="fas fa-info-circle"></i> Mô tả Bộ phận</h3>
            </div>
            <div class="panel-body">
                <p class="detail-description"><?= nl2br(h($dept['description'])) ?></p>
            </div>
        </div>
        <?php endif; ?>

        <!-- Danh sách Chức năng / Nhiệm vụ -->
        <?php if (!empty($functions)): ?>
        <div class="panel detail-section">
            <div class="panel-header">
                <h3><i class="fas fa-tasks"></i> Chức năng & Nhiệm vụ chính</h3>
                <span class="badge badge-primary"><?= count($functions) ?> nhiệm vụ</span>
            </div>
            <div class="panel-body p-0">
                <div class="functions-list">
                    <?php foreach ($functions as $i => $func): ?>
                    <div class="function-item">
                        <div class="function-number"><?= str_pad($i + 1, 2, '0', STR_PAD_LEFT) ?></div>
                        <div class="function-text"><?= h($func) ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Danh sách Nhân viên -->
        <div class="panel detail-section">
            <div class="panel-header">
                <h3><i class="fas fa-users"></i> Danh sách Nhân viên</h3>
                <span class="badge badge-active"><?= count($employees) ?> người</span>
            </div>
            <div class="panel-body p-0">
                <?php if (empty($employees)): ?>
                    <div class="empty-state" style="padding: 32px;">
                        <i class="fas fa-user-slash"></i>
                        <p>Chưa có nhân viên nào thuộc bộ phận này.</p>
                    </div>
                <?php else: ?>
                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>Mã NV</th>
                                    <th>Họ và tên</th>
                                    <th>Chức vụ</th>
                                    <th>Điện thoại</th>
                                    <th>Email</th>
                                    <th>Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($employees as $emp): ?>
                                <tr>
                                    <td><strong><?= h($emp['emp_code']) ?></strong></td>
                                    <td>
                                        <a href="<?= BASE_URL ?>/employee/detail/<?= $emp['id'] ?>" class="text-link">
                                            <?= h($emp['full_name']) ?>
                                        </a>
                                    </td>
                                    <td><small><?= h($emp['pos_title'] ?? '—') ?></small></td>
                                    <td><small><?= h($emp['phone'] ?? '—') ?></small></td>
                                    <td><small><?= h($emp['email'] ?? '—') ?></small></td>
                                    <td>
                                        <span class="badge <?= $emp['status'] === 'Active' ? 'badge-active' : 'badge-warning' ?>">
                                            <?= $emp['status'] ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Bộ phận con -->
        <?php if (!empty($children)): ?>
        <div class="panel detail-section">
            <div class="panel-header">
                <h3><i class="fas fa-sitemap"></i> Bộ phận Trực thuộc</h3>
                <span class="badge badge-primary"><?= count($children) ?></span>
            </div>
            <div class="panel-body p-0">
                <div class="children-list">
                    <?php foreach ($children as $child):
                        $childType = strtolower($child['type'] ?? 'department');
                    ?>
                    <a href="<?= BASE_URL ?>/organization/detail/<?= $child['id'] ?>" class="child-item">
                        <div class="child-icon child-icon-<?= $childType ?>">
                            <i class="<?= $iconMap[$childType] ?? 'fas fa-building' ?>"></i>
                        </div>
                        <div class="child-info">
                            <h5><?= h($child['name']) ?></h5>
                            <span class="dept-type-badge badge-<?= $childType ?>" style="font-size:0.6rem;"><?= h($child['code']) ?></span>
                            <?php if (!empty($child['manager_name'])): ?>
                                <small class="text-muted"><i class="fas fa-user-tie"></i> <?= h($child['manager_name']) ?></small>
                            <?php endif; ?>
                        </div>
                        <i class="fas fa-chevron-right" style="color: var(--text-muted); font-size: 0.75rem;"></i>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- CỘT PHẢI: Thông tin liên hệ & Trưởng BP -->
    <div class="detail-sidebar">
        <!-- Trưởng bộ phận -->
        <div class="panel detail-section">
            <div class="panel-header">
                <h3><i class="fas fa-user-tie"></i> Trưởng Bộ phận</h3>
            </div>
            <div class="panel-body">
                <?php if (!empty($dept['manager_name'])): ?>
                <div class="manager-card">
                    <div class="manager-avatar">
                        <?= strtoupper(mb_substr($dept['manager_name'], 0, 1)) ?>
                    </div>
                    <div class="manager-info">
                        <h4><?= h($dept['manager_name']) ?></h4>
                        <span class="manager-code"><?= h($dept['manager_code'] ?? '') ?></span>
                        <?php if (!empty($dept['manager_position'])): ?>
                            <span class="manager-position"><?= h($dept['manager_position']) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($dept['manager_phone'])): ?>
                            <span class="manager-contact"><i class="fas fa-phone"></i> <?= h($dept['manager_phone']) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($dept['manager_email'])): ?>
                            <span class="manager-contact"><i class="fas fa-envelope"></i> <?= h($dept['manager_email']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php else: ?>
                <div class="empty-state" style="padding: 20px;">
                    <i class="fas fa-user-slash" style="font-size: 1.2rem;"></i>
                    <p>Chưa chỉ định Trưởng bộ phận</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Thông tin liên hệ -->
        <div class="panel detail-section">
            <div class="panel-header">
                <h3><i class="fas fa-address-card"></i> Thông tin Liên hệ</h3>
            </div>
            <div class="panel-body">
                <div class="contact-list">
                    <?php if (!empty($dept['phone'])): ?>
                    <div class="contact-item">
                        <div class="contact-icon"><i class="fas fa-phone"></i></div>
                        <div>
                            <span class="contact-label">Điện thoại</span>
                            <span class="contact-value"><?= h($dept['phone']) ?></span>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($dept['email'])): ?>
                    <div class="contact-item">
                        <div class="contact-icon"><i class="fas fa-envelope"></i></div>
                        <div>
                            <span class="contact-label">Email</span>
                            <span class="contact-value"><?= h($dept['email']) ?></span>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($dept['office_location'])): ?>
                    <div class="contact-item">
                        <div class="contact-icon"><i class="fas fa-map-marker-alt"></i></div>
                        <div>
                            <span class="contact-label">Địa điểm</span>
                            <span class="contact-value"><?= h($dept['office_location']) ?></span>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($dept['established_date'])): ?>
                    <div class="contact-item">
                        <div class="contact-icon"><i class="fas fa-calendar-alt"></i></div>
                        <div>
                            <span class="contact-label">Ngày thành lập</span>
                            <span class="contact-value"><?= date('d/m/Y', strtotime($dept['established_date'])) ?></span>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <?php if (empty($dept['phone']) && empty($dept['email']) && empty($dept['office_location'])): ?>
                <div class="empty-state" style="padding: 20px;">
                    <i class="fas fa-info-circle" style="font-size: 1.2rem;"></i>
                    <p>Chưa cập nhật thông tin liên hệ</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Thống kê nhanh -->
        <div class="panel detail-section">
            <div class="panel-header">
                <h3><i class="fas fa-chart-bar"></i> Thống kê</h3>
            </div>
            <div class="panel-body">
                <div class="quick-stats">
                    <div class="quick-stat">
                        <span class="quick-stat-icon" style="background: rgba(99,102,241,0.15); color: var(--primary-light);">
                            <i class="fas fa-users"></i>
                        </span>
                        <div>
                            <span class="quick-stat-number"><?= count($employees) ?></span>
                            <span class="quick-stat-label">Nhân viên</span>
                        </div>
                    </div>
                    <div class="quick-stat">
                        <span class="quick-stat-icon" style="background: rgba(16,185,129,0.15); color: #10b981;">
                            <i class="fas fa-sitemap"></i>
                        </span>
                        <div>
                            <span class="quick-stat-number"><?= count($children) ?></span>
                            <span class="quick-stat-label">Bộ phận con</span>
                        </div>
                    </div>
                    <?php if (!empty($functions)): ?>
                    <div class="quick-stat">
                        <span class="quick-stat-icon" style="background: rgba(245,158,11,0.15); color: #f59e0b;">
                            <i class="fas fa-tasks"></i>
                        </span>
                        <div>
                            <span class="quick-stat-number"><?= count($functions) ?></span>
                            <span class="quick-stat-label">Chức năng</span>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* ── Breadcrumb ── */
.breadcrumb-bar {
    display: flex; align-items: center; gap: 8px;
    margin-bottom: 20px; font-size: 0.85rem; color: var(--text-muted);
}
.breadcrumb-bar a { color: var(--primary-light); text-decoration: none; }
.breadcrumb-bar a:hover { text-decoration: underline; }
.breadcrumb-bar i.fa-chevron-right { font-size: 0.6rem; }

/* ── Detail Header ── */
.detail-header {
    padding: 28px 32px; border-radius: 16px; margin-bottom: 24px;
    border: 1px solid rgba(255,255,255,0.08);
}
.detail-header-content { display: flex; align-items: center; gap: 24px; }
.detail-header-icon {
    width: 64px; height: 64px; border-radius: 16px;
    background: rgba(255,255,255,0.08); 
    display: flex; align-items: center; justify-content: center;
    font-size: 1.5rem; flex-shrink: 0;
}
.detail-header-meta { display: flex; align-items: center; gap: 8px; margin-bottom: 8px; flex-wrap: wrap; }
.detail-code { font-size: 0.8rem; font-weight: 700; color: var(--primary-light); }
.detail-title { font-size: 1.4rem; font-weight: 800; margin-bottom: 4px; }
.detail-parent { font-size: 0.8rem; color: var(--text-muted); }
.badge-active { background: rgba(16,185,129,0.15); color: #10b981; }
.badge-inactive { background: rgba(239,68,68,0.15); color: #ef4444; }
.badge-warning { background: rgba(245,158,11,0.15); color: #f59e0b; }

/* ── Detail Grid ── */
.detail-grid { display: grid; grid-template-columns: 1fr 340px; gap: 24px; }
@media (max-width: 1024px) { .detail-grid { grid-template-columns: 1fr; } }

.detail-section { margin-bottom: 0; }
.detail-main { display: flex; flex-direction: column; gap: 20px; }
.detail-sidebar { display: flex; flex-direction: column; gap: 20px; }

/* ── Description ── */
.detail-description { font-size: 0.9rem; line-height: 1.7; color: var(--text-secondary); }

/* ── Functions List ── */
.functions-list { }
.function-item {
    display: flex; align-items: flex-start; gap: 14px;
    padding: 14px 20px;
    border-bottom: 1px solid rgba(255,255,255,0.04);
    transition: background 0.2s ease;
}
.function-item:hover { background: rgba(99,102,241,0.04); }
.function-item:last-child { border-bottom: none; }
.function-number {
    width: 28px; height: 28px; border-radius: 8px;
    background: linear-gradient(135deg, rgba(99,102,241,0.2), rgba(139,92,246,0.15));
    color: var(--primary-light);
    display: flex; align-items: center; justify-content: center;
    font-size: 0.7rem; font-weight: 700; flex-shrink: 0;
}
.function-text { font-size: 0.85rem; color: var(--text-secondary); line-height: 1.5; padding-top: 3px; }

/* ── Manager Card ── */
.manager-card { display: flex; align-items: center; gap: 16px; }
.manager-avatar {
    width: 56px; height: 56px; border-radius: 14px;
    background: linear-gradient(135deg, var(--primary), var(--primary-light));
    display: flex; align-items: center; justify-content: center;
    font-size: 1.4rem; font-weight: 700; color: #fff;
    flex-shrink: 0;
}
.manager-info { display: flex; flex-direction: column; gap: 3px; }
.manager-info h4 { font-size: 1rem; font-weight: 700; }
.manager-code { font-size: 0.75rem; color: var(--primary-light); font-weight: 600; }
.manager-position { font-size: 0.8rem; color: var(--text-muted); }
.manager-contact { font-size: 0.75rem; color: var(--text-muted); }
.manager-contact i { width: 16px; text-align: center; margin-right: 4px; }

/* ── Contact List ── */
.contact-list { display: flex; flex-direction: column; gap: 14px; }
.contact-item { display: flex; align-items: flex-start; gap: 12px; }
.contact-icon {
    width: 34px; height: 34px; border-radius: 8px;
    background: rgba(99,102,241,0.1); color: var(--primary-light);
    display: flex; align-items: center; justify-content: center;
    font-size: 0.8rem; flex-shrink: 0;
}
.contact-label { display: block; font-size: 0.7rem; color: var(--text-muted); margin-bottom: 2px; }
.contact-value { display: block; font-size: 0.8rem; color: var(--text-secondary); }

/* ── Quick Stats ── */
.quick-stats { display: flex; flex-direction: column; gap: 12px; }
.quick-stat { display: flex; align-items: center; gap: 12px; }
.quick-stat-icon {
    width: 40px; height: 40px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.9rem; flex-shrink: 0;
}
.quick-stat-number { display: block; font-size: 1.2rem; font-weight: 800; line-height: 1; }
.quick-stat-label { display: block; font-size: 0.7rem; color: var(--text-muted); margin-top: 2px; }

/* ── Children List ── */
.children-list { }
.child-item {
    display: flex; align-items: center; gap: 14px;
    padding: 12px 18px; text-decoration: none; color: inherit;
    border-bottom: 1px solid rgba(255,255,255,0.04);
    transition: all 0.2s ease;
}
.child-item:hover { background: rgba(99,102,241,0.05); }
.child-item:last-child { border-bottom: none; }
.child-icon {
    width: 36px; height: 36px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.85rem; flex-shrink: 0;
}
.child-icon-division { background: rgba(245,158,11,0.15); color: #f59e0b; }
.child-icon-department { background: rgba(99,102,241,0.15); color: var(--primary-light); }
.child-icon-team { background: rgba(59,130,246,0.15); color: #3b82f6; }
.child-icon-project { background: rgba(16,185,129,0.15); color: #10b981; }
.child-info { flex: 1; }
.child-info h5 { font-size: 0.85rem; font-weight: 600; margin-bottom: 3px; }

/* ── Text Link ── */
.text-link { color: var(--primary-light); text-decoration: none; }
.text-link:hover { text-decoration: underline; }
.text-muted { color: var(--text-muted); }

/* ── Type Badges ── */
.dept-type-badge {
    font-size: 0.65rem; padding: 2px 8px; border-radius: 6px;
    font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;
}
.badge-division { background: rgba(245,158,11,0.15); color: #f59e0b; }
.badge-department { background: rgba(99,102,241,0.15); color: var(--primary-light); }
.badge-team { background: rgba(59,130,246,0.15); color: #3b82f6; }
.badge-project { background: rgba(16,185,129,0.15); color: #10b981; }
.badge-primary { background: rgba(99,102,241,0.2); color: var(--primary-light); }
</style>
