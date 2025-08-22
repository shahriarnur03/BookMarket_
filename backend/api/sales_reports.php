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
    $db = getDB();
    
    // Debug: Log the query parameters
    error_log("Admin Sales KPI Query - Start Date: $startDate, End Date: $endDate");
    
    $sql = "SELECT 
                COUNT(DISTINCT o.id) as total_orders,
                SUM(oi.quantity) as total_books_sold,
                SUM(oi.quantity * oi.price_per_item) as total_sales,
                AVG(oi.quantity * oi.price_per_item) as avg_order_value
            FROM orders o
            JOIN order_items oi ON o.id = oi.order_id
            WHERE o.order_date BETWEEN ? AND ?
            AND o.order_status IN ('Delivered', 'Shipped', 'Processing')";
    
    $result = $db->selectOne($sql, [$startDate, $endDate]);
    
    // Debug: Log the result
    error_log("Admin Sales KPI Result: " . json_encode($result));
    
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
    // Set headers for HTML download that can be converted to PDF
    header('Content-Type: text/html; charset=utf-8');
    header('Content-Disposition: attachment; filename="sales_report_' . $startDate . '_to_' . $endDate . '.html"');
    
    // Generate HTML that can be opened in browser and printed to PDF
    $html = generatePDFHTML($startDate, $endDate, $kpiData);
    
    // Output the HTML (this can be opened in browser and printed to PDF)
    echo $html;
}

function generatePDFHTML($startDate, $endDate, $kpiData) {
    // Get additional data for detailed report
    $db = getDB();
    $detailedSales = $db->select(
        "SELECT 
            o.order_number,
            o.order_date,
            o.total_amount,
            o.order_status,
            oi.quantity,
            oi.price_per_item,
            b.title as book_title,
            b.author as book_author,
            u.first_name,
            u.last_name
        FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        JOIN books b ON oi.book_id = b.id
        JOIN users u ON o.user_id = u.id
        WHERE o.order_date BETWEEN ? AND ?
        AND o.order_status IN ('Delivered', 'Shipped', 'Processing')
        ORDER BY o.order_date DESC",
        [$startDate, $endDate]
    );
    
    // Create a clean, basic report format suitable for PDF
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>BookMarket Sales Report</title>
        <style>
            /* Basic, clean styling for PDF reports */
            body { 
                font-family: Arial, sans-serif; 
                margin: 0; 
                padding: 20px; 
                background-color: white;
                color: #333;
                font-size: 12px;
                line-height: 1.4;
            }
            
            .report-container {
                max-width: 800px;
                margin: 0 auto;
                background-color: white;
            }
            
            /* Report Header */
            .report-header { 
                text-align: center; 
                border-bottom: 2px solid #333; 
                padding-bottom: 15px; 
                margin-bottom: 20px; 
            }
            
            .report-header h1 { 
                margin: 0 0 5px 0; 
                font-size: 24px; 
                font-weight: bold;
                color: #333;
            }
            
            .report-header h2 { 
                margin: 0 0 10px 0; 
                font-size: 18px; 
                font-weight: bold;
                color: #666;
            }
            
            .report-header p { 
                margin: 3px 0; 
                font-size: 12px;
                color: #666;
            }
            
            /* Summary Section */
            .summary-section {
                margin-bottom: 25px;
            }
            
            .summary-title {
                font-size: 16px;
                font-weight: bold;
                color: #333;
                margin-bottom: 15px;
                border-bottom: 1px solid #ccc;
                padding-bottom: 5px;
            }
            
            .summary-grid {
                display: table;
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 20px;
            }
            
            .summary-row {
                display: table-row;
            }
            
            .summary-cell {
                display: table-cell;
                width: 25%;
                padding: 10px;
                text-align: center;
                border: 1px solid #ddd;
                vertical-align: top;
            }
            
            .summary-cell h3 {
                margin: 0 0 8px 0;
                font-size: 11px;
                color: #666;
                text-transform: uppercase;
                font-weight: bold;
            }
            
            .summary-cell .value {
                font-size: 18px;
                font-weight: bold;
                color: #333;
                margin-bottom: 5px;
            }
            
            .summary-cell .description {
                font-size: 10px;
                color: #888;
            }
            
            /* Detailed Section */
            .detailed-section {
                margin-bottom: 25px;
            }
            
            .detailed-title {
                font-size: 16px;
                font-weight: bold;
                color: #333;
                margin-bottom: 15px;
                border-bottom: 1px solid #ccc;
                padding-bottom: 5px;
            }
            
            /* Sales Table */
            .sales-table { 
                width: 100%; 
                border-collapse: collapse; 
                margin: 15px 0; 
                font-size: 10px;
            }
            
            .sales-table th, .sales-table td { 
                border: 1px solid #ddd; 
                padding: 6px; 
                text-align: left; 
                vertical-align: top;
            }
            
            .sales-table th { 
                background-color: #f5f5f5; 
                color: #333; 
                font-weight: bold;
                text-transform: uppercase;
                font-size: 9px;
            }
            
            .sales-table tbody tr:nth-child(even) {
                background-color: #fafafa;
            }
            
            /* Status badges */
            .status-badge {
                padding: 2px 6px;
                border-radius: 3px;
                font-size: 9px;
                font-weight: bold;
                text-transform: uppercase;
            }
            
            .status-delivered { background-color: #d4edda; color: #155724; }
            .status-shipped { background-color: #cce5ff; color: #004085; }
            .status-processing { background-color: #fff3cd; color: #856404; }
            
            /* Footer */
            .report-footer { 
                margin-top: 30px; 
                text-align: center; 
                color: #666; 
                font-size: 10px;
                padding-top: 15px;
                border-top: 1px solid #ccc;
            }
            
            /* Print optimization */
            @media print {
                body {
                    padding: 0;
                    margin: 0;
                }
                .report-container {
                    max-width: none;
                }
                .sales-table {
                    page-break-inside: auto;
                }
                .sales-table tr {
                    page-break-inside: avoid;
                    page-break-after: auto;
                }
            }
        </style>
    </head>
    <body>
        <div class="report-container">
            <!-- Report Header -->
            <div class="report-header">
                <h1>BookMarket Sales Report</h1>
                <h2>Sales Analysis & Summary</h2>
                <p><strong>Report Period:</strong> ' . date('F j, Y', strtotime($startDate)) . ' to ' . date('F j, Y', strtotime($endDate)) . '</p>
                <p><strong>Generated:</strong> ' . date('F j, Y \a\t g:i A') . '</p>
            </div>
            
            <!-- Summary Section -->
            <div class="summary-section">
                <h3 class="summary-title">Sales Summary</h3>
                <div class="summary-grid">
                    <div class="summary-row">
                        <div class="summary-cell">
                            <h3>Total Revenue</h3>
                            <div class="value">৳' . number_format($kpiData["total_sales"], 2) . '</div>
                            <div class="description">Total sales amount</div>
                        </div>
                        <div class="summary-cell">
                            <h3>Total Orders</h3>
                            <div class="value">' . $kpiData["total_orders"] . '</div>
                            <div class="description">Successful transactions</div>
                        </div>
                        <div class="summary-cell">
                            <h3>Books Sold</h3>
                            <div class="value">' . $kpiData["total_books_sold"] . '</div>
                            <div class="description">Total units sold</div>
                        </div>
                        <div class="summary-cell">
                            <h3>Avg Order Value</h3>
                            <div class="value">৳' . number_format($kpiData["avg_order_value"], 2) . '</div>
                            <div class="description">Per transaction</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Detailed Sales Section -->
            <div class="detailed-section">
                <h3 class="detailed-title">Detailed Sales Breakdown</h3>
                <table class="sales-table">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Date</th>
                            <th>Book Title</th>
                            <th>Author</th>
                            <th>Qty</th>
                            <th>Unit Price</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Customer</th>
                        </tr>
                    </thead>
                    <tbody>';
    
    if ($detailedSales) {
        foreach ($detailedSales as $sale) {
            $statusClass = strtolower(str_replace(' ', '-', $sale['order_status']));
            $html .= '
                        <tr>
                            <td><strong>' . $sale['order_number'] . '</strong></td>
                            <td>' . date('M j, Y', strtotime($sale['order_date'])) . '</td>
                            <td>' . htmlspecialchars($sale['book_title']) . '</td>
                            <td>' . htmlspecialchars($sale['book_author']) . '</td>
                            <td>' . $sale['quantity'] . '</td>
                            <td>৳' . number_format($sale['price_per_item'], 2) . '</td>
                            <td><strong>৳' . number_format($sale['quantity'] * $sale['price_per_item'], 2) . '</strong></td>
                            <td><span class="status-badge status-' . $statusClass . '">' . $sale['order_status'] . '</span></td>
                            <td>' . htmlspecialchars($sale['first_name'] . ' ' . $sale['last_name']) . '</td>
                        </tr>';
        }
    } else {
        $html .= '
                        <tr>
                            <td colspan="9" style="text-align: center; padding: 20px; color: #666;">
                                <em>No sales data available for the selected period</em>
                            </td>
                        </tr>';
    }
    
    $html .= '
                    </tbody>
                </table>
            </div>
            
            <!-- Report Footer -->
            <div class="report-footer">
                <p><strong>BookMarket</strong> - Your trusted platform for buying and selling books</p>
                <p>Report generated automatically by BookMarket Admin System</p>
                <p>© ' . date('Y') . ' BookMarket. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>';
    
    return $html;
}
?>
