<?php
// Helper function to render project org chart recursively
if (!function_exists('renderProjectOrgChart')) {
    function renderProjectOrgChart(array $nodes, int $level = 0): void
    {
        $levelClass = $level === 0 ? 'org-level-root' : 'org-level-child';
        echo '<div class="org-chart-level ' . $levelClass . '">';
        foreach ($nodes as $node) {
            $hasChildren = !empty($node['children']);
            if (!empty($node['avatar_path'])) {
                $avatar = (strpos($node['avatar_path'], 'http') === 0) ? $node['avatar_path'] : BASE_URL . '/public/' . $node['avatar_path'];
            } else {
                $avatar = 'https://ui-avatars.com/api/?name='.urlencode($node['full_name']).'&background=random';
            }
            
            echo '<div class="org-chart-node">';
            echo '<div class="org-chart-card" onclick="window.open(\'' . BASE_URL . '/employee/detail/' . $node['id'] . '\', \'_blank\')" style="cursor: pointer;" title="Xem chi tiết hồ sơ">';
            
            // Avatar
            echo '<img src="'.h($avatar).'" alt="Avatar" class="org-chart-avatar" loading="lazy">';
            
            // Info
            echo '<div class="org-chart-name">' . h($node['full_name']) . '</div>';
            echo '<div class="org-chart-code">' . h($node['emp_code']) . '</div>';
            echo '<div class="org-chart-pos">' . h($node['pos_title'] ?? 'Nhân viên') . '</div>';
            
            if (!empty($node['phone'])) {
                echo '<div class="org-chart-phone"><i class="fas fa-phone-alt"></i> ' . h($node['phone']) . '</div>';
            }
            
            echo '</div>';
            
            // Connector line
            if ($hasChildren) {
                echo '<div class="org-chart-connector"></div>';
                renderProjectOrgChart($node['children'], $level + 1);
            }
            
            echo '</div>';
        }
        echo '</div>';
    }
}
?>
<!-- ══════════════════════════════════════════════════════════
     CHI TIẾT DỰ ÁN (V2)
     ══════════════════════════════════════════════════════════ -->

<div class="breadcrumb-bar no-print">
    <a href="<?= BASE_URL ?>/project"><i class="fas fa-hard-hat"></i> Danh sách Dự án</a>
    <i class="fas fa-chevron-right"></i>
    <span><?= h($project['project_name']) ?></span>
</div>

<!-- Tabs Control -->
<div class="org-tabs no-print">
    <button class="tab-btn active" onclick="switchTab('listTab', this)"><i class="fas fa-list"></i> Danh sách Nhân sự</button>
    <button class="tab-btn" onclick="switchTab('chartTab', this)"><i class="fas fa-sitemap"></i> Sơ đồ Cơ cấu</button>
    <button class="tab-btn" onclick="switchTab('historyTab', this)"><i class="fas fa-history"></i> Lịch sử thuyên chuyển</button>
    <button class="tab-btn" onclick="switchTab('approvalTab', this)"><i class="fas fa-clipboard-check"></i> Phê duyệt Yêu cầu</button>
    <button class="tab-btn" onclick="switchTab('hseTab', this)"><i class="fas fa-shield-alt text-danger"></i> An toàn & HSE</button>
    <button class="tab-btn" onclick="window.print()" style="margin-left:auto; background:var(--primary); color:white; border-radius: 6px;"><i class="fas fa-print"></i> In Sơ đồ</button>
</div>

<!-- TAB 1: DANH SÁCH -->
<div id="listTab" class="tab-content active">
    <div class="panel" style="margin-bottom: 24px; background: linear-gradient(135deg, rgba(16,185,129,0.05) 0%, rgba(5,150,105,0.02) 100%);">
        <div class="panel-body">
            <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                <div>
                    <div style="display:flex; align-items:center; gap: 12px; margin-bottom: 8px;">
                        <h2 style="margin: 0; color:var(--primary-light);"><?= h($project['project_name']) ?> (<?= h($project['project_code']) ?>)</h2>
                        <?php if (Session::isManager() || Session::userRole() === 'project_manager'): ?>
                        <button class="btn btn-sm btn-outline-primary" onclick="openEditProjectModal()" title="Chỉnh sửa Dự án">
                            <i class="fas fa-edit"></i> Chỉnh sửa
                        </button>
                        <?php endif; ?>
                    </div>
                    <p style="color:var(--text-muted);"><i class="fas fa-building"></i> Khách hàng: <?= h($project['client_name']) ?></p>
                    <p style="color:var(--text-muted);"><i class="fas fa-map-marker-alt"></i> Địa điểm: <?= h($project['location']) ?></p>
                    <p style="color:var(--text-muted);"><i class="fas fa-money-check-alt"></i> Cost Center: <strong><?= h($project['cost_center_code']) ?></strong></p>
                    <div style="margin-top: 12px;">
                        <p style="color:var(--text-muted); margin-bottom: 4px;"><i class="fas fa-tasks"></i> Tiến độ dự án: <strong><?= (int)($project['progress_percent'] ?? 0) ?>%</strong></p>
                        <div class="progress-bar-wrapper" style="max-width: 300px; height: 8px;">
                            <div class="progress-bar" style="width: <?= (int)($project['progress_percent'] ?? 0) ?>%; background: var(--primary-light);"></div>
                        </div>
                    </div>
                </div>
                
                <?php 
                    $pct = $stats['quota'] > 0 ? round(($stats['actual'] / $stats['quota']) * 100) : 0;
                    $totalReal = $stats['actual'] + $stats['incoming'];
                ?>
                <div style="text-align:right; min-width: 200px;">
                    <div style="font-size: 2rem; font-weight:700; color: #10b981;"><?= $stats['actual'] ?> <span style="font-size:1rem; color:var(--text-muted);">/ <?= $stats['quota'] ?></span></div>
                    <div style="font-size: 0.85rem; color:var(--text-muted); margin-bottom: 8px;">Nhân sự cơ hữu hiện tại</div>
                    <div class="progress-bar-wrapper">
                        <div class="progress-bar" style="width: <?= $pct ?>%; background: <?= $pct < 50 ? '#ef4444' : ($pct < 90 ? '#f59e0b' : '#10b981') ?>;"></div>
                    </div>
                    <?php if($stats['missing'] > 0): ?>
                        <div style="margin-top:8px; font-size:0.8rem; color:#ef4444;"><i class="fas fa-exclamation-triangle"></i> Đang thiếu <?= $stats['missing'] ?> người</div>
                    <?php endif; ?>
                    <?php if($stats['incoming'] > 0): ?>
                        <div style="margin-top:4px; font-size:0.8rem; color:#3b82f6;"><i class="fas fa-truck-moving"></i> Điều động đến: +<?= $stats['incoming'] ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="panel">
        <div class="panel-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h3><i class="fas fa-users"></i> Danh sách Nhân sự Hiện hành</h3>
            <button class="btn btn-primary btn-sm" onclick="openAssignModal()"><i class="fas fa-user-plus"></i> Phân bổ nhân sự</button>
        </div>
        <div class="panel-body p-0">
            <?php if (empty($personnel)): ?>
                <div class="empty-state"><i class="fas fa-users-slash"></i><p>Chưa có nhân sự nào được điều động đến dự án này.</p></div>
            <?php else: ?>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Mã NV</th>
                                <th>Họ Tên</th>
                                <th>Chức danh</th>
                                <th>Phòng ban gốc</th>
                                <th style="text-align:center;">Quốc tịch</th>
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($personnel as $emp): ?>
                            <tr>
                                <td><a href="<?= BASE_URL ?>/employee/detail/<?= $emp['id'] ?>" style="color:var(--text-main); text-decoration:none;"><strong><?= h($emp['emp_code'] ?? '') ?></strong></a></td>
                                <td><a href="<?= BASE_URL ?>/employee/detail/<?= $emp['id'] ?>" style="color:var(--primary); font-weight:600; text-decoration:none;"><?= h($emp['full_name']) ?></a></td>
                                <td>
                                    <?php
                                    $pos = h($emp['pos_title'] ?? '—');
                                    if (stripos($pos, 'Kỹ sư') !== false || stripos($pos, 'Engineer') !== false) {
                                        echo '<span class="badge" style="background:#dbeafe; color:#2563eb;"><i class="fas fa-laptop-code"></i> ' . $pos . '</span>';
                                    } elseif (stripos($pos, 'Thợ hàn') !== false || stripos($pos, 'Welder') !== false) {
                                        echo '<span class="badge" style="background:#ffedd5; color:#ea580c;"><i class="fas fa-fire"></i> ' . $pos . '</span>';
                                    } else {
                                        echo $pos;
                                    }
                                    ?>
                                </td>
                                <td><?= h($emp['dept_name'] ?? '—') ?></td>
                                <td style="text-align:center;">
                                    <?php if (!empty($emp['is_expat'])): ?>
                                        <span class="badge" style="background:rgba(99,102,241,0.15); color:#6366f1;">Expat</span>
                                    <?php else: ?>
                                        <span style="font-size:0.85rem; color:var(--text-muted);">VN</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($emp['status'] === 'active' || $emp['status'] === 'Active'): ?>
                                        <span class="badge badge-active">Đang làm việc</span>
                                    <?php elseif ($emp['status'] === 'probation' || $emp['status'] === 'Probation'): ?>
                                        <span class="badge badge-probation">Thử việc</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary"><?= h($emp['status']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-danger" onclick="removeEmployee(<?= $emp['id'] ?>)" title="Rút khỏi dự án">
                                        <i class="fas fa-sign-out-alt"></i> Rút
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- TAB 2: SƠ ĐỒ TỔ CHỨC -->
<div id="chartTab" class="tab-content" style="display:none;">
    <div class="printable-area">
        <h2 class="print-only" style="text-align: center; margin-bottom: 20px;">SƠ ĐỒ CƠ CẤU TỔ CHỨC: <?= h($project['project_name']) ?></h2>
        <div class="org-chart-wrapper">
            <?php if (empty($orgChart)): ?>
                <div class="empty-state"><i class="fas fa-sitemap"></i><p>Chưa có cấu trúc nhân sự.</p></div>
            <?php else: ?>
                <?php renderProjectOrgChart($orgChart, 0); ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- TAB 3: LỊCH SỬ THUYÊN CHUYỂN -->
<div id="historyTab" class="tab-content" style="display:none;">
    <div class="panel">
        <div class="panel-header">
            <h3><i class="fas fa-history"></i> Quá trình nhân sự dự án</h3>
        </div>
        <div class="panel-body p-0">
            <?php if (empty($movements)): ?>
                <div class="empty-state"><i class="fas fa-file-alt"></i><p>Chưa có lịch sử thuyên chuyển nào liên quan đến dự án này.</p></div>
            <?php else: ?>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Ngày hiệu lực</th>
                                <th>Mã NV</th>
                                <th>Họ Tên</th>
                                <th>Chức danh</th>
                                <th>Loại</th>
                                <th>Phòng/Dự án</th>
                                <th>Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($movements as $m): ?>
                            <tr>
                                <td><?= date('d/m/Y', strtotime($m['effective_date'])) ?></td>
                                <td><strong><a href="<?= BASE_URL ?>/employee/detail/<?= $m['employee_id'] ?>" target="_blank" style="color:var(--text-main); text-decoration:none;"><?= h($m['emp_code'] ?? '') ?></a></strong></td>
                                <td><strong><a href="<?= BASE_URL ?>/employee/detail/<?= $m['employee_id'] ?>" target="_blank" style="color:var(--primary); text-decoration:none;"><?= h($m['full_name']) ?></a></strong></td>
                                <td><?= h($m['pos_title'] ?? '') ?></td>
                                <td>
                                    <?php
                                    if ($m['movement_type'] === 'transfer') echo '<span class="badge" style="background:#e0e7ff; color:#4f46e5;">Điều động</span>';
                                    elseif ($m['movement_type'] === 'promotion') echo '<span class="badge" style="background:#dcfce7; color:#16a34a;">Thăng tiến</span>';
                                    else echo '<span class="badge badge-secondary">Khác</span>';
                                    ?>
                                </td>
                                <td>
                                    <?php 
                                    if ($m['to_project_id'] == $project['id']) {
                                        echo '<span style="color:#10b981;"><i class="fas fa-arrow-right"></i> Vào dự án</span> (từ '.h($m['dept_name'] ?? 'Khác').')';
                                    } elseif ($m['from_project_id'] == $project['id']) {
                                        echo '<span style="color:#ef4444;"><i class="fas fa-arrow-left"></i> Rời dự án</span> (về '.h($m['dept_name'] ?? 'Khác').')';
                                    } else {
                                        echo h($m['dept_name'] ?? '');
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    if ($m['status'] === 'approved') echo '<span class="badge badge-active">Đã duyệt</span>';
                                    elseif ($m['status'] === 'pending') echo '<span class="badge badge-probation">Chờ duyệt</span>';
                                    else echo '<span class="badge badge-secondary">Từ chối</span>';
                                    ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
/* Breadcrumb */
.breadcrumb-bar { display: flex; align-items: center; gap: 8px; margin-bottom: 20px; font-size: 0.85rem; color: var(--text-muted); }
.breadcrumb-bar a { color: var(--primary-light); text-decoration: none; }

.progress-bar-wrapper { width: 100%; height: 6px; background: rgba(255,255,255,0.1); border-radius: 3px; overflow: hidden; }
.progress-bar { height: 100%; border-radius: 3px; transition: width 0.5s; }

/* ── Tabs ── */
.org-tabs { display: flex; gap: 12px; margin-bottom: 24px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 8px; }
.tab-btn { background: transparent; border: none; color: var(--text-muted); font-size: 1rem; padding: 8px 16px; cursor: pointer; transition: 0.3s; font-weight: 600; border-radius: 8px; }
.tab-btn:hover { background: rgba(255,255,255,0.05); color: var(--text-main); }
.tab-btn.active { background: rgba(99,102,241,0.15); color: var(--primary-light); }

/* ── Org Chart Card-Based ── */
.org-chart-wrapper { padding: 24px 0; overflow-x: auto; text-align: center; }
.org-chart-level { display: flex; justify-content: center; flex-wrap: nowrap; margin-bottom: 20px; }
.org-level-root { margin-bottom: 40px; }
.org-chart-node { display: flex; flex-direction: column; align-items: center; position: relative; padding: 30px 12px 0 12px; }
.org-level-root > .org-chart-node { padding-top: 0; }

.org-chart-card {
    display: flex; flex-direction: column; align-items: center;
    min-width: 160px; max-width: 180px; padding: 16px; border-radius: 12px;
    background: var(--bg-card); border: 1px solid rgba(255,255,255,0.08);
    box-shadow: 0 4px 12px rgba(0,0,0,0.2); transition: all 0.3s ease;
    position: relative; z-index: 2;
}
.org-chart-card::before {
    content: ''; position: absolute; top: 0; left: 0; right: 0; height: 4px;
    background: linear-gradient(90deg, var(--primary), var(--primary-light));
    border-radius: 12px 12px 0 0;
}
.org-chart-card:hover { transform: translateY(-4px); box-shadow: 0 8px 24px rgba(99,102,241,0.3); border-color: rgba(99,102,241,0.5); }

.org-chart-avatar { width: 50px; height: 50px; border-radius: 50%; object-fit: cover; margin-bottom: 10px; border: 2px solid var(--primary-light); }
.org-chart-name { font-weight: 700; font-size: 0.9rem; line-height: 1.3; margin-bottom: 4px; color: var(--text-main); text-align: center; }
.org-chart-code { font-size: 0.75rem; color: var(--primary-light); font-weight: 600; margin-bottom: 4px; background: rgba(99,102,241,0.1); padding: 2px 6px; border-radius: 4px;}
.org-chart-pos { font-size: 0.75rem; color: var(--text-muted); font-weight: 500; text-align: center; margin-bottom: 6px; }
.org-chart-phone { font-size: 0.7rem; color: var(--text-muted); }

/* Connectors */
.org-chart-connector { width: 2px; height: 30px; background: #4b5563; margin: 0 auto; position: relative; z-index: 1; }

.org-chart-node:not(:only-child)::before {
    content: ''; position: absolute; top: 0; left: 50%; width: 2px; height: 30px; background: #4b5563; margin-left: -1px;
}
.org-chart-node:only-child::before {
    content: ''; position: absolute; top: 0; left: 50%; width: 2px; height: 30px; background: #4b5563; margin-left: -1px;
}
.org-level-root > .org-chart-node::before { display: none; }

.org-chart-node:not(:first-child):not(:last-child)::after {
    content: ''; position: absolute; top: 0; left: 0; right: 0; height: 2px; background: #4b5563;
}
.org-chart-node:first-child:not(:only-child)::after {
    content: ''; position: absolute; top: 0; left: 50%; right: 0; height: 2px; background: #4b5563;
}
.org-chart-node:last-child:not(:only-child)::after {
    content: ''; position: absolute; top: 0; left: 0; right: 50%; height: 2px; background: #4b5563;
}

/* ── Print Styles ── */
.print-only { display: none; }
@media print {
    body { background: white !important; color: black !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .no-print { display: none !important; }
    .print-only { display: block !important; text-align: center; margin-bottom: 20px; font-size: 24px; font-weight: bold; text-transform: uppercase; }
    .sidebar, .topbar, .breadcrumb-bar, .org-tabs { display: none !important; }
    .main-content { margin: 0 !important; padding: 0 !important; width: 100% !important; max-width: 100% !important; background: white !important; }
    
    /* Force show chart tab, hide others */
    .tab-content { display: none !important; }
    #chartTab { display: block !important; }
    
    .org-chart-wrapper { padding: 0 !important; overflow: visible !important; }
    .org-chart-card { 
        background: white !important; 
        border: 2px solid #3b82f6 !important;
        box-shadow: none !important;
        color: black !important;
        page-break-inside: avoid;
        break-inside: avoid;
    }
    .org-chart-card::before { display: block !important; background: #3b82f6 !important; }
    .org-chart-name { color: #000 !important; font-size: 14px !important; }
    .org-chart-code { background: #e0e7ff !important; color: #3730a3 !important; border: 1px solid #c7d2fe !important; }
    .org-chart-pos, .org-chart-phone { color: #444 !important; }
    .org-chart-connector, .org-chart-node::before, .org-chart-node::after { background: #3b82f6 !important; }
    
    @page { size: A4 landscape; margin: 10mm; }
}
</style>

<script>
function switchTab(tabId, btn) {
    document.querySelectorAll('.tab-content').forEach(el => el.style.display = 'none');
    document.getElementById(tabId).style.display = 'block';
    
    document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
    btn.classList.add('active');
}
</script>

<!-- TAB 3: PHÊ DUYỆT YÊU CẦU -->
<div id="approvalTab" class="tab-content" style="display:none;">
    <div class="panel glass-panel">
        <h3 style="margin-bottom: 20px;"><i class="fas fa-clipboard-check text-gradient"></i> Các yêu cầu cần phê duyệt</h3>
        <div class="empty-state">
            <i class="fas fa-inbox"></i>
            <p>Hiện không có yêu cầu nghỉ phép hay thuyên chuyển nào cần duyệt từ dự án này.</p>
        </div>
    </div>
</div>

<!-- TAB 4: AN TOÀN & HSE -->
<div id="hseTab" class="tab-content" style="display:none;">
    <div class="row">
        <!-- Sự cố HSE -->
        <div class="col-md-6">
            <div class="panel" style="border: 1px solid #f59e0b;">
                <div class="panel-header" style="background: #f59e0b;">
                    <h3 class="m-0" style="color: #fff;"><i class="fas fa-exclamation-triangle"></i> Sự cố An toàn (Incidents)</h3>
                </div>
                <div class="panel-body p-0">
                    <?php if (empty($hseIncidents)): ?>
                        <div class="empty-state"><p class="text-success m-0 p-3"><i class="fas fa-check"></i> Không có sự cố nào.</p></div>
                    <?php else: ?>
                        <div class="table-wrapper">
                            <table class="table mb-0">
                                <thead>
                                    <tr>
                                        <th>Ngày</th>
                                        <th>Mức độ</th>
                                        <th>Mô tả</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($hseIncidents as $inc): ?>
                                    <tr>
                                        <td><?= date('d/m/Y', strtotime($inc['incident_date'])) ?></td>
                                        <td><span class="badge" style="background: <?= $inc['severity'] == 'High' ? '#ef4444' : '#f59e0b' ?>;"><?= h($inc['severity']) ?></span></td>
                                        <td title="<?= h($inc['description']) ?>"><?= mb_strimwidth(h($inc['description']), 0, 40, '...') ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-warning" onclick="alert('CHI TIẾT SỰ CỐ:\n\n<?= addslashes($inc['description']) ?>\n\nMức độ: <?= h($inc['severity']) ?>\nNgười liên quan: <?= addslashes($inc['full_name'] ?? 'Không rõ') ?> (<?= addslashes($inc['emp_code'] ?? '') ?>)\nPhòng ban: <?= addslashes($inc['dept_name'] ?? 'Không rõ') ?>')"><i class="fas fa-eye"></i></button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Vi phạm HSE -->
        <div class="col-md-6">
            <div class="panel" style="border: 1px solid #ef4444;">
                <div class="panel-header" style="background: #ef4444;">
                    <h3 class="m-0" style="color: #fff;"><i class="fas fa-times-circle"></i> Vi phạm Quy định (Violations)</h3>
                </div>
                <div class="panel-body p-0">
                    <?php if (empty($hseViolations)): ?>
                        <div class="empty-state"><p class="text-success m-0 p-3"><i class="fas fa-check"></i> Không có vi phạm nào.</p></div>
                    <?php else: ?>
                        <div class="table-wrapper">
                            <table class="table mb-0">
                                <thead>
                                    <tr>
                                        <th>Ngày</th>
                                        <th>Người vi phạm</th>
                                        <th>Lỗi</th>
                                        <th>Phạt</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($hseViolations as $vio): ?>
                                    <tr>
                                        <td><?= date('d/m/Y', strtotime($vio['violation_date'])) ?></td>
                                        <td><a href="<?= BASE_URL ?>/employee/detail/<?= $vio['emp_id'] ?? 0 ?>"><?= h($vio['full_name'] ?? '—') ?></a> <br><small class="text-muted"><?= h($vio['emp_code'] ?? '') ?></small></td>
                                        <td title="<?= h($vio['description']) ?>"><?= mb_strimwidth(h($vio['description']), 0, 40, '...') ?></td>
                                        <td class="text-danger fw-bold"><?= number_format($vio['penalty_amount'] ?? 0, 0, ',', '.') ?>đ</td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-info" onclick="alert('CHI TIẾT VI PHẠM:\n\nNgười vi phạm: <?= addslashes($vio['full_name'] ?? '—') ?> (<?= addslashes($vio['emp_code'] ?? '') ?>)\nPhòng ban: <?= addslashes($vio['dept_name'] ?? 'Không rõ') ?>\n\nLỗi vi phạm: <?= addslashes($vio['description']) ?>\n\nMức phạt: <?= number_format($vio['penalty_amount'] ?? 0, 0, ',', '.') ?>đ')"><i class="fas fa-eye"></i></button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Phân bổ nhân sự -->
<div id="assignModal" class="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
    <div class="modal-content panel glass-panel" style="width:700px; max-width:90%; padding:24px; position:relative;">
        <span onclick="closeAssignModal()" style="position:absolute; top:15px; right:20px; font-size:24px; cursor:pointer; color:#64748b;">&times;</span>
        <h3 style="margin-bottom:20px; color:#0f172a;"><i class="fas fa-user-plus text-gradient"></i> Phân bổ nhân sự vào dự án</h3>
        
        <!-- Smart Matcher Tabs -->
        <div style="display:flex; gap:10px; margin-bottom:15px; border-bottom:1px solid #e2e8f0; padding-bottom:10px;">
            <button class="btn btn-sm btn-primary" id="btnTabAll" onclick="toggleAssignTab('all')"><i class="fas fa-list"></i> Danh sách tất cả</button>
            <button class="btn btn-sm btn-ghost" id="btnTabAI" onclick="toggleAssignTab('ai')"><i class="fas fa-robot text-purple" style="color:#8b5cf6;"></i> AI Gợi ý thông minh</button>
        </div>

        <!-- TAB All -->
        <div id="assignTabAll">
            <p style="color:#475569; font-size:0.9rem; margin-bottom:10px;">Chọn các nhân sự chưa thuộc dự án nào để điều động vào <strong><?= h($project['project_name']) ?></strong>.</p>
            <div style="max-height:300px; overflow-y:auto; border:1px solid #e2e8f0; border-radius:8px; padding:10px; margin-bottom:20px;" id="empListContainer">
                <div style="text-align:center; padding:20px; color:#94a3b8;"><i class="fas fa-spinner fa-spin"></i> Đang tải dữ liệu...</div>
            </div>
        </div>

        <!-- TAB AI -->
        <div id="assignTabAI" style="display:none;">
            <p style="color:#475569; font-size:0.9rem; margin-bottom:10px;">Nhập từ khóa kỹ năng, chức danh hoặc chứng chỉ (VD: Hàn, An toàn, Kỹ sư). Hệ thống sẽ quét và chấm điểm phù hợp.</p>
            <div style="display:flex; gap:10px; margin-bottom:15px;">
                <input type="text" id="aiKeyword" class="form-control" placeholder="Từ khóa tìm kiếm..." style="flex:1;">
                <button class="btn btn-primary" onclick="searchAIMatch()"><i class="fas fa-search"></i> Tìm kiếm</button>
            </div>
            <div style="margin-bottom:15px;">
                <span style="font-size:0.85rem; color:var(--text-muted); margin-right:5px;">Gợi ý từ AI:</span>
                <span class="badge" style="background:#e0e7ff; color:#4f46e5; cursor:pointer; margin-right:5px;" onclick="document.getElementById('aiKeyword').value='Kỹ sư M&E'; searchAIMatch();">Kỹ sư M&E</span>
                <span class="badge" style="background:#e0e7ff; color:#4f46e5; cursor:pointer; margin-right:5px;" onclick="document.getElementById('aiKeyword').value='Thợ hàn 6G'; searchAIMatch();">Thợ hàn 6G</span>
                <span class="badge" style="background:#e0e7ff; color:#4f46e5; cursor:pointer; margin-right:5px;" onclick="document.getElementById('aiKeyword').value='An toàn HSE'; searchAIMatch();">An toàn HSE</span>
                <span class="badge" style="background:#e0e7ff; color:#4f46e5; cursor:pointer; margin-right:5px;" onclick="document.getElementById('aiKeyword').value='Kế toán'; searchAIMatch();">Kế toán</span>
            </div>
            <div style="max-height:260px; overflow-y:auto; border:1px solid #e2e8f0; border-radius:8px; padding:10px; margin-bottom:20px;" id="aiResultContainer">
                <div style="text-align:center; padding:20px; color:#94a3b8;"><i class="fas fa-robot text-purple"></i> Nhập từ khóa để AI gợi ý.</div>
            </div>
        </div>

        <div style="text-align:right;">
            <button class="btn btn-outline-secondary" onclick="closeAssignModal()">Hủy bỏ</button>
            <button class="btn btn-success" onclick="submitAssign()" id="btnSubmitAssign"><i class="fas fa-check"></i> Xác nhận Phân bổ</button>
        </div>
    </div>
</div>

<!-- Modal Sửa Dự án -->
<div id="editProjectModal" class="modal-overlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:9999; align-items:center; justify-content:center; backdrop-filter:blur(4px);">
    <div class="modal-content glass-panel" style="width:600px; max-width:90%; padding:24px; position:relative; background:var(--bg-card); border-radius:16px; border: 1px solid rgba(255,255,255,0.08);">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 20px; border-bottom: 1px solid rgba(255,255,255,0.06); padding-bottom:15px;">
            <h3 style="margin:0;"><i class="fas fa-edit"></i> Chỉnh sửa Dự án</h3>
            <button onclick="closeEditProjectModal()" style="background:none; border:none; color:var(--text-muted); font-size:1.5rem; cursor:pointer;">&times;</button>
        </div>
        <form method="POST" action="<?= BASE_URL ?>/project/store">
            <input type="hidden" name="_csrf_token" value="<?= Session::generateCsrfToken() ?>">
            <input type="hidden" name="id" value="<?= $project['id'] ?>">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                <div class="form-group" style="grid-column: 1 / -1;">
                    <label>Tên dự án <span class="required">*</span></label>
                    <input type="text" name="project_name" required value="<?= h($project['project_name']) ?>" style="width:100%; padding:10px; border-radius:8px; border:1px solid rgba(255,255,255,0.1); background:rgba(255,255,255,0.04); color:var(--text-primary);">
                </div>
                <div class="form-group">
                    <label>Mã dự án <span class="required">*</span></label>
                    <input type="text" name="project_code" required value="<?= h($project['project_code']) ?>" style="width:100%; padding:10px; border-radius:8px; border:1px solid rgba(255,255,255,0.1); background:rgba(255,255,255,0.04); color:var(--text-primary);">
                </div>
                <div class="form-group">
                    <label>Tiến độ (%)</label>
                    <input type="number" name="progress_percent" min="0" max="100" value="<?= (int)($project['progress_percent'] ?? 0) ?>" style="width:100%; padding:10px; border-radius:8px; border:1px solid rgba(255,255,255,0.1); background:rgba(255,255,255,0.04); color:var(--text-primary);">
                </div>
                <div class="form-group">
                    <label>Khách hàng</label>
                    <input type="text" name="client_name" value="<?= h($project['client_name']) ?>" style="width:100%; padding:10px; border-radius:8px; border:1px solid rgba(255,255,255,0.1); background:rgba(255,255,255,0.04); color:var(--text-primary);">
                </div>
                <div class="form-group">
                    <label>Mã Cost Center</label>
                    <input type="text" name="cost_center_code" value="<?= h($project['cost_center_code']) ?>" style="width:100%; padding:10px; border-radius:8px; border:1px solid rgba(255,255,255,0.1); background:rgba(255,255,255,0.04); color:var(--text-primary);">
                </div>
                <div class="form-group" style="grid-column: 1 / -1;">
                    <label>Địa điểm</label>
                    <input type="text" name="location" value="<?= h($project['location']) ?>" style="width:100%; padding:10px; border-radius:8px; border:1px solid rgba(255,255,255,0.1); background:rgba(255,255,255,0.04); color:var(--text-primary);">
                </div>
            </div>
            <div style="margin-top:20px; padding-top:15px; border-top:1px solid rgba(255,255,255,0.06); text-align:right;">
                <button type="button" class="btn btn-outline-secondary" onclick="closeEditProjectModal()">Hủy</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Cập nhật</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditProjectModal() {
    document.getElementById('editProjectModal').style.display = 'flex';
}
function closeEditProjectModal() {
    document.getElementById('editProjectModal').style.display = 'none';
}

function openAssignModal() {
    document.getElementById('assignModal').style.display = 'flex';
    toggleAssignTab('all');
    fetchAvailableEmployees();
}

function closeAssignModal() {
    document.getElementById('assignModal').style.display = 'none';
}

function toggleAssignTab(tab) {
    if (tab === 'all') {
        document.getElementById('assignTabAll').style.display = 'block';
        document.getElementById('assignTabAI').style.display = 'none';
        document.getElementById('btnTabAll').className = 'btn btn-sm btn-primary';
        document.getElementById('btnTabAI').className = 'btn btn-sm btn-ghost';
    } else {
        document.getElementById('assignTabAll').style.display = 'none';
        document.getElementById('assignTabAI').style.display = 'block';
        document.getElementById('btnTabAll').className = 'btn btn-sm btn-ghost';
        document.getElementById('btnTabAI').className = 'btn btn-sm btn-primary';
    }
}

function searchAIMatch() {
    const keyword = document.getElementById('aiKeyword').value;
    const container = document.getElementById('aiResultContainer');
    if (!keyword.trim()) {
        alert('Vui lòng nhập từ khóa');
        return;
    }

    container.innerHTML = '<div style="text-align:center; padding:20px; color:#94a3b8;"><i class="fas fa-spinner fa-spin"></i> Đang quét dữ liệu...</div>';

    let formData = new URLSearchParams();
    formData.append('keyword', keyword);

    fetch('<?= BASE_URL ?>/project/ajaxSmartMatch', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: formData
    })
    .then(res => res.json())
    .then(res => {
        if(res.success && res.data.length > 0) {
            let html = '<table class="table table-sm"><thead><tr><th>Chọn</th><th>Nhân sự</th><th>Chức danh</th><th>Chứng chỉ khớp</th><th>Độ phù hợp</th></tr></thead><tbody>';
            res.data.forEach(emp => {
                let matchClass = emp.match_score >= 50 ? 'badge-active' : (emp.match_score >= 30 ? 'badge-probation' : 'badge-muted');
                html += `
                <tr>
                    <td style="text-align:center;">
                        <input type="checkbox" class="emp-checkbox" value="${emp.id}">
                    </td>
                    <td>
                        <strong><a href="<?= BASE_URL ?>/employee/detail/${emp.id}" target="_blank">${emp.full_name}</a></strong><br>
                        <small class="text-muted">${emp.emp_code}</small>
                    </td>
                    <td>${emp.pos_title || '-'}</td>
                    <td style="font-size:0.8rem; color:#8b5cf6;">${emp.matched_certs || '-'}</td>
                    <td style="text-align:center;"><span class="badge ${matchClass}">${emp.match_score} pts</span></td>
                </tr>`;
            });
            html += '</tbody></table>';
            container.innerHTML = html;
        } else {
            container.innerHTML = '<div style="text-align:center; padding:20px; color:#94a3b8;">Không tìm thấy nhân sự phù hợp với từ khóa.</div>';
        }
    })
    .catch(err => {
        container.innerHTML = '<div style="color:red; text-align:center;">Lỗi quét AI.</div>';
    });
}

function fetchAvailableEmployees() {
    const container = document.getElementById('empListContainer');
    fetch('<?= BASE_URL ?>/project/getAvailableEmployees')
    .then(res => res.json())
    .then(res => {
        if(res.success && res.data.length > 0) {
            let html = '<table class="table table-sm"><thead><tr><th>Chọn</th><th>Mã NV</th><th>Họ tên</th></tr></thead><tbody>';
            res.data.forEach(emp => {
                html += `
                <tr>
                    <td style="text-align:center;">
                        <input type="checkbox" class="emp-checkbox" value="${emp.id}">
                    </td>
                    <td>${emp.emp_code}</td>
                    <td><strong><a href="<?= BASE_URL ?>/employee/detail/${emp.id}" target="_blank">${emp.full_name}</a></strong></td>
                </tr>`;
            });
            html += '</tbody></table>';
            container.innerHTML = html;
        } else {
            container.innerHTML = '<div style="text-align:center; padding:20px; color:#94a3b8;">Không có nhân sự nào đang khả dụng.</div>';
        }
    })
    .catch(err => {
        container.innerHTML = '<div style="color:red; text-align:center;">Lỗi tải dữ liệu.</div>';
    });
}

function submitAssign() {
    const checkboxes = document.querySelectorAll('.emp-checkbox:checked');
    if(checkboxes.length === 0) {
        alert('Vui lòng chọn ít nhất 1 nhân sự.');
        return;
    }
    
    // Thu thập IDs và lọc trùng (nếu check cả 2 tab)
    let ids = new Set();
    checkboxes.forEach(cb => ids.add(cb.value));
    
    const btn = document.getElementById('btnSubmitAssign');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';

    let formData = new URLSearchParams();
    formData.append('project_id', '<?= $project['id'] ?>');
    ids.forEach(id => formData.append('employee_ids[]', id));

    fetch('<?= BASE_URL ?>/project/ajaxAssignPersonnel', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: formData
    })
    .then(res => res.json())
    .then(res => {
        if(res.success) {
            alert('Phân bổ nhân sự thành công!');
            location.reload();
        } else {
            alert('Lỗi: ' + res.message);
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check"></i> Xác nhận Phân bổ';
        }
    })
    .catch(err => {
        alert('Có lỗi kết nối máy chủ.');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check"></i> Xác nhận Phân bổ';
    });
}

function removeEmployee(empId) {
    if (!confirm('Bạn có chắc chắn muốn rút nhân sự này khỏi dự án?')) return;
    
    let formData = new URLSearchParams();
    formData.append('project_id', '<?= $project['id'] ?>');
    formData.append('employee_id', empId);

    fetch('<?= BASE_URL ?>/project/ajaxRemovePersonnel', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: formData
    })
    .then(res => res.json())
    .then(res => {
        if(res.success) {
            alert('Đã rút nhân sự khỏi dự án.');
            location.reload();
        } else {
            alert('Lỗi: ' + res.message);
        }
    })
    .catch(err => {
        alert('Có lỗi kết nối máy chủ.');
    });
}
</script>
