<?php
/**
 * ============================================================
 *  View: payroll/payslip.php
 *  Phiếu lương điện tử (e-Payslip) sẵn sàng in ấn A5
 * ============================================================
 */
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Phiếu Lương - <?= htmlspecialchars($payslip->emp_code) ?> - Tháng <?= $payslip->month ?>/<?= $payslip->year ?></title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #e2e8f0;
            margin: 0;
            padding: 20px;
            color: #334155;
            font-size: 13px;
        }
        .slip-container {
            width: 148mm; /* A5 width */
            background: #fff;
            margin: 0 auto;
            padding: 15mm;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
            border-top: 5px solid #0f172a;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 10px;
        }
        .header h2 {
            margin: 0;
            color: #0f172a;
            text-transform: uppercase;
            font-size: 18px;
        }
        .header p {
            margin: 5px 0 0;
            color: #64748b;
        }
        
        .info-section {
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 20px;
            background: #f8fafc;
            padding: 10px;
            border-radius: 4px;
        }
        .info-col {
            width: 50%;
            margin-bottom: 8px;
        }
        .info-label {
            color: #64748b;
            display: inline-block;
            width: 80px;
        }
        .info-val {
            font-weight: bold;
            color: #0f172a;
        }

        .salary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .salary-table th, .salary-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #e2e8f0;
        }
        .salary-table th {
            text-align: left;
            color: #64748b;
            font-weight: 500;
        }
        .salary-table .val {
            text-align: right;
            font-weight: 600;
        }
        .section-title {
            background: #f1f5f9;
            font-weight: bold;
            color: #0f172a;
        }
        
        .net-salary {
            background: #0f172a;
            color: #fff;
            padding: 15px;
            text-align: center;
            border-radius: 4px;
            margin-top: 20px;
        }
        .net-salary .amount {
            font-size: 24px;
            font-weight: bold;
            color: #10b981;
            margin-top: 5px;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 11px;
            color: #94a3b8;
        }

        .no-print {
            text-align: center;
            margin-bottom: 20px;
        }
        .btn {
            padding: 8px 16px;
            background: #2563eb;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }
        
        @media print {
            body { background: none; padding: 0; }
            .slip-container { box-shadow: none; width: 100%; padding: 10mm; }
            .no-print { display: none !important; }
            @page { margin: 0; size: A5; }
        }
    </style>
</head>
<body>

<div class="no-print">
    <button class="btn" onclick="window.print()">🖨️ In Phiếu Lương (A5)</button>
    <button class="btn" onclick="window.close()" style="background: #64748b; margin-left: 10px;">Đóng</button>
</div>

<div class="slip-container">
    <div class="header">
        <h2>PHIẾU LƯƠNG / PAYSLIP</h2>
        <p>Tháng <?= $payslip->month ?> Năm <?= $payslip->year ?></p>
    </div>

    <div class="info-section">
        <div class="info-col">
            <span class="info-label">Mã NV:</span> 
            <span class="info-val"><?= htmlspecialchars($payslip->emp_code) ?></span>
        </div>
        <div class="info-col">
            <span class="info-label">Họ Tên:</span> 
            <span class="info-val"><?= htmlspecialchars($payslip->full_name) ?></span>
        </div>
        <div class="info-col">
            <span class="info-label">Chức vụ:</span> 
            <span class="info-val"><?= htmlspecialchars($payslip->pos_title ?? '---') ?></span>
        </div>
        <div class="info-col">
            <span class="info-label">Dự án:</span> 
            <span class="info-val"><?= htmlspecialchars($payslip->project_name ?? '---') ?></span>
        </div>
    </div>

    <table class="salary-table">
        <tr class="section-title">
            <td colspan="2">I. THÔNG TIN CHẤM CÔNG (TIMESHEET)</td>
        </tr>
        <tr>
            <td>Ngày công chuẩn (Standard Days)</td>
            <td class="val"><?= $payslip->standard_days ?></td>
        </tr>
        <tr>
            <td>Ngày công thực tế (Actual Days)</td>
            <td class="val"><?= $payslip->actual_days ?></td>
        </tr>
        <tr>
            <td>Số giờ tăng ca (OT Hours)</td>
            <td class="val">
                Ngày: <?= $payslip->timesheet['ot_day_hours'] ?? 0 ?>h | 
                Đêm: <?= $payslip->timesheet['ot_night_hours'] ?? 0 ?>h | 
                CN: <?= $payslip->timesheet['ot_sunday_hours'] ?? 0 ?>h
            </td>
        </tr>

        <tr class="section-title">
            <td colspan="2">II. CHI TIẾT THU NHẬP (INCOME)</td>
        </tr>
        <tr>
            <td>Lương cơ bản (Base Salary)</td>
            <td class="val"><?= number_format($payslip->base_salary ?? 0, 0, ',', '.') ?> đ</td>
        </tr>
        <tr>
            <td>Lương làm thêm giờ (OT Pay)</td>
            <td class="val"><?= number_format($payslip->ot_pay, 0, ',', '.') ?> đ</td>
        </tr>
        <tr>
            <td>Tổng phụ cấp (Allowances)</td>
            <td class="val"><?= number_format($payslip->allowances_total, 0, ',', '.') ?> đ</td>
        </tr>
        <tr>
            <td style="font-weight:bold; color: #10b981;">TỔNG THU NHẬP (GROSS INCOME)</td>
            <td class="val" style="color: #10b981;"><?= number_format(($payslip->base_salary/$payslip->standard_days*$payslip->actual_days) + $payslip->ot_pay + $payslip->allowances_total, 0, ',', '.') ?> đ</td>
        </tr>

        <tr class="section-title">
            <td colspan="2">III. KHẤU TRỪ (DEDUCTIONS)</td>
        </tr>
        <tr>
            <td>Bảo hiểm (BHXH, BHYT, BHTN)</td>
            <td class="val text-danger">-<?= number_format(($payslip->base_salary * 0.105), 0, ',', '.') ?> đ</td>
        </tr>
        <tr>
            <td>Thuế thu nhập cá nhân (PIT)</td>
            <td class="val text-danger">-<?= number_format($payslip->deductions_total - ($payslip->base_salary * 0.105), 0, ',', '.') ?> đ</td>
        </tr>
        <tr>
            <td style="font-weight:bold; color: #ef4444;">TỔNG KHẤU TRỪ (TOTAL DEDUCTIONS)</td>
            <td class="val" style="color: #ef4444;">-<?= number_format($payslip->deductions_total, 0, ',', '.') ?> đ</td>
        </tr>
    </table>

    <div class="net-salary">
        <div>THỰC LĨNH / NET SALARY</div>
        <div class="amount"><?= number_format($payslip->net_salary, 0, ',', '.') ?> VNĐ</div>
    </div>

    <div class="footer">
        <p>Mọi thắc mắc về phiếu lương, vui lòng liên hệ Phòng Nhân sự (HR Dept) trong vòng 03 ngày làm việc.</p>
        <p><em>(Phiếu này được xuất tự động từ hệ thống POSUNG HRIS)</em></p>
    </div>
</div>

</body>
</html>
