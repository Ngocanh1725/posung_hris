<?php
/**
 * View: insurance/index.php – Dashboard Tổng quan Quản lý Bảo hiểm Xã hội & Y tế
 */

$statusBadges = [
    'Active'    => ['Đang tham gia', 'badge bg-success text-white', 'fas fa-check-circle'],
    'Suspended' => ['Tạm dừng đóng', 'badge bg-warning text-dark', 'fas fa-pause-circle'],
    'Stopped'   => ['Đã dừng đóng',  'badge bg-danger text-white', 'fas fa-times-circle'],
];

$adjTypeLabels = [
    'Tang_Moi'        => ['Báo tăng mới', 'badge bg-success text-white'],
    'Tang_Luong'      => ['Tăng mức lương', 'badge bg-primary text-white'],
    'Giam_Han'        => ['Báo giảm hẳn (Nghỉ việc)', 'badge bg-danger text-white'],
    'Giam_ThaiSan'    => ['Giảm thai sản', 'badge bg-info text-dark'],
    'Giam_OmDau'      => ['Giảm ốm đau dài ngày', 'badge bg-warning text-dark'],
    'Giam_KhongLuong' => ['Giảm nghỉ không lương', 'badge bg-secondary text-white'],
];

$claimTypeLabels = [
    'OmDau'        => ['Ốm đau', 'badge bg-warning text-dark'],
    'ThaiSan'      => ['Thai sản', 'badge bg-info text-dark'],
    'TaiNanLD_BNN' => ['Tai nạn LĐ - BNN', 'badge bg-danger text-white'],
    'DuongSuc'     => ['Dưỡng sức PHST', 'badge bg-success text-white'],
];
?>

<!-- FILTER & TITLE BAR -->
<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div>
        <h2 style="font-size: 22px; font-weight: 700; margin: 0; color: var(--text);">
            <i class="fas fa-shield-alt text-primary"></i> Quản Lý Bảo Hiểm Xã Hội (Social Insurance)
        </h2>
        <p style="margin: 4px 0 0; font-size: 13px; color: var(--text-muted);">
            Theo dõi tỷ lệ trích nộp BHXH - BHYT - BHTN, biến động tăng giảm lao động và giải quyết chế độ
        </p>
    </div>

    <!-- Month & Year Selector -->
    <form method="GET" action="<?= BASE_URL ?>/insurance" class="d-flex align-items-center gap-2">
        <select name="month" class="form-select form-select-sm" style="width: 120px;" onchange="this.form.submit()">
            <?php for ($m = 1; $m <= 12; $m++): ?>
                <option value="<?= $m ?>" <?= ($m == $month) ? 'selected' : '' ?>>Tháng <?= $m ?></option>
            <?php endfor; ?>
        </select>
        <select name="year" class="form-select form-select-sm" style="width: 100px;" onchange="this.form.submit()">
            <?php for ($y = date('Y') - 1; $y <= date('Y') + 1; $y++): ?>
                <option value="<?= $y ?>" <?= ($y == $year) ? 'selected' : '' ?>>Năm <?= $y ?></option>
            <?php endfor; ?>
        </select>
        <a href="<?= BASE_URL ?>/insurance/monthlyCalculation?month=<?= $month ?>&year=<?= $year ?>" class="btn btn-sm btn-primary" style="white-space: nowrap;">
            <i class="fas fa-calculator"></i> Bảng tính tiền đóng
        </a>
    </form>
</div>

<!-- KPI CARDS ROW -->
<div class="row g-3 mb-4">
    <!-- 1. Đang tham gia -->
    <div class="col-xl-3 col-sm-6">
        <div class="card h-100" style="border: 1px solid var(--border); box-shadow: 0 4px 16px rgba(0,0,0,0.04); border-radius: 12px;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted" style="font-size: 12px; font-weight: 600; text-transform: uppercase;">Lao động tham gia BH</div>
                        <div style="font-size: 26px; font-weight: 800; color: #10b981; line-height: 1.2; margin-top: 4px;">
                            <?= number_format($stats['active_count']) ?> <span style="font-size: 13px; font-weight: normal; color: var(--text-muted);">người</span>
                        </div>
                    </div>
                    <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(16, 185, 129, 0.12); color: #10b981; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
                <div class="mt-2 pt-2" style="border-top: 1px dashed var(--border); font-size: 12px; color: var(--text-muted);">
                    <a href="<?= BASE_URL ?>/insurance/employees" style="color: var(--primary); text-decoration: none; font-weight: 600;">
                        Xem danh sách <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Biến động trong tháng -->
    <div class="col-xl-3 col-sm-6">
        <div class="card h-100" style="border: 1px solid var(--border); box-shadow: 0 4px 16px rgba(0,0,0,0.04); border-radius: 12px;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted" style="font-size: 12px; font-weight: 600; text-transform: uppercase;">Biến động tháng <?= $month ?>/<?= $year ?></div>
                        <div style="font-size: 20px; font-weight: 800; margin-top: 4px; line-height: 1.2;">
                            <span class="text-success">+<?= $stats['increase_count'] ?> tăng</span> • <span class="text-danger">-<?= $stats['decrease_count'] ?> giảm</span>
                        </div>
                    </div>
                    <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(59, 130, 246, 0.12); color: #3b82f6; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                        <i class="fas fa-exchange-alt"></i>
                    </div>
                </div>
                <div class="mt-2 pt-2" style="border-top: 1px dashed var(--border); font-size: 12px; color: var(--text-muted);">
                    <a href="<?= BASE_URL ?>/insurance/adjust?month=<?= sprintf('%04d-%02d', $year, $month) ?>" style="color: var(--primary); text-decoration: none; font-weight: 600;">
                        Báo tăng/giảm D02-TS <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Tổng quỹ lương đóng BH -->
    <div class="col-xl-3 col-sm-6">
        <div class="card h-100" style="border: 1px solid var(--border); box-shadow: 0 4px 16px rgba(0,0,0,0.04); border-radius: 12px;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted" style="font-size: 12px; font-weight: 600; text-transform: uppercase;">Quỹ lương trích đóng</div>
                        <div style="font-size: 22px; font-weight: 800; color: #4f46e5; line-height: 1.2; margin-top: 4px;">
                            <?= number_format($stats['total_insurance_fund'], 0, ',', '.') ?> <span style="font-size: 12px; font-weight: normal; color: var(--text-muted);">đ</span>
                        </div>
                    </div>
                    <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(79, 70, 229, 0.12); color: #4f46e5; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                </div>
                <div class="mt-2 pt-2" style="border-top: 1px dashed var(--border); font-size: 12px; color: var(--text-muted);">
                    Tổng mức lương cơ bản đóng bảo hiểm
                </div>
            </div>
        </div>
    </div>

    <!-- 4. Tổng tiền trích nộp tháng -->
    <div class="col-xl-3 col-sm-6">
        <div class="card h-100" style="border: 1px solid var(--border); box-shadow: 0 4px 16px rgba(0,0,0,0.04); border-radius: 12px;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted" style="font-size: 12px; font-weight: 600; text-transform: uppercase;">Tổng nộp BH tháng này (32.5%)</div>
                        <div style="font-size: 22px; font-weight: 800; color: #d97706; line-height: 1.2; margin-top: 4px;">
                            <?= number_format($stats['total_insurance_est'], 0, ',', '.') ?> <span style="font-size: 12px; font-weight: normal; color: var(--text-muted);">đ</span>
                        </div>
                    </div>
                    <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(245, 158, 11, 0.12); color: #d97706; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                </div>
                <div class="mt-2 pt-2" style="border-top: 1px dashed var(--border); font-size: 12px; color: var(--text-muted);">
                    DN: <strong><?= number_format($stats['company_cost_est'], 0, ',', '.') ?>đ</strong> • NLĐ: <strong><?= number_format($stats['emp_deduction_est'], 0, ',', '.') ?>đ</strong>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ACTION LINKS BAR -->
<div class="d-flex gap-2 flex-wrap mb-4">
    <a href="<?= BASE_URL ?>/insurance/employees" class="btn btn-outline-primary">
        <i class="fas fa-user-shield"></i> Quản lý Hồ sơ BH Nhân sự
    </a>
    <a href="<?= BASE_URL ?>/insurance/adjust" class="btn btn-outline-primary">
        <i class="fas fa-chart-line"></i> Biến động Báo Tăng/Giảm (D02-TS)
    </a>
    <a href="<?= BASE_URL ?>/insurance/claims" class="btn btn-outline-primary">
        <i class="fas fa-medkit"></i> Chế độ Ốm đau, Thai sản (C70a-HD)
    </a>
    <a href="<?= BASE_URL ?>/insurance/rates" class="btn btn-outline-secondary">
        <i class="fas fa-percentage"></i> Cấu hình Tỷ lệ đóng BH
    </a>
    <a href="<?= BASE_URL ?>/insurance/report" class="btn btn-outline-secondary">
        <i class="fas fa-file-export"></i> Báo cáo Tổng hợp & Xuất File
    </a>
</div>

<!-- SPLIT ROW: RATES & RECENT ADJUSTMENTS -->
<div class="row g-4 mb-4">
    <!-- CƠ CẤU TỶ LỆ TRÍCH ĐÓNG BẢO HIỂM -->
    <div class="col-lg-5">
        <div class="card h-100" style="border: 1px solid var(--border); box-shadow: 0 2px 10px rgba(0,0,0,0.03);">
            <div class="card-header d-flex justify-content-between align-items-center" style="background: var(--bg-hover); padding: 14px 18px; border-bottom: 1px solid var(--border);">
                <h5 style="margin: 0; font-size: 14.5px; font-weight: 700; color: var(--text);">
                    <i class="fas fa-pie-chart text-primary"></i> Cơ Cấu Tỷ Lệ Đóng Bảo Hiểm (Năm <?= $year ?>)
                </h5>
                <a href="<?= BASE_URL ?>/insurance/rates" class="btn btn-sm btn-ghost" style="font-size: 12px;">
                    <i class="fas fa-cog"></i> Sửa
                </a>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                    <thead style="background: var(--bg-hover); color: var(--text-muted); font-size: 11.5px; text-transform: uppercase;">
                        <tr>
                            <th>Loại Bảo Hiểm</th>
                            <th style="text-align: center; width: 90px;">NLĐ Đóng</th>
                            <th style="text-align: center; width: 90px;">DN Đóng</th>
                            <th style="text-align: center; width: 90px;">Tổng Cộng</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stats['rates']['rates_by_type'] as $type => $r): ?>
                            <tr>
                                <td>
                                    <strong><?= $type ?></strong>
                                    <div style="font-size: 11.5px; color: var(--text-muted);"><?= htmlspecialchars($r['name']) ?></div>
                                </td>
                                <td style="text-align: center; font-weight: 600; color: #4f46e5;"><?= number_format($r['emp'], 1) ?>%</td>
                                <td style="text-align: center; font-weight: 600; color: #d97706;"><?= number_format($r['com'], 1) ?>%</td>
                                <td style="text-align: center; font-weight: 700; color: var(--text);"><?= number_format($r['emp'] + $r['com'], 1) ?>%</td>
                            </tr>
                        <?php endforeach; ?>
                        <tr style="background: rgba(79, 70, 229, 0.05); font-weight: 700;">
                            <td>TỔNG TỶ LỆ TRÍCH ĐÓNG</td>
                            <td style="text-align: center; color: #4f46e5; font-size: 14px;"><?= number_format($stats['rates']['employee_total_rate'], 1) ?>%</td>
                            <td style="text-align: center; color: #d97706; font-size: 14px;"><?= number_format($stats['rates']['company_total_rate'], 1) ?>%</td>
                            <td style="text-align: center; color: #10b981; font-size: 15px;"><?= number_format($stats['rates']['total_rate'], 1) ?>%</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- BIẾN ĐỘNG LAO ĐỘNG GẦN ĐÂY (D02-TS) -->
    <div class="col-lg-7">
        <div class="card h-100" style="border: 1px solid var(--border); box-shadow: 0 2px 10px rgba(0,0,0,0.03);">
            <div class="card-header d-flex justify-content-between align-items-center" style="background: var(--bg-hover); padding: 14px 18px; border-bottom: 1px solid var(--border);">
                <h5 style="margin: 0; font-size: 14.5px; font-weight: 700; color: var(--text);">
                    <i class="fas fa-history text-primary"></i> Biến Động Lao Động Gần Nhất (Tháng <?= $month ?>/<?= $year ?>)
                </h5>
                <a href="<?= BASE_URL ?>/insurance/adjust" class="btn btn-sm btn-ghost" style="font-size: 12px;">
                    Xem tất cả <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($recentAdjustments)): ?>
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-clipboard-check fa-2x mb-2 text-secondary" style="opacity: 0.5;"></i>
                        <div>Chưa có bản ghi biến động nào trong tháng <?= $month ?>/<?= $year ?>.</div>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                            <thead style="background: var(--bg-hover); color: var(--text-muted); font-size: 11.5px; text-transform: uppercase;">
                                <tr>
                                    <th>Nhân viên</th>
                                    <th>Phân loại biến động</th>
                                    <th style="text-align: right;">Lương mới</th>
                                    <th>Hiệu lực</th>
                                    <th style="text-align: center;">Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentAdjustments as $adj): ?>
                                    <tr>
                                        <td>
                                            <strong style="color: var(--text);"><?= htmlspecialchars($adj['full_name']) ?></strong>
                                            <div style="font-size: 11.5px; color: var(--text-muted);"><?= htmlspecialchars($adj['emp_code']) ?> • <?= htmlspecialchars($adj['dept_name'] ?? '') ?></div>
                                        </td>
                                        <td>
                                            <?php $typeMeta = $adjTypeLabels[$adj['adjustment_type']] ?? [$adj['adjustment_type'], 'badge bg-secondary']; ?>
                                            <span class="<?= $typeMeta[1] ?>" style="font-size: 11px;"><?= $typeMeta[0] ?></span>
                                        </td>
                                        <td style="text-align: right; font-weight: 600;">
                                            <?= number_format((float)$adj['new_salary'], 0, ',', '.') ?>đ
                                        </td>
                                        <td><?= date('d/m/Y', strtotime($adj['effective_date'])) ?></td>
                                        <td style="text-align: center;">
                                            <?php if ($adj['status'] === 'Approved'): ?>
                                                <span class="badge bg-success" style="font-size: 10px;">Đã duyệt</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning text-dark" style="font-size: 10px;">Chờ gửi</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- HỒ SƠ HƯỞNG CHẾ ĐỘ GẦN NHẤT (C70a-HD) -->
<div class="card mb-4" style="border: 1px solid var(--border); box-shadow: 0 2px 10px rgba(0,0,0,0.03);">
    <div class="card-header d-flex justify-content-between align-items-center" style="background: var(--bg-hover); padding: 14px 18px; border-bottom: 1px solid var(--border);">
        <h5 style="margin: 0; font-size: 14.5px; font-weight: 700; color: var(--text);">
            <i class="fas fa-medkit text-danger"></i> Hồ Sơ Giải Quyết Chế Độ Bảo Hiểm Gần Đây (Năm <?= $year ?>)
        </h5>
        <a href="<?= BASE_URL ?>/insurance/claims" class="btn btn-sm btn-ghost" style="font-size: 12px;">
            Xem tất cả hồ sơ <i class="fas fa-arrow-right"></i>
        </a>
    </div>
    <div class="card-body p-0">
        <?php if (empty($recentClaims)): ?>
            <div class="text-center py-4 text-muted">
                <i class="fas fa-heartbeat fa-2x mb-2 text-secondary" style="opacity: 0.5;"></i>
                <div>Chưa có hồ sơ thanh toán chế độ nào trong năm <?= $year ?>.</div>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                    <thead style="background: var(--bg-hover); color: var(--text-muted); font-size: 11.5px; text-transform: uppercase;">
                        <tr>
                            <th>Nhân viên</th>
                            <th>Chế độ hưởng</th>
                            <th>Thời gian nghỉ</th>
                            <th style="text-align: center;">Số ngày</th>
                            <th style="text-align: right;">Số tiền trợ cấp</th>
                            <th>Chứng từ</th>
                            <th style="text-align: center;">Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentClaims as $c): ?>
                            <tr>
                                <td>
                                    <strong style="color: var(--text);"><?= htmlspecialchars($c['full_name']) ?></strong>
                                    <div style="font-size: 11.5px; color: var(--text-muted);"><?= htmlspecialchars($c['emp_code']) ?></div>
                                </td>
                                <td>
                                    <?php $cMeta = $claimTypeLabels[$c['claim_type']] ?? [$c['claim_type'], 'badge bg-secondary']; ?>
                                    <span class="<?= $cMeta[1] ?>" style="font-size: 11px;"><?= $cMeta[0] ?></span>
                                </td>
                                <td><?= date('d/m/Y', strtotime($c['from_date'])) ?> – <?= date('d/m/Y', strtotime($c['to_date'])) ?></td>
                                <td style="text-align: center; font-weight: 600;"><?= $c['leave_days'] ?> ngày</td>
                                <td style="text-align: right; font-weight: 700; color: #10b981;">
                                    <?= number_format((float)$c['claim_amount'], 0, ',', '.') ?>đ
                                </td>
                                <td><?= htmlspecialchars($c['document_ref'] ?? '---') ?></td>
                                <td style="text-align: center;">
                                    <?php if ($c['status'] === 'Paid'): ?>
                                        <span class="badge bg-success" style="font-size: 10px;">Đã chi trả</span>
                                    <?php elseif ($c['status'] === 'Approved'): ?>
                                        <span class="badge bg-primary text-white" style="font-size: 10px;">BHXH đã duyệt</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark" style="font-size: 10px;">Chờ cơ quan BH</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
