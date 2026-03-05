<?php
require_once __DIR__ . '/../models/ProductImage.php';

class ProductImageDTO
{
    public ?int  $id;
    public ?int  $productId;
    public string $url;
    public bool  $isPrimary;

    public function __construct(
        ?int   $id        = null,
        ?int   $productId = null,
        string $url       = '',
        bool   $isPrimary = false
    ) {
        $this->id        = $id;
        $this->productId = $productId;
        $this->url       = $url;
        $this->isPrimary = $isPrimary;
    }

    // ── Mapping ──────────────────────────────────────────────

    public static function fromArray(array $row): static
    {
        return new static(
            isset($row['id'])         ? (int)  $row['id']         : null,
            isset($row['product_id']) ? (int)  $row['product_id'] : null,
            $row['url']        ?? '',
            isset($row['is_primary']) ? (bool) $row['is_primary'] : false
        );
    }

    public static function fromModel(ProductImage $model): static
    {
        return new static(
            $model->getId(),
            $model->getProductId(),
            $model->getUrl(),
            (bool) $model->getIsPrimary()
        );
    }

    public function toArray(): array
    {
        return [
            'product_id' => $this->productId,
            'url'        => $this->url,
            'is_primary' => $this->isPrimary ? 1 : 0,
        ];
    }
}
