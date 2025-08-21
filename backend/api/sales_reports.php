<?php
session_start();
require_once '../config/database.php';
require_once '../config/session.php';

// Check if user is logged in and is admin
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please log in to access this resource']);
    exit;
}

if (!isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Admin access required']);
    exit;
}

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
            $data = getSalesKPI($startDate, $endDate);
            echo json_encode(['success' => true, 'data' => $data]);
            break;
            
        case 'export':
            exportReport($startDate, $endDate, $format);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    error_log('Sales Reports Error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred while processing the request']);
}

function getSalesKPI($startDate, $endDate) {
    global $conn;
    
    $sql = "SELECT 
                COUNT(DISTINCT o.id) as total_orders,
                SUM(oi.quantity) as total_books_sold,
                SUM(oi.quantity * oi.price) as total_sales,
                AVG(oi.quantity * oi.price) as avg_order_value
            FROM orders o
            JOIN order_items oi ON o.id = oi.order_id
            WHERE o.order_date BETWEEN ? AND ?
            AND o.status = 'completed'";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ss', $startDate, $endDate);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    
    return [
        'total_orders' => (int)$data['total_orders'],
        'total_books_sold' => (int)$data['total_books_sold'],
        'total_sales' => (float)$data['total_sales'],
        'avg_order_value' => (float)$data['avg_order_value']
    ];
}

function exportReport($startDate, $endDate, $format) {
    // Get KPI data for export
    $kpiData = getSalesKPI($startDate, $endDate);
    
    switch ($format) {
        case 'csv':
            exportAsCSV($startDate, $endDate, $kpiData);
            break;
        case 'excel':
            exportAsExcel($startDate, $endDate, $kpiData);
            break;
        case 'pdf':
            exportAsPDF($startDate, $endDate, $kpiData);
            break;
        default:
            exportAsCSV($startDate, $endDate, $kpiData);
    }
}

function exportAsCSV($startDate, $endDate, $kpiData) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="sales_report_' . $startDate . '_to_' . $endDate . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // Report Header
    fputcsv($output, ['SALES REPORT SUMMARY']);
    fputcsv($output, ['Period:', $startDate, 'to', $endDate]);
    fputcsv($output, []);
    
    // KPI Summary Table
    fputcsv($output, ['Metric', 'Value', 'Calculation', 'Description']);
    fputcsv($output, [
        'Total Revenue',
        $kpiData['total_sales'],
        'Sum of all order values',
        'Total sales amount for the period'
    ]);
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
        'Average Order Value',
        $kpiData['avg_order_value'],
        'Total Revenue ÷ Total Orders',
        'Average amount spent per order'
    ]);
    
    // Calculate additional metrics
    $totalRevenue = $kpiData['total_sales'];
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
        $dailyRevenue = $totalRevenue / $daysDiff;
        $dailyOrders = $totalOrders / $daysDiff;
        
        fputcsv($output, [
            'Daily Average Revenue',
            round($dailyRevenue, 0),
            "Total Revenue ÷ {$daysDiff} days",
            'Average daily sales performance'
        ]);
        fputcsv($output, [
            'Daily Average Orders',
            round($dailyOrders, 1),
            "Total Orders ÷ {$daysDiff} days",
            'Average daily order volume'
        ]);
    }
    
    // Calculate profit estimates
    $estimatedProfit = $totalRevenue * 0.3;
    $profitMargin = $totalRevenue > 0 ? ($estimatedProfit / $totalRevenue) * 100 : 0;
    
    fputcsv($output, [
        'Estimated Profit',
        round($estimatedProfit, 0),
        'Total Revenue × 30%',
        'Estimated profit based on 30% margin'
    ]);
    fputcsv($output, [
        'Profit Margin',
        round($profitMargin, 1) . '%',
        '(Estimated Profit ÷ Total Revenue) × 100',
        'Profit as percentage of revenue'
    ]);
    
    fclose($output);
}

function exportAsExcel($startDate, $endDate, $kpiData) {
    // For Excel export, we'll create a CSV with Excel-compatible formatting
    // In a production environment, you might want to use a library like PhpSpreadsheet
    exportAsCSV($startDate, $endDate, $kpiData);
}

function exportAsPDF($startDate, $endDate, $kpiData) {
    // For PDF export, we'll create a CSV for now
    // In a production environment, you might want to use a library like TCPDF or FPDF
    exportAsCSV($startDate, $endDate, $kpiData);
}
?>
