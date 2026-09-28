<!-- app/views/transfer/decision_print.php -->
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quyết định Điều động <?= h($order->decision_number) ?></title>
    <style>
        body { 
            font-family: "Times New Roman", Times, serif; 
            line-height: 1.5; 
            color: #000; 
            font-size: 14pt; 
            padding: 0;
            margin: 0;
            background: #fff;
        }
        .header { display: flex; justify-content: space-between; text-align: center; font-weight: bold; margin-bottom: 20px; }
        .header-left { width: 40%; }
        .header-right { width: 60%; }
        .title { text-align: center; margin: 30px 0; }
        .title h1 { font-size: 18pt; margin: 0; padding: 0; text-transform: uppercase; }
        .content { text-align: justify; }
        .table-employees { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .table-employees th, .table-employees td { border: 1px solid #000; padding: 5px; text-align: center; }
        .footer { display: flex; justify-content: space-between; margin-top: 50px; text-align: center; }
        .footer-box { width: 33%; font-weight: bold; }
        .signature-space { height: 100px; }
        @media print {
            @page { size: A4; margin: 20mm; }
            body { margin: 0; padding: 0; }
            .no-print { display: none; }
        }
        .a4-container {
            width: 210mm;
            min-height: 297mm;
            padding: 20mm;
            margin: 10mm auto;
            border: 1px #D3D3D3 solid;
            border-radius: 5px;
            background: white;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
            box-sizing: border-box;
        }
        @media print {
            .a4-container {
                margin: 0;
                border: initial;
                border-radius: initial;
                width: initial;
                min-height: initial;
                box-shadow: initial;
                background: initial;
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: right; margin-bottom: 20px; padding: 10px;">
        <button onclick="window.print()" style="padding: 10px 20px; font-size: 14pt; cursor: pointer; background: #007bff; color: white; border: none; border-radius: 4px;">In Quyết định (A4)</button>
    </div>

    <div class="a4-container">
        <div class="header">
            <div class="header-left">
                CÔNG TY TNHH PO SUNG MEC VN<br>
                Số: <?= h($order->order_code ?? '') ?>/QĐ-ĐĐ
            </div>
            <div class="header-right">
                CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM<br>
                Độc lập - Tự do - Hạnh phúc<br>
                -------***-------
            </div>
        </div>

    <div style="text-align: right; font-style: italic;">
        Hà Nội, ngày <?= date('d') ?> tháng <?= date('m') ?> năm <?= date('Y') ?>
    </div>

    <div class="title">
        <h1>QUYẾT ĐỊNH</h1>
        <i>(V/v Điều động nhân sự đi công trình)</i>
    </div>

    <div class="content">
        <p><b>GIÁM ĐỐC CÔNG TY TNHH PO SUNG MEC VIỆT NAM</b></p>
        <p>- Căn cứ vào Điều lệ hoạt động của Công ty TNHH Po Sung Mec Việt Nam;</p>
        <p>- Căn cứ vào Hợp đồng lao động và năng lực của nhân sự;</p>
        <p>- Căn cứ vào yêu cầu tiến độ thi công của Dự án <b><?= h($order->to_project ?? '...') ?></b>;</p>
        <p>- Xét đề nghị của Trưởng phòng Nhân sự.</p>

        <h3 style="text-align: center;">QUYẾT ĐỊNH:</h3>
        
        <p><b>Điều 1:</b> Điều động danh sách nhân sự dưới đây đến làm việc tại công trường dự án <b><?= h($order->to_project) ?></b> kể từ ngày <b><?= date('d/m/Y', strtotime($order->effective_date)) ?></b>:</p>
        
        <table class="table-employees">
            <thead>
                <tr>
                    <th>STT</th>
                    <th>Mã NV</th>
                    <th>Họ và tên</th>
                    <th>Chức danh (Site)</th>
                    <th>Phụ cấp Site</th>
                    <th>Ghi chú</th>
                </tr>
            </thead>
            <tbody>
                <?php if(!empty($order->employees)): ?>
                    <?php foreach($order->employees as $index => $emp): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= h($emp->emp_code) ?></td>
                            <td style="text-align: left; padding-left: 5px;"><?= h($emp->full_name) ?></td>
                            <td><?= h($emp->site_position ?? $emp->pos_title) ?></td>
                            <td><?= $emp->site_allowance > 0 ? number_format($emp->site_allowance) . ' VNĐ' : 'Theo quy định' ?></td>
                            <td><?= $emp->end_date ? 'Đến ' . date('d/m/Y', strtotime($emp->end_date)) : '' ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <p><b>Điều 2:</b> Các nhân sự có tên tại Điều 1 được hưởng các chế độ tiền lương, phụ cấp theo quy định hiện hành của Dự án và Công ty.</p>
        <p><b>Điều 3:</b> Phòng Nhân sự, Phòng Kế toán, Giám đốc Dự án và các Ông/Bà có tên tại Điều 1 chịu trách nhiệm thi hành Quyết định này.</p>
    </div>

    <div class="footer">
        <div class="footer-box">
            Nơi nhận:<br>
            <span style="font-weight: normal; font-size: 12pt;">
                - Như Điều 3;<br>
                - Lưu VT, HR.
            </span>
        </div>
        <div class="footer-box">
            TRƯỞNG PHÒNG HR
            <div class="signature-space"></div>
            (Đã ký)
        </div>
        <div class="footer-box">
            GIÁM ĐỐC CÔNG TY
            <div class="signature-space"></div>
            
    </div>
    </div>
</body>
</html>
