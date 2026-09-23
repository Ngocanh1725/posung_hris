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
                            <th>Nhắc nhở</th>
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
                                    $bg = $ex->visa_status === 'red' ? '#ef4444' : ($ex->visa_status === 'yellow' ? '#f59e0b' : '#10b981');
                                    $days = (int)((strtotime($ex->visa_expiry) - time()) / 86400);
                                ?>
                                    <span class="badge" style="background: <?= $bg ?>; color: white;">
                                        <?= date('d/m/Y', strtotime($ex->visa_expiry)) ?> (<?= $days ?>d)
                                    </span>
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
                                    $bg = $ex->trc_status === 'red' ? '#ef4444' : ($ex->trc_status === 'yellow' ? '#f59e0b' : '#10b981');
                                    $days = (int)((strtotime($ex->trc_expiry) - time()) / 86400);
                                ?>
                                    <span class="badge" style="background: <?= $bg ?>; color: white;">
                                        <?= date('d/m/Y', strtotime($ex->trc_expiry)) ?> (<?= $days ?>d)
                                    </span>
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
                            <td>
                                <?php if ($ex->email): ?>
                                    <a href="mailto:<?= h($ex->email) ?>?subject=Thông báo gia hạn giấy tờ pháp lý (Visa/TRC)&body=Kính gửi <?= h($ex->full_name) ?>,%0D%0A%0D%0AVui lòng nộp lại hộ chiếu để gia hạn các giấy tờ sắp hết hạn của bạn." class="btn btn-sm btn-primary" title="Gửi mail nhắc nhở">
                                        <i class="fas fa-envelope"></i> Gửi Mail
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted small">Chưa có email</span>
                                <?php endif; ?>
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
