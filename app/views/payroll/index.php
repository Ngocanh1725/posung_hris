<?php
/**
 * ============================================================
 *  View: payroll/index.php
 *  Bảng Lương Tổng Hợp
 * ============================================================
 */
?>

<div class="panel mb-4">
    <div class="panel-header d-flex justify-content-between align-items-center">
        <h3><i class="fas fa-money-check-alt"></i> Bảng Lương Tháng <?= $month ?>/<?= $year ?></h3>
        
        <div class="d-flex align-items-center">
            <form action="<?= BASE_URL ?>/payroll" method="GET" class="d-flex mr-3">
                <select name="month" class="form-control form-control-sm mr-2" style="width: 80px;">
                    <?php for($m=1; $m<=12; $m++): ?>
                        <option value="<?= $m ?>" <?= $m == $month ? 'selected' : '' ?>><?= $m ?></option>
                    <?php endfor; ?>
                </select>
                <select name="year" class="form-control form-control-sm mr-2" style="width: 80px;">
                    <?php for($y=2024; $y<=date('Y'); $y++): ?>
                        <option value="<?= $y ?>" <?= $y == $year ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
                <select name="project_id" class="form-control form-control-sm mr-2" style="width: 150px;">
                    <option value="">-- Tất cả Dự án --</option>
                    <?php foreach($projects as $p): ?>
                        <option value="<?= $p->id ?>" <?= $p->id == $currentProject ? 'selected' : '' ?>><?= htmlspecialchars($p->project_code) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-secondary btn-sm"><i class="fas fa-filter"></i> Xem</button>
            </form>

            <form action="<?= BASE_URL ?>/payroll/calculate" method="POST" style="margin: 0;" onsubmit="return confirm('Hệ thống sẽ chạy Engine tính lương lại cho tháng <?= $m ?>/<?= $y ?>. Quá trình này có thể mất vài giây. Tiếp tục?');">
                <input type="hidden" name="month" value="<?= $month ?>">
                <input type="hidden" name="year" value="<?= $year ?>">
                <input type="hidden" name="project_id" value="<?= $currentProject ?>">
                <button type="submit" class="btn btn-primary btn-sm mr-2">
                    <i class="fas fa-calculator"></i> Tính Lương
                </button>
            </form>

            <button type="button" class="btn btn-success btn-sm" onclick="exportTableToExcel('payrollTable', 'BangLuong_<?= $month ?>_<?= $year ?>')">
                <i class="fas fa-file-excel"></i> Xuất Excel
            </button>
        </div>
    </div>

    <!-- Tóm tắt -->
    <div class="panel-body border-bottom bg-light">
        <div class="row text-center">
            <div class="col-md-3 border-right">
                <div class="text-muted small">Tổng Nhân Sự</div>
                <h3 class="text-primary mt-1 mb-0"><?= count($payrolls) ?></h3>
            </div>
            <div class="col-md-4 border-right">
                <div class="text-muted small">Tổng Quỹ Lương (Thực lĩnh)</div>
                <h3 class="text-success mt-1 mb-0"><?= number_format($totalNet, 0, ',', '.') ?> <small>VNĐ</small></h3>
            </div>
            <div class="col-md-5 text-left pl-4">
                <div class="text-muted small mb-1"><i class="fas fa-info-circle"></i> Trạng thái Kỳ lương:</div>
                <?php if (!empty($payrolls) && $payrolls[0]->payment_status === 'Approved'): ?>
                    <span class="badge badge-active"><i class="fas fa-lock"></i> Đã Chốt & Gửi Kế Toán</span>
                <?php elseif (!empty($payrolls)): ?>
                    <span class="badge badge-warning"><i class="fas fa-unlock"></i> Đang tính toán (Chưa chốt)</span>
                <?php else: ?>
                    <span class="badge badge-resigned">Chưa có dữ liệu</span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Bảng dữ liệu -->
    <div class="panel-body p-0">
        <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
            <table class="table table-bordered table-hover mb-0" id="payrollTable" style="font-size: 13px;">
                <thead style="position: sticky; top: 0; background: var(--bg-card); z-index: 10;">
                    <tr class="text-center align-middle">
                        <th rowspan="2" style="min-width: 50px;">STT</th>
                        <th rowspan="2" style="min-width: 100px;">Mã NV</th>
                        <th rowspan="2" style="min-width: 180px;">Họ tên</th>
                        <th rowspan="2" style="min-width: 100px;">Dự án / CC</th>
                        <th colspan="3">Công & Tăng ca</th>
                        <th colspan="3">Chi tiết Thu nhập (VNĐ)</th>
                        <th rowspan="2" style="min-width: 120px;">Thực lĩnh</th>
                        <th rowspan="2" class="no-print">Phiếu Lương</th>
                    </tr>
                    <tr class="text-center">
                        <th>Quy đổi</th>
                        <th>Phụ cấp</th>
                        <th>Tiền OT</th>
                        <th>Khấu trừ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($payrolls)): ?>
                        <tr><td colspan="11" class="text-center p-5 text-muted">Chưa có dữ liệu lương. Vui lòng nhấn "Tính Lương" để hệ thống chạy Engine.</td></tr>
                    <?php else: ?>
                        <?php foreach ($payrolls as $index => $pr): ?>
                        <tr>
                            <td class="text-center"><?= $index + 1 ?></td>
                            <td class="text-center font-weight-bold text-primary"><?= htmlspecialchars($pr->emp_code) ?></td>
                            <td>
                                <strong><?= htmlspecialchars($pr->full_name) ?></strong><br>
                                <small class="text-muted"><?= htmlspecialchars($pr->pos_title ?? '') ?></small>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-secondary"><?= htmlspecialchars($pr->cc_code ?? 'N/A') ?></span>
                            </td>
                            <td class="text-center text-success font-weight-bold"><?= $pr->actual_days ?></td>
                            <td class="text-right"><?= number_format($pr->allowances_total, 0, ',', '.') ?></td>
                            <td class="text-right"><?= number_format($pr->ot_pay, 0, ',', '.') ?></td>
                            <td class="text-right text-danger">-<?= number_format($pr->deductions_total, 0, ',', '.') ?></td>
                            <td class="text-right font-weight-bold text-success" style="font-size: 15px;">
                                <?= number_format($pr->net_salary, 0, ',', '.') ?>
                            </td>
                            <td class="text-center no-print">
                                <a href="<?= BASE_URL ?>/payroll/payslip/<?= $pr->employee_id ?>/<?= $month ?>/<?= $year ?>" target="_blank" class="btn btn-ghost btn-sm text-primary" title="In Phiếu Lương">
                                    <i class="fas fa-print"></i> View
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
.table-bordered th, .table-bordered td { border: 1px solid var(--border); }
.text-right { text-align: right; }
.text-center { text-align: center; }
.font-weight-bold { font-weight: 600; }
.bg-light { background: rgba(241, 245, 249, 0.5); }
.border-right { border-right: 1px solid var(--border); }
.border-bottom { border-bottom: 1px solid var(--border); }
.badge-warning { background: rgba(245, 158, 11, 0.2); color: #f59e0b; }
</style>

<!-- Sử dụng thư viện xlsx để xuất excel đơn giản phía client -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script>
function exportTableToExcel(tableID, filename = ''){
    var downloadLink;
    var dataType = 'application/vnd.ms-excel';
    var tableSelect = document.getElementById(tableID);
    
    // Tạo workbook từ table (bỏ qua cột no-print)
    var wb = XLSX.utils.table_to_book(tableSelect, {sheet:"Sheet1", raw:true});
    XLSX.writeFile(wb, filename + ".xlsx");
}
</script>
