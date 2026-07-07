<?php



require_once __DIR__ . "/../config/Database.php";
require_once __DIR__ . "/../helpers/JwtHelper.php";

class AuthController
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    public function register(?array $body): void
    {
        $body = is_array($body) ? $body : [];

        if (empty($body['name']) && empty($body['username'])) {
            http_response_code(422);
            echo json_encode(['error' => "Field 'name' is required"]);
            return;
        }

        if (empty($body['email'])) {
            http_response_code(422);
            echo json_encode(['error' => "Field 'email' is required"]);
            return;
        }

        if (empty($body['password'])) {
            http_response_code(422);
            echo json_encode(['error' => "Field 'password' is required"]);
            return;
        }

        $name     = trim((string) ($body['name'] ?? $body['username'] ?? ''));
        $email    = trim(strtolower((string) ($body['email'] ?? '')));
        $password = (string) $body['password'];

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(422);
            echo json_encode(['error' => 'Invalid email format']);
            return;
        }

        if (strlen($password) < 8) {
            http_response_code(422);
            echo json_encode(['error' => 'Password must be at least 8 characters']);
            return;
        }

        if ($this->emailExists($email)) {
            http_response_code(409);
            echo json_encode(['error' => 'Email already in use']);
            return;
        }

        $hashed = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $this->pdo->prepare(
            "INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, :role)"
        );
        $stmt->execute([
            'name' => $name,
            'email' => $email,
            'password' => $hashed,
            'role' => 'user'
        ]);

        $userId = (int) $this->pdo->lastInsertId();
        $userStmt = $this->pdo->prepare("SELECT id, name, email, role FROM users WHERE id = :id");
        $userStmt->execute(['id' => $userId]);
        $createdUser = $userStmt->fetch(PDO::FETCH_ASSOC);

        $jwtHelper = new JwtHelper();
        $token = $jwtHelper->generateToken([
            'id' => $createdUser['id'] ?? $userId,
            'email' => $createdUser['email'] ?? $email,
            'role' => $createdUser['role'] ?? 'user',
        ]);

        http_response_code(201);
        echo json_encode([
            'message' => 'Account created successfully',
            'token' => $token,
            'user' => [
                'id' => $createdUser['id'] ?? $userId,
                'name' => $createdUser['name'] ?? $name,
                'email' => $createdUser['email'] ?? $email,
                'role' => $createdUser['role'] ?? 'user',
            ],
        ]);
    }

    public function login(?array $body): void
    {
        if (empty($body['email']) || empty($body['password'])) {
            http_response_code(422);
            echo json_encode(['error' => 'Email and password are required']);
            return;
        }

        $email    = trim(strtolower($body['email']));
        $password = $body['password'];

        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid credentials ❌']);
            return;
        }

        $jwtHelper = new JwtHelper();
        $token = $jwtHelper->generateToken([
            'id'    => $user['id'],
            'email' => $user['email'],
            'role'  => $user['role'] ?? 'user',
        ]);

        http_response_code(200);
        echo json_encode([
            'message' => 'Login successful ✔',
            'token'   => $token,
            'user'    => [
                'id'    => $user['id'],
                'name'  => $user['name'],
                'email' => $user['email'],
                'role'  => $user['role'] ?? 'user',
            ],
        ]);
    }

    private function emailExists(string $email): bool
    {
        $stmt = $this->pdo->prepare(
            "SELECT id FROM users WHERE email = :email"
        );
        $stmt->execute(['email' => $email]);
        return (bool) $stmt->fetch();
    }
}

?>
