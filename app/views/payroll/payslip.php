<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Phiếu Lương - <?= h($payslip->full_name) ?> - <?= $payslip->month ?>/<?= $payslip->year ?></title>
    <style>
        /* Reset & Base */
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: "Arial", sans-serif; font-size: 11pt; line-height: 1.5; color: #333; background: #e2e8f0; }
        
        /* A5 Page Setup (Half A4) */
        @page { size: A5 landscape; margin: 10mm; }
        .page {
            background: #fff;
            width: 210mm;
            min-height: 148mm;
            margin: 20px auto;
            padding: 15mm;
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
        .mb-2 { margin-bottom: 10px; }
        .mb-3 { margin-bottom: 15px; }
        
        /* Header */
        .header { border-bottom: 2px solid #1e3a8a; padding-bottom: 10px; margin-bottom: 15px; }
        .company-name { font-size: 14pt; font-weight: bold; color: #1e3a8a; }
        .doc-title { font-size: 16pt; font-weight: bold; text-align: center; margin-top: 10px; }
        .doc-subtitle { font-size: 11pt; text-align: center; font-style: italic; color: #64748b; }

        /* Info Block */
        .info-table { width: 100%; margin-bottom: 15px; }
        .info-table td { padding: 3px 0; }
        .info-label { width: 15%; font-weight: bold; color: #475569; }
        .info-value { width: 35%; }

        /* Payroll Details Table */
        .details-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .details-table th, .details-table td { border: 1px solid #cbd5e1; padding: 6px 10px; }
        .details-table th { background-color: #f1f5f9; text-align: left; font-weight: bold; }
        .amount-col { text-align: right; width: 25%; font-family: monospace; font-size: 12pt; }
        
        .section-title { background-color: #e2e8f0 !important; font-weight: bold; }
        
        .net-pay-row th, .net-pay-row td { background-color: #dcfce7; font-size: 13pt; font-weight: bold; color: #166534; border-top: 2px solid #166534; }

        /* Footer */
        .footer { margin-top: 20px; font-size: 10pt; color: #64748b; font-style: italic; border-top: 1px dashed #cbd5e1; padding-top: 10px; }
    </style>
</head>
<body>

<div class="text-center no-print" style="padding: 15px; background: #fff; margin-bottom: 20px; border-bottom: 2px solid #ccc;">
    <button onclick="window.print()" style="padding: 10px 20px; font-size: 16px; cursor: pointer; background: #2563eb; color: #fff; border: none; border-radius: 5px;">
        🖨️ In Phiếu Lương (Print Payslip)
    </button>
</div>

<div class="page">
    <div class="header">
        <table style="width: 100%;">
            <tr>
                <td style="width: 50%;">
                    <div class="company-name">PO SUNG MEC CO., LTD</div>
                    <div style="font-size: 9pt; color: #64748b;">Hệ thống Quản trị Nhân sự HRIS</div>
                </td>
                <td style="width: 50%; text-align: right; font-size: 10pt;">
                    <strong>Cost Center:</strong> <?= h($payslip->cc_code ?? 'N/A') ?>
                </td>
            </tr>
        </table>
    </div>

    <div class="doc-title">PHIẾU LƯƠNG CÁ NHÂN (PAYSLIP)</div>
    <div class="doc-subtitle">Kỳ lương tháng <?= $payslip->month ?> năm <?= $payslip->year ?></div>

    <table class="info-table mt-3">
        <tr>
            <td class="info-label">Mã NV:</td>
            <td class="info-value fw-bold"><?= h($payslip->emp_code) ?></td>
            <td class="info-label">Dự án:</td>
            <td class="info-value"><?= h($payslip->project_name ?? 'Trụ sở chính') ?></td>
        </tr>
        <tr>
            <td class="info-label">Họ Tên:</td>
            <td class="info-value fw-bold text-uppercase"><?= h($payslip->full_name) ?></td>
            <td class="info-label">Vị trí:</td>
            <td class="info-value"><?= h($payslip->pos_title ?? 'N/A') ?></td>
        </tr>
        <tr>
            <td class="info-label">Ngày công chuẩn:</td>
            <td class="info-value"><?= $payslip->standard_days ?> ngày</td>
            <td class="info-label">Ngày công đi làm:</td>
            <td class="info-value fw-bold"><?= $payslip->actual_days ?> ngày</td>
        </tr>
    </table>

    <table class="details-table">
        <tbody>
            <!-- LƯƠNG & PHỤ CẤP -->
            <tr>
                <td class="section-title" colspan="2">A. THU NHẬP (INCOME)</td>
            </tr>
            <tr>
                <td>Lương cơ bản (Base Salary)</td>
                <td class="amount-col"><?= number_format($payslip->base_salary, 0, ',', '.') ?></td>
            </tr>
            <tr>
                <td>Tiền làm thêm giờ (OT Pay)</td>
                <td class="amount-col"><?= number_format($payslip->ot_pay, 0, ',', '.') ?></td>
            </tr>
            <tr>
                <td>Tổng Phụ cấp (Allowances: Chức vụ, Dự án, Độc hại, Xa nhà)</td>
                <td class="amount-col"><?= number_format($payslip->allowances_total, 0, ',', '.') ?></td>
            </tr>
            <tr>
                <td class="fw-bold text-right">Tổng Thu Nhập (Gross Income)</td>
                <td class="amount-col fw-bold">
                    <?= number_format(
                        (($payslip->base_salary / $payslip->standard_days) * $payslip->actual_days) 
                        + $payslip->ot_pay 
                        + $payslip->allowances_total
                    , 0, ',', '.') ?>
                </td>
            </tr>

            <!-- KHẤU TRỪ -->
            <tr>
                <td class="section-title" colspan="2">B. KHẤU TRỪ (DEDUCTIONS)</td>
            </tr>
            <tr>
                <td>Trích nộp BHXH, BHYT, BHTN & Thuế TNCN (Tạm tính)</td>
                <td class="amount-col text-danger">-<?= number_format($payslip->deductions_total, 0, ',', '.') ?></td>
            </tr>

            <!-- THỰC LĨNH -->
            <tr class="net-pay-row">
                <td class="text-right">C. THỰC LĨNH (NET PAY) = A - B</td>
                <td class="amount-col"><?= number_format($payslip->net_salary, 0, ',', '.') ?> VNĐ</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        * Đây là phiếu lương điện tử được trích xuất tự động từ Hệ thống HRIS Po Sung MEC.<br>
        * Vui lòng bảo mật thông tin thu nhập. Nếu có thắc mắc, vui lòng liên hệ Phòng Nhân sự trong vòng 03 ngày làm việc.
    </div>
</div>

</body>
</html>
