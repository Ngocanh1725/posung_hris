<?php
/**
 * ============================================================
 *  POSUNG HRIS – View: expense/report.php
 * ============================================================
 *  Báo cáo Phân tích Chi phí Công tác & Chi phí Dự án
 * ============================================================
 */

$totalAmount = (float)($stats['total_amount'] ?? 0);
$totalNet = (float)($stats['total_net_payable'] ?? 0);
?>
<div class="content-wrapper">
    <!-- TIÊU ĐỀ & ĐIỀU HƯỚNG -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h2 class="mb-1 fw-bold text-dark"><i class="fas fa-chart-pie text-primary me-2"></i>Báo cáo Chi phí Công tác & Dự án</h2>
            <p class="text-muted mb-0">Thống kê chi phí công tác phí, vé tàu xe, lưu trú khách sạn và chi phí mua sắm vật tư tại các công trường</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>/expense" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Danh sách công tác & chi phí
            </a>
            <button type="button" class="btn btn-outline-primary" onclick="window.print()">
                <i class="fas fa-print me-1"></i> In Báo cáo
            </button>
        </div>
    </div>

    <!-- KHỐI KPI TỔNG HỢP -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 bg-white p-3 h-100 border-start border-4 border-primary">
                <span class="text-muted small fw-semibold text-uppercase">Tổng chi phí phát sinh</span>
                <h3 class="fw-bold text-dark mb-0 mt-1"><?= number_format($totalAmount, 0, ',', '.') ?> ₫</h3>
                <small class="text-primary mt-1 d-block"><i class="fas fa-receipt me-1"></i><?= $stats['total_claims'] ?? 0 ?> bảng quyết toán đã duyệt</small>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 bg-white p-3 h-100 border-start border-4 border-success">
                <span class="text-muted small fw-semibold text-uppercase">Đã thực chi thanh toán</span>
                <h3 class="fw-bold text-success mb-0 mt-1"><?= number_format($totalNet, 0, ',', '.') ?> ₫</h3>
                <small class="text-success mt-1 d-block"><i class="fas fa-check-double me-1"></i><?= $stats['paid_count'] ?? 0 ?> hồ sơ đã nhận tiền</small>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 bg-white p-3 h-100 border-start border-4 border-warning">
                <span class="text-muted small fw-semibold text-uppercase">Ngân sách công tác dự kiến</span>
                <h3 class="fw-bold text-warning mb-0 mt-1"><?= number_format((float)($travelStats['total_budget'] ?? 0), 0, ',', '.') ?> ₫</h3>
                <small class="text-muted mt-1 d-block"><i class="fas fa-route me-1"></i><?= $travelStats['total_requests'] ?? 0 ?> đợt công tác</small>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 bg-white p-3 h-100 border-start border-4 border-info">
                <span class="text-muted small fw-semibold text-uppercase">Tạm ứng đã cấp</span>
                <h3 class="fw-bold text-info mb-0 mt-1"><?= number_format((float)($travelStats['total_advance'] ?? 0), 0, ',', '.') ?> ₫</h3>
                <small class="text-muted mt-1 d-block">Đã nhận trước khi đi</small>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <!-- BẢNG PHÂN BỔ CHI PHÍ THEO DỰ ÁN -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="fas fa-hard-hat text-primary me-2"></i>Chi phí theo Dự án / Công trường
                    </h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small text-muted text-uppercase">
                            <tr>
                                <th class="ps-3">Dự án công trình</th>
                                <th class="text-center">Số hồ sơ</th>
                                <th class="text-end">Tổng chi</th>
                                <th class="text-end pe-3">Thực chi thanh toán</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($projectSummary)): ?>
                                <tr>
                                    <td colspan="4" class="text-center py-3 text-muted">Chưa có dữ liệu.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($projectSummary as $prj): ?>
                                    <?php
                                    $prjRatio = ($totalAmount > 0) ? round(((float)$prj->total_amount / $totalAmount) * 100, 1) : 0;
                                    ?>
                                    <tr>
                                        <td class="ps-3 fw-semibold text-dark">
                                            <?= h($prj->project_name) ?>
                                            <div class="progress mt-1" style="height: 4px; width: 120px;">
                                                <div class="progress-bar bg-primary" style="width: <?= $prjRatio ?>%"></div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-light text-dark border"><?= $prj->claims_count ?></span>
                                        </td>
                                        <td class="text-end fw-bold text-dark">
                                            <?= number_format((float)$prj->total_amount, 0, ',', '.') ?> ₫
                                        </td>
                                        <td class="text-end pe-3 text-success fw-semibold">
                                            <?= number_format((float)$prj->total_net_payable, 0, ',', '.') ?> ₫
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- BẢNG PHÂN BỔ CHI PHÍ THEO HẠNG MỤC -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="fas fa-tags text-primary me-2"></i>Chi phí theo Hạng mục (Khoản chi)
                    </h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small text-muted text-uppercase">
                            <tr>
                                <th class="ps-3">Hạng mục chi tiêu</th>
                                <th class="text-center">Số hóa đơn</th>
                                <th class="text-end pe-3">Tổng chi phí</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($categorySummary)): ?>
                                <tr>
                                    <td colspan="3" class="text-center py-3 text-muted">Chưa có dữ liệu.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($categorySummary as $cat): ?>
                                    <?php
                                    $catRatio = ($totalAmount > 0) ? round(((float)$cat->total_amount / $totalAmount) * 100, 1) : 0;
                                    $catLabel = match($cat->category) {
                                        'Transport' => '<i class="fas fa-plane-departure text-primary me-2"></i>Vé xe / Tàu / Máy bay',
                                        'Hotel'     => '<i class="fas fa-hotel text-info me-2"></i>Lưu trú khách sạn',
                                        'Meal'      => '<i class="fas fa-utensils text-success me-2"></i>Ăn uống & Tiếp khách',
                                        'Fuel'      => '<i class="fas fa-gas-pump text-warning me-2"></i>Xăng dầu đi lại',
                                        'Material'  => '<i class="fas fa-screwdriver-wrench text-danger me-2"></i>Vật tư cơ điện khẩn cấp',
                                        default     => '<i class="fas fa-tag text-secondary me-2"></i>Khác'
                                    };
                                    ?>
                                    <tr>
                                        <td class="ps-3 fw-semibold text-dark">
                                            <?= $catLabel ?>
                                            <div class="progress mt-1" style="height: 4px; width: 120px;">
                                                <div class="progress-bar bg-warning" style="width: <?= $catRatio ?>%"></div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-light text-dark border"><?= $cat->item_count ?> bill</span>
                                        </td>
                                        <td class="text-end pe-3 fw-bold text-dark">
                                            <?= number_format((float)$cat->total_amount, 0, ',', '.') ?> ₫
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
