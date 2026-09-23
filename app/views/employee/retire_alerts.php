<?php
/**
 * ============================================================
 *  View: employee/retire_alerts.php
 *  Danh sách cảnh báo nhân sự sắp đến tuổi Hưu trí
 * ============================================================
 */
?>

<div class="panel mb-4" style="border: 1px solid #f59e0b;">
    <div class="panel-header" style="background: rgba(245, 158, 11, 0.1); color: #b45309;">
        <h3><i class="fas fa-user-clock"></i> Cảnh báo Hưu trí (Trong vòng 12 tháng)</h3>
    </div>
    <div class="panel-body p-0">
        <div style="padding: 15px; background: #fffbeb; border-bottom: 1px solid #fde68a;">
            <small style="color: #92400e;">
                <i class="fas fa-info-circle"></i> Hệ thống đang tính theo lộ trình Bộ luật Lao động: Nam tiến tới 62 tuổi, Nữ tiến tới 60 tuổi. Các cảnh báo này giúp doanh nghiệp chuẩn bị nguồn nhân sự kế cận.
            </small>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th style="width: 100px;">Mã NV</th>
                        <th>Họ tên</th>
                        <th>Phân loại</th>
                        <th>Ngày sinh</th>
                        <th style="width: 300px;">Tiến độ Hưu trí</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($alerts)): ?>
                        <tr><td colspan="6" style="text-align:center; padding: 24px; color: var(--text-muted);">
                            <i class="fas fa-calendar-check" style="font-size: 2rem; display: block; margin-bottom: 8px;"></i>
                            Hiện không có nhân sự nào sắp đến tuổi nghỉ hưu.
                        </td></tr>
                    <?php else: ?>
                        <?php foreach ($alerts as $a): ?>
                            <tr>
                                <td style="font-weight: bold; color: var(--primary);"><?= h($a['emp_code']) ?></td>
                                <td>
                                    <div style="font-weight: 600;"><?= h($a['full_name']) ?></div>
                                    <small style="color: var(--text-muted);"><?= $a['current_age'] ?> tuổi</small>
                                </td>
                                <td>
                                    <?php if ($a['gender'] === 'Male'): ?>
                                        <span class="badge badge-info"><i class="fas fa-mars"></i> Nam (62)</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger"><i class="fas fa-venus"></i> Nữ (60)</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('d/m/Y', strtotime($a['dob'])) ?></td>
                                <td>
                                    <?php 
                                        $remaining = (int)$a['months_to_retire'];
                                        // Tính % tiến độ (Lấy mốc 12 tháng làm 100% cảnh báo)
                                        // 12 tháng -> 0% bar (Mới vô vùng cảnh báo)
                                        // 0 tháng -> 100% bar (Đã đến hạn)
                                        // -X tháng -> 100% bar (Quá hạn)
                                        $percent = 0;
                                        if ($remaining <= 0) {
                                            $percent = 100;
                                            $barColor = '#ef4444'; // Red
                                            $text = "Đã đến hạn hưu";
                                        } else {
                                            $percent = ((12 - $remaining) / 12) * 100;
                                            $barColor = $remaining <= 3 ? '#f97316' : '#f59e0b'; // Orange/Yellow
                                            $text = "Còn ~ $remaining tháng";
                                        }
                                    ?>
                                    <div style="display: flex; justify-content: space-between; margin-bottom: 4px; font-size: 0.85rem; font-weight: 600;">
                                        <span style="color: <?= $barColor ?>;"><?= $text ?></span>
                                    </div>
                                    <div style="width: 100%; background: #e2e8f0; border-radius: 4px; height: 8px; overflow: hidden;">
                                        <div style="height: 100%; width: <?= $percent ?>%; background: <?= $barColor ?>; transition: width 0.3s;"></div>
                                    </div>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                                        <a href="<?= BASE_URL ?>/employee/detail/<?= $a['id'] ?>" class="btn btn-sm btn-ghost" title="Lập kế hoạch nhân sự kế thừa">
                                            <i class="fas fa-users-cog"></i> Kế hoạch
                                        </a>
                                        <a href="<?= BASE_URL ?>/employee/offboard/<?= $a['id'] ?>" class="btn btn-sm" style="background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5;">
                                            <i class="fas fa-sign-out-alt"></i> Bàn giao Hưu trí
                                        </a>
                                    </div>
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
.badge-info { background: rgba(14, 165, 233, 0.1); color: #0ea5e9; }
.badge-danger { background: rgba(244, 63, 94, 0.1); color: #f43f5e; }
</style>
