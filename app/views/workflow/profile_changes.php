<?php
/**
 * POSUNG HRIS - Duyệt sửa đổi hồ sơ & Diff View (Profile Change Requests)
 * File: app/views/workflow/profile_changes.php
 */
?>

<div class="container-fluid px-4 py-4">

    <!-- Header & Action Bar -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold mb-1 text-dark">
                <i class="fa-solid fa-user-shield text-primary me-2"></i> Duyệt sửa đổi hồ sơ cá nhân (Profile Change Requests)
            </h4>
            <p class="text-muted small mb-0">Thẩm định các yêu cầu cập nhật thông tin nhạy cảm (CCCD, Hộ khẩu thường trú, Tài khoản ngân hàng) từ nhân viên.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>/workflow/pendingApprovals" class="btn btn-outline-secondary rounded-pill px-3 shadow-sm">
                <i class="fa-solid fa-inbox me-1"></i> Về Hộp duyệt
            </a>
            <a href="<?= BASE_URL ?>/workflow/flows" class="btn btn-outline-primary rounded-pill px-3 shadow-sm">
                <i class="fa-solid fa-diagram-project me-1"></i> Cấu hình luồng
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

    <!-- Status Tabs -->
    <ul class="nav nav-pills mb-4 gap-2 bg-white p-2 rounded-4 shadow-sm border">
        <li class="nav-item">
            <a href="<?= BASE_URL ?>/workflow/profileChanges?status=Pending" 
               class="nav-link rounded-pill px-3 py-2 fw-semibold small <?= ($currentStatus === 'Pending' || empty($currentStatus)) ? 'active' : '' ?>">
                <i class="fa-solid fa-clock-rotate-left me-1"></i> Chờ phê duyệt
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= BASE_URL ?>/workflow/profileChanges?status=Approved" 
               class="nav-link rounded-pill px-3 py-2 fw-semibold small <?= $currentStatus === 'Approved' ? 'active' : '' ?>">
                <i class="fa-solid fa-circle-check me-1"></i> Đã phê duyệt
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= BASE_URL ?>/workflow/profileChanges?status=Rejected" 
               class="nav-link rounded-pill px-3 py-2 fw-semibold small <?= $currentStatus === 'Rejected' ? 'active' : '' ?>">
                <i class="fa-solid fa-circle-xmark me-1"></i> Bị từ chối
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= BASE_URL ?>/workflow/profileChanges?status=all" 
               class="nav-link rounded-pill px-3 py-2 fw-semibold small <?= $currentStatus === 'all' ? 'active' : '' ?>">
                <i class="fa-solid fa-list me-1"></i> Tất cả lịch sử
            </a>
        </li>
    </ul>

    <!-- Diff Requests List -->
    <?php if (empty($requests)): ?>
        <div class="card border-0 shadow-sm rounded-4 text-center py-5">
            <div class="text-muted display-4 mb-3"><i class="fa-solid fa-clipboard-check"></i></div>
            <h6 class="fw-bold text-dark">Không có yêu cầu nào trong trạng thái này</h6>
            <p class="text-muted small">Hiện tại không có thông tin sửa đổi nào cần xử lý.</p>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($requests as $r): ?>
                <?php
                    $statusBadge = match($r['status']) {
                        'Pending'  => 'bg-warning text-dark',
                        'Approved' => 'bg-success text-white',
                        'Rejected' => 'bg-danger text-white',
                        default    => 'bg-secondary text-white'
                    };
                    $statusLabel = match($r['status']) {
                        'Pending'  => 'Chờ HR duyệt',
                        'Approved' => 'Đã duyệt & Áp dụng',
                        'Rejected' => 'Bị từ chối',
                        default    => $r['status']
                    };
                ?>
                <div class="col-12">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                        <div class="card-header bg-white py-3 px-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 border-bottom">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle bg-light border d-flex align-items-center justify-content-center fw-bold text-primary" style="width: 44px; height: 44px;">
                                    <?= mb_substr($r['employee_name'] ?? 'N', 0, 1) ?>
                                </div>
                                <div>
                                    <h6 class="fw-bold text-dark mb-0">
                                        <?= htmlspecialchars($r['employee_name']) ?>
                                        <span class="badge bg-light text-dark border ms-2 font-monospace"><?= htmlspecialchars($r['emp_code']) ?></span>
                                    </h6>
                                    <small class="text-muted">
                                        <?= htmlspecialchars($r['dept_name'] ?? 'Phòng ban') ?> • <?= htmlspecialchars($r['pos_title'] ?? 'Chức vụ') ?>
                                    </small>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-3">
                                <span class="badge <?= $statusBadge ?> rounded-pill px-3 py-2 fw-semibold">
                                    <i class="fa-solid <?= $r['status'] === 'Pending' ? 'fa-spinner fa-spin' : ($r['status'] === 'Approved' ? 'fa-check' : 'fa-xmark') ?> me-1"></i>
                                    <?= $statusLabel ?>
                                </span>
                                <small class="text-muted">
                                    <i class="fa-regular fa-clock me-1"></i><?= date('d/m/Y H:i', strtotime($r['created_at'])) ?>
                                </small>
                            </div>
                        </div>

                        <div class="card-body p-4">
                            <div class="mb-3">
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2 py-1 mb-2">
                                    Mục thay đổi: <strong><?= htmlspecialchars($r['field_label'] ?: $r['field_name']) ?></strong>
                                </span>
                                <?php if (!empty($r['notes'])): ?>
                                    <div class="small text-muted mb-2">
                                        <i class="fa-regular fa-comment-dots me-1"></i> Ghi chú: <em><?= htmlspecialchars($r['notes']) ?></em>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Visual Diff View (Old Red vs New Green) -->
                            <div class="row g-3 align-items-center bg-light p-3 rounded-4 border">
                                <div class="col-md-5">
                                    <label class="form-label small fw-bold text-danger mb-1">
                                        <i class="fa-solid fa-minus-circle me-1"></i> Dữ liệu hiện tại (Giá trị cũ):
                                    </label>
                                    <div class="p-3 bg-danger-subtle text-danger border border-danger-subtle rounded-3 font-monospace small" style="min-height: 54px; word-break: break-all;">
                                        <del><?= !empty($r['old_value']) ? htmlspecialchars($r['old_value']) : '<em>(Chưa có dữ liệu / Trống)</em>' ?></del>
                                    </div>
                                </div>

                                <div class="col-md-2 text-center text-muted">
                                    <div class="d-none d-md-block fs-4 text-primary">
                                        <i class="fa-solid fa-arrow-right"></i>
                                    </div>
                                    <div class="d-md-none fs-4 text-primary my-2">
                                        <i class="fa-solid fa-arrow-down"></i>
                                    </div>
                                    <span class="badge bg-white text-muted border small shadow-sm">Đổi thành</span>
                                </div>

                                <div class="col-md-5">
                                    <label class="form-label small fw-bold text-success mb-1">
                                        <i class="fa-solid fa-plus-circle me-1"></i> Đề xuất cập nhật (Giá trị mới):
                                    </label>
                                    <div class="p-3 bg-success-subtle text-success border border-success-subtle rounded-3 font-monospace fw-bold small" style="min-height: 54px; word-break: break-all;">
                                        <ins><?= !empty($r['new_value']) ? htmlspecialchars($r['new_value']) : '<em>(Trống)</em>' ?></ins>
                                    </div>
                                </div>
                            </div>

                            <!-- Reviewer Information (if already reviewed) -->
                            <?php if ($r['status'] !== 'Pending'): ?>
                                <div class="mt-3 p-3 bg-white border rounded-3 small text-muted d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="fa-solid fa-user-check text-primary me-1"></i>
                                        Người duyệt: <strong><?= htmlspecialchars($r['reviewer_name'] ?? 'HR Admin') ?></strong>
                                        vào lúc <?= !empty($r['reviewed_at']) ? date('d/m/Y H:i', strtotime($r['reviewed_at'])) : '—' ?>
                                    </div>
                                    <?php if (!empty($r['notes'])): ?>
                                        <div>
                                            Ý kiến thẩm định: <span class="fw-semibold text-dark"><?= htmlspecialchars($r['notes']) ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Action Footer for Pending items -->
                        <?php if ($r['status'] === 'Pending'): ?>
                            <div class="card-footer bg-white py-3 px-4 d-flex justify-content-end gap-2 border-top">
                                <button type="button" class="btn btn-outline-danger rounded-pill px-4 btn-reject-diff" 
                                        data-id="<?= $r['id'] ?>"
                                        data-label="<?= htmlspecialchars($r['field_label'] ?: $r['field_name']) ?>"
                                        data-emp="<?= htmlspecialchars($r['employee_name']) ?>">
                                    <i class="fa-solid fa-xmark me-1"></i> Từ chối
                                </button>
                                <button type="button" class="btn btn-success rounded-pill px-4 fw-semibold btn-approve-diff" 
                                        data-id="<?= $r['id'] ?>"
                                        data-label="<?= htmlspecialchars($r['field_label'] ?: $r['field_name']) ?>"
                                        data-emp="<?= htmlspecialchars($r['employee_name']) ?>"
                                        data-newval="<?= htmlspecialchars($r['new_value'] ?? '') ?>">
                                    <i class="fa-solid fa-check me-1"></i> Duyệt & Cập nhật vào hồ sơ
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<!-- Modal Duyệt Diff -->
<div class="modal fade" id="approveDiffModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form id="approveDiffForm" action="" method="POST">
                <?= Session::csrfField() ?>
                <div class="modal-header bg-success text-white rounded-top-4 py-3">
                    <h5 class="modal-title fw-bold">
                        <i class="fa-solid fa-check-circle me-2"></i> Xác nhận Phê duyệt thay đổi
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="small text-muted mb-3">
                        Bạn đang duyệt cập nhật mục <strong id="approveDiffLabel" class="text-dark"></strong> cho nhân viên <strong id="approveDiffEmp" class="text-primary"></strong>.
                    </p>
                    <div class="alert alert-success-subtle border border-success-subtle rounded-3 p-3 mb-3 small">
                        Dữ liệu mới sẽ được ghi đè trực tiếp vào hồ sơ nhân sự của nhân sự này ngay khi bạn bấm duyệt.
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-semibold">Ghi chú phê duyệt (Tùy chọn):</label>
                        <input type="text" name="comments" class="form-control" placeholder="Đồng ý cập nhật theo minh chứng">
                    </div>
                </div>
                <div class="modal-footer bg-light rounded-bottom-4 py-2">
                    <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-semibold">
                        <i class="fa-solid fa-check me-1"></i> Xác nhận Duyệt
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Từ chối Diff -->
<div class="modal fade" id="rejectDiffModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form id="rejectDiffForm" action="" method="POST">
                <?= Session::csrfField() ?>
                <div class="modal-header bg-danger text-white rounded-top-4 py-3">
                    <h5 class="modal-title fw-bold">
                        <i class="fa-solid fa-times-circle me-2"></i> Từ chối yêu cầu thay đổi
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="small text-muted mb-3">
                        Từ chối cập nhật mục <strong id="rejectDiffLabel" class="text-dark"></strong> cho nhân viên <strong id="rejectDiffEmp" class="text-danger"></strong>.
                    </p>
                    <div class="mb-2">
                        <label class="form-label small fw-semibold text-danger">Lý do từ chối (*):</label>
                        <textarea name="comments" class="form-control border-danger" rows="3" required placeholder="Nêu rõ lý do (VD: Số tài khoản không chính chủ, ảnh CCCD bị mờ...)"></textarea>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const approveModal = new bootstrap.Modal(document.getElementById('approveDiffModal'));
    const rejectModal = new bootstrap.Modal(document.getElementById('rejectDiffModal'));

    document.querySelectorAll('.btn-approve-diff').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            document.getElementById('approveDiffForm').action = `<?= BASE_URL ?>/workflow/approveProfileChange/${id}`;
            document.getElementById('approveDiffLabel').textContent = this.dataset.label;
            document.getElementById('approveDiffEmp').textContent = this.dataset.emp;
            approveModal.show();
        });
    });

    document.querySelectorAll('.btn-reject-diff').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            document.getElementById('rejectDiffForm').action = `<?= BASE_URL ?>/workflow/rejectProfileChange/${id}`;
            document.getElementById('rejectDiffLabel').textContent = this.dataset.label;
            document.getElementById('rejectDiffEmp').textContent = this.dataset.emp;
            rejectModal.show();
        });
    });
});
</script>
