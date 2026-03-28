<?php

namespace Databases;

use Contracts\DatabaseContract;
use Core\Path;
use PDO;
use PDOException;
use Exception;

class PostgresDatabase implements DatabaseContract
{
    private PDO $pdo;

    public function __construct()
    {
        $configPath = Path::config('database/postgres.php');

        if (!file_exists($configPath)) {
            throw new Exception("Конфигурация Postgres не найдена: $configPath");
        }

        $c = require $configPath;

        // В Postgres кодировка НЕ указывается в DSN
        $dsn = "pgsql:host={$c['host']};port={$c['port']};dbname={$c['dbname']}";

        try {
            $this->pdo = new PDO($dsn, $c['username'], $c['password'], [
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);

            // Для Postgres кодировка задается отдельным запросом после подключения
            if (!empty($c['charset'])) {
                $this->pdo->exec("SET NAMES '{$c['charset']}'");
            }
        } catch (PDOException $e) {
            throw new Exception("Ошибка подключения к Postgres: " . $e->getMessage());
        }
    }

    public function query(string $sql, array $params = []): \PDOStatement
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
        // В Postgres для lastInsertId часто нужно передавать имя последовательности (sequence)
        return $this->pdo->lastInsertId($name);
    }

    public function beginTransaction(): bool { return $this->pdo->beginTransaction(); }
    public function commit(): bool { return $this->pdo->commit(); }
    public function rollBack(): bool { return $this->pdo->rollBack(); }
}
