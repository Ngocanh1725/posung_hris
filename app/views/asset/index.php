<?php
/**
 * ============================================================
 *  POSUNG HRIS – View: asset/index.php
 * ============================================================
 *  Danh sách Quản lý Tài sản Doanh nghiệp (Table / Card view)
 * ============================================================
 */
?>
<div class="content-wrapper">
    <!-- TIÊU ĐỀ & THANH ĐIỀU HƯỚNG -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h2 class="mb-1 fw-bold text-dark"><i class="fas fa-laptop-house text-primary me-2"></i>Quản lý Tài sản Doanh nghiệp</h2>
            <p class="text-muted mb-0">Quản lý toàn bộ thiết bị CNTT, máy móc thi công, phương tiện, thẻ từ và trang thiết bị công ty</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>/asset/categories" class="btn btn-outline-secondary">
                <i class="fas fa-tags me-1"></i> Danh mục loại
            </a>
            <a href="<?= BASE_URL ?>/asset/report" class="btn btn-outline-info">
                <i class="fas fa-chart-pie me-1"></i> Báo cáo kiểm kê
            </a>
            <a href="<?= BASE_URL ?>/asset/create" class="btn btn-primary">
                <i class="fas fa-plus-circle me-1"></i> Thêm tài sản mới
            </a>
        </div>
    </div>

    <!-- THỐNG KÊ NHANH (KPI CARDS) -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 bg-white p-3 h-100 border-start border-4 border-primary">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Tổng số tài sản</span>
                        <h3 class="fw-bold mb-0 text-dark mt-1"><?= number_format($stats['total']) ?></h3>
                        <small class="text-primary"><i class="fas fa-boxes-stacked me-1"></i>Đang theo dõi</small>
                    </div>
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 text-primary fs-3">
                        <i class="fas fa-laptop-code"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 bg-white p-3 h-100 border-start border-4 border-success">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Đang sẵn sàng (Kho)</span>
                        <h3 class="fw-bold mb-0 text-success mt-1"><?= number_format($stats['available']) ?></h3>
                        <small class="text-muted">Chưa bàn giao</small>
                    </div>
                    <div class="rounded-circle bg-success bg-opacity-10 p-3 text-success fs-3">
                        <i class="fas fa-box-open"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 bg-white p-3 h-100 border-start border-4 border-warning">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Đang cấp phát (Sử dụng)</span>
                        <h3 class="fw-bold mb-0 text-warning mt-1"><?= number_format($stats['assigned']) ?></h3>
                        <small class="text-muted">Nhân viên đang giữ</small>
                    </div>
                    <div class="rounded-circle bg-warning bg-opacity-10 p-3 text-warning fs-3">
                        <i class="fas fa-user-check"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 bg-white p-3 h-100 border-start border-4 border-info">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Tổng nguyên giá đầu tư</span>
                        <h3 class="fw-bold mb-0 text-info mt-1"><?= number_format($stats['total_value'], 0, ',', '.') ?> ₫</h3>
                        <small class="text-muted">Đang bảo dưỡng: <?= number_format($stats['maintenance']) ?></small>
                    </div>
                    <div class="rounded-circle bg-info bg-opacity-10 p-3 text-info fs-3">
                        <i class="fas fa-coins"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- BỘ LỌC TÌM KIẾM & CHẾ ĐỘ XEM -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-3">
            <form action="<?= BASE_URL ?>/asset" method="GET" class="row g-2 align-items-center">
                <input type="hidden" name="view" value="<?= h($filters['view']) ?>" id="filterViewMode">

                <div class="col-md-3">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="fas fa-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Tên, mã TS, số serial, vị trí..." value="<?= h($filters['search']) ?>">
                    </div>
                </div>

                <div class="col-md-2">
                    <select name="category_id" class="form-select">
                        <option value="">-- Tất cả loại tài sản --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat->id ?>" <?= $filters['category_id'] == $cat->id ? 'selected' : '' ?>>
                                <?= h($cat->name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">-- Trạng thái --</option>
                        <option value="Available" <?= $filters['status'] === 'Available' ? 'selected' : '' ?>>Sẵn sàng (Available)</option>
                        <option value="Assigned" <?= $filters['status'] === 'Assigned' ? 'selected' : '' ?>>Đang cấp phát (Assigned)</option>
                        <option value="Maintenance" <?= $filters['status'] === 'Maintenance' ? 'selected' : '' ?>>Bảo dưỡng (Maintenance)</option>
                        <option value="Disposed" <?= $filters['status'] === 'Disposed' ? 'selected' : '' ?>>Đã thanh lý (Disposed)</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <select name="condition" class="form-select">
                        <option value="">-- Tình trạng --</option>
                        <option value="New" <?= $filters['condition'] === 'New' ? 'selected' : '' ?>>Mới 100% (New)</option>
                        <option value="Good" <?= $filters['condition'] === 'Good' ? 'selected' : '' ?>>Tốt (Good)</option>
                        <option value="Fair" <?= $filters['condition'] === 'Fair' ? 'selected' : '' ?>>Bình thường (Fair)</option>
                        <option value="Damaged" <?= $filters['condition'] === 'Damaged' ? 'selected' : '' ?>>Hư hỏng (Damaged)</option>
                    </select>
                </div>

                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1"><i class="fas fa-filter me-1"></i> Lọc</button>
                    <a href="<?= BASE_URL ?>/asset" class="btn btn-light" title="Đặt lại bộ lọc"><i class="fas fa-rotate-left"></i></a>
                    
                    <!-- Chuyển đổi View -->
                    <div class="btn-group" role="group">
                        <button type="button" class="btn <?= $filters['view'] === 'table' ? 'btn-secondary' : 'btn-outline-secondary' ?>" onclick="switchView('table')" title="Xem dạng bảng">
                            <i class="fas fa-table-list"></i>
                        </button>
                        <button type="button" class="btn <?= $filters['view'] === 'card' ? 'btn-secondary' : 'btn-outline-secondary' ?>" onclick="switchView('card')" title="Xem dạng thẻ">
                            <i class="fas fa-grip"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- NỘI DUNG DANH SÁCH TÀI SẢN -->
    <?php if (empty($assets)): ?>
        <div class="card border-0 shadow-sm rounded-3 text-center py-5">
            <div class="card-body">
                <i class="fas fa-box-open text-muted fa-4x mb-3"></i>
                <h5 class="text-secondary fw-semibold">Không tìm thấy tài sản nào phù hợp</h5>
                <p class="text-muted small">Hãy thử thay đổi điều kiện tìm kiếm hoặc thêm mới tài sản.</p>
                <a href="<?= BASE_URL ?>/asset/create" class="btn btn-primary mt-2">
                    <i class="fas fa-plus me-1"></i> Thêm mới tài sản
                </a>
            </div>
        </div>
    <?php elseif ($filters['view'] === 'card'): ?>
        <!-- CARD VIEW -->
        <div class="row g-3">
            <?php foreach ($assets as $asset): ?>
                <?php
                $statusBadge = match($asset->status) {
                    'Available'   => '<span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Sẵn sàng</span>',
                    'Assigned'    => '<span class="badge bg-warning text-dark"><i class="fas fa-user-check me-1"></i>Đang cấp phát</span>',
                    'Maintenance' => '<span class="badge bg-danger"><i class="fas fa-wrench me-1"></i>Bảo dưỡng</span>',
                    'Disposed'    => '<span class="badge bg-secondary"><i class="fas fa-archive me-1"></i>Đã thanh lý</span>',
                    default       => '<span class="badge bg-light text-dark">' . h($asset->status) . '</span>'
                };
                $condBadge = match($asset->condition) {
                    'New'     => '<span class="badge bg-info text-dark">Mới</span>',
                    'Good'    => '<span class="badge bg-primary">Tốt</span>',
                    'Fair'    => '<span class="badge bg-secondary">Bình thường</span>',
                    'Damaged' => '<span class="badge bg-danger">Hỏng</span>',
                    default   => '<span class="badge bg-light text-dark">' . h($asset->condition) . '</span>'
                };
                ?>
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <div class="card border-0 shadow-sm rounded-3 h-100 hover-shadow transition-all">
                        <div class="card-header bg-white border-0 pt-3 pb-0 d-flex justify-content-between align-items-center">
                            <span class="badge bg-light text-dark border font-monospace"><?= h($asset->asset_code) ?></span>
                            <?= $statusBadge ?>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <div class="rounded p-2 bg-light text-primary">
                                    <i class="<?= h($asset->category_icon ?? 'fas fa-cube') ?> fa-lg"></i>
                                </div>
                                <div>
                                    <small class="text-muted d-block"><?= h($asset->category_name) ?></small>
                                    <span class="fw-semibold text-truncate d-inline-block" style="max-width: 190px;" title="<?= h($asset->name) ?>"><?= h($asset->name) ?></span>
                                </div>
                            </div>

                            <div class="small text-muted mb-3 space-y-1">
                                <?php if ($asset->serial_number): ?>
                                    <div><i class="fas fa-barcode me-1 text-secondary"></i> SN: <span class="fw-medium font-monospace"><?= h($asset->serial_number) ?></span></div>
                                <?php endif; ?>
                                <div><i class="fas fa-shield-halved me-1 text-secondary"></i> Tình trạng: <?= $condBadge ?></div>
                                <?php if ($asset->location): ?>
                                    <div><i class="fas fa-location-dot me-1 text-secondary"></i> <?= h($asset->location) ?></div>
                                <?php endif; ?>
                                <div><i class="fas fa-tag me-1 text-secondary"></i> <?= number_format($asset->purchase_cost ?? 0, 0, ',', '.') ?> ₫</div>
                            </div>

                            <!-- KHỐI NGƯỜI ĐANG NẮM GIỮ -->
                            <div class="p-2 rounded bg-light border small">
                                <?php if ($asset->status === 'Assigned' && !empty($asset->holder_name)): ?>
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div>
                                            <i class="fas fa-user-circle text-primary me-1"></i>
                                            <a href="<?= BASE_URL ?>/employee/view/<?= $asset->current_holder_id ?>" class="text-decoration-none fw-semibold text-dark">
                                                <?= h($asset->holder_name) ?>
                                            </a>
                                            <span class="text-muted d-block small"><?= h($asset->holder_dept ?? '') ?></span>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2" onclick="openReturnModal(<?= $asset->id ?>, <?= $asset->current_assignment_id ?>, '<?= h($asset->name) ?>', '<?= h($asset->holder_name) ?>')" title="Thu hồi tài sản">
                                            Thu hồi
                                        </button>
                                    </div>
                                <?php else: ?>
                                    <div class="d-flex align-items-center justify-content-between text-muted">
                                        <span><i class="fas fa-warehouse me-1"></i> Đang lưu kho</span>
                                        <?php if ($asset->status === 'Available'): ?>
                                            <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2" onclick="openAssignModal(<?= $asset->id ?>, '<?= h($asset->asset_code) ?>', '<?= h($asset->name) ?>')" title="Bàn giao tài sản">
                                                Giao ngay
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-top-0 pt-0 pb-3 d-flex justify-content-between">
                            <a href="<?= BASE_URL ?>/asset/show/<?= $asset->id ?>" class="btn btn-sm btn-light text-primary flex-grow-1 me-1">
                                <i class="fas fa-eye me-1"></i> Chi tiết
                            </a>
                            <a href="<?= BASE_URL ?>/asset/edit/<?= $asset->id ?>" class="btn btn-sm btn-light text-secondary px-2 me-1" title="Chỉnh sửa">
                                <i class="fas fa-pen"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <!-- TABLE VIEW -->
        <div class="card border-0 shadow-sm rounded-3">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3" style="width: 140px;">Mã tài sản</th>
                            <th>Tên & Thông số tài sản</th>
                            <th>Phân loại</th>
                            <th>Tình trạng</th>
                            <th>Vị trí / Đơn vị</th>
                            <th>Người nắm giữ</th>
                            <th>Nguyên giá</th>
                            <th>Trạng thái</th>
                            <th class="text-end pe-3" style="width: 150px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($assets as $asset): ?>
                            <?php
                            $statusBadge = match($asset->status) {
                                'Available'   => '<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1"><i class="fas fa-check-circle me-1"></i>Sẵn sàng</span>',
                                'Assigned'    => '<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1"><i class="fas fa-user-check me-1"></i>Đang cấp phát</span>',
                                'Maintenance' => '<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1"><i class="fas fa-wrench me-1"></i>Bảo dưỡng</span>',
                                'Disposed'    => '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1"><i class="fas fa-archive me-1"></i>Đã thanh lý</span>',
                                default       => '<span class="badge bg-light text-dark">' . h($asset->status) . '</span>'
                            };
                            $condBadge = match($asset->condition) {
                                'New'     => '<span class="badge bg-info text-dark">Mới</span>',
                                'Good'    => '<span class="badge bg-primary">Tốt</span>',
                                'Fair'    => '<span class="badge bg-secondary">Bình thường</span>',
                                'Damaged' => '<span class="badge bg-danger">Hư hỏng</span>',
                                default   => '<span class="badge bg-light text-dark">' . h($asset->condition) . '</span>'
                            };
                            ?>
                            <tr>
                                <td class="ps-3">
                                    <a href="<?= BASE_URL ?>/asset/show/<?= $asset->id ?>" class="font-monospace fw-bold text-primary text-decoration-none">
                                        <?= h($asset->asset_code) ?>
                                    </a>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark"><?= h($asset->name) ?></div>
                                    <?php if ($asset->serial_number): ?>
                                        <small class="text-muted font-monospace"><i class="fas fa-barcode me-1"></i>SN: <?= h($asset->serial_number) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        <i class="<?= h($asset->category_icon ?? 'fas fa-cube') ?> text-secondary me-1"></i><?= h($asset->category_name) ?>
                                    </span>
                                </td>
                                <td><?= $condBadge ?></td>
                                <td>
                                    <small class="text-muted"><?= h($asset->location ?: 'Kho công ty') ?></small>
                                </td>
                                <td>
                                    <?php if ($asset->status === 'Assigned' && !empty($asset->holder_name)): ?>
                                        <div>
                                            <a href="<?= BASE_URL ?>/employee/view/<?= $asset->current_holder_id ?>" class="text-decoration-none fw-semibold text-dark">
                                                <i class="fas fa-user-circle text-primary me-1"></i><?= h($asset->holder_name) ?>
                                            </a>
                                        </div>
                                        <small class="text-muted d-block"><?= h($asset->holder_dept ?? '') ?> (Từ <?= fmtDate($asset->assigned_date) ?>)</small>
                                    <?php else: ?>
                                        <span class="text-muted small fst-italic">-- Trong kho --</span>
                                    <?php endif; ?>
                                </td>
                                <td class="font-monospace fw-medium text-end pe-3">
                                    <?= number_format($asset->purchase_cost ?? 0, 0, ',', '.') ?> ₫
                                </td>
                                <td><?= $statusBadge ?></td>
                                <td class="text-end pe-3">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= BASE_URL ?>/asset/show/<?= $asset->id ?>" class="btn btn-light" title="Xem chi tiết">
                                            <i class="fas fa-eye text-primary"></i>
                                        </a>

                                        <?php if ($asset->status === 'Available'): ?>
                                            <button type="button" class="btn btn-light" onclick="openAssignModal(<?= $asset->id ?>, '<?= h($asset->asset_code) ?>', '<?= h($asset->name) ?>')" title="Bàn giao cho nhân viên">
                                                <i class="fas fa-hand-holding-hand text-success"></i>
                                            </button>
                                        <?php elseif ($asset->status === 'Assigned'): ?>
                                            <button type="button" class="btn btn-light" onclick="openReturnModal(<?= $asset->id ?>, <?= $asset->current_assignment_id ?>, '<?= h($asset->name) ?>', '<?= h($asset->holder_name) ?>')" title="Thu hồi tài sản">
                                                <i class="fas fa-rotate-left text-danger"></i>
                                            </button>
                                        <?php endif; ?>

                                        <a href="<?= BASE_URL ?>/asset/edit/<?= $asset->id ?>" class="btn btn-light" title="Sửa thông tin">
                                            <i class="fas fa-pen text-secondary"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- ==================== MODAL BÀN GIAO TÀI SẢN ==================== -->
<div class="modal fade" id="assignModal" tabindex="-1" aria-labelledby="assignModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="" method="POST" id="assignForm" class="modal-content border-0 shadow">
            <input type="hidden" name="_csrf_token" value="<?= Session::generateCsrfToken() ?>">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="assignModalLabel"><i class="fas fa-hand-holding-hand me-2"></i>Bàn giao Tài sản cho Nhân viên</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="p-3 bg-light rounded mb-3 border">
                    <span class="text-muted small d-block">Tài sản bàn giao:</span>
                    <strong class="text-primary fs-6" id="modalAssetInfo">---</strong>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Nhân viên tiếp nhận <span class="text-danger">*</span></label>
                    <select name="employee_id" class="form-select" required id="assignEmployeeSelect">
                        <option value="">-- Chọn nhân viên tiếp nhận --</option>
                        <?php foreach ($employees as $emp): ?>
                            <option value="<?= $emp->id ?>">
                                <?= h($emp->emp_code) ?> - <?= h($emp->full_name) ?> (<?= h($emp->dept_name ?? 'N/A') ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Ngày bàn giao <span class="text-danger">*</span></label>
                        <input type="date" name="assigned_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Tình trạng lúc giao</label>
                        <select name="condition_on_assign" class="form-select">
                            <option value="New">Mới 100%</option>
                            <option value="Good" selected>Hoạt động tốt</option>
                            <option value="Fair">Bình thường (Đã qua sử dụng)</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Ghi chú bàn giao / Phụ kiện kèm theo</label>
                    <textarea name="notes" class="form-control" rows="3" placeholder="Ví dụ: Kèm sạc zin, chuột, túi xách, cáp kết nối..."></textarea>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy bỏ</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-check me-1"></i> Xác nhận bàn giao</button>
            </div>
        </form>
    </div>
</div>

<!-- ==================== MODAL THU HỒI TÀI SẢN ==================== -->
<div class="modal fade" id="returnModal" tabindex="-1" aria-labelledby="returnModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="" method="POST" id="returnForm" class="modal-content border-0 shadow">
            <input type="hidden" name="_csrf_token" value="<?= Session::generateCsrfToken() ?>">
            <input type="hidden" name="assignment_id" id="returnAssignmentId" value="">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="returnModalLabel"><i class="fas fa-rotate-left me-2"></i>Thu hồi Tài sản về Kho</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="p-3 bg-light rounded mb-3 border">
                    <div class="mb-1"><span class="text-muted small">Tài sản thu hồi:</span> <strong id="returnAssetTitle" class="text-dark">---</strong></div>
                    <div><span class="text-muted small">Nhân viên hoàn trả:</span> <strong id="returnHolderName" class="text-primary">---</strong></div>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Ngày thu hồi <span class="text-danger">*</span></label>
                        <input type="date" name="return_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Tình trạng lúc thu hồi</label>
                        <select name="condition_on_return" class="form-select">
                            <option value="Good" selected>Tốt / Bình thường</option>
                            <option value="Fair">Có trầy xước nhẹ</option>
                            <option value="Damaged">Hư hỏng (Chuyển bảo dưỡng)</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Ghi chú kiểm tra & lý do hoàn trả</label>
                    <textarea name="notes" class="form-control" rows="3" placeholder="Đã kiểm tra đầy đủ phụ kiện, máy móc hoạt động bình thường hoặc ghi chú hư hại nếu có..."></textarea>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy bỏ</button>
                <button type="submit" class="btn btn-danger"><i class="fas fa-check me-1"></i> Xác nhận hoàn tất thu hồi</button>
            </div>
        </form>
    </div>
</div>

<script>
function switchView(mode) {
    document.getElementById('filterViewMode').value = mode;
    document.getElementById('filterViewMode').form.submit();
}

function openAssignModal(assetId, assetCode, assetName) {
    document.getElementById('modalAssetInfo').textContent = assetCode + ' - ' + assetName;
    document.getElementById('assignForm').action = '<?= BASE_URL ?>/asset/assign/' + assetId;
    var myModal = new bootstrap.Modal(document.getElementById('assignModal'));
    myModal.show();
}

function openReturnModal(assetId, assignmentId, assetName, holderName) {
    document.getElementById('returnAssignmentId').value = assignmentId;
    document.getElementById('returnAssetTitle').textContent = assetName;
    document.getElementById('returnHolderName').textContent = holderName;
    document.getElementById('returnForm').action = '<?= BASE_URL ?>/asset/return/' + assetId;
    var myModal = new bootstrap.Modal(document.getElementById('returnModal'));
    myModal.show();
}
</script>
