<?php
require_once APP_ROOT . '/views/layouts/header.php';
?>

<div class="content-container">
    
    <!-- Header Page -->
    <div class="page-header d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3">
        <div>
            <h2 class="page-title fw-bold mb-1">
                <i class="fa-solid fa-bell text-primary me-2"></i> Trung tâm thông báo (Notification Center)
            </h2>
            <p class="text-muted small mb-0">Theo dõi thông báo hệ thống, cảnh báo hạn ngạch, nhắc việc và phê duyệt hồ sơ.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <?php if ($unreadCount > 0): ?>
                <a href="<?= BASE_URL ?>/notification/markAllRead" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                    <i class="fa-solid fa-check-double me-1"></i> Đánh dấu đã đọc tất cả
                </a>
            <?php endif; ?>
            <?php if (Session::isAdmin() || Session::isManager()): ?>
                <a href="<?= BASE_URL ?>/notification/settings" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
                    <i class="fa-solid fa-sliders me-1"></i> Cài đặt quy tắc
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Bộ Lọc Thông Báo -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
            <div class="row align-items-center g-3">
                <!-- Lọc Loại Thông Báo -->
                <div class="col-md-8">
                    <div class="d-flex flex-wrap gap-1">
                        <a href="<?= BASE_URL ?>/notification?type=all&status=<?= $statusFilter ?>" 
                           class="btn btn-sm rounded-pill px-3 <?= $typeFilter === 'all' ? 'btn-primary' : 'btn-light text-secondary' ?>">
                            Tất cả
                        </a>
                        <a href="<?= BASE_URL ?>/notification?type=reminder&status=<?= $statusFilter ?>" 
                           class="btn btn-sm rounded-pill px-3 <?= $typeFilter === 'reminder' ? 'btn-warning text-dark fw-bold' : 'btn-light text-secondary' ?>">
                            <i class="fa-solid fa-clock me-1"></i> Nhắc nhở hạn
                        </a>
                        <a href="<?= BASE_URL ?>/notification?type=approval&status=<?= $statusFilter ?>" 
                           class="btn btn-sm rounded-pill px-3 <?= $typeFilter === 'approval' ? 'btn-primary' : 'btn-light text-secondary' ?>">
                            <i class="fa-solid fa-clipboard-check me-1"></i> Cần phê duyệt
                        </a>
                        <a href="<?= BASE_URL ?>/notification?type=alert&status=<?= $statusFilter ?>" 
                           class="btn btn-sm rounded-pill px-3 <?= $typeFilter === 'alert' ? 'btn-danger' : 'btn-light text-secondary' ?>">
                            <i class="fa-solid fa-triangle-exclamation me-1"></i> Cảnh báo
                        </a>
                        <a href="<?= BASE_URL ?>/notification?type=system&status=<?= $statusFilter ?>" 
                           class="btn btn-sm rounded-pill px-3 <?= $typeFilter === 'system' ? 'btn-info text-white' : 'btn-light text-secondary' ?>">
                            <i class="fa-solid fa-circle-info me-1"></i> Hệ thống
                        </a>
                    </div>
                </div>

                <!-- Lọc Trạng Thái Đã Đọc -->
                <div class="col-md-4 text-md-end">
                    <div class="btn-group btn-group-sm rounded-pill p-1 bg-light border" role="group">
                        <a href="<?= BASE_URL ?>/notification?type=<?= $typeFilter ?>&status=all" 
                           class="btn rounded-pill px-3 <?= $statusFilter === 'all' ? 'btn-white shadow-sm fw-bold' : 'text-muted' ?>">
                            Tất cả
                        </a>
                        <a href="<?= BASE_URL ?>/notification?type=<?= $typeFilter ?>&status=unread" 
                           class="btn rounded-pill px-3 <?= $statusFilter === 'unread' ? 'btn-white shadow-sm fw-bold text-danger' : 'text-muted' ?>">
                            Chưa đọc (<?= $unreadCount ?>)
                        </a>
                        <a href="<?= BASE_URL ?>/notification?type=<?= $typeFilter ?>&status=read" 
                           class="btn rounded-pill px-3 <?= $statusFilter === 'read' ? 'btn-white shadow-sm fw-bold text-success' : 'text-muted' ?>">
                            Đã đọc
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Danh Sách Thông Báo -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
        <?php if (!empty($notifications)): ?>
            <div class="list-group list-group-flush">
                <?php foreach ($notifications as $n): ?>
                    <?php 
                        $iconClass = match($n['type']) {
                            'reminder' => 'fa-solid fa-clock text-warning',
                            'approval' => 'fa-solid fa-clipboard-check text-primary',
                            'alert'    => 'fa-solid fa-triangle-exclamation text-danger',
                            default    => 'fa-solid fa-circle-info text-info'
                        };
                        $bgIcon = match($n['type']) {
                            'reminder' => 'bg-warning-subtle',
                            'approval' => 'bg-primary-subtle',
                            'alert'    => 'bg-danger-subtle',
                            default    => 'bg-info-subtle'
                        };
                        $badgeLabel = match($n['type']) {
                            'reminder' => 'Nhắc việc',
                            'approval' => 'Phê duyệt',
                            'alert'    => 'Cảnh báo',
                            default    => 'Hệ thống'
                        };
                    ?>
                    <div class="list-group-item p-3 p-md-4 border-bottom d-flex align-items-start gap-3 transition-hover <?= empty($n['is_read']) ? 'bg-light' : 'bg-white' ?>">
                        <div class="rounded-circle p-3 d-flex align-items-center justify-content-center <?= $bgIcon ?>" style="width: 46px; height: 46px; flex-shrink: 0;">
                            <i class="<?= $iconClass ?> fs-5"></i>
                        </div>

                        <div class="flex-grow-1">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-1">
                                <div class="d-flex align-items-center gap-2">
                                    <h6 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($n['title']) ?></h6>
                                    <span class="badge <?= $bgIcon ?> border border-light-subtle rounded-pill small">
                                        <?= $badgeLabel ?>
                                    </span>
                                    <?php if (empty($n['is_read'])): ?>
                                        <span class="badge bg-danger rounded-pill" style="font-size: 0.65rem;">Mới</span>
                                    <?php endif; ?>
                                </div>
                                <small class="text-muted" style="font-size: 0.75rem;">
                                    <i class="fa-regular fa-clock me-1"></i>
                                    <?= date('d/m/Y H:i', strtotime($n['created_at'])) ?>
                                </small>
                            </div>

                            <p class="text-secondary small mb-2"><?= htmlspecialchars($n['message']) ?></p>

                            <div class="d-flex align-items-center gap-2">
                                <?php if (!empty($n['link'])): ?>
                                    <a href="<?= BASE_URL ?>/notification/markAsRead/<?= $n['id'] ?>" class="btn btn-sm btn-primary rounded-pill px-3 py-1" style="font-size: 0.75rem;">
                                        Xem chi tiết <i class="fa-solid fa-arrow-right ms-1"></i>
                                    </a>
                                <?php endif; ?>

                                <?php if (empty($n['is_read'])): ?>
                                    <a href="<?= BASE_URL ?>/notification/markAsRead/<?= $n['id'] ?>" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1" style="font-size: 0.75rem;">
                                        <i class="fa-solid fa-check me-1"></i> Đánh dấu đã đọc
                                    </a>
                                <?php else: ?>
                                    <small class="text-muted" style="font-size: 0.7rem;">
                                        <i class="fa-solid fa-circle-check text-success me-1"></i> Đã đọc lúc <?= !empty($n['read_at']) ? date('d/m/Y H:i', strtotime($n['read_at'])) : '' ?>
                                    </small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Phân Trang (Pagination) -->
            <?php if ($totalPages > 1): ?>
                <div class="card-footer bg-white p-3 border-top d-flex justify-content-between align-items-center">
                    <span class="small text-muted">
                        Hiển thị <?= count($notifications) ?> trên tổng số <?= $totalItems ?> thông báo
                    </span>
                    <nav>
                        <ul class="pagination pagination-sm mb-0">
                            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                                <li class="page-item <?= $p == $currentPage ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= BASE_URL ?>/notification?type=<?= $typeFilter ?>&status=<?= $statusFilter ?>&page=<?= $p ?>">
                                        <?= $p ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <div class="text-center py-5 text-muted">
                <i class="fa-regular fa-bell-slash fa-3x text-secondary opacity-50 mb-3"></i>
                <h5>Không có thông báo nào</h5>
                <p class="small">Bạn đã xem hết các thông báo hoặc không có thông báo phù hợp với bộ lọc hiện tại.</p>
            </div>
        <?php endif; ?>
    </div>

</div>

<?php require_once APP_ROOT . '/views/layouts/footer.php'; ?>
