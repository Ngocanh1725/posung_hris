<?php
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/core/Database.php';

$db = new Database();
$db->query("DESCRIBE employees");
$cols = $db->fetchAll();
foreach ($cols as $col) {
    echo $col['Field'] . "\n";
}
