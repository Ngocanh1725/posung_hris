<?php
/**
 * View: insurance/employees.php – Danh sách Nhân sự Tham gia Bảo hiểm Xã hội
 */

$statusBadges = [
    'Active'    => ['Đang tham gia', 'badge bg-success text-white', 'fas fa-check-circle'],
    'Suspended' => ['Tạm dừng',      'badge bg-warning text-dark',  'fas fa-pause-circle'],
    'Stopped'   => ['Đã dừng đóng',  'badge bg-danger text-white',  'fas fa-times-circle'],
];
?>

<!-- BREADCRUMB -->
<div class="breadcrumb-nav mb-3" style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: var(--text-muted);">
    <a href="<?= BASE_URL ?>/insurance" style="color: var(--primary); text-decoration: none;">
        <i class="fas fa-shield-alt"></i> Quản lý Bảo hiểm Xã hội
    </a>
    <i class="fas fa-chevron-right" style="font-size: 10px;"></i>
    <span>Danh sách Nhân sự Tham gia Bảo hiểm</span>
</div>

<!-- TOP ACTION TOOLBAR -->
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div>
        <h3 style="font-size: 18px; font-weight: 700; margin: 0; color: var(--text);">
            <i class="fas fa-id-card text-primary"></i> Danh Sách Nhân Sự Tham Gia Bảo Hiểm
        </h3>
        <span class="text-muted" style="font-size: 13px;">
            Tổng cộng: <strong><?= count($employees) ?></strong> nhân sự trong danh sách
        </span>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= BASE_URL ?>/insurance/adjust" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-exchange-alt"></i> Báo Tăng/Giảm (D02-TS)
        </a>
        <a href="<?= BASE_URL ?>/insurance/monthlyCalculation" class="btn btn-primary btn-sm">
            <i class="fas fa-calculator"></i> Bảng Tính Tiền Đóng Tháng
        </a>
    </div>
</div>

<!-- FILTER CARD -->
<div class="card mb-3" style="border: 1px solid var(--border);">
    <div class="card-body p-3">
        <form method="GET" action="<?= BASE_URL ?>/insurance/employees" class="row g-2 align-items-center">
            <!-- Search -->
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text" style="background: var(--bg-hover); border-color: var(--border);">
                        <i class="fas fa-search text-muted"></i>
                    </span>
                    <input type="text" name="search" class="form-control" 
                           placeholder="Tìm kiếm họ tên, mã NV, số sổ BHXH, thẻ BHYT..." 
                           value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
                </div>
            </div>

            <!-- Dept -->
            <div class="col-md-4">
                <select name="dept_id" class="form-select">
                    <option value="">-- Tất cả Phòng ban --</option>
                    <?php foreach ($departments as $dept): ?>
                        <option value="<?= $dept->id ?>" <?= ($filters['dept_id'] == $dept->id) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($dept->dept_name) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Status -->
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">-- Trạng thái BH --</option>
                    <option value="Active" <?= ($filters['status'] === 'Active') ? 'selected' : '' ?>>Đang tham gia</option>
                    <option value="Suspended" <?= ($filters['status'] === 'Suspended') ? 'selected' : '' ?>>Tạm dừng đóng</option>
                    <option value="Stopped" <?= ($filters['status'] === 'Stopped') ? 'selected' : '' ?>>Đã dừng đóng</option>
                    <option value="Not_Registered" <?= ($filters['status'] === 'Not_Registered') ? 'selected' : '' ?>>Chưa đăng ký BH</option>
                </select>
            </div>

            <!-- Buttons -->
            <div class="col-md-1 d-flex gap-1">
                <button type="submit" class="btn btn-primary w-100" title="Lọc">
                    <i class="fas fa-filter"></i>
                </button>
                <?php if (!empty($filters['search']) || !empty($filters['dept_id']) || !empty($filters['status'])): ?>
                    <a href="<?= BASE_URL ?>/insurance/employees" class="btn btn-ghost" title="Xóa bộ lọc" style="border: 1px solid var(--border);">
                        <i class="fas fa-redo"></i>
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- TABLE CARD -->
<div class="card" style="border: 1px solid var(--border); box-shadow: 0 2px 10px rgba(0,0,0,0.02);">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
            <thead style="background: var(--bg-hover); color: var(--text-muted); font-size: 11.5px; text-transform: uppercase;">
                <tr>
                    <th style="width: 50px; text-align: center;">STT</th>
                    <th style="width: 240px;">Nhân sự</th>
                    <th>Phòng ban & Vị trí</th>
                    <th style="width: 130px;">Số sổ BHXH</th>
                    <th style="width: 220px;">Thẻ BHYT & Nơi KCB</th>
                    <th style="text-align: right; width: 130px;">Lương đóng BH</th>
                    <th style="text-align: center; width: 110px;">Ngày bắt đầu</th>
                    <th style="text-align: center; width: 120px;">Trạng thái</th>
                    <th style="text-align: right; width: 120px;">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($employees)): ?>
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <i class="fas fa-user-slash fa-3x mb-3 text-secondary" style="opacity: 0.5;"></i>
                            <div>Không tìm thấy nhân viên nào phù hợp với điều kiện tìm kiếm.</div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php $stt = 1; foreach ($employees as $e): ?>
                        <tr>
                            <td style="text-align: center; color: var(--text-muted);"><?= $stt++ ?></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div style="width: 36px; height: 36px; border-radius: 50%; background: #e0e7ff; color: #4338ca; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 14px; flex-shrink: 0; overflow: hidden;">
                                        <?php if (!empty($e['avatar_path'])): ?>
                                            <img src="<?= BASE_URL . '/' . htmlspecialchars($e['avatar_path']) ?>" alt="" style="width: 100%; height: 100%; object-fit: cover;">
                                        <?php else: ?>
                                            <?= mb_strtoupper(mb_substr($e['full_name'], 0, 1, 'UTF-8'), 'UTF-8') ?>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <div style="font-weight: 600; color: var(--text);">
                                            <a href="<?= BASE_URL ?>/insurance/register/<?= $e['employee_id'] ?>" style="color: inherit; text-decoration: none;">
                                                <?= htmlspecialchars($e['full_name']) ?>
                                            </a>
                                        </div>
                                        <div style="font-size: 11.5px; color: var(--text-muted);">
                                            Mã: <span class="badge bg-light text-dark" style="border: 1px solid var(--border);"><?= htmlspecialchars($e['emp_code']) ?></span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div style="font-weight: 500; color: var(--text);"><?= htmlspecialchars($e['dept_name'] ?? 'Chưa gán') ?></div>
                                <div style="font-size: 11.5px; color: var(--text-muted);"><?= htmlspecialchars($e['pos_title'] ?? '---') ?></div>
                            </td>
                            <td>
                                <?php if (!empty($e['social_insurance_no'])): ?>
                                    <strong style="color: #4f46e5;"><?= htmlspecialchars($e['social_insurance_no']) ?></strong>
                                <?php else: ?>
                                    <span class="text-muted" style="font-style: italic;">Chưa có sổ</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($e['health_insurance_no'])): ?>
                                    <div style="font-weight: 600; color: var(--text);"><?= htmlspecialchars($e['health_insurance_no']) ?></div>
                                    <div style="font-size: 11px; color: var(--text-muted);" title="<?= htmlspecialchars($e['hospital_name'] ?? '') ?>">
                                        <i class="fas fa-hospital-alt text-primary"></i> <?= htmlspecialchars(mb_substr($e['hospital_name'] ?? '', 0, 25, 'UTF-8')) ?><?= mb_strlen($e['hospital_name'] ?? '', 'UTF-8') > 25 ? '...' : '' ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted" style="font-style: italic;">Chưa cấp thẻ BHYT</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: right;">
                                <?php if (!empty($e['insurance_salary'])): ?>
                                    <strong style="color: #10b981; font-size: 13.5px;">
                                        <?= number_format((float)$e['insurance_salary'], 0, ',', '.') ?>đ
                                    </strong>
                                <?php else: ?>
                                    <span class="text-muted">---</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center; font-size: 12px;">
                                <?= !empty($e['ins_start_date']) ? date('d/m/Y', strtotime($e['ins_start_date'])) : '---' ?>
                            </td>
                            <td style="text-align: center;">
                                <?php if (empty($e['ins_status'])): ?>
                                    <span class="badge bg-secondary" style="font-size: 11px;">Chưa đăng ký</span>
                                <?php else: ?>
                                    <?php $meta = $statusBadges[$e['ins_status']] ?? [$e['ins_status'], 'badge bg-secondary', '']; ?>
                                    <span class="<?= $meta[1] ?>" style="font-size: 11px; padding: 4px 8px; border-radius: 12px;">
                                        <i class="<?= $meta[2] ?>"></i> <?= $meta[0] ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: right;">
                                <div class="d-flex justify-content-end gap-1">
                                    <a href="<?= BASE_URL ?>/insurance/register/<?= $e['employee_id'] ?>" 
                                       class="btn btn-sm btn-outline-primary" 
                                       title="Chỉnh sửa hồ sơ bảo hiểm">
                                        <i class="fas fa-edit"></i> Hồ sơ
                                    </a>
                                    <a href="<?= BASE_URL ?>/employee/detail/<?= $e['employee_id'] ?>" 
                                       target="_blank" 
                                       class="btn btn-sm btn-ghost" 
                                       style="border: 1px solid var(--border);" 
                                       title="Xem Hồ sơ 360°">
                                        <i class="fas fa-user"></i>
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
