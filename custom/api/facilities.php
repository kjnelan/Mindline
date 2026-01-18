<?php
/**
 * Mindline EMHR - Facilities API (MIGRATED TO MINDLINE)
 * Handles CRUD operations for facilities
 */

require_once(__DIR__ . '/../init.php');

use Custom\Lib\Database\Database;
use Custom\Lib\Session\SessionManager;

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    $session = SessionManager::getInstance();
    $session->start();

    if (!$session->isAuthenticated()) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }

    $db = Database::getInstance();
    $method = $_SERVER['REQUEST_METHOD'];
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $_GET['action'] ?? null;

    switch ($method) {
        case 'GET':
            if ($action === 'get' && isset($_GET['id'])) {
                // Get single facility
                $facilityId = filter_var($_GET['id'], FILTER_VALIDATE_INT);
                if (!$facilityId) {
                    throw new Exception('Invalid facility ID');
                }

                $sql = "SELECT
                    id, name, facility_type,
                    phone, fax, email, website,
                    address_line1, address_line2, city, state, zip,
                    npi, tax_id, facility_npi, pos_code,
                    is_active, is_primary, business_hours
                FROM facilities
                WHERE id = ?";

                $result = $db->query($sql, [$facilityId]);

                if (!$result) {
                    throw new Exception('Facility not found');
                }

                http_response_code(200);
                echo json_encode($result);
            } else {
                // Get all active facilities
                $sql = "SELECT
                    id, name, facility_type,
                    phone, fax, email,
                    address_line1, city, state, zip,
                    is_active, is_primary
                FROM facilities
                WHERE is_active = 1
                ORDER BY is_primary DESC, name";

                $facilities = $db->queryAll($sql);

                http_response_code(200);
                echo json_encode(['facilities' => $facilities]);
            }
            break;

        case 'POST':
            // Create new facility
            $name = $input['name'] ?? null;

            if (!$name) {
                throw new Exception('Facility name is required');
            }

            $sql = "INSERT INTO facilities (
                name, facility_type,
                phone, fax, email, website,
                address_line1, address_line2, city, state, zip,
                npi, tax_id, facility_npi, pos_code,
                is_active, is_primary
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $params = [
                $name,
                $input['facility_type'] ?? null,
                $input['phone'] ?? null,
                $input['fax'] ?? null,
                $input['email'] ?? null,
                $input['website'] ?? null,
                $input['address_line1'] ?? null,
                $input['address_line2'] ?? null,
                $input['city'] ?? null,
                $input['state'] ?? null,
                $input['zip'] ?? null,
                $input['npi'] ?? null,
                $input['tax_id'] ?? null,
                $input['facility_npi'] ?? null,
                $input['pos_code'] ?? null,
                $input['is_active'] ?? 1,
                $input['is_primary'] ?? 0
            ];

            $newId = $db->insert($sql, $params);

            http_response_code(201);
            echo json_encode(['success' => true, 'id' => $newId]);
            break;

        case 'PUT':
            // Update facility
            $facilityId = $input['id'] ?? null;

            if (!$facilityId) {
                throw new Exception('Facility ID is required');
            }

            $updateFields = [];
            $params = [];

            $fieldMap = [
                'name' => 'name',
                'facility_type' => 'facility_type',
                'phone' => 'phone',
                'fax' => 'fax',
                'email' => 'email',
                'website' => 'website',
                'address_line1' => 'address_line1',
                'address_line2' => 'address_line2',
                'city' => 'city',
                'state' => 'state',
                'zip' => 'zip',
                'npi' => 'npi',
                'tax_id' => 'tax_id',
                'facility_npi' => 'facility_npi',
                'pos_code' => 'pos_code',
                'is_active' => 'is_active',
                'is_primary' => 'is_primary'
            ];

            foreach ($fieldMap as $inputKey => $dbField) {
                if (array_key_exists($inputKey, $input)) {
                    $updateFields[] = "$dbField = ?";
                    $params[] = $input[$inputKey];
                }
            }

            if (empty($updateFields)) {
                throw new Exception('No fields to update');
            }

            $params[] = $facilityId;
            $sql = "UPDATE facilities SET " . implode(', ', $updateFields) . " WHERE id = ?";

            $db->execute($sql, $params);

            http_response_code(200);
            echo json_encode(['success' => true]);
            break;

        case 'DELETE':
            // Soft delete (set inactive)
            $facilityId = $_GET['id'] ?? $input['id'] ?? null;

            if (!$facilityId) {
                throw new Exception('Facility ID is required');
            }

            $sql = "UPDATE facilities SET is_active = 0 WHERE id = ?";
            $db->execute($sql, [$facilityId]);

            http_response_code(200);
            echo json_encode(['success' => true]);
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            break;
    }

} catch (\Exception $e) {
    error_log("Facilities API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error', 'message' => $e->getMessage()]);
}
