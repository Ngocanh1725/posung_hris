<div class="breadcrumb-bar">
    <a href="<?= BASE_URL ?>/dashboard"><i class="fas fa-home"></i> Trang chủ</a>
    <i class="fas fa-chevron-right"></i>
    <span>Trung tâm Phân quyền</span>
</div>

<div class="panel-header" style="margin-bottom: 20px;">
    <h2><i class="fas fa-shield-halved" style="color: #3b82f6;"></i> Trung tâm Phân quyền Đa tầng (RBAC)</h2>
    <p class="text-muted">Quản trị viên tối cao: Hệ thống quản lý vai trò và quyền hạn.</p>
</div>

<!-- Thống kê và phím tắt điều hướng -->
<div class="row" style="display: flex; gap: 20px; margin-bottom: 30px;">
    <div style="flex: 1; background: #fff; border-radius: 8px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <h4><i class="fas fa-users-cog"></i> Phân quyền Vai trò</h4>
        <p>Quản lý quyền hạn cho các nhóm người dùng.</p>
        <a href="<?= BASE_URL ?>/rbac/matrix" class="btn btn-primary"><i class="fas fa-table-cells"></i> Ma trận Phân quyền</a>
    </div>
    <div style="flex: 1; background: #fff; border-radius: 8px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <h4><i class="fas fa-bars-staggered"></i> Admin Khung Web</h4>
        <p>Cấu hình cây Menu động hiển thị bên trái.</p>
        <a href="<?= BASE_URL ?>/menuAdmin" class="btn btn-info" style="color:#fff;"><i class="fas fa-sitemap"></i> Quản lý Menu</a>
    </div>
    <div style="flex: 1; background: #fff; border-radius: 8px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <h4><i class="fas fa-database"></i> Admin Cơ sở Dữ liệu</h4>
        <p>Bảo trì, sao lưu và tối ưu hóa hệ thống dữ liệu.</p>
        <a href="<?= BASE_URL ?>/databaseAdmin" class="btn btn-warning"><i class="fas fa-hdd"></i> Quản lý CSDL</a>
    </div>
</div>

<div class="panel">
    <h3>Danh sách Vai trò Hệ thống</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Cấp độ</th>
                <th>Mã Role</th>
                <th>Tên Vai trò</th>
                <th>Mô tả</th>
                <th>Số tài khoản</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($roles as $r): ?>
            <tr>
                <td>
                    <span class="badge" style="background: <?= $r['level'] == 0 ? '#ef4444' : ($r['level'] == 1 ? '#f59e0b' : ($r['level'] == 2 ? '#3b82f6' : '#6b7280')) ?>;">
                        Level <?= h($r['level']) ?>
                    </span>
                </td>
                <td><strong><?= h($r['code']) ?></strong></td>
                <td><?= h($r['name']) ?></td>
                <td class="text-muted"><?= h($r['description']) ?></td>
                <td><?= h($r['user_count']) ?></td>
                <td>
                    <a href="<?= BASE_URL ?>/rbac/matrix/<?= $r['id'] ?>" class="btn btn-sm btn-primary">
                        <i class="fas fa-edit"></i> Cấu hình
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
