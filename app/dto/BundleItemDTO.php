<?php
require_once __DIR__ . '/../models/BundleItem.php';

class BundleItemDTO
{
    public ?int $id;
    public ?int $bundleId;
    public ?int $productId;
    public ?string $productName;
    public ?string $productSlug;
    public ?float $productPrice;
    public ?string $productImageUrl;

    public function __construct(
        ?int $id        = null,
        ?int $bundleId  = null,
        ?int $productId = null,
        ?string $productName = null,
        ?string $productSlug = null,
        ?float $productPrice = null,
        ?string $productImageUrl = null
    ) {
        $this->id        = $id;
        $this->bundleId  = $bundleId;
        $this->productId = $productId;
        $this->productName = $productName;
        $this->productSlug = $productSlug;
        $this->productPrice = $productPrice;
        $this->productImageUrl = $productImageUrl;
    }

    // ── Mapping ──────────────────────────────────────────────

    public static function fromArray(array $row): static
    {
        return new static(
            isset($row['id'])         ? (int) $row['id']         : null,
            isset($row['bundle_id'])  ? (int) $row['bundle_id']  : null,
            isset($row['product_id']) ? (int) $row['product_id'] : null,
            $row['name'] ?? null,
            $row['slug'] ?? null,
            isset($row['price']) ? (float) $row['price'] : null,
            $row['image_url'] ?? null
        );
    }

    public static function fromModel(BundleItem $model): static
    {
        return new static(
            $model->getId(),
            $model->getBundleId(),
            $model->getProductId()
        );
    }

    public function toArray(): array
    {
        $data = [
            'bundle_id'  => $this->bundleId,
            'product_id' => $this->productId,
        ];

        // When available (e.g. joined queries), expose product detail inside each bundle item.
        if ($this->productName !== null || $this->productSlug !== null || $this->productPrice !== null || $this->productImageUrl !== null) {
            $data['product'] = [
                'id' => $this->productId,
                'name' => $this->productName,
                'slug' => $this->productSlug,
                'price' => $this->productPrice,
                'image_url' => $this->productImageUrl,
            ];
        }

        return $data;
    }
}
