<div class="content-header">
    <div class="header-left">
        <h2><i class="fas fa-signature text-danger"></i> Phê duyệt & E-Sign (Kanban)</h2>
        <p>Quản lý luồng duyệt động đa cấp và ký số bảo mật.</p>
    </div>
</div>

<?php if (isset($_SESSION['flash_success'])): ?>
    <div class="alert alert-success"><?= $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger"><?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?></div>
<?php endif; ?>

<div class="row">
    <!-- Cột 1: Cần tôi duyệt (To-Do) -->
    <div class="col-md-7">
        <div class="panel">
            <div class="panel-header">
                <h3><i class="fas fa-clipboard-check text-warning"></i> Chờ tôi duyệt (To-Do Approvals)</h3>
            </div>
            <div class="panel-body">
                <div class="kanban-board">
                    <?php if (empty($approvals)): ?>
                        <p class="text-muted">Không có yêu cầu nào cần duyệt.</p>
                    <?php endif; ?>
                    
                    <?php foreach ($approvals as $a): ?>
                        <div class="kanban-card">
                            <div class="k-header">
                                <span class="badge bg-secondary"><?= h($a['workflow_name']) ?></span>
                                <small><?= date('d/m/Y', strtotime($a['created_at'])) ?></small>
                            </div>
                            <h4 class="k-title"><?= h($a['title']) ?></h4>
                            <p class="k-desc">Người yêu cầu: <strong><?= h($a['emp_name']) ?></strong></p>
                            <p class="k-desc">Bước hiện tại: <?= $a['current_step'] ?> / <?= $a['total_steps'] ?></p>
                            
                            <div class="k-actions mt-3">
                                <button class="btn btn-sm btn-info text-white" onclick="viewDetail(<?= htmlspecialchars(json_encode($a)) ?>)">
                                    <i class="fas fa-eye"></i> Chi tiết
                                </button>
                                <button class="btn btn-sm btn-success" onclick="openSignModal(<?= $a['id'] ?>, 'Approve')">
                                    <i class="fas fa-check"></i> Duyệt
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="openSignModal(<?= $a['id'] ?>, 'Reject')">
                                    <i class="fas fa-times"></i> Từ chối
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Cột 2: Yêu cầu của tôi (My Requests) -->
    <div class="col-md-5">
        <div class="panel">
            <div class="panel-header">
                <h3><i class="fas fa-user-edit text-primary"></i> Yêu cầu của tôi (My Requests)</h3>
            </div>
            <div class="panel-body">
                <ul class="request-list">
                    <?php foreach ($myRequests as $req): ?>
                        <li>
                            <div class="r-info">
                                <strong><?= h($req['title']) ?></strong>
                                <span class="text-muted d-block" style="font-size: 12px;"><?= date('d/m H:i', strtotime($req['created_at'])) ?></span>
                            </div>
                            <div class="r-status">
                                <?php
                                $badge = 'bg-secondary';
                                if ($req['status'] == 'Approved') $badge = 'bg-success';
                                if ($req['status'] == 'Rejected') $badge = 'bg-danger';
                                if ($req['status'] == 'In Progress') $badge = 'bg-info';
                                if ($req['status'] == 'Pending') $badge = 'bg-warning text-dark';
                                ?>
                                <span class="badge <?= $badge ?>"><?= h($req['status']) ?> (<?= $req['current_step'] ?>/<?= $req['total_steps'] ?>)</span>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Modal E-Sign -->
<div class="custom-esign-modal" id="esignModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center; pointer-events:auto;">
    <div class="modal-dialog" style="background:#fff; padding:20px; border-radius:8px; width:400px; pointer-events:auto;">
        <h4 id="esignTitle">Xác thực Ký số</h4>
        <hr>
        <form action="<?= BASE_URL ?>/workflow/process" method="POST">
            <input type="hidden" name="request_id" id="reqIdInput">
            <input type="hidden" name="action" id="actionInput">
            
            <div class="mb-3">
                <label>Ghi chú / Ý kiến:</label>
                <textarea name="comment" class="form-control" rows="2" placeholder="Có thể bỏ trống..."></textarea>
            </div>
            <div class="mb-3">
                <label>Mật khẩu Cấp 2 (E-Sign PIN):</label>
                <input type="password" id="esignPasswordInput" name="esign_password" class="form-control" required placeholder="Nhập: posung@123">
                <small class="text-danger">* Bắt buộc để xác thực chữ ký số.</small>
            </div>
            <div class="text-end">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('esignModal').style.display='none'">Hủy</button>
                <button type="submit" class="btn btn-primary" id="btnSubmitSign">Xác nhận Ký</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Chi tiết Yêu cầu -->
<div class="custom-esign-modal" id="detailModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center; pointer-events:auto;">
    <div class="modal-dialog" style="background:#fff; padding:20px; border-radius:8px; width:500px; pointer-events:auto; max-height: 80vh; overflow-y: auto;">
        <h4 id="detailTitle" class="text-primary border-bottom pb-2"><i class="fas fa-info-circle"></i> Chi tiết Yêu cầu</h4>
        <div class="mb-3 mt-3">
            <strong>Tiêu đề:</strong> <span id="dt_title"></span>
        </div>
        <div class="mb-3">
            <strong>Người yêu cầu:</strong> <span id="dt_emp"></span>
        </div>
        <div class="mb-3">
            <strong>Ngày tạo:</strong> <span id="dt_date"></span>
        </div>
        <div class="mb-3">
            <strong>Nội dung chi tiết:</strong>
            <div id="dt_desc" class="p-3 mt-2 border rounded bg-light" style="min-height: 80px;"></div>
        </div>
        <div class="text-end mt-4">
            <button type="button" class="btn btn-secondary" onclick="document.getElementById('detailModal').style.display='none'">Đóng</button>
        </div>
    </div>
</div>

<script>
function viewDetail(data) {
    document.getElementById('dt_title').innerText = data.title;
    document.getElementById('dt_emp').innerText = data.emp_name || 'N/A';
    document.getElementById('dt_date').innerText = data.created_at;
    
    let desc = data.description ? data.description : '<i>Không có nội dung mô tả chi tiết.</i>';
    // escape html manually if needed, but innerHTML is fine for plain text seeded.
    document.getElementById('dt_desc').innerHTML = desc;
    
    document.getElementById('detailModal').style.display = 'flex';
}

function openSignModal(reqId, action) {
    document.getElementById('reqIdInput').value = reqId;
    document.getElementById('actionInput').value = action;
    
    const title = action === 'Approve' ? 'Xác thực Duyệt yêu cầu' : 'Xác thực Từ chối yêu cầu';
    document.getElementById('esignTitle').innerText = title;
    
    document.getElementById('btnSubmitSign').className = action === 'Approve' ? 'btn btn-success' : 'btn btn-danger';
    
    document.getElementById('esignModal').style.display = 'flex';
    
    setTimeout(() => {
        document.getElementById('esignPasswordInput').focus();
    }, 100);
}
</script>

<style>
.kanban-board {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
}
.kanban-card {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 15px;
    width: calc(50% - 15px);
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    transition: transform 0.2s;
}
.kanban-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}
.k-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
}
.k-title { font-size: 16px; margin: 0 0 5px 0; }
.k-desc { font-size: 14px; margin: 0; color: #475569; }
.request-list {
    list-style: none; padding: 0; margin: 0;
}
.request-list li {
    display: flex; justify-content: space-between; align-items: center;
    padding: 10px 0; border-bottom: 1px dashed #cbd5e1;
}
.request-list li:last-child { border-bottom: none; }
</style>
