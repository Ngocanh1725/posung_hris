-- ==============================================================================
-- POSUNG HRIS - MASTER DATABASE SCHEMA V2
-- System: MySQL / MariaDB
-- Character Set: utf8mb4 / utf8mb4_unicode_ci
-- ==============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ------------------------------------------------------------------------------
-- 1. DANH MỤC & CƠ CẤU TỔ CHỨC (Organization & Categories)
-- ------------------------------------------------------------------------------

-- Phòng ban / Ban quản lý dự án
DROP TABLE IF EXISTS `departments`;
CREATE TABLE `departments` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `code` VARCHAR(20) NOT NULL UNIQUE,
    `name` VARCHAR(100) NOT NULL,
    `type` ENUM('office', 'site_pmb', 'factory') NOT NULL DEFAULT 'office',
    `parent_id` INT UNSIGNED DEFAULT NULL,
    `manager_id` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_dept_parent` FOREIGN KEY (`parent_id`) REFERENCES `departments`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dự án / Công trường thi công
DROP TABLE IF EXISTS `projects`;
CREATE TABLE `projects` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `project_code` VARCHAR(50) NOT NULL UNIQUE COMMENT 'PRJ_AMKOR, PRJ_STARLAKE...',
    `name` VARCHAR(150) NOT NULL,
    `client_name` VARCHAR(100) DEFAULT NULL COMMENT 'Samsung, Amkor, Daewoo...',
    `location` VARCHAR(255) DEFAULT NULL,
    `start_date` DATE DEFAULT NULL,
    `end_date` DATE DEFAULT NULL,
    `status` ENUM('planning', 'in_progress', 'suspended', 'completed') NOT NULL DEFAULT 'in_progress',
    `cost_center_code` VARCHAR(50) DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Vị trí & Chức danh
DROP TABLE IF EXISTS `positions`;
CREATE TABLE `positions` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(100) NOT NULL,
    `grade_level` VARCHAR(20) DEFAULT NULL,
    `base_salary_range_min` DECIMAL(18,2) DEFAULT 0.00,
    `base_salary_range_max` DECIMAL(18,2) DEFAULT 0.00,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 2. HỒ SƠ NHÂN SỰ CỐT LÕI (Core HR)
-- ------------------------------------------------------------------------------

DROP TABLE IF EXISTS `employees`;
CREATE TABLE `employees` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    
    -- Định danh cơ bản
    `employee_code` VARCHAR(20) NOT NULL UNIQUE,
    `full_name` VARCHAR(100) NOT NULL,
    `gender` ENUM('Male', 'Female', 'Other') DEFAULT 'Male',
    `birth_date` DATE DEFAULT NULL,
    `id_card_no` VARCHAR(20) DEFAULT NULL UNIQUE COMMENT 'CCCD',
    `id_card_date` DATE DEFAULT NULL,
    `id_card_place` VARCHAR(100) DEFAULT NULL,
    `tax_code` VARCHAR(50) DEFAULT NULL,
    `social_insurance_no` VARCHAR(50) DEFAULT NULL,
    
    -- Quốc tịch & Expat
    `nationality` VARCHAR(50) DEFAULT 'Vietnamese',
    `is_expat` TINYINT(1) NOT NULL DEFAULT 0,
    `passport_no` VARCHAR(50) DEFAULT NULL,
    `passport_expiry` DATE DEFAULT NULL,
    `work_permit_no` VARCHAR(50) DEFAULT NULL,
    `work_permit_expiry` DATE DEFAULT NULL,
    `trc_no` VARCHAR(50) DEFAULT NULL COMMENT 'Thẻ tạm trú',
    `trc_expiry` DATE DEFAULT NULL,
    
    -- Công việc & Ma trận tổ chức
    `department_id` INT UNSIGNED DEFAULT NULL,
    `current_project_id` INT UNSIGNED DEFAULT NULL,
    `position_id` INT UNSIGNED DEFAULT NULL,
    `direct_manager_id` INT UNSIGNED DEFAULT NULL,
    `hire_date` DATE DEFAULT NULL,
    `official_date` DATE DEFAULT NULL,
    `status` ENUM('active', 'probation', 'suspended', 'terminated', 'retired', 'blocked_hse') NOT NULL DEFAULT 'active',
    
    -- Trình độ & 2C
    `academic_level` VARCHAR(50) DEFAULT NULL,
    `degree_title` VARCHAR(100) DEFAULT NULL,
    `training_school` VARCHAR(150) DEFAULT NULL,
    `political_theory` VARCHAR(100) DEFAULT NULL,
    `foreign_languages` VARCHAR(255) DEFAULT NULL,
    `home_address` VARCHAR(255) DEFAULT NULL,
    `current_address` VARCHAR(255) DEFAULT NULL,
    `emergency_contact` VARCHAR(255) DEFAULT NULL,
    
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_emp_dept` FOREIGN KEY (`department_id`) REFERENCES `departments`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_emp_project` FOREIGN KEY (`current_project_id`) REFERENCES `projects`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_emp_position` FOREIGN KEY (`position_id`) REFERENCES `positions`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_emp_manager` FOREIGN KEY (`direct_manager_id`) REFERENCES `employees`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng tài khoản truy cập
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `employee_id` INT UNSIGNED DEFAULT NULL,
    `role` ENUM('admin', 'hr_manager', 'project_manager', 'cb_specialist', 'employee') NOT NULL DEFAULT 'employee',
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_user_emp` FOREIGN KEY (`employee_id`) REFERENCES `employees`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bổ sung khóa ngoại manager_id cho departments
ALTER TABLE `departments` ADD CONSTRAINT `fk_dept_manager` FOREIGN KEY (`manager_id`) REFERENCES `employees`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- ------------------------------------------------------------------------------
-- 3. CHỨNG CHỈ, ĐÀO TẠO & HỒ SƠ 2C
-- ------------------------------------------------------------------------------

DROP TABLE IF EXISTS `employee_certificates`;
CREATE TABLE `employee_certificates` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `employee_id` INT UNSIGNED NOT NULL,
    `certificate_name` VARCHAR(150) NOT NULL COMMENT 'VD: Hàn 6G, ATLD Nhóm 3',
    `license_no` VARCHAR(50) DEFAULT NULL,
    `issued_by` VARCHAR(150) DEFAULT NULL,
    `issue_date` DATE DEFAULT NULL,
    `expiry_date` DATE DEFAULT NULL,
    `scan_file_path` VARCHAR(255) DEFAULT NULL,
    `is_mandatory_for_site` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_cert_emp` FOREIGN KEY (`employee_id`) REFERENCES `employees`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 4. ĐIỀU ĐỘNG, KHEN THƯỞNG & KỶ LUẬT (HSE)
-- ------------------------------------------------------------------------------

DROP TABLE IF EXISTS `job_movements`;
CREATE TABLE `job_movements` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `employee_id` INT UNSIGNED NOT NULL,
    `movement_type` ENUM('transfer', 'promotion', 'salary_adjustment') NOT NULL,
    `from_project_id` INT UNSIGNED DEFAULT NULL,
    `to_project_id` INT UNSIGNED DEFAULT NULL,
    `from_department_id` INT UNSIGNED DEFAULT NULL,
    `to_department_id` INT UNSIGNED DEFAULT NULL,
    `from_cost_center` VARCHAR(50) DEFAULT NULL,
    `to_cost_center` VARCHAR(50) DEFAULT NULL,
    `decision_no` VARCHAR(50) DEFAULT NULL,
    `effective_date` DATE NOT NULL,
    `approved_by` INT UNSIGNED DEFAULT NULL,
    `status` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'approved',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_move_emp` FOREIGN KEY (`employee_id`) REFERENCES `employees`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_move_from_prj` FOREIGN KEY (`from_project_id`) REFERENCES `projects`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_move_to_prj` FOREIGN KEY (`to_project_id`) REFERENCES `projects`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_move_from_dept` FOREIGN KEY (`from_department_id`) REFERENCES `departments`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_move_to_dept` FOREIGN KEY (`to_department_id`) REFERENCES `departments`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_move_approver` FOREIGN KEY (`approved_by`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `reward_disciplines`;
CREATE TABLE `reward_disciplines` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `employee_id` INT UNSIGNED NOT NULL,
    `type` ENUM('reward', 'discipline') NOT NULL,
    `decision_no` VARCHAR(50) DEFAULT NULL,
    `decision_date` DATE NOT NULL,
    `reason` TEXT NOT NULL,
    `amount` DECIMAL(18,2) DEFAULT 0.00,
    `is_hse_violation` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_rd_emp` FOREIGN KEY (`employee_id`) REFERENCES `employees`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `hse_blacklists`;
CREATE TABLE `hse_blacklists` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_card_no` VARCHAR(20) NOT NULL COMMENT 'Dùng CCCD để block liên dự án',
    `full_name` VARCHAR(100) NOT NULL,
    `violation_date` DATE NOT NULL,
    `project_id` INT UNSIGNED DEFAULT NULL,
    `violation_type` ENUM('severe_safety', 'brawl', 'theft', 'other') NOT NULL,
    `reason` TEXT NOT NULL,
    `penalty_action` VARCHAR(255) DEFAULT NULL,
    `is_blocked_forever` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_hse_prj` FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 5. CHẤM CÔNG, LƯƠNG & PHỤ CẤP (Time & Attendance, Payroll)
-- ------------------------------------------------------------------------------

DROP TABLE IF EXISTS `timesheets`;
CREATE TABLE `timesheets` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `employee_id` INT UNSIGNED NOT NULL,
    `project_id` INT UNSIGNED DEFAULT NULL,
    `work_date` DATE NOT NULL,
    `check_in` TIME DEFAULT NULL,
    `check_out` TIME DEFAULT NULL,
    `standard_hours` DECIMAL(4,2) DEFAULT 0.00,
    `ot_normal_hours` DECIMAL(4,2) DEFAULT 0.00 COMMENT '150%',
    `ot_sunday_hours` DECIMAL(4,2) DEFAULT 0.00 COMMENT '200%',
    `ot_holiday_hours` DECIMAL(4,2) DEFAULT 0.00 COMMENT '300%',
    `night_shift_hours` DECIMAL(4,2) DEFAULT 0.00 COMMENT '130%',
    `work_environment` ENUM('normal', 'cleanroom', 'hazardous', 'outdoor_height') NOT NULL DEFAULT 'normal',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_ts_emp_date` (`employee_id`, `work_date`),
    CONSTRAINT `fk_ts_emp` FOREIGN KEY (`employee_id`) REFERENCES `employees`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_ts_prj` FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `allowances`;
CREATE TABLE `allowances` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `code` VARCHAR(50) NOT NULL UNIQUE,
    `name` VARCHAR(100) NOT NULL,
    `amount` DECIMAL(18,2) DEFAULT 0.00,
    `is_taxable` TINYINT(1) NOT NULL DEFAULT 0,
    `type` ENUM('fixed_monthly', 'daily_attendance') NOT NULL DEFAULT 'fixed_monthly',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `employee_allowances`;
CREATE TABLE `employee_allowances` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `employee_id` INT UNSIGNED NOT NULL,
    `allowance_id` INT UNSIGNED NOT NULL,
    `effective_date` DATE NOT NULL,
    `expiry_date` DATE DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_ea_emp` FOREIGN KEY (`employee_id`) REFERENCES `employees`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_ea_allowance` FOREIGN KEY (`allowance_id`) REFERENCES `allowances`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `payrolls`;
CREATE TABLE `payrolls` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `employee_id` INT UNSIGNED NOT NULL,
    `payroll_month` VARCHAR(7) NOT NULL COMMENT 'YYYY-MM',
    `cost_center_code` VARCHAR(50) DEFAULT NULL,
    `base_salary` DECIMAL(18,2) NOT NULL DEFAULT 0.00,
    `standard_days` DECIMAL(4,2) NOT NULL DEFAULT 26.00,
    `actual_days` DECIMAL(4,2) NOT NULL DEFAULT 0.00,
    `ot_pay` DECIMAL(18,2) NOT NULL DEFAULT 0.00,
    `allowances_total` DECIMAL(18,2) NOT NULL DEFAULT 0.00,
    `gross_salary` DECIMAL(18,2) NOT NULL DEFAULT 0.00,
    `tax_deduction` DECIMAL(18,2) NOT NULL DEFAULT 0.00,
    `insurance_deduction` DECIMAL(18,2) NOT NULL DEFAULT 0.00,
    `net_salary` DECIMAL(18,2) NOT NULL DEFAULT 0.00,
    `status` ENUM('draft', 'calculated', 'approved', 'paid') NOT NULL DEFAULT 'draft',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_pr_emp_month` (`employee_id`, `payroll_month`),
    CONSTRAINT `fk_pr_emp` FOREIGN KEY (`employee_id`) REFERENCES `employees`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `payroll_details`;
CREATE TABLE `payroll_details` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `payroll_id` INT UNSIGNED NOT NULL,
    `item_type` ENUM('allowance', 'deduction', 'bonus', 'other') NOT NULL,
    `description` VARCHAR(255) NOT NULL,
    `amount` DECIMAL(18,2) NOT NULL DEFAULT 0.00,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_prd_payroll` FOREIGN KEY (`payroll_id`) REFERENCES `payrolls`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 6. TUYỂN DỤNG & OFFBOARDING
-- ------------------------------------------------------------------------------

DROP TABLE IF EXISTS `recruitment_requests`;
CREATE TABLE `recruitment_requests` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `department_id` INT UNSIGNED DEFAULT NULL,
    `project_id` INT UNSIGNED DEFAULT NULL,
    `position_id` INT UNSIGNED DEFAULT NULL,
    `quantity` INT UNSIGNED NOT NULL DEFAULT 1,
    `reason` TEXT DEFAULT NULL,
    `status` ENUM('pending', 'approved', 'recruiting', 'closed', 'rejected') NOT NULL DEFAULT 'pending',
    `requested_by` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_rr_dept` FOREIGN KEY (`department_id`) REFERENCES `departments`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_rr_prj` FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_rr_pos` FOREIGN KEY (`position_id`) REFERENCES `positions`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_rr_user` FOREIGN KEY (`requested_by`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `candidates`;
CREATE TABLE `candidates` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `request_id` INT UNSIGNED NOT NULL,
    `full_name` VARCHAR(100) NOT NULL,
    `id_card_no` VARCHAR(20) DEFAULT NULL,
    `phone` VARCHAR(20) DEFAULT NULL,
    `cv_file_path` VARCHAR(255) DEFAULT NULL,
    `welding_test_score` VARCHAR(50) DEFAULT NULL COMMENT 'Đánh giá tay nghề hàn',
    `kanban_state` ENUM('applied', 'interviewing', 'welding_test', 'offered', 'hired', 'rejected') NOT NULL DEFAULT 'applied',
    `interviewer_notes` TEXT DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_cd_req` FOREIGN KEY (`request_id`) REFERENCES `recruitment_requests`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `offboardings`;
CREATE TABLE `offboardings` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `employee_id` INT UNSIGNED NOT NULL,
    `type` ENUM('resignation', 'termination', 'retirement') NOT NULL,
    `decision_no` VARCHAR(50) DEFAULT NULL,
    `effective_date` DATE NOT NULL,
    `ppe_returned` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Đồ bảo hộ',
    `tools_returned` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Thiết bị đo/Hàn',
    `laptop_returned` TINYINT(1) NOT NULL DEFAULT 0,
    `id_card_returned` TINYINT(1) NOT NULL DEFAULT 0,
    `notes` TEXT DEFAULT NULL,
    `created_by` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_off_emp` FOREIGN KEY (`employee_id`) REFERENCES `employees`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_off_user` FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
