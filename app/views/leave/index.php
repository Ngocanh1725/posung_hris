<?php
/**
 * ============================================================
 *  POSUNG HRIS – Leave Dashboard (Chuẩn Frappe HRMS)
 * ============================================================
 *  View: leave/index.php
 * ============================================================
 */
?>

<div class="leave-dashboard">
    <!-- Top Action Bar -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h3 class="fw-bold text-dark mb-1">
                <i class="fas fa-calendar-alt text-primary me-2"></i> Quản lý Nghỉ phép & Quỹ phép
            </h3>
            <p class="text-muted small mb-0">Hệ thống phân bổ định ngạch, theo dõi ngày phép cá nhân và lịch nghỉ toàn công ty theo chuẩn Frappe HRMS.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <button class="btn btn-primary shadow-sm" onclick="openApplyModal()">
                <i class="fas fa-paper-plane me-1"></i> Nộp đơn xin nghỉ
            </button>
            <a href="<?= BASE_URL ?>/leave/holidays" class="btn btn-outline-danger shadow-sm">
                <i class="fas fa-gifts me-1"></i> Lịch Nghỉ Lễ Tết
            </a>
            <?php if ($isManager): ?>
                <a href="<?= BASE_URL ?>/leave/allocations" class="btn btn-outline-primary shadow-sm">
                    <i class="fas fa-layer-group me-1"></i> Quản lý Quỹ phép (HR)
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- 4 KPI SUMMARY CARDS -->
    <div class="row g-3 mb-4">
        <!-- Card 1: Phép năm (Annual Leave) -->
        <div class="col-xl-3 col-md-6">
            <div class="card h-100 border-0 shadow-sm" style="border-radius: 14px; background: linear-gradient(135deg, #1e3a8a, #2563eb); color: #fff;">
                <div class="card-body p-3 p-xl-4 d-flex flex-column justify-content-between">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <span class="text-white-50 text-uppercase fw-semibold" style="font-size: 0.75rem; letter-spacing: 0.5px;">Quỹ Phép Năm <?= $currentYear ?></span>
                            <h2 class="fw-bold mb-0 text-white mt-1"><?= number_format($annualStats['remaining'], 1) ?> <small style="font-size: 1rem; font-weight: normal;">ngày còn</small></h2>
                        </div>
                        <div style="background: rgba(255,255,255,0.18); width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-umbrella-beach fa-lg text-white"></i>
                        </div>
                    </div>
                    <div>
                        <div class="d-flex justify-content-between small text-white-50 mb-1" style="font-size: 0.8rem;">
                            <span>Đã dùng: <strong><?= number_format($annualStats['used'], 1) ?></strong> ngày</span>
                            <span>Hạn mức: <strong><?= number_format($annualStats['total'], 1) ?></strong> ngày</span>
                        </div>
                        <?php 
                            $pct = ($annualStats['total'] > 0) ? min(100, round(($annualStats['used'] / $annualStats['total']) * 100)) : 0;
                        ?>
                        <div class="progress" style="height: 6px; background: rgba(255,255,255,0.25); border-radius: 4px;">
                            <div class="progress-bar bg-warning" role="progressbar" style="width: <?= $pct ?>%;"></div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-2 pt-1 border-top border-white-10" style="font-size: 0.75rem;">
                            <span class="text-white-50">Chuyển tiếp: <?= number_format($annualStats['carried'], 1) ?>d</span>
                            <?php if ($annualStats['pending'] > 0): ?>
                                <span class="badge bg-warning text-dark"><i class="fas fa-clock"></i> Chờ duyệt: <?= number_format($annualStats['pending'], 1) ?>d</span>
                            <?php else: ?>
                                <span class="text-white-50">Không có đơn chờ</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 2: Phép ốm / Chế độ BHXH -->
        <div class="col-xl-3 col-md-6">
            <div class="card h-100 border-0 shadow-sm" style="border-radius: 14px; background: #ffffff; border-left: 4px solid #06b6d4 !important;">
                <div class="card-body p-3 p-xl-4 d-flex flex-column justify-content-between">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <span class="text-muted text-uppercase fw-semibold" style="font-size: 0.75rem; letter-spacing: 0.5px;">Nghỉ Ốm & Chế độ BHXH</span>
                            <h2 class="fw-bold mb-0 text-dark mt-1"><?= number_format($sickUsed, 1) ?> <small style="font-size: 0.9rem; color: #64748b;">ngày</small></h2>
                        </div>
                        <div style="background: #e0f2fe; width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-briefcase-medical fa-lg text-info"></i>
                        </div>
                    </div>
                    <div>
                        <p class="text-muted small mb-0" style="font-size: 0.8rem;">
                            <i class="fas fa-info-circle text-info me-1"></i> Tối đa 30 ngày/năm (theo Luật BHXH được cơ quan BHXH chi trả).
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 3: Nghỉ không lương (Payroll integrated) -->
        <div class="col-xl-3 col-md-6">
            <div class="card h-100 border-0 shadow-sm" style="border-radius: 14px; background: #ffffff; border-left: 4px solid #f59e0b !important;">
                <div class="card-body p-3 p-xl-4 d-flex flex-column justify-content-between">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <span class="text-muted text-uppercase fw-semibold" style="font-size: 0.75rem; letter-spacing: 0.5px;">Nghỉ Không Lương (NKL)</span>
                            <h2 class="fw-bold mb-0 text-warning mt-1"><?= number_format($unpaidUsed, 1) ?> <small style="font-size: 0.9rem; color: #64748b;">ngày</small></h2>
                        </div>
                        <div style="background: #fef3c7; width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-user-clock fa-lg text-warning"></i>
                        </div>
                    </div>
                    <div>
                        <p class="text-muted small mb-0" style="font-size: 0.8rem;">
                            <i class="fas fa-file-invoice-dollar text-warning me-1"></i> Tự động đồng bộ và khấu trừ công trong <strong>Phiếu lương (Payroll)</strong>.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 4: Ngày Lễ sắp tới -->
        <div class="col-xl-3 col-md-6">
            <div class="card h-100 border-0 shadow-sm" style="border-radius: 14px; background: #ffffff; border-left: 4px solid #ef4444 !important;">
                <div class="card-body p-3 p-xl-4 d-flex flex-column justify-content-between">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <span class="text-muted text-uppercase fw-semibold" style="font-size: 0.75rem; letter-spacing: 0.5px;">Lễ / Tết sắp tới</span>
                            <?php if (!empty($upcomingHolidays)): ?>
                                <?php $nextH = $upcomingHolidays[0]; ?>
                                <h6 class="fw-bold mb-0 text-danger mt-1 text-truncate" style="max-width: 170px;" title="<?= h($nextH['name']) ?>">
                                    <?= h($nextH['name']) ?>
                                </h6>
                                <small class="text-muted"><?= date('d/m/Y', strtotime($nextH['date'])) ?></small>
                            <?php else: ?>
                                <h6 class="fw-bold mb-0 text-muted mt-1">Không có ngày lễ</h6>
                            <?php endif; ?>
                        </div>
                        <div style="background: #fee2e2; width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-gift fa-lg text-danger"></i>
                        </div>
                    </div>
                    <div>
                        <div class="d-flex justify-content-between align-items-center" style="font-size: 0.8rem;">
                            <span class="text-muted">Tổng ngày lễ <?= $currentYear ?>: <strong><?= count($holidays) ?> ngày</strong></span>
                            <a href="<?= BASE_URL ?>/leave/holidays" class="text-danger fw-bold text-decoration-none">Xem tất cả <i class="fas fa-arrow-right" style="font-size:10px;"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MAIN TABS: ĐƠN TỪ / LỊCH TRỰC QUAN / DUYỆT PHÉP -->
    <div class="card border-0 shadow-sm" style="border-radius: 14px; overflow: hidden;">
        <div class="card-header bg-white border-bottom p-0">
            <ul class="nav nav-tabs card-header-tabs m-0 px-3 pt-2" id="leaveTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active fw-bold text-dark py-3 px-4 border-0 border-bottom border-3 border-primary" id="tab-my-leaves-btn" data-bs-toggle="tab" data-bs-target="#tab-my-leaves" type="button" role="tab">
                        <i class="fas fa-history text-primary me-2"></i> Lịch sử đơn của tôi
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold text-muted py-3 px-4 border-0" id="tab-calendar-btn" data-bs-toggle="tab" data-bs-target="#tab-calendar" type="button" role="tab" onclick="renderCalendarEvents()">
                        <i class="fas fa-calendar-day text-success me-2"></i> Lịch Nghỉ & Lễ Tết (Calendar)
                    </button>
                </li>
                <?php if ($isManager): ?>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold text-muted py-3 px-4 border-0" id="tab-pending-btn" data-bs-toggle="tab" data-bs-target="#tab-pending" type="button" role="tab">
                        <i class="fas fa-clipboard-check text-warning me-2"></i> Cần phê duyệt
                        <?php if (count($pendingRequests) > 0): ?>
                            <span class="badge rounded-pill bg-danger ms-1"><?= count($pendingRequests) ?></span>
                        <?php endif; ?>
                    </button>
                </li>
                <?php endif; ?>
            </ul>
        </div>

        <div class="card-body p-4">
            <div class="tab-content" id="leaveTabsContent">
                
                <!-- TAB 1: LỊCH SỬ ĐƠN CỦA TÔI -->
                <div class="tab-pane fade show active" id="tab-my-leaves" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0 text-dark">Danh sách đơn xin nghỉ phép của bạn</h5>
                        <button class="btn btn-sm btn-primary rounded-pill px-3" onclick="openApplyModal()">
                            <i class="fas fa-plus me-1"></i> Nộp đơn mới
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr style="font-size: 0.85rem; text-transform: uppercase; color: #64748b;">
                                    <th>Mã đơn</th>
                                    <th>Loại nghỉ phép</th>
                                    <th>Thời gian nghỉ</th>
                                    <th class="text-center">Số ngày làm việc</th>
                                    <th>Lý do</th>
                                    <th class="text-center">Trạng thái</th>
                                    <th>Người duyệt & Ghi chú</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($myRequests)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted">
                                            <i class="fas fa-folder-open fa-3x mb-3 text-secondary opacity-50"></i>
                                            <p class="mb-2">Bạn chưa có đơn xin nghỉ phép nào trong năm <?= $currentYear ?>.</p>
                                            <button class="btn btn-sm btn-outline-primary" onclick="openApplyModal()">Tạo đơn xin nghỉ đầu tiên</button>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($myRequests as $req): ?>
                                    <tr>
                                        <td class="font-monospace fw-bold text-muted">#<?= $req->id ?></td>
                                        <td>
                                            <span class="fw-bold text-dark"><?= h($req->leave_type_name) ?></span>
                                            <?php if ($req->is_paid): ?>
                                                <span class="badge bg-success-subtle text-success ms-1" style="font-size: 10px;">Có lương</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary-subtle text-secondary ms-1" style="font-size: 10px;">Không lương</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-1" style="font-size: 0.9rem;">
                                                <span class="fw-semibold"><?= date('d/m/Y', strtotime($req->start_date)) ?></span>
                                                <i class="fas fa-arrow-right text-muted mx-1" style="font-size: 10px;"></i>
                                                <span class="fw-semibold"><?= date('d/m/Y', strtotime($req->end_date)) ?></span>
                                            </div>
                                            <small class="text-muted">Nộp ngày: <?= date('d/m/Y H:i', strtotime($req->created_at)) ?></small>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-light text-primary border fw-bold px-3 py-2" style="font-size: 0.9rem;">
                                                <?= floatval($req->total_days ?? $req->days) ?> ngày
                                            </span>
                                        </td>
                                        <td>
                                            <span class="text-muted text-break" style="max-width: 250px; display: inline-block;">
                                                <?= h($req->reason) ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($req->status === 'Approved'): ?>
                                                <span class="badge bg-success px-3 py-2"><i class="fas fa-check-circle me-1"></i> Đã duyệt</span>
                                            <?php elseif ($req->status === 'Rejected'): ?>
                                                <span class="badge bg-danger px-3 py-2"><i class="fas fa-times-circle me-1"></i> Từ chối</span>
                                            <?php elseif ($req->status === 'Cancelled'): ?>
                                                <span class="badge bg-secondary px-3 py-2">Đã hủy</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning text-dark px-3 py-2"><i class="fas fa-hourglass-half me-1"></i> Chờ duyệt</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($req->approved_by)): ?>
                                                <small class="d-block text-dark fw-semibold"><?= h($req->approver_name) ?></small>
                                                <small class="text-muted fst-italic">"<?= h($req->approver_note ?? 'Đồng ý') ?>"</small>
                                            <?php else: ?>
                                                <span class="text-muted small">---</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- TAB 2: LỊCH NGHỈ TRỰC QUAN (CALENDAR VIEW) -->
                <div class="tab-pane fade" id="tab-calendar" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                        <div>
                            <h5 class="fw-bold mb-1 text-dark"><i class="fas fa-calendar-alt text-success me-2"></i> Lịch Nghỉ Toàn Công Ty & Nghỉ Lễ Tết</h5>
                            <p class="text-muted small mb-0">Hiển thị trực quan các ngày lễ quốc gia và danh sách nhân sự đã được phê duyệt nghỉ phép.</p>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <div class="d-flex align-items-center gap-1 small">
                                <span style="display:inline-block; width:12px; height:12px; background:#ef4444; border-radius:3px;"></span> Lễ Quốc gia
                            </div>
                            <div class="d-flex align-items-center gap-1 small">
                                <span style="display:inline-block; width:12px; height:12px; background:#f59e0b; border-radius:3px;"></span> Lễ Công ty
                            </div>
                            <div class="d-flex align-items-center gap-1 small">
                                <span style="display:inline-block; width:12px; height:12px; background:#3b82f6; border-radius:3px;"></span> Phép đã duyệt
                            </div>
                        </div>
                    </div>

                    <!-- Calendar Controls -->
                    <div class="d-flex justify-content-between align-items-center p-3 mb-3 bg-light rounded" style="border: 1px solid #e2e8f0;">
                        <button class="btn btn-sm btn-outline-secondary" onclick="prevMonth()"><i class="fas fa-chevron-left me-1"></i> Tháng trước</button>
                        <h4 class="fw-bold mb-0 text-dark" id="calendarMonthTitle">Tháng <?= date('m/Y') ?></h4>
                        <button class="btn btn-sm btn-outline-secondary" onclick="nextMonth()">Tháng sau <i class="fas fa-chevron-right ms-1"></i></button>
                    </div>

                    <!-- Custom Pure CSS Calendar Grid -->
                    <div class="calendar-container" style="border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden; background: #fff;">
                        <div class="d-grid text-center py-2 bg-light fw-bold text-muted border-bottom" style="grid-template-columns: repeat(7, 1fr); font-size: 0.85rem;">
                            <div class="text-danger">Chủ Nhật</div>
                            <div>Thứ Hai</div>
                            <div>Thứ Ba</div>
                            <div>Thứ Tư</div>
                            <div>Thứ Năm</div>
                            <div>Thứ Sáu</div>
                            <div class="text-primary">Thứ Bảy</div>
                        </div>
                        <div id="calendarGrid" class="d-grid" style="grid-template-columns: repeat(7, 1fr); min-height: 480px;">
                            <!-- Dynamic generated days via JS -->
                        </div>
                    </div>
                </div>

                <!-- TAB 3: PHÊ DUYỆT ĐƠN (DÀNH CHO MANAGER / HR) -->
                <?php if ($isManager): ?>
                <div class="tab-pane fade" id="tab-pending" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0 text-dark">
                            Đơn xin nghỉ chờ phê duyệt 
                            <span class="badge bg-warning text-dark ms-2"><?= count($pendingRequests) ?> đơn</span>
                        </h5>
                    </div>

                    <?php if (empty($pendingRequests)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-check-circle fa-3x text-success mb-3 opacity-50"></i>
                            <h5 class="fw-bold text-dark">Tuyệt vời!</h5>
                            <p class="mb-0">Hiện không có đơn xin nghỉ phép nào đang chờ bạn xét duyệt.</p>
                        </div>
                    <?php else: ?>
                        <div class="row g-3">
                            <?php foreach ($pendingRequests as $pReq): ?>
                            <div class="col-md-6 col-xl-4">
                                <div class="card h-100 border shadow-sm" style="border-radius: 12px; border-left: 4px solid var(--primary) !important;">
                                    <div class="card-body p-3 d-flex flex-column justify-content-between">
                                        <div>
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <div class="d-flex align-items-center gap-2">
                                                    <div style="width: 36px; height: 36px; border-radius: 50%; background: #e2e8f0; display:flex; align-items:center; justify-content:center; font-weight:bold; color: #1e3a8a;">
                                                        <?= strtoupper(substr($pReq->full_name, 0, 1)) ?>
                                                    </div>
                                                    <div>
                                                        <strong class="d-block text-dark"><?= h($pReq->full_name) ?></strong>
                                                        <span class="badge bg-light text-muted border" style="font-size: 10px;"><?= h($pReq->emp_code) ?> - <?= h($pReq->dept_name ?? 'Phòng ban') ?></span>
                                                    </div>
                                                </div>
                                                <span class="badge bg-primary text-white"><?= h($pReq->leave_type_name) ?></span>
                                            </div>

                                            <div class="p-2 bg-light rounded my-2 small">
                                                <div class="text-muted"><i class="fas fa-calendar-day text-primary me-1"></i> Từ <strong><?= date('d/m/Y', strtotime($pReq->start_date)) ?></strong> đến <strong><?= date('d/m/Y', strtotime($pReq->end_date)) ?></strong></div>
                                                <div class="text-dark mt-1">Số ngày: <strong><?= floatval($pReq->total_days ?? $pReq->days) ?> ngày làm việc</strong></div>
                                            </div>

                                            <div class="small text-muted mb-3 fst-italic">
                                                "<?= h($pReq->reason) ?>"
                                            </div>
                                        </div>

                                        <div class="d-flex gap-2 pt-2 border-top">
                                            <form action="<?= BASE_URL ?>/leave/approve/<?= $pReq->id ?>" method="POST" style="flex: 1;">
                                                <?= Session::csrfField() ?>
                                                <input type="hidden" name="approver_note" value="Đồng ý phê duyệt">
                                                <button type="submit" class="btn btn-success btn-sm w-100" onclick="return confirm('Bạn chắc chắn muốn duyệt đơn nghỉ phép này?');">
                                                    <i class="fas fa-check me-1"></i> Duyệt
                                                </button>
                                            </form>
                                            <button type="button" class="btn btn-outline-danger btn-sm" style="flex: 1;" onclick="openRejectModal(<?= $pReq->id ?>, '<?= h($pReq->full_name) ?>')">
                                                <i class="fas fa-times me-1"></i> Từ chối
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- MODAL 1: NỘP ĐƠN XIN NGHỈ PHÉP (LIVE OVERLAP & BALANCE CHECK) -->
<!-- ============================================================ -->
<div class="modal fade" id="applyModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 14px; border:none; box-shadow: 0 20px 40px rgba(0,0,0,0.15);">
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #1e3a8a, #2563eb); border-radius: 14px 14px 0 0;">
                <h5 class="modal-title fw-bold"><i class="fas fa-paper-plane me-2"></i> Nộp Đơn Xin Nghỉ Phép</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= BASE_URL ?>/leave/store" method="POST" id="leaveApplyForm">
                <?= Session::csrfField() ?>
                <div class="modal-body p-4">
                    
                    <!-- Alert Banner: Overlap warning -->
                    <div id="overlapAlert" class="alert alert-danger d-none py-2 px-3 small" style="border-radius: 8px;">
                        <i class="fas fa-exclamation-triangle me-1"></i> <span id="overlapAlertText"></span>
                    </div>

                    <!-- Alert Banner: Balance warning -->
                    <div id="balanceAlert" class="alert alert-warning d-none py-2 px-3 small" style="border-radius: 8px;">
                        <i class="fas fa-exclamation-circle me-1"></i> <span id="balanceAlertText"></span>
                    </div>

                    <!-- Loại phép -->
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-dark">Loại nghỉ phép <span class="text-danger">*</span></label>
                        <select name="leave_type_id" id="modalLeaveType" class="form-select" required onchange="handleTypeChange()">
                            <option value="">-- Chọn loại phép --</option>
                            <?php foreach ($leaveTypes as $lt): ?>
                                <option value="<?= $lt['id'] ?>" 
                                        data-paid="<?= $lt['is_paid'] ?>"
                                        data-negative="<?= $lt['allow_negative'] ?? 0 ?>"
                                        data-max-continuous="<?= $lt['max_continuous_days'] ?? 0 ?>">
                                    <?= h($lt['name']) ?> <?= $lt['is_paid'] ? '(Hưởng lương)' : '(Không hưởng lương)' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div id="leaveTypeBalanceBadge" class="mt-1 small text-muted"></div>
                    </div>

                    <!-- Thời gian từ ngày - đến ngày -->
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-bold small text-dark">Từ ngày <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" id="modalStartDate" class="form-control" required onchange="triggerDaysCalculation()">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold small text-dark">Đến ngày <span class="text-danger">*</span></label>
                            <input type="date" name="end_date" id="modalEndDate" class="form-control" required onchange="triggerDaysCalculation()">
                        </div>
                    </div>

                    <!-- Số ngày tính phép (Dynamic Auto-calculated) -->
                    <div class="mb-3 p-3 bg-light rounded" style="border: 1px dashed #cbd5e1;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <label class="form-label fw-bold small text-dark mb-0">Số ngày làm việc tính phép:</label>
                                <div id="calcDaysNote" class="text-muted" style="font-size: 0.75rem;">(Hệ thống tự động trừ Chủ Nhật và Ngày Lễ)</div>
                            </div>
                            <div class="input-group" style="width: 130px;">
                                <input type="number" step="0.5" name="total_days" id="modalTotalDays" class="form-control fw-bold text-primary text-center" value="1.0" required>
                                <span class="input-group-text bg-white small">ngày</span>
                            </div>
                        </div>
                        <div id="calcBreakdownBadge" class="mt-2 text-primary small d-none"></div>
                    </div>

                    <!-- Lý do xin nghỉ -->
                    <div class="mb-2">
                        <label class="form-label fw-bold small text-dark">Lý do xin nghỉ <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control" rows="3" required placeholder="Nhập lý do chi tiết và phương án bàn giao công việc..."></textarea>
                    </div>

                </div>
                <div class="modal-footer bg-light" style="border-radius: 0 0 14px 14px;">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Hủy bỏ</button>
                    <button type="submit" id="submitApplyBtn" class="btn btn-primary btn-sm px-4 fw-bold">
                        <i class="fas fa-paper-plane me-1"></i> Gửi đơn ngay
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- MODAL 2: TỪ CHỐI ĐƠN PHÉP (DÀNH CHO QUẢN LÝ) -->
<!-- ============================================================ -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 12px;">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title fw-bold"><i class="fas fa-times-circle me-1"></i> Từ chối đơn xin nghỉ</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="rejectForm" method="POST">
                <?= Session::csrfField() ?>
                <div class="modal-body p-4">
                    <p class="text-muted">Nhân viên: <strong id="rejectEmpName" class="text-dark"></strong></p>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Lý do từ chối đơn <span class="text-danger">*</span></label>
                        <textarea name="approver_note" class="form-control" rows="3" required placeholder="Nêu rõ lý do từ chối để nhân viên nắm được..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-danger btn-sm">Xác nhận từ chối</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Dữ liệu số dư theo từng loại phép (nạp từ PHP)
const employeeBalances = <?= json_encode($balances) ?>;
const allHolidays = <?= json_encode($holidays) ?>;
const allCalendarEvents = <?= json_encode($calendarEvents) ?>;
const currentEmpId = <?= (int)($employeeId ?? 0) ?>;
const baseUrl = "<?= BASE_URL ?>";

let applyModalInstance;
let rejectModalInstance;
let currentCalYear = <?= $currentYear ?>;
let currentCalMonth = <?= (int)date('m') ?>;

document.addEventListener('DOMContentLoaded', () => {
    applyModalInstance = new bootstrap.Modal(document.getElementById('applyModal'));
    rejectModalInstance = new bootstrap.Modal(document.getElementById('rejectModal'));

    // Khởi tạo ngày mặc định cho form nộp đơn
    const todayStr = new Date().toISOString().split('T')[0];
    document.getElementById('modalStartDate').value = todayStr;
    document.getElementById('modalEndDate').value = todayStr;

    // Render lịch ban đầu
    renderCalendarGrid(currentCalYear, currentCalMonth);
});

function openApplyModal() {
    applyModalInstance.show();
    triggerDaysCalculation();
}

function openRejectModal(reqId, empName) {
    document.getElementById('rejectEmpName').textContent = empName;
    document.getElementById('rejectForm').action = `${baseUrl}/leave/reject/${reqId}`;
    rejectModalInstance.show();
}

function handleTypeChange() {
    const typeSelect = document.getElementById('modalLeaveType');
    const selectedTypeId = parseInt(typeSelect.value);
    const badge = document.getElementById('leaveTypeBalanceBadge');
    
    if(!selectedTypeId) {
        badge.textContent = '';
        return;
    }

    const found = employeeBalances.find(b => b.leave_type_id === selectedTypeId);
    if(found) {
        if(found.is_paid === 0 || found.allow_negative) {
            badge.innerHTML = `<span class="badge bg-secondary">Không giới hạn định ngạch (Khấu trừ theo lương)</span>`;
        } else {
            badge.innerHTML = `<span class="badge bg-success">Số dư khả dụng: <strong>${found.available_days} ngày</strong> (Quỹ: ${found.total_quota}d, Đã dùng: ${found.used_days}d)</span>`;
        }
    } else {
        badge.textContent = '';
    }

    triggerDaysCalculation();
}

// Gọi API tự động tính ngày làm việc, trừ Weekend & Holiday, kiểm tra Overlap
async function triggerDaysCalculation() {
    const sDate = document.getElementById('modalStartDate').value;
    const eDate = document.getElementById('modalEndDate').value;
    const typeSelect = document.getElementById('modalLeaveType');
    const selectedTypeId = parseInt(typeSelect.value);

    if(!sDate || !eDate) return;

    const overlapAlert = document.getElementById('overlapAlert');
    const balanceAlert = document.getElementById('balanceAlert');
    const submitBtn = document.getElementById('submitApplyBtn');
    const breakdownBadge = document.getElementById('calcBreakdownBadge');

    overlapAlert.classList.add('d-none');
    balanceAlert.classList.add('d-none');
    submitBtn.disabled = false;

    if(new Date(sDate) > new Date(eDate)) {
        overlapAlert.classList.remove('d-none');
        document.getElementById('overlapAlertText').textContent = 'Ngày kết thúc không được nhỏ hơn ngày bắt đầu.';
        submitBtn.disabled = true;
        return;
    }

    try {
        const resp = await fetch(`${baseUrl}/leave/calcDays`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                start_date: sDate,
                end_date: eDate,
                employee_id: currentEmpId
            })
        });
        const res = await resp.json();

        if(res.success) {
            const data = res.data;
            document.getElementById('modalTotalDays').value = data.working_days;

            let breakdownText = `Đã trừ: ${data.weekend_days} ngày cuối tuần (Chủ nhật)`;
            if(data.holiday_days > 0) {
                breakdownText += `, ${data.holiday_days} ngày Lễ Tết`;
            }
            breakdownBadge.textContent = breakdownText;
            breakdownBadge.classList.remove('d-none');

            // Cảnh báo Overlap
            if(res.has_overlap) {
                overlapAlert.classList.remove('d-none');
                document.getElementById('overlapAlertText').textContent = `⚠️ Phát hiện chồng đơn: Bạn đã có đơn '${res.overlap_info.type}' (${res.overlap_info.start} -> ${res.overlap_info.end})!`;
                submitBtn.disabled = true;
            }

            // Kiểm tra số dư phép
            if(selectedTypeId) {
                const found = employeeBalances.find(b => b.leave_type_id === selectedTypeId);
                if(found && found.is_paid === 1 && !found.allow_negative) {
                    if(data.working_days > found.available_days) {
                        balanceAlert.classList.remove('d-none');
                        document.getElementById('balanceAlertText').textContent = `Số ngày nghỉ (${data.working_days} ngày) vượt quá số dư khả dụng (${found.available_days} ngày)!`;
                        submitBtn.disabled = true;
                    }
                }
            }
        }
    } catch(err) {
        console.error('Lỗi tính ngày làm việc:', err);
    }
}

// ── CALENDAR VIEW ENGINE (Pure JS Grid) ──
function renderCalendarEvents() {
    renderCalendarGrid(currentCalYear, currentCalMonth);
}

function prevMonth() {
    currentCalMonth--;
    if(currentCalMonth < 1) {
        currentCalMonth = 12;
        currentCalYear--;
    }
    renderCalendarGrid(currentCalYear, currentCalMonth);
}

function nextMonth() {
    currentCalMonth++;
    if(currentCalMonth > 12) {
        currentCalMonth = 1;
        currentCalYear++;
    }
    renderCalendarGrid(currentCalYear, currentCalMonth);
}

function renderCalendarGrid(year, month) {
    document.getElementById('calendarMonthTitle').textContent = `Tháng ${month.toString().padStart(2, '0')}/${year}`;
    const grid = document.getElementById('calendarGrid');
    grid.innerHTML = '';

    const firstDay = new Date(year, month - 1, 1).getDay(); // 0 is Sunday
    const daysInMonth = new Date(year, month, 0).getDate();

    // Map holidays
    const holidaysInMonth = allHolidays.filter(h => {
        const d = new Date(h.date);
        return d.getFullYear() === year && (d.getMonth() + 1) === month;
    });

    // Map approved leaves
    const leavesInMonth = allCalendarEvents.filter(l => {
        const s = new Date(l.start_date);
        const e = new Date(l.end_date);
        const curM = month - 1;
        return (s.getFullYear() === year && s.getMonth() === curM) || (e.getFullYear() === year && e.getMonth() === curM);
    });

    // Empty cells before start of month
    for(let i = 0; i < firstDay; i++) {
        const emptyCell = document.createElement('div');
        emptyCell.className = 'border-end border-bottom bg-light opacity-50 p-2';
        emptyCell.style.minHeight = '90px';
        grid.appendChild(emptyCell);
    }

    // Days in month
    for(let day = 1; day <= daysInMonth; day++) {
        const curDate = new Date(year, month - 1, day);
        const isSunday = curDate.getDay() === 0;
        const curDateStr = `${year}-${month.toString().padStart(2, '0')}-${day.toString().padStart(2, '0')}`;

        const cell = document.createElement('div');
        cell.className = `border-end border-bottom p-1 d-flex flex-column justify-content-between ${isSunday ? 'bg-light-subtle' : ''}`;
        cell.style.minHeight = '90px';
        cell.style.position = 'relative';

        const dayNum = document.createElement('span');
        dayNum.className = `badge ${isSunday ? 'bg-danger-subtle text-danger' : 'text-dark'} mb-1 align-self-start fw-bold`;
        dayNum.textContent = day;
        cell.appendChild(dayNum);

        const eventsContainer = document.createElement('div');
        eventsContainer.className = 'd-flex flex-column gap-1 overflow-hidden';

        // Check if Holiday
        const holiday = holidaysInMonth.find(h => h.date === curDateStr);
        if(holiday) {
            const hBadge = document.createElement('div');
            hBadge.className = 'small px-1 rounded text-white text-truncate';
            hBadge.style.fontSize = '10px';
            hBadge.style.backgroundColor = holiday.type === 'Company' ? '#f59e0b' : '#ef4444';
            hBadge.title = holiday.name;
            hBadge.textContent = '🎉 ' + holiday.name;
            eventsContainer.appendChild(hBadge);
        }

        // Check Leaves
        leavesInMonth.forEach(l => {
            if(curDateStr >= l.start_date && curDateStr <= l.end_date) {
                const lBadge = document.createElement('div');
                lBadge.className = 'small px-1 rounded text-white text-truncate';
                lBadge.style.fontSize = '10px';
                lBadge.style.backgroundColor = l.is_paid ? '#3b82f6' : '#64748b';
                lBadge.title = `${l.full_name} (${l.leave_type_name}): ${l.reason}`;
                lBadge.textContent = `👤 ${l.full_name}`;
                eventsContainer.appendChild(lBadge);
            }
        });

        cell.appendChild(eventsContainer);
        grid.appendChild(cell);
    }
}
</script>

<style>
.calendar-container {
    user-select: none;
}
.progress-bar {
    transition: width 0.6s ease;
}
</style>
