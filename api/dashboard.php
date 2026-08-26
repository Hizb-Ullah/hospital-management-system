<?php
// hospital/api/dashboard.php
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

if ($method !== 'GET') {
    sendResponse(false, 'Method not allowed.', null, 405);
}

try {
    if ($role === 'patient') {
        $patientId = $_SESSION['patient_id'] ?? 0;

        // 1. Get unpaid bills sum
        $stmt = $pdo->prepare("SELECT SUM(amount) as unpaid_sum FROM billing WHERE patient_id = ? AND status != 'paid'");
        $stmt->execute([$patientId]);
        $unpaid = $stmt->fetch();
        $unpaidSum = floatval($unpaid['unpaid_sum'] ?? 0.00);

        // 2. Count appointments
        $stmt = $pdo->prepare("SELECT COUNT(*) as appt_count FROM appointments WHERE patient_id = ?");
        $stmt->execute([$patientId]);
        $apptCount = intval($stmt->fetch()['appt_count'] ?? 0);

        // 3. Count medical records
        $stmt = $pdo->prepare("SELECT COUNT(*) as record_count FROM medical_records WHERE patient_id = ?");
        $stmt->execute([$patientId]);
        $recordCount = intval($stmt->fetch()['record_count'] ?? 0);

        // 4. Retrieve 5 recent appointments
        $stmt = $pdo->prepare("
            SELECT a.*, u_doc.first_name as doctor_first_name, u_doc.last_name as doctor_last_name, doc.specialization
            FROM appointments a
            JOIN doctors doc ON a.doctor_id = doc.id
            JOIN users u_doc ON doc.user_id = u_doc.id
            WHERE a.patient_id = ?
            ORDER BY a.appointment_date DESC, a.appointment_time DESC
            LIMIT 5
        ");
        $stmt->execute([$patientId]);
        $recentAppts = $stmt->fetchAll();

        // 5. Retrieve 3 recent medical records
        $stmt = $pdo->prepare("
            SELECT mr.*, u_doc.first_name as doctor_first_name, u_doc.last_name as doctor_last_name, doc.specialization
            FROM medical_records mr
            JOIN doctors doc ON mr.doctor_id = doc.id
            JOIN users u_doc ON doc.user_id = u_doc.id
            WHERE mr.patient_id = ?
            ORDER BY mr.visit_date DESC, mr.id DESC
            LIMIT 3
        ");
        $stmt->execute([$patientId]);
        $recentRecords = $stmt->fetchAll();

        sendResponse(true, 'Patient dashboard data loaded.', [
            'stats' => [
                'unpaid_bills' => $unpaidSum,
                'total_appointments' => $apptCount,
                'medical_records' => $recordCount
            ],
            'recent_appointments' => $recentAppts,
            'recent_medical_records' => $recentRecords
        ]);

    } elseif ($role === 'doctor') {
        $doctorId = $_SESSION['doctor_id'] ?? 0;
        $today = date('Y-m-d');

        // 1. Appointments Today
        $stmt = $pdo->prepare("SELECT COUNT(*) as today_count FROM appointments WHERE doctor_id = ? AND appointment_date = ?");
        $stmt->execute([$doctorId, $today]);
        $todayCount = intval($stmt->fetch()['today_count'] ?? 0);

        // 2. Pending Appointments
        $stmt = $pdo->prepare("SELECT COUNT(*) as pending_count FROM appointments WHERE doctor_id = ? AND status = 'pending'");
        $stmt->execute([$doctorId]);
        $pendingCount = intval($stmt->fetch()['pending_count'] ?? 0);

        // 3. Treated patients
        $stmt = $pdo->prepare("SELECT COUNT(DISTINCT patient_id) as patient_count FROM medical_records WHERE doctor_id = ?");
        $stmt->execute([$doctorId]);
        $patientCount = intval($stmt->fetch()['patient_count'] ?? 0);

        // 4. Recent Appointments (today or future)
        $stmt = $pdo->prepare("
            SELECT a.*, u_pat.first_name as patient_first_name, u_pat.last_name as patient_last_name, pat.phone, pat.blood_group
            FROM appointments a
            JOIN patients pat ON a.patient_id = pat.id
            JOIN users u_pat ON pat.user_id = u_pat.id
            WHERE a.doctor_id = ?
            ORDER BY a.appointment_date DESC, a.appointment_time DESC
            LIMIT 5
        ");
        $stmt->execute([$doctorId]);
        $recentAppts = $stmt->fetchAll();

        sendResponse(true, 'Doctor dashboard data loaded.', [
            'stats' => [
                'appointments_today' => $todayCount,
                'pending_appointments' => $pendingCount,
                'treated_patients' => $patientCount
            ],
            'recent_appointments' => $recentAppts
        ]);

    } elseif ($role === 'admin') {
        // 1. Total patients
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM patients");
        $patientsCount = intval($stmt->fetch()['count'] ?? 0);

        // 2. Total doctors
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM doctors");
        $doctorsCount = intval($stmt->fetch()['count'] ?? 0);

        // 3. Total appointments
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM appointments");
        $appointmentsCount = intval($stmt->fetch()['count'] ?? 0);

        // 4. Total revenue (sum of paid invoices)
        $stmt = $pdo->query("SELECT SUM(amount) as total_rev FROM billing WHERE status = 'paid'");
        $rev = $stmt->fetch();
        $totalRevenue = floatval($rev['total_rev'] ?? 0.00);

        // 5. Recent 5 Appointments
        $stmt = $pdo->query("
            SELECT a.*, 
                   u_doc.first_name as doctor_first_name, u_doc.last_name as doctor_last_name,
                   u_pat.first_name as patient_first_name, u_pat.last_name as patient_last_name
            FROM appointments a
            JOIN doctors doc ON a.doctor_id = doc.id
            JOIN users u_doc ON doc.user_id = u_doc.id
            JOIN patients pat ON a.patient_id = pat.id
            JOIN users u_pat ON pat.user_id = u_pat.id
            ORDER BY a.created_at DESC
            LIMIT 5
        ");
        $recentAppts = $stmt->fetchAll();

        // 6. Recent 5 Payments
        $stmt = $pdo->query("
            SELECT p.*, u_pat.first_name, u_pat.last_name
            FROM payments p
            JOIN billing b ON p.billing_id = b.id
            JOIN patients pat ON b.patient_id = pat.id
            JOIN users u_pat ON pat.user_id = u_pat.id
            ORDER BY p.payment_date DESC
            LIMIT 5
        ");
        $recentPayments = $stmt->fetchAll();

        sendResponse(true, 'Admin dashboard data loaded.', [
            'stats' => [
                'total_patients' => $patientsCount,
                'total_doctors' => $doctorsCount,
                'total_appointments' => $appointmentsCount,
                'total_revenue' => $totalRevenue
            ],
            'recent_appointments' => $recentAppts,
            'recent_payments' => $recentPayments
        ]);

    } elseif ($role === 'nurse') {
        // Nurse summary stats
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM appointments WHERE status = 'pending'");
        $pendingAppts = intval($stmt->fetch()['count'] ?? 0);

        $stmt = $pdo->query("SELECT COUNT(*) as count FROM patients");
        $patientsCount = intval($stmt->fetch()['count'] ?? 0);

        sendResponse(true, 'Nurse dashboard data loaded.', [
            'stats' => [
                'pending_appointments' => $pendingAppts,
                'total_patients' => $patientsCount
            ]
        ]);
    } else {
        sendResponse(false, 'Role dashboard metrics not defined.', null, 403);
    }
} catch (PDOException $e) {
    sendResponse(false, 'Dashboard aggregation query failed.', ['error' => $e->getMessage()], 500);
}
?>
