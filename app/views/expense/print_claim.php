<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biên Bản Quyết Toán Chi Phí - <?= h($claim->claim_code) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 13pt;
            color: #000;
            background: #f4f6f9;
            margin: 0;
            padding: 20px;
        }
        .page {
            background: #fff;
            max-width: 800px;
            margin: 0 auto;
            padding: 40px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        .company-info h3 {
            margin: 0 0 4px 0;
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .company-info p {
            margin: 0;
            font-size: 10pt;
            color: #444;
        }
        .voucher-meta {
            text-align: right;
            font-size: 10pt;
        }
        .title-block {
            text-align: center;
            margin-bottom: 25px;
        }
        .title-block h2 {
            margin: 0 0 6px 0;
            font-size: 16pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .title-block .claim-code {
            font-style: italic;
            font-size: 11pt;
        }
        .info-table {
            width: 100%;
            margin-bottom: 20px;
        }
        .info-table td {
            padding: 4px 8px;
            vertical-align: top;
        }
        .info-table td.label {
            width: 25%;
            font-weight: bold;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .items-table th, .items-table td {
            border: 1px solid #000;
            padding: 6px 10px;
            font-size: 11pt;
        }
        .items-table th {
            background: #f0f0f0;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
        }
        .summary-box {
            margin-top: 15px;
            margin-bottom: 30px;
            font-size: 12pt;
        }
        .signatures {
            display: flex;
            justify-content: space-between;
            text-align: center;
            margin-top: 40px;
        }
        .sig-block {
            width: 24%;
        }
        .sig-block .title {
            font-weight: bold;
            margin-bottom: 60px;
        }
        .sig-block .name {
            font-weight: bold;
        }
        .print-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #2563eb;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(37,99,235,0.3);
        }
        @media print {
            body {
                background: #fff;
                padding: 0;
            }
            .page {
                box-shadow: none;
                padding: 0;
                max-width: 100%;
            }
            .print-btn {
                display: none;
            }
        }
    </style>
</head>
<body>
    <button class="print-btn" onclick="window.print()">
        <i class="fas fa-print me-1"></i> In Biên Bản (Ctrl + P)
    </button>

    <div class="page">
        <!-- HEADER CÔNG TY -->
        <div class="header">
            <div class="company-info">
                <h3>CÔNG TY TNHH CƠ ĐIỆN PO SUNG</h3>
                <p>Địa chỉ: Lô CN02, KCN Điềm Thụy, Thái Nguyên</p>
                <p>Website: www.posung.vn &bull; Hotline: 0280.3888.999</p>
            </div>
            <div class="voucher-meta">
                <div><strong>Mẫu số:</strong> 03-TCKT/POSUNG</div>
                <div><strong>Mã phiếu:</strong> <?= h($claim->claim_code) ?></div>
                <div><strong>Ngày lập:</strong> <?= date('d/m/Y', strtotime($claim->submitted_date)) ?></div>
            </div>
        </div>

        <!-- TIÊU ĐỀ BIÊN BẢN -->
        <div class="title-block">
            <h2>BIÊN BẢN THANH QUYẾT TOÁN CHI PHÍ</h2>
            <div class="claim-code"><?= h($claim->title) ?></div>
        </div>

        <!-- THÔNG TIN NGƯỜI ĐỀ NGHỊ -->
        <table class="info-table">
            <tr>
                <td class="label">Họ và tên người đề nghị:</td>
                <td><strong><?= h($claim->employee_name) ?></strong> (Mã NV: <?= h($claim->employee_code) ?>)</td>
                <td class="label">Bộ phận / Phòng ban:</td>
                <td><?= h($claim->dept_name ?? 'N/A') ?></td>
            </tr>
            <tr>
                <td class="label">Chức danh / Vị trí:</td>
                <td><?= h($claim->pos_title ?? 'N/A') ?></td>
                <td class="label">Dự án công trình:</td>
                <td><?= !empty($claim->project_name) ? h($claim->project_name) . ' (' . h($claim->project_code) . ')' : 'Chi phí Văn phòng' ?></td>
            </tr>
            <?php if (!empty($claim->travel_request_code)): ?>
                <tr>
                    <td class="label">Theo chuyến công tác:</td>
                    <td colspan="3">
                        <?= h($claim->travel_request_code) ?> &bull; Lộ trình: <?= h($claim->from_location) ?> &rarr; <?= h($claim->to_location) ?> 
                        (Từ <?= date('d/m/Y', strtotime($claim->departure_date)) ?> đến <?= date('d/m/Y', strtotime($claim->return_date)) ?>)
                    </td>
                </tr>
            <?php endif; ?>
            <tr>
                <td class="label">Tài khoản nhận tiền:</td>
                <td colspan="3">
                    Số TK: <strong><?= h($claim->bank_account_no ?: 'Chưa cập nhật') ?></strong> &bull; 
                    Ngân hàng: <strong><?= h($claim->bank_name ?: '---') ?></strong> 
                    <?= !empty($claim->bank_branch) ? '(' . h($claim->bank_branch) . ')' : '' ?>
                </td>
            </tr>
        </table>

        <!-- BẢNG CHI TIẾT CÁC HẠNG MỤC CHI -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 35px;">STT</th>
                    <th>Nội dung chi phí & Chứng từ kèm theo</th>
                    <th style="width: 100px;">Ngày chi</th>
                    <th style="width: 100px;">Loại chi</th>
                    <th style="width: 120px;">Số tiền (VNĐ)</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($claim->items)): ?>
                    <tr>
                        <td colspan="5" style="text-align: center; font-style: italic;">Không có mục chi tiết.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($claim->items as $idx => $item): ?>
                        <tr>
                            <td style="text-align: center;"><?= $idx + 1 ?></td>
                            <td>
                                <?= h($item->description) ?>
                                <?php if (!empty($item->notes)): ?>
                                    <div style="font-size: 9pt; color: #555;">(<?= h($item->notes) ?>)</div>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center;"><?= date('d/m/Y', strtotime($item->expense_date)) ?></td>
                            <td style="text-align: center;"><?= h($item->category) ?></td>
                            <td style="text-align: right; font-weight: bold;"><?= number_format((float)$item->amount, 0, ',', '.') ?> ₫</td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- TỔNG KẾT TÀI CHÍNH -->
        <div class="summary-box">
            <table style="width: 100%;">
                <tr>
                    <td style="width: 70%;"><strong>1. Tổng chi phí thực tế phát sinh:</strong></td>
                    <td style="text-align: right; font-weight: bold; font-size: 13pt;"><?= number_format((float)$claim->total_amount, 0, ',', '.') ?> VNĐ</td>
                </tr>
                <tr>
                    <td><strong>2. Số tiền đã tạm ứng trước:</strong></td>
                    <td style="text-align: right; color: #555;">- <?= number_format((float)$claim->advance_deducted, 0, ',', '.') ?> VNĐ</td>
                </tr>
                <tr style="border-top: 1px solid #000;">
                    <td style="padding-top: 6px;">
                        <strong>3. <?= ((float)$claim->net_payable >= 0) ? 'Số tiền Công ty thanh toán thêm cho người đề nghị:' : 'Số tiền người đề nghị hoàn trả lại Quỹ:' ?></strong>
                    </td>
                    <td style="text-align: right; font-weight: bold; font-size: 14pt; padding-top: 6px;">
                        <?= number_format(abs((float)$claim->net_payable), 0, ',', '.') ?> VNĐ
                    </td>
                </tr>
            </table>
        </div>

        <!-- CHỮ KÝ CÁC BÊN -->
        <div class="signatures">
            <div class="sig-block">
                <div class="title">Người đề nghị<br><small style="font-weight: normal;">(Ký & ghi rõ họ tên)</small></div>
                <div class="name"><?= h($claim->employee_name) ?></div>
            </div>
            <div class="sig-block">
                <div class="title">Phụ trách Bộ phận / Site PM<br><small style="font-weight: normal;">(Ký & ghi rõ họ tên)</small></div>
                <div class="name">---</div>
            </div>
            <div class="sig-block">
                <div class="title">Kế toán Thanh toán<br><small style="font-weight: normal;">(Ký & ghi rõ họ tên)</small></div>
                <div class="name">---</div>
            </div>
            <div class="sig-block">
                <div class="title">Giám đốc Phê duyệt<br><small style="font-weight: normal;">(Ký & đóng dấu)</small></div>
                <div class="name"><?= h($claim->approver_fullname ?: ($claim->approver_username ?: '---')) ?></div>
            </div>
        </div>
    </div>
</body>
</html>
