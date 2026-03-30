<?php

namespace Databases;

use Exception;

/**
 * Драйвер для работы с MySQL
 */
class MySQLDatabase extends AbstractSqlDatabase
{
    /**
     * @throws Exception
     */
    public function __construct()
    {
        // Берем настройки напрямую из окружения, так как конфиг теперь плоский
        $host    = env('DB_HOST', '127.0.0.1'); // Хост
        $port    = env('DB_PORT', '3306'); // Порт
        $dbname  = env('DB_NAME'); // Имя базы
        $user    = env('DB_USER'); // Логин
        $pass    = env('DB_PASS', ''); // Пароль
        $charset = env('DB_CHARSET', 'utf8mb4'); // Кодировка

        if (!$dbname || !$user) {
            throw new Exception("Ошибка MySQL: Проверьте переменные в .env (DB_NAME, DB_USER).");
        }

        $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset={$charset}"; // Сборка DSN

        $this->connect($dsn, $user, $pass); // Подключение через родительский метод
    }
}
