<!-- app/views/category/shifts.php -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Quản lý Ca làm việc (Shifts)</h6>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addShiftModal">
            <i class="fas fa-plus"></i> Thêm Ca Mới
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                <thead class="table-light">
                    <tr>
                        <th>Mã Ca</th>
                        <th>Tên Ca</th>
                        <th>Thời gian</th>
                        <th>Giờ nghỉ</th>
                        <th>Loại Ca</th>
                        <th>Hệ số OT</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($shifts)): ?>
                        <?php foreach ($shifts as $s): ?>
                            <tr>
                                <td><?= h($s['shift_code']) ?></td>
                                <td><?= h($s['shift_name']) ?></td>
                                <td><?= h($s['start_time']) ?> - <?= h($s['end_time']) ?></td>
                                <td><?= $s['break_start'] ? h($s['break_start']) . ' - ' . h($s['break_end']) : 'Không' ?></td>
                                <td>
                                    <?php if ($s['is_night_shift']): ?>
                                        <span class="badge bg-dark">Ca Đêm</span>
                                    <?php endif; ?>
                                    <?php if ($s['is_split_shift']): ?>
                                        <span class="badge bg-warning">Ca Gãy</span>
                                    <?php endif; ?>
                                    <?php if (!$s['is_night_shift'] && !$s['is_split_shift']): ?>
                                        <span class="badge bg-info">Ca Ngày</span>
                                    <?php endif; ?>
                                </td>
                                <td>x<?= h($s['ot_rate_default']) ?></td>
                                <td>
                                    <?php if ($s['status'] === 'Active'): ?>
                                        <span class="badge bg-success">Đang hoạt động</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Ngừng hoạt động</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <!-- Edit/Delete buttons logic here -->
                                    <button class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="8" class="text-center">Chưa có dữ liệu</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Thêm Ca -->
<div class="modal fade" id="addShiftModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form action="<?= BASE_URL ?>/category/shifts" method="POST">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            <input type="hidden" name="action" value="create">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Thêm Ca Làm Việc Mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label>Mã Ca <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="shift_code" required placeholder="VD: CA1">
                        </div>
                        <div class="col-md-8 mb-3">
                            <label>Tên Ca <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="shift_name" required placeholder="VD: Ca Sáng">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Giờ bắt đầu <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" name="start_time" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Giờ kết thúc <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" name="end_time" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Bắt đầu nghỉ giữa ca</label>
                            <input type="time" class="form-control" name="break_start">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Kết thúc nghỉ giữa ca</label>
                            <input type="time" class="form-control" name="break_end">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label>Hệ số OT Mặc định</label>
                            <input type="number" step="0.1" class="form-control" name="ot_rate_default" value="1.5">
                        </div>
                        <div class="col-md-4 mb-3 d-flex align-items-end pb-2">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_night" name="is_night_shift" value="1">
                                <label class="form-check-label" for="is_night">Là Ca Đêm</label>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3 d-flex align-items-end pb-2">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_split" name="is_split_shift" value="1">
                                <label class="form-check-label" for="is_split">Là Ca Gãy</label>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label>Mô tả</label>
                        <textarea class="form-control" name="description" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Lưu Ca</button>
                </div>
            </div>
        </form>
    </div>
</div>
