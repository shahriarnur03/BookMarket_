<?php
/**
 * Admin Login API
 * Handles admin authentication and sets PHP sessions
 */

session_start();
require_once '../config/database.php';
require_once '../config/session.php';

// Set response headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

try {
    // Get POST data
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        $input = $_POST;
    }
    
    $username = $input['username'] ?? '';
    $password = $input['password'] ?? '';
    
    // Validate input
    if (empty($username) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Username and password are required']);
        exit();
    }
    
    // Get database connection
    $conn = getDB();
    
    // Check if user exists and is admin
    $sql = "SELECT id, username, email, password_hash, user_type, first_name, last_name 
            FROM users 
            WHERE username = ? AND user_type = 'admin'";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
        exit();
    }
    
    // Verify password
    if (!password_verify($password, $user['password_hash'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
        exit();
    }
    
    // Login successful - set session
    loginUser(
        $user['id'],
        $user['username'],
        $user['user_type'],
        [
            'email' => $user['email'],
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name']
        ]
    );
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Login successful',
        'data' => [
            'user_id' => $user['id'],
            'username' => $user['username'],
            'user_type' => $user['user_type'],
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name']
        ]
    ]);
    
} catch (Exception $e) {
    error_log('Admin Login Error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred during login']);
}
?>
