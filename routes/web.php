<?php
return [
    '/' => [
        'GET' => [\Controllers\IndexController::class, 'actionIndexGet']
    ],

    '/users' => [
        'GET' => [\Controllers\UserController::class, 'actionIndexGet']
    ],

/*    '/user/{id}' => [
        'GET' => [UserController::class, 'show']
    ],
    '/contact' => [
        'GET'  => [ContactController::class, 'showForm'],
        'POST' => [ContactController::class, 'submitForm']
    ],*/
];