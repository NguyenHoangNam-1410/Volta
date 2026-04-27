<?php
require_once __DIR__ . '/BaseDAO.php';

class PriceAlertDAO extends BaseDAO
{
    protected string $table      = 'products';
    protected string $primaryKey = 'id';

    /**
     * Get product analytics for price alerts.
     */
    public function getProductAnalytics(string $startDate, string $endDate): array
    {
        $sql = "
            SELECT
                p.id as product_id,
                p.name,
                p.price,
                p.stock,
                p.created_at,
                COALESCE(SUM(CASE WHEN o.status <> 'cancelled' THEN oi.quantity ELSE 0 END), 0) as sold_quantity,
                COUNT(DISTINCT oi.order_id) as total_orders,
                COUNT(DISTINCT CASE WHEN o.status = 'cancelled' THEN oi.order_id ELSE NULL END) as cancelled_orders
            FROM products p
            LEFT JOIN order_items oi ON p.id = oi.product_id
            LEFT JOIN orders o ON oi.order_id = o.id AND o.created_at >= :start_date AND o.created_at <= :end_date
            WHERE p.is_active = 1
            GROUP BY p.id, p.name, p.price, p.stock, p.created_at
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':start_date' => $startDate,
            ':end_date'   => $endDate,
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
