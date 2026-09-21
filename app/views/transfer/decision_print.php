<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quyết định Điều động</title>
    <style>
        @page {
            size: A4;
            margin: 20mm;
        }
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 13pt;
            line-height: 1.4;
            color: #000;
            margin: 0;
            padding: 0;
            background: #fff;
        }
        .header-table {
            width: 100%;
            margin-bottom: 20px;
        }
        .header-table td {
            vertical-align: top;
            text-align: center;
        }
        .header-left {
            width: 40%;
        }
        .header-right {
            width: 60%;
        }
        .company-name {
            font-weight: bold;
            text-transform: uppercase;
        }
        .national-motto {
            font-weight: bold;
            text-transform: uppercase;
        }
        .line {
            width: 50%;
            border-top: 1px solid #000;
            margin: 5px auto;
        }
        .decision-title {
            text-align: center;
            font-size: 16pt;
            font-weight: bold;
            margin: 30px 0 10px;
        }
        .decision-subtitle {
            text-align: center;
            font-style: italic;
            margin-bottom: 30px;
        }
        .content {
            text-align: justify;
        }
        .article {
            margin-top: 15px;
        }
        .article-title {
            font-weight: bold;
        }
        table.list-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 12pt;
        }
        table.list-table, table.list-table th, table.list-table td {
            border: 1px solid #000;
        }
        table.list-table th, table.list-table td {
            padding: 5px;
            text-align: center;
            vertical-align: middle;
        }
        .signatures {
            margin-top: 40px;
            width: 100%;
        }
        .signatures td {
            width: 50%;
            text-align: center;
            vertical-align: top;
        }
        .sign-title {
            font-weight: bold;
        }
        .sign-space {
            height: 100px;
        }
        .warning-print {
            display: none;
        }
        @media print {
            @page { size: A4; margin: 15mm; }
            body { background: transparent; padding: 0; margin: 0; box-shadow: none; max-width: none; border: none; }
            .no-print { display: none !important; }
        }
        @media screen {
            body { max-width: 210mm; margin: 20px auto; padding: 20mm; border: 1px solid #ccc; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
            .warning-print { display: block; background: #fff3cd; color: #856404; padding: 10px; margin-bottom: 20px; text-align: center; border: 1px solid #ffeeba; }
        }
    </style>
</head>
<body>

<div class="warning-print no-print">
    Vui lòng nhấn <strong>Ctrl + P</strong> để in Quyết định này.
    <br>
    <button onclick="window.print()" style="margin-top: 10px; padding: 5px 15px; cursor: pointer;">In Quyết định</button>
</div>

<table class="header-table">
    <tr>
        <td class="header-left">
            <div class="company-name">CÔNG TY TNHH<br>PO SUNG MEC VIỆT NAM</div>
            <div class="line"></div>
            <div>Số: <?= h($order->decision_number) ?></div>
        </td>
        <td class="header-right">
            <div class="national-motto">CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM<br>Độc lập - Tự do - Hạnh phúc</div>
            <div class="line"></div>
            <div style="text-align: right; font-style: italic; padding-right: 20px;">
                Ngày <?= date('d', strtotime($order->created_at)) ?> tháng <?= date('m', strtotime($order->created_at)) ?> năm <?= date('Y', strtotime($order->created_at)) ?>
            </div>
        </td>
    </tr>
</table>

<div class="decision-title">QUYẾT ĐỊNH</div>
<div class="decision-subtitle">Về việc Điều động Cán bộ, Kỹ sư và Công nhân viên</div>

<div class="content">
    <div style="text-align: center; font-weight: bold; margin-bottom: 20px;">TỔNG GIÁM ĐỐC CÔNG TY TNHH PO SUNG MEC VIỆT NAM</div>
    
    <p><em>- Căn cứ Bộ luật Lao động nước Cộng hòa Xã hội Chủ nghĩa Việt Nam;</em></p>
    <p><em>- Căn cứ Điều lệ tổ chức và hoạt động của Công ty TNHH Po Sung MEC Việt Nam;</em></p>
    <p><em>- Căn cứ nhu cầu công việc và tình hình triển khai thi công tại các Dự án;</em></p>
    <p><em>- Theo đề nghị của Trưởng phòng Hành chính - Nhân sự.</em></p>
    
    <div style="text-align: center; font-weight: bold; margin: 30px 0 20px;">QUYẾT ĐỊNH:</div>

    <div class="article">
        <span class="article-title">Điều 1.</span> Điều động các Ông/Bà có tên trong danh sách kèm theo từ Dự án <strong><?= h($order->from_project ?? 'Khác') ?></strong> đến làm việc tại Dự án <strong><?= h($order->to_project) ?></strong> kể từ ngày <strong><?= date('d/m/Y', strtotime($order->effective_date)) ?></strong>.
    </div>

    <div class="article">
        <span class="article-title">Điều 2.</span> Mã hạch toán chi phí (Cost Center) mới áp dụng cho danh sách nhân sự trên là: <strong><?= isset($order->employees[0]) ? h($order->employees[0]->cc_code . ' - ' . $order->employees[0]->cc_name) : 'N/A' ?></strong>.
    </div>
    
    <div class="article">
        <span class="article-title">Điều 3.</span> Các khoản phụ cấp công trường, hỗ trợ đi lại và lưu trú (nếu có) sẽ được áp dụng theo quy định của Dự án tiếp nhận.
    </div>

    <div class="article">
        <span class="article-title">Điều 4.</span> Phòng Hành chính - Nhân sự, Kế toán trưởng, Giám đốc dự án các bên liên quan và các Ông/Bà có tên tại Điều 1 chịu trách nhiệm thi hành Quyết định này.
    </div>
    
    <div style="margin-top: 30px; font-weight: bold;">DANH SÁCH NHÂN SỰ ĐIỀU ĐỘNG:</div>
    <table class="list-table">
        <thead>
            <tr>
                <th width="5%">STT</th>
                <th width="15%">Mã NV</th>
                <th width="30%">Họ và Tên</th>
                <th width="20%">Chức vụ / Chức danh</th>
                <th width="30%">Ghi chú</th>
            </tr>
        </thead>
        <tbody>
            <?php $stt = 1; foreach ($order->employees as $emp): ?>
            <tr>
                <td><?= $stt++ ?></td>
                <td><?= h($emp->emp_code) ?></td>
                <td style="text-align: left; padding-left: 10px;"><?= h($emp->full_name) ?></td>
                <td><?= h($emp->pos_title ?? 'N/A') ?></td>
                <td></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<table class="signatures">
    <tr>
        <td>
            <div style="text-align: left; padding-left: 20px;">
                <strong>Nơi nhận:</strong><br>
                <em>- Như Điều 4;</em><br>
                <em>- Lưu: VT, HC-NS.</em>
            </div>
        </td>
        <td>
            <div class="sign-title">TỔNG GIÁM ĐỐC</div>
            <div class="sign-space"></div>
            <div style="font-weight: bold;">(Đã ký)</div>
        </td>
    </tr>
</table>

</body>
</html>
