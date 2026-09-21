<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="POSUNG HRIS – Hệ thống Quản lý Nhân sự Công ty TNHH CK KT XD Po Sung">
    <title>Đăng nhập – POSUNG HRIS</title>

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    <style>
        /* ═══════════════════════════════════════════════════════
         * POSUNG HRIS – Trang đăng nhập
         * Bảng màu thương hiệu công nghiệp:
         *   Xanh Navy đậm: #0a1628, #0f2240, #152d50
         *   Cam cơ khí:    #e8630a, #f57c1f, #ff9642
         * ═══════════════════════════════════════════════════════ */

        :root {
            /* Xanh Navy đậm – Công nghiệp nặng */
            --navy-900: #0a1628;
            --navy-800: #0f2240;
            --navy-700: #152d50;
            --navy-600: #1c3a66;
            --navy-500: #24497d;

            /* Cam cơ khí – Năng động, mạnh mẽ */
            --orange-600: #e8630a;
            --orange-500: #f57c1f;
            --orange-400: #ff9642;
            --orange-300: #ffb76b;

            /* Trung tính */
            --steel-100: #e8ecf1;
            --steel-200: #c8d1dc;
            --steel-400: #7a8ba0;
            --steel-600: #4a5b71;
        }

        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            min-height: 100vh;
            display: flex;
            background: var(--navy-900);
            overflow: hidden;
            position: relative;
        }

        /* ── Panel bên trái: Ảnh thương hiệu ──────────────── */
        .login-brand {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 60px;
            position: relative;
            overflow: hidden;
            background:
                linear-gradient(135deg,
                    rgba(10, 22, 40, 0.92) 0%,
                    rgba(15, 34, 64, 0.88) 50%,
                    rgba(21, 45, 80, 0.85) 100%
                ),
                url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="rgba(255,255,255,0.03)" stroke-width="0.5"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
        }

        /* Hiệu ứng đường chéo cam */
        .login-brand::before {
            content: '';
            position: absolute;
            width: 300px; height: 600px;
            background: linear-gradient(135deg, var(--orange-600), var(--orange-400));
            opacity: 0.06;
            transform: rotate(-30deg);
            top: -100px; right: -100px;
            border-radius: 60px;
        }
        .login-brand::after {
            content: '';
            position: absolute;
            width: 200px; height: 400px;
            background: linear-gradient(135deg, var(--orange-500), var(--orange-300));
            opacity: 0.04;
            transform: rotate(-30deg);
            bottom: -50px; left: -50px;
            border-radius: 40px;
        }

        .brand-content {
            position: relative;
            z-index: 2;
            text-align: center;
            max-width: 440px;
        }

        .brand-logo-wrapper {
            margin-bottom: 40px;
        }
        .brand-logo-icon {
            width: 88px; height: 88px;
            background: linear-gradient(135deg, var(--orange-600), var(--orange-400));
            border-radius: 22px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            font-weight: 900;
            color: #fff;
            box-shadow:
                0 12px 40px rgba(232, 99, 10, 0.25),
                0 0 0 1px rgba(232, 99, 10, 0.15);
            margin-bottom: 24px;
            animation: logoFloat 4s ease-in-out infinite;
        }
        @keyframes logoFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-8px); }
        }

        .brand-title {
            font-size: 32px;
            font-weight: 900;
            color: #fff;
            letter-spacing: 3px;
            margin-bottom: 8px;
        }
        .brand-title span {
            color: var(--orange-500);
        }
        .brand-subtitle {
            font-size: 14px;
            color: var(--steel-400);
            font-weight: 400;
            line-height: 1.8;
        }

        .brand-features {
            margin-top: 48px;
            text-align: left;
        }
        .brand-feature {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 12px 0;
            color: var(--steel-200);
            font-size: 13px;
            font-weight: 400;
        }
        .brand-feature i {
            width: 36px; height: 36px;
            background: rgba(232, 99, 10, 0.12);
            border: 1px solid rgba(232, 99, 10, 0.2);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--orange-400);
            font-size: 14px;
            flex-shrink: 0;
        }

        .brand-clients {
            margin-top: 48px;
            padding-top: 24px;
            border-top: 1px solid rgba(255,255,255,0.06);
        }
        .brand-clients-label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: var(--steel-600);
            margin-bottom: 16px;
        }
        .client-logos {
            display: flex;
            gap: 24px;
            justify-content: center;
            align-items: center;
        }
        .client-name {
            font-size: 13px;
            font-weight: 600;
            color: var(--steel-400);
            letter-spacing: 1px;
            opacity: 0.7;
        }

        /* ── Panel bên phải: Form đăng nhập ────────────────── */
        .login-form-panel {
            width: 520px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 60px;
            background: var(--navy-800);
            border-left: 1px solid rgba(255,255,255,0.04);
        }

        .login-form-wrapper {
            width: 100%;
            max-width: 380px;
        }

        .login-header {
            margin-bottom: 36px;
        }
        .login-header h2 {
            font-size: 24px;
            font-weight: 700;
            color: #fff;
            margin-bottom: 8px;
        }
        .login-header p {
            font-size: 14px;
            color: var(--steel-400);
        }

        /* Thông báo lỗi */
        .login-error {
            background: rgba(220, 53, 69, 0.1);
            border: 1px solid rgba(220, 53, 69, 0.25);
            border-left: 4px solid #dc3545;
            color: #f8a4ad;
            padding: 14px 18px;
            border-radius: 10px;
            font-size: 13px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: shakeError 0.4s ease;
        }
        .login-error i {
            font-size: 16px;
            color: #dc3545;
        }
        @keyframes shakeError {
            0%, 100% { transform: translateX(0); }
            20% { transform: translateX(-8px); }
            40% { transform: translateX(8px); }
            60% { transform: translateX(-4px); }
            80% { transform: translateX(4px); }
        }

        /* Form fields */
        .form-floating-custom {
            margin-bottom: 20px;
            position: relative;
        }
        .form-floating-custom label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: var(--steel-400);
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 10px;
        }
        .input-wrapper {
            position: relative;
        }
        .input-wrapper i.input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--steel-600);
            font-size: 15px;
            transition: color 0.3s;
            z-index: 2;
        }
        .form-control-custom {
            width: 100%;
            padding: 15px 16px 15px 48px;
            background: var(--navy-900);
            border: 1.5px solid rgba(255,255,255,0.08);
            border-radius: 12px;
            color: #fff;
            font-size: 15px;
            font-family: 'Inter', sans-serif;
            transition: all 0.3s ease;
            outline: none;
        }
        .form-control-custom::placeholder {
            color: var(--steel-600);
        }
        .form-control-custom:focus {
            border-color: var(--orange-500);
            box-shadow: 0 0 0 3px rgba(245, 124, 31, 0.12);
            background: rgba(10, 22, 40, 0.8);
        }
        .form-control-custom:focus + i.input-icon,
        .input-wrapper:focus-within i.input-icon {
            color: var(--orange-500);
        }

        /* Nút hiện/ẩn mật khẩu */
        .toggle-password {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--steel-600);
            cursor: pointer;
            padding: 4px;
            font-size: 14px;
            z-index: 2;
            transition: color 0.2s;
        }
        .toggle-password:hover {
            color: var(--orange-400);
        }

        /* Nút đăng nhập */
        .btn-login {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, var(--orange-600), var(--orange-500));
            border: none;
            border-radius: 12px;
            color: #fff;
            font-size: 15px;
            font-weight: 700;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            margin-top: 8px;
            position: relative;
            overflow: hidden;
        }
        .btn-login::before {
            content: '';
            position: absolute;
            top: 0; left: -100%;
            width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.15), transparent);
            transition: left 0.5s ease;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(232, 99, 10, 0.3);
        }
        .btn-login:hover::before {
            left: 100%;
        }
        .btn-login:active {
            transform: translateY(0);
        }

        /* Footer */
        .login-footer {
            margin-top: 40px;
            text-align: center;
            color: var(--steel-600);
            font-size: 11px;
            line-height: 1.8;
        }
        .login-footer a {
            color: var(--orange-400);
            text-decoration: none;
        }

        /* ── Responsive ────────────────────────────────────── */
        @media (max-width: 1024px) {
            .login-brand { display: none; }
            .login-form-panel {
                width: 100%;
                border-left: none;
            }
        }
        @media (max-width: 480px) {
            .login-form-panel { padding: 30px 24px; }
        }
    </style>
</head>
<body>

    <!-- ══════════════════════════════════════════════════════
         PANEL TRÁI: Giới thiệu thương hiệu Po Sung
         ══════════════════════════════════════════════════════ -->
    <div class="login-brand">
        <div class="brand-content">
            <div class="brand-logo-wrapper">
                <div class="brand-logo-icon">PS</div>
                <h1 class="brand-title">PO<span>SUNG</span> HRIS</h1>
                <p class="brand-subtitle">
                    Hệ thống Quản lý Nhân sự<br>
                    Công ty TNHH Cơ khí Kỹ thuật Xây dựng Po Sung
                </p>
            </div>

            <div class="brand-features">
                <div class="brand-feature">
                    <i class="fas fa-users-gear"></i>
                    <span>Quản lý hồ sơ nhân sự & Chuyên gia nước ngoài (Expat)</span>
                </div>
                <div class="brand-feature">
                    <i class="fas fa-helmet-safety"></i>
                    <span>Chứng chỉ An toàn thi công – Hàn 6G – HSE Nhóm 3/6</span>
                </div>
                <div class="brand-feature">
                    <i class="fas fa-building"></i>
                    <span>Phân bổ chi phí dự án (Cost Center) theo hợp đồng</span>
                </div>
                <div class="brand-feature">
                    <i class="fas fa-chart-line"></i>
                    <span>Chấm công, Tính lương, Báo cáo biểu mẫu 2C</span>
                </div>
            </div>

            <div class="brand-clients">
                <div class="brand-clients-label">Đối tác chiến lược</div>
                <div class="client-logos">
                    <span class="client-name">SAMSUNG</span>
                    <span class="client-name">AMKOR</span>
                    <span class="client-name">DAEWOO E&C</span>
                </div>
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════
         PANEL PHẢI: Form đăng nhập
         ══════════════════════════════════════════════════════ -->
    <div class="login-form-panel">
        <div class="login-form-wrapper">

            <div class="login-header">
                <h2><i class="fas fa-right-to-bracket" style="color: var(--orange-500); margin-right: 10px;"></i>Đăng nhập</h2>
                <p>Nhập thông tin tài khoản để truy cập hệ thống</p>
            </div>

            <!-- Thông báo lỗi (hiện khi nhập sai) -->
            <?php if (!empty($error)): ?>
                <div class="login-error" id="login-error">
                    <i class="fas fa-circle-exclamation"></i>
                    <span><?= h($error) ?></span>
                </div>
            <?php endif; ?>

            <!-- Flash message từ session (VD: hết phiên đăng nhập) -->
            <?php
                $flashError = Session::getFlash('error');
                if ($flashError && empty($error)):
            ?>
                <div class="login-error">
                    <i class="fas fa-circle-exclamation"></i>
                    <span><?= h($flashError) ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?= BASE_URL ?>/auth/login" autocomplete="off" id="login-form">
                <input type="hidden" name="_csrf_token" value="<?= Session::generateCsrfToken() ?>">

                <!-- Tên đăng nhập -->
                <div class="form-floating-custom">
                    <label for="username">Tên đăng nhập</label>
                    <div class="input-wrapper">
                        <input type="text"
                               class="form-control-custom"
                               id="username"
                               name="username"
                               placeholder="Nhập tên đăng nhập"
                               value="<?= h($username ?? '') ?>"
                               required
                               autofocus>
                        <i class="fas fa-user input-icon"></i>
                    </div>
                </div>

                <!-- Mật khẩu -->
                <div class="form-floating-custom">
                    <label for="password">Mật khẩu</label>
                    <div class="input-wrapper">
                        <input type="password"
                               class="form-control-custom"
                               id="password"
                               name="password"
                               placeholder="Nhập mật khẩu"
                               required>
                        <i class="fas fa-lock input-icon"></i>
                        <button type="button" class="toggle-password" id="toggle-password" title="Hiện/Ẩn mật khẩu">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <!-- Nút đăng nhập -->
                <button type="submit" class="btn-login" id="btn-login">
                    <i class="fas fa-arrow-right-to-bracket"></i>&nbsp;&nbsp;Đăng nhập hệ thống
                </button>
            </form>

            <div class="login-footer">
                <p>&copy; <?= date('Y') ?> Công ty TNHH CK KT XD Po Sung</p>
                <p>Nhà thầu Cơ điện Công nghiệp FDI – <a href="#">posung.vn</a></p>
            </div>
        </div>
    </div>

    <!-- ── JavaScript ──────────────────────────────────────── -->
    <script>
        // Nút hiện/ẩn mật khẩu
        document.getElementById('toggle-password').addEventListener('click', function () {
            const input = document.getElementById('password');
            const icon  = this.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        });
    </script>
</body>
</html>
