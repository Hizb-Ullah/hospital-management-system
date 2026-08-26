<?php
// hospital/api/doctor_actions.php
session_start();
require_once '../includes/db.php';

header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'];
$userId = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? null;
$doctorId = $_SESSION['doctor_id'] ?? null;
$deptId = $_SESSION['department_id'] ?? null;

function sendResponse($success, $message, $data = null, $code = 200) {
    http_response_code($code);
    echo json_encode(['success' => $success, 'message' => $message, 'data' => $data]);
    exit;
}

if (!$userId || $role !== 'doctor' || !$doctorId) {
    sendResponse(false, 'Unauthorized. Access restricted to doctors.', null, 401);
}

// Function to calculate patient criteria (Age & Gender)
function getPatientCriteria($dob, $gender) {
    if (!$dob) return 'N/A';
    try {
        $birthDate = new DateTime($dob);
        $today = new DateTime();
        $age = $today->diff($birthDate)->y;
        
        if ($age < 18) {
            return ($gender === 'female') ? 'Girl' : 'Boy';
        } elseif ($age >= 18 && $age <= 60) {
            return ($gender === 'female') ? 'Young Woman' : 'Young Man';
        } else {
            return ($gender === 'female') ? 'Old Woman' : 'Old Man';
        }
    } catch (Exception $e) {
        return 'N/A';
    }
}

if ($method === 'GET') {
    $action = $_GET['action'] ?? '';
    
    if ($action === 'overview') {
        try {
            // 1. Next Patient
            $stmt = $pdo->prepare("
                SELECT a.id as appointment_id, a.appointment_date, a.appointment_time, a.reason,
                       u.first_name, u.last_name, u.dob, u.gender, u.phone,
                       p.id as patient_id, p.blood_group,
                       w.name as ward_name,
                       n_u.first_name as nurse_first_name, n_u.last_name as nurse_last_name
                FROM appointments a
                JOIN patients p ON a.patient_id = p.id
                JOIN users u ON p.user_id = u.id
                LEFT JOIN wards w ON p.ward_id = w.id
                LEFT JOIN nurses n ON p.nurse_id = n.id
                LEFT JOIN users n_u ON n.user_id = n_u.id
                WHERE a.doctor_id = ? AND a.status IN ('pending', 'approved') AND a.appointment_date >= CURDATE()
                ORDER BY a.appointment_date ASC, a.appointment_time ASC
                LIMIT 1
            ");
            $stmt->execute([$doctorId]);
            $nextPatient = $stmt->fetch();
            
            if ($nextPatient) {
                $nextPatient['criteria'] = getPatientCriteria($nextPatient['dob'], $nextPatient['gender']);
            }
            
            // 2. Waiting Patients Count
            $stmt = $pdo->prepare("
                SELECT COUNT(*) 
                FROM appointments 
                WHERE doctor_id = ? AND status IN ('pending', 'approved') AND appointment_date >= CURDATE()
            ");
            $stmt->execute([$doctorId]);
            $waitingCount = $stmt->fetchColumn();
            
            // 3. Appointments List for Doctor
            $stmt = $pdo->prepare("
                SELECT a.id as appointment_id, a.appointment_date, a.appointment_time, a.reason, a.status,
                       u.first_name, u.last_name, u.dob, u.gender, u.phone,
                       p.id as patient_id, p.blood_group,
                       w.name as ward_name,
                       n.id as assigned_nurse_id, n_u.first_name as nurse_first_name, n_u.last_name as nurse_last_name
                FROM appointments a
                JOIN patients p ON a.patient_id = p.id
                JOIN users u ON p.user_id = u.id
                LEFT JOIN wards w ON p.ward_id = w.id
                LEFT JOIN nurses n ON p.nurse_id = n.id
                LEFT JOIN users n_u ON n.user_id = n_u.id
                WHERE a.doctor_id = ?
                ORDER BY a.appointment_date DESC, a.appointment_time DESC
            ");
            $stmt->execute([$doctorId]);
            $appointments = $stmt->fetchAll();
            
            foreach ($appointments as &$appt) {
                $appt['criteria'] = getPatientCriteria($appt['dob'], $appt['gender']);
            }
            
            sendResponse(true, 'Doctor overview data retrieved.', [
                'next_patient' => $nextPatient,
                'waiting_count' => $waitingCount,
                'appointments' => $appointments
            ]);
            
        } catch (PDOException $e) {
            sendResponse(false, 'Failed to fetch doctor overview.', ['error' => $e->getMessage()], 500);
        }
        
    } elseif ($action === 'nurses') {
        try {
            // Get nurses in doctor's department
            $stmt = $pdo->prepare("
                SELECT n.id as nurse_id, n.shift, n.duty_time, n.ward_id,
                       u.first_name, u.last_name,
                       w.name as ward_name
                FROM nurses n
                JOIN users u ON n.user_id = u.id
                LEFT JOIN wards w ON n.ward_id = w.id
                WHERE n.department_id = ?
                ORDER BY u.first_name ASC
            ");
            $stmt->execute([$deptId]);
            $nurses = $stmt->fetchAll();
            sendResponse(true, 'Nurses list retrieved.', $nurses);
        } catch (PDOException $e) {
            sendResponse(false, 'Failed to fetch nurses list.', ['error' => $e->getMessage()], 500);
        }
        
    } elseif ($action === 'referral_options') {
        try {
            // Get all doctors grouped by department
            $stmt = $pdo->query("
                SELECT d.id as doctor_id, d.specialization,
                       u.first_name, u.last_name,
                       dept.name as department_name
                FROM doctors d
                JOIN users u ON d.user_id = u.id
                JOIN departments dept ON d.department_id = dept.id
                ORDER BY dept.name ASC, u.first_name ASC
            ");
            $doctors = $stmt->fetchAll();
            
            // Get all wards
            $stmt = $pdo->query("
                SELECT w.id as ward_id, w.name as ward_name, w.capacity,
                       dept.name as department_name,
                       (SELECT COUNT(*) FROM patients WHERE ward_id = w.id) as occupied
                FROM wards w
                JOIN departments dept ON w.department_id = dept.id
                ORDER BY dept.name ASC, w.name ASC
            ");
            $wards = $stmt->fetchAll();
            
            sendResponse(true, 'Referral options retrieved.', [
                'doctors' => $doctors,
                'wards' => $wards
            ]);
        } catch (PDOException $e) {
            sendResponse(false, 'Failed to fetch referral options.', ['error' => $e->getMessage()], 500);
        }
    } else {
        sendResponse(false, 'Invalid GET action.', null, 400);
    }
} elseif ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $_GET['action'] ?? '';
    
    if ($action === 'assign_nurse') {
        $patientId = intval($input['patient_id'] ?? 0);
        $nurseId = intval($input['nurse_id'] ?? 0);
        
        if ($patientId <= 0 || $nurseId <= 0) {
            sendResponse(false, 'Invalid patient or nurse selected.', null, 400);
        }
        
        try {
            // Verify nurse exists
            $stmt = $pdo->prepare("SELECT id FROM nurses WHERE id = ?");
            $stmt->execute([$nurseId]);
            if (!$stmt->fetch()) {
                sendResponse(false, 'Selected nurse does not exist.', null, 404);
            }
            
            // Update patient's assigned nurse
            $stmt = $pdo->prepare("UPDATE patients SET nurse_id = ? WHERE id = ?");
            $stmt->execute([$nurseId, $patientId]);
            
            sendResponse(true, 'Nurse successfully assigned to the patient.');
        } catch (PDOException $e) {
            sendResponse(false, 'Failed to assign nurse.', ['error' => $e->getMessage()], 500);
        }
        
    } elseif ($action === 'refer_patient') {
        $patientId = intval($input['patient_id'] ?? 0);
        $referTo = $input['refer_to'] ?? ''; // 'doctor', 'ward', or 'both'
        $referredDoctorId = intval($input['referred_doctor_id'] ?? 0);
        $referredWardId = intval($input['referred_ward_id'] ?? 0);
        $referralReason = trim($input['reason'] ?? '');
        
        if ($patientId <= 0 || empty($referTo) || empty($referralReason)) {
            sendResponse(false, 'Invalid inputs for patient referral.', null, 400);
        }
        
        try {
            $pdo->beginTransaction();
            
            // 1. Get patient's existing details
            $stmt = $pdo->prepare("SELECT user_id FROM patients WHERE id = ?");
            $stmt->execute([$patientId]);
            $patient = $stmt->fetch();
            if (!$patient) {
                sendResponse(false, 'Patient not found.', null, 404);
            }
            
            // 2. Perform referrals
            $referredName = "";
            if (($referTo === 'doctor' || $referTo === 'both') && $referredDoctorId > 0) {
                // Schedule referral appointment (approved by default)
                $stmt = $pdo->prepare("
                    INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, reason, status)
                    VALUES (?, ?, CURDATE() + INTERVAL 1 DAY, '10:00:00', ?, 'approved')
                ");
                $stmt->execute([$patientId, $referredDoctorId, 'Referral: ' . $referralReason]);
                
                // Get referred doctor's fee to make billing invoice
                $stmt = $pdo->prepare("
                    SELECT d.consultation_fee, u.first_name, u.last_name 
                    FROM doctors d 
                    JOIN users u ON d.user_id = u.id 
                    WHERE d.id = ?
                ");
                $stmt->execute([$referredDoctorId]);
                $refDoc = $stmt->fetch();
                
                if ($refDoc) {
                    $referredName = "Dr. " . $refDoc['first_name'] . " " . $refDoc['last_name'];
                    // Generate Billing Invoice
                    $stmt = $pdo->prepare("
                        INSERT INTO billing (patient_id, amount, status, due_date)
                        VALUES (?, ?, 'unpaid', CURDATE() + INTERVAL 1 DAY)
                    ");
                    $stmt->execute([$patientId, $refDoc['consultation_fee']]);
                }
            }
            
            if (($referTo === 'ward' || $referTo === 'both') && $referredWardId > 0) {
                // Admit to ward
                // Verify ward capacity
                $stmt = $pdo->prepare("SELECT capacity, (SELECT COUNT(*) FROM patients WHERE ward_id = ?) as occupied, name FROM wards WHERE id = ?");
                $stmt->execute([$referredWardId, $referredWardId]);
                $ward = $stmt->fetch();
                
                if (!$ward) {
                    sendResponse(false, 'Selected ward does not exist.', null, 404);
                }
                
                if ($ward['occupied'] >= $ward['capacity']) {
                    sendResponse(false, 'Referral failed. Selected ward is at full capacity.', null, 400);
                }
                
                // Update patient's ward
                $stmt = $pdo->prepare("UPDATE patients SET ward_id = ? WHERE id = ?");
                $stmt->execute([$referredWardId, $patientId]);
                
                // Automatically assign patient to a nurse in that ward, if available
                $stmt = $pdo->prepare("SELECT id FROM nurses WHERE ward_id = ? LIMIT 1");
                $stmt->execute([$referredWardId]);
                $nurse = $stmt->fetch();
                if ($nurse) {
                    $stmt = $pdo->prepare("UPDATE patients SET nurse_id = ? WHERE id = ?");
                    $stmt->execute([$nurse['id'], $patientId]);
                }
                
                $referredName = ($referredName ? $referredName . " and " : "") . $ward['name'];
            }
            
            // Add a medical record entry for the referral log
            $stmt = $pdo->prepare("
                INSERT INTO medical_records (patient_id, doctor_id, diagnosis, treatment, prescription, visit_date)
                VALUES (?, ?, ?, ?, ?, CURDATE())
            ");
            $stmt->execute([
                $patientId, 
                $doctorId, 
                'Referred to ' . $referredName, 
                'Referral initiated by primary doctor.', 
                'Reason: ' . $referralReason
            ]);
            
            $pdo->commit();
            sendResponse(true, 'Patient successfully referred to ' . $referredName . '.');
            
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            sendResponse(false, 'Failed to complete referral.', ['error' => $e->getMessage()], 500);
        }
    } else {
        sendResponse(false, 'Invalid POST action.', null, 400);
    }
} else {
    sendResponse(false, 'Method not allowed.', null, 405);
}
?>
