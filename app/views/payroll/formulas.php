<?php
/**
 * ============================================================
 *  View: payroll/formulas.php
 * ============================================================
 */
?>

<div class="panel mb-4">
    <div class="panel-header d-flex justify-content-between align-items-center">
        <h3><i class="fas fa-calculator"></i> Cấu hình Công thức Lương</h3>
        <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#addFormulaModal">
            <i class="fas fa-plus"></i> Thêm Công thức
        </button>
    </div>
    <div class="panel-body p-0">
        <table class="table table-bordered table-hover mb-0">
            <thead class="bg-light">
                <tr>
                    <th width="5%" class="text-center">ID</th>
                    <th width="20%">Tên Công thức</th>
                    <th width="20%">Mã Biến (Code)</th>
                    <th width="35%">Biểu thức (Expression)</th>
                    <th width="10%" class="text-center">Trạng thái</th>
                    <th width="10%" class="text-center">Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($formulas)): ?>
                    <tr><td colspan="6" class="text-center text-muted p-4">Chưa có công thức nào.</td></tr>
                <?php else: ?>
                    <?php foreach ($formulas as $f): ?>
                        <tr>
                            <td class="text-center"><?= $f->id ?></td>
                            <td><?= h($f->formula_name) ?></td>
                            <td><span class="badge badge-secondary"><?= h($f->formula_code) ?></span></td>
                            <td style="font-family: monospace; font-size: 14px;"><?= h($f->expression) ?></td>
                            <td class="text-center">
                                <?php if ($f->is_active): ?>
                                    <span class="badge badge-success">Đang áp dụng</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Không áp dụng</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#editFormulaModal<?= $f->id ?>">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <a href="<?= BASE_URL ?>/payrollformula/delete/<?= $f->id ?>" class="btn btn-danger btn-sm" onclick="return confirm('Xóa công thức này?');">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>

                        <!-- Edit Modal -->
                        <div class="modal fade text-left" id="editFormulaModal<?= $f->id ?>" tabindex="-1" role="dialog">
                            <div class="modal-dialog modal-lg" role="document">
                                <form action="<?= BASE_URL ?>/payrollformula/update" method="POST" class="modal-content">
                                    <input type="hidden" name="id" value="<?= $f->id ?>">
                                    <div class="modal-header bg-primary text-white">
                                        <h5 class="modal-title">Sửa Công thức</h5>
                                        <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-md-6 form-group">
                                                <label>Tên Công thức *</label>
                                                <input type="text" name="formula_name" class="form-control" required value="<?= h($f->formula_name) ?>">
                                            </div>
                                            <div class="col-md-6 form-group">
                                                <label>Mã Biến (Code) *</label>
                                                <input type="text" name="formula_code" class="form-control" required value="<?= h($f->formula_code) ?>">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label>Biểu thức (Expression) *</label>
                                            <textarea name="expression" class="form-control" rows="3" required style="font-family: monospace;"><?= h($f->expression) ?></textarea>
                                            <small class="text-muted">Hỗ trợ các biến như: {base_salary}, {actual_days}, {standard_days}, {ot_day_hours}, {cleanroom_days}... và hàm IF(điều kiện, đúng, sai).</small>
                                        </div>
                                        <div class="form-group">
                                            <label>Trạng thái</label>
                                            <select name="is_active" class="form-control">
                                                <option value="1" <?= $f->is_active == 1 ? 'selected' : '' ?>>Đang áp dụng</option>
                                                <option value="0" <?= $f->is_active == 0 ? 'selected' : '' ?>>Không áp dụng</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                                        <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addFormulaModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <form action="<?= BASE_URL ?>/payrollformula/store" method="POST" class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Thêm Công thức Mới</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label>Tên Công thức *</label>
                        <input type="text" name="formula_name" class="form-control" required placeholder="VD: Phụ cấp vùng sâu">
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Mã Biến (Code) *</label>
                        <input type="text" name="formula_code" class="form-control" required placeholder="VD: remote_allowance">
                    </div>
                </div>
                <div class="form-group">
                    <label>Biểu thức (Expression) *</label>
                    <textarea name="expression" class="form-control" rows="3" required style="font-family: monospace;" placeholder="VD: IF({actual_days} > 20, 500000, 200000)"></textarea>
                    <small class="text-muted">Hỗ trợ các biến như: {base_salary}, {actual_days}, {standard_days}, {ot_day_hours}, {cleanroom_days}... và hàm IF(điều kiện, đúng, sai).</small>
                </div>
                <div class="form-group">
                    <label>Trạng thái</label>
                    <select name="is_active" class="form-control">
                        <option value="1">Đang áp dụng</option>
                        <option value="0">Không áp dụng</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                <button type="submit" class="btn btn-success">Thêm</button>
            </div>
        </form>
    </div>
</div>
