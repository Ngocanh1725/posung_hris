<?php
/**
 * ============================================================
 *  View: payroll/payslip.php
 *  Phiếu lương điện tử (e-Payslip) sẵn sàng in ấn A5/A4
 * ============================================================
 */
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Phiếu Lương - <?= h($payslip->emp_code) ?> - Tháng <?= $payslip->month ?>/<?= $payslip->year ?></title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            background: #e2e8f0;
            margin: 0;
            padding: 20px;
            color: #000;
            font-size: 14px;
        }
        .slip-container {
            width: 210mm; /* A4 width */
            background: #fff;
            margin: 0 auto;
            padding: 20mm;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
            box-sizing: border-box;
        }
        .company-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .company-info h1 {
            margin: 0;
            font-size: 18px;
            text-transform: uppercase;
        }
        .company-info p {
            margin: 3px 0 0;
            font-size: 12px;
            color: #555;
        }
        .title {
            text-align: center;
            margin-bottom: 20px;
        }
        .title h2 {
            margin: 0;
            font-size: 22px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .title p {
            margin: 5px 0 0;
            font-style: italic;
        }
        
        .employee-info {
            display: table;
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }
        .employee-info-row {
            display: table-row;
        }
        .employee-info-cell {
            display: table-cell;
            padding: 5px 0;
            width: 50%;
        }
        .label {
            font-weight: bold;
            display: inline-block;
            width: 120px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .data-table th, .data-table td {
            border: 1px solid #000;
            padding: 8px;
        }
        .data-table th {
            background-color: #f0f0f0;
            text-align: center;
            font-weight: bold;
        }
        .data-table td.amount {
            text-align: right;
        }
        .data-table td.section-header {
            background-color: #f9f9f9;
            font-weight: bold;
            font-style: italic;
        }

        .summary-box {
            width: 50%;
            float: right;
            border: 2px solid #000;
            padding: 10px;
            margin-bottom: 30px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        .summary-total {
            font-weight: bold;
            font-size: 18px;
            border-top: 1px solid #000;
            padding-top: 5px;
            margin-top: 5px;
        }

        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }

        .signature-section {
            display: flex;
            justify-content: space-between;
            margin-top: 50px;
            text-align: center;
        }
        .signature-box {
            width: 30%;
        }
        .signature-box p {
            margin: 0;
            font-weight: bold;
        }
        .signature-space {
            height: 80px;
        }

        .no-print {
            text-align: center;
            margin-bottom: 20px;
        }
        .btn {
            padding: 10px 20px;
            background: #2563eb;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            font-size: 14px;
        }
        
        @media print {
            body { background: none; padding: 0; }
            .slip-container { box-shadow: none; width: 100%; padding: 0; }
            .no-print { display: none !important; }
            @page { margin: 15mm; size: A4; }
        }
    </style>
</head>
<body>

<div class="no-print">
    <button class="btn" onclick="window.print()">🖨️ In Phiếu Lương</button>
    <button class="btn" onclick="window.close()" style="background: #64748b; margin-left: 10px;">Đóng</button>
</div>

<div class="slip-container">
    <div class="company-header">
        <div class="company-info">
            <h1>CÔNG TY TNHH POSUNG VINA</h1>
            <p>Địa chỉ: Tầng 12, Tòa nhà Lotte, 54 Liễu Giai, Ba Đình, Hà Nội</p>
            <p>Mã số thuế: 0101234567</p>
        </div>
        <div class="company-logo">
            <!-- Placeholder for logo -->
            <h1 style="color: #2563eb; margin: 0; font-family: 'Arial', sans-serif;">POSUNG HRIS</h1>
        </div>
    </div>

    <div class="title">
        <h2>PHIẾU THANH TOÁN LƯƠNG / PAYSLIP</h2>
        <p>Tháng (Month): <?= $payslip->month ?> / <?= $payslip->year ?></p>
    </div>

    <div class="employee-info">
        <div class="employee-info-row">
            <div class="employee-info-cell">
                <span class="label">Mã NV (Emp ID):</span> <?= h($payslip->emp_code) ?>
            </div>
            <div class="employee-info-cell">
                <span class="label">Họ Tên (Name):</span> <strong><?= h($payslip->full_name) ?></strong>
            </div>
        </div>
        <div class="employee-info-row">
            <div class="employee-info-cell">
                <span class="label">Chức vụ (Title):</span> <?= h($payslip->pos_title ?? '---') ?>
            </div>
            <div class="employee-info-cell">
                <span class="label">Phòng ban (Dept):</span> <?= h($payslip->dept_name ?? $payslip->project_name ?? '---') ?>
            </div>
        </div>
    </div>

    <?php 
        $gross_income = ($payslip->base_salary / $payslip->standard_days * $payslip->actual_days) + $payslip->ot_pay + $payslip->allowances_total;
        $insurance = $payslip->base_salary * 0.105;
        $pit = $payslip->deductions_total - $insurance;
        if($pit < 0) $pit = 0;
    ?>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 50%;">THU NHẬP (EARNINGS)</th>
                <th style="width: 50%;">KHẤU TRỪ (DEDUCTIONS)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <!-- Cột Thu nhập -->
                <td style="vertical-align: top; padding: 0; border: none;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td class="section-header" colspan="2">1. Thông tin công (Timesheet)</td>
                        </tr>
                        <tr>
                            <td>Ngày công chuẩn (Standard Days)</td>
                            <td class="amount"><?= $payslip->standard_days ?></td>
                        </tr>
                        <tr>
                            <td>Ngày công thực tế (Actual Days)</td>
                            <td class="amount"><?= $payslip->actual_days ?></td>
                        </tr>
                        <tr>
                            <td>Giờ tăng ca (OT: Day/Night/Sun)</td>
                            <td class="amount"><?= ($payslip->timesheet['ot_day_hours'] ?? 0) . '/' . ($payslip->timesheet['ot_night_hours'] ?? 0) . '/' . ($payslip->timesheet['ot_sunday_hours'] ?? 0) ?> h</td>
                        </tr>
                        <tr>
                            <td class="section-header" colspan="2">2. Chi tiết Thu nhập (Income)</td>
                        </tr>
                        <tr>
                            <td>Lương cơ bản (Base Salary)</td>
                            <td class="amount"><?= number_format($payslip->base_salary ?? 0, 0, ',', '.') ?></td>
                        </tr>
                        <tr>
                            <td>Lương thực tế (Actual Salary)</td>
                            <td class="amount"><?= number_format(($payslip->base_salary / $payslip->standard_days * $payslip->actual_days), 0, ',', '.') ?></td>
                        </tr>
                        <tr>
                            <td>Tiền làm thêm giờ (OT Pay)</td>
                            <td class="amount"><?= number_format($payslip->ot_pay, 0, ',', '.') ?></td>
                        </tr>
                        <tr>
                            <td>Tổng phụ cấp (Allowances)</td>
                            <td class="amount"><?= number_format($payslip->allowances_total, 0, ',', '.') ?></td>
                        </tr>
                    </table>
                </td>

                <!-- Cột Khấu trừ -->
                <td style="vertical-align: top; padding: 0; border: none; border-left: 1px solid #000;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td class="section-header" colspan="2">3. Các khoản khấu trừ (Deductions)</td>
                        </tr>
                        <tr>
                            <td>Bảo hiểm XH, YT, TN (Insurance 10.5%)</td>
                            <td class="amount"><?= number_format($insurance, 0, ',', '.') ?></td>
                        </tr>
                        <tr>
                            <td>Thuế TNCN (PIT)</td>
                            <td class="amount"><?= number_format($pit, 0, ',', '.') ?></td>
                        </tr>
                        <tr>
                            <td>Tạm ứng / Khác (Advances/Other)</td>
                            <td class="amount">0</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>

    <div class="clearfix">
        <div class="summary-box">
            <div class="summary-row">
                <span>TỔNG THU (Total Gross):</span>
                <span><?= number_format($gross_income, 0, ',', '.') ?> VNĐ</span>
            </div>
            <div class="summary-row">
                <span>TỔNG KHẤU TRỪ (Total Deductions):</span>
                <span><?= number_format($payslip->deductions_total, 0, ',', '.') ?> VNĐ</span>
            </div>
            <div class="summary-row summary-total">
                <span>THỰC LĨNH (Net Salary):</span>
                <span><?= number_format($payslip->net_salary, 0, ',', '.') ?> VNĐ</span>
            </div>
        </div>
    </div>

    <div class="signature-section">
        <div class="signature-box">
            <p>Người lập phiếu</p>
            <p style="font-weight: normal; font-size: 12px;">(Prepared by)</p>
            <div class="signature-space"></div>
            <p>Phòng Nhân Sự</p>
        </div>
        <div class="signature-box">
            <p>Kế toán trưởng</p>
            <p style="font-weight: normal; font-size: 12px;">(Chief Accountant)</p>
            <div class="signature-space"></div>
        </div>
        <div class="signature-box">
            <p>Người lao động ký nhận</p>
            <p style="font-weight: normal; font-size: 12px;">(Employee Signature)</p>
            <div class="signature-space"></div>
            <p><?= h($payslip->full_name) ?></p>
        </div>
    </div>

</div>

</body>
</html>
