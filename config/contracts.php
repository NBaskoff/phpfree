<?php
return [
    /**
     * Одиночки (singletons)
     * Объект создается один раз и переиспользуется.
     */
    'singletons' => [
        // Для MySQL:
        \Contracts\DatabaseContract::class => \Databases\MySQLDatabase::class,

        // ИЛИ для Postgres:
        // \Contracts\DatabaseContract::class => \Databases\PostgresDatabase::class,
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