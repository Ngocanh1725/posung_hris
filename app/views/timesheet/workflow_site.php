<!-- app/views/timesheet/workflow_site.php -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Duyệt Công (Site Workflow) - Tháng <?= h($month) ?>/<?= h($year) ?></h6>
        <div class="d-flex gap-2">
            <form class="d-flex gap-2" method="GET" action="<?= BASE_URL ?>/timesheet/approvalSiteWorkflow">
                <input type="hidden" name="month" value="<?= h($month) ?>">
                <input type="hidden" name="year" value="<?= h($year) ?>">
                <select name="project_id" class="form-select form-select-sm w-auto">
                    <option value="">-- Tất cả dự án --</option>
                    <?php if (!empty($projects)): ?>
                        <?php foreach ($projects as $p): ?>
                            <option value="<?= $p['id'] ?>" <?= ($p['id'] == $currentProject) ? 'selected' : '' ?>>
                                <?= h($p['project_code'] . ' - ' . $p['project_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-filter"></i> Lọc</button>
            </form>
        </div>
    </div>
    <div class="card-body">
        
        <?php if (empty($grid)): ?>
            <div class="alert alert-info text-center">Không có dữ liệu chấm công nào trong tháng này.</div>
        <?php else: ?>
            <form action="<?= BASE_URL ?>/timesheet/approvalSiteWorkflow" method="POST">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                
                <div class="d-flex gap-2 mb-3 align-items-center bg-light p-2 rounded border">
                    <span class="fw-bold text-secondary me-2"><i class="fas fa-check-double"></i> Thao tác hàng loạt (Bulk Action):</span>
                    <select name="approval_status" class="form-select w-auto fw-bold" required>
                        <option value="">-- Chọn trạng thái duyệt --</option>
                        <option value="PENDING_SITE_MANAGER" class="text-warning">Chuyển lên Chỉ huy trưởng duyệt (Cấp 2)</option>
                        <option value="PENDING_HR" class="text-info">Chuyển lên Phòng HR duyệt (Cấp 3)</option>
                        <option value="APPROVED" class="text-success">Chốt công cuối cùng (HR Approved)</option>
                        <option value="REJECTED" class="text-danger">Từ chối / Yêu cầu giải trình</option>
                    </select>
                    <button type="submit" class="btn btn-success fw-bold px-4"><i class="fas fa-paper-plane"></i> Thực hiện</button>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-sm text-center align-middle" style="font-size: 0.85rem;">
                        <thead class="table-dark">
                            <tr>
                                <th style="width: 40px;">
                                    <input type="checkbox" id="checkAll" class="form-check-input">
                                </th>
                                <th>Ngày</th>
                                <th>Mã NV</th>
                                <th>Họ Tên</th>
                                <th>Ca/Giờ vào-ra</th>
                                <th>GPS / Khoảng cách</th>
                                <th>OT</th>
                                <th>Trạng thái hiện tại</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            // Note: $grid returned from getMonthlyGrid is grouped by employee_id,
                            // we need a flat list of timesheets to approve. 
                            // Actually, getMonthlyGrid only returns the summary matrix, NOT individual timesheet IDs.
                            // I will do a quick fetch of raw timesheets for this view logic!
                            
                            $db = Database::getInstance();
                            $pFilter = $currentProject ? "AND project_id = " . (int)$currentProject : "";
                            $db->query("
                                SELECT t.*, e.emp_code, e.full_name 
                                FROM timesheets t 
                                JOIN employees e ON t.employee_id = e.id 
                                WHERE MONTH(t.work_date) = $month AND YEAR(t.work_date) = $year $pFilter
                                ORDER BY t.work_date DESC, e.emp_code ASC
                            ");
                            $rawTimesheets = $db->fetchAll();
                            ?>
                            
                            <?php foreach ($rawTimesheets as $row): ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" name="timesheet_ids[]" value="<?= $row['id'] ?>" class="form-check-input item-check">
                                    </td>
                                    <td class="fw-bold"><?= date('d/m/Y', strtotime($row['work_date'])) ?></td>
                                    <td><?= h($row['emp_code']) ?></td>
                                    <td class="text-start"><?= h($row['full_name']) ?></td>
                                    <td>
                                        <?php if ($row['shift_type'] === 'Night'): ?>
                                            <span class="badge bg-dark">Đêm</span>
                                        <?php else: ?>
                                            <span class="badge bg-info text-dark">Ngày</span>
                                        <?php endif; ?>
                                        <br>
                                        <small class="text-muted"><?= h($row['check_in']) ?> - <?= h($row['check_out']) ?></small>
                                    </td>
                                    <td>
                                        <?php if ($row['checkin_device_type'] === 'SITE_GPS'): ?>
                                            <span class="text-success"><i class="fas fa-map-marker-alt"></i> Mobile</span><br>
                                            <small><?= $row['checkin_distance_m'] ?>m</small>
                                        <?php elseif ($row['checkin_device_type'] === 'OFFICE_FACEID'): ?>
                                            <span class="text-primary"><i class="fas fa-fingerprint"></i> FaceID</span>
                                        <?php else: ?>
                                            <span class="text-secondary"><i class="fas fa-edit"></i> Thủ công</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="fw-bold text-danger"><?= $row['ot_hours'] > 0 ? $row['ot_hours'].'h' : '-' ?></td>
                                    <td>
                                        <?php
                                        $badge = 'bg-secondary';
                                        $label = 'Không xác định';
                                        switch ($row['approval_status']) {
                                            case 'PENDING_FOREMAN': $badge = 'bg-secondary'; $label = 'Chờ Tổ trưởng'; break;
                                            case 'PENDING_SITE_MANAGER': $badge = 'bg-warning text-dark'; $label = 'Chờ CHT'; break;
                                            case 'PENDING_HR': $badge = 'bg-info text-dark'; $label = 'Chờ HR'; break;
                                            case 'APPROVED': $badge = 'bg-success'; $label = 'Đã chốt'; break;
                                            case 'REJECTED': $badge = 'bg-danger'; $label = 'Từ chối'; break;
                                        }
                                        ?>
                                        <span class="badge <?= $badge ?>"><?= $label ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<script>
document.getElementById('checkAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.item-check');
    checkboxes.forEach(cb => {
        cb.checked = this.checked;
    });
});
</script>
