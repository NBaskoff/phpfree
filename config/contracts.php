<?php

use Contracts\{DatabaseContract, SessionContract}; // Интерфейсы
use Core\{Session}; // Реализации

return [
    'singletons' => [
        // Динамически получаем класс драйвера из конфига database
        DatabaseContract::class => config('databases.class'),
        SessionContract::class   => Session::class, // Сессия как синглтон
    ],
    'bindings' => [
        // Здесь можно регистрировать обычные привязки
    ]
];
