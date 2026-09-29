<?php
/**
 * ============================================================
 *  POSUNG HRIS – View: loan/report.php
 * ============================================================
 *  Báo cáo Phân tích Dư nợ Tạm ứng & Khoản vay Nhân viên
 * ============================================================
 */

$totalLent = (float)($stats['total_lent'] ?? 0);
$totalOutstanding = (float)($stats['total_outstanding'] ?? 0);
$totalRepaid = (float)($stats['total_repaid'] ?? 0);
$recoveryRate = ($totalLent > 0) ? round(($totalRepaid / $totalLent) * 100, 1) : 0;
?>
<div class="content-wrapper">
    <!-- TIÊU ĐỀ & THANH ĐIỀU HƯỚNG -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h2 class="mb-1 fw-bold text-dark"><i class="fas fa-chart-pie text-primary me-2"></i>Báo cáo Phân tích Dư nợ & Tạm ứng</h2>
            <p class="text-muted mb-0">Thống kê quy mô vốn vay, tiến độ thu hồi công nợ nội bộ theo phòng ban và chương trình phúc lợi</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>/loan" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Danh sách khoản vay
            </a>
            <button type="button" class="btn btn-outline-primary" onclick="window.print()">
                <i class="fas fa-print me-1"></i> Xuất Báo cáo
            </button>
        </div>
    </div>

    <!-- KHỐI KPI TỔNG HỢP TOÀN CÔNG TY -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 bg-white p-3 h-100 border-start border-4 border-primary">
                <span class="text-muted small fw-semibold text-uppercase">Tổng vốn đã giải ngân</span>
                <h3 class="fw-bold text-dark mb-0 mt-1"><?= number_format($totalLent, 0, ',', '.') ?> ₫</h3>
                <small class="text-primary mt-1 d-block"><i class="fas fa-arrow-trend-up me-1"></i>Toàn bộ các gói vay & tạm ứng</small>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 bg-white p-3 h-100 border-start border-4 border-danger">
                <span class="text-muted small fw-semibold text-uppercase">Dư nợ còn phải thu hồi</span>
                <h3 class="fw-bold text-danger mb-0 mt-1"><?= number_format($totalOutstanding, 0, ',', '.') ?> ₫</h3>
                <small class="text-danger mt-1 d-block"><i class="fas fa-scale-unbalanced me-1"></i><?= number_format($stats['active_count'] ?? 0) ?> hợp đồng đang thực hiện</small>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 bg-white p-3 h-100 border-start border-4 border-success">
                <span class="text-muted small fw-semibold text-uppercase">Đã thu hồi hoàn trả</span>
                <h3 class="fw-bold text-success mb-0 mt-1"><?= number_format($totalRepaid, 0, ',', '.') ?> ₫</h3>
                <small class="text-success mt-1 d-block"><i class="fas fa-circle-check me-1"></i>Đã khấu trừ lương và nộp quỹ</small>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 bg-white p-3 h-100 border-start border-4 border-info">
                <span class="text-muted small fw-semibold text-uppercase">Tỷ lệ thu hồi vốn vay</span>
                <h3 class="fw-bold text-info mb-0 mt-1"><?= $recoveryRate ?>%</h3>
                <div class="progress mt-2" style="height: 6px;">
                    <div class="progress-bar bg-info" style="width: <?= $recoveryRate ?>%"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <!-- BẢNG THỐNG KÊ THEO PHÒNG BAN -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="fas fa-sitemap text-primary me-2"></i>Dư nợ theo Phòng ban / Khối
                    </h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small text-muted text-uppercase">
                            <tr>
                                <th class="ps-3">Phòng ban</th>
                                <th class="text-center">Số khoản</th>
                                <th class="text-end">Tổng vay</th>
                                <th class="text-end pe-3">Dư nợ còn lại</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($deptSummary)): ?>
                                <tr>
                                    <td colspan="4" class="text-center py-3 text-muted">Chưa có dữ liệu.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($deptSummary as $dept): ?>
                                    <?php
                                    $deptRatio = ($totalOutstanding > 0) ? round(((float)$dept->total_outstanding / $totalOutstanding) * 100, 1) : 0;
                                    ?>
                                    <tr>
                                        <td class="ps-3 fw-semibold text-dark">
                                            <?= h($dept->dept_name) ?>
                                            <div class="progress mt-1" style="height: 4px; width: 100px;">
                                                <div class="progress-bar bg-primary" style="width: <?= $deptRatio ?>%"></div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-light text-dark border"><?= $dept->loan_count ?></span>
                                        </td>
                                        <td class="text-end text-muted">
                                            <?= number_format((float)$dept->total_lent, 0, ',', '.') ?> ₫
                                        </td>
                                        <td class="text-end pe-3 fw-bold text-danger">
                                            <?= number_format((float)$dept->total_outstanding, 0, ',', '.') ?> ₫
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- BẢNG THỐNG KÊ THEO LOẠI KHOẢN VAY -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="fas fa-tags text-primary me-2"></i>Dư nợ theo Loại hình Phúc lợi
                    </h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small text-muted text-uppercase">
                            <tr>
                                <th class="ps-3">Chương trình / Loại vay</th>
                                <th class="text-center">Số khoản</th>
                                <th class="text-end">Tổng vay</th>
                                <th class="text-end pe-3">Dư nợ còn lại</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($typeSummary)): ?>
                                <tr>
                                    <td colspan="4" class="text-center py-3 text-muted">Chưa có dữ liệu.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($typeSummary as $t): ?>
                                    <?php
                                    $tRatio = ($totalOutstanding > 0) ? round(((float)$t->total_outstanding / $totalOutstanding) * 100, 1) : 0;
                                    ?>
                                    <tr>
                                        <td class="ps-3 fw-semibold text-dark">
                                            <?= h($t->type_name) ?>
                                            <div class="progress mt-1" style="height: 4px; width: 100px;">
                                                <div class="progress-bar bg-warning" style="width: <?= $tRatio ?>%"></div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-light text-dark border"><?= $t->loan_count ?></span>
                                        </td>
                                        <td class="text-end text-muted">
                                            <?= number_format((float)$t->total_lent, 0, ',', '.') ?> ₫
                                        </td>
                                        <td class="text-end pe-3 fw-bold text-danger">
                                            <?= number_format((float)$t->total_outstanding, 0, ',', '.') ?> ₫
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
