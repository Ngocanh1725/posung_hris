<?php
/**
 * ============================================================
 *  View: payroll/timesheet.php
 *  Ma trận chấm công (Grid View) 31 ngày
 * ============================================================
 */
?>

<div class="panel mb-4">
    <div class="panel-header d-flex justify-content-between align-items-center">
        <h3><i class="far fa-calendar-check"></i> Bảng Chấm Công - <?= $month ?>/<?= $year ?></h3>
        <div class="d-flex align-items-center">
            <form action="<?= BASE_URL ?>/payroll/timesheet" method="GET" class="d-flex align-items-center mr-3">
                <select name="month" class="form-control form-control-sm mr-2" style="width: 80px;">
                    <?php for($m=1; $m<=12; $m++): ?>
                        <option value="<?= $m ?>" <?= $m == $month ? 'selected' : '' ?>>Tháng <?= $m ?></option>
                    <?php endfor; ?>
                </select>
                <select name="year" class="form-control form-control-sm mr-2" style="width: 100px;">
                    <?php for($y=2024; $y<=date('Y'); $y++): ?>
                        <option value="<?= $y ?>" <?= $y == $year ? 'selected' : '' ?>>Năm <?= $y ?></option>
                    <?php endfor; ?>
                </select>
                <select name="project_id" class="form-control form-control-sm mr-2">
                    <option value="">-- Toàn công ty --</option>
                    <?php foreach($projects as $p): ?>
                        <option value="<?= $p->id ?>" <?= $p->id == $currentProject ? 'selected' : '' ?>><?= h($p->project_name) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter"></i> Lọc</button>
            </form>
            
            <form action="<?= BASE_URL ?>/timesheet/lock" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn KHÓA bảng công tháng này? Sau khi khóa sẽ không thể sửa đổi.');">
                <input type="hidden" name="month" value="<?= $month ?>">
                <input type="hidden" name="year" value="<?= $year ?>">
                <input type="hidden" name="project_id" value="<?= $currentProject ?>">
                <button type="submit" class="btn btn-danger btn-sm" <?= !$currentProject ? 'disabled title="Vui lòng chọn Dự án"' : '' ?>>
                    <i class="fas fa-lock"></i> Khóa Bảng Công
                </button>
            </form>
        </div>
    </div>

    <div class="panel-body p-0">
        <div class="table-responsive" style="max-height: 500px; overflow-y: auto; overflow-x: auto;">
            <table class="table table-bordered table-sm table-hover mb-0 text-center" style="font-size: 13px; table-layout: auto !important; width: max-content !important; min-width: 100% !important;">
                <thead style="position: sticky; top: 0; background: var(--bg-card); z-index: 10;">
                    <tr>
                        <th rowspan="2" class="align-middle text-left pl-3" style="min-width: 250px; z-index: 11; left: 0; position: sticky; background: var(--bg-card);">Nhân sự</th>
                        <th colspan="31">Ngày trong tháng</th>
                    </tr>
                    <tr>
                        <?php 
                        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
                        for($d=1; $d<=$daysInMonth; $d++): 
                            // Highlight Chủ nhật
                            $dateStr = "$year-$month-$d";
                            $isSunday = date('w', strtotime($dateStr)) == 0;
                        ?>
                            <th class="<?= $isSunday ? 'text-danger' : '' ?>" style="min-width: 35px;"><?= $d ?></th>
                        <?php endfor; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($grid)): ?>
                        <tr><td colspan="32" class="p-5 text-muted">Chưa có dữ liệu chấm công. Vui lòng Import từ thiết bị.</td></tr>
                    <?php else: ?>
                        <?php foreach ($grid as $empId => $row): ?>
                        <tr>
                            <td class="text-left pl-3" style="left: 0; position: sticky; background: #fff; z-index: 1; border-right: 2px solid var(--border);">
                                <strong><?= h($row['full_name']) ?></strong><br>
                                <small class="text-muted"><?= h($row['emp_code']) ?> | <?= h($row['pos_title'] ?? '') ?></small>
                            </td>
                            <?php for($d=1; $d<=$daysInMonth; $d++): ?>
                                <?php 
                                    $symbol = $row['days'][$d] ?? ''; 
                                    $class = '';
                                    if (strpos($symbol, 'N') !== false) $class = 'text-purple';
                                    if (strpos($symbol, 'CN') !== false) $class = 'text-danger font-weight-bold';
                                    if (strpos($symbol, 'CR') !== false) $class = 'text-info';
                                ?>
                                <td class="align-middle <?= $class ?>">
                                    <?= $symbol ?>
                                </td>
                            <?php endfor; ?>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="panel-footer bg-light p-3 border-top">
        <strong>Chú giải:</strong>
        <span class="mr-3 ml-2"><span class="badge bg-secondary">X</span> Ca ngày (1.0)</span>
        <span class="mr-3"><span class="badge" style="background:#8b5cf6;color:#fff;">N</span> Ca đêm (1.3)</span>
        <span class="mr-3"><span class="badge bg-danger">CN</span> Chủ nhật (2.0)</span>
        <span class="mr-3"><span class="badge bg-info">CR</span> Phòng sạch (+150k)</span>
        <span class="mr-3"><span class="text-danger small">+2.5</span> Giờ OT</span>
    </div>
</div>

<style>
.table-bordered th, .table-bordered td { border: 1px solid var(--border); }
.text-purple { color: #8b5cf6; font-weight: 600; }
.text-info { color: #0ea5e9; font-weight: 600; }
.text-danger { color: #ef4444; }
.pl-3 { padding-left: 1rem !important; }
.mr-2 { margin-right: 0.5rem; }
.mr-3 { margin-right: 1rem; }
.ml-2 { margin-left: 0.5rem; }
.bg-light { background: rgba(241, 245, 249, 0.5); }
</style>
