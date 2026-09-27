<div class="breadcrumb-bar">
    <a href="<?= BASE_URL ?>/dashboard"><i class="fas fa-home"></i> Trang chủ</a>
    <i class="fas fa-chevron-right"></i>
    <span>Quản lý Bảng tin & HSE (Ad2)</span>
</div>

<div class="panel-header" style="margin-bottom: 20px; display:flex; justify-content:space-between; align-items:center;">
    <h2><i class="fas fa-newspaper" style="color: #f59e0b;"></i> Bảng tin Nội bộ & An toàn HSE</h2>
    <a href="<?= BASE_URL ?>/articles/create" class="btn btn-primary"><i class="fas fa-pen-nib"></i> Viết bài mới</a>
</div>

<div class="panel glass-panel">
    <div style="margin-bottom: 20px; display: flex; gap: 15px;">
        <select class="form-control" style="width: 200px;">
            <option value="">Tất cả chuyên mục</option>
            <option value="news">Tin tức nội bộ</option>
            <option value="hse_rule">Quy định An toàn (HSE)</option>
            <option value="notice">Thông báo</option>
        </select>
        <input type="text" class="form-control" placeholder="Tìm kiếm bài viết..." style="max-width: 300px;">
        <button class="btn btn-outline-primary"><i class="fas fa-search"></i> Lọc</button>
    </div>

    <table class="table table-hover">
        <thead>
            <tr>
                <th style="width: 50px;">ID</th>
                <th style="width: 150px;">Chuyên mục</th>
                <th>Tiêu đề bài viết</th>
                <th>Tác giả</th>
                <th>Trạng thái</th>
                <th>Ngày đăng</th>
                <th style="width: 100px;">Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($articles)): ?>
            <tr>
                <td colspan="7" style="text-align:center; color:#94a3b8; padding:30px;">Chưa có bài viết nào.</td>
            </tr>
            <?php endif; ?>

            <?php foreach ($articles as $a): ?>
            <tr>
                <td><?= $a['id'] ?></td>
                <td>
                    <?php if ($a['category'] === 'news'): ?>
                        <span class="badge" style="background: rgba(59,130,246,0.1); color: #3b82f6;"><i class="fas fa-newspaper"></i> Tin tức</span>
                    <?php elseif ($a['category'] === 'hse_rule'): ?>
                        <span class="badge" style="background: rgba(245,158,11,0.1); color: #f59e0b;"><i class="fas fa-hard-hat"></i> HSE</span>
                    <?php else: ?>
                        <span class="badge" style="background: rgba(139,92,246,0.1); color: #8b5cf6;"><i class="fas fa-bullhorn"></i> Thông báo</span>
                    <?php endif; ?>
                </td>
                <td>
                    <strong style="color:#0f172a; font-size: 1.05rem;"><?= h($a['title']) ?></strong>
                </td>
                <td><?= h($a['author_name']) ?></td>
                <td>
                    <?php if ($a['status'] === 'published'): ?>
                        <span class="badge" style="background: rgba(16,185,129,0.1); color: #10b981;">Đã xuất bản</span>
                    <?php elseif ($a['status'] === 'draft'): ?>
                        <span class="badge" style="background: rgba(100,116,139,0.1); color: #64748b;">Bản nháp</span>
                    <?php else: ?>
                        <span class="badge" style="background: rgba(239,68,68,0.1); color: #ef4444;">Đã lưu trữ</span>
                    <?php endif; ?>
                </td>
                <td><?= $a['published_at'] ? date('d/m/Y H:i', strtotime($a['published_at'])) : '—' ?></td>
                <td>
                    <a href="#" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                    <a href="<?= BASE_URL ?>/articles/delete/<?= $a['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Bạn có chắc chắn muốn xóa bài viết này?');"><i class="fas fa-trash"></i></a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
