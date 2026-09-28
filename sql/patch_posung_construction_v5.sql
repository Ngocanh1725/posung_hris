-- ============================================================
-- POSUNG HRIS - BẢN VÁ CẬP NHẬT (PATCH) V5
-- Chủ đề: Điều động dự án & Bóc tách chi phí lương theo mã dự án
-- ============================================================

-- 1. Bổ sung trường cho job_movements
ALTER TABLE `job_movements`
ADD COLUMN `site_allowance` DECIMAL(15,2) NULL AFTER `effective_date`,
ADD COLUMN `site_position` VARCHAR(150) NULL AFTER `site_allowance`,
ADD COLUMN `end_date` DATE NULL AFTER `site_position`;

-- 2. Tạo bảng bóc tách chi phí lương theo dự án
CREATE TABLE IF NOT EXISTS `payroll_project_allocations` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `payroll_id` INT NOT NULL,
    `employee_id` INT NOT NULL,
    `project_id` INT NOT NULL,
    `month` INT NOT NULL,
    `year` INT NOT NULL,
    `actual_days` DECIMAL(5,2) DEFAULT 0.00,
    `allocated_base_salary` DECIMAL(15,2) DEFAULT 0.00,
    `allocated_ot_salary` DECIMAL(15,2) DEFAULT 0.00,
    `site_allowance` DECIMAL(15,2) DEFAULT 0.00,
    `total_cost` DECIMAL(15,2) DEFAULT 0.00,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`payroll_id`) REFERENCES `payrolls`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`employee_id`) REFERENCES `employees`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
