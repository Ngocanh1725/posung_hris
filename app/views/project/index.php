<!-- ══════════════════════════════════════════════════════════
     QUẢN LÝ DỰ ÁN (V2)
     ══════════════════════════════════════════════════════════ -->

<div class="breadcrumb-bar">
    <a href="<?= BASE_URL ?>/organization"><i class="fas fa-sitemap"></i> Tổ chức</a>
    <i class="fas fa-chevron-right"></i>
    <span>Dự án</span>
</div>

<div class="panel-header" style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 24px;">
    <h2><i class="fas fa-hard-hat" style="color: #10b981;"></i> Danh sách Dự án Thi công</h2>
    <?php if (Session::isManager() || Session::userRole() === 'project_manager'): ?>
    <button class="btn btn-primary btn-sm" onclick="document.getElementById('addProjectModal').style.display='flex'">
        <i class="fas fa-plus"></i> Thêm Dự án mới
    </button>
    <?php endif; ?>
</div>

<?php if (empty($projects)): ?>
    <div class="empty-state"><i class="fas fa-hard-hat"></i><p>Chưa có dự án nào trong hệ thống.</p></div>
<?php else: ?>
    <div class="prj-cards-grid">
        <?php foreach ($projects as $p): 
            $stats = $p['stats'];
            $pct = $stats['quota'] > 0 ? round(($stats['actual'] / $stats['quota']) * 100) : 0;
            if ($pct > 100) $pct = 100;

            $statusMap = [
                'planning'    => ['Lập kế hoạch', '#3b82f6', 'fa-calendar-alt'],
                'in_progress' => ['Đang thi công', '#10b981', 'fa-hammer'],
                'suspended'   => ['Tạm dừng', '#f59e0b', 'fa-pause-circle'],
                'completed'   => ['Hoàn thành', '#8b5cf6', 'fa-check-double'],
            ];
            $sInfo = $statusMap[$p['status']] ?? ['Không rõ', '#6b7280', 'fa-question'];
        ?>
        <div class="prj-card">
            <!-- Header Card -->
            <div class="prj-card-header" style="border-bottom: 2px solid <?= $sInfo[1] ?>;">
                <div class="prj-status" style="background: <?= $sInfo[1] ?>20; color: <?= $sInfo[1] ?>;">
                    <i class="fas <?= $sInfo[2] ?>"></i> <?= $sInfo[0] ?>
                </div>
                <div class="prj-code"><?= h($p['project_code']) ?></div>
            </div>
            
            <!-- Body Card -->
            <div class="prj-card-body">
                <a href="<?= BASE_URL ?>/project/detail/<?= $p['id'] ?>" class="prj-title text-link">
                    <?= h($p['name']) ?>
                </a>
                <p class="prj-client"><i class="fas fa-building"></i> <?= h($p['client_name'] ?? 'Chưa xác định khách hàng') ?></p>
                <p class="prj-location"><i class="fas fa-map-marker-alt"></i> <?= h($p['location'] ?? '—') ?></p>
                
                <div class="prj-cost-center">
                    Mã Cost Center: <strong><?= h($p['cost_center_code'] ?: 'N/A') ?></strong>
                </div>

                <!-- Định biên & Progress bar -->
                <div class="prj-headcount-section">
                    <div class="hc-labels">
                        <span>Nhân sự cơ hữu: <strong><?= $stats['actual'] ?></strong></span>
                        <span style="color:var(--text-muted); font-size:0.75rem;">Định biên: <?= $stats['quota'] ?: '?' ?></span>
                    </div>
                    <div class="progress-bar-wrapper">
                        <div class="progress-bar" style="width: <?= $pct ?>%; background: <?= $pct < 50 ? '#ef4444' : ($pct < 90 ? '#f59e0b' : '#10b981') ?>;"></div>
                    </div>
                </div>
            </div>
            
            <!-- Footer Card -->
            <div class="prj-card-footer">
                <div class="prj-footer-item" title="Kỹ sư nước ngoài">
                    <i class="fas fa-globe-asia" style="color:#6366f1;"></i> 
                    <strong><?= $p['expat_count'] ?? 0 ?></strong> Expat
                </div>
                <div class="prj-footer-item">
                    <i class="far fa-clock"></i>
                    <?= $p['start_date'] ? date('d/m/y', strtotime($p['start_date'])) : '—' ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Modal Thêm Dự án -->
<div id="addProjectModal" class="modal-overlay" style="display:none;">
    <div class="modal-content" style="max-width:600px;">
        <div class="modal-header">
            <h3><i class="fas fa-plus-circle"></i> Thêm Dự án mới</h3>
            <button class="modal-close" onclick="this.closest('.modal-overlay').style.display='none'">&times;</button>
        </div>
        <form method="POST" action="<?= BASE_URL ?>/project/store">
            <input type="hidden" name="_csrf_token" value="<?= Session::generateCsrfToken() ?>">
            <div class="modal-body">
                <div class="form-grid" style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                    <div class="form-group">
                        <label>Mã dự án <span class="required">*</span></label>
                        <input type="text" name="project_code" required placeholder="VD: SEHC-2026">
                    </div>
                    <div class="form-group">
                        <label>Tên dự án <span class="required">*</span></label>
                        <input type="text" name="name" required placeholder="VD: Samsung SEHC Phase 3">
                    </div>
                    <div class="form-group">
                        <label>Khách hàng</label>
                        <input type="text" name="client_name" placeholder="VD: Samsung Electronics">
                    </div>
                    <div class="form-group">
                        <label>Mã Cost Center</label>
                        <input type="text" name="cost_center_code" placeholder="VD: CC_SEHC_01">
                    </div>
                    <div class="form-group">
                        <label>Địa điểm</label>
                        <input type="text" name="location" placeholder="VD: Bắc Ninh">
                    </div>
                    <div class="form-group">
                        <label>Định biên phê duyệt</label>
                        <input type="number" name="headcount_quota" placeholder="VD: 50" min="0" value="0">
                    </div>
                    <div class="form-group">
                        <label>Ngày bắt đầu</label>
                        <input type="date" name="start_date">
                    </div>
                    <div class="form-group">
                        <label>Trạng thái</label>
                        <select name="status">
                            <option value="planning">Lập kế hoạch</option>
                            <option value="in_progress">Đang thi công</option>
                            <option value="suspended">Tạm dừng</option>
                            <option value="completed">Hoàn thành</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="this.closest('.modal-overlay').style.display='none'">Hủy</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Lưu dự án</button>
            </div>
        </form>
    </div>
</div>

<style>
/* Breadcrumb */
.breadcrumb-bar { display: flex; align-items: center; gap: 8px; margin-bottom: 20px; font-size: 0.85rem; color: var(--text-muted); }
.breadcrumb-bar a { color: var(--primary-light); text-decoration: none; }

/* Grid Card */
.prj-cards-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 24px; }
.prj-card {
    background: var(--bg-card); border-radius: 12px;
    border: 1px solid rgba(255,255,255,0.08);
    display: flex; flex-direction: column;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transition: transform 0.3s ease, box-shadow 0.3s;
}
.prj-card:hover { transform: translateY(-4px); box-shadow: 0 8px 24px rgba(0,0,0,0.2); }

.prj-card-header {
    display: flex; justify-content: space-between; align-items: center;
    padding: 16px 20px; background: rgba(0,0,0,0.2); border-radius: 12px 12px 0 0;
}
.prj-status { font-size: 0.75rem; padding: 4px 10px; border-radius: 20px; font-weight: 600; display: flex; align-items: center; gap: 6px; }
.prj-code { font-weight: 700; color: var(--primary-light); font-size: 0.9rem; }

.prj-card-body { padding: 20px; flex: 1; }
.prj-title { display: block; font-size: 1.2rem; font-weight: 700; margin-bottom: 12px; line-height: 1.3; }
.prj-client, .prj-location { font-size: 0.85rem; color: var(--text-muted); margin-bottom: 6px; display: flex; align-items: center; gap: 8px; }
.prj-cost-center {
    margin-top: 16px; padding: 10px; background: rgba(255,255,255,0.03);
    border-radius: 8px; border: 1px dashed rgba(255,255,255,0.1);
    font-size: 0.8rem; color: var(--text-secondary);
}

.prj-headcount-section { margin-top: 20px; }
.hc-labels { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 8px; font-size: 0.85rem; }
.progress-bar-wrapper { width: 100%; height: 6px; background: rgba(255,255,255,0.1); border-radius: 3px; overflow: hidden; }
.progress-bar { height: 100%; border-radius: 3px; transition: width 0.5s; }

.prj-card-footer {
    display: flex; justify-content: space-between; align-items: center;
    padding: 16px 20px; border-top: 1px solid rgba(255,255,255,0.06);
    background: rgba(255,255,255,0.01); border-radius: 0 0 12px 12px;
}
.prj-footer-item { font-size: 0.8rem; color: var(--text-muted); display: flex; align-items: center; gap: 6px; }

/* Modal */
.modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.6); z-index: 9999; display: flex; align-items: center; justify-content: center; backdrop-filter: blur(4px); }
.modal-content { background: var(--bg-card); border-radius: 16px; width: 90%; border: 1px solid rgba(255,255,255,0.08); }
.modal-header { display: flex; justify-content: space-between; align-items: center; padding: 20px 24px; border-bottom: 1px solid rgba(255,255,255,0.06); }
.modal-close { background: none; border: none; color: var(--text-muted); font-size: 1.5rem; cursor: pointer; }
.modal-body { padding: 24px; }
.modal-footer { padding: 16px 24px; border-top: 1px solid rgba(255,255,255,0.06); display: flex; justify-content: flex-end; gap: 12px; }
.form-group { margin-bottom: 0; }
.form-group label { display: block; margin-bottom: 6px; font-size: 0.85rem; color: var(--text-secondary); }
.form-group input, .form-group select { width: 100%; padding: 10px 14px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1); background: rgba(255,255,255,0.04); color: var(--text-primary); }
.btn { padding: 10px 20px; border-radius: 8px; border: none; cursor: pointer; font-weight: 600; font-size: 0.85rem; transition: all 0.2s; }
.btn-primary { background: var(--primary); color: #fff; }
.btn-secondary { background: rgba(255,255,255,0.08); color: var(--text-secondary); }
</style>
