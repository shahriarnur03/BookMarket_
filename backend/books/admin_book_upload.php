<?php
/**
 * Admin Book Upload API
 * Handles book uploads specifically for admin users
 * 
 * @author BookMarket Team
 * @version 1.0
 */

// Prevent any output before JSON response
ob_start();

// Include required files
require_once '../config/database.php';
require_once '../config/session.php';

/**
 * Admin Book Upload Manager Class
 * Manages book uploads for admin users
 */
class AdminBookUploadManager {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
    }
    
    /**
     * Add a new book as admin (automatically approved)
     * @param array $bookData Book data
     * @return array Result with success status and message
     */
    public function addBookAsAdmin($bookData) {
        try {
            // Validate required fields
            $requiredFields = ['title', 'author', 'price', 'book_condition', 'category_id'];
            foreach ($requiredFields as $field) {
                if (empty($bookData[$field])) {
                    return ['success' => false, 'message' => ucfirst($field) . ' is required'];
                }
            }
            
            // Validate price
            if (!is_numeric($bookData['price']) || $bookData['price'] <= 0) {
                return ['success' => false, 'message' => 'Invalid price'];
            }
            
            // Validate condition
            $validConditions = ['New', 'Excellent', 'Good', 'Fair', 'Poor'];
            if (!in_array($bookData['book_condition'], $validConditions)) {
                return ['success' => false, 'message' => 'Invalid condition'];
            }
            
            // Check if category exists
            $category = $this->db->selectOne(
                "SELECT id FROM categories WHERE id = ?",
                [$bookData['category_id']]
            );
            if (!$category) {
                return ['success' => false, 'message' => 'Invalid category'];
            }
            
            // Insert new book with admin seller ID (you can set this to a default admin user or create one)
            $adminSellerId = $this->getAdminSellerId();
            
            $bookId = $this->db->insert(
                "INSERT INTO books (title, author, isbn, description, price, book_condition, cover_image_path, additional_images, seller_id, category_id, status) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'approved')",
                [
                    cleanInput($bookData['title']),
                    cleanInput($bookData['author']),
                    cleanInput($bookData['isbn'] ?? ''),
                    cleanInput($bookData['description'] ?? ''),
                    floatval($bookData['price']),
                    cleanInput($bookData['book_condition']),
                    cleanInput($bookData['cover_image_path'] ?? ''),
                    cleanInput($bookData['additional_images'] ?? ''),
                    intval($adminSellerId),
                    intval($bookData['category_id'])
                ]
            );
            
            if ($bookId) {
                return [
                    'success' => true,
                    'message' => 'Book added successfully and approved',
                    'book_id' => $bookId
                ];
            } else {
                return ['success' => false, 'message' => 'Failed to add book'];
            }
            
        } catch (Exception $e) {
            error_log("Admin Add Book Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to add book. Please try again.'];
        }
    }
    
    /**
     * Get or create admin seller ID
     * @return int Admin seller ID
     */
    private function getAdminSellerId() {
        // First try to find an existing admin user
        $adminUser = $this->db->selectOne(
            "SELECT id FROM users WHERE user_type = 'admin' LIMIT 1"
        );
        
        if ($adminUser) {
            return $adminUser['id'];
        }
        
        // If no admin user exists, create one (this should rarely happen)
        $adminId = $this->db->insert(
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
        
        return $adminId;
    }
    
    /**
     * Get book categories
     * @return array|false Categories array or false on failure
     */
    public function getCategories() {
        try {
            $categories = $this->db->select(
                "SELECT * FROM categories ORDER BY name ASC"
            );
            
            return $categories;
            
        } catch (Exception $e) {
            error_log("Get Categories Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get admin upload statistics
     * @return array|false Statistics array or false on failure
     */
    public function getAdminUploadStats() {
        try {
            $adminSellerId = $this->getAdminSellerId();
            
            $stats = $this->db->selectOne(
                "SELECT 
                    COUNT(*) as total_books,
                    COUNT(CASE WHEN status = 'approved' THEN 1 END) as approved_books,
                    COUNT(CASE WHEN status = 'sold' THEN 1 END) as sold_books,
                    SUM(CASE WHEN status = 'approved' THEN price ELSE 0 END) as total_value
                 FROM books 
                 WHERE seller_id = ?",
                [$adminSellerId]
            );
            
            return $stats;
            
        } catch (Exception $e) {
            error_log("Get Admin Upload Stats Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get recent admin uploads
     * @param int $limit Number of books to return
     * @return array|false Books array or false on failure
     */
    public function getRecentAdminUploads($limit = 10) {
        try {
            $adminSellerId = $this->getAdminSellerId();
            
            $books = $this->db->select(
                "SELECT b.*, c.name as category_name
                 FROM books b
                 JOIN categories c ON b.category_id = c.id
                 WHERE b.seller_id = ?
                 ORDER BY b.created_at DESC
                 LIMIT ?",
                [$adminSellerId, $limit]
            );
            
            return $books;
            
        } catch (Exception $e) {
            error_log("Get Recent Admin Uploads Error: " . $e->getMessage());
            return false;
        }
    }
}

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
        // Check if user is logged in and is admin
        if (!isLoggedIn()) {
            error_log("User not logged in");
            sendErrorResponse('User not logged in', 401);
        }
        
        if (!isAdmin()) {
            error_log("User is not admin");
            sendErrorResponse('Access denied. Admin privileges required.', 403);
        }
        
        // Log session information for debugging
        error_log("Session user ID: " . (getCurrentUserId() ?? 'null'));
        error_log("Session user type: " . (getCurrentUserType() ?? 'null'));
        
        $adminBookManager = new AdminBookUploadManager();
        $action = $_POST['action'] ?? '';
        
        error_log("Action requested: " . $action);
        
        switch ($action) {
            case 'add_book':
                try {
                    $result = $adminBookManager->addBookAsAdmin($_POST);
                    error_log("Book upload result: " . json_encode($result));
                    sendJSONResponse($result);
                } catch (Exception $e) {
                    error_log("Exception in add_book: " . $e->getMessage());
                    sendErrorResponse('Server error: ' . $e->getMessage(), 500);
                }
                break;
            
        case 'get_categories':
            $categories = $adminBookManager->getCategories();
            if ($categories !== false) {
                sendSuccessResponse($categories, 'Categories retrieved successfully');
            } else {
                sendErrorResponse('Failed to retrieve categories');
            }
            break;
            
        case 'get_upload_stats':
            $stats = $adminBookManager->getAdminUploadStats();
            if ($stats !== false) {
                sendSuccessResponse($stats, 'Statistics retrieved successfully');
            } else {
                sendErrorResponse('Failed to retrieve statistics');
            }
            break;
            
        case 'get_recent_uploads':
            $limit = intval($_POST['limit'] ?? 10);
            $books = $adminBookManager->getRecentAdminUploads($limit);
            if ($books !== false) {
                sendSuccessResponse($books, 'Recent uploads retrieved successfully');
            } else {
                sendErrorResponse('Failed to retrieve recent uploads');
            }
            break;
            
        case 'test_connection':
            try {
                $db = getDB();
                if ($db) {
                    sendSuccessResponse(['status' => 'connected'], 'Database connection successful');
                } else {
                    sendErrorResponse('Database connection failed');
                }
            } catch (Exception $e) {
                sendErrorResponse('Database error: ' . $e->getMessage());
            }
            break;
            
        default:
            sendErrorResponse('Invalid action', 400);
            break;
        }
    } catch (Exception $e) {
        error_log("General error in admin book upload: " . $e->getMessage());
        sendErrorResponse('Server error: ' . $e->getMessage(), 500);
    }
}
?>
