-- BookMarket 2025 Monthly Sales Seed (Additive, idempotent)
-- Populates orders and order_items across all months of 2025

USE bookmarket_DataBase;

-- =============================
-- 2025 ORDERS (2 per month)
-- =============================
INSERT IGNORE INTO orders (order_number, user_id, total_amount, shipping_address, shipping_city, shipping_postal_code, shipping_country, order_status, payment_status, payment_method, order_date) VALUES
-- January
('ORD-2025-001', (SELECT id FROM users WHERE username = 'nabila_reader'), 520.00, 'House 10, Road 12', 'Dhaka', '1207', 'Bangladesh', 'Delivered', 'Paid', 'Credit Card', '2025-01-05 10:15:00'),
('ORD-2025-002', (SELECT id FROM users WHERE username = 'kabir_collector'), 900.00, 'Book Street 4', 'Dhaka', '1206', 'Bangladesh', 'Shipped', 'Paid', 'Mobile Banking', '2025-01-18 16:30:00'),
-- February
('ORD-2025-003', (SELECT id FROM users WHERE username = 'sadia_mom'), 1000.00, 'Green Road 45', 'Dhaka', '1205', 'Bangladesh', 'Processing', 'Paid', 'Credit Card', '2025-02-08 11:40:00'),
('ORD-2025-004', (SELECT id FROM users WHERE username = 'hasan_exec'), 980.00, 'Corporate Tower 12F', 'Dhaka', '1217', 'Bangladesh', 'Pending', 'Pending', 'Mobile Banking', '2025-02-21 14:55:00'),
-- March
('ORD-2025-005', (SELECT id FROM users WHERE username = 'faria_designer'), 580.00, 'Creative Block A-3', 'Dhaka', '1211', 'Bangladesh', 'Delivered', 'Paid', 'Credit Card', '2025-03-06 09:25:00'),
('ORD-2025-006', (SELECT id FROM users WHERE username = 'tasnim_student'), 1700.00, 'Student Dorm D2-11', 'Dhaka', '1209', 'Bangladesh', 'Delivered', 'Paid', 'Mobile Banking', '2025-03-19 18:05:00'),
-- April
('ORD-2025-007', (SELECT id FROM users WHERE username = 'rehan_dev'), 1800.00, 'Tech Park, Suite 203', 'Dhaka', '1212', 'Bangladesh', 'Shipped', 'Paid', 'Credit Card', '2025-04-04 12:10:00'),
('ORD-2025-008', (SELECT id FROM users WHERE username = 'zayed_student'), 1450.00, 'Student Dorm E1-07', 'Dhaka', '1209', 'Bangladesh', 'Processing', 'Paid', 'Mobile Banking', '2025-04-22 15:40:00'),
-- May
('ORD-2025-009', (SELECT id FROM users WHERE username = 'mouna_teacher'), 780.00, 'Faculty Housing 6B', 'Dhaka', '1215', 'Bangladesh', 'Delivered', 'Paid', 'Credit Card', '2025-05-03 10:00:00'),
('ORD-2025-010', (SELECT id FROM users WHERE username = 'farhan_buyer'), 900.00, 'House 22, Road 3', 'Dhaka', '1205', 'Bangladesh', 'Cancelled', 'Failed', 'Mobile Banking', '2025-05-18 13:20:00'),
-- June
('ORD-2025-011', (SELECT id FROM users WHERE username = 'rahul_student'), 1150.00, 'Student Housing Block A, Room 15', 'Dhaka', '1205', 'Bangladesh', 'Delivered', 'Paid', 'Credit Card', '2025-06-07 11:15:00'),
('ORD-2025-012', (SELECT id FROM users WHERE username = 'sarah_professional'), 900.00, 'Tech Park Plaza, Apt 12B', 'Dhaka', '1205', 'Bangladesh', 'Shipped', 'Paid', 'Mobile Banking', '2025-06-25 16:05:00'),
-- July
('ORD-2025-013', (SELECT id FROM users WHERE username = 'mike_developer'), 650.00, 'Digital Hub, Unit 7', 'Dhaka', '1205', 'Bangladesh', 'Processing', 'Paid', 'Credit Card', '2025-07-09 09:45:00'),
('ORD-2025-014', (SELECT id FROM users WHERE username = 'lisa_designer'), 1100.00, 'Creative Quarter, Studio 3', 'Dhaka', '1205', 'Bangladesh', 'Delivered', 'Paid', 'Credit Card', '2025-07-21 14:30:00'),
-- August
('ORD-2025-015', (SELECT id FROM users WHERE username = 'prof_smith'), 1900.00, 'Academic Village, Apt 5A', 'Dhaka', '1205', 'Bangladesh', 'Delivered', 'Paid', 'Mobile Banking', '2025-08-05 10:20:00'),
('ORD-2025-016', (SELECT id FROM users WHERE username = 'dr_ahmed'), 1600.00, 'Faculty Housing, Building 2', 'Dhaka', '1205', 'Bangladesh', 'Shipped', 'Paid', 'Credit Card', '2025-08-24 17:10:00'),
-- September
('ORD-2025-017', (SELECT id FROM users WHERE username = 'abid'), 950.00, 'House 1, Road 1', 'Dhaka', '1205', 'Bangladesh', 'Processing', 'Paid', 'Mobile Banking', '2025-09-06 12:50:00'),
('ORD-2025-018', (SELECT id FROM users WHERE username = 'ayman'), 900.00, 'House 3, Road 3', 'Dhaka', '1205', 'Bangladesh', 'Delivered', 'Paid', 'Credit Card', '2025-09-19 09:05:00'),
-- October
('ORD-2025-019', (SELECT id FROM users WHERE username = 'neha'), 750.00, 'House 4, Road 4', 'Dhaka', '1205', 'Bangladesh', 'Shipped', 'Paid', 'Mobile Banking', '2025-10-08 15:35:00'),
('ORD-2025-020', (SELECT id FROM users WHERE username = 'momin'), 800.00, 'House 5, Road 5', 'Dhaka', '1205', 'Bangladesh', 'Pending', 'Pending', 'Credit Card', '2025-10-25 11:25:00'),
-- November
('ORD-2025-021', (SELECT id FROM users WHERE username = 'siyam'), 600.00, 'House 2, Road 2', 'Dhaka', '1205', 'Bangladesh', 'Delivered', 'Paid', 'Mobile Banking', '2025-11-07 10:10:00'),
('ORD-2025-022', (SELECT id FROM users WHERE username = 'momin'), 500.00, 'House 5, Road 5', 'Dhaka', '1205', 'Bangladesh', 'Cancelled', 'Failed', 'Credit Card', '2025-11-18 13:45:00'),
-- December
('ORD-2025-023', (SELECT id FROM users WHERE username = 'collector_maria'), 700.00, 'Book Street, Villa 9', 'Dhaka', '1205', 'Bangladesh', 'Delivered', 'Paid', 'Credit Card', '2025-12-05 09:30:00'),
('ORD-2025-024', (SELECT id FROM users WHERE username = 'bookworm_alex'), 450.00, 'Literary Lane, House 18', 'Dhaka', '1205', 'Bangladesh', 'Shipped', 'Paid', 'Mobile Banking', '2025-12-22 17:50:00');

-- =============================
-- 2025 ORDER ITEMS
-- (mostly single-item to keep totals consistent)
-- =============================
INSERT IGNORE INTO order_items (order_id, book_id, quantity, price_per_item, seller_id) VALUES
-- Jan
((SELECT id FROM orders WHERE order_number = 'ORD-2025-001'), (SELECT id FROM books WHERE title = 'The Night Circus'), 1, 520.00, (SELECT seller_id FROM books WHERE title = 'The Night Circus')),
((SELECT id FROM orders WHERE order_number = 'ORD-2025-002'), (SELECT id FROM books WHERE title = 'The Silk Roads'), 1, 900.00, (SELECT seller_id FROM books WHERE title = 'The Silk Roads')),
-- Feb
((SELECT id FROM orders WHERE order_number = 'ORD-2025-003'), (SELECT id FROM books WHERE title = 'Simple'), 1, 700.00, (SELECT seller_id FROM books WHERE title = 'Simple')),
((SELECT id FROM orders WHERE order_number = 'ORD-2025-003'), (SELECT id FROM books WHERE title = 'The Very Busy Spider'), 1, 300.00, (SELECT seller_id FROM books WHERE title = 'The Very Busy Spider')),
((SELECT id FROM orders WHERE order_number = 'ORD-2025-004'), (SELECT id FROM books WHERE title = 'Postwar: A History of Europe Since 1945'), 1, 980.00, (SELECT seller_id FROM books WHERE title = 'Postwar: A History of Europe Since 1945')),
-- Mar
((SELECT id FROM orders WHERE order_number = 'ORD-2025-005'), (SELECT id FROM books WHERE title = 'Make Time'), 1, 580.00, (SELECT seller_id FROM books WHERE title = 'Make Time')),
((SELECT id FROM orders WHERE order_number = 'ORD-2025-006'), (SELECT id FROM books WHERE title = 'Discrete Mathematics and Its Applications'), 1, 1700.00, (SELECT seller_id FROM books WHERE title = 'Discrete Mathematics and Its Applications')),
-- Apr
((SELECT id FROM orders WHERE order_number = 'ORD-2025-007'), (SELECT id FROM books WHERE title = 'Artificial Intelligence: A Modern Approach'), 1, 3200.00, (SELECT seller_id FROM books WHERE title = 'Artificial Intelligence: A Modern Approach')),
((SELECT id FROM orders WHERE order_number = 'ORD-2025-008'), (SELECT id FROM books WHERE title = 'Engineering Mechanics: Statics'), 1, 1450.00, (SELECT seller_id FROM books WHERE title = 'Engineering Mechanics: Statics')),
-- May
((SELECT id FROM orders WHERE order_number = 'ORD-2025-009'), (SELECT id FROM books WHERE title = 'Team of Rivals'), 1, 780.00, (SELECT seller_id FROM books WHERE title = 'Team of Rivals')),
((SELECT id FROM orders WHERE order_number = 'ORD-2025-010'), (SELECT id FROM books WHERE title = 'Good to Great'), 1, 900.00, (SELECT seller_id FROM books WHERE title = 'Good to Great')),
-- Jun
((SELECT id FROM orders WHERE order_number = 'ORD-2025-011'), (SELECT id FROM books WHERE title = 'Clean Architecture'), 1, 1150.00, (SELECT seller_id FROM books WHERE title = 'Clean Architecture')),
((SELECT id FROM orders WHERE order_number = 'ORD-2025-012'), (SELECT id FROM books WHERE title = 'The Silk Roads'), 1, 900.00, (SELECT seller_id FROM books WHERE title = 'The Silk Roads')),
-- Jul
((SELECT id FROM orders WHERE order_number = 'ORD-2025-013'), (SELECT id FROM books WHERE title = 'You Don\'t Know JS Yet'), 1, 650.00, (SELECT seller_id FROM books WHERE title = 'You Don\'t Know JS Yet')),
((SELECT id FROM orders WHERE order_number = 'ORD-2025-014'), (SELECT id FROM books WHERE title = 'The Pragmatic Programmer'), 1, 1100.00, (SELECT seller_id FROM books WHERE title = 'The Pragmatic Programmer')),
-- Aug
((SELECT id FROM orders WHERE order_number = 'ORD-2025-015'), (SELECT id FROM books WHERE title = 'Database System Concepts'), 1, 1900.00, (SELECT seller_id FROM books WHERE title = 'Database System Concepts')),
((SELECT id FROM orders WHERE order_number = 'ORD-2025-016'), (SELECT id FROM books WHERE title = 'Linear Algebra and Its Applications'), 1, 1600.00, (SELECT seller_id FROM books WHERE title = 'Linear Algebra and Its Applications')),
-- Sep
((SELECT id FROM orders WHERE order_number = 'ORD-2025-017'), (SELECT id FROM books WHERE title = 'Sapiens: A Brief History of Humankind'), 1, 950.00, (SELECT seller_id FROM books WHERE title = 'Sapiens: A Brief History of Humankind')),
((SELECT id FROM orders WHERE order_number = 'ORD-2025-018'), (SELECT id FROM books WHERE title = 'Zero to One'), 1, 880.00, (SELECT seller_id FROM books WHERE title = 'Zero to One')),
-- Oct
((SELECT id FROM orders WHERE order_number = 'ORD-2025-019'), (SELECT id FROM books WHERE title = 'Harry Potter and the Sorcerer''s Stone'), 1, 750.00, (SELECT seller_id FROM books WHERE title = 'Harry Potter and the Sorcerer''s Stone')),
((SELECT id FROM orders WHERE order_number = 'ORD-2025-020'), (SELECT id FROM books WHERE title = 'The 7 Habits of Highly Effective People'), 1, 800.00, (SELECT seller_id FROM books WHERE title = 'The 7 Habits of Highly Effective People')),
-- Nov
((SELECT id FROM orders WHERE order_number = 'ORD-2025-021'), (SELECT id FROM books WHERE title = 'The Alchemist'), 1, 400.00, (SELECT seller_id FROM books WHERE title = 'The Alchemist')),
((SELECT id FROM orders WHERE order_number = 'ORD-2025-021'), (SELECT id FROM books WHERE title = 'The Power of Now'), 1, 600.00, (SELECT seller_id FROM books WHERE title = 'The Power of Now')),
((SELECT id FROM orders WHERE order_number = 'ORD-2025-022'), (SELECT id FROM books WHERE title = 'The Art of War'), 1, 500.00, (SELECT seller_id FROM books WHERE title = 'The Art of War')),
-- Dec
((SELECT id FROM orders WHERE order_number = 'ORD-2025-023'), (SELECT id FROM books WHERE title = 'The 4-Hour Workweek'), 1, 700.00, (SELECT seller_id FROM books WHERE title = 'The 4-Hour Workweek')),
((SELECT id FROM orders WHERE order_number = 'ORD-2025-024'), (SELECT id FROM books WHERE title = 'Pride and Prejudice'), 1, 450.00, (SELECT seller_id FROM books WHERE title = 'Pride and Prejudice'));


