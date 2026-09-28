<!-- app/views/reports/project_labor_cost.php -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<div class="panel">
    <div class="panel-header d-flex justify-content-between align-items-center">
        <h3><i class="fas fa-file-invoice-dollar"></i> Báo cáo Chi phí Nhân công theo Dự án</h3>
        <div>
            <button type="button" class="btn btn-sm btn-success" onclick="exportTableToExcel('costTable', 'BaoCaoChiPhi_DuAn_T<?= $filters['month'] ?>_<?= $filters['year'] ?>')">
                <i class="fas fa-file-excel"></i> Xuất Excel
            </button>
            <button type="button" class="btn btn-sm btn-dark ms-2" onclick="window.print()">
                <i class="fas fa-print"></i> In Báo cáo
            </button>
        </div>
    </div>
    
    <div class="panel-body bg-light border-bottom no-print">
        <form method="GET" action="<?= BASE_URL ?>/report/projectLaborCost" class="row gx-2 gy-2 align-items-center">
            <div class="col-auto">
                <select name="month" class="form-select form-select-sm">
                    <?php for($i=1; $i<=12; $i++): ?>
                        <option value="<?= $i ?>" <?= $i == $filters['month'] ? 'selected' : '' ?>>Tháng <?= $i ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-auto">
                <select name="year" class="form-select form-select-sm">
                    <?php for($i=2024; $i<=date('Y')+1; $i++): ?>
                        <option value="<?= $i ?>" <?= $i == $filters['year'] ? 'selected' : '' ?>>Năm <?= $i ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-auto">
                <select name="project_id" class="form-select form-select-sm" style="width: 250px;">
                    <option value="">-- Tất cả dự án --</option>
                    <?php foreach($projects as $p): ?>
                        <option value="<?= $p->id ?>" <?= $p->id == $filters['project_id'] ? 'selected' : '' ?>><?= h($p->project_code) ?> - <?= h($p->project_name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto">
                <select name="position" class="form-select form-select-sm">
                    <option value="">-- Tất cả vị trí --</option>
                    <option value="Kỹ sư" <?= $filters['position'] == 'Kỹ sư' ? 'selected' : '' ?>>Nhóm Kỹ sư</option>
                    <option value="Giám sát" <?= $filters['position'] == 'Giám sát' ? 'selected' : '' ?>>Nhóm Giám sát</option>
                    <option value="Thợ cơ điện" <?= $filters['position'] == 'Thợ cơ điện' ? 'selected' : '' ?>>Nhóm Thợ cơ điện (M&E)</option>
                    <option value="Thợ phụ" <?= $filters['position'] == 'Thợ phụ' ? 'selected' : '' ?>>Nhóm Thợ phụ</option>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-filter"></i> Lọc dữ liệu</button>
            </div>
        </form>
    </div>

    <div class="panel-body print-area">
        <!-- Print Header -->
        <div class="d-none d-print-block mb-4 text-center">
            <h2 class="mb-1 fw-bold text-uppercase">POSUNG MEC CO., LTD</h2>
            <h4 class="mb-2">BÁO CÁO PHÂN BỔ CHI PHÍ NHÂN CÔNG THEO DỰ ÁN</h4>
            <p>Kỳ báo cáo: Tháng <?= $filters['month'] ?> Năm <?= $filters['year'] ?></p>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle text-center" id="costTable" style="font-size: 13px;">
                <thead class="table-dark">
                    <tr>
                        <th rowspan="2" class="align-middle">STT</th>
                        <th rowspan="2" class="align-middle">Mã Dự án</th>
                        <th rowspan="2" class="align-middle">Tên Dự án</th>
                        <th rowspan="2" class="align-middle">Mã NV</th>
                        <th rowspan="2" class="align-middle text-start">Họ tên</th>
                        <th rowspan="2" class="align-middle">Chức vụ / Vị trí</th>
                        <th colspan="4">Cấu thành Chi phí (VNĐ)</th>
                        <th rowspan="2" class="align-middle">Tổng Chi phí <br> (Total Labor Cost)</th>
                    </tr>
                    <tr>
                        <th>Lương cơ bản</th>
                        <th>Tiền tăng ca (OT)</th>
                        <th>Tổng Phụ cấp</th>
                        <th>Khấu trừ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                        $grandTotal = 0;
                        $otTotal = 0;
                        if(empty($data)): 
                    ?>
                        <tr><td colspan="11" class="text-muted p-4">Không có dữ liệu trong kỳ này.</td></tr>
                    <?php else: ?>
                        <?php foreach($data as $idx => $row): 
                            $grandTotal += $row['net_salary'];
                            $otTotal += $row['ot_pay'];
                        ?>
                        <tr>
                            <td><?= $idx + 1 ?></td>
                            <td class="fw-bold"><?= h($row['project_code'] ?? 'VP') ?></td>
                            <td class="text-start"><?= h($row['project_name'] ?? 'Văn phòng Công ty') ?></td>
                            <td><?= h($row['emp_code']) ?></td>
                            <td class="text-start fw-bold"><?= h($row['full_name']) ?></td>
                            <td><?= h($row['pos_title']) ?></td>
                            <td class="text-end"><?= number_format($row['regular_pay']) ?></td>
                            <td class="text-end text-danger"><?= number_format($row['ot_pay']) ?></td>
                            <td class="text-end text-success"><?= number_format($row['allowances_total']) ?></td>
                            <td class="text-end">-<?= number_format($row['deductions_total']) ?></td>
                            <td class="text-end fw-bold text-primary" style="font-size: 14px;"><?= number_format($row['net_salary']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <!-- Dòng Tổng cộng -->
                        <tr class="table-secondary fw-bold" style="font-size: 14px;">
                            <td colspan="6" class="text-end text-uppercase">TỔNG CỘNG TOÀN CÔNG TY (KỲ <?= $filters['month'] ?>/<?= $filters['year'] ?>):</td>
                            <td colspan="3"></td>
                            <td class="text-end text-danger">TỔNG OT: <?= number_format($otTotal) ?></td>
                            <td class="text-end text-primary"><?= number_format($grandTotal) ?> VNĐ</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Print Footer -->
        <div class="d-none d-print-block mt-5 pt-4">
            <div class="row text-center fw-bold">
                <div class="col-4">Người lập biểu<br><br><br><br></div>
                <div class="col-4">Kế toán trưởng<br><br><br><br></div>
                <div class="col-4">Giám đốc (CEO)<br><br><br><br></div>
            </div>
        </div>
    </div>
</div>

<script>
function exportTableToExcel(tableID, filename = ''){
    var downloadLink;
    var dataType = 'application/vnd.ms-excel';
    var tableSelect = document.getElementById(tableID);
    
    var wb = XLSX.utils.table_to_book(tableSelect, {sheet:"Cost_Allocation", raw:true});
    XLSX.writeFile(wb, filename + ".xlsx");
}
</script>

<style>
@media print {
    body * { visibility: hidden; }
    .print-area, .print-area * { visibility: visible; }
    .print-area { position: absolute; left: 0; top: 0; width: 100%; }
    .no-print { display: none !important; }
    .table-dark { background-color: #f8f9fa !important; color: #000 !important; }
    .table-dark th { border: 1px solid #000 !important; }
    .table-bordered td, .table-bordered th { border: 1px solid #000 !important; }
}
</style>
