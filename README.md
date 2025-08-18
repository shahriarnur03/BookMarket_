# BookMarket

BookMarket is a web-based platform for buying and selling used books.

## Features

- User registration and login for customers and admins.
- Customers can list books for sale.
- Admins must approve book listings.
- Users can browse, search, and purchase books.
- Shopping cart and order management system.
- Admin dashboard for managing users, books, and orders.

## Requirements

- [XAMPP](https://www.apachefriends.org/index.html) (or any other web server with PHP and MySQL)

## Setup Instructions

1.  **Clone the repository** to your web server's document root (e.g., `htdocs` for XAMPP).

2.  **Start Services**: Open the XAMPP Control Panel and start the Apache and MySQL services.

3.  **Database Configuration**:
    - Navigate to the `backend/config/` directory.
    - Make a copy of `database.example.php` and rename it to `database.php`.
    - Open `database.php` and update the `DB_HOST`, `DB_NAME`, `DB_USER`, and `DB_PASS` constants with your local database credentials if they are different from the XAMPP defaults.

4.  **Import the Database**:
    - Open your browser and go to `http://localhost/phpmyadmin`.
    - Create a new database named `bookmarket_DataBase`.
    - Select the new database and go to the **Import** tab.
    - Click **Choose File** and select the `backend/database/setup.sql` file.
    - Click **Go** to run the import.

5.  **Verify Setup**:
    - Open your browser and navigate to `http://localhost/YOUR_PROJECT_FOLDER_NAME/backend/test_connection.php`.
    - You should see a success message with green checkmarks.

## How to Use

- The main entry point is `index.html`.
- The admin user created by default is:
  - **Username**: admin
  - **Password**: admin123
