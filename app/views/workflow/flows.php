<?php
/**
 * POSUNG HRIS - Cấu hình Luồng phê duyệt đa cấp (Approval Flows)
 * File: app/views/workflow/flows.php
 */
?>

<div class="container-fluid px-4 py-4">

    <!-- Header & Action Bar -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold mb-1 text-dark">
                <i class="fa-solid fa-diagram-project text-primary me-2"></i> Cấu hình Luồng phê duyệt (Approval Flows)
            </h4>
            <p class="text-muted small mb-0">Thiết lập quy trình phê duyệt đa cấp, chỉ định vai trò và thứ tự các bước duyệt cho từng phân hệ.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>/workflow/pendingApprovals" class="btn btn-outline-secondary rounded-pill px-3 shadow-sm">
                <i class="fa-solid fa-inbox me-1"></i> Về Hộp duyệt
            </a>
            <button type="button" class="btn btn-primary rounded-pill px-3 shadow-sm" id="btnAddNewFlow">
                <i class="fa-solid fa-plus me-1"></i> Tạo luồng phê duyệt mới
            </button>
        </div>
    </div>

    <!-- Flash Notifications -->
    <?php if (Session::hasFlash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm rounded-3" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i> <?= Session::flash('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (Session::hasFlash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm rounded-3" role="alert">
            <i class="fa-solid fa-triangle-exclamation me-2"></i> <?= Session::flash('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Flows List Card -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
        <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-dark">
                <i class="fa-solid fa-sliders text-primary me-2"></i> Danh sách quy trình đang áp dụng (<?= count($flows) ?>)
            </h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small text-uppercase fw-semibold text-muted">
                        <tr>
                            <th style="width: 50px;" class="text-center">#</th>
                            <th style="width: 250px;">Tên quy trình</th>
                            <th style="width: 150px;">Phân hệ</th>
                            <th>Các bước phê duyệt (Sequence)</th>
                            <th style="width: 120px;" class="text-center">Trạng thái</th>
                            <th style="width: 150px;" class="text-end pe-4">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        <?php if (empty($flows)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">Chưa có luồng duyệt nào được cấu hình.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($flows as $idx => $f): ?>
                                <?php
                                    $modLabels = [
                                        'leave'          => 'Nghỉ phép',
                                        'loan'           => 'Vay vốn / Ứng',
                                        'expense'        => 'Thanh toán chi phí',
                                        'transfer'       => 'Điều chuyển nhân sự',
                                        'profile_change' => 'Sửa đổi hồ sơ'
                                    ];
                                    $steps = $f['steps_array'] ?? [];
                                ?>
                                <tr>
                                    <td class="text-center text-muted fw-bold"><?= $idx + 1 ?></td>
                                    <td>
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($f['name']) ?></div>
                                        <div class="text-muted text-truncate" style="max-width: 240px; font-size: 0.75rem;">
                                            <?= htmlspecialchars($f['description'] ?? 'Không có mô tả') ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border font-monospace px-2 py-1">
                                            <?= $modLabels[$f['module']] ?? $f['module'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-wrap align-items-center gap-2">
                                            <?php foreach ($steps as $sIdx => $st): ?>
                                                <div class="d-flex align-items-center bg-light border rounded-pill px-3 py-1 shadow-sm">
                                                    <span class="badge bg-primary rounded-circle me-2" style="width: 20px; height: 20px; line-height: 12px; padding: 4px 0;">
                                                        <?= $st['level'] ?? ($sIdx + 1) ?>
                                                    </span>
                                                    <span class="fw-semibold text-dark me-1"><?= htmlspecialchars($st['role_name'] ?? $st['role']) ?></span>
                                                    <small class="text-muted">(<?= $st['required_count'] ?? 1 ?>)</small>
                                                </div>
                                                <?php if ($sIdx < count($steps) - 1): ?>
                                                    <i class="fa-solid fa-arrow-right text-muted small"></i>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($f['is_active']): ?>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1">
                                                <i class="fa-solid fa-circle-check me-1"></i> Đang bật
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2 py-1">
                                                <i class="fa-solid fa-pause me-1"></i> Tạm ngưng
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-primary rounded-pill px-3 me-1 btn-edit-flow"
                                                    data-flow='<?= htmlspecialchars(json_encode($f), ENT_QUOTES, 'UTF-8') ?>'>
                                                <i class="fa-solid fa-pen-to-square me-1"></i> Sửa
                                            </button>
                                            <a href="<?= BASE_URL ?>/workflow/deleteFlow/<?= $f['id'] ?>" 
                                               class="btn btn-outline-danger rounded-pill px-2"
                                               onclick="return confirm('Bạn có chắc chắn muốn xóa quy trình này?');">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════
     MODAL CẤU HÌNH LUỒNG DUYỆT (FLOW MODAL)
     ══════════════════════════════════════════════════════════ -->
<div class="modal fade" id="flowModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form action="<?= BASE_URL ?>/workflow/saveFlow" method="POST" id="flowForm">
                <?= Session::csrfField() ?>
                <input type="hidden" name="id" id="flow_id" value="">

                <div class="modal-header bg-primary text-white rounded-top-4 py-3">
                    <h5 class="modal-title fw-bold" id="flowModalTitle">
                        <i class="fa-solid fa-diagram-project me-2"></i> Thiết lập Luồng phê duyệt
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-md-7">
                            <label class="form-label small fw-semibold">Tên quy trình phê duyệt (*):</label>
                            <input type="text" name="name" id="flow_name" class="form-control" required placeholder="VD: Quy trình Phê duyệt Nghỉ phép">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small fw-semibold">Áp dụng cho Phân hệ (*):</label>
                            <select name="module" id="flow_module" class="form-select" required>
                                <option value="leave">Nghỉ phép (Leave)</option>
                                <option value="loan">Vay vốn / Ứng lương (Loan)</option>
                                <option value="expense">Thanh toán chi phí (Expense)</option>
                                <option value="transfer">Điều chuyển công trường (Transfer)</option>
                                <option value="profile_change">Sửa đổi hồ sơ cá nhân (Profile Change)</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label small fw-semibold">Mô tả quy trình:</label>
                            <input type="text" name="description" id="flow_description" class="form-control" placeholder="Mô tả phạm vi áp dụng, tiêu chí hoặc đối tượng">
                        </div>
                        <div class="col-md-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" id="flow_is_active" value="1" checked>
                                <label class="form-check-label small fw-semibold" for="flow_is_active">Kích hoạt quy trình này</label>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Steps Builder (Drag & Drop Sequence) -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h6 class="fw-bold mb-0 text-dark">
                                <i class="fa-solid fa-bars-staggered text-primary me-2"></i> Trình tự các cấp phê duyệt (Sequence Steps)
                            </h6>
                            <small class="text-muted">Kéo thả icon <i class="fa-solid fa-grip-vertical"></i> hoặc dùng mũi tên để thay đổi thứ tự cấp duyệt.</small>
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3" id="btnAddStep">
                            <i class="fa-solid fa-plus me-1"></i> Thêm bước duyệt
                        </button>
                    </div>

                    <div id="stepsContainer" class="d-flex flex-column gap-2 mb-3">
                        <!-- Step rows injected dynamically by JavaScript -->
                    </div>

                    <div class="alert alert-light border small text-muted mb-0">
                        <i class="fa-solid fa-circle-info text-primary me-1"></i>
                        <strong>Cơ chế duyệt:</strong> Đơn khi được tạo sẽ tuần tự chuyển qua Cấp 1, sau khi Cấp 1 duyệt xong sẽ tự động thông báo và chuyển tới Cấp 2, cho đến khi qua hết tất cả các cấp để hoàn tất.
                    </div>
                </div>

                <div class="modal-footer bg-light rounded-bottom-4 py-2">
                    <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Lưu cấu hình quy trình
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const AVAILABLE_ROLES = <?= json_encode($roles, JSON_UNESCAPED_UNICODE) ?>;
let stepCounter = 0;

function createStepRow(level, roleKey = 'direct_manager', roleName = '', requiredCount = 1) {
    stepCounter++;
    const rowId = `step_row_${stepCounter}`;
    
    let optionsHtml = '';
    for (const [key, label] of Object.entries(AVAILABLE_ROLES)) {
        const selected = (key === roleKey) ? 'selected' : '';
        optionsHtml += `<option value="${key}" data-label="${label}" ${selected}>${label}</option>`;
    }

    const row = document.createElement('div');
    row.className = 'step-item card border shadow-sm p-3 bg-white rounded-3';
    row.draggable = true;
    row.id = rowId;
    row.innerHTML = `
        <div class="d-flex align-items-center justify-content-between gap-3">
            <div class="drag-handle text-muted cursor-grab" style="cursor: grab;">
                <i class="fa-solid fa-grip-vertical fs-5"></i>
            </div>
            <div class="badge bg-primary rounded-circle d-flex align-items-center justify-content-center step-level-badge" style="width: 28px; height: 28px;">
                ${level}
            </div>
            <div class="flex-grow-1">
                <label class="form-label small fw-semibold mb-1">Vai trò phê duyệt Cấp <span class="step-level-text">${level}</span>:</label>
                <select name="steps[${stepCounter}][role]" class="form-select form-select-sm step-role-select" onchange="updateRoleName(this)">
                    ${optionsHtml}
                </select>
                <input type="hidden" name="steps[${stepCounter}][role_name]" class="step-role-name-input" value="${roleName || AVAILABLE_ROLES[roleKey] || ''}">
            </div>
            <div style="width: 120px;">
                <label class="form-label small fw-semibold mb-1">Số lượt duyệt:</label>
                <input type="number" name="steps[${stepCounter}][required_count]" class="form-control form-control-sm text-center" value="${requiredCount}" min="1" max="10">
            </div>
            <div class="d-flex gap-1 pt-3">
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="moveStepUp(this)" title="Di chuyển lên">
                    <i class="fa-solid fa-chevron-up"></i>
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="moveStepDown(this)" title="Di chuyển xuống">
                    <i class="fa-solid fa-chevron-down"></i>
                </button>
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeStep(this)" title="Xóa bước này">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </div>
        </div>
    `;

    // Attach Drag and Drop handlers
    attachDragAndDrop(row);

    return row;
}

function updateRoleName(selectElem) {
    const selectedOption = selectElem.options[selectElem.selectedIndex];
    const roleName = selectedOption.getAttribute('data-label') || selectedOption.text;
    const parentRow = selectElem.closest('.step-item');
    parentRow.querySelector('.step-role-name-input').value = roleName;
}

function reindexSteps() {
    const container = document.getElementById('stepsContainer');
    const items = container.querySelectorAll('.step-item');
    items.forEach((item, index) => {
        const lvl = index + 1;
        item.querySelector('.step-level-badge').textContent = lvl;
        item.querySelector('.step-level-text').textContent = lvl;
    });
}

function moveStepUp(btn) {
    const item = btn.closest('.step-item');
    const prev = item.previousElementSibling;
    if (prev) {
        item.parentNode.insertBefore(item, prev);
        reindexSteps();
    }
}

function moveStepDown(btn) {
    const item = btn.closest('.step-item');
    const next = item.nextElementSibling;
    if (next) {
        item.parentNode.insertBefore(next, item);
        reindexSteps();
    }
}

function removeStep(btn) {
    const container = document.getElementById('stepsContainer');
    if (container.querySelectorAll('.step-item').length <= 1) {
        alert('Quy trình phải có ít nhất một cấp phê duyệt!');
        return;
    }
    btn.closest('.step-item').remove();
    reindexSteps();
}

// Drag & Drop Implementation
let draggedItem = null;
function attachDragAndDrop(elem) {
    elem.addEventListener('dragstart', function(e) {
        draggedItem = elem;
        setTimeout(() => elem.style.opacity = '0.4', 0);
    });

    elem.addEventListener('dragend', function() {
        setTimeout(() => {
            elem.style.opacity = '1';
            draggedItem = null;
            reindexSteps();
        }, 0);
    });

    elem.addEventListener('dragover', function(e) {
        e.preventDefault();
    });

    elem.addEventListener('dragenter', function(e) {
        e.preventDefault();
        elem.classList.add('bg-light-subtle');
    });

    elem.addEventListener('dragleave', function() {
        elem.classList.remove('bg-light-subtle');
    });

    elem.addEventListener('drop', function() {
        elem.classList.remove('bg-light-subtle');
        if (draggedItem && draggedItem !== elem) {
            const container = document.getElementById('stepsContainer');
            const allItems = [...container.querySelectorAll('.step-item')];
            const draggedIdx = allItems.indexOf(draggedItem);
            const droppedIdx = allItems.indexOf(elem);

            if (draggedIdx < droppedIdx) {
                elem.after(draggedItem);
            } else {
                elem.before(draggedItem);
            }
            reindexSteps();
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const flowModal = new bootstrap.Modal(document.getElementById('flowModal'));
    const stepsContainer = document.getElementById('stepsContainer');

    // Thêm bước mới
    document.getElementById('btnAddStep').addEventListener('click', function() {
        const nextLevel = stepsContainer.querySelectorAll('.step-item').length + 1;
        const newRow = createStepRow(nextLevel);
        stepsContainer.appendChild(newRow);
        reindexSteps();
    });

    // Mở modal tạo mới
    document.getElementById('btnAddNewFlow').addEventListener('click', function() {
        document.getElementById('flowForm').reset();
        document.getElementById('flow_id').value = '';
        document.getElementById('flowModalTitle').innerHTML = '<i class="fa-solid fa-plus me-2"></i> Tạo Luồng phê duyệt mới';
        stepsContainer.innerHTML = '';

        // Mặc định tạo 2 bước chuẩn
        stepsContainer.appendChild(createStepRow(1, 'direct_manager', 'Quản lý trực tiếp (Direct Manager)', 1));
        stepsContainer.appendChild(createStepRow(2, 'hr_admin', 'Chuyên viên Nhân sự (C&B)', 1));
        reindexSteps();

        flowModal.show();
    });

    // Mở modal chỉnh sửa
    document.querySelectorAll('.btn-edit-flow').forEach(btn => {
        btn.addEventListener('click', function() {
            const flow = JSON.parse(this.dataset.flow);
            document.getElementById('flow_id').value = flow.id;
            document.getElementById('flow_name').value = flow.name;
            document.getElementById('flow_module').value = flow.module;
            document.getElementById('flow_description').value = flow.description || '';
            document.getElementById('flow_is_active').checked = (flow.is_active == 1);
            document.getElementById('flowModalTitle').innerHTML = `<i class="fa-solid fa-pen-to-square me-2"></i> Chỉnh sửa Luồng: ${flow.name}`;

            stepsContainer.innerHTML = '';
            const steps = flow.steps_array || [];
            if (steps.length > 0) {
                steps.forEach((st, idx) => {
                    stepsContainer.appendChild(createStepRow(idx + 1, st.role, st.role_name || '', st.required_count || 1));
                });
            } else {
                stepsContainer.appendChild(createStepRow(1, 'direct_manager', 'Quản lý trực tiếp (Direct Manager)', 1));
            }
            reindexSteps();

            flowModal.show();
        });
    });
});
</script>
