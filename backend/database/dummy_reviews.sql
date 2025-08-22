-- BookMarket Dummy Reviews Data
-- This script adds comprehensive book reviews to make the website look fully functional

USE bookmarket_DataBase;

-- =====================================================
-- ADD COMPREHENSIVE BOOK REVIEWS
-- =====================================================

-- Academic Books Reviews
INSERT INTO book_reviews (book_id, user_id, rating, review_text) VALUES
-- Calculus textbook reviews
((SELECT id FROM books WHERE title = 'Calculus: Early Transcendentals'), (SELECT id FROM users WHERE username = 'fatima_student'), 5, 'Excellent textbook! The explanations are clear and the examples are very helpful. Perfect for self-study.'),
((SELECT id FROM books WHERE title = 'Calculus: Early Transcendentals'), (SELECT id FROM users WHERE username = 'omar_student'), 4, 'Great book for learning calculus. The exercises are challenging but manageable.'),
((SELECT id FROM books WHERE title = 'Calculus: Early Transcendentals'), (SELECT id FROM users WHERE username = 'dr_ahmed'), 5, 'As a professor, I highly recommend this book. It covers all essential topics comprehensively.'),

-- Physics textbook reviews
((SELECT id FROM books WHERE title = 'Physics for Scientists and Engineers'), (SELECT id FROM users WHERE username = 'rahul_student'), 4, 'Very good physics book with practical applications. The problem sets are excellent.'),
((SELECT id FROM books WHERE title = 'Physics for Scientists and Engineers'), (SELECT id FROM users WHERE username = 'prof_smith'), 5, 'Outstanding physics textbook. Used this in my courses for years.'),
((SELECT id FROM books WHERE title = 'Physics for Scientists and Engineers'), (SELECT id FROM users WHERE username = 'sarah_professional'), 4, 'Great reference book for engineering applications. Clear explanations and good examples.'),

-- Computer Science Books Reviews
((SELECT id FROM books WHERE title = 'Introduction to Algorithms'), (SELECT id FROM users WHERE username = 'mike_developer'), 5, 'The bible of algorithms! Every programmer should read this book.'),
((SELECT id FROM books WHERE title = 'Introduction to Algorithms'), (SELECT id FROM users WHERE username = 'lisa_designer'), 4, 'Comprehensive coverage of algorithms. Great for technical interviews.'),
((SELECT id FROM books WHERE title = 'Introduction to Algorithms'), (SELECT id FROM users WHERE username = 'sarah_professional'), 5, 'Essential reading for any serious software engineer. Excellent explanations.'),

-- Design Patterns Reviews
((SELECT id FROM books WHERE title = 'Design Patterns'), (SELECT id FROM users WHERE username = 'mike_developer'), 5, 'Classic book on design patterns. Changed how I think about software design.'),
((SELECT id FROM books WHERE title = 'Design Patterns'), (SELECT id FROM users WHERE username = 'lisa_designer'), 4, 'Great reference for common design patterns. Very practical examples.'),
((SELECT id FROM books WHERE title = 'Design Patterns'), (SELECT id FROM users WHERE username = 'prof_smith'), 5, 'Must-read for software engineering students. Excellent coverage of patterns.'),

-- Fiction Books Reviews
((SELECT id FROM books WHERE title = 'Pride and Prejudice'), (SELECT id FROM users WHERE username = 'bookworm_alex'), 5, 'Timeless classic! Jane Austen''s wit and social commentary are brilliant.'),
((SELECT id FROM books WHERE title = 'Pride and Prejudice'), (SELECT id FROM users WHERE username = 'collector_maria'), 5, 'One of my favorite books ever. The romance and social satire are perfect.'),
((SELECT id FROM books WHERE title = 'Pride and Prejudice'), (SELECT id FROM users WHERE username = 'fatima_student'), 4, 'Beautiful writing and engaging story. A must-read classic.'),

-- Jane Eyre Reviews
((SELECT id FROM books WHERE title = 'Jane Eyre'), (SELECT id FROM users WHERE username = 'bookworm_alex'), 5, 'Gothic romance at its finest. Charlotte Brontë''s masterpiece.'),
((SELECT id FROM books WHERE title = 'Jane Eyre'), (SELECT id FROM users WHERE username = 'rahul_student'), 4, 'Compelling story with strong female protagonist. Very engaging read.'),
((SELECT id FROM books WHERE title = 'Jane Eyre'), (SELECT id FROM users WHERE username = 'dr_ahmed'), 5, 'Literary masterpiece. The character development is exceptional.'),

-- Children's Books Reviews
((SELECT id FROM books WHERE title = 'Where the Wild Things Are'), (SELECT id FROM users WHERE username = 'neha'), 5, 'My children love this book! The illustrations are magical.'),
((SELECT id FROM books WHERE title = 'Where the Wild Things Are'), (SELECT id FROM users WHERE username = 'fatima_student'), 5, 'Perfect bedtime story. The imagination and creativity are wonderful.'),
((SELECT id FROM books WHERE title = 'Where the Wild Things Are'), (SELECT id FROM users WHERE username = 'prof_smith'), 5, 'Classic children''s literature. Teaches important lessons about imagination and home.'),

-- Charlotte's Web Reviews
((SELECT id FROM books WHERE title = 'Charlotte''s Web'), (SELECT id FROM users WHERE username = 'prof_smith'), 5, 'Beautiful story about friendship and sacrifice. Perfect for young readers.'),
((SELECT id FROM books WHERE title = 'Charlotte''s Web'), (SELECT id FROM users WHERE username = 'bookworm_alex'), 5, 'Heartwarming tale that teaches important life lessons. A true classic.'),
((SELECT id FROM books WHERE title = 'Charlotte''s Web'), (SELECT id FROM users WHERE username = 'omar_student'), 4, 'Great book for children. The story is engaging and meaningful.'),

-- History Books Reviews
((SELECT id FROM books WHERE title = 'Guns, Germs, and Steel'), (SELECT id FROM users WHERE username = 'momin'), 5, 'Fascinating perspective on human history. Jared Diamond''s insights are eye-opening.'),
((SELECT id FROM books WHERE title = 'Guns, Germs, and Steel'), (SELECT id FROM users WHERE username = 'dr_ahmed'), 5, 'Pulitzer Prize winner for good reason. Revolutionary approach to history.'),
((SELECT id FROM books WHERE title = 'Guns, Germs, and Steel'), (SELECT id FROM users WHERE username = 'prof_smith'), 4, 'Excellent analysis of why societies developed differently. Very thought-provoking.'),

-- Self-Help Books Reviews
((SELECT id FROM books WHERE title = 'Think and Grow Rich'), (SELECT id FROM users WHERE username = 'bookworm_alex'), 4, 'Classic self-help book with timeless principles. Very motivational.'),
((SELECT id FROM books WHERE title = 'Think and Grow Rich'), (SELECT id FROM users WHERE username = 'mike_developer'), 3, 'Good principles but some concepts are dated. Still valuable insights.'),
((SELECT id FROM books WHERE title = 'Think and Grow Rich'), (SELECT id FROM users WHERE username = 'sarah_professional'), 4, 'Essential reading for anyone interested in success principles.'),

-- The Power of Now Reviews
((SELECT id FROM books WHERE title = 'The Power of Now'), (SELECT id FROM users WHERE username = 'collector_maria'), 5, 'Life-changing book! Eckhart Tolle''s insights are profound.'),
((SELECT id FROM books WHERE title = 'The Power of Now'), (SELECT id FROM users WHERE username = 'lisa_designer'), 4, 'Great book on mindfulness and living in the present. Very helpful.'),
((SELECT id FROM books WHERE title = 'The Power of Now'), (SELECT id FROM users WHERE username = 'dr_ahmed'), 5, 'Excellent guide to spiritual enlightenment. Highly recommended.'),

-- Cookbooks Reviews
((SELECT id FROM books WHERE title = 'The Joy of Cooking'), (SELECT id FROM users WHERE username = 'lisa_designer'), 5, 'Comprehensive cookbook with thousands of reliable recipes. A kitchen essential!'),
((SELECT id FROM books WHERE title = 'The Joy of Cooking'), (SELECT id FROM users WHERE username = 'fatima_student'), 4, 'Great reference book for home cooking. Recipes are well-tested and delicious.'),
((SELECT id FROM books WHERE title = 'The Joy of Cooking'), (SELECT id FROM users WHERE username = 'omar_student'), 5, 'The ultimate cookbook! Every recipe I''ve tried has been perfect.'),

-- Business Books Reviews
((SELECT id FROM books WHERE title = 'Principles of Economics'), (SELECT id FROM users WHERE username = 'ayman'), 4, 'Excellent introduction to economics. Clear explanations and good examples.'),
((SELECT id FROM books WHERE title = 'Principles of Economics'), (SELECT id FROM users WHERE username = 'sarah_professional'), 5, 'Great textbook for learning economics fundamentals. Very comprehensive.'),
((SELECT id FROM books WHERE title = 'Principles of Economics'), (SELECT id FROM users WHERE username = 'mike_developer'), 4, 'Good overview of economic principles. Well-written and accessible.'),

-- Marketing Management Reviews
((SELECT id FROM books WHERE title = 'Marketing Management'), (SELECT id FROM users WHERE username = 'sarah_professional'), 5, 'The definitive guide to marketing. Philip Kotler is the authority.'),
((SELECT id FROM books WHERE title = 'Marketing Management'), (SELECT id FROM users WHERE username = 'lisa_designer'), 4, 'Comprehensive marketing textbook. Great for both students and professionals.'),
((SELECT id FROM books WHERE title = 'Marketing Management'), (SELECT id FROM users WHERE username = 'prof_smith'), 5, 'Essential reading for marketing students. Covers all key concepts thoroughly.');

-- =====================================================
-- ADD SOME CART ITEMS FOR REALISTIC SHOPPING
-- =====================================================

-- Add items to various user carts
INSERT INTO cart (user_id, book_id, quantity) VALUES
-- Rahul's cart
((SELECT id FROM users WHERE username = 'rahul_student'), (SELECT id FROM books WHERE title = 'The Pragmatic Programmer'), 1),
((SELECT id FROM users WHERE username = 'rahul_student'), (SELECT id FROM books WHERE title = 'Design Patterns'), 1),

-- Fatima's cart
((SELECT id FROM users WHERE username = 'fatima_student'), (SELECT id FROM books WHERE title = 'The Alchemist'), 1),
((SELECT id FROM users WHERE username = 'fatima_student'), (SELECT id FROM books WHERE title = 'Goodnight Moon'), 1),

-- Sarah's cart
((SELECT id FROM users WHERE username = 'sarah_professional'), (SELECT id FROM books WHERE title = 'Strategic Management'), 1),
((SELECT id FROM users WHERE username = 'sarah_professional'), (SELECT id FROM books WHERE title = 'Mindset: The New Psychology of Success'), 1),

-- Mike's cart
((SELECT id FROM users WHERE username = 'mike_developer'), (SELECT id FROM books WHERE username = 'Database System Concepts'), 1),

-- Alex's cart
((SELECT id FROM users WHERE username = 'bookworm_alex'), (SELECT id FROM books WHERE title = 'Wuthering Heights'), 1),
((SELECT id FROM users WHERE username = 'bookworm_alex'), (SELECT id FROM books WHERE title = 'The Catcher in the Rye'), 1);

-- =====================================================
-- ADD ADMIN ACTIONS FOR AUDIT TRAIL
-- =====================================================

INSERT INTO admin_actions (admin_id, action_type, action_description, target_table, target_id) VALUES
-- Book approval actions
((SELECT id FROM users WHERE username = 'admin'), 'book_approved', 'Approved book: Calculus: Early Transcendentals', 'books', (SELECT id FROM books WHERE title = 'Calculus: Early Transcendentals')),
((SELECT id FROM users WHERE username = 'admin'), 'book_approved', 'Approved book: Physics for Scientists and Engineers', 'books', (SELECT id FROM books WHERE title = 'Physics for Scientists and Engineers')),
((SELECT id FROM users WHERE username = 'admin'), 'book_approved', 'Approved book: Introduction to Algorithms', 'books', (SELECT id FROM books WHERE title = 'Introduction to Algorithms')),
((SELECT id FROM users WHERE username = 'admin'), 'book_approved', 'Approved book: Pride and Prejudice', 'books', (SELECT id FROM books WHERE title = 'Pride and Prejudice')),
((SELECT id FROM users WHERE username = 'admin'), 'book_approved', 'Approved book: Where the Wild Things Are', 'books', (SELECT id FROM books WHERE title = 'Where the Wild Things Are')),

-- User management actions
((SELECT id FROM users WHERE username = 'admin'), 'user_verified', 'Verified user account: rahul_student', 'users', (SELECT id FROM users WHERE username = 'rahul_student')),
((SELECT id FROM users WHERE username = 'admin'), 'user_verified', 'Verified user account: fatima_student', 'users', (SELECT id FROM users WHERE username = 'fatima_student')),
((SELECT id FROM users WHERE username = 'admin'), 'user_verified', 'Verified user account: sarah_professional', 'users', (SELECT id FROM users WHERE username = 'sarah_professional')),

-- Order management actions
((SELECT id FROM users WHERE username = 'admin'), 'order_processed', 'Processed order: ORD-2024-001', 'orders', (SELECT id FROM orders WHERE order_number = 'ORD-2024-001')),
((SELECT id FROM users WHERE username = 'admin'), 'order_shipped', 'Shipped order: ORD-2024-008', 'orders', (SELECT id FROM orders WHERE order_number = 'ORD-2024-008')),
((SELECT id FROM users WHERE username = 'admin'), 'order_delivered', 'Marked order as delivered: ORD-2024-001', 'orders', (SELECT id FROM orders WHERE order_number = 'ORD-2024-001'));

-- =====================================================
-- UPDATE BOOK STOCK QUANTITIES FOR REALISTIC INVENTORY
-- =====================================================

-- Update some books to have lower stock for realistic inventory management
UPDATE books SET stock_quantity = 1 WHERE title = 'The Pragmatic Programmer';
UPDATE books SET stock_quantity = 0 WHERE title = 'The Power of Now';
UPDATE books SET stock_quantity = 2 WHERE title = 'Design Patterns';
UPDATE books SET stock_quantity = 1 WHERE title = 'Pride and Prejudice';
UPDATE books SET stock_quantity = 3 WHERE title = 'Where the Wild Things Are';
UPDATE books SET stock_quantity = 1 WHERE title = 'Charlotte''s Web';
UPDATE books SET stock_quantity = 2 WHERE title = 'Guns, Germs, and Steel';
UPDATE books SET stock_quantity = 1 WHERE title = 'The Joy of Cooking';
