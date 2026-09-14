/**
 * ============================================================
 *  POSUNG HRIS – Client-side Application Script
 * ============================================================
 */

document.addEventListener('DOMContentLoaded', () => {

    // ── Sidebar Toggle Logic ────────────────────────────────────
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const mobileToggle = document.getElementById('mobileToggle');

    // Desktop Toggle (Collapse/Expand)
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
            
            // Lưu trạng thái vào localStorage để giữ nguyên khi chuyển trang
            if (sidebar.classList.contains('collapsed')) {
                localStorage.setItem('sidebarState', 'collapsed');
            } else {
                localStorage.removeItem('sidebarState');
            }
        });

        // Khôi phục trạng thái từ localStorage khi tải trang
        if (localStorage.getItem('sidebarState') === 'collapsed' && window.innerWidth > 1024) {
            sidebar.classList.add('collapsed');
        }
    }

    // Mobile Toggle (Off-canvas)
    if (mobileToggle && sidebar) {
        mobileToggle.addEventListener('click', () => {
            sidebar.classList.toggle('mobile-open');
        });

        // Đóng sidebar khi click ra ngoài (trên mobile)
        document.addEventListener('click', (e) => {
            if (window.innerWidth <= 1024) {
                if (!sidebar.contains(e.target) && !mobileToggle.contains(e.target) && sidebar.classList.contains('mobile-open')) {
                    sidebar.classList.remove('mobile-open');
                }
            }
        });
    }

    // ── Auto-hide Flash Alerts ────────────────────────────────
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        // Tự động biến mất sau 5 giây
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            
            setTimeout(() => {
                alert.remove();
            }, 500);
        }, 5000);
    });

});
