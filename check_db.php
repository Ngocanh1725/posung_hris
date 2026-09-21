<?php
require_once 'app/config/config.php';
require_once 'app/core/Database.php';
$db = Database::getInstance();
$db->query("DESCRIBE rewards_disciplines;");
print_r($db->fetchAll());
