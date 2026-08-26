<?php
// hospital/api/patients.php
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
        if ($role === 'patient') {
            // Get patient profile
            $patientId = $_SESSION['patient_id'] ?? 0;
            $stmt = $pdo->prepare("
                SELECT p.*, u.first_name, u.last_name, u.email, u.phone, u.gender, u.dob
                FROM patients p
                JOIN users u ON p.user_id = u.id
                WHERE p.id = ?
            ");
            $stmt->execute([$patientId]);
            $profile = $stmt->fetch();
            if (!$profile) {
                sendResponse(false, 'Patient profile not found.', null, 404);
            }
            sendResponse(true, 'Patient profile retrieved.', $profile);
        } else {
            // Doctors, Nurses, and Admins can list all patients
            $stmt = $pdo->query("
                SELECT p.*, u.first_name, u.last_name, u.email, u.phone, u.gender, u.dob
                FROM patients p
                JOIN users u ON p.user_id = u.id
                ORDER BY u.first_name ASC
            ");
            $patients = $stmt->fetchAll();
            sendResponse(true, 'Patients retrieved successfully.', $patients);
        }
    } catch (PDOException $e) {
        sendResponse(false, 'Failed to fetch patient data.', ['error' => $e->getMessage()], 500);
    }
} elseif ($method === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true);

    try {
        if ($role === 'patient') {
            $patientId = $_SESSION['patient_id'] ?? 0;
            
            $first_name = trim($input['first_name'] ?? '');
            $last_name = trim($input['last_name'] ?? '');
            $phone = trim($input['phone'] ?? '');
            $dob = trim($input['dob'] ?? '');
            $gender = trim($input['gender'] ?? '');
            $blood_group = trim($input['blood_group'] ?? '');
            $address = trim($input['address'] ?? '');
            $emergency_contact = trim($input['emergency_contact'] ?? '');

            if (empty($first_name) || empty($last_name)) {
                sendResponse(false, 'First and Last name are required.', null, 400);
            }

            $pdo->beginTransaction();

            // Update user details
            $stmt = $pdo->prepare("
                UPDATE users 
                SET first_name = ?, last_name = ?, phone = ?, dob = ?, gender = ?
                WHERE id = ?
            ");
            $stmt->execute([$first_name, $last_name, $phone, $dob, $gender, $userId]);

            // Update patient details
            $stmt = $pdo->prepare("
                UPDATE patients 
                SET blood_group = ?, address = ?, emergency_contact = ?
                WHERE id = ?
            ");
            $stmt->execute([$blood_group, $address, $emergency_contact, $patientId]);

            $pdo->commit();

            // Update session data
            $_SESSION['first_name'] = $first_name;
            $_SESSION['last_name'] = $last_name;

            sendResponse(true, 'Profile updated successfully.');
        } else {
            sendResponse(false, 'Only patients can update their patient profiles.', null, 403);
        }
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        sendResponse(false, 'Failed to update profile.', ['error' => $e->getMessage()], 500);
    }
} else {
    sendResponse(false, 'Method not allowed.', null, 405);
}
?>
