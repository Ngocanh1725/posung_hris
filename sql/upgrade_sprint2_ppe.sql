CREATE TABLE `emp_ppe_issuances` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(10) unsigned NOT NULL,
  `item_name` varchar(150) NOT NULL COMMENT 'Tên vật tư/BHLĐ (VD: Giày, Mũ, Áo phản quang)',
  `issue_date` date NOT NULL,
  `return_date` date DEFAULT NULL,
  `status` enum('Issued','Returned','Lost','Damaged') NOT NULL DEFAULT 'Issued',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  CONSTRAINT `fk_ppe_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
