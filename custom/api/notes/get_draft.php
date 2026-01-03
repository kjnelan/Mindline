<?php
/**
 * Mindline EMHR
 * Get Draft API - Session-based authentication
 * Retrieves saved draft for a note
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
error_log("Get draft API called - Session ID: " . session_id());

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
    error_log("Get draft: Invalid method - " . $_SERVER['REQUEST_METHOD']);
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Check if user is authenticated via session
if (!isset($_SESSION['authUserID']) || empty($_SESSION['authUserID'])) {
    error_log("Get draft: Not authenticated - authUserID not set");
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$userId = intval($_SESSION['authUserID']);

// Get query parameters
$noteId = $_GET['note_id'] ?? null;
$appointmentId = $_GET['appointment_id'] ?? null;
$patientId = $_GET['patient_id'] ?? null;

error_log("Get draft: User authenticated - " . $userId);

try {
    // Build query based on available parameters
    $sql = "SELECT
        d.id,
        d.note_id,
        d.provider_id,
        d.patient_id,
        d.appointment_id,
        d.draft_content,
        d.note_type,
        d.service_date,
        d.saved_at
    FROM note_drafts d
    WHERE d.provider_id = ?";

    $params = [$userId];

    if ($noteId) {
        $sql .= " AND d.note_id = ?";
        $params[] = intval($noteId);
    } elseif ($appointmentId) {
        $sql .= " AND d.appointment_id = ?";
        $params[] = intval($appointmentId);
    } elseif ($patientId) {
        $sql .= " AND d.patient_id = ?";
        $params[] = intval($patientId);
        // Get most recent draft for this patient
        $sql .= " ORDER BY d.saved_at DESC LIMIT 1";
    } else {
        // No specific identifier - get all user's drafts
        $sql .= " ORDER BY d.saved_at DESC";
    }

    error_log("Draft SQL: " . $sql);
    error_log("Params: " . print_r($params, true));

    $result = sqlStatement($sql, $params);
    $drafts = [];

    while ($row = sqlFetchArray($result)) {
        // Decode draft content JSON
        $row['draft_content'] = json_decode($row['draft_content'], true);
        $drafts[] = $row;
    }

    if (empty($drafts)) {
        error_log("No draft found");
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'No draft found'
        ]);
        exit;
    }

    error_log("Found " . count($drafts) . " draft(s)");

    // If looking for specific draft, return single object
    if ($noteId || $appointmentId) {
        $response = [
            'success' => true,
            'draft' => $drafts[0]
        ];
    } else {
        // Return array of drafts
        $response = [
            'success' => true,
            'drafts' => $drafts,
            'count' => count($drafts)
        ];
    }

    http_response_code(200);
    echo json_encode($response);

} catch (Exception $e) {
    error_log("Error fetching draft: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to fetch draft',
        'message' => $e->getMessage()
    ]);
}
