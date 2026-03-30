<?php

require_once __DIR__ . '/../Core/App.php'; // Подключение App
Core\App::init(__DIR__); // Подготовка окружения (без запуска роутера)

$router = new Core\Router(); // Создание роутера (берет зависимости из Contract)
$router->loadRoutes('', Core\Path::routes('web.php')); // Загрузка маршрутов
$router->dispatch(); // запуск
