<?php

namespace Databases;

use Exception;

/**
 * Драйвер для работы с PostgreSQL
 */
class PostgresDatabase extends AbstractSqlDatabase
{
    /**
     * @throws Exception
     */
    public function __construct()
    {
        $host    = env('DB_HOST', '127.0.0.1');
        $port    = env('DB_PORT', '5432');
        $dbname  = env('DB_NAME');
        $user    = env('DB_USER');
        $pass    = env('DB_PASS', '');
        $charset = env('DB_CHARSET', 'utf8');

        if (!$dbname || !$user) {
            throw new Exception("Ошибка Postgres: Проверьте переменные окружения.");
        }

        $dsn = "pgsql:host={$host};port={$port};dbname={$dbname}";

        $this->connect($dsn, $user, $pass);

        if (!empty($charset)) {
            $this->pdo->exec("SET NAMES '{$charset}'");
        }
    }
}
