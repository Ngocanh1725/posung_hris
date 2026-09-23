<div class="breadcrumb-bar">
    <a href="<?= BASE_URL ?>/rbac"><i class="fas fa-shield-halved"></i> Trung tâm Phân quyền</a>
    <i class="fas fa-chevron-right"></i>
    <span>Admin Cơ sở Dữ liệu</span>
</div>

<div class="panel-header" style="margin-bottom: 20px; display:flex; justify-content:space-between; align-items:center;">
    <h2><i class="fas fa-database" style="color: #f59e0b;"></i> Quản trị Cơ sở Dữ liệu</h2>
    <div>
        <a href="<?= BASE_URL ?>/databaseAdmin/optimize" class="btn btn-warning"><i class="fas fa-magic"></i> Tối ưu hóa Toàn bộ (Optimize)</a>
        <a href="<?= BASE_URL ?>/databaseAdmin/backup" class="btn btn-success"><i class="fas fa-download"></i> Sao lưu (Backup SQL)</a>
    </div>
</div>

<div class="panel">
    <h3>Danh sách Bảng Dữ liệu (Tables)</h3>
    <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th>Tên Bảng</th>
                <th>Engine</th>
                <th>Số Dòng (Rows)</th>
                <th>Dung lượng Dữ liệu</th>
                <th>Dung lượng Index</th>
                <th>Collation</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $totalRows = 0;
            $totalData = 0;
            $totalIndex = 0;
            foreach ($tables as $t): 
                $totalRows += $t['Rows'];
                $totalData += $t['Data_length'];
                $totalIndex += $t['Index_length'];
            ?>
            <tr>
                <td><strong><?= h($t['Name']) ?></strong></td>
                <td><?= h($t['Engine']) ?></td>
                <td><?= number_format((float)$t['Rows']) ?></td>
                <td><?= round($t['Data_length'] / 1024, 2) ?> KB</td>
                <td><?= round($t['Index_length'] / 1024, 2) ?> KB</td>
                <td><?= h($t['Collation']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot style="background: #f8fafc; font-weight:bold;">
            <tr>
                <td colspan="2" class="text-right">TỔNG CỘNG:</td>
                <td><?= number_format($totalRows) ?> dòng</td>
                <td><?= round($totalData / 1024, 2) ?> KB</td>
                <td><?= round($totalIndex / 1024, 2) ?> KB</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</div>
