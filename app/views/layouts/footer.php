<?php
/**
 * ============================================================
 *  POSUNG HRIS – Footer Layout
 * ============================================================
 *  Đóng thẻ page-content, main-content, body, html.
 *  Nạp các file JavaScript.
 * ============================================================
 */
?>
        </div> <!-- /page-content -->
    </main> <!-- /main-content -->

    <style>
    /* PWA Mobile Bottom Navigation */
    .mobile-bottom-nav {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        box-shadow: 0 -2px 10px rgba(0,0,0,0.05);
        display: flex;
        justify-content: space-around;
        padding: 10px 0;
        z-index: 9999;
        border-top: 1px solid rgba(226, 232, 240, 0.8);
        padding-bottom: env(safe-area-inset-bottom, 10px); /* For iPhone X+ */
    }
    .mobile-bottom-nav .nav-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-decoration: none;
        color: #64748b;
        font-size: 11px;
        font-weight: 500;
        transition: all 0.2s ease;
    }
    .mobile-bottom-nav .nav-item i {
        font-size: 20px;
        margin-bottom: 4px;
        transition: transform 0.2s;
    }
    .mobile-bottom-nav .nav-item:hover,
    .mobile-bottom-nav .nav-item:active {
        color: #1d4ed8;
    }
    .mobile-bottom-nav .nav-item:hover i {
        transform: translateY(-2px);
    }
    
    /* Responsive Fixes for PWA */
    @media (max-width: 768px) {
        body { padding-bottom: 70px; } /* Space for bottom nav */
        .sidebar { display: none !important; }
        .main-content { margin-left: 0 !important; width: 100% !important; padding: 15px; }
        .dashboard-grid, .kpi-row, .row { display: flex; flex-direction: column; }
        .col-md-6, .col-md-7, .col-md-5 { width: 100% !important; margin-bottom: 15px; }
        .kanban-card { width: 100% !important; }
        .table-wrapper { overflow-x: auto; }
        .header-left h2 { font-size: 1.25rem; }
    }
    </style>

    <!-- Mobile Bottom Navigation (Only visible on Mobile) -->
    <nav class="mobile-bottom-nav d-md-none">
        <a href="<?= BASE_URL ?>" class="nav-item">
            <i class="fas fa-home"></i>
            <span>Home</span>
        </a>
        <a href="<?= BASE_URL ?>/workflow" class="nav-item">
            <i class="fas fa-calendar-alt"></i>
            <span>Xin Nghỉ</span>
        </a>
        <a href="<?= BASE_URL ?>/payroll" class="nav-item">
            <i class="fas fa-file-invoice-dollar"></i>
            <span>E-Payslip</span>
        </a>
        <a href="#" class="nav-item" onclick="alert('Đã gửi thông báo Push Notification thử nghiệm!')">
            <i class="fas fa-bell"></i>
            <span>Thông báo</span>
        </a>
    </nav>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- JS của ứng dụng -->
    <script src="<?= BASE_URL ?>/js/app.js"></script>

    <!-- Register Service Worker (PWA) -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('<?= BASE_URL ?>/service-worker.js')
                    .then(registration => {
                        console.log('ServiceWorker registered with scope:', registration.scope);
                    })
                    .catch(error => {
                        console.error('ServiceWorker registration failed:', error);
                    });
            });
        }
    </script>
</body>
</html>
