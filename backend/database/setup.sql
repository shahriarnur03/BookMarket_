-- BookMarket Database Setup
-- This file creates the complete database structure for the BookMarket website

-- Create the database
CREATE DATABASE IF NOT EXISTS bookmarket_DataBase;
USE bookmarket_DataBase;

-- 1. Users Table - Stores customer and admin accounts
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    user_type ENUM('customer', 'admin') DEFAULT 'customer',
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    city VARCHAR(50),
    postal_code VARCHAR(20),
    country VARCHAR(50) DEFAULT 'Bangladesh',
    profile_image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 2. Categories Table - Stores book categories
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    icon_class VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. Books Table - Stores all book listings
CREATE TABLE IF NOT EXISTS books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(255) NOT NULL,
    isbn VARCHAR(20),
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    book_condition ENUM('New', 'Excellent', 'Good', 'Fair', 'Poor') NOT NULL,
    cover_image_path VARCHAR(255),
    additional_images TEXT, -- JSON array of additional image paths
    status ENUM('pending', 'approved', 'rejected', 'sold') DEFAULT 'pending',
    seller_id INT NOT NULL,
    category_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

-- Create cart table
CREATE TABLE IF NOT EXISTS cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    book_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_book (user_id, book_id)
);

-- Create cart_items table for better cart management
CREATE TABLE IF NOT EXISTS cart_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cart_id INT NOT NULL,
    book_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cart_id) REFERENCES cart(id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE
);

-- 5. Orders Table - Stores order information
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    user_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    shipping_address TEXT NOT NULL,
    shipping_city VARCHAR(50) NOT NULL,
    shipping_postal_code VARCHAR(20) NOT NULL,
    shipping_country VARCHAR(50) DEFAULT 'Bangladesh',
    order_status ENUM('Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled') DEFAULT 'Pending',
    payment_status ENUM('Pending', 'Paid', 'Failed') DEFAULT 'Pending',
    payment_method VARCHAR(50),
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 6. Order Items Table - Stores individual items in orders
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    book_id INT NOT NULL,
    quantity INT NOT NULL,
    price_per_item DECIMAL(10,2) NOT NULL,
    seller_id INT NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
    FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 7. Book Reviews Table - Stores customer reviews for books
CREATE TABLE IF NOT EXISTS book_reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    book_id INT NOT NULL,
    user_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    review_text TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_review (book_id, user_id)
);

-- 8. Admin Actions Table - Logs admin actions for audit
CREATE TABLE IF NOT EXISTS admin_actions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    action_type VARCHAR(100) NOT NULL,
    action_description TEXT,
    target_table VARCHAR(50),
    target_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert default categories
INSERT INTO categories (name, description, icon_class) VALUES
('Fiction', 'Novels, short stories, and literary works', 'fas fa-book'),
('Academic', 'Textbooks and educational materials', 'fas fa-graduation-cap'),
('Science & Tech', 'Scientific and technological publications', 'fas fa-microscope'),
('Business', 'Business, economics, and finance books', 'fas fa-chart-line'),
('Children', 'Books for children and young adults', 'fas fa-child'),
('History', 'Historical books and biographies', 'fas fa-landmark'),
('Self-Help', 'Personal development and motivation books', 'fas fa-heart'),
('Cookbooks', 'Cooking and recipe books', 'fas fa-utensils');

-- Insert default admin user (password: admin123)
INSERT INTO users (username, email, password_hash, user_type, first_name, last_name, phone) VALUES
('admin', 'admin@bookmarket.com', '$2y$12$9kU/owMM2CNfom2WD/3g9ely5yVf64F3hVEspSdTOOUv/CeUKpapa', 'admin', 'System', 'Administrator', '+880 123 456789');

-- Seed sample customers (password for all: 123456)
INSERT INTO users (username, email, password_hash, user_type, first_name, last_name, phone, address, city, postal_code, country) VALUES
('abid', 'abid@gmail.com', '$2y$12$ck0pYMHHJP9HVc22rFgvXecPN.Anc1Qs2QmZ2P57EyZ6cL6Ct2iou', 'customer', 'Abid', 'Customer', '+8801700000001', 'House 1, Road 1', 'Dhaka', '1205', 'Bangladesh'),
('siyam', 'siyam@gmail.com', '$2y$12$ck0pYMHHJP9HVc22rFgvXecPN.Anc1Qs2QmZ2P57EyZ6cL6Ct2iou', 'customer', 'Siyam', 'Customer', '+8801700000002', 'House 2, Road 2', 'Dhaka', '1205', 'Bangladesh'),
('ayman', 'ayman@gmail.com', '$2y$12$ck0pYMHHJP9HVc22rFgvXecPN.Anc1Qs2QmZ2P57EyZ6cL6Ct2iou', 'customer', 'Ayman', 'Customer', '+8801700000003', 'House 3, Road 3', 'Dhaka', '1205', 'Bangladesh'),
('neha', 'neha@gamil.com', '$2y$12$ck0pYMHHJP9HVc22rFgvXecPN.Anc1Qs2QmZ2P57EyZ6cL6Ct2iou', 'customer', 'Neha', 'Customer', '+8801700000004', 'House 4, Road 4', 'Dhaka', '1205', 'Bangladesh'),
('momin', 'momin@gmail.com', '$2y$12$ck0pYMHHJP9HVc22rFgvXecPN.Anc1Qs2QmZ2P57EyZ6cL6Ct2iou', 'customer', 'Momin', 'Customer', '+8801700000005', 'House 5, Road 5', 'Dhaka', '1205', 'Bangladesh');

-- Seed books with mixed statuses (approved, pending, sold) for realistic data
INSERT INTO books (title, author, isbn, description, price, book_condition, cover_image_path, additional_images, status, seller_id, category_id)
VALUES
-- Abid's books (2 approved, 1 pending)
('The Great Gatsby', 'F. Scott Fitzgerald', '9780743273565', 'Classic American novel set in the Jazz Age.', 650.00, 'Excellent', NULL, NULL, 'approved', (SELECT id FROM users WHERE email = 'abid@gmail.com'), (SELECT id FROM categories WHERE name = 'Fiction')),
('To Kill a Mockingbird', 'Harper Lee', '9780061120084', 'Pulitzer Prize-winning novel about justice and race.', 700.00, 'Good', NULL, NULL, 'approved', (SELECT id FROM users WHERE email = 'abid@gmail.com'), (SELECT id FROM categories WHERE name = 'Fiction')),
('Sapiens: A Brief History of Humankind', 'Yuval Noah Harari', '9780062316097', 'A narrative of humanity''s creation and evolution.', 950.00, 'New', NULL, NULL, 'pending', (SELECT id FROM users WHERE email = 'abid@gmail.com'), (SELECT id FROM categories WHERE name = 'History')),

-- Siyam's books (1 approved, 1 pending, 1 sold)
('1984', 'George Orwell', '9780451524935', 'Dystopian social science fiction novel and cautionary tale.', 600.00, 'Good', NULL, NULL, 'approved', (SELECT id FROM users WHERE email = 'siyam@gmail.com'), (SELECT id FROM categories WHERE name = 'Fiction')),
('Brave New World', 'Aldous Huxley', '9780060850524', 'Dystopian classic envisioning a futuristic society.', 620.00, 'Excellent', NULL, NULL, 'pending', (SELECT id FROM users WHERE email = 'siyam@gmail.com'), (SELECT id FROM categories WHERE name = 'Fiction')),
('Atomic Habits', 'James Clear', '9780735211292', 'An easy & proven way to build good habits & break bad ones.', 850.00, 'New', NULL, NULL, 'sold', (SELECT id FROM users WHERE email = 'siyam@gmail.com'), (SELECT id FROM categories WHERE name = 'Self-Help')),

-- Ayman's books (2 approved, 1 pending)
('The Lean Startup', 'Eric Ries', '9780307887894', 'How constant innovation creates radically successful businesses.', 900.00, 'Excellent', NULL, NULL, 'approved', (SELECT id FROM users WHERE email = 'ayman@gmail.com'), (SELECT id FROM categories WHERE name = 'Business')),
('Zero to One', 'Peter Thiel', '9780804139298', 'Notes on startups or how to build the future.', 880.00, 'Good', NULL, NULL, 'approved', (SELECT id FROM users WHERE email = 'ayman@gmail.com'), (SELECT id FROM categories WHERE name = 'Business')),
('Clean Code', 'Robert C. Martin', '9780132350884', 'Handbook of agile software craftsmanship.', 1200.00, 'New', NULL, NULL, 'pending', (SELECT id FROM users WHERE email = 'ayman@gmail.com'), (SELECT id FROM categories WHERE name = 'Science & Tech')),

-- Neha's books (1 approved, 1 pending, 1 sold)
('Harry Potter and the Sorcerer''s Stone', 'J.K. Rowling', '9780590353427', 'The first book in the Harry Potter series.', 750.00, 'New', NULL, NULL, 'approved', (SELECT id FROM users WHERE email = 'neha@gamil.com'), (SELECT id FROM categories WHERE name = 'Children')),
('The Hobbit', 'J.R.R. Tolkien', '9780547928227', 'A fantasy novel and prelude to The Lord of the Rings.', 700.00, 'Excellent', NULL, NULL, 'pending', (SELECT id FROM users WHERE email = 'neha@gamil.com'), (SELECT id FROM categories WHERE name = 'Fiction')),
('The Very Hungry Caterpillar', 'Eric Carle', '9780399226908', 'Beloved children''s book featuring a caterpillar''s journey.', 400.00, 'Good', NULL, NULL, 'sold', (SELECT id FROM users WHERE email = 'neha@gamil.com'), (SELECT id FROM categories WHERE name = 'Children')),

-- Momin's books (1 approved, 1 pending, 1 sold)
('A Brief History of Time', 'Stephen Hawking', '9780553380163', 'From the Big Bang to black holes.', 950.00, 'Excellent', NULL, NULL, 'approved', (SELECT id FROM users WHERE email = 'momin@gmail.com'), (SELECT id FROM categories WHERE name = 'Science & Tech')),
('The 7 Habits of Highly Effective People', 'Stephen R. Covey', '9780743269513', 'Powerful lessons in personal change.', 800.00, 'Good', NULL, NULL, 'pending', (SELECT id FROM users WHERE email = 'momin@gmail.com'), (SELECT id FROM categories WHERE name = 'Self-Help')),
('The Art of War', 'Sun Tzu', '9781599869773', 'Ancient Chinese military treatise on strategy and tactics.', 500.00, 'Good', NULL, NULL, 'sold', (SELECT id FROM users WHERE email = 'momin@gmail.com'), (SELECT id FROM categories WHERE name = 'History'));

-- Create indexes for better performance
CREATE INDEX idx_books_status ON books(status);
CREATE INDEX idx_books_category ON books(category_id);
CREATE INDEX idx_books_seller ON books(seller_id);
CREATE INDEX idx_books_price ON books(price);
CREATE INDEX idx_orders_user ON orders(user_id);
CREATE INDEX idx_orders_status ON orders(order_status);
CREATE INDEX idx_cart_items_cart ON cart_items(cart_id);
CREATE INDEX idx_cart_items_book ON cart_items(book_id);
CREATE INDEX idx_cart_user ON cart(user_id);
