<?php
/** View: recruitment/candidates.php – Danh sách ứng viên */
$candidateStatusLabels = [
    'New' => ['Mới','badge-probation'], 'Screening' => ['Sàng lọc','badge-probation'],
    'Interview_Scheduled' => ['Hẹn PV','badge-probation'], 'Interviewed' => ['Đã PV','badge-active'],
    'Offer' => ['Đã Offer','badge-active'], 'Hired' => ['Đã tuyển','badge-active'],
    'Rejected' => ['Từ chối','badge-resigned'], 'Withdrawn' => ['Rút hồ sơ','badge-resigned'],
];
?>

<!-- Bộ lọc -->
<div class="panel mb-4">
    <div class="panel-header"><h3><i class="fas fa-filter"></i> Lọc Ứng viên</h3></div>
    <div class="panel-body">
        <form method="GET" action="<?= BASE_URL ?>/recruitment/candidates">
            <div style="display:flex; gap:12px; align-items:end; flex-wrap:wrap;">
                <div class="form-group" style="flex:1; min-width:200px;">
                    <label class="form-label-sm">Từ khóa</label>
                    <input type="text" name="search" class="form-control" value="<?= htmlspecialchars($filters['search'] ?? '') ?>" placeholder="Tên, SĐT, email...">
                </div>
                <div class="form-group" style="min-width:180px;">
                    <label class="form-label-sm">Yêu cầu TD</label>
                    <select name="request_id" class="form-control">
                        <option value="">-- Tất cả --</option>
                        <?php foreach($requests as $rq): ?>
                            <option value="<?= $rq->id ?>" <?= ($filters['request_id'] ?? 0) == $rq->id ? 'selected' : '' ?>><?= htmlspecialchars($rq->request_code) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" style="min-width:140px;">
                    <label class="form-label-sm">Trạng thái</label>
                    <select name="status" class="form-control">
                        <option value="">-- Tất cả --</option>
                        <?php foreach($candidateStatusLabels as $k => $v): ?>
                            <option value="<?= $k ?>" <?= ($filters['status'] ?? '') === $k ? 'selected' : '' ?>><?= $v[0] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Lọc</button>
                <a href="<?= BASE_URL ?>/recruitment/candidates" class="btn btn-ghost"><i class="fas fa-times"></i></a>
            </div>
        </form>
    </div>
</div>

<!-- Action bar -->
<div style="display:flex; justify-content:space-between; margin-bottom:16px;">
    <a href="<?= BASE_URL ?>/recruitment/addCandidate<?= !empty($filters['request_id']) ? '?request_id='.$filters['request_id'] : '' ?>" class="btn btn-primary">
        <i class="fas fa-user-plus"></i> Thêm Ứng viên
    </a>
    <a href="<?= BASE_URL ?>/recruitment" class="btn btn-ghost"><i class="fas fa-arrow-left"></i> Quay lại YCTD</a>
</div>

<!-- Danh sách -->
<div class="panel">
    <div class="panel-header"><h3><i class="fas fa-users"></i> Danh sách Ứng viên (<?= count($candidates) ?>)</h3></div>
    <div class="panel-body p-0">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Họ tên</th>
                        <th>SĐT / Email</th>
                        <th>Học vấn</th>
                        <th>Kinh nghiệm</th>
                        <th>Mã YCTD</th>
                        <th>Nguồn</th>
                        <th>Điểm PV</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($candidates)): ?>
                        <tr><td colspan="9" style="text-align:center; padding:24px; color:var(--text-muted);">
                            <i class="fas fa-inbox" style="font-size:2rem; display:block; margin-bottom:8px;"></i> Chưa có ứng viên
                        </td></tr>
                    <?php else: ?>
                        <?php foreach ($candidates as $c): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($c->full_name) ?></strong>
                                    <?php if ($c->gender): ?>
                                        <br><small style="color:var(--text-muted);"><?= $c->gender === 'Male' ? 'Nam' : ($c->gender === 'Female' ? 'Nữ' : 'Khác') ?></small>
                                    <?php endif; ?>
                                    <?php if ($c->dob): ?>
                                        <small> · <?= date('Y') - date('Y', strtotime($c->dob)) ?> tuổi</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($c->phone): ?><small><i class="fas fa-phone"></i> <?= htmlspecialchars($c->phone) ?></small><br><?php endif; ?>
                                    <?php if ($c->email): ?><small><i class="fas fa-envelope"></i> <?= htmlspecialchars($c->email) ?></small><?php endif; ?>
                                </td>
                                <td><small><?= htmlspecialchars($c->highest_degree ?? '-') ?><br><?= htmlspecialchars($c->major ?? '') ?></small></td>
                                <td style="text-align:center;"><?= $c->years_experience ?> năm</td>
                                <td><small><?= htmlspecialchars($c->request_code ?? '-') ?></small></td>
                                <td><small><?= htmlspecialchars($c->source ?? '-') ?></small></td>
                                <td style="text-align:center;">
                                    <?php if ($c->interview_score): ?>
                                        <strong><?= $c->interview_score ?></strong>/10
                                    <?php else: ?>
                                        <span style="color:var(--text-muted);">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php $st = $candidateStatusLabels[$c->status] ?? ['N/A','']; ?>
                                    <span class="badge <?= $st[1] ?>"><?= $st[0] ?></span>
                                </td>
                                <td>
                                    <div style="display:flex; gap:4px; flex-wrap:wrap;">
                                        <a href="<?= BASE_URL ?>/recruitment/interview/<?= $c->id ?>" class="btn-icon" title="Phỏng vấn / Cập nhật"><i class="fas fa-edit"></i></a>
                                        <?php if ($c->status === 'Offer'): ?>
                                            <a href="<?= BASE_URL ?>/recruitment/hire/<?= $c->id ?>" class="btn-icon btn-icon-success" title="Tuyển dụng (Hire)" onclick="return confirm('Xác nhận TUYỂN ứng viên này? Hệ thống sẽ tự động tạo hồ sơ nhân viên mới.')"><i class="fas fa-user-check"></i></a>
                                            <a href="<?= BASE_URL ?>/recruitment/printOffer/<?= $c->id ?>" target="_blank" class="btn-icon" title="In thư mời"><i class="fas fa-print"></i></a>
                                        <?php endif; ?>
                                        <?php if ($c->cv_file_path): ?>
                                            <a href="<?= BASE_URL ?>/<?= $c->cv_file_path ?>" target="_blank" class="btn-icon" title="Xem CV"><i class="fas fa-file-pdf"></i></a>
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
