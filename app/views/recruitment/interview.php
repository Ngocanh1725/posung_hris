<?php /** View: recruitment/interview.php – Form phỏng vấn / cập nhật trạng thái ứng viên */ ?>

<div style="max-width:800px;">
    <!-- Thông tin ứng viên -->
    <div class="panel mb-4">
        <div class="panel-header"><h3><i class="fas fa-user"></i> Thông tin Ứng viên</h3></div>
        <div class="panel-body">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <div><strong>Họ tên:</strong> <?= htmlspecialchars($candidate->full_name) ?></div>
                <div><strong>SĐT:</strong> <?= htmlspecialchars($candidate->phone ?? '-') ?></div>
                <div><strong>Email:</strong> <?= htmlspecialchars($candidate->email ?? '-') ?></div>
                <div><strong>Học vấn:</strong> <?= htmlspecialchars($candidate->highest_degree ?? '-') ?> – <?= htmlspecialchars($candidate->major ?? '') ?></div>
                <div><strong>Kinh nghiệm:</strong> <?= $candidate->years_experience ?> năm</div>
                <div><strong>Mã YCTD:</strong> <?= htmlspecialchars($candidate->request_code ?? 'Không gắn') ?></div>
                <div><strong>Vị trí ứng tuyển:</strong> <?= htmlspecialchars($candidate->pos_title ?? '-') ?></div>
                <div><strong>Lương mong muốn:</strong> <?= $candidate->expected_salary ? number_format($candidate->expected_salary, 0, ',', '.') . ' đ' : '-' ?></div>
            </div>
        </div>
    </div>

    <!-- Form cập nhật -->
    <div class="panel">
        <div class="panel-header"><h3><i class="fas fa-clipboard-check"></i> Phỏng vấn & Cập nhật Trạng thái</h3></div>
        <div class="panel-body">
            <form action="<?= BASE_URL ?>/recruitment/interview/<?= $candidate->id ?>" method="POST">
                <!-- Trạng thái mới -->
                <div class="form-group mb-3">
                    <label>Cập nhật Trạng thái <span class="text-danger">*</span></label>
                    <select name="new_status" class="form-control" required onchange="toggleSections(this.value)">
                        <option value="Screening" <?= $candidate->status === 'Screening' ? 'selected' : '' ?>>Sàng lọc (Screening)</option>
                        <option value="Interview_Scheduled" <?= $candidate->status === 'Interview_Scheduled' ? 'selected' : '' ?>>Đã hẹn Phỏng vấn</option>
                        <option value="Interviewed" <?= $candidate->status === 'Interviewed' ? 'selected' : '' ?>>Đã Phỏng vấn</option>
                        <option value="Offer" <?= $candidate->status === 'Offer' ? 'selected' : '' ?>>Offer (Mời nhận việc)</option>
                        <option value="Rejected" <?= $candidate->status === 'Rejected' ? 'selected' : '' ?>>Từ chối</option>
                        <option value="Withdrawn">Rút hồ sơ</option>
                    </select>
                </div>

                <!-- Section: Phỏng vấn -->
                <div id="interviewSection" style="border:1px solid var(--border); border-radius:8px; padding:16px; margin-bottom:16px;">
                    <h4 style="margin-bottom:12px; font-size:0.9rem;"><i class="fas fa-comments"></i> Thông tin Phỏng vấn</h4>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                        <div class="form-group">
                            <label>Ngày giờ phỏng vấn</label>
                            <input type="datetime-local" name="interview_date" class="form-control" 
                                   value="<?= $candidate->interview_date ? date('Y-m-d\TH:i', strtotime($candidate->interview_date)) : '' ?>">
                        </div>
                        <div class="form-group">
                            <label>Địa điểm</label>
                            <input type="text" name="interview_location" class="form-control" 
                                   value="<?= htmlspecialchars($candidate->interview_location ?? '') ?>" placeholder="VD: Văn phòng HQ, Phòng họp A">
                        </div>
                        <div class="form-group">
                            <label>Người phỏng vấn</label>
                            <input type="text" name="interviewer_name" class="form-control" 
                                   value="<?= htmlspecialchars($candidate->interviewer_name ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label>Kết quả PV</label>
                            <select name="interview_result" class="form-control">
                                <option value="">-- Chưa có --</option>
                                <option value="Pass" <?= ($candidate->interview_result ?? '') === 'Pass' ? 'selected' : '' ?>>Đạt (Pass)</option>
                                <option value="Fail" <?= ($candidate->interview_result ?? '') === 'Fail' ? 'selected' : '' ?>>Không đạt (Fail)</option>
                                <option value="Deferred" <?= ($candidate->interview_result ?? '') === 'Deferred' ? 'selected' : '' ?>>Hoãn (Deferred)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Điểm PV (thang 10)</label>
                            <input type="number" name="interview_score" class="form-control" min="0" max="10" step="0.5"
                                   value="<?= $candidate->interview_score ?? '' ?>">
                        </div>
                    </div>
                    <div class="form-group" style="margin-top:12px;">
                        <label>Nhận xét phỏng vấn</label>
                        <textarea name="interviewer_notes" class="form-control" rows="3"><?= htmlspecialchars($candidate->interviewer_notes ?? '') ?></textarea>
                    </div>
                </div>

                <!-- Section: Offer -->
                <div id="offerSection" style="border:1px solid var(--border); border-radius:8px; padding:16px; margin-bottom:16px; display:none;">
                    <h4 style="margin-bottom:12px; font-size:0.9rem;"><i class="fas fa-handshake"></i> Thông tin Offer</h4>
                    <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px;">
                        <div class="form-group">
                            <label>Mức lương Offer (VNĐ)</label>
                            <input type="number" name="offer_salary" class="form-control" value="<?= $candidate->offer_salary ?? '' ?>">
                        </div>
                        <div class="form-group">
                            <label>Ngày gửi Offer</label>
                            <input type="date" name="offer_date" class="form-control" value="<?= $candidate->offer_date ?? date('Y-m-d') ?>">
                        </div>
                        <div class="form-group">
                            <label>Ngày dự kiến bắt đầu</label>
                            <input type="date" name="start_date" class="form-control" value="<?= $candidate->start_date ?? '' ?>">
                        </div>
                    </div>
                    <div class="form-group" style="margin-top:12px;">
                        <label>Quyết định cuối cùng</label>
                        <select name="final_decision" class="form-control">
                            <option value="">-- Chưa quyết --</option>
                            <option value="Hire" <?= ($candidate->final_decision ?? '') === 'Hire' ? 'selected' : '' ?>>Tuyển (Hire)</option>
                            <option value="Reject" <?= ($candidate->final_decision ?? '') === 'Reject' ? 'selected' : '' ?>>Không tuyển (Reject)</option>
                            <option value="On_Hold" <?= ($candidate->final_decision ?? '') === 'On_Hold' ? 'selected' : '' ?>>Chờ (On Hold)</option>
                        </select>
                    </div>
                </div>

                <!-- Section: Từ chối -->
                <div id="rejectSection" style="display:none;">
                    <div class="form-group mb-3">
                        <label>Lý do từ chối</label>
                        <textarea name="rejection_reason" class="form-control" rows="2" placeholder="Lý do từ chối ứng viên..."><?= htmlspecialchars($candidate->rejection_reason ?? '') ?></textarea>
                    </div>
                </div>

                <div style="margin-top:20px; display:flex; gap:12px;">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Lưu cập nhật</button>
                    <a href="<?= BASE_URL ?>/recruitment/candidates" class="btn btn-ghost">Quay lại</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleSections(status) {
    document.getElementById('interviewSection').style.display = 
        ['Interview_Scheduled','Interviewed','Offer'].includes(status) ? 'block' : 'none';
    document.getElementById('offerSection').style.display = 
        status === 'Offer' ? 'block' : 'none';
    document.getElementById('rejectSection').style.display = 
        status === 'Rejected' ? 'block' : 'none';
}
// Trigger on load
toggleSections(document.querySelector('[name="new_status"]').value);
</script>
