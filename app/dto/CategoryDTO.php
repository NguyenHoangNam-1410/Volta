<?php
require_once __DIR__ . '/../models/Category.php';

class CategoryDTO
{
    public ?int    $id;
    public string  $name;
    public string  $slug;

    public function __construct(
        ?int   $id   = null,
        string $name = '',
        string $slug = ''
    ) {
        $this->id   = $id;
        $this->name = $name;
        $this->slug = $slug;
    }

    // ── Mapping ──────────────────────────────────────────────

    /** Map a raw DB row (associative array) to a DTO. */
    public static function fromArray(array $row): static
    {
        return new static(
            isset($row['id'])   ? (int) $row['id'] : null,
            $row['name'] ?? '',
            $row['slug'] ?? ''
        );
    }

    /** Map a Category model instance to a DTO. */
    public static function fromModel(Category $model): static
    {
        return new static(
            $model->getId(),
            $model->getName(),
            $model->getSlug()
        );
    }

    /** Convert to an array suitable for DAO insert/update (excludes id). */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'slug' => $this->slug,
        ];
    }
}
