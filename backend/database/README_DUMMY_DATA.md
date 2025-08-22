# 📚 BookMarket Dummy Data Setup

This directory contains comprehensive dummy data to make your BookMarket website look fully functional and professional for demonstration purposes.

## 🎯 What This Adds

### 👥 **Users (15+ diverse profiles)**

-   **Students**: rahul_student, fatima_student, omar_student
-   **Professionals**: sarah_professional, mike_developer, lisa_designer
-   **Teachers**: dr_ahmed, prof_smith
-   **Collectors**: bookworm_alex, collector_maria
-   **Existing**: abid, siyam, ayman, neha, momin

### 📖 **Books (40+ diverse titles)**

-   **Academic**: Calculus, Physics, Chemistry, Linear Algebra
-   **Computer Science**: Algorithms, Design Patterns, Database Systems
-   **Business**: Economics, Marketing, Accounting, Strategy
-   **Fiction**: Pride and Prejudice, Jane Eyre, Wuthering Heights
-   **Children**: Where the Wild Things Are, Charlotte's Web, Matilda
-   **History**: Guns Germs and Steel, Sapiens, Third Reich
-   **Self-Help**: Think and Grow Rich, The Power of Now, Mindset
-   **Cookbooks**: Joy of Cooking, French Cooking, Food Lab

### 📊 **Realistic Data Distribution**

-   **Approved Books**: 25+ (ready for purchase)
-   **Pending Books**: 10+ (awaiting admin approval)
-   **Sold Books**: 5+ (completed transactions)
-   **Various Conditions**: New, Excellent, Good, Fair, Poor
-   **Price Range**: ৳280 - ৳2800 (realistic pricing)
-   **Stock Quantities**: 0-5 items (inventory management)

### 🛒 **Orders & Transactions (11 orders)**

-   **Delivered**: 5 orders (completed)
-   **Processing**: 2 orders (in progress)
-   **Shipped**: 2 orders (en route)
-   **Pending**: 2 orders (awaiting payment)
-   **Payment Methods**: Credit Card, Mobile Banking
-   **Total Revenue**: ৳20,000+ (realistic business data)

### ⭐ **Book Reviews (50+ reviews)**

-   **5-Star Reviews**: 30+ (excellent feedback)
-   **4-Star Reviews**: 15+ (good feedback)
-   **3-Star Reviews**: 5+ (average feedback)
-   **Detailed Comments**: Professional, realistic feedback
-   **Diverse Reviewers**: Students, professionals, teachers

### 🛍️ **Shopping Cart Items**

-   **Active Carts**: 5 users with items
-   **Realistic Shopping**: Multiple books per cart
-   **Various Categories**: Mix of academic and leisure books

### 🔍 **Admin Audit Trail**

-   **Book Approvals**: 5+ approval actions
-   **User Verifications**: 3+ user account verifications
-   **Order Management**: 3+ order processing actions
-   **Professional Logging**: Complete audit trail

## 🚀 How to Run

### **Option 1: Direct SQL Execution (Recommended)**

1. Open your MySQL client (phpMyAdmin, MySQL Workbench, etc.)
2. Select your `bookmarket_DataBase` database
3. Run the SQL files in this order:
    - `dummy_data.sql` (main data)
    - `dummy_reviews.sql` (reviews and additional data)

### **Option 2: Command Line**

```bash
# Navigate to database directory
cd backend/database

# Run the PHP execution script
php run_dummy_data.php
```

### **Option 3: Manual Copy-Paste**

1. Open each SQL file in a text editor
2. Copy the content
3. Paste into your MySQL client and execute

## 📁 Files Included

-   **`dummy_data.sql`** - Main dummy data (users, books, orders)
-   **`dummy_reviews.sql`** - Reviews, cart items, admin actions
-   **`run_dummy_data.php`** - PHP execution script
-   **`README_DUMMY_DATA.md`** - This instruction file

## 🎨 Website Features Now Available

### **Customer Experience**

-   ✅ **Browse Books**: 40+ books across all categories
-   ✅ **Search & Filter**: Realistic data for testing
-   ✅ **Book Reviews**: 50+ reviews with ratings
-   ✅ **Shopping Cart**: Realistic cart functionality
-   ✅ **Order History**: 11 orders with various statuses

### **Admin Dashboard**

-   ✅ **Book Management**: 40+ books to manage
-   ✅ **User Management**: 15+ diverse users
-   ✅ **Order Management**: 11 orders to process
-   ✅ **Sales Reports**: Realistic data for charts
-   ✅ **Audit Trail**: Professional admin logging

### **Sales & Analytics**

-   ✅ **Revenue Data**: ৳20,000+ in orders
-   ✅ **Customer Diversity**: Students, professionals, teachers
-   ✅ **Category Distribution**: All 8 categories populated
-   ✅ **Inventory Management**: Various stock levels
-   ✅ **Order Statuses**: Complete order lifecycle

## 🔧 Troubleshooting

### **Database Connection Issues**

-   Ensure XAMPP MySQL service is running
-   Check database credentials in `../config/database.example.php`
-   Verify database `bookmarket_DataBase` exists

### **SQL Execution Errors**

-   Run statements one by one to identify issues
-   Check for syntax errors in SQL files
-   Ensure all referenced tables exist

### **Data Not Appearing**

-   Refresh your website pages
-   Clear browser cache
-   Check database tables for new data

## 🎉 Result

After running these scripts, your BookMarket website will have:

-   **Professional appearance** with realistic data
-   **Full functionality** across all features
-   **Rich content** for demonstration purposes
-   **Realistic business data** for presentations
-   **Complete user experience** for testing

Your website will look like a fully operational, professional book marketplace! 🚀
