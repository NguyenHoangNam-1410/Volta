<?php
require_once __DIR__ . '/../models/BundleItem.php';

class BundleItemDTO
{
    public ?int $id;
    public ?int $bundleId;
    public ?int $productId;

    public function __construct(
        ?int $id        = null,
        ?int $bundleId  = null,
        ?int $productId = null
    ) {
        $this->id        = $id;
        $this->bundleId  = $bundleId;
        $this->productId = $productId;
    }

    // ── Mapping ──────────────────────────────────────────────

    public static function fromArray(array $row): static
    {
        return new static(
            isset($row['id'])         ? (int) $row['id']         : null,
            isset($row['bundle_id'])  ? (int) $row['bundle_id']  : null,
            isset($row['product_id']) ? (int) $row['product_id'] : null
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
        return [
            'bundle_id'  => $this->bundleId,
            'product_id' => $this->productId,
        ];
    }
}
