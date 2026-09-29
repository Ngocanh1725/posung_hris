<?php
/**
 * ============================================================
 *  POSUNG HRIS – View: asset/create.php
 * ============================================================
 *  Form Thêm Mới Tài sản Doanh nghiệp
 * ============================================================
 */
?>
<div class="content-wrapper">
    <div class="mb-4">
        <a href="<?= BASE_URL ?>/asset" class="text-decoration-none text-muted small">
            <i class="fas fa-arrow-left me-1"></i> Quay lại danh sách tài sản
        </a>
        <h2 class="mt-2 fw-bold text-dark"><i class="fas fa-plus-circle text-primary me-2"></i>Thêm Mới Tài sản Công ty</h2>
        <p class="text-muted mb-0">Khai báo thiết bị, phương tiện hoặc tài sản đưa vào theo dõi và quản trị cấp phát</p>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-info-circle text-primary me-2"></i>Thông tin chi tiết tài sản</h5>
                </div>
                <div class="card-body p-4">
                    <form action="<?= BASE_URL ?>/asset/store" method="POST" id="createAssetForm">
                        <input type="hidden" name="_csrf_token" value="<?= Session::generateCsrfToken() ?>">

                        <!-- HÀNG 1: Loại tài sản & Mã tài sản -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Loại tài sản <span class="text-danger">*</span></label>
                                <select name="category_id" id="categoryIdSelect" class="form-select" required onchange="fetchSuggestedCode(this.value)">
                                    <option value="">-- Chọn danh mục --</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= $cat->id ?>">
                                            <?= h($cat->name) ?> (<?= h($cat->category_code) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Mã quản lý tài sản <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="text" name="asset_code" id="assetCodeInput" class="form-control font-monospace text-uppercase" placeholder="Ví dụ: AST-IT-0001" value="<?= h($suggestedCode ?? '') ?>" required>
                                    <button class="btn btn-outline-secondary" type="button" onclick="refreshCode()" title="Tự động sinh mã mới">
                                        <i class="fas fa-wand-magic-sparkles"></i>
                                    </button>
                                </div>
                                <small class="text-muted">Mã duy nhất để định danh và in tem nhãn tài sản.</small>
                            </div>
                        </div>

                        <!-- HÀNG 2: Tên tài sản & Số Serial -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">Tên tài sản / Model <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" placeholder="Ví dụ: Laptop Dell Latitude 7420 (Core i7, 16GB, 512GB)" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Số Serial / Service Tag / IMEI</label>
                                <input type="text" name="serial_number" class="form-control font-monospace" placeholder="Số hiệu phần cứng">
                            </div>
                        </div>

                        <!-- HÀNG 3: Ngày mua & Nguyên giá & Hạn bảo hành -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Ngày mua sắm</label>
                                <input type="date" name="purchase_date" class="form-control" value="<?= date('Y-m-d') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Nguyên giá mua sắm (VNĐ)</label>
                                <div class="input-group">
                                    <input type="number" step="1000" name="purchase_cost" class="form-control" placeholder="0" value="0">
                                    <span class="input-group-text">₫</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Hạn bảo hành</label>
                                <input type="date" name="warranty_expiry" class="form-control">
                            </div>
                        </div>

                        <!-- HÀNG 4: Tình trạng & Trạng thái -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tình trạng vật lý ban đầu</label>
                                <select name="condition" class="form-select">
                                    <option value="New" selected>Mới 100% (New)</option>
                                    <option value="Good">Tốt (Good)</option>
                                    <option value="Fair">Bình thường (Fair)</option>
                                    <option value="Damaged">Hư hỏng / Lỗi (Damaged)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Trạng thái quản lý ban đầu</label>
                                <select name="status" class="form-select">
                                    <option value="Available" selected>Sẵn sàng cấp phát (Lưu kho)</option>
                                    <option value="Maintenance">Đang kiểm định / Bảo dưỡng</option>
                                    <option value="Disposed">Thanh lý</option>
                                </select>
                                <small class="text-muted">Để bàn giao cho nhân viên, hãy tạo tài sản rồi bấm "Bàn giao".</small>
                            </div>
                        </div>

                        <!-- HÀNG 5: Vị trí lưu kho & Dự án -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Vị trí đặt tài sản / Phòng lưu kho</label>
                                <input type="text" name="location" class="form-control" placeholder="Ví dụ: Kho IT - Tầng 3 Trụ sở hoặc Công trường Dự án A">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Thuộc Dự án (Nếu phân bổ theo dự án)</label>
                                <select name="project_id" class="form-select">
                                    <option value="">-- Dùng chung toàn công ty --</option>
                                    <?php foreach ($projects as $prj): ?>
                                        <option value="<?= $prj['id'] ?>">
                                            <?= h($prj['project_code']) ?> - <?= h($prj['project_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- HÀNG 6: Ghi chú & Cấu hình -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Ghi chú kỹ thuật / Phụ kiện đi kèm</label>
                            <textarea name="notes" class="form-control" rows="4" placeholder="Cấu hình máy, tình trạng phụ kiện sạc/cáp, thông tin nhà cung cấp..."></textarea>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="<?= BASE_URL ?>/asset" class="btn btn-light px-4">Hủy bỏ</a>
                            <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save me-1"></i> Lưu tài sản</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- HƯỚNG DẪN & QUY ĐỊNH QUẢN TRỊ TÀI SẢN -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-3 bg-light mb-3">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-dark mb-3"><i class="fas fa-lightbulb text-warning me-2"></i>Quy chuẩn Mã tài sản</h6>
                    <ul class="small text-muted ps-3 mb-0 space-y-2">
                        <li><strong>AST-IT-XXXX:</strong> Thiết bị Công nghệ thông tin (Laptop, Màn hình, Máy in...).</li>
                        <li><strong>AST-VEH-XXXX:</strong> Phương tiện vận tải (Xe ô tô con, xe bán tải, xe máy).</li>
                        <li><strong>AST-CON-XXXX:</strong> Máy móc & Công cụ thi công (Máy thủy chuẩn, bộ đàm...).</li>
                        <li><strong>AST-FUR-XXXX:</strong> Nội thất & Thiết bị văn phòng (Bàn, ghế cao cấp, két sắt).</li>
                        <li><strong>AST-ACC-XXXX:</strong> Thẻ từ vào cổng, chìa khóa tổng, USB Token.</li>
                    </ul>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-dark mb-3"><i class="fas fa-shield-alt text-success me-2"></i>Chính sách Cấp phát & Bồi thường</h6>
                    <p class="small text-muted mb-2">Mọi tài sản khi bàn giao đều phải được lập <strong>Biên bản bàn giao chuẩn A4</strong> có chữ ký xác nhận của Người lao động và Phòng Hành chính - Nhân sự.</p>
                    <p class="small text-muted mb-0">Khi nhân viên làm mất mát, hư hỏng do lỗi chủ quan, việc bồi thường sẽ căn cứ theo giá trị còn lại trên hệ thống.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function fetchSuggestedCode(catId) {
    if (!catId) return;
    fetch('<?= BASE_URL ?>/asset/ajaxSuggestCode?category_id=' + catId)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.code) {
                document.getElementById('assetCodeInput').value = data.code;
            }
        })
        .catch(err => console.error(err));
}

function refreshCode() {
    var catId = document.getElementById('categoryIdSelect').value;
    if (catId) {
        fetchSuggestedCode(catId);
    } else {
        alert('Vui lòng chọn loại tài sản trước.');
    }
}
</script>
