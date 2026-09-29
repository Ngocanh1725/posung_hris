-- ============================================================
-- POSUNG HRIS - SQL PATCH: PERFORMANCE 360° (GOALS/KRA + MULTI-REVIEWER)
-- File: sql/patch_performance360_v1.sql
-- ============================================================

-- 1. Bảng quản lý Mục tiêu KRA của nhân viên trong từng chu kỳ
CREATE TABLE IF NOT EXISTS `performance_goals` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `period_id` INT NOT NULL,
    `employee_id` INT NOT NULL,
    `kra_title` VARCHAR(255) NOT NULL COMMENT 'Tên mục tiêu / KRA',
    `description` TEXT NULL COMMENT 'Mô tả chi tiết mục tiêu',
    `weightage` INT NOT NULL DEFAULT 20 COMMENT 'Trọng số phần trăm (Tổng = 100%)',
    `target_metric` VARCHAR(255) NULL COMMENT 'Chỉ tiêu đo lường / Mục tiêu cần đạt',
    `actual_achievement` TEXT NULL COMMENT 'Kết quả thực tế đạt được',
    `self_score` DECIMAL(5,2) NULL COMMENT 'Điểm nhân viên tự chấm (0-100)',
    `manager_score` DECIMAL(5,2) NULL COMMENT 'Điểm quản lý chấm (0-100)',
    `final_score` DECIMAL(5,2) NULL COMMENT 'Điểm chốt cuối cùng (0-100)',
    `status` ENUM('Draft', 'Submitted', 'Reviewed') NOT NULL DEFAULT 'Draft' COMMENT 'Trạng thái KRA',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_goals_period_emp` (`period_id`, `employee_id`),
    INDEX `idx_goals_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Mục tiêu KRA & Kết quả đánh giá';

-- 2. Bảng đánh giá 360 độ (Self, Manager, Peer, Subordinate)
CREATE TABLE IF NOT EXISTS `performance_reviews_360` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `period_id` INT NOT NULL,
    `employee_id` INT NOT NULL COMMENT 'Nhân viên được đánh giá',
    `reviewer_id` INT NOT NULL COMMENT 'Nhân viên thực hiện đánh giá',
    `relationship` ENUM('Self', 'Manager', 'Peer', 'Subordinate') NOT NULL COMMENT 'Mối quan hệ đánh giá',
    `overall_score` DECIMAL(5,2) NULL COMMENT 'Điểm đánh giá tổng thể (0-100)',
    `strengths` TEXT NULL COMMENT 'Điểm mạnh nổi bật',
    `improvements` TEXT NULL COMMENT 'Điểm cần cải thiện',
    `comments` TEXT NULL COMMENT 'Nhận xét chi tiết',
    `kra_scores` TEXT NULL COMMENT 'Điểm chấm chi tiết theo từng KRA (JSON format: {goal_id: score})',
    `status` ENUM('Pending', 'Submitted') NOT NULL DEFAULT 'Submitted' COMMENT 'Trạng thái review',
    `submitted_at` DATETIME NULL COMMENT 'Thời gian nộp bài đánh giá',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_rev360_period_emp` (`period_id`, `employee_id`),
    INDEX `idx_rev360_reviewer` (`reviewer_id`),
    INDEX `idx_rev360_rel` (`relationship`),
    UNIQUE KEY `uk_rev360_period_emp_rev` (`period_id`, `employee_id`, `reviewer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Phiếu đánh giá đa chiều 360 độ';

-- 3. Cập nhật bảng evaluation_periods thêm cấu hình 360 độ
-- Kiểm tra và bổ sung cột nếu chưa tồn tại
SET @dbname = DATABASE();
SET @tablename = "evaluation_periods";

-- allow_self_review
SET @columnname = "allow_self_review";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      TABLE_SCHEMA = @dbname
      AND TABLE_NAME = @tablename
      AND COLUMN_NAME = @columnname
  ) > 0,
  "SELECT 1",
  CONCAT("ALTER TABLE `", @tablename, "` ADD COLUMN `", @columnname, "` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Cho phép nhân viên tự đánh giá' AFTER `department_id`;")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- allow_peer_review
SET @columnname = "allow_peer_review";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      TABLE_SCHEMA = @dbname
      AND TABLE_NAME = @tablename
      AND COLUMN_NAME = @columnname
  ) > 0,
  "SELECT 1",
  CONCAT("ALTER TABLE `", @tablename, "` ADD COLUMN `", @columnname, "` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Cho phép đồng nghiệp đánh giá chéo' AFTER `allow_self_review`;")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- peer_review_count
SET @columnname = "peer_review_count";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      TABLE_SCHEMA = @dbname
      AND TABLE_NAME = @tablename
      AND COLUMN_NAME = @columnname
  ) > 0,
  "SELECT 1",
  CONCAT("ALTER TABLE `", @tablename, "` ADD COLUMN `", @columnname, "` INT NOT NULL DEFAULT 2 COMMENT 'Số lượng peer review tối đa' AFTER `allow_peer_review`;")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;
