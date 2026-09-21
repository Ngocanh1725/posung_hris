<div class="panel">
    <div class="panel-header d-flex justify-content-between align-items-center">
        <h3><i class="fas fa-calendar-times"></i> Danh mục <?= h($title) ?></h3>
        <button class="btn btn-primary btn-sm" onclick="openModal()"><i class="fas fa-plus"></i> Thêm mới</button>
    </div>
    <div class="panel-body p-0">
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>Tên Loại Nghỉ phép</th>
                        <th>Số ngày tối đa/năm</th>
                        <th>Hưởng lương?</th>
                        <th width="120">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td class="fw-bold"><?= h($item['name']) ?></td>
                        <td><?= h($item['days_per_year']) ?></td>
                        <td>
                            <?php if ($item['is_paid']): ?>
                                <span class="badge bg-success text-white">Có hưởng lương</span>
                            <?php else: ?>
                                <span class="badge bg-secondary text-white">Không lương</span>
                            <?php endif; ?>
                        </td>
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

<div id="itemModal" class="modal">
    <div class="modal-content" style="max-width: 400px;">
        <div class="modal-header">
            <h3 id="modalTitle">Thêm mới</h3>
            <span class="close" onclick="closeModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form action="<?= BASE_URL ?>/category/manage/leave_types" method="POST" id="itemForm">
                <input type="hidden" name="_csrf_token" value="<?= Session::generateCsrfToken() ?>">
                <input type="hidden" name="id" id="item_id" value="0">
                <div class="form-group">
                    <label>Tên Loại phép *</label>
                    <input type="text" name="name" id="name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Số ngày định mức/năm</label>
                    <input type="number" step="0.5" name="days_per_year" id="days_per_year" class="form-control" value="12">
                </div>
                <div class="form-group">
                    <label>Chế độ lương</label>
                    <select name="is_paid" id="is_paid" class="form-control">
                        <option value="1">Có hưởng lương</option>
                        <option value="0">Không hưởng lương</option>
                    </select>
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
.bg-success { background-color: #22c55e; }
.text-white { color: white; }
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
    document.getElementById('days_per_year').value = data.days_per_year;
    document.getElementById('is_paid').value = data.is_paid;
    document.getElementById('modalTitle').innerText = 'Sửa';
    document.getElementById('itemModal').style.display = 'flex';
}
</script>
