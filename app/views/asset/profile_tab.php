<?php
/**
 * ============================================================
 *  POSUNG HRIS – View: asset/profile_tab.php
 * ============================================================
 *  Tab 12: Quản lý Tài sản Doanh nghiệp cấp cho Nhân viên
 *  (Tích hợp trong Hồ sơ Nhân viên 360 độ)
 * ============================================================
 */
$assetList = $employee->assets ?? [];
$activeAssets = $employee->active_assets ?? [];
$totalActiveValue = 0;
foreach ($activeAssets as $aa) {
    $totalActiveValue += (float)($aa->purchase_cost ?? 0);
}
?>

<div class="asset-profile-tab">
    <!-- KHỐI THỐNG KÊ NHANH TÀI SẢN NHÂN VIÊN ĐANG GIỮ -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 bg-light rounded-3 p-3">
                <span class="text-muted small fw-semibold text-uppercase">Tài sản đang giữ (Chưa hoàn trả)</span>
                <div class="d-flex align-items-center justify-content-between mt-1">
                    <h3 class="fw-bold mb-0 text-primary"><?= count($activeAssets) ?></h3>
                    <div class="fs-4 text-primary bg-white rounded-circle p-2 shadow-sm">
                        <i class="fas fa-user-check"></i>
                    </div>
                </div>
                <small class="text-muted">Cần thu hồi khi nghỉ việc</small>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 bg-light rounded-3 p-3">
                <span class="text-muted small fw-semibold text-uppercase">Tổng giá trị tài sản đang quản lý</span>
                <div class="d-flex align-items-center justify-content-between mt-1">
                    <h3 class="fw-bold mb-0 text-success"><?= number_format($totalActiveValue, 0, ',', '.') ?> ₫</h3>
                    <div class="fs-4 text-success bg-white rounded-circle p-2 shadow-sm">
                        <i class="fas fa-vault"></i>
                    </div>
                </div>
                <small class="text-muted">Theo nguyên giá bàn giao</small>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 bg-light rounded-3 p-3">
                <span class="text-muted small fw-semibold text-uppercase">Tổng số đợt cấp phát lịch sử</span>
                <div class="d-flex align-items-center justify-content-between mt-1">
                    <h3 class="fw-bold mb-0 text-dark"><?= count($assetList) ?></h3>
                    <div class="fs-4 text-secondary bg-white rounded-circle p-2 shadow-sm">
                        <i class="fas fa-history"></i>
                    </div>
                </div>
                <small class="text-muted">Bao gồm cả tài sản đã hoàn trả</small>
            </div>
        </div>
    </div>

    <!-- DANH SÁCH TÀI SẢN -->
    <div class="card border rounded-3 mb-3">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-laptop-house text-primary me-2"></i>Danh mục Tài sản Công ty Được Bàn giao</h5>
                <small class="text-muted">Máy tính, laptop, điện thoại, máy thi công, thẻ ra vào, xe cộ...</small>
            </div>
            <div>
                <a href="<?= BASE_URL ?>/asset" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-1"></i> Bàn giao tài sản mới
                </a>
            </div>
        </div>

        <div class="card-body p-0">
            <?php if (empty($assetList)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-box-open fa-3x mb-3 text-secondary"></i>
                    <p class="mb-0">Nhân viên này chưa từng được bàn giao tài sản nào.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3" style="width: 50px;">STT</th>
                                <th>Mã tài sản</th>
                                <th>Tên & Model tài sản</th>
                                <th>Nhóm loại</th>
                                <th>Số Serial / IMEI</th>
                                <th>Ngày giao</th>
                                <th>Trạng thái</th>
                                <th>Ngày trả</th>
                                <th>Tình trạng</th>
                                <th class="text-end">Nguyên giá</th>
                                <th class="text-end pe-3" style="width: 140px;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $stt = 1; foreach ($assetList as $ast): ?>
                                <tr>
                                    <td class="ps-3 text-muted"><?= $stt++ ?></td>
                                    <td>
                                        <a href="<?= BASE_URL ?>/asset/show/<?= $ast->asset_id ?>" class="font-monospace fw-bold text-primary text-decoration-none">
                                            <?= h($ast->asset_code) ?>
                                        </a>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark"><?= h($ast->asset_name) ?></div>
                                        <?php if (!empty($ast->notes)): ?>
                                            <small class="text-muted fst-italic"><?= h($ast->notes) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            <i class="<?= h($ast->category_icon ?? 'fas fa-cube') ?> me-1"></i><?= h($ast->category_name) ?>
                                        </span>
                                    </td>
                                    <td class="font-monospace small"><?= h($ast->serial_number ?: '---') ?></td>
                                    <td class="fw-medium"><?= fmtDate($ast->assigned_date) ?></td>
                                    <td>
                                        <?php if ($ast->return_date): ?>
                                            <span class="badge bg-secondary-subtle text-secondary border">Đã hoàn trả</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i>Đang sử dụng</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= $ast->return_date ? fmtDate($ast->return_date) : '<span class="text-muted small">---</span>' ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            <?= h($ast->condition_on_assign ?? 'Good') ?>
                                        </span>
                                    </td>
                                    <td class="font-monospace fw-medium text-end">
                                        <?= number_format($ast->purchase_cost ?? 0, 0, ',', '.') ?> ₫
                                    </td>
                                    <td class="text-end pe-3">
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= BASE_URL ?>/asset/handoverReceipt/<?= $ast->id ?>" target="_blank" class="btn btn-light" title="In Biên bản bàn giao">
                                                <i class="fas fa-print text-secondary"></i>
                                            </a>
                                            <a href="<?= BASE_URL ?>/asset/show/<?= $ast->asset_id ?>" class="btn btn-light" title="Xem chi tiết tài sản">
                                                <i class="fas fa-eye text-primary"></i>
                                            </a>
                                        </div>
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
