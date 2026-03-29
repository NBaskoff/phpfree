<?php

use Core\Path;
use Contracts\DatabaseContract;
use Core\Session;
use Contracts\SessionContract;

$database = require Path::config('database.php');
return [
    'singletons' => [
        DatabaseContract::class => $database,
        SessionContract::class => Session::class
    ],
    'bindings' => []
];
