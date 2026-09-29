<?php
/**
 * View: training/show.php – Chi tiết Khóa đào tạo & Quản lý Học viên
 */

$statusBadges = [
    'Planning'    => ['Lập kế hoạch', 'badge bg-warning text-dark', 'fas fa-calendar-alt'],
    'In_Progress' => ['Đang diễn ra',  'badge bg-primary text-white', 'fas fa-spinner fa-spin'],
    'Completed'   => ['Đã hoàn tất',   'badge bg-success text-white', 'fas fa-check-circle'],
    'Cancelled'   => ['Đã hủy',        'badge bg-secondary text-white', 'fas fa-ban'],
];

$participantStatusBadges = [
    'Registered' => ['Mới đăng ký', 'badge bg-secondary text-dark'],
    'Attending'  => ['Đang học',    'badge bg-primary text-white'],
    'Completed'  => ['Hoàn thành',  'badge bg-info text-white'],
    'Dropped'    => ['Bỏ dở',       'badge bg-danger text-white'],
];

$resultBadges = [
    'Passed'  => ['Đạt',            'badge bg-success text-white', 'fas fa-check-circle'],
    'Failed'  => ['Không đạt',      'badge bg-danger text-white',  'fas fa-times-circle'],
    'Pending' => ['Chưa đánh giá',  'badge bg-warning text-dark',  'fas fa-clock'],
];

$courseBadge = $statusBadges[$course->status] ?? ['Không rõ', 'badge bg-secondary', 'fas fa-question'];
$totalP = count($participants);
$passedP = 0;
foreach ($participants as $p) {
    if ($p['result'] === 'Passed') $passedP++;
}
$passRate = $totalP > 0 ? round(($passedP / $totalP) * 100) : 0;
?>

<!-- Breadcrumb & Top Bar -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <div style="font-size: 13px; color: var(--text-muted); margin-bottom: 4px;">
            <a href="<?= BASE_URL ?>/training" style="color: var(--text-muted); text-decoration: none;">Đào tạo & L&D</a>
            <span style="margin: 0 6px;">/</span>
            <span style="color: var(--primary); font-weight: 600;">Chi tiết khóa học</span>
        </div>
        <h2 style="font-size: 22px; font-weight: 800; color: var(--text-heading); margin: 0; display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-graduation-cap text-primary"></i> <?= h($course->course_name) ?>
            <span class="<?= $courseBadge[1] ?>" style="font-size: 12px; font-weight: 600; padding: 4px 10px; border-radius: 6px;">
                <i class="<?= $courseBadge[2] ?>"></i> <?= $courseBadge[0] ?>
            </span>
        </h2>
    </div>
    
    <div class="d-flex gap-2">
        <a href="<?= BASE_URL ?>/training" class="btn btn-ghost btn-sm">
            <i class="fas fa-arrow-left"></i> Danh sách
        </a>
        <a href="<?= BASE_URL ?>/training/edit/<?= $course->id ?>" class="btn btn-warning btn-sm" style="font-weight: 600;">
            <i class="fas fa-edit"></i> Chỉnh sửa
        </a>
        <button type="button" class="btn btn-primary btn-sm" onclick="openParticipantModal()" style="font-weight: 600; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25);">
            <i class="fas fa-user-plus"></i> Thêm học viên
        </button>
    </div>
</div>

<!-- KHỐI THÔNG TIN TỔNG QUAN KHÓA HỌC -->
<div class="panel mb-4" style="border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.04); border-radius: var(--radius-lg); overflow: hidden;">
    <div class="panel-body p-4">
        <div class="row">
            <!-- Cột trái: Chi tiết thông tin -->
            <div class="col-md-8" style="border-right: 1px solid var(--border);">
                <h4 style="font-size: 15px; font-weight: 700; color: var(--text-heading); margin-bottom: 16px;">
                    <i class="fas fa-info-circle text-primary"></i> Thông tin Tổng quan
                </h4>

                <div class="row">
                    <div class="col-sm-6 mb-3">
                        <div class="text-muted" style="font-size: 12px; font-weight: 600;">ĐƠN VỊ / GIẢNG VIÊN</div>
                        <div style="font-weight: 700; font-size: 14px; color: var(--text-heading);">
                            <?= h($course->provider ?: 'Nội bộ Công ty') ?>
                        </div>
                    </div>

                    <div class="col-sm-6 mb-3">
                        <div class="text-muted" style="font-size: 12px; font-weight: 600;">PHÒNG BAN PHỤ TRÁCH</div>
                        <div style="font-weight: 700; font-size: 14px; color: var(--text-heading);">
                            <?= h($course->dept_name ?: 'Toàn công ty') ?>
                        </div>
                    </div>

                    <div class="col-sm-6 mb-3">
                        <div class="text-muted" style="font-size: 12px; font-weight: 600;">THỜI GIAN ĐÀO TẠO</div>
                        <div style="font-weight: 700; font-size: 14px; color: var(--text-heading);">
                            <i class="far fa-calendar-alt text-primary"></i> <?= fmtDate($course->start_date) ?>
                            <?php if (!empty($course->end_date)): ?>
                                <span class="text-muted" style="font-weight: 400;">đến</span> <?= fmtDate($course->end_date) ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="col-sm-6 mb-3">
                        <div class="text-muted" style="font-size: 12px; font-weight: 600;">ĐỊA ĐIỂM / HÌNH THỨC</div>
                        <div style="font-weight: 700; font-size: 14px; color: var(--text-heading);">
                            <i class="fas fa-map-marker-alt text-danger"></i> <?= h($course->location ?: 'Trụ sở chính') ?>
                        </div>
                    </div>

                    <div class="col-sm-6 mb-3">
                        <div class="text-muted" style="font-size: 12px; font-weight: 600;">NGÂN SÁCH / CHI PHÍ</div>
                        <div style="font-weight: 800; font-size: 15px; color: var(--primary);">
                            <?= number_format((float)$course->cost) ?> <span style="font-size: 12px; font-weight: 400;">VNĐ</span>
                        </div>
                    </div>

                    <div class="col-sm-6 mb-3">
                        <div class="text-muted" style="font-size: 12px; font-weight: 600;">SỐ LƯỢNG HỌC VIÊN TỐI ĐA</div>
                        <div style="font-weight: 700; font-size: 14px; color: var(--text-heading);">
                            <?= (int)$course->max_participants > 0 ? (int)$course->max_participants . ' người' : 'Không giới hạn' ?>
                        </div>
                    </div>
                </div>

                <?php if (!empty($course->description)): ?>
                    <div style="margin-top: 10px; padding: 12px 16px; background: var(--bg-app); border-radius: 8px; font-size: 13px;">
                        <strong style="color: var(--text-heading);"><i class="fas fa-align-left text-muted"></i> Nội dung / Đề cương:</strong>
                        <div style="margin-top: 6px; color: var(--text-body); white-space: pre-line;"><?= h($course->description) ?></div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Cột phải: Thống kê học viên & Tiến độ -->
            <div class="col-md-4 d-flex flex-column justify-content-center p-4">
                <h4 style="font-size: 14px; font-weight: 700; color: var(--text-heading); margin-bottom: 16px; text-align: center;">
                    Tiến độ & Kết quả Đào tạo
                </h4>

                <div class="text-center mb-3">
                    <div style="font-size: 42px; font-weight: 900; color: var(--primary); line-height: 1;">
                        <?= $passRate ?>%
                    </div>
                    <div class="text-muted" style="font-size: 12px; margin-top: 4px;">Tỷ lệ học viên Đạt</div>
                </div>

                <!-- Progress Bar -->
                <div style="height: 10px; background: rgba(0,0,0,0.06); border-radius: 10px; overflow: hidden; margin-bottom: 20px;">
                    <div style="width: <?= $passRate ?>%; height: 100%; background: linear-gradient(90deg, #10b981, #059669); border-radius: 10px; transition: width 0.5s ease;"></div>
                </div>

                <div class="d-flex justify-content-between p-2 rounded mb-2" style="background: var(--bg-app); font-size: 13px;">
                    <span class="text-muted">Tổng học viên đã đăng ký:</span>
                    <strong class="text-dark"><?= $totalP ?></strong>
                </div>

                <div class="d-flex justify-content-between p-2 rounded mb-2" style="background: rgba(16, 185, 129, 0.08); font-size: 13px;">
                    <span class="text-success fw-bold"><i class="fas fa-check-circle"></i> Đạt (Passed):</span>
                    <strong class="text-success"><?= $passedP ?></strong>
                </div>

                <div class="d-flex justify-content-between p-2 rounded" style="background: rgba(239, 68, 68, 0.08); font-size: 13px;">
                    <span class="text-danger fw-bold"><i class="fas fa-times-circle"></i> Không đạt / Bỏ:</span>
                    <strong class="text-danger"><?= $totalP - $passedP ?></strong>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- KHỐI DANH SÁCH HỌC VIÊN -->
<div class="panel" style="border: none; box-shadow: 0 4px 20px rgba(0,0,0,0.04);">
    <div class="panel-header d-flex justify-content-between align-items-center flex-wrap gap-2" style="background: var(--bg-card); border-bottom: 1px solid var(--border);">
        <h3 style="font-size: 15px; font-weight: 700; margin: 0; color: var(--text-heading);">
            <i class="fas fa-users text-primary"></i> Danh sách Học viên tham gia (<?= $totalP ?>)
        </h3>

        <div class="d-flex gap-2 align-items-center">
            <input type="text" id="participantTableSearch" class="form-control form-control-sm" placeholder="Tìm nhanh học viên..." oninput="filterParticipantTable()" style="width: 200px;">
            <button type="button" class="btn btn-primary btn-sm" onclick="openParticipantModal()">
                <i class="fas fa-user-plus"></i> Thêm học viên
            </button>
        </div>
    </div>

    <div class="panel-body p-0">
        <?php if (empty($participants)): ?>
            <div class="text-center p-5">
                <div style="font-size: 40px; color: var(--text-muted); margin-bottom: 10px;">
                    <i class="fas fa-user-friends"></i>
                </div>
                <h4 style="font-size: 15px; font-weight: 700; color: var(--text-heading);">Chưa có học viên nào trong khóa học này</h4>
                <p class="text-muted" style="font-size: 13px; max-width: 400px; margin: 0 auto 16px;">
                    Nhấn vào nút "Thêm học viên" để chọn nhân viên từ các phòng ban tham gia khóa đào tạo.
                </p>
                <button type="button" class="btn btn-primary btn-sm" onclick="openParticipantModal()">
                    <i class="fas fa-plus"></i> Thêm học viên ngay
                </button>
            </div>
        <?php else: ?>
            <div class="table-wrapper">
                <table class="table-hover" id="participantTable">
                    <thead>
                        <tr>
                            <th style="width: 40px; text-align: center;">STT</th>
                            <th style="min-width: 220px;">Học viên</th>
                            <th style="min-width: 140px;">Phòng ban / Dự án</th>
                            <th style="min-width: 110px; text-align: center;">Trạng thái</th>
                            <th style="min-width: 90px; text-align: center;">Điểm số</th>
                            <th style="min-width: 110px; text-align: center;">Kết quả</th>
                            <th style="min-width: 130px;">Số hiệu Chứng chỉ</th>
                            <th style="min-width: 120px;">Ngày HT / Ghi chú</th>
                            <th style="width: 130px; text-align: center;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($participants as $idx => $p): 
                            $resBadge = $resultBadges[$p['result']] ?? ['Chưa đánh giá', 'badge bg-secondary', 'fas fa-clock'];
                            $pStatBadge = $participantStatusBadges[$p['status']] ?? ['Đang học', 'badge bg-primary text-white'];
                            
                            $jsonData = htmlspecialchars(json_encode([
                                'employee_id'    => $p['employee_id'],
                                'emp_code'       => $p['emp_code'],
                                'full_name'      => $p['full_name'],
                                'dept_name'      => $p['dept_name'],
                                'score'          => $p['score'],
                                'result'         => $p['result'],
                                'status'         => $p['status'],
                                'certificate_no' => $p['certificate_no'],
                                'completed_date' => $p['completed_date'],
                                'notes'          => $p['notes'],
                            ]), ENT_QUOTES, 'UTF-8');
                        ?>
                            <tr class="participant-row">
                                <td style="text-align: center; color: var(--text-muted); font-size: 13px;"><?= $idx + 1 ?></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div style="width: 34px; height: 34px; border-radius: 8px; background: linear-gradient(135deg, var(--primary), var(--accent)); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 14px;">
                                            <?= mb_substr($p['full_name'], 0, 1) ?>
                                        </div>
                                        <div>
                                            <a href="<?= BASE_URL ?>/employee/detail/<?= $p['employee_id'] ?>" style="font-weight: 700; color: var(--text-heading); text-decoration: none; font-size: 13px;" target="_blank">
                                                <?= h($p['full_name']) ?>
                                            </a>
                                            <div style="font-size: 11px; color: var(--text-muted);">
                                                <span class="badge bg-secondary text-dark" style="font-size: 10px;"><?= h($p['emp_code']) ?></span>
                                                <?= !empty($p['pos_title']) ? '• ' . h($p['pos_title']) : '' ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-size: 12px; font-weight: 600; color: var(--text-heading);">
                                        <?= h($p['dept_name'] ?: '---') ?>
                                    </div>
                                    <?php if (!empty($p['project_name'])): ?>
                                        <div style="font-size: 11px; color: var(--primary);">
                                            <i class="fas fa-hard-hat"></i> <?= h($p['project_name']) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: center;">
                                    <span class="<?= $pStatBadge[1] ?>" style="font-size: 11px; padding: 4px 8px; border-radius: 4px;">
                                        <?= $pStatBadge[0] ?>
                                    </span>
                                </td>
                                <td style="text-align: center; font-weight: 700; font-size: 14px;">
                                    <?= $p['score'] !== null ? number_format((float)$p['score'], 1) : '<span class="text-muted">--</span>' ?>
                                </td>
                                <td style="text-align: center;">
                                    <span class="<?= $resBadge[1] ?>" style="font-size: 11px; padding: 4px 8px; border-radius: 4px;">
                                        <i class="<?= $resBadge[2] ?>"></i> <?= $resBadge[0] ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (!empty($p['certificate_no'])): ?>
                                        <span class="badge bg-light text-dark" style="border: 1px dashed var(--border); font-family: monospace; font-size: 12px;">
                                            <i class="fas fa-certificate text-warning"></i> <?= h($p['certificate_no']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted" style="font-size: 12px;">Chưa cấp</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($p['completed_date'])): ?>
                                        <div style="font-size: 12px; font-weight: 500;">
                                            <i class="far fa-calendar-check text-success"></i> <?= fmtDate($p['completed_date']) ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($p['notes'])): ?>
                                        <div class="text-muted" style="font-size: 11px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 140px;" title="<?= h($p['notes']) ?>">
                                            <?= h($p['notes']) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: center;">
                                    <div class="d-flex justify-content-center gap-1">
                                        <button type="button" class="btn btn-primary btn-sm" onclick='openResultModal(<?= $jsonData ?>)' title="Cập nhật kết quả & Điểm" style="padding: 4px 8px; font-size: 12px;">
                                            <i class="fas fa-edit"></i> Đánh giá
                                        </button>
                                        <form method="POST" action="<?= BASE_URL ?>/training/removeParticipant/<?= $course->id ?>/<?= $p['employee_id'] ?>" style="display: inline;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa học viên <?= h($p['full_name']) ?> khỏi khóa đào tạo này?');">
                                            <input type="hidden" name="_csrf_token" value="<?= Session::generateCsrfToken() ?>">
                                            <button type="submit" class="btn btn-ghost btn-sm" title="Xóa học viên" style="padding: 4px 8px; color: var(--danger);">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- NHÚNG 2 MODALS -->
<?php require '_participant_modal.php'; ?>
<?php require '_result_modal.php'; ?>

<script>
function filterParticipantTable() {
    const term = document.getElementById('participantTableSearch').value.trim().toLowerCase();
    const rows = document.querySelectorAll('#participantTable tbody tr.participant-row');
    rows.forEach(row => {
        const text = row.innerText.toLowerCase();
        row.style.display = text.includes(term) ? '' : 'none';
    });
}
</script>
