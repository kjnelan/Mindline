<?php
/**
 * Mindline EMHR
 * Sign Note API - Session-based authentication
 * Signs and locks a clinical note
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
error_log("Sign note API called - Session ID: " . session_id());

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
    error_log("Sign note: Invalid method - " . $_SERVER['REQUEST_METHOD']);
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Check if user is authenticated via session
if (!isset($_SESSION['authUserID']) || empty($_SESSION['authUserID'])) {
    error_log("Sign note: Not authenticated - authUserID not set");
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$userId = intval($_SESSION['authUserID']);
error_log("Sign note: User authenticated - " . $userId);

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        throw new Exception('Invalid JSON input');
    }

    error_log("Sign note input: " . print_r($input, true));

    // Validate required fields
    if (!isset($input['noteId']) || $input['noteId'] === '') {
        throw new Exception("Missing required field: noteId");
    }

    $noteId = intval($input['noteId']);
    $signatureData = $input['signatureData'] ?? null; // Optional electronic signature details

    // Check if note exists and is not already locked
    $checkSql = "SELECT id, is_locked, status, provider_id, supervisor_review_required, supervisor_review_status
                 FROM clinical_notes
                 WHERE id = ?";
    $checkResult = sqlStatement($checkSql, [$noteId]);
    $note = sqlFetchArray($checkResult);

    if (!$note) {
        throw new Exception("Note not found");
    }

    if ($note['is_locked']) {
        throw new Exception("Note is already signed and locked");
    }

    // Check if note requires supervisor review
    if ($note['supervisor_review_required'] && $note['supervisor_review_status'] !== 'approved') {
        throw new Exception("Note requires supervisor approval before signing");
    }

    // Optional: Check if user is the note author
    // if (intval($note['provider_id']) !== $userId) {
    //     throw new Exception("You can only sign your own notes");
    // }

    // Sign and lock the note
    $signSql = "UPDATE clinical_notes SET
        status = 'signed',
        is_locked = 1,
        signed_at = NOW(),
        signed_by = ?,
        signature_data = ?,
        locked_at = NOW()
        WHERE id = ?";

    $signParams = [
        $userId,
        $signatureData,
        $noteId
    ];

    error_log("Signing note SQL: " . $signSql);
    error_log("Params: " . print_r($signParams, true));

    sqlStatement($signSql, $signParams);

    error_log("Note signed and locked successfully: ID " . $noteId);

    $response = [
        'success' => true,
        'noteId' => $noteId,
        'message' => 'Note signed and locked successfully',
        'signedAt' => date('Y-m-d H:i:s')
    ];

    http_response_code(200);
    echo json_encode($response);

} catch (Exception $e) {
    error_log("Error signing note: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to sign note',
        'message' => $e->getMessage()
    ]);
}
