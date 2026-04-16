<?php
// app/controllers/CustomerCartController.php
// Customer-facing cart + checkout

require_once __DIR__ . '/../helpers/Auth.php';
require_once __DIR__ . '/../helpers/ApiResponse.php';
require_once __DIR__ . '/../services/CustomerCartService.php';

class CustomerCartController
{
    private CustomerCartService $cartService;

    public function __construct()
    {
        $this->cartService = new CustomerCartService();
    }

    /**
     * GET /api/cart
     */
    public function index(): void
    {
        $cart = $this->cartService->getCart(Auth::userId());

        // Serialize cart items manually (they have join fields)
        $items = array_map(function ($item) {
            $payload = [
                'id'            => $item->id,
                'item_type'     => $item->itemType,
                'item_id'       => $item->itemType === 'bundle' ? $item->bundleId : $item->productId,
                'product_id'    => $item->productId,
                'bundle_id'     => $item->bundleId,
                'quantity'      => $item->quantity,
                'image_url'     => $item->imageUrl,
            ];

            if ($item->itemType === 'bundle') {
                $payload['bundle_name']  = $item->bundleName;
                $payload['bundle_price'] = $item->bundlePrice;
                $payload['line_total']   = round(($item->bundlePrice ?? 0) * $item->quantity, 2);
                return $payload;
            }

            $payload['product_name']  = $item->productName;
            $payload['product_slug']  = $item->productSlug;
            $payload['product_price'] = $item->productPrice;
            $payload['product_stock'] = $item->productStock;
            $payload['line_total']    = round(($item->productPrice ?? 0) * $item->quantity, 2);

            return $payload;
        }, $cart['items']);

        ApiResponse::success([
            'items'    => $items,
            'subtotal' => $cart['subtotal'],
            'count'    => $cart['count'],
        ]);
    }

    /**
     * POST /api/cart/items
     * Body: { item_type?: product|bundle, product_id?, bundle_id?, quantity? }
     */
    public function add(): void
    {
        $data      = ApiResponse::body();
        $itemType  = ($data['item_type'] ?? '') === 'bundle' || isset($data['bundle_id']) ? 'bundle' : 'product';
        $itemId    = $itemType === 'bundle'
            ? (int) ($data['bundle_id'] ?? 0)
            : (int) ($data['product_id'] ?? 0);
        $quantity  = max(1, (int) ($data['quantity'] ?? 1));

        if ($itemId <= 0) {
            ApiResponse::error('Invalid item ID.', 422);
        }

        $result = $this->cartService->addItem(Auth::userId(), $itemType, $itemId, $quantity);

        if (!$result['success']) {
            ApiResponse::error($result['message'], 422);
        }

        ApiResponse::success([
            'cartCount' => $result['cartCount'] ?? 0,
        ], $result['message'], 201);
    }

    /**
     * PUT /api/cart/items
     * Body: { item_type?: product|bundle, product_id?|bundle_id?, quantity }
     */
    public function update(): void
    {
        $data      = ApiResponse::body();
        $itemType  = ($data['item_type'] ?? '') === 'bundle' || isset($data['bundle_id']) ? 'bundle' : 'product';
        $itemId    = $itemType === 'bundle'
            ? (int) ($data['bundle_id'] ?? 0)
            : (int) ($data['product_id'] ?? 0);
        $quantity  = (int) ($data['quantity'] ?? 0);

        if ($itemId <= 0) {
            ApiResponse::error('Invalid item ID.', 422);
        }

        $result = $this->cartService->updateItem(Auth::userId(), $itemType, $itemId, $quantity);

        if (!$result['success']) {
            ApiResponse::error($result['message'], 422);
        }

        ApiResponse::success([
            'subtotal'  => $result['subtotal'] ?? 0,
            'cartCount' => $result['cartCount'] ?? 0,
        ], $result['message']);
    }

    /**
     * DELETE /api/cart/items/{productId}
     */
    public function remove(int $productId): void
    {
        $itemType = ($_GET['item_type'] ?? 'product') === 'bundle' ? 'bundle' : 'product';
        $result = $this->cartService->removeItem(Auth::userId(), $itemType, $productId);

        if (!$result['success']) {
            ApiResponse::error($result['message'], 422);
        }

        ApiResponse::success([
            'subtotal'  => $result['subtotal'] ?? 0,
            'cartCount' => $result['cartCount'] ?? 0,
        ], $result['message']);
    }

    /**
     * DELETE /api/cart
     */
    public function clear(): void
    {
        $this->cartService->clearCart(Auth::userId());
        ApiResponse::success(null, 'Cart cleared.');
    }

    /**
     * GET /api/cart/checkout
     */
    public function checkout(): void
    {
        $data = $this->cartService->getCheckoutData(Auth::userId());
        if (!$data) {
            ApiResponse::error('Cart is empty.', 422);
        }

        // Serialize items
        $items = array_map(function ($item) {
            $payload = [
                'id'            => $item->id,
                'item_type'     => $item->itemType,
                'item_id'       => $item->itemType === 'bundle' ? $item->bundleId : $item->productId,
                'product_id'    => $item->productId,
                'bundle_id'     => $item->bundleId,
                'quantity'      => $item->quantity,
            ];

            if ($item->itemType === 'bundle') {
                $payload['bundle_name']  = $item->bundleName;
                $payload['bundle_price'] = $item->bundlePrice;
                $payload['line_total']   = round(($item->bundlePrice ?? 0) * $item->quantity, 2);
                return $payload;
            }

            $payload['product_name']  = $item->productName;
            $payload['product_price'] = $item->productPrice;
            $payload['line_total']    = round(($item->productPrice ?? 0) * $item->quantity, 2);

            return $payload;
        }, $data['items']);

        ApiResponse::success([
            'items'     => $items,
            'subtotal'  => $data['subtotal'],
            'count'     => $data['count'],
            'addresses' => $data['addresses'],
        ]);
    }

    /**
     * POST /api/cart/apply-discount
     * Body: { discount_code, subtotal }
     */
    public function applyDiscount(): void
    {
        $data     = ApiResponse::body();
        $code     = $data['discount_code'] ?? '';
        $subtotal = (float) ($data['subtotal'] ?? 0);

        if (empty($code)) {
            ApiResponse::error('Discount code is required.', 422);
        }

        $result = $this->cartService->applyDiscount($code, $subtotal);

        if (!$result['valid']) {
            ApiResponse::error($result['message'], 422);
        }

        ApiResponse::success([
            'discount_amount' => $result['amount'],
            'total'           => $result['total'],
        ], $result['message']);
    }

    /**
     * POST /api/cart/place-order
     * Body: { address_id?, discount_code? }
     */
    public function placeOrder(): void
    {
        $data = ApiResponse::body();

        $result = $this->cartService->placeOrder(Auth::userId(), $data);

        if (!$result['success']) {
            ApiResponse::error($result['message'], 422);
        }

        ApiResponse::success([
            'order_id' => $result['orderId'],
        ], $result['message'], 201);
    }

    /**
     * GET /api/orders/my
     * Customer's own orders.
     */
    public function myOrders(): void
    {
        Auth::requireLogin();

        require_once __DIR__ . '/../services/OrderService.php';
        $orderService = new OrderService();
        $orders = $orderService->getByUser(Auth::userId());

        ApiResponse::success(ApiResponse::dtoList($orders));
    }

    /**
     * GET /api/orders/my/{id}
     * Customer's own order detail.
     */
    public function myOrderDetail(int $orderId): void
    {
        Auth::requireLogin();

        $data = $this->cartService->getOrderDetails($orderId);
        if (!$data) {
            ApiResponse::error('Order not found.', 404);
        }

        // Verify order belongs to user
        if ($data['order']->userId !== Auth::userId()) {
            ApiResponse::error('Forbidden.', 403);
        }

        ApiResponse::success([
            'order' => ApiResponse::dto($data['order']),
            'items' => $data['items'],
        ]);
    }
}
