<?php
require 'config/config.php';
require 'app/core/Database.php';

$db = Database::getInstance();
$tables = $db->query('SHOW TABLES')->fetchAll();
$schema = [];

foreach($tables as $row) {
    $t = array_values($row)[0];
    $schema[$t] = [
        'columns' => $db->query("SHOW COLUMNS FROM $t")->fetchAll(),
        'indexes' => $db->query("SHOW INDEX FROM $t")->fetchAll(),
        'fks'     => $db->query("SELECT COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = 'posung_hris' AND TABLE_NAME = '$t' AND REFERENCED_TABLE_NAME IS NOT NULL")->fetchAll()
    ];
}

file_put_contents('db_schema.json', json_encode($schema, JSON_PRETTY_PRINT));
echo "Schema dumped to db_schema.json";
