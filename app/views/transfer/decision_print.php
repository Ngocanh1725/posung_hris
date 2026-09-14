<?php
/**
 * ============================================================
 *  View: transfer/decision_print.php
 *  Mẫu in Quyết định điều động nhân sự (Bản mô phỏng)
 * ============================================================
 */
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quyết định Điều động Nhân sự - <?= htmlspecialchars($order->decision_number) ?></title>
    <style>
        /* Base Styling for Print */
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 14pt;
            line-height: 1.5;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 20px;
        }
        .a4-container {
            width: 210mm;
            min-height: 297mm;
            margin: auto;
            padding: 20mm;
            background: white;
            box-sizing: border-box;
        }
        
        .header-row { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 30px; }
        .header-left { text-align: center; width: 45%; }
        .header-right { text-align: center; width: 50%; }
        
        .title { text-align: center; font-size: 18pt; font-weight: bold; margin: 30px 0; text-transform: uppercase; }
        .subtitle { text-align: center; font-size: 14pt; font-weight: bold; font-style: italic; margin-bottom: 30px; }
        
        .content { margin-bottom: 20px; text-align: justify; }
        .article { margin-bottom: 15px; }
        .article-title { font-weight: bold; }
        
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { font-weight: bold; text-align: center; }
        
        .signature-row { display: flex; justify-content: space-between; margin-top: 50px; }
        .signature-left { width: 40%; font-size: 12pt; }
        .signature-right { width: 40%; text-align: center; }
        
        /* Print Specific */
        @media print {
            body { padding: 0; background: none; }
            .a4-container { margin: 0; padding: 0; width: 100%; min-height: auto; box-shadow: none; border: none; }
            .no-print { display: none !important; }
            @page { margin: 20mm; size: A4; }
        }
        
        /* Non-print controls */
        .controls { text-align: center; margin-bottom: 20px; background: #f0f0f0; padding: 10px; border-radius: 5px; border: 1px solid #ccc; }
        .btn { padding: 8px 16px; background: #0a1628; color: #fff; text-decoration: none; border: none; cursor: pointer; font-size: 14px; border-radius: 4px; }
    </style>
</head>
<body>

<div class="controls no-print">
    <button class="btn" onclick="window.print()">🖨️ In Quyết Định</button>
    <button class="btn" onclick="window.close()" style="background: #666;">Đóng</button>
</div>

<div class="a4-container">
    
    <div class="header-row">
        <div class="header-left">
            <div>CÔNG TY TNHH CK KT XD PO SUNG</div>
            <strong>BAN GIÁM ĐỐC</strong>
            <div style="border-bottom: 1px solid #000; width: 50%; margin: 5px auto;"></div>
            <div style="margin-top: 5px;">Số: <?= htmlspecialchars($order->decision_number) ?></div>
        </div>
        <div class="header-right">
            <strong>CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM</strong><br>
            <strong>Độc lập - Tự do - Hạnh phúc</strong>
            <div style="border-bottom: 1px solid #000; width: 50%; margin: 5px auto;"></div>
            <div style="margin-top: 5px;"><em>TP.HCM, ngày <?= date('d') ?> tháng <?= date('m') ?> năm <?= date('Y') ?></em></div>
        </div>
    </div>
    
    <div class="title">QUYẾT ĐỊNH</div>
    <div class="subtitle">V/v Điều động Nhân sự thi công công trình</div>
    
    <div class="content">
        <em>- Căn cứ Bộ luật Lao động của nước Cộng hòa Xã hội Chủ nghĩa Việt Nam;<br>
        - Căn cứ Điều lệ tổ chức và hoạt động của Công ty TNHH Cơ khí Kỹ thuật Xây dựng Po Sung;<br>
        - Căn cứ vào nhu cầu nhân sự tại <strong><?= htmlspecialchars($order->to_project) ?></strong>;<br>
        - Xét đề nghị của Trưởng phòng Nhân sự và Giám đốc Dự án.</em>
    </div>
    
    <div class="title" style="font-size: 16pt; margin: 20px 0;">QUYẾT ĐỊNH</div>

    <div class="content">
        <div class="article">
            <span class="article-title">Điều 1.</span> Quyết định điều động <strong><?= count($order->employees) ?></strong> Cán bộ/Công nhân viên có tên trong Danh sách đính kèm đến nhận công tác tại dự án: 
            <strong><?= htmlspecialchars($order->to_project) ?></strong>.
        </div>
        
        <div class="article">
            <span class="article-title">Điều 2.</span> Thời gian có hiệu lực kể từ ngày: <strong><?= date('d/m/Y', strtotime($order->effective_date)) ?></strong>.<br>
            - Phụ cấp và chi phí lương sẽ được hạch toán vào Cost Center: <strong><?= htmlspecialchars($order->employees[0]->cc_code ?? '---') ?></strong>.<br>
            - Ban quản lý dự án đích có trách nhiệm chuẩn bị đầy đủ Thẻ an toàn và Đồ bảo hộ lao động (PPE).
        </div>
        
        <div class="article">
            <span class="article-title">Điều 3.</span> Các Ông/Bà Trưởng phòng Nhân sự, Kế toán trưởng, Giám đốc dự án liên quan và các nhân sự có tên tại Điều 1 chịu trách nhiệm thi hành Quyết định này.
        </div>
    </div>
    
    <div class="signature-row">
        <div class="signature-left">
            <strong>Nơi nhận:</strong><br>
            <em>- Như Điều 3;<br>
            - Lưu: NS, HSDA.</em>
        </div>
        <div class="signature-right">
            <strong>GIÁM ĐỐC CÔNG TY</strong><br>
            <em>(Ký, ghi rõ họ tên và đóng dấu)</em><br><br><br><br><br>
            <strong><?= htmlspecialchars($order->approver_name ?? '.........................................') ?></strong>
        </div>
    </div>

    <!-- Trang 2 (Danh sách đính kèm) nếu cần -->
    <div style="page-break-before: always;"></div>
    
    <div class="title" style="margin-top: 50px;">DANH SÁCH NHÂN SỰ ĐIỀU ĐỘNG</div>
    <div style="text-align: center; margin-bottom: 20px;"><em>(Kèm theo Quyết định số: <?= htmlspecialchars($order->decision_number) ?>)</em></div>

    <table>
        <thead>
            <tr>
                <th width="8%">STT</th>
                <th width="15%">Mã NV</th>
                <th width="30%">Họ và tên</th>
                <th width="25%">Chức danh / Vị trí</th>
                <th width="22%">Phòng ban</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($order->employees as $index => $emp): ?>
            <tr>
                <td style="text-align: center;"><?= $index + 1 ?></td>
                <td style="text-align: center;"><?= htmlspecialchars($emp->emp_code) ?></td>
                <td><strong><?= htmlspecialchars($emp->full_name) ?></strong></td>
                <td><?= htmlspecialchars($emp->pos_title ?? '---') ?></td>
                <td><?= htmlspecialchars($emp->dept_name ?? '---') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</div>

</body>
</html>
