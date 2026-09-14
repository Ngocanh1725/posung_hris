<div class="panel">
    <div class="panel-header d-flex justify-content-between align-items-center">
        <h3><i class="fas fa-sitemap"></i> Danh mục Phòng Ban</h3>
        <button class="btn btn-primary btn-sm" onclick="openModal()"><i class="fas fa-plus"></i> Thêm Phòng ban</button>
    </div>
    <div class="panel-body p-0">
        <div class="table-wrapper">
            <table class="table" id="dataTable">
                <thead>
                    <tr>
                        <th>Mã PB</th>
                        <th>Tên Phòng ban</th>
                        <th>Chi nhánh</th>
                        <th>Trưởng phòng</th>
                        <th>Mô tả</th>
                        <th width="120">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($departments as $dept): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($dept['dept_code']) ?></strong></td>
                        <td><?= htmlspecialchars($dept['dept_name']) ?></td>
                        <td><span class="badge bg-secondary"><?= htmlspecialchars($dept['branch']) ?></span></td>
                        <td>
                            <?php 
                            $mgr = '---';
                            foreach($employees as $e) {
                                if($e->id == $dept['manager_id']) { $mgr = $e->full_name; break; }
                            }
                            echo htmlspecialchars($mgr);
                            ?>
                        </td>
                        <td><small class="text-muted"><?= htmlspecialchars($dept['description'] ?? '') ?></small></td>
                        <td>
                            <button class="btn btn-sm btn-ghost text-primary" onclick='editDept(<?= json_encode($dept) ?>)'><i class="fas fa-edit"></i></button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div id="deptModal" class="modal">
    <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header">
            <h3 id="modalTitle">Thêm Phòng ban</h3>
            <span class="close" onclick="closeModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form action="<?= BASE_URL ?>/category/departments" method="POST" id="deptForm">
                <input type="hidden" name="id" id="dept_id" value="0">
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>Mã PB *</label>
                        <input type="text" name="dept_code" id="dept_code" class="form-control" required>
                    </div>
                    <div class="form-group col-md-8">
                        <label>Tên Phòng ban *</label>
                        <input type="text" name="dept_name" id="dept_name" class="form-control" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Chi nhánh</label>
                        <select name="branch" id="branch" class="form-control">
                            <option value="Hanoi_HQ">Hanoi_HQ</option>
                            <option value="HCM_Office">HCM_Office</option>
                            <option value="Factory_Spool">Factory_Spool</option>
                            <option value="Site_Project">Site_Project</option>
                        </select>
                    </div>
                    <div class="form-group col-md-6">
                        <label>Phòng ban Cha</label>
                        <select name="parent_id" id="parent_id" class="form-control">
                            <option value="">-- Không có --</option>
                            <?php foreach ($departments as $d): ?>
                                <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['dept_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Trưởng phòng</label>
                    <select name="manager_id" id="manager_id" class="form-control">
                        <option value="">-- Chọn NV --</option>
                        <?php foreach ($employees as $e): ?>
                            <option value="<?= $e->id ?>"><?= htmlspecialchars($e->full_name) ?> (<?= htmlspecialchars($e->emp_code) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Mô tả chức năng</label>
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
.col-md-4 { width: 33.33%; padding: 10px; }
.col-md-8 { width: 66.66%; padding: 10px; }
.col-md-6 { width: 50%; padding: 10px; }
@keyframes slideDown { from { transform: translateY(-20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
</style>

<script>
function openModal() {
    document.getElementById('deptForm').reset();
    document.getElementById('dept_id').value = '0';
    document.getElementById('modalTitle').innerText = 'Thêm Phòng ban';
    document.getElementById('deptModal').style.display = 'flex';
}
function closeModal() {
    document.getElementById('deptModal').style.display = 'none';
}
function editDept(data) {
    document.getElementById('dept_id').value = data.id;
    document.getElementById('dept_code').value = data.dept_code;
    document.getElementById('dept_name').value = data.dept_name;
    document.getElementById('branch').value = data.branch;
    document.getElementById('parent_id').value = data.parent_id || '';
    document.getElementById('manager_id').value = data.manager_id || '';
    document.getElementById('description').value = data.description || '';
    document.getElementById('modalTitle').innerText = 'Sửa Phòng ban';
    document.getElementById('deptModal').style.display = 'flex';
}
</script>
