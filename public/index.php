<?php

use Core\Path;
use Core\Contract;
use Core\Router;
use Core\Autoloader;
use Core\Env;

// 1. Инициализация путей (всегда первой)
require_once __DIR__ . '/../Core/Path.php';
Path::initFromPublic(__DIR__);

// 2. Регистрация автозагрузчика
require_once Path::root('Core/Autoloader.php');
Autoloader::register();

// 3. Функция env() подхватится автоматически, если она объявлена в Core/Env.php вне класса
require_once Path::root('Core/Env.php');
Env::load(Path::root('.env'));

// 4. Загрузка контейнера (Contract внутри использует env() для БД)
Contract::loadConfig(Path::config('contracts.php'));

// 5. Роутинг и запуск
$router = new Router();
$router->loadRoutes(Path::config('routes.php'));
$router->dispatch();
