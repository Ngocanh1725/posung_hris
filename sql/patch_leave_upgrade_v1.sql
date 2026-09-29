-- ==============================================================================
-- POSUNG HRIS - SQL PATCH: LEAVE UPGRADE & ALLOCATION (FRAPPE HRMS STANDARD)
-- File: patch_leave_upgrade_v1.sql
-- Description:
--   1. Tạo bảng leave_allocations quản lý quỹ phép theo nhân viên/năm
--   2. Bổ sung các chính sách cấu hình vào bảng leave_types
--   3. Tạo bảng leave_holidays quản lý lịch nghỉ lễ Quốc gia và Công ty
--   4. Cập nhật bảng leave_requests (thêm approver_note nếu chưa có)
--   5. Seed dữ liệu ngày lễ Quốc gia & Công ty năm 2026
--   6. Seed quyền hạn phân hệ Nghỉ phép (Leave Management)
-- ==============================================================================

USE `posung_hris`;

-- 1. BẢNG LEAVE_ALLOCATIONS (Quỹ phép phân bổ)
CREATE TABLE IF NOT EXISTS `leave_allocations` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `employee_id` INT NOT NULL,
  `leave_type_id` INT NOT NULL,
  `year` INT NOT NULL,
  `entitled_days` DECIMAL(5,1) NOT NULL DEFAULT 0.0 COMMENT 'Số ngày được hưởng theo tiêu chuẩn + thâm niên',
  `carried_forward_days` DECIMAL(5,1) NOT NULL DEFAULT 0.0 COMMENT 'Số ngày chuyển từ năm trước sang',
  `used_days` DECIMAL(5,1) NOT NULL DEFAULT 0.0 COMMENT 'Số ngày đã nghỉ (được duyệt)',
  `remaining_days` DECIMAL(5,1) NOT NULL DEFAULT 0.0 COMMENT 'Số ngày phép khả dụng còn lại',
  `effective_from` DATE NULL COMMENT 'Ngày bắt đầu có hiệu lực',
  `effective_to` DATE NULL COMMENT 'Hạn chót sử dụng phép (đặc biệt phép tồn)',
  `created_by` INT NULL COMMENT 'Người tạo / HR phê duyệt phân bổ',
  `notes` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uk_emp_type_year` (`employee_id`, `leave_type_id`, `year`),
  KEY `idx_emp_year` (`employee_id`, `year`),
  KEY `idx_leave_type` (`leave_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. BỔ SUNG CỘT CẤU HÌNH VÀO LEAVE_TYPES
SET @dbname = DATABASE();
SET @tablename = 'leave_types';

-- max_carry_forward
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = 'max_carry_forward') > 0,
  "SELECT 1",
  "ALTER TABLE `leave_types` ADD COLUMN `max_carry_forward` DECIMAL(5,1) NOT NULL DEFAULT 5.0 COMMENT 'Số ngày phép tối đa được chuyển sang năm sau' AFTER `max_days_per_year`"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- carry_forward_expiry_months
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = 'carry_forward_expiry_months') > 0,
  "SELECT 1",
  "ALTER TABLE `leave_types` ADD COLUMN `carry_forward_expiry_months` INT NOT NULL DEFAULT 3 COMMENT 'Hạn chót dùng phép năm cũ (ví dụ 3 tháng = hết ngày 31/03)' AFTER `max_carry_forward`"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- allow_negative
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = 'allow_negative') > 0,
  "SELECT 1",
  "ALTER TABLE `leave_types` ADD COLUMN `allow_negative` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Cho phép ứng trước phép âm hay không' AFTER `carry_forward_expiry_months`"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- proration_enabled
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = 'proration_enabled') > 0,
  "SELECT 1",
  "ALTER TABLE `leave_types` ADD COLUMN `proration_enabled` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Tự động tính tỷ lệ số tháng làm việc trong năm' AFTER `allow_negative`"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- max_continuous_days
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = 'max_continuous_days') > 0,
  "SELECT 1",
  "ALTER TABLE `leave_types` ADD COLUMN `max_continuous_days` INT NOT NULL DEFAULT 5 COMMENT 'Số ngày nghỉ liên tục tối đa không cần TGĐ duyệt (0: không giới hạn)' AFTER `proration_enabled`"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Cập nhật mặc định cho Loại Nghỉ Phép Năm (ID = 1)
UPDATE `leave_types` 
SET `max_carry_forward` = 5.0, 
    `carry_forward_expiry_months` = 3, 
    `allow_negative` = 0, 
    `proration_enabled` = 1, 
    `max_continuous_days` = 5 
WHERE `id` = 1;

-- Cập nhật cho Loại Nghỉ Không Lương (ID = 3)
UPDATE `leave_types` 
SET `max_carry_forward` = 0.0, 
    `carry_forward_expiry_months` = 0, 
    `allow_negative` = 1, 
    `proration_enabled` = 0, 
    `max_continuous_days` = 30 
WHERE `id` = 3;

-- 3. BẢNG LEAVE_HOLIDAYS (Lịch nghỉ Lễ / Tết / Ngày kỷ niệm Công ty)
CREATE TABLE IF NOT EXISTS `leave_holidays` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(150) NOT NULL COMMENT 'Tên ngày lễ',
  `date` DATE NOT NULL COMMENT 'Ngày nghỉ',
  `type` ENUM('National', 'Company') NOT NULL DEFAULT 'National' COMMENT 'Phân loại lễ Quốc gia hoặc Lễ nội bộ công ty',
  `is_recurring` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Lặp lại hàng năm theo Dương lịch',
  `applies_to_department_id` INT NULL COMMENT 'Phòng ban áp dụng (NULL: Toàn công ty)',
  `description` TEXT NULL COMMENT 'Mô tả chi tiết',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uk_holiday_date_name` (`date`, `name`),
  KEY `idx_holiday_date` (`date`),
  KEY `idx_holiday_type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. BỔ SUNG CỘT APPROVER_NOTE VÀO LEAVE_REQUESTS (Nếu chưa có)
SET @tablename_lr = 'leave_requests';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename_lr AND COLUMN_NAME = 'approver_note') > 0,
  "SELECT 1",
  "ALTER TABLE `leave_requests` ADD COLUMN `approver_note` TEXT NULL COMMENT 'Ghi chú phê duyệt hoặc lý do từ chối' AFTER `approved_at`"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- 5. SEED DỮ LIỆU LỊCH NGHỈ LỄ NĂM 2026
INSERT IGNORE INTO `leave_holidays` (`name`, `date`, `type`, `is_recurring`, `description`) VALUES
('Tết Dương Lịch 2026', '2026-01-01', 'National', 1, 'Nghỉ Tết Dương Lịch theo Bộ luật Lao động'),
('Tết Nguyên Đán 2026 (28 Tết)', '2026-02-15', 'National', 0, 'Kỳ nghỉ Tết Nguyên Đán Bính Ngọ 2026'),
('Tết Nguyên Đán 2026 (29 Tết)', '2026-02-16', 'National', 0, 'Kỳ nghỉ Tết Nguyên Đán Bính Ngọ 2026'),
('Tết Nguyên Đán 2026 (Mùng 1)', '2026-02-17', 'National', 0, 'Kỳ nghỉ Tết Nguyên Đán Bính Ngọ 2026'),
('Tết Nguyên Đán 2026 (Mùng 2)', '2026-02-18', 'National', 0, 'Kỳ nghỉ Tết Nguyên Đán Bính Ngọ 2026'),
('Tết Nguyên Đán 2026 (Mùng 3)', '2026-02-19', 'National', 0, 'Kỳ nghỉ Tết Nguyên Đán Bính Ngọ 2026'),
('Tết Nguyên Đán 2026 (Mùng 4)', '2026-02-20', 'National', 0, 'Kỳ nghỉ Tết Nguyên Đán Bính Ngọ 2026'),
('Tết Nguyên Đán 2026 (Mùng 5)', '2026-02-21', 'National', 0, 'Kỳ nghỉ Tết Nguyên Đán Bính Ngọ 2026'),
('Giỗ Tổ Hùng Vương (10/3 ÂL)', '2026-04-26', 'National', 0, 'Lễ Giỗ tổ Hùng Vương (Chủ Nhật nghỉ bù 27/04)'),
('Nghỉ bù Giỗ Tổ Hùng Vương', '2026-04-27', 'National', 0, 'Nghỉ bù Giỗ tổ Hùng Vương rơi vào Chủ Nhật'),
('Ngày Giải phóng Miền Nam', '2026-04-30', 'National', 1, 'Kỷ niệm ngày Giải phóng miền Nam 30/4'),
('Ngày Quốc tế Lao động', '2026-05-01', 'National', 1, 'Kỷ niệm ngày Quốc tế Lao động 1/5'),
('Ngày Thành Lập POSUNG E&C', '2026-08-18', 'Company', 1, 'Ngày truyền thống thành lập Tập đoàn Xây dựng POSUNG'),
('Quốc khánh Việt Nam', '2026-09-02', 'National', 1, 'Kỷ niệm Quốc khánh nước CHXHCN Việt Nam'),
('Nghỉ liền kề Quốc khánh', '2026-09-03', 'National', 0, 'Nghỉ liền kề ngày Quốc khánh theo quy định của Chính phủ');

-- 6. SEED QUYỀN HẠN PHÂN HỆ NGHỈ PHÉP
INSERT IGNORE INTO `permissions` (`module_code`, `action_code`, `name`, `description`) VALUES
('leave', 'view', 'Xem Nghỉ phép', 'Xem danh sách và số dư phép của nhân viên'),
('leave', 'create', 'Nộp đơn nghỉ phép', 'Tạo đơn xin nghỉ phép cá nhân'),
('leave', 'approve', 'Phê duyệt phép', 'Phê duyệt hoặc từ chối đơn xin phép của cấp dưới'),
('leave', 'allocations', 'Quản lý quỹ phép', 'Phân bổ phép tự động, chuyển phép tồn theo năm'),
('leave', 'holidays', 'Quản lý lịch nghỉ lễ', 'Thêm mới, sửa, xóa danh mục ngày nghỉ lễ Tết');

-- 7. TỰ ĐỘNG KHỞI TẠO QUỸ PHÉP NĂM 2026 CHO TẤT CẢ NHÂN VIÊN ĐANG HOẠT ĐỘNG
-- Công thức chuẩn Việt Nam (BLLĐ 2019 Điều 114):
-- - Phép cơ bản: 12 ngày/năm
-- - Thâm niên: Mỗi 5 năm làm việc tính đến 2026 được cộng thêm 1 ngày
-- - Tỷ lệ tháng làm việc nếu vào làm trong năm 2026: (12 - MONTH(join_date) + 1)
INSERT INTO `leave_allocations` 
  (`employee_id`, `leave_type_id`, `year`, `entitled_days`, `carried_forward_days`, `used_days`, `remaining_days`, `effective_from`, `effective_to`, `created_by`, `notes`)
SELECT 
  e.id AS employee_id,
  1 AS leave_type_id,
  2026 AS `year`,
  -- Tính entitled_days (Cơ bản + Thâm niên hoặc Proration)
  CASE 
    WHEN e.join_date IS NULL THEN 12.0
    WHEN YEAR(e.join_date) = 2026 THEN ROUND(((12 - MONTH(e.join_date) + 1) / 12.0) * 12.0, 1)
    WHEN YEAR(e.join_date) < 2026 THEN 12.0 + FLOOR(TIMESTAMPDIFF(YEAR, e.join_date, '2026-01-01') / 5)
    ELSE 12.0
  END AS entitled_days,
  0.0 AS carried_forward_days,
  -- Lấy used_days từ các đơn đã được duyệt trong năm 2026
  COALESCE((
    SELECT SUM(lr.total_days)
    FROM `leave_requests` lr
    WHERE lr.employee_id = e.id 
      AND lr.leave_type_id = 1 
      AND lr.status = 'Approved' 
      AND YEAR(lr.start_date) = 2026
  ), 0.0) AS used_days,
  -- remaining_days
  (
    CASE 
      WHEN e.join_date IS NULL THEN 12.0
      WHEN YEAR(e.join_date) = 2026 THEN ROUND(((12 - MONTH(e.join_date) + 1) / 12.0) * 12.0, 1)
      WHEN YEAR(e.join_date) < 2026 THEN 12.0 + FLOOR(TIMESTAMPDIFF(YEAR, e.join_date, '2026-01-01') / 5)
      ELSE 12.0
    END
    - 
    COALESCE((
      SELECT SUM(lr.total_days)
      FROM `leave_requests` lr
      WHERE lr.employee_id = e.id 
        AND lr.leave_type_id = 1 
        AND lr.status = 'Approved' 
        AND YEAR(lr.start_date) = 2026
    ), 0.0)
  ) AS remaining_days,
  '2026-01-01' AS effective_from,
  '2026-12-31' AS effective_to,
  1 AS created_by,
  'Khởi tạo tự động quỹ phép năm 2026 theo Frappe HRMS standard' AS notes
FROM `employees` e
WHERE e.status = 'Active'
ON DUPLICATE KEY UPDATE 
  `entitled_days` = VALUES(`entitled_days`),
  `remaining_days` = VALUES(`entitled_days`) + `carried_forward_days` - `used_days`;
