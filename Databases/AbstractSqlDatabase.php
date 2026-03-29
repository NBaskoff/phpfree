<?php

namespace Databases;

use Contracts\DatabaseContract;
use PDO;
use PDOStatement;
use PDOException;
use Exception;

/**
 * Базовый класс для всех SQL-совместимых баз данных (PDO)
 */
abstract class AbstractSqlDatabase implements DatabaseContract
{
    /** @var PDO Экземпляр соединения */
    protected PDO $pdo;

    /**
     * Создает подключение к базе данных с общими настройками
     *
     * @param string $dsn Строка подключения
     * @param string $user Пользователь
     * @param string $pass Пароль
     * @throws Exception
     */
    protected function connect(string $dsn, string $user, string $pass): void
    {
        try {
            $this->pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_ERR_MODE           => PDO::ERR_MODE_EXCEPTION,
            ]);
        } catch (PDOException $e) {
            throw new Exception("Ошибка подключения к БД: " . $e->getMessage());
        }
    }

    /**
     * Выполняет SQL-запрос и возвращает объект стейтмента
     */
    public function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Получает одну строку из результата запроса
     */
    public function row(string $sql, array $params = []): array|false
    {
        return $this->query($sql, $params)->fetch();
    }

    /**
     * Получает все строки из результата запроса
     */
    public function all(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetchAll();
    }

    /**
     * Возвращает ID последней вставленной записи
     */
    public function lastInsertId(?string $name = null): string|false
    {
        return $this->pdo->lastInsertId($name);
    }

    public function beginTransaction(): bool { return $this->pdo->beginTransaction(); }
    public function commit(): bool { return $this->pdo->commit(); }
    public function rollBack(): bool { return $this->pdo->rollBack(); }
}
