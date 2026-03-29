<?php

use Core\App;
use Core\Router;
use Core\Path;

require_once __DIR__ . '/../Core/App.php';

// Инициализируем всё окружение
App::init(__DIR__);

// Запускаем роутинг
$router = new Router();
$router->loadRoutes(Path::config('routes.php'));
$router->dispatch();
