<div class="panel">
    <div class="panel-header d-flex justify-content-between align-items-center">
        <h3><i class="fas fa-money-bill-wave"></i> Danh mục <?= htmlspecialchars($title) ?></h3>
        <button class="btn btn-primary btn-sm" onclick="openModal()"><i class="fas fa-plus"></i> Thêm mới</button>
    </div>
    <div class="panel-body p-0">
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>Mã PC</th>
                        <th>Tên Phụ cấp</th>
                        <th>Loại PC</th>
                        <th>Số tiền (VNĐ)</th>
                        <th>Chịu thuế TNCN?</th>
                        <th width="120">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($item['code']) ?></strong></td>
                        <td class="fw-bold"><?= htmlspecialchars($item['name']) ?></td>
                        <td><?= htmlspecialchars($item['type']) ?></td>
                        <td class="text-primary fw-bold"><?= number_format($item['amount']) ?></td>
                        <td>
                            <?php if ($item['is_taxable']): ?>
                                <span class="badge bg-danger text-white">Chịu thuế</span>
                            <?php else: ?>
                                <span class="badge bg-secondary text-white">Miễn thuế</span>
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
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h3 id="modalTitle">Thêm mới</h3>
            <span class="close" onclick="closeModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form action="<?= BASE_URL ?>/category/manage/allowances" method="POST" id="itemForm">
                <input type="hidden" name="id" id="item_id" value="0">
                <div class="form-row" style="display:flex; margin:-10px;">
                    <div class="form-group" style="width:40%; padding:10px;">
                        <label>Mã Phụ cấp *</label>
                        <input type="text" name="code" id="code" class="form-control" required>
                    </div>
                    <div class="form-group" style="width:60%; padding:10px;">
                        <label>Tên Phụ cấp *</label>
                        <input type="text" name="name" id="name" class="form-control" required>
                    </div>
                </div>
                <div class="form-row" style="display:flex; margin:-10px;">
                    <div class="form-group" style="width:50%; padding:10px;">
                        <label>Loại Phụ cấp</label>
                        <select name="type" id="type" class="form-control">
                            <option value="Fixed">Cố định (Hàng tháng)</option>
                            <option value="Variable">Biến đổi (Theo ngày/Ca)</option>
                            <option value="Percentage">Theo Tỉ lệ % Lương</option>
                        </select>
                    </div>
                    <div class="form-group" style="width:50%; padding:10px;">
                        <label>Mức tiền cơ bản (VNĐ)</label>
                        <input type="number" step="1000" name="amount" id="amount" class="form-control" value="0">
                    </div>
                </div>
                <div class="form-group">
                    <label>Tính thuế TNCN</label>
                    <select name="is_taxable" id="is_taxable" class="form-control">
                        <option value="1">Có chịu thuế</option>
                        <option value="0">Không chịu thuế</option>
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
.bg-danger { background-color: #ef4444; }
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
    document.getElementById('code').value = data.code;
    document.getElementById('name').value = data.name;
    document.getElementById('type').value = data.type;
    document.getElementById('amount').value = data.amount;
    document.getElementById('is_taxable').value = data.is_taxable;
    document.getElementById('modalTitle').innerText = 'Sửa';
    document.getElementById('itemModal').style.display = 'flex';
}
</script>
