<?php
require_once __DIR__ . '/BaseDAO.php';

class CartDAO extends BaseDAO
{
    protected string $table      = 'cart_items';
    protected string $primaryKey = 'id';

    /**
     * Get all cart items for a user, joined with product info.
     */
    public function findByUser(int $userId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT ci.*, 'product' AS item_type, p.name, p.slug, p.price, p.stock,
                    (SELECT pi.url FROM product_images pi WHERE pi.product_id = p.id AND pi.is_primary = 1 LIMIT 1) AS image_url
             FROM {$this->table} ci
             JOIN products p ON ci.product_id = p.id
             WHERE ci.user_id = :uid
             ORDER BY ci.added_at DESC"
        );
        $stmt->execute([':uid' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Add a product to the cart (or increment quantity if exists).
     * Uses INSERT … ON DUPLICATE KEY UPDATE.
     */
    public function addItem(int $userId, int $productId, int $quantity = 1): void
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO {$this->table} (user_id, product_id, quantity)
             VALUES (:uid, :pid, :qty)
             ON DUPLICATE KEY UPDATE quantity = quantity + :qty2"
        );
        $stmt->execute([
            ':uid'  => $userId,
            ':pid'  => $productId,
            ':qty'  => $quantity,
            ':qty2' => $quantity,
        ]);
    }

    /**
     * Update quantity for a specific cart item.
     */
    public function updateQuantity(int $userId, int $productId, int $quantity): int
    {
        $stmt = $this->pdo->prepare(
            "UPDATE {$this->table} SET quantity = :qty WHERE user_id = :uid AND product_id = :pid"
        );
        $stmt->execute([':qty' => $quantity, ':uid' => $userId, ':pid' => $productId]);
        return $stmt->rowCount();
    }

    /**
     * Remove a specific product from a user's cart.
     */
    public function removeItem(int $userId, int $productId): int
    {
        $stmt = $this->pdo->prepare(
            "DELETE FROM {$this->table} WHERE user_id = :uid AND product_id = :pid"
        );
        $stmt->execute([':uid' => $userId, ':pid' => $productId]);
        return $stmt->rowCount();
    }

    /**
     * Clear all items from a user's cart.
     */
    public function clearCart(int $userId): int
    {
        $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE user_id = :uid");
        $stmt->execute([':uid' => $userId]);
        return $stmt->rowCount();
    }

    /**
     * Count distinct items in a user's cart.
     */
    public function countItems(int $userId): int
    {
        return $this->count(['user_id' => $userId]);
    }
}
