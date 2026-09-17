<!-- ══════════════════════════════════════════════════════════
     SƠ ĐỒ TỔ CHỨC – CARD-BASED ORG CHART
     ══════════════════════════════════════════════════════════ -->

<!-- Breadcrumb -->
<div class="breadcrumb-bar">
    <a href="<?= BASE_URL ?>/organization/overview"><i class="fas fa-building"></i> Cơ cấu Tổ chức</a>
    <i class="fas fa-chevron-right"></i>
    <span>Sơ đồ Tổ chức</span>
</div>

<!-- ── PHẦN 1: Sơ đồ Org Chart (Card-based) ── -->
<div class="panel org-chart-panel" style="margin-bottom: 24px;">
    <div class="panel-header">
        <h3><i class="fas fa-sitemap"></i> Sơ đồ Tổ chức – Công ty TNHH Cơ khí Kỹ thuật Xây dựng Po Sung</h3>
        <div class="panel-actions">
            <button class="btn btn-sm btn-ghost" onclick="toggleView('chart')" id="btnChart" title="Xem dạng Sơ đồ">
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
            <!-- Chart View -->
            <div id="chartView" class="org-chart-wrapper">
                <?php renderOrgChart($tree, 0); ?>
            </div>
            <!-- List View (ẩn mặc định) -->
            <div id="listView" class="org-tree" style="display:none;">
                <?php renderTreeList($tree); ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ── PHẦN 2: Phân loại bộ phận ── -->
<div class="org-sections-grid">
    <!-- Khối Văn phòng -->
    <div class="panel org-section-panel">
        <div class="panel-header" style="background: linear-gradient(135deg, rgba(99,102,241,0.15) 0%, rgba(139,92,246,0.1) 100%);">
            <h3><i class="fas fa-building" style="color: var(--primary-light);"></i> Khối Văn phòng</h3>
            <span class="badge badge-primary"><?php
                $officeCount = 0;
                foreach ($deptStats as $s) {
                    if (in_array($s['branch'], ['Hanoi_HQ','HCM_Office','Factory_Spool'])) $officeCount++;
                }
                echo $officeCount;
            ?> bộ phận</span>
        </div>
        <div class="panel-body p-0">
            <div class="dept-cards-grid">
                <?php foreach ($deptStats as $id => $stat):
                    if (!in_array($stat['branch'], ['Hanoi_HQ','HCM_Office','Factory_Spool'])) continue;
                    $typeClass = strtolower($stat['dept_type'] ?? 'department');
                ?>
                <a href="<?= BASE_URL ?>/organization/detail/<?= $id ?>" class="dept-card dept-card-<?= $typeClass ?>">
                    <div class="dept-card-header">
                        <div class="dept-icon">
                            <?php if ($typeClass === 'division'): ?>
                                <i class="fas fa-crown"></i>
                            <?php else: ?>
                                <i class="fas fa-building"></i>
                            <?php endif; ?>
                        </div>
                        <span class="dept-type-badge badge-<?= $typeClass ?>"><?= htmlspecialchars($stat['dept_type'] ?? 'Department') ?></span>
                    </div>
                    <div class="dept-card-body">
                        <h4 class="dept-card-title"><?= htmlspecialchars($stat['dept_name']) ?></h4>
                        <span class="dept-card-code"><?= htmlspecialchars($stat['dept_code']) ?></span>
                        <?php if (!empty($stat['description'])): ?>
                            <p class="dept-card-desc"><?= htmlspecialchars(mb_substr($stat['description'], 0, 80)) ?>…</p>
                        <?php endif; ?>
                    </div>
                    <div class="dept-card-footer">
                        <div class="dept-stat">
                            <i class="fas fa-users"></i>
                            <span><?= $stat['headcount'] ?> người</span>
                        </div>
                        <?php if (!empty($stat['manager_name'])): ?>
                        <div class="dept-stat">
                            <i class="fas fa-user-tie"></i>
                            <span><?= htmlspecialchars($stat['manager_name']) ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Khối Dự án -->
    <div class="panel org-section-panel">
        <div class="panel-header" style="background: linear-gradient(135deg, rgba(16,185,129,0.15) 0%, rgba(5,150,105,0.1) 100%);">
            <h3><i class="fas fa-hard-hat" style="color: #10b981;"></i> Khối Dự án Công trường</h3>
            <span class="badge" style="background: rgba(16,185,129,0.2); color: #10b981;"><?php
                $projCount = 0;
                foreach ($deptStats as $s) {
                    if ($s['branch'] === 'Site_Project') $projCount++;
                }
                echo $projCount;
            ?> dự án</span>
        </div>
        <div class="panel-body p-0">
            <div class="dept-cards-grid">
                <?php foreach ($deptStats as $id => $stat):
                    if ($stat['branch'] !== 'Site_Project') continue;
                ?>
                <a href="<?= BASE_URL ?>/organization/detail/<?= $id ?>" class="dept-card dept-card-project">
                    <div class="dept-card-header">
                        <div class="dept-icon dept-icon-project">
                            <i class="fas fa-hard-hat"></i>
                        </div>
                        <span class="dept-type-badge badge-project">Dự án</span>
                    </div>
                    <div class="dept-card-body">
                        <h4 class="dept-card-title"><?= htmlspecialchars($stat['dept_name']) ?></h4>
                        <span class="dept-card-code"><?= htmlspecialchars($stat['dept_code']) ?></span>
                        <?php if (!empty($stat['description'])): ?>
                            <p class="dept-card-desc"><?= htmlspecialchars(mb_substr($stat['description'], 0, 80)) ?>…</p>
                        <?php endif; ?>
                    </div>
                    <div class="dept-card-footer">
                        <div class="dept-stat">
                            <i class="fas fa-users"></i>
                            <span><?= $stat['headcount'] ?> người</span>
                        </div>
                        <?php if (!empty($stat['manager_name'])): ?>
                        <div class="dept-stat">
                            <i class="fas fa-user-tie"></i>
                            <span><?= htmlspecialchars($stat['manager_name']) ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- ── PHẦN 3: Bảng thống kê quân số nâng cấp ── -->
<div class="panel" style="margin-top: 24px;">
    <div class="panel-header">
        <h3><i class="fas fa-chart-bar"></i> Thống kê Quân số theo Bộ phận</h3>
    </div>
    <div class="panel-body">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Mã PB</th>
                        <th>Tên Bộ phận</th>
                        <th>Loại hình</th>
                        <th>Chi nhánh</th>
                        <th>Trưởng BP</th>
                        <th style="text-align:center; width:80px;">Quân số</th>
                        <th style="width:160px;">Tỷ lệ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $totalHC = 0;
                    foreach ($deptStats as $id => $stat): 
                        $totalHC += $stat['headcount'];
                    endforeach;
                    
                    foreach ($deptStats as $id => $stat): 
                        $pct = $totalHC > 0 ? round(($stat['headcount'] / $totalHC) * 100, 1) : 0;
                        $typeClass = strtolower($stat['dept_type'] ?? 'department');
                    ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($stat['dept_code']) ?></strong></td>
                        <td>
                            <a href="<?= BASE_URL ?>/organization/detail/<?= $id ?>" class="text-link">
                                <?= htmlspecialchars($stat['dept_name']) ?>
                            </a>
                        </td>
                        <td><span class="dept-type-badge badge-<?= $typeClass ?>" style="font-size:0.7rem;"><?= htmlspecialchars($stat['dept_type'] ?? 'Department') ?></span></td>
                        <td><span class="badge bg-secondary" style="font-size:0.65rem;"><?= htmlspecialchars($stat['branch'] ?: '—') ?></span></td>
                        <td><?= htmlspecialchars($stat['manager_name'] ?: '—') ?></td>
                        <td style="text-align:center;">
                            <?php if ($stat['headcount'] > 0): ?>
                                <span class="badge badge-active"><?= $stat['headcount'] ?></span>
                            <?php else: ?>
                                <span style="color:var(--text-muted);">0</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="progress-bar-wrapper">
                                <div class="progress-bar progress-bar-<?= $typeClass ?>" style="width: <?= $pct ?>%;"></div>
                                <span class="progress-label"><?= $pct ?>%</span>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr style="background:rgba(99,102,241,0.1); font-weight:700;">
                        <td colspan="5">TỔNG CỘNG</td>
                        <td style="text-align:center;"><?= $totalHC ?></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
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
        $typeClass = strtolower($node->dept_type ?? 'department');
        
        echo '<div class="org-chart-node">';
        echo '<a href="' . BASE_URL . '/organization/detail/' . $node->id . '" class="org-chart-card org-chart-card-' . $typeClass . '">';
        
        // Icon + Type
        echo '<div class="org-chart-card-type">';
        if ($typeClass === 'division') {
            echo '<i class="fas fa-crown"></i> ';
        } elseif ($typeClass === 'project') {
            echo '<i class="fas fa-hard-hat"></i> ';
        } else {
            echo '<i class="fas fa-building"></i> ';
        }
        echo '<small>' . htmlspecialchars($node->dept_type ?? 'Department') . '</small>';
        echo '</div>';
        
        // Name + Code
        echo '<div class="org-chart-card-name">' . htmlspecialchars($node->dept_name) . '</div>';
        echo '<div class="org-chart-card-code">' . htmlspecialchars($node->dept_code) . '</div>';
        
        // Manager
        if (!empty($node->manager_name)) {
            echo '<div class="org-chart-card-manager"><i class="fas fa-user-tie"></i> ' . htmlspecialchars($node->manager_name) . '</div>';
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
        $typeClass = strtolower($node->dept_type ?? 'department');
        echo '<li class="tree-item">';
        echo '<div class="tree-node">';
        if ($hasChildren) {
            echo '<i class="fas fa-caret-down tree-toggle"></i> ';
        } else {
            echo '<i class="fas fa-circle" style="font-size:6px; vertical-align:middle; margin-right:8px; color:var(--primary-light);"></i> ';
        }
        
        if ($typeClass === 'division') {
            echo '<i class="fas fa-crown" style="color:#f59e0b; margin-right:6px;"></i>';
        } elseif ($typeClass === 'project') {
            echo '<i class="fas fa-hard-hat" style="color:#10b981; margin-right:6px;"></i>';
        } else {
            echo '<i class="fas fa-building" style="color:var(--primary-light); margin-right:6px;"></i>';
        }
        
        echo '<a href="' . BASE_URL . '/organization/detail/' . $node->id . '" class="text-link">';
        echo '<strong>' . htmlspecialchars($node->dept_name) . '</strong>';
        echo '</a>';
        echo ' <small style="color:var(--text-muted);">(' . htmlspecialchars($node->dept_code) . ')</small>';
        echo ' <span class="dept-type-badge badge-' . $typeClass . '" style="font-size:0.6rem; padding:1px 6px;">' . htmlspecialchars($node->dept_type ?? '') . '</span>';
        
        if (!empty($node->manager_name)) {
            echo ' <small style="color:var(--text-muted);"><i class="fas fa-user-tie"></i> ' . htmlspecialchars($node->manager_name) . '</small>';
        }
        if (!empty($node->branch)) {
            echo ' <span class="badge" style="font-size:0.6rem; padding:1px 6px; background:rgba(99,102,241,0.15); color:var(--primary-light);">' . htmlspecialchars($node->branch) . '</span>';
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
.breadcrumb-bar {
    display: flex; align-items: center; gap: 8px;
    margin-bottom: 20px; font-size: 0.85rem; color: var(--text-muted);
}
.breadcrumb-bar a { color: var(--primary-light); text-decoration: none; }
.breadcrumb-bar a:hover { text-decoration: underline; }

/* ── Org Chart Card-Based ── */
.org-chart-wrapper { padding: 24px 0; overflow-x: auto; }
.org-chart-level { display: flex; gap: 16px; justify-content: center; flex-wrap: wrap; margin-bottom: 20px; }
.org-level-root { margin-bottom: 32px; }
.org-chart-node { display: flex; flex-direction: column; align-items: center; }

.org-chart-card {
    display: block; text-decoration: none; color: inherit;
    min-width: 180px; max-width: 220px;
    padding: 16px; border-radius: 12px;
    background: var(--bg-card); border: 1px solid rgba(255,255,255,0.08);
    box-shadow: 0 2px 12px rgba(0,0,0,0.15);
    transition: all 0.3s ease; text-align: center;
    position: relative; overflow: hidden;
}
.org-chart-card::before {
    content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
    background: linear-gradient(90deg, var(--primary), var(--primary-light));
    transition: height 0.3s ease;
}
.org-chart-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(99,102,241,0.2);
    border-color: rgba(99,102,241,0.3);
}
.org-chart-card:hover::before { height: 4px; }

.org-chart-card-division::before { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
.org-chart-card-project::before { background: linear-gradient(90deg, #10b981, #34d399); }

.org-chart-card-type { font-size: 0.7rem; color: var(--text-muted); margin-bottom: 6px; }
.org-chart-card-type i { font-size: 0.75rem; }
.org-chart-card-name { font-weight: 700; font-size: 0.85rem; line-height: 1.3; margin-bottom: 4px; }
.org-chart-card-code { font-size: 0.7rem; color: var(--primary-light); font-weight: 600; margin-bottom: 8px; }
.org-chart-card-manager { font-size: 0.7rem; color: var(--text-muted); }
.org-chart-card-manager i { margin-right: 4px; }

.org-chart-connector {
    width: 2px; height: 20px;
    background: linear-gradient(180deg, rgba(99,102,241,0.4), rgba(99,102,241,0.1));
    margin: 4px auto;
}

/* ── Sections Grid ── */
.org-sections-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
@media (max-width: 1024px) { .org-sections-grid { grid-template-columns: 1fr; } }

.org-section-panel .panel-header { display: flex; justify-content: space-between; align-items: center; }

/* ── Dept Cards Grid ── */
.dept-cards-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 16px; padding: 16px; }

.dept-card {
    display: block; text-decoration: none; color: inherit;
    padding: 20px; border-radius: 12px;
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.06);
    transition: all 0.3s ease;
    position: relative; overflow: hidden;
}
.dept-card::before {
    content: ''; position: absolute; top: 0; left: 0; width: 4px; height: 100%;
    border-radius: 4px 0 0 4px;
    background: linear-gradient(180deg, var(--primary), var(--primary-light));
    transition: width 0.3s ease;
}
.dept-card:hover {
    background: rgba(99,102,241,0.06);
    border-color: rgba(99,102,241,0.2);
    transform: translateX(4px);
}
.dept-card:hover::before { width: 5px; }

.dept-card-division::before { background: linear-gradient(180deg, #f59e0b, #fbbf24); }
.dept-card-project::before { background: linear-gradient(180deg, #10b981, #34d399); }

.dept-card-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px; }
.dept-icon {
    width: 40px; height: 40px; border-radius: 10px;
    background: linear-gradient(135deg, rgba(99,102,241,0.2), rgba(139,92,246,0.15));
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem; color: var(--primary-light);
}
.dept-icon-project { background: linear-gradient(135deg, rgba(16,185,129,0.2), rgba(5,150,105,0.15)); color: #10b981; }

.dept-type-badge {
    font-size: 0.65rem; padding: 2px 8px; border-radius: 6px;
    font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;
}
.badge-division { background: rgba(245,158,11,0.15); color: #f59e0b; }
.badge-department { background: rgba(99,102,241,0.15); color: var(--primary-light); }
.badge-team { background: rgba(59,130,246,0.15); color: #3b82f6; }
.badge-project { background: rgba(16,185,129,0.15); color: #10b981; }

.dept-card-body { margin-bottom: 12px; }
.dept-card-title { font-size: 0.95rem; font-weight: 700; margin-bottom: 4px; line-height: 1.3; }
.dept-card-code { font-size: 0.7rem; color: var(--primary-light); font-weight: 600; }
.dept-card-desc { font-size: 0.75rem; color: var(--text-muted); line-height: 1.4; margin-top: 8px; }

.dept-card-footer { display: flex; justify-content: space-between; align-items: center; padding-top: 12px; border-top: 1px solid rgba(255,255,255,0.06); }
.dept-stat { display: flex; align-items: center; gap: 6px; font-size: 0.75rem; color: var(--text-muted); }
.dept-stat i { font-size: 0.7rem; }

/* ── Progress Bar ── */
.progress-bar-wrapper {
    display: flex; align-items: center; gap: 8px; width: 100%;
}
.progress-bar-wrapper .progress-bar {
    height: 6px; border-radius: 3px;
    background: linear-gradient(90deg, var(--primary), var(--primary-light));
    transition: width 0.6s ease; min-width: 2px;
}
.progress-bar-division { background: linear-gradient(90deg, #f59e0b, #fbbf24) !important; }
.progress-bar-project { background: linear-gradient(90deg, #10b981, #34d399) !important; }
.progress-label { font-size: 0.7rem; color: var(--text-muted); white-space: nowrap; }

/* ── Text Link ── */
.text-link { color: var(--primary-light); text-decoration: none; }
.text-link:hover { text-decoration: underline; }

/* ── Tree List (from old design) ── */
.org-tree { padding: 16px 0; }
.tree-list { list-style: none; padding: 0; margin: 8px 0; }
.tree-item { margin: 4px 0; }
.tree-node {
    padding: 10px 16px; border-radius: 8px;
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.06);
    display: inline-flex; align-items: center; gap: 4px;
    transition: background 0.2s;
}
.tree-node:hover { background: rgba(99,102,241,0.08); }
.tree-toggle { cursor: pointer; color: var(--primary-light); margin-right: 6px; }

/* ── Panel Actions ── */
.panel-actions { display: flex; gap: 4px; }
.panel-actions .btn.active { background: rgba(99,102,241,0.2); color: var(--primary-light); }

/* ── Badge Primary ── */
.badge-primary { background: rgba(99,102,241,0.2); color: var(--primary-light); }
</style>

<script>
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
// Default: chart view active
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('btnChart')?.classList.add('active');
});
</script>
