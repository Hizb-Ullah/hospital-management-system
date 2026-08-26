<?php
// hospital/api/appointments.php
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
            SELECT a.*, 
                   doc.specialization, doc.consultation_fee,
                   u_doc.first_name as doctor_first_name, u_doc.last_name as doctor_last_name,
                   u_pat.first_name as patient_first_name, u_pat.last_name as patient_last_name,
                   pat.blood_group, pat.phone as patient_phone, u_pat.phone as user_phone
            FROM appointments a
            JOIN doctors doc ON a.doctor_id = doc.id
            JOIN users u_doc ON doc.user_id = u_doc.id
            JOIN patients pat ON a.patient_id = pat.id
            JOIN users u_pat ON pat.user_id = u_pat.id
        ";

        if ($role === 'patient') {
            $patientId = $_SESSION['patient_id'] ?? 0;
            $query .= " WHERE a.patient_id = ?";
            $params[] = $patientId;
        } elseif ($role === 'doctor') {
            $doctorId = $_SESSION['doctor_id'] ?? 0;
            $query .= " WHERE a.doctor_id = ?";
            $params[] = $doctorId;
        } elseif ($role === 'nurse') {
            // Nurse sees all appointments
        } elseif ($role === 'admin') {
            // Admin sees all appointments
        } else {
            sendResponse(false, 'Unauthorized role access.', null, 403);
        }

        $query .= " ORDER BY a.appointment_date DESC, a.appointment_time DESC";
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $appointments = $stmt->fetchAll();

        sendResponse(true, 'Appointments retrieved successfully.', $appointments);
    } catch (PDOException $e) {
        sendResponse(false, 'Failed to retrieve appointments.', ['error' => $e->getMessage()], 500);
    }
} elseif ($method === 'POST') {
    if ($role !== 'patient') {
        sendResponse(false, 'Only patients can book appointments.', null, 403);
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $doctorId = intval($input['doctor_id'] ?? 0);
    $date = trim($input['appointment_date'] ?? '');
    $time = trim($input['appointment_time'] ?? '');
    $reason = trim($input['reason'] ?? '');
    $patientId = $_SESSION['patient_id'] ?? 0;

    if ($doctorId <= 0 || empty($date) || empty($time) || empty($reason) || $patientId <= 0) {
        sendResponse(false, 'Please fill in all details for booking.', null, 400);
    }

    try {
        // Start transaction
        $pdo->beginTransaction();

        // 1. Get Doctor's fee
        $stmt = $pdo->prepare("SELECT consultation_fee FROM doctors WHERE id = ?");
        $stmt->execute([$doctorId]);
        $doctor = $stmt->fetch();
        if (!$doctor) {
            sendResponse(false, 'Doctor not found.', null, 404);
        }
        $fee = $doctor['consultation_fee'];

        // 2. Insert Appointment
        $stmt = $pdo->prepare("
            INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, reason, status)
            VALUES (?, ?, ?, ?, ?, 'pending')
        ");
        $stmt->execute([$patientId, $doctorId, $date, $time, $reason]);
        $appointmentId = $pdo->lastInsertId();

        // 3. Generate Billing invoice (unpaid, due on appointment date)
        $stmt = $pdo->prepare("
            INSERT INTO billing (patient_id, appointment_id, amount, status, due_date)
            VALUES (?, ?, ?, 'unpaid', ?)
        ");
        $stmt->execute([$patientId, $appointmentId, $fee, $date]);

        // Commit transaction
        $pdo->commit();

        sendResponse(true, 'Appointment booked successfully. Invoice has been generated under your billing tab.', ['appointment_id' => $appointmentId]);
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        sendResponse(false, 'Failed to book appointment.', ['error' => $e->getMessage()], 500);
    }
} elseif ($method === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true);
    $appointmentId = intval($input['id'] ?? 0);
    $status = trim($input['status'] ?? '');

    $allowedStatuses = ['pending', 'approved', 'cancelled', 'completed'];
    if ($appointmentId <= 0 || !in_array($status, $allowedStatuses)) {
        sendResponse(false, 'Invalid appointment ID or status.', null, 400);
    }

    try {
        // Fetch existing appointment details
        $stmt = $pdo->prepare("SELECT * FROM appointments WHERE id = ?");
        $stmt->execute([$appointmentId]);
        $appointment = $stmt->fetch();

        if (!$appointment) {
            sendResponse(false, 'Appointment not found.', null, 404);
        }

        // Check authorization permissions
        if ($role === 'patient') {
            $patientId = $_SESSION['patient_id'] ?? 0;
            if ($appointment['patient_id'] !== $patientId) {
                sendResponse(false, 'Unauthorized to modify this appointment.', null, 403);
            }
            if ($status !== 'cancelled') {
                sendResponse(false, 'Patients can only cancel appointments.', null, 403);
            }
            
            // Permanent deletion from database
            $stmt = $pdo->prepare("DELETE FROM appointments WHERE id = ?");
            $stmt->execute([$appointmentId]);
            sendResponse(true, 'Appointment cancelled and permanently deleted from the database.');
        } elseif ($role === 'doctor') {
            $doctorId = $_SESSION['doctor_id'] ?? 0;
            if ($appointment['doctor_id'] !== $doctorId) {
                sendResponse(false, 'Unauthorized. This appointment is booked with another doctor.', null, 403);
            }
        } elseif ($role === 'nurse' || $role === 'admin') {
            // Nurse/Admin can update any appointment
        } else {
            sendResponse(false, 'Role not permitted.', null, 403);
        }

        // Update appointment status
        $stmt = $pdo->prepare("UPDATE appointments SET status = ? WHERE id = ?");
        $stmt->execute([$status, $appointmentId]);

        sendResponse(true, 'Appointment status updated successfully.');
    } catch (PDOException $e) {
        sendResponse(false, 'Failed to update appointment status.', ['error' => $e->getMessage()], 500);
    }
} else {
    sendResponse(false, 'Method not allowed.', null, 405);
}
?>
