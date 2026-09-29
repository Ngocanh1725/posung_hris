-- ============================================================
-- POSUNG HRIS - SQL PATCH CHO PHÂN HỆ ESS PORTAL (V1)
-- ============================================================

-- 1. Bổ sung quyền hạn cho ESS vào bảng permissions nếu chưa có
INSERT IGNORE INTO permissions (module_code, action_code, name, description) VALUES
('ess', 'view', 'Truy cập cổng ESS', 'Cho phép nhân viên đăng nhập vào cổng tự phục vụ ESS'),
('ess', 'profile', 'Quản lý hồ sơ cá nhân', 'Xem và cập nhật thông tin cá nhân trên ESS'),
('ess', 'leave', 'Đăng ký nghỉ phép', 'Nộp đơn xin nghỉ phép và theo dõi trạng thái đơn'),
('ess', 'attendance', 'Xem lịch sử chấm công', 'Xem chi tiết bảng công và giờ làm việc cá nhân'),
('ess', 'payroll', 'Xem phiếu lương', 'Xem và in phiếu lương hàng tháng cá nhân'),
('ess', 'contract', 'Xem hợp đồng lao động', 'Xem danh sách hợp đồng cá nhân'),
('ess', 'training', 'Xem lịch sử đào tạo', 'Xem danh sách các khóa đào tạo đã tham gia');

-- 2. Gán các quyền ESS cho Role Employee (ID = 7)
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 7, p.id FROM permissions p WHERE p.module_code = 'ess';

-- 3. Tạo bảng thông báo cá nhân cho nhân viên (employee_notifications)
CREATE TABLE IF NOT EXISTS employee_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info', 'success', 'warning', 'danger') DEFAULT 'info',
    link VARCHAR(255) NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_emp_read (employee_id, is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Tạo tài khoản mẫu cho nhân viên (nv001 / 123456) liên kết với employee_id = 1
-- Mật khẩu 123456 mã hóa bcrypt: $2y$10$e8w.x.sSGBvH9.vjT709/uN9Xf5n9V55zO4zB858/zU8kM0O5b9b6 (hoặc băm sinh bởi PHP)
INSERT INTO users (username, password_hash, employee_id, role_id, status, created_at)
VALUES ('nv001', '$2y$10$WqB3pG8e/7yF574N0sIu0.mZ7C4bF3u19W9yT6l5x8x4iXmZJk9mG', 1, 7, 'active', NOW())
ON DUPLICATE KEY UPDATE 
    employee_id = 1, 
    role_id = 7, 
    status = 'active';

-- 5. Thêm thông báo nội bộ mẫu vào bảng internal_articles nếu chưa có
INSERT INTO internal_articles (category, title, slug, content, author_id, status, published_at, created_at)
VALUES 
('notice', 'Chào mừng ra mắt Cổng tự phục vụ Nhân viên (POSUNG ESS Portal)', 'chao-mung-ra-mat-ess-portal', 
'<p>Ban Giám đốc và Phòng Nhân sự xin trân trọng thông báo ra mắt <strong>Cổng tự phục vụ Nhân viên (ESS Portal)</strong>.</p><p>Từ nay, toàn thể CBNV có thể chủ động:</p><ul><li>Tra cứu hồ sơ cá nhân và người phụ thuộc</li><li>Theo dõi chi tiết dữ liệu chấm công hàng ngày</li><li>Xem và in phiếu lương hàng tháng bảo mật</li><li>Nộp và theo dõi đơn xin nghỉ phép trực tuyến</li><li>Tra cứu lịch sử hợp đồng lao động và chứng chỉ đào tạo</li></ul><p>Mọi thắc mắc xin vui lòng liên hệ Phòng Nhân sự để được hỗ trợ kịp thời.</p>',
102, 'published', NOW(), NOW()),
('hse_rule', 'Nghiêm chỉnh tuân thủ quy định An toàn Lao động tại Công trường', 'nghiem-chinh-tuan-thu-quy-dinh-hse',
'<p>Để đảm bảo an toàn tuyệt đối tính mạng và sức khỏe cho người lao động tại các dự án nhà máy công nghiệp (Samsung, Amkor, Foxconn,...), toàn thể cán bộ công nhân viên bắt buộc tuân thủ:</p><ol><li>Luôn mang đầy đủ trang thiết bị bảo hộ lao động (mũ, kính, giày mũi sắt, áo phản quang).</li><li>Đeo dây an toàn đúng quy cách khi làm việc trên cao từ 2 mét trở lên.</li><li>Không hút thuốc, không sử dụng điện thoại tại các khu vực nguy hiểm.</li><li>Báo cáo ngay sự cố hoặc mối nguy tiềm ẩn cho Cán bộ HSE tại công trường.</li></ol>',
102, 'published', NOW(), NOW()),
('news', 'Thông báo Lịch làm việc và Nghỉ lễ năm 2026', 'thong-bao-lich-nghi-le-2026',
'<p>Phòng Nhân sự thông báo lịch làm việc, chế độ trực ca và lịch nghỉ lễ theo quy định của Bộ Luật Lao động và thỏa ước lao động Posung E&C. Các bộ phận chủ động sắp xếp nhân sự đảm bảo tiến độ công trường.</p>',
102, 'published', NOW(), NOW())
ON DUPLICATE KEY UPDATE title=VALUES(title);

-- 6. Tạo dữ liệu mẫu thông báo cá nhân cho nhân viên 1
INSERT INTO employee_notifications (employee_id, title, message, type, link, is_read, created_at)
VALUES 
(1, 'Phiếu lương tháng mới đã sẵn sàng', 'Phiếu lương của bạn đã được phòng Kế toán duyệt và cập nhật trên hệ thống.', 'success', 'ess/payslip', 0, NOW()),
(1, 'Nhắc nhở cập nhật thông tin cá nhân', 'Vui lòng kiểm tra và cập nhật số điện thoại, địa chỉ và thông tin người phụ thuộc trên cổng ESS.', 'info', 'ess/profile', 0, NOW());

-- 7. Thêm dữ liệu mẫu người phụ thuộc cho nhân viên 1 (nếu chưa có)
INSERT INTO dependents (employee_id, full_name, relationship, birth_date, id_number, is_tax_dependent, deduction_from)
SELECT 1, 'Nguyễn Minh Khôi', 'Con', '2018-05-12', '001201809988', 1, '2020-01-01'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM dependents WHERE employee_id = 1 AND full_name = 'Nguyễn Minh Khôi');

-- 8. Thêm dữ liệu mẫu đơn nghỉ phép cho nhân viên 1 (nếu chưa có)
INSERT INTO leave_requests (employee_id, leave_type_id, start_date, end_date, total_days, reason, status, approved_by, approved_at, created_at)
SELECT 1, 1, DATE_SUB(CURDATE(), INTERVAL 15 DAY), DATE_SUB(CURDATE(), INTERVAL 14 DAY), 2.0, 'Nghỉ giải quyết việc gia đình', 'Approved', 101, NOW(), DATE_SUB(NOW(), INTERVAL 16 DAY)
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM leave_requests WHERE employee_id = 1);
