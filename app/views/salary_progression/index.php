<?php
/**
 * ============================================================
 *  POSUNG HRIS – View: salary_progression/index.php
 * ============================================================
 *  Dashboard Quản lý Diễn biến Lương & Nâng bậc Nhân sự
 * ============================================================
 */
?>
<div class="content-wrapper">
    <!-- TIÊU ĐỀ & THANH ĐIỀU HƯỚNG -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h2 class="mb-1 fw-bold text-dark"><i class="fas fa-chart-line-up text-primary me-2"></i>Lịch sử Lương & Nâng bậc Nhân sự</h2>
            <p class="text-muted mb-0">Theo dõi tiến trình thăng cấp bậc lương, điều chỉnh thu nhập và nâng lương định kỳ</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>/salaryProgression/report" class="btn btn-outline-info">
                <i class="fas fa-chart-pie me-1"></i> Báo cáo biến động
            </a>
            <a href="<?= BASE_URL ?>/salaryProgression/batchIncrease" class="btn btn-success">
                <i class="fas fa-users-gear me-1"></i> Nâng lương hàng loạt
            </a>
            <a href="<?= BASE_URL ?>/payroll" class="btn btn-outline-secondary">
                <i class="fas fa-money-check-dollar me-1"></i> Bảng tính lương
            </a>
        </div>
    </div>

    <!-- 3 THẺ KPI THỐNG KÊ -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 bg-white p-3 h-100 border-start border-4 border-primary">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Số lượt nâng bậc</span>
                        <h3 class="fw-bold mb-0 text-primary mt-1"><?= number_format($totalIncreases) ?></h3>
                        <small class="text-muted">Trong phạm vi lọc</small>
                    </div>
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 text-primary fs-3">
                        <i class="fas fa-file-signature"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 bg-white p-3 h-100 border-start border-4 border-success">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Tổng quỹ lương tăng thêm</span>
                        <h3 class="fw-bold mb-0 text-success mt-1">+<?= number_format($totalAmount, 0, ',', '.') ?> ₫</h3>
                        <small class="text-muted">Chi phí gia tăng hàng tháng</small>
                    </div>
                    <div class="rounded-circle bg-success bg-opacity-10 p-3 text-success fs-3">
                        <i class="fas fa-hand-holding-dollar"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 bg-white p-3 h-100 border-start border-4 border-warning">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Mức tăng bình quân / lượt</span>
                        <?php 
                        $avgInc = ($totalIncreases > 0) ? round($totalAmount / $totalIncreases) : 0;
                        ?>
                        <h3 class="fw-bold mb-0 text-warning mt-1">+<?= number_format($avgInc, 0, ',', '.') ?> ₫</h3>
                        <small class="text-muted">Trung bình mỗi quyết định</small>
                    </div>
                    <div class="rounded-circle bg-warning bg-opacity-10 p-3 text-warning fs-3">
                        <i class="fas fa-arrow-trend-up"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- BỘ LỌC TÌM KIẾM -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-3">
            <form action="<?= BASE_URL ?>/salaryProgression" method="GET" class="row g-2 align-items-center">
                <div class="col-md-3">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="fas fa-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Tên nhân viên, mã NV, số QĐ..." value="<?= h($filters['search']) ?>">
                    </div>
                </div>

                <div class="col-md-2">
                    <select name="department_id" class="form-select">
                        <option value="">-- Tất cả phòng ban --</option>
                        <?php foreach ($departments as $d): ?>
                            <option value="<?= $d['id'] ?>" <?= $filters['department_id'] == $d['id'] ? 'selected' : '' ?>>
                                <?= h($d['dept_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <select name="project_id" class="form-select">
                        <option value="">-- Tất cả dự án --</option>
                        <?php foreach ($projects as $prj): ?>
                            <option value="<?= $prj['id'] ?>" <?= $filters['project_id'] == $prj['id'] ? 'selected' : '' ?>>
                                <?= h($prj['project_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <select name="year" class="form-select">
                        <option value="">-- Tất cả các năm --</option>
                        <?php for ($y = date('Y'); $y >= 2022; $y--): ?>
                            <option value="<?= $y ?>" <?= $filters['year'] == $y ? 'selected' : '' ?>>Năm <?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1"><i class="fas fa-filter me-1"></i> Lọc</button>
                    <a href="<?= BASE_URL ?>/salaryProgression" class="btn btn-light" title="Đặt lại bộ lọc"><i class="fas fa-rotate-left"></i></a>
                </div>
            </form>
        </div>
    </div>

    <!-- DANH SÁCH LỊCH SỬ NÂNG BẬC TOÀN CÔNG TY -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-list-check text-primary me-2"></i>Danh sách Quyết định Nâng lương</h5>
            <span class="badge bg-secondary"><?= count($progressions) ?> quyết định</span>
        </div>
        <div class="card-body p-0">
            <?php if (empty($progressions)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-calendar-xmark fa-3x mb-3 text-secondary"></i>
                    <h5 class="fw-semibold">Không tìm thấy quyết định nâng bậc nào phù hợp</h5>
                    <p class="small">Hãy thử thay đổi tiêu chí lọc hoặc thực hiện nâng lương mới.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3" style="width: 110px;">Ngày H.Lực</th>
                                <th>Nhân sự</th>
                                <th>Phòng ban / Chức vụ</th>
                                <th class="text-end">Mức lương cũ</th>
                                <th class="text-end">Mức lương mới</th>
                                <th class="text-center">Biến động</th>
                                <th>Lý do điều chỉnh</th>
                                <th>Số QĐ / Ngày ký</th>
                                <th>Người duyệt</th>
                                <th class="text-end pe-3" style="width: 120px;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($progressions as $p): ?>
                                <tr>
                                    <td class="ps-3 fw-medium font-monospace"><?= fmtDate($p->effective_date) ?></td>
                                    <td>
                                        <a href="<?= BASE_URL ?>/salaryProgression/index/<?= $p->employee_id ?>" class="text-decoration-none fw-semibold text-dark">
                                            <?= h($p->employee_name) ?>
                                        </a>
                                        <small class="text-muted font-monospace d-block"><?= h($p->emp_code) ?></small>
                                    </td>
                                    <td>
                                        <div class="small fw-semibold"><?= h($p->dept_name ?? '---') ?></div>
                                        <small class="text-muted"><?= h($p->pos_title ?? '') ?></small>
                                    </td>
                                    <td class="text-end font-monospace text-muted">
                                        <?= number_format($p->old_salary ?? 0, 0, ',', '.') ?> ₫
                                    </td>
                                    <td class="text-end font-monospace fw-bold text-dark">
                                        <?= number_format($p->new_salary ?? $p->base_salary ?? 0, 0, ',', '.') ?> ₫
                                    </td>
                                    <td class="text-center">
                                        <?php if ($p->increase_amount > 0): ?>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                                <i class="fas fa-arrow-up me-1"></i>+<?= number_format($p->increase_amount, 0, ',', '.') ?> ₫ (+<?= $p->increase_percent ?>%)
                                            </span>
                                        <?php elseif ($p->increase_amount < 0): ?>
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                                <i class="fas fa-arrow-down me-1"></i><?= number_format($p->increase_amount, 0, ',', '.') ?> ₫ (<?= $p->increase_percent ?>%)
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-light text-muted border">Khởi điểm</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border"><?= h($p->reason ?: 'Điều chỉnh lương') ?></span>
                                        <?php if (!empty($p->notes)): ?>
                                            <small class="text-muted d-block text-truncate" style="max-width: 220px;" title="<?= h($p->notes) ?>"><?= h($p->notes) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-primary font-monospace small"><?= h($p->decision_num ?: '---') ?></div>
                                        <?php if (!empty($p->decision_date)): ?>
                                            <small class="text-muted"><?= fmtDate($p->decision_date) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small class="text-muted"><?= h($p->approver_name ?: 'HĐQT / BGĐ') ?></small>
                                    </td>
                                    <td class="text-end pe-3">
                                        <a href="<?= BASE_URL ?>/salaryProgression/index/<?= $p->employee_id ?>" class="btn btn-sm btn-outline-primary" title="Xem Timeline lịch sử lương">
                                            <i class="fas fa-timeline me-1"></i> Timeline
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
