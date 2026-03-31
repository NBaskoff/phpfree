<?php
require_once __DIR__ . '/../app/Core/App.php'; // Подключаем App из директории /app/
Core\App::init(); // Инициализируем систему от корня проекта

// Стандартный запуск роутера
$router = new Core\Router();
$router->loadRoutes('', Core\Path::routes('web.php'));
$router->dispatch();
