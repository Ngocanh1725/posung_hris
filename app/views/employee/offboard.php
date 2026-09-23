<?php
/** View: employee/offboard.php – Quy trình thôi việc / Clearance Form */
?>

<div class="panel" style="max-width: 900px; margin: 0 auto;">
    <div class="panel-header bg-danger text-white">
        <h3><i class="fas fa-sign-out-alt"></i> QUY TRÌNH THÔI VIỆC (OFFBOARDING)</h3>
    </div>
    <div class="panel-body">
        
        <div class="employee-info mb-4 p-3 bg-light rounded border">
            <h4 class="mb-2 text-primary"><?= h($employee->full_name) ?> (<?= h($employee->emp_code) ?>)</h4>
            <div class="row">
                <div class="col-md-4">
                    <p class="mb-1"><i class="fas fa-briefcase text-muted"></i> <strong>Chức vụ:</strong> <?= h($employee->pos_title ?? 'N/A') ?></p>
                    <p class="mb-1"><i class="fas fa-building text-muted"></i> <strong>Phòng ban:</strong> <?= h($employee->dept_name ?? 'N/A') ?></p>
                </div>
                <div class="col-md-4">
                    <p class="mb-1"><i class="fas fa-map-marker-alt text-muted"></i> <strong>Dự án:</strong> <?= h($employee->project_name ?? 'Trụ sở chính') ?></p>
                    <p class="mb-1"><i class="fas fa-id-card text-muted"></i> <strong>CCCD:</strong> <?= h($employee->id_card) ?></p>
                </div>
                <div class="col-md-4">
                    <p class="mb-1"><i class="far fa-calendar-alt text-muted"></i> <strong>Ngày vào làm:</strong> <?= fmtDate($employee->join_date) ?></p>
                    <p class="mb-1"><i class="fas fa-user-tag text-muted"></i> <strong>Trạng thái HT:</strong> <span class="badge bg-secondary"><?= h($employee->status) ?></span></p>
                </div>
            </div>
        </div>

        <?php if ($employee->status === 'Terminated' || $employee->status === 'Resigned'): ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i> Nhân sự này đã hoàn tất quy trình nghỉ việc và đang ở trạng thái <strong><?= h($employee->status) ?></strong>.
            </div>
        <?php else: ?>
            <form action="<?= BASE_URL ?>/employee/offboard/<?= $employee->id ?>" method="POST" id="offboardForm">
                <input type="hidden" name="_csrf_token" value="<?= Session::generateCsrfToken() ?>">
                
                <h5 class="mb-3"><i class="fas fa-tasks"></i> DANH SÁCH KIỂM KÊ BÀN GIAO (CLEARANCE CHECKLIST)</h5>
                
                <div class="table-responsive mb-4">
                    <table class="table table-bordered checklist-table">
                        <thead class="bg-light">
                            <tr>
                                <th width="10%" class="text-center">Xác nhận</th>
                                <th width="30%">Nội dung bàn giao</th>
                                <th width="30%">Phòng ban phụ trách</th>
                                <th width="30%">Chữ ký (Bản cứng)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Bước 1 -->
                            <tr>
                                <td class="text-center align-middle">
                                    <div class="custom-control custom-checkbox custom-control-lg">
                                        <input type="checkbox" class="custom-control-input step-check" id="step1" name="tools_returned" value="1">
                                        <label class="custom-control-label" for="step1"></label>
                                    </div>
                                </td>
                                <td class="align-middle fw-bold text-dark">
                                    1. Trả máy móc thi công, thẻ từ, chìa khóa, laptop (nếu có)
                                </td>
                                <td class="align-middle">Quản lý kho / Admin Dự án</td>
                                <td class="align-middle text-muted fst-italic"></td>
                            </tr>
                            <!-- Bước 2 -->
                            <tr>
                                <td class="text-center align-middle">
                                    <div class="custom-control custom-checkbox custom-control-lg">
                                        <input type="checkbox" class="custom-control-input step-check" id="step2" name="ppe_returned" value="1">
                                        <label class="custom-control-label" for="step2"></label>
                                    </div>
                                </td>
                                <td class="align-middle fw-bold text-dark">
                                    2. Bàn giao đồ bảo hộ lao động (PPE) / Khấu trừ nếu làm mất
                                </td>
                                <td class="align-middle">Phòng HSE / Cán bộ An toàn</td>
                                <td class="align-middle text-muted fst-italic"></td>
                            </tr>
                            <!-- Bước 3 -->
                            <tr>
                                <td class="text-center align-middle">
                                    <div class="custom-control custom-checkbox custom-control-lg">
                                        <input type="checkbox" class="custom-control-input step-check" id="step3" name="account_settled" value="1">
                                        <label class="custom-control-label" for="step3"></label>
                                    </div>
                                </td>
                                <td class="align-middle fw-bold text-dark">
                                    3. Quyết toán tạm ứng, công nợ, tính lương tháng cuối
                                </td>
                                <td class="align-middle">Phòng Kế toán (Accounting)</td>
                                <td class="align-middle text-muted fst-italic"></td>
                            </tr>
                            <!-- Bước 4 -->
                            <tr>
                                <td class="text-center align-middle">
                                    <div class="custom-control custom-checkbox custom-control-lg">
                                        <input type="checkbox" class="custom-control-input step-check" id="step4" name="insurance_closed" value="1">
                                        <label class="custom-control-label" for="step4"></label>
                                    </div>
                                </td>
                                <td class="align-middle fw-bold text-dark">
                                    4. Chốt sổ BHXH, ra Quyết định thôi việc & Trả hồ sơ gốc
                                </td>
                                <td class="align-middle">Phòng HCNS (HR Dept)</td>
                                <td class="align-middle text-muted fst-italic"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="form-group mb-4">
                    <label class="fw-bold">Ghi chú thêm (Lý do nghỉ việc, Vấn đề tồn đọng...)</label>
                    <textarea name="notes" class="form-control" rows="3" placeholder="Nhập ghi chú..."></textarea>
                </div>

                <!-- Cảnh báo trạng thái -->
                <div class="alert alert-info" id="statusWarning">
                    <i class="fas fa-info-circle"></i> Vui lòng tick chọn ĐẦY ĐỦ 4 bước trên để hệ thống cho phép chốt trạng thái thành <strong>TERMINATED</strong>.
                </div>

                <div class="form-actions text-end border-top pt-3 mt-4">
                    <a href="<?= BASE_URL ?>/employee/detail/<?= $employee->id ?>" class="btn btn-outline-secondary">Hủy bỏ</a>
                    <button type="submit" class="btn btn-danger" id="btnSubmit" disabled>
                        <i class="fas fa-power-off"></i> XÁC NHẬN CHỐT THÔI VIỆC
                    </button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<style>
.checklist-table th { background: var(--bg-card-header); }
.custom-control-lg .custom-control-label::before, 
.custom-control-lg .custom-control-label::after {
    top: 0.1rem;
    left: -2rem;
    width: 1.5rem;
    height: 1.5rem;
}
.custom-control-input { width: 20px; height: 20px; cursor: pointer; }
.align-middle { vertical-align: middle !important; }
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const checks = document.querySelectorAll('.step-check');
    const btnSubmit = document.getElementById('btnSubmit');
    const statusWarning = document.getElementById('statusWarning');

    function checkAllCompleted() {
        let allChecked = true;
        checks.forEach(c => {
            if (!c.checked) allChecked = false;
        });

        if (allChecked) {
            btnSubmit.disabled = false;
            btnSubmit.classList.remove('btn-secondary');
            btnSubmit.classList.add('btn-danger');
            statusWarning.className = 'alert alert-success';
            statusWarning.innerHTML = '<i class="fas fa-check-circle"></i> Đã đủ điều kiện để chốt thôi việc. Hành động này không thể hoàn tác!';
        } else {
            btnSubmit.disabled = true;
            btnSubmit.classList.remove('btn-danger');
            btnSubmit.classList.add('btn-secondary');
            statusWarning.className = 'alert alert-info';
            statusWarning.innerHTML = '<i class="fas fa-info-circle"></i> Vui lòng tick chọn ĐẦY ĐỦ 4 bước trên để hệ thống cho phép chốt trạng thái.';
        }
    }

    checks.forEach(c => c.addEventListener('change', checkAllCompleted));
});
</script>
