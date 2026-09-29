<!-- ══════════════════════════════════════════════════════════
     POSUNG HRIS – QUẢN LÝ MẪU QUY TRÌNH HỘI NHẬP (TEMPLATES)
     ══════════════════════════════════════════════════════════ -->

<div class="onboarding-templates-page">
    <!-- Breadcrumb & Top Bar -->
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div class="breadcrumb-bar m-0">
            <a href="<?= BASE_URL ?>/onboarding"><i class="fas fa-user-check"></i> Hội nhập</a>
            <i class="fas fa-chevron-right"></i>
            <span class="text-primary fw-bold"><i class="fas fa-layer-group"></i> Mẫu Quy trình Hội nhập (Templates)</span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="<?= BASE_URL ?>/onboarding" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Dashboard Hội nhập
            </a>
            <a href="<?= BASE_URL ?>/onboarding/createTemplate" class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i> Tạo Mẫu Quy Trình Mới
            </a>
        </div>
    </div>

    <!-- Templates Grid -->
    <div class="row g-4">
        <?php if (empty($templates)): ?>
            <div class="col-12 text-center py-5 text-muted">
                <i class="fas fa-layer-group fa-3x mb-3 text-secondary" style="opacity: 0.3;"></i>
                <h5>Chưa có mẫu quy trình hội nhập nào.</h5>
                <a href="<?= BASE_URL ?>/onboarding/createTemplate" class="btn btn-primary mt-2">
                    <i class="fas fa-plus"></i> Tạo mẫu quy trình đầu tiên
                </a>
            </div>
        <?php else: ?>
            <?php foreach ($templates as $tpl): ?>
            <div class="col-lg-6">
                <div class="card h-100 border-0 shadow-sm" style="border-radius: 14px; background: var(--bg-card); overflow: hidden; border-top: 4px solid #4f46e5 !important;">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h5 class="fw-bold mb-1" style="color: var(--text);">
                                    <?= htmlspecialchars($tpl['name']) ?>
                                </h5>
                                <span class="badge bg-light text-primary border">
                                    <i class="fas fa-building me-1"></i><?= htmlspecialchars($tpl['dept_name'] ?? 'Toàn công ty / Mặc định') ?>
                                </span>
                            </div>
                            <?php if ($tpl['is_active']): ?>
                                <span class="badge bg-success bg-opacity-10 text-success border border-success" style="font-size: 11.5px;">Đang kích hoạt</span>
                            <?php else: ?>
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border" style="font-size: 11.5px;">Đã đóng</span>
                            <?php endif; ?>
                        </div>

                        <p class="text-muted small mb-3" style="min-height: 38px;">
                            <?= htmlspecialchars($tpl['description'] ?: 'Không có mô tả chi tiết.') ?>
                        </p>

                        <div class="d-flex align-items-center gap-3 py-2 px-3 rounded border mb-3" style="background: var(--bg-hover);">
                            <div class="d-flex align-items-center gap-2">
                                <i class="fas fa-tasks text-primary"></i>
                                <span class="small"><strong><?= $tpl['total_tasks'] ?></strong> nhiệm vụ</span>
                            </div>
                            <div class="vr" style="height: 18px;"></div>
                            <div class="d-flex align-items-center gap-2">
                                <i class="fas fa-asterisk text-danger"></i>
                                <span class="small"><strong><?= $tpl['required_tasks'] ?></strong> bắt buộc</span>
                            </div>
                            <div class="vr" style="height: 18px;"></div>
                            <div class="d-flex align-items-center gap-2 text-muted small">
                                <i class="fas fa-users-gear text-secondary"></i>
                                <span>IT • HR • HSE • Admin • Finance</span>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center pt-2">
                            <span class="text-muted small">
                                <i class="fas fa-calendar-alt me-1"></i>Cập nhật: <?= date('d/m/Y', strtotime($tpl['created_at'])) ?>
                            </span>
                            <div class="btn-group btn-group-sm">
                                <a href="<?= BASE_URL ?>/onboarding/editTemplate/<?= $tpl['id'] ?>" class="btn btn-outline-primary px-3">
                                    <i class="fas fa-edit me-1"></i> Chỉnh sửa & Thiết lập Tasks
                                </a>
                                <a href="<?= BASE_URL ?>/onboarding/deleteTemplate/<?= $tpl['id'] ?>" 
                                   class="btn btn-outline-danger" 
                                   onclick="return confirm('Bạn có chắc chắn muốn xóa/vô hiệu hóa mẫu quy trình này?');">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
