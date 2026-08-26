<?php
// hospital/api/medical_records.php
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
            SELECT mr.*, 
                   u_doc.first_name as doctor_first_name, u_doc.last_name as doctor_last_name,
                   doc.specialization,
                   u_pat.first_name as patient_first_name, u_pat.last_name as patient_last_name,
                   pat.blood_group
            FROM medical_records mr
            JOIN doctors doc ON mr.doctor_id = doc.id
            JOIN users u_doc ON doc.user_id = u_doc.id
            JOIN patients pat ON mr.patient_id = pat.id
            JOIN users u_pat ON pat.user_id = u_pat.id
        ";

        if ($role === 'patient') {
            $patientId = $_SESSION['patient_id'] ?? 0;
            $query .= " WHERE mr.patient_id = ?";
            $params[] = $patientId;
        } else {
            // For Doctors, Nurses, and Admins: Optional filter by patient_id
            $filter_patient_id = isset($_GET['patient_id']) ? intval($_GET['patient_id']) : 0;
            if ($filter_patient_id > 0) {
                $query .= " WHERE mr.patient_id = ?";
                $params[] = $filter_patient_id;
            }
        }

        $query .= " ORDER BY mr.visit_date DESC, mr.id DESC";
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $records = $stmt->fetchAll();

        sendResponse(true, 'Medical records retrieved successfully.', $records);
    } catch (PDOException $e) {
        sendResponse(false, 'Failed to retrieve medical records.', ['error' => $e->getMessage()], 500);
    }
} elseif ($method === 'POST') {
    // Only Doctors and Admins can create medical records
    if ($role !== 'doctor' && $role !== 'admin') {
        sendResponse(false, 'Unauthorized. Only doctors and admins can write medical records.', null, 403);
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $patientId = intval($input['patient_id'] ?? 0);
    $diagnosis = trim($input['diagnosis'] ?? '');
    $treatment = trim($input['treatment'] ?? '');
    $prescription = trim($input['prescription'] ?? '');
    $visitDate = trim($input['visit_date'] ?? date('Y-m-d'));
    
    // Get doctor_id of logged-in doctor, or if admin is posting, we expect doctor_id in payload
    $doctorId = 0;
    if ($role === 'doctor') {
        $doctorId = $_SESSION['doctor_id'] ?? 0;
    } else {
        $doctorId = intval($input['doctor_id'] ?? 0);
    }

    if ($patientId <= 0 || $doctorId <= 0 || empty($diagnosis) || empty($treatment) || empty($prescription)) {
        sendResponse(false, 'Please fill in all medical record fields.', null, 400);
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO medical_records (patient_id, doctor_id, diagnosis, treatment, prescription, visit_date)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$patientId, $doctorId, $diagnosis, $treatment, $prescription, $visitDate]);
        sendResponse(true, 'Medical record successfully added.', ['id' => $pdo->lastInsertId()]);
    } catch (PDOException $e) {
        sendResponse(false, 'Failed to add medical record.', ['error' => $e->getMessage()], 500);
    }
} else {
    sendResponse(false, 'Method not allowed.', null, 405);
}
?>
