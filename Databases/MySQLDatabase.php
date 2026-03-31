<?php

namespace Databases;

use Exception;

class MySQLDatabase extends AbstractSqlDatabase
{
    public function __construct()
    {
        $c = config('databases.connections.mysql'); // Читаем конфиг одной строкой

        if (empty($c['dbname']) || empty($c['user'])) {
            throw new Exception("Ошибка Postgres: Неверные настройки"); // Валидация
        }

        $dsn = "mysql:host={$c['host']};port={$c['port']};dbname={$c['dbname']};charset={$c['charset']}"; // DSN

        $this->connect($dsn, $c['user'], $c['pass']); // Подключение
    }
}
