<div class="content-header">
    <div class="header-left">
        <h2><i class="fas fa-users-cog text-primary"></i> Quản lý Công nhân Thầu phụ</h2>
        <p>Kiểm soát chứng chỉ An toàn lao động & QR Code ra vào cổng.</p>
    </div>
    <div class="header-actions">
        <a href="<?= BASE_URL ?>/subcontractor" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Quay lại</a>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <h3>Danh sách công nhân (<?= count($workers) ?> người)</h3>
    </div>
    <div class="panel-body">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Mã CN</th>
                        <th>Họ và Tên</th>
                        <th>CMND/CCCD</th>
                        <th>Vị trí thi công</th>
                        <th>Trạng thái HSE</th>
                        <th>Ngày hết hạn HSE</th>
                        <th>Trạng thái</th>
                        <th>Thẻ QR</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($workers as $w): ?>
                        <tr>
                            <td><strong><?= h($w['worker_code']) ?></strong></td>
                            <td><?= h($w['full_name']) ?></td>
                            <td><?= h($w['id_card']) ?></td>
                            <td><?= h($w['pos_title']) ?></td>
                            <td>
                                <?php if($w['has_hse_cert']): ?>
                                    <span class="badge bg-success text-white"><i class="fas fa-check-circle"></i> Đã cấp thẻ</span>
                                <?php else: ?>
                                    <span class="badge bg-danger text-white"><i class="fas fa-times-circle"></i> Thiếu thẻ</span>
                                <?php endif; ?>
                            </td>
                            <td><?= $w['has_hse_cert'] ? date('d/m/Y', strtotime($w['hse_expiry_date'])) : '---' ?></td>
                            <td>
                                <span class="badge <?= $w['is_active'] ? 'badge-active' : 'badge-inactive' ?>">
                                    <?= $w['is_active'] ? 'Đang làm việc' : 'Đã nghỉ' ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-ghost text-primary" onclick="alert('Đã in thẻ QR Code: <?= h($w['qr_code']) ?>')">
                                    <i class="fas fa-qrcode"></i> In thẻ
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
.badge-active { background: rgba(16, 185, 129, 0.1); color: #059669; }
.badge-inactive { background: rgba(100, 116, 139, 0.1); color: #475569; }
.bg-success { background-color: var(--success) !important; }
.bg-danger { background-color: var(--danger) !important; }
</style>
