<?php
require_once APP_ROOT . '/views/ess/layout/header.php';
?>

<div class="container-xl">
    
    <!-- Header Page -->
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-2">
        <div>
            <h4 class="fw-bold mb-1 text-dark">
                <i class="fa-solid fa-bell text-primary me-2"></i> Trung tâm thông báo nội bộ
            </h4>
            <p class="text-muted small mb-0">Cập nhật quy định mới, tin tức hoạt động và các thông báo phê duyệt cá nhân.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>/ess/markAllNotificationsRead" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                <i class="fa-solid fa-check-double me-1"></i> Đánh dấu đã đọc tất cả
            </a>
            <a href="<?= BASE_URL ?>/ess/dashboard" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="fa-solid fa-arrow-left me-1"></i> Trang chủ
            </a>
        </div>
    </div>

    <!-- Nav Tabs -->
    <ul class="nav nav-pills mb-4 gap-2 bg-white p-2 rounded-3 shadow-sm border" id="notifTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active rounded-pill px-3 py-2 fw-semibold small" id="company-tab" data-bs-toggle="tab" data-bs-target="#tab-company" type="button" role="tab">
                <i class="fa-solid fa-bullhorn me-1"></i> 1. Thông báo công ty & An toàn HSE (<?= count($companyArticles ?? []) ?>)
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill px-3 py-2 fw-semibold small position-relative" id="personal-tab" data-bs-toggle="tab" data-bs-target="#tab-personal" type="button" role="tab">
                <i class="fa-solid fa-user-tag me-1"></i> 2. Thông báo cá nhân (<?= count($personalNotifications ?? []) ?>)
            </button>
        </li>
    </ul>

    <div class="tab-content" id="notifTabContent">
        
        <!-- TAB 1: THÔNG BÁO CÔNG TY -->
        <div class="tab-pane fade show active" id="tab-company" role="tabpanel">
            <div class="card-custom">
                <div class="card-custom-body p-0">
                    <?php if (!empty($companyArticles)): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($companyArticles as $art): ?>
                                <a href="<?= BASE_URL ?>/ess/viewArticle/<?= $art['id'] ?>" class="list-group-item list-group-item-action p-4 border-bottom">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <div>
                                            <?php if ($art['category'] === 'hse_rule'): ?>
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                                    <i class="fa-solid fa-shield-halved me-1"></i> Quy định An toàn HSE
                                                </span>
                                            <?php elseif ($art['category'] === 'notice'): ?>
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                                                    <i class="fa-solid fa-bullhorn me-1"></i> Thông báo Ban Giám đốc
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1">
                                                    <i class="fa-solid fa-newspaper me-1"></i> Tin tức Posung
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <small class="text-muted">
                                            <i class="fa-regular fa-clock me-1"></i>
                                            <?= date('d/m/Y H:i', strtotime($art['published_at'] ?? $art['created_at'])) ?>
                                        </small>
                                    </div>
                                    <h5 class="fw-bold text-dark mb-2"><?= htmlspecialchars($art['title']) ?></h5>
                                    <div class="text-muted small text-truncate" style="max-height: 40px;">
                                        <?= strip_tags($art['content']) ?>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5 text-muted small">
                            Chưa có thông báo nội bộ nào.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- TAB 2: THÔNG BÁO CÁ NHÂN -->
        <div class="tab-pane fade" id="tab-personal" role="tabpanel">
            <div class="card-custom">
                <div class="card-custom-body p-0">
                    <?php if (!empty($personalNotifications)): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($personalNotifications as $notif): ?>
                                <div class="list-group-item p-3 border-bottom d-flex align-items-center justify-content-between <?= empty($notif['is_read']) ? 'bg-light' : '' ?>">
                                    <div class="d-flex align-items-start gap-3">
                                        <div class="rounded-circle p-2 mt-1 <?= empty($notif['is_read']) ? 'bg-primary-subtle text-primary' : 'bg-light text-muted' ?>">
                                            <i class="fa-solid fa-envelope fs-5"></i>
                                        </div>
                                        <div>
                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                <strong class="text-dark small"><?= htmlspecialchars($notif['title']) ?></strong>
                                                <?php if (empty($notif['is_read'])): ?>
                                                    <span class="badge bg-danger rounded-pill" style="font-size: 0.65rem;">Mới</span>
                                                <?php endif; ?>
                                            </div>
                                            <p class="small text-secondary mb-1"><?= htmlspecialchars($notif['message']) ?></p>
                                            <small class="text-muted" style="font-size: 0.72rem;">
                                                <?= date('d/m/Y H:i', strtotime($notif['created_at'])) ?>
                                            </small>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <?php if (!empty($notif['link'])): ?>
                                            <a href="<?= BASE_URL . '/' . htmlspecialchars($notif['link']) ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                                Xem chi tiết
                                            </a>
                                        <?php endif; ?>
                                        <?php if (empty($notif['is_read'])): ?>
                                            <a href="<?= BASE_URL ?>/ess/markNotificationRead/<?= $notif['id'] ?>" class="btn btn-sm btn-light text-muted" title="Đánh dấu đã đọc">
                                                <i class="fa-solid fa-check"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5 text-muted small">
                            Bạn không có thông báo cá nhân nào.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>

</div>

<?php require_once APP_ROOT . '/views/ess/layout/footer.php'; ?>
