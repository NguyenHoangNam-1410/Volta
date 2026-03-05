<?php
require_once __DIR__ . '/../models/Order.php';

class OrderDTO
{
    public ?int    $id;
    public ?int    $userId;
    public ?int    $addressId;
    public string  $status;
    public float   $totalPrice;
    public string  $createdAt;

    public function __construct(
        ?int   $id         = null,
        ?int   $userId     = null,
        ?int   $addressId  = null,
        string $status     = 'pending',
        float  $totalPrice = 0.00,
        string $createdAt  = ''
    ) {
        $this->id         = $id;
        $this->userId     = $userId;
        $this->addressId  = $addressId;
        $this->status     = $status;
        $this->totalPrice = $totalPrice;
        $this->createdAt  = $createdAt ?: date('Y-m-d H:i:s');
    }

    // ── Mapping ──────────────────────────────────────────────

    public static function fromArray(array $row): static
    {
        return new static(
            isset($row['id'])          ? (int)   $row['id']          : null,
            isset($row['user_id'])     ? (int)   $row['user_id']     : null,
            isset($row['address_id'])  ? (int)   $row['address_id']  : null,
            $row['status']      ?? 'pending',
            isset($row['total_price']) ? (float) $row['total_price'] : 0.00,
            $row['created_at']  ?? date('Y-m-d H:i:s')
        );
    }

    public static function fromModel(Order $model): static
    {
        return new static(
            $model->getId(),
            $model->getUserId(),
            $model->getAddressId(),
            $model->getStatus(),
            (float) $model->getTotalPrice(),
            $model->getCreatedAt()
        );
    }

    public function toArray(): array
    {
        return [
            'user_id'     => $this->userId,
            'address_id'  => $this->addressId,
            'status'      => $this->status,
            'total_price' => $this->totalPrice,
        ];
    }
}
