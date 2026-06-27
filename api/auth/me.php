<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/jwt.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$token = getBearerToken();
if (!$token) {
    sendError('Authorization token required', 401);
}

$payload = jwt_decode($token);
if (!$payload) {
    sendError('Invalid or expired token', 401);
}

sendResponse([
    'user' => [
        'id' => $payload['id'],
        'username' => $payload['username'],
        'email' => $payload['email'],
        'role' => $payload['role'],
    ],
]);
