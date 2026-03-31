<?php

namespace Databases;

use Exception;

class PostgresDatabase extends AbstractSqlDatabase
{
    public function __construct()
    {
        $c = config('database.connections.pgsql'); // Читаем конфиг

        if (empty($c['dbname']) || empty($c['user'])) {
            throw new Exception("Ошибка Postgres: Неверные настройки"); // Валидация
        }

        $dsn = "pgsql:host={$c['host']};port={$c['port']};dbname={$c['dbname']}"; // DSN

        $this->connect($dsn, $c['user'], $c['pass']); // Подключение

        if (!empty($c['charset'])) $this->pdo->exec("SET NAMES '{$c['charset']}'"); // Кодировка
    }
}
