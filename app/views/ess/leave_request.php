<?php
require_once APP_ROOT . '/views/ess/layout/header.php';
?>

<div class="container-xl">
    
    <!-- Header Page -->
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-2">
        <div>
            <h4 class="fw-bold mb-1 text-dark">
                <i class="fa-solid fa-plane-departure text-warning me-2"></i> Đăng ký nộp đơn xin nghỉ phép
            </h4>
            <p class="text-muted small mb-0">Nộp đơn xin nghỉ phép trực tuyến, hệ thống sẽ tự động chuyển tiếp tới Quản lý trực tiếp phê duyệt.</p>
        </div>
        <div>
            <a href="<?= BASE_URL ?>/ess/myLeaves" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                <i class="fa-solid fa-clock-rotate-left me-1"></i> Lịch sử nghỉ phép của tôi
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Form Nộp Đơn -->
        <div class="col-lg-7">
            <div class="card-custom p-4">
                <form action="<?= BASE_URL ?>/ess/submitLeaveRequest" method="POST" id="leaveForm">
                    <?= Session::csrfField() ?>

                    <h5 class="fw-bold text-dark mb-3 border-bottom pb-2">
                        <i class="fa-solid fa-file-pen text-primary me-2"></i> Thông tin đơn xin nghỉ
                    </h5>

                    <!-- Loại nghỉ phép -->
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Loại nghỉ phép (*):</label>
                        <select class="form-select" name="leave_type_id" id="leaveTypeSelect" required>
                            <?php foreach ($leaveTypes as $type): ?>
                                <option value="<?= $type['id'] ?>" data-max="<?= $type['max_days_per_year'] ?>" data-paid="<?= $type['is_paid'] ?>">
                                    <?= htmlspecialchars($type['name']) ?> 
                                    <?= !empty($type['is_paid']) ? '(Hưởng nguyên lương)' : '(Không hưởng lương)' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Thời gian nghỉ: Từ ngày -> Đến ngày -->
                    <div class="row g-3 mb-3">
                        <div class="col-sm-6">
                            <label class="form-label small fw-semibold">Từ ngày (*):</label>
                            <input type="date" class="form-control" name="start_date" id="startDate" 
                                   value="<?= date('Y-m-d') ?>" required min="<?= date('Y-m-d', strtotime('-30 days')) ?>">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label small fw-semibold">Đến ngày (*):</label>
                            <input type="date" class="form-control" name="end_date" id="endDate" 
                                   value="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>

                    <!-- Số ngày nghỉ -->
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Số ngày nghỉ làm việc (*):</label>
                        <div class="input-group" style="max-width: 250px;">
                            <input type="number" step="0.5" min="0.5" class="form-control fw-bold text-primary font-monospace" 
                                   name="total_days" id="totalDays" value="1.0" required>
                            <span class="input-group-text">ngày</span>
                        </div>
                        <small class="text-muted" style="font-size: 0.75rem;">(Hệ thống tự động tính ngày, bạn có thể chỉnh 0.5 ngày nếu nghỉ nửa buổi)</small>
                    </div>

                    <!-- Lý do xin nghỉ -->
                    <div class="mb-4">
                        <label class="form-label small fw-semibold">Lý do xin nghỉ (*):</label>
                        <textarea class="form-control" name="reason" rows="3" required
                                  placeholder="Nêu rõ lý do xin nghỉ và kế hoạch bàn giao công việc..."></textarea>
                    </div>

                    <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                        <a href="<?= BASE_URL ?>/ess/dashboard" class="btn btn-outline-secondary rounded-pill px-4 small">
                            Hủy bỏ
                        </a>
                        <button type="submit" class="btn btn-warning text-dark fw-bold rounded-pill px-4 shadow-sm">
                            <i class="fa-solid fa-paper-plane me-1"></i> Gửi đơn xin phê duyệt
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Cột Phải: Thống Kê Phép & Lịch Sử Gần Nhất -->
        <div class="col-lg-5">
            <!-- Thẻ thống kê phép năm -->
            <div class="card-custom p-4 mb-4 border-start border-4 border-success">
                <h5 class="fw-bold text-dark mb-3">
                    <i class="fa-solid fa-umbrella-beach text-success me-2"></i> Quỹ phép năm <?= date('Y') ?>
                </h5>
                <div class="row g-2 text-center mb-3">
                    <div class="col-6">
                        <div class="bg-light p-2 rounded">
                            <small class="text-muted d-block">Tổng phép được cấp</small>
                            <span class="fs-5 fw-bold text-dark"><?= number_format($leaveStats['entitled'], 1) ?></span> <small>ngày</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="bg-light p-2 rounded">
                            <small class="text-muted d-block">Đã sử dụng</small>
                            <span class="fs-5 fw-bold text-danger"><?= number_format($leaveStats['used'], 1) ?></span> <small>ngày</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="bg-light p-2 rounded">
                            <small class="text-muted d-block">Đang chờ duyệt</small>
                            <span class="fs-5 fw-bold text-warning"><?= number_format($leaveStats['pending'], 1) ?></span> <small>ngày</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="bg-success-subtle p-2 rounded border border-success-subtle">
                            <small class="text-success fw-bold d-block">Số ngày còn lại</small>
                            <span class="fs-5 fw-bold text-success"><?= number_format($leaveStats['remaining'], 1) ?></span> <small class="text-success">ngày</small>
                        </div>
                    </div>
                </div>
                <div class="small text-muted fst-italic">
                    <i class="fa-solid fa-circle-info me-1"></i> Phép năm được tính theo Luật Lao động (12 ngày cơ bản + thâm niên 5 năm/ngày).
                </div>
            </div>

            <!-- Các đơn gần nhất -->
            <div class="card-custom">
                <div class="card-custom-header">
                    <h5 class="card-custom-title">
                        <i class="fa-solid fa-clock-rotate-left text-primary"></i> Đơn gần đây
                    </h5>
                    <a href="<?= BASE_URL ?>/ess/myLeaves" class="small text-primary text-decoration-none fw-semibold">
                        Xem tất cả
                    </a>
                </div>
                <div class="card-custom-body p-0">
                    <?php if (!empty($recentLeaves)): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($recentLeaves as $lr): ?>
                                <div class="list-group-item p-3 border-bottom small">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <strong class="text-dark"><?= htmlspecialchars($lr['leave_type_name']) ?></strong>
                                        <?php 
                                            $stClass = match($lr['status']) {
                                                'Approved' => 'bg-success-subtle text-success border border-success-subtle',
                                                'Pending'  => 'bg-warning-subtle text-warning border border-warning-subtle',
                                                'Rejected' => 'bg-danger-subtle text-danger border border-danger-subtle',
                                                default    => 'bg-secondary-subtle text-secondary'
                                            };
                                            $stLabel = match($lr['status']) {
                                                'Approved' => 'Đã duyệt',
                                                'Pending'  => 'Chờ duyệt',
                                                'Rejected' => 'Từ chối',
                                                default    => 'Đã hủy'
                                            };
                                        ?>
                                        <span class="badge <?= $stClass ?>"><?= $stLabel ?></span>
                                    </div>
                                    <div class="text-muted">
                                        <?= date('d/m/Y', strtotime($lr['start_date'])) ?> &rarr; <?= date('d/m/Y', strtotime($lr['end_date'])) ?>
                                        (<strong><?= $lr['total_days'] ?></strong> ngày)
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4 text-muted small">
                            Chưa có đơn nghỉ phép nào.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
// Tự động tính số ngày khi thay đổi ngày bắt đầu / ngày kết thúc
document.addEventListener('DOMContentLoaded', function() {
    const startInput = document.getElementById('startDate');
    const endInput = document.getElementById('endDate');
    const totalDaysInput = document.getElementById('totalDays');

    function calculateDays() {
        const start = new Date(startInput.value);
        const end = new Date(endInput.value);
        if (start && end && end >= start) {
            let count = 0;
            let cur = new Date(start);
            while (cur <= end) {
                // Bỏ qua Chủ nhật nếu theo lịch làm việc 6 ngày/tuần
                if (cur.getDay() !== 0) {
                    count++;
                }
                cur.setDate(cur.getDate() + 1);
            }
            totalDaysInput.value = count > 0 ? count : 1;
        }
    }

    startInput.addEventListener('change', function() {
        if (endInput.value < startInput.value) {
            endInput.value = startInput.value;
        }
        calculateDays();
    });
    endInput.addEventListener('change', calculateDays);
});
</script>

<?php require_once APP_ROOT . '/views/ess/layout/footer.php'; ?>
