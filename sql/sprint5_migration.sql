-- ============================================================
-- SPRINT 5: ĐỒNG BỘ CHẤM CÔNG BIÊN VÀ ĐỘNG CƠ TÍNH LƯƠNG
-- MIGRATION SCRIPT
-- ============================================================

-- 1. Bổ sung các cột phục vụ API đồng bộ chấm công cạnh biên vào bảng `timesheets`
ALTER TABLE `timesheets`
    ADD COLUMN `sync_status` ENUM('pending', 'synced', 'error') NOT NULL DEFAULT 'pending' AFTER `is_cleanroom`,
    ADD COLUMN `device_ip` VARCHAR(45) NULL AFTER `sync_status`,
    ADD COLUMN `verification_type` VARCHAR(50) NULL AFTER `device_ip`;

-- 2. Cập nhật trạng thái `status` để hỗ trợ chức năng Khóa Bảng Công (Locked)
ALTER TABLE `timesheets`
    MODIFY COLUMN `status` ENUM('Pending', 'Approved', 'Rejected', 'Locked') NOT NULL DEFAULT 'Pending';

-- ============================================================
-- SUCCESS
-- ============================================================
