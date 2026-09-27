<style>
/* CSS cho Toggle Switch */
.switch {
  position: relative;
  display: inline-block;
  width: 44px;
  height: 24px;
}
.switch input { 
  opacity: 0;
  width: 0;
  height: 0;
}
.slider {
  position: absolute;
  cursor: pointer;
  top: 0; left: 0; right: 0; bottom: 0;
  background-color: #cbd5e1;
  transition: .4s;
  border-radius: 24px;
}
.slider:before {
  position: absolute;
  content: "";
  height: 18px;
  width: 18px;
  left: 3px;
  bottom: 3px;
  background-color: white;
  transition: .4s;
  border-radius: 50%;
}
input:checked + .slider {
  background-color: #10b981;
}
input:checked + .slider:before {
  transform: translateX(20px);
}
.order-input {
    width: 60px;
    padding: 4px 8px;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    text-align: center;
}
.order-input:focus {
    border-color: #3b82f6;
    outline: none;
    box-shadow: 0 0 0 3px rgba(59,130,246,0.1);
}
.menu-row { transition: all 0.2s; }
.menu-row:hover { background: #f8fafc; }
</style>

<div class="breadcrumb-bar">
    <a href="<?= BASE_URL ?>/rbac"><i class="fas fa-shield-halved"></i> Trung tâm Phân quyền</a>
    <i class="fas fa-chevron-right"></i>
    <span>Admin Khung Web (Menu)</span>
</div>

<div class="panel-header" style="margin-bottom: 20px; display:flex; justify-content:space-between; align-items:center;">
    <h2><i class="fas fa-bars-staggered" style="color: #3b82f6;"></i> Quản trị Cây Menu (Sidebar)</h2>
    <a href="<?= BASE_URL ?>/menuAdmin/create" class="btn btn-primary"><i class="fas fa-plus"></i> Thêm Menu mới</a>
</div>

<div class="panel glass-panel">
    <table class="table">
        <thead>
            <tr>
                <th style="width: 50px; text-align: center;">Icon</th>
                <th>Tên Menu</th>
                <th>Đường dẫn (URL)</th>
                <th>Quyền yêu cầu</th>
                <th style="width: 100px; text-align: center;">Sắp xếp</th>
                <th style="width: 100px; text-align: center;">Hiển thị</th>
                <th style="width: 120px; text-align: center;">Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($menus as $m): ?>
            <tr class="menu-row">
                <td style="text-align: center; font-size: 1.2rem; color: #64748b;"><i class="<?= h($m['icon']) ?>"></i></td>
                <td>
                    <?php if ($m['parent_id']): ?>
                        <span style="color: #cbd5e1; margin-right: 8px;">—</span>
                    <?php endif; ?>
                    <strong><?= h($m['title']) ?></strong>
                </td>
                <td><code style="background:#f1f5f9; padding:2px 6px; border-radius:4px; color: #ef4444;">/<?= h($m['url']) ?></code></td>
                <td>
                    <?php if ($m['permission_required']): ?>
                        <span class="badge" style="background:rgba(139,92,246,0.1); color: #8b5cf6;"><i class="fas fa-key"></i> <?= h($m['permission_required']) ?></span>
                    <?php else: ?>
                        <span class="text-muted" style="font-size: 0.85rem;">Không yêu cầu</span>
                    <?php endif; ?>
                </td>
                <td style="text-align: center;">
                    <input type="number" class="order-input" data-id="<?= $m['id'] ?>" value="<?= h($m['sort_order']) ?>" onchange="updateOrder(this)">
                </td>
                <td style="text-align: center;">
                    <label class="switch">
                        <input type="checkbox" data-id="<?= $m['id'] ?>" onchange="toggleStatus(this)" <?= $m['is_active'] ? 'checked' : '' ?>>
                        <span class="slider"></span>
                    </label>
                </td>
                <td style="text-align: center;">
                    <a href="<?= BASE_URL ?>/menuAdmin/edit/<?= $m['id'] ?>" class="btn btn-sm" style="color: #3b82f6; background: rgba(59,130,246,0.1); border: none;"><i class="fas fa-edit"></i></a>
                    <a href="<?= BASE_URL ?>/menuAdmin/delete/<?= $m['id'] ?>" class="btn btn-sm" style="color: #ef4444; background: rgba(239,68,68,0.1); border: none;" onclick="return confirm('Bạn có chắc chắn muốn xóa Menu này?');"><i class="fas fa-trash"></i></a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
function toggleStatus(checkbox) {
    const id = checkbox.getAttribute('data-id');
    const status = checkbox.checked ? 1 : 0;
    
    fetch('<?= BASE_URL ?>/menuAdmin/ajaxToggleStatus', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `id=${id}&status=${status}`
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            // Hiển thị thông báo nhỏ gọn (toast) nếu cần
            console.log('Cập nhật trạng thái thành công');
        }
    })
    .catch(error => console.error('Error:', error));
}

function updateOrder(input) {
    const id = input.getAttribute('data-id');
    const order = input.value;
    
    fetch('<?= BASE_URL ?>/menuAdmin/ajaxUpdateOrder', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `id=${id}&order=${order}`
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            input.style.backgroundColor = '#dcfce7'; // green highlight
            setTimeout(() => {
                input.style.backgroundColor = '';
            }, 500);
        }
    })
    .catch(error => console.error('Error:', error));
}
</script>
