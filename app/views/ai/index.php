<?php /** View: ai/index.php – Dashboard Hệ Chuyên gia (Expert System) */ ?>

<div style="margin-bottom:20px; display:flex; justify-content:space-between; align-items:center;">
    <div>
        <h2 style="margin:0; font-size:1.5rem; color:var(--text-heading);"><i class="fas fa-brain" style="color:var(--primary);"></i> Phân tích Chuyên sâu (HR Expert System)</h2>
        <p style="margin:5px 0 0; color:var(--text-muted);">Hệ thống rule-based phân tích dữ liệu thực tế và đưa ra các đề xuất quản trị nhân sự cho Po Sung.</p>
    </div>
    <div style="text-align:right;">
        <span class="badge badge-active" style="font-size:0.9rem; padding:8px 12px;"><i class="fas fa-sync fa-spin"></i> Lần chạy cuối: <?= date('d/m/Y H:i', strtotime($analysis['last_run'])) ?></span>
    </div>
</div>

<!-- 1. Cảnh báo HSE (Critical) -->
<?php if (!empty($analysis['safety_alerts'])): ?>
<div class="panel" style="border-left:4px solid var(--danger); margin-bottom:24px;">
    <div class="panel-header" style="background:rgba(239,68,68,0.05); border-bottom:1px solid rgba(239,68,68,0.2);">
        <h3 style="color:var(--danger); margin:0;"><i class="fas fa-skull-crossbones"></i> CẢNH BÁO AN TOÀN (BLACKLIST)</h3>
    </div>
    <div class="panel-body">
        <?php foreach($analysis['safety_alerts'] as $alert): ?>
            <div style="padding:12px; margin-bottom:12px; background:rgba(239,68,68,0.1); border-radius:8px; border:1px solid rgba(239,68,68,0.2);">
                <p style="margin:0 0 8px; font-weight:bold; color:var(--danger);"><i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($alert['message']) ?></p>
                <p style="margin:0; font-size:0.9rem;"><strong>Hành động bắt buộc:</strong> <?= htmlspecialchars($alert['action']) ?></p>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- 2. Phân tích Nhu cầu Đào tạo -->
<div class="panel mb-4">
    <div class="panel-header" style="display:flex; justify-content:space-between; align-items:center;">
        <h3 style="margin:0;"><i class="fas fa-graduation-cap text-primary"></i> Phân tích Nhu cầu Đào tạo & Phát triển</h3>
        <span class="badge" style="background:var(--primary); color:#fff;"><?= count($analysis['training_need']) ?> đề xuất</span>
    </div>
    <div class="panel-body p-0">
        <?php if (empty($analysis['training_need'])): ?>
            <div style="padding:24px; text-align:center; color:var(--text-muted);"><i class="fas fa-check-circle" style="font-size:2rem; color:var(--success); margin-bottom:10px; display:block;"></i> Không phát hiện nhu cầu đào tạo khẩn cấp.</div>
        <?php else: ?>
            <div class="table-wrapper">
                <table>
                    <thead><tr><th>Đối tượng (SL)</th><th>Loại Đào tạo</th><th>Độ ưu tiên</th><th>Đề xuất của Hệ thống</th></tr></thead>
                    <tbody>
                        <?php foreach($analysis['training_need'] as $nd): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($nd['target_group']) ?></strong><br><span class="badge badge-muted"><?= $nd['count'] ?> người</span></td>
                            <td style="font-weight:600;"><?= htmlspecialchars($nd['training_type']) ?></td>
                            <td>
                                <?php if($nd['priority']==='Critical'): ?><span class="badge" style="background:var(--danger);color:#fff;">Rất Cấp bách</span>
                                <?php else: ?><span class="badge badge-active">Cao</span><?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($nd['recommendation']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- 3. Rủi ro Nghỉ việc -->
<div class="panel mb-4">
    <div class="panel-header" style="display:flex; justify-content:space-between; align-items:center;">
        <h3 style="margin:0;"><i class="fas fa-user-slash text-warning"></i> Dự báo Rủi ro Nghỉ việc (Turnover Risk)</h3>
        <span class="badge" style="background:var(--warning); color:#fff;"><?= count($analysis['turnover_risk']) ?> nhân sự rủi ro</span>
    </div>
    <div class="panel-body p-0">
        <?php if (empty($analysis['turnover_risk'])): ?>
            <div style="padding:24px; text-align:center; color:var(--text-muted);"><i class="fas fa-shield-alt" style="font-size:2rem; color:var(--success); margin-bottom:10px; display:block;"></i> Tỷ lệ rủi ro thấp, nhân sự ổn định.</div>
        <?php else: ?>
            <div class="table-wrapper">
                <table>
                    <thead><tr><th>Mã NV</th><th>Họ tên</th><th>Mức độ Rủi ro</th><th>Lý do (Dấu hiệu)</th><th>Gợi ý Xử lý</th></tr></thead>
                    <tbody>
                        <?php foreach($analysis['turnover_risk'] as $risk): ?>
                        <tr>
                            <td><?= htmlspecialchars($risk['emp_code']) ?></td>
                            <td><strong><?= htmlspecialchars($risk['full_name']) ?></strong></td>
                            <td style="text-align:center;">
                                <?php if($risk['risk_level']==='Critical'): ?><span class="badge" style="background:var(--danger);color:#fff;">Cao</span>
                                <?php elseif($risk['risk_level']==='High'): ?><span class="badge badge-warning">Trung Bình-Cao</span>
                                <?php else: ?><span class="badge badge-probation">Trung Bình</span><?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($risk['reason']) ?></td>
                            <td><small><i class="fas fa-lightbulb text-warning"></i> <?= htmlspecialchars($risk['recommendation']) ?></small></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- 4. Nhu cầu Tuyển dụng -->
<div class="panel mb-4">
    <div class="panel-header" style="display:flex; justify-content:space-between; align-items:center;">
        <h3 style="margin:0;"><i class="fas fa-user-plus text-success"></i> Đánh giá Nhu cầu Tuyển dụng</h3>
        <span class="badge" style="background:var(--success); color:#fff;"><?= count($analysis['recruitment_need']) ?> cảnh báo</span>
    </div>
    <div class="panel-body p-0">
        <?php if (empty($analysis['recruitment_need'])): ?>
            <div style="padding:24px; text-align:center; color:var(--text-muted);"><i class="fas fa-thumbs-up" style="font-size:2rem; color:var(--success); margin-bottom:10px; display:block;"></i> Tiến độ tuyển dụng đang đáp ứng đủ nhu cầu dự án.</div>
        <?php else: ?>
            <div class="table-wrapper">
                <table>
                    <thead><tr><th>Phòng ban</th><th>Vị trí Cần tuyển</th><th>Mức độ</th><th>Phân tích Thực trạng</th><th>Đề xuất Cải thiện</th></tr></thead>
                    <tbody>
                        <?php foreach($analysis['recruitment_need'] as $rn): ?>
                        <tr>
                            <td><?= htmlspecialchars($rn['department']) ?></td>
                            <td style="font-weight:600;"><?= htmlspecialchars($rn['position']) ?></td>
                            <td style="text-align:center;">
                                <?php if($rn['urgency']==='Critical'): ?><span class="badge" style="background:var(--danger);color:#fff;">Quá Hạn/Khẩn</span>
                                <?php else: ?><span class="badge badge-active">Cần Gấp</span><?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($rn['analysis']) ?></td>
                            <td><small><i class="fas fa-lightbulb text-warning"></i> <?= htmlspecialchars($rn['recommendation']) ?></small></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.text-primary { color: var(--primary); }
.text-success { color: var(--success); }
.text-warning { color: var(--warning); }
.text-danger { color: var(--danger); }
.badge-warning { background: rgba(245, 158, 11, 0.15); color: #d97706; }
</style>
