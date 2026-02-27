# Sales Reports System Setup Guide

## Overview

The Sales Reports system provides a comprehensive overview of business metrics in a clean, table-based format. It shows key performance indicators, calculations, and business insights based on selected date ranges.

## Features

-   **Overall Summary Table**: Single table with 10 key business metrics
-   **Date Range Selection**: Choose from predefined periods or custom dates
-   **Export Options**: PDF, Excel, and CSV formats
-   **Clean Design**: Matches website's design format
-   **Responsive Layout**: Works on all devices

## Setup Instructions

### 1. Database Setup

Make sure your database is set up with the required tables:

-   `users` table with admin user
-   `orders` table for sales data
-   `order_items` table for order details
-   `books` table for book information

### 2. Default Admin User

The system includes a default admin user:

-   **Username**: `admin`
-   **Password**: `admin123`
-   **Email**: `admin@bookmarket.com`

### 3. Authentication

The Sales Reports page requires admin authentication:

1. Go to `/pages/admin/login.html`
2. Login with admin credentials
3. You'll be redirected to the admin dashboard
4. Navigate to Sales Reports from the navbar

### 4. File Structure

```
backend/
├── api/
│   └── sales_reports.php          # Main API endpoint
├── auth/
│   └── admin_login.php            # Admin authentication
└── config/
    ├── database.php               # Database connection
    └── session.php                # Session management

pages/admin/
└── sales-reports.html             # Sales Reports page

js/
└── sales-reports.js               # Frontend functionality
```

## How to Use

### 1. Access the Page

-   Navigate to `/pages/admin/sales-reports.html`
-   Must be logged in as admin user

### 2. Select Date Range

-   Choose from dropdown: This Month, Last Month, This Quarter, This Year, Last Year, or Custom Range
-   For custom dates, select start and end dates

### 3. Generate Report

-   Click "Generate Report" button
-   All metrics will update automatically

### 4. View Data

The summary table shows:

-   **Total Revenue**: Sum of all order values
-   **Total Orders**: Count of completed orders
-   **Total Books Sold**: Sum of all book quantities
-   **Average Order Value**: Total Revenue ÷ Total Orders
-   **Books Per Order**: Total Books ÷ Total Orders
-   **Daily Average Revenue**: Total Revenue ÷ Number of Days
-   **Daily Average Orders**: Total Orders ÷ Number of Days
-   **Estimated Profit**: Total Revenue × 30%
-   **Profit Margin**: (Estimated Profit ÷ Total Revenue) × 100
-   **Report Period**: Selected date range

### 5. Export Reports

-   **PDF**: Complete summary as PDF
-   **Excel**: Data in Excel format
-   **CSV**: Raw data in CSV format

## Troubleshooting

### Common Issues

#### 1. "Unauthorized Access" Error

-   **Cause**: Not logged in as admin
-   **Solution**: Login at `/pages/admin/login.html` with admin credentials

#### 2. "Failed to load KPI data" Error

-   **Cause**: Database connection or query issues
-   **Solution**: Check database connection and ensure tables exist

#### 3. Page Shows Sample Data

-   **Cause**: API call failed (usually authentication issue)
-   **Solution**: Login as admin and refresh the page

#### 4. Empty Tables

-   **Cause**: No sales data in the selected date range
-   **Solution**: Select a different date range or add test data

### Debug Steps

1. Check browser console for error messages
2. Verify admin login status
3. Check database connection
4. Ensure required tables exist with data
5. Check file permissions for backend files

## Sample Data

If you need to test the system, you can add sample orders to the database:

```sql
-- Add sample order
INSERT INTO orders (order_number, user_id, total_amount, shipping_address, shipping_city, shipping_postal_code, order_status, payment_status)
VALUES ('ORD001', 1, 500.00, 'Test Address', 'Test City', '1234', 'Delivered', 'Paid');

-- Add sample order item
INSERT INTO order_items (order_id, book_id, quantity, price_per_item, seller_id)
VALUES (1, 1, 2, 250.00, 1);
```

## Security Notes

-   Admin authentication required for all access
-   Session-based backend authentication
-   Input validation and sanitization
-   Prepared statements for database queries
-   CORS headers properly configured

## Future Enhancements

-   Real-time data updates
-   Advanced filtering options
-   Custom date range presets
-   Email report scheduling
-   Chart visualizations (optional)
-   User permission levels

## Support

For technical support or questions about the Sales Reports system, please check:

1. Browser console for error messages
2. Database connection status
3. Admin authentication status
4. File permissions and paths
