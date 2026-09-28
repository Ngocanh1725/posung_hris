<?php
require 'app/core/Database.php';
require 'config/config.php';
$db = Database::getInstance();
$db->query("DESCRIBE job_movements");
print_r($db->fetchAll());
