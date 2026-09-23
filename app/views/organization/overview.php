<!-- ══════════════════════════════════════════════════════════
     TỔNG QUAN CƠ CẤU TỔ CHỨC – POSUNG HRIS
     ══════════════════════════════════════════════════════════ -->

<!-- ── BANNER GIỚI THIỆU CÔNG TY ── -->
<div class="company-banner">
    <div class="banner-bg-pattern"></div>
    <div class="banner-content">
        <div class="banner-icon">
            <div class="banner-logo">PS</div>
        </div>
        <div class="banner-text">
            <h2 class="banner-title">Công ty TNHH Cơ khí Kỹ thuật Xây dựng Po Sung</h2>
            <p class="banner-subtitle">PO SUNG Mechanical Engineering & Construction Co., Ltd.</p>
            <div class="banner-tags">
                <span class="banner-tag"><i class="fas fa-cogs"></i> Cơ khí Chế tạo</span>
                <span class="banner-tag"><i class="fas fa-bolt"></i> M&E (Cơ điện)</span>
                <span class="banner-tag"><i class="fas fa-wind"></i> HVAC</span>
                <span class="banner-tag"><i class="fas fa-drafting-compass"></i> BIM/Engineering</span>
            </div>
        </div>
    </div>
</div>

<!-- ── SỨ MỆNH & TẦM NHÌN ── -->
<div class="mission-vision-grid">
    <div class="mv-card mv-mission">
        <div class="mv-icon"><i class="fas fa-bullseye"></i></div>
        <h3>Sứ mệnh</h3>
        <p>Cung cấp giải pháp thi công cơ khí – cơ điện (M&E) chất lượng cao, đáp ứng tiêu chuẩn quốc tế, đồng hành cùng các chủ đầu tư lớn trong ngành sản xuất công nghệ cao và xây dựng dân dụng tại Việt Nam.</p>
    </div>
    <div class="mv-card mv-vision">
        <div class="mv-icon"><i class="fas fa-eye"></i></div>
        <h3>Tầm nhìn</h3>
        <p>Trở thành nhà thầu M&E hàng đầu Việt Nam, được các tập đoàn đa quốc gia (Samsung, Amkor, LG, Daewoo) tin tưởng lựa chọn, với đội ngũ chuyên gia Hàn – Việt có trình độ cao và công nghệ thi công tiên tiến.</p>
    </div>
    <div class="mv-card mv-values">
        <div class="mv-icon"><i class="fas fa-gem"></i></div>
        <h3>Giá trị cốt lõi</h3>
        <ul class="mv-values-list">
            <li><i class="fas fa-check-circle"></i> <strong>Chất lượng</strong> – Tuân thủ ASME, AWS, JIS</li>
            <li><i class="fas fa-check-circle"></i> <strong>An toàn</strong> – Zero Accident là mục tiêu</li>
            <li><i class="fas fa-check-circle"></i> <strong>Đúng tiến độ</strong> – Cam kết milestone</li>
            <li><i class="fas fa-check-circle"></i> <strong>Đổi mới</strong> – BIM, Prefab, Modular</li>
        </ul>
    </div>
</div>

<!-- ── INFOGRAPHIC THỐNG KÊ ── -->
<div class="org-stats-grid">
    <div class="stat-card stat-card-primary">
        <div class="stat-icon"><i class="fas fa-sitemap"></i></div>
        <div class="stat-info">
            <span class="stat-number"><?= $summary['totalDepts'] ?></span>
            <span class="stat-label">Bộ phận / Phòng ban</span>
        </div>
    </div>
    <div class="stat-card stat-card-success">
        <div class="stat-icon"><i class="fas fa-users"></i></div>
        <div class="stat-info">
            <span class="stat-number"><?= $summary['totalEmployees'] ?></span>
            <span class="stat-label">Nhân viên đang làm việc</span>
        </div>
    </div>
    <div class="stat-card stat-card-warning">
        <div class="stat-icon"><i class="fas fa-hard-hat"></i></div>
        <div class="stat-info">
            <span class="stat-number"><?= $summary['totalProjects'] ?></span>
            <span class="stat-label">Dự án đang triển khai</span>
        </div>
    </div>
    <div class="stat-card stat-card-info">
        <div class="stat-icon"><i class="fas fa-layer-group"></i></div>
        <div class="stat-info">
            <span class="stat-number"><?php
                $typeMap = [];
                foreach ($summary['typeCounts'] as $tc) { $typeMap[$tc['type']] = $tc['cnt']; }
                echo ($typeMap['Division'] ?? 0) + ($typeMap['Department'] ?? 0);
            ?></span>
            <span class="stat-label">Phòng ban Văn phòng</span>
        </div>
    </div>
</div>

<!-- ── CƠ CẤU TỔ CHỨC 2 KHỐI ── -->
<div class="org-structure-section">
    <div class="panel">
        <div class="panel-header">
            <h3><i class="fas fa-project-diagram"></i> Cơ cấu Tổ chức Công ty</h3>
            <a href="<?= BASE_URL ?>/organization" class="btn btn-sm btn-primary">
                <i class="fas fa-sitemap"></i> Xem Sơ đồ chi tiết
            </a>
        </div>
        <div class="panel-body">
            <!-- Khối Văn phòng (Ban Giám đốc + Phòng ban) -->
            <div class="structure-block">
                <div class="structure-block-header">
                    <div class="structure-block-icon" style="background: linear-gradient(135deg, #6366f1, #8b5cf6);">
                        <i class="fas fa-building"></i>
                    </div>
                    <div>
                        <h4>Khối Văn phòng (Hanoi HQ)</h4>
                        <p class="text-muted">Ban Giám đốc và các Phòng ban chức năng</p>
                    </div>
                </div>
                
                <!-- Ban Giám đốc -->
                <?php foreach ($hqDepts as $dept): ?>
                <div class="structure-dept structure-dept-division">
                    <a href="<?= BASE_URL ?>/organization/detail/<?= $dept['id'] ?>" class="structure-dept-inner">
                        <div class="structure-dept-icon"><i class="fas fa-crown"></i></div>
                        <div class="structure-dept-info">
                            <h5><?= h($dept['name']) ?></h5>
                            <span class="dept-type-badge badge-division"><?= $dept['code'] ?></span>
                            <?php if (!empty($dept['manager_name'])): ?>
                                <small class="text-muted"><i class="fas fa-user-tie"></i> <?= h($dept['manager_name']) ?></small>
                            <?php endif; ?>
                        </div>
                        <i class="fas fa-chevron-right structure-arrow"></i>
                    </a>
                </div>
                <?php endforeach; ?>

                <!-- Các phòng ban -->
                <div class="structure-children">
                    <?php foreach ($officeDepts as $dept): ?>
                    <div class="structure-dept structure-dept-department">
                        <a href="<?= BASE_URL ?>/organization/detail/<?= $dept['id'] ?>" class="structure-dept-inner">
                            <div class="structure-dept-icon" style="background: linear-gradient(135deg, rgba(99,102,241,0.2), rgba(139,92,246,0.15)); color: var(--primary-light);">
                                <i class="fas fa-building"></i>
                            </div>
                            <div class="structure-dept-info">
                                <h5><?= h($dept['name']) ?></h5>
                                <span class="dept-type-badge badge-department"><?= $dept['code'] ?></span>
                                <?php if (!empty($dept['description'])): ?>
                                    <p class="structure-desc"><?= h(mb_substr($dept['description'], 0, 120)) ?>…</p>
                                <?php endif; ?>
                                <?php if (!empty($dept['manager_name'])): ?>
                                    <small class="text-muted"><i class="fas fa-user-tie"></i> <?= h($dept['manager_name']) ?></small>
                                <?php endif; ?>
                            </div>
                            <i class="fas fa-chevron-right structure-arrow"></i>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Khối Dự án -->
            <div class="structure-block" style="margin-top: 32px;">
                <div class="structure-block-header">
                    <div class="structure-block-icon" style="background: linear-gradient(135deg, #10b981, #059669);">
                        <i class="fas fa-hard-hat"></i>
                    </div>
                    <div>
                        <h4>Khối Dự án Công trường</h4>
                        <p class="text-muted">Các Ban Điều hành Dự án tại công trường</p>
                    </div>
                </div>
                <div class="structure-children">
                    <?php foreach ($projectDepts as $dept): ?>
                    <div class="structure-dept structure-dept-project">
                        <a href="<?= BASE_URL ?>/organization/detail/<?= $dept['id'] ?>" class="structure-dept-inner">
                            <div class="structure-dept-icon" style="background: linear-gradient(135deg, rgba(16,185,129,0.2), rgba(5,150,105,0.15)); color: #10b981;">
                                <i class="fas fa-hard-hat"></i>
                            </div>
                            <div class="structure-dept-info">
                                <h5><?= h($dept['name']) ?></h5>
                                <span class="dept-type-badge badge-project"><?= $dept['code'] ?></span>
                                <?php if (!empty($dept['description'])): ?>
                                    <p class="structure-desc"><?= h(mb_substr($dept['description'], 0, 120)) ?>…</p>
                                <?php endif; ?>
                                <?php if (!empty($dept['manager_name'])): ?>
                                    <small class="text-muted"><i class="fas fa-user-tie"></i> <?= h($dept['manager_name']) ?></small>
                                <?php endif; ?>
                            </div>
                            <i class="fas fa-chevron-right structure-arrow"></i>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ── BIỂU ĐỒ PHÂN BỔ QUÂN SỐ ── -->
<div class="panel" style="margin-top: 24px;">
    <div class="panel-header">
        <h3><i class="fas fa-chart-pie"></i> Phân bổ Quân số theo Bộ phận</h3>
    </div>
    <div class="panel-body">
        <div class="headcount-chart">
            <?php 
            $totalHC = 0;
            foreach ($summary['deptHeadcounts'] as $dh) {
                $totalHC += (int)$dh['headcount'];
            }
            foreach ($summary['deptHeadcounts'] as $dh):
                $hc = (int)$dh['headcount'];
                $pct = $totalHC > 0 ? round(($hc / $totalHC) * 100, 1) : 0;
                $typeClass = strtolower($dh['type'] ?? 'department');
            ?>
            <div class="hc-row">
                <div class="hc-label">
                    <span class="hc-name"><?= h($dh['name']) ?></span>
                    <span class="hc-count"><?= $hc ?> người</span>
                </div>
                <div class="hc-bar-wrap">
                    <div class="hc-bar hc-bar-<?= $typeClass ?>" style="width: <?= max($pct, 2) ?>%;">
                        <span class="hc-pct"><?= $pct ?>%</span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <div class="hc-total">
                <strong>Tổng cộng: <?= $totalHC ?> nhân viên</strong>
            </div>
        </div>
    </div>
</div>

<style>
/* ── Company Banner ── */
.company-banner {
    position: relative; padding: 40px 32px; border-radius: 16px; margin-bottom: 24px;
    background: linear-gradient(135deg, rgba(99,102,241,0.15) 0%, rgba(139,92,246,0.1) 40%, rgba(16,185,129,0.08) 100%);
    border: 1px solid rgba(99,102,241,0.2); overflow: hidden;
}
.banner-bg-pattern {
    position: absolute; top: 0; left: 0; right: 0; bottom: 0; opacity: 0.05;
    background-image: radial-gradient(circle at 20% 50%, var(--primary-light) 1px, transparent 1px),
                      radial-gradient(circle at 80% 20%, var(--primary-light) 1px, transparent 1px);
    background-size: 60px 60px;
}
.banner-content { display: flex; align-items: center; gap: 24px; position: relative; z-index: 1; }
.banner-logo {
    width: 72px; height: 72px; border-radius: 16px;
    background: linear-gradient(135deg, var(--primary), var(--primary-light));
    display: flex; align-items: center; justify-content: center;
    font-size: 1.6rem; font-weight: 800; color: #fff;
    box-shadow: 0 4px 16px rgba(99,102,241,0.3);
}
.banner-title { font-size: 1.5rem; font-weight: 800; margin-bottom: 4px; }
.banner-subtitle { font-size: 0.9rem; color: var(--text-muted); margin-bottom: 12px; font-style: italic; }
.banner-tags { display: flex; flex-wrap: wrap; gap: 8px; }
.banner-tag {
    padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 500;
    background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1);
    color: var(--text-secondary);
}
.banner-tag i { margin-right: 4px; color: var(--primary-light); }

/* ── Mission / Vision ── */
.mission-vision-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-bottom: 24px; }
@media (max-width: 1024px) { .mission-vision-grid { grid-template-columns: 1fr; } }

.mv-card {
    padding: 24px; border-radius: 14px;
    background: var(--bg-card); border: 1px solid rgba(255,255,255,0.06);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.mv-card:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(0,0,0,0.2); }
.mv-icon {
    width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center;
    font-size: 1.2rem; margin-bottom: 16px;
}
.mv-mission .mv-icon { background: linear-gradient(135deg, rgba(99,102,241,0.2), rgba(139,92,246,0.15)); color: var(--primary-light); }
.mv-vision .mv-icon { background: linear-gradient(135deg, rgba(16,185,129,0.2), rgba(5,150,105,0.15)); color: #10b981; }
.mv-values .mv-icon { background: linear-gradient(135deg, rgba(245,158,11,0.2), rgba(217,119,6,0.15)); color: #f59e0b; }
.mv-card h3 { font-size: 1.1rem; font-weight: 700; margin-bottom: 12px; }
.mv-card p { font-size: 0.85rem; color: var(--text-secondary); line-height: 1.6; }
.mv-values-list { list-style: none; padding: 0; margin: 0; }
.mv-values-list li { font-size: 0.85rem; color: var(--text-secondary); padding: 4px 0; display: flex; align-items: center; gap: 8px; }
.mv-values-list li i { color: #10b981; font-size: 0.75rem; }

/* ── Stats Grid ── */
.org-stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px; }
@media (max-width: 1024px) { .org-stats-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 640px) { .org-stats-grid { grid-template-columns: 1fr; } }

.stat-card {
    display: flex; align-items: center; gap: 16px;
    padding: 20px; border-radius: 14px;
    background: var(--bg-card); border: 1px solid rgba(255,255,255,0.06);
    transition: transform 0.3s ease;
}
.stat-card:hover { transform: translateY(-2px); }
.stat-icon {
    width: 52px; height: 52px; border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.3rem;
}
.stat-card-primary .stat-icon { background: linear-gradient(135deg, rgba(99,102,241,0.2), rgba(139,92,246,0.15)); color: var(--primary-light); }
.stat-card-success .stat-icon { background: linear-gradient(135deg, rgba(16,185,129,0.2), rgba(5,150,105,0.15)); color: #10b981; }
.stat-card-warning .stat-icon { background: linear-gradient(135deg, rgba(245,158,11,0.2), rgba(217,119,6,0.15)); color: #f59e0b; }
.stat-card-info .stat-icon { background: linear-gradient(135deg, rgba(59,130,246,0.2), rgba(37,99,235,0.15)); color: #3b82f6; }

.stat-number { display: block; font-size: 1.8rem; font-weight: 800; line-height: 1; }
.stat-label { display: block; font-size: 0.8rem; color: var(--text-muted); margin-top: 4px; }

/* ── Org Structure ── */
.structure-block { }
.structure-block-header { display: flex; align-items: center; gap: 16px; margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1px solid rgba(255,255,255,0.06); }
.structure-block-icon {
    width: 44px; height: 44px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.1rem; color: #fff; flex-shrink: 0;
}
.structure-block-header h4 { font-size: 1rem; font-weight: 700; margin-bottom: 2px; }

.structure-children { display: grid; grid-template-columns: 1fr; gap: 8px; padding-left: 20px; border-left: 2px solid rgba(99,102,241,0.15); margin-left: 22px; }

.structure-dept { }
.structure-dept-inner {
    display: flex; align-items: center; gap: 14px;
    padding: 14px 18px; border-radius: 10px;
    background: rgba(255,255,255,0.02);
    border: 1px solid rgba(255,255,255,0.05);
    text-decoration: none; color: inherit;
    transition: all 0.25s ease;
}
.structure-dept-inner:hover {
    background: rgba(99,102,241,0.06);
    border-color: rgba(99,102,241,0.15);
    transform: translateX(4px);
}
.structure-dept-icon {
    width: 38px; height: 38px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.9rem; flex-shrink: 0;
}
.structure-dept-division .structure-dept-icon { background: linear-gradient(135deg, rgba(245,158,11,0.2), rgba(217,119,6,0.15)); color: #f59e0b; }

.structure-dept-info { flex: 1; }
.structure-dept-info h5 { font-size: 0.9rem; font-weight: 600; margin-bottom: 4px; }
.structure-desc { font-size: 0.75rem; color: var(--text-muted); line-height: 1.4; margin-top: 6px; margin-bottom: 4px; }
.structure-arrow { color: var(--text-muted); font-size: 0.75rem; transition: transform 0.2s; }
.structure-dept-inner:hover .structure-arrow { transform: translateX(4px); color: var(--primary-light); }

/* ── Headcount Chart ── */
.headcount-chart { max-width: 700px; }
.hc-row { display: flex; align-items: center; gap: 12px; margin-bottom: 10px; }
.hc-label { width: 200px; display: flex; justify-content: space-between; gap: 8px; flex-shrink: 0; }
.hc-name { font-size: 0.8rem; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.hc-count { font-size: 0.75rem; color: var(--text-muted); white-space: nowrap; }
.hc-bar-wrap { flex: 1; height: 22px; background: rgba(255,255,255,0.04); border-radius: 6px; overflow: hidden; }
.hc-bar {
    height: 100%; border-radius: 6px; display: flex; align-items: center; justify-content: flex-end;
    padding-right: 8px; transition: width 0.8s ease;
    background: linear-gradient(90deg, var(--primary), var(--primary-light));
    min-width: 30px;
}
.hc-bar-division { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
.hc-bar-project { background: linear-gradient(90deg, #10b981, #34d399); }
.hc-pct { font-size: 0.65rem; font-weight: 600; color: #fff; }
.hc-total { margin-top: 16px; padding-top: 12px; border-top: 1px solid rgba(255,255,255,0.06); font-size: 0.9rem; }

/* ── Utilities ── */
.text-muted { color: var(--text-muted); }

/* Badge Type */
.dept-type-badge {
    font-size: 0.65rem; padding: 2px 8px; border-radius: 6px;
    font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;
}
.badge-division { background: rgba(245,158,11,0.15); color: #f59e0b; }
.badge-department { background: rgba(99,102,241,0.15); color: var(--primary-light); }
.badge-team { background: rgba(59,130,246,0.15); color: #3b82f6; }
.badge-project { background: rgba(16,185,129,0.15); color: #10b981; }
</style>
