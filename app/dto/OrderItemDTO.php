<?php
require_once __DIR__ . '/../models/OrderItem.php';

class OrderItemDTO
{
    public ?int  $id;
    public ?int  $orderId;
    public ?int  $productId;
    public int   $quantity;
    public float $unitPrice;

    public function __construct(
        ?int  $id        = null,
        ?int  $orderId   = null,
        ?int  $productId = null,
        int   $quantity  = 1,
        float $unitPrice = 0.00
    ) {
        $this->id        = $id;
        $this->orderId   = $orderId;
        $this->productId = $productId;
        $this->quantity  = $quantity;
        $this->unitPrice = $unitPrice;
    }

    // ── Mapping ──────────────────────────────────────────────

    public static function fromArray(array $row): static
    {
        return new static(
            isset($row['id'])         ? (int)   $row['id']         : null,
            isset($row['order_id'])   ? (int)   $row['order_id']   : null,
            isset($row['product_id']) ? (int)   $row['product_id'] : null,
            isset($row['quantity'])   ? (int)   $row['quantity']   : 1,
            isset($row['unit_price']) ? (float) $row['unit_price'] : 0.00
        );
    }

    public static function fromModel(OrderItem $model): static
    {
        return new static(
            $model->getId(),
            $model->getOrderId(),
            $model->getProductId(),
            (int)   $model->getQuantity(),
            (float) $model->getUnitPrice()
        );
    }

    public function toArray(): array
    {
        return [
            'order_id'   => $this->orderId,
            'product_id' => $this->productId,
            'quantity'   => $this->quantity,
            'unit_price' => $this->unitPrice,
        ];
    }
}
