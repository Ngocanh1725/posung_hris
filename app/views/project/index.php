<!-- Quản lý Dự án -->
<div class="panel" style="margin-bottom: 24px;">
    <div class="panel-header" style="display:flex; justify-content:space-between; align-items:center;">
        <h3><i class="fas fa-hard-hat"></i> Danh sách Dự án</h3>
        <?php if (Session::isManager() || Session::userRole() === 'Project_Manager'): ?>
        <button class="btn btn-primary btn-sm" onclick="document.getElementById('addProjectModal').style.display='flex'">
            <i class="fas fa-plus"></i> Thêm dự án
        </button>
        <?php endif; ?>
    </div>
    <div class="panel-body">
        <?php if (empty($projects)): ?>
            <div class="empty-state"><i class="fas fa-hard-hat"></i><p>Chưa có dự án nào.</p></div>
        <?php else: ?>
            <div class="table-wrapper">
                <table id="projects-table">
                    <thead>
                        <tr>
                            <th>Mã DA</th>
                            <th>Tên Dự án</th>
                            <th>Khách hàng</th>
                            <th>Địa điểm</th>
                            <th style="text-align:center;">Nhân sự</th>
                            <th style="text-align:center;">Expat</th>
                            <th>Bắt đầu</th>
                            <th>Kết thúc</th>
                            <th>Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($projects as $p): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($p->project_code) ?></strong></td>
                            <td><?= htmlspecialchars($p->project_name) ?></td>
                            <td><?= htmlspecialchars($p->client_name ?? '—') ?></td>
                            <td><?= htmlspecialchars($p->location ?? '—') ?></td>
                            <td style="text-align:center;">
                                <span class="badge badge-active"><?= $p->headcount ?? 0 ?></span>
                            </td>
                            <td style="text-align:center;">
                                <?php if (($p->expat_count ?? 0) > 0): ?>
                                    <span class="badge badge-probation"><?= $p->expat_count ?></span>
                                <?php else: ?>
                                    <span style="color:var(--text-muted);">0</span>
                                <?php endif; ?>
                            </td>
                            <td><?= $p->start_date ? date('d/m/Y', strtotime($p->start_date)) : '—' ?></td>
                            <td><?= $p->end_date ? date('d/m/Y', strtotime($p->end_date)) : '—' ?></td>
                            <td>
                                <?php
                                $statusMap = [
                                    'In_Progress' => ['Đang thực hiện', 'badge-active'],
                                    'Completed'   => ['Hoàn thành', 'badge-resigned'],
                                    'Suspended'   => ['Tạm dừng', 'badge-probation'],
                                ];
                                $s = $statusMap[$p->status] ?? [$p->status, ''];
                                ?>
                                <span class="badge <?= $s[1] ?>"><?= $s[0] ?></span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Thêm Dự án -->
<div id="addProjectModal" class="modal-overlay" style="display:none;">
    <div class="modal-content" style="max-width:600px;">
        <div class="modal-header">
            <h3><i class="fas fa-plus-circle"></i> Thêm Dự án mới</h3>
            <button class="modal-close" onclick="this.closest('.modal-overlay').style.display='none'">&times;</button>
        </div>
        <form method="POST" action="<?= BASE_URL ?>/project/store">
            <div class="modal-body">
                <div class="form-grid" style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                    <div class="form-group">
                        <label>Mã dự án <span class="required">*</span></label>
                        <input type="text" name="project_code" required placeholder="VD: SEHC-2026">
                    </div>
                    <div class="form-group">
                        <label>Tên dự án <span class="required">*</span></label>
                        <input type="text" name="project_name" required placeholder="VD: Samsung SEHC Phase 3">
                    </div>
                    <div class="form-group">
                        <label>Khách hàng</label>
                        <input type="text" name="client_name" placeholder="VD: Samsung Electronics">
                    </div>
                    <div class="form-group">
                        <label>Địa điểm</label>
                        <input type="text" name="location" placeholder="VD: Bắc Ninh">
                    </div>
                    <div class="form-group">
                        <label>Ngày bắt đầu</label>
                        <input type="date" name="start_date">
                    </div>
                    <div class="form-group">
                        <label>Ngày kết thúc</label>
                        <input type="date" name="end_date">
                    </div>
                    <div class="form-group">
                        <label>Trạng thái</label>
                        <select name="status">
                            <option value="In_Progress">Đang thực hiện</option>
                            <option value="Completed">Hoàn thành</option>
                            <option value="Suspended">Tạm dừng</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="this.closest('.modal-overlay').style.display='none'">Hủy</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Lưu dự án</button>
            </div>
        </form>
    </div>
</div>

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
</style>
