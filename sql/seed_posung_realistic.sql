-- ==============================================================================
-- POSUNG HRIS - REALISTIC SEED DATA (Phiên bản demo 15 nhân sự)
-- ==============================================================================
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

TRUNCATE TABLE `departments`;
TRUNCATE TABLE `projects`;
TRUNCATE TABLE `positions`;
TRUNCATE TABLE `employees`;
TRUNCATE TABLE `users`;
TRUNCATE TABLE `employee_certificates`;
TRUNCATE TABLE `hse_blacklists`;

-- 1. SEED DEPARTMENTS (Phòng ban)
INSERT INTO `departments` (`id`, `code`, `name`, `type`) VALUES
(1, 'DEP_BOD', 'Ban Giám Đốc', 'office'),
(2, 'DEP_HR', 'Phòng Hành Chính Nhân Sự', 'office'),
(3, 'DEP_BIM', 'Phòng Thiết Kế BIM', 'office'),
(4, 'DEP_SEHC', 'BQLDA Samsung SEHC', 'site_pmb'),
(5, 'DEP_AMKOR', 'BQLDA Amkor', 'site_pmb'),
(6, 'DEP_STAR', 'BQLDA Daewoo Starlake', 'site_pmb');

-- 2. SEED PROJECTS (Dự án / Công trường)
INSERT INTO `projects` (`id`, `project_code`, `name`, `client_name`, `location`, `status`, `cost_center_code`) VALUES
(1, 'PRJ_SEHC', 'Dự án Cơ Điện Samsung SEHC', 'Samsung Electronics', 'KCNC Quận 9, TP.HCM', 'in_progress', 'CC_SEHC_01'),
(2, 'PRJ_AMKOR', 'Dự án M&E Nhà máy Amkor', 'Amkor Technology', 'KCN Yên Phong 2C, Bắc Ninh', 'in_progress', 'CC_AMKOR_02'),
(3, 'PRJ_STARLAKE', 'Dự án Daewoo Starlake B3CC1', 'Daewoo E&C', 'KĐT Tây Hồ Tây, Hà Nội', 'in_progress', 'CC_STAR_03');

-- 3. SEED POSITIONS (Vị trí / Dải lương)
INSERT INTO `positions` (`id`, `code`, `title`, `grade_level`, `base_salary_min`, `base_salary_max`) VALUES
(1, 'POS_01', 'Giám đốc Dự án (Project Director)', '1', 50000000, 100000000),
(2, 'POS_02', 'Chuyên gia M&E (Korean Expat)', 'L2', 60000000, 120000000),
(3, 'POS_03', 'Trưởng phòng Nhân sự', 'M1', 25000000, 40000000),
(4, 'POS_04', 'Kỹ sư BIM', 'S1', 15000000, 30000000),
(5, 'POS_05', 'Kỹ sư Giám sát (Site Engineer)', 'S1', 14000000, 28000000),
(6, 'POS_06', 'Thợ hàn 6G / TIG / MIG', 'W1', 10000000, 20000000),
(7, 'POS_07', 'Thợ phụ Cơ điện', 'W2', 8000000, 12000000);

-- 4. SEED EMPLOYEES (15 Nhân sự)
-- 4.1. Expat (2 Chuyên gia Hàn Quốc)
INSERT INTO `employees` (`id`, `employee_code`, `full_name`, `nationality`, `is_expat`, `passport_no`, `passport_expiry`, `work_permit_no`, `work_permit_expiry`, `department_id`, `current_project_id`, `position_id`, `hire_date`, `status`) VALUES
(1, 'EMP001', 'Kim Jong Un', 'Korean', 1, 'M12345678', '2030-12-31', 'WP-KJU-2023', '2025-12-31', 4, 1, 2, '2023-01-10', 'active'),
(2, 'EMP002', 'Lee Min Ho', 'Korean', 1, 'M87654321', '2028-05-15', 'WP-LMH-2024', '2026-05-15', 5, 2, 1, '2022-03-01', 'active');

-- 4.2. Khối Văn phòng / Kỹ sư BIM (3 người)
INSERT INTO `employees` (`id`, `employee_code`, `full_name`, `gender`, `id_card_no`, `department_id`, `position_id`, `hire_date`, `status`) VALUES
(3, 'EMP003', 'Nguyễn Thị Hoa', 'Female', '001190123456', 2, 3, '2021-06-15', 'active'),
(4, 'EMP004', 'Trần Văn Thiết Kế', 'Male', '034091234567', 3, 4, '2022-08-01', 'active'),
(5, 'EMP005', 'Phạm Thị BIM', 'Female', '031195678901', 3, 4, '2023-02-10', 'active');

-- 4.3. Kỹ sư Giám sát công trường (4 người)
INSERT INTO `employees` (`id`, `employee_code`, `full_name`, `gender`, `id_card_no`, `department_id`, `current_project_id`, `position_id`, `hire_date`, `status`) VALUES
(6, 'EMP006', 'Lê Giám Sát SEHC', 'Male', '024090111222', 4, 1, 5, '2021-10-05', 'active'),
(7, 'EMP007', 'Ngô Công Trường', 'Male', '030092333444', 5, 2, 5, '2022-11-20', 'active'),
(8, 'EMP008', 'Vũ Giám Sát Amkor', 'Male', '027088555666', 5, 2, 5, '2023-05-15', 'active'),
(9, 'EMP009', 'Đinh Kỹ Sư Starlake', 'Male', '001095777888', 6, 3, 5, '2024-01-10', 'active');

-- 4.4. Thợ hàn / Thợ phụ cơ điện (6 người)
INSERT INTO `employees` (`id`, `employee_code`, `full_name`, `gender`, `id_card_no`, `department_id`, `current_project_id`, `position_id`, `hire_date`, `status`) VALUES
(10, 'EMP010', 'Hoàng Thợ Hàn 6G', 'Male', '038090999000', 4, 1, 6, '2020-03-12', 'active'),
(11, 'EMP011', 'Trịnh Bá Mài', 'Male', '034085111333', 4, 1, 7, '2021-07-22', 'active'),
(12, 'EMP012', 'Bùi Văn Hàn TIG', 'Male', '036092222444', 5, 2, 6, '2023-04-18', 'active'),
(13, 'EMP013', 'Lý Thợ Ống', 'Male', '022091555777', 5, 2, 7, '2023-08-30', 'active'),
(14, 'EMP014', 'Đoàn Văn Ống Spool', 'Male', '031089666888', 6, 3, 6, '2024-02-15', 'active'),
-- Người này sẽ bị HSE Blacklist
(15, 'EMP015', 'Trương Vi Phạm', 'Male', '037080999111', 5, 2, 7, '2022-09-10', 'blocked_hse');

-- 5. SEED USERS (Tài khoản)
INSERT INTO `users` (`id`, `username`, `password_hash`, `employee_id`, `role_id`) VALUES
(100, 'hoant', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3, 5),
(101, 'kimju', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 4);

-- 6. SEED EMPLOYEE CERTIFICATES (Chứng chỉ đặc thù)
INSERT INTO `employee_certificates` (`employee_id`, `certificate_name`, `license_no`, `issue_date`, `expiry_date`, `is_mandatory_for_site`) VALUES
(6, 'Giám sát M&E Hạng 1', 'GS-001', '2020-10-01', '2025-10-01', 1),
(10, 'Thợ hàn áp lực 6G', '6G-099', '2022-01-15', '2027-01-15', 1),
-- Thẻ an toàn sắp hết hạn (còn 2 ngày)
(12, 'An toàn Lao động Nhóm 3', 'ATLD-012', '2024-09-20', '2026-09-23', 1);

-- 7. SEED HSE BLACKLIST (Danh sách vi phạm an toàn)
INSERT INTO `hse_blacklists` (`id_card_no`, `full_name`, `violation_date`, `project_id`, `violation_type`, `reason`, `penalty_action`) VALUES
('037080999111', 'Trương Vi Phạm', '2024-09-15', 2, 'severe_safety', 'Không đeo dây đai an toàn khi thi công trên giáo cao 5m tại khu vực Cleanroom', 'Đuổi việc ngay lập tức, cấm cửa vĩnh viễn trên toàn công ty');

SET FOREIGN_KEY_CHECKS = 1;
