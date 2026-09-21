<!-- Expat Management View -->
<div class="panel">
    <div class="panel-header">
        <h3><i class="fas fa-passport"></i> Danh sách Chuyên gia nước ngoài (Expat)</h3>
    </div>
    <div class="panel-body">
        <?php if (empty($expats)): ?>
            <div class="empty-state"><i class="fas fa-globe-asia"></i><p>Chưa có chuyên gia nước ngoài nào.</p></div>
        <?php else: ?>
            <div class="table-wrapper">
                <table id="expats-table">
                    <thead>
                        <tr>
                            <th>Mã NV</th>
                            <th>Họ và tên</th>
                            <th>Quốc tịch</th>
                            <th>Hộ chiếu</th>
                            <th>Visa hết hạn</th>
                            <th>GPLĐ hết hạn</th>
                            <th>TRC hết hạn</th>
                            <th>Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($expats as $ex): ?>
                        <tr>
                            <td><strong><?= h($ex->emp_code) ?></strong></td>
                            <td>
                                <a href="<?= BASE_URL ?>/employee/detail/<?= $ex->id ?>" class="emp-name-link">
                                    <?= h($ex->full_name) ?>
                                </a>
                            </td>
                            <td><?= h($ex->nationality) ?></td>
                            <td><?= h($ex->passport_number ?? '—') ?></td>
                            <td>
                                <?php if ($ex->visa_expiry):
                                    $days = (int)((strtotime($ex->visa_expiry) - time()) / 86400);
                                    $cls = $days <= 90 ? ($days <= 30 ? 'text-danger' : 'text-warning') : '';
                                ?>
                                    <span class="<?= $cls ?>"><?= date('d/m/Y', strtotime($ex->visa_expiry)) ?></span>
                                    <?php if ($days <= 90): ?> <small>(<?= $days ?> ngày)</small><?php endif; ?>
                                <?php else: ?>—<?php endif; ?>
                            </td>
                            <td>
                                <?php if ($ex->work_permit_expiry):
                                    $days = (int)((strtotime($ex->work_permit_expiry) - time()) / 86400);
                                    $cls = $days <= 90 ? ($days <= 30 ? 'text-danger' : 'text-warning') : '';
                                ?>
                                    <span class="<?= $cls ?>"><?= date('d/m/Y', strtotime($ex->work_permit_expiry)) ?></span>
                                <?php else: ?>—<?php endif; ?>
                            </td>
                            <td>
                                <?php if ($ex->trc_expiry):
                                    $days = (int)((strtotime($ex->trc_expiry) - time()) / 86400);
                                    $cls = $days <= 90 ? ($days <= 30 ? 'text-danger' : 'text-warning') : '';
                                ?>
                                    <span class="<?= $cls ?>"><?= date('d/m/Y', strtotime($ex->trc_expiry)) ?></span>
                                <?php else: ?>—<?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $badgeMap = ['Active' => 'badge-active', 'Probation' => 'badge-probation'];
                                $statusLabels = ['Active' => 'Đang làm', 'Probation' => 'Thử việc'];
                                ?>
                                <span class="badge <?= $badgeMap[$ex->status] ?? 'badge-resigned' ?>">
                                    <?= $statusLabels[$ex->status] ?? $ex->status ?>
                                </span>
                            </td>
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
.emp-name-link { color: var(--primary-light); font-weight: 500; }
.emp-name-link:hover { text-decoration: underline; }
</style>
