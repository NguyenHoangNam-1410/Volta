<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../dao/AddressDAO.php';
require_once __DIR__ . '/../dto/AddressDTO.php';

class AddressService
{
    private AddressDAO $addressDAO;

    public function __construct()
    {
        $this->addressDAO = new AddressDAO(Database::getConnection());
    }

    // ── READ ─────────────────────────────────────────────────

    /**
     * Get all addresses for a user.
     * @return AddressDTO[]
     */
    public function getByUser(int $userId): array
    {
        $rows = $this->addressDAO->findByUser($userId);
        return array_map([AddressDTO::class, 'fromArray'], $rows);
    }

    /**
     * Get a single address by ID.
     */
    public function getById(int $id): ?AddressDTO
    {
        $row = $this->addressDAO->findById($id);
        return $row ? AddressDTO::fromArray($row) : null;
    }

    /**
     * Get the default address for a user.
     */
    public function getDefault(int $userId): ?AddressDTO
    {
        $row = $this->addressDAO->findDefault($userId);
        return $row ? AddressDTO::fromArray($row) : null;
    }

    // ── WRITE ────────────────────────────────────────────────

    /**
     * Create a new address.
     * Returns the new address ID.
     */
    public function create(int $userId, array $data): int
    {
        $dto = new AddressDTO(
            null,
            $userId,
            trim($data['label'] ?? ''),
            trim($data['street'] ?? ''),
            trim($data['city'] ?? ''),
            trim($data['country'] ?? ''),
            (bool) ($data['is_default'] ?? false)
        );

        if (empty($dto->street) || empty($dto->city)) {
            throw new \InvalidArgumentException('Street and city are required.');
        }

        // If this is set as default, unset previous default
        if ($dto->isDefault) {
            $this->addressDAO->setDefault($userId, 0); // clear all defaults first
        }

        $addressId = $this->addressDAO->insert($dto->toArray());

        // If this is the first address, make it default
        $addresses = $this->addressDAO->findByUser($userId);
        if (count($addresses) === 1) {
            $this->addressDAO->setDefault($userId, $addressId);
        } elseif ($dto->isDefault) {
            $this->addressDAO->setDefault($userId, $addressId);
        }

        return $addressId;
    }

    /**
     * Update an existing address.
     */
    public function update(int $id, array $data): int
    {
        $updateData = [];

        if (isset($data['label']))
            $updateData['label'] = trim($data['label']);
        if (isset($data['street']))
            $updateData['street'] = trim($data['street']);
        if (isset($data['city']))
            $updateData['city'] = trim($data['city']);
        if (isset($data['country']))
            $updateData['country'] = trim($data['country']);
        if (isset($data['is_default']))
            $updateData['is_default'] = $data['is_default'] ? 1 : 0;

        if (empty($updateData))
            return 0;

        return $this->addressDAO->update($id, $updateData);
    }

    /**
     * Delete an address by ID.
     */
    public function delete(int $id): int
    {
        return $this->addressDAO->delete($id);
    }

    /**
     * Set an address as the default for a user.
     */
    public function setDefault(int $userId, int $addressId): void
    {
        $this->addressDAO->setDefault($userId, $addressId);
    }
}
