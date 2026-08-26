<?php
// hospital/api/departments.php
session_start();
require_once '../includes/db.php';

header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'];
$role = $_SESSION['role'] ?? null;

function sendResponse($success, $message, $data = null, $code = 200) {
    http_response_code($code);
    echo json_encode(['success' => $success, 'message' => $message, 'data' => $data]);
    exit;
}

if ($method === 'GET') {
    try {
        $stmt = $pdo->query("SELECT * FROM departments ORDER BY name ASC");
        $departments = $stmt->fetchAll();
        sendResponse(true, 'Departments retrieved successfully.', $departments);
    } catch (PDOException $e) {
        sendResponse(false, 'Failed to retrieve departments.', ['error' => $e->getMessage()], 500);
    }
} else {
    // Admin only check for writing operations
    if ($role !== 'admin') {
        sendResponse(false, 'Unauthorized. Administrative privilege required.', null, 403);
    }

    $input = json_decode(file_get_contents('php://input'), true);

    if ($method === 'POST') {
        $name = trim($input['name'] ?? '');
        $description = trim($input['description'] ?? '');
        $icon = trim($input['icon'] ?? 'general-medicine');

        if (empty($name)) {
            sendResponse(false, 'Department name is required.', null, 400);
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO departments (name, description, icon) VALUES (?, ?, ?)");
            $stmt->execute([$name, $description, $icon]);
            sendResponse(true, 'Department created successfully.', ['id' => $pdo->lastInsertId()]);
        } catch (PDOException $e) {
            sendResponse(false, 'Failed to create department.', ['error' => $e->getMessage()], 500);
        }
    } elseif ($method === 'PUT') {
        $id = intval($input['id'] ?? 0);
        $name = trim($input['name'] ?? '');
        $description = trim($input['description'] ?? '');
        $icon = trim($input['icon'] ?? 'general-medicine');

        if ($id <= 0 || empty($name)) {
            sendResponse(false, 'Department ID and Name are required.', null, 400);
        }

        try {
            $stmt = $pdo->prepare("UPDATE departments SET name = ?, description = ?, icon = ? WHERE id = ?");
            $stmt->execute([$name, $description, $icon, $id]);
            sendResponse(true, 'Department updated successfully.');
        } catch (PDOException $e) {
            sendResponse(false, 'Failed to update department.', ['error' => $e->getMessage()], 500);
        }
    } elseif ($method === 'DELETE') {
        $id = intval($_GET['id'] ?? 0);

        if ($id <= 0) {
            sendResponse(false, 'Invalid Department ID.', null, 400);
        }

        try {
            $stmt = $pdo->prepare("DELETE FROM departments WHERE id = ?");
            $stmt->execute([$id]);
            sendResponse(true, 'Department deleted successfully.');
        } catch (PDOException $e) {
            sendResponse(false, 'Failed to delete department.', ['error' => $e->getMessage()], 500);
        }
    } else {
        sendResponse(false, 'Method not allowed.', null, 405);
    }
}
?>
