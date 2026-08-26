<?php
// hospital/api/nurse_actions.php
session_start();
require_once '../includes/db.php';

header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'];
$userId = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? null;
$nurseId = $_SESSION['nurse_id'] ?? null;
$deptId = $_SESSION['department_id'] ?? null;

function sendResponse($success, $message, $data = null, $code = 200) {
    http_response_code($code);
    echo json_encode(['success' => $success, 'message' => $message, 'data' => $data]);
    exit;
}

if (!$userId || $role !== 'nurse' || !$nurseId) {
    sendResponse(false, 'Unauthorized. Access restricted to nurses.', null, 401);
}

if ($method === 'GET') {
    try {
        // Fetch nurse's complete profile
        $stmt = $pdo->prepare("
            SELECT n.id as nurse_id, n.shift, n.duty_time,
                   u.first_name, u.last_name, u.email, u.phone, u.gender, u.dob,
                   d.id as doctor_id, doc_u.first_name as doctor_first_name, doc_u.last_name as doctor_last_name, d.specialization,
                   w.id as ward_id, w.name as ward_name,
                   dept.name as department_name
            FROM nurses n
            JOIN users u ON n.user_id = u.id
            JOIN departments dept ON n.department_id = dept.id
            LEFT JOIN doctors d ON n.doctor_id = d.id
            LEFT JOIN users doc_u ON d.user_id = doc_u.id
            LEFT JOIN wards w ON n.ward_id = w.id
            WHERE n.id = ?
        ");
        $stmt->execute([$nurseId]);
        $profile = $stmt->fetch();
        
        if (!$profile) {
            sendResponse(false, 'Nurse profile not found.', null, 404);
        }
        
        // Fetch patients under their care (either assigned nurse_id, or in their assigned ward)
        $patients = [];
        if ($profile['ward_id']) {
            $stmt = $pdo->prepare("
                SELECT p.id as patient_id, p.blood_group, p.emergency_contact, p.address,
                       u.first_name, u.last_name, u.gender, u.dob, u.phone,
                       w.name as ward_name,
                       (p.nurse_id = ?) as is_directly_assigned
                FROM patients p
                JOIN users u ON p.user_id = u.id
                LEFT JOIN wards w ON p.ward_id = w.id
                WHERE p.nurse_id = ? OR p.ward_id = ?
                ORDER BY is_directly_assigned DESC, u.first_name ASC
            ");
            $stmt->execute([$nurseId, $nurseId, $profile['ward_id']]);
            $patients = $stmt->fetchAll();
        } else {
            $stmt = $pdo->prepare("
                SELECT p.id as patient_id, p.blood_group, p.emergency_contact, p.address,
                       u.first_name, u.last_name, u.gender, u.dob, u.phone,
                       w.name as ward_name,
                       1 as is_directly_assigned
                FROM patients p
                JOIN users u ON p.user_id = u.id
                LEFT JOIN wards w ON p.ward_id = w.id
                WHERE p.nurse_id = ?
                ORDER BY u.first_name ASC
            ");
            $stmt->execute([$nurseId]);
            $patients = $fetchAll();
        }
        
        // Function to calculate patient criteria (Age & Gender)
        if (!function_exists('getPatientCriteria')) {
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
        }
        
        foreach ($patients as &$pat) {
            $pat['criteria'] = getPatientCriteria($pat['dob'], $pat['gender']);
        }
        
        sendResponse(true, 'Nurse details retrieved.', [
            'profile' => $profile,
            'patients' => $patients
        ]);
        
    } catch (PDOException $e) {
        sendResponse(false, 'Failed to fetch nurse details.', ['error' => $e->getMessage()], 500);
    }
} else {
    sendResponse(false, 'Method not allowed.', null, 405);
}
?>
