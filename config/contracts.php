<?php

use Core\Path;
use Contracts\DatabaseContract;

$dbConfig = require Path::config('database.php');

return [
    'singletons' => [
        DatabaseContract::class => $dbConfig['class'],
    ],
    'bindings' => []
];
