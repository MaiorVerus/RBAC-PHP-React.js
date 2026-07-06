<?php

class Database
{
    // [1] Holds the single instance of this class
    private static ?Database $instance = null;

    // [2] Holds the actual PDO connection
    private PDO $pdo;

    // [3] Private constructor — blocks "new Database()" from outside
    private function __construct()
    {
        // [4] Validate required ENV keys exist before using them
        $required = ['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS', 'DB_CHARSET'];
        foreach ($required as $key) {
            if (empty($_ENV[$key])) {
                throw new RuntimeException("Missing environment variable: $key");
            }
        }

        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            $_ENV['DB_HOST'],
            $_ENV['DB_NAME'],
            $_ENV['DB_CHARSET']
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        // [5] Throws — doesn't die()
        $this->pdo = new PDO($dsn, $_ENV['DB_USER'], $_ENV['DB_PASS'], $options);
    }

    // [6] The only way to get the instance
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    // [7] The only way to get the PDO object
    public function getConnection(): PDO
    {
        return $this->pdo;
    }

    // [8] Block cloning and unserialization — Singleton must stay one
    private function __clone() {}
    public function __wakeup(): never
    {
        throw new RuntimeException("Cannot unserialize a singleton.");
    }
}
