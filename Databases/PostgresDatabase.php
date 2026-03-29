<?php

namespace Databases;

use Exception;

class PostgresDatabase extends AbstractSqlDatabase
{
    public function __construct()
    {
        $host   = env('DB_HOST', '127.0.0.1');
        $port   = env('DB_PORT', '5432');
        $dbname = env('DB_NAME');
        $user   = env('DB_USER');
        $pass   = env('DB_PASS', '');
        $charset = env('DB_CHARSET', 'utf8');

        if (!$dbname || !$user) {
            throw new Exception("Ошибка Postgres: Не указаны DB_NAME или DB_USER в .env");
        }

        $dsn = "pgsql:host={$host};port={$port};dbname={$dbname}";

        // Подключаемся и устанавливаем кодировку специфичным для Postgres способом
        $this->connect($dsn, $user, $pass);
        $this->pdo->exec("SET NAMES '{$charset}'");
    }
}
