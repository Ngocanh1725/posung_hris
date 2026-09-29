<?php
require_once APP_ROOT . '/views/ess/layout/header.php';
?>

<div class="container-xl">
    
    <!-- Header Page -->
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-2">
        <div>
            <h4 class="fw-bold mb-1 text-dark">
                <i class="fa-solid fa-graduation-cap text-primary me-2"></i> Khóa đào tạo & Phát triển (L&D)
            </h4>
            <p class="text-muted small mb-0">Theo dõi quá trình nâng cao năng lực, kỹ năng an toàn và chứng chỉ chuyên môn.</p>
        </div>
        <div>
            <a href="<?= BASE_URL ?>/ess/dashboard" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="fa-solid fa-arrow-left me-1"></i> Trang chủ
            </a>
        </div>
    </div>

    <!-- Danh sách Khóa Đào tạo -->
    <div class="card-custom">
        <div class="card-custom-header">
            <h5 class="card-custom-title">
                <i class="fa-solid fa-book-open-reader text-primary"></i> Các khóa đào tạo đã tham gia
            </h5>
            <span class="badge bg-light text-muted border"><?= count($trainings) ?> khóa học</span>
        </div>
        <div class="card-custom-body p-0">
            <?php if (!empty($trainings)): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th>Khóa học</th>
                                <th>Giảng viên / Đơn vị</th>
                                <th>Thời gian</th>
                                <th>Địa điểm</th>
                                <th class="text-center">Điểm số</th>
                                <th class="text-center">Kết quả</th>
                                <th>Số chứng chỉ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($trainings as $t): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($t['course_name']) ?></div>
                                        <small class="text-muted font-monospace"><?= htmlspecialchars($t['course_code'] ?? '') ?></small>
                                    </td>
                                    <td>
                                        <span class="fw-semibold text-dark"><?= htmlspecialchars($t['provider'] ?? 'Posung E&C Academy') ?></span>
                                    </td>
                                    <td>
                                        <?= !empty($t['start_date']) ? date('d/m/Y', strtotime($t['start_date'])) : '—' ?>
                                        &rarr;
                                        <?= !empty($t['end_date']) ? date('d/m/Y', strtotime($t['end_date'])) : '—' ?>
                                    </td>
                                    <td>
                                        <span class="text-muted"><?= htmlspecialchars($t['location'] ?? 'Văn phòng / Online') ?></span>
                                    </td>
                                    <td class="text-center font-monospace fw-bold fs-6">
                                        <?= !empty($t['score']) ? number_format($t['score'], 1) : '—' ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if (in_array(strtolower($t['result'] ?? ''), ['passed', 'đạt'])): ?>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                                <i class="fa-solid fa-check me-1"></i> Đạt yêu cầu
                                            </span>
                                        <?php elseif (in_array(strtolower($t['result'] ?? ''), ['failed', 'không đạt'])): ?>
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                                Không đạt
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1">
                                                Đang học / Chờ
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($t['certificate_no'])): ?>
                                            <span class="badge bg-light text-primary border font-monospace px-2 py-1">
                                                <i class="fa-solid fa-award me-1"></i> <?= htmlspecialchars($t['certificate_no']) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5 text-muted small">
                    <i class="fa-solid fa-graduation-cap fa-3x text-secondary opacity-50 mb-3"></i>
                    <p>Bạn chưa được phân công hoặc chưa tham gia khóa đào tạo nào.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<?php require_once APP_ROOT . '/views/ess/layout/footer.php'; ?>
