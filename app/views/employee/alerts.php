<!-- ══════════════════════════════════════════════════════════
     CẢNH BÁO HẾT HẠN (V2)
     ══════════════════════════════════════════════════════════ -->
<div class="breadcrumb-bar">
    <a href="<?= BASE_URL ?>/employee"><i class="fas fa-users"></i> Nhân sự</a>
    <i class="fas fa-chevron-right"></i>
    <span>Cảnh báo hết hạn</span>
</div>

<div class="panel-header" style="margin-bottom: 24px;">
    <h2><i class="fas fa-bell text-warning"></i> Trung tâm Cảnh báo (Expiry Alert Engine)</h2>
</div>

<!-- Lưới thẻ thống kê nhanh -->
<div class="dashboard-widgets" style="margin-bottom: 24px;">
    <?php
    $countRed = $countOrange = $countYellow = $countPurple = 0;
    foreach ($expiringDocs as $doc) {
        if ($doc->alert_level === 'purple') $countPurple++;
        elseif ($doc->alert_level === 'red') $countRed++;
        elseif ($doc->alert_level === 'orange') $countOrange++;
        elseif ($doc->alert_level === 'yellow') $countYellow++;
    }
    ?>
    <div class="widget" style="background: rgba(139, 92, 246, 0.1); border: 1px solid #8b5cf6;">
        <div class="widget-icon" style="background: #8b5cf6; color:#fff;"><i class="fas fa-times-circle"></i></div>
        <div class="widget-info">
            <span class="widget-label" style="color: #8b5cf6;">Đã hết hạn</span>
            <span class="widget-value"><?= $countPurple ?></span>
        </div>
    </div>
    <div class="widget" style="background: rgba(239, 68, 68, 0.1); border: 1px solid #ef4444;">
        <div class="widget-icon" style="background: #ef4444; color:#fff;"><i class="fas fa-exclamation-circle"></i></div>
        <div class="widget-info">
            <span class="widget-label" style="color: #ef4444;">Khẩn cấp (<= 15 ngày)</span>
            <span class="widget-value"><?= $countRed ?></span>
        </div>
    </div>
    <div class="widget" style="background: rgba(245, 158, 11, 0.1); border: 1px solid #f59e0b;">
        <div class="widget-icon" style="background: #f59e0b; color:#fff;"><i class="fas fa-exclamation-triangle"></i></div>
        <div class="widget-info">
            <span class="widget-label" style="color: #f59e0b;">Cảnh báo (<= 30 ngày)</span>
            <span class="widget-value"><?= $countOrange ?></span>
        </div>
    </div>
    <div class="widget" style="background: rgba(234, 179, 8, 0.1); border: 1px solid #eab308;">
        <div class="widget-icon" style="background: #eab308; color:#fff;"><i class="fas fa-info-circle"></i></div>
        <div class="widget-info">
            <span class="widget-label" style="color: #eab308;">Nhắc nhở (<= 60 ngày)</span>
            <span class="widget-value"><?= $countYellow ?></span>
        </div>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <h3><i class="fas fa-file-signature"></i> Danh sách Giấy tờ / Chứng chỉ sắp hết hạn</h3>
    </div>
    <div class="panel-body">
        <?php if (empty($expiringDocs)): ?>
            <div class="empty-state">
                <i class="fas fa-check-circle" style="color:#10b981;"></i>
                <p>Tất cả giấy tờ đều còn hạn an toàn.</p>
            </div>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Mã NV</th>
                        <th>Họ & Tên</th>
                        <th>Loại Giấy tờ</th>
                        <th>Tên Giấy tờ</th>
                        <th>Ngày hết hạn</th>
                        <th>Còn lại</th>
                        <th>Mức độ</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($expiringDocs as $doc): ?>
                    <tr>
                        <td><strong><?= h($doc->emp_code) ?></strong></td>
                        <td>
                            <a href="<?= BASE_URL ?>/employee/detail/<?= $doc->employee_id ?>" class="text-link">
                                <?= h($doc->full_name) ?>
                            </a>
                        </td>
                        <td><?= h($doc->doc_type) ?></td>
                        <td><?= h($doc->doc_name) ?></td>
                        <td style="font-weight:600;"><?= date('d/m/Y', strtotime($doc->expiry_date)) ?></td>
                        <td>
                            <?php if ($doc->days_left < 0): ?>
                                Trễ <?= abs($doc->days_left) ?> ngày
                            <?php elseif ($doc->days_left == 0): ?>
                                Hết hạn hôm nay
                            <?php else: ?>
                                <?= $doc->days_left ?> ngày
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php 
                                $colorMap = [
                                    'purple' => '#8b5cf6',
                                    'red'    => '#ef4444',
                                    'orange' => '#f59e0b',
                                    'yellow' => '#eab308'
                                ];
                                $c = $colorMap[$doc->alert_level] ?? '#6b7280';
                            ?>
                            <span style="background: <?= $c ?>; color:#fff; padding:4px 10px; border-radius:20px; font-size:0.75rem; font-weight:bold;">
                                <?= $doc->alert_label ?>
                            </span>
                        </td>
                        <td>
                            <a href="<?= BASE_URL ?>/employee/detail/<?= $doc->employee_id ?>" class="btn btn-sm btn-secondary"><i class="fas fa-eye"></i> Xem HS</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<div class="panel" style="margin-top: 24px;">
    <div class="panel-header">
        <h3><i class="fas fa-user-clock"></i> Cảnh báo Hưu trí (Trong vòng 12 tháng)</h3>
    </div>
    <div class="panel-body">
        <?php if (empty($retiringAlerts)): ?>
            <div class="empty-state">
                <i class="fas fa-check-circle" style="color:#10b981;"></i>
                <p>Không có nhân sự nào sắp đến tuổi hưu.</p>
            </div>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Mã NV</th>
                        <th>Họ & Tên</th>
                        <th>Giới tính</th>
                        <th>Ngày sinh</th>
                        <th>Tuổi hiện tại</th>
                        <th>Tháng còn lại</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($retiringAlerts as $r): ?>
                    <tr>
                        <td><strong><?= h($r['emp_code']) ?></strong></td>
                        <td>
                            <a href="<?= BASE_URL ?>/employee/detail/<?= $r['id'] ?>" class="text-link">
                                <?= h($r['full_name']) ?>
                            </a>
                        </td>
                        <td><?= h($r['gender'] == 'Male' ? 'Nam' : 'Nữ') ?></td>
                        <td><?= date('d/m/Y', strtotime($r['birth_date'])) ?></td>
                        <td><?= floor($r['age_months'] / 12) ?> tuổi <?= $r['age_months'] % 12 ?> tháng</td>
                        <td>
                            <?php if ($r['months_to_retire'] <= 0): ?>
                                <span class="badge-danger">Đã đến tuổi hưu</span>
                            <?php else: ?>
                                <span class="badge-warning">Còn <?= $r['months_to_retire'] ?> tháng</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<style>
.dashboard-widgets { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; }
.widget { display: flex; align-items: center; padding: 20px; border-radius: 12px; gap: 16px; }
.widget-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }
.widget-info { display: flex; flex-direction: column; }
.widget-label { font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; }
.widget-value { font-size: 1.8rem; font-weight: 800; color: var(--text-primary); line-height: 1; }
</style>
