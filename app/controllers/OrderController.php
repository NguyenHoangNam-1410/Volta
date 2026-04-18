<?php
// app/controllers/OrderController.php
// Dedicated order management (admin + customer access)

require_once __DIR__ . '/../helpers/Auth.php';
require_once __DIR__ . '/../helpers/ApiResponse.php';
require_once __DIR__ . '/../services/OrderService.php';

class OrderController
{
    private OrderService $orderService;

    public function __construct()
    {
        $this->orderService = new OrderService();
    }

    /**
     * GET /api/admin/orders?page=&limit=&status=
     */
    public function index(): void
    {
        Auth::requireAdmin();

        $page   = max(1, (int) ($_GET['page'] ?? 1));
        $limit  = max(1, (int) ($_GET['limit'] ?? 20));
        $status = $_GET['status'] ?? null;

        $conditions = [];
        if ($status) {
            $conditions['status'] = $status;
        }

        $result = $this->orderService->paginate($page, $limit, $conditions);

        ApiResponse::paginated(
            ApiResponse::dtoList($result['data']),
            [
                'page'  => $result['page'],
                'limit' => $result['limit'],
                'total' => $result['total'],
            ]
        );
    }

    /**
     * GET /api/admin/orders/{id}
     */
    public function show(int $id): void
    {
        Auth::requireAdmin();

        $data = $this->orderService->getWithItems($id);
        if (!$data) {
            ApiResponse::error('Order not found.', 404);
        }

        ApiResponse::success([
            'order' => ApiResponse::dto($data['order']),
            'items' => ApiResponse::dtoList($data['items']),
        ]);
    }

    /**
     * POST /api/admin/orders
     * Body: { user_id, address_id?, status?, total_price, items: [{product_id, quantity, unit_price}] }
     */
    public function store(): void
    {
        Auth::requireAdmin();

        $data  = ApiResponse::body();
        $items = $data['items'] ?? [];
        unset($data['items']);

        try {
            $id = $this->orderService->create($data, $items);
            $order = $this->orderService->getWithItems($id);

            ApiResponse::success(
                $order ? [
                    'order' => ApiResponse::dto($order['order']),
                    'items' => ApiResponse::dtoList($order['items']),
                ] : ['id' => $id],
                'Order created successfully.',
                201
            );
        } catch (\Exception $e) {
            ApiResponse::error($e->getMessage(), 422);
        }
    }

    /**
     * PUT /api/admin/orders/{id}
     * Body: { status?, address_id?, total_price? }
     */
    public function update(int $id): void
    {
        Auth::requireAdmin();

        $data = ApiResponse::body();

        try {
            $this->orderService->update($id, $data);

            $order = $this->orderService->getById($id);
            if (!$order) {
                ApiResponse::error('Order not found.', 404);
            }

            ApiResponse::success(ApiResponse::dto($order), 'Order updated successfully.');

        } catch (\InvalidArgumentException $e) {
            ApiResponse::error($e->getMessage(), 422);
        }
    }

    /**
     * PUT /api/admin/orders/{id}/status
     * Body: { status }
     */
    public function updateStatus(int $id): void
    {
        Auth::requireAdmin();

        $data   = ApiResponse::body();
        $status = $data['status'] ?? '';

        try {
            $this->orderService->updateStatus($id, $status);
            $order = $this->orderService->getById($id);

            ApiResponse::success(
                $order ? ApiResponse::dto($order) : null,
                'Order status updated.'
            );
        } catch (\InvalidArgumentException $e) {
            ApiResponse::error($e->getMessage(), 422);
        }
    }

    /**
     * DELETE /api/admin/orders/{id}
     */
    public function destroy(int $id): void
    {
        Auth::requireAdmin();

        $affected = $this->orderService->delete($id);
        if ($affected === 0) {
            ApiResponse::error('Order not found.', 404);
        }

        ApiResponse::success(null, 'Order deleted successfully.');
    }

    /**
     * GET /api/admin/orders/stats?start_date=&end_date=
     */
    public function stats(): void
    {
        Auth::requireAdmin();

        $startDate = $_GET['start_date'] ?? null;
        $endDate   = $_GET['end_date'] ?? null;
        $days = max(1, min(60, (int) ($_GET['days'] ?? 7)));
        $recentLimit = max(1, min(50, (int) ($_GET['recent_limit'] ?? 10)));
        $topLimit = max(1, min(50, (int) ($_GET['top_limit'] ?? 10)));

        $stats = $this->orderService->getStats($startDate, $endDate, $days, $recentLimit, $topLimit);
        ApiResponse::success($stats);
    }
}
