<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title><?= h($title) ?></title>
    <style>
        @page { size: A4 landscape; margin: 15mm; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Times New Roman', serif; font-size: 13px; line-height: 1.5; color: #000; background: #fff; }
        .container { padding: 20px; }
        
        .header { display: flex; justify-content: space-between; margin-bottom: 20px; }
        .company-name { font-size: 14px; font-weight: bold; text-transform: uppercase; }
        .doc-title { text-align: center; font-size: 18px; font-weight: bold; margin-bottom: 20px; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: left; }
        th { font-weight: bold; background-color: #f0f0f0; text-align: center; }
        
        .signatures { display: flex; justify-content: space-between; margin-top: 40px; }
        .sig-block { text-align: center; width: 30%; }
        .sig-title { font-weight: bold; text-transform: uppercase; }
        
        @media print {
            .no-print { display: none !important; }
        }
        .print-btn { position: fixed; top: 20px; right: 20px; padding: 10px 20px; background: #4f46e5; color: #fff; border: none; cursor: pointer; border-radius: 4px; font-weight: bold; z-index: 1000; }
    </style>
</head>
<body>
    <button class="print-btn no-print" onclick="window.print()">In Báo Cáo</button>
    <div class="container">
        <div class="header">
            <div>
                <p class="company-name">CÔNG TY TNHH CƠ KHÍ<br>KỸ THUẬT XÂY DỰNG PO SUNG</p>
            </div>
            <div style="text-align: right;">
                <p>Ngày in: <?= date('d/m/Y') ?></p>
            </div>
        </div>

        <div class="doc-title"><?= h($title) ?></div>

        <?php if ($type === 'headcount'): ?>
            <table>
                <thead><tr><th>STT</th><th>Mã NV</th><th>Họ tên</th><th>Giới tính</th><th>Ngày sinh</th><th>Chức vụ</th><th>Phòng ban</th><th>Dự án</th><th>Loại nhân sự</th><th>Ngày vào</th></tr></thead>
                <tbody>
                    <?php foreach($data as $i => $r): ?>
                    <tr>
                        <td style="text-align:center;"><?= $i+1 ?></td>
                        <td style="text-align:center;"><?= $r['emp_code'] ?></td>
                        <td><?= $r['full_name'] ?></td>
                        <td style="text-align:center;"><?= $r['gender']=='Male'?'Nam':'Nữ' ?></td>
                        <td style="text-align:center;"><?= $r['dob'] ? date('d/m/Y', strtotime($r['dob'])) : '' ?></td>
                        <td><?= $r['pos_title'] ?></td>
                        <td><?= $r['dept_name'] ?></td>
                        <td><?= $r['project_name'] ?></td>
                        <td style="text-align:center;"><?= $r['employee_type'] ?></td>
                        <td style="text-align:center;"><?= $r['join_date'] ? date('d/m/Y', strtotime($r['join_date'])) : '' ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php elseif ($type === 'reward'): ?>
            <table>
                <thead><tr><th>STT</th><th>Ngày QĐ</th><th>Số QĐ</th><th>Mã NV</th><th>Họ tên</th><th>Phòng ban</th><th>Hình thức</th><th>Nội dung</th><th>Số tiền</th></tr></thead>
                <tbody>
                    <?php $total = 0; foreach($data as $i => $r): $total += $r['amount']; ?>
                    <tr>
                        <td style="text-align:center;"><?= $i+1 ?></td>
                        <td style="text-align:center;"><?= date('d/m/Y', strtotime($r['decision_date'])) ?></td>
                        <td><?= $r['decision_number'] ?></td>
                        <td style="text-align:center;"><?= $r['emp_code'] ?></td>
                        <td><?= $r['full_name'] ?></td>
                        <td><?= $r['dept_name'] ?></td>
                        <td><?= $r['reward_form'] ?></td>
                        <td><?= h($r['title']) ?></td>
                        <td style="text-align:right;"><?= number_format($r['amount'],0,',','.') ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <tr><td colspan="8" style="text-align:right;font-weight:bold;">TỔNG CỘNG</td><td style="text-align:right;font-weight:bold;"><?= number_format($total,0,',','.') ?></td></tr>
                </tbody>
            </table>
        <?php elseif ($type === 'discipline'): ?>
            <table>
                <thead><tr><th>STT</th><th>Ngày QĐ</th><th>Số QĐ</th><th>Mã NV</th><th>Họ tên</th><th>Phòng ban</th><th>Hình thức</th><th>Nội dung</th><th>Vi phạm HSE</th></tr></thead>
                <tbody>
                    <?php foreach($data as $i => $r): ?>
                    <tr>
                        <td style="text-align:center;"><?= $i+1 ?></td>
                        <td style="text-align:center;"><?= date('d/m/Y', strtotime($r['decision_date'])) ?></td>
                        <td><?= $r['decision_number'] ?></td>
                        <td style="text-align:center;"><?= $r['emp_code'] ?></td>
                        <td><?= $r['full_name'] ?></td>
                        <td><?= $r['dept_name'] ?></td>
                        <td><?= $r['discipline_form'] ?></td>
                        <td><?= h($r['title']) ?></td>
                        <td style="text-align:center;"><?= $r['is_safety_violation'] ? 'X' : '' ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <div class="signatures">
            <div class="sig-block"><p class="sig-title">Người lập biểu</p><p style="margin-top:80px;">(Ký, ghi rõ họ tên)</p></div>
            <div class="sig-block"><p class="sig-title">Trưởng phòng HC-NS</p><p style="margin-top:80px;">(Ký, ghi rõ họ tên)</p></div>
            <div class="sig-block"><p class="sig-title">Giám đốc</p><p style="margin-top:80px;">(Ký, đóng dấu)</p></div>
        </div>
    </div>
</body>
</html>
