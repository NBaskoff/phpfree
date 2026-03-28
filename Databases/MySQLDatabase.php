<?php

namespace Databases;

use Contracts\DatabaseContract;
use PDO;
use PDOException;
use PDOStatement;
use Exception;

class MySQLDatabase implements DatabaseContract
{
    private PDO $pdo;

    public function __construct()
    {
        $host    = env('DB_HOST', '127.0.0.1');
        $port    = env('DB_PORT', '3306');
        $dbname  = env('DB_NAME');
        $user    = env('DB_USER');
        $pass    = env('DB_PASS', '');
        $charset = env('DB_CHARSET', 'utf8mb4');

        if (!$dbname || !$user) {
            throw new Exception("Ошибка MySQL: Проверьте DB_NAME и DB_USER в .env");
        }

        $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset={$charset}";

        try {
            $this->pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            throw new Exception("Ошибка подключения к MySQL: " . $e->getMessage());
        }
    }

    public function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function row(string $sql, array $params = []): array|false
    {
        return $this->query($sql, $params)->fetch();
    }

    public function all(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetchAll();
    }

    public function lastInsertId(?string $name = null): string|false
    {
        return $this->pdo->lastInsertId($name);
    }

    public function beginTransaction(): bool { return $this->pdo->beginTransaction(); }
    public function commit(): bool { return $this->pdo->commit(); }
    public function rollBack(): bool { return $this->pdo->rollBack(); }
}
