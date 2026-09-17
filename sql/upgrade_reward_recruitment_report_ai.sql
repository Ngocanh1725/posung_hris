-- ============================================================
--  POSUNG HRIS – Upgrade: Khen thưởng/Kỷ luật, Tuyển dụng,
--  Báo cáo & AI Analytics
--  Migration Script (MySQL / MariaDB)
--  Ngày: 2026-09-16
-- ============================================================

-- ══════════════════════════════════════════════════════════════
-- GIAI ĐOẠN 1: Bổ sung trường Khen thưởng – Kỷ luật
-- ══════════════════════════════════════════════════════════════

ALTER TABLE `rewards_disciplines`
  ADD COLUMN IF NOT EXISTS `reward_form` VARCHAR(100) DEFAULT NULL 
    COMMENT 'Hình thức KT: Bằng khen, Giấy khen, Tiền thưởng, Tăng lương trước hạn' AFTER `type`,
  ADD COLUMN IF NOT EXISTS `discipline_form` VARCHAR(100) DEFAULT NULL 
    COMMENT 'Hình thức KL: Khiển trách, Cảnh cáo, Cách chức, Hạ bậc lương, Buộc thôi việc' AFTER `reward_form`,
  ADD COLUMN IF NOT EXISTS `authority_level` VARCHAR(100) DEFAULT NULL 
    COMMENT 'Cấp ra QĐ: Giám đốc, Trưởng phòng, Trưởng dự án' AFTER `reason`,
  ADD COLUMN IF NOT EXISTS `proposed_by` VARCHAR(150) DEFAULT NULL 
    COMMENT 'Người đề xuất' AFTER `authority_level`,
  ADD COLUMN IF NOT EXISTS `status` ENUM('Draft','Pending','Approved','Rejected') NOT NULL DEFAULT 'Approved' 
    COMMENT 'Trạng thái quyết định' AFTER `proposed_by`,
  ADD COLUMN IF NOT EXISTS `approved_by` INT(10) UNSIGNED DEFAULT NULL 
    COMMENT 'Người phê duyệt' AFTER `status`,
  ADD COLUMN IF NOT EXISTS `approved_date` DATE DEFAULT NULL 
    COMMENT 'Ngày phê duyệt' AFTER `approved_by`,
  ADD COLUMN IF NOT EXISTS `department_id` INT(10) UNSIGNED DEFAULT NULL 
    COMMENT 'Phòng ban liên quan' AFTER `employee_id`,
  ADD COLUMN IF NOT EXISTS `project_id` INT(10) UNSIGNED DEFAULT NULL 
    COMMENT 'Dự án liên quan' AFTER `department_id`;

-- ══════════════════════════════════════════════════════════════
-- GIAI ĐOẠN 2: Phân hệ Tuyển dụng
-- ══════════════════════════════════════════════════════════════

-- Bảng Yêu cầu Tuyển dụng
CREATE TABLE IF NOT EXISTS `recruitment_requests` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `request_code` VARCHAR(30) NOT NULL COMMENT 'Mã yêu cầu: TD-YYYY-XXXX',
  `department_id` INT(10) UNSIGNED DEFAULT NULL,
  `position_id` INT(10) UNSIGNED DEFAULT NULL,
  `project_id` INT(10) UNSIGNED DEFAULT NULL,
  `quantity` INT NOT NULL DEFAULT 1 COMMENT 'Số lượng cần tuyển',
  `reason` ENUM('Replacement','Expansion','New_Position','Seasonal') NOT NULL DEFAULT 'Expansion' 
    COMMENT 'Lý do: Thay thế/Mở rộng/Vị trí mới/Thời vụ',
  `urgency` ENUM('Normal','Urgent','Critical') NOT NULL DEFAULT 'Normal',
  `description` TEXT DEFAULT NULL COMMENT 'Mô tả công việc',
  `requirements` TEXT DEFAULT NULL COMMENT 'Yêu cầu ứng viên',
  `salary_range_from` DECIMAL(18,2) DEFAULT NULL,
  `salary_range_to` DECIMAL(18,2) DEFAULT NULL,
  `benefits` TEXT DEFAULT NULL COMMENT 'Quyền lợi',
  `work_location` VARCHAR(200) DEFAULT NULL COMMENT 'Địa điểm làm việc',
  `deadline` DATE DEFAULT NULL COMMENT 'Hạn tuyển',
  `requested_by` INT(10) UNSIGNED DEFAULT NULL,
  `approved_by` INT(10) UNSIGNED DEFAULT NULL,
  `status` ENUM('Draft','Pending','Approved','In_Progress','Closed','Cancelled') NOT NULL DEFAULT 'Draft',
  `hired_count` INT NOT NULL DEFAULT 0 COMMENT 'Số đã tuyển được',
  `notes` TEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_request_code` (`request_code`),
  KEY `idx_rr_dept` (`department_id`),
  KEY `idx_rr_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng Hồ sơ Ứng viên
CREATE TABLE IF NOT EXISTS `candidates` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `request_id` INT(10) UNSIGNED DEFAULT NULL COMMENT 'FK → recruitment_requests.id',
  `full_name` VARCHAR(100) NOT NULL,
  `dob` DATE DEFAULT NULL,
  `gender` ENUM('Male','Female','Other') DEFAULT 'Male',
  `phone` VARCHAR(20) DEFAULT NULL,
  `email` VARCHAR(100) DEFAULT NULL,
  `address` TEXT DEFAULT NULL,
  `id_card` VARCHAR(20) DEFAULT NULL COMMENT 'Số CCCD',
  `highest_degree` VARCHAR(100) DEFAULT NULL COMMENT 'Bằng cấp cao nhất',
  `major` VARCHAR(150) DEFAULT NULL COMMENT 'Chuyên ngành',
  `university` VARCHAR(200) DEFAULT NULL COMMENT 'Trường đào tạo',
  `graduation_year` SMALLINT DEFAULT NULL,
  `years_experience` INT DEFAULT 0 COMMENT 'Số năm kinh nghiệm',
  `current_company` VARCHAR(200) DEFAULT NULL COMMENT 'Công ty hiện tại',
  `current_position` VARCHAR(150) DEFAULT NULL COMMENT 'Vị trí hiện tại',
  `expected_salary` DECIMAL(18,2) DEFAULT NULL,
  `cv_file_path` VARCHAR(255) DEFAULT NULL,
  `source` VARCHAR(100) DEFAULT NULL COMMENT 'Nguồn ứng tuyển: Website/Giới thiệu/Headhunt/Facebook/Khác',
  `skills` TEXT DEFAULT NULL COMMENT 'Kỹ năng nổi bật',
  `languages` VARCHAR(200) DEFAULT NULL COMMENT 'Ngoại ngữ',
  `status` ENUM('New','Screening','Interview_Scheduled','Interviewed','Offer','Hired','Rejected','Withdrawn') NOT NULL DEFAULT 'New',
  `interview_date` DATETIME DEFAULT NULL,
  `interview_location` VARCHAR(200) DEFAULT NULL,
  `interview_result` ENUM('Pass','Fail','Pending','Deferred') DEFAULT NULL,
  `interview_score` DECIMAL(5,2) DEFAULT NULL COMMENT 'Điểm phỏng vấn (thang 10)',
  `interviewer_name` VARCHAR(150) DEFAULT NULL,
  `interviewer_notes` TEXT DEFAULT NULL,
  `final_decision` ENUM('Hire','Reject','On_Hold') DEFAULT NULL,
  `offer_salary` DECIMAL(18,2) DEFAULT NULL,
  `offer_date` DATE DEFAULT NULL,
  `start_date` DATE DEFAULT NULL COMMENT 'Ngày dự kiến bắt đầu',
  `rejection_reason` VARCHAR(255) DEFAULT NULL,
  `converted_employee_id` INT(10) UNSIGNED DEFAULT NULL COMMENT 'ID nhân viên sau khi chuyển đổi',
  `notes` TEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_cand_request` (`request_id`),
  KEY `idx_cand_status` (`status`),
  KEY `idx_cand_name` (`full_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ══════════════════════════════════════════════════════════════
-- DỮ LIỆU MẪU (Seed Data)
-- ══════════════════════════════════════════════════════════════

-- Seed một vài yêu cầu tuyển dụng mẫu (Nếu chưa có dữ liệu)
INSERT IGNORE INTO `recruitment_requests` (`request_code`, `quantity`, `reason`, `description`, `requirements`, `status`, `deadline`) VALUES
('TD-2026-0001', 3, 'Expansion', 'Tuyển thợ hàn 3G/6G cho dự án Amkor Bắc Ninh', 'Có chứng chỉ hàn 3G hoặc 6G, kinh nghiệm >2 năm', 'In_Progress', '2026-10-15'),
('TD-2026-0002', 1, 'Replacement', 'Tuyển kỹ sư M&E thay thế', 'Tốt nghiệp ĐH ngành Điện/Cơ điện, TOEIC > 450', 'Approved', '2026-11-01');
