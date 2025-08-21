<?php
/**
 * Session Check Endpoint
 * Returns current user's authentication status and admin privileges
 */

// Start session
session_start();

// Include session functions
require_once 'session.php';

// Set response headers
header('Content-Type: application/json');

// Check if user is logged in
$isLoggedIn = isLoggedIn();
$isAdmin = isAdmin();
$userId = getCurrentUserId();
$userType = getCurrentUserType();

// Return response
echo json_encode([
    'success' => true,
    'isLoggedIn' => $isLoggedIn,
    'isAdmin' => $isAdmin,
    'userId' => $userId,
    'userType' => $userType
]);
?>
