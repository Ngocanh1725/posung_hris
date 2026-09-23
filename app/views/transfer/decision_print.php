<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quyết định điều động - <?= h($order->decision_number) ?></title>
    <style>
        /* Reset & Base */
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: "Times New Roman", Times, serif; font-size: 14pt; line-height: 1.3; color: #000; background: #e2e8f0; }
        
        /* A4 Page Setup */
        @page { size: A4; margin: 20mm 20mm; }
        .page {
            background: #fff;
            width: 210mm;
            min-height: 297mm;
            margin: 20px auto;
            padding: 20mm 20mm;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        @media print {
            body { background: #fff; }
            .page { margin: 0; padding: 0; box-shadow: none; border: none; width: 100%; min-height: auto; }
            .no-print { display: none !important; }
        }

        /* Layout */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-justify { text-align: justify; }
        .fw-bold { font-weight: bold; }
        .fst-italic { font-style: italic; }
        .mb-1 { margin-bottom: 5px; }
        .mb-2 { margin-bottom: 10px; }
        .mb-3 { margin-bottom: 15px; }
        .mb-4 { margin-bottom: 20px; }
        
        /* Header */
        .header-table { width: 100%; margin-bottom: 30px; }
        .header-table td { vertical-align: top; text-align: center; }
        .org-col { width: 40%; }
        .nation-col { width: 60%; }
        
        .org-name { font-weight: bold; text-transform: uppercase; }
        .org-line { width: 40%; border-top: 1px solid #000; margin: 5px auto; }
        .nation-name { font-weight: bold; text-transform: uppercase; }
        .nation-motto { font-weight: bold; }
        .nation-line { width: 40%; border-top: 1px solid #000; margin: 5px auto; }
        
        /* Title */
        .doc-title { font-size: 16pt; font-weight: bold; text-transform: uppercase; text-align: center; margin-top: 20px; margin-bottom: 5px; }
        .doc-subject { font-size: 14pt; font-weight: bold; text-align: center; margin-bottom: 20px; }
        
        /* Content */
        .article { margin-bottom: 10px; text-align: justify; }
        .article-num { font-weight: bold; }
        
        /* Table Danh sách */
        .emp-table { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 15px; }
        .emp-table th, .emp-table td { border: 1px solid #000; padding: 5px; text-align: left; font-size: 12pt; }
        .emp-table th { font-weight: bold; text-align: center; }
        
        /* Footer / Signatures */
        .footer-table { width: 100%; margin-top: 30px; }
        .footer-table td { vertical-align: top; }
        .recipient-col { width: 50%; font-size: 11pt; }
        .sign-col { width: 50%; text-align: center; }
        
    </style>
</head>
<body>

<div class="text-center no-print" style="padding: 15px; background: #fff; margin-bottom: 20px; border-bottom: 2px solid #ccc;">
    <button onclick="window.print()" style="padding: 10px 20px; font-size: 16px; cursor: pointer; background: #2563eb; color: #fff; border: none; border-radius: 5px;">
        🖨️ In Quyết Định (Print Decision)
    </button>
</div>

<div class="page">
    <table class="header-table">
        <tr>
            <td class="org-col">
                CÔNG TY TNHH PO SUNG MEC<br>
                <div class="org-name">GIÁM ĐỐC ĐIỀU HÀNH</div>
                <div class="org-line"></div>
                Số: <?= h($order->decision_number) ?>
            </td>
            <td class="nation-col">
                <div class="nation-name">CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM</div>
                <div class="nation-motto">Độc lập - Tự do - Hạnh phúc</div>
                <div class="nation-line"></div>
                <div class="fst-italic text-right" style="padding-right:20px; margin-top:10px;">
                    Hà Nội, ngày <?= date('d') ?> tháng <?= date('m') ?> năm <?= date('Y') ?>
                </div>
            </td>
        </tr>
    </table>

    <div class="doc-title">QUYẾT ĐỊNH</div>
    <div class="doc-subject">V/v Điều động nhân sự dự án</div>

    <div class="text-center fw-bold mb-4">GIÁM ĐỐC ĐIỀU HÀNH CÔNG TY TNHH PO SUNG MEC</div>
    
    <div class="article fst-italic">
        - Căn cứ Bộ luật Lao động của nước Cộng hòa Xã hội Chủ nghĩa Việt Nam;<br>
        - Căn cứ Điều lệ tổ chức và hoạt động của Công ty TNHH Po Sung MEC;<br>
        - Căn cứ vào nhu cầu tổ chức thi công tại dự án <?= h($order->to_project) ?>;<br>
        - Xét đề nghị của Trưởng phòng Nhân sự.
    </div>

    <div class="text-center fw-bold mt-4 mb-4" style="font-size: 16pt;">QUYẾT ĐỊNH:</div>

    <div class="article">
        <span class="article-num">Điều 1.</span> Điều động các Cán bộ/Nhân viên có tên trong danh sách dưới đây chuyển đến công tác tại dự án: <strong><?= h($order->to_project) ?></strong>.
    </div>

    <table class="emp-table">
        <thead>
            <tr>
                <th width="10%">STT</th>
                <th width="20%">Mã NV</th>
                <th width="35%">Họ và Tên</th>
                <th width="35%">Chức vụ / Vị trí</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $stt = 1;
            foreach ($order->employees as $emp): 
            ?>
            <tr>
                <td class="text-center"><?= $stt++ ?></td>
                <td class="text-center"><?= h($emp->emp_code) ?></td>
                <td><?= h($emp->full_name) ?></td>
                <td><?= h($emp->position_name) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="article">
        <span class="article-num">Điều 2.</span> Thời gian điều động kể từ ngày <strong><?= fmtDate($order->effective_date) ?></strong>. Mức lương và các khoản phụ cấp của Cán bộ/Nhân viên được thực hiện theo quy chế hiện hành đối với dự án <?= h($order->to_project) ?>.<br>
        Lý do điều động: <?= h($order->reason ?? 'Thực hiện nhiệm vụ thi công tại dự án mới.') ?>
    </div>

    <div class="article">
        <span class="article-num">Điều 3.</span> Trưởng phòng Nhân sự, Trưởng phòng Kế toán, Giám đốc dự án <?= h($order->to_project) ?> và các Cán bộ/Nhân viên có tên tại Điều 1 chịu trách nhiệm thi hành Quyết định này.
    </div>

    <table class="footer-table">
        <tr>
            <td class="recipient-col">
                <strong>Nơi nhận:</strong><br>
                <span class="fst-italic">- Như Điều 3;</span><br>
                <span class="fst-italic">- Lưu: VT, HCNS.</span>
            </td>
            <td class="sign-col">
                <strong>GIÁM ĐỐC ĐIỀU HÀNH</strong><br>
                <span class="fst-italic">(Ký, đóng dấu, ghi rõ họ tên)</span>
                <div style="height: 100px;"></div>
                <strong>LEE JONG HO</strong>
            </td>
        </tr>
    </table>
</div>

</body>
</html>
