# Admin Sales Reports - BookMarket

## Overview

The Admin Sales Reports page provides comprehensive analytics and reporting functionality for administrators to monitor and analyze sales performance across the BookMarket platform.

## Features

### 1. Key Performance Indicators (KPIs)

-   **Total Sales**: Overall revenue generated
-   **Total Orders**: Number of orders placed
-   **Average Order Value**: Mean transaction value
-   **Conversion Rate**: Sales conversion metrics
-   **Trend Indicators**: Percentage change from previous periods

### 2. Advanced Filtering

-   **Date Range Selection**: Today, Week, Month, Quarter, Year, Custom
-   **Category Filtering**: Filter by book categories (Fiction, Non-Fiction, Academic, Children)
-   **Custom Date Range**: Select specific start and end dates

### 3. Interactive Charts

-   **Sales Trend Chart**: Line chart showing sales performance over time
-   **Category Performance Chart**: Doughnut chart displaying category distribution

### 4. Data Tables

-   **Top Selling Books**: Ranked list of best-performing books
-   **Recent Orders**: Latest order transactions with details

### 5. Export Functionality

-   **Multiple Formats**: PDF, Excel, CSV export options
-   **Filtered Exports**: Export data based on current filter settings
-   **Scheduled Reports**: Future feature for automated reporting

## File Structure

```
pages/admin/
├── sales-reports.html          # Main sales reports page
├── dashboard.html              # Updated with sales reports link
├── book-management.html        # Updated with sales reports link
├── order-management.html       # Updated with sales reports link
├── user-management.html        # Updated with sales reports link
├── book-approval.html          # Updated with sales reports link
└── advertising.html            # Updated with sales reports link

js/
├── sales-reports.js            # Sales reports functionality
└── admin-navbar.js             # Updated navigation

backend/api/
└── sales_reports.php           # Backend API for data

css/
└── styles.css                  # Updated with new variables
```

## Navigation Integration

### Top Navbar (admin-navbar.js)

-   Added "Sales Reports" link to main navigation menu
-   Integrated with profile dropdown menu
-   Active state management for current page

### Sidebar Navigation

-   Added to all admin pages with sidebar navigation
-   Consistent icon and styling across all pages
-   Proper active state highlighting

## API Endpoints

### Backend API (sales_reports.php)

#### 1. Get Sales KPI

```
POST /backend/api/sales_reports.php
Action: get_sales_kpi
Parameters: start_date, end_date, category
```

#### 2. Get Top Books

```
POST /backend/api/sales_reports.php
Action: get_top_books
Parameters: start_date, end_date, category
```

#### 3. Get Recent Orders

```
POST /backend/api/sales_reports.php
Action: get_recent_orders
Parameters: start_date, end_date, category
```

#### 4. Export Report

```
POST /backend/api/sales_reports.php
Action: export_report
Parameters: type, start_date, end_date, category
```

## Database Queries

### Sales KPI Query

```sql
SELECT
    COUNT(DISTINCT o.id) as total_orders,
    SUM(oi.price_per_item * oi.quantity) as total_sales,
    AVG(oi.price_per_item * oi.quantity) as avg_order_value
FROM orders o
JOIN order_items oi ON o.id = oi.order_id
JOIN books b ON oi.book_id = b.id
WHERE o.order_date BETWEEN ? AND ?
```

### Top Books Query

```sql
SELECT
    b.id, b.title, b.author, b.category,
    SUM(oi.quantity) as units_sold,
    SUM(oi.price_per_item * oi.quantity) as revenue
FROM books b
JOIN order_items oi ON b.id = oi.book_id
JOIN orders o ON oi.order_id = o.id
WHERE o.order_date BETWEEN ? AND ?
GROUP BY b.id, b.title, b.author, b.category
ORDER BY units_sold DESC
LIMIT 10
```

## Usage Instructions

### 1. Accessing Sales Reports

-   Navigate to any admin page
-   Click "Sales Reports" in the navigation menu
-   Or access directly: `/pages/admin/sales-reports.html`

### 2. Generating Reports

1. Select date range from dropdown or custom dates
2. Choose category filter (optional)
3. Click "Generate Report" button
4. View real-time data updates

### 3. Exporting Data

1. Set desired filters
2. Choose export format (PDF, Excel, CSV)
3. Click export button
4. File downloads automatically

### 4. Chart Interaction

-   Hover over chart elements for detailed information
-   Charts automatically update with filter changes
-   Responsive design for mobile devices

## Technical Implementation

### Frontend Technologies

-   **HTML5**: Semantic structure and accessibility
-   **CSS3**: Modern styling with CSS variables
-   **JavaScript ES6+**: Modern JavaScript features
-   **Chart.js**: Interactive chart library
-   **Font Awesome**: Icon library

### Backend Technologies

-   **PHP 7.4+**: Server-side processing
-   **MySQL**: Database queries and data management
-   **Prepared Statements**: SQL injection prevention
-   **Session Management**: Admin authentication

### Security Features

-   **Admin Authentication**: Session-based access control
-   **SQL Injection Prevention**: Prepared statements
-   **Input Validation**: Server-side parameter validation
-   **CORS Headers**: Proper cross-origin handling

## Responsive Design

### Mobile Optimization

-   Responsive grid layouts
-   Touch-friendly interface elements
-   Optimized chart display for small screens
-   Collapsible sidebar on mobile devices

### Breakpoints

-   **Desktop**: 1200px and above
-   **Tablet**: 768px - 1199px
-   **Mobile**: Below 768px

## Browser Compatibility

### Supported Browsers

-   Chrome 80+
-   Firefox 75+
-   Safari 13+
-   Edge 80+

### Required Features

-   ES6+ JavaScript support
-   CSS Grid and Flexbox
-   Canvas API for charts
-   Fetch API for AJAX requests

## Future Enhancements

### Planned Features

1. **Advanced Analytics**: Machine learning insights
2. **Real-time Updates**: WebSocket integration
3. **Custom Dashboards**: User-configurable layouts
4. **Email Reports**: Automated report delivery
5. **Data Visualization**: Advanced chart types
6. **Performance Metrics**: Page load and user behavior

### Integration Opportunities

-   **Google Analytics**: Enhanced tracking
-   **Payment Gateways**: Financial reporting
-   **Inventory Systems**: Stock level analytics
-   **CRM Systems**: Customer relationship insights

## Troubleshooting

### Common Issues

#### 1. Charts Not Loading

-   Check Chart.js CDN connection
-   Verify JavaScript console for errors
-   Ensure proper HTML structure

#### 2. Data Not Displaying

-   Check backend API connectivity
-   Verify database connection
-   Review browser network tab

#### 3. Export Not Working

-   Check file permissions
-   Verify export format support
-   Review server error logs

### Debug Mode

Enable debug mode by adding `?debug=1` to URL for detailed error information.

## Performance Considerations

### Optimization Strategies

-   **Lazy Loading**: Load data on demand
-   **Caching**: Implement Redis/Memcached
-   **Database Indexing**: Optimize query performance
-   **CDN Usage**: Static asset delivery

### Monitoring

-   **Response Times**: Track API performance
-   **Memory Usage**: Monitor resource consumption
-   **Database Queries**: Optimize slow queries
-   **User Experience**: Track page load times

## Support and Maintenance

### Documentation Updates

-   Keep this README current with changes
-   Document new features and API changes
-   Maintain troubleshooting guides

### Code Maintenance

-   Regular security updates
-   Performance optimization
-   Bug fixes and improvements
-   Feature enhancements

---

**Last Updated**: December 2024
**Version**: 1.0.0
**Author**: BookMarket Development Team
