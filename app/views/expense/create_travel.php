<?php
/**
 * ============================================================
 *  POSUNG HRIS – View: expense/create_travel.php
 * ============================================================
 *  Lập Đề xuất Đi công tác (Travel Request Form)
 * ============================================================
 */
?>
<div class="content-wrapper">
    <!-- TIÊU ĐỀ & ĐIỀU HƯỚNG -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1 fw-bold text-dark"><i class="fas fa-calendar-plus text-primary me-2"></i>Lập Đề xuất Đi công tác</h2>
            <p class="text-muted mb-0">Đăng ký kế hoạch công tác dự án, ước tính kinh phí và đề xuất tạm ứng</p>
        </div>
        <div>
            <a href="<?= BASE_URL ?>/expense?tab=travel" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Quay lại
            </a>
        </div>
    </div>

    <form action="<?= BASE_URL ?>/expense/storeTravel" method="POST" id="travelForm">
        <input type="hidden" name="_csrf_token" value="<?= Session::generateCsrfToken() ?>">

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
                    <h5 class="fw-bold mb-3 text-dark border-bottom pb-2">
                        <i class="fas fa-user-tie text-primary me-2"></i>1. Nhân sự & Dự án công tác
                    </h5>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Cán bộ đi công tác <span class="text-danger">*</span></label>
                            <select name="employee_id" class="form-select" required>
                                <option value="">-- Chọn nhân viên --</option>
                                <?php foreach ($employees as $emp): ?>
                                    <option value="<?= $emp->id ?>" <?= $selectedEmpId == $emp->id ? 'selected' : '' ?>>
                                        <?= h($emp->full_name) ?> (<?= h($emp->emp_code) ?> - <?= h($emp->dept_name ?? 'N/A') ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Dự án / Công trường liên quan</label>
                            <select name="project_id" id="projectSelect" class="form-select" onchange="autoFillProjectLocation()">
                                <option value="">-- Không theo dự án (Công tác chung) --</option>
                                <?php foreach ($projects as $prj): ?>
                                    <option value="<?= $prj->id ?>" data-location="<?= h($prj->location ?? '') ?>" <?= $selectedProjId == $prj->id ? 'selected' : '' ?>>
                                        <?= h($prj->project_name) ?> (<?= h($prj->location ?? 'N/A') ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Mục đích chuyến công tác <span class="text-danger">*</span></label>
                        <textarea name="purpose" class="form-control" rows="2" placeholder="Ví dụ: Chỉ đạo nghiệm thu phòng sạch, xử lý sự cố thiết bị tại công trường, làm việc với chủ đầu tư..." required></textarea>
                    </div>

                    <h5 class="fw-bold mb-3 text-dark border-bottom pb-2 mt-4">
                        <i class="fas fa-map-location-dot text-primary me-2"></i>2. Lộ trình & Lịch trình
                    </h5>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Địa điểm xuất phát <span class="text-danger">*</span></label>
                            <input type="text" name="from_location" class="form-control" value="Hà Nội (Trụ sở POSUNG)" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Địa điểm đến (Công trường / Đối tác) <span class="text-danger">*</span></label>
                            <input type="text" name="to_location" id="toLocationInput" class="form-control" placeholder="Ví dụ: Công trường Samsung SEVM Bắc Ninh" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Ngày bắt đầu đi <span class="text-danger">*</span></label>
                            <input type="date" name="departure_date" id="deptDate" class="form-control" value="<?= date('Y-m-d') ?>" required onchange="calculateDuration()">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Ngày kết thúc về <span class="text-danger">*</span></label>
                            <input type="date" name="return_date" id="retDate" class="form-control" value="<?= date('Y-m-d', strtotime('+3 days')) ?>" required onchange="calculateDuration()">
                        </div>
                    </div>

                    <div class="alert alert-light border rounded-3 py-2 px-3 mb-3 d-flex align-items-center justify-content-between">
                        <span class="text-muted"><i class="fas fa-clock me-1"></i> Thời gian dự kiến:</span>
                        <strong id="durationDisplay" class="text-primary fs-6">4 ngày</strong>
                    </div>

                    <h5 class="fw-bold mb-3 text-dark border-bottom pb-2 mt-4">
                        <i class="fas fa-coins text-primary me-2"></i>3. Dự toán Kinh phí & Phương tiện
                    </h5>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Ngân sách dự kiến (VNĐ)</label>
                            <div class="input-group">
                                <input type="text" name="estimated_budget" class="form-control form-control-lg fw-bold text-dark" placeholder="Ví dụ: 8,500,000" oninput="formatCurrency(this)">
                                <span class="input-group-text bg-light fw-bold">₫</span>
                            </div>
                            <small class="text-muted">Bao gồm vé xe, phòng nghỉ, ăn uống, tiếp khách.</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Đề xuất Tạm ứng (VNĐ)</label>
                            <div class="input-group">
                                <input type="text" name="advance_amount" class="form-control form-control-lg fw-bold text-primary" placeholder="Ví dụ: 5,000,000" oninput="formatCurrency(this)">
                                <span class="input-group-text bg-light fw-bold">₫</span>
                            </div>
                            <small class="text-muted">Khoản tiền nhận trước để chi trả trong chuyến đi.</small>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Phương tiện di chuyển</label>
                            <select name="transport_type" class="form-select">
                                <option value="Xe ô tô công ty">Xe ô tô công ty</option>
                                <option value="Máy bay">Máy bay</option>
                                <option value="Tàu hỏa">Tàu hỏa</option>
                                <option value="Xe khách / Limousine">Xe khách / Limousine</option>
                                <option value="Phương tiện cá nhân">Phương tiện cá nhân</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Hình thức lưu trú</label>
                            <input type="text" name="accommodation" class="form-control" value="Khách sạn tiêu chuẩn">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Ghi chú bổ sung</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Ghi chú người đi cùng, yêu cầu hỗ trợ từ văn phòng..."></textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="<?= BASE_URL ?>/expense?tab=travel" class="btn btn-light border px-4">Hủy bỏ</a>
                    <button type="submit" class="btn btn-primary px-4 fw-semibold">
                        <i class="fas fa-paper-plane me-1"></i> Gửi Đề xuất Phê duyệt
                    </button>
                </div>
            </div>

            <!-- CỘT PHẢI: QUY ĐỊNH & HƯỚNG DẪN -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-3 p-4 bg-white sticky-top" style="top: 20px;">
                    <h5 class="fw-bold mb-3 text-dark border-bottom pb-2">
                        <i class="fas fa-shield-halved text-primary me-2"></i>Quy định Công tác Phí
                    </h5>

                    <div class="small text-muted mb-3">
                        Hệ thống quy chuẩn tài chính cho cán bộ kỹ sư POSUNG thi công công trường:
                    </div>

                    <div class="list-group list-group-flush small">
                        <div class="list-group-item px-0 py-2 border-0">
                            <strong class="text-dark d-block"><i class="fas fa-bed text-primary me-1"></i> Tiêu chuẩn Khách sạn:</strong>
                            Tối đa 800.000đ/đêm (Hà Nội, TP.HCM) và 500.000đ/đêm tại các tỉnh thành khác.
                        </div>
                        <div class="list-group-item px-0 py-2 border-0">
                            <strong class="text-dark d-block"><i class="fas fa-utensils text-success me-1"></i> Phụ cấp ăn uống:</strong>
                            150.000đ - 250.000đ/ngày tùy theo địa bàn công trường dự án.
                        </div>
                        <div class="list-group-item px-0 py-2 border-0">
                            <strong class="text-dark d-block"><i class="fas fa-receipt text-warning me-1"></i> Chứng từ Hóa đơn:</strong>
                            Bắt buộc xuất hóa đơn VAT điện tử mang tên <strong>CÔNG TY TNHH CƠ ĐIỆN PO SUNG</strong>.
                        </div>
                        <div class="list-group-item px-0 py-2 border-0">
                            <strong class="text-dark d-block"><i class="fas fa-calendar-check text-info me-1"></i> Thời hạn quyết toán:</strong>
                            Trong vòng <strong>05 ngày làm việc</strong> sau khi kết thúc đợt công tác phải nộp Bảng thanh quyết toán.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function formatCurrency(input) {
    let value = input.value.replace(/\D/g, '');
    if (value === '') {
        input.value = '';
        return;
    }
    input.value = new Intl.NumberFormat('vi-VN').format(value);
}

function autoFillProjectLocation() {
    const sel = document.getElementById('projectSelect');
    const opt = sel.options[sel.selectedIndex];
    if (opt && opt.value) {
        const loc = opt.getAttribute('data-location');
        if (loc) {
            document.getElementById('toLocationInput').value = 'Công trường dự án ' + opt.text.split('(')[0].trim() + ' (' + loc + ')';
        }
    }
}

function calculateDuration() {
    const d1 = document.getElementById('deptDate').value;
    const d2 = document.getElementById('retDate').value;
    if (d1 && d2) {
        const diff = Math.round((new Date(d2) - new Date(d1)) / (1000 * 60 * 60 * 24)) + 1;
        document.getElementById('durationDisplay').innerText = (diff > 0 ? diff : 1) + ' ngày';
    }
}

document.addEventListener('DOMContentLoaded', calculateDuration);
</script>
