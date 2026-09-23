<div class="breadcrumb-bar">
    <a href="<?= BASE_URL ?>/rbac"><i class="fas fa-shield-halved"></i> Trung tâm Phân quyền</a>
    <i class="fas fa-chevron-right"></i>
    <span>Admin Khung Web (Menu)</span>
</div>

<div class="panel-header" style="margin-bottom: 20px; display:flex; justify-content:space-between; align-items:center;">
    <h2><i class="fas fa-bars-staggered" style="color: #3b82f6;"></i> Quản trị Cây Menu (Sidebar)</h2>
    <a href="<?= BASE_URL ?>/menuAdmin/create" class="btn btn-primary"><i class="fas fa-plus"></i> Thêm Menu mới</a>
</div>

<div class="panel">
    <table class="table table-hover">
        <thead>
            <tr>
                <th>Icon</th>
                <th>Tên Menu</th>
                <th>Đường dẫn (URL)</th>
                <th>Quyền yêu cầu</th>
                <th>Sắp xếp</th>
                <th>Trạng thái</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($menus as $m): ?>
            <tr>
                <td><i class="<?= h($m['icon']) ?>"></i></td>
                <td><strong><?= h($m['title']) ?></strong></td>
                <td><code style="background:#f1f5f9; padding:2px 6px; border-radius:4px;">/<?= h($m['url']) ?></code></td>
                <td>
                    <?php if ($m['permission_required']): ?>
                        <span class="badge" style="background:#8b5cf6;"><i class="fas fa-key"></i> <?= h($m['permission_required']) ?></span>
                    <?php else: ?>
                        <span class="text-muted">Không yêu cầu</span>
                    <?php endif; ?>
                </td>
                <td><?= h($m['sort_order']) ?></td>
                <td>
                    <?php if ($m['is_active']): ?>
                        <span class="badge" style="background:#10b981;">Hiển thị</span>
                    <?php else: ?>
                        <span class="badge" style="background:#6b7280;">Đã ẩn</span>
                    <?php endif; ?>
                </td>
                <td>
                    <a href="<?= BASE_URL ?>/menuAdmin/edit/<?= $m['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                    <a href="<?= BASE_URL ?>/menuAdmin/delete/<?= $m['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Bạn có chắc chắn muốn xóa Menu này?');"><i class="fas fa-trash"></i></a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
