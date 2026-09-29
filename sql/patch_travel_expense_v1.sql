-- ============================================================
-- POSUNG HRIS - PATCH TRAVEL & EXPENSES V1
-- Module Quản lý Đề xuất Công tác & Quyết toán Chi phí
-- ============================================================

-- 1. Bảng Đề xuất Công tác (travel_requests)
CREATE TABLE IF NOT EXISTS `travel_requests` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `request_code` VARCHAR(50) NOT NULL UNIQUE,
    `employee_id` INT NOT NULL,
    `purpose` TEXT NOT NULL,
    `from_location` VARCHAR(255) NOT NULL,
    `to_location` VARCHAR(255) NOT NULL,
    `departure_date` DATE NOT NULL,
    `return_date` DATE NOT NULL,
    `project_id` INT DEFAULT NULL,
    `estimated_budget` DECIMAL(15,2) DEFAULT 0.00,
    `advance_amount` DECIMAL(15,2) DEFAULT 0.00,
    `transport_type` VARCHAR(100) DEFAULT 'Xe công ty',
    `accommodation` VARCHAR(255) DEFAULT 'Khách sạn',
    `status` ENUM('Draft', 'Pending', 'Approved', 'Rejected', 'Completed') DEFAULT 'Pending',
    `approved_by` INT DEFAULT NULL,
    `approved_date` DATETIME DEFAULT NULL,
    `rejected_reason` TEXT DEFAULT NULL,
    `notes` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_travel_req_emp` FOREIGN KEY (`employee_id`) REFERENCES `employees`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_travel_req_prj` FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_travel_req_approver` FOREIGN KEY (`approved_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Bảng Bảng Quyết toán Chi phí (expense_claims)
CREATE TABLE IF NOT EXISTS `expense_claims` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `claim_code` VARCHAR(50) NOT NULL UNIQUE,
    `employee_id` INT NOT NULL,
    `travel_request_id` INT DEFAULT NULL,
    `project_id` INT DEFAULT NULL,
    `title` VARCHAR(255) NOT NULL,
    `category` ENUM('Travel', 'Office', 'Project', 'Other') DEFAULT 'Travel',
    `total_amount` DECIMAL(15,2) DEFAULT 0.00,
    `advance_deducted` DECIMAL(15,2) DEFAULT 0.00,
    `net_payable` DECIMAL(15,2) DEFAULT 0.00,
    `status` ENUM('Draft', 'Submitted', 'Approved', 'Rejected', 'Paid') DEFAULT 'Submitted',
    `submitted_date` DATE NOT NULL,
    `approved_by` INT DEFAULT NULL,
    `approved_date` DATETIME DEFAULT NULL,
    `rejected_reason` TEXT DEFAULT NULL,
    `paid_date` DATE DEFAULT NULL,
    `paid_by` INT DEFAULT NULL,
    `payment_method` VARCHAR(50) DEFAULT 'Bank Transfer',
    `notes` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_expense_claim_emp` FOREIGN KEY (`employee_id`) REFERENCES `employees`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_expense_claim_req` FOREIGN KEY (`travel_request_id`) REFERENCES `travel_requests`(`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_expense_claim_prj` FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_expense_claim_approver` FOREIGN KEY (`approved_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_expense_claim_payer` FOREIGN KEY (`paid_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Bảng Chi tiết các Khoản chi (expense_items)
CREATE TABLE IF NOT EXISTS `expense_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `claim_id` INT NOT NULL,
    `description` VARCHAR(255) NOT NULL,
    `category` ENUM('Transport', 'Hotel', 'Meal', 'Fuel', 'Material', 'Other') DEFAULT 'Transport',
    `amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `expense_date` DATE NOT NULL,
    `receipt_path` VARCHAR(255) DEFAULT NULL,
    `notes` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_expense_item_claim` FOREIGN KEY (`claim_id`) REFERENCES `expense_claims`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Bổ sung Quyền trong bảng permissions
INSERT INTO `permissions` (`module_code`, `action_code`, `name`, `description`)
VALUES
('expense', 'view', 'Xem công tác & chi phí', 'Xem danh sách đề xuất công tác và bảng quyết toán chi phí'),
('expense', 'create', 'Tạo đề xuất & quyết toán', 'Lập kế hoạch công tác và lập bảng quyết toán chi phí'),
('expense', 'approve', 'Phê duyệt công tác & chi phí', 'Phê duyệt hồ sơ công tác và bảng thanh toán chi phí'),
('expense', 'pay', 'Chi trả tiền quyết toán', 'Xác nhận giải ngân / hoàn trả chi phí công tác cho nhân viên'),
('expense', 'report', 'Báo cáo chi phí công tác', 'Xem báo cáo thống kê chi phí công tác theo dự án và phòng ban')
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `description`=VALUES(`description`);

-- 5. Gán quyền cho các Roles
-- Super Admin (id=1): toàn quyền
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 1, p.id FROM `permissions` p WHERE p.module_code = 'expense';

-- Site Manager (id=4): view, create, approve, report
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 4, p.id FROM `permissions` p WHERE p.module_code = 'expense' AND p.action_code IN ('view', 'create', 'approve', 'report');

-- QTVP (id=5): view, create, approve, pay, report
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 5, p.id FROM `permissions` p WHERE p.module_code = 'expense' AND p.action_code IN ('view', 'create', 'approve', 'pay', 'report');

-- C&B Staff (id=6): view, create, approve, pay, report
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 6, p.id FROM `permissions` p WHERE p.module_code = 'expense' AND p.action_code IN ('view', 'create', 'approve', 'pay', 'report');

-- Employee (id=7): view, create
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 7, p.id FROM `permissions` p WHERE p.module_code = 'expense' AND p.action_code IN ('view', 'create');

-- 6. Thêm Menu vào system_menus
INSERT INTO `system_menus` (`parent_id`, `title`, `url`, `icon`, `sort_order`, `is_active`, `permission_required`)
VALUES
(NULL, 'Công tác & Chi phí', 'expense', 'fas fa-plane-departure', 43, 1, 'expense.view')
ON DUPLICATE KEY UPDATE `title`=VALUES(`title`), `icon`=VALUES(`icon`), `sort_order`=VALUES(`sort_order`);

-- 7. Seed dữ liệu mẫu (Sample data)
-- Chuyến công tác 1: Nguyễn Văn Tổng đi dự án Samsung SEVM (Đã hoàn tất)
INSERT INTO `travel_requests` (
    `request_code`, `employee_id`, `purpose`, `from_location`, `to_location`, 
    `departure_date`, `return_date`, `project_id`, `estimated_budget`, `advance_amount`, 
    `transport_type`, `accommodation`, `status`, `approved_by`, `approved_date`, `notes`
) VALUES (
    'TR-2026-0001', 1, 'Chỉ đạo nghiệm thu hệ thống phòng sạch và làm việc với BQL Dự án Samsung', 
    'Hà Nội (Trụ sở)', 'Bắc Ninh (Công trường SEVM)', 
    '2026-08-10', '2026-08-13', 1, 8500000.00, 5000000.00, 
    'Xe ô tô công ty', 'Khách sạn Mường Thanh Luxury Bắc Ninh', 
    'Completed', 102, '2026-08-08 10:00:00', 'Đã hoàn thành công tác và lập quyết toán EXP-2026-0001'
) ON DUPLICATE KEY UPDATE `request_code`=`request_code`;

-- Chuyến công tác 2: Lê Văn Sự đi dự án Lọc Hóa Dầu Nghi Sơn (Đã duyệt)
INSERT INTO `travel_requests` (
    `request_code`, `employee_id`, `purpose`, `from_location`, `to_location`, 
    `departure_date`, `return_date`, `project_id`, `estimated_budget`, `advance_amount`, 
    `transport_type`, `accommodation`, `status`, `approved_by`, `approved_date`, `notes`
) VALUES (
    'TR-2026-0002', 3, 'Khảo sát hiện trường lắp đặt hệ thống đường ống công nghệ và bảo hộ HSE', 
    'Hà Nội', 'Nghi Sơn - Thanh Hóa', 
    '2026-09-15', '2026-09-19', 2, 12000000.00, 7000000.00, 
    'Tàu hỏa / Xe khách', 'Nhà nghỉ chuyên gia dự án', 
    'Approved', 102, '2026-09-12 14:20:00', 'Đã chuyển tạm ứng 7.000.000đ qua tài khoản ngân hàng'
) ON DUPLICATE KEY UPDATE `request_code`=`request_code`;

-- Chuyến công tác 3: Hoàng Văn Kế đi kiểm toán công trình Thái Bình (Chờ duyệt)
INSERT INTO `travel_requests` (
    `request_code`, `employee_id`, `purpose`, `from_location`, `to_location`, 
    `departure_date`, `return_date`, `project_id`, `estimated_budget`, `advance_amount`, 
    `transport_type`, `accommodation`, `status`, `notes`
) VALUES (
    'TR-2026-0003', 5, 'Kiểm kê vật tư công trình và kiểm tra tiến độ thi công Nhiệt điện Thái Bình', 
    'Hà Nội', 'Thái Bình', 
    '2026-10-02', '2026-10-04', 3, 4500000.00, 3000000.00, 
    'Xe khách chất lượng cao', 'Khách sạn Dầu Khí Thái Bình', 
    'Pending', 'Đề xuất tạm ứng 3.000.000đ chi phí đi lại và ăn ở'
) ON DUPLICATE KEY UPDATE `request_code`=`request_code`;

-- Bảng quyết toán 1: Quyết toán chuyến công tác TR-2026-0001 (Đã chi trả)
INSERT INTO `expense_claims` (
    `claim_code`, `employee_id`, `travel_request_id`, `project_id`, `title`, 
    `category`, `total_amount`, `advance_deducted`, `net_payable`, `status`, 
    `submitted_date`, `approved_by`, `approved_date`, `paid_date`, `paid_by`, `payment_method`, `notes`
) VALUES (
    'EXP-2026-0001', 1, 1, 1, 'Quyết toán công tác dự án Samsung SEVM (10/08 - 13/08/2026)', 
    'Travel', 7850000.00, 5000000.00, 2850000.00, 'Paid', 
    '2026-08-15', 102, '2026-08-16 11:30:00', '2026-08-17', 102, 'Bank Transfer', 'Chi trả chuyển khoản phần chênh lệch 2.850.000đ'
) ON DUPLICATE KEY UPDATE `claim_code`=`claim_code`;

-- Các hạng mục chi tiết của EXP-2026-0001
INSERT INTO `expense_items` (`claim_id`, `description`, `category`, `amount`, `expense_date`, `receipt_path`, `notes`)
SELECT c.id, 'Vé xe Limousine Hà Nội - Bắc Ninh khứ hồi', 'Transport', 600000.00, '2026-08-10', NULL, 'Hóa đơn điện tử số HD-9921'
FROM `expense_claims` c WHERE c.claim_code = 'EXP-2026-0001' AND NOT EXISTS (
    SELECT 1 FROM `expense_items` i WHERE i.claim_id = c.id AND i.category = 'Transport'
);

INSERT INTO `expense_items` (`claim_id`, `description`, `category`, `amount`, `expense_date`, `receipt_path`, `notes`)
SELECT c.id, 'Tiền phòng khách sạn Mường Thanh Bắc Ninh (3 đêm)', 'Hotel', 3600000.00, '2026-08-13', NULL, 'Hóa đơn GTGT VAT 8%'
FROM `expense_claims` c WHERE c.claim_code = 'EXP-2026-0001' AND NOT EXISTS (
    SELECT 1 FROM `expense_items` i WHERE i.claim_id = c.id AND i.category = 'Hotel'
);

INSERT INTO `expense_items` (`claim_id`, `description`, `category`, `amount`, `expense_date`, `receipt_path`, `notes`)
SELECT c.id, 'Tiếp khách Ban Quản lý & Chuyên gia Hàn Quốc', 'Meal', 2400000.00, '2026-08-11', NULL, 'Kèm bill chi tiết nhà hàng'
FROM `expense_claims` c WHERE c.claim_code = 'EXP-2026-0001' AND NOT EXISTS (
    SELECT 1 FROM `expense_items` i WHERE i.claim_id = c.id AND i.category = 'Meal'
);

INSERT INTO `expense_items` (`claim_id`, `description`, `category`, `amount`, `expense_date`, `receipt_path`, `notes`)
SELECT c.id, 'Phụ cấp lưu trú & đi lại nội bộ công trường', 'Other', 1250000.00, '2026-08-13', NULL, 'Theo định mức công tác phí công ty'
FROM `expense_claims` c WHERE c.claim_code = 'EXP-2026-0001' AND NOT EXISTS (
    SELECT 1 FROM `expense_items` i WHERE i.claim_id = c.id AND i.category = 'Other'
);

-- Bảng quyết toán 2: Mua vật tư cơ điện khẩn cấp công trường (Đã duyệt, chờ chi)
INSERT INTO `expense_claims` (
    `claim_code`, `employee_id`, `travel_request_id`, `project_id`, `title`, 
    `category`, `total_amount`, `advance_deducted`, `net_payable`, `status`, 
    `submitted_date`, `approved_by`, `approved_date`, `notes`
) VALUES (
    'EXP-2026-0002', 3, NULL, 1, 'Mua cáp điện và vật tư hàn khẩn cấp tại công trường SEVM', 
    'Project', 3200000.00, 0.00, 3200000.00, 'Approved', 
    '2026-09-20', 102, '2026-09-22 15:45:00', 'Đã duyệt thanh toán, kế toán chuyển khoản thủ quỹ'
) ON DUPLICATE KEY UPDATE `claim_code`=`claim_code`;

INSERT INTO `expense_items` (`claim_id`, `description`, `category`, `amount`, `expense_date`, `receipt_path`, `notes`)
SELECT c.id, 'Cuộn cáp hàn chịu tải 50m tại chợ cơ điện', 'Material', 2200000.00, '2026-09-19', NULL, 'Biên nhận mua hàng đại lý Minh Phát'
FROM `expense_claims` c WHERE c.claim_code = 'EXP-2026-0002' AND NOT EXISTS (
    SELECT 1 FROM `expense_items` i WHERE i.claim_id = c.id AND i.category = 'Material'
);

INSERT INTO `expense_items` (`claim_id`, `description`, `category`, `amount`, `expense_date`, `receipt_path`, `notes`)
SELECT c.id, 'Xăng xe vận chuyển vật tư về lán điều hành', 'Fuel', 1000000.00, '2026-09-20', NULL, 'Hóa đơn cây xăng Petrolimex'
FROM `expense_claims` c WHERE c.claim_code = 'EXP-2026-0002' AND NOT EXISTS (
    SELECT 1 FROM `expense_items` i WHERE i.claim_id = c.id AND i.category = 'Fuel'
);
