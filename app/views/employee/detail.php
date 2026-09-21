<?php
/**
 * ============================================================
 *  View: employee/detail.php
 *  Hồ sơ nhân sự 360 độ – Chuẩn Doanh nghiệp Cơ điện FDI
 *  3 Tab chính + Toolbar 7 Quá trình Công tác (AJAX Modal)
 * ============================================================
 */

// Helper hiển thị ngày
function fmtDate(?string $d, string $fmt = 'd/m/Y'): string {
    return $d ? date($fmt, strtotime($d)) : '---';
}
// Helper tính số ngày còn lại
function daysLeft(?string $d): ?int {
    return $d ? (int)((strtotime($d) - time()) / 86400) : null;
}
?>

<!-- Profile Header (Giữ nguyên thiết kế premium) -->
<div class="profile-header panel mb-4 overflow-hidden position-relative" style="border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.05); border-radius: var(--radius-lg);">
    <div class="profile-cover" style="height: 160px; background: linear-gradient(135deg, rgba(37,99,235,0.85), rgba(14,165,233,0.85)), url('data:image/svg+xml,%3Csvg width=%2760%27 height=%2760%27 viewBox=%270 0 60 60%27 xmlns=%27http://www.w3.org/2000/svg%27%3E%3Cg fill=%27none%27 fill-rule=%27evenodd%27%3E%3Cg fill=%27%23ffffff%27 fill-opacity=%270.15%27%3E%3Cpath d=%27M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z%27/%3E%3C/g%3E%3C/g%3E%3C/svg%3E'); background-size: cover; border-radius: var(--radius-lg) var(--radius-lg) 0 0;"></div>
    
    <div class="panel-body d-flex flex-column flex-md-row gap-4 position-relative" style="padding: 0 30px 30px;">
        <div class="profile-avatar text-center" style="margin-top: -60px; position: relative; z-index: 2; width: max-content;">
            <div class="avatar-wrapper" style="border: 4px solid #fff; border-radius: 20px; box-shadow: 0 8px 24px rgba(0,0,0,0.12); display: inline-block; background: #fff; transition: transform 0.3s ease;">
                <?php if (!empty($employee->avatar_path)): ?>
                    <img src="<?= BASE_URL ?>/<?= h($employee->avatar_path) ?>" alt="Avatar" style="width: 140px; height: 140px; border-radius: 16px; object-fit: cover;">
                <?php else: ?>
                    <div style="width: 140px; height: 140px; border-radius: 16px; background: linear-gradient(135deg, var(--primary), var(--accent)); color: #fff; font-size: 56px; display: flex; align-items: center; justify-content: center; font-weight: 800;"><?= mb_substr($employee->full_name, 0, 1) ?></div>
                <?php endif; ?>
            </div>
            <div class="mt-3">
                <span class="badge badge-<?= strtolower($employee->status) ?>" style="font-size: 13px; padding: 6px 14px;">
                    <i class="fas fa-circle" style="font-size: 8px; margin-right: 6px;"></i><?= h($employee->status) ?>
                </span>
            </div>
        </div>

        <div class="profile-info flex-1" style="padding-top: 24px;">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h2 style="font-size: 28px; font-weight: 800; letter-spacing: -0.5px; color: var(--text-heading); margin: 0 0 4px;">
                        <?= h($employee->full_name) ?>
                    </h2>
                    <p class="text-muted mb-0" style="font-size: 14px; font-weight: 500;">
                        Mã NV: <span class="badge bg-secondary text-dark px-2 py-1"><?= h($employee->emp_code) ?></span>
                    </p>
                </div>
                <div class="d-flex gap-2 mt-2 mt-md-0">
                    <a href="<?= BASE_URL ?>/employee/edit/<?= $employee->id ?>" class="btn btn-warning btn-sm" style="border-radius: 8px; padding: 8px 16px; font-weight: 600;">
                        <i class="fas fa-edit"></i> Chỉnh sửa
                    </a>
                    <a href="<?= BASE_URL ?>/employee/printProfile/<?= $employee->id ?>" target="_blank" class="btn btn-primary btn-sm" style="border-radius: 8px; padding: 8px 16px; font-weight: 600; box-shadow: 0 6px 15px rgba(37,99,235,0.25);">
                        <i class="fas fa-print"></i> In Hồ sơ DN
                    </a>
                    <a href="<?= BASE_URL ?>/employee/print2c/<?= $employee->id ?>" target="_blank" class="btn btn-ghost btn-sm" style="border-radius: 8px; padding: 8px 16px; font-weight: 600;">
                        <i class="fas fa-file-alt"></i> Mẫu 2C
                    </a>
                </div>
            </div>
            
            <div class="profile-meta mt-4" style="background: rgba(248,250,252,0.9); padding: 16px 20px; border-radius: 12px; border: 1px solid var(--border); display: flex; flex-wrap: wrap; gap: 24px;">
                <div class="meta-item"><i class="fas fa-briefcase text-primary" style="font-size: 16px;"></i> <span class="fw-bold text-dark" style="font-size: 14px;"><?= h($employee->pos_title ?? 'Chưa cập nhật') ?></span></div>
                <div class="meta-item"><i class="fas fa-sitemap text-info" style="font-size: 16px;"></i> <span style="font-size: 14px;"><?= h($employee->dept_name ?? 'Chưa phân bổ') ?></span></div>
                <div class="meta-item"><i class="fas fa-hard-hat text-warning" style="font-size: 16px;"></i> <span style="font-size: 14px;">Dự án: <strong class="text-dark"><?= h($employee->project_name ?? 'N/A') ?></strong></span></div>
            </div>

            <div class="profile-contact mt-3 d-flex flex-wrap gap-4">
                <div class="contact-item p-2 rounded hover-bg" style="display: flex; align-items: center; gap: 8px;"><i class="fas fa-phone-alt text-muted"></i> <a href="tel:<?= h($employee->phone) ?>" class="text-secondary" style="font-weight: 500; text-decoration: none; font-size: 14px;"><?= h($employee->phone ?: '---') ?></a></div>
                <div class="contact-item p-2 rounded hover-bg" style="display: flex; align-items: center; gap: 8px;"><i class="fas fa-envelope text-muted"></i> <a href="mailto:<?= h($employee->email) ?>" class="text-secondary" style="font-weight: 500; text-decoration: none; font-size: 14px;"><?= h($employee->email ?: '---') ?></a></div>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════ -->
<!--  3 TAB CHÍNH                                               -->
<!-- ══════════════════════════════════════════════════════════ -->
<?php require 'detail_tabs.php'; ?>

<!-- ══════════════════════════════════════════════════════════ -->
<!--  STYLES                                                    -->
<!-- ══════════════════════════════════════════════════════════ -->
<style>
/* Layout Utilities */
.gap-4 { gap: 1.5rem; } .gap-2 { gap: 0.5rem; } .flex-1 { flex: 1; }
.flex-column { flex-direction: column; }
.justify-content-between { justify-content: space-between; }
.align-items-center { align-items: center; } .align-items-start { align-items: flex-start; }
.mx-1 { margin-left:0.25rem; margin-right:0.25rem; } .m-0 { margin:0; }
.mb-1 { margin-bottom:0.25rem; } .mb-3 { margin-bottom:1rem; } .mb-4 { margin-bottom:1.5rem; }
.mt-2 { margin-top:0.5rem; } .mt-3 { margin-top:1rem; } .mt-4 { margin-top:1.5rem; }
.py-2 { padding-top:0.5rem; padding-bottom:0.5rem; } .px-3 { padding-left:1rem; padding-right:1rem; }
.p-0 { padding:0!important; } .p-4 { padding:1.5rem; } .pt-3 { padding-top:1rem; } .px-4 { padding-left:1.5rem; padding-right:1.5rem; }
.text-dark { color:#0f172a; } .hover-bg:hover { background:rgba(37,99,235,0.05); }
@media (min-width:768px) { .flex-md-row { flex-direction:row; } .mt-md-0 { margin-top:0; } }

/* Modern Tabs */
.nav-tabs-modern { display:flex; list-style:none; margin:0; padding:0; }
.nav-tabs-modern .tab-link {
    position:relative; padding:14px 20px; font-weight:600; color:var(--text-secondary);
    cursor:pointer; transition:all 0.3s cubic-bezier(0.4,0,0.2,1); font-size:14px;
    display:flex; align-items:center; gap:8px; white-space:nowrap;
}
.nav-tabs-modern .tab-link i { font-size:16px; color:var(--text-muted); transition:all 0.3s; }
.nav-tabs-modern .tab-link:hover, .nav-tabs-modern .tab-link:hover i { color:var(--primary); }
.nav-tabs-modern .tab-link.active, .nav-tabs-modern .tab-link.active i { color:var(--primary); }
.nav-tabs-modern .tab-link::after {
    content:''; position:absolute; bottom:-1px; left:0; right:0; height:3px;
    background:var(--primary); border-radius:3px 3px 0 0; opacity:0; transform:scaleX(0.5);
    transition:all 0.3s cubic-bezier(0.4,0,0.2,1);
}
.nav-tabs-modern .tab-link.active::after { opacity:1; transform:scaleX(1); }

/* Tab Content */
.tab-content { display:none; animation:fadeIn 0.4s cubic-bezier(0.4,0,0.2,1); }
.tab-content.active { display:block; }
@keyframes fadeIn { from { opacity:0; transform:translateY(10px); } to { opacity:1; transform:translateY(0); } }

/* Grid */
.row { display:flex; flex-wrap:wrap; margin:-12px; }
.col-md-6 { width:50%; padding:12px; }
@media (max-width:768px) { .col-md-6 { width:100%; } }

/* Section Title */
.section-title {
    font-size:16px; font-weight:700; color:var(--text-heading); margin-bottom:20px;
    padding-bottom:12px; border-bottom:2px solid #f1f5f9; display:flex; align-items:center; gap:10px;
}
.section-title::before { content:''; display:block; width:4px; height:16px; background:var(--primary); border-radius:4px; }

/* Table Info */
.table-info { width:100%; border-collapse:separate; border-spacing:0; }
.table-info tr { transition:background 0.2s; }
.table-info tr:hover td { background:#f8fafc; }
.table-info td { padding:12px 16px; border-bottom:1px dashed var(--border); font-size:14px; }
.table-info tr:last-child td { border-bottom:none; }
.table-info td:first-child { color:var(--text-secondary); width:35%; font-weight:600; border-radius:8px 0 0 8px; }
.table-info td:last-child { color:var(--text-heading); font-weight:500; border-radius:0 8px 8px 0; }
.bg-secondary { background:rgba(148,163,184,0.15); color:#475569; }
.bg-success { background: #10b981; color: #fff; }
.bg-danger { background: #ef4444; }
.bg-light { background: #f1f5f9; }
.text-danger { color:var(--danger); }
.text-muted { color: var(--text-muted); }
.fw-bold { font-weight:600; }

/* Expiry Badges */
.badge-expiry-warning {
    display:inline-block; font-size:11px; font-weight:700; padding:3px 8px; border-radius:6px;
    background:rgba(245,158,11,0.15); color:#d97706; margin-left:6px;
    animation: pulse 2s ease-in-out infinite;
}
.badge-expiry-danger {
    display:inline-block; font-size:11px; font-weight:700; padding:3px 8px; border-radius:6px;
    background:rgba(239,68,68,0.15); color:#dc2626; margin-left:6px;
    animation: pulse 1.5s ease-in-out infinite;
}
@keyframes pulse { 0%,100%{opacity:1;} 50%{opacity:0.6;} }

/* ═══════ Process Toolbar ═══════ */
.process-buttons { display:flex; flex-wrap:wrap; gap:10px; }
.process-btn {
    display:flex; align-items:center; gap:8px; padding:12px 18px; border:1px solid var(--border);
    border-radius:12px; background:#fff; color:var(--text-heading); font-size:13px; font-weight:600;
    cursor:pointer; transition:all 0.3s cubic-bezier(0.4,0,0.2,1); box-shadow:0 2px 8px rgba(0,0,0,0.04);
}
.process-btn i { font-size:16px; color:var(--primary); transition:all 0.3s; }
.process-btn:hover {
    background:var(--primary); color:#fff; border-color:var(--primary);
    transform:translateY(-2px); box-shadow:0 8px 20px rgba(37,99,235,0.25);
}
.process-btn:hover i { color:#fff; }

/* ═══════ Custom Modal (No Bootstrap dependency) ═══════ */
.modal-overlay {
    position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5);
    z-index:9999; display:flex; align-items:center; justify-content:center;
    animation:fadeIn 0.25s ease;
}
.modal-container {
    background:#fff; border-radius:16px; width:90%; max-width:900px; max-height:90vh;
    display:flex; flex-direction:column; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);
    animation:slideUp 0.3s ease;
}
@keyframes slideUp { from { opacity:0; transform:translateY(30px); } to { opacity:1; transform:translateY(0); } }
.modal-header-custom {
    display:flex; justify-content:space-between; align-items:center; padding:20px 24px;
    border-bottom:1px solid var(--border); background:linear-gradient(135deg,rgba(37,99,235,0.05),rgba(14,165,233,0.05));
    border-radius:16px 16px 0 0;
}
.modal-title-custom { font-size:18px; font-weight:700; color:var(--text-heading); margin:0; }
.modal-close-btn {
    width:36px; height:36px; border:none; background:rgba(0,0,0,0.05); border-radius:10px;
    font-size:20px; cursor:pointer; display:flex; align-items:center; justify-content:center;
    color:var(--text-secondary); transition:all 0.2s;
}
.modal-close-btn:hover { background:var(--danger); color:#fff; }
.modal-body-custom { padding:24px; overflow-y:auto; flex:1; }
.modal-footer-custom {
    display:flex; gap:10px; padding:16px 24px; border-top:1px solid var(--border);
    border-radius:0 0 16px 16px; background:#fafbfc;
}

/* Process table in modal */
#processTable { width:100%; border-collapse:collapse; font-size:13px; }
#processTable th { background:#f8fafc; font-weight:700; padding:10px 12px; text-align:left; border-bottom:2px solid var(--border); color:var(--text-secondary); font-size:12px; text-transform:uppercase; letter-spacing:0.5px; }
#processTable td { padding:10px 12px; border-bottom:1px solid #f1f5f9; }
#processTable tbody tr { cursor:pointer; transition:all 0.2s; }
#processTable tbody tr:hover { background:rgba(37,99,235,0.05); }
#processTable tbody tr.selected { background:rgba(37,99,235,0.1); border-left:3px solid var(--primary); }

/* Process form fields */
#processFormFields { display:flex; flex-wrap:wrap; margin:0 -8px; }
#processFormFields .form-group { margin-bottom:14px; padding:0 8px; box-sizing: border-box; }
#processFormFields .form-group label {
    display:block; font-size:12px; font-weight:700; color:var(--text-secondary);
    margin-bottom:5px; text-transform:uppercase; letter-spacing:0.3px;
}
#processFormFields .form-group input,
#processFormFields .form-group select,
#processFormFields .form-group textarea {
    width:100%; padding:9px 12px; border:1px solid var(--border); border-radius:8px;
    font-size:14px; transition:all 0.2s; outline:none; font-family:inherit;
    box-sizing:border-box;
}
#processFormFields .form-group input:focus,
#processFormFields .form-group select:focus,
#processFormFields .form-group textarea:focus {
    border-color:var(--primary); box-shadow:0 0 0 3px rgba(37,99,235,0.1);
}

/* Avatar hover */
.avatar-wrapper:hover { transform:translateY(-5px); }
</style>

<!-- ══════════════════════════════════════════════════════════ -->
<!--  JAVASCRIPT                                                -->
<!-- ══════════════════════════════════════════════════════════ -->
<!-- Modal Gán Phụ Cấp -->
<?php if (Session::isManager() || Session::isAdmin()): ?>
<div class="modal fade" id="assignAllowanceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            <div class="modal-header" style="background: linear-gradient(135deg, var(--primary), var(--accent)); color: #fff; border-radius: 12px 12px 0 0;">
                <h5 class="modal-title"><i class="fas fa-hand-holding-usd"></i> Gán Phụ cấp cho nhân sự</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= BASE_URL ?>/employeeallowance/store" method="POST">
                <input type="hidden" name="_csrf_token" value="<?= Session::generateCsrfToken() ?>">
                <input type="hidden" name="employee_id" value="<?= $employee->id ?>">
                <div class="modal-body p-4">
                    <div class="form-group mb-3">
                        <label>Chọn Phụ cấp <span class="text-danger">*</span></label>
                        <select name="allowance_id" class="form-control" required>
                            <option value="">-- Chọn phụ cấp --</option>
                            <?php if (isset($allAllowances)): ?>
                                <?php foreach($allAllowances as $a): ?>
                                    <option value="<?= $a->id ?>"><?= h($a->name) ?> (Mặc định: <?= number_format($a->default_amount) ?>)</option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label>Mức tiền (Ghi đè - Tùy chọn)</label>
                        <input type="text" name="amount" class="form-control number-format" placeholder="Để trống nếu lấy theo mức mặc định">
                    </div>
                    <div class="form-group">
                        <label>Ngày hiệu lực <span class="text-danger">*</span></label>
                        <input type="date" name="effective_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>
                <div class="modal-footer bg-light" style="border-radius: 0 0 12px 12px;">
                    <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Lưu Phụ cấp</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
document.querySelectorAll('.number-format').forEach(input => {
    input.addEventListener('input', function(e) {
        let value = this.value.replace(/[^0-9]/g, '');
        if (value) {
            this.value = parseInt(value, 10).toLocaleString('en-US');
        } else {
            this.value = '';
        }
    });
});
</script>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Tab switching
    const tabs = document.querySelectorAll('.tab-link');
    const contents = document.querySelectorAll('.tab-content');
    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            tabs.forEach(t => t.classList.remove('active'));
            contents.forEach(c => c.classList.remove('active'));
            tab.classList.add('active');
            document.getElementById(tab.getAttribute('data-target')).classList.add('active');
        });
    });
});

// ═══════════════════════════════════════════════════════════
//  7 PROCESSES – AJAX MODULE
// ═══════════════════════════════════════════════════════════
const BASE = '<?= BASE_URL ?>';
const EMP_ID = <?= $employee->id ?>;
let currentProcess = '';
let processData = {};

// Cấu hình cho từng quá trình
const PROCESS_CONFIG = {
    work_histories: {
        title: '1. Quá trình Công tác',
        saveUrl: `${BASE}/employee/saveWorkHistory`,
        deleteUrl: `${BASE}/employee/deleteWorkHistory`,
        columns: ['Từ ngày', 'Đến ngày', 'Tổ chức / Công ty', 'Chức vụ', 'Dự án'],
        fields: ['from_date', 'to_date', 'organization', 'position', 'project_name', 'description'],
        colWidths: ['col-3','col-3','col-6','col-6','col-6','col-12'],
        labels: ['Từ ngày', 'Đến ngày', 'Tổ chức / Công ty', 'Chức vụ', 'Tên dự án', 'Mô tả công việc'],
        types: ['date','date','text','text','text','textarea'],
        tableFields: ['from_date','to_date','organization','position','project_name']
    },
    trainings: {
        title: '2. Quá trình Đào tạo',
        saveUrl: `${BASE}/employee/saveTraining`,
        deleteUrl: `${BASE}/employee/deleteTraining`,
        columns: ['Từ ngày', 'Đến ngày', 'Trường / Cơ sở', 'Chuyên ngành', 'Văn bằng'],
        fields: ['from_date','to_date','institution','major','certificate','degree_type','notes'],
        colWidths: ['col-3','col-3','col-6','col-6','col-6','col-6','col-12'],
        labels: ['Từ ngày','Đến ngày','Trường / Cơ sở đào tạo','Chuyên ngành','Chứng chỉ / Văn bằng','Loại hình (ĐH, CĐ, TC...)','Ghi chú'],
        types: ['date','date','text','text','text','text','textarea'],
        tableFields: ['from_date','to_date','institution','major','certificate']
    },
    salary_progressions: {
        title: '3. Diễn biến Lương',
        saveUrl: `${BASE}/employee/saveSalaryProgression`,
        deleteUrl: `${BASE}/employee/deleteSalaryProgression`,
        columns: ['Ngày áp dụng', 'Ngạch / Bậc', 'Hệ số', 'Mức lương CB', 'Số QĐ'],
        fields: ['effective_date','salary_grade','salary_coefficient','base_salary','decision_number','notes'],
        colWidths: ['col-4','col-4','col-4','col-4','col-4','col-12'],
        labels: ['Ngày áp dụng','Ngạch / Bậc lương','Hệ số lương','Mức lương cơ bản (VNĐ)','Số quyết định','Ghi chú'],
        types: ['date','text','number','number','text','textarea'],
        tableFields: ['effective_date','salary_grade','salary_coefficient','base_salary','decision_number']
    },
    family_members: {
        title: '4. Quan hệ Gia đình',
        saveUrl: `${BASE}/employee/saveFamilyMember`,
        deleteUrl: `${BASE}/employee/deleteFamilyMember`,
        columns: ['Họ tên', 'Quan hệ', 'Năm sinh', 'Nghề nghiệp', 'Nơi ở'],
        fields: ['full_name','relationship','dob','occupation','workplace','address','id_card','phone','notes'],
        colWidths: ['col-6','col-3','col-3','col-6','col-6','col-12','col-4','col-4','col-12'],
        labels: ['Họ tên','Quan hệ','Ngày sinh','Nghề nghiệp','Nơi làm việc','Nơi ở hiện nay','Số CCCD','Điện thoại','Ghi chú'],
        types: ['text','select:Bố|Mẹ|Vợ|Chồng|Con trai|Con gái|Anh|Chị|Em','date','text','text','text','text','text','textarea'],
        tableFields: ['full_name','relationship','dob','occupation','address']
    },
    reward_disciplines: {
        title: '5. Khen thưởng – Kỷ luật',
        saveUrl: `${BASE}/employee/saveRewardDiscipline`,
        deleteUrl: `${BASE}/employee/deleteRewardDiscipline`,
        columns: ['Loại', 'Số QĐ', 'Ngày QĐ', 'Hình thức', 'Cơ quan QĐ'],
        fields: ['type','decision_number','decision_date','title','reason','authority','notes'],
        colWidths: ['col-4','col-4','col-4','col-12','col-12','col-6','col-12'],
        labels: ['Loại','Số quyết định','Ngày quyết định','Hình thức KT/KL','Lý do','Cơ quan quyết định','Ghi chú'],
        types: ['select:Reward|Discipline','text','date','text','textarea','text','textarea'],
        tableFields: ['type','decision_number','decision_date','title','authority']
    },
    evaluations: {
        title: '6. Đánh giá KPI',
        saveUrl: `${BASE}/employee/saveEvaluation`,
        deleteUrl: `${BASE}/employee/deleteEvaluation`,
        columns: ['Năm', 'Kỳ đánh giá', 'Xếp loại', 'Điểm', 'Người đánh giá'],
        fields: ['eval_year','eval_period','rating','score','evaluator','notes'],
        colWidths: ['col-3','col-3','col-3','col-3','col-6','col-12'],
        labels: ['Năm đánh giá','Kỳ đánh giá','Xếp loại','Điểm số','Người đánh giá','Nhận xét'],
        types: ['number','select:6 tháng đầu|6 tháng cuối|Cả năm','select:Xuất sắc|Tốt|Khá|Trung bình|Yếu','number','text','textarea'],
        tableFields: ['eval_year','eval_period','rating','score','evaluator']
    },
    appointments: {
        title: '7. Quá trình Bổ nhiệm',
        saveUrl: `${BASE}/employee/saveAppointment`,
        deleteUrl: `${BASE}/employee/deleteAppointment`,
        columns: ['Ngày hiệu lực', 'Chức vụ', 'Phòng ban', 'Số QĐ'],
        fields: ['effective_date','position_title','department','decision_number','notes'],
        colWidths: ['col-4','col-4','col-4','col-6','col-12'],
        labels: ['Ngày hiệu lực','Chức vụ được bổ nhiệm','Phòng ban / Bộ phận','Số quyết định','Ghi chú'],
        types: ['date','text','text','text','textarea'],
        tableFields: ['effective_date','position_title','department','decision_number']
    }
};

function openProcessModal(processKey) {
    currentProcess = processKey;
    const cfg = PROCESS_CONFIG[processKey];
    document.getElementById('processModalTitle').textContent = cfg.title;
    document.getElementById('pf_process_type').value = processKey;
    
    // Build table header
    let headHtml = '<tr>';
    headHtml += '<th>#</th>';
    cfg.columns.forEach(c => headHtml += `<th>${c}</th>`);
    headHtml += '</tr>';
    document.getElementById('processTableHead').innerHTML = headHtml;
    
    // Build form fields
    let formHtml = '';
    cfg.fields.forEach((f, i) => {
        const colW = cfg.colWidths[i] || 'col-6';
        const label = cfg.labels[i] || f;
        const type = cfg.types[i] || 'text';
        const widthStyle = colW === 'col-12' ? 'width:100%' : (colW === 'col-6' ? 'width:50%' : (colW === 'col-4' ? 'width:33.33%' : 'width:25%'));
        
        formHtml += `<div class="form-group" style="${widthStyle}; display:inline-block; vertical-align:top;">`;
        formHtml += `<label for="pf_${f}">${label}</label>`;
        
        if (type === 'textarea') {
            formHtml += `<textarea name="${f}" id="pf_${f}" rows="2"></textarea>`;
        } else if (type.startsWith('select:')) {
            const opts = type.substring(7).split('|');
            formHtml += `<select name="${f}" id="pf_${f}"><option value="">-- Chọn --</option>`;
            opts.forEach(o => formHtml += `<option value="${o}">${o}</option>`);
            formHtml += '</select>';
        } else {
            formHtml += `<input type="${type}" name="${f}" id="pf_${f}" step="any">`;
        }
        formHtml += '</div>';
    });
    document.getElementById('processFormFields').innerHTML = formHtml;
    
    // Show modal
    document.getElementById('processModalOverlay').style.display = 'flex';
    document.body.style.overflow = 'hidden';
    
    // Load data
    loadProcessData(processKey);
    resetProcessForm();
}

function closeProcessModal() {
    document.getElementById('processModalOverlay').style.display = 'none';
    document.body.style.overflow = '';
}

// Click outside modal to close
document.addEventListener('click', (e) => {
    if (e.target.id === 'processModalOverlay') closeProcessModal();
});

// ESC to close
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeProcessModal();
});

function loadProcessData(processKey) {
    fetch(`${BASE}/employee/getProcesses/${EMP_ID}`)
        .then(r => r.json())
        .then(resp => {
            if (resp.success) {
                processData = resp.data;
                renderProcessTable(processKey);
            }
        })
        .catch(err => console.error('Load error:', err));
}

function renderProcessTable(processKey) {
    const cfg = PROCESS_CONFIG[processKey];
    const rows = processData[processKey] || [];
    const tbody = document.getElementById('processTableBody');
    
    if (rows.length === 0) {
        tbody.innerHTML = `<tr><td colspan="${cfg.columns.length + 1}" style="text-align:center; color:var(--text-muted); padding:20px;">Chưa có dữ liệu</td></tr>`;
        return;
    }
    
    let html = '';
    rows.forEach((row, idx) => {
        html += `<tr onclick="selectProcessRow(${JSON.stringify(row).replace(/"/g, '&quot;')})">`;
        html += `<td>${idx + 1}</td>`;
        cfg.tableFields.forEach(f => {
            let val = row[f] ?? '---';
            // Format dates
            if (f.includes('date') || f === 'from_date' || f === 'to_date' || f === 'effective_date' || f === 'dob') {
                val = val && val !== '---' ? formatDate(val) : '---';
            }
            // Format money
            if (f === 'base_salary' && val !== '---') {
                val = Number(val).toLocaleString('vi-VN');
            }
            html += `<td>${val}</td>`;
        });
        html += '</tr>';
    });
    tbody.innerHTML = html;
}

function formatDate(d) {
    if (!d) return '---';
    const parts = d.split('-');
    if (parts.length === 3) return `${parts[2]}/${parts[1]}/${parts[0]}`;
    return d;
}

function selectProcessRow(rowData) {
    const cfg = PROCESS_CONFIG[currentProcess];
    document.getElementById('pf_id').value = rowData.id;
    
    cfg.fields.forEach(f => {
        const el = document.getElementById(`pf_${f}`);
        if (el) el.value = rowData[f] ?? '';
    });
    
    document.getElementById('btnDeleteProcess').style.display = 'inline-flex';
    
    // Highlight selected row
    document.querySelectorAll('#processTableBody tr').forEach(tr => tr.classList.remove('selected'));
    event.currentTarget.classList.add('selected');
}

function resetProcessForm() {
    document.getElementById('pf_id').value = '0';
    const cfg = PROCESS_CONFIG[currentProcess];
    if (cfg) {
        cfg.fields.forEach(f => {
            const el = document.getElementById(`pf_${f}`);
            if (el) el.value = '';
        });
    }
    document.getElementById('btnDeleteProcess').style.display = 'none';
    document.querySelectorAll('#processTableBody tr').forEach(tr => tr.classList.remove('selected'));
}

function saveProcess(e) {
    if (e) e.preventDefault();
    const cfg = PROCESS_CONFIG[currentProcess];
    const form = document.getElementById('processForm');
    const formData = new FormData(form);
    
    fetch(cfg.saveUrl, { method: 'POST', body: formData })
        .then(r => r.json())
        .then(resp => {
            if (resp.success) {
                showToast(resp.message, 'success');
                loadProcessData(currentProcess);
                resetProcessForm();
            } else {
                showToast(resp.message || 'Có lỗi xảy ra', 'error');
            }
        })
        .catch(err => {
            console.error('Save error:', err);
            showToast('Lỗi kết nối server', 'error');
        });
}

function deleteProcess() {
    const id = document.getElementById('pf_id').value;
    if (!id || id === '0') return;
    
    if (!confirm('Bạn có chắc chắn muốn xóa bản ghi này?')) return;
    
    const cfg = PROCESS_CONFIG[currentProcess];
    fetch(`${cfg.deleteUrl}/${id}`, { method: 'POST' })
        .then(r => r.json())
        .then(resp => {
            if (resp.success) {
                showToast(resp.message, 'success');
                loadProcessData(currentProcess);
                resetProcessForm();
            } else {
                showToast(resp.message || 'Có lỗi xảy ra', 'error');
            }
        })
        .catch(err => {
            console.error('Delete error:', err);
            showToast('Lỗi kết nối server', 'error');
        });
}

// Toast notification
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = 'toast-notification';
    toast.style.cssText = `
        position:fixed; top:20px; right:20px; z-index:99999; padding:14px 24px;
        border-radius:12px; font-size:14px; font-weight:600; color:#fff;
        box-shadow:0 10px 30px rgba(0,0,0,0.2); animation:slideIn 0.3s ease;
        background:${type === 'success' ? '#10b981' : '#ef4444'};
    `;
    toast.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}" style="margin-right:8px;"></i>${message}`;
    document.body.appendChild(toast);
    setTimeout(() => { toast.style.animation = 'fadeOut 0.3s ease'; setTimeout(() => toast.remove(), 300); }, 3000);
}
</script>
<style>
@keyframes slideIn { from { transform:translateX(100px); opacity:0; } to { transform:translateX(0); opacity:1; } }
@keyframes fadeOut { to { opacity:0; transform:translateY(-10px); } }
</style>
