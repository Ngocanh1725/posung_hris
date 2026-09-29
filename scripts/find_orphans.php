<?php
require 'config/config.php';
try {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

$tablesToCheck = [
    'payrolls' => ['employee_id' => 'employees', 'project_id' => 'projects'],
    'timesheets' => ['employee_id' => 'employees'],
    'job_movements' => ['employee_id' => 'employees', 'new_project_id' => 'projects', 'new_department_id' => 'departments'],
    'employee_certificates' => ['employee_id' => 'employees'],
    'hse_safety_cards' => ['employee_id' => 'employees'],
    'rewards_disciplines' => ['employee_id' => 'employees', 'project_id' => 'projects', 'department_id' => 'departments']
];

$orphans = [];

foreach ($tablesToCheck as $table => $relations) {
    foreach ($relations as $col => $refTable) {
        $sql = "SELECT id FROM $table WHERE $col IS NOT NULL AND $col != 0 AND $col NOT IN (SELECT id FROM $refTable)";
        try {
            $res = $pdo->query($sql)->fetchAll();
            if (count($res) > 0) {
                $orphans[$table][$col] = count($res);
            }
        } catch(Exception $e) {
            // Ignore if table/col doesn't exist
        }
    }
}

print_r($orphans);

// Let's also search for ambiguous columns in app/models
