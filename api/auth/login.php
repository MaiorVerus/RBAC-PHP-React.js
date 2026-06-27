<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/jwt.php';

$input = getJsonInput();
$email = trim($input['email'] ?? '');
$password = trim($input['password'] ?? '');

if (!$email || !$password) {
    sendError('Email and password are required', 422);
}

try {
    $stmt = $conn->prepare('SELECT id, username, email, password, role FROM users WHERE email = :email');
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($password, $user['password'])) {
        sendError('Invalid email or password', 401);
    }

    $token = jwt_encode([
        'id' => $user['id'],
        'username' => $user['username'],
        'email' => $user['email'],
        'role' => $user['role'],
    ]);

    sendResponse(['token' => $token, 'user' => ['id' => $user['id'], 'username' => $user['username'], 'email' => $user['email'], 'role' => $user['role']]]);
} catch (PDOException $e) {
    sendError('Login failed', 500);
}
