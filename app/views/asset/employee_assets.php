<?php
/**
 * ============================================================
 *  POSUNG HRIS – View: asset/employee_assets.php
 * ============================================================
 *  Danh sách Tài sản Cấp phát cho 1 Nhân sự Cụ thể
 * ============================================================
 */
?>
<div class="content-wrapper">
    <div class="mb-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <a href="<?= BASE_URL ?>/asset/report" class="text-decoration-none text-muted small">
                <i class="fas fa-arrow-left me-1"></i> Quay lại báo cáo kiểm kê
            </a>
            <h2 class="mt-2 fw-bold text-dark"><i class="fas fa-user-shield text-primary me-2"></i>Tài sản Cấp phát cho: <?= h($employee->full_name) ?></h2>
            <p class="text-muted mb-0">Mã NV: <span class="font-monospace fw-semibold"><?= h($employee->emp_code) ?></span> | Trạng thái NV: <span class="badge bg-secondary"><?= h($employee->status) ?></span></p>
        </div>
        <div>
            <a href="<?= BASE_URL ?>/employee/view/<?= $employee->id ?>#tab-12" class="btn btn-outline-primary">
                <i class="fas fa-id-card me-1"></i> Đến Hồ sơ 360°
            </a>
            <a href="<?= BASE_URL ?>/asset" class="btn btn-secondary">
                <i class="fas fa-boxes-stacked me-1"></i> Danh sách tài sản
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-laptop-house text-primary me-2"></i>Lịch sử & Hiện trạng Tài sản Được Bàn giao</h5>
            <span class="badge bg-primary fs-6"><?= count($assignments) ?> tài sản</span>
        </div>
        <div class="card-body p-0">
            <?php if (empty($assignments)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-box-open fa-3x mb-3 text-secondary"></i>
                    <p class="mb-0">Nhân viên này hiện chưa được bàn giao bất kỳ tài sản nào.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">STT</th>
                                <th>Mã tài sản</th>
                                <th>Tên & Model tài sản</th>
                                <th>Loại</th>
                                <th>Số Serial</th>
                                <th>Ngày bàn giao</th>
                                <th>Hiện trạng</th>
                                <th>Ngày thu hồi</th>
                                <th>Nguyên giá</th>
                                <th class="text-end pe-3">Biên bản</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $stt = 1; foreach ($assignments as $a): ?>
                                <tr>
                                    <td class="ps-3 text-muted"><?= $stt++ ?></td>
                                    <td>
                                        <a href="<?= BASE_URL ?>/asset/show/<?= $a->asset_id ?>" class="font-monospace fw-bold text-primary text-decoration-none">
                                            <?= h($a->asset_code) ?>
                                        </a>
                                    </td>
                                    <td class="fw-semibold text-dark"><?= h($a->asset_name) ?></td>
                                    <td>
                                        <small class="badge bg-light text-dark border">
                                            <i class="<?= h($a->category_icon ?? 'fas fa-cube') ?> me-1"></i><?= h($a->category_name) ?>
                                        </small>
                                    </td>
                                    <td class="font-monospace small"><?= h($a->serial_number ?: '---') ?></td>
                                    <td><?= fmtDate($a->assigned_date) ?></td>
                                    <td>
                                        <?php if ($a->return_date): ?>
                                            <span class="badge bg-secondary-subtle text-secondary border">Đã hoàn trả</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark"><i class="fas fa-user-check me-1"></i>Đang giữ</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= $a->return_date ? fmtDate($a->return_date) : '<span class="text-muted small">---</span>' ?>
                                    </td>
                                    <td class="font-monospace fw-medium">
                                        <?= number_format($a->purchase_cost ?? 0, 0, ',', '.') ?> ₫
                                    </td>
                                    <td class="text-end pe-3">
                                        <a href="<?= BASE_URL ?>/asset/handoverReceipt/<?= $a->id ?>" target="_blank" class="btn btn-sm btn-outline-secondary" title="In biên bản bàn giao">
                                            <i class="fas fa-print"></i>
                                        </a>
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
