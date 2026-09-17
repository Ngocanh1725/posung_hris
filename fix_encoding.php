<?php
require_once 'app/core/Database.php';
require_once 'config/config.php';

$db = Database::getInstance();

$tables = [
    'users' => ['full_name'],
    'employees' => ['full_name', 'address'],
    'departments' => ['dept_name'],
    'positions' => ['pos_title', 'description'],
    'projects' => ['project_name', 'location'],
    'rewards_disciplines' => ['title', 'reason', 'reward_form', 'discipline_form', 'authority_level', 'proposed_by'],
    'recruitment_requests' => ['description', 'requirements', 'benefits', 'work_location', 'notes'],
    'candidates' => ['full_name', 'address', 'major', 'university', 'current_company', 'current_position', 'source', 'skills', 'languages', 'interview_location', 'interviewer_name', 'interviewer_notes', 'rejection_reason', 'notes']
];

foreach ($tables as $table => $columns) {
    // Check if table exists
    try {
        $db->query("SELECT 1 FROM $table LIMIT 1");
    } catch (Exception $e) {
        continue;
    }
    
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
