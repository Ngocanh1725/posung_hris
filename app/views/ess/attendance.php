<?php
require_once APP_ROOT . '/views/ess/layout/header.php';
?>

<div class="container-xl">
    
    <!-- Header Page & Bộ lọc Tháng -->
    <div class="card-custom p-3 mb-4">
        <div class="row align-items-center g-3">
            <div class="col-md-6">
                <h5 class="fw-bold mb-0 text-dark">
                    <i class="fa-solid fa-calendar-check text-primary me-2"></i> Lịch sử chấm công cá nhân
                </h5>
                <small class="text-muted">Theo dõi giờ vào/ra, số giờ công chuẩn và làm thêm giờ (OT).</small>
            </div>
            <div class="col-md-6">
                <form action="<?= BASE_URL ?>/ess/attendance" method="GET" class="d-flex justify-content-md-end align-items-center gap-2">
                    <span class="small text-muted fw-semibold">Chọn tháng:</span>
                    <select name="month" class="form-select form-select-sm" style="width: 100px;">
                        <?php for ($m = 1; $m <= 12; $m++): ?>
                            <option value="<?= $m ?>" <?= $selectedMonth == $m ? 'selected' : '' ?>>Tháng <?= $m ?></option>
                        <?php endfor; ?>
                    </select>
                    <select name="year" class="form-select form-select-sm" style="width: 100px;">
                        <?php for ($y = date('Y') - 1; $y <= date('Y') + 1; $y++): ?>
                            <option value="<?= $y ?>" <?= $selectedYear == $y ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                    <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3">
                        <i class="fa-solid fa-filter me-1"></i> Xem
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- 4 Thẻ Thống kê Chấm công Tháng -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="metric-card blue">
                <div class="metric-icon">
                    <i class="fa-solid fa-calendar-days"></i>
                </div>
                <div class="metric-value text-primary"><?= number_format($summary['total_worked_days']) ?></div>
                <div class="metric-label">Ngày công thực tế</div>
                <div class="metric-sub">Tháng <?= $selectedMonth ?>/<?= $selectedYear ?></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="metric-card emerald">
                <div class="metric-icon">
                    <i class="fa-solid fa-clock"></i>
                </div>
                <div class="metric-value text-success"><?= number_format($summary['total_standard_hours'], 1) ?>h</div>
                <div class="metric-label">Tổng giờ làm chuẩn</div>
                <div class="metric-sub">Tiêu chuẩn: 8h / ngày</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="metric-card amber">
                <div class="metric-icon">
                    <i class="fa-solid fa-bolt"></i>
                </div>
                <div class="metric-value text-warning"><?= number_format($summary['total_ot_hours'], 1) ?>h</div>
                <div class="metric-label">Làm thêm giờ (OT)</div>
                <div class="metric-sub">Bao gồm OT thường & Lễ/CN</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="metric-card rose">
                <div class="metric-icon">
                    <i class="fa-solid fa-pump-medical"></i>
                </div>
                <div class="metric-value text-danger"><?= number_format($summary['cleanroom_days']) ?></div>
                <div class="metric-label">Công phòng sạch</div>
                <div class="metric-sub">Hưởng phụ cấp Cleanroom</div>
            </div>
        </div>
    </div>

    <!-- Bảng Chi tiết Chấm công Từng Ngày -->
    <div class="card-custom">
        <div class="card-custom-header">
            <h5 class="card-custom-title">
                <i class="fa-solid fa-list-ol text-primary"></i> Chi tiết bảng công tháng <?= $selectedMonth ?>/<?= $selectedYear ?>
            </h5>
            <span class="badge bg-light text-muted border">
                Tổng cộng: <?= count($attendanceRecords) ?> lượt ghi nhận
            </span>
        </div>
        <div class="card-custom-body p-0">
            <?php if (!empty($attendanceRecords)): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th>Ngày</th>
                                <th>Thứ</th>
                                <th>Ca làm việc</th>
                                <th>Giờ vào (In)</th>
                                <th>Giờ ra (Out)</th>
                                <th class="text-center">Giờ chuẩn</th>
                                <th class="text-center">Giờ OT</th>
                                <th>Môi trường / Thiết bị</th>
                                <th class="text-center">Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $dayNames = ['Chủ nhật', 'Thứ hai', 'Thứ ba', 'Thứ tư', 'Thứ năm', 'Thứ sáu', 'Thứ bảy'];
                            foreach ($attendanceRecords as $att): 
                                $dayOfWeek = date('w', strtotime($att['work_date']));
                                $isSunday = ($dayOfWeek == 0);
                                $totalOt = (float)($att['ot_hours'] ?? 0) + (float)($att['ot_normal_hours'] ?? 0) + (float)($att['ot_sunday_hours'] ?? 0) + (float)($att['ot_holiday_hours'] ?? 0);
                            ?>
                                <tr class="<?= $isSunday ? 'table-warning-subtle' : '' ?>">
                                    <td class="fw-bold font-monospace">
                                        <?= date('d/m/Y', strtotime($att['work_date'])) ?>
                                    </td>
                                    <td>
                                        <span class="badge <?= $isSunday ? 'bg-danger text-white' : 'bg-light text-dark border' ?>">
                                            <?= $dayNames[$dayOfWeek] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="fw-semibold text-dark">
                                            <?= htmlspecialchars($att['shift_name'] ?? ($att['shift_type'] ?? 'Hành chính')) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (!empty($att['check_in'])): ?>
                                            <span class="fw-bold text-success font-monospace">
                                                <?= date('H:i', strtotime($att['check_in'])) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($att['check_out'])): ?>
                                            <span class="fw-bold text-primary font-monospace">
                                                <?= date('H:i', strtotime($att['check_out'])) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center fw-semibold text-dark">
                                        <?= number_format((float)($att['standard_hours'] ?? 0), 1) ?>h
                                    </td>
                                    <td class="text-center">
                                        <?php if ($totalOt > 0): ?>
                                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle">
                                                +<?= number_format($totalOt, 1) ?>h
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">0.0h</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-1">
                                            <?php if (!empty($att['is_cleanroom']) || ($att['work_environment'] ?? '') === 'cleanroom'): ?>
                                                <span class="badge bg-info-subtle text-info border border-info-subtle" title="Phòng sạch Cleanroom">
                                                    Cleanroom
                                                </span>
                                            <?php endif; ?>
                                            <small class="text-muted">
                                                <?= htmlspecialchars($att['checkin_device_type'] ?? 'SITE_GPS') ?>
                                            </small>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <?php 
                                            $st = $att['status'] ?? 'Approved';
                                            $badgeClass = match($st) {
                                                'Approved' => 'bg-success-subtle text-success border border-success-subtle',
                                                'Pending'  => 'bg-warning-subtle text-warning border border-warning-subtle',
                                                default    => 'bg-secondary-subtle text-secondary'
                                            };
                                        ?>
                                        <span class="badge <?= $badgeClass ?>">
                                            <?= htmlspecialchars($st) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5 text-muted small">
                    <i class="fa-solid fa-calendar-xmark fa-3x text-secondary opacity-50 mb-3"></i>
                    <p>Không có dữ liệu chấm công cho tháng <?= $selectedMonth ?>/<?= $selectedYear ?>.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<?php require_once APP_ROOT . '/views/ess/layout/footer.php'; ?>
