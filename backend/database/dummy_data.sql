-- BookMarket Dummy Data Script
-- This script adds comprehensive dummy data to make the website look fully functional
-- Run this after setup.sql to populate with realistic demonstration data

USE bookmarket_DataBase;

-- =====================================================
-- 1. ADD MORE DIVERSE USERS (CUSTOMERS)
-- =====================================================

-- Add more customers with diverse profiles
INSERT INTO users (username, email, password_hash, user_type, first_name, last_name, phone, address, city, postal_code, country) VALUES
-- Students
('rahul_student', 'rahul@student.edu', '$2y$12$ck0pYMHHJP9HVc22rFgvXecPN.Anc1Qs2QmZ2P57EyZ6cL6Ct2iou', 'customer', 'Rahul', 'Ahmed', '+8801700000006', 'Student Housing Block A, Room 15', 'Dhaka', '1205', 'Bangladesh'),
('fatima_student', 'fatima@student.edu', '$2y$12$ck0pYMHHJP9HVc22rFgvXecPN.Anc1Qs2QmZ2P57EyZ6cL6Ct2iou', 'customer', 'Fatima', 'Khan', '+8801700000007', 'Student Housing Block B, Room 22', 'Dhaka', '1205', 'Bangladesh'),
('omar_student', 'omar@student.edu', '$2y$12$ck0pYMHHJP9HVc22rFgvXecPN.Anc1Qs2QmZ2P57EyZ6cL6Ct2iou', 'customer', 'Omar', 'Rahman', '+8801700000008', 'Student Housing Block C, Room 8', 'Dhaka', '1205', 'Bangladesh'),

-- Working Professionals
('sarah_professional', 'sarah@tech.com', '$2y$12$ck0pYMHHJP9HVc22rFgvXecPN.Anc1Qs2QmZ2P57EyZ6cL6Ct2iou', 'customer', 'Sarah', 'Johnson', '+8801700000009', 'Tech Park Plaza, Apt 12B', 'Dhaka', '1205', 'Bangladesh'),
('mike_developer', 'mike@dev.com', '$2y$12$ck0pYMHHJP9HVc22rFgvXecPN.Anc1Qs2QmZ2P57EyZ6cL6Ct2iou', 'customer', 'Mike', 'Chen', '+8801700000010', 'Digital Hub, Unit 7', 'Dhaka', '1205', 'Bangladesh'),
('lisa_designer', 'lisa@design.com', '$2y$12$ck0pYMHHJP9HVc22rFgvXecPN.Anc1Qs2QmZ2P57EyZ6cL6Ct2iou', 'customer', 'Lisa', 'Wang', '+8801700000011', 'Creative Quarter, Studio 3', 'Dhaka', '1205', 'Bangladesh'),

-- Teachers/Professors
('dr_ahmed', 'ahmed@university.edu', '$2y$12$ck0pYMHHJP9HVc22rFgvXecPN.Anc1Qs2QmZ2P57EyZ6cL6Ct2iou', 'customer', 'Dr. Ahmed', 'Hassan', '+8801700000012', 'Faculty Housing, Building 2', 'Dhaka', '1205', 'Bangladesh'),
('prof_smith', 'smith@college.edu', '$2y$12$ck0pYMHHJP9HVc22rFgvXecPN.Anc1Qs2QmZ2P57EyZ6cL6Ct2iou', 'customer', 'Prof. Smith', 'Wilson', '+8801700000013', 'Academic Village, Apt 5A', 'Dhaka', '1205', 'Bangladesh'),

-- Book Collectors/Enthusiasts
('bookworm_alex', 'alex@books.com', '$2y$12$ck0pYMHHJP9HVc22rFgvXecPN.Anc1Qs2QmZ2P57EyZ6cL6Ct2iou', 'customer', 'Alex', 'Thompson', '+8801700000014', 'Literary Lane, House 18', 'Dhaka', '1205', 'Bangladesh'),
('collector_maria', 'maria@collector.com', '$2y$12$ck0pYMHHJP9HVc22rFgvXecPN.Anc1Qs2QmZ2P57EyZ6cL6Ct2iou', 'customer', 'Maria', 'Garcia', '+8801700000015', 'Book Street, Villa 9', 'Dhaka', '1205', 'Bangladesh');

-- =====================================================
-- 2. ADD MORE BOOKS WITH DIVERSE STATUSES
-- =====================================================

-- Academic Books (Various conditions and prices)
INSERT INTO books (title, author, isbn, description, price, book_condition, cover_image_path, additional_images, status, seller_id, category_id, stock_quantity) VALUES
-- Mathematics & Science
('Calculus: Early Transcendentals', 'James Stewart', '9781285741550', 'Comprehensive calculus textbook covering limits, derivatives, integrals, and series.', 1800.00, 'Excellent', 'uploads/books/images (1).jpeg', NULL, 'approved', (SELECT id FROM users WHERE username = 'rahul_student'), (SELECT id FROM categories WHERE name = 'Academic'), 3),
('Physics for Scientists and Engineers', 'Raymond A. Serway', '9781133954057', 'Modern physics textbook with practical applications and problem-solving techniques.', 2200.00, 'Good', 'uploads/books/images (2).jpeg', NULL, 'approved', (SELECT id FROM users WHERE username = 'fatima_student'), (SELECT id FROM categories WHERE name = 'Academic'), 2),
('Organic Chemistry', 'John McMurry', '9781305080485', 'Comprehensive organic chemistry textbook with molecular models and reactions.', 2500.00, 'New', 'uploads/books/images (3).jpeg', NULL, 'approved', (SELECT id FROM users WHERE username = 'omar_student'), (SELECT id FROM categories WHERE name = 'Academic'), 1),
('Linear Algebra and Its Applications', 'Gilbert Strang', '9780030105678', 'Introduction to linear algebra with applications to computer science and engineering.', 1600.00, 'Good', 'uploads/books/images (4).jpeg', NULL, 'pending', (SELECT id FROM users WHERE username = 'dr_ahmed'), (SELECT id FROM categories WHERE name = 'Academic'), 4),

-- Computer Science & Technology
('Introduction to Algorithms', 'Thomas H. Cormen', '9780262033848', 'Comprehensive guide to algorithms and data structures.', 2800.00, 'Excellent', 'uploads/books/images (5).jpeg', NULL, 'approved', (SELECT id FROM users WHERE username = 'sarah_professional'), (SELECT id FROM categories WHERE name = 'Science & Tech'), 2),
('Design Patterns', 'Erich Gamma', '9780201633610', 'Elements of reusable object-oriented software design patterns.', 1200.00, 'Good', 'uploads/books/images (6).jpeg', NULL, 'approved', (SELECT id FROM users WHERE username = 'mike_developer'), (SELECT id FROM categories WHERE name = 'Science & Tech'), 3),
('The Pragmatic Programmer', 'Andrew Hunt', '9780201616224', 'Your journey to mastery in software development.', 1100.00, 'Excellent', 'uploads/books/images (7).jpeg', NULL, 'approved', (SELECT id FROM users WHERE username = 'lisa_designer'), (SELECT id FROM categories WHERE name = 'Science & Tech'), 1),
('Database System Concepts', 'Abraham Silberschatz', '9780073523323', 'Comprehensive database management systems textbook.', 1900.00, 'New', 'uploads/books/images (8).jpeg', NULL, 'pending', (SELECT id FROM users WHERE username = 'prof_smith'), (SELECT id FROM categories WHERE name = 'Science & Tech'), 2),

-- Business & Economics
('Principles of Economics', 'N. Gregory Mankiw', '9781305585126', 'Introduction to economic principles and market analysis.', 2100.00, 'Good', 'uploads/books/images (9).jpeg', NULL, 'approved', (SELECT id FROM users WHERE username = 'ayman'), (SELECT id FROM categories WHERE name = 'Business'), 3),
('Marketing Management', 'Philip Kotler', '9780133856460', 'Comprehensive marketing strategy and management textbook.', 1800.00, 'Excellent', 'uploads/books/images (10).jpeg', NULL, 'approved', (SELECT id FROM users WHERE username = 'sarah_professional'), (SELECT id FROM categories WHERE name = 'Business'), 2),
('Financial Accounting', 'Jerry J. Weygandt', '9781118162302', 'Principles of financial accounting and reporting.', 1600.00, 'Good', 'uploads/books/images (11).jpeg', NULL, 'pending', (SELECT id FROM users WHERE username = 'mike_developer'), (SELECT id FROM categories WHERE name = 'Business'), 4),
('Strategic Management', 'Fred R. David', '9780134167848', 'Concepts and cases in strategic business management.', 1400.00, 'New', 'uploads/books/images (12).jpeg', NULL, 'approved', (SELECT id FROM users WHERE username = 'lisa_designer'), (SELECT id FROM categories WHERE name = 'Business'), 1);

-- =====================================================
-- 3. ADD FICTION & LITERATURE BOOKS
-- =====================================================

INSERT INTO books (title, author, isbn, description, price, book_condition, cover_image_path, additional_images, status, seller_id, category_id, stock_quantity) VALUES
-- Classic Fiction
('Pride and Prejudice', 'Jane Austen', '9780141439518', 'Classic romance novel about the relationship between Elizabeth Bennet and Mr. Darcy.', 450.00, 'Excellent', 'uploads/books/images.jpeg', NULL, 'approved', (SELECT id FROM users WHERE username = 'bookworm_alex'), (SELECT id FROM categories WHERE name = 'Fiction'), 2),
('Jane Eyre', 'Charlotte Brontë', '9780141441146', 'Gothic romance novel about the young governess Jane Eyre and her mysterious employer.', 500.00, 'Good', 'uploads/books/download.jpeg', NULL, 'approved', (SELECT id FROM users WHERE username = 'collector_maria'), (SELECT id FROM categories WHERE name = 'Fiction'), 1),
('Wuthering Heights', 'Emily Brontë', '9780141439556', 'Tale of passionate love and revenge set in the Yorkshire moors.', 480.00, 'Fair', 'uploads/books/download (1).jpeg', NULL, 'approved', (SELECT id FROM users WHERE username = 'bookworm_alex'), (SELECT id FROM categories WHERE name = 'Fiction'), 3),
('The Catcher in the Rye', 'J.D. Salinger', '9780316769488', 'Coming-of-age story about teenage alienation and loss of innocence.', 550.00, 'Good', 'uploads/books/download (2).jpeg', NULL, 'pending', (SELECT id FROM users WHERE username = 'collector_maria'), (SELECT id FROM categories WHERE name = 'Fiction'), 2),

-- Modern Fiction
('The Kite Runner', 'Khaled Hosseini', '9781594631931', 'Story of unlikely friendship between a wealthy boy and the son of his father''s servant.', 650.00, 'New', 'uploads/books/download (3).jpeg', NULL, 'approved', (SELECT id FROM users WHERE username = 'rahul_student'), (SELECT id FROM categories WHERE name = 'Fiction'), 1),
('The Alchemist', 'Paulo Coelho', '9780062315007', 'Novel about a young Andalusian shepherd who dreams of finding a worldly treasure.', 400.00, 'Excellent', 'uploads/books/download (4).jpeg', NULL, 'approved', (SELECT id FROM users WHERE username = 'fatima_student'), (SELECT id FROM categories WHERE name = 'Fiction'), 2),
('Life of Pi', 'Yann Martel', '9780156027328', 'Adventure novel about an Indian boy who survives a shipwreck and is stranded in the Pacific Ocean.', 580.00, 'Good', 'uploads/books/download (5).jpeg', NULL, 'pending', (SELECT id FROM users WHERE username = 'omar_student'), (SELECT id FROM categories WHERE name = 'Fiction'), 1);

-- =====================================================
-- 4. ADD CHILDREN'S BOOKS
-- =====================================================

INSERT INTO books (title, author, isbn, description, price, book_condition, cover_image_path, additional_images, status, seller_id, category_id, stock_quantity) VALUES
-- Picture Books
('Where the Wild Things Are', 'Maurice Sendak', '9780064431781', 'Classic children''s picture book about a boy named Max who sails to an island inhabited by monsters.', 350.00, 'Excellent', 'uploads/books/download (6).jpeg', NULL, 'approved', (SELECT id FROM users WHERE username = 'neha'), (SELECT id FROM categories WHERE name = 'Children'), 4),
('Goodnight Moon', 'Margaret Wise Brown', '9780064430173', 'Beloved bedtime story with rhythmic text and gentle illustrations.', 300.00, 'Good', 'uploads/books/download.png', NULL, 'approved', (SELECT id FROM users WHERE username = 'fatima_student'), (SELECT id FROM categories WHERE name = 'Children'), 3),
('The Gruffalo', 'Julia Donaldson', '9780803730457', 'Story of a clever mouse who outwits predators by inventing a monster.', 400.00, 'New', 'uploads/books/download.png', NULL, 'approved', (SELECT id FROM users WHERE username = 'omar_student'), (SELECT id FROM categories WHERE name = 'Children'), 2),
('Brown Bear, Brown Bear', 'Bill Martin Jr.', '9780805017441', 'Colorful picture book introducing colors and animals to young children.', 280.00, 'Good', 'uploads/books/images (13).jpeg', NULL, 'pending', (SELECT id FROM users WHERE username = 'dr_ahmed'), (SELECT id FROM categories WHERE name = 'Children'), 5),

-- Chapter Books
('Charlotte''s Web', 'E.B. White', '9780061124952', 'Classic children''s novel about a pig named Wilbur and his friendship with a spider.', 450.00, 'Excellent', 'uploads/books/images (14).jpeg', NULL, 'approved', (SELECT id FROM users WHERE username = 'prof_smith'), (SELECT id FROM categories WHERE name = 'Children'), 2),
('Matilda', 'Roald Dahl', '9780141304707', 'Story of a brilliant little girl with extraordinary powers and a love of reading.', 500.00, 'Good', 'uploads/books/images (15).jpeg', NULL, 'approved', (SELECT id FROM users WHERE username = 'bookworm_alex'), (SELECT id FROM categories WHERE name = 'Children'), 1);

-- =====================================================
-- 5. ADD HISTORY & SELF-HELP BOOKS
-- =====================================================

INSERT INTO books (title, author, isbn, description, price, book_condition, cover_image_path, additional_images, status, seller_id, category_id, stock_quantity) VALUES
-- History Books
('Guns, Germs, and Steel', 'Jared Diamond', '9780393317558', 'Pulitzer Prize-winning book about the fates of human societies.', 750.00, 'Excellent', 'uploads/books/file_1755796682_257431278755d6ff.jpeg', NULL, 'approved', (SELECT id FROM users WHERE username = 'momin'), (SELECT id FROM categories WHERE name = 'History'), 2),
('Sapiens: A Brief History of Humankind', 'Yuval Noah Harari', '9780062316097', 'A narrative of humanity''s creation and evolution.', 950.00, 'New', 'uploads/books/file_1755797512_db1644c0de9ea7f8.png', NULL, 'approved', (SELECT id FROM users WHERE username = 'abid'), (SELECT id FROM categories WHERE name = 'History'), 1),
('The Rise and Fall of the Third Reich', 'William L. Shirer', '9781451655193', 'Comprehensive history of Nazi Germany.', 1200.00, 'Good', 'uploads/books/images (1).jpeg', NULL, 'pending', (SELECT id FROM users WHERE username = 'dr_ahmed'), (SELECT id FROM categories WHERE name = 'History'), 1),
('A People''s History of the United States', 'Howard Zinn', '9780061965586', 'Alternative perspective on American history.', 850.00, 'Fair', 'uploads/books/images (2).jpeg', NULL, 'approved', (SELECT id FROM users WHERE username = 'prof_smith'), (SELECT id FROM categories WHERE name = 'History'), 3),

-- Self-Help & Personal Development
('Think and Grow Rich', 'Napoleon Hill', '9781585424337', 'Classic self-help book on success principles.', 450.00, 'Good', 'uploads/books/images (3).jpeg', NULL, 'approved', (SELECT id FROM users WHERE username = 'bookworm_alex'), (SELECT id FROM categories WHERE name = 'Self-Help'), 2),
('The Power of Now', 'Eckhart Tolle', '9781577314806', 'Guide to spiritual enlightenment and living in the present moment.', 600.00, 'Excellent', 'uploads/books/images (4).jpeg', NULL, 'approved', (SELECT id FROM users WHERE username = 'collector_maria'), (SELECT id FROM categories WHERE name = 'Self-Help'), 1),
('Mindset: The New Psychology of Success', 'Carol S. Dweck', '9780345472328', 'How we can learn to fulfill our potential through mindset.', 550.00, 'New', 'uploads/books/images (5).jpeg', NULL, 'pending', (SELECT id FROM users WHERE username = 'sarah_professional'), (SELECT id FROM categories WHERE name = 'Self-Help'), 2),
('The 4-Hour Workweek', 'Timothy Ferriss', '9780307465351', 'Escape 9-5, live anywhere, and join the new rich.', 700.00, 'Good', 'uploads/books/images (6).jpeg', NULL, 'approved', (SELECT id FROM users WHERE username = 'mike_developer'), (SELECT id FROM categories WHERE name = 'Self-Help'), 1);

-- =====================================================
-- 6. ADD COOKBOOKS & LIFESTYLE BOOKS
-- =====================================================

INSERT INTO books (title, author, isbn, description, price, book_condition, cover_image_path, additional_images, status, seller_id, category_id, stock_quantity) VALUES
-- Cookbooks
('The Joy of Cooking', 'Irma S. Rombauer', '9780743246263', 'Comprehensive American cookbook with over 4,500 recipes.', 800.00, 'Excellent', 'uploads/books/images (7).jpeg', NULL, 'approved', (SELECT id FROM users WHERE username = 'lisa_designer'), (SELECT id FROM categories WHERE name = 'Cookbooks'), 2),
('Mastering the Art of French Cooking', 'Julia Child', '9780394721781', 'Classic French cooking techniques and recipes.', 950.00, 'Good', 'uploads/books/images (8).jpeg', NULL, 'approved', (SELECT id FROM users WHERE username = 'fatima_student'), (SELECT id FROM categories WHERE name = 'Cookbooks'), 1),
('Salt, Fat, Acid, Heat', 'Samin Nosrat', '9781476753836', 'Mastering the elements of good cooking.', 650.00, 'New', 'uploads/books/images (9).jpeg', NULL, 'pending', (SELECT id FROM users WHERE username = 'omar_student'), (SELECT id FROM categories WHERE name = 'Cookbooks'), 3),
('The Food Lab', 'J. Kenji López-Alt', '9780393081084', 'Better home cooking through science.', 1200.00, 'Excellent', 'uploads/books/images (10).jpeg', NULL, 'approved', (SELECT id FROM users WHERE username = 'rahul_student'), (SELECT id FROM categories WHERE name = 'Cookbooks'), 1);

-- =====================================================
-- 7. ADD REALISTIC ORDERS & TRANSACTIONS
-- =====================================================

-- Create diverse orders with different statuses
INSERT INTO orders (order_number, user_id, total_amount, shipping_address, shipping_city, shipping_postal_code, shipping_country, order_status, payment_status, payment_method, order_date) VALUES
-- Completed Orders
('ORD-2024-001', (SELECT id FROM users WHERE username = 'rahul_student'), 1800.00, 'Student Housing Block A, Room 15', 'Dhaka', '1205', 'Bangladesh', 'Delivered', 'Paid', 'Credit Card', '2024-01-15 10:30:00'),
('ORD-2024-002', (SELECT id FROM users WHERE username = 'fatima_student'), 2200.00, 'Student Housing Block B, Room 22', 'Dhaka', '1205', 'Bangladesh', 'Delivered', 'Paid', 'Mobile Banking', '2024-01-18 14:20:00'),
('ORD-2024-003', (SELECT id FROM users WHERE username = 'sarah_professional'), 2800.00, 'Tech Park Plaza, Apt 12B', 'Dhaka', '1205', 'Bangladesh', 'Delivered', 'Paid', 'Credit Card', '2024-01-20 09:15:00'),
('ORD-2024-004', (SELECT id FROM users WHERE username = 'mike_developer'), 1200.00, 'Digital Hub, Unit 7', 'Dhaka', '1205', 'Bangladesh', 'Delivered', 'Paid', 'Mobile Banking', '2024-01-22 16:45:00'),
('ORD-2024-005', (SELECT id FROM users WHERE username = 'bookworm_alex'), 900.00, 'Literary Lane, House 18', 'Dhaka', '1205', 'Bangladesh', 'Delivered', 'Paid', 'Credit Card', '2024-01-25 11:30:00'),

-- Processing Orders
('ORD-2024-006', (SELECT id FROM users WHERE username = 'omar_student'), 2500.00, 'Student Housing Block C, Room 8', 'Dhaka', '1205', 'Bangladesh', 'Processing', 'Paid', 'Mobile Banking', '2024-01-28 13:20:00'),
('ORD-2024-007', (SELECT id FROM users WHERE username = 'lisa_designer'), 1100.00, 'Creative Quarter, Studio 3', 'Dhaka', '1205', 'Bangladesh', 'Processing', 'Paid', 'Credit Card', '2024-01-29 15:10:00'),

-- Shipped Orders
('ORD-2024-008', (SELECT id FROM users WHERE username = 'collector_maria'), 500.00, 'Book Street, Villa 9', 'Dhaka', '1205', 'Bangladesh', 'Shipped', 'Paid', 'Credit Card', '2024-01-30 10:45:00'),
('ORD-2024-009', (SELECT id FROM users WHERE username = 'dr_ahmed'), 1600.00, 'Faculty Housing, Building 2', 'Dhaka', '1205', 'Bangladesh', 'Shipped', 'Paid', 'Mobile Banking', '2024-02-01 14:30:00'),

-- Pending Orders
('ORD-2024-010', (SELECT id FROM users WHERE username = 'prof_smith'), 1900.00, 'Academic Village, Apt 5A', 'Dhaka', '1205', 'Bangladesh', 'Pending', 'Pending', 'Credit Card', '2024-02-02 09:20:00'),
('ORD-2024-011', (SELECT id FROM users WHERE username = 'ayman'), 2100.00, 'House 5, Road 5', 'Dhaka', '1205', 'Bangladesh', 'Pending', 'Pending', 'Mobile Banking', '2024-02-03 11:15:00');

-- =====================================================
-- 8. ADD ORDER ITEMS FOR REALISTIC TRANSACTIONS
-- =====================================================

-- Add order items for each order
INSERT INTO order_items (order_id, book_id, quantity, price_per_item, seller_id) VALUES
-- Order 1: Calculus textbook
((SELECT id FROM orders WHERE order_number = 'ORD-2024-001'), (SELECT id FROM books WHERE title = 'Calculus: Early Transcendentals'), 1, 1800.00, (SELECT seller_id FROM books WHERE title = 'Calculus: Early Transcendentals')),

-- Order 2: Physics textbook
((SELECT id FROM orders WHERE order_number = 'ORD-2024-002'), (SELECT id FROM books WHERE title = 'Physics for Scientists and Engineers'), 1, 2200.00, (SELECT seller_id FROM books WHERE title = 'Physics for Scientists and Engineers')),

-- Order 3: Algorithms book
((SELECT id FROM orders WHERE order_number = 'ORD-2024-003'), (SELECT id FROM books WHERE title = 'Introduction to Algorithms'), 1, 2800.00, (SELECT seller_id FROM books WHERE title = 'Introduction to Algorithms')),

-- Order 4: Design Patterns
((SELECT id FROM orders WHERE order_number = 'ORD-2024-004'), (SELECT id FROM books WHERE title = 'Design Patterns'), 1, 1200.00, (SELECT seller_id FROM books WHERE title = 'Design Patterns')),

-- Order 5: Pride and Prejudice + Jane Eyre
((SELECT id FROM orders WHERE order_number = 'ORD-2024-005'), (SELECT id FROM books WHERE title = 'Pride and Prejudice'), 1, 450.00, (SELECT seller_id FROM books WHERE title = 'Pride and Prejudice')),
((SELECT id FROM orders WHERE order_number = 'ORD-2024-005'), (SELECT id FROM books WHERE title = 'Jane Eyre'), 1, 500.00, (SELECT seller_id FROM books WHERE title = 'Jane Eyre')),

-- Order 6: Organic Chemistry
((SELECT id FROM orders WHERE order_number = 'ORD-2024-006'), (SELECT id FROM books WHERE title = 'Organic Chemistry'), 1, 2500.00, (SELECT seller_id FROM books WHERE title = 'Organic Chemistry')),

-- Order 7: Pragmatic Programmer
((SELECT id FROM orders WHERE order_number = 'ORD-2024-007'), (SELECT id FROM books WHERE title = 'The Pragmatic Programmer'), 1, 1100.00, (SELECT seller_id FROM books WHERE title = 'The Pragmatic Programmer')),

-- Order 8: Wuthering Heights
((SELECT id FROM orders WHERE order_number = 'ORD-2024-008'), (SELECT id FROM books WHERE title = 'Wuthering Heights'), 1, 500.00, (SELECT seller_id FROM books WHERE title = 'Wuthering Heights')),

-- Order 9: Linear Algebra
((SELECT id FROM orders WHERE order_number = 'ORD-2024-009'), (SELECT id FROM books WHERE title = 'Linear Algebra and Its Applications'), 1, 1600.00, (SELECT seller_id FROM books WHERE title = 'Linear Algebra and Its Applications')),

-- Order 10: Database Concepts
((SELECT id FROM orders WHERE order_number = 'ORD-2024-010'), (SELECT id FROM books WHERE title = 'Database System Concepts'), 1, 1900.00, (SELECT seller_id FROM books WHERE title = 'Database System Concepts')),

-- Order 11: Economics Principles
((SELECT id FROM orders WHERE order_number = 'ORD-2024-011'), (SELECT id FROM books WHERE title = 'Principles of Economics'), 1, 2100.00, (SELECT seller_id FROM books WHERE title = 'Principles of Economics'));
