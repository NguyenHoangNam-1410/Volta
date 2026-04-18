<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../dao/OrderDAO.php';
require_once __DIR__ . '/../dao/OrderItemDAO.php';
require_once __DIR__ . '/../dto/OrderDTO.php';
require_once __DIR__ . '/../dto/OrderItemDTO.php';

class OrderService
{
    private OrderDAO $orderDAO;
    private OrderItemDAO $orderItemDAO;

    public function __construct()
    {
        $pdo = Database::getConnection();
        $this->orderDAO = new OrderDAO($pdo);
        $this->orderItemDAO = new OrderItemDAO($pdo);
    }

    // ── READ ─────────────────────────────────────────────────

    /**
     * Get all orders, newest first.
     * @return OrderDTO[]
     */
    public function getAll(): array
    {
        $rows = $this->orderDAO->findAll('created_at', 'DESC');
        return array_map([OrderDTO::class, 'fromArray'], $rows);
    }

    /**
     * Get a single order by ID.
     */
    public function getById(int $id): ?OrderDTO
    {
        $row = $this->orderDAO->findById($id);
        return $row ? OrderDTO::fromArray($row) : null;
    }

    /**
     * Get orders for a specific user.
     * @return OrderDTO[]
     */
    public function getByUser(int $userId): array
    {
        $rows = $this->orderDAO->findByUser($userId);
        return array_map([OrderDTO::class, 'fromArray'], $rows);
    }

    /**
     * Get orders filtered by status.
     * @return OrderDTO[]
     */
    public function getByStatus(string $status): array
    {
        $rows = $this->orderDAO->findByStatus($status);
        return array_map([OrderDTO::class, 'fromArray'], $rows);
    }

    /**
     * Get an order with its items and joined product details.
     * Returns ['order' => OrderDTO, 'items' => OrderItemDTO[]] or null.
     */
    public function getWithItems(int $orderId): ?array
    {
        $data = $this->orderDAO->findWithItems($orderId);
        if (!$data)
            return null;

        $items = isset($data['items']) ? $data['items'] : [];
        unset($data['items']);

        return [
            'order' => OrderDTO::fromArray($data),
            'items' => array_map([OrderItemDTO::class, 'fromArray'], $items),
        ];
    }

    /**
     * Paginate orders.
     * Returns ['data' => OrderDTO[], 'total', 'page', 'limit']
     */
    public function paginate(int $page = 1, int $limit = 20, array $conditions = []): array
    {
        $result = $this->orderDAO->paginate($page, $limit, $conditions, 'created_at', 'DESC');
        $result['data'] = array_map([OrderDTO::class, 'fromArray'], $result['data']);
        return $result;
    }

    /**
     * Get order statistics (revenue, counts).
     */
    public function getStats(
        ?string $startDate = null,
        ?string $endDate = null,
        int $days = 7,
        int $recentLimit = 10,
        int $topLimit = 10
    ): array
    {
        $days = max(1, min(60, $days));
        $recentLimit = max(1, min(50, $recentLimit));
        $topLimit = max(1, min(50, $topLimit));

        $startDate = $this->normalizeStartDate($startDate);
        $endDate = $this->normalizeEndDate($endDate);

        $useManualRange = $startDate !== null && $endDate !== null;
        if ($useManualRange && strtotime($startDate) > strtotime($endDate)) {
            [$startDate, $endDate] = [$endDate, $startDate];
        }

        if ($useManualRange) {
            $effectiveStart = $startDate;
            $effectiveEnd = $endDate;
            $effectiveDays = (int) floor((strtotime(substr($effectiveEnd, 0, 10)) - strtotime(substr($effectiveStart, 0, 10))) / 86400) + 1;
            $effectiveDays = max(1, min(60, $effectiveDays));
            $revenueRows = $this->orderDAO->getRevenueByDateRange($effectiveStart, $effectiveEnd);
        } else {
            $effectiveDays = $days;
            $effectiveStart = date('Y-m-d 00:00:00', strtotime('-' . ($effectiveDays - 1) . ' days'));
            $effectiveEnd = date('Y-m-d 23:59:59');
            $revenueRows = $this->orderDAO->getRevenueLastNDays($effectiveDays);
            $startDate = $effectiveStart;
            $endDate = $effectiveEnd;
        }

        $summary = $this->orderDAO->getStats($startDate, $endDate);
        $revenueMap = [];
        foreach ($revenueRows as $row) {
            $revenueMap[$row['order_date']] = (float) $row['amount'];
        }

        $revenueByDay = [];
        $cursorDate = substr($effectiveStart, 0, 10);
        $lastDate = substr($effectiveEnd, 0, 10);
        while (strtotime($cursorDate) <= strtotime($lastDate)) {
            $revenueByDay[] = [
                'date' => $cursorDate,
                'day_of_week' => date('D', strtotime($cursorDate)),
                'amount' => $revenueMap[$cursorDate] ?? 0.0,
            ];
            $cursorDate = date('Y-m-d', strtotime($cursorDate . ' +1 day'));
        }

        $recentOrders = array_map(static function ($row): array {
            return [
                'order_id' => (int) $row['id'],
                'customer' => (string) $row['customer'],
                'total' => (float) $row['total_price'],
                'status' => (string) $row['status'],
                'date' => (string) $row['created_at'],
            ];
        }, $this->orderDAO->getRecentOrders($recentLimit, $startDate, $endDate));

        $topProducts = array_map(static function ($row): array {
            return [
                'product_id' => (int) $row['product_id'],
                'product' => (string) $row['product'],
                'price' => (float) $row['price'],
                'stock' => (int) $row['stock'],
                'sold_quantity' => (int) $row['sold_quantity'],
            ];
        }, $this->orderDAO->getTopProducts($topLimit, $startDate, $endDate));

        return [
            'total_orders' => (int) ($summary['total_orders'] ?? 0),
            'total_revenue' => (float) ($summary['total_revenue'] ?? 0),
            'completed_orders' => (int) ($summary['completed_orders'] ?? 0),
            'cancelled_orders' => (int) ($summary['cancelled_orders'] ?? 0),
            'effective_filter' => [
                'mode' => $useManualRange ? 'date_range' : 'last_n_days',
                'start_date' => $effectiveStart,
                'end_date' => $effectiveEnd,
                'days' => $effectiveDays,
            ],
            'revenue_window_days' => $effectiveDays,
            'revenue_by_day' => $revenueByDay,
            'revenue_last_7_days' => $revenueByDay,
            'recent_orders' => $recentOrders,
            'top_products' => $topProducts,
        ];
    }

    private function normalizeStartDate(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }
        $trimmed = trim($value);
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $trimmed) === 1) {
            return $trimmed . ' 00:00:00';
        }
        return $trimmed;
    }

    private function normalizeEndDate(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }
        $trimmed = trim($value);
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $trimmed) === 1) {
            return $trimmed . ' 23:59:59';
        }
        return $trimmed;
    }

    // ── WRITE ────────────────────────────────────────────────

    /**
     * Create a new order with items.
     * $items = [['product_id' => int, 'quantity' => int, 'unit_price' => float], ...]
     * Returns the new order ID.
     */
    public function create(array $orderData, array $items = []): int
    {
        $dto = new OrderDTO(
            null,
            isset($orderData['user_id']) ? (int) $orderData['user_id'] : null,
            isset($orderData['address_id']) ? (int) $orderData['address_id'] : null,
            $orderData['status'] ?? 'pending',
            (float) ($orderData['total_price'] ?? 0)
        );

        $pdo = Database::getConnection();
        $pdo->beginTransaction();

        try {
            $orderId = $this->orderDAO->insert($dto->toArray());

            if (!empty($items)) {
                $this->orderItemDAO->insertMany($orderId, $items);
            }

            $pdo->commit();
            return $orderId;

        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Update order fields.
     */
    public function update(int $id, array $data): int
    {
        $updateData = [];

        if (isset($data['user_id']))
            $updateData['user_id'] = (int) $data['user_id'];
        if (isset($data['address_id']))
            $updateData['address_id'] = (int) $data['address_id'];
        if (isset($data['status']))
            $updateData['status'] = $data['status'];
        if (isset($data['total_price']))
            $updateData['total_price'] = (float) $data['total_price'];

        if (empty($updateData))
            return 0;

        return $this->orderDAO->update($id, $updateData);
    }

    /**
     * Update only the order status.
     */
    public function updateStatus(int $orderId, string $status): int
    {
        $validStatuses = ['pending', 'confirmed', 'shipping', 'delivered', 'cancelled'];
        if (!in_array($status, $validStatuses)) {
            throw new \InvalidArgumentException('Invalid order status: ' . $status);
        }

        return $this->orderDAO->updateStatus($orderId, $status);
    }

    /**
     * Delete an order and its items.
     */
    public function delete(int $id): int
    {
        // Order items are typically FK-cascaded, but explicit cleanup:
        $items = $this->orderItemDAO->findByOrder($id);
        foreach ($items as $item) {
            $this->orderItemDAO->delete($item['id']);
        }
        return $this->orderDAO->delete($id);
    }

    // ── ORDER ITEMS ─────────────────────────────────────────

    /**
     * Get all items for an order.
     * @return OrderItemDTO[]
     */
    public function getOrderItems(int $orderId): array
    {
        $rows = $this->orderItemDAO->findByOrder($orderId);
        return array_map([OrderItemDTO::class, 'fromArray'], $rows);
    }

    /**
     * Add a single item to an existing order.
     */
    public function addOrderItem(int $orderId, array $itemData): int
    {
        $dto = new OrderItemDTO(
            null,
            $orderId,
            (int) $itemData['product_id'],
            (int) ($itemData['quantity'] ?? 1),
            (float) ($itemData['unit_price'] ?? 0)
        );
        return $this->orderItemDAO->insert($dto->toArray());
    }

    /**
     * Remove an order item.
     */
    public function removeOrderItem(int $itemId): int
    {
        return $this->orderItemDAO->delete($itemId);
    }

    /**
     * Count total orders.
     */
    public function count(array $conditions = []): int
    {
        return $this->orderDAO->count($conditions);
    }
}
