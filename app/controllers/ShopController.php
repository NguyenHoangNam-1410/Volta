<?php
// app/controllers/ShopController.php
// Public storefront endpoints

require_once __DIR__ . '/../helpers/ApiResponse.php';
require_once __DIR__ . '/../services/ShopService.php';

class ShopController
{
    private ShopService $shopService;

    public function __construct()
    {
        $this->shopService = new ShopService();
    }

    /**
     * GET /api/shop/products?page=&limit=&category_id=&search=
     */
    public function index(): void
    {
        $page       = max(1, (int) ($_GET['page'] ?? 1));
        $limit      = max(1, min(60, (int) ($_GET['limit'] ?? 12)));
        $categoryId = isset($_GET['category_id']) ? (int) $_GET['category_id'] : null;
        $search     = $_GET['search'] ?? null;

        $result = $this->shopService->getProducts($page, $limit, $categoryId, $search);

        // Serialize images map
        $imagesMap = [];
        foreach ($result['images'] as $productId => $imgDto) {
            $imagesMap[$productId] = $imgDto ? ApiResponse::dto($imgDto) : null;
        }

        $products = $this->serializeProductsWithImageUrl($result['data'], $result['images']);

        ApiResponse::paginated(
            $products,
            [
                'page'   => $result['page'],
                'limit'  => $result['limit'],
                'total'  => $result['total'],
                'images' => $imagesMap,
            ]
        );
    }

    /**
     * GET /api/shop/products/slug/{slug}
     */
    public function showBySlug(string $slug): void
    {
        $data = $this->shopService->getProductDetailBySlug($slug);
        if (!$data) {
            ApiResponse::error('Product not found.', 404);
        }

        $images = ApiResponse::dtoList($data['images']);
        $product = ApiResponse::dto($data['product']);
        $product['image_url'] = $this->extractPrimaryImageUrl($images);

        ApiResponse::success([
            'product' => $product,
            'images'  => $images,
        ]);
    }

    /**
     * GET /api/shop/products/{id}
     */
    public function show(int $id): void
    {
        $data = $this->shopService->getProductDetail($id);
        if (!$data) {
            ApiResponse::error('Product not found.', 404);
        }

        $images = ApiResponse::dtoList($data['images']);
        $product = ApiResponse::dto($data['product']);
        $product['image_url'] = $this->extractPrimaryImageUrl($images);

        ApiResponse::success([
            'product' => $product,
            'images'  => $images,
        ]);
    }

    /**
     * GET /api/shop/categories
     */
    public function categories(): void
    {
        $categories = $this->shopService->getCategories();
        ApiResponse::success(ApiResponse::dtoList($categories));
    }

    /**
     * GET /api/shop/featured?badge=&limit=
     */
    public function featured(): void
    {
        $badge = $_GET['badge'] ?? 'hot';
        $limit = max(1, min(50, (int) ($_GET['limit'] ?? 8)));

        $result = $this->shopService->getFeatured($badge, $limit);
        ApiResponse::success($this->serializeProductsWithImageUrl($result['data'], $result['images']));
    }

    /**
     * @param ProductDTO[] $products
     * @param array<int, ProductImageDTO|null> $images
     * @return array<int, array<string, mixed>>
     */
    private function serializeProductsWithImageUrl(array $products, array $images): array
    {
        $payload = [];

        foreach ($products as $dto) {
            $item = ApiResponse::dto($dto);
            $imgDto = $images[$dto->id] ?? null;
            $item['image_url'] = $imgDto ? $imgDto->url : null;
            $payload[] = $item;
        }

        return $payload;
    }

    /**
     * @param array<int, array<string, mixed>> $images
     */
    private function extractPrimaryImageUrl(array $images): ?string
    {
        foreach ($images as $img) {
            if (!empty($img['is_primary'])) {
                return $img['url'] ?? null;
            }
        }

        return $images[0]['url'] ?? null;
    }
}
