<?php
/**
 * View: audit/detail.php – Chi tiết Bản ghi Nhật ký Hoạt động
 */

$oldVal = $log->old_decoded ?? [];
$newVal = $log->new_decoded ?? [];
$allKeys = Array_unique(array_merge(array_keys($oldVal ?: []), array_keys($newVal ?: [])));
?>

<!-- BREADCRUMB -->
<div class="breadcrumb-nav mb-3" style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: var(--text-muted);">
    <a href="<?= BASE_URL ?>/audit" style="color: var(--primary); text-decoration: none;">
        <i class="fas fa-clock-rotate-left"></i> Nhật ký Hoạt động
    </a>
    <i class="fas fa-chevron-right" style="font-size: 10px;"></i>
    <span>Chi tiết bản ghi #<?= $log->id ?></span>
</div>

<div class="card mb-4" style="border: 1px solid var(--border); box-shadow: 0 4px 18px rgba(0,0,0,0.04);">
    <div class="card-header d-flex justify-content-between align-items-center" style="background: var(--bg-hover); padding: 14px 20px; border-bottom: 1px solid var(--border);">
        <h5 style="margin: 0; font-size: 16px; font-weight: 700; color: var(--text);">
            <i class="fas fa-info-circle text-primary"></i> Chi Tiết Nhật Ký Hoạt Động #<?= $log->id ?>
        </h5>
        <a href="<?= BASE_URL ?>/audit" class="btn btn-sm btn-ghost" style="border: 1px solid var(--border);">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
    </div>
    <div class="card-body p-4">
        <!-- META INFO -->
        <div class="row g-3 mb-4 p-3 bg-light rounded" style="font-size: 13px;">
            <div class="col-md-3">
                <span class="text-muted">Người thực hiện:</span><br>
                <strong><?= htmlspecialchars($log->user_name ?? 'System') ?></strong>
            </div>
            <div class="col-md-3">
                <span class="text-muted">Thời gian:</span><br>
                <strong><?= date('d/m/Y H:i:s', strtotime($log->created_at)) ?></strong>
            </div>
            <div class="col-md-3">
                <span class="text-muted">Hành động:</span><br>
                <span class="badge bg-primary"><?= strtoupper($log->action) ?></span>
            </div>
            <div class="col-md-3">
                <span class="text-muted">Phân hệ:</span><br>
                <strong><?= strtoupper($log->module) ?></strong> <?= $log->record_id ? '#' . $log->record_id : '' ?>
            </div>
            <div class="col-md-6">
                <span class="text-muted">Mô tả:</span><br>
                <em>"<?= htmlspecialchars($log->description) ?>"</em>
            </div>
            <div class="col-md-6">
                <span class="text-muted">Địa chỉ IP & Thiết bị:</span><br>
                <code><?= htmlspecialchars($log->ip_address) ?></code> | <small class="text-muted"><?= htmlspecialchars($log->user_agent) ?></small>
            </div>
        </div>

        <!-- DIFF TABLE -->
        <h6 style="font-weight: 700; margin-bottom: 10px;">
            <i class="fas fa-code-compare text-primary"></i> Biến Động Dữ Liệu Chi Tiết (Data Diff)
        </h6>

        <?php if (empty($allKeys)): ?>
            <div class="alert alert-secondary py-3 text-center" style="font-size: 13px;">
                Thao tác này không lưu biến động dữ liệu chi tiết dạng JSON (như đăng nhập, đăng xuất, truy cập).
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered align-middle" style="font-size: 13px;">
                    <thead style="background: var(--bg-hover); text-transform: uppercase; font-size: 11.5px;">
                        <tr>
                            <th style="width: 180px;">Thuộc tính (Field)</th>
                            <th style="width: 45%;">Giá trị cũ (Trước thay đổi)</th>
                            <th style="width: 45%;">Giá trị mới (Sau thay đổi)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($allKeys as $k): ?>
                            <?php
                                $o = $oldVal[$k] ?? null;
                                $n = $newVal[$k] ?? null;
                                $oStr = $o !== null ? (is_array($o) ? json_encode($o, JSON_UNESCAPED_UNICODE) : (string)$o) : null;
                                $nStr = $n !== null ? (is_array($n) ? json_encode($n, JSON_UNESCAPED_UNICODE) : (string)$n) : null;
                                $isChanged = ($oStr !== $nStr);
                            ?>
                            <tr style="<?= $isChanged ? 'background: rgba(245, 158, 11, 0.04);' : '' ?>">
                                <td><strong><?= htmlspecialchars($k) ?></strong></td>
                                <td style="<?= ($isChanged && $oStr !== null) ? 'background: #fee2e2; color: #b91c1c; font-weight: 500;' : '' ?>">
                                    <?= $oStr !== null ? htmlspecialchars($oStr) : '<span class="text-muted">NULL</span>' ?>
                                </td>
                                <td style="<?= ($isChanged && $nStr !== null) ? 'background: #dcfce7; color: #15803d; font-weight: 600;' : '' ?>">
                                    <?= $nStr !== null ? htmlspecialchars($nStr) : '<span class="text-muted">NULL</span>' ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
