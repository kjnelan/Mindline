<?php
/**
 * SanctumEMHR EMHR
 * Get Client API - Session-based authentication
 * Returns client details including payment type
 *
 * Author: Kenneth J. Nelan
 * License: Proprietary and Confidential
 * Version: ALPHA
 *
 * Copyright © 2026 Sacred Wandering
 * Proprietary and Confidential
 */

require_once(__DIR__ . '/../init.php');

use Custom\Lib\Database\Database;
use Custom\Lib\Session\SessionManager;

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
    error_log("Get client: Invalid method - " . $_SERVER['REQUEST_METHOD']);
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    // Initialize session and check authentication
    $session = SessionManager::getInstance();
    $session->start();

    if (!$session->isAuthenticated()) {
        error_log("Get client: Not authenticated");
        http_response_code(401);
        echo json_encode(['error' => 'Not authenticated']);
        exit;
    }

    error_log("Get client: User authenticated - " . $session->getUserId());

    // Get client ID from query parameter
    $pid = isset($_GET['pid']) ? intval($_GET['pid']) : null;

    if (!$pid) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing patient ID']);
        exit;
    }

    // Initialize database
    $db = Database::getInstance();

    // Fetch client details
    $sql = "SELECT
        id,
        fname,
        lname,
        mname,
        DOB,
        sex,
        street,
        postal_code,
        city,
        state,
        country_code,
        phone_home,
        phone_cell,
        email,
        payment_type,
        custom_session_fee,
        occupation,
        status
    FROM clients
    WHERE id = ?";

    $client = $db->query($sql, [$pid]);

    if (!$client) {
        http_response_code(404);
        echo json_encode(['error' => 'Client not found']);
        exit;
    }

    error_log("Get client: Found client ID $pid");

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'client' => [
            'id' => $client['id'],
            'fname' => $client['fname'],
            'lname' => $client['lname'],
            'mname' => $client['mname'],
            'DOB' => $client['DOB'],
            'sex' => $client['sex'],
            'street' => $client['street'],
            'postal_code' => $client['postal_code'],
            'city' => $client['city'],
            'state' => $client['state'],
            'country_code' => $client['country_code'],
            'phone_home' => $client['phone_home'],
            'phone_cell' => $client['phone_cell'],
            'email' => $client['email'],
            'payment_type' => $client['payment_type'],
            'custom_session_fee' => $client['custom_session_fee'],
            'occupation' => $client['occupation'],
            'status' => $client['status']
        ]
    ]);

} catch (Exception $e) {
    error_log("Get client: Error - " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch client',
        'message' => $e->getMessage()
    ]);
}
