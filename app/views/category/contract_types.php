<div class="panel">
    <div class="panel-header d-flex justify-content-between align-items-center">
        <h3><i class="fas fa-file-signature"></i> Danh mục <?= h($title) ?></h3>
        <button class="btn btn-primary btn-sm" onclick="openModal()"><i class="fas fa-plus"></i> Thêm mới</button>
    </div>
    <div class="panel-body p-0">
        <div class="table-wrapper">
            <table class="table" id="dataTable">
                <thead>
                    <tr>
                        <th>Tên Loại Hợp đồng</th>
                        <th>Thời hạn (Tháng)</th>
                        <th width="120">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td class="fw-bold"><?= h($item['name']) ?></td>
                        <td><?= $item['duration_months'] ? h($item['duration_months']) . ' tháng' : 'Không xác định' ?></td>
                        <td>
                            <button class="btn btn-sm btn-ghost text-primary" onclick='editItem(<?= json_encode($item) ?>)'><i class="fas fa-edit"></i></button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div id="itemModal" class="modal">
    <div class="modal-content" style="max-width: 400px;">
        <div class="modal-header">
            <h3 id="modalTitle">Thêm mới</h3>
            <span class="close" onclick="closeModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form action="<?= BASE_URL ?>/category/manage/contract_types" method="POST" id="itemForm">
                <input type="hidden" name="_csrf_token" value="<?= Session::generateCsrfToken() ?>">
                <input type="hidden" name="id" id="item_id" value="0">
                <div class="form-group">
                    <label>Tên Loại Hợp đồng *</label>
                    <input type="text" name="name" id="name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Thời hạn (Tháng) - Để trống nếu Không xác định</label>
                    <input type="number" name="duration_months" id="duration_months" class="form-control">
                </div>
                <div class="text-right mt-3 border-top pt-3">
                    <button type="button" class="btn btn-ghost" onclick="closeModal()">Hủy</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Lưu</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); align-items: center; justify-content: center; }
.modal-content { background: var(--bg-card); border-radius: var(--radius-lg); width: 100%; box-shadow: var(--shadow-lg); }
.modal-header { padding: 1rem 1.5rem; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; }
.close { font-size: 1.5rem; cursor: pointer; color: var(--text-muted); }
.modal-body { padding: 1.5rem; }
</style>

<script>
function openModal() {
    document.getElementById('itemForm').reset();
    document.getElementById('item_id').value = '0';
    document.getElementById('modalTitle').innerText = 'Thêm mới';
    document.getElementById('itemModal').style.display = 'flex';
}
function closeModal() {
    document.getElementById('itemModal').style.display = 'none';
}
function editItem(data) {
    document.getElementById('item_id').value = data.id;
    document.getElementById('name').value = data.name;
    document.getElementById('duration_months').value = data.duration_months || '';
    document.getElementById('modalTitle').innerText = 'Sửa';
    document.getElementById('itemModal').style.display = 'flex';
}
</script>
