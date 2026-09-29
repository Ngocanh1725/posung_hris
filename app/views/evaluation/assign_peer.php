<?php
/**
 * View: evaluation/assign_peer.php – HR Phân Công Đồng Nghiệp Đánh Giá Chéo (Peer Review)
 */

$maxPeers = !empty($period->peer_review_count) ? (int)$period->peer_review_count : 2;

// Thống kê nhanh
$totalEmps = count($employees);
$fullyAssignedCount = 0;
$totalAssignedCount = 0;
$totalSubmittedPeers = 0;

foreach ($employees as $e) {
    $assignedCount = count($e['assigned_peers'] ?? []);
    $totalAssignedCount += $assignedCount;
    if ($assignedCount >= $maxPeers) $fullyAssignedCount++;

    foreach ($e['assigned_peers'] as $ap) {
        if ($ap['status'] === 'Submitted') $totalSubmittedPeers++;
    }
}
?>

<!-- BREADCRUMB -->
<div class="breadcrumb-nav mb-3 d-flex align-items-center gap-2" style="font-size: 13px; color: var(--text-muted);">
    <a href="<?= BASE_URL ?>/evaluation" class="text-decoration-none" style="color: var(--primary);">
        <i class="fas fa-award"></i> Quản lý Đánh giá KPI
    </a>
    <i class="fas fa-chevron-right" style="font-size: 10px;"></i>
    <a href="<?= BASE_URL ?>/evaluation/goals/<?= $period->id ?>" class="text-decoration-none" style="color: var(--primary);">
        Mục tiêu KRA: <?= htmlspecialchars($period->name) ?>
    </a>
    <i class="fas fa-chevron-right" style="font-size: 10px;"></i>
    <span>Phân công Peer Reviewers</span>
</div>

<!-- HEADER CARD -->
<div class="card mb-4" style="border: 1px solid var(--border); box-shadow: 0 4px 18px rgba(0,0,0,0.04); border-radius: 12px;">
    <div class="card-body" style="padding: 24px;">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div class="d-flex gap-3 align-items-center">
                <div style="width: 56px; height: 56px; border-radius: 14px; background: linear-gradient(135deg, #0284c7, #0ea5e9); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 24px; font-weight: 700; box-shadow: 0 6px 16px rgba(14,165,233,0.3); flex-shrink: 0;">
                    <i class="fas fa-users-cog"></i>
                </div>
                <div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <h2 style="font-size: 20px; font-weight: 700; margin: 0; color: var(--text);">
                            Phân Công Đồng Nghiệp Đánh Giá Chéo (Peer Review)
                        </h2>
                        <span class="badge bg-info text-white" style="font-size: 12px; font-weight: 600;">
                            Quy định: Tối đa <?= $maxPeers ?> đồng nghiệp / nhân sự
                        </span>
                    </div>
                    <div style="font-size: 13px; color: var(--text-muted); display: flex; gap: 16px; flex-wrap: wrap; margin-top: 6px;">
                        <span><i class="fas fa-calendar-alt text-primary"></i> Chu kỳ: <strong><?= htmlspecialchars($period->name) ?></strong></span>
                        <span><i class="fas fa-user-friends text-primary"></i> Đã gán đủ: <strong><?= $fullyAssignedCount ?>/<?= $totalEmps ?></strong> nhân sự</span>
                        <span><i class="fas fa-check-double text-success"></i> Đồng nghiệp đã nộp: <strong><?= $totalSubmittedPeers ?>/<?= $totalAssignedCount ?></strong> lượt</span>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2">
                <a href="<?= BASE_URL ?>/evaluation/goals/<?= $period->id ?>" class="btn btn-primary" style="font-weight: 600;">
                    <i class="fas fa-bullseye me-1"></i> Bảng Mục Tiêu KRA
                </a>
                <a href="<?= BASE_URL ?>/evaluation/show/<?= $period->id ?>" class="btn btn-ghost" style="border: 1px solid var(--border);">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>
            </div>
        </div>
    </div>
</div>

<!-- FILTER BAR -->
<div class="card mb-4" style="border: 1px solid var(--border); box-shadow: 0 2px 10px rgba(0,0,0,0.02); border-radius: 10px;">
    <div class="card-body" style="padding: 16px 20px;">
        <form method="GET" action="<?= BASE_URL ?>/evaluation/assignPeerReviewers/<?= $period->id ?>" class="row g-2 align-items-center">
            <!-- Lọc Phòng ban -->
            <div class="col-md-4 col-sm-6">
                <select name="dept" class="form-select form-select-sm">
                    <option value="">-- Tất cả Phòng ban --</option>
                    <?php foreach ($departments as $dept): ?>
                        <option value="<?= $dept->id ?>" <?= $filters['dept_id'] == $dept->id ? 'selected' : '' ?>>
                            <?= htmlspecialchars($dept->dept_name) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Tìm kiếm tên/mã -->
            <div class="col-md-5 col-sm-6">
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Tìm tên hoặc mã nhân viên cần phân công..." value="<?= htmlspecialchars($filters['search']) ?>">
                </div>
            </div>

            <!-- Nút Lọc & Reset -->
            <div class="col-md-3 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                    <i class="fas fa-filter me-1"></i> Lọc danh sách
                </button>
                <a href="<?= BASE_URL ?>/evaluation/assignPeerReviewers/<?= $period->id ?>" class="btn btn-ghost btn-sm" title="Xóa bộ lọc" style="border: 1px solid var(--border);">
                    <i class="fas fa-redo"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- DANH SÁCH NHÂN SỰ & PHÂN CÔNG PEERS -->
<div class="card" style="border: 1px solid var(--border); box-shadow: 0 4px 18px rgba(0,0,0,0.04); border-radius: 12px; overflow: hidden;">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
            <thead style="background: var(--bg-hover); border-bottom: 2px solid var(--border);">
                <tr>
                    <th style="padding: 14px 16px; width: 50px;" class="text-center">#</th>
                    <th style="padding: 14px 16px; width: 280px;">Nhân sự được đánh giá</th>
                    <th style="padding: 14px 16px; width: 180px;">Phòng ban</th>
                    <th style="padding: 14px 16px;">Đồng nghiệp đánh giá (Peer Reviewers)</th>
                    <th style="padding: 14px 16px; width: 140px;" class="text-center">Trạng thái gán</th>
                    <th style="padding: 14px 16px; width: 130px;" class="text-end">Phân công</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($employees)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="fas fa-inbox fa-3x mb-3 text-secondary opacity-50"></i>
                            <div>Không tìm thấy nhân viên nào phù hợp.</div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($employees as $idx => $e): ?>
                        <?php 
                            $assigned = $e['assigned_peers'] ?? [];
                            $assignedCount = count($assigned);
                            $assignedIds = array_column($assigned, 'reviewer_id');
                        ?>
                        <tr>
                            <td class="text-center text-muted fw-semibold"><?= $idx + 1 ?></td>

                            <!-- Nhân sự -->
                            <td style="padding: 12px 16px;">
                                <div class="d-flex align-items-center gap-2">
                                    <div style="width: 36px; height: 36px; border-radius: 8px; background: linear-gradient(135deg, #0284c7, #0ea5e9); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px; flex-shrink: 0;">
                                        <?= mb_strtoupper(mb_substr($e['full_name'], 0, 1, 'UTF-8')) ?>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($e['full_name']) ?></div>
                                        <div class="text-muted" style="font-size: 11px;">
                                            <span class="badge bg-secondary-subtle text-secondary"><?= htmlspecialchars($e['employee_code']) ?></span>
                                            <?= htmlspecialchars($e['pos_title'] ?? 'Nhân viên') ?>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Phòng ban -->
                            <td style="padding: 12px 16px;">
                                <div class="fw-semibold text-dark"><?= htmlspecialchars($e['dept_name'] ?? 'Chưa phân bổ') ?></div>
                            </td>

                            <!-- Danh sách Peer Reviewers -->
                            <td style="padding: 12px 16px;">
                                <?php if (!empty($assigned)): ?>
                                    <div class="d-flex flex-wrap gap-2">
                                        <?php foreach ($assigned as $ap): ?>
                                            <div class="d-inline-flex align-items-center gap-1 px-2 py-1 rounded" style="background: var(--bg-hover); border: 1px solid var(--border); font-size: 12px;">
                                                <i class="fas fa-user-circle text-primary"></i>
                                                <span class="fw-semibold"><?= htmlspecialchars($ap['reviewer_name']) ?></span>
                                                <?php if ($ap['status'] === 'Submitted'): ?>
                                                    <span class="badge bg-success-subtle text-success ms-1" title="Đã nộp bài đánh giá"><i class="fas fa-check"></i> Đã nộp</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning-subtle text-warning ms-1" title="Đang chờ nộp bài"><i class="fas fa-clock"></i> Chờ nộp</span>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted fst-italic" style="font-size: 12px;">
                                        <i class="fas fa-exclamation-circle text-warning me-1"></i> Chưa có đồng nghiệp nào được gán
                                    </span>
                                <?php endif; ?>
                            </td>

                            <!-- Trạng thái gán -->
                            <td class="text-center" style="padding: 12px 16px;">
                                <?php if ($assignedCount >= $maxPeers): ?>
                                    <span class="badge bg-success-subtle text-success fw-bold" style="font-size: 11px;">
                                        <i class="fas fa-check-circle me-1"></i> Đủ (<?= $assignedCount ?>/<?= $maxPeers ?>)
                                    </span>
                                <?php elseif ($assignedCount > 0): ?>
                                    <span class="badge bg-warning-subtle text-warning fw-bold" style="font-size: 11px;">
                                        <i class="fas fa-exclamation-triangle me-1"></i> Thiếu (<?= $assignedCount ?>/<?= $maxPeers ?>)
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-danger-subtle text-danger fw-bold" style="font-size: 11px;">
                                        <i class="fas fa-times-circle me-1"></i> Chưa gán (0/<?= $maxPeers ?>)
                                    </span>
                                <?php endif; ?>
                            </td>

                            <!-- Nút thao tác -->
                            <td class="text-end" style="padding: 12px 16px;">
                                <button type="button" class="btn btn-sm btn-outline-primary" 
                                        onclick="openAssignModal(<?= $e['id'] ?>, '<?= htmlspecialchars(addslashes($e['full_name'])) ?>', <?= json_encode($assignedIds) ?>)">
                                    <i class="fas fa-user-plus me-1"></i> Phân công
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL PHÂN CÔNG PEER REVIEWERS -->
<div class="modal fade" id="assignPeerModal" tabindex="-1" aria-labelledby="assignPeerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius: 14px; border: 1px solid var(--border); box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            <div class="modal-header" style="background: var(--bg-hover); padding: 18px 24px; border-bottom: 1px solid var(--border);">
                <div>
                    <h5 class="modal-title fw-bold" id="assignPeerModalLabel" style="font-size: 16px; color: var(--text);">
                        <i class="fas fa-users-cog text-primary me-2"></i> Phân Công Peer Reviewers
                    </h5>
                    <div class="text-muted small mt-1">
                        Chỉ định đồng nghiệp đánh giá chéo cho: <strong id="modalTargetEmployeeName" class="text-dark"></strong>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="<?= BASE_URL ?>/evaluation/assignPeerReviewers/<?= $period->id ?>" method="POST" id="modalAssignForm">
                <input type="hidden" name="_csrf_token" value="<?= Session::generateCsrfToken() ?>">
                <input type="hidden" name="employee_id" id="modalEmployeeId" value="0">

                <div class="modal-body p-4">
                    <div class="alert alert-info py-2 px-3 mb-3 small d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-info-circle me-1"></i> Vui lòng tích chọn tối đa <strong><?= $maxPeers ?> đồng nghiệp</strong> để tham gia đánh giá chéo.</span>
                        <span class="badge bg-primary" id="selectedPeersCounter">0/<?= $maxPeers ?> đã chọn</span>
                    </div>

                    <!-- Tìm kiếm nhanh đồng nghiệp trong modal -->
                    <div class="input-group input-group-sm mb-3">
                        <span class="input-group-text"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" id="colleagueSearchInput" class="form-control" placeholder="Gõ tên hoặc mã đồng nghiệp để lọc nhanh..." onkeyup="filterColleaguesList()">
                    </div>

                    <!-- Danh sách đồng nghiệp để chọn -->
                    <div class="colleagues-list-box p-2 border rounded" style="max-height: 360px; overflow-y: auto;">
                        <div class="row g-2" id="colleaguesCheckboxesContainer">
                            <?php foreach ($allColleagues as $col): ?>
                                <div class="col-md-6 colleague-item" data-name="<?= strtolower(htmlspecialchars($col->full_name)) ?>" data-code="<?= strtolower(htmlspecialchars($col->employee_code)) ?>">
                                    <div class="form-check p-2 rounded border colleague-card" style="cursor: pointer; transition: background 0.2s;" onclick="togglePeerCheckbox(this)">
                                        <input class="form-check-input ms-1 me-2 peer-checkbox" type="checkbox" name="peer_ids[]" 
                                               value="<?= $col->id ?>" id="peer_chk_<?= $col->id ?>" onchange="updateSelectedCount()">
                                        <label class="form-check-label w-100" for="peer_chk_<?= $col->id ?>" style="cursor: pointer;">
                                            <div class="fw-bold text-dark" style="font-size: 13px;"><?= htmlspecialchars($col->full_name) ?></div>
                                            <small class="text-muted" style="font-size: 11px;">
                                                [<?= htmlspecialchars($col->employee_code) ?>] - <?= htmlspecialchars($col->dept_name ?? 'Công ty') ?>
                                            </small>
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="modal-footer" style="padding: 14px 24px; border-top: 1px solid var(--border); background: var(--bg-hover);">
                    <button type="button" class="btn btn-ghost" data-bs-dismiss="modal" style="border: 1px solid var(--border);">Hủy bỏ</button>
                    <button type="submit" class="btn btn-primary" id="btnSavePeers" style="font-weight: 600; box-shadow: 0 4px 12px rgba(2, 132, 199, 0.3);">
                        <i class="fas fa-check me-1"></i> Lưu & Gửi Thông Báo Phân Công
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- JAVASCRIPT MODAL & CHECKBOXES -->
<script>
const maxPeersAllowed = <?= $maxPeers ?>;

function openAssignModal(employeeId, employeeName, assignedPeerIds) {
    document.getElementById('modalEmployeeId').value = employeeId;
    document.getElementById('modalTargetEmployeeName').innerText = employeeName;

    // Reset tất cả checkboxes
    const checkboxes = document.querySelectorAll('.peer-checkbox');
    checkboxes.forEach(chk => {
        chk.checked = false;
        const card = chk.closest('.colleague-item');
        card.style.display = '';

        // Ẩn chính nhân viên đó (không thể tự review chéo chính mình)
        if (chk.value == employeeId) {
            card.style.display = 'none';
        }
    });

    // Check những peer đã gán trước đó
    if (assignedPeerIds && Array.isArray(assignedPeerIds)) {
        assignedPeerIds.forEach(pId => {
            const chk = document.getElementById('peer_chk_' + pId);
            if (chk) chk.checked = true;
        });
    }

    updateSelectedCount();

    const modal = new bootstrap.Modal(document.getElementById('assignPeerModal'));
    modal.show();
}

function togglePeerCheckbox(container) {
    // Không can thiệp nếu click trực tiếp vào input
    const checkbox = container.querySelector('.peer-checkbox');
    // Handled by default label/input
}

function updateSelectedCount() {
    const checked = document.querySelectorAll('.peer-checkbox:checked');
    const count = checked.length;
    const counterBadge = document.getElementById('selectedPeersCounter');
    counterBadge.innerText = `${count}/${maxPeersAllowed} đã chọn`;

    if (count > maxPeersAllowed) {
        counterBadge.className = 'badge bg-danger';
        alert(`Bạn chỉ được phân công tối đa ${maxPeersAllowed} đồng nghiệp cho mỗi nhân sự.`);
        // Bỏ check cái vừa chọn
        event.target.checked = false;
        updateSelectedCount();
        return;
    } else if (count === maxPeersAllowed) {
        counterBadge.className = 'badge bg-success';
    } else {
        counterBadge.className = 'badge bg-primary';
    }
}

function filterColleaguesList() {
    const q = document.getElementById('colleagueSearchInput').value.toLowerCase().trim();
    const items = document.querySelectorAll('.colleague-item');

    items.forEach(item => {
        const name = item.dataset.name;
        const code = item.dataset.code;
        if (!q || name.includes(q) || code.includes(q)) {
            // Chỉ hiện nếu không phải là nhân sự đang được phân công
            const chk = item.querySelector('.peer-checkbox');
            const targetId = document.getElementById('modalEmployeeId').value;
            if (chk.value != targetId) {
                item.style.display = '';
            }
        } else {
            item.style.display = 'none';
        }
    });
}
</script>
