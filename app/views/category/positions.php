<div class="panel">
    <div class="panel-header d-flex justify-content-between align-items-center">
        <h3><i class="fas fa-id-badge"></i> Danh mục Chức vụ / Vị trí</h3>
        <button class="btn btn-primary btn-sm" onclick="openModal()"><i class="fas fa-plus"></i> Thêm Chức vụ</button>
    </div>
    <div class="panel-body p-0">
        <div class="table-wrapper">
            <table class="table" id="dataTable">
                <thead>
                    <tr>
                        <th>Mã CV</th>
                        <th>Chức danh</th>
                        <th>Cấp bậc (Level)</th>
                        <th>Mức PC (Hệ số)</th>
                        <th>Mô tả</th>
                        <th width="120">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($positions as $pos): ?>
                    <tr>
                        <td><strong><?= h($pos['pos_code']) ?></strong></td>
                        <td class="fw-bold text-primary"><?= h($pos['pos_title']) ?></td>
                        <td><span class="badge badge-<?= $pos['job_level'] >= 3 ? 'success' : 'secondary' ?>">Level <?= $pos['job_level'] ?></span></td>
                        <td><?= h($pos['allowance_rate']) ?></td>
                        <td><small class="text-muted"><?= h($pos['description'] ?? '') ?></small></td>
                        <td>
                            <button class="btn btn-sm btn-ghost text-primary" onclick='editPos(<?= json_encode($pos) ?>)'><i class="fas fa-edit"></i></button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div id="posModal" class="modal">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h3 id="modalTitle">Thêm Chức vụ</h3>
            <span class="close" onclick="closeModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form action="<?= BASE_URL ?>/category/positions" method="POST" id="posForm">
                <input type="hidden" name="_csrf_token" value="<?= Session::generateCsrfToken() ?>">
                <input type="hidden" name="id" id="pos_id" value="0">
                <div class="form-row">
                    <div class="form-group col-md-5">
                        <label>Mã CV *</label>
                        <input type="text" name="pos_code" id="pos_code" class="form-control" required>
                    </div>
                    <div class="form-group col-md-7">
                        <label>Chức danh *</label>
                        <input type="text" name="pos_title" id="pos_title" class="form-control" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Cấp bậc (1-5)</label>
                        <input type="number" name="job_level" id="job_level" class="form-control" value="1" min="1" max="5" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label>Hệ số PC</label>
                        <input type="number" step="0.01" name="allowance_rate" id="allowance_rate" class="form-control" value="0.00">
                    </div>
                </div>
                <div class="form-group">
                    <label>Mô tả công việc chung</label>
                    <textarea name="description" id="description" class="form-control" rows="3"></textarea>
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
.modal-content { background: var(--bg-card); border-radius: var(--radius-lg); width: 100%; max-width: 500px; box-shadow: var(--shadow-lg); animation: slideDown 0.3s var(--ease); }
.modal-header { padding: 1rem 1.5rem; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; }
.modal-header h3 { margin: 0; font-size: 1.1rem; }
.close { font-size: 1.5rem; cursor: pointer; color: var(--text-muted); }
.modal-body { padding: 1.5rem; }
.form-row { display: flex; flex-wrap: wrap; margin: -10px; }
.col-md-5 { width: 41.66%; padding: 10px; }
.col-md-7 { width: 58.33%; padding: 10px; }
.col-md-6 { width: 50%; padding: 10px; }
@keyframes slideDown { from { transform: translateY(-20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
</style>

<script>
function openModal() {
    document.getElementById('posForm').reset();
    document.getElementById('pos_id').value = '0';
    document.getElementById('modalTitle').innerText = 'Thêm Chức vụ';
    document.getElementById('posModal').style.display = 'flex';
}
function closeModal() {
    document.getElementById('posModal').style.display = 'none';
}
function editPos(data) {
    document.getElementById('pos_id').value = data.id;
    document.getElementById('pos_code').value = data.pos_code;
    document.getElementById('pos_title').value = data.pos_title;
    document.getElementById('job_level').value = data.job_level;
    document.getElementById('allowance_rate').value = data.allowance_rate;
    document.getElementById('description').value = data.description || '';
    document.getElementById('modalTitle').innerText = 'Sửa Chức vụ';
    document.getElementById('posModal').style.display = 'flex';
}
</script>
