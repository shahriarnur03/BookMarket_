<?php
/**
 * Session Management and Authentication
 * Handles user sessions, authentication checks, and user data retrieval
 * 
 * @author BookMarket Team
 * @version 1.0
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if user is currently logged in
 * @return bool True if user is logged in, false otherwise
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if current user is an admin
 * @return bool True if user is admin, false otherwise
 */
function isAdmin() {
    return isLoggedIn() && isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin';
}

/**
 * Get current user ID from session
 * @return int|null User ID or null if not logged in
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user type from session
 * @return string|null User type or null if not logged in
 */
function getCurrentUserType() {
    return $_SESSION['user_type'] ?? null;
}

/**
 * Get current user data from session
 * @return array|null User data array or null if not logged in
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'] ?? '',
        'email' => $_SESSION['email'] ?? '',
        'user_type' => $_SESSION['user_type'] ?? '',
        'first_name' => $_SESSION['first_name'] ?? '',
        'last_name' => $_SESSION['last_name'] ?? ''
    ];
}

/**
 * Login user and set session
 * @param int $userId User ID
 * @param string $username Username
 * @param string $userType User type (admin/customer)
 * @return void
 */
function loginUser($userId, $username, $userType) {
    $_SESSION['user_id'] = $userId;
    $_SESSION['username'] = $username;
    $_SESSION['user_type'] = $userType;
    $_SESSION['login_time'] = time();
}

/**
 * Clean and sanitize input data (alias for sanitizeInput for compatibility)
 * @param string $data Input data to clean
 * @return string Cleaned data
 */
function cleanInput($data) {
    return sanitizeInput($data);
}

/**
 * Validate phone number format
 * @param string $phone Phone number to validate
 * @return bool True if valid, false otherwise
 */
function isValidPhone($phone) {
    // Basic phone validation - allows digits, spaces, dashes, and parentheses
    return preg_match('/^[\d\s\-\(\)\+]+$/', $phone) && strlen($phone) >= 10;
}

/**
 * Logout current user
 */
function logoutUser() {
    // Clear all session variables
    $_SESSION = array();
    
    // Destroy the session
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    session_destroy();
}

/**
 * Send JSON success response
 * @param mixed $data Response data
 * @param string $message Success message
 * @param int $statusCode HTTP status code
 */
function sendSuccessResponse($data, $message = 'Success', $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

/**
 * Send JSON error response
 * @param string $message Error message
 * @param int $statusCode HTTP status code
 */
function sendErrorResponse($message = 'Error occurred', $statusCode = 400) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => $message,
        'data' => null
    ]);
    exit;
}

/**
 * Send JSON response (legacy function for compatibility)
 * @param array $result Result array
 */
function sendJSONResponse($result) {
    header('Content-Type: application/json');
    echo json_encode($result);
    exit;
}

/**
 * Format currency for display
 * @param float $amount Amount to format
 * @param string $currency Currency symbol
 * @return string Formatted currency string
 */
function formatCurrency($amount, $currency = '৳') {
    return $currency . number_format($amount, 2);
}

/**
 * Sanitize input data
 * @param mixed $data Data to sanitize
 * @return mixed Sanitized data
 */
function sanitizeInput($data) {
    if (is_array($data)) {
        foreach ($data as $key => $value) {
            $data[$key] = sanitizeInput($value);
        }
    } else {
        $data = htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
    return $data;
}

/**
 * Validate email format
 * @param string $email Email to validate
 * @return bool True if valid email, false otherwise
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Generate random string
 * @param int $length Length of string to generate
 * @return string Random string
 */
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}
?>
