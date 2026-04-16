<?php
require_once __DIR__ . '/BaseDAO.php';

class BundleDAO extends BaseDAO
{
    protected string $table      = 'bundles';
    protected string $primaryKey = 'id';

    /**
     * Get all active bundles.
     */
    public function findActive(): array
    {
        return $this->findWhere(['is_active' => 1]);
    }

    /**
     * Get a bundle with its items and product details.
     */
    public function findWithItems(int $bundleId): ?array
    {
        $bundle = $this->findById($bundleId);
        if (!$bundle) return null;

        $stmt = $this->pdo->prepare(
            "SELECT bi.*, p.name, p.slug, p.price, p.stock,
                    (SELECT pi.url FROM product_images pi WHERE pi.product_id = p.id AND pi.is_primary = 1 LIMIT 1) AS image_url
             FROM bundle_items bi
             JOIN products p ON bi.product_id = p.id
             WHERE bi.bundle_id = :bid
             ORDER BY bi.id ASC"
        );
        $stmt->execute([':bid' => $bundleId]);
        $bundle['items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $bundle;
    }
}
