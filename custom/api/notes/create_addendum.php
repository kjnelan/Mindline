<?php
/**
 * Mindline EMHR
 * Create Addendum API - Session-based authentication
 * Creates an addendum to a locked clinical note
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
error_log("Create addendum API called - Session ID: " . session_id());

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
    error_log("Create addendum: Invalid method - " . $_SERVER['REQUEST_METHOD']);
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Check if user is authenticated via session
if (!isset($_SESSION['authUserID']) || empty($_SESSION['authUserID'])) {
    error_log("Create addendum: Not authenticated - authUserID not set");
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$userId = intval($_SESSION['authUserID']);
error_log("Create addendum: User authenticated - " . $userId);

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        throw new Exception('Invalid JSON input');
    }

    error_log("Create addendum input: " . print_r($input, true));

    // Validate required fields
    $required = ['parentNoteId', 'addendumReason', 'addendumContent'];
    foreach ($required as $field) {
        if (!isset($input[$field]) || $input[$field] === '') {
            throw new Exception("Missing required field: $field");
        }
    }

    $parentNoteId = intval($input['parentNoteId']);
    $addendumReason = $input['addendumReason'];
    $addendumContent = $input['addendumContent'];

    // Verify parent note exists and is locked
    $checkSql = "SELECT id, patient_id, provider_id, note_type, service_date, is_locked
                 FROM clinical_notes
                 WHERE id = ?";
    $checkResult = sqlStatement($checkSql, [$parentNoteId]);
    $parentNote = sqlFetchArray($checkResult);

    if (!$parentNote) {
        throw new Exception("Parent note not found");
    }

    // Check system setting for post-signature edits
    $settingSql = "SELECT setting_value FROM clinical_settings WHERE setting_key = 'allow_post_signature_edits'";
    $settingResult = sqlStatement($settingSql);
    $setting = sqlFetchArray($settingResult);
    $allowAddenda = $setting && $setting['setting_value'] === 'true';

    if (!$allowAddenda) {
        throw new Exception("System does not allow post-signature addenda");
    }

    if (!$parentNote['is_locked']) {
        throw new Exception("Can only create addenda for locked notes. Edit the note directly instead.");
    }

    // Generate UUID for addendum
    $addendumUuid = sprintf(
        '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );

    // Create addendum as new note linked to parent
    $addendumSql = "INSERT INTO clinical_notes (
        note_uuid,
        patient_id,
        provider_id,
        note_type,
        template_type,
        service_date,
        parent_note_id,
        is_addendum,
        addendum_reason,
        plan,
        status,
        is_locked
    ) VALUES (?, ?, ?, ?, 'addendum', ?, ?, 1, ?, ?, 'draft', 0)";

    $addendumParams = [
        $addendumUuid,
        $parentNote['patient_id'],
        $userId,
        $parentNote['note_type'],
        $parentNote['service_date'],
        $parentNoteId,
        $addendumReason,
        $addendumContent
    ];

    error_log("Creating addendum SQL: " . $addendumSql);
    error_log("Params: " . print_r($addendumParams, true));

    sqlStatement($addendumSql, $addendumParams);
    $addendumId = $GLOBALS['adodb']['db']->Insert_ID();

    error_log("Addendum created successfully with ID: " . $addendumId);

    $response = [
        'success' => true,
        'addendumId' => $addendumId,
        'addendumUuid' => $addendumUuid,
        'parentNoteId' => $parentNoteId,
        'message' => 'Addendum created successfully'
    ];

    http_response_code(201);
    echo json_encode($response);

} catch (Exception $e) {
    error_log("Error creating addendum: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to create addendum',
        'message' => $e->getMessage()
    ]);
}
