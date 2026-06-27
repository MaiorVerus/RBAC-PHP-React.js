<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/jwt.php';

$input = getJsonInput();
$username = trim($input['username'] ?? '');
$email = trim($input['email'] ?? '');
$password = trim($input['password'] ?? '');
$role = trim($input['role'] ?? 'user');

if (!$username || !$email || !$password) {
    sendError('Username, email, and password are required', 422);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendError('Invalid email address', 422);
}

try {
    $stmt = $conn->prepare('SELECT id FROM users WHERE email = :email');
    $stmt->execute(['email' => $email]);
    if ($stmt->fetch()) {
        sendError('Email is already registered', 409);
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare('INSERT INTO users (username, email, password, role, created_at) VALUES (:username, :email, :password, :role, NOW())');
    $stmt->execute([
        'username' => $username,
        'email' => $email,
        'password' => $passwordHash,
        'role' => $role,
    ]);

    $userId = $conn->lastInsertId();
    $token = jwt_encode([
        'id' => $userId,
        'username' => $username,
        'email' => $email,
        'role' => $role,
    ]);

    sendResponse(['token' => $token, 'user' => ['id' => $userId, 'username' => $username, 'email' => $email, 'role' => $role]]);
} catch (PDOException $e) {
    sendError('Failed to create user', 500);
}
