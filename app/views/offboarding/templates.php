<?php
/**
 * ============================================================
 *  POSUNG HRIS – View: offboarding/templates.php
 *  Quản lý Mẫu Quy trình Thôi việc & Checklist chuẩn
 * ============================================================
 */
?>

<div class="offboarding-templates-page">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <a href="<?= BASE_URL ?>/offboarding" class="text-decoration-none text-muted small">
                <i class="fa-solid fa-arrow-left me-1"></i> Quay lại Danh sách Thôi việc
            </a>
            <h4 class="fw-bold text-dark mt-1 mb-0">Mẫu Quy trình Thôi việc (Offboarding Templates)</h4>
        </div>
        <div>
            <a href="<?= BASE_URL ?>/offboarding" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm">
                <i class="fa-solid fa-list me-1"></i> Danh sách thôi việc
            </a>
        </div>
    </div>

    <!-- Templates List -->
    <div class="row g-4">
        <?php foreach ($templates as $tmpl): ?>
            <?php 
            $offboardingModel = new Offboarding();
            $fullTmpl = $offboardingModel->getTemplateWithTasks((int)$tmpl['id']);
            $tasks = $fullTmpl['tasks'] ?? [];
            ?>
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                    <div class="card-header bg-white border-bottom p-4">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h5 class="fw-bold mb-1 text-dark"><?= h($tmpl['name']) ?></h5>
                                <p class="text-muted small mb-0"><?= h($tmpl['description']) ?></p>
                            </div>
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1">
                                Đang kích hoạt
                            </span>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3 text-muted small">
                            <span class="fw-bold text-uppercase"><i class="fa-solid fa-list-check me-1 text-primary"></i> Checklist Nhiệm vụ (<?= count($tasks) ?>)</span>
                            <span class="text-danger fw-semibold"><i class="fa-solid fa-lock me-1"></i><?= $tmpl['blocking_tasks'] ?> mục bắt buộc</span>
                        </div>
                        <div class="list-group list-group-flush border rounded-3 overflow-hidden">
                            <?php foreach ($tasks as $t): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center p-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <?php 
                                        $deptBadge = match($t['responsible_department']) {
                                            'IT'      => '<span class="badge bg-primary-subtle text-primary border" style="width: 55px;">IT</span>',
                                            'Admin'   => '<span class="badge bg-warning-subtle text-warning border" style="width: 55px;">Admin</span>',
                                            'HSE'     => '<span class="badge bg-danger-subtle text-danger border" style="width: 55px;">HSE</span>',
                                            'Finance' => '<span class="badge bg-success-subtle text-success border" style="width: 55px;">Finance</span>',
                                            'HR'      => '<span class="badge bg-info-subtle text-info border" style="width: 55px;">HR</span>',
                                            default   => '<span class="badge bg-secondary-subtle text-secondary" style="width: 55px;">Khác</span>'
                                        };
                                        echo $deptBadge;
                                        ?>
                                        <span class="small fw-semibold text-dark"><?= h($t['title']) ?></span>
                                    </div>
                                    <?php if ($t['is_blocking'] == 1): ?>
                                        <span class="badge bg-danger text-white small" title="Nhiệm vụ bắt buộc hoàn thành mới được chốt thôi việc">
                                            Bắt buộc
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-light text-muted small">Tùy chọn</span>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
