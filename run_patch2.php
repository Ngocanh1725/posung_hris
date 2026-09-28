<?php
require 'config/config.php';
require 'app/core/Database.php';

$db = Database::getInstance();

try {
    $db->query("ALTER TABLE `timesheets`
ADD COLUMN `shift_id` INT NULL AFTER `project_id`,
ADD COLUMN `checkin_lat` DECIMAL(10,8) NULL AFTER `check_out`,
ADD COLUMN `checkin_long` DECIMAL(11,8) NULL AFTER `checkin_lat`,
ADD COLUMN `checkin_distance_m` INT NULL AFTER `checkin_long`,
ADD COLUMN `checkin_device_type` ENUM('OFFICE_FACEID', 'SITE_GPS', 'MANUAL') DEFAULT 'SITE_GPS' AFTER `checkin_distance_m`,
ADD COLUMN `approval_status` ENUM('PENDING_FOREMAN', 'PENDING_SITE_MANAGER', 'PENDING_HR', 'APPROVED', 'REJECTED') DEFAULT 'PENDING_FOREMAN' AFTER `status`;");
    echo "Added columns to timesheets.\n";
} catch (Exception $e) { echo "Error: " . $e->getMessage() . "\n"; }

try {
    $db->query("ALTER TABLE `timesheets` ADD CONSTRAINT `fk_timesheets_shift` FOREIGN KEY (`shift_id`) REFERENCES `shifts`(`id`) ON DELETE SET NULL;");
    echo "Added fk to timesheets.\n";
} catch (Exception $e) { echo "Error: " . $e->getMessage() . "\n"; }

try {
    $db->query("INSERT INTO `shifts` (`shift_code`, `shift_name`, `start_time`, `end_time`, `break_start`, `break_end`, `is_night_shift`, `ot_rate_default`, `status`) VALUES
('HC', 'Ca Hành chính VP', '08:00:00', '17:00:00', '12:00:00', '13:00:00', 0, 1.50, 'Active'),
('CA1', 'Ca 1 (Sáng)', '06:00:00', '14:00:00', NULL, NULL, 0, 1.50, 'Active'),
('CA2', 'Ca 2 (Chiều)', '14:00:00', '22:00:00', NULL, NULL, 0, 1.50, 'Active'),
('CA3', 'Ca 3 (Đêm)', '22:00:00', '06:00:00', NULL, NULL, 1, 2.00, 'Active'),
('BETONG', 'Ca Đổ Bê Tông (Xuyên đêm)', '18:00:00', '06:00:00', '00:00:00', '01:00:00', 1, 2.00, 'Active');");
    echo "Inserted shifts data.\n";
} catch (Exception $e) { echo "Error: " . $e->getMessage() . "\n"; }
?>
