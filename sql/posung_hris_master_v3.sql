-- ==========================================================
-- POSUNG HRIS MASTER V3 (STANDALONE)
-- Generated at: 2026-09-24 23:34:38
-- Database: posung_hris
-- ==========================================================

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `departments`;
DROP TABLE IF EXISTS `positions`;
DROP TABLE IF EXISTS `projects`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `roles`;
DROP TABLE IF EXISTS `permissions`;
DROP TABLE IF EXISTS `role_permissions`;
DROP TABLE IF EXISTS `employees`;
DROP TABLE IF EXISTS `contracts`;
DROP TABLE IF EXISTS `contract_types`;
DROP TABLE IF EXISTS `employee_certificates`;
DROP TABLE IF EXISTS `certificates`;
DROP TABLE IF EXISTS `job_movements`;
DROP TABLE IF EXISTS `transfer_orders`;
DROP TABLE IF EXISTS `timesheets`;
DROP TABLE IF EXISTS `payrolls`;
DROP TABLE IF EXISTS `payroll_details`;
DROP TABLE IF EXISTS `payroll_formulas`;
DROP TABLE IF EXISTS `salaries`;
DROP TABLE IF EXISTS `cost_centers`;
DROP TABLE IF EXISTS `allowances`;
DROP TABLE IF EXISTS `employee_allowances`;
DROP TABLE IF EXISTS `dependents`;
DROP TABLE IF EXISTS `work_experiences`;
DROP TABLE IF EXISTS `clearance_checklists`;
DROP TABLE IF EXISTS `emp_ppe_issuances`;
DROP TABLE IF EXISTS `emp_trainings`;
DROP TABLE IF EXISTS `emp_evaluations`;
DROP TABLE IF EXISTS `candidates`;
DROP TABLE IF EXISTS `recruitment_requests`;
DROP TABLE IF EXISTS `rewards_disciplines`;
DROP TABLE IF EXISTS `leave_requests`;
DROP TABLE IF EXISTS `leave_types`;
DROP TABLE IF EXISTS `expat_details`;
DROP TABLE IF EXISTS `user_permissions`;
DROP TABLE IF EXISTS `system_roles`;
DROP TABLE IF EXISTS `system_permissions`;
DROP TABLE IF EXISTS `system_role_permissions`;
DROP TABLE IF EXISTS `system_modules`;
DROP TABLE IF EXISTS `system_menus`;

CREATE TABLE `departments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dept_code` varchar(50) NOT NULL,
  `dept_name` varchar(150) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `manager_id` int(11) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL COMMENT 'Sß╗æ ─æiß╗çn thoß║íi li├¬n hß╗ç bß╗Ö phß║¡n',
  `email` varchar(100) DEFAULT NULL COMMENT 'Email bß╗Ö phß║¡n',
  `office_location` varchar(200) DEFAULT NULL COMMENT '─Éß╗ïa ─æiß╗âm v─ân ph├▓ng / c├┤ng tr├¼nh',
  `established_date` date DEFAULT NULL COMMENT 'Ng├áy th├ánh lß║¡p bß╗Ö phß║¡n',
  `dept_type` enum('Division','Department','Team','Project') NOT NULL DEFAULT 'Department' COMMENT 'Loß║íi h├¼nh tß╗ò chß╗®c',
  `type` enum('office','factory') DEFAULT 'office',
  `branch` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `functions` text DEFAULT NULL COMMENT 'Danh s├ích chß╗®c n─âng ch├¡nh (JSON array)',
  `status` varchar(20) DEFAULT 'Active',
  `sort_order` int(11) NOT NULL DEFAULT 0 COMMENT 'Thß╗® tß╗▒ hiß╗ân thß╗ï',
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`dept_code`),
  KEY `parent_id` (`parent_id`),
  KEY `fk_dept_manager` (`manager_id`),
  CONSTRAINT `departments_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_dept_manager` FOREIGN KEY (`manager_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `positions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pos_code` varchar(50) NOT NULL,
  `pos_title` varchar(150) NOT NULL,
  `job_level` varchar(50) DEFAULT NULL,
  `base_salary_min` decimal(15,2) DEFAULT 0.00,
  `base_salary_max` decimal(15,2) DEFAULT 0.00,
  `allowance_rate` decimal(5,2) DEFAULT 0.00,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`pos_code`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `projects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_code` varchar(50) NOT NULL,
  `project_name` varchar(150) NOT NULL,
  `client_name` varchar(100) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('Active','In_Progress','Completed','Suspended') DEFAULT 'Active',
  `cost_center_code` varchar(50) DEFAULT NULL,
  `headcount_budget` int(11) DEFAULT 0,
  `site_manager_id` int(11) DEFAULT NULL,
  `hse_lead_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `project_code` (`project_code`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `role_id` int(11) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `status` enum('active','locked') DEFAULT 'active',
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `employee_id` (`employee_id`),
  KEY `role_id` (`role_id`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL,
  CONSTRAINT `users_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=108 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `is_system` tinyint(1) DEFAULT 0,
  `level` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `module_code` varchar(50) NOT NULL,
  `action_code` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_module_action` (`module_code`,`action_code`)
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `role_permissions` (
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  PRIMARY KEY (`role_id`,`permission_id`),
  KEY `permission_id` (`permission_id`),
  CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `employees` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `emp_code` varchar(20) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `gender` enum('Male','Female','Other') DEFAULT 'Male',
  `birth_date` date DEFAULT NULL,
  `birth_place` varchar(255) DEFAULT NULL,
  `native_place` varchar(255) DEFAULT NULL,
  `id_card_no` varchar(50) DEFAULT NULL,
  `id_card_date` date DEFAULT NULL,
  `id_card_place` varchar(255) DEFAULT NULL,
  `ethnic` varchar(50) DEFAULT 'Kinh',
  `religion` varchar(50) DEFAULT 'Không',
  `blood_type` varchar(10) DEFAULT NULL,
  `nationality` varchar(50) DEFAULT 'Vietnam',
  `is_expat` tinyint(1) DEFAULT 0,
  `passport_no` varchar(50) DEFAULT NULL,
  `passport_expiry` date DEFAULT NULL,
  `work_permit_no` varchar(100) DEFAULT NULL,
  `work_permit_expiry` date DEFAULT NULL,
  `trc_no` varchar(100) DEFAULT NULL,
  `trc_expiry` date DEFAULT NULL,
  `academic_level` varchar(50) DEFAULT NULL,
  `degree_level` enum('Tiến sĩ','Thạc sĩ','Đại học','Cao đẳng','Trung cấp','Công nhân kỹ thuật','Khác') DEFAULT NULL,
  `degree_title` varchar(150) DEFAULT NULL,
  `training_school` varchar(150) DEFAULT NULL,
  `political_theory` enum('Cao cấp','Trung cấp','Sơ cấp','Không') DEFAULT 'Không',
  `state_management` varchar(100) DEFAULT NULL,
  `foreign_languages` varchar(255) DEFAULT NULL,
  `computer_skill` varchar(255) DEFAULT NULL,
  `party_entry_date` date DEFAULT NULL,
  `party_official_date` date DEFAULT NULL,
  `youth_union_date` date DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `current_project_id` int(11) DEFAULT NULL,
  `position_id` int(11) DEFAULT NULL,
  `direct_manager_id` int(11) DEFAULT NULL,
  `join_date` date DEFAULT NULL,
  `official_date` date DEFAULT NULL,
  `contract_status` enum('Probation','Definite','Indefinite','Seasonal') DEFAULT 'Probation',
  `status` varchar(50) DEFAULT 'Active',
  `social_insurance_no` varchar(50) DEFAULT NULL,
  `health_insurance_no` varchar(50) DEFAULT NULL,
  `tax_code` varchar(50) DEFAULT NULL,
  `bank_account_no` varchar(50) DEFAULT NULL,
  `bank_name` varchar(150) DEFAULT NULL,
  `employee_type` varchar(50) DEFAULT 'Local',
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `avatar_path` varchar(255) DEFAULT NULL,
  `id_card_front` varchar(255) DEFAULT NULL,
  `id_card_back` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `cv_file_path` varchar(255) DEFAULT NULL,
  `document_returned` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employee_code` (`emp_code`),
  KEY `position_id` (`position_id`),
  KEY `direct_manager_id` (`direct_manager_id`),
  KEY `idx_status` (`status`),
  KEY `idx_current_project_id` (`current_project_id`),
  KEY `idx_department_id` (`department_id`),
  CONSTRAINT `employees_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employees_ibfk_2` FOREIGN KEY (`current_project_id`) REFERENCES `projects` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employees_ibfk_3` FOREIGN KEY (`position_id`) REFERENCES `positions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employees_ibfk_4` FOREIGN KEY (`direct_manager_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `contracts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `contract_type_id` int(11) DEFAULT NULL,
  `contract_number` varchar(50) DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `salary` decimal(15,2) DEFAULT NULL,
  `status` enum('Active','Expired','Terminated') DEFAULT 'Active',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `file_path` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  KEY `contract_type_id` (`contract_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `contract_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `code` varchar(50) DEFAULT NULL,
  `duration_months` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `employee_certificates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `certificate_name` varchar(150) NOT NULL,
  `license_no` varchar(100) DEFAULT NULL,
  `issued_by` varchar(150) DEFAULT NULL,
  `issue_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `scan_file_path` varchar(255) DEFAULT NULL,
  `is_mandatory_for_site` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  KEY `idx_certificate_name` (`certificate_name`),
  CONSTRAINT `employee_certificates_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `certificates` AS select `employee_certificates`.`id` AS `id`,`employee_certificates`.`employee_id` AS `employee_id`,`employee_certificates`.`certificate_name` AS `cert_name`,`employee_certificates`.`certificate_name` AS `cert_type`,`employee_certificates`.`certificate_name` AS `certificate_name`,`employee_certificates`.`license_no` AS `license_no`,`employee_certificates`.`issued_by` AS `issued_by`,`employee_certificates`.`issue_date` AS `issue_date`,`employee_certificates`.`expiry_date` AS `expiry_date`,`employee_certificates`.`scan_file_path` AS `scan_file_path`,`employee_certificates`.`is_mandatory_for_site` AS `is_mandatory_for_site` from `employee_certificates`;

CREATE TABLE `job_movements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `movement_type` enum('transfer','promotion','salary_adjustment') NOT NULL,
  `from_project_id` int(11) DEFAULT NULL,
  `to_project_id` int(11) DEFAULT NULL,
  `from_cost_center` varchar(50) DEFAULT NULL,
  `to_cost_center` varchar(50) DEFAULT NULL,
  `from_dept_id` int(11) DEFAULT NULL,
  `to_dept_id` int(11) DEFAULT NULL,
  `decision_no` varchar(50) DEFAULT NULL,
  `effective_date` date NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `approved_by` int(11) DEFAULT NULL,
  `transfer_order_id` int(11) DEFAULT NULL,
  `to_position_id` int(11) DEFAULT NULL,
  `cost_center_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  KEY `from_project_id` (`from_project_id`),
  KEY `to_project_id` (`to_project_id`),
  KEY `from_department_id` (`from_dept_id`),
  KEY `to_department_id` (`to_dept_id`),
  KEY `approved_by` (`approved_by`),
  KEY `idx_effective_date` (`effective_date`),
  CONSTRAINT `job_movements_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `job_movements_ibfk_2` FOREIGN KEY (`from_project_id`) REFERENCES `projects` (`id`) ON DELETE SET NULL,
  CONSTRAINT `job_movements_ibfk_3` FOREIGN KEY (`to_project_id`) REFERENCES `projects` (`id`) ON DELETE SET NULL,
  CONSTRAINT `job_movements_ibfk_4` FOREIGN KEY (`from_dept_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `job_movements_ibfk_5` FOREIGN KEY (`to_dept_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `job_movements_ibfk_6` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `transfer_orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_code` varchar(50) DEFAULT NULL,
  `from_project_id` int(11) DEFAULT NULL,
  `to_project_id` int(11) DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `status` enum('Draft','Pending','PM_Approved','Approved','Rejected','Completed') DEFAULT 'Draft',
  `created_by` int(11) DEFAULT NULL,
  `pm_approved_by` int(11) DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `effective_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `from_project_id` (`from_project_id`),
  KEY `to_project_id` (`to_project_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `timesheets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `project_id` int(11) DEFAULT NULL,
  `work_date` date NOT NULL,
  `check_in` time DEFAULT NULL,
  `check_out` time DEFAULT NULL,
  `standard_hours` decimal(5,2) DEFAULT 0.00,
  `ot_normal_hours` decimal(5,2) DEFAULT 0.00,
  `ot_sunday_hours` decimal(5,2) DEFAULT 0.00,
  `ot_holiday_hours` decimal(5,2) DEFAULT 0.00,
  `night_shift_hours` decimal(5,2) DEFAULT 0.00,
  `work_environment` enum('normal','cleanroom','hazardous') DEFAULT 'normal',
  `sync_status` enum('edge_synced','manual') DEFAULT 'manual',
  `shift_type` enum('Day','Night','Sunday','Holiday') DEFAULT 'Day',
  `ot_hours` decimal(5,2) DEFAULT 0.00,
  `is_cleanroom` tinyint(1) DEFAULT 0,
  `status` enum('Pending','Approved','Locked') DEFAULT 'Approved',
  `device_ip` varchar(45) DEFAULT NULL,
  `verification_type` varchar(50) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_employee_date` (`employee_id`,`work_date`),
  KEY `project_id` (`project_id`),
  CONSTRAINT `timesheets_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `timesheets_ibfk_2` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `payrolls` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `month` int(11) NOT NULL,
  `year` int(11) NOT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `project_id` int(11) DEFAULT NULL,
  `cost_center_id` int(11) DEFAULT NULL,
  `standard_days` decimal(5,1) DEFAULT 26.0,
  `actual_days` decimal(5,1) DEFAULT 0.0,
  `base_salary` decimal(15,2) DEFAULT 0.00,
  `ot_pay` decimal(15,2) DEFAULT 0.00,
  `allowances_total` decimal(15,2) DEFAULT 0.00,
  `deductions_total` decimal(15,2) DEFAULT 0.00,
  `net_salary` decimal(15,2) DEFAULT 0.00,
  `payment_status` varchar(20) DEFAULT 'Draft',
  `status` enum('Draft','Approved','Paid') DEFAULT 'Draft',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `regular_pay` decimal(15,2) DEFAULT 0.00,
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`),
  KEY `idx_cost_center` (`cost_center_id`),
  CONSTRAINT `payrolls_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `payroll_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `payroll_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `cost_center_code` varchar(50) DEFAULT NULL,
  `base_salary` decimal(15,2) DEFAULT 0.00,
  `actual_work_days` decimal(5,2) DEFAULT 0.00,
  `ot_salary` decimal(15,2) DEFAULT 0.00,
  `site_allowance` decimal(15,2) DEFAULT 0.00,
  `cleanroom_allowance` decimal(15,2) DEFAULT 0.00,
  `insurance_deduction` decimal(15,2) DEFAULT 0.00,
  `personal_tax` decimal(15,2) DEFAULT 0.00,
  `net_salary` decimal(15,2) DEFAULT 0.00,
  PRIMARY KEY (`id`),
  KEY `payroll_id` (`payroll_id`),
  KEY `employee_id` (`employee_id`),
  CONSTRAINT `payroll_details_ibfk_1` FOREIGN KEY (`payroll_id`) REFERENCES `payrolls` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payroll_details_ibfk_2` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `payroll_formulas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `formula_name` varchar(150) NOT NULL,
  `formula_code` varchar(50) NOT NULL,
  `expression` text NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `formula_code` (`formula_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `salaries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `base_salary` decimal(15,2) NOT NULL DEFAULT 0.00,
  `project_allowance` decimal(15,2) DEFAULT 0.00,
  `cleanroom_allowance` decimal(15,2) DEFAULT 0.00,
  `remote_allowance` decimal(15,2) DEFAULT 0.00,
  `hazard_allowance` decimal(15,2) DEFAULT 0.00,
  `insurance_rate` decimal(5,2) DEFAULT 10.50,
  `effective_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `cost_centers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `name` varchar(150) NOT NULL,
  `project_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` varchar(20) DEFAULT 'Active',
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `project_id` (`project_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `allowances` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `type` enum('Fixed','Percentage','Daily') DEFAULT 'Fixed',
  `default_amount` decimal(15,2) DEFAULT 0.00,
  `is_taxable` tinyint(1) DEFAULT 1,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `employee_allowances` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `allowance_id` int(11) NOT NULL,
  `amount` decimal(15,2) DEFAULT 0.00,
  `effective_from` date DEFAULT NULL,
  `effective_to` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  KEY `allowance_id` (`allowance_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `dependents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `relationship` varchar(50) DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `id_number` varchar(20) DEFAULT NULL,
  `is_tax_dependent` tinyint(1) DEFAULT 0,
  `deduction_from` date DEFAULT NULL,
  `deduction_to` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `work_experiences` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `company_name` varchar(150) NOT NULL,
  `position_title` varchar(100) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `clearance_checklists` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `tools_returned` tinyint(1) DEFAULT 0,
  `ppe_returned` tinyint(1) DEFAULT 0,
  `account_settled` tinyint(1) DEFAULT 0,
  `insurance_closed` tinyint(1) DEFAULT 0,
  `notes` text DEFAULT NULL,
  `status` enum('Pending','Completed') DEFAULT 'Pending',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `emp_ppe_issuances` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `ppe_item_name` varchar(150) NOT NULL,
  `serial_no` varchar(50) DEFAULT NULL,
  `quantity` int(11) DEFAULT 1,
  `issue_date` date DEFAULT NULL,
  `return_date` date DEFAULT NULL,
  `condition_note` varchar(255) DEFAULT NULL,
  `status` enum('Issued','Returned','Lost','Damaged') DEFAULT 'Issued',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `emp_trainings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `training_name` varchar(200) NOT NULL,
  `training_type` varchar(100) DEFAULT NULL,
  `institution` varchar(200) DEFAULT NULL,
  `from_date` date DEFAULT NULL,
  `to_date` date DEFAULT NULL,
  `result` varchar(100) DEFAULT NULL,
  `certificate_no` varchar(50) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `emp_evaluations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `eval_year` int(11) NOT NULL,
  `eval_period` varchar(50) DEFAULT NULL,
  `evaluator_id` int(11) DEFAULT NULL,
  `kpi_score` decimal(5,2) DEFAULT NULL,
  `competency_score` decimal(5,2) DEFAULT NULL,
  `overall_grade` varchar(10) DEFAULT NULL,
  `strengths` text DEFAULT NULL,
  `improvements` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `candidates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `request_id` int(11) DEFAULT NULL,
  `full_name` varchar(150) NOT NULL,
  `id_card_no` varchar(50) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `status` enum('received','screening','skills_test','interview','offered','hired','rejected') NOT NULL DEFAULT 'received' COMMENT 'Cß╗Öt Kanban: received (Mß╗øi nhß║¡n), screening (S├áng lß╗ìc), skills_test (Test 6G/HSE), interview (Phß╗Ång vß║Ñn), offered (─Éß╗ü xuß║Ñt), hired (Tiß║┐p nhß║¡n), rejected (Tß╗½ chß╗æi)',
  `interview_score` decimal(5,2) DEFAULT NULL,
  `welding_6g_result` enum('Pass','Fail','Not_Tested') DEFAULT 'Not_Tested',
  `is_blacklisted` tinyint(1) DEFAULT 0,
  `cv_file_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `birth_date` date DEFAULT NULL,
  `gender` enum('Male','Female') DEFAULT NULL,
  `address` text DEFAULT NULL,
  `experience_years` int(11) DEFAULT 0,
  `applied_position` varchar(150) DEFAULT NULL,
  `interview_date` datetime DEFAULT NULL,
  `interviewer` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `hired_employee_id` int(11) DEFAULT NULL,
  `highest_degree` varchar(100) DEFAULT NULL,
  `major` varchar(100) DEFAULT NULL,
  `university` varchar(150) DEFAULT NULL,
  `graduation_year` int(11) DEFAULT NULL,
  `current_company` varchar(150) DEFAULT NULL,
  `expected_salary` decimal(15,2) DEFAULT NULL,
  `source` varchar(50) DEFAULT NULL,
  `skills` text DEFAULT NULL,
  `languages` text DEFAULT NULL,
  `interview_location` varchar(200) DEFAULT NULL,
  `interview_result` varchar(50) DEFAULT NULL,
  `final_decision` varchar(50) DEFAULT NULL,
  `offer_salary` decimal(15,2) DEFAULT NULL,
  `offer_date` date DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `test_6g` text DEFAULT NULL,
  `current_position` varchar(150) DEFAULT NULL,
  `blacklist_reason` text DEFAULT NULL COMMENT 'L├¢ do Blacklist (Lß║Ñy tß╗½ hß╗ç thß╗æng Rewards_Disciplines)',
  `front_id_card_path` varchar(255) DEFAULT NULL COMMENT '─Éã░ß╗Øng dß║½n ß║únh CCCD Mß║Àt trã░ß╗øc',
  `back_id_card_path` varchar(255) DEFAULT NULL COMMENT '─Éã░ß╗Øng dß║½n ß║únh CCCD Mß║Àt sau',
  `cert_file_path` varchar(255) DEFAULT NULL COMMENT '─Éã░ß╗Øng dß║½n ß║únh Chß╗®ng chß╗ë (H├án 6G/An to├án HSE)',
  PRIMARY KEY (`id`),
  KEY `request_id` (`request_id`),
  CONSTRAINT `candidates_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `recruitment_requests` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `recruitment_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `department_id` int(11) DEFAULT NULL,
  `position_id` int(11) DEFAULT NULL,
  `project_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT 1,
  `status` enum('Pending','Approved','In_Progress','Completed','Cancelled') DEFAULT 'Pending',
  `requirements` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `urgency` enum('Normal','Urgent','Critical') DEFAULT 'Normal',
  `requested_by` int(11) DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `deadline` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `reason` varchar(100) DEFAULT 'Expansion',
  `description` text DEFAULT NULL,
  `salary_range_from` decimal(15,2) DEFAULT NULL,
  `salary_range_to` decimal(15,2) DEFAULT NULL,
  `benefits` text DEFAULT NULL,
  `work_location` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `department_id` (`department_id`),
  KEY `position_id` (`position_id`),
  KEY `project_id` (`project_id`),
  CONSTRAINT `recruitment_requests_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `recruitment_requests_ibfk_2` FOREIGN KEY (`position_id`) REFERENCES `positions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `recruitment_requests_ibfk_3` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `rewards_disciplines` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `type` enum('Reward','Discipline') NOT NULL,
  `decision_number` varchar(50) DEFAULT NULL,
  `decision_date` date NOT NULL,
  `reason` text DEFAULT NULL,
  `amount` decimal(15,2) DEFAULT 0.00,
  `project_id` int(11) DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `approved_date` date DEFAULT NULL COMMENT 'Ng├áy ph├¬ duyß╗çt',
  `approved_at` datetime DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `department_id` int(11) DEFAULT NULL,
  `reward_form` varchar(100) DEFAULT NULL,
  `discipline_form` varchar(100) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `is_safety_violation` tinyint(1) DEFAULT 0,
  `authority_level` varchar(100) DEFAULT NULL,
  `proposed_by` int(11) DEFAULT NULL,
  `status` enum('Proposed','Draft','Pending','Approved','Issued','Rejected') NOT NULL DEFAULT 'Proposed',
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  CONSTRAINT `rewards_disciplines_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `leave_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `leave_type_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `total_days` decimal(4,1) DEFAULT 1.0,
  `reason` text DEFAULT NULL,
  `status` enum('Pending','Approved','Rejected','Cancelled') DEFAULT 'Pending',
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  KEY `leave_type_id` (`leave_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `leave_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `code` varchar(50) DEFAULT NULL,
  `max_days_per_year` int(11) DEFAULT 12,
  `is_paid` tinyint(1) DEFAULT 1,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `expat_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `passport_number` varchar(100) DEFAULT NULL,
  `passport_expiry` date DEFAULT NULL COMMENT 'Hß║ín hß╗Ö chiß║┐u',
  `visa_number` varchar(100) DEFAULT NULL,
  `visa_expiry` date DEFAULT NULL,
  `work_permit_number` varchar(100) DEFAULT NULL,
  `work_permit_expiry` date DEFAULT NULL,
  `trc_number` varchar(100) DEFAULT NULL,
  `trc_expiry` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  CONSTRAINT `expat_details_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `user_permissions` (
  `user_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  `is_granted` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`user_id`,`permission_id`),
  KEY `permission_id` (`permission_id`),
  CONSTRAINT `fk_up_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_permissions_ibfk_1` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `system_roles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `level` tinyint(3) unsigned NOT NULL DEFAULT 3 COMMENT '1: Super Admin, 2: Quản trị bộ phận, 3: Nhân viên',
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `system_permissions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `module_id` int(10) unsigned NOT NULL,
  `code` varchar(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `action_name` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `fk_sys_perm_module` (`module_id`),
  CONSTRAINT `fk_sys_perm_module` FOREIGN KEY (`module_id`) REFERENCES `system_modules` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=71 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `system_role_permissions` (
  `role_id` int(10) unsigned NOT NULL,
  `permission_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`permission_id`),
  KEY `fk_sys_rp_perm` (`permission_id`),
  CONSTRAINT `fk_sys_rp_perm` FOREIGN KEY (`permission_id`) REFERENCES `system_permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_sys_rp_role` FOREIGN KEY (`role_id`) REFERENCES `system_roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `system_modules` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `parent_id` int(10) unsigned DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `fk_sys_module_parent` (`parent_id`),
  CONSTRAINT `fk_sys_module_parent` FOREIGN KEY (`parent_id`) REFERENCES `system_modules` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `system_menus` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) DEFAULT NULL,
  `title` varchar(100) NOT NULL,
  `url` varchar(255) DEFAULT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `permission_required` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`),
  CONSTRAINT `system_menus_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `system_menus` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `employees` ADD INDEX `idx_id_card_no` (`id_card_no`);
ALTER TABLE `employees` ADD INDEX `idx_status` (`status`);
ALTER TABLE `employee_certificates` ADD INDEX `idx_expiry_date` (`expiry_date`);
ALTER TABLE `timesheets` ADD INDEX `idx_date_emp` (`work_date`, `employee_id`);
ALTER TABLE `job_movements` ADD INDEX `idx_effective_to_proj` (`effective_date`, `to_project_id`);

DELIMITER //
DROP TRIGGER IF EXISTS trg_auto_update_employee_project //
CREATE TRIGGER trg_auto_update_employee_project AFTER UPDATE ON job_movements
FOR EACH ROW
BEGIN
    IF NEW.status = 'Approved' AND OLD.status != 'Approved' THEN
        UPDATE employees SET current_project_id = NEW.to_project_id, department_id = NEW.to_dept_id, position_id = NEW.to_position_id WHERE id = NEW.employee_id;
    END IF;
END //
DELIMITER ;

-- SEED DATA
INSERT INTO `roles` (`id`, `role_code`, `role_name`, `description`, `created_at`) VALUES
(1, 'admin', 'Administrator', 'Quản trị hệ thống', NOW()),
(2, 'hr_manager', 'HR Manager', 'Trưởng phòng nhân sự', NOW()),
(3, 'project_manager', 'Project Manager', 'Quản lý dự án', NOW()),
(4, 'employee', 'Employee', 'Nhân viên', NOW());

INSERT INTO `users` (`id`, `username`, `password_hash`, `email`, `role_id`, `status`, `created_at`) VALUES
(1, 'admin', '$2y$10$qIZH93tXDbs/8Dg1736vceohkk5nyOqRJmKtA2/krZ2fQyO4u2L1m', 'admin@posung.com', 1, 'Active', NOW()),
(2, 'hrmanager', '$2y$10$qIZH93tXDbs/8Dg1736vceohkk5nyOqRJmKtA2/krZ2fQyO4u2L1m', 'hr@posung.com', 2, 'Active', NOW()),
(3, 'pm_amkor', '$2y$10$qIZH93tXDbs/8Dg1736vceohkk5nyOqRJmKtA2/krZ2fQyO4u2L1m', 'pm.amkor@posung.com', 3, 'Active', NOW()),
(4, 'pm_daewoo', '$2y$10$qIZH93tXDbs/8Dg1736vceohkk5nyOqRJmKtA2/krZ2fQyO4u2L1m', 'pm.daewoo@posung.com', 3, 'Active', NOW()),
(5, 'worker1', '$2y$10$qIZH93tXDbs/8Dg1736vceohkk5nyOqRJmKtA2/krZ2fQyO4u2L1m', 'worker1@posung.com', 4, 'Active', NOW());

INSERT INTO `departments` (`id`, `dept_code`, `dept_name`, `status`) VALUES
(1, 'BIM', 'Khối Kỹ sư BIM & Văn phòng', 'Active'),
(2, 'FIELD', 'Khối Kỹ sư Hiện trường', 'Active'),
(3, 'EXPAT', 'Khối Expat', 'Active'),
(4, 'WELD', 'Khối Thợ hàn', 'Active');

INSERT INTO `projects` (`id`, `project_code`, `project_name`, `location`, `status`) VALUES
(1, 'P-AMK', 'Amkor Bắc Ninh', 'Bắc Ninh', 'In_Progress'),
(2, 'P-DAE', 'Daewoo Starlake', 'Hà Nội', 'In_Progress'),
(3, 'P-HQ', 'Trụ sở chính', 'Hà Nội', 'Active');

INSERT INTO `positions` (`id`, `pos_code`, `pos_title`, `job_level`) VALUES
(1, 'DIR', 'Giám đốc dự án', 5),
(2, 'BIM_ENG', 'Kỹ sư BIM', 3),
(3, 'FIELD_ENG', 'Kỹ sư Hiện trường', 3),
(4, 'WELDER_6G', 'Thợ hàn 6G', 2),
(5, 'WORKER', 'Công nhân', 1);

INSERT INTO `employees` (`id`, `emp_code`, `full_name`, `department_id`, `position_id`, `current_project_id`, `status`, `id_card_no`) VALUES
(1, 'EMP-EXP-01', 'Kim Jong Un (PM)', 3, 1, 1, 'Active', 'CCCD_EXP_1'),
(2, 'EMP-EXP-02', 'Lee Min Ho', 3, 1, 2, 'Active', 'CCCD_EXP_2'),
(3, 'EMP-EXP-03', 'Park Seo Joon', 3, 3, 1, 'Active', 'CCCD_EXP_3'),
(4, 'EMP-EXP-04', 'Song Joong Ki', 3, 3, 2, 'Active', 'CCCD_EXP_4'),
(5, 'EMP-BIM-01', 'Nguyễn Văn BIM 1', 1, 2, 3, 'Active', 'CCCD_BIM_1'),
(6, 'EMP-BIM-02', 'Trần Thị BIM 2', 1, 2, 3, 'Active', 'CCCD_BIM_2'),
(7, 'EMP-BIM-03', 'Lê Văn BIM 3', 1, 2, 3, 'Active', 'CCCD_BIM_3'),
(8, 'EMP-BIM-04', 'Phạm Thị BIM 4', 1, 2, 3, 'Active', 'CCCD_BIM_4'),
(9, 'EMP-BIM-05', 'Hoàng Văn BIM 5', 1, 2, 3, 'Active', 'CCCD_BIM_5'),
(10, 'EMP-FLD-01', 'Nguyễn Hiện Trường 1', 2, 3, 1, 'Active', 'CCCD_FLD_1'),
(11, 'EMP-FLD-02', 'Trần Hiện Trường 2', 2, 3, 1, 'Active', 'CCCD_FLD_2'),
(12, 'EMP-FLD-03', 'Lê Hiện Trường 3', 2, 3, 2, 'Active', 'CCCD_FLD_3'),
(13, 'EMP-FLD-04', 'Phạm Hiện Trường 4', 2, 3, 2, 'Active', 'CCCD_FLD_4'),
(14, 'EMP-FLD-05', 'Hoàng Hiện Trường 5', 2, 3, 1, 'Active', 'CCCD_FLD_5'),
(15, 'EMP-WLD-01', 'Thợ Hàn Nguyễn Một', 4, 4, 1, 'Active', 'CCCD_WLD_1'),
(16, 'EMP-WLD-02', 'Thợ Hàn Trần Hai', 4, 4, 2, 'Active', 'CCCD_WLD_2'),
(17, 'EMP-WLD-03', 'Thợ Hàn Lê Ba', 4, 4, 1, 'Active', 'CCCD_WLD_3'),
(18, 'EMP-WLD-04', 'Thợ Hàn Phạm Bốn', 4, 4, 2, 'Active', 'CCCD_WLD_4'),
(19, 'EMP-WLD-05', 'Thợ Hàn Hoàng Năm', 4, 4, 1, 'Active', 'CCCD_WLD_5'),
(20, 'EMP-WLD-06', 'Công Nhân Lỗi An Toàn', 4, 5, 2, 'Blacklisted', 'CCCD_WLD_6');

INSERT INTO `expat_details` (`employee_id`, `visa_expiry`, `trc_expiry`, `work_permit_expiry`) VALUES
(1, '2026-10-09', '2026-10-24', '2026-11-23'),
(2, '2026-10-24', '2026-11-23', '2026-10-09'),
(3, '2026-11-23', '2026-10-09', '2026-10-24'),
(4, '2026-10-09', '2026-10-09', '2026-10-09');

INSERT INTO `certificates` (`employee_id`, `cert_name`, `cert_type`, `issue_date`, `expiry_date`, `provider`) VALUES
(15, 'Chứng chỉ Hàn 6G', 'Weld_6G', '2023-01-01', '2025-01-01', 'Trung tâm KĐ'),
(16, 'Chứng chỉ Hàn 6G', 'Weld_6G', '2023-02-01', '2025-02-01', 'Trung tâm KĐ'),
(17, 'Chứng chỉ Hàn 6G', 'Weld_6G', '2023-03-01', '2025-03-01', 'Trung tâm KĐ'),
(18, 'Chứng chỉ Hàn 6G', 'Weld_6G', '2023-04-01', '2025-04-01', 'Trung tâm KĐ'),
(19, 'Chứng chỉ Hàn 6G', 'Weld_6G', '2023-05-01', '2025-05-01', 'Trung tâm KĐ');

INSERT INTO `rewards_disciplines` (`employee_id`, `type`, `decision_number`, `decision_date`, `title`, `reason`, `is_safety_violation`, `department_id`, `project_id`) VALUES
(20, 'Discipline', 'QD-KL-001', '2024-01-01', 'Vi phạm nghiêm trọng an toàn lao động (HSE)', 'Không tuân thủ quy tắc làm việc trên cao', 1, 4, 2);

SET FOREIGN_KEY_CHECKS = 1;
