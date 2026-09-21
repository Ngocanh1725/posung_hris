<!-- ══════════════════════════════════════════════════════════
     CHI TIẾT DỰ ÁN (V2)
     ══════════════════════════════════════════════════════════ -->

<div class="breadcrumb-bar">
    <a href="<?= BASE_URL ?>/project"><i class="fas fa-hard-hat"></i> Danh sách Dự án</a>
    <i class="fas fa-chevron-right"></i>
    <span><?= h($project['name']) ?></span>
</div>

<div class="panel" style="margin-bottom: 24px; background: linear-gradient(135deg, rgba(16,185,129,0.05) 0%, rgba(5,150,105,0.02) 100%);">
    <div class="panel-body">
        <div style="display:flex; justify-content:space-between; align-items:flex-start;">
            <div>
                <h2 style="margin-bottom: 8px; color:var(--primary-light);"><?= h($project['name']) ?> (<?= h($project['project_code']) ?>)</h2>
                <p style="color:var(--text-muted);"><i class="fas fa-building"></i> Khách hàng: <?= h($project['client_name']) ?></p>
                <p style="color:var(--text-muted);"><i class="fas fa-map-marker-alt"></i> Địa điểm: <?= h($project['location']) ?></p>
                <p style="color:var(--text-muted);"><i class="fas fa-money-check-alt"></i> Cost Center: <strong><?= h($project['cost_center_code']) ?></strong></p>
            </div>
            
            <?php 
                $pct = $stats['quota'] > 0 ? round(($stats['actual'] / $stats['quota']) * 100) : 0;
                $totalReal = $stats['actual'] + $stats['incoming'];
            ?>
            <div style="text-align:right; min-width: 200px;">
                <div style="font-size: 2rem; font-weight:700; color: #10b981;"><?= $stats['actual'] ?> <span style="font-size:1rem; color:var(--text-muted);">/ <?= $stats['quota'] ?></span></div>
                <div style="font-size: 0.85rem; color:var(--text-muted); margin-bottom: 8px;">Nhân sự cơ hữu hiện tại</div>
                <div class="progress-bar-wrapper">
                    <div class="progress-bar" style="width: <?= $pct ?>%; background: <?= $pct < 50 ? '#ef4444' : ($pct < 90 ? '#f59e0b' : '#10b981') ?>;"></div>
                </div>
                <?php if($stats['missing'] > 0): ?>
                    <div style="margin-top:8px; font-size:0.8rem; color:#ef4444;"><i class="fas fa-exclamation-triangle"></i> Đang thiếu <?= $stats['missing'] ?> người</div>
                <?php endif; ?>
                <?php if($stats['incoming'] > 0): ?>
                    <div style="margin-top:4px; font-size:0.8rem; color:#3b82f6;"><i class="fas fa-truck-moving"></i> Điều động đến: +<?= $stats['incoming'] ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <h3><i class="fas fa-users"></i> Danh sách Nhân sự Hiện hành</h3>
    </div>
    <div class="panel-body p-0">
        <?php if (empty($personnel)): ?>
            <div class="empty-state"><i class="fas fa-users-slash"></i><p>Chưa có nhân sự nào được điều động đến dự án này.</p></div>
        <?php else: ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Mã NV</th>
                            <th>Họ Tên</th>
                            <th>Chức danh</th>
                            <th>Phòng ban gốc</th>
                            <th style="text-align:center;">Quốc tịch</th>
                            <th>Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($personnel as $emp): ?>
                        <tr>
                            <td><strong><?= h($emp['employee_code']) ?></strong></td>
                            <td><?= h($emp['full_name']) ?></td>
                            <td><?= h($emp['pos_title'] ?? '—') ?></td>
                            <td><?= h($emp['dept_name'] ?? '—') ?></td>
                            <td style="text-align:center;">
                                <?php if ($emp['is_expat']): ?>
                                    <span class="badge" style="background:rgba(99,102,241,0.15); color:#6366f1;">Expat (<?= h($emp['nationality']) ?>)</span>
                                <?php else: ?>
                                    <span style="font-size:0.85rem; color:var(--text-muted);"><?= h($emp['nationality']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($emp['status'] === 'active'): ?>
                                    <span class="badge badge-active">Đang làm việc</span>
                                <?php elseif ($emp['status'] === 'probation'): ?>
                                    <span class="badge badge-probation">Thử việc</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary"><?= h($emp['status']) ?></span>
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
/* Breadcrumb */
.breadcrumb-bar { display: flex; align-items: center; gap: 8px; margin-bottom: 20px; font-size: 0.85rem; color: var(--text-muted); }
.breadcrumb-bar a { color: var(--primary-light); text-decoration: none; }

.progress-bar-wrapper { width: 100%; height: 6px; background: rgba(255,255,255,0.1); border-radius: 3px; overflow: hidden; }
.progress-bar { height: 100%; border-radius: 3px; transition: width 0.5s; }
</style>
