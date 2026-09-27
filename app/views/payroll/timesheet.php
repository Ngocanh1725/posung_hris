<?php
/**
 * ============================================================
 *  View: payroll/timesheet.php
 *  Drill-down Timesheet: Company -> Department -> Employee Detail
 * ============================================================
 */

$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

// Calculate statistics
$departments = [];
$employees_in_dept = [];
$employee_detail = null;

foreach ($grid as $eId => $row) {
    $dId = $row['employee']['department_id'] ?? 0;
    $dName = $row['employee']['dept_name'] ?? 'Chưa phân bổ';
    
    // Initialize department if not exists
    if (!isset($departments[$dId])) {
        $departments[$dId] = [
            'id' => $dId,
            'name' => $dName,
            'total_employees' => 0,
            'total_working_days' => 0,
            'actual_working_days' => 0,
        ];
    }
    
    // Count employee's working days
    $empWorkDays = 0;
    $empActualDays = 0;
    $dayShifts = 0;
    $nightShifts = 0;
    $otHours = 0;

    for ($d=1; $d<=$daysInMonth; $d++) {
        $dateStr = "$year-$month-" . str_pad($d, 2, '0', STR_PAD_LEFT);
        $isSunday = date('w', strtotime($dateStr)) == 0;
        
        // Count total required working days (excluding Sundays)
        if (!$isSunday) { 
            $empWorkDays++;
        }
        
        $sym = $row['days'][$d] ?? '';
        if ($sym === 'X') { $empActualDays++; $dayShifts++; }
        if ($sym === 'Đ') { $empActualDays++; $nightShifts++; }
        if ($sym === 'OT') { $empActualDays++; $otHours += 2.5; }
    }
    
    $departments[$dId]['total_employees']++;
    $departments[$dId]['total_working_days'] += $empWorkDays;
    $departments[$dId]['actual_working_days'] += $empActualDays;
    
    if ($dept_id && $dept_id == $dId) {
        $employees_in_dept[$eId] = [
            'employee' => $row['employee'],
            'attendance_rate' => $empWorkDays > 0 ? round(($empActualDays / $empWorkDays) * 100, 1) : 0,
            'day_shifts' => $dayShifts,
            'night_shifts' => $nightShifts,
            'ot_hours' => $otHours,
        ];
    }
    
    if ($emp_id && $emp_id == $eId) {
        $employee_detail = $row;
    }
}
?>

<div class="panel mb-4" style="border-radius:12px; box-shadow:0 10px 30px rgba(0,0,0,0.05); overflow:hidden; border:none;">
    <div class="panel-header d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, var(--primary), var(--accent)); color: white; padding: 20px;">
        <div>
            <h3 class="mb-1" style="color:white; font-weight:700;"><i class="far fa-calendar-check"></i> Tổng hợp Chấm Công - <?= $month ?>/<?= $year ?></h3>
            <p class="mb-0 text-white-50">
                <?php if ($emp_id): ?>
                    <a href="<?= BASE_URL ?>/payroll/timesheet?month=<?= $month ?>&year=<?= $year ?>&dept_id=<?= $employee_detail['employee']['department_id'] ?? 0 ?>" style="color:white; text-decoration:underline;">Quay lại Phòng ban</a>
                <?php elseif ($dept_id): ?>
                    <a href="<?= BASE_URL ?>/payroll/timesheet?month=<?= $month ?>&year=<?= $year ?>" style="color:white; text-decoration:underline;">Quay lại Toàn công ty</a>
                <?php else: ?>
                    Theo dõi chấm công toàn công ty
                <?php endif; ?>
            </p>
        </div>
        
        <!-- Filter Form -->
        <div class="d-flex align-items-center bg-white p-2" style="border-radius:8px;">
            <form action="<?= BASE_URL ?>/payroll/timesheet" method="GET" class="d-flex align-items-center m-0">
                <select name="month" class="form-control form-control-sm mr-2" style="border:none; background:#f1f5f9; font-weight:bold;">
                    <?php for($m=1; $m<=12; $m++): ?>
                        <option value="<?= $m ?>" <?= $m == $month ? 'selected' : '' ?>>Tháng <?= $m ?></option>
                    <?php endfor; ?>
                </select>
                <select name="year" class="form-control form-control-sm mr-2" style="border:none; background:#f1f5f9; font-weight:bold;">
                    <?php for($y=2024; $y<=date('Y'); $y++): ?>
                        <option value="<?= $y ?>" <?= $y == $year ? 'selected' : '' ?>>Năm <?= $y ?></option>
                    <?php endfor; ?>
                </select>
                <?php if ($dept_id): ?><input type="hidden" name="dept_id" value="<?= $dept_id ?>"><?php endif; ?>
                <?php if ($emp_id): ?><input type="hidden" name="emp_id" value="<?= $emp_id ?>"><?php endif; ?>
                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter"></i> Lọc</button>
            </form>
        </div>
    </div>
    
    <div class="panel-body p-4 bg-white">
        
        <?php if (!$dept_id && !$emp_id): ?>
            <!-- VIEW 1: DEPARTMENT LIST -->
            <h5 class="fw-bold mb-3"><i class="fas fa-building text-secondary"></i> Báo cáo theo Phòng Ban</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle table-modern">
                    <thead class="table-light">
                        <tr>
                            <th>Phòng Ban</th>
                            <th class="text-center">Số Nhân sự</th>
                            <th class="text-center">Tổng Ngày Công</th>
                            <th class="text-center">Thực Tế</th>
                            <th class="text-center">Tỷ lệ Chấm công</th>
                            <th class="text-right">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($departments as $dept): 
                            $rate = $dept['total_working_days'] > 0 ? round(($dept['actual_working_days'] / $dept['total_working_days']) * 100, 1) : 0;
                            $rateClass = $rate >= 95 ? 'success' : ($rate >= 85 ? 'warning' : 'danger');
                        ?>
                        <tr>
                            <td class="fw-bold text-primary" style="font-size:15px;"><?= h($dept['name']) ?></td>
                            <td class="text-center fw-bold text-dark"><?= $dept['total_employees'] ?> <i class="fas fa-users text-muted small"></i></td>
                            <td class="text-center"><?= $dept['total_working_days'] ?> ngày</td>
                            <td class="text-center fw-bold"><?= $dept['actual_working_days'] ?> ngày</td>
                            <td class="text-center" style="width: 250px;">
                                <div class="d-flex align-items-center justify-content-center">
                                    <div class="progress mr-2" style="height: 10px; border-radius: 5px; flex-grow: 1; background:#f1f5f9; min-width: 100px;">
                                        <div class="progress-bar bg-<?= $rateClass ?>" role="progressbar" style="width: <?= $rate ?>%;"></div>
                                    </div>
                                    <span class="text-<?= $rateClass ?> fw-bold" style="font-size:13px; min-width: 40px; text-align: right;"><?= $rate ?>%</span>
                                </div>
                            </td>
                            <td class="text-right">
                                <a href="<?= BASE_URL ?>/payroll/timesheet?month=<?= $month ?>&year=<?= $year ?>&dept_id=<?= $dept['id'] ?>" class="btn btn-sm btn-ghost text-primary fw-bold" style="background:rgba(37,99,235,0.1);">
                                    Chi tiết <i class="fas fa-chevron-right ms-1"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
        <?php elseif ($dept_id && !$emp_id): ?>
            <!-- VIEW 2: EMPLOYEE LIST IN DEPARTMENT -->
            <h5 class="fw-bold mb-3"><i class="fas fa-users text-secondary"></i> Chi tiết nhân sự phòng: <span class="text-primary"><?= h($departments[$dept_id]['name'] ?? '') ?></span></h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle table-modern">
                    <thead class="table-light">
                        <tr>
                            <th>Mã NV</th>
                            <th>Họ Tên</th>
                            <th>Chức vụ</th>
                            <th class="text-center">Ca Ngày</th>
                            <th class="text-center">Ca Đêm</th>
                            <th class="text-center">Tỷ lệ Chấm công</th>
                            <th class="text-right">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($employees_in_dept as $eId => $eData): 
                            $e = $eData['employee'];
                            $rate = $eData['attendance_rate'];
                            $rateClass = $rate >= 95 ? 'success' : ($rate >= 85 ? 'warning' : 'danger');
                        ?>
                        <tr>
                            <td class="fw-bold text-muted"><?= h($e['emp_code'] ?? '') ?></td>
                            <td class="fw-bold text-dark" style="font-size:15px;"><?= h($e['full_name'] ?? '') ?></td>
                            <td><?= h($e['pos_title'] ?? 'Nhân viên') ?></td>
                            <td class="text-center fw-bold text-success"><i class="fas fa-sun text-warning me-1"></i> <?= $eData['day_shifts'] ?></td>
                            <td class="text-center fw-bold text-purple"><i class="fas fa-moon text-muted me-1"></i> <?= $eData['night_shifts'] ?></td>
                            <td class="text-center">
                                <span class="badge bg-<?= $rateClass ?>-light text-<?= $rateClass ?>" style="font-size:13px; padding:6px 12px;"><?= $rate ?>%</span>
                            </td>
                            <td class="text-right">
                                <a href="<?= BASE_URL ?>/payroll/timesheet?month=<?= $month ?>&year=<?= $year ?>&emp_id=<?= $eId ?>" class="btn btn-sm btn-primary" style="box-shadow: 0 4px 10px rgba(37,99,235,0.2);">
                                    Bảng Công Chi tiết <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        <?php elseif ($emp_id && $employee_detail): ?>
            <!-- VIEW 3: EMPLOYEE DETAIL GRID -->
            <?php $e = $employee_detail['employee']; ?>
            <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-4">
                <div class="d-flex align-items-center">
                    <div style="width:50px; height:50px; border-radius:50%; background:var(--primary); color:white; display:flex; align-items:center; justify-content:center; font-size:20px; font-weight:bold; margin-right:15px;">
                        <?= substr($e['full_name'] ?? 'A', 0, 1) ?>
                    </div>
                    <div>
                        <h4 class="mb-0 fw-bold"><?= h($e['full_name'] ?? '') ?></h4>
                        <p class="mb-0 text-muted">Mã NV: <?= h($e['emp_code'] ?? '') ?> | Phân loại: <?= h($e['pos_title'] ?? '') ?></p>
                    </div>
                </div>
            </div>
            
            <?php
            // Calculate employee specific stats
            $empWorkD = 0; $empActD = 0; $dShift = 0; $nShift = 0; $ot = 0;
            for ($d=1; $d<=$daysInMonth; $d++) {
                $ds = "$year-$month-" . str_pad($d, 2, '0', STR_PAD_LEFT);
                if (date('w', strtotime($ds)) != 0) $empWorkD++;
                $sym = $employee_detail['days'][$d] ?? '';
                if ($sym === 'X') { $empActD++; $dShift++; }
                if ($sym === 'Đ') { $empActD++; $nShift++; }
                if ($sym === 'OT') { $empActD++; $ot += 2.5; }
            }
            $rate = $empWorkD > 0 ? round(($empActD / $empWorkD) * 100, 1) : 0;
            ?>
            <div class="row mb-4">
                <div class="col-md-2"><div class="card bg-light border-0 text-center p-3"><h3 class="text-primary mb-1"><?= $empWorkD ?></h3><small class="text-muted fw-bold">Tổng Ngày Công</small></div></div>
                <div class="col-md-2"><div class="card bg-light border-0 text-center p-3"><h3 class="text-success mb-1"><?= $empActD ?></h3><small class="text-muted fw-bold">Thực Tế Làm</small></div></div>
                <div class="col-md-2"><div class="card bg-light border-0 text-center p-3"><h3 class="text-warning mb-1"><?= $dShift ?></h3><small class="text-muted fw-bold">Ca Ngày</small></div></div>
                <div class="col-md-2"><div class="card bg-light border-0 text-center p-3"><h3 class="text-purple mb-1"><?= $nShift ?></h3><small class="text-muted fw-bold">Ca Đêm</small></div></div>
                <div class="col-md-2"><div class="card bg-light border-0 text-center p-3"><h3 class="text-danger mb-1"><?= $ot ?>h</h3><small class="text-muted fw-bold">Giờ OT</small></div></div>
                <div class="col-md-2"><div class="card bg-light border-0 text-center p-3"><h3 class="text-dark mb-1"><?= $rate ?>%</h3><small class="text-muted fw-bold">Tỷ lệ Chấm công</small></div></div>
            </div>

            <div class="row mb-4">
                <div class="col-md-8">
                    <h6 class="fw-bold"><i class="fas fa-calendar-alt text-primary"></i> Lịch trình chấm công 31 ngày</h6>
                    <div class="table-responsive" style="border-radius:12px; border:1px solid var(--border); overflow-x: auto;">
                        <table class="table table-bordered table-sm text-center mb-0" style="table-layout: fixed; min-width: 1000px;">
                            <thead class="bg-light">
                                <tr>
                                    <?php for($d=1; $d<=$daysInMonth; $d++): 
                                        $dateStr = "$year-$month-" . str_pad($d, 2, '0', STR_PAD_LEFT);
                                        $isSunday = date('w', strtotime($dateStr)) == 0;
                                    ?>
                                        <th class="<?= $isSunday ? 'text-danger bg-danger-light' : '' ?>" style="width:3.2%; font-size:12px; padding:10px 0;"><?= $d ?></th>
                                    <?php endfor; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <?php for($d=1; $d<=$daysInMonth; $d++): 
                                        $symbol = $employee_detail['days'][$d] ?? ''; 
                                        $bgClass = '';
                                        $textClass = 'fw-bold';
                                        $tooltip = '';
                                        
                                        if ($symbol === 'X') {
                                            $bgClass = 'bg-success-light';
                                            $textClass = 'text-success';
                                            $tooltip = 'Làm Ca Ngày (08:00 - 17:00)';
                                        } elseif ($symbol === 'Đ') {
                                            $bgClass = 'bg-success-light';
                                            $textClass = 'text-success';
                                            $tooltip = 'Làm Ca Đêm (20:00 - 05:00)';
                                        } elseif ($symbol === 'OT') {
                                            $bgClass = 'bg-purple-light';
                                            $textClass = 'text-purple';
                                            $tooltip = 'Tăng ca (17:00 - 20:00)';
                                        } elseif ($symbol === 'P') {
                                            $bgClass = 'bg-warning-light';
                                            $textClass = 'text-warning';
                                            $tooltip = 'Nghỉ Phép (Có lương)';
                                        } else {
                                            $symbol = 'V';
                                            $bgClass = 'bg-danger-light';
                                            $textClass = 'text-danger';
                                            $tooltip = 'Vắng mặt (Không lương)';
                                        }
                                        
                                        $dateStr = "$year-$month-" . str_pad($d, 2, '0', STR_PAD_LEFT);
                                        $isSunday = date('w', strtotime($dateStr)) == 0;
                                        if ($isSunday) {
                                            if ($symbol === 'OT') {
                                                $tooltip = 'Tăng ca Chủ Nhật (08:00 - 17:00) - 2.0x Lương';
                                            } elseif ($symbol === 'V') {
                                                $symbol = ''; // Don't show V for Sunday if not working
                                                $bgClass = 'bg-light';
                                                $tooltip = 'Nghỉ Chủ Nhật (Ngày nghỉ tuần)';
                                            }
                                        }
                                    ?>
                                    <td class="align-middle <?= $bgClass ?> <?= $textClass ?>" style="padding:10px 0; font-size:13px; cursor: help;" title="<?= $tooltip ?>">
                                        <?= $symbol ?>
                                    </td>
                                    <?php endfor; ?>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <h6 class="fw-bold"><i class="fas fa-info-circle text-info"></i> Quy định / Ghi chú Ca Làm Việc</h6>
                    <div class="card shadow-none" style="border:1px dashed var(--border); border-radius:12px; background:#f8fafc;">
                        <div class="card-body">
                            <ul class="list-unstyled mb-0" style="font-size:14px; line-height:1.8;">
                                <li><i class="fas fa-sun text-warning me-2"></i> <strong>Ca Ngày (X):</strong> 08:00 - 17:00 (1.0x lương)</li>
                                <li><i class="fas fa-moon text-purple me-2"></i> <strong>Ca Đêm (Đ):</strong> 20:00 - 05:00 (1.3x lương)</li>
                                <li><i class="fas fa-clock text-danger me-2"></i> <strong>Tăng ca (OT):</strong> 17:00 - 20:00 (1.5x lương)</li>
                                <li><i class="fas fa-calendar-day text-success me-2"></i> <strong>Tăng ca CN (OT CN):</strong> 08:00 - 17:00 (2.0x lương)</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            
        <?php endif; ?>
    </div>
</div>

<style>
/* Utility Colors */
.text-purple { color: #8b5cf6 !important; font-weight: 600; }
.text-success { color: #10b981 !important; font-weight: 600; }
.text-warning { color: #f59e0b !important; font-weight: 600; }
.text-danger { color: #ef4444 !important; font-weight: 600; }
.bg-success-light { background-color: rgba(16, 185, 129, 0.1) !important; }
.bg-purple-light { background-color: rgba(139, 92, 246, 0.1) !important; }
.bg-warning-light { background-color: rgba(245, 158, 11, 0.1) !important; }
.bg-danger-light { background-color: rgba(239, 68, 68, 0.1) !important; }
.bg-light { background-color: #f8fafc !important; }
.fw-bold { font-weight: bold; }
.table-modern th, .table-modern td { padding: 16px 12px; }
.btn-ghost { background: transparent; border: none; padding: 6px 12px; border-radius: 6px; }
.btn-ghost:hover { background: rgba(0,0,0,0.05); }
.progress { overflow: hidden; }
</style>
