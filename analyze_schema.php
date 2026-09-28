<?php
$schema = json_decode(file_get_contents('db_schema.json'), true);
$patch = "-- ============================================================\n";
$patch .= "-- POSUNG HRIS - BẢN VÁ CẬP NHẬT (PATCH) V7 - AUDIT\n";
$patch .= "-- ============================================================\n\n";

$tables = array_keys($schema);

// Mapping common foreign keys based on column names
$fkRules = [
    'employee_id' => ['table' => 'employees', 'col' => 'id'],
    'department_id' => ['table' => 'departments', 'col' => 'id'],
    'project_id' => ['table' => 'projects', 'col' => 'id'],
    'current_project_id' => ['table' => 'projects', 'col' => 'id'],
    'position_id' => ['table' => 'positions', 'col' => 'id'],
    'role_id' => ['table' => 'roles', 'col' => 'id'],
    'module_id' => ['table' => 'modules', 'col' => 'id'],
    'sub_id' => ['table' => 'subcontractors', 'col' => 'id']
];

foreach ($schema as $tableName => $tableData) {
    // Collect existing FKs
    $existingFks = [];
    foreach ($tableData['fks'] as $fk) {
        $existingFks[] = $fk['COLUMN_NAME'];
    }

    // Collect existing indexes
    $existingIndexes = [];
    foreach ($tableData['indexes'] as $idx) {
        $existingIndexes[] = $idx['Column_name'];
    }

    foreach ($tableData['columns'] as $colInfo) {
        $colName = $colInfo['Field'];
        $type = strtolower($colInfo['Type']);

        // Check for missing FKs
        if (isset($fkRules[$colName]) && !in_array($colName, $existingFks) && $tableName != $fkRules[$colName]['table']) {
            $refTable = $fkRules[$colName]['table'];
            $refCol = $fkRules[$colName]['col'];
            
            // Check if refTable exists
            if (in_array($refTable, $tables)) {
                $patch .= "ALTER TABLE `$tableName` ADD CONSTRAINT `fk_{$tableName}_{$colName}` FOREIGN KEY (`$colName`) REFERENCES `$refTable`(`$refCol`) ON DELETE SET NULL ON UPDATE CASCADE;\n";
            }
        }

        // Check for missing indexes on FK columns or commonly queried columns
        $isFkCol = isset($fkRules[$colName]);
        $isCommonCol = in_array($colName, ['emp_code', 'month', 'year', 'status', 'project_code']);
        
        if (($isFkCol || $isCommonCol) && !in_array($colName, $existingIndexes) && $colName != 'id') {
            $patch .= "ALTER TABLE `$tableName` ADD INDEX `idx_{$tableName}_{$colName}` (`$colName`);\n";
        }
    }
}

file_put_contents('sql/patch_posung_construction_v7_audit.sql', $patch);
echo "SQL patch v7 generated.";
