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
    <title><?= h($pageTitle ?? 'Trang quản trị') ?> – POSUNG HRIS</title>

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Font Awesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- CSS của ứng dụng -->
    <link href="<?= BASE_URL ?>/css/style.css?v=<?= time() ?>" rel="stylesheet">
</head>
<body>

    <!-- ══════════════════════════════════════════════════════
         SIDEBAR ĐIỀU HƯỚNG
         ══════════════════════════════════════════════════════ -->
    <aside class="sidebar" id="sidebar">
        <!-- Logo -->
        <div class="sidebar-header">
            <a href="<?= BASE_URL ?>" class="sidebar-logo">
                <div class="logo-icon">PS</div>
                <div class="logo-text">
                    <span class="logo-title">POSUNG HRIS</span>
                    <span class="logo-subtitle">Quản trị Hệ thống</span>
                </div>
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
            $currentUrl = $_GET['url'] ?? '';

            // ── TỔNG QUAN (Cố định, ai cũng được xem Dashboard) ──
            ?>
            <div class="nav-section">
                <span class="nav-section-title">Tổng quan</span>
                <a href="<?= BASE_URL ?>" class="nav-link <?= (empty($currentUrl) || $currentUrl === 'dashboard/index') ? 'active' : '' ?>">
                    <i class="fas fa-chart-pie"></i><span>Dashboard</span>
                </a>
            </div>
            
            <?php
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
            $hasMenuAdmin = $isSA || Session::hasPermission('frame_menu.manage');
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
                <button class="mobile-toggle" id="mobileToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <h1 class="page-title"><?= h($pageTitle ?? 'POSUNG HRIS') ?></h1>
            </div>
            <div class="topbar-right">
                <div class="topbar-date">
                    <i class="fas fa-calendar-day"></i>
                    <span><?= date('d/m/Y') ?></span>
                </div>
            </div>
        </header>

        <!-- Page Wrapper -->
        <div class="page-content">
            <!-- Flash Messages Global -->
            <?php if (Session::hasFlash('success')): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <span><?= h(Session::getFlash('success')) ?></span>
                </div>
            <?php endif; ?>

            <?php if (Session::hasFlash('error')): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?= h(Session::getFlash('error')) ?></span>
                </div>
            <?php endif; ?>
