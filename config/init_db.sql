-- ── CATEGORIES ──────────────────────────────────────────────
INSERT INTO categories (name, slug) VALUES
('Audio',       'audio'),
('Wearables',   'wearables'),
('Imaging',     'imaging'),
('Peripherals', 'peripherals'),
('Power',       'power'),
('Accessories', 'accessories');

-- ── product ────────────────────────────────────────────────
INSERT INTO products (category_id, name, slug, description, price, stock, badge) VALUES
-- Audio
(1, 'ProSound X3',     'prosound-x3',     'Active noise cancellation, 40hr battery, foldable design.',            249.00, 80,  'hot'),
(1, 'AirPods Nano V2', 'airpods-nano-v2', 'Hi-fi spatial audio, IPX5 waterproof, 36hr total battery life.',       129.00, 120, 'none'),
(1, 'BassBar Mini',    'bassbar-mini',    'Compact Bluetooth speaker, 360° sound, 24hr playback, dustproof.',      79.00, 60,  'new'),
(1, 'SonicPulse Studio','sonicpulse-studio','Professional Bluetooth studio monitor, 360W output, balanced audio.',  599.00, 15,  'new'),
(1, 'TuneLink Air',    'tunelink-air',    'Lightweight true wireless earbuds, 48hr case battery, touch controls.', 149.00, 95,  'none'),

-- Wearables
(2, 'Tempo Watch S2',  'tempo-watch-s2',  'Health sensors, GPS, 7-day battery. Titanium case.',                   349.00, 45,  'new'),
(2, 'FitBand Lite',    'fitband-lite',    'Sleep tracking, heart rate monitor, 14-day battery, ultra-light.',      59.00, 150, 'none'),
(2, 'CircleRing Pro',  'circlering-pro',  'Smart ring: sleep analysis, stress tracking, 7-day battery.',          299.00, 28,  'sale'),
(2, 'VitalStrap V3',   'vitalstrap-v3',   'Fitness band with blood oxygen monitor, waterproof to 100m.',          129.00, 110, 'none'),

-- Imaging
(3, 'SnapMini Pro',    'snapmini-pro',    '48MP, 4K video, pocket-size mirrorless camera.',                       419.00, 30,  'sale'),
(3, 'LensMate 50mm',   'lensmate-50mm',   'f/1.8 prime lens, compatible with SnapMini Pro, razor-sharp portraits.',149.00, 25,  'none'),
(3, 'FocusGrip Gimbal', 'focusgip-gimbal', 'Stabilized three-axis gimbal for video recording, 8hr battery.',       189.00, 35,  'new'),
(3, 'LuxLight Panel',  'luxlight-panel',  'RGB LED light panel for creators, 300W output, adjustable spectrum.',   259.00, 18,  'none'),

-- Peripherals
(4, 'KeyFlow 75%',     'keyflow-75',      'Wireless mechanical, gasket mount, PBT keycaps.',                      189.00, 55,  'hot'),
(4, 'Flow Mouse MX',   'flow-mouse-mx',   'Ergonomic wireless mouse, 4000 DPI, silent clicks, 70hr battery.',      79.00, 90,  'none'),
(4, 'Desk Mat XL',     'desk-mat-xl',     'Non-slip 900×400mm desk mat, stitched edges, water-resistant.',          45.00, 200, 'none'),
(4, 'MonitorArm Elite','monitor-arm-elite','Adjustable dual monitor arm, VESA mount, cable management.',           149.00, 42,  'none'),
(4, 'TypePro Macro',   'typepro-macro',   'Mechanical macro pad with RGB backlight, 12 programmable keys.',         89.00, 65,  'none'),

-- Power
(5, 'PowerCore 140W',  'powercore-140w',  'Charge 4 devices simultaneously. GaN tech, ultra-slim.',                89.00, 100, 'new'),
(5, 'MagPad Duo',      'magpad-duo',      'Dual wireless charger, 15W fast charge, supports MagSafe.',             49.00, 75,  'none'),
(5, 'PowerBank Ultra', 'powerbank-ultra', 'Portable 25000mAh, 140W output, compact form factor, Type-C display.',  149.00, 52,  'sale'),
(5, 'SolarCharge Kit', 'solarcharge-kit', 'Folding solar panel charger, 100W output, weatherproof.',              199.00, 22,  'new'),

-- Accessories
(6, 'Carry Case Pro',  'carry-case-pro',  'Hard-shell carry case, fits headphones + cables, TSA-friendly.',        39.00, 180, 'none'),
(6, 'USB-C Cable 3m',  'usbc-cable-3m',   '240W USB-C braided cable, 3 meters, supports 40Gbps data.',             19.00, 300, 'none'),
(6, 'Tech Pouch',      'tech-pouch',      'Organizer pouch for cables, adapters, and small gadgets.',              39.00, 160, 'none'),
(6, 'Sport Band Pack', 'sport-band-pack', 'Set of 3 silicone bands for Tempo Watch S2. Black, Blue, Sand.',        29.00, 110, 'none'),
(6, 'Desk Organizer',  'desk-organizer',  'Multi-compartment desk organizer for tech accessories and supplies.',    49.00, 88,  'none'),
(6, 'Phone Stand Flex', 'phone-stand-flex','Adjustable phone stand, thick aluminum, non-slip rubber pads.',         29.00, 140, 'none'),
(6, 'HDMI 2.1 Cable',  'hdmi-21-cable',   '8K HDMI 2.1 certified cable, 3 meters, gold-plated connectors.',        24.00, 175, 'none');

-- ── PRODUCT IMAGES ──────────────────────────────────────────
INSERT INTO product_images (product_id, url, is_primary) VALUES
(1,  'public/image/product/1/prosound-x3-main.jpg',     1),
(1,  'public/image/product/1/prosound-x3-side.jpg',      0),
(2,  'public/image/product/2/airpods-nano-v2-main.jpg',  1),
(3,  'public/image/product/3/bassbar-mini-main.jpg',     1),
(4,  'public/image/product/4/sonicpulse-studio-main.jpg', 1),
(5,  'public/image/product/5/tunelink-air-main.jpg',     1),
(5,  'public/image/product/5/tunelink-air-case.jpg',     0),
(6,  'public/image/product/6/tempo-watch-s2-main.jpg',   1),
(7,  'public/image/product/7/fitband-lite-main.jpg',     1),
(8,  'public/image/product/8/circlering-pro-main.jpg',   1),
(8,  'public/image/product/8/circlering-pro-worn.jpg',   0),
(9,  'public/image/product/9/vitalstrap-v3-main.jpg',    1),
(9,  'public/image/product/9/vitalstrap-v3-detail.jpg',  0),
(10, 'public/image/product/10/snapmini-pro-main.jpg',     1),
(11, 'public/image/product/11/lensmate-50mm-main.jpg',    1),
(12, 'public/image/product/12/focusgip-gimbal-main.jpg',  1),
(12, 'public/image/product/12/focusgip-gimbal-gimbal.jpg',0),
(13, 'public/image/product/13/luxlight-panel-main.jpg',   1),
(13, 'public/image/product/13/luxlight-panel-on.jpg',     0),
(14, 'public/image/product/14/keyflow-75-main.jpg',       1),
(15, 'public/image/product/15/flow-mouse-mx-main.jpg',    1),
(16, 'public/image/product/16/desk-mat-xl-main.jpg',      1),
(16, 'public/image/product/16/desk-mat-xl-setup.jpg',     0),
(17, 'public/image/product/17/monitor-arm-elite-main.jpg', 1),
(17, 'public/image/product/17/monitor-arm-elite-mounted.jpg', 0),
(18, 'public/image/product/18/typepro-macro-main.jpg',    1),
(18, 'public/image/product/18/typepro-macro-lit.jpg',     0),
(19, 'public/image/product/19/powercore-140w-main.jpg',   1),
(20, 'public/image/product/20/magpad-duo-main.jpg',       1),
(21, 'public/image/product/21/powerbank-ultra-main.jpg',  1),
(21, 'public/image/product/21/powerbank-ultra-charging.jpg', 0),
(22, 'public/image/product/22/solarcharge-kit-main.jpg',  1),
(22, 'public/image/product/22/solarcharge-kit-open.jpg',  0),
(23, 'public/image/product/23/carry-case-pro-main.jpg',   1),
(24, 'public/image/product/24/usbc-cable-3m-main.webp',    1),
(25, 'public/image/product/25/tech-pouch-main.jpg',       1),
(26, 'public/image/product/26/sport-band-pack-main.jpg',  1),
(27, 'public/image/product/27/desk-organizer-main.jpg',   1),
(27, 'public/image/product/27/desk-organizer-filled.jpg', 0),
(28, 'public/image/product/28/phone-stand-flex-main.jpg', 1),
(28, 'public/image/product/28/phone-stand-flex-phone.jpg', 0),
(29, 'public/image/product/29/hdmi-21-cable-main.jpg',    1),
(29, 'public/image/product/29/hdmi-21-cable-coil.jpg',    0);

-- ── PRODUCT RELATIONS ────────────────────────────────────────
-- Upsell: suggest a more premium alternative
INSERT INTO product_relations (product_id, related_id, type, discount_amount, sort_order) VALUES
-- FitBand Lite → upgrade to Tempo Watch S2
(7,  6,  'upsell', 20.00, 1),
-- AirPods Nano V2 → upgrade to ProSound X3
(2,  1,  'upsell', 15.00, 1),
-- Flow Mouse MX → upgrade to KeyFlow 75%
(15,  14,  'upsell', 10.00, 1),
-- USB-C Cable → upgrade to PowerCore 140W
(24, 19, 'upsell', 8.00,  1),
-- BassBar Mini → upgrade to SonicPulse Studio
(3,  4,  'upsell', 25.00, 1),
-- TuneLink Air → upgrade to ProSound X3
(5,  1,  'upsell', 20.00, 1),
-- CircleRing Pro → upgrade to Tempo Watch S2
(8,  6,  'upsell', 15.00, 1),
-- VitalStrap V3 → upgrade to Tempo Watch S2
(9,  6,  'upsell', 25.00, 1),
-- LensMate 50mm → upgrade to SnapMini Pro
(11, 10, 'upsell', 30.00, 1),
-- MagPad Duo → upgrade to PowerCore 140W
(20, 19, 'upsell', 12.00, 1);

-- Cross-sell: Frequently Bought Together
INSERT INTO product_relations (product_id, related_id, type, discount_amount, sort_order) VALUES
-- ProSound X3 → AirPods, PowerCore, Carry Case
(1, 2,  'crosssell', 0, 1),
(1, 19, 'crosssell', 0, 2),
(1, 23, 'crosssell', 0, 3),
(1, 25, 'crosssell', 0, 4),
-- Tempo Watch S2 → Sport Band Pack, PowerCore, Tech Pouch
(6, 26, 'crosssell', 0, 1),
(6, 19, 'crosssell', 0, 2),
(6, 25, 'crosssell', 0, 3),
(6, 28, 'crosssell', 0, 4),
-- SnapMini Pro → LensMate, Gimbal, Light, Carry Case
(10, 11, 'crosssell', 0, 1),
(10, 12, 'crosssell', 0, 2),
(10, 13, 'crosssell', 0, 3),
(10, 23, 'crosssell', 0, 4),
-- KeyFlow 75% → Flow Mouse MX, Desk Mat XL, Monitor Arm
(14, 15, 'crosssell', 0, 1),
(14, 16, 'crosssell', 0, 2),
(14, 17, 'crosssell', 0, 3),
(14, 28, 'crosssell', 0, 4),
-- PowerCore 140W → USB-C Cable, MagPad Duo, PowerBank
(19, 24, 'crosssell', 0, 1),
(19, 20, 'crosssell', 0, 2),
(19, 21, 'crosssell', 0, 3),
-- Flow Mouse MX → Desk Mat, Monitor Arm, TypePro Macro
(15, 16, 'crosssell', 0, 1),
(15, 17, 'crosssell', 0, 2),
(15, 18, 'crosssell', 0, 3),
-- Carry Case Pro → USB-C Cable, Tech Pouch, Phone Stand
(23, 24, 'crosssell', 0, 1),
(23, 25, 'crosssell', 0, 2),
(23, 28, 'crosssell', 0, 3),
-- SonicPulse Studio → PowerCore, USB-C Cable, HDMI Cable
(4, 19, 'crosssell', 0, 1),
(4, 24, 'crosssell', 0, 2),
(4, 29, 'crosssell', 0, 3),
-- CircleRing Pro → Sport Band Pack, Tech Pouch, PowerBank
(8, 26, 'crosssell', 0, 1),
(8, 25, 'crosssell', 0, 2),
(8, 21, 'crosssell', 0, 3),
-- FocusGrip Gimbal → LuxLight Panel, SnapMini Pro, PowerBank
(12, 13, 'crosssell', 0, 1),
(12, 10, 'crosssell', 0, 2),
(12, 21, 'crosssell', 0, 3),
-- Monitor Arm Elite → KeyFlow, Flow Mouse, Desk Mat
(17, 14, 'crosssell', 0, 1),
(17, 15, 'crosssell', 0, 2),
(17, 16, 'crosssell', 0, 3);

-- ── BUNDLES ──────────────────────────────────────────────────
INSERT INTO bundles (name, description, bundle_price) VALUES
('Creator Bundle',      'ProSound X3 + SnapMini Pro + PowerCore 140W. Everything a creator needs.', 649.00),
('Desk Setup Pro',      'KeyFlow 75% + Flow Mouse MX + Desk Mat XL. The ultimate clean workspace.',  279.00),
('Travel Essentials',   'AirPods Nano V2 + PowerCore 140W + Carry Case Pro. Pack light, stay charged.', 239.00),
('Watch Starter Kit',   'Tempo Watch S2 + Sport Band Pack + Tech Pouch. Day-one ready.',              399.00),
('Audio Enthusiast',    'ProSound X3 + SonicPulse Studio + PowerBank Ultra. Immersive sound setup.',   798.00),
('Fitness Pro Pack',    'Tempo Watch S2 + VitalStrap V3 + Sport Band Pack. Track everything.',        438.00),
('Creator Studio Mini', 'SnapMini Pro + FocusGrip Gimbal + LuxLight Panel. Video production ready.',   869.00),
('Cable & Power Hub',   'PowerCore 140W + MagPad Duo + USB-C Cable 3m + HDMI 2.1 Cable. Complete power management.', 181.00),
('Home Office Setup',   'MonitorArm Elite + KeyFlow 75% + Desk Mat XL + Phone Stand Flex. Full workstation.', 428.00),
('Tech Lover Ultimate', 'CircleRing Pro + PowerBank Ultra + Carry Case Pro + USB-C Cable 3m + Tech Pouch. Daily companion kit.', 519.00);

-- ── BUNDLE ITEMS ─────────────────────────────────────────────
INSERT INTO bundle_items (bundle_id, product_id) VALUES
-- Bundle 1: Creator Bundle
(1, 1), (1, 10), (1, 19),
-- Bundle 2: Desk Setup Pro
(2, 14), (2, 15), (2, 16),
-- Bundle 3: Travel Essentials
(3, 2), (3, 19), (3, 23),
-- Bundle 4: Watch Starter Kit
(4, 6), (4, 26), (4, 25),
-- Bundle 5: Audio Enthusiast
(5, 1), (5, 4), (5, 21),
-- Bundle 6: Fitness Pro Pack
(6, 6), (6, 9), (6, 26),
-- Bundle 7: Creator Studio Mini
(7, 10), (7, 12), (7, 13),
-- Bundle 8: Cable & Power Hub
(8, 19), (8, 20), (8, 24), (8, 29),
-- Bundle 9: Home Office Setup
(9, 17), (9, 14), (9, 16), (9, 28),
-- Bundle 10: Tech Lover Ultimate
(10, 8), (10, 21), (10, 23), (10, 24), (10, 25);

-- ── USERS ───────────────────────────────────────────────────
-- Passwords are bcrypt hashes of 'password123'
INSERT INTO users (email, password_hash, full_name, phone) VALUES
('admin@volta.com',   '$2y$10$.ztJ7lvF98dB9NkAQAqAhu2v0rYz7P0Ke7wVHe5roI1jCrlctwYNC', 'Volta Admin',    NULL),
('alice@example.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Alice Nguyen',   '+84901234567'),
('bob@example.com',   '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Bob Tran',       '+84907654321'),
('carol@example.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Carol Le',       '+84909988776'),
('david@example.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'David Pham',     '+84912345678'),
('emma@example.com',  '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Emma Tran',      '+84918765432'),
('frank@example.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Frank Minh',     '+84924681357'),
('grace@example.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Grace Kim',      '+84913579246'),
('henry@example.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Henry Ngo',      '+84935792468');
    
-- ── ADDRESSES ───────────────────────────────────────────────
INSERT INTO addresses (user_id, label, street, city, country, is_default) VALUES
(2, 'Home',   '12 Nguyen Hue, District 1',          'Ho Chi Minh City', 'Vietnam', 1),
(2, 'Work',   '88 Le Loi, District 3',               'Ho Chi Minh City', 'Vietnam', 0),
(3, 'Home',   '45 Tran Hung Dao, Hoan Kiem',         'Hanoi',            'Vietnam', 1),
(4, 'Home',   '7 Bach Dang, Hai Chau',               'Da Nang',          'Vietnam', 1),
(5, 'Home',   '123 Ly Chinh Thang, District 3',      'Ho Chi Minh City', 'Vietnam', 1),
(5, 'Office', '456 Nguyen Van Linh, District 7',     'Ho Chi Minh City', 'Vietnam', 0),
(6, 'Home',   '789 Hoang Dieu, Ba Dinh',             'Hanoi',            'Vietnam', 1),
(7, 'Home',   '321 Duong Vo Van Dung, Binh Thanh',   'Ho Chi Minh City', 'Vietnam', 1),
(7, 'Studio', '654 Nguyen Huu Canh, Binh Thanh',     'Ho Chi Minh City', 'Vietnam', 0),
(8, 'Home',   '987 Pasteur, District 1',             'Ho Chi Minh City', 'Vietnam', 1),
(9, 'Home',   '135 Trang Tien, Hoan Kiem',          'Hanoi',            'Vietnam', 1);

-- ── ORDERS ──────────────────────────────────────────────────
INSERT INTO orders (user_id, address_id, status, total_price, created_at) VALUES
(2, 1, 'delivered', 378.00, '2025-01-10 09:15:00'),
(2, 1, 'shipped',   649.00, '2025-02-20 14:30:00'),
(3, 3, 'paid',      279.00, '2025-02-25 11:00:00'),
(4, 4, 'pending',   129.00, '2025-03-01 08:45:00'),
(5, 5, 'delivered', 598.00, '2025-01-20 10:30:00'),
(6, 7, 'delivered', 249.00, '2025-01-25 14:15:00'),
(7, 8, 'shipped',   799.00, '2025-02-28 09:20:00'),
(8, 10, 'delivered', 459.00, '2025-02-05 11:45:00'),
(9, 11, 'paid',     349.00, '2025-03-05 15:30:00'),
(2, 2, 'delivered', 438.00, '2025-02-10 13:00:00'),
(3, 3, 'shipped',   199.00, '2025-03-02 16:20:00'),
(5, 6, 'delivered', 269.00, '2026-02-14 10:00:00'),
(6, 7, 'delivered', 579.00, '2026-02-18 12:30:00'),
(7, 9, 'pending',   849.00, '2026-03-10 08:15:00'),
(8, 10, 'paid',     329.00, '2026-02-22 14:45:00');

-- ── ORDER ITEMS ──────────────────────────────────────────────
INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES
-- Order 1: Alice — ProSound X3 + USB-C Cable + PowerCore
(1, 1, 1, 249.00),
(1, 24, 1, 19.00),
(1, 19, 1, 89.00),
-- Order 2: Alice — Creator Bundle items
(2, 1, 1, 249.00),
(2, 10, 1, 419.00),
-- Order 3: Bob — Desk Setup Pro items
(3, 14, 1, 189.00),
(3, 15, 1, 79.00),
(3, 16, 1, 45.00),
-- Order 4: Carol — AirPods
(4, 2, 1, 129.00),
-- Order 5: David — Premium audio setup
(5, 4, 1, 599.00),
-- Order 6: Emma — Wearable tech
(6, 6, 1, 349.00),
-- Order 7: Frank — Creator kit
(7, 10, 1, 419.00),
(7, 12, 1, 189.00),
(7, 13, 1, 259.00),
-- Order 8: Grace — Watch & band
(8, 6, 1, 349.00),
(8, 26, 1, 29.00),
(8, 25, 1, 39.00),
-- Order 9: Henry — Power storage
(9, 21, 1, 149.00),
(9, 20, 1, 49.00),
(9, 17, 1, 149.00),
-- Order 10: Alice — Additional items
(10, 5, 1, 149.00),
(10, 28, 1, 29.00),
(10, 27, 1, 49.00),
(10, 29, 1, 24.00),
-- Order 11: Bob — Accessories
(11, 18, 1, 89.00),
(11, 24, 1, 19.00),
(11, 23, 1, 39.00),
-- Order 12: David — Gimbal and light
(12, 12, 1, 189.00),
(12, 13, 1, 89.00),
-- Order 13: Emma — Charging solutions
(13, 22, 1, 199.00),
(13, 21, 1, 149.00),
(13, 20, 1, 49.00),
-- Order 14: Frank — Expensive setup
(14, 11, 2, 299.00),
(14, 17, 1, 149.00),
(14, 18, 1, 89.00),
(14, 16, 1, 45.00),
-- Order 15: Grace — Tech essentials
(15, 8, 1, 299.00),
(15, 9, 1, 129.00),
(15, 26, 1, 29.00);

-- ── CART ITEMS ───────────────────────────────────────────────
INSERT INTO cart_items (user_id, product_id, quantity) VALUES
(3, 6,  1),       -- Bob eyeing Tempo Watch S2
(3, 26, 1),       -- + Sport Band Pack
(4, 1,  1),       -- Carol eyeing ProSound X3
(4, 19, 1),       -- + PowerCore 140W
(5, 4,  1),       -- David eyeing SonicPulse Studio
(5, 19, 1),       -- + PowerCore
(5, 24, 2),       -- + USB-C Cables
(6, 14, 1),       -- Emma eyeing KeyFlow 75%
(6, 15, 1),       -- + Flow Mouse MX
(6, 16, 1),       -- + Desk Mat
(7, 10, 1),       -- Frank eyeing SnapMini Pro
(7, 12, 1),       -- + FocusGrip Gimbal
(8, 6,  1),       -- Grace eyeing Tempo Watch
(8, 8,  1),       -- + CircleRing Pro
(9, 21, 1),       -- Henry eyeing PowerBank Ultra
(9, 20, 1),       -- + MagPad Duo
(9, 19, 1);

-- ── DISCOUNTS ────────────────────────────────────────────────
INSERT INTO discounts (code, type, value, min_order, uses_remaining, expires_at) VALUES
('VOLTA10',      'percent', 10.00, 0.00,    NULL,  NULL),
('WELCOME20',    'fixed',   20.00, 100.00,  500,   '2025-12-31 23:59:59'),
('BUNDLE15',     'percent', 15.00, 300.00,  200,   '2025-09-30 23:59:59'),
('FLASH50',      'fixed',   50.00, 500.00,  50,    '2025-03-31 23:59:59'),
('TECH2026',     'percent', 12.00, 0.00,    NULL,  '2026-12-31 23:59:59'),
('NEWUSER25',    'fixed',   25.00, 150.00,  1000,  '2026-06-30 23:59:59'),
('AUDIO20',      'percent', 20.00, 250.00,  100,   '2026-04-30 23:59:59'),
('SUMMER15',     'percent', 15.00, 0.00,    300,   '2026-08-31 23:59:59'),
('VIP30',        'percent', 30.00, 500.00,  50,    '2026-12-31 23:59:59'),
('CLEARANCE40',  'percent', 40.00, 0.00,    200,   '2026-03-31 23:59:59'),
('EARLYBIRD10',  'fixed',   10.00, 50.00,   999,   '2026-04-30 23:59:59'),
('POWER25',      'percent', 25.00, 200.00,  150,   '2026-05-31 23:59:59');