-- ==========================================================
-- TỆP SEED DATA V3 - POSUNG HRIS
-- Chứa dữ liệu mẫu (15 nhân sự đa dạng, 5 Users, 10 Menus, 3 Dự án trọng điểm)
-- ==========================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ── 1. ROLES & PERMISSIONS ──────────────────────────────
TRUNCATE TABLE `roles`;
INSERT INTO `roles` (`id`, `code`, `name`, `description`, `is_system`) VALUES
(1, 'super_admin', 'Quản trị tối cao', 'Toàn quyền hệ thống', 1),
(2, 'ui_admin', 'Admin Giao diện', 'Quản trị viên chuyên chỉnh sửa UI/Menu', 1),
(3, 'content_admin', 'Admin Nội dung', 'Quản trị viên nội dung HR', 0),
(4, 'site_manager', 'Giám đốc Dự án / Chỉ huy trưởng', 'Quản lý 1 hoặc nhiều Site', 0),
(5, 'qtvp', 'Quản trị văn phòng', 'Hành chính nhân sự', 0),
(6, 'cb_staff', 'Chuyên viên C&B', 'Chuyên viên tính lương', 0),
(7, 'employee', 'Nhân viên / Công nhân', 'Tài khoản nhân viên cơ bản', 1);

TRUNCATE TABLE `permissions`;
INSERT INTO `permissions` (`id`, `module_code`, `action_code`, `name`, `description`) VALUES
(1, 'employee', 'view', 'Xem nhân sự', 'Xem danh sách và chi tiết NV'),
(2, 'employee', 'create', 'Thêm nhân sự', 'Tạo hồ sơ NV mới'),
(3, 'employee', 'edit', 'Sửa nhân sự', 'Cập nhật hồ sơ 360 độ'),
(4, 'employee', 'delete', 'Xóa nhân sự', 'Xóa hồ sơ NV (Quản trị viên)'),
(5, 'employee', 'print_2c', 'In Sơ yếu lý lịch', 'In Sơ yếu lý lịch chuẩn 2C'),
(6, 'payroll', 'view', 'Xem bảng lương', 'Xem bảng công và lương'),
(7, 'payroll', 'calculate', 'Tính lương', 'Chạy engine tính lương động'),
(8, 'recruitment', 'manage', 'Quản lý tuyển dụng', 'Quản lý yêu cầu và ứng viên'),
(9, 'system_menu', 'manage', 'Quản lý hệ thống', 'Quản lý danh mục và menu');

TRUNCATE TABLE `role_permissions`;
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES
(1, 1), (1, 2), (1, 3), (1, 4), (1, 5), (1, 6), (1, 7), (1, 8), (1, 9), -- Super Admin all
(6, 1), (6, 6), (6, 7), -- C&B Staff
(5, 1), (5, 2), (5, 3), (5, 5); -- QTVP

-- ── 2. HỆ THỐNG MENU (10 MENUS) ──────────────────────────
TRUNCATE TABLE `system_menus`;
INSERT INTO `system_menus` (`id`, `parent_id`, `title`, `url`, `icon`, `sort_order`, `is_active`, `permission_required`) VALUES
(1, NULL, 'Tổng quan', 'dashboard', 'fas fa-chart-pie', 10, 1, NULL),
(2, NULL, 'Nhân sự & Chuyên gia', 'employee', 'fas fa-users', 20, 1, 'employee.view'),
(3, 2, 'Hồ sơ Nhân sự', 'employee', 'fas fa-id-card', 21, 1, 'employee.view'),
(4, 2, 'Tuyển dụng', 'recruitment', 'fas fa-user-plus', 22, 1, 'recruitment.manage'),
(5, NULL, 'Tiền lương', 'payroll', 'fas fa-money-check-dollar', 30, 1, 'payroll.view'),
(6, 5, 'Chấm công', 'payroll/timesheet', 'fas fa-clock', 31, 1, 'payroll.view'),
(7, 5, 'Tính lương', 'payroll', 'fas fa-calculator', 32, 1, 'payroll.calculate'),
(8, NULL, 'Dự án & Tổ chức', 'project', 'fas fa-hard-hat', 40, 1, NULL),
(9, NULL, 'Hệ Chuyên gia (AI)', 'ai', 'fas fa-brain', 50, 1, NULL),
(10, NULL, 'Hệ thống (Admin)', 'category', 'fas fa-cogs', 90, 1, 'system_menu.manage');

-- ── 3. PROJECTS & DEPARTMENTS & POSITIONS ────────────────
TRUNCATE TABLE `projects`;
INSERT INTO `projects` (`id`, `project_code`, `name`, `client_name`, `location`, `status`, `cost_center_code`, `headcount_budget`) VALUES
(1, 'PRJ_AMKOR', 'Nhà máy Amkor Technology', 'Amkor', 'Bắc Ninh', 'Active', 'CC_AMKOR_01', 500),
(2, 'PRJ_STARLAKE', 'Daewoo Starlake Tây Hồ Tây', 'Daewoo', 'Hà Nội', 'Active', 'CC_STARLAKE', 300),
(3, 'PRJ_SEHC_HCM', 'Samsung CE Complex (SEHC)', 'Samsung', 'TP.HCM', 'Active', 'CC_SEHC', 800);

TRUNCATE TABLE `departments`;
INSERT INTO `departments` (`id`, `code`, `name`, `type`) VALUES
(1, 'BOD', 'Ban Giám Đốc', 'office'),
(2, 'HR_ADMIN', 'Phòng Hành Chính Nhân Sự', 'office'),
(3, 'FINANCE', 'Phòng Kế Toán Tài Chính', 'office'),
(4, 'ME_BIM', 'Khối Kỹ Thuật (ME & BIM)', 'office'),
(5, 'SPOOL_FACTORY', 'Xưởng Spool Tự động', 'factory');

TRUNCATE TABLE `positions`;
INSERT INTO `positions` (`id`, `code`, `title`, `grade_level`, `base_salary_min`, `base_salary_max`) VALUES
(1, 'DIR', 'Giám Đốc', 10, 50000000, 100000000),
(2, 'MGR', 'Trưởng Phòng', 8, 25000000, 50000000),
(3, 'SUP', 'Giám sát Công trường', 6, 15000000, 30000000),
(4, 'BIM_ENG', 'Kỹ sư BIM', 5, 12000000, 25000000),
(5, 'WELDER_6G', 'Thợ hàn 6G', 4, 15000000, 35000000),
(6, 'WORKER', 'Lao động phổ thông', 1, 6000000, 10000000);

-- ── 4. NHÂN VIÊN MẪU (15 EMPLOYEES) ──────────────────────
TRUNCATE TABLE `employees`;
INSERT INTO `employees` (`id`, `employee_code`, `full_name`, `gender`, `is_expat`, `nationality`, `department_id`, `current_project_id`, `position_id`, `employee_status`) VALUES
-- Expat Management Team
(1, 'PS0001', 'Park Seong Taek', 'Male', 1, 'Korea', 1, 3, 1, 'active'),
(2, 'PS0002', 'Kim Jung Hoon', 'Male', 1, 'Korea', 4, 1, 2, 'active'),

-- Office / BIM Engineers
(3, 'PS0003', 'Nguyễn Thị Hoa', 'Female', 0, 'Vietnam', 2, NULL, 2, 'active'), -- HR Manager
(4, 'PS0004', 'Trần Văn Bình', 'Male', 0, 'Vietnam', 3, NULL, 2, 'active'),   -- Finance Manager
(5, 'PS0005', 'Lê Kỹ Sư BIM 1', 'Male', 0, 'Vietnam', 4, NULL, 4, 'active'),
(6, 'PS0006', 'Phạm Kỹ Sư BIM 2', 'Female', 0, 'Vietnam', 4, NULL, 4, 'active'),

-- Site Supervisors
(7, 'PS0007', 'Đinh Giám Sát Amkor', 'Male', 0, 'Vietnam', NULL, 1, 3, 'active'),
(8, 'PS0008', 'Vũ Giám Sát SEHC', 'Male', 0, 'Vietnam', NULL, 3, 3, 'active'),

-- Direct Workers (Welders & Workers)
(9, 'PS0009', 'Lý Thợ Hàn 6G Tốt', 'Male', 0, 'Vietnam', 5, 1, 5, 'active'),
(10, 'PS0010', 'Bùi Thợ Hàn 6G Siêu', 'Male', 0, 'Vietnam', 5, 3, 5, 'active'),
(11, 'PS0011', 'Trương Công Nhân 1', 'Male', 0, 'Vietnam', 5, 1, 6, 'active'),
(12, 'PS0012', 'Ngô Công Nhân 2', 'Male', 0, 'Vietnam', 5, 2, 6, 'active'),
(13, 'PS0013', 'Dương Công Nhân 3', 'Female', 0, 'Vietnam', 5, 2, 6, 'active'),
(14, 'PS0014', 'Hoàng Công Nhân 4', 'Male', 0, 'Vietnam', 5, 3, 6, 'active'),

-- HSE Blacklisted Employee (Bị đình chỉ / Cấm vào công trường)
(15, 'PS0015', 'Tạ Vi Phạm An Toàn', 'Male', 0, 'Vietnam', 5, 1, 6, 'blocked_hse');

-- ── 5. THÔNG TIN EXPAT & CHỨNG CHỈ (2C & M&E) ────────────
UPDATE `employees` SET 
    `passport_no` = 'M12345678', `passport_expiry` = '2030-12-31', 
    `work_permit_no` = 'WP-2025-HN', `work_permit_expiry` = '2025-10-10' 
WHERE `id` = 1;

TRUNCATE TABLE `employee_certificates`;
INSERT INTO `employee_certificates` (`employee_id`, `certificate_name`, `is_mandatory_for_site`) VALUES
(9, 'Chứng chỉ Hàn 6G Quốc tế (AWS)', 1),
(10, 'Chứng chỉ Hàn 6G (Amkor Approved)', 1),
(7, 'An toàn lao động Nhóm 3 (Giám sát)', 1),
(8, 'An toàn lao động Nhóm 3 (Giám sát)', 1);

-- ── 6. HSE BLACKLIST (Cho Nhân sự #15) ──────────────────
TRUNCATE TABLE `hse_blacklists`;
INSERT INTO `hse_blacklists` (`id_card_no`, `full_name`, `violation_date`, `project_id`, `violation_type`, `reason`, `penalty_action`) VALUES
('001202029302', 'Tạ Vi Phạm An Toàn', '2024-05-10', 1, 'severe_safety', 'Hút thuốc trong phòng sạch Amkor', 'Đuổi việc, cấm cửa vĩnh viễn hệ thống Samsung/Amkor');

-- ── 7. NGƯỜI DÙNG (5 USERS) ──────────────────────────────
TRUNCATE TABLE `users`;
-- Mật khẩu mặc định là '123456' (Hash theo bcrypt)
-- Thay thế password hash bằng chuỗi hash thực tế của '123456' ($2y$10$wT0lV3z...)
INSERT INTO `users` (`id`, `username`, `password_hash`, `employee_id`, `role_id`, `email`, `status`) VALUES
(1, 'admin', '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', 1, 1, 'admin@posung.com', 'active'),    -- Super Admin
(2, 'hrmanager', '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', 3, 5, 'hr@posung.com', 'active'),        -- QTVP
(3, 'cbstaff', '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', 4, 6, 'cb@posung.com', 'active'),          -- CB Staff
(4, 'sitemanager1', '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', 7, 4, 'sm1@posung.com', 'active'),    -- Site Manager
(5, 'user_worker', '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', 9, 7, 'worker9@posung.com', 'active'); -- Normal Employee

SET FOREIGN_KEY_CHECKS = 1;
