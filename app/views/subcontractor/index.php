<div class="content-header">
    <div class="header-left">
        <h2><i class="fas fa-hard-hat text-warning"></i> Quản lý Nhà thầu phụ & Tổ đội</h2>
        <p>Quản lý hồ sơ pháp lý, danh sách nhân công và chứng chỉ an toàn của thầu phụ.</p>
    </div>
    <div class="header-actions">
        <button class="btn btn-primary"><i class="fas fa-plus"></i> Thêm Nhà Thầu</button>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <h3>Danh sách Nhà thầu đang hợp tác</h3>
    </div>
    <div class="panel-body">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Mã NT</th>
                        <th>Tên Nhà Thầu / Tổ Đội</th>
                        <th>Đại diện</th>
                        <th>SĐT liên hệ</th>
                        <th>Tổng số công nhân</th>
                        <th>Tỉ lệ thẻ HSE</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($subs as $s): ?>
                        <?php 
                            $ratio = $s['total_workers'] > 0 ? round(($s['certified_workers'] / $s['total_workers']) * 100) : 0;
                            $badgeColor = $ratio >= 50 ? 'bg-success' : 'bg-danger';
                        ?>
                        <tr>
                            <td><strong><?= h($s['sub_code']) ?></strong></td>
                            <td><?= h($s['sub_name']) ?></td>
                            <td><?= h($s['contact_person']) ?></td>
                            <td><?= h($s['phone']) ?></td>
                            <td class="text-center"><strong><?= h($s['total_workers']) ?></strong> người</td>
                            <td>
                                <span class="badge <?= $badgeColor ?> text-white">
                                    <i class="fas fa-shield-alt"></i> <?= $ratio ?>% (<?= $s['certified_workers'] ?>/<?= $s['total_workers'] ?>)
                                </span>
                            </td>
                            <td>
                                <span class="badge <?= $s['status'] === 'Active' ? 'badge-active' : 'badge-inactive' ?>">
                                    <?= h($s['status']) ?>
                                </span>
                            </td>
                            <td>
                                <a href="<?= BASE_URL ?>/subcontractor/detail/<?= $s['id'] ?>" class="btn btn-sm btn-info" title="Xem danh sách công nhân"><i class="fas fa-users"></i></a>
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
