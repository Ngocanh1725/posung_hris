<?php
require 'config/config.php';
try {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

$sql = file_get_contents('sql/patch_posung_construction_v7_audit_fixed.sql');
$queries = explode(';', $sql);
$success = 0;
$fail = 0;
foreach ($queries as $q) {
    $q = trim($q);
    if (!empty($q)) {
        try {
            $pdo->exec($q);
            $success++;
        } catch (Exception $e) {
            echo "Error running: $q\n";
            echo "Message: " . $e->getMessage() . "\n\n";
            $fail++;
        }
    }
}
echo "Done. Success: $success, Fail: $fail\n";
