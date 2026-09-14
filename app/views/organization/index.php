<!-- Sơ đồ Tổ chức -->
<div class="panel" style="margin-bottom: 24px;">
    <div class="panel-header">
        <h3><i class="fas fa-sitemap"></i> Sơ đồ Tổ chức – Công ty Po Sung</h3>
    </div>
    <div class="panel-body">
        <?php if (empty($tree)): ?>
            <div class="empty-state"><i class="fas fa-sitemap"></i><p>Chưa có dữ liệu phòng ban.</p></div>
        <?php else: ?>
            <div class="org-tree">
                <?php renderTree($tree); ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Bảng thống kê quân số -->
<div class="panel">
    <div class="panel-header">
        <h3><i class="fas fa-users"></i> Thống kê quân số theo Phòng ban</h3>
    </div>
    <div class="panel-body">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Mã PB</th>
                        <th>Tên Phòng ban</th>
                        <th>Chi nhánh</th>
                        <th style="text-align:center;">Quân số</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $totalHC = 0;
                    foreach ($deptStats as $id => $stat): 
                        $totalHC += $stat['headcount'];
                    ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($stat['dept_code']) ?></strong></td>
                        <td><?= htmlspecialchars($stat['dept_name']) ?></td>
                        <td><?= htmlspecialchars($stat['branch'] ?: '—') ?></td>
                        <td style="text-align:center;">
                            <?php if ($stat['headcount'] > 0): ?>
                                <span class="badge badge-active"><?= $stat['headcount'] ?></span>
                            <?php else: ?>
                                <span style="color:var(--text-muted);">0</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr style="background:rgba(99,102,241,0.1); font-weight:700;">
                        <td colspan="3">TỔNG CỘNG</td>
                        <td style="text-align:center;"><?= $totalHC ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<?php
/**
 * Helper: Render cây tổ chức đệ quy
 */
function renderTree(array $nodes, int $level = 0): void
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
        echo '<strong>' . htmlspecialchars($node->dept_name) . '</strong>';
        echo ' <small style="color:var(--text-muted);">(' . htmlspecialchars($node->dept_code) . ')</small>';
        if (!empty($node->branch)) {
            echo ' <span class="badge" style="font-size:0.65rem; padding:2px 8px; background:rgba(99,102,241,0.15); color:var(--primary-light);">' . htmlspecialchars($node->branch) . '</span>';
        }
        echo '</div>';
        if ($hasChildren) {
            renderTree($node->children, $level + 1);
        }
        echo '</li>';
    }
    echo '</ul>';
}
?>

<style>
.org-tree { padding: 16px 0; }
.tree-list { list-style: none; padding: 0; margin: 8px 0; }
.tree-item { margin: 4px 0; }
.tree-node {
    padding: 10px 16px;
    border-radius: 8px;
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.06);
    display: inline-flex;
    align-items: center;
    gap: 4px;
    transition: background 0.2s;
}
.tree-node:hover { background: rgba(99,102,241,0.08); }
.tree-toggle { cursor: pointer; color: var(--primary-light); margin-right: 6px; }
</style>
