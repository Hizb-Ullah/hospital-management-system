<?php
// hospital/api/payments.php
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

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $billingId = intval($input['billing_id'] ?? 0);
    $amount = floatval($input['amount'] ?? 0.00);
    $method_pay = trim($input['payment_method'] ?? 'card');

    $allowedMethods = ['cash', 'card', 'bank_transfer'];
    if ($billingId <= 0 || $amount <= 0 || !in_array($method_pay, $allowedMethods)) {
        sendResponse(false, 'Invalid input parameters for payment.', null, 400);
    }

    try {
        // Start Transaction
        $pdo->beginTransaction();

        // 1. Get Bill Details
        $stmt = $pdo->prepare("SELECT * FROM billing WHERE id = ?");
        $stmt->execute([$billingId]);
        $bill = $stmt->fetch();

        if (!$bill) {
            sendResponse(false, 'Billing record not found.', null, 404);
        }

        if ($bill['status'] === 'paid') {
            sendResponse(false, 'This bill has already been paid in full.', null, 400);
        }

        // Check if patient is paying their own bill
        if ($role === 'patient') {
            $patientId = $_SESSION['patient_id'] ?? 0;
            if ($bill['patient_id'] !== $patientId) {
                sendResponse(false, 'Unauthorized. You cannot pay someone else\'s bill.', null, 403);
            }
        }

        // 2. Generate a Unique Transaction ID
        $txnId = 'TXN' . strtoupper(uniqid()) . rand(10, 99);

        // 3. Record Payment
        $stmt = $pdo->prepare("
            INSERT INTO payments (billing_id, amount, payment_method, transaction_id)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$billingId, $amount, $method_pay, $txnId]);

        // 4. Update Bill Status
        // Let's see total paid so far
        $stmt = $pdo->prepare("SELECT SUM(amount) as total_paid FROM payments WHERE billing_id = ?");
        $stmt->execute([$billingId]);
        $payments = $stmt->fetch();
        $totalPaid = floatval($payments['total_paid'] ?? 0.00);

        $newStatus = 'partially_paid';
        if ($totalPaid >= $bill['amount']) {
            $newStatus = 'paid';
        }

        $stmt = $pdo->prepare("UPDATE billing SET status = ? WHERE id = ?");
        $stmt->execute([$newStatus, $billingId]);

        // Commit Transaction
        $pdo->commit();

        sendResponse(true, 'Payment processed successfully.', [
            'transaction_id' => $txnId,
            'amount_paid' => $amount,
            'bill_status' => $newStatus
        ]);

    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        sendResponse(false, 'Failed to process payment.', ['error' => $e->getMessage()], 500);
    }
} elseif ($method === 'GET') {
    try {
        $params = [];
        $query = "
            SELECT p.*, b.patient_id, b.amount as bill_amount,
                   u_pat.first_name, u_pat.last_name
            FROM payments p
            JOIN billing b ON p.billing_id = b.id
            JOIN patients pat ON b.patient_id = pat.id
            JOIN users u_pat ON pat.user_id = u_pat.id
        ";

        if ($role === 'patient') {
            $patientId = $_SESSION['patient_id'] ?? 0;
            $query .= " WHERE b.patient_id = ?";
            $params[] = $patientId;
        }

        $query .= " ORDER BY p.payment_date DESC";
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $paymentsList = $stmt->fetchAll();

        sendResponse(true, 'Payments retrieved successfully.', $paymentsList);
    } catch (PDOException $e) {
        sendResponse(false, 'Failed to retrieve payments.', ['error' => $e->getMessage()], 500);
    }
} else {
    sendResponse(false, 'Method not allowed.', null, 405);
}
?>
