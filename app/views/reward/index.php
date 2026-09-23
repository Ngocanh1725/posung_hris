<?php
/**
 * ============================================================
 *  View: reward/index.php (Upgraded)
 *  Quản lý Khen thưởng, Kỷ luật & HSE Blacklist
 *  - Bộ lọc tìm kiếm đa tiêu chí
 *  - Form thêm mới đầy đủ trường
 *  - Danh sách với nút phê duyệt, in QĐ
 * ============================================================
 */

$rewardForms = [
    'Bằng khen'             => 'Bằng khen',
    'Giấy khen'             => 'Giấy khen',
    'Tiền thưởng'           => 'Tiền thưởng',
    'Tăng lương trước hạn'  => 'Tăng lương trước hạn',
    'Thưởng hiện vật'       => 'Thưởng hiện vật',
    'Khen thưởng đột xuất'  => 'Khen thưởng đột xuất',
];

$disciplineForms = [
    'Nhắc nhở'        => 'Nhắc nhở',
    'Khiển trách'     => 'Khiển trách',
    'Cảnh cáo'        => 'Cảnh cáo',
    'Hạ bậc lương'    => 'Hạ bậc lương',
    'Cách chức'       => 'Cách chức',
    'Đình chỉ công tác' => 'Đình chỉ công tác',
    'Buộc thôi việc'  => 'Buộc thôi việc',
];

$authorityLevels = [
    'Giám đốc Công ty'    => 'Giám đốc Công ty',
    'Phó Giám đốc'        => 'Phó Giám đốc',
    'Trưởng phòng HC-NS'  => 'Trưởng phòng HC-NS',
    'Trưởng Ban ĐH Dự án' => 'Trưởng Ban ĐH Dự án',
    'Trưởng phòng'         => 'Trưởng phòng',
];
?>

<!-- ═══════════ BỘ LỌC TÌM KIẾM ═══════════ -->
<div class="panel mb-4">
    <div class="panel-header">
        <h3><i class="fas fa-filter"></i> Bộ lọc & Tìm kiếm</h3>
    </div>
    <div class="panel-body">
        <form method="GET" action="<?= BASE_URL ?>/reward/index">
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:12px; align-items:end;">
                <!-- Từ khóa -->
                <div class="form-group">
                    <label class="form-label-sm">Từ khóa</label>
                    <input type="text" name="search" class="form-control" 
                           value="<?= h($filters['search'] ?? '') ?>" 
                           placeholder="Tên, mã NV, số QĐ...">
                </div>
                <!-- Loại -->
                <div class="form-group">
                    <label class="form-label-sm">Phân loại</label>
                    <select name="type" class="form-control">
                        <option value="">-- Tất cả --</option>
                        <option value="Reward" <?= ($filters['type'] ?? '') === 'Reward' ? 'selected' : '' ?>>Khen thưởng</option>
                        <option value="Discipline" <?= ($filters['type'] ?? '') === 'Discipline' ? 'selected' : '' ?>>Kỷ luật</option>
                    </select>
                </div>
                <!-- Trạng thái -->
                <div class="form-group">
                    <label class="form-label-sm">Trạng thái QĐ</label>
                    <select name="status" class="form-control">
                        <option value="">-- Tất cả --</option>
                        <option value="Draft" <?= ($filters['status'] ?? '') === 'Draft' ? 'selected' : '' ?>>Nháp</option>
                        <option value="Pending" <?= ($filters['status'] ?? '') === 'Pending' ? 'selected' : '' ?>>Chờ duyệt</option>
                        <option value="Approved" <?= ($filters['status'] ?? '') === 'Approved' ? 'selected' : '' ?>>Đã duyệt</option>
                        <option value="Rejected" <?= ($filters['status'] ?? '') === 'Rejected' ? 'selected' : '' ?>>Từ chối</option>
                    </select>
                </div>
                <!-- Phòng ban -->
                <div class="form-group">
                    <label class="form-label-sm">Phòng ban</label>
                    <select name="department_id" class="form-control">
                        <option value="">-- Tất cả --</option>
                        <?php foreach($departments as $dept): ?>
                            <option value="<?= $dept['id'] ?>" <?= ($filters['department_id'] ?? 0) == $dept['id'] ? 'selected' : '' ?>>
                                <?= h($dept['dept_code'] . ' - ' . $dept['dept_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <!-- Từ ngày -->
                <div class="form-group">
                    <label class="form-label-sm">Từ ngày</label>
                    <input type="date" name="from_date" class="form-control" value="<?= h($filters['from_date'] ?? '') ?>">
                </div>
                <!-- Đến ngày -->
                <div class="form-group">
                    <label class="form-label-sm">Đến ngày</label>
                    <input type="date" name="to_date" class="form-control" value="<?= h($filters['to_date'] ?? '') ?>">
                </div>
                <!-- Nút lọc -->
                <div class="form-group" style="display:flex; gap:8px;">
                    <button type="submit" class="btn btn-primary" style="flex:1;">
                        <i class="fas fa-search"></i> Lọc
                    </button>
                    <a href="<?= BASE_URL ?>/reward" class="btn btn-ghost" title="Xóa bộ lọc">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </div>
            <!-- Checkbox HSE -->
            <div style="margin-top:10px;">
                <label style="cursor:pointer; color:var(--danger); font-weight:600; font-size:0.85rem;">
                    <input type="checkbox" name="safety_only" value="1" <?= !empty($filters['safety_only']) ? 'checked' : '' ?> onchange="this.form.submit()">
                    <i class="fas fa-skull-crossbones"></i> Chỉ hiển thị Vi phạm HSE (Blacklist)
                </label>
            </div>
        </form>
    </div>
</div>

<div class="row">
    <!-- ═══════════ FORM THÊM MỚI ═══════════ -->
    <div class="col-md-4">
        <div class="panel mb-4">
            <div class="panel-header">
                <h3><i class="fas fa-plus-circle"></i> Thêm Quyết Định</h3>
            </div>
            <div class="panel-body">
                <form action="<?= BASE_URL ?>/reward/store" method="POST">
                    <input type="hidden" name="_csrf_token" value="<?= Session::generateCsrfToken() ?>">
                    <!-- Nhân sự -->
                    <div class="form-group mb-3">
                        <label>Nhân sự <span class="text-danger">*</span></label>
                        <select name="employee_id" class="form-control" required>
                            <option value="">-- Chọn nhân sự --</option>
                            <?php foreach($employees as $emp): ?>
                                <option value="<?= $emp->id ?>"><?= h($emp->emp_code) ?> - <?= h($emp->full_name) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Phòng ban & Dự án -->
                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label>Phòng ban</label>
                            <select name="department_id" class="form-control">
                                <option value="">-- Tự động --</option>
                                <?php foreach($departments as $dept): ?>
                                    <option value="<?= $dept['id'] ?>"><?= h($dept['dept_code']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label>Dự án</label>
                            <select name="project_id" class="form-control">
                                <option value="">-- Tự động --</option>
                                <?php foreach($projects as $proj): ?>
                                    <option value="<?= $proj->id ?>"><?= h($proj->project_code ?? $proj->project_name) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Loại quyết định -->
                    <div class="form-group mb-3">
                        <label>Loại quyết định <span class="text-danger">*</span></label>
                        <select name="type" class="form-control" required onchange="toggleRewardType(this.value)">
                            <option value="Reward">Khen thưởng (Reward)</option>
                            <option value="Discipline">Kỷ luật (Discipline)</option>
                        </select>
                    </div>

                    <!-- Hình thức Khen thưởng -->
                    <div class="form-group mb-3" id="rewardFormGroup">
                        <label>Hình thức Khen thưởng</label>
                        <select name="reward_form" class="form-control">
                            <option value="">-- Chọn hình thức --</option>
                            <?php foreach($rewardForms as $key => $label): ?>
                                <option value="<?= $key ?>"><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Hình thức Kỷ luật -->
                    <div class="form-group mb-3" id="disciplineFormGroup" style="display:none;">
                        <label>Hình thức Kỷ luật</label>
                        <select name="discipline_form" class="form-control">
                            <option value="">-- Chọn hình thức --</option>
                            <?php foreach($disciplineForms as $key => $label): ?>
                                <option value="<?= $key ?>"><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Checkbox Vi phạm HSE -->
                    <div class="form-group mb-3" id="safetyCheck" style="display:none;">
                        <div style="padding:10px; border:1px solid var(--danger); border-radius:8px; background:rgba(239,68,68,0.05);">
                            <label style="cursor:pointer; color:var(--danger); font-weight:600;">
                                <input type="checkbox" id="is_safety" name="is_safety_violation" value="1">
                                <i class="fas fa-exclamation-triangle"></i> Vi phạm An toàn (HSE Blacklist)
                            </label>
                            <small style="display:block; margin-top:4px; color:var(--text-muted); font-size:0.8rem;">
                                Nhân viên sẽ bị khóa vĩnh viễn (Blacklisted) nếu đánh dấu.
                            </small>
                        </div>
                    </div>

                    <!-- Số QĐ & Ngày QĐ -->
                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label>Số Quyết định</label>
                            <input type="text" name="decision_number" class="form-control" placeholder="QĐ-XXX">
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label>Ngày QĐ</label>
                            <input type="date" name="decision_date" class="form-control" value="<?= date('Y-m-d') ?>">
                        </div>
                    </div>

                    <!-- Tiêu đề -->
                    <div class="form-group mb-3">
                        <label>Tiêu đề / Nội dung vắn tắt <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" required placeholder="VD: Hoàn thành xuất sắc dự án Amkor">
                    </div>
                    
                    <!-- Số tiền -->
                    <div class="form-group mb-3">
                        <label>Số tiền (VNĐ)</label>
                        <input type="number" name="amount" class="form-control" value="0">
                    </div>

                    <!-- Cấp ra QĐ -->
                    <div class="form-group mb-3">
                        <label>Cấp ra Quyết định</label>
                        <select name="authority_level" class="form-control">
                            <option value="">-- Chọn --</option>
                            <?php foreach($authorityLevels as $key => $label): ?>
                                <option value="<?= $key ?>"><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Người đề xuất -->
                    <div class="form-group mb-3">
                        <label>Người đề xuất</label>
                        <input type="text" name="proposed_by" class="form-control" placeholder="Họ tên người đề xuất">
                    </div>

                    <!-- Trạng thái QĐ -->
                    <div class="form-group mb-3">
                        <label>Trạng thái QĐ</label>
                        <select name="decision_status" class="form-control">
                            <option value="Approved">Đã duyệt (Approved)</option>
                            <option value="Pending">Chờ duyệt (Pending)</option>
                            <option value="Draft">Nháp (Draft)</option>
                        </select>
                    </div>

                    <!-- Lý do chi tiết -->
                    <div class="form-group mb-4">
                        <label>Lý do / Chi tiết</label>
                        <textarea name="reason" class="form-control" rows="3" placeholder="Mô tả chi tiết lý do khen thưởng hoặc kỷ luật..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-save"></i> Lưu Quyết Định
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- ═══════════ DANH SÁCH ═══════════ -->
    <div class="col-md-8">
        <div class="panel mb-4">
            <div class="panel-header" style="display:flex; justify-content:space-between; align-items:center;">
                <h3><i class="fas fa-history"></i> Lịch sử Khen thưởng / Kỷ luật (<?= count($records) ?> bản ghi)</h3>
                <a href="<?= BASE_URL ?>/reward/statistics" class="btn btn-ghost" style="font-size:0.85rem;">
                    <i class="fas fa-chart-bar"></i> Thống kê
                </a>
            </div>
            <div class="panel-body p-0">
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th style="width:90px;">Ngày QĐ</th>
                                <th>Nhân sự</th>
                                <th>Phòng ban</th>
                                <th>Phân loại</th>
                                <th>Hình thức</th>
                                <th>Nội dung</th>
                                <th style="text-align:right;">Số tiền</th>
                                <th>Trạng thái</th>
                                <th style="width:100px;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($records)): ?>
                                <tr><td colspan="9" style="text-align:center; padding:24px; color:var(--text-muted);">
                                    <i class="fas fa-inbox" style="font-size:2rem; display:block; margin-bottom:8px;"></i>
                                    Chưa có dữ liệu khen thưởng / kỷ luật
                                </td></tr>
                            <?php else: ?>
                                <?php foreach ($records as $r): ?>
                                    <tr class="<?= $r['is_safety_violation'] ? 'row-danger' : '' ?>">
                                        <td>
                                            <?= $r['decision_date'] ? date('d/m/Y', strtotime($r['decision_date'])) : '-' ?>
                                            <br><small style="color:var(--text-muted);"><?= h($r['decision_number'] ?? '') ?></small>
                                        </td>
                                        <td>
                                            <a href="<?= BASE_URL ?>/employee/detail/<?= $r['employee_id'] ?>" style="font-weight:600;">
                                                <?= h($r['full_name']) ?>
                                            </a>
                                            <br><small style="color:var(--text-muted);"><?= h($r['emp_code']) ?></small>
                                        </td>
                                        <td>
                                            <small><?= h($r['dept_name'] ?? '-') ?></small>
                                            <?php if (!empty($r['project_name'])): ?>
                                                <br><small style="color:var(--text-muted);"><?= h($r['project_name']) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if($r['type'] === 'Reward'): ?>
                                                <span class="badge badge-active">Khen thưởng</span>
                                            <?php else: ?>
                                                <span class="badge badge-resigned">Kỷ luật</span>
                                                <?php if($r['is_safety_violation']): ?>
                                                    <br><span class="badge" style="background:#b91c1c; color:#fff; font-weight:bold; margin-top:4px; box-shadow: 0 0 5px #b91c1c; padding: 4px 8px; border-radius: 8px;">
                                                        <i class="fas fa-skull-crossbones"></i> Vi phạm HSE
                                                    </span>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <small>
                                            <?php if ($r['type'] === 'Reward' && !empty($r['reward_form'])): ?>
                                                <?= h($r['reward_form']) ?>
                                            <?php elseif ($r['type'] === 'Discipline' && !empty($r['discipline_form'])): ?>
                                                <?= h($r['discipline_form']) ?>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                            </small>
                                        </td>
                                        <td>
                                            <?= h($r['title']) ?>
                                            <?php if (!empty($r['authority_level'])): ?>
                                                <br><small style="color:var(--text-muted);">Cấp QĐ: <?= h($r['authority_level']) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td style="text-align:right; font-weight:600;">
                                            <?php if ((float)$r['amount'] > 0): ?>
                                                <?= number_format($r['amount'], 0, ',', '.') ?> đ
                                            <?php else: ?>
                                                <span style="color:var(--text-muted);">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php
                                            $statusMap = [
                                                'Draft'    => ['Nháp', 'badge-muted'],
                                                'Pending'  => ['Chờ duyệt', 'badge-probation'],
                                                'Approved' => ['Đã duyệt', 'badge-active'],
                                                'Rejected' => ['Từ chối', 'badge-resigned'],
                                            ];
                                            $st = $statusMap[$r['status'] ?? 'Approved'] ?? ['N/A', ''];
                                            ?>
                                            <span class="badge <?= $st[1] ?>"><?= $st[0] ?></span>
                                            <?php if ($r['emp_status'] === 'Blacklisted'): ?>
                                                <br><span class="badge badge-resigned" style="margin-top:4px;">Blacklisted</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div style="display:flex; gap:4px; flex-wrap:wrap;">
                                                <!-- In QĐ -->
                                                <a href="<?= BASE_URL ?>/reward/printDecision/<?= $r['id'] ?>" 
                                                   target="_blank" class="btn-icon" title="In Quyết định">
                                                    <i class="fas fa-print"></i>
                                                </a>
                                                <?php if (in_array($r['status'] ?? 'Approved', ['Draft','Pending'])): ?>
                                                    <!-- Phê duyệt -->
                                                    <a href="<?= BASE_URL ?>/reward/approve/<?= $r['id'] ?>" 
                                                       class="btn-icon btn-icon-success" title="Phê duyệt"
                                                       onclick="return confirm('Xác nhận PHÊ DUYỆT quyết định này?')">
                                                        <i class="fas fa-check"></i>
                                                    </a>
                                                    <!-- Từ chối -->
                                                    <a href="<?= BASE_URL ?>/reward/reject/<?= $r['id'] ?>" 
                                                       class="btn-icon btn-icon-danger" title="Từ chối"
                                                       onclick="return confirm('Xác nhận TỪ CHỐI quyết định này?')">
                                                        <i class="fas fa-times"></i>
                                                    </a>
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
    </div>
</div>

<style>
.form-label-sm { display:block; margin-bottom:4px; font-weight:500; font-size:0.8rem; color:var(--text-secondary); }
.row-danger { background: rgba(239,68,68,0.04) !important; }
.badge-muted { background: rgba(148,163,184,0.15); color: #94a3b8; }
.btn-icon { display:inline-flex; align-items:center; justify-content:center; width:30px; height:30px; border-radius:6px; border:1px solid rgba(255,255,255,0.08); background:rgba(255,255,255,0.03); color:var(--text-secondary); cursor:pointer; text-decoration:none; font-size:0.8rem; transition:all 0.2s; }
.btn-icon:hover { background:var(--primary); color:#fff; border-color:var(--primary); }
.btn-icon-success:hover { background:var(--success); border-color:var(--success); }
.btn-icon-danger:hover { background:var(--danger); border-color:var(--danger); }
</style>

<script>
function toggleRewardType(type) {
    document.getElementById('rewardFormGroup').style.display = (type === 'Reward') ? 'block' : 'none';
    document.getElementById('disciplineFormGroup').style.display = (type === 'Discipline') ? 'block' : 'none';
    document.getElementById('safetyCheck').style.display = (type === 'Discipline') ? 'block' : 'none';
    if (type !== 'Discipline') {
        document.getElementById('is_safety').checked = false;
    }
}
</script>
