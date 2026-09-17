-- ============================================================
-- POSUNG HRIS – Fix encoding tables job_movements, salaries, cost_centers
-- Ghi đè dữ liệu tiếng Việt đúng chuẩn UTF-8
-- ============================================================

SET NAMES utf8mb4;

-- 1. Bảng job_movements
UPDATE `job_movements` SET `reason` = 'Điều động KS Nguyễn Văn An về Ban ĐH DA Amkor Bắc Ninh' WHERE `id` = 1;
UPDATE `job_movements` SET `reason` = 'Chuyển KS Lê Minh Đức từ Amkor sang Samsung SEHC phụ trách HVAC' WHERE `id` = 2;
UPDATE `job_movements` SET `reason` = 'Điều động thợ hàn Đỗ Quốc Cường về Ban ĐH DA Starlake' WHERE `id` = 3;

-- 2. Bảng salaries
UPDATE `salaries` SET `notes` = 'Lương Expat – thanh toán USD quy đổi' WHERE `id` = 1;
UPDATE `salaries` SET `notes` = 'KS M&E site Amkor' WHERE `id` = 2;
UPDATE `salaries` SET `notes` = 'KS BIM văn phòng' WHERE `id` = 3;
UPDATE `salaries` SET `notes` = 'KS M&E site Samsung SEHC – phòng sạch' WHERE `id` = 4;
UPDATE `salaries` SET `notes` = 'Thợ hàn 6G site Amkor' WHERE `id` = 5;
UPDATE `salaries` SET `notes` = 'Thợ hàn 6G site Amkor' WHERE `id` = 6;
UPDATE `salaries` SET `notes` = 'Thợ hàn 6G site Starlake' WHERE `id` = 7;
UPDATE `salaries` SET `notes` = 'NV Hành chính' WHERE `id` = 8;
UPDATE `salaries` SET `notes` = 'GSCT site Amkor' WHERE `id` = 9;

-- 3. Bảng cost_centers
UPDATE `cost_centers` SET `name` = 'CC Amkor – Nhân công M&E' WHERE `id` = 1;
UPDATE `cost_centers` SET `name` = 'CC Amkor – Vật tư ống' WHERE `id` = 2;
UPDATE `cost_centers` SET `name` = 'CC Samsung – Nhân công HVAC' WHERE `id` = 3;
UPDATE `cost_centers` SET `name` = 'CC Samsung – Phòng sạch CR' WHERE `id` = 4;
UPDATE `cost_centers` SET `name` = 'CC Starlake – Nhân công MEP' WHERE `id` = 5;
