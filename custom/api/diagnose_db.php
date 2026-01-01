<?php
$ignoreAuth = true;
require_once(__DIR__ . '/../../interface/globals.php');
header('Content-Type: application/json');

try {
    // Check tables
    $tables = sqlStatement("SHOW TABLES");
    $tableList = [];
    while ($row = sqlFetchArray($tables)) {
        $tableList[] = array_values($row)[0];
    }

    // Check if the categories table exists
    $categoriesTable = null;
    foreach ($tableList as $table) {
        if (stripos($table, 'calendar') !== false && stripos($table, 'categories') !== false) {
            $categoriesTable = $table;
            break;
        }
    }

    $result = [
        'tables_with_calendar' => array_filter($tableList, function($t) {
            return stripos($t, 'calendar') !== false;
        }),
        'categories_table' => $categoriesTable
    ];

    // If we found a categories table, describe it
    if ($categoriesTable) {
        $describe = sqlStatement("DESCRIBE $categoriesTable");
        $columns = [];
        while ($col = sqlFetchArray($describe)) {
            $columns[] = $col['Field'];
        }
        $result['categories_columns'] = $columns;

        // Get sample data
        $sample = sqlQuery("SELECT * FROM $categoriesTable LIMIT 1");
        $result['sample_category'] = $sample;
    }

    echo json_encode($result, JSON_PRETTY_PRINT);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
