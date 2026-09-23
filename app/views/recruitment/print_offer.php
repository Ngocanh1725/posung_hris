<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Offer Letter - <?= h($candidate->full_name) ?></title>
    <style>
        /* Reset & Base */
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: "Times New Roman", Times, serif; font-size: 13pt; line-height: 1.5; color: #000; background: #e2e8f0; }
        
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

        /* Typography & Layout */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .fw-bold { font-weight: bold; }
        .mb-2 { margin-bottom: 8px; }
        .mb-4 { margin-bottom: 20px; }
        .mt-4 { margin-top: 20px; }
        
        .header { text-align: center; margin-bottom: 30px; }
        .company-name { font-size: 16pt; font-weight: bold; text-transform: uppercase; color: #1e3a8a; }
        .company-info { font-size: 11pt; font-style: italic; color: #475569; }
        
        .title-vn { font-size: 18pt; font-weight: bold; text-transform: uppercase; margin-bottom: 5px; }
        .title-en { font-size: 14pt; font-weight: bold; text-transform: uppercase; color: #475569; margin-bottom: 30px; }

        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        td { padding: 5px; vertical-align: top; }
        .col-label { width: 40%; font-weight: bold; }
        .col-val { width: 60%; }

        .signature-area { display: flex; justify-content: space-between; margin-top: 50px; }
        .signature-box { text-align: center; width: 45%; }
        
        .lang-en { font-style: italic; color: #475569; font-size: 12pt; display: block; margin-top: -3px; margin-bottom: 5px; }
    </style>
</head>
<body>

<div class="text-center no-print" style="padding: 15px; background: #fff; margin-bottom: 20px; border-bottom: 2px solid #ccc;">
    <button onclick="window.print()" style="padding: 10px 20px; font-size: 16px; cursor: pointer; background: #2563eb; color: #fff; border: none; border-radius: 5px;">
        🖨️ In Thư Mời (Print Offer)
    </button>
</div>

<div class="page">
    <div class="header">
        <div class="company-name">CÔNG TY TNHH CƠ KHÍ KỸ THUẬT XÂY DỰNG PO SUNG</div>
        <div class="company-info">PO SUNG MEC CO., LTD</div>
    </div>

    <div class="text-center">
        <div class="title-vn">THƯ MỜI NHẬN VIỆC</div>
        <div class="title-en">OFFER LETTER</div>
    </div>

    <div class="mb-4">
        Kính gửi / <span class="lang-en" style="display:inline;">Dear</span> <strong>Ông/Bà <?= h($candidate->full_name) ?></strong>,
    </div>

    <div class="mb-4">
        Chúng tôi rất vui mừng thông báo rằng bạn đã vượt qua các vòng phỏng vấn của Po Sung MEC. Chúng tôi trân trọng kính mời bạn gia nhập công ty với các điều kiện sau đây:
        <span class="lang-en">We are pleased to inform you that you have passed the interview rounds of Po Sung MEC. We cordially invite you to join our company under the following terms and conditions:</span>
    </div>

    <table>
        <tr>
            <td class="col-label">1. Vị trí công việc:<br><span class="lang-en">Position:</span></td>
            <td class="col-val fw-bold"><?= h($candidate->pos_title ?? $candidate->request_desc ?? 'Ứng viên tự do') ?></td>
        </tr>
        <tr>
            <td class="col-label">2. Dự án phân bổ:<br><span class="lang-en">Assigned Project:</span></td>
            <td class="col-val"><?= h($request->project_name ?? 'Khối Văn Phòng / Chưa xác định') ?></td>
        </tr>
        <tr>
            <td class="col-label">3. Mức lương cơ bản:<br><span class="lang-en">Basic Salary:</span></td>
            <td class="col-val fw-bold"><?= number_format($candidate->offer_salary ?? $candidate->expected_salary ?? 0) ?> VNĐ / tháng</td>
        </tr>
        <tr>
            <td class="col-label">4. Phụ cấp dự án/công trường:<br><span class="lang-en">Site Allowances:</span></td>
            <td class="col-val">
                Theo quy định hiện hành của công ty đối với dự án <?= h($request->project_name ?? '') ?>.<br>
                <span class="lang-en">In accordance with current company regulations for the <?= h($request->project_name ?? '') ?> project.</span>
            </td>
        </tr>
        <tr>
            <td class="col-label">5. Ngày dự kiến nhận việc:<br><span class="lang-en">Expected Start Date:</span></td>
            <td class="col-val fw-bold"><?= !empty($candidate->start_date) ? fmtDate($candidate->start_date) : 'Sẽ thông báo sau / To be advised' ?></td>
        </tr>
    </table>

    <div class="mt-4 mb-4">
        Khi đến nhận việc, vui lòng mang theo hồ sơ gốc bao gồm: Sơ yếu lý lịch, CCCD (bản sao công chứng), Bằng cấp/Chứng chỉ chuyên môn, Giấy khám sức khỏe.
        <span class="lang-en">Upon starting, please bring your original documents including: Resume, Notarized ID Card, Professional Degrees/Certificates, and Health Certificate.</span>
    </div>

    <div class="signature-area">
        <div class="signature-box">
            <div class="fw-bold">ỨNG VIÊN ĐỒNG Ý NHẬN VIỆC<br><span class="lang-en">CANDIDATE ACCEPTANCE</span></div>
            <div style="font-size: 11pt; font-style: italic; margin-bottom: 80px;">(Ký và ghi rõ họ tên / Sign & Full name)</div>
            <div class="fw-bold"><?= h($candidate->full_name) ?></div>
        </div>
        <div class="signature-box">
            <div class="fw-bold">ĐẠI DIỆN CÔNG TY PO SUNG<br><span class="lang-en">ON BEHALF OF PO SUNG MEC</span></div>
            <div style="font-size: 11pt; font-style: italic; margin-bottom: 80px;">(Ký và đóng dấu / Sign & Stamp)</div>
            <div class="fw-bold">GIÁM ĐỐC NHÂN SỰ</div>
        </div>
    </div>
</div>

</body>
</html>
