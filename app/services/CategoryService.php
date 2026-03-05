<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../dao/CategoryDAO.php';
require_once __DIR__ . '/../dto/CategoryDTO.php';

class CategoryService
{
    private CategoryDAO $categoryDAO;

    public function __construct()
    {
        $this->categoryDAO = new CategoryDAO(Database::getConnection());
    }

    // ── READ ─────────────────────────────────────────────────

    /**
     * Get all categories.
     * @return CategoryDTO[]
     */
    public function getAll(): array
    {
        $rows = $this->categoryDAO->findAll('name', 'ASC');
        return array_map([CategoryDTO::class, 'fromArray'], $rows);
    }

    /**
     * Get a single category by ID.
     */
    public function getById(int $id): ?CategoryDTO
    {
        $row = $this->categoryDAO->findById($id);
        return $row ? CategoryDTO::fromArray($row) : null;
    }

    /**
     * Find a category by slug.
     */
    public function getBySlug(string $slug): ?CategoryDTO
    {
        $row = $this->categoryDAO->findBySlug($slug);
        return $row ? CategoryDTO::fromArray($row) : null;
    }

    /**
     * Paginate categories.
     * Returns ['data' => CategoryDTO[], 'total', 'page', 'limit']
     */
    public function paginate(int $page = 1, int $limit = 20): array
    {
        $result = $this->categoryDAO->paginate($page, $limit, [], 'name', 'ASC');
        $result['data'] = array_map([CategoryDTO::class, 'fromArray'], $result['data']);
        return $result;
    }

    // ── WRITE ────────────────────────────────────────────────

    /**
     * Create a new category.
     * Returns the new category ID.
     */
    public function create(array $data): int
    {
        $name = trim($data['name'] ?? '');
        $slug = trim($data['slug'] ?? $this->generateSlug($name));

        if (empty($name)) {
            throw new \InvalidArgumentException('Category name is required.');
        }

        // Check duplicate slug
        if ($this->categoryDAO->findBySlug($slug)) {
            throw new \RuntimeException('Category slug already exists.');
        }

        $dto = new CategoryDTO(null, $name, $slug);
        return $this->categoryDAO->insert($dto->toArray());
    }

    /**
     * Update an existing category.
     */
    public function update(int $id, array $data): int
    {
        $updateData = [];

        if (isset($data['name']))
            $updateData['name'] = trim($data['name']);
        if (isset($data['slug']))
            $updateData['slug'] = trim($data['slug']);

        if (empty($updateData))
            return 0;

        return $this->categoryDAO->update($id, $updateData);
    }

    /**
     * Delete a category by ID.
     */
    public function delete(int $id): int
    {
        return $this->categoryDAO->delete($id);
    }

    /**
     * Count total categories.
     */
    public function count(): int
    {
        return $this->categoryDAO->count();
    }

    // ── HELPERS ──────────────────────────────────────────────

    private function generateSlug(string $name): string
    {
        $slug = strtolower(trim($name));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        return trim($slug, '-');
    }
}
