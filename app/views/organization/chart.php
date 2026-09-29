<!-- ══════════════════════════════════════════════════════════
     POSUNG HRIS – SƠ ĐỒ TỔ CHỨC DẠNG CÂY TRỰC QUAN (ORG CHART)
     ══════════════════════════════════════════════════════════ -->

<div class="org-chart-page">
    <!-- Breadcrumb & Top Bar -->
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div class="breadcrumb-bar m-0">
            <a href="<?= BASE_URL ?>/organization/overview"><i class="fas fa-building"></i> Tổng quan</a>
            <i class="fas fa-chevron-right"></i>
            <a href="<?= BASE_URL ?>/organization"><i class="fas fa-list"></i> Phòng ban</a>
            <i class="fas fa-chevron-right"></i>
            <span class="text-primary fw-bold"><i class="fas fa-sitemap"></i> Sơ đồ Cây Tổ chức</span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="<?= BASE_URL ?>/organization" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-list-ul"></i> Dạng Danh sách
            </a>
            <a href="<?= BASE_URL ?>/organization/statistics" class="btn btn-sm btn-outline-info">
                <i class="fas fa-chart-pie"></i> Thống kê Nhân sự
            </a>
            <a href="<?= BASE_URL ?>/category/departments" class="btn btn-sm btn-primary">
                <i class="fas fa-cog"></i> Quản lý Phòng ban
            </a>
        </div>
    </div>

    <!-- Overview Bar & Interactive Controls -->
    <div class="card mb-3" style="border: 1px solid var(--border); box-shadow: 0 4px 15px rgba(0,0,0,0.03);">
        <div class="card-body p-3">
            <div class="row g-3 align-items-center">
                <!-- Search Box -->
                <div class="col-lg-4 col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0" style="border-color: var(--border);">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" id="chartSearchInput" class="form-control border-start-0" 
                               placeholder="Tìm tên phòng ban, mã PB, trưởng phòng..." 
                               style="border-color: var(--border); font-size: 13.5px;"
                               oninput="filterOrgTree(this.value)">
                        <button class="btn btn-outline-secondary" type="button" onclick="clearSearch()" title="Xóa tìm kiếm">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>

                <!-- Stats summary badges -->
                <div class="col-lg-4 col-md-7 d-flex align-items-center gap-3 justify-content-lg-center">
                    <div class="stat-pill">
                        <i class="fas fa-building text-primary"></i>
                        <span><strong><?= $statistics['total_depts'] ?? 0 ?></strong> Đơn vị</span>
                    </div>
                    <div class="stat-pill">
                        <i class="fas fa-users text-success"></i>
                        <span><strong><?= number_format($statistics['total_employees'] ?? 0) ?></strong> Nhân sự</span>
                    </div>
                    <?php if (!empty($statistics['largest_dept'])): ?>
                    <div class="stat-pill d-none d-xl-flex" title="Đơn vị có quy mô nhân sự lớn nhất">
                        <i class="fas fa-crown text-warning"></i>
                        <span>Lớn nhất: <strong><?= htmlspecialchars($statistics['largest_dept']['name'] ?? '') ?></strong> (<?= $statistics['largest_dept']['headcount'] ?? 0 ?> NV)</span>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Canvas Control Toolbar -->
                <div class="col-lg-4 col-md-12 d-flex justify-content-lg-end justify-content-start align-items-center gap-1">
                    <button class="btn btn-sm btn-light border" id="btnZoomIn" onclick="zoomChart(0.15)" title="Phóng to">
                        <i class="fas fa-search-plus"></i>
                    </button>
                    <button class="btn btn-sm btn-light border" id="btnZoomOut" onclick="zoomChart(-0.15)" title="Thu nhỏ">
                        <i class="fas fa-search-minus"></i>
                    </button>
                    <button class="btn btn-sm btn-light border" id="btnZoomReset" onclick="resetZoom()" title="Về 100%">
                        <span id="zoomLevelText" style="font-size: 11px; font-weight: 700;">100%</span>
                    </button>
                    <div class="vr mx-1" style="height: 20px;"></div>
                    <button class="btn btn-sm btn-light border" id="btnExpandAll" onclick="toggleAllNodes(true)" title="Mở rộng tất cả nhánh">
                        <i class="fas fa-expand-alt"></i> Mở rộng
                    </button>
                    <button class="btn btn-sm btn-light border" id="btnCollapseAll" onclick="toggleAllNodes(false)" title="Thu gọn tất cả nhánh">
                        <i class="fas fa-compress-alt"></i> Thu gọn
                    </button>
                    <button class="btn btn-sm btn-light border" id="btnFullscreen" onclick="toggleFullscreen()" title="Toàn màn hình">
                        <i class="fas fa-expand"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Interactive Org Chart Viewport -->
    <div class="org-viewport-card" id="orgViewportCard">
        <div class="viewport-hints">
            <span class="badge bg-dark bg-opacity-75 text-white">
                <i class="fas fa-mouse text-info"></i> Giữ chuột & kéo (Drag) để di chuyển • Lăn chuột để Zoom • Click nút [+/-] để Đóng/Mở nhánh
            </span>
        </div>

        <div class="org-chart-canvas-container" id="orgCanvasContainer">
            <div class="org-chart-tree" id="orgChartTree">
                <?php if (empty($tree)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-sitemap fa-3x mb-3 text-secondary" style="opacity: 0.4;"></i>
                        <h5>Chưa có dữ liệu phòng ban để hiển thị sơ đồ cây.</h5>
                    </div>
                <?php else: ?>
                    <ul class="tree-root-list">
                        <?php foreach ($tree as $rootNode): ?>
                            <?php renderOrgTreeNode($rootNode); ?>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Department Quick View Modal -->
<div class="modal fade" id="deptModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 14px; border: 1px solid var(--border); box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
            <div class="modal-header border-bottom py-3" style="background: var(--bg-hover);">
                <div class="d-flex align-items-center gap-2">
                    <div class="dept-badge-icon" id="modalTypeIcon"><i class="fas fa-building"></i></div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="modalDeptName">Tên phòng ban</h5>
                        <small class="text-muted" id="modalDeptCode">Mã phòng ban</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="manager-preview-card p-3 mb-3 d-flex align-items-center gap-3">
                    <img id="modalMgrAvatar" src="" class="rounded-circle shadow-sm" style="width: 54px; height: 54px; object-fit: cover; border: 2px solid #fff;" alt="">
                    <div>
                        <small class="text-uppercase text-muted fw-bold" style="font-size: 11px;">Trưởng đơn vị / Quản lý</small>
                        <h6 class="fw-bold mb-0 text-primary" id="modalMgrName">---</h6>
                        <small class="text-muted" id="modalMgrPosition">---</small>
                    </div>
                </div>

                <div class="row g-2 text-center mb-3">
                    <div class="col-4">
                        <div class="p-2 border rounded" style="background: rgba(99,102,241,0.04);">
                            <div class="text-muted small">Quân số trực tiếp</div>
                            <h5 class="fw-bold mb-0 text-primary" id="modalDirectCount">0</h5>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-2 border rounded" style="background: rgba(16,185,129,0.04);">
                            <div class="text-muted small">Tổng cả khối</div>
                            <h5 class="fw-bold mb-0 text-success" id="modalTotalCount">0</h5>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-2 border rounded" style="background: rgba(245,158,11,0.04);">
                            <div class="text-muted small">Cơ cấu Giới tính</div>
                            <div class="fw-bold small mb-0" id="modalGenderRatio">Nam: 0 | Nữ: 0</div>
                        </div>
                    </div>
                </div>

                <div class="dept-info-list" style="font-size: 13.5px;">
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted"><i class="fas fa-map-marker-alt text-danger me-1"></i> Vị trí / Văn phòng:</span>
                        <span class="fw-semibold text-end" id="modalLocation">---</span>
                    </div>
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted"><i class="fas fa-phone text-success me-1"></i> Điện thoại:</span>
                        <span class="fw-semibold text-end" id="modalPhone">---</span>
                    </div>
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted"><i class="fas fa-envelope text-info me-1"></i> Email liên hệ:</span>
                        <span class="fw-semibold text-end" id="modalEmail">---</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top py-2 px-3 d-flex justify-content-between">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <a href="#" id="modalDetailBtn" class="btn btn-sm btn-primary">
                    <i class="fas fa-external-link-alt"></i> Xem Chi Tiết & Danh Sách NV
                </a>
            </div>
        </div>
    </div>
</div>

<?php
/**
 * Render một Node trong Cây Tổ Chức
 */
function renderOrgTreeNode(object $node): void
{
    $hasChildren = !empty($node->children);
    $type = strtolower($node->type ?? 'office');
    
    // Theme colors theo loại hình đơn vị
    $themeMap = [
        'bod'      => ['bg' => 'linear-gradient(135deg, #1e1b4b, #312e81)', 'border' => '#4338ca', 'color' => '#ffffff', 'badge' => 'Ban Giám Đốc', 'icon' => 'fa-crown'],
        'office'   => ['bg' => 'linear-gradient(135deg, #0f172a, #1e293b)', 'border' => '#3b82f6', 'color' => '#ffffff', 'badge' => 'Văn Phòng', 'icon' => 'fa-building'],
        'factory'  => ['bg' => 'linear-gradient(135deg, #1c1917, #292524)', 'border' => '#f59e0b', 'color' => '#ffffff', 'badge' => 'Sản Xuất / Xưởng', 'icon' => 'fa-industry'],
        'site_pmb' => ['bg' => 'linear-gradient(135deg, #064e3b, #047857)', 'border' => '#10b981', 'color' => '#ffffff', 'badge' => 'Ban QLDA Công Trường', 'icon' => 'fa-hard-hat'],
    ];

    $theme = $themeMap[$type] ?? $themeMap['office'];

    // Manager Avatar
    $hasAvatar = !empty($node->manager_avatar) && file_exists(ROOT_PATH . '/public/' . $node->manager_avatar);
    $avatarUrl = $hasAvatar ? BASE_URL . '/' . $node->manager_avatar : '';
    $initials = '';
    if (!empty($node->manager_name)) {
        $parts = explode(' ', trim($node->manager_name));
        $initials = mb_substr(end($parts), 0, 1, 'UTF-8');
    }

    $directCount = (int)($node->employee_count ?? 0);
    $totalCount = (int)($node->total_headcount ?? $directCount);

    $jsonProps = htmlspecialchars(json_encode([
        'id'          => $node->id,
        'name'        => $node->dept_name,
        'code'        => $node->dept_code,
        'type'        => $type,
        'typeName'    => $theme['badge'],
        'typeIcon'    => $theme['icon'],
        'managerName' => $node->manager_name ?: 'Chưa bổ nhiệm',
        'managerPos'  => $node->manager_position ?: 'Trưởng đơn vị',
        'managerAvatar' => $avatarUrl,
        'directCount' => $directCount,
        'totalCount'  => $totalCount,
        'maleCount'   => (int)($node->male_count ?? 0),
        'femaleCount' => (int)($node->female_count ?? 0),
        'location'    => $node->office_location ?: 'Văn phòng trụ sở POSUNG',
        'phone'       => $node->phone ?: '---',
        'email'       => $node->email ?: '---',
    ], JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
    ?>
    <li class="tree-node-item <?= $hasChildren ? 'has-children' : '' ?>" id="node-item-<?= $node->id ?>">
        <!-- Node Card Box -->
        <div class="org-node-card-wrapper">
            <div class="org-node-card org-node-<?= $type ?>" 
                 id="node-card-<?= $node->id ?>"
                 data-dept='<?= $jsonProps ?>'
                 onclick="openDeptModal(this)">
                
                <!-- Card Header: Code & Type Badge -->
                <div class="node-card-header">
                    <span class="dept-code-tag"><i class="fas <?= $theme['icon'] ?> me-1"></i><?= htmlspecialchars($node->dept_code) ?></span>
                    <span class="dept-type-tag"><?= $theme['badge'] ?></span>
                </div>

                <!-- Card Body: Department Name -->
                <div class="node-dept-name" title="<?= htmlspecialchars($node->dept_name) ?>">
                    <?= htmlspecialchars($node->dept_name) ?>
                </div>

                <!-- Card Middle: Manager Info -->
                <div class="node-manager-info">
                    <?php if ($hasAvatar): ?>
                        <img src="<?= $avatarUrl ?>" class="node-mgr-avatar" alt="Avatar">
                    <?php else: ?>
                        <div class="node-mgr-avatar-placeholder <?= ($node->manager_gender ?? '') === 'Female' ? 'female' : 'male' ?>">
                            <?= !empty($initials) ? $initials : '<i class="fas fa-user"></i>' ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="node-mgr-text">
                        <div class="mgr-name"><?= htmlspecialchars($node->manager_name ?: 'Chưa bổ nhiệm') ?></div>
                        <div class="mgr-title"><?= htmlspecialchars($node->manager_position ?: 'Trưởng đơn vị') ?></div>
                    </div>
                </div>

                <!-- Card Footer: Headcount & Details link -->
                <div class="node-card-footer">
                    <div class="node-headcount-badge" title="Quân số trực tiếp: <?= $directCount ?> | Tổng cả khối: <?= $totalCount ?>">
                        <i class="fas fa-users text-primary me-1"></i>
                        <span><?= $directCount ?> NV</span>
                        <?php if ($totalCount > $directCount): ?>
                            <small class="text-muted ms-1">(Tổng: <?= $totalCount ?>)</small>
                        <?php endif; ?>
                    </div>
                    <a href="<?= BASE_URL ?>/organization/detail/<?= $node->id ?>" 
                       class="node-detail-link" 
                       onclick="event.stopPropagation();" 
                       title="Xem hồ sơ bộ phận">
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>

            <!-- Toggle Expand/Collapse Button if has children -->
            <?php if ($hasChildren): ?>
                <button class="node-toggle-btn" 
                        id="toggle-btn-<?= $node->id ?>" 
                        onclick="toggleSubTree(event, <?= $node->id ?>)" 
                        title="Đóng / Mở nhánh con (<?= count($node->children) ?> đơn vị)">
                    <span class="toggle-icon"><i class="fas fa-minus"></i></span>
                    <span class="toggle-count"><?= count($node->children) ?></span>
                </button>
            <?php endif; ?>
        </div>

        <!-- Sub-tree branches -->
        <?php if ($hasChildren): ?>
            <ul class="tree-sub-list" id="sub-tree-<?= $node->id ?>">
                <?php foreach ($node->children as $child): ?>
                    <?php renderOrgTreeNode($child); ?>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </li>
    <?php
}
?>

<style>
/* ── ORG CHART STYLES ── */
.org-chart-page {
    position: relative;
    user-select: none;
}

.stat-pill {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 6px 14px;
    background: var(--bg-hover, #f8fafc);
    border: 1px solid var(--border, #e2e8f0);
    border-radius: 20px;
    font-size: 13px;
    color: var(--text, #1e293b);
}

/* Viewport Card */
.org-viewport-card {
    background: #0f172a;
    background-image: 
        radial-gradient(rgba(255, 255, 255, 0.08) 1px, transparent 1px),
        radial-gradient(rgba(255, 255, 255, 0.04) 1px, transparent 1px);
    background-size: 24px 24px;
    background-position: 0 0, 12px 12px;
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 16px;
    height: 75vh;
    min-height: 600px;
    position: relative;
    overflow: hidden;
    box-shadow: inset 0 2px 10px rgba(0,0,0,0.4);
    cursor: grab;
}

.org-viewport-card:active {
    cursor: grabbing;
}

.viewport-hints {
    position: absolute;
    bottom: 16px;
    left: 16px;
    z-index: 10;
    pointer-events: none;
}

/* Canvas & Tree Layout */
.org-chart-canvas-container {
    width: 100%;
    height: 100%;
    transform-origin: 50% 10%;
    transition: transform 0.15s ease-out;
    display: flex;
    justify-content: center;
    align-items: flex-start;
    padding: 60px 40px;
}

.org-chart-tree {
    display: inline-block;
    white-space: nowrap;
}

.org-chart-tree ul {
    position: relative;
    padding-top: 26px;
    transition: all 0.3s;
    display: flex;
    justify-content: center;
    padding-left: 0;
    margin: 0;
    list-style: none;
}

.org-chart-tree li {
    float: left;
    text-align: center;
    list-style-type: none;
    position: relative;
    padding: 26px 12px 0 12px;
    transition: all 0.3s;
}

/* Connectors lines via CSS pseudo-elements */
.org-chart-tree li::before, 
.org-chart-tree li::after {
    content: '';
    position: absolute;
    top: 0;
    right: 50%;
    border-top: 2px solid #3b82f6;
    width: 50%;
    height: 26px;
    z-index: 1;
}

.org-chart-tree li::after {
    right: auto;
    left: 50%;
    border-left: 2px solid #3b82f6;
}

.org-chart-tree li:only-child::after, 
.org-chart-tree li:only-child::before {
    display: none;
}

.org-chart-tree li:only-child {
    padding-top: 0;
}

.org-chart-tree li:first-child::before, 
.org-chart-tree li:last-child::after {
    border: 0 none;
}

.org-chart-tree li:last-child::before {
    border-right: 2px solid #3b82f6;
    border-radius: 0 8px 0 0;
}

.org-chart-tree li:first-child::after {
    border-radius: 8px 0 0 0;
}

.org-chart-tree ul ul::before {
    content: '';
    position: absolute;
    top: 0;
    left: 50%;
    border-left: 2px solid #3b82f6;
    width: 0;
    height: 26px;
    transform: translateX(-50%);
    z-index: 1;
}

/* Node Wrapper */
.org-node-card-wrapper {
    display: inline-flex;
    flex-direction: column;
    align-items: center;
    position: relative;
    z-index: 2;
}

/* Node Card */
.org-node-card {
    width: 230px;
    background: #1e293b;
    border: 2px solid rgba(255,255,255,0.12);
    border-radius: 14px;
    padding: 14px;
    text-align: left;
    box-shadow: 0 10px 25px rgba(0,0,0,0.3);
    transition: all 0.25s cubic-bezier(0.165, 0.84, 0.44, 1);
    cursor: pointer;
    position: relative;
    backdrop-filter: blur(8px);
}

.org-node-card:hover {
    transform: translateY(-5px) scale(1.02);
    border-color: #60a5fa !important;
    box-shadow: 0 14px 30px rgba(59, 130, 246, 0.35);
}

.org-node-card.highlighted {
    border-color: #f59e0b !important;
    box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.4), 0 14px 30px rgba(0,0,0,0.4);
    animation: pulseHighlight 1.5s infinite;
}

@keyframes pulseHighlight {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

/* Card Header */
.node-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}

.dept-code-tag {
    font-size: 11px;
    font-weight: 700;
    color: #93c5fd;
    background: rgba(59, 130, 246, 0.15);
    padding: 2px 7px;
    border-radius: 6px;
    letter-spacing: 0.5px;
}

.dept-type-tag {
    font-size: 10px;
    font-weight: 600;
    color: #94a3b8;
    background: rgba(255,255,255,0.06);
    padding: 2px 6px;
    border-radius: 4px;
}

/* Card Body */
.node-dept-name {
    font-size: 13.5px;
    font-weight: 700;
    color: #f8fafc;
    line-height: 1.35;
    margin-bottom: 10px;
    white-space: normal;
    min-height: 36px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Card Manager */
.node-manager-info {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 10px;
    background: rgba(0, 0, 0, 0.25);
    border-radius: 8px;
    margin-bottom: 10px;
    border: 1px solid rgba(255,255,255,0.05);
}

.node-mgr-avatar, 
.node-mgr-avatar-placeholder {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    flex-shrink: 0;
    object-fit: cover;
}

.node-mgr-avatar-placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    font-weight: 700;
    color: #fff;
}

.node-mgr-avatar-placeholder.male { background: #3b82f6; }
.node-mgr-avatar-placeholder.female { background: #ec4899; }

.node-mgr-text {
    overflow: hidden;
}

.mgr-name {
    font-size: 12px;
    font-weight: 700;
    color: #e2e8f0;
    white-space: nowrap;
    text-overflow: ellipsis;
    overflow: hidden;
}

.mgr-title {
    font-size: 11px;
    color: #94a3b8;
    white-space: nowrap;
    text-overflow: ellipsis;
    overflow: hidden;
}

/* Card Footer */
.node-card-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 6px;
    border-top: 1px solid rgba(255,255,255,0.08);
}

.node-headcount-badge {
    font-size: 11.5px;
    font-weight: 600;
    color: #cbd5e1;
}

.node-detail-link {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: rgba(255,255,255,0.08);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #93c5fd;
    text-decoration: none;
    font-size: 11px;
    transition: all 0.2s;
}

.node-detail-link:hover {
    background: #3b82f6;
    color: #fff;
    transform: translateX(2px);
}

/* Node Types Themes */
.org-node-bod {
    border-color: #6366f1;
    background: linear-gradient(145deg, #1e1b4b, #2e1065);
}
.org-node-bod .dept-code-tag {
    color: #c7d2fe;
    background: rgba(99, 102, 241, 0.3);
}

.org-node-factory {
    border-color: #d97706;
    background: linear-gradient(145deg, #1c1917, #292524);
}
.org-node-factory .dept-code-tag {
    color: #fde68a;
    background: rgba(217, 119, 6, 0.25);
}

.org-node-site_pmb {
    border-color: #059669;
    background: linear-gradient(145deg, #064e3b, #065f46);
}
.org-node-site_pmb .dept-code-tag {
    color: #a7f3d0;
    background: rgba(5, 150, 105, 0.25);
}

/* Toggle Expand/Collapse Button */
.node-toggle-btn {
    position: absolute;
    bottom: -15px;
    background: #1e293b;
    border: 2px solid #3b82f6;
    border-radius: 12px;
    color: #fff;
    padding: 1px 8px;
    font-size: 11px;
    font-weight: 700;
    cursor: pointer;
    box-shadow: 0 4px 10px rgba(0,0,0,0.3);
    z-index: 5;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.node-toggle-btn:hover {
    background: #3b82f6;
    transform: scale(1.1);
}

.node-toggle-btn.collapsed {
    border-color: #f59e0b;
    background: #78350f;
}

.node-toggle-btn.collapsed .toggle-icon i {
    transform: rotate(90deg);
}

.tree-sub-list.collapsed {
    display: none !important;
}

/* Modal styling */
.manager-preview-card {
    background: var(--bg-hover, #f8fafc);
    border: 1px solid var(--border, #e2e8f0);
    border-radius: 12px;
}
.dept-badge-icon {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    background: rgba(99, 102, 241, 0.15);
    color: #4f46e5;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
}
</style>

<script>
// Interactive Canvas State
let scale = 1.0;
let isPanning = false;
let startX = 0, startY = 0;
let translateX = 0, translateY = 0;

const viewport = document.getElementById('orgViewportCard');
const canvas = document.getElementById('orgCanvasContainer');
const zoomText = document.getElementById('zoomLevelText');

function updateTransform() {
    canvas.style.transform = `translate(${translateX}px, ${translateY}px) scale(${scale})`;
    zoomText.innerText = Math.round(scale * 100) + '%';
}

function zoomChart(delta) {
    scale = Math.min(Math.max(0.35, scale + delta), 2.2);
    updateTransform();
}

function resetZoom() {
    scale = 1.0;
    translateX = 0;
    translateY = 0;
    updateTransform();
}

// Pan & Drag event listeners
viewport.addEventListener('mousedown', (e) => {
    // Only drag when clicking background or canvas, not card or button
    if (e.target.closest('.org-node-card') || e.target.closest('.node-toggle-btn') || e.target.closest('button')) {
        return;
    }
    isPanning = true;
    startX = e.clientX - translateX;
    startY = e.clientY - translateY;
    viewport.style.cursor = 'grabbing';
});

window.addEventListener('mousemove', (e) => {
    if (!isPanning) return;
    translateX = e.clientX - startX;
    translateY = e.clientY - startY;
    updateTransform();
});

window.addEventListener('mouseup', () => {
    isPanning = false;
    viewport.style.cursor = 'grab';
});

// Mouse wheel zoom
viewport.addEventListener('wheel', (e) => {
    e.preventDefault();
    const zoomFactor = e.deltaY < 0 ? 0.08 : -0.08;
    zoomChart(zoomFactor);
}, { passive: false });

// Expand/Collapse single sub-tree
function toggleSubTree(e, nodeId) {
    e.stopPropagation();
    const subTree = document.getElementById('sub-tree-' + nodeId);
    const btn = document.getElementById('toggle-btn-' + nodeId);
    if (!subTree) return;

    if (subTree.classList.contains('collapsed')) {
        subTree.classList.remove('collapsed');
        btn.classList.remove('collapsed');
        btn.querySelector('.toggle-icon i').className = 'fas fa-minus';
    } else {
        subTree.classList.add('collapsed');
        btn.classList.add('collapsed');
        btn.querySelector('.toggle-icon i').className = 'fas fa-plus';
    }
}

// Expand / Collapse All
function toggleAllNodes(expand) {
    const allSubTrees = document.querySelectorAll('.tree-sub-list');
    const allBtns = document.querySelectorAll('.node-toggle-btn');
    
    allSubTrees.forEach(st => {
        if (expand) {
            st.classList.remove('collapsed');
        } else {
            st.classList.add('collapsed');
        }
    });

    allBtns.forEach(btn => {
        const icon = btn.querySelector('.toggle-icon i');
        if (expand) {
            btn.classList.remove('collapsed');
            if (icon) icon.className = 'fas fa-minus';
        } else {
            btn.classList.add('collapsed');
            if (icon) icon.className = 'fas fa-plus';
        }
    });
}

// Search & Filter in Org Chart
function filterOrgTree(query) {
    query = query.trim().toLowerCase();
    const cards = document.querySelectorAll('.org-node-card');
    
    if (!query) {
        cards.forEach(c => c.classList.remove('highlighted'));
        return;
    }

    let firstMatch = null;
    cards.forEach(c => {
        const data = JSON.parse(c.getAttribute('data-dept') || '{}');
        const match = (data.name && data.name.toLowerCase().includes(query)) ||
                      (data.code && data.code.toLowerCase().includes(query)) ||
                      (data.managerName && data.managerName.toLowerCase().includes(query));
        
        if (match) {
            c.classList.add('highlighted');
            // Auto expand parents if collapsed
            let parentUl = c.closest('.tree-sub-list');
            while (parentUl) {
                parentUl.classList.remove('collapsed');
                const btn = parentUl.previousElementSibling?.querySelector('.node-toggle-btn');
                if (btn) {
                    btn.classList.remove('collapsed');
                    btn.querySelector('.toggle-icon i').className = 'fas fa-minus';
                }
                parentUl = parentUl.parentElement.closest('.tree-sub-list');
            }
            if (!firstMatch) firstMatch = c;
        } else {
            c.classList.remove('highlighted');
        }
    });

    // Center to first match if found
    if (firstMatch) {
        firstMatch.scrollIntoView({ behavior: 'smooth', block: 'center', inline: 'center' });
    }
}

function clearSearch() {
    document.getElementById('chartSearchInput').value = '';
    filterOrgTree('');
}

// Fullscreen toggle
function toggleFullscreen() {
    if (!document.fullscreenElement) {
        viewport.requestFullscreen().catch(err => {
            alert(`Lỗi toàn màn hình: ${err.message}`);
        });
    } else {
        document.exitFullscreen();
    }
}

// Department Quick View Modal
function openDeptModal(cardEl) {
    const data = JSON.parse(cardEl.getAttribute('data-dept') || '{}');
    if (!data.id) return;

    document.getElementById('modalDeptName').innerText = data.name;
    document.getElementById('modalDeptCode').innerText = data.code + ' • ' + (data.typeName || '');
    document.getElementById('modalMgrName').innerText = data.managerName;
    document.getElementById('modalMgrPosition').innerText = data.managerPos;
    document.getElementById('modalDirectCount').innerText = data.directCount + ' NV';
    document.getElementById('modalTotalCount').innerText = data.totalCount + ' NV';
    document.getElementById('modalGenderRatio').innerText = `Nam: ${data.maleCount || 0} | Nữ: ${data.femaleCount || 0}`;
    document.getElementById('modalLocation').innerText = data.location;
    document.getElementById('modalPhone').innerText = data.phone;
    document.getElementById('modalEmail').innerText = data.email;
    document.getElementById('modalDetailBtn').href = '<?= BASE_URL ?>/organization/detail/' + data.id;

    const avatarEl = document.getElementById('modalMgrAvatar');
    if (data.managerAvatar) {
        avatarEl.src = data.managerAvatar;
        avatarEl.style.display = 'block';
    } else {
        avatarEl.src = '<?= BASE_URL ?>/public/assets/images/default-avatar.png';
    }

    const modal = new bootstrap.Modal(document.getElementById('deptModal'));
    modal.show();
}
</script>
