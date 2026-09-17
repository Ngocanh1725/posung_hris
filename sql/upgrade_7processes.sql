-- ============================================================
-- POSUNG HRIS – Upgrade: 7 Quá trình Công tác
-- Migration Script (MySQL / MariaDB)
-- Ngày: 2026-09-14
-- ============================================================

-- ── 1. Bảng Quá trình Công tác (Dự án / Kinh nghiệm) ──────
CREATE TABLE IF NOT EXISTS `emp_work_histories` (
    `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `employee_id`   INT UNSIGNED NOT NULL,
    `from_date`     DATE         NULL,
    `to_date`       DATE         NULL,
    `organization`  VARCHAR(200) NOT NULL DEFAULT '' COMMENT 'Tên công ty / tổ chức',
    `position`      VARCHAR(150) NULL     COMMENT 'Chức vụ',
    `project_name`  VARCHAR(200) NULL     COMMENT 'Tên dự án',
    `description`   TEXT         NULL     COMMENT 'Mô tả công việc',
    `created_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_wh_emp` (`employee_id`),
    FOREIGN KEY (`employee_id`) REFERENCES `employees`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── 2. Bảng Quá trình Đào tạo ──────────────────────────────
CREATE TABLE IF NOT EXISTS `emp_trainings` (
    `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `employee_id`   INT UNSIGNED NOT NULL,
    `from_date`     DATE         NULL,
    `to_date`       DATE         NULL,
    `institution`   VARCHAR(200) NOT NULL DEFAULT '' COMMENT 'Trường / Cơ sở đào tạo',
    `major`         VARCHAR(200) NULL     COMMENT 'Chuyên ngành',
    `certificate`   VARCHAR(200) NULL     COMMENT 'Chứng chỉ / Văn bằng',
    `degree_type`   VARCHAR(100) NULL     COMMENT 'Loại hình (ĐH, CĐ, TC, Ngắn hạn...)',
    `notes`         TEXT         NULL,
    `created_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_tr_emp` (`employee_id`),
    FOREIGN KEY (`employee_id`) REFERENCES `employees`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── 3. Bảng Diễn biến Lương ─────────────────────────────────
CREATE TABLE IF NOT EXISTS `emp_salary_progressions` (
    `id`                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `employee_id`         INT UNSIGNED  NOT NULL,
    `effective_date`      DATE          NOT NULL,
    `salary_grade`        VARCHAR(50)   NULL     COMMENT 'Ngạch / Bậc lương',
    `salary_coefficient`  DECIMAL(5,2)  NULL     COMMENT 'Hệ số lương',
    `base_salary`         DECIMAL(18,2) NOT NULL DEFAULT 0 COMMENT 'Mức lương cơ bản',
    `decision_number`     VARCHAR(50)   NULL     COMMENT 'Số quyết định',
    `notes`               TEXT          NULL,
    `created_at`          DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`          DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_sp_emp` (`employee_id`),
    INDEX `idx_sp_date` (`effective_date`),
    FOREIGN KEY (`employee_id`) REFERENCES `employees`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── 4. Bảng Quan hệ Gia đình ────────────────────────────────
CREATE TABLE IF NOT EXISTS `emp_family_members` (
    `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `employee_id`   INT UNSIGNED NOT NULL,
    `full_name`     VARCHAR(100) NOT NULL,
    `relationship`  VARCHAR(50)  NOT NULL COMMENT 'Bố, Mẹ, Vợ/Chồng, Con...',
    `dob`           DATE         NULL,
    `occupation`    VARCHAR(150) NULL     COMMENT 'Nghề nghiệp',
    `workplace`     VARCHAR(200) NULL     COMMENT 'Nơi làm việc / Đơn vị công tác',
    `address`       VARCHAR(250) NULL     COMMENT 'Nơi ở hiện nay',
    `id_card`       VARCHAR(30)  NULL     COMMENT 'Số CCCD',
    `phone`         VARCHAR(20)  NULL,
    `notes`         TEXT         NULL,
    `created_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_fm_emp` (`employee_id`),
    FOREIGN KEY (`employee_id`) REFERENCES `employees`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── 5. Bảng Khen thưởng – Kỷ luật (Lịch sử riêng 7 quá trình) ─
CREATE TABLE IF NOT EXISTS `emp_reward_discipline_histories` (
    `id`              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `employee_id`     INT UNSIGNED NOT NULL,
    `type`            ENUM('Reward','Discipline') NOT NULL,
    `decision_number` VARCHAR(50)  NULL     COMMENT 'Số quyết định',
    `decision_date`   DATE         NULL,
    `title`           VARCHAR(200) NOT NULL COMMENT 'Hình thức KT/KL',
    `reason`          TEXT         NULL     COMMENT 'Lý do',
    `authority`       VARCHAR(200) NULL     COMMENT 'Cơ quan quyết định',
    `notes`           TEXT         NULL,
    `created_at`      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_rdh_emp` (`employee_id`),
    INDEX `idx_rdh_type` (`type`),
    FOREIGN KEY (`employee_id`) REFERENCES `employees`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── 6. Bảng Đánh giá KPI / Năng lực ────────────────────────
CREATE TABLE IF NOT EXISTS `emp_evaluations` (
    `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `employee_id`   INT UNSIGNED NOT NULL,
    `eval_year`     SMALLINT     NOT NULL COMMENT 'Năm đánh giá',
    `eval_period`   VARCHAR(50)  NULL     COMMENT 'Kỳ đánh giá (6 tháng đầu, cả năm...)',
    `rating`        VARCHAR(50)  NULL     COMMENT 'Xếp loại (Xuất sắc, Tốt, Khá, TB, Yếu)',
    `evaluator`     VARCHAR(100) NULL     COMMENT 'Người đánh giá',
    `score`         DECIMAL(5,2) NULL     COMMENT 'Điểm số (nếu có)',
    `notes`         TEXT         NULL     COMMENT 'Nhận xét',
    `created_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_ev_emp` (`employee_id`),
    INDEX `idx_ev_year` (`eval_year`),
    FOREIGN KEY (`employee_id`) REFERENCES `employees`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── 7. Bảng Quá trình Bổ nhiệm ─────────────────────────────
CREATE TABLE IF NOT EXISTS `emp_appointments` (
    `id`              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `employee_id`     INT UNSIGNED NOT NULL,
    `effective_date`  DATE         NOT NULL,
    `position_title`  VARCHAR(150) NOT NULL COMMENT 'Chức vụ được bổ nhiệm',
    `department`      VARCHAR(150) NULL     COMMENT 'Phòng ban / Bộ phận',
    `decision_number` VARCHAR(50)  NULL     COMMENT 'Số quyết định',
    `notes`           TEXT         NULL,
    `created_at`      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_ap_emp` (`employee_id`),
    INDEX `idx_ap_date` (`effective_date`),
    FOREIGN KEY (`employee_id`) REFERENCES `employees`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ══════════════════════════════════════════════════════════════
-- BỔ SUNG CỘT MỚI VÀO BẢNG employees (Tab 2 & Tab 3)
-- ══════════════════════════════════════════════════════════════

-- Tab 2: Kỹ năng kỹ thuật M&E
ALTER TABLE `employees`
    ADD COLUMN IF NOT EXISTS `skill_autocad`      VARCHAR(50)  NULL DEFAULT NULL COMMENT 'Trình độ AutoCAD' AFTER `nang_luc_so_truong`,
    ADD COLUMN IF NOT EXISTS `skill_revit_bim`    VARCHAR(50)  NULL DEFAULT NULL COMMENT 'Trình độ Revit BIM' AFTER `skill_autocad`,
    ADD COLUMN IF NOT EXISTS `skill_navisworks`   VARCHAR(50)  NULL DEFAULT NULL COMMENT 'Trình độ Navisworks' AFTER `skill_revit_bim`,
    ADD COLUMN IF NOT EXISTS `skill_estimation`   VARCHAR(50)  NULL DEFAULT NULL COMMENT 'Trình độ Dự toán' AFTER `skill_navisworks`,
    ADD COLUMN IF NOT EXISTS `welding_cert_3g`    TINYINT(1)   NOT NULL DEFAULT 0 COMMENT 'Chứng chỉ hàn 3G' AFTER `skill_estimation`,
    ADD COLUMN IF NOT EXISTS `welding_cert_6g`    TINYINT(1)   NOT NULL DEFAULT 0 COMMENT 'Chứng chỉ hàn 6G' AFTER `welding_cert_3g`,
    ADD COLUMN IF NOT EXISTS `welding_cert_tig`   TINYINT(1)   NOT NULL DEFAULT 0 COMMENT 'Chứng chỉ hàn TIG' AFTER `welding_cert_6g`,
    ADD COLUMN IF NOT EXISTS `welding_cert_mig`   TINYINT(1)   NOT NULL DEFAULT 0 COMMENT 'Chứng chỉ hàn MIG' AFTER `welding_cert_tig`,
    ADD COLUMN IF NOT EXISTS `korean_level`       VARCHAR(50)  NULL DEFAULT NULL COMMENT 'Trình độ tiếng Hàn (Topik/Giao tiếp)' AFTER `welding_cert_mig`,
    ADD COLUMN IF NOT EXISTS `english_level`      VARCHAR(50)  NULL DEFAULT NULL COMMENT 'Trình độ tiếng Anh (TOEIC/Giao tiếp)' AFTER `korean_level`,
    ADD COLUMN IF NOT EXISTS `it_level`           VARCHAR(50)  NULL DEFAULT NULL COMMENT 'Trình độ Tin học' AFTER `english_level`,
    ADD COLUMN IF NOT EXISTS `hse_card_number`    VARCHAR(50)  NULL DEFAULT NULL COMMENT 'Số thẻ an toàn LĐ' AFTER `it_level`,
    ADD COLUMN IF NOT EXISTS `hse_card_issue_date` DATE        NULL DEFAULT NULL COMMENT 'Ngày cấp thẻ ATLĐ' AFTER `hse_card_number`,
    ADD COLUMN IF NOT EXISTS `hse_card_expiry_samsung` DATE    NULL DEFAULT NULL COMMENT 'Hạn thẻ ATLĐ Samsung' AFTER `hse_card_issue_date`,
    ADD COLUMN IF NOT EXISTS `hse_card_expiry_amkor`   DATE    NULL DEFAULT NULL COMMENT 'Hạn thẻ ATLĐ Amkor' AFTER `hse_card_expiry_samsung`;

-- Tab 3: Sức khỏe & PPE (một số cột đã tồn tại: chieu_cao, can_nang, nhom_mau)
ALTER TABLE `employees`
    ADD COLUMN IF NOT EXISTS `can_work_at_height`      TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Đủ ĐK làm việc trên cao' AFTER `nhom_mau`,
    ADD COLUMN IF NOT EXISTS `can_work_confined_space`  TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Đủ ĐK làm việc hầm kín' AFTER `can_work_at_height`,
    ADD COLUMN IF NOT EXISTS `safety_shoe_size`         VARCHAR(10) NULL DEFAULT NULL COMMENT 'Size giày BH' AFTER `can_work_confined_space`,
    ADD COLUMN IF NOT EXISTS `safety_uniform_size`      VARCHAR(10) NULL DEFAULT NULL COMMENT 'Size áo BH' AFTER `safety_shoe_size`;

-- Bổ sung passport_expiry vào expat_details (nếu chưa có)
ALTER TABLE `expat_details`
    ADD COLUMN IF NOT EXISTS `passport_expiry` DATE NULL DEFAULT NULL COMMENT 'Hạn hộ chiếu' AFTER `passport_number`;

