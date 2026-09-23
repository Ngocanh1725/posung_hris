-- Thiết lập RBAC và Menu (Role-Based Access Control)
-- Cú pháp MySQL chuẩn, hỗ trợ InnoDB và utf8mb4_unicode_ci
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- 1. Bảng system_modules (Quản lý Menu và Module)
CREATE TABLE IF NOT EXISTS `system_modules` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `code` VARCHAR(50) NOT NULL UNIQUE,
    `name` VARCHAR(100) NOT NULL,
    `icon` VARCHAR(50) DEFAULT NULL,
    `parent_id` INT UNSIGNED DEFAULT NULL,
    `url` VARCHAR(255) DEFAULT NULL,
    `sort_order` INT NOT NULL DEFAULT 0,
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_sys_module_parent` FOREIGN KEY (`parent_id`) REFERENCES `system_modules`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Bảng system_permissions (Danh mục quyền)
CREATE TABLE IF NOT EXISTS `system_permissions` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `module_id` INT UNSIGNED NOT NULL,
    `code` VARCHAR(100) NOT NULL UNIQUE,
    `name` VARCHAR(100) NOT NULL,
    `action_name` VARCHAR(50) NOT NULL,
    `description` VARCHAR(255) DEFAULT NULL,
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_sys_perm_module` FOREIGN KEY (`module_id`) REFERENCES `system_modules`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Bảng system_roles (Vai trò)
CREATE TABLE IF NOT EXISTS `system_roles` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `code` VARCHAR(50) NOT NULL UNIQUE,
    `name` VARCHAR(100) NOT NULL,
    `description` VARCHAR(255) DEFAULT NULL,
    `level` TINYINT UNSIGNED NOT NULL DEFAULT 3 COMMENT '1: Super Admin, 2: Quản trị bộ phận, 3: Nhân viên',
    `created_by` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Bảng system_role_permissions (Gắn quyền cho Vai trò)
CREATE TABLE IF NOT EXISTS `system_role_permissions` (
    `role_id` INT UNSIGNED NOT NULL,
    `permission_id` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`role_id`, `permission_id`),
    CONSTRAINT `fk_sys_rp_role` FOREIGN KEY (`role_id`) REFERENCES `system_roles`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_sys_rp_perm` FOREIGN KEY (`permission_id`) REFERENCES `system_permissions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Bảng system_user_permissions (Quyền đặc thù cho từng User)
CREATE TABLE IF NOT EXISTS `system_user_permissions` (
    `user_id` INT UNSIGNED NOT NULL,
    `permission_id` INT UNSIGNED NOT NULL,
    `is_granted` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1: Cấp quyền, 0: Cấm quyền (Ghi đè quyền từ role)',
    `assigned_by` INT UNSIGNED DEFAULT NULL,
    PRIMARY KEY (`user_id`, `permission_id`),
    CONSTRAINT `fk_sys_up_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_sys_up_perm` FOREIGN KEY (`permission_id`) REFERENCES `system_permissions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Cập nhật bảng users hiện hữu
ALTER TABLE `users` 
    ADD COLUMN `role_id` INT UNSIGNED DEFAULT NULL AFTER `employee_id`,
    ADD COLUMN `parent_admin_id` INT UNSIGNED DEFAULT NULL AFTER `status`,
    ADD COLUMN `can_delegate` TINYINT(1) NOT NULL DEFAULT 0 AFTER `parent_admin_id`;

ALTER TABLE `users`
    ADD CONSTRAINT `fk_user_role` FOREIGN KEY (`role_id`) REFERENCES `system_roles`(`id`) ON DELETE SET NULL,
    ADD CONSTRAINT `fk_user_parent_admin` FOREIGN KEY (`parent_admin_id`) REFERENCES `users`(`id`) ON DELETE SET NULL;

-- ---------------------------------------------------------
-- NẠP DỮ LIỆU SEEDER (Dữ liệu mẫu chuẩn của Po Sung)
-- ---------------------------------------------------------

-- Modules
INSERT INTO `system_modules` (`id`, `code`, `name`, `icon`, `url`, `sort_order`) VALUES
(1, 'admin_system', 'Quản trị hệ thống', 'fas fa-cogs', '/admin', 10),
(2, 'office_admin', 'Hành chính văn phòng', 'fas fa-file-alt', '/office', 20),
(3, 'accounting', 'Kế toán - Tài chính', 'fas fa-money-check-alt', '/accounting', 30),
(4, 'human_resources', 'Quản trị Nhân sự', 'fas fa-users', '/hr', 40),
(5, 'recruitment', 'Tuyển dụng', 'fas fa-user-plus', '/recruitment', 50),
(6, 'timesheet_leave', 'Chấm công & Nghỉ phép', 'fas fa-calendar-check', '/timesheet', 60),
(7, 'system_reports', 'Báo cáo & Thống kê', 'fas fa-chart-bar', '/report', 70)
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);

-- Permissions
INSERT INTO `system_permissions` (`module_id`, `code`, `name`, `action_name`, `description`) VALUES
(1, 'admin_system.view', 'Xem hệ thống', 'view', 'Xem danh mục và vai trò'),
(1, 'admin_system.manage', 'Quản lý hệ thống', 'manage', 'Thêm sửa xóa người dùng, phân quyền'),
(2, 'office_admin.view', 'Xem tài liệu hành chính', 'view', 'Xem tài liệu, công văn'),
(2, 'office_admin.manage', 'Quản lý hành chính', 'manage', 'Soạn thảo, xóa công văn'),
(3, 'accounting.view', 'Xem tài chính', 'view', 'Xem bảng lương, chi phí'),
(3, 'accounting.manage', 'Quản lý tài chính', 'manage', 'Tính lương, tạo phiếu chi'),
(3, 'accounting.approve', 'Phê duyệt lương', 'approve', 'Phê duyệt phiếu lương, tạm ứng'),
(4, 'human_resources.view', 'Xem hồ sơ nhân sự', 'view', 'Xem thông tin nhân viên'),
(4, 'human_resources.manage', 'Quản lý nhân sự', 'manage', 'Thêm sửa xóa hồ sơ, điều chuyển'),
(5, 'recruitment.view', 'Xem hồ sơ ứng viên', 'view', 'Xem dữ liệu ứng viên'),
(5, 'recruitment.manage', 'Quản lý tuyển dụng', 'manage', 'Tạo kế hoạch, mời phỏng vấn'),
(6, 'timesheet_leave.view', 'Xem bảng công', 'view', 'Xem dữ liệu chấm công'),
(6, 'timesheet_leave.manage', 'Quản lý chấm công', 'manage', 'Duyệt đơn nghỉ phép, sửa công'),
(7, 'system_reports.view', 'Xem báo cáo', 'view', 'Xem báo cáo nhân sự, lương')
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);

-- Roles
INSERT INTO `system_roles` (`id`, `code`, `name`, `level`, `description`) VALUES
(1, 'super_admin', 'Super Admin', 1, 'Quyền quản trị tối cao của toàn hệ thống'),
(2, 'sub_admin', 'Quản trị bộ phận', 2, 'Quản trị viên dành cho Trưởng bộ phận'),
(3, 'employee', 'Nhân viên', 3, 'Tài khoản nhân sự thường')
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);

-- Gắn toàn bộ quyền cho role super_admin
INSERT IGNORE INTO `system_role_permissions` (`role_id`, `permission_id`)
SELECT 1, `id` FROM `system_permissions`;

-- Nạp tài khoản Super Admin mẫu
-- Mật khẩu hash là chữ 'admin' bằng Bcrypt
INSERT INTO `users` (`username`, `password_hash`, `role_id`, `status`, `can_delegate`) 
VALUES ('admin', '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', 1, 'active', 1)
ON DUPLICATE KEY UPDATE `role_id` = 1, `can_delegate` = 1;

SET FOREIGN_KEY_CHECKS = 1;
