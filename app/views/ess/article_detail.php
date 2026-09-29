<?php
require_once APP_ROOT . '/views/ess/layout/header.php';
?>

<div class="container-xl" style="max-width: 850px;">
    
    <div class="mb-3">
        <a href="<?= BASE_URL ?>/ess/notifications" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            <i class="fa-solid fa-arrow-left me-1"></i> Quay lại danh sách thông báo
        </a>
    </div>

    <article class="card-custom p-4 p-md-5 bg-white">
        <!-- Metadata -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 border-bottom pb-3 mb-4">
            <div>
                <?php if ($article['category'] === 'hse_rule'): ?>
                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-1 rounded-pill">
                        <i class="fa-solid fa-shield-halved me-1"></i> Quy định An toàn HSE
                    </span>
                <?php elseif ($article['category'] === 'notice'): ?>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1 rounded-pill">
                        <i class="fa-solid fa-bullhorn me-1"></i> Thông báo Ban Giám đốc
                    </span>
                <?php else: ?>
                    <span class="badge bg-info-subtle text-info border border-info-subtle px-3 py-1 rounded-pill">
                        <i class="fa-solid fa-newspaper me-1"></i> Tin tức Posung
                    </span>
                <?php endif; ?>
            </div>
            <div class="text-muted small">
                <span><i class="fa-regular fa-clock me-1"></i> Ngày đăng: <?= date('d/m/Y H:i', strtotime($article['published_at'] ?? $article['created_at'])) ?></span>
                <?php if (!empty($article['author_name'])): ?>
                    <span class="ms-3"><i class="fa-regular fa-user me-1"></i> Người đăng: <?= htmlspecialchars($article['author_name']) ?></span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Article Title -->
        <h2 class="fw-bold text-dark mb-4 lh-base">
            <?= htmlspecialchars($article['title']) ?>
        </h2>

        <!-- Article Body -->
        <div class="article-content text-dark lh-lg" style="font-size: 1.02rem;">
            <?= $article['content'] ?>
        </div>

        <?php if (!empty($article['attachment_path'])): ?>
            <div class="mt-4 p-3 bg-light rounded-3 border">
                <div class="fw-semibold small mb-2"><i class="fa-solid fa-paperclip text-primary me-1"></i> Tệp đính kèm:</div>
                <a href="<?= BASE_URL . '/' . htmlspecialchars($article['attachment_path']) ?>" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill">
                    <i class="fa-solid fa-download me-1"></i> Tải tài liệu đính kèm
                </a>
            </div>
        <?php endif; ?>

        <div class="mt-5 pt-3 border-top d-flex justify-content-between align-items-center">
            <span class="text-muted small fst-italic">Ban Giám đốc & Phòng Nhân sự Posung E&C</span>
            <a href="<?= BASE_URL ?>/ess/notifications" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                <i class="fa-solid fa-list me-1"></i> Xem các thông báo khác
            </a>
        </div>
    </article>

</div>

<?php require_once APP_ROOT . '/views/ess/layout/footer.php'; ?>
