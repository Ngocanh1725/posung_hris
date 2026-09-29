-- ============================================================
-- POSUNG HRIS - SQL PATCH: AUDIT LOG & DATABASE BACKUP
-- File: sql/patch_audit_backup_v1.sql
-- ============================================================

-- 1. BẢNG NHẬT KÝ HOẠT ĐỘNG TOÀN DIỆN (SYSTEM AUDIT LOGS)
CREATE TABLE IF NOT EXISTS `audit_logs` (
    `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NULL,
    `user_name` VARCHAR(100) NULL,
    `action` ENUM('create', 'update', 'delete', 'login', 'logout', 'view', 'export', 'backup', 'restore') NOT NULL,
    `module` VARCHAR(50) NOT NULL COMMENT 'employee, payroll, contract, insurance, evaluation, training, auth, system...',
    `record_id` VARCHAR(50) NULL COMMENT 'ID của bản ghi bị tác động',
    `description` VARCHAR(255) NULL COMMENT 'Mô tả tóm tắt hành động',
    `old_values` LONGTEXT NULL COMMENT 'Dữ liệu trước thay đổi (JSON)',
    `new_values` LONGTEXT NULL COMMENT 'Dữ liệu sau thay đổi (JSON)',
    `ip_address` VARCHAR(45) NULL,
    `user_agent` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_audit_user` (`user_id`),
    INDEX `idx_audit_module` (`module`),
    INDEX `idx_audit_action` (`action`),
    INDEX `idx_audit_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. BẢNG QUẢN LÝ BẢN SAO LƯU CSDL (DATABASE BACKUPS METADATA)
CREATE TABLE IF NOT EXISTS `system_backups` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `filename` VARCHAR(255) NOT NULL UNIQUE,
    `file_path` VARCHAR(255) NOT NULL,
    `file_size` BIGINT NOT NULL DEFAULT 0,
    `tables_count` INT NOT NULL DEFAULT 0,
    `records_count` INT NOT NULL DEFAULT 0,
    `backup_type` ENUM('Manual', 'Auto') DEFAULT 'Manual',
    `created_by` INT NULL,
    `creator_name` VARCHAR(100) NULL,
    `notes` TEXT NULL,
    `status` ENUM('Completed', 'Failed', 'Restored') DEFAULT 'Completed',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_backup_status` (`status`),
    INDEX `idx_backup_type` (`backup_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. BẢNG CẤU HÌNH HỆ THỐNG & AUTO BACKUP SETTINGS
CREATE TABLE IF NOT EXISTS `system_settings` (
    `setting_key` VARCHAR(100) PRIMARY KEY,
    `setting_value` TEXT NULL,
    `description` VARCHAR(255) NULL,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `system_settings` (`setting_key`, `setting_value`, `description`)
VALUES 
('auto_backup_enabled', '1', 'Bật/Tắt tự động sao lưu CSDL định kỳ'),
('auto_backup_frequency', 'daily', 'Tần suất sao lưu: daily (hàng ngày) hoặc weekly (hàng tuần)'),
('auto_backup_time', '02:00', 'Khung giờ chạy sao lưu tự động'),
('auto_backup_keep_days', '30', 'Số ngày lưu trữ bản backup trước khi tự động dọn dẹp')
ON DUPLICATE KEY UPDATE `description` = VALUES(`description`);

-- 4. THÊM MENU HỆ THỐNG VÀO SYSTEM_MENUS
INSERT INTO `system_menus` (`title`, `url`, `icon`, `parent_id`, `sort_order`, `is_active`, `permission_required`)
SELECT 'Sao lưu & Phục hồi', 'backup', 'fas fa-database', NULL, 48, 1, 'database_mgr.view'
WHERE NOT EXISTS (
    SELECT 1 FROM `system_menus` WHERE `url` = 'backup'
);

INSERT INTO `system_menus` (`title`, `url`, `icon`, `parent_id`, `sort_order`, `is_active`, `permission_required`)
SELECT 'Nhật ký Hoạt động (Audit)', 'audit', 'fas fa-clock-rotate-left', NULL, 49, 1, 'database_mgr.view'
WHERE NOT EXISTS (
    SELECT 1 FROM `system_menus` WHERE `url` = 'audit'
);

-- 5. SEED DỮ LIỆU MẪU BAN ĐẦU CHO AUDIT_LOGS
INSERT INTO `audit_logs` (`user_id`, `user_name`, `action`, `module`, `record_id`, `description`, `old_values`, `new_values`, `ip_address`, `created_at`)
VALUES
(1, 'Administrator', 'login', 'auth', '1', 'Quản trị viên đăng nhập vào hệ thống', NULL, NULL, '127.0.0.1', DATE_SUB(NOW(), INTERVAL 2 HOUR)),
(1, 'Administrator', 'backup', 'system', NULL, 'Khởi tạo sao lưu cơ sở dữ liệu định kỳ', NULL, '{"filename": "backup_20260928_init.sql", "size": "2.4MB"}', '127.0.0.1', DATE_SUB(NOW(), INTERVAL 1 HOUR)),
(1, 'Administrator', 'update', 'employee', '1', 'Cập nhật mức lương và phòng ban nhân viên', '{"department_id": 2, "base_salary": "12000000"}', '{"department_id": 3, "base_salary": "14000000"}', '127.0.0.1', DATE_SUB(NOW(), INTERVAL 30 MINUTE)),
(1, 'Administrator', 'create', 'insurance', '5', 'Đăng ký mới hồ sơ bảo hiểm xã hội', NULL, '{"social_insurance_no": "0120000005", "insurance_salary": "6500000"}', '127.0.0.1', DATE_SUB(NOW(), INTERVAL 15 MINUTE));
