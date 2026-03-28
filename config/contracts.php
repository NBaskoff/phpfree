<?php

use Contracts\DatabaseContract;
use Databases\MySQLDatabase;
use Databases\PostgresDatabase;

return [
    /**
     * Одиночки (singletons)
     * Объект создается один раз и переиспользуется.
     */
    'singletons' => [
        // Для MySQL:
        DatabaseContract::class => MySQLDatabase::class,

        // ИЛИ для Postgres:
        // DatabaseContract::class => PostgresDatabase::class,
    ],

    /**
     * Обычные привязки (bind)
     * Каждый раз создается новый экземпляр класса.
     */
    'bindings' => [
        // Интерфейс => Реализация
        //LoggerInterface::class => FileLogger::class,
    ],
];