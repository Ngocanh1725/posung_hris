<?php
/**
 * View: training/employee_history.php
 * Hiển thị lịch sử quá trình đào tạo & phát triển của CBNV
 * (Dùng để nhúng vào Profile 360° hoặc gọi qua AJAX)
 */
?>

<div class="training-history-wrapper">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="section-title m-0" style="font-size: 15px; font-weight: 700; color: var(--text-heading);">
            <i class="fas fa-graduation-cap text-primary"></i> Quá trình Đào tạo & Bồi dưỡng Chuyên môn
        </h4>
        <button type="button" class="btn btn-primary btn-sm" onclick="openAddEmployeeTrainingModal()" style="border-radius: 8px;">
            <i class="fas fa-plus"></i> Thêm Quá trình Đào tạo
        </button>
    </div>

    <?php if (empty($trainings)): ?>
        <div class="text-center p-4 rounded" style="background: var(--bg-app); border: 1px dashed var(--border);">
            <div style="font-size: 32px; color: var(--text-muted); margin-bottom: 8px;">
                <i class="fas fa-user-graduate"></i>
            </div>
            <p class="text-muted m-0" style="font-size: 13px;">Chưa có dữ liệu quá trình đào tạo nào cho nhân sự này.</p>
        </div>
    <?php else: ?>
        <div class="table-wrapper">
            <table class="table-info">
                <thead>
                    <tr>
                        <th style="width: 40px; text-align: center;">STT</th>
                        <th style="min-width: 200px;">Khóa đào tạo / Văn bằng</th>
                        <th style="min-width: 140px;">Cơ sở / Đơn vị ĐT</th>
                        <th style="min-width: 120px;">Thời gian</th>
                        <th style="min-width: 100px; text-align: center;">Kết quả / Điểm</th>
                        <th style="min-width: 130px;">Số hiệu Chứng chỉ</th>
                        <th style="min-width: 130px;">Ghi chú</th>
                        <th style="width: 80px; text-align: center;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($trainings as $idx => $tr): 
                        $isInternal = !empty($tr['training_id']);
                        $res = $tr['result'] ?? 'Completed';
                        $resClass = $res === 'Passed' || $res === 'Đạt' || $res === 'Completed' ? 'success' : ($res === 'Failed' || $res === 'Không đạt' ? 'danger' : 'warning');
                    ?>
                        <tr>
                            <td style="text-align: center; color: var(--text-muted); font-size: 12px;"><?= $idx + 1 ?></td>
                            <td>
                                <div style="font-weight: 700; color: var(--text-heading); font-size: 13px;">
                                    <?php if ($isInternal && is_numeric($tr['training_id'])): ?>
                                        <a href="<?= BASE_URL ?>/training/show/<?= $tr['training_id'] ?>" target="_blank" style="color: var(--primary); text-decoration: none;">
                                            <?= h($tr['training_name']) ?> <i class="fas fa-external-link-alt" style="font-size: 10px;"></i>
                                        </a>
                                    <?php else: ?>
                                        <?= h($tr['training_name']) ?>
                                    <?php endif; ?>
                                </div>
                                <div style="font-size: 11px;">
                                    <span class="badge bg-<?= $isInternal ? 'info text-white' : 'secondary text-dark' ?>" style="font-size: 10px; padding: 2px 6px;">
                                        <?= $isInternal ? 'Nội bộ công ty' : ($tr['training_type'] ?: 'Bên ngoài') ?>
                                    </span>
                                    <?php if (!empty($tr['location'])): ?>
                                        <span class="text-muted">• <?= h($tr['location']) ?></span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <div style="font-weight: 600; font-size: 12px;"><?= h($tr['institution'] ?: ($tr['provider'] ?: '---')) ?></div>
                            </td>
                            <td>
                                <div style="font-size: 12px;">
                                    <?= fmtDate($tr['from_date'] ?? null) ?>
                                    <?php if (!empty($tr['to_date'])): ?>
                                        <br><span class="text-muted" style="font-size: 11px;">đến <?= fmtDate($tr['to_date']) ?></span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td style="text-align: center;">
                                <span class="badge bg-<?= $resClass ?>" style="font-size: 11px; padding: 4px 8px;">
                                    <?= h($res) ?>
                                </span>
                                <?php if (isset($tr['score']) && $tr['score'] !== null && $tr['score'] !== ''): ?>
                                    <div style="font-size: 11px; font-weight: 700; color: var(--primary); margin-top: 2px;">
                                        <?= number_format((float)$tr['score'], 1) ?> đ
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($tr['certificate_no'])): ?>
                                    <span class="badge bg-light text-dark" style="border: 1px dashed var(--border); font-family: monospace; font-size: 11px;">
                                        <i class="fas fa-certificate text-warning"></i> <?= h($tr['certificate_no']) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted" style="font-size: 11px;">---</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="text-muted" style="font-size: 11px;"><?= h($tr['notes'] ?: '---') ?></div>
                            </td>
                            <td style="text-align: center;">
                                <?php if (!$isInternal && is_numeric($tr['id'])): ?>
                                    <button type="button" class="btn btn-ghost btn-sm" onclick="deleteEmployeeTrainingRecord(<?= (int)$tr['id'] ?>, <?= (int)$employeeId ?>)" title="Xóa bản ghi" style="color: var(--danger); padding: 4px 8px;">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                <?php elseif ($isInternal && is_numeric($tr['training_id'])): ?>
                                    <a href="<?= BASE_URL ?>/training/show/<?= $tr['training_id'] ?>" target="_blank" class="btn btn-ghost btn-sm" title="Xem khóa học" style="padding: 4px 8px;">
                                        <i class="fas fa-eye text-primary"></i>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">---</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- MODAL THÊM QUÁ TRÌNH ĐÀO TẠO CHO CBNV -->
<div class="modal fade" id="addEmpTrainingModal" tabindex="-1" style="display: none; background: rgba(15,23,42,0.6); position: fixed; inset: 0; z-index: 1060; overflow-y: auto;">
    <div class="modal-dialog" style="margin: 50px auto; max-width: 550px; padding: 0 15px;">
        <div class="modal-content" style="background: var(--bg-card); border-radius: var(--radius-lg); border: none; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); overflow: hidden;">
            <div class="modal-header d-flex justify-content-between align-items-center" style="padding: 16px 24px; border-bottom: 1px solid var(--border); background: var(--bg-app);">
                <h4 style="font-size: 16px; font-weight: 800; color: var(--text-heading); margin: 0;">
                    <i class="fas fa-graduation-cap text-primary"></i> Thêm Quá trình Đào tạo / Chứng chỉ
                </h4>
                <button type="button" class="btn btn-ghost btn-sm" onclick="closeAddEmployeeTrainingModal()">&times;</button>
            </div>
            <form id="addEmpTrainingForm" onsubmit="submitEmployeeTraining(event)">
                <input type="hidden" name="employee_id" value="<?= (int)($employeeId ?? 0) ?>">
                <div class="modal-body" style="padding: 20px 24px;">
                    <div class="mb-3">
                        <label class="form-label" style="font-weight: 600; font-size: 13px;">Tên khóa học / Chứng chỉ / Văn bằng <span class="text-danger">*</span></label>
                        <input type="text" name="training_name" class="form-control" placeholder="VD: Khóa An toàn Lao động Nhóm 3 / Chứng chỉ Thợ Hàn 6G" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" style="font-weight: 600; font-size: 13px;">Loại hình đào tạo</label>
                            <select name="training_type" class="form-control">
                                <option value="Internal">Nội bộ công ty</option>
                                <option value="External" selected>Đơn vị bên ngoài</option>
                                <option value="Overseas">Tu nghiệp nước ngoài</option>
                                <option value="University">Đại học / Cao đẳng</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" style="font-weight: 600; font-size: 13px;">Cơ sở / Viện đào tạo</label>
                            <input type="text" name="institution" class="form-control" placeholder="VD: Trường ĐH Bách Khoa / Trung tâm Vinacontrol">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" style="font-weight: 600; font-size: 13px;">Từ ngày</label>
                            <input type="date" name="from_date" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" style="font-weight: 600; font-size: 13px;">Đến ngày</label>
                            <input type="date" name="to_date" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" style="font-weight: 600; font-size: 13px;">Kết quả</label>
                            <select name="result" class="form-control">
                                <option value="Passed">Đạt (Passed)</option>
                                <option value="Completed" selected>Hoàn thành (Completed)</option>
                                <option value="Failed">Không đạt (Failed)</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" style="font-weight: 600; font-size: 13px;">Số hiệu Chứng chỉ</label>
                            <input type="text" name="certificate_no" class="form-control" placeholder="VD: CC-2026-0012">
                        </div>
                        <div class="col-md-12 mb-0">
                            <label class="form-label" style="font-weight: 600; font-size: 13px;">Ghi chú</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Ghi chú thêm..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between" style="padding: 14px 24px; border-top: 1px solid var(--border); background: var(--bg-app);">
                    <button type="button" class="btn btn-ghost btn-sm" onclick="closeAddEmployeeTrainingModal()">Hủy bỏ</button>
                    <button type="submit" class="btn btn-primary btn-sm" style="padding: 8px 20px; font-weight: 700;">
                        <i class="fas fa-save"></i> Lưu đào tạo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openAddEmployeeTrainingModal() {
    const modal = document.getElementById('addEmpTrainingModal');
    if (modal) {
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }
}

function closeAddEmployeeTrainingModal() {
    const modal = document.getElementById('addEmpTrainingModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

function submitEmployeeTraining(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);

    fetch('<?= BASE_URL ?>/training/saveEmployeeTraining', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('Thêm quá trình đào tạo thành công!');
            closeAddEmployeeTrainingModal();
            location.reload();
        } else {
            alert('Lỗi: ' + (data.message || 'Không thể lưu.'));
        }
    })
    .catch(err => {
        alert('Lỗi kết nối máy chủ!');
    });
}

function deleteEmployeeTrainingRecord(recordId, employeeId) {
    if (!confirm('Bạn có chắc chắn muốn xóa bản ghi đào tạo này?')) return;

    const formData = new FormData();
    formData.append('employee_id', employeeId);

    fetch('<?= BASE_URL ?>/training/deleteEmployeeTraining/' + recordId, {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Lỗi: ' + (data.message || 'Không thể xóa.'));
        }
    })
    .catch(err => {
        alert('Lỗi kết nối máy chủ!');
    });
}
</script>
