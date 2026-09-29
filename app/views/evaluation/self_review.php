<?php
/**
 * View: evaluation/self_review.php – Phiếu Nhân viên Tự Đánh Giá 360° (Self Review)
 */
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
    <span>Tự Đánh Giá (Self Review): <?= htmlspecialchars($employee->full_name) ?></span>
</div>

<!-- HEADER INFO CARD -->
<div class="card mb-4" style="border: 1px solid var(--border); box-shadow: 0 4px 18px rgba(0,0,0,0.04); border-radius: 12px;">
    <div class="card-body" style="padding: 24px;">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div class="d-flex gap-3 align-items-center">
                <div style="width: 56px; height: 56px; border-radius: 14px; background: linear-gradient(135deg, #10b981, #059669); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 24px; font-weight: 700; box-shadow: 0 6px 16px rgba(16,185,129,0.3); flex-shrink: 0;">
                    <i class="fas fa-user-check"></i>
                </div>
                <div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <h2 style="font-size: 20px; font-weight: 700; margin: 0; color: var(--text);">
                            Phiếu Tự Đánh Giá Hiệu Suất Cá Nhân (Self Review)
                        </h2>
                        <span class="badge bg-success-subtle text-success" style="font-size: 12px; font-weight: 600;">
                            Chu kỳ: <?= htmlspecialchars($period->name) ?>
                        </span>
                    </div>
                    <div style="font-size: 13px; color: var(--text-muted); display: flex; gap: 16px; flex-wrap: wrap; margin-top: 6px;">
                        <span><i class="fas fa-user text-success"></i> Họ tên: <strong><?= htmlspecialchars($employee->full_name) ?></strong> (<?= htmlspecialchars($employee->employee_code) ?>)</span>
                        <span><i class="fas fa-id-badge text-success"></i> Vị trí: <strong><?= htmlspecialchars($employee->pos_title ?? 'Nhân viên') ?></strong></span>
                        <span><i class="fas fa-building text-success"></i> Bộ phận: <strong><?= htmlspecialchars($employee->dept_name ?? 'Chưa phân bổ') ?></strong></span>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2">
                <a href="<?= BASE_URL ?>/evaluation/goals/<?= $period->id ?>" class="btn btn-ghost" style="border: 1px solid var(--border);">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>
            </div>
        </div>

        <div class="mt-3 p-3 rounded" style="background: rgba(16, 185, 129, 0.05); border-left: 4px solid #10b981; font-size: 13px;">
            <i class="fas fa-info-circle text-success me-1"></i>
            <strong>Hướng dẫn tự đánh giá:</strong> Vui lòng điền chi tiết <strong>Kết quả thực tế đạt được</strong> cho từng mục tiêu KRA và trung thực tự chấm điểm số tương ứng (thang điểm 0 - 100). Sau khi hoàn tất, kết quả sẽ được chuyển tới Quản lý trực tiếp để phục vụ đối chiếu và chốt điểm 360°.
        </div>
    </div>
</div>

<!-- SELF REVIEW FORM -->
<form action="<?= BASE_URL ?>/evaluation/submitSelfReview/<?= $period->id ?>/<?= $employee->id ?>" method="POST" id="selfReviewForm">
    <input type="hidden" name="_csrf_token" value="<?= Session::generateCsrfToken() ?>">

    <!-- PHẦN 1: ĐÁNH GIÁ TỪNG MỤC TIÊU KRA -->
    <div class="card mb-4" style="border: 1px solid var(--border); box-shadow: 0 4px 18px rgba(0,0,0,0.04); border-radius: 12px;">
        <div class="card-header d-flex justify-content-between align-items-center" style="background: var(--bg-hover); padding: 14px 20px;">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-tasks text-success"></i>
                <strong style="font-size: 14px; color: var(--text);">Phần I: Đánh Giá Kết Quả Thực Tế Trên Từng Mục Tiêu KRA</strong>
            </div>
            <span class="badge bg-primary"><?= count($goals) ?> Mục tiêu KRA (Tổng 100%)</span>
        </div>

        <div class="card-body p-4">
            <div class="row g-4">
                <?php foreach ($goals as $idx => $g): ?>
                    <div class="col-12">
                        <div class="kra-card p-3 rounded" style="border: 1px solid var(--border); background: #ffffff; box-shadow: 0 2px 8px rgba(0,0,0,0.02);">
                            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2 pb-2" style="border-bottom: 1px dashed var(--border);">
                                <div>
                                    <span class="badge bg-secondary me-2">KRA #<?= $idx + 1 ?></span>
                                    <strong style="font-size: 15px; color: var(--text);"><?= htmlspecialchars($g['kra_title']) ?></strong>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-info-subtle text-info fw-bold" style="font-size: 12px;">
                                        Trọng số: <?= (int)$g['weightage'] ?>%
                                    </span>
                                </div>
                            </div>

                            <div class="row g-3">
                                <!-- Chi tiết KRA & Chỉ tiêu cam kết -->
                                <div class="col-md-6">
                                    <?php if (!empty($g['description'])): ?>
                                        <div class="mb-2" style="font-size: 13px; color: var(--text-muted);">
                                            <strong>Mô tả:</strong> <?= nl2br(htmlspecialchars($g['description'])) ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="p-2 rounded" style="background: var(--bg-hover); font-size: 13px;">
                                        <i class="fas fa-flag-checkered text-primary me-1"></i>
                                        <strong>Chỉ tiêu cần đạt (Target):</strong>
                                        <span class="text-dark fw-semibold"><?= htmlspecialchars($g['target_metric'] ?? 'Chưa xác định') ?></span>
                                    </div>
                                </div>

                                <!-- Kết quả thực tế & Điểm tự chấm -->
                                <div class="col-md-6">
                                    <!-- Kết quả thực tế đạt được -->
                                    <div class="mb-3">
                                        <label class="form-label" style="font-size: 12px; font-weight: 700; color: var(--text);">
                                            Kết quả thực tế đạt được (Actual Achievement) <span class="text-danger">*</span>
                                        </label>
                                        <textarea name="actual_achievement[<?= $g['id'] ?>]" class="form-control form-control-sm" rows="3" required
                                                  placeholder="Báo cáo số liệu thực tế, phần trăm hoàn thành, hồ sơ nghiệm thu hoặc các chứng chỉ đã đạt..."><?= htmlspecialchars($g['actual_achievement'] ?? '') ?></textarea>
                                    </div>

                                    <!-- Điểm tự chấm -->
                                    <div>
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <label class="form-label mb-0" style="font-size: 12px; font-weight: 700; color: var(--text);">
                                                Điểm tự chấm (Thang 0 - 100) <span class="text-danger">*</span>
                                            </label>
                                            <span class="badge bg-success" id="badgeScore_<?= $g['id'] ?>" style="font-size: 13px;">
                                                <?= $g['self_score'] !== null ? (float)$g['self_score'] : 85 ?> điểm
                                            </span>
                                        </div>
                                        <div class="d-flex align-items-center gap-3">
                                            <input type="range" class="form-range kra-score-slider flex-grow-1" min="0" max="100" step="1" 
                                                   value="<?= $g['self_score'] !== null ? (float)$g['self_score'] : 85 ?>" 
                                                   data-weight="<?= (int)$g['weightage'] ?>"
                                                   data-target="inputScore_<?= $g['id'] ?>"
                                                   data-badge="badgeScore_<?= $g['id'] ?>"
                                                   oninput="syncSliderAndInput(this)">
                                            <input type="number" name="self_score[<?= $g['id'] ?>]" id="inputScore_<?= $g['id'] ?>" 
                                                   class="form-control form-control-sm text-center fw-bold kra-score-input" style="width: 80px;" 
                                                   min="0" max="100" value="<?= $g['self_score'] !== null ? (float)$g['self_score'] : 85 ?>"
                                                   data-weight="<?= (int)$g['weightage'] ?>"
                                                   data-slider=""
                                                   data-badge="badgeScore_<?= $g['id'] ?>"
                                                   oninput="syncInputAndSlider(this)">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- PHẦN 2: TỔNG KẾT, ĐIỂM MẠNH, ĐIỂM CẦN CẢI THIỆN & Ý KIẾN -->
    <div class="card mb-4" style="border: 1px solid var(--border); box-shadow: 0 4px 18px rgba(0,0,0,0.04); border-radius: 12px;">
        <div class="card-header" style="background: var(--bg-hover); padding: 14px 20px;">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-comment-dots text-success"></i>
                <strong style="font-size: 14px; color: var(--text);">Phần II: Tổng Kết Cá Nhân, Điểm Mạnh & Phương Hướng Phát Triển</strong>
            </div>
        </div>

        <div class="card-body p-4">
            <div class="row g-4">
                <!-- Điểm đánh giá tổng thể -->
                <div class="col-md-12">
                    <div class="p-3 rounded d-flex justify-content-between align-items-center flex-wrap gap-3" style="background: rgba(16, 185, 129, 0.08); border: 1px solid rgba(16, 185, 129, 0.2);">
                        <div>
                            <div class="fw-bold text-success" style="font-size: 16px;">
                                <i class="fas fa-calculator me-1"></i> Điểm Tự Chấm Tổng Thể (Weighted Overall Score)
                            </div>
                            <small class="text-muted">Được tính tự động theo trọng số từng KRA, có thể điều chỉnh tổng quan</small>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <input type="number" name="overall_score" id="overallScoreInput" step="0.1" min="0" max="100" 
                                   class="form-control form-control-lg text-center fw-bolder text-success" style="width: 120px; font-size: 22px;" 
                                   value="<?= $existingSelfReview ? (float)$existingSelfReview->overall_score : 85 ?>">
                            <span class="fw-bold" style="font-size: 16px;">/ 100</span>
                        </div>
                    </div>
                </div>

                <!-- Điểm mạnh nổi bật -->
                <div class="col-md-6">
                    <label class="form-label" style="font-size: 13px; font-weight: 700; color: var(--text);">
                        <i class="fas fa-thumbs-up text-success me-1"></i> Điểm mạnh nổi bật trong chu kỳ vừa qua
                    </label>
                    <textarea name="strengths" class="form-control" rows="4" 
                              placeholder="Những thành tích xuất sắc, kỹ năng phát huy hiệu quả, tinh thần trách nhiệm trong các dự án công trình..."><?= htmlspecialchars($existingSelfReview->strengths ?? '') ?></textarea>
                </div>

                <!-- Điểm cần cải thiện -->
                <div class="col-md-6">
                    <label class="form-label" style="font-size: 13px; font-weight: 700; color: var(--text);">
                        <i class="fas fa-tools text-warning me-1"></i> Điểm cần cải thiện & Đề xuất đào tạo phát triển
                    </label>
                    <textarea name="improvements" class="form-control" rows="4" 
                              placeholder="Kỹ năng còn hạn chế, các tình huống cần rút kinh nghiệm, các chứng chỉ hoặc khóa học mong muốn công ty hỗ trợ..."><?= htmlspecialchars($existingSelfReview->improvements ?? '') ?></textarea>
                </div>

                <!-- Nhận xét & Đề xuất chung -->
                <div class="col-12">
                    <label class="form-label" style="font-size: 13px; font-weight: 700; color: var(--text);">
                        <i class="fas fa-envelope-open-text text-primary me-1"></i> Ý kiến & Nguyện vọng gửi tới Quản lý trực tiếp / Ban Giám đốc
                    </label>
                    <textarea name="comments" class="form-control" rows="3" 
                              placeholder="Đề xuất về điều kiện làm việc, trang thiết bị công trường, phân bổ nhân lực hoặc kế hoạch thăng tiến..."><?= htmlspecialchars($existingSelfReview->comments ?? '') ?></textarea>
                </div>
            </div>
        </div>
    </div>

    <!-- BUTTONS -->
    <div class="d-flex justify-content-between align-items-center mb-5">
        <a href="<?= BASE_URL ?>/evaluation/goals/<?= $period->id ?>" class="btn btn-ghost" style="border: 1px solid var(--border);">
            <i class="fas fa-times me-1"></i> Hủy bỏ
        </a>
        <button type="submit" class="btn btn-success btn-lg px-4" style="box-shadow: 0 4px 14px rgba(16,185,129,0.3); font-weight: 700;">
            <i class="fas fa-paper-plane me-2"></i> Hoàn Tất & Nộp Phiếu Tự Đánh Giá
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

    const row = input.closest('.kra-card');
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
