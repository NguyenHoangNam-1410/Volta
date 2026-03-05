<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../dao/CartDAO.php';
require_once __DIR__ . '/../dao/ProductDAO.php';
require_once __DIR__ . '/../dao/OrderDAO.php';
require_once __DIR__ . '/../dao/OrderItemDAO.php';
require_once __DIR__ . '/../dao/DiscountDAO.php';
require_once __DIR__ . '/../dao/AddressDAO.php';
require_once __DIR__ . '/../dto/CartItemDTO.php';
require_once __DIR__ . '/../dto/OrderDTO.php';
require_once __DIR__ . '/../dto/DiscountDTO.php';

class CustomerCartService
{
    private CartDAO $cartDAO;
    private ProductDAO $productDAO;
    private OrderDAO $orderDAO;
    private OrderItemDAO $orderItemDAO;
    private DiscountDAO $discountDAO;
    private AddressDAO $addressDAO;
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
        $this->cartDAO = new CartDAO($this->pdo);
        $this->productDAO = new ProductDAO($this->pdo);
        $this->orderDAO = new OrderDAO($this->pdo);
        $this->orderItemDAO = new OrderItemDAO($this->pdo);
        $this->discountDAO = new DiscountDAO($this->pdo);
        $this->addressDAO = new AddressDAO($this->pdo);
    }

    /**
     * Get all cart items for a user with product details.
     * Returns ['items' => CartItemDTO[], 'subtotal' => float, 'count' => int]
     */
    public function getCart(int $userId): array
    {
        $rows = $this->cartDAO->findByUser($userId);
        $items = array_map([CartItemDTO::class, 'fromArray'], $rows);
        $subtotal = 0.0;

        foreach ($items as $item) {
            $subtotal += ($item->productPrice ?? 0) * $item->quantity;
        }

        return [
            'items' => $items,
            'subtotal' => round($subtotal, 2),
            'count' => count($items),
        ];
    }

    /**
     * Add a product to the cart.
     * Returns ['success' => bool, 'message' => string]
     */
    public function addItem(int $userId, int $productId, int $quantity = 1): array
    {
        // Validate product
        $product = $this->productDAO->findById($productId);
        if (!$product || !$product['is_active']) {
            return ['success' => false, 'message' => 'Product not found or unavailable.'];
        }

        // Check stock
        if ($product['stock'] < $quantity) {
            return ['success' => false, 'message' => 'Not enough stock available.'];
        }

        $this->cartDAO->addItem($userId, $productId, $quantity);

        return [
            'success' => true,
            'message' => 'Product added to cart.',
            'cartCount' => $this->cartDAO->countItems($userId),
        ];
    }

    /**
     * Update quantity of a cart item.
     * Returns ['success' => bool, 'message' => string, 'subtotal' => float]
     */
    public function updateItem(int $userId, int $productId, int $quantity): array
    {
        if ($quantity <= 0) {
            return $this->removeItem($userId, $productId);
        }

        // Check stock
        $product = $this->productDAO->findById($productId);
        if (!$product) {
            return ['success' => false, 'message' => 'Product not found.'];
        }

        if ($product['stock'] < $quantity) {
            return ['success' => false, 'message' => 'Not enough stock available.'];
        }

        $this->cartDAO->updateQuantity($userId, $productId, $quantity);
        $cart = $this->getCart($userId);

        return [
            'success' => true,
            'message' => 'Cart updated.',
            'subtotal' => $cart['subtotal'],
            'cartCount' => $cart['count'],
        ];
    }

    /**
     * Remove a product from the cart.
     */
    public function removeItem(int $userId, int $productId): array
    {
        $this->cartDAO->removeItem($userId, $productId);
        $cart = $this->getCart($userId);

        return [
            'success' => true,
            'message' => 'Item removed from cart.',
            'subtotal' => $cart['subtotal'],
            'cartCount' => $cart['count'],
        ];
    }

    /**
     * Clear entire cart.
     */
    public function clearCart(int $userId): void
    {
        $this->cartDAO->clearCart($userId);
    }

    /**
     * Get cart item count.
     */
    public function getCartCount(int $userId): int
    {
        return $this->cartDAO->countItems($userId);
    }

    // ══════════════════════════════════════════════════════════
    //  CHECKOUT / ORDER
    // ══════════════════════════════════════════════════════════

    /**
     * Get checkout data (cart summary + addresses).
     */
    public function getCheckoutData(int $userId): ?array
    {
        $cart = $this->getCart($userId);
        if (empty($cart['items'])) {
            return null;
        }

        $addresses = $this->addressDAO->findByUser($userId);

        return [
            'items' => $cart['items'],
            'subtotal' => $cart['subtotal'],
            'count' => $cart['count'],
            'addresses' => $addresses,
        ];
    }

    /**
     * Apply a discount code to the cart subtotal.
     * Returns ['valid' => bool, 'amount' => float, 'total' => float, 'message' => string]
     */
    public function applyDiscount(string $code, float $subtotal): array
    {
        $row = $this->discountDAO->findByCode($code);
        if (!$row) {
            return ['valid' => false, 'amount' => 0, 'total' => $subtotal, 'message' => 'Invalid discount code.'];
        }

        $discount = DiscountDTO::fromArray($row);

        if (!$discount->isValid()) {
            return ['valid' => false, 'amount' => 0, 'total' => $subtotal, 'message' => 'Discount code has expired or is used up.'];
        }

        if ($subtotal < $discount->minOrder) {
            return [
                'valid' => false,
                'amount' => 0,
                'total' => $subtotal,
                'message' => 'Minimum order amount is ' . number_format($discount->minOrder, 2) . '.',
            ];
        }

        $discountAmount = $discount->calculate($subtotal);
        $total = round($subtotal - $discountAmount, 2);

        return [
            'valid' => true,
            'amount' => $discountAmount,
            'total' => $total,
            'message' => 'Discount applied successfully.',
            'discountId' => $discount->id,
        ];
    }

    /**
     * Place an order from the user's cart.
     * Returns ['success' => bool, 'message' => string, 'orderId' => ?int]
     */
    public function placeOrder(int $userId, array $orderData): array
    {
        $cart = $this->getCart($userId);

        if (empty($cart['items'])) {
            return ['success' => false, 'message' => 'Cart is empty.', 'orderId' => null];
        }

        $addressId = isset($orderData['address_id']) ? (int) $orderData['address_id'] : null;
        $discountCode = $orderData['discount_code'] ?? null;
        $subtotal = $cart['subtotal'];
        $totalPrice = $subtotal;

        // Apply discount if provided
        $discountId = null;
        if ($discountCode) {
            $discountResult = $this->applyDiscount($discountCode, $subtotal);
            if ($discountResult['valid']) {
                $totalPrice = $discountResult['total'];
                $discountId = $discountResult['discountId'] ?? null;
            }
        }

        try {
            $this->pdo->beginTransaction();

            // 1. Create order
            $orderId = $this->orderDAO->insert([
                'user_id' => $userId,
                'address_id' => $addressId,
                'status' => 'pending',
                'total_price' => $totalPrice,
            ]);

            // 2. Create order items + update stock
            $orderItems = [];
            foreach ($cart['items'] as $item) {
                $orderItems[] = [
                    'product_id' => $item->productId,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->productPrice ?? 0,
                ];

                // Decrease stock
                $currentProduct = $this->productDAO->findById($item->productId);
                if ($currentProduct) {
                    $newStock = max(0, $currentProduct['stock'] - $item->quantity);
                    $this->productDAO->update($item->productId, ['stock' => $newStock]);
                }
            }

            $this->orderItemDAO->insertMany($orderId, $orderItems);

            // 3. Decrement discount usage
            if ($discountId) {
                $this->discountDAO->decrementUse($discountId);
            }

            // 4. Clear cart
            $this->cartDAO->clearCart($userId);

            $this->pdo->commit();

            return [
                'success' => true,
                'message' => 'Order placed successfully.',
                'orderId' => $orderId,
            ];

        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            error_log('CustomerCartService::placeOrder error: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Failed to place order. Please try again.',
                'orderId' => null,
            ];
        }
    }

    /**
     * Get order details (for order success page).
     */
    public function getOrderDetails(int $orderId): ?array
    {
        $data = $this->orderDAO->findWithItems($orderId);
        if (!$data)
            return null;

        return [
            'order' => OrderDTO::fromArray($data),
            'items' => $data['items'] ?? [],
        ];
    }
}
