<?php
/**
 * View: evaluation/evaluate.php – Form Chấm điểm Đánh giá Năng lực / KPI Trực quan
 */

$selfMode = (isset($_GET['mode']) && $_GET['mode'] === 'self');
$role = $selfMode ? 'self' : 'manager';

// Phân nhóm tiêu chí theo category
$groupedCriteria = [];
foreach ($criteria as $c) {
    $cat = $c['category'] ?? 'Khác';
    $groupedCriteria[$cat][] = $c;
}

$categoryLabels = [
    'KPI'        => ['Chỉ số Hiệu suất Công việc (KPI)', 'fas fa-bullseye', '#4f46e5'],
    'Competency' => ['Năng lực Chuyên môn & Kỹ năng',   'fas fa-brain',    '#0ea5e9'],
    'Attitude'   => ['Thái độ, Kỷ luật & Tác phong',    'fas fa-smile',    '#10b981'],
    'HSE'        => ['An toàn lao động, 5S & Sức khỏe', 'fas fa-shield-alt','#f59e0b'],
];

$levelDescriptions = [
    1 => 'Kém / Không đạt (Dưới chuẩn yêu cầu)',
    2 => 'Cần cải thiện (Chưa hoàn thành kỳ vọng)',
    3 => 'Đạt chuẩn (Đáp ứng đầy đủ yêu cầu công việc)',
    4 => 'Tốt / Vượt chuẩn (Chủ động, hiệu quả cao)',
    5 => 'Xuất sắc (Hình mẫu tiêu biểu, vượt xa kỳ vọng)',
];
?>

<!-- BREADCRUMB -->
<div class="breadcrumb-nav mb-3" style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: var(--text-muted);">
    <a href="<?= BASE_URL ?>/evaluation" style="color: var(--primary); text-decoration: none;">
        <i class="fas fa-award"></i> Quản lý Đánh giá KPI
    </a>
    <i class="fas fa-chevron-right" style="font-size: 10px;"></i>
    <a href="<?= BASE_URL ?>/evaluation/show/<?= $period->id ?>" style="color: var(--primary); text-decoration: none;">
        <?= htmlspecialchars($period->name) ?>
    </a>
    <i class="fas fa-chevron-right" style="font-size: 10px;"></i>
    <span>Chấm điểm: <?= htmlspecialchars($employee->full_name) ?></span>
</div>

<!-- EMPLOYEE & PERIOD INFO HEADER -->
<div class="card mb-4" style="border: 1px solid var(--border); box-shadow: 0 4px 18px rgba(0,0,0,0.04);">
    <div class="card-body" style="padding: 22px 24px;">
        <div class="row align-items-center g-3">
            <div class="col-lg-7 d-flex align-items-center gap-3">
                <div style="width: 64px; height: 64px; border-radius: 50%; background: #e0e7ff; color: #4338ca; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 24px; flex-shrink: 0; overflow: hidden; border: 2px solid var(--border);">
                    <?php if (!empty($employee->avatar_path)): ?>
                        <img src="<?= BASE_URL . '/' . htmlspecialchars($employee->avatar_path) ?>" alt="" style="width: 100%; height: 100%; object-fit: cover;">
                    <?php else: ?>
                        <?= mb_strtoupper(mb_substr($employee->full_name, 0, 1, 'UTF-8'), 'UTF-8') ?>
                    <?php endif; ?>
                </div>
                <div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <h2 style="font-size: 20px; font-weight: 700; margin: 0; color: var(--text);">
                            <?= htmlspecialchars($employee->full_name) ?>
                        </h2>
                        <span class="badge bg-light text-dark" style="border: 1px solid var(--border); font-size: 12px;">
                            Mã: <?= htmlspecialchars($employee->emp_code) ?>
                        </span>
                        <?php if ($selfMode): ?>
                            <span class="badge bg-warning text-dark"><i class="fas fa-user-edit"></i> Tự đánh giá cá nhân</span>
                        <?php else: ?>
                            <span class="badge bg-primary text-white"><i class="fas fa-user-tie"></i> Quản lý trực tiếp chấm điểm</span>
                        <?php endif; ?>
                    </div>
                    <div style="font-size: 13px; color: var(--text-muted); display: flex; gap: 16px; flex-wrap: wrap; margin-top: 6px;">
                        <span><i class="fas fa-building text-primary"></i> <?= htmlspecialchars($employee->dept_name ?? 'Chưa gán') ?></span>
                        <span><i class="fas fa-id-badge text-primary"></i> <?= htmlspecialchars($employee->pos_title ?? '---') ?></span>
                        <?php if (!empty($employee->project_name)): ?>
                            <span><i class="fas fa-project-diagram text-primary"></i> <?= htmlspecialchars($employee->project_name) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- LIVE SCORE PREVIEW BOX -->
            <div class="col-lg-5">
                <div style="background: linear-gradient(135deg, rgba(79, 70, 229, 0.08), rgba(99, 102, 241, 0.02)); border: 1px solid rgba(79, 70, 229, 0.2); border-radius: 12px; padding: 14px 20px;">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span style="font-size: 12px; text-transform: uppercase; font-weight: 700; color: var(--text-muted); letter-spacing: 0.5px;">
                            Điểm Tổng Hợp Dự Kiến
                        </span>
                        <span id="liveGradeBadge" class="badge bg-success text-white" style="font-size: 13px; font-weight: 700; padding: 4px 12px; border-radius: 14px;">
                            Hạng A (Xuất sắc)
                        </span>
                    </div>
                    <div class="d-flex align-items-baseline gap-2">
                        <span id="liveFinalScore" style="font-size: 32px; font-weight: 800; color: var(--primary); line-height: 1;">
                            <?= !empty($summary->final_score) ? number_format((float)$summary->final_score, 1) : '0.0' ?>
                        </span>
                        <span style="font-size: 15px; color: var(--text-muted); font-weight: 500;">/ 100 điểm</span>
                    </div>
                    <div class="d-flex justify-content-between mt-2 pt-2" style="border-top: 1px dashed rgba(79, 70, 229, 0.2); font-size: 12px;">
                        <span>Điểm KPI: <strong id="liveKpiScore"><?= !empty($summary->kpi_score) ? number_format((float)$summary->kpi_score, 1) : '0.0' ?></strong>%</span>
                        <span>Năng lực: <strong id="liveCompScore"><?= !empty($summary->competency_score) ? number_format((float)$summary->competency_score, 1) : '0.0' ?></strong>%</span>
                        <span>Trọng số đã chấm: <strong id="liveEvaluatedWeight">100</strong>%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- FORM EVALUATION -->
<form action="<?= BASE_URL ?>/evaluation/saveEvaluation" method="POST" id="evalForm">
    <input type="hidden" name="_csrf_token" value="<?= Session::generateCsrfToken() ?>">
    <input type="hidden" name="period_id" value="<?= $period->id ?>">
    <input type="hidden" name="employee_id" value="<?= $employee->id ?>">
    <input type="hidden" name="eval_role" value="<?= $role ?>">

    <!-- CRITERIA GROUPS -->
    <?php foreach ($groupedCriteria as $catKey => $items): ?>
        <?php 
            $catMeta = $categoryLabels[$catKey] ?? [$catKey, 'fas fa-tasks', '#6366f1'];
        ?>
        <div class="card mb-4" style="border: 1px solid var(--border); box-shadow: 0 2px 12px rgba(0,0,0,0.03);">
            <div class="card-header" style="background: var(--bg-hover); border-bottom: 1px solid var(--border); padding: 14px 20px;">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <i class="<?= $catMeta[1] ?>" style="color: <?= $catMeta[2] ?>; font-size: 16px;"></i>
                        <h4 style="margin: 0; font-size: 15px; font-weight: 700; color: var(--text);">
                            <?= $catMeta[0] ?>
                        </h4>
                    </div>
                    <span class="badge bg-light text-muted" style="border: 1px solid var(--border); font-size: 12px;">
                        <?= count($items) ?> tiêu chí
                    </span>
                </div>
            </div>

            <div class="card-body p-0">
                <?php foreach ($items as $c): ?>
                    <?php
                        $cId = $c['id'];
                        $currentScore = $selfMode ? ($c['self_score'] ?? 3) : ($c['final_score'] ?? $c['manager_score'] ?? $c['self_score'] ?? 3);
                        $currentScore = (float)$currentScore;
                    ?>
                    <div class="p-3" style="border-bottom: 1px solid var(--border); transition: background 0.15s;" onmouseover="this.style.background='rgba(79,70,229,0.02)'" onmouseout="this.style.background='transparent'">
                        <div class="row align-items-center g-3">
                            <!-- Criterion Info -->
                            <div class="col-lg-5">
                                <div class="d-flex align-items-start justify-content-between">
                                    <div>
                                        <div style="font-weight: 600; font-size: 14px; color: var(--text);">
                                            <?= htmlspecialchars($c['name']) ?>
                                        </div>
                                        <?php if (!empty($c['description'])): ?>
                                            <div style="font-size: 12.5px; color: var(--text-muted); margin-top: 3px; line-height: 1.4;">
                                                <?= htmlspecialchars($c['description']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <span class="badge" style="background: rgba(79, 70, 229, 0.1); color: var(--primary); font-weight: 700; font-size: 12px; margin-left: 8px; flex-shrink: 0;">
                                        <?= (int)$c['weight'] ?>%
                                    </span>
                                </div>

                                <!-- Self score reference if manager mode -->
                                <?php if (!$selfMode && $c['self_score'] !== null): ?>
                                    <div style="margin-top: 6px; font-size: 12px; color: #d97706; background: rgba(245, 158, 11, 0.1); padding: 3px 8px; border-radius: 4px; display: inline-block;">
                                        <i class="fas fa-user-check"></i> NV tự đánh giá: <strong><?= number_format((float)$c['self_score'], 1) ?> / 5</strong>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Rating Level Selection (1 - 5) -->
                            <div class="col-lg-4">
                                <div class="d-flex align-items-center justify-content-between gap-1 rating-scale-container">
                                    <?php for ($lv = 1; $lv <= 5; $lv++): ?>
                                        <label class="rating-choice-label text-center flex-grow-1" 
                                               style="cursor: pointer; margin: 0; padding: 6px 4px; border: 1px solid var(--border); border-radius: 8px; transition: all 0.2s; user-select: none;"
                                               title="<?= $lv ?> - <?= $levelDescriptions[$lv] ?>">
                                            <input type="radio" 
                                                   name="scores[<?= $cId ?>]" 
                                                   value="<?= $lv ?>" 
                                                   class="criteria-score-input"
                                                   data-weight="<?= (int)$c['weight'] ?>"
                                                   data-category="<?= htmlspecialchars($c['category']) ?>"
                                                   <?= ((int)round($currentScore) === $lv) ? 'checked' : '' ?>
                                                   style="display: none;" 
                                                   onchange="onScoreChanged()">
                                            <div style="font-weight: 700; font-size: 15px;"><?= $lv ?></div>
                                            <div style="font-size: 10px; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                <?= $lv == 1 ? 'Kém' : ($lv == 2 ? 'Cần cải thiện' : ($lv == 3 ? 'Đạt' : ($lv == 4 ? 'Tốt' : 'Xuất sắc'))) ?>
                                            </div>
                                        </label>
                                    <?php endfor; ?>
                                </div>
                            </div>

                            <!-- Comment per criterion -->
                            <div class="col-lg-3">
                                <input type="text" 
                                       name="comments[<?= $cId ?>]" 
                                       value="<?= htmlspecialchars($c['comment'] ?? '') ?>" 
                                       placeholder="Ghi chú minh chứng (nếu có)..." 
                                       class="form-control form-control-sm"
                                       style="font-size: 12.5px;">
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>

    <!-- OVERALL FEEDBACK CARD -->
    <div class="card mb-4" style="border: 1px solid var(--border); box-shadow: 0 2px 12px rgba(0,0,0,0.03);">
        <div class="card-header" style="background: var(--bg-hover); border-bottom: 1px solid var(--border); padding: 14px 20px;">
            <h4 style="margin: 0; font-size: 15px; font-weight: 700; color: var(--text);">
                <i class="fas fa-comment-dots text-primary"></i> Nhận Xét Tổng Quan & Định Hướng Phát Triển
            </h4>
        </div>
        <div class="card-body" style="padding: 20px;">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" style="font-weight: 600; font-size: 13px;">
                        <i class="fas fa-star text-warning"></i> Điểm mạnh & Thành tích nổi bật
                    </label>
                    <textarea name="strengths" class="form-control" rows="3" 
                              placeholder="Ghi nhận những đóng góp, sáng kiến, sự chủ động hoặc tinh thần trách nhiệm nổi trội..."><?= htmlspecialchars($summary->strengths ?? '') ?></textarea>
                </div>

                <div class="col-md-6">
                    <label class="form-label" style="font-weight: 600; font-size: 13px;">
                        <i class="fas fa-arrow-circle-up text-primary"></i> Điểm cần cải thiện & Mục tiêu phát triển
                    </label>
                    <textarea name="improvements" class="form-control" rows="3" 
                              placeholder="Các kỹ năng cần rèn luyện thêm, các khóa đào tạo khuyến nghị hoặc mục tiêu cho quý tới..."><?= htmlspecialchars($summary->improvements ?? '') ?></textarea>
                </div>

                <div class="col-12">
                    <label class="form-label" style="font-weight: 600; font-size: 13px;">
                        <i class="fas fa-sticky-note text-secondary"></i> Ghi chú bổ sung của Quản lý / Hội đồng
                    </label>
                    <textarea name="notes" class="form-control" rows="2" 
                              placeholder="Ý kiến hoặc lưu ý khác..."><?= htmlspecialchars($summary->notes ?? '') ?></textarea>
                </div>
            </div>
        </div>
    </div>

    <!-- STICKY ACTION BAR -->
    <div class="card" style="position: sticky; bottom: 15px; z-index: 100; border: 1px solid var(--border); box-shadow: 0 -4px 20px rgba(0,0,0,0.1); background: var(--bg-card);">
        <div class="card-body py-2 px-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center gap-3">
                <a href="<?= BASE_URL ?>/evaluation/show/<?= $period->id ?>" class="btn btn-ghost" style="border: 1px solid var(--border);">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>
                <span class="text-muted" style="font-size: 13px;">
                    Chấm điểm cho: <strong><?= htmlspecialchars($employee->full_name) ?></strong>
                </span>
            </div>

            <div class="d-flex align-items-center gap-3">
                <div class="d-none d-md-block text-end" style="font-size: 13px;">
                    Tổng điểm: <strong id="stickyScoreDisplay" style="color: var(--primary); font-size: 16px;">0.0</strong> / 100
                </div>
                <button type="submit" class="btn btn-primary" style="padding: 8px 24px; font-weight: 600; box-shadow: 0 4px 14px rgba(79,70,229,0.3);">
                    <i class="fas fa-save"></i> Lưu Kết Quả Đánh Giá
                </button>
            </div>
        </div>
    </div>
</form>

<style>
.rating-choice-label:hover {
    border-color: var(--primary) !important;
    background: rgba(79, 70, 229, 0.05);
}
.rating-choice-label.active {
    background: #4f46e5 !important;
    border-color: #4f46e5 !important;
    color: #fff !important;
    box-shadow: 0 2px 8px rgba(79, 70, 229, 0.4);
}
.rating-choice-label.active div {
    color: #fff !important;
}
</style>

<script>
function onScoreChanged() {
    let inputs = document.querySelectorAll('.criteria-score-input');
    let totalWeightedScore = 0;
    let evaluatedWeight = 0;
    
    let kpiWeighted = 0;
    let kpiWeight = 0;
    let compWeighted = 0;
    let compWeight = 0;

    inputs.forEach(input => {
        let label = input.closest('.rating-choice-label');
        if (input.checked) {
            label.classList.add('active');

            let score = parseFloat(input.value); // 1 - 5
            let weight = parseFloat(input.getAttribute('data-weight'));
            let category = input.getAttribute('data-category');

            let score100 = (score / 5) * 100;
            totalWeightedScore += score100 * (weight / 100);
            evaluatedWeight += weight;

            if (category === 'KPI') {
                kpiWeighted += score100 * weight;
                kpiWeight += weight;
            } else {
                compWeighted += score100 * weight;
                compWeight += weight;
            }
        } else {
            label.classList.remove('active');
        }
    });

    let finalScore = totalWeightedScore.toFixed(1);
    let kpiScore = kpiWeight > 0 ? (kpiWeighted / kpiWeight).toFixed(1) : '---';
    let compScore = compWeight > 0 ? (compWeighted / compWeight).toFixed(1) : '---';

    // Update Live UI
    document.getElementById('liveFinalScore').innerText = finalScore;
    document.getElementById('stickyScoreDisplay').innerText = finalScore;
    document.getElementById('liveKpiScore').innerText = kpiScore;
    document.getElementById('liveCompScore').innerText = compScore;
    document.getElementById('liveEvaluatedWeight').innerText = evaluatedWeight;

    // Grade classification
    let gradeBadge = document.getElementById('liveGradeBadge');
    let numScore = parseFloat(finalScore);

    if (numScore >= 85) {
        gradeBadge.className = 'badge bg-success text-white';
        gradeBadge.innerHTML = 'Hạng A (Xuất sắc)';
    } else if (numScore >= 70) {
        gradeBadge.className = 'badge bg-primary text-white';
        gradeBadge.innerHTML = 'Hạng B (Tốt)';
    } else if (numScore >= 50) {
        gradeBadge.className = 'badge bg-warning text-dark';
        gradeBadge.innerHTML = 'Hạng C (Đạt)';
    } else {
        gradeBadge.className = 'badge bg-danger text-white';
        gradeBadge.innerHTML = 'Hạng D (Cần cải thiện)';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    onScoreChanged();
});
</script>
