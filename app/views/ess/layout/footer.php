    </main>

    <!-- ══════════════════════════════════════════════════════
         FOOTER DESKTOP
         ══════════════════════════════════════════════════════ -->
    <footer class="bg-white border-top py-3 mt-auto no-print text-center text-md-start">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col-md-6 mb-2 mb-md-0">
                    <span class="text-muted small">
                        <strong>POSUNG HRIS</strong> – Cổng tự phục vụ Nhân viên (ESS Portal) &copy; <?= date('Y') ?> Posung E&C. Bảo lưu mọi quyền.
                    </span>
                </div>
                <div class="col-md-6 text-md-end">
                    <span class="text-muted small">
                        <i class="fa-solid fa-headset text-primary me-1"></i> Hỗ trợ kỹ thuật / HR: <strong>hr@posung.vn</strong> | Hotline: <strong>024.3795.8888</strong>
                    </span>
                </div>
            </div>
        </div>
    </footer>

    <!-- ══════════════════════════════════════════════════════
         MOBILE BOTTOM NAVIGATION (Màn hình điện thoại < 992px)
         ══════════════════════════════════════════════════════ -->
    <?php $act = $this->currentAction ?? 'dashboard'; ?>
    <div class="ess-bottom-nav no-print">
        <a href="<?= BASE_URL ?>/ess/dashboard" class="ess-bottom-item <?= in_array($act, ['dashboard', 'index']) ? 'active' : '' ?>">
            <i class="fa-solid fa-house-chimney"></i>
            <span>Trang chủ</span>
        </a>
        <a href="<?= BASE_URL ?>/ess/attendance" class="ess-bottom-item <?= $act === 'attendance' ? 'active' : '' ?>">
            <i class="fa-solid fa-calendar-check"></i>
            <span>Chấm công</span>
        </a>
        <a href="<?= BASE_URL ?>/ess/myLeaves" class="ess-bottom-item <?= in_array($act, ['myLeaves', 'leaveRequest']) ? 'active' : '' ?>">
            <i class="fa-solid fa-calendar-xmark"></i>
            <span>Nghỉ phép</span>
        </a>
        <a href="<?= BASE_URL ?>/ess/payslip" class="ess-bottom-item <?= $act === 'payslip' ? 'active' : '' ?>">
            <i class="fa-solid fa-receipt"></i>
            <span>Phiếu lương</span>
        </a>
        <a href="<?= BASE_URL ?>/ess/profile" class="ess-bottom-item <?= $act === 'profile' ? 'active' : '' ?>">
            <i class="fa-solid fa-circle-user"></i>
            <span>Hồ sơ</span>
        </a>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
