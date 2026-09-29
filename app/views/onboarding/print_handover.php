<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biên Bản Bàn Giao Hội Nhập – <?= htmlspecialchars($onboarding['full_name']) ?></title>
    <style>
        @page {
            size: A4;
            margin: 15mm;
        }
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 13pt;
            line-height: 1.4;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 20px;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .header-table td {
            vertical-align: top;
            padding: 0;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .fw-bold { font-weight: bold; }
        .text-uppercase { text-transform: uppercase; }

        .doc-title {
            text-align: center;
            font-size: 16pt;
            font-weight: bold;
            margin: 25px 0 5px 0;
            text-transform: uppercase;
        }
        .doc-subtitle {
            text-align: center;
            font-style: italic;
            font-size: 11pt;
            margin-bottom: 25px;
        }
        .info-table {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 5px 8px;
            font-size: 12pt;
        }
        .checklist-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .checklist-table th, .checklist-table td {
            border: 1px solid #000;
            padding: 6px 8px;
            font-size: 11pt;
        }
        .checklist-table th {
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
        }
        .dept-header-row td {
            background-color: #eaeaea;
            font-weight: bold;
            font-size: 11.5pt;
        }
        .signatures-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
            page-break-inside: avoid;
        }
        .signatures-table td {
            vertical-align: top;
            text-align: center;
            width: 20%;
            font-size: 11pt;
        }
        .sig-space {
            height: 75px;
        }
        .no-print {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #fff;
            padding: 10px 15px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            border: 1px solid #ccc;
        }
        .btn-print {
            background: #10b981;
            color: #fff;
            border: none;
            padding: 8px 16px;
            font-size: 14px;
            font-weight: bold;
            border-radius: 6px;
            cursor: pointer;
        }
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>

<div class="no-print">
    <button class="btn-print" onclick="window.print()">🖨 In Biên Bản (A4)</button>
</div>

<!-- Header Quốc hiệu & Đơn vị -->
<table class="header-table">
    <tr>
        <td class="text-center" style="width: 45%;">
            <strong>CÔNG TY CỔ PHẦN XÂY DỰNG POSUNG</strong><br>
            <span style="font-size: 11pt;">PHÒNG HÀNH CHÍNH NHÂN SỰ</span><br>
            <span style="font-size: 10pt; font-style: italic;">Số: ....... /BBBG-POSUNG</span>
        </td>
        <td class="text-center" style="width: 55%;">
            <strong>CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM</strong><br>
            <strong>Độc lập – Tự do – Hạnh phúc</strong><br>
            <span style="font-size: 11pt;">-------------------</span><br>
            <span style="font-size: 10pt; font-style: italic;">Hà Nội, ngày <?= date('d') ?> tháng <?= date('m') ?> năm <?= date('Y') ?></span>
        </td>
    </tr>
</table>

<!-- Tiêu đề văn bản -->
<div class="doc-title">BIÊN BẢN BÀN GIAO & TIẾP NHẬN NHÂN SỰ MỚI</div>
<div class="doc-subtitle">(Quy trình Hội nhập & Tiếp nhận Trang thiết bị, Hồ sơ lao động - Onboarding Checklist)</div>

<!-- Thông tin nhân sự -->
<table class="info-table">
    <tr>
        <td style="width: 22%;"><strong>Họ và tên nhân sự:</strong></td>
        <td style="width: 40%;"><strong class="text-uppercase"><?= htmlspecialchars($onboarding['full_name']) ?></strong></td>
        <td style="width: 18%;"><strong>Mã nhân viên:</strong></td>
        <td><strong><?= htmlspecialchars($onboarding['emp_code']) ?></strong></td>
    </tr>
    <tr>
        <td><strong>Vị trí / Chức danh:</strong></td>
        <td><?= htmlspecialchars($onboarding['pos_title'] ?? 'Nhân viên') ?></td>
        <td><strong>Phòng ban / Dự án:</strong></td>
        <td><?= htmlspecialchars($onboarding['dept_name'] ?? 'POSUNG') ?></td>
    </tr>
    <tr>
        <td><strong>Ngày nhận việc:</strong></td>
        <td><?= date('d/m/Y', strtotime($onboarding['start_date'])) ?></td>
        <td><strong>Quy trình áp dụng:</strong></td>
        <td><?= htmlspecialchars($onboarding['template_name']) ?></td>
    </tr>
</table>

<!-- Danh mục Checklist kiểm tra -->
<table class="checklist-table">
    <thead>
        <tr>
            <th style="width: 35px;">STT</th>
            <th>Danh mục bàn giao / Nhiệm vụ hội nhập</th>
            <th style="width: 130px;">Hạn hoàn tất</th>
            <th style="width: 120px;">Trạng thái</th>
            <th style="width: 180px;">Ghi chú / Số Serial</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $stt = 1;
        $deptNames = [
            'IT'      => '1. BỘ PHẬN CÔNG NGHỆ THÔNG TIN (IT SUPPORT)',
            'HR'      => '2. BỘ PHẬN HÀNH CHÍNH NHÂN SỰ (HR)',
            'HSE'     => '3. BỘ PHẬN AN TOÀN LAO ĐỘNG & MÔI TRƯỜNG (HSE)',
            'Admin'   => '4. BỘ PHẬN HÀNH CHÍNH TỔNG HỢP (ADMIN)',
            'Finance' => '5. BỘ PHẬN TÀI CHÍNH KẾ TOÁN (FINANCE)',
        ];

        foreach ($onboarding['items_by_dept'] as $deptKey => $tasks): 
        ?>
            <tr class="dept-header-row">
                <td colspan="5"><?= $deptNames[$deptKey] ?? ("BỘ PHẬN " . $deptKey) ?></td>
            </tr>
            <?php foreach ($tasks as $task): 
                $isDone = ($task['status'] === 'Done');
                $isSkipped = ($task['status'] === 'Skipped');
            ?>
            <tr>
                <td class="text-center"><?= $stt++ ?></td>
                <td>
                    <strong><?= htmlspecialchars($task['title']) ?></strong>
                    <?php if (!empty($task['description'])): ?>
                        <div style="font-size: 9.5pt; color: #444;"><?= htmlspecialchars($task['description']) ?></div>
                    <?php endif; ?>
                </td>
                <td class="text-center">
                    <?= !empty($task['due_date']) ? date('d/m/Y', strtotime($task['due_date'])) : '---' ?>
                </td>
                <td class="text-center">
                    <?php if ($isDone): ?>
                        <strong>[ Đã xong ]</strong>
                    <?php elseif ($isSkipped): ?>
                        <em>[ Bỏ qua ]</em>
                    <?php else: ?>
                        <span>Chưa hoàn tất</span>
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($task['notes'] ?? '---') ?></td>
            </tr>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </tbody>
</table>

<div style="font-size: 11pt; font-style: italic; margin-bottom: 10px;">
    * Người lao động cam kết đã nhận đầy đủ các trang thiết bị, phương tiện bảo hộ lao động và tài khoản được bàn giao ở trên; đồng thời cam đoan chấp hành nghiêm túc Nội quy lao động và Quy chuẩn an toàn thi công của POSUNG.
</div>

<!-- 5 Khung Ký Xác Nhận -->
<table class="signatures-table">
    <tr>
        <td>
            <strong>NGƯỜI LAO ĐỘNG</strong><br>
            <span style="font-size: 9.5pt; font-style: italic;">(Ký & ghi rõ họ tên)</span>
            <div class="sig-space"></div>
            <strong><?= htmlspecialchars($onboarding['full_name']) ?></strong>
        </td>
        <td>
            <strong>PHỤ TRÁCH IT</strong><br>
            <span style="font-size: 9.5pt; font-style: italic;">(Ký & ghi rõ họ tên)</span>
            <div class="sig-space"></div>
            ........................
        </td>
        <td>
            <strong>PHỤ TRÁCH HSE</strong><br>
            <span style="font-size: 9.5pt; font-style: italic;">(Ký & ghi rõ họ tên)</span>
            <div class="sig-space"></div>
            ........................
        </td>
        <td>
            <strong>HÀNH CHÍNH</strong><br>
            <span style="font-size: 9.5pt; font-style: italic;">(Ký & ghi rõ họ tên)</span>
            <div class="sig-space"></div>
            ........................
        </td>
        <td>
            <strong>TRƯỞNG PHÒNG NS</strong><br>
            <span style="font-size: 9.5pt; font-style: italic;">(Ký & đóng dấu)</span>
            <div class="sig-space"></div>
            ........................
        </td>
    </tr>
</table>

</body>
</html>
