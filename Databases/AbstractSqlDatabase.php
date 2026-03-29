<?php

namespace Databases;

use Contracts\DatabaseContract;
use PDO;
use PDOStatement;
use PDOException;
use Exception;

/**
 * Базовая реализация SQL-хранилища на основе PDO
 */
abstract class AbstractSqlDatabase implements DatabaseContract
{
    /** @var PDO Экземпляр соединения */
    protected PDO $pdo;

    /**
     * Инициализирует соединение с базой данных
     *
     * @param string $dsn
     * @param string $user
     * @param string $pass
     * @throws Exception
     */
    protected function connect(string $dsn, string $user, string $pass): void
    {
        try {
            $this->pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            throw new Exception("Ошибка подключения к БД: " . $e->getMessage());
        }
    }

    /**
     * @param string $sql
     * @param array $params
     * @return PDOStatement
     */
    public function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * @param string $sql
     * @param array $params
     * @return array|false
     */
    public function row(string $sql, array $params = []): array|false
    {
        return $this->query($sql, $params)->fetch();
    }

    /**
     * @param string $sql
     * @param array $params
     * @return array
     */
    public function all(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetchAll();
    }

    /**
     * @param string|null $name
     * @return string|false
     */
    public function lastInsertId(?string $name = null): string|false
    {
        return $this->pdo->lastInsertId($name);
    }

    /**
     * @return bool
     */
    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }

    /**
     * @return bool
     */
    public function commit(): bool
    {
        return $this->pdo->commit();
    }

    /**
     * @return bool
     */
    public function rollBack(): bool
    {
        return $this->pdo->rollBack();
    }
}
