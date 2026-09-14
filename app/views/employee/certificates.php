<!-- Certificates & HSE View -->
<?php if (!empty($expiring)): ?>
<div class="panel" style="margin-bottom:24px; border-color: rgba(245,158,11,0.3);">
    <div class="panel-header" style="background: rgba(245,158,11,0.08);">
        <h3><i class="fas fa-exclamation-triangle" style="color:var(--warning);"></i> Chứng chỉ sắp hết hạn (90 ngày)</h3>
    </div>
    <div class="panel-body">
        <div class="table-wrapper">
            <table>
                <thead><tr><th>Mã NV</th><th>Họ tên</th><th>Loại CC</th><th>Tên chứng chỉ</th><th>Hết hạn</th><th>Còn lại</th></tr></thead>
                <tbody>
                <?php foreach ($expiring as $c):
                    $daysLeft = (int)((strtotime($c->expiry_date) - time()) / 86400);
                    $cls = $daysLeft <= 30 ? 'text-danger' : 'text-warning';
                ?>
                <tr>
                    <td><strong><?= htmlspecialchars($c->emp_code) ?></strong></td>
                    <td><?= htmlspecialchars($c->full_name) ?></td>
                    <td><?= htmlspecialchars($c->cert_type) ?></td>
                    <td><?= htmlspecialchars($c->cert_name) ?></td>
                    <td class="<?= $cls ?>"><?= date('d/m/Y', strtotime($c->expiry_date)) ?></td>
                    <td class="<?= $cls ?>"><strong><?= $daysLeft ?> ngày</strong></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="panel">
    <div class="panel-header">
        <h3><i class="fas fa-certificate"></i> Tất cả chứng chỉ</h3>
    </div>
    <div class="panel-body">
        <?php if (empty($allCerts)): ?>
            <div class="empty-state"><i class="fas fa-certificate"></i><p>Chưa có chứng chỉ nào.</p></div>
        <?php else: ?>
            <div class="table-wrapper">
                <table id="certs-table">
                    <thead>
                        <tr>
                            <th>Mã NV</th><th>Họ tên</th><th>Loại</th><th>Tên chứng chỉ</th>
                            <th>Ngày cấp</th><th>Hết hạn</th><th>Cơ quan</th><th>Bắt buộc</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($allCerts as $c): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($c->emp_code) ?></strong></td>
                        <td>
                            <a href="<?= BASE_URL ?>/employee/show/<?= $c->employee_id ?>" class="emp-name-link">
                                <?= htmlspecialchars($c->full_name) ?>
                            </a>
                        </td>
                        <td><?= htmlspecialchars($c->cert_type) ?></td>
                        <td><?= htmlspecialchars($c->cert_name) ?></td>
                        <td><?= $c->issue_date ? date('d/m/Y', strtotime($c->issue_date)) : '—' ?></td>
                        <td>
                            <?php if ($c->expiry_date):
                                $d = (int)((strtotime($c->expiry_date) - time()) / 86400);
                                $cls = $d <= 0 ? 'text-danger' : ($d <= 90 ? 'text-warning' : '');
                            ?>
                                <span class="<?= $cls ?>"><?= date('d/m/Y', strtotime($c->expiry_date)) ?></span>
                            <?php else: ?>—<?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($c->issuing_authority ?? '—') ?></td>
                        <td style="text-align:center;"><?= $c->is_mandatory_site ? '<i class="fas fa-check-circle text-success"></i>' : '' ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.text-warning { color: var(--warning); }
.text-danger { color: var(--danger); }
.text-success { color: var(--success); }
.emp-name-link { color: var(--primary-light); font-weight: 500; }
.emp-name-link:hover { text-decoration: underline; }
</style>
