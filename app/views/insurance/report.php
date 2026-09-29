<?php
/**
 * View: insurance/report.php – Báo cáo BHXH Chuẩn Mẫu D02-TS & C70a-HD
 */

$parts = explode('-', $month);
$yearStr = $parts[0] ?? date('Y');
$monthStr = $parts[1] ?? date('m');
?>

<!-- BREADCRUMB -->
<div class="breadcrumb-nav mb-3 d-print-none" style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: var(--text-muted);">
    <a href="<?= BASE_URL ?>/insurance" style="color: var(--primary); text-decoration: none;">
        <i class="fas fa-shield-alt"></i> Quản lý Bảo hiểm Xã hội
    </a>
    <i class="fas fa-chevron-right" style="font-size: 10px;"></i>
    <span>Báo cáo Tổng hợp BHXH</span>
</div>

<!-- CONTROLS & EXPORT TOOLBAR -->
<div class="card mb-4 d-print-none" style="border: 1px solid var(--border);">
    <div class="card-body p-3">
        <form method="GET" action="<?= BASE_URL ?>/insurance/report" class="row g-2 align-items-center justify-content-between">
            <div class="col-md-7 d-flex align-items-center gap-2 flex-wrap">
                <!-- Chọn mẫu -->
                <div class="btn-group" role="group">
                    <a href="<?= BASE_URL ?>/insurance/report?type=D02TS&month=<?= $month ?>" 
                       class="btn btn-sm <?= ($reportType === 'D02TS') ? 'btn-primary' : 'btn-outline-primary' ?>">
                        <i class="fas fa-file-contract"></i> Mẫu D02-TS (Biến động Lao động)
                    </a>
                    <a href="<?= BASE_URL ?>/insurance/report?type=C70aHD&month=<?= $month ?>" 
                       class="btn btn-sm <?= ($reportType === 'C70aHD') ? 'btn-primary' : 'btn-outline-primary' ?>">
                        <i class="fas fa-file-medical"></i> Mẫu C70a-HD (Chế độ Ốm đau, Thai sản)
                    </a>
                </div>

                <!-- Chọn tháng -->
                <input type="month" name="month" class="form-control form-control-sm" style="width: 150px;" 
                       value="<?= htmlspecialchars($month) ?>" onchange="this.form.submit()">
                <input type="hidden" name="type" value="<?= htmlspecialchars($reportType) ?>">
            </div>

            <div class="col-md-5 d-flex justify-content-end gap-2">
                <a href="<?= BASE_URL ?>/insurance/report?type=<?= $reportType ?>&month=<?= $month ?>&export=csv" class="btn btn-sm btn-success">
                    <i class="fas fa-file-excel"></i> Xuất File Excel / CSV
                </a>
                <button type="button" onclick="window.print()" class="btn btn-sm btn-secondary">
                    <i class="fas fa-print"></i> In Báo Cáo
                </button>
            </div>
        </form>
    </div>
</div>

<!-- KHUNG BÁO CÁO MẪU IN -->
<div class="card" style="border: 1px solid var(--border); box-shadow: 0 4px 20px rgba(0,0,0,0.04); background: #fff; padding: 25px 35px; border-radius: 8px;">

    <!-- TIÊU NGỮ & TÊN ĐƠN VỊ -->
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <div style="font-weight: 700; font-size: 13.5px; text-transform: uppercase;">CÔNG TY TNHH POSUNG VINA</div>
            <div style="font-size: 12.5px; color: #555;">Mã đơn vị: <strong>TN0491B</strong></div>
            <div style="font-size: 12.5px; color: #555;">Cơ quan BHXH: BHXH TP. Hải Phòng</div>
        </div>
        <div class="text-center">
            <div style="font-weight: 700; font-size: 13px; text-transform: uppercase;">CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM</div>
            <div style="font-weight: 600; font-size: 12.5px; text-decoration: underline;">Độc lập - Tự do - Hạnh phúc</div>
            <div style="font-size: 11.5px; margin-top: 4px; font-style: italic;">Hải Phòng, ngày <?= date('d') ?> tháng <?= date('m') ?> năm <?= date('Y') ?></div>
        </div>
    </div>

    <?php if ($reportType === 'D02TS'): ?>
        <!-- ══════════════════════════════════════════════════════════
             MẪU D02-TS: DANH SÁCH LAO ĐỘNG THAM GIA BHXH, BHYT, BHTN
             ══════════════════════════════════════════════════════════ -->
        <div class="text-center mb-4">
            <h3 style="font-size: 17px; font-weight: 800; margin: 0; text-transform: uppercase;">
                DANH SÁCH LAO ĐỘNG THAM GIA BHXH, BHYT, BHTN, BHTNLĐ-BNN
            </h3>
            <div style="font-size: 13.5px; font-weight: 600; margin-top: 4px;">
                Tháng <?= $monthStr ?> năm <?= $yearStr ?>
            </div>
            <div style="font-size: 12px; color: #666; font-style: italic;">(Ban hành kèm theo Quyết định số 595/QĐ-BHXH ngày 14/4/2017 của BHXH Việt Nam)</div>
        </div>

        <!-- BẢNG BIẾN ĐỘNG TĂNG -->
        <h5 style="font-size: 14px; font-weight: 700; margin-bottom: 8px; color: #1e3a8a;">
            I. DANH SÁCH LAO ĐỘNG TĂNG (TĂNG MỚI, TĂNG MỨC ĐÓNG) (<?= count($data['increases']) ?> người)
        </h5>
        <div class="table-responsive mb-4">
            <table class="table table-bordered align-middle" style="font-size: 12px; border-color: #333;">
                <thead style="background: #f1f5f9; text-align: center; font-weight: 700;">
                    <tr>
                        <th style="width: 40px;">STT</th>
                        <th style="width: 170px;">Họ và tên</th>
                        <th style="width: 110px;">Mã số BHXH</th>
                        <th>Chức vụ / Vị trí</th>
                        <th style="width: 120px;">Mức tiền lương cũ</th>
                        <th style="width: 120px;">Mức tiền lương mới</th>
                        <th style="width: 100px;">Từ tháng năm</th>
                        <th>Ghi chú / Căn cứ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data['increases'])): ?>
                        <tr><td colspan="8" class="text-center py-2 text-muted">Không có biến động tăng trong tháng.</td></tr>
                    <?php else: ?>
                        <?php $i = 1; foreach ($data['increases'] as $inc): ?>
                            <tr>
                                <td style="text-align: center;"><?= $i++ ?></td>
                                <td><strong><?= htmlspecialchars($inc['full_name']) ?></strong></td>
                                <td style="text-align: center;"><?= htmlspecialchars($inc['social_insurance_no'] ?? 'Chưa cấp') ?></td>
                                <td><?= htmlspecialchars($inc['pos_title'] ?? '') ?> (<?= htmlspecialchars($inc['dept_name'] ?? '') ?>)</td>
                                <td style="text-align: right;"><?= $inc['old_salary'] > 0 ? number_format((float)$inc['old_salary'], 0, ',', '.') : '---' ?></td>
                                <td style="text-align: right; font-weight: 700;"><?= number_format((float)$inc['new_salary'], 0, ',', '.') ?></td>
                                <td style="text-align: center;"><?= date('m/Y', strtotime($inc['effective_date'])) ?></td>
                                <td><?= htmlspecialchars($inc['reason']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- BẢNG BIẾN ĐỘNG GIẢM -->
        <h5 style="font-size: 14px; font-weight: 700; margin-bottom: 8px; color: #b91c1c;">
            II. DANH SÁCH LAO ĐỘNG GIẢM (GIẢM HẲN, GIẢM THAI SẢN, GIẢM ỐM ĐAU) (<?= count($data['decreases']) ?> người)
        </h5>
        <div class="table-responsive mb-4">
            <table class="table table-bordered align-middle" style="font-size: 12px; border-color: #333;">
                <thead style="background: #f1f5f9; text-align: center; font-weight: 700;">
                    <tr>
                        <th style="width: 40px;">STT</th>
                        <th style="width: 170px;">Họ và tên</th>
                        <th style="width: 110px;">Mã số BHXH</th>
                        <th>Chức danh / Phòng ban</th>
                        <th style="width: 120px;">Mức tiền lương đóng</th>
                        <th style="width: 100px;">Từ tháng năm</th>
                        <th>Lý do giảm</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data['decreases'])): ?>
                        <tr><td colspan="7" class="text-center py-2 text-muted">Không có biến động giảm trong tháng.</td></tr>
                    <?php else: ?>
                        <?php $i = 1; foreach ($data['decreases'] as $dec): ?>
                            <tr>
                                <td style="text-align: center;"><?= $i++ ?></td>
                                <td><strong><?= htmlspecialchars($dec['full_name']) ?></strong></td>
                                <td style="text-align: center;"><?= htmlspecialchars($dec['social_insurance_no'] ?? '') ?></td>
                                <td><?= htmlspecialchars($dec['pos_title'] ?? '') ?> (<?= htmlspecialchars($dec['dept_name'] ?? '') ?>)</td>
                                <td style="text-align: right;"><?= number_format((float)$dec['old_salary'], 0, ',', '.') ?></td>
                                <td style="text-align: center;"><?= date('m/Y', strtotime($dec['effective_date'])) ?></td>
                                <td><?= htmlspecialchars($dec['reason']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    <?php else: ?>
        <!-- ══════════════════════════════════════════════════════════
             MẪU C70a-HD: DANH SÁCH ĐỀ NGHỊ GIẢI QUYẾT HƯỞNG CHẾ ĐỘ ỐM ĐAU, THAI SẢN
             ══════════════════════════════════════════════════════════ -->
        <div class="text-center mb-4">
            <h3 style="font-size: 17px; font-weight: 800; margin: 0; text-transform: uppercase;">
                DANH SÁCH ĐỀ NGHỊ GIẢI QUYẾT HƯỞNG CHẾ ĐỘ ỐM ĐAU, THAI SẢN, DƯỠNG SỨC
            </h3>
            <div style="font-size: 13.5px; font-weight: 600; margin-top: 4px;">
                Tháng <?= $monthStr ?> năm <?= $yearStr ?>
            </div>
            <div style="font-size: 12px; color: #666; font-style: italic;">(Mẫu số C70a-HD ban hành kèm theo Quyết định số 166/QĐ-BHXH ngày 31/01/2019 của BHXH Việt Nam)</div>
        </div>

        <div class="table-responsive mb-4">
            <table class="table table-bordered align-middle" style="font-size: 12px; border-color: #333;">
                <thead style="background: #f1f5f9; text-align: center; font-weight: 700;">
                    <tr>
                        <th style="width: 40px;">STT</th>
                        <th style="width: 170px;">Họ và tên</th>
                        <th style="width: 100px;">Mã số BHXH</th>
                        <th>Chế độ hưởng</th>
                        <th style="width: 160px;">Thời gian nghỉ</th>
                        <th style="width: 70px;">Số ngày</th>
                        <th style="width: 130px;">Số tiền đề nghị</th>
                        <th>Số tài khoản cá nhân</th>
                        <th style="width: 110px;">Trạng thái</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        $stt = 1;
                        $hasAnyClaim = false;
                        foreach ($data['by_type'] as $type => $items):
                            if (empty($items)) continue;
                            $hasAnyClaim = true;
                    ?>
                        <tr style="background: #f8fafc; font-weight: 700;">
                            <td colspan="9" style="color: #1e3a8a;">
                                <?= $type === 'OmDau' ? 'I. CHẾ ĐỘ ỐM ĐAU' : ($type === 'ThaiSan' ? 'II. CHẾ ĐỘ THAI SẢN' : ($type === 'TaiNanLD_BNN' ? 'III. TAI NẠN LAO ĐỘNG & BỆNH NGHỀ NGHIỆP' : 'IV. DƯỠNG SỨC PHỤC HỒI SỨC KHỎE')) ?>
                            </td>
                        </tr>
                        <?php foreach ($items as $c): ?>
                            <tr>
                                <td style="text-align: center;"><?= $stt++ ?></td>
                                <td><strong><?= htmlspecialchars($c['full_name']) ?></strong></td>
                                <td style="text-align: center;"><?= htmlspecialchars($c['social_insurance_no'] ?? '') ?></td>
                                <td><?= htmlspecialchars($c['notes'] ?? $c['claim_type']) ?></td>
                                <td style="text-align: center;"><?= date('d/m/Y', strtotime($c['from_date'])) ?> – <?= date('d/m/Y', strtotime($c['to_date'])) ?></td>
                                <td style="text-align: center; font-weight: 600;"><?= $c['leave_days'] ?></td>
                                <td style="text-align: right; font-weight: 700; color: #10b981;"><?= number_format((float)$c['claim_amount'], 0, ',', '.') ?>đ</td>
                                <td><?= htmlspecialchars($c['bank_account'] ?? '---') ?> (<?= htmlspecialchars($c['bank_name'] ?? '') ?>)</td>
                                <td style="text-align: center;"><?= $c['status'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endforeach; ?>

                    <?php if (!$hasAnyClaim): ?>
                        <tr><td colspan="9" class="text-center py-3 text-muted">Không có hồ sơ hưởng chế độ nào trong tháng.</td></tr>
                    <?php else: ?>
                        <tr style="font-weight: 800; background: #f1f5f9;">
                            <td colspan="5" class="text-center">TỔNG CỘNG</td>
                            <td style="text-align: center;"><?= $data['total_days'] ?> ngày</td>
                            <td style="text-align: right; color: #10b981; font-size: 13px;"><?= number_format($data['total_amount'], 0, ',', '.') ?>đ</td>
                            <td colspan="2"></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <!-- CHỮ KÝ XÁC NHẬN -->
    <div class="row text-center mt-5" style="font-size: 13px;">
        <div class="col-4">
            <div style="font-weight: 700; text-transform: uppercase;">Người lập biểu</div>
            <div style="font-size: 11.5px; font-style: italic; color: #666;">(Ký, ghi rõ họ tên)</div>
            <div style="height: 70px;"></div>
            <div style="font-weight: 600;"><?= h(Session::userFullName() ?? 'Chuyên viên Nhân sự') ?></div>
        </div>
        <div class="col-4">
            <div style="font-weight: 700; text-transform: uppercase;">Kế toán trưởng</div>
            <div style="font-size: 11.5px; font-style: italic; color: #666;">(Ký, ghi rõ họ tên)</div>
            <div style="height: 70px;"></div>
            <div style="font-weight: 600;">Nguyễn Thị Mai</div>
        </div>
        <div class="col-4">
            <div style="font-weight: 700; text-transform: uppercase;">Thủ trưởng đơn vị</div>
            <div style="font-size: 11.5px; font-style: italic; color: #666;">(Ký, đóng dấu và ghi rõ họ tên)</div>
            <div style="height: 70px;"></div>
            <div style="font-weight: 600;">Tổng Giám Đốc</div>
        </div>
    </div>
</div>
