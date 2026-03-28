<?php
return [
    'host'     => env('PG_HOST', 'localhost'),
    'port'     => env('PG_PORT', '5432'),
    'dbname'   => env('PG_NAME'),
    'username' => env('PG_USER'),
    'password' => env('PG_PASS'),
    'charset'  => env('PG_CHARSET', 'utf8'),
];
