<!-- app/views/hse/cards.php -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Quản lý Thẻ An toàn (Nhóm 1-6)</h6>
        <div class="d-flex gap-2">
            <form action="<?= BASE_URL ?>/hse/cards" method="GET" class="d-flex gap-2">
                <select name="project_id" class="form-select form-select-sm" style="width: 250px;">
                    <option value="">-- Tất cả dự án --</option>
                    <?php foreach ($projects as $p): ?>
                        <option value="<?= $p->id ?>" <?= $p->id == $currentProject ? 'selected' : '' ?>><?= h($p->project_code . ' - ' . $p->project_name) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-filter"></i> Lọc</button>
            </form>
            <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#addCardModal"><i class="fas fa-plus"></i> Cấp thẻ mới</button>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle text-center">
                <thead class="table-dark">
                    <tr>
                        <th>STT</th>
                        <th>Mã NV</th>
                        <th>Họ tên</th>
                        <th>Chức vụ / Dự án</th>
                        <th>Nhóm An toàn</th>
                        <th>Số thẻ</th>
                        <th>Ngày cấp</th>
                        <th>Ngày hết hạn</th>
                        <th>Trạng thái</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cards as $idx => $c): ?>
                    <tr>
                        <td><?= $idx + 1 ?></td>
                        <td><?= h($c['emp_code']) ?></td>
                        <td class="text-start font-weight-bold"><?= h($c['full_name']) ?></td>
                        <td><?= h($c['pos_title']) ?><br><small class="text-muted"><?= h($c['project_name']) ?></small></td>
                        <td><span class="badge bg-info text-dark"><?= h($c['group_type']) ?></span></td>
                        <td><?= h($c['card_number']) ?></td>
                        <td><?= date('d/m/Y', strtotime($c['issue_date'])) ?></td>
                        <td class="font-weight-bold <?= (strtotime($c['expiry_date']) < time()) ? 'text-danger' : '' ?>">
                            <?= date('d/m/Y', strtotime($c['expiry_date'])) ?>
                        </td>
                        <td>
                            <?php if (strtotime($c['expiry_date']) < time()): ?>
                                <span class="badge bg-danger">Hết hạn</span>
                            <?php elseif (strtotime($c['expiry_date']) < strtotime('+60 days')): ?>
                                <span class="badge bg-warning text-dark">Sắp hết hạn</span>
                            <?php else: ?>
                                <span class="badge bg-success">Hợp lệ</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if(empty($cards)): ?>
                        <tr><td colspan="9" class="text-muted py-4">Chưa có dữ liệu thẻ an toàn</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Add Card -->
<div class="modal fade" id="addCardModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="<?= BASE_URL ?>/hse/saveCard" method="POST">
          <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
          <div class="modal-header bg-success text-white">
            <h5 class="modal-title"><i class="fas fa-id-card"></i> Cấp thẻ An toàn mới</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
                <label class="form-label">Nhân viên <span class="text-danger">*</span></label>
                <input type="number" class="form-control" name="employee_id" placeholder="Nhập ID Nhân viên..." required>
                <!-- Trong thực tế sẽ dùng Select2 để chọn nhân viên -->
            </div>
            <div class="mb-3">
                <label class="form-label">Nhóm An toàn <span class="text-danger">*</span></label>
                <select name="group_type" class="form-select" required>
                    <option value="GROUP_1">Nhóm 1 (Người quản lý)</option>
                    <option value="GROUP_2">Nhóm 2 (Cán bộ HSE)</option>
                    <option value="GROUP_3">Nhóm 3 (Nghiêm ngặt: hàn, điện, trên cao...)</option>
                    <option value="GROUP_4">Nhóm 4 (LĐ thông thường)</option>
                    <option value="GROUP_5">Nhóm 5 (Y tế)</option>
                    <option value="GROUP_6">Nhóm 6 (ATVSV)</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Số thẻ <span class="text-danger">*</span></label>
                <input type="text" name="card_number" class="form-control" required>
            </div>
            <div class="row mb-3">
                <div class="col-6">
                    <label class="form-label">Ngày cấp <span class="text-danger">*</span></label>
                    <input type="date" name="issue_date" class="form-control" required>
                </div>
                <div class="col-6">
                    <label class="form-label">Ngày hết hạn <span class="text-danger">*</span></label>
                    <input type="date" name="expiry_date" class="form-control" required>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Đơn vị huấn luyện</label>
                <input type="text" name="training_unit" class="form-control" placeholder="VD: Cục An toàn, TT Huấn luyện...">
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
            <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Lưu Thẻ</button>
          </div>
      </form>
    </div>
  </div>
</div>
