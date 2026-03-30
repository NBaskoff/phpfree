<?php

require_once __DIR__ . '/../Core/App.php'; // Подключаем ядро
Core\App::init(__DIR__); // Инициализация путей, автозагрузки и конфигов

$router = new Core\Router(); // Создаем автономный роутер (Request и Resolver создадутся внутри)
$router->loadRoutes('', Core\Path::routes('web.php')); // Загружаем маршруты из файла web.php
$router->dispatch(); // Запускаем поиск маршрута и выполнение контроллера
