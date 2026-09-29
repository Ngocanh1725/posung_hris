<?php
/**
 * View: evaluation/review_360.php – Phiếu Đánh Giá 360° Đa Chiều (Manager / Peer / Subordinate)
 */

$relBadges = [
    'Manager'     => ['Quản Lý Trực Tiếp (Manager)', 'badge bg-primary', 'fas fa-user-shield'],
    'Peer'        => ['Đồng Nghiệp Chéo (Peer)',     'badge bg-info text-white', 'fas fa-user-friends'],
    'Subordinate' => ['Cấp Dưới (Subordinate)',      'badge bg-secondary text-white', 'fas fa-users'],
    'Self'        => ['Bản Thân (Self)',             'badge bg-success', 'fas fa-user-check'],
];

$relInfo = $relBadges[$relationship] ?? ['Người Đánh Giá 360°', 'badge bg-primary', 'fas fa-user'];

// Parse điểm KRA đã chấm trước đó nếu có
$savedKraScores = [];
if (!empty($existingReview->kra_scores)) {
    $savedKraScores = is_string($existingReview->kra_scores) ? json_decode($existingReview->kra_scores, true) : $existingReview->kra_scores;
}
?>

<!-- BREADCRUMB -->
<div class="breadcrumb-nav mb-3 d-flex align-items-center gap-2" style="font-size: 13px; color: var(--text-muted);">
    <a href="<?= BASE_URL ?>/evaluation" class="text-decoration-none" style="color: var(--primary);">
        <i class="fas fa-award"></i> Quản lý Đánh giá KPI
    </a>
    <i class="fas fa-chevron-right" style="font-size: 10px;"></i>
    <a href="<?= BASE_URL ?>/evaluation/goals/<?= $period->id ?>" class="text-decoration-none" style="color: var(--primary);">
        Mục tiêu KRA: <?= htmlspecialchars($period->name) ?>
    </a>
    <i class="fas fa-chevron-right" style="font-size: 10px;"></i>
    <span>Đánh giá 360°: <?= htmlspecialchars($employee->full_name) ?></span>
</div>

<!-- HEADER CARD -->
<div class="card mb-4" style="border: 1px solid var(--border); box-shadow: 0 4px 18px rgba(0,0,0,0.04); border-radius: 12px;">
    <div class="card-body" style="padding: 24px;">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div class="d-flex gap-3 align-items-center">
                <div style="width: 56px; height: 56px; border-radius: 14px; background: linear-gradient(135deg, #f59e0b, #d97706); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 24px; font-weight: 700; box-shadow: 0 6px 16px rgba(245,158,11,0.3); flex-shrink: 0;">
                    <i class="fas fa-star-half-alt"></i>
                </div>
                <div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <h2 style="font-size: 20px; font-weight: 700; margin: 0; color: var(--text);">
                            Phiếu Đánh Giá Hiệu Suất 360 Độ
                        </h2>
                        <span class="<?= $relInfo[1] ?>" style="font-size: 12px; font-weight: 600; padding: 4px 10px; border-radius: 20px;">
                            <i class="<?= $relInfo[2] ?>"></i> Vai trò: <?= $relInfo[0] ?>
                        </span>
                    </div>
                    <div style="font-size: 13px; color: var(--text-muted); display: flex; gap: 16px; flex-wrap: wrap; margin-top: 6px;">
                        <span><i class="fas fa-user text-warning"></i> Nhân sự được đánh giá: <strong><?= htmlspecialchars($employee->full_name) ?></strong> (<?= htmlspecialchars($employee->employee_code) ?>)</span>
                        <span><i class="fas fa-id-badge text-warning"></i> Vị trí: <strong><?= htmlspecialchars($employee->pos_title ?? 'Nhân viên') ?></strong></span>
                        <span><i class="fas fa-building text-warning"></i> Phòng ban: <strong><?= htmlspecialchars($employee->dept_name ?? 'Chưa phân bổ') ?></strong></span>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2">
                <a href="<?= BASE_URL ?>/evaluation/goals/<?= $period->id ?>" class="btn btn-ghost" style="border: 1px solid var(--border);">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>
            </div>
        </div>

        <div class="mt-3 p-3 rounded" style="background: rgba(245, 158, 11, 0.06); border-left: 4px solid #f59e0b; font-size: 13px;">
            <i class="fas fa-info-circle text-warning me-1"></i>
            <strong>Quy tắc đánh giá 360°:</strong> Bạn đang đánh giá với tư cách là <strong><?= $relInfo[0] ?></strong>. Vui lòng đối chiếu giữa <strong>Chỉ tiêu cam kết</strong> và <strong>Kết quả thực tế nhân viên tự báo cáo</strong> để đưa ra điểm số khách quan, công tâm nhất.
        </div>
    </div>
</div>

<!-- FORM REVIEW 360 -->
<form action="<?= BASE_URL ?>/evaluation/review360/<?= $period->id ?>/<?= $employee->id ?>" method="POST" id="review360Form">
    <input type="hidden" name="_csrf_token" value="<?= Session::generateCsrfToken() ?>">
    <input type="hidden" name="relationship" value="<?= htmlspecialchars($relationship) ?>">

    <!-- PHẦN 1: ĐÁNH GIÁ CHI TIẾT TỪNG KRA -->
    <div class="card mb-4" style="border: 1px solid var(--border); box-shadow: 0 4px 18px rgba(0,0,0,0.04); border-radius: 12px;">
        <div class="card-header d-flex justify-content-between align-items-center" style="background: var(--bg-hover); padding: 14px 20px;">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-tasks text-warning"></i>
                <strong style="font-size: 14px; color: var(--text);">Phần I: Đánh Giá Chi Tiết Theo Từng Mục Tiêu KRA</strong>
            </div>
            <span class="badge bg-secondary"><?= count($goals) ?> Mục tiêu KRA</span>
        </div>

        <div class="card-body p-4">
            <?php if (empty($goals)): ?>
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-exclamation-triangle fa-2x text-warning mb-2"></i>
                    <div>Nhân viên này chưa thiết lập mục tiêu KRA cho chu kỳ.</div>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($goals as $idx => $g): ?>
                        <?php 
                            $savedScore = $savedKraScores[$g['id']] ?? ($g['manager_score'] ?? ($g['self_score'] ?? 80));
                        ?>
                        <div class="col-12">
                            <div class="p-3 rounded kra-review-box" style="border: 1px solid var(--border); background: #ffffff; box-shadow: 0 2px 8px rgba(0,0,0,0.02);">
                                <!-- KRA Header -->
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3 pb-2" style="border-bottom: 1px dashed var(--border);">
                                    <div>
                                        <span class="badge bg-dark me-2">KRA #<?= $idx + 1 ?></span>
                                        <strong style="font-size: 15px; color: var(--text);"><?= htmlspecialchars($g['kra_title']) ?></strong>
                                    </div>
                                    <span class="badge bg-info-subtle text-info fw-bold" style="font-size: 12px;">
                                        Trọng số: <?= (int)$g['weightage'] ?>%
                                    </span>
                                </div>

                                <div class="row g-3">
                                    <!-- Cột trái: Thông tin cam kết & NV tự báo cáo -->
                                    <div class="col-md-6" style="border-right: 1px solid var(--border);">
                                        <div class="mb-2" style="font-size: 13px;">
                                            <strong class="text-secondary"><i class="fas fa-bullseye me-1"></i> Chỉ tiêu đo lường:</strong>
                                            <div class="p-2 rounded mt-1" style="background: var(--bg-hover);">
                                                <?= htmlspecialchars($g['target_metric'] ?: 'Không có ghi chú cụ thể') ?>
                                            </div>
                                        </div>

                                        <div class="mb-2" style="font-size: 13px;">
                                            <strong class="text-success"><i class="fas fa-user-check me-1"></i> Kết quả thực tế nhân viên tự báo cáo:</strong>
                                            <div class="p-2 rounded mt-1 border-start border-3 border-success" style="background: rgba(16, 185, 129, 0.05); min-height: 48px;">
                                                <?= !empty($g['actual_achievement']) ? nl2br(htmlspecialchars($g['actual_achievement'])) : '<em class="text-muted">Nhân viên chưa nộp kết quả thực tế</em>' ?>
                                            </div>
                                        </div>

                                        <div class="d-flex align-items-center gap-2" style="font-size: 13px;">
                                            <span class="text-muted">Điểm nhân viên tự chấm:</span>
                                            <?php if ($g['self_score'] !== null): ?>
                                                <span class="badge bg-success-subtle text-success fw-bold" style="font-size: 13px;">
                                                    <?= (float)$g['self_score'] ?> điểm
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-light text-muted border">Chưa chấm</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <!-- Cột phải: Điểm chấm của Reviewer -->
                                    <div class="col-md-6 ps-md-4">
                                        <div class="p-3 rounded" style="background: rgba(245, 158, 11, 0.04); border: 1px solid rgba(245, 158, 11, 0.2);">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <label class="form-label mb-0 fw-bold" style="font-size: 13px; color: var(--text);">
                                                    <i class="fas fa-pen text-warning me-1"></i> Điểm bạn đánh giá cho KRA này:
                                                </label>
                                                <span class="badge bg-warning text-dark fw-bold" id="badgeKra_<?= $g['id'] ?>" style="font-size: 14px;">
                                                    <?= (float)$savedScore ?> điểm
                                                </span>
                                            </div>

                                            <div class="d-flex align-items-center gap-3">
                                                <input type="range" class="form-range kra-score-slider flex-grow-1" min="0" max="100" step="1" 
                                                       value="<?= (float)$savedScore ?>" 
                                                       data-weight="<?= (int)$g['weightage'] ?>"
                                                       data-target="inputKra_<?= $g['id'] ?>"
                                                       data-badge="badgeKra_<?= $g['id'] ?>"
                                                       oninput="syncSliderAndInput(this)">
                                                <input type="number" name="kra_scores[<?= $g['id'] ?>]" id="inputKra_<?= $g['id'] ?>" 
                                                       class="form-control form-control-sm text-center fw-bold kra-score-input" style="width: 80px;" 
                                                       min="0" max="100" value="<?= (float)$savedScore ?>"
                                                       data-weight="<?= (int)$g['weightage'] ?>"
                                                       data-badge="badgeKra_<?= $g['id'] ?>"
                                                       oninput="syncInputAndSlider(this)">
                                            </div>
                                            <div class="text-muted mt-2" style="font-size: 11px;">
                                                Thang điểm 0 - 100. Kéo thanh trượt hoặc nhập số trực tiếp để chấm điểm KRA này.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- PHẦN 2: ĐIỂM TỔNG THỂ, ĐIỂM MẠNH, ĐIỂM CẦN CẢI THIỆN & NHẬN XÉT -->
    <div class="card mb-4" style="border: 1px solid var(--border); box-shadow: 0 4px 18px rgba(0,0,0,0.04); border-radius: 12px;">
        <div class="card-header" style="background: var(--bg-hover); padding: 14px 20px;">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-clipboard-check text-warning"></i>
                <strong style="font-size: 14px; color: var(--text);">Phần II: Điểm Đánh Giá Tổng Thể & Nhận Xét Định Tính</strong>
            </div>
        </div>

        <div class="card-body p-4">
            <div class="row g-4">
                <!-- Điểm tổng thể -->
                <div class="col-12">
                    <div class="p-3 rounded d-flex justify-content-between align-items-center flex-wrap gap-3" style="background: rgba(245, 158, 11, 0.08); border: 1px solid rgba(245, 158, 11, 0.25);">
                        <div>
                            <div class="fw-bold text-dark" style="font-size: 16px;">
                                <i class="fas fa-calculator text-warning me-1"></i> Điểm Đánh Giá Tổng Thể 360° (Overall Score)
                            </div>
                            <small class="text-muted">Tự động tính theo trọng số từng KRA, người đánh giá có thể tinh chỉnh điểm tổng kết</small>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <input type="number" name="overall_score" id="overallScoreInput" step="0.1" min="0" max="100" 
                                   class="form-control form-control-lg text-center fw-bolder text-warning" style="width: 120px; font-size: 22px;" 
                                   value="<?= $existingReview ? (float)$existingReview->overall_score : 80 ?>">
                            <span class="fw-bold" style="font-size: 16px;">/ 100</span>
                        </div>
                    </div>
                </div>

                <!-- Điểm mạnh nổi bật -->
                <div class="col-md-6">
                    <label class="form-label" style="font-size: 13px; font-weight: 700; color: var(--text);">
                        <i class="fas fa-thumbs-up text-success me-1"></i> Điểm mạnh nổi bật của nhân viên (Strengths)
                    </label>
                    <textarea name="strengths" class="form-control" rows="4" 
                              placeholder="Khen ngợi tinh thần làm việc, thế mạnh chuyên môn, khả năng xử lý vấn đề tại công trường..."><?= htmlspecialchars($existingReview->strengths ?? '') ?></textarea>
                </div>

                <!-- Điểm cần cải thiện -->
                <div class="col-md-6">
                    <label class="form-label" style="font-size: 13px; font-weight: 700; color: var(--text);">
                        <i class="fas fa-tools text-warning me-1"></i> Điểm cần cải thiện & Gợi ý đào tạo (Improvements)
                    </label>
                    <textarea name="improvements" class="form-control" rows="4" 
                              placeholder="Những mặt còn hạn chế cần khắc phục, gợi ý các kỹ năng cần trau dồi thêm..."><?= htmlspecialchars($existingReview->improvements ?? '') ?></textarea>
                </div>

                <!-- Nhận xét chi tiết -->
                <div class="col-12">
                    <label class="form-label" style="font-size: 13px; font-weight: 700; color: var(--text);">
                        <i class="fas fa-comment-dots text-primary me-1"></i> Nhận xét & Đóng góp ý kiến (Comments)
                    </label>
                    <textarea name="comments" class="form-control" rows="3" 
                              placeholder="Nhận xét tổng quan về tác phong, sự phối hợp đội nhóm và tiềm năng phát triển của nhân sự..."><?= htmlspecialchars($existingReview->comments ?? '') ?></textarea>
                </div>
            </div>
        </div>
    </div>

    <!-- BUTTONS -->
    <div class="d-flex justify-content-between align-items-center mb-5">
        <a href="<?= BASE_URL ?>/evaluation/goals/<?= $period->id ?>" class="btn btn-ghost" style="border: 1px solid var(--border);">
            <i class="fas fa-times me-1"></i> Hủy bỏ
        </a>
        <button type="submit" class="btn btn-warning btn-lg px-4 text-dark fw-bold" style="box-shadow: 0 4px 14px rgba(245,158,11,0.3);">
            <i class="fas fa-paper-plane me-2"></i> Lưu & Nộp Đánh Giá 360°
        </button>
    </div>
</form>

<!-- JAVASCRIPT SLIDERS & WEIGHTED AVERAGE -->
<script>
function syncSliderAndInput(slider) {
    const targetInputId = slider.dataset.target;
    const badgeId = slider.dataset.badge;
    const val = slider.value;

    document.getElementById(targetInputId).value = val;
    document.getElementById(badgeId).innerText = val + ' điểm';
    calculateWeightedOverall();
}

function syncInputAndSlider(input) {
    let val = parseFloat(input.value) || 0;
    if (val < 0) val = 0;
    if (val > 100) val = 100;
    input.value = val;

    const row = input.closest('.kra-review-box');
    const slider = row.querySelector('.kra-score-slider');
    const badgeId = input.dataset.badge;

    if (slider) slider.value = val;
    if (badgeId) document.getElementById(badgeId).innerText = val + ' điểm';

    calculateWeightedOverall();
}

function calculateWeightedOverall() {
    const inputs = document.querySelectorAll('.kra-score-input');
    let weightedSum = 0;
    let totalWeight = 0;

    inputs.forEach(inp => {
        const score = parseFloat(inp.value) || 0;
        const weight = parseFloat(inp.dataset.weight) || 0;
        weightedSum += (score * (weight / 100));
        totalWeight += weight;
    });

    if (totalWeight > 0) {
        const finalCalculated = Math.round(weightedSum * 10) / 10;
        document.getElementById('overallScoreInput').value = finalCalculated;
    }
}

document.addEventListener('DOMContentLoaded', function() {
    calculateWeightedOverall();
});
</script>
