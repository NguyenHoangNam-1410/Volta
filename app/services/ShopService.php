<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../dao/ProductDAO.php';
require_once __DIR__ . '/../dao/ProductImageDAO.php';
require_once __DIR__ . '/../dao/CategoryDAO.php';
require_once __DIR__ . '/../dto/ProductDTO.php';
require_once __DIR__ . '/../dto/ProductImageDTO.php';
require_once __DIR__ . '/../dto/CategoryDTO.php';

class ShopService
{
    private ProductDAO $productDAO;
    private ProductImageDAO $imageDAO;
    private CategoryDAO $categoryDAO;

    public function __construct()
    {
        $pdo = Database::getConnection();
        $this->productDAO = new ProductDAO($pdo);
        $this->imageDAO = new ProductImageDAO($pdo);
        $this->categoryDAO = new CategoryDAO($pdo);
    }

    // ── SHOP LISTING ─────────────────────────────────────────

    /**
     * Get paginated active products for the storefront.
     * Returns ['data' => ProductDTO[], 'images' => [...], 'total', 'page', 'limit']
     */
    public function getProducts(int $page = 1, int $limit = 12, ?int $categoryId = null, ?string $search = null): array
    {
        if ($search) {
            $result = $this->productDAO->search($search, $page, $limit);
        } elseif ($categoryId) {
            // Manual pagination for category filter
            $allRows = $this->productDAO->findByCategory($categoryId);
            $total = count($allRows);
            $offset = ($page - 1) * $limit;
            $data = array_slice($allRows, $offset, $limit);
            $result = ['data' => $data, 'total' => $total, 'page' => $page, 'limit' => $limit];
        } else {
            $result = $this->productDAO->paginate($page, $limit, ['is_active' => 1], 'created_at', 'DESC');
        }

        // Map to DTOs
        $products = array_map([ProductDTO::class, 'fromArray'], $result['data']);

        $images = $this->buildPrimaryImagesMap($products);

        return [
            'data' => $products,
            'images' => $images,
            'total' => $result['total'],
            'page' => $result['page'],
            'limit' => $result['limit'],
        ];
    }

    /**
     * Get full product detail for the product page by slug.
     * Returns ['product' => ProductDTO, 'images' => ProductImageDTO[], 'upsells' => [...], 'crosssells' => [...]]
     */
    public function getProductDetailBySlug(string $slug): ?array
    {
        $row = $this->productDAO->findBySlug($slug);
        if (!$row || !$row['is_active']) {
            return null;
        }

        $product = ProductDTO::fromArray($row);

        // All images
        $imageRows = $this->imageDAO->findByProduct($product->id);
        $images = array_map([ProductImageDTO::class, 'fromArray'], $imageRows);

        return [
            'product' => $product,
            'images' => $images,
        ];
    }

    /**
     * Get full product detail for the product page.
     * Returns ['product' => ProductDTO, 'images' => ProductImageDTO[], 'upsells' => [...], 'crosssells' => [...]]
     */
    public function getProductDetail(int $id): ?array
    {
        $row = $this->productDAO->findById($id);
        if (!$row || !$row['is_active']) {
            return null;
        }

        $product = ProductDTO::fromArray($row);

        // All images
        $imageRows = $this->imageDAO->findByProduct($id);
        $images = array_map([ProductImageDTO::class, 'fromArray'], $imageRows);

        return [
            'product' => $product,
            'images' => $images,
        ];
    }

    /**
     * Get all categories for the shop sidebar/filter.
     * @return CategoryDTO[]
     */
    public function getCategories(): array
    {
        $rows = $this->categoryDAO->findAll('name', 'ASC');
        return array_map([CategoryDTO::class, 'fromArray'], $rows);
    }

    /**
     * Get featured products by badge.
     * Returns ['data' => ProductDTO[], 'images' => array<int, ProductImageDTO|null>]
     */
    public function getFeatured(string $badge = 'hot', int $limit = 8): array
    {
        $rows = $this->productDAO->findByBadge($badge, $limit);
        $products = array_map([ProductDTO::class, 'fromArray'], $rows);

        return [
            'data' => $products,
            'images' => $this->buildPrimaryImagesMap($products),
        ];
    }

    /**
     * Build map of product_id => primary image DTO (or null).
     *
     * @param ProductDTO[] $products
     * @return array<int, ProductImageDTO|null>
     */
    private function buildPrimaryImagesMap(array $products): array
    {
        $images = [];
        foreach ($products as $dto) {
            $imgRow = $this->imageDAO->findPrimary($dto->id);
            $images[$dto->id] = $imgRow ? ProductImageDTO::fromArray($imgRow) : null;
        }

        return $images;
    }
}
