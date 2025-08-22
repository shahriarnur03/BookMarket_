-- BookMarket Additional Dummy Data (Additive Only)
-- Safe to import multiple times; uses INSERT IGNORE where unique constraints exist

USE bookmarket_DataBase;

-- ==============================================
-- 1) More users (unique username/email)
-- ==============================================
INSERT IGNORE INTO users (username, email, password_hash, user_type, first_name, last_name, phone, address, city, postal_code, country)
VALUES
('nabila_reader', 'nabila.reader@example.com', '$2y$12$ck0pYMHHJP9HVc22rFgvXecPN.Anc1Qs2QmZ2P57EyZ6cL6Ct2iou', 'customer', 'Nabila', 'Rahman', '+8801700001010', 'House 10, Road 12', 'Dhaka', '1207', 'Bangladesh'),
('farhan_buyer', 'farhan.buyer@example.com', '$2y$12$ck0pYMHHJP9HVc22rFgvXecPN.Anc1Qs2QmZ2P57EyZ6cL6Ct2iou', 'customer', 'Farhan', 'Islam', '+8801700001011', 'House 22, Road 3', 'Dhaka', '1205', 'Bangladesh'),
('tasnim_student', 'tasnim.student@example.com', '$2y$12$ck0pYMHHJP9HVc22rFgvXecPN.Anc1Qs2QmZ2P57EyZ6cL6Ct2iou', 'customer', 'Tasnim', 'Akter', '+8801700001012', 'Student Dorm D2-11', 'Dhaka', '1209', 'Bangladesh'),
('rehan_dev', 'rehan.dev@example.com', '$2y$12$ck0pYMHHJP9HVc22rFgvXecPN.Anc1Qs2QmZ2P57EyZ6cL6Ct2iou', 'customer', 'Rehan', 'Mahmud', '+8801700001013', 'Tech Park, Suite 203', 'Dhaka', '1212', 'Bangladesh'),
('mouna_teacher', 'mouna.teacher@example.com', '$2y$12$ck0pYMHHJP9HVc22rFgvXecPN.Anc1Qs2QmZ2P57EyZ6cL6Ct2iou', 'customer', 'Mouna', 'Hossain', '+8801700001014', 'Faculty Housing 6B', 'Dhaka', '1215', 'Bangladesh'),
('kabir_collector', 'kabir.collector@example.com', '$2y$12$ck0pYMHHJP9HVc22rFgvXecPN.Anc1Qs2QmZ2P57EyZ6cL6Ct2iou', 'customer', 'Kabir', 'Chowdhury', '+8801700001015', 'Book Street 4', 'Dhaka', '1206', 'Bangladesh'),
('sadia_mom', 'sadia.mom@example.com', '$2y$12$ck0pYMHHJP9HVc22rFgvXecPN.Anc1Qs2QmZ2P57EyZ6cL6Ct2iou', 'customer', 'Sadia', 'Khatun', '+8801700001016', 'Green Road 45', 'Dhaka', '1205', 'Bangladesh'),
('hasan_exec', 'hasan.exec@example.com', '$2y$12$ck0pYMHHJP9HVc22rFgvXecPN.Anc1Qs2QmZ2P57EyZ6cL6Ct2iou', 'customer', 'Hasan', 'Karim', '+8801700001017', 'Corporate Tower 12F', 'Dhaka', '1217', 'Bangladesh'),
('faria_designer', 'faria.designer@example.com', '$2y$12$ck0pYMHHJP9HVc22rFgvXecPN.Anc1Qs2QmZ2P57EyZ6cL6Ct2iou', 'customer', 'Faria', 'Nawaz', '+8801700001018', 'Creative Block A-3', 'Dhaka', '1211', 'Bangladesh'),
('zayed_student', 'zayed.student@example.com', '$2y$12$ck0pYMHHJP9HVc22rFgvXecPN.Anc1Qs2QmZ2P57EyZ6cL6Ct2iou', 'customer', 'Zayed', 'Rahim', '+8801700001019', 'Student Dorm E1-07', 'Dhaka', '1209', 'Bangladesh');

-- ==============================================
-- 2) More books across categories and statuses
--    Only columns that exist in schema (no stock_quantity)
-- ==============================================
INSERT INTO books (title, author, isbn, description, price, book_condition, cover_image_path, additional_images, status, seller_id, category_id)
VALUES
-- Science & Tech
('Clean Architecture', 'Robert C. Martin', '9780134494166', 'A guide to building robust software architectures.', 1150.00, 'Excellent', 'uploads/books/images (7).jpeg', NULL, 'approved', (SELECT id FROM users WHERE username = 'rehan_dev'), (SELECT id FROM categories WHERE name = 'Science & Tech')),
('Artificial Intelligence: A Modern Approach', 'Russell & Norvig', '9780134610993', 'Comprehensive AI textbook covering theory and practice.', 3200.00, 'Good', 'uploads/books/images (8).jpeg', NULL, 'approved', (SELECT id FROM users WHERE username = 'ayman'), (SELECT id FROM categories WHERE name = 'Science & Tech')),
('You Don\'t Know JS Yet', 'Kyle Simpson', '9781091210099', 'Deep dive into modern JavaScript.', 650.00, 'New', 'uploads/books/images (6).jpeg', NULL, 'pending', (SELECT id FROM users WHERE username = 'mike_developer'), (SELECT id FROM categories WHERE name = 'Science & Tech')),

-- Academic
('Discrete Mathematics and Its Applications', 'Kenneth Rosen', '9780073383095', 'Foundational topics in discrete math.', 1700.00, 'Excellent', 'uploads/books/images (4).jpeg', NULL, 'approved', (SELECT id FROM users WHERE username = 'tasnim_student'), (SELECT id FROM categories WHERE name = 'Academic')),
('Engineering Mechanics: Statics', 'J. L. Meriam', '9781118396813', 'Statics for engineering students.', 1450.00, 'Good', 'uploads/books/images (2).jpeg', NULL, 'approved', (SELECT id FROM users WHERE username = 'zayed_student'), (SELECT id FROM categories WHERE name = 'Academic')),
('Modern Control Engineering', 'Katsuhiko Ogata', '9780136156734', 'Classical and modern control theory.', 2100.00, 'Fair', 'uploads/books/images (3).jpeg', NULL, 'pending', (SELECT id FROM users WHERE username = 'dr_ahmed'), (SELECT id FROM categories WHERE name = 'Academic')),

-- Business
('Purple Cow', 'Seth Godin', '9781591843177', 'Transform your business by being remarkable.', 700.00, 'Good', 'uploads/books/images (12).jpeg', NULL, 'approved', (SELECT id FROM users WHERE username = 'hasan_exec'), (SELECT id FROM categories WHERE name = 'Business')),
('Good to Great', 'Jim Collins', '9780066620992', 'Why some companies make the leap.', 900.00, 'Excellent', 'uploads/books/images (11).jpeg', NULL, 'approved', (SELECT id FROM users WHERE username = 'sarah_professional'), (SELECT id FROM categories WHERE name = 'Business')),
('The Personal MBA', 'Josh Kaufman', '9781591845577', 'Master the art of business.', 800.00, 'New', 'uploads/books/images (10).jpeg', NULL, 'pending', (SELECT id FROM users WHERE username = 'ayman'), (SELECT id FROM categories WHERE name = 'Business')),

-- Fiction
('The Night Circus', 'Erin Morgenstern', '9780385534635', 'A phantasmagorical fairy tale of imagination.', 520.00, 'Excellent', 'uploads/books/download (3).jpeg', NULL, 'approved', (SELECT id FROM users WHERE username = 'nabila_reader'), (SELECT id FROM categories WHERE name = 'Fiction')),
('The Book Thief', 'Markus Zusak', '9780375842207', 'A story about the power of words and reading.', 560.00, 'Good', 'uploads/books/images (5).jpeg', NULL, 'approved', (SELECT id FROM users WHERE username = 'kabir_collector'), (SELECT id FROM categories WHERE name = 'Fiction')),
('Normal People', 'Sally Rooney', '9780571334650', 'Complex relationship between two people.', 480.00, 'Fair', 'uploads/books/download (5).jpeg', NULL, 'pending', (SELECT id FROM users WHERE username = 'faria_designer'), (SELECT id FROM categories WHERE name = 'Fiction')),

-- Children
('The Very Busy Spider', 'Eric Carle', '9780399229190', 'Beloved picture book about persistence.', 300.00, 'Excellent', 'uploads/books/download (6).jpeg', NULL, 'approved', (SELECT id FROM users WHERE username = 'sadia_mom'), (SELECT id FROM categories WHERE name = 'Children')),
('The Tale of Peter Rabbit', 'Beatrix Potter', '9780723247708', 'Classic children\'s story.', 250.00, 'Good', 'uploads/books/download.png', NULL, 'approved', (SELECT id FROM users WHERE username = 'neha'), (SELECT id FROM categories WHERE name = 'Children')),
('Diary of a Wimpy Kid', 'Jeff Kinney', '9781419749155', 'Humorous children\'s novel.', 350.00, 'New', 'uploads/books/images (14).jpeg', NULL, 'pending', (SELECT id FROM users WHERE username = 'zayed_student'), (SELECT id FROM categories WHERE name = 'Children')),

-- History
('Team of Rivals', 'Doris Kearns Goodwin', '9780684824901', 'Political genius of Abraham Lincoln.', 780.00, 'Good', 'uploads/books/images (2).jpeg', NULL, 'approved', (SELECT id FROM users WHERE username = 'mouna_teacher'), (SELECT id FROM categories WHERE name = 'History')),
('The Silk Roads', 'Peter Frankopan', '9781101912379', 'A new history of the world.', 900.00, 'Excellent', 'uploads/books/download (4).jpeg', NULL, 'approved', (SELECT id FROM users WHERE username = 'kabir_collector'), (SELECT id FROM categories WHERE name = 'History')),
('Postwar: A History of Europe Since 1945', 'Tony Judt', '9780143037757', 'Europe\'s story after WWII.', 980.00, 'Fair', 'uploads/books/images (1).jpeg', NULL, 'pending', (SELECT id FROM users WHERE username = 'hasan_exec'), (SELECT id FROM categories WHERE name = 'History')),

-- Self-Help
('Deep Work', 'Cal Newport', '9781455586691', 'Rules for focused success in a distracted world.', 650.00, 'Excellent', 'uploads/books/images (6).jpeg', NULL, 'approved', (SELECT id FROM users WHERE username = 'rehan_dev'), (SELECT id FROM categories WHERE name = 'Self-Help')),
('Essentialism', 'Greg McKeown', '9780804137386', 'The disciplined pursuit of less.', 600.00, 'Good', 'uploads/books/images (5).jpeg', NULL, 'approved', (SELECT id FROM users WHERE username = 'nabila_reader'), (SELECT id FROM categories WHERE name = 'Self-Help')),
('Make Time', 'Knapp & Zeratsky', '9780385543477', 'Focus on what matters every day.', 580.00, 'New', 'uploads/books/images (9).jpeg', NULL, 'pending', (SELECT id FROM users WHERE username = 'faria_designer'), (SELECT id FROM categories WHERE name = 'Self-Help')),

-- Cookbooks
('Simple', 'Yotam Ottolenghi', '9781607749165', 'Vibrant vegetable-forward recipes.', 700.00, 'Excellent', 'uploads/books/images (10).jpeg', NULL, 'approved', (SELECT id FROM users WHERE username = 'sadia_mom'), (SELECT id FROM categories WHERE name = 'Cookbooks')),
('Salt\, Smoke\, Time', 'Will Horowitz', '9780062427106', 'Traditional methods of preserving food.', 850.00, 'Good', 'uploads/books/images (9).jpeg', NULL, 'approved', (SELECT id FROM users WHERE username = 'kabir_collector'), (SELECT id FROM categories WHERE name = 'Cookbooks')),
('The Flavor Bible', 'Karen Page', '9780316118408', 'The essential guide to culinary creativity.', 950.00, 'Fair', 'uploads/books/images (8).jpeg', NULL, 'pending', (SELECT id FROM users WHERE username = 'mouna_teacher'), (SELECT id FROM categories WHERE name = 'Cookbooks'));

-- ==============================================
-- 3) More orders with varied statuses
-- ==============================================
INSERT INTO orders (order_number, user_id, total_amount, shipping_address, shipping_city, shipping_postal_code, shipping_country, order_status, payment_status, payment_method, order_date)
VALUES
('ORD-2024-101', (SELECT id FROM users WHERE username = 'nabila_reader'), 520.00, 'House 10, Road 12', 'Dhaka', '1207', 'Bangladesh', 'Delivered', 'Paid', 'Credit Card', '2024-03-01 10:00:00'),
('ORD-2024-102', (SELECT id FROM users WHERE username = 'kabir_collector'), 1460.00, 'Book Street 4', 'Dhaka', '1206', 'Bangladesh', 'Delivered', 'Paid', 'Mobile Banking', '2024-03-03 12:30:00'),
('ORD-2024-103', (SELECT id FROM users WHERE username = 'sadia_mom'), 1000.00, 'Green Road 45', 'Dhaka', '1205', 'Bangladesh', 'Shipped', 'Paid', 'Credit Card', '2024-03-05 09:45:00'),
('ORD-2024-104', (SELECT id FROM users WHERE username = 'hasan_exec'), 980.00, 'Corporate Tower 12F', 'Dhaka', '1217', 'Bangladesh', 'Processing', 'Paid', 'Mobile Banking', '2024-03-07 16:20:00'),
('ORD-2024-105', (SELECT id FROM users WHERE username = 'faria_designer'), 580.00, 'Creative Block A-3', 'Dhaka', '1211', 'Bangladesh', 'Pending', 'Pending', 'Credit Card', '2024-03-08 11:10:00'),
('ORD-2024-106', (SELECT id FROM users WHERE username = 'tasnim_student'), 1700.00, 'Student Dorm D2-11', 'Dhaka', '1209', 'Bangladesh', 'Delivered', 'Paid', 'Mobile Banking', '2024-03-09 13:50:00'),
('ORD-2024-107', (SELECT id FROM users WHERE username = 'rehan_dev'), 1800.00, 'Tech Park, Suite 203', 'Dhaka', '1212', 'Bangladesh', 'Delivered', 'Paid', 'Credit Card', '2024-03-11 18:40:00'),
('ORD-2024-108', (SELECT id FROM users WHERE username = 'zayed_student'), 1450.00, 'Student Dorm E1-07', 'Dhaka', '1209', 'Bangladesh', 'Shipped', 'Paid', 'Mobile Banking', '2024-03-12 10:05:00'),
('ORD-2024-109', (SELECT id FROM users WHERE username = 'mouna_teacher'), 780.00, 'Faculty Housing 6B', 'Dhaka', '1215', 'Bangladesh', 'Processing', 'Paid', 'Credit Card', '2024-03-13 14:25:00'),
('ORD-2024-110', (SELECT id FROM users WHERE username = 'farhan_buyer'), 900.00, 'House 22, Road 3', 'Dhaka', '1205', 'Bangladesh', 'Pending', 'Pending', 'Mobile Banking', '2024-03-14 09:30:00'),
('ORD-2024-111', (SELECT id FROM users WHERE username = 'hasan_exec'), 1600.00, 'Corporate Tower 12F', 'Dhaka', '1217', 'Bangladesh', 'Cancelled', 'Failed', 'Credit Card', '2024-03-16 13:10:00'),
('ORD-2024-112', (SELECT id FROM users WHERE username = 'kabir_collector'), 900.00, 'Book Street 4', 'Dhaka', '1206', 'Bangladesh', 'Delivered', 'Paid', 'Credit Card', '2024-03-18 17:45:00');

-- ==============================================
-- 4) Order items for the above orders
-- ==============================================
INSERT INTO order_items (order_id, book_id, quantity, price_per_item, seller_id)
VALUES
((SELECT id FROM orders WHERE order_number = 'ORD-2024-101'), (SELECT id FROM books WHERE title = 'The Night Circus'), 1, 520.00, (SELECT seller_id FROM books WHERE title = 'The Night Circus')),
((SELECT id FROM orders WHERE order_number = 'ORD-2024-102'), (SELECT id FROM books WHERE title = 'The Book Thief'), 1, 560.00, (SELECT seller_id FROM books WHERE title = 'The Book Thief')),
((SELECT id FROM orders WHERE order_number = 'ORD-2024-102'), (SELECT id FROM books WHERE title = 'Purple Cow'), 1, 700.00, (SELECT seller_id FROM books WHERE title = 'Purple Cow')),
((SELECT id FROM orders WHERE order_number = 'ORD-2024-103'), (SELECT id FROM books WHERE title = 'The Very Busy Spider'), 1, 300.00, (SELECT seller_id FROM books WHERE title = 'The Very Busy Spider')),
((SELECT id FROM orders WHERE order_number = 'ORD-2024-103'), (SELECT id FROM books WHERE title = 'Simple'), 1, 700.00, (SELECT seller_id FROM books WHERE title = 'Simple')),
((SELECT id FROM orders WHERE order_number = 'ORD-2024-104'), (SELECT id FROM books WHERE title = 'Postwar: A History of Europe Since 1945'), 1, 980.00, (SELECT seller_id FROM books WHERE title = 'Postwar: A History of Europe Since 1945')),
((SELECT id FROM orders WHERE order_number = 'ORD-2024-105'), (SELECT id FROM books WHERE title = 'Make Time'), 1, 580.00, (SELECT seller_id FROM books WHERE title = 'Make Time')),
((SELECT id FROM orders WHERE order_number = 'ORD-2024-106'), (SELECT id FROM books WHERE title = 'Discrete Mathematics and Its Applications'), 1, 1700.00, (SELECT seller_id FROM books WHERE title = 'Discrete Mathematics and Its Applications')),
((SELECT id FROM orders WHERE order_number = 'ORD-2024-107'), (SELECT id FROM books WHERE title = 'Deep Work'), 1, 650.00, (SELECT seller_id FROM books WHERE title = 'Deep Work')),
((SELECT id FROM orders WHERE order_number = 'ORD-2024-107'), (SELECT id FROM books WHERE title = 'Clean Architecture'), 1, 1150.00, (SELECT seller_id FROM books WHERE title = 'Clean Architecture')),
((SELECT id FROM orders WHERE order_number = 'ORD-2024-108'), (SELECT id FROM books WHERE title = 'Engineering Mechanics: Statics'), 1, 1450.00, (SELECT seller_id FROM books WHERE title = 'Engineering Mechanics: Statics')),
((SELECT id FROM orders WHERE order_number = 'ORD-2024-109'), (SELECT id FROM books WHERE title = 'Team of Rivals'), 1, 780.00, (SELECT seller_id FROM books WHERE title = 'Team of Rivals')),
((SELECT id FROM orders WHERE order_number = 'ORD-2024-110'), (SELECT id FROM books WHERE title = 'Good to Great'), 1, 900.00, (SELECT seller_id FROM books WHERE title = 'Good to Great')),
((SELECT id FROM orders WHERE order_number = 'ORD-2024-111'), (SELECT id FROM books WHERE title = 'Artificial Intelligence: A Modern Approach'), 1, 3200.00, (SELECT seller_id FROM books WHERE title = 'Artificial Intelligence: A Modern Approach')),
((SELECT id FROM orders WHERE order_number = 'ORD-2024-112'), (SELECT id FROM books WHERE title = 'The Silk Roads'), 1, 900.00, (SELECT seller_id FROM books WHERE title = 'The Silk Roads'));

-- ==============================================
-- 5) More cart items (unique per user/book)
-- ==============================================
INSERT IGNORE INTO cart (user_id, book_id, quantity)
VALUES
((SELECT id FROM users WHERE username = 'farhan_buyer'), (SELECT id FROM books WHERE title = 'Normal People'), 1),
((SELECT id FROM users WHERE username = 'faria_designer'), (SELECT id FROM books WHERE title = 'Essentialism'), 1),
((SELECT id FROM users WHERE username = 'rehan_dev'), (SELECT id FROM books WHERE title = 'You Don\'t Know JS Yet'), 1),
((SELECT id FROM users WHERE username = 'tasnim_student'), (SELECT id FROM books WHERE title = 'Engineering Mechanics: Statics'), 1),
((SELECT id FROM users WHERE username = 'zayed_student'), (SELECT id FROM books WHERE title = 'Diary of a Wimpy Kid'), 1),
((SELECT id FROM users WHERE username = 'sadia_mom'), (SELECT id FROM books WHERE title = 'The Tale of Peter Rabbit'), 1);

-- ==============================================
-- 6) More book reviews (ratings 1..5)
-- ==============================================
INSERT INTO book_reviews (book_id, user_id, rating, review_text)
VALUES
((SELECT id FROM books WHERE title = 'Clean Architecture'), (SELECT id FROM users WHERE username = 'mike_developer'), 5, 'A must-read for professional developers. Clear principles and trade-offs.'),
((SELECT id FROM books WHERE title = 'Clean Architecture'), (SELECT id FROM users WHERE username = 'rehan_dev'), 4, 'Great patterns and guidance. Some chapters are opinionated.'),
((SELECT id FROM books WHERE title = 'Artificial Intelligence: A Modern Approach'), (SELECT id FROM users WHERE username = 'sarah_professional'), 5, 'The definitive reference on AI. Dense but invaluable.'),
((SELECT id FROM books WHERE title = 'You Don\'t Know JS Yet'), (SELECT id FROM users WHERE username = 'faria_designer'), 4, 'Deep JS insights explained clearly. Loved it.'),
((SELECT id FROM books WHERE title = 'Discrete Mathematics and Its Applications'), (SELECT id FROM users WHERE username = 'tasnim_student'), 5, 'Perfect companion for my course. Exercises are helpful.'),
((SELECT id FROM books WHERE title = 'Engineering Mechanics: Statics'), (SELECT id FROM users WHERE username = 'zayed_student'), 4, 'Well-structured and practical examples.'),
((SELECT id FROM books WHERE title = 'Purple Cow'), (SELECT id FROM users WHERE username = 'hasan_exec'), 5, 'Remarkable ideas. Instantly applicable in marketing.'),
((SELECT id FROM books WHERE title = 'Good to Great'), (SELECT id FROM users WHERE username = 'farhan_buyer'), 4, 'Compelling research and case studies.'),
((SELECT id FROM books WHERE title = 'The Personal MBA'), (SELECT id FROM users WHERE username = 'ayman'), 3, 'Good overview, but surface-level in places.'),
((SELECT id FROM books WHERE title = 'The Night Circus'), (SELECT id FROM users WHERE username = 'nabila_reader'), 5, 'Magical and atmospheric. Could not put it down.'),
((SELECT id FROM books WHERE title = 'The Book Thief'), (SELECT id FROM users WHERE username = 'kabir_collector'), 5, 'Heartbreaking and beautiful.'),
((SELECT id FROM books WHERE title = 'Normal People'), (SELECT id FROM users WHERE username = 'faria_designer'), 4, 'Raw and honest portrayal of relationships.'),
((SELECT id FROM books WHERE title = 'The Very Busy Spider'), (SELECT id FROM users WHERE username = 'sadia_mom'), 5, 'Kids adore it. Lovely illustrations.'),
((SELECT id FROM books WHERE title = 'The Tale of Peter Rabbit'), (SELECT id FROM users WHERE username = 'neha'), 5, 'All-time classic for bedtime.'),
((SELECT id FROM books WHERE title = 'Diary of a Wimpy Kid'), (SELECT id FROM users WHERE username = 'zayed_student'), 4, 'Funny and relatable.'),
((SELECT id FROM books WHERE title = 'Team of Rivals'), (SELECT id FROM users WHERE username = 'mouna_teacher'), 5, 'Exceptional narrative history.'),
((SELECT id FROM books WHERE title = 'The Silk Roads'), (SELECT id FROM users WHERE username = 'kabir_collector'), 4, 'Expansive and enlightening.'),
((SELECT id FROM books WHERE title = 'Postwar: A History of Europe Since 1945'), (SELECT id FROM users WHERE username = 'hasan_exec'), 4, 'Comprehensive and insightful.'),
((SELECT id FROM books WHERE title = 'Deep Work'), (SELECT id FROM users WHERE username = 'rehan_dev'), 5, 'Changed how I plan my day.'),
((SELECT id FROM books WHERE title = 'Essentialism'), (SELECT id FROM users WHERE username = 'nabila_reader'), 5, 'Prioritization distilled to its essence.'),
((SELECT id FROM books WHERE title = 'Make Time'), (SELECT id FROM users WHERE username = 'faria_designer'), 4, 'Practical tactics, easy to adopt.'),
((SELECT id FROM books WHERE title = 'Simple'), (SELECT id FROM users WHERE username = 'sadia_mom'), 5, 'Delicious recipes that truly are simple.'),
((SELECT id FROM books WHERE title = 'Salt\, Smoke\, Time'), (SELECT id FROM users WHERE username = 'kabir_collector'), 4, 'Great for preservation techniques.'),
((SELECT id FROM books WHERE title = 'The Flavor Bible'), (SELECT id FROM users WHERE username = 'mouna_teacher'), 5, 'Inspires creativity in the kitchen.');

-- End of additional data


