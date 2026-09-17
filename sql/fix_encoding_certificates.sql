-- ============================================================
-- POSUNG HRIS – Fix encoding bảng certificates
-- Ghi đè dữ liệu tiếng Việt đúng chuẩn UTF-8
-- ============================================================

SET NAMES utf8mb4;

-- ID 1: Phạm Đình Hùng (employee_id=5) - Hàn 6G SMAW/GTAW
UPDATE `certificates` SET 
  `cert_name` = 'Chứng chỉ Hàn 6G – SMAW/GTAW Ống thép áp lực',
  `issuing_authority` = 'Viện Hàn – CĐ Việt Đức'
WHERE `id` = 1;

-- ID 2: Bùi Văn Thắng (employee_id=6) - Hàn 6G SMAW/GTAW
UPDATE `certificates` SET 
  `cert_name` = 'Chứng chỉ Hàn 6G – SMAW/GTAW Ống thép áp lực',
  `issuing_authority` = 'CĐ Nghề Lilama'
WHERE `id` = 2;

-- ID 3: Đỗ Quốc Cường (employee_id=7) - Hàn 6G FCAW
UPDATE `certificates` SET 
  `cert_name` = 'Chứng chỉ Hàn 6G – FCAW Ống thép Carbon',
  `issuing_authority` = 'TT Đào tạo Nghề HN'
WHERE `id` = 3;

-- ID 4: Nguyễn Văn An (employee_id=2) - An toàn Nhóm 3
UPDATE `certificates` SET 
  `cert_name` = 'Chứng chỉ An toàn Nhóm 3 – Kỹ sư hiện trường',
  `issuing_authority` = 'Cục ATLĐ – Bộ LĐTBXH'
WHERE `id` = 4;

-- ID 5: Lê Minh Đức (employee_id=4) - An toàn Nhóm 3
UPDATE `certificates` SET 
  `cert_name` = 'Chứng chỉ An toàn Nhóm 3 – Kỹ sư hiện trường',
  `issuing_authority` = 'Cục ATLĐ – Bộ LĐTBXH'
WHERE `id` = 5;

-- ID 6: Trần Văn Tùng (employee_id=9) - An toàn Nhóm 3 GS
UPDATE `certificates` SET 
  `cert_name` = 'Chứng chỉ An toàn Nhóm 3 – Giám sát thi công',
  `issuing_authority` = 'Cục ATLĐ – Bộ LĐTBXH'
WHERE `id` = 6;

-- ID 7: Nguyễn Văn An (employee_id=2) - Hành nghề M&E
UPDATE `certificates` SET 
  `cert_name` = 'Chứng chỉ hành nghề Thiết kế Điện M&E – Hạng II',
  `issuing_authority` = 'Bộ Xây dựng'
WHERE `id` = 7;

-- ID 8: Lê Minh Đức (employee_id=4) - Hành nghề GS CK
UPDATE `certificates` SET 
  `cert_name` = 'Chứng chỉ hành nghề Giám sát CK – Hạng II',
  `issuing_authority` = 'Bộ Xây dựng'
WHERE `id` = 8;

-- ID 9: Trần Thị Bích Ngọc (employee_id=3) - Revit
UPDATE `certificates` SET 
  `cert_name` = 'Autodesk Revit Professional – MEP',
  `issuing_authority` = 'Autodesk'
WHERE `id` = 9;
