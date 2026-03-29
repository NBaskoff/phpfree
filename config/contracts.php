<?php

use Core\Path;
use Contracts\DatabaseContract;

// Теперь здесь лежит просто строка, например "Databases\MySQLDatabase"
$database = require Path::config('database.php');

return [
    'singletons' => [
        // Передаем строку напрямую
        DatabaseContract::class => $database,
        \Contracts\SessionContract::class => \Core\Session::class
    ],
    'bindings' => []
];
