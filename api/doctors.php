<?php
// hospital/api/doctors.php
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
    $dept_id = isset($_GET['department_id']) ? intval($_GET['department_id']) : 0;
    $search = trim($_GET['search'] ?? '');

    try {
        $query = "
            SELECT d.*, u.first_name, u.last_name, u.email, u.phone, u.gender, u.dob, u.username,
                   dept.name as department_name, dept.icon as department_icon
            FROM doctors d
            JOIN users u ON d.user_id = u.id
            LEFT JOIN departments dept ON d.department_id = dept.id
            WHERE 1=1
        ";
        $params = [];

        if ($dept_id > 0) {
            $query .= " AND d.department_id = ?";
            $params[] = $dept_id;
        }

        if (!empty($search)) {
            $query .= " AND (u.first_name LIKE ? OR u.last_name LIKE ? OR d.specialization LIKE ?)";
            $searchTerm = "%$search%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $query .= " ORDER BY u.first_name ASC";
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $doctors = $stmt->fetchAll();

        sendResponse(true, 'Doctors retrieved successfully.', $doctors);
    } catch (PDOException $e) {
        sendResponse(false, 'Failed to retrieve doctors.', ['error' => $e->getMessage()], 500);
    }
} else {
    // Admin checking
    if ($role !== 'admin') {
        sendResponse(false, 'Unauthorized. Administrative privilege required.', null, 403);
    }

    $input = json_decode(file_get_contents('php://input'), true);

    if ($method === 'POST') {
        // Validate inputs
        $username = trim($input['username'] ?? '');
        $password = trim($input['password'] ?? '');
        $email = trim($input['email'] ?? '');
        $first_name = trim($input['first_name'] ?? '');
        $last_name = trim($input['last_name'] ?? '');
        $phone = trim($input['phone'] ?? '');
        $gender = trim($input['gender'] ?? 'male');
        $dob = trim($input['dob'] ?? '');
        
        $department_id = intval($input['department_id'] ?? 0);
        $specialization = trim($input['specialization'] ?? '');
        $experience_years = intval($input['experience_years'] ?? 0);
        $consultation_fee = floatval($input['consultation_fee'] ?? 0.00);
        $bio = trim($input['bio'] ?? '');

        if (empty($username) || empty($password) || empty($email) || empty($first_name) || empty($last_name) || $department_id <= 0) {
            sendResponse(false, 'Please fill in all required fields.', null, 400);
        }

        try {
            // Check uniqueness
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            if ($stmt->fetch()) {
                sendResponse(false, 'Username or Email already exists.', null, 409);
            }

            $pdo->beginTransaction();

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("
                INSERT INTO users (username, password, email, first_name, last_name, role, phone, gender, dob)
                VALUES (?, ?, ?, ?, ?, 'doctor', ?, ?, ?)
            ");
            $stmt->execute([$username, $hashedPassword, $email, $first_name, $last_name, $phone, $gender, $dob]);
            $userId = $pdo->lastInsertId();

            $stmt = $pdo->prepare("
                INSERT INTO doctors (user_id, department_id, specialization, experience_years, consultation_fee, bio)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$userId, $department_id, $specialization, $experience_years, $consultation_fee, $bio]);

            $pdo->commit();
            sendResponse(true, 'Doctor account created successfully.');
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            sendResponse(false, 'Failed to create doctor account.', ['error' => $e->getMessage()], 500);
        }
    } elseif ($method === 'PUT') {
        $id = intval($input['id'] ?? 0); // Doctor primary ID
        $user_id = intval($input['user_id'] ?? 0);
        $email = trim($input['email'] ?? '');
        $first_name = trim($input['first_name'] ?? '');
        $last_name = trim($input['last_name'] ?? '');
        $phone = trim($input['phone'] ?? '');
        $gender = trim($input['gender'] ?? 'male');
        $dob = trim($input['dob'] ?? '');
        
        $department_id = intval($input['department_id'] ?? 0);
        $specialization = trim($input['specialization'] ?? '');
        $experience_years = intval($input['experience_years'] ?? 0);
        $consultation_fee = floatval($input['consultation_fee'] ?? 0.00);
        $bio = trim($input['bio'] ?? '');

        if ($id <= 0 || $user_id <= 0 || empty($email) || empty($first_name) || empty($last_name) || $department_id <= 0) {
            sendResponse(false, 'Required fields missing.', null, 400);
        }

        try {
            $pdo->beginTransaction();

            // Update user record
            $stmt = $pdo->prepare("
                UPDATE users 
                SET email = ?, first_name = ?, last_name = ?, phone = ?, gender = ?, dob = ?
                WHERE id = ?
            ");
            $stmt->execute([$email, $first_name, $last_name, $phone, $gender, $dob, $user_id]);

            // Update doctor record
            $stmt = $pdo->prepare("
                UPDATE doctors 
                SET department_id = ?, specialization = ?, experience_years = ?, consultation_fee = ?, bio = ?
                WHERE id = ?
            ");
            $stmt->execute([$department_id, $specialization, $experience_years, $consultation_fee, $bio, $id]);

            $pdo->commit();
            sendResponse(true, 'Doctor account updated successfully.');
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            sendResponse(false, 'Failed to update doctor account.', ['error' => $e->getMessage()], 500);
        }
    } elseif ($method === 'DELETE') {
        $user_id = intval($_GET['user_id'] ?? 0); // Deleting user will cascade delete doctor details

        if ($user_id <= 0) {
            sendResponse(false, 'Invalid Doctor User ID.', null, 400);
        }

        try {
            // Delete user, CASCADE deletes from doctors
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'doctor'");
            $stmt->execute([$user_id]);
            sendResponse(true, 'Doctor deleted successfully.');
        } catch (PDOException $e) {
            sendResponse(false, 'Failed to delete doctor account.', ['error' => $e->getMessage()], 500);
        }
    } else {
        sendResponse(false, 'Method not allowed.', null, 405);
    }
}
?>
