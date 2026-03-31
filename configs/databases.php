<?php

use Databases\{MySQLDatabase, PostgresDatabase};

return [
    'connections' => [
        'mysql' => [
            'host'    => env('DB_HOST', '127.0.0.1'),
            'port'    => env('DB_PORT', '3306'),
            'dbname'  => env('DB_NAME'),
            'user'    => env('DB_USER'),
            'pass'    => env('DB_PASS', ''),
            'charset' => env('DB_CHARSET', 'utf8mb4'),
        ],
        'pgsql' => [
            'host'    => env('DB_HOST', '127.0.0.1'),
            'port'    => env('DB_PORT', '5432'),
            'dbname'  => env('DB_NAME'),
            'user'    => env('DB_USER'),
            'pass'    => env('DB_PASS', ''),
            'charset' => env('DB_CHARSET', 'utf8'),
        ],
    ],

    // Метод для получения класса (используется в contracts.php)
    'class' => match(env('DB_DRIVER', 'mysql')) {
        'pgsql' => PostgresDatabase::class,
        'mysql' => MySQLDatabase::class,
        default => throw new Exception("Неподдерживаемый драйвер")
    }
];
