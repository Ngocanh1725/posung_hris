<?php
/**
 * ============================================================
 *  POSUNG HRIS – View: asset/receipt.php
 * ============================================================
 *  Biên bản Bàn giao Tài sản & Thiết bị Công ty (Printable A4)
 * ============================================================
 */
$asg = $assignment;
$assignDateStr = $asg->assigned_date ? date('d/m/Y', strtotime($asg->assigned_date)) : date('d/m/Y');
$day = date('d', strtotime($asg->assigned_date ?? 'now'));
$month = date('m', strtotime($asg->assigned_date ?? 'now'));
$year = date('Y', strtotime($asg->assigned_date ?? 'now'));
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biên bản bàn giao tài sản - <?= h($asg->asset_code) ?> - <?= h($asg->employee_name) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @page {
            size: A4;
            margin: 15mm 20mm;
        }
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 13pt;
            line-height: 1.4;
            color: #000;
            background: #f4f6f9;
            margin: 0;
            padding: 20px;
        }
        .a4-container {
            width: 210mm;
            min-height: 297mm;
            padding: 20mm;
            margin: 0 auto;
            background: #fff;
            box-shadow: 0 0 10px rgba(0,0,0,0.15);
            box-sizing: border-box;
            position: relative;
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
        .text-end { text-align: right; }
        .fw-bold { font-weight: bold; }
        .fst-italic { font-style: italic; }
        .text-uppercase { text-transform: uppercase; }

        .company-name {
            font-size: 11pt;
            font-weight: bold;
        }
        .company-sub {
            font-size: 10pt;
        }
        .doc-title {
            font-size: 16pt;
            font-weight: bold;
            text-transform: uppercase;
            text-align: center;
            margin-top: 15px;
            margin-bottom: 5px;
        }
        .doc-no {
            text-align: center;
            font-size: 11pt;
            font-style: italic;
            margin-bottom: 20px;
        }
        .content-section {
            margin-bottom: 15px;
        }
        .section-title {
            font-weight: bold;
            font-size: 13pt;
            margin-top: 12px;
            margin-bottom: 6px;
        }
        .info-row {
            margin-bottom: 4px;
        }
        .dotted-line {
            border-bottom: 1px dotted #000;
            display: inline-block;
            min-width: 150px;
        }
        .asset-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        .asset-table th, .asset-table td {
            border: 1px solid #000;
            padding: 7px 9px;
            font-size: 12pt;
        }
        .asset-table th {
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
        }
        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
            page-break-inside: avoid;
        }
        .signature-table td {
            text-align: center;
            vertical-align: top;
            width: 33.33%;
            padding: 0;
        }
        .sign-box {
            min-height: 85px;
        }

        /* Nút công cụ in ấn */
        .print-toolbar {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            background: #fff;
            padding: 10px 15px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            display: flex;
            gap: 10px;
        }
        .btn-print {
            background: #2563eb;
            color: #fff;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-print:hover { background: #1d4ed8; }
        .btn-back {
            background: #f3f4f6;
            color: #374151;
            border: 1px solid #d1d5db;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 14px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        @media print {
            body {
                background: #fff;
                padding: 0;
            }
            .a4-container {
                width: 100%;
                min-height: auto;
                padding: 0;
                box-shadow: none;
                margin: 0;
            }
            .print-toolbar {
                display: none !important;
            }
        }
    </style>
</head>
<body>

    <!-- THANH CÔNG CỤ IN -->
    <div class="print-toolbar">
        <a href="javascript:window.close()" class="btn-back"><i class="fas fa-times"></i> Đóng</a>
        <button onclick="window.print()" class="btn-print"><i class="fas fa-print"></i> In Biên Bản (A4)</button>
    </div>

    <div class="a4-container">
        <!-- HEADER CÔNG TY & TIÊU NGỮ -->
        <table class="header-table">
            <tr>
                <td style="width: 45%;">
                    <div class="company-name">CÔNG TY TNHH POSUNG VINA</div>
                    <div class="company-sub">PHÒNG HÀNH CHÍNH - NHÂN SỰ</div>
                    <div style="font-size: 9.5pt; font-style: italic;">Số: <?= str_pad($asg->id, 4, '0', STR_PAD_LEFT) ?>/BB-BGTS/<?= $year ?></div>
                </td>
                <td style="width: 55%;" class="text-center">
                    <div class="fw-bold" style="font-size: 11pt;">CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM</div>
                    <div class="fw-bold" style="font-size: 11pt;">Độc lập – Tự do – Hạnh phúc</div>
                    <div style="font-size: 10pt; margin-top: 2px;">--------------o0o--------------</div>
                </td>
            </tr>
        </table>

        <!-- TIÊU ĐỀ BIÊN BẢN -->
        <div class="doc-title">BIÊN BẢN BÀN GIAO TÀI SẢN & THIẾT BỊ</div>
        <div class="doc-no">Hôm nay, ngày <?= $day ?> tháng <?= $month ?> năm <?= $year ?>, tại Văn phòng Công ty TNHH Posung Vina, chúng tôi gồm có:</div>

        <!-- BÊN GIAO (BÊN A) -->
        <div class="section-title">I. ĐẠI DIỆN BÊN GIAO (BÊN A - CÔNG TY):</div>
        <div class="info-row">- Ông/Bà: <strong><?= h($asg->assigned_by_fullname ?? $asg->assigned_by_name ?? 'Bộ phận Quản trị Thiết bị') ?></strong></div>
        <div class="info-row">- Chức vụ: Cán bộ Quản lý Tài sản / Đại diện Phòng HC-NS</div>
        <div class="info-row">- Đơn vị công tác: Công ty TNHH Posung Vina</div>

        <!-- BÊN NHẬN (BÊN B) -->
        <div class="section-title" style="margin-top: 10px;">II. ĐẠI DIỆN BÊN NHẬN (BÊN B - NGƯỜI LAO ĐỘNG):</div>
        <div class="info-row">- Ông/Bà: <strong><?= h($asg->employee_name) ?></strong> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Mã nhân viên: <strong><?= h($asg->emp_code) ?></strong></div>
        <div class="info-row">- Số CCCD/CMND: <?= h($asg->id_card_no ?: '..........................................') ?> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Điện thoại: <?= h($asg->employee_phone ?: '..................................') ?></div>
        <div class="info-row">- Chức vụ: <?= h($asg->pos_title ?: 'Nhân viên') ?> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Phòng ban / Bộ phận: <?= h($asg->dept_name ?: '..................................') ?></div>

        <!-- DANH MỤC TÀI SẢN BÀN GIAO -->
        <div class="section-title" style="margin-top: 10px;">III. NỘI DUNG BÀN GIAO TÀI SẢN & TRANG THIẾT BỊ:</div>
        <div>Bên A tiến hành bàn giao cho Bên B quản lý và sử dụng các tài sản sau:</div>

        <table class="asset-table">
            <thead>
                <tr>
                    <th style="width: 6%;">STT</th>
                    <th style="width: 32%;">Tên tài sản / Model</th>
                    <th style="width: 18%;">Mã quản lý</th>
                    <th style="width: 18%;">Số Serial / IMEI</th>
                    <th style="width: 13%;">Tình trạng</th>
                    <th style="width: 13%;">Nguyên giá</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="text-center">1</td>
                    <td>
                        <strong><?= h($asg->asset_name) ?></strong>
                        <?php if ($asg->category_name): ?>
                            <div style="font-size: 10pt; color: #444;">Nhóm: <?= h($asg->category_name) ?></div>
                        <?php endif; ?>
                    </td>
                    <td class="text-center fw-bold" style="font-family: monospace;"><?= h($asg->asset_code) ?></td>
                    <td class="text-center" style="font-family: monospace;"><?= h($asg->serial_number ?: '---') ?></td>
                    <td class="text-center"><?= h($asg->condition_on_assign ?? 'Hoạt động tốt') ?></td>
                    <td class="text-end" style="font-family: monospace;"><?= number_format($asg->purchase_cost ?? 0, 0, ',', '.') ?> đ</td>
                </tr>
            </tbody>
        </table>

        <!-- PHỤ KIỆN VÀ GHI CHÚ -->
        <div class="info-row">
            <strong>* Phụ kiện & Hồ sơ kèm theo:</strong> 
            <?= h($asg->notes ?: 'Đầy đủ phụ kiện tiêu chuẩn đi kèm thiết bị.') ?>
        </div>

        <!-- TRÁCH NHIỆM & CAM KẾT -->
        <div class="section-title" style="margin-top: 10px;">IV. TRÁCH NHIỆM VÀ CAM KẾT CỦA BÊN NHẬN:</div>
        <div style="font-size: 11.5pt; text-align: justify; line-height: 1.4;">
            <p style="margin: 3px 0;">1. Bên B có trách nhiệm quản lý, bảo quản cẩn thận tài sản được giao và chỉ sử dụng phục vụ mục đích công việc của Công ty.</p>
            <p style="margin: 3px 0;">2. Không được tự ý cho mượn, tháo lắp, thay đổi cấu hình, chuyển nhượng hoặc mang tài sản ra khỏi trụ sở/công trường khi chưa có sự phê duyệt bằng văn bản.</p>
            <p style="margin: 3px 0;">3. Nếu để xảy ra mất mát, hư hỏng do nguyên nhân chủ quan hoặc bất cẩn, Bên B phải hoàn toàn chịu trách nhiệm bồi thường theo Quy chế tài sản của Posung Vina.</p>
            <p style="margin: 3px 0;">4. Khi chấm dứt Hợp đồng lao động, chuyển công tác hoặc khi có yêu cầu thu hồi, Bên B có nghĩa vụ bàn giao hoàn trả đầy đủ toàn bộ tài sản và linh kiện đính kèm nguyên vẹn.</p>
        </div>

        <div>Biên bản này được lập thành 02 (hai) bản có giá trị pháp lý như nhau, mỗi bên giữ 01 bản để làm căn cứ thực hiện.</div>

        <!-- CHỮ KÝ CÁC BÊN -->
        <table class="signature-table">
            <tr>
                <td>
                    <div class="fw-bold">ĐẠI DIỆN BÊN GIAO</div>
                    <div class="fst-italic" style="font-size: 10pt;">(Ký và ghi rõ họ tên)</div>
                    <div class="sign-box"></div>
                    <div class="fw-bold"><?= h($asg->assigned_by_fullname ?? $asg->assigned_by_name ?? '') ?></div>
                </td>
                <td>
                    <div class="fw-bold">ĐẠI DIỆN BÊN NHẬN</div>
                    <div class="fst-italic" style="font-size: 10pt;">(Ký và ghi rõ họ tên)</div>
                    <div class="sign-box"></div>
                    <div class="fw-bold"><?= h($asg->employee_name) ?></div>
                </td>
                <td>
                    <div class="fw-bold">PHÒNG HC-NS / DUYỆT</div>
                    <div class="fst-italic" style="font-size: 10pt;">(Ký và đóng dấu)</div>
                    <div class="sign-box"></div>
                    <div class="fw-bold">Ban Giám Đốc</div>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
