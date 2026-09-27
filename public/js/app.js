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

    // ── Bootstrap Toasts ───────────────────────────────────────
    const toastElList = document.querySelectorAll('.toast');
    const toastList = [...toastElList].map(toastEl => new bootstrap.Toast(toastEl));
    toastList.forEach(toast => toast.show());

    // ── Bootstrap Tooltips ─────────────────────────────────────
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

});
