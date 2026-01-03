<?php
/**
 * Mindline EMHR
 * Auto-save Note API - Session-based authentication
 * Saves note draft for auto-recovery
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
error_log("Auto-save note API called - Session ID: " . session_id());

// Set JSON header
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log("Auto-save note: Invalid method - " . $_SERVER['REQUEST_METHOD']);
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Check if user is authenticated via session
if (!isset($_SESSION['authUserID']) || empty($_SESSION['authUserID'])) {
    error_log("Auto-save note: Not authenticated - authUserID not set");
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

error_log("Auto-save note: User authenticated - " . $_SESSION['authUserID']);

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        throw new Exception('Invalid JSON input');
    }

    // Required fields
    $patientId = intval($input['patientId']);
    $providerId = intval($_SESSION['authUserID']);
    $noteType = $input['noteType'];
    $serviceDate = $input['serviceDate'];
    $draftContent = json_encode($input['draftContent']); // Store entire form state

    // Optional note ID (if editing existing note)
    $noteId = isset($input['noteId']) ? intval($input['noteId']) : null;
    $appointmentId = isset($input['appointmentId']) ? intval($input['appointmentId']) : null;

    // Check if draft already exists for this note/appointment/patient
    $checkSql = "SELECT id FROM note_drafts WHERE provider_id = ? AND patient_id = ?";
    $checkParams = [$providerId, $patientId];

    if ($noteId) {
        $checkSql .= " AND note_id = ?";
        $checkParams[] = $noteId;
    } elseif ($appointmentId) {
        $checkSql .= " AND appointment_id = ?";
        $checkParams[] = $appointmentId;
    } else {
        // For new notes without appointment, match by note_type and service_date
        $checkSql .= " AND note_type = ? AND service_date = ? AND note_id IS NULL";
        $checkParams[] = $noteType;
        $checkParams[] = $serviceDate;
    }

    $checkResult = sqlStatement($checkSql, $checkParams);
    $existingDraft = sqlFetchArray($checkResult);

    if ($existingDraft) {
        // Update existing draft
        $updateSql = "UPDATE note_drafts SET
            draft_content = ?,
            note_type = ?,
            service_date = ?,
            saved_at = NOW()
            WHERE id = ?";

        $updateParams = [
            $draftContent,
            $noteType,
            $serviceDate,
            $existingDraft['id']
        ];

        sqlStatement($updateSql, $updateParams);
        $draftId = $existingDraft['id'];

        error_log("Updated existing draft ID: " . $draftId);
    } else {
        // Insert new draft
        $insertSql = "INSERT INTO note_drafts (
            note_id,
            provider_id,
            patient_id,
            appointment_id,
            draft_content,
            note_type,
            service_date
        ) VALUES (?, ?, ?, ?, ?, ?, ?)";

        $insertParams = [
            $noteId,
            $providerId,
            $patientId,
            $appointmentId,
            $draftContent,
            $noteType,
            $serviceDate
        ];

        sqlStatement($insertSql, $insertParams);
        $draftId = $GLOBALS['adodb']['db']->Insert_ID();

        error_log("Created new draft ID: " . $draftId);
    }

    $response = [
        'success' => true,
        'draftId' => $draftId,
        'message' => 'Draft saved successfully',
        'savedAt' => date('Y-m-d H:i:s')
    ];

    http_response_code(200);
    echo json_encode($response);

} catch (Exception $e) {
    error_log("Error auto-saving note: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to save draft',
        'message' => $e->getMessage()
    ]);
}
