<!-- app/views/category/locations.php -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Danh mục Điểm Chấm công (GPS/FaceID)</h6>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addLocationModal">
            <i class="fas fa-plus"></i> Thêm Điểm Mới
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Dự án</th>
                        <th>Tên Điểm</th>
                        <th>Loại thiết bị</th>
                        <th>Tọa độ (Lat, Lng)</th>
                        <th>Bán kính (m)</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($locations)): ?>
                        <?php foreach ($locations as $loc): ?>
                            <tr>
                                <td><?= h($loc['id']) ?></td>
                                <td><?= h($loc['project_code'] . ' - ' . $loc['project_name']) ?></td>
                                <td><?= h($loc['location_name']) ?></td>
                                <td>
                                    <?php if ($loc['type'] === 'SITE_GPS'): ?>
                                        <span class="badge bg-primary"><i class="fas fa-map-marker-alt"></i> GPS Công trường</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary"><i class="fas fa-fingerprint"></i> FaceID VP</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= $loc['latitude'] ? h($loc['latitude']) . ', ' . h($loc['longitude']) : 'N/A' ?></td>
                                <td><?= h($loc['allowed_radius_meters']) ?>m</td>
                                <td>
                                    <?php if ($loc['status'] === 'Active'): ?>
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

<!-- Modal Thêm Điểm Chấm công -->
<div class="modal fade" id="addLocationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form action="<?= BASE_URL ?>/category/locations" method="POST">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            <input type="hidden" name="action" value="create">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Thêm Điểm Chấm công Mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Thuộc Dự án <span class="text-danger">*</span></label>
                            <select class="form-select" name="project_id" required>
                                <option value="">-- Chọn Dự án --</option>
                                <?php if (!empty($projects)): ?>
                                    <?php foreach ($projects as $p): ?>
                                        <option value="<?= $p['id'] ?>"><?= h($p['project_code'] . ' - ' . $p['project_name']) ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Loại Hình <span class="text-danger">*</span></label>
                            <select class="form-select" name="type" required>
                                <option value="SITE_GPS">GPS Công trường (App Mobile)</option>
                                <option value="OFFICE_FACEID">Máy FaceID Văn phòng</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label>Tên Điểm Chấm công (Khu vực) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="location_name" required placeholder="VD: Cổng số 1, Trụ sở chính...">
                    </div>
                    <div class="mb-3">
                        <label>Địa chỉ chi tiết</label>
                        <textarea class="form-control" name="address" rows="2"></textarea>
                    </div>
                    
                    <h6 class="mt-4 border-bottom pb-2 text-primary"><i class="fas fa-satellite"></i> Tọa độ GPS & Bán kính (Dành cho Mobile App)</h6>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label>Vĩ độ (Latitude)</label>
                            <input type="text" class="form-control" name="latitude" placeholder="VD: 21.028511">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Kinh độ (Longitude)</label>
                            <input type="text" class="form-control" name="longitude" placeholder="VD: 105.804817">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Bán kính cho phép (Meters)</label>
                            <input type="number" class="form-control" name="allowed_radius_meters" value="100">
                        </div>
                    </div>

                    <h6 class="mt-3 border-bottom pb-2 text-primary"><i class="fas fa-network-wired"></i> Thiết bị mạng (Dành cho FaceID)</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Địa chỉ IP Thiết bị</label>
                            <input type="text" class="form-control" name="device_ip" placeholder="192.168.1.xxx">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Số Serial Máy (SN)</label>
                            <input type="text" class="form-control" name="device_serial">
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Lưu Điểm Chấm công</button>
                </div>
            </div>
        </form>
    </div>
</div>
