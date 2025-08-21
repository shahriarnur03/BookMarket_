<?php
/**
 * Order Management System
 * Handles order creation, tracking, and management for both customers and admins
 * 
 * @author BookMarket Team
 * @version 1.0
 */

// Include required files
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

/**
 * Order Manager Class
 * Manages all order-related operations
 */
class OrderManager {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
        $this->ensureOrderItemsTable();
        $this->ensureBookStockColumn();
    }
    
    /**
     * Generate a unique order number
     */
    private function generateOrderNumber(): string {
        return 'BM-' . date('Ymd') . '-' . strtoupper(substr(uniqid('', true), -6));
    }

    /** Ensure order_items table exists (idempotent) */
    private function ensureOrderItemsTable(): void {
        try {
            $this->db->execute(
                "CREATE TABLE IF NOT EXISTS order_items (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    order_id INT NOT NULL,
                    book_id INT NOT NULL,
                    quantity INT NOT NULL,
                    price_per_item DECIMAL(10,2) NOT NULL,
                    seller_id INT NOT NULL,
                    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
                    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
                    FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE
                )"
            );
        } catch (Exception $e) {
            error_log('ensureOrderItemsTable error: ' . $e->getMessage());
        }
    }

    /** Ensure books.stock_quantity column exists (idempotent) */
    private function ensureBookStockColumn(): void {
        try {
            // MariaDB/MySQL 8 supports IF NOT EXISTS
            $this->db->execute("ALTER TABLE books ADD COLUMN IF NOT EXISTS stock_quantity INT NOT NULL DEFAULT 1");
        } catch (Exception $e) {
            error_log('ensureBookStockColumn error: ' . $e->getMessage());
        }
    }

    /**
     * Create an order from the user's current cart
     * - Validates cart
     * - Inserts order and order_items
     * - Marks books as sold
     * - Clears cart
     */
    public function createOrderFromCart(int $userId, array $shipping, string $paymentMethod = 'Card') {
        try {
            if (!$userId) {
                return ['success' => false, 'message' => 'User not logged in'];
            }

            // Load cart items
            $cartItems = $this->db->select(
                "SELECT c.quantity, b.id as book_id, b.title, b.price, b.seller_id, COALESCE(b.stock_quantity,1) as stock_quantity, b.status
                 FROM cart c
                 JOIN books b ON c.book_id = b.id
                 WHERE c.user_id = ? AND b.status = 'approved'",
                [intval($userId)]
            );

            if (!$cartItems || count($cartItems) === 0) {
                return ['success' => false, 'message' => 'Your cart is empty'];
            }

            // Calculate totals
            $subtotal = 0.0;
            foreach ($cartItems as $ci) {
                $subtotal += floatval($ci['price']) * intval($ci['quantity']);
            }
            $shippingCost = 10.0; // flat rate
            $taxRate = 0.03;      // 3%
            $taxAmount = $subtotal * $taxRate;
            $totalAmount = $subtotal + $shippingCost + $taxAmount;

            // Prepare shipping fields
            $address = $shipping['address'] ?? '';
            $city = $shipping['city'] ?? '';
            $postal = $shipping['postal_code'] ?? '';
            $country = $shipping['country'] ?? 'Bangladesh';
            if (!$address || !$city || !$postal) {
                return ['success' => false, 'message' => 'Missing shipping information'];
            }

            // Start transaction
            $this->db->beginTransaction();

            // Insert order
            $orderNumber = $this->generateOrderNumber();
            $orderId = $this->db->insert(
                "INSERT INTO orders (order_number, user_id, total_amount, shipping_address, shipping_city, shipping_postal_code, shipping_country, order_status, payment_status, payment_method)
                 VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending', 'Pending', ?)",
                [
                    $orderNumber,
                    intval($userId),
                    $totalAmount,
                    $address,
                    $city,
                    $postal,
                    $country,
                    $paymentMethod
                ]
            );

            $orderId = intval($orderId);
            if ($orderId <= 0) {
                $this->db->rollback();
                return ['success' => false, 'message' => 'Failed to create order'];
            }

            // Insert order items
            foreach ($cartItems as $ci) {
                $requestedQty = intval($ci['quantity']);
                $availableQty = intval($ci['stock_quantity']);
                if ($requestedQty <= 0) { $requestedQty = 1; }
                if ($availableQty < $requestedQty) {
                    $this->db->rollback();
                    return ['success' => false, 'message' => 'Insufficient stock for ' . $ci['title']];
                }
                $this->db->insert(
                    "INSERT INTO order_items (order_id, book_id, quantity, price_per_item, seller_id)
                     VALUES (?, ?, ?, ?, ?)",
                    [
                        intval($orderId),
                        intval($ci['book_id']),
                        $requestedQty,
                        floatval($ci['price']),
                        intval($ci['seller_id'])
                    ]
                );
                // Decrement stock and update status when zero
                $updated = $this->db->execute(
                    "UPDATE books 
                     SET stock_quantity = stock_quantity - ?, 
                         status = CASE WHEN stock_quantity - ? <= 0 THEN 'sold' ELSE status END
                     WHERE id = ? AND stock_quantity >= ?",
                    [$requestedQty, $requestedQty, intval($ci['book_id']), $requestedQty]
                );
                if ($updated === false) {
                    $this->db->rollback();
                    return ['success' => false, 'message' => 'Failed to update stock for ' . $ci['title']];
                }
            }

            // Clear cart
            $this->db->execute("DELETE FROM cart WHERE user_id = ?", [intval($userId)]);

            // Verify order exists before commit
            $check = $this->db->selectOne("SELECT id FROM orders WHERE id = ?", [$orderId]);
            if (!$check) {
                $this->db->rollback();
                return ['success' => false, 'message' => 'Order could not be verified'];
            }

            $this->db->commit();

            return [
                'success' => true,
                'message' => 'Order placed successfully',
                'order_id' => intval($orderId),
                'order_number' => $orderNumber,
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount
            ];

        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Create Order From Cart Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to place order'];
        }
    }
    
    /**
     * Get user's orders
     * @param int $userId User ID
     * @param array $filters Filter parameters
     * @param int $limit Number of orders to return
     * @param int $offset Offset for pagination
     * @return array|false Orders array or false on failure
     */
    public function getUserOrders($userId, $filters = [], $limit = 20, $offset = 0) {
        try {
            $whereConditions = ["o.user_id = ?"];
            $params = [intval($userId)];
            
            // Apply filters
            if (!empty($filters['status'])) {
                $whereConditions[] = "o.order_status = ?";
                $params[] = cleanInput($filters['status']);
            }
            
            if (!empty($filters['order_number'])) {
                $whereConditions[] = "o.order_number LIKE ?";
                $params[] = '%' . cleanInput($filters['order_number']) . '%';
            }
            
            $whereClause = implode(' AND ', $whereConditions);
            
            // Compose query with numeric limit/offset to avoid PDO binding issues
            $limitInt = intval($limit);
            $offsetInt = intval($offset);
            $sql = "SELECT o.*, 
                           COUNT(oi.id) as total_items,
                           SUM(oi.quantity) as total_quantity
                    FROM orders o
                    LEFT JOIN order_items oi ON o.id = oi.order_id
                    WHERE $whereClause
                    GROUP BY o.id
                    ORDER BY o.order_date DESC
                    LIMIT $limitInt OFFSET $offsetInt";
            
            $orders = $this->db->select($sql, $params);
            if ($orders === false) {
                // Retry without GROUP BY extras to diagnose
                $orders = $this->db->select(
                    "SELECT o.* FROM orders o WHERE $whereClause ORDER BY o.order_date DESC",
                    $params
                );
            }
            
            return $orders;
            
        } catch (Exception $e) {
            error_log("Get User Orders Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get order details by ID
     * @param int $orderId Order ID
     * @param int $userId User ID (for customer access control)
     * @return array|false Order details or false on failure
     */
    public function getOrderDetails($orderId, $userId = null) {
        try {
            $whereConditions = ["o.id = ?"];
            $params = [intval($orderId)];
            
            // If user ID provided, restrict access to user's own orders
            if ($userId) {
                $whereConditions[] = "o.user_id = ?";
                $params[] = intval($userId);
            }
            
            $whereClause = implode(' AND ', $whereConditions);
            
            // Get order header
            $order = $this->db->selectOne(
                "SELECT o.*, u.username, u.first_name, u.last_name, u.email, u.phone
                 FROM orders o
                 JOIN users u ON o.user_id = u.id
                 WHERE $whereClause",
                $params
            );
            
            if (!$order) {
                return false;
            }
            
            // Get order items
            $orderItems = $this->db->select(
                "SELECT oi.*, b.title, b.author, b.cover_image_path, b.book_condition, b.isbn,
                        c.name as category_name, u.username as seller_name
                 FROM order_items oi
                 JOIN books b ON oi.book_id = b.id
                 JOIN categories c ON b.category_id = c.id
                 JOIN users u ON oi.seller_id = u.id
                 WHERE oi.order_id = ?
                 ORDER BY oi.id ASC",
                [intval($orderId)]
            );
            if ($orderItems === false) { $orderItems = []; }

            // Compute derived totals
            $subtotal = 0.0;
            foreach ($orderItems as $it) {
                $subtotal += floatval($it['price_per_item']) * intval($it['quantity']);
            }
            $shipping = 10.0;
            $tax = $subtotal * 0.03;
            $order['subtotal'] = $subtotal;
            $order['shipping_cost'] = $shipping;
            $order['tax_amount'] = $tax;
            $order['total_amount'] = floatval($order['total_amount']);
            $order['items'] = $orderItems;
            
            return $order;
            
        } catch (Exception $e) {
            error_log("Get Order Details Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all orders for admin management
     * @param array $filters Filter parameters
     * @param int $limit Number of orders to return
     * @param int $offset Offset for pagination
     * @return array|false Orders array or false on failure
     */
    public function getAllOrders($filters = [], $limit = 50, $offset = 0) {
        try {
            $whereConditions = ["1=1"];
            $params = [];
            
            // Apply filters
            if (!empty($filters['status'])) {
                $whereConditions[] = "o.order_status = ?";
                $params[] = cleanInput($filters['status']);
            }
            
            if (!empty($filters['order_number'])) {
                $whereConditions[] = "o.order_number LIKE ?";
                $params[] = '%' . cleanInput($filters['order_number']) . '%';
            }
            
            if (!empty($filters['user_id'])) {
                $whereConditions[] = "o.user_id = ?";
                $params[] = intval($filters['user_id']);
            }
            
            if (!empty($filters['date_from'])) {
                $whereConditions[] = "DATE(o.order_date) >= ?";
                $params[] = cleanInput($filters['date_from']);
            }
            
            if (!empty($filters['date_to'])) {
                $whereConditions[] = "DATE(o.order_date) <= ?";
                $params[] = cleanInput($filters['date_to']);
            }
            
            $whereClause = implode(' AND ', $whereConditions);
            
            // Compose query with numeric limit/offset to avoid PDO binding issues
            $limitInt = intval($limit);
            $offsetInt = intval($offset);
            $sql = "SELECT o.*, u.username, u.first_name, u.last_name, u.email,
                           COUNT(oi.id) as total_items,
                           SUM(oi.quantity) as total_quantity
                    FROM orders o
                    JOIN users u ON o.user_id = u.id
                    LEFT JOIN order_items oi ON o.id = oi.order_id
                    WHERE $whereClause
                    GROUP BY o.id
                    ORDER BY o.order_date DESC
                    LIMIT $limitInt OFFSET $offsetInt";
            
            $orders = $this->db->select($sql, $params);
            
            return $orders;
            
        } catch (Exception $e) {
            error_log("Get All Orders Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update order status
     * @param int $orderId Order ID
     * @param string $newStatus New order status
     * @param string $notes Additional notes
     * @return array Result with success status and message
     */
    public function updateOrderStatus($orderId, $newStatus, $notes = '') {
        try {
            // Validate status
            $validStatuses = ['Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'];
            if (!in_array($newStatus, $validStatuses)) {
                return ['success' => false, 'message' => 'Invalid order status'];
            }
            
            // Check if order exists
            $order = $this->db->selectOne(
                "SELECT id, order_status FROM orders WHERE id = ?",
                [intval($orderId)]
            );
            
            if (!$order) {
                return ['success' => false, 'message' => 'Order not found'];
            }
            
            // Update order status
            $result = $this->db->execute(
                "UPDATE orders SET order_status = ? WHERE id = ?",
                [cleanInput($newStatus), intval($orderId)]
            );
            
            if ($result !== false) {
                // Log admin action if user is admin
                if (isAdmin()) {
                    $this->logAdminAction(
                        'update_order_status',
                        "Order #{$orderId} status changed from {$order['order_status']} to {$newStatus}",
                        'orders',
                        $orderId
                    );
                }
                
                return ['success' => true, 'message' => 'Order status updated successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to update order status'];
            }
            
        } catch (Exception $e) {
            error_log("Update Order Status Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to update order status. Please try again.'];
        }
    }
    
    /**
     * Cancel order
     * @param int $orderId Order ID
     * @param int $userId User ID (for customer access control)
     * @param string $reason Cancellation reason
     * @return array Result with success status and message
     */
    public function cancelOrder($orderId, $userId, $reason = '') {
        try {
            // Check if order exists and belongs to user
            $order = $this->db->selectOne(
                "SELECT id, order_status, total_amount FROM orders WHERE id = ? AND user_id = ?",
                [intval($orderId), intval($userId)]
            );
            
            if (!$order) {
                return ['success' => false, 'message' => 'Order not found or access denied'];
            }
            
            // Check if order can be cancelled
            if ($order['order_status'] === 'Delivered' || $order['order_status'] === 'Cancelled') {
                return ['success' => false, 'message' => 'Order cannot be cancelled'];
            }
            
            // Start transaction
            $this->db->beginTransaction();
            
            // Update order status
            $result = $this->db->execute(
                "UPDATE orders SET order_status = 'Cancelled' WHERE id = ?",
                [intval($orderId)]
            );
            
            if (!$result) {
                $this->db->rollback();
                return ['success' => false, 'message' => 'Failed to cancel order'];
            }
            
            // Mark books as available again
            $orderItems = $this->db->select(
                "SELECT book_id FROM order_items WHERE order_id = ?",
                [intval($orderId)]
            );
            
            foreach ($orderItems as $item) {
                $this->db->execute(
                    "UPDATE books SET status = 'approved' WHERE id = ?",
                    [$item['book_id']]
                );
            }
            
            // Commit transaction
            $this->db->commit();
            
            return ['success' => true, 'message' => 'Order cancelled successfully'];
            
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Cancel Order Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to cancel order. Please try again.'];
        }
    }
    
    /**
     * Get order statistics for admin dashboard
     * @return array|false Statistics array or false on failure
     */
    public function getOrderStats() {
        try {
            $stats = $this->db->selectOne(
                "SELECT 
                    COUNT(*) as total_orders,
                    COUNT(CASE WHEN order_status = 'Pending' THEN 1 END) as pending_orders,
                    COUNT(CASE WHEN order_status = 'Processing' THEN 1 END) as processing_orders,
                    COUNT(CASE WHEN order_status = 'Shipped' THEN 1 END) as shipped_orders,
                    COUNT(CASE WHEN order_status = 'Delivered' THEN 1 END) as delivered_orders,
                    COUNT(CASE WHEN order_status = 'Cancelled' THEN 1 END) as cancelled_orders,
                    SUM(CASE WHEN order_status IN ('Delivered', 'Shipped') THEN total_amount ELSE 0 END) as total_revenue,
                    AVG(CASE WHEN order_status IN ('Delivered', 'Shipped') THEN total_amount ELSE NULL END) as avg_order_value
                 FROM orders"
            );
            
            if ($stats) {
                $stats['total_revenue'] = floatval($stats['total_revenue'] ?? 0);
                $stats['avg_order_value'] = floatval($stats['avg_order_value'] ?? 0);
            }
            
            // Calculate commission based on book ownership
            $commissionStats = $this->getCommissionStats();
            if ($commissionStats) {
                $stats = array_merge($stats, $commissionStats);
            }
            
            return $stats;
            
        } catch (Exception $e) {
            error_log("Get Order Stats Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get commission statistics based on book ownership
     * @return array|false Commission statistics
     */
    public function getCommissionStats() {
        try {
            $stats = $this->db->selectOne(
                "SELECT 
                    -- Customer books: Admin gets 5% commission
                    SUM(CASE 
                        WHEN o.order_status IN ('Delivered', 'Shipped') 
                        AND b.seller_id != 1  -- Assuming admin user_id is 1
                        THEN oi.price_per_item * oi.quantity * 0.05
                        ELSE 0 
                    END) as customer_book_commission,
                    
                    -- Admin books: Admin gets 15% profit
                    SUM(CASE 
                        WHEN o.order_status IN ('Delivered', 'Shipped') 
                        AND b.seller_id = 1  -- Admin uploaded books
                        THEN oi.price_per_item * oi.quantity * 0.15
                        ELSE 0 
                    END) as admin_book_profit,
                    
                    -- Total admin earnings (commission + profit)
                    SUM(CASE 
                        WHEN o.order_status IN ('Delivered', 'Shipped') 
                        AND b.seller_id != 1  -- Customer books
                        THEN oi.price_per_item * oi.quantity * 0.05
                        WHEN o.order_status IN ('Delivered', 'Shipped') 
                        AND b.seller_id = 1  -- Admin books
                        THEN oi.price_per_item * oi.quantity * 0.15
                        ELSE 0 
                    END) as total_admin_earnings
                    
                 FROM orders o
                 JOIN order_items oi ON o.id = oi.order_id
                 JOIN books b ON oi.book_id = b.id
                 WHERE o.order_status IN ('Delivered', 'Shipped')"
            );
            
            if ($stats) {
                $stats['customer_book_commission'] = floatval($stats['customer_book_commission'] ?? 0);
                $stats['admin_book_profit'] = floatval($stats['admin_book_profit'] ?? 0);
                $stats['total_admin_earnings'] = floatval($stats['total_admin_earnings'] ?? 0);
            }
            
            return $stats;
            
        } catch (Exception $e) {
            error_log("Get Commission Stats Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get revenue time series aggregated by month for the last N months
     * @param int $months Number of months to include
     * @return array|false Array of [month, month_label, revenue] or false on failure
     */
    public function getRevenueTimeseries($months = 12) {
        try {
            $months = intval($months);
            if ($months < 1) { $months = 1; }
            if ($months > 36) { $months = 36; }
            
            // Start from the first day of the month (months-1) ago
            $startTimestamp = strtotime('-' . ($months - 1) . ' months', strtotime(date('Y-m-01')));
            $startDate = date('Y-m-01', $startTimestamp);
            
            $rows = $this->db->select(
                "SELECT DATE_FORMAT(order_date, '%Y-%m') as ym, SUM(total_amount) as revenue\n                 FROM orders\n                 WHERE order_status IN ('Delivered', 'Shipped') AND order_date >= ?\n                 GROUP BY ym\n                 ORDER BY ym ASC",
                [$startDate]
            );
            
            $map = [];
            foreach ($rows as $r) {
                $map[$r['ym']] = floatval($r['revenue'] ?? 0);
            }
            
            $result = [];
            for ($i = 0; $i < $months; $i++) {
                $ts = strtotime('+' . $i . ' months', $startTimestamp);
                $key = date('Y-m', $ts);
                $label = date('M Y', $ts);
                $result[] = [
                    'month' => $key,
                    'month_label' => $label,
                    'revenue' => isset($map[$key]) ? $map[$key] : 0.0
                ];
            }
            
            return $result;
        } catch (Exception $e) {
            error_log("Get Revenue Timeseries Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get recent orders for admin dashboard
     * @param int $limit Number of orders to return
     * @return array|false Recent orders array or false on failure
     */
    public function getRecentOrders($limit = 10) {
        try {
            $orders = $this->db->select(
                "SELECT o.id, o.order_number, o.total_amount, o.order_status, o.order_date,
                        u.username, u.first_name, u.last_name
                 FROM orders o
                 JOIN users u ON o.user_id = u.id
                 ORDER BY o.order_date DESC
                 LIMIT ?",
                [intval($limit)]
            );
            
            // Debug: Log what we're returning
            error_log("getRecentOrders returning: " . json_encode($orders));
            
            return $orders;
            
        } catch (Exception $e) {
            error_log("Get Recent Orders Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get user's selling statistics
     * @param int $userId User ID
     * @return array|false Selling statistics or false on failure
     */
    public function getUserSellingStats($userId) {
        try {
            $stats = $this->db->selectOne(
                "SELECT 
                    COUNT(DISTINCT o.id) as total_orders,
                    SUM(oi.quantity * oi.price_per_item) as total_earnings,
                    COUNT(DISTINCT oi.book_id) as unique_books_sold,
                    SUM(oi.quantity) as total_books_sold
                 FROM orders o
                 JOIN order_items oi ON o.id = oi.order_id
                 WHERE oi.seller_id = ? AND o.order_status IN ('Delivered', 'Shipped')",
                [intval($userId)]
            );
            
            if ($stats) {
                $stats['total_earnings'] = floatval($stats['total_earnings'] ?? 0);
            }
            
            return $stats;
            
        } catch (Exception $e) {
            error_log("Get User Selling Stats Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all orders for a specific user (admin function)
     * @param int $userId User ID
     * @return array|false Orders array or false on failure
     */
    public function getUserOrdersAdmin($userId) {
        try {
            $orders = $this->db->select(
                "SELECT o.*, 
                        COUNT(oi.id) as total_items,
                        SUM(oi.quantity) as total_quantity
                 FROM orders o
                 LEFT JOIN order_items oi ON o.id = oi.order_id
                 WHERE o.user_id = ?
                 GROUP BY o.id
                 ORDER BY o.order_date DESC",
                [intval($userId)]
            );
            
            return $orders;
            
        } catch (Exception $e) {
            error_log("Get User Orders Error: " . $e->getMessage());
            return false;
        }
    }
    

    
    /**
     * Log admin action for audit trail
     * @param string $actionType Type of action
     * @param string $description Action description
     * @param string $targetTable Target table name
     * @param int $targetId Target record ID
     */
    private function logAdminAction($actionType, $description, $targetTable = '', $targetId = 0) {
        try {
            if (isAdmin()) {
                $this->db->insert(
                    "INSERT INTO admin_actions (admin_id, action_type, action_description, target_table, target_id) 
                     VALUES (?, ?, ?, ?, ?)",
                    [
                        getCurrentUserId(),
                        cleanInput($actionType),
                        cleanInput($description),
                        cleanInput($targetTable),
                        intval($targetId)
                    ]
                );
            }
        } catch (Exception $e) {
            error_log("Log Admin Action Error: " . $e->getMessage());
        }
    }
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderManager = new OrderManager();
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'place_order':
            // Allow user_id from POST or from session
            $userId = $_POST['user_id'] ?? null;
            if (!$userId && function_exists('isLoggedIn') && isLoggedIn()) {
                $userId = getCurrentUserId();
            }
            $shipping = [
                'address' => $_POST['address'] ?? '',
                'city' => $_POST['city'] ?? '',
                'postal_code' => $_POST['postal_code'] ?? '',
                'country' => $_POST['country'] ?? 'Bangladesh'
            ];
            $paymentMethod = $_POST['payment_method'] ?? 'Card';
            $result = $orderManager->createOrderFromCart(intval($userId), $shipping, $paymentMethod);
            sendJSONResponse($result);
            break;
        case 'get_user_orders':
            // Allow user_id from POST (for localStorage-based auth) or fall back to session
            $userId = $_POST['user_id'] ?? null;
            if (!$userId && isLoggedIn()) {
                $userId = getCurrentUserId();
            }
            if (!$userId) {
                sendErrorResponse('User not logged in', 401);
            }
            $orders = $orderManager->getUserOrders(intval($userId), $_POST);
            if ($orders !== false) {
                sendSuccessResponse($orders, 'Orders retrieved successfully');
            } else {
                sendErrorResponse('Failed to retrieve orders');
            }
            break;
            
        case 'get_order_details':
            // Accept either active session or explicit user_id for frontend that relies on localStorage
            $orderId = $_POST['order_id'] ?? 0;
            $userId = $_POST['user_id'] ?? null;
            if (!$userId && isLoggedIn()) {
                $userId = getCurrentUserId();
            }
            // Admin can view any order
            if ($userId && isAdmin()) {
                $userId = null;
            }
            $orderDetails = $orderManager->getOrderDetails($orderId, $userId);
            if ($orderDetails) {
                sendSuccessResponse($orderDetails, 'Order details retrieved successfully');
            } else {
                sendErrorResponse('Order not found or access denied');
            }
            break;
            
        case 'get_all_orders':
            if (!isAdmin()) {
                sendErrorResponse('Access denied', 403);
            }
            $orders = $orderManager->getAllOrders($_POST);
            if ($orders !== false) {
                sendSuccessResponse($orders, 'Orders retrieved successfully');
            } else {
                sendErrorResponse('Failed to retrieve orders');
            }
            break;
            
        case 'update_order_status':
            if (!isAdmin()) {
                sendErrorResponse('Access denied', 403);
            }
            $result = $orderManager->updateOrderStatus(
                $_POST['order_id'],
                $_POST['status'],
                $_POST['notes'] ?? ''
            );
            sendJSONResponse($result);
            break;
            
        case 'cancel_order':
            if (!isLoggedIn()) {
                sendErrorResponse('User not logged in', 401);
            }
            $result = $orderManager->cancelOrder(
                $_POST['order_id'],
                getCurrentUserId(),
                $_POST['reason'] ?? ''
            );
            sendJSONResponse($result);
            break;
            
        case 'get_order_stats':
            if (!isAdmin()) {
                sendErrorResponse('Access denied', 403);
            }
            $stats = $orderManager->getOrderStats();
            if ($stats !== false) {
                sendSuccessResponse($stats, 'Order statistics retrieved successfully');
            } else {
                sendErrorResponse('Failed to retrieve order statistics');
            }
            break;
            
        case 'get_revenue_timeseries':
            if (!isAdmin()) {
                sendErrorResponse('Access denied', 403);
            }
            $months = isset($_POST['months']) ? intval($_POST['months']) : 12;
            $data = $orderManager->getRevenueTimeseries($months);
            if ($data !== false) {
                sendSuccessResponse($data, 'Revenue timeseries retrieved successfully');
            } else {
                sendErrorResponse('Failed to retrieve revenue timeseries');
            }
            break;
            
        case 'get_recent_orders':
            if (!isAdmin()) {
                sendErrorResponse('Access denied', 403);
            }
            $orders = $orderManager->getRecentOrders($_POST['limit'] ?? 10);
            if ($orders !== false) {
                sendSuccessResponse($orders, 'Recent orders retrieved successfully');
            } else {
                sendErrorResponse('Failed to retrieve recent orders');
            }
            break;
            

            
        case 'get_user_selling_stats':
            if (!isLoggedIn()) {
                sendErrorResponse('User not logged in', 401);
            }
            $stats = $orderManager->getUserSellingStats(getCurrentUserId());
            if ($stats !== false) {
                sendSuccessResponse($stats, 'Selling statistics retrieved successfully');
            } else {
                sendErrorResponse('Failed to retrieve selling statistics');
            }
            break;
            
        case 'get_user_orders':
            if (!isAdmin()) {
                sendErrorResponse('Access denied', 403);
            }
            $userId = $_POST['user_id'] ?? 0;
            if (!$userId) {
                sendErrorResponse('User ID is required');
            }
            $orders = $orderManager->getUserOrdersAdmin($userId);
            if ($orders !== false) {
                sendSuccessResponse($orders, 'User orders retrieved successfully');
            } else {
                sendErrorResponse('Failed to retrieve user orders');
            }
            break;
            
        default:
            sendErrorResponse('Invalid action', 400);
            break;
    }
}
?>
