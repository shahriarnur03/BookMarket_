<?php
/**
 * Sales Reports API
 * Provides sales data for customer sales reports including statistics and detailed sales
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

class SalesReportsManager {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
    }
    
    /**
     * Get sales data for a customer
     * @param int $userId User ID
     * @return array Sales data including stats and detailed sales
     */
    public function getSalesData($userId) {
        try {
            // Get total books sold
            $booksSoldQuery = "SELECT COUNT(*) as total_books_sold
                               FROM order_items oi
                               JOIN orders o ON oi.order_id = o.id
                               JOIN books b ON oi.book_id = b.id
                               WHERE b.seller_id = ? AND o.order_status = 'Delivered'";
            $booksSold = $this->db->selectOne($booksSoldQuery, [$userId]);
            
            // Get total earnings
            $earningsQuery = "SELECT SUM(oi.price_per_item * oi.quantity) as total_earnings
                             FROM order_items oi
                             JOIN orders o ON oi.order_id = o.id
                             JOIN books b ON oi.book_id = b.id
                             WHERE b.seller_id = ? AND o.order_status = 'Delivered'";
            $earnings = $this->db->selectOne($earningsQuery, [$userId]);
            
            // Get total commission (5% of total earnings)
            $totalCommission = ($earnings['total_earnings'] ?? 0) * 0.05;
            
            // Get detailed sales data
            $salesQuery = "SELECT 
                            oi.id,
                            oi.order_id,
                            oi.book_id,
                            oi.price_per_item,
                            oi.quantity,
                            o.order_number,
                            o.order_date,
                            o.order_status,
                            b.title as book_title,
                            b.author as book_author,
                            u.username as buyer_name
                           FROM order_items oi
                           JOIN orders o ON oi.order_id = o.id
                           JOIN books b ON oi.book_id = b.id
                           JOIN users u ON o.user_id = u.id
                           WHERE b.seller_id = ? AND o.order_status = 'Delivered'
                           ORDER BY o.order_date DESC";
            $sales = $this->db->select($salesQuery, [$userId]);
            
            return [
                'success' => true,
                'data' => [
                    'total_books_sold' => intval($booksSold['total_books_sold'] ?? 0),
                    'total_earnings' => floatval($earnings['total_earnings'] ?? 0),
                    'total_commission' => $totalCommission,
                    'sales' => $sales ?: []
                ]
            ];
            
        } catch (Exception $e) {
            error_log("Sales Data Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to fetch sales data',
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Export sales report as CSV
     * @param int $userId User ID
     * @param array $filters Filter criteria
     * @return string CSV data
     */
    public function exportSalesReport($userId, $filters = []) {
        try {
            // Get sales data with filters
            $salesData = $this->getSalesData($userId);
            if (!$salesData['success']) {
                throw new Exception('Failed to get sales data');
            }
            
            $sales = $salesData['data']['sales'];
            
            // Apply filters if provided
            if (!empty($filters)) {
                $sales = $this->applyExportFilters($sales, $filters);
            }
            
            // Generate CSV
            $csv = "Date,Book Title,Order ID,Buyer,Price,Commission,Earnings\n";
            
            foreach ($sales as $sale) {
                $saleDate = date('Y-m-d', strtotime($sale['order_date']));
                $price = floatval($sale['price_per_item']);
                $commission = $price * 0.05;
                $earnings = $price - $commission;
                
                $csv .= sprintf(
                    "%s,%s,%s,%s,%.2f,%.2f,%.2f\n",
                    $saleDate,
                    $this->escapeCsv($sale['book_title'] ?? 'N/A'),
                    $sale['order_number'] ?? $sale['id'],
                    $this->escapeCsv($sale['buyer_name'] ?? 'Customer'),
                    $price,
                    $commission,
                    $earnings
                );
            }
            
            return $csv;
            
        } catch (Exception $e) {
            error_log("Export Sales Report Error: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Apply filters to sales data for export
     * @param array $sales Sales data
     * @param array $filters Filter criteria
     * @return array Filtered sales data
     */
    private function applyExportFilters($sales, $filters) {
        if (empty($filters)) return $sales;
        
        $filtered = $sales;
        
        // Apply date range filter
        if (isset($filters['dateRange']) && $filters['dateRange'] !== 'all-time') {
            $now = new DateTime();
            $startDate = new DateTime();
            
            switch ($filters['dateRange']) {
                case 'this-month':
                    $startDate = new DateTime('first day of this month');
                    break;
                case 'last-month':
                    $startDate = new DateTime('first day of last month');
                    break;
                case 'last-3-months':
                    $startDate->modify('-3 months');
                    break;
                case 'last-6-months':
                    $startDate->modify('-6 months');
                    break;
                case 'this-year':
                    $startDate = new DateTime('first day of january this year');
                    break;
                case 'custom':
                    if (!empty($filters['customDateFrom'])) {
                        $startDate = new DateTime($filters['customDateFrom']);
                    }
                    break;
            }
            
            $filtered = array_filter($filtered, function($sale) use ($startDate) {
                $saleDate = new DateTime($sale['order_date']);
                return $saleDate >= $startDate;
            });
        }
        
        // Apply book filter
        if (isset($filters['bookFilter']) && $filters['bookFilter'] !== 'all') {
            $filtered = array_filter($filtered, function($sale) use ($filters) {
                return $sale['book_id'] == $filters['bookFilter'];
            });
        }
        
        return array_values($filtered);
    }
    
    /**
     * Escape CSV values
     * @param string $value Value to escape
     * @return string Escaped value
     */
    private function escapeCsv($value) {
        if (strpos($value, ',') !== false || strpos($value, '"') !== false || strpos($value, "\n") !== false) {
            return '"' . str_replace('"', '""', $value) . '"';
        }
        return $value;
    }
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $salesManager = new SalesReportsManager();
    
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
        case 'get_sales_data':
            $result = $salesManager->getSalesData($userId);
            echo json_encode($result);
            break;
            
        case 'export_sales_report':
            try {
                $filters = json_decode($_POST['filters'] ?? '{}', true);
                $csvData = $salesManager->exportSalesReport($userId, $filters);
                
                // Set headers for CSV download
                header('Content-Type: text/csv');
                header('Content-Disposition: attachment; filename="sales_report_' . date('Y-m-d') . '.csv"');
                header('Content-Length: ' . strlen($csvData));
                
                echo $csvData;
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Export failed: ' . $e->getMessage()
                ]);
            }
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
