<div class="content-header">
    <div class="header-left">
        <h2><i class="fas fa-cog"></i> Cài đặt hệ thống</h2>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="panel">
            <div class="panel-header">
                <h3>Cập nhật Logo Công ty</h3>
            </div>
            <div class="panel-body">
                <form action="<?= BASE_URL ?>/setting" method="POST" enctype="multipart/form-data">
                    <div class="form-group mb-3">
                        <label class="form-label">Chọn ảnh Logo mới (PNG, JPG):</label>
                        <input type="file" name="logo" class="form-control" accept="image/*" required>
                    </div>
                    <?php if(file_exists(APP_ROOT . '/../public/uploads/logo.png')): ?>
                        <div class="mb-3">
                            <label class="form-label d-block">Logo hiện tại:</label>
                            <img src="<?= BASE_URL ?>/uploads/logo.png?t=<?= time() ?>" alt="Current Logo" style="max-height: 100px; border: 1px solid #ccc; padding: 5px; border-radius: 5px;">
                        </div>
                    <?php endif; ?>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-upload"></i> Cập nhật Logo</button>
                </form>
            </div>
        </div>
    </div>
</div>
