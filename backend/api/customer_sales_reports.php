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
        case 'html':
            exportAsHTML($startDate, $endDate, $kpiData, $detailedSales);
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

function exportAsHTML($startDate, $endDate, $kpiData, $detailedSales) {
    header('Content-Type: text/html');
    header('Content-Disposition: attachment; filename="customer_sales_report_' . $startDate . '_to_' . $endDate . '.html"');
    
    // Calculate additional metrics
    $totalSales = $kpiData['total_sales'];
    $totalOrders = $kpiData['total_orders'];
    $totalBooks = $kpiData['total_books_sold'];
    
    $booksPerOrder = $totalOrders > 0 ? $totalBooks / $totalOrders : 0;
    $commission = $totalSales * 0.05;
    
    // Calculate daily averages
    $startDateObj = new DateTime($startDate);
    $endDateObj = new DateTime($endDate);
    $daysDiff = $startDateObj->diff($endDateObj)->days + 1;
    $dailySales = $daysDiff > 0 ? $totalSales / $daysDiff : 0;
    $dailyOrders = $daysDiff > 0 ? $totalOrders / $daysDiff : 0;
    
    echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Sales Report - BookMarket</title>
    <style>
        @page { margin: 1in; }
        body { 
            font-family: "Times New Roman", serif; 
            margin: 0; 
            padding: 0; 
            background: white; 
            color: #333;
            line-height: 1.4;
        }
        .container { 
            max-width: 8.5in; 
            margin: 0 auto; 
            background: white; 
            padding: 0.5in;
        }
        .header { 
            text-align: center; 
            margin-bottom: 0.5in; 
            padding-bottom: 0.3in; 
            border-bottom: 2px solid #000;
        }
        .header h1 { 
            color: #000; 
            margin: 0 0 0.2in 0; 
            font-size: 24pt;
            font-weight: bold;
        }
        .header p { 
            color: #333; 
            margin: 0.1in 0; 
            font-size: 12pt;
        }
        .report-info {
            margin-bottom: 0.4in;
            padding: 0.2in;
            background: #f9f9f9;
            border: 1px solid #ddd;
        }
        .report-info table {
            width: 100%;
            border-collapse: collapse;
        }
        .report-info td {
            padding: 0.1in;
            border: none;
            vertical-align: top;
        }
        .report-info .label {
            font-weight: bold;
            width: 30%;
            color: #000;
        }
        .summary-section {
            margin-bottom: 0.4in;
        }
        .summary-section h2 {
            color: #000;
            margin: 0 0 0.2in 0;
            font-size: 16pt;
            font-weight: bold;
            border-bottom: 1px solid #000;
            padding-bottom: 0.1in;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0.3in;
        }
        .summary-table th,
        .summary-table td {
            padding: 0.15in;
            text-align: left;
            border: 1px solid #000;
            font-size: 11pt;
        }
        .summary-table th {
            background-color: #f0f0f0;
            font-weight: bold;
            color: #000;
        }
        .summary-table .metric-name {
            font-weight: bold;
            background-color: #f9f9f9;
        }
        .summary-table .value {
            text-align: center;
            font-weight: bold;
        }
        .summary-table .description {
            font-style: italic;
            color: #666;
        }
        .detailed-section {
            margin-bottom: 0.4in;
        }
        .detailed-section h2 {
            color: #000;
            margin: 0 0 0.2in 0;
            font-size: 16pt;
            font-weight: bold;
            border-bottom: 1px solid #000;
            padding-bottom: 0.1in;
        }
        .detailed-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
        }
        .detailed-table th,
        .detailed-table td {
            padding: 0.1in;
            text-align: left;
            border: 1px solid #000;
        }
        .detailed-table th {
            background-color: #f0f0f0;
            font-weight: bold;
            color: #000;
        }
        .footer {
            margin-top: 0.5in;
            padding-top: 0.3in;
            border-top: 1px solid #000;
            text-align: center;
            font-size: 10pt;
            color: #666;
        }
        .footer .company {
            font-weight: bold;
            color: #000;
            margin-bottom: 0.1in;
        }
        @media print {
            body { background: white; }
            .container { box-shadow: none; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>CUSTOMER SALES REPORT</h1>
            <p>BookMarket - Professional Book Marketplace</p>
            <p>Period: ' . $startDate . ' to ' . $endDate . '</p>
            <p>Generated on: ' . date('F j, Y') . ' at ' . date('g:i A') . '</p>
        </div>
        
        <div class="report-info">
            <table>
                <tr>
                    <td class="label">Report Type:</td>
                    <td>Customer Sales Performance Analysis</td>
                    <td class="label">Total Orders:</td>
                    <td>' . $kpiData['total_orders'] . '</td>
                </tr>
                <tr>
                    <td class="label">Report Period:</td>
                    <td>' . $startDate . ' to ' . $endDate . '</td>
                    <td class="label">Total Books Sold:</td>
                    <td>' . $kpiData['total_books_sold'] . '</td>
                </tr>
                <tr>
                    <td class="label">Generated By:</td>
                    <td>Customer Account</td>
                    <td class="label">Total Sales Value:</td>
                    <td>৳' . number_format($kpiData['total_sales'], 0) . '</td>
                </tr>
            </table>
        </div>
        
        <div class="summary-section">
            <h2>EXECUTIVE SUMMARY</h2>
            <table class="summary-table">
                <thead>
                    <tr>
                        <th>Metric</th>
                        <th>Value</th>
                        <th>Calculation Method</th>
                        <th>Business Impact</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="metric-name">Total Orders</td>
                        <td class="value">' . $kpiData['total_orders'] . '</td>
                        <td>Count of completed transactions</td>
                        <td class="description">Indicates customer demand and market activity</td>
                    </tr>
                    <tr>
                        <td class="metric-name">Total Books Sold</td>
                        <td class="value">' . $kpiData['total_books_sold'] . '</td>
                        <td>Sum of all book quantities</td>
                        <td class="description">Shows inventory movement and sales volume</td>
                    </tr>
                    <tr>
                        <td class="metric-name">Total Sales Revenue</td>
                        <td class="value">৳' . number_format($kpiData['total_sales'], 0) . '</td>
                        <td>Sum of all order values</td>
                        <td class="description">Primary revenue indicator for the period</td>
                    </tr>
                    <tr>
                        <td class="metric-name">Average Order Value</td>
                        <td class="value">৳' . number_format($kpiData['avg_order_value'], 0) . '</td>
                        <td>Total Sales ÷ Total Orders</td>
                        <td class="description">Customer spending behavior and pricing strategy</td>
                    </tr>
                    <tr>
                        <td class="metric-name">Books Per Order</td>
                        <td class="value">' . round($booksPerOrder, 1) . '</td>
                        <td>Total Books ÷ Total Orders</td>
                        <td class="description">Cross-selling effectiveness and customer preferences</td>
                    </tr>
                    <tr>
                        <td class="metric-name">Daily Average Sales</td>
                        <td class="value">৳' . number_format($dailySales, 0) . '</td>
                        <td>Total Sales ÷ ' . $daysDiff . ' days</td>
                        <td class="description">Consistent daily performance indicator</td>
                    </tr>
                    <tr>
                        <td class="metric-name">Commission Earned</td>
                        <td class="value">৳' . number_format($commission, 0) . '</td>
                        <td>Total Sales × 5%</td>
                        <td class="description">Platform revenue from sales transactions</td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <div class="detailed-section">
            <h2>DETAILED TRANSACTION BREAKDOWN</h2>
            <table class="detailed-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Order #</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Book Title</th>
                        <th>Author</th>
                        <th>Qty</th>
                        <th>Unit Price</th>
                        <th>Total</th>
                        <th>Customer</th>
                    </tr>
                </thead>
                <tbody>';
    
    foreach ($detailedSales as $sale) {
        echo '<tr>
                <td>' . htmlspecialchars($sale['order_id']) . '</td>
                <td>' . htmlspecialchars($sale['order_number']) . '</td>
                <td>' . htmlspecialchars($sale['order_date']) . '</td>
                <td>' . htmlspecialchars($sale['order_status']) . '</td>
                <td>' . htmlspecialchars($sale['book_title']) . '</td>
                <td>' . htmlspecialchars($sale['book_author']) . '</td>
                <td>' . htmlspecialchars($sale['quantity']) . '</td>
                <td>৳' . number_format($sale['price_per_item'], 0) . '</td>
                <td>৳' . number_format($sale['total_amount'], 0) . '</td>
                <td>' . htmlspecialchars($sale['customer_name']) . '</td>
            </tr>';
    }
    
    echo '</tbody>
            </table>
        </div>
        
        <div class="footer">
            <div class="company">BookMarket</div>
            <p>Your trusted online book marketplace</p>
            <p>This report contains confidential business information</p>
            <p>Generated automatically on ' . date('F j, Y') . ' at ' . date('g:i A') . '</p>
        </div>
    </div>
</body>
</html>';
}
?>
