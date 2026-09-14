<?php
/**
 * ============================================================
 *  View: employee/print_2c.php
 *  Mẫu in Sơ yếu lý lịch 2C/TCTW-98 (Bản mô phỏng kỹ thuật số)
 * ============================================================
 */
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Sơ Yếu Lý Lịch (Mẫu 2C) - <?= htmlspecialchars($employee->full_name) ?></title>
    <style>
        /* Base Styling for Print */
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 13pt;
            line-height: 1.4;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 20px;
        }
        .a4-container {
            width: 210mm;
            min-height: 297mm;
            margin: auto;
            padding: 15mm;
            background: white;
            box-sizing: border-box;
        }
        
        /* Grid & Table */
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .table-bordered th, .table-bordered td { border: 1px solid #000; padding: 5px; }
        
        .header-row { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; }
        .header-left { text-align: center; width: 40%; }
        .header-right { text-align: right; width: 40%; font-size: 11pt; }
        .header-center { text-align: center; width: 100%; margin: 20px 0; }
        
        .title { font-size: 20pt; font-weight: bold; text-transform: uppercase; margin-bottom: 5px; }
        
        .photo-box {
            width: 30mm; height: 40mm; border: 1px solid #000;
            display: flex; align-items: center; justify-content: center;
            font-size: 10pt; color: #666; float: left; margin-right: 15px; margin-bottom: 10px;
            overflow: hidden;
        }
        .photo-box img { width: 100%; height: 100%; object-fit: cover; }
        
        .field { margin-bottom: 6px; }
        .field-label { display: inline-block; font-weight: bold; }
        .field-value { display: inline; border-bottom: 1px dotted #999; }
        
        .section-title { font-weight: bold; text-transform: uppercase; margin-top: 15px; margin-bottom: 10px; }
        
        /* Utilities */
        .text-center { text-align: center; }
        .clear { clear: both; }
        
        /* Print Specific */
        @media print {
            body { padding: 0; background: none; }
            .a4-container { margin: 0; padding: 0; width: 100%; min-height: auto; box-shadow: none; border: none; }
            .no-print { display: none !important; }
            @page { margin: 15mm; size: A4; }
        }
        
        /* Non-print controls */
        .controls { text-align: center; margin-bottom: 20px; background: #f0f0f0; padding: 10px; border-radius: 5px; border: 1px solid #ccc; }
        .btn { padding: 8px 16px; background: #0a1628; color: #fff; text-decoration: none; border: none; cursor: pointer; font-size: 14px; border-radius: 4px; }
    </style>
</head>
<body>

<div class="controls no-print">
    <button class="btn" onclick="window.print()">🖨️ In Mẫu 2C</button>
    <button class="btn" onclick="window.close()" style="background: #666;">Đóng</button>
</div>

<div class="a4-container">
    
    <div class="header-row">
        <div class="header-left">
            <div>CƠ QUAN, ĐƠN VỊ CÓ THẨM QUYỀN QUẢN LÝ CBCC</div>
            <strong>CÔNG TY TNHH CƠ KHÍ KT XD PO SUNG</strong>
            <div style="border-bottom: 1px solid #000; width: 60%; margin: 5px auto;"></div>
        </div>
        <div class="header-right">
            Số hiệu cán bộ: <strong><?= htmlspecialchars($employee->emp_code) ?></strong><br>
            Cơ quan lập lý lịch: Nhân sự
        </div>
    </div>
    
    <div class="header-center">
        <div class="title">SƠ YẾU LÝ LỊCH CÁN BỘ, CÔNG CHỨC</div>
        <em>(Ban hành kèm theo Quyết định số 02/2008/QĐ-BNV)</em>
    </div>
    
    <div>
        <div class="photo-box">
            <?php if (!empty($employee->avatar_path)): ?>
                <img src="<?= BASE_URL ?>/<?= htmlspecialchars($employee->avatar_path) ?>" alt="Ảnh thẻ">
            <?php else: ?>
                Ảnh<br>4 x 6 cm
            <?php endif; ?>
        </div>
        
        <div class="field">
            <span class="field-label">1) Họ và tên khai sinh:</span> 
            <strong style="text-transform: uppercase; font-size: 14pt;"><?= htmlspecialchars($employee->full_name) ?></strong>
        </div>
        <div class="field">
            <span class="field-label">2) Tên gọi khác:</span> <span class="field-value">....................................................................................</span>
        </div>
        <div class="field">
            <span class="field-label">3) Sinh ngày:</span> <span class="field-value"><?= $employee->dob ? date('d', strtotime($employee->dob)) : '.....' ?></span> 
            tháng <span class="field-value"><?= $employee->dob ? date('m', strtotime($employee->dob)) : '.....' ?></span> 
            năm <span class="field-value"><?= $employee->dob ? date('Y', strtotime($employee->dob)) : '........' ?></span>,
            Giới tính (Nam/Nữ): <span class="field-value"><?= $employee->gender === 'Male' ? 'Nam' : ($employee->gender === 'Female' ? 'Nữ' : 'Khác') ?></span>
        </div>
        <div class="field">
            <span class="field-label">4) Nơi sinh:</span> <span class="field-value"><?= htmlspecialchars($employee->hometown ?: '.........................................................') ?></span>
        </div>
        <div class="field">
            <span class="field-label">5) Quê quán:</span> <span class="field-value"><?= htmlspecialchars($employee->hometown ?: '.........................................................') ?></span>
        </div>
        <div class="field">
            <span class="field-label">6) Nơi ở hiện nay:</span> <span class="field-value"><?= htmlspecialchars($employee->address ?: '..........................................................') ?></span>
        </div>
        <div class="field">
            <span class="field-label">7) Nghề nghiệp khi tuyển dụng:</span> <span class="field-value"><?= htmlspecialchars($employee->pos_title ?? '.........................................') ?></span>
        </div>
        <div class="field">
            <span class="field-label">8) Ngày tuyển dụng:</span> <span class="field-value"><?= $employee->join_date ? date('d/m/Y', strtotime($employee->join_date)) : '...................' ?></span>
        </div>
        
        <div class="clear"></div>
    </div>
    
    <div style="margin-top: 15px;">
        <div class="field">
            <span class="field-label">9) Chức vụ (chức danh) hiện tại:</span> <span class="field-value"><?= htmlspecialchars($employee->pos_title ?? '.............................') ?></span>
        </div>
        <div class="field">
            <span class="field-label">10) Công việc chính đang đảm nhận:</span> <span class="field-value"><?= htmlspecialchars($employee->employee_type) ?> tại <?= htmlspecialchars($employee->project_name ?? 'Văn phòng') ?></span>
        </div>
        <div class="field">
            <span class="field-label">11) Trình độ giáo dục phổ thông:</span> <span class="field-value">12/12</span>
        </div>
        <div class="field">
            <span class="field-label">12) Trình độ chuyên môn cao nhất:</span> <span class="field-value"><?= htmlspecialchars($employee->highest_degree ?: '.....................................') ?></span>
        </div>
        <div class="field">
            <span class="field-label">13) Ngày vào Đảng Cộng sản Việt Nam:</span> <span class="field-value"><?= $employee->party_join_date ? date('d/m/Y', strtotime($employee->party_join_date)) : '.............................' ?></span>
        </div>
        <div class="field">
            <span class="field-label">14) Căn cước công dân số:</span> <span class="field-value"><?= htmlspecialchars($employee->id_card ?: '.............................') ?></span>
            Ngày cấp: <span class="field-value"><?= $employee->id_card_date ? date('d/m/Y', strtotime($employee->id_card_date)) : '...............' ?></span>
        </div>
    </div>

    <div class="section-title">15) Tóm tắt quá trình công tác</div>
    <table class="table-bordered">
        <thead>
            <tr>
                <th width="25%">Từ tháng năm - đến tháng năm</th>
                <th width="75%">Chức danh, chức vụ, đơn vị công tác</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($employee->movements)): ?>
                <?php foreach ($employee->movements as $mov): ?>
                <tr>
                    <td class="text-center"><?= date('m/Y', strtotime($mov->effective_date)) ?> - Nay</td>
                    <td>
                        <?= htmlspecialchars($mov->movement_type) ?> đến 
                        <?= htmlspecialchars($mov->to_project ?? $mov->to_dept ?? 'N/A') ?> 
                        (QĐ: <?= htmlspecialchars($mov->decision_number ?: '---') ?>)
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td class="text-center"><?= $employee->join_date ? date('m/Y', strtotime($employee->join_date)) : '..../....' ?> - Nay</td>
                    <td>Làm việc tại Công ty Po Sung (Vị trí: <?= htmlspecialchars($employee->pos_title ?? 'Nhân viên') ?>)</td>
                </tr>
                <tr><td style="height: 30px;"></td><td></td></tr>
                <tr><td style="height: 30px;"></td><td></td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    
    <div style="margin-top: 30px; display: flex; justify-content: space-between;">
        <div style="text-align: center; width: 40%;">
            <strong>Người khai</strong><br>
            <em>(Ký, ghi rõ họ tên)</em><br><br><br><br>
            <?= htmlspecialchars($employee->full_name) ?>
        </div>
        <div style="text-align: center; width: 50%;">
            Ngày ..... tháng ..... năm .......<br>
            <strong>Thủ trưởng cơ quan, đơn vị quản lý và sử dụng CBCC</strong><br>
            <em>(Ký tên, đóng dấu)</em>
        </div>
    </div>

</div>

</body>
</html>
