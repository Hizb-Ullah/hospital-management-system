<?php
// hospital/api/auth.php
session_start();
require_once '../includes/db.php';

header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

// Helper to return JSON responses
function sendResponse($success, $message, $data = null, $code = 200) {
    http_response_code($code);
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

// Get raw JSON input
$input = json_decode(file_get_contents('php://input'), true);

if ($method === 'POST') {
    if ($action === 'register') {
        // Validate patient registration fields
        $username   = trim($input['username'] ?? '');
        $password   = trim($input['password'] ?? '');
        $email      = trim($input['email'] ?? '');
        $first_name = trim($input['first_name'] ?? '');
        $last_name  = trim($input['last_name'] ?? '');
        $phone      = trim($input['phone'] ?? '');
        $gender     = trim($input['gender'] ?? '');
        $dob        = trim($input['dob'] ?? '');
        $blood      = trim($input['blood_group'] ?? '');
        $address    = trim($input['address'] ?? '');
        $emergency  = trim($input['emergency_contact'] ?? '');

        // Server-side validation
        if (empty($username) || empty($password) || empty($email) || empty($first_name) || empty($last_name)) {
            sendResponse(false, 'Please fill in all required fields.', null, 400);
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            sendResponse(false, 'Please provide a valid email address.', null, 400);
        }
        if (strlen($password) < 6) {
            sendResponse(false, 'Password must be at least 6 characters long.', null, 400);
        }

        try {
            // Check if username or email already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            if ($stmt->fetch()) {
                sendResponse(false, 'Username or Email is already registered.', null, 409);
            }

            // Begin Transaction
            $pdo->beginTransaction();

            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Insert into users table
            $stmt = $pdo->prepare("
                INSERT INTO users (username, password, email, first_name, last_name, role, phone, gender, dob)
                VALUES (?, ?, ?, ?, ?, 'patient', ?, ?, ?)
            ");
            $stmt->execute([$username, $hashedPassword, $email, $first_name, $last_name, $phone, $gender, $dob]);
            
            $userId = $pdo->lastInsertId();

            // Insert into patients table
            $stmt = $pdo->prepare("
                INSERT INTO patients (user_id, blood_group, address, emergency_contact)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$userId, $blood, $address, $emergency]);

            // Commit Transaction
            $pdo->commit();

            sendResponse(true, 'Registration successful! You can now log in.');

        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            sendResponse(false, 'Server database error. Please try again later.', ['error' => $e->getMessage()], 500);
        }

    } elseif ($action === 'login') {
        $username = trim($input['username'] ?? '');
        $password = trim($input['password'] ?? '');
        $selected_role = trim($input['role'] ?? '');

        if (empty($username) || empty($password) || empty($selected_role)) {
            sendResponse(false, 'Please select your role and enter username and password.', null, 400);
        }

        try {
            // Retrieve user details
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($password, $user['password'])) {
                sendResponse(false, 'Invalid username or password.', null, 401);
            }

            if ($user['role'] !== $selected_role) {
                sendResponse(false, 'Role mismatch. You are not registered as a ' . ucfirst($selected_role) . '.', null, 403);
            }

            // Set basic session info
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];
            $_SESSION['email'] = $user['email'];

            // Fetch secondary roles details
            if ($user['role'] === 'patient') {
                $stmt = $pdo->prepare("SELECT id FROM patients WHERE user_id = ?");
                $stmt->execute([$user['id']]);
                $patient = $stmt->fetch();
                $_SESSION['patient_id'] = $patient['id'] ?? null;
            } elseif ($user['role'] === 'doctor') {
                $stmt = $pdo->prepare("SELECT id, department_id FROM doctors WHERE user_id = ?");
                $stmt->execute([$user['id']]);
                $doctor = $stmt->fetch();
                $_SESSION['doctor_id'] = $doctor['id'] ?? null;
                $_SESSION['department_id'] = $doctor['department_id'] ?? null;
            } elseif ($user['role'] === 'nurse') {
                $stmt = $pdo->prepare("SELECT id, department_id FROM nurses WHERE user_id = ?");
                $stmt->execute([$user['id']]);
                $nurse = $stmt->fetch();
                $_SESSION['nurse_id'] = $nurse['id'] ?? null;
                $_SESSION['department_id'] = $nurse['department_id'] ?? null;
            }

            sendResponse(true, 'Login successful!', [
                'username' => $user['username'],
                'role' => $user['role'],
                'first_name' => $user['first_name'],
                'last_name' => $user['last_name']
            ]);

        } catch (PDOException $e) {
            sendResponse(false, 'Database error during login.', ['error' => $e->getMessage()], 500);
        }

    } elseif ($action === 'logout') {
        // Destroy session
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
        sendResponse(true, 'Logged out successfully.');
    } else {
        sendResponse(false, 'Action not recognized.', null, 400);
    }
} elseif ($method === 'GET') {
    if ($action === 'status') {
        if (isset($_SESSION['user_id'])) {
            sendResponse(true, 'User is authenticated.', [
                'user_id' => $_SESSION['user_id'],
                'username' => $_SESSION['username'],
                'role' => $_SESSION['role'],
                'first_name' => $_SESSION['first_name'],
                'last_name' => $_SESSION['last_name'],
                'email' => $_SESSION['email'],
                'patient_id' => $_SESSION['patient_id'] ?? null,
                'doctor_id' => $_SESSION['doctor_id'] ?? null,
                'nurse_id' => $_SESSION['nurse_id'] ?? null
            ]);
        } else {
            sendResponse(false, 'User is not authenticated.', null, 401);
        }
    } else {
        sendResponse(false, 'Action not recognized.', null, 400);
    }
} else {
    sendResponse(false, 'HTTP Method not supported.', null, 405);
}
?>
