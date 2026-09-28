-- ============================================================
-- POSUNG HRIS - BẢN VÁ CẬP NHẬT (PATCH) V6
-- Chủ đề: Nâng cấp HSE - Thẻ An Toàn, PPE & Thầu Phụ
-- ============================================================

-- 1. Bổ sung trường cho emp_ppe_issuances
ALTER TABLE `emp_ppe_issuances`
ADD COLUMN `next_replacement_date` DATE NULL AFTER `issue_date`;

-- 2. Cập nhật bảng employee_certificates nếu thiếu
-- (Đã có certificate_name, license_no, issued_by, issue_date, expiry_date, scan_file_path)

-- 3. Cập nhật bảng sub_workers (Thầu phụ) nếu thiếu induction
ALTER TABLE `sub_workers`
ADD COLUMN `safety_induction_date` DATE NULL AFTER `hse_expiry_date`,
ADD COLUMN `induction_status` ENUM('Pending', 'Completed', 'Failed') DEFAULT 'Pending' AFTER `safety_induction_date`;
