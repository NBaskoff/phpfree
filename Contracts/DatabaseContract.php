<?php

namespace Contracts;

use PDOStatement;

interface DatabaseContract
{
    public function query(string $sql, array $params = []): PDOStatement;
    public function row(string $sql, array $params = []): array|false;
    public function all(string $sql, array $params = []): array;
    public function lastInsertId(?string $name = null): string|false;
    public function beginTransaction(): bool;
    public function commit(): bool;
    public function rollBack(): bool;
}
