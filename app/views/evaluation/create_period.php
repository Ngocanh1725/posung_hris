<?php
/**
 * View: evaluation/create_period.php – Tạo chu kỳ đánh giá KPI / Năng lực mới
 */
?>

<!-- BREADCRUMB -->
<div class="breadcrumb-nav mb-3" style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: var(--text-muted);">
    <a href="<?= BASE_URL ?>/evaluation" style="color: var(--primary); text-decoration: none;">
        <i class="fas fa-award"></i> Quản lý Đánh giá KPI
    </a>
    <i class="fas fa-chevron-right" style="font-size: 10px;"></i>
    <span>Tạo Chu kỳ Đánh giá Mới</span>
</div>

<div class="card" style="max-width: 900px; margin: 0 auto; box-shadow: 0 4px 20px rgba(0,0,0,0.06);">
    <div class="card-header" style="background: linear-gradient(135deg, rgba(79, 70, 229, 0.08), rgba(99, 102, 241, 0.02)); border-bottom: 1px solid var(--border); padding: 18px 24px;">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-3">
                <div style="width: 44px; height: 44px; border-radius: 12px; background: linear-gradient(135deg, #4f46e5, #6366f1); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 20px; box-shadow: 0 4px 10px rgba(79,70,229,0.3);">
                    <i class="fas fa-calendar-plus"></i>
                </div>
                <div>
                    <h3 style="margin: 0; font-size: 18px; font-weight: 700; color: var(--text);">Khởi Tạo Chu Kỳ Đánh Giá Năng Lực / KPI</h3>
                    <p style="margin: 4px 0 0; font-size: 13px; color: var(--text-muted);">Thiết lập chu kỳ định kỳ, liên kết mẫu tiêu chí và chỉ định phạm vi phòng ban</p>
                </div>
            </div>
            <a href="<?= BASE_URL ?>/evaluation" class="btn btn-ghost btn-sm" style="border: 1px solid var(--border);">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>

    <div class="card-body" style="padding: 28px 24px;">
        <form action="<?= BASE_URL ?>/evaluation/storePeriod" method="POST" id="createPeriodForm">
            <input type="hidden" name="_csrf_token" value="<?= Session::generateCsrfToken() ?>">

            <div class="row g-3">
                <!-- Tên chu kỳ -->
                <div class="col-12">
                    <label class="form-label" style="font-weight: 600; font-size: 13px;">
                        Tên Chu kỳ Đánh giá <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text" style="background: var(--bg-hover); border-color: var(--border);">
                            <i class="fas fa-tag text-muted"></i>
                        </span>
                        <input type="text" name="name" class="form-control" required
                               placeholder="Ví dụ: Đánh giá Hiệu suất Q3/2026 hoặc Đánh giá Thử việc Tháng 10/2026..."
                               style="font-size: 14px; font-weight: 500;">
                    </div>
                </div>

                <!-- Chọn Mẫu Đánh Giá -->
                <div class="col-md-7">
                    <label class="form-label" style="font-weight: 600; font-size: 13px;">
                        Mẫu Tiêu chí & Thang điểm (Template) <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text" style="background: var(--bg-hover); border-color: var(--border);">
                            <i class="fas fa-clipboard-list text-muted"></i>
                        </span>
                        <select name="template_id" id="templateSelect" class="form-select" required onchange="updateTemplatePreview()">
                            <option value="">-- Chọn Mẫu Đánh giá áp dụng --</option>
                            <?php foreach ($templates as $t): ?>
                                <option value="<?= $t['id'] ?>" 
                                        data-criteria="<?= $t['criteria_count'] ?>" 
                                        data-weight="<?= $t['total_weight'] ?>" 
                                        data-applies="<?= htmlspecialchars($t['applies_to']) ?>"
                                        data-desc="<?= htmlspecialchars($t['description'] ?? '') ?>">
                                    <?= htmlspecialchars($t['name']) ?> (<?= $t['criteria_count'] ?> tiêu chí - <?= $t['total_weight'] ?>%)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-text" style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">
                        Chưa có mẫu phù hợp? <a href="<?= BASE_URL ?>/evaluation/templates" target="_blank" style="color: var(--primary);">Quản lý & tạo mẫu mới tại đây</a>.
                    </div>
                </div>

                <!-- Phạm vi Phòng ban -->
                <div class="col-md-5">
                    <label class="form-label" style="font-weight: 600; font-size: 13px;">
                        Phạm vi Áp dụng
                    </label>
                    <div class="input-group">
                        <span class="input-group-text" style="background: var(--bg-hover); border-color: var(--border);">
                            <i class="fas fa-building text-muted"></i>
                        </span>
                        <select name="department_id" class="form-select">
                            <option value="">-- Toàn bộ Công ty (Tất cả Phòng ban) --</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?= $dept->id ?>">
                                    [<?= htmlspecialchars($dept->dept_code) ?>] <?= htmlspecialchars($dept->dept_name) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-text" style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">
                        Chọn phòng ban nếu chu kỳ chỉ dành riêng cho bộ phận đó.
                    </div>
                </div>

                <!-- Box Preview Template Info -->
                <div class="col-12" id="templatePreviewCard" style="display: none;">
                    <div style="background: rgba(79, 70, 229, 0.04); border: 1px dashed rgba(79, 70, 229, 0.3); border-radius: 8px; padding: 12px 16px; font-size: 13px;">
                        <div class="d-flex align-items-center gap-2 mb-1" style="font-weight: 600; color: var(--primary);">
                            <i class="fas fa-info-circle"></i> Thông tin Mẫu đã chọn:
                        </div>
                        <div id="previewContent" style="color: var(--text-muted);">
                            <!-- Dynamic Content by JS -->
                        </div>
                    </div>
                </div>

                <!-- Thời gian bắt đầu & kết thúc -->
                <div class="col-md-4">
                    <label class="form-label" style="font-weight: 600; font-size: 13px;">
                        Từ ngày (Start Date) <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text" style="background: var(--bg-hover); border-color: var(--border);">
                            <i class="fas fa-calendar-alt text-muted"></i>
                        </span>
                        <input type="date" name="start_date" class="form-control" required value="<?= date('Y-m-01') ?>">
                    </div>
                </div>

                <div class="col-md-4">
                    <label class="form-label" style="font-weight: 600; font-size: 13px;">
                        Đến ngày (End Date) <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text" style="background: var(--bg-hover); border-color: var(--border);">
                            <i class="fas fa-calendar-check text-muted"></i>
                        </span>
                        <input type="date" name="end_date" class="form-control" required value="<?= date('Y-m-t') ?>">
                    </div>
                </div>

                <!-- Trạng thái chu kỳ -->
                <div class="col-md-4">
                    <label class="form-label" style="font-weight: 600; font-size: 13px;">
                        Trạng thái kích hoạt <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text" style="background: var(--bg-hover); border-color: var(--border);">
                            <i class="fas fa-toggle-on text-muted"></i>
                        </span>
                        <select name="status" class="form-select" required>
                            <option value="Active" selected>🟢 Đang diễn ra (Active - Cho phép đánh giá)</option>
                            <option value="Draft">🟡 Bản nháp (Draft - Chuẩn bị nội dung)</option>
                            <option value="Closed">🔒 Đã đóng / Khóa (Closed)</option>
                        </select>
                    </div>
                </div>

                <!-- Cấu hình Đánh giá 360° -->
                <div class="col-12">
                    <div style="background: linear-gradient(135deg, rgba(79, 70, 229, 0.05), rgba(99, 102, 241, 0.02)); border: 1px solid rgba(79, 70, 229, 0.2); border-radius: 10px; padding: 16px 20px;">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <i class="fas fa-sync-alt text-primary" style="font-size: 16px;"></i>
                            <strong style="color: var(--text); font-size: 14px;">Cấu hình Đánh giá 360 Độ & Mục tiêu KRA (Performance 360°)</strong>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="form-check form-switch pt-2">
                                    <input class="form-check-input" type="checkbox" role="switch" id="allowSelfReview" name="allow_self_review" value="1" checked>
                                    <label class="form-check-label" for="allowSelfReview" style="font-size: 13px; font-weight: 600;">
                                        Cho phép Nhân viên Tự đánh giá (Self Review)
                                    </label>
                                    <div class="text-muted" style="font-size: 11px;">Nhân viên tự chấm điểm và nộp kết quả thực tế trên KRA</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check form-switch pt-2">
                                    <input class="form-check-input" type="checkbox" role="switch" id="allowPeerReview" name="allow_peer_review" value="1" checked>
                                    <label class="form-check-label" for="allowPeerReview" style="font-size: 13px; font-weight: 600;">
                                        Cho phép Đồng nghiệp Đánh giá (Peer Review)
                                    </label>
                                    <div class="text-muted" style="font-size: 11px;">Kích hoạt đánh giá chéo đa chiều từ đồng nghiệp cùng phòng</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" style="font-weight: 600; font-size: 12px;">
                                    Số lượng Peer Reviewers tối đa
                                </label>
                                <input type="number" name="peer_review_count" class="form-control form-control-sm" value="2" min="1" max="5">
                                <div class="text-muted" style="font-size: 11px;">Số đồng nghiệp HR sẽ chỉ định cho mỗi nhân sự (Mặc định 2)</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ghi chú / Hướng dẫn -->
                <div class="col-12">
                    <label class="form-label" style="font-weight: 600; font-size: 13px;">
                        Hướng dẫn & Lưu ý cho Quản lý / Nhân viên
                    </label>
                    <textarea name="notes" class="form-control" rows="3"
                              placeholder="Nhập hướng dẫn chấm điểm, hạn chót hoàn thành tự đánh giá hoặc thông điệp từ Ban Giám đốc..."></textarea>
                </div>
            </div>

            <!-- BUTTONS -->
            <div class="d-flex justify-content-end gap-2 mt-4 pt-3" style="border-top: 1px solid var(--border);">
                <a href="<?= BASE_URL ?>/evaluation" class="btn btn-ghost" style="border: 1px solid var(--border);">
                    <i class="fas fa-times"></i> Hủy bỏ
                </a>
                <button type="submit" class="btn btn-primary" style="padding: 9px 24px; font-weight: 600; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);">
                    <i class="fas fa-check-circle"></i> Khởi Tạo Chu Kỳ Ngay
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function updateTemplatePreview() {
    const select = document.getElementById('templateSelect');
    const previewCard = document.getElementById('templatePreviewCard');
    const previewContent = document.getElementById('previewContent');

    if (!select.value) {
        previewCard.style.display = 'none';
        return;
    }

    const opt = select.options[select.selectedIndex];
    const criteriaCount = opt.getAttribute('data-criteria');
    const totalWeight = opt.getAttribute('data-weight');
    const applies = opt.getAttribute('data-applies');
    const desc = opt.getAttribute('data-desc');

    let weightBadge = totalWeight == 100 
        ? '<span class="badge bg-success text-white"><i class="fas fa-check"></i> Đủ 100% trọng số</span>'
        : '<span class="badge bg-warning text-dark"><i class="fas fa-exclamation-triangle"></i> Trọng số: ' + totalWeight + '% (chưa đạt 100%)</span>';

    previewContent.innerHTML = `
        <div style="display: flex; gap: 16px; flex-wrap: wrap; margin-bottom: 4px;">
            <div><strong>Số lượng tiêu chí:</strong> ${criteriaCount} tiêu chí</div>
            <div><strong>Tổng trọng số:</strong> ${weightBadge}</div>
            <div><strong>Đối tượng mặc định:</strong> ${applies}</div>
        </div>
        ${desc ? `<div style="margin-top: 4px; font-style: italic;">"${desc}"</div>` : ''}
    `;

    previewCard.style.display = 'block';
}

document.addEventListener('DOMContentLoaded', function() {
    updateTemplatePreview();
});
</script>
