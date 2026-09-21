<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/app/core/Database.php';

try {
    $db = Database::getInstance();
    $db->query("ALTER TABLE projects ADD COLUMN headcount_quota INT NOT NULL DEFAULT 0 AFTER cost_center_code");
    echo "Thêm cột headcount_quota thành công!\n";
} catch (Exception $e) {
    echo "Lỗi: " . $e->getMessage() . "\n";
}
