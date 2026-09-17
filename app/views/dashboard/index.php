<!-- Dashboard Grid -->
<div class="dashboard-grid">

    <!-- KPI Row -->
    <div class="kpi-row">
        <!-- Tổng nhân sự -->
        <div class="kpi-card" onclick="window.location.href='<?= BASE_URL ?>/employee'" style="cursor: pointer;" title="Xem danh sách nhân sự">
            <div class="kpi-icon" style="background: linear-gradient(135deg, var(--primary), var(--primary-dark));">
                <i class="fas fa-users"></i>
            </div>
            <div class="kpi-body">
                <div class="kpi-value"><?= number_format($totalEmployees ?? 0) ?></div>
                <div class="kpi-label">Tổng Nhân sự</div>
            </div>
        </div>

        <!-- Dự án đang chạy -->
        <div class="kpi-card" onclick="window.location.href='<?= BASE_URL ?>/project'" style="cursor: pointer;" title="Quản lý dự án">
            <div class="kpi-icon" style="background: linear-gradient(135deg, var(--success), #059669);">
                <i class="fas fa-hard-hat"></i>
            </div>
            <div class="kpi-body">
                <div class="kpi-value"><?= number_format($totalProjects ?? 0) ?></div>
                <div class="kpi-label">Dự án Đang chạy</div>
            </div>
        </div>

        <!-- Chuyên gia nước ngoài -->
        <div class="kpi-card" onclick="window.location.href='<?= BASE_URL ?>/employee/expats'" style="cursor: pointer;" title="Quản lý chuyên gia">
            <div class="kpi-icon" style="background: linear-gradient(135deg, var(--accent), var(--accent-dark));">
                <i class="fas fa-passport"></i>
            </div>
            <div class="kpi-body">
                <div class="kpi-value"><?= number_format($totalExpats ?? 0) ?></div>
                <div class="kpi-label">Chuyên gia Expat</div>
            </div>
        </div>

        <!-- Cảnh báo hết hạn (Chứng chỉ + Visa) -->
        <div class="kpi-card" onclick="window.location.href='<?= BASE_URL ?>/employee/certificates'" style="<?= ($expiringCerts + $expiringExpat > 0) ? 'border-color: rgba(239,68,68,0.4); background: rgba(239,68,68,0.05);' : '' ?> cursor: pointer;" title="Xem danh sách hết hạn">
            <div class="kpi-icon" style="background: linear-gradient(135deg, var(--warning), #d97706);">
                <i class="fas fa-triangle-exclamation"></i>
            </div>
            <div class="kpi-body">
                <div class="kpi-value <?= ($expiringCerts + $expiringExpat > 0) ? 'text-danger' : '' ?>">
                    <?= number_format(($expiringCerts ?? 0) + ($expiringExpat ?? 0)) ?>
                </div>
                <div class="kpi-label">Cảnh báo Hết hạn (90 ngày)</div>
            </div>
        </div>
    </div>

    <!-- Analytics Charts Row -->
    <div class="row mt-4 mb-4">
        <!-- Biểu đồ phân bổ nhân lực -->
        <div class="col-md-6 mb-4">
            <div class="panel h-100">
                <div class="panel-header">
                    <h3><i class="fas fa-chart-pie"></i> Phân bổ Nhân lực (Theo loại)</h3>
                </div>
                <div class="panel-body d-flex justify-content-center align-items-center" style="height: 400px;">
                    <canvas id="employeeTypeChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Biểu đồ chi phí lương theo Cost Center -->
        <div class="col-md-6 mb-4">
            <div class="panel h-100">
                <div class="panel-header">
                    <h3><i class="fas fa-chart-doughnut"></i> Quỹ lương tháng này (Theo Dự án)</h3>
                </div>
                <div class="panel-body d-flex justify-content-center align-items-center" style="height: 400px;">
                    <canvas id="payrollCostChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Panel Row -->
    <div class="panel-row">
        <!-- Tin tức / Thông báo -->
        <div class="panel">
            <div class="panel-header">
                <h3><i class="fas fa-bullhorn"></i> Bảng tin Nội bộ</h3>
            </div>
            <div class="panel-body">
                <div class="timeline" style="margin-left: -10px;">
                    <div class="timeline-item">
                        <div class="timeline-dot"></div>
                        <div class="timeline-content">
                            <div class="timeline-date"><?= date('d/m/Y') ?></div>
                            <div class="timeline-title">Chào mừng nhân sự mới (<?= $newThisMonth ?? 0 ?>)</div>
                            <div class="timeline-desc">Tháng này công ty đã chào đón <?= $newThisMonth ?? 0 ?> thành viên mới gia nhập.</div>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-dot" style="background: var(--warning);"></div>
                        <div class="timeline-content">
                            <div class="timeline-date">Tin Hệ thống</div>
                            <div class="timeline-title">Hoàn tất nâng cấp HRIS V1.0</div>
                            <div class="timeline-desc">Hệ thống chuyển đổi từ DBF sang MySQL đã hoàn tất, hỗ trợ chấm công và phân bổ Cost Center. Tích hợp HSE Blacklist và Offboarding.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Phím tắt nhanh -->
        <div class="panel">
            <div class="panel-header">
                <h3><i class="fas fa-bolt"></i> Truy cập nhanh</h3>
            </div>
            <div class="panel-body" style="display: flex; flex-wrap: wrap; gap: 12px;">
                <?php if (Session::isManager()): ?>
                    <a href="<?= BASE_URL ?>/employee/create" class="btn btn-primary">
                        <i class="fas fa-user-plus"></i> Thêm Nhân viên
                    </a>
                <?php endif; ?>
                <a href="<?= BASE_URL ?>/employee/retireAlerts" class="btn btn-ghost">
                    <i class="fas fa-user-clock text-warning"></i> Báo cáo Hưu trí
                </a>
                <a href="<?= BASE_URL ?>/reward" class="btn btn-ghost">
                    <i class="fas fa-skull text-danger"></i> HSE Blacklist
                </a>
                <a href="<?= BASE_URL ?>/employee/certificates" class="btn btn-ghost <?= ($expiringCerts > 0) ? 'text-warning' : '' ?>">
                    <i class="fas fa-certificate"></i> Chứng chỉ (<?= $expiringCerts ?? 0 ?>)
                </a>
                <a href="<?= BASE_URL ?>/employee/expats" class="btn btn-ghost <?= ($expiringExpat > 0) ? 'text-warning' : '' ?>">
                    <i class="fas fa-plane-arrival"></i> Visa Expat (<?= $expiringExpat ?? 0 ?>)
                </a>
            </div>
        </div>
    </div>
</div>

<style>
.text-danger { color: var(--danger) !important; }
.text-warning { color: var(--warning) !important; border-color: var(--warning) !important; }

/* Tái sử dụng style timeline */
.timeline { position: relative; padding-left: 24px; }
.timeline::before { content: ''; position: absolute; left: 6px; top: 0; bottom: 0; width: 2px; background: var(--border); }
.timeline-item { position: relative; margin-bottom: 24px; }
.timeline-dot { position: absolute; left: -21px; top: 4px; width: 10px; height: 10px; border-radius: 50%; background: var(--primary); border: 2px solid var(--bg-card); }
.timeline-date { font-size: 11px; color: var(--text-muted); margin-bottom: 4px; font-weight: 600; }
.timeline-title { font-size: 14px; font-weight: 600; color: var(--text-heading); margin-bottom: 4px; }
.timeline-desc { font-size: 13px; color: var(--text-secondary); line-height: 1.6; }
</style>

<!-- Tích hợp Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // 1. Biểu đồ Phân bổ nhân lực (Pie Chart)
    const empTypesData = <?= json_encode($employeeTypesData ?? []) ?>;
    
    // Mapping label cho đẹp
    const typeMapping = {
        'Expat': 'Chuyên gia (Expat)',
        'Office': 'Văn phòng',
        'Site_Engineer': 'Kỹ sư Hiện trường',
        'Direct_Worker': 'Công nhân / Thợ hàn'
    };

    const empLabels = empTypesData.map(item => typeMapping[item.employee_type] || item.employee_type);
    const empData = empTypesData.map(item => item.count);
    const empColors = ['#10B981', '#3B82F6', '#F59E0B', '#EF4444'];

    if (empData.length > 0) {
        new Chart(document.getElementById('employeeTypeChart'), {
            type: 'pie',
            data: {
                labels: empLabels,
                datasets: [{
                    data: empData,
                    backgroundColor: empColors,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    }

    // 2. Biểu đồ Quỹ lương theo Cost Center (Doughnut Chart)
    const payrollData = <?= json_encode($payrollCostData ?? []) ?>;
    
    const prLabels = payrollData.map(item => item.project_name || 'Văn phòng / Chưa phân bổ');
    const prCost = payrollData.map(item => item.total_cost);
    
    // Auto-generate colors
    const prColors = ['#8B5CF6', '#EC4899', '#14B8A6', '#F97316', '#06B6D4', '#EAB308'];

    if (prCost.length > 0) {
        new Chart(document.getElementById('payrollCostChart'), {
            type: 'doughnut',
            data: {
                labels: prLabels,
                datasets: [{
                    data: prCost,
                    backgroundColor: prColors.slice(0, prLabels.length),
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                if (label) { label += ': '; }
                                if (context.parsed !== null) {
                                    label += new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(context.parsed);
                                }
                                return label;
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>
