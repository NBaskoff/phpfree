<?php
use Core\Path;
use Core\Contract;
use Core\Router;
use Core\Autoloader;

require_once __DIR__ . '/../Core/Path.php';
Path::initFromPublic(__DIR__);
require_once Path::root('Core/Autoloader.php');
Autoloader::register();
Contract::loadConfig(Path::root('config/container.php'));
$router = new Router();
$router->loadRoutes(Path::root('config/routes.php'));
$router->dispatch();
