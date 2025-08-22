<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '../config/database.php';
require_once '../config/session.php';

class BookReviewManager {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    // Get all reviews for a specific book
    public function getBookReviews($bookId) {
        try {
            // First check if the book exists and is viewable
            $bookQuery = "SELECT id, status FROM books WHERE id = ?";
            $bookStmt = $this->db->getConnection()->prepare($bookQuery);
            $bookStmt->execute([$bookId]);
            $book = $bookStmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$book) {
                return ['success' => false, 'message' => 'Book not found'];
            }
            
            // For admin access, allow viewing reviews for any book
            // For regular users, only show reviews for approved books
            if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
                if ($book['status'] !== 'approved') {
                    return ['success' => false, 'message' => 'Reviews not available for this book'];
                }
            }
            
            $query = "SELECT r.*, u.username as author 
                      FROM book_reviews r 
                      JOIN users u ON r.user_id = u.id 
                      WHERE r.book_id = ? 
                      ORDER BY r.created_at DESC";
            
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute([$bookId]);
            
            $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Format the data for frontend
            foreach ($reviews as &$review) {
                $review['rating'] = (int)$review['rating'];
                $review['created_at'] = $review['created_at'];
            }
            
            return ['success' => true, 'reviews' => $reviews];
            
        } catch (PDOException $e) {
            error_log("Error getting book reviews: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to load reviews'];
        }
    }
    
    // Create a new review
    public function createReview($bookId, $userId, $rating, $comment) {
        try {
            // First check if the book exists and is reviewable (approved or owned by user)
            $bookQuery = "SELECT id, status, seller_id FROM books WHERE id = ?";
            $bookStmt = $this->db->getConnection()->prepare($bookQuery);
            $bookStmt->execute([$bookId]);
            $book = $bookStmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$book) {
                return ['success' => false, 'message' => 'Book not found'];
            }
            
            // Check if user can review this book (approved, owner, or admin)
            $userQuery = "SELECT user_type FROM users WHERE id = ?";
            $userStmt = $this->db->getConnection()->prepare($userQuery);
            $userStmt->execute([$userId]);
            $user = $userStmt->fetch(PDO::FETCH_ASSOC);
            
            $isAdmin = $user && $user['user_type'] === 'admin';
            $isOwner = $book['seller_id'] == $userId;
            $isApproved = $book['status'] === 'approved';
            
            if (!$isAdmin && !$isOwner && !$isApproved) {
                return ['success' => false, 'message' => 'You can only review approved books or your own books'];
            }
            
            // Check if user already reviewed this book
            $checkQuery = "SELECT id FROM book_reviews WHERE user_id = ? AND book_id = ?";
            $checkStmt = $this->db->getConnection()->prepare($checkQuery);
            $checkStmt->execute([$userId, $bookId]);
            
            if ($checkStmt->fetch()) {
                return ['success' => false, 'message' => 'You have already reviewed this book'];
            }
            
            // Validate rating
            if ($rating < 1 || $rating > 5) {
                return ['success' => false, 'message' => 'Invalid rating value'];
            }
            
            // Validate comment
            if (empty(trim($comment))) {
                return ['success' => false, 'message' => 'Review comment cannot be empty'];
            }
            
            // Insert new review
            $query = "INSERT INTO book_reviews (book_id, user_id, rating, review_text) VALUES (?, ?, ?, ?)";
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute([$bookId, $userId, $rating, $comment]);
            
            // Get the newly created review with user info
            $newReviewId = $this->db->getConnection()->lastInsertId();
            $getQuery = "SELECT r.*, u.username as author 
                        FROM book_reviews r 
                        JOIN users u ON r.user_id = u.id 
                        WHERE r.id = ?";
            $getStmt = $this->db->getConnection()->prepare($getQuery);
            $getStmt->execute([$newReviewId]);
            $newReview = $getStmt->fetch(PDO::FETCH_ASSOC);
            
            return [
                'success' => true, 
                'message' => 'Review submitted successfully',
                'review' => $newReview
            ];
            
        } catch (PDOException $e) {
            error_log("Error creating book review: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to submit review'];
        }
    }
    
    // Update an existing review
    public function updateReview($reviewId, $userId, $rating, $comment) {
        try {
            // Check if user owns this review
            $checkQuery = "SELECT id FROM book_reviews WHERE id = ? AND user_id = ?";
            $checkStmt = $this->db->getConnection()->prepare($checkQuery);
            $checkStmt->execute([$reviewId, $userId]);
            
            if (!$checkStmt->fetch()) {
                return ['success' => false, 'message' => 'You can only edit your own reviews'];
            }
            
            // Validate rating
            if ($rating < 1 || $rating > 5) {
                return ['success' => false, 'message' => 'Invalid rating value'];
            }
            
            // Validate comment
            if (empty(trim($comment))) {
                return ['success' => false, 'message' => 'Review comment cannot be empty'];
            }
            
            // Update review
            $query = "UPDATE book_reviews SET rating = ?, review_text = ? WHERE id = ? AND user_id = ?";
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute([$rating, $comment, $reviewId, $userId]);
            
            return ['success' => true, 'message' => 'Review updated successfully'];
            
        } catch (PDOException $e) {
            error_log("Error updating book review: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to update review'];
        }
    }
    
    // Delete a review
    public function deleteReview($reviewId, $userId) {
        try {
            // Check if user owns this review
            $checkQuery = "SELECT id FROM book_reviews WHERE id = ? AND user_id = ?";
            $checkStmt = $this->db->getConnection()->prepare($checkQuery);
            $checkStmt->execute([$reviewId, $userId]);
            
            if (!$checkStmt->fetch()) {
                return ['success' => false, 'message' => 'You can only delete your own reviews'];
            }
            
            // Delete review
            $query = "DELETE FROM book_reviews WHERE id = ? AND user_id = ?";
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute([$reviewId, $userId]);
            
            return ['success' => true, 'message' => 'Review deleted successfully'];
            
        } catch (PDOException $e) {
            error_log("Error deleting book review: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to delete review'];
        }
    }
    
    // Get review statistics for a book
    public function getBookReviewStats($bookId) {
        try {
            // First check if the book exists and is viewable
            $bookQuery = "SELECT id, status FROM books WHERE id = ?";
            $bookStmt = $this->db->getConnection()->prepare($bookQuery);
            $bookStmt->execute([$bookId]);
            $book = $bookStmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$book) {
                return ['success' => false, 'message' => 'Book not found'];
            }
            
            // Only show stats for approved books
            if ($book['status'] !== 'approved') {
                return ['success' => false, 'message' => 'Review statistics not available for this book'];
            }
            
            $query = "SELECT 
                        COUNT(*) as total_reviews,
                        AVG(rating) as average_rating,
                        COUNT(CASE WHEN rating = 5 THEN 1 END) as five_star,
                        COUNT(CASE WHEN rating = 4 THEN 1 END) as four_star,
                        COUNT(CASE WHEN rating = 3 THEN 1 END) as three_star,
                        COUNT(CASE WHEN rating = 2 THEN 1 END) as two_star,
                        COUNT(CASE WHEN rating = 1 THEN 1 END) as one_star
                      FROM book_reviews 
                      WHERE book_id = ?";
            
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute([$bookId]);
            
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Format the data
            $stats['average_rating'] = round($stats['average_rating'], 1);
            $stats['total_reviews'] = (int)$stats['total_reviews'];
            $stats['five_star'] = (int)$stats['five_star'];
            $stats['four_star'] = (int)$stats['four_star'];
            $stats['three_star'] = (int)$stats['three_star'];
            $stats['two_star'] = (int)$stats['two_star'];
            $stats['one_star'] = (int)$stats['one_star'];
            
            return ['success' => true, 'stats' => $stats];
            
        } catch (PDOException $e) {
            error_log("Error getting book review stats: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to load review statistics'];
        }
    }
}

// Handle API requests
try {
    $db = getDB();
    $reviewManager = new BookReviewManager($db);
    
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'get_reviews':
            if (!isset($_GET['book_id'])) {
                echo json_encode(['success' => false, 'message' => 'Book ID is required']);
                exit;
            }
            
            $bookId = (int)$_GET['book_id'];
            $result = $reviewManager->getBookReviews($bookId);
            echo json_encode($result);
            break;
            
        case 'create_review':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                exit;
            }
            
            if (!isLoggedIn()) {
                echo json_encode(['success' => false, 'message' => 'Authentication required']);
                exit;
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            $bookId = (int)($input['book_id'] ?? 0);
            $rating = (int)($input['rating'] ?? 0);
            $comment = trim($input['comment'] ?? '');
            $userId = getCurrentUserId();
            
            if (!$bookId || !$rating || !$comment) {
                echo json_encode(['success' => false, 'message' => 'Missing required fields']);
                exit;
            }
            
            $result = $reviewManager->createReview($bookId, $userId, $rating, $comment);
            echo json_encode($result);
            break;
            
        case 'update_review':
            if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                exit;
            }
            
            if (!isLoggedIn()) {
                echo json_encode(['success' => false, 'message' => 'Authentication required']);
                exit;
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            $reviewId = (int)($input['review_id'] ?? 0);
            $rating = (int)($input['rating'] ?? 0);
            $comment = trim($input['comment'] ?? '');
            $userId = getCurrentUserId();
            
            if (!$reviewId || !$rating || !$comment) {
                echo json_encode(['success' => false, 'message' => 'Missing required fields']);
                exit;
            }
            
            $result = $reviewManager->updateReview($reviewId, $userId, $rating, $comment);
            echo json_encode($result);
            break;
            
        case 'delete_review':
            if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                exit;
            }
            
            if (!isLoggedIn()) {
                echo json_encode(['success' => false, 'message' => 'Authentication required']);
                exit;
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            $reviewId = (int)($input['review_id'] ?? 0);
            $userId = getCurrentUserId();
            
            if (!$reviewId) {
                echo json_encode(['success' => false, 'message' => 'Review ID is required']);
                exit;
            }
            
            $result = $reviewManager->deleteReview($reviewId, $userId);
            echo json_encode($result);
            break;
            
        case 'get_stats':
            if (!isset($_GET['book_id'])) {
                echo json_encode(['success' => false, 'message' => 'Book ID is required']);
                exit;
            }
            
            $bookId = (int)$_GET['book_id'];
            $result = $reviewManager->getBookReviewStats($bookId);
            echo json_encode($result);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
    
} catch (Exception $e) {
    error_log("Book reviews API error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
}
?>
