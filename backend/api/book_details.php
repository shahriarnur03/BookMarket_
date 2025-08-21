<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

class BookDetailsManager {
    private $db;

    public function __construct() {
        $this->db = getDB();
    }

    /**
     * Get book details by ID
     */
    public function getBookDetails($bookId) {
        try {
            $sql = "SELECT 
                        b.id,
                        b.title,
                        b.author,
                        b.isbn,
                        b.description,
                        b.price,
                        b.book_condition,
                        b.stock_quantity,
                        b.cover_image_path,
                        b.additional_images,
                        b.status,
                        b.created_at,
                        b.updated_at,
                        c.name as category_name,
                        c.description as category_description,
                        u.username as seller_name,
                        u.email as seller_email
                    FROM books b
                    LEFT JOIN categories c ON b.category_id = c.id
                    LEFT JOIN users u ON b.seller_id = u.id
                    WHERE b.id = ? AND b.status = 'approved'";
            
            $result = $this->db->select($sql, [$bookId]);
            
            if (empty($result)) {
                return [
                    'success' => false,
                    'message' => 'Book not found or not approved'
                ];
            }

            $book = $result[0];

            // Get related books (same category, excluding current book)
            $relatedBooks = $this->getRelatedBooks($book['category_id'], $bookId);

            // Get book reviews/ratings (if you have a reviews table)
            $reviews = $this->getBookReviews($bookId);

            return [
                'success' => true,
                'data' => [
                    'book' => $book,
                    'related_books' => $relatedBooks,
                    'reviews' => $reviews
                ]
            ];

        } catch (Exception $e) {
            error_log("Book Details Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to fetch book details',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get related books from the same category
     */
    private function getRelatedBooks($categoryId, $excludeBookId, $limit = 4) {
        try {
            $sql = "SELECT 
                        b.id,
                        b.title,
                        b.author,
                        b.price,
                        b.book_condition,
                        b.cover_image_path
                    FROM books b
                    WHERE b.category_id = ? 
                    AND b.id != ? 
                    AND b.status = 'approved'
                    ORDER BY b.created_at DESC
                    LIMIT ?";
            
            return $this->db->select($sql, [$categoryId, $excludeBookId, $limit]);
        } catch (Exception $e) {
            error_log("Related Books Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get book reviews (placeholder for future implementation)
     */
    private function getBookReviews($bookId) {
        // This is a placeholder - you can implement reviews later
        return [
            'average_rating' => 4.5,
            'total_reviews' => 12,
            'reviews' => []
        ];
    }
}

// Handle the request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $bookDetailsManager = new BookDetailsManager();

    switch ($action) {
        case 'get_book_details':
            $bookId = intval($_POST['book_id'] ?? 0);
            
            if ($bookId <= 0) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid book ID'
                ]);
                exit;
            }
            
            $result = $bookDetailsManager->getBookDetails($bookId);
            echo json_encode($result);
            break;

        default:
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action'
            ]);
            break;
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Only POST method allowed'
    ]);
}
?>
