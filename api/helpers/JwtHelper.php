<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtHelper
{
    private string $secretKey;
    private string $algorithm = 'HS256';
    private string $issuer = 'CRUD-RBAC';

    public function __construct()
    {
        $key = $_ENV['JWT_KEY'] ?? null;

        if (empty($key)) {
            throw new RuntimeException('Missing environment variable: JWT_KEY');
        }

        $this->secretKey = $key;
    }

    public function generateToken(array $data): string
    {
        $issuedAt = time();
        $expirationTime = $issuedAt + 3600; // 1 hour

        $payload = [
            'iss'  => $this->issuer,
            'aud'  => $this->issuer,
            'iat'  => $issuedAt,
            'nbf'  => $issuedAt,
            'exp'  => $expirationTime,
            'data' => $data,
        ];

        return JWT::encode($payload, $this->secretKey, $this->algorithm);
    }

    public function validateToken(string $token): array
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secretKey, $this->algorithm));
            return (array) $decoded->data;
        } catch (Exception $e) {
            throw new Exception('Invalid token: ' . $e->getMessage());
        }
    }
}
