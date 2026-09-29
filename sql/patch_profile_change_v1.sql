-- ============================================================
-- POSUNG HRIS - SQL PATCH: PROFILE CHANGE REQUEST & APPROVAL ENGINE
-- File: sql/patch_profile_change_v1.sql
-- ============================================================

-- 1. Bảng lưu trữ Yêu cầu phê duyệt chỉnh sửa thông tin hồ sơ nhân viên
CREATE TABLE IF NOT EXISTS `profile_change_requests` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `employee_id` INT NOT NULL COMMENT 'ID nhân viên yêu cầu thay đổi',
    `requested_by` INT NOT NULL COMMENT 'User ID người gửi yêu cầu',
    `request_type` ENUM('personal_info', 'contact', 'bank', 'dependents', 'education') NOT NULL DEFAULT 'personal_info' COMMENT 'Nhóm thông tin thay đổi',
    `field_name` VARCHAR(100) NOT NULL COMMENT 'Tên trường CSDL thay đổi',
    `field_label` VARCHAR(150) NULL COMMENT 'Tên nhãn hiển thị tiếng Việt',
    `old_value` TEXT NULL COMMENT 'Giá trị hiện tại',
    `new_value` TEXT NULL COMMENT 'Giá trị mới đề xuất',
    `status` ENUM('Pending', 'Approved', 'Rejected') NOT NULL DEFAULT 'Pending' COMMENT 'Trạng thái phê duyệt',
    `reviewed_by` INT NULL COMMENT 'User ID người phê duyệt',
    `reviewed_at` DATETIME NULL COMMENT 'Thời gian phê duyệt',
    `notes` TEXT NULL COMMENT 'Ghi chú / Lý do từ chối',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_pcr_emp` (`employee_id`),
    INDEX `idx_pcr_status` (`status`),
    INDEX `idx_pcr_type` (`request_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Yêu cầu phê duyệt chỉnh sửa thông tin hồ sơ nhân viên';

-- 2. Bảng cấu hình quy trình phê duyệt đa cấp theo Module (Approval Flows)
CREATE TABLE IF NOT EXISTS `approval_flows` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(150) NOT NULL COMMENT 'Tên quy trình duyệt',
    `module` VARCHAR(50) NOT NULL COMMENT 'leave, loan, expense, transfer, profile_change',
    `description` TEXT NULL COMMENT 'Mô tả chi tiết quy trình',
    `steps` JSON NOT NULL COMMENT 'Các bước duyệt: [{"level":1,"role":"direct_manager","role_name":"Quản lý trực tiếp","required_count":1}]',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Trạng thái hoạt động (1: Hoạt động, 0: Khóa)',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_af_module` (`module`),
    INDEX `idx_af_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Cấu hình quy trình phê duyệt đa cấp theo Module';

-- 3. Bảng theo dõi các Phiếu yêu cầu phê duyệt (Approval Requests)
CREATE TABLE IF NOT EXISTS `approval_requests` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `flow_id` INT NULL COMMENT 'ID quy trình phê duyệt áp dụng',
    `module` VARCHAR(50) NOT NULL COMMENT 'leave, loan, expense, transfer, profile_change',
    `record_id` INT NOT NULL COMMENT 'ID bản ghi tương ứng trong bảng nghiệp vụ',
    `current_level` INT NOT NULL DEFAULT 1 COMMENT 'Cấp duyệt hiện tại đang chờ',
    `status` ENUM('Pending', 'Approved', 'Rejected', 'Cancelled') NOT NULL DEFAULT 'Pending' COMMENT 'Trạng thái tổng thể',
    `created_by` INT NULL COMMENT 'User ID người tạo yêu cầu',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_ar_mod_rec` (`module`, `record_id`),
    INDEX `idx_ar_status` (`status`),
    INDEX `idx_ar_flow` (`flow_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Phiếu yêu cầu phê duyệt trong quy trình Approval Engine';

-- 4. Bảng ghi nhận Lịch sử các bước phê duyệt (Approval Actions)
CREATE TABLE IF NOT EXISTS `approval_actions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `request_id` INT NOT NULL COMMENT 'Liên kết approval_requests.id',
    `level` INT NOT NULL COMMENT 'Cấp duyệt thực hiện hành động',
    `action` ENUM('approve', 'reject', 'return') NOT NULL COMMENT 'Hành động: Duyệt, Từ chối, Trả về',
    `user_id` INT NOT NULL COMMENT 'User ID người thực hiện',
    `comments` TEXT NULL COMMENT 'Ý kiến / Ghi chú phê duyệt',
    `acted_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Thời gian thực hiện',
    INDEX `idx_aa_req` (`request_id`),
    INDEX `idx_aa_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Nhật ký lịch sử các bước phê duyệt';

-- 5. Seed dữ liệu mặc định cho Approval Flows nếu chưa có
INSERT INTO `approval_flows` (`name`, `module`, `description`, `steps`, `is_active`)
SELECT 'Quy trình Phê duyệt Nghỉ phép', 'leave', 'Duyệt đơn xin nghỉ phép năm, nghỉ ốm, việc riêng', 
       '[{"level":1,"role":"direct_manager","role_name":"Quản lý trực tiếp","required_count":1},{"level":2,"role":"hr_admin","role_name":"Phòng Nhân sự (HR)","required_count":1}]', 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `approval_flows` WHERE `module` = 'leave');

INSERT INTO `approval_flows` (`name`, `module`, `description`, `steps`, `is_active`)
SELECT 'Quy trình Đề nghị Vay vốn & Tạm ứng', 'loan', 'Duyệt các khoản vay phúc lợi, tạm ứng lương dài hạn', 
       '[{"level":1,"role":"dept_head","role_name":"Trưởng bộ phận","required_count":1},{"level":2,"role":"accountant","role_name":"Kế toán trưởng","required_count":1},{"level":3,"role":"director","role_name":"Ban Giám đốc","required_count":1}]', 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `approval_flows` WHERE `module` = 'loan');

INSERT INTO `approval_flows` (`name`, `module`, `description`, `steps`, `is_active`)
SELECT 'Quy trình Thanh toán Chi phí & Công tác phí', 'expense', 'Duyệt thanh toán hóa đơn công tác, chi phí hiện trường', 
       '[{"level":1,"role":"site_manager","role_name":"Chỉ huy trưởng / QLDA","required_count":1},{"level":2,"role":"accountant","role_name":"Kế toán thanh toán","required_count":1},{"level":3,"role":"director","role_name":"Giám đốc điều hành","required_count":1}]', 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `approval_flows` WHERE `module` = 'expense');

INSERT INTO `approval_flows` (`name`, `module`, `description`, `steps`, `is_active`)
SELECT 'Quy trình Điều chuyển Nhân sự công trường', 'transfer', 'Duyệt lệnh điều chuyển nhân sự giữa các dự án / site', 
       '[{"level":1,"role":"site_manager_from","role_name":"Chỉ huy trưởng Site chuyển đi","required_count":1},{"level":2,"role":"site_manager_to","role_name":"Chỉ huy trưởng Site tiếp nhận","required_count":1},{"level":3,"role":"hr_admin","role_name":"Trưởng phòng Nhân sự","required_count":1}]', 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `approval_flows` WHERE `module` = 'transfer');

INSERT INTO `approval_flows` (`name`, `module`, `description`, `steps`, `is_active`)
SELECT 'Quy trình Duyệt thay đổi Hồ sơ cá nhân', 'profile_change', 'Duyệt thay đổi thông tin nhạy cảm: CCCD, Ngân hàng, Hộ khẩu', 
       '[{"level":1,"role":"hr_admin","role_name":"Chuyên viên Nhân sự C&B","required_count":1},{"level":2,"role":"hr_manager","role_name":"Trưởng phòng Nhân sự","required_count":1}]', 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `approval_flows` WHERE `module` = 'profile_change');

-- 6. Đăng ký quyền hệ thống cho module Workflow nếu chưa có
INSERT INTO `system_permissions` (`module_id`, `code`, `name`, `action_name`, `description`)
SELECT 1, 'workflow.view', 'Xem Hộp duyệt & Luồng phê duyệt', 'view', 'Xem danh sách phê duyệt tập trung và cấu hình luồng'
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `system_permissions` WHERE `code` = 'workflow.view');

INSERT INTO `system_permissions` (`module_id`, `code`, `name`, `action_name`, `description`)
SELECT 1, 'workflow.approve', 'Thực hiện phê duyệt yêu cầu', 'approve', 'Phê duyệt hoặc từ chối các yêu cầu trong workflow'
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `system_permissions` WHERE `code` = 'workflow.approve');

INSERT INTO `system_permissions` (`module_id`, `code`, `name`, `action_name`, `description`)
SELECT 1, 'workflow.manage', 'Cấu hình quy trình phê duyệt', 'manage', 'Tạo, sửa, xóa các luồng phê duyệt'
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `system_permissions` WHERE `code` = 'workflow.manage');
