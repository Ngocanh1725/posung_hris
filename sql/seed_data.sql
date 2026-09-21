-- ==========================================================
-- POSUNG HRIS - SEED DATA
-- ==========================================================
-- Script khởi tạo dữ liệu mẫu cho hệ thống.

-- 1. Departments
INSERT IGNORE INTO `departments` (`id`, `dept_code`, `dept_name`, `manager_id`, `created_at`, `updated_at`) VALUES
(1, 'BGD', 'Ban Giám đốc', NULL, NOW(), NOW()),
(2, 'HR', 'Hành chính Nhân sự', NULL, NOW(), NOW()),
(3, 'ACC', 'Tài chính Kế toán', NULL, NOW(), NOW()),
(4, 'MNE', 'Phòng Cơ điện (M&E)', NULL, NOW(), NOW()),
(5, 'CON', 'Khối Thi công', NULL, NOW(), NOW());

-- 2. Positions
INSERT IGNORE INTO `positions` (`id`, `pos_code`, `pos_title`, `allowance_rate`, `job_level`, `created_at`, `updated_at`) VALUES
(1, 'DIR', 'Tổng Giám đốc', 50.00, 10, NOW(), NOW()),
(2, 'HRM', 'Trưởng phòng Nhân sự', 20.00, 8, NOW(), NOW()),
(3, 'MEE', 'Kỹ sư M&E Cao cấp', 15.00, 7, NOW(), NOW()),
(4, 'SME', 'Chỉ huy trưởng', 25.00, 9, NOW(), NOW()),
(5, 'W6G', 'Thợ hàn 6G', 10.00, 4, NOW(), NOW());

-- 3. Projects
INSERT IGNORE INTO `projects` (`id`, `project_code`, `project_name`, `location`, `client_name`, `start_date`, `end_date`, `status`, `created_at`, `updated_at`) VALUES
(1, 'AMK01', 'Amkor Technology Giai đoạn 1', 'Bắc Ninh', 'Amkor', '2023-01-01', '2025-12-31', 'In_Progress', NOW(), NOW()),
(2, 'AMK02', 'Amkor Technology Giai đoạn 2', 'Bắc Ninh', 'Amkor', '2024-06-01', '2026-12-31', 'Planning', NOW(), NOW()),
(3, 'SEHC', 'Samsung Electronics HCMC CE Complex', 'TP.HCM', 'Samsung', '2022-01-01', '2024-12-31', 'In_Progress', NOW(), NOW()),
(4, 'STARLAKE', 'Starlake Tây Hồ Tây', 'Hà Nội', 'Daewoo', '2023-05-01', '2026-05-01', 'In_Progress', NOW(), NOW());

-- 4. Users (Admin Account)
-- Pass: admin123 -> $2y$10$Tnsz0zWiHd5Vm9BE5Sj65.YPu./EjZJCUB/9ypk8HIHe1jCRxcOsO
INSERT IGNORE INTO `users` (`id`, `username`, `password`, `full_name`, `email`, `role`, `status`, `created_at`, `updated_at`) VALUES
(1, 'admin', '$2y$10$Tnsz0zWiHd5Vm9BE5Sj65.YPu./EjZJCUB/9ypk8HIHe1jCRxcOsO', 'Quản trị Hệ thống', 'admin@posung.vn', 'Admin', 'Active', NOW(), NOW());
