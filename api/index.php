<?php

// ============================================================
// 1. BOOTSTRAP
// ============================================================
$autoloadPaths = [
    __DIR__ . '/vendor/autoload.php',
    __DIR__ . '/../vendor/autoload.php',
    dirname(__DIR__) . '/vendor/autoload.php',
];

$autoloadFile = null;
foreach ($autoloadPaths as $path) {
    if (file_exists($path)) {
        $autoloadFile = $path;
        break;
    }
}

if ($autoloadFile === null) {
    header('Content-Type: application/json', true, 500);
    echo json_encode(['error' => 'Composer autoload file not found. Run composer install and verify the API directory structure.']);
    exit();
}

require_once $autoloadFile;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// [A] Load your own classes — order matters
// Database first, then helpers, then controllers that USE them
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/helpers/JwtHelper.php';
require_once __DIR__ . '/helpers/ResponseHelper.php';
require_once __DIR__ . '/controllers/authController.php';

// ============================================================
// 2. CORS
// ============================================================
header('Content-Type: application/json');

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowedOrigins = [
    'http://localhost:5173',
    'http://127.0.0.1:5173',
    'http://localhost:3000',
    'http://127.0.0.1:3000',
];

if ($origin !== '' && in_array($origin, $allowedOrigins, true)) {
    header("Access-Control-Allow-Origin: $origin");
    header('Vary: Origin');
} else {
    header('Access-Control-Allow-Origin: *');
}

header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ============================================================
// 3. PARSE REQUEST
// ============================================================
$method = $_SERVER['REQUEST_METHOD'];
$uri    = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/');
$uri    = str_replace(['/CRUD-RBAC', '/CRUD RBAC'], '', $uri);
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
