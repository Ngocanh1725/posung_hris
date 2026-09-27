<?php /** View: recruitment/interview.php – Form phỏng vấn / cập nhật trạng thái ứng viên */ ?>

<div style="max-width:800px;">
    <!-- Thông tin ứng viên -->
    <div class="panel mb-4">
        <div class="panel-header"><h3><i class="fas fa-user"></i> Thông tin Ứng viên</h3></div>
        <div class="panel-body">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <div><strong>Họ tên:</strong> <?= h($candidate->full_name) ?></div>
                <div><strong>SĐT:</strong> <?= h($candidate->phone ?? '-') ?></div>
                <div><strong>Email:</strong> <?= h($candidate->email ?? '-') ?></div>
                <div><strong>Học vấn:</strong> <?= h($candidate->highest_degree ?? '-') ?> – <?= h($candidate->major ?? '') ?></div>
                <div><strong>Kinh nghiệm:</strong> <?= (int)($candidate->experience_years ?? 0) ?> năm</div>
                <div><strong>Mã YCTD:</strong> <?= h($candidate->request_code ?? 'Không gắn') ?></div>
                <div><strong>Vị trí ứng tuyển:</strong> <?= h($candidate->pos_title ?? '-') ?></div>
                <div><strong>Lương mong muốn:</strong> <?= !empty($candidate->expected_salary ?? null) ? number_format($candidate->expected_salary, 0, ',', '.') . ' đ' : '-' ?></div>
            </div>
        </div>
    </div>

    <!-- Form cập nhật -->
    <div class="panel">
        <div class="panel-header"><h3><i class="fas fa-clipboard-check"></i> Phỏng vấn & Cập nhật Trạng thái</h3></div>
        <div class="panel-body">
            <form action="<?= BASE_URL ?>/recruitment/interview/<?= $candidate->id ?>" method="POST">
                <input type="hidden" name="_csrf_token" value="<?= Session::generateCsrfToken() ?>">
                <!-- Trạng thái mới -->
                <div class="form-group mb-3">
                    <label>Cập nhật Trạng thái <span class="text-danger">*</span></label>
                    <select name="new_status" class="form-control" required onchange="toggleSections(this.value)">
                        <option value="new" <?= $candidate->status === 'new' ? 'selected' : '' ?>>Mới nhận (New)</option>
                        <option value="screening" <?= $candidate->status === 'screening' ? 'selected' : '' ?>>Sàng lọc (Screening)</option>
                        <option value="interviewing" <?= $candidate->status === 'interviewing' ? 'selected' : '' ?>>Phỏng vấn (Interviewing)</option>
                        <option value="offered" <?= $candidate->status === 'offered' ? 'selected' : '' ?>>Đề xuất (Offered)</option>
                        <option value="hired" <?= $candidate->status === 'hired' ? 'selected' : '' ?>>Đã tuyển (Hired)</option>
                        <option value="rejected" <?= $candidate->status === 'rejected' ? 'selected' : '' ?>>Từ chối (Rejected)</option>
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
                                   value="<?= h($candidate->interview_location ?? '') ?>" placeholder="VD: Văn phòng HQ, Phòng họp A">
                        </div>
                        <div class="form-group">
                            <label>Người phỏng vấn</label>
                            <input type="text" name="interviewer_name" class="form-control" 
                                   value="<?= h($candidate->interviewer_name ?? '') ?>">
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
                        <textarea name="interviewer_notes" class="form-control" rows="3"><?= h($candidate->interviewer_notes ?? '') ?></textarea>
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
                <div id="rejectSection" style="display:none; border:1px solid var(--border); border-radius:8px; padding:16px; margin-bottom:16px;">
                    <h4 style="margin-bottom:12px; font-size:0.9rem; color:#e11d48;"><i class="fas fa-times-circle"></i> Thông tin Từ chối</h4>
                    <div class="form-group mb-3">
                        <label>Lý do từ chối</label>
                        <select name="rejection_reason_select" class="form-control mb-2" onchange="if(this.value!=='Other'){ document.getElementById('rejection_reason').value = this.value; } else { document.getElementById('rejection_reason').value = ''; document.getElementById('rejection_reason').focus(); }">
                            <option value="">-- Chọn lý do --</option>
                            <option value="Không phù hợp kinh nghiệm">Không phù hợp kinh nghiệm</option>
                            <option value="Không đạt yêu cầu kỹ năng (Test/PV)">Không đạt yêu cầu kỹ năng (Test/PV)</option>
                            <option value="Mức lương mong muốn quá cao">Mức lương mong muốn quá cao</option>
                            <option value="Không phù hợp văn hóa công ty">Không phù hợp văn hóa công ty</option>
                            <option value="Ứng viên từ chối offer">Ứng viên từ chối offer</option>
                            <option value="Ứng viên không đến phỏng vấn">Ứng viên không đến phỏng vấn</option>
                            <option value="Other">Khác (Tự nhập dưới đây)</option>
                        </select>
                        <textarea name="rejection_reason" id="rejection_reason" class="form-control" rows="2" placeholder="Nhập lý do từ chối..."><?= h($candidate->rejection_reason ?? '') ?></textarea>
                    </div>
                </div>

                <!-- Section: Thợ hàn 6G -->
                <div id="welderSection" style="border:1px solid var(--border); border-radius:8px; padding:16px; margin-bottom:16px; background:#f0f9ff; display:none;">
                    <h4 style="margin-bottom:12px; font-size:0.9rem; color:#0369a1;"><i class="fas fa-fire"></i> Đánh giá Thợ Hàn 6G (UT/RT)</h4>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                        <div class="form-group">
                            <label>Điểm thực hành (0 - 100)</label>
                            <?php 
                                $test6g = json_decode($candidate->test_6g ?? '{}', true) ?: []; 
                            ?>
                            <input type="number" name="test_6g_score" class="form-control" min="0" max="100" value="<?= $test6g['score'] ?? '' ?>">
                        </div>
                        <div class="form-group">
                            <label>Kết quả siêu âm/chụp chiếu (UT/RT)</label>
                            <select name="test_6g_utrt" class="form-control">
                                <option value="">-- Chưa đánh giá --</option>
                                <option value="Pass" <?= ($test6g['utrt'] ?? '') === 'Pass' ? 'selected' : '' ?>>Đạt (Pass)</option>
                                <option value="Fail" <?= ($test6g['utrt'] ?? '') === 'Fail' ? 'selected' : '' ?>>Không đạt (Fail)</option>
                            </select>
                        </div>
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
        ['interviewing','offered','hired'].includes(status) ? 'block' : 'none';
    document.getElementById('offerSection').style.display = 
        ['offered','hired'].includes(status) ? 'block' : 'none';
    document.getElementById('rejectSection').style.display = 
        status === 'rejected' ? 'block' : 'none';

    // Welder Section (Thợ hàn)
    const isWelder = <?= stripos($candidate->pos_title ?? '', 'hàn') !== false || stripos($candidate->pos_title ?? '', 'Welder') !== false ? 'true' : 'false' ?>;
    if (isWelder) {
        document.getElementById('welderSection').style.display = 
            ['interviewing','offered','hired'].includes(status) ? 'block' : 'none';
    }
}
// Trigger on load
toggleSections(document.querySelector('[name="new_status"]').value);
</script>
