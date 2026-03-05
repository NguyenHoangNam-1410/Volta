-- ── CATEGORIES ──────────────────────────────────────────────
INSERT INTO categories (name, slug) VALUES
('Audio',       'audio'),
('Wearables',   'wearables'),
('Imaging',     'imaging'),
('Peripherals', 'peripherals'),
('Power',       'power'),
('Accessories', 'accessories');

-- ── PRODUCTS ────────────────────────────────────────────────
INSERT INTO products (category_id, name, slug, description, price, stock, badge) VALUES
-- Audio
(1, 'ProSound X3',     'prosound-x3',     'Active noise cancellation, 40hr battery, foldable design.',            249.00, 80,  'hot'),
(1, 'AirPods Nano V2', 'airpods-nano-v2', 'Hi-fi spatial audio, IPX5 waterproof, 36hr total battery life.',       129.00, 120, 'none'),
(1, 'BassBar Mini',    'bassbar-mini',    'Compact Bluetooth speaker, 360° sound, 24hr playback, dustproof.',      79.00, 60,  'new'),

-- Wearables
(2, 'Tempo Watch S2',  'tempo-watch-s2',  'Health sensors, GPS, 7-day battery. Titanium case.',                   349.00, 45,  'new'),
(2, 'FitBand Lite',    'fitband-lite',    'Sleep tracking, heart rate monitor, 14-day battery, ultra-light.',      59.00, 150, 'none'),

-- Imaging
(3, 'SnapMini Pro',    'snapmini-pro',    '48MP, 4K video, pocket-size mirrorless camera.',                       419.00, 30,  'sale'),
(3, 'LensMate 50mm',   'lensmate-50mm',   'f/1.8 prime lens, compatible with SnapMini Pro, razor-sharp portraits.',149.00, 25,  'none'),

-- Peripherals
(4, 'KeyFlow 75%',     'keyflow-75',      'Wireless mechanical, gasket mount, PBT keycaps.',                      189.00, 55,  'hot'),
(4, 'Flow Mouse MX',   'flow-mouse-mx',   'Ergonomic wireless mouse, 4000 DPI, silent clicks, 70hr battery.',      79.00, 90,  'none'),
(4, 'Desk Mat XL',     'desk-mat-xl',     'Non-slip 900×400mm desk mat, stitched edges, water-resistant.',          45.00, 200, 'none'),

-- Power
(5, 'PowerCore 140W',  'powercore-140w',  'Charge 4 devices simultaneously. GaN tech, ultra-slim.',                89.00, 100, 'new'),
(5, 'MagPad Duo',      'magpad-duo',      'Dual wireless charger, 15W fast charge, supports MagSafe.',             49.00, 75,  'none'),

-- Accessories
(6, 'Carry Case Pro',  'carry-case-pro',  'Hard-shell carry case, fits headphones + cables, TSA-friendly.',        39.00, 180, 'none'),
(6, 'USB-C Cable 3m',  'usbc-cable-3m',   '240W USB-C braided cable, 3 meters, supports 40Gbps data.',             19.00, 300, 'none'),
(6, 'Tech Pouch',      'tech-pouch',      'Organizer pouch for cables, adapters, and small gadgets.',              39.00, 160, 'none'),
(6, 'Sport Band Pack', 'sport-band-pack', 'Set of 3 silicone bands for Tempo Watch S2. Black, Blue, Sand.',        29.00, 110, 'none');

-- ── PRODUCT IMAGES ──────────────────────────────────────────
INSERT INTO product_images (product_id, url, is_primary) VALUES
(1,  '/images/products/prosound-x3-main.jpg',     1),
(1,  '/images/products/prosound-x3-side.jpg',      0),
(2,  '/images/products/airpods-nano-v2-main.jpg',  1),
(3,  '/images/products/bassbar-mini-main.jpg',     1),
(4,  '/images/products/tempo-watch-s2-main.jpg',   1),
(4,  '/images/products/tempo-watch-s2-band.jpg',   0),
(5,  '/images/products/fitband-lite-main.jpg',     1),
(6,  '/images/products/snapmini-pro-main.jpg',     1),
(6,  '/images/products/snapmini-pro-back.jpg',     0),
(7,  '/images/products/lensmate-50mm-main.jpg',    1),
(8,  '/images/products/keyflow-75-main.jpg',       1),
(8,  '/images/products/keyflow-75-top.jpg',        0),
(9,  '/images/products/flow-mouse-mx-main.jpg',    1),
(10, '/images/products/desk-mat-xl-main.jpg',      1),
(11, '/images/products/powercore-140w-main.jpg',   1),
(12, '/images/products/magpad-duo-main.jpg',       1),
(13, '/images/products/carry-case-pro-main.jpg',   1),
(14, '/images/products/usbc-cable-3m-main.jpg',    1),
(15, '/images/products/tech-pouch-main.jpg',       1),
(16, '/images/products/sport-band-pack-main.jpg',  1);

-- ── PRODUCT RELATIONS ────────────────────────────────────────
-- Upsell: suggest a more premium alternative
INSERT INTO product_relations (product_id, related_id, type, discount_amount, sort_order) VALUES
-- FitBand Lite → upgrade to Tempo Watch S2
(5,  4,  'upsell', 20.00, 1),
-- AirPods Nano V2 → upgrade to ProSound X3
(2,  1,  'upsell', 15.00, 1),
-- Flow Mouse MX → upgrade to KeyFlow 75% bundle
(9,  8,  'upsell', 10.00, 1),
-- USB-C Cable → upgrade to PowerCore 140W
(14, 11, 'upsell', 8.00,  1);

-- Cross-sell: Frequently Bought Together
INSERT INTO product_relations (product_id, related_id, type, discount_amount, sort_order) VALUES
-- ProSound X3 → AirPods Nano V2, PowerCore, Carry Case
(1, 2,  'crosssell', 0, 1),
(1, 11, 'crosssell', 0, 2),
(1, 13, 'crosssell', 0, 3),
-- Tempo Watch S2 → Sport Band Pack, PowerCore, Tech Pouch
(4, 16, 'crosssell', 0, 1),
(4, 11, 'crosssell', 0, 2),
(4, 15, 'crosssell', 0, 3),
-- SnapMini Pro → LensMate 50mm, Carry Case, USB-C Cable
(6, 7,  'crosssell', 0, 1),
(6, 13, 'crosssell', 0, 2),
(6, 14, 'crosssell', 0, 3),
-- KeyFlow 75% → Flow Mouse MX, Desk Mat XL, PowerCore
(8, 9,  'crosssell', 0, 1),
(8, 10, 'crosssell', 0, 2),
(8, 11, 'crosssell', 0, 3);

-- ── BUNDLES ──────────────────────────────────────────────────
INSERT INTO bundles (name, description, bundle_price) VALUES
('Creator Bundle',   'ProSound X3 + SnapMini Pro + PowerCore 140W. Everything a creator needs.',  649.00),
('Desk Setup Pro',   'KeyFlow 75% + Flow Mouse MX + Desk Mat XL. The ultimate clean workspace.',   279.00),
('Travel Essentials','AirPods Nano V2 + PowerCore 140W + Carry Case Pro. Pack light, stay charged.',239.00),
('Watch Starter Kit','Tempo Watch S2 + Sport Band Pack + Tech Pouch. Day-one ready.',              399.00);

-- ── BUNDLE ITEMS ─────────────────────────────────────────────
INSERT INTO bundle_items (bundle_id, product_id) VALUES
-- Creator Bundle
(1, 1), (1, 6), (1, 11),
-- Desk Setup Pro
(2, 8), (2, 9), (2, 10),
-- Travel Essentials
(3, 2), (3, 11), (3, 13),
-- Watch Starter Kit
(4, 4), (4, 16), (4, 15);

-- ── USERS ───────────────────────────────────────────────────
-- Passwords are bcrypt hashes of 'password123'
INSERT INTO users (email, password_hash, full_name, phone) VALUES
('admin@volta.com',   '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Volta Admin',    NULL),
('alice@example.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Alice Nguyen',   '+84901234567'),
('bob@example.com',   '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Bob Tran',       '+84907654321'),
('carol@example.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Carol Le',       '+84909988776');

-- ── ADDRESSES ───────────────────────────────────────────────
INSERT INTO addresses (user_id, label, street, city, country, is_default) VALUES
(2, 'Home',   '12 Nguyen Hue, District 1',          'Ho Chi Minh City', 'Vietnam', 1),
(2, 'Work',   '88 Le Loi, District 3',               'Ho Chi Minh City', 'Vietnam', 0),
(3, 'Home',   '45 Tran Hung Dao, Hoan Kiem',         'Hanoi',            'Vietnam', 1),
(4, 'Home',   '7 Bach Dang, Hai Chau',               'Da Nang',          'Vietnam', 1);

-- ── ORDERS ──────────────────────────────────────────────────
INSERT INTO orders (user_id, address_id, status, total_price, created_at) VALUES
(2, 1, 'delivered', 378.00, '2025-01-10 09:15:00'),
(2, 1, 'shipped',   649.00, '2025-02-20 14:30:00'),
(3, 3, 'paid',      279.00, '2025-02-25 11:00:00'),
(4, 4, 'pending',   129.00, '2025-03-01 08:45:00');

-- ── ORDER ITEMS ──────────────────────────────────────────────
INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES
-- Order 1: Alice — ProSound X3 + Carry Case
(1, 1, 1, 249.00),
(1, 13, 1, 39.00),
(1, 11, 1, 89.00),
-- Order 2: Alice — Creator Bundle items
(2, 1, 1, 249.00),
(2, 6, 1, 419.00),
-- Order 3: Bob — Desk Setup Pro items
(3, 8, 1, 189.00),
(3, 9, 1,  79.00),
(3, 10, 1,  45.00),
-- Order 4: Carol — AirPods
(4, 2, 1, 129.00);

-- ── CART ITEMS ───────────────────────────────────────────────
INSERT INTO cart_items (user_id, product_id, quantity) VALUES
(3, 4,  1),   -- Bob eyeing Tempo Watch S2
(3, 16, 1),   -- + Sport Band Pack
(4, 1,  1),   -- Carol eyeing ProSound X3
(4, 11, 1);   -- + PowerCore 140W

-- ── DISCOUNTS ────────────────────────────────────────────────
INSERT INTO discounts (code, type, value, min_order, uses_remaining, expires_at) VALUES
('VOLTA10',    'percent', 10.00, 0.00,   NULL, NULL),
('WELCOME20',  'fixed',   20.00, 100.00, 500,  '2025-12-31 23:59:59'),
('BUNDLE15',   'percent', 15.00, 300.00, 200,  '2025-09-30 23:59:59'),
('FLASH50',    'fixed',   50.00, 500.00, 50,   '2025-03-31 23:59:59');