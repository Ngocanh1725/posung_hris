<?php
/**
 * View: insurance/monthly_calc.php – Bảng Tính Tiền Đóng Bảo Hiểm Chi Tiết Hàng Tháng
 */

$t = $totals;
?>

<!-- BREADCRUMB -->
<div class="breadcrumb-nav mb-3 d-print-none" style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: var(--text-muted);">
    <a href="<?= BASE_URL ?>/insurance" style="color: var(--primary); text-decoration: none;">
        <i class="fas fa-shield-alt"></i> Quản lý Bảo hiểm Xã hội
    </a>
    <i class="fas fa-chevron-right" style="font-size: 10px;"></i>
    <span>Bảng Tính Tiền Đóng BHXH Tháng <?= $month ?>/<?= $year ?></span>
</div>

<!-- TOP CONTROLS & FILTER -->
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3 d-print-none">
    <div>
        <h3 style="font-size: 18px; font-weight: 700; margin: 0; color: var(--text);">
            <i class="fas fa-calculator text-primary"></i> Bảng Tính Chi Tiết Tiền Đóng Bảo Hiểm Tháng <?= $month ?>/<?= $year ?>
        </h3>
        <span class="text-muted" style="font-size: 13px;">
            Áp dụng cho <strong><?= $count ?></strong> nhân sự có trạng thái Đang tham gia (Active)
        </span>
    </div>

    <div class="d-flex align-items-center gap-2">
        <form method="GET" action="<?= BASE_URL ?>/insurance/monthlyCalculation" class="d-flex align-items-center gap-1">
            <select name="month" class="form-select form-select-sm" style="width: 110px;" onchange="this.form.submit()">
                <?php for ($m = 1; $m <= 12; $m++): ?>
                    <option value="<?= $m ?>" <?= ($m == $month) ? 'selected' : '' ?>>Tháng <?= $m ?></option>
                <?php endfor; ?>
            </select>
            <select name="year" class="form-select form-select-sm" style="width: 95px;" onchange="this.form.submit()">
                <?php for ($y = date('Y') - 1; $y <= date('Y') + 1; $y++): ?>
                    <option value="<?= $y ?>" <?= ($y == $year) ? 'selected' : '' ?>>Năm <?= $y ?></option>
                <?php endfor; ?>
            </select>
        </form>
        <a href="<?= BASE_URL ?>/insurance/monthlyCalculation?month=<?= $month ?>&year=<?= $year ?>&export=csv" class="btn btn-sm btn-success">
            <i class="fas fa-file-excel"></i> Xuất File CSV
        </a>
        <button onclick="window.print()" class="btn btn-sm btn-secondary">
            <i class="fas fa-print"></i> In Bảng Tính
        </button>
    </div>
</div>

<!-- TOTAL SUMMARY CARDS ROW -->
<div class="row g-3 mb-4 d-print-none">
    <div class="col-md-3 col-sm-6">
        <div class="card p-3" style="border: 1px solid var(--border); border-radius: 10px;">
            <div class="text-muted" style="font-size: 11.5px; font-weight: 600; text-transform: uppercase;">Tổng Quỹ Lương Đóng BH</div>
            <div style="font-size: 20px; font-weight: 800; color: var(--text); margin-top: 3px;">
                <?= number_format($t['total_salary'], 0, ',', '.') ?> <span style="font-size: 11px; font-weight: normal; color: var(--text-muted);">đ</span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card p-3" style="border: 1px solid rgba(79, 70, 229, 0.2); background: rgba(79, 70, 229, 0.03); border-radius: 10px;">
            <div class="text-muted" style="font-size: 11.5px; font-weight: 600; text-transform: uppercase;">NLĐ Đóng (10.5%)</div>
            <div style="font-size: 20px; font-weight: 800; color: #4f46e5; margin-top: 3px;">
                <?= number_format($t['emp_total'], 0, ',', '.') ?> <span style="font-size: 11px; font-weight: normal; color: var(--text-muted);">đ</span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card p-3" style="border: 1px solid rgba(217, 119, 6, 0.2); background: rgba(217, 119, 6, 0.03); border-radius: 10px;">
            <div class="text-muted" style="font-size: 11.5px; font-weight: 600; text-transform: uppercase;">DN Nộp (22.0%)</div>
            <div style="font-size: 20px; font-weight: 800; color: #d97706; margin-top: 3px;">
                <?= number_format($t['com_total'], 0, ',', '.') ?> <span style="font-size: 11px; font-weight: normal; color: var(--text-muted);">đ</span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card p-3" style="border: 1px solid rgba(16, 185, 129, 0.2); background: rgba(16, 185, 129, 0.03); border-radius: 10px;">
            <div class="text-muted" style="font-size: 11.5px; font-weight: 600; text-transform: uppercase;">Tổng Trích Nộp (32.5%)</div>
            <div style="font-size: 20px; font-weight: 800; color: #10b981; margin-top: 3px;">
                <?= number_format($t['grand_total'], 0, ',', '.') ?> <span style="font-size: 11px; font-weight: normal; color: var(--text-muted);">đ</span>
            </div>
        </div>
    </div>
</div>

<!-- DETAILED CALCULATION TABLE -->
<div class="card" style="border: 1px solid var(--border); box-shadow: 0 2px 10px rgba(0,0,0,0.02);">
    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle mb-0" style="font-size: 12px;">
            <thead style="background: var(--bg-hover); color: var(--text-muted); text-transform: uppercase; font-size: 11px; text-align: center;">
                <tr>
                    <th rowspan="2" style="width: 40px; vertical-align: middle;">STT</th>
                    <th rowspan="2" style="width: 170px; vertical-align: middle;">Họ và tên</th>
                    <th rowspan="2" style="width: 90px; vertical-align: middle;">Mã NV</th>
                    <th rowspan="2" style="vertical-align: middle;">Phòng ban</th>
                    <th rowspan="2" style="width: 100px; vertical-align: middle;">Số sổ BHXH</th>
                    <th rowspan="2" style="width: 110px; vertical-align: middle;">Lương đóng BH</th>
                    <th colspan="4" style="background: rgba(79, 70, 229, 0.08); color: #4f46e5;">NGƯỜI LAO ĐỘNG ĐÓNG (10.5%)</th>
                    <th colspan="5" style="background: rgba(217, 119, 6, 0.08); color: #d97706;">DOANH NGHIỆP ĐÓNG (22.0%)</th>
                    <th rowspan="2" style="width: 120px; vertical-align: middle; background: rgba(16, 185, 129, 0.08); color: #059669;">TỔNG NỘP (32.5%)</th>
                </tr>
                <tr>
                    <!-- NLĐ -->
                    <th style="width: 80px;">BHXH (8%)</th>
                    <th style="width: 80px;">BHYT (1.5%)</th>
                    <th style="width: 75px;">BHTN (1%)</th>
                    <th style="width: 90px; font-weight: 700; color: #4f46e5;">Tổng NLĐ</th>
                    <!-- DN -->
                    <th style="width: 85px;">BHXH (17.5%)</th>
                    <th style="width: 80px;">BHYT (3%)</th>
                    <th style="width: 75px;">BHTN (1%)</th>
                    <th style="width: 80px;">TNLĐ (0.5%)</th>
                    <th style="width: 95px; font-weight: 700; color: #d97706;">Tổng DN</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($list)): ?>
                    <tr>
                        <td colspan="16" class="text-center py-4 text-muted">
                            Không có nhân sự nào có trạng thái Active trong tháng này.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php $stt = 1; foreach ($list as $r): ?>
                        <tr>
                            <td style="text-align: center; color: var(--text-muted);"><?= $stt++ ?></td>
                            <td>
                                <strong style="color: var(--text);"><?= htmlspecialchars($r['full_name']) ?></strong>
                            </td>
                            <td style="text-align: center;"><?= htmlspecialchars($r['emp_code']) ?></td>
                            <td><?= htmlspecialchars($r['dept_name'] ?? '') ?></td>
                            <td style="text-align: center;"><?= htmlspecialchars($r['social_insurance_no'] ?? '---') ?></td>
                            <td style="text-align: right; font-weight: 600;">
                                <?= number_format($r['salary'], 0, ',', '.') ?>
                            </td>

                            <!-- NLĐ -->
                            <td style="text-align: right;"><?= number_format($r['emp_bhxh'], 0, ',', '.') ?></td>
                            <td style="text-align: right;"><?= number_format($r['emp_bhyt'], 0, ',', '.') ?></td>
                            <td style="text-align: right;"><?= number_format($r['emp_bhtn'], 0, ',', '.') ?></td>
                            <td style="text-align: right; font-weight: 700; color: #4f46e5; background: rgba(79, 70, 229, 0.02);">
                                <?= number_format($r['emp_total'], 0, ',', '.') ?>
                            </td>

                            <!-- DN -->
                            <td style="text-align: right;"><?= number_format($r['com_bhxh'], 0, ',', '.') ?></td>
                            <td style="text-align: right;"><?= number_format($r['com_bhyt'], 0, ',', '.') ?></td>
                            <td style="text-align: right;"><?= number_format($r['com_bhtn'], 0, ',', '.') ?></td>
                            <td style="text-align: right;"><?= number_format($r['com_bhtnld'], 0, ',', '.') ?></td>
                            <td style="text-align: right; font-weight: 700; color: #d97706; background: rgba(217, 119, 6, 0.02);">
                                <?= number_format($r['com_total'], 0, ',', '.') ?>
                            </td>

                            <!-- TỔNG CỘNG -->
                            <td style="text-align: right; font-weight: 800; color: #10b981; background: rgba(16, 185, 129, 0.04);">
                                <?= number_format($r['grand_total'], 0, ',', '.') ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <!-- FOOTER ROW TỔNG CỘNG -->
                    <tr style="font-weight: 800; background: #f8fafc; font-size: 12.5px;">
                        <td colspan="5" style="text-align: center; text-transform: uppercase;">TỔNG CỘNG (<?= $count ?> nhân sự)</td>
                        <td style="text-align: right;"><?= number_format($t['total_salary'], 0, ',', '.') ?></td>
                        
                        <td style="text-align: right;"><?= number_format($t['emp_bhxh'], 0, ',', '.') ?></td>
                        <td style="text-align: right;"><?= number_format($t['emp_bhyt'], 0, ',', '.') ?></td>
                        <td style="text-align: right;"><?= number_format($t['emp_bhtn'], 0, ',', '.') ?></td>
                        <td style="text-align: right; color: #4f46e5; font-size: 13px;"><?= number_format($t['emp_total'], 0, ',', '.') ?></td>

                        <td style="text-align: right;"><?= number_format($t['com_bhxh'], 0, ',', '.') ?></td>
                        <td style="text-align: right;"><?= number_format($t['com_bhyt'], 0, ',', '.') ?></td>
                        <td style="text-align: right;"><?= number_format($t['com_bhtn'], 0, ',', '.') ?></td>
                        <td style="text-align: right;"><?= number_format($t['com_bhtnld'], 0, ',', '.') ?></td>
                        <td style="text-align: right; color: #d97706; font-size: 13px;"><?= number_format($t['com_total'], 0, ',', '.') ?></td>

                        <td style="text-align: right; color: #10b981; font-size: 14px; background: rgba(16, 185, 129, 0.1);">
                            <?= number_format($t['grand_total'], 0, ',', '.') ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
