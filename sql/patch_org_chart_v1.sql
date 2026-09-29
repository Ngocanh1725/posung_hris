-- ============================================================
-- POSUNG HRIS - Patch Org Chart & Department Hierarchy V1
-- Chuẩn hóa cấu trúc phân cấp và trưởng phòng cho sơ đồ tổ chức
-- ============================================================

-- 1. Cập nhật Ban Giám đốc làm Gốc (Root)
UPDATE departments SET parent_id = NULL, manager_id = 1, sort_order = 1 WHERE id = 1;

-- 2. Cập nhật các Phòng Ban cấp 1 trực thuộc Ban Giám đốc (parent_id = 1)
UPDATE departments SET parent_id = 1, manager_id = 2, sort_order = 2 WHERE id = 2;   -- Phòng Hành Chính Nhân Sự
UPDATE departments SET parent_id = 1, manager_id = 4, sort_order = 3 WHERE id = 3;   -- Phòng Kế Toán Tài Chính
UPDATE departments SET parent_id = 1, manager_id = 6, sort_order = 4 WHERE id = 4;   -- Phòng Kỹ Thuật
UPDATE departments SET parent_id = 1, manager_id = 9, sort_order = 5 WHERE id = 5;   -- Phòng Sản Xuất & Thi Công
UPDATE departments SET parent_id = 1, manager_id = 272, sort_order = 6 WHERE id = 17; -- Phòng CNTT
UPDATE departments SET parent_id = 1, manager_id = 1093, sort_order = 7 WHERE id = 30; -- Phòng QA/QC
UPDATE departments SET parent_id = 1, manager_id = 1083, sort_order = 8 WHERE id = 31; -- Phòng BIM
UPDATE departments SET parent_id = 1, manager_id = 1088, sort_order = 9 WHERE id = 32; -- Phòng Cơ điện MEP
UPDATE departments SET parent_id = 1, manager_id = 1084, sort_order = 10 WHERE id = 33; -- Phòng Cung ứng Vật tư
UPDATE departments SET parent_id = 1, manager_id = 1092, sort_order = 11 WHERE id = 34; -- Phòng Pháp chế

-- 3. Cập nhật các Tổ / Đội trực thuộc các Phòng Ban cấp 1
-- Trực thuộc Nhân sự (id = 2)
UPDATE departments SET parent_id = 2, manager_id = 551, sort_order = 1 WHERE id = 6; -- Tổ Hành Chính
UPDATE departments SET parent_id = 2, manager_id = 552, sort_order = 2 WHERE id = 7; -- Tổ Tuyển Dụng & Đào Tạo
UPDATE departments SET parent_id = 2, manager_id = 2,   sort_order = 3 WHERE id = 8; -- Tổ C&B

-- Trực thuộc Kế toán (id = 3)
UPDATE departments SET parent_id = 3, manager_id = 217, sort_order = 1 WHERE id = 9;  -- Kế toán nội bộ
UPDATE departments SET parent_id = 3, manager_id = 661, sort_order = 2 WHERE id = 10; -- Kế toán thuế

-- Trực thuộc Kỹ thuật (id = 4)
UPDATE departments SET parent_id = 4, manager_id = 198, sort_order = 1 WHERE id = 11; -- Tổ Cơ
UPDATE departments SET parent_id = 4, manager_id = 237, sort_order = 2 WHERE id = 12; -- Tổ Điện
UPDATE departments SET parent_id = 4, manager_id = 244, sort_order = 3 WHERE id = 13; -- Tổ Thiết kế

-- Trực thuộc Sản xuất & Thi công (id = 5)
UPDATE departments SET parent_id = 5, manager_id = 351, sort_order = 1 WHERE id = 14; -- Đội Thi công Amkor
UPDATE departments SET parent_id = 5, manager_id = 232, sort_order = 2 WHERE id = 15; -- Đội Thi công Samsung
UPDATE departments SET parent_id = 5, manager_id = 323, sort_order = 3 WHERE id = 16; -- Đội Cung ứng
