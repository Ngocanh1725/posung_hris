<?php
/**
 * View: recruitment/kiosk.php
 * Hiển thị mã QR để ứng viên quét và nộp hồ sơ.
 */
?>
<div class="panel" style="max-width: 600px; margin: 40px auto; text-align: center; padding: 40px;">
    <h2>Kiosk Tuyển Dụng</h2>
    <p class="text-muted mb-4">Quét mã QR bên dưới để nộp hồ sơ ứng tuyển trực tuyến nhanh chóng.</p>
    
    <div style="background: #fff; padding: 20px; display: inline-block; border-radius: 16px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); margin-bottom: 20px;">
        <img src="https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=<?= urlencode(BASE_URL . '/recruitment/apply') ?>" alt="QR Code" style="width: 250px; height: 250px;">
    </div>
    
    <p style="font-size: 1.1rem; font-weight: 600; color: var(--primary);">posung.vn/tuyendung</p>
    
    <div class="mt-4">
        <a href="<?= BASE_URL ?>/recruitment" class="btn btn-secondary">Quay lại Quản lý Tuyển dụng</a>
    </div>
</div>
