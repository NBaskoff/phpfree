<?php

// Подключаем App из новой директории /app/Core/
require_once __DIR__ . '/../app/Core/App.php';

// Инициализируем систему от корня проекта
Core\App::init();

// Стандартный запуск роутера
$router = new Core\Router();
$router->loadRoutes('', Core\Path::routes('web.php'));
$router->dispatch();
