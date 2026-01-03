<?php
/**
 * Check what calendar tables and columns actually exist in the database
 */

// IMPORTANT: Set these BEFORE loading globals.php to prevent redirects
$ignoreAuth = true;
$ignoreAuth_onsite_portal = true;
$ignoreAuth_onsite_portal_two = true;

require_once(__DIR__ . '/../../interface/globals.php');

header('Content-Type: text/plain');

echo "=== Checking Calendar Tables in Database ===\n\n";

// Check what tables exist with 'calendar' in the name
echo "1. Tables with 'calendar' or 'postcalendar' in name:\n";
$tables = sqlStatement("SHOW TABLES LIKE '%calendar%'");
while ($row = sqlFetchArray($tables)) {
    $tableName = array_values($row)[0];
    echo "   - $tableName\n";
}

echo "\n2. Checking openemr_postcalendar_categories structure:\n";
try {
    $describe = sqlStatement("DESCRIBE openemr_postcalendar_categories");
    echo "   Columns in openemr_postcalendar_categories:\n";
    while ($col = sqlFetchArray($describe)) {
        echo "   - {$col['Field']} ({$col['Type']}) {$col['Null']}\n";
    }
} catch (Exception $e) {
    echo "   ERROR: " . $e->getMessage() . "\n";
}

echo "\n3. Sample category data:\n";
try {
    $cats = sqlStatement("SELECT pc_catid, pc_catname, pc_cattype FROM openemr_postcalendar_categories LIMIT 5");
    while ($cat = sqlFetchArray($cats)) {
        echo "   - ID: {$cat['pc_catid']}, Name: {$cat['pc_catname']}, Type: {$cat['pc_cattype']}\n";
    }
} catch (Exception $e) {
    echo "   ERROR: " . $e->getMessage() . "\n";
}
