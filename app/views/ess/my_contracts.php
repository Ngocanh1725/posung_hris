<?php
require_once APP_ROOT . '/views/ess/layout/header.php';
?>

<div class="container-xl">
    
    <!-- Header Page -->
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-2">
        <div>
            <h4 class="fw-bold mb-1 text-dark">
                <i class="fa-solid fa-file-signature text-primary me-2"></i> Hợp đồng lao động của tôi
            </h4>
            <p class="text-muted small mb-0">Tra cứu quá trình ký kết và hiệu lực các hợp đồng lao động tại Posung E&C.</p>
        </div>
        <div>
            <a href="<?= BASE_URL ?>/ess/dashboard" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="fa-solid fa-arrow-left me-1"></i> Trang chủ
            </a>
        </div>
    </div>

    <!-- Danh sách Hợp đồng -->
    <div class="card-custom">
        <div class="card-custom-header">
            <h5 class="card-custom-title">
                <i class="fa-solid fa-folder-open text-primary"></i> Danh sách hợp đồng lao động
            </h5>
            <span class="badge bg-light text-muted border"><?= count($contracts) ?> bản ghi</span>
        </div>
        <div class="card-custom-body p-0">
            <?php if (!empty($contracts)): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th>Số hợp đồng</th>
                                <th>Loại hợp đồng</th>
                                <th>Ngày bắt đầu</th>
                                <th>Ngày kết thúc</th>
                                <th>Mức lương thỏa thuận</th>
                                <th class="text-center">Trạng thái</th>
                                <th class="text-center">Tệp đính kèm</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($contracts as $c): ?>
                                <tr>
                                    <td class="fw-bold font-monospace text-primary">
                                        <?= htmlspecialchars($c['contract_number'] ?? 'HDLD-' . $c['id']) ?>
                                    </td>
                                    <td>
                                        <span class="fw-semibold text-dark">
                                            <?= htmlspecialchars($c['contract_type_name'] ?? 'Hợp đồng lao động') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?= !empty($c['start_date']) ? date('d/m/Y', strtotime($c['start_date'])) : '—' ?>
                                    </td>
                                    <td>
                                        <?= !empty($c['end_date']) ? date('d/m/Y', strtotime($c['end_date'])) : '<span class="text-success fw-bold">Không thời hạn</span>' ?>
                                    </td>
                                    <td class="fw-semibold text-dark">
                                        <?= !empty($c['salary']) ? number_format($c['salary'], 0, ',', '.') . ' đ' : 'Theo thỏa thuận' ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if (($c['status'] ?? '') === 'Active'): ?>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                                <i class="fa-solid fa-circle-check me-1"></i> Đang hiệu lực
                                            </span>
                                        <?php elseif (($c['status'] ?? '') === 'Expired'): ?>
                                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1">
                                                Hết hạn
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary-subtle text-secondary px-2 py-1">
                                                Chấm dứt
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if (!empty($c['file_path']) && file_exists(APP_ROOT . '/../public/' . $c['file_path'])): ?>
                                            <a href="<?= BASE_URL . '/' . htmlspecialchars($c['file_path']) ?>" target="_blank" 
                                               class="btn btn-outline-primary btn-sm rounded-pill py-0 px-2" title="Tải hợp đồng">
                                                <i class="fa-solid fa-download me-1"></i> Tải về
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted small">Bản cứng HR lưu</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5 text-muted small">
                    <i class="fa-solid fa-file-contract fa-3x text-secondary opacity-50 mb-3"></i>
                    <p>Hiện chưa có thông tin hợp đồng lao động trên hệ thống.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<?php require_once APP_ROOT . '/views/ess/layout/footer.php'; ?>
