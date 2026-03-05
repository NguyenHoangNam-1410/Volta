-- ── CATEGORIES ──────────────────────────────────────────────
CREATE TABLE categories (
    id          INT PRIMARY KEY AUTO_INCREMENT,
    name        VARCHAR(100) NOT NULL,
    slug        VARCHAR(100) NOT NULL UNIQUE  -- for SEO-friendly URLs
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── PRODUCTS ────────────────────────────────────────────────
CREATE TABLE products (
    id          INT PRIMARY KEY AUTO_INCREMENT,
    category_id INT NOT NULL,
    name        VARCHAR(200) NOT NULL,
    slug        VARCHAR(200) NOT NULL UNIQUE,  -- for SEO-friendly URLs
    description TEXT,
    price       DECIMAL(10,2) NOT NULL,
    stock       INT NOT NULL DEFAULT 0,
    badge       ENUM('none','new','hot','sale') DEFAULT 'none',
    is_active   BOOLEAN DEFAULT TRUE,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (category_id) REFERENCES categories(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── PRODUCT IMAGES ──────────────────────────────────────────
CREATE TABLE product_images (
    id          INT PRIMARY KEY AUTO_INCREMENT,
    product_id  INT NOT NULL,
    url         VARCHAR(500) NOT NULL,
    is_primary  BOOLEAN DEFAULT FALSE,

    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── UPSELL / CROSS-SELL LINKS ────────────────────────────────
-- Covers both "Upsell" (upgrade suggestion) and
-- "Cross-sell" (Frequently Bought Together)
CREATE TABLE product_relations (
    id              INT PRIMARY KEY AUTO_INCREMENT,
    product_id      INT NOT NULL,               -- the source product
    related_id      INT NOT NULL,               -- the suggested product
    type            ENUM('upsell','crosssell') NOT NULL,
    discount_amount DECIMAL(10,2) DEFAULT 0,    -- bundle discount offered
    sort_order      INT DEFAULT 0,

    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (related_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE (product_id, related_id, type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── BUNDLES ──────────────────────────────────────────────────
CREATE TABLE bundles (
    id            INT PRIMARY KEY AUTO_INCREMENT,
    name          VARCHAR(200) NOT NULL,
    description   TEXT,
    bundle_price  DECIMAL(10,2) NOT NULL,       -- discounted total price
    is_active     BOOLEAN DEFAULT TRUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE bundle_items (
    id          INT PRIMARY KEY AUTO_INCREMENT,
    bundle_id   INT NOT NULL,
    product_id  INT NOT NULL,

    FOREIGN KEY (bundle_id) REFERENCES bundles(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── USERS ───────────────────────────────────────────────────
CREATE TABLE users (
    id            INT PRIMARY KEY AUTO_INCREMENT,
    email         VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name     VARCHAR(200),
    phone         VARCHAR(20),
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── ADDRESSES ───────────────────────────────────────────────
CREATE TABLE addresses (
    id          INT PRIMARY KEY AUTO_INCREMENT,
    user_id     INT NOT NULL,
    label       VARCHAR(50),                    -- e.g. "Home", "Work"
    street      VARCHAR(300) NOT NULL,
    city        VARCHAR(100) NOT NULL,
    country     VARCHAR(100) NOT NULL,
    is_default  BOOLEAN DEFAULT FALSE,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── ORDERS ──────────────────────────────────────────────────
CREATE TABLE orders (
    id              INT PRIMARY KEY AUTO_INCREMENT,
    user_id         INT NOT NULL,
    address_id      INT,
    status          ENUM('pending','paid','shipped','delivered','cancelled') DEFAULT 'pending',
    total_price     DECIMAL(10,2) NOT NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (address_id) REFERENCES addresses(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE order_items (
    id          INT PRIMARY KEY AUTO_INCREMENT,
    order_id    INT NOT NULL,
    product_id  INT NOT NULL,
    quantity    INT NOT NULL DEFAULT 1,
    unit_price  DECIMAL(10,2) NOT NULL,         -- price at time of purchase

    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── CART ────────────────────────────────────────────────────
CREATE TABLE cart_items (
    id          INT PRIMARY KEY AUTO_INCREMENT,
    user_id     INT NOT NULL,
    product_id  INT NOT NULL,
    quantity    INT NOT NULL DEFAULT 1,
    added_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    UNIQUE (user_id, product_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── DISCOUNTS / PROMO CODES ──────────────────────────────────
CREATE TABLE discounts (
    id              INT PRIMARY KEY AUTO_INCREMENT,
    code            VARCHAR(50) NOT NULL UNIQUE,
    type            ENUM('percent','fixed') NOT NULL,
    value           DECIMAL(10,2) NOT NULL,
    min_order       DECIMAL(10,2) DEFAULT 0,
    uses_remaining  INT DEFAULT NULL,           -- NULL = unlimited
    expires_at      TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── INDEXES ──────────────────────────────────────────────────

-- products: category browsing, shop listing filters, price sort
CREATE INDEX idx_products_category_id      ON products (category_id);
CREATE INDEX idx_products_is_active_badge  ON products (is_active, badge);
CREATE INDEX idx_products_price            ON products (price);
CREATE INDEX idx_products_created_at       ON products (created_at);

-- product_images: fetch all/primary image(s) for a product
CREATE INDEX idx_product_images_product_primary ON product_images (product_id, is_primary);

-- product_relations: reverse lookup (what products point to this one?)
CREATE INDEX idx_product_relations_related_id ON product_relations (related_id);

-- bundle_items: list products in a bundle; find bundles containing a product
CREATE INDEX idx_bundle_items_bundle_id   ON bundle_items (bundle_id);
CREATE INDEX idx_bundle_items_product_id  ON bundle_items (product_id);

-- addresses: user address list; fast default-address lookup
CREATE INDEX idx_addresses_user_default   ON addresses (user_id, is_default);

-- orders: order history per user (sorted newest first); admin status filter
CREATE INDEX idx_orders_user_created      ON orders (user_id, created_at DESC);
CREATE INDEX idx_orders_status            ON orders (status);

-- order_items: line items per order; per-product sales reporting
CREATE INDEX idx_order_items_order_id     ON order_items (order_id);
CREATE INDEX idx_order_items_product_id   ON order_items (product_id);

-- cart_items: product_id FK for cascade/reporting (user_id covered by UNIQUE)
CREATE INDEX idx_cart_items_product_id    ON cart_items (product_id);

-- discounts: promo-code validation (filter out expired / exhausted codes)
CREATE INDEX idx_discounts_expires_at     ON discounts (expires_at);
