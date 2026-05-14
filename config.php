<?php
define('DB_HOST', 'localhost');
// define('DB_HOST', '127.0.0.1:3309');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'employee_management');

function getConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        http_response_code(500);
        die(json_encode([
            'success' => false,
            'message' => 'Database connection failed: ' . $conn->connect_error
        ]));
    }
    $conn->set_charset('utf8mb4');
    return $conn;
}
