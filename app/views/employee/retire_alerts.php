<?php
/**
 * ============================================================
 *  View: employee/retire_alerts.php
 *  Danh sách cảnh báo nhân sự sắp đến tuổi Hưu trí
 * ============================================================
 */
?>

<div class="panel mb-4 border-warning">
    <div class="panel-header bg-warning text-dark">
        <h3><i class="fas fa-user-clock"></i> Cảnh báo Hưu trí (Trong vòng 6 tháng)</h3>
    </div>
    <div class="panel-body p-0">
        <div class="p-3 bg-light border-bottom">
            <small class="text-muted"><i class="fas fa-info-circle"></i> Hệ thống đang tính theo lộ trình: Nam nghỉ hưu lúc 60.5 tuổi, Nữ lúc 55.6 tuổi.</small>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Mã NV</th>
                        <th>Họ tên</th>
                        <th>Giới tính</th>
                        <th>Ngày sinh</th>
                        <th>Tuổi hiện tại</th>
                        <th>Thời gian ước tính</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($alerts)): ?>
                        <tr><td colspan="7" class="text-center p-4">Hiện không có nhân sự nào sắp đến tuổi nghỉ hưu.</td></tr>
                    <?php else: ?>
                        <?php foreach ($alerts as $a): ?>
                            <tr>
                                <td class="font-weight-bold text-primary"><?= h($a['emp_code']) ?></td>
                                <td><?= h($a['full_name']) ?></td>
                                <td><?= $a['gender'] === 'Male' ? 'Nam' : 'Nữ' ?></td>
                                <td><?= date('d/m/Y', strtotime($a['dob'])) ?></td>
                                <td><span class="badge badge-warning"><?= $a['current_age'] ?> tuổi</span></td>
                                <td>
                                    <?php 
                                        $targetMonths = $a['gender'] === 'Male' ? 60*12 + 6 : 55*12 + 8;
                                        $remaining = $targetMonths - $a['age_months'];
                                        if ($remaining <= 0) {
                                            echo '<span class="text-danger font-weight-bold">Đã đến hạn hưu</span>';
                                        } else {
                                            echo "<span class='text-warning'>Còn ~ $remaining tháng</span>";
                                        }
                                    ?>
                                </td>
                                <td>
                                    <a href="<?= BASE_URL ?>/employee/offboard/<?= $a['id'] ?>" class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-sign-out-alt"></i> Lập BB Bàn Giao Hưu Trí
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
