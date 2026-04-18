<?php
require_once __DIR__ . '/BaseDAO.php';

class OrderDAO extends BaseDAO
{
    protected string $table      = 'orders';
    protected string $primaryKey = 'id';

    /**
     * Get orders for a user (newest first).
     */
    public function findByUser(int $userId): array
    {
        return $this->findWhere(['user_id' => $userId], 'created_at', 'DESC');
    }

    /**
     * Get orders filtered by status.
     */
    public function findByStatus(string $status): array
    {
        return $this->findWhere(['status' => $status], 'created_at', 'DESC');
    }

    /**
     * Update only the status of an order.
     */
    public function updateStatus(int $orderId, string $status): int
    {
        return $this->update($orderId, ['status' => $status]);
    }

    /**
     * Get an order with its items and product details.
     */
    public function findWithItems(int $orderId): ?array
    {
        $order = $this->findById($orderId);
        if (!$order) return null;

        $stmt = $this->pdo->prepare(
            "SELECT oi.*, p.name, p.slug,
                    (SELECT pi.url FROM product_images pi WHERE pi.product_id = p.id AND pi.is_primary = 1 LIMIT 1) AS image_url
             FROM order_items oi
             JOIN products p ON oi.product_id = p.id
             WHERE oi.order_id = :oid
             ORDER BY oi.id ASC"
        );
        $stmt->execute([':oid' => $orderId]);
        $order['items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $order;
    }

    /**
     * Revenue / order stats with optional date filtering.
     */
    public function getStats(?string $startDate = null, ?string $endDate = null): array
    {
        $where  = '';
        $params = [];
        $conditions = [];

        if ($startDate) {
            $conditions[]          = "created_at >= :start";
            $params[':start']      = $startDate;
        }
        if ($endDate) {
            $conditions[]          = "created_at <= :end";
            $params[':end']        = $endDate;
        }
        if ($conditions) {
            $where = 'WHERE ' . implode(' AND ', $conditions);
        }

        $stmt = $this->pdo->prepare(
            "SELECT
                 COUNT(*)                                                        AS total_orders,
                 COALESCE(SUM(total_price), 0)                                   AS total_revenue,
                 SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END)           AS completed_orders,
                 SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END)          AS cancelled_orders
             FROM {$this->table} {$where}"
        );
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Revenue grouped by day for the last N days (including today).
     */
    public function getRevenueLastNDays(int $days = 7): array
    {
        $days = max(1, $days);

        $stmt = $this->pdo->prepare(
            "SELECT DATE(created_at) AS order_date,
                    COALESCE(SUM(total_price), 0) AS amount
             FROM {$this->table}
             WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL :days DAY)
             GROUP BY DATE(created_at)
             ORDER BY order_date ASC"
        );
        $stmt->bindValue(':days', $days - 1, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Revenue grouped by day for an explicit date range.
     */
    public function getRevenueByDateRange(string $startDate, string $endDate): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT DATE(created_at) AS order_date,
                    COALESCE(SUM(total_price), 0) AS amount
             FROM {$this->table}
             WHERE created_at >= :start AND created_at <= :end
             GROUP BY DATE(created_at)
             ORDER BY order_date ASC"
        );
        $stmt->execute([
            ':start' => $startDate,
            ':end' => $endDate,
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Most recent orders with customer display information.
     */
    public function getRecentOrders(int $limit = 10, ?string $startDate = null, ?string $endDate = null): array
    {
        $limit = max(1, $limit);

        $where = [];
        $params = [];
        if ($startDate !== null) {
            $where[] = 'o.created_at >= :start';
            $params[':start'] = $startDate;
        }
        if ($endDate !== null) {
            $where[] = 'o.created_at <= :end';
            $params[':end'] = $endDate;
        }

        $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

        $stmt = $this->pdo->prepare(
            "SELECT o.id,
                    o.total_price,
                    o.status,
                    o.created_at,
                    COALESCE(u.full_name, 'Guest') AS customer
             FROM {$this->table} o
             LEFT JOIN users u ON o.user_id = u.id
             {$whereSql}
             ORDER BY o.created_at DESC
             LIMIT :limit"
        );
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Top-selling products by quantity, excluding cancelled orders.
     */
    public function getTopProducts(int $limit = 10, ?string $startDate = null, ?string $endDate = null): array
    {
        $limit = max(1, $limit);

        $where = ['o.status <> \'cancelled\''];
        $params = [];
        if ($startDate !== null) {
            $where[] = 'o.created_at >= :start';
            $params[':start'] = $startDate;
        }
        if ($endDate !== null) {
            $where[] = 'o.created_at <= :end';
            $params[':end'] = $endDate;
        }

        $whereSql = 'WHERE ' . implode(' AND ', $where);

        $stmt = $this->pdo->prepare(
            "SELECT p.id AS product_id,
                    p.name AS product,
                    p.price,
                    p.stock,
                    COALESCE(SUM(oi.quantity), 0) AS sold_quantity
             FROM order_items oi
             JOIN orders o ON oi.order_id = o.id
             JOIN products p ON oi.product_id = p.id
             {$whereSql}
             GROUP BY p.id, p.name, p.price, p.stock
             ORDER BY sold_quantity DESC, p.id ASC
             LIMIT :limit"
        );
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
