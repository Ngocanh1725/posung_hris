<?php
/**
 * ============================================================
 *  POSUNG HRIS – View: salary_progression/profile_tab.php
 * ============================================================
 *  Tích hợp Lịch sử Diễn biến Lương & Nâng bậc vào Hồ sơ 360°
 * ============================================================
 */
require_once APP_ROOT . '/models/SalaryProgression.php';
$progModel = new SalaryProgression();
$progressions = $progModel->getByEmployee($employee->id, 'DESC');
$curSalary = $progModel->getCurrentSalary($employee->id);
?>

<div class="salary-progression-tab mt-4 pt-3 border-top">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h4 class="section-title mb-0"><i class="fas fa-chart-line-up text-primary me-2"></i>Diễn biến Lương & Nâng bậc (Salary Progressions)</h4>
            <small class="text-muted">Lịch sử điều chỉnh mức lương cơ bản qua các năm theo quyết định công ty</small>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>/salaryProgression/index/<?= $employee->id ?>" class="btn btn-sm btn-outline-primary" style="border-radius: 6px;">
                <i class="fas fa-timeline me-1"></i> Xem Timeline & Biểu đồ
            </a>
            <?php if (Session::isManager() || Session::isAdmin()): ?>
                <a href="<?= BASE_URL ?>/salaryProgression/create/<?= $employee->id ?>" class="btn btn-sm btn-primary" style="border-radius: 6px;">
                    <i class="fas fa-plus me-1"></i> Quyết định nâng lương
                </a>
            <?php endif; ?>
        </div>
    </div>

    <?php if (empty($progressions)): ?>
        <div class="p-4 bg-light rounded text-center text-muted">
            <i class="fas fa-file-invoice-dollar fa-2x mb-2 text-secondary"></i>
            <p class="mb-0">Chưa có bản ghi quyết định nâng bậc lương nào cho nhân viên này.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-2">Ngày H.Lực</th>
                        <th>Lý do điều chỉnh</th>
                        <th class="text-end">Mức lương cũ</th>
                        <th class="text-end">Mức lương mới</th>
                        <th class="text-center">Biến động</th>
                        <th>Số Quyết định</th>
                        <th>Người duyệt</th>
                        <th class="text-end pe-2">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($progressions as $idx => $p): ?>
                        <tr>
                            <td class="ps-2 font-monospace fw-medium"><?= fmtDate($p->effective_date) ?></td>
                            <td>
                                <strong class="text-dark"><?= h($p->reason ?: 'Điều chỉnh lương') ?></strong>
                                <?php if (!empty($p->notes)): ?>
                                    <small class="text-muted d-block"><?= h($p->notes) ?></small>
                                <?php endif; ?>
                            </td>
                            <td class="text-end font-monospace text-muted">
                                <?= ($p->old_salary > 0) ? number_format($p->old_salary, 0, ',', '.') . ' ₫' : '---' ?>
                            </td>
                            <td class="text-end font-monospace fw-bold text-dark">
                                <?= number_format($p->new_salary ?? $p->base_salary, 0, ',', '.') ?> ₫
                            </td>
                            <td class="text-center">
                                <?php if ($p->increase_amount > 0): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                        +<?= number_format($p->increase_amount, 0, ',', '.') ?> ₫ (+<?= $p->increase_percent ?>%)
                                    </span>
                                <?php elseif ($p->increase_amount < 0): ?>
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                        <?= number_format($p->increase_amount, 0, ',', '.') ?> ₫ (<?= $p->increase_percent ?>%)
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-light text-muted border">Mức khởi điểm</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="font-monospace fw-medium text-primary"><?= h($p->decision_num ?: '---') ?></span>
                                <?php if (!empty($p->decision_date)): ?>
                                    <small class="text-muted d-block"><?= fmtDate($p->decision_date) ?></small>
                                <?php endif; ?>
                            </td>
                            <td><small class="text-muted"><?= h($p->approver_name ?: 'HĐQT / BGĐ') ?></small></td>
                            <td class="text-end pe-2">
                                <a href="<?= BASE_URL ?>/salaryProgression/index/<?= $employee->id ?>" class="btn btn-sm btn-light text-primary" title="Xem Timeline">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
