<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quyết định <?= $record->type === 'Reward' ? 'Khen thưởng' : 'Kỷ luật' ?> – <?= h($record->full_name) ?></title>
    <style>
        @page { size: A4; margin: 20mm 25mm; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Times New Roman', serif; font-size: 14px; line-height: 1.8; color: #000; background: #fff; }
        .print-container { max-width: 700px; margin: 0 auto; padding: 20px; }
        
        /* Header */
        .doc-header { display: flex; justify-content: space-between; margin-bottom: 20px; }
        .doc-header-left { text-align: center; width: 45%; }
        .doc-header-right { text-align: center; width: 45%; }
        .company-name { font-size: 13px; font-weight: bold; text-transform: uppercase; }
        .doc-title { font-size: 16px; font-weight: bold; text-transform: uppercase; color: #000; margin: 20px 0 5px; }
        .doc-subtitle { font-size: 13px; font-style: italic; }
        .doc-number { font-size: 13px; margin-bottom: 5px; }
        .doc-hr { border: none; border-bottom: 1px solid #000; width: 60px; margin: 5px auto; }
        
        /* Body */
        .doc-basis { margin: 15px 0; }
        .doc-basis p { text-indent: 40px; margin-bottom: 5px; }
        .doc-content { margin: 15px 0; }
        .doc-content p { text-indent: 40px; margin-bottom: 8px; }
        .article { margin-bottom: 12px; }
        .article-title { font-weight: bold; text-indent: 40px; }
        .article-content { text-indent: 40px; }
        
        /* Employee Info */
        .emp-info { margin: 10px 0; padding: 10px 20px; }
        .emp-info p { margin-bottom: 3px; text-indent: 40px; }
        
        /* Signatures */
        .signatures { display: flex; justify-content: space-between; margin-top: 40px; }
        .sig-block { text-align: center; width: 45%; }
        .sig-title { font-weight: bold; text-transform: uppercase; font-size: 13px; }
        .sig-name { font-weight: bold; margin-top: 60px; }
        .sig-note { font-style: italic; font-size: 12px; }
        
        /* Print styles */
        @media print {
            @page { size: A4; margin: 15mm; }
            body { background: transparent; padding: 0; margin: 0; box-shadow: none; max-width: none; border: none; }
            .no-print { display: none !important; }
            .print-container { max-width: none; padding: 0; border: none; }
        }
        
        /* Print button */
        .print-btn { position: fixed; top: 20px; right: 20px; padding: 10px 24px; background: #4f46e5; color: #fff; border: none; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: 600; z-index: 999; box-shadow: 0 4px 12px rgba(79,70,229,0.3); }
        .print-btn:hover { background: #4338ca; }

        /* Badge for type */
        .type-reward { color: #059669; }
        .type-discipline { color: #dc2626; }
    </style>
</head>
<body>

<button class="print-btn no-print" onclick="window.print()">
    <i class="fas fa-print"></i> In Quyết định
</button>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

<div class="print-container">
    <!-- ═══════════ HEADER ═══════════ -->
    <div class="doc-header">
        <div class="doc-header-left">
            <p class="company-name">CÔNG TY TNHH CƠ KHÍ<br>KỸ THUẬT XÂY DỰNG PO SUNG</p>
            <hr class="doc-hr">
            <p class="doc-number">
                Số: <?= h($record->decision_number ?? '......./QĐ-PS') ?>
            </p>
        </div>
        <div class="doc-header-right">
            <p style="font-size:13px; font-weight:bold;">CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM</p>
            <p style="font-size:13px; font-weight:bold;">Độc lập – Tự do – Hạnh phúc</p>
            <hr class="doc-hr">
            <p style="font-style:italic; font-size:13px;">
                Hà Nội, ngày <?= $record->decision_date ? date('d', strtotime($record->decision_date)) : '...' ?> 
                tháng <?= $record->decision_date ? date('m', strtotime($record->decision_date)) : '...' ?> 
                năm <?= $record->decision_date ? date('Y', strtotime($record->decision_date)) : '...' ?>
            </p>
        </div>
    </div>

    <!-- ═══════════ TITLE ═══════════ -->
    <div style="text-align:center; margin:20px 0;">
        <p class="doc-title">
            QUYẾT ĐỊNH
        </p>
        <p class="doc-subtitle">
            <?php if ($record->type === 'Reward'): ?>
                Về việc Khen thưởng cá nhân
            <?php else: ?>
                Về việc Kỷ luật lao động
            <?php endif; ?>
        </p>
        <hr class="doc-hr" style="margin-top:10px;">
    </div>

    <!-- ═══════════ CĂN CỨ ═══════════ -->
    <div class="doc-basis">
        <p style="font-weight:bold; text-align:center; margin-bottom:10px;">
            <?= strtoupper(h($record->authority_level ?? 'GIÁM ĐỐC CÔNG TY')) ?>
        </p>
        <p>Căn cứ Bộ luật Lao động số 45/2019/QH14 ngày 20/11/2019;</p>
        <p>Căn cứ Nội quy lao động và Quy chế <?= $record->type === 'Reward' ? 'Thi đua – Khen thưởng' : 'Xử lý kỷ luật lao động' ?> của Công ty TNHH Cơ khí Kỹ thuật Xây dựng Po Sung;</p>
        <?php if ($record->type === 'Discipline'): ?>
            <p>Căn cứ Biên bản họp xử lý kỷ luật lao động ngày <?= $record->decision_date ? date('d/m/Y', strtotime($record->decision_date)) : '.../.../...' ?>;</p>
        <?php endif; ?>
        <p>Xét đề nghị của <?= h($record->proposed_by ?? 'Phòng Hành chính – Nhân sự') ?>;</p>
    </div>

    <!-- ═══════════ NỘI DUNG ═══════════ -->
    <div style="text-align:center; font-weight:bold; margin:15px 0;">QUYẾT ĐỊNH:</div>

    <div class="doc-content">
        <!-- Điều 1 -->
        <div class="article">
            <p class="article-title">Điều 1.</p>
            <?php if ($record->type === 'Reward'): ?>
                <p class="article-content">
                    Tặng <?= h($record->reward_form ?? 'Khen thưởng') ?> cho:
                </p>
            <?php else: ?>
                <p class="article-content">
                    Áp dụng hình thức kỷ luật <strong>"<?= h($record->discipline_form ?? 'Kỷ luật') ?>"</strong> đối với:
                </p>
            <?php endif; ?>
            
            <div class="emp-info">
                <p>- Ông/Bà: <strong><?= h($record->full_name) ?></strong></p>
                <p>- Mã nhân viên: <strong><?= h($record->emp_code) ?></strong></p>
                <p>- Chức vụ: <?= h($record->pos_title ?? 'N/A') ?></p>
                <p>- Phòng ban / Đơn vị: <?= h($record->dept_name ?? 'N/A') ?></p>
                <?php if (!empty($record->project_name)): ?>
                    <p>- Dự án: <?= h($record->project_name) ?></p>
                <?php endif; ?>
                <p>- Số CCCD: <?= h($record->id_card ?? '...') ?></p>
                <p>- Ngày vào Công ty: <?= $record->join_date ? date('d/m/Y', strtotime($record->join_date)) : '...' ?></p>
            </div>
        </div>

        <!-- Điều 2 -->
        <div class="article">
            <p class="article-title">Điều 2.</p>
            <?php if ($record->type === 'Reward'): ?>
                <p class="article-content">
                    Lý do khen thưởng: <strong><?= h($record->title) ?></strong>
                </p>
                <?php if (!empty($record->reason)): ?>
                    <p class="article-content"><?= nl2br(h($record->reason)) ?></p>
                <?php endif; ?>
                <?php if ((float)$record->amount > 0): ?>
                    <p class="article-content">
                        Mức thưởng: <strong><?= number_format($record->amount, 0, ',', '.') ?> VNĐ</strong>
                        (Bằng chữ: <?= h($record->amount) ?> đồng).
                    </p>
                <?php endif; ?>
            <?php else: ?>
                <p class="article-content">
                    Lý do kỷ luật: <strong><?= h($record->title) ?></strong>
                </p>
                <?php if (!empty($record->reason)): ?>
                    <p class="article-content"><?= nl2br(h($record->reason)) ?></p>
                <?php endif; ?>
                <?php if ($record->is_safety_violation): ?>
                    <p class="article-content" style="color:#dc2626; font-weight:bold;">
                        ⚠ Đây là vi phạm quy định An toàn lao động (HSE). Nhân viên bị đưa vào Danh sách đen (Blacklist).
                    </p>
                <?php endif; ?>
                <?php if ((float)$record->amount > 0): ?>
                    <p class="article-content">
                        Mức phạt: <strong><?= number_format($record->amount, 0, ',', '.') ?> VNĐ</strong>.
                    </p>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- Điều 3 -->
        <div class="article">
            <p class="article-title">Điều 3.</p>
            <p class="article-content">
                Quyết định này có hiệu lực kể từ ngày ký. Phòng Hành chính – Nhân sự, Phòng Kế toán – Tài chính, 
                và Ông/Bà <?= h($record->full_name) ?> chịu trách nhiệm thi hành Quyết định này.
            </p>
        </div>
    </div>

    <!-- ═══════════ NƠI NHẬN ═══════════ -->
    <div style="margin-top:20px;">
        <p style="font-size:12px;"><strong>Nơi nhận:</strong></p>
        <ul style="font-size:12px; list-style:none; padding-left:20px;">
            <li>- Như Điều 3;</li>
            <li>- Lưu: VP, HC-NS.</li>
        </ul>
    </div>

    <!-- ═══════════ CHỮ KÝ ═══════════ -->
    <div class="signatures">
        <div class="sig-block">
            &nbsp;
        </div>
        <div class="sig-block">
            <p class="sig-title"><?= strtoupper(h($record->authority_level ?? 'GIÁM ĐỐC')) ?></p>
            <p class="sig-note">(Ký, ghi rõ họ tên, đóng dấu)</p>
            <p class="sig-name">
                <?= h($record->approver_name ?? '..........................') ?>
            </p>
        </div>
    </div>
</div>

</body>
</html>
