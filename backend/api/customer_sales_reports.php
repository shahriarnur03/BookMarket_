<?php
session_start();
require_once '../config/database.php';
require_once '../config/session.php';

// Check if user is logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please log in to access this resource']);
    exit;
}

// Get current user ID
$userId = getCurrentUserId();

// Get request parameters
$action = $_GET['action'] ?? $_POST['action'] ?? '';
$startDate = $_GET['start_date'] ?? $_POST['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? $_POST['end_date'] ?? '';
$format = $_GET['format'] ?? $_POST['format'] ?? 'csv';

// Validate dates
if (empty($startDate) || empty($endDate)) {
    echo json_encode(['success' => false, 'message' => 'Start date and end date are required']);
    exit;
}

try {
    switch ($action) {
        case 'kpi':
            $data = getCustomerSalesKPI($userId, $startDate, $endDate);
            echo json_encode(['success' => true, 'data' => $data]);
            break;
            
        case 'detailed_sales':
            $data = getCustomerDetailedSales($userId, $startDate, $endDate);
            echo json_encode(['success' => true, 'data' => $data]);
            break;
            
        case 'export':
            exportCustomerReport($userId, $startDate, $endDate, $format);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    error_log('Customer Sales Reports Error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred while processing the request']);
}

function getCustomerSalesKPI($userId, $startDate, $endDate) {
    $db = getDB();
    
    // Debug: Log the query parameters
    error_log("Customer Sales KPI Query - User ID: $userId, Start Date: $startDate, End Date: $endDate");
    
    $sql = "SELECT 
                COUNT(DISTINCT o.id) as total_orders,
                SUM(oi.quantity) as total_books_sold,
                SUM(oi.quantity * oi.price_per_item) as total_sales,
                AVG(oi.quantity * oi.price_per_item) as avg_order_value
            FROM orders o
            JOIN order_items oi ON o.id = oi.order_id
            JOIN books b ON oi.book_id = b.id
            WHERE b.seller_id = ? 
            AND o.order_date BETWEEN ? AND ?
            AND o.order_status IN ('Delivered', 'Shipped', 'Processing')";
    
    $result = $db->selectOne($sql, [$userId, $startDate, $endDate]);
    
    // Debug: Log the result
    error_log("Customer Sales KPI Result: " . json_encode($result));
    
    if (!$result) {
        return [
            'total_orders' => 0,
            'total_books_sold' => 0,
            'total_sales' => 0.0,
            'avg_order_value' => 0.0
        ];
    }
    
    return [
        'total_orders' => (int)$result['total_orders'],
        'total_books_sold' => (int)$result['total_books_sold'],
        'total_sales' => (float)$result['total_sales'],
        'avg_order_value' => (float)$result['avg_order_value']
    ];
}

function getCustomerDetailedSales($userId, $startDate, $endDate) {
    $db = getDB();
    
    // Debug: Log the query parameters
    error_log("Customer Detailed Sales Query - User ID: $userId, Start Date: $startDate, End Date: $endDate");
    
    $sql = "SELECT 
                o.id as order_id,
                o.order_number,
                o.order_date,
                o.order_status,
                b.title as book_title,
                b.author as book_author,
                oi.quantity,
                oi.price_per_item,
                (oi.quantity * oi.price_per_item) as total_amount,
                u.first_name,
                u.last_name,
                u.username
            FROM orders o
            JOIN order_items oi ON o.id = oi.order_id
            JOIN books b ON oi.book_id = b.id
            JOIN users u ON o.user_id = u.id
            WHERE b.seller_id = ? 
            AND o.order_date BETWEEN ? AND ?
            AND o.order_status IN ('Delivered', 'Shipped', 'Processing')
            ORDER BY o.order_date DESC";
    
    $result = $db->select($sql, [$userId, $startDate, $endDate]);
    
    // Debug: Log the result
    error_log("Customer Detailed Sales Result: " . json_encode($result));
    
    if (!$result) {
        return [];
    }
    
    $sales = [];
    foreach ($result as $row) {
        $sales[] = [
            'order_id' => $row['order_id'],
            'order_number' => $row['order_number'],
            'order_date' => $row['order_date'],
            'order_status' => $row['order_status'],
            'book_title' => $row['book_title'],
            'book_author' => $row['book_author'],
            'quantity' => (int)$row['quantity'],
            'price_per_item' => (float)$row['price_per_item'],
            'total_amount' => (float)$row['total_amount'],
            'customer_name' => $row['first_name'] . ' ' . $row['last_name'],
            'customer_username' => $row['username']
        ];
    }
    
    return $sales;
}

function exportCustomerReport($userId, $startDate, $endDate, $format) {
    // Get KPI data for export
    $kpiData = getCustomerSalesKPI($userId, $startDate, $endDate);
    $detailedSales = getCustomerDetailedSales($userId, $startDate, $endDate);
    
    switch ($format) {
        case 'csv':
            exportAsCSV($startDate, $endDate, $kpiData, $detailedSales);
            break;
        case 'excel':
            exportAsExcel($startDate, $endDate, $kpiData, $detailedSales);
            break;
        case 'pdf':
            exportAsPDF($startDate, $endDate, $kpiData, $detailedSales);
            break;
        default:
            exportAsCSV($startDate, $endDate, $kpiData, $detailedSales);
    }
}

function exportAsCSV($startDate, $endDate, $kpiData, $detailedSales) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="customer_sales_report_' . $startDate . '_to_' . $endDate . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // Report Header
    fputcsv($output, ['CUSTOMER SALES REPORT SUMMARY']);
    fputcsv($output, ['Period:', $startDate, 'to', $endDate]);
    fputcsv($output, []);
    
    // KPI Summary Table
    fputcsv($output, ['Metric', 'Value', 'Calculation', 'Description']);
    fputcsv($output, [
        'Total Orders',
        $kpiData['total_orders'],
        'Count of completed orders',
        'Number of successful transactions'
    ]);
    fputcsv($output, [
        'Total Books Sold',
        $kpiData['total_books_sold'],
        'Sum of all book quantities',
        'Total units sold across all orders'
    ]);
    fputcsv($output, [
        'Total Sales',
        $kpiData['total_sales'],
        'Sum of all order values',
        'Total sales amount for the period'
    ]);
    fputcsv($output, [
        'Average Order Value',
        $kpiData['avg_order_value'],
        'Total Sales ÷ Total Orders',
        'Average amount spent per order'
    ]);
    
    // Calculate additional metrics
    $totalSales = $kpiData['total_sales'];
    $totalOrders = $kpiData['total_orders'];
    $totalBooks = $kpiData['total_books_sold'];
    
    if ($totalOrders > 0) {
        $booksPerOrder = $totalBooks / $totalOrders;
        fputcsv($output, [
            'Books Per Order',
            round($booksPerOrder, 1),
            'Total Books ÷ Total Orders',
            'Average number of books per transaction'
        ]);
    }
    
    // Calculate daily averages
    $startDateObj = new DateTime($startDate);
    $endDateObj = new DateTime($endDate);
    $daysDiff = $startDateObj->diff($endDateObj)->days + 1;
    
    if ($daysDiff > 0) {
        $dailySales = $totalSales / $daysDiff;
        $dailyOrders = $totalOrders / $daysDiff;
        
        fputcsv($output, [
            'Daily Average Sales',
            round($dailySales, 0),
            "Total Sales ÷ {$daysDiff} days",
            'Average daily sales performance'
        ]);
        fputcsv($output, [
            'Daily Average Orders',
            round($dailyOrders, 1),
            "Total Orders ÷ {$daysDiff} days",
            'Average daily order volume'
        ]);
    }
    
    // Calculate commission (assuming 5% commission)
    $commission = $totalSales * 0.05;
    $commissionRate = 5.0;
    
    fputcsv($output, [
        'Commission Earned',
        round($commission, 0),
        "Total Sales × {$commissionRate}%",
        'Commission earned from sales'
    ]);
    
    fputcsv($output, []);
    
    // Detailed Sales Table
    fputcsv($output, ['DETAILED SALES BREAKDOWN']);
    fputcsv($output, [
        'Order ID', 'Order Number', 'Date', 'Status', 'Book Title', 
        'Author', 'Quantity', 'Price Per Item', 'Total Amount', 'Customer'
    ]);
    
    foreach ($detailedSales as $sale) {
        fputcsv($output, [
            $sale['order_id'],
            $sale['order_number'],
            $sale['order_date'],
            $sale['order_status'],
            $sale['book_title'],
            $sale['book_author'],
            $sale['quantity'],
            $sale['price_per_item'],
            $sale['total_amount'],
            $sale['customer_name']
        ]);
    }
    
    fclose($output);
}

function exportAsExcel($startDate, $endDate, $kpiData, $detailedSales) {
    // For Excel export, we'll create a CSV with Excel-compatible formatting
    // In a production environment, you might want to use a library like PhpSpreadsheet
    exportAsCSV($startDate, $endDate, $kpiData, $detailedSales);
}

function exportAsPDF($startDate, $endDate, $kpiData, $detailedSales) {
    // For PDF export, we'll create a CSV for now
    // In a production environment, you might want to use a library like TCPDF or FPDF
    exportAsCSV($startDate, $endDate, $kpiData, $detailedSales);
}
?>
