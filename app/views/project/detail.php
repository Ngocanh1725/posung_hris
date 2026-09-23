<?php
// Helper function to render project org chart recursively
if (!function_exists('renderProjectOrgChart')) {
    function renderProjectOrgChart(array $nodes, int $level = 0): void
    {
        $levelClass = $level === 0 ? 'org-level-root' : 'org-level-child';
        echo '<div class="org-chart-level ' . $levelClass . '">';
        foreach ($nodes as $node) {
            $hasChildren = !empty($node['children']);
            $avatar = !empty($node['avatar_path']) ? BASE_URL . '/public/' . $node['avatar_path'] : 'https://ui-avatars.com/api/?name='.urlencode($node['full_name']).'&background=random';
            
            echo '<div class="org-chart-node">';
            echo '<div class="org-chart-card">';
            
            // Avatar
            echo '<img src="'.h($avatar).'" alt="Avatar" class="org-chart-avatar">';
            
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
    <button class="tab-btn" onclick="switchTab('chartTab', this)"><i class="fas fa-sitemap"></i> Sơ đồ Cơ cấu Nhân sự</button>
    <button class="tab-btn" onclick="window.print()" style="margin-left:auto; background:var(--primary); color:white; border-radius: 6px;"><i class="fas fa-print"></i> In Sơ đồ</button>
</div>

<!-- TAB 1: DANH SÁCH -->
<div id="listTab" class="tab-content active">
    <div class="panel" style="margin-bottom: 24px; background: linear-gradient(135deg, rgba(16,185,129,0.05) 0%, rgba(5,150,105,0.02) 100%);">
        <div class="panel-body">
            <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                <div>
                    <h2 style="margin-bottom: 8px; color:var(--primary-light);"><?= h($project['project_name']) ?> (<?= h($project['project_code']) ?>)</h2>
                    <p style="color:var(--text-muted);"><i class="fas fa-building"></i> Khách hàng: <?= h($project['client_name']) ?></p>
                    <p style="color:var(--text-muted);"><i class="fas fa-map-marker-alt"></i> Địa điểm: <?= h($project['location']) ?></p>
                    <p style="color:var(--text-muted);"><i class="fas fa-money-check-alt"></i> Cost Center: <strong><?= h($project['cost_center_code']) ?></strong></p>
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
        <div class="panel-header">
            <h3><i class="fas fa-users"></i> Danh sách Nhân sự Hiện hành</h3>
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
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($personnel as $emp): ?>
                            <tr>
                                <td><strong><?= h($emp['emp_code'] ?? '') ?></strong></td>
                                <td><?= h($emp['full_name']) ?></td>
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
                                    <?php if ($emp['status'] === 'active'): ?>
                                        <span class="badge badge-active">Đang làm việc</span>
                                    <?php elseif ($emp['status'] === 'probation'): ?>
                                        <span class="badge badge-probation">Thử việc</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary"><?= h($emp['status']) ?></span>
                                    <?php endif; ?>
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
.org-chart-level { display: flex; gap: 24px; justify-content: center; flex-wrap: nowrap; margin-bottom: 20px; }
.org-level-root { margin-bottom: 40px; }
.org-chart-node { display: flex; flex-direction: column; align-items: center; position: relative; }

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
    content: ''; position: absolute; top: -30px; left: 50%; width: 2px; height: 30px; background: #4b5563;
}
.org-chart-node:not(:first-child):not(:last-child)::after {
    content: ''; position: absolute; top: -30px; left: 0; right: 0; height: 2px; background: #4b5563;
}
.org-chart-node:first-child:not(:only-child)::after {
    content: ''; position: absolute; top: -30px; left: 50%; right: 0; height: 2px; background: #4b5563;
}
.org-chart-node:last-child:not(:only-child)::after {
    content: ''; position: absolute; top: -30px; left: 0; right: 50%; height: 2px; background: #4b5563;
}

/* ── Print Styles ── */
.print-only { display: none; }
@media print {
    body { background: white !important; color: black !important; }
    .no-print { display: none !important; }
    .print-only { display: block !important; }
    .sidebar, .topbar { display: none !important; }
    .main-content { margin: 0 !important; padding: 0 !important; width: 100% !important; }
    
    .org-chart-wrapper { padding: 0 !important; overflow: visible !important; }
    .org-chart-card { 
        background: white !important; 
        border: 1px solid #ccc !important;
        box-shadow: none !important;
        color: black !important;
        break-inside: avoid;
    }
    .org-chart-card::before { display: none; }
    .org-chart-name { color: black !important; }
    .org-chart-code { background: #f0f0f0 !important; color: #333 !important; }
    .org-chart-pos, .org-chart-phone { color: #555 !important; }
    .org-chart-connector, .org-chart-node::before, .org-chart-node::after { background: #999 !important; }
    
    @page { size: landscape; margin: 1cm; }
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
