<?php
/**
 * ============================================================
 *  View: employee/index.php
 *  Hiển thị danh sách nhân sự với DataTables
 * ============================================================
 */
?>

<!-- Include DataTables CSS -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css"/>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css"/>

<div class="panel">
    <div class="panel-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h3><i class="fas fa-users"></i> Quản lý Hồ sơ Nhân sự</h3>
        <?php if (Session::isManager() || Session::userRole() === 'Project_Manager'): ?>
        <a href="<?= BASE_URL ?>/employee/create" class="btn btn-primary">
            <i class="fas fa-plus"></i> Thêm mới
        </a>
        <?php endif; ?>
    </div>

    <!-- Bộ lọc -->
    <div class="panel-body border-bottom">
        <form method="GET" action="<?= BASE_URL ?>/employee/index" class="filter-form">
            <div class="filter-row">
                <div class="form-group mb-0">
                    <input type="text" name="search" class="form-control" placeholder="Tìm tên, mã, SĐT..." value="<?= htmlspecialchars($filters['search']) ?>">
                </div>
                <div class="form-group mb-0">
                    <select name="status" class="form-control">
                        <option value="">-- Tất cả trạng thái --</option>
                        <option value="Active" <?= $filters['status'] === 'Active' ? 'selected' : '' ?>>Đang làm việc (Active)</option>
                        <option value="Probation" <?= $filters['status'] === 'Probation' ? 'selected' : '' ?>>Thử việc</option>
                        <option value="Resigned" <?= $filters['status'] === 'Resigned' ? 'selected' : '' ?>>Đã nghỉ việc</option>
                        <option value="Blacklisted" <?= $filters['status'] === 'Blacklisted' ? 'selected' : '' ?>>Blacklist HSE</option>
                    </select>
                </div>
                <div class="form-group mb-0">
                    <select name="type" class="form-control">
                        <option value="">-- Loại hình --</option>
                        <option value="Expat" <?= $filters['type'] === 'Expat' ? 'selected' : '' ?>>Chuyên gia (Expat)</option>
                        <option value="Office_BIM" <?= $filters['type'] === 'Office_BIM' ? 'selected' : '' ?>>Văn phòng / BIM</option>
                        <option value="Site_Engineer" <?= $filters['type'] === 'Site_Engineer' ? 'selected' : '' ?>>Kỹ sư hiện trường</option>
                        <option value="Direct_Worker" <?= $filters['type'] === 'Direct_Worker' ? 'selected' : '' ?>>Công nhân trực tiếp</option>
                    </select>
                </div>
                <div class="form-group mb-0">
                    <select name="project" class="form-control">
                        <option value="0">-- Chọn Dự án --</option>
                        <?php foreach ($projects as $prj): ?>
                            <option value="<?= $prj->id ?>" <?= $filters['projectId'] == $prj->id ? 'selected' : '' ?>><?= htmlspecialchars($prj->project_name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Lọc</button>
            </div>
        </form>
    </div>

    <!-- Bảng dữ liệu -->
    <div class="panel-body">
        <table id="employeesTable" class="table table-striped nowrap" style="width:100%">
            <thead>
                <tr>
                    <th>Mã NV</th>
                    <th>Nhân viên</th>
                    <th>Liên hệ</th>
                    <th>Dự án / Phòng ban</th>
                    <th>Loại NS</th>
                    <th>Trạng thái</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($employees as $emp): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($emp->emp_code) ?></strong></td>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar-sm">
                                <?php if (!empty($emp->avatar_path)): ?>
                                    <img src="<?= BASE_URL ?>/<?= htmlspecialchars($emp->avatar_path) ?>" alt="Avatar" class="avatar-img">
                                <?php else: ?>
                                    <div class="avatar-initial"><?= mb_substr($emp->full_name, 0, 1) ?></div>
                                <?php endif; ?>
                            </div>
                            <div>
                                <div class="fw-bold"><?= htmlspecialchars($emp->full_name) ?></div>
                                <div class="text-muted small"><?= htmlspecialchars($emp->pos_title ?? 'Chưa rõ') ?></div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="small"><i class="fas fa-phone text-muted"></i> <?= htmlspecialchars($emp->phone ?? '') ?></div>
                        <div class="small"><i class="fas fa-envelope text-muted"></i> <?= htmlspecialchars($emp->email ?? '') ?></div>
                    </td>
                    <td>
                        <div class="fw-bold text-primary"><?= htmlspecialchars($emp->project_name ?? 'N/A') ?></div>
                        <div class="small text-muted"><?= htmlspecialchars($emp->dept_name ?? 'Chưa phân bổ') ?></div>
                    </td>
                    <td>
                        <?php if ($emp->employee_type === 'Expat'): ?>
                            <span class="badge" style="background: rgba(139,92,246,0.15); color: #c4b5fd;"><i class="fas fa-plane"></i> Expat</span>
                        <?php elseif ($emp->employee_type === 'Office_BIM'): ?>
                            <span class="badge" style="background: rgba(59,130,246,0.15); color: #93c5fd;"><i class="fas fa-laptop"></i> Office</span>
                        <?php else: ?>
                            <span class="badge" style="background: rgba(245,158,11,0.15); color: #fcd34d;"><i class="fas fa-hard-hat"></i> Site</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($emp->status === 'Active'): ?>
                            <span class="badge badge-active"><i class="fas fa-check-circle"></i> Đang làm</span>
                        <?php elseif ($emp->status === 'Probation'): ?>
                            <span class="badge badge-probation"><i class="fas fa-clock"></i> Thử việc</span>
                        <?php elseif ($emp->status === 'Blacklisted'): ?>
                            <span class="badge" style="background: rgba(0,0,0,0.5); color: #ef4444;"><i class="fas fa-ban"></i> Blacklist</span>
                        <?php else: ?>
                            <span class="badge badge-resigned"><i class="fas fa-sign-out-alt"></i> Nghỉ việc</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="<?= BASE_URL ?>/employee/detail/<?= $emp->id ?>" class="btn btn-ghost btn-sm" title="Xem chi tiết 360">
                            <i class="fas fa-id-card"></i>
                        </a>
                        <a href="<?= BASE_URL ?>/employee/print2c/<?= $emp->id ?>" class="btn btn-ghost btn-sm" target="_blank" title="In lý lịch 2C">
                            <i class="fas fa-print"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
/* Utilities cho bảng */
.filter-row { display: flex; gap: 12px; flex-wrap: wrap; }
.filter-row .form-group { flex: 1; min-width: 200px; }
.avatar-sm { width: 40px; height: 40px; flex-shrink: 0; }
.avatar-img { width: 100%; height: 100%; object-fit: cover; border-radius: 8px; border: 1px solid var(--border); }
.avatar-initial { width: 100%; height: 100%; background: linear-gradient(135deg, var(--primary), var(--accent)); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: bold; border-radius: 8px; font-size: 16px; }
.btn-sm { padding: 6px 12px; font-size: 12px; }
.d-flex { display: flex; }
.align-items-center { align-items: center; }
.gap-3 { gap: 1rem; }
.fw-bold { font-weight: 600; }
.text-primary { color: var(--primary-light); }
.text-muted { color: var(--text-muted); }
.small { font-size: 12px; }
.mb-0 { margin-bottom: 0 !important; }

/* DataTables Overrides cho Dark Theme */
.dataTables_wrapper .dataTables_length, .dataTables_wrapper .dataTables_filter, .dataTables_wrapper .dataTables_info, .dataTables_wrapper .dataTables_processing, .dataTables_wrapper .dataTables_paginate {
    color: var(--text-secondary);
    font-size: 13px;
    margin-bottom: 12px;
}
.dataTables_wrapper .dataTables_filter input, .dataTables_wrapper .dataTables_length select {
    background: var(--bg-input);
    border: 1px solid var(--border);
    color: var(--text-primary);
    border-radius: var(--radius-sm);
    padding: 6px 10px;
    outline: none;
}
.dataTables_wrapper .dataTables_paginate .paginate_button {
    color: var(--text-secondary) !important;
    border: 1px solid transparent;
}
.dataTables_wrapper .dataTables_paginate .paginate_button.current, .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: #fff !important;
    border: 1px solid var(--primary);
    border-radius: var(--radius-sm);
}
</style>

<!-- jQuery & DataTables JS -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
    $('#employeesTable').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/vi.json"
        },
        "scrollX": true,
        "pageLength": 25,
        "order": [], // Không tự sắp xếp cột đầu tiên
        "columnDefs": [
            { "orderable": false, "targets": 6 } // Disable sort cho cột Hành động
        ]
    });
});
</script>
