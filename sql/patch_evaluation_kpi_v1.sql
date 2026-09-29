-- ============================================================
-- POSUNG HRIS - BẢN VÁ CẬP NHẬT (PATCH) EVALUATION & KPI V1
-- Chủ đề: Phân hệ Đánh giá Năng lực & KPI (Performance Management)
-- ============================================================

-- 1. Bảng mẫu đánh giá (Templates)
CREATE TABLE IF NOT EXISTS `evaluation_templates` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `applies_to` VARCHAR(100) DEFAULT 'All',
    `position_id` INT NULL,
    `department_id` INT NULL,
    `status` ENUM('Active', 'Inactive') DEFAULT 'Active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_template_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Bảng tiêu chí đánh giá (Criteria)
CREATE TABLE IF NOT EXISTS `evaluation_criteria` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `template_id` INT NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `category` VARCHAR(100) DEFAULT 'KPI',
    `weight` INT NOT NULL DEFAULT 20,
    `description` TEXT NULL,
    `sort_order` INT DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_criteria_template` (`template_id`),
    CONSTRAINT `fk_criteria_template` FOREIGN KEY (`template_id`) REFERENCES `evaluation_templates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Bảng chu kỳ đánh giá (Periods)
CREATE TABLE IF NOT EXISTS `evaluation_periods` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `template_id` INT NOT NULL,
    `start_date` DATE NOT NULL,
    `end_date` DATE NOT NULL,
    `department_id` INT NULL,
    `status` ENUM('Draft', 'Active', 'Closed') DEFAULT 'Draft',
    `notes` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_period_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Bảng điểm đánh giá chi tiết (Detailed Scores)
CREATE TABLE IF NOT EXISTS `evaluation_scores` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `period_id` INT NOT NULL,
    `employee_id` INT NOT NULL,
    `criteria_id` INT NOT NULL,
    `self_score` DECIMAL(4,2) NULL,
    `manager_score` DECIMAL(4,2) NULL,
    `final_score` DECIMAL(4,2) NULL,
    `comment` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_period_emp_crit` (`period_id`, `employee_id`, `criteria_id`),
    INDEX `idx_score_period_emp` (`period_id`, `employee_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Bổ sung trường cho bảng emp_evaluations (Tổng hợp kết quả kỳ đánh giá)
ALTER TABLE `emp_evaluations`
ADD COLUMN `period_id` INT NULL AFTER `eval_period`,
ADD COLUMN `template_id` INT NULL AFTER `period_id`,
ADD COLUMN `final_score` DECIMAL(5,2) NULL AFTER `competency_score`,
ADD COLUMN `status` ENUM('Draft', 'Self_Evaluated', 'Manager_Evaluated', 'Approved') DEFAULT 'Draft' AFTER `overall_grade`;

-- 6. Thêm menu Đánh giá & KPI vào system_menus
INSERT INTO `system_menus` (`parent_id`, `title`, `url`, `icon`, `sort_order`, `is_active`, `permission_required`)
VALUES (NULL, 'Đánh giá & KPI', 'evaluation', 'fas fa-star-half-alt', 46, 1, 'employee.view');
