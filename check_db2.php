<?php
require 'app/core/Database.php';
require 'config/config.php';
$db = Database::getInstance();
$db->query("SELECT pos_title, job_level FROM positions");
$rows = $db->fetchAll();
print_r($rows);
