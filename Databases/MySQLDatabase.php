<?php

namespace Databases;

use Contracts\DatabaseContract;
use Core\Path;
use PDO;
use PDOException;
use PDOStatement;
use Exception;

class MySQLDatabase implements DatabaseContract
{
    private PDO $pdo;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        $configPath = Path::config('database/mysql.php');

        if (!file_exists($configPath)) {
            throw new Exception("Конфигурация MySQL не найдена по адресу: {$configPath}");
        }

        $c = require $configPath;

        // В MySQL кодировка (charset) передается прямо в DSN
        $dsn = "mysql:host={$c['host']};port={$c['port']};dbname={$c['dbname']};charset={$c['charset']}";

        try {
            // В PHP 8.0+ режим PDO::ERR_MODE_EXCEPTION включен по умолчанию,
            // поэтому мы фокусируемся на других важных опциях.
            $this->pdo = new PDO($dsn, $c['username'], $c['password'], [
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false, // Используем реальные подготовленные выражения
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

    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->pdo->commit();
    }

    public function rollBack(): bool
    {
        return $this->pdo->rollBack();
    }
}
