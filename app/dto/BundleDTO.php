<?php
require_once __DIR__ . '/../models/Bundle.php';

class BundleDTO
{
    public ?int    $id;
    public string  $name;
    public string  $description;
    public float   $bundlePrice;
    public bool    $isActive;
    public ?float  $totalProductPrice;
    public ?float  $offPercentage;

    public function __construct(
        ?int   $id          = null,
        string $name        = '',
        string $description = '',
        float  $bundlePrice = 0.00,
        bool   $isActive    = true,
        ?float $totalProductPrice = null,
        ?float $offPercentage = null
    ) {
        $this->id          = $id;
        $this->name        = $name;
        $this->description = $description;
        $this->bundlePrice = $bundlePrice;
        $this->isActive    = $isActive;
        $this->totalProductPrice = $totalProductPrice;
        $this->offPercentage = $offPercentage;
    }

    // ── Mapping ──────────────────────────────────────────────

    public static function fromArray(array $row): static
    {
        return new static(
            isset($row['id'])           ? (int)   $row['id']           : null,
            $row['name']        ?? '',
            $row['description'] ?? '',
            isset($row['bundle_price']) ? (float) $row['bundle_price'] : 0.00,
            isset($row['is_active'])    ? (bool)  $row['is_active']    : true,
            isset($row['total_product_price']) ? (float) $row['total_product_price'] : null,
            isset($row['off_percentage']) ? (float) $row['off_percentage'] : null
        );
    }

    public static function fromModel(Bundle $model): static
    {
        return new static(
            $model->getId(),
            $model->getName(),
            $model->getDescription(),
            (float) $model->getBundlePrice(),
            (bool)  $model->getIsActive(),
            null,
            null
        );
    }

    public function toArray(): array
    {
        $data = [
            'name'         => $this->name,
            'description'  => $this->description,
            'bundle_price' => $this->bundlePrice,
            'is_active'    => $this->isActive ? 1 : 0,
        ];

        // Computed fields for response payload only.
        if ($this->totalProductPrice !== null) {
            $data['total_product_price'] = $this->totalProductPrice;
        }
        if ($this->offPercentage !== null) {
            $data['off_percentage'] = $this->offPercentage;
        }

        return $data;
    }
}
