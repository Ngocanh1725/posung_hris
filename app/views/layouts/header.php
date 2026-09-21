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
            <!-- ── TỔNG QUAN ── -->
            <div class="nav-section">
                <span class="nav-section-title">Tổng quan</span>
                <a href="<?= BASE_URL ?>" class="nav-link <?= (empty($_GET['url']) || $_GET['url'] === 'dashboard/index') ? 'active' : '' ?>">
                    <i class="fas fa-chart-pie"></i><span>Dashboard</span>
                </a>
                <a href="<?= BASE_URL ?>/leave" class="nav-link <?= (strpos($_GET['url'] ?? '', 'leave') === 0) ? 'active' : '' ?>">
                    <i class="fas fa-calendar-alt"></i><span>Nghỉ phép</span>
                </a>
            </div>

            <!-- ── QUẢN LÝ NHÂN SỰ ── -->
            <div class="nav-section">
                <span class="nav-section-title">Nhân sự & Chuyên gia</span>
                <a href="<?= BASE_URL ?>/employee" class="nav-link <?= (strpos($_GET['url'] ?? '', 'employee') === 0 && strpos($_GET['url'] ?? '', 'expats') === false && strpos($_GET['url'] ?? '', 'certificates') === false) ? 'active' : '' ?>">
                    <i class="fas fa-users"></i><span>Hồ sơ Nhân sự</span>
                </a>
                <a href="<?= BASE_URL ?>/contract" class="nav-link <?= (strpos($_GET['url'] ?? '', 'contract') === 0) ? 'active' : '' ?>">
                    <i class="fas fa-file-signature"></i><span>Quản lý Hợp đồng</span>
                </a>
                <a href="<?= BASE_URL ?>/recruitment" class="nav-link <?= (strpos($_GET['url'] ?? '', 'recruitment') === 0) ? 'active' : '' ?>">
                    <i class="fas fa-user-plus"></i><span>Tuyển dụng</span>
                </a>
                <a href="<?= BASE_URL ?>/employee/expats" class="nav-link <?= strpos($_GET['url'] ?? '', 'expats') !== false ? 'active' : '' ?>">
                    <i class="fas fa-passport"></i><span>Chuyên gia (Expat)</span>
                </a>
                <a href="<?= BASE_URL ?>/employee/certificates" class="nav-link <?= strpos($_GET['url'] ?? '', 'certificates') !== false ? 'active' : '' ?>">
                    <i class="fas fa-certificate"></i><span>Chứng chỉ & HSE</span>
                </a>
            </div>

            <!-- ── QUẢN LÝ DỰ ÁN ── -->
            <?php if (Session::isManager() || Session::userRole() === 'Project_Manager' || Session::userRole() === 'Site_Supervisor'): ?>
            <div class="nav-section">
                <span class="nav-section-title">Dự án & Tổ chức</span>
                <a href="<?= BASE_URL ?>/organization/overview" class="nav-link <?= strpos($_GET['url'] ?? '', 'organization/overview') !== false ? 'active' : '' ?>">
                    <i class="fas fa-building"></i><span>Tổng quan Cơ cấu</span>
                </a>
                <a href="<?= BASE_URL ?>/organization" class="nav-link <?= (strpos($_GET['url'] ?? '', 'organization') === 0 && strpos($_GET['url'] ?? '', 'organization/overview') === false && strpos($_GET['url'] ?? '', 'organization/detail') === false) ? 'active' : '' ?>">
                    <i class="fas fa-sitemap"></i><span>Sơ đồ Tổ chức</span>
                </a>
                <a href="<?= BASE_URL ?>/project" class="nav-link <?= strpos($_GET['url'] ?? '', 'project') === 0 ? 'active' : '' ?>">
                    <i class="fas fa-hard-hat"></i><span>Quản lý Dự án</span>
                </a>
                <a href="<?= BASE_URL ?>/movement" class="nav-link <?= strpos($_GET['url'] ?? '', 'movement') === 0 ? 'active' : '' ?>">
                    <i class="fas fa-truck-fast"></i><span>Điều động (Job Move)</span>
                </a>
            </div>
            <?php endif; ?>

            <!-- ── LƯƠNG & CHẤM CÔNG ── -->
            <?php if (Session::isManager() || Session::userRole() === 'Project_Manager'): ?>
            <div class="nav-section">
                <span class="nav-section-title">Tiền lương</span>
                <a href="<?= BASE_URL ?>/timesheet" class="nav-link <?= strpos($_GET['url'] ?? '', 'timesheet') === 0 ? 'active' : '' ?>">
                    <i class="fas fa-clock"></i><span>Chấm công</span>
                </a>
                <a href="<?= BASE_URL ?>/payroll" class="nav-link <?= strpos($_GET['url'] ?? '', 'payroll') === 0 ? 'active' : '' ?>">
                    <i class="fas fa-money-check-dollar"></i><span>Tính lương</span>
                </a>
                <a href="<?= BASE_URL ?>/reward" class="nav-link <?= strpos($_GET['url'] ?? '', 'reward') === 0 ? 'active' : '' ?>">
                    <i class="fas fa-award"></i><span>Khen thưởng / Kỷ luật</span>
                </a>
            </div>
            <?php endif; ?>

            <!-- ── DANH MỤC (ADMIN) ── -->
            <?php if (Session::isAdmin() || Session::userRole() === 'HR_Manager'): ?>
            <div class="nav-section">
                <span class="nav-section-title">Danh mục Hệ thống</span>
                <a href="<?= BASE_URL ?>/category/departments" class="nav-link <?= strpos($_GET['url'] ?? '', 'category/departments') !== false ? 'active' : '' ?>">
                    <i class="fas fa-sitemap"></i><span>Sơ đồ / Phòng ban</span>
                </a>
                <a href="<?= BASE_URL ?>/category/positions" class="nav-link <?= strpos($_GET['url'] ?? '', 'category/positions') !== false ? 'active' : '' ?>">
                    <i class="fas fa-id-badge"></i><span>Chức vụ / Vị trí</span>
                </a>
                <a href="<?= BASE_URL ?>/category/manage/contract_types" class="nav-link <?= strpos($_GET['url'] ?? '', 'category/manage/contract_types') !== false ? 'active' : '' ?>">
                    <i class="fas fa-file-signature"></i><span>Loại hợp đồng</span>
                </a>
                <a href="<?= BASE_URL ?>/category/manage/leave_types" class="nav-link <?= strpos($_GET['url'] ?? '', 'category/manage/leave_types') !== false ? 'active' : '' ?>">
                    <i class="fas fa-calendar-times"></i><span>Loại phép</span>
                </a>
                <a href="<?= BASE_URL ?>/category/manage/allowances" class="nav-link <?= strpos($_GET['url'] ?? '', 'category/manage/allowances') !== false ? 'active' : '' ?>">
                    <i class="fas fa-money-bill-wave"></i><span>Các khoản Phụ cấp</span>
                </a>
            </div>
            <?php endif; ?>

            <!-- ── BÁO CÁO & AI ── -->
            <?php if (Session::isManager()): ?>
            <div class="nav-section">
                <span class="nav-section-title">Báo cáo & Phân tích</span>
                <a href="<?= BASE_URL ?>/report" class="nav-link <?= strpos($_GET['url'] ?? '', 'report') === 0 ? 'active' : '' ?>">
                    <i class="fas fa-chart-pie"></i><span>Báo cáo Tổng hợp</span>
                </a>
                <a href="<?= BASE_URL ?>/ai" class="nav-link <?= strpos($_GET['url'] ?? '', 'ai') === 0 ? 'active' : '' ?>">
                    <i class="fas fa-brain"></i><span>Hệ Chuyên gia (AI)</span>
                </a>
                <?php if (Session::isAdmin()): ?>
                <a href="<?= BASE_URL ?>/user" class="nav-link <?= strpos($_GET['url'] ?? '', 'user') === 0 ? 'active' : '' ?>">
                    <i class="fas fa-user-shield"></i><span>Quản lý Tài khoản</span>
                </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Footer Sidebar (User Profile) -->
        <div class="sidebar-footer">
            <div class="user-card">
                <div class="user-avatar">
                    <?= strtoupper(mb_substr(Session::userFullName() ?? 'A', 0, 1)) ?>
                </div>
                <div class="user-info">
                    <span class="user-name"><?= h(Session::userFullName() ?? 'User') ?></span>
                    <span class="user-role"><?= h(Session::userRole() ?? 'Employee') ?></span>
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
