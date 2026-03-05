<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../dao/DiscountDAO.php';
require_once __DIR__ . '/../dto/DiscountDTO.php';

class DiscountService
{
    private DiscountDAO $discountDAO;

    public function __construct()
    {
        $this->discountDAO = new DiscountDAO(Database::getConnection());
    }

    // ── READ ─────────────────────────────────────────────────

    /**
     * Get all discounts.
     * @return DiscountDTO[]
     */
    public function getAll(): array
    {
        $rows = $this->discountDAO->findAll('id', 'DESC');
        return array_map([DiscountDTO::class, 'fromArray'], $rows);
    }

    /**
     * Get a single discount by ID.
     */
    public function getById(int $id): ?DiscountDTO
    {
        $row = $this->discountDAO->findById($id);
        return $row ? DiscountDTO::fromArray($row) : null;
    }

    /**
     * Find a discount by promo code.
     */
    public function getByCode(string $code): ?DiscountDTO
    {
        $row = $this->discountDAO->findByCode($code);
        return $row ? DiscountDTO::fromArray($row) : null;
    }

    /**
     * Get all currently valid discounts.
     * @return DiscountDTO[]
     */
    public function getValid(): array
    {
        $rows = $this->discountDAO->findValid();
        return array_map([DiscountDTO::class, 'fromArray'], $rows);
    }

    /**
     * Paginate discounts.
     * Returns ['data' => DiscountDTO[], 'total', 'page', 'limit']
     */
    public function paginate(int $page = 1, int $limit = 20): array
    {
        $result = $this->discountDAO->paginate($page, $limit, [], 'id', 'DESC');
        $result['data'] = array_map([DiscountDTO::class, 'fromArray'], $result['data']);
        return $result;
    }

    // ── WRITE ────────────────────────────────────────────────

    /**
     * Create a new discount.
     * Returns the new discount ID.
     */
    public function create(array $data): int
    {
        $dto = new DiscountDTO(
            null,
            trim($data['code'] ?? ''),
            $data['type'] ?? 'percent',
            (float) ($data['value'] ?? 0),
            (float) ($data['min_order'] ?? 0),
            isset($data['uses_remaining']) ? (int) $data['uses_remaining'] : null,
            $data['expires_at'] ?? null
        );

        if (empty($dto->code)) {
            throw new \InvalidArgumentException('Discount code is required.');
        }

        // Check duplicate code
        if ($this->discountDAO->findByCode($dto->code)) {
            throw new \RuntimeException('Discount code already exists.');
        }

        return $this->discountDAO->insert($dto->toArray());
    }

    /**
     * Update an existing discount.
     */
    public function update(int $id, array $data): int
    {
        $updateData = [];

        if (isset($data['code']))
            $updateData['code'] = trim($data['code']);
        if (isset($data['type']))
            $updateData['type'] = $data['type'];
        if (isset($data['value']))
            $updateData['value'] = (float) $data['value'];
        if (isset($data['min_order']))
            $updateData['min_order'] = (float) $data['min_order'];
        if (array_key_exists('uses_remaining', $data)) {
            $updateData['uses_remaining'] = $data['uses_remaining'] !== null ? (int) $data['uses_remaining'] : null;
        }
        if (array_key_exists('expires_at', $data)) {
            $updateData['expires_at'] = $data['expires_at'];
        }

        if (empty($updateData))
            return 0;

        return $this->discountDAO->update($id, $updateData);
    }

    /**
     * Delete a discount by ID.
     */
    public function delete(int $id): int
    {
        return $this->discountDAO->delete($id);
    }

    // ── BUSINESS LOGIC ──────────────────────────────────────

    /**
     * Validate and apply a discount code to a subtotal.
     * Returns ['valid' => bool, 'amount' => float, 'message' => string, 'discount' => ?DiscountDTO]
     */
    public function applyCode(string $code, float $subtotal): array
    {
        $dto = $this->getByCode($code);

        if (!$dto) {
            return ['valid' => false, 'amount' => 0, 'message' => 'Invalid discount code.', 'discount' => null];
        }

        if (!$dto->isValid()) {
            return ['valid' => false, 'amount' => 0, 'message' => 'This discount code has expired or is used up.', 'discount' => null];
        }

        if ($subtotal < $dto->minOrder) {
            return [
                'valid' => false,
                'amount' => 0,
                'message' => "Minimum order amount is " . number_format($dto->minOrder, 2) . ".",
                'discount' => null,
            ];
        }

        $amount = $dto->calculate($subtotal);

        return [
            'valid' => true,
            'amount' => $amount,
            'message' => 'Discount applied successfully.',
            'discount' => $dto,
        ];
    }

    /**
     * Decrement uses_remaining after an order is placed.
     */
    public function decrementUse(int $id): int
    {
        return $this->discountDAO->decrementUse($id);
    }

    /**
     * Count total discounts.
     */
    public function count(): int
    {
        return $this->discountDAO->count();
    }
}
