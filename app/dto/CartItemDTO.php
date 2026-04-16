<?php
require_once __DIR__ . '/../models/CartItem.php';

class CartItemDTO
{
    public ?int    $id;
    public ?int    $userId;
    public ?int    $productId;
    public string  $itemType;
    public ?int    $bundleId;
    public int     $quantity;
    public string  $addedAt;

    // Optional join fields populated by CartDAO::findByUser()
    public ?string $productName  = null;
    public ?string $productSlug  = null;
    public ?float  $productPrice = null;
    public ?int    $productStock = null;
    public ?string $imageUrl     = null;

    public ?string $bundleName   = null;
    public ?float  $bundlePrice  = null;

    public function __construct(
        ?int   $id        = null,
        ?int   $userId    = null,
        ?int   $productId = null,
        string $itemType  = 'product',
        ?int   $bundleId  = null,
        int    $quantity  = 1,
        string $addedAt   = ''
    ) {
        $this->id        = $id;
        $this->userId    = $userId;
        $this->productId = $productId;
        $this->itemType  = $itemType;
        $this->bundleId  = $bundleId;
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
            $row['item_type'] ?? 'product',
            isset($row['bundle_id'])  ? (int)   $row['bundle_id']  : null,
            isset($row['quantity'])   ? (int)   $row['quantity']   : 1,
            $row['added_at'] ?? date('Y-m-d H:i:s')
        );

        // Joined product fields (present when loaded via CartDAO::findByUser)
        $dto->productName  = $row['name']          ?? null;
        $dto->productSlug  = $row['slug']          ?? null;
        $dto->productPrice = isset($row['price'])  ? (float) $row['price'] : null;
        $dto->productStock = isset($row['stock'])  ? (int)   $row['stock'] : null;
        $dto->imageUrl     = $row['image_url']     ?? null;

        // Joined bundle fields
        $dto->bundleName   = $row['bundle_name'] ?? null;
        $dto->bundlePrice  = isset($row['bundle_price']) ? (float) $row['bundle_price'] : null;

        return $dto;
    }

    public static function fromModel(CartItem $model): static
    {
        return new static(
            $model->getId(),
            $model->getUserId(),
            $model->getProductId(),
            'product',
            null,
            (int) $model->getQuantity(),
            $model->getAddedAt()
        );
    }

    public function toArray(): array
    {
        return [
            'user_id'    => $this->userId,
            'product_id' => $this->productId,
            'item_type'  => $this->itemType,
            'bundle_id'  => $this->bundleId,
            'quantity'   => $this->quantity,
        ];
    }
}
