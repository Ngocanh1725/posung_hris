<?php
/**
 * POSUNG HRIS - Hộp duyệt tập trung (Approval Inbox)
 * File: app/views/workflow/pending.php
 */
?>

<div class="container-fluid px-4 py-4">

    <!-- Header & Action Bar -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold mb-1 text-dark">
                <i class="fa-solid fa-inbox text-primary me-2"></i> Hộp duyệt tập trung (Approval Inbox)
            </h4>
            <p class="text-muted small mb-0">Trung tâm xử lý phê duyệt đa cấp tất cả đơn từ nhân sự: Nghỉ phép, Vay vốn, Chi phí, Điều chuyển & Sửa hồ sơ.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>/workflow/profileChanges" class="btn btn-outline-info rounded-pill px-3 shadow-sm">
                <i class="fa-solid fa-code-compare me-1"></i> Diff View sửa hồ sơ
            </a>
            <a href="<?= BASE_URL ?>/workflow/flows" class="btn btn-primary rounded-pill px-3 shadow-sm">
                <i class="fa-solid fa-diagram-project me-1"></i> Cấu hình luồng duyệt
            </a>
        </div>
    </div>

    <!-- Flash Notifications -->
    <?php if (Session::hasFlash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm rounded-3" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i> <?= Session::flash('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (Session::hasFlash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm rounded-3" role="alert">
            <i class="fa-solid fa-triangle-exclamation me-2"></i> <?= Session::flash('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- KPI Metric Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-2">
            <a href="<?= BASE_URL ?>/workflow/pendingApprovals?module=all" class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-4 text-center p-3 h-100 transition-hover <?= ($currentModule === 'all' || empty($currentModule)) ? 'border-primary border-2 bg-primary-subtle' : 'bg-white' ?>">
                    <div class="text-primary mb-1"><i class="fa-solid fa-layer-group fs-4"></i></div>
                    <div class="fs-4 fw-bold text-dark"><?= $counts['total'] ?? 0 ?></div>
                    <div class="text-muted small fw-semibold">Tất cả đơn</div>
                </div>
            </a>
        </div>
        <div class="col-6 col-lg-2">
            <a href="<?= BASE_URL ?>/workflow/pendingApprovals?module=leave" class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-4 text-center p-3 h-100 transition-hover <?= $currentModule === 'leave' ? 'border-success border-2 bg-success-subtle' : 'bg-white' ?>">
                    <div class="text-success mb-1"><i class="fa-solid fa-calendar-day fs-4"></i></div>
                    <div class="fs-4 fw-bold text-dark"><?= $counts['leave'] ?? 0 ?></div>
                    <div class="text-muted small fw-semibold">Nghỉ phép</div>
                </div>
            </a>
        </div>
        <div class="col-6 col-lg-2">
            <a href="<?= BASE_URL ?>/workflow/pendingApprovals?module=loan" class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-4 text-center p-3 h-100 transition-hover <?= $currentModule === 'loan' ? 'border-warning border-2 bg-warning-subtle' : 'bg-white' ?>">
                    <div class="text-warning mb-1"><i class="fa-solid fa-hand-holding-dollar fs-4"></i></div>
                    <div class="fs-4 fw-bold text-dark"><?= $counts['loan'] ?? 0 ?></div>
                    <div class="text-muted small fw-semibold">Vay vốn / Ứng</div>
                </div>
            </a>
        </div>
        <div class="col-6 col-lg-2">
            <a href="<?= BASE_URL ?>/workflow/pendingApprovals?module=expense" class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-4 text-center p-3 h-100 transition-hover <?= $currentModule === 'expense' ? 'border-info border-2 bg-info-subtle' : 'bg-white' ?>">
                    <div class="text-info mb-1"><i class="fa-solid fa-receipt fs-4"></i></div>
                    <div class="fs-4 fw-bold text-dark"><?= $counts['expense'] ?? 0 ?></div>
                    <div class="text-muted small fw-semibold">Chi phí / CT phí</div>
                </div>
            </a>
        </div>
        <div class="col-6 col-lg-2">
            <a href="<?= BASE_URL ?>/workflow/pendingApprovals?module=transfer" class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-4 text-center p-3 h-100 transition-hover <?= $currentModule === 'transfer' ? 'border-secondary border-2 bg-secondary-subtle' : 'bg-white' ?>">
                    <div class="text-secondary mb-1"><i class="fa-solid fa-shuffle fs-4"></i></div>
                    <div class="fs-4 fw-bold text-dark"><?= $counts['transfer'] ?? 0 ?></div>
                    <div class="text-muted small fw-semibold">Điều chuyển Site</div>
                </div>
            </a>
        </div>
        <div class="col-6 col-lg-2">
            <a href="<?= BASE_URL ?>/workflow/pendingApprovals?module=profile_change" class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-4 text-center p-3 h-100 transition-hover <?= $currentModule === 'profile_change' ? 'border-danger border-2 bg-danger-subtle' : 'bg-white' ?>">
                    <div class="text-danger mb-1"><i class="fa-solid fa-user-shield fs-4"></i></div>
                    <div class="fs-4 fw-bold text-dark"><?= $counts['profile_change'] ?? 0 ?></div>
                    <div class="text-muted small fw-semibold">Sửa hồ sơ</div>
                </div>
            </a>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
            <form action="<?= BASE_URL ?>/workflow/pendingApprovals" method="GET" class="row g-2 align-items-center">
                <input type="hidden" name="module" value="<?= htmlspecialchars($currentModule) ?>">
                
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                        <input type="text" name="search" class="form-control bg-light border-start-0" 
                               placeholder="Tìm theo tên nhân viên, mã NV hoặc nội dung đơn..." 
                               value="<?= htmlspecialchars($search ?? '') ?>">
                    </div>
                </div>

                <div class="col-md-3">
                    <select name="module" class="form-select bg-light" onchange="this.form.submit()">
                        <option value="all" <?= $currentModule === 'all' ? 'selected' : '' ?>>-- Tất cả phân hệ (<?= $counts['total'] ?? 0 ?>) --</option>
                        <option value="leave" <?= $currentModule === 'leave' ? 'selected' : '' ?>>Nghỉ phép (<?= $counts['leave'] ?? 0 ?>)</option>
                        <option value="loan" <?= $currentModule === 'loan' ? 'selected' : '' ?>>Vay vốn / Ứng lương (<?= $counts['loan'] ?? 0 ?>)</option>
                        <option value="expense" <?= $currentModule === 'expense' ? 'selected' : '' ?>>Thanh toán chi phí (<?= $counts['expense'] ?? 0 ?>)</option>
                        <option value="transfer" <?= $currentModule === 'transfer' ? 'selected' : '' ?>>Điều chuyển nhân sự (<?= $counts['transfer'] ?? 0 ?>)</option>
                        <option value="profile_change" <?= $currentModule === 'profile_change' ? 'selected' : '' ?>>Sửa đổi hồ sơ cá nhân (<?= $counts['profile_change'] ?? 0 ?>)</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100 rounded-pill">
                        <i class="fa-solid fa-filter me-1"></i> Lọc
                    </button>
                </div>

                <?php if (!empty($search) || ($currentModule !== 'all' && !empty($currentModule))): ?>
                    <div class="col-md-2">
                        <a href="<?= BASE_URL ?>/workflow/pendingApprovals" class="btn btn-outline-secondary w-100 rounded-pill">
                            <i class="fa-solid fa-rotate-left me-1"></i> Đặt lại
                        </a>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Main Approval Requests Table -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-bottom">
            <h6 class="fw-bold mb-0 text-dark">
                <i class="fa-solid fa-list-check text-primary me-2"></i> Danh sách đơn đang chờ duyệt (<?= count($requests) ?> đơn)
            </h6>
            <div class="small text-muted">
                Được cập nhật tự động từ Approval Engine
            </div>
        </div>
        <div class="card-body p-0">
            <?php if (empty($requests)): ?>
                <div class="text-center py-5">
                    <div class="text-muted display-4 mb-3"><i class="fa-regular fa-folder-open"></i></div>
                    <h6 class="fw-bold text-dark">Tuyệt vời! Không có đơn nào đang chờ duyệt</h6>
                    <p class="text-muted small">Tất cả các yêu cầu trong hệ thống đã được xử lý hoàn tất.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small text-uppercase fw-semibold text-muted">
                            <tr>
                                <th style="width: 50px;" class="text-center">#</th>
                                <th style="width: 130px;">Phân hệ</th>
                                <th style="width: 220px;">Người yêu cầu</th>
                                <th>Nội dung chi tiết</th>
                                <th style="width: 160px;" class="text-center">Cấp duyệt</th>
                                <th style="width: 140px;">Thời gian gửi</th>
                                <th style="width: 180px;" class="text-end pe-4">Hành động</th>
                            </tr>
                        </thead>
                        <tbody class="small">
                            <?php foreach ($requests as $idx => $r): ?>
                                <?php
                                    $mod = $r['module'];
                                    $det = $r['details'] ?? [];
                                    
                                    // Badge color & icon mapping
                                    $badgeClass = match($mod) {
                                        'leave'          => 'bg-success-subtle text-success border border-success-subtle',
                                        'loan'           => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
                                        'expense'        => 'bg-info-subtle text-info-emphasis border border-info-subtle',
                                        'transfer'       => 'bg-secondary-subtle text-secondary border border-secondary-subtle',
                                        'profile_change' => 'bg-danger-subtle text-danger border border-danger-subtle',
                                        default          => 'bg-primary-subtle text-primary border border-primary-subtle'
                                    };
                                    $modIcon = match($mod) {
                                        'leave'          => 'fa-calendar-day',
                                        'loan'           => 'fa-hand-holding-dollar',
                                        'expense'        => 'fa-receipt',
                                        'transfer'       => 'fa-shuffle',
                                        'profile_change' => 'fa-user-shield',
                                        default          => 'fa-file'
                                    };
                                    $modLabels = [
                                        'leave'          => 'Nghỉ phép',
                                        'loan'           => 'Vay vốn',
                                        'expense'        => 'Chi phí',
                                        'transfer'       => 'Điều chuyển',
                                        'profile_change' => 'Sửa hồ sơ'
                                    ];
                                ?>
                                <tr>
                                    <td class="text-center text-muted fw-bold"><?= $idx + 1 ?></td>
                                    <td>
                                        <span class="badge <?= $badgeClass ?> rounded-pill px-2 py-1">
                                            <i class="fa-solid <?= $modIcon ?> me-1"></i> <?= $modLabels[$mod] ?? $mod ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="rounded-circle bg-light d-flex align-items-center justify-content-center fw-bold text-primary border" style="width: 38px; height: 38px;">
                                                <?= mb_substr($det['employee_name'] ?? $r['creator_name'] ?? 'N', 0, 1) ?>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark"><?= htmlspecialchars($det['employee_name'] ?? $r['creator_name'] ?? 'Nhân viên') ?></div>
                                                <div class="text-muted font-monospace" style="font-size: 0.75rem;">
                                                    <?= htmlspecialchars($det['emp_code'] ?? $r['creator_emp_code'] ?? 'POSUNG') ?> 
                                                    • <?= htmlspecialchars($det['dept_name'] ?? 'Văn phòng') ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark mb-1">
                                            <?= htmlspecialchars($det['title'] ?? "Yêu cầu #{$r['record_id']}") ?>
                                        </div>
                                        <div class="text-muted text-truncate" style="max-width: 400px;">
                                            <?= htmlspecialchars($det['summary'] ?? 'Đang chờ thẩm định phê duyệt theo quy trình.') ?>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-primary text-white rounded-pill px-2 py-1 mb-1">
                                            Cấp <?= $r['current_level'] ?> / <?= $r['total_levels'] ?>
                                        </span>
                                        <div class="text-muted" style="font-size: 0.72rem;">
                                            <?= htmlspecialchars($r['current_role_name'] ?? 'Quản lý duyệt') ?>
                                        </div>
                                    </td>
                                    <td class="text-muted">
                                        <div><i class="fa-regular fa-clock me-1"></i><?= date('H:i d/m/Y', strtotime($r['created_at'])) ?></div>
                                        <div class="text-muted" style="font-size: 0.72rem;"><?= htmlspecialchars($r['flow_name'] ?? 'Quy trình tiêu chuẩn') ?></div>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="btn-group btn-group-sm">
                                            <?php if ($mod === 'profile_change'): ?>
                                                <a href="<?= BASE_URL ?>/workflow/profileChanges" class="btn btn-outline-info rounded-pill me-1 px-2" title="Xem Diff chi tiết">
                                                    <i class="fa-solid fa-code-compare me-1"></i> Diff
                                                </a>
                                            <?php endif; ?>
                                            <button type="button" class="btn btn-success rounded-pill me-1 px-3 btn-approve-modal" 
                                                    data-id="<?= $r['id'] ?>"
                                                    data-title="<?= htmlspecialchars($det['title'] ?? '') ?>"
                                                    data-user="<?= htmlspecialchars($det['employee_name'] ?? $r['creator_name'] ?? '') ?>"
                                                    data-level="<?= $r['current_level'] ?>"
                                                    data-maxlevel="<?= $r['total_levels'] ?>">
                                                <i class="fa-solid fa-check me-1"></i> Duyệt
                                            </button>
                                            <button type="button" class="btn btn-outline-danger rounded-pill px-2 btn-reject-modal" 
                                                    data-id="<?= $r['id'] ?>"
                                                    data-title="<?= htmlspecialchars($det['title'] ?? '') ?>"
                                                    data-user="<?= htmlspecialchars($det['employee_name'] ?? $r['creator_name'] ?? '') ?>">
                                                <i class="fa-solid fa-xmark"></i>
                                            </button>
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
</div>

<!-- ══════════════════════════════════════════════════════════
     MODAL DUYỆT ĐƠN (APPROVE MODAL)
     ══════════════════════════════════════════════════════════ -->
<div class="modal fade" id="approveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form action="<?= BASE_URL ?>/workflow/approve" method="POST">
                <?= Session::csrfField() ?>
                <input type="hidden" name="request_id" id="modal_approve_req_id">
                <input type="hidden" name="return_url" value="workflow/pendingApprovals?module=<?= htmlspecialchars($currentModule) ?>">

                <div class="modal-header bg-success text-white rounded-top-4 py-3">
                    <h5 class="modal-title fw-bold">
                        <i class="fa-solid fa-stamp me-2"></i> Xác nhận Ký duyệt Yêu cầu
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-success-subtle border border-success-subtle rounded-3 p-3 mb-3">
                        <div class="fw-bold text-dark" id="modal_approve_title">Tiêu đề đơn</div>
                        <div class="text-muted small mt-1">Người gửi: <span id="modal_approve_user" class="fw-semibold text-dark"></span></div>
                        <div class="text-muted small">Cấp duyệt hiện tại: <span id="modal_approve_level" class="badge bg-success"></span></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Ý kiến phê duyệt / Ghi chú (Tùy chọn):</label>
                        <textarea name="comments" class="form-control" rows="3" placeholder="Đồng ý phê duyệt yêu cầu này..."></textarea>
                    </div>

                    <div class="mb-2">
                        <label class="form-label small fw-semibold">Mã PIN Ký số E-Sign (Nếu có thiết lập):</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-key text-muted"></i></span>
                            <input type="password" name="esign_pin" class="form-control" placeholder="Nhập PIN E-Sign (Mặc định: posung@123)">
                        </div>
                        <div class="form-text text-muted" style="font-size: 0.75rem;">Để trống nếu bạn không bật xác thực mã PIN cấp 2.</div>
                    </div>
                </div>
                <div class="modal-footer bg-light rounded-bottom-4 py-2">
                    <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-semibold">
                        <i class="fa-solid fa-check me-1"></i> Ký duyệt ngay
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════
     MODAL TỪ CHỐI ĐƠN (REJECT MODAL)
     ══════════════════════════════════════════════════════════ -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form action="<?= BASE_URL ?>/workflow/reject" method="POST">
                <?= Session::csrfField() ?>
                <input type="hidden" name="request_id" id="modal_reject_req_id">
                <input type="hidden" name="return_url" value="workflow/pendingApprovals?module=<?= htmlspecialchars($currentModule) ?>">

                <div class="modal-header bg-danger text-white rounded-top-4 py-3">
                    <h5 class="modal-title fw-bold">
                        <i class="fa-solid fa-ban me-2"></i> Xác nhận Từ chối Yêu cầu
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-danger-subtle border border-danger-subtle rounded-3 p-3 mb-3">
                        <div class="fw-bold text-dark" id="modal_reject_title">Tiêu đề đơn</div>
                        <div class="text-muted small mt-1">Người gửi: <span id="modal_reject_user" class="fw-semibold text-dark"></span></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-danger">Lý do từ chối phê duyệt (*):</label>
                        <textarea name="comments" class="form-control border-danger" rows="3" required placeholder="Vui lòng nêu rõ lý do từ chối để người gửi nắm bắt thông tin..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light rounded-bottom-4 py-2">
                    <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4 fw-semibold">
                        <i class="fa-solid fa-ban me-1"></i> Xác nhận Từ chối
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.transition-hover {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.transition-hover:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.08) !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Xử lý modal Approve
    const approveModal = new bootstrap.Modal(document.getElementById('approveModal'));
    document.querySelectorAll('.btn-approve-modal').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('modal_approve_req_id').value = this.dataset.id;
            document.getElementById('modal_approve_title').textContent = this.dataset.title;
            document.getElementById('modal_approve_user').textContent = this.dataset.user;
            document.getElementById('modal_approve_level').textContent = `Cấp ${this.dataset.level} / ${this.dataset.maxlevel}`;
            approveModal.show();
        });
    });

    // Xử lý modal Reject
    const rejectModal = new bootstrap.Modal(document.getElementById('rejectModal'));
    document.querySelectorAll('.btn-reject-modal').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('modal_reject_req_id').value = this.dataset.id;
            document.getElementById('modal_reject_title').textContent = this.dataset.title;
            document.getElementById('modal_reject_user').textContent = this.dataset.user;
            rejectModal.show();
        });
    });
});
</script>
