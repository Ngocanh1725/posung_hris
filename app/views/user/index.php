<!-- Quản lý Tài khoản -->
<div class="panel">
    <div class="panel-header" style="display:flex; justify-content:space-between; align-items:center;">
        <h3><i class="fas fa-user-shield"></i> Danh sách Tài khoản</h3>
        <button class="btn btn-primary btn-sm" onclick="document.getElementById('addUserModal').style.display='flex'">
            <i class="fas fa-user-plus"></i> Thêm tài khoản
        </button>
    </div>
    <div class="panel-body">
        <?php if (empty($users)): ?>
            <div class="empty-state"><i class="fas fa-user-shield"></i><p>Chưa có tài khoản nào.</p></div>
        <?php else: ?>
            <div class="table-wrapper">
                <table id="users-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tên đăng nhập</th>
                            <th>Họ và tên</th>
                            <th>Email</th>
                            <th>Vai trò</th>
                            <th>Trạng thái</th>
                            <th>Ngày tạo</th>
                            <th style="text-align:center;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                        <tr>
                            <td><?= $u['id'] ?></td>
                            <td><strong><?= htmlspecialchars($u['username']) ?></strong></td>
                            <td><?= htmlspecialchars($u['full_name']) ?></td>
                            <td><?= htmlspecialchars($u['email'] ?? '—') ?></td>
                            <td>
                                <?php
                                $roleColors = [
                                    'Admin' => 'background:rgba(239,68,68,0.15); color:#f87171;',
                                    'HR_Manager' => 'background:rgba(99,102,241,0.15); color:#818cf8;',
                                    'Project_Manager' => 'background:rgba(245,158,11,0.15); color:#fbbf24;',
                                    'Site_Supervisor' => 'background:rgba(16,185,129,0.15); color:#34d399;',
                                    'Employee' => 'background:rgba(255,255,255,0.08); color:var(--text-secondary);',
                                ];
                                $roleLabels = [
                                    'Admin' => 'Quản trị viên',
                                    'HR_Manager' => 'TP Nhân sự',
                                    'Project_Manager' => 'Giám đốc DA',
                                    'Site_Supervisor' => 'Giám sát CT',
                                    'Employee' => 'Nhân viên',
                                ];
                                ?>
                                <span class="badge" style="<?= $roleColors[$u['role']] ?? '' ?>">
                                    <?= $roleLabels[$u['role']] ?? $u['role'] ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($u['status'] === 'Active'): ?>
                                    <span class="badge badge-active">Hoạt động</span>
                                <?php elseif ($u['status'] === 'Locked'): ?>
                                    <span class="badge badge-resigned">Khóa</span>
                                <?php else: ?>
                                    <span class="badge" style="background:rgba(255,255,255,0.08);"><?= $u['status'] ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?= $u['created_at'] ? date('d/m/Y', strtotime($u['created_at'])) : '—' ?></td>
                            <td style="text-align:center;">
                                <div style="display:flex; gap:6px; justify-content:center;">
                                    <button class="action-btn" title="Sửa" onclick="editUser(<?= htmlspecialchars(json_encode($u)) ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <a href="<?= BASE_URL ?>/user/resetPassword/<?= $u['id'] ?>" 
                                       class="action-btn" title="Reset mật khẩu"
                                       onclick="return confirm('Reset mật khẩu tài khoản <?= htmlspecialchars($u['username']) ?> về 123456?');">
                                        <i class="fas fa-key"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Thêm Tài khoản -->
<div id="addUserModal" class="modal-overlay" style="display:none;">
    <div class="modal-content" style="max-width:550px;">
        <div class="modal-header">
            <h3><i class="fas fa-user-plus"></i> Thêm Tài khoản mới</h3>
            <button class="modal-close" onclick="this.closest('.modal-overlay').style.display='none'">&times;</button>
        </div>
        <form method="POST" action="<?= BASE_URL ?>/user/store">
            <div class="modal-body">
                <div class="form-grid" style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                    <div class="form-group">
                        <label>Tên đăng nhập <span class="required">*</span></label>
                        <input type="text" name="username" required placeholder="VD: admin hoặc PS-2026-0001">
                    </div>
                    <div class="form-group">
                        <label>Mật khẩu</label>
                        <input type="text" name="password" value="123456" placeholder="Mặc định: 123456">
                    </div>
                    <div class="form-group" style="grid-column:1/-1;">
                        <label>Họ và tên <span class="required">*</span></label>
                        <input type="text" name="full_name" required placeholder="Nguyễn Văn A">
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" placeholder="email@posung.vn">
                    </div>
                    <div class="form-group">
                        <label>Vai trò <span class="required">*</span></label>
                        <select name="role" required>
                            <option value="Employee">Nhân viên</option>
                            <option value="Site_Supervisor">Giám sát Công trường</option>
                            <option value="Project_Manager">Giám đốc Dự án</option>
                            <option value="HR_Manager">Trưởng phòng Nhân sự</option>
                            <option value="Admin">Quản trị viên</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="this.closest('.modal-overlay').style.display='none'">Hủy</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Tạo tài khoản</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Sửa Tài khoản -->
<div id="editUserModal" class="modal-overlay" style="display:none;">
    <div class="modal-content" style="max-width:550px;">
        <div class="modal-header">
            <h3><i class="fas fa-user-edit"></i> Cập nhật Tài khoản</h3>
            <button class="modal-close" onclick="this.closest('.modal-overlay').style.display='none'">&times;</button>
        </div>
        <form method="POST" id="editUserForm">
            <div class="modal-body">
                <div class="form-grid" style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                    <div class="form-group" style="grid-column:1/-1;">
                        <label>Họ và tên</label>
                        <input type="text" name="full_name" id="edit_full_name" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" id="edit_email">
                    </div>
                    <div class="form-group">
                        <label>Vai trò</label>
                        <select name="role" id="edit_role">
                            <option value="Employee">Nhân viên</option>
                            <option value="Site_Supervisor">Giám sát Công trường</option>
                            <option value="Project_Manager">Giám đốc Dự án</option>
                            <option value="HR_Manager">Trưởng phòng Nhân sự</option>
                            <option value="Admin">Quản trị viên</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Trạng thái</label>
                        <select name="status" id="edit_status">
                            <option value="Active">Hoạt động</option>
                            <option value="Inactive">Vô hiệu</option>
                            <option value="Locked">Khóa</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="this.closest('.modal-overlay').style.display='none'">Hủy</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Cập nhật</button>
            </div>
        </form>
    </div>
</div>

<script>
function editUser(user) {
    document.getElementById('editUserForm').action = '<?= BASE_URL ?>/user/update/' + user.id;
    document.getElementById('edit_full_name').value = user.full_name || '';
    document.getElementById('edit_email').value = user.email || '';
    document.getElementById('edit_role').value = user.role || 'Employee';
    document.getElementById('edit_status').value = user.status || 'Active';
    document.getElementById('editUserModal').style.display = 'flex';
}
</script>

<style>
.modal-overlay {
    position: fixed; top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0,0,0,0.6); z-index: 9999;
    display: flex; align-items: center; justify-content: center;
    backdrop-filter: blur(4px);
}
.modal-content {
    background: var(--bg-card, #1e2139); border-radius: 16px;
    width: 90%; box-shadow: 0 20px 60px rgba(0,0,0,0.4);
    border: 1px solid rgba(255,255,255,0.08);
}
.modal-header {
    display: flex; justify-content: space-between; align-items: center;
    padding: 20px 24px; border-bottom: 1px solid rgba(255,255,255,0.06);
}
.modal-header h3 { margin: 0; font-size: 1.1rem; }
.modal-close {
    background: none; border: none; color: var(--text-muted); font-size: 1.5rem;
    cursor: pointer; line-height: 1;
}
.modal-close:hover { color: var(--danger); }
.modal-body { padding: 24px; }
.modal-footer {
    padding: 16px 24px; border-top: 1px solid rgba(255,255,255,0.06);
    display: flex; justify-content: flex-end; gap: 12px;
}
.form-group { margin-bottom: 0; }
.form-group label { display: block; margin-bottom: 6px; font-weight: 500; font-size: 0.85rem; color: var(--text-secondary); }
.form-group input, .form-group select {
    width: 100%; padding: 10px 14px; border-radius: 8px;
    border: 1px solid rgba(255,255,255,0.1); background: rgba(255,255,255,0.04);
    color: var(--text-primary); font-size: 0.9rem;
}
.form-group input:focus, .form-group select:focus {
    outline: none; border-color: var(--primary-light);
    box-shadow: 0 0 0 3px rgba(99,102,241,0.2);
}
.required { color: var(--danger); }
.btn { padding: 10px 20px; border-radius: 8px; border: none; cursor: pointer; font-weight: 600; font-size: 0.85rem; transition: all 0.2s; }
.btn-primary { background: var(--primary); color: #fff; }
.btn-primary:hover { background: var(--primary-light); transform: translateY(-1px); }
.btn-secondary { background: rgba(255,255,255,0.08); color: var(--text-secondary); }
.btn-secondary:hover { background: rgba(255,255,255,0.12); }
.btn-sm { padding: 8px 16px; font-size: 0.8rem; }
.action-btn {
    width: 32px; height: 32px; border-radius: 8px; border: none;
    background: rgba(255,255,255,0.06); color: var(--text-secondary);
    cursor: pointer; display: flex; align-items: center; justify-content: center;
    transition: all 0.2s;
}
.action-btn:hover { background: rgba(99,102,241,0.15); color: var(--primary-light); }
</style>
