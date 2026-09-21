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
                        <th>Loại hình</th>
                        <th>Chi nhánh</th>
                        <th>Trưởng phòng</th>
                        <th>Trạng thái</th>
                        <th>Mô tả</th>
                        <th width="120">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($departments as $dept): 
                        $typeClass = strtolower($dept['dept_type'] ?? 'department');
                    ?>
                    <tr>
                        <td><strong><?= h($dept['dept_code']) ?></strong></td>
                        <td>
                            <a href="<?= BASE_URL ?>/organization/detail/<?= $dept['id'] ?>" class="text-link" title="Xem chi tiết">
                                <?= h($dept['dept_name']) ?>
                            </a>
                        </td>
                        <td><span class="dept-type-badge badge-<?= $typeClass ?>"><?= h($dept['dept_type'] ?? 'Department') ?></span></td>
                        <td><span class="badge bg-secondary"><?= h($dept['branch']) ?></span></td>
                        <td>
                            <?php 
                            $mgr = '---';
                            foreach($employees as $e) {
                                if($e->id == $dept['manager_id']) { $mgr = $e->full_name; break; }
                            }
                            echo h($mgr);
                            ?>
                        </td>
                        <td>
                            <span class="badge <?= ($dept['status'] ?? 'Active') === 'Active' ? 'badge-active' : 'badge-inactive' ?>" style="font-size:0.65rem;">
                                <?= ($dept['status'] ?? 'Active') === 'Active' ? '● Active' : '○ Inactive' ?>
                            </span>
                        </td>
                        <td><small class="text-muted"><?= h(mb_substr($dept['description'] ?? '', 0, 50)) ?><?= mb_strlen($dept['description'] ?? '') > 50 ? '…' : '' ?></small></td>
                        <td>
                            <button class="btn btn-sm btn-ghost text-primary" onclick='editDept(<?= json_encode($dept, JSON_UNESCAPED_UNICODE) ?>)' title="Sửa"><i class="fas fa-edit"></i></button>
                            <a href="<?= BASE_URL ?>/organization/detail/<?= $dept['id'] ?>" class="btn btn-sm btn-ghost" title="Xem chi tiết"><i class="fas fa-eye"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Thêm/Sửa Phòng Ban -->
<div id="deptModal" class="modal">
    <div class="modal-content" style="max-width: 720px;">
        <div class="modal-header">
            <h3 id="modalTitle">Thêm Phòng ban</h3>
            <span class="close" onclick="closeModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form action="<?= BASE_URL ?>/category/departments" method="POST" id="deptForm">
                <input type="hidden" name="_csrf_token" value="<?= Session::generateCsrfToken() ?>">
                <input type="hidden" name="id" id="dept_id" value="0">
                
                <!-- Dòng 1: Mã PB + Tên + Loại hình -->
                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label>Mã PB *</label>
                        <input type="text" name="dept_code" id="dept_code" class="form-control" required placeholder="VD: HQ-HC">
                    </div>
                    <div class="form-group col-md-5">
                        <label>Tên Phòng ban *</label>
                        <input type="text" name="dept_name" id="dept_name" class="form-control" required placeholder="VD: Phòng Hành chính">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Loại hình</label>
                        <select name="dept_type" id="dept_type" class="form-control">
                            <option value="Division">Division (Ban lãnh đạo)</option>
                            <option value="Department" selected>Department (Phòng ban)</option>
                            <option value="Team">Team (Nhóm/Tổ)</option>
                            <option value="Project">Project (Dự án)</option>
                        </select>
                    </div>
                </div>

                <!-- Dòng 2: Chi nhánh + Phòng ban cha + Trạng thái -->
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>Chi nhánh</label>
                        <select name="branch" id="branch" class="form-control">
                            <option value="Hanoi_HQ">Hanoi_HQ</option>
                            <option value="HCM_Office">HCM_Office</option>
                            <option value="Factory_Spool">Factory_Spool</option>
                            <option value="Site_Project">Site_Project</option>
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label>Phòng ban Cha</label>
                        <select name="parent_id" id="parent_id" class="form-control">
                            <option value="">-- Không có --</option>
                            <?php foreach ($departments as $d): ?>
                                <option value="<?= $d['id'] ?>"><?= h($d['dept_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label>Trạng thái</label>
                        <select name="status" id="dept_status" class="form-control">
                            <option value="Active">Active (Hoạt động)</option>
                            <option value="Inactive">Inactive (Ngưng)</option>
                        </select>
                    </div>
                </div>

                <!-- Dòng 3: Trưởng phòng + Thứ tự + Ngày thành lập -->
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>Trưởng phòng</label>
                        <select name="manager_id" id="manager_id" class="form-control">
                            <option value="">-- Chọn NV --</option>
                            <?php foreach ($employees as $e): ?>
                                <option value="<?= $e->id ?>"><?= h($e->full_name) ?> (<?= h($e->emp_code) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label>Ngày thành lập</label>
                        <input type="date" name="established_date" id="established_date" class="form-control">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Thứ tự hiển thị</label>
                        <input type="number" name="sort_order" id="sort_order" class="form-control" value="0" min="0">
                    </div>
                </div>

                <!-- Dòng 4: Liên hệ -->
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label><i class="fas fa-phone" style="margin-right:4px;"></i> Điện thoại</label>
                        <input type="text" name="phone" id="dept_phone" class="form-control" placeholder="024-3755-XXXX">
                    </div>
                    <div class="form-group col-md-4">
                        <label><i class="fas fa-envelope" style="margin-right:4px;"></i> Email</label>
                        <input type="email" name="email" id="dept_email" class="form-control" placeholder="dept@posung.vn">
                    </div>
                    <div class="form-group col-md-4">
                        <label><i class="fas fa-map-marker-alt" style="margin-right:4px;"></i> Địa điểm</label>
                        <input type="text" name="office_location" id="office_location" class="form-control" placeholder="Tầng 8, Tòa nhà...">
                    </div>
                </div>

                <!-- Mô tả chi tiết -->
                <div class="form-group">
                    <label>Mô tả chi tiết chức năng</label>
                    <textarea name="description" id="description" class="form-control" rows="3" placeholder="Mô tả chi tiết chức năng, nhiệm vụ của bộ phận..."></textarea>
                </div>

                <!-- Chức năng / Nhiệm vụ (mỗi dòng 1 nhiệm vụ) -->
                <div class="form-group">
                    <label>Danh sách Chức năng / Nhiệm vụ <small class="text-muted">(mỗi dòng 1 nhiệm vụ)</small></label>
                    <textarea name="functions" id="dept_functions" class="form-control" rows="5" placeholder="Tuyển dụng và onboarding nhân viên mới&#10;Quản lý hồ sơ nhân sự&#10;Tính lương, BHXH, BHYT..."></textarea>
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
.modal-content { background: var(--bg-card); border-radius: var(--radius-lg); width: 100%; max-width: 720px; box-shadow: var(--shadow-lg); animation: slideDown 0.3s var(--ease); max-height: 90vh; overflow-y: auto; }
.modal-header { padding: 1rem 1.5rem; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; background: var(--bg-card); z-index: 10; }
.modal-header h3 { margin: 0; font-size: 1.1rem; }
.close { font-size: 1.5rem; cursor: pointer; color: var(--text-muted); }
.modal-body { padding: 1.5rem; }
.form-row { display: flex; flex-wrap: wrap; margin: -8px; }
.col-md-3 { width: 25%; padding: 8px; }
.col-md-4 { width: 33.33%; padding: 8px; }
.col-md-5 { width: 41.66%; padding: 8px; }
.col-md-6 { width: 50%; padding: 8px; }
.col-md-8 { width: 66.66%; padding: 8px; }
@media (max-width: 768px) {
    .col-md-3, .col-md-4, .col-md-5, .col-md-6, .col-md-8 { width: 100%; }
}
@keyframes slideDown { from { transform: translateY(-20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

/* Badges */
.text-link { color: var(--primary-light); text-decoration: none; }
.text-link:hover { text-decoration: underline; }
.dept-type-badge { font-size: 0.65rem; padding: 2px 8px; border-radius: 6px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
.badge-division { background: rgba(245,158,11,0.15); color: #f59e0b; }
.badge-department { background: rgba(99,102,241,0.15); color: var(--primary-light); }
.badge-team { background: rgba(59,130,246,0.15); color: #3b82f6; }
.badge-project { background: rgba(16,185,129,0.15); color: #10b981; }
.badge-active { background: rgba(16,185,129,0.15); color: #10b981; }
.badge-inactive { background: rgba(239,68,68,0.15); color: #ef4444; }
</style>

<script>
function openModal() {
    document.getElementById('deptForm').reset();
    document.getElementById('dept_id').value = '0';
    document.getElementById('sort_order').value = '0';
    document.getElementById('dept_type').value = 'Department';
    document.getElementById('dept_status').value = 'Active';
    document.getElementById('modalTitle').innerText = 'Thêm Phòng ban';
    document.getElementById('deptModal').style.display = 'flex';
}
function closeModal() {
    document.getElementById('deptModal').style.display = 'none';
}
function editDept(data) {
    document.getElementById('dept_id').value = data.id;
    document.getElementById('dept_code').value = data.dept_code || '';
    document.getElementById('dept_name').value = data.dept_name || '';
    document.getElementById('branch').value = data.branch || 'Hanoi_HQ';
    document.getElementById('parent_id').value = data.parent_id || '';
    document.getElementById('manager_id').value = data.manager_id || '';
    document.getElementById('description').value = data.description || '';
    document.getElementById('dept_type').value = data.dept_type || 'Department';
    document.getElementById('dept_status').value = data.status || 'Active';
    document.getElementById('dept_phone').value = data.phone || '';
    document.getElementById('dept_email').value = data.email || '';
    document.getElementById('office_location').value = data.office_location || '';
    document.getElementById('established_date').value = data.established_date || '';
    document.getElementById('sort_order').value = data.sort_order || '0';
    
    // Parse JSON functions thành từng dòng
    var funcs = '';
    if (data.functions) {
        try {
            var arr = JSON.parse(data.functions);
            if (Array.isArray(arr)) {
                funcs = arr.join("\n");
            }
        } catch(e) {
            funcs = data.functions;
        }
    }
    document.getElementById('dept_functions').value = funcs;
    
    document.getElementById('modalTitle').innerText = 'Sửa Phòng ban';
    document.getElementById('deptModal').style.display = 'flex';
}
</script>
