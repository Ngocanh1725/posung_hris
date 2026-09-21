<div class="panel" style="box-shadow: 0 10px 30px rgba(0,0,0,0.03); border: none;">
    <div class="panel-body p-0">
        <div class="tabs-wrapper px-4 pt-3" style="border-bottom: 1px solid var(--border); background: var(--bg-card); overflow-x: auto;">
            <ul class="nav-tabs-modern" id="profileTabs">
                <li class="tab-link active" data-target="tab-1">
                    <i class="fas fa-id-card"></i> <span>1. TT Cá nhân & Định danh</span>
                </li>
                <li class="tab-link" data-target="tab-2">
                    <i class="fas fa-certificate"></i> <span>2. Chứng chỉ & Pháp lý</span>
                </li>
                <li class="tab-link" data-target="tab-3">
                    <i class="fas fa-briefcase"></i> <span>3. QT Công tác & Dự án</span>
                </li>
                <li class="tab-link" data-target="tab-4">
                    <i class="fas fa-chart-line"></i> <span>4. Lương & Đãi ngộ</span>
                </li>
                <li class="tab-link" data-target="tab-5">
                    <i class="fas fa-award"></i> <span>5. KT-KL & Blacklist</span>
                </li>
                <li class="tab-link" data-target="tab-6">
                    <i class="fas fa-hard-hat"></i> <span>6. Cấp phát PPE</span>
                </li>
            </ul>
        </div>
        
        <div class="p-4">
            <!-- TAB 1: THÔNG TIN CÁ NHÂN, ĐỊNH DANH & QUAN HỆ GIA ĐÌNH -->
            <div class="tab-content active" id="tab-1">
                <div class="row">
                    <div class="col-md-6">
                        <h4 class="section-title">Lý lịch trích ngang</h4>
                        <table class="table-info">
                            <tr><td>Ngày sinh:</td><td><?= fmtDate($employee->dob) ?></td></tr>
                            <tr><td>Giới tính:</td><td><?= $employee->gender === 'Male' ? 'Nam' : ($employee->gender === 'Female' ? 'Nữ' : 'Khác') ?></td></tr>
                            <tr><td>Hôn nhân:</td><td><?= ['Single'=>'Độc thân','Married'=>'Đã kết hôn','Divorced'=>'Ly hôn','Widowed'=>'Góa'][$employee->marital_status ?? 'Single'] ?? 'Độc thân' ?></td></tr>
                            <tr><td>Dân tộc / Tôn giáo:</td><td><?= h($employee->ethnic ?? '---') ?> / <?= h($employee->religion ?? '---') ?></td></tr>
                            <tr><td>Quốc tịch:</td><td><?= h($employee->nationality) ?></td></tr>
                            <tr><td>Số CCCD/HC:</td><td><?= h($employee->id_card) ?> (Cấp: <?= fmtDate($employee->id_card_date) ?> tại <?= h($employee->id_card_place) ?>)</td></tr>
                            <tr><td>Quê quán:</td><td><?= h($employee->hometown ?: '---') ?></td></tr>
                            <tr><td>Thường trú:</td><td><?= h($employee->address ?: '---') ?></td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h4 class="section-title">Liên hệ Khẩn cấp & Ngân hàng</h4>
                        <table class="table-info">
                            <tr><td>Mã số thuế:</td><td class="fw-bold"><?= h($employee->tax_code ?? '---') ?></td></tr>
                            <tr><td>Số sổ BHXH:</td><td class="fw-bold text-primary"><?= h($employee->social_insurance_no ?? '---') ?></td></tr>
                            <tr><td>Tài khoản NH:</td><td class="fw-bold"><?= h($employee->bank_account ?? '---') ?></td></tr>
                            <tr><td>Ngân hàng:</td><td><?= h($employee->bank_name ?? '---') ?></td></tr>
                            <tr><td>Người liên hệ:</td><td><?= h($employee->emergency_contact_name ?? '---') ?> (<?= h($employee->emergency_contact_relation ?? '---') ?>)</td></tr>
                            <tr><td>SĐT Khẩn cấp:</td><td><?= h($employee->emergency_contact_phone ?? '---') ?></td></tr>
                        </table>
                        
                        <h4 class="section-title mt-4">Quan hệ gia đình</h4>
                        <?php if (empty($employee->dependents)): ?>
                            <p class="text-muted small">Chưa có thông tin người phụ thuộc.</p>
                        <?php else: ?>
                            <table class="table-info">
                                <thead><tr><th>Họ tên</th><th>Quan hệ</th><th>Ngày sinh</th></tr></thead>
                                <tbody>
                                    <?php foreach($employee->dependents as $dep): ?>
                                    <tr>
                                        <td><?= h($dep->full_name) ?></td>
                                        <td><?= h($dep->relationship) ?></td>
                                        <td><?= fmtDate($dep->dob) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- TAB 2: CHỨNG CHỈ HÀNH NGHÊ & GIẤY TỜ PHÁP LÝ -->
            <div class="tab-content" id="tab-2">
                <div class="row">
                    <!-- Hồ sơ Expat -->
                    <div class="col-md-6">
                        <h4 class="section-title">Hồ sơ Chuyên gia (Expat)</h4>
                        <?php if ($employee->employee_type === 'Expat' && isset($employee->expat)): ?>
                        <table class="table-info">
                            <tr><td>Số Hộ chiếu:</td><td class="fw-bold"><?= h($employee->expat->passport_number ?: '---') ?></td></tr>
                            <tr>
                                <td>Hạn Hộ chiếu:</td>
                                <td>
                                    <?php $dlp = daysLeft($employee->expat->passport_expiry ?? null); ?>
                                    <?= fmtDate($employee->expat->passport_expiry ?? null) ?>
                                    <?php if ($dlp !== null && $dlp <= 90): ?>
                                        <span class="badge-expiry-<?= $dlp <= 30 ? 'danger' : 'warning' ?>">
                                            <?= $dlp <= 0 ? 'HẾT HẠN' : "Còn {$dlp} ngày" ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr><td>Số Work Permit:</td><td class="fw-bold"><?= h($employee->expat->work_permit_number ?: '---') ?></td></tr>
                            <tr>
                                <td>Hạn Work Permit:</td>
                                <td>
                                    <?php $dlw = daysLeft($employee->expat->work_permit_expiry ?? null); ?>
                                    <?= fmtDate($employee->expat->work_permit_expiry ?? null) ?>
                                    <?php if ($dlw !== null && $dlw <= 90): ?>
                                        <span class="badge-expiry-<?= $dlw <= 30 ? 'danger' : 'warning' ?>">
                                            <?= $dlw <= 0 ? 'HẾT HẠN' : "Còn {$dlw} ngày" ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr><td>Số thẻ TRC:</td><td class="fw-bold"><?= h($employee->expat->trc_number ?: '---') ?></td></tr>
                            <tr>
                                <td>Hạn thẻ TRC:</td>
                                <td>
                                    <?php $dlt = daysLeft($employee->expat->trc_expiry ?? null); ?>
                                    <?= fmtDate($employee->expat->trc_expiry ?? null) ?>
                                    <?php if ($dlt !== null && $dlt <= 90): ?>
                                        <span class="badge-expiry-<?= $dlt <= 30 ? 'danger' : 'warning' ?>">
                                            <?= $dlt <= 0 ? 'HẾT HẠN' : "Còn {$dlt} ngày" ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </table>
                        <?php else: ?>
                            <p class="text-muted small">Không áp dụng (Chỉ dành cho Expat).</p>
                        <?php endif; ?>
                    </div>

                    <!-- Chứng chỉ Hành nghề -->
                    <div class="col-md-6">
                        <h4 class="section-title">Chứng chỉ Hành nghề & Kỹ thuật</h4>
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <span class="badge <?= ($employee->welding_cert_3g ?? 0) ? 'bg-success' : 'bg-light text-muted' ?> py-2 px-3">Hàn 3G <?= ($employee->welding_cert_3g ?? 0) ? '✓' : '✗' ?></span>
                            <span class="badge <?= ($employee->welding_cert_6g ?? 0) ? 'bg-success' : 'bg-light text-muted' ?> py-2 px-3">Hàn 6G <?= ($employee->welding_cert_6g ?? 0) ? '✓' : '✗' ?></span>
                        </div>
                        <?php if (empty($employee->certificates)): ?>
                            <div class="alert alert-info py-2">Chưa có chứng chỉ hành nghề nào.</div>
                        <?php else: ?>
                            <div class="table-wrapper">
                                <table class="table-info">
                                    <thead><tr><th>Loại</th><th>Tên chứng chỉ</th><th>Ngày hết hạn</th></tr></thead>
                                    <tbody>
                                        <?php foreach ($employee->certificates as $cert): ?>
                                        <tr>
                                            <td><span class="badge bg-secondary"><?= h($cert->cert_type) ?></span></td>
                                            <td class="fw-bold"><?= h($cert->cert_name) ?></td>
                                            <td>
                                                <?php if ($cert->expiry_date):
                                                    $cdl = daysLeft($cert->expiry_date);
                                                ?>
                                                    <span class="<?= $cdl !== null && $cdl < 30 ? 'text-danger fw-bold' : '' ?>"><?= fmtDate($cert->expiry_date) ?></span>
                                                    <?php if ($cdl !== null && $cdl <= 30): ?>
                                                        <span class="badge-expiry-<?= $cdl <= 0 ? 'danger' : 'warning' ?>"><?= $cdl <= 0 ? 'HẾT HẠN' : "Còn {$cdl} ngày" ?></span>
                                                    <?php endif; ?>
                                                <?php else: echo '---'; endif; ?>
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

            <!-- TAB 3: QUÁ TRÌNH CÔNG TÁC & LỊCH SỬ DỰ ÁN -->
            <div class="tab-content" id="tab-3">
                <div class="row">
                    <div class="col-md-12">
                        <h4 class="section-title">Lịch sử điều động & Dự án (Movements)</h4>
                        <?php if (empty($employee->movements)): ?>
                            <p class="text-muted small">Chưa có lịch sử điều động.</p>
                        <?php else: ?>
                            <table class="table-info">
                                <thead>
                                    <tr>
                                        <th>Ngày H.Lực</th>
                                        <th>Loại</th>
                                        <th>Từ (PB/Dự án)</th>
                                        <th>Đến (PB/Dự án)</th>
                                        <th>Trạng thái</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($employee->movements as $mov): ?>
                                    <tr>
                                        <td class="fw-bold"><?= fmtDate($mov->effective_date) ?></td>
                                        <td><?= h($mov->movement_type) ?></td>
                                        <td><?= h($mov->from_dept ?: $mov->from_project) ?></td>
                                        <td><span class="text-primary fw-bold"><?= h($mov->to_dept ?: $mov->to_project) ?></span></td>
                                        <td><span class="badge bg-<?= $mov->status==='Approved'?'success':($mov->status==='Pending'?'warning':'danger') ?>"><?= h($mov->status) ?></span></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>

                        <h4 class="section-title mt-4">Kinh nghiệm làm việc trước đây</h4>
                        <?php if (empty($employee->work_experiences)): ?>
                            <p class="text-muted small">Chưa có kinh nghiệm.</p>
                        <?php else: ?>
                            <table class="table-info">
                                <thead><tr><th>Thời gian</th><th>Công ty</th><th>Vị trí</th></tr></thead>
                                <tbody>
                                    <?php foreach($employee->work_experiences as $we): ?>
                                    <tr>
                                        <td><?= fmtDate($we->start_date, 'm/Y') ?> - <?= fmtDate($we->end_date, 'm/Y') ?></td>
                                        <td class="fw-bold"><?= h($we->company_name) ?></td>
                                        <td><?= h($we->position) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- TAB 4: LỊCH SỬ TIỀN LƯƠNG & ĐÃI NGỘ -->
            <div class="tab-content" id="tab-4">
                <div class="row">
                    <div class="col-md-6">
                        <h4 class="section-title">Lương & Phụ cấp hiện hưởng</h4>
                        <table class="table-info">
                            <tr><td>Ngày hưởng lương:</td><td><?= fmtDate($employee->ngay_huong_luong ?? null) ?></td></tr>
                            <tr><td>Hệ số lương:</td><td class="fw-bold text-primary"><?= h($employee->pctn_vuot_khung ?? '---') ?></td></tr>
                            <tr><td>PC Công trường/Xa nhà:</td><td><?= number_format($employee->phu_cap_khu_vuc ?? 0, 2) ?></td></tr>
                            <tr><td>PC Độc hại Cleanroom:</td><td><?= number_format($employee->phu_cap_khac ?? 0, 2) ?></td></tr>
                            <tr><td>PC Trách nhiệm:</td><td><?= number_format($employee->phu_cap_trach_nhiem ?? 0, 2) ?></td></tr>
                            <tr><td>PC Kiêm nhiệm:</td><td><?= number_format($employee->phu_cap_kiem_nhiem ?? 0, 2) ?></td></tr>
                        </table>

                        <h4 class="section-title mt-4">Phụ cấp đặc biệt (Allowances)</h4>
                        <?php if (!empty($employee->allowances)): ?>
                        <div class="d-flex flex-wrap gap-2 mt-2">
                            <?php foreach ($employee->allowances as $allw): ?>
                                <div class="badge badge-primary py-2 px-3 d-flex align-items-center gap-2" style="font-size:13px; background: rgba(59, 130, 246, 0.1); color: var(--primary); border: 1px solid var(--primary);">
                                    <span><?= h($allw->allowance_name) ?>: <strong><?= number_format($allw->amount) ?> VNĐ</strong></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                            <p class="text-muted mt-2 small">Chưa có phụ cấp đặc biệt nào.</p>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <h4 class="section-title">Lịch sử tăng lương (Salaries)</h4>
                        <?php if (empty($employee->salaries)): ?>
                            <p class="text-muted small">Chưa có lịch sử tăng lương.</p>
                        <?php else: ?>
                            <table class="table-info">
                                <thead><tr><th>Ngày H.Lực</th><th>Mức lương</th><th>Ghi chú</th></tr></thead>
                                <tbody>
                                    <?php foreach($employee->salaries as $sal): ?>
                                    <tr>
                                        <td><?= fmtDate($sal->effective_date) ?></td>
                                        <td class="fw-bold text-success"><?= number_format($sal->base_salary) ?></td>
                                        <td><?= h($sal->notes) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- TAB 5: KHEN THƯỞNG, KỶ LUẬT & HSE BLACKLIST -->
            <div class="tab-content" id="tab-5">
                <div class="row">
                    <div class="col-md-12">
                        <h4 class="section-title">Danh sách Khen thưởng & Kỷ luật</h4>
                        <?php if (empty($employee->rewards)): ?>
                            <p class="text-muted small">Chưa có quyết định nào.</p>
                        <?php else: ?>
                            <table class="table-info">
                                <thead>
                                    <tr>
                                        <th>Ngày QĐ</th>
                                        <th>Số QĐ</th>
                                        <th>Loại</th>
                                        <th>Nội dung</th>
                                        <th>Số tiền</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($employee->rewards as $rew): ?>
                                    <tr>
                                        <td><?= fmtDate($rew->decision_date) ?></td>
                                        <td><?= h($rew->decision_number) ?></td>
                                        <td><span class="badge bg-<?= $rew->type==='Reward'?'success':'danger' ?>"><?= h($rew->type) ?></span></td>
                                        <td><?= h($rew->reason) ?></td>
                                        <td class="fw-bold"><?= number_format($rew->amount) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- TAB 6: QUẢN LÝ CẤP PHÁT PPE & TÀI SẢN -->
            <div class="tab-content" id="tab-6">
                <div class="row">
                    <div class="col-md-12">
                        <h4 class="section-title">Danh sách cấp phát PPE & BHLĐ</h4>
                        <?php if (empty($employee->ppes)): ?>
                            <p class="text-muted small">Chưa có đợt cấp phát nào.</p>
                        <?php else: ?>
                            <table class="table-info">
                                <thead>
                                    <tr>
                                        <th>Tên vật tư (Giày, Mũ...)</th>
                                        <th>Ngày cấp</th>
                                        <th>Trạng thái</th>
                                        <th>Ngày thu hồi</th>
                                        <th>Ghi chú</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($employee->ppes as $ppe): ?>
                                    <tr>
                                        <td class="fw-bold"><?= h($ppe->item_name) ?></td>
                                        <td><?= fmtDate($ppe->issue_date) ?></td>
                                        <td>
                                            <?php 
                                            $s = $ppe->status;
                                            $col = $s==='Issued'?'primary':($s==='Returned'?'success':($s==='Lost'?'danger':'warning'));
                                            ?>
                                            <span class="badge bg-<?= $col ?>"><?= h($s) ?></span>
                                        </td>
                                        <td><?= fmtDate($ppe->return_date) ?></td>
                                        <td><?= h($ppe->notes) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div><!-- /p-4 -->
    </div>
</div>
