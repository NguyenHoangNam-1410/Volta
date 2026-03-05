<?php
// config/routes.php

$router = new Router();

// ============= AUTH ROUTES =============
$router->get('/login', function() {
    include '../app/views/auth/login.php';
});
$router->post('/login', 'AuthController@handleLogin');

$router->get('/signup', function() {
    include '../app/views/auth/signup.php';
});
$router->post('/signup', 'AuthController@handleSignup');

$router->get('/logout', 'AuthController@handleLogout');

// ============= PROFILE ROUTES =============
$router->get('/profile', 'ProfileController@index');
$router->post('/profile', 'ProfileController@update');

// ============= USER ROUTES (ADMIN) =============
$router->get('/users', 'UserController@index');
$router->get('/users/create', 'UserController@create');
$router->post('/users/store', 'UserController@store');
$router->get('/^\/users\/edit\/(\d+)$/', 'UserController@edit');
$router->post('/^\/users\/update\/(\d+)$/', 'UserController@update');
$router->get('/^\/users\/delete\/(\d+)$/', 'UserController@destroy');

// ============= PRODUCT ROUTES (ADMIN) =============
$router->get('/products', 'ProductController@index');
$router->get('/products/create', 'ProductController@create');
$router->post('/products/store', 'ProductController@store');
$router->get('/^\/products\/edit\/(\d+)$/', 'ProductController@edit');
$router->post('/^\/products\/update\/(\d+)$/', 'ProductController@update');
$router->get('/^\/products\/delete\/(\d+)$/', 'ProductController@destroy');

// Product Images
$router->get('/^\/products\/(\d+)\/images$/', 'ProductController@manageImages');
$router->post('/^\/products\/(\d+)\/images\/upload$/', 'ProductController@uploadImage');
$router->get('/^\/products\/(\d+)\/images\/(\d+)\/delete$/', 'ProductController@deleteImage');
$router->get('/^\/products\/(\d+)\/images\/(\d+)\/set-primary$/', 'ProductController@setPrimaryImage');

// ============= DISCOUNT ROUTES (ADMIN) =============
$router->get('/discounts', 'DiscountController@index');
$router->get('/discounts/create', 'DiscountController@create');
$router->post('/discounts/store', 'DiscountController@store');
$router->get('/^\/discounts\/edit\/(\d+)$/', 'DiscountController@edit');
$router->post('/^\/discounts\/update\/(\d+)$/', 'DiscountController@update');
$router->get('/^\/discounts\/delete\/(\d+)$/', 'DiscountController@destroy');

// ============= CART/ORDER ROUTES (ADMIN) =============
$router->get('/carts', 'CartController@index');
$router->get('/^\/carts\/view\/(\d+)$/', 'CartController@view');
$router->get('/^\/carts\/edit\/(\d+)$/', 'CartController@edit');
$router->post('/^\/carts\/update\/(\d+)$/', 'CartController@update');

// ============= PUBLIC ROUTES =============
$router->get('/', function() { include '../app/views/public/home.php'; });
$router->get('/home', function() { include '../app/views/public/home.php'; });
$router->get('/401', function() { include '../app/views/public/401.php'; });
$router->get('/shop', 'ShopController@index');
$router->get('/^\/shop\/product\/(\d+)$/', 'ShopController@productDetail');
$router->get('/warranty-policy', function() { include '../app/views/public/warranty_policy.php'; });
$router->get('/return-policy', function() { include '../app/views/public/return_policy.php'; });
$router->get('/contact', function() { include '../app/views/public/contact.php'; });
$router->get('/faq', function() { include '../app/views/public/faq.php'; });
$router->get('/maintain', function() { include '../app/views/public/maintain.php'; });

// ============= CUSTOMER CART ROUTES =============
$router->get('/cart', 'CustomerCartController@index');
$router->post('/cart/add', 'CustomerCartController@add');
$router->post('/cart/update', 'CustomerCartController@update');
$router->post('/cart/remove', 'CustomerCartController@remove');
$router->get('/checkout', 'CustomerCartController@checkout');
$router->post('/cart/apply-discount', 'CustomerCartController@applyDiscount');
$router->post('/cart/place-order', 'CustomerCartController@placeOrder');
$router->get('/^\/order-success\/(\d+)$/', 'CustomerCartController@orderSuccess');
