<?php
/**
 * Mindline EMHR
 * Get Treatment Goals API - Session-based authentication
 * Returns treatment goals for a patient
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
error_log("Get treatment goals API called - Session ID: " . session_id());

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
    error_log("Get treatment goals: Invalid method - " . $_SERVER['REQUEST_METHOD']);
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Check if user is authenticated via session
if (!isset($_SESSION['authUserID']) || empty($_SESSION['authUserID'])) {
    error_log("Get treatment goals: Not authenticated - authUserID not set");
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

// Get patient ID from query parameter
$patientId = $_GET['patient_id'] ?? null;

if (!$patientId) {
    error_log("Get treatment goals: No patient ID provided");
    http_response_code(400);
    echo json_encode(['error' => 'Patient ID is required']);
    exit;
}

// Optional filters
$status = $_GET['status'] ?? 'active'; // Default to active goals only
$includeAll = isset($_GET['include_all']) ? boolval($_GET['include_all']) : false;

error_log("Get treatment goals: User authenticated - " . $_SESSION['authUserID'] . ", fetching goals for patient ID: " . $patientId);

try {
    // Build SQL query
    $sql = "SELECT
        g.id,
        g.patient_id,
        g.provider_id,
        g.goal_text,
        g.goal_category,
        g.target_date,
        g.status,
        g.progress_level,
        g.created_at,
        g.updated_at,
        g.achieved_at,
        g.discontinued_at,
        CONCAT(p.fname, ' ', p.lname) AS provider_name
    FROM treatment_goals g
    LEFT JOIN users p ON p.id = g.provider_id
    WHERE g.patient_id = ?";

    $params = [$patientId];

    // Add status filter if not including all
    if (!$includeAll && $status) {
        $sql .= " AND g.status = ?";
        $params[] = $status;
    }

    // Order by status (active first), then target date, then created date
    $sql .= " ORDER BY
        CASE g.status
            WHEN 'active' THEN 1
            WHEN 'achieved' THEN 2
            WHEN 'revised' THEN 3
            WHEN 'discontinued' THEN 4
            ELSE 5
        END,
        g.target_date ASC,
        g.created_at DESC";

    error_log("Treatment goals SQL: " . $sql);
    error_log("Params: " . print_r($params, true));

    $result = sqlStatement($sql, $params);
    $goals = [];

    while ($row = sqlFetchArray($result)) {
        $goals[] = $row;
    }

    error_log("Found " . count($goals) . " treatment goals for patient");

    // Group goals by status for easier frontend handling
    $grouped = [
        'active' => [],
        'achieved' => [],
        'revised' => [],
        'discontinued' => []
    ];

    foreach ($goals as $goal) {
        $goalStatus = $goal['status'];
        if (isset($grouped[$goalStatus])) {
            $grouped[$goalStatus][] = $goal;
        }
    }

    $response = [
        'success' => true,
        'patient_id' => $patientId,
        'goals' => $goals,
        'grouped' => $grouped,
        'total_count' => count($goals),
        'active_count' => count($grouped['active'])
    ];

    http_response_code(200);
    echo json_encode($response);

} catch (Exception $e) {
    error_log("Error fetching treatment goals: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to fetch treatment goals',
        'message' => $e->getMessage()
    ]);
}
