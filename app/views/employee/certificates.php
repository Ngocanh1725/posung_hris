<!-- Certificates & HSE View -->
<?php if (!empty($expiring)): ?>
<div class="panel" style="margin-bottom:24px; border-color: rgba(245,158,11,0.3);">
    <div class="panel-header" style="background: rgba(245,158,11,0.08);">
        <h3><i class="fas fa-exclamation-triangle" style="color:var(--warning);"></i> Chứng chỉ sắp hết hạn (90 ngày)</h3>
    </div>
    <div class="panel-body">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th style="width: 100px;">Mã NV</th>
                        <th style="width: 200px;">Họ tên</th>
                        <th style="width: 150px;">Loại CC</th>
                        <th style="width: 350px;">Tên chứng chỉ</th>
                        <th style="width: 120px;">Hết hạn</th>
                        <th style="width: 100px;">Còn lại</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($expiring as $c):
                    $daysLeft = (int)((strtotime($c->expiry_date) - time()) / 86400);
                    $cls = $daysLeft <= 30 ? 'text-danger' : 'text-warning';
                ?>
                <tr>
                    <td><strong><?= h($c->emp_code) ?></strong></td>
                    <td><?= h($c->full_name) ?></td>
                    <td><?= h($c->cert_type) ?></td>
                    <td style="white-space: normal !important; word-wrap: break-word;"><?= h($c->cert_name) ?></td>
                    <td class="<?= $cls ?>"><?= date('d/m/Y', strtotime($c->expiry_date)) ?></td>
                    <td class="<?= $cls ?>"><strong><?= $daysLeft ?> ngày</strong></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="panel">
    <div class="panel-header d-flex justify-content-between align-items-center">
        <h3><i class="fas fa-certificate"></i> Tất cả chứng chỉ & Hành nghề</h3>
        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addCertModal"><i class="fas fa-plus"></i> Cập nhật Chứng chỉ mới</button>
    </div>
    <div class="panel-body">
        
<!-- Modal Add Certificate -->
<div class="modal fade" id="addCertModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form action="<?= BASE_URL ?>/employee/saveCertificate" method="POST" enctype="multipart/form-data">
          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title"><i class="fas fa-award"></i> Cập nhật Chứng chỉ Hành nghề / Đào tạo</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nhân viên <span class="text-danger">*</span></label>
                    <input type="number" name="employee_id" class="form-control" placeholder="ID Nhân viên" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Loại chứng chỉ <span class="text-danger">*</span></label>
                    <select name="certificate_name" class="form-select" required>
                        <option value="Hành nghề CHT Công trình hạng 1">Hành nghề CHT Công trình Hạng 1</option>
                        <option value="Hành nghề CHT Công trình hạng 2">Hành nghề CHT Công trình Hạng 2</option>
                        <option value="Hành nghề Giám sát M&E">Hành nghề Giám sát Lắp đặt thiết bị / M&E</option>
                        <option value="Hành nghề Giám sát Xây dựng">Hành nghề Giám sát Xây dựng dân dụng</option>
                        <option value="Khác">Khác (Nhập tên bên dưới)</option>
                    </select>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Số quyết định / License No. <span class="text-danger">*</span></label>
                    <input type="text" name="license_no" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Cơ quan cấp <span class="text-danger">*</span></label>
                    <select name="issued_by" class="form-select" required>
                        <option value="Bộ Xây dựng">Bộ Xây dựng</option>
                        <option value="Sở Xây dựng Hà Nội">Sở Xây dựng Hà Nội</option>
                        <option value="Sở Xây dựng TP.HCM">Sở Xây dựng TP.HCM</option>
                        <option value="Cục PCCC & CNCH">Cục Cảnh sát PCCC & CNCH</option>
                    </select>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Ngày cấp <span class="text-danger">*</span></label>
                    <input type="date" name="issue_date" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Ngày hết hạn <span class="text-danger">*</span></label>
                    <input type="date" name="expiry_date" class="form-control" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Bản scan 2 mặt (Ảnh/PDF) <span class="text-danger">*</span></label>
                <input type="file" name="scan_file" class="form-control" accept=".jpg,.jpeg,.png,.pdf" required>
                <small class="text-muted">Đính kèm bản chụp 2 mặt rõ nét của chứng chỉ hành nghề để CĐT kiểm tra.</small>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
            <button type="submit" class="btn btn-primary"><i class="fas fa-upload"></i> Lưu thông tin & Tải lên</button>
          </div>
      </form>
    </div>
  </div>
</div>
        <?php if (empty($allCerts)): ?>
            <div class="empty-state"><i class="fas fa-certificate"></i><p>Chưa có chứng chỉ nào.</p></div>
        <?php else: ?>
            <div class="table-wrapper">
                <table id="certs-table">
                    <thead>
                        <tr>
                            <th style="width: 80px;">Mã NV</th>
                            <th style="width: 180px;">Họ tên</th>
                            <th style="width: 120px;">Loại</th>
                            <th style="width: 300px;">Tên chứng chỉ</th>
                            <th style="width: 100px;">Ngày cấp</th>
                            <th style="width: 100px;">Hết hạn</th>
                            <th style="width: 200px;">Cơ quan</th>
                            <th style="width: 80px;">Bắt buộc</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($allCerts as $c): ?>
                    <tr>
                        <td><strong><?= h($c->emp_code) ?></strong></td>
                        <td>
                            <a href="<?= BASE_URL ?>/employee/detail/<?= $c->employee_id ?>" class="emp-name-link">
                                <?= h($c->full_name) ?>
                            </a>
                        </td>
                        <td><?= h($c->cert_type) ?></td>
                        <td style="white-space: normal !important; word-wrap: break-word;"><?= h($c->cert_name) ?></td>
                        <td><?= $c->issue_date ? date('d/m/Y', strtotime($c->issue_date)) : '—' ?></td>
                        <td>
                            <?php if ($c->expiry_date):
                                $d = (int)((strtotime($c->expiry_date) - time()) / 86400);
                                $cls = $d <= 0 ? 'text-danger' : ($d <= 90 ? 'text-warning' : '');
                            ?>
                                <span class="<?= $cls ?>"><?= date('d/m/Y', strtotime($c->expiry_date)) ?></span>
                            <?php else: ?>—<?php endif; ?>
                        </td>
                        <td><?= h($c->issuing_authority ?? '—') ?></td>
                        <td style="text-align:center;"><?= ($c->is_mandatory_site ?? false) ? '<i class="fas fa-check-circle text-success"></i>' : '' ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.text-warning { color: var(--warning); }
.text-danger { color: var(--danger); }
.text-success { color: var(--success); }
.emp-name-link { color: var(--primary-light); font-weight: 500; }
.emp-name-link:hover { text-decoration: underline; }
</style>
