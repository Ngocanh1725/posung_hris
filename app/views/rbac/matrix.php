<style>
.matrix-table th {
    text-align: center;
    background: #f8fafc;
    color: #334155;
    font-weight: 700;
    padding: 12px;
}
.matrix-table td {
    vertical-align: middle;
    text-align: center;
    padding: 10px;
}
.matrix-table td.module-name {
    text-align: left;
    font-weight: 600;
    color: #0f172a;
    background: #f8fafc;
}
/* CSS Checkbox Custom */
.cbx-container {
    display: inline-block;
    position: relative;
    cursor: pointer;
    font-size: 22px;
    user-select: none;
    margin: 0;
}
.cbx-container input {
    position: absolute;
    opacity: 0;
    cursor: pointer;
    height: 0;
    width: 0;
}
.checkmark {
    position: relative;
    top: 0; left: 0;
    height: 22px;
    width: 22px;
    background-color: #e2e8f0;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}
.cbx-container:hover input ~ .checkmark { background-color: #cbd5e1; }
.cbx-container input:checked ~ .checkmark { background-color: #10b981; }
.cbx-container input:disabled ~ .checkmark { background-color: #f1f5f9; cursor: not-allowed; }
.checkmark:after {
    content: "\f00c";
    font-family: "Font Awesome 6 Free";
    font-weight: 900;
    color: white;
    font-size: 12px;
    display: none;
}
.cbx-container input:checked ~ .checkmark:after { display: block; }
</style>

<div class="breadcrumb-bar">
    <a href="<?= BASE_URL ?>/dashboard"><i class="fas fa-home"></i> Trang chủ</a>
    <i class="fas fa-chevron-right"></i>
    <a href="<?= BASE_URL ?>/rbac">Trung tâm Phân quyền</a>
    <i class="fas fa-chevron-right"></i>
    <span>Ma trận Phân quyền</span>
</div>

<div class="panel-header" style="margin-bottom: 20px;">
    <h2><i class="fas fa-table-cells" style="color: #10b981;"></i> Ma trận Phân quyền (RBAC Matrix)</h2>
</div>

<!-- Bộ chọn Role -->
<div class="panel glass-panel" style="margin-bottom: 20px;">
    <h4 style="font-size: 1rem; margin-bottom: 12px; color: #475569;"><i class="fas fa-users-gear"></i> Nhóm Vai trò (Role):</h4>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <?php foreach ($roles as $r): ?>
            <a href="<?= BASE_URL ?>/rbac/matrix/<?= $r['id'] ?>" class="btn <?= $currentRoleId == $r['id'] ? 'btn-primary' : 'btn-outline-primary' ?>" style="border-radius: 8px;">
                <?= h($r['name']) ?>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<form method="POST" action="<?= BASE_URL ?>/rbac/saveMatrix" id="matrixForm">
    <?= Session::csrfField() ?>
    <input type="hidden" name="role_id" value="<?= $currentRoleId ?>">

    <div style="margin-bottom: 15px; display: flex; justify-content: space-between;">
        <div>
            <button type="button" class="btn btn-sm" style="background:#e0f2fe; color:#0369a1;" onclick="checkAll(true)"><i class="fas fa-check-double"></i> Chọn tất cả</button>
            <button type="button" class="btn btn-sm" style="background:#fee2e2; color:#b91c1c;" onclick="checkAll(false)"><i class="fas fa-ban"></i> Bỏ chọn tất cả</button>
        </div>
        <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Áp dụng Phân quyền</button>
    </div>

    <div class="panel glass-panel" style="padding: 0; overflow: hidden;">
        <table class="table matrix-table table-bordered" style="margin: 0;">
            <thead>
                <tr>
                    <th style="width: 200px; text-align: left;">Phân hệ / Module</th>
                    <th style="width: 100px;">
                        <div>Xem (Read)</div>
                        <label class="cbx-container" style="margin-top:5px;"><input type="checkbox" onchange="checkCol('view', this.checked)"><span class="checkmark"></span></label>
                    </th>
                    <th style="width: 100px;">
                        <div>Thêm (Create)</div>
                        <label class="cbx-container" style="margin-top:5px;"><input type="checkbox" onchange="checkCol('create', this.checked)"><span class="checkmark"></span></label>
                    </th>
                    <th style="width: 100px;">
                        <div>Sửa (Update)</div>
                        <label class="cbx-container" style="margin-top:5px;"><input type="checkbox" onchange="checkCol('update', this.checked)"><span class="checkmark"></span></label>
                    </th>
                    <th style="width: 100px;">
                        <div>Xóa (Delete)</div>
                        <label class="cbx-container" style="margin-top:5px;"><input type="checkbox" onchange="checkCol('delete', this.checked)"><span class="checkmark"></span></label>
                    </th>
                    <th style="width: 100px;">
                        <div>In / Xuất (Print)</div>
                        <label class="cbx-container" style="margin-top:5px;"><input type="checkbox" onchange="checkCol('print', this.checked)"><span class="checkmark"></span></label>
                    </th>
                    <th style="width: 100px;">
                        <div>Phê duyệt (Approve)</div>
                        <label class="cbx-container" style="margin-top:5px;"><input type="checkbox" onchange="checkCol('approve', this.checked)"><span class="checkmark"></span></label>
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $standardActions = ['view', 'create', 'update', 'delete', 'print', 'approve'];
                
                foreach ($groupedPerms as $moduleCode => $perms): 
                    // Map lại mảng action_code => id cho module này
                    $actionMap = [];
                    foreach ($perms as $p) {
                        $actionMap[$p['action_code']] = $p['id'];
                    }
                ?>
                <tr class="module-row" data-module="<?= $moduleCode ?>">
                    <td class="module-name">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span><i class="fas fa-cube text-muted" style="margin-right: 5px;"></i> <?= strtoupper(h($moduleCode)) ?></span>
                            <label class="cbx-container" title="Chọn toàn bộ dòng này">
                                <input type="checkbox" onchange="checkRow('<?= $moduleCode ?>', this.checked)">
                                <span class="checkmark" style="background-color:#3b82f6;"></span>
                            </label>
                        </div>
                    </td>
                    
                    <?php foreach ($standardActions as $act): ?>
                        <td>
                            <?php if (isset($actionMap[$act])): ?>
                                <?php $permId = $actionMap[$act]; ?>
                                <label class="cbx-container">
                                    <input type="checkbox" name="permissions[]" value="<?= $permId ?>" class="perm-checkbox col-<?= $act ?>" <?= in_array($permId, $assignedPerms) ? 'checked' : '' ?>>
                                    <span class="checkmark"></span>
                                </label>
                            <?php else: ?>
                                <span style="color: #cbd5e1;">—</span>
                            <?php endif; ?>
                        </td>
                    <?php endforeach; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div style="margin-top: 20px; text-align: right;">
        <button type="submit" class="btn btn-success btn-lg" id="btnSaveAjax"><i class="fas fa-save"></i> LƯU MA TRẬN</button>
    </div>
</form>

<script>
function checkAll(state) {
    document.querySelectorAll('.perm-checkbox:not(:disabled)').forEach(cb => {
        cb.checked = state;
    });
}
function checkCol(action, state) {
    document.querySelectorAll('.col-' + action + ':not(:disabled)').forEach(cb => {
        cb.checked = state;
    });
}
function checkRow(moduleCode, state) {
    document.querySelectorAll('.module-row[data-module="' + moduleCode + '"] .perm-checkbox:not(:disabled)').forEach(cb => {
        cb.checked = state;
    });
}

// Xử lý lưu AJAX
document.getElementById('matrixForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSaveAjax');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang lưu...';
    btn.disabled = true;

    const formData = new FormData(this);
    fetch(this.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.text())
    .then(data => {
        // Có thể reload hoặc báo thành công
        btn.innerHTML = '<i class="fas fa-check"></i> Đã lưu thành công!';
        btn.classList.replace('btn-success', 'btn-primary');
        setTimeout(() => {
            btn.innerHTML = originalText;
            btn.classList.replace('btn-primary', 'btn-success');
            btn.disabled = false;
        }, 2000);
    })
    .catch(error => {
        console.error('Error:', error);
        btn.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Lỗi';
        btn.disabled = false;
    });
});
</script>
