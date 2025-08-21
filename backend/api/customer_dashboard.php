<?php
/**
 * Customer Dashboard Data API
 * Provides real-time data for customer dashboard including statistics, orders, and books
 * 
 * @author BookMarket Team
 * @version 1.0
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

class CustomerDashboardManager {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
    }
    
    /**
     * Get dashboard statistics for a customer
     * @param int $userId User ID
     * @return array Dashboard statistics
     */
    public function getDashboardStats($userId) {
        try {
            // Get total purchases (orders)
            $purchasesQuery = "SELECT COUNT(*) as total_purchases, SUM(total_amount) as total_spent 
                              FROM orders 
                              WHERE user_id = ? AND order_status != 'Cancelled'";
            $purchases = $this->db->selectOne($purchasesQuery, [$userId]);
            
            // Get total earnings from sold books
            $earningsQuery = "SELECT COUNT(*) as total_sales, SUM(oi.price_per_item * oi.quantity) as total_earnings
                             FROM order_items oi
                             JOIN orders o ON oi.order_id = o.id
                             JOIN books b ON oi.book_id = b.id
                             WHERE b.seller_id = ? AND o.order_status = 'Delivered'";
            $earnings = $this->db->selectOne($earningsQuery, [$userId]);
            
            // Get total books listed
            $booksQuery = "SELECT COUNT(*) as total_books,
                          COUNT(CASE WHEN status = 'approved' THEN 1 END) as approved_books,
                          COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_books,
                          COUNT(CASE WHEN status = 'rejected' THEN 1 END) as rejected_books,
                          COUNT(CASE WHEN status = 'sold' THEN 1 END) as sold_books
                          FROM books WHERE seller_id = ?";
            $books = $this->db->selectOne($booksQuery, [$userId]);
            
            // Get average seller rating (placeholder for future implementation)
            $rating = 4.8; // This would come from a reviews table
            
            return [
                'success' => true,
                'data' => [
                    'purchases' => [
                        'total' => intval($purchases['total_purchases'] ?? 0),
                        'total_spent' => floatval($purchases['total_spent'] ?? 0)
                    ],
                    'earnings' => [
                        'total_sales' => intval($earnings['total_sales'] ?? 0),
                        'total_earnings' => floatval($earnings['total_earnings'] ?? 0)
                    ],
                    'books' => [
                        'total' => intval($books['total_books'] ?? 0),
                        'approved' => intval($books['approved_books'] ?? 0),
                        'pending' => intval($books['pending_books'] ?? 0),
                        'rejected' => intval($books['rejected_books'] ?? 0),
                        'sold' => intval($books['sold_books'] ?? 0)
                    ],
                    'rating' => $rating
                ]
            ];
            
        } catch (Exception $e) {
            error_log("Dashboard Stats Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to fetch dashboard statistics',
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get recent orders for a customer
     * @param int $userId User ID
     * @param int $limit Number of orders to return
     * @return array Recent orders
     */
    public function getRecentOrders($userId, $limit = 5) {
        try {
            $query = "SELECT o.*, 
                             COUNT(oi.id) as total_items,
                             SUM(oi.quantity) as total_quantity
                      FROM orders o
                      LEFT JOIN order_items oi ON o.id = oi.order_id
                      WHERE o.user_id = ?
                      GROUP BY o.id
                      ORDER BY o.order_date DESC
                      LIMIT ?";
            
            $orders = $this->db->select($query, [$userId, $limit]);
            
            if ($orders === false) {
                // Fallback query without GROUP BY
                $orders = $this->db->select(
                    "SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC LIMIT ?",
                    [$userId, $limit]
                );
            }
            
            return [
                'success' => true,
                'data' => $orders ?: []
            ];
            
        } catch (Exception $e) {
            error_log("Recent Orders Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to fetch recent orders',
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get recent books for a customer
     * @param int $userId User ID
     * @param int $limit Number of books to return
     * @return array Recent books
     */
    public function getRecentBooks($userId, $limit = 4) {
        try {
            $query = "SELECT b.*, c.name as category_name
                      FROM books b
                      JOIN categories c ON b.category_id = c.id
                      WHERE b.seller_id = ?
                      ORDER BY b.created_at DESC
                      LIMIT ?";
            
            $books = $this->db->select($query, [$userId, $limit]);
            
            return [
                'success' => true,
                'data' => $books ?: []
            ];
            
        } catch (Exception $e) {
            error_log("Recent Books Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to fetch recent books',
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get sales overview data for charts
     * @param int $userId User ID
     * @param int $months Number of months to look back
     * @return array Sales data for charts
     */
    public function getSalesOverview($userId, $months = 6) {
        try {
            $query = "SELECT 
                        DATE_FORMAT(o.order_date, '%Y-%m') as month,
                        COUNT(DISTINCT o.id) as total_orders,
                        SUM(oi.price_per_item * oi.quantity) as total_revenue
                      FROM orders o
                      JOIN order_items oi ON o.id = oi.order_id
                      JOIN books b ON oi.book_id = b.id
                      WHERE b.seller_id = ? 
                        AND o.order_status = 'Delivered'
                        AND o.order_date >= DATE_SUB(NOW(), INTERVAL ? MONTH)
                      GROUP BY DATE_FORMAT(o.order_date, '%Y-%m')
                      ORDER BY month ASC";
            
            $salesData = $this->db->select($query, [$userId, $months]);
            
            return [
                'success' => true,
                'data' => $salesData ?: []
            ];
            
        } catch (Exception $e) {
            error_log("Sales Overview Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to fetch sales overview',
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get all dashboard data in one call
     * @param int $userId User ID
     * @return array Complete dashboard data
     */
    public function getAllDashboardData($userId) {
        try {
            $stats = $this->getDashboardStats($userId);
            $recentOrders = $this->getRecentOrders($userId, 5);
            $recentBooks = $this->getRecentBooks($userId, 4);
            $salesOverview = $this->getSalesOverview($userId, 6);
            
            return [
                'success' => true,
                'data' => [
                    'stats' => $stats['data'] ?? [],
                    'recent_orders' => $recentOrders['data'] ?? [],
                    'recent_books' => $recentBooks['data'] ?? [],
                    'sales_overview' => $salesOverview['data'] ?? []
                ]
            ];
            
        } catch (Exception $e) {
            error_log("All Dashboard Data Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to fetch dashboard data',
                'error' => $e->getMessage()
            ];
        }
    }
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $dashboardManager = new CustomerDashboardManager();
    
    // Check if user is logged in
    if (!isLoggedIn()) {
        echo json_encode([
            'success' => false,
            'message' => 'User not logged in',
            'data' => null
        ]);
        exit;
    }
    
    $userId = getCurrentUserId();
    
    switch ($action) {
        case 'get_dashboard_data':
            $result = $dashboardManager->getAllDashboardData($userId);
            echo json_encode($result);
            break;
            
        case 'get_stats':
            $result = $dashboardManager->getDashboardStats($userId);
            echo json_encode($result);
            break;
            
        case 'get_recent_orders':
            $limit = intval($_POST['limit'] ?? 5);
            $result = $dashboardManager->getRecentOrders($userId, $limit);
            echo json_encode($result);
            break;
            
        case 'get_recent_books':
            $limit = intval($_POST['limit'] ?? 4);
            $result = $dashboardManager->getRecentBooks($userId, $limit);
            echo json_encode($result);
            break;
            
        case 'get_sales_overview':
            $months = intval($_POST['months'] ?? 6);
            $result = $dashboardManager->getSalesOverview($userId, $months);
            echo json_encode($result);
            break;
            
        default:
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action',
                'data' => null
            ]);
            break;
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Only POST method allowed',
        'data' => null
    ]);
}
?>
