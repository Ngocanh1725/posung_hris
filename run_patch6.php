<?php
require 'config/config.php';
require 'app/core/Database.php';
$db = Database::getInstance();
$sql = file_get_contents('sql/patch_posung_construction_v6.sql');
$queries = explode(';', $sql);
foreach ($queries as $q) {
    $q = trim($q);
    if (!empty($q)) {
        try {
            $db->query($q);
            echo "Executed query.\n";
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage() . "\n";
        }
    }
}
?>
