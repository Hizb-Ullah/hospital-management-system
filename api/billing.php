<?php
// hospital/api/billing.php
session_start();
require_once '../includes/db.php';

header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'];
$userId = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? null;

function sendResponse($success, $message, $data = null, $code = 200) {
    http_response_code($code);
    echo json_encode(['success' => $success, 'message' => $message, 'data' => $data]);
    exit;
}

if (!$userId) {
    sendResponse(false, 'Unauthorized. Please log in.', null, 401);
}

if ($method === 'GET') {
    try {
        $params = [];
        $query = "
            SELECT b.*, 
                   u_pat.first_name as patient_first_name, u_pat.last_name as patient_last_name,
                   pat.phone as patient_phone,
                   a.appointment_date, a.appointment_time,
                   u_doc.first_name as doctor_first_name, u_doc.last_name as doctor_last_name
            FROM billing b
            JOIN patients pat ON b.patient_id = pat.id
            JOIN users u_pat ON pat.user_id = u_pat.id
            LEFT JOIN appointments a ON b.appointment_id = a.id
            LEFT JOIN doctors doc ON a.doctor_id = doc.id
            LEFT JOIN users u_doc ON doc.user_id = u_doc.id
        ";

        if ($role === 'patient') {
            $patientId = $_SESSION['patient_id'] ?? 0;
            $query .= " WHERE b.patient_id = ?";
            $params[] = $patientId;
        }

        $query .= " ORDER BY b.created_at DESC";
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $bills = $stmt->fetchAll();

        sendResponse(true, 'Billing invoices retrieved successfully.', $bills);
    } catch (PDOException $e) {
        sendResponse(false, 'Failed to retrieve bills.', ['error' => $e->getMessage()], 500);
    }
} elseif ($method === 'POST') {
    if ($role !== 'admin' && $role !== 'nurse') {
        sendResponse(false, 'Unauthorized. Only admins and nurses can generate custom invoices.', null, 403);
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $patientId = intval($input['patient_id'] ?? 0);
    $amount = floatval($input['amount'] ?? 0.00);
    $dueDate = trim($input['due_date'] ?? '');

    if ($patientId <= 0 || $amount <= 0 || empty($dueDate)) {
        sendResponse(false, 'Please provide valid Patient ID, Amount, and Due Date.', null, 400);
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO billing (patient_id, amount, status, due_date)
            VALUES (?, ?, 'unpaid', ?)
        ");
        $stmt->execute([$patientId, $amount, $dueDate]);
        sendResponse(true, 'Invoice generated successfully.', ['id' => $pdo->lastInsertId()]);
    } catch (PDOException $e) {
        sendResponse(false, 'Failed to generate invoice.', ['error' => $e->getMessage()], 500);
    }
} else {
    sendResponse(false, 'Method not allowed.', null, 405);
}
?>
