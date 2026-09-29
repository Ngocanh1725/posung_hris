<!-- ══════════════════════════════════════════════════════════
     POSUNG HRIS – CƠ CẤU PHÒNG BAN & DỰ ÁN (V3)
     ══════════════════════════════════════════════════════════ -->

<div class="organization-index-page">
    <!-- Breadcrumb & Action Toolbar -->
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div class="breadcrumb-bar m-0">
            <a href="<?= BASE_URL ?>/organization/overview"><i class="fas fa-building"></i> Tổng quan</a>
            <i class="fas fa-chevron-right"></i>
            <span class="text-primary fw-bold"><i class="fas fa-sitemap"></i> Danh sách Phòng ban & Cơ cấu</span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <!-- Nút Toggle Xem Sơ Đồ Cây (Org Chart) Nổi Bật -->
            <a href="<?= BASE_URL ?>/organization/chart" class="btn btn-sm btn-primary shadow-sm" style="background: linear-gradient(135deg, #4f46e5, #3730a3); border: none;">
                <i class="fas fa-project-diagram me-1"></i> <strong>Xem Sơ Đồ Cây (Org Chart)</strong>
            </a>
            <a href="<?= BASE_URL ?>/organization/statistics" class="btn btn-sm btn-outline-info">
                <i class="fas fa-chart-pie me-1"></i> Thống kê Nhân sự
            </a>
            <a href="<?= BASE_URL ?>/category/departments" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-cog me-1"></i> Quản lý Phòng ban
            </a>
        </div>
    </div>

    <!-- 1. Card Overview Thống Kê Nhanh -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-sm-6">
            <div class="card h-100 border-0 shadow-sm" style="border-radius: 12px; border-left: 4px solid #4f46e5 !important; background: var(--bg-card);">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small text-uppercase fw-bold">Tổng Phòng Ban / Đơn Vị</div>
                            <h3 class="fw-bold my-1 text-primary"><?= $statistics['total_depts'] ?? count($allDepts) ?></h3>
                            <small class="text-muted">Gồm Ban GĐ, Khối VP & Tổ đội</small>
                        </div>
                        <div class="stat-icon-box bg-primary bg-opacity-10 text-primary">
                            <i class="fas fa-building fa-lg"></i>
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
                            <div class="text-muted small text-uppercase fw-bold">Tổng Quân Số Toàn Công Ty</div>
                            <h3 class="fw-bold my-1 text-success"><?= number_format($statistics['total_employees'] ?? 0) ?></h3>
                            <small class="text-muted">Trung bình: <?= $statistics['avg_headcount'] ?? 0 ?> NV / đơn vị</small>
                        </div>
                        <div class="stat-icon-box bg-success bg-opacity-10 text-success">
                            <i class="fas fa-users fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="card h-100 border-0 shadow-sm" style="border-radius: 12px; border-left: 4px solid #f59e0b !important; background: var(--bg-card);">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small text-uppercase fw-bold">Phòng Ban Lớn Nhất</div>
                            <h5 class="fw-bold my-1 text-truncate" style="max-width: 170px;" title="<?= htmlspecialchars($statistics['largest_dept']['name'] ?? '---') ?>">
                                <?= htmlspecialchars($statistics['largest_dept']['name'] ?? '---') ?>
                            </h5>
                            <small class="text-warning fw-bold"><i class="fas fa-crown"></i> <?= $statistics['largest_dept']['headcount'] ?? 0 ?> nhân sự</small>
                        </div>
                        <div class="stat-icon-box bg-warning bg-opacity-10 text-warning">
                            <i class="fas fa-trophy fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="card h-100 border-0 shadow-sm" style="border-radius: 12px; border-left: 4px solid #3b82f6 !important; background: var(--bg-card);">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small text-uppercase fw-bold">Khối VP vs Hiện Trường</div>
                            <?php 
                            $vpCount = $statistics['type_stats']['office']['employees'] ?? 0;
                            $ctCount = ($statistics['type_stats']['factory']['employees'] ?? 0) + ($statistics['type_stats']['site_pmb']['employees'] ?? 0);
                            ?>
                            <h4 class="fw-bold my-1">
                                <span class="text-primary"><?= $vpCount ?></span> <small class="text-muted small">VP</small> 
                                <span class="text-muted">/</span> 
                                <span class="text-success"><?= $ctCount ?></span> <small class="text-muted small">CT</small>
                            </h4>
                            <small class="text-muted">Văn phòng / Công trường & Xưởng</small>
                        </div>
                        <div class="stat-icon-box bg-info bg-opacity-10 text-info">
                            <i class="fas fa-city fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Tabs Switcher: Phòng Ban Hành Chính vs Ban QLDA Công Trường -->
    <div class="org-tabs mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="d-flex gap-2">
            <button class="tab-btn active" onclick="switchTab('officeTab', this)">
                <i class="fas fa-building me-1"></i> Cơ Cấu Phòng Ban Hành Chính (<?= count($allDepts) ?>)
            </button>
            <button class="tab-btn" onclick="switchTab('pmbTab', this)">
                <i class="fas fa-hard-hat me-1"></i> Ma Trận Ban QLDA Công Trường (<?= count($projects) ?>)
            </button>
        </div>

        <!-- Chuyển đổi nhanh chế độ xem trong Tab Phòng Ban -->
        <div class="view-toggle-group btn-group" id="deptViewToggle">
            <button type="button" class="btn btn-sm btn-outline-primary active" id="btnViewTable" onclick="toggleDeptView('table')" title="Xem dạng Bảng chi tiết">
                <i class="fas fa-table me-1"></i> Bảng Chi Tiết
            </button>
            <a href="<?= BASE_URL ?>/organization/chart" class="btn btn-sm btn-outline-primary" title="Chuyển sang Sơ đồ Cây Phân Cấp">
                <i class="fas fa-sitemap me-1"></i> Sơ Đồ Cây Phân Cấp
            </a>
            <button type="button" class="btn btn-sm btn-outline-primary" id="btnViewTreeList" onclick="toggleDeptView('treeList')" title="Xem dạng Cây danh sách thu gọn">
                <i class="fas fa-folder-tree me-1"></i> Cây Thu Gọn
            </button>
        </div>
    </div>

    <!-- TAB 1: CƠ CẤU PHÒNG BAN -->
    <div id="officeTab" class="tab-content active">
        <!-- Chế độ 1: Bảng Danh Sách Phòng Ban Chi Tiết -->
        <div id="tableView" class="card border-0 shadow-sm" style="border-radius: 12px; background: var(--bg-card);">
            <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h6 class="fw-bold mb-0 text-primary">
                    <i class="fas fa-list-check me-2"></i>Danh Sách Toàn Bộ Phòng Ban & Đơn Vị POSUNG
                </h6>
                <div class="d-flex align-items-center gap-2">
                    <input type="text" id="deptTableSearch" class="form-control form-control-sm" placeholder="Tìm kiếm phòng ban, trưởng phòng..." style="width: 250px;" oninput="filterDeptTable(this.value)">
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="deptTable" style="font-size: 13.5px;">
                        <thead style="background: var(--bg-hover, #f8fafc); font-size: 11px; text-transform: uppercase; color: var(--text-muted);">
                            <tr>
                                <th style="width: 50px; text-align: center;">STT</th>
                                <th style="width: 110px;">Mã Đơn Vị</th>
                                <th>Tên Phòng Ban / Bộ Phận</th>
                                <th>Cấp Trực Thuộc</th>
                                <th>Trưởng Phòng / Người Phụ Trách</th>
                                <th style="text-align: center;">Khối Hình</th>
                                <th style="text-align: right; width: 130px;">Số Nhân Viên</th>
                                <th style="text-align: center; width: 140px;">Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $idx = 1;
                            $maxCount = 1;
                            foreach ($allDepts as $d) {
                                if ((int)($d['employee_count'] ?? 0) > $maxCount) {
                                    $maxCount = (int)$d['employee_count'];
                                }
                            }

                            foreach ($allDepts as $dept): 
                                $empCount = (int)($dept['employee_count'] ?? 0);
                                $hasAvatar = !empty($dept['manager_avatar']) && file_exists(ROOT_PATH . '/public/' . $dept['manager_avatar']);
                                $avatarUrl = $hasAvatar ? BASE_URL . '/' . $dept['manager_avatar'] : '';
                                $pct = round(($empCount / $maxCount) * 100);
                            ?>
                            <tr>
                                <td style="text-align: center; font-weight: 600; color: var(--text-muted);"><?= $idx++ ?></td>
                                <td>
                                    <span class="badge bg-light text-primary border fw-bold" style="font-size: 11.5px;"><?= htmlspecialchars($dept['dept_code']) ?></span>
                                </td>
                                <td>
                                    <div>
                                        <strong style="color: var(--text); font-size: 14px;"><?= htmlspecialchars($dept['dept_name']) ?></strong>
                                    </div>
                                    <?php if (!empty($dept['office_location'])): ?>
                                        <small class="text-muted"><i class="fas fa-map-marker-alt text-danger me-1"></i><?= htmlspecialchars($dept['office_location']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($dept['parent_name'])): ?>
                                        <span class="text-muted"><i class="fas fa-level-up-alt fa-rotate-90 me-1 text-primary"></i><?= htmlspecialchars($dept['parent_name']) ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary">Cấp Gốc (Root)</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($dept['manager_name'])): ?>
                                        <div class="d-flex align-items-center gap-2">
                                            <?php if ($hasAvatar): ?>
                                                <img src="<?= $avatarUrl ?>" class="rounded-circle shadow-sm" style="width: 32px; height: 32px; object-fit: cover;" alt="">
                                            <?php else: ?>
                                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 12px;">
                                                    <?= mb_substr(trim($dept['manager_name']), 0, 1, 'UTF-8') ?>
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <div class="fw-bold" style="line-height: 1.2; color: var(--text);"><?= htmlspecialchars($dept['manager_name']) ?></div>
                                                <small class="text-muted" style="font-size: 11px;"><?= htmlspecialchars($dept['manager_position'] ?? 'Trưởng đơn vị') ?></small>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted fst-italic">Chưa bổ nhiệm</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: center;">
                                    <?php
                                    $typeBadge = [
                                        'bod'      => ['Ban Giám Đốc', 'bg-indigo text-white', '#4338ca'],
                                        'office'   => ['Văn Phòng', 'bg-primary text-white', '#3b82f6'],
                                        'factory'  => ['Sản Xuất', 'bg-warning text-dark', '#f59e0b'],
                                        'site_pmb' => ['Công Trường', 'bg-success text-white', '#10b981'],
                                    ][$dept['type'] ?? 'office'] ?? ['Khác', 'bg-secondary text-white', '#64748b'];
                                    ?>
                                    <span class="badge" style="background: <?= $typeBadge[2] ?>; font-size: 11px; padding: 4px 8px;">
                                        <?= $typeBadge[0] ?>
                                    </span>
                                </td>
                                <td style="text-align: right;">
                                    <div class="d-flex align-items-center justify-content-end gap-2">
                                        <span class="fw-bold fs-6 <?= $empCount > 0 ? 'text-primary' : 'text-muted' ?>">
                                            <?= number_format($empCount) ?>
                                        </span>
                                        <small class="text-muted">NV</small>
                                    </div>
                                    <div class="progress ms-auto mt-1" style="height: 4px; width: 60px; background: #e2e8f0;">
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $pct ?>%;"></div>
                                    </div>
                                </td>
                                <td style="text-align: center;">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= BASE_URL ?>/organization/detail/<?= $dept['id'] ?>" class="btn btn-outline-primary" title="Xem hồ sơ bộ phận & danh sách nhân sự">
                                            <i class="fas fa-eye"></i> Chi tiết
                                        </a>
                                        <a href="<?= BASE_URL ?>/organization/chart" class="btn btn-outline-secondary" title="Định vị trên Sơ đồ Cây">
                                            <i class="fas fa-sitemap"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Chế độ 2: Cây Danh Sách Thu Gọn (Tree List) -->
        <div id="treeListView" class="card border-0 shadow-sm" style="border-radius: 12px; background: var(--bg-card); display: none;">
            <div class="card-header bg-transparent border-bottom py-3">
                <h6 class="fw-bold mb-0 text-primary"><i class="fas fa-folder-tree me-2"></i>Cơ Cấu Cây Phân Cấp Dạng Thư Mục</h6>
            </div>
            <div class="card-body p-4">
                <div class="org-tree-hierarchical">
                    <?php renderTreeListHierarchical($tree); ?>
                </div>
            </div>
        </div>
    </div>

    <!-- TAB 2: MA TRẬN BQL DỰ ÁN CÔNG TRƯỜNG -->
    <div id="pmbTab" class="tab-content" style="display: none;">
        <div class="dept-cards-grid">
            <?php if(empty($projects)): ?>
                <div class="text-center py-5 text-muted col-12">
                    <i class="fas fa-hard-hat fa-3x mb-3 text-secondary" style="opacity: 0.4;"></i>
                    <h5>Chưa có dữ liệu dự án xây dựng nào.</h5>
                </div>
            <?php else: ?>
                <?php foreach ($projects as $prj): 
                    $stats = $projectStats[$prj['id']] ?? ['quota'=>0, 'actual'=>0, 'incoming'=>0, 'missing'=>0];
                    $totalReal = $stats['actual'] + $stats['incoming'];
                    
                    // Cảnh báo định biên
                    $badgeClass = 'badge-success';
                    $badgeText = 'Đủ định biên';
                    $statusColor = '#10b981';
                    
                    if ($stats['quota'] == 0) {
                        $badgeClass = 'badge-secondary';
                        $badgeText = 'Chưa chốt định biên';
                        $statusColor = '#6b7280';
                    } elseif ($totalReal < $stats['quota']) {
                        $missingPct = ($stats['missing'] / $stats['quota']) * 100;
                        if ($missingPct > 15) {
                            $badgeClass = 'badge-danger';
                            $badgeText = 'Thiếu hụt ' . $stats['missing'] . ' người (>' . round($missingPct) . '%)';
                            $statusColor = '#ef4444';
                        } else {
                            $badgeClass = 'badge-warning';
                            $badgeText = 'Thiếu ' . $stats['missing'] . ' người';
                            $statusColor = '#f59e0b';
                        }
                    } elseif ($totalReal > $stats['quota']) {
                        $badgeClass = 'badge-danger';
                        $badgeText = 'Vượt định biên';
                        $statusColor = '#ef4444';
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
                        <h4 class="dept-card-title"><?= htmlspecialchars($prj['project_name']) ?></h4>
                        <span class="dept-card-code"><?= htmlspecialchars($prj['project_code']) ?></span>
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
</div>

<?php
/**
 * Helper: Render cây thư mục phân cấp
 */
function renderTreeListHierarchical(array $nodes, int $level = 0): void
{
    echo '<ul class="tree-list-h" style="padding-left:' . ($level > 0 ? '28px' : '0') . '; list-style:none;">';
    foreach ($nodes as $node) {
        $hasChildren = !empty($node->children);
        $empCount = (int)($node->employee_count ?? 0);
        $totalCount = (int)($node->total_headcount ?? $empCount);
        ?>
        <li class="tree-item-h mb-2">
            <div class="tree-node-h p-2 rounded d-flex align-items-center gap-2" style="background: var(--bg-hover); border: 1px solid var(--border);">
                <?php if ($hasChildren): ?>
                    <i class="fas fa-folder-open text-warning"></i>
                <?php else: ?>
                    <i class="fas fa-folder text-primary opacity-75"></i>
                <?php endif; ?>

                <a href="<?= BASE_URL ?>/organization/detail/<?= $node->id ?>" class="text-decoration-none fw-bold" style="color: var(--text);">
                    <?= htmlspecialchars($node->dept_name) ?>
                </a>
                <span class="badge bg-light text-primary border" style="font-size: 11px;"><?= htmlspecialchars($node->dept_code) ?></span>

                <?php if (!empty($node->manager_name)): ?>
                    <span class="text-muted small ms-2"><i class="fas fa-user-tie text-secondary me-1"></i><?= htmlspecialchars($node->manager_name) ?></span>
                <?php endif; ?>

                <span class="badge bg-primary bg-opacity-10 text-primary ms-auto" style="font-size: 11px;">
                    <?= $empCount ?> NV <?php if ($totalCount > $empCount): ?>(Tổng: <?= $totalCount ?>)<?php endif; ?>
                </span>
            </div>
            <?php if ($hasChildren): ?>
                <?php renderTreeListHierarchical($node->children, $level + 1); ?>
            <?php endif; ?>
        </li>
        <?php
    }
    echo '</ul>';
}
?>

<style>
.stat-icon-box {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Tabs */
.org-tabs {
    border-bottom: 2px solid var(--border);
    padding-bottom: 8px;
}
.tab-btn {
    background: transparent;
    border: none;
    color: var(--text-muted);
    font-size: 0.95rem;
    padding: 8px 18px;
    cursor: pointer;
    transition: 0.25s;
    font-weight: 700;
    border-radius: 8px;
}
.tab-btn:hover {
    background: var(--bg-hover);
    color: var(--text);
}
.tab-btn.active {
    background: rgba(79, 70, 229, 0.12);
    color: #4f46e5;
}

/* Dept Cards Grid */
.dept-cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
}
.dept-card {
    display: block;
    text-decoration: none;
    color: inherit;
    padding: 20px;
    border-radius: 12px;
    background: var(--bg-card);
    border: 1px solid var(--border);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.03);
}
.dept-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    border-radius: 4px 0 0 4px;
    background: var(--card-color, #4f46e5);
    transition: width 0.3s ease;
}
.dept-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.08);
}
.dept-card:hover::before {
    width: 6px;
}
.dept-card-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 12px;
}
.dept-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
}
.dept-card-title {
    font-size: 1rem;
    font-weight: 700;
    margin-bottom: 4px;
    color: var(--text);
}
.dept-card-code {
    font-size: 0.8rem;
    color: var(--text-muted);
}
.dept-card-footer {
    display: flex;
    justify-content: space-between;
    margin-top: 16px;
    padding-top: 12px;
    border-top: 1px solid var(--border);
    font-size: 0.85rem;
}
.dept-stat {
    display: flex;
    align-items: center;
    gap: 6px;
    color: var(--text-muted);
}
.dept-type-badge {
    font-size: 0.72rem;
    padding: 3px 8px;
    border-radius: 6px;
    font-weight: 600;
}
.badge-success { background: #d1fae5; color: #065f46; }
.badge-warning { background: #fef3c7; color: #92400e; }
.badge-danger { background: #fee2e2; color: #991b1b; }
.badge-secondary { background: #f1f5f9; color: #475569; }
</style>

<script>
// Switch between Office Tab and PMB Tab
function switchTab(tabId, btn) {
    document.querySelectorAll('.tab-content').forEach(el => el.style.display = 'none');
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    
    document.getElementById(tabId).style.display = 'block';
    btn.classList.add('active');

    // Toggle secondary controls visibility
    const viewToggle = document.getElementById('deptViewToggle');
    if (viewToggle) {
        viewToggle.style.display = tabId === 'officeTab' ? 'inline-flex' : 'none';
    }
}

// Toggle between Table View and Tree List View
function toggleDeptView(mode) {
    const tableView = document.getElementById('tableView');
    const treeListView = document.getElementById('treeListView');
    const btnTable = document.getElementById('btnViewTable');
    const btnTree = document.getElementById('btnViewTreeList');

    if (mode === 'table') {
        tableView.style.display = 'block';
        treeListView.style.display = 'none';
        btnTable.classList.add('active');
        btnTree.classList.remove('active');
    } else {
        tableView.style.display = 'none';
        treeListView.style.display = 'block';
        btnTable.classList.remove('active');
        btnTree.classList.add('active');
    }
}

// Search Filter in Department Table
function filterDeptTable(query) {
    query = query.toLowerCase().trim();
    const rows = document.querySelectorAll('#deptTable tbody tr');
    rows.forEach(r => {
        const text = r.innerText.toLowerCase();
        r.style.display = text.includes(query) ? '' : 'none';
    });
}
</script>
