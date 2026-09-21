<?php
/** View: recruitment/candidates.php – Bảng ATS Kanban Ứng viên */

$candidateStatusLabels = [
    'received'    => ['Mới nhận', 'primary'],
    'screening'   => ['Sàng lọc', 'info'],
    'skills_test' => ['Test 6G/HSE', 'warning'],
    'interview'   => ['Phỏng vấn', 'secondary'],
    'offered'     => ['Đề xuất', 'success'],
    'hired'       => ['Tiếp nhận', 'success'],
    'rejected'    => ['Từ chối', 'danger'],
];

// Group candidates by status
$kanban = [];
foreach (array_keys($candidateStatusLabels) as $k) {
    $kanban[$k] = [];
}

foreach ($candidates as $c) {
    if (isset($kanban[$c->status])) {
        $kanban[$c->status][] = $c;
    } else {
        $kanban['received'][] = $c;
    }
}
?>

<style>
/* CSS cho Kanban Board */
.kanban-board {
    display: flex;
    overflow-x: auto;
    gap: 16px;
    padding-bottom: 20px;
    min-height: 70vh;
}
.kanban-column {
    flex: 0 0 280px;
    background: #f1f5f9;
    border-radius: 8px;
    display: flex;
    flex-direction: column;
    max-height: 100%;
}
.kanban-column-header {
    padding: 12px 16px;
    font-weight: bold;
    border-bottom: 2px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.kanban-cards {
    padding: 12px;
    flex: 1;
    overflow-y: auto;
    min-height: 150px;
}
.kanban-card {
    background: #ffffff;
    padding: 12px;
    border-radius: 6px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    margin-bottom: 10px;
    cursor: grab;
    border-left: 4px solid var(--primary);
    position: relative;
    transition: transform 0.2s, box-shadow 0.2s;
}
.kanban-card:active {
    cursor: grabbing;
}
.kanban-card:hover {
    box-shadow: 0 4px 6px rgba(0,0,0,0.15);
    transform: translateY(-2px);
}
.kanban-card.blacklisted {
    border-left-color: var(--danger);
    background: #fff5f5;
}
.kanban-card .c-name {
    font-weight: bold;
    font-size: 14px;
    color: var(--text-heading);
    margin-bottom: 4px;
}
.kanban-card .c-pos {
    font-size: 12px;
    color: var(--text-secondary);
    margin-bottom: 8px;
}
.kanban-card .c-meta {
    font-size: 11px;
    display: flex;
    gap: 8px;
    color: #64748b;
}
.badge-blacklist {
    background: var(--danger);
    color: #fff;
    font-size: 10px;
    padding: 2px 6px;
    border-radius: 4px;
    margin-bottom: 6px;
    display: inline-block;
}
.kanban-ghost {
    opacity: 0.4;
    background: #e2e8f0;
}
</style>

<!-- Bộ lọc -->
<div class="panel mb-4">
    <div class="panel-header"><h3><i class="fas fa-filter"></i> Lọc Ứng viên</h3></div>
    <div class="panel-body">
        <form method="GET" action="<?= BASE_URL ?>/recruitment/candidates">
            <div style="display:flex; gap:12px; align-items:end; flex-wrap:wrap;">
                <div class="form-group" style="flex:1; min-width:200px;">
                    <label class="form-label-sm">Từ khóa</label>
                    <input type="text" name="search" class="form-control" value="<?= h($filters['search'] ?? '') ?>" placeholder="Tên, SĐT, email...">
                </div>
                <div class="form-group" style="min-width:180px;">
                    <label class="form-label-sm">Yêu cầu TD</label>
                    <select name="request_id" class="form-control">
                        <option value="">-- Tất cả --</option>
                        <?php foreach($requests as $rq): ?>
                            <option value="<?= $rq->id ?>" <?= ($filters['request_id'] ?? 0) == $rq->id ? 'selected' : '' ?>><?= h($rq->request_code) ?></option>
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
    <div>
        <a href="<?= BASE_URL ?>/recruitment/addCandidate<?= !empty($filters['request_id']) ? '?request_id='.$filters['request_id'] : '' ?>" class="btn btn-primary">
            <i class="fas fa-user-plus"></i> Thêm Ứng viên
        </a>
    </div>
    <div>
        <a href="<?= BASE_URL ?>/recruitment/apply" target="_blank" class="btn btn-outline-primary">
            <i class="fas fa-qrcode"></i> Kiosk QR Form
        </a>
    </div>
</div>

<!-- Kanban Board -->
<div class="kanban-board">
    <?php foreach ($candidateStatusLabels as $statusKey => $labelInfo): ?>
    <div class="kanban-column">
        <div class="kanban-column-header">
            <span class="text-<?= $labelInfo[1] ?>"><i class="fas fa-circle" style="font-size: 10px; margin-right: 4px;"></i> <?= $labelInfo[0] ?></span>
            <span class="badge bg-light text-dark" style="font-size: 12px;" id="count-<?= $statusKey ?>"><?= count($kanban[$statusKey]) ?></span>
        </div>
        <div class="kanban-cards" data-status="<?= $statusKey ?>" id="col-<?= $statusKey ?>">
            <?php foreach ($kanban[$statusKey] as $c): ?>
            <div class="kanban-card <?= $c->is_blacklisted ? 'blacklisted' : '' ?>" data-id="<?= $c->id ?>" data-blacklisted="<?= $c->is_blacklisted ?>">
                <?php if ($c->is_blacklisted): ?>
                    <div class="badge-blacklist" title="<?= h($c->blacklist_reason) ?>"><i class="fas fa-exclamation-triangle"></i> HSE BLACKLIST</div>
                <?php endif; ?>
                
                <div class="c-name">
                    <a href="<?= BASE_URL ?>/recruitment/interview/<?= $c->id ?>" style="text-decoration:none; color:inherit;">
                        <?= h($c->full_name) ?>
                    </a>
                </div>
                <div class="c-pos" title="<?= h($c->request_code) ?>"><i class="fas fa-briefcase"></i> <?= h($c->pos_title ?? $c->request_desc ?? 'Ứng viên tự do') ?></div>
                <div class="c-meta">
                    <span><i class="far fa-calendar-alt"></i> <?= fmtDate($c->created_at) ?></span>
                    <?php if ($c->cert_file_path): ?>
                        <span class="text-success"><i class="fas fa-certificate"></i> Có CC</span>
                    <?php endif; ?>
                </div>
                
                <div class="mt-2 text-end">
                    <?php if ($statusKey === 'offered' && !$c->is_blacklisted): ?>
                        <a href="<?= BASE_URL ?>/recruitment/printOffer/<?= $c->id ?>" target="_blank" class="btn btn-sm btn-ghost" style="padding: 2px 5px; font-size: 11px;"><i class="fas fa-print"></i> In Offer</a>
                        <a href="<?= BASE_URL ?>/recruitment/hire/<?= $c->id ?>" class="btn btn-sm btn-success" style="padding: 2px 5px; font-size: 11px;" onclick="return confirm('Chuyển ứng viên này thành nhân sự chính thức?');"><i class="fas fa-check"></i> Nhận việc</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- SortableJS -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const columns = document.querySelectorAll('.kanban-cards');
    
    columns.forEach(col => {
        new Sortable(col, {
            group: 'kanban',
            animation: 150,
            ghostClass: 'kanban-ghost',
            onEnd: function (evt) {
                const itemEl = evt.item;
                const toList = evt.to;
                const fromList = evt.from;
                
                if (toList === fromList) return; // Không thay đổi cột
                
                const candId = itemEl.dataset.id;
                const newStatus = toList.dataset.status;
                const oldStatus = fromList.dataset.status;
                const isBlacklisted = itemEl.dataset.blacklisted === '1';

                // Kiểm tra ràng buộc
                if (isBlacklisted && newStatus === 'offered') {
                    alert('LỖI BẢO MẬT: Ứng viên này nằm trong danh sách đen (Blacklist). Bạn không thể chuyển qua Đề xuất lương!');
                    fromList.appendChild(itemEl); // Trả lại vị trí cũ
                    return;
                }

                // Gửi AJAX cập nhật
                const formData = new URLSearchParams();
                formData.append('id', candId);
                formData.append('status', newStatus);
                formData.append('_csrf_token', '<?= Session::generateCsrfToken() ?>');

                fetch('<?= BASE_URL ?>/recruitment/updateStatus', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: formData.toString()
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Cập nhật số đếm
                        document.getElementById('count-' + newStatus).innerText = toList.children.length;
                        document.getElementById('count-' + oldStatus).innerText = fromList.children.length;
                    } else {
                        alert('Lỗi: ' + data.message);
                        fromList.appendChild(itemEl); // Trả lại vị trí cũ
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('Lỗi kết nối.');
                    fromList.appendChild(itemEl);
                });
            }
        });
    });
});
</script>
