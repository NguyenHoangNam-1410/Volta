<?php
require_once __DIR__ . '/BaseDAO.php';

class ProductRelationDAO extends BaseDAO
{
    protected string $table      = 'product_relations';
    protected string $primaryKey = 'id';

    /**
     * Get upsell products for a given product.
     */
    public function findUpsells(int $productId): array
    {
        return $this->findRelated($productId, 'upsell');
    }

    /**
     * Get cross-sell products for a given product.
     */
    public function findCrossSells(int $productId): array
    {
        return $this->findRelated($productId, 'crosssell');
    }

    /**
     * Find related products by type.
     */
    private function findRelated(int $productId, string $type): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT p.*,
                    (SELECT pi.url FROM product_images pi WHERE pi.product_id = p.id AND pi.is_primary = 1 LIMIT 1) AS image_url
             FROM {$this->table} pr
             JOIN products p ON pr.related_id = p.id
             WHERE pr.product_id = :pid AND pr.type = :type AND p.is_active = 1
             ORDER BY pr.sort_order ASC"
        );
        $stmt->execute([':pid' => $productId, ':type' => $type]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
