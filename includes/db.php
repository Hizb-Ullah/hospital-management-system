<?php
// hospital/includes/db.php

$host = 'localhost';
$db   = 'medicare_database';
$user = 'root';
$pass = ''; // Default XAMPP password is empty
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // If it's an API request, return JSON. Otherwise, display a user-friendly error.
    $is_api = (strpos($_SERVER['REQUEST_URI'], '/api/') !== false);
    
    if ($is_api) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Database connection failed. Please ensure MySQL is running in XAMPP and the database is imported.',
            'debug' => $e->getMessage()
        ]);
        exit;
    } else {
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Database Connection Error - Medicare</title>
            <style>
                body {
                    font-family: 'Inter', -apple-system, sans-serif;
                    background-color: #f8fafc;
                    color: #0f172a;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    height: 100vh;
                    margin: 0;
                }
                .error-card {
                    background: white;
                    padding: 2.5rem;
                    border-radius: 16px;
                    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
                    max-width: 500px;
                    width: 100%;
                    text-align: center;
                    border-top: 4px solid #ef4444;
                }
                h1 {
                    color: #ef4444;
                    font-size: 1.5rem;
                    margin-top: 0;
                }
                p {
                    color: #64748b;
                    line-height: 1.6;
                    margin-bottom: 1.5rem;
                }
                .instruction {
                    background: #f1f5f9;
                    padding: 1rem;
                    border-radius: 8px;
                    text-align: left;
                    font-size: 0.9rem;
                    font-family: monospace;
                    word-break: break-all;
                }
            </style>
        </head>
        <body>
            <div class="error-card">
                <h1>Database Connection Failed</h1>
                <p>We are unable to connect to the database. Please ensure your local MySQL server is running inside the XAMPP Control Panel and the <strong>medicare_database</strong> has been correctly imported.</p>
                <div class="instruction">
                    1. Open XAMPP Control Panel<br>
                    2. Click "Start" next to MySQL<br>
                    3. Import: database/medicare_database.sql<br>
                    4. Error Details: <?php echo htmlspecialchars($e->getMessage()); ?>
                </div>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
}
?>
