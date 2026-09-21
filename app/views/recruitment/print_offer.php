<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thư Mời Nhận Việc - Offer Letter</title>
    <style>
        @page {
            size: A4;
            margin: 20mm;
        }
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 14pt;
            line-height: 1.4;
            color: #000;
            margin: 0;
            padding: 0;
            background: #fff;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .company-name {
            font-size: 16pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .title {
            text-align: center;
            font-size: 18pt;
            font-weight: bold;
            margin: 20px 0 10px;
        }
        .subtitle {
            text-align: center;
            font-style: italic;
            margin-bottom: 30px;
        }
        .section-title {
            font-weight: bold;
            margin-top: 20px;
            font-size: 14pt;
        }
        .content p {
            margin: 10px 0;
            text-align: justify;
        }
        .bilingual {
            display: block;
            font-style: italic;
            font-size: 12pt;
            color: #555;
            margin-top: -5px;
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        table, th, td {
            border: 1px solid #000;
        }
        th, td {
            padding: 8px;
            vertical-align: top;
        }
        .signatures {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
        }
        .signatures div {
            text-align: center;
            width: 40%;
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
    Vui lòng nhấn <strong>Ctrl + P</strong> để in thư mời này.
    <br>
    <button onclick="window.print()" style="margin-top: 10px; padding: 5px 15px; cursor: pointer;">In (Print)</button>
</div>

<div class="header">
    <div class="company-name">CÔNG TY TNHH PO SUNG MEC VIỆT NAM</div>
    <div style="font-style: italic;">PO SUNG MEC VINA CO., LTD</div>
</div>

<div class="title">THƯ MỜI NHẬN VIỆC</div>
<div class="subtitle">OFFER LETTER</div>

<div class="content">
    <p>Kính gửi Anh/Chị: <strong><?= h($candidate->full_name) ?></strong></p>
    <span class="bilingual">Dear Mr./Ms.: <?= h($candidate->full_name) ?></span>

    <p>Chúng tôi rất vui mừng thông báo rằng Anh/Chị đã trúng tuyển vào vị trí <strong><?= h($candidate->offer_position ?? $candidate->current_position ?? 'Nhân viên') ?></strong> tại Công ty TNHH Po Sung MEC Việt Nam.</p>
    <span class="bilingual">We are pleased to inform you that you have been selected for the position of <?= h($candidate->offer_position ?? $candidate->current_position ?? 'Employee') ?> at Po Sung MEC Vina Co., Ltd.</span>

    <div class="section-title">1. Chi tiết công việc (Job Details)</div>
    <ul>
        <li><strong>Vị trí (Position):</strong> <?= h($candidate->offer_position ?? $candidate->current_position ?? 'N/A') ?></li>
        <li><strong>Ngày bắt đầu dự kiến (Expected Start Date):</strong> <?= !empty($candidate->start_date) ? date('d/m/Y', strtotime($candidate->start_date)) : 'N/A' ?></li>
        <li><strong>Địa điểm làm việc (Work Location):</strong> <?= h($candidate->interview_location ?? 'Công trường dự án') ?></li>
    </ul>

    <div class="section-title">2. Lương và Phụ cấp (Salary & Allowances)</div>
    <table>
        <tr>
            <th width="50%">Khoản mục (Item)</th>
            <th>Số tiền / Nội dung (Amount / Detail)</th>
        </tr>
        <tr>
            <td>
                Lương cơ bản (Thử việc)<br>
                <span style="font-size: 11pt; font-style: italic; font-weight: normal;">Basic Salary (Probation)</span>
            </td>
            <td><strong><?= !empty($candidate->offer_salary) ? number_format($candidate->offer_salary, 0, ',', '.') . ' VNĐ' : 'Thỏa thuận' ?></strong></td>
        </tr>
        <tr>
            <td>
                Phụ cấp công trường<br>
                <span style="font-size: 11pt; font-style: italic; font-weight: normal;">Site Allowance</span>
            </td>
            <td>Theo quy định dự án (As per project policy)</td>
        </tr>
        <tr>
            <td>
                Bảo hiểm (BHXH, BHYT)<br>
                <span style="font-size: 11pt; font-style: italic; font-weight: normal;">Insurances</span>
            </td>
            <td>Sau khi ký HĐLĐ chính thức (After signing official contract)</td>
        </tr>
    </table>

    <div class="section-title">3. Cam kết An toàn (Safety Commitment)</div>
    <p>An toàn lao động (HSE) là ưu tiên số 1 tại Po Sung MEC. Anh/Chị cam kết tuân thủ tuyệt đối các quy định về an toàn công trường. Mọi vi phạm về an toàn (không móc dây an toàn trên cao, hút thuốc sai quy định...) sẽ dẫn đến việc chấm dứt hợp đồng ngay lập tức và đưa vào danh sách đen (Blacklist).</p>
    <span class="bilingual">HSE is our top priority. You commit to strictly follow site safety rules. Any safety violation (e.g., failure to use safety harness, illegal smoking) will result in immediate termination and blacklisting.</span>

    <p>Vui lòng xác nhận sự đồng ý với các điều khoản trên bằng cách ký tên dưới đây.</p>
    <span class="bilingual">Please signify your acceptance of these terms and conditions by signing below.</span>
</div>

<div class="signatures">
    <div>
        <strong>ĐẠI DIỆN PO SUNG MEC</strong><br>
        <span style="font-style: italic; font-size: 12pt;">For Po Sung MEC</span>
        <div class="sign-space"></div>
        <p>_______________________</p>
    </div>
    <div>
        <strong>NGƯỜI TRÚNG TUYỂN</strong><br>
        <span style="font-style: italic; font-size: 12pt;">Candidate Signature</span>
        <div class="sign-space"></div>
        <p><strong><?= h($candidate->full_name) ?></strong></p>
        <p>Ngày (Date): ____/____/20___</p>
    </div>
</div>

</body>
</html>
