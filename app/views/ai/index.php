<?php /** View: ai/index.php – Dashboard Hệ Chuyên gia (Expert System) */ ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div style="margin-bottom:20px; display:flex; justify-content:space-between; align-items:center;">
    <div>
        <h2 style="margin:0; font-size:1.5rem; color:var(--text-heading);"><i class="fas fa-brain" style="color:var(--primary);"></i> Phân tích Chuyên sâu (HR Expert System)</h2>
        <p style="margin:5px 0 0; color:var(--text-muted);">Hệ thống AI phân tích kỹ năng, dự báo nhân sự và cảnh báo rủi ro biến động.</p>
    </div>
    <div style="text-align:right;">
        <span class="badge badge-active" style="font-size:0.9rem; padding:8px 12px;"><i class="fas fa-sync fa-spin"></i> Lần chạy cuối: <?= date('d/m/Y H:i', strtotime($analysis['last_run'])) ?></span>
    </div>
</div>

<div class="row">
    <!-- Khối 1: Biểu đồ Radar (Khoảng trống kỹ năng) -->
    <div class="col-md-6 mb-4">
        <div class="panel h-100">
            <div class="panel-header">
                <h3 style="margin:0;"><i class="fas fa-chart-pie text-primary"></i> Ma trận Năng lực (Skill Gap)</h3>
            </div>
            <div class="panel-body text-center">
                <div style="height: 300px; width: 100%; display:flex; justify-content:center;">
                    <canvas id="skillRadarChart"></canvas>
                </div>
                <div class="mt-3 text-left">
                    <h5 class="text-danger border-bottom pb-2">Đề xuất Đào tạo (Skill Match < 75%)</h5>
                    <?php if(empty($analysis['skill_gaps']['individual_gaps'])): ?>
                        <p class="text-success"><i class="fas fa-check-circle"></i> Đội ngũ kỹ thuật đáp ứng 100% chứng chỉ cốt lõi.</p>
                    <?php else: ?>
                        <ul style="font-size: 0.9rem; padding-left: 20px;">
                        <?php foreach($analysis['skill_gaps']['individual_gaps'] as $gap): ?>
                            <li>
                                <strong><?= h($gap['full_name']) ?> (<?= h($gap['position']) ?>) - Đạt <?= $gap['match_index'] ?>%</strong>
                                <ul>
                                <?php foreach($gap['recommendations'] as $rec): ?>
                                    <li class="text-danger"><?= h($rec) ?></li>
                                <?php endforeach; ?>
                                </ul>
                            </li>
                        <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Khối 2: Biểu đồ Cột (Dự báo Nhân sự) -->
    <div class="col-md-6 mb-4">
        <div class="panel h-100">
            <div class="panel-header">
                <h3 style="margin:0;"><i class="fas fa-chart-bar text-success"></i> Dự báo Nhân sự Dự án (60 Ngày)</h3>
            </div>
            <div class="panel-body">
                <div style="height: 300px; width: 100%;">
                    <canvas id="staffingChart"></canvas>
                </div>
                <div class="mt-3">
                    <?php if(empty($analysis['staffing_preds'])): ?>
                        <p class="text-success"><i class="fas fa-check-circle"></i> Không có dự án nào thiếu hụt nhân sự trong 60 ngày tới.</p>
                    <?php else: ?>
                        <?php foreach($analysis['staffing_preds'] as $pred): ?>
                            <div class="alert alert-warning p-2 mb-2" style="font-size: 0.85rem;">
                                <strong><?= h($pred['project_name']) ?>:</strong> Thiếu <?= $pred['shortage'] ?> nhân sự. 
                                <?= h($pred['recommendation']) ?>
                                <a href="<?= BASE_URL ?>/recruitment/create?project_id=<?= $pred['project_id'] ?>&ai_shortage=<?= $pred['shortage'] ?>" class="btn btn-sm btn-primary float-right" style="padding: 2px 8px; font-size: 0.8rem;">
                                    Tạo YCTD
                                </a>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Khối 3: Cảnh báo Rủi ro Nghỉ việc (Flight Risk) -->
<div class="panel mb-4">
    <div class="panel-header" style="display:flex; justify-content:space-between; align-items:center;">
        <h3 style="margin:0;"><i class="fas fa-user-slash text-danger"></i> Cảnh báo Biến động Nhân sự (Flight Risk)</h3>
        <span class="badge" style="background:var(--danger); color:#fff;"><?= count($analysis['flight_risks']) ?> cảnh báo</span>
    </div>
    <div class="panel-body p-0">
        <?php if (empty($analysis['flight_risks'])): ?>
            <div style="padding:24px; text-align:center; color:var(--text-muted);"><i class="fas fa-shield-alt" style="font-size:2rem; color:var(--success); margin-bottom:10px; display:block;"></i> Tỷ lệ rủi ro thấp, nhân sự ổn định.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered mb-0">
                    <thead style="background: #f8f9fa;">
                        <tr>
                            <th>Mã NV</th>
                            <th>Họ tên</th>
                            <th>Vị trí</th>
                            <th>Nguyên nhân (AI Phát hiện)</th>
                            <th class="text-center">Mức độ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($analysis['flight_risks'] as $risk): ?>
                        <tr>
                            <td><?= h($risk['emp_code']) ?></td>
                            <td><strong><?= h($risk['full_name']) ?></strong></td>
                            <td><?= h($risk['position']) ?></td>
                            <td class="text-danger"><?= h($risk['reason']) ?></td>
                            <td class="text-center">
                                <?php if($risk['level'] === 'Critical'): ?>
                                    <span class="badge bg-danger text-white">Nghiêm Trọng</span>
                                <?php elseif($risk['level'] === 'High'): ?>
                                    <span class="badge bg-warning text-dark">Cao</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary text-white">Trung Bình</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Kịch bản JS cho Biểu đồ -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    // Dữ liệu cho Radar Chart
    <?php
        $radarLabels = json_encode($analysis['skill_gaps']['radar_data']['labels'] ?? []);
        $radarData = json_encode($analysis['skill_gaps']['radar_data']['data'] ?? []);
    ?>
    var ctxRadar = document.getElementById('skillRadarChart').getContext('2d');
    new Chart(ctxRadar, {
        type: 'radar',
        data: {
            labels: <?= $radarLabels ?>,
            datasets: [{
                label: 'Chỉ số Phù hợp Năng lực (%)',
                data: <?= $radarData ?>,
                backgroundColor: 'rgba(59, 130, 246, 0.2)',
                borderColor: 'rgba(59, 130, 246, 1)',
                pointBackgroundColor: 'rgba(59, 130, 246, 1)',
                pointBorderColor: '#fff',
                pointHoverBackgroundColor: '#fff',
                pointHoverBorderColor: 'rgba(59, 130, 246, 1)'
            }]
        },
        options: {
            scales: {
                r: {
                    angleLines: { display: false },
                    suggestedMin: 0,
                    suggestedMax: 100
                }
            },
            maintainAspectRatio: false
        }
    });

    // Dữ liệu cho Bar Chart (Staffing)
    <?php
        $barLabels = [];
        $barRequired = [];
        $barAvailable = [];
        foreach ($analysis['staffing_preds'] as $pred) {
            $barLabels[] = $pred['project_name'];
            $barRequired[] = $pred['required'];
            $barAvailable[] = $pred['available'];
        }
    ?>
    var ctxBar = document.getElementById('staffingChart').getContext('2d');
    new Chart(ctxBar, {
        type: 'bar',
        data: {
            labels: <?= json_encode($barLabels) ?>,
            datasets: [
                {
                    label: 'Định biên Yêu cầu',
                    data: <?= json_encode($barRequired) ?>,
                    backgroundColor: 'rgba(14, 165, 233, 0.7)'
                },
                {
                    label: 'Thực tế (Sẵn sàng)',
                    data: <?= json_encode($barAvailable) ?>,
                    backgroundColor: 'rgba(16, 185, 129, 0.7)'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
});
</script>

<style>
.text-primary { color: var(--primary); }
.text-success { color: var(--success); }
.text-warning { color: var(--warning); }
.text-danger { color: var(--danger); }
.badge-warning { background: rgba(245, 158, 11, 0.15); color: #d97706; }
</style>
