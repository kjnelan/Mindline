<?php
/**
 * SanctumEMHR - Settings Lists API
 * Generic CRUD for configurable lists (statuses, reasons, rooms, etc.)
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

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    $session = SessionManager::getInstance();
    $session->start();

    if (!$session->isAuthenticated()) {
        http_response_code(401);
        echo json_encode(['error' => 'Not authenticated']);
        exit;
    }

    // Only admins can modify settings lists
    $currentUser = $session->get('user');
    $isAdmin = $currentUser && $currentUser['user_type'] === 'admin';

    $db = Database::getInstance();
    $method = $_SERVER['REQUEST_METHOD'];

    // Get list_id from query param or request body
    $listId = $_GET['list_id'] ?? null;
    $input = null;

    if ($method !== 'GET') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$listId && isset($input['list_id'])) {
            $listId = $input['list_id'];
        }
    }

    switch ($method) {
        case 'GET':
            // Get all items for a specific list
            if (!$listId) {
                http_response_code(400);
                echo json_encode(['error' => 'list_id parameter required']);
                exit;
            }

            $sql = "SELECT option_id, title, notes, is_active, is_default, sort_order
                    FROM settings_lists
                    WHERE list_id = ?
                    ORDER BY sort_order, title";

            $items = $db->queryAll($sql, [$listId]);

            // Transform to frontend-friendly format
            $result = array_map(function($item) {
                return [
                    'value' => $item['option_id'],
                    'label' => $item['title'],
                    'option_id' => $item['option_id'],
                    'title' => $item['title'],
                    'notes' => $item['notes'],
                    'is_active' => (int)$item['is_active'],
                    'is_default' => (int)$item['is_default'],
                    'sort_order' => (int)$item['sort_order']
                ];
            }, $items ?: []);

            echo json_encode([
                'success' => true,
                'list_id' => $listId,
                'items' => $result
            ]);
            break;

        case 'POST':
            // Create new list item
            if (!$isAdmin) {
                http_response_code(403);
                echo json_encode(['error' => 'Admin access required']);
                exit;
            }

            if (!$listId || !isset($input['option_id']) || !isset($input['title'])) {
                http_response_code(400);
                echo json_encode(['error' => 'list_id, option_id, and title are required']);
                exit;
            }

            // Check for duplicate
            $existing = $db->query(
                "SELECT id FROM settings_lists WHERE list_id = ? AND option_id = ?",
                [$listId, $input['option_id']]
            );

            if ($existing) {
                http_response_code(409);
                echo json_encode(['error' => 'An item with this ID already exists']);
                exit;
            }

            $sql = "INSERT INTO settings_lists (list_id, option_id, title, notes, is_active, is_default, sort_order)
                    VALUES (?, ?, ?, ?, ?, ?, ?)";

            $result = $db->insert($sql, [
                $listId,
                $input['option_id'],
                $input['title'],
                $input['notes'] ?? null,
                isset($input['is_active']) ? (int)$input['is_active'] : 1,
                isset($input['is_default']) ? (int)$input['is_default'] : 0,
                isset($input['sort_order']) ? (int)$input['sort_order'] : 0
            ]);

            http_response_code(201);
            echo json_encode([
                'success' => true,
                'message' => 'Item created successfully',
                'id' => $result
            ]);
            break;

        case 'PUT':
            // Update existing list item
            if (!$isAdmin) {
                http_response_code(403);
                echo json_encode(['error' => 'Admin access required']);
                exit;
            }

            if (!$listId || !isset($input['option_id'])) {
                http_response_code(400);
                echo json_encode(['error' => 'list_id and option_id are required']);
                exit;
            }

            $updates = [];
            $params = [];

            if (isset($input['title'])) {
                $updates[] = 'title = ?';
                $params[] = $input['title'];
            }
            if (array_key_exists('notes', $input)) {
                $updates[] = 'notes = ?';
                $params[] = $input['notes'];
            }
            if (isset($input['is_active'])) {
                $updates[] = 'is_active = ?';
                $params[] = (int)$input['is_active'];
            }
            if (isset($input['is_default'])) {
                $updates[] = 'is_default = ?';
                $params[] = (int)$input['is_default'];
            }
            if (isset($input['sort_order'])) {
                $updates[] = 'sort_order = ?';
                $params[] = (int)$input['sort_order'];
            }

            if (empty($updates)) {
                http_response_code(400);
                echo json_encode(['error' => 'No fields to update']);
                exit;
            }

            $params[] = $listId;
            $params[] = $input['option_id'];

            $sql = "UPDATE settings_lists SET " . implode(', ', $updates) . " WHERE list_id = ? AND option_id = ?";
            $db->execute($sql, $params);

            echo json_encode([
                'success' => true,
                'message' => 'Item updated successfully'
            ]);
            break;

        case 'DELETE':
            // Delete list item
            if (!$isAdmin) {
                http_response_code(403);
                echo json_encode(['error' => 'Admin access required']);
                exit;
            }

            $optionId = $_GET['option_id'] ?? null;

            if (!$listId || !$optionId) {
                http_response_code(400);
                echo json_encode(['error' => 'list_id and option_id parameters required']);
                exit;
            }

            $sql = "DELETE FROM settings_lists WHERE list_id = ? AND option_id = ?";
            $db->execute($sql, [$listId, $optionId]);

            echo json_encode([
                'success' => true,
                'message' => 'Item deleted successfully'
            ]);
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            break;
    }

} catch (Exception $e) {
    error_log("Settings lists API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
