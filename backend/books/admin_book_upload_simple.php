<?php
/**
 * Simplified Admin Book Upload API
 * This is a temporary version to test basic functionality
 */

// Prevent any output before JSON response
ob_start();

// Include required files
require_once '../config/database.php';

// Set content type to JSON
header('Content-Type: application/json');

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Clear any output buffer
    ob_clean();
    
    // Disable error display (only log to file)
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    
    // Log the incoming request
    error_log("Admin Book Upload Request: " . json_encode($_POST));
    
    try {
        $action = $_POST['action'] ?? '';
        error_log("Action requested: " . $action);
        
        switch ($action) {
            case 'test_connection':
                try {
                    $db = getDB();
                    if ($db) {
                        echo json_encode([
                            'success' => true,
                            'message' => 'Database connection successful',
                            'data' => ['status' => 'connected']
                        ]);
                    } else {
                        echo json_encode([
                            'success' => false,
                            'message' => 'Database connection failed'
                        ]);
                    }
                } catch (Exception $e) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Database error: ' . $e->getMessage()
                    ]);
                }
                break;
                
            case 'get_categories':
                try {
                    $db = getDB();
                    $categories = $db->select("SELECT * FROM categories ORDER BY name ASC");
                    
                    if ($categories !== false) {
                        echo json_encode([
                            'success' => true,
                            'message' => 'Categories retrieved successfully',
                            'data' => $categories
                        ]);
                    } else {
                        echo json_encode([
                            'success' => false,
                            'message' => 'Failed to retrieve categories'
                        ]);
                    }
                } catch (Exception $e) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Error: ' . $e->getMessage()
                    ]);
                }
                break;
                
            case 'add_book':
                try {
                    // Validate required fields
                    $requiredFields = ['title', 'author', 'price', 'book_condition', 'category_id'];
                    foreach ($requiredFields as $field) {
                        if (empty($_POST[$field])) {
                            echo json_encode([
                                'success' => false,
                                'message' => ucfirst($field) . ' is required'
                            ]);
                            exit;
                        }
                    }
                    
                    // Validate price
                    if (!is_numeric($_POST['price']) || $_POST['price'] <= 0) {
                        echo json_encode([
                            'success' => false,
                            'message' => 'Invalid price'
                        ]);
                        exit;
                    }
                    
                    // Validate condition
                    $validConditions = ['New', 'Excellent', 'Good', 'Fair', 'Poor'];
                    if (!in_array($_POST['book_condition'], $validConditions)) {
                        echo json_encode([
                            'success' => false,
                            'message' => 'Invalid condition'
                        ]);
                        exit;
                    }
                    
                    $db = getDB();
                    
                    // Check if category exists
                    $category = $db->selectOne(
                        "SELECT id FROM categories WHERE id = ?",
                        [$_POST['category_id']]
                    );
                    if (!$category) {
                        echo json_encode([
                            'success' => false,
                            'message' => 'Invalid category'
                        ]);
                        exit;
                    }
                    
                    // Get or create admin seller ID
                    $adminUser = $db->selectOne(
                        "SELECT id, first_name, last_name FROM users WHERE user_type = 'admin' LIMIT 1"
                    );
                    
                    if (!$adminUser) {
                        // Create admin user if none exists
                        $adminId = $db->insert(
                            "INSERT INTO users (username, email, password_hash, user_type, first_name, last_name) 
                             VALUES (?, ?, ?, ?, ?, ?)",
                            [
                                'admin_seller',
                                'admin@bookmarket.com',
                                password_hash('admin123', PASSWORD_DEFAULT),
                                'admin',
                                'Admin',
                                'Seller'
                            ]
                        );
                        $adminSellerId = $adminId;
                    } else {
                        $adminSellerId = $adminUser['id'];
                        
                        // Update admin user with current admin's name if it's generic
                        if ($adminUser['first_name'] === 'Admin' && $adminUser['last_name'] === 'Seller') {
                            $currentAdmin = $db->selectOne(
                                "SELECT first_name, last_name FROM users WHERE id = ?",
                                [getCurrentUserId()]
                            );
                            if ($currentAdmin && ($currentAdmin['first_name'] !== 'Admin' || $currentAdmin['last_name'] !== 'Seller')) {
                                $db->execute(
                                    "UPDATE users SET first_name = ?, last_name = ? WHERE id = ?",
                                    [$currentAdmin['first_name'], $currentAdmin['last_name'], $adminSellerId]
                                );
                            }
                        }
                    }
                    
                    // Insert new book
                    $bookId = $db->insert(
                        "INSERT INTO books (title, author, isbn, description, price, book_condition, cover_image_path, additional_images, seller_id, category_id, status) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'approved')",
                        [
                            $_POST['title'],
                            $_POST['author'],
                            $_POST['isbn'] ?? '',
                            $_POST['description'] ?? '',
                            floatval($_POST['price']),
                            $_POST['book_condition'],
                            $_POST['cover_image_path'] ?? '',
                            $_POST['additional_images'] ?? '',
                            $adminSellerId,
                            $_POST['category_id']
                        ]
                    );
                    
                    if ($bookId) {
                        echo json_encode([
                            'success' => true,
                            'message' => 'Book added successfully and approved',
                            'book_id' => $bookId
                        ]);
                    } else {
                        echo json_encode([
                            'success' => false,
                            'message' => 'Failed to add book'
                        ]);
                    }
                    
                } catch (Exception $e) {
                    error_log("Add Book Error: " . $e->getMessage());
                    echo json_encode([
                        'success' => false,
                        'message' => 'Failed to add book: ' . $e->getMessage()
                    ]);
                }
                break;
                
            default:
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid action'
                ]);
                break;
        }
        
    } catch (Exception $e) {
        error_log("General error in admin book upload: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Server error: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}
?>
