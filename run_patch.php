<?php
require 'config/config.php';
require 'app/core/Database.php';

$db = Database::getInstance();
$sql = file_get_contents('sql/patch_posung_construction_v4.sql');
$queries = explode(';', $sql);
foreach ($queries as $q) {
    $q = trim($q);
    if (!empty($q)) {
        try {
            $db->query($q);
            echo "Executed query successfully.\n";
        } catch (Exception $e) {
            echo "Error executing query: " . $e->getMessage() . "\n";
        }
    }
}
echo "Migration V4 completed!\n";
?>
