<div class="content-header">
    <div class="header-left">
        <h2><i class="fas fa-hard-hat text-danger"></i> Quản lý HSE (Safety)</h2>
        <p>Bảng điều khiển cảnh báo an toàn & Vi phạm quy định.</p>
    </div>
</div>

<div class="row">
    <!-- Cột 1: Cảnh báo Dự án -->
    <div class="col-md-7">
        <div class="panel border-danger">
            <div class="panel-header bg-danger text-white">
                <h3 class="text-white m-0"><i class="fas fa-exclamation-triangle"></i> Cảnh báo Vi phạm theo Dự án</h3>
            </div>
            <div class="panel-body">
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Dự án</th>
                                <th>Tổng Sự cố</th>
                                <th>Tổng Biên bản Phạt</th>
                                <th>Cảnh báo</th>
                                <th style="width: 80px;">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($projectStats as $s): ?>
                                <?php 
                                    $isDanger = $s['violation_count'] > 10 || $s['incident_count'] > 5;
                                ?>
                                <tr class="<?= $isDanger ? 'bg-red-50' : '' ?>">
                                    <td><strong><?= h($s['name']) ?></strong></td>
                                    <td class="text-center">
                                        <span class="badge <?= $s['incident_count'] > 0 ? 'bg-warning text-dark' : 'bg-success' ?>">
                                            <?= h($s['incident_count']) ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge <?= $s['violation_count'] > 0 ? 'bg-danger' : 'bg-success' ?>">
                                            <?= h($s['violation_count']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if($isDanger): ?>
                                            <span class="text-danger fw-bold"><i class="fas fa-skull"></i> Mức độ ĐỎ</span>
                                        <?php else: ?>
                                            <span class="text-success"><i class="fas fa-check-circle"></i> An toàn</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <a href="<?= BASE_URL ?>/project/detail/<?= $s['project_id'] ?? 0 ?>" class="btn btn-sm btn-outline-primary" title="Xem chi tiết dự án">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Cột 2: Cảnh báo Thẻ An Toàn -->
    <div class="col-md-5">
        <div class="panel border-warning">
            <div class="panel-header bg-warning">
                <h3 class="text-dark m-0"><i class="fas fa-id-card"></i> Thẻ HSE sắp hết hạn (30 ngày)</h3>
            </div>
            <div class="panel-body">
                <?php if (empty($expiringCerts)): ?>
                    <p class="text-success"><i class="fas fa-check"></i> Không có nhân sự nào sắp hết hạn thẻ.</p>
                <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($expiringCerts as $c): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <div>
                                    <strong><?= h($c['full_name']) ?></strong> 
                                    <span class="text-muted">(<?= h($c['emp_code']) ?> - <?= h($c['role']) ?>)</span>
                                    <br>
                                    <small class="text-danger">Hết hạn: <?= date('d/m/Y', strtotime($c['expiry'])) ?></small>
                                </div>
                                <div>
                                    <span class="badge bg-secondary me-2"><?= h($c['type']) ?></span>
                                    <?php if(isset($c['emp_id'])): ?>
                                        <a href="<?= BASE_URL ?>/employee/detail/<?= $c['emp_id'] ?>" class="btn btn-sm btn-outline-secondary" title="Xem hồ sơ">
                                            <i class="fas fa-arrow-right"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.bg-red-50 { background-color: #fef2f2 !important; }
.border-danger { border: 1px solid #ef4444; }
.border-warning { border: 1px solid #f59e0b; }
</style>
