<?php
require_once 'config/config.php';
require_once 'app/core/Database.php';
$db = Database::getInstance();
$db->query("DESCRIBE positions;"); print_r($db->fetchAll());
$db->query("DESCRIBE certificates;"); print_r($db->fetchAll());
$db->query("DESCRIBE projects;"); print_r($db->fetchAll());
$db->query("DESCRIBE contracts;"); print_r($db->fetchAll());
