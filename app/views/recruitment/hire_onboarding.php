<!-- ══════════════════════════════════════════════════════════
     POSUNG HRIS – XÁC NHẬN TUYỂN DỤNG & KHỞI TẠO ONBOARDING
     ══════════════════════════════════════════════════════════ -->

<div class="row justify-content-center">
    <div class="col-lg-8 col-md-10">
        <div class="breadcrumb-bar mb-3">
            <a href="<?= BASE_URL ?>/recruitment"><i class="fas fa-user-plus"></i> Tuyển dụng</a>
            <i class="fas fa-chevron-right"></i>
            <a href="<?= BASE_URL ?>/recruitment/candidates"><i class="fas fa-users"></i> Ứng viên</a>
            <i class="fas fa-chevron-right"></i>
            <span>Tuyển dụng & Hội nhập</span>
        </div>

        <div class="card shadow-sm border-0" style="border-radius: 14px; overflow: hidden; background: var(--bg-card);">
            <div class="card-header p-4 text-white" style="background: linear-gradient(135deg, #10b981, #047857);">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-white text-success d-flex align-items-center justify-content-center shadow" style="width: 54px; height: 54px; font-size: 24px;">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-1">Xác Nhận Tuyển Dụng & Khởi Tạo Hội Nhập</h4>
                        <p class="mb-0 text-white-50" style="font-size: 13.5px;">Chuyển đổi ứng viên thành nhân viên chính thức và kích hoạt quy trình Onboarding</p>
                    </div>
                </div>
            </div>

            <div class="card-body p-4">
                <!-- Thông tin ứng viên trúng tuyển -->
                <div class="p-3 mb-4 rounded-3 border" style="background: var(--bg-hover);">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="text-muted small">Ứng viên trúng tuyển</div>
                            <div class="fw-bold fs-5 text-primary"><?= htmlspecialchars($candidate->full_name) ?></div>
                            <small class="text-muted"><i class="fas fa-phone me-1"></i><?= htmlspecialchars($candidate->phone) ?> | <i class="fas fa-envelope me-1"></i><?= htmlspecialchars($candidate->email) ?></small>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="text-muted small">Vị trí ứng tuyển</div>
                            <div class="fw-bold"><?= htmlspecialchars($candidate->pos_title ?? 'Chưa gán') ?></div>
                            <small class="text-muted"><?= htmlspecialchars($candidate->dept_name ?? 'POSUNG') ?></small>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="text-muted small">Mức lương Offer</div>
                            <div class="fw-bold text-success">
                                <?= !empty($candidate->offer_salary) ? number_format((float)$candidate->offer_salary) . ' đ' : 'Thỏa thuận' ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form chọn Template Onboarding -->
                <form action="<?= BASE_URL ?>/recruitment/hire/<?= $candidate->id ?>" method="POST">
                    <input type="hidden" name="_csrf_token" value="<?= Session::getCsrfToken() ?>">

                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            <i class="fas fa-clipboard-check text-primary me-1"></i> Chọn Mẫu Quy Trình Hội Nhập (Onboarding Template) <span class="text-danger">*</span>
                        </label>
                        <select name="onboarding_template_id" id="templateSelect" class="form-select form-select-lg" required style="border-radius: 10px;">
                            <option value="">-- Chọn mẫu quy trình phù hợp --</option>
                            <?php foreach ($templates as $tpl): ?>
                                <option value="<?= $tpl['id'] ?>" <?= $tpl['id'] == 1 ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($tpl['name']) ?> (<?= $tpl['total_tasks'] ?> nhiệm vụ • <?= $tpl['dept_name'] ?? 'Toàn công ty' ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Hệ thống sẽ tự động nhân bản danh sách checklist nhiệm vụ (IT, HR, HSE, Admin, Tài chính) dựa trên mẫu này.</div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold"><i class="fas fa-calendar-alt text-primary me-1"></i> Ngày Bắt Đầu Đi Làm (Start Date) <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" class="form-control" value="<?= !empty($candidate->start_date) ? $candidate->start_date : date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold"><i class="fas fa-shield-alt text-primary me-1"></i> Quy định An toàn & Khối hình</label>
                            <input type="text" class="form-control bg-light" readonly value="Huấn luyện bắt buộc theo tiêu chuẩn POSUNG HSE">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold"><i class="fas fa-sticky-note text-primary me-1"></i> Ghi chú lưu ý khi tiếp nhận</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Nhập lưu ý về máy tính, phòng ban, phân công người hướng dẫn (Mentor)..."></textarea>
                    </div>

                    <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                        <a href="<?= BASE_URL ?>/recruitment/candidates" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Quay lại
                        </a>
                        <button type="submit" class="btn btn-success btn-lg px-4 shadow-sm" style="border-radius: 10px; font-weight: 700;">
                            <i class="fas fa-check-circle me-1"></i> Xác Nhận Tuyển Dụng & Bắt Đầu Hội Nhập
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
