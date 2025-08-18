<?php
/**
 * Session Management and Helper Functions
 * This file contains session management functions and utility functions
 * 
 * @author BookMarket Team
 * @version 1.0
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if user is logged in
 * @return bool True if user is logged in, false otherwise
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
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
 * Check if current user is admin
 * @return bool True if user is admin, false otherwise
 */
function isAdmin() {
    return getCurrentUserType() === 'admin';
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
 * Logout user and clear session
 * @return void
 */
function logoutUser() {
    session_unset();
    session_destroy();
}

/**
 * Clean and sanitize input data
 * @param string $data Input data to clean
 * @return string Cleaned data
 */
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Validate email format
 * @param string $email Email to validate
 * @return bool True if valid, false otherwise
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
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
 * Send JSON response
 * @param array $data Response data
 * @return void
 */
function sendJSONResponse($data) {
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Send success response
 * @param array $data Response data
 * @param string $message Success message
 * @return void
 */
function sendSuccessResponse($data, $message = 'Success') {
    sendJSONResponse([
        'success' => true,
        'message' => $message,
        'data' => $data
    ]);
}

/**
 * Send error response
 * @param string $message Error message
 * @param int $code HTTP status code
 * @return void
 */
function sendErrorResponse($message, $code = 400) {
    http_response_code($code);
    sendJSONResponse([
        'success' => false,
        'message' => $message
    ]);
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

/**
 * Generate order number
 * @return string Unique order number
 */
function generateOrderNumber() {
    $prefix = 'BM';
    $timestamp = date('YmdHis');
    $random = generateRandomString(4);
    return $prefix . $timestamp . $random;
}
?>
