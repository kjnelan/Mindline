<?php
/**
 * User Management API (MIGRATED TO MINDLINE)
 * Full CRUD operations for system users/providers
 */

require_once(__DIR__ . '/../init.php');

use Custom\Lib\Database\Database;
use Custom\Lib\Session\SessionManager;

// Set JSON header
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    // Initialize session and check authentication
    $session = SessionManager::getInstance();
    $session->start();

    if (!$session->isAuthenticated()) {
        error_log("Users API: Not authenticated");
        http_response_code(401);
        echo json_encode(['error' => 'Not authenticated']);
        exit;
    }

    // Initialize database
    $db = Database::getInstance();

    $method = $_SERVER['REQUEST_METHOD'];
    $input = json_decode(file_get_contents('php://input'), true);

    switch ($method) {
        case 'GET':
            $action = $_GET['action'] ?? 'list';

            if ($action === 'list') {
                // List all users
                $search = $_GET['search'] ?? '';
                $status = $_GET['status'] ?? ''; // active, inactive, all

                $sql = "SELECT
                    u.id,
                    u.username,
                    u.first_name AS fname,
                    u.middle_name AS mname,
                    u.last_name AS lname,
                    u.suffix,
                    u.title,
                    u.npi,
                    u.federal_tax_id AS federaltaxid,
                    u.taxonomy,
                    u.state_license_number,
                    u.facility,
                    u.facility_id,
                    u.specialty,
                    u.is_provider AS authorized,
                    u.is_admin AS calendar,
                    u.portal_user,
                    u.is_active AS active,
                    u.email,
                    u.phone,
                    u.phone_cell AS phonecell,
                    u.supervisor_id,
                    u.notes,
                    CONCAT(sup.first_name, ' ', sup.last_name) AS supervisor_fname,
                    sup.last_name AS supervisor_lname
                FROM users u
                LEFT JOIN users sup ON u.supervisor_id = sup.id
                WHERE 1=1";

                $params = [];

                // Apply search filter
                if ($search) {
                    $sql .= " AND (u.first_name LIKE ? OR u.last_name LIKE ? OR u.username LIKE ? OR u.email LIKE ?)";
                    $searchParam = "%$search%";
                    $params[] = $searchParam;
                    $params[] = $searchParam;
                    $params[] = $searchParam;
                    $params[] = $searchParam;
                }

                // Apply status filter
                if ($status === 'active') {
                    $sql .= " AND u.is_active = 1";
                } elseif ($status === 'inactive') {
                    $sql .= " AND u.is_active = 0";
                }

                $sql .= " ORDER BY u.last_name, u.first_name";

                $users = $db->queryAll($sql, $params);

                http_response_code(200);
                echo json_encode(['users' => $users]);

            } elseif ($action === 'get') {
                // Get single user details
                $userId = $_GET['id'] ?? null;

                if (!$userId) {
                    http_response_code(400);
                    echo json_encode(['error' => 'User ID required']);
                    exit;
                }

                $sql = "SELECT
                    u.id,
                    u.username,
                    u.first_name AS fname,
                    u.middle_name AS mname,
                    u.last_name AS lname,
                    u.suffix,
                    u.title,
                    u.email,
                    u.phone,
                    u.phone_cell AS phonecell,
                    u.npi,
                    u.federal_tax_id AS federaltaxid,
                    u.taxonomy,
                    u.state_license_number,
                    u.supervisor_id,
                    u.facility_id,
                    u.is_provider AS authorized,
                    u.is_supervisor,
                    u.is_active AS active,
                    u.is_admin AS calendar,
                    u.portal_user,
                    u.see_auth,
                    u.notes,
                    CONCAT(sup.first_name, ' ', sup.last_name) AS supervisor_fname,
                    sup.last_name AS supervisor_lname
                FROM users u
                LEFT JOIN users sup ON u.supervisor_id = sup.id
                WHERE u.id = ?";

                $user = $db->query($sql, [$userId]);

                if (!$user) {
                    http_response_code(404);
                    echo json_encode(['error' => 'User not found']);
                    exit;
                }

                http_response_code(200);
                echo json_encode(['user' => $user]);

            } elseif ($action === 'user_supervisors') {
                // Get supervisors for a specific user from junction table
                $userId = $_GET['id'] ?? null;

                if (!$userId) {
                    http_response_code(400);
                    echo json_encode(['error' => 'User ID required']);
                    exit;
                }

                $sql = "SELECT supervisor_id
                        FROM user_supervisors
                        WHERE user_id = ?";

                $rows = $db->queryAll($sql, [$userId]);
                $supervisor_ids = array_column($rows, 'supervisor_id');

                http_response_code(200);
                echo json_encode(['supervisor_ids' => $supervisor_ids]);

            } elseif ($action === 'supervisors') {
                // Get list of potential supervisors
                $sql = "SELECT id, first_name AS fname, last_name AS lname, title
                        FROM users
                        WHERE is_active = 1 AND is_supervisor = 1
                        ORDER BY last_name, first_name";

                $supervisors = $db->queryAll($sql);

                http_response_code(200);
                echo json_encode(['supervisors' => $supervisors]);

            } elseif ($action === 'facilities') {
                // Get list of facilities
                $sql = "SELECT id, name, street, city, state, postal_code
                        FROM facility
                        ORDER BY name";

                $facilities = $db->queryAll($sql);

                http_response_code(200);
                echo json_encode(['facilities' => $facilities]);
            }
            break;

        case 'POST':
            // Create new user
            $username = $input['username'] ?? null;
            $password = $input['password'] ?? null;
            $fname = $input['fname'] ?? null;
            $lname = $input['lname'] ?? null;

            if (!$username || !$password || !$fname || !$lname) {
                http_response_code(400);
                echo json_encode(['error' => 'Username, password, first name, and last name are required']);
                exit;
            }

            // Check if username exists
            $checkSql = "SELECT id FROM users WHERE username = ?";
            $existing = $db->query($checkSql, [$username]);

            if ($existing) {
                http_response_code(409);
                echo json_encode(['error' => 'Username already exists']);
                exit;
            }

            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Insert user
            $insertSql = "INSERT INTO users (
                username,
                password,
                first_name,
                middle_name,
                last_name,
                suffix,
                title,
                email,
                phone,
                phone_cell,
                npi,
                federal_tax_id,
                taxonomy,
                state_license_number,
                supervisor_id,
                facility_id,
                is_provider,
                is_supervisor,
                is_active,
                is_admin,
                portal_user,
                see_auth,
                notes,
                created_at,
                updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";

            $params = [
                $username,
                $hashedPassword,
                $fname,
                $input['mname'] ?? '',
                $lname,
                $input['suffix'] ?? '',
                $input['title'] ?? '',
                $input['email'] ?? '',
                $input['phone'] ?? '',
                $input['phonecell'] ?? '',
                $input['npi'] ?? '',
                $input['federaltaxid'] ?? '',
                $input['taxonomy'] ?? '207Q00000X',
                $input['state_license_number'] ?? '',
                $input['supervisor_id'] ?? null,
                $input['facility_id'] ?? null,
                $input['authorized'] ?? 0,
                $input['is_supervisor'] ?? 0,
                $input['active'] ?? 1,
                $input['calendar'] ?? 0,
                $input['portal_user'] ?? 0,
                $input['see_auth'] ?? 1,
                $input['notes'] ?? ''
            ];

            $userId = $db->insert($insertSql, $params);

            // Handle supervisor assignments
            if (isset($input['supervisor_ids']) && is_array($input['supervisor_ids'])) {
                foreach ($input['supervisor_ids'] as $supervisorId) {
                    if ($supervisorId > 0) {
                        $supSql = "INSERT INTO user_supervisors (user_id, supervisor_id) VALUES (?, ?)";
                        $db->execute($supSql, [$userId, $supervisorId]);
                    }
                }
            }

            error_log("User created - ID: $userId, Username: $username");

            http_response_code(201);
            echo json_encode([
                'success' => true,
                'user_id' => $userId,
                'message' => 'User created successfully'
            ]);
            break;

        case 'PUT':
            // Update user
            $userId = $input['id'] ?? null;

            if (!$userId) {
                http_response_code(400);
                echo json_encode(['error' => 'User ID is required']);
                exit;
            }

            // Check if user exists
            $checkSql = "SELECT id FROM users WHERE id = ?";
            $existing = $db->query($checkSql, [$userId]);

            if (!$existing) {
                http_response_code(404);
                echo json_encode(['error' => 'User not found']);
                exit;
            }

            // Build update query
            $updateSql = "UPDATE users SET
                first_name = ?,
                middle_name = ?,
                last_name = ?,
                suffix = ?,
                title = ?,
                email = ?,
                phone = ?,
                phone_cell = ?,
                npi = ?,
                federal_tax_id = ?,
                taxonomy = ?,
                state_license_number = ?,
                supervisor_id = ?,
                facility_id = ?,
                is_provider = ?,
                is_supervisor = ?,
                is_active = ?,
                is_admin = ?,
                portal_user = ?,
                see_auth = ?,
                notes = ?,
                updated_at = NOW()
            WHERE id = ?";

            $params = [
                $input['fname'],
                $input['mname'] ?? '',
                $input['lname'],
                $input['suffix'] ?? '',
                $input['title'] ?? '',
                $input['email'] ?? '',
                $input['phone'] ?? '',
                $input['phonecell'] ?? '',
                $input['npi'] ?? '',
                $input['federaltaxid'] ?? '',
                $input['taxonomy'] ?? '207Q00000X',
                $input['state_license_number'] ?? '',
                $input['supervisor_id'] ?? null,
                $input['facility_id'] ?? null,
                $input['authorized'] ?? 0,
                $input['is_supervisor'] ?? 0,
                $input['active'] ?? 1,
                $input['calendar'] ?? 0,
                $input['portal_user'] ?? 0,
                $input['see_auth'] ?? 1,
                $input['notes'] ?? '',
                $userId
            ];

            $db->execute($updateSql, $params);

            // Update password if provided
            if (!empty($input['password'])) {
                $hashedPassword = password_hash($input['password'], PASSWORD_DEFAULT);
                $pwdSql = "UPDATE users SET password = ? WHERE id = ?";
                $db->execute($pwdSql, [$hashedPassword, $userId]);
            }

            // Update supervisor assignments
            if (isset($input['supervisor_ids']) && is_array($input['supervisor_ids'])) {
                // Remove existing
                $deleteSql = "DELETE FROM user_supervisors WHERE user_id = ?";
                $db->execute($deleteSql, [$userId]);

                // Add new
                foreach ($input['supervisor_ids'] as $supervisorId) {
                    if ($supervisorId > 0) {
                        $supSql = "INSERT INTO user_supervisors (user_id, supervisor_id) VALUES (?, ?)";
                        $db->execute($supSql, [$userId, $supervisorId]);
                    }
                }
            }

            error_log("User updated - ID: $userId");

            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'User updated successfully'
            ]);
            break;

        case 'DELETE':
            // Deactivate user (soft delete)
            $userId = $_GET['id'] ?? null;

            if (!$userId) {
                http_response_code(400);
                echo json_encode(['error' => 'User ID is required']);
                exit;
            }

            $deactivateSql = "UPDATE users SET is_active = 0 WHERE id = ?";
            $db->execute($deactivateSql, [$userId]);

            error_log("User deactivated - ID: $userId");

            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'User deactivated successfully'
            ]);
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
    }

} catch (Exception $e) {
    error_log("Error in users API: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode([
        'error' => 'Server error',
        'message' => $e->getMessage()
    ]);
}
