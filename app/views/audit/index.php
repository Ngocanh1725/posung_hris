<?php
/**
 * View: audit/index.php – Nhật ký Hoạt động Hệ thống (System Audit Log)
 */

$actionBadges = [
    'create'  => ['Thêm mới',  'badge bg-success text-white',       'fas fa-plus-circle'],
    'update'  => ['Cập nhật',  'badge bg-primary text-white',       'fas fa-edit'],
    'delete'  => ['Xóa bỏ',    'badge bg-danger text-white',        'fas fa-trash-alt'],
    'login'   => ['Đăng nhập', 'badge bg-warning text-dark',        'fas fa-sign-in-alt'],
    'logout'  => ['Đăng xuất', 'badge bg-secondary text-white',     'fas fa-sign-out-alt'],
    'backup'  => ['Sao lưu',   'badge bg-info text-dark',           'fas fa-database'],
    'restore' => ['Phục hồi',  'badge bg-danger text-white',        'fas fa-undo'],
    'view'    => ['Truy cập',  'badge bg-light text-dark',          'fas fa-eye'],
    'export'  => ['Xuất file', 'badge bg-success text-white',       'fas fa-file-export'],
];
?>

<!-- BREADCRUMB -->
<div class="breadcrumb-nav mb-3" style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: var(--text-muted);">
    <a href="<?= BASE_URL ?>/dashboard" style="color: var(--primary); text-decoration: none;">
        <i class="fas fa-home"></i> Trang chủ
    </a>
    <i class="fas fa-chevron-right" style="font-size: 10px;"></i>
    <span>Quản trị Hệ thống</span>
    <i class="fas fa-chevron-right" style="font-size: 10px;"></i>
    <span>Nhật ký Hoạt động (Audit Log)</span>
</div>

<!-- KPI CARDS ROW -->
<div class="row g-3 mb-4">
    <div class="col-md-3 col-sm-6">
        <div class="card p-3" style="border: 1px solid var(--border); border-radius: 12px;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted" style="font-size: 12px; font-weight: 600; text-transform: uppercase;">Tổng nhật ký ghi nhận</div>
                    <div style="font-size: 24px; font-weight: 800; color: var(--text); margin-top: 4px;">
                        <?= number_format($stats['total_logs']) ?>
                    </div>
                </div>
                <div style="width: 44px; height: 44px; border-radius: 10px; background: rgba(79, 70, 229, 0.1); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 20px;">
                    <i class="fas fa-list-check"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6">
        <div class="card p-3" style="border: 1px solid rgba(16, 185, 129, 0.2); background: rgba(16, 185, 129, 0.02); border-radius: 12px;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted" style="font-size: 12px; font-weight: 600; text-transform: uppercase;">Thao tác trong ngày</div>
                    <div style="font-size: 24px; font-weight: 800; color: #10b981; margin-top: 4px;">
                        <?= number_format($stats['today_logs']) ?>
                    </div>
                </div>
                <div style="width: 44px; height: 44px; border-radius: 10px; background: rgba(16, 185, 129, 0.1); color: #10b981; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                    <i class="fas fa-calendar-day"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6">
        <div class="card p-3" style="border: 1px solid rgba(59, 130, 246, 0.2); background: rgba(59, 130, 246, 0.02); border-radius: 12px;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted" style="font-size: 12px; font-weight: 600; text-transform: uppercase;">Thao tác Dữ liệu (CUD)</div>
                    <div style="font-size: 24px; font-weight: 800; color: #3b82f6; margin-top: 4px;">
                        <?= number_format($stats['cud_actions']) ?>
                    </div>
                </div>
                <div style="width: 44px; height: 44px; border-radius: 10px; background: rgba(59, 130, 246, 0.1); color: #3b82f6; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                    <i class="fas fa-database"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6">
        <div class="card p-3" style="border: 1px solid rgba(245, 158, 11, 0.2); background: rgba(245, 158, 11, 0.02); border-radius: 12px;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted" style="font-size: 12px; font-weight: 600; text-transform: uppercase;">Lượt Đăng nhập</div>
                    <div style="font-size: 24px; font-weight: 800; color: #d97706; margin-top: 4px;">
                        <?= number_format($stats['login_actions']) ?>
                    </div>
                </div>
                <div style="width: 44px; height: 44px; border-radius: 10px; background: rgba(245, 158, 11, 0.1); color: #d97706; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                    <i class="fas fa-sign-in-alt"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- TOP CONTROLS & EXPORT -->
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div>
        <h3 style="font-size: 18px; font-weight: 700; margin: 0; color: var(--text);">
            <i class="fas fa-clock-rotate-left text-primary"></i> Nhật Ký Hoạt Động & Kiểm Toán Hệ Thống
        </h3>
        <span class="text-muted" style="font-size: 13px;">
            Ghi nhận chi tiết mọi tác động thay đổi dữ liệu, IP và dấu vết bảo mật
        </span>
    </div>

    <div class="d-flex gap-2">
        <a href="<?= BASE_URL ?>/backup" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-database"></i> Quản lý Sao lưu CSDL
        </a>
        <a href="<?= BASE_URL ?>/audit/export?<?= http_build_query($filters) ?>" class="btn btn-success btn-sm">
            <i class="fas fa-file-excel"></i> Xuất File CSV
        </a>
    </div>
</div>

<!-- FILTER CARD -->
<div class="card mb-3" style="border: 1px solid var(--border);">
    <div class="card-body p-3">
        <form method="GET" action="<?= BASE_URL ?>/audit" class="row g-2 align-items-center">
            <!-- User -->
            <div class="col-md-2">
                <select name="user_id" class="form-select form-select-sm">
                    <option value="">-- Người dùng --</option>
                    <?php foreach ($filterOptions['users'] as $u): ?>
                        <option value="<?= $u['user_id'] ?>" <?= ($filters['user_id'] == $u['user_id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($u['user_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Module -->
            <div class="col-md-2">
                <select name="module" class="form-select form-select-sm">
                    <option value="">-- Phân hệ (Module) --</option>
                    <?php foreach ($filterOptions['modules'] as $m): ?>
                        <option value="<?= $m ?>" <?= ($filters['module'] === $m) ? 'selected' : '' ?>>
                            <?= strtoupper($m) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Action -->
            <div class="col-md-2">
                <select name="action" class="form-select form-select-sm">
                    <option value="">-- Hành động --</option>
                    <?php foreach ($filterOptions['actions'] as $act): ?>
                        <option value="<?= $act ?>" <?= ($filters['action'] === $act) ? 'selected' : '' ?>>
                            <?= strtoupper($act) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Date From & To -->
            <div class="col-md-2">
                <input type="date" name="date_from" class="form-control form-control-sm" placeholder="Từ ngày" value="<?= htmlspecialchars($filters['date_from']) ?>">
            </div>
            <div class="col-md-2">
                <input type="date" name="date_to" class="form-control form-control-sm" placeholder="Đến ngày" value="<?= htmlspecialchars($filters['date_to']) ?>">
            </div>

            <!-- Search -->
            <div class="col-md-2 d-flex gap-1">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Tìm kiếm từ khóa..." value="<?= htmlspecialchars($filters['search']) ?>">
                <button type="submit" class="btn btn-sm btn-primary" title="Lọc"><i class="fas fa-search"></i></button>
                <?php if (!empty(array_filter($filters))): ?>
                    <a href="<?= BASE_URL ?>/audit" class="btn btn-sm btn-ghost" style="border: 1px solid var(--border);" title="Xóa bộ lọc"><i class="fas fa-redo"></i></a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- AUDIT TABLE CARD -->
<div class="card" style="border: 1px solid var(--border); box-shadow: 0 2px 10px rgba(0,0,0,0.02);">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
            <thead style="background: var(--bg-hover); color: var(--text-muted); font-size: 11.5px; text-transform: uppercase;">
                <tr>
                    <th style="width: 60px; text-align: center;">ID</th>
                    <th style="width: 150px;">Thời gian</th>
                    <th style="width: 170px;">Người thực hiện</th>
                    <th style="width: 120px;">Hành động</th>
                    <th style="width: 110px;">Phân hệ</th>
                    <th style="width: 90px; text-align: center;">Mã record</th>
                    <th>Nội dung thao tác</th>
                    <th style="width: 120px;">Địa chỉ IP</th>
                    <th style="text-align: right; width: 90px;">Chi tiết</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <i class="fas fa-clipboard-check fa-3x mb-3 text-secondary" style="opacity: 0.5;"></i>
                            <div>Không có dữ liệu nhật ký nào phù hợp với bộ lọc tìm kiếm.</div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($logs as $l): ?>
                        <tr>
                            <td style="text-align: center; color: var(--text-muted);">#<?= $l['id'] ?></td>
                            <td style="font-size: 12px; color: var(--text-muted);">
                                <?= date('d/m/Y H:i:s', strtotime($l['created_at'])) ?>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div style="width: 28px; height: 28px; border-radius: 50%; background: #e0e7ff; color: #4338ca; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 12px; flex-shrink: 0;">
                                        <?= mb_strtoupper(mb_substr($l['user_name'] ?? 'U', 0, 1, 'UTF-8'), 'UTF-8') ?>
                                    </div>
                                    <strong style="color: var(--text); font-size: 12.5px;">
                                        <?= htmlspecialchars($l['user_name'] ?? 'System') ?>
                                    </strong>
                                </div>
                            </td>
                            <td>
                                <?php $b = $actionBadges[$l['action']] ?? [$l['action'], 'badge bg-secondary', '']; ?>
                                <span class="<?= $b[1] ?>" style="font-size: 11px; padding: 4px 8px; border-radius: 12px;">
                                    <i class="<?= $b[2] ?>"></i> <?= $b[0] ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark" style="border: 1px solid var(--border); font-size: 11px;">
                                    <?= strtoupper($l['module']) ?>
                                </span>
                            </td>
                            <td style="text-align: center; font-weight: 600;">
                                <?= $l['record_id'] ? '#' . htmlspecialchars($l['record_id']) : '---' ?>
                            </td>
                            <td>
                                <div style="color: var(--text); font-size: 12.5px;"><?= htmlspecialchars($l['description']) ?></div>
                            </td>
                            <td>
                                <span class="text-muted" style="font-size: 11.5px; font-family: monospace;">
                                    <?= htmlspecialchars($l['ip_address'] ?? '127.0.0.1') ?>
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <?php if (!empty($l['old_values']) || !empty($l['new_values'])): ?>
                                    <button type="button" class="btn btn-sm btn-ghost" style="border: 1px solid var(--border);"
                                            onclick="viewAuditDiff(<?= $l['id'] ?>)" title="Xem biến động dữ liệu">
                                        <i class="fas fa-code-compare text-primary"></i> Diff
                                    </button>
                                <?php else: ?>
                                    <a href="<?= BASE_URL ?>/audit/detail/<?= $l['id'] ?>" class="btn btn-sm btn-ghost" style="border: 1px solid var(--border);" title="Xem chi tiết">
                                        <i class="fas fa-eye text-muted"></i>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- PHÂN TRANG -->
    <?php if ($totalPages > 1): ?>
        <div class="card-footer d-flex justify-content-between align-items-center" style="background: transparent; border-top: 1px solid var(--border); padding: 12px 20px;">
            <div style="font-size: 12.5px; color: var(--text-muted);">
                Hiển thị trang <strong><?= $page ?></strong> / <strong><?= $totalPages ?></strong> (Tổng số: <?= number_format($total) ?> bản ghi)
            </div>
            <ul class="pagination pagination-sm mb-0">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="<?= BASE_URL ?>/audit?<?= http_build_query(array_merge($filters, ['page' => $page - 1])) ?>">« Trước</a>
                    </li>
                <?php endif; ?>

                <?php for ($p = max(1, $page - 2); $p <= min($totalPages, $page + 2); $p++): ?>
                    <li class="page-item <?= ($p == $page) ? 'active' : '' ?>">
                        <a class="page-link" href="<?= BASE_URL ?>/audit?<?= http_build_query(array_merge($filters, ['page' => $p])) ?>"><?= $p ?></a>
                    </li>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link" href="<?= BASE_URL ?>/audit?<?= http_build_query(array_merge($filters, ['page' => $page + 1])) ?>">Sau »</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    <?php endif; ?>
</div>

<!-- MODAL DIFF VIEWER -->
<div class="modal fade" id="diffModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius: 14px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            <div class="modal-header" style="background: linear-gradient(135deg, rgba(79,70,229,0.08), rgba(99,102,241,0.02)); border-bottom: 1px solid var(--border);">
                <h5 class="modal-title" style="font-weight: 700; color: var(--text);">
                    <i class="fas fa-code-compare text-primary"></i> So Sánh Chi Tiết Thay Đổi (Data Diff)
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="diffModalBody" style="padding: 20px;">
                <div class="text-center py-4 text-muted"><i class="fas fa-spinner fa-spin fa-2x"></i> Đang tải dữ liệu...</div>
            </div>
            <div class="modal-footer" style="background: var(--bg-hover); border-top: 1px solid var(--border);">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<script>
function viewAuditDiff(id) {
    let modal = new bootstrap.Modal(document.getElementById('diffModal'));
    let body = document.getElementById('diffModalBody');
    body.innerHTML = '<div class="text-center py-4 text-muted"><i class="fas fa-spinner fa-spin fa-2x"></i> Đang tải dữ liệu...</div>';
    modal.show();

    fetch('<?= BASE_URL ?>/audit/detail/' + id + '?format=json', {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.json())
    .then(res => {
        if (!res.success || !res.log) {
            body.innerHTML = '<div class="alert alert-danger">Không tải được chi tiết nhật ký.</div>';
            return;
        }

        let log = res.log;
        let oldVal = log.old_decoded || {};
        let newVal = log.new_decoded || {};

        let allKeys = Array.from(new Set([...Object.keys(oldVal), ...Object.keys(newVal)]));

        let html = `
            <div class="mb-3 p-2 bg-light rounded" style="font-size: 13px;">
                <div><strong>Người thực hiện:</strong> ${log.user_name || 'System'} | <strong>Thời gian:</strong> ${log.created_at}</div>
                <div><strong>Hành động:</strong> <span class="badge bg-primary">${log.action}</span> trên <strong>${log.module} #${log.record_id || ''}</strong></div>
                <div style="font-style: italic; margin-top: 3px;">"${log.description}"</div>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered align-middle" style="font-size: 12.5px;">
                    <thead style="background: var(--bg-hover); text-transform: uppercase; font-size: 11px;">
                        <tr>
                            <th style="width: 160px;">Thuộc tính (Field)</th>
                            <th style="width: 50%;">Giá trị cũ (Trước)</th>
                            <th style="width: 50%;">Giá trị mới (Sau)</th>
                        </tr>
                    </thead>
                    <tbody>
        `;

        if (allKeys.length === 0) {
            html += `<tr><td colspan="3" class="text-center text-muted">Không có thông tin trường thay đổi.</td></tr>`;
        } else {
            allKeys.forEach(k => {
                let o = oldVal[k];
                let n = newVal[k];
                let oStr = o !== undefined ? (typeof o === 'object' ? JSON.stringify(o) : o) : '<span class="text-muted">NULL</span>';
                let nStr = n !== undefined ? (typeof n === 'object' ? JSON.stringify(n) : n) : '<span class="text-muted">NULL</span>';

                let isChanged = oStr !== nStr;
                let rowBg = isChanged ? 'background: rgba(245, 158, 11, 0.05);' : '';

                html += `
                    <tr style="${rowBg}">
                        <td><strong>${k}</strong></td>
                        <td style="${isChanged && o !== undefined ? 'background: #fee2e2; color: #b91c1c; font-weight: 500;' : ''}">${oStr}</td>
                        <td style="${isChanged && n !== undefined ? 'background: #dcfce7; color: #15803d; font-weight: 600;' : ''}">${nStr}</td>
                    </tr>
                `;
            });
        }

        html += `</tbody></table></div>`;
        body.innerHTML = html;
    })
    .catch(() => {
        body.innerHTML = '<div class="alert alert-danger">Lỗi kết nối máy chủ.</div>';
    });
}
</script>
