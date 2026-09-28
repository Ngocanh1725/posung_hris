<?php
require 'config/config.php';
$pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME, DB_USER, DB_PASS);
print_r($pdo->query('SHOW COLUMNS FROM employees WHERE Field = "id"')->fetch(PDO::FETCH_ASSOC));
print_r($pdo->query('SHOW COLUMNS FROM contracts WHERE Field = "employee_id"')->fetch(PDO::FETCH_ASSOC));
