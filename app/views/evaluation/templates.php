<?php
/**
 * View: evaluation/templates.php – Quản lý Mẫu & Tiêu chí Đánh giá
 */
?>

<div style="max-width: 1100px; margin: 0 auto;">
    <!-- Top Bar -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <div style="font-size: 13px; color: var(--text-muted); margin-bottom: 4px;">
                <a href="<?= BASE_URL ?>/evaluation" style="color: var(--text-muted); text-decoration: none;">Đánh giá & KPI</a>
                <span style="margin: 0 6px;">/</span>
                <span style="color: var(--primary); font-weight: 600;">Mẫu & Tiêu chí</span>
            </div>
            <h2 style="font-size: 22px; font-weight: 800; color: var(--text-heading); margin: 0;">
                <i class="fas fa-sliders-h text-primary"></i> Quản lý Mẫu & Tiêu chí Đánh giá
            </h2>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>/evaluation" class="btn btn-ghost btn-sm">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
            <button type="button" class="btn btn-primary btn-sm" onclick="openTemplateModal()" style="font-weight: 600; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25);">
                <i class="fas fa-plus"></i> Thêm Mẫu Đánh giá
            </button>
        </div>
    </div>

    <!-- DANH SÁCH MẪU ĐÁNH GIÁ -->
    <?php if (empty($templates)): ?>
        <div class="panel p-5 text-center" style="border: none; box-shadow: 0 4px 15px rgba(0,0,0,0.03);">
            <div style="font-size: 40px; color: var(--text-muted); margin-bottom: 12px;">
                <i class="fas fa-layer-group"></i>
            </div>
            <h4 style="font-weight: 700; font-size: 16px;">Chưa có mẫu đánh giá nào</h4>
            <p class="text-muted" style="font-size: 13px; max-width: 400px; margin: 0 auto 16px;">
                Tạo mẫu đánh giá để thiết lập bộ tiêu chí và trọng số phù hợp cho từng khối nhân sự (Văn phòng, Hiện trường, Thợ tay nghề).
            </p>
            <button type="button" class="btn btn-primary btn-sm" onclick="openTemplateModal()">
                <i class="fas fa-plus"></i> Tạo mẫu đầu tiên
            </button>
        </div>
    <?php else: ?>
        <div style="display: flex; flex-direction: column; gap: 24px;">
            <?php foreach($templates as $tmpl): 
                $criteria = $tmpl['criteria_list'] ?? [];
                $totalW = (int)$tmpl['total_weight'];
                $isWeightValid = ($totalW === 100);
            ?>
                <div class="panel" style="border: none; box-shadow: 0 4px 20px rgba(0,0,0,0.04); border-radius: var(--radius-lg); overflow: hidden;">
                    <!-- Template Header -->
                    <div class="panel-header d-flex justify-content-between align-items-center flex-wrap gap-2" style="background: var(--bg-card); border-bottom: 1px solid var(--border); padding: 16px 20px;">
                        <div>
                            <div class="d-flex align-items-center gap-2">
                                <h3 style="font-size: 16px; font-weight: 800; color: var(--text-heading); margin: 0;">
                                    <?= h($tmpl['name']) ?>
                                </h3>
                                <span class="badge bg-<?= $tmpl['status'] === 'Active' ? 'success' : 'secondary' ?>" style="font-size: 11px;">
                                    <?= h($tmpl['status']) ?>
                                </span>
                                <span class="badge bg-light text-dark" style="border: 1px solid var(--border); font-size: 11px;">
                                    Áp dụng: <?= h($tmpl['applies_to']) ?>
                                </span>
                            </div>
                            <?php if (!empty($tmpl['description'])): ?>
                                <div class="text-muted" style="font-size: 12px; margin-top: 4px;">
                                    <?= h($tmpl['description']) ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="d-flex align-items-center gap-3">
                            <div>
                                <span style="font-size: 12px;" class="text-muted">Tổng trọng số:</span>
                                <strong class="<?= $isWeightValid ? 'text-success' : 'text-danger' ?>" style="font-size: 14px;">
                                    <?= $totalW ?>%
                                </strong>
                                <?php if (!$isWeightValid): ?>
                                    <span class="badge bg-danger text-white" style="font-size: 10px;" title="Tổng trọng số các tiêu chí phải đạt đúng 100%">Chưa đủ 100%</span>
                                <?php endif; ?>
                            </div>

                            <div class="d-flex gap-1">
                                <button type="button" class="btn btn-ghost btn-sm" onclick='editTemplate(<?= json_encode($tmpl) ?>)' title="Sửa mẫu">
                                    <i class="fas fa-edit text-warning"></i>
                                </button>
                                <form method="POST" action="<?= BASE_URL ?>/evaluation/deleteTemplate/<?= $tmpl['id'] ?>" style="display:inline;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa mẫu này? Toàn bộ tiêu chí sẽ bị xóa theo!');">
                                    <input type="hidden" name="_csrf_token" value="<?= Session::generateCsrfToken() ?>">
                                    <button type="submit" class="btn btn-ghost btn-sm" title="Xóa mẫu" style="color: var(--danger);">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Criteria Table -->
                    <div class="panel-body p-0">
                        <?php if (empty($criteria)): ?>
                            <div class="text-center p-4 text-muted" style="font-size: 13px;">
                                <i class="fas fa-list-ul"></i> Mẫu này chưa có tiêu chí nào. Nhấn nút bên dưới để thêm tiêu chí!
                            </div>
                        <?php else: ?>
                            <div class="table-wrapper">
                                <table>
                                    <thead>
                                        <tr>
                                            <th style="width: 50px; text-align: center;">STT</th>
                                            <th style="min-width: 200px;">Tiêu chí Đánh giá</th>
                                            <th style="min-width: 100px;">Nhóm</th>
                                            <th style="min-width: 80px; text-align: center;">Trọng số (%)</th>
                                            <th style="min-width: 250px;">Mô tả tiêu chuẩn & Thang điểm</th>
                                            <th style="width: 100px; text-align: center;">Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($criteria as $cIdx => $crit): 
                                            $catBadge = [
                                                'KPI'        => 'badge bg-primary text-white',
                                                'Competency' => 'badge bg-info text-white',
                                                'Attitude'   => 'badge bg-warning text-dark',
                                                'HSE'        => 'badge bg-danger text-white',
                                            ][$crit['category']] ?? 'badge bg-secondary text-white';
                                        ?>
                                            <tr>
                                                <td style="text-align: center; color: var(--text-muted); font-size: 12px;"><?= $crit['sort_order'] ?: ($cIdx + 1) ?></td>
                                                <td>
                                                    <strong style="color: var(--text-heading); font-size: 13px;"><?= h($crit['name']) ?></strong>
                                                </td>
                                                <td>
                                                    <span class="<?= $catBadge ?>" style="font-size: 10px; padding: 3px 6px;">
                                                        <?= h($crit['category']) ?>
                                                    </span>
                                                </td>
                                                <td style="text-align: center; font-weight: 700; color: var(--primary); font-size: 13px;">
                                                    <?= (int)$crit['weight'] ?>%
                                                </td>
                                                <td>
                                                    <div style="font-size: 12px; color: var(--text-secondary); line-height: 1.4;">
                                                        <?= h($crit['description'] ?: '---') ?>
                                                    </div>
                                                </td>
                                                <td style="text-align: center;">
                                                    <div class="d-flex justify-content-center gap-1">
                                                        <button type="button" class="btn btn-ghost btn-sm" onclick='editCriteria(<?= json_encode($crit) ?>)' title="Sửa tiêu chí" style="padding: 2px 6px;">
                                                            <i class="fas fa-edit text-warning"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-ghost btn-sm" onclick="deleteCriteria(<?= (int)$crit['id'] ?>)" title="Xóa tiêu chí" style="padding: 2px 6px; color: var(--danger);">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>

                        <!-- Footer thêm tiêu chí -->
                        <div style="padding: 12px 20px; background: var(--bg-app); border-top: 1px solid var(--border);" class="d-flex justify-content-end">
                            <button type="button" class="btn btn-ghost btn-sm" onclick="openCriteriaModal(<?= $tmpl['id'] ?>)" style="color: var(--primary); font-weight: 600;">
                                <i class="fas fa-plus"></i> Thêm Tiêu chí cho mẫu này
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- MODAL THÊM / SỬA TEMPLATE -->
<div class="modal fade" id="templateModal" tabindex="-1" style="display: none; background: rgba(15,23,42,0.6); position: fixed; inset: 0; z-index: 1050; overflow-y: auto;">
    <div class="modal-dialog" style="margin: 50px auto; max-width: 550px; padding: 0 15px;">
        <div class="modal-content" style="background: var(--bg-card); border-radius: var(--radius-lg); border: none; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); overflow: hidden;">
            <div class="modal-header d-flex justify-content-between align-items-center" style="padding: 16px 24px; border-bottom: 1px solid var(--border); background: var(--bg-app);">
                <h4 id="tmplModalTitle" style="font-size: 16px; font-weight: 800; color: var(--text-heading); margin: 0;">
                    Thêm Mẫu Đánh giá
                </h4>
                <button type="button" class="btn btn-ghost btn-sm" onclick="closeTemplateModal()">&times;</button>
            </div>
            <form method="POST" action="<?= BASE_URL ?>/evaluation/saveTemplate">
                <input type="hidden" name="_csrf_token" value="<?= Session::generateCsrfToken() ?>">
                <input type="hidden" name="id" id="tmplId" value="0">
                <div class="modal-body" style="padding: 20px 24px;">
                    <div class="mb-3">
                        <label class="form-label" style="font-weight: 600; font-size: 13px;">Tên mẫu đánh giá <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="tmplName" class="form-control" placeholder="VD: Mẫu Đánh giá Khối Kỹ sư Hiện trường" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" style="font-weight: 600; font-size: 13px;">Khối nhân sự áp dụng</label>
                            <select name="applies_to" id="tmplAppliesTo" class="form-control">
                                <option value="All">Toàn bộ nhân sự</option>
                                <option value="Office_BIM">Khối Văn phòng / BIM</option>
                                <option value="Site_Engineer">Kỹ sư Hiện trường / Giám sát</option>
                                <option value="Direct_Worker">Công nhân trực tiếp / Thợ</option>
                                <option value="Expat">Chuyên gia nước ngoài (Expat)</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" style="font-weight: 600; font-size: 13px;">Trạng thái</label>
                            <select name="status" id="tmplStatus" class="form-control">
                                <option value="Active">Hoạt động (Active)</option>
                                <option value="Inactive">Tạm ngưng (Inactive)</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label" style="font-weight: 600; font-size: 13px;">Mô tả / Mục tiêu</label>
                        <textarea name="description" id="tmplDesc" class="form-control" rows="3" placeholder="Mục đích sử dụng của mẫu đánh giá này..."></textarea>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between" style="padding: 14px 24px; border-top: 1px solid var(--border); background: var(--bg-app);">
                    <button type="button" class="btn btn-ghost btn-sm" onclick="closeTemplateModal()">Hủy bỏ</button>
                    <button type="submit" class="btn btn-primary btn-sm" style="padding: 8px 20px; font-weight: 700;">
                        <i class="fas fa-save"></i> Lưu Mẫu
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL THÊM / SỬA TIÊU CHÍ (CRITERIA) -->
<div class="modal fade" id="criteriaModal" tabindex="-1" style="display: none; background: rgba(15,23,42,0.6); position: fixed; inset: 0; z-index: 1060; overflow-y: auto;">
    <div class="modal-dialog" style="margin: 50px auto; max-width: 550px; padding: 0 15px;">
        <div class="modal-content" style="background: var(--bg-card); border-radius: var(--radius-lg); border: none; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); overflow: hidden;">
            <div class="modal-header d-flex justify-content-between align-items-center" style="padding: 16px 24px; border-bottom: 1px solid var(--border); background: var(--bg-app);">
                <h4 id="critModalTitle" style="font-size: 16px; font-weight: 800; color: var(--text-heading); margin: 0;">
                    Thêm Tiêu chí Đánh giá
                </h4>
                <button type="button" class="btn btn-ghost btn-sm" onclick="closeCriteriaModal()">&times;</button>
            </div>
            <form id="criteriaForm" onsubmit="submitCriteria(event)">
                <input type="hidden" name="id" id="critId" value="0">
                <input type="hidden" name="template_id" id="critTemplateId" value="0">
                <div class="modal-body" style="padding: 20px 24px;">
                    <div class="mb-3">
                        <label class="form-label" style="font-weight: 600; font-size: 13px;">Tên tiêu chí đánh giá <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="critName" class="form-control" placeholder="VD: Tiến độ công việc & KPI hoặc Kỹ năng giải quyết vấn đề" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" style="font-weight: 600; font-size: 13px;">Nhóm tiêu chí</label>
                            <select name="category" id="critCategory" class="form-control">
                                <option value="KPI">KPI (Hiệu suất công việc)</option>
                                <option value="Competency">Competency (Năng lực chuyên môn)</option>
                                <option value="Attitude">Attitude (Thái độ & Văn hóa)</option>
                                <option value="HSE">HSE (An toàn & Kỷ luật)</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" style="font-weight: 600; font-size: 13px;">Trọng số (%) <span class="text-danger">*</span></label>
                            <input type="number" name="weight" id="critWeight" class="form-control" value="20" min="1" max="100" required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label" style="font-weight: 600; font-size: 13px;">Thứ tự hiển thị</label>
                            <input type="number" name="sort_order" id="critOrder" class="form-control" value="1" min="1">
                        </div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label" style="font-weight: 600; font-size: 13px;">Mô tả tiêu chuẩn đạt mức 1 - 5 điểm</label>
                        <textarea name="description" id="critDesc" class="form-control" rows="3" placeholder="Ghi chú hướng dẫn người chấm điểm mức nào đạt yêu cầu..."></textarea>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between" style="padding: 14px 24px; border-top: 1px solid var(--border); background: var(--bg-app);">
                    <button type="button" class="btn btn-ghost btn-sm" onclick="closeCriteriaModal()">Hủy bỏ</button>
                    <button type="submit" class="btn btn-primary btn-sm" style="padding: 8px 20px; font-weight: 700;">
                        <i class="fas fa-save"></i> Lưu Tiêu chí
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Template Modal Functions
function openTemplateModal() {
    document.getElementById('tmplId').value = '0';
    document.getElementById('tmplName').value = '';
    document.getElementById('tmplAppliesTo').value = 'All';
    document.getElementById('tmplStatus').value = 'Active';
    document.getElementById('tmplDesc').value = '';
    document.getElementById('tmplModalTitle').innerText = 'Thêm Mẫu Đánh giá Mới';
    document.getElementById('templateModal').style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function editTemplate(tmpl) {
    document.getElementById('tmplId').value = tmpl.id;
    document.getElementById('tmplName').value = tmpl.name || '';
    document.getElementById('tmplAppliesTo').value = tmpl.applies_to || 'All';
    document.getElementById('tmplStatus').value = tmpl.status || 'Active';
    document.getElementById('tmplDesc').value = tmpl.description || '';
    document.getElementById('tmplModalTitle').innerText = 'Chỉnh sửa Mẫu: ' + tmpl.name;
    document.getElementById('templateModal').style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeTemplateModal() {
    document.getElementById('templateModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

// Criteria Modal Functions
function openCriteriaModal(templateId) {
    document.getElementById('critId').value = '0';
    document.getElementById('critTemplateId').value = templateId;
    document.getElementById('critName').value = '';
    document.getElementById('critCategory').value = 'KPI';
    document.getElementById('critWeight').value = '20';
    document.getElementById('critOrder').value = '1';
    document.getElementById('critDesc').value = '';
    document.getElementById('critModalTitle').innerText = 'Thêm Tiêu chí Đánh giá';
    document.getElementById('criteriaModal').style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function editCriteria(crit) {
    document.getElementById('critId').value = crit.id;
    document.getElementById('critTemplateId').value = crit.template_id;
    document.getElementById('critName').value = crit.name || '';
    document.getElementById('critCategory').value = crit.category || 'KPI';
    document.getElementById('critWeight').value = crit.weight || 20;
    document.getElementById('critOrder').value = crit.sort_order || 1;
    document.getElementById('critDesc').value = crit.description || '';
    document.getElementById('critModalTitle').innerText = 'Chỉnh sửa Tiêu chí: ' + crit.name;
    document.getElementById('criteriaModal').style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeCriteriaModal() {
    document.getElementById('criteriaModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

function submitCriteria(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);

    fetch('<?= BASE_URL ?>/evaluation/saveCriteria', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            closeCriteriaModal();
            location.reload();
        } else {
            alert('Lỗi: ' + (data.message || 'Không thể lưu tiêu chí.'));
        }
    })
    .catch(err => {
        alert('Lỗi kết nối máy chủ!');
    });
}

function deleteCriteria(id) {
    if (!confirm('Bạn có chắc chắn muốn xóa tiêu chí này?')) return;

    fetch('<?= BASE_URL ?>/evaluation/deleteCriteria/' + id, {
        method: 'POST'
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Lỗi: ' + (data.message || 'Không thể xóa tiêu chí.'));
        }
    })
    .catch(err => {
        alert('Lỗi kết nối máy chủ!');
    });
}
</script>
