-- BookMarket 2024-2025 Activity Seed (users, books, approvals, reviews)
-- Adds time-distributed signups, listings, approvals, and reviews

USE bookmarket_DataBase;

-- ==============================================
-- 1) Backdated user signups across 2024-2025
-- ==============================================
INSERT IGNORE INTO users (username, email, password_hash, user_type, first_name, last_name, phone, address, city, postal_code, country, created_at)
VALUES
('tahsin_reader', 'tahsin.reader@example.com', '$2y$12$ck0pYMHHJP9HVc22rFgvXecPN.Anc1Qs2QmZ2P57EyZ6cL6Ct2iou', 'customer', 'Tahsin', 'Islam', '+8801700002010', 'Lane 12, House 8', 'Dhaka', '1207', 'Bangladesh', '2024-01-10 08:00:00'),
('nabil_seller', 'nabil.seller@example.com', '$2y$12$ck0pYMHHJP9HVc22rFgvXecPN.Anc1Qs2QmZ2P57EyZ6cL6Ct2iou', 'customer', 'Nabil', 'Hossain', '+8801700002011', 'Road 5, House 16', 'Dhaka', '1206', 'Bangladesh', '2024-03-14 09:20:00'),
('misha_student', 'misha.student@example.com', '$2y$12$ck0pYMHHJP9HVc22rFgvXecPN.Anc1Qs2QmZ2P57EyZ6cL6Ct2iou', 'customer', 'Misha', 'Ahmed', '+8801700002012', 'Dorm F-203', 'Dhaka', '1209', 'Bangladesh', '2024-07-22 10:45:00'),
('araf_dev', 'araf.dev@example.com', '$2y$12$ck0pYMHHJP9HVc22rFgvXecPN.Anc1Qs2QmZ2P57EyZ6cL6Ct2iou', 'customer', 'Araf', 'Rahman', '+8801700002013', 'Tech Park, Suite 120', 'Dhaka', '1212', 'Bangladesh', '2025-02-05 13:15:00'),
('tania_teacher', 'tania.teacher@example.com', '$2y$12$ck0pYMHHJP9HVc22rFgvXecPN.Anc1Qs2QmZ2P57EyZ6cL6Ct2iou', 'customer', 'Tania', 'Karim', '+8801700002014', 'Faculty Housing A-4', 'Dhaka', '1215', 'Bangladesh', '2025-05-18 11:30:00');

-- ==============================================
-- 2) Listings with created_at spanning months, mixed statuses
-- ==============================================
INSERT INTO books (title, author, isbn, description, price, book_condition, cover_image_path, additional_images, status, seller_id, category_id, created_at)
VALUES
('Refactoring', 'Martin Fowler', '9780201485677', 'Improving the design of existing code.', 1300.00, 'Good', 'uploads/books/images (6).jpeg', NULL, 'approved', (SELECT id FROM users WHERE username = 'nabil_seller'), (SELECT id FROM categories WHERE name = 'Science & Tech'), '2024-03-20 10:00:00'),
('Hooked', 'Nir Eyal', '9781591847786', 'How to build habit-forming products.', 750.00, 'Excellent', 'uploads/books/images (12).jpeg', NULL, 'approved', (SELECT id FROM users WHERE username = 'araf_dev'), (SELECT id FROM categories WHERE name = 'Business'), '2025-02-10 12:05:00'),
('Educated', 'Tara Westover', '9780399590504', 'A memoir about a woman who leaves her survivalist family.', 500.00, 'Fair', 'uploads/books/download (2).jpeg', NULL, 'pending', (SELECT id FROM users WHERE username = 'tahsin_reader'), (SELECT id FROM categories WHERE name = 'Fiction'), '2024-01-15 09:15:00'),
('Grit', 'Angela Duckworth', '9781501111105', 'The power of passion and perseverance.', 600.00, 'New', 'uploads/books/images (5).jpeg', NULL, 'pending', (SELECT id FROM users WHERE username = 'misha_student'), (SELECT id FROM categories WHERE name = 'Self-Help'), '2024-07-28 11:25:00');

-- ==============================================
-- 3) Admin approvals distributed over time (audit log)
-- ==============================================
INSERT INTO admin_actions (admin_id, action_type, action_description, target_table, target_id, created_at)
VALUES
((SELECT id FROM users WHERE username = 'admin'), 'book_approved', 'Approved book: Refactoring', 'books', (SELECT id FROM books WHERE title = 'Refactoring'), '2024-03-22 09:30:00'),
((SELECT id FROM users WHERE username = 'admin'), 'book_approved', 'Approved book: Hooked', 'books', (SELECT id FROM books WHERE title = 'Hooked'), '2025-02-12 14:45:00');

-- ==============================================
-- 4) Reviews across 2024-2025 with created_at
-- ==============================================
INSERT INTO book_reviews (book_id, user_id, rating, review_text, created_at)
VALUES
((SELECT id FROM books WHERE title = 'Refactoring'), (SELECT id FROM users WHERE username = 'mike_developer'), 5, 'Practical techniques that pay off quickly.', '2024-04-01 10:10:00'),
((SELECT id FROM books WHERE title = 'Refactoring'), (SELECT id FROM users WHERE username = 'sarah_professional'), 4, 'Great concepts, a bit dated examples.', '2024-04-03 12:20:00'),
((SELECT id FROM books WHERE title = 'Hooked'), (SELECT id FROM users WHERE username = 'lisa_designer'), 5, 'Clear framework for product habit loops.', '2025-02-20 09:05:00'),
((SELECT id FROM books WHERE title = 'Educated'), (SELECT id FROM users WHERE username = 'nabila_reader'), 5, 'Powerful and inspiring memoir.', '2024-02-01 08:55:00'),
((SELECT id FROM books WHERE title = 'Grit'), (SELECT id FROM users WHERE username = 'faria_designer'), 4, 'Motivational and research-backed.', '2024-08-05 15:40:00');


