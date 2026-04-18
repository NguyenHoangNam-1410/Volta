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

    /**
     * Sum product prices per bundle.
     * Returns map: [bundle_id => total_product_price].
     */
    public function getTotalProductPriceMap(array $bundleIds): array
    {
        if (empty($bundleIds)) {
            return [];
        }

        $bundleIds = array_values(array_unique(array_map('intval', $bundleIds)));
        $placeholders = [];
        foreach ($bundleIds as $idx => $id) {
            $placeholders[] = ':b' . $idx;
        }

        $sql = "SELECT bi.bundle_id, COALESCE(SUM(p.price), 0) AS total_product_price
                FROM bundle_items bi
                JOIN products p ON p.id = bi.product_id
                WHERE bi.bundle_id IN (" . implode(', ', $placeholders) . ")
                GROUP BY bi.bundle_id";

        $stmt = $this->pdo->prepare($sql);
        foreach ($bundleIds as $idx => $id) {
            $stmt->bindValue(':b' . $idx, $id, PDO::PARAM_INT);
        }
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $map = [];
        foreach ($rows as $row) {
            $map[(int) $row['bundle_id']] = (float) $row['total_product_price'];
        }

        return $map;
    }
}
