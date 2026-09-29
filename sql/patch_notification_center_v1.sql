-- ============================================================
-- POSUNG HRIS - SQL PATCH CHO NOTIFICATION CENTER (V1)
-- ============================================================

-- 1. Bảng lưu trữ thông báo người dùng
CREATE TABLE IF NOT EXISTS `notifications` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `type` ENUM('system', 'reminder', 'approval', 'alert') NOT NULL DEFAULT 'system',
    `title` VARCHAR(255) NOT NULL,
    `message` TEXT NOT NULL,
    `link` VARCHAR(255) NULL,
    `is_read` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `read_at` DATETIME NULL,
    INDEX `idx_user_read` (`user_id`, `is_read`),
    INDEX `idx_user_created` (`user_id`, `created_at`),
    INDEX `idx_type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Bảng cấu hình quy tắc thông báo tự động (Notification Rules)
CREATE TABLE IF NOT EXISTS `notification_rules` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `event_type` VARCHAR(100) NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `days_before` INT NOT NULL DEFAULT 0,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `template` TEXT NOT NULL,
    `target_role` VARCHAR(100) DEFAULT 'admin,hr_manager',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_event_active` (`event_type`, `is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Cài đặt các quy tắc cảnh báo mặc định theo yêu cầu hệ thống
INSERT INTO `notification_rules` (`event_type`, `name`, `days_before`, `is_active`, `template`, `target_role`) VALUES
('contract_expiry_30', 'Hợp đồng lao động hết hạn trước 30 ngày', 30, 1, 'Hợp đồng của nhân viên {employee_name} ({emp_code}) sẽ hết hạn vào ngày {expiry_date} (còn 30 ngày).', 'admin,hr_manager'),
('contract_expiry_15', 'Hợp đồng lao động hết hạn trước 15 ngày', 15, 1, 'Cảnh báo: Hợp đồng của nhân viên {employee_name} ({emp_code}) sẽ hết hạn vào ngày {expiry_date} (còn 15 ngày). Cần chuẩn bị ký gia hạn.', 'admin,hr_manager'),
('contract_expiry_7', 'Hợp đồng lao động hết hạn trước 7 ngày', 7, 1, 'Khẩn cấp: Hợp đồng của nhân viên {employee_name} ({emp_code}) chỉ còn 7 ngày hiệu lực (ngày hết hạn: {expiry_date}).', 'admin,hr_manager'),

('hse_cert_expiry_60', 'Thẻ An toàn HSE / Chứng chỉ hết hạn trước 60 ngày', 60, 1, 'Chứng chỉ/Thẻ HSE ({cert_name}) của nhân viên {employee_name} sẽ hết hạn vào ngày {expiry_date}.', 'admin,hr_manager,site_manager'),
('hse_cert_expiry_30', 'Thẻ An toàn HSE / Chứng chỉ hết hạn trước 30 ngày', 30, 1, 'Cảnh báo: Thẻ An toàn HSE ({cert_name}) của {employee_name} chỉ còn 30 ngày. Vui lòng sắp xếp đào tạo sát hạch gia hạn.', 'admin,hr_manager,site_manager'),

('visa_permit_expiry_90', 'Visa / Giấy phép lao động hết hạn trước 90 ngày', 90, 1, 'Giấy phép LĐ/Visa của chuyên gia {employee_name} ({emp_code}) sắp hết hạn vào ngày {expiry_date} (còn 90 ngày).', 'admin,hr_manager'),
('visa_permit_expiry_60', 'Visa / Giấy phép lao động hết hạn trước 60 ngày', 60, 1, 'Cảnh báo: Giấy phép LĐ/Visa của {employee_name} còn 60 ngày (ngày {expiry_date}). Cần nộp hồ sơ gia hạn lên Sở LĐ-TB&XH.', 'admin,hr_manager'),
('visa_permit_expiry_30', 'Visa / Giấy phép lao động hết hạn trước 30 ngày', 30, 1, 'Khẩn cấp: Giấy phép LĐ/Visa của {employee_name} chỉ còn 30 ngày. Nguy cơ vi phạm pháp lý lao động nước ngoài.', 'admin,hr_manager'),

('birthday_today', 'Chúc mừng sinh nhật nhân viên trong ngày', 0, 1, 'Hôm nay ({date}) là sinh nhật của {employee_name} ({dept_name}). Hãy gửi lời chúc mừng sinh nhật!', 'all'),
('probation_end_15', 'Đánh giá hết hạn thử việc trước 15 ngày', 15, 1, 'Nhân viên {employee_name} ({emp_code}) sắp hoàn thành thời gian thử việc vào ngày {expiry_date}. Cần làm thủ tục đánh giá.', 'admin,hr_manager,site_manager'),
('probation_end_7', 'Hạn ký hợp đồng chính thức sau thử việc trước 7 ngày', 7, 1, 'Khẩn cấp: Thời gian thử việc của {employee_name} kết thúc ngày {expiry_date} (còn 7 ngày). Cần chuẩn bị ký HĐLĐ chính thức.', 'admin,hr_manager'),

('leave_approval', 'Đơn xin nghỉ phép cần phê duyệt', 0, 1, 'Nhân viên {employee_name} vừa nộp đơn xin nghỉ phép từ {start_date} đến {end_date}. Vui lòng xem xét phê duyệt.', 'manager'),
('payroll_approved', 'Phiếu lương tháng đã được phê duyệt', 0, 1, 'Phiếu lương tháng {month}/{year} của bạn đã được duyệt và sẵn sàng tra cứu trên Cổng tự phục vụ ESS.', 'employee')
ON DUPLICATE KEY UPDATE `template` = VALUES(`template`);

-- 4. Thêm quyền notification vào bảng permissions và cấp cho Admin/HR
INSERT IGNORE INTO permissions (module_code, action_code, name, description) VALUES
('notification', 'view', 'Xem thông báo', 'Xem trung tâm thông báo và danh sách thông báo'),
('notification', 'settings', 'Cấu hình quy tắc thông báo', 'Quản lý các quy tắc cảnh báo tự động');

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 1, id FROM permissions WHERE module_code = 'notification';

-- 5. Tạo thông báo mẫu cho Admin và nhân viên
-- Cho Super Admin (ID 102)
INSERT INTO `notifications` (`user_id`, `type`, `title`, `message`, `link`, `is_read`, `created_at`) VALUES
(102, 'reminder', 'Hợp đồng lao động sắp hết hạn', 'Có 3 nhân viên công trường Amkor sắp hết hạn hợp đồng trong 30 ngày tới. Vui lòng kiểm tra.', 'contract', 0, NOW()),
(102, 'alert', 'Cảnh báo An toàn HSE', 'Phát hiện 2 thẻ an toàn lao động nhóm 3 sắp hết hạn trong 30 ngày tới tại Dự án Samsung.', 'hse/safetyCards', 0, NOW()),
(102, 'approval', 'Đơn nghỉ phép chờ duyệt', 'Nhân viên Nguyễn Văn Tổng vừa nộp đơn xin nghỉ phép 2 ngày đang chờ phê duyệt.', 'leave', 0, NOW()),
(102, 'system', 'Hệ thống hoàn tất sao lưu dữ liệu tự động', 'Bản sao lưu CSDL posung_hris định kỳ hàng tuần đã được tạo thành công.', 'backup', 1, DATE_SUB(NOW(), INTERVAL 1 DAY));

-- Cho Nhân viên test (nv001 - User ID 1110)
INSERT INTO `notifications` (`user_id`, `type`, `title`, `message`, `link`, `is_read`, `created_at`) VALUES
(1110, 'system', 'Chào mừng bạn đến với POSUNG ESS', 'Bạn có thể chủ động tra cứu bảng lương, chấm công và nộp đơn nghỉ phép trực tuyến.', 'ess/dashboard', 0, NOW()),
(1110, 'reminder', 'Nhắc nhở cập nhật thông tin người phụ thuộc', 'Vui lòng nộp đầy đủ hồ sơ chứng minh người phụ thuộc để được giảm trừ gia cảnh thuế TNCN.', 'ess/profile', 0, NOW()),
(1110, 'approval', 'Phiếu lương tháng 9/2026 đã sẵn sàng', 'Phiếu lương tháng 9/2026 của bạn đã được Phòng Kế toán & C&B phê duyệt.', 'ess/payslip', 0, NOW());
