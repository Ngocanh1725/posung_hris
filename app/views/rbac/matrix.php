<div class="breadcrumb-bar">
    <a href="<?= BASE_URL ?>/dashboard"><i class="fas fa-home"></i> Trang chủ</a>
    <i class="fas fa-chevron-right"></i>
    <a href="<?= BASE_URL ?>/rbac">Trung tâm Phân quyền</a>
    <i class="fas fa-chevron-right"></i>
    <span>Ma trận</span>
</div>

<div class="panel-header" style="margin-bottom: 20px;">
    <h2><i class="fas fa-table-cells" style="color: #10b981;"></i> Ma trận Phân quyền</h2>
</div>

<!-- Bộ chọn Role -->
<div style="margin-bottom: 20px;">
    <strong>Chọn Vai trò để cấu hình:</strong>
    <div style="display: flex; gap: 10px; margin-top: 10px; flex-wrap: wrap;">
        <?php foreach ($roles as $r): ?>
            <a href="<?= BASE_URL ?>/rbac/matrix/<?= $r['id'] ?>" class="btn <?= $currentRoleId == $r['id'] ? 'btn-primary' : 'btn-outline-primary' ?>">
                <?= h($r['name']) ?>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<form method="POST" action="<?= BASE_URL ?>/rbac/saveMatrix">
    <?= Session::csrfField() ?>
    <input type="hidden" name="role_id" value="<?= $currentRoleId ?>">

    <div style="margin-bottom: 15px;">
        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="checkAll(true)">Chọn tất cả</button>
        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="checkAll(false)">Bỏ chọn tất cả</button>
        <button type="submit" class="btn btn-sm btn-success" style="float:right;"><i class="fas fa-save"></i> Lưu phân quyền</button>
    </div>

    <div class="panel">
        <table class="table table-bordered">
            <thead style="background: #f3f4f6;">
                <tr>
                    <th style="width: 250px;">Phân hệ chức năng</th>
                    <th>Danh sách quyền thao tác (Granular Permissions)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($groupedPerms as $module => $perms): ?>
                <tr>
                    <td style="vertical-align: top; font-weight: bold; background: #fafafa;">
                        <i class="fas fa-cube text-muted"></i> <?= strtoupper(h($module)) ?>
                        <div style="margin-top:5px; font-size:12px;">
                            <a href="javascript:void(0)" onclick="toggleRow('<?= $module ?>', true)">Tất cả</a> | 
                            <a href="javascript:void(0)" onclick="toggleRow('<?= $module ?>', false)">Bỏ</a>
                        </div>
                    </td>
                    <td>
                        <div style="display:flex; flex-wrap:wrap; gap:15px;">
                            <?php foreach ($perms as $p): ?>
                                <label style="display:flex; align-items:center; cursor:pointer; background:#fff; padding:5px 10px; border:1px solid #ddd; border-radius:4px;" class="perm-<?= $module ?>">
                                    <input type="checkbox" name="permissions[]" value="<?= $p['id'] ?>" class="perm-checkbox" <?= in_array($p['id'], $assignedPerms) ? 'checked' : '' ?> style="margin-right: 8px;">
                                    <span>
                                        <strong><?= h($p['action_code']) ?></strong><br>
                                        <small class="text-muted"><?= h($p['name']) ?></small>
                                    </span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div style="margin-top: 20px; text-align: center;">
        <button type="submit" class="btn btn-success btn-lg"><i class="fas fa-save"></i> Lưu cấu hình phân quyền</button>
    </div>
</form>

<script>
function checkAll(state) {
    document.querySelectorAll('.perm-checkbox').forEach(cb => cb.checked = state);
}
function toggleRow(module, state) {
    document.querySelectorAll('.perm-' + module + ' input[type=checkbox]').forEach(cb => cb.checked = state);
}
</script>
