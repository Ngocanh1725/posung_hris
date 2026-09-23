<?php
/**
 * ============================================================
 *  View: transfer/create.php
 *  Giao diện tạo Lệnh điều động nhân sự hàng loạt
 * ============================================================
 */
?>

<form action="<?= BASE_URL ?>/transfer/store" method="POST" id="formTransfer">
    <input type="hidden" name="_csrf_token" value="<?= Session::generateCsrfToken() ?>">
    <div class="row">
        <!-- Cột trái: Chọn Nhân sự -->
        <div class="col-md-7">
            <div class="panel h-100">
                <div class="panel-header">
                    <h3><i class="fas fa-users"></i> Bước 1: Chọn Nhân sự điều động</h3>
                </div>
                <div class="panel-body">
                    <div class="filter-controls mb-3 p-3 bg-card-light rounded border">
                        <div class="row">
                            <div class="col-md-6">
                                <label>Lọc theo Dự án hiện tại</label>
                                <select id="filter_from_project" class="form-control form-control-sm">
                                    <option value="">-- Tất cả dự án --</option>
                                    <?php foreach ($projects as $p): ?>
                                        <option value="<?= $p->id ?>"><?= h($p->project_name) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label>Tìm nhanh (Tên/Mã)</label>
                                <input type="text" id="filter_search" class="form-control form-control-sm" placeholder="Nhập từ khóa...">
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-12">
                                <label style="display: flex; align-items: center; gap: 8px;">
                                    <input type="checkbox" id="filter_6g"> Chỉ hiển thị thợ hàn 6G (Chứng chỉ hợp lệ)
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto; border: 1px solid var(--border);">
                        <table class="table table-hover mb-0" id="empTable">
                            <thead style="position: sticky; top: 0; background: var(--bg-card); z-index: 1;">
                                <tr>
                                    <th width="50" class="text-center">
                                        <input type="checkbox" id="checkAll">
                                    </th>
                                    <th>Mã NV</th>
                                    <th>Họ Tên</th>
                                    <th>Loại NS</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($employees as $emp): ?>
                                <tr class="emp-row" data-project="<?= $emp->current_project_id ?>" data-name="<?= strtolower($emp->full_name . ' ' . $emp->emp_code) ?>" data-6g="<?= !empty($emp->is_6g_welder) ? '1' : '0' ?>">
                                    <td class="text-center">
                                        <input type="checkbox" name="employee_ids[]" value="<?= $emp->id ?>" class="emp-checkbox">
                                    </td>
                                    <td class="fw-bold text-primary"><?= h($emp->emp_code) ?></td>
                                    <td>
                                        <?= h($emp->full_name) ?>
                                        <?php if (!empty($emp->is_6g_welder)): ?>
                                            <span class="badge bg-success" style="font-size: 10px; margin-left: 5px;"><i class="fas fa-fire"></i> THỢ HÀN 6G</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary"><?= h($emp->employee_type) ?></span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-2 text-muted small">
                        Đã chọn: <strong id="selectedCount" class="text-primary">0</strong> nhân sự
                    </div>
                </div>
            </div>
        </div>

        <!-- Cột phải: Thông tin Lệnh -->
        <div class="col-md-5">
            <div class="panel h-100">
                <div class="panel-header">
                    <h3><i class="fas fa-file-signature"></i> Bước 2: Thông tin Quyết định</h3>
                </div>
                <div class="panel-body">
                    
                    <div class="form-group">
                        <label>Số Quyết định *</label>
                        <input type="text" name="decision_number" class="form-control" required placeholder="VD: 125/2026/QĐ-ĐĐ-POSUNG" value="QĐ-<?= date('YmdHis') ?>-POSUNG">
                    </div>

                    <div class="form-group">
                        <label>Ngày hiệu lực *</label>
                        <input type="date" name="effective_date" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Dự án hiện tại (Từ dự án)</label>
                        <!-- Giá trị này sẽ được update tự động dựa trên filter bên trái -->
                        <select name="from_project_id" id="form_from_project_id" class="form-control readonly-select" tabindex="-1">
                            <option value="">-- Hỗn hợp nhiều dự án --</option>
                            <?php foreach ($projects as $p): ?>
                                <option value="<?= $p->id ?>"><?= h($p->project_name) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Hệ thống tự động nhận diện từ bộ lọc.</small>
                    </div>

                    <div class="form-group mt-4 pt-3 border-top">
                        <label class="text-success fw-bold"><i class="fas fa-arrow-right"></i> ĐIỀU ĐỘNG ĐẾN DỰ ÁN *</label>
                        <select name="to_project_id" class="form-control border-success" style="background: rgba(16, 185, 129, 0.05);" required>
                            <option value="">-- Chọn dự án đích --</option>
                            <?php foreach ($projects as $p): ?>
                                <option value="<?= $p->id ?>"><?= h($p->project_name) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Trung tâm chi phí (Cost Center) *</label>
                        <select name="cost_center_id" class="form-control" required>
                            <option value="">-- Chọn Cost Center hạch toán lương --</option>
                            <?php foreach ($costCenters as $cc): ?>
                                <option value="<?= $cc->id ?>"><?= h($cc->code . ' - ' . $cc->name) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Lý do điều động / Ghi chú</label>
                        <textarea name="reason" class="form-control" rows="3" placeholder="Nhập lý do điều động..."></textarea>
                    </div>

                    <div class="form-actions mt-4 text-right">
                        <a href="<?= BASE_URL ?>/transfer" class="btn btn-ghost">Hủy</a>
                        <button type="submit" name="action_type" value="Draft" class="btn btn-outline-secondary">
                            <i class="fas fa-save"></i> Lưu Nháp
                        </button>
                        <button type="submit" name="action_type" value="Pending" class="btn btn-primary" onclick="return confirm('Bạn có chắc chắn muốn trình phê duyệt Lệnh điều động này không?');">
                            <i class="fas fa-paper-plane"></i> Trình Phê Duyệt
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </div>
</form>

<style>
.row { display: flex; flex-wrap: wrap; margin: -10px; }
.col-md-7 { width: 58.333%; padding: 10px; }
.col-md-5 { width: 41.666%; padding: 10px; }
.col-md-6 { width: 50%; padding: 0 10px; }
.h-100 { height: 100%; display: flex; flex-direction: column; }
.h-100 .panel-body { flex: 1; }
.bg-card-light { background: #f8fafc; }
.rounded { border-radius: 8px; }
.border { border: 1px solid var(--border); }
.border-top { border-top: 1px dashed var(--border); }
.border-success { border-color: #10b981 !important; }
.text-success { color: #10b981; }
.p-3 { padding: 1rem; }
.mb-0 { margin-bottom: 0; }
.mb-3 { margin-bottom: 1rem; }
.mt-4 { margin-top: 1.5rem; }
.pt-3 { padding-top: 1rem; }
.text-right { text-align: right; }
.readonly-select { pointer-events: none; opacity: 0.7; }
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const filterProj = document.getElementById('filter_from_project');
    const filterSearch = document.getElementById('filter_search');
    const rows = document.querySelectorAll('.emp-row');
    const checkAll = document.getElementById('checkAll');
    const checkboxes = document.querySelectorAll('.emp-checkbox');
    const countDisplay = document.getElementById('selectedCount');
    const formFromProj = document.getElementById('form_from_project_id');

    // Hàm lọc table
    function filterTable() {
        const projVal = filterProj.value;
        const searchVal = filterSearch.value.toLowerCase();
        const only6G = document.getElementById('filter_6g').checked;

        rows.forEach(row => {
            const rowProj = row.getAttribute('data-project');
            const rowName = row.getAttribute('data-name');
            const row6g = row.getAttribute('data-6g');
            
            const matchProj = projVal === '' || rowProj === projVal;
            const matchSearch = searchVal === '' || rowName.includes(searchVal);
            const match6G = !only6G || row6g === '1';

            if (matchProj && matchSearch && match6G) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
                // Bỏ check nếu bị ẩn
                const cb = row.querySelector('.emp-checkbox');
                if (cb.checked) {
                    cb.checked = false;
                }
            }
        });
        updateCount();
    }

    // Khi đổi bộ lọc Dự án -> Tự động điền vào Form bên phải
    filterProj.addEventListener('change', () => {
        formFromProj.value = filterProj.value;
        filterTable();
    });

    filterSearch.addEventListener('input', filterTable);
    document.getElementById('filter_6g').addEventListener('change', filterTable);

    // Cập nhật số lượng
    function updateCount() {
        const checked = document.querySelectorAll('.emp-checkbox:checked').length;
        countDisplay.textContent = checked;
    }

    // Check all
    checkAll.addEventListener('change', (e) => {
        const isChecked = e.target.checked;
        rows.forEach(row => {
            if (row.style.display !== 'none') {
                row.querySelector('.emp-checkbox').checked = isChecked;
            }
        });
        updateCount();
    });

    // Check individual
    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateCount);
    });
});
</script>
