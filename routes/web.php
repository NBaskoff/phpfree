<?php

use Controllers\{IndexController, UserController/*, AuthController*/};
use Middleware\{AuthMiddleware, CsrfMiddleware};

return [
    // Публичные маршруты
    '/' => [
        'GET' => [IndexController::class, 'actionIndexGet']
    ],
    '/users' => [
        'GET' => [\Controllers\UserController::class, 'actionIndexGet']
    ],
    '/user/{id}' => [
        'GET' => [\Controllers\UserController::class, 'actionOneGet']
    ],
/*
    // Группа авторизации (только для гостей + CSRF)
    '/auth' => [
        'middleware' => [CsrfMiddleware::class], // Общий MW для всей группы
        'routes' => [
            '/login' => [
                'GET'  => [AuthController::class, 'showLoginForm'],
                'POST' => [AuthController::class, 'login']
            ],
            '/register' => [
                'GET'  => [AuthController::class, 'showRegisterForm'],
                'POST' => [AuthController::class, 'register']
            ]
        ]
    ],

    // Группа личного кабинета (только для своих)
    '/admin' => [
        'middleware' => [AuthMiddleware::class, CsrfMiddleware::class], // Сразу два MW
        'routes' => [
            '/users' => [
                'GET' => [UserController::class, 'actionIndexGet']
            ],
            '/user/update/{id}' => [
                'POST' => [UserController::class, 'actionUpdatePost']
            ]
        ]
    ]
*/
];
