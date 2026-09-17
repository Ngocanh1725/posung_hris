<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thư mời nhận việc – <?= htmlspecialchars($candidate->full_name) ?></title>
    <style>
        @page { size: A4; margin: 25mm; }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Times New Roman',serif; font-size:14px; line-height:1.8; color:#000; background:#fff; }
        .container { max-width:700px; margin:0 auto; padding:20px; }
        .header { display:flex; justify-content:space-between; margin-bottom:30px; }
        .header-left { text-align:center; width:45%; }
        .header-right { text-align:center; width:45%; }
        .company { font-size:14px; font-weight:bold; text-transform:uppercase; }
        hr.short { border:none; border-bottom:1px solid #000; width:60px; margin:5px auto; }
        h1 { text-align:center; font-size:18px; text-transform:uppercase; margin:20px 0 5px; }
        .subtitle { text-align:center; font-style:italic; margin-bottom:20px; }
        p { text-indent:40px; margin-bottom:8px; }
        .info-row { text-indent:40px; }
        .signatures { display:flex; justify-content:space-between; margin-top:50px; }
        .sig-block { text-align:center; width:45%; }
        .sig-title { font-weight:bold; text-transform:uppercase; }
        .sig-name { font-weight:bold; margin-top:60px; }
        .print-btn { position:fixed; top:20px; right:20px; padding:10px 24px; background:#4f46e5; color:#fff; border:none; border-radius:8px; cursor:pointer; font-size:14px; font-weight:600; z-index:999; }
        @media print { .no-print { display:none !important; } .container { max-width:none; padding:0; } }
    </style>
</head>
<body>
<button class="print-btn no-print" onclick="window.print()">🖨️ In thư mời</button>
<div class="container">
    <div class="header">
        <div class="header-left">
            <p class="company">Công ty TNHH Cơ Khí<br>Kỹ thuật Xây dựng Po Sung</p>
            <hr class="short">
        </div>
        <div class="header-right">
            <p style="font-weight:bold;">CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM</p>
            <p style="font-weight:bold;">Độc lập – Tự do – Hạnh phúc</p>
            <hr class="short">
            <p style="font-style:italic; font-size:13px;">Hà Nội, ngày <?= date('d') ?> tháng <?= date('m') ?> năm <?= date('Y') ?></p>
        </div>
    </div>

    <h1>THƯ MỜI NHẬN VIỆC</h1>
    <p class="subtitle">(Offer Letter)</p>

    <p>Kính gửi: Ông/Bà <strong><?= htmlspecialchars($candidate->full_name) ?></strong></p>
    
    <p>Công ty TNHH Cơ khí Kỹ thuật Xây dựng Po Sung trân trọng thông báo kết quả phỏng vấn và mời Ông/Bà nhận việc với các thông tin như sau:</p>

    <div style="margin:20px 0; padding:15px 20px; border:1px solid #ccc; border-radius:4px;">
        <p class="info-row">1. <strong>Vị trí:</strong> <?= htmlspecialchars($candidate->pos_title ?? 'Theo thỏa thuận') ?></p>
        <p class="info-row">2. <strong>Phòng ban:</strong> <?= htmlspecialchars($candidate->dept_name ?? 'Theo bố trí') ?></p>
        <p class="info-row">3. <strong>Mức lương:</strong> <?= $candidate->offer_salary ? number_format($candidate->offer_salary, 0, ',', '.') . ' VNĐ/tháng' : 'Theo thỏa thuận' ?></p>
        <p class="info-row">4. <strong>Ngày bắt đầu:</strong> <?= $candidate->start_date ? date('d/m/Y', strtotime($candidate->start_date)) : '___/___/______' ?></p>
        <p class="info-row">5. <strong>Loại hợp đồng:</strong> Hợp đồng thử việc (02 tháng)</p>
        <p class="info-row">6. <strong>Địa điểm làm việc:</strong> Theo bố trí của Công ty</p>
    </div>

    <p><strong>Quyền lợi:</strong></p>
    <ul style="margin-left:60px; margin-bottom:15px;">
        <li>Được tham gia BHXH, BHYT, BHTN theo quy định</li>
        <li>Phụ cấp ăn trưa, phụ cấp đi lại (nếu có)</li>
        <li>Thưởng Tết, thưởng dự án theo kết quả kinh doanh</li>
        <li>Được đào tạo nâng cao nghiệp vụ</li>
    </ul>

    <p><strong>Hồ sơ nhận việc cần chuẩn bị:</strong></p>
    <ul style="margin-left:60px; margin-bottom:15px;">
        <li>Đơn xin việc (theo mẫu Công ty)</li>
        <li>Sơ yếu lý lịch có xác nhận (06 tháng gần nhất)</li>
        <li>Bản sao CCCD, Hộ khẩu</li>
        <li>Bản sao bằng cấp, chứng chỉ</li>
        <li>Giấy khám sức khỏe (06 tháng gần nhất)</li>
        <li>04 ảnh 3x4</li>
    </ul>

    <p>Vui lòng xác nhận tham gia trước ngày <strong><?= $candidate->offer_date ? date('d/m/Y', strtotime($candidate->offer_date . ' +7 days')) : '___/___/______' ?></strong> bằng cách liên hệ Phòng HC-NS qua số điện thoại: <strong>024-3755-0010</strong> hoặc email: <strong>hr@posung.vn</strong>.</p>

    <p>Trân trọng kính mời.</p>

    <div class="signatures">
        <div class="sig-block"></div>
        <div class="sig-block">
            <p class="sig-title">TRƯỞNG PHÒNG HC-NS</p>
            <p style="font-style:italic; font-size:12px;">(Ký, ghi rõ họ tên)</p>
            <p class="sig-name">.............................</p>
        </div>
    </div>
</div>
</body>
</html>
