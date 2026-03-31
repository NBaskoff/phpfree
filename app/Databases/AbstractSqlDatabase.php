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
     * @param string $dsn Строка подключения
     * @param string $user Имя пользователя
     * @param string $pass Пароль
     * @throws Exception
     */
    protected function connect(string $dsn, string $user, string $pass): void
    {
        try {
            $this->pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            throw new Exception("Ошибка подключения к БД: " . $e->getMessage());
        }
    }

    /**
     * Подготавливает и выполняет SQL запрос
     *
     * @param string $sql SQL текст
     * @param array $params Параметры для подстановки
     * @return PDOStatement
     */
    public function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Получает одну строку результата
     *
     * @param string $sql
     * @param array $params
     * @return array|false
     */
    public function row(string $sql, array $params = []): array|false
    {
        return $this->query($sql, $params)->fetch();
    }

    /**
     * Получает все строки результата
     *
     * @param string $sql
     * @param array $params
     * @return array
     */
    public function all(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetchAll();
    }

    /**
     * Возвращает ID последней вставленной записи
     *
     * @param string|null $name Имя последовательности (для Postgres)
     * @return string|false
     */
    public function lastInsertId(?string $name = null): string|false
    {
        return $this->pdo->lastInsertId($name);
    }

    /**
     * Начало транзакции с проверкой, не запущена ли она уже
     *
     * @return bool
     */
    public function beginTransaction(): bool
    {
        return !$this->pdo->inTransaction() ? $this->pdo->beginTransaction() : true;
    }

    /**
     * Подтверждение транзакции с проверкой её активности
     *
     * @return bool
     */
    public function commit(): bool
    {
        if ($this->pdo->inTransaction()) {
            return $this->pdo->commit();
        }
        return false;
    }

    /**
     * Откат транзакции с проверкой её активности
     *
     * @return bool
     */
    public function rollBack(): bool
    {
        if ($this->pdo->inTransaction()) {
            return $this->pdo->rollBack();
        }
        return false;
    }

    /**
     * Проверяет существование записи по заданному полю и значению
     *
     * @param string $table Имя таблицы
     * @param string $column Имя колонки
     * @param mixed $value Проверяемое значение
     * @param mixed $ignoreId ID для исключения (полезно при обновлении)
     * @return bool
     */
    public function exists(string $table, string $column, mixed $value, mixed $ignoreId = null): bool
    {
        // Оборачиваем имена таблицы и колонки в обратные кавычки
        $sql = "SELECT COUNT(*) as count FROM `{$table}` WHERE `{$column}` = :val";
        $params = ['val' => $value];

        if ($ignoreId !== null) {
            // Оборачиваем id в кавычки для единообразия
            $sql .= " AND `id` != :id";
            $params['id'] = $ignoreId;
        }

        $result = $this->row($sql, $params);

        return (int)($result['count'] ?? 0) > 0;
    }

}
