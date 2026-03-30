<?php

namespace Databases;

use Exception; // Исключения

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
        // Настройки напрямую из окружения .env
        $host    = env('DB_HOST', '127.0.0.1'); // Хост
        $port    = env('DB_PORT', '5432'); // Порт
        $dbname  = env('DB_NAME'); // База
        $user    = env('DB_USER'); // Юзер
        $pass    = env('DB_PASS', ''); // Пароль
        $charset = env('DB_CHARSET', 'utf8'); // Кодировка

        if (!$dbname || !$user) {
            throw new Exception("Ошибка Postgres: Проверьте переменные в .env (DB_NAME, DB_USER)."); // Валидация
        }

        $dsn = "pgsql:host={$host};port={$port};dbname={$dbname}"; // Формирование DSN

        $this->connect($dsn, $user, $pass); // Подключение через родителя

        if (!empty($charset)) {
            $this->pdo->exec("SET NAMES '{$charset}'"); // Установка кодировки для PG
        }
    }
}
