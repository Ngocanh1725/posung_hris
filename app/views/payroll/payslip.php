<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Phiếu Lương - <?= h($payslip->full_name) ?> - <?= $payslip->month ?>/<?= $payslip->year ?></title>
    <style>
        /* Reset & Base */
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: "Arial", sans-serif; font-size: 11pt; line-height: 1.5; color: #333; background: #e2e8f0; }
        
        /* A4 Portrait Setup */
        @page { size: A4 portrait; margin: 15mm; }
        .page {
            background: #fff;
            width: 210mm;
            min-height: 297mm;
            margin: 20px auto;
            padding: 20mm 15mm;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            position: relative;
        }
        @media print {
            body { background: #fff; }
            .page { margin: 0; padding: 0; box-shadow: none; border: none; width: 100%; min-height: auto; }
            .no-print { display: none !important; }
        }

        /* Layout */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .fw-bold { font-weight: bold; }
        .text-uppercase { text-transform: uppercase; }
        .text-primary { color: #333; }
        .text-danger { color: #333; }
        .text-success { color: #333; }
        .mb-1 { margin-bottom: 5px; }
        .mb-2 { margin-bottom: 10px; }
        .mb-3 { margin-bottom: 15px; }
        .mb-4 { margin-bottom: 20px; }
        
        /* Header */
        .header { border-bottom: 3px double #333; padding-bottom: 15px; margin-bottom: 20px; }
        .company-name { font-size: 16pt; font-weight: bold; color: #333; }
        .doc-title { font-size: 18pt; font-weight: bold; text-align: center; margin-top: 15px; }
        .doc-subtitle { font-size: 12pt; text-align: center; font-style: italic; color: #64748b; margin-bottom: 20px; }

        /* Info Block */
        .info-table { width: 100%; margin-bottom: 20px; font-size: 11pt; }
        .info-table td { padding: 4px 5px; vertical-align: top; }
        .info-label { width: 18%; font-weight: bold; color: #475569; }
        .info-value { width: 32%; border-bottom: 1px dotted #ccc; }

        /* Payroll Details Table */
        .details-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .details-table th, .details-table td { border: 1px solid #cbd5e1; padding: 8px 12px; }
        .details-table th { background-color: #f8fafc; text-align: left; font-weight: bold; color: #1e293b; }
        .col-stt { width: 8%; text-align: center; font-weight: bold; }
        .col-desc { width: 62%; }
        .col-amount { width: 30%; text-align: right; font-size: 12pt; }
        
        .section-title { background-color: #e2e8f0 !important; font-weight: bold; font-size: 11pt; color: #0f172a; text-transform: uppercase; }
        .sub-total { background-color: #f1f5f9; font-weight: bold; font-style: italic; }
        
        .net-pay-row th, .net-pay-row td { background-color: #f8fafc; font-size: 14pt; font-weight: bold; color: #000; border: 2px solid #000; padding: 12px; }

        /* Footer */
        .footer { margin-top: 40px; }
        .signature-table { width: 100%; text-align: center; margin-top: 20px; }
        .signature-table td { width: 50%; padding-top: 10px; }
        .note { margin-top: 30px; font-size: 10pt; color: #64748b; font-style: italic; border-top: 1px dashed #cbd5e1; padding-top: 10px; }
    </style>
</head>
<body>

<div class="text-center no-print" style="padding: 15px; background: #fff; margin-bottom: 20px; border-bottom: 2px solid #ccc; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
    <button onclick="window.print()" style="padding: 10px 25px; font-size: 16px; font-weight: bold; cursor: pointer; background: #2563eb; color: #fff; border: none; border-radius: 5px;">
        🖨️ In Phiếu Lương
    </button>
    <button onclick="window.close()" style="padding: 10px 25px; font-size: 16px; cursor: pointer; background: #64748b; color: #fff; border: none; border-radius: 5px; margin-left: 10px;">
        Đóng
    </button>
</div>

<div class="page">
    <div class="header">
        <table style="width: 100%;">
            <tr>
                <td style="width: 60%; vertical-align: top;">
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <?php if(file_exists(APP_ROOT . '/../public/uploads/logo.png')): ?>
                            <img src="<?= BASE_URL ?>/uploads/logo.png?t=<?= time() ?>" alt="Logo" style="max-height: 50px; margin-right: 15px;">
                        <?php else: ?>
                            <div style="width: 50px; height: 50px; background: #333; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: 900; color: #fff; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
                                PS
                            </div>
                        <?php endif; ?>
                        <div>
                            <div class="company-name">POSUNG MEC CO., LTD</div>
                            <div style="font-size: 10pt; color: #64748b; margin-top: 5px;">Head Office: POSUNG Tower, Hanoi, Vietnam</div>
                            <div style="font-size: 10pt; color: #64748b;">Phone: (024) 1234 5678 | Web: posung.vn</div>
                        </div>
                    </div>
                </td>
                <td style="width: 40%; text-align: right; vertical-align: top;">
                    <div style="font-size: 14pt; font-weight: 800; color: #333;">HRIS PAYROLL SYSTEM</div>
                    <div style="font-size: 10pt; color: #475569; margin-top: 5px;">Printed on: <?= date('d/m/Y') ?></div>
                    <div style="font-size: 10pt; font-family: monospace; letter-spacing: 2px; margin-top: 10px;">*PS-<?= str_pad((string)$payslip->employee_id, 4, '0', STR_PAD_LEFT) ?>-<?= $payslip->month ?><?= $payslip->year ?>*</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="doc-title">PHIẾU BÁO LƯƠNG (PAYSLIP)</div>
    <div class="doc-subtitle">Kỳ lương tháng <?= $payslip->month ?> năm <?= $payslip->year ?></div>

    <table class="info-table">
        <tr>
            <td class="info-label">Họ và tên:</td>
            <td class="info-value fw-bold text-uppercase"><?= h($payslip->full_name) ?></td>
            <td class="info-label">Mã nhân viên:</td>
            <td class="info-value fw-bold text-primary"><?= h($payslip->emp_code) ?></td>
        </tr>
        <tr>
            <td class="info-label">Phòng ban:</td>
            <td class="info-value"><?= h($payslip->dept_name ?? 'N/A') ?></td>
            <td class="info-label">Chức vụ:</td>
            <td class="info-value"><?= h($payslip->pos_title ?? 'N/A') ?></td>
        </tr>
        <tr>
            <td class="info-label">Số TK Ngân hàng:</td>
            <td class="info-value"><?= h($payslip->bank_account_no ?? '...') ?> (<?= h($payslip->bank_name ?? '...') ?>)</td>
            <td class="info-label">Mã số thuế:</td>
            <td class="info-value"><?= h($payslip->tax_code ?? '...') ?></td>
        </tr>
        <tr>
            <td class="info-label">Ngày công chuẩn:</td>
            <td class="info-value"><?= $payslip->standard_days ?> ngày</td>
            <td class="info-label">Ngày công thực tế:</td>
            <td class="info-value fw-bold text-success"><?= $payslip->actual_days ?> ngày</td>
        </tr>
        <tr>
            <td class="info-label">Nghỉ phép (AL):</td>
            <td class="info-value"><?= $payslip->leave_days ?> ngày</td>
            <td class="info-label">Nghỉ KL (UP):</td>
            <td class="info-value text-danger"><?= $payslip->unpaid_leave_days ?> ngày</td>
        </tr>
    </table>

    <?php 
        $base_actual = ($payslip->base_salary / max(1, $payslip->standard_days)) * $payslip->actual_days;
        $total_income = $base_actual + $payslip->performance_salary + $payslip->allowance_hazard + $payslip->allowance_meal + $payslip->allowance_travel + $payslip->allowance_phone + $payslip->ot_pay + $payslip->bonus + $payslip->other_support;
        $total_deduction = $payslip->insurance_social + $payslip->insurance_health + $payslip->insurance_unemployment + $payslip->tax_pit + $payslip->union_fee + $payslip->advance_payment;
        $net = $total_income - $total_deduction;
    ?>

    <table class="details-table">
        <thead>
            <tr>
                <th class="col-stt">STT</th>
                <th class="col-desc">DIỄN GIẢI KHOẢN MỤC (DESCRIPTION)</th>
                <th class="col-amount">SỐ TIỀN (VNĐ)</th>
            </tr>
        </thead>
        <tbody>
            <!-- A. THU NHẬP -->
            <tr>
                <td class="section-title text-center">A</td>
                <td class="section-title" colspan="2">CÁC KHOẢN THU NHẬP (INCOME)</td>
            </tr>
            <tr>
                <td class="text-center">1</td>
                <td>Lương cơ bản thực tế (Basic Salary) <br><small class="text-muted">(Lương HĐ / Ngày công chuẩn x Ngày công thực tế)</small></td>
                <td class="col-amount"><?= number_format($base_actual, 0, ',', '.') ?></td>
            </tr>
            <tr>
                <td class="text-center">2</td>
                <td>Lương năng suất / Trách nhiệm (Performance Salary)</td>
                <td class="col-amount"><?= number_format($payslip->performance_salary, 0, ',', '.') ?></td>
            </tr>
            <tr>
                <td class="text-center">3</td>
                <td>Tiền làm thêm giờ (OT Pay)</td>
                <td class="col-amount"><?= number_format($payslip->ot_pay, 0, ',', '.') ?></td>
            </tr>
            <tr>
                <td class="text-center">4</td>
                <td>Phụ cấp ăn trưa (Meal Allowance)</td>
                <td class="col-amount"><?= number_format($payslip->allowance_meal, 0, ',', '.') ?></td>
            </tr>
            <tr>
                <td class="text-center">5</td>
                <td>Phụ cấp đi lại / Xăng xe (Travel Allowance)</td>
                <td class="col-amount"><?= number_format($payslip->allowance_travel, 0, ',', '.') ?></td>
            </tr>
            <tr>
                <td class="text-center">6</td>
                <td>Phụ cấp điện thoại (Phone Allowance)</td>
                <td class="col-amount"><?= number_format($payslip->allowance_phone, 0, ',', '.') ?></td>
            </tr>
            <tr>
                <td class="text-center">7</td>
                <td>Phụ cấp độc hại / Khác (Hazard/Other Allowance)</td>
                <td class="col-amount"><?= number_format($payslip->allowance_hazard, 0, ',', '.') ?></td>
            </tr>
            <tr>
                <td class="text-center">8</td>
                <td>Thưởng (Bonus)</td>
                <td class="col-amount"><?= number_format($payslip->bonus, 0, ',', '.') ?></td>
            </tr>
            <tr>
                <td class="text-center">9</td>
                <td>Hỗ trợ khác (Other Support)</td>
                <td class="col-amount"><?= number_format($payslip->other_support, 0, ',', '.') ?></td>
            </tr>
            <tr>
                <td colspan="2" class="sub-total text-right">TỔNG THU NHẬP (TOTAL GROSS INCOME):</td>
                <td class="col-amount sub-total text-primary"><?= number_format($total_income, 0, ',', '.') ?></td>
            </tr>

            <!-- B. KHẤU TRỪ -->
            <tr>
                <td class="section-title text-center">B</td>
                <td class="section-title" colspan="2">CÁC KHOẢN KHẤU TRỪ (DEDUCTIONS)</td>
            </tr>
            <tr>
                <td class="text-center">1</td>
                <td>Bảo hiểm Xã hội - BHXH (8%)</td>
                <td class="col-amount text-danger"><?= number_format($payslip->insurance_social, 0, ',', '.') ?></td>
            </tr>
            <tr>
                <td class="text-center">2</td>
                <td>Bảo hiểm Y tế - BHYT (1.5%)</td>
                <td class="col-amount text-danger"><?= number_format($payslip->insurance_health, 0, ',', '.') ?></td>
            </tr>
            <tr>
                <td class="text-center">3</td>
                <td>Bảo hiểm Thất nghiệp - BHTN (1%)</td>
                <td class="col-amount text-danger"><?= number_format($payslip->insurance_unemployment, 0, ',', '.') ?></td>
            </tr>
            <tr>
                <td class="text-center">4</td>
                <td>Thuế Thu nhập Cá nhân - TNCN (PIT)</td>
                <td class="col-amount text-danger"><?= number_format($payslip->tax_pit, 0, ',', '.') ?></td>
            </tr>
            <tr>
                <td class="text-center">5</td>
                <td>Đoàn phí Công đoàn (Union Fee)</td>
                <td class="col-amount text-danger"><?= number_format($payslip->union_fee, 0, ',', '.') ?></td>
            </tr>
            <tr>
                <td class="text-center">6</td>
                <td>Tạm ứng / Giảm trừ khác (Advance / Others)</td>
                <td class="col-amount text-danger"><?= number_format($payslip->advance_payment, 0, ',', '.') ?></td>
            </tr>
            <tr>
                <td colspan="2" class="sub-total text-right">TỔNG KHẤU TRỪ (TOTAL DEDUCTIONS):</td>
                <td class="col-amount sub-total text-danger"><?= number_format($total_deduction, 0, ',', '.') ?></td>
            </tr>

            <!-- C. THỰC LĨNH -->
            <tr class="net-pay-row">
                <td colspan="2" class="text-right text-uppercase">C. Thực lĩnh (Net Pay) = A - B</td>
                <td class="col-amount"><?= number_format($net, 0, ',', '.') ?></td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <table class="signature-table">
            <tr>
                <td>
                    <strong>NGƯỜI LAO ĐỘNG</strong><br>
                    <em>(Ký, ghi rõ họ tên)</em>
                    <br><br><br><br><br>
                </td>
                <td>
                    <strong>ĐẠI DIỆN CÔNG TY</strong><br>
                    <em>Phòng Nhân sự / Kế toán</em>
                    <br><br><br><br><br>
                </td>
            </tr>
        </table>

        <div class="note">
            * Đây là phiếu lương điện tử được trích xuất từ Hệ thống HRIS Po Sung MEC.<br>
            * Vui lòng bảo mật thông tin thu nhập. Nếu có thắc mắc, vui lòng liên hệ Phòng Nhân sự / Kế toán trong vòng 03 ngày làm việc kể từ ngày nhận được phiếu lương. Quá thời hạn trên, Công ty sẽ không giải quyết các khiếu nại liên quan.
        </div>
    </div>
</div>

</body>
</html>
