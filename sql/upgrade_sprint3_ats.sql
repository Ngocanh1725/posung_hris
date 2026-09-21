-- ============================================================
--  POSUNG HRIS – Upgrade: ATS Kanban & HSE Blacklist
--  Migration Script (MySQL / MariaDB)
--  Ngày: 2026-09-19
-- ============================================================

-- 1. Mở rộng trạng thái của ứng viên cho quy trình ATS Kanban
ALTER TABLE `candidates` 
MODIFY COLUMN `status` ENUM('received','screening','skills_test','interview','offered','hired','rejected') NOT NULL DEFAULT 'received' 
COMMENT 'Cột Kanban: received (Mới nhận), screening (Sàng lọc), skills_test (Test 6G/HSE), interview (Phỏng vấn), offered (Đề xuất), hired (Tiếp nhận), rejected (Từ chối)';

-- 2. Thêm các cột cho tính năng Cảnh báo Blacklist
ALTER TABLE `candidates` 
ADD COLUMN IF NOT EXISTS `is_blacklisted` TINYINT(1) NOT NULL DEFAULT 0 
COMMENT '1 = Nằm trong danh sách đen (HSE / Bị sa thải), 0 = Bình thường',
ADD COLUMN IF NOT EXISTS `blacklist_reason` TEXT DEFAULT NULL 
COMMENT 'Lý do Blacklist (Lấy từ hệ thống Rewards_Disciplines)';

-- 3. Thêm các cột lưu trữ tài liệu ứng tuyển từ QR Kiosk
ALTER TABLE `candidates` 
ADD COLUMN IF NOT EXISTS `front_id_card_path` VARCHAR(255) DEFAULT NULL 
COMMENT 'Đường dẫn ảnh CCCD Mặt trước',
ADD COLUMN IF NOT EXISTS `back_id_card_path` VARCHAR(255) DEFAULT NULL 
COMMENT 'Đường dẫn ảnh CCCD Mặt sau',
ADD COLUMN IF NOT EXISTS `cert_file_path` VARCHAR(255) DEFAULT NULL 
COMMENT 'Đường dẫn ảnh Chứng chỉ (Hàn 6G/An toàn HSE)';

-- Cập nhật dữ liệu cũ (nếu có) để khớp với enum mới
UPDATE `candidates` SET `status` = 'received' WHERE `status` = 'New';
UPDATE `candidates` SET `status` = 'screening' WHERE `status` = 'Screening';
UPDATE `candidates` SET `status` = 'interview' WHERE `status` = 'Interview_Scheduled' OR `status` = 'Interviewed';
UPDATE `candidates` SET `status` = 'offered' WHERE `status` = 'Offer';
UPDATE `candidates` SET `status` = 'hired' WHERE `status` = 'Hired';
UPDATE `candidates` SET `status` = 'rejected' WHERE `status` = 'Rejected' OR `status` = 'Withdrawn';
