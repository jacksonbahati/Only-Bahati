<?php
declare(strict_types=1);

// Simple router for the school_app backend API
// Usage examples:
//   POST /backend/routes/api.php?route=auth/register
//   POST /backend/routes/api.php?route=auth/login
//   GET  /backend/routes/api.php?route=auth/me
//   POST /backend/routes/api.php?route=auth/logout
//   GET  /backend/routes/api.php?route=posts
//   GET  /backend/routes/api.php?route=posts/123
//   POST /backend/routes/api.php?route=posts
//   PUT  /backend/routes/api.php?route=posts/123
//   DELETE /backend/routes/api.php?route=posts/123

require_once __DIR__ . '/../helpers/utils.php';
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/PostController.php';
require_once __DIR__ . '/../controllers/UserController.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$route = $_GET['route'] ?? '';

// Allow method override via X-HTTP-Method-Override
$headers = getallheaders();
if (isset($headers['X-HTTP-Method-Override'])) {
    $method = strtoupper($headers['X-HTTP-Method-Override']);
}

try {
    switch (true) {
        // Auth routes
        case $method === 'POST' && $route === 'auth/register':
            AuthController::register();
            break;
        case $method === 'POST' && $route === 'auth/login':
            AuthController::login();
            break;
        case $method === 'GET' && $route === 'auth/me':
            AuthController::me();
            break;
        case $method === 'POST' && $route === 'auth/logout':
            AuthController::logout();
            break;

        // Post routes
        case $method === 'GET' && $route === 'posts':
            PostController::list();
            break;
        case $method === 'GET' && preg_match('#^posts/(\d+)$#', $route, $m):
            PostController::get((int)$m[1]);
            break;
        case $method === 'POST' && $route === 'posts':
            PostController::create();
            break;
        case in_array($method, ['PUT', 'PATCH'], true) && preg_match('#^posts/(\d+)$#', $route, $m):
            PostController::update((int)$m[1]);
            break;
        case $method === 'DELETE' && preg_match('#^posts/(\d+)$#', $route, $m):
            PostController::delete((int)$m[1]);
            break;

        // User routes
        case $method === 'GET' && preg_match('#^users/(\d+)$#', $route, $m):
            UserController::profile((int)$m[1]);
            break;
        case in_array($method, ['PUT', 'PATCH'], true) && preg_match('#^users/(\d+)$#', $route, $m):
            UserController::update((int)$m[1]);
            break;

        default:
            json_error('Not Found', 404, ['method' => $method, 'route' => $route]);
    }
} catch (Throwable $e) {
    json_error('Server error', 500, ['message' => $e->getMessage()]);
}
