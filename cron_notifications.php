<?php
/**
 * ============================================================
 *  POSUNG HRIS – Cron Job Thông Báo Tự Động (Notification Cron)
 * ============================================================
 *  Lệnh chạy CLI:
 *    php cron_notifications.php
 *
 *  Mục đích:
 *  - Quét CSDL hàng ngày để tự động tạo thông báo nhắc nhở:
 *    + Hợp đồng lao động sắp hết hạn (30/15/7 ngày)
 *    + Thẻ An toàn HSE / Chứng chỉ sắp hết hạn (60/30 ngày)
 *    + Visa / Work Permit chuyên gia sắp hết hạn (90/60/30 ngày)
 *    + Chúc mừng sinh nhật nhân viên trong ngày
 *    + Nhân viên sắp kết thúc thử việc (15/7 ngày)
 * ============================================================
 */

// Đảm bảo chạy từ CLI hoặc kiểm tra token bảo mật nếu chạy qua HTTP
if (php_sapi_name() !== 'cli') {
    $secretToken = 'posung_cron_secret_key_2026';
    if (!isset($_GET['token']) || $_GET['token'] !== $secretToken) {
        http_response_code(403);
        die("403 Forbidden: Invalid token.");
    }
}

// Thiết lập môi trường và nạp dependencies
require_once __DIR__ . '/config/config.php';
require_once APP_ROOT . '/core/Database.php';
require_once APP_ROOT . '/core/Session.php';
require_once APP_ROOT . '/core/AuditLogger.php';
require_once APP_ROOT . '/core/NotificationService.php';

$startTime = microtime(true);
$today = date('Y-m-d H:i:s');

echo "============================================================\n";
echo " POSUNG HRIS - CRON NOTIFICATION RUNNER\n";
echo " Thời gian khởi chạy: {$today}\n";
echo "============================================================\n";

try {
    // Chạy kiểm tra các nhắc nhở
    $results = NotificationService::runScheduledReminders();

    $duration = round(microtime(true) - $startTime, 4);

    echo "[1] Hợp đồng lao động hết hạn:       " . $results['contract_reminders'] . " thông báo\n";
    echo "[2] Thẻ An toàn HSE & Chứng chỉ:    " . $results['hse_reminders'] . " thông báo\n";
    echo "[3] Visa & Giấy phép lao động:      " . $results['visa_reminders'] . " thông báo\n";
    echo "[4] Sinh nhật nhân viên trong ngày: " . $results['birthdays'] . " thông báo\n";
    echo "[5] Kết thúc thời gian thử việc:    " . $results['probation_reminders'] . " thông báo\n";
    echo "[6] Đánh giá hiệu suất 360° & KRA:  " . ($results['evaluation_reminders'] ?? 0) . " thông báo\n";
    echo "------------------------------------------------------------\n";
    echo "=> TỔNG CỘNG THÔNG BÁO ĐÃ TẠO:       " . $results['total_created'] . " thông báo\n";
    echo "=> Thời gian thực thi:              {$duration} giây\n";
    echo "============================================================\n";

    // Ghi audit log
    AuditLogger::log('cron', 'notifications', null, null, $results, "Cron Job chạy quét thông báo tự động lúc {$today}. Tạo {$results['total_created']} thông báo.");

} catch (Exception $e) {
    echo "LỖI KHI CHẠY CRON: " . $e->getMessage() . "\n";
    AuditLogger::log('cron_error', 'notifications', null, null, ['error' => $e->getMessage()], "Lỗi khi chạy cron_notifications.php");
    exit(1);
}

exit(0);
