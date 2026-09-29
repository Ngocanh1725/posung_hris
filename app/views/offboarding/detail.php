<?php
/**
 * ============================================================
 *  POSUNG HRIS – View: offboarding/detail.php
 *  Chi tiết Hồ sơ Thôi việc & Checklist Bàn giao Đa phòng ban
 * ============================================================
 */
?>

<div class="offboarding-detail-page">
    <!-- Breadcrumb & Actions -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <a href="<?= BASE_URL ?>/offboarding" class="text-decoration-none text-muted small">
                <i class="fa-solid fa-arrow-left me-1"></i> Quay lại Danh sách Thôi việc
            </a>
            <h4 class="fw-bold text-dark mt-1 mb-0">
                Hồ sơ Thôi việc: <?= h($offboarding['employee_name']) ?> 
                <span class="badge bg-secondary font-monospace fs-6"><?= h($offboarding['emp_code']) ?></span>
            </h4>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm" onclick="window.print()">
                <i class="fa-solid fa-print me-1"></i> In Biên bản Bàn giao
            </button>
            <?php if ($offboarding['status'] === 'InProgress'): ?>
                <form action="<?= BASE_URL ?>/offboarding/complete/<?= $offboarding['id'] ?>" method="POST" id="formCompleteOffboarding"
                      onsubmit="return confirm('XÁC NHẬN: Bạn có chắc chắn muốn chốt hoàn tất quy trình thôi việc? Trạng thái của nhân viên sẽ tự động chuyển sang ĐÃ NGHỈ và tài khoản làm việc sẽ bị vô hiệu hóa.');">
                    <button type="submit" class="btn btn-danger btn-sm rounded-pill px-3 shadow-sm" id="btnCompleteOffboarding" 
                            <?= !$offboarding['can_complete'] ? 'disabled title="Còn mục bắt buộc chưa hoàn thành"' : '' ?>>
                        <i class="fa-solid fa-power-off me-1"></i> Chốt Thôi việc & Đổi trạng thái NV
                    </button>
                </form>
            <?php else: ?>
                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill fs-6">
                    <i class="fa-solid fa-circle-check me-1"></i> Đã hoàn tất thôi việc (<?= date('d/m/Y', strtotime($offboarding['clearance_date'])) ?>)
                </span>
            <?php endif; ?>
        </div>
    </div>

    <!-- Alert Flash Messages -->
    <?php if ($flash = Session::getFlash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i><?= h($flash) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if ($flash = Session::getFlash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
            <i class="fa-solid fa-triangle-exclamation me-2"></i><?= h($flash) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Employee Overview Banner -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white overflow-hidden">
        <div class="card-body p-4">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <div class="d-flex align-items-start gap-3">
                        <div class="rounded-circle bg-primary text-white fw-bold d-flex align-items-center justify-content-center flex-shrink-0" 
                             style="width: 56px; height: 56px; font-size: 1.5rem;">
                            <?= strtoupper(mb_substr($offboarding['employee_name'], 0, 1)) ?>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <h4 class="fw-bold mb-0 text-dark"><?= h($offboarding['employee_name']) ?></h4>
                                <span class="badge bg-light text-dark border font-monospace"><?= h($offboarding['emp_code']) ?></span>
                                <span class="badge bg-info-subtle text-info border">
                                    Trạng thái HT: <?= h($offboarding['employee_current_status']) ?>
                                </span>
                            </div>
                            <div class="row g-2 text-muted small mt-2">
                                <div class="col-sm-6">
                                    <div><i class="fa-solid fa-briefcase text-secondary me-2"></i><strong>Chức vụ:</strong> <?= h($offboarding['pos_name'] ?? 'N/A') ?></div>
                                    <div><i class="fa-solid fa-building text-secondary me-2"></i><strong>Phòng ban:</strong> <?= h($offboarding['dept_name'] ?? 'N/A') ?></div>
                                    <div><i class="fa-solid fa-location-dot text-secondary me-2"></i><strong>Dự án:</strong> <?= h($offboarding['project_name'] ?? 'Văn phòng chính') ?></div>
                                </div>
                                <div class="col-sm-6">
                                    <div><i class="fa-regular fa-id-card text-secondary me-2"></i><strong>CCCD:</strong> <?= h($offboarding['id_card_no'] ?? '---') ?></div>
                                    <div><i class="fa-regular fa-calendar-check text-secondary me-2"></i><strong>Ngày vào làm:</strong> <?= !empty($offboarding['join_date']) ? date('d/m/Y', strtotime($offboarding['join_date'])) : '---' ?></div>
                                    <div><i class="fa-solid fa-calendar-xmark text-danger me-2"></i><strong>Ngày làm việc cuối:</strong> <span class="text-danger fw-bold"><?= date('d/m/Y', strtotime($offboarding['last_working_day'])) ?></span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 border-start-lg mt-3 mt-lg-0 ps-lg-4">
                    <div class="bg-light rounded-4 p-3 border">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small fw-semibold text-muted">Tiến độ hoàn tất:</span>
                            <span class="badge bg-primary fs-6" id="overallPercentBadge"><?= $offboarding['progress_percent'] ?>%</span>
                        </div>
                        <div class="progress mb-2" style="height: 8px;">
                            <div class="progress-bar <?= $offboarding['progress_percent'] === 100 ? 'bg-success' : 'bg-primary' ?>" 
                                 id="overallProgressBar" role="progressbar" style="width: <?= $offboarding['progress_percent'] ?>%"></div>
                        </div>
                        <div class="d-flex justify-content-between small text-muted">
                            <span>Đã xong: <strong id="overallDoneCount"><?= $offboarding['done_items'] + $offboarding['na_items'] ?></strong>/<?= $offboarding['total_items'] ?></span>
                            <span>Mục chặn (Blocking): <strong class="text-danger" id="overallPendingBlocking"><?= $offboarding['pending_blocking_count'] ?></strong></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Blocking Warning Banner -->
    <div id="clearanceStatusAlert">
        <?php if ($offboarding['can_complete']): ?>
            <div class="alert alert-success border-success-subtle shadow-sm rounded-4 mb-4 d-flex align-items-center gap-3">
                <i class="fa-solid fa-circle-check fa-2x text-success"></i>
                <div>
                    <h6 class="fw-bold mb-0 text-success">ĐỦ ĐIỀU KIỆN CHỐT THÔI VIỆC</h6>
                    <small>Tất cả các nhiệm vụ bắt buộc của IT, HR, Finance, HSE, Admin đã được hoàn tất. Bạn có thể bấm nút <strong>"Chốt Thôi việc & Đổi trạng thái NV"</strong>.</small>
                </div>
            </div>
        <?php elseif ($offboarding['status'] === 'InProgress'): ?>
            <div class="alert alert-warning border-warning-subtle shadow-sm rounded-4 mb-4 d-flex align-items-center gap-3">
                <i class="fa-solid fa-triangle-exclamation fa-2x text-warning"></i>
                <div>
                    <h6 class="fw-bold mb-0 text-warning-emphasis">CHƯA ĐỦ ĐIỀU KIỆN CHỐT THÔI VIỆC (CÒN <?= $offboarding['pending_blocking_count'] ?> MỤC BẮT BUỘC)</h6>
                    <small>Ràng buộc hệ thống: Nhân viên chỉ được chuyển trạng thái sang <em>Resigned/Terminated</em> sau khi các phòng ban hoàn tất các mục được đánh dấu <span class="badge bg-danger">Bắt buộc</span>.</small>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- 3 Context Linked Widgets (Asset, Payroll/Leave, Insurance) -->
    <div class="row g-3 mb-4">
        <!-- Widget 1: Thu hồi Tài sản (Asset) -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="fa-solid fa-laptop-file text-primary me-2"></i>Tài sản đang giữ (<?= count($offboarding['assigned_assets']) ?>)
                    </h6>
                    <a href="<?= BASE_URL ?>/asset/employeeAssets/<?= $offboarding['employee_id'] ?>" target="_blank" class="btn btn-outline-primary btn-sm py-0 px-2" style="font-size: 0.75rem;">
                        <i class="fa-solid fa-up-right-from-square"></i> Quản lý TS
                    </a>
                </div>
                <div class="card-body p-3">
                    <?php if (empty($offboarding['assigned_assets'])): ?>
                        <div class="text-center py-3 text-muted">
                            <i class="fa-solid fa-circle-check text-success fa-2x mb-2 d-block"></i>
                            <div class="small fw-semibold">Không giữ tài sản nào</div>
                            <small class="text-muted">Đã sạch công nợ tài sản công ty</small>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive" style="max-height: 220px;">
                            <table class="table table-sm table-hover mb-0" style="font-size: 0.8rem;">
                                <thead class="table-light">
                                    <tr>
                                        <th>Mã TS</th>
                                        <th>Tên thiết bị</th>
                                        <th class="text-end">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($offboarding['assigned_assets'] as $ast): ?>
                                        <tr>
                                            <td class="font-monospace text-primary fw-bold">
                                                <a href="<?= BASE_URL ?>/asset/show/<?= $ast['asset_id'] ?>" target="_blank" class="text-decoration-none">
                                                    <?= h($ast['asset_code']) ?>
                                                </a>
                                            </td>
                                            <td class="text-truncate" style="max-width: 130px;" title="<?= h($ast['asset_name']) ?>">
                                                <?= h($ast['asset_name']) ?>
                                            </td>
                                            <td class="text-end">
                                                <a href="<?= BASE_URL ?>/asset/show/<?= $ast['asset_id'] ?>" target="_blank" class="badge bg-danger text-decoration-none">
                                                    Thu hồi
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Widget 2: Quyết toán Lương & Nghỉ phép (Payroll/Leave) -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="fa-solid fa-money-check-dollar text-success me-2"></i>Quyết toán Lương & Phép
                    </h6>
                    <a href="<?= BASE_URL ?>/leave" target="_blank" class="btn btn-outline-success btn-sm py-0 px-2" style="font-size: 0.75rem;">
                        <i class="fa-solid fa-up-right-from-square"></i> Xem Phép
                    </a>
                </div>
                <div class="card-body p-3">
                    <!-- Phép tồn -->
                    <div class="mb-3">
                        <div class="text-muted small fw-semibold mb-1">Số dư ngày phép năm <?= date('Y') ?>:</div>
                        <?php if (empty($offboarding['leave_balances'])): ?>
                            <small class="text-muted fst-italic">Chưa có dữ liệu quỹ phép năm nay</small>
                        <?php else: ?>
                            <div class="d-flex flex-wrap gap-2">
                                <?php foreach ($offboarding['leave_balances'] as $lb): ?>
                                    <span class="badge bg-light text-dark border p-2">
                                        <?= h($lb['leave_type_name']) ?>: 
                                        <strong class="text-primary"><?= (float)$lb['remaining_days'] ?> ngày</strong>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Lương tháng gần nhất -->
                    <div class="border-top pt-2">
                        <div class="text-muted small fw-semibold mb-1">Bảng lương tháng gần nhất:</div>
                        <?php if ($offboarding['latest_payroll']): ?>
                            <div class="d-flex justify-content-between small">
                                <span>Kỳ lương: <strong><?= $offboarding['latest_payroll']['month'] ?>/<?= $offboarding['latest_payroll']['year'] ?></strong></span>
                                <span>Thực lĩnh: <strong class="text-success"><?= number_format($offboarding['latest_payroll']['net_salary'] ?? 0) ?> ₫</strong></span>
                            </div>
                            <div class="d-flex justify-content-between small text-muted mt-1">
                                <span>Tạm ứng chưa trừ: <?= number_format($offboarding['latest_payroll']['advance_payment'] ?? 0) ?> ₫</span>
                                <span class="badge bg-light text-secondary border"><?= h($offboarding['latest_payroll']['status']) ?></span>
                            </div>
                        <?php else: ?>
                            <small class="text-muted fst-italic">Chưa có phiếu lương nào ghi nhận</small>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Widget 3: Hồ sơ BHXH (Insurance) -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="fa-solid fa-shield-halved text-info me-2"></i>Hồ sơ Bảo hiểm Xã hội
                    </h6>
                    <a href="<?= BASE_URL ?>/insurance" target="_blank" class="btn btn-outline-info btn-sm py-0 px-2" style="font-size: 0.75rem;">
                        <i class="fa-solid fa-up-right-from-square"></i> Quản lý BHXH
                    </a>
                </div>
                <div class="card-body p-3">
                    <?php if ($offboarding['insurance_info']): ?>
                        <div class="small">
                            <div class="mb-1"><span class="text-muted">Mã số BHXH:</span> <strong class="font-monospace text-dark"><?= h($offboarding['insurance_info']['social_insurance_no'] ?? 'Chưa cập nhật') ?></strong></div>
                            <div class="mb-1"><span class="text-muted">Mã thẻ BHYT:</span> <strong class="font-monospace text-dark"><?= h($offboarding['insurance_info']['health_insurance_no'] ?? 'Chưa cập nhật') ?></strong></div>
                            <div class="mb-1"><span class="text-muted">Nơi KCB ban đầu:</span> <span><?= h($offboarding['insurance_info']['hospital_name'] ?? 'N/A') ?></span></div>
                            <div class="mb-1"><span class="text-muted">Mức đóng BHXH:</span> <strong class="text-primary"><?= number_format($offboarding['insurance_info']['insurance_salary'] ?? 0) ?> ₫</strong></div>
                            <div><span class="text-muted">Trạng thái BH:</span> <span class="badge bg-light text-dark border"><?= h($offboarding['insurance_info']['status'] ?? 'Active') ?></span></div>
                        </div>
                    <?php else: ?>
                        <div class="text-muted small py-2">
                            <div><i class="fa-solid fa-info-circle text-muted me-1"></i> Chưa có hồ sơ BHXH trên phân hệ Bảo hiểm.</div>
                            <small class="text-muted">HR cần kiểm tra sổ BHXH trực tiếp khi làm thủ tục thôi việc.</small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Multi-Department Clearance Checklist Tabs / Sections -->
    <div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
        <div class="card-header bg-white border-bottom p-3">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-dark">
                    <i class="fa-solid fa-tasks text-danger me-2"></i>Checklist Bàn giao Đa Phòng ban
                </h5>
                <span class="badge bg-light text-dark border small">
                    Mẫu: <?= h($offboarding['template_name']) ?>
                </span>
            </div>
        </div>

        <div class="card-body p-0">
            <!-- Accordion theo 5 phòng ban -->
            <div class="accordion accordion-flush" id="offboardingAccordion">
                
                <?php
                $deptsConfig = [
                    'IT' => [
                        'title' => 'Phòng Công nghệ Thông tin (IT Department)',
                        'icon'  => 'fa-solid fa-laptop-code text-primary',
                        'desc'  => 'Thu hồi máy tính, thiết bị ngoại vi, vô hiệu hóa tài khoản và email'
                    ],
                    'Admin' => [
                        'title' => 'Phòng Hành chính - Quản trị (Admin Department)',
                        'icon'  => 'fa-solid fa-building text-warning',
                        'desc'  => 'Thu hồi thẻ nhân viên, chìa khóa, xe, con dấu và trang thiết bị văn phòng'
                    ],
                    'HSE' => [
                        'title' => 'Phòng An toàn Lao động & Môi trường (HSE Department)',
                        'icon'  => 'fa-solid fa-hard-hat text-danger',
                        'desc'  => 'Thu hồi đồ bảo hộ PPE, biên bản kiểm tra vi phạm an toàn công trường'
                    ],
                    'Finance' => [
                        'title' => 'Phòng Tài chính - Kế toán (Finance & Accounting)',
                        'icon'  => 'fa-solid fa-coins text-success',
                        'desc'  => 'Quyết toán tạm ứng công trình, hoàn ứng, ngày phép tồn và lương tháng cuối'
                    ],
                    'HR' => [
                        'title' => 'Phòng Nhân sự (HR Department)',
                        'icon'  => 'fa-solid fa-user-tie text-info',
                        'desc'  => 'Chốt sổ BHXH, hoàn tất hồ sơ gốc, ký thanh lý HĐLĐ và trao quyết định'
                    ]
                ];

                foreach ($deptsConfig as $deptKey => $dCfg):
                    $deptItems = $offboarding['grouped_items'][$deptKey] ?? [];
                    $deptDone = count(array_filter($deptItems, fn($it) => $it['status'] === 'Done' || $it['status'] === 'NA'));
                    $deptTotal = count($deptItems);
                    $deptPendingBlocking = count(array_filter($deptItems, fn($it) => $it['is_blocking'] == 1 && $it['status'] === 'Pending'));
                    $isAllDone = ($deptTotal > 0 && $deptDone === $deptTotal);
                ?>
                <div class="accordion-item border-bottom">
                    <h2 class="accordion-header" id="heading_<?= $deptKey ?>">
                        <button class="accordion-button <?= $deptKey !== 'IT' ? 'collapsed' : '' ?> py-3 px-4" type="button" 
                                data-bs-toggle="collapse" data-bs-target="#collapse_<?= $deptKey ?>" 
                                aria-expanded="<?= $deptKey === 'IT' ? 'true' : 'false' ?>" aria-controls="collapse_<?= $deptKey ?>">
                            <div class="d-flex align-items-center justify-content-between w-100 me-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="fs-4"><i class="<?= $dCfg['icon'] ?>"></i></div>
                                    <div>
                                        <div class="fw-bold text-dark"><?= $dCfg['title'] ?></div>
                                        <small class="text-muted d-none d-md-block"><?= $dCfg['desc'] ?></small>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <?php if ($deptPendingBlocking > 0): ?>
                                        <span class="badge bg-danger-subtle text-danger small">
                                            <i class="fa-solid fa-lock me-1"></i><?= $deptPendingBlocking ?> mục chặn
                                        </span>
                                    <?php endif; ?>
                                    <span class="badge <?= $isAllDone ? 'bg-success' : 'bg-light text-dark border' ?> px-3 py-1">
                                        <?= $deptDone ?>/<?= $deptTotal ?> xong
                                    </span>
                                </div>
                            </div>
                        </button>
                    </h2>
                    <div id="collapse_<?= $deptKey ?>" class="accordion-collapse collapse <?= $deptKey === 'IT' ? 'show' : '' ?>" 
                         aria-labelledby="heading_<?= $deptKey ?>" data-bs-parent="#offboardingAccordion">
                        <div class="accordion-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light small text-muted">
                                        <tr>
                                            <th class="ps-4" style="width: 45%;">Nội dung nhiệm vụ bàn giao</th>
                                            <th style="width: 15%;">Mức độ</th>
                                            <th style="width: 20%;">Trạng thái xử lý</th>
                                            <th style="width: 20%;" class="pe-4">Ghi chú & Người xác nhận</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($deptItems)): ?>
                                            <tr>
                                                <td colspan="4" class="text-center py-3 text-muted">Không có nhiệm vụ nào cho bộ phận này.</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($deptItems as $it): ?>
                                                <tr id="row_item_<?= $it['id'] ?>">
                                                    <td class="ps-4">
                                                        <div class="fw-semibold text-dark"><?= h($it['task_title']) ?></div>
                                                    </td>
                                                    <td>
                                                        <?php if ($it['is_blocking'] == 1): ?>
                                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle small">
                                                                <i class="fa-solid fa-lock me-1"></i>Bắt buộc (Blocking)
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="badge bg-light text-secondary border small">Không bắt buộc</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($offboarding['status'] === 'InProgress' && $canEdit): ?>
                                                            <select class="form-select form-select-sm rounded-3 item-status-select" 
                                                                    data-item-id="<?= $it['id'] ?>" 
                                                                    data-blocking="<?= $it['is_blocking'] ?>"
                                                                    data-dept="<?= $deptKey ?>">
                                                                <option value="Pending" <?= $it['status'] === 'Pending' ? 'selected' : '' ?>>⏳ Chờ xử lý</option>
                                                                <option value="Done" <?= $it['status'] === 'Done' ? 'selected' : '' ?>>✅ Hoàn tất (Done)</option>
                                                                <option value="NA" <?= $it['status'] === 'NA' ? 'selected' : '' ?>>➖ Không áp dụng (N/A)</option>
                                                            </select>
                                                        <?php else: ?>
                                                            <?php 
                                                            $stBadge = match($it['status']) {
                                                                'Done'    => '<span class="badge bg-success"><i class="fa-solid fa-check me-1"></i>Đã xong</span>',
                                                                'NA'      => '<span class="badge bg-secondary">Không áp dụng</span>',
                                                                default   => '<span class="badge bg-warning text-dark">Chờ xử lý</span>'
                                                            };
                                                            echo $stBadge;
                                                            ?>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="pe-4">
                                                        <?php if ($offboarding['status'] === 'InProgress' && $canEdit): ?>
                                                            <input type="text" class="form-control form-control-sm rounded-3 item-notes-input" 
                                                                   data-item-id="<?= $it['id'] ?>"
                                                                   placeholder="Ghi chú bàn giao..." 
                                                                   value="<?= h($it['notes'] ?? '') ?>">
                                                        <?php else: ?>
                                                            <div class="small text-muted"><?= h($it['notes'] ?: '---') ?></div>
                                                        <?php endif; ?>
                                                        <?php if (!empty($it['completed_by_name'])): ?>
                                                            <small class="text-muted d-block mt-1" style="font-size: 0.72rem;">
                                                                <i class="fa-solid fa-user-check text-success me-1"></i><?= h($it['completed_by_name']) ?> (<?= date('d/m/Y H:i', strtotime($it['completed_at'])) ?>)
                                                            </small>
                                                        <?php endif; ?>
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
                <?php endforeach; ?>

            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const offboardingId = <?= (int)$offboarding['id'] ?>;
    const baseUrl = '<?= BASE_URL ?>';

    // Xử lý thay đổi Status của từng Item
    document.querySelectorAll('.item-status-select').forEach(select => {
        select.addEventListener('change', function() {
            const itemId = this.dataset.itemId;
            const newStatus = this.value;
            const row = document.getElementById('row_item_' + itemId);
            const notesInput = row.querySelector('.item-notes-input');
            const notes = notesInput ? notesInput.value.trim() : '';

            updateItemOnServer(itemId, newStatus, notes);
        });
    });

    // Xử lý thay đổi Notes khi rời ô input
    document.querySelectorAll('.item-notes-input').forEach(input => {
        input.addEventListener('blur', function() {
            const itemId = this.dataset.itemId;
            const row = document.getElementById('row_item_' + itemId);
            const statusSelect = row.querySelector('.item-status-select');
            const status = statusSelect ? statusSelect.value : 'Pending';
            const notes = this.value.trim();

            updateItemOnServer(itemId, status, notes);
        });
    });

    function updateItemOnServer(itemId, status, notes) {
        fetch(baseUrl + '/offboarding/updateItem', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                offboarding_id: offboardingId,
                item_id: itemId,
                status: status,
                notes: notes
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Cập nhật trạng thái nút Complete
                const btnComplete = document.getElementById('btnCompleteOffboarding');
                const alertContainer = document.getElementById('clearanceStatusAlert');
                
                if (btnComplete) {
                    btnComplete.disabled = !data.can_complete;
                }

                if (data.can_complete) {
                    alertContainer.innerHTML = `
                        <div class="alert alert-success border-success-subtle shadow-sm rounded-4 mb-4 d-flex align-items-center gap-3">
                            <i class="fa-solid fa-circle-check fa-2x text-success"></i>
                            <div>
                                <h6 class="fw-bold mb-0 text-success">ĐỦ ĐIỀU KIỆN CHỐT THÔI VIỆC</h6>
                                <small>Tất cả các nhiệm vụ bắt buộc của IT, HR, Finance, HSE, Admin đã được hoàn tất. Bạn có thể bấm nút <strong>"Chốt Thôi việc & Đổi trạng thái NV"</strong>.</small>
                            </div>
                        </div>
                    `;
                } else {
                    alertContainer.innerHTML = `
                        <div class="alert alert-warning border-warning-subtle shadow-sm rounded-4 mb-4 d-flex align-items-center gap-3">
                            <i class="fa-solid fa-triangle-exclamation fa-2x text-warning"></i>
                            <div>
                                <h6 class="fw-bold mb-0 text-warning-emphasis">CHƯA ĐỦ ĐIỀU KIỆN CHỐT THÔI VIỆC (CÒN ${data.pending_blocking_cnt} MỤC BẮT BUỘC)</h6>
                                <small>Ràng buộc hệ thống: Nhân viên chỉ được chuyển trạng thái sang <em>Resigned/Terminated</em> sau khi các phòng ban hoàn tất các mục được đánh dấu <span class="badge bg-danger">Bắt buộc</span>.</small>
                            </div>
                        </div>
                    `;
                }
            } else {
                alert('Lỗi cập nhật: ' + (data.message || 'Không thể lưu'));
            }
        })
        .catch(err => {
            console.error('Fetch error:', err);
        });
    }
});
</script>

<style>
@media print {
    .topbar, .sidebar, .btn, .breadcrumb, form button, .card-header .btn {
        display: none !important;
    }
    .accordion-collapse {
        display: block !important;
    }
    .card {
        border: 1px solid #ddd !important;
        box-shadow: none !important;
    }
}
</style>
