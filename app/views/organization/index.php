<!-- ══════════════════════════════════════════════════════════
     SƠ ĐỒ TỔ CHỨC & PMB (V2)
     ══════════════════════════════════════════════════════════ -->

<div class="breadcrumb-bar">
    <a href="<?= BASE_URL ?>/organization/overview"><i class="fas fa-building"></i> Tổng quan</a>
    <i class="fas fa-chevron-right"></i>
    <span>Sơ đồ Tổ chức & PMB</span>
</div>

<!-- Tabs Control -->
<div class="org-tabs">
    <button class="tab-btn active" onclick="switchTab('officeTab', this)">
        <i class="fas fa-building"></i> Cơ Cấu Phòng Ban Hành Chính
    </button>
    <button class="tab-btn" onclick="switchTab('pmbTab', this)">
        <i class="fas fa-hard-hat"></i> Ma Trận Ban Quản Lý Dự Án
    </button>
</div>

<!-- TAB 1: CƠ CẤU PHÒNG BAN -->
<div id="officeTab" class="tab-content active">
    <div class="panel org-chart-panel" style="margin-bottom: 24px;">
        <div class="panel-header">
            <h3><i class="fas fa-sitemap"></i> Sơ đồ Khối Văn phòng</h3>
            <div class="panel-actions">
                <button class="btn btn-sm btn-ghost active" onclick="toggleView('chart')" id="btnChart" title="Xem dạng Sơ đồ">
                    <i class="fas fa-project-diagram"></i>
                </button>
                <button class="btn btn-sm btn-ghost" onclick="toggleView('list')" id="btnList" title="Xem dạng Danh sách">
                    <i class="fas fa-list"></i>
                </button>
            </div>
        </div>
        <div class="panel-body">
            <?php if (empty($tree)): ?>
                <div class="empty-state"><i class="fas fa-sitemap"></i><p>Chưa có dữ liệu phòng ban.</p></div>
            <?php else: ?>
                <div id="chartView" class="org-chart-wrapper">
                    <?php renderOrgChart($tree, 0); ?>
                </div>
                <div id="listView" class="org-tree" style="display:none;">
                    <?php renderTreeList($tree); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- TAB 2: MA TRẬN BQL DỰ ÁN -->
<div id="pmbTab" class="tab-content" style="display: none;">
    <div class="dept-cards-grid">
        <?php if(empty($projects)): ?>
            <div class="empty-state"><i class="fas fa-hard-hat"></i><p>Chưa có dự án nào.</p></div>
        <?php else: ?>
            <?php foreach ($projects as $prj): 
                $stats = $projectStats[$prj['id']] ?? ['quota'=>0, 'actual'=>0, 'incoming'=>0, 'missing'=>0];
                $totalReal = $stats['actual'] + $stats['incoming'];
                
                // Cảnh báo định biên
                $badgeClass = 'badge-success'; // Đủ
                $badgeText = 'Đủ định biên';
                $statusColor = '#10b981';
                
                if ($stats['quota'] == 0) {
                    $badgeClass = 'badge-secondary';
                    $badgeText = 'Chưa chốt định biên';
                    $statusColor = '#6b7280';
                } elseif ($totalReal < $stats['quota']) {
                    $badgeClass = 'badge-danger';
                    $badgeText = 'Thiếu ' . $stats['missing'] . ' người';
                    $statusColor = '#ef4444';
                } elseif ($totalReal > $stats['quota']) {
                    $badgeClass = 'badge-warning';
                    $badgeText = 'Dư thừa nhân sự';
                    $statusColor = '#f59e0b';
                }
            ?>
            <a href="<?= BASE_URL ?>/project/detail/<?= $prj['id'] ?>" class="dept-card dept-card-project" style="--card-color: <?= $statusColor ?>;">
                <div class="dept-card-header">
                    <div class="dept-icon dept-icon-project" style="background: <?= $statusColor ?>20; color: <?= $statusColor ?>;">
                        <i class="fas fa-hard-hat"></i>
                    </div>
                    <span class="dept-type-badge <?= $badgeClass ?>"><?= $badgeText ?></span>
                </div>
                <div class="dept-card-body">
                    <h4 class="dept-card-title"><?= h($prj['name']) ?></h4>
                    <span class="dept-card-code"><?= h($prj['project_code']) ?></span>
                </div>
                <div class="dept-card-footer">
                    <div class="dept-stat">
                        <i class="fas fa-users"></i>
                        <span>Thực tế: <?= $stats['actual'] ?> / <?= $stats['quota'] ?></span>
                    </div>
                    <?php if($stats['incoming'] > 0): ?>
                    <div class="dept-stat" style="color: #3b82f6;" title="Đang điều động đến">
                        <i class="fas fa-truck-moving"></i>
                        <span>+<?= $stats['incoming'] ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php
/**
 * Helper: Render Org Chart dạng card (đệ quy)
 */
function renderOrgChart(array $nodes, int $level = 0): void
{
    $levelClass = $level === 0 ? 'org-level-root' : 'org-level-child';
    echo '<div class="org-chart-level ' . $levelClass . '">';
    foreach ($nodes as $node) {
        $hasChildren = !empty($node->children);
        $typeClass = strtolower($node->type ?? 'office');
        
        echo '<div class="org-chart-node">';
        echo '<a href="' . BASE_URL . '/organization/detail/' . $node->id . '" class="org-chart-card org-chart-card-' . $typeClass . '">';
        
        // Icon + Type
        echo '<div class="org-chart-card-type">';
        echo '<i class="fas fa-building"></i> ';
        echo '<small>' . h(strtoupper($node->type ?? 'Office')) . '</small>';
        echo '</div>';
        
        // Name + Code
        echo '<div class="org-chart-card-name">' . h($node->name) . '</div>';
        echo '<div class="org-chart-card-code">' . h($node->code) . '</div>';
        
        // Manager
        if (!empty($node->manager_name)) {
            echo '<div class="org-chart-card-manager"><i class="fas fa-user-tie"></i> ' . h($node->manager_name) . '</div>';
        }
        
        echo '</a>';
        
        // Connector line
        if ($hasChildren) {
            echo '<div class="org-chart-connector"></div>';
            renderOrgChart($node->children, $level + 1);
        }
        
        echo '</div>';
    }
    echo '</div>';
}

/**
 * Helper: Render cây tổ chức đệ quy (list view)
 */
function renderTreeList(array $nodes, int $level = 0): void
{
    echo '<ul class="tree-list" style="margin-left:' . ($level * 24) . 'px;">';
    foreach ($nodes as $node) {
        $hasChildren = !empty($node->children);
        echo '<li class="tree-item">';
        echo '<div class="tree-node">';
        if ($hasChildren) {
            echo '<i class="fas fa-caret-down tree-toggle"></i> ';
        } else {
            echo '<i class="fas fa-circle" style="font-size:6px; vertical-align:middle; margin-right:8px; color:var(--primary-light);"></i> ';
        }
        
        echo '<i class="fas fa-building" style="color:var(--primary-light); margin-right:6px;"></i>';
        
        echo '<a href="' . BASE_URL . '/organization/detail/' . $node->id . '" class="text-link">';
        echo '<strong>' . h($node->name) . '</strong>';
        echo '</a>';
        echo ' <small style="color:var(--text-muted);">(' . h($node->code) . ')</small>';
        
        if (!empty($node->manager_name)) {
            echo ' <small style="color:var(--text-muted);"><i class="fas fa-user-tie"></i> ' . h($node->manager_name) . '</small>';
        }
        echo '</div>';
        if ($hasChildren) {
            renderTreeList($node->children, $level + 1);
        }
        echo '</li>';
    }
    echo '</ul>';
}
?>

<style>
/* ── Breadcrumb ── */
.breadcrumb-bar { display: flex; align-items: center; gap: 8px; margin-bottom: 20px; font-size: 0.85rem; color: var(--text-muted); }
.breadcrumb-bar a { color: var(--primary-light); text-decoration: none; }
.breadcrumb-bar a:hover { text-decoration: underline; }

/* ── Tabs ── */
.org-tabs { display: flex; gap: 12px; margin-bottom: 24px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 8px; }
.tab-btn { background: transparent; border: none; color: var(--text-muted); font-size: 1rem; padding: 8px 16px; cursor: pointer; transition: 0.3s; font-weight: 600; border-radius: 8px; }
.tab-btn:hover { background: rgba(255,255,255,0.05); color: var(--text-main); }
.tab-btn.active { background: rgba(99,102,241,0.15); color: var(--primary-light); }

/* ── Org Chart Card-Based ── */
.org-chart-wrapper { padding: 24px 0; overflow-x: auto; }
.org-chart-level { display: flex; gap: 16px; justify-content: center; flex-wrap: wrap; margin-bottom: 20px; }
.org-level-root { margin-bottom: 32px; }
.org-chart-node { display: flex; flex-direction: column; align-items: center; }

.org-chart-card {
    display: block; text-decoration: none; color: inherit;
    min-width: 180px; max-width: 220px; padding: 16px; border-radius: 12px;
    background: var(--bg-card); border: 1px solid rgba(255,255,255,0.08);
    box-shadow: 0 2px 12px rgba(0,0,0,0.15); transition: all 0.3s ease; text-align: center;
    position: relative; overflow: hidden;
}
.org-chart-card::before {
    content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
    background: linear-gradient(90deg, var(--primary), var(--primary-light));
    transition: height 0.3s ease;
}
.org-chart-card:hover { transform: translateY(-4px); box-shadow: 0 8px 24px rgba(99,102,241,0.2); border-color: rgba(99,102,241,0.3); }
.org-chart-card:hover::before { height: 4px; }

.org-chart-card-type { font-size: 0.7rem; color: var(--text-muted); margin-bottom: 6px; }
.org-chart-card-name { font-weight: 700; font-size: 0.85rem; line-height: 1.3; margin-bottom: 4px; }
.org-chart-card-code { font-size: 0.7rem; color: var(--primary-light); font-weight: 600; margin-bottom: 8px; }
.org-chart-card-manager { font-size: 0.7rem; color: var(--text-muted); }
.org-chart-connector { width: 2px; height: 20px; background: linear-gradient(180deg, rgba(99,102,241,0.4), rgba(99,102,241,0.1)); margin: 4px auto; }

/* ── Dept Cards Grid ── */
.dept-cards-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; }
.dept-card {
    display: block; text-decoration: none; color: inherit;
    padding: 20px; border-radius: 12px;
    background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06);
    transition: all 0.3s ease; position: relative; overflow: hidden;
}
.dept-card::before {
    content: ''; position: absolute; top: 0; left: 0; width: 4px; height: 100%;
    border-radius: 4px 0 0 4px; background: var(--card-color, var(--primary));
    transition: width 0.3s ease;
}
.dept-card:hover { background: rgba(255,255,255,0.05); transform: translateX(4px); border-color: rgba(255,255,255,0.1); }
.dept-card:hover::before { width: 6px; }
.dept-card-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px; }
.dept-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1rem; }
.dept-type-badge { font-size: 0.7rem; padding: 4px 8px; border-radius: 6px; font-weight: 600; text-transform: uppercase; }
.dept-card-body { margin-bottom: 12px; }
.dept-card-title { font-size: 1.1rem; font-weight: 700; margin-bottom: 4px; line-height: 1.3; }
.dept-card-code { font-size: 0.8rem; color: var(--text-muted); font-weight: 600; }
.dept-card-footer { display: flex; justify-content: space-between; align-items: center; padding-top: 12px; border-top: 1px solid rgba(255,255,255,0.06); }
.dept-stat { display: flex; align-items: center; gap: 6px; font-size: 0.85rem; color: var(--text-muted); font-weight: 600; }

.badge-success { background: rgba(16,185,129,0.15); color: #10b981; }
.badge-danger { background: rgba(239,68,68,0.15); color: #ef4444; }
.badge-warning { background: rgba(245,158,11,0.15); color: #f59e0b; }
.badge-secondary { background: rgba(107,114,128,0.15); color: #9ca3af; }

.org-tree { padding: 16px 0; }
.tree-list { list-style: none; padding: 0; margin: 8px 0; }
.tree-item { margin: 4px 0; }
.tree-node { padding: 10px 16px; border-radius: 8px; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06); display: inline-flex; align-items: center; gap: 4px; }
.panel-actions { display: flex; gap: 4px; }
.panel-actions .btn.active { background: rgba(99,102,241,0.2); color: var(--primary-light); }
.text-link { color: var(--primary-light); text-decoration: none; }
.text-link:hover { text-decoration: underline; }
</style>

<script>
function switchTab(tabId, btn) {
    document.querySelectorAll('.tab-content').forEach(el => el.style.display = 'none');
    document.getElementById(tabId).style.display = 'block';
    
    document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
    btn.classList.add('active');
}

function toggleView(type) {
    const chartView = document.getElementById('chartView');
    const listView = document.getElementById('listView');
    const btnChart = document.getElementById('btnChart');
    const btnList = document.getElementById('btnList');
    
    if (type === 'chart') {
        chartView.style.display = 'block';
        listView.style.display = 'none';
        btnChart.classList.add('active');
        btnList.classList.remove('active');
    } else {
        chartView.style.display = 'none';
        listView.style.display = 'block';
        btnList.classList.add('active');
        btnChart.classList.remove('active');
    }
}
</script>
