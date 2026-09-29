<?php
/**
 * POSUNG HRIS - Trang kết quả tìm kiếm toàn cục (Global Search Results)
 * File: app/views/search/index.php
 */
?>

<div class="container-fluid px-4 py-4">

    <!-- Search Header & Bar -->
    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
        <div class="row align-items-center g-3">
            <div class="col-lg-6">
                <h4 class="fw-bold mb-1 text-dark">
                    <i class="fa-solid fa-magnifying-glass text-primary me-2"></i> Kết quả tìm kiếm toàn cục
                </h4>
                <p class="text-muted small mb-0">
                    <?php if (!empty($query)): ?>
                        Tìm thấy <strong><?= $totalCount ?></strong> kết quả phù hợp cho từ khóa: <span class="badge bg-primary-subtle text-primary fs-6 px-3 py-1">"<?= htmlspecialchars($query) ?>"</span>
                    <?php else: ?>
                        Nhập từ khóa tìm kiếm nhân viên, phòng ban, dự án công trường hoặc khóa đào tạo.
                    <?php endif; ?>
                </p>
            </div>
            <div class="col-lg-6">
                <form action="<?= BASE_URL ?>/search" method="GET" class="position-relative">
                    <div class="input-group shadow-sm rounded-pill overflow-hidden border">
                        <span class="input-group-text bg-white border-0 ps-3">
                            <i class="fa-solid fa-magnifying-glass text-muted"></i>
                        </span>
                        <input type="text" name="q" class="form-control border-0 bg-white py-2" 
                               placeholder="Tìm tên, mã NV, phòng ban, dự án, khóa học... (Phím tắt: Ctrl+K hoặc /)" 
                               value="<?= htmlspecialchars($query) ?>" autofocus>
                        <button type="submit" class="btn btn-primary px-4 fw-semibold rounded-pill m-1">
                            Tìm kiếm
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php if (empty($query)): ?>
        <!-- Gợi ý tìm kiếm nhanh khi chưa nhập từ khóa -->
        <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white">
            <div class="text-primary display-4 mb-3"><i class="fa-solid fa-compass"></i></div>
            <h5 class="fw-bold text-dark">Khám phá thông tin nhanh chóng trên toàn hệ thống</h5>
            <p class="text-muted small mb-4" style="max-width: 600px; margin: 0 auto;">
                Bạn có thể gõ tên bất kỳ nhân sự, mã nhân viên (VD: POSUNG001), tên phòng ban, mã công trường hay các khóa huấn luyện an toàn.
            </p>
            <div class="d-flex flex-wrap justify-content-center gap-2">
                <a href="<?= BASE_URL ?>/search?q=Kỹ thuật" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                    <i class="fa-solid fa-tag me-1"></i> Phòng Kỹ thuật
                </a>
                <a href="<?= BASE_URL ?>/search?q=Samsung" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                    <i class="fa-solid fa-tag me-1"></i> Dự án Samsung
                </a>
                <a href="<?= BASE_URL ?>/search?q=HSE" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                    <i class="fa-solid fa-tag me-1"></i> Đào tạo HSE
                </a>
                <a href="<?= BASE_URL ?>/search?q=Giám đốc" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                    <i class="fa-solid fa-tag me-1"></i> Ban Giám đốc
                </a>
            </div>
        </div>
    <?php elseif ($totalCount === 0): ?>
        <!-- Không có kết quả -->
        <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white">
            <div class="text-muted display-4 mb-3"><i class="fa-solid fa-box-open"></i></div>
            <h5 class="fw-bold text-dark">Không tìm thấy kết quả nào cho "<?= htmlspecialchars($query) ?>"</h5>
            <p class="text-muted small mb-0">Vui lòng kiểm tra lại chính tả hoặc thử tìm kiếm với các từ khóa ngắn hơn, tổng quát hơn.</p>
        </div>
    <?php else: ?>
        <!-- Tabs lọc kết quả -->
        <ul class="nav nav-pills mb-4 gap-2 bg-white p-2 rounded-4 shadow-sm border" id="searchResultTabs" role="tablist">
            <li class="nav-item">
                <button class="nav-link active rounded-pill px-3 py-2 fw-semibold small" id="all-tab" data-bs-toggle="tab" data-bs-target="#tab-all" type="button">
                    <i class="fa-solid fa-layer-group me-1"></i> Tất cả kết quả (<?= $totalCount ?>)
                </button>
            </li>
            <?php if (($results['counts']['employees'] ?? 0) > 0): ?>
                <li class="nav-item">
                    <button class="nav-link rounded-pill px-3 py-2 fw-semibold small" id="emp-tab" data-bs-toggle="tab" data-bs-target="#tab-emp" type="button">
                        <i class="fa-solid fa-user me-1"></i> Nhân viên (<?= $results['counts']['employees'] ?>)
                    </button>
                </li>
            <?php endif; ?>
            <?php if (($results['counts']['departments'] ?? 0) > 0): ?>
                <li class="nav-item">
                    <button class="nav-link rounded-pill px-3 py-2 fw-semibold small" id="dept-tab" data-bs-toggle="tab" data-bs-target="#tab-dept" type="button">
                        <i class="fa-solid fa-sitemap me-1"></i> Phòng ban (<?= $results['counts']['departments'] ?>)
                    </button>
                </li>
            <?php endif; ?>
            <?php if (($results['counts']['projects'] ?? 0) > 0): ?>
                <li class="nav-item">
                    <button class="nav-link rounded-pill px-3 py-2 fw-semibold small" id="prj-tab" data-bs-toggle="tab" data-bs-target="#tab-prj" type="button">
                        <i class="fa-solid fa-building-shield me-1"></i> Dự án (<?= $results['counts']['projects'] ?>)
                    </button>
                </li>
            <?php endif; ?>
            <?php if (($results['counts']['trainings'] ?? 0) > 0): ?>
                <li class="nav-item">
                    <button class="nav-link rounded-pill px-3 py-2 fw-semibold small" id="trn-tab" data-bs-toggle="tab" data-bs-target="#tab-trn" type="button">
                        <i class="fa-solid fa-graduation-cap me-1"></i> Đào tạo (<?= $results['counts']['trainings'] ?>)
                    </button>
                </li>
            <?php endif; ?>
        </ul>

        <div class="tab-content" id="searchResultTabsContent">
            
            <!-- TAB TẤT CẢ KẾT QUẢ -->
            <div class="tab-pane fade show active" id="tab-all" role="tabpanel">
                
                <!-- Nhóm Nhân viên -->
                <?php if (!empty($results['results']['employees'])): ?>
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold text-dark mb-0">
                                <i class="fa-solid fa-user text-primary me-2"></i> Nhân viên (<?= count($results['results']['employees']) ?>)
                            </h6>
                            <a href="<?= BASE_URL ?>/employee" class="text-primary small text-decoration-none fw-semibold">Quản lý nhân sự &rarr;</a>
                        </div>
                        <div class="row g-3">
                            <?php foreach ($results['results']['employees'] as $item): ?>
                                <div class="col-md-6 col-lg-4">
                                    <a href="<?= $item['url'] ?>" class="card border-0 shadow-sm rounded-4 p-3 text-decoration-none text-dark h-100 transition-hover bg-white">
                                        <div class="d-flex align-items-center gap-3">
                                            <?php if (!empty($item['avatar'])): ?>
                                                <img src="<?= $item['avatar'] ?>" alt="" class="rounded-circle border" style="width: 46px; height: 46px; object-fit: cover;">
                                            <?php else: ?>
                                                <div class="rounded-circle bg-light border d-flex align-items-center justify-content-center text-primary fw-bold" style="width: 46px; height: 46px; flex-shrink: 0;">
                                                    <?= mb_substr($item['title'], 0, 1) ?>
                                                </div>
                                            <?php endif; ?>
                                            <div class="flex-grow-1 overflow-hidden">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <div class="fw-bold text-truncate"><?= htmlspecialchars($item['title']) ?></div>
                                                    <span class="badge <?= $item['badge_class'] ?> rounded-pill small ms-1"><?= $item['badge'] ?></span>
                                                </div>
                                                <div class="text-muted small text-truncate"><?= htmlspecialchars($item['subtitle']) ?></div>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Nhóm Phòng ban -->
                <?php if (!empty($results['results']['departments'])): ?>
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold text-dark mb-0">
                                <i class="fa-solid fa-sitemap text-info me-2"></i> Phòng ban & Cơ cấu tổ chức (<?= count($results['results']['departments']) ?>)
                            </h6>
                            <a href="<?= BASE_URL ?>/organization" class="text-primary small text-decoration-none fw-semibold">Sơ đồ tổ chức &rarr;</a>
                        </div>
                        <div class="row g-3">
                            <?php foreach ($results['results']['departments'] as $item): ?>
                                <div class="col-md-6 col-lg-4">
                                    <a href="<?= $item['url'] ?>" class="card border-0 shadow-sm rounded-4 p-3 text-decoration-none text-dark h-100 transition-hover bg-white">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="rounded-circle bg-info-subtle border border-info-subtle d-flex align-items-center justify-content-center text-info fs-5" style="width: 46px; height: 46px; flex-shrink: 0;">
                                                <i class="<?= $item['icon'] ?>"></i>
                                            </div>
                                            <div class="flex-grow-1 overflow-hidden">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <div class="fw-bold text-truncate"><?= htmlspecialchars($item['title']) ?></div>
                                                    <span class="badge bg-light text-muted border small"><?= $item['meta'] ?></span>
                                                </div>
                                                <div class="text-muted small text-truncate"><?= htmlspecialchars($item['subtitle']) ?></div>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Nhóm Dự án công trường -->
                <?php if (!empty($results['results']['projects'])): ?>
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold text-dark mb-0">
                                <i class="fa-solid fa-building-shield text-success me-2"></i> Dự án công trường (<?= count($results['results']['projects']) ?>)
                            </h6>
                            <a href="<?= BASE_URL ?>/project" class="text-primary small text-decoration-none fw-semibold">Tất cả dự án &rarr;</a>
                        </div>
                        <div class="row g-3">
                            <?php foreach ($results['results']['projects'] as $item): ?>
                                <div class="col-md-6 col-lg-4">
                                    <a href="<?= $item['url'] ?>" class="card border-0 shadow-sm rounded-4 p-3 text-decoration-none text-dark h-100 transition-hover bg-white">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="rounded-circle bg-success-subtle border border-success-subtle d-flex align-items-center justify-content-center text-success fs-5" style="width: 46px; height: 46px; flex-shrink: 0;">
                                                <i class="<?= $item['icon'] ?>"></i>
                                            </div>
                                            <div class="flex-grow-1 overflow-hidden">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <div class="fw-bold text-truncate"><?= htmlspecialchars($item['title']) ?></div>
                                                    <span class="badge bg-light text-primary border small"><?= $item['meta'] ?></span>
                                                </div>
                                                <div class="text-muted small text-truncate"><?= htmlspecialchars($item['subtitle']) ?></div>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Nhóm Đào tạo -->
                <?php if (!empty($results['results']['trainings'])): ?>
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold text-dark mb-0">
                                <i class="fa-solid fa-graduation-cap text-warning me-2"></i> Khóa đào tạo & Huấn luyện (<?= count($results['results']['trainings']) ?>)
                            </h6>
                            <a href="<?= BASE_URL ?>/training" class="text-primary small text-decoration-none fw-semibold">Quản lý đào tạo &rarr;</a>
                        </div>
                        <div class="row g-3">
                            <?php foreach ($results['results']['trainings'] as $item): ?>
                                <div class="col-md-6 col-lg-4">
                                    <a href="<?= $item['url'] ?>" class="card border-0 shadow-sm rounded-4 p-3 text-decoration-none text-dark h-100 transition-hover bg-white">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="rounded-circle bg-warning-subtle border border-warning-subtle d-flex align-items-center justify-content-center text-warning fs-5" style="width: 46px; height: 46px; flex-shrink: 0;">
                                                <i class="<?= $item['icon'] ?>"></i>
                                            </div>
                                            <div class="flex-grow-1 overflow-hidden">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <div class="fw-bold text-truncate"><?= htmlspecialchars($item['title']) ?></div>
                                                    <span class="badge bg-light text-muted border small"><?= $item['meta'] ?></span>
                                                </div>
                                                <div class="text-muted small text-truncate"><?= htmlspecialchars($item['subtitle']) ?></div>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

            </div>

            <!-- TAB RIÊNG NHÂN VIÊN -->
            <?php if (!empty($results['results']['employees'])): ?>
                <div class="tab-pane fade" id="tab-emp" role="tabpanel">
                    <div class="row g-3">
                        <?php foreach ($results['results']['employees'] as $item): ?>
                            <div class="col-md-6 col-lg-4">
                                <a href="<?= $item['url'] ?>" class="card border-0 shadow-sm rounded-4 p-3 text-decoration-none text-dark h-100 transition-hover bg-white">
                                    <div class="d-flex align-items-center gap-3">
                                        <?php if (!empty($item['avatar'])): ?>
                                            <img src="<?= $item['avatar'] ?>" alt="" class="rounded-circle border" style="width: 46px; height: 46px; object-fit: cover;">
                                        <?php else: ?>
                                            <div class="rounded-circle bg-light border d-flex align-items-center justify-content-center text-primary fw-bold" style="width: 46px; height: 46px; flex-shrink: 0;">
                                                <?= mb_substr($item['title'], 0, 1) ?>
                                            </div>
                                        <?php endif; ?>
                                        <div class="flex-grow-1 overflow-hidden">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <div class="fw-bold text-truncate"><?= htmlspecialchars($item['title']) ?></div>
                                                <span class="badge <?= $item['badge_class'] ?> rounded-pill small ms-1"><?= $item['badge'] ?></span>
                                            </div>
                                            <div class="text-muted small text-truncate"><?= htmlspecialchars($item['subtitle']) ?></div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- TAB RIÊNG PHÒNG BAN -->
            <?php if (!empty($results['results']['departments'])): ?>
                <div class="tab-pane fade" id="tab-dept" role="tabpanel">
                    <div class="row g-3">
                        <?php foreach ($results['results']['departments'] as $item): ?>
                            <div class="col-md-6 col-lg-4">
                                <a href="<?= $item['url'] ?>" class="card border-0 shadow-sm rounded-4 p-3 text-decoration-none text-dark h-100 transition-hover bg-white">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="rounded-circle bg-info-subtle border border-info-subtle d-flex align-items-center justify-content-center text-info fs-5" style="width: 46px; height: 46px; flex-shrink: 0;">
                                            <i class="<?= $item['icon'] ?>"></i>
                                        </div>
                                        <div class="flex-grow-1 overflow-hidden">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <div class="fw-bold text-truncate"><?= htmlspecialchars($item['title']) ?></div>
                                                <span class="badge bg-light text-muted border small"><?= $item['meta'] ?></span>
                                            </div>
                                            <div class="text-muted small text-truncate"><?= htmlspecialchars($item['subtitle']) ?></div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- TAB RIÊNG DỰ ÁN -->
            <?php if (!empty($results['results']['projects'])): ?>
                <div class="tab-pane fade" id="tab-prj" role="tabpanel">
                    <div class="row g-3">
                        <?php foreach ($results['results']['projects'] as $item): ?>
                            <div class="col-md-6 col-lg-4">
                                <a href="<?= $item['url'] ?>" class="card border-0 shadow-sm rounded-4 p-3 text-decoration-none text-dark h-100 transition-hover bg-white">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="rounded-circle bg-success-subtle border border-success-subtle d-flex align-items-center justify-content-center text-success fs-5" style="width: 46px; height: 46px; flex-shrink: 0;">
                                            <i class="<?= $item['icon'] ?>"></i>
                                        </div>
                                        <div class="flex-grow-1 overflow-hidden">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <div class="fw-bold text-truncate"><?= htmlspecialchars($item['title']) ?></div>
                                                <span class="badge bg-light text-primary border small"><?= $item['meta'] ?></span>
                                            </div>
                                            <div class="text-muted small text-truncate"><?= htmlspecialchars($item['subtitle']) ?></div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- TAB RIÊNG ĐÀO TẠO -->
            <?php if (!empty($results['results']['trainings'])): ?>
                <div class="tab-pane fade" id="tab-trn" role="tabpanel">
                    <div class="row g-3">
                        <?php foreach ($results['results']['trainings'] as $item): ?>
                            <div class="col-md-6 col-lg-4">
                                <a href="<?= $item['url'] ?>" class="card border-0 shadow-sm rounded-4 p-3 text-decoration-none text-dark h-100 transition-hover bg-white">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="rounded-circle bg-warning-subtle border border-warning-subtle d-flex align-items-center justify-content-center text-warning fs-5" style="width: 46px; height: 46px; flex-shrink: 0;">
                                            <i class="<?= $item['icon'] ?>"></i>
                                        </div>
                                        <div class="flex-grow-1 overflow-hidden">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <div class="fw-bold text-truncate"><?= htmlspecialchars($item['title']) ?></div>
                                                <span class="badge bg-light text-muted border small"><?= $item['meta'] ?></span>
                                            </div>
                                            <div class="text-muted small text-truncate"><?= htmlspecialchars($item['subtitle']) ?></div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    <?php endif; ?>

</div>

<style>
.transition-hover {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.transition-hover:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.08) !important;
}
</style>
