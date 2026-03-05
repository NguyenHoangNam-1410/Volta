<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../dao/OrderDAO.php';
require_once __DIR__ . '/../dao/OrderItemDAO.php';
require_once __DIR__ . '/../dto/OrderDTO.php';
require_once __DIR__ . '/../dto/OrderItemDTO.php';

class CartService
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
     * Get all orders (admin), newest first.
     * @return OrderDTO[]
     */
    public function getAll(): array
    {
        $rows = $this->orderDAO->findAll('created_at', 'DESC');
        return array_map([OrderDTO::class, 'fromArray'], $rows);
    }

    /**
     * Paginate orders with optional filters.
     * Returns ['data' => OrderDTO[], 'total', 'page', 'limit']
     */
    public function paginate(int $page = 1, int $limit = 20, array $conditions = []): array
    {
        $result = $this->orderDAO->paginate($page, $limit, $conditions, 'created_at', 'DESC');
        $result['data'] = array_map([OrderDTO::class, 'fromArray'], $result['data']);
        return $result;
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
     * Get a single order by ID.
     */
    public function getById(int $id): ?OrderDTO
    {
        $row = $this->orderDAO->findById($id);
        return $row ? OrderDTO::fromArray($row) : null;
    }

    /**
     * Get order with its items (joined with product info).
     * Returns ['order' => OrderDTO, 'items' => OrderItemDTO[]] or null.
     */
    public function getWithItems(int $orderId): ?array
    {
        $data = $this->orderDAO->findWithItems($orderId);
        if (!$data)
            return null;

        $order = OrderDTO::fromArray($data);
        $items = array_map([OrderItemDTO::class, 'fromArray'], $data['items'] ?? []);

        return ['order' => $order, 'items' => $items];
    }

    /**
     * Get order statistics.
     */
    public function getStats(?string $startDate = null, ?string $endDate = null): array
    {
        return $this->orderDAO->getStats($startDate, $endDate);
    }

    // ── WRITE ────────────────────────────────────────────────

    /**
     * Update an order's status.
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
     * Update order fields.
     */
    public function update(int $id, array $data): int
    {
        $updateData = [];

        if (isset($data['status']))
            $updateData['status'] = $data['status'];
        if (isset($data['address_id']))
            $updateData['address_id'] = (int) $data['address_id'];
        if (isset($data['total_price']))
            $updateData['total_price'] = (float) $data['total_price'];

        if (empty($updateData))
            return 0;

        return $this->orderDAO->update($id, $updateData);
    }

    /**
     * Count total orders.
     */
    public function count(array $conditions = []): int
    {
        return $this->orderDAO->count($conditions);
    }
}
