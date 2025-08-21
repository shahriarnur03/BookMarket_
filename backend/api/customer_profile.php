<?php
/**
 * Customer Profile API
 * Handles customer profile data retrieval, updates, password changes, and account deletion
 * 
 * @author BookMarket Team
 * @version 1.0
 */

// Include required files
require_once '../config/database.php';
require_once '../config/session.php';

/**
 * Customer Profile Manager Class
 * Manages all customer profile operations
 */
class CustomerProfileManager {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
    }
    
    /**
     * Get customer profile data
     * @param int $userId User ID
     * @return array|false Profile data or false on failure
     */
    public function getCustomerProfile($userId) {
        try {
            $profile = $this->db->selectOne(
                "SELECT id, username, email, user_type, first_name, last_name, phone, 
                        address, city, postal_code, country, created_at
                 FROM users 
                 WHERE id = ?",
                [intval($userId)]
            );
            
            if ($profile) {
                $profile['created_at'] = date('F j, Y', strtotime($profile['created_at']));
            }
            
            return $profile;
            
        } catch (Exception $e) {
            error_log("Get Customer Profile Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update customer profile
     * @param int $userId User ID
     * @param array $data Profile data to update
     * @return bool True on success, false on failure
     */
    public function updateCustomerProfile($userId, $data) {
        try {
            $result = $this->db->execute(
                "UPDATE users SET 
                    first_name = ?, 
                    last_name = ?, 
                    phone = ?, 
                    address = ?, 
                    city = ?, 
                    postal_code = ?, 
                    country = ?
                 WHERE id = ?",
                [
                    $data['first_name'],
                    $data['last_name'],
                    $data['phone'],
                    $data['address'],
                    $data['city'],
                    $data['postal_code'],
                    $data['country'],
                    intval($userId)
                ]
            );
            
            return $result !== false;
            
        } catch (Exception $e) {
            error_log("Update Customer Profile Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Change customer password
     * @param int $userId User ID
     * @param string $currentPassword Current password
     * @param string $newPassword New password
     * @return bool True on success, false on failure
     */
    public function changePassword($userId, $currentPassword, $newPassword) {
        try {
            // First verify current password
            $user = $this->db->selectOne(
                "SELECT password_hash FROM users WHERE id = ?",
                [intval($userId)]
            );
            
            if (!$user) {
                return false;
            }
            
            // Verify current password
            if (!password_verify($currentPassword, $user['password_hash'])) {
                return false;
            }
            
            // Hash new password
            $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
            
            // Update password
            $result = $this->db->execute(
                "UPDATE users SET password_hash = ? WHERE id = ?",
                [$newPasswordHash, intval($userId)]
            );
            
            return $result !== false;
            
        } catch (Exception $e) {
            error_log("Change Password Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete customer account
     * @param int $userId User ID
     * @param string $password Password for verification
     * @return bool True on success, false on failure
     */
    public function deleteAccount($userId, $password) {
        try {
            // First verify password
            $user = $this->db->selectOne(
                "SELECT password_hash FROM users WHERE id = ?",
                [intval($userId)]
            );
            
            if (!$user) {
                return false;
            }
            
            // Verify password
            if (!password_verify($password, $user['password_hash'])) {
                return false;
            }
            
            // Start transaction
            $this->db->beginTransaction();
            
            try {
                // Delete user's books
                $this->db->execute(
                    "DELETE FROM books WHERE seller_id = ?",
                    [intval($userId)]
                );
                
                // Delete user's orders
                $this->db->execute(
                    "DELETE FROM orders WHERE user_id = ?",
                    [intval($userId)]
                );
                
                // Delete user's cart items
                $this->db->execute(
                    "DELETE FROM cart WHERE user_id = ?",
                    [intval($userId)]
                );
                
                // Delete user's reviews
                $this->db->execute(
                    "DELETE FROM book_reviews WHERE user_id = ?",
                    [intval($userId)]
                );
                
                // Finally delete the user
                $result = $this->db->execute(
                    "DELETE FROM users WHERE id = ?",
                    [intval($userId)]
                );
                
                if ($result !== false) {
                    $this->db->commit();
                    return true;
                } else {
                    $this->db->rollback();
                    return false;
                }
                
            } catch (Exception $e) {
                $this->db->rollback();
                throw $e;
            }
            
        } catch (Exception $e) {
            error_log("Delete Account Error: " . $e->getMessage());
            return false;
        }
    }
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customerProfile = new CustomerProfileManager();
    $action = $_POST['action'] ?? '';
    
    // For now, accept user_id from request (will be improved with proper session management)
    $userId = $_POST['user_id'] ?? null;
    
    // If no user_id provided, try to get from session
    if (!$userId) {
        if (!isLoggedIn()) {
            sendErrorResponse('User not logged in', 401);
            exit;
        }
        $userId = getCurrentUserId();
    }
    
    if (!$userId) {
        sendErrorResponse('User ID required', 400);
        exit;
    }
    
    switch ($action) {
        case 'get_profile':
            $profile = $customerProfile->getCustomerProfile($userId);
            if ($profile !== false) {
                sendSuccessResponse($profile, 'Profile retrieved successfully');
            } else {
                sendErrorResponse('Failed to retrieve profile');
            }
            break;
            
        case 'update_profile':
            $profileData = [
                'first_name' => $_POST['first_name'] ?? '',
                'last_name' => $_POST['last_name'] ?? '',
                'phone' => $_POST['phone'] ?? '',
                'address' => $_POST['address'] ?? '',
                'city' => $_POST['city'] ?? '',
                'postal_code' => $_POST['postal_code'] ?? '',
                'country' => $_POST['country'] ?? 'Bangladesh'
            ];
            
            $result = $customerProfile->updateCustomerProfile($userId, $profileData);
            if ($result) {
                sendSuccessResponse([], 'Profile updated successfully');
            } else {
                sendErrorResponse('Failed to update profile');
            }
            break;
            
        case 'change_password':
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            
            if (empty($currentPassword) || empty($newPassword)) {
                sendErrorResponse('Current password and new password are required');
            }
            
            $result = $customerProfile->changePassword($userId, $currentPassword, $newPassword);
            if ($result) {
                sendSuccessResponse([], 'Password changed successfully');
            } else {
                sendErrorResponse('Failed to change password. Please check your current password.');
            }
            break;
            
        case 'delete_account':
            $password = $_POST['password'] ?? '';
            
            if (empty($password)) {
                sendErrorResponse('Password is required to delete account');
            }
            
            $result = $customerProfile->deleteAccount($userId, $password);
            if ($result) {
                sendSuccessResponse([], 'Account deleted successfully');
            } else {
                sendErrorResponse('Failed to delete account. Please check your password.');
            }
            break;
            
        default:
            sendErrorResponse('Invalid action', 400);
            break;
    }
}

// Handle GET requests for direct data access
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $customerProfile = new CustomerProfileManager();
    $action = $_GET['action'] ?? '';
    
    // For now, accept user_id from request (will be improved with proper session management)
    $userId = $_GET['user_id'] ?? null;
    
    // If no user_id provided, try to get from session
    if (!$userId) {
        if (!isLoggedIn()) {
            sendErrorResponse('User not logged in', 401);
            exit;
        }
        $userId = getCurrentUserId();
    }
    
    if (!$userId) {
        sendErrorResponse('User ID required', 400);
        exit;
    }
    
    switch ($action) {
        case 'get_profile':
            $profile = $customerProfile->getCustomerProfile($userId);
            if ($profile !== false) {
                sendSuccessResponse($profile, 'Profile retrieved successfully');
            } else {
                sendErrorResponse('Failed to retrieve profile');
            }
            break;
            
        default:
            sendErrorResponse('Invalid action', 400);
            break;
    }
}
?>
