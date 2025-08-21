<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

class CartManager {
    private $db;

    public function __construct() {
        $this->db = getDB();
    }

    /**
     * Add item to cart
     */
    public function addToCart($userId, $bookId, $quantity = 1) {
        try {
            // Check if user is logged in
            if (!$userId) {
                return [
                    'success' => false,
                    'message' => 'User not logged in'
                ];
            }

            // Check if book exists and is approved
            $bookCheck = $this->db->select(
                "SELECT id, title, price, cover_image_path FROM books WHERE id = ? AND status = 'approved'",
                [$bookId]
            );

            if (empty($bookCheck)) {
                return [
                    'success' => false,
                    'message' => 'Book not found or not available'
                ];
            }

            // Check if item already exists in cart
            $existingItem = $this->db->select(
                "SELECT id, quantity FROM cart WHERE user_id = ? AND book_id = ?",
                [$userId, $bookId]
            );

            if (!empty($existingItem)) {
                // Update quantity
                $newQuantity = $existingItem[0]['quantity'] + $quantity;
                $this->db->execute(
                    "UPDATE cart SET quantity = ?, updated_at = CURRENT_TIMESTAMP WHERE user_id = ? AND book_id = ?",
                    [$newQuantity, $userId, $bookId]
                );
                
                return [
                    'success' => true,
                    'message' => 'Cart updated successfully',
                    'quantity' => $newQuantity
                ];
            } else {
                // Add new item
                $this->db->execute(
                    "INSERT INTO cart (user_id, book_id, quantity) VALUES (?, ?, ?)",
                    [$userId, $bookId, $quantity]
                );
                
                return [
                    'success' => true,
                    'message' => 'Item added to cart successfully',
                    'quantity' => $quantity
                ];
            }

        } catch (Exception $e) {
            error_log("Add to Cart Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to add item to cart',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get user's cart
     */
    public function getUserCart($userId) {
        try {
            if (!$userId) {
                return [
                    'success' => false,
                    'message' => 'User not logged in'
                ];
            }

            $cartItems = $this->db->select(
                "SELECT c.id, c.quantity, c.created_at, 
                        b.id as book_id, b.title, b.author, b.price, b.cover_image_path, b.book_condition
                 FROM cart c
                 JOIN books b ON c.book_id = b.id
                 WHERE c.user_id = ? AND b.status = 'approved'
                 ORDER BY c.created_at DESC",
                [$userId]
            );

            return [
                'success' => true,
                'data' => $cartItems
            ];

        } catch (Exception $e) {
            error_log("Get User Cart Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to get cart',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Update cart item quantity
     */
    public function updateCartItem($userId, $bookId, $quantity) {
        try {
            if (!$userId) {
                return [
                    'success' => false,
                    'message' => 'User not logged in'
                ];
            }

            if ($quantity <= 0) {
                // Remove item if quantity is 0 or negative
                $this->db->execute(
                    "DELETE FROM cart WHERE user_id = ? AND book_id = ?",
                    [$userId, $bookId]
                );
                
                return [
                    'success' => true,
                    'message' => 'Item removed from cart'
                ];
            }

            $this->db->execute(
                "UPDATE cart SET quantity = ?, updated_at = CURRENT_TIMESTAMP WHERE user_id = ? AND book_id = ?",
                [$quantity, $userId, $bookId]
            );

            return [
                'success' => true,
                'message' => 'Cart updated successfully'
            ];

        } catch (Exception $e) {
            error_log("Update Cart Item Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to update cart',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Remove item from cart
     */
    public function removeFromCart($userId, $bookId) {
        try {
            if (!$userId) {
                return [
                    'success' => false,
                    'message' => 'User not logged in'
                ];
            }

            $this->db->execute(
                "DELETE FROM cart WHERE user_id = ? AND book_id = ?",
                [$userId, $bookId]
            );

            return [
                'success' => true,
                'message' => 'Item removed from cart'
            ];

        } catch (Exception $e) {
            error_log("Remove from Cart Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to remove item from cart',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get cart count for user
     */
    public function getCartCount($userId) {
        try {
            if (!$userId) {
                return 0;
            }

            $result = $this->db->select(
                "SELECT SUM(quantity) as total FROM cart WHERE user_id = ?",
                [$userId]
            );

            return $result[0]['total'] ?? 0;

        } catch (Exception $e) {
            error_log("Get Cart Count Error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Clear user's cart
     */
    public function clearCart($userId) {
        try {
            if (!$userId) {
                return [
                    'success' => false,
                    'message' => 'User not logged in'
                ];
            }

            $this->db->execute(
                "DELETE FROM cart WHERE user_id = ?",
                [$userId]
            );

            return [
                'success' => true,
                'message' => 'Cart cleared successfully'
            ];

        } catch (Exception $e) {
            error_log("Clear Cart Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to clear cart',
                'error' => $e->getMessage()
            ];
        }
    }
}

// Handle the request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $cartManager = new CartManager();

    switch ($action) {
        case 'add_to_cart':
            $userId = $_POST['user_id'] ?? null;
            $bookId = intval($_POST['book_id'] ?? 0);
            $quantity = intval($_POST['quantity'] ?? 1);
            
            if (!$userId || $bookId <= 0) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid parameters'
                ]);
                exit;
            }
            
            $result = $cartManager->addToCart($userId, $bookId, $quantity);
            echo json_encode($result);
            break;

        case 'get_cart':
            // Try to read user_id from POST, then from active session
            $userId = $_POST['user_id'] ?? null;
            if (!$userId && function_exists('isLoggedIn') && isLoggedIn()) {
                $userId = getCurrentUserId();
            }

            if (!$userId) {
                echo json_encode([
                    'success' => false,
                    'message' => 'User not logged in'
                ]);
                exit;
            }

            $result = $cartManager->getUserCart($userId);
            echo json_encode($result);
            break;

        case 'update_cart_item':
            $userId = $_POST['user_id'] ?? null;
            $bookId = intval($_POST['book_id'] ?? 0);
            $quantity = intval($_POST['quantity'] ?? 1);
            
            if (!$userId || $bookId <= 0) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid parameters'
                ]);
                exit;
            }
            
            $result = $cartManager->updateCartItem($userId, $bookId, $quantity);
            echo json_encode($result);
            break;

        case 'remove_from_cart':
            $userId = $_POST['user_id'] ?? null;
            $bookId = intval($_POST['book_id'] ?? 0);
            
            if (!$userId || $bookId <= 0) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid parameters'
                ]);
                exit;
            }
            
            $result = $cartManager->removeFromCart($userId, $bookId);
            echo json_encode($result);
            break;

        case 'get_cart_count':
            $userId = $_POST['user_id'] ?? null;
            if (!$userId && function_exists('isLoggedIn') && isLoggedIn()) {
                $userId = getCurrentUserId();
            }

            if (!$userId) {
                echo json_encode(['count' => 0]);
                exit;
            }

            $count = $cartManager->getCartCount($userId);
            echo json_encode(['count' => $count]);
            break;

        case 'clear_cart':
            $userId = $_POST['user_id'] ?? null;
            
            if (!$userId) {
                echo json_encode([
                    'success' => false,
                    'message' => 'User not logged in'
                ]);
                exit;
            }
            
            $result = $cartManager->clearCart($userId);
            echo json_encode($result);
            break;

        default:
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action'
            ]);
            break;
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Provide simple GET access for read-only actions (useful for debugging)
    $action = $_GET['action'] ?? '';
    $cartManager = new CartManager();

    switch ($action) {
        case 'get_cart':
            $userId = $_GET['user_id'] ?? null;
            if (!$userId && function_exists('isLoggedIn') && isLoggedIn()) {
                $userId = getCurrentUserId();
            }

            if (!$userId) {
                echo json_encode([
                    'success' => false,
                    'message' => 'User not logged in'
                ]);
                exit;
            }

            echo json_encode($cartManager->getUserCart($userId));
            break;

        case 'get_cart_count':
            $userId = $_GET['user_id'] ?? null;
            if (!$userId && function_exists('isLoggedIn') && isLoggedIn()) {
                $userId = getCurrentUserId();
            }
            $count = $userId ? $cartManager->getCartCount($userId) : 0;
            echo json_encode(['count' => $count]);
            break;

        default:
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action'
            ]);
            break;
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Only POST/GET methods allowed'
    ]);
}
?>
