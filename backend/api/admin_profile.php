<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Include database configuration
require_once '../config/database.example.php';

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Only POST method is allowed'
    ]);
    exit();
}

// Get the action from the request
$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'get_profile':
            getAdminProfile($pdo);
            break;
        case 'update_profile':
            updateAdminProfile($pdo);
            break;
        case 'change_password':
            changeAdminPassword($pdo);
            break;
        case 'update_admin_settings':
            updateAdminSettings($pdo);
            break;
        case 'delete_account':
            deleteAdminAccount($pdo);
            break;
        default:
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action'
            ]);
            break;
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}

function getAdminProfile($pdo) {
    $admin_id = $_POST['admin_id'] ?? '';
    
    if (empty($admin_id)) {
        echo json_encode([
            'success' => false,
            'message' => 'Admin ID is required'
        ]);
        return;
    }
    
    try {
        // For now, return default admin data
        // In a real application, you would query the database
        $adminData = [
            'id' => $admin_id,
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@bookmarket.com',
            'phone' => '+880 123 456789',
            'role' => 'super_admin',
            'permissions' => ['users', 'books', 'orders', 'reports', 'settings'],
            'created_at' => 'January 2023',
            'last_login' => date('Y-m-d H:i:s')
        ];
        
        echo json_encode([
            'success' => true,
            'data' => $adminData
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error fetching admin profile: ' . $e->getMessage()
        ]);
    }
}

function updateAdminProfile($pdo) {
    $admin_id = $_POST['admin_id'] ?? '';
    $first_name = $_POST['first_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    
    if (empty($admin_id)) {
        echo json_encode([
            'success' => false,
            'message' => 'Admin ID is required'
        ]);
        return;
    }
    
    if (empty($first_name) || empty($last_name)) {
        echo json_encode([
            'success' => false,
            'message' => 'First name and last name are required'
        ]);
        return;
    }
    
    try {
        // For now, just return success
        // In a real application, you would update the database
        echo json_encode([
            'success' => true,
            'message' => 'Admin profile updated successfully'
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error updating admin profile: ' . $e->getMessage()
        ]);
    }
}

function changeAdminPassword($pdo) {
    $admin_id = $_POST['admin_id'] ?? '';
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    
    if (empty($admin_id) || empty($current_password) || empty($new_password)) {
        echo json_encode([
            'success' => false,
            'message' => 'All fields are required'
        ]);
        return;
    }
    
    if (strlen($new_password) < 6) {
        echo json_encode([
            'success' => false,
            'message' => 'New password must be at least 6 characters long'
        ]);
        return;
    }
    
    try {
        // For now, just return success
        // In a real application, you would verify the current password and update with the new one
        echo json_encode([
            'success' => true,
            'message' => 'Password changed successfully'
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error changing password: ' . $e->getMessage()
        ]);
    }
}

function updateAdminSettings($pdo) {
    $admin_id = $_POST['admin_id'] ?? '';
    $role = $_POST['role'] ?? '';
    $permissions = $_POST['permissions'] ?? '';
    
    if (empty($admin_id)) {
        echo json_encode([
            'success' => false,
            'message' => 'Admin ID is required'
        ]);
        return;
    }
    
    try {
        // For now, just return success
        // In a real application, you would update the database
        echo json_encode([
            'success' => true,
            'message' => 'Admin settings updated successfully'
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error updating admin settings: ' . $e->getMessage()
        ]);
    }
}

function deleteAdminAccount($pdo) {
    $admin_id = $_POST['admin_id'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($admin_id) || empty($password)) {
        echo json_encode([
            'success' => false,
            'message' => 'Admin ID and password are required'
        ]);
        return;
    }
    
    try {
        // For now, just return success
        // In a real application, you would verify the password and delete the account
        echo json_encode([
            'success' => true,
            'message' => 'Admin account deleted successfully'
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error deleting admin account: ' . $e->getMessage()
        ]);
    }
}
?>
