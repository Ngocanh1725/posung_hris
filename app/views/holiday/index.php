<?php
/**
 * ============================================================
 *  POSUNG HRIS – View: holiday/index.php
 *  Lịch Nghỉ Lễ Toàn Công Ty & Calendar View Nghỉ Phép Nhân Viên
 * ============================================================
 */
?>

<div class="holiday-calendar-page">
    <!-- Header Page -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h3 class="fw-bold mb-1 text-dark">
                <i class="fa-solid fa-calendar-star text-danger me-2"></i>Lịch Nghỉ Lễ & Lịch Phép Năm <?= $year ?>
            </h3>
            <p class="text-muted small mb-0">Quản lý ngày lễ Quốc gia, ngày kỷ niệm Công ty và theo dõi lịch nghỉ phép nhân viên toàn công ty</p>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <!-- Year Selector -->
            <select class="form-select form-select-sm bg-white border rounded-pill shadow-sm" style="width: 120px;" 
                    onchange="window.location.href = '<?= BASE_URL ?>/holiday?year=' + this.value + '<?= $deptId ? "&dept_id={$deptId}" : "" ?>'">
                <?php for ($y = 2024; $y <= 2028; $y++): ?>
                    <option value="<?= $y ?>" <?= $y === $year ? 'selected' : '' ?>>Năm <?= $y ?></option>
                <?php endfor; ?>
            </select>

            <?php if ($isManager): ?>
                <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#importHolidaysModal">
                    <i class="fa-solid fa-file-import me-1"></i> Import Lễ Chuẩn VN
                </button>
                <button type="button" class="btn btn-danger btn-sm rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#addHolidayModal">
                    <i class="fa-solid fa-plus me-1"></i> Thêm Ngày Lễ
                </button>
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

    <!-- Stat Cards -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white border-start border-4 border-danger">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold">Tổng ngày nghỉ lễ <?= $year ?></div>
                        <h3 class="fw-bold text-danger mt-1 mb-0"><?= $totalCount ?> <small class="fs-6 text-muted fw-normal">ngày</small></h3>
                    </div>
                    <div class="bg-danger-subtle text-danger rounded-4 p-3 fs-4">
                        <i class="fa-solid fa-gifts"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white border-start border-4 border-primary">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold">Lễ Quốc gia (Luật LĐ)</div>
                        <h3 class="fw-bold text-primary mt-1 mb-0"><?= $nationalCount ?> <small class="fs-6 text-muted fw-normal">ngày</small></h3>
                    </div>
                    <div class="bg-primary-subtle text-primary rounded-4 p-3 fs-4">
                        <i class="fa-solid fa-flag"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white border-start border-4 border-warning">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold">Lễ kỷ niệm Công ty</div>
                        <h3 class="fw-bold text-warning mt-1 mb-0"><?= $companyCount ?> <small class="fs-6 text-muted fw-normal">ngày</small></h3>
                    </div>
                    <div class="bg-warning-subtle text-warning rounded-4 p-3 fs-4">
                        <i class="fa-solid fa-building-flag"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white border-start border-4 border-success">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold">Lặp lại hàng năm</div>
                        <h3 class="fw-bold text-success mt-1 mb-0"><?= $recurringCount ?> <small class="fs-6 text-muted fw-normal">ngày</small></h3>
                    </div>
                    <div class="bg-success-subtle text-success rounded-4 p-3 fs-4">
                        <i class="fa-solid fa-arrows-rotate"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs (Calendar View vs Table View) -->
    <div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
        <div class="card-header bg-white border-bottom p-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <ul class="nav nav-pills card-header-pills" id="holidayTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active rounded-pill px-4 small fw-bold" id="calendar-tab" data-bs-toggle="pill" data-bs-target="#calendar-pane" type="button" role="tab">
                        <i class="fa-solid fa-calendar-days me-1"></i> Xem dạng Lịch (Calendar View)
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-pill px-4 small fw-bold" id="table-tab" data-bs-toggle="pill" data-bs-target="#table-pane" type="button" role="tab">
                        <i class="fa-solid fa-list me-1"></i> Danh sách Ngày lễ (Table View)
                    </button>
                </li>
            </ul>

            <div class="d-flex align-items-center gap-3 small">
                <div class="d-flex align-items-center gap-1">
                    <span class="badge rounded-circle p-1 bg-danger"> </span>
                    <span class="text-muted">Lễ Quốc gia</span>
                </div>
                <div class="d-flex align-items-center gap-1">
                    <span class="badge rounded-circle p-1 bg-warning"> </span>
                    <span class="text-muted">Lễ Công ty</span>
                </div>
                <div class="d-flex align-items-center gap-1">
                    <span class="badge rounded-circle p-1 bg-primary"> </span>
                    <span class="text-muted">Nghỉ phép đã duyệt</span>
                </div>
            </div>
        </div>

        <div class="card-body p-4">
            <div class="tab-content" id="holidayTabContent">
                
                <!-- ══════════════════════════════════════════════════
                     TAB 1: CALENDAR VIEW
                     ══════════════════════════════════════════════════ -->
                <div class="tab-pane fade show active" id="calendar-pane" role="tabpanel" tabindex="0">
                    <!-- Calendar Month Nav Controls -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm" id="calPrevMonthBtn">
                            <i class="fa-solid fa-chevron-left me-1"></i> Tháng trước
                        </button>
                        <h5 class="fw-bold mb-0 text-dark" id="currentMonthYearLabel">Tháng 1 / <?= $year ?></h5>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-light btn-sm rounded-pill px-3 border shadow-sm" id="calTodayBtn">
                                Hôm nay
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm" id="calNextMonthBtn">
                                Tháng sau <i class="fa-solid fa-chevron-right ms-1"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Calendar Grid Container -->
                    <div class="calendar-grid-wrapper bg-white rounded-4 border overflow-hidden">
                        <!-- Days of Week Header -->
                        <div class="row g-0 text-center fw-bold small text-muted border-bottom py-2 bg-light">
                            <div class="col" style="flex: 1;">Thứ 2</div>
                            <div class="col" style="flex: 1;">Thứ 3</div>
                            <div class="col" style="flex: 1;">Thứ 4</div>
                            <div class="col" style="flex: 1;">Thứ 5</div>
                            <div class="col" style="flex: 1;">Thứ 6</div>
                            <div class="col text-primary" style="flex: 1;">Thứ 7</div>
                            <div class="col text-danger" style="flex: 1;">Chủ Nhật</div>
                        </div>
                        <!-- Month Grid Days dynamically rendered via JS -->
                        <div id="calendarDaysGrid" class="row g-0"></div>
                    </div>
                </div>

                <!-- ══════════════════════════════════════════════════
                     TAB 2: TABLE VIEW
                     ══════════════════════════════════════════════════ -->
                <div class="tab-pane fade" id="table-pane" role="tabpanel" tabindex="0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light small text-muted text-uppercase">
                                <tr>
                                    <th class="ps-3">Ngày nghỉ</th>
                                    <th>Tên ngày lễ / Kỷ niệm</th>
                                    <th>Loại ngày lễ</th>
                                    <th>Định kỳ</th>
                                    <th>Phòng ban áp dụng</th>
                                    <th>Mô tả / Ý nghĩa</th>
                                    <?php if ($isManager): ?>
                                        <th class="text-end pe-3">Thao tác</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($holidays)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted">
                                            <i class="fa-solid fa-calendar-xmark fa-3x text-secondary opacity-50 mb-3 d-block"></i>
                                            <div class="fw-semibold">Chưa có ngày lễ nào được thiết lập trong năm <?= $year ?></div>
                                            <small>Bấm "Import Lễ Chuẩn VN" hoặc "Thêm Ngày Lễ" để cập nhật lịch.</small>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($holidays as $h): ?>
                                        <tr>
                                            <td class="ps-3">
                                                <div class="fw-bold text-dark font-monospace">
                                                    <?= date('d/m/Y', strtotime($h['date'])) ?>
                                                </div>
                                                <small class="text-muted">
                                                    <?php 
                                                    $dayOfWeek = date('w', strtotime($h['date']));
                                                    $dowMap = ['Chủ Nhật', 'Thứ Hai', 'Thứ Ba', 'Thứ Tư', 'Thứ Năm', 'Thứ Sáu', 'Thứ Bảy'];
                                                    echo $dowMap[$dayOfWeek];
                                                    ?>
                                                </small>
                                            </td>
                                            <td>
                                                <div class="fw-bold text-dark"><?= h($h['name']) ?></div>
                                            </td>
                                            <td>
                                                <?php if ($h['type'] === 'National'): ?>
                                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 rounded-pill">
                                                        🇻🇳 Quốc gia
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1 rounded-pill">
                                                        🏢 Công ty
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($h['is_recurring'])): ?>
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">
                                                        <i class="fa-solid fa-check me-1"></i>Hàng năm
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-light text-muted border rounded-pill">Năm <?= $year ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <small class="text-muted"><?= h($h['dept_name'] ?? 'Toàn công ty') ?></small>
                                            </td>
                                            <td>
                                                <small class="text-muted"><?= h($h['description'] ?? '---') ?></small>
                                            </td>
                                            <?php if ($isManager): ?>
                                                <td class="text-end pe-3">
                                                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-2 py-0 edit-holiday-btn"
                                                            data-id="<?= $h['id'] ?>"
                                                            data-name="<?= h($h['name']) ?>"
                                                            data-date="<?= $h['date'] ?>"
                                                            data-type="<?= $h['type'] ?>"
                                                            data-recurring="<?= $h['is_recurring'] ?>"
                                                            data-dept="<?= $h['applies_to_department_id'] ?>"
                                                            data-desc="<?= h($h['description'] ?? '') ?>">
                                                        <i class="fa-solid fa-pen-to-square"></i>
                                                    </button>
                                                    <form action="<?= BASE_URL ?>/holiday/delete/<?= $h['id'] ?>" method="POST" class="d-inline"
                                                          onsubmit="return confirm('Bạn có chắc chắn muốn xóa ngày lễ \'<?= h($h['name']) ?>\'?');">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2 py-0">
                                                            <i class="fa-solid fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Modal Thêm Ngày Lễ -->
<div class="modal fade" id="addHolidayModal" tabindex="-1" aria-labelledby="addHolidayLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form action="<?= BASE_URL ?>/holiday/store" method="POST">
                <div class="modal-header border-bottom bg-light rounded-top-4">
                    <h5 class="modal-title fw-bold text-dark" id="addHolidayLabel">
                        <i class="fa-solid fa-calendar-plus text-danger me-2"></i>Thêm Ngày Nghỉ Lễ Mới
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted">Tên ngày lễ / Kỷ niệm <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control rounded-3" placeholder="Ví dụ: Ngày Quốc khánh 2/9" required>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold small text-muted">Ngày nghỉ <span class="text-danger">*</span></label>
                            <input type="date" name="date" class="form-control rounded-3" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold small text-muted">Phân loại</label>
                            <select name="type" class="form-select rounded-3">
                                <option value="National">Lễ Quốc Gia (Luật LĐ)</option>
                                <option value="Company">Lễ Công Ty (POSUNG)</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted">Phòng ban áp dụng</label>
                        <select name="applies_to_department_id" class="form-select rounded-3">
                            <option value="">Toàn công ty (Tất cả)</option>
                            <?php foreach ($departments as $d): ?>
                                <option value="<?= $d['id'] ?>"><?= h($d['dept_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_recurring" value="1" id="recurringSwitch" checked>
                            <label class="form-check-label small fw-semibold" for="recurringSwitch">
                                Lặp lại cố định ngày này hàng năm (Recurring)
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted">Mô tả / Căn cứ pháp lý</label>
                        <textarea name="description" class="form-control rounded-3" rows="2" placeholder="Căn cứ thông báo nghỉ lễ hoặc quy định nội bộ..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Hủy bỏ</button>
                    <button type="submit" class="btn btn-danger btn-sm rounded-pill px-4 shadow-sm">
                        <i class="fa-solid fa-check me-1"></i> Lưu ngày lễ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Sửa Ngày Lễ -->
<div class="modal fade" id="editHolidayModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form id="editHolidayForm" method="POST">
                <div class="modal-header border-bottom bg-light rounded-top-4">
                    <h5 class="modal-title fw-bold text-dark">
                        <i class="fa-solid fa-pen-to-square text-primary me-2"></i>Cập nhật Ngày Nghỉ Lễ
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted">Tên ngày lễ <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="editName" class="form-control rounded-3" required>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold small text-muted">Ngày nghỉ <span class="text-danger">*</span></label>
                            <input type="date" name="date" id="editDate" class="form-control rounded-3" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold small text-muted">Phân loại</label>
                            <select name="type" id="editType" class="form-select rounded-3">
                                <option value="National">Lễ Quốc Gia (Luật LĐ)</option>
                                <option value="Company">Lễ Công Ty (POSUNG)</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted">Phòng ban áp dụng</label>
                        <select name="applies_to_department_id" id="editDept" class="form-select rounded-3">
                            <option value="">Toàn công ty (Tất cả)</option>
                            <?php foreach ($departments as $d): ?>
                                <option value="<?= $d['id'] ?>"><?= h($d['dept_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_recurring" value="1" id="editRecurring">
                            <label class="form-check-label small fw-semibold" for="editRecurring">
                                Lặp lại cố định ngày này hàng năm
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted">Mô tả</label>
                        <textarea name="description" id="editDesc" class="form-control rounded-3" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Hủy bỏ</button>
                    <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4 shadow-sm">
                        <i class="fa-solid fa-check me-1"></i> Cập nhật
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Import Lễ Chuẩn VN -->
<div class="modal fade" id="importHolidaysModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form action="<?= BASE_URL ?>/holiday/import" method="POST">
                <div class="modal-header border-bottom bg-light rounded-top-4">
                    <h5 class="modal-title fw-bold text-dark">
                        <i class="fa-solid fa-file-import text-danger me-2"></i>Import Ngày Lễ Chuẩn Việt Nam
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="small text-muted mb-3">
                        Hệ thống sẽ tự động khởi tạo danh sách các ngày nghỉ lễ tiêu chuẩn của Nước Cộng hòa Xã hội Chủ nghĩa Việt Nam và Ngày kỷ niệm thành lập Công ty:
                    </p>
                    <ul class="small text-muted mb-3 ps-3">
                        <li><strong>Tết Dương Lịch:</strong> 01/01</li>
                        <li><strong>Tết Nguyên Đán (Âm lịch):</strong> 5-7 ngày nghỉ theo lịch chính thức</li>
                        <li><strong>Giỗ Tổ Hùng Vương:</strong> 10/03 Âm lịch</li>
                        <li><strong>Ngày Giải phóng Miền Nam & Quốc tế Lao động:</strong> 30/04 & 01/05</li>
                        <li><strong>Ngày Thành Lập POSUNG E&C:</strong> 18/08 (Lễ Công ty)</li>
                        <li><strong>Quốc khánh Nước CHXHCN Việt Nam:</strong> 02/09 & ngày nghỉ liền kề</li>
                    </ul>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted">Chọn Năm áp dụng:</label>
                        <select name="year" class="form-select rounded-3">
                            <?php for ($y = 2024; $y <= 2028; $y++): ?>
                                <option value="<?= $y ?>" <?= $y === $year ? 'selected' : '' ?>>Năm <?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-top bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-danger btn-sm rounded-pill px-4 shadow-sm">
                        <i class="fa-solid fa-download me-1"></i> Bắt đầu Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.calendar-day-cell {
    min-height: 110px;
    padding: 6px;
    border-right: 1px solid #f1f5f9;
    border-bottom: 1px solid #f1f5f9;
    background-color: #fff;
    transition: background-color 0.15s ease;
}
.calendar-day-cell:hover {
    background-color: #f8fafc;
}
.calendar-day-cell.other-month {
    background-color: #f8fafc;
    opacity: 0.45;
}
.calendar-day-cell.today {
    background-color: #eff6ff;
    border: 2px solid #3b82f6 !important;
}
.calendar-day-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 4px;
}
.calendar-day-num {
    font-size: 0.85rem;
    font-weight: 700;
    color: #334155;
}
.calendar-event-badge {
    font-size: 0.72rem;
    padding: 2px 6px;
    border-radius: 6px;
    margin-bottom: 3px;
    display: block;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    color: #fff;
    cursor: pointer;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const baseUrl = '<?= BASE_URL ?>';
    let currentYear = <?= (int)$year ?>;
    let currentMonth = new Date().getMonth(); // 0-indexed (0 = Jan)

    // Khởi tạo Calendar ban đầu
    loadCalendar(currentYear, currentMonth);

    // Nút Tháng Trước / Sau / Hôm Nay
    document.getElementById('calPrevMonthBtn').addEventListener('click', () => {
        currentMonth--;
        if (currentMonth < 0) {
            currentMonth = 11;
            currentYear--;
        }
        loadCalendar(currentYear, currentMonth);
    });

    document.getElementById('calNextMonthBtn').addEventListener('click', () => {
        currentMonth++;
        if (currentMonth > 11) {
            currentMonth = 0;
            currentYear++;
        }
        loadCalendar(currentYear, currentMonth);
    });

    document.getElementById('calTodayBtn').addEventListener('click', () => {
        const now = new Date();
        currentYear = now.getFullYear();
        currentMonth = now.getMonth();
        loadCalendar(currentYear, currentMonth);
    });

    // Xử lý Edit Holiday Modal
    document.querySelectorAll('.edit-holiday-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const name = this.dataset.name;
            const date = this.dataset.date;
            const type = this.dataset.type;
            const recurring = this.dataset.recurring;
            const dept = this.dataset.dept;
            const desc = this.dataset.desc;

            const form = document.getElementById('editHolidayForm');
            form.action = baseUrl + '/holiday/update/' + id;

            document.getElementById('editName').value = name;
            document.getElementById('editDate').value = date;
            document.getElementById('editType').value = type;
            document.getElementById('editRecurring').checked = (recurring == '1');
            document.getElementById('editDept').value = dept || '';
            document.getElementById('editDesc').value = desc || '';

            const modal = new bootstrap.Modal(document.getElementById('editHolidayModal'));
            modal.show();
        });
    });

    // Động cơ nạp và vẽ Calendar Grid
    function loadCalendar(year, month) {
        // Cập nhật nhãn tháng/năm
        const monthNames = ['Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6', 
                            'Tháng 7', 'Tháng 8', 'Tháng 9', 'Tháng 10', 'Tháng 11', 'Tháng 12'];
        document.getElementById('currentMonthYearLabel').textContent = `${monthNames[month]} / ${year}`;

        // Xác định khoảng ngày để fetch events
        const firstDayOfMonth = new Date(year, month, 1);
        const lastDayOfMonth = new Date(year, month + 1, 0);

        const startDateStr = formatDateYMD(new Date(year, month, -6));
        const endDateStr = formatDateYMD(new Date(year, month + 1, 7));

        fetch(`${baseUrl}/holiday/apiEvents?start=${startDateStr}&end=${endDateStr}`)
            .then(res => res.json())
            .then(events => {
                renderCalendarGrid(year, month, events);
            })
            .catch(err => {
                console.error('Error fetching calendar events:', err);
                renderCalendarGrid(year, month, []);
            });
    }

    function renderCalendarGrid(year, month, events) {
        const container = document.getElementById('calendarDaysGrid');
        container.innerHTML = '';

        const firstDayOfMonth = new Date(year, month, 1);
        const lastDayOfMonth = new Date(year, month + 1, 0);

        // Thứ trong tuần (JS: 0=CN, 1=T2, ..., 6=T7). Chuyển sang chuẩn VN: T2 = 0, ..., CN = 6
        let firstDayIndex = firstDayOfMonth.getDay() - 1;
        if (firstDayIndex < 0) firstDayIndex = 6;

        const totalDays = lastDayOfMonth.getDate();
        const prevMonthLastDay = new Date(year, month, 0).getDate();

        const todayStr = formatDateYMD(new Date());

        // 1. Ngày của tháng trước để lấp đầy hàng đầu
        for (let i = firstDayIndex; i > 0; i--) {
            const dayNum = prevMonthLastDay - i + 1;
            const prevMonthDate = new Date(year, month - 1, dayNum);
            const dateStr = formatDateYMD(prevMonthDate);
            container.appendChild(createDayCell(dayNum, dateStr, true, todayStr === dateStr, events));
        }

        // 2. Các ngày trong tháng hiện tại
        for (let d = 1; d <= totalDays; d++) {
            const curDate = new Date(year, month, d);
            const dateStr = formatDateYMD(curDate);
            container.appendChild(createDayCell(d, dateStr, false, todayStr === dateStr, events));
        }

        // 3. Các ngày của tháng sau để lấp đầy 7 cột
        const totalRendered = firstDayIndex + totalDays;
        const remainingDays = (7 - (totalRendered % 7)) % 7;
        for (let j = 1; j <= remainingDays; j++) {
            const nextMonthDate = new Date(year, month + 1, j);
            const dateStr = formatDateYMD(nextMonthDate);
            container.appendChild(createDayCell(j, dateStr, true, todayStr === dateStr, events));
        }
    }

    function createDayCell(dayNum, dateStr, isOtherMonth, isToday, events) {
        const col = document.createElement('div');
        col.style.flex = '0 0 14.2857%';
        col.style.maxWidth = '14.2857%';
        col.className = `calendar-day-cell ${isOtherMonth ? 'other-month' : ''} ${isToday ? 'today' : ''}`;

        // Header ô ngày
        const header = document.createElement('div');
        header.className = 'calendar-day-header';
        
        const numSpan = document.createElement('span');
        numSpan.className = 'calendar-day-num';
        numSpan.textContent = dayNum;
        header.appendChild(numSpan);

        if (isToday) {
            const todayBadge = document.createElement('span');
            todayBadge.className = 'badge bg-primary rounded-pill';
            todayBadge.style.fontSize = '0.6rem';
            todayBadge.textContent = 'Hôm nay';
            header.appendChild(todayBadge);
        }

        col.appendChild(header);

        // Lọc các sự kiện rơi vào ngày này
        const dayEvents = events.filter(e => {
            if (e.type === 'Holiday') {
                return e.start === dateStr;
            } else if (e.type === 'Leave') {
                return (dateStr >= e.start && dateStr < e.end);
            }
            return false;
        });

        // Vẽ badges sự kiện
        dayEvents.forEach(e => {
            const badge = document.createElement('div');
            badge.className = 'calendar-event-badge';
            badge.style.backgroundColor = e.color || '#3b82f6';
            badge.textContent = e.title;
            badge.title = `${e.title}\n${e.description || e.reason || ''}`;
            col.appendChild(badge);
        });

        return col;
    }

    function formatDateYMD(d) {
        const year = d.getFullYear();
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }
});
</script>
