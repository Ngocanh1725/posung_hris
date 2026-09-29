-- ============================================================
-- POSUNG HRIS - SQL PATCH: MODULE BẢO HIỂM XÃ HỘI (SOCIAL INSURANCE)
-- File: sql/patch_insurance_v1.sql
-- ============================================================

-- 1. BẢNG TỶ LỆ ĐÓNG BẢO HIỂM THEO NĂM (BHXH, BHYT, BHTN, BHTNLĐ)
CREATE TABLE IF NOT EXISTS `insurance_rates` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `insurance_type` ENUM('BHXH', 'BHYT', 'BHTN', 'BHTNLD') NOT NULL,
    `name` VARCHAR(100) NOT NULL COMMENT 'Tên loại bảo hiểm',
    `employee_rate` DECIMAL(5,2) NOT NULL DEFAULT 0.00 COMMENT 'Tỷ lệ NLĐ đóng (%)',
    `company_rate` DECIMAL(5,2) NOT NULL DEFAULT 0.00 COMMENT 'Tỷ lệ Doanh nghiệp đóng (%)',
    `effective_year` INT NOT NULL DEFAULT 2026 COMMENT 'Năm áp dụng',
    `start_date` DATE NOT NULL COMMENT 'Ngày bắt đầu hiệu lực',
    `end_date` DATE NULL COMMENT 'Ngày hết hiệu lực (nếu có)',
    `status` ENUM('Active', 'Inactive') DEFAULT 'Active',
    `notes` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. BẢNG THÔNG TIN BẢO HIỂM CỦA NHÂN VIÊN
CREATE TABLE IF NOT EXISTS `employee_insurance` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `employee_id` INT NOT NULL UNIQUE,
    `social_insurance_no` VARCHAR(50) NULL COMMENT 'Số sổ BHXH (10 số)',
    `health_insurance_no` VARCHAR(50) NULL COMMENT 'Mã số thẻ BHYT (15 ký tự)',
    `hospital_code` VARCHAR(20) NULL COMMENT 'Mã bệnh viện/cơ sở KCB',
    `hospital_name` VARCHAR(255) NULL COMMENT 'Nơi đăng ký KCB ban đầu',
    `insurance_salary` DECIMAL(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Mức tiền lương đóng BHXH',
    `start_date` DATE NOT NULL COMMENT 'Ngày bắt đầu tham gia',
    `end_date` DATE NULL COMMENT 'Ngày dừng / báo giảm',
    `status` ENUM('Active', 'Suspended', 'Stopped') DEFAULT 'Active' COMMENT 'Đang tham gia / Tạm dừng / Đã dừng',
    `notes` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_emp_id` (`employee_id`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. BẢNG BIẾN ĐỘNG LAO ĐỘNG BẢO HIỂM (BÁO TĂNG / GIẢM D02-TS)
CREATE TABLE IF NOT EXISTS `insurance_adjustments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `employee_id` INT NOT NULL,
    `adjustment_type` ENUM('Tang_Moi', 'Tang_Luong', 'Giam_Han', 'Giam_ThaiSan', 'Giam_OmDau', 'Giam_KhongLuong') NOT NULL,
    `old_salary` DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Mức lương cũ',
    `new_salary` DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Mức lương mới',
    `reason` TEXT NOT NULL COMMENT 'Lý do biến động',
    `effective_month` VARCHAR(7) NOT NULL COMMENT 'Tháng áp dụng dạng YYYY-MM',
    `effective_date` DATE NOT NULL COMMENT 'Ngày hiệu lực',
    `doc_no` VARCHAR(100) NULL COMMENT 'Số công văn / đợt nộp D02-TS',
    `status` ENUM('Draft', 'Submitted', 'Approved', 'Rejected') DEFAULT 'Draft',
    `created_by` INT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_adj_emp` (`employee_id`),
    INDEX `idx_adj_month` (`effective_month`),
    INDEX `idx_adj_type` (`adjustment_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. BẢNG CHẾ ĐỘ BẢO HIỂM ĐÃ HƯỞNG (C70a-HD)
CREATE TABLE IF NOT EXISTS `insurance_claims` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `employee_id` INT NOT NULL,
    `claim_type` ENUM('OmDau', 'ThaiSan', 'TaiNanLD_BNN', 'DuongSuc') NOT NULL COMMENT 'Loại chế độ',
    `from_date` DATE NOT NULL COMMENT 'Từ ngày nghỉ',
    `to_date` DATE NOT NULL COMMENT 'Đến ngày nghỉ',
    `leave_days` INT NOT NULL DEFAULT 1 COMMENT 'Số ngày nghỉ thực tế',
    `claim_amount` DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Số tiền cơ quan BH duyệt chi trả',
    `bank_account` VARCHAR(50) NULL COMMENT 'Số tài khoản nhận trợ cấp',
    `bank_name` VARCHAR(100) NULL COMMENT 'Ngân hàng',
    `document_ref` VARCHAR(255) NULL COMMENT 'Chứng từ kèm theo (Giấy ra viện, giấy khai sinh...)',
    `status` ENUM('Pending', 'Approved', 'Paid', 'Rejected') DEFAULT 'Pending',
    `approved_date` DATE NULL,
    `paid_date` DATE NULL,
    `notes` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_claim_emp` (`employee_id`),
    INDEX `idx_claim_status` (`status`),
    INDEX `idx_claim_type` (`claim_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. THÊM MENU HỆ THỐNG
INSERT INTO `system_menus` (`title`, `url`, `icon`, `parent_id`, `sort_order`, `is_active`, `permission_required`)
SELECT 'Bảo hiểm Xã hội', 'insurance', 'fas fa-shield-alt', NULL, 47, 1, 'employee.view'
WHERE NOT EXISTS (
    SELECT 1 FROM `system_menus` WHERE `url` = 'insurance'
);

-- 6. SEED TỶ LỆ ĐÓNG BẢO HIỂM CHUẨN VIỆT NAM (2026)
INSERT INTO `insurance_rates` (`insurance_type`, `name`, `employee_rate`, `company_rate`, `effective_year`, `start_date`, `status`, `notes`)
VALUES 
('BHXH',    'Bảo hiểm Xã hội (Hưu trí & Tử tuất, Ốm đau, Thai sản)', 8.00,  17.50, 2026, '2026-01-01', 'Active', 'Áp dụng cho HĐLĐ có thời hạn từ 1 tháng trở lên'),
('BHYT',    'Bảo hiểm Y tế',                                      1.50,   3.00, 2026, '2026-01-01', 'Active', 'Khám chữa bệnh BHYT toàn quốc'),
('BHTN',    'Bảo hiểm Thất nghiệp',                               1.00,   1.00, 2026, '2026-01-01', 'Active', 'Áp dụng cho người lao động Việt Nam'),
('BHTNLD',  'Bảo hiểm Tai nạn lao động, Bệnh nghề nghiệp (BHTNLĐ-BNN)', 0.00,   0.50, 2026, '2026-01-01', 'Active', 'Doanh nghiệp đóng 100%')
ON DUPLICATE KEY UPDATE `status` = VALUES(`status`);

-- 7. SEED DỮ LIỆU MẪU BAN ĐẦU CHO EMPLOYEE_INSURANCE TỪ NHÂN VIÊN HIỆN CÓ
INSERT INTO `employee_insurance` (`employee_id`, `social_insurance_no`, `health_insurance_no`, `hospital_code`, `hospital_name`, `insurance_salary`, `start_date`, `status`, `notes`)
SELECT 
    e.id,
    COALESCE(NULLIF(e.social_insurance_no, ''), CONCAT('012', LPAD(e.id, 7, '0'))),
    COALESCE(NULLIF(e.health_insurance_no, ''), CONCAT('DN401', LPAD(e.id, 10, '0'))),
    '01-015',
    'Bệnh viện Đa khoa Quốc tế Hải Phòng',
    COALESCE(s.base_salary, 6000000.00),
    COALESCE(e.join_date, '2025-01-01'),
    'Active',
    'Khởi tạo đồng bộ tự động từ hệ thống nhân sự'
FROM `employees` e
LEFT JOIN (
    SELECT employee_id, MAX(base_salary) as base_salary 
    FROM salaries 
    GROUP BY employee_id
) s ON e.id = s.employee_id
WHERE e.status != 'Resigned'
ON DUPLICATE KEY UPDATE `insurance_salary` = VALUES(`insurance_salary`);

-- 8. SEED BIẾN ĐỘNG MẪU (D02-TS)
INSERT INTO `insurance_adjustments` (`employee_id`, `adjustment_type`, `old_salary`, `new_salary`, `reason`, `effective_month`, `effective_date`, `doc_no`, `status`)
SELECT 
    e.id,
    'Tang_Moi',
    0.00,
    COALESCE(ei.insurance_salary, 6500000),
    'Ký HĐLĐ chính thức tham gia BHXH mới',
    DATE_FORMAT(CURRENT_DATE, '%Y-%m'),
    CURRENT_DATE,
    CONCAT('D02-', DATE_FORMAT(CURRENT_DATE, '%Y%m'), '-01'),
    'Approved'
FROM `employees` e
JOIN `employee_insurance` ei ON e.id = ei.employee_id
WHERE e.status != 'Resigned'
LIMIT 3;

-- 9. SEED CHẾ ĐỘ MẪU (C70a-HD)
INSERT INTO `insurance_claims` (`employee_id`, `claim_type`, `from_date`, `to_date`, `leave_days`, `claim_amount`, `bank_account`, `bank_name`, `document_ref`, `status`, `approved_date`, `notes`)
SELECT 
    e.id,
    'OmDau',
    DATE_SUB(CURRENT_DATE, INTERVAL 15 DAY),
    DATE_SUB(CURRENT_DATE, INTERVAL 12 DAY),
    3,
    750000.00,
    COALESCE(e.bank_account_no, '19034567890123'),
    COALESCE(e.bank_name, 'Techcombank'),
    'GCN-BV-2026/8941',
    'Paid',
    DATE_SUB(CURRENT_DATE, INTERVAL 5 DAY),
    'Nghỉ điều trị ngoại trú viêm phế quản cấp'
FROM `employees` e
LIMIT 1;
