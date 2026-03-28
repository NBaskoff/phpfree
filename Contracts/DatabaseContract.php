<?php

namespace Contracts;

use PDOStatement;

/**
 * Контракт для работы с базой данных (стандарт PHP 8.4)
 */
interface DatabaseContract
{
    /**
     * Выполнение запроса и возврат объекта стейтмента
     */
    public function query(string $sql, array $params = []): PDOStatement;

    /**
     * Получение одной строки (массив или false, если ничего не найдено)
     */
    public function row(string $sql, array $params = []): array|false;

    /**
     * Получение всех строк в виде массива
     */
    public function all(string $sql, array $params = []): array;

    /**
     * Получение последнего вставленного ID
     */
    public function lastInsertId(?string $name = null): string|false;

    /**
     * Управление транзакциями
     */
    public function beginTransaction(): bool;

    public function commit(): bool;

    public function rollBack(): bool;
}
