<?php
require_once APP_ROOT . '/views/ess/layout/header.php';

$avatarSrc = !empty($employee['avatar_path']) && file_exists(APP_ROOT . '/../public/' . $employee['avatar_path'])
    ? BASE_URL . '/' . $employee['avatar_path']
    : null;
$firstLetter = mb_strtoupper(mb_substr($employee['full_name'] ?? 'N', 0, 1, 'UTF-8'), 'UTF-8');
?>

<div class="container-xl">
    
    <!-- Header Page -->
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-2">
        <div>
            <h4 class="fw-bold mb-1 text-dark">
                <i class="fa-solid fa-id-badge text-primary me-2"></i> Hồ sơ nhân sự cá nhân
            </h4>
            <p class="text-muted small mb-0">Tra cứu thông tin hồ sơ và chủ động cập nhật thông tin liên hệ, người phụ thuộc.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>/ess/dashboard" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="fa-solid fa-arrow-left me-1"></i> Trang chủ
            </a>
        </div>
    </div>

    <!-- Alert Yêu cầu thay đổi hồ sơ đang chờ HR duyệt -->
    <?php if (!empty($pendingChanges)): ?>
        <div class="alert alert-warning border border-warning shadow-sm rounded-3 mb-4">
            <div class="d-flex align-items-center gap-2 mb-2">
                <i class="fa-solid fa-clock-rotate-left fs-5 text-warning"></i>
                <h6 class="fw-bold mb-0">Yêu cầu thay đổi hồ sơ đang chờ Phòng Nhân sự phê duyệt</h6>
            </div>
            <p class="small text-muted mb-2">Các thông tin quan trọng dưới đây đã được gửi yêu cầu và đang chờ HR xác thực trước khi cập nhật vào hồ sơ chính thức:</p>
            <div class="table-responsive">
                <table class="table table-sm table-bordered bg-white mb-0 small">
                    <thead class="table-light">
                        <tr>
                            <th>Mục thay đổi</th>
                            <th>Giá trị hiện tại</th>
                            <th>Giá trị mới đề xuất</th>
                            <th>Thời gian gửi</th>
                            <th>Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pendingChanges as $pc): ?>
                            <tr>
                                <td class="fw-semibold text-primary"><?= htmlspecialchars($pc['field_label'] ?: $pc['field_name']) ?></td>
                                <td class="text-danger"><del><?= htmlspecialchars($pc['old_value'] ?: '(Trống)') ?></del></td>
                                <td class="text-success fw-bold"><ins><?= htmlspecialchars($pc['new_value'] ?: '(Trống)') ?></ins></td>
                                <td class="text-muted"><?= date('d/m/Y H:i', strtotime($pc['created_at'])) ?></td>
                                <td><span class="badge bg-warning text-dark"><i class="fa-solid fa-spinner fa-spin me-1"></i> Chờ HR duyệt</span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <!-- Nav Tabs -->
    <ul class="nav nav-pills mb-4 gap-2 bg-white p-2 rounded-3 shadow-sm border" id="profileTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active rounded-pill px-3 py-2 fw-semibold small" id="view-tab" data-bs-toggle="tab" data-bs-target="#tab-view" type="button" role="tab">
                <i class="fa-solid fa-user-check me-1"></i> 1. Xem thông tin hồ sơ (Chỉ đọc)
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill px-3 py-2 fw-semibold small" id="edit-tab" data-bs-toggle="tab" data-bs-target="#tab-edit" type="button" role="tab">
                <i class="fa-solid fa-user-pen me-1"></i> 2. Cập nhật thông tin được phép
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill px-3 py-2 fw-semibold small" id="dep-tab" data-bs-toggle="tab" data-bs-target="#tab-dep" type="button" role="tab">
                <i class="fa-solid fa-people-roof me-1"></i> 3. Người phụ thuộc & Giảm trừ gia cảnh (<?= count($dependents ?? []) ?>)
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill px-3 py-2 fw-semibold small" id="ins-tab" data-bs-toggle="tab" data-bs-target="#tab-ins" type="button" role="tab">
                <i class="fa-solid fa-hand-holding-medical me-1"></i> 4. Thông tin Bảo hiểm Xã hội
            </button>
        </li>
    </ul>

    <div class="tab-content" id="profileTabContent">
        
        <!-- ══════════════════════════════════════════════════════
             TAB 1: XEM HỒ SƠ (CHỈ ĐỌC)
             ══════════════════════════════════════════════════════ -->
        <div class="tab-pane fade show active" id="tab-view" role="tabpanel">
            <div class="row g-4">
                <!-- Cột trái: Tóm tắt & Ảnh đại diện -->
                <div class="col-lg-4">
                    <div class="card-custom text-center p-4 mb-4">
                        <?php if ($avatarSrc): ?>
                            <img src="<?= $avatarSrc ?>" alt="<?= htmlspecialchars($employee['full_name']) ?>" 
                                 class="rounded-circle shadow mx-auto mb-3" style="width: 120px; height: 120px; object-fit: cover; border: 4px solid #0088cc;">
                        <?php else: ?>
                            <div class="rounded-circle shadow mx-auto mb-3 d-flex align-items-center justify-content-center text-white fw-bold display-4"
                                 style="width: 120px; height: 120px; background: linear-gradient(135deg, #0d3c61, #0088cc); border: 4px solid #e0f2fe;">
                                <?= $firstLetter ?>
                            </div>
                        <?php endif; ?>

                        <h5 class="fw-bold mb-1"><?= htmlspecialchars($employee['full_name'] ?? '') ?></h5>
                        <p class="text-primary fw-semibold small mb-2"><?= htmlspecialchars($employee['emp_code'] ?? '') ?></p>
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1 rounded-pill mb-3">
                            <i class="fa-solid fa-circle-check me-1"></i> <?= htmlspecialchars($employee['status'] ?? 'Active') ?>
                        </span>

                        <hr class="my-3">

                        <div class="text-start small">
                            <div class="mb-2 d-flex justify-content-between">
                                <span class="text-muted">Chức vụ:</span>
                                <strong class="text-dark"><?= htmlspecialchars($employee['pos_title'] ?? '—') ?></strong>
                            </div>
                            <div class="mb-2 d-flex justify-content-between">
                                <span class="text-muted">Phòng ban:</span>
                                <strong class="text-dark"><?= htmlspecialchars($employee['dept_name'] ?? '—') ?></strong>
                            </div>
                            <div class="mb-2 d-flex justify-content-between">
                                <span class="text-muted">Dự án hiện tại:</span>
                                <strong class="text-primary"><?= htmlspecialchars($employee['project_name'] ?? 'Văn phòng chính') ?></strong>
                            </div>
                            <div class="mb-2 d-flex justify-content-between">
                                <span class="text-muted">Quản lý trực tiếp:</span>
                                <strong class="text-dark"><?= htmlspecialchars($employee['manager_name'] ?? '—') ?></strong>
                            </div>
                            <div class="mb-2 d-flex justify-content-between">
                                <span class="text-muted">Ngày vào làm:</span>
                                <strong class="text-dark"><?= !empty($employee['join_date']) ? date('d/m/Y', strtotime($employee['join_date'])) : '—' ?></strong>
                            </div>
                            <div class="mb-2 d-flex justify-content-between">
                                <span class="text-muted">Loại hợp đồng:</span>
                                <span class="badge bg-light text-dark border"><?= htmlspecialchars($employee['contract_status'] ?? 'Thử việc') ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cột phải: Thông tin định danh & bằng cấp chuyên môn -->
                <div class="col-lg-8">
                    <!-- Thông tin định danh (CCCD, thuế, BH) -->
                    <div class="card-custom mb-4">
                        <div class="card-custom-header">
                            <h5 class="card-custom-title">
                                <i class="fa-solid fa-address-card text-primary"></i> Giấy tờ định danh & Thuế (Bảo mật - Chỉ đọc)
                            </h5>
                        </div>
                        <div class="card-custom-body">
                            <div class="row g-3 small">
                                <div class="col-md-6">
                                    <label class="text-muted d-block">Số CCCD / CMND:</label>
                                    <span class="fw-bold text-dark fs-6 font-monospace">
                                        <?= !empty($employee['id_card_no']) ? htmlspecialchars($employee['id_card_no']) : 'Chưa có' ?>
                                    </span>
                                </div>
                                <div class="col-md-3">
                                    <label class="text-muted d-block">Ngày cấp:</label>
                                    <span class="fw-semibold text-dark">
                                        <?= !empty($employee['id_card_date']) ? date('d/m/Y', strtotime($employee['id_card_date'])) : '—' ?>
                                    </span>
                                </div>
                                <div class="col-md-3">
                                    <label class="text-muted d-block">Nơi cấp:</label>
                                    <span class="fw-semibold text-dark">
                                        <?= htmlspecialchars($employee['id_card_place'] ?? 'Cục CS QLHC') ?>
                                    </span>
                                </div>
                                <div class="col-md-4">
                                    <label class="text-muted d-block">Mã số thuế cá nhân:</label>
                                    <span class="fw-bold text-dark font-monospace">
                                        <?= htmlspecialchars($employee['tax_code'] ?? 'Chưa cập nhật') ?>
                                    </span>
                                </div>
                                <div class="col-md-4">
                                    <label class="text-muted d-block">Số sổ BHXH:</label>
                                    <span class="fw-bold text-primary font-monospace">
                                        <?= htmlspecialchars($employee['social_insurance_no'] ?? 'Chưa cập nhật') ?>
                                    </span>
                                </div>
                                <div class="col-md-4">
                                    <label class="text-muted d-block">Số thẻ BHYT:</label>
                                    <span class="fw-bold text-success font-monospace">
                                        <?= htmlspecialchars($employee['health_insurance_no'] ?? 'Chưa cập nhật') ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Thông tin cá nhân cơ bản -->
                    <div class="card-custom mb-4">
                        <div class="card-custom-header">
                            <h5 class="card-custom-title">
                                <i class="fa-solid fa-cake-candles text-primary"></i> Thông tin ngày sinh, quê quán
                            </h5>
                        </div>
                        <div class="card-custom-body">
                            <div class="row g-3 small">
                                <div class="col-md-4">
                                    <label class="text-muted d-block">Ngày sinh:</label>
                                    <span class="fw-semibold text-dark">
                                        <?= !empty($employee['birth_date']) ? date('d/m/Y', strtotime($employee['birth_date'])) : '—' ?>
                                    </span>
                                </div>
                                <div class="col-md-4">
                                    <label class="text-muted d-block">Giới tính:</label>
                                    <span class="fw-semibold text-dark">
                                        <?= match($employee['gender'] ?? '') { 'Male' => 'Nam', 'Female' => 'Nữ', default => 'Khác' } ?>
                                    </span>
                                </div>
                                <div class="col-md-4">
                                    <label class="text-muted d-block">Tình trạng hôn nhân:</label>
                                    <span class="fw-semibold text-dark"><?= htmlspecialchars($employee['marital_status'] ?? '—') ?></span>
                                </div>
                                <div class="col-md-4">
                                    <label class="text-muted d-block">Nơi sinh:</label>
                                    <span class="fw-semibold text-dark"><?= htmlspecialchars($employee['birth_place'] ?? '—') ?></span>
                                </div>
                                <div class="col-md-4">
                                    <label class="text-muted d-block">Nguyên quán:</label>
                                    <span class="fw-semibold text-dark"><?= htmlspecialchars($employee['native_place'] ?? '—') ?></span>
                                </div>
                                <div class="col-md-4">
                                    <label class="text-muted d-block">Dân tộc / Quốc tịch:</label>
                                    <span class="fw-semibold text-dark">
                                        <?= htmlspecialchars($employee['ethnic'] ?? 'Kinh') ?> / <?= htmlspecialchars($employee['nationality'] ?? 'Việt Nam') ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bằng cấp & Trình độ học vấn -->
                    <div class="card-custom">
                        <div class="card-custom-header">
                            <h5 class="card-custom-title">
                                <i class="fa-solid fa-graduation-cap text-primary"></i> Học vấn & Kỹ năng chuyên môn
                            </h5>
                        </div>
                        <div class="card-custom-body">
                            <div class="row g-3 small">
                                <div class="col-md-4">
                                    <label class="text-muted d-block">Trình độ cao nhất:</label>
                                    <span class="fw-bold text-dark"><?= htmlspecialchars($employee['highest_degree'] ?? 'Đại học') ?></span>
                                </div>
                                <div class="col-md-4">
                                    <label class="text-muted d-block">Chuyên ngành đào tạo:</label>
                                    <span class="fw-semibold text-dark"><?= htmlspecialchars($employee['degree_title'] ?? 'Kỹ thuật / Kinh tế') ?></span>
                                </div>
                                <div class="col-md-4">
                                    <label class="text-muted d-block">Trường đào tạo:</label>
                                    <span class="fw-semibold text-dark"><?= htmlspecialchars($employee['training_school'] ?? '—') ?></span>
                                </div>
                                <div class="col-md-4">
                                    <label class="text-muted d-block">Trình độ tiếng Anh:</label>
                                    <span class="fw-semibold text-dark"><?= htmlspecialchars($employee['english_level'] ?? 'Cơ bản') ?></span>
                                </div>
                                <div class="col-md-4">
                                    <label class="text-muted d-block">Trình độ tiếng Hàn:</label>
                                    <span class="fw-semibold text-dark"><?= htmlspecialchars($employee['korean_level'] ?? 'Không') ?></span>
                                </div>
                                <div class="col-md-4">
                                    <label class="text-muted d-block">Thẻ An toàn Lao động (HSE):</label>
                                    <span class="fw-bold text-success">
                                        <?= htmlspecialchars($employee['hse_card_number'] ?? 'Đã cấp thẻ an toàn') ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ══════════════════════════════════════════════════════
             TAB 2: CẬP NHẬT THÔNG TIN ĐƯỢC PHÉP
             ══════════════════════════════════════════════════════ -->
        <div class="tab-pane fade" id="tab-edit" role="tabpanel">
            <div class="card-custom p-4">
                <form action="<?= BASE_URL ?>/ess/updateProfile" method="POST" enctype="multipart/form-data">
                    <?= Session::csrfField() ?>
                    <input type="hidden" name="action_type" value="update_contact">

                    <h5 class="fw-bold text-dark mb-3 border-bottom pb-2">
                        <i class="fa-solid fa-phone text-primary me-2"></i> Thông tin liên hệ cá nhân
                    </h5>
                    
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Số điện thoại di động (*): <span class="badge bg-success-subtle text-success border border-success-subtle ms-1"><i class="fa-solid fa-bolt"></i> Cập nhật ngay</span></label>
                            <input type="text" class="form-control" name="phone" 
                                   value="<?= htmlspecialchars($employee['phone'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Địa chỉ Email liên hệ (*): <span class="badge bg-success-subtle text-success border border-success-subtle ms-1"><i class="fa-solid fa-bolt"></i> Cập nhật ngay</span></label>
                            <input type="email" class="form-control" name="email" 
                                   value="<?= htmlspecialchars($employee['email'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">
                                Địa chỉ thường trú (theo sổ hộ khẩu/CCCD): 
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle ms-1"><i class="fa-solid fa-shield-halved"></i> Cần HR duyệt</span>
                            </label>
                            <input type="text" class="form-control" name="home_address" 
                                   value="<?= htmlspecialchars($employee['home_address'] ?? '') ?>" 
                                   placeholder="Số nhà, đường, phường/xã, quận/huyện, tỉnh/thành phố">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Địa chỉ nơi ở hiện tại (tạm trú): <span class="badge bg-success-subtle text-success border border-success-subtle ms-1"><i class="fa-solid fa-bolt"></i> Cập nhật ngay</span></label>
                            <input type="text" class="form-control" name="current_address" 
                                   value="<?= htmlspecialchars($employee['current_address'] ?? '') ?>"
                                   placeholder="Địa chỉ đang sinh sống thực tế">
                        </div>
                    </div>

                    <h5 class="fw-bold text-dark mb-3 border-bottom pb-2">
                        <i class="fa-solid fa-id-card text-info me-2"></i> Giấy tờ định danh & Căn cước công dân (CCCD)
                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle ms-1 small"><i class="fa-solid fa-shield-halved"></i> Cần HR duyệt</span>
                    </h5>

                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Số CCCD / CMND:</label>
                            <input type="text" class="form-control font-monospace" name="id_card_no" 
                                   value="<?= htmlspecialchars($employee['id_card_no'] ?? '') ?>"
                                   placeholder="Số thẻ CCCD 12 số">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Ngày cấp:</label>
                            <input type="date" class="form-control" name="id_card_date" 
                                   value="<?= !empty($employee['id_card_date']) ? date('Y-m-d', strtotime($employee['id_card_date'])) : '' ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Nơi cấp:</label>
                            <input type="text" class="form-control" name="id_card_place" 
                                   value="<?= htmlspecialchars($employee['id_card_place'] ?? '') ?>"
                                   placeholder="Cục CS QLHC về TTXH">
                        </div>
                    </div>

                    <h5 class="fw-bold text-dark mb-3 border-bottom pb-2">
                        <i class="fa-solid fa-kit-medical text-danger me-2"></i> Liên hệ khẩn cấp (Emergency Contact)
                        <span class="badge bg-success-subtle text-success border border-success-subtle ms-1 small"><i class="fa-solid fa-bolt"></i> Cập nhật ngay</span>
                    </h5>
                    
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Họ và tên người liên hệ:</label>
                            <input type="text" class="form-control" name="emergency_contact_name" 
                                   value="<?= htmlspecialchars($employee['emergency_contact_name'] ?? '') ?>"
                                   placeholder="VD: Nguyễn Thị Lan">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Số điện thoại khẩn cấp:</label>
                            <input type="text" class="form-control" name="emergency_contact_phone" 
                                   value="<?= htmlspecialchars($employee['emergency_contact_phone'] ?? '') ?>"
                                   placeholder="VD: 0988123456">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Mối quan hệ:</label>
                            <input type="text" class="form-control" name="emergency_contact_relation" 
                                   value="<?= htmlspecialchars($employee['emergency_contact_relation'] ?? '') ?>"
                                   placeholder="Vợ/Chồng, Bố/Mẹ, Anh/Chị">
                        </div>
                    </div>

                    <h5 class="fw-bold text-dark mb-3 border-bottom pb-2">
                        <i class="fa-solid fa-building-columns text-success me-2"></i> Tài khoản nhận lương ngân hàng
                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle ms-1 small"><i class="fa-solid fa-shield-halved"></i> Cần HR duyệt</span>
                    </h5>
                    
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Số tài khoản ngân hàng:</label>
                            <input type="text" class="form-control font-monospace" name="bank_account_no" 
                                   value="<?= htmlspecialchars($employee['bank_account_no'] ?? '') ?>"
                                   placeholder="Nhập số tài khoản">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Tên ngân hàng:</label>
                            <input type="text" class="form-control" name="bank_name" 
                                   value="<?= htmlspecialchars($employee['bank_name'] ?? '') ?>"
                                   placeholder="VD: Vietcombank, Techcombank, BIDV">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Chi nhánh ngân hàng:</label>
                            <input type="text" class="form-control" name="bank_branch" 
                                   value="<?= htmlspecialchars($employee['bank_branch'] ?? '') ?>"
                                   placeholder="VD: Chi nhánh Thăng Long">
                        </div>
                    </div>

                    <h5 class="fw-bold text-dark mb-3 border-bottom pb-2">
                        <i class="fa-solid fa-vest text-warning me-2"></i> Size trang phục & Trang bị Bảo hộ lao động (PPE)
                    </h5>
                    
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Cỡ giày bảo hộ (Shoe Size):</label>
                            <select class="form-select" name="safety_shoe_size">
                                <option value="">-- Chọn cỡ giày --</option>
                                <?php for ($s = 37; $s <= 45; $s++): ?>
                                    <option value="<?= $s ?>" <?= ($employee['safety_shoe_size'] ?? '') == (string)$s ? 'selected' : '' ?>>
                                        Size <?= $s ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Cỡ quần áo bảo hộ (Uniform Size):</label>
                            <select class="form-select" name="safety_uniform_size">
                                <option value="">-- Chọn cỡ áo --</option>
                                <?php foreach (['S', 'M', 'L', 'XL', '2XL', '3XL'] as $sz): ?>
                                    <option value="<?= $sz ?>" <?= ($employee['safety_uniform_size'] ?? '') == $sz ? 'selected' : '' ?>>
                                        Size <?= $sz ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Tải lên ảnh chân dung mới (Avatar):</label>
                            <input type="file" class="form-control" name="avatar" accept="image/png, image/jpeg, image/webp">
                            <small class="text-muted" style="font-size: 0.72rem;">Định dạng: JPG, PNG, WebP (Tối đa 2MB)</small>
                        </div>
                    </div>

                    <div class="text-end pt-3 border-top">
                        <button type="submit" class="btn btn-primary px-4 fw-semibold rounded-pill">
                            <i class="fa-solid fa-floppy-disk me-1"></i> Lưu thay đổi thông tin
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ══════════════════════════════════════════════════════
             TAB 3: NGƯỜI PHỤ THUỘC (GIẢM TRỪ GIA CẢNH)
             ══════════════════════════════════════════════════════ -->
        <div class="tab-pane fade" id="tab-dep" role="tabpanel">
            <div class="row g-4">
                <!-- Danh sách người phụ thuộc hiện có -->
                <div class="col-lg-7">
                    <div class="card-custom h-100">
                        <div class="card-custom-header">
                            <h5 class="card-custom-title">
                                <i class="fa-solid fa-list-check text-primary"></i> Danh sách người phụ thuộc hiện tại
                            </h5>
                            <span class="badge bg-primary rounded-pill"><?= count($dependents ?? []) ?> người</span>
                        </div>
                        <div class="card-custom-body p-0">
                            <?php if (!empty($dependents)): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0 small">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Họ và tên</th>
                                                <th>Mối quan hệ</th>
                                                <th>Ngày sinh</th>
                                                <th>Mã định danh/CCCD</th>
                                                <th>Giảm trừ thuế</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($dependents as $dep): ?>
                                                <tr>
                                                    <td class="fw-bold text-dark"><?= htmlspecialchars($dep['full_name']) ?></td>
                                                    <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($dep['relationship']) ?></span></td>
                                                    <td><?= !empty($dep['birth_date']) ? date('d/m/Y', strtotime($dep['birth_date'])) : '—' ?></td>
                                                    <td class="font-monospace text-muted"><?= htmlspecialchars($dep['id_number'] ?? '—') ?></td>
                                                    <td>
                                                        <?php if (!empty($dep['is_tax_dependent'])): ?>
                                                            <span class="badge bg-success-subtle text-success border border-success-subtle">
                                                                <i class="fa-solid fa-check me-1"></i> Có giảm trừ
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="badge bg-secondary-subtle text-secondary">Chưa duyệt</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-5 text-muted small">
                                    <i class="fa-solid fa-people-roof fa-3x text-secondary opacity-50 mb-3"></i>
                                    <p>Bạn chưa có người phụ thuộc nào được đăng ký trong hệ thống.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Form đăng ký người phụ thuộc mới -->
                <div class="col-lg-5">
                    <div class="card-custom p-4">
                        <h5 class="fw-bold text-dark mb-3 border-bottom pb-2">
                            <i class="fa-solid fa-user-plus text-success me-2"></i> Đăng ký người phụ thuộc mới
                        </h5>
                        <p class="text-muted small mb-3">
                            Theo quy định pháp luật về Thuế TNCN, mức giảm trừ cho mỗi người phụ thuộc là <strong>4.400.000 VNĐ/tháng</strong>.
                        </p>

                        <form action="<?= BASE_URL ?>/ess/updateProfile" method="POST">
                            <?= Session::csrfField() ?>
                            <input type="hidden" name="action_type" value="add_dependent">

                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Họ và tên người phụ thuộc (*):</label>
                                <input type="text" class="form-control" name="dep_full_name" required placeholder="VD: Nguyễn Minh Khôi">
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label small fw-semibold">Mối quan hệ (*):</label>
                                    <select class="form-select" name="dep_relationship" required>
                                        <option value="Con">Con đẻ / Con nuôi</option>
                                        <option value="Vợ/Chồng">Vợ / Chồng</option>
                                        <option value="Bố/Mẹ">Bố / Mẹ</option>
                                        <option value="Khác">Người giám hộ hợp pháp khác</option>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-semibold">Ngày sinh:</label>
                                    <input type="date" class="form-control" name="dep_birth_date">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Mã định danh / CCCD / Giấy khai sinh:</label>
                                <input type="text" class="form-control" name="dep_id_number" placeholder="Số CCCD hoặc số định danh cá nhân">
                            </div>

                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" name="is_tax_dependent" value="1" id="checkTax" checked>
                                <label class="form-check-label small" for="checkTax">
                                    Đăng ký tính giảm trừ gia cảnh thuế TNCN
                                </label>
                            </div>

                            <button type="submit" class="btn btn-success w-100 fw-semibold rounded-pill">
                                <i class="fa-solid fa-paper-plane me-1"></i> Gửi hồ sơ đăng ký
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- ══════════════════════════════════════════════════════
             TAB 4: THÔNG TIN BẢO HIỂM XÃ HỘI
             ══════════════════════════════════════════════════════ -->
        <div class="tab-pane fade" id="tab-ins" role="tabpanel">
            <div class="card-custom p-4">
                <h5 class="fw-bold text-dark mb-4 border-bottom pb-2">
                    <i class="fa-solid fa-notes-medical text-primary me-2"></i> Thông tin Bảo hiểm Xã hội & Y tế (Posung Social Insurance)
                </h5>

                <?php if (!empty($insurance)): ?>
                    <div class="row g-4 small">
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-3">
                                <label class="text-muted d-block mb-1">Mã số sổ BHXH:</label>
                                <span class="fw-bold text-dark fs-5 font-monospace">
                                    <?= htmlspecialchars($insurance['social_insurance_number'] ?? ($employee['social_insurance_no'] ?? 'Chưa cập nhật')) ?>
                                </span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-3">
                                <label class="text-muted d-block mb-1">Mã thẻ BHYT:</label>
                                <span class="fw-bold text-success fs-5 font-monospace">
                                    <?= htmlspecialchars($insurance['health_insurance_number'] ?? ($employee['health_insurance_no'] ?? 'Chưa cập nhật')) ?>
                                </span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-3">
                                <label class="text-muted d-block mb-1">Trạng thái đóng bảo hiểm:</label>
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill fs-6">
                                    <?= htmlspecialchars($insurance['status'] ?? 'Đang tham gia') ?>
                                </span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="text-muted d-block">Nơi đăng ký Khám chữa bệnh ban đầu (KCB):</label>
                            <span class="fw-bold text-dark fs-6">
                                <?= htmlspecialchars($insurance['medical_provider'] ?? 'Bệnh viện Đa khoa KV / Trung tâm Y tế') ?>
                            </span>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted d-block">Mức lương đóng BHXH hàng tháng:</label>
                            <span class="fw-bold text-primary fs-6">
                                <?= !empty($insurance['insurance_salary']) ? number_format($insurance['insurance_salary'], 0, ',', '.') . ' VNĐ' : 'Theo mức lương cơ sở quy định' ?>
                            </span>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted d-block">Ngày bắt đầu tham gia BH tại công ty:</label>
                            <span class="fw-semibold text-dark">
                                <?= !empty($insurance['start_date']) ? date('d/m/Y', strtotime($insurance['start_date'])) : (!empty($employee['official_date']) ? date('d/m/Y', strtotime($employee['official_date'])) : '—') ?>
                            </span>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="row g-4 small">
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-3">
                                <label class="text-muted d-block mb-1">Số sổ BHXH:</label>
                                <span class="fw-bold text-dark fs-5 font-monospace">
                                    <?= htmlspecialchars($employee['social_insurance_no'] ?? 'Chưa cập nhật') ?>
                                </span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-3">
                                <label class="text-muted d-block mb-1">Số thẻ BHYT:</label>
                                <span class="fw-bold text-success fs-5 font-monospace">
                                    <?= htmlspecialchars($employee['health_insurance_no'] ?? 'Chưa cập nhật') ?>
                                </span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-3">
                                <label class="text-muted d-block mb-1">Tỷ lệ trích đóng cá nhân:</label>
                                <span class="fw-bold text-primary fs-6">
                                    10.5% (BHXH 8% + BHYT 1.5% + BHTN 1%)
                                </span>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="alert alert-info mt-4 mb-0 small" role="alert">
                    <i class="fa-solid fa-circle-info me-2"></i>
                    <strong>Lưu ý:</strong> Dữ liệu quá trình đóng BHXH được công ty đồng bộ định kỳ theo Cổng thông tin Bảo hiểm Xã hội Việt Nam (VssID).
                </div>
            </div>
        </div>

    </div>

</div>

<?php require_once APP_ROOT . '/views/ess/layout/footer.php'; ?>
