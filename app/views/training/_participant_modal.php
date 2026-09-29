<!-- MODAL THÊM HỌC VIÊN VÀO KHÓA ĐÀO TẠO -->
<div class="modal fade" id="participantModal" tabindex="-1" aria-hidden="true" style="display: none; background: rgba(15,23,42,0.6); position: fixed; inset: 0; z-index: 1050; overflow-y: auto;">
    <div class="modal-dialog modal-lg" style="margin: 40px auto; max-width: 800px; padding: 0 15px;">
        <div class="modal-content" style="background: var(--bg-card); border-radius: var(--radius-lg); border: none; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); overflow: hidden;">
            
            <div class="modal-header d-flex justify-content-between align-items-center" style="padding: 16px 24px; border-bottom: 1px solid var(--border); background: var(--bg-app);">
                <h4 style="font-size: 16px; font-weight: 800; color: var(--text-heading); margin: 0;">
                    <i class="fas fa-user-plus text-primary"></i> Thêm Nhân viên vào Khóa Đào tạo
                </h4>
                <button type="button" class="btn btn-ghost btn-sm" onclick="closeParticipantModal()" style="font-size: 18px; line-height: 1; padding: 4px 8px;">
                    &times;
                </button>
            </div>

            <form id="addParticipantsForm" method="POST" action="<?= BASE_URL ?>/training/addParticipants/<?= $course->id ?>">
                <input type="hidden" name="_csrf_token" value="<?= Session::generateCsrfToken() ?>">

                <div class="modal-body" style="padding: 20px 24px;">
                    <!-- Filter Toolbar inside modal -->
                    <div style="display: flex; gap: 12px; margin-bottom: 16px; flex-wrap: wrap;">
                        <div style="flex: 1.5; min-width: 200px;">
                            <input type="text" id="modalSearchInput" class="form-control form-control-sm" placeholder="Tìm theo tên NV, mã NV..." oninput="filterAvailableEmployees()">
                        </div>
                        <div style="flex: 1; min-width: 180px;">
                            <select id="modalDeptSelect" class="form-control form-control-sm" onchange="fetchAvailableEmployees()">
                                <option value="">-- Tất cả phòng ban --</option>
                                <?php foreach($departments as $d): ?>
                                    <option value="<?= $d['id'] ?>"><?= h($d['dept_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Toolbar actions: Select All / Deselect All -->
                    <div class="d-flex justify-content-between align-items-center mb-2" style="background: var(--bg-app); padding: 8px 12px; border-radius: 6px; font-size: 12px;">
                        <div>
                            <label style="cursor: pointer; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">
                                <input type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAllEmployees(this)">
                                <span>Chọn tất cả</span>
                            </label>
                        </div>
                        <div class="text-muted">
                            Đã chọn: <strong id="selectedCountBadge" class="text-primary">0</strong> nhân viên
                        </div>
                    </div>

                    <!-- Employees List Container -->
                    <div id="employeesListContainer" style="max-height: 360px; overflow-y: auto; border: 1px solid var(--border); border-radius: 8px; padding: 4px;">
                        <div class="text-center p-4 text-muted" id="employeesListLoading">
                            <i class="fas fa-spinner fa-spin"></i> Đang tải danh sách nhân viên khả dụng...
                        </div>
                    </div>
                </div>

                <div class="modal-footer d-flex justify-content-between align-items-center" style="padding: 14px 24px; border-top: 1px solid var(--border); background: var(--bg-app);">
                    <button type="button" class="btn btn-ghost btn-sm" onclick="closeParticipantModal()">Hủy bỏ</button>
                    <button type="submit" id="btnSubmitParticipants" class="btn btn-primary btn-sm" style="padding: 8px 20px; font-weight: 700;" disabled>
                        <i class="fas fa-plus"></i> Xác nhận thêm học viên
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<script>
let availableEmployees = [];

function openParticipantModal() {
    const modal = document.getElementById('participantModal');
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
    fetchAvailableEmployees();
}

function closeParticipantModal() {
    const modal = document.getElementById('participantModal');
    modal.style.display = 'none';
    document.body.style.overflow = 'auto';
}

function fetchAvailableEmployees() {
    const container = document.getElementById('employeesListContainer');
    const deptId = document.getElementById('modalDeptSelect').value;
    container.innerHTML = '<div class="text-center p-4 text-muted"><i class="fas fa-spinner fa-spin"></i> Đang tải danh sách...</div>';

    fetch('<?= BASE_URL ?>/training/getAvailableEmployees/<?= $course->id ?>?dept_id=' + encodeURIComponent(deptId))
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                availableEmployees = data.data || [];
                renderAvailableEmployees(availableEmployees);
            } else {
                container.innerHTML = '<div class="p-3 text-danger text-center">Lỗi: ' + (data.message || 'Không thể tải') + '</div>';
            }
        })
        .catch(err => {
            container.innerHTML = '<div class="p-3 text-danger text-center">Lỗi kết nối máy chủ!</div>';
        });
}

function filterAvailableEmployees() {
    const term = document.getElementById('modalSearchInput').value.trim().toLowerCase();
    if (!term) {
        renderAvailableEmployees(availableEmployees);
        return;
    }
    const filtered = availableEmployees.filter(e => {
        return (e.full_name && e.full_name.toLowerCase().includes(term)) ||
               (e.emp_code && e.emp_code.toLowerCase().includes(term)) ||
               (e.dept_name && e.dept_name.toLowerCase().includes(term));
    });
    renderAvailableEmployees(filtered);
}

function renderAvailableEmployees(list) {
    const container = document.getElementById('employeesListContainer');
    if (!list || list.length === 0) {
        container.innerHTML = '<div class="text-center p-4 text-muted" style="font-size: 13px;"><i class="fas fa-user-slash"></i> Không tìm thấy nhân viên nào khả dụng.</div>';
        updateSelectedCounter();
        return;
    }

    let html = '<div style="display: flex; flex-direction: column; gap: 2px;">';
    list.forEach(emp => {
        html += `
            <label class="emp-select-item" style="display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; border-radius: 6px; cursor: pointer; transition: background 0.15s; margin: 0;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <input type="checkbox" name="employee_ids[]" value="${emp.id}" class="emp-checkbox" onchange="updateSelectedCounter()" style="width: 16px; height: 16px; cursor: pointer;">
                    <div>
                        <div style="font-weight: 700; font-size: 13px; color: var(--text-heading);">${escapeHtml(emp.full_name)}</div>
                        <div style="font-size: 11px; color: var(--text-muted);">
                            <span class="badge bg-secondary text-dark" style="font-size: 10px;">${escapeHtml(emp.emp_code)}</span>
                            ${emp.pos_title ? ' • ' + escapeHtml(emp.pos_title) : ''}
                        </div>
                    </div>
                </div>
                <div style="text-align: right; font-size: 12px; color: var(--text-muted);">
                    <div>${emp.dept_name ? escapeHtml(emp.dept_name) : '---'}</div>
                    ${emp.project_name ? '<div style="font-size: 10px; color: var(--primary);"><i class="fas fa-hard-hat"></i> ' + escapeHtml(emp.project_name) + '</div>' : ''}
                </div>
            </label>
        `;
    });
    html += '</div>';
    container.innerHTML = html;
    updateSelectedCounter();
}

function toggleSelectAllEmployees(source) {
    const checkboxes = document.querySelectorAll('.emp-checkbox');
    checkboxes.forEach(cb => {
        // Chỉ chọn những checkbox đang hiển thị
        if (cb.closest('.emp-select-item').style.display !== 'none') {
            cb.checked = source.checked;
        }
    });
    updateSelectedCounter();
}

function updateSelectedCounter() {
    const checked = document.querySelectorAll('.emp-checkbox:checked');
    const count = checked.length;
    document.getElementById('selectedCountBadge').innerText = count;
    document.getElementById('btnSubmitParticipants').disabled = (count === 0);

    const totalVisible = document.querySelectorAll('.emp-checkbox').length;
    const selectAllCb = document.getElementById('selectAllCheckbox');
    if (selectAllCb) {
        selectAllCb.checked = (totalVisible > 0 && count === totalVisible);
    }
}

function escapeHtml(text) {
    if (!text) return '';
    const map = {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'};
    return text.toString().replace(/[&<>"']/g, m => map[m]);
}
</script>

<style>
.emp-select-item:hover {
    background: rgba(79, 70, 229, 0.06);
}
</style>
