<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quyết định <?= h($record->type === 'Reward' ? 'Khen thưởng' : 'Kỷ luật') ?> - <?= h($record->decision_number) ?></title>
    <style>
        /* CSS reset & base cho trang in */
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: "Times New Roman", Times, serif; font-size: 14pt; line-height: 1.5; color: #000; background: #e2e8f0; }
        
        @page { size: A4 portrait; margin: 20mm 20mm 20mm 25mm; } /* Chuẩn văn bản VN: lề trái 25-30, phải 15-20 */
        .page {
            background: #fff;
            width: 210mm;
            min-height: 297mm;
            margin: 20px auto;
            padding: 20mm 20mm 20mm 25mm;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        @media print {
            body { background: #fff; }
            .page { margin: 0; padding: 0; box-shadow: none; width: 100%; min-height: auto; }
            .no-print { display: none !important; }
        }

        /* Utilities */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .fw-bold { font-weight: bold; }
        .text-uppercase { text-transform: uppercase; }
        .text-italic { font-style: italic; }
        .mb-2 { margin-bottom: 5px; }
        .mb-3 { margin-bottom: 10px; }
        .mt-3 { margin-top: 15px; }
        .mt-4 { margin-top: 25px; }

        /* Header (Quốc hiệu, Tiêu ngữ, Tên CQ) */
        .header-table { width: 100%; margin-bottom: 25px; }
        .header-table td { vertical-align: top; text-align: center; }
        .col-cq { width: 45%; }
        .col-qh { width: 55%; }
        .line-cq { border-top: 1px solid #000; width: 40%; margin: 5px auto 0; }
        .line-qh { border-top: 1px solid #000; width: 50%; margin: 5px auto 0; }

        /* Tên Quyết định */
        .decision-title { font-size: 15pt; font-weight: bold; text-align: center; margin: 30px 0 15px; }
        
        /* Căn cứ */
        .cancu-list { list-style-type: none; padding: 0; text-align: justify; font-style: italic; margin-bottom: 20px; }
        .cancu-list li { margin-bottom: 5px; text-indent: 30px; }

        /* Quyết định (Thẩm quyền) */
        .quyet-dinh { text-align: center; font-weight: bold; font-size: 14pt; margin: 25px 0 15px; }

        /* Điều khoản */
        .article { margin-bottom: 10px; text-align: justify; }
        .article-title { font-weight: bold; }

        /* Chữ ký & Nơi nhận */
        .footer-table { width: 100%; margin-top: 40px; }
        .footer-table td { vertical-align: top; }
        .col-noinhan { width: 50%; font-size: 11pt; }
        .col-chuky { width: 50%; text-align: center; }
    </style>
</head>
<body>

<div class="text-center no-print" style="padding: 15px; background: #fff; margin-bottom: 20px; border-bottom: 2px solid #ccc;">
    <button onclick="window.print()" style="padding: 10px 20px; font-size: 16px; cursor: pointer; background: #b91c1c; color: #fff; border: none; border-radius: 5px; font-weight: bold;">
        🖨️ In Quyết Định (A4)
    </button>
</div>

<div class="page">
    <table class="header-table">
        <tr>
            <td class="col-cq">
                <div class="text-uppercase" style="font-size: 12pt;">CÔNG TY TNHH CK KT XD<br>PO SUNG MEC</div>
                <div class="fw-bold" style="font-size: 13pt;">BAN GIÁM ĐỐC</div>
                <div class="line-cq"></div>
                <div class="mt-3">Số: <?= h($record->decision_number) ?>/QĐ-PS</div>
            </td>
            <td class="col-qh">
                <div class="fw-bold text-uppercase" style="font-size: 13pt;">CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM</div>
                <div class="fw-bold" style="font-size: 14pt;">Độc lập - Tự do - Hạnh phúc</div>
                <div class="line-qh"></div>
                <div class="mt-3 text-italic" style="text-align: right; padding-right: 20px;">
                    Hà Nội, ngày <?= date('d', strtotime($record->decision_date)) ?> tháng <?= date('m', strtotime($record->decision_date)) ?> năm <?= date('Y', strtotime($record->decision_date)) ?>
                </div>
            </td>
        </tr>
    </table>

    <div class="decision-title text-uppercase">
        QUYẾT ĐỊNH<br>
        <span style="font-size: 14pt; font-weight: normal;">
            Về việc <?= $record->type === 'Reward' ? 'Khen thưởng' : 'Kỷ luật' ?> Cán bộ / Nhân viên
        </span>
    </div>

    <ul class="cancu-list">
        <li>Căn cứ Bộ luật Lao động của nước Cộng hòa Xã hội Chủ nghĩa Việt Nam;</li>
        <li>Căn cứ Điều lệ tổ chức và hoạt động của Công ty TNHH Cơ khí Kỹ thuật Xây dựng Po Sung;</li>
        <li>Căn cứ Nội quy lao động và các Quy chế hiện hành của Công ty;</li>
        <?php if ($record->is_safety_violation): ?>
        <li>Căn cứ Quy định An toàn, Sức khỏe và Môi trường (HSE) tại công trường;</li>
        <?php endif; ?>
        <li>Xét đề nghị của Trưởng phòng Nhân sự và <?= h($record->proposed_by) ?>.</li>
    </ul>

    <div class="quyet-dinh">
        GIÁM ĐỐC CÔNG TY<br>QUYẾT ĐỊNH:
    </div>

    <div class="article">
        <span class="article-title">Điều 1.</span> Nay thi hành Quyết định <?= $record->type === 'Reward' ? 'Khen thưởng' : 'Kỷ luật' ?> đối với:
        <ul style="list-style-type: none; padding-left: 30px; margin-top: 5px;">
            <li>- Ông/Bà: <strong><?= h($record->full_name) ?></strong> (Mã NV: <?= h($record->emp_code) ?>)</li>
            <li>- Chức vụ: <?= h($record->pos_title ?? 'N/A') ?></li>
            <li>- Bộ phận/Dự án: <?= h($record->project_name ?? $record->dept_name ?? 'N/A') ?></li>
        </ul>
    </div>

    <div class="article">
        <span class="article-title">Điều 2.</span> Hình thức và mức độ <?= $record->type === 'Reward' ? 'Khen thưởng' : 'Kỷ luật' ?>:
        <ul style="list-style-type: none; padding-left: 30px; margin-top: 5px;">
            <li>- Hình thức: <strong><?= h($record->reward_form ?? $record->discipline_form ?? 'Theo quy định') ?></strong></li>
            <li>- Lý do: <?= nl2br(h($record->reason)) ?></li>
            <?php if ($record->amount > 0): ?>
            <li>- Số tiền <?= $record->type === 'Reward' ? 'thưởng' : 'phạt' ?>: <strong><?= number_format($record->amount, 0, ',', '.') ?> VNĐ</strong></li>
            <?php endif; ?>
        </ul>
    </div>

    <div class="article">
        <span class="article-title">Điều 3.</span> Quyết định này có hiệu lực kể từ ngày ký.
    </div>
    
    <div class="article">
        <span class="article-title">Điều 4.</span> Các Trưởng phòng Hành chính Nhân sự, Kế toán trưởng, Quản lý dự án <?= h($record->project_name ?? '') ?> và Ông/Bà <strong><?= h($record->full_name) ?></strong> chịu trách nhiệm thi hành Quyết định này./.
    </div>

    <table class="footer-table">
        <tr>
            <td class="col-noinhan">
                <div class="fw-bold text-italic">Nơi nhận:</div>
                <ul style="list-style-type: none; padding-left: 10px; margin-top: 5px;">
                    <li>- Như Điều 4;</li>
                    <li>- Lưu: VT, HCNS.</li>
                </ul>
            </td>
            <td class="col-chuky">
                <div class="fw-bold text-uppercase" style="font-size: 13pt; margin-bottom: 80px;">GIÁM ĐỐC CÔNG TY</div>
                <div class="fw-bold" style="font-size: 13pt;">(Đã ký)</div>
                <div class="fw-bold mt-2" style="font-size: 14pt;"><?= h($record->approver_name ?? 'LEE CHANG BUM') ?></div>
            </td>
        </tr>
    </table>
</div>

</body>
</html>
