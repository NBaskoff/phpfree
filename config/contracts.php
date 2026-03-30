<?php

use Core\Path;
use Contracts\DatabaseContract;
use Core\Session;
use Contracts\SessionContract;

$database = require Path::configs('databases.php');
return [
    'singletons' => [
        DatabaseContract::class => $database,
        SessionContract::class => Session::class
    ],
    'bindings' => []
];
