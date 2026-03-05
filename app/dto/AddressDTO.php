<?php
require_once __DIR__ . '/../models/Address.php';

class AddressDTO
{
    public ?int    $id;
    public ?int    $userId;
    public string  $label;
    public string  $street;
    public string  $city;
    public string  $country;
    public bool    $isDefault;

    public function __construct(
        ?int   $id        = null,
        ?int   $userId    = null,
        string $label     = '',
        string $street    = '',
        string $city      = '',
        string $country   = '',
        bool   $isDefault = false
    ) {
        $this->id        = $id;
        $this->userId    = $userId;
        $this->label     = $label;
        $this->street    = $street;
        $this->city      = $city;
        $this->country   = $country;
        $this->isDefault = $isDefault;
    }

    // ── Mapping ──────────────────────────────────────────────

    public static function fromArray(array $row): static
    {
        return new static(
            isset($row['id'])         ? (int)  $row['id']         : null,
            isset($row['user_id'])    ? (int)  $row['user_id']    : null,
            $row['label']   ?? '',
            $row['street']  ?? '',
            $row['city']    ?? '',
            $row['country'] ?? '',
            isset($row['is_default']) ? (bool) $row['is_default'] : false
        );
    }

    public static function fromModel(Address $model): static
    {
        return new static(
            $model->getId(),
            $model->getUserId(),
            $model->getLabel(),
            $model->getStreet(),
            $model->getCity(),
            $model->getCountry(),
            (bool) $model->getIsDefault()
        );
    }

    public function toArray(): array
    {
        return [
            'user_id'    => $this->userId,
            'label'      => $this->label,
            'street'     => $this->street,
            'city'       => $this->city,
            'country'    => $this->country,
            'is_default' => $this->isDefault ? 1 : 0,
        ];
    }
}
