<?php
/**
 * ============================================================
 *  View: about/index.php
 *  Giới thiệu Công ty TNHH Cơ Khí Kỹ Thuật Xây Dựng PO SUNG
 *  Cơ cấu Tổ chức, Chức năng Nhiệm vụ, Org Chart Động
 * ============================================================
 */
?>

<style>
/* ═══════ COMPANY HERO ═══════ */
.company-hero {
    background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 50%, #0ea5e9 100%);
    border-radius: 16px;
    padding: 40px;
    color: #fff;
    margin-bottom: 30px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(15, 23, 42, 0.2);
}
.company-hero::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -20%;
    width: 500px;
    height: 500px;
    background: radial-gradient(circle, rgba(14,165,233,0.2) 0%, transparent 70%);
    border-radius: 50%;
}
.company-hero .hero-content { position: relative; z-index: 1; }
.company-hero h1 { font-size: 1.8rem; font-weight: 800; margin-bottom: 8px; letter-spacing: -0.5px; }
.company-hero .hero-subtitle { font-size: 1.1rem; color: rgba(255,255,255,0.8); margin-bottom: 20px; }
.company-hero .hero-desc { font-size: 0.95rem; color: rgba(255,255,255,0.7); line-height: 1.7; max-width: 700px; }

/* ═══════ KPI CARDS ═══════ */
.kpi-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
.kpi-card {
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(10px);
    border-radius: 12px;
    padding: 24px;
    display: flex;
    align-items: center;
    gap: 16px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    border: 1px solid rgba(255, 255, 255, 0.5);
    transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}
.kpi-card:hover { transform: translateY(-5px) scale(1.02); box-shadow: 0 12px 25px rgba(14, 165, 233, 0.15); border-color: #38bdf8; }
.kpi-icon {
    width: 56px; height: 56px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.4rem;
    flex-shrink: 0;
}
.kpi-icon.blue { background: rgba(59,130,246,0.1); color: #3b82f6; }
.kpi-icon.green { background: rgba(16,185,129,0.1); color: #10b981; }
.kpi-icon.purple { background: rgba(139,92,246,0.1); color: #8b5cf6; }
.kpi-icon.orange { background: rgba(245,158,11,0.1); color: #f59e0b; }
.kpi-value { font-size: 1.6rem; font-weight: 800; color: #0f172a; }
.kpi-label { font-size: 0.82rem; color: #64748b; margin-top: 2px; }

/* ═══════ COMPANY INFO GRID ═══════ */
.info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 30px; }
.info-panel {
    background: #fff;
    border-radius: 12px;
    padding: 28px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    border: 1px solid #e2e8f0;
}
.info-panel h3 {
    font-size: 1.1rem;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 10px;
}
.info-panel h3 i { color: #0ea5e9; }
.info-list { list-style: none; padding: 0; margin: 0; }
.info-list li {
    padding: 10px 0;
    border-bottom: 1px solid #f1f5f9;
    display: flex;
    align-items: flex-start;
    gap: 12px;
    font-size: 0.92rem;
    color: #334155;
    line-height: 1.6;
}
.info-list li:last-child { border-bottom: none; }
.info-list li i { color: #0ea5e9; margin-top: 4px; flex-shrink: 0; width: 18px; text-align: center; }

/* ═══════ DYNAMIC ORG CHART ═══════ */
.org-section {
    background: #fff;
    border-radius: 12px;
    padding: 30px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    border: 1px solid #e2e8f0;
    margin-bottom: 30px;
    overflow-x: auto;
    text-align: center;
}
.org-section h3 {
    font-size: 1.2rem;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 24px;
}

/* CSS Tree Structure */
.tree {
    display: inline-block;
    padding: 20px;
}
.tree ul {
    padding-top: 20px; position: relative;
    transition: all 0.5s;
    -webkit-transition: all 0.5s;
    -moz-transition: all 0.5s;
    display: flex;
    justify-content: center;
    padding-left: 0;
}
.tree li {
    float: left; text-align: center;
    list-style-type: none;
    position: relative;
    padding: 20px 5px 0 5px;
    transition: all 0.5s;
    -webkit-transition: all 0.5s;
    -moz-transition: all 0.5s;
}

/* Connecting lines */
.tree li::before, .tree li::after{
    content: '';
    position: absolute; top: 0; right: 50%;
    border-top: 2px solid #cbd5e1;
    width: 50%; height: 20px;
}
.tree li::after{
    right: auto; left: 50%;
    border-left: 2px solid #cbd5e1;
}
/* We need to remove left-right connectors from elements without any siblings */
.tree li:only-child::after, .tree li:only-child::before {
    display: none;
}
.tree li:only-child{ padding-top: 0;}

/* Remove space from the top of single children */
.tree li:first-child::before, .tree li:last-child::after{
    border: 0 none;
}
/* Adding back the vertical line to the last nodes */
.tree li:last-child::before{
    border-right: 2px solid #cbd5e1;
    border-radius: 0 5px 0 0;
}
.tree li:first-child::after{
    border-radius: 5px 0 0 0;
}

/* Time to add downward connectors from parents */
.tree ul ul::before{
    content: '';
    position: absolute; top: 0; left: 50%;
    border-left: 2px solid #cbd5e1;
    width: 0; height: 20px;
    transform: translateX(-50%);
}

.tree li a {
    border: 2px solid #e2e8f0;
    padding: 15px;
    text-decoration: none;
    color: #333;
    font-size: 14px;
    display: inline-block;
    border-radius: 12px;
    transition: all 0.3s;
    background: #f8fafc;
    min-width: 160px;
    position: relative;
}
.tree li a:hover, .tree li a:hover+ul li a {
    background: #f0f9ff; color: #0ea5e9; border-color: #0ea5e9;
    box-shadow: 0 4px 15px rgba(14, 165, 233, 0.1);
}
.tree li a:hover+ul li::after, 
.tree li a:hover+ul li::before, 
.tree li a:hover+ul::before, 
.tree li a:hover+ul ul::before {
    border-color: #0ea5e9;
}

/* Specific Node Styles */
.node-director { background: linear-gradient(135deg, #0f172a, #1e3a8a) !important; color: #fff !important; border-color: #0ea5e9 !important; min-width: 220px !important; }
.node-director .node-icon { background: rgba(255,255,255,0.1); color: #fff; }
.node-director .node-title { color: #fff; }
.node-director .node-subtitle { color: #94a3b8; }

.node-icon {
    width: 40px; height: 40px; border-radius: 10px; background: rgba(14,165,233,0.1); color: #0ea5e9;
    display: flex; align-items: center; justify-content: center; font-size: 1.2rem; margin: 0 auto 8px;
}
.node-title { font-weight: 700; font-size: 0.9rem; color: #0f172a; }
.node-director .node-title { color: #fff; }
.node-subtitle { font-size: 0.75rem; color: #64748b; margin-top: 4px; }
.node-badge {
    display: inline-block; background: rgba(14,165,233,0.1); color: #0284c7;
    font-size: 0.7rem; font-weight: 600; padding: 2px 8px; border-radius: 20px; margin-top: 8px;
}
.node-director .node-badge { background: rgba(56,189,248,0.2); color: #7dd3fc; }

/* Toggle Button */
.btn-toggle-tree {
    position: absolute;
    bottom: -12px;
    left: 50%;
    transform: translateX(-50%);
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: #fff;
    border: 2px solid #cbd5e1;
    color: #64748b;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    z-index: 10;
    transition: all 0.2s;
}
.btn-toggle-tree:hover { border-color: #0ea5e9; color: #0ea5e9; }
.collapsed-ul { display: none !important; }

/* ═══════ DEPT DETAIL MODAL ═══════ */
.dept-detail-overlay {
    display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(15,23,42,0.6); backdrop-filter: blur(4px); z-index: 9999;
    align-items: center; justify-content: center;
}
.dept-detail-overlay.show { display: flex; }
.dept-detail-box {
    background: #fff; border-radius: 16px; padding: 32px; max-width: 600px; width: 90%; max-height: 80vh; overflow-y: auto;
    box-shadow: 0 25px 50px rgba(0,0,0,0.25); animation: slideUp 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}
@keyframes slideUp { from { transform: translateY(50px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
.dept-detail-box h3 { font-size: 1.2rem; font-weight: 700; color: #0f172a; margin-bottom: 6px; }
.dept-detail-box .dept-code { font-size: 0.85rem; color: #0ea5e9; font-weight: 600; margin-bottom: 16px; }
.dept-detail-box .dept-desc { font-size: 0.92rem; color: #334155; line-height: 1.7; }
.dept-detail-box .dept-tasks { margin-top: 16px; }
.dept-detail-box .dept-tasks h4 { font-size: 0.95rem; font-weight: 600; color: #0f172a; margin-bottom: 10px; }
.dept-detail-box .dept-tasks ul { padding-left: 20px; }
.dept-detail-box .dept-tasks li { margin-bottom: 6px; font-size: 0.9rem; color: #475569; }
.dept-detail-close { float: right; background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #94a3b8; transition: color 0.2s; }
.dept-detail-close:hover { color: #ef4444; }

/* ═══════ DEPT DB TABLE ═══════ */
.dept-table-section { margin-top: 30px; }
.dept-table-section table { width: 100%; }
.dept-table-section th { background: #f8fafc; }

@media (max-width: 768px) {
    .info-grid { grid-template-columns: 1fr; }
    .kpi-row { grid-template-columns: 1fr 1fr; }
}
</style>

<!-- ═══════ HERO SECTION ═══════ -->
<div class="company-hero">
    <div class="hero-content">
        <h1><i class="fas fa-building"></i> CÔNG TY TNHH CƠ KHÍ KỸ THUẬT XÂY DỰNG PO SUNG</h1>
        <div class="hero-subtitle">PO SUNG Mechanical Engineering & Construction Co., Ltd.</div>
        <div class="hero-desc">
            Chuyên thi công cơ điện (M&E), xây dựng công nghiệp, lắp đặt hệ thống HVAC, PCCC, cấp thoát nước 
            cho các nhà máy công nghệ cao như Samsung, Amkor Technology,... tại Việt Nam. 
            Trụ sở đặt tại <strong>Bắc Ninh</strong>, với nhiều công trường tại <strong>Thái Nguyên, Hà Nội, Bắc Giang</strong>.
        </div>
    </div>
</div>

<!-- ═══════ KPI CARDS ═══════ -->
<div class="kpi-row">
    <div class="kpi-card" style="cursor: pointer;" onclick="window.location.href='<?= BASE_URL ?>/employee'">
        <div class="kpi-icon blue"><i class="fas fa-users"></i></div>
        <div>
            <div class="kpi-value"><?= number_format($totalActive ?? 0) ?></div>
            <div class="kpi-label">Nhân sự đang làm việc</div>
        </div>
    </div>
    <div class="kpi-card" style="cursor: pointer;" onclick="window.location.href='<?= BASE_URL ?>/organization'">
        <div class="kpi-icon green"><i class="fas fa-sitemap"></i></div>
        <div>
            <div class="kpi-value"><?= $totalDepts ?? 0 ?></div>
            <div class="kpi-label">Phòng ban / Bộ phận</div>
        </div>
    </div>
    <div class="kpi-card" style="cursor: pointer;" onclick="window.location.href='<?= BASE_URL ?>/project'">
        <div class="kpi-icon purple"><i class="fas fa-hard-hat"></i></div>
        <div>
            <div class="kpi-value"><?= $totalProjects ?? 0 ?></div>
            <div class="kpi-label">Dự án đang triển khai</div>
        </div>
    </div>
    <div class="kpi-card" style="cursor: pointer;" onclick="window.location.href='<?= BASE_URL ?>/employee'">
        <div class="kpi-icon orange"><i class="fas fa-globe-asia"></i></div>
        <div>
            <div class="kpi-value"><?= ($typeBreakdown['Expat'] ?? 0) ?></div>
            <div class="kpi-label">Chuyên gia nước ngoài</div>
        </div>
    </div>
</div>

<!-- ═══════ COMPANY INFO ═══════ -->
<div class="info-grid">
    <div class="info-panel">
        <h3><i class="fas fa-info-circle"></i> Thông tin Doanh nghiệp</h3>
        <ul class="info-list">
            <li><i class="fas fa-building"></i> <div><strong>Tên công ty:</strong> Công ty TNHH Cơ Khí Kỹ Thuật Xây Dựng PO SUNG</div></li>
            <li><i class="fas fa-globe"></i> <div><strong>Tên quốc tế:</strong> PO SUNG Mechanical Engineering & Construction Co., Ltd.</div></li>
            <li><i class="fas fa-map-marker-alt"></i> <div><strong>Trụ sở chính:</strong> Khu Công nghiệp Yên Phong, Bắc Ninh, Việt Nam</div></li>
            <li><i class="fas fa-calendar-alt"></i> <div><strong>Năm thành lập:</strong> Hoạt động tại Việt Nam từ 2010</div></li>
            <li><i class="fas fa-users-cog"></i> <div><strong>Quy mô:</strong> 500 – 800 nhân sự (biến động theo dự án)</div></li>
            <li><i class="fas fa-flag"></i> <div><strong>Quốc tịch vốn:</strong> Hàn Quốc (100% vốn FDI)</div></li>
        </ul>
    </div>
    <div class="info-panel">
        <h3><i class="fas fa-cogs"></i> Lĩnh vực Hoạt động</h3>
        <ul class="info-list">
            <li><i class="fas fa-bolt"></i> <div>Thi công Cơ điện (M&E): Hệ thống điện hạ thế, trung thế, chiếu sáng, tủ bảng điện</div></li>
            <li><i class="fas fa-wind"></i> <div>Lắp đặt HVAC: Hệ thống điều hòa không khí, thông gió, phòng sạch (Cleanroom)</div></li>
            <li><i class="fas fa-fire-extinguisher"></i> <div>PCCC: Hệ thống chữa cháy tự động (Sprinkler, FM200, khí CO2)</div></li>
            <li><i class="fas fa-water"></i> <div>Cấp thoát nước: Hệ thống cấp nước sạch, xử lý nước thải công nghiệp</div></li>
            <li><i class="fas fa-industry"></i> <div>Xây dựng Công nghiệp: Kết cấu thép, bê tông, hoàn thiện nội thất nhà máy</div></li>
            <li><i class="fas fa-microchip"></i> <div><strong>Khách hàng chính:</strong> Samsung, Amkor Technology, LG, Hòa Phát</div></li>
        </ul>
    </div>
</div>

<!-- ═══════ DYNAMIC ORG CHART ═══════ -->
<div class="org-section">
    <h3><i class="fas fa-sitemap"></i> SƠ ĐỒ CƠ CẤU TỔ CHỨC CÔNG TY</h3>
    <p style="color: #64748b; font-size: 0.85rem; margin-bottom: 20px;">
        <i class="fas fa-mouse-pointer"></i> Click vào phòng ban để xem chi tiết &nbsp;|&nbsp; <i class="fas fa-plus-circle"></i> Click nút tròn dưới Node để thu/mở
    </p>

    <div class="tree" id="orgTree">
        <ul>
            <li>
                <a href="#" onclick="showDeptDetail('bgd'); return false;" class="node-director">
                    <div class="node-icon"><i class="fas fa-crown"></i></div>
                    <div class="node-title">BAN GIÁM ĐỐC</div>
                    <div class="node-subtitle">BOD</div>
                    <div class="node-badge">Điều hành cao nhất</div>
                    <div class="btn-toggle-tree" onclick="toggleTree(event, this)"><i class="fas fa-minus"></i></div>
                </a>
                <ul>
                    <li>
                        <a href="#" onclick="showDeptDetail('hr'); return false;">
                            <div class="node-icon"><i class="fas fa-user-tie"></i></div>
                            <div class="node-title">Hành chính Nhân sự</div>
                            <div class="node-badge">HR & Admin</div>
                        </a>
                    </li>
                    <li>
                        <a href="#" onclick="showDeptDetail('finance'); return false;">
                            <div class="node-icon"><i class="fas fa-calculator"></i></div>
                            <div class="node-title">Kế toán Tài chính</div>
                            <div class="node-badge">Finance</div>
                        </a>
                    </li>
                    <li>
                        <a href="#" onclick="showDeptDetail('pm'); return false;">
                            <div class="node-icon"><i class="fas fa-tasks"></i></div>
                            <div class="node-title">Quản lý Dự án</div>
                            <div class="node-badge">PM Office</div>
                            <div class="btn-toggle-tree" onclick="toggleTree(event, this)"><i class="fas fa-minus"></i></div>
                        </a>
                        <ul>
                            <li>
                                <a href="#" onclick="showDeptDetail('engineering'); return false;">
                                    <div class="node-icon"><i class="fas fa-drafting-compass"></i></div>
                                    <div class="node-title">Kỹ thuật Thiết kế</div>
                                </a>
                            </li>
                            <li>
                                <a href="#" onclick="showDeptDetail('hse'); return false;">
                                    <div class="node-icon"><i class="fas fa-shield-alt"></i></div>
                                    <div class="node-title">An toàn HSE</div>
                                </a>
                            </li>
                            <li>
                                <a href="#" onclick="showDeptDetail('procurement'); return false;">
                                    <div class="node-icon"><i class="fas fa-truck-loading"></i></div>
                                    <div class="node-title">Mua sắm Vật tư</div>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li>
                        <a href="#" onclick="showDeptDetail('site'); return false;">
                            <div class="node-icon"><i class="fas fa-helmet-safety"></i></div>
                            <div class="node-title">Các Đội Thi công</div>
                            <div class="node-badge">Site Teams</div>
                        </a>
                    </li>
                    <li>
                        <a href="#" onclick="showDeptDetail('qaqc'); return false;">
                            <div class="node-icon"><i class="fas fa-check-circle"></i></div>
                            <div class="node-title">Phòng QA/QC</div>
                            <div class="node-badge">Quality Assurance</div>
                        </a>
                    </li>
                    <li>
                        <a href="#" onclick="showDeptDetail('bim'); return false;">
                            <div class="node-icon"><i class="fas fa-cube"></i></div>
                            <div class="node-title">Phòng BIM</div>
                            <div class="node-badge">BIM</div>
                        </a>
                    </li>
                    <li>
                        <a href="#" onclick="showDeptDetail('legal'); return false;">
                            <div class="node-icon"><i class="fas fa-gavel"></i></div>
                            <div class="node-title">Phòng Pháp chế</div>
                            <div class="node-badge">Legal</div>
                        </a>
                    </li>
                    <li>
                        <a href="#" onclick="showDeptDetail('it'); return false;">
                            <div class="node-icon"><i class="fas fa-network-wired"></i></div>
                            <div class="node-title">Phòng CNTT</div>
                            <div class="node-badge">IT</div>
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</div>

<!-- ═══════ DANH SÁCH PHÒNG BAN TỪ CSDL ═══════ -->
<div class="org-section dept-table-section">
    <h3><i class="fas fa-database"></i> Danh sách Phòng ban trong Hệ thống (CSDL)</h3>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th style="width: 50px;">STT</th>
                    <th>Mã</th>
                    <th>Tên Phòng ban</th>
                    <th>Mô tả</th>
                    <th style="width: 100px; text-align: center;">Số nhân sự</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($departments)): ?>
                    <?php foreach ($departments as $i => $dept): ?>
                    <tr>
                        <td style="text-align: center;"><?= $i + 1 ?></td>
                        <td><strong><?= h($dept['dept_code']) ?></strong></td>
                        <td><?= h($dept['dept_name']) ?></td>
                        <td style="color: #64748b; font-size: 0.88rem;"><?= h($dept['description'] ?? '—') ?></td>
                        <td style="text-align: center;">
                            <span class="node-badge"><?= $dept['staff_count'] ?> người</span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5" style="text-align: center; color: #94a3b8;">Chưa có dữ liệu phòng ban.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ═══════ DEPT DETAIL POPUP ═══════ -->
<div class="dept-detail-overlay" id="deptDetailOverlay" onclick="closeDeptDetail(event)">
    <div class="dept-detail-box" onclick="event.stopPropagation()" style="max-height: 90vh; overflow-y: auto;">
        <button class="dept-detail-close" onclick="closeDeptDetail()">&times;</button>
        <h3 id="deptDetailTitle"></h3>
        <div class="dept-code" id="deptDetailCode"></div>
        <div class="dept-desc" id="deptDetailDesc"></div>
        <div class="dept-tasks">
            <h4><i class="fas fa-clipboard-list"></i> Chức năng – Nhiệm vụ:</h4>
            <ul id="deptDetailTasks"></ul>
        </div>
        <div class="dept-employees" style="margin-top: 15px; padding-top: 15px; border-top: 1px dashed #cbd5e1;">
            <h4><i class="fas fa-users"></i> Danh sách Nhân sự:</h4>
            <ul id="deptDetailEmployees" style="list-style-type: disc; margin-left: 20px; line-height: 1.6; color: #334155; font-size: 0.95rem;"></ul>
        </div>
    </div>
</div>

<script>
// Toggle Tree Branch Function
function toggleTree(e, btn) {
    e.stopPropagation(); // Ngăn sự kiện click lan ra thẻ <a>
    e.preventDefault();
    
    // Tìm thẻ <ul> ngay sau thẻ <a> (btn nằm trong thẻ <a>)
    const aTag = btn.parentElement;
    const ulTag = aTag.nextElementSibling;
    
    if (ulTag && ulTag.tagName === 'UL') {
        ulTag.classList.toggle('collapsed-ul');
        
        // Đổi icon
        const icon = btn.querySelector('i');
        if (ulTag.classList.contains('collapsed-ul')) {
            icon.classList.remove('fa-minus');
            icon.classList.add('fa-plus');
        } else {
            icon.classList.remove('fa-plus');
            icon.classList.add('fa-minus');
        }
    }
}

// Drag to Pan (Kéo để lướt sơ đồ tổ chức)
const slider = document.getElementById('orgTree');
let isDown = false;
let startX;
let scrollLeft;

slider.parentElement.addEventListener('mousedown', (e) => {
    isDown = true;
    slider.parentElement.style.cursor = 'grabbing';
    startX = e.pageX - slider.parentElement.offsetLeft;
    scrollLeft = slider.parentElement.scrollLeft;
});
slider.parentElement.addEventListener('mouseleave', () => {
    isDown = false;
    slider.parentElement.style.cursor = 'auto';
});
slider.parentElement.addEventListener('mouseup', () => {
    isDown = false;
    slider.parentElement.style.cursor = 'auto';
});
slider.parentElement.addEventListener('mousemove', (e) => {
    if (!isDown) return;
    e.preventDefault();
    const x = e.pageX - slider.parentElement.offsetLeft;
    const walk = (x - startX) * 2; // Tốc độ trượt
    slider.parentElement.scrollLeft = scrollLeft - walk;
});

// Data for Dept Detail Modal
const deptData = {
    bgd: {
        title: 'Ban Giám đốc',
        code: 'BOD – Board of Directors',
        desc: 'Cơ quan điều hành cao nhất của công ty, chịu trách nhiệm hoạch định chiến lược, phê duyệt kế hoạch kinh doanh, quản lý toàn diện mọi hoạt động sản xuất – kinh doanh.',
        tasks: [
            'Hoạch định chiến lược phát triển kinh doanh dài hạn',
            'Phê duyệt kế hoạch nhân sự, tuyển dụng, bổ nhiệm/miễn nhiệm cán bộ',
            'Ký duyệt các Hợp đồng, Quyết định Khen thưởng/Kỷ luật/Thuyên chuyển',
            'Giám sát tiến độ các dự án, phê duyệt ngân sách',
            'Đại diện pháp lý cho công ty trong các quan hệ đối ngoại'
        ]
    },
    hr: {
        title: 'Phòng Hành chính – Nhân sự (HR)',
        code: 'HR – Human Resources & Administration',
        desc: 'Quản lý toàn bộ hồ sơ nhân sự, quy trình tuyển dụng, chế độ chính sách, chấm công, tiền lương, bảo hiểm xã hội, và các hoạt động hành chính văn phòng.',
        tasks: [
            'Quản lý hồ sơ nhân viên: Lý lịch, văn bằng chứng chỉ, HĐLĐ (lưu trữ điện tử và giấy)',
            'Tổ chức tuyển dụng: Tiếp nhận đề xuất, đăng tin, thu hồ sơ, phỏng vấn, xếp lương',
            'Chấm công hàng ngày và tính lương hàng tháng cho toàn bộ CBCNV',
            'Quản lý BHXH, BHYT, BHTN: Đăng ký, khai báo, thanh toán chế độ',
            'Thực hiện khen thưởng, kỷ luật, thuyên chuyển, nghỉ hưu, nghỉ việc',
            'Đào tạo, bồi dưỡng nghiệp vụ cho nhân viên',
            'Quản lý tài sản, văn phòng phẩm, xe cộ, nhà ăn'
        ]
    },
    finance: {
        title: 'Phòng Kế toán – Tài chính',
        code: 'FIN – Finance & Accounting',
        desc: 'Quản lý tài chính doanh nghiệp, hạch toán chi phí nhân công theo từng dự án (Cost Center), lập báo cáo tài chính theo quy định.',
        tasks: [
            'Hạch toán chi phí nhân công theo từng dự án / Cost Center',
            'Chi trả lương, thưởng, phụ cấp cho CBCNV',
            'Quản lý thu chi, ngân sách, dòng tiền',
            'Lập báo cáo tài chính theo quy định (Quý, Năm)',
            'Phối hợp với HR đối chiếu bảng lương, BHXH',
            'Quản lý hóa đơn, chứng từ, hợp đồng đầu vào – đầu ra'
        ]
    },
    engineering: {
        title: 'Phòng Kỹ thuật – Thiết kế',
        code: 'ENG – Engineering & Design',
        desc: 'Chịu trách nhiệm về thiết kế bản vẽ kỹ thuật, dự toán công trình, ứng dụng công nghệ BIM vào quản lý dự án.',
        tasks: [
            'Thiết kế bản vẽ kỹ thuật M&E (Shop Drawing, As-built)',
            'Lập dự toán công trình (BOQ – Bill of Quantities)',
            'Ứng dụng BIM/Revit vào quản lý xung đột bản vẽ',
            'Hỗ trợ kỹ thuật cho các đội thi công tại công trường',
            'Nghiên cứu, áp dụng công nghệ mới vào thi công'
        ]
    },
    pm: {
        title: 'Phòng Quản lý Dự án (PM)',
        code: 'PM – Project Management',
        desc: 'Điều phối nhân sự, vật tư, tiến độ thi công tại các dự án/công trường. Là đầu mối liên lạc giữa công ty với chủ đầu tư.',
        tasks: [
            'Lập kế hoạch thi công, phân bổ nguồn lực (nhân sự, thiết bị)',
            'Điều phối nhân sự giữa các công trường (thuyên chuyển nội bộ)',
            'Giám sát tiến độ, chất lượng thi công',
            'Báo cáo tiến độ dự án cho Ban Giám đốc',
            'Phối hợp với HSE đảm bảo an toàn lao động',
            'Quản lý hợp đồng phụ (Subcontractor)'
        ]
    },
    hse: {
        title: 'Phòng An toàn – HSE',
        code: 'HSE – Health, Safety & Environment',
        desc: 'Quản lý an toàn lao động, sức khỏe nghề nghiệp, môi trường thi công. Quản lý Blacklist nhân sự vi phạm an toàn nghiêm trọng.',
        tasks: [
            'Đào tạo an toàn lao động cho CBCNV (huấn luyện định kỳ)',
            'Cấp và quản lý thẻ an toàn (HSE Card) theo từng chủ đầu tư',
            'Kiểm tra, giám sát an toàn tại công trường',
            'Quản lý Blacklist: Danh sách đen nhân sự vi phạm an toàn nghiêm trọng',
            'Điều tra sự cố, lập báo cáo tai nạn lao động',
            'Quản lý chứng chỉ an toàn: Làm việc trên cao, hàn cắt, không gian kín'
        ]
    },
    procurement: {
        title: 'Phòng Mua sắm – Vật tư',
        code: 'PROC – Procurement & Supply Chain',
        desc: 'Quản lý chuỗi cung ứng vật tư, thiết bị phục vụ thi công. Đảm bảo vật tư đúng chất lượng, đúng tiến độ, đúng giá.',
        tasks: [
            'Lập kế hoạch mua sắm vật tư theo tiến độ dự án',
            'Tìm kiếm, đánh giá và quản lý nhà cung cấp',
            'Đàm phán giá, ký hợp đồng mua sắm',
            'Quản lý kho bãi, nhập xuất vật tư tại công trường',
            'Theo dõi giao hàng, kiểm tra chất lượng vật tư đầu vào'
        ]
    },
    site: {
        title: 'Các Đội Thi công tại Công trường',
        code: 'SITE – Site Construction Teams',
        desc: 'Lực lượng thi công trực tiếp tại các dự án. Gồm các đội chuyên môn: Cơ khí, Điện, Đường ống, Hàn, HVAC, PCCC. Là bộ phận đông nhân sự nhất (~60-70% tổng quân số).',
        tasks: [
            'Thi công lắp đặt hệ thống cơ điện (M&E) theo bản vẽ',
            'Hàn ống, hàn kết cấu thép (yêu cầu chứng chỉ 3G/6G)',
            'Lắp đặt hệ thống HVAC, điều hòa không khí, phòng sạch',
            'Thi công hệ thống PCCC (Sprinkler, đường ống, tủ cứu hỏa)',
            'Lắp đặt hệ thống cấp thoát nước công nghiệp',
            'Chấp hành nội quy an toàn, đeo thẻ HSE, chấm công đúng giờ',
            'Báo cáo tiến độ cho Quản lý Dự án hàng ngày'
        ]
    }
};

const deptEmployeesData = <?= json_encode($deptEmployees ?? []) ?>;
const dbDepartments = <?= json_encode($departments ?? []) ?>;

const deptCodeMap = {
    'bgd': 'BOD',
    'hr': 'HR',
    'finance': 'FIN',
    'engineering': 'TECH',
    'site': 'PROD',
    'pm': 'PM',
    'hse': 'HSE',
    'procurement': 'PROC'
};

function showDeptDetail(key) {
    const d = deptData[key];
    if (!d) return;
    
    // Check if we have DB data for this code
    const dbCode = deptCodeMap[key];
    const dbDept = dbDepartments.find(x => x.dept_code === dbCode);
    
    document.getElementById('deptDetailTitle').textContent = dbDept ? dbDept.dept_name : d.title;
    document.getElementById('deptDetailCode').textContent = dbCode || d.code;
    document.getElementById('deptDetailDesc').textContent = (dbDept && dbDept.description) ? dbDept.description : d.desc;

    // Functions
    let tasks = d.tasks;
    if (dbDept && dbDept.functions) {
        try {
            const parsed = JSON.parse(dbDept.functions);
            if (Array.isArray(parsed) && parsed.length > 0) tasks = parsed;
        } catch (e) {}
    }

    const tasksUl = document.getElementById('deptDetailTasks');
    tasksUl.innerHTML = '';
    tasks.forEach(t => {
        const li = document.createElement('li');
        li.textContent = t;
        tasksUl.appendChild(li);
    });
    
    // Employees
    const empUl = document.getElementById('deptDetailEmployees');
    empUl.innerHTML = '';
    const emps = dbCode ? (deptEmployeesData[dbCode] || []) : [];
    
    if (emps.length > 0) {
        emps.forEach(e => {
            const li = document.createElement('li');
            li.innerHTML = `<strong>${e.full_name}</strong> - ${e.pos_title || 'Nhân viên'} <span style="color:#94a3b8; font-size:0.85em;">(${e.emp_code})</span>`;
            empUl.appendChild(li);
        });
    } else {
        empUl.innerHTML = '<li style="color: #94a3b8; font-style: italic;">Chưa có nhân sự được phân bổ vào phòng ban này.</li>';
    }

    document.getElementById('deptDetailOverlay').classList.add('show');
}

function closeDeptDetail(e) {
    if (e && e.target !== e.currentTarget) return;
    document.getElementById('deptDetailOverlay').classList.remove('show');
}

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeDeptDetail();
});
</script>
