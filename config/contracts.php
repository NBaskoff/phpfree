<?php

use Core\Path;
use Contracts\DatabaseContract;
use Core\Session;
use Contracts\SessionContract;

// Теперь здесь лежит просто строка, например "Databases\MySQLDatabase"
$database = require Path::config('database.php');

return [
    'singletons' => [
        // Передаем строку напрямую
        DatabaseContract::class => $database,
        SessionContract::class => Session::class
    ],
    'bindings' => []
];
