-- ============================================================
-- POSUNG HRIS - Patch Offboarding V2 & Holiday Calendar
-- Nâng cấp Quy trình Thôi việc Đa bộ phận & Lịch Nghỉ lễ Toàn công ty
-- ============================================================

-- ------------------------------------------------------------
-- 1. Bảng offboarding_templates: Mẫu quy trình thôi việc
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `offboarding_templates` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(150) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 2. Bảng offboarding_tasks: Nhiệm vụ chi tiết trong mẫu thôi việc
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `offboarding_tasks` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `template_id` INT(11) NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `responsible_department` ENUM('IT', 'HR', 'Finance', 'HSE', 'Admin') NOT NULL,
    `sort_order` INT(11) NOT NULL DEFAULT 0,
    `is_blocking` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_template_id` (`template_id`),
    KEY `idx_resp_dept` (`responsible_department`),
    CONSTRAINT `fk_offb_task_template` FOREIGN KEY (`template_id`) REFERENCES `offboarding_templates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 3. Bảng employee_offboardings: Phiên thôi việc của nhân viên
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `employee_offboardings` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `employee_id` INT(11) NOT NULL,
    `template_id` INT(11) NOT NULL,
    `last_working_day` DATE NOT NULL,
    `reason` ENUM('Resign', 'Terminate', 'Contract_End', 'Retirement') NOT NULL DEFAULT 'Resign',
    `status` ENUM('InProgress', 'Completed') NOT NULL DEFAULT 'InProgress',
    `clearance_date` DATE DEFAULT NULL,
    `notes` TEXT DEFAULT NULL,
    `created_by` INT(11) DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_employee_id` (`employee_id`),
    KEY `idx_template_id` (`template_id`),
    KEY `idx_status` (`status`),
    CONSTRAINT `fk_emp_offboardings_emp` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_emp_offboardings_tpl` FOREIGN KEY (`template_id`) REFERENCES `offboarding_templates` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 4. Bảng employee_offboarding_items: Checklist chi tiết từng task
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `employee_offboarding_items` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `offboarding_id` INT(11) NOT NULL,
    `task_id` INT(11) NOT NULL,
    `status` ENUM('Pending', 'Done', 'NA') NOT NULL DEFAULT 'Pending',
    `completed_by` INT(11) DEFAULT NULL,
    `completed_at` DATETIME DEFAULT NULL,
    `notes` TEXT DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_offboarding_id` (`offboarding_id`),
    KEY `idx_task_id` (`task_id`),
    KEY `idx_status` (`status`),
    CONSTRAINT `fk_emp_offb_items_off` FOREIGN KEY (`offboarding_id`) REFERENCES `employee_offboardings` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_emp_offb_items_task` FOREIGN KEY (`task_id`) REFERENCES `offboarding_tasks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 5. Bảng holidays: Quản lý lịch ngày lễ, tết toàn công ty
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `holidays` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(150) NOT NULL,
    `date` DATE NOT NULL,
    `type` ENUM('National', 'Company') NOT NULL DEFAULT 'National',
    `is_recurring` TINYINT(1) NOT NULL DEFAULT 0,
    `applies_to_department_id` INT(11) DEFAULT NULL,
    `description` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_date` (`date`),
    KEY `idx_type` (`type`),
    KEY `idx_dept` (`applies_to_department_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Đồng bộ dữ liệu ban đầu từ leave_holidays nếu holidays còn trống
INSERT IGNORE INTO `holidays` (`id`, `name`, `date`, `type`, `is_recurring`, `applies_to_department_id`, `description`, `created_at`)
SELECT `id`, `name`, `date`, `type`, `is_recurring`, `applies_to_department_id`, `description`, `created_at`
FROM `leave_holidays`;

-- ------------------------------------------------------------
-- 6. Khởi tạo dữ liệu mẫu cho Offboarding Templates & Tasks
-- ------------------------------------------------------------
INSERT INTO `offboarding_templates` (`id`, `name`, `description`, `is_active`) VALUES
(1, 'Quy trình Thôi việc Tiêu chuẩn POSUNG (Đa phòng ban)', 'Áp dụng cho nhân viên chính thức xin thôi việc hoặc đơn phương chấm dứt hợp đồng lao động', 1),
(2, 'Quy trình Nghỉ hưu theo Chế độ (Retirement)', 'Áp dụng cho cán bộ nhân viên đủ tuổi nghỉ hưu theo quy định của Luật Lao động', 1),
(3, 'Quy trình Chấm dứt Thử việc / Hết hạn HĐLĐ', 'Áp dụng cho nhân sự thử việc không đạt hoặc hợp đồng xác định thời hạn hết hạn không tái ký', 1)
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `description` = VALUES(`description`);

-- Tasks cho Template 1 (Thôi việc Tiêu chuẩn)
INSERT INTO `offboarding_tasks` (`template_id`, `title`, `responsible_department`, `sort_order`, `is_blocking`) VALUES
(1, 'Thu hồi Laptop/Máy tính bàn, màn hình, phụ kiện sạc, chuột & tài sản tin học', 'IT', 10, 1),
(1, 'Vô hiệu hóa Email công ty (@posung.vn), tài khoản HRIS, ERP & thu hồi quyền truy cập hệ thống', 'IT', 20, 1),
(1, 'Sao lưu và bàn giao dữ liệu công việc lưu trữ trên Google Drive/OneDrive', 'IT', 30, 0),
(1, 'Thu hồi thẻ nhân viên, thẻ gửi xe, thẻ từ ra vào văn phòng / công trường POSUNG', 'Admin', 40, 1),
(1, 'Bàn giao chìa khóa phòng làm việc, tủ hồ sơ cá nhân, con dấu văn phòng (nếu có)', 'Admin', 50, 0),
(1, 'Thu hồi trang thiết bị bảo hộ lao động cá nhân (PPE: Mũ, áo phản quang, giày bảo hộ, dây an toàn)', 'HSE', 60, 1),
(1, 'Biên bản kiểm tra an toàn, xác nhận không còn vi phạm an toàn công trường hoặc khoản phạt tồn đọng', 'HSE', 70, 1),
(1, 'Quyết toán & hoàn ứng các khoản tạm ứng công tác, mua vật tư công trình chưa giải trình', 'Finance', 80, 1),
(1, 'Quyết toán lương tháng cuối, trợ cấp thôi việc, số ngày phép dư và các khoản nợ cá nhân', 'Finance', 90, 1),
(1, 'Chốt sổ Bảo hiểm Xã hội (BHXH, BHTN) và hoàn tất thủ tục báo giảm lao động', 'HR', 100, 1),
(1, 'Bàn giao hồ sơ gốc, ký Biên bản thanh lý HĐLĐ và trao Quyết định thôi việc chính thức', 'HR', 110, 1);

-- Tasks cho Template 2 (Nghỉ hưu)
INSERT INTO `offboarding_tasks` (`template_id`, `title`, `responsible_department`, `sort_order`, `is_blocking`) VALUES
(2, 'Thu hồi máy tính, thiết bị công nghệ và lưu trữ hồ sơ tài liệu làm việc bàn giao', 'IT', 10, 1),
(2, 'Vô hiệu hóa tài khoản hệ thống nội bộ', 'IT', 20, 1),
(2, 'Thu hồi thẻ nhân viên, chìa khóa và tài sản hành chính bàn giao', 'Admin', 30, 1),
(2, 'Bàn giao trang thiết bị bảo hộ lao động công trường (PPE)', 'HSE', 40, 1),
(2, 'Thanh toán chế độ hưu trí, trợ cấp thâm niên cống hiến và các khoản khen thưởng', 'Finance', 50, 1),
(2, 'Quyết toán lương tháng cuối và chi trả các khoản phúc lợi theo quy chế công ty', 'Finance', 60, 1),
(2, 'Hoàn tất thủ tục chốt sổ BHXH và hướng dẫn thủ tục nhận lương hưu hàng tháng', 'HR', 70, 1),
(2, 'Tổ chức buổi lễ tri ân, trao Kỷ niệm chương và Quyết định nghỉ hưu', 'HR', 80, 1);

-- Tasks cho Template 3 (Thử việc / Hết hạn HĐ)
INSERT INTO `offboarding_tasks` (`template_id`, `title`, `responsible_department`, `sort_order`, `is_blocking`) VALUES
(3, 'Thu hồi laptop/thiết bị IT và khóa tài khoản làm việc', 'IT', 10, 1),
(3, 'Thu hồi thẻ nhân viên và thẻ ra vào tòa nhà/công trường', 'Admin', 20, 1),
(3, 'Thu hồi đồ bảo hộ lao động cá nhân (PPE)', 'HSE', 30, 1),
(3, 'Quyết toán lương thời gian làm việc thực tế và công nợ (nếu có)', 'Finance', 40, 1),
(3, 'Thông báo kết quả thử việc / chấm dứt HĐLĐ và trả lại hồ sơ cá nhân gốc', 'HR', 50, 1);

-- ------------------------------------------------------------
-- 7. Cập nhật Menu Điều hướng hệ thống (system_menus)
-- ------------------------------------------------------------
INSERT INTO `system_menus` (`parent_id`, `title`, `url`, `icon`, `sort_order`, `is_active`, `permission_required`)
VALUES
(2, 'Quy trình Thôi việc (Offboarding)', 'offboarding', 'fas fa-user-minus', 48, 1, 'employee.view'),
(48, 'Lịch Nghỉ Lễ (Holiday Calendar)', 'holiday', 'fas fa-calendar-star', 52, 1, 'leave.view')
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `icon` = VALUES(`icon`);
