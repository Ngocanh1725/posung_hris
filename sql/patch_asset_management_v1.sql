-- ============================================================
-- POSUNG HRIS - SQL PATCH: Module Quản lý Tài sản Công ty
-- Version: v1.0
-- Created: 2026-09-28
-- Description:
--   1. Tạo bảng asset_categories (Danh mục loại tài sản)
--   2. Tạo bảng assets (Danh sách tài sản công ty)
--   3. Tạo bảng asset_assignments (Lịch sử cấp phát và thu hồi tài sản)
--   4. Thêm permissions và system_menus cho Module Asset
--   5. Seed dữ liệu danh mục & tài sản mẫu
-- ============================================================

USE `posung_hris`;

-- 1. BẢNG DANH MỤC LOẠI TÀI SẢN
CREATE TABLE IF NOT EXISTS `asset_categories` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `category_code` VARCHAR(50) NOT NULL UNIQUE,
    `name` VARCHAR(150) NOT NULL,
    `description` TEXT NULL,
    `icon` VARCHAR(50) DEFAULT 'fas fa-box',
    `status` ENUM('Active', 'Inactive') DEFAULT 'Active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. BẢNG TÀI SẢN CÔNG TY (ASSETS)
CREATE TABLE IF NOT EXISTS `assets` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `asset_code` VARCHAR(50) NOT NULL UNIQUE COMMENT 'Mã quản lý tài sản, vd: AST-IT-0001',
    `name` VARCHAR(255) NOT NULL COMMENT 'Tên tài sản, model',
    `category_id` INT(11) NOT NULL COMMENT 'Loại tài sản',
    `serial_number` VARCHAR(100) NULL COMMENT 'Số serial / Service Tag / IMEI',
    `purchase_date` DATE NULL COMMENT 'Ngày mua sắm',
    `purchase_cost` DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Nguyên giá tài sản',
    `warranty_expiry` DATE NULL COMMENT 'Hạn bảo hành',
    `condition` ENUM('New', 'Good', 'Fair', 'Damaged', 'Disposed') DEFAULT 'Good' COMMENT 'Tình trạng vật lý',
    `status` ENUM('Available', 'Assigned', 'Maintenance', 'Disposed') DEFAULT 'Available' COMMENT 'Trạng thái cấp phát',
    `location` VARCHAR(255) NULL COMMENT 'Vị trí đặt tài sản / Phòng ban / Kho',
    `project_id` INT(11) NULL COMMENT 'Dự án đang sử dụng tài sản (nếu có)',
    `notes` TEXT NULL COMMENT 'Ghi chú kỹ thuật, cấu hình, v.v.',
    `image_path` VARCHAR(255) NULL COMMENT 'Hình ảnh tài sản',
    `created_by` INT(11) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_asset_code` (`asset_code`),
    INDEX `idx_category_id` (`category_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_project_id` (`project_id`),
    CONSTRAINT `fk_assets_category` FOREIGN KEY (`category_id`) REFERENCES `asset_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. BẢNG CẤP PHÁT & THU HỒI TÀI SẢN (ASSET_ASSIGNMENTS)
CREATE TABLE IF NOT EXISTS `asset_assignments` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `asset_id` INT(11) NOT NULL,
    `employee_id` INT(11) NOT NULL,
    `assigned_date` DATE NOT NULL COMMENT 'Ngày bàn giao tài sản',
    `return_date` DATE NULL COMMENT 'Ngày thu hồi (NULL nếu đang giữ)',
    `assigned_by` INT(11) NULL COMMENT 'Người lập biên bản bàn giao (Admin/HR)',
    `returned_to` INT(11) NULL COMMENT 'Người nhận thu hồi (Admin/HR)',
    `condition_on_assign` ENUM('New', 'Good', 'Fair', 'Damaged') DEFAULT 'Good' COMMENT 'Tình trạng lúc giao',
    `condition_on_return` ENUM('New', 'Good', 'Fair', 'Damaged') NULL COMMENT 'Tình trạng lúc trả',
    `notes` TEXT NULL COMMENT 'Ghi chú bàn giao / Lý do thu hồi / Phụ kiện kèm theo',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_assign_asset` (`asset_id`),
    INDEX `idx_assign_employee` (`employee_id`),
    INDEX `idx_assign_return_date` (`return_date`),
    CONSTRAINT `fk_assignments_asset` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_assignments_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. BỔ SUNG PERMISSIONS
INSERT INTO `permissions` (`module_code`, `action_code`, `name`, `description`)
SELECT 'asset', 'view', 'Xem tài sản', 'Xem danh mục và danh sách tài sản công ty'
WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE `module_code` = 'asset' AND `action_code` = 'view');

INSERT INTO `permissions` (`module_code`, `action_code`, `name`, `description`)
SELECT 'asset', 'create', 'Thêm tài sản', 'Tạo mới tài sản và danh mục'
WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE `module_code` = 'asset' AND `action_code` = 'create');

INSERT INTO `permissions` (`module_code`, `action_code`, `name`, `description`)
SELECT 'asset', 'edit', 'Sửa tài sản', 'Cập nhật thông tin tài sản'
WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE `module_code` = 'asset' AND `action_code` = 'edit');

INSERT INTO `permissions` (`module_code`, `action_code`, `name`, `description`)
SELECT 'asset', 'delete', 'Xóa tài sản', 'Xóa bỏ tài sản'
WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE `module_code` = 'asset' AND `action_code` = 'delete');

INSERT INTO `permissions` (`module_code`, `action_code`, `name`, `description`)
SELECT 'asset', 'assign', 'Giao & Thu hồi tài sản', 'Thực hiện bàn giao và thu hồi tài sản nhân viên'
WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE `module_code` = 'asset' AND `action_code` = 'assign');

INSERT INTO `permissions` (`module_code`, `action_code`, `name`, `description`)
SELECT 'asset', 'report', 'Báo cáo tài sản', 'Xem báo cáo thống kê kiểm kê tài sản'
WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE `module_code` = 'asset' AND `action_code` = 'report');

-- Gán quyền cho Super Admin (role_id = 1) và HR Manager (role_id = 2 nếu có)
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 1, id FROM `permissions` WHERE `module_code` = 'asset';

-- 5. BỔ SUNG MENU VÀO SYSTEM_MENUS
INSERT INTO `system_menus` (`title`, `url`, `icon`, `sort_order`, `is_active`, `parent_id`, `permission_required`)
SELECT 'Tài sản Công ty', 'asset', 'fas fa-laptop-house', 44, 1, NULL, 'asset.view'
WHERE NOT EXISTS (SELECT 1 FROM `system_menus` WHERE `url` = 'asset' AND `parent_id` IS NULL);

-- Lấy ID của menu cha vừa tạo
SET @parent_asset_id = (SELECT `id` FROM `system_menus` WHERE `url` = 'asset' AND `parent_id` IS NULL LIMIT 1);

INSERT INTO `system_menus` (`title`, `url`, `icon`, `sort_order`, `is_active`, `parent_id`, `permission_required`)
SELECT 'Danh sách Tài sản', 'asset', 'fas fa-boxes-stacked', 1, 1, @parent_asset_id, 'asset.view'
WHERE NOT EXISTS (SELECT 1 FROM `system_menus` WHERE `url` = 'asset' AND `parent_id` = @parent_asset_id);

INSERT INTO `system_menus` (`title`, `url`, `icon`, `sort_order`, `is_active`, `parent_id`, `permission_required`)
SELECT 'Danh mục Loại tài sản', 'asset/categories', 'fas fa-tags', 2, 1, @parent_asset_id, 'asset.create'
WHERE NOT EXISTS (SELECT 1 FROM `system_menus` WHERE `url` = 'asset/categories' AND `parent_id` = @parent_asset_id);

INSERT INTO `system_menus` (`title`, `url`, `icon`, `sort_order`, `is_active`, `parent_id`, `permission_required`)
SELECT 'Báo cáo Kiểm kê & BI', 'asset/report', 'fas fa-chart-pie', 3, 1, @parent_asset_id, 'asset.report'
WHERE NOT EXISTS (SELECT 1 FROM `system_menus` WHERE `url` = 'asset/report' AND `parent_id` = @parent_asset_id);

-- 6. SEED DỮ LIỆU DANH MỤC TÀI SẢN
INSERT INTO `asset_categories` (`id`, `category_code`, `name`, `description`, `icon`, `status`) VALUES
(1, 'IT_EQUIPMENT', 'Thiết bị CNTT & Viễn thông', 'Laptop, màn hình, máy in, smartphone, bộ định tuyến, switch...', 'fas fa-laptop', 'Active'),
(2, 'VEHICLES', 'Phương tiện & Vận tải', 'Xe ô tô lãnh đạo, xe bán tải giám sát dự án, xe tải vận chuyển...', 'fas fa-car-side', 'Active'),
(3, 'CONSTRUCTION_MACHINERY', 'Thiết bị & Máy thi công', 'Máy thủy bình, máy kinh vĩ, máy đo laser, bộ đàm công trình...', 'fas fa-tools', 'Active'),
(4, 'FURNITURE', 'Đồ nội thất & Văn phòng phẩm', 'Bàn ghế làm việc, tủ hồ sơ tài liệu, két sắt, bàn họp...', 'fas fa-chair', 'Active'),
(5, 'ACCESS_SECURITY', 'Thẻ từ, Chìa khóa & Bảo mật', 'Thẻ vào cổng trụ sở, khóa từ, USB Token chữ ký số, chìa khóa kho...', 'fas fa-id-badge', 'Active')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- 7. SEED DỮ LIỆU TÀI SẢN MẪU
INSERT INTO `assets` (`id`, `asset_code`, `name`, `category_id`, `serial_number`, `purchase_date`, `purchase_cost`, `warranty_expiry`, `condition`, `status`, `location`, `notes`) VALUES
(1, 'AST-IT-0001', 'Laptop Dell Latitude 7420 (Core i7, 16GB, 512GB SSD)', 1, 'DL-7420-SN8821', '2025-06-15', 24500000.00, '2028-06-15', 'Good', 'Assigned', 'Phòng Tổng Giám Đốc / Văn phòng HN', 'Trang bị chuột không dây Logitech và túi chống sốc.'),
(2, 'AST-IT-0002', 'Màn hình chuyên dụng Dell UltraSharp 27 inch U2722D 2K', 1, 'DE-U27-99410', '2025-08-20', 9800000.00, '2028-08-20', 'Good', 'Available', 'Kho IT - Trụ sở', 'Kèm cáp HDMI và Type-C to DisplayPort.'),
(3, 'AST-IT-0003', 'Smartphone iPhone 14 Pro Max 256GB Deep Purple', 1, 'IMEI-358941098273615', '2025-01-10', 27900000.00, '2026-01-10', 'Good', 'Available', 'Phòng Hành chính - Quản trị', 'Điện thoại hotline dự án cấp cao.'),
(4, 'AST-VH-0001', 'Xe ô tô bán tải Ford Ranger Wildtrak 4x4 (BKS: 29C-889.92)', 2, 'CHASSIS-FR9921477', '2024-03-10', 860000000.00, '2027-03-10', 'Good', 'Assigned', 'Ban chỉ huy Dự án Posung Vina', 'Bảo dưỡng định kỳ 5.000km tại Ford Thăng Long.'),
(5, 'AST-EQ-0001', 'Bộ đàm kỹ thuật số Motorola GP328 Plus Chống nước', 3, 'MT-GP328-88301', '2025-02-15', 3400000.00, '2027-02-15', 'Good', 'Assigned', 'Công trường Dự án Posung', 'Tần số an toàn lao động kênh 1-4.'),
(6, 'AST-EQ-0002', 'Máy thủy chuẩn điện tử Leica NA730 Plus độ chính xác cao', 3, 'LC-NA730-10928', '2024-11-05', 13500000.00, '2026-11-05', 'Good', 'Available', 'Kho máy móc trắc đạc - Công trường', 'Đã hiệu chuẩn định kỳ tháng 12/2025.'),
(7, 'AST-SC-0001', 'Thẻ thông minh kiểm soát ra vào tòa nhà + Mã QR cá nhân', 5, 'TAG-HQ-001-ADMIN', '2025-01-01', 150000.00, NULL, 'Good', 'Assigned', 'Trụ sở chính Posung', 'Phân quyền ra vào toàn bộ các phòng ban và phòng máy chủ.'),
(8, 'AST-FN-0001', 'Ghế công thái học Herman Miller Aeron Ergonomic Chair', 4, 'HM-AERON-2024V', '2024-09-01', 28500000.00, '2034-09-01', 'Good', 'Available', 'Phòng họp VIP', 'Hàng nhập khẩu chính hãng.')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- 8. SEED DỮ LIỆU CẤP PHÁT & LỊCH SỬ THU HỒI
-- Giao tài sản cho nhân viên ID = 1 (Nguyễn Văn Tổng)
INSERT INTO `asset_assignments` (`id`, `asset_id`, `employee_id`, `assigned_date`, `return_date`, `assigned_by`, `returned_to`, `condition_on_assign`, `condition_on_return`, `notes`) VALUES
(1, 1, 1, '2025-06-20', NULL, 102, NULL, 'New', NULL, 'Cấp phát phục vụ công tác quản lý điều hành.'),
(2, 4, 1, '2025-07-01', NULL, 102, NULL, 'Good', NULL, 'Bàn giao xe ô tô bán tải đi công tác dự án và khảo sát hiện trường.'),
(3, 5, 1, '2025-07-01', NULL, 102, NULL, 'Good', NULL, 'Bộ đàm liên lạc với ban chỉ huy an toàn công trường.'),
(4, 7, 1, '2025-01-02', NULL, 102, NULL, 'New', NULL, 'Thẻ từ nhân sự cấp cao.')
ON DUPLICATE KEY UPDATE `notes` = VALUES(`notes`);
