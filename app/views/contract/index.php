<?php
/**
 * ============================================================
 *  View: contract/index.php
 * ============================================================
 */
?>

<div class="panel mb-4 position-relative overflow-hidden" style="border:none; box-shadow:0 10px 30px rgba(0,0,0,0.05); border-radius:var(--radius-lg);">
    <div class="panel-header d-flex justify-content-between align-items-center" style="background:linear-gradient(135deg, var(--primary), var(--accent)); color:#fff; padding:20px 24px;">
        <div>
            <h3 class="mb-1" style="color:#fff; font-weight:700;"><i class="fas fa-file-signature"></i> Quản lý Hợp đồng Nhân sự</h3>
            <p class="mb-0 text-white-50">Giám sát vòng đời hợp đồng của toàn bộ nhân sự</p>
        </div>
        <div>
            <a href="<?= BASE_URL ?>/employee" class="btn btn-light text-primary fw-bold" style="border-radius:8px;">
                <i class="fas fa-search"></i> Tìm Nhân sự để Ký HĐ
            </a>
        </div>
    </div>
    
    <div class="panel-body p-4">
        
        <?php if (!empty($expiringContracts)): ?>
            <div class="alert alert-warning border-warning" style="background:rgba(245,158,11,0.05); border-radius:12px;">
                <h5 class="alert-heading text-warning fw-bold"><i class="fas fa-exclamation-triangle"></i> Cảnh báo: <?= count($expiringContracts) ?> Hợp đồng sắp hết hạn</h5>
                <p class="mb-2">Danh sách nhân sự cần gia hạn hợp đồng trong 30 ngày tới:</p>
                <div class="table-responsive" style="max-height: 250px;">
                    <table class="table table-sm table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Mã NV</th>
                                <th>Họ Tên</th>
                                <th>Loại HĐ</th>
                                <th>Ngày hết hạn</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($expiringContracts as $exp): ?>
                            <tr>
                                <td><?= h($exp->emp_code) ?></td>
                                <td class="fw-bold"><?= h($exp->full_name) ?></td>
                                <td><?= h($exp->contract_type_name) ?></td>
                                <td class="text-danger fw-bold"><?= date('d/m/Y', strtotime($exp->end_date)) ?></td>
                                <td>
                                    <a href="<?= BASE_URL ?>/contract/employee/<?= $exp->employee_id ?>" class="btn btn-primary btn-sm rounded-pill"><i class="fas fa-sync-alt"></i> Gia hạn ngay</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <h5 class="mt-4 mb-3 fw-bold"><i class="fas fa-list text-secondary"></i> Tất cả Hợp đồng gần đây</h5>
        <div class="table-responsive">
            <table class="table table-hover align-middle table-modern" id="contractTable">
                <thead class="table-light">
                    <tr>
                        <th>Mã NV</th>
                        <th>Họ Tên</th>
                        <th>Loại HĐ</th>
                        <th>Số HĐ</th>
                        <th>Thời hạn</th>
                        <th>Lương Cơ bản</th>
                        <th>Trạng thái</th>
                        <th class="text-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($contracts as $c): ?>
                    <tr>
                        <td><span class="badge bg-secondary text-dark"><?= h($c->emp_code) ?></span></td>
                        <td class="fw-bold text-dark"><?= h($c->full_name) ?></td>
                        <td><?= h($c->contract_type_name) ?></td>
                        <td class="text-primary"><?= h($c->contract_number) ?></td>
                        <td>
                            <small class="d-block text-muted">Từ: <?= date('d/m/Y', strtotime($c->start_date)) ?></small>
                            <small class="d-block text-muted">Đến: <?= $c->end_date ? date('d/m/Y', strtotime($c->end_date)) : 'Vô thời hạn' ?></small>
                        </td>
                        <td><?= number_format($c->basic_salary) ?> đ</td>
                        <td>
                            <?php if ($c->status === 'Active'): ?>
                                <span class="badge bg-success">Đang hiệu lực</span>
                            <?php elseif ($c->status === 'Expired'): ?>
                                <span class="badge bg-warning text-dark">Hết hạn</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Đã chấm dứt</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <a href="<?= BASE_URL ?>/contract/employee/<?= $c->employee_id ?>" class="btn btn-sm btn-ghost" title="Xem chi tiết HĐ của NV">
                                <i class="fas fa-folder-open"></i> Hồ sơ HĐ
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    $('#contractTable').DataTable({
        language: { url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/vi.json' },
        pageLength: 25,
        order: [[4, 'desc']] // Sắp xếp theo ngày bắt đầu giảm dần
    });
});
</script>
