<div class="content-header">
    <div class="header-left">
        <h2><i class="fas fa-users-cog text-primary"></i> Quản lý Công nhân Thầu phụ</h2>
        <p>Kiểm soát chứng chỉ An toàn lao động & QR Code ra vào cổng.</p>
    </div>
    <div class="header-actions">
        <a href="<?= BASE_URL ?>/subcontractor" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Quay lại</a>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <h3>Danh sách công nhân (<?= count($workers) ?> người)</h3>
    </div>
    <div class="panel-body">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Mã CN</th>
                        <th>Họ và Tên</th>
                        <th>CMND/CCCD</th>
                        <th>Safety Induction</th>
                        <th>Trạng thái HSE</th>
                        <th>Ngày hết hạn HSE</th>
                        <th>Trạng thái</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($workers as $w): ?>
                        <tr>
                            <td><strong><?= h($w['worker_code']) ?></strong></td>
                            <td><?= h($w['full_name']) ?></td>
                            <td><?= h($w['id_card']) ?></td>
                            <td>
                                <?php if($w['induction_status'] === 'Completed'): ?>
                                    <span class="badge bg-success text-white"><i class="fas fa-check-circle"></i> Đạt (<?= date('d/m/Y', strtotime($w['safety_induction_date'])) ?>)</span>
                                <?php elseif($w['induction_status'] === 'Failed'): ?>
                                    <span class="badge bg-danger text-white"><i class="fas fa-times-circle"></i> Không Đạt</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark"><i class="fas fa-clock"></i> Chưa học</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($w['has_hse_cert']): ?>
                                    <span class="badge bg-success text-white"><i class="fas fa-check-circle"></i> Đã cấp thẻ</span>
                                <?php else: ?>
                                    <span class="badge bg-danger text-white"><i class="fas fa-times-circle"></i> Thiếu thẻ</span>
                                <?php endif; ?>
                            </td>
                            <td><?= $w['has_hse_cert'] ? date('d/m/Y', strtotime($w['hse_expiry_date'])) : '---' ?></td>
                            <td>
                                <span class="badge <?= $w['is_active'] ? 'badge-active' : 'badge-inactive' ?>">
                                    <?= $w['is_active'] ? 'Đang làm việc' : 'Đã nghỉ' ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-ghost text-primary" onclick="alert('Đã in thẻ QR Code: <?= h($w['qr_code']) ?>')" title="In thẻ QR">
                                    <i class="fas fa-qrcode"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-info" title="Cập nhật Induction" data-bs-toggle="modal" data-bs-target="#inductionModal<?= $w['id'] ?>">
                                    <i class="fas fa-user-graduate"></i>
                                </button>

                                <!-- Modal cập nhật Safety Induction -->
                                <div class="modal fade" id="inductionModal<?= $w['id'] ?>" tabindex="-1" style="text-align: left;">
                                  <div class="modal-dialog">
                                    <div class="modal-content">
                                      <form action="<?= BASE_URL ?>/subcontractor/updateInduction" method="POST">
                                          <div class="modal-header bg-info text-white">
                                            <h5 class="modal-title"><i class="fas fa-shield-alt"></i> Safety Induction: <?= h($w['full_name']) ?></h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                          </div>
                                          <div class="modal-body">
                                            <input type="hidden" name="worker_id" value="<?= $w['id'] ?>">
                                            <input type="hidden" name="sub_id" value="<?= $sub_id ?>">
                                            <div class="mb-3">
                                                <label class="form-label">Ngày đào tạo đầu vào <span class="text-danger">*</span></label>
                                                <input type="date" name="induction_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Kết quả đánh giá <span class="text-danger">*</span></label>
                                                <select name="induction_status" class="form-select" required>
                                                    <option value="Completed" <?= $w['induction_status'] == 'Completed' ? 'selected' : '' ?>>Đạt (Cho phép vào công trường)</option>
                                                    <option value="Failed" <?= $w['induction_status'] == 'Failed' ? 'selected' : '' ?>>Không Đạt (Từ chối vào)</option>
                                                    <option value="Pending" <?= $w['induction_status'] == 'Pending' ? 'selected' : '' ?>>Chưa học (Pending)</option>
                                                </select>
                                            </div>
                                          </div>
                                          <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                                            <button type="submit" class="btn btn-primary">Lưu kết quả</button>
                                          </div>
                                      </form>
                                    </div>
                                  </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
.badge-active { background: rgba(16, 185, 129, 0.1); color: #059669; }
.badge-inactive { background: rgba(100, 116, 139, 0.1); color: #475569; }
.bg-success { background-color: var(--success) !important; }
.bg-danger { background-color: var(--danger) !important; }
</style>
