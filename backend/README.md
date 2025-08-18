# BookMarket Backend System

A complete PHP + MySQL backend system for the BookMarket online book buying and selling platform.

## 🚀 **Quick Start Guide**

### Prerequisites
- XAMPP (Apache + MySQL + PHP)
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web browser

### Installation Steps

#### 1. **Setup XAMPP**
1. Download and install XAMPP from [https://www.apachefriends.org/](https://www.apachefriends.org/)
2. Start Apache and MySQL services
3. Ensure both services are running (green status)

#### 2. **Database Setup**
1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Create a new database named `bookmarket_DataBase`
3. Import the SQL file: `database/setup.sql`
4. Verify all tables are created successfully

#### 3. **Project Setup**
1. Copy the entire `BookMarket` folder to `htdocs` directory
2. Navigate to: `http://localhost/BookMarket/`
3. The system should now be accessible

## 🗂️ **Project Structure**

```
BookMarket/
├── backend/                          # Backend PHP files
│   ├── config/                       # Configuration files
│   │   ├── database.php             # Database connection
│   │   └── session.php              # Session management
│   ├── auth/                         # Authentication system
│   │   └── user_auth.php            # User login/register
│   ├── books/                        # Book management
│   │   └── book_manager.php         # Book CRUD operations
│   ├── cart/                         # Shopping cart system
│   │   └── cart_manager.php         # Cart operations
│   ├── orders/                       # Order management
│   │   └── order_manager.php        # Order operations
│   ├── uploads/                      # File upload system
│   │   └── file_upload.php          # Image uploads
│   ├── api/                          # API endpoints
│   │   └── home_data.php            # Home page data
│   ├── database/                     # Database files
│   │   └── setup.sql                # Database schema
│   └── README.md                     # This file
├── css/                              # Frontend stylesheets
├── js/                               # Frontend JavaScript
├── images/                           # Static images
├── pages/                            # HTML pages
└── index.html                        # Home page
```

## 🗄️ **Database Schema**

### Core Tables

#### 1. **users** - User accounts
- `id` - Primary key
- `username` - Unique username
- `email` - Unique email
- `password_hash` - Encrypted password
- `user_type` - 'admin' or 'customer'
- `first_name`, `last_name` - User names
- `phone`, `address`, `city`, `postal_code`, `country` - Contact info
- `is_active` - Account status
- `created_at`, `updated_at` - Timestamps

#### 2. **categories** - Book categories
- `id` - Primary key
- `name` - Category name
- `description` - Category description
- `icon_class` - FontAwesome icon class

#### 3. **books** - Book listings
- `id` - Primary key
- `title`, `author`, `isbn` - Book details
- `description`, `price`, `condition` - Book info
- `cover_image_path`, `additional_images` - Image paths
- `status` - 'pending', 'approved', 'rejected', 'sold'
- `seller_id` - Foreign key to users
- `category_id` - Foreign key to categories
- `views_count` - View counter

#### 4. **cart_items** - Shopping cart
- `id` - Primary key
- `user_id` - Foreign key to users
- `book_id` - Foreign key to books
- `quantity` - Item quantity
- `added_at` - Timestamp

#### 5. **orders** - Customer orders
- `id` - Primary key
- `order_number` - Unique order number
- `user_id` - Foreign key to users
- `total_amount` - Order total
- `shipping_address`, `shipping_city`, `shipping_postal_code`, `shipping_country`
- `order_status` - 'Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'
- `payment_status` - 'Pending', 'Paid', 'Failed'
- `order_date` - Timestamp

#### 6. **order_items** - Order details
- `id` - Primary key
- `order_id` - Foreign key to orders
- `book_id` - Foreign key to books
- `quantity` - Item quantity
- `price_per_item` - Price at time of purchase
- `seller_id` - Foreign key to users

#### 7. **book_reviews** - Customer reviews
- `id` - Primary key
- `book_id` - Foreign key to books
- `user_id` - Foreign key to users
- `rating` - 1-5 star rating
- `review_text` - Review content
- `created_at` - Timestamp

#### 8. **admin_actions** - Admin audit trail
- `id` - Primary key
- `admin_id` - Foreign key to users
- `action_type` - Type of action
- `action_description` - Action details
- `target_table`, `target_id` - Affected record
- `created_at` - Timestamp

## 🔐 **Authentication System**

### User Types
- **Customer** - Can buy/sell books, manage profile
- **Admin** - Full system access, user management, book approval

### Security Features
- Password hashing with `password_hash()`
- Session management with timeout
- CSRF protection
- Input validation and sanitization
- SQL injection prevention with prepared statements

### Default Admin Account
- **Username**: `admin`
- **Password**: `admin123`
- **Email**: `admin@bookmarket.com`

## 📚 **Book Management System**

### Book Lifecycle
1. **Pending** - Book submitted by seller, awaiting admin approval
2. **Approved** - Book approved and available for purchase
3. **Rejected** - Book rejected by admin
4. **Sold** - Book purchased by customer

### Book Operations
- Add new book (customers)
- Edit book details (customers, before approval)
- Delete book (customers)
- Approve/reject book (admins)
- View book details (all users)
- Search and filter books (all users)

## 🛒 **Shopping Cart System**

### Features
- Add books to cart
- Update quantities
- Remove items
- Cart validation
- Checkout process

### Cart Validation
- Book availability check
- Price verification
- Stock validation

## 📋 **Order Management System**

### Order Statuses
- **Pending** - Order placed, awaiting processing
- **Processing** - Order being prepared
- **Shipped** - Order shipped to customer
- **Delivered** - Order delivered successfully
- **Cancelled** - Order cancelled

### Order Operations
- Place order (customers)
- View order history (customers)
- Update order status (admins)
- Cancel order (customers, before shipping)

## 📁 **File Upload System**

### Supported Formats
- **Images**: JPG, JPEG, PNG, GIF, WebP
- **Max Size**: 5MB per file
- **Storage**: Organized in subdirectories (books, profiles, temp)

### Security Features
- File type validation
- File size limits
- Unique filename generation
- Directory traversal prevention

## 🌐 **API Endpoints**

### Authentication
- `POST /backend/auth/user_auth.php` - User registration, login, profile management

### Books
- `POST /backend/books/book_manager.php` - Book CRUD operations
- `GET /backend/api/home_data.php` - Home page data

### Cart
- `POST /backend/cart/cart_manager.php` - Cart operations

### Orders
- `POST /backend/orders/order_manager.php` - Order management

### File Uploads
- `POST /backend/uploads/file_upload.php` - File upload operations

## 🔧 **Configuration**

### Database Configuration
Edit `backend/config/database.php`:
```php
define('DB_HOST', 'localhost');        // Database host
define('DB_NAME', 'bookmarket_DataBase'); // Database name
define('DB_USER', 'root');             // Database username
define('DB_PASS', '');                 // Database password
```

### Session Configuration
Edit `backend/config/session.php`:
```php
$session_timeout = 1800; // 30 minutes in seconds
```

## 🚨 **Troubleshooting**

### Common Issues

#### 1. **Database Connection Failed**
- Check if MySQL is running in XAMPP
- Verify database credentials in `database.php`
- Ensure database `bookmarket_DataBase` exists

#### 2. **File Upload Errors**
- Check upload directory permissions (755)
- Verify PHP upload limits in `php.ini`
- Check available disk space

#### 3. **Session Issues**
- Ensure cookies are enabled
- Check PHP session configuration
- Verify session directory permissions

#### 4. **404 Errors**
- Check Apache configuration
- Verify file paths and permissions
- Ensure `.htaccess` is properly configured

### Error Logs
- **PHP Errors**: Check XAMPP error logs
- **Database Errors**: Check MySQL error logs
- **Application Errors**: Check `error_log()` output

## 📱 **Frontend Integration**

### JavaScript AJAX Calls
```javascript
// Example: Login user
fetch('/backend/auth/user_auth.php', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
    },
    body: 'action=login&email=' + email + '&password=' + password
})
.then(response => response.json())
.then(data => {
    if (data.success) {
        // Handle success
    } else {
        // Handle error
    }
});
```

### Form Submission
```html
<form action="/backend/auth/user_auth.php" method="POST">
    <input type="hidden" name="action" value="register">
    <input type="text" name="username" required>
    <input type="email" name="email" required>
    <input type="password" name="password" required>
    <button type="submit">Register</button>
</form>
```

## 🔒 **Security Considerations**

### Input Validation
- All user inputs are sanitized using `cleanInput()`
- Email validation with `isValidEmail()`
- Phone number validation for Bangladesh format
- File upload validation and type checking

### SQL Injection Prevention
- All database queries use prepared statements
- Parameter binding prevents SQL injection
- Input sanitization for additional security

### XSS Prevention
- Output encoding with `htmlspecialchars()`
- Session security headers
- CSRF token protection

### File Upload Security
- File type validation
- File size limits
- Secure filename generation
- Directory traversal prevention

## 📊 **Performance Optimization**

### Database Optimization
- Proper indexing on frequently queried columns
- Efficient JOIN queries
- Pagination for large datasets
- Connection pooling with singleton pattern

### Caching Strategies
- Session-based caching
- Database query optimization
- Image optimization and thumbnails

## 🧪 **Testing**

### Test Data
The system includes sample data:
- Default admin account
- Sample book categories
- Test users can be created through registration

### Testing Checklist
- [ ] User registration and login
- [ ] Book submission and approval
- [ ] Shopping cart operations
- [ ] Order placement and management
- [ ] File upload functionality
- [ ] Admin operations
- [ ] Search and filtering

## 📈 **Future Enhancements**

### Planned Features
- Email notifications
- Payment gateway integration
- Advanced search with Elasticsearch
- Mobile app API
- Analytics dashboard
- Multi-language support
- Advanced reporting

### Scalability
- Database optimization
- Caching implementation
- Load balancing
- CDN integration

## 📞 **Support**

### Documentation
- This README file
- Code comments throughout the system
- Database schema documentation

### Contact
For technical support or questions:
- Check the troubleshooting section
- Review error logs
- Verify configuration settings

## 📄 **License**

This project is developed for educational and commercial use. All rights reserved.

---

**BookMarket Backend System** - Built with ❤️ for the book community
