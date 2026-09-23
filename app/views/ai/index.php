<?php
/**
 * ============================================================
 *  View: ai/index.php
 *  Trung tâm Phân tích Trí tuệ Nhân tạo (AI Command Center)
 * ============================================================
 */
?>

<div class="panel mb-4" style="border: 1px solid var(--primary); border-top: 4px solid var(--primary);">
    <div class="panel-header" style="background: rgba(15, 23, 42, 0.03); display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h2 style="color: var(--primary); margin: 0;"><i class="fas fa-brain"></i> AI Command Center</h2>
            <small style="color: var(--text-muted);">Cập nhật lần cuối: <?= h($analysis['last_run']) ?></small>
        </div>
        <div>
            <button class="btn btn-primary" onclick="refreshInsights()"><i class="fas fa-sync-alt"></i> Phân tích lại (Real-time)</button>
        </div>
    </div>
</div>

<div class="row">
    <!-- KHỐI 1: Dự báo Kỹ năng & Đào tạo (Skill Gaps & Training Needs) -->
    <div class="col-md-6 mb-4">
        <div class="panel h-100 border-info">
            <div class="panel-header bg-info text-white">
                <h3 style="margin: 0; color: #fff;"><i class="fas fa-graduation-cap"></i> Dự báo Nhu cầu Đào tạo & Kỹ năng</h3>
            </div>
            <div class="panel-body">
                <?php if(empty($analysis['project_skill_gaps']) && empty($analysis['training_need'])): ?>
                    <div class="alert alert-success">Hệ thống kỹ năng ổn định. Không có cảnh báo khẩn cấp.</div>
                <?php else: ?>
                    <ul style="list-style: none; padding: 0;">
                        <?php foreach($analysis['project_skill_gaps'] as $gap): ?>
                            <li style="margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px dashed #e2e8f0;">
                                <div style="display: flex; gap: 10px; align-items: flex-start;">
                                    <i class="fas fa-exclamation-triangle" style="color: var(--warning); margin-top: 4px;"></i>
                                    <div>
                                        <strong>Dự án: <?= h($gap['project']) ?></strong>
                                        <div style="font-size: 0.9rem; color: var(--text-secondary); margin: 5px 0;">
                                            <?= h($gap['issue']) ?>
                                        </div>
                                        <div style="font-size: 0.9rem; color: var(--success); font-weight: 600;">
                                            <i class="fas fa-lightbulb"></i> Khuyến nghị: <?= h($gap['recommendation']) ?>
                                        </div>
                                        <button class="btn btn-sm btn-ghost mt-2" style="color: var(--primary); font-size: 0.8rem;" onclick="applyAiRecommendation('CreateRecruitment', '<?= h($gap['project']) ?>')">
                                            <i class="fas fa-magic"></i> Áp dụng Khuyến nghị AI
                                        </button>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>

                        <?php foreach($analysis['training_need'] as $train): ?>
                            <li style="margin-bottom: 15px;">
                                <div style="display: flex; gap: 10px; align-items: flex-start;">
                                    <i class="fas fa-chalkboard-teacher" style="color: var(--info); margin-top: 4px;"></i>
                                    <div>
                                        <strong><?= h($train['training_type']) ?></strong>
                                        <div style="font-size: 0.9rem; color: var(--text-secondary); margin: 5px 0;">
                                            <?= h($train['recommendation']) ?>
                                        </div>
                                        <button class="btn btn-sm btn-ghost mt-2" style="color: var(--primary); font-size: 0.8rem;">
                                            <i class="fas fa-magic"></i> Tự động lập danh sách tham gia
                                        </button>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- KHỐI 2: Cảnh báo Kỹ sư có Nguy cơ Nghỉ việc Cao (Attrition / Flight Risk) -->
    <div class="col-md-6 mb-4">
        <div class="panel h-100 border-danger">
            <div class="panel-header bg-danger text-white">
                <h3 style="margin: 0; color: #fff;"><i class="fas fa-user-injured"></i> Cảnh báo Nguy cơ Nghỉ việc (Key Engineers)</h3>
            </div>
            <div class="panel-body">
                <?php if(empty($analysis['attrition_risk'])): ?>
                    <div class="alert alert-success">Không phát hiện rủi ro nghỉ việc cao trong khối kỹ sư chủ chốt.</div>
                <?php else: ?>
                    <div class="table-wrapper">
                        <table style="font-size: 0.9rem;">
                            <thead>
                                <tr>
                                    <th>Kỹ sư (Senior/Lead)</th>
                                    <th>Mức rủi ro</th>
                                    <th>Phân tích AI</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($analysis['attrition_risk'] as $risk): ?>
                                    <?php if($risk['level'] !== 'Thấp'): ?>
                                    <tr>
                                        <td>
                                            <strong><?= h($risk['full_name']) ?></strong><br>
                                            <small><?= h($risk['position']) ?></small>
                                        </td>
                                        <td>
                                            <?php if($risk['level'] === 'Cao'): ?>
                                                <span class="badge badge-resigned" style="box-shadow: 0 0 5px red;"><i class="fas fa-fire"></i> Cao (<?= $risk['score'] ?>đ)</span>
                                            <?php else: ?>
                                                <span class="badge badge-probation">Trung bình (<?= $risk['score'] ?>đ)</span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="color: var(--text-secondary);">
                                            <?= h($risk['reasons']) ?>
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- KHỐI 3: Cảnh báo An toàn HSE Blacklist (Hệ thống) -->
    <div class="col-md-6 mb-4">
        <div class="panel h-100 border-dark">
            <div class="panel-header bg-dark text-white">
                <h3 style="margin: 0; color: #fff;"><i class="fas fa-skull-crossbones"></i> Cảnh báo An toàn Hệ thống</h3>
            </div>
            <div class="panel-body">
                <?php if(empty($analysis['safety_alerts'])): ?>
                    <div class="alert alert-success">Hệ thống tuân thủ an toàn tốt.</div>
                <?php else: ?>
                    <ul style="list-style: none; padding: 0;">
                        <?php foreach($analysis['safety_alerts'] as $alert): ?>
                            <li style="margin-bottom: 15px; padding: 10px; background: rgba(239, 68, 68, 0.1); border-left: 4px solid var(--danger); border-radius: 4px;">
                                <div style="font-weight: 600; color: var(--danger); margin-bottom: 5px;">
                                    <?= h($alert['message']) ?>
                                </div>
                                <div style="font-size: 0.85rem;">
                                    <strong>Hành động:</strong> <?= h($alert['action']) ?>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- KHỐI 4: Dự báo Ngân sách Lương / Nhu cầu Tuyển dụng (Staffing & Budget) -->
    <div class="col-md-6 mb-4">
        <div class="panel h-100 border-success">
            <div class="panel-header bg-success text-white">
                <h3 style="margin: 0; color: #fff;"><i class="fas fa-chart-line"></i> Dự báo Tuyển dụng & Ngân sách</h3>
            </div>
            <div class="panel-body">
                <?php if(empty($analysis['staffing_preds'])): ?>
                    <div class="alert alert-success">Nhân lực định biên đang đáp ứng đủ cho các dự án.</div>
                <?php else: ?>
                    <ul style="list-style: none; padding: 0;">
                        <?php foreach($analysis['staffing_preds'] as $pred): ?>
                            <li style="margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px dashed #e2e8f0;">
                                <div style="display: flex; gap: 10px; align-items: flex-start;">
                                    <i class="fas fa-users-cog" style="color: var(--success); margin-top: 4px;"></i>
                                    <div>
                                        <strong>Dự án: <?= h($pred['project_name']) ?></strong>
                                        <div style="font-size: 0.9rem; color: var(--text-secondary); margin: 5px 0;">
                                            Yêu cầu: <?= $pred['required'] ?> | Hiện có: <?= $pred['available'] ?> | <strong>Thiếu: <?= $pred['shortage'] ?></strong>
                                        </div>
                                        <div style="font-size: 0.9rem; color: var(--primary); font-weight: 600;">
                                            <i class="fas fa-robot"></i> AI: <?= h($pred['recommendation']) ?>
                                        </div>
                                        <button class="btn btn-sm btn-ghost mt-2" style="color: var(--success); font-size: 0.8rem;" onclick="applyAiRecommendation('CreateRecruitment', '<?= h($pred['project_name']) ?>')">
                                            <i class="fas fa-magic"></i> Tạo Phiếu YCTD Tự Động
                                        </button>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.border-info { border: 1px solid var(--info); }
.bg-info { background-color: var(--info); }
.border-danger { border: 1px solid var(--danger); }
.bg-danger { background-color: var(--danger); }
.border-dark { border: 1px solid #1e293b; }
.bg-dark { background-color: #1e293b; }
.border-success { border: 1px solid var(--success); }
.bg-success { background-color: var(--success); }
.badge-probation { background: rgba(245, 158, 11, 0.15); color: #d97706; }
.badge-resigned { background: rgba(239, 68, 68, 0.15); color: #b91c1c; }
</style>

<script>
function applyAiRecommendation(action, target) {
    if (confirm("Xác nhận Hệ thống tự động thiết lập hành động: " + action + " cho " + target + "?")) {
        // Mock AJAX call
        alert("Đã tự động tạo Phiếu Yêu cầu Tuyển dụng / Danh sách đào tạo thành công!");
        window.location.href = "<?= BASE_URL ?>/recruitment";
    }
}

function refreshInsights() {
    // Demo real-time API
    fetch("<?= BASE_URL ?>/ai/apiGetInsights")
        .then(res => res.json())
        .then(data => {
            if(data.status === 'success') {
                alert("Dữ liệu phân tích đã được cập nhật time-real!");
                window.location.reload();
            }
        });
}
</script>
