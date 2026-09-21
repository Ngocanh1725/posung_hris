<?php
$this->view('layouts/header', ['pageTitle' => 'Xuất dữ liệu HUHA HRM - ' . h($employee->full_name)]);
?>

<div class="panel">
    <div class="panel-header">
        <h3 class="panel-title"><i class="fas fa-file-export"></i> Xuất dữ liệu tích hợp HUHA HRM</h3>
        <div>
            <a href="<?= BASE_URL ?>/employee/detail/<?= $employee->id ?>" class="btn btn-ghost"><i class="fas fa-arrow-left"></i> Quay lại hồ sơ</a>
        </div>
    </div>
    
    <div class="panel-body">
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> Tính năng này xuất dữ liệu của nhân sự <strong><?= h($employee->full_name) ?> (<?= h($employee->emp_code) ?>)</strong> ra các định dạng chuẩn tương thích với phần mềm quản lý nhân sự HUHA (Cấu trúc DBF, CSV).
        </div>

        <div class="row mt-4">
            <!-- 1. Bảng Thông tin gia đình -->
            <div class="col-md-6 mb-4">
                <div class="card" style="border: 1px solid var(--border); border-radius: 8px;">
                    <div class="card-header bg-light p-3" style="border-bottom: 1px solid var(--border); font-weight: bold;">
                        <i class="fas fa-users"></i> Quan hệ gia đình (QTQHGD.DBF)
                    </div>
                    <div class="card-body p-3">
                        <p class="text-muted small mb-3">Xuất thông tin người phụ thuộc, cha mẹ, vợ chồng theo cấu trúc mã chuẩn của HUHA.</p>
                        <button class="btn btn-primary btn-sm" onclick="alert('Tính năng đang được hoàn thiện. Dữ liệu mẫu đã sẵn sàng tải xuống.')">
                            <i class="fas fa-download"></i> Tải xuống (.DBF)
                        </button>
                        <button class="btn btn-outline-secondary btn-sm ms-2" onclick="alert('Tính năng đang được hoàn thiện.')">
                            <i class="fas fa-file-csv"></i> Tải xuống (.CSV)
                        </button>
                    </div>
                </div>
            </div>

            <!-- 2. Bảng Quá trình công tác -->
            <div class="col-md-6 mb-4">
                <div class="card" style="border: 1px solid var(--border); border-radius: 8px;">
                    <div class="card-header bg-light p-3" style="border-bottom: 1px solid var(--border); font-weight: bold;">
                        <i class="fas fa-briefcase"></i> Quá trình công tác (QTCTAC.DBF)
                    </div>
                    <div class="card-body p-3">
                        <p class="text-muted small mb-3">Xuất lịch sử công tác, luân chuyển dự án và vị trí công việc theo bảng mã chức vụ HUHA.</p>
                        <button class="btn btn-primary btn-sm" onclick="alert('Tính năng đang được hoàn thiện.')">
                            <i class="fas fa-download"></i> Tải xuống (.DBF)
                        </button>
                    </div>
                </div>
            </div>

            <!-- 3. Bảng Quá trình lương -->
            <div class="col-md-6 mb-4">
                <div class="card" style="border: 1px solid var(--border); border-radius: 8px;">
                    <div class="card-header bg-light p-3" style="border-bottom: 1px solid var(--border); font-weight: bold;">
                        <i class="fas fa-money-bill-wave"></i> Diễn biến lương (QTLUONG.DBF)
                    </div>
                    <div class="card-body p-3">
                        <p class="text-muted small mb-3">Xuất lịch sử tăng lương, hệ số, phụ cấp chức vụ, phụ cấp độc hại.</p>
                        <button class="btn btn-primary btn-sm" onclick="alert('Tính năng đang được hoàn thiện.')">
                            <i class="fas fa-download"></i> Tải xuống (.DBF)
                        </button>
                    </div>
                </div>
            </div>

            <!-- 4. Bảng Khen thưởng - Kỷ luật -->
            <div class="col-md-6 mb-4">
                <div class="card" style="border: 1px solid var(--border); border-radius: 8px;">
                    <div class="card-header bg-light p-3" style="border-bottom: 1px solid var(--border); font-weight: bold;">
                        <i class="fas fa-balance-scale"></i> Khen thưởng Kỷ luật (QTKTKL.DBF)
                    </div>
                    <div class="card-body p-3">
                        <p class="text-muted small mb-3">Xuất các quyết định khen thưởng, vi phạm HSE, Blacklist đồng bộ lên tổng công ty.</p>
                        <button class="btn btn-primary btn-sm" onclick="alert('Tính năng đang được hoàn thiện.')">
                            <i class="fas fa-download"></i> Tải xuống (.DBF)
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<?php $this->view('layouts/footer'); ?>
