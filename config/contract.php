<?php
return [
    /**
     * Одиночки (singletons)
     * Объект создается один раз и переиспользуется.
     */
    'singletons' => [
        // Интерфейс => Реализация
        /*        DatabaseInterface::class => MysqlDatabase::class,
                CacheInterface::class    => RedisCache::class,*/
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