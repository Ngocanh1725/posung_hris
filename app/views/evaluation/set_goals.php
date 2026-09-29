<?php
/**
 * View: evaluation/set_goals.php – Thiết lập Mục tiêu KRA & Trọng số
 */

$currentGoalsCount = count($currentGoals);
?>

<!-- BREADCRUMB -->
<div class="breadcrumb-nav mb-3 d-flex align-items-center gap-2" style="font-size: 13px; color: var(--text-muted);">
    <a href="<?= BASE_URL ?>/evaluation" class="text-decoration-none" style="color: var(--primary);">
        <i class="fas fa-award"></i> Quản lý Đánh giá KPI
    </a>
    <i class="fas fa-chevron-right" style="font-size: 10px;"></i>
    <a href="<?= BASE_URL ?>/evaluation/goals/<?= $period->id ?>" class="text-decoration-none" style="color: var(--primary);">
        Mục tiêu KRA: <?= htmlspecialchars($period->name) ?>
    </a>
    <i class="fas fa-chevron-right" style="font-size: 10px;"></i>
    <span>Thiết lập Goals: <?= htmlspecialchars($employee->full_name) ?></span>
</div>

<!-- EMPLOYEE & PERIOD HEADER -->
<div class="card mb-4" style="border: 1px solid var(--border); box-shadow: 0 4px 18px rgba(0,0,0,0.04); border-radius: 12px;">
    <div class="card-body" style="padding: 24px;">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div class="d-flex gap-3 align-items-center">
                <div style="width: 56px; height: 56px; border-radius: 14px; background: linear-gradient(135deg, #4f46e5, #6366f1); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 24px; font-weight: 700; box-shadow: 0 6px 16px rgba(79,70,229,0.3); flex-shrink: 0;">
                    <?= mb_strtoupper(mb_substr($employee->full_name, 0, 1, 'UTF-8')) ?>
                </div>
                <div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <h2 style="font-size: 20px; font-weight: 700; margin: 0; color: var(--text);">
                            Thiết Lập Mục Tiêu KRA (Key Result Areas)
                        </h2>
                        <span class="badge bg-primary-subtle text-primary" style="font-size: 12px; font-weight: 600;">
                            Chu kỳ: <?= htmlspecialchars($period->name) ?>
                        </span>
                    </div>
                    <div style="font-size: 13px; color: var(--text-muted); display: flex; gap: 16px; flex-wrap: wrap; margin-top: 6px;">
                        <span><i class="fas fa-user text-primary"></i> Nhân viên: <strong><?= htmlspecialchars($employee->full_name) ?></strong> (<?= htmlspecialchars($employee->employee_code) ?>)</span>
                        <span><i class="fas fa-id-badge text-primary"></i> Vị trí: <strong><?= htmlspecialchars($employee->pos_title ?? 'Nhân viên') ?></strong></span>
                        <span><i class="fas fa-building text-primary"></i> Phòng ban: <strong><?= htmlspecialchars($employee->dept_name ?? 'Chưa phân bổ') ?></strong></span>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2">
                <a href="<?= BASE_URL ?>/evaluation/goals/<?= $period->id ?>" class="btn btn-ghost" style="border: 1px solid var(--border);">
                    <i class="fas fa-arrow-left"></i> Quay lại Danh sách
                </a>
            </div>
        </div>

        <!-- REALTIME TOTAL WEIGHTAGE BAR -->
        <div class="mt-4 pt-3" style="border-top: 1px solid var(--border);">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span style="font-size: 13px; font-weight: 700; color: var(--text);">
                    <i class="fas fa-balance-scale text-primary me-1"></i> Tổng Trọng Số Các Mục Tiêu KRA (Bắt buộc = 100%)
                </span>
                <span id="weightTextStatus" class="fw-bold" style="font-size: 14px;">0%</span>
            </div>
            <div class="progress" style="height: 10px; border-radius: 6px; background-color: var(--bg-hover);">
                <div id="weightProgressBar" class="progress-bar" role="progressbar" style="width: 0%; transition: width 0.3s ease;"></div>
            </div>
            <div id="weightAlertBox" class="mt-2 small" style="font-size: 12px;"></div>
        </div>
    </div>
</div>

<!-- GOAL SETTING FORM -->
<form action="<?= BASE_URL ?>/evaluation/setGoals/<?= $period->id ?>/<?= $employee->id ?>" method="POST" id="goalSettingForm">
    <input type="hidden" name="_csrf_token" value="<?= Session::generateCsrfToken() ?>">
    <input type="hidden" name="action" id="formActionInput" value="submit">

    <div class="card mb-4" style="border: 1px solid var(--border); box-shadow: 0 4px 18px rgba(0,0,0,0.04); border-radius: 12px;">
        <div class="card-header d-flex justify-content-between align-items-center" style="background: var(--bg-hover); padding: 14px 20px;">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-tasks text-primary"></i>
                <strong style="font-size: 14px; color: var(--text);">Danh sách Mục tiêu KRA & Chỉ tiêu đo lường</strong>
            </div>
            <div class="d-flex gap-2">
                <!-- Nạp mẫu gợi ý KRA nhanh -->
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="loadSuggestedKraTemplate()">
                    <i class="fas fa-magic text-warning me-1"></i> Nạp mẫu KRA gợi ý
                </button>
                <button type="button" class="btn btn-sm btn-primary" onclick="addNewKraRow()">
                    <i class="fas fa-plus me-1"></i> Thêm Mục Tiêu KRA
                </button>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0" id="kraTable" style="font-size: 13px;">
                    <thead style="background: rgba(0,0,0,0.02);">
                        <tr>
                            <th style="width: 45px;" class="text-center">#</th>
                            <th style="min-width: 240px;">Tên Mục tiêu / KRA <span class="text-danger">*</span></th>
                            <th style="min-width: 250px;">Mô tả chi tiết & Hành động cụ thể</th>
                            <th style="min-width: 200px;">Chỉ tiêu đo lường (Target Metric)</th>
                            <th style="width: 140px;" class="text-center">Trọng số (%) <span class="text-danger">*</span></th>
                            <th style="width: 60px;" class="text-center">Xóa</th>
                        </tr>
                    </thead>
                    <tbody id="kraRowsContainer">
                        <?php if (!empty($currentGoals)): ?>
                            <?php foreach ($currentGoals as $i => $g): ?>
                                <tr class="kra-row" data-row-id="<?= $i ?>">
                                    <td class="text-center row-number text-muted fw-bold"><?= $i + 1 ?></td>
                                    <td>
                                        <input type="hidden" name="goals[<?= $i ?>][id]" value="<?= $g['id'] ?>">
                                        <input type="text" name="goals[<?= $i ?>][kra_title]" class="form-control form-control-sm fw-bold kra-title" 
                                               required placeholder="Ví dụ: Đảm bảo tiến độ thi công dự án..." value="<?= htmlspecialchars($g['kra_title']) ?>">
                                    </td>
                                    <td>
                                        <textarea name="goals[<?= $i ?>][description]" class="form-control form-control-sm" rows="2" 
                                                  placeholder="Chi tiết công việc và phương pháp thực hiện..."><?= htmlspecialchars($g['description'] ?? '') ?></textarea>
                                    </td>
                                    <td>
                                        <input type="text" name="goals[<?= $i ?>][target_metric]" class="form-control form-control-sm" 
                                               placeholder="Ví dụ: Hoàn thành 100% đúng hạn, Sai sót < 2%..." value="<?= htmlspecialchars($g['target_metric'] ?? '') ?>">
                                    </td>
                                    <td class="text-center">
                                        <div class="input-group input-group-sm">
                                            <input type="number" name="goals[<?= $i ?>][weightage]" class="form-control form-control-sm text-center fw-bold kra-weight" 
                                                   required min="1" max="100" value="<?= (int)$g['weightage'] ?>" oninput="calculateTotalWeight()">
                                            <span class="input-group-text">%</span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-outline-danger btn-sm border-0" onclick="removeKraRow(this)" title="Xóa dòng">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <tfoot style="background: var(--bg-hover); font-weight: 700;">
                        <tr>
                            <td colspan="4" class="text-end">TỔNG CỘNG TRỌNG SỐ:</td>
                            <td class="text-center">
                                <span id="footerTotalWeight" style="font-size: 15px;">0%</span>
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- FORM SUBMISSION CONTROLS -->
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-5">
        <a href="<?= BASE_URL ?>/evaluation/goals/<?= $period->id ?>" class="btn btn-ghost" style="border: 1px solid var(--border);">
            <i class="fas fa-times me-1"></i> Hủy thay đổi
        </a>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary" onclick="submitFormWithAction('draft')">
                <i class="fas fa-save me-1"></i> Lưu Bản Nháp (Draft)
            </button>
            <button type="button" class="btn btn-primary" id="btnSubmitGoals" onclick="submitFormWithAction('submit')" style="box-shadow: 0 4px 14px rgba(79,70,229,0.3); font-weight: 600;">
                <i class="fas fa-paper-plane me-1"></i> Hoàn Tất & Gửi Duyệt (100%)
            </button>
        </div>
    </div>
</form>

<!-- JAVASCRIPT AUTO-CHECK & DYNAMIC ROWS -->
<script>
let rowIndex = <?= $currentGoalsCount ?>;

// Mẫu KRA chuẩn cho ngành xây dựng / quản lý công trình
const suggestedTemplates = [
    { title: "Đảm bảo tiến độ thi công & Hoàn thành các mốc dự án (Milestone)", desc: "Theo dõi sát sao nhật ký công trình, phối hợp nhà thầu phụ và nghiệm thu từng giai đoạn đúng cam kết.", metric: "Đạt >= 98% mốc tiến độ theo phê duyệt", weight: 35 },
    { title: "Kiểm soát an toàn lao động & Tiêu chuẩn 5S công trường (HSE)", desc: "Tuân thủ nghiêm ngặt quy định HSE Po Sung, huấn luyện an toàn định kỳ và kiểm tra bảo hộ lao động PPE.", metric: "0 tai nạn lao động nghiêm trọng, 100% tuân thủ thẻ an toàn", weight: 25 },
    { title: "Quản lý chất lượng thi công & Nghiệm thu không lỗi (QA/QC)", desc: "Giám sát chất lượng vật liệu đầu vào và quy trình xây dựng theo bản vẽ thiết kế tiêu chuẩn.", metric: "Tỷ lệ sai sót kỹ thuật < 2%, 100% biên bản nghiệm thu đạt", weight: 20 },
    { title: "Tối ưu hóa chi phí & Tiết kiệm hao hụt vật tư xây dựng", desc: "Kiểm soát định mức vật tư thép, bê tông, cốp pha; hạn chế tối đa lãng phí tại công trường.", metric: "Hao hụt vật tư trong định mức <= 1.5%", weight: 10 },
    { title: "Kỷ luật làm việc, phối hợp nội bộ & Phát triển kỹ năng", desc: "Chấp hành nội quy công ty, tích cực tham gia các khóa đào tạo nâng cao chuyên môn.", metric: "Chấm công đầy đủ >= 98%, hoàn thành 100% khóa đào tạo", weight: 10 }
];

document.addEventListener('DOMContentLoaded', function() {
    if (document.querySelectorAll('.kra-row').length === 0) {
        // Tự động thêm 3 dòng mặc định nếu chưa có KRA
        for (let i = 0; i < 3; i++) {
            addNewKraRow();
        }
    }
    calculateTotalWeight();
});

function addNewKraRow(title = '', desc = '', metric = '', weight = 20) {
    const container = document.getElementById('kraRowsContainer');
    const idx = rowIndex++;

    const tr = document.createElement('tr');
    tr.className = 'kra-row';
    tr.dataset.rowId = idx;
    tr.innerHTML = `
        <td class="text-center row-number text-muted fw-bold">${container.children.length + 1}</td>
        <td>
            <input type="text" name="goals[${idx}][kra_title]" class="form-control form-control-sm fw-bold kra-title" 
                   required placeholder="Ví dụ: Đảm bảo tiến độ thi công dự án..." value="${title}">
        </td>
        <td>
            <textarea name="goals[${idx}][description]" class="form-control form-control-sm" rows="2" 
                      placeholder="Chi tiết công việc và phương pháp thực hiện...">${desc}</textarea>
        </td>
        <td>
            <input type="text" name="goals[${idx}][target_metric]" class="form-control form-control-sm" 
                   placeholder="Ví dụ: Hoàn thành 100% đúng hạn, Sai sót < 2%..." value="${metric}">
        </td>
        <td class="text-center">
            <div class="input-group input-group-sm">
                <input type="number" name="goals[${idx}][weightage]" class="form-control form-control-sm text-center fw-bold kra-weight" 
                       required min="1" max="100" value="${weight}" oninput="calculateTotalWeight()">
                <span class="input-group-text">%</span>
            </div>
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-outline-danger btn-sm border-0" onclick="removeKraRow(this)" title="Xóa dòng">
                <i class="fas fa-trash-alt"></i>
            </button>
        </td>
    `;
    container.appendChild(tr);
    reindexRows();
    calculateTotalWeight();
}

function removeKraRow(btn) {
    const row = btn.closest('.kra-row');
    if (document.querySelectorAll('.kra-row').length <= 1) {
        alert('Phải có ít nhất 1 mục tiêu KRA trong bản đánh giá.');
        return;
    }
    row.remove();
    reindexRows();
    calculateTotalWeight();
}

function reindexRows() {
    const rows = document.querySelectorAll('.kra-row');
    rows.forEach((row, i) => {
        row.querySelector('.row-number').innerText = (i + 1);
    });
}

function calculateTotalWeight() {
    const weightInputs = document.querySelectorAll('.kra-weight');
    let total = 0;
    weightInputs.forEach(input => {
        const val = parseInt(input.value) || 0;
        total += val;
    });

    const progressBar = document.getElementById('weightProgressBar');
    const statusText = document.getElementById('weightTextStatus');
    const footerTotal = document.getElementById('footerTotalWeight');
    const alertBox = document.getElementById('weightAlertBox');
    const btnSubmit = document.getElementById('btnSubmitGoals');

    statusText.innerText = total + '%';
    footerTotal.innerText = total + '%';
    progressBar.style.width = Math.min(total, 100) + '%';

    if (total === 100) {
        progressBar.className = 'progress-bar bg-success';
        statusText.className = 'fw-bold text-success';
        footerTotal.className = 'text-success';
        alertBox.innerHTML = '<span class="text-success"><i class="fas fa-check-circle me-1"></i> Tổng trọng số KRA chính xác 100%. Sẵn sàng gửi duyệt!</span>';
        if (btnSubmit) btnSubmit.disabled = false;
    } else if (total < 100) {
        progressBar.className = 'progress-bar bg-warning';
        statusText.className = 'fw-bold text-warning';
        footerTotal.className = 'text-warning';
        alertBox.innerHTML = `<span class="text-warning"><i class="fas fa-exclamation-triangle me-1"></i> Tổng trọng số hiện tại là ${total}%. Bạn cần thêm <strong>${100 - total}%</strong> nữa để đạt 100%.</span>`;
    } else {
        progressBar.className = 'progress-bar bg-danger';
        statusText.className = 'fw-bold text-danger';
        footerTotal.className = 'text-danger';
        alertBox.innerHTML = `<span class="text-danger"><i class="fas fa-times-circle me-1"></i> Tổng trọng số là ${total}%, vượt quá <strong>${total - 100}%</strong>. Vui lòng điều chỉnh lại.</span>`;
    }

    return total;
}

function loadSuggestedKraTemplate() {
    if (confirm('Bạn có muốn tải mẫu mục tiêu KRA gợi ý chuẩn cho nhân sự xây dựng không? Các dòng hiện tại sẽ được thay thế.')) {
        const container = document.getElementById('kraRowsContainer');
        container.innerHTML = '';
        rowIndex = 0;
        suggestedTemplates.forEach(t => {
            addNewKraRow(t.title, t.desc, t.metric, t.weight);
        });
    }
}

function submitFormWithAction(action) {
    document.getElementById('formActionInput').value = action;
    const total = calculateTotalWeight();

    if (action === 'submit') {
        if (total !== 100) {
            alert(`LỖI: Tổng trọng số các mục tiêu KRA phải bằng đúng 100%!\nHiện tại là: ${total}%\nVui lòng cân chỉnh lại các mục tiêu trước khi nộp.`);
            return;
        }

        // Kiểm tra tiêu đề KRA có để trống không
        let hasEmptyTitle = false;
        document.querySelectorAll('.kra-title').forEach(input => {
            if (!input.value.trim()) hasEmptyTitle = true;
        });

        if (hasEmptyTitle) {
            alert('Vui lòng nhập đầy đủ tên cho tất cả các mục tiêu KRA.');
            return;
        }

        if (!confirm('Xác nhận hoàn tất và gửi thiết lập mục tiêu KRA (tổng 100%) cho Quản lý phê duyệt?')) {
            return;
        }
    }

    document.getElementById('goalSettingForm').submit();
}
</script>
