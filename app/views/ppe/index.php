<div class="breadcrumb-bar">
    <a href="<?= BASE_URL ?>/dashboard"><i class="fas fa-home"></i> Trang chủ</a>
    <i class="fas fa-chevron-right"></i>
    <span>Quản lý BHLĐ (PPE)</span>
</div>

<div class="panel-header" style="margin-bottom: 20px; display:flex; justify-content:space-between; align-items:center;">
    <h2><i class="fas fa-hard-hat" style="color: #f59e0b;"></i> Quản lý Cấp phát BHLĐ (PPE)</h2>
    <button class="btn btn-primary"><i class="fas fa-plus"></i> Cấp phát mới</button>
</div>

<div class="row">
    <!-- Cột Biểu đồ -->
    <div class="col-md-4">
        <div class="panel glass-panel" style="text-align: center;">
            <h3 style="font-size: 1.1rem; margin-bottom: 15px;"><i class="fas fa-chart-pie"></i> Tình trạng PPE Hiện tại</h3>
            <div style="position: relative; height: 250px; width: 100%;">
                <canvas id="ppeChart"></canvas>
            </div>
            <div style="margin-top: 15px; font-size: 0.9rem; color: #475569;">
                Tổng số lượng thiết bị/đồng phục được ghi nhận.
            </div>
        </div>
    </div>

    <!-- Cột Bảng dữ liệu -->
    <div class="col-md-8">
        <div class="panel glass-panel">
            <div style="margin-bottom: 15px; display: flex; justify-content: space-between;">
                <input type="text" id="searchInput" class="form-control" placeholder="Tìm theo Tên NV, Mã NV hoặc Tên thiết bị..." style="max-width: 300px;" onkeyup="filterTable()">
                <button class="btn btn-outline-secondary"><i class="fas fa-file-excel"></i> Xuất Excel</button>
            </div>

            <div class="table-wrapper" style="max-height: 400px; overflow-y: auto;">
                <table class="table table-hover" id="ppeTable">
                    <thead style="position: sticky; top: 0; background: #f8fafc; z-index: 1;">
                        <tr>
                            <th>Nhân viên</th>
                            <th>Trang bị / Vật tư</th>
                            <th style="text-align:center;">SL</th>
                            <th>Ngày cấp</th>
                            <th>Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($issuances)): ?>
                        <tr><td colspan="5" style="text-align:center; padding: 20px; color:#94a3b8;">Chưa có dữ liệu cấp phát.</td></tr>
                        <?php else: ?>
                            <?php foreach ($issuances as $i): ?>
                            <tr>
                                <td>
                                    <strong><?= h($i['full_name']) ?></strong><br>
                                    <small style="color: #64748b;"><?= h($i['emp_code']) ?></small>
                                </td>
                                <td>
                                    <?= h($i['ppe_item_name']) ?><br>
                                    <?php if ($i['serial_no']): ?>
                                        <small style="color: #8b5cf6;">S/N: <?= h($i['serial_no']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align:center;"><strong><?= $i['quantity'] ?></strong></td>
                                <td><?= $i['issue_date'] ? date('d/m/Y', strtotime($i['issue_date'])) : '—' ?></td>
                                <td>
                                    <?php if ($i['status'] === 'Issued'): ?>
                                        <span class="badge" style="background:rgba(16,185,129,0.1); color:#10b981;">Đang sử dụng</span>
                                    <?php elseif ($i['status'] === 'Returned'): ?>
                                        <span class="badge" style="background:rgba(100,116,139,0.1); color:#64748b;">Đã trả lại</span>
                                    <?php elseif ($i['status'] === 'Lost'): ?>
                                        <span class="badge" style="background:rgba(239,68,68,0.1); color:#ef4444;">Báo mất</span>
                                    <?php elseif ($i['status'] === 'Damaged'): ?>
                                        <span class="badge" style="background:rgba(245,158,11,0.1); color:#f59e0b;">Hư hỏng</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Nạp Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('ppeChart').getContext('2d');
    const dataStats = <?= json_encode($stats) ?>;
    
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Đang sử dụng', 'Đã trả', 'Mất', 'Hư hỏng'],
            datasets: [{
                data: [dataStats.Issued, dataStats.Returned, dataStats.Lost, dataStats.Damaged],
                backgroundColor: [
                    '#10b981', // Issued (Green)
                    '#94a3b8', // Returned (Gray)
                    '#ef4444', // Lost (Red)
                    '#f59e0b'  // Damaged (Orange)
                ],
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }
            },
            cutout: '70%'
        }
    });
});

// Hàm lọc table đơn giản
function filterTable() {
    const filter = document.getElementById('searchInput').value.toUpperCase();
    const rows = document.getElementById('ppeTable').getElementsByTagName('tr');
    
    for (let i = 1; i < rows.length; i++) { // Skip header
        let td = rows[i].getElementsByTagName('td');
        let match = false;
        for (let j = 0; j < td.length; j++) {
            if (td[j]) {
                if (td[j].innerHTML.toUpperCase().indexOf(filter) > -1) {
                    match = true;
                    break;
                }
            }
        }
        rows[i].style.display = match ? '' : 'none';
    }
}
</script>
