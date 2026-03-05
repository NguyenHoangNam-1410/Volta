<?php
require_once __DIR__ . '/../models/Bundle.php';

class BundleDTO
{
    public ?int    $id;
    public string  $name;
    public string  $description;
    public float   $bundlePrice;
    public bool    $isActive;

    public function __construct(
        ?int   $id          = null,
        string $name        = '',
        string $description = '',
        float  $bundlePrice = 0.00,
        bool   $isActive    = true
    ) {
        $this->id          = $id;
        $this->name        = $name;
        $this->description = $description;
        $this->bundlePrice = $bundlePrice;
        $this->isActive    = $isActive;
    }

    // ── Mapping ──────────────────────────────────────────────

    public static function fromArray(array $row): static
    {
        return new static(
            isset($row['id'])           ? (int)   $row['id']           : null,
            $row['name']        ?? '',
            $row['description'] ?? '',
            isset($row['bundle_price']) ? (float) $row['bundle_price'] : 0.00,
            isset($row['is_active'])    ? (bool)  $row['is_active']    : true
        );
    }

    public static function fromModel(Bundle $model): static
    {
        return new static(
            $model->getId(),
            $model->getName(),
            $model->getDescription(),
            (float) $model->getBundlePrice(),
            (bool)  $model->getIsActive()
        );
    }

    public function toArray(): array
    {
        return [
            'name'         => $this->name,
            'description'  => $this->description,
            'bundle_price' => $this->bundlePrice,
            'is_active'    => $this->isActive ? 1 : 0,
        ];
    }
}
