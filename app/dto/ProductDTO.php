<?php
require_once __DIR__ . '/../models/Product.php';

class ProductDTO
{
    public ?int    $id;
    public ?int    $categoryId;
    public string  $name;
    public string  $slug;
    public string  $description;
    public float   $price;
    public int     $stock;
    public string  $badge;
    public bool    $isActive;
    public string  $createdAt;

    public function __construct(
        ?int   $id          = null,
        ?int   $categoryId  = null,
        string $name        = '',
        string $slug        = '',
        string $description = '',
        float  $price       = 0.00,
        int    $stock       = 0,
        string $badge       = 'none',
        bool   $isActive    = true,
        string $createdAt   = ''
    ) {
        $this->id          = $id;
        $this->categoryId  = $categoryId;
        $this->name        = $name;
        $this->slug        = $slug;
        $this->description = $description;
        $this->price       = $price;
        $this->stock       = $stock;
        $this->badge       = $badge;
        $this->isActive    = $isActive;
        $this->createdAt   = $createdAt ?: date('Y-m-d H:i:s');
    }

    // ── Mapping ──────────────────────────────────────────────

    public static function fromArray(array $row): static
    {
        return new static(
            isset($row['id'])          ? (int)   $row['id']          : null,
            isset($row['category_id']) ? (int)   $row['category_id'] : null,
            $row['name']        ?? '',
            $row['slug']        ?? '',
            $row['description'] ?? '',
            isset($row['price'])       ? (float) $row['price']       : 0.00,
            isset($row['stock'])       ? (int)   $row['stock']       : 0,
            $row['badge']       ?? 'none',
            isset($row['is_active'])   ? (bool)  $row['is_active']   : true,
            $row['created_at']  ?? date('Y-m-d H:i:s')
        );
    }

    public static function fromModel(Product $model): static
    {
        return new static(
            $model->getId(),
            $model->getCategoryId(),
            $model->getName(),
            $model->getSlug(),
            $model->getDescription(),
            (float) $model->getPrice(),
            (int)   $model->getStock(),
            $model->getBadge(),
            (bool)  $model->getIsActive(),
            $model->getCreatedAt()
        );
    }

    public function toArray(): array
    {
        return [
            'category_id' => $this->categoryId,
            'name'        => $this->name,
            'slug'        => $this->slug,
            'description' => $this->description,
            'price'       => $this->price,
            'stock'       => $this->stock,
            'badge'       => $this->badge,
            'is_active'   => $this->isActive ? 1 : 0,
        ];
    }
}
