<!-- Employee Detail View -->
<?php
$emp = $employee;
$statusLabels = [
    'Active' => 'Đang làm', 'Probation' => 'Thử việc', 'Suspended' => 'Tạm nghỉ',
    'Resigned' => 'Đã nghỉ', 'Retired' => 'Nghỉ hưu', 'Blacklisted' => 'Blacklist',
];
$typeLabels = [
    'Expat' => 'Chuyên gia nước ngoài', 'Office_BIM' => 'Văn phòng / BIM',
    'Site_Engineer' => 'Kỹ sư hiện trường', 'Direct_Worker' => 'Công nhân trực tiếp',
];
$badgeMap = [
    'Active' => 'badge-active', 'Probation' => 'badge-probation',
    'Suspended' => 'badge-suspended', 'Resigned' => 'badge-resigned', 'Blacklisted' => 'badge-suspended',
];
?>

<div class="detail-header">
    <a href="<?= BASE_URL ?>/employee" class="btn btn-ghost btn-sm">
        <i class="fas fa-arrow-left"></i> Quay lại
    </a>
</div>

<div class="profile-grid">
    <!-- Left: Basic Info Card -->
    <div class="profile-card">
        <div class="profile-top">
            <div class="profile-avatar">
                <?= strtoupper(mb_substr($emp->full_name, 0, 1)) ?>
            </div>
            <h2 class="profile-name"><?= h($emp->full_name) ?></h2>
            <span class="badge <?= $badgeMap[$emp->status] ?? 'badge-resigned' ?>">
                <?= $statusLabels[$emp->status] ?? $emp->status ?>
            </span>
            <p class="profile-code"><?= h($emp->emp_code) ?></p>
        </div>
        <div class="profile-details">
            <div class="detail-row">
                <span class="detail-label"><i class="fas fa-building"></i> Phòng ban</span>
                <span class="detail-value"><?= h($emp->dept_name ?? '—') ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label"><i class="fas fa-briefcase"></i> Chức vụ</span>
                <span class="detail-value"><?= h($emp->pos_title ?? '—') ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label"><i class="fas fa-hard-hat"></i> Dự án</span>
                <span class="detail-value"><?= h($emp->project_name ?? '— Trụ sở —') ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label"><i class="fas fa-tag"></i> Phân loại</span>
                <span class="detail-value"><?= $typeLabels[$emp->employee_type] ?? $emp->employee_type ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label"><i class="fas fa-flag"></i> Quốc tịch</span>
                <span class="detail-value"><?= h($emp->nationality) ?></span>
            </div>
        </div>
    </div>

    <!-- Right: Tabs for detailed info -->
    <div class="profile-tabs-wrapper">
        <div class="tabs-nav" id="profile-tabs-nav">
            <button class="tab-btn active" data-tab="personal">Lý lịch</button>
            <button class="tab-btn" data-tab="salary">Lương</button>
            <button class="tab-btn" data-tab="certificates">Chứng chỉ</button>
            <button class="tab-btn" data-tab="movements">Điều động</button>
            <button class="tab-btn" data-tab="rewards">KT – KL</button>
            <?php if ($emp->employee_type === 'Expat' && $expatDetail): ?>
            <button class="tab-btn" data-tab="expat">Visa / TRC</button>
            <?php endif; ?>
        </div>

        <!-- Tab: Personal -->
        <div class="tab-panel active" id="tab-personal">
            <div class="info-grid">
                <div class="info-item"><label>Ngày sinh</label><span><?= $emp->dob ? date('d/m/Y', strtotime($emp->dob)) : '—' ?></span></div>
                <div class="info-item"><label>Giới tính</label><span><?= $emp->gender === 'Male' ? 'Nam' : ($emp->gender === 'Female' ? 'Nữ' : 'Khác') ?></span></div>
                <div class="info-item"><label>CCCD / Hộ chiếu</label><span><?= h($emp->id_card ?? '—') ?></span></div>
                <div class="info-item"><label>Ngày cấp</label><span><?= $emp->id_card_date ? date('d/m/Y', strtotime($emp->id_card_date)) : '—' ?></span></div>
                <div class="info-item"><label>Nơi cấp</label><span><?= h($emp->id_card_place ?? '—') ?></span></div>
                <div class="info-item"><label>Quê quán</label><span><?= h($emp->hometown ?? '—') ?></span></div>
                <div class="info-item"><label>Địa chỉ</label><span><?= h($emp->address ?? '—') ?></span></div>
                <div class="info-item"><label>SĐT</label><span><?= h($emp->phone ?? '—') ?></span></div>
                <div class="info-item"><label>Email</label><span><?= h($emp->email ?? '—') ?></span></div>
                <div class="info-item"><label>Ngày vào công ty</label><span><?= $emp->join_date ? date('d/m/Y', strtotime($emp->join_date)) : '—' ?></span></div>
                <div class="info-item"><label>Ngày chính thức</label><span><?= $emp->official_date ? date('d/m/Y', strtotime($emp->official_date)) : '—' ?></span></div>
                <div class="info-item"><label>Trình độ</label><span><?= h($emp->highest_degree ?? '—') ?></span></div>
                <div class="info-item"><label>Ngày vào Đảng</label><span><?= $emp->party_join_date ? date('d/m/Y', strtotime($emp->party_join_date)) : '—' ?></span></div>
            </div>
        </div>

        <!-- Tab: Salary History -->
        <div class="tab-panel" id="tab-salary">
            <?php if (empty($salaries)): ?>
                <div class="empty-state"><i class="fas fa-money-bill"></i><p>Chưa có dữ liệu lương.</p></div>
            <?php else: ?>
                <div class="table-wrapper">
                    <table>
                        <thead><tr><th>Áp dụng từ</th><th>Ngạch bậc</th><th>Lương CB</th><th>PC Dự án</th><th>PC Phòng sạch</th><th>PC Xa nhà</th><th>% BH</th></tr></thead>
                        <tbody>
                        <?php foreach ($salaries as $s): ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($s->effective_date)) ?></td>
                            <td><?= h($s->grade_code ?? '') ?></td>
                            <td class="text-right"><?= number_format($s->base_salary, 0, ',', '.') ?></td>
                            <td class="text-right"><?= number_format($s->project_allowance, 0, ',', '.') ?></td>
                            <td class="text-right"><?= number_format($s->cleanroom_allowance, 0, ',', '.') ?></td>
                            <td class="text-right"><?= number_format($s->remote_allowance, 0, ',', '.') ?></td>
                            <td><?= $s->insurance_rate ?>%</td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Tab: Certificates -->
        <div class="tab-panel" id="tab-certificates">
            <?php if (empty($certificates)): ?>
                <div class="empty-state"><i class="fas fa-certificate"></i><p>Chưa có chứng chỉ nào.</p></div>
            <?php else: ?>
                <div class="table-wrapper">
                    <table>
                        <thead><tr><th>Loại</th><th>Tên chứng chỉ</th><th>Ngày cấp</th><th>Hết hạn</th><th>Cơ quan cấp</th><th>Bắt buộc</th></tr></thead>
                        <tbody>
                        <?php foreach ($certificates as $c): ?>
                        <tr>
                            <td><?= h($c->cert_type) ?></td>
                            <td><?= h($c->cert_name) ?></td>
                            <td><?= $c->issue_date ? date('d/m/Y', strtotime($c->issue_date)) : '—' ?></td>
                            <td>
                                <?php
                                if ($c->expiry_date) {
                                    $exp = strtotime($c->expiry_date);
                                    $daysLeft = (int)(($exp - time()) / 86400);
                                    $class = $daysLeft <= 90 ? 'text-warning' : ($daysLeft <= 0 ? 'text-danger' : '');
                                    echo "<span class='{$class}'>" . date('d/m/Y', $exp) . "</span>";
                                    if ($daysLeft <= 90 && $daysLeft > 0) echo " <small>({$daysLeft} ngày)</small>";
                                    if ($daysLeft <= 0) echo " <small class='text-danger'>(HẾT HẠN)</small>";
                                } else {
                                    echo '—';
                                }
                                ?>
                            </td>
                            <td><?= h($c->issuing_authority ?? '—') ?></td>
                            <td><?= $c->is_mandatory_site ? '<i class="fas fa-check-circle text-success"></i>' : '' ?></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Tab: Job Movements -->
        <div class="tab-panel" id="tab-movements">
            <?php if (empty($movements)): ?>
                <div class="empty-state"><i class="fas fa-exchange-alt"></i><p>Chưa có lịch sử điều động.</p></div>
            <?php else: ?>
                <div class="timeline">
                    <?php foreach ($movements as $m): ?>
                    <div class="timeline-item">
                        <div class="timeline-dot"></div>
                        <div class="timeline-content">
                            <div class="timeline-date"><?= date('d/m/Y', strtotime($m->effective_date)) ?></div>
                            <div class="timeline-title">
                                <?= h($m->movement_type) ?>
                                <?php if ($m->decision_number): ?>
                                    – <strong><?= h($m->decision_number) ?></strong>
                                <?php endif; ?>
                            </div>
                            <div class="timeline-desc">
                                <?php if ($m->from_dept || $m->to_dept): ?>
                                    Phòng ban: <?= h($m->from_dept ?? '—') ?> → <?= h($m->to_dept ?? '—') ?><br>
                                <?php endif; ?>
                                <?php if ($m->from_project || $m->to_project): ?>
                                    Dự án: <?= h($m->from_project ?? '—') ?> → <?= h($m->to_project ?? '—') ?><br>
                                <?php endif; ?>
                                <?php if ($m->reason): ?>
                                    <em><?= h($m->reason) ?></em>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Tab: Rewards / Disciplines -->
        <div class="tab-panel" id="tab-rewards">
            <?php if (empty($rewards)): ?>
                <div class="empty-state"><i class="fas fa-award"></i><p>Chưa có khen thưởng / kỷ luật.</p></div>
            <?php else: ?>
                <div class="table-wrapper">
                    <table>
                        <thead><tr><th>Loại</th><th>Số QĐ</th><th>Ngày</th><th>Nội dung</th><th>Số tiền</th><th>HSE</th></tr></thead>
                        <tbody>
                        <?php foreach ($rewards as $r): ?>
                        <tr>
                            <td>
                                <span class="badge <?= $r->type === 'Reward' ? 'badge-active' : 'badge-suspended' ?>">
                                    <?= $r->type === 'Reward' ? 'Khen thưởng' : 'Kỷ luật' ?>
                                </span>
                            </td>
                            <td><?= h($r->decision_number ?? '—') ?></td>
                            <td><?= $r->decision_date ? date('d/m/Y', strtotime($r->decision_date)) : '—' ?></td>
                            <td><?= h($r->title) ?></td>
                            <td class="text-right"><?= number_format($r->amount, 0, ',', '.') ?></td>
                            <td><?= $r->is_safety_violation ? '<i class="fas fa-exclamation-triangle text-danger"></i>' : '' ?></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Tab: Expat Details -->
        <?php if ($emp->employee_type === 'Expat' && $expatDetail): ?>
        <div class="tab-panel" id="tab-expat">
            <div class="info-grid">
                <div class="info-item"><label>Số Hộ chiếu</label><span><?= h($expatDetail->passport_number ?? '—') ?></span></div>
                <div class="info-item"><label>Số Visa</label><span><?= h($expatDetail->visa_number ?? '—') ?></span></div>
                <div class="info-item"><label>Visa hết hạn</label><span><?= $expatDetail->visa_expiry ? date('d/m/Y', strtotime($expatDetail->visa_expiry)) : '—' ?></span></div>
                <div class="info-item"><label>Số GPLĐ</label><span><?= h($expatDetail->work_permit_number ?? '—') ?></span></div>
                <div class="info-item"><label>GPLĐ hết hạn</label><span><?= $expatDetail->work_permit_expiry ? date('d/m/Y', strtotime($expatDetail->work_permit_expiry)) : '—' ?></span></div>
                <div class="info-item"><label>Số TRC</label><span><?= h($expatDetail->trc_number ?? '—') ?></span></div>
                <div class="info-item"><label>TRC hết hạn</label><span><?= $expatDetail->trc_expiry ? date('d/m/Y', strtotime($expatDetail->trc_expiry)) : '—' ?></span></div>
                <div class="info-item info-item-full"><label>LH Khẩn cấp (Hàn Quốc)</label><span><?= h($expatDetail->emergency_korea_contact ?? '—') ?></span></div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.detail-header { margin-bottom: 20px; }
.profile-grid {
    display: grid;
    grid-template-columns: 320px 1fr;
    gap: 24px;
    align-items: start;
}
.profile-card {
    background: var(--bg-card); border: 1px solid var(--border);
    border-radius: var(--radius-lg); overflow: hidden;
}
.profile-top {
    text-align: center; padding: 32px 24px 24px;
    border-bottom: 1px solid var(--border);
    background: linear-gradient(135deg, rgba(59,130,246,0.08), rgba(139,92,246,0.05));
}
.profile-avatar {
    width: 72px; height: 72px; border-radius: 50%;
    background: linear-gradient(135deg, var(--primary), var(--accent));
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 28px; font-weight: 800; color: #fff; margin-bottom: 12px;
}
.profile-name { font-size: 18px; font-weight: 700; color: var(--text-primary); margin-bottom: 8px; }
.profile-code { font-size: 12px; color: var(--text-muted); margin-top: 8px; letter-spacing: 1px; }
.profile-details { padding: 20px 24px; }
.detail-row {
    display: flex; justify-content: space-between; align-items: center;
    padding: 10px 0; border-bottom: 1px solid var(--border);
}
.detail-row:last-child { border-bottom: none; }
.detail-label { font-size: 12px; color: var(--text-secondary); display: flex; align-items: center; gap: 8px; }
.detail-label i { width: 16px; color: var(--text-muted); }
.detail-value { font-size: 13px; color: var(--text-primary); font-weight: 500; text-align: right; }

/* Tabs */
.profile-tabs-wrapper {
    background: var(--bg-card); border: 1px solid var(--border);
    border-radius: var(--radius-lg); overflow: hidden;
}
.tabs-nav {
    display: flex; border-bottom: 1px solid var(--border); overflow-x: auto;
    background: #f8fafc;
}
.tab-btn {
    padding: 14px 20px; font-size: 13px; font-weight: 500;
    color: var(--text-secondary); white-space: nowrap;
    border-bottom: 2px solid transparent;
    transition: all 0.2s var(--ease);
}
.tab-btn:hover { color: var(--primary-light); }
.tab-btn.active {
    color: var(--primary-light);
    border-bottom-color: var(--primary);
}
.tab-panel { display: none; padding: 24px; }
.tab-panel.active { display: block; }

/* Info grid */
.info-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; }
.info-item label { display: block; font-size: 11px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; }
.info-item span { font-size: 14px; color: var(--text-primary); }
.info-item-full { grid-column: 1 / -1; }

/* Timeline */
.timeline { position: relative; padding-left: 24px; }
.timeline::before { content: ''; position: absolute; left: 6px; top: 0; bottom: 0; width: 2px; background: var(--border); }
.timeline-item { position: relative; margin-bottom: 24px; }
.timeline-dot { position: absolute; left: -21px; top: 4px; width: 10px; height: 10px; border-radius: 50%; background: var(--primary); border: 2px solid var(--bg-body); }
.timeline-date { font-size: 11px; color: var(--text-muted); margin-bottom: 4px; }
.timeline-title { font-size: 14px; font-weight: 600; color: var(--text-heading); margin-bottom: 4px; }
.timeline-desc { font-size: 13px; color: var(--text-secondary); line-height: 1.6; }

/* Utilities */
.text-right { text-align: right; }
.text-warning { color: var(--warning); }
.text-danger { color: var(--danger); }
.text-success { color: var(--success); }

@media (max-width: 1024px) {
    .profile-grid { grid-template-columns: 1fr; }
    .info-grid { grid-template-columns: 1fr; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabPanels = document.querySelectorAll('.tab-panel');

    tabBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            tabBtns.forEach(b => b.classList.remove('active'));
            tabPanels.forEach(p => p.classList.remove('active'));
            btn.classList.add('active');
            document.getElementById('tab-' + btn.dataset.tab).classList.add('active');
        });
    });
});
</script>
