<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../dao/CartDAO.php';
require_once __DIR__ . '/../dao/ProductDAO.php';
require_once __DIR__ . '/../dao/BundleDAO.php';
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
    private BundleDAO $bundleDAO;
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
        $this->bundleDAO = new BundleDAO($this->pdo);
        $this->orderDAO = new OrderDAO($this->pdo);
        $this->orderItemDAO = new OrderItemDAO($this->pdo);
        $this->discountDAO = new DiscountDAO($this->pdo);
        $this->addressDAO = new AddressDAO($this->pdo);
    }

    private function getGuestCartMap(): array
    {
        $cart = $_SESSION['guest_cart'] ?? [];
        if (!is_array($cart)) {
            return ['products' => [], 'bundles' => []];
        }

        // Backward compatibility: old format was productId => qty.
        if (!isset($cart['products']) && !isset($cart['bundles'])) {
            return ['products' => $cart, 'bundles' => []];
        }

        return [
            'products' => is_array($cart['products'] ?? null) ? $cart['products'] : [],
            'bundles' => is_array($cart['bundles'] ?? null) ? $cart['bundles'] : [],
        ];
    }

    private function setGuestCartMap(array $cart): void
    {
        $_SESSION['guest_cart'] = [
            'products' => is_array($cart['products'] ?? null) ? $cart['products'] : [],
            'bundles' => is_array($cart['bundles'] ?? null) ? $cart['bundles'] : [],
        ];
    }

    private function getPrimaryImageUrl(int $productId): ?string
    {
        $stmt = $this->pdo->prepare(
            'SELECT url FROM product_images WHERE product_id = :pid AND is_primary = 1 LIMIT 1'
        );
        $stmt->execute([':pid' => $productId]);

        $url = $stmt->fetchColumn();
        return $url !== false ? (string) $url : null;
    }

    private function buildGuestProductRows(array $guestProducts): array
    {
        $rows = [];

        foreach ($guestProducts as $productId => $qty) {
            $productId = (int) $productId;
            $qty = (int) $qty;

            if ($productId <= 0 || $qty <= 0) {
                continue;
            }

            $product = $this->productDAO->findById($productId);
            if (!$product || !(bool) ($product['is_active'] ?? false)) {
                continue;
            }

            $rows[] = [
                'id' => null,
                'user_id' => null,
                'item_type' => 'product',
                'product_id' => $productId,
                'bundle_id' => null,
                'quantity' => $qty,
                'added_at' => date('Y-m-d H:i:s'),
                'name' => $product['name'] ?? null,
                'slug' => $product['slug'] ?? null,
                'price' => $product['price'] ?? null,
                'stock' => $product['stock'] ?? null,
                'image_url' => $this->getPrimaryImageUrl($productId),
            ];
        }

        return $rows;
    }

    private function buildGuestBundleRows(array $guestBundles): array
    {
        $rows = [];

        foreach ($guestBundles as $bundleId => $qty) {
            $bundleId = (int) $bundleId;
            $qty = (int) $qty;

            if ($bundleId <= 0 || $qty <= 0) {
                continue;
            }

            $bundle = $this->bundleDAO->findWithItems($bundleId);
            if (!$bundle || !(bool) ($bundle['is_active'] ?? false)) {
                continue;
            }

            // Validate stock for all products in this bundle.
            $inStock = true;
            foreach (($bundle['items'] ?? []) as $bundleItem) {
                $stock = (int) ($bundleItem['stock'] ?? 0);
                if ($stock < $qty) {
                    $inStock = false;
                    break;
                }
            }
            if (!$inStock) {
                continue;
            }

            $rows[] = [
                'id' => null,
                'user_id' => null,
                'item_type' => 'bundle',
                'product_id' => null,
                'bundle_id' => $bundleId,
                'quantity' => $qty,
                'added_at' => date('Y-m-d H:i:s'),
                'bundle_name' => $bundle['name'] ?? null,
                'bundle_price' => $bundle['bundle_price'] ?? null,
                'image_url' => $bundle['items'][0]['image_url'] ?? null,
            ];
        }

        return $rows;
    }

    private function getBundleWithItems(int $bundleId): ?array
    {
        $bundle = $this->bundleDAO->findWithItems($bundleId);
        if (!$bundle || !(bool) ($bundle['is_active'] ?? false)) {
            return null;
        }

        return $bundle;
    }

    private function assertBundleStock(array $bundle, int $bundleQty): bool
    {
        if ($bundleQty <= 0) {
            return false;
        }

        foreach (($bundle['items'] ?? []) as $item) {
            $stock = (int) ($item['stock'] ?? 0);
            if ($stock < $bundleQty) {
                return false;
            }
        }

        return true;
    }

    private function buildBundleUnitPriceMap(array $bundleItems, float $bundlePrice): array
    {
        $lines = [];
        foreach ($bundleItems as $item) {
            $pid = (int) ($item['product_id'] ?? 0);
            if ($pid <= 0) {
                continue;
            }

            if (!isset($lines[$pid])) {
                $lines[$pid] = [
                    'product_id' => $pid,
                    'unit_base' => 0.0,
                    'qty_per_bundle' => 0,
                ];
            }

            $lines[$pid]['unit_base'] += (float) ($item['price'] ?? 0);
            $lines[$pid]['qty_per_bundle']++;
        }

        if (empty($lines)) {
            return [];
        }

        $totalBase = array_sum(array_column($lines, 'unit_base'));
        $remaining = round($bundlePrice, 2);
        $allocated = [];
        $keys = array_keys($lines);

        foreach ($keys as $idx => $pid) {
            if ($idx === count($keys) - 1) {
                $allocated[$pid] = max(0, $remaining);
                break;
            }

            if ($totalBase > 0) {
                $portion = round(($lines[$pid]['unit_base'] / $totalBase) * $bundlePrice, 2);
            } else {
                $portion = round($bundlePrice / count($keys), 2);
            }

            $allocated[$pid] = $portion;
            $remaining = round($remaining - $portion, 2);
        }

        foreach ($allocated as $pid => $linePrice) {
            $qtyPerBundle = max(1, (int) $lines[$pid]['qty_per_bundle']);
            $allocated[$pid] = round($linePrice / $qtyPerBundle, 2);
        }

        return ['lines' => $lines, 'unit_prices' => $allocated];
    }

    /**
     * Get all cart items for a user with product details.
     * Returns ['items' => CartItemDTO[], 'subtotal' => float, 'count' => int]
     */
    public function getCart(?int $userId): array
    {
        $guestCart = $this->getGuestCartMap();

        if ($userId !== null) {
            $rows = $this->cartDAO->findByUser($userId);
            $rows = array_merge($rows, $this->buildGuestBundleRows($guestCart['bundles']));
        } else {
            $rows = array_merge(
                $this->buildGuestProductRows($guestCart['products']),
                $this->buildGuestBundleRows($guestCart['bundles'])
            );
        }

        $items = array_map([CartItemDTO::class, 'fromArray'], $rows);
        $subtotal = 0.0;

        foreach ($items as $item) {
            if ($item->itemType === 'bundle') {
                $subtotal += ($item->bundlePrice ?? 0) * $item->quantity;
            } else {
                $subtotal += ($item->productPrice ?? 0) * $item->quantity;
            }
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
    public function addItem(?int $userId, string $itemType, int $itemId, int $quantity = 1): array
    {
        if (!in_array($itemType, ['product', 'bundle'], true)) {
            return ['success' => false, 'message' => 'Invalid item type.'];
        }

        if ($itemType === 'product') {
            // Validate product
            $product = $this->productDAO->findById($itemId);
            if (!$product || !(bool) ($product['is_active'] ?? false)) {
                return ['success' => false, 'message' => 'Product not found or unavailable.'];
            }

            // Check stock
            if ((int) $product['stock'] < $quantity) {
                return ['success' => false, 'message' => 'Not enough stock available.'];
            }

            if ($userId !== null) {
                $this->cartDAO->addItem($userId, $itemId, $quantity);
                $cartCount = $this->cartDAO->countItems($userId) + count($this->getGuestCartMap()['bundles']);
            } else {
                $guestCart = $this->getGuestCartMap();
                $guestCart['products'][$itemId] = ((int) ($guestCart['products'][$itemId] ?? 0)) + $quantity;
                $this->setGuestCartMap($guestCart);
                $cartCount = count($guestCart['products']) + count($guestCart['bundles']);
            }

            return [
                'success' => true,
                'message' => 'Product added to cart.',
                'cartCount' => $cartCount,
            ];
        }

        // Bundle flow: always session-backed so guests and logged users can both add bundle lines.
        $bundle = $this->getBundleWithItems($itemId);
        if (!$bundle) {
            return ['success' => false, 'message' => 'Bundle not found or unavailable.'];
        }
        if (!$this->assertBundleStock($bundle, $quantity)) {
            return ['success' => false, 'message' => 'One or more items in this bundle are out of stock.'];
        }

        $guestCart = $this->getGuestCartMap();
        $newQty = ((int) ($guestCart['bundles'][$itemId] ?? 0)) + $quantity;
        if (!$this->assertBundleStock($bundle, $newQty)) {
            return ['success' => false, 'message' => 'Not enough stock available for this bundle quantity.'];
        }

        $guestCart['bundles'][$itemId] = $newQty;
        $this->setGuestCartMap($guestCart);

        if ($userId !== null) {
            $cartCount = $this->cartDAO->countItems($userId) + count($guestCart['bundles']);
        } else {
            $cartCount = count($guestCart['products']) + count($guestCart['bundles']);
        }

        return [
            'success' => true,
            'message' => 'Bundle added to cart.',
            'cartCount' => $cartCount,
        ];
    }

    /**
     * Update quantity of a cart item.
     * Returns ['success' => bool, 'message' => string, 'subtotal' => float]
     */
    public function updateItem(?int $userId, string $itemType, int $itemId, int $quantity): array
    {
        if ($quantity <= 0) {
            return $this->removeItem($userId, $itemType, $itemId);
        }

        if (!in_array($itemType, ['product', 'bundle'], true)) {
            return ['success' => false, 'message' => 'Invalid item type.'];
        }

        if ($itemType === 'product') {
            // Check stock
            $product = $this->productDAO->findById($itemId);
            if (!$product) {
                return ['success' => false, 'message' => 'Product not found.'];
            }

            if ((int) $product['stock'] < $quantity) {
                return ['success' => false, 'message' => 'Not enough stock available.'];
            }

            if ($userId !== null) {
                $this->cartDAO->updateQuantity($userId, $itemId, $quantity);
            } else {
                $guestCart = $this->getGuestCartMap();
                if (isset($guestCart['products'][$itemId])) {
                    $guestCart['products'][$itemId] = $quantity;
                    $this->setGuestCartMap($guestCart);
                }
            }

            $cart = $this->getCart($userId);

            return [
                'success' => true,
                'message' => 'Cart updated.',
                'subtotal' => $cart['subtotal'],
                'cartCount' => $cart['count'],
            ];
        }

        $bundle = $this->getBundleWithItems($itemId);
        if (!$bundle) {
            return ['success' => false, 'message' => 'Bundle not found or unavailable.'];
        }
        if (!$this->assertBundleStock($bundle, $quantity)) {
            return ['success' => false, 'message' => 'Not enough stock available for this bundle quantity.'];
        }

        $guestCart = $this->getGuestCartMap();
        if (isset($guestCart['bundles'][$itemId])) {
            $guestCart['bundles'][$itemId] = $quantity;
            $this->setGuestCartMap($guestCart);
        }

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
    public function removeItem(?int $userId, string $itemType, int $itemId): array
    {
        if (!in_array($itemType, ['product', 'bundle'], true)) {
            return ['success' => false, 'message' => 'Invalid item type.'];
        }

        if ($itemType === 'product') {
            if ($userId !== null) {
                $this->cartDAO->removeItem($userId, $itemId);
            } else {
                $guestCart = $this->getGuestCartMap();
                unset($guestCart['products'][$itemId]);
                $this->setGuestCartMap($guestCart);
            }
        } else {
            $guestCart = $this->getGuestCartMap();
            unset($guestCart['bundles'][$itemId]);
            $this->setGuestCartMap($guestCart);
        }

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
    public function clearCart(?int $userId): void
    {
        if ($userId !== null) {
            $this->cartDAO->clearCart($userId);
        }

        $this->setGuestCartMap(['products' => [], 'bundles' => []]);
    }

    /**
     * Get cart item count.
     */
    public function getCartCount(?int $userId): int
    {
        $guestCart = $this->getGuestCartMap();

        if ($userId !== null) {
            return $this->cartDAO->countItems($userId) + count($guestCart['bundles']);
        }

        return count($guestCart['products']) + count($guestCart['bundles']);
    }

    // ══════════════════════════════════════════════════════════
    //  CHECKOUT / ORDER
    // ══════════════════════════════════════════════════════════

    /**
     * Get checkout data (cart summary + addresses).
     */
    public function getCheckoutData(?int $userId): ?array
    {
        $cart = $this->getCart($userId);
        if (empty($cart['items'])) {
            return null;
        }

        $addresses = $userId !== null ? $this->addressDAO->findByUser($userId) : [];

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
    public function placeOrder(?int $userId, array $orderData): array
    {
        $cart = $this->getCart($userId);

        if (empty($cart['items'])) {
            return ['success' => false, 'message' => 'Cart is empty.', 'orderId' => null];
        }

        $addressId     = isset($orderData['address_id'])    ? (int) $orderData['address_id'] : null;
        $discountCode  = $orderData['discount_code']         ?? null;
        $paymentMethod = $orderData['payment_method']         ?? 'cod';
        $deliveryTier  = $orderData['delivery_tier']          ?? 'standard';
        $shippingFee   = $deliveryTier === 'express' ? 15.00 : 0.00;
        $subtotal      = $cart['subtotal'];
        $totalPrice    = $subtotal + $shippingFee;

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
                'user_id'        => $userId,
                'address_id'     => $addressId,
                'status'         => 'pending',
                'payment_method' => in_array($paymentMethod, ['cod', 'credit_card']) ? $paymentMethod : 'cod',
                'shipping_fee'   => $shippingFee,
                'total_price'    => $totalPrice,
            ]);

            // 2. Create order items + update stock
            $orderItems = [];
            foreach ($cart['items'] as $item) {
                if ($item->itemType === 'bundle') {
                    $bundle = $this->getBundleWithItems((int) $item->bundleId);
                    if (!$bundle) {
                        throw new \RuntimeException('Bundle no longer available.');
                    }
                    if (!$this->assertBundleStock($bundle, $item->quantity)) {
                        throw new \RuntimeException('Insufficient stock for bundle items.');
                    }

                    $pricing = $this->buildBundleUnitPriceMap($bundle['items'] ?? [], (float) ($bundle['bundle_price'] ?? 0));
                    $lineDefs = $pricing['lines'] ?? [];
                    $lineUnitPrices = $pricing['unit_prices'] ?? [];

                    foreach ($lineDefs as $productId => $lineDef) {
                        $qtyPerBundle = max(1, (int) ($lineDef['qty_per_bundle'] ?? 1));
                        $orderItems[] = [
                            'product_id' => (int) $productId,
                            'quantity' => $item->quantity * $qtyPerBundle,
                            'unit_price' => (float) ($lineUnitPrices[$productId] ?? 0),
                        ];

                        $currentProduct = $this->productDAO->findById((int) $productId);
                        if (!$currentProduct) {
                            throw new \RuntimeException('Bundle product missing during checkout.');
                        }

                        $newStock = max(0, (int) $currentProduct['stock'] - ($item->quantity * $qtyPerBundle));
                        $this->productDAO->update((int) $productId, ['stock' => $newStock]);
                    }
                    continue;
                }

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
            if ($userId !== null) {
                $this->cartDAO->clearCart($userId);
            } else {
                $this->setGuestCartMap([]);
            }

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
