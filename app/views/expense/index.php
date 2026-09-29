<?php
/**
 * ============================================================
 *  POSUNG HRIS – View: expense/index.php
 * ============================================================
 *  Màn hình chính: Quản lý Công tác & Quyết toán Chi phí
 * ============================================================
 */
?>
<div class="content-wrapper">
    <!-- TIÊU ĐỀ & THANH ĐIỀU HƯỚNG -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h2 class="mb-1 fw-bold text-dark"><i class="fas fa-plane-departure text-primary me-2"></i>Quản lý Công tác & Quyết toán Chi phí</h2>
            <p class="text-muted mb-0">Theo dõi kế hoạch đi công tác công trường, tạm ứng và thanh quyết toán tài chính dự án</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>/expense/report" class="btn btn-outline-info">
                <i class="fas fa-chart-pie me-1"></i> Báo cáo chi phí
            </a>
            <a href="<?= BASE_URL ?>/expense/createTravel" class="btn btn-outline-primary">
                <i class="fas fa-calendar-plus me-1"></i> Đề xuất công tác
            </a>
            <a href="<?= BASE_URL ?>/expense/createClaim" class="btn btn-primary">
                <i class="fas fa-receipt me-1"></i> Lập quyết toán chi phí
            </a>
        </div>
    </div>

    <!-- THỐNG KÊ TỔNG HỢP (KPI CARDS) -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 bg-white p-3 h-100 border-start border-4 border-primary">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Ngân sách công tác</span>
                        <h3 class="fw-bold mb-0 text-dark mt-1"><?= number_format($travelStats['total_budget'] ?? 0, 0, ',', '.') ?> <small class="fs-6 text-muted">₫</small></h3>
                        <small class="text-primary"><i class="fas fa-suitcase me-1"></i><?= $travelStats['total_requests'] ?? 0 ?> đợt công tác</small>
                    </div>
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 text-primary fs-3">
                        <i class="fas fa-route"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 bg-white p-3 h-100 border-start border-4 border-warning">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Tổng chi phí quyết toán</span>
                        <h3 class="fw-bold mb-0 text-warning mt-1"><?= number_format($claimStats['total_amount'] ?? 0, 0, ',', '.') ?> <small class="fs-6 text-muted">₫</small></h3>
                        <small class="text-muted"><i class="fas fa-file-invoice me-1"></i><?= $claimStats['total_claims'] ?? 0 ?> bảng quyết toán</small>
                    </div>
                    <div class="rounded-circle bg-warning bg-opacity-10 p-3 text-warning fs-3">
                        <i class="fas fa-calculator"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 bg-white p-3 h-100 border-start border-4 border-success">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Đã thực chi thanh toán</span>
                        <h3 class="fw-bold mb-0 text-success mt-1"><?= number_format($claimStats['total_net_payable'] ?? 0, 0, ',', '.') ?> <small class="fs-6 text-muted">₫</small></h3>
                        <small class="text-success"><i class="fas fa-check-circle me-1"></i><?= $claimStats['paid_count'] ?? 0 ?> hồ sơ đã nhận tiền</small>
                    </div>
                    <div class="rounded-circle bg-success bg-opacity-10 p-3 text-success fs-3">
                        <i class="fas fa-money-check-dollar"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 bg-white p-3 h-100 border-start border-4 border-danger">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Chờ duyệt & chi trả</span>
                        <h3 class="fw-bold mb-0 text-danger mt-1"><?= (int)($travelStats['pending_count'] ?? 0) + (int)($claimStats['submitted_count'] ?? 0) ?></h3>
                        <small class="text-danger"><i class="fas fa-clock me-1"></i><?= $travelStats['pending_count'] ?? 0 ?> chuyến, <?= $claimStats['submitted_count'] ?? 0 ?> bảng chi</small>
                    </div>
                    <div class="rounded-circle bg-danger bg-opacity-10 p-3 text-danger fs-3">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- THANH ĐIỀU HƯỚNG TAB LỰA CHỌN -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-white p-2 border-bottom-0">
            <ul class="nav nav-pills nav-fill" id="expenseTabs">
                <li class="nav-item">
                    <a class="nav-link fw-semibold py-2 <?= $activeTab === 'travel' ? 'active' : '' ?>" href="<?= BASE_URL ?>/expense?tab=travel">
                        <i class="fas fa-plane-departure me-2"></i>1. Kế hoạch & Đề xuất Công tác (<?= count($travels) ?>)
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-semibold py-2 <?= $activeTab === 'claims' ? 'active' : '' ?>" href="<?= BASE_URL ?>/expense?tab=claims">
                        <i class="fas fa-receipt me-2"></i>2. Bảng Thanh Quyết toán Chi phí (<?= count($claims) ?>)
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- BỘ LỌC TÌM KIẾM -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-3">
            <form action="<?= BASE_URL ?>/expense" method="GET" class="row g-2 align-items-center">
                <input type="hidden" name="tab" value="<?= h($activeTab) ?>">

                <div class="col-md-3">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Mã hồ sơ, Tên NV, Mục đích..." value="<?= h($filters['search']) ?>">
                    </div>
                </div>

                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">-- Trạng thái --</option>
                        <?php if ($activeTab === 'travel'): ?>
                            <option value="Pending" <?= $filters['status'] === 'Pending' ? 'selected' : '' ?>>Chờ duyệt</option>
                            <option value="Approved" <?= $filters['status'] === 'Approved' ? 'selected' : '' ?>>Đã duyệt</option>
                            <option value="Completed" <?= $filters['status'] === 'Completed' ? 'selected' : '' ?>>Đã hoàn tất</option>
                            <option value="Rejected" <?= $filters['status'] === 'Rejected' ? 'selected' : '' ?>>Bị từ chối</option>
                        <?php else: ?>
                            <option value="Submitted" <?= $filters['status'] === 'Submitted' ? 'selected' : '' ?>>Chờ duyệt chi</option>
                            <option value="Approved" <?= $filters['status'] === 'Approved' ? 'selected' : '' ?>>Đã duyệt (Chờ chi)</option>
                            <option value="Paid" <?= $filters['status'] === 'Paid' ? 'selected' : '' ?>>Đã thanh toán</option>
                            <option value="Rejected" <?= $filters['status'] === 'Rejected' ? 'selected' : '' ?>>Bị từ chối</option>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <select name="project_id" class="form-select">
                        <option value="">-- Dự án / Công trường --</option>
                        <?php foreach ($projects as $prj): ?>
                            <option value="<?= $prj->id ?>" <?= $filters['project_id'] == $prj->id ? 'selected' : '' ?>>
                                <?= h($prj->project_name) ?> (<?= h($prj->location ?? 'N/A') ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <select name="department_id" class="form-select">
                        <option value="">-- Phòng ban --</option>
                        <?php foreach ($departments as $d): ?>
                            <option value="<?= $d->id ?>" <?= $filters['department_id'] == $d->id ? 'selected' : '' ?>>
                                <?= h($d->dept_name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill">
                        <i class="fas fa-filter me-1"></i> Lọc
                    </button>
                    <a href="<?= BASE_URL ?>/expense?tab=<?= $activeTab ?>" class="btn btn-light border" title="Đặt lại">
                        <i class="fas fa-undo"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- NỘI DUNG TAB 1: KẾ HOẠCH & ĐỀ XUẤT CÔNG TÁC -->
    <?php if ($activeTab === 'travel'): ?>
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-dark">
                    <i class="fas fa-suitcase-rolling text-primary me-2"></i>Danh sách Đề xuất Công tác
                    <span class="badge bg-light text-dark ms-2"><?= count($travels) ?> chuyến</span>
                </h5>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-muted small text-uppercase">
                        <tr>
                            <th class="ps-3" style="width: 140px;">Mã chuyến</th>
                            <th>Cán bộ công tác</th>
                            <th>Mục đích & Dự án</th>
                            <th>Lộ trình (Từ &rarr; Đến)</th>
                            <th>Thời gian công tác</th>
                            <th class="text-end">Ngân sách / Tạm ứng</th>
                            <th class="text-center">Trạng thái</th>
                            <th class="text-end pe-3">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($travels)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="fas fa-map-location-dot fs-1 d-block mb-3 opacity-50"></i>
                                    Không tìm thấy đề xuất công tác nào phù hợp.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($travels as $tr): ?>
                                <?php
                                $statusBadge = match($tr->status) {
                                    'Pending'   => '<span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i>Chờ duyệt</span>',
                                    'Approved'  => '<span class="badge bg-info text-white"><i class="fas fa-check me-1"></i>Đã duyệt</span>',
                                    'Completed' => '<span class="badge bg-success text-white"><i class="fas fa-circle-check me-1"></i>Đã hoàn tất</span>',
                                    'Rejected'  => '<span class="badge bg-danger text-white"><i class="fas fa-ban me-1"></i>Từ chối</span>',
                                    default     => '<span class="badge bg-secondary">' . h($tr->status) . '</span>'
                                };
                                $days = max(1, round((strtotime($tr->return_date) - strtotime($tr->departure_date)) / 86400) + 1);
                                ?>
                                <tr>
                                    <td class="ps-3">
                                        <a href="<?= BASE_URL ?>/expense/showTravel/<?= $tr->id ?>" class="fw-bold text-primary text-decoration-none">
                                            <?= h($tr->request_code) ?>
                                        </a>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if (!empty($tr->avatar_path)): ?>
                                                <img src="<?= BASE_URL ?>/<?= h($tr->avatar_path) ?>" alt="Avatar" class="rounded-circle me-2" style="width: 34px; height: 34px; object-fit: cover;">
                                            <?php else: ?>
                                                <div class="rounded-circle bg-primary bg-opacity-10 text-primary fw-bold d-flex align-items-center justify-content-center me-2" style="width: 34px; height: 34px; font-size: 13px;">
                                                    <?= mb_substr($tr->employee_name, 0, 1) ?>
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <a href="<?= BASE_URL ?>/employee/detail/<?= $tr->employee_id ?>" class="fw-semibold text-dark text-decoration-none d-block">
                                                    <?= h($tr->employee_name) ?>
                                                </a>
                                                <small class="text-muted"><?= h($tr->employee_code) ?> &bull; <?= h($tr->dept_name ?? 'N/A') ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark" style="max-width: 250px;"><?= h($tr->purpose) ?></div>
                                        <?php if (!empty($tr->project_name)): ?>
                                            <span class="badge bg-light text-primary border mt-1">
                                                <i class="fas fa-hard-hat me-1"></i><?= h($tr->project_name) ?>
                                            </span>
                                        <?php else: ?>
                                            <small class="text-muted d-block mt-1">Công tác văn phòng</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="text-dark small"><i class="fas fa-location-dot text-danger me-1"></i><?= h($tr->to_location) ?></div>
                                        <small class="text-muted">Từ: <?= h($tr->from_location) ?></small>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark"><?= date('d/m/Y', strtotime($tr->departure_date)) ?> &rarr; <?= date('d/m/Y', strtotime($tr->return_date)) ?></div>
                                        <small class="badge bg-light text-muted border"><?= $days ?> ngày</small>
                                    </td>
                                    <td class="text-end">
                                        <div class="fw-bold text-dark"><?= number_format((float)$tr->estimated_budget, 0, ',', '.') ?> ₫</div>
                                        <?php if ((float)$tr->advance_amount > 0): ?>
                                            <small class="text-primary d-block">Tạm ứng: <?= number_format((float)$tr->advance_amount, 0, ',', '.') ?> ₫</small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?= $statusBadge ?>
                                    </td>
                                    <td class="text-end pe-3">
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= BASE_URL ?>/expense/showTravel/<?= $tr->id ?>" class="btn btn-outline-primary" title="Xem chi tiết">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if ($tr->status === 'Pending'): ?>
                                                <button type="button" class="btn btn-outline-success" title="Phê duyệt nhanh" onclick="openApproveTravelModal(<?= $tr->id ?>, '<?= h($tr->request_code) ?>', '<?= h($tr->employee_name) ?>')">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            <?php elseif (in_array($tr->status, ['Approved', 'Completed'])): ?>
                                                <a href="<?= BASE_URL ?>/expense/createClaim?travel_id=<?= $tr->id ?>" class="btn btn-outline-warning" title="Tạo bảng quyết toán chi phí cho chuyến này">
                                                    <i class="fas fa-receipt"></i>
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

    <!-- NỘI DUNG TAB 2: BẢNG THANH QUYẾT TOÁN CHI PHÍ -->
    <?php else: ?>
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-dark">
                    <i class="fas fa-file-invoice-dollar text-primary me-2"></i>Danh sách Bảng Quyết toán Chi phí
                    <span class="badge bg-light text-dark ms-2"><?= count($claims) ?> hồ sơ</span>
                </h5>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-muted small text-uppercase">
                        <tr>
                            <th class="ps-3" style="width: 140px;">Mã quyết toán</th>
                            <th>Tiêu đề & Dự án</th>
                            <th>Người đề nghị</th>
                            <th class="text-end">Tổng chi phí</th>
                            <th class="text-end">Đã trừ tạm ứng</th>
                            <th class="text-end">Thực nhận / Chi trả</th>
                            <th>Ngày nộp</th>
                            <th class="text-center">Trạng thái</th>
                            <th class="text-end pe-3">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($claims)): ?>
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="fas fa-receipt fs-1 d-block mb-3 opacity-50"></i>
                                    Không tìm thấy bảng quyết toán chi phí nào phù hợp.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($claims as $claim): ?>
                                <?php
                                $claimBadge = match($claim->status) {
                                    'Submitted' => '<span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i>Chờ duyệt chi</span>',
                                    'Approved'  => '<span class="badge bg-info text-white"><i class="fas fa-check me-1"></i>Đã duyệt (Chờ chi)</span>',
                                    'Paid'      => '<span class="badge bg-success text-white"><i class="fas fa-money-bill-check me-1"></i>Đã chi trả</span>',
                                    'Rejected'  => '<span class="badge bg-danger text-white"><i class="fas fa-ban me-1"></i>Từ chối</span>',
                                    default     => '<span class="badge bg-secondary">' . h($claim->status) . '</span>'
                                };
                                ?>
                                <tr>
                                    <td class="ps-3">
                                        <a href="<?= BASE_URL ?>/expense/showClaim/<?= $claim->id ?>" class="fw-bold text-primary text-decoration-none">
                                            <?= h($claim->claim_code) ?>
                                        </a>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark"><?= h($claim->title) ?></div>
                                        <div class="small text-muted mt-1">
                                            <span class="badge bg-light text-dark border"><?= h($claim->category) ?></span>
                                            <?php if (!empty($claim->project_name)): ?>
                                                &bull; <i class="fas fa-hard-hat text-warning me-1"></i><?= h($claim->project_name) ?>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark"><?= h($claim->employee_name) ?></div>
                                        <small class="text-muted"><?= h($claim->employee_code) ?> &bull; <?= h($claim->dept_name ?? 'N/A') ?></small>
                                    </td>
                                    <td class="text-end fw-bold text-dark">
                                        <?= number_format((float)$claim->total_amount, 0, ',', '.') ?> ₫
                                        <small class="text-muted d-block"><?= $claim->items_count ?> khoản chi</small>
                                    </td>
                                    <td class="text-end text-muted">
                                        <?= number_format((float)$claim->advance_deducted, 0, ',', '.') ?> ₫
                                    </td>
                                    <td class="text-end fw-bold text-success fs-6">
                                        <?= number_format((float)$claim->net_payable, 0, ',', '.') ?> ₫
                                    </td>
                                    <td class="text-muted small">
                                        <?= date('d/m/Y', strtotime($claim->submitted_date)) ?>
                                    </td>
                                    <td class="text-center">
                                        <?= $claimBadge ?>
                                    </td>
                                    <td class="text-end pe-3">
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= BASE_URL ?>/expense/showClaim/<?= $claim->id ?>" class="btn btn-outline-primary" title="Xem chi tiết">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?= BASE_URL ?>/expense/printClaim/<?= $claim->id ?>" target="_blank" class="btn btn-outline-secondary" title="In phiếu quyết toán">
                                                <i class="fas fa-print"></i>
                                            </a>
                                            <?php if ($claim->status === 'Submitted'): ?>
                                                <button type="button" class="btn btn-outline-success" title="Duyệt quyết toán" onclick="openApproveClaimModal(<?= $claim->id ?>, '<?= h($claim->claim_code) ?>', '<?= number_format((float)$claim->net_payable, 0, ',', '.') ?> ₫')">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            <?php elseif ($claim->status === 'Approved'): ?>
                                                <button type="button" class="btn btn-outline-success" title="Xác nhận chi tiền" onclick="openPayClaimModal(<?= $claim->id ?>, '<?= h($claim->claim_code) ?>', '<?= number_format((float)$claim->net_payable, 0, ',', '.') ?> ₫')">
                                                    <i class="fas fa-hand-holding-dollar"></i>
                                                </button>
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
    <?php endif; ?>
</div>

<!-- MODAL DUYỆT CÔNG TÁC NHANH -->
<div class="modal fade" id="approveTravelModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="" method="POST" id="approveTravelForm" class="modal-content border-0 shadow">
            <input type="hidden" name="_csrf_token" value="<?= Session::generateCsrfToken() ?>">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-circle-check me-2"></i>Phê duyệt Kế hoạch Công tác</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <p>Bạn sắp xác nhận phê duyệt kế hoạch công tác <strong id="modalTravelCode" class="text-primary"></strong> cho nhân viên <strong id="modalTravelEmp"></strong>.</p>
                <div class="alert alert-info border-0 rounded-3 small">
                    <i class="fas fa-info-circle me-1"></i> Sau khi duyệt, nhân viên có thể nhận tiền tạm ứng và lập bảng quyết toán chi phí khi công tác hoàn tất.
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy bỏ</button>
                <button type="submit" class="btn btn-success"><i class="fas fa-check me-1"></i> Xác nhận Phê duyệt</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL DUYỆT QUYẾT TOÁN NHANH -->
<div class="modal fade" id="approveClaimModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="" method="POST" id="approveClaimForm" class="modal-content border-0 shadow">
            <input type="hidden" name="_csrf_token" value="<?= Session::generateCsrfToken() ?>">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-circle-check me-2"></i>Phê duyệt Quyết toán Chi phí</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <p>Xác nhận phê duyệt bảng quyết toán <strong id="modalClaimCode" class="text-primary"></strong>.</p>
                <div class="d-flex justify-content-between p-3 bg-light rounded-3 mb-3">
                    <span class="text-muted">Số tiền thực thanh toán:</span>
                    <strong id="modalClaimAmount" class="text-success fs-5"></strong>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="submit" class="btn btn-success"><i class="fas fa-check me-1"></i> Phê duyệt</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL CHI TRẢ NHANH -->
<div class="modal fade" id="payClaimModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="" method="POST" id="payClaimForm" class="modal-content border-0 shadow">
            <input type="hidden" name="_csrf_token" value="<?= Session::generateCsrfToken() ?>">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-money-check-dollar me-2"></i>Xác nhận Chi trả Tiền Quyết toán</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="d-flex justify-content-between p-3 bg-light rounded-3 mb-3">
                    <span class="text-muted">Hồ sơ: <strong id="modalPayCode" class="text-primary"></strong></span>
                    <span class="text-muted">Số tiền chi: <strong id="modalPayAmount" class="text-success fs-5"></strong></span>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Ngày chi tiền <span class="text-danger">*</span></label>
                    <input type="date" name="paid_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Hình thức chi trả</label>
                    <select name="payment_method" class="form-select">
                        <option value="Bank Transfer">Chuyển khoản Ngân hàng (Khuyên dùng)</option>
                        <option value="Cash">Chi tiền mặt qua Thủ quỹ</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-check me-1"></i> Xác nhận Đã chi tiền</button>
            </div>
        </form>
    </div>
</div>

<script>
function openApproveTravelModal(id, code, emp) {
    document.getElementById('approveTravelForm').action = '<?= BASE_URL ?>/expense/approveTravel/' + id;
    document.getElementById('modalTravelCode').innerText = code;
    document.getElementById('modalTravelEmp').innerText = emp;
    new bootstrap.Modal(document.getElementById('approveTravelModal')).show();
}

function openApproveClaimModal(id, code, amount) {
    document.getElementById('approveClaimForm').action = '<?= BASE_URL ?>/expense/approveClaim/' + id;
    document.getElementById('modalClaimCode').innerText = code;
    document.getElementById('modalClaimAmount').innerText = amount;
    new bootstrap.Modal(document.getElementById('approveClaimModal')).show();
}

function openPayClaimModal(id, code, amount) {
    document.getElementById('payClaimForm').action = '<?= BASE_URL ?>/expense/markPaid/' + id;
    document.getElementById('modalPayCode').innerText = code;
    document.getElementById('modalPayAmount').innerText = amount;
    new bootstrap.Modal(document.getElementById('payClaimModal')).show();
}
</script>
