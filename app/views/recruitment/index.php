<?php
/**
 * View: recruitment/index.php – Dashboard Tuyển dụng
 */
$statusLabels = [
    'Draft' => ['Nháp','badge-muted'], 'Pending' => ['Chờ duyệt','badge-probation'],
    'Approved' => ['Đã duyệt','badge-active'], 'In_Progress' => ['Đang tuyển','badge-probation'],
    'Closed' => ['Đã đóng','badge-resigned'], 'Cancelled' => ['Hủy','badge-resigned'],
];
$reasonLabels = ['Replacement'=>'Thay thế','Expansion'=>'Mở rộng','New_Position'=>'Vị trí mới','Seasonal'=>'Thời vụ'];
$urgencyLabels = ['Normal'=>['Bình thường',''], 'Urgent'=>['Gấp','badge-probation'], 'Critical'=>['Rất gấp','badge-resigned']];
?>

<!-- KPI Cards -->
<div class="kpi-row" style="margin-bottom:24px;">
    <div class="kpi-card">
        <div class="kpi-icon" style="background:linear-gradient(135deg,#6366f1,#4f46e5);"><i class="fas fa-clipboard-list"></i></div>
        <div class="kpi-body">
            <div class="kpi-value"><?= array_sum($statusCounts) ?></div>
            <div class="kpi-label">Tổng YCTD</div>
        </div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon" style="background:linear-gradient(135deg,#f59e0b,#d97706);"><i class="fas fa-hourglass-half"></i></div>
        <div class="kpi-body">
            <div class="kpi-value"><?= ($statusCounts['Pending'] ?? 0) + ($statusCounts['In_Progress'] ?? 0) ?></div>
            <div class="kpi-label">Đang xử lý</div>
        </div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon" style="background:linear-gradient(135deg,#10b981,#059669);"><i class="fas fa-user-check"></i></div>
        <div class="kpi-body">
            <div class="kpi-value"><?= $statusCounts['Closed'] ?? 0 ?></div>
            <div class="kpi-label">Đã hoàn tất</div>
        </div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon" style="background:linear-gradient(135deg,#ec4899,#db2777);"><i class="fas fa-user-tie"></i></div>
        <div class="kpi-body">
            <div class="kpi-value"><?= $newCandidates ?></div>
            <div class="kpi-label">Ứng viên mới</div>
        </div>
    </div>
</div>

<!-- Action Buttons -->
<div style="display:flex; gap:12px; margin-bottom:20px; flex-wrap:wrap;">
    <a href="<?= BASE_URL ?>/recruitment/createRequest" class="btn btn-primary">
        <i class="fas fa-plus"></i> Tạo Yêu cầu Tuyển dụng
    </a>
    <a href="<?= BASE_URL ?>/recruitment/addCandidate" class="btn btn-ghost">
        <i class="fas fa-user-plus"></i> Thêm Ứng viên
    </a>
    <a href="<?= BASE_URL ?>/recruitment/candidates" class="btn btn-ghost">
        <i class="fas fa-users"></i> DS Ứng viên
    </a>
</div>

<!-- Bộ lọc -->
<div class="panel mb-4">
    <div class="panel-header"><h3><i class="fas fa-filter"></i> Lọc Yêu cầu Tuyển dụng</h3></div>
    <div class="panel-body">
        <form method="GET" action="<?= BASE_URL ?>/recruitment">
            <div style="display:flex; gap:12px; align-items:end; flex-wrap:wrap;">
                <div class="form-group" style="flex:1; min-width:180px;">
                    <label class="form-label-sm">Từ khóa</label>
                    <input type="text" name="search" class="form-control" value="<?= h($searchFilter) ?>" placeholder="Mã YCTD, mô tả...">
                </div>
                <div class="form-group" style="min-width:150px;">
                    <label class="form-label-sm">Trạng thái</label>
                    <select name="status" class="form-control">
                        <option value="">-- Tất cả --</option>
                        <?php foreach($statusLabels as $k => $v): ?>
                            <option value="<?= $k ?>" <?= $currentStatus === $k ? 'selected' : '' ?>><?= $v[0] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Lọc</button>
                <a href="<?= BASE_URL ?>/recruitment" class="btn btn-ghost"><i class="fas fa-times"></i></a>
            </div>
        </form>
    </div>
</div>

<!-- Danh sách YCTD -->
<div class="panel">
    <div class="panel-header"><h3><i class="fas fa-clipboard-list"></i> Danh sách Yêu cầu Tuyển dụng (<?= count($requests) ?>)</h3></div>
    <div class="panel-body p-0">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Mã YCTD</th>
                        <th>Phòng ban</th>
                        <th>Vị trí</th>
                        <th style="text-align:center;">SL cần</th>
                        <th style="text-align:center;">Đã tuyển</th>
                        <th style="text-align:center;">Ứng viên</th>
                        <th>Lý do</th>
                        <th>Độ gấp</th>
                        <th>Hạn tuyển</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($requests)): ?>
                        <tr><td colspan="11" style="text-align:center; padding:24px; color:var(--text-muted);">
                            <i class="fas fa-inbox" style="font-size:2rem; display:block; margin-bottom:8px;"></i> Chưa có yêu cầu tuyển dụng
                        </td></tr>
                    <?php else: ?>
                        <?php foreach ($requests as $r): ?>
                            <tr>
                                <td><strong><?= h($r->request_code) ?></strong></td>
                                <td><?= h($r->dept_name ?? '-') ?></td>
                                <td><?= h($r->pos_title ?? '-') ?></td>
                                <td style="text-align:center; font-weight:700;"><?= $r->quantity ?></td>
                                <td style="text-align:center;">
                                    <span class="badge <?= $r->hired_count >= $r->quantity ? 'badge-active' : '' ?>"><?= $r->hired_count ?>/<?= $r->quantity ?></span>
                                </td>
                                <td style="text-align:center;">
                                    <a href="<?= BASE_URL ?>/recruitment/candidates?request_id=<?= $r->id ?>" style="font-weight:600;">
                                        <?= $r->candidate_count ?? 0 ?>
                                    </a>
                                </td>
                                <td><small><?= $reasonLabels[$r->reason] ?? $r->reason ?></small></td>
                                <td>
                                    <?php $urg = $urgencyLabels[$r->urgency] ?? ['N/A','']; ?>
                                    <span class="badge <?= $urg[1] ?>"><?= $urg[0] ?></span>
                                </td>
                                <td>
                                    <?php if ($r->deadline): ?>
                                        <?= date('d/m/Y', strtotime($r->deadline)) ?>
                                        <?php if (strtotime($r->deadline) < time() && !in_array($r->status, ['Closed','Cancelled'])): ?>
                                            <br><small style="color:var(--danger);">Quá hạn!</small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span style="color:var(--text-muted);">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php $st = $statusLabels[$r->status] ?? ['N/A','']; ?>
                                    <span class="badge <?= $st[1] ?>"><?= $st[0] ?></span>
                                </td>
                                <td>
                                    <div style="display:flex; gap:4px;">
                                        <a href="<?= BASE_URL ?>/recruitment/addCandidate?request_id=<?= $r->id ?>" class="btn-icon" title="Thêm ứng viên"><i class="fas fa-user-plus"></i></a>
                                        <?php if (in_array($r->status, ['Draft','Pending'])): ?>
                                            <a href="<?= BASE_URL ?>/recruitment/approveRequest/<?= $r->id ?>" class="btn-icon btn-icon-success" title="Phê duyệt" onclick="return confirm('Phê duyệt YCTD này?')"><i class="fas fa-check"></i></a>
                                        <?php endif; ?>
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
.form-label-sm { display:block; margin-bottom:4px; font-weight:500; font-size:0.8rem; color:var(--text-secondary); }
.badge-muted { background:rgba(148,163,184,0.15); color:#94a3b8; }
.btn-icon { display:inline-flex; align-items:center; justify-content:center; width:30px; height:30px; border-radius:6px; border:1px solid rgba(255,255,255,0.08); background:rgba(255,255,255,0.03); color:var(--text-secondary); cursor:pointer; text-decoration:none; font-size:0.8rem; transition:all 0.2s; }
.btn-icon:hover { background:var(--primary); color:#fff; border-color:var(--primary); }
.btn-icon-success:hover { background:var(--success); border-color:var(--success); }
</style>
