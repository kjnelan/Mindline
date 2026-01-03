<?php
/**
 * Mindline EMHR
 * Get Patient Notes API - Session-based authentication
 * Returns all clinical notes for a patient
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
error_log("Get patient notes API called - Session ID: " . session_id());

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
    error_log("Get patient notes: Invalid method - " . $_SERVER['REQUEST_METHOD']);
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Check if user is authenticated via session
if (!isset($_SESSION['authUserID']) || empty($_SESSION['authUserID'])) {
    error_log("Get patient notes: Not authenticated - authUserID not set");
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

// Get patient ID from query parameter
$patientId = $_GET['patient_id'] ?? null;

if (!$patientId) {
    error_log("Get patient notes: No patient ID provided");
    http_response_code(400);
    echo json_encode(['error' => 'Patient ID is required']);
    exit;
}

// Optional filters
$noteType = $_GET['note_type'] ?? null;
$status = $_GET['status'] ?? null;
$startDate = $_GET['start_date'] ?? null;
$endDate = $_GET['end_date'] ?? null;

error_log("Get patient notes: User authenticated - " . $_SESSION['authUserID'] . ", fetching notes for patient ID: " . $patientId);

try {
    // Build SQL query with optional filters
    $sql = "SELECT
        n.id,
        n.note_uuid,
        n.patient_id,
        n.provider_id,
        n.appointment_id,
        n.billing_id,
        n.note_type,
        n.template_type,
        n.service_date,
        n.service_duration,
        n.service_location,
        n.behavior_problem,
        n.intervention,
        n.response,
        n.plan,
        n.risk_assessment,
        n.risk_present,
        n.goals_addressed,
        n.interventions_selected,
        n.client_presentation,
        n.diagnosis_codes,
        n.presenting_concerns,
        n.clinical_observations,
        n.mental_status_exam,
        n.status,
        n.is_locked,
        n.signed_at,
        n.signed_by,
        n.supervisor_review_required,
        n.supervisor_review_status,
        n.supervisor_signed_at,
        n.supervisor_signed_by,
        n.supervisor_comments,
        n.parent_note_id,
        n.is_addendum,
        n.addendum_reason,
        n.created_at,
        n.updated_at,
        n.locked_at,
        CONCAT(p.fname, ' ', p.lname) AS provider_name,
        CONCAT(sb.fname, ' ', sb.lname) AS signed_by_name,
        CONCAT(ss.fname, ' ', ss.lname) AS supervisor_name
    FROM clinical_notes n
    LEFT JOIN users p ON p.id = n.provider_id
    LEFT JOIN users sb ON sb.id = n.signed_by
    LEFT JOIN users ss ON ss.id = n.supervisor_signed_by
    WHERE n.patient_id = ?";

    $params = [$patientId];

    // Add optional filters
    if ($noteType) {
        $sql .= " AND n.note_type = ?";
        $params[] = $noteType;
    }

    if ($status) {
        $sql .= " AND n.status = ?";
        $params[] = $status;
    }

    if ($startDate) {
        $sql .= " AND n.service_date >= ?";
        $params[] = $startDate;
    }

    if ($endDate) {
        $sql .= " AND n.service_date <= ?";
        $params[] = $endDate;
    }

    // Order by service date descending (most recent first)
    $sql .= " ORDER BY n.service_date DESC, n.created_at DESC";

    error_log("Notes SQL: " . $sql);
    error_log("Params: " . print_r($params, true));

    $result = sqlStatement($sql, $params);
    $notes = [];

    while ($row = sqlFetchArray($result)) {
        // Decode JSON fields
        $row['goals_addressed'] = $row['goals_addressed'] ? json_decode($row['goals_addressed'], true) : null;
        $row['interventions_selected'] = $row['interventions_selected'] ? json_decode($row['interventions_selected'], true) : null;
        $row['client_presentation'] = $row['client_presentation'] ? json_decode($row['client_presentation'], true) : null;
        $row['diagnosis_codes'] = $row['diagnosis_codes'] ? json_decode($row['diagnosis_codes'], true) : null;

        // Convert boolean fields
        $row['risk_present'] = (bool)$row['risk_present'];
        $row['is_locked'] = (bool)$row['is_locked'];
        $row['supervisor_review_required'] = (bool)$row['supervisor_review_required'];
        $row['is_addendum'] = (bool)$row['is_addendum'];

        $notes[] = $row;
    }

    error_log("Found " . count($notes) . " clinical notes for patient");

    // Build response
    $response = [
        'success' => true,
        'patient_id' => $patientId,
        'notes' => $notes,
        'total_count' => count($notes),
        'filters' => [
            'note_type' => $noteType,
            'status' => $status,
            'start_date' => $startDate,
            'end_date' => $endDate
        ]
    ];

    error_log("Get patient notes: Successfully built response for patient " . $patientId);
    http_response_code(200);
    echo json_encode($response);

} catch (Exception $e) {
    error_log("Error fetching patient notes: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to fetch patient notes',
        'message' => $e->getMessage()
    ]);
}
