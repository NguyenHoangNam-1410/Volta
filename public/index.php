<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// Initialize session activity timestamp
if (!isset($_SESSION['LAST_ACTIVITY'])) {
    $_SESSION['LAST_ACTIVITY'] = time();
}

// Load Router class
require_once '../app/helpers/Router.php';

// Load all routes
require_once '../config/routes.php';

// Dispatch the request
$router->dispatch();
?>