-- ============================================================
-- POSUNG HRIS - BẢN VÁ CẬP NHẬT (PATCH) TRAINING MODULE V1
-- Chủ đề: Phân hệ Quản lý Đào tạo & Phát triển (L&D)
-- ============================================================

-- 1. Bổ sung trường cho bảng trainings
ALTER TABLE `trainings`
ADD COLUMN `department_id` INT NULL AFTER `provider`,
ADD COLUMN `max_participants` INT DEFAULT 0 AFTER `cost`,
ADD COLUMN `location` VARCHAR(255) NULL AFTER `max_participants`,
ADD COLUMN `deleted_at` TIMESTAMP NULL DEFAULT NULL AFTER `created_at`;

-- 2. Bổ sung trường cho bảng training_participants
ALTER TABLE `training_participants`
ADD COLUMN `score` DECIMAL(5,2) NULL AFTER `status`,
ADD COLUMN `result` VARCHAR(50) NULL DEFAULT 'Pending' AFTER `score`,
ADD COLUMN `certificate_no` VARCHAR(100) NULL AFTER `result`,
ADD COLUMN `completed_date` DATE NULL AFTER `certificate_no`,
ADD COLUMN `notes` TEXT NULL AFTER `completed_date`;

-- 3. Bổ sung trường training_id cho emp_trainings nếu muốn liên kết trực tiếp
ALTER TABLE `emp_trainings`
ADD COLUMN `training_id` INT NULL AFTER `employee_id`;

-- 4. Thêm Menu Đào tạo & L&D vào system_menus
INSERT INTO `system_menus` (`parent_id`, `title`, `url`, `icon`, `sort_order`, `is_active`, `permission_required`)
VALUES (NULL, 'Đào tạo & L&D', 'training', 'fas fa-graduation-cap', 45, 1, 'employee.view');
