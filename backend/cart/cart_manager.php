<?php
/**
 * Shopping Cart Management System
 * Handles cart operations including add, remove, update, and checkout
 * 
 * @author BookMarket Team
 * @version 1.0
 */

// Include required files
require_once '../config/database.php';
require_once '../config/session.php';

/**
 * Cart Manager Class
 * Manages all shopping cart operations
 */
class CartManager {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
    }
    
    /**
     * Add item to cart
     * @param int $userId User ID
     * @param int $bookId Book ID
     * @param int $quantity Quantity to add
     * @return array Result with success status and message
     */
    public function addToCart($userId, $bookId, $quantity = 1) {
        try {
            // Validate quantity
            if ($quantity < 1) {
                return ['success' => false, 'message' => 'Invalid quantity'];
            }
            
            // Check if book exists and is approved
            $book = $this->db->selectOne(
                "SELECT id, price, status FROM books WHERE id = ? AND status = 'approved'",
                [intval($bookId)]
            );
            
            if (!$book) {
                return ['success' => false, 'message' => 'Book not available for purchase'];
            }
            
            // Check if book is already in cart
            $existingItem = $this->db->selectOne(
                "SELECT id, quantity FROM cart_items WHERE user_id = ? AND book_id = ?",
                [intval($userId), intval($bookId)]
            );
            
            if ($existingItem) {
                // Update existing item quantity
                $newQuantity = $existingItem['quantity'] + $quantity;
                $result = $this->db->execute(
                    "UPDATE cart_items SET quantity = ? WHERE id = ?",
                    [$newQuantity, $existingItem['id']]
                );
                
                if ($result !== false) {
                    return [
                        'success' => true,
                        'message' => 'Cart updated successfully',
                        'action' => 'updated'
                    ];
                } else {
                    return ['success' => false, 'message' => 'Failed to update cart'];
                }
            } else {
                // Add new item to cart
                $cartItemId = $this->db->insert(
                    "INSERT INTO cart_items (user_id, book_id, quantity) VALUES (?, ?, ?)",
                    [intval($userId), intval($bookId), intval($quantity)]
                );
                
                if ($cartItemId) {
                    return [
                        'success' => true,
                        'message' => 'Item added to cart successfully',
                        'action' => 'added'
                    ];
                } else {
                    return ['success' => false, 'message' => 'Failed to add item to cart'];
                }
            }
            
        } catch (Exception $e) {
            error_log("Add to Cart Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to add item to cart. Please try again.'];
        }
    }
    
    /**
     * Get user's cart items
     * @param int $userId User ID
     * @return array|false Cart items array or false on failure
     */
    public function getCartItems($userId) {
        try {
            $cartItems = $this->db->select(
                                 "SELECT ci.*, b.title, b.author, b.price, b.book_condition, b.cover_image_path, b.status,
                        c.name as category_name
                 FROM cart_items ci
                 JOIN books b ON ci.book_id = b.id
                 JOIN categories c ON b.category_id = c.id
                 WHERE ci.user_id = ? AND b.status = 'approved'
                 ORDER BY ci.added_at DESC",
                [intval($userId)]
            );
            
            return $cartItems;
            
        } catch (Exception $e) {
            error_log("Get Cart Items Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update cart item quantity
     * @param int $userId User ID
     * @param int $cartItemId Cart item ID
     * @param int $quantity New quantity
     * @return array Result with success status and message
     */
    public function updateCartQuantity($userId, $cartItemId, $quantity) {
        try {
            // Validate quantity
            if ($quantity < 1) {
                return ['success' => false, 'message' => 'Quantity must be at least 1'];
            }
            
            // Check if cart item belongs to user
            $cartItem = $this->db->selectOne(
                "SELECT ci.id, b.status FROM cart_items ci
                 JOIN books b ON ci.book_id = b.id
                 WHERE ci.id = ? AND ci.user_id = ?",
                [intval($cartItemId), intval($userId)]
            );
            
            if (!$cartItem) {
                return ['success' => false, 'message' => 'Cart item not found'];
            }
            
            if ($cartItem['status'] !== 'approved') {
                return ['success' => false, 'message' => 'Book is no longer available'];
            }
            
            // Update quantity
            $result = $this->db->execute(
                "UPDATE cart_items SET quantity = ? WHERE id = ?",
                [intval($quantity), intval($cartItemId)]
            );
            
            if ($result !== false) {
                return ['success' => true, 'message' => 'Quantity updated successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to update quantity'];
            }
            
        } catch (Exception $e) {
            error_log("Update Cart Quantity Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to update quantity. Please try again.'];
        }
    }
    
    /**
     * Remove item from cart
     * @param int $userId User ID
     * @param int $cartItemId Cart item ID
     * @return array Result with success status and message
     */
    public function removeFromCart($userId, $cartItemId) {
        try {
            // Check if cart item belongs to user
            $cartItem = $this->db->selectOne(
                "SELECT id FROM cart_items WHERE id = ? AND user_id = ?",
                [intval($cartItemId), intval($userId)]
            );
            
            if (!$cartItem) {
                return ['success' => false, 'message' => 'Cart item not found'];
            }
            
            // Remove item
            $result = $this->db->execute(
                "DELETE FROM cart_items WHERE id = ?",
                [intval($cartItemId)]
            );
            
            if ($result !== false) {
                return ['success' => true, 'message' => 'Item removed from cart successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to remove item from cart'];
            }
            
        } catch (Exception $e) {
            error_log("Remove from Cart Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to remove item from cart. Please try again.'];
        }
    }
    
    /**
     * Clear user's entire cart
     * @param int $userId User ID
     * @return array Result with success status and message
     */
    public function clearCart($userId) {
        try {
            $result = $this->db->execute(
                "DELETE FROM cart_items WHERE user_id = ?",
                [intval($userId)]
            );
            
            if ($result !== false) {
                return ['success' => true, 'message' => 'Cart cleared successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to clear cart'];
            }
            
        } catch (Exception $e) {
            error_log("Clear Cart Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to clear cart. Please try again.'];
        }
    }
    
    /**
     * Get cart summary (total items and total price)
     * @param int $userId User ID
     * @return array|false Cart summary or false on failure
     */
    public function getCartSummary($userId) {
        try {
            $summary = $this->db->selectOne(
                "SELECT 
                    COUNT(ci.id) as total_items,
                    SUM(ci.quantity) as total_quantity,
                    SUM(ci.quantity * b.price) as total_price
                 FROM cart_items ci
                 JOIN books b ON ci.book_id = b.id
                 WHERE ci.user_id = ? AND b.status = 'approved'",
                [intval($userId)]
            );
            
            if ($summary) {
                $summary['total_price'] = floatval($summary['total_price'] ?? 0);
                $summary['total_items'] = intval($summary['total_items'] ?? 0);
                $summary['total_quantity'] = intval($summary['total_quantity'] ?? 0);
            }
            
            return $summary;
            
        } catch (Exception $e) {
            error_log("Get Cart Summary Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Checkout cart items and create order
     * @param int $userId User ID
     * @param array $orderData Order information
     * @return array Result with success status and message
     */
    public function checkout($userId, $orderData) {
        try {
            // Start transaction
            $this->db->beginTransaction();
            
            // Get cart items
            $cartItems = $this->getCartItems($userId);
            if (!$cartItems || empty($cartItems)) {
                $this->db->rollback();
                return ['success' => false, 'message' => 'Cart is empty'];
            }
            
            // Calculate total
            $totalAmount = 0;
            foreach ($cartItems as $item) {
                $totalAmount += $item['price'] * $item['quantity'];
            }
            
            // Generate order number
            $orderNumber = 'ORD' . date('Ymd') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
            
            // Create order
            $orderId = $this->db->insert(
                "INSERT INTO orders (order_number, user_id, total_amount, shipping_address, shipping_city, shipping_postal_code, shipping_country, payment_method) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                [
                    $orderNumber,
                    intval($userId),
                    $totalAmount,
                    cleanInput($orderData['shipping_address']),
                    cleanInput($orderData['shipping_city']),
                    cleanInput($orderData['shipping_postal_code']),
                    cleanInput($orderData['shipping_country'] ?? 'Bangladesh'),
                    cleanInput($orderData['payment_method'] ?? 'Card')
                ]
            );
            
            if (!$orderId) {
                $this->db->rollback();
                return ['success' => false, 'message' => 'Failed to create order'];
            }
            
            // Create order items
            foreach ($cartItems as $item) {
                $orderItemId = $this->db->insert(
                    "INSERT INTO order_items (order_id, book_id, quantity, price_per_item, seller_id) 
                     VALUES (?, ?, ?, ?, ?)",
                    [
                        $orderId,
                        $item['book_id'],
                        $item['quantity'],
                        $item['price'],
                        $item['seller_id'] ?? 0 // This should be set from book data
                    ]
                );
                
                if (!$orderItemId) {
                    $this->db->rollback();
                    return ['success' => false, 'message' => 'Failed to create order items'];
                }
                
                // Mark book as sold
                $this->db->execute(
                    "UPDATE books SET status = 'sold' WHERE id = ?",
                    [$item['book_id']]
                );
            }
            
            // Clear cart
            $this->clearCart($userId);
            
            // Commit transaction
            $this->db->commit();
            
            return [
                'success' => true,
                'message' => 'Order placed successfully',
                'order_id' => $orderId,
                'order_number' => $orderNumber,
                'total_amount' => $totalAmount
            ];
            
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Checkout Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Checkout failed. Please try again.'];
        }
    }
    
    /**
     * Validate cart items (check availability and prices)
     * @param int $userId User ID
     * @return array Result with validation status and any issues
     */
    public function validateCart($userId) {
        try {
            $cartItems = $this->getCartItems($userId);
            if (!$cartItems) {
                return ['valid' => false, 'message' => 'Cart is empty'];
            }
            
            $issues = [];
            $totalAmount = 0;
            
            foreach ($cartItems as $item) {
                // Check if book is still available
                if ($item['status'] !== 'approved') {
                    $issues[] = "Book '{$item['title']}' is no longer available";
                    continue;
                }
                
                // Check if price has changed
                $currentBook = $this->db->selectOne(
                    "SELECT price FROM books WHERE id = ?",
                    [$item['book_id']]
                );
                
                if ($currentBook && $currentBook['price'] != $item['price']) {
                    $issues[] = "Price for '{$item['title']}' has changed from ৳{$item['price']} to ৳{$currentBook['price']}";
                }
                
                $totalAmount += $item['price'] * $item['quantity'];
            }
            
            if (!empty($issues)) {
                return [
                    'valid' => false,
                    'issues' => $issues,
                    'message' => 'Cart validation failed'
                ];
            }
            
            return [
                'valid' => true,
                'total_amount' => $totalAmount,
                'message' => 'Cart is valid'
            ];
            
        } catch (Exception $e) {
            error_log("Validate Cart Error: " . $e->getMessage());
            return ['valid' => false, 'message' => 'Cart validation failed. Please try again.'];
        }
    }
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cartManager = new CartManager();
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add_to_cart':
            if (!isLoggedIn()) {
                sendErrorResponse('User not logged in', 401);
            }
            $result = $cartManager->addToCart(
                getCurrentUserId(),
                $_POST['book_id'],
                $_POST['quantity'] ?? 1
            );
            sendJSONResponse($result);
            break;
            
        case 'get_cart_items':
            if (!isLoggedIn()) {
                sendErrorResponse('User not logged in', 401);
            }
            $cartItems = $cartManager->getCartItems(getCurrentUserId());
            if ($cartItems !== false) {
                sendSuccessResponse($cartItems, 'Cart items retrieved successfully');
            } else {
                sendErrorResponse('Failed to retrieve cart items');
            }
            break;
            
        case 'update_cart_quantity':
            if (!isLoggedIn()) {
                sendErrorResponse('User not logged in', 401);
            }
            $result = $cartManager->updateCartQuantity(
                getCurrentUserId(),
                $_POST['cart_item_id'],
                $_POST['quantity']
            );
            sendJSONResponse($result);
            break;
            
        case 'remove_from_cart':
            if (!isLoggedIn()) {
                sendErrorResponse('User not logged in', 401);
            }
            $result = $cartManager->removeFromCart(
                getCurrentUserId(),
                $_POST['cart_item_id']
            );
            sendJSONResponse($result);
            break;
            
        case 'clear_cart':
            if (!isLoggedIn()) {
                sendErrorResponse('User not logged in', 401);
            }
            $result = $cartManager->clearCart(getCurrentUserId());
            sendJSONResponse($result);
            break;
            
        case 'get_cart_summary':
            if (!isLoggedIn()) {
                sendErrorResponse('User not logged in', 401);
            }
            $summary = $cartManager->getCartSummary(getCurrentUserId());
            if ($summary !== false) {
                sendSuccessResponse($summary, 'Cart summary retrieved successfully');
            } else {
                sendErrorResponse('Failed to retrieve cart summary');
            }
            break;
            
        case 'validate_cart':
            if (!isLoggedIn()) {
                sendErrorResponse('User not logged in', 401);
            }
            $validation = $cartManager->validateCart(getCurrentUserId());
            sendJSONResponse($validation);
            break;
            
        case 'checkout':
            if (!isLoggedIn()) {
                sendErrorResponse('User not logged in', 401);
            }
            $result = $cartManager->checkout(getCurrentUserId(), $_POST);
            sendJSONResponse($result);
            break;
            
        default:
            sendErrorResponse('Invalid action', 400);
            break;
    }
}
?>
