<?php
/**
 * User Authentication System
 * Handles user registration, login, logout, and profile management
 * 
 * @author BookMarket Team
 * @version 1.0
 */

// Include required files
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

/**
 * User Authentication Class
 * Manages all user authentication operations
 */
class UserAuth {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
    }
    
    /**
     * Get user statistics (admin)
     * @return array|false Statistics or false on failure
     */
    public function getUserStats() {
        try {
            $stats = $this->db->selectOne(
                "SELECT 
                    COUNT(*) as total_users,
                    COUNT(CASE WHEN user_type = 'admin' THEN 1 END) as admin_users,
                    COUNT(CASE WHEN user_type = 'customer' THEN 1 END) as customer_users
                 FROM users"
            );
            
            if ($stats) {
                $stats['total_users'] = intval($stats['total_users'] ?? 0);
                $stats['admin_users'] = intval($stats['admin_users'] ?? 0);
                $stats['customer_users'] = intval($stats['customer_users'] ?? 0);
            }
            
            return $stats;
        } catch (Exception $e) {
            error_log("Get User Stats Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get recent users (admin)
     * @param int $limit Number of users to return
     * @return array|false Users or false on failure
     */
    public function getRecentUsers($limit = 10) {
        try {
            $users = $this->db->select(
                "SELECT id, username, email, user_type, created_at 
                 FROM users 
                 ORDER BY created_at DESC 
                 LIMIT ?",
                [intval($limit)]
            );
            return $users;
        } catch (Exception $e) {
            error_log("Get Recent Users Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Register a new user
     * @param array $userData User registration data
     * @return array Result with success status and message
     */
    public function registerUser($userData) {
        try {
            // Validate required fields
            $requiredFields = ['username', 'email', 'password', 'first_name', 'last_name', 'user_type'];
            foreach ($requiredFields as $field) {
                if (empty($userData[$field])) {
                    return ['success' => false, 'message' => ucfirst($field) . ' is required'];
                }
            }
            
            // Validate email format
            if (!isValidEmail($userData['email'])) {
                return ['success' => false, 'message' => 'Invalid email format'];
            }
            
            // Validate phone if provided
            if (!empty($userData['phone']) && !isValidPhone($userData['phone'])) {
                return ['success' => false, 'message' => 'Invalid phone number format'];
            }
            
            // Check if username already exists
            $existingUser = $this->db->selectOne(
                "SELECT id FROM users WHERE username = ?",
                [$userData['username']]
            );
            if ($existingUser) {
                return ['success' => false, 'message' => 'Username already exists'];
            }
            
            // Check if email already exists
            $existingEmail = $this->db->selectOne(
                "SELECT id FROM users WHERE email = ?",
                [$userData['email']]
            );
            if ($existingEmail) {
                return ['success' => false, 'message' => 'Email already registered'];
            }
            
            // Hash password
            $passwordHash = password_hash($userData['password'], PASSWORD_DEFAULT);
            
            // Insert new user
            $userId = $this->db->insert(
                "INSERT INTO users (username, email, password_hash, user_type, first_name, last_name, phone, address, city, postal_code, country) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [
                    cleanInput($userData['username']),
                    cleanInput($userData['email']),
                    $passwordHash,
                    cleanInput($userData['user_type']),
                    cleanInput($userData['first_name']),
                    cleanInput($userData['last_name']),
                    cleanInput($userData['phone'] ?? ''),
                    cleanInput($userData['address'] ?? ''),
                    cleanInput($userData['city'] ?? ''),
                    cleanInput($userData['postal_code'] ?? ''),
                    cleanInput($userData['country'] ?? 'Bangladesh')
                ]
            );
            
            if ($userId) {
                return [
                    'success' => true, 
                    'message' => 'User registered successfully',
                    'user_id' => $userId
                ];
            } else {
                return ['success' => false, 'message' => 'Registration failed'];
            }
            
        } catch (Exception $e) {
            error_log("User Registration Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Registration failed. Please try again.'];
        }
    }
    
    /**
     * Authenticate user login
     * @param string $email User email
     * @param string $password User password
     * @return array Result with success status and user data
     */
    public function loginUser($email, $password, $userType = null) {
        try {
            // Validate input
            if (empty($email) || empty($password)) {
                return ['success' => false, 'message' => 'Email and password are required'];
            }
            
            // Get user by email
            $user = $this->db->selectOne(
                "SELECT * FROM users WHERE email = ?",
                [cleanInput($email)]
            );
            
            if (!$user) {
                return ['success' => false, 'message' => 'Invalid email or password'];
            }
            
            // Verify password
            if (!password_verify($password, $user['password_hash'])) {
                return ['success' => false, 'message' => 'Invalid email or password'];
            }
            
            // Validate user type if provided
            if ($userType && $user['user_type'] !== $userType) {
                return ['success' => false, 'message' => 'Invalid user type selected. Please select the correct user type.'];
            }
            
            // Create user session with additional user data
            loginUser($user['id'], $user['username'], $user['user_type'], $user);
            
            // Return success with user data (excluding sensitive info)
            unset($user['password_hash']);
            return [
                'success' => true,
                'message' => 'Login successful',
                'user' => $user,
                'redirect' => $user['user_type'] === 'admin' ? 'admin/dashboard.html' : 'customer/dashboard.html'
            ];
            
        } catch (Exception $e) {
            error_log("User Login Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Login failed. Please try again.'];
        }
    }
    
    /**
     * Get user profile by ID
     * @param int $userId User ID
     * @return array|false User profile or false on failure
     */
    public function getUserProfile($userId) {
        try {
            $user = $this->db->selectOne(
                "SELECT id, username, email, user_type, first_name, last_name, phone, address, city, postal_code, country, profile_image, created_at 
                 FROM users WHERE id = ?",
                [$userId]
            );
            
            return $user;
            
        } catch (Exception $e) {
            error_log("Get User Profile Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get user details by ID (admin function)
     * @param int $userId User ID
     * @return array|false User details or false on failure
     */
    public function getUserDetails($userId) {
        try {
            $user = $this->db->selectOne(
                "SELECT id, username, email, user_type, first_name, last_name, phone, address, city, postal_code, country, profile_image, created_at 
                 FROM users WHERE id = ?",
                [$userId]
            );
            
            return $user;
            
        } catch (Exception $e) {
            error_log("Get User Details Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update user profile
     * @param int $userId User ID
     * @param array $profileData Profile data to update
     * @return array Result with success status and message
     */
    public function updateUserProfile($userId, $profileData) {
        try {
            // Validate user exists
            $existingUser = $this->db->selectOne("SELECT id FROM users WHERE id = ?", [$userId]);
            if (!$existingUser) {
                return ['success' => false, 'message' => 'User not found'];
            }
            
            // Validate email if being updated
            if (isset($profileData['email']) && !isValidEmail($profileData['email'])) {
                return ['success' => false, 'message' => 'Invalid email format'];
            }
            
            // Check if email is already taken by another user
            if (isset($profileData['email'])) {
                $existingEmail = $this->db->selectOne(
                    "SELECT id FROM users WHERE email = ? AND id != ?",
                    [$profileData['email'], $userId]
                );
                if ($existingEmail) {
                    return ['success' => false, 'message' => 'Email already taken'];
                }
            }
            
            // Build update query dynamically
            $updateFields = [];
            $params = [];
            
            $allowedFields = ['first_name', 'last_name', 'phone', 'address', 'city', 'postal_code', 'country'];
            foreach ($allowedFields as $field) {
                if (isset($profileData[$field])) {
                    $updateFields[] = "$field = ?";
                    $params[] = cleanInput($profileData[$field]);
                }
            }
            
            // Add email if provided
            if (isset($profileData['email'])) {
                $updateFields[] = "email = ?";
                $params[] = cleanInput($profileData['email']);
            }
            
            if (empty($updateFields)) {
                return ['success' => false, 'message' => 'No valid fields to update'];
            }
            
            // Add user ID to params
            $params[] = $userId;
            
            // Execute update
            $result = $this->db->execute(
                "UPDATE users SET " . implode(', ', $updateFields) . " WHERE id = ?",
                $params
            );
            
            if ($result !== false) {
                return ['success' => true, 'message' => 'Profile updated successfully'];
            } else {
                return ['success' => false, 'message' => 'Profile update failed'];
            }
            
        } catch (Exception $e) {
            error_log("Update User Profile Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Profile update failed. Please try again.'];
        }
    }
    
    /**
     * Change user password
     * @param int $userId User ID
     * @param string $currentPassword Current password
     * @param string $newPassword New password
     * @return array Result with success status and message
     */
    public function changePassword($userId, $currentPassword, $newPassword) {
        try {
            // Validate input
            if (empty($currentPassword) || empty($newPassword)) {
                return ['success' => false, 'message' => 'Current and new passwords are required'];
            }
            
            // Validate new password length
            if (strlen($newPassword) < 6) {
                return ['success' => false, 'message' => 'New password must be at least 6 characters'];
            }
            
            // Get current user password
            $user = $this->db->selectOne(
                "SELECT password_hash FROM users WHERE id = ?",
                [$userId]
            );
            
            if (!$user) {
                return ['success' => false, 'message' => 'User not found'];
            }
            
            // Verify current password
            if (!password_verify($currentPassword, $user['password_hash'])) {
                return ['success' => false, 'message' => 'Current password is incorrect'];
            }
            
            // Hash new password
            $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
            
            // Update password
            $result = $this->db->execute(
                "UPDATE users SET password_hash = ? WHERE id = ?",
                [$newPasswordHash, $userId]
            );
            
            if ($result !== false) {
                return ['success' => true, 'message' => 'Password changed successfully'];
            } else {
                return ['success' => false, 'message' => 'Password change failed'];
            }
            
        } catch (Exception $e) {
            error_log("Change Password Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Password change failed. Please try again.'];
        }
    }
    
    /**
     * Get all users (admin function)
     * @param int $limit Number of users to return
     * @param int $offset Offset for pagination
     * @return array|false Users array or false on failure
     */
    public function getAllUsers($limit = 50, $offset = 0) {
        try {
            $users = $this->db->select(
                "SELECT id, username, email, user_type, first_name, last_name, phone, city, created_at 
                 FROM users ORDER BY created_at DESC LIMIT ? OFFSET ?",
                [$limit, $offset]
            );
            
            return $users;
            
        } catch (Exception $e) {
            error_log("Get All Users Error: " . $e->getMessage());
            return false;
        }
    }
    

    
    /**
     * Delete user (admin function)
     * @param int $userId User ID
     * @return array Result with success status and message
     */
    public function deleteUser($userId) {
        try {
            // Check if user exists
            $user = $this->db->selectOne("SELECT id FROM users WHERE id = ?", [$userId]);
            if (!$user) {
                return ['success' => false, 'message' => 'User not found'];
            }
            
            // Delete user (cascade will handle related records)
            $result = $this->db->execute("DELETE FROM users WHERE id = ?", [$userId]);
            
            if ($result !== false) {
                return ['success' => true, 'message' => 'User deleted successfully'];
            } else {
                return ['success' => false, 'message' => 'User deletion failed'];
            }
            
        } catch (Exception $e) {
            error_log("Delete User Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'User deletion failed. Please try again.'];
        }
    }
}

// Handle AJAX requests
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $userAuth = new UserAuth();
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'register':
            $result = $userAuth->registerUser($_POST);
            sendJSONResponse($result);
            break;
            
        case 'login':
            $userType = isset($_POST['user_type']) ? $_POST['user_type'] : null;
            $result = $userAuth->loginUser($_POST['email'], $_POST['password'], $userType);
            sendJSONResponse($result);
            break;
            
        case 'logout':
            logoutUser();
            sendSuccessResponse([], 'Logged out successfully');
            break;
            
        case 'update_profile':
            if (!isLoggedIn()) {
                sendErrorResponse('User not logged in', 401);
            }
            $result = $userAuth->updateUserProfile(getCurrentUserId(), $_POST);
            sendJSONResponse($result);
            break;
            
        case 'change_password':
            if (!isLoggedIn()) {
                sendErrorResponse('User not logged in', 401);
            }
            $result = $userAuth->changePassword(
                getCurrentUserId(),
                $_POST['current_password'],
                $_POST['new_password']
            );
            sendJSONResponse($result);
            break;
            
        case 'get_profile':
            if (!isLoggedIn()) {
                sendErrorResponse('User not logged in', 401);
            }
            $profile = $userAuth->getUserProfile(getCurrentUserId());
            if ($profile) {
                sendSuccessResponse($profile, 'Profile retrieved successfully');
            } else {
                sendErrorResponse('Failed to retrieve profile');
            }
            break;
            
        case 'get_all_users':
            if (!isAdmin()) {
                sendErrorResponse('Access denied', 403);
            }
            $users = $userAuth->getAllUsers();
            if ($users !== false) {
                sendSuccessResponse($users, 'Users retrieved successfully');
            } else {
                sendErrorResponse('Failed to retrieve users');
            }
            break;
            

            
        case 'delete_user':
            if (!isLoggedIn()) {
                sendErrorResponse('User not logged in', 401);
            }
            if (!isAdmin()) {
                sendErrorResponse('Access denied. Admin privileges required', 403);
            }
            $userId = $_POST['user_id'] ?? 0;
            if (!$userId) {
                sendErrorResponse('User ID is required');
            }
            $result = $userAuth->deleteUser($userId);
            if ($result && $result['success']) {
                sendSuccessResponse(['message' => $result['message']]);
            } else {
                sendErrorResponse($result['message'] ?? 'Failed to delete user');
            }
            break;
        
        case 'admin_login':
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            
            if (empty($email) || empty($password)) {
                sendErrorResponse('Email and password are required');
            }
            
            $result = $userAuth->loginUser($email, $password, 'admin');
            if ($result && $result['success']) {
                // Login successful, session already set by loginUser method
                sendSuccessResponse($result['user'], 'Login successful');
            } else {
                sendErrorResponse($result['message'] ?? 'Login failed');
            }
            break;
            
        case 'get_current_user':
            if (!isLoggedIn()) {
                sendErrorResponse('User not logged in', 401);
            }
            $currentUser = getCurrentUser();
            if ($currentUser) {
                sendSuccessResponse($currentUser, 'Current user retrieved successfully');
            } else {
                sendErrorResponse('Failed to retrieve current user');
            }
            break;
            
        case 'debug_session':
            // Debug endpoint to see what's in the session
            $debugData = [
                'session_id' => session_id(),
                'session_data' => $_SESSION,
                'is_logged_in' => isLoggedIn(),
                'is_admin' => isAdmin(),
                'current_user_id' => getCurrentUserId(),
                'current_user_type' => getCurrentUserType()
            ];
            sendSuccessResponse($debugData, 'Session debug info');
            break;
            
        case 'check_admin_auth':
            if (!isLoggedIn()) {
                sendErrorResponse('User not logged in', 401);
            }
            if (!isAdmin()) {
                sendErrorResponse('Access denied. Admin privileges required', 403);
            }
            sendSuccessResponse(['message' => 'Admin authenticated successfully']);
            break;

        case 'get_user_stats':
            if (!isLoggedIn()) {
                sendErrorResponse('User not logged in', 401);
            }
            if (!isAdmin()) {
                sendErrorResponse('Access denied. Admin privileges required', 403);
            }
            $stats = $userAuth->getUserStats();
            if ($stats !== false) {
                sendSuccessResponse($stats, 'User statistics retrieved successfully');
            } else {
                sendErrorResponse('Failed to retrieve user statistics');
            }
            break;
        
        case 'get_recent_users':
            if (!isAdmin()) {
                sendErrorResponse('Access denied', 403);
            }
            $limit = isset($_POST['limit']) ? intval($_POST['limit']) : 10;
            $users = $userAuth->getRecentUsers($limit);
            if ($users !== false) {
                sendSuccessResponse($users, 'Recent users retrieved successfully');
            } else {
                sendErrorResponse('Failed to retrieve recent users');
            }
            break;
            
        case 'get_user_details':
            if (!isAdmin()) {
                sendErrorResponse('Access denied', 403);
            }
            $userDetails = $userAuth->getUserDetails($_POST['user_id']);
            if ($userDetails) {
                sendSuccessResponse($userDetails, 'User details retrieved successfully');
            } else {
                sendErrorResponse('Failed to retrieve user details');
            }
            break;
            
        default:
            sendErrorResponse('Invalid action', 400);
            break;
    }
}
?>
