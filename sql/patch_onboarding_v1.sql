-- ============================================================
-- POSUNG HRIS - Patch Onboarding V1
-- Module Quy trình Hội nhập Nhân sự mới (Employee Onboarding)
-- ============================================================

-- 1. Bảng onboarding_templates: Mẫu quy trình hội nhập
CREATE TABLE IF NOT EXISTS `onboarding_templates` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `department_id` INT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_dept` (`department_id`),
    INDEX `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Bảng onboarding_tasks: Nhiệm vụ chi tiết trong mẫu hội nhập
CREATE TABLE IF NOT EXISTS `onboarding_tasks` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `template_id` INT NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `responsible_department` VARCHAR(50) NOT NULL COMMENT 'IT, HR, Admin, Finance, HSE',
    `due_days_after_join` INT DEFAULT 3 COMMENT 'Hạn hoàn thành sau số ngày nhận việc',
    `sort_order` INT DEFAULT 0,
    `is_required` TINYINT(1) DEFAULT 1,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_template` (`template_id`),
    INDEX `idx_resp_dept` (`responsible_department`),
    CONSTRAINT `fk_task_template` FOREIGN KEY (`template_id`) REFERENCES `onboarding_templates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Bảng employee_onboardings: Phiên hội nhập của từng nhân viên
CREATE TABLE IF NOT EXISTS `employee_onboardings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `employee_id` INT NOT NULL,
    `template_id` INT NOT NULL,
    `start_date` DATE NOT NULL,
    `status` ENUM('InProgress', 'Completed', 'Overdue') DEFAULT 'InProgress',
    `completed_at` DATETIME NULL,
    `notes` TEXT NULL,
    `created_by` INT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_emp` (`employee_id`),
    INDEX `idx_status` (`status`),
    CONSTRAINT `fk_onb_emp` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_onb_tpl` FOREIGN KEY (`template_id`) REFERENCES `onboarding_templates` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Bảng employee_onboarding_items: Checklist từng task của nhân viên
CREATE TABLE IF NOT EXISTS `employee_onboarding_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `onboarding_id` INT NOT NULL,
    `task_id` INT NOT NULL,
    `status` ENUM('Pending', 'InProgress', 'Done', 'Skipped') DEFAULT 'Pending',
    `assigned_to` INT NULL COMMENT 'Người phụ trách xử lý',
    `due_date` DATE NULL,
    `completed_at` DATETIME NULL,
    `notes` TEXT NULL,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_onb` (`onboarding_id`),
    INDEX `idx_task` (`task_id`),
    INDEX `idx_item_status` (`status`),
    CONSTRAINT `fk_item_onb` FOREIGN KEY (`onboarding_id`) REFERENCES `employee_onboardings` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_item_task` FOREIGN KEY (`task_id`) REFERENCES `onboarding_tasks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Cấu hình quyền hạn (Permissions)
INSERT IGNORE INTO `permissions` (`module_code`, `action_code`, `name`, `description`) VALUES
('onboarding', 'view',   'Xem hội nhập', 'Xem danh sách & checklist hội nhập nhân viên'),
('onboarding', 'manage', 'Quản lý hội nhập', 'Tạo và quản lý mẫu quy trình hội nhập'),
('onboarding', 'update', 'Cập nhật task', 'Cập nhật trạng thái hoàn thành task hội nhập');

-- Gán quyền cho vai trò Admin (id=1)
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 1, id FROM `permissions` WHERE `module_code` = 'onboarding';

-- 6. Dữ liệu mẫu (Seed Data) cho POSUNG Construction
-- Template 1: Khối Văn phòng (Trụ sở POSUNG)
INSERT INTO `onboarding_templates` (`id`, `name`, `description`, `department_id`, `is_active`) VALUES
(1, 'Hội nhập Khối Văn phòng Trụ sở (HQ Staff)', 'Quy trình chuẩn dành cho chuyên viên, nhân viên khối phòng ban văn phòng', 2, 1)
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);

-- Tasks Template 1
INSERT INTO `onboarding_tasks` (`template_id`, `title`, `description`, `responsible_department`, `due_days_after_join`, `sort_order`, `is_required`) VALUES
(1, 'Cấp email doanh nghiệp @posung.com', 'Tạo hộp thư công vụ và phân quyền nhóm liên lạc nội bộ', 'IT', 1, 1, 1),
(1, 'Bàn giao Laptop / PC làm việc', 'Kiểm tra cấu hình, dán tem tài sản và lập biên bản bàn giao máy tính', 'IT', 1, 2, 1),
(1, 'Cài đặt tài khoản POSUNG HRIS & VPN', 'Cấp tài khoản đăng nhập portal HRIS và cấu hình truy cập mạng an toàn', 'IT', 2, 3, 1),
(1, 'Ký kết Hợp đồng Thử việc / Lao động', 'Hoàn tất thủ tục pháp lý hợp đồng và lưu trữ hồ sơ nhân sự', 'HR', 1, 4, 1),
(1, 'Tiếp nhận hồ sơ nhân sự & Đăng ký BHXH', 'Kiểm tra CCCD, văn bằng gốc, đăng ký mã định danh bảo hiểm xã hội', 'HR', 3, 5, 1),
(1, 'Chụp ảnh thẻ & Giới thiệu văn hóa công ty', 'Chụp ảnh hồ sơ, gửi sổ tay nhân viên và phổ biến nội quy lao động', 'HR', 1, 6, 1),
(1, 'Cấp thẻ từ ra vào văn phòng & Đăng ký xe', 'Khai báo thẻ kiểm soát an ninh cửa ra vào và vé gửi xe tầng hầm', 'Admin', 1, 7, 1),
(1, 'Bố trí chỗ ngồi & Cấp phát văn phòng phẩm', 'Chuẩn bị bàn làm việc, thẻ tên và các dụng cụ phục vụ công việc', 'Admin', 1, 8, 1),
(1, 'Mở tài khoản ngân hàng nhận lương', 'Thu thập STK Vietcombank/BIDV để chi trả lương định kỳ', 'Finance', 3, 9, 1),
(1, 'Đăng ký Mã số thuế & Giảm trừ gia cảnh', 'Khai báo MST cá nhân và hồ sơ người phụ thuộc lên cơ quan thuế', 'Finance', 7, 10, 0);

-- Template 2: Kỹ sư Ban QLDA Công trường (Site Engineer / PMB)
INSERT INTO `onboarding_templates` (`id`, `name`, `description`, `department_id`, `is_active`) VALUES
(2, 'Hội nhập Kỹ sư Giám sát & Ban QLDA Công trường', 'Quy trình hội nhập công trình xây dựng, bắt buộc chứng chỉ an toàn & trang bị PPE', 4, 1)
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);

-- Tasks Template 2
INSERT INTO `onboarding_tasks` (`template_id`, `title`, `description`, `responsible_department`, `due_days_after_join`, `sort_order`, `is_required`) VALUES
(2, 'Huấn luyện An toàn Lao động (Nhóm 1-6)', 'Đào tạo quy chuẩn an toàn công trình xây dựng và kiểm tra sát hạch đầu vào', 'HSE', 1, 1, 1),
(2, 'Khám sức khỏe đầu vào đủ điều kiện công trường', 'Kiểm tra thị lực, huyết áp và chứng nhận đủ điều kiện làm việc trên cao', 'HSE', 2, 2, 1),
(2, 'Cấp phát đầy đủ bảo hộ cá nhân (PPE)', 'Cấp mũ bảo hộ loại A, giày mũi thép, áo phản quang, dây an toàn 2 móc', 'HSE', 1, 3, 1),
(2, 'Cấp Thẻ An toàn Công trường (HSE Safety Card)', 'Cấp thẻ ra vào công trường thi công sau khi hoàn tất kiểm tra an toàn', 'HSE', 2, 4, 1),
(2, 'Ký Hợp đồng & Quyết định điều động dự án', 'Ký hợp đồng lao động và bàn giao quyết định bổ nhiệm vào Ban QLDA', 'HR', 1, 5, 1),
(2, 'Phổ biến quy chế công tác & phụ cấp lưu trú', 'Hướng dẫn chế độ công tác phí, nhà ở công trường và phụ cấp trách nhiệm', 'HR', 2, 6, 1),
(2, 'Cấp tài khoản BIM / Phần mềm Quản lý Dự án', 'Cấp quyền truy cập bản vẽ CAD, tài liệu kỹ thuật và báo cáo tiến độ', 'IT', 2, 7, 1),
(2, 'Cài đặt app Chấm công GPS công trình', 'Hướng dẫn điểm danh chấm công qua nhận diện khuôn mặt và vị trí GPS dự án', 'IT', 1, 8, 1),
(2, 'Bố trí chỗ ở Ký túc xá công trình', 'Bàn giao phòng lưu trú, trang thiết bị sinh hoạt tại ban chỉ huy công trường', 'Admin', 2, 9, 1),
(2, 'Cấp tạm ứng chi phí công trường ban đầu', 'Tạm ứng quỹ công việc phục vụ khảo sát và điều hành hiện trường', 'Finance', 3, 10, 0);

-- Thêm menu Onboarding vào sidebar
INSERT IGNORE INTO `system_menus` (`parent_id`, `title`, `url`, `icon`, `sort_order`, `is_active`, `permission_required`)
VALUES (2, 'Quy trình Hội nhập (Onboarding)', 'onboarding', 'fas fa-user-check', 26, 1, 'onboarding.view');

-- Khởi tạo thử 1 bản ghi onboarding cho nhân viên mới
INSERT INTO `employee_onboardings` (`id`, `employee_id`, `template_id`, `start_date`, `status`, `notes`) VALUES
(1, 1, 1, CURRENT_DATE, 'InProgress', 'Hội nhập nhân viên quản lý khối văn phòng')
ON DUPLICATE KEY UPDATE `id`=`id`;

-- Nhân bản tasks cho onboarding 1
INSERT IGNORE INTO `employee_onboarding_items` (`onboarding_id`, `task_id`, `status`, `due_date`, `notes`)
SELECT 1, t.id, 
       IF(t.sort_order <= 3, 'Done', 'Pending'),
       DATE_ADD(CURRENT_DATE, INTERVAL t.due_days_after_join DAY),
       IF(t.sort_order <= 3, 'Đã hoàn tất theo biên bản bàn giao ban đầu', NULL)
FROM `onboarding_tasks` t
WHERE t.template_id = 1;
