<?php
/**
 * View: evaluation/employee_history.php – Lịch sử Đánh giá Năng lực / KPI của Nhân viên
 * (Dùng độc lập hoặc nhúng vào Tab Profile 360°)
 */

$gradeBadges = [
    'A' => ['badge bg-success text-white', 'Xuất sắc (A)', '#10b981'],
    'B' => ['badge bg-primary text-white', 'Tốt (B)', '#3b82f6'],
    'C' => ['badge bg-warning text-dark',  'Đạt (C)', '#f59e0b'],
    'D' => ['badge bg-danger text-white',  'Cần cải thiện (D)', '#ef4444'],
];

$totalEvals = count($evaluations);
$sumScore = 0;
$countScore = 0;
$latestGrade = '---';

foreach ($evaluations as $idx => $ev) {
    if ($ev['final_score'] !== null) {
        $sumScore += (float)$ev['final_score'];
        $countScore++;
        if ($idx === 0) {
            $latestGrade = $ev['overall_grade'] ?? '---';
        }
    }
}

$avgEmpScore = $countScore > 0 ? round($sumScore / $countScore, 1) : 0;
?>

<div class="evaluation-history-module">
    <!-- MINI STATS BAR -->
    <div class="row g-3 mb-3">
        <div class="col-sm-4">
            <div style="background: var(--bg-hover); padding: 12px 16px; border-radius: 10px; border: 1px solid var(--border);">
                <div class="text-muted" style="font-size: 11px; text-transform: uppercase; font-weight: 600;">Số Chu Kỳ Đã Đánh Giá</div>
                <div style="font-size: 22px; font-weight: 800; color: var(--text);"><?= $totalEvals ?> <span style="font-size: 12px; font-weight: normal; color: var(--text-muted);">kỳ</span></div>
            </div>
        </div>
        <div class="col-sm-4">
            <div style="background: rgba(79, 70, 229, 0.06); padding: 12px 16px; border-radius: 10px; border: 1px solid rgba(79, 70, 229, 0.2);">
                <div class="text-muted" style="font-size: 11px; text-transform: uppercase; font-weight: 600;">Điểm Trung Bình Tích Lũy</div>
                <div style="font-size: 22px; font-weight: 800; color: var(--primary);"><?= $avgEmpScore > 0 ? $avgEmpScore : '---' ?> <span style="font-size: 12px; font-weight: normal; color: var(--text-muted);">/ 100</span></div>
            </div>
        </div>
        <div class="col-sm-4">
            <div style="background: rgba(16, 185, 129, 0.06); padding: 12px 16px; border-radius: 10px; border: 1px solid rgba(16, 185, 129, 0.2);">
                <div class="text-muted" style="font-size: 11px; text-transform: uppercase; font-weight: 600;">Xếp Loại Gần Nhất</div>
                <div style="font-size: 22px; font-weight: 800; color: #10b981;">
                    <?php if (isset($gradeBadges[$latestGrade])): ?>
                        <span class="<?= $gradeBadges[$latestGrade][0] ?>" style="font-size: 13px; font-weight: 700; padding: 4px 10px; border-radius: 14px;">
                            <?= $gradeBadges[$latestGrade][1] ?>
                        </span>
                    <?php else: ?>
                        <span class="text-muted" style="font-size: 14px; font-weight: 500;">Chưa có</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- TABLE EVALUATION HISTORY -->
    <div class="card" style="border: 1px solid var(--border); box-shadow: 0 2px 10px rgba(0,0,0,0.02);">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                <thead style="background: var(--bg-hover); color: var(--text-muted); font-size: 11.5px; text-transform: uppercase;">
                    <tr>
                        <th style="width: 50px; text-align: center;">STT</th>
                        <th style="width: 220px;">Chu kỳ / Thời gian</th>
                        <th>Mẫu đánh giá</th>
                        <th style="text-align: center; width: 90px;">KPI</th>
                        <th style="text-align: center; width: 90px;">Năng lực</th>
                        <th style="text-align: center; width: 100px;">Tổng điểm</th>
                        <th style="text-align: center; width: 90px;">Xếp loại</th>
                        <th>Nhận xét & Định hướng</th>
                        <th style="text-align: right; width: 110px;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($evaluations)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                <i class="fas fa-clipboard-list fa-2x mb-2 text-secondary" style="opacity: 0.5;"></i>
                                <div>Chưa có dữ liệu đánh giá nào cho nhân viên này.</div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $idx = 1; foreach ($evaluations as $ev): ?>
                            <tr>
                                <td style="text-align: center; color: var(--text-muted);"><?= $idx++ ?></td>
                                <td>
                                    <strong style="color: var(--text);">
                                        <?= htmlspecialchars($ev['period_name'] ?? ('Năm ' . $ev['eval_year'])) ?>
                                    </strong>
                                    <?php if (!empty($ev['start_date'])): ?>
                                        <div style="font-size: 11.5px; color: var(--text-muted);">
                                            <?= date('d/m/Y', strtotime($ev['start_date'])) ?> – <?= date('d/m/Y', strtotime($ev['end_date'])) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark" style="border: 1px solid var(--border);">
                                        <?= htmlspecialchars($ev['template_name'] ?? 'Mặc định') ?>
                                    </span>
                                    <?php if (!empty($ev['evaluator_name'])): ?>
                                        <div style="font-size: 11px; color: var(--text-muted); margin-top: 3px;">
                                            Người chấm: <?= htmlspecialchars($ev['evaluator_name']) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: center; font-weight: 600;">
                                    <?= ($ev['kpi_score'] !== null) ? number_format((float)$ev['kpi_score'], 1) : '---' ?>
                                </td>
                                <td style="text-align: center; font-weight: 600;">
                                    <?= ($ev['competency_score'] !== null) ? number_format((float)$ev['competency_score'], 1) : '---' ?>
                                </td>
                                <td style="text-align: center;">
                                    <?php if ($ev['final_score'] !== null): ?>
                                        <?php
                                            $fs = (float)$ev['final_score'];
                                            $col = $fs >= 85 ? '#10b981' : ($fs >= 70 ? '#3b82f6' : ($fs >= 50 ? '#f59e0b' : '#ef4444'));
                                        ?>
                                        <strong style="font-size: 14px; color: <?= $col ?>;"><?= number_format($fs, 1) ?></strong>
                                        <span style="font-size: 10px; color: var(--text-muted);">/100</span>
                                    <?php else: ?>
                                        <span class="text-muted">---</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: center;">
                                    <?php if (!empty($ev['overall_grade']) && isset($gradeBadges[$ev['overall_grade']])): ?>
                                        <span class="<?= $gradeBadges[$ev['overall_grade']][0] ?>" style="font-size: 11px; padding: 3px 8px; border-radius: 12px;">
                                            <?= $ev['overall_grade'] ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">---</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($ev['strengths'])): ?>
                                        <div style="font-size: 12px; color: #059669;">
                                            <i class="fas fa-check-circle"></i> <strong>Ưu điểm:</strong> <?= htmlspecialchars(mb_substr($ev['strengths'], 0, 80, 'UTF-8')) ?><?= mb_strlen($ev['strengths'], 'UTF-8') > 80 ? '...' : '' ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($ev['improvements'])): ?>
                                        <div style="font-size: 12px; color: #d97706; margin-top: 2px;">
                                            <i class="fas fa-arrow-circle-up"></i> <strong>Cải thiện:</strong> <?= htmlspecialchars(mb_substr($ev['improvements'], 0, 80, 'UTF-8')) ?><?= mb_strlen($ev['improvements'], 'UTF-8') > 80 ? '...' : '' ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (empty($ev['strengths']) && empty($ev['improvements'])): ?>
                                        <span class="text-muted" style="font-size: 12px; font-style: italic;">Chưa có nhận xét</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: right;">
                                    <?php if (!empty($ev['period_id'])): ?>
                                        <a href="<?= BASE_URL ?>/evaluation/evaluate/<?= $ev['period_id'] ?>/<?= $ev['employee_id'] ?>" 
                                           class="btn btn-sm btn-ghost" 
                                           title="Xem chi tiết phiếu chấm"
                                           style="border: 1px solid var(--border);">
                                            <i class="fas fa-eye text-primary"></i> Chi tiết
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
