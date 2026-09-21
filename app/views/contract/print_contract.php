<?php
/**
 * ============================================================
 *  View: contract/print_contract.php
 *  Bản in HTML - Hợp đồng Lao động
 * ============================================================
 */
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Hợp đồng LĐ - <?= h($contract->full_name) ?></title>
    <style>
        @page { size: A4; margin: 20mm; }
        body { font-family: "Times New Roman", Times, serif; font-size: 13pt; line-height: 1.5; color: #000; margin: 0; padding: 0; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }
        .print-container { width: 100%; max-width: 800px; margin: auto; }
        h1, h2, h3, h4 { margin: 5px 0; }
        .company-header { margin-bottom: 30px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .contract-title { font-size: 18pt; margin: 20px 0; text-transform: uppercase; }
        .section-title { font-weight: bold; text-decoration: underline; margin-top: 15px; }
        table { width: 100%; border-collapse: collapse; margin-top: 5px; }
        td { vertical-align: top; padding: 3px 0; }
        .w-30 { width: 30%; }
        .w-70 { width: 70%; }
        .signature-box { margin-top: 40px; display: flex; justify-content: space-between; }
        .signature-item { width: 45%; text-align: center; }
        .signature-space { height: 100px; }
    </style>
</head>
<body onload="window.print()">
    <div class="print-container">
        <div class="company-header text-center">
            <div class="fw-bold" style="font-size: 14pt;">CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM</div>
            <div class="fw-bold" style="text-decoration: underline; font-size: 14pt;">Độc lập - Tự do - Hạnh phúc</div>
            <div style="margin-top: 10px;">---oOo---</div>
        </div>

        <div class="text-center contract-title fw-bold">
            <?= h($contract->contract_type_name) ?>
        </div>
        <div class="text-center mb-4">
            Số: <?= h($contract->contract_number) ?>
        </div>

        <p>Hôm nay, ngày <?= date('d', strtotime($contract->start_date)) ?> tháng <?= date('m', strtotime($contract->start_date)) ?> năm <?= date('Y', strtotime($contract->start_date)) ?>, tại Công ty TNHH Po Sung MEC Việt Nam, chúng tôi gồm:</p>

        <div class="section-title">BÊN A (NGƯỜI SỬ DỤNG LAO ĐỘNG): CÔNG TY TNHH PO SUNG MEC VIỆT NAM</div>
        <table>
            <tr><td class="w-30">Đại diện bởi:</td><td class="w-70 fw-bold">Ông CHOI JAE SUNG</td></tr>
            <tr><td>Chức vụ:</td><td>Tổng Giám Đốc</td></tr>
            <tr><td>Địa chỉ:</td><td>Tầng 3, Tòa nhà HH3, Sudico, Nam Từ Liêm, Hà Nội</td></tr>
        </table>

        <div class="section-title">BÊN B (NGƯỜI LAO ĐỘNG):</div>
        <table>
            <tr><td class="w-30">Ông/Bà:</td><td class="w-70 fw-bold"><?= h($contract->full_name) ?></td></tr>
            <tr><td>Sinh ngày:</td><td><?= $contract->dob ? date('d/m/Y', strtotime($contract->dob)) : '...' ?></td></tr>
            <tr><td>Số CCCD:</td><td><?= h($contract->id_card) ?> cấp ngày <?= $contract->id_card_date ? date('d/m/Y', strtotime($contract->id_card_date)) : '...' ?> tại <?= h($contract->id_card_place) ?></td></tr>
            <tr><td>Thường trú:</td><td><?= h($contract->address) ?></td></tr>
        </table>

        <p>Cùng thỏa thuận ký kết hợp đồng lao động này và cam kết thực hiện đúng những điều khoản sau đây:</p>

        <div class="section-title">ĐIỀU 1: CÔNG VIỆC VÀ THỜI HẠN HỢP ĐỒNG</div>
        <p>- Loại hợp đồng: <strong><?= h($contract->contract_type_name) ?></strong></p>
        <p>- Từ ngày <strong><?= date('d/m/Y', strtotime($contract->start_date)) ?></strong> đến ngày <strong><?= $contract->end_date ? date('d/m/Y', strtotime($contract->end_date)) : 'Vô thời hạn' ?></strong>.</p>
        <p>- Chức danh chuyên môn: <strong><?= h($contract->pos_title ?? '---') ?></strong></p>
        <p>- Thuộc phòng ban/bộ phận: <strong><?= h($contract->dept_name ?? '---') ?></strong></p>
        <p>- Địa điểm làm việc: Theo sự phân công của Công ty.</p>

        <div class="section-title">ĐIỀU 2: CHẾ ĐỘ LÀM VIỆC VÀ TIỀN LƯƠNG</div>
        <p>- Thời giờ làm việc: 48 giờ/tuần, có thể điều chỉnh tùy theo tính chất công việc tại công trường.</p>
        <p>- Mức lương cơ bản: <strong><?= number_format($contract->basic_salary) ?> VNĐ/tháng</strong></p>
        <p>- Mức lương đóng BHXH: <strong><?= number_format($contract->insurance_salary) ?> VNĐ/tháng</strong></p>
        <p>- Phụ cấp: Theo quy định hiện hành của công ty đối với chức vụ và vị trí công tác.</p>
        <p>- Hình thức trả lương: Chuyển khoản hoặc tiền mặt vào mùng 10 hàng tháng.</p>
        <?php if (!empty($contract->note)): ?>
        <p>- Ghi chú thêm: <?= h($contract->note) ?></p>
        <?php endif; ?>

        <div class="section-title">ĐIỀU 3: NGHĨA VỤ VÀ QUYỀN LỢI CỦA NGƯỜI LAO ĐỘNG</div>
        <p>- Thực hiện công việc được giao theo đúng yêu cầu chất lượng, tiến độ và đảm bảo an toàn lao động (HSE).</p>
        <p>- Được trang bị BHLĐ (PPE) phù hợp với yêu cầu công việc.</p>
        <p>- Tham gia BHXH, BHYT, BHTN theo quy định của Luật Lao động Việt Nam.</p>

        <p style="margin-top: 30px;">Hợp đồng này được lập thành 02 bản có giá trị pháp lý như nhau, mỗi bên giữ 01 bản để thực hiện.</p>

        <div class="signature-box">
            <div class="signature-item">
                <div class="fw-bold">NGƯỜI LAO ĐỘNG</div>
                <div style="font-size: 11pt; font-style: italic;">(Ký và ghi rõ họ tên)</div>
                <div class="signature-space"></div>
                <div class="fw-bold"><?= h($contract->full_name) ?></div>
            </div>
            <div class="signature-item">
                <div class="fw-bold">ĐẠI DIỆN CÔNG TY</div>
                <div style="font-size: 11pt; font-style: italic;">(Ký, đóng dấu và ghi rõ họ tên)</div>
                <div class="signature-space"></div>
                <div class="fw-bold">CHOI JAE SUNG</div>
            </div>
        </div>
    </div>
</body>
</html>
