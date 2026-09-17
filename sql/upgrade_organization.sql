-- ============================================================
--  POSUNG HRIS – Upgrade Organization Module
--  Mở rộng bảng departments: chức năng, loại hình, thông tin liên hệ
-- ============================================================

-- 1. Thêm các cột mới vào bảng departments
ALTER TABLE `departments`
  ADD COLUMN IF NOT EXISTS `description` TEXT DEFAULT NULL COMMENT 'Mô tả chi tiết chức năng bộ phận' AFTER `branch`,
  ADD COLUMN IF NOT EXISTS `functions` TEXT DEFAULT NULL COMMENT 'Danh sách chức năng chính (JSON array)' AFTER `description`,
  ADD COLUMN IF NOT EXISTS `manager_id` INT(10) UNSIGNED DEFAULT NULL COMMENT 'FK → employees.id – Trưởng bộ phận' AFTER `parent_id`,
  ADD COLUMN IF NOT EXISTS `phone` VARCHAR(30) DEFAULT NULL COMMENT 'Số điện thoại liên hệ bộ phận' AFTER `manager_id`,
  ADD COLUMN IF NOT EXISTS `email` VARCHAR(100) DEFAULT NULL COMMENT 'Email bộ phận' AFTER `phone`,
  ADD COLUMN IF NOT EXISTS `office_location` VARCHAR(200) DEFAULT NULL COMMENT 'Địa điểm văn phòng / công trình' AFTER `email`,
  ADD COLUMN IF NOT EXISTS `established_date` DATE DEFAULT NULL COMMENT 'Ngày thành lập bộ phận' AFTER `office_location`,
  ADD COLUMN IF NOT EXISTS `dept_type` ENUM('Division','Department','Team','Project') NOT NULL DEFAULT 'Department' COMMENT 'Loại hình tổ chức' AFTER `established_date`,
  ADD COLUMN IF NOT EXISTS `status` ENUM('Active','Inactive') NOT NULL DEFAULT 'Active' COMMENT 'Trạng thái hoạt động' AFTER `dept_type`,
  ADD COLUMN IF NOT EXISTS `sort_order` INT NOT NULL DEFAULT 0 COMMENT 'Thứ tự hiển thị' AFTER `status`;

-- 2. Thêm Foreign Key cho manager_id (nếu chưa có)
-- Kiểm tra trước, nếu FK đã tồn tại thì bỏ qua
SET @fk_exists = (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS 
    WHERE CONSTRAINT_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'departments' 
    AND CONSTRAINT_NAME = 'fk_dept_manager');

SET @sql = IF(@fk_exists = 0, 
    'ALTER TABLE `departments` ADD CONSTRAINT `fk_dept_manager` FOREIGN KEY (`manager_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL ON UPDATE CASCADE',
    'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 3. Cập nhật dữ liệu chi tiết cho các bộ phận hiện có
-- ── Ban Giám đốc ──
UPDATE `departments` SET
  `description` = 'Ban Giám đốc Công ty TNHH Cơ khí Kỹ thuật Xây dựng Po Sung. Chịu trách nhiệm điều hành toàn bộ hoạt động sản xuất kinh doanh, hoạch định chiến lược phát triển, quan hệ đối tác và đại diện pháp lý của công ty. Trực tiếp quản lý các phòng ban chức năng và giám sát tiến độ các dự án trọng điểm.',
  `functions` = '["Hoạch định chiến lược và kế hoạch phát triển dài hạn","Phê duyệt dự toán, hợp đồng và quyết định đầu tư","Điều phối nguồn lực giữa các dự án","Quan hệ đối tác với chủ đầu tư (Samsung, Amkor, Lotte...)","Đại diện pháp lý và ký kết văn bản quan trọng","Đánh giá hiệu quả hoạt động định kỳ (KPI)","Chủ trì họp Ban Giám đốc hàng tuần"]',
  `phone` = '024-3755-0001',
  `email` = 'info@posung.vn',
  `office_location` = 'Tầng 8, Tòa nhà Vinaconex Tower, 34 Láng Hạ, Ba Đình, Hà Nội',
  `established_date` = '2010-03-15',
  `dept_type` = 'Division',
  `status` = 'Active',
  `sort_order` = 1
WHERE `dept_code` = 'HQ';

-- ── Phòng Hành chính – Nhân sự ──
UPDATE `departments` SET
  `description` = 'Phòng Hành chính – Nhân sự chịu trách nhiệm quản lý toàn bộ công tác nhân sự, hành chính và đời sống người lao động. Bao gồm: tuyển dụng, đào tạo, quản lý hồ sơ lao động, chế độ BHXH/BHYT/BHTN, quan hệ lao động, và công tác hành chính văn phòng. Là đầu mối giải quyết thủ tục pháp lý cho chuyên gia nước ngoài (Expat).',
  `functions` = '["Tuyển dụng và onboarding nhân viên mới","Quản lý hồ sơ nhân sự (lý lịch, hợp đồng lao động)","Tính lương, BHXH, BHYT, BHTN, thuế TNCN","Đào tạo và phát triển năng lực nhân viên","Quản lý chế độ nghỉ phép, công tác phí","Thủ tục GPLĐ, Visa, TRC cho chuyên gia nước ngoài","Quản lý tài sản, văn phòng phẩm, xe công ty","Tổ chức sự kiện nội bộ (teambuilding, lễ tết)","Xây dựng nội quy, quy chế lao động"]',
  `phone` = '024-3755-0010',
  `email` = 'hr@posung.vn',
  `office_location` = 'Tầng 8, Tòa nhà Vinaconex Tower, 34 Láng Hạ, Ba Đình, Hà Nội',
  `established_date` = '2010-03-15',
  `dept_type` = 'Department',
  `status` = 'Active',
  `sort_order` = 2
WHERE `dept_code` = 'HQ-HC';

-- ── Phòng Kế toán – Tài chính ──
UPDATE `departments` SET
  `description` = 'Phòng Kế toán – Tài chính quản lý toàn bộ hoạt động tài chính, kế toán của công ty. Thực hiện hạch toán kế toán theo chuẩn mực VAS, lập báo cáo tài chính, quyết toán thuế, quản lý dòng tiền và kiểm soát chi phí dự án. Phối hợp chặt chẽ với các Ban Điều hành Dự án để phân bổ chi phí theo cost center.',
  `functions` = '["Hạch toán kế toán theo chuẩn mực VAS","Lập báo cáo tài chính định kỳ (tháng, quý, năm)","Quyết toán thuế TNDN, thuế GTGT, thuế TNCN","Quản lý dòng tiền, tài khoản ngân hàng","Kiểm soát chi phí theo Cost Center (dự án)","Lập bảng lương và chi trả lương nhân viên","Thanh toán nhà cung cấp, nhà thầu phụ","Quản lý hóa đơn đầu vào / đầu ra","Phối hợp kiểm toán nội bộ và kiểm toán độc lập"]',
  `phone` = '024-3755-0020',
  `email` = 'finance@posung.vn',
  `office_location` = 'Tầng 8, Tòa nhà Vinaconex Tower, 34 Láng Hạ, Ba Đình, Hà Nội',
  `established_date` = '2010-03-15',
  `dept_type` = 'Department',
  `status` = 'Active',
  `sort_order` = 3
WHERE `dept_code` = 'HQ-KT';

-- ── Phòng Kinh doanh – Dự toán ──
UPDATE `departments` SET
  `description` = 'Phòng Kinh doanh – Dự toán đảm nhiệm công tác phát triển thị trường, đấu thầu và lập dự toán công trình. Tìm kiếm và mở rộng cơ hội kinh doanh trong lĩnh vực M&E (cơ điện), cơ khí chế tạo, lắp đặt hệ thống HVAC, piping, và sản xuất spool. Lập hồ sơ dự thầu, đàm phán và ký kết hợp đồng với chủ đầu tư.',
  `functions` = '["Tìm kiếm và phát triển khách hàng tiềm năng","Lập hồ sơ năng lực, hồ sơ dự thầu","Dự toán khối lượng và chi phí công trình (BoQ)","Đàm phán hợp đồng, điều khoản thương mại","Quản lý mối quan hệ khách hàng (CRM)","Theo dõi tiến độ nghiệm thu và thanh toán","Phân tích thị trường và đối thủ cạnh tranh","Báo cáo doanh thu, pipeline dự án"]',
  `phone` = '024-3755-0030',
  `email` = 'sales@posung.vn',
  `office_location` = 'Tầng 8, Tòa nhà Vinaconex Tower, 34 Láng Hạ, Ba Đình, Hà Nội',
  `established_date` = '2010-03-15',
  `dept_type` = 'Department',
  `status` = 'Active',
  `sort_order` = 4
WHERE `dept_code` = 'HQ-KD';

-- ── Phòng Kỹ thuật – Thiết kế BIM ──
UPDATE `departments` SET
  `description` = 'Phòng Kỹ thuật – Thiết kế (BIM) chịu trách nhiệm thiết kế kỹ thuật, triển khai bản vẽ thi công (shop drawing) và ứng dụng BIM (Building Information Modeling) cho các dự án. Đảm bảo chất lượng kỹ thuật và tuân thủ tiêu chuẩn quốc tế (ASME, AWS, JIS). Hỗ trợ kỹ thuật cho các Ban Điều hành Dự án tại công trường.',
  `functions` = '["Thiết kế bản vẽ thi công (Shop Drawing) hệ thống M&E","Mô hình hóa 3D/BIM (Revit, Tekla, AutoCAD)","Phối hợp giải quyết xung đột kỹ thuật (Clash Detection)","Lập phương án thi công (Method Statement)","Kiểm tra và phê duyệt bản vẽ hoàn công (As-built)","Xây dựng thư viện tiêu chuẩn vật liệu, thiết bị","Nghiên cứu áp dụng công nghệ mới (Prefab, Modular)","Hỗ trợ kỹ thuật tại công trường","Quản lý tài liệu kỹ thuật (DCC – Document Control)"]',
  `phone` = '024-3755-0040',
  `email` = 'engineering@posung.vn',
  `office_location` = 'Tầng 8, Tòa nhà Vinaconex Tower, 34 Láng Hạ, Ba Đình, Hà Nội',
  `established_date` = '2012-06-01',
  `dept_type` = 'Department',
  `status` = 'Active',
  `sort_order` = 5
WHERE `dept_code` = 'HQ-KS';

-- ── Ban ĐH DA Amkor Bắc Ninh ──
UPDATE `departments` SET
  `description` = 'Ban Điều hành Dự án Amkor Bắc Ninh – phụ trách thi công lắp đặt hệ thống cơ điện (M&E) cho Nhà máy sản xuất chip bán dẫn Amkor Technology tại KCN Yên Phong, Bắc Ninh. Dự án bao gồm: hệ thống ống công nghệ (process piping), HVAC cleanroom, hệ thống điện hạ thế/trung thế, và PCCC. Quy mô ~50 kỹ sư và công nhân.',
  `functions` = '["Quản lý thi công hệ thống Piping (ống công nghệ, khí sạch)","Lắp đặt hệ thống HVAC và Cleanroom","Thi công hệ thống điện hạ thế / trung thế","Lắp đặt hệ thống PCCC và báo cháy","Quản lý tiến độ, chất lượng và an toàn lao động","Nghiệm thu từng hạng mục với chủ đầu tư","Quản lý nhà thầu phụ và vật tư tại công trường","Lập báo cáo tuần/tháng gửi Ban Giám đốc"]',
  `phone` = '0912-100-001',
  `email` = 'amkor.site@posung.vn',
  `office_location` = 'Văn phòng công trường, KCN Yên Phong I, Bắc Ninh',
  `established_date` = '2022-01-15',
  `dept_type` = 'Project',
  `status` = 'Active',
  `sort_order` = 10
WHERE `dept_code` = 'PRJ-AMK';

-- ── Ban ĐH DA Samsung SEHC ──
UPDATE `departments` SET
  `description` = 'Ban Điều hành Dự án Samsung SEHC – phụ trách thi công lắp đặt hệ thống HVAC (Heating, Ventilation & Air Conditioning) cho Nhà máy Samsung Electronics HC tại KCN Yên Phong, Bắc Ninh. Hệ thống bao gồm AHU, chiller, đường ống gió, và hệ thống điều khiển tự động BMS. Tuân thủ nghiêm ngặt tiêu chuẩn Samsung.',
  `functions` = '["Thi công lắp đặt hệ thống HVAC (AHU, FCU, Chiller)","Lắp đặt đường ống gió (ductwork) và cách nhiệt","Hệ thống điều khiển tự động (BMS/DDC)","Kiểm tra vận hành và cân chỉnh hệ thống (TAB)","Quản lý an toàn lao động theo tiêu chuẩn Samsung","Testing & Commissioning (T&C)","Phối hợp với nhà thầu chính và các gói thầu khác","Quản lý chất lượng hàn ống (QC Welding)"]',
  `phone` = '0912-100-002',
  `email` = 'samsung.site@posung.vn',
  `office_location` = 'Văn phòng công trường, KCN Yên Phong II, Bắc Ninh',
  `established_date` = '2021-07-01',
  `dept_type` = 'Project',
  `status` = 'Active',
  `sort_order` = 11
WHERE `dept_code` = 'PRJ-SEHC';

-- ── Ban ĐH DA Starlake Tây Hồ ──
UPDATE `departments` SET
  `description` = 'Ban Điều hành Dự án Starlake Tây Hồ – phụ trách thi công lắp đặt hệ thống cơ điện (M&E) cho Khu đô thị Starlake Tây Hồ Tây, Hà Nội. Dự án bao gồm: hệ thống cấp thoát nước, điện chiếu sáng, PCCC, HVAC cho tòa nhà thương mại và căn hộ cao cấp. Phối hợp chặt với Tổng thầu Daewoo E&C.',
  `functions` = '["Thi công hệ thống cấp thoát nước (plumbing)","Lắp đặt hệ thống điện chiếu sáng và ổ cắm","Thi công hệ thống PCCC (sprinkler, bơm chữa cháy)","Lắp đặt hệ thống HVAC cho tòa nhà thương mại","Quản lý tiến độ theo milestone của Tổng thầu","Kiểm tra chất lượng vật tư đầu vào","Đảm bảo an toàn lao động (HSE) tại công trường","Nghiệm thu và bàn giao từng block/tầng"]',
  `phone` = '0912-100-003',
  `email` = 'starlake.site@posung.vn',
  `office_location` = 'Văn phòng công trường, Khu đô thị Starlake, Tây Hồ Tây, Hà Nội',
  `established_date` = '2023-05-01',
  `dept_type` = 'Project',
  `status` = 'Active',
  `sort_order` = 12
WHERE `dept_code` = 'PRJ-STL';
