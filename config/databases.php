<?php

use Databases\MySQLDatabase;
use Databases\PostgresDatabase;

// Определяем драйвер из .env
$driver = env('DB_DRIVER', 'mysql');

// Возвращаем просто строку (имя класса)
return match($driver) {
    'pgsql' => PostgresDatabase::class,
    'mysql' => MySQLDatabase::class,
    default => throw new \Exception("Неподдерживаемый драйвер БД: $driver")
};
