<?php
/**
 * View: insurance/claims.php – Quản lý Hồ sơ Chế độ Bảo hiểm Đã hưởng (Mẫu C70a-HD)
 */

$claimTypeBadges = [
    'OmDau'        => ['Ốm đau', 'badge bg-warning text-dark', 'fas fa-stethoscope'],
    'ThaiSan'      => ['Thai sản', 'badge bg-info text-dark', 'fas fa-baby'],
    'TaiNanLD_BNN' => ['Tai nạn LĐ - BNN', 'badge bg-danger text-white', 'fas fa-user-injured'],
    'DuongSuc'     => ['Dưỡng sức PHST', 'badge bg-success text-white', 'fas fa-heartbeat'],
];

$totalClaimsCount = count($claims);
$totalAmountSum = 0;
$pendingCount = 0;

foreach ($claims as $c) {
    if (in_array($c['status'], ['Approved', 'Paid'])) {
        $totalAmountSum += (float)$c['claim_amount'];
    }
    if ($c['status'] === 'Pending') {
        $pendingCount++;
    }
}
?>

<!-- BREADCRUMB -->
<div class="breadcrumb-nav mb-3" style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: var(--text-muted);">
    <a href="<?= BASE_URL ?>/insurance" style="color: var(--primary); text-decoration: none;">
        <i class="fas fa-shield-alt"></i> Quản lý Bảo hiểm Xã hội
    </a>
    <i class="fas fa-chevron-right" style="font-size: 10px;"></i>
    <span>Quản lý Chế độ BHXH Đã hưởng (Mẫu C70a-HD)</span>
</div>

<!-- STATS CARDS -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card p-3" style="border: 1px solid var(--border); border-radius: 12px;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted" style="font-size: 12px; font-weight: 600; text-transform: uppercase;">Tổng lượt hồ sơ chế độ</div>
                    <div style="font-size: 24px; font-weight: 800; color: var(--text); margin-top: 4px;"><?= $totalClaimsCount ?> <span style="font-size: 13px; font-weight: normal; color: var(--text-muted);">hồ sơ</span></div>
                </div>
                <div style="width: 44px; height: 44px; border-radius: 10px; background: rgba(79,70,229,0.1); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 20px;">
                    <i class="fas fa-file-medical"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card p-3" style="border: 1px solid rgba(245,158,11,0.3); background: rgba(245,158,11,0.03); border-radius: 12px;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted" style="font-size: 12px; font-weight: 600; text-transform: uppercase;">Đang chờ Cơ quan BH duyệt</div>
                    <div style="font-size: 24px; font-weight: 800; color: #d97706; margin-top: 4px;"><?= $pendingCount ?> <span style="font-size: 13px; font-weight: normal; color: var(--text-muted);">hồ sơ</span></div>
                </div>
                <div style="width: 44px; height: 44px; border-radius: 10px; background: rgba(245,158,11,0.1); color: #d97706; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card p-3" style="border: 1px solid rgba(16,185,129,0.3); background: rgba(16,185,129,0.03); border-radius: 12px;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted" style="font-size: 12px; font-weight: 600; text-transform: uppercase;">Tổng tiền trợ cấp đã chi trả</div>
                    <div style="font-size: 24px; font-weight: 800; color: #10b981; margin-top: 4px;"><?= number_format($totalAmountSum, 0, ',', '.') ?> <span style="font-size: 13px; font-weight: normal; color: var(--text-muted);">đ</span></div>
                </div>
                <div style="width: 44px; height: 44px; border-radius: 10px; background: rgba(16,185,129,0.1); color: #10b981; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                    <i class="fas fa-hand-holding-usd"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ACTIONS TOOLBAR -->
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div>
        <h3 style="font-size: 18px; font-weight: 700; margin: 0; color: var(--text);">
            <i class="fas fa-medkit text-danger"></i> Hồ Sơ Hưởng Chế Độ Bảo Hiểm (Mẫu C70a-HD)
        </h3>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newClaimModal" style="box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);">
            <i class="fas fa-plus-circle"></i> Nộp Hồ Sơ Chế Độ Mới
        </button>
        <a href="<?= BASE_URL ?>/insurance/report?type=C70aHD&month=<?= date('Y-m') ?>" class="btn btn-outline-secondary">
            <i class="fas fa-file-invoice"></i> Mẫu C70a-HD Chuẩn
        </a>
    </div>
</div>

<!-- FILTER CARD -->
<div class="card mb-3" style="border: 1px solid var(--border);">
    <div class="card-body p-3">
        <form method="GET" action="<?= BASE_URL ?>/insurance/claims" class="row g-2 align-items-center">
            <!-- Loại chế độ -->
            <div class="col-md-3">
                <select name="type" class="form-select" onchange="this.form.submit()">
                    <option value="">-- Tất cả chế độ --</option>
                    <option value="OmDau" <?= ($filters['type'] === 'OmDau') ? 'selected' : '' ?>>Ốm đau</option>
                    <option value="ThaiSan" <?= ($filters['type'] === 'ThaiSan') ? 'selected' : '' ?>>Thai sản</option>
                    <option value="TaiNanLD_BNN" <?= ($filters['type'] === 'TaiNanLD_BNN') ? 'selected' : '' ?>>Tai nạn LĐ - BNN</option>
                    <option value="DuongSuc" <?= ($filters['type'] === 'DuongSuc') ? 'selected' : '' ?>>Dưỡng sức PHST</option>
                </select>
            </div>

            <!-- Năm -->
            <div class="col-md-2">
                <select name="year" class="form-select" onchange="this.form.submit()">
                    <?php for ($y = date('Y') - 1; $y <= date('Y') + 1; $y++): ?>
                        <option value="<?= $y ?>" <?= ($filters['year'] == $y) ? 'selected' : '' ?>>Năm <?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </div>

            <!-- Trạng thái -->
            <div class="col-md-3">
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="">-- Trạng thái giải quyết --</option>
                    <option value="Pending" <?= ($filters['status'] === 'Pending') ? 'selected' : '' ?>>Chờ cơ quan BH duyệt</option>
                    <option value="Approved" <?= ($filters['status'] === 'Approved') ? 'selected' : '' ?>>Cơ quan BH đã duyệt</option>
                    <option value="Paid" <?= ($filters['status'] === 'Paid') ? 'selected' : '' ?>>Đã chi trả cho NLĐ</option>
                    <option value="Rejected" <?= ($filters['status'] === 'Rejected') ? 'selected' : '' ?>>Từ chối thanh toán</option>
                </select>
            </div>

            <!-- Search -->
            <div class="col-md-3">
                <input type="text" name="search" class="form-control" placeholder="Tìm theo tên, mã NV, chứng từ..." value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
            </div>

            <div class="col-md-1">
                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i></button>
            </div>
        </form>
    </div>
</div>

<!-- TABLE CLAIMS -->
<div class="card" style="border: 1px solid var(--border); box-shadow: 0 2px 10px rgba(0,0,0,0.02);">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
            <thead style="background: var(--bg-hover); color: var(--text-muted); font-size: 11.5px; text-transform: uppercase;">
                <tr>
                    <th style="width: 50px; text-align: center;">STT</th>
                    <th style="width: 230px;">Nhân sự</th>
                    <th style="width: 150px;">Chế độ hưởng</th>
                    <th style="width: 190px;">Thời gian nghỉ</th>
                    <th style="text-align: center; width: 80px;">Số ngày</th>
                    <th style="text-align: right; width: 140px;">Tiền trợ cấp</th>
                    <th>Tài khoản nhận / Chứng từ</th>
                    <th style="text-align: center; width: 130px;">Trạng thái</th>
                    <th style="text-align: right; width: 120px;">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($claims)): ?>
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <i class="fas fa-notes-medical fa-3x mb-3 text-secondary" style="opacity: 0.5;"></i>
                            <div>Không tìm thấy hồ sơ chế độ bảo hiểm nào phù hợp.</div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php $idx = 1; foreach ($claims as $c): ?>
                        <tr>
                            <td style="text-align: center; color: var(--text-muted);"><?= $idx++ ?></td>
                            <td>
                                <strong style="color: var(--text);"><?= htmlspecialchars($c['full_name']) ?></strong>
                                <div style="font-size: 11.5px; color: var(--text-muted);">
                                    Mã: <span class="badge bg-light text-dark" style="border: 1px solid var(--border);"><?= htmlspecialchars($c['emp_code']) ?></span>
                                    • Sổ: <?= htmlspecialchars($c['social_insurance_no'] ?? 'Chưa cấp') ?>
                                </div>
                            </td>
                            <td>
                                <?php $b = $claimTypeBadges[$c['claim_type']] ?? [$c['claim_type'], 'badge bg-secondary', '']; ?>
                                <span class="<?= $b[1] ?>" style="font-size: 11px; padding: 4px 8px; border-radius: 12px;">
                                    <i class="<?= $b[2] ?>"></i> <?= $b[0] ?>
                                </span>
                            </td>
                            <td>
                                <?= date('d/m/Y', strtotime($c['from_date'])) ?> – <?= date('d/m/Y', strtotime($c['to_date'])) ?>
                            </td>
                            <td style="text-align: center; font-weight: 700; color: #4f46e5;">
                                <?= $c['leave_days'] ?> ngày
                            </td>
                            <td style="text-align: right; font-weight: 800; color: #10b981; font-size: 13.5px;">
                                <?= number_format((float)$c['claim_amount'], 0, ',', '.') ?>đ
                            </td>
                            <td>
                                <?php if (!empty($c['bank_account'])): ?>
                                    <div><i class="fas fa-credit-card text-muted"></i> <strong><?= htmlspecialchars($c['bank_account']) ?></strong> (<?= htmlspecialchars($c['bank_name'] ?? '') ?>)</div>
                                <?php endif; ?>
                                <?php if (!empty($c['document_ref'])): ?>
                                    <div style="font-size: 11.5px; color: var(--text-muted);"><i class="fas fa-paperclip"></i> <?= htmlspecialchars($c['document_ref']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center;">
                                <?php if ($c['status'] === 'Paid'): ?>
                                    <span class="badge bg-success text-white" style="font-size: 11px;"><i class="fas fa-check-double"></i> Đã chi trả</span>
                                <?php elseif ($c['status'] === 'Approved'): ?>
                                    <span class="badge bg-primary text-white" style="font-size: 11px;"><i class="fas fa-check"></i> BHXH duyệt</span>
                                <?php elseif ($c['status'] === 'Rejected'): ?>
                                    <span class="badge bg-danger text-white" style="font-size: 11px;"><i class="fas fa-times"></i> Từ chối</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark" style="font-size: 11px;"><i class="fas fa-clock"></i> Chờ duyệt</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: right;">
                                <?php if ($c['status'] === 'Pending'): ?>
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="changeClaimStatus(<?= $c['id'] ?>, 'Approved', '<?= $c['claim_amount'] ?>')">
                                        <i class="fas fa-check"></i> Duyệt
                                    </button>
                                <?php elseif ($c['status'] === 'Approved'): ?>
                                    <button type="button" class="btn btn-sm btn-outline-success" onclick="changeClaimStatus(<?= $c['id'] ?>, 'Paid')">
                                        <i class="fas fa-money-check-alt"></i> Chi trả
                                    </button>
                                <?php else: ?>
                                    <span class="text-muted" style="font-size: 12px;"><i class="fas fa-lock"></i> Hoàn tất</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL THÊM HỒ SƠ CHẾ ĐỘ MỚI (C70a-HD) -->
<div class="modal fade" id="newClaimModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius: 14px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            <div class="modal-header" style="background: linear-gradient(135deg, rgba(79,70,229,0.08), rgba(99,102,241,0.02)); border-bottom: 1px solid var(--border);">
                <h5 class="modal-title" style="font-weight: 700; color: var(--text);">
                    <i class="fas fa-plus-circle text-primary"></i> Lập Hồ Sơ Đề Nghị Hưởng Chế Độ BHXH (C70a-HD)
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= BASE_URL ?>/insurance/saveClaim" method="POST">
                <input type="hidden" name="_csrf_token" value="<?= Session::generateCsrfToken() ?>">

                <div class="modal-body" style="padding: 24px;">
                    <div class="row g-3">
                        <!-- Chọn nhân viên -->
                        <div class="col-md-6">
                            <label class="form-label" style="font-weight: 600; font-size: 13px;">
                                Chọn Nhân viên <span class="text-danger">*</span>
                            </label>
                            <select name="employee_id" class="form-select" required>
                                <option value="">-- Chọn nhân sự --</option>
                                <?php foreach ($employees as $emp): ?>
                                    <option value="<?= $emp->id ?>">
                                        [<?= htmlspecialchars($emp->emp_code) ?>] <?= htmlspecialchars($emp->full_name) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Loại chế độ -->
                        <div class="col-md-6">
                            <label class="form-label" style="font-weight: 600; font-size: 13px;">
                                Loại Chế độ Hưởng <span class="text-danger">*</span>
                            </label>
                            <select name="claim_type" class="form-select" required>
                                <option value="OmDau">🟡 Chế độ Ốm đau (Bản thân / Con ốm)</option>
                                <option value="ThaiSan">🟣 Chế độ Thai sản (Khám thai, sinh con, dưỡng thai...)</option>
                                <option value="TaiNanLD_BNN">🔴 Tai nạn Lao động & Bệnh nghề nghiệp</option>
                                <option value="DuongSuc">🟢 Dưỡng sức, phục hồi sức khỏe sau ốm/thai sản/TNLĐ</option>
                            </select>
                        </div>

                        <!-- Từ ngày, đến ngày -->
                        <div class="col-md-4">
                            <label class="form-label" style="font-weight: 600; font-size: 13px;">
                                Từ ngày nghỉ <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="from_date" id="claimFromDate" class="form-control" required value="<?= date('Y-m-d') ?>" onchange="calcLeaveDays()">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label" style="font-weight: 600; font-size: 13px;">
                                Đến ngày nghỉ <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="to_date" id="claimToDate" class="form-control" required value="<?= date('Y-m-d') ?>" onchange="calcLeaveDays()">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label" style="font-weight: 600; font-size: 13px;">
                                Số ngày nghỉ thực tế <span class="text-danger">*</span>
                            </label>
                            <input type="number" name="leave_days" id="claimLeaveDays" class="form-control" min="1" value="1" required>
                        </div>

                        <!-- Số tiền trợ cấp -->
                        <div class="col-md-6">
                            <label class="form-label" style="font-weight: 600; font-size: 13px;">
                                Số tiền trợ cấp được duyệt (VNĐ) <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">₫</span>
                                <input type="text" name="claim_amount" class="form-control" placeholder="0" oninput="formatNumberInput(this)" required>
                            </div>
                        </div>

                        <!-- Trạng thái -->
                        <div class="col-md-6">
                            <label class="form-label" style="font-weight: 600; font-size: 13px;">
                                Trạng thái xử lý <span class="text-danger">*</span>
                            </label>
                            <select name="status" class="form-select" required>
                                <option value="Pending" selected>🕒 Chờ Cơ quan BHXH duyệt</option>
                                <option value="Approved">✅ Cơ quan BHXH đã duyệt chi</option>
                                <option value="Paid">💰 Đã thanh toán / chi trả cho NLĐ</option>
                            </select>
                        </div>

                        <!-- Tài khoản & ngân hàng -->
                        <div class="col-md-6">
                            <label class="form-label" style="font-weight: 600; font-size: 13px;">Số tài khoản nhận trợ cấp</label>
                            <input type="text" name="bank_account" class="form-control" placeholder="Số tài khoản cá nhân của NLĐ...">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" style="font-weight: 600; font-size: 13px;">Ngân hàng thụ hưởng</label>
                            <input type="text" name="bank_name" class="form-control" placeholder="VD: Vietcombank, Techcombank...">
                        </div>

                        <!-- Giấy tờ chứng từ đính kèm -->
                        <div class="col-12">
                            <label class="form-label" style="font-weight: 600; font-size: 13px;">Số hiệu Giấy tờ / Hồ sơ minh chứng</label>
                            <input type="text" name="document_ref" class="form-control" placeholder="VD: Giấy ra viện số 4912/BV-HN, Giấy chứng sinh số 129/CS...">
                        </div>

                        <div class="col-12">
                            <label class="form-label" style="font-weight: 600; font-size: 13px;">Ghi chú / Chẩn đoán bệnh</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Ghi chú thêm về điều trị hoặc nội dung bệnh viện chỉ định..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer" style="background: var(--bg-hover); border-top: 1px solid var(--border);">
                    <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary" style="padding: 7px 20px;">
                        <i class="fas fa-save"></i> Lưu Hồ Sơ Chế Độ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function formatNumberInput(input) {
    let val = input.value.replace(/[^0-9]/g, '');
    input.value = val ? parseInt(val, 10).toLocaleString('vi-VN') : '';
}

function calcLeaveDays() {
    let from = document.getElementById('claimFromDate').value;
    let to = document.getElementById('claimToDate').value;
    if (from && to) {
        let d1 = new Date(from);
        let d2 = new Date(to);
        let diffTime = Math.abs(d2 - d1);
        let diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
        document.getElementById('claimLeaveDays').value = diffDays > 0 ? diffDays : 1;
    }
}

function changeClaimStatus(id, newStatus, currentAmt = '') {
    let promptMsg = 'Xác nhận cập nhật trạng thái chế độ sang: ' + newStatus + '?';
    let newAmt = null;

    if (newStatus === 'Approved') {
        let inputAmt = prompt('Nhập số tiền trợ cấp được duyệt (VNĐ):', currentAmt ? parseInt(currentAmt, 10).toLocaleString('vi-VN') : '');
        if (inputAmt === null) return;
        newAmt = inputAmt.replace(/[^0-9]/g, '');
    } else {
        if (!confirm(promptMsg)) return;
    }

    let bodyData = 'status=' + encodeURIComponent(newStatus) + '&_csrf_token=<?= Session::generateCsrfToken() ?>';
    if (newAmt !== null) {
        bodyData += '&claim_amount=' + encodeURIComponent(newAmt);
    }

    fetch('<?= BASE_URL ?>/insurance/updateClaimStatus/' + id, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: bodyData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            window.location.reload();
        } else {
            alert(data.message || 'Lỗi xử lý.');
        }
    })
    .catch(() => alert('Không thể kết nối đến máy chủ.'));
}
</script>
