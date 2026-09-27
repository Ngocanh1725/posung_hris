<?php
/**
 * ============================================================
 *  View: employee/index.php
 *  Hiển thị danh sách nhân sự với DataTables + scrollX
 * ============================================================
 */
?>

<!-- DataTables CSS (không dùng Responsive vì nó xung đột scrollX) -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css"/>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/fixedcolumns/4.3.0/css/fixedColumns.bootstrap5.min.css"/>

<div class="panel">
    <div class="panel-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h3><i class="fas fa-users"></i> Quản lý Hồ sơ Nhân sự</h3>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-secondary" onclick="exportData()">
                <i class="fas fa-file-excel"></i> Xuất Danh Sách Cán Bộ
            </button>
            <script>
            function exportData() {
                var form = document.querySelector('.filter-form');
                var oldAction = form.action;
                form.action = '<?= BASE_URL ?>/employee/export';
                form.submit();
                form.action = oldAction;
            }
            </script>
            <?php if (Session::isManager() || Session::userRole() === 'Project_Manager'): ?>
            <a href="<?= BASE_URL ?>/employee/create" class="btn btn-primary">
                <i class="fas fa-plus"></i> Thêm mới
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Bộ lọc -->
    <div class="panel-body border-bottom">
        <form method="GET" action="<?= BASE_URL ?>/employee/index" class="filter-form">
            <div class="filter-row">
                <div class="form-group mb-0">
                    <input type="text" id="realtimeSearch" name="search" class="form-control" placeholder="Tìm tên, mã, SĐT..." value="<?= h($filters['search']) ?>">
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
                    <select name="dept" class="form-control">
                        <option value="0">-- Chọn Phòng ban --</option>
                        <?php foreach ($departments as $d): ?>
                            <option value="<?= $d['id'] ?>" <?= $filters['deptId'] == $d['id'] ? 'selected' : '' ?>><?= h($d['dept_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group mb-0">
                    <select name="project" class="form-control">
                        <option value="0">-- Chọn Dự án --</option>
                        <?php foreach ($projects as $prj): ?>
                            <option value="<?= $prj['id'] ?>" <?= $filters['projectId'] == $prj['id'] ? 'selected' : '' ?>><?= h($prj['project_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group mb-0">
                    <select name="cert_status" class="form-control">
                        <option value="">-- Tình trạng Chứng chỉ --</option>
                        <option value="expiring" <?= $filters['cert_status'] === 'expiring' ? 'selected' : '' ?>>Sắp hết hạn (<60 ngày)</option>
                        <option value="expired" <?= $filters['cert_status'] === 'expired' ? 'selected' : '' ?>>Đã hết hạn</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Lọc</button>
            </div>
        </form>
    </div>

    <!-- Skeleton Loading -->
    <div id="tableSkeleton" class="emp-table-scroll p-3">
        <?php for($i=0; $i<5; $i++): ?>
        <div class="d-flex align-items-center mb-4">
            <div class="skeleton skeleton-text" style="width: 80px; margin-right: 15px; margin-bottom: 0;"></div>
            <div class="skeleton skeleton-avatar" style="margin-right: 15px;"></div>
            <div class="skeleton skeleton-text" style="width: 150px; margin-right: 15px; margin-bottom: 0;"></div>
            <div class="skeleton skeleton-text" style="width: 150px; margin-right: 15px; margin-bottom: 0;"></div>
            <div class="skeleton skeleton-text" style="width: 200px; margin-right: 15px; margin-bottom: 0;"></div>
            <div class="skeleton skeleton-text" style="width: 100px; margin-right: 15px; margin-bottom: 0;"></div>
            <div class="skeleton skeleton-btn"></div>
        </div>
        <?php endfor; ?>
    </div>

    <!-- Bảng dữ liệu – bọc trong div scroll ngang -->
    <div class="emp-table-scroll" id="tableContainer" style="display: none;">
        <table id="employeesTable" class="table table-striped nowrap" style="width: 100%;">
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
                    <td><strong><?= h($emp->emp_code) ?></strong></td>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar-sm">
                                <?php if (!empty($emp->avatar_path)): ?>
                                    <?php $avatarUrl = (strpos($emp->avatar_path, 'http') === 0) ? $emp->avatar_path : BASE_URL . '/' . $emp->avatar_path; ?>
                                    <img src="<?= h($avatarUrl) ?>" alt="Avatar" class="avatar-img">
                                <?php else: ?>
                                    <div class="avatar-initial"><?= mb_substr($emp->full_name, 0, 1) ?></div>
                                <?php endif; ?>
                            </div>
                            <div>
                                <div class="fw-bold"><?= h($emp->full_name) ?></div>
                                <div class="text-muted small"><?= h($emp->pos_title ?? 'Chưa rõ') ?></div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="small"><i class="fas fa-phone text-muted"></i> <?= h($emp->phone ?? '') ?></div>
                        <div class="small"><i class="fas fa-envelope text-muted"></i> <?= h($emp->email ?? '') ?></div>
                    </td>
                    <td>
                        <?php if (!empty($emp->current_project_id)): ?>
                            <a href="<?= BASE_URL ?>/project/detail/<?= $emp->current_project_id ?>" class="fw-bold text-primary text-decoration-none" title="Xem chi tiết dự án"><?= h($emp->project_name ?? 'N/A') ?></a>
                        <?php else: ?>
                            <div class="fw-bold text-primary"><?= h($emp->project_name ?? 'N/A') ?></div>
                        <?php endif; ?>
                        
                        <?php if (!empty($emp->department_id)): ?>
                            <a href="<?= BASE_URL ?>/organization/detail/<?= $emp->department_id ?>" class="small text-muted text-decoration-none" title="Xem chi tiết phòng ban"><?= h($emp->dept_name ?? 'Chưa phân bổ') ?></a>
                        <?php else: ?>
                            <div class="small text-muted"><?= h($emp->dept_name ?? 'Chưa phân bổ') ?></div>
                        <?php endif; ?>
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
/* ── Scroll ngang cho bảng nhân sự ── */
.emp-table-scroll {
    width: 100%;
    overflow-x: scroll !important;  /* scroll thay vì auto = luôn hiện thanh */
    overflow-y: visible;
    -webkit-overflow-scrolling: touch;
}

/* Ép scrollbar LUÔN hiện dạng classic (không overlay) */
.emp-table-scroll,
.dataTables_wrapper .dataTables_scrollBody {
    scrollbar-width: auto;              /* Firefox: hiện scrollbar chuẩn */
    scrollbar-color: #94a3b8 #e2e8f0;   /* Firefox: thumb + track color */
}
.emp-table-scroll::-webkit-scrollbar,
.dataTables_wrapper .dataTables_scrollBody::-webkit-scrollbar {
    height: 14px !important;            /* Chiều cao thanh scroll ngang */
    display: block !important;
}
.emp-table-scroll::-webkit-scrollbar-track,
.dataTables_wrapper .dataTables_scrollBody::-webkit-scrollbar-track {
    background: #e2e8f0;
    border-radius: 0;
}
.emp-table-scroll::-webkit-scrollbar-thumb,
.dataTables_wrapper .dataTables_scrollBody::-webkit-scrollbar-thumb {
    background: #94a3b8;
    border-radius: 4px;
    border: 2px solid #e2e8f0;
}
.emp-table-scroll::-webkit-scrollbar-thumb:hover,
.dataTables_wrapper .dataTables_scrollBody::-webkit-scrollbar-thumb:hover {
    background: #64748b;
}
.emp-table-scroll::-webkit-scrollbar-button,
.dataTables_wrapper .dataTables_scrollBody::-webkit-scrollbar-button {
    display: block;
    width: 14px;
    height: 14px;
    background: #cbd5e1;
}

/* DataTables wrapper */
.emp-table-scroll .dataTables_wrapper {
    min-width: 900px;
    width: max-content;
}

/* DataTables scrollBody luôn hiện thanh scroll ngang */
.dataTables_wrapper .dataTables_scrollBody {
    overflow-x: scroll !important;
    overflow-y: hidden !important;
}
.dataTables_wrapper .dataTables_scroll {
    overflow-x: auto !important;
}
.dataTables_wrapper .dataTables_scrollHead {
    overflow: hidden !important;
}

/* Utilities */
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
.border-bottom { border-bottom: 1px solid var(--border); }

/* DataTables Overrides */
.dataTables_wrapper .dataTables_length,
.dataTables_wrapper .dataTables_filter,
.dataTables_wrapper .dataTables_info,
.dataTables_wrapper .dataTables_processing,
.dataTables_wrapper .dataTables_paginate {
    color: var(--text-secondary);
    font-size: 13px;
    padding: 12px 16px;
}
.dataTables_wrapper .dataTables_filter input,
.dataTables_wrapper .dataTables_length select {
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
.dataTables_wrapper .dataTables_paginate .paginate_button.current,
.dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: #fff !important;
    border: 1px solid var(--primary);
    border-radius: var(--radius-sm);
}
</style>

<!-- jQuery & DataTables JS (KHÔNG có Responsive extension) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/fixedcolumns/4.3.0/js/dataTables.fixedColumns.min.js"></script>

<script>
$(document).ready(function() {
    $('#tableSkeleton').hide();
    $('#tableContainer').fadeIn(300);

    var table = $('#employeesTable').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/vi.json"
        },
        "scrollX": true,   // DataTables tạo thanh scroll ngang tự động
        "autoWidth": false,
        "pageLength": 25,
        "order": [],
        "fixedColumns": {
            left: 0,
            right: 1
        },
        "columnDefs": [
            { "width": "80px",  "targets": 0 },
            { "width": "220px", "targets": 1 },
            { "width": "160px", "targets": 2 },
            { "width": "250px", "targets": 3 },
            { "width": "100px", "targets": 4 },
            { "width": "100px", "targets": 5 },
            { "width": "90px",  "orderable": false, "targets": 6 }
        ]
    });

    // Tính năng tìm kiếm real-time (tương tác trực tiếp client-side trên trang hiện tại)
    $('#realtimeSearch').on('keyup', function() {
        table.search(this.value).draw();
    });
});
</script>
