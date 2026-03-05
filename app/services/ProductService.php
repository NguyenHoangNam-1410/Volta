<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../dao/ProductDAO.php';
require_once __DIR__ . '/../dao/ProductImageDAO.php';
require_once __DIR__ . '/../dao/ProductRelationDAO.php';
require_once __DIR__ . '/../dto/ProductDTO.php';
require_once __DIR__ . '/../dto/ProductImageDTO.php';
require_once __DIR__ . '/../dto/ProductRelationDTO.php';

class ProductService
{
    private ProductDAO $productDAO;
    private ProductImageDAO $imageDAO;
    private ProductRelationDAO $relationDAO;

    public function __construct()
    {
        $pdo = Database::getConnection();
        $this->productDAO = new ProductDAO($pdo);
        $this->imageDAO = new ProductImageDAO($pdo);
        $this->relationDAO = new ProductRelationDAO($pdo);
    }
    /**
     * Get all products.
     * @return ProductDTO[]
     */
    public function getAll(): array
    {
        $rows = $this->productDAO->findAll('created_at', 'DESC');
        return array_map([ProductDTO::class, 'fromArray'], $rows);
    }

    /**
     * Get a single product by ID.
     */
    public function getById(int $id): ?ProductDTO
    {
        $row = $this->productDAO->findById($id);
        return $row ? ProductDTO::fromArray($row) : null;
    }

    /**
     * Find a product by slug.
     */
    public function getBySlug(string $slug): ?ProductDTO
    {
        $row = $this->productDAO->findBySlug($slug);
        return $row ? ProductDTO::fromArray($row) : null;
    }

    /**
     * Get active products by category.
     * @return ProductDTO[]
     */
    public function getByCategory(int $categoryId): array
    {
        $rows = $this->productDAO->findByCategory($categoryId);
        return array_map([ProductDTO::class, 'fromArray'], $rows);
    }

    /**
     * Get products with a specific badge.
     * @return ProductDTO[]
     */
    public function getByBadge(string $badge, int $limit = 10): array
    {
        $rows = $this->productDAO->findByBadge($badge, $limit);
        return array_map([ProductDTO::class, 'fromArray'], $rows);
    }

    /**
     * Search products with pagination.
     * Returns ['data' => ProductDTO[], 'total', 'page', 'limit']
     */
    public function search(string $keyword, int $page = 1, int $limit = 20): array
    {
        $result = $this->productDAO->search($keyword, $page, $limit);
        $result['data'] = array_map([ProductDTO::class, 'fromArray'], $result['data']);
        return $result;
    }

    /**
     * Paginate products with optional conditions.
     * Returns ['data' => ProductDTO[], 'total', 'page', 'limit']
     */
    public function paginate(int $page = 1, int $limit = 20, array $conditions = []): array
    {
        $result = $this->productDAO->paginate($page, $limit, $conditions, 'created_at', 'DESC');
        $result['data'] = array_map([ProductDTO::class, 'fromArray'], $result['data']);
        return $result;
    }

    /**
     * Create a new product.
     * Returns the new product ID.
     */
    public function create(array $data): int
    {
        $dto = new ProductDTO(
            null,
            isset($data['category_id']) ? (int) $data['category_id'] : null,
            trim($data['name'] ?? ''),
            trim($data['slug'] ?? $this->generateSlug($data['name'] ?? '')),
            trim($data['description'] ?? ''),
            (float) ($data['price'] ?? 0),
            (int) ($data['stock'] ?? 0),
            $data['badge'] ?? 'none',
            isset($data['is_active']) ? (bool) $data['is_active'] : true
        );

        return $this->productDAO->insert($dto->toArray());
    }

    /**
     * Update an existing product.
     */
    public function update(int $id, array $data): int
    {
        $updateData = [];

        if (isset($data['category_id']))
            $updateData['category_id'] = (int) $data['category_id'];
        if (isset($data['name']))
            $updateData['name'] = trim($data['name']);
        if (isset($data['slug']))
            $updateData['slug'] = trim($data['slug']);
        if (isset($data['description']))
            $updateData['description'] = trim($data['description']);
        if (isset($data['price']))
            $updateData['price'] = (float) $data['price'];
        if (isset($data['stock']))
            $updateData['stock'] = (int) $data['stock'];
        if (isset($data['badge']))
            $updateData['badge'] = $data['badge'];
        if (isset($data['is_active']))
            $updateData['is_active'] = $data['is_active'] ? 1 : 0;

        if (empty($updateData))
            return 0;

        return $this->productDAO->update($id, $updateData);
    }

    /**
     * Delete a product and its images/relations.
     */
    public function delete(int $id): int
    {
        // Delete associated images from disk
        $images = $this->imageDAO->findByProduct($id);
        foreach ($images as $img) {
            $filePath = __DIR__ . '/../../' . $img['url'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        // Delete DB records (images first due to FK)
        $this->imageDAO->deleteByProduct($id);
        return $this->productDAO->delete($id);
    }

    /**
     * Count total products.
     */
    public function count(array $conditions = []): int
    {
        return $this->productDAO->count($conditions);
    }

    // ══════════════════════════════════════════════════════════
    //  PRODUCT IMAGES
    // ══════════════════════════════════════════════════════════

    /**
     * Get all images for a product.
     * @return ProductImageDTO[]
     */
    public function getImages(int $productId): array
    {
        $rows = $this->imageDAO->findByProduct($productId);
        return array_map([ProductImageDTO::class, 'fromArray'], $rows);
    }

    /**
     * Get a product's primary image.
     */
    public function getPrimaryImage(int $productId): ?ProductImageDTO
    {
        $row = $this->imageDAO->findPrimary($productId);
        return $row ? ProductImageDTO::fromArray($row) : null;
    }

    /**
     * Upload and store a product image.
     * Returns the new image ID on success, false on failure.
     */
    public function uploadImage(int $productId, array $file): int|false
    {
        if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }

        // Validate type
        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $fileType = mime_content_type($file['tmp_name']);
        if (!in_array($fileType, $allowed))
            return false;

        // Validate size (5MB)
        if ($file['size'] > 5 * 1024 * 1024)
            return false;

        // Create directory
        $uploadDir = __DIR__ . '/../../public/image/product/' . $productId . '/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Generate filename
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)) ?: 'jpg';
        $filename = uniqid('img_') . '_' . time() . '.' . $ext;
        $destPath = $uploadDir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            return false;
        }

        $relativePath = 'public/image/product/' . $productId . '/' . $filename;

        // Check if this is the first image → make it primary
        $existingImages = $this->imageDAO->findByProduct($productId);
        $isPrimary = empty($existingImages) ? 1 : 0;

        $imageId = $this->imageDAO->insert([
            'product_id' => $productId,
            'url' => $relativePath,
            'is_primary' => $isPrimary,
        ]);

        return $imageId;
    }

    /**
     * Set an image as the primary image for a product.
     */
    public function setPrimaryImage(int $productId, int $imageId): void
    {
        $this->imageDAO->setPrimary($productId, $imageId);
    }

    /**
     * Delete a product image.
     */
    public function deleteImage(int $imageId): int
    {
        $row = $this->imageDAO->findById($imageId);
        if ($row) {
            $filePath = __DIR__ . '/../../' . $row['url'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
        return $this->imageDAO->delete($imageId);
    }

    // ══════════════════════════════════════════════════════════
    //  PRODUCT RELATIONS
    // ══════════════════════════════════════════════════════════

    /**
     * Get upsell products for a given product.
     * @return ProductRelationDTO[]
     */
    public function getUpsells(int $productId): array
    {
        $rows = $this->relationDAO->findUpsells($productId);
        return array_map([ProductRelationDTO::class, 'fromArray'], $rows);
    }

    /**
     * Get cross-sell products for a given product.
     * @return ProductRelationDTO[]
     */
    public function getCrossSells(int $productId): array
    {
        $rows = $this->relationDAO->findCrossSells($productId);
        return array_map([ProductRelationDTO::class, 'fromArray'], $rows);
    }

    /**
     * Add a product relation.
     */
    public function addRelation(array $data): int
    {
        $dto = new ProductRelationDTO(
            null,
            (int) $data['product_id'],
            (int) $data['related_id'],
            $data['type'] ?? 'crosssell',
            (float) ($data['discount_amount'] ?? 0),
            (int) ($data['sort_order'] ?? 0)
        );
        return $this->relationDAO->insert($dto->toArray());
    }

    /**
     * Remove a product relation.
     */
    public function removeRelation(int $relationId): int
    {
        return $this->relationDAO->delete($relationId);
    }

    // ── HELPERS ──────────────────────────────────────────────

    private function generateSlug(string $name): string
    {
        $slug = strtolower(trim($name));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        return trim($slug, '-');
    }
}
