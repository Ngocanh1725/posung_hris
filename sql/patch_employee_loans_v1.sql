-- ============================================================
-- POSUNG HRIS - PATCH EMPLOYEE LOANS & ADVANCES V1
-- Module Quản lý Tạm ứng & Khoản vay Nhân viên
-- ============================================================

-- 1. Bảng loại khoản vay / tạm ứng
CREATE TABLE IF NOT EXISTS `loan_types` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `type_code` VARCHAR(50) NOT NULL UNIQUE,
    `name` VARCHAR(150) NOT NULL,
    `max_amount` DECIMAL(15,2) DEFAULT NULL,
    `max_term_months` INT DEFAULT 12,
    `interest_rate` DECIMAL(5,2) DEFAULT 0.00,
    `description` TEXT DEFAULT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Bảng khoản vay / tạm ứng của nhân viên
CREATE TABLE IF NOT EXISTS `employee_loans` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `loan_code` VARCHAR(50) NOT NULL UNIQUE,
    `employee_id` INT NOT NULL,
    `loan_type_id` INT NOT NULL,
    `amount` DECIMAL(15,2) NOT NULL,
    `interest_rate` DECIMAL(5,2) DEFAULT 0.00,
    `term_months` INT NOT NULL DEFAULT 1,
    `monthly_emi` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `total_repayment` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `total_paid` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `remaining_balance` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `status` ENUM('Pending', 'Approved', 'Rejected', 'Active', 'Closed', 'Defaulted') NOT NULL DEFAULT 'Pending',
    `applied_date` DATE NOT NULL,
    `disbursement_date` DATE DEFAULT NULL,
    `approved_by` INT DEFAULT NULL,
    `approved_date` DATETIME DEFAULT NULL,
    `rejected_reason` TEXT DEFAULT NULL,
    `reason` TEXT NOT NULL,
    `disbursement_method` VARCHAR(50) DEFAULT 'Bank Transfer',
    `notes` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_employee_loans_emp` FOREIGN KEY (`employee_id`) REFERENCES `employees`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_employee_loans_type` FOREIGN KEY (`loan_type_id`) REFERENCES `loan_types`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Bảng lịch sử trả nợ / hoàn ứng
CREATE TABLE IF NOT EXISTS `loan_repayments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `loan_id` INT NOT NULL,
    `payroll_id` INT DEFAULT NULL,
    `amount` DECIMAL(15,2) NOT NULL,
    `principal_amount` DECIMAL(15,2) DEFAULT 0.00,
    `interest_amount` DECIMAL(15,2) DEFAULT 0.00,
    `balance_after` DECIMAL(15,2) NOT NULL,
    `payment_date` DATE NOT NULL,
    `payment_method` VARCHAR(50) DEFAULT 'Payroll Deduction',
    `payment_type` ENUM('Auto', 'Manual') DEFAULT 'Manual',
    `recorded_by` INT DEFAULT NULL,
    `notes` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_loan_repayments_loan` FOREIGN KEY (`loan_id`) REFERENCES `employee_loans`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Bổ sung cột loan_deduction trong payroll_details nếu chưa có
SET @dbname = DATABASE();
SET @tablename = "payroll_details";
SET @columnname = "loan_deduction";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_NAME = @tablename)
      AND (TABLE_SCHEMA = @dbname)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  "SELECT 1",
  "ALTER TABLE payroll_details ADD COLUMN loan_deduction DECIMAL(15,2) DEFAULT 0.00 AFTER insurance_deduction;"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- 5. Seed dữ liệu danh mục loại khoản vay (loan_types)
INSERT INTO `loan_types` (`type_code`, `name`, `max_amount`, `max_term_months`, `interest_rate`, `description`, `is_active`)
VALUES
('ADV_SALARY', 'Tạm ứng lương định kỳ', 15000000.00, 3, 0.00, 'Tạm ứng lương chi tiêu khẩn cấp giữa tháng hoặc trước kỳ lương, hoàn trả trực tiếp qua khấu trừ lương 1-3 tháng, lãi suất 0%.', 1),
('WELFARE_AID', 'Vay phúc lợi công đoàn', 50000000.00, 12, 0.00, 'Hỗ trợ đoàn viên công đoàn gặp hoàn cảnh gia đình khó khăn đột xuất, ốm đau hoặc thiên tai, kỳ hạn tới 12 tháng không lãi suất.', 1),
('DEVICE_PURCHASE', 'Vay mua thiết bị / Phương tiện', 40000000.00, 24, 3.50, 'Chương trình trợ giá cán bộ công nhân viên mua xe máy, laptop hoặc thiết bị làm việc công trình với lãi suất ưu đãi 3.5%/năm.', 1),
('EMERGENCY_LOAN', 'Khoản vay cá nhân khẩn cấp', 30000000.00, 6, 5.00, 'Khoản vay đặc thù phê duyệt nhanh trong 24h phục vụ công tác đối ngoại hoặc sự cố cá nhân đột xuất.', 1)
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `description`=VALUES(`description`);

-- 6. Cập nhật bảng permissions
INSERT INTO `permissions` (`module_code`, `action_code`, `name`, `description`)
VALUES
('loan', 'view', 'Xem khoản vay & tạm ứng', 'Xem danh sách và chi tiết các khoản vay, tạm ứng của cán bộ nhân viên'),
('loan', 'create', 'Tạo đề xuất vay / tạm ứng', 'Lập hồ sơ đề xuất tạm ứng lương hoặc vay vốn cho nhân viên'),
('loan', 'approve', 'Phê duyệt khoản vay', 'Phê duyệt hoặc từ chối giải ngân các khoản vay và tạm ứng'),
('loan', 'repay', 'Ghi nhận hoàn trả / trả nợ', 'Ghi nhận các đợt khấu trừ hoặc thanh toán trả nợ khoản vay'),
('loan', 'delete', 'Hủy / Xóa khoản vay', 'Hủy hoặc xóa bản ghi đề xuất khoản vay'),
('loan', 'report', 'Báo cáo khoản vay & dư nợ', 'Xem báo cáo thống kê biến động dư nợ, tổng hợp nợ vay theo phòng ban/dự án')
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `description`=VALUES(`description`);

-- 7. Gán quyền cho các role
-- Super Admin: toàn quyền (id=1)
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 1, p.id FROM `permissions` p WHERE p.module_code = 'loan';

-- C&B Staff (id=6): view, create, approve, repay, report
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 6, p.id FROM `permissions` p WHERE p.module_code = 'loan' AND p.action_code IN ('view', 'create', 'approve', 'repay', 'report');

-- QTVP (id=5): view, create, repay, report
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 5, p.id FROM `permissions` p WHERE p.module_code = 'loan' AND p.action_code IN ('view', 'create', 'repay', 'report');

-- Employee (id=7): view, create
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 7, p.id FROM `permissions` p WHERE p.module_code = 'loan' AND p.action_code IN ('view', 'create');

-- 8. Thêm Menu vào system_menus
-- Thêm mục Tạm ứng & Khoản vay nằm dưới parent_id = 17 (Bảng lương)
INSERT INTO `system_menus` (`parent_id`, `title`, `url`, `icon`, `sort_order`, `is_active`, `permission_required`)
VALUES
(17, 'Tạm ứng & Khoản vay', 'loan', 'fas fa-hand-holding-dollar', 84, 1, 'loan.view')
ON DUPLICATE KEY UPDATE `title`=VALUES(`title`), `icon`=VALUES(`icon`);

-- 9. Dữ liệu mẫu (Sample loans & repayments)
-- Khoản 1: Tạm ứng lương đang hoạt động (Active) của nhân viên 1 (Nguyễn Văn Tổng)
INSERT INTO `employee_loans` (
    `loan_code`, `employee_id`, `loan_type_id`, `amount`, `interest_rate`, 
    `term_months`, `monthly_emi`, `total_repayment`, `total_paid`, `remaining_balance`, 
    `status`, `applied_date`, `disbursement_date`, `approved_by`, `approved_date`, `reason`, `disbursement_method`, `notes`
) VALUES (
    'LN-2026-0001', 1, 1, 9000000.00, 0.00,
    3, 3000000.00, 9000000.00, 3000000.00, 6000000.00,
    'Active', '2026-08-01', '2026-08-03', 102, '2026-08-02 14:30:00',
    'Tạm ứng sửa chữa nhà ở gia đình đầu tháng 8', 'Bank Transfer', 'Khấu trừ 3.000.000đ/tháng trong 3 kỳ lương liên tiếp'
) ON DUPLICATE KEY UPDATE `loan_code`=`loan_code`;

-- Đợt trả nợ mẫu cho Khoản 1
INSERT INTO `loan_repayments` (
    `loan_id`, `amount`, `principal_amount`, `interest_amount`, `balance_after`, 
    `payment_date`, `payment_method`, `payment_type`, `recorded_by`, `notes`
) 
SELECT l.id, 3000000.00, 3000000.00, 0.00, 6000000.00, '2026-08-31', 'Payroll Deduction', 'Auto', 102, 'Khấu trừ tự động kỳ lương tháng 08/2026'
FROM `employee_loans` l WHERE l.loan_code = 'LN-2026-0001' AND NOT EXISTS (
    SELECT 1 FROM `loan_repayments` r WHERE r.loan_id = l.id
);

-- Khoản 2: Vay mua thiết bị laptop của nhân viên 3 (Lê Văn Sự)
INSERT INTO `employee_loans` (
    `loan_code`, `employee_id`, `loan_type_id`, `amount`, `interest_rate`, 
    `term_months`, `monthly_emi`, `total_repayment`, `total_paid`, `remaining_balance`, 
    `status`, `applied_date`, `disbursement_date`, `approved_by`, `approved_date`, `reason`, `disbursement_method`, `notes`
) VALUES (
    'LN-2026-0002', 3, 3, 24000000.00, 3.50,
    12, 2070000.00, 24840000.00, 4140000.00, 20700000.00,
    'Active', '2026-07-10', '2026-07-15', 102, '2026-07-12 09:15:00',
    'Vay mua laptop ThinkPad cấu hình cao phục vụ công tác thiết kế bản vẽ dự án Amkor', 'Bank Transfer', 'Lãi suất ưu đãi 3.5%/năm hỗ trợ bởi công ty'
) ON DUPLICATE KEY UPDATE `loan_code`=`loan_code`;

-- Đợt trả nợ mẫu cho Khoản 2 (2 tháng)
INSERT INTO `loan_repayments` (
    `loan_id`, `amount`, `principal_amount`, `interest_amount`, `balance_after`, 
    `payment_date`, `payment_method`, `payment_type`, `recorded_by`, `notes`
)
SELECT l.id, 2070000.00, 2000000.00, 70000.00, 22770000.00, '2026-07-31', 'Payroll Deduction', 'Auto', 102, 'Kỳ 1/12 - Tháng 07/2026'
FROM `employee_loans` l WHERE l.loan_code = 'LN-2026-0002' AND NOT EXISTS (
    SELECT 1 FROM `loan_repayments` r WHERE r.loan_id = l.id AND r.payment_date = '2026-07-31'
);

INSERT INTO `loan_repayments` (
    `loan_id`, `amount`, `principal_amount`, `interest_amount`, `balance_after`, 
    `payment_date`, `payment_method`, `payment_type`, `recorded_by`, `notes`
)
SELECT l.id, 2070000.00, 2000000.00, 70000.00, 20700000.00, '2026-08-31', 'Payroll Deduction', 'Auto', 102, 'Kỳ 2/12 - Tháng 08/2026'
FROM `employee_loans` l WHERE l.loan_code = 'LN-2026-0002' AND NOT EXISTS (
    SELECT 1 FROM `loan_repayments` r WHERE r.loan_id = l.id AND r.payment_date = '2026-08-31'
);

-- Khoản 3: Đề xuất tạm ứng lương đang chờ duyệt (Pending) của nhân viên 2 (Trần Thị Nhàn)
INSERT INTO `employee_loans` (
    `loan_code`, `employee_id`, `loan_type_id`, `amount`, `interest_rate`, 
    `term_months`, `monthly_emi`, `total_repayment`, `total_paid`, `remaining_balance`, 
    `status`, `applied_date`, `disbursement_date`, `approved_by`, `approved_date`, `reason`, `disbursement_method`, `notes`
) VALUES (
    'LN-2026-0003', 2, 1, 5000000.00, 0.00,
    1, 5000000.00, 5000000.00, 0.00, 5000000.00,
    'Pending', '2026-09-25', NULL, NULL, NULL,
    'Đề xuất tạm ứng lương đóng học phí cho con đầu năm học mới', 'Bank Transfer', 'Khấu trừ 1 lần vào kỳ lương tháng 10/2026'
) ON DUPLICATE KEY UPDATE `loan_code`=`loan_code`;

-- Khoản 4: Khoản vay phúc lợi đã hoàn tất (Closed) của nhân viên 4 (Phạm Thị Tiền)
INSERT INTO `employee_loans` (
    `loan_code`, `employee_id`, `loan_type_id`, `amount`, `interest_rate`, 
    `term_months`, `monthly_emi`, `total_repayment`, `total_paid`, `remaining_balance`, 
    `status`, `applied_date`, `disbursement_date`, `approved_by`, `approved_date`, `reason`, `disbursement_method`, `notes`
) VALUES (
    'LN-2026-0004', 4, 2, 10000000.00, 0.00,
    2, 5000000.00, 10000000.00, 10000000.00, 0.00,
    'Closed', '2026-05-10', '2026-05-12', 102, '2026-05-11 10:00:00',
    'Vay quỹ tương trợ công đoàn', 'Bank Transfer', 'Đã tất toán xong'
) ON DUPLICATE KEY UPDATE `loan_code`=`loan_code`;
