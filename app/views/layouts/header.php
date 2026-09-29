<?php
/**
 * ============================================================
 *  POSUNG HRIS – Header Layout
 * ============================================================
 *  Chứa thẻ HTML tĩnh, CSS link, Sidebar điều hướng (phân quyền),
 *  và Topbar.
 * ============================================================
 */
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="POSUNG CONSTRUCTION HRIS – The best global construction company in VIETNAM">
    <meta name="keywords" content="HRIS, POSUNG CONSTRUCTION, POSUNG, PNSG, BOWOO MEC, Quản lý nhân sự, ERP">
    <meta name="author" content="POSUNG CONSTRUCTION">
    
    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#1e293b">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="manifest" href="<?= BASE_URL ?>/manifest.json">
    <link rel="apple-touch-icon" href="https://cdn-icons-png.flaticon.com/512/3281/3281289.png">

    <title><?= h($pageTitle ?? 'Trang quản trị') ?> – POSUNG HRIS</title>

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Font Awesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- CSS của ứng dụng (Minified) -->
    <link href="<?= BASE_URL ?>/css/style.min.css?v=<?= time() ?>" rel="stylesheet">
</head>
<body>

    <!-- ══════════════════════════════════════════════════════
         SIDEBAR ĐIỀU HƯỚNG
         ══════════════════════════════════════════════════════ -->
    <aside class="sidebar" id="sidebar">
        <!-- Logo -->
        <div class="sidebar-header">
            <a href="<?= BASE_URL ?>" class="sidebar-logo">
                <?php if(file_exists(APP_ROOT . '/../public/uploads/logo.png')): ?>
                    <img src="<?= BASE_URL ?>/uploads/logo.png?t=<?= time() ?>" alt="POSUNG Logo" style="max-height: 40px; margin-right: 10px;">
                <?php else: ?>
                    <div class="logo-icon">PS</div>
                <?php endif; ?>
                <div class="logo-text">
                    <span class="logo-title">POSUNG HRIS</span>
                    <span class="logo-subtitle">POSUNG CONSTRUCTION</span>
                </div>
            </a>
            <a href="<?= BASE_URL ?>/setting" class="sidebar-toggle" style="margin-right: 5px; color: var(--text-muted);" title="Cài đặt">
                <i class="fas fa-cog"></i>
            </a>
            <button class="sidebar-toggle" id="sidebarToggle" title="Thu gọn">
                <i class="fas fa-bars-staggered"></i>
            </button>
        </div>

        <!-- Menu Navigation -->
        <div class="sidebar-nav">
            <?php
            // Khởi tạo các Model cần thiết cho Layout (nếu chưa được Autoload)
            require_once APP_ROOT . '/models/Module.php';
            require_once APP_ROOT . '/models/Navigation.php';
            require_once APP_ROOT . '/models/User.php';

            $navModel = new Navigation();
            $menuTree = $navModel->renderMenu(Session::userId());
            $currentUrl = $_GET['_route'] ?? '';

            // ── MENU ĐỘNG TỪ RBAC ──
            foreach ($menuTree as $menu) {
                // Xác định trạng thái active của menu cha
                $isActive = false;
                $menuUrl = trim($menu['url'] ?? '', '/');
                if ($menuUrl !== '' && strpos($currentUrl, $menuUrl) === 0) {
                    $isActive = true;
                }
                
                // Kiểm tra active cho các menu con
                if (!empty($menu['children'])) {
                    foreach ($menu['children'] as $child) {
                        $childUrl = trim($child['url'] ?? '', '/');
                        if ($childUrl !== '' && strpos($currentUrl, $childUrl) === 0) {
                            $isActive = true;
                            break;
                        }
                    }
                }

                echo '<div class="nav-section">';
                
                if (empty($menu['children'])) {
                    // Cấp 1 không có con -> Link trực tiếp
                    $activeClass = $isActive ? 'active' : '';
                    $href = $menuUrl !== '' ? BASE_URL . '/' . $menuUrl : '#';
                    echo '<a href="' . $href . '" class="nav-link ' . $activeClass . '">';
                    echo '<i class="' . h($menu['icon'] ?? 'fas fa-cube') . '"></i><span>' . h($menu['name']) . '</span>';
                    echo '</a>';
                } else {
                    // Cấp 1 có con -> Tạo Section tiêu đề
                    echo '<span class="nav-section-title"><i class="' . h($menu['icon'] ?? 'fas fa-folder') . '"></i> ' . h($menu['name']) . '</span>';
                    // Render các menu con
                    foreach ($menu['children'] as $child) {
                        $childUrl = trim($child['url'] ?? '', '/');
                        $childActive = ($childUrl !== '' && strpos($currentUrl, $childUrl) === 0) ? 'active' : '';
                        $href = $childUrl !== '' ? BASE_URL . '/' . $childUrl : '#';
                        echo '<a href="' . $href . '" class="nav-link ' . $childActive . '">';
                        echo '<i class="' . h($child['icon'] ?? 'fas fa-angle-right') . '" style="font-size: 0.85em; margin-left: 5px;"></i><span>' . h($child['name']) . '</span>';
                        echo '</a>';
                    }
                }
                echo '</div>';
            }
            ?>
            
            <?php
            // ── KHỐI MENU ĐẶC QUYỀN (SUPER ADMIN & SYSTEM ADMIN) ──
            $isSA = Session::isSuperAdmin();
            $hasRbac = $isSA || Session::hasPermission('rbac_matrix.view');
            $hasMenuAdmin = $isSA || Session::hasPermission('system_menu.manage');
            $hasDbAdmin = $isSA || Session::hasPermission('database_mgr.view');

            if ($hasRbac || $hasMenuAdmin || $hasDbAdmin):
            ?>
            <div class="nav-section" style="margin-top: 15px; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 15px;">
                <span class="nav-section-title" style="color: #f59e0b;"><i class="fas fa-crown"></i> QUẢN TRỊ HỆ THỐNG</span>
                
                <?php if ($hasRbac): ?>
                <a href="<?= BASE_URL ?>/rbac" class="nav-link <?= strpos($currentUrl, 'rbac') === 0 ? 'active' : '' ?>">
                    <i class="fas fa-shield-halved"></i><span>Phân Quyền & Vai Trò</span>
                </a>
                <a href="<?= BASE_URL ?>/rbac/matrix" class="nav-link <?= strpos($currentUrl, 'rbac/matrix') === 0 ? 'active' : '' ?>">
                    <i class="fas fa-table-cells" style="font-size: 0.85em; margin-left: 5px;"></i><span>Ma trận Cấp quyền</span>
                </a>
                <?php endif; ?>

                <?php if ($hasMenuAdmin): ?>
                <a href="<?= BASE_URL ?>/menuAdmin" class="nav-link <?= strpos($currentUrl, 'menuAdmin') === 0 ? 'active' : '' ?>">
                    <i class="fas fa-bars-staggered"></i><span>Admin Khung Web (Menu)</span>
                </a>
                <?php endif; ?>

                <?php if ($hasDbAdmin): ?>
                <a href="<?= BASE_URL ?>/databaseAdmin" class="nav-link <?= strpos($currentUrl, 'databaseAdmin') === 0 ? 'active' : '' ?>">
                    <i class="fas fa-database"></i><span>Admin Cơ sở Dữ liệu</span>
                </a>
                <a href="<?= BASE_URL ?>/backup" class="nav-link <?= strpos($currentUrl, 'backup') === 0 ? 'active' : '' ?>">
                    <i class="fas fa-server" style="font-size: 0.85em; margin-left: 5px;"></i><span>Sao lưu & Phục hồi</span>
                </a>
                <a href="<?= BASE_URL ?>/audit" class="nav-link <?= strpos($currentUrl, 'audit') === 0 ? 'active' : '' ?>">
                    <i class="fas fa-clock-rotate-left" style="font-size: 0.85em; margin-left: 5px;"></i><span>Nhật ký Hoạt động (Audit)</span>
                </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>

        </div>

        <!-- Footer Sidebar (User Profile) -->
        <?php
            // Truy xuất chức danh thực tế từ DB
            $userModel = new User();
            $roleInfo = $userModel->getUserRoleInfo(Session::userId());
            $displayRole = $roleInfo ? $roleInfo['role_name'] : 'Nhân viên';
        ?>
        <div class="sidebar-footer">
            <div class="user-card">
                <div class="user-avatar">
                    <?= strtoupper(mb_substr(Session::userFullName() ?? 'A', 0, 1)) ?>
                </div>
                <div class="user-info">
                    <span class="user-name"><?= h(Session::userFullName() ?? 'User') ?></span>
                    <span class="user-role" style="font-size: 0.75rem; color: #34d399; font-weight: 600;"><?= h($displayRole) ?></span>
                </div>
                <a href="<?= BASE_URL ?>/auth/logout" class="btn-logout" title="Đăng xuất">
                    <i class="fas fa-right-from-bracket"></i>
                </a>
            </div>
        </div>
    </aside>

    <!-- ══════════════════════════════════════════════════════
         MAIN CONTENT AREA
         ══════════════════════════════════════════════════════ -->
    <main class="main-content" id="mainContent">

        <!-- Topbar -->
        <header class="topbar">
            <div class="topbar-left">
                <button class="mobile-toggle" id="mobileToggle" type="button" aria-label="Mở Menu">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="topbar-logo" style="display: flex; align-items: center; gap: 10px; margin-right: 15px;">
                    <div class="logo-icon-small" style="width: 32px; height: 32px; background: linear-gradient(135deg, var(--primary), var(--accent)); border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 800; color: #fff; box-shadow: 0 2px 8px rgba(59,130,246,0.3);">
                        PS
                    </div>
                </div>
                <h1 class="page-title d-none d-md-block text-truncate" style="max-width: 200px;"><?= h($pageTitle ?? 'POSUNG HRIS') ?></h1>
            </div>

            <!-- Global Search Component (Ctrl+K or /) -->
            <div class="topbar-search position-relative flex-grow-1 mx-2 mx-md-4" style="max-width: 440px;">
                <form action="<?= BASE_URL ?>/search" method="GET" id="globalSearchForm" onsubmit="if(!document.getElementById('globalSearchInput').value.trim()) return false;">
                    <div class="input-group input-group-sm rounded-pill overflow-hidden border bg-white shadow-sm" id="globalSearchInputGroup" style="border-color: #cbd5e1 !important;">
                        <span class="input-group-text bg-transparent border-0 ps-3 pe-2 text-muted">
                            <i class="fa-solid fa-magnifying-glass" id="globalSearchIcon"></i>
                        </span>
                        <input type="text" name="q" id="globalSearchInput" class="form-control bg-transparent border-0 py-2 small shadow-none" 
                               placeholder="Tìm nhân viên, phòng ban, dự án, đào tạo..." autocomplete="off">
                        <span class="input-group-text bg-transparent border-0 pe-2 text-muted d-none d-sm-flex align-items-center">
                            <kbd class="bg-light border rounded px-1.5 py-0.5 text-muted" style="font-size: 0.65rem; font-family: monospace;">Ctrl+K</kbd>
                        </span>
                    </div>
                </form>

                <!-- Global Search Results Dropdown -->
                <div id="globalSearchDropdown" class="dropdown-menu shadow-lg border-0 rounded-4 p-0 w-100" 
                     style="display: none; position: absolute; top: 100%; left: 0; right: 0; margin-top: 8px; z-index: 1070; max-height: 480px; overflow-y: auto;">
                    <div id="globalSearchDropdownContent">
                        <!-- AJAX Live Results dynamically populated -->
                    </div>
                </div>
            </div>

            <div class="topbar-right d-flex align-items-center">
                <!-- Quick Actions Dropdown -->
                <div class="dropdown me-2">
                    <button class="btn btn-primary btn-sm rounded-pill px-3 d-flex align-items-center gap-1 shadow-sm" 
                            type="button" id="quickActionsBtn" data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 0.8rem; font-weight: 600;">
                        <i class="fa-solid fa-plus"></i> <span class="d-none d-lg-inline">Tạo nhanh</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 p-2" aria-labelledby="quickActionsBtn" style="width: 270px; z-index: 1060;">
                        <li class="dropdown-header small text-uppercase fw-bold text-muted pb-1">Nghiệp vụ Nhân sự</li>
                        <li>
                            <a class="dropdown-item rounded-3 py-2 small d-flex align-items-center gap-2" href="<?= BASE_URL ?>/employee/create">
                                <i class="fa-solid fa-user-plus text-primary" style="width: 18px;"></i> Thêm nhân viên mới
                            </a>
                        <li>
                            <a class="dropdown-item rounded-3 py-2 small d-flex align-items-center gap-2" href="<?= BASE_URL ?>/offboarding">
                                <i class="fa-solid fa-user-minus text-danger" style="width: 18px;"></i> Quy trình thôi việc (Offboarding)
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item rounded-3 py-2 small d-flex align-items-center gap-2" href="<?= BASE_URL ?>/holiday">
                                <i class="fa-solid fa-calendar-star text-warning" style="width: 18px;"></i> Lịch nghỉ Lễ & Phép
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item rounded-3 py-2 small d-flex align-items-center gap-2" href="<?= BASE_URL ?>/leave/create">
                                <i class="fa-solid fa-calendar-plus text-success" style="width: 18px;"></i> Tạo đơn xin nghỉ phép
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item rounded-3 py-2 small d-flex align-items-center gap-2" href="<?= BASE_URL ?>/loan/create">
                                <i class="fa-solid fa-hand-holding-dollar text-warning" style="width: 18px;"></i> Đề nghị tạm ứng / vay vốn
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item rounded-3 py-2 small d-flex align-items-center gap-2" href="<?= BASE_URL ?>/expense/create">
                                <i class="fa-solid fa-receipt text-info" style="width: 18px;"></i> Đề nghị thanh toán chi phí
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item rounded-3 py-2 small d-flex align-items-center gap-2" href="<?= BASE_URL ?>/transfer/create">
                                <i class="fa-solid fa-shuffle text-secondary" style="width: 18px;"></i> Lệnh điều chuyển công trường
                            </a>
                        </li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li class="dropdown-header small text-uppercase fw-bold text-muted pb-1">Quy trình & Hộp duyệt</li>
                        <li>
                            <a class="dropdown-item rounded-3 py-2 small d-flex align-items-center gap-2" href="<?= BASE_URL ?>/workflow/pendingApprovals">
                                <i class="fa-solid fa-inbox text-danger" style="width: 18px;"></i> Hộp duyệt tập trung (Inbox)
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Notification Bell Dropdown -->
                <?php
                    $initialUnread = 0;
                    if (class_exists('NotificationService')) {
                        $initialUnread = NotificationService::getUnreadCount((int)Session::userId());
                    }
                ?>
                <div class="dropdown me-2" id="notifDropdownContainer">
                    <button class="btn btn-light position-relative rounded-circle p-0 d-flex align-items-center justify-content-center shadow-sm" 
                            type="button" id="notifBellBtn" data-bs-toggle="dropdown" aria-expanded="false" 
                            style="width: 38px; height: 38px; background: #fff; border: 1px solid #e2e8f0;">
                        <i class="fa-solid fa-bell text-secondary fs-6"></i>
                        <span id="notifBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" 
                              style="<?= $initialUnread > 0 ? '' : 'display: none;' ?> font-size: 0.65rem; padding: 3px 6px;">
                            <?= $initialUnread > 99 ? '99+' : $initialUnread ?>
                        </span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 p-0" 
                         style="width: 360px; max-width: 90vw; margin-top: 10px; z-index: 1060;" aria-labelledby="notifBellBtn">
                        <div class="p-3 border-bottom d-flex align-items-center justify-content-between bg-light rounded-top-4">
                            <span class="fw-bold text-dark small"><i class="fa-solid fa-bell text-primary me-1"></i> Thông báo</span>
                            <div class="d-flex gap-2">
                                <a href="<?= BASE_URL ?>/notification/markAllRead" class="text-decoration-none small text-primary fw-semibold" style="font-size: 0.75rem;">Đã đọc hết</a>
                                <span class="text-muted small">&bull;</span>
                                <a href="<?= BASE_URL ?>/notification" class="text-decoration-none small text-secondary" style="font-size: 0.75rem;">Xem tất cả</a>
                            </div>
                        </div>
                        <div id="notifDropdownList" class="list-group list-group-flush" style="max-height: 380px; overflow-y: auto;">
                            <div class="text-center py-4 text-muted small">
                                <i class="fa-solid fa-spinner fa-spin me-1"></i> Đang tải thông báo...
                            </div>
                        </div>
                        <div class="p-2 border-top text-center bg-light rounded-bottom-4">
                            <a href="<?= BASE_URL ?>/notification" class="btn btn-sm btn-link text-primary text-decoration-none small w-100 fw-semibold py-1">
                                Xem tất cả trong Trung tâm thông báo <i class="fa-solid fa-arrow-right small ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <a href="<?= BASE_URL ?>/ess/dashboard" class="btn btn-sm btn-outline-primary rounded-pill px-3 me-2 d-none d-sm-inline-flex align-items-center" style="font-size: 0.8rem; font-weight: 600;">
                    <i class="fa-solid fa-user-gear me-1"></i> Cổng ESS
                </a>
                <div class="topbar-date d-none d-xl-flex">
                    <i class="fas fa-calendar-day"></i>
                    <span><?= date('d/m/Y') ?></span>
                </div>
            </div>
        </header>

        <!-- Mobile Sidebar Backdrop Overlay -->
        <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

        <!-- AJAX Polling Script for Notification Bell & Global Search -->
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ── 1. NOTIFICATION BELL ──
            const notifBadge = document.getElementById('notifBadge');
            const notifList = document.getElementById('notifDropdownList');

            function fetchNotifications() {
                fetch('<?= BASE_URL ?>/notification/getUnread')
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success') {
                            if (data.unread_count > 0) {
                                notifBadge.textContent = data.unread_count > 99 ? '99+' : data.unread_count;
                                notifBadge.style.display = 'inline-block';
                            } else {
                                notifBadge.style.display = 'none';
                            }

                            if (notifList) {
                                if (!data.notifications || data.notifications.length === 0) {
                                    notifList.innerHTML = '<div class="text-center py-4 text-muted small"><i class="fa-regular fa-bell-slash me-1"></i> Không có thông báo mới</div>';
                                } else {
                                    let html = '';
                                    data.notifications.forEach(n => {
                                        const readBg = n.is_read ? 'bg-white' : 'bg-light';
                                        const itemLink = n.link ? n.link : ('<?= BASE_URL ?>/notification/markAsRead/' + n.id);
                                        html += `
                                        <a href="${itemLink}" class="list-group-item list-group-item-action p-3 border-bottom d-flex align-items-start gap-2 ${readBg}">
                                            <div class="rounded-circle p-2 d-flex align-items-center justify-content-center ${n.bg_class}" style="width: 32px; height: 32px; flex-shrink: 0; font-size: 0.8rem;">
                                                <i class="${n.icon_class}"></i>
                                            </div>
                                            <div class="flex-grow-1 overflow-hidden">
                                                <div class="d-flex align-items-center justify-content-between mb-1">
                                                    <span class="fw-bold small text-dark text-truncate">${escapeHtml(n.title)}</span>
                                                    ${!n.is_read ? '<span class="badge bg-danger rounded-pill ms-1" style="font-size: 0.6rem;">Mới</span>' : ''}
                                                </div>
                                                <p class="small text-secondary mb-1 text-truncate" style="max-height: 38px;">${escapeHtml(n.message)}</p>
                                                <small class="text-muted" style="font-size: 0.7rem;"><i class="fa-regular fa-clock me-1"></i>${n.time_ago}</small>
                                            </div>
                                        </a>`;
                                    });
                                    notifList.innerHTML = html;
                                }
                            }
                        }
                    })
                    .catch(err => console.error('Error fetching notifications:', err));
            }

            fetchNotifications();
            setInterval(fetchNotifications, 60000);

            // ── 2. GLOBAL SEARCH COMPONENT (Ctrl+K or /) ──
            const searchInput = document.getElementById('globalSearchInput');
            const searchDropdown = document.getElementById('globalSearchDropdown');
            const searchContent = document.getElementById('globalSearchDropdownContent');
            const searchIcon = document.getElementById('globalSearchIcon');
            let debounceTimer = null;
            let currentSelectedIndex = -1;

            // Shortcut Ctrl+K hoặc '/' để focus
            document.addEventListener('keydown', function(e) {
                if ((e.ctrlKey && e.key.toLowerCase() === 'k') || (e.key === '/' && document.activeElement.tagName !== 'INPUT' && document.activeElement.tagName !== 'TEXTAREA')) {
                    e.preventDefault();
                    if (searchInput) {
                        searchInput.focus();
                        searchInput.select();
                    }
                } else if (e.key === 'Escape' && searchDropdown && searchDropdown.style.display !== 'none') {
                    searchDropdown.style.display = 'none';
                    if (searchInput) searchInput.blur();
                }
            });

            if (searchInput) {
                // Lắng nghe gõ phím với debounce 300ms
                searchInput.addEventListener('input', function() {
                    const q = this.value.trim();
                    clearTimeout(debounceTimer);

                    if (q.length === 0) {
                        searchDropdown.style.display = 'none';
                        searchIcon.className = 'fa-solid fa-magnifying-glass text-muted';
                        return;
                    }

                    searchIcon.className = 'fa-solid fa-spinner fa-spin text-primary';
                    debounceTimer = setTimeout(() => {
                        performGlobalSearch(q);
                    }, 300);
                });

                // Lắng nghe điều hướng bằng phím mũi tên Lên/Xuống và Enter
                searchInput.addEventListener('keydown', function(e) {
                    const items = searchDropdown.querySelectorAll('.global-search-item');
                    if (items.length === 0 || searchDropdown.style.display === 'none') return;

                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        currentSelectedIndex = (currentSelectedIndex + 1) % items.length;
                        updateSelectedItem(items);
                    } else if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        currentSelectedIndex = (currentSelectedIndex - 1 + items.length) % items.length;
                        updateSelectedItem(items);
                    } else if (e.key === 'Enter' && currentSelectedIndex >= 0) {
                        e.preventDefault();
                        items[currentSelectedIndex].click();
                    }
                });

                // Focus lại nếu có text
                searchInput.addEventListener('focus', function() {
                    if (this.value.trim().length > 0 && searchContent.innerHTML.trim() !== '') {
                        searchDropdown.style.display = 'block';
                    }
                });

                // Ẩn dropdown khi click ra ngoài
                document.addEventListener('click', function(e) {
                    if (!searchInput.contains(e.target) && !searchDropdown.contains(e.target)) {
                        searchDropdown.style.display = 'none';
                    }
                });
            }

            function updateSelectedItem(items) {
                items.forEach((item, idx) => {
                    if (idx === currentSelectedIndex) {
                        item.classList.add('bg-primary-subtle');
                        item.scrollIntoView({ block: 'nearest' });
                    } else {
                        item.classList.remove('bg-primary-subtle');
                    }
                });
            }

            function performGlobalSearch(q) {
                fetch(`<?= BASE_URL ?>/search/search?q=${encodeURIComponent(q)}`)
                    .then(res => res.json())
                    .then(data => {
                        searchIcon.className = 'fa-solid fa-magnifying-glass text-muted';
                        currentSelectedIndex = -1;

                        if (data.status === 'success') {
                            renderSearchResults(data, q);
                        }
                    })
                    .catch(err => {
                        searchIcon.className = 'fa-solid fa-magnifying-glass text-muted';
                        console.error('Search error:', err);
                    });
            }

            function renderSearchResults(data, q) {
                if (data.total === 0) {
                    searchContent.innerHTML = `
                        <div class="text-center py-4 text-muted small">
                            <i class="fa-regular fa-folder-open fs-5 mb-2 d-block text-secondary"></i>
                            Không tìm thấy kết quả nào cho "<strong>${escapeHtml(q)}</strong>"
                        </div>
                    `;
                    searchDropdown.style.display = 'block';
                    return;
                }

                let html = '';

                // 1. Nhóm Nhân viên
                if (data.results.employees && data.results.employees.length > 0) {
                    html += `<div class="p-2 px-3 bg-light text-muted small fw-bold border-bottom d-flex justify-content-between align-items-center">
                        <span><i class="fa-solid fa-user me-1 text-primary"></i> Nhân viên (${data.results.employees.length})</span>
                    </div>`;
                    data.results.employees.forEach(item => {
                        const avatarHtml = item.avatar 
                            ? `<img src="${item.avatar}" class="rounded-circle border" style="width: 32px; height: 32px; object-fit: cover;">`
                            : `<div class="rounded-circle bg-light border d-flex align-items-center justify-content-center text-primary fw-bold" style="width: 32px; height: 32px; font-size: 0.8rem;">${item.title.charAt(0)}</div>`;
                        html += `
                        <a href="${item.url}" class="dropdown-item global-search-item px-3 py-2 d-flex align-items-center gap-2 border-bottom text-decoration-none">
                            ${avatarHtml}
                            <div class="flex-grow-1 overflow-hidden">
                                <div class="fw-bold text-dark small text-truncate">${highlightMatch(item.title, q)}</div>
                                <div class="text-muted text-truncate" style="font-size: 0.72rem;">${escapeHtml(item.subtitle)}</div>
                            </div>
                            <span class="badge ${item.badge_class} rounded-pill" style="font-size: 0.65rem;">${escapeHtml(item.badge)}</span>
                        </a>`;
                    });
                }

                // 2. Nhóm Phòng ban
                if (data.results.departments && data.results.departments.length > 0) {
                    html += `<div class="p-2 px-3 bg-light text-muted small fw-bold border-bottom d-flex justify-content-between align-items-center">
                        <span><i class="fa-solid fa-sitemap me-1 text-info"></i> Phòng ban (${data.results.departments.length})</span>
                    </div>`;
                    data.results.departments.forEach(item => {
                        html += `
                        <a href="${item.url}" class="dropdown-item global-search-item px-3 py-2 d-flex align-items-center gap-2 border-bottom text-decoration-none">
                            <div class="rounded-circle bg-info-subtle border border-info-subtle d-flex align-items-center justify-content-center text-info" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                <i class="${item.icon}"></i>
                            </div>
                            <div class="flex-grow-1 overflow-hidden">
                                <div class="fw-bold text-dark small text-truncate">${highlightMatch(item.title, q)}</div>
                                <div class="text-muted text-truncate" style="font-size: 0.72rem;">${escapeHtml(item.subtitle)}</div>
                            </div>
                            <span class="badge bg-light text-muted border" style="font-size: 0.65rem;">${escapeHtml(item.meta)}</span>
                        </a>`;
                    });
                }

                // 3. Nhóm Dự án
                if (data.results.projects && data.results.projects.length > 0) {
                    html += `<div class="p-2 px-3 bg-light text-muted small fw-bold border-bottom d-flex justify-content-between align-items-center">
                        <span><i class="fa-solid fa-building-shield me-1 text-success"></i> Dự án (${data.results.projects.length})</span>
                    </div>`;
                    data.results.projects.forEach(item => {
                        html += `
                        <a href="${item.url}" class="dropdown-item global-search-item px-3 py-2 d-flex align-items-center gap-2 border-bottom text-decoration-none">
                            <div class="rounded-circle bg-success-subtle border border-success-subtle d-flex align-items-center justify-content-center text-success" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                <i class="${item.icon}"></i>
                            </div>
                            <div class="flex-grow-1 overflow-hidden">
                                <div class="fw-bold text-dark small text-truncate">${highlightMatch(item.title, q)}</div>
                                <div class="text-muted text-truncate" style="font-size: 0.72rem;">${escapeHtml(item.subtitle)}</div>
                            </div>
                            <span class="badge bg-light text-primary border" style="font-size: 0.65rem;">${escapeHtml(item.meta)}</span>
                        </a>`;
                    });
                }

                // 4. Nhóm Khóa đào tạo
                if (data.results.trainings && data.results.trainings.length > 0) {
                    html += `<div class="p-2 px-3 bg-light text-muted small fw-bold border-bottom d-flex justify-content-between align-items-center">
                        <span><i class="fa-solid fa-graduation-cap me-1 text-warning"></i> Đào tạo (${data.results.trainings.length})</span>
                    </div>`;
                    data.results.trainings.forEach(item => {
                        html += `
                        <a href="${item.url}" class="dropdown-item global-search-item px-3 py-2 d-flex align-items-center gap-2 border-bottom text-decoration-none">
                            <div class="rounded-circle bg-warning-subtle border border-warning-subtle d-flex align-items-center justify-content-center text-warning" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                <i class="${item.icon}"></i>
                            </div>
                            <div class="flex-grow-1 overflow-hidden">
                                <div class="fw-bold text-dark small text-truncate">${highlightMatch(item.title, q)}</div>
                                <div class="text-muted text-truncate" style="font-size: 0.72rem;">${escapeHtml(item.subtitle)}</div>
                            </div>
                        </a>`;
                    });
                }

                // Bottom link: Xem tất cả
                html += `
                <div class="p-2 bg-light text-center border-top rounded-bottom-4">
                    <a href="<?= BASE_URL ?>/search?q=${encodeURIComponent(q)}" class="btn btn-sm btn-link text-primary text-decoration-none fw-semibold small w-100 py-1">
                        Xem tất cả ${data.total} kết quả cho "<strong>${escapeHtml(q)}</strong>" <i class="fa-solid fa-arrow-right ms-1 small"></i>
                    </a>
                </div>`;

                searchContent.innerHTML = html;
                searchDropdown.style.display = 'block';
            }

            function highlightMatch(text, query) {
                if (!text) return '';
                const escapedQuery = query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                const regex = new RegExp(`(${escapedQuery})`, 'gi');
                return text.replace(regex, '<span class="text-primary fw-bolder text-decoration-underline">$1</span>');
            }

            function escapeHtml(text) {
                if (!text) return '';
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }

            // ── 3. MOBILE SIDEBAR OFF-CANVAS & BACKDROP ──
            const sidebar = document.getElementById('sidebar');
            const mobileToggle = document.getElementById('mobileToggle');
            const sidebarBackdrop = document.getElementById('sidebarBackdrop');

            if (mobileToggle && sidebar) {
                mobileToggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    sidebar.classList.toggle('mobile-open');
                    if (sidebarBackdrop) {
                        sidebarBackdrop.style.display = sidebar.classList.contains('mobile-open') ? 'block' : 'none';
                    }
                });
            }

            if (sidebarBackdrop && sidebar) {
                sidebarBackdrop.addEventListener('click', function() {
                    sidebar.classList.remove('mobile-open');
                    sidebarBackdrop.style.display = 'none';
                });
            }
        });
        </script>

        <!-- Page Wrapper -->
        <div class="page-content">

            <!-- Breadcrumb Navigation -->
            <?php
            $breadcrumbs = $breadcrumbs ?? [];
            if (empty($breadcrumbs)) {
                $currentRoute = trim($_GET['_route'] ?? '', '/');
                $parts = explode('/', $currentRoute);
                $breadcrumbs[] = ['title' => 'Trang chủ', 'url' => BASE_URL];
                
                $moduleTitles = [
                    'employee'          => 'Quản lý Nhân sự',
                    'organization'      => 'Cơ cấu Tổ chức',
                    'workflow'          => 'Quy trình & Phê duyệt',
                    'payroll'           => 'Tiền lương & C&B',
                    'leave'             => 'Nghỉ phép',
                    'timesheet'         => 'Chấm công',
                    'project'           => 'Dự án & Công trường',
                    'evaluation'        => 'Đánh giá 360°',
                    'training'          => 'Đào tạo & Phát triển',
                    'recruitment'       => 'Tuyển dụng',
                    'asset'             => 'Tài sản & Thiết bị',
                    'loan'              => 'Vay vốn & Ứng lương',
                    'expense'           => 'Chi phí & Công tác',
                    'transfer'          => 'Điều chuyển',
                    'insurance'         => 'Bảo hiểm Xã hội',
                    'notification'      => 'Thông báo',
                    'audit'             => 'Nhật ký Hoạt động',
                    'setting'           => 'Cài đặt Hệ thống',
                    'rbac'              => 'Phân quyền & Vai trò',
                    'ess'               => 'Cổng Nhân viên (ESS)',
                    'search'            => 'Tìm kiếm toàn cục'
                ];

                if (!empty($parts[0]) && isset($moduleTitles[$parts[0]])) {
                    $breadcrumbs[] = ['title' => $moduleTitles[$parts[0]], 'url' => BASE_URL . '/' . $parts[0]];
                }
                if (!empty($pageTitle) && (empty($parts[0]) || $pageTitle !== ($moduleTitles[$parts[0]] ?? ''))) {
                    $breadcrumbs[] = ['title' => $pageTitle, 'url' => null];
                }
            }
            ?>
            <nav aria-label="breadcrumb" class="mb-3 d-none d-md-block">
                <ol class="breadcrumb mb-0 py-1 px-0 bg-transparent small">
                    <?php foreach ($breadcrumbs as $bIdx => $bc): ?>
                        <?php if ($bIdx === count($breadcrumbs) - 1 || empty($bc['url'])): ?>
                            <li class="breadcrumb-item active text-dark fw-semibold" aria-current="page">
                                <?= h($bc['title']) ?>
                            </li>
                        <?php else: ?>
                            <li class="breadcrumb-item">
                                <a href="<?= $bc['url'] ?>" class="text-decoration-none text-muted">
                                    <?php if ($bIdx === 0): ?><i class="fa-solid fa-house-chimney me-1"></i><?php endif; ?>
                                    <?= h($bc['title']) ?>
                                </a>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ol>
            </nav>
            <!-- Flash Messages Global (Toasts) -->
            <div class="toast-container position-fixed top-0 end-0 p-3">
                <?php if (Session::hasFlash('success')): ?>
                    <div class="toast toast-success align-items-center text-bg-light border-0 show" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="5000">
                        <div class="d-flex">
                            <div class="toast-body">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <?= h(Session::getFlash('success')) ?>
                            </div>
                            <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (Session::hasFlash('error')): ?>
                    <div class="toast toast-danger align-items-center text-bg-light border-0 show" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="5000">
                        <div class="d-flex">
                            <div class="toast-body">
                                <i class="fas fa-exclamation-circle text-danger me-2"></i>
                                <?= h(Session::getFlash('error')) ?>
                            </div>
                            <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (Session::hasFlash('warning')): ?>
                    <div class="toast toast-warning align-items-center text-bg-light border-0 show" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="5000">
                        <div class="d-flex">
                            <div class="toast-body">
                                <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                                <?= h(Session::getFlash('warning')) ?>
                            </div>
                            <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
