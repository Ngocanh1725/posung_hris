<?php
/**
 * View: backup/index.php – Quản lý Sao lưu & Phục hồi Cơ sở dữ liệu
 */

$isAutoEnabled = ($settings['auto_backup_enabled'] ?? '1') === '1';
?>

<!-- BREADCRUMB -->
<div class="breadcrumb-nav mb-3" style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: var(--text-muted);">
    <a href="<?= BASE_URL ?>/dashboard" style="color: var(--primary); text-decoration: none;">
        <i class="fas fa-home"></i> Trang chủ
    </a>
    <i class="fas fa-chevron-right" style="font-size: 10px;"></i>
    <span>Quản trị Hệ thống</span>
    <i class="fas fa-chevron-right" style="font-size: 10px;"></i>
    <span>Sao lưu & Phục hồi CSDL</span>
</div>

<!-- KPI STATS CARDS -->
<div class="row g-3 mb-4">
    <div class="col-md-3 col-sm-6">
        <div class="card p-3" style="border: 1px solid var(--border); border-radius: 12px;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted" style="font-size: 12px; font-weight: 600; text-transform: uppercase;">Số bản sao lưu hiện có</div>
                    <div style="font-size: 24px; font-weight: 800; color: var(--text); margin-top: 4px;">
                        <?= number_format($stats['total_count']) ?> <span style="font-size: 13px; font-weight: normal; color: var(--text-muted);">bản</span>
                    </div>
                </div>
                <div style="width: 44px; height: 44px; border-radius: 10px; background: rgba(79, 70, 229, 0.1); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 20px;">
                    <i class="fas fa-database"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6">
        <div class="card p-3" style="border: 1px solid rgba(16, 185, 129, 0.2); background: rgba(16, 185, 129, 0.02); border-radius: 12px;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted" style="font-size: 12px; font-weight: 600; text-transform: uppercase;">Dung lượng lưu trữ</div>
                    <div style="font-size: 24px; font-weight: 800; color: #10b981; margin-top: 4px;">
                        <?= $stats['total_size_formatted'] ?>
                    </div>
                </div>
                <div style="width: 44px; height: 44px; border-radius: 10px; background: rgba(16, 185, 129, 0.1); color: #10b981; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                    <i class="fas fa-hard-drive"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6">
        <div class="card p-3" style="border: 1px solid rgba(59, 130, 246, 0.2); background: rgba(59, 130, 246, 0.02); border-radius: 12px;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted" style="font-size: 12px; font-weight: 600; text-transform: uppercase;">Bản sao lưu gần nhất</div>
                    <div style="font-size: 15px; font-weight: 700; color: #3b82f6; margin-top: 8px;">
                        <?= $stats['latest'] ? date('d/m/Y H:i', strtotime($stats['latest'])) : 'Chưa có' ?>
                    </div>
                </div>
                <div style="width: 44px; height: 44px; border-radius: 10px; background: rgba(59, 130, 246, 0.1); color: #3b82f6; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6">
        <div class="card p-3" style="border: 1px solid rgba(245, 158, 11, 0.2); background: rgba(245, 158, 11, 0.02); border-radius: 12px;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted" style="font-size: 12px; font-weight: 600; text-transform: uppercase;">Tự động sao lưu</div>
                    <div style="font-size: 15px; font-weight: 700; margin-top: 8px;">
                        <?php if ($isAutoEnabled): ?>
                            <span class="text-success"><i class="fas fa-check-circle"></i> Đang bật (<?= strtoupper($settings['auto_backup_frequency'] ?? 'DAILY') ?>)</span>
                        <?php else: ?>
                            <span class="text-secondary"><i class="fas fa-ban"></i> Đang tắt</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div style="width: 44px; height: 44px; border-radius: 10px; background: rgba(245, 158, 11, 0.1); color: #d97706; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                    <i class="fas fa-robot"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ACTIONS TOOLBAR -->
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div>
        <h3 style="font-size: 18px; font-weight: 700; margin: 0; color: var(--text);">
            <i class="fas fa-server text-primary"></i> Quản Lý Bản Sao Lưu CSDL (Database Backups)
        </h3>
        <p style="margin: 3px 0 0; font-size: 13px; color: var(--text-muted);">
            Thực hiện sao lưu an toàn toàn bộ dữ liệu hệ thống, bảng nhân sự, tiền lương, hợp đồng và bảo hiểm
        </p>
    </div>

    <div class="d-flex gap-2">
        <button type="button" class="btn btn-primary" onclick="startBackupNow()" id="btnStartBackup" style="box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);">
            <i class="fas fa-download"></i> Sao Lưu Ngay (Backup Now)
        </button>
        <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#autoBackupModal">
            <i class="fas fa-cog"></i> Cài Đặt Tự Động
        </button>
        <a href="<?= BASE_URL ?>/audit?module=system" class="btn btn-ghost" style="border: 1px solid var(--border);">
            <i class="fas fa-clock-rotate-left"></i> Nhật Ký Backup
        </a>
    </div>
</div>

<!-- PROGRESS BAR WHEN BACKING UP -->
<div id="backupProgressBox" class="card mb-3" style="display: none; border: 1px solid var(--primary); background: rgba(79,70,229,0.04);">
    <div class="card-body p-3">
        <div class="d-flex align-items-center gap-3">
            <div class="spinner-border text-primary" role="status" style="width: 24px; height: 24px;"></div>
            <div class="flex-grow-1">
                <div style="font-weight: 600; font-size: 13.5px; color: var(--primary);">
                    Đang kết xuất dữ liệu và tạo file sao lưu SQL... Vui lòng không đóng trình duyệt.
                </div>
                <div class="progress mt-2" style="height: 6px;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" style="width: 100%;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- BACKUP LIST TABLE -->
<div class="card" style="border: 1px solid var(--border); box-shadow: 0 2px 10px rgba(0,0,0,0.02);">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
            <thead style="background: var(--bg-hover); color: var(--text-muted); font-size: 11.5px; text-transform: uppercase;">
                <tr>
                    <th style="width: 50px; text-align: center;">STT</th>
                    <th style="width: 280px;">Tên file sao lưu</th>
                    <th style="width: 120px;">Dung lượng</th>
                    <th style="text-align: center; width: 110px;">Loại sao lưu</th>
                    <th>Người thực hiện & Ghi chú</th>
                    <th style="width: 160px;">Thời gian tạo</th>
                    <th style="text-align: center; width: 120px;">Trạng thái</th>
                    <th style="text-align: right; width: 180px;">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($backups)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="fas fa-database fa-3x mb-3 text-secondary" style="opacity: 0.5;"></i>
                            <div>Chưa có bản sao lưu nào. Hãy bấm <strong>"Sao Lưu Ngay"</strong> để tạo bản backup đầu tiên!</div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php $idx = 1; foreach ($backups as $b): ?>
                        <tr>
                            <td style="text-align: center; color: var(--text-muted);"><?= $idx++ ?></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fas fa-file-code fa-lg text-primary"></i>
                                    <div>
                                        <strong style="color: var(--text); font-family: monospace; font-size: 13px;">
                                            <?= htmlspecialchars($b['filename']) ?>
                                        </strong>
                                        <?php if (!empty($b['tables_count'])): ?>
                                            <div style="font-size: 11px; color: var(--text-muted);">
                                                <?= (int)$b['tables_count'] ?> bảng • <?= number_format((int)$b['records_count']) ?> dòng
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark" style="border: 1px solid var(--border); font-size: 12px;">
                                    <?php
                                        $sz = (int)$b['file_size'];
                                        echo ($sz >= 1048576) ? round($sz / 1048576, 2) . ' MB' : round($sz / 1024, 1) . ' KB';
                                    ?>
                                </span>
                            </td>
                            <td style="text-align: center;">
                                <?php if (($b['backup_type'] ?? 'Manual') === 'Auto'): ?>
                                    <span class="badge bg-info text-dark" style="font-size: 11px;"><i class="fas fa-robot"></i> Tự động</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary" style="font-size: 11px;"><i class="fas fa-user"></i> Thủ công</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong style="color: var(--text);"><?= htmlspecialchars($b['creator_name'] ?? 'System') ?></strong>
                                <?php if (!empty($b['notes'])): ?>
                                    <div style="font-size: 11.5px; color: var(--text-muted); font-style: italic;">
                                        <?= htmlspecialchars($b['notes']) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= date('d/m/Y H:i:s', strtotime($b['created_at'])) ?>
                            </td>
                            <td style="text-align: center;">
                                <?php if (($b['status'] ?? 'Completed') === 'Restored'): ?>
                                    <span class="badge bg-warning text-dark" style="font-size: 10px;"><i class="fas fa-check-circle"></i> Đã phục hồi</span>
                                <?php else: ?>
                                    <span class="badge bg-success" style="font-size: 10px;"><i class="fas fa-check"></i> Sẵn sàng</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: right;">
                                <div class="d-flex justify-content-end gap-1">
                                    <a href="<?= BASE_URL ?>/backup/download?file=<?= urlencode($b['filename']) ?>" 
                                       class="btn btn-sm btn-outline-success" title="Tải xuống file SQL">
                                        <i class="fas fa-download"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                            onclick="openRestoreConfirm('<?= htmlspecialchars($b['filename']) ?>')" 
                                            title="Phục hồi CSDL từ bản này">
                                        <i class="fas fa-undo"></i> Phục hồi
                                    </button>
                                    <a href="<?= BASE_URL ?>/backup/delete?file=<?= urlencode($b['filename']) ?>" 
                                       class="btn btn-sm btn-ghost" 
                                       onclick="return confirm('Bạn có chắc chắn muốn xóa bản sao lưu này?')" 
                                       title="Xóa bản backup" style="border: 1px solid var(--border);">
                                        <i class="fas fa-trash-alt text-danger"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL CÀI ĐẶT SAO LƯU TỰ ĐỘNG -->
<div class="modal fade" id="autoBackupModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius: 14px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            <div class="modal-header" style="background: linear-gradient(135deg, rgba(79,70,229,0.08), rgba(99,102,241,0.02)); border-bottom: 1px solid var(--border);">
                <h5 class="modal-title" style="font-weight: 700; color: var(--text);">
                    <i class="fas fa-robot text-primary"></i> Cài Đặt Tự Động Sao Lưu CSDL
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= BASE_URL ?>/backup/autoBackupSettings" method="POST">
                <input type="hidden" name="_csrf_token" value="<?= Session::generateCsrfToken() ?>">

                <div class="modal-body" style="padding: 20px;">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="auto_backup_enabled" value="1" id="autoEnableSwitch" <?= $isAutoEnabled ? 'checked' : '' ?>>
                        <label class="form-check-label" for="autoEnableSwitch" style="font-weight: 600;">
                            Kích hoạt tự động sao lưu định kỳ
                        </label>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" style="font-weight: 600; font-size: 13px;">Tần suất sao lưu</label>
                            <select name="auto_backup_frequency" class="form-select">
                                <option value="daily" <?= (($settings['auto_backup_frequency'] ?? 'daily') === 'daily') ? 'selected' : '' ?>>Hàng ngày (Daily)</option>
                                <option value="weekly" <?= (($settings['auto_backup_frequency'] ?? '') === 'weekly') ? 'selected' : '' ?>>Hàng tuần (Weekly)</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" style="font-weight: 600; font-size: 13px;">Thời điểm chạy</label>
                            <input type="time" name="auto_backup_time" class="form-control" value="<?= htmlspecialchars($settings['auto_backup_time'] ?? '02:00') ?>">
                        </div>

                        <div class="col-12">
                            <label class="form-label" style="font-weight: 600; font-size: 13px;">Thời hạn lưu giữ bản sao lưu cũ (ngày)</label>
                            <input type="number" name="auto_backup_keep_days" class="form-control" min="7" max="365" value="<?= htmlspecialchars($settings['auto_backup_keep_days'] ?? '30') ?>">
                            <div class="form-text" style="font-size: 11.5px;">Các file sao lưu vượt quá số ngày này sẽ được hệ thống tự động dọn dẹp để tiết kiệm dung lượng đĩa.</div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer" style="background: var(--bg-hover); border-top: 1px solid var(--border);">
                    <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary" style="padding: 7px 20px;">
                        <i class="fas fa-save"></i> Lưu Cấu Hình
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL CẢNH BÁO XÁC NHẬN PHỤC HỒI DỮ LIỆU (RESTORE CONFIRMATION) -->
<div class="modal fade" id="restoreConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius: 14px; border: 2px solid #ef4444; box-shadow: 0 10px 30px rgba(239, 68, 68, 0.2);">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" style="font-weight: 700;">
                    <i class="fas fa-exclamation-triangle"></i> CẢNH BÁO NGUY HIỂM: PHỤC HỒI CƠ SỞ DỮ LIỆU
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= BASE_URL ?>/backup/restore" method="POST" id="restoreForm">
                <input type="hidden" name="_csrf_token" value="<?= Session::generateCsrfToken() ?>">
                <input type="hidden" name="filename" id="restoreFilenameInput" value="">

                <div class="modal-body" style="padding: 24px;">
                    <div class="alert alert-danger" style="font-size: 13px; line-height: 1.5;">
                        <i class="fas fa-radiation fa-lg me-1"></i>
                        <strong>LƯU Ý ĐẶC BIỆT QUAN TRỌNG:</strong><br>
                        Hành động này sẽ <strong>GHI ĐÈ TOÀN BỘ CƠ SỞ DỮ LIỆU HIỆN TẠI</strong> bằng dữ liệu từ bản sao lưu được chọn.
                        Mọi thông tin nhân viên, chấm công, tiền lương phát sinh sau thời điểm tạo bản sao lưu này <strong>sẽ bị mất hoàn toàn</strong> và không thể hoàn tác!
                    </div>

                    <div class="mb-3">
                        <label class="form-label" style="font-weight: 600; font-size: 13px;">Bản sao lưu được chọn phục hồi:</label>
                        <input type="text" id="restoreDisplayFile" class="form-control" readonly style="font-weight: 700; color: #b91c1c; background: #fff5f5; font-family: monospace;">
                    </div>

                    <div class="mb-3">
                        <label class="form-label" style="font-weight: 700; font-size: 13px; color: #b91c1c;">
                            Để xác nhận, vui lòng gõ chính xác chữ <span style="background: #fee2e2; padding: 2px 6px; border-radius: 4px;">RESTORE</span> vào ô dưới đây:
                        </label>
                        <input type="text" name="confirm_code" id="restoreConfirmCode" class="form-control" required placeholder="Gõ RESTORE..." autocomplete="off">
                    </div>
                </div>

                <div class="modal-footer" style="background: var(--bg-hover); border-top: 1px solid var(--border);">
                    <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Hủy thao tác</button>
                    <button type="submit" class="btn btn-danger" style="padding: 8px 24px; font-weight: 700;">
                        <i class="fas fa-undo"></i> Tôi Hiểu Rủi Ro, Tiến Hành Phục Hồi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function startBackupNow() {
    let btn = document.getElementById('btnStartBackup');
    let progressBox = document.getElementById('backupProgressBox');

    btn.disabled = true;
    progressBox.style.display = 'block';

    fetch('<?= BASE_URL ?>/backup/create?format=json', {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.json())
    .then(data => {
        progressBox.style.display = 'none';
        btn.disabled = false;
        if (data.success) {
            alert(data.message);
            window.location.reload();
        } else {
            alert(data.message || 'Lỗi tạo sao lưu.');
        }
    })
    .catch(() => {
        progressBox.style.display = 'none';
        btn.disabled = false;
        alert('Lỗi kết nối máy chủ khi thực hiện sao lưu.');
    });
}

function openRestoreConfirm(filename) {
    document.getElementById('restoreFilenameInput').value = filename;
    document.getElementById('restoreDisplayFile').value = filename;
    document.getElementById('restoreConfirmCode').value = '';
    new bootstrap.Modal(document.getElementById('restoreConfirmModal')).show();
}
</script>
