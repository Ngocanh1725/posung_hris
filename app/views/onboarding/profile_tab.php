<?php
/**
 * View: onboarding/profile_tab.php – Tab Quy trình Hội nhập nhúng vào Employee Profile 360°
 */

$onb = $employee->onboarding ?? null;
?>

<div class="onboarding-profile-tab">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0" style="color: var(--text);">
            <i class="fas fa-user-check text-primary me-2"></i>Quy Trình Hội Nhập & Tiếp Nhận Nhân Sự (Onboarding)
        </h5>
        <?php if ($onb): ?>
            <div class="d-flex gap-2">
                <a href="<?= BASE_URL ?>/onboarding/printHandover/<?= $onb['id'] ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-print me-1"></i> In Biên Bản Bàn Giao
                </a>
                <a href="<?= BASE_URL ?>/onboarding/show/<?= $onb['id'] ?>" class="btn btn-sm btn-primary">
                    <i class="fas fa-clipboard-check me-1"></i> Xem Chi Tiết Checklist
                </a>
            </div>
        <?php endif; ?>
    </div>

    <?php if (!$onb): ?>
        <div class="card border-0 shadow-sm text-center py-5" style="border-radius: 12px; background: var(--bg-hover);">
            <div class="card-body">
                <i class="fas fa-user-clock fa-3x text-secondary mb-3" style="opacity: 0.4;"></i>
                <h5 class="fw-bold">Nhân sự này chưa được thiết lập Quy trình Hội nhập</h5>
                <p class="text-muted small">Khởi tạo quy trình hội nhập để phân công nhiệm vụ cho IT, HR, HSE, Admin và Kế toán.</p>
                <a href="<?= BASE_URL ?>/onboarding/start/<?= $employee->id ?>" class="btn btn-primary px-4 shadow-sm" style="border-radius: 8px;">
                    <i class="fas fa-play me-1"></i> Kích Hoạt Quy Trình Hội Nhập
                </a>
            </div>
        </div>
    <?php else: 
        $pct = (int)($onb['progress_pct'] ?? 0);
        $done = (int)($onb['done_items'] ?? 0);
        $total = (int)($onb['total_items'] ?? 0);
    ?>
        <!-- Progress Summary Card -->
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; background: var(--bg-card); border-left: 4px solid #4f46e5 !important;">
            <div class="card-body p-4">
                <div class="row g-3 align-items-center">
                    <div class="col-md-6">
                        <div class="text-muted small">Mẫu quy trình áp dụng:</div>
                        <h5 class="fw-bold text-primary mb-1"><?= htmlspecialchars($onb['template_name']) ?></h5>
                        <div class="text-muted small">
                            <i class="fas fa-calendar-alt text-success me-1"></i>Ngày bắt đầu: <strong><?= date('d/m/Y', strtotime($onb['start_date'])) ?></strong> • 
                            Trạng thái: 
                            <?php if ($onb['status'] === 'Completed'): ?>
                                <span class="badge bg-success">Hoàn tất 100%</span>
                            <?php elseif ($onb['status'] === 'Overdue'): ?>
                                <span class="badge bg-danger">Quá hạn</span>
                            <?php else: ?>
                                <span class="badge bg-primary">Đang thực hiện</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small fw-bold">Tiến độ hoàn thành</span>
                            <strong class="fs-5 <?= $pct >= 100 ? 'text-success' : 'text-primary' ?>"><?= $pct ?>%</strong>
                        </div>
                        <div class="progress mb-2" style="height: 8px; border-radius: 4px; background: #e2e8f0;">
                            <div class="progress-bar <?= $pct >= 100 ? 'bg-success' : 'bg-primary' ?>" role="progressbar" style="width: <?= $pct ?>%;"></div>
                        </div>
                        <div class="d-flex justify-content-between small text-muted">
                            <span>Đã xong <strong><?= $done ?></strong> / <?= $total ?> nhiệm vụ</span>
                            <?php if (!empty($onb['overdue_items'])): ?>
                                <span class="text-danger fw-bold"><i class="fas fa-exclamation-circle"></i> Trễ <?= $onb['overdue_items'] ?> task</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Checklist Table Summary -->
        <div class="card border-0 shadow-sm" style="border-radius: 12px; background: var(--bg-card);">
            <div class="card-header bg-transparent py-3 border-bottom">
                <h6 class="fw-bold mb-0 text-primary"><i class="fas fa-tasks me-2"></i>Checklist Các Đầu Việc Tiếp Nhận</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                        <thead style="background: var(--bg-hover); font-size: 11px; text-transform: uppercase; color: var(--text-muted);">
                            <tr>
                                <th style="width: 130px;">Bộ phận</th>
                                <th>Nhiệm vụ</th>
                                <th style="text-align: center; width: 120px;">Hạn chót</th>
                                <th style="text-align: center; width: 140px;">Trạng thái</th>
                                <th>Ghi chú</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($onb['items'] as $item): 
                                $isDone = ($item['status'] === 'Done' || $item['status'] === 'Skipped');
                                $dColor = [
                                    'IT'      => '#3b82f6',
                                    'HR'      => '#ec4899',
                                    'HSE'     => '#10b981',
                                    'Admin'   => '#f59e0b',
                                    'Finance' => '#6366f1',
                                ][$item['responsible_department']] ?? '#64748b';
                            ?>
                            <tr class="<?= $isDone ? 'table-light text-muted' : '' ?>">
                                <td>
                                    <span class="badge text-white" style="background: <?= $dColor ?>; font-size: 11px;">
                                        <?= htmlspecialchars($item['responsible_department']) ?>
                                    </span>
                                </td>
                                <td>
                                    <strong class="<?= $isDone ? 'text-decoration-line-through text-muted' : '' ?>" style="color: var(--text);">
                                        <?= htmlspecialchars($item['title']) ?>
                                    </strong>
                                    <?php if ($item['is_required']): ?>
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger small ms-1" style="font-size: 9px;">Bắt buộc</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: center;">
                                    <?= !empty($item['due_date']) ? date('d/m/Y', strtotime($item['due_date'])) : '---' ?>
                                </td>
                                <td style="text-align: center;">
                                    <?php if ($item['status'] === 'Done'): ?>
                                        <span class="badge bg-success"><i class="fas fa-check"></i> Đã xong</span>
                                    <?php elseif ($item['status'] === 'Skipped'): ?>
                                        <span class="badge bg-secondary">Bỏ qua</span>
                                    <?php elseif ($item['status'] === 'InProgress'): ?>
                                        <span class="badge bg-info text-dark">Đang làm</span>
                                    <?php else: ?>
                                        <span class="badge bg-light text-dark border">Chờ xử lý</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small class="text-muted"><?= htmlspecialchars($item['notes'] ?? '---') ?></small>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
