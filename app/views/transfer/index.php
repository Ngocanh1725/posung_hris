<?php
/**
 * ============================================================
 *  View: transfer/index.php
 *  Danh sách Lệnh điều động (Transfer Orders)
 * ============================================================
 */
?>

<div class="panel">
    <div class="panel-header d-flex justify-content-between align-items-center">
        <h3><i class="fas fa-truck-loading"></i> Lệnh Điều Động Công Tác (Mobilization)</h3>
        <div>
            <?php if (Session::isManager() || Session::userRole() === 'Project_Manager'): ?>
                <a href="<?= BASE_URL ?>/transfer/create" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Tạo Lệnh mới
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Filter Tabs -->
    <div class="panel-body border-bottom p-0">
        <ul class="nav-tabs px-4 pt-3 m-0 border-0">
            <li class="tab-link <?= empty($currentStatus) ? 'active' : '' ?>">
                <a href="<?= BASE_URL ?>/transfer" class="text-decoration-none">Tất cả</a>
            </li>
            <li class="tab-link <?= $currentStatus === 'Pending' ? 'active' : '' ?>">
                <a href="<?= BASE_URL ?>/transfer?status=Pending" class="text-decoration-none">Chờ phê duyệt</a>
            </li>
            <li class="tab-link <?= $currentStatus === 'Approved' ? 'active' : '' ?>">
                <a href="<?= BASE_URL ?>/transfer?status=Approved" class="text-decoration-none">Đã hoàn thành</a>
            </li>
        </ul>
    </div>

    <div class="panel-body">
        <?php if (empty($orders)): ?>
            <div class="text-center text-muted p-5">
                <i class="fas fa-folder-open fa-3x mb-3"></i>
                <p>Chưa có lệnh điều động nào.</p>
            </div>
        <?php else: ?>
            <div class="table-wrapper">
                <table class="table table-striped table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Số QĐ</th>
                            <th>Ngày hiệu lực</th>
                            <th>Lộ trình (Từ -> Đến)</th>
                            <th>SL Nhân sự</th>
                            <th>Người lập</th>
                            <th>Trạng thái</th>
                            <th class="text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $o): ?>
                        <tr>
                            <td class="fw-bold text-primary"><?= h($o->decision_number) ?></td>
                            <td><?= date('d/m/Y', strtotime($o->effective_date)) ?></td>
                            <td>
                                <div class="small">
                                    <span class="text-muted">Từ:</span> 
                                    <strong><?= h($o->from_project ?? 'N/A') ?></strong>
                                </div>
                                <div class="small mt-1">
                                    <span class="text-muted">Đến:</span> 
                                    <strong class="text-success"><?= h($o->to_project ?? 'N/A') ?></strong>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-secondary rounded-pill px-3"><?= $o->employee_count ?></span>
                            </td>
                            <td>
                                <?= h($o->creator_name) ?><br>
                                <small class="text-muted"><?= date('d/m/Y H:i', strtotime($o->created_at)) ?></small>
                            </td>
                            <td>
                                <?php if ($o->status === 'Pending'): ?>
                                    <span class="badge" style="background: rgba(245,158,11,0.15); color: #f59e0b;">
                                        <i class="fas fa-clock"></i> Chờ duyệt
                                    </span>
                                <?php elseif ($o->status === 'Approved'): ?>
                                    <span class="badge badge-active">
                                        <i class="fas fa-check-circle"></i> Đã duyệt
                                    </span>
                                <?php else: ?>
                                    <span class="badge badge-resigned">Đã hủy</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <a href="<?= BASE_URL ?>/transfer/exportDecision/<?= $o->id ?>" target="_blank" class="btn btn-ghost btn-sm" title="In Quyết định">
                                    <i class="fas fa-print"></i>
                                </a>
                                
                                <?php if ($o->status === 'Pending' && Session::isManager()): ?>
                                    <form action="<?= BASE_URL ?>/transfer/approve/<?= $o->id ?>" method="POST" style="display:inline;" onsubmit="return confirm('Bạn có chắc chắn PHÊ DUYỆT lệnh này không? Hệ thống sẽ ngay lập tức cập nhật hồ sơ của <?= $o->employee_count ?> nhân sự sang Dự án mới!');">
                                        <input type="hidden" name="_csrf_token" value="<?= Session::generateCsrfToken() ?>">
                                        <button type="submit" class="btn btn-success btn-sm ml-1" title="Phê duyệt">
                                            <i class="fas fa-check"></i> Duyệt
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.d-flex { display: flex; }
.justify-content-between { justify-content: space-between; }
.align-items-center { align-items: center; }
.border-bottom { border-bottom: 1px solid var(--border); }
.border-0 { border: 0 !important; }
.p-0 { padding: 0 !important; }
.p-5 { padding: 3rem !important; }
.px-4 { padding-left: 1.5rem; padding-right: 1.5rem; }
.pt-3 { padding-top: 1rem; }
.m-0 { margin: 0 !important; }
.mb-3 { margin-bottom: 1rem; }
.mt-1 { margin-top: 0.25rem; }
.ml-1 { margin-left: 0.25rem; }
.text-decoration-none { text-decoration: none; color: inherit; }
.text-center { text-align: center; }
.text-muted { color: var(--text-muted); }
.text-success { color: #10b981; }
.text-primary { color: var(--primary-light); }
.fw-bold { font-weight: 600; }
.small { font-size: 13px; }
.bg-secondary { background: var(--bg-card); border: 1px solid var(--border); }
.rounded-pill { border-radius: 50rem; }
.px-3 { padding-left: 1rem; padding-right: 1rem; }
.align-middle td, .align-middle th { vertical-align: middle; }
.btn-success { background: linear-gradient(135deg, #10b981, #059669); color: white; border: none; }
.btn-success:hover { background: linear-gradient(135deg, #059669, #047857); }
</style>
