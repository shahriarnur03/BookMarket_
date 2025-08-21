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

class BrowseBooksManager {
    private $db;

    public function __construct() {
        $this->db = getDB();
    }

    /**
     * Get books with filters, search, and pagination
     */
    public function getBooks($filters = [], $page = 1, $limit = 12) {
        try {
            $offset = ($page - 1) * $limit;
            
            // Build the base query
            $sql = "SELECT 
                        b.id,
                        b.title,
                        b.author,
                        b.isbn,
                        b.description,
                        b.price,
                        b.book_condition as book_condition,
                        b.cover_image_path,
                        b.status,
                        b.created_at,
                        c.name as category_name,
                        u.username as seller_name
                    FROM books b
                    LEFT JOIN categories c ON b.category_id = c.id
                    LEFT JOIN users u ON b.seller_id = u.id
                    WHERE b.status = 'approved'";
            
            $params = [];
            $whereConditions = [];

            // Add search filter
            if (!empty($filters['search'])) {
                $searchTerm = '%' . $filters['search'] . '%';
                $whereConditions[] = "(b.title LIKE ? OR b.author LIKE ? OR b.isbn LIKE ? OR b.description LIKE ?)";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }

            // Add category filter
            if (!empty($filters['category'])) {
                $whereConditions[] = "c.name = ?";
                $params[] = $filters['category'];
            }

            // Add condition filter
            if (!empty($filters['condition'])) {
                $whereConditions[] = "b.book_condition = ?";
                $params[] = $filters['condition'];
            }

            // Add price range filter
            if (!empty($filters['min_price']) && is_numeric($filters['min_price'])) {
                $whereConditions[] = "b.price >= ?";
                $params[] = floatval($filters['min_price']);
            }

            if (!empty($filters['max_price']) && is_numeric($filters['max_price'])) {
                $whereConditions[] = "b.price <= ?";
                $params[] = floatval($filters['max_price']);
            }

            // Add WHERE conditions if any
            if (!empty($whereConditions)) {
                $sql .= " AND " . implode(" AND ", $whereConditions);
            }

            // Add sorting
            $sql .= $this->buildSortClause($filters['sort_by'] ?? 'newest');

            // Get total count for pagination
            $countSql = "SELECT COUNT(*) as total FROM books b 
                        LEFT JOIN categories c ON b.category_id = c.id 
                        LEFT JOIN users u ON b.seller_id = u.id 
                        WHERE b.status = 'approved'";
            
            if (!empty($whereConditions)) {
                $countSql .= " AND " . implode(" AND ", $whereConditions);
            }

            $countResult = $this->db->select($countSql, $params);
            $totalCount = $countResult[0]['total'] ?? 0;

            // Add pagination
            $sql .= " LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;

            // Execute the main query
            $books = $this->db->select($sql, $params);

            // Calculate pagination info
            $totalPages = ceil($totalCount / $limit);
            $currentPage = $page;

            return [
                'success' => true,
                'data' => $books,
                'pagination' => [
                    'current_page' => $currentPage,
                    'total_pages' => $totalPages,
                    'total_books' => $totalCount,
                    'per_page' => $limit,
                    'showing_from' => $offset + 1,
                    'showing_to' => min($offset + $limit, $totalCount)
                ]
            ];

        } catch (Exception $e) {
            error_log("Browse Books Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to fetch books',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get all available categories
     */
    public function getCategories() {
        try {
            $sql = "SELECT id, name, description FROM categories ORDER BY name";
            return $this->db->select($sql);
        } catch (Exception $e) {
            error_log("Get Categories Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all available book conditions
     */
    public function getConditions() {
        return [
            ['value' => 'new', 'label' => 'New'],
            ['value' => 'excellent', 'label' => 'Excellent'],
            ['value' => 'good', 'label' => 'Good'],
            ['value' => 'fair', 'label' => 'Fair'],
            ['value' => 'poor', 'label' => 'Poor']
        ];
    }

    /**
     * Build ORDER BY clause based on sort option
     */
    private function buildSortClause($sortBy) {
        switch ($sortBy) {
            case 'a-z':
                return " ORDER BY b.title ASC";
            case 'z-a':
                return " ORDER BY b.title DESC";
            case 'price-low':
                return " ORDER BY b.price ASC";
            case 'price-high':
                return " ORDER BY b.price DESC";
            case 'newest':
            default:
                return " ORDER BY b.created_at DESC";
        }
    }
}

// Handle the request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $browseManager = new BrowseBooksManager();

    switch ($action) {
        case 'get_books':
            $filters = [
                'search' => $_POST['search'] ?? '',
                'category' => $_POST['category'] ?? '',
                'condition' => $_POST['condition'] ?? '',
                'min_price' => $_POST['min_price'] ?? '',
                'max_price' => $_POST['max_price'] ?? '',
                'sort_by' => $_POST['sort_by'] ?? 'newest'
            ];
            $page = intval($_POST['page'] ?? 1);
            $limit = intval($_POST['limit'] ?? 12);
            
            $result = $browseManager->getBooks($filters, $page, $limit);
            echo json_encode($result);
            break;

        case 'get_categories':
            $categories = $browseManager->getCategories();
            echo json_encode([
                'success' => true,
                'data' => $categories
            ]);
            break;

        case 'get_conditions':
            $conditions = $browseManager->getConditions();
            echo json_encode([
                'success' => true,
                'data' => $conditions
            ]);
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
