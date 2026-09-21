<?php
/**
 * ============================================================
 *  View: contract/employee.php
 * ============================================================
 */
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1" style="font-weight: 800; color: var(--text-heading);">Hồ sơ Hợp đồng: <span class="text-primary"><?= h($employee->full_name) ?></span></h2>
        <p class="text-muted mb-0">Mã NV: <strong class="text-dark"><?= h($employee->emp_code) ?></strong> | Vị trí: <?= h($employee->pos_title ?? '---') ?></p>
    </div>
    <div>
        <a href="<?= BASE_URL ?>/employee/show/<?= $employee->id ?>" class="btn btn-ghost"><i class="fas fa-arrow-left"></i> Về Hồ sơ NV</a>
        <button class="btn btn-primary" style="border-radius: 8px; box-shadow: 0 4px 12px rgba(37,99,235,0.3);" onclick="openContractModal()">
            <i class="fas fa-plus"></i> Thêm/Ký Hợp đồng mới
        </button>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="panel" style="border-radius: var(--radius-lg); border: none; box-shadow: 0 5px 20px rgba(0,0,0,0.04);">
            <div class="panel-body">
                
                <?php if (empty($contracts)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-file-signature mb-3" style="font-size: 48px; opacity: 0.3;"></i>
                        <h5>Nhân sự này chưa có hợp đồng nào</h5>
                        <p>Vui lòng ký hợp đồng mới để nhân sự được hưởng lương và quyền lợi.</p>
                        <button class="btn btn-primary mt-2" onclick="openContractModal()">Ký Hợp đồng ngay</button>
                    </div>
                <?php else: ?>
                    <div class="timeline-container" style="position: relative; padding-left: 30px;">
                        <div style="position: absolute; left: 15px; top: 0; bottom: 0; width: 2px; background: var(--border);"></div>
                        
                        <?php foreach($contracts as $idx => $c): ?>
                        <div class="timeline-item mb-4" style="position: relative;">
                            <div style="position: absolute; left: -21px; top: 0; width: 14px; height: 14px; border-radius: 50%; background: <?= $c->status === 'Active' ? 'var(--success)' : 'var(--border)' ?>; border: 2px solid #fff; box-shadow: 0 0 0 3px <?= $c->status === 'Active' ? 'rgba(16,185,129,0.2)' : 'transparent' ?>;"></div>
                            
                            <div class="card shadow-sm" style="border-radius: 12px; border: <?= $c->status === 'Active' ? '1px solid rgba(16,185,129,0.3)' : '1px solid var(--border)' ?>;">
                                <div class="card-header bg-transparent d-flex justify-content-between align-items-center border-bottom-0 pt-3 pb-0">
                                    <h5 class="mb-0 fw-bold text-dark">
                                        <i class="fas fa-file-contract text-primary me-2"></i> <?= h($c->contract_type_name) ?>
                                        <span class="badge ms-2 <?= $c->status === 'Active' ? 'bg-success' : ($c->status === 'Expired' ? 'bg-warning text-dark' : 'bg-danger') ?>">
                                            <?= h($c->status) ?>
                                        </span>
                                    </h5>
                                    <div>
                                        <button class="btn btn-sm btn-ghost" onclick="editContract(<?= htmlspecialchars(json_encode($c)) ?>)"><i class="fas fa-edit"></i> Cập nhật</button>
                                        <a href="<?= BASE_URL ?>/contract/print/<?= $c->id ?>" target="_blank" class="btn btn-sm btn-ghost text-info"><i class="fas fa-print"></i> In HĐ</a>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-md-3">
                                            <small class="text-muted d-block">Số Hợp đồng</small>
                                            <strong class="text-primary"><?= h($c->contract_number) ?></strong>
                                        </div>
                                        <div class="col-md-3">
                                            <small class="text-muted d-block">Ngày bắt đầu</small>
                                            <strong><?= date('d/m/Y', strtotime($c->start_date)) ?></strong>
                                        </div>
                                        <div class="col-md-3">
                                            <small class="text-muted d-block">Ngày hết hạn</small>
                                            <strong class="<?= $c->end_date && strtotime($c->end_date) < time() ? 'text-danger' : '' ?>">
                                                <?= $c->end_date ? date('d/m/Y', strtotime($c->end_date)) : 'Vô thời hạn' ?>
                                            </strong>
                                        </div>
                                    </div>
                                    <div class="row p-3 rounded" style="background: rgba(248,250,252,1);">
                                        <div class="col-md-4">
                                            <small class="text-muted d-block">Lương cơ bản (Theo HĐ)</small>
                                            <strong style="font-size: 16px;"><?= number_format($c->basic_salary) ?> VNĐ</strong>
                                        </div>
                                        <div class="col-md-4">
                                            <small class="text-muted d-block">Mức lương đóng BHXH</small>
                                            <strong><?= number_format($c->insurance_salary) ?> VNĐ</strong>
                                        </div>
                                        <div class="col-md-4">
                                            <small class="text-muted d-block">Công thức tính lương áp dụng</small>
                                            <strong><?= $c->payroll_formula_id ? 'Formula #'.$c->payroll_formula_id : 'Mặc định' ?></strong>
                                        </div>
                                    </div>
                                    <?php if (!empty($c->note)): ?>
                                        <div class="mt-3 text-muted small"><i class="fas fa-info-circle"></i> Ghi chú: <?= h($c->note) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
            </div>
        </div>
    </div>
</div>

<!-- Modal Thêm/Sửa Hợp đồng -->
<div class="modal fade" id="contractModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius: 16px; border: none; box-shadow: 0 20px 40px rgba(0,0,0,0.1);">
            <div class="modal-header" style="background: linear-gradient(135deg, var(--primary), var(--accent)); color: #fff; border-radius: 16px 16px 0 0;">
                <h5 class="modal-title" id="modalTitle"><i class="fas fa-file-signature"></i> Thêm Hợp đồng mới</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="contractForm" action="<?= BASE_URL ?>/contract/store" method="POST">
                <input type="hidden" name="_csrf_token" value="<?= Session::generateCsrfToken() ?>">
                <input type="hidden" name="employee_id" value="<?= $employee->id ?>">
                
                <div class="modal-body p-4">
                    <div class="row mb-3">
                        <div class="col-md-6 form-group">
                            <label>Loại Hợp đồng <span class="text-danger">*</span></label>
                            <select name="contract_type_id" id="contract_type_id" class="form-control" required>
                                <option value="">-- Chọn Loại HĐ --</option>
                                <?php foreach($contractTypes as $t): ?>
                                    <option value="<?= $t->id ?>"><?= h($t->name) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Số Hợp đồng <span class="text-danger">*</span></label>
                            <input type="text" name="contract_number" id="contract_number" class="form-control" required placeholder="VD: 123/2026/HĐLĐ-POSUNG" value="HĐ-<?= $employee->emp_code ?>-<?= date('Ymd') ?>">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 form-group">
                            <label>Ngày có hiệu lực <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" id="start_date" class="form-control" required value="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Ngày hết hạn</label>
                            <input type="date" name="end_date" id="end_date" class="form-control">
                            <small class="text-muted">Bỏ trống nếu Vô thời hạn</small>
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Trạng thái</label>
                            <select name="status" id="status" class="form-control">
                                <option value="Active">Đang hiệu lực (Active)</option>
                                <option value="Expired">Hết hạn (Expired)</option>
                                <option value="Terminated">Chấm dứt (Terminated)</option>
                            </select>
                        </div>
                    </div>
                    
                    <h6 class="mt-4 mb-3 fw-bold text-primary border-bottom pb-2"><i class="fas fa-money-check-alt"></i> Lương & Thu nhập trong HĐ</h6>
                    <div class="row mb-3">
                        <div class="col-md-4 form-group">
                            <label>Mức Lương cơ bản <span class="text-danger">*</span></label>
                            <input type="text" name="basic_salary" id="basic_salary" class="form-control number-format" required placeholder="0">
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Mức Lương đóng BHXH <span class="text-danger">*</span></label>
                            <input type="text" name="insurance_salary" id="insurance_salary" class="form-control number-format" required placeholder="0">
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Công thức lương áp dụng</label>
                            <select name="payroll_formula_id" id="payroll_formula_id" class="form-control">
                                <option value="">-- Mặc định --</option>
                                <?php foreach($formulas as $f): ?>
                                    <option value="<?= $f->id ?>"><?= h($f->name) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Ghi chú / Điều khoản bổ sung</label>
                        <textarea name="note" id="note" class="form-control" rows="3" placeholder="Nhập ghi chú..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light" style="border-radius: 0 0 16px 16px;">
                    <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save"></i> Lưu Hợp đồng</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Format tiền tệ
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

let contractModal;
document.addEventListener('DOMContentLoaded', () => {
    contractModal = new bootstrap.Modal(document.getElementById('contractModal'));
});

function openContractModal() {
    document.getElementById('contractForm').action = '<?= BASE_URL ?>/contract/store';
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-file-signature"></i> Thêm/Ký Hợp đồng mới';
    document.getElementById('contractForm').reset();
    document.getElementById('contract_number').value = 'HĐ-<?= $employee->emp_code ?>-' + new Date().toISOString().slice(0,10).replace(/-/g,'');
    contractModal.show();
}

function editContract(c) {
    document.getElementById('contractForm').action = '<?= BASE_URL ?>/contract/update/' + c.id;
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-edit"></i> Cập nhật Hợp đồng';
    
    document.getElementById('contract_type_id').value = c.contract_type_id;
    document.getElementById('contract_number').value = c.contract_number;
    document.getElementById('start_date').value = c.start_date.substring(0,10);
    document.getElementById('end_date').value = c.end_date ? c.end_date.substring(0,10) : '';
    document.getElementById('status').value = c.status;
    document.getElementById('basic_salary').value = parseFloat(c.basic_salary).toLocaleString('en-US');
    document.getElementById('insurance_salary').value = parseFloat(c.insurance_salary).toLocaleString('en-US');
    document.getElementById('payroll_formula_id').value = c.payroll_formula_id || '';
    document.getElementById('note').value = c.note || '';
    
    contractModal.show();
}
</script>
