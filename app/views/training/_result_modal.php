<!-- MODAL ĐÁNH GIÁ & CẬP NHẬT KẾT QUẢ ĐÀO TẠO -->
<div class="modal fade" id="resultModal" tabindex="-1" aria-hidden="true" style="display: none; background: rgba(15,23,42,0.6); position: fixed; inset: 0; z-index: 1050; overflow-y: auto;">
    <div class="modal-dialog" style="margin: 50px auto; max-width: 550px; padding: 0 15px;">
        <div class="modal-content" style="background: var(--bg-card); border-radius: var(--radius-lg); border: none; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); overflow: hidden;">
            
            <div class="modal-header d-flex justify-content-between align-items-center" style="padding: 16px 24px; border-bottom: 1px solid var(--border); background: var(--bg-app);">
                <h4 style="font-size: 16px; font-weight: 800; color: var(--text-heading); margin: 0;">
                    <i class="fas fa-award text-warning"></i> Đánh giá Kết quả Đào tạo
                </h4>
                <button type="button" class="btn btn-ghost btn-sm" onclick="closeResultModal()" style="font-size: 18px; line-height: 1; padding: 4px 8px;">
                    &times;
                </button>
            </div>

            <form id="updateResultForm" method="POST" action="<?= BASE_URL ?>/training/updateResult">
                <input type="hidden" name="_csrf_token" value="<?= Session::generateCsrfToken() ?>">
                <input type="hidden" name="training_id" id="resultTrainingId" value="<?= $course->id ?>">
                <input type="hidden" name="employee_id" id="resultEmployeeId" value="">

                <div class="modal-body" style="padding: 24px;">
                    <!-- Thông tin học viên -->
                    <div class="d-flex align-items-center gap-3 p-3 mb-4 rounded" style="background: var(--bg-app); border: 1px solid var(--border);">
                        <div id="resultEmpAvatar" style="width: 44px; height: 44px; border-radius: 8px; background: var(--primary); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 18px;">
                            NV
                        </div>
                        <div>
                            <div id="resultEmpName" style="font-weight: 800; font-size: 14px; color: var(--text-heading);">Nguyễn Văn A</div>
                            <div style="font-size: 12px; color: var(--text-muted);">
                                Mã NV: <span id="resultEmpCode" class="fw-bold">NV001</span> • <span id="resultEmpDept">Phòng Kỹ thuật</span>
                            </div>
                        </div>
                    </div>

                    <!-- Trường Kết quả & Điểm số -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" style="font-weight: 600; font-size: 13px;">
                                Đánh giá Kết quả <span class="text-danger">*</span>
                            </label>
                            <select name="result" id="resultSelect" class="form-control" required onchange="handleResultChange(this.value)">
                                <option value="Passed">Đạt (Passed)</option>
                                <option value="Failed">Không đạt (Failed)</option>
                                <option value="Pending">Chưa đánh giá (Pending)</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label" style="font-weight: 600; font-size: 13px;">
                                Điểm số / Xếp loại
                            </label>
                            <input type="number" step="0.1" min="0" max="100" name="score" id="resultScore" class="form-control" placeholder="VD: 85.5">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label" style="font-weight: 600; font-size: 13px;">
                                Trạng thái tham gia
                            </label>
                            <select name="status" id="resultStatus" class="form-control">
                                <option value="Completed">Đã hoàn thành</option>
                                <option value="Attending">Đang theo học</option>
                                <option value="Registered">Mới đăng ký</option>
                                <option value="Dropped">Bỏ dở / Rút lui</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label" style="font-weight: 600; font-size: 13px;">
                                Ngày hoàn thành / Cấp
                            </label>
                            <input type="date" name="completed_date" id="resultCompletedDate" class="form-control" value="<?= date('Y-m-d') ?>">
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label" style="font-weight: 600; font-size: 13px;">
                                Số hiệu Chứng chỉ / Bằng cấp (nếu có)
                            </label>
                            <input type="text" name="certificate_no" id="resultCertNo" class="form-control" placeholder="VD: CERT-2026-HSE-0089">
                        </div>

                        <div class="col-md-12 mb-0">
                            <label class="form-label" style="font-weight: 600; font-size: 13px;">
                                Ghi chú / Nhận xét của Giảng viên
                            </label>
                            <textarea name="notes" id="resultNotes" class="form-control" rows="3" placeholder="Nhận xét ý thức tham gia, kỹ năng đạt được..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer d-flex justify-content-between align-items-center" style="padding: 14px 24px; border-top: 1px solid var(--border); background: var(--bg-app);">
                    <button type="button" class="btn btn-ghost btn-sm" onclick="closeResultModal()">Hủy bỏ</button>
                    <button type="submit" class="btn btn-primary btn-sm" style="padding: 8px 20px; font-weight: 700;">
                        <i class="fas fa-check"></i> Lưu kết quả đào tạo
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<script>
function openResultModal(data) {
    document.getElementById('resultEmployeeId').value = data.employee_id;
    document.getElementById('resultEmpName').innerText = data.full_name || 'Học viên';
    document.getElementById('resultEmpCode').innerText = data.emp_code || '';
    document.getElementById('resultEmpDept').innerText = data.dept_name || 'N/A';

    const avatarBox = document.getElementById('resultEmpAvatar');
    if (data.full_name) {
        avatarBox.innerText = data.full_name.charAt(0).toUpperCase();
    }

    document.getElementById('resultSelect').value = data.result || 'Pending';
    document.getElementById('resultScore').value = data.score !== null && data.score !== undefined ? data.score : '';
    document.getElementById('resultStatus').value = data.status || 'Completed';
    document.getElementById('resultCompletedDate').value = data.completed_date || '<?= date('Y-m-d') ?>';
    document.getElementById('resultCertNo').value = data.certificate_no || '';
    document.getElementById('resultNotes').value = data.notes || '';

    const modal = document.getElementById('resultModal');
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeResultModal() {
    const modal = document.getElementById('resultModal');
    modal.style.display = 'none';
    document.body.style.overflow = 'auto';
}

function handleResultChange(val) {
    const statusSelect = document.getElementById('resultStatus');
    if (val === 'Passed') {
        statusSelect.value = 'Completed';
    } else if (val === 'Failed') {
        statusSelect.value = 'Completed';
    }
}
</script>
