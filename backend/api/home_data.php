<?php
/**
 * Home Page Data API
 * Provides dynamic data for the home page including featured books, categories, and reviews
 * 
 * @author BookMarket Team
 * @version 1.0
 */

// Include required files
require_once '../config/database.php';
require_once '../config/session.php';

/**
 * Home Data Manager Class
 * Manages all home page data operations
 */
class HomeDataManager {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
    }
    
    /**
     * Get featured books for home page
     * @param int $limit Number of books to return
     * @return array|false Featured books array or false on failure
     */
    public function getFeaturedBooks($limit = 6) {
        try {
            $books = $this->db->select(
                "SELECT b.*, c.name as category_name, u.username as seller_name, u.city as seller_city
                 FROM books b
                 JOIN categories c ON b.category_id = c.id
                 JOIN users u ON b.seller_id = u.id
                 WHERE b.status = 'approved' AND b.cover_image_path IS NOT NULL
                 ORDER BY RAND()
                 LIMIT ?",
                [intval($limit)]
            );
            
            return $books;
            
        } catch (Exception $e) {
            error_log("Get Featured Books Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get old books (older listings) for home page diversity
     * @param int $limit Number of books to return
     * @return array|false Old books array or false on failure
     */
    public function getOldBooks($limit = 6) {
        try {
            $books = $this->db->select(
                "SELECT b.*, c.name as category_name, u.username as seller_name, u.city as seller_city
                 FROM books b
                 JOIN categories c ON b.category_id = c.id
                 JOIN users u ON b.seller_id = u.id
                 WHERE b.status = 'approved' AND b.cover_image_path IS NOT NULL
                 ORDER BY b.created_at ASC
                 LIMIT ?",
                [intval($limit)]
            );

            return $books;

        } catch (Exception $e) {
            error_log("Get Old Books Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get book categories with counts
     * @return array|false Categories array or false on failure
     */
    public function getCategoriesWithCounts() {
        try {
            $categories = $this->db->select(
                "SELECT c.*, COUNT(b.id) as book_count
                 FROM categories c
                 LEFT JOIN books b ON c.id = b.category_id AND b.status = 'approved'
                 GROUP BY c.id
                 ORDER BY c.name ASC"
            );
            
            return $categories;
            
        } catch (Exception $e) {
            error_log("Get Categories With Counts Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get most rated books
     * @param int $limit Number of books to return
     * @return array|false Most rated books array or false on failure
     */
    public function getMostRatedBooks($limit = 6) {
        try {
            $books = $this->db->select(
                "SELECT b.*, c.name as category_name, u.username as seller_name,
                        AVG(br.rating) as avg_rating, COUNT(br.id) as review_count
                 FROM books b
                 JOIN categories c ON b.category_id = c.id
                 JOIN users u ON b.seller_id = u.id
                 LEFT JOIN book_reviews br ON b.id = br.book_id
                 WHERE b.status = 'approved'
                 GROUP BY b.id
                 HAVING review_count > 0
                 ORDER BY avg_rating DESC, review_count DESC
                 LIMIT ?",
                [intval($limit)]
            );
            
            return $books;
            
        } catch (Exception $e) {
            error_log("Get Most Rated Books Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get newest books
     * @param int $limit Number of books to return
     * @return array|false Newest books array or false on failure
     */
    public function getNewestBooks($limit = 6) {
        try {
            $books = $this->db->select(
                "SELECT b.*, c.name as category_name, u.username as seller_name, u.city as seller_city
                 FROM books b
                 JOIN categories c ON b.category_id = c.id
                 JOIN users u ON b.seller_id = u.id
                 WHERE b.status = 'approved'
                 ORDER BY b.created_at DESC
                 LIMIT ?",
                [intval($limit)]
            );
            
            return $books;
            
        } catch (Exception $e) {
            error_log("Get Newest Books Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get customer reviews for home page
     * @param int $limit Number of reviews to return
     * @return array|false Reviews array or false on failure
     */
    public function getCustomerReviews($limit = 5) {
        try {
            $reviews = $this->db->select(
                "SELECT br.*, b.title as book_title, b.cover_image_path,
                        u.username, u.first_name, u.last_name
                 FROM book_reviews br
                 JOIN books b ON br.book_id = b.id
                 JOIN users u ON br.user_id = u.id
                 WHERE b.status = 'approved'
                 ORDER BY br.created_at DESC
                 LIMIT ?",
                [intval($limit)]
            );
            
            return $reviews;
            
        } catch (Exception $e) {
            error_log("Get Customer Reviews Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get home page statistics
     * @return array|false Statistics array or false on failure
     */
    public function getHomeStats() {
        try {
            $stats = $this->db->selectOne(
                "SELECT 
                    COUNT(DISTINCT b.id) as total_books,
                    COUNT(DISTINCT u.id) as total_users,
                    COUNT(DISTINCT o.id) as total_orders,
                    SUM(CASE WHEN o.order_status IN ('Delivered', 'Shipped') THEN o.total_amount ELSE 0 END) as total_revenue
                 FROM books b
                 CROSS JOIN users u
                 CROSS JOIN orders o
                 WHERE b.status = 'approved'"
            );
            
            if ($stats) {
                $stats['total_books'] = intval($stats['total_books'] ?? 0);
                $stats['total_users'] = intval($stats['total_users'] ?? 0);
                $stats['total_orders'] = intval($stats['total_orders'] ?? 0);
                $stats['total_revenue'] = floatval($stats['total_revenue'] ?? 0);
            }
            
            return $stats;
            
        } catch (Exception $e) {
            error_log("Get Home Stats Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get books by category for home page
     * @param int $categoryId Category ID
     * @param int $limit Number of books to return
     * @return array|false Books array or false on failure
     */
    public function getBooksByCategory($categoryId, $limit = 4) {
        try {
            $books = $this->db->select(
                "SELECT b.*, c.name as category_name, u.username as seller_name, u.city as seller_city
                 FROM books b
                 JOIN categories c ON b.category_id = c.id
                 JOIN users u ON b.seller_id = u.id
                 WHERE b.status = 'approved' AND b.category_id = ?
                 ORDER BY b.created_at DESC
                 LIMIT ?",
                [intval($categoryId), intval($limit)]
            );
            
            return $books;
            
        } catch (Exception $e) {
            error_log("Get Books By Category Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Search books for home page search
     * @param string $searchTerm Search term
     * @param int $limit Number of results to return
     * @return array|false Search results array or false on failure
     */
    public function searchBooks($searchTerm, $limit = 10) {
        try {
            $searchPattern = '%' . cleanInput($searchTerm) . '%';
            
            $books = $this->db->select(
                "SELECT b.*, c.name as category_name, u.username as seller_name, u.city as seller_city
                 FROM books b
                 JOIN categories c ON b.category_id = c.id
                 JOIN users u ON b.seller_id = u.id
                 WHERE b.status = 'approved' 
                 AND (b.title LIKE ? OR b.author LIKE ? OR b.isbn LIKE ? OR c.name LIKE ?)
                 ORDER BY 
                    CASE 
                        WHEN b.title LIKE ? THEN 1
                        WHEN b.author LIKE ? THEN 2
                        WHEN b.isbn LIKE ? THEN 3
                        ELSE 4
                    END,
                    b.views_count DESC
                 LIMIT ?",
                [
                    $searchPattern, $searchPattern, $searchPattern, $searchPattern,
                    $searchPattern, $searchPattern, $searchPattern,
                    intval($limit)
                ]
            );
            
            return $books;
            
        } catch (Exception $e) {
            error_log("Search Books Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get trending books (based on views and recent activity)
     * @param int $limit Number of books to return
     * @return array|false Trending books array or false on failure
     */
    public function getTrendingBooks($limit = 6) {
        try {
            $books = $this->db->select(
                "SELECT b.*, c.name as category_name, u.username as seller_name, u.city as seller_city,
                        (b.views_count * 0.3 + DATEDIFF(NOW(), b.created_at) * -0.1) as trend_score
                 FROM books b
                 JOIN categories c ON b.category_id = c.id
                 JOIN users u ON b.seller_id = u.id
                 WHERE b.status = 'approved'
                 ORDER BY trend_score DESC
                 LIMIT ?",
                [intval($limit)]
            );
            
            return $books;
            
        } catch (Exception $e) {
            error_log("Get Trending Books Error: " . $e->getMessage());
            return false;
        }
    }
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $homeDataManager = new HomeDataManager();
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'get_featured_books':
            $limit = $_POST['limit'] ?? 6;
            $books = $homeDataManager->getFeaturedBooks($limit);
            if ($books !== false) {
                sendSuccessResponse($books, 'Featured books retrieved successfully');
            } else {
                sendErrorResponse('Failed to retrieve featured books');
            }
            break;
        case 'get_old_books':
            $limit = $_POST['limit'] ?? 6;
            $books = $homeDataManager->getOldBooks($limit);
            if ($books !== false) {
                sendSuccessResponse($books, 'Old books retrieved successfully');
            } else {
                sendErrorResponse('Failed to retrieve old books');
            }
            break;
            
        case 'get_categories_with_counts':
            $categories = $homeDataManager->getCategoriesWithCounts();
            if ($categories !== false) {
                sendSuccessResponse($categories, 'Categories retrieved successfully');
            } else {
                sendErrorResponse('Failed to retrieve categories');
            }
            break;
            
        case 'get_most_rated_books':
            $limit = $_POST['limit'] ?? 6;
            $books = $homeDataManager->getMostRatedBooks($limit);
            if ($books !== false) {
                sendSuccessResponse($books, 'Most rated books retrieved successfully');
            } else {
                sendErrorResponse('Failed to retrieve most rated books');
            }
            break;
            
        case 'get_newest_books':
            $limit = $_POST['limit'] ?? 6;
            $books = $homeDataManager->getNewestBooks($limit);
            if ($books !== false) {
                sendSuccessResponse($books, 'Newest books retrieved successfully');
            } else {
                sendErrorResponse('Failed to retrieve newest books');
            }
            break;
            
        case 'get_customer_reviews':
            $limit = $_POST['limit'] ?? 5;
            $reviews = $homeDataManager->getCustomerReviews($limit);
            if ($reviews !== false) {
                sendSuccessResponse($reviews, 'Customer reviews retrieved successfully');
            } else {
                sendErrorResponse('Failed to retrieve customer reviews');
            }
            break;
            
        case 'get_home_stats':
            $stats = $homeDataManager->getHomeStats();
            if ($stats !== false) {
                sendSuccessResponse($stats, 'Home statistics retrieved successfully');
            } else {
                sendErrorResponse('Failed to retrieve home statistics');
            }
            break;
            
        case 'get_books_by_category':
            $categoryId = $_POST['category_id'] ?? 0;
            $limit = $_POST['limit'] ?? 4;
            
            if (!$categoryId) {
                sendErrorResponse('Category ID is required');
            }
            
            $books = $homeDataManager->getBooksByCategory($categoryId, $limit);
            if ($books !== false) {
                sendSuccessResponse($books, 'Category books retrieved successfully');
            } else {
                sendErrorResponse('Failed to retrieve category books');
            }
            break;
            
        case 'search_books':
            $searchTerm = $_POST['search_term'] ?? '';
            $limit = $_POST['limit'] ?? 10;
            
            if (empty($searchTerm)) {
                sendErrorResponse('Search term is required');
            }
            
            $books = $homeDataManager->searchBooks($searchTerm, $limit);
            if ($books !== false) {
                sendSuccessResponse($books, 'Search results retrieved successfully');
            } else {
                sendErrorResponse('Failed to retrieve search results');
            }
            break;
            
        case 'get_trending_books':
            $limit = $_POST['limit'] ?? 6;
            $books = $homeDataManager->getTrendingBooks($limit);
            if ($books !== false) {
                sendSuccessResponse($books, 'Trending books retrieved successfully');
            } else {
                sendErrorResponse('Failed to retrieve trending books');
            }
            break;
            
        default:
            sendErrorResponse('Invalid action', 400);
            break;
    }
}

// Handle GET requests for direct data access
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $homeDataManager = new HomeDataManager();
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'featured_books':
            $limit = $_GET['limit'] ?? 6;
            $books = $homeDataManager->getFeaturedBooks($limit);
            if ($books !== false) {
                sendSuccessResponse($books, 'Featured books retrieved successfully');
            } else {
                sendErrorResponse('Failed to retrieve featured books');
            }
            break;
        case 'old_books':
            $limit = $_GET['limit'] ?? 6;
            $books = $homeDataManager->getOldBooks($limit);
            if ($books !== false) {
                sendSuccessResponse($books, 'Old books retrieved successfully');
            } else {
                sendErrorResponse('Failed to retrieve old books');
            }
            break;
            
        case 'categories':
            $categories = $homeDataManager->getCategoriesWithCounts();
            if ($categories !== false) {
                sendSuccessResponse($categories, 'Categories retrieved successfully');
            } else {
                sendErrorResponse('Failed to retrieve categories');
            }
            break;
            
        case 'home_stats':
            $stats = $homeDataManager->getHomeStats();
            if ($stats !== false) {
                sendSuccessResponse($stats, 'Home statistics retrieved successfully');
            } else {
                sendErrorResponse('Failed to retrieve home statistics');
            }
            break;
            
        default:
            sendErrorResponse('Invalid action', 400);
            break;
    }
}
?>
