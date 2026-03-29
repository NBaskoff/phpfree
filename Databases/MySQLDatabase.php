<?php

namespace Databases;

use Exception;

class MySQLDatabase extends AbstractSqlDatabase
{
    public function __construct()
    {
        $host    = env('DB_HOST', '127.0.0.1');
        $port    = env('DB_PORT', '3306');
        $dbname  = env('DB_NAME');
        $user    = env('DB_USER');
        $pass    = env('DB_PASS', '');
        $charset = env('DB_CHARSET', 'utf8mb4');

        if (!$dbname || !$user) {
            throw new Exception("Ошибка MySQL: Не указаны DB_NAME или DB_USER в .env");
        }

        $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset={$charset}";

        // Вызываем общий метод подключения
        $this->connect($dsn, $user, $pass);
    }
}
