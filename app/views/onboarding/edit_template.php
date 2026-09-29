<!-- ══════════════════════════════════════════════════════════
     POSUNG HRIS – CHỈNH SỬA MẪU QUY TRÌNH HỘI NHẬP (EDIT TEMPLATE)
     ══════════════════════════════════════════════════════════ -->

<div class="onboarding-builder-page">
    <div class="breadcrumb-bar mb-3">
        <a href="<?= BASE_URL ?>/onboarding"><i class="fas fa-user-check"></i> Hội nhập</a>
        <i class="fas fa-chevron-right"></i>
        <a href="<?= BASE_URL ?>/onboarding/templates">Mẫu Quy trình</a>
        <i class="fas fa-chevron-right"></i>
        <span class="text-primary fw-bold">Chỉnh Sửa: <?= htmlspecialchars($template['name']) ?></span>
    </div>

    <form action="<?= BASE_URL ?>/onboarding/updateTemplate/<?= $template['id'] ?>" method="POST" id="templateForm">
        <input type="hidden" name="_csrf_token" value="<?= Session::getCsrfToken() ?>">

        <!-- General Info Card -->
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px; background: var(--bg-card);">
            <div class="card-header bg-transparent border-bottom py-3">
                <h5 class="fw-bold mb-0 text-primary"><i class="fas fa-info-circle me-2"></i>Thông Tin Chung Mẫu Quy Trình</h5>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Tên Mẫu Quy Trình <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($template['name']) ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Phòng Ban Áp Dụng</label>
                        <select name="department_id" class="form-select">
                            <option value="">-- Áp dụng toàn công ty --</option>
                            <?php foreach ($departments as $d): ?>
                                <option value="<?= $d['id'] ?>" <?= $template['department_id'] == $d['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($d['dept_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold">Trạng Thái</label>
                        <select name="is_active" class="form-select">
                            <option value="1" <?= $template['is_active'] ? 'selected' : '' ?>>Kích hoạt</option>
                            <option value="0" <?= !$template['is_active'] ? 'selected' : '' ?>>Tạm khóa</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-bold">Mô Tả Quy Trình</label>
                        <textarea name="description" class="form-control" rows="2"><?= htmlspecialchars($template['description'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Task Checklist Builder Card -->
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px; background: var(--bg-card);">
            <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h5 class="fw-bold mb-1 text-primary"><i class="fas fa-list-check me-2"></i>Danh Mục Nhiệm Vụ Hội Nhập (<?= count($template['tasks'] ?? []) ?>)</h5>
                    <small class="text-muted">Các nhiệm vụ phân công cho các bộ phận chuyên môn</small>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-outline-info" onclick="insertPreset('IT')"><i class="fas fa-plus"></i> Gói IT</button>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="insertPreset('HR')"><i class="fas fa-plus"></i> Gói HR</button>
                    <button type="button" class="btn btn-sm btn-outline-success" onclick="insertPreset('HSE')"><i class="fas fa-plus"></i> Gói HSE</button>
                    <button type="button" class="btn btn-sm btn-outline-warning" onclick="insertPreset('Admin')"><i class="fas fa-plus"></i> Gói Admin</button>
                    <button type="button" class="btn btn-sm btn-primary" onclick="addTaskRow()">
                        <i class="fas fa-plus-circle me-1"></i> Thêm Nhiệm Vụ Mới
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="tasksTable" style="font-size: 13.5px;">
                        <thead style="background: var(--bg-hover, #f8fafc); font-size: 11px; text-transform: uppercase; color: var(--text-muted);">
                            <tr>
                                <th style="width: 40px; text-align: center;">#</th>
                                <th style="width: 140px;">Bộ phận phụ trách</th>
                                <th>Tiêu đề nhiệm vụ</th>
                                <th>Chi tiết hướng dẫn</th>
                                <th style="width: 110px; text-align: center;">Hạn (ngày)</th>
                                <th style="width: 100px; text-align: center;">Bắt buộc</th>
                                <th style="width: 60px; text-align: center;">Xóa</th>
                            </tr>
                        </thead>
                        <tbody id="taskRowsContainer">
                            <!-- Populated by PHP & JS -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-5">
            <a href="<?= BASE_URL ?>/onboarding/templates" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Hủy & Quay lại
            </a>
            <button type="submit" class="btn btn-success btn-lg px-4 shadow-sm" style="border-radius: 10px; font-weight: 700;">
                <i class="fas fa-save me-1"></i> Cập Nhật Mẫu Quy Trình
            </button>
        </div>
    </form>
</div>

<script>
let rowIndex = 0;

function addTaskRow(dept = 'HR', title = '', desc = '', days = 1, required = true) {
    rowIndex++;
    const container = document.getElementById('taskRowsContainer');
    const tr = document.createElement('tr');
    tr.id = 'task-row-' + rowIndex;
    
    tr.innerHTML = `
        <td style="text-align: center; color: var(--text-muted); font-weight: 600;">${rowIndex}</td>
        <td>
            <select name="task_dept[]" class="form-select form-select-sm" style="font-weight: 600;">
                <option value="IT" ${dept === 'IT' ? 'selected' : ''}>💻 IT Support</option>
                <option value="HR" ${dept === 'HR' ? 'selected' : ''}>👥 Hành chính NS</option>
                <option value="HSE" ${dept === 'HSE' ? 'selected' : ''}>⛑ An toàn HSE</option>
                <option value="Admin" ${dept === 'Admin' ? 'selected' : ''}>🏢 Quản trị Admin</option>
                <option value="Finance" ${dept === 'Finance' ? 'selected' : ''}>💰 Tài chính KT</option>
            </select>
        </td>
        <td>
            <input type="text" name="task_title[]" class="form-control form-control-sm" placeholder="Tiêu đề nhiệm vụ..." value="${escapeHtml(title)}" required>
        </td>
        <td>
            <input type="text" name="task_desc[]" class="form-control form-control-sm" placeholder="Mô tả hướng dẫn..." value="${escapeHtml(desc)}">
        </td>
        <td style="text-align: center;">
            <div class="input-group input-group-sm">
                <input type="number" name="task_days[]" class="form-control text-center" min="0" max="90" value="${days}">
                <span class="input-group-text">ngày</span>
            </div>
        </td>
        <td style="text-align: center;">
            <input type="checkbox" name="task_required[${rowIndex-1}]" value="1" class="form-check-input" ${required ? 'checked' : ''} style="cursor: pointer; width: 18px; height: 18px;">
        </td>
        <td style="text-align: center;">
            <button type="button" class="btn btn-sm btn-ghost text-danger" onclick="removeTaskRow(${rowIndex})">
                <i class="fas fa-trash-alt"></i>
            </button>
        </td>
    `;

    container.appendChild(tr);
}

function removeTaskRow(idx) {
    const row = document.getElementById('task-row-' + idx);
    if (row) row.remove();
}

function escapeHtml(text) {
    return text ? text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;") : '';
}

function insertPreset(type) {
    if (type === 'IT') {
        addTaskRow('IT', 'Cấp email doanh nghiệp @posung.com', 'Tạo tài khoản hộp thư và thêm vào nhóm chat dự án', 1, true);
        addTaskRow('IT', 'Cấp phát máy tính / laptop làm việc', 'Kiểm tra cấu hình, dán tem tài sản và bàn giao phụ kiện', 1, true);
        addTaskRow('IT', 'Cài đặt tài khoản phần mềm HRIS & VPN', 'Cấu hình quyền truy cập và hướng dẫn đăng nhập', 2, true);
    } else if (type === 'HR') {
        addTaskRow('HR', 'Ký kết Hợp đồng lao động', 'Ký hợp đồng thử việc/xác định thời hạn & cam kết bảo mật', 1, true);
        addTaskRow('HR', 'Hoàn thiện hồ sơ & Đăng ký BHXH', 'Kiểm tra hồ sơ gốc và khai báo cơ quan BHXH', 3, true);
        addTaskRow('HR', 'Chụp ảnh thẻ & Giới thiệu nội quy', 'Làm thẻ nhân viên và gửi sổ tay nội quy lao động', 1, true);
    } else if (type === 'HSE') {
        addTaskRow('HSE', 'Huấn luyện An toàn Lao động đầu vào', 'Đào tạo quy định an toàn xây dựng và sát hạch nhóm 1-6', 1, true);
        addTaskRow('HSE', 'Cấp phát trang bị bảo hộ cá nhân (PPE)', 'Cấp mũ cứng, giày mũi thép, áo phản quang, dây đai an toàn', 1, true);
        addTaskRow('HSE', 'Cấp Thẻ An Toàn Công Trường (HSE Card)', 'Thẻ kiểm soát điều kiện vào công trường xây dựng', 2, true);
    } else if (type === 'Admin') {
        addTaskRow('Admin', 'Cấp thẻ từ ra vào & Đăng ký gửi xe', 'Thẻ an ninh cửa từ tòa nhà / công trường', 1, true);
        addTaskRow('Admin', 'Bố trí chỗ ngồi & Văn phòng phẩm', 'Chuẩn bị bàn làm việc, văn phòng phẩm ban đầu', 1, true);
    }
}

// Pre-fill existing tasks from PHP
document.addEventListener('DOMContentLoaded', function() {
    <?php foreach (($template['tasks'] ?? []) as $t): ?>
        addTaskRow(
            '<?= addslashes($t['responsible_department']) ?>',
            '<?= addslashes($t['title']) ?>',
            '<?= addslashes($t['description'] ?? '') ?>',
            <?= (int)$t['due_days_after_join'] ?>,
            <?= $t['is_required'] ? 'true' : 'false' ?>
        );
    <?php endforeach; ?>
});
</script>
