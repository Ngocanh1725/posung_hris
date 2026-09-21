<?php
/**
 * ============================================================
 *  View: employee/print_profile.php
 *  BẢN KHAI HỒ SƠ NHÂN SỰ DOANH NGHIỆP (Khổ A4)
 *  Công ty TNHH Cơ khí Kỹ thuật Xây dựng Po Sung
 * ============================================================
 */

// Helper
function pd(?string $d, string $fmt = 'd/m/Y'): string {
    return $d ? date($fmt, strtotime($d)) : '...............';
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Hồ sơ Nhân sự Doanh nghiệp - <?= h($employee->full_name) ?></title>
    <style>
        /* ═══════ Base ═══════ */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 13pt;
            line-height: 1.5;
            color: #000;
            background: #e0e0e0;
        }
        .a4-page {
            width: 210mm;
            min-height: 297mm;
            margin: 10mm auto;
            padding: 15mm 20mm;
            background: #fff;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }

        /* ═══════ Header ═══════ */
        .doc-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
        }
        .doc-header-left {
            text-align: center;
            width: 45%;
            font-size: 11pt;
        }
        .doc-header-right {
            text-align: center;
            width: 50%;
            font-size: 11pt;
        }
        .company-name {
            font-weight: bold;
            font-size: 12pt;
            text-transform: uppercase;
        }
        .doc-title {
            text-align: center;
            margin: 15px 0;
        }
        .doc-title h1 {
            font-size: 16pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .doc-title .subtitle {
            font-size: 11pt;
            font-style: italic;
            margin-top: 5px;
        }
        .hr-line {
            border: none;
            border-top: 1px solid #000;
            width: 40%;
            margin: 5px auto;
        }

        /* ═══════ Photo & Basic Info ═══════ */
        .basic-info { display: flex; gap: 15px; margin-bottom: 15px; }
        .photo-box {
            width: 30mm;
            height: 40mm;
            border: 1px solid #000;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 9pt;
            color: #666;
            overflow: hidden;
        }
        .photo-box img { width: 100%; height: 100%; object-fit: cover; }
        .info-grid { flex: 1; }

        /* ═══════ Fields ═══════ */
        .field {
            margin-bottom: 4px;
            display: flex;
            flex-wrap: wrap;
        }
        .field-label {
            font-weight: bold;
            min-width: 200px;
        }
        .field-value {
            flex: 1;
            border-bottom: 1px dotted #999;
            padding-left: 5px;
        }
        .field-inline {
            display: inline;
            margin-right: 20px;
        }

        /* ═══════ Section ═══════ */
        .section-header {
            font-weight: bold;
            font-size: 12pt;
            text-transform: uppercase;
            margin: 15px 0 8px;
            padding-bottom: 3px;
            border-bottom: 1.5px solid #000;
        }
        .section-sub {
            font-weight: bold;
            font-size: 11pt;
            margin: 10px 0 5px;
            font-style: italic;
        }

        /* ═══════ Tables ═══════ */
        table.bordered {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 11pt;
        }
        table.bordered th,
        table.bordered td {
            border: 1px solid #000;
            padding: 4px 6px;
            vertical-align: top;
        }
        table.bordered th {
            background: #f5f5f5;
            font-weight: bold;
            text-align: center;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .nowrap { white-space: nowrap; }

        /* ═══════ Signatures ═══════ */
        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
            page-break-inside: avoid;
        }
        .sig-block {
            text-align: center;
            width: 30%;
        }
        .sig-block .sig-title {
            font-weight: bold;
            font-size: 11pt;
        }
        .sig-block .sig-note {
            font-style: italic;
            font-size: 10pt;
            margin-bottom: 60px;
        }
        .sig-block .sig-name {
            font-weight: bold;
        }

        /* ═══════ Print Controls ═══════ */
        .controls {
            text-align: center;
            margin: 10px auto;
            padding: 12px;
            background: #333;
            border-radius: 8px;
            width: 210mm;
        }
        .controls button {
            padding: 10px 24px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            margin: 0 5px;
            color: #fff;
        }
        .btn-print { background: #2563eb; }
        .btn-close { background: #666; }

        /* ═══════ Print Media ═══════ */
        @media print {
            body { background: none; }
            .a4-page {
                margin: 0;
                padding: 10mm 15mm;
                box-shadow: none;
                width: 100%;
                min-height: auto;
            }
            .controls { display: none !important; }
            @page {
                size: A4;
                margin: 10mm;
            }
            .page-break { page-break-before: always; }
        }

        /* ═══════ Skill badges ═══════ */
        .skill-tag {
            display: inline-block;
            padding: 2px 8px;
            border: 1px solid #333;
            border-radius: 3px;
            font-size: 10pt;
            margin: 2px;
        }
        .skill-tag.active { background: #333; color: #fff; }
    </style>
</head>
<body>

<div class="controls">
    <button class="btn-print" onclick="window.print()">🖨️ In Hồ sơ Doanh nghiệp</button>
    <button class="btn-close" onclick="window.close()">✕ Đóng</button>
</div>

<!-- ═══════ TRANG 1 ═══════ -->
<div class="a4-page">

    <!-- Header -->
    <div class="doc-header">
        <div class="doc-header-left">
            <div>CÔNG TY TNHH CƠ KHÍ</div>
            <div class="company-name">KỸ THUẬT XÂY DỰNG PO SUNG</div>
            <hr class="hr-line">
        </div>
        <div class="doc-header-right">
            <div>CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM</div>
            <div style="font-weight:bold;">Độc lập – Tự do – Hạnh phúc</div>
            <hr class="hr-line">
            <div style="margin-top:5px;">Mã nhân viên: <strong><?= h($employee->emp_code) ?></strong></div>
        </div>
    </div>

    <!-- Title -->
    <div class="doc-title">
        <h1>BẢN KHAI HỒ SƠ NHÂN SỰ<br>DOANH NGHIỆP</h1>
        <div class="subtitle">(Po Sung MEC Co., Ltd – Human Resources Profile)</div>
    </div>

    <!-- Photo + Basic -->
    <div class="basic-info">
        <div class="photo-box">
            <?php if (!empty($employee->avatar_path)): ?>
                <img src="<?= BASE_URL ?>/<?= h($employee->avatar_path) ?>" alt="Ảnh">
            <?php else: ?>
                Ảnh<br>4×6
            <?php endif; ?>
        </div>
        <div class="info-grid">
            <div class="field">
                <span class="field-label">1. Họ và tên:</span>
                <span class="field-value" style="font-weight:bold; font-size:14pt; text-transform:uppercase;"><?= h($employee->full_name) ?></span>
            </div>
            <div class="field">
                <span class="field-label">2. Ngày sinh:</span>
                <span class="field-value"><?= pd($employee->dob) ?></span>
                <span style="margin-left:20px;"><strong>Giới tính:</strong> <?= $employee->gender === 'Male' ? 'Nam' : ($employee->gender === 'Female' ? 'Nữ' : 'Khác') ?></span>
            </div>
            <div class="field">
                <span class="field-label">3. Quê quán:</span>
                <span class="field-value"><?= h($employee->hometown ?: '...') ?></span>
            </div>
            <div class="field">
                <span class="field-label">4. Nơi ở hiện nay:</span>
                <span class="field-value"><?= h($employee->address ?: '...') ?></span>
            </div>
            <div class="field">
                <span class="field-label">5. Số CCCD / Hộ chiếu:</span>
                <span class="field-value"><?= h($employee->id_card ?: '...') ?> – Cấp ngày: <?= pd($employee->id_card_date) ?> tại <?= h($employee->id_card_place ?: '...') ?></span>
            </div>
            <div class="field">
                <span class="field-label">6. Điện thoại:</span>
                <span class="field-value"><?= h($employee->phone ?: '...') ?></span>
                <span style="margin-left:20px;"><strong>Email:</strong> <?= h($employee->email ?: '...') ?></span>
            </div>
        </div>
    </div>

    <!-- Organization info -->
    <div class="section-header">I. Thông tin Tổ chức</div>
    <div class="field"><span class="field-label">7. Phòng ban / Bộ phận:</span><span class="field-value"><?= h($employee->dept_name ?? '...') ?></span></div>
    <div class="field"><span class="field-label">8. Chức vụ hiện tại:</span><span class="field-value"><?= h($employee->pos_title ?? '...') ?></span></div>
    <div class="field"><span class="field-label">9. Dự án đang tham gia:</span><span class="field-value"><?= h($employee->project_name ?? '...') ?></span></div>
    <div class="field"><span class="field-label">10. Ngày vào công ty:</span><span class="field-value"><?= pd($employee->join_date) ?></span></div>
    <div class="field"><span class="field-label">11. Loại nhân sự:</span><span class="field-value"><?= h($employee->employee_type) ?></span></div>
    <div class="field"><span class="field-label">12. Trình độ chuyên môn:</span><span class="field-value"><?= h($employee->highest_degree ?: '...') ?></span></div>

    <!-- M&E Skills -->
    <div class="section-header">II. Năng lực Kỹ thuật M&E</div>
    <div class="field"><span class="field-label">13. Phần mềm chuyên môn:</span></div>
    <div style="margin-left:20px; margin-bottom:5px;">
        AutoCAD: <strong><?= h($employee->skill_autocad ?? '---') ?></strong> &nbsp;|&nbsp;
        Revit BIM: <strong><?= h($employee->skill_revit_bim ?? '---') ?></strong> &nbsp;|&nbsp;
        Navisworks: <strong><?= h($employee->skill_navisworks ?? '---') ?></strong> &nbsp;|&nbsp;
        Dự toán: <strong><?= h($employee->skill_estimation ?? '---') ?></strong>
    </div>
    <div class="field"><span class="field-label">14. Chứng chỉ thợ hàn:</span></div>
    <div style="margin-left:20px; margin-bottom:5px;">
        <span class="skill-tag <?= ($employee->welding_cert_3g ?? 0) ? 'active' : '' ?>">3G</span>
        <span class="skill-tag <?= ($employee->welding_cert_6g ?? 0) ? 'active' : '' ?>">6G</span>
        <span class="skill-tag <?= ($employee->welding_cert_tig ?? 0) ? 'active' : '' ?>">TIG</span>
        <span class="skill-tag <?= ($employee->welding_cert_mig ?? 0) ? 'active' : '' ?>">MIG</span>
    </div>
    <div class="field"><span class="field-label">15. Ngoại ngữ:</span>
        <span class="field-value">Tiếng Hàn: <?= h($employee->korean_level ?? '---') ?> | Tiếng Anh: <?= h($employee->english_level ?? '---') ?></span>
    </div>
    <div class="field"><span class="field-label">16. Tin học:</span><span class="field-value"><?= h($employee->it_level ?? '---') ?></span></div>

    <!-- HSE -->
    <div class="section-header">III. An toàn Lao động (HSE)</div>
    <div class="field"><span class="field-label">17. Số thẻ an toàn LĐ:</span><span class="field-value"><?= h($employee->hse_card_number ?? '...') ?></span></div>
    <div class="field"><span class="field-label">18. Ngày cấp thẻ ATLĐ:</span><span class="field-value"><?= pd($employee->hse_card_issue_date ?? null) ?></span></div>
    <div class="field"><span class="field-label">19. Hạn thẻ ATLĐ Samsung:</span><span class="field-value"><?= pd($employee->hse_card_expiry_samsung ?? null) ?></span></div>
    <div class="field"><span class="field-label">20. Hạn thẻ ATLĐ Amkor:</span><span class="field-value"><?= pd($employee->hse_card_expiry_amkor ?? null) ?></span></div>
    <div class="field"><span class="field-label">21. ĐK làm việc trên cao:</span><span class="field-value"><?= ($employee->can_work_at_height ?? 0) ? '☑ Đạt' : '☐ Chưa' ?></span>
        <span style="margin-left:20px;"><strong>Hầm kín:</strong> <?= ($employee->can_work_confined_space ?? 0) ? '☑ Đạt' : '☐ Chưa' ?></span>
    </div>

    <!-- Quá trình Công tác Dự án -->
    <div class="section-header">IV. Tóm tắt Quá trình Công tác Dự án</div>
    <table class="bordered">
        <thead>
            <tr><th width="15%">Từ tháng/năm</th><th width="15%">Đến tháng/năm</th><th width="35%">Tổ chức / Công ty</th><th width="20%">Chức vụ</th><th width="15%">Dự án</th></tr>
        </thead>
        <tbody>
            <?php if (!empty($processes['work_histories'])): ?>
                <?php foreach ($processes['work_histories'] as $wh): $wh = (object)$wh; ?>
                <tr>
                    <td class="text-center"><?= pd($wh->from_date ?? null, 'm/Y') ?></td>
                    <td class="text-center"><?= $wh->to_date ? pd($wh->to_date, 'm/Y') : 'Nay' ?></td>
                    <td><?= h($wh->organization) ?></td>
                    <td><?= h($wh->position ?? '') ?></td>
                    <td><?= h($wh->project_name ?? '') ?></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="5" style="height:25px;"></td></tr>
                <tr><td colspan="5" style="height:25px;"></td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Quá trình Đào tạo -->
    <div class="section-header">V. Quá trình Đào tạo</div>
    <table class="bordered">
        <thead>
            <tr><th width="15%">Từ</th><th width="15%">Đến</th><th width="30%">Trường / Cơ sở</th><th width="20%">Chuyên ngành</th><th width="20%">Văn bằng</th></tr>
        </thead>
        <tbody>
            <?php if (!empty($processes['trainings'])): ?>
                <?php foreach ($processes['trainings'] as $tr): $tr = (object)$tr; ?>
                <tr>
                    <td class="text-center"><?= pd($tr->from_date ?? null, 'm/Y') ?></td>
                    <td class="text-center"><?= $tr->to_date ? pd($tr->to_date, 'm/Y') : 'Nay' ?></td>
                    <td><?= h($tr->institution) ?></td>
                    <td><?= h($tr->major ?? '') ?></td>
                    <td><?= h($tr->certificate ?? '') ?></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="5" style="height:25px;"></td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- ═══════ TRANG 2 ═══════ -->
<div class="a4-page page-break">

    <!-- Diễn biến Lương -->
    <div class="section-header">VI. Diễn biến Lương</div>
    <table class="bordered">
        <thead>
            <tr><th width="20%">Ngày áp dụng</th><th width="20%">Ngạch / Bậc</th><th width="15%">Hệ số</th><th width="25%">Mức lương CB</th><th width="20%">Số QĐ</th></tr>
        </thead>
        <tbody>
            <?php if (!empty($processes['salary_progressions'])): ?>
                <?php foreach ($processes['salary_progressions'] as $sp): $sp = (object)$sp; ?>
                <tr>
                    <td class="text-center"><?= pd($sp->effective_date) ?></td>
                    <td><?= h($sp->salary_grade ?? '') ?></td>
                    <td class="text-center"><?= h($sp->salary_coefficient ?? '') ?></td>
                    <td class="text-right"><?= number_format($sp->base_salary ?? 0) ?></td>
                    <td><?= h($sp->decision_number ?? '') ?></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="5" style="height:25px;"></td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Khen thưởng / Kỷ luật -->
    <div class="section-header">VII. Khen thưởng – Kỷ luật</div>
    <table class="bordered">
        <thead>
            <tr><th width="10%">Loại</th><th width="15%">Số QĐ</th><th width="15%">Ngày QĐ</th><th width="30%">Hình thức</th><th width="30%">Lý do</th></tr>
        </thead>
        <tbody>
            <?php if (!empty($processes['reward_disciplines'])): ?>
                <?php foreach ($processes['reward_disciplines'] as $rd): $rd = (object)$rd; ?>
                <tr>
                    <td class="text-center"><?= $rd->type === 'Reward' ? 'KT' : 'KL' ?></td>
                    <td><?= h($rd->decision_number ?? '') ?></td>
                    <td class="text-center"><?= pd($rd->decision_date ?? null) ?></td>
                    <td><?= h($rd->title) ?></td>
                    <td><?= h($rd->reason ?? '') ?></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="5" style="height:25px;"></td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Quan hệ Gia đình -->
    <div class="section-header">VIII. Quan hệ Gia đình</div>
    <table class="bordered">
        <thead>
            <tr><th width="25%">Họ tên</th><th width="12%">Quan hệ</th><th width="12%">Năm sinh</th><th width="25%">Nghề nghiệp</th><th width="26%">Nơi ở</th></tr>
        </thead>
        <tbody>
            <?php if (!empty($processes['family_members'])): ?>
                <?php foreach ($processes['family_members'] as $fm): $fm = (object)$fm; ?>
                <tr>
                    <td><?= h($fm->full_name) ?></td>
                    <td class="text-center"><?= h($fm->relationship) ?></td>
                    <td class="text-center"><?= pd($fm->dob ?? null, 'Y') ?></td>
                    <td><?= h($fm->occupation ?? '') ?></td>
                    <td><?= h($fm->address ?? '') ?></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="5" style="height:25px;"></td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Cam kết -->
    <div style="margin-top: 20px; font-size: 12pt; line-height: 1.8;">
        <p>Tôi xin cam đoan những lời khai trên là đúng sự thật. Nếu có gì sai sót, tôi xin hoàn toàn chịu trách nhiệm trước Pháp luật và Công ty.</p>
    </div>

    <!-- Signatures -->
    <div class="signatures">
        <div class="sig-block">
            <div class="sig-title">NGƯỜI KHAI</div>
            <div class="sig-note">(Ký, ghi rõ họ tên)</div>
            <div class="sig-name"><?= h($employee->full_name) ?></div>
        </div>
        <div class="sig-block">
            <div class="sig-title">TRƯỞNG BAN QLDA /<br>TRƯỞNG PHÒNG</div>
            <div class="sig-note">(Ký, ghi rõ họ tên)</div>
            <div class="sig-name">&nbsp;</div>
        </div>
        <div class="sig-block">
            <div style="margin-bottom:5px; font-size:10pt;">Ngày ...... tháng ...... năm ..........</div>
            <div class="sig-title">TỔNG GIÁM ĐỐC</div>
            <div class="sig-note">(Ký tên, đóng dấu)</div>
            <div class="sig-name">&nbsp;</div>
        </div>
    </div>

</div>

</body>
</html>
