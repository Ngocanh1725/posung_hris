<div class="breadcrumb-bar">
    <a href="<?= BASE_URL ?>/dashboard"><i class="fas fa-home"></i> Trang chủ</a>
    <i class="fas fa-chevron-right"></i>
    <span>Quản lý Danh mục</span>
</div>

<div class="panel-header" style="margin-bottom: 20px;">
    <h2><i class="fas fa-th-list" style="color: #3b82f6;"></i> Quản lý Danh mục Hệ thống</h2>
    <p class="text-muted">Cấu hình các danh mục dùng chung: Phòng ban, Chức vụ, Loại Hợp đồng, Loại Nghỉ phép, Phụ cấp.</p>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px;">
    <!-- Phòng ban -->
    <a href="<?= BASE_URL ?>/category/departments" class="panel" style="text-decoration:none; color:inherit; transition: transform 0.2s, box-shadow 0.2s;">
        <div style="display:flex; align-items:center; gap:15px; padding:10px 0;">
            <div style="width:50px;height:50px;border-radius:12px;background:linear-gradient(135deg,#3b82f6,#2563eb);display:flex;align-items:center;justify-content:center;color:#fff;font-size:22px;">
                <i class="fas fa-sitemap"></i>
            </div>
            <div>
                <h3 style="margin:0;">Phòng ban</h3>
                <p class="text-muted" style="margin:4px 0 0;font-size:13px;">Cơ cấu tổ chức, phòng/ban/bộ phận</p>
            </div>
        </div>
    </a>

    <!-- Chức vụ -->
    <a href="<?= BASE_URL ?>/category/positions" class="panel" style="text-decoration:none; color:inherit; transition: transform 0.2s, box-shadow 0.2s;">
        <div style="display:flex; align-items:center; gap:15px; padding:10px 0;">
            <div style="width:50px;height:50px;border-radius:12px;background:linear-gradient(135deg,#10b981,#059669);display:flex;align-items:center;justify-content:center;color:#fff;font-size:22px;">
                <i class="fas fa-user-tie"></i>
            </div>
            <div>
                <h3 style="margin:0;">Chức vụ</h3>
                <p class="text-muted" style="margin:4px 0 0;font-size:13px;">Ngạch bậc, vị trí công việc</p>
            </div>
        </div>
    </a>

    <!-- Loại Hợp đồng -->
    <a href="<?= BASE_URL ?>/category/manage/contract_types" class="panel" style="text-decoration:none; color:inherit; transition: transform 0.2s, box-shadow 0.2s;">
        <div style="display:flex; align-items:center; gap:15px; padding:10px 0;">
            <div style="width:50px;height:50px;border-radius:12px;background:linear-gradient(135deg,#f59e0b,#d97706);display:flex;align-items:center;justify-content:center;color:#fff;font-size:22px;">
                <i class="fas fa-file-contract"></i>
            </div>
            <div>
                <h3 style="margin:0;">Loại Hợp đồng</h3>
                <p class="text-muted" style="margin:4px 0 0;font-size:13px;">HĐLĐ xác định/không xác định thời hạn</p>
            </div>
        </div>
    </a>

    <!-- Loại Nghỉ phép -->
    <a href="<?= BASE_URL ?>/category/manage/leave_types" class="panel" style="text-decoration:none; color:inherit; transition: transform 0.2s, box-shadow 0.2s;">
        <div style="display:flex; align-items:center; gap:15px; padding:10px 0;">
            <div style="width:50px;height:50px;border-radius:12px;background:linear-gradient(135deg,#8b5cf6,#7c3aed);display:flex;align-items:center;justify-content:center;color:#fff;font-size:22px;">
                <i class="fas fa-calendar-day"></i>
            </div>
            <div>
                <h3 style="margin:0;">Loại Nghỉ phép</h3>
                <p class="text-muted" style="margin:4px 0 0;font-size:13px;">Phép năm, ốm, thai sản, hiếu hỉ...</p>
            </div>
        </div>
    </a>

    <!-- Phụ cấp -->
    <a href="<?= BASE_URL ?>/category/manage/allowances" class="panel" style="text-decoration:none; color:inherit; transition: transform 0.2s, box-shadow 0.2s;">
        <div style="display:flex; align-items:center; gap:15px; padding:10px 0;">
            <div style="width:50px;height:50px;border-radius:12px;background:linear-gradient(135deg,#ef4444,#dc2626);display:flex;align-items:center;justify-content:center;color:#fff;font-size:22px;">
                <i class="fas fa-hand-holding-dollar"></i>
            </div>
            <div>
                <h3 style="margin:0;">Phụ cấp</h3>
                <p class="text-muted" style="margin:4px 0 0;font-size:13px;">Phụ cấp công trường, xăng xe, ăn trưa</p>
            </div>
        </div>
    </a>
</div>
