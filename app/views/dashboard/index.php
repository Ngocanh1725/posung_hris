<!-- Dashboard Grid -->
<div class="dashboard-grid">

    <!-- Real-time On-site (IoT Camera) -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="panel glass-panel" style="background: linear-gradient(135deg, rgba(15, 23, 42, 0.9), rgba(30, 41, 59, 0.9)); color: white; border: 1px solid rgba(255,255,255,0.1);">
                <div class="panel-body d-flex justify-content-between align-items-center">
                    <div>
                        <h3 style="color: #38bdf8; margin: 0;"><i class="fas fa-satellite-dish fa-spin"></i> Real-time On-site (Camera AI)</h3>
                        <p style="margin: 5px 0 0 0; color: #94a3b8; font-size: 14px;">Cập nhật theo thời gian thực từ 3 Cổng công trường</p>
                    </div>
                    <div class="text-end" id="iotWidget">
                        <div class="spinner-border spinner-border-sm text-info"></div> Đang tải dữ liệu...
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI Row -->
    <div class="kpi-row">
        <!-- Tổng nhân sự -->
        <div class="kpi-card" onclick="window.location.href='<?= BASE_URL ?>/employee'" style="cursor: pointer; position: relative; padding-bottom: 40px;" title="Xem danh sách nhân sự">
            <div class="kpi-icon" style="background: linear-gradient(135deg, var(--primary), var(--primary-dark));">
                <i class="fas fa-users"></i>
            </div>
            <div class="kpi-body">
                <div class="kpi-value"><?= number_format($totalEmployees ?? 0) ?></div>
                <div class="kpi-label">Tổng Nhân sự</div>
            </div>
            <div style="position: absolute; bottom: 0; left: 0; width: 100%; padding: 10px 20px; background: rgba(0,0,0,0.02); border-top: 1px solid rgba(0,0,0,0.05); text-align: center; color: var(--primary); font-size: 0.85rem; font-weight: 600;">
                Xem chi tiết <i class="fas fa-arrow-right"></i>
            </div>
        </div>

        <!-- Dự án đang chạy -->
        <div class="kpi-card" onclick="window.location.href='<?= BASE_URL ?>/project'" style="cursor: pointer; position: relative; padding-bottom: 40px;" title="Quản lý dự án">
            <div class="kpi-icon" style="background: linear-gradient(135deg, var(--success), #059669);">
                <i class="fas fa-hard-hat"></i>
            </div>
            <div class="kpi-body">
                <div class="kpi-value"><?= number_format($totalProjects ?? 0) ?></div>
                <div class="kpi-label">Dự án Đang chạy</div>
            </div>
            <div style="position: absolute; bottom: 0; left: 0; width: 100%; padding: 10px 20px; background: rgba(0,0,0,0.02); border-top: 1px solid rgba(0,0,0,0.05); text-align: center; color: var(--success); font-size: 0.85rem; font-weight: 600;">
                Xem chi tiết <i class="fas fa-arrow-right"></i>
            </div>
        </div>

        <!-- Chuyên gia nước ngoài -->
        <div class="kpi-card" onclick="window.location.href='<?= BASE_URL ?>/employee/expats'" style="cursor: pointer; position: relative; padding-bottom: 40px;" title="Quản lý chuyên gia">
            <div class="kpi-icon" style="background: linear-gradient(135deg, var(--accent), var(--accent-dark));">
                <i class="fas fa-passport"></i>
            </div>
            <div class="kpi-body">
                <div class="kpi-value"><?= number_format($totalExpats ?? 0) ?></div>
                <div class="kpi-label">Chuyên gia Expat</div>
            </div>
            <div style="position: absolute; bottom: 0; left: 0; width: 100%; padding: 10px 20px; background: rgba(0,0,0,0.02); border-top: 1px solid rgba(0,0,0,0.05); text-align: center; color: var(--accent); font-size: 0.85rem; font-weight: 600;">
                Xem chi tiết <i class="fas fa-arrow-right"></i>
            </div>
        </div>

        <!-- Cảnh báo hết hạn (Chứng chỉ + Visa) -->
        <div class="kpi-card" onclick="window.location.href='<?= BASE_URL ?>/employee/certificates'" style="<?= ($expiringCerts + $expiringExpat > 0) ? 'border-color: rgba(239,68,68,0.4); background: rgba(239,68,68,0.05);' : '' ?> cursor: pointer; position: relative; padding-bottom: 40px;" title="Xem danh sách hết hạn">
            <div class="kpi-icon" style="background: linear-gradient(135deg, var(--warning), #d97706);">
                <i class="fas fa-triangle-exclamation"></i>
            </div>
            <div class="kpi-body">
                <div class="kpi-value <?= ($expiringCerts + $expiringExpat > 0) ? 'text-danger' : '' ?>">
                    <?= number_format(($expiringCerts ?? 0) + ($expiringExpat ?? 0)) ?>
                </div>
                <div class="kpi-label">Cảnh báo Hết hạn (90 ngày)</div>
            </div>
            <div style="position: absolute; bottom: 0; left: 0; width: 100%; padding: 10px 20px; background: rgba(0,0,0,0.02); border-top: 1px solid rgba(0,0,0,0.05); text-align: center; color: var(--warning); font-size: 0.85rem; font-weight: 600;">
                Xem chi tiết <i class="fas fa-arrow-right"></i>
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

    <!-- Biểu đồ biến động nhân sự -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="panel h-100">
                <div class="panel-header">
                    <h3><i class="fas fa-chart-bar"></i> Biến động Nhân sự (6 tháng gần nhất)</h3>
                </div>
                <div class="panel-body d-flex justify-content-center align-items-center" style="height: 350px;">
                    <canvas id="jobMovementsChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- System Architecture Diagram -->
    <div class="row mt-4 mb-4">
        <div class="col-12">
            <div class="panel glass-panel h-100">
                <div class="panel-header glass-header">
                    <h3><i class="fas fa-network-wired text-gradient"></i> Kiến trúc Hệ thống HRIS POSUNG</h3>
                </div>
                <div class="panel-body">
                    <div class="system-diagram-wrapper" style="display: flex; flex-wrap: wrap; justify-content: center; align-items: center; gap: 30px; padding: 20px;">
                        
                        <!-- Core System -->
                        <div class="sys-module core" style="background: linear-gradient(135deg, var(--primary), var(--primary-dark)); color: white; padding: 24px; border-radius: 16px; width: 220px; text-align: center; box-shadow: 0 10px 30px rgba(30,58,138,0.25); transition: all 0.3s; cursor: default; position: relative; z-index: 2;">
                            <i class="fas fa-server" style="font-size: 2.5rem; margin-bottom: 12px; color: #60a5fa;"></i>
                            <h4 style="font-size: 1.2rem; font-weight: 800; margin: 0 0 8px 0; letter-spacing: 0.5px;">CORE SYSTEM</h4>
                            <p style="font-size: 0.85rem; opacity: 0.85; margin: 0; line-height: 1.4;">Quản lý CSDL &<br>Phân quyền ma trận</p>
                        </div>

                        <!-- Connector -->
                        <div class="connector" style="display: flex; align-items: center; color: #cbd5e1; font-size: 2rem;">
                            <i class="fas fa-arrow-right-arrow-left"></i>
                        </div>

                        <!-- Modules Grid -->
                        <div class="sys-modules-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; flex: 1; max-width: 600px;">
                            
                            <div class="sys-node" style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 16px; border-radius: 12px; display: flex; align-items: center; gap: 16px; transition: all 0.3s; cursor: pointer;" onmouseover="this.style.borderColor='#3b82f6'; this.style.transform='translateY(-3px)'; this.style.boxShadow='0 8px 20px rgba(59,130,246,0.1)';" onmouseout="this.style.borderColor='#e2e8f0'; this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                                <div style="width: 48px; height: 48px; background: rgba(59,130,246,0.1); color: #3b82f6; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; flex-shrink: 0;"><i class="fas fa-building"></i></div>
                                <div>
                                    <div style="font-weight: 700; font-size: 0.95rem; color: #0f172a;">Quản trị Văn phòng</div>
                                    <div style="font-size: 0.8rem; color: #64748b; margin-top: 2px;">Hồ sơ, Chấm công, Khen thưởng</div>
                                </div>
                            </div>
                            
                            <div class="sys-node" style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 16px; border-radius: 12px; display: flex; align-items: center; gap: 16px; transition: all 0.3s; cursor: pointer;" onmouseover="this.style.borderColor='#10b981'; this.style.transform='translateY(-3px)'; this.style.boxShadow='0 8px 20px rgba(16,185,129,0.1)';" onmouseout="this.style.borderColor='#e2e8f0'; this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                                <div style="width: 48px; height: 48px; background: rgba(16,185,129,0.1); color: #10b981; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; flex-shrink: 0;"><i class="fas fa-hard-hat"></i></div>
                                <div>
                                    <div style="font-weight: 700; font-size: 0.95rem; color: #0f172a;">Site PM</div>
                                    <div style="font-size: 0.8rem; color: #64748b; margin-top: 2px;">Dự án, Điều phối nhân sự</div>
                                </div>
                            </div>

                            <div class="sys-node" style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 16px; border-radius: 12px; display: flex; align-items: center; gap: 16px; transition: all 0.3s; cursor: pointer;" onmouseover="this.style.borderColor='#f59e0b'; this.style.transform='translateY(-3px)'; this.style.boxShadow='0 8px 20px rgba(245,158,11,0.1)';" onmouseout="this.style.borderColor='#e2e8f0'; this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                                <div style="width: 48px; height: 48px; background: rgba(245,158,11,0.1); color: #f59e0b; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; flex-shrink: 0;"><i class="fas fa-shield-alt"></i></div>
                                <div>
                                    <div style="font-weight: 700; font-size: 0.95rem; color: #0f172a;">Admin Nội dung (HSE)</div>
                                    <div style="font-size: 0.8rem; color: #64748b; margin-top: 2px;">Bảng tin, An toàn lao động</div>
                                </div>
                            </div>

                            <div class="sys-node" style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 16px; border-radius: 12px; display: flex; align-items: center; gap: 16px; transition: all 0.3s; cursor: pointer;" onmouseover="this.style.borderColor='#8b5cf6'; this.style.transform='translateY(-3px)'; this.style.boxShadow='0 8px 20px rgba(139,92,246,0.1)';" onmouseout="this.style.borderColor='#e2e8f0'; this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                                <div style="width: 48px; height: 48px; background: rgba(139,92,246,0.1); color: #8b5cf6; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; flex-shrink: 0;"><i class="fas fa-microchip"></i></div>
                                <div>
                                    <div style="font-weight: 700; font-size: 0.95rem; color: #0f172a;">AI & Thống kê</div>
                                    <div style="font-size: 0.8rem; color: #64748b; margin-top: 2px;">Báo cáo Dashboard, Tối ưu</div>
                                </div>
                            </div>

                        </div>
                    </div>
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

    // 3. Biểu đồ Biến động nhân sự (Bar Chart)
    const movementsData = <?= json_encode($chartMovements ?? []) ?>;
    if (movementsData && movementsData.labels) {
        new Chart(document.getElementById('jobMovementsChart'), {
            type: 'bar',
            data: {
                labels: movementsData.labels,
                datasets: [
                    {
                        label: 'Thuyên chuyển',
                        data: movementsData.transfer,
                        backgroundColor: '#3B82F6',
                        borderRadius: 4
                    },
                    {
                        label: 'Thăng chức',
                        data: movementsData.promotion,
                        backgroundColor: '#10B981',
                        borderRadius: 4
                    },
                    {
                        label: 'Giáng chức',
                        data: movementsData.demotion,
                        backgroundColor: '#F59E0B',
                        borderRadius: 4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                onClick: (e, activeEls) => {
                    window.location.href = '<?= BASE_URL ?>/movement';
                },
                scales: {
                    x: { stacked: true },
                    y: { stacked: true, beginAtZero: true, ticks: { stepSize: 1 } }
                },
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    }

    // 4. FETCH IoT REAL-TIME ONSITE
    function fetchIotData() {
        fetch('<?= BASE_URL ?>/iot/realtime')
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success') {
                    const d = res.data;
                    document.getElementById('iotWidget').innerHTML = `
                        <div style="font-size: 24px; font-weight: 700; color: #10b981;">
                            ${d.present_now} <span style="font-size: 14px; color: #94a3b8; font-weight: normal;">/ ${d.total_workforce}</span>
                        </div>
                        <div style="font-size: 13px;">
                            <span class="badge" style="background: rgba(16, 185, 129, 0.2); color: #10b981;">Đang có mặt</span>
                            <span style="margin-left: 10px; color: #f43f5e;"><i class="fas fa-users-slash"></i> Vắng: ${d.absent}</span>
                        </div>
                    `;
                }
            })
            .catch(e => console.error(e));
    }
    
    fetchIotData();
    setInterval(fetchIotData, 15000); // 15s refresh
});
</script>
