-- ============================================================
-- POSUNG HRIS - SQL PATCH: Module Lịch sử Lương & Nâng bậc
-- Version: v1.0
-- Created: 2026-09-28
-- Description:
--   1. Mở rộng cấu trúc bảng salary_progressions
--   2. Bổ sung các cột: old_salary, new_salary, reason, decision_number, decision_date, approved_by, notes, created_at
--   3. Seed dữ liệu lịch sử nâng bậc mẫu
--   4. Bổ sung menu và permissions
-- ============================================================

USE `posung_hris`;

-- 1. NÂNG CẤP CẤU TRÚC BẢNG SALARY_PROGRESSIONS
ALTER TABLE `salary_progressions`
    ADD COLUMN IF NOT EXISTS `old_salary` DECIMAL(15,2) DEFAULT 0.00 AFTER `employee_id`,
    ADD COLUMN IF NOT EXISTS `new_salary` DECIMAL(15,2) NOT NULL DEFAULT 0.00 AFTER `old_salary`,
    ADD COLUMN IF NOT EXISTS `reason` VARCHAR(150) NULL COMMENT 'Lý do nâng lương: Nâng bậc định kỳ, Thăng chức, Điều chỉnh thị trường, v.v.' AFTER `effective_date`,
    ADD COLUMN IF NOT EXISTS `decision_number` VARCHAR(100) NULL COMMENT 'Số quyết định tăng lương' AFTER `reason`,
    ADD COLUMN IF NOT EXISTS `decision_date` DATE NULL COMMENT 'Ngày ký quyết định' AFTER `decision_number`,
    ADD COLUMN IF NOT EXISTS `approved_by` INT(11) NULL COMMENT 'Người phê duyệt (User ID)' AFTER `decision_date`,
    ADD COLUMN IF NOT EXISTS `notes` TEXT NULL COMMENT 'Ghi chú quyết định' AFTER `approved_by`,
    ADD COLUMN IF NOT EXISTS `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER `notes`;

-- Cập nhật new_salary bằng base_salary nếu new_salary đang là 0
UPDATE `salary_progressions` SET `new_salary` = `base_salary` WHERE `new_salary` = 0.00 AND `base_salary` > 0;

-- 2. SEED DỮ LIỆU LỊCH SỬ NÂNG LƯƠNG MẪU (Cho Nhân viên ID = 1 - Nguyễn Văn Tổng)
-- Giả lập tiến trình lương tăng trưởng qua các năm:
-- 2023: Vào công ty: 8,500,000 VND
-- 2024: Nâng bậc định kỳ: 10,000,000 VND (+17.6%)
-- 2025: Thăng chức Trưởng bộ phận: 12,000,000 VND (+20.0%)
-- 2026: Đánh giá thành tích xuất sắc: 15,000,000 VND (+25.0%)

DELETE FROM `salary_progressions` WHERE `employee_id` = 1;

INSERT INTO `salary_progressions` 
(`employee_id`, `old_salary`, `new_salary`, `effective_date`, `reason`, `decision_number`, `decision_date`, `approved_by`, `notes`, `base_salary`) 
VALUES
(1, 0.00, 8500000.00, '2023-01-15', 'Ký HĐLĐ chính thức (Mức lương khởi điểm)', 'QĐ-TD-2023/01', '2023-01-10', 102, 'Áp dụng sau thời gian thử việc đạt loại Giỏi.', 8500000.00),
(1, 8500000.00, 10000000.00, '2024-01-01', 'Nâng bậc lương định kỳ hàng năm', 'QĐ-NL-2024/015', '2023-12-25', 102, 'Đạt đánh giá KPI hoàn thành xuất sắc nhiệm vụ năm 2023.', 10000000.00),
(1, 10000000.00, 12000000.00, '2025-01-01', 'Thăng chức & Điều chỉnh chức danh', 'QĐ-BN-2024/88', '2024-12-20', 102, 'Bổ nhiệm vị trí phụ trách ban điều hành dự án.', 12000000.00),
(1, 12000000.00, 15000000.00, '2026-01-01', 'Điều chỉnh mức lương theo hiệu quả công việc', 'QĐ-NL-2026/003', '2025-12-28', 102, 'Đạt danh hiệu Chiến sĩ thi đua cấp cơ sở năm 2025.', 15000000.00);

-- Cập nhật luôn mức lương mới nhất vào bảng salaries của nhân viên 1
INSERT INTO `salaries` (`employee_id`, `base_salary`, `effective_date`, `notes`)
VALUES (1, 15000000.00, '2026-01-01', 'Điều chỉnh nâng bậc lương năm 2026')
ON DUPLICATE KEY UPDATE `base_salary` = VALUES(`base_salary`);

-- Seed mẫu cho nhân viên ID = 2 (Trần Thị Bích) nếu có
INSERT IGNORE INTO `salary_progressions` 
(`employee_id`, `old_salary`, `new_salary`, `effective_date`, `reason`, `decision_number`, `decision_date`, `approved_by`, `notes`, `base_salary`) 
SELECT 2, 9000000.00, 11000000.00, '2026-01-01', 'Nâng bậc lương định kỳ hàng năm', 'QĐ-NL-2026/004', '2025-12-28', 102, 'Nâng bậc theo thâm niên và KPI A', 11000000.00
WHERE EXISTS (SELECT 1 FROM `employees` WHERE `id` = 2);

-- 3. BỔ SUNG PERMISSIONS
INSERT INTO `permissions` (`module_code`, `action_code`, `name`, `description`)
SELECT 'salary_progression', 'view', 'Xem lịch sử lương', 'Xem tiến trình nâng bậc lương'
WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE `module_code` = 'salary_progression' AND `action_code` = 'view');

INSERT INTO `permissions` (`module_code`, `action_code`, `name`, `description`)
SELECT 'salary_progression', 'edit', 'Đề xuất nâng lương', 'Tạo và cập nhật quyết định nâng lương'
WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE `module_code` = 'salary_progression' AND `action_code` = 'edit');

INSERT INTO `permissions` (`module_code`, `action_code`, `name`, `description`)
SELECT 'salary_progression', 'batch', 'Nâng lương hàng loạt', 'Thực hiện tăng lương hàng loạt theo % hoặc số tiền'
WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE `module_code` = 'salary_progression' AND `action_code` = 'batch');

-- Gán quyền cho Super Admin (role_id = 1)
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 1, id FROM `permissions` WHERE `module_code` = 'salary_progression';

-- 4. BỔ SUNG MENU VÀO SYSTEM_MENUS
-- Tìm menu cha "Bảng lương" (id 17) hoặc tạo riêng
SET @parent_salary_id = (SELECT `id` FROM `system_menus` WHERE `url` = 'payroll' AND `parent_id` IS NULL LIMIT 1);

INSERT INTO `system_menus` (`title`, `url`, `icon`, `sort_order`, `is_active`, `parent_id`, `permission_required`)
SELECT 'Lịch sử Lương & Nâng bậc', 'salaryProgression', 'fas fa-chart-line-up', 4, 1, @parent_salary_id, 'salary_progression.view'
WHERE @parent_salary_id IS NOT NULL 
  AND NOT EXISTS (SELECT 1 FROM `system_menus` WHERE `url` = 'salaryProgression');
