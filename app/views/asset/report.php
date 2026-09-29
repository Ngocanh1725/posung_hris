<?php
/**
 * ============================================================
 *  POSUNG HRIS – View: asset/report.php
 * ============================================================
 *  Báo cáo Kiểm kê Tài sản Doanh nghiệp & Thống kê BI
 * ============================================================
 */

$ov = $overview;
$utilizationRate = ($ov->total_assets > 0) ? round(($ov->total_assigned / $ov->total_assets) * 100, 1) : 0;
?>
<div class="content-wrapper">
    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <a href="<?= BASE_URL ?>/asset" class="text-decoration-none text-muted small">
                <i class="fas fa-arrow-left me-1"></i> Quay lại danh sách tài sản
            </a>
            <h2 class="mt-2 fw-bold text-dark"><i class="fas fa-chart-pie text-primary me-2"></i>Báo cáo Kiểm kê Tài sản Công ty</h2>
            <p class="text-muted mb-0">Thống kê hiện trạng tài sản, giá trị khấu hao và định mức cấp phát nhân sự</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
                <i class="fas fa-print me-1"></i> In báo cáo kiểm kê
            </button>
            <a href="<?= BASE_URL ?>/asset" class="btn btn-primary">
                <i class="fas fa-boxes-stacked me-1"></i> Quản lý tài sản
            </a>
        </div>
    </div>

    <!-- 4 CARDS TỔNG QUAN -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 bg-white p-3 h-100 border-start border-4 border-primary">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Tổng lượng tài sản</span>
                        <h3 class="fw-bold mb-0 text-dark mt-1"><?= number_format($ov->total_assets) ?></h3>
                        <small class="text-muted">Kho: <?= number_format($ov->total_available) ?> | Đang dùng: <?= number_format($ov->total_assigned) ?></small>
                    </div>
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 text-primary fs-3">
                        <i class="fas fa-boxes-stacked"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 bg-white p-3 h-100 border-start border-4 border-success">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Tổng nguyên giá đầu tư</span>
                        <h3 class="fw-bold mb-0 text-success mt-1"><?= number_format($ov->total_value, 0, ',', '.') ?> ₫</h3>
                        <small class="text-muted">Giá trị tài sản toàn hệ thống</small>
                    </div>
                    <div class="rounded-circle bg-success bg-opacity-10 p-3 text-success fs-3">
                        <i class="fas fa-vault"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 bg-white p-3 h-100 border-start border-4 border-warning">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Tỷ lệ đưa vào sử dụng</span>
                        <h3 class="fw-bold mb-0 text-warning mt-1"><?= $utilizationRate ?>%</h3>
                        <small class="text-muted">Giá trị đang cấp: <?= number_format($ov->assigned_value, 0, ',', '.') ?> ₫</small>
                    </div>
                    <div class="rounded-circle bg-warning bg-opacity-10 p-3 text-warning fs-3">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 bg-white p-3 h-100 border-start border-4 border-danger">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Bảo dưỡng & Hỏng hóc</span>
                        <h3 class="fw-bold mb-0 text-danger mt-1"><?= number_format($ov->total_maintenance) ?></h3>
                        <small class="text-muted">Thanh lý: <?= number_format($ov->total_disposed) ?></small>
                    </div>
                    <div class="rounded-circle bg-danger bg-opacity-10 p-3 text-danger fs-3">
                        <i class="fas fa-screwdriver-wrench"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- BẢNG 1: CƠ CẤU THEO LOẠI TÀI SẢN -->
    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-sitemap text-primary me-2"></i>Cơ cấu theo Danh mục Loại Tài sản</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Loại tài sản</th>
                                <th class="text-center">Số lượng</th>
                                <th class="text-center">Đang cấp</th>
                                <th class="text-end pe-3">Tổng giá trị (VNĐ)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($byCategory as $bc): ?>
                                <?php 
                                $catPct = ($ov->total_assets > 0) ? round(($bc->count / $ov->total_assets) * 100, 1) : 0;
                                ?>
                                <tr>
                                    <td class="ps-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="<?= h($bc->icon ?: 'fas fa-box') ?> text-primary"></i>
                                            <div>
                                                <strong><?= h($bc->name) ?></strong>
                                                <div class="progress mt-1" style="height: 4px; width: 120px;">
                                                    <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $catPct ?>%"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center fw-semibold"><?= number_format($bc->count) ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-warning text-dark"><?= number_format($bc->assigned_count) ?></span>
                                    </td>
                                    <td class="text-end pe-3 font-monospace fw-medium">
                                        <?= number_format($bc->total_cost, 0, ',', '.') ?> ₫
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- BẢNG 2: CẢNH BÁO BẢO HÀNH -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-triangle-exclamation text-warning me-2"></i>Tài sản Sắp / Đã Hết Hạn Bảo Hành</h5>
                    <span class="badge bg-danger"><?= count($warrantyAlerts) ?> thiết bị</span>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($warrantyAlerts)): ?>
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-shield-heart fa-2x mb-2 text-success"></i>
                            <p class="mb-0">Tất cả tài sản đều đang trong thời hạn bảo hành an toàn.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3">Mã & Tên tài sản</th>
                                        <th>Loại</th>
                                        <th>Hạn BH</th>
                                        <th class="text-end pe-3">Tình trạng BH</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($warrantyAlerts as $wa): ?>
                                        <?php 
                                        $dDiff = (strtotime($wa->warranty_expiry) - time()) / 86400;
                                        ?>
                                        <tr>
                                            <td class="ps-3">
                                                <a href="<?= BASE_URL ?>/asset/show/<?= $wa->id ?>" class="font-monospace fw-bold text-decoration-none text-dark">
                                                    <?= h($wa->asset_code) ?>
                                                </a>
                                                <small class="text-muted d-block text-truncate" style="max-width: 200px;"><?= h($wa->name) ?></small>
                                            </td>
                                            <td><small class="badge bg-light text-dark border"><?= h($wa->category_name) ?></small></td>
                                            <td class="fw-medium font-monospace"><?= fmtDate($wa->warranty_expiry) ?></td>
                                            <td class="text-end pe-3">
                                                <?php if ($dDiff < 0): ?>
                                                    <span class="badge bg-danger">Hết hạn <?= abs(round($dDiff)) ?> ngày</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning text-dark">Còn <?= round($dDiff) ?> ngày</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- BẢNG 3: TOP NHÂN VIÊN ĐANG GIỮ NHIỀU TÀI SẢN -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-users-viewfinder text-primary me-2"></i>Thống kê Tài sản Đang Cấp phát theo Nhân sự</h5>
            <span class="text-muted small">Top cán bộ, nhân viên đang quản lý tài sản có giá trị lớn</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Mã NV</th>
                        <th>Họ và tên nhân sự</th>
                        <th>Phòng ban</th>
                        <th class="text-center">Số lượng tài sản đang giữ</th>
                        <th class="text-end">Tổng giá trị quản lý</th>
                        <th class="text-end pe-3">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($topHolders)): ?>
                        <tr><td colspan="6" class="text-center py-4 text-muted">Hiện chưa có nhân sự nào đang giữ tài sản.</td></tr>
                    <?php else: ?>
                        <?php foreach ($topHolders as $th): ?>
                            <tr>
                                <td class="ps-3 font-monospace fw-bold text-primary"><?= h($th->emp_code) ?></td>
                                <td>
                                    <a href="<?= BASE_URL ?>/employee/view/<?= $th->id ?>" class="text-dark text-decoration-none fw-semibold">
                                        <i class="fas fa-user-circle text-muted me-1"></i><?= h($th->full_name) ?>
                                    </a>
                                </td>
                                <td><span class="text-muted"><?= h($th->dept_name ?? '---') ?></span></td>
                                <td class="text-center">
                                    <span class="badge bg-primary fs-6 px-3 py-1"><?= number_format($th->holding_count) ?></span>
                                </td>
                                <td class="text-end font-monospace fw-bold text-success">
                                    <?= number_format($th->total_holding_value, 0, ',', '.') ?> ₫
                                </td>
                                <td class="text-end pe-3">
                                    <a href="<?= BASE_URL ?>/asset/employeeAssets/<?= $th->id ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-list-check me-1"></i> Xem tài sản
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
