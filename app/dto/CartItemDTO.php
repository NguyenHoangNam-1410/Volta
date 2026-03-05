<?php
require_once __DIR__ . '/../models/CartItem.php';

class CartItemDTO
{
    public ?int    $id;
    public ?int    $userId;
    public ?int    $productId;
    public int     $quantity;
    public string  $addedAt;

    // Optional join fields populated by CartDAO::findByUser()
    public ?string $productName  = null;
    public ?string $productSlug  = null;
    public ?float  $productPrice = null;
    public ?int    $productStock = null;
    public ?string $imageUrl     = null;

    public function __construct(
        ?int   $id        = null,
        ?int   $userId    = null,
        ?int   $productId = null,
        int    $quantity  = 1,
        string $addedAt   = ''
    ) {
        $this->id        = $id;
        $this->userId    = $userId;
        $this->productId = $productId;
        $this->quantity  = $quantity;
        $this->addedAt   = $addedAt ?: date('Y-m-d H:i:s');
    }

    // ── Mapping ──────────────────────────────────────────────

    /** Maps a plain cart_items row or a joined row from CartDAO::findByUser(). */
    public static function fromArray(array $row): static
    {
        $dto = new static(
            isset($row['id'])         ? (int)   $row['id']         : null,
            isset($row['user_id'])    ? (int)   $row['user_id']    : null,
            isset($row['product_id']) ? (int)   $row['product_id'] : null,
            isset($row['quantity'])   ? (int)   $row['quantity']   : 1,
            $row['added_at'] ?? date('Y-m-d H:i:s')
        );

        // Joined product fields (present when loaded via CartDAO::findByUser)
        $dto->productName  = $row['name']          ?? null;
        $dto->productSlug  = $row['slug']          ?? null;
        $dto->productPrice = isset($row['price'])  ? (float) $row['price'] : null;
        $dto->productStock = isset($row['stock'])  ? (int)   $row['stock'] : null;
        $dto->imageUrl     = $row['image_url']     ?? null;

        return $dto;
    }

    public static function fromModel(CartItem $model): static
    {
        return new static(
            $model->getId(),
            $model->getUserId(),
            $model->getProductId(),
            (int) $model->getQuantity(),
            $model->getAddedAt()
        );
    }

    public function toArray(): array
    {
        return [
            'user_id'    => $this->userId,
            'product_id' => $this->productId,
            'quantity'   => $this->quantity,
        ];
    }
}
