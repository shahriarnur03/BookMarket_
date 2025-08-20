<?php
/**
 * Book Management System
 * Handles all book-related operations including CRUD, search, and filtering
 * 
 * @author BookMarket Team
 * @version 1.0
 */

// Include required files
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

/**
 * Book Manager Class
 * Manages all book-related operations
 */
class BookManager {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
    }
    
    /**
     * Add a new book for sale
     * @param array $bookData Book data
     * @param int $sellerId Seller user ID
     * @return array Result with success status and message
     */
    public function addBook($bookData, $sellerId) {
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
            
            // Insert new book
            $bookId = $this->db->insert(
                                                  "INSERT INTO books (title, author, isbn, description, price, book_condition, cover_image_path, additional_images, seller_id, category_id) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                 [
                    cleanInput($bookData['title']),
                    cleanInput($bookData['author']),
                    cleanInput($bookData['isbn'] ?? ''),
                    cleanInput($bookData['description'] ?? ''),
                    floatval($bookData['price']),
                    cleanInput($bookData['book_condition']),
                    cleanInput($bookData['cover_image_path'] ?? ''),
                    cleanInput($bookData['additional_images'] ?? ''),
                    intval($sellerId),
                    intval($bookData['category_id'])
                ]
            );
            
            if ($bookId) {
                return [
                    'success' => true,
                    'message' => 'Book added successfully and pending approval',
                    'book_id' => $bookId
                ];
            } else {
                return ['success' => false, 'message' => 'Failed to add book'];
            }
            
        } catch (Exception $e) {
            error_log("Add Book Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to add book. Please try again.'];
        }
    }
    
    /**
     * Get all approved books for browsing
     * @param array $filters Search and filter parameters
     * @param int $limit Number of books to return
     * @param int $offset Offset for pagination
     * @return array|false Books array or false on failure
     */
    public function getApprovedBooks($filters = [], $limit = 20, $offset = 0) {
        try {
            $whereConditions = ["b.status = 'approved'"];
            $params = [];
            
            // Apply filters
            if (!empty($filters['search'])) {
                $searchTerm = '%' . cleanInput($filters['search']) . '%';
                $whereConditions[] = "(b.title LIKE ? OR b.author LIKE ? OR b.isbn LIKE ?)";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            if (!empty($filters['category_id'])) {
                $whereConditions[] = "b.category_id = ?";
                $params[] = intval($filters['category_id']);
            }
            
            if (!empty($filters['book_condition'])) {
                $whereConditions[] = "b.book_condition = ?";
                $params[] = cleanInput($filters['book_condition']);
            }
            
            if (!empty($filters['min_price'])) {
                $whereConditions[] = "b.price >= ?";
                $params[] = floatval($filters['min_price']);
            }
            
            if (!empty($filters['max_price'])) {
                $whereConditions[] = "b.price <= ?";
                $params[] = floatval($filters['max_price']);
            }
            
            // Build WHERE clause
            $whereClause = implode(' AND ', $whereConditions);
            
            // Apply sorting
            $orderBy = "b.created_at DESC";
            if (!empty($filters['sort'])) {
                switch ($filters['sort']) {
                    case 'price_low':
                        $orderBy = "b.price ASC";
                        break;
                    case 'price_high':
                        $orderBy = "b.price DESC";
                        break;
                    case 'title_az':
                        $orderBy = "b.title ASC";
                        break;
                    case 'title_za':
                        $orderBy = "b.title DESC";
                        break;
                    case 'newest':
                        $orderBy = "b.created_at DESC";
                        break;
                    case 'oldest':
                        $orderBy = "b.created_at ASC";
                        break;
                }
            }
            
            // Add limit and offset to params
            $params[] = $limit;
            $params[] = $offset;
            
            $books = $this->db->select(
                "SELECT b.*, c.name as category_name, u.username as seller_name, u.city as seller_city
                 FROM books b
                 JOIN categories c ON b.category_id = c.id
                 JOIN users u ON b.seller_id = u.id
                 WHERE $whereClause
                 ORDER BY $orderBy
                 LIMIT ? OFFSET ?",
                $params
            );
            
            return $books;
            
        } catch (Exception $e) {
            error_log("Get Approved Books Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get book by ID with full details
     * @param int $bookId Book ID
     * @return array|false Book data or false on failure
     */
    public function getBookById($bookId) {
        try {
            $book = $this->db->selectOne(
                "SELECT b.*, c.name as category_name, c.description as category_description,
                        u.username as seller_name, u.first_name as seller_first_name, u.last_name as seller_last_name,
                        u.city as seller_city, u.phone as seller_phone
                 FROM books b
                 JOIN categories c ON b.category_id = c.id
                 JOIN users u ON b.seller_id = u.id
                 WHERE b.id = ?",
                [intval($bookId)]
            );
            
            if ($book) {
                // Increment view count
                $this->db->execute(
                    "UPDATE books SET views_count = views_count + 1 WHERE id = ?",
                    [intval($bookId)]
                );
            }
            
            return $book;
            
        } catch (Exception $e) {
            error_log("Get Book By ID Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get books by seller ID
     * @param int $sellerId Seller user ID
     * @param string $status Book status filter
     * @return array|false Books array or false on failure
     */
    public function getBooksBySeller($sellerId, $status = null) {
        try {
            $whereConditions = ["b.seller_id = ?"];
            $params = [intval($sellerId)];
            
            if ($status) {
                $whereConditions[] = "b.status = ?";
                $params[] = cleanInput($status);
            }
            
            $whereClause = implode(' AND ', $whereConditions);
            
            $books = $this->db->select(
                "SELECT b.*, c.name as category_name
                 FROM books b
                 JOIN categories c ON b.category_id = c.id
                 WHERE $whereClause
                 ORDER BY b.created_at DESC",
                $params
            );
            
            return $books;
            
        } catch (Exception $e) {
            error_log("Get Books By Seller Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all books for a specific user (admin function)
     * @param int $userId User ID
     * @return array|false Books array or false on failure
     */
    public function getUserBooks($userId) {
        try {
            $books = $this->db->select(
                "SELECT b.*, c.name as category_name
                 FROM books b
                 JOIN categories c ON b.category_id = c.id
                 WHERE b.seller_id = ?
                 ORDER BY b.created_at DESC",
                [intval($userId)]
            );
            
            return $books;
            
        } catch (Exception $e) {
            error_log("Get User Books Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all books for admin management
     * @param array $filters Filter parameters
     * @param int $limit Number of books to return
     * @param int $offset Offset for pagination
     * @return array|false Books array or false on failure
     */
    public function getAllBooks($filters = [], $limit = 50, $offset = 0) {
        try {
            $whereConditions = ["1=1"];
            $params = [];
            
            // Apply filters
            if (!empty($filters['status'])) {
                $whereConditions[] = "b.status = ?";
                $params[] = cleanInput($filters['status']);
            }
            
            if (!empty($filters['category_id'])) {
                $whereConditions[] = "b.category_id = ?";
                $params[] = intval($filters['category_id']);
            }
            
            if (!empty($filters['seller_id'])) {
                $whereConditions[] = "b.seller_id = ?";
                $params[] = intval($filters['seller_id']);
            }
            
            $whereClause = implode(' AND ', $whereConditions);
            
            // Add limit and offset to params
            $params[] = $limit;
            $params[] = $offset;
            
            $books = $this->db->select(
                "SELECT b.*, c.name as category_name, u.first_name, u.last_name, u.username, u.email as seller_email, u.user_type as seller_type
                 FROM books b
                 JOIN categories c ON b.category_id = c.id
                 JOIN users u ON b.seller_id = u.id
                 WHERE $whereClause
                 ORDER BY b.created_at DESC
                 LIMIT ? OFFSET ?",
                $params
            );
            
            return $books;
            
        } catch (Exception $e) {
            error_log("Get All Books Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update book status (approve/reject)
     * @param int $bookId Book ID
     * @param string $status New status
     * @param string $rejectionReason Reason for rejection (if applicable)
     * @return array Result with success status and message
     */
    public function updateBookStatus($bookId, $status, $rejectionReason = '') {
        try {
            // Validate status
            $validStatuses = ['pending', 'approved', 'rejected', 'sold'];
            if (!in_array($status, $validStatuses)) {
                return ['success' => false, 'message' => 'Invalid status'];
            }
            
            // Check if book exists
            $book = $this->db->selectOne("SELECT id, status FROM books WHERE id = ?", [intval($bookId)]);
            if (!$book) {
                return ['success' => false, 'message' => 'Book not found'];
            }
            
            // Update book status
            $result = $this->db->execute(
                "UPDATE books SET status = ? WHERE id = ?",
                [cleanInput($status), intval($bookId)]
            );
            
            if ($result !== false) {
                $message = "Book status updated to " . ucfirst($status);
                if ($status === 'rejected' && !empty($rejectionReason)) {
                    $message .= " - Reason: " . cleanInput($rejectionReason);
                }
                
                return ['success' => true, 'message' => $message];
            } else {
                return ['success' => false, 'message' => 'Failed to update book status'];
            }
            
        } catch (Exception $e) {
            error_log("Update Book Status Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to update book status. Please try again.'];
        }
    }
    
    /**
     * Update book information
     * @param int $bookId Book ID
     * @param array $bookData Book data to update
     * @param int $sellerId Seller user ID (for verification)
     * @return array Result with success status and message
     */
    public function updateBook($bookId, $bookData, $sellerId) {
        try {
            // Check if book exists and belongs to seller
            $book = $this->db->selectOne(
                "SELECT id, status FROM books WHERE id = ? AND seller_id = ?",
                [intval($bookId), intval($sellerId)]
            );
            
            if (!$book) {
                return ['success' => false, 'message' => 'Book not found or access denied'];
            }
            
            // Only allow updates if book is pending or rejected
            if ($book['status'] === 'approved' || $book['status'] === 'sold') {
                return ['success' => false, 'message' => 'Cannot update approved or sold books'];
            }
            
            // Build update query dynamically
            $updateFields = [];
            $params = [];
            
            $allowedFields = ['title', 'author', 'isbn', 'description', 'price', 'book_condition', 'category_id'];
            foreach ($allowedFields as $field) {
                if (isset($bookData[$field])) {
                    $updateFields[] = "$field = ?";
                    $params[] = cleanInput($bookData[$field]);
                }
            }
            
            if (empty($updateFields)) {
                return ['success' => false, 'message' => 'No valid fields to update'];
            }
            
            // Reset status to pending after update
            $updateFields[] = "status = 'pending'";
            
            // Add book ID to params
            $params[] = intval($bookId);
            
            // Execute update
            $result = $this->db->execute(
                "UPDATE books SET " . implode(', ', $updateFields) . " WHERE id = ?",
                $params
            );
            
            if ($result !== false) {
                return ['success' => true, 'message' => 'Book updated successfully and pending approval'];
            } else {
                return ['success' => false, 'message' => 'Book update failed'];
            }
            
        } catch (Exception $e) {
            error_log("Update Book Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Book update failed. Please try again.'];
        }
    }
    
    /**
     * Delete book
     * @param int $bookId Book ID
     * @param int $sellerId Seller user ID (for verification)
     * @return array Result with success status and message
     */
    public function deleteBook($bookId, $sellerId) {
        try {
            // Check if book exists and belongs to seller
            $book = $this->db->selectOne(
                "SELECT id, status FROM books WHERE id = ? AND seller_id = ?",
                [intval($bookId), intval($sellerId)]
            );
            
            if (!$book) {
                return ['success' => false, 'message' => 'Book not found or access denied'];
            }
            
            // Delete book
            $result = $this->db->execute("DELETE FROM books WHERE id = ?", [intval($bookId)]);
            
            if ($result !== false) {
                return ['success' => true, 'message' => 'Book deleted successfully'];
            } else {
                return ['success' => false, 'message' => 'Book deletion failed'];
            }
            
        } catch (Exception $e) {
            error_log("Delete Book Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Book deletion failed. Please try again.'];
        }
    }
    
    /**
     * Get detailed book information
     * @param int $bookId Book ID
     * @return array|false Book details or false on failure
     */
    public function getBookDetails($bookId) {
        try {
            $book = $this->db->selectOne(
                "SELECT b.*, c.name as category_name, u.first_name, u.last_name, u.username as seller_name, u.user_type as seller_type 
                 FROM books b 
                 LEFT JOIN categories c ON b.category_id = c.id 
                 LEFT JOIN users u ON b.seller_id = u.id 
                 WHERE b.id = ?",
                [intval($bookId)]
            );
            
            if ($book) {
                // Combine first and last name for seller
                if ($book['first_name'] && $book['last_name']) {
                    $book['seller_name'] = $book['first_name'] . ' ' . $book['last_name'];
                } elseif ($book['seller_name']) {
                    $book['seller_name'] = $book['seller_name'];
                } else {
                    $book['seller_name'] = 'Unknown Seller';
                }
                
                return $book;
            }
            
            return false;
        } catch (Exception $e) {
            error_log("Get Book Details Error: " . $e->getMessage());
            return false;
        }
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
     * Get book statistics for admin dashboard
     * @return array|false Statistics array or false on failure
     */
    public function getBookStats() {
        try {
            $stats = $this->db->selectOne(
                "SELECT 
                    COUNT(*) as total_books,
                    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_books,
                    COUNT(CASE WHEN status = 'approved' THEN 1 END) as approved_books,
                    COUNT(CASE WHEN status = 'rejected' THEN 1 END) as rejected_books,
                    COUNT(CASE WHEN status = 'sold' THEN 1 END) as sold_books,
                    SUM(CASE WHEN status = 'approved' THEN price ELSE 0 END) as total_value
                 FROM books"
            );
            
            return $stats;
            
        } catch (Exception $e) {
            error_log("Get Book Stats Error: " . $e->getMessage());
            return false;
        }
    }
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bookManager = new BookManager();
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add_book':
            if (!isLoggedIn()) {
                sendErrorResponse('User not logged in', 401);
            }
            $result = $bookManager->addBook($_POST, getCurrentUserId());
            sendJSONResponse($result);
            break;
            
        case 'get_approved_books':
            $result = $bookManager->getApprovedBooks($_POST);
            if ($result !== false) {
                sendSuccessResponse($result, 'Books retrieved successfully');
            } else {
                sendErrorResponse('Failed to retrieve books');
            }
            break;
            
        case 'get_book':
            $bookId = $_POST['book_id'] ?? 0;
            $book = $bookManager->getBookById($bookId);
            if ($book) {
                sendSuccessResponse($book, 'Book retrieved successfully');
            } else {
                sendErrorResponse('Book not found');
            }
            break;
            
        case 'get_seller_books':
            if (!isLoggedIn()) {
                sendErrorResponse('User not logged in', 401);
            }
            $status = $_POST['status'] ?? null;
            $books = $bookManager->getBooksBySeller(getCurrentUserId(), $status);
            if ($books !== false) {
                sendSuccessResponse($books, 'Books retrieved successfully');
            } else {
                sendErrorResponse('Failed to retrieve books');
            }
            break;
            
        case 'get_all_books':
            if (!isAdmin()) {
                sendErrorResponse('Access denied', 403);
            }
            $books = $bookManager->getAllBooks($_POST);
            if ($books !== false) {
                sendSuccessResponse($books, 'Books retrieved successfully');
            } else {
                sendErrorResponse('Failed to retrieve books');
            }
            break;
            
        case 'update_book_status':
            if (!isAdmin()) {
                sendErrorResponse('Access denied', 403);
            }
            $result = $bookManager->updateBookStatus(
                $_POST['book_id'],
                $_POST['status'],
                $_POST['rejection_reason'] ?? ''
            );
            sendJSONResponse($result);
            break;
            
        case 'update_book':
            if (!isLoggedIn()) {
                sendErrorResponse('User not logged in', 401);
            }
            $result = $bookManager->updateBook(
                $_POST['book_id'],
                $_POST,
                getCurrentUserId()
            );
            sendJSONResponse($result);
            break;
            
        case 'delete_book':
            if (!isLoggedIn()) {
                sendErrorResponse('User not logged in', 401);
            }
            $result = $bookManager->deleteBook(
                $_POST['book_id'],
                getCurrentUserId()
            );
            sendJSONResponse($result);
            break;
            
        case 'get_book_details':
            if (!isAdmin()) {
                sendErrorResponse('Access denied', 403);
            }
            $book = $bookManager->getBookDetails($_POST['book_id']);
            if ($book !== false) {
                sendSuccessResponse($book, 'Book details retrieved successfully');
            } else {
                sendErrorResponse('Failed to retrieve book details');
            }
            break;
            
        case 'get_categories':
            $categories = $bookManager->getCategories();
            if ($categories !== false) {
                sendSuccessResponse($categories, 'Categories retrieved successfully');
            } else {
                sendErrorResponse('Failed to retrieve categories');
            }
            break;
            
        case 'get_book_stats':
            if (!isAdmin()) {
                sendErrorResponse('Access denied', 403);
            }
            $stats = $bookManager->getBookStats();
            if ($stats !== false) {
                sendSuccessResponse($stats, 'Statistics retrieved successfully');
            } else {
                sendErrorResponse('Failed to retrieve statistics');
            }
            break;
            
        case 'get_user_books':
            if (!isAdmin()) {
                sendErrorResponse('Access denied', 403);
            }
            $userId = $_POST['user_id'] ?? 0;
            if (!$userId) {
                sendErrorResponse('User ID is required');
            }
            $books = $bookManager->getUserBooks($userId);
            if ($books !== false) {
                sendSuccessResponse($books, 'User books retrieved successfully');
            } else {
                sendErrorResponse('Failed to retrieve user books');
            }
            break;
            
        default:
            sendErrorResponse('Invalid action', 400);
            break;
    }
}
?>
