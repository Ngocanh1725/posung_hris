<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Sơ Yếu Lý Lịch - <?= h($employee->full_name) ?></title>
    <style>
        /* Reset & Base */
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: "Times New Roman", Times, serif; font-size: 13pt; line-height: 1.4; color: #000; background: #e2e8f0; }
        
        /* A4 Page Setup */
        @page { size: A4; margin: 20mm 15mm; }
        .page {
            background: #fff;
            width: 210mm;
            min-height: 297mm;
            margin: 20px auto;
            padding: 20mm 15mm;
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
        .mb-2 { margin-bottom: 5px; }
        .mb-4 { margin-bottom: 15px; }
        .mt-4 { margin-top: 15px; }
        
        /* Header */
        .header { display: flex; justify-content: space-between; margin-bottom: 20px; }
        .header-left { width: 40%; text-align: center; }
        .header-right { width: 60%; text-align: center; }
        .title { font-size: 16pt; font-weight: bold; margin-top: 15px; text-transform: uppercase; }
        .subtitle { font-size: 12pt; font-style: italic; }

        /* Photo block */
        .photo-box {
            width: 30mm; height: 40mm; border: 1px solid #000;
            display: flex; align-items: center; justify-content: center;
            font-size: 10pt; color: #666; margin-right: 15px;
        }

        /* Content Rows */
        .row { display: flex; margin-bottom: 8px; flex-wrap: wrap; }
        .col { flex: 1; }
        .label { display: inline-block; }
        .value { display: inline-block; font-weight: bold; border-bottom: 1px dotted #000; min-width: 50px; flex: 1; }
        .d-flex { display: flex; width: 100%; }

        /* Tables */
        table { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 15px; font-size: 12pt; }
        th, td { border: 1px solid #000; padding: 5px; text-align: left; }
        th { text-align: center; font-weight: bold; background: #f9f9f9; }

        /* Utilities */
        .signature-area { display: flex; justify-content: space-between; margin-top: 30px; }
        .signature-box { text-align: center; width: 45%; }
    </style>
</head>
<body>

<!-- Nút in (ẩn khi in) -->
<div class="text-center no-print" style="padding: 15px; background: #fff; margin-bottom: 20px; border-bottom: 2px solid #ccc;">
    <button onclick="window.print()" style="padding: 10px 20px; font-size: 16px; cursor: pointer; background: #2563eb; color: #fff; border: none; border-radius: 5px;">
        🖨️ In Mẫu 2C-BNV/2008
    </button>
    <button onclick="window.close()" style="padding: 10px 20px; font-size: 16px; cursor: pointer; background: #64748b; color: #fff; border: none; border-radius: 5px; margin-left: 10px;">
        Đóng
    </button>
</div>

<div class="page">
    <!-- Header Mẫu 2C -->
    <div class="header">
        <div class="header-left">
            <div>Cơ quan, đơn vị có thẩm quyền quản lý CBCC: <br><span class="fw-bold">Po Sung MEC</span></div>
            <div>Cơ quan, đơn vị sử dụng CBCC: <br><span class="fw-bold"><?= h($employee->dept_name ?? 'Khối Dự án') ?></span></div>
        </div>
        <div class="header-right">
            <div class="fw-bold" style="font-size: 14pt;">CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM</div>
            <div class="fw-bold" style="text-decoration: underline;">Độc lập - Tự do - Hạnh phúc</div>
            <div style="margin-top: 5px;">Mẫu 2C-BNV/2008 (Dành cho Doanh nghiệp)</div>
            <div>Mã số NV: <span class="fw-bold"><?= h($employee->emp_code) ?></span></div>
        </div>
    </div>

    <!-- Tiêu đề -->
    <div style="display: flex; margin-bottom: 20px;">
        <div class="photo-box">Ảnh<br>(4x6)</div>
        <div style="flex: 1; text-align: center;">
            <div class="title">SƠ YẾU LÝ LỊCH CÁN BỘ, CÔNG CHỨC, VIÊN CHỨC</div>
            <div class="subtitle">(Tương thích định dạng nhân sự Doanh nghiệp)</div>
        </div>
    </div>

    <!-- I. Thông tin cá nhân -->
    <div class="fw-bold mb-2">I. THÔNG TIN BẢN THÂN</div>
    
    <div class="d-flex mb-2">
        <div style="width: 50%;">1) Họ và tên khai sinh: <span class="value text-uppercase"><?= h($employee->full_name) ?></span></div>
        <div style="width: 50%;">2) Tên gọi khác: <span class="value text-uppercase">---</span></div>
    </div>
    
    <div class="d-flex mb-2">
        <div style="width: 30%;">3) Sinh ngày: <span class="value"><?= fmtDate($employee->dob) ?></span></div>
        <div style="width: 20%;">Giới tính: <span class="value"><?= $employee->gender === 'Male' ? 'Nam' : ($employee->gender === 'Female' ? 'Nữ' : 'Khác') ?></span></div>
        <div style="width: 50%;">4) Nơi sinh: <span class="value"><?= h($employee->hometown ?: '..........................................') ?></span></div>
    </div>

    <div class="d-flex mb-2">
        <div style="width: 100%;">5) Quê quán: <span class="value"><?= h($employee->hometown ?: '....................................................................................................') ?></span></div>
    </div>

    <div class="d-flex mb-2">
        <div style="width: 100%;">6) Nơi đăng ký thường trú: <span class="value"><?= h($employee->address ?: '....................................................................................................') ?></span></div>
    </div>

    <div class="d-flex mb-2">
        <div style="width: 40%;">7) Nơi ở hiện tại: <span class="value"><?= h($employee->address ?: '..........................................') ?></span></div>
        <div style="width: 30%;">8) Điện thoại: <span class="value"><?= h($employee->phone ?: '................') ?></span></div>
        <div style="width: 30%;">9) Dân tộc: <span class="value"><?= h($employee->ethnic ?: '................') ?></span></div>
    </div>

    <div class="d-flex mb-2">
        <div style="width: 30%;">10) Tôn giáo: <span class="value"><?= h($employee->religion ?: 'Không') ?></span></div>
        <div style="width: 70%;">11) Số CMND/CCCD: <span class="value"><?= h($employee->id_card) ?></span> Cấp ngày: <span class="value"><?= fmtDate($employee->id_card_date) ?></span></div>
    </div>

    <div class="d-flex mb-2">
        <div style="width: 100%;">12) Trình độ giáo dục phổ thông (đã tốt nghiệp lớp mấy/thuộc hệ nào): <span class="value">12/12</span></div>
    </div>

    <div class="d-flex mb-2">
        <div style="width: 100%;">13) Trình độ chuyên môn cao nhất: <span class="value"><?= h($employee->highest_degree ?: '..........................................') ?></span></div>
    </div>

    <div class="d-flex mb-2">
        <div style="width: 50%;">14) Chức vụ hiện tại: <span class="value"><?= h($employee->pos_title ?: '..........................................') ?></span></div>
        <div style="width: 50%;">15) Ngày tuyển dụng: <span class="value"><?= fmtDate($employee->join_date) ?></span></div>
    </div>

    <!-- II. Lịch sử bản thân (Quá trình công tác) -->
    <div class="fw-bold mt-4 mb-2">II. TÓM TẮT QUÁ TRÌNH CÔNG TÁC</div>
    <table>
        <thead>
            <tr>
                <th style="width: 25%">Từ tháng, năm đến tháng, năm</th>
                <th style="width: 75%">Chức danh, chức vụ, đơn vị công tác (Đảng, Chính quyền, Đoàn thể, Công ty)</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($employee->work_experiences)): ?>
                <?php foreach($employee->work_experiences as $we): ?>
                <tr>
                    <td class="text-center"><?= fmtDate($we->start_date, 'm/Y') ?> - <?= fmtDate($we->end_date, 'm/Y') ?></td>
                    <td><?= h($we->position) ?> tại <?= h($we->company_name) ?></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td class="text-center">.../... - .../...</td><td>....................................................................................................</td></tr>
                <tr><td class="text-center">.../... - .../...</td><td>....................................................................................................</td></tr>
                <tr><td class="text-center">.../... - .../...</td><td>....................................................................................................</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- III. Quan hệ gia đình -->
    <div class="fw-bold mt-4 mb-2">III. QUAN HỆ GIA ĐÌNH</div>
    <table>
        <thead>
            <tr>
                <th style="width: 15%">Quan hệ</th>
                <th style="width: 25%">Họ và tên</th>
                <th style="width: 15%">Năm sinh</th>
                <th style="width: 45%">Quê quán, nghề nghiệp, chức danh, chức vụ, đơn vị công tác</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($employee->dependents)): ?>
                <?php foreach($employee->dependents as $dep): ?>
                <tr>
                    <td class="text-center"><?= h($dep->relationship) ?></td>
                    <td><?= h($dep->full_name) ?></td>
                    <td class="text-center"><?= fmtDate($dep->dob, 'Y') ?></td>
                    <td>Đăng ký người phụ thuộc, giảm trừ gia cảnh (Mã số thuế cá nhân)</td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td>................</td><td>....................................</td><td>................</td><td>....................................................................................</td></tr>
                <tr><td>................</td><td>....................................</td><td>................</td><td>....................................................................................</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Chữ ký -->
    <div class="signature-area">
        <div class="signature-box">
            <div class="fw-bold">NGƯỜI KHAI</div>
            <div style="font-size: 11pt; font-style: italic;">Tôi xin cam đoan những lời khai trên đây là đúng sự thật</div>
            <div style="margin-top: 80px;" class="fw-bold"><?= h($employee->full_name) ?></div>
        </div>
        <div class="signature-box">
            <div>Ngày ..... tháng ..... năm 20.....</div>
            <div class="fw-bold">THỦ TRƯỞNG CƠ QUAN, ĐƠN VỊ<br>QUẢN LÝ VÀ SỬ DỤNG</div>
            <div style="font-size: 11pt; font-style: italic;">(Ký tên, đóng dấu)</div>
            <div style="margin-top: 80px;">............................................</div>
        </div>
    </div>

</div>
</body>
</html>
