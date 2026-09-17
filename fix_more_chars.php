<?php
require_once 'app/core/Database.php';
require_once 'config/config.php';
$db = Database::getInstance();

$tables = [
    'employees' => ['highest_degree', 'id_card_place', 'hometown']
];

foreach ($tables as $table => $columns) {
    foreach ($columns as $column) {
        try {
            $db->query("UPDATE $table SET $column = CAST(CONVERT($column USING cp850) AS BINARY) WHERE $column IS NOT NULL AND $column != ''");
            echo "Updated $table.$column\n";
        } catch (Exception $e) {
            echo "Error updating $table.$column: " . $e->getMessage() . "\n";
        }
    }
}
echo "Done.\n";
