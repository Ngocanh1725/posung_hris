-- ============================================================
-- POSUNG HRIS - BẢN VÁ CẬP NHẬT (PATCH) V4
-- Chủ đề: Geofence GPS, Ca kíp công trường, Thẻ An toàn HSE, Hạch toán dự án
-- ============================================================

-- 1. Bảng `shifts` (Quản lý ca làm việc)
CREATE TABLE IF NOT EXISTS `shifts` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `shift_code` VARCHAR(50) NOT NULL UNIQUE,
    `shift_name` VARCHAR(100) NOT NULL,
    `start_time` TIME NOT NULL,
    `end_time` TIME NOT NULL,
    `break_start` TIME NULL,
    `break_end` TIME NULL,
    `is_night_shift` TINYINT(1) DEFAULT 0,
    `ot_rate_default` DECIMAL(5,2) DEFAULT 1.50,
    `is_split_shift` TINYINT(1) DEFAULT 0,
    `description` TEXT NULL,
    `status` ENUM('Active', 'Inactive') DEFAULT 'Active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Bảng `timekeeping_locations` (Danh mục điểm chấm công GPS/FaceID)
CREATE TABLE IF NOT EXISTS `timekeeping_locations` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `project_id` INT NULL,
    `location_name` VARCHAR(150) NOT NULL,
    `address` TEXT NULL,
    `latitude` DECIMAL(10,8) NULL,
    `longitude` DECIMAL(11,8) NULL,
    `allowed_radius_meters` INT DEFAULT 100,
    `device_ip` VARCHAR(45) NULL,
    `device_serial` VARCHAR(100) NULL,
    `type` ENUM('OFFICE_FACEID', 'SITE_GPS') NOT NULL DEFAULT 'SITE_GPS',
    `status` ENUM('Active', 'Inactive') DEFAULT 'Active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Bảng `hse_safety_cards` (Thẻ an toàn lao động 6 nhóm theo luật)
CREATE TABLE IF NOT EXISTS `hse_safety_cards` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `employee_id` INT NOT NULL,
    `group_type` ENUM('GROUP_1', 'GROUP_2', 'GROUP_3', 'GROUP_4', 'GROUP_5', 'GROUP_6') NOT NULL,
    `card_number` VARCHAR(100) NOT NULL,
    `issue_date` DATE NOT NULL,
    `expiry_date` DATE NOT NULL,
    `training_unit` VARCHAR(255) NULL,
    `status` ENUM('VALID', 'EXPIRING_SOON', 'EXPIRED') DEFAULT 'VALID',
    `scan_attachment_url` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`employee_id`) REFERENCES `employees`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Cập nhật bảng `projects`
-- site_manager_id và hse_lead_id ĐÃ TỒN TẠI TRONG BẢNG PROJECTS (như Describe ở trên hiển thị), cost_center_code cũng đã tồn tại.
-- Tôi sẽ bỏ các ALTER TABLE projects hoặc đổi tên nếu cần. Nhưng Describe đã có `site_manager_id` và `hse_lead_id` và `cost_center_code`. Tôi sẽ chỉ update Foreign Keys.
-- (Bỏ qua Add column đã có)

ALTER TABLE `projects`
ADD CONSTRAINT `fk_projects_site_mgr` FOREIGN KEY (`site_manager_id`) REFERENCES `employees`(`id`) ON DELETE SET NULL,
ADD CONSTRAINT `fk_projects_safety_off` FOREIGN KEY (`hse_lead_id`) REFERENCES `employees`(`id`) ON DELETE SET NULL;

-- 5. Cập nhật bảng `timesheets`
ALTER TABLE `timesheets`
ADD COLUMN `shift_id` INT NULL AFTER `project_id`,
ADD COLUMN `checkin_lat` DECIMAL(10,8) NULL AFTER `check_out`,
ADD COLUMN `checkin_long` DECIMAL(11,8) NULL AFTER `checkin_lat`,
ADD COLUMN `checkin_distance_m` INT NULL AFTER `checkin_long`,
ADD COLUMN `checkin_device_type` ENUM('OFFICE_FACEID', 'SITE_GPS', 'MANUAL') DEFAULT 'SITE_GPS' AFTER `checkin_distance_m`,
ADD COLUMN `approval_status` ENUM('PENDING_FOREMAN', 'PENDING_SITE_MANAGER', 'PENDING_HR', 'APPROVED', 'REJECTED') DEFAULT 'PENDING_FOREMAN' AFTER `status`;

ALTER TABLE `timesheets`
ADD CONSTRAINT `fk_timesheets_shift` FOREIGN KEY (`shift_id`) REFERENCES `shifts`(`id`) ON DELETE SET NULL;

-- 6. Insert một số dữ liệu mẫu cho Ca làm việc
INSERT INTO `shifts` (`shift_code`, `shift_name`, `start_time`, `end_time`, `break_start`, `break_end`, `is_night_shift`, `ot_rate_default`, `status`) VALUES
('HC', 'Ca Hành chính VP', '08:00:00', '17:00:00', '12:00:00', '13:00:00', 0, 1.50, 'Active'),
('CA1', 'Ca 1 (Sáng)', '06:00:00', '14:00:00', NULL, NULL, 0, 1.50, 'Active'),
('CA2', 'Ca 2 (Chiều)', '14:00:00', '22:00:00', NULL, NULL, 0, 1.50, 'Active'),
('CA3', 'Ca 3 (Đêm)', '22:00:00', '06:00:00', NULL, NULL, 1, 2.00, 'Active'),
('BETONG', 'Ca Đổ Bê Tông (Xuyên đêm)', '18:00:00', '06:00:00', '00:00:00', '01:00:00', 1, 2.00, 'Active');
