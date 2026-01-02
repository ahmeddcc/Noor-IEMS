<?php
// index.php - Main Router

// 1. App Initialization (Autoloading, Session, Error Handling)
require_once 'app/init.php';

use App\Core\Router;

// 2. Initialize Router
$router = new Router();

// 3. Load Routes Definition
require_once 'app/Config/routes.php';

// 4. Dispatch Request
$page = $_GET['page'] ?? 'dashboard';
$action = $_GET['action'] ?? 'index';

try {
    $router->dispatch($page, $action);
} catch (Exception $e) {
    // Fallback error display
    error_log("System Error: " . $e->getMessage());
    echo '<div style="color:red; text-align:center; padding:50px; font-family:sans-serif;">';
    echo '<h1>System Error</h1>';
    echo '<p>An unexpected error occurred. Please try again later.</p>';
    echo '</div>';
}
