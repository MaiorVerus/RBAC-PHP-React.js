
<?php

namespace App\Middleware;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use Firebase\JWT\BeforeValidException;

class AuthMiddleware
{
    public static function authenticate(): object
    {
        header('Content-Type: application/json');

        $headers    = getallheaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;

        if (!$authHeader) {
            http_response_code(401);
            echo json_encode(['error' => 'No token provided', 'code' => 'NO_TOKEN']);
            exit();
        }

        if (!preg_match('/^Bearer\s+(.+)$/i', $authHeader, $matches)) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid Authorization format', 'code' => 'BAD_FORMAT']);
            exit();
        }

        $token = $matches[1];

        // ── 4. Validate secret exists ─────────────────────────────────────────
        $secret = $_ENV['JWT_SECRET'] ?? null;

        if (!$secret) {
            error_log('JWT_SECRET missing from environment');  // server-side only
            http_response_code(500);
            echo json_encode(['error' => 'Server configuration error.', 'code' => 'SERVER_ERROR']);
            exit();
        }

        // ── 5. Decode & verify ────────────────────────────────────────────────
        try {
            return JWT::decode($token, new Key($secret, 'HS256'));
        } catch (ExpiredException $e) {
            http_response_code(401);
            echo json_encode(['error' => 'Token expired', 'code' => 'TOKEN_EXPIRED']);
            exit();
        } catch (SignatureInvalidException $e) {
            error_log('JWT signature invalid. IP: ' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
            http_response_code(401);
            echo json_encode(['error' => 'Token invalid', 'code' => 'TOKEN_INVALID']);
            exit();
        } catch (BeforeValidException $e) {
            http_response_code(401);
            echo json_encode(['error' => 'Token not yet valid', 'code' => 'TOKEN_NOT_YET_VALID']);
            exit();
        } catch (\Exception $e) {
            http_response_code(401);
            echo json_encode(['error' => 'Authentication failed', 'code' => 'AUTH_FAILED']);
            exit();
        }
    }

    public static function authorize(array $allowedRoles): object
    {
        $user = self::authenticate();

        $userRole = $user->role ?? null;

        if (!$userRole || !in_array($userRole, $allowedRoles, strict: true)) {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden', 'code' => 'INSUFFICIENT_ROLE']);
            exit();
        }

        return $user;
    }
}
?>