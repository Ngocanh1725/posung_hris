<?php /** View: reports/index.php – Dashboard Báo cáo */ ?>

<div class="kpi-row" style="margin-bottom:24px;">
    <!-- Active Employees -->
    <div class="kpi-card">
        <div class="kpi-icon" style="background:linear-gradient(135deg,var(--primary),var(--primary-dark));"><i class="fas fa-users"></i></div>
        <div class="kpi-body">
            <div class="kpi-value"><?= $summary['total_active'] ?></div>
            <div class="kpi-label">Nhân sự Đang LV</div>
        </div>
    </div>
    <!-- Khen thưởng -->
    <div class="kpi-card">
        <div class="kpi-icon" style="background:linear-gradient(135deg,#10b981,#059669);"><i class="fas fa-trophy"></i></div>
        <div class="kpi-body">
            <div class="kpi-value"><?= $summary['total_reward'] ?? 0 ?></div>
            <div class="kpi-label">Khen thưởng (<?= date('Y') ?>)</div>
        </div>
    </div>
    <!-- Kỷ luật -->
    <div class="kpi-card">
        <div class="kpi-icon" style="background:linear-gradient(135deg,#ef4444,#dc2626);"><i class="fas fa-gavel"></i></div>
        <div class="kpi-body">
            <div class="kpi-value"><?= $summary['total_discipline'] ?? 0 ?></div>
            <div class="kpi-label">Kỷ luật (<?= date('Y') ?>)</div>
        </div>
    </div>
    <!-- Sắp hưu -->
    <div class="kpi-card">
        <div class="kpi-icon" style="background:linear-gradient(135deg,#f59e0b,#d97706);"><i class="fas fa-user-clock"></i></div>
        <div class="kpi-body">
            <div class="kpi-value"><?= $summary['retiring_soon'] ?></div>
            <div class="kpi-label">Sắp hưu (6 tháng)</div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Cột trái: Nhân sự -->
    <div class="col-md-6 mb-4">
        <div class="panel h-100">
            <div class="panel-header">
                <h3><i class="fas fa-users"></i> Báo cáo Nhân sự</h3>
            </div>
            <div class="panel-body p-0">
                <div class="list-group">
                    <a href="<?= BASE_URL ?>/report/headcount" class="list-group-item">
                        <div><i class="fas fa-users fa-fw text-primary"></i> <strong>BC Quân số</strong><br><small class="text-muted">Chi tiết danh sách nhân viên theo phòng ban, dự án</small></div>
                        <i class="fas fa-chevron-right text-muted"></i>
                    </a>
                    <a href="<?= BASE_URL ?>/report/retirement" class="list-group-item">
                        <div><i class="fas fa-user-clock fa-fw text-warning"></i> <strong>BC Hưu trí</strong><br><small class="text-muted">Danh sách nhân sự đến tuổi nghỉ hưu</small></div>
                        <i class="fas fa-chevron-right text-muted"></i>
                    </a>
                    <a href="<?= BASE_URL ?>/report/transfer" class="list-group-item">
                        <div><i class="fas fa-exchange-alt fa-fw text-info"></i> <strong>BC Thuyên chuyển</strong><br><small class="text-muted">Lịch sử luân chuyển công tác (Job Movement)</small></div>
                        <i class="fas fa-chevron-right text-muted"></i>
                    </a>
                    <a href="<?= BASE_URL ?>/report/recruitment" class="list-group-item">
                        <div><i class="fas fa-user-plus fa-fw text-success"></i> <strong>BC Tuyển dụng</strong><br><small class="text-muted">Tình hình tuyển dụng (<?= $summary['active_recruitment'] ?> YCTD đang mở)</small></div>
                        <i class="fas fa-chevron-right text-muted"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Cột phải: KT/KL & Tiền lương -->
    <div class="col-md-6 mb-4">
        <div class="panel h-100">
            <div class="panel-header">
                <h3><i class="fas fa-money-check-dollar"></i> Khen thưởng & Tiền lương</h3>
            </div>
            <div class="panel-body p-0">
                <div class="list-group">
                    <a href="<?= BASE_URL ?>/report/reward" class="list-group-item">
                        <div><i class="fas fa-trophy fa-fw text-success"></i> <strong>BC Khen thưởng</strong><br><small class="text-muted">Danh sách các quyết định khen thưởng</small></div>
                        <i class="fas fa-chevron-right text-muted"></i>
                    </a>
                    <a href="<?= BASE_URL ?>/report/discipline" class="list-group-item">
                        <div><i class="fas fa-gavel fa-fw text-danger"></i> <strong>BC Kỷ luật & HSE</strong><br><small class="text-muted">Danh sách kỷ luật, vi phạm an toàn lao động</small></div>
                        <i class="fas fa-chevron-right text-muted"></i>
                    </a>
                    <a href="<?= BASE_URL ?>/report/attendance" class="list-group-item">
                        <div><i class="fas fa-clock fa-fw text-primary"></i> <strong>BC Chấm công</strong><br><small class="text-muted">Tổng hợp ngày công, tăng ca (OT), đi làm ngày Lễ</small></div>
                        <i class="fas fa-chevron-right text-muted"></i>
                    </a>
                    <a href="<?= BASE_URL ?>/report/payroll" class="list-group-item">
                        <div><i class="fas fa-money-bill-wave fa-fw text-success"></i> <strong>BC Tiền lương</strong><br><small class="text-muted">Bảng lương tổng hợp, khấu trừ, thuế TNCN</small></div>
                        <i class="fas fa-chevron-right text-muted"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.list-group { display: flex; flex-direction: column; }
.list-group-item { display: flex; justify-content: space-between; align-items: center; padding: 16px 20px; border-bottom: 1px solid var(--border); text-decoration: none; color: var(--text-heading); transition: all 0.2s; }
.list-group-item:last-child { border-bottom: none; }
.list-group-item:hover { background: rgba(0,0,0,0.02); padding-left: 24px; }
.text-primary { color: var(--primary); }
.text-success { color: var(--success); }
.text-danger { color: var(--danger); }
.text-warning { color: var(--warning); }
.text-info { color: #06b6d4; }
.text-muted { color: var(--text-muted); font-size: 0.85rem; }
.fa-fw { width: 24px; text-align: center; margin-right: 8px; font-size: 1.1rem; }
</style>
