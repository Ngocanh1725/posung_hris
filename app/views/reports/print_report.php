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
        <?php elseif ($type === 'retirement'): ?>
            <table>
                <thead><tr><th>STT</th><th>Mã NV</th><th>Họ tên</th><th>Giới tính</th><th>Ngày sinh</th><th>Chức vụ</th><th>Phòng ban</th><th>Ngày vào</th><th>Tuổi HT</th><th>Ngày nghỉ hưu</th></tr></thead>
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
                        <td style="text-align:center;"><?= $r['join_date'] ? date('d/m/Y', strtotime($r['join_date'])) : '' ?></td>
                        <td style="text-align:center;"><?= $r['current_age'] ?></td>
                        <td style="text-align:center; color: red; font-weight: bold;"><?= $r['retirement_date'] ? date('d/m/Y', strtotime($r['retirement_date'])) : '' ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php elseif ($type === 'transfer'): ?>
            <table>
                <thead><tr><th>STT</th><th>Ngày H/L</th><th>Số QĐ</th><th>Mã NV</th><th>Họ tên</th><th>Từ Phòng/Dự án</th><th>Sang Phòng/Dự án</th><th>Lý do</th></tr></thead>
                <tbody>
                    <?php foreach($data as $i => $r): ?>
                    <tr>
                        <td style="text-align:center;"><?= $i+1 ?></td>
                        <td style="text-align:center;"><?= date('d/m/Y', strtotime($r['effective_date'])) ?></td>
                        <td><?= $r['decision_number'] ?></td>
                        <td style="text-align:center;"><?= $r['emp_code'] ?></td>
                        <td><?= $r['full_name'] ?></td>
                        <td><?= $r['from_dept'] ?: $r['from_project'] ?></td>
                        <td><?= $r['to_dept'] ?: $r['to_project'] ?></td>
                        <td><?= h($r['reason']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php elseif ($type === 'attendance'): ?>
            <table>
                <thead><tr><th>STT</th><th>Mã NV</th><th>Họ tên</th><th>Phòng ban</th><th>Dự án</th><th>Ngày làm</th><th>Ca đêm</th><th>Chủ nhật</th><th>Ngày lễ</th><th>Giờ OT</th></tr></thead>
                <tbody>
                    <?php foreach($data as $i => $r): ?>
                    <tr>
                        <td style="text-align:center;"><?= $i+1 ?></td>
                        <td style="text-align:center;"><?= $r['emp_code'] ?></td>
                        <td><?= $r['full_name'] ?></td>
                        <td><?= $r['dept_name'] ?></td>
                        <td><?= $r['project_name'] ?></td>
                        <td style="text-align:center;"><?= $r['total_days'] ?></td>
                        <td style="text-align:center;"><?= $r['night_shifts'] ?></td>
                        <td style="text-align:center;"><?= $r['sunday_shifts'] ?></td>
                        <td style="text-align:center;"><?= $r['holiday_shifts'] ?></td>
                        <td style="text-align:center;"><?= $r['ot_hours'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php elseif ($type === 'payroll'): ?>
            <table>
                <thead><tr><th>STT</th><th>Mã NV</th><th>Họ tên</th><th>Phòng ban/Dự án</th><th>Công TT</th><th>Lương CB</th><th>Phụ cấp</th><th>Lương OT</th><th>Khấu trừ</th><th>Thực lĩnh</th></tr></thead>
                <tbody>
                    <?php $totBase=0; $totAllow=0; $totOT=0; $totDed=0; $totNet=0; foreach($data as $i => $r): 
                        $totBase += $r['base_salary']; $totAllow += $r['allowances_total']; $totOT += $r['ot_pay']; $totDed += $r['deductions_total']; $totNet += $r['net_salary'];
                    ?>
                    <tr>
                        <td style="text-align:center;"><?= $i+1 ?></td>
                        <td style="text-align:center;"><?= $r['emp_code'] ?></td>
                        <td><?= $r['full_name'] ?></td>
                        <td><?= $r['project_name'] ?: $r['dept_name'] ?></td>
                        <td style="text-align:center;"><?= $r['actual_days'] ?></td>
                        <td style="text-align:right;"><?= number_format($r['base_salary'],0,',','.') ?></td>
                        <td style="text-align:right;"><?= number_format($r['allowances_total'],0,',','.') ?></td>
                        <td style="text-align:right;"><?= number_format($r['ot_pay'],0,',','.') ?></td>
                        <td style="text-align:right;"><?= number_format($r['deductions_total'],0,',','.') ?></td>
                        <td style="text-align:right;font-weight:bold;"><?= number_format($r['net_salary'],0,',','.') ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <tr><td colspan="5" style="text-align:right;font-weight:bold;">TỔNG CỘNG</td>
                        <td style="text-align:right;font-weight:bold;"><?= number_format($totBase,0,',','.') ?></td>
                        <td style="text-align:right;font-weight:bold;"><?= number_format($totAllow,0,',','.') ?></td>
                        <td style="text-align:right;font-weight:bold;"><?= number_format($totOT,0,',','.') ?></td>
                        <td style="text-align:right;font-weight:bold;"><?= number_format($totDed,0,',','.') ?></td>
                        <td style="text-align:right;font-weight:bold;"><?= number_format($totNet,0,',','.') ?></td>
                    </tr>
                </tbody>
            </table>
        <?php elseif ($type === 'recruitment'): ?>
            <table>
                <thead><tr><th>STT</th><th>Mã YCTD</th><th>Vị trí</th><th>Phòng ban</th><th>Số lượng cần</th><th>Đã tuyển</th><th>Deadline</th><th>Trạng thái</th></tr></thead>
                <tbody>
                    <?php foreach($data as $i => $r): ?>
                    <tr>
                        <td style="text-align:center;"><?= $i+1 ?></td>
                        <td style="text-align:center;"><?= $r['request_code'] ?></td>
                        <td><?= $r['pos_title'] ?></td>
                        <td><?= $r['dept_name'] ?></td>
                        <td style="text-align:center;"><?= $r['quantity'] ?></td>
                        <td style="text-align:center;"><?= $r['hired_count'] ?></td>
                        <td style="text-align:center;"><?= $r['deadline'] ? date('d/m/Y', strtotime($r['deadline'])) : '' ?></td>
                        <td style="text-align:center;"><?= $r['status'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php elseif ($type === 'retirementDecision'): ?>
            <div style="font-size: 16px; line-height: 1.6; text-align: justify;">
                <p><strong>Căn cứ:</strong></p>
                <ul>
                    <li>Bộ luật Lao động nước Cộng hòa Xã hội Chủ nghĩa Việt Nam;</li>
                    <li>Nội quy lao động và Tình hình thực tế của Công ty;</li>
                    <li>Xét độ tuổi nghỉ hưu của ông/bà <strong><?= $data['full_name'] ?? '' ?></strong> theo quy định của Pháp luật.</li>
                </ul>
                <p style="text-align:center; font-weight:bold; font-size:18px; margin: 20px 0;">GIÁM ĐỐC CÔNG TY QUYẾT ĐỊNH</p>
                <p><strong>Điều 1:</strong> Nay cho ông/bà <strong><?= $data['full_name'] ?? '' ?></strong> (Mã NV: <?= $data['emp_code'] ?? '' ?>) - Chức vụ: <?= $data['pos_title'] ?? '' ?> thuộc <?= $data['dept_name'] ?? '' ?> được nghỉ việc hưởng chế độ hưu trí.</p>
                <p><strong>Điều 2:</strong> Kể từ ngày <strong><?= !empty($data['resignation_date']) ? date('d/m/Y', strtotime($data['resignation_date'])) : '' ?></strong>, ông/bà <?= $data['full_name'] ?? '' ?> được chính thức nghỉ việc.</p>
                <p><strong>Điều 3:</strong> Các phòng ban liên quan và ông/bà <?= $data['full_name'] ?? '' ?> chịu trách nhiệm thi hành quyết định này.</p>
            </div>
        <?php endif; ?>

        <div class="signatures">
            <div class="sig-block"><p class="sig-title">Người lập biểu</p><p style="margin-top:80px;">(Ký, ghi rõ họ tên)</p></div>
            <div class="sig-block"><p class="sig-title">Trưởng phòng HC-NS</p><p style="margin-top:80px;">(Ký, ghi rõ họ tên)</p></div>
            <div class="sig-block"><p class="sig-title">Giám đốc</p><p style="margin-top:80px;">(Ký, đóng dấu)</p></div>
        </div>
    </div>
</body>
</html>
