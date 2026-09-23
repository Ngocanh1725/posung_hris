-- ==========================================================
-- TỆP SQL MASTER V3 CHO PHPMYADMIN - POSUNG HRIS
-- Character Set: utf8mb4_unicode_ci
-- ==========================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ══════════════════════════════════════════════════════════
-- 1. NHÓM PHÂN QUYỀN ĐA TẦNG & QUẢN LÝ KHUNG WEB
-- ══════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS `roles` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(50) NOT NULL UNIQUE,
    `name` VARCHAR(100) NOT NULL,
    `description` TEXT NULL,
    `is_system` TINYINT(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `permissions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `module_code` VARCHAR(50) NOT NULL,
    `action_code` VARCHAR(50) NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `description` TEXT NULL,
    UNIQUE KEY `uk_module_action` (`module_code`, `action_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `role_permissions` (
    `role_id` INT NOT NULL,
    `permission_id` INT NOT NULL,
    PRIMARY KEY (`role_id`, `permission_id`),
    FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`permission_id`) REFERENCES `permissions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `user_permissions` (
    `user_id` INT NOT NULL,
    `permission_id` INT NOT NULL,
    `is_granted` TINYINT(1) DEFAULT 1,
    PRIMARY KEY (`user_id`, `permission_id`),
    FOREIGN KEY (`permission_id`) REFERENCES `permissions`(`id`) ON DELETE CASCADE
    -- Foreign key to users added later
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `system_menus` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `parent_id` INT NULL,
    `title` VARCHAR(100) NOT NULL,
    `url` VARCHAR(255) NULL,
    `icon` VARCHAR(100) NULL,
    `sort_order` INT DEFAULT 0,
    `is_active` TINYINT(1) DEFAULT 1,
    `permission_required` VARCHAR(100) NULL,
    FOREIGN KEY (`parent_id`) REFERENCES `system_menus`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ══════════════════════════════════════════════════════════
-- 2. NHÓM TỔ CHỨC CƠ CẤU
-- ══════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS `departments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(50) NOT NULL UNIQUE,
    `name` VARCHAR(150) NOT NULL,
    `parent_id` INT NULL,
    `manager_id` INT NULL,
    `type` ENUM('office', 'factory') DEFAULT 'office',
    FOREIGN KEY (`parent_id`) REFERENCES `departments`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `projects` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `project_code` VARCHAR(50) NOT NULL UNIQUE,
    `name` VARCHAR(150) NOT NULL,
    `client_name` VARCHAR(100) NULL,
    `location` VARCHAR(255) NULL,
    `start_date` DATE NULL,
    `end_date` DATE NULL,
    `status` ENUM('Active', 'Completed', 'Suspended') DEFAULT 'Active',
    `cost_center_code` VARCHAR(50) NULL,
    `headcount_budget` INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `positions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(50) NOT NULL UNIQUE,
    `title` VARCHAR(150) NOT NULL,
    `grade_level` INT DEFAULT 1,
    `base_salary_min` DECIMAL(15,2) DEFAULT 0,
    `base_salary_max` DECIMAL(15,2) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ══════════════════════════════════════════════════════════
-- 3. NHÓM HỒ SƠ NHÂN SỰ CHUẨN 2C-BNV / HUHA_HRM
-- ══════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS `employees` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `employee_code` VARCHAR(50) NOT NULL UNIQUE,
    `full_name` VARCHAR(150) NOT NULL,
    `gender` ENUM('Male', 'Female', 'Other') DEFAULT 'Male',
    `birth_date` DATE NULL,
    `birth_place` VARCHAR(255) NULL,
    `native_place` VARCHAR(255) NULL,
    `id_card_no` VARCHAR(50) NULL,
    `id_card_date` DATE NULL,
    `id_card_place` VARCHAR(255) NULL,
    `ethnic` VARCHAR(50) DEFAULT 'Kinh',
    `religion` VARCHAR(50) DEFAULT 'Không',
    `nationality` VARCHAR(50) DEFAULT 'Vietnam',
    
    -- Expat Fields
    `is_expat` TINYINT(1) DEFAULT 0,
    `passport_no` VARCHAR(50) NULL,
    `passport_expiry` DATE NULL,
    `work_permit_no` VARCHAR(100) NULL,
    `work_permit_expiry` DATE NULL,
    `trc_no` VARCHAR(100) NULL,
    `trc_expiry` DATE NULL,
    
    -- Education (2C)
    `academic_level` VARCHAR(50) NULL,
    `degree_level` ENUM('Tiến sĩ', 'Thạc sĩ', 'Đại học', 'Cao đẳng', 'Trung cấp', 'Công nhân kỹ thuật', 'Khác') NULL,
    `degree_title` VARCHAR(150) NULL,
    `training_school` VARCHAR(150) NULL,
    `political_theory` ENUM('Cao cấp', 'Trung cấp', 'Sơ cấp', 'Không') DEFAULT 'Không',
    `state_management` VARCHAR(100) NULL,
    `foreign_languages` VARCHAR(255) NULL,
    `computer_skill` VARCHAR(255) NULL,
    
    -- Party/Union
    `party_entry_date` DATE NULL,
    `party_official_date` DATE NULL,
    `youth_union_date` DATE NULL,
    
    -- Current Employment
    `department_id` INT NULL,
    `current_project_id` INT NULL,
    `position_id` INT NULL,
    `direct_manager_id` INT NULL,
    `hire_date` DATE NULL,
    `official_date` DATE NULL,
    `contract_status` ENUM('Probation', 'Definite', 'Indefinite', 'Seasonal') DEFAULT 'Probation',
    `employee_status` ENUM('active', 'probation', 'suspended', 'terminated', 'blocked_hse') DEFAULT 'active',
    
    -- Insurance & Finance
    `social_insurance_no` VARCHAR(50) NULL,
    `health_insurance_no` VARCHAR(50) NULL,
    `tax_code` VARCHAR(50) NULL,
    `bank_account_no` VARCHAR(50) NULL,
    `bank_name` VARCHAR(150) NULL,

    FOREIGN KEY (`department_id`) REFERENCES `departments`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`current_project_id`) REFERENCES `projects`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`position_id`) REFERENCES `positions`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`direct_manager_id`) REFERENCES `employees`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `employee_id` INT NULL,
    `role_id` INT NULL,
    `email` VARCHAR(150) NULL,
    `phone` VARCHAR(20) NULL,
    `status` ENUM('active', 'locked') DEFAULT 'active',
    `last_login` DATETIME NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`employee_id`) REFERENCES `employees`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `user_permissions` ADD CONSTRAINT `fk_up_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE;

CREATE TABLE IF NOT EXISTS `employee_certificates` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `employee_id` INT NOT NULL,
    `certificate_name` VARCHAR(150) NOT NULL,
    `license_no` VARCHAR(100) NULL,
    `issued_by` VARCHAR(150) NULL,
    `issue_date` DATE NULL,
    `expiry_date` DATE NULL,
    `scan_file_path` VARCHAR(255) NULL,
    `is_mandatory_for_site` TINYINT(1) DEFAULT 0,
    FOREIGN KEY (`employee_id`) REFERENCES `employees`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `work_histories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `employee_id` INT NOT NULL,
    `from_date` DATE NOT NULL,
    `to_date` DATE NULL,
    `organization_name` VARCHAR(150) NOT NULL,
    `position_title` VARCHAR(150) NULL,
    `job_description` TEXT NULL,
    FOREIGN KEY (`employee_id`) REFERENCES `employees`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `salary_progressions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `employee_id` INT NOT NULL,
    `decision_no` VARCHAR(50) NULL,
    `effective_date` DATE NOT NULL,
    `salary_scale` VARCHAR(50) NULL,
    `salary_grade` VARCHAR(20) NULL,
    `salary_coefficient` DECIMAL(5,2) NULL,
    `base_salary` DECIMAL(15,2) NOT NULL,
    FOREIGN KEY (`employee_id`) REFERENCES `employees`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `appointments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `employee_id` INT NOT NULL,
    `decision_no` VARCHAR(50) NULL,
    `effective_date` DATE NOT NULL,
    `position_id` INT NULL,
    `department_id` INT NULL,
    `appointed_by` INT NULL,
    FOREIGN KEY (`employee_id`) REFERENCES `employees`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`position_id`) REFERENCES `positions`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`department_id`) REFERENCES `departments`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`appointed_by`) REFERENCES `employees`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `family_members` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `employee_id` INT NOT NULL,
    `relation_type` VARCHAR(50) NOT NULL,
    `full_name` VARCHAR(150) NOT NULL,
    `birth_year` INT NULL,
    `occupation` VARCHAR(150) NULL,
    `address` VARCHAR(255) NULL,
    FOREIGN KEY (`employee_id`) REFERENCES `employees`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `employee_ppes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `employee_id` INT NOT NULL,
    `ppe_item_name` VARCHAR(150) NOT NULL,
    `serial_no` VARCHAR(50) NULL,
    `issue_date` DATE NOT NULL,
    `status` ENUM('using', 'returned', 'lost_damaged') DEFAULT 'using',
    FOREIGN KEY (`employee_id`) REFERENCES `employees`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ══════════════════════════════════════════════════════════
-- 4. NHÓM ĐIỀU ĐỘNG, CHẤM CÔNG, LƯƠNG ĐỘNG & HSE
-- ══════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS `job_movements` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `employee_id` INT NOT NULL,
    `movement_type` ENUM('transfer', 'promotion', 'salary_adjustment') NOT NULL,
    `from_project_id` INT NULL,
    `to_project_id` INT NULL,
    `from_cost_center` VARCHAR(50) NULL,
    `to_cost_center` VARCHAR(50) NULL,
    `from_department_id` INT NULL,
    `to_department_id` INT NULL,
    `decision_no` VARCHAR(50) NULL,
    `effective_date` DATE NOT NULL,
    `status` ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    `approved_by` INT NULL,
    FOREIGN KEY (`employee_id`) REFERENCES `employees`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`from_project_id`) REFERENCES `projects`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`to_project_id`) REFERENCES `projects`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`from_department_id`) REFERENCES `departments`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`to_department_id`) REFERENCES `departments`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`approved_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `hse_blacklists` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `id_card_no` VARCHAR(50) NOT NULL,
    `full_name` VARCHAR(150) NOT NULL,
    `violation_date` DATE NOT NULL,
    `project_id` INT NULL,
    `violation_type` VARCHAR(100) NOT NULL,
    `reason` TEXT NULL,
    `penalty_action` VARCHAR(255) NULL,
    `is_blocked_forever` TINYINT(1) DEFAULT 1,
    FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `timesheets` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `employee_id` INT NOT NULL,
    `project_id` INT NULL,
    `work_date` DATE NOT NULL,
    `check_in` TIME NULL,
    `check_out` TIME NULL,
    `standard_hours` DECIMAL(5,2) DEFAULT 0,
    `ot_normal_hours` DECIMAL(5,2) DEFAULT 0,
    `ot_sunday_hours` DECIMAL(5,2) DEFAULT 0,
    `ot_holiday_hours` DECIMAL(5,2) DEFAULT 0,
    `night_shift_hours` DECIMAL(5,2) DEFAULT 0,
    `work_environment` ENUM('normal', 'cleanroom', 'hazardous') DEFAULT 'normal',
    `sync_status` ENUM('edge_synced', 'manual') DEFAULT 'manual',
    UNIQUE KEY `uk_employee_date` (`employee_id`, `work_date`),
    FOREIGN KEY (`employee_id`) REFERENCES `employees`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `payrolls` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `month` INT NOT NULL,
    `year` INT NOT NULL,
    `project_id` INT NULL,
    `status` ENUM('Draft', 'Approved', 'Paid') DEFAULT 'Draft',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `payroll_details` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `payroll_id` INT NOT NULL,
    `employee_id` INT NOT NULL,
    `cost_center_code` VARCHAR(50) NULL,
    `base_salary` DECIMAL(15,2) DEFAULT 0,
    `actual_work_days` DECIMAL(5,2) DEFAULT 0,
    `ot_salary` DECIMAL(15,2) DEFAULT 0,
    `site_allowance` DECIMAL(15,2) DEFAULT 0,
    `cleanroom_allowance` DECIMAL(15,2) DEFAULT 0,
    `insurance_deduction` DECIMAL(15,2) DEFAULT 0,
    `personal_tax` DECIMAL(15,2) DEFAULT 0,
    `net_salary` DECIMAL(15,2) DEFAULT 0,
    FOREIGN KEY (`payroll_id`) REFERENCES `payrolls`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`employee_id`) REFERENCES `employees`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `payroll_formulas` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `formula_name` VARCHAR(150) NOT NULL,
    `formula_code` VARCHAR(50) NOT NULL UNIQUE,
    `expression` TEXT NOT NULL,
    `is_active` TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `recruitment_requests` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `department_id` INT NULL,
    `position_id` INT NULL,
    `project_id` INT NULL,
    `quantity` INT DEFAULT 1,
    `status` ENUM('Pending', 'Approved', 'In_Progress', 'Completed', 'Cancelled') DEFAULT 'Pending',
    `requirements` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`department_id`) REFERENCES `departments`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`position_id`) REFERENCES `positions`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `candidates` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `request_id` INT NULL,
    `full_name` VARCHAR(150) NOT NULL,
    `id_card_no` VARCHAR(50) NULL,
    `phone` VARCHAR(20) NULL,
    `email` VARCHAR(150) NULL,
    `status` ENUM('New', 'Screening', 'Interviewing', 'Offered', 'Hired', 'Rejected') DEFAULT 'New',
    `interview_score` DECIMAL(5,2) NULL,
    `welding_6g_result` ENUM('Pass', 'Fail', 'Not_Tested') DEFAULT 'Not_Tested',
    `is_blacklisted` TINYINT(1) DEFAULT 0,
    `cv_file_path` VARCHAR(255) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`request_id`) REFERENCES `recruitment_requests`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `reward_disciplines` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `employee_id` INT NOT NULL,
    `type` ENUM('Reward', 'Discipline') NOT NULL,
    `decision_no` VARCHAR(50) NULL,
    `decision_date` DATE NOT NULL,
    `reason` TEXT NULL,
    `amount` DECIMAL(15,2) DEFAULT 0,
    FOREIGN KEY (`employee_id`) REFERENCES `employees`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `offboardings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `employee_id` INT NOT NULL,
    `resignation_date` DATE NOT NULL,
    `last_working_date` DATE NOT NULL,
    `reason` TEXT NULL,
    `asset_returned` TINYINT(1) DEFAULT 0,
    `status` ENUM('Pending', 'Completed') DEFAULT 'Pending',
    FOREIGN KEY (`employee_id`) REFERENCES `employees`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
