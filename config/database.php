<?php

use Databases\MySQLDatabase;
use Databases\PostgresDatabase;

$driver = env('DB_DRIVER', 'mysql');

return match($driver) {
    'pgsql' => PostgresDatabase::class,
    'mysql' => MySQLDatabase::class,
    default => throw new \Exception("Неподдерживаемый драйвер БД: $driver")
};
