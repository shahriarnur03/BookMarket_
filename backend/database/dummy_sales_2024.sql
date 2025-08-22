-- BookMarket 2024 Monthly Sales Seed (Additive, idempotent)
-- Populates orders and order_items across all months of 2024

USE bookmarket_DataBase;

-- =============================
-- 2024 ORDERS (2 per month)
-- Use distinct order_numbers to avoid conflicts with existing 2024 seeds
-- =============================
INSERT IGNORE INTO orders (order_number, user_id, total_amount, shipping_address, shipping_city, shipping_postal_code, shipping_country, order_status, payment_status, payment_method, order_date) VALUES
-- January
('ORD-2024-201', (SELECT id FROM users WHERE username = 'rahul_student'), 1800.00, 'Student Housing Block A, Room 15', 'Dhaka', '1205', 'Bangladesh', 'Delivered', 'Paid', 'Credit Card', '2024-01-07 10:00:00'),
('ORD-2024-202', (SELECT id FROM users WHERE username = 'fatima_student'), 400.00, 'Student Housing Block B, Room 22', 'Dhaka', '1205', 'Bangladesh', 'Shipped', 'Paid', 'Mobile Banking', '2024-01-21 15:30:00'),
-- February
('ORD-2024-203', (SELECT id FROM users WHERE username = 'omar_student'), 2500.00, 'Student Housing Block C, Room 8', 'Dhaka', '1205', 'Bangladesh', 'Delivered', 'Paid', 'Credit Card', '2024-02-10 09:45:00'),
('ORD-2024-204', (SELECT id FROM users WHERE username = 'sarah_professional'), 1100.00, 'Tech Park Plaza, Apt 12B', 'Dhaka', '1205', 'Bangladesh', 'Processing', 'Paid', 'Credit Card', '2024-02-24 14:20:00'),
-- March
('ORD-2024-205', (SELECT id FROM users WHERE username = 'mike_developer'), 1200.00, 'Digital Hub, Unit 7', 'Dhaka', '1205', 'Bangladesh', 'Delivered', 'Paid', 'Mobile Banking', '2024-03-05 11:10:00'),
('ORD-2024-206', (SELECT id FROM users WHERE username = 'lisa_designer'), 1100.00, 'Creative Quarter, Studio 3', 'Dhaka', '1205', 'Bangladesh', 'Shipped', 'Paid', 'Credit Card', '2024-03-19 16:05:00'),
-- April
('ORD-2024-207', (SELECT id FROM users WHERE username = 'prof_smith'), 1900.00, 'Academic Village, Apt 5A', 'Dhaka', '1205', 'Bangladesh', 'Delivered', 'Paid', 'Mobile Banking', '2024-04-08 12:25:00'),
('ORD-2024-208', (SELECT id FROM users WHERE username = 'dr_ahmed'), 1600.00, 'Faculty Housing, Building 2', 'Dhaka', '1205', 'Bangladesh', 'Processing', 'Paid', 'Credit Card', '2024-04-23 10:55:00'),
-- May
('ORD-2024-209', (SELECT id FROM users WHERE username = 'ayman'), 900.00, 'House 3, Road 3', 'Dhaka', '1205', 'Bangladesh', 'Delivered', 'Paid', 'Credit Card', '2024-05-06 09:00:00'),
('ORD-2024-210', (SELECT id FROM users WHERE username = 'neha'), 750.00, 'House 4, Road 4', 'Dhaka', '1205', 'Bangladesh', 'Pending', 'Pending', 'Mobile Banking', '2024-05-20 17:45:00'),
-- June
('ORD-2024-211', (SELECT id FROM users WHERE username = 'momin'), 800.00, 'House 5, Road 5', 'Dhaka', '1205', 'Bangladesh', 'Delivered', 'Paid', 'Credit Card', '2024-06-04 10:40:00'),
('ORD-2024-212', (SELECT id FROM users WHERE username = 'abid'), 950.00, 'House 1, Road 1', 'Dhaka', '1205', 'Bangladesh', 'Shipped', 'Paid', 'Mobile Banking', '2024-06-26 15:25:00'),
-- July
('ORD-2024-213', (SELECT id FROM users WHERE username = 'siyam'), 600.00, 'House 2, Road 2', 'Dhaka', '1205', 'Bangladesh', 'Processing', 'Paid', 'Credit Card', '2024-07-07 13:15:00'),
('ORD-2024-214', (SELECT id FROM users WHERE username = 'bookworm_alex'), 500.00, 'Literary Lane, House 18', 'Dhaka', '1205', 'Bangladesh', 'Delivered', 'Paid', 'Credit Card', '2024-07-21 10:05:00'),
-- August
('ORD-2024-215', (SELECT id FROM users WHERE username = 'collector_maria'), 500.00, 'Book Street, Villa 9', 'Dhaka', '1205', 'Bangladesh', 'Delivered', 'Paid', 'Mobile Banking', '2024-08-03 09:20:00'),
('ORD-2024-216', (SELECT id FROM users WHERE username = 'mike_developer'), 650.00, 'Digital Hub, Unit 7', 'Dhaka', '1205', 'Bangladesh', 'Shipped', 'Paid', 'Credit Card', '2024-08-28 16:40:00'),
-- September
('ORD-2024-217', (SELECT id FROM users WHERE username = 'rahul_student'), 1800.00, 'Student Housing Block A, Room 15', 'Dhaka', '1205', 'Bangladesh', 'Delivered', 'Paid', 'Credit Card', '2024-09-06 10:55:00'),
('ORD-2024-218', (SELECT id FROM users WHERE username = 'sarah_professional'), 2100.00, 'Tech Park Plaza, Apt 12B', 'Dhaka', '1205', 'Bangladesh', 'Processing', 'Paid', 'Mobile Banking', '2024-09-19 14:45:00'),
-- October
('ORD-2024-219', (SELECT id FROM users WHERE username = 'ayman'), 880.00, 'House 3, Road 3', 'Dhaka', '1205', 'Bangladesh', 'Delivered', 'Paid', 'Credit Card', '2024-10-08 09:10:00'),
('ORD-2024-220', (SELECT id FROM users WHERE username = 'neha'), 700.00, 'House 4, Road 4', 'Dhaka', '1205', 'Bangladesh', 'Shipped', 'Paid', 'Mobile Banking', '2024-10-26 18:30:00'),
-- November
('ORD-2024-221', (SELECT id FROM users WHERE username = 'momin'), 800.00, 'House 5, Road 5', 'Dhaka', '1205', 'Bangladesh', 'Processing', 'Paid', 'Credit Card', '2024-11-11 12:35:00'),
('ORD-2024-222', (SELECT id FROM users WHERE username = 'abid'), 950.00, 'House 1, Road 1', 'Dhaka', '1205', 'Bangladesh', 'Delivered', 'Paid', 'Credit Card', '2024-11-24 10:25:00'),
-- December
('ORD-2024-223', (SELECT id FROM users WHERE username = 'siyam'), 600.00, 'House 2, Road 2', 'Dhaka', '1205', 'Bangladesh', 'Delivered', 'Paid', 'Mobile Banking', '2024-12-05 09:35:00'),
('ORD-2024-224', (SELECT id FROM users WHERE username = 'bookworm_alex'), 450.00, 'Literary Lane, House 18', 'Dhaka', '1205', 'Bangladesh', 'Pending', 'Pending', 'Credit Card', '2024-12-22 17:15:00');

-- =============================
-- 2024 ORDER ITEMS
-- =============================
INSERT IGNORE INTO order_items (order_id, book_id, quantity, price_per_item, seller_id) VALUES
-- Jan
((SELECT id FROM orders WHERE order_number = 'ORD-2024-201'), (SELECT id FROM books WHERE title = 'Calculus: Early Transcendentals'), 1, 1800.00, (SELECT seller_id FROM books WHERE title = 'Calculus: Early Transcendentals')),
((SELECT id FROM orders WHERE order_number = 'ORD-2024-202'), (SELECT id FROM books WHERE title = 'The Alchemist'), 1, 400.00, (SELECT seller_id FROM books WHERE title = 'The Alchemist')),
-- Feb
((SELECT id FROM orders WHERE order_number = 'ORD-2024-203'), (SELECT id FROM books WHERE title = 'Organic Chemistry'), 1, 2500.00, (SELECT seller_id FROM books WHERE title = 'Organic Chemistry')),
((SELECT id FROM orders WHERE order_number = 'ORD-2024-204'), (SELECT id FROM books WHERE title = 'The Pragmatic Programmer'), 1, 1100.00, (SELECT seller_id FROM books WHERE title = 'The Pragmatic Programmer')),
-- Mar
((SELECT id FROM orders WHERE order_number = 'ORD-2024-205'), (SELECT id FROM books WHERE title = 'Design Patterns'), 1, 1200.00, (SELECT seller_id FROM books WHERE title = 'Design Patterns')),
((SELECT id FROM orders WHERE order_number = 'ORD-2024-206'), (SELECT id FROM books WHERE title = 'The Pragmatic Programmer'), 1, 1100.00, (SELECT seller_id FROM books WHERE title = 'The Pragmatic Programmer')),
-- Apr
((SELECT id FROM orders WHERE order_number = 'ORD-2024-207'), (SELECT id FROM books WHERE title = 'Database System Concepts'), 1, 1900.00, (SELECT seller_id FROM books WHERE title = 'Database System Concepts')),
((SELECT id FROM orders WHERE order_number = 'ORD-2024-208'), (SELECT id FROM books WHERE title = 'Linear Algebra and Its Applications'), 1, 1600.00, (SELECT seller_id FROM books WHERE title = 'Linear Algebra and Its Applications')),
-- May
((SELECT id FROM orders WHERE order_number = 'ORD-2024-209'), (SELECT id FROM books WHERE title = 'Zero to One'), 1, 880.00, (SELECT seller_id FROM books WHERE title = 'Zero to One')),
((SELECT id FROM orders WHERE order_number = 'ORD-2024-210'), (SELECT id FROM books WHERE title = 'Harry Potter and the Sorcerer''s Stone'), 1, 750.00, (SELECT seller_id FROM books WHERE title = 'Harry Potter and the Sorcerer''s Stone')),
-- Jun
((SELECT id FROM orders WHERE order_number = 'ORD-2024-211'), (SELECT id FROM books WHERE title = 'The 7 Habits of Highly Effective People'), 1, 800.00, (SELECT seller_id FROM books WHERE title = 'The 7 Habits of Highly Effective People')),
((SELECT id FROM orders WHERE order_number = 'ORD-2024-212'), (SELECT id FROM books WHERE title = 'Sapiens: A Brief History of Humankind'), 1, 950.00, (SELECT seller_id FROM books WHERE title = 'Sapiens: A Brief History of Humankind')),
-- Jul
((SELECT id FROM orders WHERE order_number = 'ORD-2024-213'), (SELECT id FROM books WHERE title = 'The Alchemist'), 1, 400.00, (SELECT seller_id FROM books WHERE title = 'The Alchemist')),
((SELECT id FROM orders WHERE order_number = 'ORD-2024-214'), (SELECT id FROM books WHERE title = 'Wuthering Heights'), 1, 480.00, (SELECT seller_id FROM books WHERE title = 'Wuthering Heights')),
-- Aug
((SELECT id FROM orders WHERE order_number = 'ORD-2024-215'), (SELECT id FROM books WHERE title = 'Wuthering Heights'), 1, 480.00, (SELECT seller_id FROM books WHERE title = 'Wuthering Heights')),
((SELECT id FROM orders WHERE order_number = 'ORD-2024-216'), (SELECT id FROM books WHERE title = 'You Don\'t Know JS Yet'), 1, 650.00, (SELECT seller_id FROM books WHERE title = 'You Don\'t Know JS Yet')),
-- Sep
((SELECT id FROM orders WHERE order_number = 'ORD-2024-217'), (SELECT id FROM books WHERE title = 'Calculus: Early Transcendentals'), 1, 1800.00, (SELECT seller_id FROM books WHERE title = 'Calculus: Early Transcendentals')),
((SELECT id FROM orders WHERE order_number = 'ORD-2024-218'), (SELECT id FROM books WHERE title = 'Principles of Economics'), 1, 2100.00, (SELECT seller_id FROM books WHERE title = 'Principles of Economics')),
-- Oct
((SELECT id FROM orders WHERE order_number = 'ORD-2024-219'), (SELECT id FROM books WHERE title = 'Zero to One'), 1, 880.00, (SELECT seller_id FROM books WHERE title = 'Zero to One')),
((SELECT id FROM orders WHERE order_number = 'ORD-2024-220'), (SELECT id FROM books WHERE title = 'Marketing Management'), 1, 1800.00, (SELECT seller_id FROM books WHERE title = 'Marketing Management')),
-- Nov
((SELECT id FROM orders WHERE order_number = 'ORD-2024-221'), (SELECT id FROM books WHERE title = 'The 7 Habits of Highly Effective People'), 1, 800.00, (SELECT seller_id FROM books WHERE title = 'The 7 Habits of Highly Effective People')),
((SELECT id FROM orders WHERE order_number = 'ORD-2024-222'), (SELECT id FROM books WHERE title = 'Pride and Prejudice'), 1, 450.00, (SELECT seller_id FROM books WHERE title = 'Pride and Prejudice')),
-- Dec
((SELECT id FROM orders WHERE order_number = 'ORD-2024-223'), (SELECT id FROM books WHERE title = 'The Power of Now'), 1, 600.00, (SELECT seller_id FROM books WHERE title = 'The Power of Now')),
((SELECT id FROM orders WHERE order_number = 'ORD-2024-224'), (SELECT id FROM books WHERE title = 'Jane Eyre'), 1, 500.00, (SELECT seller_id FROM books WHERE title = 'Jane Eyre'));


