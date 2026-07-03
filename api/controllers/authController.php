<?php




require __DIR__ . "/../config/db.php";
require __DIR__ . "/../helpers/JwtHelper.php";

class AuthController
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    public function register(?array $body): void
    {
        $required = ['name', 'email', 'password'];
        foreach ($required as $field) {
            if (empty($body[$field])) {
                http_response_code(422);
                echo json_encode(['error' => "Field '$field' is required"]);
                return;
            }
        }

        $name     = trim($body['name']);
        $email    = trim(strtolower($body['email']));
        $password = $body['password'];

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
            "INSERT INTO users (name, email, password) VALUES (?, ?, ?)"
        );
        $stmt->execute([$name, $email, $hashed]);

        http_response_code(201);
        echo json_encode(['message' => 'Account created successfully']);
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

        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid credentials']);
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
            'message' => 'Login successful',
            'token'   => $token,
        ]);
    }

    private function emailExists(string $email): bool
    {
        $stmt = $this->pdo->prepare(
            "SELECT id FROM users WHERE email = ?"
        );
        $stmt->execute([$email]);
        return (bool) $stmt->fetch();
    }
}

?>