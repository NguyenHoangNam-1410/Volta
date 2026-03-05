<?php
require_once __DIR__ . '/../models/ProductRelation.php';

class ProductRelationDTO
{
    public ?int   $id;
    public ?int   $productId;
    public ?int   $relatedId;
    public string $type;
    public float  $discountAmount;
    public int    $sortOrder;

    public function __construct(
        ?int   $id             = null,
        ?int   $productId      = null,
        ?int   $relatedId      = null,
        string $type           = 'crosssell',
        float  $discountAmount = 0.00,
        int    $sortOrder      = 0
    ) {
        $this->id             = $id;
        $this->productId      = $productId;
        $this->relatedId      = $relatedId;
        $this->type           = $type;
        $this->discountAmount = $discountAmount;
        $this->sortOrder      = $sortOrder;
    }

    // ── Mapping ──────────────────────────────────────────────

    public static function fromArray(array $row): static
    {
        return new static(
            isset($row['id'])              ? (int)   $row['id']              : null,
            isset($row['product_id'])      ? (int)   $row['product_id']      : null,
            isset($row['related_id'])      ? (int)   $row['related_id']      : null,
            $row['type']            ?? 'crosssell',
            isset($row['discount_amount']) ? (float) $row['discount_amount'] : 0.00,
            isset($row['sort_order'])      ? (int)   $row['sort_order']      : 0
        );
    }

    public static function fromModel(ProductRelation $model): static
    {
        return new static(
            $model->getId(),
            $model->getProductId(),
            $model->getRelatedId(),
            $model->getType(),
            (float) $model->getDiscountAmount(),
            (int)   $model->getSortOrder()
        );
    }

    public function toArray(): array
    {
        return [
            'product_id'      => $this->productId,
            'related_id'      => $this->relatedId,
            'type'            => $this->type,
            'discount_amount' => $this->discountAmount,
            'sort_order'      => $this->sortOrder,
        ];
    }
}
