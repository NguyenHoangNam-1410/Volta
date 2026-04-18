<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../dao/BundleDAO.php';
require_once __DIR__ . '/../dao/BundleItemDAO.php';
require_once __DIR__ . '/../dto/BundleDTO.php';
require_once __DIR__ . '/../dto/BundleItemDTO.php';

class BundleService
{
    private BundleDAO $bundleDAO;
    private BundleItemDAO $bundleItemDAO;

    public function __construct()
    {
        $pdo = Database::getConnection();
        $this->bundleDAO = new BundleDAO($pdo);
        $this->bundleItemDAO = new BundleItemDAO($pdo);
    }
    
    /**
     * Get all bundles.
     * @return BundleDTO[]
     */
    public function getAll(): array
    {
        $rows = $this->bundleDAO->findAll('id', 'DESC');
        $bundles = array_map([BundleDTO::class, 'fromArray'], $rows);
        return $this->withSavings($bundles);
    }

    /**
     * Get only active bundles.
     * @return BundleDTO[]
     */
    public function getActive(): array
    {
        $rows = $this->bundleDAO->findActive();
        $bundles = array_map([BundleDTO::class, 'fromArray'], $rows);
        return $this->withSavings($bundles);
    }

    /**
     * Get a single bundle by ID.
     */
    public function getById(int $id): ?BundleDTO
    {
        $row = $this->bundleDAO->findById($id);
        if (!$row) {
            return null;
        }

        $bundle = BundleDTO::fromArray($row);
        $bundles = $this->withSavings([$bundle]);
        return $bundles[0] ?? $bundle;
    }

    /**
     * Get a bundle with its items and product details.
     * Returns ['bundle' => BundleDTO, 'items' => BundleItemDTO[]] or null.
     */
    public function getWithItems(int $bundleId): ?array
    {
        $data = $this->bundleDAO->findWithItems($bundleId);
        if (!$data)
            return null;

        $bundle = BundleDTO::fromArray($data);
        $items = array_map([BundleItemDTO::class, 'fromArray'], $data['items'] ?? []);

        $totalProductPrice = 0.0;
        foreach ($items as $item) {
            $totalProductPrice += (float) ($item->productPrice ?? 0);
        }
        $bundle->totalProductPrice = $totalProductPrice;
        $bundle->offPercentage = $this->calculateOffPercentage($totalProductPrice, $bundle->bundlePrice);

        return ['bundle' => $bundle, 'items' => $items];
    }

    /**
     * Paginate bundles.
     * Returns ['data' => BundleDTO[], 'total', 'page', 'limit']
     */
    public function paginate(int $page = 1, int $limit = 20): array
    {
        $result = $this->bundleDAO->paginate($page, $limit, [], 'id', 'DESC');
        $bundles = array_map([BundleDTO::class, 'fromArray'], $result['data']);
        $result['data'] = $this->withSavings($bundles);
        return $result;
    }

    /**
     * Create a new bundle.
     * Returns the new bundle ID.
     */
    public function create(array $data): int
    {
        $dto = new BundleDTO(
            null,
            trim($data['name'] ?? ''),
            trim($data['description'] ?? ''),
            (float) ($data['bundle_price'] ?? 0),
            isset($data['is_active']) ? (bool) $data['is_active'] : true
        );

        if (empty($dto->name)) {
            throw new \InvalidArgumentException('Bundle name is required.');
        }

        return $this->bundleDAO->insert($dto->toArray());
    }

    /**
     * Update an existing bundle.
     */
    public function update(int $id, array $data): int
    {
        $updateData = [];

        if (isset($data['name']))
            $updateData['name'] = trim($data['name']);
        if (isset($data['description']))
            $updateData['description'] = trim($data['description']);
        if (isset($data['bundle_price']))
            $updateData['bundle_price'] = (float) $data['bundle_price'];
        if (isset($data['is_active']))
            $updateData['is_active'] = $data['is_active'] ? 1 : 0;

        if (empty($updateData))
            return 0;

        return $this->bundleDAO->update($id, $updateData);
    }

    /**
     * Delete a bundle and its items.
     */
    public function delete(int $id): int
    {
        $this->bundleItemDAO->deleteByBundle($id);
        return $this->bundleDAO->delete($id);
    }

    /**
     * Count total bundles.
     */
    public function count(): int
    {
        return $this->bundleDAO->count();
    }

    // ══════════════════════════════════════════════════════════
    //  BUNDLE ITEMS
    // ══════════════════════════════════════════════════════════

    /**
     * Get all items in a bundle.
     * @return BundleItemDTO[]
     */
    public function getItems(int $bundleId): array
    {
        $rows = $this->bundleItemDAO->findByBundle($bundleId);
        return array_map([BundleItemDTO::class, 'fromArray'], $rows);
    }

    /**
     * Add a product to a bundle.
     * Returns the new bundle item ID.
     */
    public function addItem(int $bundleId, int $productId): int
    {
        $dto = new BundleItemDTO(null, $bundleId, $productId);
        return $this->bundleItemDAO->insert($dto->toArray());
    }

    /**
     * Remove a product from a bundle.
     */
    public function removeItem(int $itemId): int
    {
        return $this->bundleItemDAO->delete($itemId);
    }

    /**
     * Replace all items in a bundle with a new set of product IDs.
     */
    public function syncItems(int $bundleId, array $productIds): void
    {
        $this->bundleItemDAO->deleteByBundle($bundleId);
        foreach ($productIds as $productId) {
            $this->addItem($bundleId, (int) $productId);
        }
    }

    /**
     * Find all bundles containing a specific product.
     * @return BundleItemDTO[]
     */
    public function findBundlesForProduct(int $productId): array
    {
        $rows = $this->bundleItemDAO->findByProduct($productId);
        return array_map([BundleItemDTO::class, 'fromArray'], $rows);
    }

    /**
     * Attach computed savings fields for FE cards/tables.
     * @param BundleDTO[] $bundles
     * @return BundleDTO[]
     */
    private function withSavings(array $bundles): array
    {
        if (empty($bundles)) {
            return $bundles;
        }

        $bundleIds = array_values(array_filter(array_map(
            static fn(BundleDTO $bundle): ?int => $bundle->id,
            $bundles
        )));

        $priceMap = $this->bundleDAO->getTotalProductPriceMap($bundleIds);

        foreach ($bundles as $bundle) {
            $totalProductPrice = (float) ($priceMap[$bundle->id ?? 0] ?? 0.0);
            $bundle->totalProductPrice = $totalProductPrice;
            $bundle->offPercentage = $this->calculateOffPercentage($totalProductPrice, $bundle->bundlePrice);
        }

        return $bundles;
    }

    private function calculateOffPercentage(float $totalProductPrice, float $bundlePrice): float
    {
        if ($totalProductPrice <= 0) {
            return 0.0;
        }

        $off = (($totalProductPrice - $bundlePrice) / $totalProductPrice) * 100;
        if ($off < 0) {
            $off = 0.0;
        }

        return round($off, 2);
    }
}
