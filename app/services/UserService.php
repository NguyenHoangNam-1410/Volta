<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../dao/UserDAO.php';
require_once __DIR__ . '/../dto/UserDTO.php';

class UserService
{
    private UserDAO $userDAO;

    public function __construct()
    {
        $this->userDAO = new UserDAO(Database::getConnection());
    }

    // ── READ ─────────────────────────────────────────────────

    /**
     * Get all users, ordered by newest first.
     * @return UserDTO[]
     */
    public function getAll(): array
    {
        $rows = $this->userDAO->findAll('created_at', 'DESC');
        return array_map([UserDTO::class, 'fromArray'], $rows);
    }

    /**
     * Get a single user by ID.
     */
    public function getById(int $id): ?UserDTO
    {
        $row = $this->userDAO->findById($id);
        return $row ? UserDTO::fromArray($row) : null;
    }

    /**
     * Find a user by email.
     */
    public function getByEmail(string $email): ?UserDTO
    {
        $row = $this->userDAO->findByEmail($email);
        return $row ? UserDTO::fromArray($row) : null;
    }

    /**
     * Search users with pagination.
     * Returns ['data' => UserDTO[], 'total' => int, 'page' => int, 'limit' => int]
     */
    public function search(string $keyword, int $page = 1, int $limit = 20): array
    {
        $result = $this->userDAO->search($keyword, $page, $limit);
        $result['data'] = array_map([UserDTO::class, 'fromArray'], $result['data']);
        return $result;
    }

    /**
     * Paginate users.
     * Returns ['data' => UserDTO[], 'total' => int, 'page' => int, 'limit' => int]
     */
    public function paginate(int $page = 1, int $limit = 20): array
    {
        $result = $this->userDAO->paginate($page, $limit, [], 'created_at', 'DESC');
        $result['data'] = array_map([UserDTO::class, 'fromArray'], $result['data']);
        return $result;
    }

    // ── WRITE ────────────────────────────────────────────────

    /**
     * Create a new user (hashes password automatically).
     * Returns the new user ID.
     */
    public function create(array $data): int
    {
        $email = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';
        $fullName = trim($data['full_name'] ?? '');
        $phone = trim($data['phone'] ?? '');

        if (empty($email) || empty($password)) {
            throw new \InvalidArgumentException('Email and password are required.');
        }

        if ($this->userDAO->findByEmail($email)) {
            throw new \RuntimeException('Email already exists.');
        }

        return $this->userDAO->register($email, $password, $fullName, $phone);
    }

    /**
     * Update an existing user.
     * If 'password' is provided, it will be re-hashed.
     */
    public function update(int $id, array $data): int
    {
        $updateData = [];

        if (isset($data['email']))
            $updateData['email'] = trim($data['email']);
        if (isset($data['full_name']))
            $updateData['full_name'] = trim($data['full_name']);
        if (isset($data['phone']))
            $updateData['phone'] = trim($data['phone']);

        if (!empty($data['password'])) {
            $updateData['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        if (empty($updateData))
            return 0;

        return $this->userDAO->update($id, $updateData);
    }

    /**
     * Delete a user by ID.
     */
    public function delete(int $id): int
    {
        return $this->userDAO->delete($id);
    }

    // ── AUTH ─────────────────────────────────────────────────

    /**
     * Verify credentials. Returns UserDTO on success, null on failure.
     */
    public function authenticate(string $email, string $password): ?UserDTO
    {
        $row = $this->userDAO->findByEmail($email);
        if (!$row || !password_verify($password, $row['password_hash'])) {
            return null;
        }
        return UserDTO::fromArray($row);
    }

    /**
     * Count total users.
     */
    public function count(): int
    {
        return $this->userDAO->count();
    }
}
