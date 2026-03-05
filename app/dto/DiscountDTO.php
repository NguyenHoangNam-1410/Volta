<?php
require_once __DIR__ . '/../models/Discount.php';

class DiscountDTO
{
    public ?int    $id;
    public string  $code;
    public string  $type;           // 'percent' | 'fixed'
    public float   $value;
    public float   $minOrder;
    public ?int    $usesRemaining;  // null = unlimited
    public ?string $expiresAt;

    public function __construct(
        ?int    $id            = null,
        string  $code          = '',
        string  $type          = 'percent',
        float   $value         = 0.00,
        float   $minOrder      = 0.00,
        ?int    $usesRemaining = null,
        ?string $expiresAt     = null
    ) {
        $this->id            = $id;
        $this->code          = $code;
        $this->type          = $type;
        $this->value         = $value;
        $this->minOrder      = $minOrder;
        $this->usesRemaining = $usesRemaining;
        $this->expiresAt     = $expiresAt;
    }

    // ── Mapping ──────────────────────────────────────────────

    public static function fromArray(array $row): static
    {
        return new static(
            isset($row['id'])              ? (int)   $row['id']              : null,
            $row['code']          ?? '',
            $row['type']          ?? 'percent',
            isset($row['value'])           ? (float) $row['value']           : 0.00,
            isset($row['min_order'])       ? (float) $row['min_order']       : 0.00,
            isset($row['uses_remaining'])  ? (int)   $row['uses_remaining']  : null,
            $row['expires_at']    ?? null
        );
    }

    public static function fromModel(Discount $model): static
    {
        return new static(
            $model->getId(),
            $model->getCode(),
            $model->getType(),
            (float) $model->getValue(),
            (float) $model->getMinOrder(),
            $model->getUsesRemaining() !== null ? (int) $model->getUsesRemaining() : null,
            $model->getExpiresAt()
        );
    }

    public function toArray(): array
    {
        return [
            'code'           => $this->code,
            'type'           => $this->type,
            'value'          => $this->value,
            'min_order'      => $this->minOrder,
            'uses_remaining' => $this->usesRemaining,
            'expires_at'     => $this->expiresAt,
        ];
    }

    // ── Helpers ──────────────────────────────────────────────

    /** Check if the discount is still valid right now. */
    public function isValid(): bool
    {
        if ($this->usesRemaining !== null && $this->usesRemaining <= 0) return false;
        if ($this->expiresAt !== null && strtotime($this->expiresAt) < time()) return false;
        return true;
    }

    /**
     * Calculate the discount amount for the given order subtotal.
     * Returns 0 if the minimum order requirement is not met.
     */
    public function calculate(float $subtotal): float
    {
        if ($subtotal < $this->minOrder) return 0.00;
        if ($this->type === 'percent') return round($subtotal * $this->value / 100, 2);
        return min($this->value, $subtotal); // fixed discount can't exceed subtotal
    }
}
