-- ============================================================
-- SPRINT 4: ĐIỀU ĐỘNG DỰ ÁN & TỰ ĐỘNG HÓA COST CENTER
-- MIGRATION SCRIPT
-- ============================================================

-- 1. Cập nhật Enum trạng thái của transfer_orders
ALTER TABLE `transfer_orders`
    MODIFY COLUMN `status` ENUM('Draft', 'Pending', 'Approved', 'Cancelled') NOT NULL DEFAULT 'Draft';

-- 2. Thêm trường project_id và cost_center_code vào bảng lịch sử công tác
ALTER TABLE `emp_work_histories`
    ADD COLUMN `project_id` INT(10) UNSIGNED NULL AFTER `position`,
    ADD COLUMN `cost_center_code` VARCHAR(50) NULL AFTER `project_id`;

-- Ràng buộc khóa ngoại (Optional - nếu hệ thống thiết kế cho phép)
ALTER TABLE `emp_work_histories`
    ADD CONSTRAINT `fk_emp_wh_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- ============================================================
-- SUCCESS
-- ============================================================
