<?php

// ============================================================
// 1. BOOTSTRAP
// ============================================================
require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// [A] Load your own classes — order matters
// Database first, then helpers, then controllers that USE them
require __DIR__ . '/config/Database.php';
require __DIR__ . '/helpers/JwtHelper.php';
require __DIR__ . '/helpers/ResponseHelper.php';
require __DIR__ . '/controllers/authController.php';

// ============================================================
// 2. CORS
// ============================================================
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost:5173');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ============================================================
// 3. PARSE REQUEST
// ============================================================
$method = $_SERVER['REQUEST_METHOD'];
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$body   = json_decode(file_get_contents('php://input'), true);

// ============================================================
// 4. ROUTE
// ============================================================
// [B] Routes describe resources, NOT folder paths
if ($method === 'POST' && $uri === '/api/auth/register') {
    $controller = new AuthController();
    $controller->register($body);
} elseif ($method === 'POST' && $uri === '/api/auth/login') {
    $controller = new AuthController();
    $controller->login($body);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Route not found']);
}
?>