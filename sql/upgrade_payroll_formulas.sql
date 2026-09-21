CREATE TABLE `payroll_formulas` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `description` text DEFAULT NULL,
    `contract_type_id` int(10) unsigned DEFAULT NULL COMMENT 'Áp dụng cho loại hợp đồng nào (NULL = Tất cả)',
    `is_active` tinyint(1) NOT NULL DEFAULT 1,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `contract_type_id` (`contract_type_id`),
    CONSTRAINT `fk_formula_contract_type` FOREIGN KEY (`contract_type_id`) REFERENCES `contract_types` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `payroll_formula_items` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `formula_id` int(11) NOT NULL,
    `item_code` varchar(50) NOT NULL COMMENT 'Mã hạng mục, ví dụ: BASIC_SALARY, WORKING_DAYS, TAX, ALLOWANCE_X',
    `item_name` varchar(150) NOT NULL,
    `item_type` enum('Earning','Deduction','Calculation') NOT NULL COMMENT 'Khoản cộng, Khoản trừ, hay Biến tính toán',
    `formula_expression` text DEFAULT NULL COMMENT 'Công thức tính toán (nếu có), hỗ trợ các biến',
    `sort_order` int(11) NOT NULL DEFAULT 0,
    `is_taxable` tinyint(1) NOT NULL DEFAULT 0,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `formula_id` (`formula_id`),
    CONSTRAINT `fk_formula_items_formula` FOREIGN KEY (`formula_id`) REFERENCES `payroll_formulas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
