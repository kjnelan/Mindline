<?php
/**
 * Mindline EMHR
 * Get Clinical Settings API - Session-based authentication
 * Returns clinical documentation system settings
 *
 * Author: Kenneth J. Nelan
 * License: Proprietary and Confidential
 * Version: ALPHA - Phase 4
 *
 * Copyright © 2026 Sacred Wandering
 * Proprietary and Confidential
 */

// Start output buffering to prevent any PHP warnings/notices from breaking JSON
ob_start();

// IMPORTANT: Set these BEFORE loading globals.php to prevent redirects
$ignoreAuth = true;
$ignoreAuth_onsite_portal = true;
$ignoreAuth_onsite_portal_two = true;

require_once(__DIR__ . '/../../../interface/globals.php');

// Clear any output that globals.php might have generated
ob_end_clean();

// Enable error logging
error_log("Get clinical settings API called - Session ID: " . session_id());

// Set JSON header
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    error_log("Get clinical settings: Invalid method - " . $_SERVER['REQUEST_METHOD']);
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Check if user is authenticated via session
if (!isset($_SESSION['authUserID']) || empty($_SESSION['authUserID'])) {
    error_log("Get clinical settings: Not authenticated - authUserID not set");
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

error_log("Get clinical settings: User authenticated - " . $_SESSION['authUserID']);

try {
    // Fetch all clinical settings
    $sql = "SELECT
        setting_key,
        setting_value,
        setting_type,
        updated_at,
        CONCAT(u.fname, ' ', u.lname) AS updated_by_name
    FROM clinical_settings s
    LEFT JOIN users u ON u.id = s.updated_by
    ORDER BY setting_key";

    $result = sqlStatement($sql);
    $settings = [];
    $settingsMap = [];

    while ($row = sqlFetchArray($result)) {
        $key = $row['setting_key'];
        $value = $row['setting_value'];
        $type = $row['setting_type'];

        // Convert value based on type
        if ($type === 'boolean') {
            $value = $value === 'true' || $value === '1';
        } elseif ($type === 'json') {
            $value = json_decode($value, true);
        } elseif ($type === 'number' || $type === 'integer') {
            $value = intval($value);
        }

        $settingsMap[$key] = $value;
        $settings[] = [
            'key' => $key,
            'value' => $value,
            'type' => $type,
            'updated_at' => $row['updated_at'],
            'updated_by' => $row['updated_by_name']
        ];
    }

    error_log("Found " . count($settings) . " clinical settings");

    // Build response with easy-access map
    $response = [
        'success' => true,
        'settings' => $settingsMap, // Key-value map for easy access
        'settings_detailed' => $settings, // Full details
        'total_count' => count($settings)
    ];

    http_response_code(200);
    echo json_encode($response);

} catch (Exception $e) {
    error_log("Error fetching clinical settings: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to fetch clinical settings',
        'message' => $e->getMessage()
    ]);
}
