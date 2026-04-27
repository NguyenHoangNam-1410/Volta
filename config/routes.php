<?php
// config/routes.php
// All routes are REST API endpoints returning JSON.

$router = new Router();

// ╔═══════════════════════════════════════════════════════════╗
// ║  AUTH                                                     ║
// ╚═══════════════════════════════════════════════════════════╝
$router->post('/api/login',  'AuthController@login');
$router->post('/api/register', 'AuthController@signup');
$router->post('/api/logout', 'AuthController@logout');
$router->get('/api/me',      'AuthController@me');

// ╔═══════════════════════════════════════════════════════════╗
// ║  USERS  (Admin)                                           ║
// ╚═══════════════════════════════════════════════════════════╝
$router->get('/api/users',                       'UserController@index');
$router->get('/^\/api\/users\/(\d+)$/',          'UserController@show');
$router->post('/api/users',                      'UserController@store');
$router->put('/^\/api\/users\/(\d+)$/',          'UserController@update');
$router->delete('/^\/api\/users\/(\d+)$/',       'UserController@destroy');

// ╔═══════════════════════════════════════════════════════════╗
// ║  PRODUCTS  (Admin)                                        ║
// ╚═══════════════════════════════════════════════════════════╝
$router->get('/api/products',                    'ProductController@index');
$router->get('/^\/api\/products\/(\d+)$/',       'ProductController@show');
$router->post('/api/products',                   'ProductController@store');
$router->put('/^\/api\/products\/(\d+)$/',       'ProductController@update');
$router->delete('/^\/api\/products\/(\d+)$/',    'ProductController@destroy');

// Product Images
$router->get('/^\/api\/products\/(\d+)\/images$/',                          'ProductController@images');
$router->post('/^\/api\/products\/(\d+)\/images$/',                         'ProductController@uploadImage');
$router->delete('/^\/api\/products\/(\d+)\/images\/(\d+)$/',               'ProductController@deleteImage');
$router->put('/^\/api\/products\/(\d+)\/images\/(\d+)\/primary$/',         'ProductController@setPrimaryImage');

// Product Relations
$router->get('/^\/api\/products\/(\d+)\/relations$/',                       'ProductController@relations');
$router->post('/^\/api\/products\/(\d+)\/relations$/',                      'ProductController@addRelation');
$router->delete('/^\/api\/products\/(\d+)\/relations\/(\d+)$/',            'ProductController@removeRelation');

// ╔═══════════════════════════════════════════════════════════╗
// ║  CATEGORIES                                               ║
// ╚═══════════════════════════════════════════════════════════╝
$router->get('/api/categories',                       'CategoryController@index');
$router->get('/^\/api\/categories\/(\d+)$/',          'CategoryController@show');
$router->post('/api/categories',                      'CategoryController@store');
$router->put('/^\/api\/categories\/(\d+)$/',          'CategoryController@update');
$router->delete('/^\/api\/categories\/(\d+)$/',       'CategoryController@destroy');

// ╔═══════════════════════════════════════════════════════════╗
// ║  DISCOUNTS  (Admin)                                       ║
// ╚═══════════════════════════════════════════════════════════╝
$router->get('/api/discounts',                        'DiscountController@index');
$router->get('/api/discounts/valid',                  'DiscountController@valid');
$router->get('/^\/api\/discounts\/(\d+)$/',           'DiscountController@show');
$router->post('/api/discounts',                       'DiscountController@store');
$router->put('/^\/api\/discounts\/(\d+)$/',           'DiscountController@update');
$router->delete('/^\/api\/discounts\/(\d+)$/',        'DiscountController@destroy');

// ╔═══════════════════════════════════════════════════════════╗
// ║  ORDERS  (Admin)                                          ║
// ╚═══════════════════════════════════════════════════════════╝
$router->get('/api/admin/orders',                         'OrderController@index');
$router->get('/api/admin/orders/stats',                   'OrderController@stats');
$router->get('/api/admin/price-alerts',                   'PriceAlertController@index');
$router->get('/^\/api\/admin\/orders\/(\d+)$/',           'OrderController@show');
$router->post('/api/admin/orders',                        'OrderController@store');
$router->put('/^\/api\/admin\/orders\/(\d+)$/',           'OrderController@update');
$router->put('/^\/api\/admin\/orders\/(\d+)\/status$/',   'OrderController@updateStatus');
$router->delete('/^\/api\/admin\/orders\/(\d+)$/',        'OrderController@destroy');

// ╔═══════════════════════════════════════════════════════════╗
// ║  BUNDLES                                                  ║
// ╚═══════════════════════════════════════════════════════════╝
$router->get('/api/bundles',                          'BundleController@index');
$router->get('/api/bundles/active',                   'BundleController@active');
$router->get('/^\/api\/bundles\/(\d+)$/',             'BundleController@show');
$router->post('/api/bundles',                         'BundleController@store');
$router->put('/^\/api\/bundles\/(\d+)$/',             'BundleController@update');
$router->delete('/^\/api\/bundles\/(\d+)$/',          'BundleController@destroy');

// Bundle Items
$router->get('/^\/api\/bundles\/(\d+)\/items$/',              'BundleController@items');
$router->post('/^\/api\/bundles\/(\d+)\/items$/',             'BundleController@addItem');
$router->delete('/^\/api\/bundles\/(\d+)\/items\/(\d+)$/',    'BundleController@removeItem');

// ╔═══════════════════════════════════════════════════════════╗
// ║  SHOP  (Public)                                           ║
// ╚═══════════════════════════════════════════════════════════╝
$router->get('/api/shop/products',                    'ShopController@index');
$router->get('/^\/api\/shop\/products\/(\d+)$/',      'ShopController@show');
$router->get('/^\/api\/shop\/products\/slug\/(.+)$/', 'ShopController@showBySlug');
$router->get('/api/shop/categories',                  'ShopController@categories');
$router->get('/api/shop/featured',                    'ShopController@featured');

// ╔═══════════════════════════════════════════════════════════╗
// ║  CART  (Customer)                                         ║
// ╚═══════════════════════════════════════════════════════════╝
$router->get('/api/cart',                             'CustomerCartController@index');
$router->post('/api/cart/items',                      'CustomerCartController@add');
$router->put('/api/cart/items',                       'CustomerCartController@update');
$router->delete('/^\/api\/cart\/items\/(\d+)$/',      'CustomerCartController@remove');
$router->delete('/api/cart',                          'CustomerCartController@clear');

// Checkout
$router->get('/api/cart/checkout',                    'CustomerCartController@checkout');
$router->post('/api/cart/apply-discount',             'CustomerCartController@applyDiscount');
$router->post('/api/cart/place-order',                'CustomerCartController@placeOrder');

// Customer's own orders
$router->get('/api/orders/my',                        'CustomerCartController@myOrders');
$router->get('/^\/api\/orders\/my\/(\d+)$/',          'CustomerCartController@myOrderDetail');

// ╔═══════════════════════════════════════════════════════════╗
// ║  PROFILE  (Authenticated)                                 ║
// ╚═══════════════════════════════════════════════════════════╝
$router->get('/api/profile',                              'ProfileController@index');
$router->put('/api/profile',                              'ProfileController@update');
$router->get('/api/profile/addresses',                    'ProfileController@addresses');
$router->post('/api/profile/addresses',                   'ProfileController@addAddress');
$router->put('/^\/api\/profile\/addresses\/(\d+)$/',      'ProfileController@updateAddress');
$router->delete('/^\/api\/profile\/addresses\/(\d+)$/',   'ProfileController@deleteAddress');

