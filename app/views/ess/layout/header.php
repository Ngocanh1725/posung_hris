<?php
/**
 * ============================================================
 *  POSUNG HRIS – ESS Portal Header Layout
 * ============================================================
 *  Giao diện tối ưu cho nhân viên: Hiện đại, sạch sẽ, chuẩn mobile
 * ============================================================
 */
$currentAction = $this->currentAction ?? 'dashboard';
$empFullName = Session::userFullName() ?? 'Nhân viên';
$empRole = Session::userRole() ?? 'employee';
$unreadNotifsCount = 0;
try {
    $db = Database::getInstance();
    $empId = Session::employeeId();
    if ($empId) {
        $db->query("SELECT COUNT(*) as cnt FROM employee_notifications WHERE employee_id = :id AND is_read = 0", ['id' => $empId]);
        $unreadNotifsCount = (int)($db->fetch()['cnt'] ?? 0);
    }
} catch (Exception $e) {}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#0d3c61">
    <title><?= htmlspecialchars($pageTitle ?? 'Cổng tự phục vụ Nhân viên') ?> – POSUNG ESS</title>

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Font Awesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root {
            --primary-navy: #0d3c61;
            --primary-dark: #07263e;
            --primary-blue: #0088cc;
            --primary-light: #f0f7fc;
            --accent-teal: #0ea5e9;
            --accent-emerald: #10b981;
            --accent-amber: #f59e0b;
            --accent-rose: #f43f5e;
            --gray-50: #f8fafc;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-300: #cbd5e1;
            --gray-600: #475569;
            --gray-700: #334155;
            --gray-800: #1e293b;
            --card-radius: 14px;
            --card-shadow: 0 4px 15px -1px rgba(0, 0, 0, 0.05), 0 2px 6px -2px rgba(0, 0, 0, 0.05);
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: #f4f6fa;
            color: var(--gray-800);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ── Topbar ESS ─────────────────────────────────────── */
        .ess-navbar {
            background: linear-gradient(135deg, var(--primary-navy) 0%, var(--primary-dark) 100%);
            box-shadow: 0 4px 20px rgba(13, 60, 97, 0.25);
            padding: 10px 0;
            position: sticky;
            top: 0;
            z-index: 1030;
        }

        .ess-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: #ffffff;
        }

        .ess-brand .badge-ps {
            background: linear-gradient(135deg, #0088cc, #00c6ff);
            color: #fff;
            font-weight: 800;
            font-size: 0.85rem;
            padding: 6px 10px;
            border-radius: 8px;
            letter-spacing: 0.5px;
        }

        .ess-brand-text {
            display: flex;
            flex-direction: column;
            line-height: 1.2;
        }

        .ess-brand-title {
            font-size: 1.05rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            color: #ffffff;
        }

        .ess-brand-sub {
            font-size: 0.72rem;
            color: #94a3b8;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Nav links */
        .ess-nav-link {
            color: #cbd5e1 !important;
            font-weight: 500;
            font-size: 0.9rem;
            padding: 8px 14px !important;
            border-radius: 8px;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .ess-nav-link:hover {
            color: #ffffff !important;
            background: rgba(255, 255, 255, 0.1);
        }

        .ess-nav-link.active {
            color: #ffffff !important;
            background: rgba(0, 136, 204, 0.4);
            font-weight: 600;
            box-shadow: inset 0 0 0 1px rgba(0, 136, 204, 0.6);
        }

        /* User Profile Pill */
        .ess-user-pill {
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.18);
            border-radius: 30px;
            padding: 4px 12px 4px 6px;
            display: flex;
            align-items: center;
            gap: 8px;
            color: #ffffff;
            text-decoration: none;
            cursor: pointer;
        }

        .ess-user-pill:hover {
            background: rgba(255, 255, 255, 0.2);
            color: #ffffff;
        }

        .ess-user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #0088cc;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.85rem;
            object-fit: cover;
        }

        /* Mobile Bottom Nav */
        .ess-bottom-nav {
            display: none;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: #ffffff;
            box-shadow: 0 -3px 15px rgba(0, 0, 0, 0.08);
            z-index: 1040;
            border-top: 1px solid var(--gray-200);
            padding: 6px 4px 8px;
        }

        .ess-bottom-item {
            flex: 1;
            text-align: center;
            color: var(--gray-600);
            text-decoration: none;
            font-size: 0.68rem;
            font-weight: 500;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 3px;
        }

        .ess-bottom-item i {
            font-size: 1.15rem;
        }

        .ess-bottom-item.active {
            color: var(--primary-blue);
            font-weight: 700;
        }

        @media (max-width: 991px) {
            .ess-bottom-nav {
                display: flex;
            }
            body {
                padding-bottom: 65px;
            }
        }

        /* Card Styles */
        .card-custom {
            background: #ffffff;
            border-radius: var(--card-radius);
            border: 1px solid var(--gray-200);
            box-shadow: var(--card-shadow);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .card-custom:hover {
            box-shadow: 0 8px 25px -3px rgba(0, 0, 0, 0.08);
        }

        .card-custom-header {
            padding: 16px 20px;
            border-bottom: 1px solid var(--gray-100);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .card-custom-title {
            font-size: 1rem;
            font-weight: 700;
            color: var(--gray-800);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .card-custom-body {
            padding: 20px;
        }

        /* Metric / Summary Cards */
        .metric-card {
            border-radius: var(--card-radius);
            padding: 18px;
            background: #ffffff;
            border: 1px solid var(--gray-200);
            box-shadow: var(--card-shadow);
            position: relative;
            overflow: hidden;
        }

        .metric-card::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
        }

        .metric-card.blue::before { background: var(--primary-blue); }
        .metric-card.emerald::before { background: var(--accent-emerald); }
        .metric-card.amber::before { background: var(--accent-amber); }
        .metric-card.rose::before { background: var(--accent-rose); }

        .metric-icon {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            margin-bottom: 12px;
        }

        .metric-card.blue .metric-icon { background: #e0f2fe; color: #0284c7; }
        .metric-card.emerald .metric-icon { background: #dcfce7; color: #16a34a; }
        .metric-card.amber .metric-icon { background: #fef3c7; color: #d97706; }
        .metric-card.rose .metric-icon { background: #ffe4e6; color: #e11d48; }

        .metric-value {
            font-size: 1.45rem;
            font-weight: 800;
            color: var(--gray-800);
            line-height: 1.1;
            margin-bottom: 4px;
        }

        .metric-label {
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--gray-600);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .metric-sub {
            font-size: 0.78rem;
            color: #64748b;
            margin-top: 6px;
        }

        /* Badges */
        .badge-status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.78rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .badge-status.pending { background: #fef3c7; color: #b45309; }
        .badge-status.approved { background: #dcfce7; color: #15803d; }
        .badge-status.rejected { background: #fee2e2; color: #b91c1c; }
        .badge-status.cancelled { background: #f1f5f9; color: #64748b; }
        .badge-status.active { background: #e0f2fe; color: #0369a1; }

        /* Print styles */
        @media print {
            .ess-navbar, .ess-bottom-nav, .no-print, .btn, nav, footer {
                display: none !important;
            }
            body {
                background: #ffffff !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .container {
                max-width: 100% !important;
                width: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .card-custom {
                box-shadow: none !important;
                border: none !important;
            }
        }
    </style>
</head>
<body>

    <!-- ══════════════════════════════════════════════════════
         TOPBAR NAVIGATION (DESKTOP & TABLET)
         ══════════════════════════════════════════════════════ -->
    <header class="ess-navbar">
        <div class="container-fluid px-lg-4">
            <div class="d-flex align-items-center justify-content-between">
                
                <!-- Brand Logo -->
                <a href="<?= BASE_URL ?>/ess/dashboard" class="ess-brand">
                    <span class="badge-ps">POSUNG</span>
                    <div class="ess-brand-text">
                        <span class="ess-brand-title">CỔNG NHÂN VIÊN</span>
                        <span class="ess-brand-sub">Employee Self-Service (ESS)</span>
                    </div>
                </a>

                <!-- Desktop Menu -->
                <nav class="d-none d-lg-flex align-items-center gap-1">
                    <a href="<?= BASE_URL ?>/ess/dashboard" class="ess-nav-link <?= in_array($currentAction, ['dashboard', 'index']) ? 'active' : '' ?>">
                        <i class="fa-solid fa-house-chimney"></i> Trang chủ
                    </a>
                    <a href="<?= BASE_URL ?>/ess/profile" class="ess-nav-link <?= $currentAction === 'profile' ? 'active' : '' ?>">
                        <i class="fa-solid fa-id-badge"></i> Hồ sơ
                    </a>
                    <a href="<?= BASE_URL ?>/ess/attendance" class="ess-nav-link <?= $currentAction === 'attendance' ? 'active' : '' ?>">
                        <i class="fa-solid fa-calendar-check"></i> Chấm công
                    </a>
                    <a href="<?= BASE_URL ?>/ess/payslip" class="ess-nav-link <?= $currentAction === 'payslip' ? 'active' : '' ?>">
                        <i class="fa-solid fa-receipt"></i> Phiếu lương
                    </a>
                    <a href="<?= BASE_URL ?>/ess/myLeaves" class="ess-nav-link <?= in_array($currentAction, ['myLeaves', 'leaveRequest']) ? 'active' : '' ?>">
                        <i class="fa-solid fa-calendar-xmark"></i> Nghỉ phép
                    </a>
                    <a href="<?= BASE_URL ?>/ess/myContracts" class="ess-nav-link <?= $currentAction === 'myContracts' ? 'active' : '' ?>">
                        <i class="fa-solid fa-file-signature"></i> Hợp đồng
                    </a>
                    <a href="<?= BASE_URL ?>/ess/myTrainings" class="ess-nav-link <?= $currentAction === 'myTrainings' ? 'active' : '' ?>">
                        <i class="fa-solid fa-graduation-cap"></i> Đào tạo
                    </a>
                    <a href="<?= BASE_URL ?>/ess/performance" class="ess-nav-link <?= $currentAction === 'performance' ? 'active' : '' ?>">
                        <i class="fa-solid fa-bullseye"></i> Đánh giá 360°
                    </a>
                    <a href="<?= BASE_URL ?>/ess/notifications" class="ess-nav-link <?= in_array($currentAction, ['notifications', 'viewArticle']) ? 'active' : '' ?> position-relative">
                        <i class="fa-solid fa-bell"></i> Thông báo
                        <?php if ($unreadNotifsCount > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem;">
                                <?= $unreadNotifsCount ?>
                            </span>
                        <?php endif; ?>
                    </a>
                </nav>

                <!-- Right Menu: User profile & Logout -->
                <div class="d-flex align-items-center gap-3">
                    <div class="dropdown">
                        <div class="ess-user-pill dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="ess-user-avatar">
                                <?= mb_strtoupper(mb_substr($empFullName, 0, 1, 'UTF-8'), 'UTF-8') ?>
                            </div>
                            <div class="d-none d-md-block text-start lh-1 me-1">
                                <div style="font-size: 0.85rem; font-weight: 600;"><?= htmlspecialchars($empFullName) ?></div>
                                <small style="font-size: 0.7rem; color: #94a3b8;">Nhân viên</small>
                            </div>
                            <i class="fa-solid fa-angle-down d-none d-md-inline" style="font-size: 0.75rem; color: #94a3b8;"></i>
                        </div>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" aria-labelledby="userDropdown" style="border-radius: 10px; margin-top: 8px;">
                            <li>
                                <div class="px-3 py-2 border-bottom">
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($empFullName) ?></div>
                                    <small class="text-muted">Mã tài khoản: <?= htmlspecialchars(Session::get('username') ?? '') ?></small>
                                </div>
                            </li>
                            <li>
                                <a class="dropdown-item py-2" href="<?= BASE_URL ?>/ess/profile">
                                    <i class="fa-regular fa-user text-primary me-2"></i> Hồ sơ của tôi
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item py-2" href="<?= BASE_URL ?>/ess/payslip">
                                    <i class="fa-solid fa-file-invoice-dollar text-success me-2"></i> Phiếu lương mới nhất
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item py-2" href="<?= BASE_URL ?>/ess/leaveRequest">
                                    <i class="fa-regular fa-calendar-plus text-warning me-2"></i> Nộp đơn nghỉ phép
                                </a>
                            </li>
                            <?php if (Session::isAdmin() || Session::isManager()): ?>
                            <li><hr class="dropdown-divider my-1"></li>
                            <li>
                                <a class="dropdown-item py-2 text-primary fw-semibold" href="<?= BASE_URL ?>">
                                    <i class="fa-solid fa-shield-halved me-2"></i> Về Trang Quản Trị (Admin)
                                </a>
                            </li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider my-1"></li>
                            <li>
                                <a class="dropdown-item py-2 text-danger fw-semibold" href="<?= BASE_URL ?>/auth/logout">
                                    <i class="fa-solid fa-arrow-right-from-bracket me-2"></i> Đăng xuất
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>

            </div>
        </div>
    </header>

    <!-- ══════════════════════════════════════════════════════
         FLASH MESSAGES
         ══════════════════════════════════════════════════════ -->
    <div class="container-xl mt-3 no-print">
        <?php if (Session::hasFlash('success')): ?>
            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center shadow-sm" role="alert" style="border-radius: 10px;">
                <i class="fa-solid fa-circle-check fs-5 me-2"></i>
                <div><?= Session::getFlash('success') ?></div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (Session::hasFlash('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center shadow-sm" role="alert" style="border-radius: 10px;">
                <i class="fa-solid fa-circle-exclamation fs-5 me-2"></i>
                <div><?= Session::getFlash('error') ?></div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (Session::hasFlash('warning')): ?>
            <div class="alert alert-warning alert-dismissible fade show d-flex align-items-center shadow-sm" role="alert" style="border-radius: 10px;">
                <i class="fa-solid fa-triangle-exclamation fs-5 me-2"></i>
                <div><?= Session::getFlash('warning') ?></div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
    </div>

    <!-- Main Content Wrapper -->
    <main class="flex-grow-1 py-3">
