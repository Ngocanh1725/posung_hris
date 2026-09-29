<?php
require_once APP_ROOT . '/views/layouts/header.php';
?>

<div class="content-container">
    
    <!-- Header Page -->
    <div class="page-header d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3">
        <div>
            <h2 class="page-title fw-bold mb-1">
                <i class="fa-solid fa-sliders text-primary me-2"></i> Cài đặt Quy tắc thông báo tự động
            </h2>
            <p class="text-muted small mb-0">Cấu hình thời gian kích hoạt các cảnh báo hạn ngạch, sự kiện nhân sự và mẫu thông báo hệ thống.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <form action="<?= BASE_URL ?>/notification/settings" method="POST" class="d-inline">
                <?= Session::csrfField() ?>
                <input type="hidden" name="action" value="trigger_now">
                <button type="submit" class="btn btn-warning text-dark fw-bold btn-sm rounded-pill px-3 shadow-sm" onclick="return confirm('Hệ thống sẽ quét toàn bộ CSDL và tạo các thông báo nhắc việc ngay lập tức. Bạn có muốn tiếp tục?')">
                    <i class="fa-solid fa-bolt me-1"></i> Quét & Tạo thông báo ngay
                </button>
            </form>
            <a href="<?= BASE_URL ?>/notification" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="fa-solid fa-arrow-left me-1"></i> Danh sách thông báo
            </a>
        </div>
    </div>

    <!-- Danh Sách Quy Tắc (Rules Table) -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white p-3 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-dark">
                <i class="fa-solid fa-list-check text-primary me-2"></i> Danh mục quy tắc nhắc nhở (<?= count($rules) ?> quy tắc)
            </h6>
            <span class="badge bg-success-subtle text-success border border-success-subtle">Hệ thống đang hoạt động</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 60px;">STT</th>
                            <th>Tên sự kiện / Loại nhắc việc</th>
                            <th>Mã sự kiện</th>
                            <th class="text-center">Số ngày trước</th>
                            <th>Đối tượng nhận</th>
                            <th class="text-center">Trạng thái</th>
                            <th class="text-center" style="width: 140px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rules as $index => $r): ?>
                            <tr>
                                <td class="text-center text-muted"><?= $index + 1 ?></td>
                                <td>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($r['name']) ?></div>
                                    <small class="text-muted text-truncate d-block" style="max-width: 350px;">
                                        <?= htmlspecialchars($r['template']) ?>
                                    </small>
                                </td>
                                <td>
                                    <span class="badge bg-light text-primary border font-monospace">
                                        <?= htmlspecialchars($r['event_type']) ?>
                                    </span>
                                </td>
                                <td class="text-center fw-bold fs-6 font-monospace text-primary">
                                    <?= $r['days_before'] > 0 ? ($r['days_before'] . ' ngày') : 'Trong ngày' ?>
                                </td>
                                <td>
                                    <span class="badge bg-secondary-subtle text-secondary">
                                        <?= htmlspecialchars($r['target_role'] ?? 'admin,hr_manager') ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <form action="<?= BASE_URL ?>/notification/settings" method="POST" class="d-inline">
                                        <?= Session::csrfField() ?>
                                        <input type="hidden" name="action" value="toggle_active">
                                        <input type="hidden" name="rule_id" value="<?= $r['id'] ?>">
                                        <button type="submit" class="btn btn-sm py-0 px-2 rounded-pill <?= !empty($r['is_active']) ? 'btn-success' : 'btn-secondary' ?>" title="Bấm để bật/tắt">
                                            <?= !empty($r['is_active']) ? 'Đang bật' : 'Đã tắt' ?>
                                        </button>
                                    </form>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 py-1" 
                                            data-bs-toggle="modal" data-bs-target="#editModal<?= $r['id'] ?>">
                                        <i class="fa-regular fa-pen-to-square me-1"></i> Sửa
                                    </button>
                                </td>
                            </tr>

                            <!-- Modal Chỉnh Sửa Quy Tắc -->
                            <div class="modal fade" id="editModal<?= $r['id'] ?>" tabindex="-1" aria-labelledby="modalLabel<?= $r['id'] ?>" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content rounded-4 border-0 shadow">
                                        <form action="<?= BASE_URL ?>/notification/settings" method="POST">
                                            <?= Session::csrfField() ?>
                                            <input type="hidden" name="action" value="update_rule">
                                            <input type="hidden" name="rule_id" value="<?= $r['id'] ?>">

                                            <div class="modal-header border-bottom">
                                                <h5 class="modal-title fw-bold" id="modalLabel<?= $r['id'] ?>">
                                                    <i class="fa-solid fa-pen-to-square text-primary me-2"></i> Chỉnh sửa: <?= htmlspecialchars($r['name']) ?>
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>

                                            <div class="modal-body p-4">
                                                <div class="mb-3">
                                                    <label class="form-label small fw-semibold">Số ngày nhắc trước sự kiện:</label>
                                                    <div class="input-group">
                                                        <input type="number" class="form-control" name="days_before" value="<?= $r['days_before'] ?>" min="0" max="365" required>
                                                        <span class="input-group-text">ngày</span>
                                                    </div>
                                                    <small class="text-muted" style="font-size: 0.72rem;">(0 ngày = Nhắc đúng ngày diễn ra sự kiện, ví dụ sinh nhật)</small>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label small fw-semibold">Đối tượng vai trò nhận thông báo:</label>
                                                    <input type="text" class="form-control" name="target_role" value="<?= htmlspecialchars($r['target_role'] ?? 'admin,hr_manager') ?>" required>
                                                    <small class="text-muted" style="font-size: 0.72rem;">Phân tách bằng dấu phẩy: admin, hr_manager, site_manager, cb_staff, employee, all</small>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label small fw-semibold">Mẫu nội dung thông báo (Template):</label>
                                                    <textarea class="form-control font-monospace" name="template" rows="3" required><?= htmlspecialchars($r['template']) ?></textarea>
                                                    <small class="text-muted" style="font-size: 0.72rem;">Các biến hỗ trợ: {employee_name}, {emp_code}, {expiry_date}, {date}, {dept_name}</small>
                                                </div>

                                                <div class="form-check form-switch mb-2">
                                                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="swActive<?= $r['id'] ?>" <?= !empty($r['is_active']) ? 'checked' : '' ?>>
                                                    <label class="form-check-label small fw-semibold" for="swActive<?= $r['id'] ?>">
                                                        Kích hoạt quy tắc nhắc việc này
                                                    </label>
                                                </div>
                                            </div>

                                            <div class="modal-footer border-top p-3">
                                                <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Hủy</button>
                                                <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold">Lưu thay đổi</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Hướng dẫn Cài đặt Cron Job / Scheduled Task -->
    <div class="card border-0 shadow-sm rounded-4 bg-light">
        <div class="card-body p-4">
            <h5 class="fw-bold text-dark mb-2">
                <i class="fa-solid fa-server text-secondary me-2"></i> Hướng dẫn kích hoạt Tự động hóa (Cron Job / Task Scheduler)
            </h5>
            <p class="text-muted small mb-3">
                Để hệ thống tự động quét và gửi thông báo nhắc việc hàng ngày vào lúc 07:00 sáng, hãy thiết lập lệnh sau trong <strong>Windows Task Scheduler</strong> (hoặc Crontab trên Linux):
            </p>
            <div class="bg-dark text-white p-3 rounded-3 font-monospace small mb-3 user-select-all">
                C:\xampp\php\php.exe -f C:\xampp\htdocs\posung_hris\cron_notifications.php
            </div>
            <div class="text-muted small">
                <i class="fa-solid fa-circle-check text-success me-1"></i> Script <code>cron_notifications.php</code> đã được tích hợp cơ chế chống trùng lặp (Deduplication) để đảm bảo không gửi lặp thông báo nhiều lần trong cùng 1 ngày.
            </div>
        </div>
    </div>

</div>

<?php require_once APP_ROOT . '/views/layouts/footer.php'; ?>
