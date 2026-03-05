<?php
require_once __DIR__ . '/../models/User.php';

/**
 * UserDTO — never exposes password_hash in toArray() by default.
 * Use toArrayWithPassword() only when inserting a new user row.
 */
class UserDTO
{
    public ?int    $id;
    public string  $email;
    public string  $passwordHash;   // stored hash — never send to views
    public string  $fullName;
    public string  $phone;
    public string  $createdAt;

    public function __construct(
        ?int   $id           = null,
        string $email        = '',
        string $passwordHash = '',
        string $fullName     = '',
        string $phone        = '',
        string $createdAt    = ''
    ) {
        $this->id           = $id;
        $this->email        = $email;
        $this->passwordHash = $passwordHash;
        $this->fullName     = $fullName;
        $this->phone        = $phone;
        $this->createdAt    = $createdAt ?: date('Y-m-d H:i:s');
    }

    // ── Mapping ──────────────────────────────────────────────

    public static function fromArray(array $row): static
    {
        return new static(
            isset($row['id'])            ? (int) $row['id'] : null,
            $row['email']         ?? '',
            $row['password_hash'] ?? '',
            $row['full_name']     ?? '',
            $row['phone']         ?? '',
            $row['created_at']    ?? date('Y-m-d H:i:s')
        );
    }

    public static function fromModel(User $model): static
    {
        return new static(
            $model->getId(),
            $model->getEmail(),
            $model->getPasswordHash(),
            $model->getFullName(),
            $model->getPhone(),
            $model->getCreatedAt()
        );
    }

    /** Safe array for views — omits password_hash. */
    public function toArray(): array
    {
        return [
            'email'      => $this->email,
            'full_name'  => $this->fullName,
            'phone'      => $this->phone,
            'created_at' => $this->createdAt,
        ];
    }

    /** Full array for DB insert (includes password_hash). */
    public function toArrayWithPassword(): array
    {
        return array_merge($this->toArray(), ['password_hash' => $this->passwordHash]);
    }
}
