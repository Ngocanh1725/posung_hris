<?php
require 'app/core/Database.php';
require 'config/config.php';
$db = Database::getInstance();
$db->query("DESCRIBE positions");
$columns = $db->fetchAll();
print_r($columns);
